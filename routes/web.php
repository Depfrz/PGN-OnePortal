<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::get('/integrasi-sistem', function () {
    return view('integrasi-sistem.index');
})->middleware(['auth', 'verified'])->name('integrasi-sistem.index');

Route::get('/integrasi-sistem/tambah', function () {
    return view('integrasi-sistem.create');
})->middleware(['auth', 'verified'])->name('integrasi-sistem.create');

Route::get('/management-user', function () {
    return view('management-user');
})->middleware(['auth', 'verified'])->name('management-user');

Route::get('/history', function () {
    return view('history');
})->middleware(['auth', 'verified'])->name('history');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
