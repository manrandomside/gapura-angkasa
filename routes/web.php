<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\DashboardController;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    return Inertia::render('Welcome', [
        'canLogin' => Route::has('login'),
        'canRegister' => Route::has('register'),
        'laravelVersion' => Application::VERSION,
        'phpVersion' => PHP_VERSION,
    ]);
});

// Dashboard Routes (Tanpa Middleware untuk development)
Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
Route::get('/data-karyawan', [DashboardController::class, 'employees'])->name('employees');
Route::get('/organisasi', [DashboardController::class, 'organizations'])->name('organizations');
Route::get('/laporan', [DashboardController::class, 'reports'])->name('reports');
Route::get('/pengaturan', [DashboardController::class, 'settings'])->name('settings');

// Route asli dengan middleware (untuk production nanti)
/*
Route::get('/dashboard', function () {
    return Inertia::render('Dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});
*/

require __DIR__.'/auth.php';