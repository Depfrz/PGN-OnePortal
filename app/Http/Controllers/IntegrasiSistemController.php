<?php

namespace App\Http\Controllers;

use App\Models\Module;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

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
}
