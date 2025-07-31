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
| Enhanced dengan filter sepatu dan ukuran sepatu
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
    
    // Enhanced shoe reports
    Route::get('/sepatu', function () {
        try {
            $shoeStatistics = [
                'total_employees' => \App\Models\Employee::count(),
                'pantofel_count' => \App\Models\Employee::where('jenis_sepatu', 'Pantofel')->count(),
                'safety_shoes_count' => \App\Models\Employee::where('jenis_sepatu', 'Safety Shoes')->count(),
                'no_shoe_data' => \App\Models\Employee::whereNull('jenis_sepatu')->count(),
                'size_distribution' => \App\Models\Employee::select('ukuran_sepatu')
                    ->selectRaw('COUNT(*) as count')
                    ->whereNotNull('ukuran_sepatu')
                    ->groupBy('ukuran_sepatu')
                    ->orderBy('ukuran_sepatu')
                    ->get(),
                'type_by_gender' => \App\Models\Employee::select('jenis_kelamin', 'jenis_sepatu')
                    ->selectRaw('COUNT(*) as count')
                    ->whereNotNull('jenis_sepatu')
                    ->groupBy('jenis_kelamin', 'jenis_sepatu')
                    ->get(),
                'type_by_unit' => \App\Models\Employee::select('unit_organisasi', 'jenis_sepatu')
                    ->selectRaw('COUNT(*) as count')
                    ->whereNotNull('jenis_sepatu')
                    ->whereNotNull('unit_organisasi')
                    ->groupBy('unit_organisasi', 'jenis_sepatu')
                    ->get(),
            ];
            
            return Inertia::render('Reports/Shoes', [
                'statistics' => $shoeStatistics,
                'message' => 'Laporan distribusi sepatu karyawan PT Gapura Angkasa'
            ]);
        } catch (\Exception $e) {
            return Inertia::render('Reports/Shoes', [
                'statistics' => [
                    'total_employees' => 0,
                    'pantofel_count' => 0,
                    'safety_shoes_count' => 0,
                    'no_shoe_data' => 0,
                    'size_distribution' => [],
                    'type_by_gender' => [],
                    'type_by_unit' => [],
                ],
                'error' => 'Error loading shoe statistics: ' . $e->getMessage()
            ]);
        }
    })->name('reports.shoes');
    
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
// API ROUTES (Enhanced untuk filtering sepatu)
// =====================================================

Route::prefix('api')->group(function () {
    // Dashboard API
    Route::get('/dashboard/statistics', [DashboardController::class, 'getStatistics'])
        ->name('api.dashboard.statistics');
    Route::get('/dashboard/charts', [DashboardController::class, 'getChartData'])
        ->name('api.dashboard.charts');
    Route::get('/dashboard/activities', [DashboardController::class, 'getRecentActivities'])
        ->name('api.dashboard.activities');
    
    // Employee API dengan filter enhancement
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
    
    // Enhanced filter options API untuk semua dropdown filters
    Route::get('/employees/filter-options', function() {
        try {
            $filterOptions = [
                'status_pegawai' => \App\Models\Employee::select('status_pegawai')
                    ->whereNotNull('status_pegawai')
                    ->distinct()
                    ->pluck('status_pegawai')
                    ->sort()
                    ->values(),
                
                'unit_organisasi' => \App\Models\Employee::select('unit_organisasi')
                    ->whereNotNull('unit_organisasi')
                    ->distinct()
                    ->pluck('unit_organisasi')
                    ->sort()
                    ->values(),
                
                'jenis_sepatu' => \App\Models\Employee::select('jenis_sepatu')
                    ->whereNotNull('jenis_sepatu')
                    ->distinct()
                    ->pluck('jenis_sepatu')
                    ->sort()
                    ->values(),
                
                'ukuran_sepatu' => \App\Models\Employee::select('ukuran_sepatu')
                    ->whereNotNull('ukuran_sepatu')
                    ->distinct()
                    ->pluck('ukuran_sepatu')
                    ->map(function($size) {
                        return (string) $size;
                    })
                    ->sort(function($a, $b) {
                        return (int)$a <=> (int)$b;
                    })
                    ->values(),
                
                'jenis_kelamin' => ['L', 'P'],
                
                'pendidikan' => \App\Models\Employee::select('pendidikan')
                    ->whereNotNull('pendidikan')
                    ->distinct()
                    ->pluck('pendidikan')
                    ->sort()
                    ->values(),
                
                'kelompok_jabatan' => \App\Models\Employee::select('kelompok_jabatan')
                    ->whereNotNull('kelompok_jabatan')
                    ->distinct()
                    ->pluck('kelompok_jabatan')
                    ->sort()
                    ->values(),
            ];
            
            return response()->json($filterOptions);
        } catch (\Exception $e) {
            return response()->json([
                'error' => $e->getMessage(),
                'status_pegawai' => [],
                'unit_organisasi' => [],
                'jenis_sepatu' => [],
                'ukuran_sepatu' => [],
                'jenis_kelamin' => [],
                'pendidikan' => [],
                'kelompok_jabatan' => [],
            ], 500);
        }
    })->name('api.employees.filter.options');
    
    // Enhanced advanced search dengan multiple filters termasuk sepatu
    Route::post('/employees/search/advanced', function(\Illuminate\Http\Request $request) {
        try {
            $query = \App\Models\Employee::with('organization')
                ->where('status', 'active');

            // Apply search filter (mencakup semua field termasuk sepatu)
            if ($request->filled('search')) {
                $searchTerm = $request->search;
                $query->where(function($q) use ($searchTerm) {
                    $q->where('nama_lengkap', 'like', "%{$searchTerm}%")
                      ->orWhere('nip', 'like', "%{$searchTerm}%")
                      ->orWhere('nama_jabatan', 'like', "%{$searchTerm}%")
                      ->orWhere('unit_organisasi', 'like', "%{$searchTerm}%")
                      ->orWhere('jenis_sepatu', 'like', "%{$searchTerm}%")
                      ->orWhere('ukuran_sepatu', 'like', "%{$searchTerm}%")
                      ->orWhere('tempat_lahir', 'like', "%{$searchTerm}%")
                      ->orWhere('alamat', 'like', "%{$searchTerm}%")
                      ->orWhere('handphone', 'like', "%{$searchTerm}%");
                });
            }

            // Apply individual filters
            if ($request->filled('status_pegawai') && $request->status_pegawai !== 'all') {
                $query->where('status_pegawai', $request->status_pegawai);
            }

            if ($request->filled('unit_organisasi') && $request->unit_organisasi !== 'all') {
                $query->where('unit_organisasi', $request->unit_organisasi);
            }

            if ($request->filled('jenis_kelamin') && $request->jenis_kelamin !== 'all') {
                $query->where('jenis_kelamin', $request->jenis_kelamin);
            }

            // Enhanced shoe filters
            if ($request->filled('jenis_sepatu') && $request->jenis_sepatu !== 'all') {
                $query->where('jenis_sepatu', $request->jenis_sepatu);
            }

            if ($request->filled('ukuran_sepatu') && $request->ukuran_sepatu !== 'all') {
                $query->where('ukuran_sepatu', $request->ukuran_sepatu);
            }

            // Additional filters
            if ($request->filled('pendidikan') && $request->pendidikan !== 'all') {
                $query->where('pendidikan', $request->pendidikan);
            }

            if ($request->filled('kelompok_jabatan') && $request->kelompok_jabatan !== 'all') {
                $query->where('kelompok_jabatan', $request->kelompok_jabatan);
            }

            // Age range filter
            if ($request->filled('min_age')) {
                $query->where('usia', '>=', $request->min_age);
            }
            
            if ($request->filled('max_age')) {
                $query->where('usia', '<=', $request->max_age);
            }

            // Date range filters
            if ($request->filled('start_date')) {
                $query->where('tmt_mulai_jabatan', '>=', $request->start_date);
            }
            
            if ($request->filled('end_date')) {
                $query->where('tmt_mulai_jabatan', '<=', $request->end_date);
            }

            $employees = $query->orderBy('nama_lengkap', 'asc')->get();

            // Generate statistics for the filtered results
            $statistics = [
                'total' => $employees->count(),
                'pegawai_tetap' => $employees->where('status_pegawai', 'PEGAWAI TETAP')->count(),
                'tad' => $employees->where('status_pegawai', 'TAD')->count(),
                'male' => $employees->where('jenis_kelamin', 'L')->count(),
                'female' => $employees->where('jenis_kelamin', 'P')->count(),
                'pantofel' => $employees->where('jenis_sepatu', 'Pantofel')->count(),
                'safety_shoes' => $employees->where('jenis_sepatu', 'Safety Shoes')->count(),
                'units' => $employees->groupBy('unit_organisasi')->map->count(),
                'shoe_sizes' => $employees->whereNotNull('ukuran_sepatu')->groupBy('ukuran_sepatu')->map->count(),
            ];

            return response()->json([
                'employees' => $employees,
                'total' => $employees->count(),
                'statistics' => $statistics,
                'filters_applied' => $request->only([
                    'search', 
                    'status_pegawai', 
                    'unit_organisasi', 
                    'jenis_kelamin',
                    'jenis_sepatu',
                    'ukuran_sepatu',
                    'pendidikan',
                    'kelompok_jabatan',
                    'min_age',
                    'max_age',
                    'start_date',
                    'end_date'
                ]),
                'search_metadata' => [
                    'search_time' => now()->toISOString(),
                    'has_filters' => $request->hasAny([
                        'search', 'status_pegawai', 'unit_organisasi', 'jenis_kelamin',
                        'jenis_sepatu', 'ukuran_sepatu', 'pendidikan', 'kelompok_jabatan'
                    ]),
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'employees' => [],
                'total' => 0,
                'error' => $e->getMessage(),
                'statistics' => [
                    'total' => 0,
                    'pegawai_tetap' => 0,
                    'tad' => 0,
                    'male' => 0,
                    'female' => 0,
                    'pantofel' => 0,
                    'safety_shoes' => 0,
                ],
            ], 500);
        }
    })->name('api.employees.search.advanced');
    
    // Shoe-specific API endpoints
    Route::get('/employees/shoe-distribution', function() {
        try {
            $distribution = [
                'by_type' => \App\Models\Employee::select('jenis_sepatu')
                    ->selectRaw('COUNT(*) as count')
                    ->whereNotNull('jenis_sepatu')
                    ->groupBy('jenis_sepatu')
                    ->get(),
                
                'by_size' => \App\Models\Employee::select('ukuran_sepatu')
                    ->selectRaw('COUNT(*) as count')
                    ->whereNotNull('ukuran_sepatu')
                    ->groupBy('ukuran_sepatu')
                    ->orderBy('ukuran_sepatu')
                    ->get(),
                
                'by_gender_and_type' => \App\Models\Employee::select('jenis_kelamin', 'jenis_sepatu')
                    ->selectRaw('COUNT(*) as count')
                    ->whereNotNull('jenis_sepatu')
                    ->groupBy('jenis_kelamin', 'jenis_sepatu')
                    ->get(),
                
                'by_unit_and_type' => \App\Models\Employee::select('unit_organisasi', 'jenis_sepatu')
                    ->selectRaw('COUNT(*) as count')
                    ->whereNotNull('jenis_sepatu')
                    ->whereNotNull('unit_organisasi')
                    ->groupBy('unit_organisasi', 'jenis_sepatu')
                    ->get(),
            ];
            
            return response()->json($distribution);
        } catch (\Exception $e) {
            return response()->json([
                'error' => $e->getMessage(),
                'by_type' => [],
                'by_size' => [],
                'by_gender_and_type' => [],
                'by_unit_and_type' => [],
            ], 500);
        }
    })->name('api.employees.shoe.distribution');
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
            
            // Enhanced shoe statistics
            $shoeStats = [
                'pantofel' => \App\Models\Employee::where('jenis_sepatu', 'Pantofel')->count(),
                'safety_shoes' => \App\Models\Employee::where('jenis_sepatu', 'Safety Shoes')->count(),
                'no_shoe_data' => \App\Models\Employee::whereNull('jenis_sepatu')->count(),
                'unique_sizes' => \App\Models\Employee::whereNotNull('ukuran_sepatu')->distinct()->count('ukuran_sepatu'),
            ];
        } catch (\Exception $e) {
            $dbStatus = 'Error: ' . $e->getMessage();
            $employeeCount = 0;
            $organizationCount = 0;
            $shoeStats = ['pantofel' => 0, 'safety_shoes' => 0, 'no_shoe_data' => 0, 'unique_sizes' => 0];
        }
        
        return response()->json([
            'system' => 'GAPURA ANGKASA SDM System',
            'status' => 'healthy',
            'database' => $dbStatus,
            'employee_count' => $employeeCount,
            'organization_count' => $organizationCount,
            'shoe_statistics' => $shoeStats,
            'features' => [
                'shoe_filtering' => 'enabled',
                'size_filtering' => 'enabled',
                'advanced_search' => 'enabled',
                'real_time_filtering' => 'enabled',
            ],
            'laravel_version' => Application::VERSION,
            'php_version' => PHP_VERSION,
            'timestamp' => now()->toISOString(),
            'version' => '1.1.0',
        ]);
    })->name('utilities.health.check');
    
    Route::get('/system-info', function () {
        return response()->json([
            'system_name' => 'GAPURA ANGKASA SDM System',
            'version' => '1.1.0',
            'features' => [
                'employee_management' => 'active',
                'shoe_filtering' => 'active',
                'size_filtering' => 'active',
                'advanced_search' => 'active',
                'real_time_filtering' => 'active',
                'shoe_reports' => 'active',
            ],
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
                \Artisan::call('db:seed', ['--class' => 'SDMEmployeeSeeder', '--force' => true]);
                $output = \Artisan::output();
                
                return response()->json([
                    'message' => 'SDM Employee Seeder executed successfully',
                    'output' => $output,
                    'employees_count' => \App\Models\Employee::count(),
                    'organizations_count' => \App\Models\Organization::count(),
                    'shoe_stats' => [
                        'pantofel' => \App\Models\Employee::where('jenis_sepatu', 'Pantofel')->count(),
                        'safety_shoes' => \App\Models\Employee::where('jenis_sepatu', 'Safety Shoes')->count(),
                        'unique_sizes' => \App\Models\Employee::whereNotNull('ukuran_sepatu')->distinct()->count('ukuran_sepatu'),
                    ],
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
                
                // Test shoe data
                $shoeData = [
                    'employees_with_shoes' => \App\Models\Employee::whereNotNull('jenis_sepatu')->count(),
                    'employees_with_sizes' => \App\Models\Employee::whereNotNull('ukuran_sepatu')->count(),
                    'shoe_types' => \App\Models\Employee::select('jenis_sepatu')->distinct()->whereNotNull('jenis_sepatu')->pluck('jenis_sepatu'),
                    'shoe_sizes' => \App\Models\Employee::select('ukuran_sepatu')->distinct()->whereNotNull('ukuran_sepatu')->pluck('ukuran_sepatu')->sort()->values(),
                ];
                
                return response()->json([
                    'database_status' => 'Connected',
                    'employees_count' => \App\Models\Employee::count(),
                    'organizations_count' => \App\Models\Organization::count(),
                    'sample_employees' => $employees,
                    'organizations' => $organizations,
                    'shoe_data' => $shoeData,
                    'timestamp' => now()->toISOString()
                ]);
            } catch (\Exception $e) {
                return response()->json([
                    'database_status' => 'Error',
                    'error' => $e->getMessage()
                ], 500);
            }
        })->name('dev.test.database');
        
        Route::get('/test-shoe-filters', function () {
            try {
                // Test all shoe-related filters
                $filterTests = [
                    'pantofel_employees' => \App\Models\Employee::where('jenis_sepatu', 'Pantofel')->count(),
                    'safety_shoes_employees' => \App\Models\Employee::where('jenis_sepatu', 'Safety Shoes')->count(),
                    'size_36_employees' => \App\Models\Employee::where('ukuran_sepatu', '36')->count(),
                    'size_42_employees' => \App\Models\Employee::where('ukuran_sepatu', '42')->count(),
                    'back_office_pantofel' => \App\Models\Employee::where('unit_organisasi', 'Back Office')->where('jenis_sepatu', 'Pantofel')->count(),
                    'gse_safety_shoes' => \App\Models\Employee::where('unit_organisasi', 'GSE')->where('jenis_sepatu', 'Safety Shoes')->count(),
                ];
                
                return response()->json([
                    'message' => 'Shoe filter tests completed',
                    'filter_tests' => $filterTests,
                    'timestamp' => now()->toISOString()
                ]);
            } catch (\Exception $e) {
                return response()->json([
                    'message' => 'Shoe filter tests failed',
                    'error' => $e->getMessage()
                ], 500);
            }
        })->name('dev.test.shoe.filters');
        
        Route::get('/migrate-fresh', function () {
            try {
                \Artisan::call('migrate:fresh', ['--seed' => true, '--force' => true]);
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
                \Artisan::call('db:seed', ['--force' => true]);
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
| Route Documentation - Enhanced dengan Filter Sepatu
|--------------------------------------------------------------------------
|
| MAIN ROUTES:
| - /dashboard                 - Dashboard utama dengan statistik
| - /employees                 - Management karyawan (CRUD lengkap dengan filter sepatu)
| - /organisasi               - Management organisasi
| - /laporan                  - Reports dan statistik
| - /laporan/sepatu           - Laporan khusus distribusi sepatu
| - /pengaturan               - Settings sistem
|
| ENHANCED API ROUTES untuk Filter Sepatu:
| - /api/employees/filter-options        - Get all filter options (termasuk sepatu)
| - /api/employees/search/advanced       - Advanced search dengan filter sepatu
| - /api/employees/shoe-distribution     - Statistik distribusi sepatu
|
| LEGACY/ALIAS ROUTES:
| - /management-karyawan      - Redirect ke /employees
| - /data-karyawan           - Redirect ke /employees
| - /total-karyawan          - Redirect ke /employees
|
| DEVELOPMENT ROUTES (local only):
| - /dev/test-seeder         - Test SDM Employee Seeder
| - /dev/test-database       - Test database connection
| - /dev/test-shoe-filters   - Test shoe filtering functionality
| - /dev/migrate-fresh       - Fresh migration dengan seed
| - /dev/routes              - List all available routes
|
| UTILITY ROUTES:
| - /utilities/health-check  - System health check (dengan shoe stats)
| - /utilities/clear-cache   - Clear application cache
| - /utilities/system-info   - System information (dengan feature list)
|
| ENHANCED FEATURES:
| ✓ Filter berdasarkan jenis sepatu (Pantofel/Safety Shoes)
| ✓ Filter berdasarkan ukuran sepatu (36-44)
| ✓ Advanced search pada semua field termasuk sepatu
| ✓ Statistics breakdown untuk distribusi sepatu
| ✓ Real-time filtering tanpa page reload
| ✓ Shoe distribution reports
| ✓ API endpoints untuk shoe data
| ✓ Development tools untuk testing shoe filters
|
*/