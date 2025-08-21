<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\EmployeeController;
use App\Http\Controllers\DashboardController;

/*
|--------------------------------------------------------------------------
| API Routes - GAPURA ANGKASA SDM System v1.6.0
|--------------------------------------------------------------------------
|
| API routes untuk cascading dropdown, data management, dan employee history
| Fokus pada parent-child dropdown untuk unit organisasi > unit > sub unit
| NEW: Employee History API untuk dashboard widget
| Base color: putih dengan hover hijau (#439454)
|
*/

// =====================================================
// DASHBOARD API ROUTES - PRIORITY ROUTES
// =====================================================

Route::prefix('dashboard')->group(function () {
    // Dashboard statistics dan charts
    Route::get('/statistics', function() {
        try {
            if (class_exists('App\Http\Controllers\DashboardController')) {
                return app('App\Http\Controllers\DashboardController')->getStatistics();
            } else {
                return app(EmployeeController::class)->getStatistics();
            }
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Dashboard statistics unavailable',
                'error' => $e->getMessage()
            ], 500);
        }
    })->name('api.dashboard.statistics');
    
    Route::get('/charts', function() {
        try {
            if (class_exists('App\Http\Controllers\DashboardController')) {
                return app('App\Http\Controllers\DashboardController')->getChartData();
            } else {
                return response()->json([
                    'success' => true,
                    'data' => [],
                    'message' => 'Chart data not available'
                ]);
            }
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Chart data unavailable',
                'error' => $e->getMessage()
            ], 500);
        }
    })->name('api.dashboard.charts');
    
    Route::get('/activities', function() {
        try {
            if (class_exists('App\Http\Controllers\DashboardController')) {
                return app('App\Http\Controllers\DashboardController')->getRecentActivities();
            } else {
                return response()->json([
                    'success' => true,
                    'data' => [],
                    'message' => 'Activities not available'
                ]);
            }
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Activities unavailable',
                'error' => $e->getMessage()
            ], 500);
        }
    })->name('api.dashboard.activities');
    
    // NEW: Employee History API Routes - FITUR UTAMA
    Route::get('/employee-history', function() {
        try {
            if (class_exists('App\Http\Controllers\DashboardController')) {
                return app('App\Http\Controllers\DashboardController')->getEmployeeHistory();
            } else {
                return response()->json([
                    'success' => false,
                    'history' => [],
                    'total' => 0,
                    'error' => 'DashboardController not available'
                ], 500);
            }
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'history' => [],
                'total' => 0,
                'error' => 'Employee history unavailable: ' . $e->getMessage()
            ], 500);
        }
    })->name('api.dashboard.employee.history');
    
    Route::get('/employee-history-summary', function() {
        try {
            if (class_exists('App\Http\Controllers\DashboardController')) {
                return app('App\Http\Controllers\DashboardController')->getEmployeeHistorySummary();
            } else {
                return response()->json([
                    'success' => false,
                    'summary' => [
                        'today' => 0,
                        'week' => 0,
                        'month' => 0,
                    ],
                    'latest_employees' => [],
                    'error' => 'DashboardController not available'
                ], 500);
            }
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'summary' => [
                    'today' => 0,
                    'week' => 0,
                    'month' => 0,
                ],
                'latest_employees' => [],
                'error' => 'Employee history summary unavailable: ' . $e->getMessage()
            ], 500);
        }
    })->name('api.dashboard.employee.history.summary');
});

// =====================================================
// UNIT ORGANISASI & CASCADING DROPDOWN API ROUTES
// =====================================================

Route::prefix('units')->group(function () {
    // FIXED: Get units berdasarkan unit organisasi
    Route::get('/', [EmployeeController::class, 'getUnits'])->name('api.units');
    Route::get('/by-organisasi', [EmployeeController::class, 'getUnits'])->name('api.units.by-organisasi');
    
    // Enhanced unit routes untuk debugging dan monitoring
    Route::get('/hierarchy', [EmployeeController::class, 'getAllUnitsHierarchy'])->name('api.units.hierarchy');
    Route::get('/statistics', [EmployeeController::class, 'getUnitStatistics'])->name('api.units.statistics');
});

Route::prefix('sub-units')->group(function () {
    // FIXED: Get sub units berdasarkan unit_id
    Route::get('/', [EmployeeController::class, 'getSubUnits'])->name('api.sub-units');
    Route::get('/by-unit', [EmployeeController::class, 'getSubUnits'])->name('api.sub-units.by-unit');
});

// Get unit organisasi options
Route::get('/unit-organisasi-options', [EmployeeController::class, 'getUnitOrganisasiOptions'])->name('api.unit-organisasi-options');

// =====================================================
// EMPLOYEE MANAGEMENT API ROUTES
// =====================================================

Route::prefix('employees')->group(function () {
    // Search and filter
    Route::get('/search', [EmployeeController::class, 'search'])->name('api.employees.search');
    Route::get('/statistics', [EmployeeController::class, 'getStatistics'])->name('api.employees.statistics');
    Route::get('/filter-options', [EmployeeController::class, 'getFilterOptions'])->name('api.employees.filter-options');
    
    // Validation endpoints
    Route::post('/validate-nik', [EmployeeController::class, 'validateNik'])->name('api.employees.validate-nik');
    Route::post('/validate-nip', [EmployeeController::class, 'validateNip'])->name('api.employees.validate-nip');
    Route::post('/validate', [EmployeeController::class, 'validateData'])->name('api.employees.validate');
    
    // Bulk operations
    Route::post('/bulk-action', [EmployeeController::class, 'bulkAction'])->name('api.employees.bulk-action');
    
    // Profile API dengan flexible identifier support
    Route::get('/{identifier}/profile', [EmployeeController::class, 'profile'])
        ->name('api.employees.profile')
        ->where('identifier', '[0-9]+');
});

// =====================================================
// QUICK VALIDATION ROUTES
// =====================================================

Route::prefix('validate')->group(function () {
    Route::get('/nik/{nik}', function ($nik) {
        try {
            $exists = \App\Models\Employee::where('nik', $nik)->exists();
            return response()->json([
                'success' => true,
                'available' => !$exists,
                'message' => $exists ? 'NIK sudah terdaftar' : 'NIK tersedia'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'available' => false,
                'message' => 'Error validating NIK: ' . $e->getMessage()
            ], 500);
        }
    })->name('api.validate.nik');

    Route::get('/nip/{nip}', function ($nip) {
        try {
            $exists = \App\Models\Employee::where('nip', $nip)->exists();
            return response()->json([
                'success' => true,
                'available' => !$exists,
                'message' => $exists ? 'NIP sudah terdaftar' : 'NIP tersedia'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'available' => false,
                'message' => 'Error validating NIP: ' . $e->getMessage()
            ], 500);
        }
    })->name('api.validate.nip');
});

// =====================================================
// DEBUGGING & TESTING ROUTES (Development Only)
// =====================================================

if (app()->environment('local', 'development')) {
    Route::prefix('test')->group(function () {
        // Test API connectivity
        Route::get('/connectivity', function () {
            return response()->json([
                'success' => true,
                'message' => 'API is working correctly',
                'timestamp' => now(),
                'environment' => app()->environment(),
                'routes_loaded' => true,
                'version' => '1.6.0'
            ]);
        })->name('api.test.connectivity');
        
        // Test unit API dengan parameter
        Route::get('/units/{unitOrganisasi}', function ($unitOrganisasi) {
            try {
                $request = new \Illuminate\Http\Request(['unit_organisasi' => $unitOrganisasi]);
                return app(EmployeeController::class)->getUnits($request);
            } catch (\Exception $e) {
                return response()->json([
                    'success' => false,
                    'message' => 'Error testing units API',
                    'error' => $e->getMessage()
                ], 500);
            }
        })->name('api.test.units');
        
        // Test sub units API dengan parameter
        Route::get('/sub-units/{unitId}', function ($unitId) {
            try {
                $request = new \Illuminate\Http\Request(['unit_id' => $unitId]);
                return app(EmployeeController::class)->getSubUnits($request);
            } catch (\Exception $e) {
                return response()->json([
                    'success' => false,
                    'message' => 'Error testing sub units API',
                    'error' => $e->getMessage()
                ], 500);
            }
        })->name('api.test.sub-units');
        
        // NEW: Test employee history API
        Route::get('/employee-history', function () {
            try {
                if (class_exists('App\Http\Controllers\DashboardController')) {
                    $response = app('App\Http\Controllers\DashboardController')->getEmployeeHistory();
                    $data = json_decode($response->getContent(), true);
                    
                    return response()->json([
                        'success' => true,
                        'test_endpoint' => '/api/dashboard/employee-history',
                        'response_status' => $response->getStatusCode(),
                        'data_count' => count($data['history'] ?? []),
                        'sample_data' => array_slice($data['history'] ?? [], 0, 2),
                        'timestamp' => now()
                    ]);
                } else {
                    return response()->json([
                        'success' => false,
                        'message' => 'DashboardController not available',
                        'test_endpoint' => '/api/dashboard/employee-history'
                    ], 500);
                }
            } catch (\Exception $e) {
                return response()->json([
                    'success' => false,
                    'message' => 'Error testing employee history API',
                    'error' => $e->getMessage()
                ], 500);
            }
        })->name('api.test.employee.history');
        
        // NEW: Test employee history summary API
        Route::get('/employee-history-summary', function () {
            try {
                if (class_exists('App\Http\Controllers\DashboardController')) {
                    $response = app('App\Http\Controllers\DashboardController')->getEmployeeHistorySummary();
                    $data = json_decode($response->getContent(), true);
                    
                    return response()->json([
                        'success' => true,
                        'test_endpoint' => '/api/dashboard/employee-history-summary',
                        'response_status' => $response->getStatusCode(),
                        'summary_data' => $data['summary'] ?? [],
                        'employees_count' => count($data['latest_employees'] ?? []),
                        'timestamp' => now()
                    ]);
                } else {
                    return response()->json([
                        'success' => false,
                        'message' => 'DashboardController not available',
                        'test_endpoint' => '/api/dashboard/employee-history-summary'
                    ], 500);
                }
            } catch (\Exception $e) {
                return response()->json([
                    'success' => false,
                    'message' => 'Error testing employee history summary API',
                    'error' => $e->getMessage()
                ], 500);
            }
        })->name('api.test.employee.history.summary');
        
        // Test complete cascade functionality
        Route::get('/cascade/{unitOrganisasi}', function ($unitOrganisasi) {
            try {
                $result = [
                    'unit_organisasi' => $unitOrganisasi,
                    'timestamp' => now(),
                    'units' => [],
                    'sub_units' => []
                ];
                
                // Get units
                $unitsRequest = new \Illuminate\Http\Request(['unit_organisasi' => $unitOrganisasi]);
                $unitsResponse = app(EmployeeController::class)->getUnits($unitsRequest);
                $unitsData = json_decode($unitsResponse->getContent(), true);
                
                if ($unitsData['success'] && !empty($unitsData['data'])) {
                    $result['units'] = $unitsData['data'];
                    
                    // Get sub units for first unit
                    $firstUnit = $unitsData['data'][0];
                    $subUnitsRequest = new \Illuminate\Http\Request(['unit_id' => $firstUnit['id']]);
                    $subUnitsResponse = app(EmployeeController::class)->getSubUnits($subUnitsRequest);
                    $subUnitsData = json_decode($subUnitsResponse->getContent(), true);
                    
                    if ($subUnitsData['success']) {
                        $result['sub_units'] = $subUnitsData['data'];
                    }
                }
                
                return response()->json([
                    'success' => true,
                    'data' => $result,
                    'message' => 'Cascade test completed'
                ]);
                
            } catch (\Exception $e) {
                return response()->json([
                    'success' => false,
                    'message' => 'Error testing cascade functionality',
                    'error' => $e->getMessage()
                ], 500);
            }
        })->name('api.test.cascade');
        
        // NEW: Complete API testing suite
        Route::get('/all-apis', function () {
            $results = [
                'timestamp' => now(),
                'environment' => app()->environment(),
                'version' => '1.6.0',
                'tests' => []
            ];
            
            try {
                // Test 1: Dashboard Statistics
                $statsResponse = app('App\Http\Controllers\DashboardController')->getStatistics();
                $results['tests']['dashboard_statistics'] = [
                    'status' => $statsResponse->getStatusCode() === 200 ? 'PASS' : 'FAIL',
                    'endpoint' => '/api/dashboard/statistics'
                ];
            } catch (\Exception $e) {
                $results['tests']['dashboard_statistics'] = [
                    'status' => 'ERROR',
                    'error' => $e->getMessage()
                ];
            }
            
            try {
                // Test 2: Employee History
                $historyResponse = app('App\Http\Controllers\DashboardController')->getEmployeeHistory();
                $historyData = json_decode($historyResponse->getContent(), true);
                $results['tests']['employee_history'] = [
                    'status' => $historyResponse->getStatusCode() === 200 && $historyData['success'] ? 'PASS' : 'FAIL',
                    'endpoint' => '/api/dashboard/employee-history',
                    'data_count' => count($historyData['history'] ?? [])
                ];
            } catch (\Exception $e) {
                $results['tests']['employee_history'] = [
                    'status' => 'ERROR',
                    'error' => $e->getMessage()
                ];
            }
            
            try {
                // Test 3: Employee History Summary
                $summaryResponse = app('App\Http\Controllers\DashboardController')->getEmployeeHistorySummary();
                $summaryData = json_decode($summaryResponse->getContent(), true);
                $results['tests']['employee_history_summary'] = [
                    'status' => $summaryResponse->getStatusCode() === 200 && $summaryData['success'] ? 'PASS' : 'FAIL',
                    'endpoint' => '/api/dashboard/employee-history-summary',
                    'summary' => $summaryData['summary'] ?? []
                ];
            } catch (\Exception $e) {
                $results['tests']['employee_history_summary'] = [
                    'status' => 'ERROR',
                    'error' => $e->getMessage()
                ];
            }
            
            try {
                // Test 4: Units API
                $unitsRequest = new \Illuminate\Http\Request(['unit_organisasi' => 'SSQC']);
                $unitsResponse = app(EmployeeController::class)->getUnits($unitsRequest);
                $unitsData = json_decode($unitsResponse->getContent(), true);
                $results['tests']['units_api'] = [
                    'status' => $unitsResponse->getStatusCode() === 200 && $unitsData['success'] ? 'PASS' : 'FAIL',
                    'endpoint' => '/api/units?unit_organisasi=SSQC'
                ];
            } catch (\Exception $e) {
                $results['tests']['units_api'] = [
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
            
            return response()->json($results);
        })->name('api.test.all');
    });
}

// =====================================================
// AUTHENTICATED USER ROUTES
// =====================================================

Route::middleware('auth:sanctum')->group(function () {
    // Get authenticated user info
    Route::get('/user', function (Request $request) {
        return response()->json([
            'success' => true,
            'data' => $request->user(),
            'message' => 'User data retrieved successfully'
        ]);
    })->name('api.user');
    
    // User preferences dan settings
    Route::prefix('user')->group(function () {
        Route::get('/preferences', function (Request $request) {
            return response()->json([
                'success' => true,
                'data' => $request->user()->preferences ?? [],
                'message' => 'User preferences retrieved'
            ]);
        })->name('api.user.preferences');
        
        Route::post('/preferences', function (Request $request) {
            try {
                $user = $request->user();
                $user->preferences = $request->input('preferences', []);
                $user->save();
                
                return response()->json([
                    'success' => true,
                    'data' => $user->preferences,
                    'message' => 'User preferences updated'
                ]);
            } catch (\Exception $e) {
                return response()->json([
                    'success' => false,
                    'message' => 'Error updating preferences: ' . $e->getMessage()
                ], 500);
            }
        })->name('api.user.preferences.update');
    });
});

// =====================================================
// HEALTH CHECK & SYSTEM STATUS
// =====================================================

Route::get('/health', function () {
    try {
        // Check database connection
        $dbStatus = 'unknown';
        try {
            \Illuminate\Support\Facades\DB::connection()->getPdo();
            $dbStatus = 'connected';
        } catch (\Exception $e) {
            $dbStatus = 'disconnected';
        }
        
        // Check Employee model
        $employeeModelStatus = class_exists('App\Models\Employee') ? 'available' : 'missing';
        
        // Check DashboardController
        $dashboardControllerStatus = class_exists('App\Http\Controllers\DashboardController') ? 'available' : 'missing';
        
        // Check total employees
        $totalEmployees = 0;
        try {
            if (class_exists('App\Models\Employee')) {
                $totalEmployees = \App\Models\Employee::count();
            }
        } catch (\Exception $e) {
            // Ignore error for count
        }
        
        // Check recent employees (for history feature)
        $recentEmployees = 0;
        try {
            if (class_exists('App\Models\Employee')) {
                $recentEmployees = \App\Models\Employee::where('created_at', '>=', \Carbon\Carbon::now()->subDays(30))->count();
            }
        } catch (\Exception $e) {
            // Ignore error for count
        }
        
        return response()->json([
            'success' => true,
            'status' => 'healthy',
            'timestamp' => now(),
            'environment' => app()->environment(),
            'php_version' => PHP_VERSION,
            'laravel_version' => app()->version(),
            'system_version' => '1.6.0',
            'checks' => [
                'database' => $dbStatus,
                'employee_model' => $employeeModelStatus,
                'dashboard_controller' => $dashboardControllerStatus,
                'total_employees' => $totalEmployees,
                'recent_employees_30_days' => $recentEmployees,
                'api_routes' => 'loaded'
            ],
            'features' => [
                'employee_history' => $dashboardControllerStatus === 'available',
                'cascading_dropdown' => true,
                'employee_management' => $employeeModelStatus === 'available',
                'dashboard_statistics' => $dashboardControllerStatus === 'available'
            ]
        ]);
    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'status' => 'unhealthy',
            'error' => $e->getMessage(),
            'timestamp' => now()
        ], 500);
    }
})->name('api.health');

/*
|--------------------------------------------------------------------------
| API Routes Documentation - Enhanced dengan Employee History v1.6.0
|--------------------------------------------------------------------------
|
| CRITICAL API ENDPOINTS untuk Cascading Dropdown:
|
| 1. GET /api/units?unit_organisasi={unit_organisasi}
|    - Mendapatkan list units berdasarkan unit organisasi
|    - Response: {success: true, data: [...], message: "..."}
|
| 2. GET /api/sub-units?unit_id={unit_id}
|    - Mendapatkan list sub units berdasarkan unit_id
|    - Response: {success: true, data: [...], message: "..."}
|
| 3. GET /api/unit-organisasi-options
|    - Mendapatkan list unit organisasi options
|    - Response: {success: true, data: [...], message: "..."}
|
| NEW API ENDPOINTS untuk Employee History (v1.6.0):
|
| 4. GET /api/dashboard/employee-history
|    - Mendapatkan history karyawan yang baru ditambahkan (30 hari terakhir)
|    - Response: {success: true, history: [...], total: 0, period: "30 hari terakhir"}
|
| 5. GET /api/dashboard/employee-history-summary
|    - Mendapatkan ringkasan statistik employee history
|    - Response: {success: true, summary: {today: 0, week: 0, month: 0}, latest_employees: [...]}
|
| DASHBOARD API ENDPOINTS:
| - GET /api/dashboard/statistics
| - GET /api/dashboard/charts
| - GET /api/dashboard/activities
|
| TESTING ENDPOINTS (Development):
| - GET /api/test/connectivity
| - GET /api/test/units/{unitOrganisasi}
| - GET /api/test/sub-units/{unitId}
| - GET /api/test/cascade/{unitOrganisasi}
| - GET /api/test/employee-history                    [NEW]
| - GET /api/test/employee-history-summary            [NEW]
| - GET /api/test/all-apis                           [NEW]
|
| VALIDATION ENDPOINTS:
| - GET /api/validate/nik/{nik}
| - GET /api/validate/nip/{nip}
|
| SYSTEM ENDPOINTS:
| - GET /api/health                                  [ENHANCED]
|
| FORMAT RESPONSE KONSISTEN:
| {
|     "success": true/false,
|     "data": [...],
|     "message": "Description"
| }
|
| FITUR BARU v1.6.0:
| ✅ Employee History API untuk dashboard widget
| ✅ Enhanced testing endpoints untuk debugging
| ✅ Improved health check dengan feature detection
| ✅ Comprehensive API testing suite
| ✅ Employee history summary statistics
|
| UNIT ORGANISASI STRUKTUR:
| - EGM → EGM (no sub units)
| - GM → GM (no sub units)  
| - Back Office → MU, MK → Sub units
| - SSQC → MQ → Sub units
| - Airside → MO, ME → Sub units
| - Landside → MF, MS → Sub units
| - Ancillary → MB → Sub units
|
*/