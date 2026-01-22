<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Module;
use App\Models\ModuleAccess;
use App\Services\AuditService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Spatie\Permission\Models\Role;

class ManagementUserController extends Controller
{
    public function index()
    {
        /** @var \App\Models\User $currentUser */
        $currentUser = Auth::user();

        if (!$currentUser->hasRole('Admin')) {
            abort(403, 'Unauthorized action.');
        }

        $users = User::with(['roles', 'moduleAccesses.module'])->get()->map(function($user) {
            return [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'instansi' => $user->instansi ?? '-',
                'jabatan' => $user->jabatan ?? '-',
                'role' => $user->roles->first()?->name ?? 'User',
                'status' => 'Active', // Static for now
                'hak_akses' => $user->moduleAccesses->map(fn($ma) => $ma->module->name)->values()->toArray(),
                'dashboard_access' => $user->moduleAccesses->filter(fn($ma) => $ma->show_on_dashboard)->map(fn($ma) => $ma->module->name)->values()->toArray(),
            ];
        });

        $availableRoles = Role::pluck('name');
        // Get all available modules for access rights, grouped by their 'group' column
        $availableAccess = Module::orderBy('group')->orderBy('name')->get()->groupBy('group');

        return view('management-user', compact('users', 'availableRoles', 'availableAccess'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
            'instansi' => ['nullable', 'string', 'max:255'],
            'jabatan' => ['nullable', 'string', 'max:255'],
            'role' => ['required', 'exists:roles,name'],
            'hak_akses' => ['nullable', 'array'],
            'hak_akses.*' => ['exists:modules,name'],
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'instansi' => $request->instansi,
            'jabatan' => $request->jabatan,
        ]);

        $user->assignRole($request->role);

        if ($request->has('hak_akses')) {
            $this->syncAccess($user, $request->hak_akses);
        }

        AuditService::log(Auth::user(), 'create', 'Management User', "Membuat user baru: {$user->name} ({$user->email})");

        return response()->json(['message' => 'User created successfully', 'user' => $user]);
    }

    public function update(Request $request, User $user)
    {
        // Validation and update logic for name/email if needed
        return response()->json(['message' => 'User updated']);
    }

    public function updateRole(Request $request, User $user)
    {
        $request->validate([
            'role' => ['required', 'exists:roles,name'],
        ]);

        $user->syncRoles([$request->role]);

        AuditService::log(Auth::user(), 'update', 'Management User', "Mengubah role user {$user->name} menjadi {$request->role}");

        return response()->json(['message' => 'Role updated successfully']);
    }

    public function updateAccess(Request $request, User $user)
    {
        $request->validate([
            'hak_akses' => ['array'],
            'hak_akses.*' => ['exists:modules,name'],
            'dashboard_access' => ['array'],
            'dashboard_access.*' => ['exists:modules,name'],
        ]);

        $this->syncAccess($user, $request->hak_akses ?? [], $request->dashboard_access ?? []);

        AuditService::log(Auth::user(), 'update', 'Management User', "Memperbarui hak akses user: {$user->name}");

        return response()->json(['message' => 'Access rights updated successfully']);
    }

    public function resetPassword(Request $request, User $user)
    {
        $request->validate([
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ]);

        $user->update([
            'password' => Hash::make($request->password),
        ]);

        return response()->json(['message' => 'Password reset successfully']);
    }

    public function destroy(User $user)
    {
        if ($user->id === Auth::id()) {
            return response()->json(['message' => 'Cannot delete yourself'], 403);
        }
        
        $userName = $user->name;
        $user->moduleAccesses()->delete();
        $user->delete();

        AuditService::log(Auth::user(), 'delete', 'Management User', "Menghapus user: {$userName}");

        return response()->json(['message' => 'User deleted successfully']);
    }

    private function syncAccess(User $user, array $moduleNames, array $dashboardModuleNames = [])
    {
        $modules = Module::whereIn('name', $moduleNames)->get();
        
        $user->moduleAccesses()->delete();

        // Sub-modules that should NEVER be on the dashboard
        $subModules = ['Dokumen Favorit', 'Riwayat Dokumen', 'Pengecekan File', 'Upload Dokumen', 'Beranda'];
        
        // Modules that MUST be on the dashboard if accessed
        $forcedDashboardModules = ['Buku Saku', 'List Pengawasan'];

        foreach ($modules as $module) {
            $showOnDashboard = in_array($module->name, $dashboardModuleNames);
            
            // Enforce rules
            if (in_array($module->name, $forcedDashboardModules)) {
                $showOnDashboard = true;
            } elseif (in_array($module->name, $subModules)) {
                $showOnDashboard = false;
            }

            ModuleAccess::create([
                'user_id' => $user->id,
                'module_id' => $module->id,
                'can_read' => true,
                'can_write' => true,
                'can_delete' => true,
                'show_on_dashboard' => $showOnDashboard,
            ]);
        }
    }
}
