<?php

namespace App\Http\Controllers;

use App\Models\Module;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        // Built-in features to exclude from the main dashboard grid as they are in the sidebar
        $excludedModules = ['Dashboard', 'Integrasi Sistem', 'Management User', 'Data History', 'History'];

        $search = trim((string) $request->query('search', ''));

        // Check if user has specific access rights configured (Priority over Role)
        // We check if ANY access record exists, regardless of can_read/show_on_dashboard status
        $hasConfiguredAccess = \App\Models\ModuleAccess::where('user_id', $user->id)->exists();

        if ($hasConfiguredAccess) {
            // Get module IDs that are readable AND enabled for dashboard
            $assignedModuleIds = \App\Models\ModuleAccess::where('user_id', $user->id)
                ->where('can_read', true)
                ->where('show_on_dashboard', true)
                ->pluck('module_id');

            $query = Module::whereIn('id', $assignedModuleIds)
                ->where('status', true)
                ->whereNotIn('name', $excludedModules);

            if ($search !== '') {
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'like', '%' . $search . '%')
                        ->orWhere('description', 'like', '%' . $search . '%');
                });
            }

            $modules = $query->get();
        }
        // Fallback for Admin/Supervisor if no specific access is configured (Show All)
        elseif ($user->hasRole(['Supervisor', 'Admin'])) {
            $query = Module::where('status', true)->whereNotIn('name', $excludedModules);

            if ($search !== '') {
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'like', '%' . $search . '%')
                        ->orWhere('description', 'like', '%' . $search . '%');
                });
            }

            $modules = $query->get();
        } 
        // Fallback for standard users with no configured access (show none)
        else {
            $modules = collect();
        }

        return view('dashboard', compact('modules'));
    }
}
