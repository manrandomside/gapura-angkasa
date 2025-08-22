<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\EmployeeController;
use App\Http\Controllers\DashboardController;

/*
|--------------------------------------------------------------------------
| API Routes - GAPURA ANGKASA SDM System v1.8.0 - ENHANCED
|--------------------------------------------------------------------------
|
| FIXED: API routes untuk employee history dan management data  
| PRIORITY: Employee History API untuk History Modal
| Base color: putih dengan hover hijau (#439454)
| 
| ENHANCED: Simplified structure, better error handling, enhanced debugging
|
*/

// =====================================================
// CRITICAL DASHBOARD API ROUTES - EMPLOYEE HISTORY
// =====================================================

Route::prefix('dashboard')->group(function () {
    
    // CRITICAL: Employee History API - Direct controller calls (NO middleware)
    Route::get('/employee-history', [DashboardController::class, 'getEmployeeHistory'])
        ->name('api.dashboard.employee.history');
    
    Route::get('/employee-history-summary', [DashboardController::class, 'getEmployeeHistorySummary'])
        ->name('api.dashboard.employee.history.summary');
    
    Route::get('/employee-growth-chart', [DashboardController::class, 'getEmployeeGrowthChart'])
        ->name('api.dashboard.employee.growth.chart');
    
    // Dashboard core APIs
    Route::get('/statistics', [DashboardController::class, 'getStatistics'])
        ->name('api.dashboard.statistics');
    
    Route::get('/charts', [DashboardController::class, 'getChartData'])
        ->name('api.dashboard.charts');
    
    Route::get('/activities', [DashboardController::class, 'getRecentActivities'])
        ->name('api.dashboard.activities');
    
    Route::post('/export', [DashboardController::class, 'exportData'])
        ->name('api.dashboard.export');
    
    Route::get('/health', [DashboardController::class, 'healthCheck'])
        ->name('api.dashboard.health');
});

// =====================================================
// CASCADING DROPDOWN API ROUTES
// =====================================================

// Unit organisasi options
Route::get('/unit-organisasi-options', [EmployeeController::class, 'getUnitOrganisasiOptions'])
    ->name('api.unit.organisasi.options');

// Units based on unit organisasi
Route::get('/units', [EmployeeController::class, 'getUnits'])
    ->name('api.units');

// Sub units based on unit_id
Route::get('/sub-units', [EmployeeController::class, 'getSubUnits'])
    ->name('api.sub.units');

// Enhanced cascading routes dengan better error handling
Route::prefix('units')->group(function () {
    Route::get('/hierarchy', function() {
        try {
            if (method_exists(EmployeeController::class, 'getAllUnitsHierarchy')) {
                return app(EmployeeController::class)->getAllUnitsHierarchy();
            }
            
            return response()->json([
                'success' => false,
                'message' => 'Units hierarchy method not available'
            ], 404);
        } catch (\Exception $e) {
            \Log::error('API: Units hierarchy error', [
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Error getting units hierarchy',
                'error' => config('app.debug') ? $e->getMessage() : 'Internal server error'
            ], 500);
        }
    })->name('api.units.hierarchy');

    Route::get('/statistics', function() {
        try {
            if (method_exists(EmployeeController::class, 'getUnitStatistics')) {
                return app(EmployeeController::class)->getUnitStatistics();
            }
            
            return response()->json([
                'success' => false,
                'message' => 'Unit statistics method not available'
            ], 404);
        } catch (\Exception $e) {
            \Log::error('API: Unit statistics error', [
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Error getting unit statistics',
                'error' => config('app.debug') ? $e->getMessage() : 'Internal server error'
            ], 500);
        }
    })->name('api.units.statistics');
});

// =====================================================
// EMPLOYEE MANAGEMENT API ROUTES
// =====================================================

Route::prefix('employees')->group(function () {
    
    // Core employee operations dengan enhanced error handling
    Route::get('/search', function(Request $request) {
        try {
            if (!method_exists(EmployeeController::class, 'search')) {
                return response()->json([
                    'success' => false,
                    'message' => 'Search method not available'
                ], 404);
            }
            
            return app(EmployeeController::class)->search($request);
        } catch (\Exception $e) {
            \Log::error('API: Employee search error', [
                'error' => $e->getMessage(),
                'request_params' => $request->all()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Error in employee search',
                'error' => config('app.debug') ? $e->getMessage() : 'Search failed'
            ], 500);
        }
    })->name('api.employees.search');
    
    Route::get('/statistics', function() {
        try {
            if (!method_exists(EmployeeController::class, 'getStatistics')) {
                return response()->json([
                    'success' => false,
                    'message' => 'Statistics method not available'
                ], 404);
            }
            
            return app(EmployeeController::class)->getStatistics();
        } catch (\Exception $e) {
            \Log::error('API: Employee statistics error', ['error' => $e->getMessage()]);
            
            return response()->json([
                'success' => false,
                'message' => 'Error getting employee statistics',
                'error' => config('app.debug') ? $e->getMessage() : 'Statistics unavailable'
            ], 500);
        }
    })->name('api.employees.statistics');
    
    Route::get('/filter-options', function() {
        try {
            if (!method_exists(EmployeeController::class, 'getFilterOptions')) {
                return response()->json([
                    'success' => false,
                    'message' => 'Filter options method not available'
                ], 404);
            }
            
            return app(EmployeeController::class)->getFilterOptions();
        } catch (\Exception $e) {
            \Log::error('API: Filter options error', ['error' => $e->getMessage()]);
            
            return response()->json([
                'success' => false,
                'message' => 'Error getting filter options',
                'error' => config('app.debug') ? $e->getMessage() : 'Filter options unavailable'
            ], 500);
        }
    })->name('api.employees.filter-options');
    
    // Validation endpoints
    Route::post('/validate-nik', function(Request $request) {
        try {
            if (!method_exists(EmployeeController::class, 'validateNik')) {
                return response()->json([
                    'success' => false,
                    'message' => 'NIK validation method not available'
                ], 404);
            }
            
            return app(EmployeeController::class)->validateNik($request);
        } catch (\Exception $e) {
            \Log::error('API: NIK validation error', [
                'error' => $e->getMessage(),
                'nik' => $request->input('nik', 'not_provided')
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Error validating NIK',
                'error' => config('app.debug') ? $e->getMessage() : 'Validation failed'
            ], 500);
        }
    })->name('api.employees.validate-nik');
    
    Route::post('/validate-nip', function(Request $request) {
        try {
            if (!method_exists(EmployeeController::class, 'validateNip')) {
                return response()->json([
                    'success' => false,
                    'message' => 'NIP validation method not available'
                ], 404);
            }
            
            return app(EmployeeController::class)->validateNip($request);
        } catch (\Exception $e) {
            \Log::error('API: NIP validation error', [
                'error' => $e->getMessage(),
                'nip' => $request->input('nip', 'not_provided')
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Error validating NIP',
                'error' => config('app.debug') ? $e->getMessage() : 'Validation failed'
            ], 500);
        }
    })->name('api.employees.validate-nip');
    
    // Bulk operations
    Route::post('/bulk-action', function(Request $request) {
        try {
            if (!method_exists(EmployeeController::class, 'bulkAction')) {
                return response()->json([
                    'success' => false,
                    'message' => 'Bulk action method not available'
                ], 404);
            }
            
            return app(EmployeeController::class)->bulkAction($request);
        } catch (\Exception $e) {
            \Log::error('API: Bulk action error', [
                'error' => $e->getMessage(),
                'action' => $request->input('action', 'not_provided')
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Error in bulk action',
                'error' => config('app.debug') ? $e->getMessage() : 'Bulk action failed'
            ], 500);
        }
    })->name('api.employees.bulk-action');
    
    // Profile API dengan flexible identifier support
    Route::get('/{identifier}/profile', function($identifier) {
        try {
            if (!method_exists(EmployeeController::class, 'profile')) {
                return response()->json([
                    'success' => false,
                    'message' => 'Profile method not available'
                ], 404);
            }
            
            return app(EmployeeController::class)->profile($identifier);
        } catch (\Exception $e) {
            \Log::error('API: Employee profile error', [
                'error' => $e->getMessage(),
                'identifier' => $identifier
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Error getting employee profile',
                'error' => config('app.debug') ? $e->getMessage() : 'Profile unavailable'
            ], 500);
        }
    })->name('api.employees.profile')->where('identifier', '[0-9]+');
});

// =====================================================
// SIMPLIFIED VALIDATION ROUTES
// =====================================================

Route::prefix('validate')->group(function () {
    Route::get('/nik/{nik}', function ($nik) {
        try {
            if (!class_exists('App\Models\Employee')) {
                return response()->json([
                    'success' => false,
                    'available' => false,
                    'message' => 'Employee model not available'
                ], 500);
            }

            $exists = \App\Models\Employee::where('nik', $nik)->exists();
            return response()->json([
                'success' => true,
                'available' => !$exists,
                'message' => $exists ? 'NIK sudah terdaftar' : 'NIK tersedia'
            ]);
        } catch (\Exception $e) {
            \Log::error('API: NIK validation error', [
                'error' => $e->getMessage(),
                'nik' => $nik
            ]);
            
            return response()->json([
                'success' => false,
                'available' => false,
                'message' => 'Error validating NIK',
                'error' => config('app.debug') ? $e->getMessage() : 'Validation failed'
            ], 500);
        }
    })->name('api.validate.nik');

    Route::get('/nip/{nip}', function ($nip) {
        try {
            if (!class_exists('App\Models\Employee')) {
                return response()->json([
                    'success' => false,
                    'available' => false,
                    'message' => 'Employee model not available'
                ], 500);
            }

            $exists = \App\Models\Employee::where('nip', $nip)->exists();
            return response()->json([
                'success' => true,
                'available' => !$exists,
                'message' => $exists ? 'NIP sudah terdaftar' : 'NIP tersedia'
            ]);
        } catch (\Exception $e) {
            \Log::error('API: NIP validation error', [
                'error' => $e->getMessage(),
                'nip' => $nip
            ]);
            
            return response()->json([
                'success' => false,
                'available' => false,
                'message' => 'Error validating NIP',
                'error' => config('app.debug') ? $e->getMessage() : 'Validation failed'
            ], 500);
        }
    })->name('api.validate.nip');
});

// =====================================================
// ENHANCED HEALTH CHECK & SYSTEM STATUS
// =====================================================

Route::get('/health', function () {
    try {
        // Database connection check
        $dbStatus = 'unknown';
        $dbError = null;
        try {
            \Illuminate\Support\Facades\DB::connection()->getPdo();
            $dbStatus = 'connected';
        } catch (\Exception $e) {
            $dbStatus = 'disconnected';
            $dbError = $e->getMessage();
        }
        
        // Model availability checks
        $modelStatus = [
            'employee' => class_exists('App\Models\Employee') ? 'available' : 'missing',
            'unit' => class_exists('App\Models\Unit') ? 'available' : 'missing',
            'sub_unit' => class_exists('App\Models\SubUnit') ? 'available' : 'missing'
        ];
        
        // Controller availability checks
        $controllerStatus = [
            'dashboard' => class_exists('App\Http\Controllers\DashboardController') ? 'available' : 'missing',
            'employee' => class_exists('App\Http\Controllers\EmployeeController') ? 'available' : 'missing'
        ];
        
        // Employee statistics
        $employeeStats = [
            'total' => 0,
            'recent_30_days' => 0,
            'today' => 0,
            'has_data' => false
        ];
        
        if ($modelStatus['employee'] === 'available' && $dbStatus === 'connected') {
            try {
                $employeeStats['total'] = \App\Models\Employee::count();
                $employeeStats['recent_30_days'] = \App\Models\Employee::where('created_at', '>=', \Carbon\Carbon::now()->subDays(30))->count();
                $employeeStats['today'] = \App\Models\Employee::whereDate('created_at', \Carbon\Carbon::today())->count();
                $employeeStats['has_data'] = $employeeStats['total'] > 0;
            } catch (\Exception $e) {
                \Log::warning('Health check: Employee stats failed', ['error' => $e->getMessage()]);
            }
        }
        
        // Critical history methods check
        $historyStatus = [
            'available' => false,
            'methods' => []
        ];
        
        if ($controllerStatus['dashboard'] === 'available') {
            try {
                $controller = app('App\Http\Controllers\DashboardController');
                $historyStatus['methods'] = [
                    'getEmployeeHistory' => method_exists($controller, 'getEmployeeHistory'),
                    'getEmployeeHistorySummary' => method_exists($controller, 'getEmployeeHistorySummary'),
                    'getEmployeeGrowthChart' => method_exists($controller, 'getEmployeeGrowthChart')
                ];
                $historyStatus['available'] = $historyStatus['methods']['getEmployeeHistory'] && 
                                             $historyStatus['methods']['getEmployeeHistorySummary'];
            } catch (\Exception $e) {
                \Log::warning('Health check: History methods check failed', ['error' => $e->getMessage()]);
            }
        }
        
        // Overall system health
        $overallHealth = $dbStatus === 'connected' && 
                        $modelStatus['employee'] === 'available' && 
                        $controllerStatus['dashboard'] === 'available';
        
        return response()->json([
            'success' => true,
            'status' => $overallHealth ? 'healthy' : 'degraded',
            'timestamp' => now(),
            'environment' => app()->environment(),
            'versions' => [
                'php' => PHP_VERSION,
                'laravel' => app()->version(),
                'system' => '1.8.0'
            ],
            'database' => [
                'status' => $dbStatus,
                'connected' => $dbStatus === 'connected',
                'error' => $dbError
            ],
            'models' => $modelStatus,
            'controllers' => $controllerStatus,
            'employee_data' => $employeeStats,
            'history_functionality' => $historyStatus,
            'critical_endpoints' => [
                '/api/dashboard/employee-history' => $historyStatus['available'],
                '/api/dashboard/employee-history-summary' => $historyStatus['available'],
                '/api/units' => $controllerStatus['employee'] === 'available',
                '/api/sub-units' => $controllerStatus['employee'] === 'available'
            ],
            'features_status' => [
                'employee_history' => $historyStatus['available'],
                'cascading_dropdown' => $controllerStatus['employee'] === 'available',
                'employee_management' => $modelStatus['employee'] === 'available',
                'dashboard_statistics' => $controllerStatus['dashboard'] === 'available'
            ]
        ], $overallHealth ? 200 : 503);
    } catch (\Exception $e) {
        \Log::error('Health check failed', [
            'error' => $e->getMessage(),
            'file' => $e->getFile(),
            'line' => $e->getLine()
        ]);
        
        return response()->json([
            'success' => false,
            'status' => 'unhealthy',
            'error' => config('app.debug') ? $e->getMessage() : 'Health check failed',
            'timestamp' => now()
        ], 500);
    }
})->name('api.health');

// =====================================================
// ENHANCED DEBUGGING & TESTING ROUTES
// =====================================================

Route::prefix('test')->group(function () {
    
    // Basic connectivity test
    Route::get('/connectivity', function () {
        return response()->json([
            'success' => true,
            'message' => 'API connectivity successful',
            'timestamp' => now(),
            'environment' => app()->environment(),
            'versions' => [
                'php' => PHP_VERSION,
                'laravel' => app()->version(),
                'system' => '1.8.0'
            ]
        ]);
    })->name('api.test.connectivity');
    
    // CRITICAL: Test employee history API directly
    Route::get('/employee-history', function () {
        try {
            if (!class_exists('App\Http\Controllers\DashboardController')) {
                return response()->json([
                    'success' => false,
                    'message' => 'DashboardController not available',
                    'test_endpoint' => '/api/dashboard/employee-history'
                ], 500);
            }

            $controller = app('App\Http\Controllers\DashboardController');
            
            if (!method_exists($controller, 'getEmployeeHistory')) {
                return response()->json([
                    'success' => false,
                    'message' => 'getEmployeeHistory method not found',
                    'test_endpoint' => '/api/dashboard/employee-history',
                    'available_methods' => array_slice(get_class_methods($controller), 0, 10)
                ], 404);
            }

            $response = $controller->getEmployeeHistory();
            $data = json_decode($response->getContent(), true);
            
            return response()->json([
                'success' => true,
                'test_endpoint' => '/api/dashboard/employee-history',
                'api_response' => [
                    'status_code' => $response->getStatusCode(),
                    'success' => $data['success'] ?? false,
                    'data_count' => count($data['history'] ?? []),
                    'period' => $data['period'] ?? null,
                    'has_debug_info' => isset($data['debug'])
                ],
                'sample_data' => !empty($data['history']) ? array_slice($data['history'], 0, 1) : [],
                'debug_info' => $data['debug'] ?? null,
                'timestamp' => now()
            ]);
            
        } catch (\Exception $e) {
            \Log::error('Test: Employee history API failed', [
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Error testing employee history API',
                'error' => config('app.debug') ? $e->getMessage() : 'Test failed',
                'test_endpoint' => '/api/dashboard/employee-history'
            ], 500);
        }
    })->name('api.test.employee.history');
    
    // CRITICAL: Test employee history summary API
    Route::get('/employee-history-summary', function () {
        try {
            $controller = app('App\Http\Controllers\DashboardController');
            
            if (!method_exists($controller, 'getEmployeeHistorySummary')) {
                return response()->json([
                    'success' => false,
                    'message' => 'getEmployeeHistorySummary method not found'
                ], 404);
            }

            $response = $controller->getEmployeeHistorySummary();
            $data = json_decode($response->getContent(), true);
            
            return response()->json([
                'success' => true,
                'test_endpoint' => '/api/dashboard/employee-history-summary',
                'api_response' => [
                    'status_code' => $response->getStatusCode(),
                    'success' => $data['success'] ?? false,
                    'has_summary' => isset($data['summary']),
                    'latest_count' => count($data['latest_employees'] ?? [])
                ],
                'summary_data' => $data['summary'] ?? null,
                'timestamp' => now()
            ]);
            
        } catch (\Exception $e) {
            \Log::error('Test: Employee history summary API failed', ['error' => $e->getMessage()]);
            
            return response()->json([
                'success' => false,
                'message' => 'Error testing employee history summary API',
                'error' => config('app.debug') ? $e->getMessage() : 'Test failed'
            ], 500);
        }
    })->name('api.test.employee.history.summary');
    
    // Database and model check
    Route::get('/database-check', function () {
        try {
            $result = [
                'success' => true,
                'database' => ['connected' => false, 'error' => null],
                'employee_model' => ['available' => false, 'data' => []]
            ];
            
            // Database test
            try {
                \Illuminate\Support\Facades\DB::connection()->getPdo();
                $result['database']['connected'] = true;
            } catch (\Exception $e) {
                $result['database']['error'] = $e->getMessage();
            }
            
            // Employee model test
            if (class_exists('App\Models\Employee')) {
                $result['employee_model']['available'] = true;
                
                try {
                    $result['employee_model']['data'] = [
                        'total_count' => \App\Models\Employee::count(),
                        'recent_count' => \App\Models\Employee::where('created_at', '>=', \Carbon\Carbon::now()->subDays(30))->count(),
                        'today_count' => \App\Models\Employee::whereDate('created_at', \Carbon\Carbon::today())->count()
                    ];
                    
                    $latestEmployee = \App\Models\Employee::with(['unit', 'subUnit'])
                        ->latest('created_at')
                        ->first();
                    
                    if ($latestEmployee) {
                        $result['employee_model']['latest_employee'] = [
                            'id' => $latestEmployee->id,
                            'nama_lengkap' => $latestEmployee->nama_lengkap,
                            'unit_organisasi' => $latestEmployee->unit_organisasi,
                            'created_at' => $latestEmployee->created_at,
                            'days_ago' => $latestEmployee->created_at->diffInDays(\Carbon\Carbon::now()),
                            'has_organizational_structure' => isset($latestEmployee->organizational_structure)
                        ];
                    }
                } catch (\Exception $e) {
                    $result['employee_model']['error'] = $e->getMessage();
                }
            }
            
            return response()->json($result);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage()
            ], 500);
        }
    })->name('api.test.database');
    
    // Quick recent employees check
    Route::get('/recent-employees', function () {
        try {
            if (!class_exists('App\Models\Employee')) {
                return response()->json([
                    'success' => false,
                    'message' => 'Employee model not available'
                ], 500);
            }
            
            $recentEmployees = \App\Models\Employee::with(['unit', 'subUnit'])
                ->where('created_at', '>=', \Carbon\Carbon::now()->subDays(30))
                ->orderBy('created_at', 'desc')
                ->limit(5)
                ->get();
            
            return response()->json([
                'success' => true,
                'recent_employees_30_days' => $recentEmployees->count(),
                'total_employees' => \App\Models\Employee::count(),
                'employees' => $recentEmployees->map(function($emp) {
                    return [
                        'id' => $emp->id,
                        'nama_lengkap' => $emp->nama_lengkap,
                        'unit_organisasi' => $emp->unit_organisasi,
                        'organizational_structure' => $emp->organizational_structure ?? null,
                        'created_at' => $emp->created_at,
                        'days_ago' => $emp->created_at->diffInDays(\Carbon\Carbon::now())
                    ];
                }),
                'timestamp' => now()
            ]);
            
        } catch (\Exception $e) {
            \Log::error('Test: Recent employees check failed', ['error' => $e->getMessage()]);
            
            return response()->json([
                'success' => false,
                'message' => 'Error getting recent employees data',
                'error' => config('app.debug') ? $e->getMessage() : 'Test failed'
            ], 500);
        }
    })->name('api.test.recent.employees');
});

/*
|--------------------------------------------------------------------------
| API Routes Documentation - v1.8.0 ENHANCED
|--------------------------------------------------------------------------
|
| CRITICAL ENDPOINTS untuk History Modal:
| 1. GET /api/dashboard/employee-history -> Enhanced dengan relationship loading
| 2. GET /api/dashboard/employee-history-summary -> Enhanced dengan proper structure
| 3. GET /api/dashboard/employee-growth-chart -> Growth chart data
|
| DASHBOARD ENDPOINTS:
| - GET /api/dashboard/statistics -> Employee statistics
| - GET /api/dashboard/charts -> Chart data
| - GET /api/dashboard/activities -> Recent activities
| - GET /api/dashboard/health -> System health check
|
| CASCADING DROPDOWN ENDPOINTS:
| - GET /api/units?unit_organisasi={unit_organisasi} -> Units by organization
| - GET /api/sub-units?unit_id={unit_id} -> Sub units by unit
| - GET /api/unit-organisasi-options -> Organization options
|
| VALIDATION ENDPOINTS:
| - GET /api/validate/nik/{nik} -> NIK availability check
| - GET /api/validate/nip/{nip} -> NIP availability check
|
| TESTING ENDPOINTS (Enhanced Debugging):
| - GET /api/test/employee-history -> Direct history API test
| - GET /api/test/employee-history-summary -> Direct summary API test
| - GET /api/test/database-check -> Database and model verification
| - GET /api/test/recent-employees -> Employee data verification
|
| SYSTEM ENDPOINTS:
| - GET /api/health -> Enhanced system health with feature detection
|
| FEATURES v1.8.0:
| ✅ Enhanced error handling dengan consistent JSON responses
| ✅ Comprehensive logging untuk debugging
| ✅ Better health checks dengan detailed feature detection
| ✅ Simplified route structure untuk maintainability
| ✅ Enhanced debugging information untuk development
| ✅ Compatible dengan DashboardController dan Employee model yang diperbaiki
| ✅ NO middleware usage sesuai requirement
|
*/