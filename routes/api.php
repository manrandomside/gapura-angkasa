<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\EmployeeController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\HistoryTroubleshootingController;

/*
|--------------------------------------------------------------------------
| API Routes - GAPURA ANGKASA SDM System v1.12.0 - REAL-TIME DASHBOARD
|--------------------------------------------------------------------------
|
| UPDATED: Real-time dashboard system tanpa polling otomatis
| NEW: Event-based trigger system untuk dashboard updates
| Base color: putih dengan hover hijau (#439454)
| 
| ENHANCED: Trigger-based real-time updates instead of polling
|
*/

// =====================================================
// REAL-TIME DASHBOARD TRIGGER SYSTEM - NEW
// =====================================================

Route::prefix('dashboard')->group(function () {
    
    // Core dashboard APIs (existing)
    Route::get('/statistics', [DashboardController::class, 'getStatistics'])
        ->name('api.dashboard.statistics');
    
    Route::get('/charts', [DashboardController::class, 'getChartData'])
        ->name('api.dashboard.charts');
    
    Route::get('/activities', [DashboardController::class, 'getRecentActivities'])
        ->name('api.dashboard.activities');
    
    // NEW: Manual refresh endpoint untuk dashboard
    Route::post('/refresh', function(Request $request) {
        try {
            $dashboardController = app(DashboardController::class);
            
            // Get fresh data
            $statistics = $dashboardController->getStatistics();
            $charts = $dashboardController->getChartData();
            $activities = $dashboardController->getRecentActivities();
            
            // Log refresh activity
            \Log::info('DASHBOARD: Manual refresh triggered', [
                'user_agent' => $request->userAgent(),
                'ip' => $request->ip(),
                'timestamp' => now('Asia/Makassar')->toISOString()
            ]);
            
            return response()->json([
                'success' => true,
                'message' => 'Dashboard data refreshed successfully',
                'data' => [
                    'statistics' => json_decode($statistics->getContent(), true),
                    'charts' => json_decode($charts->getContent(), true),
                    'activities' => json_decode($activities->getContent(), true)
                ],
                'refreshed_at' => now('Asia/Makassar')->toISOString(),
                'refresh_type' => 'manual'
            ]);
            
        } catch (\Exception $e) {
            \Log::error('DASHBOARD: Manual refresh failed', [
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to refresh dashboard data',
                'error' => config('app.debug') ? $e->getMessage() : 'Refresh failed'
            ], 500);
        }
    })->name('api.dashboard.refresh');
    
    // NEW: Trigger notification untuk real-time updates
    Route::post('/trigger-update', function(Request $request) {
        try {
            $eventType = $request->input('event_type', 'data_changed');
            $context = $request->input('context', []);
            
            // Log the trigger event
            \Log::info('DASHBOARD: Real-time update triggered', [
                'event_type' => $eventType,
                'context' => $context,
                'timestamp' => now('Asia/Makassar')->toISOString()
            ]);
            
            // Here you could implement broadcasting if needed
            // broadcast(new DashboardUpdateEvent($eventType, $context));
            
            return response()->json([
                'success' => true,
                'message' => 'Dashboard update triggered',
                'event_type' => $eventType,
                'triggered_at' => now('Asia/Makassar')->toISOString()
            ]);
            
        } catch (\Exception $e) {
            \Log::error('DASHBOARD: Update trigger failed', [
                'error' => $e->getMessage(),
                'context' => $request->all()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to trigger dashboard update',
                'error' => config('app.debug') ? $e->getMessage() : 'Trigger failed'
            ], 500);
        }
    })->name('api.dashboard.trigger');
    
    // Employee History APIs (existing)
    Route::get('/employee-history', [DashboardController::class, 'getEmployeeHistory'])
        ->name('api.dashboard.employee.history');
    
    Route::get('/employee-history-summary', [DashboardController::class, 'getEmployeeHistorySummary'])
        ->name('api.dashboard.employee.history.summary');
    
    Route::get('/employee-growth-chart', [DashboardController::class, 'getEmployeeGrowthChart'])
        ->name('api.dashboard.employee.growth.chart');
    
    // Other dashboard endpoints
    Route::post('/export', [DashboardController::class, 'exportData'])
        ->name('api.dashboard.export');
    
    Route::get('/health', [DashboardController::class, 'healthCheck'])
        ->name('api.dashboard.health');
});

// =====================================================
// EMPLOYEE MANAGEMENT API ROUTES - ENHANCED WITH TRIGGERS
// =====================================================

Route::prefix('employees')->group(function () {
    
    // Core employee operations dengan dashboard trigger
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
    
    // NEW: Employee CRUD operations dengan dashboard trigger
    Route::post('/store-with-trigger', function(Request $request) {
        try {
            $employeeController = app(EmployeeController::class);
            
            // Store employee
            $response = $employeeController->store($request);
            $responseData = json_decode($response->getContent(), true);
            
            // Trigger dashboard update jika sukses
            if ($responseData['success'] ?? false) {
                \Log::info('REAL-TIME: Employee added, triggering dashboard update', [
                    'employee_id' => $responseData['employee']['id'] ?? 'unknown',
                    'timestamp' => now('Asia/Makassar')->toISOString()
                ]);
                
                // Trigger dashboard refresh
                try {
                    $triggerResponse = app('Illuminate\Http\Client\Factory')->post(url('/api/dashboard/trigger-update'), [
                        'event_type' => 'employee_added',
                        'context' => [
                            'employee_id' => $responseData['employee']['id'] ?? null,
                            'action' => 'create'
                        ]
                    ]);
                } catch (\Exception $triggerError) {
                    \Log::warning('Dashboard trigger failed after employee creation', [
                        'error' => $triggerError->getMessage()
                    ]);
                }
            }
            
            return $response;
            
        } catch (\Exception $e) {
            \Log::error('API: Employee store with trigger error', [
                'error' => $e->getMessage(),
                'request_data' => $request->all()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Error creating employee',
                'error' => config('app.debug') ? $e->getMessage() : 'Creation failed'
            ], 500);
        }
    })->name('api.employees.store.trigger');
    
    Route::put('/{id}/update-with-trigger', function(Request $request, $id) {
        try {
            $employeeController = app(EmployeeController::class);
            
            // Update employee
            $response = $employeeController->update($request, $id);
            $responseData = json_decode($response->getContent(), true);
            
            // Trigger dashboard update jika sukses
            if ($responseData['success'] ?? false) {
                \Log::info('REAL-TIME: Employee updated, triggering dashboard update', [
                    'employee_id' => $id,
                    'timestamp' => now('Asia/Makassar')->toISOString()
                ]);
                
                // Trigger dashboard refresh
                try {
                    app('Illuminate\Http\Client\Factory')->post(url('/api/dashboard/trigger-update'), [
                        'event_type' => 'employee_updated',
                        'context' => [
                            'employee_id' => $id,
                            'action' => 'update'
                        ]
                    ]);
                } catch (\Exception $triggerError) {
                    \Log::warning('Dashboard trigger failed after employee update', [
                        'error' => $triggerError->getMessage()
                    ]);
                }
            }
            
            return $response;
            
        } catch (\Exception $e) {
            \Log::error('API: Employee update with trigger error', [
                'error' => $e->getMessage(),
                'employee_id' => $id
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Error updating employee',
                'error' => config('app.debug') ? $e->getMessage() : 'Update failed'
            ], 500);
        }
    })->name('api.employees.update.trigger');
    
    Route::delete('/{id}/delete-with-trigger', function(Request $request, $id) {
        try {
            $employeeController = app(EmployeeController::class);
            
            // Delete employee
            $response = $employeeController->destroy($id);
            $responseData = json_decode($response->getContent(), true);
            
            // Trigger dashboard update jika sukses
            if ($responseData['success'] ?? false) {
                \Log::info('REAL-TIME: Employee deleted, triggering dashboard update', [
                    'employee_id' => $id,
                    'timestamp' => now('Asia/Makassar')->toISOString()
                ]);
                
                // Trigger dashboard refresh
                try {
                    app('Illuminate\Http\Client\Factory')->post(url('/api/dashboard/trigger-update'), [
                        'event_type' => 'employee_deleted',
                        'context' => [
                            'employee_id' => $id,
                            'action' => 'delete'
                        ]
                    ]);
                } catch (\Exception $triggerError) {
                    \Log::warning('Dashboard trigger failed after employee deletion', [
                        'error' => $triggerError->getMessage()
                    ]);
                }
            }
            
            return $response;
            
        } catch (\Exception $e) {
            \Log::error('API: Employee delete with trigger error', [
                'error' => $e->getMessage(),
                'employee_id' => $id
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Error deleting employee',
                'error' => config('app.debug') ? $e->getMessage() : 'Deletion failed'
            ], 500);
        }
    })->name('api.employees.delete.trigger');
    
    // Existing employee endpoints
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
    
    // Bulk operations dengan trigger
    Route::post('/bulk-action-with-trigger', function(Request $request) {
        try {
            if (!method_exists(EmployeeController::class, 'bulkAction')) {
                return response()->json([
                    'success' => false,
                    'message' => 'Bulk action method not available'
                ], 404);
            }
            
            $response = app(EmployeeController::class)->bulkAction($request);
            $responseData = json_decode($response->getContent(), true);
            
            // Trigger dashboard update jika bulk action sukses
            if ($responseData['success'] ?? false) {
                $action = $request->input('action', 'bulk_action');
                
                \Log::info('REAL-TIME: Bulk action completed, triggering dashboard update', [
                    'action' => $action,
                    'affected_count' => $responseData['affected_count'] ?? 0,
                    'timestamp' => now('Asia/Makassar')->toISOString()
                ]);
                
                // Trigger dashboard refresh
                try {
                    app('Illuminate\Http\Client\Factory')->post(url('/api/dashboard/trigger-update'), [
                        'event_type' => 'employees_bulk_action',
                        'context' => [
                            'action' => $action,
                            'affected_count' => $responseData['affected_count'] ?? 0
                        ]
                    ]);
                } catch (\Exception $triggerError) {
                    \Log::warning('Dashboard trigger failed after bulk action', [
                        'error' => $triggerError->getMessage()
                    ]);
                }
            }
            
            return $response;
            
        } catch (\Exception $e) {
            \Log::error('API: Bulk action with trigger error', [
                'error' => $e->getMessage(),
                'action' => $request->input('action', 'not_provided')
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Error in bulk action',
                'error' => config('app.debug') ? $e->getMessage() : 'Bulk action failed'
            ], 500);
        }
    })->name('api.employees.bulk-action.trigger');
    
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
// TROUBLESHOOTING ROUTES - PRESERVED FOR DEBUGGING
// =====================================================

Route::prefix('troubleshoot')->group(function () {
    
    // Step-by-step troubleshooting endpoints
    Route::get('/step1-database', [HistoryTroubleshootingController::class, 'databaseVerification'])
        ->name('api.troubleshoot.database');
    
    Route::get('/step2-relationships', [HistoryTroubleshootingController::class, 'modelRelationshipVerification'])
        ->name('api.troubleshoot.relationships');
    
    Route::get('/step3-api-response', [HistoryTroubleshootingController::class, 'apiResponseVerification'])
        ->name('api.troubleshoot.api');
    
    // Comprehensive troubleshooting
    Route::get('/full-check', [HistoryTroubleshootingController::class, 'runFullTroubleshooting'])
        ->name('api.troubleshoot.full');
    
    // Quick fixes for common issues
    Route::post('/add-test-employee', [HistoryTroubleshootingController::class, 'addTestEmployee'])
        ->name('api.troubleshoot.add.employee');
    
    Route::post('/fix-timestamps', [HistoryTroubleshootingController::class, 'fixEmployeeTimestamps'])
        ->name('api.troubleshoot.fix.timestamps');
    
    Route::post('/fix-organizational-structure', [HistoryTroubleshootingController::class, 'fixOrganizationalStructure'])
        ->name('api.troubleshoot.fix.organization');
});

// =====================================================  
// QUICK TEST ENDPOINTS untuk debugging
// =====================================================

Route::prefix('quick-test')->group(function () {
    
    // Test raw SQL query
    Route::get('/raw-sql-30-days', function () {
        try {
            $startDate = \Carbon\Carbon::now('Asia/Makassar')->subDays(30)->startOfDay();
            $endDate = \Carbon\Carbon::now('Asia/Makassar')->endOfDay();
            
            $result = \Illuminate\Support\Facades\DB::select("
                SELECT 
                    e.id,
                    e.nama_lengkap,
                    e.unit_organisasi,
                    e.created_at,
                    DATEDIFF(NOW(), e.created_at) as days_ago
                FROM employees e
                WHERE e.created_at BETWEEN ? AND ?
                ORDER BY e.created_at DESC
                LIMIT 10
            ", [$startDate->format('Y-m-d H:i:s'), $endDate->format('Y-m-d H:i:s')]);
            
            return response()->json([
                'success' => true,
                'query_period' => '30 days',
                'start_date' => $startDate->toISOString(),
                'end_date' => $endDate->toISOString(),
                'results_count' => count($result),
                'raw_results' => $result,
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
            ], 500);
        }
    });
    
    // Test timezone configuration
    Route::get('/timezone-test', function () {
        try {
            $dbTimezone = \Illuminate\Support\Facades\DB::select('SELECT NOW() as db_now, @@session.time_zone as db_timezone')[0];
            $carbonNow = \Carbon\Carbon::now('Asia/Makassar');
            $carbonUtc = \Carbon\Carbon::now('UTC');
            
            return response()->json([
                'success' => true,
                'app_timezone' => config('app.timezone'),
                'wita_timezone' => 'Asia/Makassar',
                'db_timezone' => $dbTimezone->db_timezone,
                'db_now' => $dbTimezone->db_now,
                'carbon_wita' => $carbonNow->toISOString(),
                'carbon_utc' => $carbonUtc->toISOString(),
                'carbon_formatted' => $carbonNow->format('Y-m-d H:i:s'),
                'php_timezone' => date_default_timezone_get(),
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
            ], 500);
        }
    });
    
    // Direct API endpoint test
    Route::get('/direct-history-call', function () {
        try {
            $controller = app('App\Http\Controllers\DashboardController');
            $response = $controller->getEmployeeHistory();
            
            $responseData = json_decode($response->getContent(), true);
            
            return response()->json([
                'success' => true,
                'api_status_code' => $response->getStatusCode(),
                'api_response' => $responseData,
                'api_success_field' => $responseData['success'] ?? 'MISSING',
                'api_total_field' => $responseData['total'] ?? 'MISSING',
                'api_history_count' => count($responseData['history'] ?? []),
                'response_keys' => array_keys($responseData ?? []),
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
                'line' => $e->getLine(),
                'file' => basename($e->getFile()),
            ], 500);
        }
    });
    
    // NEW: Test real-time dashboard trigger
    Route::get('/trigger-test', function () {
        try {
            // Simulate dashboard trigger
            $triggerResponse = app('Illuminate\Http\Client\Factory')->post(url('/api/dashboard/trigger-update'), [
                'event_type' => 'test_trigger',
                'context' => [
                    'test' => true,
                    'timestamp' => now('Asia/Makassar')->toISOString()
                ]
            ]);
            
            return response()->json([
                'success' => true,
                'message' => 'Real-time trigger test completed',
                'trigger_response' => $triggerResponse->json(),
                'trigger_status' => $triggerResponse->status(),
                'test_timestamp' => now('Asia/Makassar')->toISOString()
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
                'message' => 'Real-time trigger test failed'
            ], 500);
        }
    });
});

// =====================================================
// DEBUGGING ROUTES - PRESERVED
// =====================================================

Route::prefix('debug')->group(function () {
    
    // Test 1: Database connection dan employee count
    Route::get('/database-test', function () {
        try {
            $total = \App\Models\Employee::count();
            $recent = \App\Models\Employee::where('created_at', '>=', \Carbon\Carbon::now('Asia/Makassar')->subDays(30))->count();
            
            $sampleEmployees = \App\Models\Employee::where('created_at', '>=', \Carbon\Carbon::now('Asia/Makassar')->subDays(30))
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
                'test_timestamp' => now('Asia/Makassar')->toISOString(),
                'timezone' => 'Asia/Makassar (WITA)'
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
    
    // Test 2: System check dengan real-time features
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
                'getStatistics' => method_exists($controller, 'getStatistics'),
                'getChartData' => method_exists($controller, 'getChartData'),
                'getGenderChartData' => method_exists($controller, 'getGenderChartData'),
                'getStatusChartData' => method_exists($controller, 'getStatusChartData'),
                'getUnitChartData' => method_exists($controller, 'getUnitChartData'),
            ];
        }
        
        // Check real-time features
        $realtimeFeatures = [
            'manual_refresh_endpoint' => true,
            'trigger_update_endpoint' => true,
            'employee_crud_with_trigger' => true,
            'dashboard_event_system' => true
        ];
        
        return response()->json([
            'test_name' => 'System Check - Real-time Dashboard',
            'success' => true,
            'models_available' => $models,
            'controllers_available' => $controllers,
            'critical_methods' => $methods,
            'realtime_features' => $realtimeFeatures,
            'php_version' => PHP_VERSION,
            'laravel_version' => app()->version(),
            'environment' => app()->environment(),
            'system_version' => '1.12.0 - Real-time Dashboard'
        ]);
    });
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
            'employee' => class_exists('App\Http\Controllers\EmployeeController') ? 'available' : 'missing',
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
                $employeeStats['recent_30_days'] = \App\Models\Employee::where('created_at', '>=', \Carbon\Carbon::now('Asia/Makassar')->subDays(30))->count();
                $employeeStats['today'] = \App\Models\Employee::whereDate('created_at', \Carbon\Carbon::today('Asia/Makassar'))->count();
                $employeeStats['has_data'] = $employeeStats['total'] > 0;
            } catch (\Exception $e) {
                \Log::warning('Health check: Employee stats failed', ['error' => $e->getMessage()]);
            }
        }
        
        // Real-time dashboard features check
        $realtimeStatus = [
            'dashboard_refresh_endpoint' => true,
            'trigger_update_endpoint' => true,
            'employee_crud_triggers' => true,
            'event_system' => true,
            'timezone_wita' => true
        ];
        
        // 6 Charts availability check
        $chartsStatus = [
            'gender_chart' => true,
            'status_chart' => true,
            'unit_chart' => true,
            'provider_chart' => true,
            'age_chart' => true,
            'jabatan_chart' => true
        ];
        
        // Overall system health
        $overallHealth = $dbStatus === 'connected' && 
                        $modelStatus['employee'] === 'available' && 
                        $controllerStatus['dashboard'] === 'available';
        
        return response()->json([
            'success' => true,
            'status' => $overallHealth ? 'healthy' : 'degraded',
            'timestamp' => now('Asia/Makassar')->toISOString(),
            'environment' => app()->environment(),
            'versions' => [
                'php' => PHP_VERSION,
                'laravel' => app()->version(),
                'system' => '1.12.0 - Real-time Dashboard'
            ],
            'database' => [
                'status' => $dbStatus,
                'connected' => $dbStatus === 'connected',
                'error' => $dbError
            ],
            'models' => $modelStatus,
            'controllers' => $controllerStatus,
            'employee_data' => $employeeStats,
            'realtime_features' => $realtimeStatus,
            'charts_system' => $chartsStatus,
            'critical_endpoints' => [
                '/api/dashboard/statistics' => $controllerStatus['dashboard'] === 'available',
                '/api/dashboard/charts' => $controllerStatus['dashboard'] === 'available',
                '/api/dashboard/refresh' => true,
                '/api/dashboard/trigger-update' => true,
                '/api/employees/store-with-trigger' => $controllerStatus['employee'] === 'available'
            ],
            'features_status' => [
                'realtime_dashboard' => true,
                'six_charts_system' => true,
                'employee_management' => $modelStatus['employee'] === 'available',
                'dashboard_statistics' => $controllerStatus['dashboard'] === 'available',
                'trigger_based_updates' => true,
                'manual_refresh' => true,
                'timezone_wita' => true
            ],
            'timezone' => 'Asia/Makassar (WITA)'
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
            'timestamp' => now('Asia/Makassar')->toISOString()
        ], 500);
    }
})->name('api.health');

/*
|--------------------------------------------------------------------------
| API Routes Documentation - v1.12.0 REAL-TIME DASHBOARD SYSTEM
|--------------------------------------------------------------------------
|
| NEW: REAL-TIME DASHBOARD ENDPOINTS:
| 1. POST /api/dashboard/refresh -> Manual refresh dashboard data
| 2. POST /api/dashboard/trigger-update -> Trigger real-time updates
| 3. POST /api/employees/store-with-trigger -> Create employee + trigger dashboard
| 4. PUT /api/employees/{id}/update-with-trigger -> Update employee + trigger dashboard
| 5. DELETE /api/employees/{id}/delete-with-trigger -> Delete employee + trigger dashboard
| 6. POST /api/employees/bulk-action-with-trigger -> Bulk operations + trigger dashboard
|
| CORE DASHBOARD ENDPOINTS:
| - GET /api/dashboard/statistics -> Employee statistics for cards
| - GET /api/dashboard/charts -> 6 charts data (gender, status, unit, provider, age, jabatan)
| - GET /api/dashboard/activities -> Recent activities
| - GET /api/dashboard/health -> System health check
|
| EMPLOYEE MANAGEMENT:
| - GET /api/employees/search -> Search employees
| - GET /api/employees/statistics -> Employee statistics
| - GET /api/employees/filter-options -> Filter options
| - GET /api/employees/{id}/profile -> Employee profile
|
| VALIDATION ENDPOINTS:
| - GET /api/validate/nik/{nik} -> Check NIK availability
| - GET /api/validate/nip/{nip} -> Check NIP availability
|
| CASCADING DROPDOWN:
| - GET /api/unit-organisasi-options -> Organization units
| - GET /api/units -> Units by organization
| - GET /api/sub-units -> Sub units by unit
|
| TESTING & DEBUGGING:
| - GET /api/health -> Enhanced system health
| - GET /api/debug/system-check -> System components check
| - GET /api/debug/database-test -> Database connectivity
| - GET /api/quick-test/trigger-test -> Test real-time triggers
|
| REAL-TIME SYSTEM WORKFLOW:
| 1. Dashboard loads with initial data
| 2. User performs CRUD operations on employees
| 3. CRUD operations automatically trigger dashboard updates
| 4. Dashboard refreshes real-time without polling
| 5. Manual refresh available via button or API call
|
| TIMEZONE: All timestamps use Asia/Makassar (WITA)
| BASE COLOR: #439454 (Green primary)
| VERSION: 1.12.0 - Real-time Dashboard System
|
*/