<?php

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\EmployeeController;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

// Dashboard Routes (No middleware untuk development)
Route::get('/', [DashboardController::class, 'index'])->name('dashboard');
Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

// Management Karyawan Routes (Updated from "Data Karyawan")
Route::prefix('management-karyawan')->group(function () {
    Route::get('/', [EmployeeController::class, 'index'])->name('employees.index');
    Route::get('/create', [EmployeeController::class, 'create'])->name('employees.create');
    Route::post('/', [EmployeeController::class, 'store'])->name('employees.store');
    Route::get('/import', function () {
        return Inertia::render('Employees/Import');
    })->name('employees.import');
    Route::post('/import', [EmployeeController::class, 'import'])->name('employees.import.process');
    Route::get('/export', [EmployeeController::class, 'export'])->name('employees.export');
    Route::get('/suggestions', [EmployeeController::class, 'suggestions'])->name('employees.suggestions');
    Route::post('/bulk-action', [EmployeeController::class, 'bulkAction'])->name('employees.bulk');
    Route::get('/{employee}', [EmployeeController::class, 'show'])->name('employees.show');
    Route::get('/{employee}/edit', [EmployeeController::class, 'edit'])->name('employees.edit');
    Route::put('/{employee}', [EmployeeController::class, 'update'])->name('employees.update');
    Route::delete('/{employee}', [EmployeeController::class, 'destroy'])->name('employees.destroy');
});

// Total Karyawan Route (same as management karyawan - untuk menu Total Karyawan)
Route::get('/total-karyawan', [EmployeeController::class, 'index'])->name('total.karyawan');

// Legacy Data Karyawan Routes (Keep for backward compatibility, redirect to new routes)
Route::prefix('data-karyawan')->group(function () {
    Route::get('/', function () {
        return redirect()->route('employees.index');
    });
    Route::get('/create', function () {
        return redirect()->route('employees.create');
    });
    Route::get('/import', function () {
        return redirect()->route('employees.import');
    });
    Route::get('/{id}', function ($id) {
        return redirect()->route('employees.show', $id);
    });
    Route::get('/{id}/edit', function ($id) {
        return redirect()->route('employees.edit', $id);
    });
});

// Organisasi Routes
Route::prefix('organisasi')->group(function () {
    Route::get('/', [DashboardController::class, 'organizations'])->name('organizations.index');
    Route::get('/struktur', function () {
        return Inertia::render('Organizations/Structure');
    })->name('organizations.structure');
    Route::get('/divisi', function () {
        return Inertia::render('Organizations/Divisions');
    })->name('organizations.divisions');
});

// Laporan Routes  
Route::prefix('laporan')->group(function () {
    Route::get('/', [DashboardController::class, 'reports'])->name('reports.index');
    Route::get('/karyawan', function () {
        return Inertia::render('Reports/Employees');
    })->name('reports.employees');
    Route::get('/organisasi', function () {
        return Inertia::render('Reports/Organizations');
    })->name('reports.organizations');
    Route::get('/export', function () {
        return Inertia::render('Reports/Export');
    })->name('reports.export');
    Route::get('/generate', function () {
        return Inertia::render('Reports/Generate');
    })->name('reports.generate');
});

// Pengaturan Routes
Route::prefix('pengaturan')->group(function () {
    Route::get('/', [DashboardController::class, 'settings'])->name('settings.index');
    Route::get('/sistem', function () {
        return Inertia::render('Settings/System');
    })->name('settings.system');
    Route::get('/pengguna', function () {
        return Inertia::render('Settings/Users');
    })->name('settings.users');
});

// API Routes untuk AJAX calls
Route::prefix('api')->group(function () {
    Route::get('/stats', [DashboardController::class, 'getStats'])->name('api.stats');
    Route::get('/organization-stats', [DashboardController::class, 'getOrganizationStats'])->name('api.organization-stats');
    // API Routes for Employee Dashboard Data
    Route::get('/employees/dashboard-data', [EmployeeController::class, 'getDashboardData'])->name('api.employees.dashboard');
});

// Development Routes (untuk testing tanpa login)
Route::get('/dev/clear-cache', function () {
    \Illuminate\Support\Facades\Artisan::call('cache:clear');
    \Illuminate\Support\Facades\Artisan::call('config:clear');
    \Illuminate\Support\Facades\Artisan::call('view:clear');
    \Illuminate\Support\Facades\Artisan::call('route:clear');
    
    return response()->json(['message' => 'Cache cleared successfully']);
})->name('dev.clear-cache');

Route::get('/dev/migrate-fresh', function () {
    \Illuminate\Support\Facades\Artisan::call('migrate:fresh --seed');
    
    return response()->json(['message' => 'Database migrated and seeded successfully']);
})->name('dev.migrate-fresh');

// Welcome route (redirect ke dashboard)
Route::get('/welcome', function () {
    return redirect()->route('dashboard');
});

// Handle 404 untuk development
Route::fallback(function () {
    return Inertia::render('Error/404');
});