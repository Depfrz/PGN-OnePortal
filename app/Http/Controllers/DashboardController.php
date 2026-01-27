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

        // Features to exclude from main dashboard (e.g. sidebar-only features)
        $excludedModules = [
            'Dashboard', 'Integrasi Sistem', 'Management User', 'Data History', 'History',
            // Exclude Buku Saku sub-modules but NOT the main 'Buku Saku' module itself
            'Dokumen Favorit', 'Riwayat Dokumen', 'Pengecekan File', 'Upload Dokumen', 'Beranda'
        ];

        $search = trim((string) $request->query('search', ''));

        // Check if user has specific access rights configured (Priority over Role)
        // We check if ANY access record exists, regardless of can_read/show_on_dashboard status
        $hasConfiguredAccess = \App\Models\ModuleAccess::where('user_id', $user->id)->exists();

        if ($hasConfiguredAccess) {
            // Get module IDs that are readable AND enabled for dashboard
            // We explicitly force 'Buku Saku' and 'List Pengawasan' to show if the user has read access,
            // regardless of the 'show_on_dashboard' flag (which might be desynced).
            $userAccesses = \App\Models\ModuleAccess::where('user_id', $user->id)
                ->where('can_read', true)
                ->with('module')
                ->get();

            $assignedModuleIds = $userAccesses->filter(function ($access) {
                if (!$access->module) return false;
                
                // Always show these specific modules if user has read access
                if (in_array($access->module->name, ['Buku Saku', 'List Pengawasan'])) {
                    return true;
                }
                
                // For others, respect the flag
                return $access->show_on_dashboard;
            })->pluck('module_id');

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
            
            // EMERGENCY FIX: Manually inject missing modules if they are not in the result but user has access
            // This handles cases where excludedModules logic might be over-filtering or DB query issues
            // [Sync Check] Forced update to ensure code propagation
            $existingNames = $modules->pluck('name')->toArray();
            
            // Check for Buku Saku
            if (!in_array('Buku Saku', $existingNames) && 
                $userAccesses->contains(fn($a) => $a->module && $a->module->name === 'Buku Saku')) {
                $bukuSaku = Module::where('name', 'Buku Saku')->first();
                if ($bukuSaku) $modules->push($bukuSaku);
            }
            
            // Check for List Pengawasan
            if (!in_array('List Pengawasan', $existingNames) && 
                $userAccesses->contains(fn($a) => $a->module && $a->module->name === 'List Pengawasan')) {
                $listPengawasan = Module::where('name', 'List Pengawasan')->first();
                if ($listPengawasan) $modules->push($listPengawasan);
            }

        }
        // Fallback for Admin/Supervisor if no specific access is configured (Show All)
        elseif ($user->hasRole(['Supervisor', 'Admin'])) {
            $query = Module::where('status', true)
                ->whereNotIn('name', $excludedModules)
                ->orderBy('order', 'asc');

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
