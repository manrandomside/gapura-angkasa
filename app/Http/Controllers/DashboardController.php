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
     * UPDATED: Include TAD Split dan Kelompok Jabatan dengan GENERAL MANAGER & NON
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
     * Get chart data for dashboard - UPDATED for 6 Charts Real-time
     * NEW: Restructured for new dashboard with 6 specific charts
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
                'gender' => [],
                'status' => [],
                'unit' => [],
                'provider' => [],
                'age' => [],
                'jabatan' => []
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
    // FIXED: EMPLOYEE HISTORY METHODS untuk History Modal - UPDATED to 3 periods only
    // =====================================================

    /**
     * FIXED: Get employee history - Karyawan yang baru ditambahkan (30 hari terakhir)
     * SAFE VERSION: Tidak menggunakan eager loading untuk menghindari error
     */
    public function getEmployeeHistory()
    {
        try {
            $startDate = Carbon::now('Asia/Makassar')->subDays(30)->startOfDay();
            $endDate = Carbon::now('Asia/Makassar')->endOfDay();

            Log::info('HISTORY API: Fetching employee history', [
                'start_date' => $startDate->format('Y-m-d H:i:s'),
                'end_date' => $endDate->format('Y-m-d H:i:s'),
                'method' => 'getEmployeeHistory'
            ]);

            // FIXED: Query tanpa eager loading untuk menghindari relationship error
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
                ->whereBetween('created_at', [$startDate, $endDate])
                ->orderBy('created_at', 'desc')
                ->get();

            Log::info('HISTORY API: Raw query results', [
                'total_found' => $employees->count(),
                'period_days' => 30,
                'safe_mode' => true
            ]);

            // FIXED: Safe data mapping tanpa dependency pada relationship
            $historyData = $employees->map(function ($employee) {
                $organizationalStructure = $this->buildOrganizationalStructureSafe($employee);
                
                $data = [
                    'id' => $employee->id,
                    'nip' => $employee->nip ?? 'Tidak tersedia',
                    'nik' => $employee->nik ?? 'Tidak tersedia',
                    'nama_lengkap' => $employee->nama_lengkap ?? 'Nama tidak tersedia',
                    'initials' => $this->getEmployeeInitials($employee->nama_lengkap),
                    'organizational_structure' => $organizationalStructure,
                    'unit_organisasi' => $employee->unit_organisasi ?? 'Tidak tersedia',
                    'unit_name' => $organizationalStructure['unit']['name'] ?? null,
                    'sub_unit_name' => $organizationalStructure['sub_unit']['name'] ?? null,
                    'full_structure' => $organizationalStructure['full_structure'],
                    'jabatan' => $employee->jabatan ?? $employee->nama_jabatan ?? 'Tidak tersedia',
                    'kelompok_jabatan' => $employee->kelompok_jabatan ?? 'Tidak tersedia',
                    'status_pegawai' => $employee->status_pegawai ?? 'Tidak tersedia',
                    'created_at' => $employee->created_at,
                    'formatted_date' => $employee->created_at ? $employee->created_at->setTimezone('Asia/Makassar')->format('d/m/Y H:i') : null,
                    'relative_date' => $employee->created_at ? $employee->created_at->diffForHumans() : null,
                    'days_ago' => $employee->created_at ? $employee->created_at->diffInDays(Carbon::now('Asia/Makassar')) : null
                ];

                Log::debug('HISTORY API: Processing employee', [
                    'employee_id' => $employee->id,
                    'name' => $employee->nama_lengkap,
                    'created_at' => $employee->created_at,
                    'full_structure' => $organizationalStructure['full_structure']
                ]);

                return $data;
            });

            // Calculate summary statistics safely (only 3 periods)
            $summary = $this->calculateHistorySummarySafe($startDate, $endDate);

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
                'periods_included' => ['today', 'this_week', 'total_period'],
                'debug' => [
                    'query_start_date' => $startDate->toISOString(),
                    'query_end_date' => $endDate->toISOString(),
                    'total_employees_found' => $historyData->count(),
                    'timestamp' => Carbon::now('Asia/Makassar')->toISOString(),
                    'safe_mode' => true,
                    'method_version' => 'fixed_safe_v3.0_3periods'
                ]
            ];

            Log::info('HISTORY API: Successfully fetched employee history (3 periods)', [
                'total_employees' => $historyData->count(),
                'period' => '30 days',
                'summary_today' => $summary['today'] ?? 0,
                'summary_week' => $summary['this_week'] ?? 0,
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
                    'timestamp' => Carbon::now('Asia/Makassar')->toISOString()
                ] : null
            ], 500);
        }
    }

    /**
     * FIXED: Get employee history summary - Enhanced untuk 3 periods only
     * API endpoint: /api/dashboard/employee-history-summary
     */
    public function getEmployeeHistorySummary()
    {
        try {
            $startDate = Carbon::now('Asia/Makassar')->subDays(30)->startOfDay();
            $endDate = Carbon::now('Asia/Makassar')->endOfDay();

            Log::info('HISTORY SUMMARY API: Fetching summary data (3 periods only)', [
                'start_date' => $startDate->format('Y-m-d H:i:s'),
                'end_date' => $endDate->format('Y-m-d H:i:s'),
                'method' => 'getEmployeeHistorySummary'
            ]);

            // Calculate summary with only 3 periods
            $summary = $this->calculateHistorySummarySafe($startDate, $endDate);

            // Get latest employees for preview
            $latestEmployees = Employee::select([
                    'id', 'nip', 'nik', 'nama_lengkap', 'unit_organisasi', 
                    'unit_id', 'sub_unit_id', 'jabatan', 'nama_jabatan', 
                    'status_pegawai', 'created_at'
                ])
                ->whereBetween('created_at', [$startDate, $endDate])
                ->orderBy('created_at', 'desc')
                ->limit(5)
                ->get()
                ->map(function ($employee) {
                    return [
                        'id' => $employee->id,
                        'nip' => $employee->nip,
                        'nik' => $employee->nik,
                        'nama_lengkap' => $employee->nama_lengkap,
                        'unit_organisasi' => $employee->unit_organisasi,
                        'jabatan' => $employee->jabatan ?? $employee->nama_jabatan,
                        'status_pegawai' => $employee->status_pegawai,
                        'created_at' => $employee->created_at,
                        'formatted_date' => $employee->created_at ? $employee->created_at->setTimezone('Asia/Makassar')->format('d/m/Y H:i') : null,
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
                'periods_included' => ['today', 'this_week', 'total_period'],
                'timestamp' => Carbon::now('Asia/Makassar')->toISOString()
            ];

            Log::info('HISTORY SUMMARY API: Summary calculated successfully (3 periods only)', [
                'today' => $summary['today'],
                'this_week' => $summary['this_week'],
                'total_period' => $summary['total_period'],
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
                    'this_week' => 0,
                    'total_period' => 0,
                    'growth_percentage' => 0
                ],
                'latest_employees' => [],
                'error' => 'Gagal mengambil summary data history',
                'debug' => config('app.debug') ? [
                    'error_message' => $e->getMessage(),
                    'error_file' => $e->getFile(),
                    'error_line' => $e->getLine(),
                    'timestamp' => Carbon::now('Asia/Makassar')->toISOString()
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
                $date = Carbon::now('Asia/Makassar')->subDays($i);
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
                    'timestamp' => Carbon::now('Asia/Makassar')->toISOString()
                ] : null
            ], 500);
        }
    }

    // =====================================================
    // UPDATED CHART DATA METHODS FOR 6 CHARTS - REAL TIME
    // =====================================================

    /**
     * NEW: Get chart data array - RESTRUCTURED for 6 specific charts
     */
    private function getChartDataArray()
    {
        try {
            Log::info('CHART DATA: Generating real-time chart data for 6 charts');
            
            $chartData = [
                'gender' => $this->getGenderChartData(),
                'status' => $this->getStatusChartData(),
                'unit' => $this->getUnitChartData(),
                'provider' => $this->getProviderChartData(),
                'age' => $this->getAgeChartData(),
                'jabatan' => $this->getJabatanChartData()
            ];
            
            Log::info('CHART DATA: Successfully generated all chart data', [
                'gender_count' => count($chartData['gender']),
                'status_count' => count($chartData['status']),
                'unit_count' => count($chartData['unit']),
                'provider_count' => count($chartData['provider']),
                'age_count' => count($chartData['age']),
                'jabatan_count' => count($chartData['jabatan']),
                'timestamp' => Carbon::now('Asia/Makassar')->toISOString()
            ]);
            
            return $chartData;
        } catch (\Exception $e) {
            Log::error('Chart Data Array Error: ' . $e->getMessage());
            return [
                'gender' => [],
                'status' => [],
                'unit' => [],
                'provider' => [],
                'age' => [],
                'jabatan' => []
            ];
        }
    }

    /**
     * NEW: Get gender chart data (Jenis Kelamin) - REAL TIME
     * Chart Type: Pie Chart
     */
    private function getGenderChartData()
    {
        try {
            $maleCount = Employee::where(function ($query) {
                $query->where('jenis_kelamin', 'L')
                      ->orWhere('jenis_kelamin', 'Laki-laki')
                      ->orWhere('jenis_kelamin', 'LAKI-LAKI');
            })
            ->when(Schema::hasColumn('employees', 'status'), function ($query) {
                return $query->where('status', 'active');
            })
            ->count();
            
            $femaleCount = Employee::where(function ($query) {
                $query->where('jenis_kelamin', 'P')
                      ->orWhere('jenis_kelamin', 'Perempuan')
                      ->orWhere('jenis_kelamin', 'PEREMPUAN');
            })
            ->when(Schema::hasColumn('employees', 'status'), function ($query) {
                return $query->where('status', 'active');
            })
            ->count();

            $total = $maleCount + $femaleCount;
            
            Log::debug('GENDER CHART: Generated data', [
                'male_count' => $maleCount,
                'female_count' => $femaleCount,
                'total' => $total
            ]);
            
            return [
                [
                    'name' => 'Laki-laki',
                    'value' => $maleCount,
                    'percentage' => $total > 0 ? round(($maleCount / $total) * 100, 1) : 0,
                    'label' => $maleCount . ' (' . ($total > 0 ? round(($maleCount / $total) * 100, 1) : 0) . '%)'
                ],
                [
                    'name' => 'Perempuan',
                    'value' => $femaleCount,
                    'percentage' => $total > 0 ? round(($femaleCount / $total) * 100, 1) : 0,
                    'label' => $femaleCount . ' (' . ($total > 0 ? round(($femaleCount / $total) * 100, 1) : 0) . '%)'
                ]
            ];
        } catch (\Exception $e) {
            Log::error('Gender Chart Data Error: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * NEW: Get status chart data (Status Pegawai) - REAL TIME
     * Chart Type: Bar Chart
     * Include: PEGAWAI TETAP, TAD (with breakdown), PKWT
     */
    private function getStatusChartData()
    {
        try {
            $query = Employee::select('status_pegawai', DB::raw('count(*) as total'))
                ->whereNotNull('status_pegawai')
                ->where('status_pegawai', '!=', '')
                ->when(Schema::hasColumn('employees', 'status'), function ($query) {
                    return $query->where('status', 'active');
                })
                ->groupBy('status_pegawai')
                ->get();

            // Process and combine TAD data as requested
            $result = [];
            $tadPaketSDM = 0;
            $tadPaketPekerjaan = 0;
            $tadOther = 0;
            
            foreach ($query as $item) {
                switch ($item->status_pegawai) {
                    case 'PEGAWAI TETAP':
                        $result[] = [
                            'name' => 'PEGAWAI TETAP',
                            'value' => $item->total
                        ];
                        break;
                    case 'PKWT':
                        $result[] = [
                            'name' => 'PKWT',
                            'value' => $item->total
                        ];
                        break;
                    case 'TAD PAKET SDM':
                        $tadPaketSDM = $item->total;
                        break;
                    case 'TAD PAKET PEKERJAAN':
                        $tadPaketPekerjaan = $item->total;
                        break;
                    case 'TAD':
                        $tadOther = $item->total;
                        break;
                }
            }
            
            // Add TAD breakdown
            if ($tadPaketSDM > 0) {
                $result[] = [
                    'name' => 'TAD PAKET SDM',
                    'value' => $tadPaketSDM
                ];
            }
            
            if ($tadPaketPekerjaan > 0) {
                $result[] = [
                    'name' => 'TAD PAKET PEKERJAAN',
                    'value' => $tadPaketPekerjaan
                ];
            }
            
            // Add TAD Total (combination of all TAD types)
            $tadTotal = $tadPaketSDM + $tadPaketPekerjaan + $tadOther;
            if ($tadTotal > 0) {
                $result[] = [
                    'name' => 'TAD TOTAL',
                    'value' => $tadTotal
                ];
            }

            // Sort by value descending to ensure proportional display
            usort($result, function($a, $b) {
                return $b['value'] - $a['value'];
            });

            Log::debug('STATUS CHART: Generated data', [
                'total_categories' => count($result),
                'tad_paket_sdm' => $tadPaketSDM,
                'tad_paket_pekerjaan' => $tadPaketPekerjaan,
                'tad_total' => $tadTotal,
                'result_data' => $result
            ]);

            return $result;
        } catch (\Exception $e) {
            Log::error('Status Chart Data Error: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * NEW: Get unit chart data (SDM per Unit) - REAL TIME
     * Chart Type: Bar Chart
     * Focus on: EGM, GM, MO, MF, MS, MU, MK, MQ, ME, MB
     */
    private function getUnitChartData()
    {
        try {
            $targetUnits = ['EGM', 'GM', 'MO', 'MF', 'MS', 'MU', 'MK', 'MQ', 'ME', 'MB'];
            
            $query = Employee::select('unit_organisasi', DB::raw('count(*) as total'))
                ->whereNotNull('unit_organisasi')
                ->where('unit_organisasi', '!=', '')
                ->whereIn('unit_organisasi', $targetUnits)
                ->when(Schema::hasColumn('employees', 'status'), function ($query) {
                    return $query->where('status', 'active');
                })
                ->groupBy('unit_organisasi')
                ->get();

            // Create complete list with all target units
            $result = [];
            foreach ($targetUnits as $unit) {
                $found = $query->firstWhere('unit_organisasi', $unit);
                $result[] = [
                    'name' => $unit,
                    'value' => $found ? $found->total : 0
                ];
            }

            // Sort by value descending to ensure proportional display
            usort($result, function($a, $b) {
                return $b['value'] - $a['value'];
            });

            Log::debug('UNIT CHART: Generated data', [
                'total_units' => count($result),
                'units_with_data' => $query->count(),
                'result_data' => $result
            ]);

            return $result;
        } catch (\Exception $e) {
            Log::error('Unit Chart Data Error: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * NEW: Get provider chart data (SDM per Provider) - REAL TIME
     * Chart Type: Bar Chart
     * Include: 10 companies as specified
     */
    private function getProviderChartData()
    {
        try {
            $targetProviders = [
                'PT Gapura Angkasa',
                'PT Air Box Personalia',
                'PT Finfleet Teknologi Indonesia',
                'PT Mitra Angkasa Perdana',
                'PT Safari Dharma Sakti',
                'PT Grha Humanindo Management',
                'PT Duta Griya Sarana',
                'PT Aerotrans Wisata',
                'PT Mandala Garda Nusantara',
                'PT Kidora Mandiri Investama'
            ];

            $query = Employee::select('provider', DB::raw('count(*) as total'))
                ->whereNotNull('provider')
                ->where('provider', '!=', '')
                ->when(Schema::hasColumn('employees', 'status'), function ($query) {
                    return $query->where('status', 'active');
                })
                ->groupBy('provider')
                ->orderBy('total', 'desc')
                ->get();

            $result = [];
            $processedProviders = [];
            
            // Process existing data and clean names
            foreach ($query as $item) {
                $cleanName = $this->cleanProviderName($item->provider);
                
                // Skip if already processed (prevents duplicates)
                if (in_array($cleanName, $processedProviders)) {
                    continue;
                }
                
                $result[] = [
                    'name' => $cleanName,
                    'value' => $item->total
                ];
                
                $processedProviders[] = $cleanName;
            }

            // Add missing target providers with 0 count
            foreach ($targetProviders as $provider) {
                if (!in_array($provider, $processedProviders)) {
                    $result[] = [
                        'name' => $provider,
                        'value' => 0
                    ];
                }
            }

            // Sort by value descending to ensure proportional display
            usort($result, function($a, $b) {
                return $b['value'] - $a['value'];
            });

            Log::debug('PROVIDER CHART: Generated data', [
                'total_providers' => count($result),
                'providers_with_data' => $query->count(),
                'result_data' => $result
            ]);

            return $result;
        } catch (\Exception $e) {
            Log::error('Provider Chart Data Error: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * FIXED: Get age chart data (Komposisi Usia SDM) - REAL TIME dengan timezone Asia/Makassar
     * Chart Type: Bar Chart  
     * Age groups: 18-25, 26-35, 36-45, 46-55
     * FIXED: Proper age calculation with WITA timezone and better date validation
     */
    private function getAgeChartData()
    {
        try {
            $ageGroups = [
                '18-25' => 0,
                '26-35' => 0, 
                '36-45' => 0,
                '46-55' => 0
            ];

            // Set timezone to Asia/Makassar (WITA) for accurate age calculation
            $currentDate = Carbon::now('Asia/Makassar');

            $employees = Employee::whereNotNull('tanggal_lahir')
                ->where('tanggal_lahir', '!=', '')
                ->where('tanggal_lahir', '!=', '0000-00-00')
                ->when(Schema::hasColumn('employees', 'status'), function ($query) {
                    return $query->where('status', 'active');
                })
                ->get(['id', 'tanggal_lahir', 'nama_lengkap']);

            $totalProcessed = 0;
            $invalidDates = 0;
            $outsideRange = 0;

            Log::info('AGE CHART: Starting age calculation', [
                'total_employees_to_process' => $employees->count(),
                'current_date' => $currentDate->toDateTimeString(),
                'timezone' => 'Asia/Makassar'
            ]);

            foreach ($employees as $employee) {
                try {
                    // Parse birth date with proper timezone handling
                    $birthDate = Carbon::parse($employee->tanggal_lahir, 'Asia/Makassar');
                    
                    // Calculate age in years using diffInYears for more accurate calculation
                    $age = $birthDate->diffInYears($currentDate);
                    $totalProcessed++;
                    
                    // Categorize by age group
                    if ($age >= 18 && $age <= 25) {
                        $ageGroups['18-25']++;
                    } elseif ($age >= 26 && $age <= 35) {
                        $ageGroups['26-35']++;
                    } elseif ($age >= 36 && $age <= 45) {
                        $ageGroups['36-45']++;
                    } elseif ($age >= 46 && $age <= 55) {
                        $ageGroups['46-55']++;
                    } else {
                        $outsideRange++;
                    }
                    
                    // Log sample calculations for debugging
                    if ($totalProcessed <= 5) {
                        Log::debug('AGE CALCULATION: Sample employee processed', [
                            'employee_id' => $employee->id,
                            'employee_name' => $employee->nama_lengkap,
                            'birth_date' => $employee->tanggal_lahir,
                            'parsed_birth_date' => $birthDate->toDateString(),
                            'calculated_age' => $age,
                            'assigned_group' => $age >= 18 && $age <= 25 ? '18-25' : 
                                              ($age >= 26 && $age <= 35 ? '26-35' : 
                                              ($age >= 36 && $age <= 45 ? '36-45' : 
                                              ($age >= 46 && $age <= 55 ? '46-55' : 'outside_range')))
                        ]);
                    }
                    
                } catch (\Exception $e) {
                    $invalidDates++;
                    Log::warning('AGE CALCULATION: Invalid birth date', [
                        'employee_id' => $employee->id,
                        'birth_date' => $employee->tanggal_lahir,
                        'error' => $e->getMessage()
                    ]);
                    continue;
                }
            }

            // Convert to result format with proper sorting
            $result = collect($ageGroups)->map(function ($count, $group) {
                return [
                    'name' => $group . ' Tahun',
                    'value' => $count
                ];
            })->values()->toArray();

            // Sort by age group order (not by value to maintain age sequence)
            $ageOrder = ['18-25 Tahun', '26-35 Tahun', '36-45 Tahun', '46-55 Tahun'];
            usort($result, function($a, $b) use ($ageOrder) {
                $aIndex = array_search($a['name'], $ageOrder);
                $bIndex = array_search($b['name'], $ageOrder);
                return $aIndex - $bIndex;
            });

            Log::info('AGE CHART: Generated data successfully', [
                'total_employees_found' => $employees->count(),
                'total_employees_processed' => $totalProcessed,
                'invalid_dates' => $invalidDates,
                'outside_age_range' => $outsideRange,
                'age_groups_breakdown' => $ageGroups,
                'current_date' => $currentDate->toDateTimeString(),
                'timezone' => 'Asia/Makassar',
                'result_data' => $result
            ]);

            return $result;
        } catch (\Exception $e) {
            Log::error('Age Chart Data Error: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString()
            ]);
            
            // Return default structure to prevent frontend errors
            return [
                ['name' => '18-25 Tahun', 'value' => 0],
                ['name' => '26-35 Tahun', 'value' => 0],
                ['name' => '36-45 Tahun', 'value' => 0],
                ['name' => '46-55 Tahun', 'value' => 0]
            ];
        }
    }

    /**
     * NEW: Get jabatan chart data (Kelompok Jabatan) - REAL TIME
     * Chart Type: Bar Chart
     * Include: ACCOUNT EXECUTIVE/AE, EXECUTIVE GENERAL MANAGER, GENERAL MANAGER, MANAGER, STAFF, SUPERVISOR, NON
     */
    private function getJabatanChartData()
    {
        try {
            $targetJabatan = [
                'ACCOUNT EXECUTIVE/AE',
                'EXECUTIVE GENERAL MANAGER',
                'GENERAL MANAGER',
                'MANAGER',
                'NON',
                'STAFF',
                'SUPERVISOR'
            ];

            $query = Employee::select('kelompok_jabatan', DB::raw('count(*) as total'))
                ->whereNotNull('kelompok_jabatan')
                ->where('kelompok_jabatan', '!=', '')
                ->when(Schema::hasColumn('employees', 'status'), function ($query) {
                    return $query->where('status', 'active');
                })
                ->groupBy('kelompok_jabatan')
                ->get();

            $result = [];
            $processedJabatan = [];

            // Process existing data
            foreach ($query as $item) {
                $jabatanName = trim($item->kelompok_jabatan);
                
                // Map variations to standard names
                if (in_array($jabatanName, $targetJabatan) || $this->isJabatanVariation($jabatanName, $targetJabatan)) {
                    $standardName = $this->getStandardJabatanName($jabatanName, $targetJabatan);
                    
                    // Check if already processed
                    $existingIndex = array_search($standardName, array_column($result, 'name'));
                    if ($existingIndex !== false) {
                        $result[$existingIndex]['value'] += $item->total;
                    } else {
                        $result[] = [
                            'name' => $standardName,
                            'value' => $item->total
                        ];
                        $processedJabatan[] = $standardName;
                    }
                }
            }

            // Add missing jabatan with 0 count
            foreach ($targetJabatan as $jabatan) {
                if (!in_array($jabatan, $processedJabatan)) {
                    $result[] = [
                        'name' => $jabatan,
                        'value' => 0
                    ];
                }
            }

            // Sort by value descending to ensure proportional display
            usort($result, function($a, $b) {
                return $b['value'] - $a['value'];
            });

            Log::debug('JABATAN CHART: Generated data', [
                'total_jabatan' => count($result),
                'jabatan_with_data' => $query->count(),
                'result_data' => $result
            ]);

            return $result;
        } catch (\Exception $e) {
            Log::error('Jabatan Chart Data Error: ' . $e->getMessage());
            return [];
        }
    }

    // =====================================================
    // HELPER METHODS
    // =====================================================

    /**
     * NEW: Clean provider name for display
     */
    private function cleanProviderName($providerName)
    {
        $cleaned = trim($providerName);
        
        // Standardize common name variations
        $mappings = [
            'PT GAPURA ANGKASA' => 'PT Gapura Angkasa',
            'PT Gapura Angkasa' => 'PT Gapura Angkasa',
            'PT AIR BOX PERSONALIA' => 'PT Air Box Personalia',
            'PT Air Box Personalia' => 'PT Air Box Personalia',
            'PT FINFLEET TEKNOLOGI INDONESIA' => 'PT Finfleet Teknologi Indonesia',
            'PT Finfleet Teknologi Indonesia' => 'PT Finfleet Teknologi Indonesia',
            'PT MITRA ANGKASA PERDANA' => 'PT Mitra Angkasa Perdana',
            'PT Mitra Angkasa Perdana' => 'PT Mitra Angkasa Perdana',
            'PT MITRA ANGKASA' => 'PT Mitra Angkasa Perdana',
            'PT Mitra Angkasa' => 'PT Mitra Angkasa Perdana',
            'PT SAFARI DHARMA SAKTI' => 'PT Safari Dharma Sakti',
            'PT Safari Dharma Sakti' => 'PT Safari Dharma Sakti',
            'PT GRHA HUMANINDO MANAGEMENT' => 'PT Grha Humanindo Management',
            'PT Grha Humanindo Management' => 'PT Grha Humanindo Management',
            'PT DUTA GRIYA SARANA' => 'PT Duta Griya Sarana',
            'PT Duta Griya Sarana' => 'PT Duta Griya Sarana',
            'PT AEROTRANS WISATA' => 'PT Aerotrans Wisata',
            'PT Aerotrans Wisata' => 'PT Aerotrans Wisata',
            'PT MANDALA GARDA NUSANTARA' => 'PT Mandala Garda Nusantara',
            'PT Mandala Garda Nusantara' => 'PT Mandala Garda Nusantara',
            'PT KIDORA MANDIRI INVESTAMA' => 'PT Kidora Mandiri Investama',
            'PT Kidora Mandiri Investama' => 'PT Kidora Mandiri Investama',
            'PT IAS Support Indonesia' => 'PT IAS Support Indonesia'
        ];

        return $mappings[$cleaned] ?? $cleaned;
    }

    /**
     * NEW: Check if jabatan name is a variation of target jabatan
     */
    private function isJabatanVariation($jabatanName, $targetJabatan)
    {
        $variations = [
            'AE' => 'ACCOUNT EXECUTIVE/AE',
            'ACCOUNT EXECUTIVE' => 'ACCOUNT EXECUTIVE/AE',
            'EGM' => 'EXECUTIVE GENERAL MANAGER',
            'GM' => 'GENERAL MANAGER',
            'MANAGER' => 'MANAGER',
            'NON' => 'NON',
            'STAFF' => 'STAFF',
            'SUPERVISOR' => 'SUPERVISOR'
        ];

        return array_key_exists($jabatanName, $variations);
    }

    /**
     * NEW: Get standard jabatan name from variations
     */
    private function getStandardJabatanName($jabatanName, $targetJabatan)
    {
        $variations = [
            'AE' => 'ACCOUNT EXECUTIVE/AE',
            'ACCOUNT EXECUTIVE' => 'ACCOUNT EXECUTIVE/AE',
            'EGM' => 'EXECUTIVE GENERAL MANAGER',
            'GM' => 'GENERAL MANAGER',
            'MANAGER' => 'MANAGER',
            'NON' => 'NON',
            'STAFF' => 'STAFF',
            'SUPERVISOR' => 'SUPERVISOR'
        ];

        return $variations[$jabatanName] ?? $jabatanName;
    }

    /**
     * Export dashboard data
     */
    public function exportData(Request $request)
    {
        try {
            $format = $request->get('format', 'json');
            $statistics = $this->getStatisticsData();
            $organizationData = $this->getUnitChartData();
            
            switch ($format) {
                case 'csv':
                    return $this->exportToCsv($statistics, $organizationData);
                case 'pdf':
                    return $this->exportToPdf($statistics, $organizationData);
                default:
                    return response()->json([
                        'statistics' => $statistics,
                        'organization_data' => $organizationData,
                        'exported_at' => Carbon::now('Asia/Makassar')->toISOString(),
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
            $recentEmployees = Employee::where('created_at', '>=', Carbon::now('Asia/Makassar')->subDays(30))->count();
            
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
                    'organizational_structure' => $unitModelAvailable && $subUnitModelAvailable,
                    'history_3_periods_only' => true,
                    'kelompok_jabatan_extended' => true,
                    'six_charts_dashboard_realtime' => true
                ],
                'charts_available' => [
                    'gender_chart' => true,
                    'status_chart' => true,
                    'unit_chart' => true,
                    'provider_chart' => true,
                    'age_chart' => true,
                    'jabatan_chart' => true
                ],
                'history_methods' => [
                    'getEmployeeHistory' => method_exists($this, 'getEmployeeHistory'),
                    'getEmployeeHistorySummary' => method_exists($this, 'getEmployeeHistorySummary'),
                    'working' => $historyMethodsWorking,
                    'periods_supported' => ['today', 'this_week', 'total_period']
                ],
                'timestamp' => Carbon::now('Asia/Makassar')->toISOString(),
                'version' => '1.12.0',
                'timezone' => 'Asia/Makassar (WITA)',
                'dashboard_type' => '6_charts_realtime'
            ]);
        } catch (\Exception $e) {
            Log::error('Dashboard Health Check Error: ' . $e->getMessage());
            return response()->json([
                'status' => 'unhealthy',
                'error' => $e->getMessage(),
                'timestamp' => Carbon::now('Asia/Makassar')->toISOString(),
            ], 500);
        }
    }

    // =====================================================
    // FIXED HELPER METHODS - SAFE IMPLEMENTATIONS
    // =====================================================

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
     * FIXED: Build organizational structure for employee - SAFE VERSION
     */
    private function buildOrganizationalStructureSafe($employee)
    {
        try {
            $structure = [
                'unit_organisasi' => $employee->unit_organisasi ?? 'Tidak tersedia',
                'unit' => null,
                'sub_unit' => null,
                'full_structure' => null
            ];

            // Try to get unit info if model is available and unit_id exists
            if (class_exists('App\Models\Unit') && $employee->unit_id) {
                try {
                    $unit = Unit::find($employee->unit_id);
                    if ($unit) {
                        $structure['unit'] = [
                            'id' => $unit->id,
                            'name' => $unit->name,
                            'code' => $unit->code ?? null
                        ];
                    }
                } catch (\Exception $unitError) {
                    Log::debug('SAFE: Unit loading failed', [
                        'unit_id' => $employee->unit_id,
                        'error' => $unitError->getMessage()
                    ]);
                }
            }

            // Try to get sub unit info if model is available and sub_unit_id exists
            if (class_exists('App\Models\SubUnit') && $employee->sub_unit_id) {
                try {
                    $subUnit = SubUnit::find($employee->sub_unit_id);
                    if ($subUnit) {
                        $structure['sub_unit'] = [
                            'id' => $subUnit->id,
                            'name' => $subUnit->name,
                            'code' => $subUnit->code ?? null
                        ];
                    }
                } catch (\Exception $subUnitError) {
                    Log::debug('SAFE: SubUnit loading failed', [
                        'sub_unit_id' => $employee->sub_unit_id,
                        'error' => $subUnitError->getMessage()
                    ]);
                }
            }

            // Build full structure string for display
            $fullStructureParts = [];
            
            if ($structure['unit_organisasi'] && $structure['unit_organisasi'] !== 'Tidak tersedia') {
                $fullStructureParts[] = $structure['unit_organisasi'];
            }
            
            if ($structure['unit'] && !empty($structure['unit']['name'])) {
                $fullStructureParts[] = $structure['unit']['name'];
            }
            
            if ($structure['sub_unit'] && !empty($structure['sub_unit']['name'])) {
                $fullStructureParts[] = $structure['sub_unit']['name'];
            }
            
            // Create full structure string
            $structure['full_structure'] = implode(' > ', $fullStructureParts);
            
            // Ensure we always have at least unit_organisasi
            if (empty($structure['full_structure']) && $structure['unit_organisasi'] !== 'Tidak tersedia') {
                $structure['full_structure'] = $structure['unit_organisasi'];
            }
            
            // Final fallback
            if (empty($structure['full_structure'])) {
                $structure['full_structure'] = 'Struktur organisasi tidak tersedia';
            }

            Log::debug('SAFE: Organizational structure built', [
                'employee_id' => $employee->id,
                'full_structure' => $structure['full_structure'],
                'has_unit' => !is_null($structure['unit']),
                'has_sub_unit' => !is_null($structure['sub_unit'])
            ]);

            return $structure;

        } catch (\Exception $e) {
            Log::warning('SAFE: Organizational structure building error', [
                'employee_id' => $employee->id ?? 'unknown',
                'error' => $e->getMessage()
            ]);
            
            return [
                'unit_organisasi' => $employee->unit_organisasi ?? 'Tidak tersedia',
                'unit' => null,
                'sub_unit' => null,
                'full_structure' => $employee->unit_organisasi ?? 'Struktur organisasi tidak tersedia'
            ];
        }
    }

    /**
     * FIXED: Calculate history summary statistics - Only 3 periods (Today, This Week, 30 Days)
     * Fixed timezone and date calculation issues
     */
    private function calculateHistorySummarySafe($startDate, $endDate)
    {
        try {
            // Set timezone to Indonesia (WITA)
            $timezone = 'Asia/Makassar';
            
            // Get current time in Indonesia timezone
            $now = Carbon::now($timezone);
            $today = $now->copy()->startOfDay();
            $weekStart = $now->copy()->startOfWeek(); // Monday
            
            Log::debug('SUMMARY CALCULATION: Date ranges', [
                'timezone' => $timezone,
                'now' => $now->toISOString(),
                'today_start' => $today->toISOString(),
                'week_start' => $weekStart->toISOString(),
                'period_start' => $startDate->toISOString(),
                'period_end' => $endDate->toISOString()
            ]);

            // Calculate statistics with proper timezone handling
            $todayCount = Employee::where('created_at', '>=', $today)
                                 ->where('created_at', '<', $today->copy()->addDay())
                                 ->count();
            
            $thisWeekCount = Employee::where('created_at', '>=', $weekStart)
                                    ->where('created_at', '<=', $now->copy()->endOfDay())
                                    ->count();
            
            $totalPeriodCount = Employee::whereBetween('created_at', [$startDate, $endDate])
                                       ->count();

            Log::debug('SUMMARY CALCULATION: Raw counts', [
                'today_count' => $todayCount,
                'this_week_count' => $thisWeekCount,
                'total_period_count' => $totalPeriodCount
            ]);

            $summary = [
                'today' => $todayCount,
                'this_week' => $thisWeekCount, 
                'total_period' => $totalPeriodCount
            ];

            // Add growth calculation for 30 days period
            $previousPeriodStart = $startDate->copy()->subDays(30);
            $previousPeriodTotal = Employee::whereBetween('created_at', [$previousPeriodStart, $startDate])->count();
            
            if ($previousPeriodTotal > 0) {
                $summary['growth_percentage'] = round((($summary['total_period'] - $previousPeriodTotal) / $previousPeriodTotal) * 100, 1);
            } else {
                $summary['growth_percentage'] = $summary['total_period'] > 0 ? 100 : 0;
            }

            Log::info('SUMMARY CALCULATION: Final summary (3 periods only)', [
                'today' => $summary['today'],
                'this_week' => $summary['this_week'], 
                'total_period' => $summary['total_period'],
                'growth_percentage' => $summary['growth_percentage'],
                'timezone_used' => $timezone
            ]);

            return $summary;

        } catch (\Exception $e) {
            Log::error('SUMMARY CALCULATION: Error', [
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine()
            ]);
            
            return [
                'today' => 0,
                'this_week' => 0, 
                'total_period' => 0,
                'growth_percentage' => 0
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
                ->where('created_at', '>=', Carbon::now('Asia/Makassar')->subDays(30))
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
                ->where('updated_at', '>=', Carbon::now('Asia/Makassar')->subDays(7))
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
                    'this_week' => 0,
                    'total_period' => 0,
                    'growth_percentage' => 0
                ],
                'latest_employees' => []
            ];
        }
    }

    /**
     * Private method to get statistics data
     * UPDATED: Support new dashboard with proper TAD breakdown and PKWT
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
            
            // TAD Statistics dengan split - UPDATED for new dashboard
            $tadPaketSDM = Employee::where('status_pegawai', 'TAD PAKET SDM')->count();
            $tadPaketPekerjaan = Employee::where('status_pegawai', 'TAD PAKET PEKERJAAN')->count();
            $tadTotal = $tadPaketSDM + $tadPaketPekerjaan;
            
            // Backward compatibility - masih support TAD lama
            $tadLegacy = Employee::where('status_pegawai', 'TAD')->count();
            if ($tadLegacy > 0 && $tadTotal == 0) {
                $tadTotal = $tadLegacy;
                $tadPaketSDM = $tadLegacy; // Assign to SDM for compatibility
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

            // Additional stats
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
                $query->where('tmt_mulai_kerja', '>=', Carbon::now('Asia/Makassar')->subMonths(6))
                      ->orWhere('created_at', '>=', Carbon::now('Asia/Makassar')->subMonths(6));
            });
            
            if (Schema::hasColumn('employees', 'status')) {
                $recentHiresQuery->where('status', 'active');
            }
            
            $recentHires = $recentHiresQuery->count();

            return [
                // Basic statistics for new dashboard
                'total_employees' => $totalEmployees,
                'active_employees' => $activeEmployees,
                'pegawai_tetap' => $pegawaiTetap,
                'pkwt' => $pkwt,
                
                // TAD Statistics with breakdown - NEW DASHBOARD STRUCTURE
                'tad_total' => $tadTotal,
                'tad_paket_sdm' => $tadPaketSDM,
                'tad_paket_pekerjaan' => $tadPaketPekerjaan,
                
                // Backward compatibility
                'inactive_employees' => $totalEmployees - $activeEmployees,
                'tad' => $tadTotal,
                
                // Gender
                'male_employees' => $maleEmployees,
                'female_employees' => $femaleEmployees,
                
                // Additional stats
                'total_organizations' => $totalOrganizations,
                'recent_hires' => $recentHires,
                'growth_rate' => $this->calculateGrowthRate(),
            ];
        } catch (\Exception $e) {
            Log::error('Statistics Data Error: ' . $e->getMessage());
            return $this->getDefaultStatistics();
        }
    }

    /**
     * Calculate growth rate (month over month)
     */
    private function calculateGrowthRate()
    {
        try {
            $currentMonth = Employee::whereMonth('created_at', Carbon::now('Asia/Makassar')->month)
                ->whereYear('created_at', Carbon::now('Asia/Makassar')->year)
                ->count();
                
            $lastMonth = Employee::whereMonth('created_at', Carbon::now('Asia/Makassar')->subMonth()->month)
                ->whereYear('created_at', Carbon::now('Asia/Makassar')->subMonth()->year)
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
     * UPDATED: Get default statistics (fallback) - NEW DASHBOARD STRUCTURE
     */
    private function getDefaultStatistics()
    {
        return [
            'total_employees' => 0,
            'active_employees' => 0,
            'pegawai_tetap' => 0,
            'pkwt' => 0,
            'tad_total' => 0,
            'tad_paket_sdm' => 0,
            'tad_paket_pekerjaan' => 0,
            // Backward compatibility
            'inactive_employees' => 0,
            'tad' => 0,
            'male_employees' => 0,
            'female_employees' => 0,
            'total_organizations' => 0,
            'recent_hires' => 0,
            'growth_rate' => 0,
        ];
    }

    /**
     * Get default chart data (fallback) - NEW DASHBOARD STRUCTURE
     */
    private function getDefaultChartData()
    {
        return [
            'gender' => [],
            'status' => [],
            'unit' => [],
            'provider' => [],
            'age' => [],
            'jabatan' => []
        ];
    }

    /**
     * Export to CSV
     * UPDATED: Include new dashboard data structure
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
            fputcsv($file, ['Tanggal Laporan', Carbon::now('Asia/Makassar')->format('d/m/Y H:i:s') . ' WITA']);
            fputcsv($file, ['']);
            
            // Statistics
            fputcsv($file, ['STATISTIK UMUM']);
            fputcsv($file, ['Metrik', 'Nilai']);
            foreach ($statistics as $key => $value) {
                if (is_array($value)) {
                    continue; // Skip complex data for CSV
                }
                $label = ucfirst(str_replace('_', ' ', $key));
                fputcsv($file, [$label, $value]);
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
            'exported_at' => Carbon::now('Asia/Makassar')->toISOString(),
        ]);
    }
}