<?php

namespace App\Http\Controllers;

use App\Models\Module;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function index()
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        // Built-in features to exclude from the main dashboard grid as they are in the sidebar
        $excludedModules = ['Dashboard', 'Integrasi Sistem', 'Management User', 'Data History', 'History'];

        if ($user->hasRole(['Supervisor', 'Admin'])) {
            $modules = Module::where('status', true)
                ->whereNotIn('name', $excludedModules)
                ->get();
            return view('dashboard', compact('modules'));
        }
        
        // Get module IDs from module_access where can_read is true
        $assignedModuleIds = \App\Models\ModuleAccess::where('user_id', $user->id)
            ->where('can_read', true)
            ->pluck('module_id');

        // Check if user has specific access rights configured
        if ($assignedModuleIds->isNotEmpty()) {
            $modules = Module::whereIn('id', $assignedModuleIds)
                ->where('status', true)
                ->whereNotIn('name', $excludedModules)
                ->get();
        } 
        // Fallback for standard users with no configured access (show none)
        else {
            $modules = collect();
        }

        return view('dashboard', compact('modules'));
    }
}
