<?php

namespace App\Http\Controllers;

use App\Models\Module;
use App\Models\ModuleAccess;
use App\Models\User;
use App\Models\MasterJenisPipa;
use App\Models\TemplateKegiatan;
use App\Models\PengawasKegiatan;
use App\Notifications\SystemNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;
use Spatie\Permission\Exceptions\RoleDoesNotExist;

class ListPengawasanController extends Controller
{
    private function normalizeStatus(?string $status): string
    {
        if (!$status || $status === 'OFF') {
            return 'Belum Dikerjakan';
        }

        if ($status === 'Active' || $status === 'On Progress') {
            return 'Sedang Dikerjakan';
        }

        if ($status === 'Done') {
            return 'Selesai';
        }

        if ($status === 'Belum Dimulai') {
            return 'Belum Dikerjakan';
        }

        if ($status === 'Sedang Berjalan') {
            return 'Sedang Dikerjakan';
        }

        return $status;
    }

    private function canWriteForModule($user): bool
    {
        // Fallback: Check roles directly from collection to bypass potential cache issues
        if ($user->roles->whereIn('name', ['Admin', 'Supervisor'])->isNotEmpty()) {
            return true;
        }

        if ($user->hasRole(['Admin', 'Supervisor'])) {
            return true;
        }

        $module = Module::where('slug', 'list-pengawasan')->first();
        if (!$module) {
            return false;
        }

        $access = ModuleAccess::where('user_id', $user->id)
            ->where('module_id', $module->id)
            ->first();

        if (!$access) {
            return false;
        }

        return (bool) $access->can_write;
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
        // Default Full Access for Admin/Supervisor
        $fullAccess = [
            'tambah_proyek' => true,
            'nama_proyek' => true,
            'status_proyek' => true,
            'bulk_proyek' => true,
            'tambah_kegiatan' => true,
            'hapus_kegiatan' => true,
            'bulk_kegiatan' => true,
            'filter_status_kegiatan' => true,
            'kelola_jenis_pekerjaan' => true,
            'kelola_template_bulk' => true,
            'tambah_keterangan' => true,
            'edit_keterangan' => true,
            'tambah_pengawasan' => true,
            'edit_pengawasan' => true,
            'bukti' => true,
        ];

        // Fallback: Check roles directly from collection to bypass potential cache issues
        if ($user->roles->whereIn('name', ['Admin', 'Supervisor'])->isNotEmpty()) {
            return $fullAccess;
        }

        if ($user->hasRole(['Admin', 'Supervisor'])) {
            return $fullAccess;
        }

        $module = Module::where('slug', 'list-pengawasan')->first();
        
        $default = [
            'tambah_proyek' => false,
            'nama_proyek' => false,
            'status_proyek' => false,
            'bulk_proyek' => false,
            'tambah_kegiatan' => false,
            'hapus_kegiatan' => false,
            'bulk_kegiatan' => false,
            'filter_status_kegiatan' => false,
            'kelola_jenis_pekerjaan' => false,
            'kelola_template_bulk' => false,
            'tambah_keterangan' => false,
            'edit_keterangan' => false,
            'tambah_pengawasan' => false,
            'edit_pengawasan' => false,
            'bukti' => false,
        ];

        if (!$module) {
            return $default;
        }

        $access = ModuleAccess::where('user_id', $user->id)
            ->where('module_id', $module->id)
            ->first();

        if (!$access || !is_array($access->extra_permissions['list_pengawasan'] ?? null)) {
            return $default;
        }

        return array_merge($default, $access->extra_permissions['list_pengawasan']);
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
                return in_array($a->status, ['Sedang Berjalan', 'Terlambat', 'Sedang Dikerjakan'], true);
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
            ->orderBy('pengawas.created_at', 'asc');

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

        $latestActivitiesMap = PengawasKegiatan::whereIn('pengawas_id', $pengawasIds)
            ->orderBy('created_at', 'desc')
            ->get(['id', 'pengawas_id', 'nama_kegiatan', 'created_at'])
            ->groupBy('pengawas_id')
            ->map(function ($rows) {
                return $rows->map(function ($a) {
                    return [
                        'id' => $a->id,
                        'nama' => $a->nama_kegiatan,
                    ];
                })->values()->toArray();
            })
            ->toArray();

        $items = $pengawas->map(function ($p) use ($assignedUsersMap, $latestActivitiesMap) {
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
                'kegiatan_terbaru' => $latestActivitiesMap[$p->id] ?? [],
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

        $data = $request->validate([
            'nama' => ['required', 'string', 'max:255'],
            'divisi' => ['nullable', 'string', 'max:255'],
            'pengawas_users' => ['array'],
            'pengawas_users.*' => ['integer', 'exists:users,id'],
        ]);

        // Check for duplicate name (case-insensitive)
        $exists = DB::table('pengawas')
            ->whereRaw('LOWER(name) = ?', [strtolower($data['nama'])])
            ->exists();

        if ($exists) {
            return response()->json(['message' => 'Proyek dengan nama tersebut sudah ada.'], 422);
        }

        $status = 'OFF';
        $tanggalMulai = now();

        $pengawasId = DB::table('pengawas')->insertGetId([
            'name' => $data['nama'],
            'divisi' => $data['divisi'] ?? null,
            'tanggal' => $tanggalMulai->toDateString(),
            'deadline' => null,
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

        return response()->json([
            'message' => 'Pengawas berhasil ditambahkan',
            'id' => $pengawasId,
            'divisi' => $data['divisi'] ?? '-',
            'tanggal' => $tanggalMulai->format('d-m-Y'),
            'deadline' => null,
            'status' => $this->normalizeStatus($status),
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
        if (!$this->canAccessPengawas($user, $id)) {
            return response()->json(['message' => 'Unauthorized action.'], 403);
        }

        $data = $request->validate([
            'nama' => ['nullable', 'string', 'max:255'],
            'divisi' => ['nullable', 'string', 'max:255'],
            'status' => ['nullable', 'string', 'in:Belum Dikerjakan,Sedang Dikerjakan,Selesai,Belum Dimulai,Sedang Berjalan,OFF,On Progress,Done,Active'],
        ]);

        $perms = $this->getListPengawasanPermissions($user);
        $wantsNameOrDivisi = array_key_exists('nama', $data) || array_key_exists('divisi', $data);
        $wantsStatus = array_key_exists('status', $data);

        if (($wantsNameOrDivisi && !$perms['nama_proyek']) || ($wantsStatus && !$perms['status_proyek'])) {
            return response()->json(['message' => 'Unauthorized action.'], 403);
        }

        if (
            (!$wantsNameOrDivisi || ((string) ($data['nama'] ?? '') === '' && $data['divisi'] === null))
            && (!$wantsStatus || $data['status'] === null || $data['status'] === '')
        ) {
            return response()->json(['message' => 'Tidak ada perubahan'], 422);
        }

        $updatePayload = ['updated_at' => now()];
        if ($perms['nama_proyek']) {
            if (array_key_exists('nama', $data) && $data['nama'] !== null && $data['nama'] !== '') {
                $updatePayload['name'] = $data['nama'];
            }
            if (array_key_exists('divisi', $data)) {
                $updatePayload['divisi'] = $data['divisi'] ?? null;
            }
        }

        if (array_key_exists('status', $data) && $data['status'] !== null && $data['status'] !== '') {
            $incoming = $data['status'];
            $dbStatus = match ($incoming) {
                'Belum Dikerjakan', 'Belum Dimulai', 'OFF' => 'OFF',
                'Sedang Dikerjakan', 'Sedang Berjalan', 'On Progress', 'Active' => 'On Progress',
                'Selesai', 'Done' => 'Done',
                default => null,
            };

            if ($dbStatus !== null) {
                $updatePayload['status'] = $dbStatus;
            }
        }

        $updated = DB::table('pengawas')->where('id', $id)->update($updatePayload);

        if (!$updated) {
            return response()->json(['message' => 'Pengawas tidak ditemukan'], 404);
        }

        $projectName = $this->getPengawasName($id) ?? 'Proyek';
        $this->notifyListPengawasan($user, $id, 'update', "Memperbarui proyek pengawasan: {$projectName}");

        return response()->json(['message' => 'Pengawas berhasil diperbarui']);
    }

    public function replacePengawasUser(Request $request, int $id)
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();
        if (!$this->canWriteForModule($user)) {
            return response()->json(['message' => 'Unauthorized action.'], 403);
        }
        if (!$this->getListPengawasanPermissions($user)['edit_pengawasan']) {
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
        if (!$this->getListPengawasanPermissions($user)['edit_pengawasan']) {
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
        if (!$this->getListPengawasanPermissions($user)['tambah_pengawasan']) {
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
            if (!$this->getListPengawasanPermissions($user)['bukti']) {
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

            if (!$permission['tambah_keterangan'] && !$permission['edit_keterangan'] && $labels->isNotEmpty()) {
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

    public function bulkUpdatePengawas(Request $request)
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();
        if (!$this->canWriteForModule($user)) {
            return response()->json(['message' => 'Unauthorized action.'], 403);
        }
        if (!$this->getListPengawasanPermissions($user)['bulk_proyek']) {
            return response()->json(['message' => 'Unauthorized action.'], 403);
        }

        $data = $request->validate([
            'ids' => ['required', 'array', 'min:1'],
            'ids.*' => ['integer'],
            'status' => ['required', 'string', 'in:Belum Dikerjakan,Sedang Dikerjakan,Selesai,Belum Dimulai,Sedang Berjalan,OFF,On Progress,Done,Active'],
        ]);

        $ids = collect($data['ids'])->filter()->unique()->values()->all();
        $projects = DB::table('pengawas')->whereIn('id', $ids)->get(['id', 'name']);

        if ($projects->count() !== count($ids)) {
            return response()->json(['message' => 'Sebagian proyek tidak ditemukan.'], 404);
        }

        foreach ($ids as $pid) {
            if (!$this->canAccessPengawas($user, (int) $pid)) {
                return response()->json(['message' => 'Unauthorized action.'], 403);
            }
        }

        $incoming = $data['status'];
        $dbStatus = match ($incoming) {
            'Belum Dikerjakan', 'Belum Dimulai', 'OFF' => 'OFF',
            'Sedang Dikerjakan', 'Sedang Berjalan', 'On Progress', 'Active' => 'On Progress',
            'Selesai', 'Done' => 'Done',
            default => null,
        };

        if ($dbStatus === null) {
            return response()->json(['message' => 'Status tidak valid.'], 422);
        }

        DB::table('pengawas')->whereIn('id', $ids)->update([
            'status' => $dbStatus,
            'updated_at' => now(),
        ]);

        $this->notifyListPengawasan($user, (int) $ids[0], 'update', 'Memperbarui status proyek (bulk)');

        return response()->json(['message' => 'Status proyek berhasil diperbarui']);
    }

    public function bulkDeletePengawas(Request $request)
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();
        if (!$this->canWriteForModule($user)) {
            return response()->json(['message' => 'Unauthorized action.'], 403);
        }
        if (!$this->getListPengawasanPermissions($user)['bulk_proyek']) {
            return response()->json(['message' => 'Unauthorized action.'], 403);
        }

        $data = $request->validate([
            'ids' => ['required', 'array', 'min:1'],
            'ids.*' => ['integer'],
        ]);

        $ids = collect($data['ids'])->filter()->unique()->values()->all();
        $projects = DB::table('pengawas')->whereIn('id', $ids)->get(['id', 'name']);

        if ($projects->count() !== count($ids)) {
            return response()->json(['message' => 'Sebagian proyek tidak ditemukan.'], 404);
        }

        foreach ($ids as $pid) {
            if (!$this->canAccessPengawas($user, (int) $pid)) {
                return response()->json(['message' => 'Unauthorized action.'], 403);
            }
        }

        DB::transaction(function () use ($ids) {
            DB::table('pengawas_kegiatan')->whereIn('pengawas_id', $ids)->delete();
            DB::table('pengawas_keterangan')->whereIn('pengawas_id', $ids)->delete();
            DB::table('pengawas_users')->whereIn('pengawas_id', $ids)->delete();
            DB::table('pengawas')->whereIn('id', $ids)->delete();
        });

        $this->notifyListPengawasan($user, (int) $ids[0], 'delete', 'Menghapus proyek (bulk)');

        return response()->json(['message' => 'Proyek berhasil dihapus']);
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
            'bukti' => ['required', 'file', 'max:10240', 'mimes:pdf,jpg,jpeg,png'],
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
            'bukti' => ['required', 'file', 'max:10240', 'mimes:pdf,jpg,jpeg,png'],
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

        $pipeTypes = MasterJenisPipa::orderBy('id')->get();
        $defaultType = $pipeTypes->isNotEmpty() ? $pipeTypes->first()->name : 'Pipa Baja';

        $type = $request->query('type', $defaultType);
        if ($pipeTypes->isNotEmpty() && !$pipeTypes->contains('name', $type)) {
            $type = $defaultType;
        }
        $search = $request->query('search');
        $status = $request->query('status');
        $lpPermissions = $this->getListPengawasanPermissions($user);
        if (empty($lpPermissions['filter_status_kegiatan'])) {
            $status = null;
        }

        $activities = PengawasKegiatan::where('pengawas_id', $id)
            ->when($type, function($q) use ($type) {
                $q->where('jenis_pipa', $type);
            })
            ->when($search, function($q) use ($search) {
                $q->where('nama_kegiatan', 'like', "%{$search}%");
            })
            ->when($status, function ($q) use ($status) {
                if ($status === 'Belum Dikerjakan') {
                    $q->whereIn('status', ['Belum Dikerjakan', 'Belum Dimulai', 'OFF']);
                    return;
                }
                if ($status === 'Sedang Dikerjakan') {
                    $q->whereIn('status', ['Sedang Dikerjakan', 'Sedang Berjalan', 'On Progress', 'Active']);
                    return;
                }
                if ($status === 'Selesai') {
                    $q->where('status', 'Selesai');
                    return;
                }
            })
            ->orderBy('created_at', 'asc')
            ->paginate(10);
        
        $activities->appends(['type' => $type, 'search' => $search, 'status' => $status]);

        $canWrite = $this->canWriteForModule($user);

        return view('list-pengawasan.kegiatan-index', compact('project', 'activities', 'canWrite', 'lpPermissions', 'type', 'search', 'status', 'pipeTypes'));
    }

    public function storeKegiatan(Request $request, int $id)
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();
        if (!$this->canWriteForModule($user)) {
            return response()->json(['message' => 'Unauthorized action.'], 403);
        }

        $data = $request->validate([
            'nama_kegiatan' => ['required', 'string', 'max:255'],
            'deskripsi' => ['nullable', 'string'],
            'jenis_pipa' => ['required', 'string', 'max:255', 'exists:master_jenis_pipas,name'],
        ]);

        // Check for duplicate name (case-insensitive)
        $exists = PengawasKegiatan::where('pengawas_id', $id)
            ->whereRaw('LOWER(nama_kegiatan) = ?', [strtolower($data['nama_kegiatan'])])
            ->where('jenis_pipa', $data['jenis_pipa'])
            ->exists();

        if ($exists) {
            return response()->json(['message' => 'Kegiatan dengan nama tersebut sudah ada dalam proyek ini.'], 422);
        }

        $kegiatan = new PengawasKegiatan();
        $kegiatan->pengawas_id = $id;
        $kegiatan->nama_kegiatan = $data['nama_kegiatan'];
        $kegiatan->jenis_pipa = $data['jenis_pipa'];
        $kegiatan->tanggal_mulai = now()->toDateString();
        $kegiatan->deadline = null;
        $kegiatan->status = 'Belum Dimulai';
        $kegiatan->deskripsi = $data['deskripsi'] ?? null;
        $kegiatan->save();

        $this->recalculateProjectStatus($id);

        $projectName = $this->getPengawasName($id) ?? 'Proyek';
        $this->notifyListPengawasan($user, $id, 'create', "Menambahkan kegiatan '{$kegiatan->nama_kegiatan}' pada proyek: {$projectName}");

        return response()->json(['message' => 'Kegiatan berhasil ditambahkan', 'data' => $kegiatan]);
    }

    public function storeBulkKegiatan(Request $request, int $id)
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();
        if (!$this->canWriteForModule($user)) {
            return response()->json(['message' => 'Unauthorized action.'], 403);
        }
        $perms = $this->getListPengawasanPermissions($user);
        if (!$perms['tambah_kegiatan'] || !$perms['bulk_kegiatan']) {
            return response()->json(['message' => 'Unauthorized action.'], 403);
        }
        if (!$this->canAccessPengawas($user, $id)) {
            return response()->json(['message' => 'Unauthorized action.'], 403);
        }

        $data = $request->validate([
            'kegiatan' => ['required', 'array'],
            'kegiatan.*' => ['required', 'string', 'max:255'],
            'jenis_pipa' => ['required', 'string', 'max:255', 'exists:master_jenis_pipas,name'],
        ]);

        $duplicates = [];
        foreach ($data['kegiatan'] as $namaKegiatan) {
            $exists = PengawasKegiatan::where('pengawas_id', $id)
                ->whereRaw('LOWER(nama_kegiatan) = ?', [strtolower($namaKegiatan)])
                ->where('jenis_pipa', $data['jenis_pipa'])
                ->exists();
            if ($exists) {
                $duplicates[] = $namaKegiatan;
            }
        }

        if (!empty($duplicates)) {
            return response()->json([
                'message' => 'Beberapa kegiatan sudah ada dalam proyek ini.',
                'duplicates' => $duplicates
            ], 422);
        }

        $inserted = [];
        foreach ($data['kegiatan'] as $namaKegiatan) {
            $kegiatan = new PengawasKegiatan();
            $kegiatan->pengawas_id = $id;
            $kegiatan->nama_kegiatan = $namaKegiatan;
            $kegiatan->jenis_pipa = $data['jenis_pipa'];
            $kegiatan->tanggal_mulai = now()->toDateString();
            $kegiatan->deadline = null;
            $kegiatan->status = 'Belum Dimulai';
            $kegiatan->deskripsi = null;
            $kegiatan->save();
            $inserted[] = $kegiatan;
        }

        $this->recalculateProjectStatus($id);

        $projectName = $this->getPengawasName($id) ?? 'Proyek';
        $count = count($inserted);
        $this->notifyListPengawasan($user, $id, 'create', "Menambahkan {$count} kegiatan bulk ({$data['jenis_pipa']}) pada proyek: {$projectName}");

        return response()->json(['message' => "{$count} Kegiatan berhasil ditambahkan", 'data' => $inserted]);
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
            ->where('pengawas_kegiatan_keterangan.pengawas_kegiatan_id', $act->id)
            ->select(
                'pengawas_kegiatan_keterangan.label',
                'pengawas_kegiatan_keterangan.bukti_path',
                'pengawas_kegiatan_keterangan.bukti_original_name',
                'pengawas_kegiatan_keterangan.bukti_mime',
                'pengawas_kegiatan_keterangan.bukti_size',
                'pengawas_kegiatan_keterangan.bukti_uploaded_at'
            )
            ->orderBy('pengawas_kegiatan_keterangan.id')
            ->get()
            ->map(function ($k) {
                // Skip if marked as inactive
                if (str_starts_with($k->label, '__inactive__')) {
                    return null;
                }
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
            ->filter()
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

        $options = DB::table('pengawas_kegiatan_keterangan')
            ->where('pengawas_kegiatan_keterangan.pengawas_kegiatan_id', $act->id)
            ->distinct()
            ->orderBy('pengawas_kegiatan_keterangan.label')
            ->pluck('pengawas_kegiatan_keterangan.label')
            ->filter(fn($v) => $v !== null && $v !== '')
            ->map(fn($l) => str_replace('__inactive__', '', $l)) // Clean prefix
            ->unique()
            ->values()
            ->toArray();
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
            'jenis_pipa' => ['nullable', 'string', 'max:255', 'exists:master_jenis_pipas,name'],
            'status' => ['nullable', 'string', 'in:Belum Dimulai,Belum Dikerjakan,Sedang Dikerjakan,Selesai'],
        ]);

        $act->nama_kegiatan = $data['nama_kegiatan'];
        $act->deskripsi = $data['deskripsi'] ?? null;
        if (isset($data['jenis_pipa'])) {
            $act->jenis_pipa = $data['jenis_pipa'];
        }
        if (isset($data['status'])) {
            $act->status = $data['status'];
        }
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
        if (!$this->getListPengawasanPermissions($user)['hapus_kegiatan']) {
            return response()->json(['message' => 'Unauthorized action.'], 403);
        }

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

    public function bulkUpdateKegiatan(Request $request)
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();
        if (!$this->canWriteForModule($user)) {
            return response()->json(['message' => 'Unauthorized action.'], 403);
        }
        if (!$this->getListPengawasanPermissions($user)['bulk_kegiatan']) {
            return response()->json(['message' => 'Unauthorized action.'], 403);
        }

        $data = $request->validate([
            'ids' => ['required', 'array', 'min:1'],
            'ids.*' => ['integer'],
            'status' => ['required', 'string', 'in:Belum Dimulai,Belum Dikerjakan,Sedang Dikerjakan,Selesai'],
        ]);

        $ids = collect($data['ids'])->filter()->unique()->values()->all();
        $activities = PengawasKegiatan::whereIn('id', $ids)->get(['id', 'pengawas_id', 'nama_kegiatan']);

        if ($activities->count() !== count($ids)) {
            return response()->json(['message' => 'Sebagian kegiatan tidak ditemukan.'], 404);
        }

        foreach ($activities->pluck('pengawas_id')->unique() as $pengawasId) {
            if (!$this->canAccessPengawas($user, (int) $pengawasId)) {
                return response()->json(['message' => 'Unauthorized action.'], 403);
            }
        }

        PengawasKegiatan::whereIn('id', $ids)->update([
            'status' => $data['status'],
            'updated_at' => now(),
        ]);

        foreach ($activities->pluck('pengawas_id')->unique() as $pengawasId) {
            $this->recalculateProjectStatus((int) $pengawasId);
        }

        $this->notifyListPengawasan($user, (int) $activities->first()->pengawas_id, 'update', 'Memperbarui status kegiatan (bulk)');

        return response()->json(['message' => 'Status kegiatan berhasil diperbarui']);
    }

    public function bulkDeleteKegiatan(Request $request)
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();
        if (!$this->canWriteForModule($user)) {
            return response()->json(['message' => 'Unauthorized action.'], 403);
        }
        $perms = $this->getListPengawasanPermissions($user);
        if (!$perms['bulk_kegiatan'] || !$perms['hapus_kegiatan']) {
            return response()->json(['message' => 'Unauthorized action.'], 403);
        }

        $data = $request->validate([
            'ids' => ['required', 'array', 'min:1'],
            'ids.*' => ['integer'],
        ]);

        $ids = collect($data['ids'])->filter()->unique()->values()->all();
        $activities = PengawasKegiatan::whereIn('id', $ids)->get(['id', 'pengawas_id']);

        if ($activities->count() !== count($ids)) {
            return response()->json(['message' => 'Sebagian kegiatan tidak ditemukan.'], 404);
        }

        foreach ($activities->pluck('pengawas_id')->unique() as $pengawasId) {
            if (!$this->canAccessPengawas($user, (int) $pengawasId)) {
                return response()->json(['message' => 'Unauthorized action.'], 403);
            }
        }

        PengawasKegiatan::whereIn('id', $ids)->delete();

        foreach ($activities->pluck('pengawas_id')->unique() as $pengawasId) {
            $this->recalculateProjectStatus((int) $pengawasId);
        }

        $this->notifyListPengawasan($user, (int) $activities->first()->pengawas_id, 'delete', 'Menghapus kegiatan (bulk)');

        return response()->json(['message' => 'Kegiatan berhasil dihapus']);
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

        $data = $request->validate(['bukti' => ['required', 'file', 'max:10240', 'mimes:pdf,jpg,jpeg,png']]);
        
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

        $permission = $this->getListPengawasanPermissions($user);
        if (!($permission['bukti'] || $permission['tambah_keterangan'] || $permission['edit_keterangan'])) {
            return response()->json(['message' => 'Unauthorized action.'], 403);
        }

        $data = $request->validate([
            'keterangan' => ['array'],
            'keterangan.*' => ['nullable', 'string', 'max:255'],
            'options' => ['nullable', 'array'],
            'options.*' => ['nullable', 'string', 'max:255'],
        ]);

        $selectedLabels = $permission['bukti']
            ? collect($data['keterangan'] ?? [])
                ->map(fn($l) => trim((string) $l))
                ->filter()
                ->unique()
                ->values()
            : collect();

        // Use provided options or fallback to selected
        $availableOptions = collect($data['options'] ?? $selectedLabels)
            ->map(fn($l) => trim((string) $l))
            ->filter()
            ->unique()
            ->values();

        // Ensure selected are in available
        $availableOptions = $availableOptions->merge($selectedLabels)->unique()->values();

        // Current existing in DB
        $existing = DB::table('pengawas_kegiatan_keterangan')
            ->where('pengawas_kegiatan_id', $act->id)
            ->select('id', 'label', 'bukti_path')
            ->get();
        
        $existingCleanLabels = $existing->map(function($row) {
            return str_replace('__inactive__', '', $row->label);
        })->unique()->values()->all();

        if (!$permission['tambah_keterangan']) {
            $newlyAdded = $availableOptions->reject(fn($l) => in_array($l, $existingCleanLabels, true));
            if ($newlyAdded->isNotEmpty()) {
                return response()->json(['message' => 'Unauthorized action (adding new options).'], 403);
            }
        }

        $finalStoreLabels = [];
        foreach ($availableOptions as $cleanLabel) {
            $existingRow = $existing->first(function($row) use ($cleanLabel) {
                return str_replace('__inactive__', '', $row->label) === $cleanLabel;
            });
            if ($permission['bukti']) {
                $finalStoreLabels[$cleanLabel] = $selectedLabels->contains($cleanLabel)
                    ? $cleanLabel
                    : '__inactive__' . $cleanLabel;
            } else {
                if ($existingRow) {
                    $finalStoreLabels[$cleanLabel] = $existingRow->label;
                } else {
                    $finalStoreLabels[$cleanLabel] = '__inactive__' . $cleanLabel;
                }
            }
        }

        $keptIds = [];

        foreach ($finalStoreLabels as $cleanLabel => $storeLabel) {
            $existingRow = $existing->first(function($row) use ($cleanLabel) {
                return $row->label === $cleanLabel || $row->label === '__inactive__' . $cleanLabel;
            });

            if ($existingRow) {
                if ($existingRow->label !== $storeLabel) {
                    DB::table('pengawas_kegiatan_keterangan')
                        ->where('id', $existingRow->id)
                        ->update(['label' => $storeLabel, 'updated_at' => now()]);
                }
                $keptIds[] = $existingRow->id;
            } else {
                $id = DB::table('pengawas_kegiatan_keterangan')->insertGetId([
                    'pengawas_kegiatan_id' => $act->id,
                    'label' => $storeLabel,
                    'created_at' => now(),
                    'updated_at' => now()
                ]);
                $keptIds[] = $id;
            }
        }

        $toDeleteRows = $existing->whereNotIn('id', $keptIds);
        foreach ($toDeleteRows as $row) {
            if ($row->bukti_path) Storage::disk('public')->delete($row->bukti_path);
        }
        if ($toDeleteRows->isNotEmpty()) {
            DB::table('pengawas_kegiatan_keterangan')->whereIn('id', $toDeleteRows->pluck('id'))->delete();
        }

        $finalList = DB::table('pengawas_kegiatan_keterangan')
            ->where('pengawas_kegiatan_id', $act->id)
            ->select(
                'pengawas_kegiatan_keterangan.label',
                'pengawas_kegiatan_keterangan.bukti_path',
                'pengawas_kegiatan_keterangan.bukti_original_name',
                'pengawas_kegiatan_keterangan.bukti_mime',
                'pengawas_kegiatan_keterangan.bukti_size',
                'pengawas_kegiatan_keterangan.bukti_uploaded_at'
            )
            ->orderBy('pengawas_kegiatan_keterangan.id')
            ->get()
            ->map(function ($k) {
                if (str_starts_with($k->label, '__inactive__')) return null;
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
            ->filter()
            ->values()
            ->toArray();

        $options = DB::table('pengawas_kegiatan_keterangan')
            ->where('pengawas_kegiatan_keterangan.pengawas_kegiatan_id', $act->id)
            ->distinct()
            ->orderBy('pengawas_kegiatan_keterangan.label')
            ->pluck('pengawas_kegiatan_keterangan.label')
            ->filter(fn($v) => $v !== null && $v !== '')
            ->map(fn($l) => str_replace('__inactive__', '', $l))
            ->unique()
            ->values()
            ->toArray();

        return response()->json([
            'message' => 'Keterangan berhasil diperbarui',
            'keterangan' => $finalList,
            'options' => $options,
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
            'bukti' => ['required', 'file', 'max:10240', 'mimes:pdf,jpg,jpeg,png'],
        ]);

        $label = trim($data['label']);
        $row = DB::table('pengawas_kegiatan_keterangan')
            ->where('pengawas_kegiatan_id', $act->id)
            ->whereIn('label', [$label, '__inactive__' . $label])
            ->orderByDesc('id')
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

        $row = DB::table('pengawas_kegiatan_keterangan')
            ->where('pengawas_kegiatan_id', $act->id)
            ->where('label', trim($data['label']))
            ->orderByDesc('id')
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

    // Master Data Management for List Pengawasan

    public function getMasterData()
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();
        if (!$this->canWriteForModule($user)) {
            return response()->json(['message' => 'Unauthorized action.'], 403);
        }
        $perms = $this->getListPengawasanPermissions($user);
        if (!$perms['bulk_kegiatan'] && !$perms['kelola_jenis_pekerjaan'] && !$perms['kelola_template_bulk']) {
            return response()->json(['message' => 'Unauthorized action.'], 403);
        }

        $data = MasterJenisPipa::with(['templateKegiatans' => function($q) {
            $q->orderBy('id');
        }])->orderBy('id')->get();
        return response()->json($data);
    }

    public function storeMasterJenisPipa(Request $request)
    {
        $user = Auth::user();
        if (!$this->canWriteForModule($user)) {
            return response()->json(['message' => 'Unauthorized action.'], 403);
        }
        if (!$this->getListPengawasanPermissions($user)['kelola_jenis_pekerjaan']) {
            return response()->json(['message' => 'Unauthorized action.'], 403);
        }

        $validated = $request->validate([
            'name' => 'required|string|unique:master_jenis_pipas,name|max:255',
        ]);

        $item = MasterJenisPipa::create($validated);
        return response()->json($item);
    }

    public function updateMasterJenisPipa(Request $request, $id)
    {
        $user = Auth::user();
        if (!$this->canWriteForModule($user)) {
            return response()->json(['message' => 'Unauthorized action.'], 403);
        }
        if (!$this->getListPengawasanPermissions($user)['kelola_jenis_pekerjaan']) {
            return response()->json(['message' => 'Unauthorized action.'], 403);
        }

        $item = MasterJenisPipa::findOrFail($id);
        $oldName = $item->name;
        
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:master_jenis_pipas,name,' . $id,
        ]);

        $item->update($validated);
        if ($oldName !== $validated['name']) {
            PengawasKegiatan::where('jenis_pipa', $oldName)->update([
                'jenis_pipa' => $validated['name'],
                'updated_at' => now(),
            ]);
        }
        return response()->json($item);
    }

    public function destroyMasterJenisPipa($id)
    {
        $user = Auth::user();
        if (!$this->canWriteForModule($user)) {
            return response()->json(['message' => 'Unauthorized action.'], 403);
        }
        if (!$this->getListPengawasanPermissions($user)['kelola_jenis_pekerjaan']) {
            return response()->json(['message' => 'Unauthorized action.'], 403);
        }

        $item = MasterJenisPipa::findOrFail($id);
        $isUsed = PengawasKegiatan::where('jenis_pipa', $item->name)->exists();
        if ($isUsed) {
            return response()->json([
                'message' => "Jenis pipa '{$item->name}' tidak bisa dihapus karena sudah digunakan pada kegiatan.",
            ], 422);
        }
        $item->delete(); 
        return response()->json(['message' => 'Deleted successfully']);
    }

    public function storeTemplateKegiatan(Request $request)
    {
        $user = Auth::user();
        if (!$this->canWriteForModule($user)) {
            return response()->json(['message' => 'Unauthorized action.'], 403);
        }
        if (!$this->getListPengawasanPermissions($user)['kelola_template_bulk']) {
            return response()->json(['message' => 'Unauthorized action.'], 403);
        }

        $validated = $request->validate([
            'master_jenis_pipa_id' => 'required|exists:master_jenis_pipas,id',
            'nama_kegiatan' => 'required|string|max:255',
        ]);

        $dup = TemplateKegiatan::where('master_jenis_pipa_id', $validated['master_jenis_pipa_id'])
            ->whereRaw('LOWER(nama_kegiatan) = ?', [strtolower($validated['nama_kegiatan'])])
            ->exists();
        if ($dup) {
            return response()->json(['message' => 'Template kegiatan tersebut sudah ada pada jenis pipa ini.'], 422);
        }

        $item = TemplateKegiatan::create($validated);
        return response()->json($item);
    }

    public function updateTemplateKegiatan(Request $request, $id)
    {
        $user = Auth::user();
        if (!$this->canWriteForModule($user)) {
            return response()->json(['message' => 'Unauthorized action.'], 403);
        }
        if (!$this->getListPengawasanPermissions($user)['kelola_template_bulk']) {
            return response()->json(['message' => 'Unauthorized action.'], 403);
        }

        $item = TemplateKegiatan::findOrFail($id);
        
        $validated = $request->validate([
            'nama_kegiatan' => 'required|string|max:255',
        ]);

        $dup = TemplateKegiatan::where('master_jenis_pipa_id', $item->master_jenis_pipa_id)
            ->where('id', '!=', $item->id)
            ->whereRaw('LOWER(nama_kegiatan) = ?', [strtolower($validated['nama_kegiatan'])])
            ->exists();
        if ($dup) {
            return response()->json(['message' => 'Template kegiatan tersebut sudah ada pada jenis pipa ini.'], 422);
        }

        $item->update($validated);
        return response()->json($item);
    }

    public function destroyTemplateKegiatan($id)
    {
        $user = Auth::user();
        if (!$this->canWriteForModule($user)) {
            return response()->json(['message' => 'Unauthorized action.'], 403);
        }
        if (!$this->getListPengawasanPermissions($user)['kelola_template_bulk']) {
            return response()->json(['message' => 'Unauthorized action.'], 403);
        }

        $item = TemplateKegiatan::findOrFail($id);
        $item->delete();
        return response()->json(['message' => 'Deleted successfully']);
    }
}
