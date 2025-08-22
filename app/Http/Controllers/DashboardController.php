<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use App\Models\Organization;
use App\Models\Unit;
use App\Models\SubUnit;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;

class DashboardController extends Controller
{
    /**
     * Display dashboard index for GAPURA ANGKASA SDM System
     */
    public function index()
    {
        try {
            $statistics = $this->getStatisticsData();
            $chartData = $this->getChartDataArray();
            $recentActivities = $this->getRecentActivitiesData();
            $employeeHistorySummary = $this->getEmployeeHistorySummaryData();
            
            return Inertia::render('Dashboard/Index', [
                'statistics' => $statistics,
                'chartData' => $chartData,
                'recentActivities' => $recentActivities,
                'employeeHistorySummary' => $employeeHistorySummary,
                'success' => session('success'),
                'info' => session('info'),
            ]);
        } catch (\Exception $e) {
            Log::error('Dashboard Index Error: ' . $e->getMessage());
            
            return Inertia::render('Dashboard/Index', [
                'statistics' => $this->getDefaultStatistics(),
                'chartData' => $this->getDefaultChartData(),
                'recentActivities' => [],
                'employeeHistorySummary' => [],
                'error' => 'Error loading dashboard: ' . $e->getMessage(),
            ]);
        }
    }

    /**
     * Get dashboard statistics (API endpoint)
     * UPDATED: Include TAD Split dan Kelompok Jabatan
     */
    public function getStatistics()
    {
        try {
            $statistics = $this->getStatisticsData();
            return response()->json($statistics);
        } catch (\Exception $e) {
            Log::error('Dashboard Statistics Error: ' . $e->getMessage());
            return response()->json([
                'total_employees' => 0,
                'active_employees' => 0,
                'pegawai_tetap' => 0,
                'pkwt' => 0,
                'tad_total' => 0,
                'tad_paket_sdm' => 0,
                'tad_paket_pekerjaan' => 0,
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get chart data for dashboard
     * UPDATED: Include TAD breakdown dan Kelompok Jabatan
     */
    public function getChartData()
    {
        try {
            $chartData = $this->getChartDataArray();
            return response()->json($chartData);
        } catch (\Exception $e) {
            Log::error('Dashboard Chart Data Error: ' . $e->getMessage());
            return response()->json([
                'error' => $e->getMessage(),
                'by_organization' => [],
                'by_status' => [],
                'by_gender' => [],
                'by_kelompok_jabatan' => [],
                'tad_breakdown' => [],
                'monthly_hires' => [],
                'age_distribution' => [],
            ], 500);
        }
    }

    /**
     * Get recent activities
     */
    public function getRecentActivities()
    {
        try {
            $activities = $this->getRecentActivitiesData();
            return response()->json($activities);
        } catch (\Exception $e) {
            Log::error('Dashboard Recent Activities Error: ' . $e->getMessage());
            return response()->json([
                'activities' => [],
                'total' => 0,
                'error' => $e->getMessage()
            ], 500);
        }
    }

    // =====================================================
    // FIXED: EMPLOYEE HISTORY METHODS untuk History Modal
    // =====================================================

    /**
     * FIXED: Get employee history - Karyawan yang baru ditambahkan (30 hari terakhir)
     * Enhanced dengan proper relationship loading dan organizational structure
     * CRITICAL: Method utama untuk History Modal functionality
     */
    public function getEmployeeHistory()
    {
        try {
            $startDate = Carbon::now()->subDays(30)->startOfDay();
            $endDate = Carbon::now()->endOfDay();

            Log::info('HISTORY API: Fetching employee history', [
                'start_date' => $startDate->format('Y-m-d H:i:s'),
                'end_date' => $endDate->format('Y-m-d H:i:s'),
                'method' => 'getEmployeeHistory'
            ]);

            // FIXED: Enhanced query dengan comprehensive relationship loading
            $employees = Employee::select([
                    'id',
                    'nip',
                    'nik', 
                    'nama_lengkap',
                    'unit_organisasi',
                    'unit_id',
                    'sub_unit_id',
                    'jabatan',
                    'nama_jabatan',
                    'kelompok_jabatan',
                    'status_pegawai',
                    'created_at',
                    'updated_at'
                ])
                ->with([
                    'unit:id,name,code,unit_organisasi',
                    'subUnit:id,name,code,unit_id'
                ])
                ->whereBetween('created_at', [$startDate, $endDate])
                ->orderBy('created_at', 'desc')
                ->get();

            Log::info('HISTORY API: Raw query results', [
                'total_found' => $employees->count(),
                'with_relationships' => true,
                'period_days' => 30
            ]);

            // FIXED: Enhanced data mapping dengan proper organizational structure menggunakan accessor
            $historyData = $employees->map(function ($employee) {
                // FIXED: Gunakan accessor dari Employee model yang sudah diperbaiki
                $organizationalStructure = $employee->organizational_structure;
                
                $data = [
                    'id' => $employee->id,
                    'nip' => $employee->nip ?? 'Tidak tersedia',
                    'nik' => $employee->nik ?? 'Tidak tersedia',
                    'nama_lengkap' => $employee->nama_lengkap ?? 'Nama tidak tersedia',
                    'initials' => $employee->initials ?? $this->getEmployeeInitials($employee->nama_lengkap),
                    'organizational_structure' => $organizationalStructure,
                    'unit_organisasi' => $employee->unit_organisasi ?? 'Tidak tersedia',
                    'unit_name' => $organizationalStructure['unit']['name'] ?? null,
                    'sub_unit_name' => $organizationalStructure['sub_unit']['name'] ?? null,
                    'full_structure' => $organizationalStructure['full_structure'],
                    'jabatan' => $employee->jabatan ?? $employee->nama_jabatan ?? 'Tidak tersedia',
                    'kelompok_jabatan' => $employee->kelompok_jabatan ?? 'Tidak tersedia',
                    'status_pegawai' => $employee->status_pegawai ?? 'Tidak tersedia',
                    'created_at' => $employee->created_at,
                    'formatted_date' => $employee->created_at ? $employee->created_at->format('d/m/Y H:i') : null,
                    'relative_date' => $employee->created_at ? $employee->created_at->diffForHumans() : null,
                    'days_ago' => $employee->created_at ? $employee->created_at->diffInDays(Carbon::now()) : null
                ];

                Log::debug('HISTORY API: Processing employee', [
                    'employee_id' => $employee->id,
                    'name' => $employee->nama_lengkap,
                    'created_at' => $employee->created_at,
                    'full_structure' => $organizationalStructure['full_structure']
                ]);

                return $data;
            });

            // Calculate summary statistics
            $summary = $this->calculateHistorySummary($startDate, $endDate);

            $response = [
                'success' => true,
                'history' => $historyData->values()->toArray(),
                'total' => $historyData->count(),
                'period' => '30 hari terakhir',
                'date_range' => [
                    'start' => $startDate->format('d/m/Y'),
                    'end' => $endDate->format('d/m/Y')
                ],
                'summary' => $summary,
                'debug' => [
                    'query_start_date' => $startDate->toISOString(),
                    'query_end_date' => $endDate->toISOString(),
                    'total_employees_found' => $historyData->count(),
                    'timestamp' => Carbon::now()->toISOString(),
                    'relationships_loaded' => true,
                    'using_employee_accessor' => true
                ]
            ];

            Log::info('HISTORY API: Successfully fetched employee history', [
                'total_employees' => $historyData->count(),
                'period' => '30 days',
                'summary_total' => $summary['total_period'] ?? 0
            ]);

            return response()->json($response);

        } catch (\Exception $e) {
            Log::error('HISTORY API: Employee History Error', [
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'history' => [],
                'total' => 0,
                'error' => 'Gagal mengambil data history karyawan',
                'message' => 'Terjadi kesalahan sistem. Silakan coba lagi.',
                'debug' => config('app.debug') ? [
                    'error_message' => $e->getMessage(),
                    'error_file' => $e->getFile(),
                    'error_line' => $e->getLine(),
                    'timestamp' => Carbon::now()->toISOString()
                ] : null
            ], 500);
        }
    }

    /**
     * FIXED: Get employee history summary - Enhanced dengan relationship loading
     * CRITICAL: Method untuk summary data History Modal
     */
    public function getEmployeeHistorySummary()
    {
        try {
            $startDate = Carbon::now()->subDays(30)->startOfDay();
            $endDate = Carbon::now()->endOfDay();

            Log::info('HISTORY SUMMARY API: Fetching employee history summary', [
                'start_date' => $startDate->format('Y-m-d H:i:s'),
                'end_date' => $endDate->format('Y-m-d H:i:s')
            ]);

            // Calculate summary statistics
            $summary = $this->calculateHistorySummary($startDate, $endDate);

            // FIXED: Get latest employees dengan relationship loading menggunakan accessor
            $latestEmployees = Employee::select([
                    'id', 'nip', 'nik', 'nama_lengkap', 'unit_organisasi', 
                    'unit_id', 'sub_unit_id', 'jabatan', 'nama_jabatan', 
                    'status_pegawai', 'created_at'
                ])
                ->with([
                    'unit:id,name,code,unit_organisasi',
                    'subUnit:id,name,code,unit_id'
                ])
                ->whereBetween('created_at', [$startDate, $endDate])
                ->orderBy('created_at', 'desc')
                ->limit(5)
                ->get()
                ->map(function ($employee) {
                    // FIXED: Gunakan accessor dari Employee model
                    return [
                        'id' => $employee->id,
                        'nama_lengkap' => $employee->nama_lengkap,
                        'initials' => $employee->initials,
                        'organizational_structure' => $employee->organizational_structure,
                        'jabatan' => $employee->jabatan ?? $employee->nama_jabatan,
                        'status_pegawai' => $employee->status_pegawai,
                        'created_at' => $employee->created_at,
                        'formatted_date' => $employee->created_at ? $employee->created_at->format('d/m/Y H:i') : null,
                        'relative_date' => $employee->created_at ? $employee->created_at->diffForHumans() : null
                    ];
                });

            $response = [
                'success' => true,
                'summary' => $summary,
                'latest_employees' => $latestEmployees->toArray(),
                'period' => '30 hari terakhir',
                'date_range' => [
                    'start' => $startDate->format('d/m/Y'),
                    'end' => $endDate->format('d/m/Y')
                ],
                'timestamp' => Carbon::now()->toISOString()
            ];

            Log::info('HISTORY SUMMARY API: Summary calculated successfully', [
                'summary_total' => $summary['total_period'] ?? 0,
                'latest_count' => $latestEmployees->count()
            ]);

            return response()->json($response);

        } catch (\Exception $e) {
            Log::error('HISTORY SUMMARY API: Error', [
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'summary' => [
                    'today' => 0,
                    'yesterday' => 0,
                    'this_week' => 0,
                    'this_month' => 0,
                    'total_period' => 0
                ],
                'latest_employees' => [],
                'error' => 'Gagal mengambil summary data history',
                'debug' => config('app.debug') ? [
                    'error_message' => $e->getMessage(),
                    'timestamp' => Carbon::now()->toISOString()
                ] : null
            ], 500);
        }
    }

    /**
     * FIXED: Get chart data untuk employee growth trend (30 hari terakhir)
     */
    public function getEmployeeGrowthChart()
    {
        try {
            $last30Days = [];
            
            for ($i = 29; $i >= 0; $i--) {
                $date = Carbon::now()->subDays($i);
                $count = Employee::whereDate('created_at', $date)->count();
                
                $last30Days[] = [
                    'date' => $date->format('Y-m-d'),
                    'formatted_date' => $date->format('d/m'),
                    'count' => $count,
                    'day_name' => $date->format('l'),
                    'is_weekend' => $date->isWeekend()
                ];
            }
            
            $totalHires = array_sum(array_column($last30Days, 'count'));
            
            Log::info('GROWTH CHART: Generated 30-day growth chart', [
                'total_days' => count($last30Days),
                'total_hires' => $totalHires
            ]);
            
            return response()->json([
                'success' => true,
                'chart_data' => $last30Days,
                'total_period' => $totalHires,
                'period' => '30 hari terakhir',
                'average_per_day' => $totalHires > 0 ? round($totalHires / 30, 2) : 0
            ]);
            
        } catch (\Exception $e) {
            Log::error('GROWTH CHART: Employee Growth Chart Error', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'success' => false,
                'chart_data' => [],
                'error' => 'Gagal mengambil data chart',
                'debug' => config('app.debug') ? [
                    'error_message' => $e->getMessage(),
                    'timestamp' => Carbon::now()->toISOString()
                ] : null
            ], 500);
        }
    }

    // =====================================================
    // EXISTING METHODS (PRESERVED & ENHANCED)
    // =====================================================

    /**
     * Export dashboard data
     */
    public function exportData(Request $request)
    {
        try {
            $format = $request->get('format', 'json');
            $statistics = $this->getStatisticsData();
            $organizationData = $this->getEmployeesByOrganization();
            
            switch ($format) {
                case 'csv':
                    return $this->exportToCsv($statistics, $organizationData);
                case 'pdf':
                    return $this->exportToPdf($statistics, $organizationData);
                default:
                    return response()->json([
                        'statistics' => $statistics,
                        'organization_data' => $organizationData,
                        'exported_at' => now()->toISOString(),
                    ]);
            }
        } catch (\Exception $e) {
            Log::error('Dashboard Export Error: ' . $e->getMessage());
            return response()->json([
                'error' => 'Export failed: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Health check for dashboard
     * ENHANCED: Include employee history feature status dan Unit/SubUnit model check
     */
    public function healthCheck()
    {
        try {
            $dbConnection = DB::connection()->getPdo();
            $employeeCount = Employee::count();
            
            // Check Unit and SubUnit models
            $unitCount = 0;
            $subUnitCount = 0;
            $unitModelAvailable = false;
            $subUnitModelAvailable = false;
            
            try {
                if (class_exists('App\Models\Unit')) {
                    $unitCount = Unit::count();
                    $unitModelAvailable = true;
                }
            } catch (\Exception $e) {
                Log::debug('Unit model check failed: ' . $e->getMessage());
            }
            
            try {
                if (class_exists('App\Models\SubUnit')) {
                    $subUnitCount = SubUnit::count();
                    $subUnitModelAvailable = true;
                }
            } catch (\Exception $e) {
                Log::debug('SubUnit model check failed: ' . $e->getMessage());
            }
            
            $organizationCount = 0;
            try {
                if (class_exists('App\Models\Organization')) {
                    $organizationCount = Organization::count();
                }
            } catch (\Exception $e) {
                // Fallback if Organization model doesn't exist
                $organizationCount = Employee::distinct('unit_organisasi')
                    ->whereNotNull('unit_organisasi')
                    ->count();
            }

            // Check recent employees for history feature
            $recentEmployees = Employee::where('created_at', '>=', Carbon::now()->subDays(30))->count();
            
            // Test history methods
            $historyMethodsWorking = false;
            try {
                $testResponse = $this->getEmployeeHistory();
                $testData = json_decode($testResponse->getContent(), true);
                $historyMethodsWorking = $testData['success'] ?? false;
            } catch (\Exception $e) {
                Log::debug('History method test failed: ' . $e->getMessage());
            }
            
            return response()->json([
                'status' => 'healthy',
                'system' => 'GAPURA ANGKASA SDM System',
                'database' => 'connected',
                'employee_count' => $employeeCount,
                'organization_count' => $organizationCount,
                'unit_count' => $unitCount,
                'sub_unit_count' => $subUnitCount,
                'recent_employees_30_days' => $recentEmployees,
                'models_available' => [
                    'Employee' => class_exists('App\Models\Employee'),
                    'Unit' => $unitModelAvailable,
                    'SubUnit' => $subUnitModelAvailable,
                    'Organization' => class_exists('App\Models\Organization')
                ],
                'features' => [
                    'employee_history' => $historyMethodsWorking,
                    'employee_statistics' => true,
                    'chart_data' => true,
                    'recent_activities' => true,
                    'organizational_structure' => $unitModelAvailable && $subUnitModelAvailable
                ],
                'history_methods' => [
                    'getEmployeeHistory' => method_exists($this, 'getEmployeeHistory'),
                    'getEmployeeHistorySummary' => method_exists($this, 'getEmployeeHistorySummary'),
                    'working' => $historyMethodsWorking
                ],
                'timestamp' => now()->toISOString(),
                'version' => '1.8.0',
            ]);
        } catch (\Exception $e) {
            Log::error('Dashboard Health Check Error: ' . $e->getMessage());
            return response()->json([
                'status' => 'unhealthy',
                'error' => $e->getMessage(),
                'timestamp' => now()->toISOString(),
            ], 500);
        }
    }

    // =====================================================
    // PRIVATE HELPER METHODS - ENHANCED & FIXED
    // =====================================================

    /**
     * FIXED: Calculate history summary statistics dengan comprehensive error handling
     */
    private function calculateHistorySummary($startDate, $endDate)
    {
        try {
            $today = Carbon::today();
            $yesterday = Carbon::yesterday();
            $weekStart = Carbon::now()->startOfWeek();
            $monthStart = Carbon::now()->startOfMonth();

            $summary = [
                'today' => Employee::whereDate('created_at', $today)->count(),
                'yesterday' => Employee::whereDate('created_at', $yesterday)->count(),
                'this_week' => Employee::where('created_at', '>=', $weekStart)->count(),
                'this_month' => Employee::where('created_at', '>=', $monthStart)->count(),
                'total_period' => Employee::whereBetween('created_at', [$startDate, $endDate])->count()
            ];

            // Add growth calculation
            $previousPeriodStart = $startDate->copy()->subDays(30);
            $previousPeriodTotal = Employee::whereBetween('created_at', [$previousPeriodStart, $startDate])->count();
            
            if ($previousPeriodTotal > 0) {
                $summary['growth_percentage'] = round((($summary['total_period'] - $previousPeriodTotal) / $previousPeriodTotal) * 100, 1);
            } else {
                $summary['growth_percentage'] = $summary['total_period'] > 0 ? 100 : 0;
            }

            Log::debug('HELPER: History summary calculated', $summary);

            return $summary;
        } catch (\Exception $e) {
            Log::error('HELPER: Calculate History Summary Error', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return [
                'today' => 0,
                'yesterday' => 0,
                'this_week' => 0,
                'this_month' => 0,
                'total_period' => 0,
                'growth_percentage' => 0
            ];
        }
    }

    /**
     * FIXED: Helper method - Get employee initials for display
     */
    private function getEmployeeInitials($namaLengkap)
    {
        if (empty($namaLengkap)) {
            return 'N';
        }

        try {
            $words = explode(' ', trim($namaLengkap));
            
            if (count($words) === 1) {
                return strtoupper(substr($words[0], 0, 1));
            }
            
            return strtoupper(substr($words[0], 0, 1) . substr($words[1], 0, 1));
        } catch (\Exception $e) {
            Log::debug('HELPER: Initials generation error', [
                'message' => $e->getMessage(),
                'employee_name' => $namaLengkap
            ]);
            return 'N';
        }
    }

    /**
     * Get chart data array - SAFE VERSION dengan enhanced error handling
     */
    private function getChartDataArray()
    {
        try {
            $byOrganization = $this->getEmployeesByOrganization();
            $byStatus = $this->getEmployeesByStatus();
            $byGender = $this->getEmployeesByGender();
            $byKelompokJabatan = $this->getEmployeesByKelompokJabatan();
            $tadBreakdown = $this->getTADBreakdown();
            $monthlyHires = $this->getMonthlyHires();
            $ageDistribution = $this->getAgeDistribution();

            return [
                'by_organization' => $byOrganization,
                'by_status' => $byStatus,
                'by_gender' => $byGender,
                'by_kelompok_jabatan' => $byKelompokJabatan,
                'tad_breakdown' => $tadBreakdown,
                'monthly_hires' => $monthlyHires,
                'age_distribution' => $ageDistribution,
            ];
        } catch (\Exception $e) {
            Log::error('Chart Data Array Error: ' . $e->getMessage());
            return [
                'by_organization' => [],
                'by_status' => [],
                'by_gender' => [],
                'by_kelompok_jabatan' => [],
                'tad_breakdown' => [],
                'monthly_hires' => [],
                'age_distribution' => [],
            ];
        }
    }

    /**
     * Get recent activities data - SAFE VERSION dengan enhanced logging
     */
    private function getRecentActivitiesData()
    {
        try {
            // Get recently added employees (last 30 days)
            $recentEmployees = Employee::select('nama_lengkap', 'jabatan', 'nama_jabatan', 'unit_organisasi', 'created_at')
                ->where('created_at', '>=', Carbon::now()->subDays(30))
                ->orderBy('created_at', 'desc')
                ->limit(10)
                ->get()
                ->map(function ($employee) {
                    return [
                        'type' => 'employee_added',
                        'title' => 'Karyawan Baru Ditambahkan',
                        'description' => $employee->nama_lengkap . ' - ' . ($employee->jabatan ?? $employee->nama_jabatan ?? 'Jabatan tidak tersedia'),
                        'unit' => $employee->unit_organisasi ?? 'Unit tidak tersedia',
                        'timestamp' => $employee->created_at,
                        'icon' => 'user-plus',
                        'color' => 'green',
                    ];
                });

            // Get recently updated employees (last 7 days)
            $updatedEmployees = Employee::select('nama_lengkap', 'jabatan', 'nama_jabatan', 'unit_organisasi', 'updated_at')
                ->where('updated_at', '>=', Carbon::now()->subDays(7))
                ->where('updated_at', '!=', DB::raw('created_at'))
                ->orderBy('updated_at', 'desc')
                ->limit(5)
                ->get()
                ->map(function ($employee) {
                    return [
                        'type' => 'employee_updated',
                        'title' => 'Data Karyawan Diperbarui',
                        'description' => $employee->nama_lengkap . ' - ' . ($employee->jabatan ?? $employee->nama_jabatan ?? 'Jabatan tidak tersedia'),
                        'unit' => $employee->unit_organisasi ?? 'Unit tidak tersedia',
                        'timestamp' => $employee->updated_at,
                        'icon' => 'edit',
                        'color' => 'blue',
                    ];
                });

            // Combine and sort by timestamp
            $activities = $recentEmployees->concat($updatedEmployees)
                ->sortByDesc('timestamp')
                ->values()
                ->take(15);

            Log::debug('HELPER: Recent activities generated', [
                'total_activities' => $activities->count(),
                'recent_employees' => $recentEmployees->count(),
                'updated_employees' => $updatedEmployees->count()
            ]);

            return [
                'activities' => $activities,
                'total' => $activities->count(),
            ];
        } catch (\Exception $e) {
            Log::error('Recent Activities Data Error: ' . $e->getMessage());
            return [
                'activities' => [],
                'total' => 0
            ];
        }
    }

    /**
     * Get employee history summary data - SAFE VERSION
     */
    private function getEmployeeHistorySummaryData()
    {
        try {
            $summary = $this->getEmployeeHistorySummary();
            return json_decode($summary->getContent(), true);
        } catch (\Exception $e) {
            Log::error('Employee History Summary Data Error: ' . $e->getMessage());
            return [
                'success' => false,
                'summary' => [
                    'today' => 0,
                    'yesterday' => 0,
                    'this_week' => 0,
                    'this_month' => 0,
                    'total_period' => 0
                ],
                'latest_employees' => []
            ];
        }
    }

    /**
     * Private method to get statistics data
     * UPDATED: Include TAD Split, PKWT, dan Kelompok Jabatan statistics
     */
    private function getStatisticsData()
    {
        try {
            // Base statistics
            $totalEmployees = Employee::count();
            
            // Check if status column exists, if not use alternative approach
            $activeEmployees = $totalEmployees;
            if (Schema::hasColumn('employees', 'status')) {
                $activeEmployees = Employee::where('status', 'active')->count();
            }
            
            $pegawaiTetap = Employee::where('status_pegawai', 'PEGAWAI TETAP')->count();
            $pkwt = Employee::where('status_pegawai', 'PKWT')->count();
            
            // TAD Statistics dengan split - FITUR BARU
            $tadPaketSDM = Employee::where('status_pegawai', 'TAD PAKET SDM')->count();
            $tadPaketPekerjaan = Employee::where('status_pegawai', 'TAD PAKET PEKERJAAN')->count();
            $tadTotal = $tadPaketSDM + $tadPaketPekerjaan;
            
            // Backward compatibility - masih support TAD lama
            $tadLegacy = Employee::where('status_pegawai', 'TAD')->count();
            if ($tadLegacy > 0 && $tadTotal == 0) {
                $tadTotal = $tadLegacy;
            }

            // Gender statistics - handle both L/P and Laki-laki/Perempuan formats
            $maleEmployees = Employee::where(function ($query) {
                $query->where('jenis_kelamin', 'L')
                      ->orWhere('jenis_kelamin', 'Laki-laki');
            })->count();
            
            $femaleEmployees = Employee::where(function ($query) {
                $query->where('jenis_kelamin', 'P')
                      ->orWhere('jenis_kelamin', 'Perempuan');
            })->count();

            // Kelompok Jabatan statistics - FITUR BARU
            $kelompokJabatanQuery = Employee::select('kelompok_jabatan', DB::raw('COUNT(*) as count'))
                ->whereNotNull('kelompok_jabatan');
                
            if (Schema::hasColumn('employees', 'status')) {
                $kelompokJabatanQuery->where('status', 'active');
            }
            
            $kelompokJabatanStats = $kelompokJabatanQuery
                ->groupBy('kelompok_jabatan')
                ->get()
                ->pluck('count', 'kelompok_jabatan');
            
            // Organization count
            $totalOrganizations = 0;
            try {
                if (class_exists('App\Models\Organization')) {
                    $totalOrganizations = Organization::count();
                }
            } catch (\Exception $e) {
                // Ignore if Organization model doesn't exist
            }
            
            if ($totalOrganizations == 0) {
                // Fallback: count unique units from employees
                $totalOrganizations = Employee::distinct('unit_organisasi')
                    ->whereNotNull('unit_organisasi')
                    ->count();
            }

            // Recent hires (last 6 months)
            $recentHiresQuery = Employee::where(function ($query) {
                $query->where('tmt_mulai_kerja', '>=', Carbon::now()->subMonths(6))
                      ->orWhere('created_at', '>=', Carbon::now()->subMonths(6));
            });
            
            if (Schema::hasColumn('employees', 'status')) {
                $recentHiresQuery->where('status', 'active');
            }
            
            $recentHires = $recentHiresQuery->count();

            // Upcoming retirement (next 12 months) - Updated untuk 56 tahun
            $upcomingRetirementQuery = Employee::whereNotNull('tmt_pensiun')
                ->whereBetween('tmt_pensiun', [Carbon::now(), Carbon::now()->addMonths(12)]);
                
            if (Schema::hasColumn('employees', 'status')) {
                $upcomingRetirementQuery->where('status', 'active');
            }
            
            $upcomingRetirement = $upcomingRetirementQuery->count();

            return [
                // Basic statistics
                'total_employees' => $totalEmployees,
                'active_employees' => $activeEmployees,
                'inactive_employees' => $totalEmployees - $activeEmployees,
                'pegawai_tetap' => $pegawaiTetap,
                'pkwt' => $pkwt,
                
                // TAD Statistics dengan breakdown - FITUR BARU
                'tad_total' => $tadTotal,
                'tad_paket_sdm' => $tadPaketSDM,
                'tad_paket_pekerjaan' => $tadPaketPekerjaan,
                'tad' => $tadTotal, // Backward compatibility
                
                // Gender
                'male_employees' => $maleEmployees,
                'female_employees' => $femaleEmployees,
                
                // Kelompok Jabatan breakdown - FITUR BARU
                'kelompok_jabatan' => [
                    'supervisor' => $kelompokJabatanStats['SUPERVISOR'] ?? 0,
                    'staff' => $kelompokJabatanStats['STAFF'] ?? 0,
                    'manager' => $kelompokJabatanStats['MANAGER'] ?? 0,
                    'executive_gm' => $kelompokJabatanStats['EXECUTIVE GENERAL MANAGER'] ?? 0,
                    'account_executive' => $kelompokJabatanStats['ACCOUNT EXECUTIVE/AE'] ?? 0,
                ],
                
                // Additional stats
                'total_organizations' => $totalOrganizations,
                'recent_hires' => $recentHires,
                'upcoming_retirement' => $upcomingRetirement,
                'growth_rate' => $this->calculateGrowthRate(),
            ];
        } catch (\Exception $e) {
            Log::error('Statistics Data Error: ' . $e->getMessage());
            return $this->getDefaultStatistics();
        }
    }

    /**
     * Get employees by organization
     */
    private function getEmployeesByOrganization()
    {
        try {
            $query = Employee::select('unit_organisasi', DB::raw('count(*) as total'))
                ->whereNotNull('unit_organisasi');
                
            if (Schema::hasColumn('employees', 'status')) {
                $query->where('status', 'active');
            }
            
            return $query->groupBy('unit_organisasi')
                ->orderBy('total', 'desc')
                ->get()
                ->map(function ($item) {
                    return [
                        'name' => $item->unit_organisasi,
                        'value' => $item->total,
                        'percentage' => 0, // Will be calculated in frontend
                    ];
                });
        } catch (\Exception $e) {
            Log::error('Employees by Organization Error: ' . $e->getMessage());
            return collect([]);
        }
    }

    /**
     * Get employees by status
     * UPDATED: Support TAD Split
     */
    private function getEmployeesByStatus()
    {
        try {
            $query = Employee::select('status_pegawai', DB::raw('count(*) as total'))
                ->whereNotNull('status_pegawai');
                
            if (Schema::hasColumn('employees', 'status')) {
                $query->where('status', 'active');
            }
            
            return $query->groupBy('status_pegawai')
                ->get()
                ->map(function ($item) {
                    return [
                        'name' => $item->status_pegawai,
                        'value' => $item->total,
                        'color' => $this->getStatusColor($item->status_pegawai),
                    ];
                });
        } catch (\Exception $e) {
            Log::error('Employees by Status Error: ' . $e->getMessage());
            return collect([]);
        }
    }

    /**
     * Get employees by gender
     */
    private function getEmployeesByGender()
    {
        try {
            // Handle both L/P and Laki-laki/Perempuan formats
            $maleQuery = Employee::where(function ($query) {
                $query->where('jenis_kelamin', 'L')
                      ->orWhere('jenis_kelamin', 'Laki-laki');
            });
            
            $femaleQuery = Employee::where(function ($query) {
                $query->where('jenis_kelamin', 'P')
                      ->orWhere('jenis_kelamin', 'Perempuan');
            });
            
            if (Schema::hasColumn('employees', 'status')) {
                $maleQuery->where('status', 'active');
                $femaleQuery->where('status', 'active');
            }
            
            $maleCount = $maleQuery->count();
            $femaleCount = $femaleQuery->count();

            return [
                [
                    'name' => 'Laki-laki',
                    'value' => $maleCount,
                    'color' => '#3B82F6',
                ],
                [
                    'name' => 'Perempuan',
                    'value' => $femaleCount,
                    'color' => '#EC4899',
                ],
            ];
        } catch (\Exception $e) {
            Log::error('Employees by Gender Error: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Get employees by kelompok jabatan - FITUR BARU
     */
    private function getEmployeesByKelompokJabatan()
    {
        try {
            $query = Employee::select('kelompok_jabatan', DB::raw('count(*) as total'))
                ->whereNotNull('kelompok_jabatan');
                
            if (Schema::hasColumn('employees', 'status')) {
                $query->where('status', 'active');
            }
            
            return $query->groupBy('kelompok_jabatan')
                ->orderBy('total', 'desc')
                ->get()
                ->map(function ($item) {
                    return [
                        'name' => $item->kelompok_jabatan,
                        'value' => $item->total,
                        'color' => $this->getKelompokJabatanColor($item->kelompok_jabatan),
                    ];
                });
        } catch (\Exception $e) {
            Log::error('Employees by Kelompok Jabatan Error: ' . $e->getMessage());
            return collect([]);
        }
    }

    /**
     * Get TAD breakdown untuk chart - FITUR BARU
     */
    private function getTADBreakdown()
    {
        try {
            $tadPaketSDM = Employee::where('status_pegawai', 'TAD PAKET SDM')->count();
            $tadPaketPekerjaan = Employee::where('status_pegawai', 'TAD PAKET PEKERJAAN')->count();

            return [
                [
                    'name' => 'TAD Paket SDM',
                    'value' => $tadPaketSDM,
                    'color' => '#F59E0B',
                    'description' => 'Sumber Daya Manusia',
                ],
                [
                    'name' => 'TAD Paket Pekerjaan', 
                    'value' => $tadPaketPekerjaan,
                    'color' => '#EF4444',
                    'description' => 'Kontrak Pekerjaan',
                ],
            ];
        } catch (\Exception $e) {
            Log::error('TAD Breakdown Error: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Get monthly hires for the last 12 months
     */
    private function getMonthlyHires()
    {
        try {
            $query = Employee::select(
                    DB::raw('YEAR(COALESCE(tmt_mulai_kerja, created_at)) as year'),
                    DB::raw('MONTH(COALESCE(tmt_mulai_kerja, created_at)) as month'),
                    DB::raw('count(*) as total')
                )
                ->where(function ($query) {
                    $query->where('tmt_mulai_kerja', '>=', now()->subMonths(12))
                          ->orWhere('created_at', '>=', now()->subMonths(12));
                });
                
            if (Schema::hasColumn('employees', 'status')) {
                $query->where('status', 'active');
            }
            
            $monthlyData = $query->groupBy('year', 'month')
                ->orderBy('year', 'asc')
                ->orderBy('month', 'asc')
                ->get();

            return $monthlyData->map(function ($item) {
                $monthName = Carbon::createFromDate($item->year, $item->month, 1)->format('M Y');
                return [
                    'month' => $monthName,
                    'hires' => $item->total,
                    'year' => $item->year,
                    'month_number' => $item->month,
                ];
            });
        } catch (\Exception $e) {
            Log::error('Monthly Hires Error: ' . $e->getMessage());
            return collect([]);
        }
    }

    /**
     * Get age distribution
     */
    private function getAgeDistribution()
    {
        try {
            $query = Employee::whereNotNull('tanggal_lahir');
            
            if (Schema::hasColumn('employees', 'status')) {
                $query->where('status', 'active');
            }
            
            $employees = $query->get();

            $ageGroups = [
                '20-30' => 0,
                '31-40' => 0,
                '41-50' => 0,
                '51-60' => 0,
                '60+' => 0,
            ];

            foreach ($employees as $employee) {
                try {
                    $age = Carbon::parse($employee->tanggal_lahir)->age;
                    
                    if ($age <= 30) {
                        $ageGroups['20-30']++;
                    } elseif ($age <= 40) {
                        $ageGroups['31-40']++;
                    } elseif ($age <= 50) {
                        $ageGroups['41-50']++;
                    } elseif ($age <= 60) {
                        $ageGroups['51-60']++;
                    } else {
                        $ageGroups['60+']++;
                    }
                } catch (\Exception $e) {
                    // Skip invalid dates
                    continue;
                }
            }

            return collect($ageGroups)->map(function ($count, $group) {
                return [
                    'name' => $group . ' tahun',
                    'value' => $count,
                ];
            })->values();
        } catch (\Exception $e) {
            Log::error('Age Distribution Error: ' . $e->getMessage());
            return collect([]);
        }
    }

    /**
     * Get color for status pegawai - FITUR BARU
     */
    private function getStatusColor($status)
    {
        return match($status) {
            'PEGAWAI TETAP' => '#10B981',
            'PKWT' => '#3B82F6',
            'TAD PAKET SDM' => '#F59E0B',
            'TAD PAKET PEKERJAAN' => '#EF4444',
            'TAD' => '#F59E0B', // Backward compatibility
            default => '#6B7280',
        };
    }

    /**
     * Get color for kelompok jabatan - FITUR BARU
     */
    private function getKelompokJabatanColor($kelompok)
    {
        return match($kelompok) {
            'SUPERVISOR' => '#8B5CF6',
            'STAFF' => '#06B6D4', 
            'MANAGER' => '#10B981',
            'EXECUTIVE GENERAL MANAGER' => '#F59E0B',
            'ACCOUNT EXECUTIVE/AE' => '#EF4444',
            default => '#6B7280',
        };
    }

    /**
     * Calculate growth rate (month over month)
     */
    private function calculateGrowthRate()
    {
        try {
            $currentMonth = Employee::whereMonth('created_at', now()->month)
                ->whereYear('created_at', now()->year)
                ->count();
                
            $lastMonth = Employee::whereMonth('created_at', now()->subMonth()->month)
                ->whereYear('created_at', now()->subMonth()->year)
                ->count();

            if ($lastMonth == 0) {
                return $currentMonth > 0 ? 100 : 0;
            }

            return round((($currentMonth - $lastMonth) / $lastMonth) * 100, 1);
        } catch (\Exception $e) {
            Log::error('Growth Rate Calculation Error: ' . $e->getMessage());
            return 0;
        }
    }

    /**
     * Get default statistics (fallback)
     * UPDATED: Include TAD Split fields
     */
    private function getDefaultStatistics()
    {
        return [
            'total_employees' => 0,
            'active_employees' => 0,
            'inactive_employees' => 0,
            'pegawai_tetap' => 0,
            'pkwt' => 0,
            'tad_total' => 0,
            'tad_paket_sdm' => 0,
            'tad_paket_pekerjaan' => 0,
            'tad' => 0,
            'male_employees' => 0,
            'female_employees' => 0,
            'kelompok_jabatan' => [
                'supervisor' => 0,
                'staff' => 0,
                'manager' => 0,
                'executive_gm' => 0,
                'account_executive' => 0,
            ],
            'total_organizations' => 0,
            'recent_hires' => 0,
            'upcoming_retirement' => 0,
            'growth_rate' => 0,
        ];
    }

    /**
     * Get default chart data (fallback)
     * UPDATED: Include new chart types
     */
    private function getDefaultChartData()
    {
        return [
            'by_organization' => [],
            'by_status' => [],
            'by_gender' => [],
            'by_kelompok_jabatan' => [],
            'tad_breakdown' => [],
            'monthly_hires' => [],
            'age_distribution' => [],
        ];
    }

    /**
     * Export to CSV
     * UPDATED: Include TAD Split dan Kelompok Jabatan data
     */
    private function exportToCsv($statistics, $organizationData)
    {
        $filename = 'dashboard_statistics_' . date('Y-m-d_H-i-s') . '.csv';
        
        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"$filename\"",
        ];

        $callback = function () use ($statistics, $organizationData) {
            $file = fopen('php://output', 'w');
            
            // Add BOM for UTF-8
            fprintf($file, chr(0xEF) . chr(0xBB) . chr(0xBF));
            
            // Header
            fputcsv($file, ['LAPORAN DASHBOARD SDM GAPURA ANGKASA']);
            fputcsv($file, ['Tanggal Laporan', date('d/m/Y H:i:s')]);
            fputcsv($file, ['']);
            
            // Statistics
            fputcsv($file, ['STATISTIK UMUM']);
            fputcsv($file, ['Metrik', 'Nilai']);
            foreach ($statistics as $key => $value) {
                if ($key === 'kelompok_jabatan' && is_array($value)) {
                    fputcsv($file, ['=== KELOMPOK JABATAN ===', '']);
                    foreach ($value as $jabatan => $count) {
                        $label = ucwords(str_replace('_', ' ', $jabatan));
                        fputcsv($file, [$label, $count]);
                    }
                } else {
                    $label = ucfirst(str_replace('_', ' ', $key));
                    fputcsv($file, [$label, is_array($value) ? 'Complex Data' : $value]);
                }
            }
            
            fputcsv($file, ['']);
            
            // Organization breakdown
            fputcsv($file, ['DISTRIBUSI PER UNIT ORGANISASI']);
            fputcsv($file, ['Unit Organisasi', 'Jumlah Karyawan']);
            foreach ($organizationData as $org) {
                fputcsv($file, [$org['name'], $org['value']]);
            }
            
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * Export to PDF (placeholder)
     */
    private function exportToPdf($statistics, $organizationData)
    {
        // This would require a PDF library like DOMPDF or TCPDF
        // For now, return JSON with a message
        return response()->json([
            'message' => 'PDF export feature akan segera tersedia',
            'data' => [
                'statistics' => $statistics,
                'organization_data' => $organizationData,
            ],
            'exported_at' => now()->toISOString(),
        ]);
    }
}