<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Module;
use App\Models\ModuleAccess;
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
            ];
        });

        $availableRoles = Role::pluck('name');
        $availableAccess = Module::pluck('name');

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

        return response()->json(['message' => 'Role updated successfully']);
    }

    public function updateAccess(Request $request, User $user)
    {
        $request->validate([
            'hak_akses' => ['array'],
            'hak_akses.*' => ['exists:modules,name'],
        ]);

        $this->syncAccess($user, $request->hak_akses ?? []);

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
        
        $user->moduleAccesses()->delete();
        $user->delete();

        return response()->json(['message' => 'User deleted successfully']);
    }

    private function syncAccess(User $user, array $moduleNames)
    {
        $modules = Module::whereIn('name', $moduleNames)->get();
        
        $user->moduleAccesses()->delete();

        foreach ($modules as $module) {
            ModuleAccess::create([
                'user_id' => $user->id,
                'module_id' => $module->id,
                'can_read' => true,
                'can_write' => true,
                'can_delete' => true,
            ]);
        }
    }
}
