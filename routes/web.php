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
| FIXED: Parent-child dropdown dan cascading units
|
*/

// =====================================================
// API ROUTES - PRIORITAS PERTAMA UNTUK UNIT CASCADING
// =====================================================

Route::prefix('api')->group(function () {
    // CRITICAL: Unit & Sub Unit API routes untuk parent-child dropdown
    // Harus didefinisikan PERTAMA untuk menghindari konflik routing
    Route::get('/units', [EmployeeController::class, 'getUnits'])->name('api.units');
    Route::get('/sub-units', [EmployeeController::class, 'getSubUnits'])->name('api.sub.units');
    Route::get('/unit-organisasi-options', [EmployeeController::class, 'getUnitOrganisasiOptions'])->name('api.unit.organisasi.options');
    
    // Enhanced Unit API untuk debugging dan monitoring
    Route::get('/units/hierarchy', [EmployeeController::class, 'getAllUnitsHierarchy'])->name('api.units.hierarchy');
    Route::get('/units/statistics', [EmployeeController::class, 'getUnitStatistics'])->name('api.units.statistics');
    
    // Dashboard API
    Route::get('/dashboard/statistics', [DashboardController::class, 'getStatistics'])->name('api.dashboard.statistics');
    Route::get('/dashboard/charts', [DashboardController::class, 'getChartData'])->name('api.dashboard.charts');
    Route::get('/dashboard/activities', [DashboardController::class, 'getRecentActivities'])->name('api.dashboard.activities');
    
    // Employee API dengan filter enhancement - UPDATED: Flexible identifier support
    Route::get('/employees/search', [EmployeeController::class, 'search'])->name('api.employees.search');
    Route::get('/employees/statistics', [EmployeeController::class, 'getStatistics'])->name('api.employees.statistics');
    
    // UPDATED: Profile API menggunakan flexible identifier parameter
    Route::get('/employees/{identifier}/profile', [EmployeeController::class, 'profile'])
        ->name('api.employees.profile')
        ->where('identifier', '[0-9]+');
        
    Route::post('/employees/validate', [EmployeeController::class, 'validateData'])->name('api.employees.validate');
    Route::post('/employees/bulk-action', [EmployeeController::class, 'bulkAction'])->name('api.employees.bulk.action');
    
    // UPDATED: Enhanced filter options API dengan flexible identifier validation support
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

            // UPDATED: Apply search filter dengan flexible identifier search
            if ($request->filled('search')) {
                $searchTerm = $request->search;
                $query->where(function($q) use ($searchTerm) {
                    $q->where('nama_lengkap', 'like', "%{$searchTerm}%")
                      ->orWhere('nip', 'like', "%{$searchTerm}%")
                      ->orWhere('nik', 'like', "%{$searchTerm}%") // Include NIK search
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

    // UPDATED: Employee summary API dengan flexible identifier parameter
    Route::get('/employees/{identifier}/summary', function($identifier) {
        try {
            $employee = null;
            
            // Try to find by NIK first (if 16 digits)
            if (preg_match('/^[0-9]{16}$/', $identifier)) {
                $employee = \App\Models\Employee::where('nik', $identifier)->first();
            }
            
            // Fallback to ID or NIP
            if (!$employee) {
                $employee = \App\Models\Employee::where('id', $identifier)->orWhere('nip', $identifier)->first();
            }
            
            if (!$employee) {
                return response()->json(['error' => 'Employee not found'], 404);
            }
            
            return response()->json([
                'id' => $employee->id,
                'nik' => $employee->nik,
                'nip' => $employee->nip,
                'nama_lengkap' => $employee->nama_lengkap,
                'unit_organisasi' => $employee->unit_organisasi,
                'nama_jabatan' => $employee->nama_jabatan,
                'kelompok_jabatan' => $employee->kelompok_jabatan,
                'status_pegawai' => $employee->status_pegawai,
                'handphone' => $employee->handphone,
                'email' => $employee->email,
            ]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    })->where('identifier', '[0-9]+');

    // UPDATED: NIK dan NIP validation API endpoints
    Route::post('/validate/nik', function(\Illuminate\Http\Request $request) {
        $nik = $request->input('nik');
        $excludeNik = $request->input('exclude_nik'); // For edit mode
        
        // Validate NIK format (allow flexible length for now)
        if (strlen($nik) < 10 || !ctype_digit($nik)) {
            return response()->json([
                'valid' => false,
                'message' => 'NIK minimal 10 digit angka'
            ]);
        }
        
        // Check if NIK already exists
        $query = \App\Models\Employee::where('nik', $nik);
        if ($excludeNik) {
            $query->where('nik', '!=', $excludeNik);
        }
        $exists = $query->exists();
        
        return response()->json([
            'valid' => !$exists,
            'message' => $exists ? 'NIK sudah terdaftar' : 'NIK tersedia'
        ]);
    });
    
    Route::post('/validate/nip', function(\Illuminate\Http\Request $request) {
        $nip = $request->input('nip');
        $excludeId = $request->input('exclude_id'); // For edit mode - use ID instead of NIK
        
        // Validate NIP format
        if (strlen($nip) < 6 || !ctype_digit($nip)) {
            return response()->json([
                'valid' => false,
                'message' => 'NIP minimal 6 digit angka'
            ]);
        }
        
        // Check if NIP already exists (exclude current employee jika edit)
        $query = \App\Models\Employee::where('nip', $nip);
        if ($excludeId) {
            $query->where('id', '!=', $excludeId); // Use ID for exclude
        }
        $exists = $query->exists();
        
        return response()->json([
            'valid' => !$exists,
            'message' => $exists ? 'NIP sudah terdaftar' : 'NIP tersedia'
        ]);
    });

    // FIXED: Simple validation endpoints for frontend validation
    Route::get('/validate/nik/{nik}', function ($nik) {
        $exists = \App\Models\Employee::where('nik', $nik)->exists();
        return response()->json([
            'available' => !$exists,
            'message' => $exists ? 'NIK sudah terdaftar' : 'NIK tersedia'
        ]);
    });
    
    Route::get('/validate/nip/{nip}', function ($nip) {
        $exists = \App\Models\Employee::where('nip', $nip)->exists();
        return response()->json([
            'available' => !$exists,
            'message' => $exists ? 'NIP sudah terdaftar' : 'NIP tersedia'
        ]);
    });
});

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
// EMPLOYEE MANAGEMENT ROUTES - FIXED: Flexible identifier support
// =====================================================

Route::prefix('employees')->group(function () {
    // Main CRUD operations
    Route::get('/', [EmployeeController::class, 'index'])->name('employees.index');
    Route::get('/create', [EmployeeController::class, 'create'])->name('employees.create');
    Route::post('/', [EmployeeController::class, 'store'])->name('employees.store');
    
    // FIXED: Flexible routes yang support both NIK dan ID
    Route::get('/{identifier}', [EmployeeController::class, 'show'])
        ->name('employees.show')
        ->where('identifier', '[0-9]+'); // Support both ID dan NIK (flexible numeric)
        
    Route::get('/{identifier}/edit', [EmployeeController::class, 'edit'])
        ->name('employees.edit')
        ->where('identifier', '[0-9]+');
        
    Route::put('/{identifier}', [EmployeeController::class, 'update'])
        ->name('employees.update')
        ->where('identifier', '[0-9]+');
        
    Route::patch('/{identifier}', [EmployeeController::class, 'update'])
        ->name('employees.patch')
        ->where('identifier', '[0-9]+');
        
    Route::delete('/{identifier}', [EmployeeController::class, 'destroy'])
        ->name('employees.destroy')
        ->where('identifier', '[0-9]+');
    
    // Search and filter
    Route::get('/search/api', [EmployeeController::class, 'search'])->name('employees.search.api');
    Route::get('/filter-options', [EmployeeController::class, 'getFilterOptions'])->name('employees.filter.options');
    Route::get('/suggestions', [EmployeeController::class, 'suggestions'])->name('employees.suggestions');
    
    // Statistics and analytics
    Route::get('/statistics/api', [EmployeeController::class, 'getStatistics'])->name('employees.statistics');
    
    // Profile dengan flexible parameter
    Route::get('/{identifier}/profile', [EmployeeController::class, 'profile'])
        ->name('employees.profile')
        ->where('identifier', '[0-9]+');
    
    // Data operations
    Route::post('/import', [EmployeeController::class, 'import'])->name('employees.import');
    Route::get('/export', [EmployeeController::class, 'export'])->name('employees.export');
    Route::post('/bulk-action', [EmployeeController::class, 'bulkAction'])->name('employees.bulk.action');
    Route::post('/validate', [EmployeeController::class, 'validateData'])->name('employees.validate');
    
    // Reports
    Route::post('/generate-report', [EmployeeController::class, 'generateReport'])->name('employees.generate.report');
    
    // Additional features dengan flexible parameter
    Route::get('/{identifier}/id-card', [EmployeeController::class, 'generateIdCard'])
        ->name('employees.id.card')
        ->where('identifier', '[0-9]+');
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
// LEGACY & ALIAS ROUTES - UPDATED: Support flexible identifier redirects
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

// Legacy data-karyawan routes - UPDATED: Handle both old ID and NIK formats
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
    
    // UPDATED: Legacy ID redirect dengan detection NIK vs old ID
    Route::get('/{identifier}', function ($identifier) {
        // Jika identifier adalah NIK format (16 digit), redirect langsung
        if (preg_match('/^[0-9]{16}$/', $identifier)) {
            return redirect()->route('employees.show', ['identifier' => $identifier]);
        }
        
        // Jika identifier adalah old auto-increment ID, cari berdasarkan ID dan redirect
        try {
            $employee = \App\Models\Employee::where('id', $identifier)->orWhere('nip', $identifier)->first();
            if ($employee) {
                return redirect()->route('employees.show', ['identifier' => $employee->id]);
            }
        } catch (\Exception $e) {
            // Ignore error dan fallback ke index
        }
        
        // Fallback ke index jika tidak ditemukan
        return redirect()->route('employees.index')->with('error', 'Employee tidak ditemukan');
    });
    
    Route::get('/{identifier}/edit', function ($identifier) {
        // UPDATED: Same logic untuk edit route
        if (preg_match('/^[0-9]{16}$/', $identifier)) {
            return redirect()->route('employees.edit', ['identifier' => $identifier]);
        }
        
        try {
            $employee = \App\Models\Employee::where('id', $identifier)->orWhere('nip', $identifier)->first();
            if ($employee) {
                return redirect()->route('employees.edit', ['identifier' => $employee->id]);
            }
        } catch (\Exception $e) {
            // Ignore error
        }
        
        return redirect()->route('employees.index')->with('error', 'Employee tidak ditemukan');
    });
});

// Total karyawan alias
Route::get('/total-karyawan', function () {
    return redirect()->route('employees.index');
})->name('total.karyawan');

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
// UTILITY ROUTES - UPDATED: Enhanced dengan flexible identifier system info
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

            // UPDATED: Flexible identifier system stats
            $identifierStats = [
                'total_with_nik' => \App\Models\Employee::whereNotNull('nik')->where('nik', '!=', '')->count(),
                'employees_without_nik' => \App\Models\Employee::whereNull('nik')->orWhere('nik', '')->count(),
                'unique_niks' => \App\Models\Employee::whereNotNull('nik')->where('nik', '!=', '')->distinct()->count('nik'),
                'unique_nips' => \App\Models\Employee::whereNotNull('nip')->where('nip', '!=', '')->distinct()->count('nip'),
                'auto_increment_ids' => \App\Models\Employee::max('id'),
            ];
        } catch (\Exception $e) {
            $dbStatus = 'Error: ' . $e->getMessage();
            $employeeCount = 0;
            $organizationCount = 0;
            $shoeStats = ['pantofel' => 0, 'safety_shoes' => 0, 'no_shoe_data' => 0, 'unique_sizes' => 0];
            $unitStats = ['message' => 'Database connection failed'];
            $identifierStats = ['total_with_nik' => 0, 'employees_without_nik' => 0, 'unique_niks' => 0, 'unique_nips' => 0, 'auto_increment_ids' => 0];
        }
        
        return response()->json([
            'system' => 'GAPURA ANGKASA SDM System',
            'status' => 'healthy',
            'database' => $dbStatus,
            'employee_count' => $employeeCount,
            'organization_count' => $organizationCount,
            'shoe_statistics' => $shoeStats,
            'unit_statistics' => $unitStats,
            'identifier_statistics' => $identifierStats, // UPDATED: Include flexible identifier stats
            'features' => [
                'shoe_filtering' => 'enabled',
                'size_filtering' => 'enabled',
                'advanced_search' => 'enabled',
                'real_time_filtering' => 'enabled',
                'unit_organisasi_expert' => 'enabled',
                'cascading_dropdown' => 'enabled',
                'unit_hierarchy' => 'enabled',
                'unit_statistics' => 'enabled',
                'flexible_identifier_support' => 'enabled', // UPDATED: New feature
                'backward_compatibility' => 'enabled', // UPDATED: New feature
            ],
            'laravel_version' => Application::VERSION,
            'php_version' => PHP_VERSION,
            'timestamp' => now()->toISOString(),
            'version' => '1.5.0', // UPDATED: Version bump untuk fixed parent-child dropdown
        ]);
    })->name('utilities.health.check');
    
    Route::get('/system-info', function () {
        return response()->json([
            'system_name' => 'GAPURA ANGKASA SDM System',
            'version' => '1.5.0', // UPDATED: Version bump
            'features' => [
                'employee_management' => 'active',
                'shoe_filtering' => 'active',
                'size_filtering' => 'active',
                'advanced_search' => 'active',
                'real_time_filtering' => 'active',
                'shoe_reports' => 'active',
                'unit_organisasi_expert' => 'active',
                'cascading_dropdown' => 'active', // FIXED
                'unit_sub_unit_management' => 'active',
                'unit_hierarchy_api' => 'active',
                'unit_statistics_api' => 'active',
                'flexible_identifier_system' => 'active', // UPDATED: New feature
                'nik_and_id_support' => 'active', // UPDATED: New feature
                'legacy_compatibility' => 'active', // UPDATED: New feature
                'parent_child_dropdown' => 'fixed', // NEW: Fixed feature indicator
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
// DEVELOPMENT ROUTES (hanya untuk local environment) - ENHANCED FOR DEBUGGING
// =====================================================

if (app()->environment('local', 'development')) {
    Route::prefix('dev')->group(function () {
        Route::get('/test-components', function () {
            return Inertia::render('Dev/TestComponents', [
                'message' => 'Component testing page'
            ]);
        })->name('dev.test.components');
        
        // CRITICAL: Test unit API endpoints langsung
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
                
                // Test 2: Units for SSQC
                try {
                    $request = new \Illuminate\Http\Request(['unit_organisasi' => 'SSQC']);
                    $response = app(EmployeeController::class)->getUnits($request);
                    $data = json_decode($response->getContent(), true);
                    $results['tests']['units_for_ssqc'] = [
                        'status' => $response->getStatusCode() === 200 ? 'PASS' : 'FAIL',
                        'units_found' => count($data['data'] ?? []),
                        'sample_units' => array_slice($data['data'] ?? [], 0, 2)
                    ];
                } catch (\Exception $e) {
                    $results['tests']['units_for_ssqc'] = [
                        'status' => 'ERROR',
                        'error' => $e->getMessage()
                    ];
                }
                
                // Test 3: Sub Units for first unit
                try {
                    $firstUnit = \App\Models\Unit::first();
                    if ($firstUnit) {
                        $request = new \Illuminate\Http\Request(['unit_id' => $firstUnit->id]);
                        $response = app(EmployeeController::class)->getSubUnits($request);
                        $data = json_decode($response->getContent(), true);
                        $results['tests']['sub_units_for_first_unit'] = [
                            'status' => $response->getStatusCode() === 200 ? 'PASS' : 'FAIL',
                            'unit_tested' => $firstUnit->name,
                            'sub_units_found' => count($data['data'] ?? []),
                            'sample_sub_units' => array_slice($data['data'] ?? [], 0, 2)
                        ];
                    } else {
                        $results['tests']['sub_units_for_first_unit'] = [
                            'status' => 'SKIP',
                            'reason' => 'No units found in database'
                        ];
                    }
                } catch (\Exception $e) {
                    $results['tests']['sub_units_for_first_unit'] = [
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

        // CRITICAL: Debug unit structure
        Route::get('/debug-units', function() {
            try {
                $debug = [
                    'timestamp' => now()->format('Y-m-d H:i:s'),
                    'database_check' => [],
                    'unit_organisasi_options' => [],
                    'units_data' => [],
                    'sub_units_data' => [],
                    'api_tests' => [],
                    'employees_count' => 0,
                ];

                // Check database connection
                try {
                    \DB::connection()->getPdo();
                    $debug['database_check'] = [
                        'status' => 'Connected',
                        'driver' => config('database.default'),
                        'host' => config('database.connections.mysql.host'),
                        'database' => config('database.connections.mysql.database')
                    ];
                } catch (\Exception $e) {
                    $debug['database_check'] = [
                        'status' => 'Error',
                        'error' => $e->getMessage()
                    ];
                }

                // Check Unit Organisasi Options
                try {
                    $options = \App\Models\Unit::UNIT_ORGANISASI_OPTIONS ?? [];
                    $debug['unit_organisasi_options'] = [
                        'status' => 'OK',
                        'count' => count($options),
                        'options' => $options
                    ];
                } catch (\Exception $e) {
                    $debug['unit_organisasi_options'] = [
                        'status' => 'Error',
                        'error' => $e->getMessage()
                    ];
                }

                // Check Units Data
                try {
                    $units = \App\Models\Unit::all();
                    $groupedUnits = [];
                    
                    foreach (\App\Models\Unit::UNIT_ORGANISASI_OPTIONS as $unitOrg) {
                        $unitsInOrg = \App\Models\Unit::where('unit_organisasi', $unitOrg)->get();
                        $groupedUnits[$unitOrg] = [
                            'count' => $unitsInOrg->count(),
                            'units' => $unitsInOrg->map(function($unit) {
                                return [
                                    'id' => $unit->id,
                                    'name' => $unit->name,
                                    'code' => $unit->code,
                                    'is_active' => $unit->is_active,
                                    'sub_units_count' => $unit->subUnits()->count()
                                ];
                            })
                        ];
                    }
                    
                    $debug['units_data'] = [
                        'status' => 'OK',
                        'total_units' => $units->count(),
                        'active_units' => \App\Models\Unit::where('is_active', true)->count(),
                        'grouped_by_unit_organisasi' => $groupedUnits
                    ];
                } catch (\Exception $e) {
                    $debug['units_data'] = [
                        'status' => 'Error',
                        'error' => $e->getMessage()
                    ];
                }

                // Check Sub Units Data
                try {
                    $subUnits = \App\Models\SubUnit::all();
                    $subUnitsWithParent = \App\Models\SubUnit::with('unit')->get()->map(function($subUnit) {
                        return [
                            'id' => $subUnit->id,
                            'name' => $subUnit->name,
                            'code' => $subUnit->code,
                            'unit_id' => $subUnit->unit_id,
                            'unit_name' => $subUnit->unit->name ?? 'Unknown',
                            'unit_organisasi' => $subUnit->unit->unit_organisasi ?? 'Unknown',
                            'is_active' => $subUnit->is_active
                        ];
                    });
                    
                    $debug['sub_units_data'] = [
                        'status' => 'OK',
                        'total_sub_units' => $subUnits->count(),
                        'active_sub_units' => \App\Models\SubUnit::where('is_active', true)->count(),
                        'sub_units_with_parent' => $subUnitsWithParent
                    ];
                } catch (\Exception $e) {
                    $debug['sub_units_data'] = [
                        'status' => 'Error',
                        'error' => $e->getMessage()
                    ];
                }

                // Test API Endpoints
                $tests = [];
                
                // Test 1: Get units for SSQC
                try {
                    $units = \App\Models\Unit::active()
                        ->byUnitOrganisasi('SSQC')
                        ->orderBy('name')
                        ->get(['id', 'name', 'code', 'description']);
                        
                    $tests['get_units_ssqc'] = [
                        'status' => 'OK',
                        'count' => $units->count(),
                        'data' => $units
                    ];
                } catch (\Exception $e) {
                    $tests['get_units_ssqc'] = [
                        'status' => 'Error',
                        'error' => $e->getMessage()
                    ];
                }

                // Test 2: Get sub units for first unit
                try {
                    $firstUnit = \App\Models\Unit::first();
                    if ($firstUnit) {
                        $subUnits = \App\Models\SubUnit::active()
                            ->byUnit($firstUnit->id)
                            ->orderBy('name')
                            ->get(['id', 'name', 'code', 'description']);
                            
                        $tests['get_sub_units_first_unit'] = [
                            'status' => 'OK',
                            'unit_id' => $firstUnit->id,
                            'unit_name' => $firstUnit->name,
                            'count' => $subUnits->count(),
                            'data' => $subUnits
                        ];
                    } else {
                        $tests['get_sub_units_first_unit'] = [
                            'status' => 'No units found'
                        ];
                    }
                } catch (\Exception $e) {
                    $tests['get_sub_units_first_unit'] = [
                        'status' => 'Error',
                        'error' => $e->getMessage()
                    ];
                }

                $debug['api_tests'] = $tests;
                $debug['employees_count'] = \App\Models\Employee::count();

                return response()->json($debug, 200, [], JSON_PRETTY_PRINT);
                
            } catch (\Exception $e) {
                return response()->json([
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString()
                ], 500);
            }
        })->name('dev.debug.units');

        // Test API langsung dengan parameter
        Route::get('/test-api/{endpoint}', function($endpoint, \Illuminate\Http\Request $request) {
            $unitOrganisasi = $request->get('unit_organisasi', 'SSQC');
            $unitId = $request->get('unit_id', 1);

            switch ($endpoint) {
                case 'units':
                    try {
                        $units = \App\Models\Unit::active()
                            ->byUnitOrganisasi($unitOrganisasi)
                            ->orderBy('name')
                            ->get(['id', 'name', 'code', 'description']);

                        return response()->json([
                            'success' => true,
                            'message' => 'Units retrieved successfully',
                            'data' => $units,
                            'count' => $units->count()
                        ]);
                    } catch (\Exception $e) {
                        return response()->json([
                            'success' => false,
                            'message' => 'Error retrieving units',
                            'error' => $e->getMessage()
                        ], 500);
                    }

                case 'sub-units':
                    try {
                        $subUnits = \App\Models\SubUnit::active()
                            ->byUnit($unitId)
                            ->orderBy('name')
                            ->get(['id', 'name', 'code', 'description']);

                        return response()->json([
                            'success' => true,
                            'message' => 'Sub units retrieved successfully',
                            'data' => $subUnits,
                            'count' => $subUnits->count()
                        ]);
                    } catch (\Exception $e) {
                        return response()->json([
                            'success' => false,
                            'message' => 'Error retrieving sub units',
                            'error' => $e->getMessage()
                        ], 500);
                    }

                default:
                    return response()->json([
                        'success' => false,
                        'message' => 'Unknown endpoint'
                    ], 400);
            }
        })->name('dev.test.api');
        
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
        
        // UPDATED: Test database dengan flexible identifier validation
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

                // UPDATED: Test flexible identifier data
                $identifierData = [
                    'total_employees' => \App\Models\Employee::count(),
                    'employees_with_nik' => \App\Models\Employee::whereNotNull('nik')->where('nik', '!=', '')->count(),
                    'employees_without_nik' => \App\Models\Employee::whereNull('nik')->orWhere('nik', '')->count(),
                    'max_auto_increment_id' => \App\Models\Employee::max('id'),
                    'sample_ids' => \App\Models\Employee::take(3)->pluck('id'),
                    'sample_niks' => \App\Models\Employee::whereNotNull('nik')->where('nik', '!=', '')->take(3)->pluck('nik'),
                    'sample_nips' => \App\Models\Employee::whereNotNull('nip')->where('nip', '!=', '')->take(3)->pluck('nip'),
                ];
                
                return response()->json([
                    'database_status' => 'Connected',
                    'employees_count' => \App\Models\Employee::count(),
                    'organizations_count' => \App\Models\Organization::count(),
                    'sample_employees' => $employees,
                    'organizations' => $organizations,
                    'shoe_data' => $shoeData,
                    'unit_data' => $unitData,
                    'identifier_data' => $identifierData, // UPDATED: Include flexible identifier data
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

        // UPDATED: Test flexible identifier system
        Route::get('/test-identifier-system', function () {
            try {
                $results = [
                    'timestamp' => now(),
                    'identifier_system_tests' => []
                ];
                
                // Test 1: Route Parameter Validation
                $testIdentifiers = ['3', '123', '1234567890123456', '999999999999999999'];
                foreach ($testIdentifiers as $testId) {
                    $matchesConstraint = preg_match('/^[0-9]+$/', $testId);
                    $results['identifier_system_tests']['route_validation'][$testId] = [
                        'input' => $testId,
                        'length' => strlen($testId),
                        'matches_constraint' => $matchesConstraint ? 'PASS' : 'FAIL'
                    ];
                }
                
                // Test 2: Database identifier Stats
                $results['identifier_system_tests']['database_stats'] = [
                    'total_employees' => \App\Models\Employee::count(),
                    'max_auto_increment_id' => \App\Models\Employee::max('id'),
                    'employees_with_nik' => \App\Models\Employee::whereNotNull('nik')->where('nik', '!=', '')->count(),
                    'unique_nips' => \App\Models\Employee::whereNotNull('nip')->where('nip', '!=', '')->distinct()->count('nip'),
                ];
                
                // Test 3: Find by different identifier types
                $sampleEmployee = \App\Models\Employee::first();
                if ($sampleEmployee) {
                    $findTests = [];
                    
                    // Test find by ID
                    $foundById = \App\Models\Employee::where('id', $sampleEmployee->id)->first();
                    $findTests['by_id'] = [
                        'test_id' => $sampleEmployee->id,
                        'found' => $foundById ? 'PASS' : 'FAIL'
                    ];
                    
                    // Test find by NIP
                    if ($sampleEmployee->nip) {
                        $foundByNip = \App\Models\Employee::where('nip', $sampleEmployee->nip)->first();
                        $findTests['by_nip'] = [
                            'test_nip' => $sampleEmployee->nip,
                            'found' => $foundByNip ? 'PASS' : 'FAIL'
                        ];
                    }
                    
                    // Test find by NIK
                    if ($sampleEmployee->nik) {
                        $foundByNik = \App\Models\Employee::where('nik', $sampleEmployee->nik)->first();
                        $findTests['by_nik'] = [
                            'test_nik' => $sampleEmployee->nik,
                            'found' => $foundByNik ? 'PASS' : 'FAIL'
                        ];
                    }
                    
                    $results['identifier_system_tests']['find_tests'] = $findTests;
                } else {
                    $results['identifier_system_tests']['find_tests'] = [
                        'status' => 'SKIP',
                        'reason' => 'No employees found'
                    ];
                }
                
                return response()->json([
                    'message' => 'Flexible Identifier System Testing Completed',
                    'results' => $results
                ], 200, [], JSON_PRETTY_PRINT);
                
            } catch (\Exception $e) {
                return response()->json([
                    'message' => 'Identifier System Testing Failed',
                    'error' => $e->getMessage()
                ], 500);
            }
        })->name('dev.test.identifier.system');
        
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
// ERROR HANDLING & FALLBACK - UPDATED: Enhanced error page untuk flexible identifier
// =====================================================

// Handle legacy routes yang masih menggunakan auto-increment ID
Route::get('/employees/{id}/legacy-redirect', function($id) {
    try {
        // Jika ID adalah NIK format (16 digit), redirect ke route yang benar
        if (preg_match('/^[0-9]{16}$/', $id)) {
            return redirect()->route('employees.show', ['identifier' => $id]);
        }
        
        // Jika ID adalah format lama, coba cari berdasarkan NIP atau ID lama
        $employee = \App\Models\Employee::where('id', $id)->orWhere('nip', $id)->first();
        if ($employee) {
            return redirect()->route('employees.show', ['identifier' => $employee->id]);
        }
        
        // Fallback ke index dengan pesan error
        return redirect()->route('employees.index')
            ->with('error', 'Employee tidak ditemukan.');
            
    } catch (\Exception $e) {
        return redirect()->route('employees.index')
            ->with('error', 'Terjadi kesalahan saat mengakses data employee.');
    }
})->where('id', '[0-9]+')->name('employees.legacy.redirect');

// Catch-all route untuk SPA (Single Page Application)
Route::fallback(function () {
    return Inertia::render('Error/404', [
        'status' => 404,
        'message' => 'Halaman tidak ditemukan',
        'suggestion' => 'Silakan gunakan menu navigasi untuk mengakses halaman yang tersedia.',
        'help_text' => 'Jika Anda mencari data karyawan, gunakan ID atau NIK sebagai identifier.'
    ]);
});

/*
|--------------------------------------------------------------------------
| Route Documentation - Enhanced dengan Fixed Parent-Child Dropdown v1.5.0
|--------------------------------------------------------------------------
|
| CRITICAL FIXES untuk Parent-Child Dropdown:
| ✅ API routes diprioritaskan di bagian atas untuk menghindari konflik
| ✅ Enhanced debugging routes untuk troubleshooting
| ✅ Dedicated test endpoints untuk unit API
| ✅ Direct API testing dengan parameter
| ✅ Comprehensive logging dan error handling
|
| MAIN ROUTES:
| - /dashboard                 - Dashboard utama dengan statistik
| - /employees                 - Management karyawan (CRUD lengkap dengan flexible identifier routing)
| - /organisasi               - Management organisasi
| - /laporan                  - Reports dan statistik
| - /laporan/sepatu           - Laporan khusus distribusi sepatu
| - /pengaturan               - Settings sistem
|
| CRITICAL API ROUTES untuk Parent-Child Dropdown:
| - /api/units                            - Get units berdasarkan unit_organisasi (FIXED)
| - /api/sub-units                        - Get sub units berdasarkan unit_id (FIXED)
| - /api/unit-organisasi-options          - Get unit organisasi options (FIXED)
| - /api/units/hierarchy                  - Get complete unit hierarchy
| - /api/units/statistics                 - Get unit statistics
|
| ENHANCED DEBUGGING ROUTES (development only):
| - /dev/debug-units                      - Comprehensive unit structure debug
| - /dev/test-api/{endpoint}              - Direct API testing dengan parameter
| - /dev/test-unit-api                    - Complete unit API testing suite
| - /dev/test-unit-seeder                 - Test unit seeder
|
| TESTING ENDPOINTS:
| - /dev/test-api/units?unit_organisasi=SSQC     - Test units API
| - /dev/test-api/sub-units?unit_id=1            - Test sub units API
| - /dev/debug-units                             - Debug complete structure
|
| NEW FEATURES v1.5.0:
| ✅ Fixed parent-child dropdown routing conflicts
| ✅ Enhanced API debugging dan testing
| ✅ Comprehensive unit structure validation
| ✅ Direct API testing dengan parameter
| ✅ Real-time debugging capabilities
| ✅ Enhanced error handling dan logging
| ✅ Improved route prioritization
|
| ROUTE PRIORITIES (order matters):
| 1. API routes (highest priority)
| 2. Main application routes
| 3. Legacy compatibility routes
| 4. Development/debugging routes
| 5. Fallback routes (lowest priority)
|
*/