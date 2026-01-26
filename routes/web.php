<?php

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\IntegrasiSistemController;
use App\Http\Controllers\ManagementUserController;
use App\Http\Controllers\ListPengawasanController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\HistoryController;

Route::get('/', function () {
    return view('welcome');
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

    Route::get('/list-pengawasan', [ListPengawasanController::class, 'index'])->name('list-pengawasan.index');
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
    Route::patch('/list-pengawasan/{id}/pengawas-users', [ListPengawasanController::class, 'replacePengawasUser'])
        ->whereNumber('id')
        ->name('list-pengawasan.replace-pengawas-user');
    Route::delete('/list-pengawasan/{id}/pengawas-users', [ListPengawasanController::class, 'removePengawasUser'])
        ->whereNumber('id')
        ->name('list-pengawasan.remove-pengawas-user');
    Route::post('/list-pengawasan/{id}/bukti', [ListPengawasanController::class, 'uploadBukti'])
        ->whereNumber('id')
        ->name('list-pengawasan.upload-bukti');
    Route::delete('/list-pengawasan/{id}/bukti', [ListPengawasanController::class, 'deleteBukti'])
        ->whereNumber('id')
        ->name('list-pengawasan.delete-bukti');
    Route::post('/list-pengawasan/{id}/keterangan/bukti', [ListPengawasanController::class, 'uploadBuktiKeterangan'])
        ->whereNumber('id')
        ->name('list-pengawasan.upload-bukti-keterangan');
    Route::delete('/list-pengawasan/{id}/keterangan/bukti', [ListPengawasanController::class, 'deleteBuktiKeterangan'])
        ->whereNumber('id')
        ->name('list-pengawasan.delete-bukti-keterangan');
    Route::delete('/list-pengawasan/{id}', [ListPengawasanController::class, 'destroy'])
        ->whereNumber('id')
        ->name('list-pengawasan.destroy');
    Route::patch('/list-pengawasan/keterangan/rename', [ListPengawasanController::class, 'renameOption'])
        ->name('list-pengawasan.keterangan.rename');
    Route::delete('/list-pengawasan/keterangan', [ListPengawasanController::class, 'deleteOption'])
        ->name('list-pengawasan.keterangan.delete');

    // Notification Routes
    Route::get('/notifications', [App\Http\Controllers\NotificationController::class, 'index'])->name('notifications.index');
    Route::match(['get', 'post'], '/notifications/mark-read/{id}', [App\Http\Controllers\NotificationController::class, 'markAsRead'])->name('notifications.mark-read');
    Route::match(['get', 'post'], '/notifications/mark-all-read', [App\Http\Controllers\NotificationController::class, 'markAllRead'])->name('notifications.mark-all-read');
});

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // Buku Saku Routes
    Route::prefix('buku-saku')->name('buku-saku.')->group(function () {
        Route::get('/', [App\Http\Controllers\BukuSakuController::class, 'index'])->name('index');
        Route::get('/upload', [App\Http\Controllers\BukuSakuController::class, 'upload'])->name('upload');
        Route::post('/store', [App\Http\Controllers\BukuSakuController::class, 'store'])->name('store');
        Route::delete('/{document}', [App\Http\Controllers\BukuSakuController::class, 'destroy'])->name('destroy');
        Route::get('/favorites', [App\Http\Controllers\BukuSakuController::class, 'favorites'])->name('favorites');
        Route::post('/{id}/favorite', [App\Http\Controllers\BukuSakuController::class, 'toggleFavorite'])->name('toggle-favorite');
        Route::get('/history', [App\Http\Controllers\BukuSakuController::class, 'history'])->name('history');
        Route::get('/download/{document}', [App\Http\Controllers\BukuSakuController::class, 'download'])->name('download');
        Route::get('/preview/{document}', [App\Http\Controllers\BukuSakuController::class, 'preview'])->name('preview');
        
        // Approval Workflow (Now Management)
        Route::get('/approval', [App\Http\Controllers\BukuSakuController::class, 'approvalIndex'])->name('approval');
        
        // Tags Management
        Route::post('/tags', [App\Http\Controllers\BukuSakuController::class, 'storeTag'])->name('tags.store');
        Route::delete('/tags/{id}', [App\Http\Controllers\BukuSakuController::class, 'destroyTag'])->name('tags.destroy');

        // Edit/Update
        Route::get('/{id}/edit', [App\Http\Controllers\BukuSakuController::class, 'edit'])->name('edit');
        Route::put('/{id}', [App\Http\Controllers\BukuSakuController::class, 'update'])->name('update');
        
        // Detail View (Must be last to avoid conflict with specific sub-routes)
        Route::get('/{document}', [App\Http\Controllers\BukuSakuController::class, 'show'])->name('show');
    });
});

require __DIR__.'/auth.php';
