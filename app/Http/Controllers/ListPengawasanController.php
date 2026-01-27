<?php

namespace App\Http\Controllers;

use App\Models\Module;
use App\Models\ModuleAccess;
use App\Models\User;
use App\Models\PengawasKegiatan;
use App\Notifications\SystemNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Carbon\Carbon;
use Spatie\Permission\Exceptions\RoleDoesNotExist;

class ListPengawasanController extends Controller
{
    private const ALLOWED_STATUS = ['OFF', 'On Progress', 'Done'];

    private function normalizeStatus(?string $status): string
    {
        if (!$status) {
            return 'On Progress';
        }

        if ($status === 'Active') {
            return 'On Progress';
        }

        return $status;
    }

    private function canWriteForModule($user): bool
    {
        if ($user->hasRole(['Admin', 'Supervisor'])) {
            return true;
        }

        $module = Module::where('slug', 'list-pengawasan')->first();
        if (!$module) {
            return false;
        }

        return ModuleAccess::where('user_id', $user->id)
            ->where('module_id', $module->id)
            ->where('can_write', true)
            ->exists();
    }

    private function canAccessPengawas($user, int $pengawasId): bool
    {
        if ($user->hasRole(['Admin', 'Supervisor'])) {
            return true;
        }

        return DB::table('pengawas_users')
            ->where('pengawas_id', $pengawasId)
            ->where('user_id', $user->id)
            ->exists();
    }

    private function getAssignedUsersMap(array $pengawasIds): array
    {
        if (empty($pengawasIds)) {
            return [];
        }

        $rows = DB::table('pengawas_users')
            ->join('users', 'users.id', '=', 'pengawas_users.user_id')
            ->whereIn('pengawas_users.pengawas_id', $pengawasIds)
            ->orderBy('users.name')
            ->get([
                'pengawas_users.pengawas_id',
                'users.id',
                'users.name',
                'users.email',
            ]);

        return $rows->groupBy('pengawas_id')
            ->map(fn($group) => $group->map(fn($row) => [
                'id' => $row->id,
                'name' => $row->name,
                'email' => $row->email,
            ])->values()->toArray())
            ->toArray();
    }

    private function getListPengawasanPermissions($user): array
    {
        if ($user->hasRole(['Admin', 'Supervisor'])) {
            return [
                'tambah_proyek' => true,
                'nama_proyek' => true,
                'pengawas' => true,
                'deadline' => true,
                'status' => true,
                'keterangan' => true,
                'edit_keterangan' => true,
                'bukti' => true,
            ];
        }

        $module = Module::where('slug', 'list-pengawasan')->first();
        if (!$module) {
            return [
                'tambah_proyek' => false,
                'nama_proyek' => false,
                'pengawas' => false,
                'deadline' => false,
                'status' => false,
                'keterangan' => false,
                'edit_keterangan' => false,
                'bukti' => false,
            ];
        }

        $access = ModuleAccess::where('user_id', $user->id)
            ->where('module_id', $module->id)
            ->first();

        $base = [
            'tambah_proyek' => false,
            'nama_proyek' => false,
            'pengawas' => false,
            'deadline' => false,
            'status' => false,
            'keterangan' => false,
            'edit_keterangan' => false,
            'bukti' => false,
        ];

        if (!$access || !is_array($access->extra_permissions['list_pengawasan'] ?? null)) {
            return $base;
        }

        return array_merge($base, $access->extra_permissions['list_pengawasan']);
    }

    private function getListPengawasanNotificationRecipients(int $pengawasId, int $actorId)
    {
        $module = Module::where('slug', 'list-pengawasan')->first();
        $moduleUserIds = collect();
        if ($module) {
            $moduleUserIds = ModuleAccess::where('module_id', $module->id)
                ->where('can_read', true)
                ->pluck('user_id');
        }

        $assignedUserIds = DB::table('pengawas_users')
            ->where('pengawas_id', $pengawasId)
            ->pluck('user_id');

        if ($moduleUserIds->isNotEmpty()) {
            $assignedUserIds = $assignedUserIds->intersect($moduleUserIds);
        }

        try {
            $adminIds = User::role(['Admin', 'Supervisor'])->pluck('id');
        } catch (RoleDoesNotExist) {
            $adminIds = collect();
        }

        $recipientIds = $assignedUserIds
            ->merge($adminIds)
            ->push($actorId)
            ->unique()
            ->values();

        if ($recipientIds->isEmpty()) {
            return collect();
        }

        return User::whereIn('id', $recipientIds)->get();
    }

    private function notifyListPengawasan(User $actor, int $pengawasId, string $action, string $description): void
    {
        $recipients = $this->getListPengawasanNotificationRecipients($pengawasId, $actor->id);
        if ($recipients->isEmpty()) {
            return;
        }

        Notification::send($recipients, new SystemNotification(
            $action,
            'List Pengawasan',
            $description,
            $actor->name
        ));
    }

    private function getPengawasName(int $pengawasId): ?string
    {
        return DB::table('pengawas')->where('id', $pengawasId)->value('name');
    }

    private function recalculateProjectStatus(int $pengawasId): void
    {
        $project = DB::table('pengawas')->where('id', $pengawasId)->first();
        if (!$project) {
            return;
        }

        $activities = PengawasKegiatan::where('pengawas_id', $pengawasId)->get(['status']);

        $status = 'OFF';

        if ($activities->isNotEmpty()) {
            $hasRunning = $activities->contains(function ($a) {
                return in_array($a->status, ['Sedang Berjalan', 'Terlambat'], true);
            });

            $allDone = $activities->every(function ($a) {
                return $a->status === 'Selesai';
            });

            if ($hasRunning) {
                $status = 'On Progress';
            } elseif ($allDone) {
                $status = 'Done';
            } else {
                $status = 'OFF';
            }
        }

        DB::table('pengawas')
            ->where('id', $pengawasId)
            ->update([
                'status' => $status,
                'updated_at' => now(),
            ]);
    }

    public function show(int $id)
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        if (!$this->canAccessPengawas($user, $id)) {
            abort(403, 'Unauthorized action.');
        }

        $p = DB::table('pengawas')->where('id', $id)->first();
        if (!$p) {
            abort(404);
        }

        $assignedUsersMap = $this->getAssignedUsersMap([$id]);

        $keterangan = DB::table('pengawas_keterangan')
            ->join('keterangan_options', 'keterangan_options.id', '=', 'pengawas_keterangan.keterangan_option_id')
            ->where('pengawas_keterangan.pengawas_id', $p->id)
            ->select(
                'keterangan_options.name as label',
                'pengawas_keterangan.bukti_path',
                'pengawas_keterangan.bukti_original_name',
                'pengawas_keterangan.bukti_mime',
                'pengawas_keterangan.bukti_size',
                'pengawas_keterangan.bukti_uploaded_at'
            )
            ->get()
            ->map(function ($k) {
                return [
                    'label' => $k->label,
                    'bukti' => $k->bukti_path ? [
                        'path' => $k->bukti_path,
                        'name' => $k->bukti_original_name,
                        'mime' => $k->bukti_mime,
                        'size' => $k->bukti_size,
                        'uploaded_at' => $k->bukti_uploaded_at ? \Carbon\Carbon::parse($k->bukti_uploaded_at)->format('d-m-Y H:i') : null,
                        'url' => asset('storage/' . $k->bukti_path),
                    ] : null
                ];
            })
            ->values()
            ->toArray();

        $createdAt = $p->created_at ? Carbon::parse($p->created_at) : null;
        $deadline = $p->deadline ? Carbon::parse($p->deadline) : null;

        $item = [
            'id' => $p->id,
            'nama' => $p->name,
            'deskripsi' => $p->deskripsi ?? null,
            'divisi' => $p->divisi ?? '-',
            'created_at' => $createdAt ? $createdAt->toISOString() : null,
            'tanggal' => $p->tanggal ? Carbon::parse($p->tanggal)->format('d-m-Y') : ($createdAt ? $createdAt->format('d-m-Y') : '-'),
            'deadline' => $deadline ? $deadline->format('Y-m-d') : null,
            'deadline_display' => $deadline ? $deadline->format('d-m-Y') : '-',
            'status' => $this->normalizeStatus($p->status),
            'keterangan' => $keterangan,
            'pengawas_users' => $assignedUsersMap[$p->id] ?? [],
            'bukti' => [
                'path' => $p->bukti_path,
                'name' => $p->bukti_original_name,
                'mime' => $p->bukti_mime,
                'size' => $p->bukti_size,
                'uploaded_at' => $p->bukti_uploaded_at ? \Carbon\Carbon::parse($p->bukti_uploaded_at)->format('d-m-Y H:i') : null,
                'url' => $p->bukti_path ? asset('storage/' . $p->bukti_path) : null,
            ],
        ];

        $options = DB::table('keterangan_options')->orderBy('name')->pluck('name')->toArray();
        $users = User::orderBy('name')->get(['id', 'name', 'email'])->toArray();

        $canWrite = $this->canWriteForModule($user);
        $lpPermissions = $this->getListPengawasanPermissions($user);

        return view('list-pengawasan.show', compact('item', 'options', 'users', 'canWrite', 'lpPermissions'));
    }

    public function index(Request $request)
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        $module = Module::where('slug', 'list-pengawasan')->first();
        $hasAccess = $user->hasRole(['Admin', 'Supervisor']);

        if (!$hasAccess && $module) {
            $hasAccess = ModuleAccess::where('user_id', $user->id)
                ->where('module_id', $module->id)
                ->where('can_read', true)
                ->exists();
        }

        if (!$hasAccess) {
            abort(403, 'Unauthorized action.');
        }

        $search = $request->query('search', '');

        $pengawasQuery = DB::table('pengawas')
            ->select(
                'pengawas.id as id',
                'pengawas.name',
                'pengawas.divisi',
                'pengawas.tanggal',
                'pengawas.deadline',
                'pengawas.status',
                'pengawas.created_at',
                'pengawas.bukti_path',
                'pengawas.bukti_original_name',
                'pengawas.bukti_mime',
                'pengawas.bukti_size',
                'pengawas.bukti_uploaded_at'
            )
            ->where('pengawas.status', 'Done')
            ->orderBy('pengawas.created_at', 'desc');

        if (!$user->hasRole(['Admin', 'Supervisor'])) {
            $pengawasQuery->join('pengawas_users', 'pengawas_users.pengawas_id', '=', 'pengawas.id')
                ->where('pengawas_users.user_id', $user->id);
        }

        if ($search !== '') {
            $pengawasQuery->where('pengawas.name', 'like', '%' . $search . '%');
        }

        $pengawas = $pengawasQuery->get();
        $pengawasIds = $pengawas->pluck('id')->all();
        $assignedUsersMap = $this->getAssignedUsersMap($pengawasIds);

        $completedActivitiesMap = PengawasKegiatan::whereIn('pengawas_id', $pengawasIds)
            ->where('status', 'Selesai')
            ->orderBy('created_at', 'desc')
            ->get(['id', 'pengawas_id', 'nama_kegiatan', 'deadline', 'tanggal_mulai'])
            ->groupBy('pengawas_id')
            ->map(function ($rows) {
                return $rows->map(function ($a) {
                    $deadline = $a->deadline ? Carbon::parse($a->deadline) : null;
                    $tanggalMulai = $a->tanggal_mulai ? Carbon::parse($a->tanggal_mulai) : null;

                    return [
                        'id' => $a->id,
                        'nama' => $a->nama_kegiatan,
                        'tanggal' => $tanggalMulai ? $tanggalMulai->format('d-m-Y') : '-',
                        'deadline' => $deadline ? $deadline->format('Y-m-d') : null,
                        'deadline_display' => $deadline ? $deadline->format('d-m-Y') : '-',
                    ];
                })->values()->toArray();
            })
            ->toArray();

        $items = $pengawas->map(function ($p) use ($assignedUsersMap, $completedActivitiesMap) {
            $keterangan = DB::table('pengawas_keterangan')
                ->join('keterangan_options', 'keterangan_options.id', '=', 'pengawas_keterangan.keterangan_option_id')
                ->where('pengawas_keterangan.pengawas_id', $p->id)
                ->select(
                    'keterangan_options.name as label',
                    'pengawas_keterangan.bukti_path',
                    'pengawas_keterangan.bukti_original_name',
                    'pengawas_keterangan.bukti_mime',
                    'pengawas_keterangan.bukti_size',
                    'pengawas_keterangan.bukti_uploaded_at'
                )
                ->get()
                ->map(function ($k) {
                    return [
                        'label' => $k->label,
                        'bukti' => $k->bukti_path ? [
                            'path' => $k->bukti_path,
                            'name' => $k->bukti_original_name,
                            'mime' => $k->bukti_mime,
                            'size' => $k->bukti_size,
                            'uploaded_at' => $k->bukti_uploaded_at ? \Carbon\Carbon::parse($k->bukti_uploaded_at)->format('d-m-Y H:i') : null,
                            'url' => asset('storage/' . $k->bukti_path),
                        ] : null
                    ];
                })
                ->values()
                ->toArray();

            $createdAt = $p->created_at ? Carbon::parse($p->created_at) : null;
            $deadline = $p->deadline ? Carbon::parse($p->deadline) : null;
            $tanggalMulai = $p->tanggal ? Carbon::parse($p->tanggal) : null;

            return [
                'id' => $p->id,
                'nama' => $p->name,
                'divisi' => $p->divisi ?? '-',
                'created_at' => $createdAt ? $createdAt->toISOString() : null,
                'tanggal' => $tanggalMulai ? $tanggalMulai->format('d-m-Y') : ($createdAt ? $createdAt->format('d-m-Y') : '-'),
                'deadline' => $deadline ? $deadline->format('Y-m-d') : null,
                'deadline_display' => $deadline ? $deadline->format('d-m-Y') : '-',
                'status' => $this->normalizeStatus($p->status),
                'keterangan' => $keterangan,
                'kegiatan_selesai' => $completedActivitiesMap[$p->id] ?? [],
                'pengawas_users' => $assignedUsersMap[$p->id] ?? [],
                'bukti' => [
                    'path' => $p->bukti_path,
                    'name' => $p->bukti_original_name,
                    'mime' => $p->bukti_mime,
                    'size' => $p->bukti_size,
                    'uploaded_at' => $p->bukti_uploaded_at ? \Carbon\Carbon::parse($p->bukti_uploaded_at)->format('d-m-Y H:i') : null,
                    'url' => $p->bukti_path ? asset('storage/' . $p->bukti_path) : null,
                ],
            ];
        })->toArray();

        $options = DB::table('keterangan_options')->orderBy('name')->pluck('name')->toArray();
        $users = User::orderBy('name')->get(['id', 'name', 'email'])->toArray();

        $canWrite = $this->canWriteForModule($user);
        $lpPermissions = $this->getListPengawasanPermissions($user);

        return view('list-pengawasan.index', compact('items', 'options', 'users', 'canWrite', 'lpPermissions'));
    }

    public function store(Request $request)
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();
        if (!$this->canWriteForModule($user)) {
            return response()->json(['message' => 'Unauthorized action.'], 403);
        }
        if (!$this->getListPengawasanPermissions($user)['tambah_proyek']) {
            return response()->json(['message' => 'Unauthorized action.'], 403);
        }

        $request->merge([
            'deadline' => $request->input('deadline') ?: null,
            'tanggal' => $request->input('tanggal') ?: null,
        ]);

        $data = $request->validate([
            'nama' => ['required', 'string', 'max:255'],
            'deskripsi' => ['nullable', 'string'],
            'divisi' => ['nullable', 'string', 'max:255'],
            'tanggal' => ['nullable', 'date'],
            'deadline' => ['nullable', 'date'],
            'pengawas_users' => ['array'],
            'pengawas_users.*' => ['integer', 'exists:users,id'],
        ]);

        $status = 'OFF';

        $pengawasId = DB::table('pengawas')->insertGetId([
            'name' => $data['nama'],
            'deskripsi' => $data['deskripsi'] ?? null,
            'divisi' => $data['divisi'] ?? null,
            'tanggal' => $data['tanggal'] ?? null,
            'deadline' => $data['deadline'] ?? null,
            'status' => $status,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $userIds = collect($data['pengawas_users'] ?? [])
            ->filter()
            ->unique()
            ->values();
        $userIds->push($user->id);
        $userIds = $userIds->unique()->values();

        foreach ($userIds as $userId) {
            DB::table('pengawas_users')->updateOrInsert(
                ['pengawas_id' => $pengawasId, 'user_id' => $userId],
                ['created_at' => now(), 'updated_at' => now()]
            );
        }

        $assignedUsers = DB::table('users')
            ->whereIn('id', $userIds->all())
            ->orderBy('name')
            ->get(['id', 'name', 'email'])
            ->map(fn($u) => ['id' => $u->id, 'name' => $u->name, 'email' => $u->email])
            ->toArray();

        $this->notifyListPengawasan($user, $pengawasId, 'create', "Menambahkan proyek pengawasan: {$data['nama']}");

        $tanggalMulai = $data['tanggal'] ? Carbon::parse($data['tanggal'])->format('d-m-Y') : now()->format('d-m-Y');

        return response()->json([
            'message' => 'Pengawas berhasil ditambahkan',
            'id' => $pengawasId,
            'divisi' => $data['divisi'] ?? '-',
            'tanggal' => $tanggalMulai,
            'deadline' => $data['deadline'] ?? null,
            'status' => $status,
            'pengawas_users' => $assignedUsers,
        ]);
    }

    public function updatePengawas(Request $request, int $id)
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();
        if (!$this->canWriteForModule($user)) {
            return response()->json(['message' => 'Unauthorized action.'], 403);
        }
        if (!$this->getListPengawasanPermissions($user)['nama_proyek']) {
            return response()->json(['message' => 'Unauthorized action.'], 403);
        }
        if (!$this->canAccessPengawas($user, $id)) {
            return response()->json(['message' => 'Unauthorized action.'], 403);
        }

        $data = $request->validate([
            'nama' => ['required', 'string', 'max:255'],
            'divisi' => ['nullable', 'string', 'max:255'],
        ]);

        $updated = DB::table('pengawas')->where('id', $id)->update([
            'name' => $data['nama'],
            'divisi' => $data['divisi'] ?? null,
            'updated_at' => now(),
        ]);

        if (!$updated) {
            return response()->json(['message' => 'Pengawas tidak ditemukan'], 404);
        }

        $this->notifyListPengawasan($user, $id, 'update', "Memperbarui proyek pengawasan: {$data['nama']}");

        return response()->json(['message' => 'Pengawas berhasil diperbarui']);
    }

    public function replacePengawasUser(Request $request, int $id)
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();
        if (!$this->canWriteForModule($user)) {
            return response()->json(['message' => 'Unauthorized action.'], 403);
        }
        if (!$this->getListPengawasanPermissions($user)['pengawas']) {
            return response()->json(['message' => 'Unauthorized action.'], 403);
        }
        if (!$this->canAccessPengawas($user, $id)) {
            return response()->json(['message' => 'Unauthorized action.'], 403);
        }

        $data = $request->validate([
            'old_user_id' => ['required', 'integer', 'exists:users,id'],
            'new_user_id' => ['required', 'integer', 'exists:users,id'],
        ]);

        if ($data['old_user_id'] === $data['new_user_id']) {
            $assignedUsersMap = $this->getAssignedUsersMap([$id]);
            return response()->json([
                'message' => 'Tidak ada perubahan',
                'pengawas_users' => $assignedUsersMap[$id] ?? [],
            ]);
        }

        $exists = DB::table('pengawas_users')
            ->where('pengawas_id', $id)
            ->where('user_id', $data['old_user_id'])
            ->exists();

        if (!$exists) {
            return response()->json(['message' => 'Pengawas tidak ditemukan'], 404);
        }

        DB::table('pengawas_users')
            ->where('pengawas_id', $id)
            ->where('user_id', $data['old_user_id'])
            ->delete();

        DB::table('pengawas_users')->updateOrInsert(
            ['pengawas_id' => $id, 'user_id' => $data['new_user_id']],
            ['created_at' => now(), 'updated_at' => now()]
        );

        $projectName = $this->getPengawasName($id) ?? 'Proyek';
        $this->notifyListPengawasan($user, $id, 'update', "Mengubah pengawas proyek: {$projectName}");

        $assignedUsersMap = $this->getAssignedUsersMap([$id]);

        return response()->json([
            'message' => 'Pengawas berhasil diperbarui',
            'pengawas_users' => $assignedUsersMap[$id] ?? [],
        ]);
    }

    public function removePengawasUser(Request $request, int $id)
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();
        if (!$this->canWriteForModule($user)) {
            return response()->json(['message' => 'Unauthorized action.'], 403);
        }
        if (!$this->getListPengawasanPermissions($user)['pengawas']) {
            return response()->json(['message' => 'Unauthorized action.'], 403);
        }
        if (!$this->canAccessPengawas($user, $id)) {
            return response()->json(['message' => 'Unauthorized action.'], 403);
        }

        $data = $request->validate([
            'user_id' => ['required', 'integer', 'exists:users,id'],
        ]);

        $deleted = DB::table('pengawas_users')
            ->where('pengawas_id', $id)
            ->where('user_id', $data['user_id'])
            ->delete();

        if (!$deleted) {
            return response()->json(['message' => 'Pengawas tidak ditemukan'], 404);
        }

        $projectName = $this->getPengawasName($id) ?? 'Proyek';
        $this->notifyListPengawasan($user, $id, 'update', "Menghapus pengawas dari proyek: {$projectName}");

        $assignedUsersMap = $this->getAssignedUsersMap([$id]);

        return response()->json([
            'message' => 'Pengawas berhasil dihapus',
            'pengawas_users' => $assignedUsersMap[$id] ?? [],
        ]);
    }

    public function addPengawasUsers(Request $request, int $id)
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();
        if (!$this->canWriteForModule($user)) {
            return response()->json(['message' => 'Unauthorized action.'], 403);
        }
        if (!$this->getListPengawasanPermissions($user)['pengawas']) {
            return response()->json(['message' => 'Unauthorized action.'], 403);
        }
        if (!$this->canAccessPengawas($user, $id)) {
            return response()->json(['message' => 'Unauthorized action.'], 403);
        }

        $data = $request->validate([
            'user_ids' => ['required', 'array', 'min:1'],
            'user_ids.*' => ['integer', 'exists:users,id'],
        ]);

        $userIds = collect($data['user_ids'])
            ->filter()
            ->unique()
            ->values();

        foreach ($userIds as $userId) {
            DB::table('pengawas_users')->updateOrInsert(
                ['pengawas_id' => $id, 'user_id' => $userId],
                ['created_at' => now(), 'updated_at' => now()]
            );
        }

        $projectName = $this->getPengawasName($id) ?? 'Proyek';
        $this->notifyListPengawasan($user, $id, 'update', "Menambahkan pengawas ke proyek: {$projectName}");

        $assignedUsersMap = $this->getAssignedUsersMap([$id]);

        return response()->json([
            'message' => 'Pengawas berhasil ditambahkan',
            'pengawas_users' => $assignedUsersMap[$id] ?? [],
        ]);
    }

    public function updateKeterangan(Request $request, int $id)
    {
        try {
            /** @var \App\Models\User $user */
            $user = Auth::user();
            if (!$this->canWriteForModule($user)) {
                return response()->json(['message' => 'Unauthorized action.'], 403);
            }
            if (!$this->getListPengawasanPermissions($user)['keterangan']) {
                return response()->json(['message' => 'Unauthorized action.'], 403);
            }
            if (!$this->canAccessPengawas($user, $id)) {
                return response()->json(['message' => 'Unauthorized action.'], 403);
            }

            $data = $request->validate([
                'keterangan' => ['array'],
                'keterangan.*' => ['nullable', 'string', 'max:255'],
            ]);

            $labels = collect($data['keterangan'] ?? [])
                ->map(fn($label) => trim((string) $label))
                ->filter()
                ->unique()
                ->values();

            $permission = $this->getListPengawasanPermissions($user);

            if (!$permission['edit_keterangan'] && $labels->isNotEmpty()) {
                $existingOptionNames = DB::table('keterangan_options')
                    ->whereIn('name', $labels->all())
                    ->pluck('name')
                    ->all();

                $unknown = $labels->reject(fn($l) => in_array($l, $existingOptionNames, true));
                if ($unknown->isNotEmpty()) {
                    return response()->json(['message' => 'Unauthorized action.'], 403);
                }
            }

            $existing = DB::table('pengawas_keterangan')
                ->join('keterangan_options', 'keterangan_options.id', '=', 'pengawas_keterangan.keterangan_option_id')
                ->where('pengawas_id', $id)
                ->select('pengawas_keterangan.*', 'keterangan_options.name')
                ->get();

            $existingLabels = $existing->pluck('name')->all();
            $newLabels = $labels->all();

            // Delete removed labels
            $toDeleteLabels = array_diff($existingLabels, $newLabels);
            if (!empty($toDeleteLabels)) {
                $toDeleteIds = $existing->whereIn('name', $toDeleteLabels)->pluck('id');
                // Delete associated files
                $rowsToDelete = $existing->whereIn('id', $toDeleteIds);
                foreach ($rowsToDelete as $row) {
                    if ($row->bukti_path) {
                        Storage::disk('public')->delete($row->bukti_path);
                    }
                }
                DB::table('pengawas_keterangan')->whereIn('id', $toDeleteIds)->delete();
            }

            // Add new labels
            foreach ($newLabels as $label) {
                // Check if already exists
                if (in_array($label, $existingLabels)) {
                    // Already exists, do nothing (preserve file)
                    continue;
                }

                $opt = DB::table('keterangan_options')->where('name', $label)->first();
                if (!$opt) {
                    $optId = DB::table('keterangan_options')->insertGetId([
                        'name' => $label,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                } else {
                    $optId = $opt->id;
                }

                // Insert new (no file initially)
                DB::table('pengawas_keterangan')->insert([
                    'pengawas_id' => $id,
                    'keterangan_option_id' => $optId,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }

            // Re-fetch final list for response
            $finalList = DB::table('pengawas_keterangan')
                ->join('keterangan_options', 'keterangan_options.id', '=', 'pengawas_keterangan.keterangan_option_id')
                ->where('pengawas_keterangan.pengawas_id', $id)
                ->select(
                    'keterangan_options.name as label',
                    'pengawas_keterangan.bukti_path',
                    'pengawas_keterangan.bukti_original_name',
                    'pengawas_keterangan.bukti_mime',
                    'pengawas_keterangan.bukti_size',
                    'pengawas_keterangan.bukti_uploaded_at'
                )
                ->get()
                ->map(function ($k) {
                    return [
                        'label' => $k->label,
                        'bukti' => $k->bukti_path ? [
                            'path' => $k->bukti_path,
                            'name' => $k->bukti_original_name,
                            'mime' => $k->bukti_mime,
                            'size' => $k->bukti_size,
                            'uploaded_at' => $k->bukti_uploaded_at ? \Carbon\Carbon::parse($k->bukti_uploaded_at)->format('d-m-Y H:i') : null,
                            'url' => asset('storage/' . $k->bukti_path),
                        ] : null
                    ];
                })
                ->values()
                ->toArray();

            $projectName = $this->getPengawasName($id) ?? 'Proyek';
            $this->notifyListPengawasan($user, $id, 'update', "Memperbarui keterangan proyek: {$projectName}");

            return response()->json([
                'message' => 'Keterangan berhasil diperbarui',
                'keterangan' => $finalList,
            ]);
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Error updating keterangan: ' . $e->getMessage());
            return response()->json([
                'message' => 'Error: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function destroy(int $id)
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();
        if (!$this->canWriteForModule($user)) {
            return response()->json(['message' => 'Unauthorized action.'], 403);
        }
        if (!$this->getListPengawasanPermissions($user)['nama_proyek']) {
            return response()->json(['message' => 'Unauthorized action.'], 403);
        }
        if (!$this->canAccessPengawas($user, $id)) {
            return response()->json(['message' => 'Unauthorized action.'], 403);
        }

        $projectName = $this->getPengawasName($id) ?? 'Proyek';

        DB::table('pengawas_keterangan')->where('pengawas_id', $id)->delete();
        DB::table('pengawas')->where('id', $id)->delete();

        $this->notifyListPengawasan($user, $id, 'delete', "Menghapus proyek pengawasan: {$projectName}");

        return response()->json(['message' => 'Pengawas berhasil dihapus']);
    }

    public function updateStatus(Request $request, int $id)
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();
        if (!$this->canWriteForModule($user)) {
            return response()->json(['message' => 'Unauthorized action.'], 403);
        }
        if (!$this->getListPengawasanPermissions($user)['status']) {
            return response()->json(['message' => 'Unauthorized action.'], 403);
        }
        if (!$this->canAccessPengawas($user, $id)) {
            return response()->json(['message' => 'Unauthorized action.'], 403);
        }

        $data = $request->validate([
            'status' => ['required', 'string', Rule::in(self::ALLOWED_STATUS)],
        ]);

        $updated = DB::table('pengawas')->where('id', $id)->update([
            'status' => $data['status'],
            'updated_at' => now(),
        ]);

        if (!$updated) {
            return response()->json(['message' => 'Pengawas tidak ditemukan'], 404);
        }

        $projectName = $this->getPengawasName($id) ?? 'Proyek';
        $this->notifyListPengawasan($user, $id, 'update', "Mengubah status proyek {$projectName} menjadi {$data['status']}");

        return response()->json(['message' => 'Status berhasil diperbarui']);
    }

    public function updateDeadline(Request $request, int $id)
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();
        if (!$this->canWriteForModule($user)) {
            return response()->json(['message' => 'Unauthorized action.'], 403);
        }
        if (!$this->getListPengawasanPermissions($user)['deadline']) {
            return response()->json(['message' => 'Unauthorized action.'], 403);
        }
        if (!$this->canAccessPengawas($user, $id)) {
            return response()->json(['message' => 'Unauthorized action.'], 403);
        }

        $request->merge(['deadline' => $request->input('deadline') ?: null]);
        $data = $request->validate([
            'deadline' => ['nullable', 'date'],
        ]);

        $updated = DB::table('pengawas')->where('id', $id)->update([
            'deadline' => $data['deadline'] ?? null,
            'updated_at' => now(),
        ]);

        if (!$updated) {
            return response()->json(['message' => 'Proyek tidak ditemukan'], 404);
        }

        $projectName = $this->getPengawasName($id) ?? 'Proyek';
        $deadlineText = $data['deadline'] ?? '-';
        $this->notifyListPengawasan($user, $id, 'update', "Memperbarui deadline proyek {$projectName}: {$deadlineText}");

        return response()->json([
            'message' => 'Deadline berhasil diperbarui',
            'deadline' => $data['deadline'] ?? null,
        ]);
    }

    public function uploadBukti(Request $request, int $id)
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();
        if (!$this->canWriteForModule($user)) {
            return response()->json(['message' => 'Unauthorized action.'], 403);
        }
        if (!$this->getListPengawasanPermissions($user)['bukti']) {
            return response()->json(['message' => 'Unauthorized action.'], 403);
        }
        if (!$this->canAccessPengawas($user, $id)) {
            return response()->json(['message' => 'Unauthorized action.'], 403);
        }

        $data = $request->validate([
            'bukti' => ['required', 'file', 'max:5120', 'mimes:pdf,jpg,jpeg,png'],
        ]);

        $pengawas = DB::table('pengawas')->where('id', $id)->first();
        if (!$pengawas) {
            return response()->json(['message' => 'Proyek tidak ditemukan'], 404);
        }

        if (!empty($pengawas->bukti_path)) {
            Storage::disk('public')->delete($pengawas->bukti_path);
        }

        $file = $data['bukti'];
        $path = $file->store('pengawasan-bukti/' . $id, 'public');

        DB::table('pengawas')->where('id', $id)->update([
            'bukti_path' => $path,
            'bukti_original_name' => $file->getClientOriginalName(),
            'bukti_mime' => $file->getClientMimeType(),
            'bukti_size' => $file->getSize(),
            'bukti_uploaded_at' => now(),
            'updated_at' => now(),
        ]);

        $projectName = $pengawas->name ?? 'Proyek';
        $this->notifyListPengawasan($user, $id, 'update', "Mengunggah bukti untuk proyek: {$projectName}");

        return response()->json([
            'message' => 'Bukti berhasil diunggah',
            'bukti' => [
                'path' => $path,
                'name' => $file->getClientOriginalName(),
                'mime' => $file->getClientMimeType(),
                'size' => $file->getSize(),
                'uploaded_at' => now()->format('d-m-Y H:i'),
                'url' => asset('storage/' . $path),
            ],
        ]);
    }

    public function deleteBukti(int $id)
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();
        if (!$this->canWriteForModule($user)) {
            return response()->json(['message' => 'Unauthorized action.'], 403);
        }
        if (!$this->getListPengawasanPermissions($user)['bukti']) {
            return response()->json(['message' => 'Unauthorized action.'], 403);
        }
        if (!$this->canAccessPengawas($user, $id)) {
            return response()->json(['message' => 'Unauthorized action.'], 403);
        }

        $pengawas = DB::table('pengawas')->where('id', $id)->first();
        if (!$pengawas) {
            return response()->json(['message' => 'Proyek tidak ditemukan'], 404);
        }

        if (!empty($pengawas->bukti_path)) {
            Storage::disk('public')->delete($pengawas->bukti_path);
        }

        DB::table('pengawas')->where('id', $id)->update([
            'bukti_path' => null,
            'bukti_original_name' => null,
            'bukti_mime' => null,
            'bukti_size' => null,
            'bukti_uploaded_at' => null,
            'updated_at' => now(),
        ]);

        $projectName = $pengawas->name ?? 'Proyek';
        $this->notifyListPengawasan($user, $id, 'update', "Menghapus bukti untuk proyek: {$projectName}");

        return response()->json(['message' => 'Bukti berhasil dihapus']);
    }

    public function renameOption(Request $request)
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();
        if (!$this->canWriteForModule($user)) {
            return response()->json(['message' => 'Unauthorized action.'], 403);
        }
        if (!$this->getListPengawasanPermissions($user)['edit_keterangan']) {
            return response()->json(['message' => 'Unauthorized action.'], 403);
        }

        $data = $request->validate([
            'old_name' => ['required', 'string', 'max:255'],
            'new_name' => ['required', 'string', 'max:255'],
        ]);

        $exists = DB::table('keterangan_options')->where('name', $data['new_name'])->exists();
        if ($exists) {
            return response()->json(['message' => 'Nama keterangan sudah ada'], 422);
        }

        $updated = DB::table('keterangan_options')->where('name', $data['old_name'])->update([
            'name' => $data['new_name'],
            'updated_at' => now(),
        ]);

        if (!$updated) {
            return response()->json(['message' => 'Keterangan tidak ditemukan'], 404);
        }

        return response()->json(['message' => 'Keterangan berhasil diubah']);
    }

    public function deleteOption(Request $request)
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();
        if (!$this->canWriteForModule($user)) {
            return response()->json(['message' => 'Unauthorized action.'], 403);
        }
        if (!$this->getListPengawasanPermissions($user)['edit_keterangan']) {
            return response()->json(['message' => 'Unauthorized action.'], 403);
        }

        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
        ]);

        $opt = DB::table('keterangan_options')->where('name', $data['name'])->first();
        if (!$opt) {
            return response()->json(['message' => 'Keterangan tidak ditemukan'], 404);
        }

        DB::table('pengawas_keterangan')->where('keterangan_option_id', $opt->id)->delete();
        DB::table('keterangan_options')->where('id', $opt->id)->delete();

        return response()->json(['message' => 'Keterangan berhasil dihapus']);
    }

    public function uploadBuktiKeterangan(Request $request, int $id)
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();
        if (!$this->canWriteForModule($user)) {
            return response()->json(['message' => 'Unauthorized action.'], 403);
        }
        if (!$this->getListPengawasanPermissions($user)['bukti']) {
            return response()->json(['message' => 'Unauthorized action.'], 403);
        }
        if (!$this->canAccessPengawas($user, $id)) {
            return response()->json(['message' => 'Unauthorized action.'], 403);
        }

        $data = $request->validate([
            'label' => ['required', 'string'],
            'bukti' => ['required', 'file', 'max:5120', 'mimes:pdf,jpg,jpeg,png'],
        ]);

        $opt = DB::table('keterangan_options')->where('name', $data['label'])->first();
        if (!$opt) {
            return response()->json(['message' => 'Label keterangan tidak ditemukan'], 404);
        }

        $row = DB::table('pengawas_keterangan')
            ->where('pengawas_id', $id)
            ->where('keterangan_option_id', $opt->id)
            ->first();

        if (!$row) {
            return response()->json(['message' => 'Keterangan tidak ditemukan pada pengawas ini'], 404);
        }

        if (!empty($row->bukti_path)) {
            Storage::disk('public')->delete($row->bukti_path);
        }

        $file = $data['bukti'];
        $path = $file->store('pengawasan-bukti-keterangan/' . $id, 'public');

        DB::table('pengawas_keterangan')
            ->where('id', $row->id)
            ->update([
                'bukti_path' => $path,
                'bukti_original_name' => $file->getClientOriginalName(),
                'bukti_mime' => $file->getClientMimeType(),
                'bukti_size' => $file->getSize(),
                'bukti_uploaded_at' => now(),
                'updated_at' => now(),
            ]);

        $projectName = $this->getPengawasName($id) ?? 'Proyek';
        $this->notifyListPengawasan($user, $id, 'update', "Mengunggah bukti keterangan '{$data['label']}' untuk proyek: {$projectName}");

        return response()->json([
            'message' => 'Bukti berhasil diunggah',
            'bukti' => [
                'path' => $path,
                'name' => $file->getClientOriginalName(),
                'mime' => $file->getClientMimeType(),
                'size' => $file->getSize(),
                'uploaded_at' => now()->format('d-m-Y H:i'),
                'url' => asset('storage/' . $path),
            ],
        ]);
    }

    public function deleteBuktiKeterangan(Request $request, int $id)
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();
        if (!$this->canWriteForModule($user)) {
            return response()->json(['message' => 'Unauthorized action.'], 403);
        }
        if (!$this->getListPengawasanPermissions($user)['bukti']) {
            return response()->json(['message' => 'Unauthorized action.'], 403);
        }
        if (!$this->canAccessPengawas($user, $id)) {
            return response()->json(['message' => 'Unauthorized action.'], 403);
        }

        $data = $request->validate([
            'label' => ['required', 'string'],
        ]);

        $opt = DB::table('keterangan_options')->where('name', $data['label'])->first();
        if (!$opt) {
            return response()->json(['message' => 'Label keterangan tidak ditemukan'], 404);
        }

        $row = DB::table('pengawas_keterangan')
            ->where('pengawas_id', $id)
            ->where('keterangan_option_id', $opt->id)
            ->first();

        if (!$row) {
            return response()->json(['message' => 'Keterangan tidak ditemukan pada pengawas ini'], 404);
        }

        if (!empty($row->bukti_path)) {
            Storage::disk('public')->delete($row->bukti_path);
        }

        DB::table('pengawas_keterangan')
            ->where('id', $row->id)
            ->update([
                'bukti_path' => null,
                'bukti_original_name' => null,
                'bukti_mime' => null,
                'bukti_size' => null,
                'bukti_uploaded_at' => null,
                'updated_at' => now(),
            ]);

        $projectName = $this->getPengawasName($id) ?? 'Proyek';
        $this->notifyListPengawasan($user, $id, 'update', "Menghapus bukti keterangan '{$data['label']}' untuk proyek: {$projectName}");

        return response()->json(['message' => 'Bukti berhasil dihapus']);
    }

    public function kegiatanIndex(Request $request, int $id)
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();
        if (!$this->canAccessPengawas($user, $id)) {
            abort(403, 'Unauthorized action.');
        }

        $project = DB::table('pengawas')->where('id', $id)->first();
        if (!$project) {
            abort(404);
        }

        $activities = PengawasKegiatan::where('pengawas_id', $id)
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        $canWrite = $this->canWriteForModule($user);
        $lpPermissions = $this->getListPengawasanPermissions($user);

        return view('list-pengawasan.kegiatan-index', compact('project', 'activities', 'canWrite', 'lpPermissions'));
    }

    public function storeKegiatan(Request $request, int $id)
    {
        Log::info('storeKegiatan called', ['id' => $id, 'data' => $request->all()]);

        /** @var \App\Models\User $user */
        $user = Auth::user();
        if (!$this->canWriteForModule($user)) {
            return response()->json(['message' => 'Unauthorized action.'], 403);
        }

        $request->merge([
            'tanggal_mulai' => $request->input('tanggal_mulai') ?: null,
            'deadline' => $request->input('deadline') ?: null,
        ]);

        $data = $request->validate([
            'nama_kegiatan' => ['required', 'string', 'max:255'],
            'tanggal_mulai' => ['nullable', 'date'],
            'deadline' => ['nullable', 'date'],
            'status' => ['required', 'string'],
            'deskripsi' => ['nullable', 'string'],
        ]);

        $kegiatan = new PengawasKegiatan();
        $kegiatan->pengawas_id = $id;
        $kegiatan->nama_kegiatan = $data['nama_kegiatan'];
        $kegiatan->tanggal_mulai = $data['tanggal_mulai'] ?? null;
        $kegiatan->deadline = $data['deadline'] ?? null;
        $kegiatan->status = $data['status'];
        $kegiatan->deskripsi = $data['deskripsi'] ?? null;
        $kegiatan->save();

        $this->recalculateProjectStatus($id);

        $projectName = $this->getPengawasName($id) ?? 'Proyek';
        $this->notifyListPengawasan($user, $id, 'create', "Menambahkan kegiatan '{$kegiatan->nama_kegiatan}' pada proyek: {$projectName}");

        return response()->json(['message' => 'Kegiatan berhasil ditambahkan', 'data' => $kegiatan]);
    }

    public function showKegiatan(int $activity)
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();
        
        $act = PengawasKegiatan::findOrFail($activity);
        if (!$this->canAccessPengawas($user, $act->pengawas_id)) {
            abort(403, 'Unauthorized action.');
        }

        $keterangan = DB::table('pengawas_kegiatan_keterangan')
            ->join('keterangan_options', 'keterangan_options.id', '=', 'pengawas_kegiatan_keterangan.keterangan_option_id')
            ->where('pengawas_kegiatan_keterangan.pengawas_kegiatan_id', $act->id)
            ->select(
                'keterangan_options.name as label',
                'pengawas_kegiatan_keterangan.bukti_path',
                'pengawas_kegiatan_keterangan.bukti_original_name',
                'pengawas_kegiatan_keterangan.bukti_mime',
                'pengawas_kegiatan_keterangan.bukti_size',
                'pengawas_kegiatan_keterangan.bukti_uploaded_at'
            )
            ->get()
            ->map(function ($k) {
                return [
                    'label' => $k->label,
                    'bukti' => $k->bukti_path ? [
                        'path' => $k->bukti_path,
                        'name' => $k->bukti_original_name,
                        'mime' => $k->bukti_mime,
                        'size' => $k->bukti_size,
                        'uploaded_at' => $k->bukti_uploaded_at ? \Carbon\Carbon::parse($k->bukti_uploaded_at)->format('d-m-Y H:i') : null,
                        'url' => asset('storage/' . $k->bukti_path),
                    ] : null
                ];
            })
            ->values()
            ->toArray();

        $deadline = $act->deadline ? Carbon::parse($act->deadline) : null;
        $tanggal = $act->tanggal_mulai ? Carbon::parse($act->tanggal_mulai) : null;

        $assignedUsersMap = $this->getAssignedUsersMap([$act->pengawas_id]);

        $item = [
            'id' => $act->id,
            'pengawas_id' => $act->pengawas_id,
            'nama' => $act->nama_kegiatan,
            'deskripsi' => $act->deskripsi,
            'divisi' => '-',
            'created_at' => $act->created_at->toISOString(),
            'tanggal' => $tanggal ? $tanggal->format('d-m-Y') : '-',
            'deadline' => $deadline ? $deadline->format('Y-m-d') : null,
            'deadline_display' => $deadline ? $deadline->format('d-m-Y') : '-',
            'status' => $act->status,
            'keterangan' => $keterangan,
            'pengawas_users' => $assignedUsersMap[$act->pengawas_id] ?? [],
            'bukti' => [
                'path' => $act->bukti_path,
                'name' => $act->bukti_original_name,
                'mime' => $act->bukti_mime,
                'size' => $act->bukti_size,
                'uploaded_at' => $act->bukti_uploaded_at ? \Carbon\Carbon::parse($act->bukti_uploaded_at)->format('d-m-Y H:i') : null,
                'url' => $act->bukti_path ? asset('storage/' . $act->bukti_path) : null,
            ],
        ];

        $options = DB::table('keterangan_options')->orderBy('name')->pluck('name')->toArray();
        $users = User::orderBy('name')->get(['id', 'name', 'email'])->toArray();
        $canWrite = $this->canWriteForModule($user);
        $lpPermissions = $this->getListPengawasanPermissions($user);

        return view('list-pengawasan.show-kegiatan', compact('item', 'options', 'users', 'canWrite', 'lpPermissions'));
    }

    public function updateKegiatan(Request $request, int $activity)
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();
        if (!$this->canWriteForModule($user)) return response()->json(['message' => 'Unauthorized action.'], 403);

        $act = PengawasKegiatan::findOrFail($activity);
        if (!$this->canAccessPengawas($user, $act->pengawas_id)) return response()->json(['message' => 'Unauthorized action.'], 403);

        $data = $request->validate([
            'nama_kegiatan' => ['required', 'string', 'max:255'],
            'deskripsi' => ['nullable', 'string'],
        ]);

        $act->nama_kegiatan = $data['nama_kegiatan'];
        $act->deskripsi = $data['deskripsi'] ?? null;
        $act->save();

        $this->recalculateProjectStatus($act->pengawas_id);

        $projectName = $this->getPengawasName($act->pengawas_id) ?? 'Proyek';
        $this->notifyListPengawasan($user, $act->pengawas_id, 'update', "Memperbarui kegiatan '{$act->nama_kegiatan}' pada proyek: {$projectName}");

        return response()->json(['message' => 'Kegiatan berhasil diperbarui']);
    }

    public function destroyKegiatan(int $activity)
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();
        if (!$this->canWriteForModule($user)) return response()->json(['message' => 'Unauthorized action.'], 403);

        $act = PengawasKegiatan::findOrFail($activity);
        if (!$this->canAccessPengawas($user, $act->pengawas_id)) return response()->json(['message' => 'Unauthorized action.'], 403);

        $name = $act->nama_kegiatan;
        $pid = $act->pengawas_id;
        $act->delete();

        $this->recalculateProjectStatus($pid);

        $projectName = $this->getPengawasName($pid) ?? 'Proyek';
        $this->notifyListPengawasan($user, $pid, 'delete', "Menghapus kegiatan '{$name}' pada proyek: {$projectName}");

        return response()->json(['message' => 'Kegiatan berhasil dihapus']);
    }

    public function updateStatusKegiatan(Request $request, int $activity)
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();
        if (!$this->canWriteForModule($user)) return response()->json(['message' => 'Unauthorized action.'], 403);

        $act = PengawasKegiatan::findOrFail($activity);
        if (!$this->canAccessPengawas($user, $act->pengawas_id)) return response()->json(['message' => 'Unauthorized action.'], 403);

        $data = $request->validate(['status' => ['required', 'string']]);
        $act->status = $data['status'];
        $act->save();

        $this->recalculateProjectStatus($act->pengawas_id);

        return response()->json(['message' => 'Status berhasil diperbarui']);
    }

    public function updateDeadlineKegiatan(Request $request, int $activity)
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();
        if (!$this->canWriteForModule($user)) return response()->json(['message' => 'Unauthorized action.'], 403);

        $act = PengawasKegiatan::findOrFail($activity);
        if (!$this->canAccessPengawas($user, $act->pengawas_id)) return response()->json(['message' => 'Unauthorized action.'], 403);

        $request->merge(['deadline' => $request->input('deadline') ?: null]);
        $data = $request->validate(['deadline' => ['nullable', 'date']]);
        $act->deadline = $data['deadline'];
        $act->save();

        return response()->json([
            'message' => 'Deadline berhasil diperbarui',
            'deadline' => $act->deadline ? Carbon::parse($act->deadline)->format('Y-m-d') : null
        ]);
    }

    public function uploadBuktiKegiatan(Request $request, int $activity)
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();
        if (!$this->canWriteForModule($user)) {
            return response()->json(['message' => 'Unauthorized action.'], 403);
        }

        $act = PengawasKegiatan::findOrFail($activity);
        if (!$this->canAccessPengawas($user, $act->pengawas_id)) {
            return response()->json(['message' => 'Unauthorized action.'], 403);
        }

        if (!$this->getListPengawasanPermissions($user)['bukti']) {
            return response()->json(['message' => 'Unauthorized action.'], 403);
        }

        $data = $request->validate(['bukti' => ['required', 'file', 'max:5120', 'mimes:pdf,jpg,jpeg,png']]);
        
        if ($act->bukti_path) Storage::disk('public')->delete($act->bukti_path);

        $file = $data['bukti'];
        $path = $file->store('kegiatan-bukti/' . $act->id, 'public');

        $act->bukti_path = $path;
        $act->bukti_original_name = $file->getClientOriginalName();
        $act->bukti_mime = $file->getClientMimeType();
        $act->bukti_size = $file->getSize();
        $act->bukti_uploaded_at = now();
        $act->save();

        return response()->json([
            'message' => 'Bukti berhasil diunggah',
            'bukti' => [
                'path' => $path,
                'name' => $file->getClientOriginalName(),
                'mime' => $file->getClientMimeType(),
                'size' => $file->getSize(),
                'uploaded_at' => now()->format('d-m-Y H:i'),
                'url' => asset('storage/' . $path),
            ],
        ]);
    }

    public function deleteBuktiKegiatan(int $activity)
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();
        if (!$this->canWriteForModule($user)) {
            return response()->json(['message' => 'Unauthorized action.'], 403);
        }

        $act = PengawasKegiatan::findOrFail($activity);
        if (!$this->canAccessPengawas($user, $act->pengawas_id)) {
            return response()->json(['message' => 'Unauthorized action.'], 403);
        }

        if (!$this->getListPengawasanPermissions($user)['bukti']) {
            return response()->json(['message' => 'Unauthorized action.'], 403);
        }

        if ($act->bukti_path) Storage::disk('public')->delete($act->bukti_path);

        $act->bukti_path = null;
        $act->bukti_original_name = null;
        $act->bukti_mime = null;
        $act->bukti_size = null;
        $act->bukti_uploaded_at = null;
        $act->save();

        return response()->json(['message' => 'Bukti berhasil dihapus']);
    }

    public function updateKeteranganKegiatan(Request $request, int $activity)
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();
        if (!$this->canWriteForModule($user)) {
            return response()->json(['message' => 'Unauthorized action.'], 403);
        }

        $act = PengawasKegiatan::findOrFail($activity);
        if (!$this->canAccessPengawas($user, $act->pengawas_id)) {
            return response()->json(['message' => 'Unauthorized action.'], 403);
        }

        if (!$this->getListPengawasanPermissions($user)['keterangan']) {
            return response()->json(['message' => 'Unauthorized action.'], 403);
        }

        $data = $request->validate([
            'keterangan' => ['array'],
            'keterangan.*' => ['nullable', 'string', 'max:255'],
        ]);

        $labels = collect($data['keterangan'] ?? [])
            ->map(fn($l) => trim((string) $l))
            ->filter()
            ->unique()
            ->values();

        $permission = $this->getListPengawasanPermissions($user);
        if (!$permission['edit_keterangan'] && $labels->isNotEmpty()) {
            $existingOptionNames = DB::table('keterangan_options')
                ->whereIn('name', $labels->all())
                ->pluck('name')
                ->all();

            $unknown = $labels->reject(fn($l) => in_array($l, $existingOptionNames, true));
            if ($unknown->isNotEmpty()) {
                return response()->json(['message' => 'Unauthorized action.'], 403);
            }
        }

        $existing = DB::table('pengawas_kegiatan_keterangan')
            ->join('keterangan_options', 'keterangan_options.id', '=', 'pengawas_kegiatan_keterangan.keterangan_option_id')
            ->where('pengawas_kegiatan_id', $act->id)
            ->select('pengawas_kegiatan_keterangan.*', 'keterangan_options.name')
            ->get();

        $existingLabels = $existing->pluck('name')->all();
        $newLabels = $labels->values()->all();

        // Delete removed
        $toDeleteLabels = array_diff($existingLabels, $newLabels);
        if (!empty($toDeleteLabels)) {
            $toDeleteIds = $existing->whereIn('name', $toDeleteLabels)->pluck('id');
            foreach ($existing->whereIn('id', $toDeleteIds) as $row) {
                if ($row->bukti_path) Storage::disk('public')->delete($row->bukti_path);
            }
            DB::table('pengawas_kegiatan_keterangan')->whereIn('id', $toDeleteIds)->delete();
        }

        // Add new
        foreach ($newLabels as $label) {
            if (in_array($label, $existingLabels, true)) {
                continue;
            }
            
            $opt = DB::table('keterangan_options')->where('name', $label)->first();
            if (!$opt) {
                $optId = DB::table('keterangan_options')->insertGetId(['name' => $label, 'created_at' => now(), 'updated_at' => now()]);
            } else {
                $optId = $opt->id;
            }
            
            DB::table('pengawas_kegiatan_keterangan')->insert([
                'pengawas_kegiatan_id' => $act->id,
                'keterangan_option_id' => $optId,
                'created_at' => now(),
                'updated_at' => now()
            ]);
        }

        $finalList = DB::table('pengawas_kegiatan_keterangan')
            ->join('keterangan_options', 'keterangan_options.id', '=', 'pengawas_kegiatan_keterangan.keterangan_option_id')
            ->where('pengawas_kegiatan_id', $act->id)
            ->select(
                'keterangan_options.name as label',
                'pengawas_kegiatan_keterangan.bukti_path',
                'pengawas_kegiatan_keterangan.bukti_original_name',
                'pengawas_kegiatan_keterangan.bukti_mime',
                'pengawas_kegiatan_keterangan.bukti_size',
                'pengawas_kegiatan_keterangan.bukti_uploaded_at'
            )
            ->get()
            ->map(function ($k) {
                return [
                    'label' => $k->label,
                    'bukti' => $k->bukti_path ? [
                        'path' => $k->bukti_path,
                        'name' => $k->bukti_original_name,
                        'mime' => $k->bukti_mime,
                        'size' => $k->bukti_size,
                        'uploaded_at' => $k->bukti_uploaded_at ? \Carbon\Carbon::parse($k->bukti_uploaded_at)->format('d-m-Y H:i') : null,
                        'url' => asset('storage/' . $k->bukti_path),
                    ] : null,
                ];
            })
            ->values()
            ->toArray();

        return response()->json([
            'message' => 'Keterangan berhasil diperbarui',
            'keterangan' => $finalList,
        ]);
    }

    public function uploadBuktiKeteranganKegiatan(Request $request, int $activity)
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();
        if (!$this->canWriteForModule($user)) {
            return response()->json(['message' => 'Unauthorized action.'], 403);
        }

        $act = PengawasKegiatan::findOrFail($activity);
        if (!$this->canAccessPengawas($user, $act->pengawas_id)) {
            return response()->json(['message' => 'Unauthorized action.'], 403);
        }

        if (!$this->getListPengawasanPermissions($user)['bukti']) {
            return response()->json(['message' => 'Unauthorized action.'], 403);
        }

        $data = $request->validate([
            'label' => ['required', 'string'],
            'bukti' => ['required', 'file', 'max:5120', 'mimes:pdf,jpg,jpeg,png'],
        ]);

        $opt = DB::table('keterangan_options')->where('name', $data['label'])->first();
        if (!$opt) return response()->json(['message' => 'Label keterangan tidak ditemukan'], 404);

        $row = DB::table('pengawas_kegiatan_keterangan')
            ->where('pengawas_kegiatan_id', $act->id)
            ->where('keterangan_option_id', $opt->id)
            ->first();

        if (!$row) return response()->json(['message' => 'Keterangan tidak ditemukan pada kegiatan ini'], 404);

        if (!empty($row->bukti_path)) Storage::disk('public')->delete($row->bukti_path);

        $file = $data['bukti'];
        $path = $file->store('kegiatan-bukti-keterangan/' . $act->id, 'public');

        DB::table('pengawas_kegiatan_keterangan')
            ->where('id', $row->id)
            ->update([
                'bukti_path' => $path,
                'bukti_original_name' => $file->getClientOriginalName(),
                'bukti_mime' => $file->getClientMimeType(),
                'bukti_size' => $file->getSize(),
                'bukti_uploaded_at' => now(),
                'updated_at' => now(),
            ]);

        return response()->json([
            'message' => 'Bukti berhasil diunggah',
            'bukti' => [
                'path' => $path,
                'name' => $file->getClientOriginalName(),
                'mime' => $file->getClientMimeType(),
                'size' => $file->getSize(),
                'uploaded_at' => now()->format('d-m-Y H:i'),
                'url' => asset('storage/' . $path),
            ],
        ]);
    }

    public function deleteBuktiKeteranganKegiatan(Request $request, int $activity)
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();
        if (!$this->canWriteForModule($user)) {
            return response()->json(['message' => 'Unauthorized action.'], 403);
        }

        $act = PengawasKegiatan::findOrFail($activity);
        if (!$this->canAccessPengawas($user, $act->pengawas_id)) {
            return response()->json(['message' => 'Unauthorized action.'], 403);
        }

        if (!$this->getListPengawasanPermissions($user)['bukti']) {
            return response()->json(['message' => 'Unauthorized action.'], 403);
        }

        $data = $request->validate(['label' => ['required', 'string']]);

        $opt = DB::table('keterangan_options')->where('name', $data['label'])->first();
        if (!$opt) return response()->json(['message' => 'Label keterangan tidak ditemukan'], 404);

        $row = DB::table('pengawas_kegiatan_keterangan')
            ->where('pengawas_kegiatan_id', $act->id)
            ->where('keterangan_option_id', $opt->id)
            ->first();

        if (!$row) return response()->json(['message' => 'Keterangan tidak ditemukan pada kegiatan ini'], 404);

        if (!empty($row->bukti_path)) Storage::disk('public')->delete($row->bukti_path);

        DB::table('pengawas_kegiatan_keterangan')
            ->where('id', $row->id)
            ->update([
                'bukti_path' => null,
                'bukti_original_name' => null,
                'bukti_mime' => null,
                'bukti_size' => null,
                'bukti_uploaded_at' => null,
                'updated_at' => now(),
            ]);

        return response()->json(['message' => 'Bukti berhasil dihapus']);
    }
}
