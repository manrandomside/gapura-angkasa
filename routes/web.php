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
| Updated dengan Unit Organisasi Expert System
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
// API ROUTES - ENHANCED dengan Unit Organisasi Expert
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
    
    // =====================================================
    // UNIT ORGANISASI EXPERT API - COMPLETE
    // =====================================================
    
    // Core Unit & Sub Unit API routes untuk cascading dropdown
    Route::get('/units', [EmployeeController::class, 'getUnits'])->name('api.units');
    Route::get('/sub-units', [EmployeeController::class, 'getSubUnits'])->name('api.sub.units');
    Route::get('/unit-organisasi-options', [EmployeeController::class, 'getUnitOrganisasiOptions'])->name('api.unit.organisasi.options');
    
    // Enhanced Unit API - BARU
    Route::get('/units/hierarchy', [EmployeeController::class, 'getAllUnitsHierarchy'])->name('api.units.hierarchy');
    Route::get('/units/statistics', [EmployeeController::class, 'getUnitStatistics'])->name('api.units.statistics');
    
    // Enhanced filter options API dengan unit organisasi expert options
    Route::get('/employees/filter-options', function() {
        try {
            // Get Unit Organisasi options dari Unit model jika ada
            $unitOrganisasiOptions = [];
            try {
                if (class_exists('App\Models\Unit')) {
                    $unitOrganisasiOptions = \App\Models\Unit::UNIT_ORGANISASI_OPTIONS;
                }
            } catch (\Exception $e) {
                // Fallback ke options dari database jika Unit model belum ada
                $unitOrganisasiOptions = \App\Models\Employee::select('unit_organisasi')
                    ->whereNotNull('unit_organisasi')
                    ->distinct()
                    ->pluck('unit_organisasi')
                    ->sort()
                    ->values()
                    ->toArray();
            }
            
            $filterOptions = [
                'status_pegawai' => \App\Models\Employee::select('status_pegawai')
                    ->whereNotNull('status_pegawai')
                    ->distinct()
                    ->pluck('status_pegawai')
                    ->sort()
                    ->values(),
                
                'unit_organisasi' => $unitOrganisasiOptions,
                
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
            
            return response()->json([
                'success' => true,
                'data' => $filterOptions
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error retrieving filter options: ' . $e->getMessage(),
                'data' => [
                    'status_pegawai' => [],
                    'unit_organisasi' => ['EGM', 'GM', 'Airside', 'Landside', 'Back Office', 'SSQC', 'Ancillary'],
                    'jenis_sepatu' => ['Pantofel', 'Safety Shoes'],
                    'ukuran_sepatu' => ['36', '37', '38', '39', '40', '41', '42', '43', '44'],
                    'jenis_kelamin' => ['L', 'P'],
                    'pendidikan' => [],
                    'kelompok_jabatan' => [],
                ]
            ], 500);
        }
    })->name('api.employees.filter.options');
    
    // Enhanced advanced search dengan multiple filters termasuk sepatu dan unit expert
    Route::post('/employees/search/advanced', function(\Illuminate\Http\Request $request) {
        try {
            $query = \App\Models\Employee::with(['organization'])
                ->where('status', 'active');

            // Load unit relationships if available
            if (method_exists(\App\Models\Employee::class, 'unit')) {
                $query->with('unit');
            }
            if (method_exists(\App\Models\Employee::class, 'subUnit')) {
                $query->with('subUnit');
            }

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
                      ->orWhere('handphone', 'like', "%{$searchTerm}%")
                      // TAMBAHAN BARU: Search dalam unit dan sub unit
                      ->orWhereHas('unit', function ($q) use ($searchTerm) {
                          $q->where('name', 'like', "%{$searchTerm}%");
                      })
                      ->orWhereHas('subUnit', function ($q) use ($searchTerm) {
                          $q->where('name', 'like', "%{$searchTerm}%");
                      });
                });
            }

            // Apply individual filters
            if ($request->filled('status_pegawai') && $request->status_pegawai !== 'all') {
                $query->where('status_pegawai', $request->status_pegawai);
            }

            if ($request->filled('unit_organisasi') && $request->unit_organisasi !== 'all') {
                $query->where('unit_organisasi', $request->unit_organisasi);
            }

            // Unit expert filters - BARU
            if ($request->filled('unit_id') && $request->unit_id !== 'all') {
                $query->where('unit_id', $request->unit_id);
            }

            if ($request->filled('sub_unit_id') && $request->sub_unit_id !== 'all') {
                $query->where('sub_unit_id', $request->sub_unit_id);
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
                    'unit_id',
                    'sub_unit_id',
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
                        'search', 'status_pegawai', 'unit_organisasi', 'unit_id', 'sub_unit_id',
                        'jenis_kelamin', 'jenis_sepatu', 'ukuran_sepatu', 'pendidikan', 'kelompok_jabatan'
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

            // Unit organisasi expert stats
            $unitStats = [];
            try {
                if (class_exists('App\Models\Unit')) {
                    $unitStats = [
                        'total_units' => \App\Models\Unit::count(),
                        'active_units' => \App\Models\Unit::where('is_active', true)->count(),
                        'total_sub_units' => class_exists('App\Models\SubUnit') ? \App\Models\SubUnit::count() : 0,
                        'active_sub_units' => class_exists('App\Models\SubUnit') ? \App\Models\SubUnit::where('is_active', true)->count() : 0,
                        'unit_organisasi_count' => count(\App\Models\Unit::UNIT_ORGANISASI_OPTIONS ?? []),
                        'hierarchy_complete' => \App\Models\Unit::where('is_active', true)->count() > 0 && class_exists('App\Models\SubUnit'),
                    ];
                }
            } catch (\Exception $e) {
                $unitStats = ['message' => 'Unit system not yet implemented'];
            }
        } catch (\Exception $e) {
            $dbStatus = 'Error: ' . $e->getMessage();
            $employeeCount = 0;
            $organizationCount = 0;
            $shoeStats = ['pantofel' => 0, 'safety_shoes' => 0, 'no_shoe_data' => 0, 'unique_sizes' => 0];
            $unitStats = ['message' => 'Database connection failed'];
        }
        
        return response()->json([
            'system' => 'GAPURA ANGKASA SDM System',
            'status' => 'healthy',
            'database' => $dbStatus,
            'employee_count' => $employeeCount,
            'organization_count' => $organizationCount,
            'shoe_statistics' => $shoeStats,
            'unit_statistics' => $unitStats,
            'features' => [
                'shoe_filtering' => 'enabled',
                'size_filtering' => 'enabled',
                'advanced_search' => 'enabled',
                'real_time_filtering' => 'enabled',
                'unit_organisasi_expert' => 'enabled',
                'cascading_dropdown' => 'enabled',
                'unit_hierarchy' => 'enabled',
                'unit_statistics' => 'enabled',
            ],
            'laravel_version' => Application::VERSION,
            'php_version' => PHP_VERSION,
            'timestamp' => now()->toISOString(),
            'version' => '1.2.0',
        ]);
    })->name('utilities.health.check');
    
    Route::get('/system-info', function () {
        return response()->json([
            'system_name' => 'GAPURA ANGKASA SDM System',
            'version' => '1.2.0',
            'features' => [
                'employee_management' => 'active',
                'shoe_filtering' => 'active',
                'size_filtering' => 'active',
                'advanced_search' => 'active',
                'real_time_filtering' => 'active',
                'shoe_reports' => 'active',
                'unit_organisasi_expert' => 'active',
                'cascading_dropdown' => 'active',
                'unit_sub_unit_management' => 'active',
                'unit_hierarchy_api' => 'active',
                'unit_statistics_api' => 'active',
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

        // Test unit seeder
        Route::get('/test-unit-seeder', function () {
            try {
                \Artisan::call('db:seed', ['--class' => 'UnitSeeder', '--force' => true]);
                $output = \Artisan::output();
                
                $unitStats = [];
                if (class_exists('App\Models\Unit')) {
                    $unitStats = [
                        'total_units' => \App\Models\Unit::count(),
                        'active_units' => \App\Models\Unit::where('is_active', true)->count(),
                        'airside_units' => \App\Models\Unit::where('unit_organisasi', 'Airside')->count(),
                        'landside_units' => \App\Models\Unit::where('unit_organisasi', 'Landside')->count(),
                        'total_sub_units' => class_exists('App\Models\SubUnit') ? \App\Models\SubUnit::count() : 0,
                        'active_sub_units' => class_exists('App\Models\SubUnit') ? \App\Models\SubUnit::where('is_active', true)->count() : 0,
                        'unit_organisasi_breakdown' => \App\Models\Unit::selectRaw('unit_organisasi, COUNT(*) as count')->groupBy('unit_organisasi')->get(),
                    ];
                }
                
                return response()->json([
                    'message' => 'Unit Seeder executed successfully',
                    'output' => $output,
                    'unit_stats' => $unitStats,
                ]);
            } catch (\Exception $e) {
                return response()->json([
                    'message' => 'Unit Seeder failed',
                    'error' => $e->getMessage()
                ], 500);
            }
        })->name('dev.test.unit.seeder');
        
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

                // Test unit data
                $unitData = [];
                try {
                    if (class_exists('App\Models\Unit')) {
                        $unitData = [
                            'units' => \App\Models\Unit::with('subUnits')->get(),
                            'unit_organisasi_options' => \App\Models\Unit::UNIT_ORGANISASI_OPTIONS ?? [],
                            'hierarchy_test' => \App\Models\Unit::getGroupedByUnitOrganisasi(),
                        ];
                    }
                } catch (\Exception $e) {
                    $unitData = ['message' => 'Unit models not yet created'];
                }
                
                return response()->json([
                    'database_status' => 'Connected',
                    'employees_count' => \App\Models\Employee::count(),
                    'organizations_count' => \App\Models\Organization::count(),
                    'sample_employees' => $employees,
                    'organizations' => $organizations,
                    'shoe_data' => $shoeData,
                    'unit_data' => $unitData,
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
                    'airside_safety_shoes' => \App\Models\Employee::where('unit_organisasi', 'Airside')->where('jenis_sepatu', 'Safety Shoes')->count(),
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

        // Enhanced unit API testing route
        Route::get('/test-unit-api', function () {
            try {
                $results = [
                    'timestamp' => now(),
                    'tests' => []
                ];
                
                // Test 1: Unit Organisasi Options
                try {
                    $response = app(EmployeeController::class)->getUnitOrganisasiOptions();
                    $data = json_decode($response->getContent(), true);
                    $results['tests']['unit_organisasi_options'] = [
                        'status' => $response->getStatusCode() === 200 ? 'PASS' : 'FAIL',
                        'data_count' => count($data['data'] ?? []),
                        'sample_data' => array_slice($data['data'] ?? [], 0, 3)
                    ];
                } catch (\Exception $e) {
                    $results['tests']['unit_organisasi_options'] = [
                        'status' => 'ERROR',
                        'error' => $e->getMessage()
                    ];
                }
                
                // Test 2: Units for Airside
                try {
                    $request = new \Illuminate\Http\Request(['unit_organisasi' => 'Airside']);
                    $response = app(EmployeeController::class)->getUnits($request);
                    $data = json_decode($response->getContent(), true);
                    $results['tests']['airside_units'] = [
                        'status' => $response->getStatusCode() === 200 ? 'PASS' : 'FAIL',
                        'units_count' => count($data['data'] ?? []),
                        'units' => $data['data'] ?? []
                    ];
                } catch (\Exception $e) {
                    $results['tests']['airside_units'] = [
                        'status' => 'ERROR',
                        'error' => $e->getMessage()
                    ];
                }
                
                // Test 3: Sub Units for MO (if exists)
                try {
                    $moUnit = \App\Models\Unit::where('name', 'MO')->where('unit_organisasi', 'Airside')->first();
                    if ($moUnit) {
                        $request = new \Illuminate\Http\Request(['unit_id' => $moUnit->id]);
                        $response = app(EmployeeController::class)->getSubUnits($request);
                        $data = json_decode($response->getContent(), true);
                        $results['tests']['mo_sub_units'] = [
                            'status' => $response->getStatusCode() === 200 ? 'PASS' : 'FAIL',
                            'sub_units_count' => count($data['data'] ?? []),
                            'sub_units' => array_column($data['data'] ?? [], 'name')
                        ];
                    } else {
                        $results['tests']['mo_sub_units'] = [
                            'status' => 'SKIP',
                            'reason' => 'MO unit not found - run UnitSeeder first'
                        ];
                    }
                } catch (\Exception $e) {
                    $results['tests']['mo_sub_units'] = [
                        'status' => 'ERROR',
                        'error' => $e->getMessage()
                    ];
                }
                
                // Test 4: Unit Hierarchy
                try {
                    $response = app(EmployeeController::class)->getAllUnitsHierarchy();
                    $data = json_decode($response->getContent(), true);
                    $results['tests']['unit_hierarchy'] = [
                        'status' => $response->getStatusCode() === 200 ? 'PASS' : 'FAIL',
                        'hierarchy_count' => count($data['data'] ?? []),
                        'sample_structure' => array_keys($data['data'] ?? [])
                    ];
                } catch (\Exception $e) {
                    $results['tests']['unit_hierarchy'] = [
                        'status' => 'ERROR',
                        'error' => $e->getMessage()
                    ];
                }
                
                // Test 5: Unit Statistics
                try {
                    $response = app(EmployeeController::class)->getUnitStatistics();
                    $data = json_decode($response->getContent(), true);
                    $results['tests']['unit_statistics'] = [
                        'status' => $response->getStatusCode() === 200 ? 'PASS' : 'FAIL',
                        'statistics' => $data['data'] ?? []
                    ];
                } catch (\Exception $e) {
                    $results['tests']['unit_statistics'] = [
                        'status' => 'ERROR',
                        'error' => $e->getMessage()
                    ];
                }
                
                // Summary
                $passCount = 0;
                $totalTests = count($results['tests']);
                foreach ($results['tests'] as $test) {
                    if ($test['status'] === 'PASS') $passCount++;
                }
                
                $results['summary'] = [
                    'total_tests' => $totalTests,
                    'passed' => $passCount,
                    'failed' => $totalTests - $passCount,
                    'success_rate' => $totalTests > 0 ? round(($passCount / $totalTests) * 100, 2) . '%' : '0%'
                ];
                
                return response()->json([
                    'message' => 'Unit API Testing Completed',
                    'results' => $results
                ], 200, [], JSON_PRETTY_PRINT);
                
            } catch (\Exception $e) {
                return response()->json([
                    'message' => 'Unit API Testing Failed',
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString()
                ], 500);
            }
        })->name('dev.test.unit.api');
        
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
| Route Documentation - Enhanced dengan Unit Organisasi Expert Complete
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
| ENHANCED API ROUTES untuk Unit Organisasi Expert:
| - /api/units                           - Get units by unit organisasi
| - /api/sub-units                       - Get sub units by unit
| - /api/unit-organisasi-options         - Get unit organisasi options
| - /api/units/hierarchy                 - Get complete unit hierarchy
| - /api/units/statistics                - Get unit statistics for monitoring
| - /api/employees/filter-options        - Get all filter options (termasuk unit expert)
| - /api/employees/search/advanced       - Advanced search dengan unit expert filters
| - /api/employees/shoe-distribution     - Statistik distribusi sepatu
|
| UNIT ORGANISASI EXPERT FEATURES:
| ✓ Cascading dropdown Unit Organisasi → Unit → Sub Unit
| ✓ Dynamic loading via API berdasarkan parent selection
| ✓ Unit Organisasi: EGM, GM, Airside, Landside, Back Office, SSQC, Ancillary
| ✓ Complete hierarchy API endpoints
| ✓ Real-time statistics monitoring
| ✓ Preview struktur organisasi real-time
| ✓ Database relationships untuk filtering dan reporting
| ✓ Enhanced testing routes untuk development
|
| LEGACY/ALIAS ROUTES:
| - /management-karyawan      - Redirect ke /employees
| - /data-karyawan           - Redirect ke /employees
| - /total-karyawan          - Redirect ke /employees
|
| ENHANCED DEVELOPMENT ROUTES (local only):
| - /dev/test-seeder         - Test SDM Employee Seeder
| - /dev/test-unit-seeder    - Test Unit Seeder (enhanced)
| - /dev/test-database       - Test database connection (dengan unit data)
| - /dev/test-shoe-filters   - Test shoe filtering functionality
| - /dev/test-unit-api       - Enhanced unit API testing dengan comprehensive results
| - /dev/migrate-fresh       - Fresh migration dengan seed
| - /dev/routes              - List all available routes
|
| UTILITY ROUTES:
| - /utilities/health-check  - System health check (enhanced dengan unit hierarchy stats)
| - /utilities/clear-cache   - Clear application cache
| - /utilities/system-info   - System information (enhanced dengan unit features)
|
| ENHANCED FEATURES v1.3.0:
| ✓ Complete Unit Organisasi Expert system dengan cascading dropdown
| ✓ Full hierarchy API endpoints (/api/units/hierarchy)
| ✓ Real-time unit statistics (/api/units/statistics)
| ✓ Enhanced search dengan unit dan sub unit filters
| ✓ Comprehensive testing suite untuk unit API
| ✓ Enhanced health check dengan unit system monitoring
| ✓ Complete API documentation dalam route comments
| ✓ Development tools untuk comprehensive testing
|
*/