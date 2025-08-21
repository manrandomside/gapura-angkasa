<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\EmployeeController;
use App\Http\Controllers\DashboardController;

/*
|--------------------------------------------------------------------------
| API Routes - GAPURA ANGKASA SDM System
|--------------------------------------------------------------------------
|
| API routes untuk cascading dropdown dan data management
| Fokus pada parent-child dropdown untuk unit organisasi > unit > sub unit
| Base color: putih dengan hover hijau (#439454)
|
*/

// =====================================================
// UNIT ORGANISASI & CASCADING DROPDOWN API ROUTES
// =====================================================

Route::prefix('units')->group(function () {
    // FIXED: Get units berdasarkan unit organisasi
    Route::get('/', [EmployeeController::class, 'getUnits'])->name('api.units');
    Route::get('/by-organisasi', [EmployeeController::class, 'getUnits'])->name('api.units.by-organisasi');
    
    // Enhanced unit routes untuk debugging dan monitoring
    Route::get('/hierarchy', [EmployeeController::class, 'getAllUnitsHierarchy'])->name('api.units.hierarchy');
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
// DASHBOARD API ROUTES
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
});

// =====================================================
// QUICK VALIDATION ROUTES
// =====================================================

Route::get('/validate/nik/{nik}', function ($nik) {
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

Route::get('/validate/nip/{nip}', function ($nip) {
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
                'routes_loaded' => true
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
        
        // Check total employees
        $totalEmployees = 0;
        try {
            if (class_exists('App\Models\Employee')) {
                $totalEmployees = \App\Models\Employee::count();
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
            'checks' => [
                'database' => $dbStatus,
                'employee_model' => $employeeModelStatus,
                'total_employees' => $totalEmployees,
                'api_routes' => 'loaded'
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
| API Routes Documentation
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
| TESTING ENDPOINTS (Development):
| - GET /api/test/connectivity
| - GET /api/test/units/{unitOrganisasi}
| - GET /api/test/sub-units/{unitId}
| - GET /api/test/cascade/{unitOrganisasi}
|
| VALIDATION ENDPOINTS:
| - GET /api/validate/nik/{nik}
| - GET /api/validate/nip/{nip}
|
| SYSTEM ENDPOINTS:
| - GET /api/health
|
| FORMAT RESPONSE KONSISTEN:
| {
|     "success": true/false,
|     "data": [...],
|     "message": "Description"
| }
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