<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\EmployeeController;
use App\Http\Controllers\DashboardController;

/*
|--------------------------------------------------------------------------
| API Routes - GAPURA ANGKASA SDM System v1.8.0 - FIXED
|--------------------------------------------------------------------------
|
| FIXED: API routes untuk employee history dan management data  
| PRIORITY: Employee History API untuk History Modal
| Base color: putih dengan hover hijau (#439454)
| 
| ENHANCED: Debugging routes added untuk troubleshooting History Modal
|
*/

// =====================================================
// DEBUGGING ROUTES - TEMPORARY (untuk troubleshooting)
// =====================================================

Route::prefix('debug')->group(function () {
    
    // Test 1: Database connection dan employee count
    Route::get('/database-test', function () {
        try {
            $total = \App\Models\Employee::count();
            $recent = \App\Models\Employee::where('created_at', '>=', \Carbon\Carbon::now()->subDays(30))->count();
            
            $sampleEmployees = \App\Models\Employee::where('created_at', '>=', \Carbon\Carbon::now()->subDays(30))
                ->orderBy('created_at', 'desc')
                ->limit(3)
                ->get(['id', 'nama_lengkap', 'unit_organisasi', 'created_at']);
            
            return response()->json([
                'test_name' => 'Database Connection Test',
                'success' => true,
                'database_connection' => 'OK',
                'total_employees' => $total,
                'recent_30_days' => $recent,
                'sample_recent_employees' => $sampleEmployees->toArray(),
                'test_timestamp' => now()
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'test_name' => 'Database Connection Test',
                'success' => false,
                'database_connection' => 'FAILED',
                'error' => $e->getMessage(),
                'line' => $e->getLine(),
                'file' => $e->getFile()
            ], 500);
        }
    });
    
    // Test 2: Direct API call ke DashboardController
    Route::get('/history-api-test', function () {
        try {
            $controller = app('App\Http\Controllers\DashboardController');
            
            if (!method_exists($controller, 'getEmployeeHistory')) {
                return response()->json([
                    'test_name' => 'History API Direct Test',
                    'success' => false,
                    'error' => 'Method getEmployeeHistory tidak ditemukan di DashboardController',
                    'available_methods' => array_slice(get_class_methods($controller), 0, 15)
                ], 404);
            }
            
            $response = $controller->getEmployeeHistory();
            $statusCode = $response->getStatusCode();
            $data = json_decode($response->getContent(), true);
            
            return response()->json([
                'test_name' => 'History API Direct Test',
                'test_success' => true,
                'api_status_code' => $statusCode,
                'api_response_keys' => array_keys($data ?? []),
                'api_has_success_field' => isset($data['success']),
                'api_success_value' => $data['success'] ?? 'NOT_SET',
                'api_has_history_field' => isset($data['history']),
                'history_count' => count($data['history'] ?? []),
                'api_error' => $data['error'] ?? null,
                'api_debug' => $data['debug'] ?? null,
                'first_history_record' => !empty($data['history']) ? $data['history'][0] : null,
                'raw_response_sample' => array_slice($data ?? [], 0, 5)
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'test_name' => 'History API Direct Test',
                'test_success' => false,
                'error' => $e->getMessage(),
                'line' => $e->getLine(),
                'file' => $e->getFile(),
                'trace' => config('app.debug') ? $e->getTraceAsString() : 'Hidden in production'
            ], 500);
        }
    });
    
    // Test 3: Model dan Controller availability
    Route::get('/system-check', function () {
        $models = [
            'Employee' => class_exists('App\Models\Employee'),
            'Unit' => class_exists('App\Models\Unit'),
            'SubUnit' => class_exists('App\Models\SubUnit'),
            'Organization' => class_exists('App\Models\Organization'),
        ];
        
        $controllers = [
            'DashboardController' => class_exists('App\Http\Controllers\DashboardController'),
            'EmployeeController' => class_exists('App\Http\Controllers\EmployeeController'),
        ];
        
        $methods = [];
        if (class_exists('App\Http\Controllers\DashboardController')) {
            $controller = app('App\Http\Controllers\DashboardController');
            $methods = [
                'getEmployeeHistory' => method_exists($controller, 'getEmployeeHistory'),
                'getEmployeeHistorySummary' => method_exists($controller, 'getEmployeeHistorySummary'),
                'buildOrganizationalStructureSafe' => method_exists($controller, 'buildOrganizationalStructureSafe'),
                'calculateHistorySummarySafe' => method_exists($controller, 'calculateHistorySummarySafe'),
            ];
        }
        
        return response()->json([
            'test_name' => 'System Check',
            'success' => true,
            'models_available' => $models,
            'controllers_available' => $controllers,
            'critical_methods' => $methods,
            'php_version' => PHP_VERSION,
            'laravel_version' => app()->version(),
            'environment' => app()->environment()
        ]);
    });
    
    // Test 4: Test route yang sebenarnya
    Route::get('/route-test', function () {
        try {
            // Test apakah route bisa diakses
            $url = url('/api/dashboard/employee-history');
            
            // Test dengan HTTP client
            $response = \Illuminate\Support\Facades\Http::get($url);
            
            return response()->json([
                'test_name' => 'Route Accessibility Test',
                'success' => true,
                'test_url' => $url,
                'http_status' => $response->status(),
                'response_size' => strlen($response->body()),
                'response_is_json' => $response->json() !== null,
                'response_sample' => $response->json() ?: substr($response->body(), 0, 500)
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'test_name' => 'Route Accessibility Test',
                'success' => false,
                'error' => $e->getMessage(),
                'test_url' => url('/api/dashboard/employee-history')
            ]);
        }
    });
});

// =====================================================
// CRITICAL DASHBOARD API ROUTES - EMPLOYEE HISTORY
// =====================================================

Route::prefix('dashboard')->group(function () {
    
    // CRITICAL: Employee History API - MUST BE FIRST AND WORKING
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

/*
|--------------------------------------------------------------------------
| API Routes Documentation - v1.8.0 FIXED
|--------------------------------------------------------------------------
|
| CRITICAL ENDPOINTS untuk History Modal:
| 1. GET /api/dashboard/employee-history -> Fixed dengan safe implementation
| 2. GET /api/dashboard/employee-history-summary -> Fixed dengan proper structure
| 3. GET /api/dashboard/employee-growth-chart -> Growth chart data
|
| DEBUGGING ENDPOINTS (TEMPORARY):
| - GET /api/debug/database-test -> Test database connection dan employee count
| - GET /api/debug/history-api-test -> Direct test ke DashboardController method
| - GET /api/debug/system-check -> Check model dan controller availability  
| - GET /api/debug/route-test -> Test route accessibility
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
| SYSTEM ENDPOINTS:
| - GET /api/health -> Enhanced system health with feature detection
|
| TROUBLESHOOTING STEPS:
| 1. Test /api/debug/database-test dulu
| 2. Kemudian /api/debug/history-api-test
| 3. Lalu /api/debug/system-check
| 4. Terakhir /api/debug/route-test
| 5. Jika semua OK, test /api/dashboard/employee-history
|
*/