<?php

namespace App\Http\Controllers;

use App\Models\Module;
use App\Models\ModuleAccess;
use App\Models\User;
use App\Notifications\SystemNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
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
            ->orderBy('pengawas.created_at', 'desc');

        if (!$user->hasRole(['Admin', 'Supervisor'])) {
            $pengawasQuery->join('pengawas_users', 'pengawas_users.pengawas_id', '=', 'pengawas.id')
                ->where('pengawas_users.user_id', $user->id);
        }

        if ($search !== '') {
            $pengawasQuery->where('pengawas.name', 'like', '%' . $search . '%');
        }

        $pengawas = $pengawasQuery->get();
        $assignedUsersMap = $this->getAssignedUsersMap($pengawas->pluck('id')->all());

        $items = $pengawas->map(function ($p) use ($assignedUsersMap) {
            $labels = DB::table('pengawas_keterangan')
                ->join('keterangan_options', 'keterangan_options.id', '=', 'pengawas_keterangan.keterangan_option_id')
                ->where('pengawas_keterangan.pengawas_id', $p->id)
                ->pluck('keterangan_options.name')
                ->toArray();

            $createdAt = $p->created_at ? Carbon::parse($p->created_at) : null;
            $deadline = $p->deadline ? Carbon::parse($p->deadline) : null;

            return [
                'id' => $p->id,
                'nama' => $p->name,
                'divisi' => $p->divisi ?? '-',
                'created_at' => $createdAt ? $createdAt->toISOString() : null,
                'tanggal' => $createdAt ? $createdAt->format('d-m-Y H:i') : '-',
                'deadline' => $deadline ? $deadline->format('Y-m-d') : null,
                'deadline_display' => $deadline ? $deadline->format('d-m-Y') : '-',
                'status' => $this->normalizeStatus($p->status),
                'keterangan' => $labels,
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

        $data = $request->validate([
            'nama' => ['required', 'string', 'max:255'],
            'divisi' => ['nullable', 'string', 'max:255'],
            'keterangan' => ['array'],
            'keterangan.*' => ['string', 'max:255'],
            'deadline' => ['nullable', 'date'],
            'status' => ['nullable', 'string', Rule::in(self::ALLOWED_STATUS)],
            'pengawas_users' => ['array'],
            'pengawas_users.*' => ['integer', 'exists:users,id'],
        ]);

        $status = $data['status'] ?? 'On Progress';

        $pengawasId = DB::table('pengawas')->insertGetId([
            'name' => $data['nama'],
            'divisi' => $data['divisi'] ?? null,
            'tanggal' => null,
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

        $labels = $data['keterangan'] ?? [];
        foreach ($labels as $label) {
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
            DB::table('pengawas_keterangan')->updateOrInsert(
                ['pengawas_id' => $pengawasId, 'keterangan_option_id' => $optId],
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
            'tanggal' => now()->format('d-m-Y H:i'),
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

    public function updateKeterangan(Request $request, int $id)
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
            'keterangan' => ['array'],
            'keterangan.*' => ['string', 'max:255'],
        ]);

        $labels = collect($data['keterangan'] ?? [])
            ->map(fn($label) => trim((string) $label))
            ->filter()
            ->unique()
            ->values();

        DB::table('pengawas_keterangan')->where('pengawas_id', $id)->delete();

        foreach ($labels as $label) {
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
            DB::table('pengawas_keterangan')->updateOrInsert(
                ['pengawas_id' => $id, 'keterangan_option_id' => $optId],
                ['created_at' => now(), 'updated_at' => now()]
            );
        }

        $projectName = $this->getPengawasName($id) ?? 'Proyek';
        $this->notifyListPengawasan($user, $id, 'update', "Memperbarui keterangan proyek: {$projectName}");

        return response()->json([
            'message' => 'Keterangan berhasil diperbarui',
            'keterangan' => $labels->values()->all(),
        ]);
    }

    public function destroy(int $id)
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();
        if (!$this->canWriteForModule($user)) {
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
        if (!$this->canAccessPengawas($user, $id)) {
            return response()->json(['message' => 'Unauthorized action.'], 403);
        }

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
}
