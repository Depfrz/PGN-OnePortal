<?php

namespace App\Http\Controllers;

use App\Models\Module;
use App\Services\AuditService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class IntegrasiSistemController extends Controller
{
    public function index(Request $request)
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();
        
        $query = Module::query();

        // Search filter
        if ($request->filled('search')) {
            $query->where('name', 'like', '%' . $request->search . '%');
        }

        // Supervisor & Admin see ALL modules (Active and Inactive)
        if ($user->hasRole(['Supervisor', 'Admin'])) {
            $modules = $query->get();
        } 
        // User & SuperUser see ONLY assigned modules
        else {
            // Get module IDs from module_access where can_read is true
            $assignedModuleIds = \App\Models\ModuleAccess::where('user_id', $user->id)
                ->where('can_read', true)
                ->pluck('module_id');
            
            $modules = $query->whereIn('id', $assignedModuleIds)
                ->where('status', true)
                ->get();
        }

        return view('integrasi-sistem.index', compact('modules'));
    }

    public function destroy(Module $module)
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        if (!$user->hasRole(['Supervisor', 'Admin'])) {
            abort(403, 'Unauthorized action.');
        }

        $moduleName = $module->name;
        $module->delete();

        AuditService::log($user, 'delete', 'Integrasi Sistem', "Menghapus modul {$moduleName}");

        return redirect()->route('integrasi-sistem.index')->with('success', 'Modul berhasil dihapus.');
    }

    public function create()
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        // Only Supervisor and Admin can access this
        // Middleware handled in route or here
        if (!$user->hasRole(['Supervisor', 'Admin'])) {
            abort(403, 'Unauthorized action.');
        }

        return view('integrasi-sistem.create');
    }

    public function store(Request $request)
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        // Check permission again
        if (!$user->hasRole(['Supervisor', 'Admin'])) {
            abort(403, 'Unauthorized action.');
        }

        // Validate
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:150', // Max 150 characters
            'url' => 'nullable|string',
            'category' => 'nullable|string',
            'tab_type' => 'nullable|string',
            'is_important' => 'nullable|in:on,off',
        ], [
            'description.max' => 'Deskripsi tidak boleh lebih dari 150 karakter.',
        ]);

        // Generate simple slug from name
        $slug = Str::slug($validated['name']);

        // Create Module
        Module::create([
            'name' => $validated['name'],
            'slug' => $slug,
            'description' => $validated['description'],
            'url' => $validated['url'],
            'status' => isset($validated['is_important']) && $validated['is_important'] === 'on' ? true : false,
            'tab_type' => $validated['tab_type'] === 'New tab (Blank)' ? 'new' : 'current',
            // Icon handling to be added later if file upload is implemented
        ]);

        AuditService::log($user, 'create', 'Integrasi Sistem', "Menambahkan modul baru: {$validated['name']}");

        return redirect()->route('integrasi-sistem.index')->with('success', 'Modul berhasil ditambahkan.');
    }

    public function edit(Module $module)
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        if (!$user->hasRole(['Supervisor', 'Admin'])) {
            abort(403, 'Unauthorized action.');
        }

        return view('integrasi-sistem.edit', compact('module'));
    }

    public function update(Request $request, Module $module)
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        if (!$user->hasRole(['Supervisor', 'Admin'])) {
            abort(403, 'Unauthorized action.');
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:150', // Max 150 characters
            'url' => 'nullable|string',
            'category' => 'nullable|string',
            'tab_type' => 'nullable|string',
            'is_important' => 'nullable|in:on,off',
        ], [
            'description.max' => 'Deskripsi tidak boleh lebih dari 150 karakter.',
        ]);

        $module->update([
            'name' => $validated['name'],
            // Don't update slug to preserve links, or optional
            'description' => $validated['description'],
            'url' => $validated['url'],
            'status' => isset($validated['is_important']) && $validated['is_important'] === 'on' ? true : false,
            'tab_type' => $validated['tab_type'] === 'New tab (Blank)' ? 'new' : 'current',
        ]);

        AuditService::log($user, 'update', 'Integrasi Sistem', "Mengubah data modul: {$validated['name']}");

        return redirect()->route('integrasi-sistem.index')->with('success', 'Modul berhasil diperbarui.');
    }
}
