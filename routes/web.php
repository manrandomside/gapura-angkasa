<?php

use App\Http\Controllers\ProfileController;
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
*/

// Public routes (no middleware untuk debugging)
Route::get('/', function () {
    try {
        // Get basic statistics safely
        $statistics = [];
        if (class_exists('\App\Models\Employee')) {
            try {
                $statistics = [
                    'total_employees' => \App\Models\Employee::count(),
                    'active_employees' => \App\Models\Employee::where('status', 'active')->count(),
                    'pegawai_tetap' => \App\Models\Employee::where('status_pegawai', 'PEGAWAI TETAP')->count(),
                    'tad' => \App\Models\Employee::where('status_pegawai', 'TAD')->count(),
                    'male_employees' => \App\Models\Employee::where('jenis_kelamin', 'L')->count(),
                    'female_employees' => \App\Models\Employee::where('jenis_kelamin', 'P')->count(),
                    'total_organizations' => \App\Models\Organization::count(),
                ];
            } catch (\Exception $e) {
                $statistics = [
                    'total_employees' => 0,
                    'active_employees' => 0,
                    'pegawai_tetap' => 0,
                    'tad' => 0,
                    'male_employees' => 0,
                    'female_employees' => 0,
                    'total_organizations' => 0,
                ];
            }
        }

        return Inertia::render('Dashboard/Index', [
            'appName' => config('app.name'),
            'appVersion' => '1.0.0',
            'canLogin' => Route::has('login'),
            'canRegister' => Route::has('register'),
            'laravelVersion' => Application::VERSION,
            'phpVersion' => PHP_VERSION,
            'statistics' => $statistics,
        ]);
    } catch (\Exception $e) {
        // Fallback if Dashboard/Index doesn't exist
        return response()->json([
            'message' => 'Dashboard SDM GAPURA ANGKASA',
            'status' => 'OK',
            'error' => $e->getMessage(),
            'action' => 'Please create Dashboard/Index.jsx component or access /employees directly'
        ]);
    }
})->name('dashboard');

// Dashboard Routes - with error handling
Route::prefix('dashboard')->group(function () {
    Route::get('/', function () {
        try {
            return app(DashboardController::class)->index();
        } catch (\Exception $e) {
            return redirect('/employees')->with('info', 'Dashboard dalam pengembangan, dialihkan ke Management Karyawan');
        }
    })->name('dashboard.index');
    
    Route::get('/statistics', function () {
        try {
            return app(DashboardController::class)->getStatistics();
        } catch (\Exception $e) {
            return response()->json([
                'total_employees' => 0,
                'active_employees' => 0,
                'error' => $e->getMessage()
            ]);
        }
    })->name('dashboard.statistics');
});

// Employee Management Routes - Resource dengan tambahan custom routes
Route::prefix('employees')->group(function () {
    // Main CRUD routes
    Route::get('/', [EmployeeController::class, 'index'])->name('employees.index');
    Route::get('/create', [EmployeeController::class, 'create'])->name('employees.create');
    Route::post('/', [EmployeeController::class, 'store'])->name('employees.store');
    Route::get('/{employee}', [EmployeeController::class, 'show'])->name('employees.show');
    Route::get('/{employee}/edit', [EmployeeController::class, 'edit'])->name('employees.edit');
    Route::put('/{employee}', [EmployeeController::class, 'update'])->name('employees.update');
    Route::patch('/{employee}', [EmployeeController::class, 'update'])->name('employees.patch');
    Route::delete('/{employee}', [EmployeeController::class, 'destroy'])->name('employees.destroy');
    
    // Additional functionality routes
    Route::get('/dashboard/data', [EmployeeController::class, 'getDashboardData'])->name('employees.dashboard.data');
    Route::get('/export/csv', [EmployeeController::class, 'export'])->name('employees.export');
    Route::post('/import', [EmployeeController::class, 'import'])->name('employees.import');
    Route::get('/suggestions', [EmployeeController::class, 'suggestions'])->name('employees.suggestions');
    Route::post('/bulk-action', [EmployeeController::class, 'bulkAction'])->name('employees.bulk.action');
    Route::get('/statistics', [EmployeeController::class, 'getStatistics'])->name('employees.statistics');
    Route::get('/search', [EmployeeController::class, 'search'])->name('employees.search');
    Route::get('/{employee}/profile', [EmployeeController::class, 'profile'])->name('employees.profile');
    Route::get('/{employee}/id-card', [EmployeeController::class, 'generateIdCard'])->name('employees.id.card');
    Route::post('/generate-report', [EmployeeController::class, 'generateReport'])->name('employees.generate.report');
    Route::get('/validate-data', [EmployeeController::class, 'validateData'])->name('employees.validate.data');
});

// Legacy routes redirect untuk backward compatibility
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

// Management Karyawan Routes - Alias untuk Employee routes
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

// Organization Routes
Route::prefix('organisasi')->group(function () {
    Route::get('/', function () {
        try {
            return app(OrganizationController::class)->index();
        } catch (\Exception $e) {
            return Inertia::render('Organizations/Index', [
                'organizations' => [],
                'error' => 'Controller not found, please create OrganizationController'
            ]);
        }
    })->name('organizations.index');
    
    // Temporary routes until controller is created
    Route::get('/create', function () {
        return Inertia::render('Organizations/Create');
    })->name('organizations.create');
    
    Route::get('/struktur', function () {
        return Inertia::render('Organizations/Structure');
    })->name('organizations.structure');
    
    Route::get('/divisi', function () {
        return Inertia::render('Organizations/Divisions');
    })->name('organizations.divisions');
});

// Reports Routes  
Route::prefix('laporan')->group(function () {
    Route::get('/', function () {
        return Inertia::render('Reports/Index');
    })->name('reports.index');
    
    Route::get('/karyawan', function () {
        return Inertia::render('Reports/Employees');
    })->name('reports.employees');
    
    Route::get('/organisasi', function () {
        return Inertia::render('Reports/Organizations');
    })->name('reports.organizations');
    
    Route::get('/statistik', function () {
        return Inertia::render('Reports/Statistics');
    })->name('reports.statistics');
    
    Route::get('/export', function () {
        return Inertia::render('Reports/Export');
    })->name('reports.export');
    
    Route::get('/generate', function () {
        return Inertia::render('Reports/Generate');
    })->name('reports.generate');
    
    // Report generation endpoints
    Route::post('/generate-employee-report', [EmployeeController::class, 'generateReport'])->name('reports.generate.employee');
});

// Settings Routes
Route::prefix('pengaturan')->group(function () {
    Route::get('/', function () {
        return Inertia::render('Settings/Index');
    })->name('settings.index');
    
    Route::get('/sistem', function () {
        return Inertia::render('Settings/System');
    })->name('settings.system');
    
    Route::get('/pengguna', function () {
        return Inertia::render('Settings/Users');
    })->name('settings.users');
    
    Route::get('/backup', function () {
        return Inertia::render('Settings/Backup');
    })->name('settings.backup');
    
    Route::get('/import-export', function () {
        return Inertia::render('Settings/ImportExport');
    })->name('settings.import.export');
});

// API Routes untuk AJAX calls - with error handling
Route::prefix('api')->group(function () {
    // Employee API endpoints
    Route::get('/employees/search', [EmployeeController::class, 'search'])->name('api.employees.search');
    Route::get('/employees/statistics', [EmployeeController::class, 'getStatistics'])->name('api.employees.statistics');
    Route::get('/employees/{employee}/profile', [EmployeeController::class, 'profile'])->name('api.employees.profile');
    Route::get('/employees/validate', [EmployeeController::class, 'validateData'])->name('api.employees.validate');
    
    // Safe API endpoints
    Route::get('/dashboard/statistics', function () {
        try {
            if (class_exists('\App\Http\Controllers\DashboardController')) {
                return app(DashboardController::class)->getStatistics();
            }
            
            // Fallback statistics
            return response()->json([
                'total_employees' => \App\Models\Employee::count(),
                'active_employees' => \App\Models\Employee::where('status', 'active')->count(),
                'pegawai_tetap' => \App\Models\Employee::where('status_pegawai', 'PEGAWAI TETAP')->count(),
                'tad' => \App\Models\Employee::where('status_pegawai', 'TAD')->count(),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'total_employees' => 0,
                'active_employees' => 0,
                'error' => $e->getMessage()
            ]);
        }
    })->name('api.dashboard.statistics');
    
    Route::get('/dashboard/charts', function () {
        try {
            return response()->json([
                'by_organization' => [],
                'by_status' => [],
                'monthly_hires' => [],
            ]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()]);
        }
    })->name('api.dashboard.charts');
});

// File Management Routes
Route::prefix('files')->group(function () {
    Route::post('/upload-employee-photo', function () {
        return response()->json(['message' => 'Photo upload feature coming soon']);
    })->name('files.upload.employee.photo');
    
    Route::post('/upload-document', function () {
        return response()->json(['message' => 'Document upload feature coming soon']);
    })->name('files.upload.document');
});

// Utility Routes
Route::prefix('utilities')->group(function () {
    Route::get('/health-check', function () {
        try {
            // Test database connection
            \DB::connection()->getPdo();
            $dbStatus = 'Connected';
        } catch (\Exception $e) {
            $dbStatus = 'Error: ' . $e->getMessage();
        }
        
        return response()->json([
            'status' => 'OK',
            'timestamp' => now(),
            'database' => $dbStatus,
            'version' => '1.0.0',
            'employees_count' => \App\Models\Employee::count(),
            'organizations_count' => \App\Models\Organization::count(),
        ]);
    })->name('utilities.health.check');
    
    Route::get('/clear-cache', function () {
        try {
            \Artisan::call('cache:clear');
            \Artisan::call('config:clear');
            \Artisan::call('view:clear');
            \Artisan::call('route:clear');
            
            return response()->json(['message' => 'Cache cleared successfully']);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    })->name('utilities.clear.cache');
});

// Development routes (hanya untuk development)
if (app()->environment('local')) {
    Route::get('/test-seeder', function () {
        try {
            \Artisan::call('db:seed', ['--class' => 'SDMEmployeeSeeder']);
            return response()->json([
                'message' => 'Seeder executed successfully',
                'output' => \Artisan::output(),
                'employees_count' => \App\Models\Employee::count(),
                'organizations_count' => \App\Models\Organization::count(),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Seeder failed',
                'error' => $e->getMessage()
            ], 500);
        }
    })->name('test.seeder');
    
    Route::get('/test-database', function () {
        try {
            $employees = \App\Models\Employee::take(5)->get();
            $organizations = \App\Models\Organization::all();
            
            return response()->json([
                'database_status' => 'Connected',
                'employees_count' => \App\Models\Employee::count(),
                'organizations_count' => \App\Models\Organization::count(),
                'sample_employees' => $employees,
                'organizations' => $organizations
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'database_status' => 'Error',
                'error' => $e->getMessage()
            ], 500);
        }
    })->name('test.database');
    
    Route::get('/migrate-fresh', function () {
        try {
            \Artisan::call('migrate:fresh', ['--seed' => true]);
            return response()->json([
                'message' => 'Database migrated and seeded successfully',
                'output' => \Artisan::output(),
                'employees_count' => \App\Models\Employee::count(),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'error' => $e->getMessage()
            ], 500);
        }
    })->name('test.migrate.fresh');
}

// Catch-all route untuk SPA (Single Page Application)
Route::fallback(function () {
    return Inertia::render('Error/404', [
        'status' => 404,
        'message' => 'Halaman tidak ditemukan'
    ]);
});