<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\PengecekanFileController;
use App\Http\Controllers\UploadDokumenController;
use App\Http\Controllers\ListPengawasanController;
use App\Http\Controllers\ListPengawasanKategoriController;
use App\Http\Controllers\BukuSakuController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\AuditLogController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\IntegrasiSistemController;
use App\Http\Controllers\ManagementUserController;
use App\Http\Controllers\HistoryController;
use App\Models\BukuSakuDocument;
use Illuminate\Support\Facades\Artisan;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/', function () {
    return redirect()->route('login');
});

Route::middleware(['auth'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Pengecekan File Routes
    Route::get('/pengecekan-file', [PengecekanFileController::class, 'index'])->name('pengecekan-file.index');
    Route::get('/pengecekan-file/search', [PengecekanFileController::class, 'search'])->name('pengecekan-file.search');

    // Upload Dokumen Routes
    Route::get('/upload-dokumen', [UploadDokumenController::class, 'index'])->name('upload-dokumen.index');
    Route::post('/upload-dokumen', [UploadDokumenController::class, 'store'])->name('upload-dokumen.store');
    
    // List Pengawasan Routes
    Route::prefix('list-pengawasan')->name('list-pengawasan.')->group(function () {
        Route::get('/', [ListPengawasanController::class, 'index'])->name('index');
        Route::get('/{id}', [ListPengawasanController::class, 'show'])
            ->whereNumber('id')
            ->name('show');

        Route::post('/', [ListPengawasanController::class, 'store'])->name('store');
        Route::post('/store', [ListPengawasanController::class, 'store']); // Backward compatibility

        Route::patch('/{id}', [ListPengawasanController::class, 'updatePengawas'])
            ->whereNumber('id')
            ->name('update');
        Route::put('/{id}', [ListPengawasanController::class, 'updatePengawas'])
            ->whereNumber('id'); // Backward compatibility / hosting fallback

        Route::delete('/{id}', [ListPengawasanController::class, 'destroy'])
            ->whereNumber('id')
            ->name('destroy');

        Route::post('/bulk-update', [ListPengawasanController::class, 'bulkUpdatePengawas'])
            ->name('bulk-update');
        Route::post('/bulk-delete', [ListPengawasanController::class, 'bulkDeletePengawas'])
            ->name('bulk-delete');
        
        // Kategori Routes
        Route::post('/kategori', [ListPengawasanKategoriController::class, 'store'])->name('kategori.store');
        Route::put('/kategori/{id}', [ListPengawasanKategoriController::class, 'update'])->name('kategori.update');
        Route::delete('/kategori/{id}', [ListPengawasanKategoriController::class, 'destroy'])->name('kategori.destroy');
        
        // Kegiatan Routes
        Route::get('/{id}/kegiatan', [ListPengawasanController::class, 'kegiatanIndex'])
            ->whereNumber('id')
            ->name('kegiatan.index');
        Route::post('/{id}/kegiatan', [ListPengawasanController::class, 'storeKegiatan'])
            ->whereNumber('id')
            ->name('kegiatan.store');
        Route::post('/{id}/kegiatan/bulk', [ListPengawasanController::class, 'storeBulkKegiatan'])
            ->whereNumber('id')
            ->name('kegiatan.store-bulk');

        Route::get('/kegiatan/{activity}', [ListPengawasanController::class, 'showKegiatan'])
            ->whereNumber('activity')
            ->name('kegiatan.show');
        Route::get('/{id}/kegiatan/{activity}', [ListPengawasanController::class, 'showKegiatan'])
            ->whereNumber('id')
            ->whereNumber('activity'); // Backward compatibility

        Route::put('/kegiatan/{activity}', [ListPengawasanController::class, 'updateKegiatan'])
            ->whereNumber('activity')
            ->name('kegiatan.update');
        Route::post('/kegiatan/{activity}', [ListPengawasanController::class, 'updateKegiatan'])
            ->whereNumber('activity'); // Fallback for hosting

        Route::delete('/kegiatan/{activity}', [ListPengawasanController::class, 'destroyKegiatan'])
            ->whereNumber('activity')
            ->name('kegiatan.destroy');

        Route::post('/kegiatan/bulk-update', [ListPengawasanController::class, 'bulkUpdateKegiatan'])
            ->name('kegiatan.bulk-update');
        Route::post('/kegiatan/bulk-delete', [ListPengawasanController::class, 'bulkDeleteKegiatan'])
            ->name('kegiatan.bulk-delete');

        Route::post('/{id}/pengawas-users', [ListPengawasanController::class, 'addPengawasUsers'])
            ->whereNumber('id');
        Route::patch('/{id}/pengawas-users', [ListPengawasanController::class, 'replacePengawasUser'])
            ->whereNumber('id');
        Route::delete('/{id}/pengawas-users', [ListPengawasanController::class, 'removePengawasUser'])
            ->whereNumber('id');

        Route::patch('/kegiatan/{activity}/keterangan', [ListPengawasanController::class, 'updateKeteranganKegiatan'])
            ->whereNumber('activity');
        Route::post('/kegiatan/{activity}/keterangan', [ListPengawasanController::class, 'updateKeteranganKegiatan'])
            ->whereNumber('activity'); // Fallback for hosting

        Route::post('/kegiatan/{activity}/bukti', [ListPengawasanController::class, 'uploadBuktiKegiatan'])
            ->whereNumber('activity');
        Route::delete('/kegiatan/{activity}/bukti', [ListPengawasanController::class, 'deleteBuktiKegiatan'])
            ->whereNumber('activity');
        Route::post('/kegiatan/{activity}/keterangan/bukti', [ListPengawasanController::class, 'uploadBuktiKeteranganKegiatan'])
            ->whereNumber('activity');
        Route::delete('/kegiatan/{activity}/keterangan/bukti', [ListPengawasanController::class, 'deleteBuktiKeteranganKegiatan'])
            ->whereNumber('activity');

        Route::get('/master-data', [ListPengawasanController::class, 'getMasterData'])
            ->name('master-data');
        Route::post('/master-jenis-pipa', [ListPengawasanController::class, 'storeMasterJenisPipa'])
            ->name('master-jenis-pipa.store');
        Route::put('/master-jenis-pipa/{id}', [ListPengawasanController::class, 'updateMasterJenisPipa'])
            ->whereNumber('id')
            ->name('master-jenis-pipa.update');
        Route::delete('/master-jenis-pipa/{id}', [ListPengawasanController::class, 'destroyMasterJenisPipa'])
            ->whereNumber('id')
            ->name('master-jenis-pipa.destroy');

        Route::post('/template-kegiatan', [ListPengawasanController::class, 'storeTemplateKegiatan'])
            ->name('template-kegiatan.store');
        Route::put('/template-kegiatan/{id}', [ListPengawasanController::class, 'updateTemplateKegiatan'])
            ->whereNumber('id')
            ->name('template-kegiatan.update');
        Route::delete('/template-kegiatan/{id}', [ListPengawasanController::class, 'destroyTemplateKegiatan'])
            ->whereNumber('id')
            ->name('template-kegiatan.destroy');
        
        // Dokumentasi Routes
        Route::post('/dokumentasi', [ListPengawasanController::class, 'storeDokumentasi'])->name('dokumentasi.store');
        Route::delete('/dokumentasi/{id}', [ListPengawasanController::class, 'destroyDokumentasi'])->name('dokumentasi.destroy');
        Route::post('/dokumentasi/bulk-delete', [ListPengawasanController::class, 'bulkDestroyDokumentasi'])->name('dokumentasi.bulk-destroy');

        Route::patch('/{id}/keterangan', [ListPengawasanController::class, 'updateKeterangan'])
            ->whereNumber('id')
            ->name('update-keterangan');
        Route::post('/{id}/keterangan', [ListPengawasanController::class, 'updateKeterangan'])
            ->whereNumber('id'); // Fallback for hosting

        Route::post('/{id}/bukti', [ListPengawasanController::class, 'uploadBukti'])
            ->whereNumber('id')
            ->name('bukti.store');
        Route::delete('/{id}/bukti', [ListPengawasanController::class, 'deleteBukti'])
            ->whereNumber('id')
            ->name('bukti.destroy');

        Route::post('/{id}/keterangan/bukti', [ListPengawasanController::class, 'uploadBuktiKeterangan'])
            ->whereNumber('id')
            ->name('keterangan.bukti.store');
        Route::delete('/{id}/keterangan/bukti', [ListPengawasanController::class, 'deleteBuktiKeterangan'])
            ->whereNumber('id')
            ->name('keterangan.bukti.destroy');
    });

    // Buku Saku Routes
    Route::prefix('buku-saku')->name('buku-saku.')->group(function () {
        Route::get('/', [BukuSakuController::class, 'index'])->name('index');
        Route::get('/upload', [BukuSakuController::class, 'upload'])->name('upload');
        Route::post('/store', [BukuSakuController::class, 'store'])->name('store');
        Route::get('/edit/{id}', [BukuSakuController::class, 'edit'])->name('edit');
        Route::put('/update/{id}', [BukuSakuController::class, 'update'])->name('update');
        Route::delete('/destroy/{document}', [BukuSakuController::class, 'destroy'])->name('destroy');
        Route::get('/preview/{document}', [BukuSakuController::class, 'preview'])->name('preview');
        Route::get('/download/{document}', [BukuSakuController::class, 'download'])->name('download');
        
        Route::get('/hapus-dokumen', [BukuSakuController::class, 'hapusDokumenIndex'])->name('hapus-dokumen');
        Route::get('/approval', [BukuSakuController::class, 'approvalIndex'])->name('approval');
        Route::post('/favorites/{id}', [BukuSakuController::class, 'toggleFavorite'])->name('toggle-favorite');
        Route::get('/favorites', [BukuSakuController::class, 'favorites'])->name('favorites');
        Route::get('/expired', [BukuSakuController::class, 'expiredIndex'])->name('expired');
        Route::get('/history', [BukuSakuController::class, 'history'])->name('history');
        Route::get('/show/{document}', [BukuSakuController::class, 'show'])->name('show');
        
        // Tag Management
        Route::post('/tags', [BukuSakuController::class, 'storeTag'])->name('tags.store');
        Route::delete('/tags/{id}', [BukuSakuController::class, 'destroyTag'])->name('tags.destroy');
    });

    // Notifications
    Route::get('/notifications', [NotificationController::class, 'index'])->name('notifications.index');
    Route::post('/notifications/mark-read/{id}', [NotificationController::class, 'markAsRead'])->name('notifications.mark-read');
    Route::post('/notifications/mark-all-read', [NotificationController::class, 'markAllAsRead'])->name('notifications.mark-all-read');

    // Audit Log
    Route::get('/audit-logs', [AuditLogController::class, 'index'])->name('audit-logs.index');
    
    // History (Global)
    Route::get('/history', [HistoryController::class, 'index'])->name('history');

    // Integrasi Sistem Routes
    Route::get('/integrasi-sistem', [IntegrasiSistemController::class, 'index'])->name('integrasi-sistem.index');
    Route::get('/integrasi-sistem/create', [IntegrasiSistemController::class, 'create'])->name('integrasi-sistem.create');
    Route::post('/integrasi-sistem', [IntegrasiSistemController::class, 'store'])->name('integrasi-sistem.store');
    Route::get('/integrasi-sistem/{module}/edit', [IntegrasiSistemController::class, 'edit'])->name('integrasi-sistem.edit');
    Route::put('/integrasi-sistem/{module}', [IntegrasiSistemController::class, 'update'])->name('integrasi-sistem.update');
    Route::delete('/integrasi-sistem/{module}', [IntegrasiSistemController::class, 'destroy'])->name('integrasi-sistem.destroy');

    // Management User Routes
    Route::get('/management-user', [ManagementUserController::class, 'index'])->name('management-user.index');
    Route::post('/management-user', [ManagementUserController::class, 'store'])->name('management-user.store');
    Route::put('/management-user/{user}', [ManagementUserController::class, 'update'])->name('management-user.update');
    Route::delete('/management-user/{user}', [ManagementUserController::class, 'destroy'])->name('management-user.destroy');
    Route::match(['put', 'patch'], '/management-user/{user}/role', [ManagementUserController::class, 'updateRole'])->name('management-user.update-role');
    Route::match(['put', 'patch'], '/management-user/{user}/access', [ManagementUserController::class, 'updateAccess'])->name('management-user.update-access');
    Route::match(['put', 'patch'], '/management-user/{user}/password', [ManagementUserController::class, 'resetPassword'])->name('management-user.reset-password');
    
    // Profile Routes
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
    
    // Debug Routes
    Route::get('/debug-routes', function() {
        $routes = collect(Route::getRoutes())->map(function ($route) {
            return $route->uri();
        });
        
        $expiredRoute = $routes->first(function($uri) {
            return str_contains($uri, 'buku-saku/expired');
        });
        
        if ($expiredRoute) {
            return "Ditemukan: " . $expiredRoute;
        }
        return "Rute 'expired' TIDAK DITEMUKAN. Pastikan Anda telah mengupload file web.php terbaru.";
    });

    Route::get('/clear-all-caches', function() {
        Artisan::call('view:clear');
        Artisan::call('route:clear');
        Artisan::call('config:clear');
        Artisan::call('cache:clear');
        return "Berhasil membersihkan semua cache.";
    });
    
    Route::get('/create-dummy-expired', function() {
        // Find a random approved document
        $doc = BukuSakuDocument::where('status', 'approved')->inRandomOrder()->first();
        if ($doc) {
            // Set valid_until to 30 days from now (Countdown mode)
            $doc->valid_until = now()->addDays(30);
            $doc->save();
            return "Berhasil mengubah dokumen '{$doc->title}' menjadi status hitung mundur (30 hari lagi). Silakan cek menu Dokumen Kedaluwarsa.";
        }
        return "Tidak ada dokumen yang ditemukan untuk diubah.";
    });

    Route::get('/fix-module-expired', function () {
        // Clear caches first
        try {
            Artisan::call('cache:clear');
            Artisan::call('config:clear');
            Artisan::call('view:clear');
            Artisan::call('route:clear');
        } catch (\Exception $e) {
            // Ignore
        }

        // 1. Create/Update 'Dokumen Kedaluwarsa'
        $module = \App\Models\Module::updateOrCreate(
            ['name' => 'Dokumen Kedaluwarsa'],
            [
                'slug' => 'dokumen-kedaluwarsa',
                'description' => 'Akses dokumen yang telah kedaluwarsa.',
                'url' => '/buku-saku/expired',
                'tab_type' => 'current',
                'icon' => 'clock',
                'group' => 'Buku Saku',
                'status' => 1,
                'order' => 4, // Intended order
            ]
        );

        // 2. Update orders for other modules
        $orders = [
            'buku-saku' => 1,
            'buku-saku-favorites' => 2,
            'buku-saku-approval' => 3,
            'dokumen-kedaluwarsa' => 4,
            'buku-saku-history' => 5,
            'buku-saku-upload' => 6,
        ];

        foreach ($orders as $slug => $order) {
            \App\Models\Module::where('slug', $slug)->update(['order' => $order]);
        }

        $user = Auth::user();
        $accessCheck = "Belum login";
        if ($user) {
            $hasAccess = $user->moduleAccesses()->where('module_id', $module->id)->exists();
            $accessCheck = $hasAccess ? "<span style='color:green'>AKSES AKTIF</span>" : "<span style='color:red'>TIDAK ADA AKSES (Silakan update di Management User)</span>";
        }
        
        return "
        <h1>Perbaikan Modul Selesai</h1>
        <p>Modul 'Dokumen Kedaluwarsa' berhasil ditambahkan/diupdate.</p>
        <p>Status untuk User saat ini (" . ($user->name ?? 'Guest') . "): $accessCheck</p>
        <p>Urutan modul telah diperbarui:</p>
        <ul>
            <li>Beranda (1)</li>
            <li>Dokumen Favorit (2)</li>
            <li>Pengecekan File (3)</li>
            <li>Dokumen Kedaluwarsa (4)</li>
            <li>Riwayat Dokumen (5)</li>
            <li>Upload Dokumen (6)</li>
        </ul>
        <p><a href='" . url('/management-user') . "'>Kembali ke Management User</a></p>
        ";
    });

    Route::get('/fix-db-access', function () {
        $output = "<h1>Perbaikan Database & Hak Akses</h1>";
        
        try {
            // 1. Cek & Buat Modul
            $module = \App\Models\Module::where('name', 'Dokumen Kedaluwarsa')->first();
            if (!$module) {
                $output .= "<p style='color:orange'>Modul 'Dokumen Kedaluwarsa' tidak ditemukan. Membuat baru...</p>";
                $module = \App\Models\Module::create([
                    'name' => 'Dokumen Kedaluwarsa',
                    'slug' => 'dokumen-kedaluwarsa',
                    'url' => '/buku-saku/expired',
                    'icon' => 'clock',
                    'group' => 'Buku Saku',
                    'status' => true,
                    'order' => 4
                ]);
                $output .= "<p style='color:green'>Modul berhasil dibuat (ID: {$module->id}).</p>";
            } else {
                $output .= "<p style='color:green'>Modul 'Dokumen Kedaluwarsa' ditemukan (ID: {$module->id}).</p>";
            }

            // 2. Cek User Saat Ini
            $user = Auth::user();
            if ($user) {
                $output .= "<p>User Login: <strong>{$user->name}</strong> (ID: {$user->id})</p>";
                
                // Cek Akses
                $access = \App\Models\ModuleAccess::where('user_id', $user->id)
                    ->where('module_id', $module->id)
                    ->first();

                if ($access) {
                    $output .= "<p style='color:blue'>User sudah memiliki akses. (Resetting permissions...)</p>";
                    // Update existing
                    $access->update([
                        'can_read' => true,
                        'can_write' => true, 
                        'can_delete' => true,
                        'show_on_dashboard' => false
                    ]);
                } else {
                    $output .= "<p style='color:orange'>User belum punya akses. Menambahkan...</p>";
                    // Create new
                    \App\Models\ModuleAccess::create([
                        'user_id' => $user->id,
                        'module_id' => $module->id,
                        'can_read' => true,
                        'can_write' => true,
                        'can_delete' => true,
                        'show_on_dashboard' => false
                    ]);
                }
                $output .= "<p style='color:green'><strong>SUKSES!</strong> Hak akses untuk user ini telah diperbaiki secara paksa.</p>";
            } else {
                $output .= "<p style='color:red'>Silakan Login terlebih dahulu untuk memperbaiki akses user Anda.</p>";
            }
            
        } catch (\Exception $e) {
            $output .= "<p style='color:red; font-weight:bold'>ERROR: " . $e->getMessage() . "</p>";
            $output .= "<pre>" . $e->getTraceAsString() . "</pre>";
        }

        $output .= "<br><a href='" . url('/management-user') . "'>Kembali ke Management User</a>";
        return $output;
    });
});

require __DIR__.'/auth.php';
