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

    // Notification Routes
    Route::get('/notifications', [App\Http\Controllers\NotificationController::class, 'index'])->name('notifications.index');
    Route::post('/notifications/mark-read/{id}', [App\Http\Controllers\NotificationController::class, 'markAsRead'])->name('notifications.mark-read');
    Route::post('/notifications/mark-all-read', [App\Http\Controllers\NotificationController::class, 'markAllRead'])->name('notifications.mark-all-read');
});

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
