<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\IntegrasiSistemController;
use App\Http\Controllers\ManagementUserController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware(['auth', 'verified'])->group(function () {
    // Integrasi Sistem Routes
    Route::get('/integrasi-sistem', [IntegrasiSistemController::class, 'index'])
        ->name('integrasi-sistem.index');
        
    Route::get('/integrasi-sistem/tambah', [IntegrasiSistemController::class, 'create'])
        ->middleware('role:Supervisor|Admin') // Restricted
        ->name('integrasi-sistem.create');

    // Management User Routes
    Route::get('/management-user', [ManagementUserController::class, 'index'])
        ->middleware('role:Admin') // Strictly Admin
        ->name('management-user');

    Route::get('/history', function () {
        return view('history');
    })->name('history');
});

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
