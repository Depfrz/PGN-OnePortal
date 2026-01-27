<?php

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\IntegrasiSistemController;
use App\Http\Controllers\ManagementUserController;
use App\Http\Controllers\ListPengawasanController;
use App\Http\Controllers\BukuSakuController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\HistoryController;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Auth;

Route::get('/', function () {
    return view('welcome');
});

// --- HELPER UNTUK FIX HOSTING (Cache & Route Clear) ---
Route::get('/fix-hosting', function() {
    try {
        Artisan::call('optimize:clear'); // Clears all caches (view, cache, route, config, compiled)
        return "<h1>BERHASIL!</h1> <p>Cache hosting sudah dibersihkan.</p> <p>Silakan kembali ke <a href='/dashboard'>Dashboard</a> dan coba lagi.</p>";
    } catch (\Exception $e) {
        return "GAGAL: " . $e->getMessage();
    }
});

Route::get('/dashboard', [DashboardController::class, 'index'])
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

Route::middleware(['auth', 'verified'])->group(function () {
    // Integrasi Sistem Routes
    Route::get('/integrasi-sistem', [IntegrasiSistemController::class, 'index'])
        ->name('integrasi-sistem.index');
        
    Route::get('/integrasi-sistem/tambah', [IntegrasiSistemController::class, 'create'])
        ->middleware('role:Supervisor|Admin') // Restricted
        ->name('integrasi-sistem.create');

    Route::post('/integrasi-sistem/tambah', [IntegrasiSistemController::class, 'store'])
        ->middleware(['role:Supervisor|Admin', 'throttle:6,1']) // Restricted & Throttled (6 requests/min)
        ->name('integrasi-sistem.store');

    Route::get('/integrasi-sistem/{module}/edit', [IntegrasiSistemController::class, 'edit'])
        ->middleware('role:Supervisor|Admin')
        ->name('integrasi-sistem.edit');

    Route::put('/integrasi-sistem/{module}', [IntegrasiSistemController::class, 'update'])
        ->middleware('role:Supervisor|Admin')
        ->name('integrasi-sistem.update');

    Route::delete('/integrasi-sistem/{module}', [IntegrasiSistemController::class, 'destroy'])
        ->middleware('role:Supervisor|Admin')
        ->name('integrasi-sistem.destroy');

    // Management User Routes
    Route::middleware(['role:Admin'])->prefix('management-user')->name('management-user.')->group(function () {
        Route::get('/', [ManagementUserController::class, 'index'])->name('index');
        Route::post('/', [ManagementUserController::class, 'store'])->name('store');
        Route::put('/{user}', [ManagementUserController::class, 'update'])->name('update');
        Route::delete('/{user}', [ManagementUserController::class, 'destroy'])->name('destroy');
        Route::patch('/{user}/role', [ManagementUserController::class, 'updateRole'])->name('update-role');
        Route::patch('/{user}/access', [ManagementUserController::class, 'updateAccess'])->name('update-access');
        Route::patch('/{user}/password', [ManagementUserController::class, 'resetPassword'])->name('reset-password');
    });

    Route::get('/history', [HistoryController::class, 'index'])->name('history');

    // Notifications
    Route::get('/notifications', [NotificationController::class, 'index'])->name('notifications.index')->middleware(['auth', 'verified']);
    Route::post('/notifications/mark-read/{id}', [NotificationController::class, 'markAsRead'])->name('notifications.mark-read')->middleware(['auth', 'verified']);
    Route::post('/notifications/mark-all-read', [NotificationController::class, 'markAllRead'])->name('notifications.mark-all-read')->middleware(['auth', 'verified']);

    Route::get('/list-pengawasan', [ListPengawasanController::class, 'index'])->name('list-pengawasan.index');
    
    // Kegiatan Routes
    Route::get('/list-pengawasan/{id}/kegiatan', [ListPengawasanController::class, 'kegiatanIndex'])
        ->whereNumber('id')
        ->name('list-pengawasan.kegiatan.index');
    Route::post('/list-pengawasan/{id}/kegiatan', [ListPengawasanController::class, 'storeKegiatan'])
        ->whereNumber('id')
        ->name('list-pengawasan.kegiatan.store');
    Route::get('/list-pengawasan/kegiatan/{activity}', [ListPengawasanController::class, 'showKegiatan'])
        ->whereNumber('activity')
        ->name('list-pengawasan.kegiatan.show');
    Route::put('/list-pengawasan/kegiatan/{activity}', [ListPengawasanController::class, 'updateKegiatan'])
        ->whereNumber('activity')
        ->name('list-pengawasan.kegiatan.update');
    Route::delete('/list-pengawasan/kegiatan/{activity}', [ListPengawasanController::class, 'destroyKegiatan'])
        ->whereNumber('activity')
        ->name('list-pengawasan.kegiatan.destroy');

    // Kegiatan Helper Routes (for show-kegiatan.blade.php)
    Route::patch('/list-pengawasan/kegiatan/{activity}/status', [ListPengawasanController::class, 'updateStatusKegiatan'])
        ->whereNumber('activity');
    Route::patch('/list-pengawasan/kegiatan/{activity}/deadline', [ListPengawasanController::class, 'updateDeadlineKegiatan'])
        ->whereNumber('activity');
    Route::patch('/list-pengawasan/kegiatan/{activity}/keterangan', [ListPengawasanController::class, 'updateKeteranganKegiatan'])
        ->whereNumber('activity');
    Route::post('/list-pengawasan/kegiatan/{activity}/bukti', [ListPengawasanController::class, 'uploadBuktiKegiatan'])
        ->whereNumber('activity');
    Route::delete('/list-pengawasan/kegiatan/{activity}/bukti', [ListPengawasanController::class, 'deleteBuktiKegiatan'])
        ->whereNumber('activity');
    Route::post('/list-pengawasan/kegiatan/{activity}/keterangan/bukti', [ListPengawasanController::class, 'uploadBuktiKeteranganKegiatan'])
        ->whereNumber('activity');
    Route::delete('/list-pengawasan/kegiatan/{activity}/keterangan/bukti', [ListPengawasanController::class, 'deleteBuktiKeteranganKegiatan'])
        ->whereNumber('activity');

    Route::get('/list-pengawasan/{id}', [ListPengawasanController::class, 'show'])
        ->whereNumber('id')
        ->name('list-pengawasan.show');
    Route::post('/list-pengawasan', [ListPengawasanController::class, 'store'])
        ->name('list-pengawasan.store');
    Route::patch('/list-pengawasan/{id}/keterangan', [ListPengawasanController::class, 'updateKeterangan'])
        ->whereNumber('id')
        ->name('list-pengawasan.update-keterangan');
    Route::patch('/list-pengawasan/{id}', [ListPengawasanController::class, 'updatePengawas'])
        ->whereNumber('id')
        ->name('list-pengawasan.update');
    Route::patch('/list-pengawasan/{id}/status', [ListPengawasanController::class, 'updateStatus'])
        ->whereNumber('id')
        ->name('list-pengawasan.update-status');
    Route::patch('/list-pengawasan/{id}/deadline', [ListPengawasanController::class, 'updateDeadline'])
        ->whereNumber('id')
        ->name('list-pengawasan.update-deadline');

    // Management Keterangan Options Routes
    Route::post('/list-pengawasan/keterangan', [ListPengawasanController::class, 'storeOption'])
        ->name('list-pengawasan.keterangan.store');
    Route::patch('/list-pengawasan/keterangan/rename', [ListPengawasanController::class, 'renameOption'])
        ->name('list-pengawasan.keterangan.rename');
    Route::delete('/list-pengawasan/keterangan', [ListPengawasanController::class, 'destroyOption'])
        ->name('list-pengawasan.keterangan.destroy');

    // Bukti Routes (Project Level)
    Route::post('/list-pengawasan/{id}/bukti', [ListPengawasanController::class, 'uploadBukti'])
        ->whereNumber('id')
        ->name('list-pengawasan.bukti.store');
    Route::delete('/list-pengawasan/{id}/bukti', [ListPengawasanController::class, 'deleteBukti'])
        ->whereNumber('id')
        ->name('list-pengawasan.bukti.destroy');
        
    // Bukti Keterangan Routes (Project Level)
    Route::post('/list-pengawasan/{id}/keterangan/bukti', [ListPengawasanController::class, 'uploadBuktiKeterangan'])
        ->whereNumber('id')
        ->name('list-pengawasan.keterangan.bukti.store');
    Route::delete('/list-pengawasan/{id}/keterangan/bukti', [ListPengawasanController::class, 'deleteBuktiKeterangan'])
        ->whereNumber('id')
        ->name('list-pengawasan.keterangan.bukti.destroy');

    Route::delete('/list-pengawasan/{id}', [ListPengawasanController::class, 'destroy'])
        ->whereNumber('id')
        ->name('list-pengawasan.destroy');

    // Buku Saku Routes
    Route::prefix('buku-saku')->name('buku-saku.')->group(function () {
        // Static Routes (Must be before wildcard routes)
        Route::get('/', [BukuSakuController::class, 'index'])->name('index');
        Route::get('/upload', [BukuSakuController::class, 'upload'])->name('upload');
        Route::post('/', [BukuSakuController::class, 'store'])->name('store');
        
        Route::get('/approval', [BukuSakuController::class, 'approvalIndex'])->name('approval');
        Route::get('/favorites', [BukuSakuController::class, 'favorites'])->name('favorites');
        Route::get('/history', [BukuSakuController::class, 'history'])->name('history');
        Route::get('/hapus-dokumen', [BukuSakuController::class, 'hapusDokumenIndex'])->name('hapus-dokumen');
        
        Route::post('/tags', [BukuSakuController::class, 'storeTag'])->name('tags.store');
        Route::delete('/tags/{id}', [BukuSakuController::class, 'destroyTag'])->name('tags.destroy');
        
        // Dynamic Routes (Wildcard)
        Route::get('/{document}', [BukuSakuController::class, 'show'])->name('show');
        Route::get('/{document}/edit', [BukuSakuController::class, 'edit'])->name('edit');
        Route::put('/{document}', [BukuSakuController::class, 'update'])->name('update');
        Route::delete('/{document}', [BukuSakuController::class, 'destroy'])->name('destroy');
        
        Route::get('/{document}/download', [BukuSakuController::class, 'download'])->name('download');
        Route::get('/{document}/preview', [BukuSakuController::class, 'preview'])->name('preview');
        Route::post('/{document}/favorite', [BukuSakuController::class, 'toggleFavorite'])->name('toggle-favorite');
    });

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
