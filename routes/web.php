<?php

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\EmployeeController;
use App\Http\Controllers\OrganizationController;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

/*
|--------------------------------------------------------------------------
| Web Routes for GAPURA ANGKASA SDM System
|--------------------------------------------------------------------------
|
| Routes tanpa middleware untuk kemudahan development
| Base color: putih dengan hover hijau (#439454)
| Sistem SDM PT Gapura Angkasa - Bandar Udara Ngurah Rai
|
*/

// =====================================================
// ROOT & DASHBOARD ROUTES
// =====================================================

// Root redirect ke dashboard
Route::get('/', function () {
    return redirect('/dashboard');
})->name('home');

// Main dashboard route
Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

// Dashboard API routes
Route::prefix('dashboard')->group(function () {
    Route::get('/statistics', [DashboardController::class, 'getStatistics'])->name('dashboard.statistics');
    Route::get('/charts', [DashboardController::class, 'getChartData'])->name('dashboard.charts');
    Route::get('/activities', [DashboardController::class, 'getRecentActivities'])->name('dashboard.activities');
    Route::post('/export', [DashboardController::class, 'exportData'])->name('dashboard.export');
    Route::get('/health', [DashboardController::class, 'healthCheck'])->name('dashboard.health');
});

// =====================================================
// EMPLOYEE MANAGEMENT ROUTES
// =====================================================

Route::prefix('employees')->group(function () {
    // Main CRUD operations
    Route::get('/', [EmployeeController::class, 'index'])->name('employees.index');
    Route::get('/create', [EmployeeController::class, 'create'])->name('employees.create');
    Route::post('/', [EmployeeController::class, 'store'])->name('employees.store');
    Route::get('/{employee}', [EmployeeController::class, 'show'])->name('employees.show');
    Route::get('/{employee}/edit', [EmployeeController::class, 'edit'])->name('employees.edit');
    Route::put('/{employee}', [EmployeeController::class, 'update'])->name('employees.update');
    Route::patch('/{employee}', [EmployeeController::class, 'update'])->name('employees.patch');
    Route::delete('/{employee}', [EmployeeController::class, 'destroy'])->name('employees.destroy');
    
    // Search and filter
    Route::get('/search/api', [EmployeeController::class, 'search'])->name('employees.search');
    Route::get('/suggestions', [EmployeeController::class, 'suggestions'])->name('employees.suggestions');
    
    // Statistics and analytics
    Route::get('/statistics/api', [EmployeeController::class, 'getStatistics'])->name('employees.statistics');
    Route::get('/{employee}/profile', [EmployeeController::class, 'profile'])->name('employees.profile');
    
    // Data operations
    Route::post('/import', [EmployeeController::class, 'import'])->name('employees.import');
    Route::get('/export', [EmployeeController::class, 'export'])->name('employees.export');
    Route::post('/bulk-action', [EmployeeController::class, 'bulkAction'])->name('employees.bulk.action');
    Route::post('/validate', [EmployeeController::class, 'validateData'])->name('employees.validate');
    
    // Reports
    Route::post('/generate-report', [EmployeeController::class, 'generateReport'])->name('employees.generate.report');
    
    // Additional features
    Route::get('/{employee}/id-card', [EmployeeController::class, 'generateIdCard'])->name('employees.id.card');
});

// =====================================================
// ORGANIZATION MANAGEMENT ROUTES
// =====================================================

Route::prefix('organisasi')->group(function () {
    Route::get('/', function () {
        try {
            if (class_exists('App\Http\Controllers\OrganizationController')) {
                return app(OrganizationController::class)->index();
            }
            
            // Fallback jika controller belum ada
            return Inertia::render('Organizations/Index', [
                'organizations' => \App\Models\Organization::all(),
                'message' => 'OrganizationController belum dibuat, menampilkan data dasar'
            ]);
        } catch (\Exception $e) {
            return Inertia::render('Organizations/Index', [
                'organizations' => [],
                'error' => 'Error loading organizations: ' . $e->getMessage()
            ]);
        }
    })->name('organizations.index');
    
    // Placeholder routes untuk future development
    Route::get('/create', function () {
        return Inertia::render('Organizations/Create', [
            'message' => 'Organization create form - coming soon'
        ]);
    })->name('organizations.create');
    
    Route::get('/struktur', function () {
        return Inertia::render('Organizations/Structure', [
            'message' => 'Organization structure view - coming soon'
        ]);
    })->name('organizations.structure');
    
    Route::get('/divisi', function () {
        return Inertia::render('Organizations/Divisions', [
            'message' => 'Divisions management - coming soon'
        ]);
    })->name('organizations.divisions');
});

// =====================================================
// REPORTS ROUTES
// =====================================================

Route::prefix('laporan')->group(function () {
    Route::get('/', function () {
        return Inertia::render('Reports/Index', [
            'message' => 'Reports dashboard - coming soon'
        ]);
    })->name('reports.index');
    
    Route::get('/karyawan', function () {
        return Inertia::render('Reports/Employees', [
            'message' => 'Employee reports - coming soon'
        ]);
    })->name('reports.employees');
    
    Route::get('/organisasi', function () {
        return Inertia::render('Reports/Organizations', [
            'message' => 'Organization reports - coming soon'
        ]);
    })->name('reports.organizations');
    
    Route::get('/statistik', function () {
        return Inertia::render('Reports/Statistics', [
            'message' => 'Statistics reports - coming soon'
        ]);
    })->name('reports.statistics');
    
    Route::get('/export', function () {
        return Inertia::render('Reports/Export', [
            'message' => 'Export center - coming soon'
        ]);
    })->name('reports.export');
    
    Route::get('/generate', function () {
        return Inertia::render('Reports/Generate', [
            'message' => 'Report generator - coming soon'
        ]);
    })->name('reports.generate');
    
    // Report generation API
    Route::post('/generate-employee-report', [EmployeeController::class, 'generateReport'])
        ->name('reports.generate.employee');
});

// =====================================================
// SETTINGS ROUTES
// =====================================================

Route::prefix('pengaturan')->group(function () {
    Route::get('/', function () {
        return Inertia::render('Settings/Index', [
            'message' => 'Settings dashboard - coming soon'
        ]);
    })->name('settings.index');
    
    Route::get('/sistem', function () {
        return Inertia::render('Settings/System', [
            'message' => 'System settings - coming soon'
        ]);
    })->name('settings.system');
    
    Route::get('/pengguna', function () {
        return Inertia::render('Settings/Users', [
            'message' => 'User management - coming soon'
        ]);
    })->name('settings.users');
    
    Route::get('/backup', function () {
        return Inertia::render('Settings/Backup', [
            'message' => 'Backup & restore - coming soon'
        ]);
    })->name('settings.backup');
    
    Route::get('/import-export', function () {
        return Inertia::render('Settings/ImportExport', [
            'message' => 'Import/Export settings - coming soon'
        ]);
    })->name('settings.import.export');
});

// =====================================================
// LEGACY & ALIAS ROUTES
// =====================================================

// Management Karyawan (Indonesian naming) - redirect to employees
Route::prefix('management-karyawan')->group(function () {
    Route::get('/', function () {
        return redirect()->route('employees.index');
    })->name('management.karyawan.index');
    
    Route::get('/tambah', function () {
        return redirect()->route('employees.create');
    })->name('management.karyawan.create');
    
    Route::get('/import', function () {
        return redirect()->route('employees.import');
    })->name('management.karyawan.import');
    
    Route::get('/export', function () {
        return redirect()->route('employees.export');
    })->name('management.karyawan.export');
});

// Legacy data-karyawan routes - redirect to employees
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

// Total karyawan alias
Route::get('/total-karyawan', function () {
    return redirect()->route('employees.index');
})->name('total.karyawan');

// =====================================================
// API ROUTES (for AJAX calls)
// =====================================================

Route::prefix('api')->group(function () {
    // Dashboard API
    Route::get('/dashboard/statistics', [DashboardController::class, 'getStatistics'])
        ->name('api.dashboard.statistics');
    Route::get('/dashboard/charts', [DashboardController::class, 'getChartData'])
        ->name('api.dashboard.charts');
    Route::get('/dashboard/activities', [DashboardController::class, 'getRecentActivities'])
        ->name('api.dashboard.activities');
    
    // Employee API
    Route::get('/employees/search', [EmployeeController::class, 'search'])
        ->name('api.employees.search');
    Route::get('/employees/statistics', [EmployeeController::class, 'getStatistics'])
        ->name('api.employees.statistics');
    Route::get('/employees/{employee}/profile', [EmployeeController::class, 'profile'])
        ->name('api.employees.profile');
    Route::post('/employees/validate', [EmployeeController::class, 'validateData'])
        ->name('api.employees.validate');
    Route::post('/employees/bulk-action', [EmployeeController::class, 'bulkAction'])
        ->name('api.employees.bulk.action');
});

// =====================================================
// FILE MANAGEMENT ROUTES
// =====================================================

Route::prefix('files')->group(function () {
    Route::post('/upload-employee-photo', function () {
        return response()->json([
            'message' => 'Photo upload feature akan segera tersedia',
            'status' => 'planned'
        ]);
    })->name('files.upload.employee.photo');
    
    Route::post('/upload-document', function () {
        return response()->json([
            'message' => 'Document upload feature akan segera tersedia',
            'status' => 'planned'
        ]);
    })->name('files.upload.document');
    
    Route::get('/download-template', function () {
        return response()->json([
            'message' => 'Template download feature akan segera tersedia',
            'status' => 'planned'
        ]);
    })->name('files.download.template');
});

// =====================================================
// UTILITY ROUTES
// =====================================================

Route::prefix('utilities')->group(function () {
    Route::get('/health-check', function () {
        try {
            // Test database connection
            \DB::connection()->getPdo();
            $dbStatus = 'Connected';
            $employeeCount = \App\Models\Employee::count();
            $organizationCount = \App\Models\Organization::count();
        } catch (\Exception $e) {
            $dbStatus = 'Error: ' . $e->getMessage();
            $employeeCount = 0;
            $organizationCount = 0;
        }
        
        return response()->json([
            'system' => 'GAPURA ANGKASA SDM System',
            'status' => 'healthy',
            'database' => $dbStatus,
            'employee_count' => $employeeCount,
            'organization_count' => $organizationCount,
            'laravel_version' => Application::VERSION,
            'php_version' => PHP_VERSION,
            'timestamp' => now()->toISOString(),
            'version' => '1.0.0',
        ]);
    })->name('utilities.health.check');
    
    Route::get('/system-info', function () {
        return response()->json([
            'system_name' => 'GAPURA ANGKASA SDM System',
            'version' => '1.0.0',
            'laravel_version' => Application::VERSION,
            'php_version' => PHP_VERSION,
            'environment' => app()->environment(),
            'timezone' => config('app.timezone'),
            'locale' => config('app.locale'),
            'debug_mode' => config('app.debug'),
        ]);
    })->name('utilities.system.info');
    
    Route::get('/clear-cache', function () {
        try {
            \Artisan::call('cache:clear');
            \Artisan::call('config:clear');
            \Artisan::call('view:clear');
            \Artisan::call('route:clear');
            
            return response()->json([
                'message' => 'Cache cleared successfully',
                'status' => 'success',
                'timestamp' => now()->toISOString()
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error clearing cache: ' . $e->getMessage(),
                'status' => 'error'
            ], 500);
        }
    })->name('utilities.clear.cache');
});

// =====================================================
// DEVELOPMENT ROUTES (hanya untuk local environment)
// =====================================================

if (app()->environment('local', 'development')) {
    Route::prefix('dev')->group(function () {
        Route::get('/test-components', function () {
            return Inertia::render('Dev/TestComponents', [
                'message' => 'Component testing page'
            ]);
        })->name('dev.test.components');
        
        Route::get('/test-seeder', function () {
            try {
                \Artisan::call('db:seed', ['--class' => 'DatabaseSeeder']);
                $output = \Artisan::output();
                
                return response()->json([
                    'message' => 'Seeder executed successfully',
                    'output' => $output,
                    'employees_count' => \App\Models\Employee::count(),
                    'organizations_count' => \App\Models\Organization::count(),
                ]);
            } catch (\Exception $e) {
                return response()->json([
                    'message' => 'Seeder failed',
                    'error' => $e->getMessage()
                ], 500);
            }
        })->name('dev.test.seeder');
        
        Route::get('/test-database', function () {
            try {
                $employees = \App\Models\Employee::take(5)->get();
                $organizations = \App\Models\Organization::all();
                
                return response()->json([
                    'database_status' => 'Connected',
                    'employees_count' => \App\Models\Employee::count(),
                    'organizations_count' => \App\Models\Organization::count(),
                    'sample_employees' => $employees,
                    'organizations' => $organizations,
                    'timestamp' => now()->toISOString()
                ]);
            } catch (\Exception $e) {
                return response()->json([
                    'database_status' => 'Error',
                    'error' => $e->getMessage()
                ], 500);
            }
        })->name('dev.test.database');
        
        Route::get('/migrate-fresh', function () {
            try {
                \Artisan::call('migrate:fresh', ['--seed' => true]);
                $output = \Artisan::output();
                
                return response()->json([
                    'message' => 'Database migrated and seeded successfully',
                    'output' => $output,
                    'employees_count' => \App\Models\Employee::count(),
                    'organizations_count' => \App\Models\Organization::count(),
                ]);
            } catch (\Exception $e) {
                return response()->json([
                    'error' => $e->getMessage()
                ], 500);
            }
        })->name('dev.migrate.fresh');
        
        Route::get('/seed-database', function () {
            try {
                \Artisan::call('db:seed');
                $output = \Artisan::output();
                
                return response()->json([
                    'message' => 'Database seeded successfully',
                    'output' => $output,
                    'employees_count' => \App\Models\Employee::count(),
                    'organizations_count' => \App\Models\Organization::count(),
                ]);
            } catch (\Exception $e) {
                return response()->json([
                    'error' => $e->getMessage()
                ], 500);
            }
        })->name('dev.seed.database');
        
        Route::get('/routes', function () {
            $routes = collect(\Route::getRoutes())->map(function ($route) {
                return [
                    'method' => implode('|', $route->methods()),
                    'uri' => $route->uri(),
                    'name' => $route->getName(),
                    'action' => $route->getActionName(),
                ];
            });
            
            return response()->json([
                'total_routes' => $routes->count(),
                'routes' => $routes->sortBy('uri')->values()
            ]);
        })->name('dev.routes');
    });
}

// =====================================================
// ERROR HANDLING & FALLBACK
// =====================================================

// Catch-all route untuk SPA (Single Page Application)
Route::fallback(function () {
    return Inertia::render('Error/404', [
        'status' => 404,
        'message' => 'Halaman tidak ditemukan',
        'suggestion' => 'Silakan gunakan menu navigasi untuk mengakses halaman yang tersedia.'
    ]);
});

/*
|--------------------------------------------------------------------------
| Route Documentation
|--------------------------------------------------------------------------
|
| MAIN ROUTES:
| - /dashboard                 - Dashboard utama dengan statistik
| - /employees                 - Management karyawan (CRUD lengkap)
| - /organisasi               - Management organisasi
| - /laporan                  - Reports dan statistik
| - /pengaturan               - Settings sistem
|
| API ROUTES:
| - /api/dashboard/*          - Dashboard API endpoints
| - /api/employees/*          - Employee API endpoints
|
| LEGACY/ALIAS ROUTES:
| - /management-karyawan      - Redirect ke /employees
| - /data-karyawan           - Redirect ke /employees
| - /total-karyawan          - Redirect ke /employees
|
| DEVELOPMENT ROUTES (local only):
| - /dev/test-seeder         - Test database seeder
| - /dev/test-database       - Test database connection
| - /dev/migrate-fresh       - Fresh migration dengan seed
| - /dev/routes              - List all available routes
|
| UTILITY ROUTES:
| - /utilities/health-check  - System health check
| - /utilities/clear-cache   - Clear application cache
| - /utilities/system-info   - System information
|
*/