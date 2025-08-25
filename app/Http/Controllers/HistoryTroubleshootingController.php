<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class HistoryTroubleshootingController extends Controller
{
    /**
     * STEP 1: Database Level Verification - SAFE VERSION
     */
    public function databaseVerification()
    {
        try {
            // Basic database connection test
            $dbConnection = 'unknown';
            try {
                DB::connection()->getPdo();
                $dbConnection = 'connected';
            } catch (\Exception $e) {
                $dbConnection = 'disconnected: ' . $e->getMessage();
            }

            // Employee count verification
            $totalEmployees = 0;
            $totalWithCreatedAt = 0;
            try {
                $totalEmployees = Employee::count();
                $totalWithCreatedAt = Employee::whereNotNull('created_at')->count();
            } catch (\Exception $e) {
                Log::error('Employee count failed: ' . $e->getMessage());
            }

            // Date range for 30 days
            $carbonNow = Carbon::now();
            $startDate = Carbon::now()->subDays(30)->startOfDay();
            $endDate = Carbon::now()->endOfDay();

            // Recent 30 days count
            $recent30Days = 0;
            try {
                $recent30Days = Employee::whereBetween('created_at', [$startDate, $endDate])->count();
            } catch (\Exception $e) {
                Log::error('Recent 30 days query failed: ' . $e->getMessage());
            }

            // Sample recent employees
            $recentSamples = [];
            try {
                $samples = Employee::select('id', 'nama_lengkap', 'created_at', 'unit_organisasi')
                    ->whereNotNull('created_at')
                    ->orderBy('created_at', 'desc')
                    ->take(5)
                    ->get();

                $recentSamples = $samples->map(function ($emp) use ($carbonNow) {
                    return [
                        'id' => $emp->id,
                        'name' => $emp->nama_lengkap,
                        'created_at_formatted' => $emp->created_at ? $emp->created_at->format('Y-m-d H:i:s') : null,
                        'days_ago' => $emp->created_at ? $emp->created_at->diffInDays($carbonNow) : null,
                        'unit_organisasi' => $emp->unit_organisasi,
                    ];
                })->toArray();
            } catch (\Exception $e) {
                Log::error('Sample employees failed: ' . $e->getMessage());
                $recentSamples = [];
            }

            $result = [
                'success' => true,
                'test_name' => 'Database Level Verification',
                'database' => [
                    'connection' => $dbConnection,
                    'app_timezone' => config('app.timezone', 'UTC'),
                    'carbon_now' => $carbonNow->toISOString(),
                ],
                'employee_counts' => [
                    'total_employees' => $totalEmployees,
                    'with_created_at' => $totalWithCreatedAt,
                    'missing_created_at' => max(0, $totalEmployees - $totalWithCreatedAt),
                ],
                'date_filtering' => [
                    'query_start_date' => $startDate->toISOString(),
                    'query_end_date' => $endDate->toISOString(),
                    'recent_30_days' => $recent30Days,
                ],
                'recent_samples' => $recentSamples,
                'timestamp' => $carbonNow->toISOString(),
            ];

            return response()->json($result);

        } catch (\Exception $e) {
            Log::error('Database verification failed: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'test_name' => 'Database Level Verification',
                'error' => $e->getMessage(),
                'line' => $e->getLine(),
                'file' => basename($e->getFile()),
            ], 500);
        }
    }

    /**
     * STEP 2: Model Relationship Verification - SAFE VERSION
     */
    public function modelRelationshipVerification()
    {
        try {
            // Test basic Employee model
            $modelTests = [
                'Employee' => class_exists('App\Models\Employee'),
                'Unit' => class_exists('App\Models\Unit'),
                'SubUnit' => class_exists('App\Models\SubUnit'),
            ];

            // Sample employees untuk testing
            $sampleTests = [];
            try {
                $employees = Employee::take(3)->get();
                foreach ($employees as $emp) {
                    $test = [
                        'employee_id' => $emp->id,
                        'employee_name' => $emp->nama_lengkap,
                        'unit_organisasi' => $emp->unit_organisasi,
                        'unit_id' => $emp->unit_id,
                        'sub_unit_id' => $emp->sub_unit_id,
                    ];

                    // Test relationships if available
                    try {
                        if (method_exists($emp, 'unit') && $emp->unit_id) {
                            $unit = $emp->unit;
                            $test['unit_relationship'] = $unit ? 'loaded' : 'null';
                            $test['unit_name'] = $unit ? $unit->name : null;
                        } else {
                            $test['unit_relationship'] = 'method_not_available';
                        }
                    } catch (\Exception $e) {
                        $test['unit_relationship'] = 'error: ' . $e->getMessage();
                    }

                    try {
                        if (method_exists($emp, 'subUnit') && $emp->sub_unit_id) {
                            $subUnit = $emp->subUnit;
                            $test['sub_unit_relationship'] = $subUnit ? 'loaded' : 'null';
                            $test['sub_unit_name'] = $subUnit ? $subUnit->name : null;
                        } else {
                            $test['sub_unit_relationship'] = 'method_not_available_or_null_id';
                        }
                    } catch (\Exception $e) {
                        $test['sub_unit_relationship'] = 'error: ' . $e->getMessage();
                    }

                    $sampleTests[] = $test;
                }
            } catch (\Exception $e) {
                Log::error('Sample employee tests failed: ' . $e->getMessage());
                $sampleTests = ['error' => $e->getMessage()];
            }

            $result = [
                'success' => true,
                'test_name' => 'Model Relationship Verification',
                'model_availability' => $modelTests,
                'sample_relationship_tests' => $sampleTests,
                'timestamp' => Carbon::now()->toISOString(),
            ];

            return response()->json($result);

        } catch (\Exception $e) {
            Log::error('Model relationship verification failed: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'test_name' => 'Model Relationship Verification',
                'error' => $e->getMessage(),
                'line' => $e->getLine(),
                'file' => basename($e->getFile()),
            ], 500);
        }
    }

    /**
     * STEP 3: API Response Structure Verification - SAFE VERSION
     */
    public function apiResponseVerification()
    {
        try {
            // Test DashboardController availability
            $controllerExists = class_exists('App\Http\Controllers\DashboardController');
            
            if (!$controllerExists) {
                return response()->json([
                    'success' => false,
                    'test_name' => 'API Response Structure Verification',
                    'error' => 'DashboardController class not found',
                    'controller_available' => false,
                ]);
            }

            $controller = app('App\Http\Controllers\DashboardController');
            $methodExists = method_exists($controller, 'getEmployeeHistory');

            if (!$methodExists) {
                return response()->json([
                    'success' => false,
                    'test_name' => 'API Response Structure Verification',
                    'error' => 'getEmployeeHistory method not found in DashboardController',
                    'controller_available' => true,
                    'method_available' => false,
                    'available_methods' => array_slice(get_class_methods($controller), 0, 10),
                ]);
            }

            // Try to call the method
            try {
                $response = $controller->getEmployeeHistory();
                $statusCode = $response->getStatusCode();
                $responseData = json_decode($response->getContent(), true);

                // Validate response structure
                $expectedFields = ['success', 'history', 'total', 'period'];
                $actualFields = array_keys($responseData ?? []);
                $missingFields = array_diff($expectedFields, $actualFields);

                $result = [
                    'success' => true,
                    'test_name' => 'API Response Structure Verification',
                    'controller_test' => [
                        'class_exists' => true,
                        'method_exists' => true,
                        'method_callable' => true,
                    ],
                    'response_test' => [
                        'status_code' => $statusCode,
                        'is_successful' => $statusCode === 200,
                        'response_size' => strlen($response->getContent()),
                    ],
                    'structure_validation' => [
                        'has_success_field' => isset($responseData['success']),
                        'success_value' => $responseData['success'] ?? 'MISSING',
                        'expected_fields' => $expectedFields,
                        'actual_fields' => $actualFields,
                        'missing_fields' => $missingFields,
                        'structure_valid' => empty($missingFields),
                    ],
                    'content_validation' => [
                        'total_records' => $responseData['total'] ?? 0,
                        'history_count' => count($responseData['history'] ?? []),
                        'period' => $responseData['period'] ?? 'MISSING',
                    ],
                    'timestamp' => Carbon::now()->toISOString(),
                ];

                return response()->json($result);

            } catch (\Exception $e) {
                return response()->json([
                    'success' => false,
                    'test_name' => 'API Response Structure Verification',
                    'error' => 'Method call failed: ' . $e->getMessage(),
                    'controller_available' => true,
                    'method_available' => true,
                    'method_call_failed' => true,
                    'line' => $e->getLine(),
                    'file' => basename($e->getFile()),
                ], 500);
            }

        } catch (\Exception $e) {
            Log::error('API response verification failed: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'test_name' => 'API Response Structure Verification',
                'error' => $e->getMessage(),
                'line' => $e->getLine(),
                'file' => basename($e->getFile()),
            ], 500);
        }
    }

    /**
     * COMPREHENSIVE: Run all troubleshooting steps - SAFE VERSION
     */
    public function runFullTroubleshooting()
    {
        try {
            $results = [
                'success' => true,
                'test_name' => 'Comprehensive History Modal Troubleshooting',
                'timestamp' => Carbon::now()->toISOString(),
            ];

            // Step 1: Database
            try {
                $step1Response = $this->databaseVerification();
                $results['step_1_database'] = json_decode($step1Response->getContent(), true);
            } catch (\Exception $e) {
                $results['step_1_database'] = ['error' => $e->getMessage()];
            }

            // Step 2: Relationships  
            try {
                $step2Response = $this->modelRelationshipVerification();
                $results['step_2_relationships'] = json_decode($step2Response->getContent(), true);
            } catch (\Exception $e) {
                $results['step_2_relationships'] = ['error' => $e->getMessage()];
            }

            // Step 3: API Response
            try {
                $step3Response = $this->apiResponseVerification();
                $results['step_3_api_response'] = json_decode($step3Response->getContent(), true);
            } catch (\Exception $e) {
                $results['step_3_api_response'] = ['error' => $e->getMessage()];
            }

            // Generate recommendation
            $issues = [];
            $actions = [];

            // Check for common issues
            $totalEmployees = $results['step_1_database']['employee_counts']['total_employees'] ?? 0;
            $recent30Days = $results['step_1_database']['date_filtering']['recent_30_days'] ?? 0;
            $apiWorking = $results['step_3_api_response']['success'] ?? false;

            if ($totalEmployees === 0) {
                $issues[] = 'Tidak ada data karyawan di database';
                $actions[] = 'Jalankan seeder: php artisan db:seed --class=SDMEmployeeSeeder';
            }

            if ($recent30Days === 0) {
                $issues[] = 'Tidak ada karyawan baru dalam 30 hari terakhir';
                $actions[] = 'Update timestamps atau tambah karyawan test';
            }

            if (!$apiWorking) {
                $issues[] = 'History API tidak berfungsi dengan benar';
                $actions[] = 'Periksa DashboardController getEmployeeHistory method';
            }

            $results['recommendation'] = [
                'status' => empty($issues) ? 'HEALTHY' : 'NEEDS_ATTENTION',
                'issues_found' => $issues,
                'recommended_actions' => $actions,
            ];

            return response()->json($results);

        } catch (\Exception $e) {
            Log::error('Full troubleshooting failed: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'test_name' => 'Comprehensive History Modal Troubleshooting',
                'error' => $e->getMessage(),
                'line' => $e->getLine(),
                'file' => basename($e->getFile()),
            ], 500);
        }
    }

    /**
     * Quick fix: Add test employee
     */
    public function addTestEmployee(Request $request)
    {
        try {
            $uniqueId = Carbon::now()->format('YmdHis');
            
            $testEmployee = Employee::create([
                'nik' => 'TEST' . $uniqueId,
                'nip' => 'TESTNIP' . $uniqueId,
                'nama_lengkap' => 'Test Employee ' . $uniqueId,
                'unit_organisasi' => 'Landside',
                'jabatan' => 'Staff Test',
                'status_pegawai' => 'PEGAWAI TETAP',
                'lokasi_kerja' => 'Test Location',
                'cabang' => 'DPS',
                'status_kerja' => 'AKTIF',
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Test employee added successfully',
                'employee_id' => $testEmployee->id,
                'employee_data' => [
                    'id' => $testEmployee->id,
                    'nik' => $testEmployee->nik,
                    'nama_lengkap' => $testEmployee->nama_lengkap,
                    'created_at' => $testEmployee->created_at->toISOString(),
                ],
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Quick fix: Fix employee timestamps
     */
    public function fixEmployeeTimestamps(Request $request)
    {
        try {
            $count = $request->get('count', 5);
            $daysAgo = $request->get('days_ago', 1);

            $employees = Employee::inRandomOrder()->take($count)->get();

            if ($employees->isEmpty()) {
                return response()->json([
                    'success' => false,
                    'error' => 'No employees found to update',
                ], 404);
            }

            $updatedEmployees = [];
            $targetDate = Carbon::now()->subDays($daysAgo);

            foreach ($employees as $employee) {
                $oldCreatedAt = $employee->created_at;
                $employee->created_at = $targetDate->copy()->addMinutes(rand(1, 1440));
                $employee->save();

                $updatedEmployees[] = [
                    'id' => $employee->id,
                    'name' => $employee->nama_lengkap,
                    'old_created_at' => $oldCreatedAt ? $oldCreatedAt->toISOString() : null,
                    'new_created_at' => $employee->created_at->toISOString(),
                ];
            }

            return response()->json([
                'success' => true,
                'message' => 'Employee timestamps updated successfully',
                'updated_count' => count($updatedEmployees),
                'updated_employees' => $updatedEmployees,
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Quick fix: Fix organizational structure
     */
    public function fixOrganizationalStructure(Request $request)
    {
        try {
            return response()->json([
                'success' => true,
                'message' => 'Organizational structure verification completed',
                'note' => 'This is a placeholder implementation',
                'timestamp' => Carbon::now()->toISOString(),
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}