<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Models\Module;
use App\Models\User;
use App\Models\ModuleAccess;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Define the strict mapping of Module Name -> Group
        $groupMapping = [
            // Web Utama
            'Integrasi Sistem' => 'Web Utama',
            'Management User' => 'Web Utama',
            'Data History' => 'Web Utama',
            
            // Buku Saku
            'Buku Saku' => 'Buku Saku',
            'Beranda' => 'Buku Saku',
            'Dokumen Favorit' => 'Buku Saku',
            'Riwayat Dokumen' => 'Buku Saku',
            'Pengecekan File' => 'Buku Saku',
            'Upload Dokumen' => 'Buku Saku',
            
            // List Pengawasan
            'List Pengawasan' => 'List Pengawasan',
        ];

        foreach ($groupMapping as $name => $group) {
            Module::where('name', $name)->update(['group' => $group]);
        }
        
        // Also ensure Admin has access to EVERYTHING to avoid "upload manual" issues
        $admins = User::role('Admin')->get();
        // Fallback for specific email
        $specificAdmin = User::where('email', 'admin@pgn.co.id')->first();
        if ($specificAdmin && !$admins->contains($specificAdmin->id)) {
            $admins->push($specificAdmin);
        }

        $allModules = Module::all();
        
        foreach ($admins as $admin) {
            foreach ($allModules as $module) {
                // Determine if it should be on dashboard
                // Only Parent modules: Buku Saku, List Pengawasan, and maybe Web Utama ones if desired (but Web Utama usually hidden from dashboard grid as per request)
                // Wait, Web Utama modules are excluded in DashboardController, so show_on_dashboard=true doesn't hurt.
                
                $showOnDashboard = in_array($module->name, ['Buku Saku', 'List Pengawasan']);
                
                ModuleAccess::updateOrCreate(
                    ['user_id' => $admin->id, 'module_id' => $module->id],
                    [
                        'can_read' => true,
                        'can_write' => true,
                        'can_delete' => true,
                        'show_on_dashboard' => $showOnDashboard
                    ]
                );
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }
};
