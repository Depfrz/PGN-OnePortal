<?php

namespace App\Http\Controllers;

use App\Models\Module;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class IntegrasiSistemController extends Controller
{
    public function index()
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();
        
        // Supervisor & Admin see ALL modules
        if ($user->hasRole(['Supervisor', 'Admin'])) {
            $modules = Module::where('status', true)->get();
        } 
        // User & SuperUser see ONLY assigned modules
        else {
            // Get module IDs from module_access where can_read is true
            $assignedModuleIds = \App\Models\ModuleAccess::where('user_id', $user->id)
                ->where('can_read', true)
                ->pluck('module_id');
            
            $modules = Module::whereIn('id', $assignedModuleIds)
                ->where('status', true)
                ->get();
        }

        return view('integrasi-sistem.index', compact('modules'));
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
            'description' => 'nullable|string',
            'url' => 'nullable|string',
            'category' => 'nullable|string',
            'tab_type' => 'nullable|string',
            'is_important' => 'nullable|in:on,off',
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

        return redirect()->route('integrasi-sistem.index')->with('success', 'Modul berhasil ditambahkan.');
    }
}
