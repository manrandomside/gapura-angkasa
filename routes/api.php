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
            return response()->json([
                'success' => false,
                'message' => 'Error getting units hierarchy: ' . $e->getMessage()
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
            return response()->json([
                'success' => false,
                'message' => 'Error getting unit statistics: ' . $e->getMessage()
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
            if (method_exists(EmployeeController::class, 'search')) {
                return app(EmployeeController::class)->search($request);
            }
            
            return response()->json([
                'success' => false,
                'message' => 'Search method not available'
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error in employee search: ' . $e->getMessage()
            ], 500);
        }
    })->name('api.employees.search');
    
    Route::get('/statistics', function() {
        try {
            if (method_exists(EmployeeController::class, 'getStatistics')) {
                return app(EmployeeController::class)->getStatistics();
            }
            
            return response()->json([
                'success' => false,
                'message' => 'Statistics method not available'
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error getting employee statistics: ' . $e->getMessage()
            ], 500);
        }
    })->name('api.employees.statistics');
    
    Route::get('/filter-options', function() {
        try {
            if (method_exists(EmployeeController::class, 'getFilterOptions')) {
                return app(EmployeeController::class)->getFilterOptions();
            }
            
            return response()->json([
                'success' => false,
                'message' => 'Filter options method not available'
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error getting filter options: ' . $e->getMessage()
            ], 500);
        }
    })->name('api.employees.filter-options');
    
    // Validation endpoints
    Route::post('/validate-nik', function(Request $request) {
        try {
            if (method_exists(EmployeeController::class, 'validateNik')) {
                return app(EmployeeController::class)->validateNik($request);
            }
            
            return response()->json([
                'success' => false,
                'message' => 'NIK validation method not available'
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error validating NIK: ' . $e->getMessage()
            ], 500);
        }
    })->name('api.employees.validate-nik');
    
    Route::post('/validate-nip', function(Request $request) {
        try {
            if (method_exists(EmployeeController::class, 'validateNip')) {
                return app(EmployeeController::class)->validateNip($request);
            }
            
            return response()->json([
                'success' => false,
                'message' => 'NIP validation method not available'
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error validating NIP: ' . $e->getMessage()
            ], 500);
        }
    })->name('api.employees.validate-nip');
    
    // Bulk operations
    Route::post('/bulk-action', function(Request $request) {
        try {
            if (method_exists(EmployeeController::class, 'bulkAction')) {
                return app(EmployeeController::class)->bulkAction($request);
            }
            
            return response()->json([
                'success' => false,
                'message' => 'Bulk action method not available'
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error in bulk action: ' . $e->getMessage()
            ], 500);
        }
    })->name('api.employees.bulk-action');
    
    // Profile API dengan flexible identifier support
    Route::get('/{identifier}/profile', function($identifier) {
        try {
            if (method_exists(EmployeeController::class, 'profile')) {
                return app(EmployeeController::class)->profile($identifier);
            }
            
            return response()->json([
                'success' => false,
                'message' => 'Profile method not available'
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error getting employee profile: ' . $e->getMessage()
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
            return response()->json([
                'success' => false,
                'available' => false,
                'message' => 'Error validating NIK: ' . $e->getMessage()
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
            return response()->json([
                'success' => false,
                'available' => false,
                'message' => 'Error validating NIP: ' . $e->getMessage()
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
        try {
            \Illuminate\Support\Facades\DB::connection()->getPdo();
            $dbStatus = 'connected';
        } catch (\Exception $e) {
            $dbStatus = 'disconnected: ' . $e->getMessage();
        }
        
        // Model availability checks
        $employeeModelStatus = class_exists('App\Models\Employee') ? 'available' : 'missing';
        $unitModelStatus = class_exists('App\Models\Unit') ? 'available' : 'missing';
        $subUnitModelStatus = class_exists('App\Models\SubUnit') ? 'available' : 'missing';
        
        // Controller availability checks
        $dashboardControllerStatus = class_exists('App\Http\Controllers\DashboardController') ? 'available' : 'missing';
        $employeeControllerStatus = class_exists('App\Http\Controllers\EmployeeController') ? 'available' : 'missing';
        
        // Employee statistics
        $totalEmployees = 0;
        $recentEmployees = 0;
        try {
            if (class_exists('App\Models\Employee')) {
                $totalEmployees = \App\Models\Employee::count();
                $recentEmployees = \App\Models\Employee::where('created_at', '>=', \Carbon\Carbon::now()->subDays(30))->count();
            }
        } catch (\Exception $e) {
            // Continue with 0 values
        }
        
        // Critical history methods check
        $historyMethodsAvailable = false;
        $historyMethods = [];
        if (class_exists('App\Http\Controllers\DashboardController')) {
            $controller = app('App\Http\Controllers\DashboardController');
            $historyMethods = [
                'getEmployeeHistory' => method_exists($controller, 'getEmployeeHistory'),
                'getEmployeeHistorySummary' => method_exists($controller, 'getEmployeeHistorySummary'),
                'getEmployeeGrowthChart' => method_exists($controller, 'getEmployeeGrowthChart'),
                'getOrganizationalStructure' => method_exists($controller, 'getOrganizationalStructure')
            ];
            $historyMethodsAvailable = $historyMethods['getEmployeeHistory'] && $historyMethods['getEmployeeHistorySummary'];
        }
        
        return response()->json([
            'success' => true,
            'status' => 'healthy',
            'timestamp' => now(),
            'environment' => app()->environment(),
            'versions' => [
                'php' => PHP_VERSION,
                'laravel' => app()->version(),
                'system' => '1.8.0'
            ],
            'database' => [
                'status' => $dbStatus,
                'connection' => $dbStatus === 'connected'
            ],
            'models' => [
                'employee' => $employeeModelStatus,
                'unit' => $unitModelStatus,
                'sub_unit' => $subUnitModelStatus
            ],
            'controllers' => [
                'dashboard' => $dashboardControllerStatus,
                'employee' => $employeeControllerStatus
            ],
            'employee_data' => [
                'total_employees' => $totalEmployees,
                'recent_30_days' => $recentEmployees,
                'has_data' => $totalEmployees > 0
            ],
            'history_functionality' => [
                'available' => $historyMethodsAvailable,
                'methods' => $historyMethods
            ],
            'critical_endpoints' => [
                '/api/dashboard/employee-history',
                '/api/dashboard/employee-history-summary',
                '/api/units',
                '/api/sub-units'
            ],
            'features_status' => [
                'employee_history' => $dashboardControllerStatus === 'available' && $historyMethodsAvailable,
                'cascading_dropdown' => $employeeControllerStatus === 'available',
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
                    'available_methods' => array_slice(get_class_methods($controller), 0, 15)
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
                'sample_data' => array_slice($data['history'] ?? [], 0, 1),
                'debug_info' => $data['debug'] ?? null,
                'timestamp' => now()
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error testing employee history API',
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'test_endpoint' => '/api/dashboard/employee-history'
            ], 500);
        }
    })->name('api.test.employee.history');
    
    // CRITICAL: Test employee history summary API
    Route::get('/employee-history-summary', function () {
        try {
            if (!class_exists('App\Http\Controllers\DashboardController')) {
                return response()->json([
                    'success' => false,
                    'message' => 'DashboardController not available'
                ], 500);
            }

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
                    'summary' => $data['summary'] ?? [],
                    'latest_count' => count($data['latest_employees'] ?? [])
                ],
                'timestamp' => now()
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error testing employee history summary API',
                'error' => $e->getMessage()
            ], 500);
        }
    })->name('api.test.employee.history.summary');
    
    // Test database dan employee data
    Route::get('/database-check', function () {
        try {
            // Database connection test
            $dbConnected = false;
            $dbError = null;
            try {
                \Illuminate\Support\Facades\DB::connection()->getPdo();
                $dbConnected = true;
            } catch (\Exception $e) {
                $dbError = $e->getMessage();
            }
            
            // Employee model test
            $employeeData = [
                'model_exists' => class_exists('App\Models\Employee'),
                'total_count' => 0,
                'recent_count' => 0,
                'sample_employee' => null
            ];
            
            if ($employeeData['model_exists']) {
                try {
                    $employeeData['total_count'] = \App\Models\Employee::count();
                    $employeeData['recent_count'] = \App\Models\Employee::where('created_at', '>=', \Carbon\Carbon::now()->subDays(30))->count();
                    
                    $sampleEmployee = \App\Models\Employee::with(['unit', 'subUnit'])
                        ->latest('created_at')
                        ->first();
                    
                    if ($sampleEmployee) {
                        $employeeData['sample_employee'] = [
                            'id' => $sampleEmployee->id,
                            'nama_lengkap' => $sampleEmployee->nama_lengkap,
                            'unit_organisasi' => $sampleEmployee->unit_organisasi,
                            'has_unit_relation' => $sampleEmployee->unit ? true : false,
                            'has_sub_unit_relation' => $sampleEmployee->subUnit ? true : false,
                            'organizational_structure' => $sampleEmployee->organizational_structure ?? 'accessor_not_available',
                            'created_at' => $sampleEmployee->created_at
                        ];
                    }
                } catch (\Exception $e) {
                    $employeeData['error'] = $e->getMessage();
                }
            }
            
            return response()->json([
                'success' => true,
                'database' => [
                    'connected' => $dbConnected,
                    'error' => $dbError
                ],
                'employee_data' => $employeeData,
                'timestamp' => now()
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
                'timestamp' => now()
            ], 500);
        }
    })->name('api.test.database');
    
    // Test cascading dropdown functionality
    Route::get('/cascading-dropdown', function () {
        try {
            $testResults = [];
            
            // Test unit organisasi options
            try {
                if (method_exists(EmployeeController::class, 'getUnitOrganisasiOptions')) {
                    $response = app(EmployeeController::class)->getUnitOrganisasiOptions();
                    $data = json_decode($response->getContent(), true);
                    $testResults['unit_organisasi_options'] = [
                        'status' => 'PASS',
                        'data_count' => count($data['data'] ?? [])
                    ];
                } else {
                    $testResults['unit_organisasi_options'] = [
                        'status' => 'SKIP',
                        'reason' => 'Method not available'
                    ];
                }
            } catch (\Exception $e) {
                $testResults['unit_organisasi_options'] = [
                    'status' => 'FAIL',
                    'error' => $e->getMessage()
                ];
            }
            
            // Test units for SSQC
            try {
                $request = new \Illuminate\Http\Request(['unit_organisasi' => 'SSQC']);
                $response = app(EmployeeController::class)->getUnits($request);
                $data = json_decode($response->getContent(), true);
                $testResults['units_api'] = [
                    'status' => ($data['success'] ?? false) ? 'PASS' : 'FAIL',
                    'test_param' => 'SSQC',
                    'data_count' => count($data['data'] ?? [])
                ];
            } catch (\Exception $e) {
                $testResults['units_api'] = [
                    'status' => 'FAIL',
                    'error' => $e->getMessage()
                ];
            }
            
            // Test sub units (if units data available)
            try {
                $request = new \Illuminate\Http\Request(['unit_id' => 'MQ']);
                $response = app(EmployeeController::class)->getSubUnits($request);
                $data = json_decode($response->getContent(), true);
                $testResults['sub_units_api'] = [
                    'status' => ($data['success'] ?? false) ? 'PASS' : 'FAIL',
                    'test_param' => 'MQ',
                    'data_count' => count($data['data'] ?? [])
                ];
            } catch (\Exception $e) {
                $testResults['sub_units_api'] = [
                    'status' => 'FAIL',
                    'error' => $e->getMessage()
                ];
            }
            
            return response()->json([
                'success' => true,
                'test_results' => $testResults,
                'timestamp' => now()
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
                'timestamp' => now()
            ], 500);
        }
    })->name('api.test.cascading');
    
    // Comprehensive API test suite
    Route::get('/all-apis', function () {
        $results = [
            'timestamp' => now(),
            'environment' => app()->environment(),
            'version' => '1.8.0',
            'summary' => [
                'total_tests' => 0,
                'passed' => 0,
                'failed' => 0,
                'skipped' => 0
            ],
            'tests' => []
        ];
        
        $tests = [
            'employee_history' => [
                'endpoint' => '/api/dashboard/employee-history',
                'method' => 'getEmployeeHistory',
                'controller' => 'DashboardController'
            ],
            'employee_history_summary' => [
                'endpoint' => '/api/dashboard/employee-history-summary',
                'method' => 'getEmployeeHistorySummary',
                'controller' => 'DashboardController'
            ],
            'dashboard_statistics' => [
                'endpoint' => '/api/dashboard/statistics',
                'method' => 'getStatistics',
                'controller' => 'DashboardController'
            ],
            'units_api' => [
                'endpoint' => '/api/units',
                'method' => 'getUnits',
                'controller' => 'EmployeeController'
            ],
            'dashboard_health' => [
                'endpoint' => '/api/dashboard/health',
                'method' => 'healthCheck',
                'controller' => 'DashboardController'
            ]
        ];
        
        foreach ($tests as $testName => $testConfig) {
            $results['summary']['total_tests']++;
            
            try {
                $controllerClass = 'App\Http\Controllers\\' . $testConfig['controller'];
                
                if (!class_exists($controllerClass)) {
                    $results['tests'][$testName] = [
                        'status' => 'SKIP',
                        'endpoint' => $testConfig['endpoint'],
                        'reason' => $testConfig['controller'] . ' not available'
                    ];
                    $results['summary']['skipped']++;
                    continue;
                }
                
                $controller = app($controllerClass);
                
                if (!method_exists($controller, $testConfig['method'])) {
                    $results['tests'][$testName] = [
                        'status' => 'SKIP',
                        'endpoint' => $testConfig['endpoint'],
                        'reason' => $testConfig['method'] . ' method not found'
                    ];
                    $results['summary']['skipped']++;
                    continue;
                }
                
                // Execute test based on method
                if ($testName === 'units_api') {
                    $request = new \Illuminate\Http\Request(['unit_organisasi' => 'SSQC']);
                    $response = $controller->{$testConfig['method']}($request);
                } else {
                    $response = $controller->{$testConfig['method']}();
                }
                
                $data = json_decode($response->getContent(), true);
                $success = $response->getStatusCode() === 200 && ($data['success'] ?? false);
                
                $results['tests'][$testName] = [
                    'status' => $success ? 'PASS' : 'FAIL',
                    'endpoint' => $testConfig['endpoint'],
                    'response_code' => $response->getStatusCode(),
                    'api_success' => $data['success'] ?? false
                ];
                
                if ($testName === 'employee_history') {
                    $results['tests'][$testName]['data_count'] = count($data['history'] ?? []);
                    $results['tests'][$testName]['period'] = $data['period'] ?? null;
                }
                
                if ($success) {
                    $results['summary']['passed']++;
                } else {
                    $results['summary']['failed']++;
                }
                
            } catch (\Exception $e) {
                $results['tests'][$testName] = [
                    'status' => 'FAIL',
                    'endpoint' => $testConfig['endpoint'],
                    'error' => $e->getMessage()
                ];
                $results['summary']['failed']++;
            }
        }
        
        $results['summary']['success_rate'] = $results['summary']['total_tests'] > 0 
            ? round(($results['summary']['passed'] / $results['summary']['total_tests']) * 100, 2) . '%'
            : '0%';
        
        $results['critical_status'] = [
            'employee_history' => $results['tests']['employee_history']['status'] ?? 'UNKNOWN',
            'cascading_dropdown' => $results['tests']['units_api']['status'] ?? 'UNKNOWN'
        ];
        
        return response()->json($results);
    })->name('api.test.all');
    
    // Quick employee data check
    Route::get('/recent-employees', function () {
        try {
            if (!class_exists('App\Models\Employee')) {
                return response()->json([
                    'success' => false,
                    'message' => 'Employee model not available'
                ], 500);
            }
            
            $totalEmployees = \App\Models\Employee::count();
            $recentEmployees = \App\Models\Employee::where('created_at', '>=', \Carbon\Carbon::now()->subDays(30))->count();
            $todayEmployees = \App\Models\Employee::whereDate('created_at', \Carbon\Carbon::today())->count();
            
            $latestEmployee = \App\Models\Employee::with(['unit', 'subUnit'])
                ->latest('created_at')
                ->first();
            
            return response()->json([
                'success' => true,
                'statistics' => [
                    'total_employees' => $totalEmployees,
                    'recent_30_days' => $recentEmployees,
                    'added_today' => $todayEmployees,
                    'has_data' => $totalEmployees > 0
                ],
                'latest_employee' => $latestEmployee ? [
                    'id' => $latestEmployee->id,
                    'nama_lengkap' => $latestEmployee->nama_lengkap,
                    'unit_organisasi' => $latestEmployee->unit_organisasi,
                    'organizational_structure' => $latestEmployee->organizational_structure ?? null,
                    'created_at' => $latestEmployee->created_at,
                    'days_ago' => $latestEmployee->created_at->diffInDays(\Carbon\Carbon::now())
                ] : null,
                'message' => 'Recent employees data retrieved successfully',
                'timestamp' => now()
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error getting recent employees data: ' . $e->getMessage()
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
| - GET /api/test/cascading-dropdown -> Dropdown functionality test
| - GET /api/test/all-apis -> Comprehensive API test suite
| - GET /api/test/recent-employees -> Employee data verification
|
| SYSTEM ENDPOINTS:
| - GET /api/health -> Enhanced system health with feature detection
|
| FEATURES v1.8.0:
| ✅ Enhanced error handling dengan consistent JSON responses
| ✅ Comprehensive testing suite untuk debugging
| ✅ Better health checks dengan detailed feature detection
| ✅ Simplified route structure untuk maintainability
| ✅ Enhanced debugging information untuk development
| ✅ Compatible dengan DashboardController dan Employee model yang diperbaiki
| ✅ NO middleware usage sesuai requirement
|
*/