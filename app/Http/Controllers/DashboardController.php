<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use App\Models\Organization;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    /**
     * Display dashboard index for GAPURA ANGKASA SDM System
     */
    public function index()
    {
        try {
            $statistics = $this->getStatisticsData();
            $chartData = $this->getChartData();
            $recentActivities = $this->getRecentActivities();
            
            return Inertia::render('Dashboard/Index', [
                'statistics' => $statistics,
                'chartData' => $chartData,
                'recentActivities' => $recentActivities,
                'success' => session('success'),
                'info' => session('info'),
            ]);
        } catch (\Exception $e) {
            return Inertia::render('Dashboard/Index', [
                'statistics' => $this->getDefaultStatistics(),
                'chartData' => $this->getDefaultChartData(),
                'recentActivities' => [],
                'error' => 'Error loading dashboard: ' . $e->getMessage(),
            ]);
        }
    }

    /**
     * Get dashboard statistics (API endpoint)
     */
    public function getStatistics()
    {
        try {
            $statistics = $this->getStatisticsData();
            return response()->json($statistics);
        } catch (\Exception $e) {
            return response()->json([
                'total_employees' => 0,
                'active_employees' => 0,
                'pegawai_tetap' => 0,
                'tad' => 0,
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get chart data for dashboard
     */
    public function getChartData()
    {
        try {
            $byOrganization = $this->getEmployeesByOrganization();
            $byStatus = $this->getEmployeesByStatus();
            $byGender = $this->getEmployeesByGender();
            $monthlyHires = $this->getMonthlyHires();
            $ageDistribution = $this->getAgeDistribution();

            return response()->json([
                'by_organization' => $byOrganization,
                'by_status' => $byStatus,
                'by_gender' => $byGender,
                'monthly_hires' => $monthlyHires,
                'age_distribution' => $ageDistribution,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'error' => $e->getMessage(),
                'by_organization' => [],
                'by_status' => [],
                'by_gender' => [],
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
            // Get recently added employees (last 30 days)
            $recentEmployees = Employee::select('nama_lengkap', 'jabatan', 'unit_organisasi', 'created_at')
                ->where('created_at', '>=', Carbon::now()->subDays(30))
                ->orderBy('created_at', 'desc')
                ->limit(10)
                ->get()
                ->map(function ($employee) {
                    return [
                        'type' => 'employee_added',
                        'title' => 'Karyawan Baru Ditambahkan',
                        'description' => $employee->nama_lengkap . ' - ' . $employee->jabatan,
                        'unit' => $employee->unit_organisasi,
                        'timestamp' => $employee->created_at,
                        'icon' => 'user-plus',
                        'color' => 'green',
                    ];
                });

            // Get recently updated employees (last 7 days)
            $updatedEmployees = Employee::select('nama_lengkap', 'jabatan', 'unit_organisasi', 'updated_at')
                ->where('updated_at', '>=', Carbon::now()->subDays(7))
                ->where('updated_at', '!=', DB::raw('created_at'))
                ->orderBy('updated_at', 'desc')
                ->limit(5)
                ->get()
                ->map(function ($employee) {
                    return [
                        'type' => 'employee_updated',
                        'title' => 'Data Karyawan Diperbarui',
                        'description' => $employee->nama_lengkap . ' - ' . $employee->jabatan,
                        'unit' => $employee->unit_organisasi,
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

            return response()->json([
                'activities' => $activities,
                'total' => $activities->count(),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'activities' => [],
                'total' => 0,
                'error' => $e->getMessage()
            ], 500);
        }
    }

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
            return response()->json([
                'error' => 'Export failed: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Health check for dashboard
     */
    public function healthCheck()
    {
        try {
            $dbConnection = DB::connection()->getPdo();
            $employeeCount = Employee::count();
            $organizationCount = Organization::count();
            
            return response()->json([
                'status' => 'healthy',
                'system' => 'GAPURA ANGKASA SDM System',
                'database' => 'connected',
                'employee_count' => $employeeCount,
                'organization_count' => $organizationCount,
                'timestamp' => now()->toISOString(),
                'version' => '1.0.0',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'unhealthy',
                'error' => $e->getMessage(),
                'timestamp' => now()->toISOString(),
            ], 500);
        }
    }

    /**
     * Private method to get statistics data
     */
    private function getStatisticsData()
    {
        // Base statistics
        $totalEmployees = Employee::count();
        $activeEmployees = Employee::where('status', 'active')->count();
        $pegawaiTetap = Employee::where('status_pegawai', 'PEGAWAI TETAP')->count();
        $tad = Employee::where('status_pegawai', 'TAD')->count();

        // Gender statistics - handle both L/P and Laki-laki/Perempuan formats
        $maleEmployees = Employee::where(function ($query) {
            $query->where('jenis_kelamin', 'L')
                  ->orWhere('jenis_kelamin', 'Laki-laki');
        })->count();
        
        $femaleEmployees = Employee::where(function ($query) {
            $query->where('jenis_kelamin', 'P')
                  ->orWhere('jenis_kelamin', 'Perempuan');
        })->count();
        
        // Organization count
        $totalOrganizations = Organization::count();
        if ($totalOrganizations == 0) {
            // Fallback: count unique units from employees
            $totalOrganizations = Employee::distinct('unit_organisasi')
                ->whereNotNull('unit_organisasi')
                ->count();
        }

        // Recent hires (last 6 months)
        $recentHires = Employee::where('tmt_mulai_kerja', '>=', Carbon::now()->subMonths(6))
                              ->orWhere('created_at', '>=', Carbon::now()->subMonths(6))
                              ->where('status', 'active')
                              ->count();

        // Upcoming retirement (next 12 months)
        $upcomingRetirement = Employee::whereNotNull('tmt_pensiun')
                                    ->whereBetween('tmt_pensiun', [Carbon::now(), Carbon::now()->addMonths(12)])
                                    ->where('status', 'active')
                                    ->count();

        return [
            'total_employees' => $totalEmployees,
            'active_employees' => $activeEmployees,
            'inactive_employees' => $totalEmployees - $activeEmployees,
            'pegawai_tetap' => $pegawaiTetap,
            'tad' => $tad,
            'male_employees' => $maleEmployees,
            'female_employees' => $femaleEmployees,
            'total_organizations' => $totalOrganizations,
            'recent_hires' => $recentHires,
            'upcoming_retirement' => $upcomingRetirement,
            'growth_rate' => $this->calculateGrowthRate(),
        ];
    }

    /**
     * Get employees by organization
     */
    private function getEmployeesByOrganization()
    {
        return Employee::select('unit_organisasi', DB::raw('count(*) as total'))
            ->whereNotNull('unit_organisasi')
            ->where('status', 'active')
            ->groupBy('unit_organisasi')
            ->orderBy('total', 'desc')
            ->get()
            ->map(function ($item) {
                return [
                    'name' => $item->unit_organisasi,
                    'value' => $item->total,
                    'percentage' => 0, // Will be calculated in frontend
                ];
            });
    }

    /**
     * Get employees by status
     */
    private function getEmployeesByStatus()
    {
        return Employee::select('status_pegawai', DB::raw('count(*) as total'))
            ->whereNotNull('status_pegawai')
            ->where('status', 'active')
            ->groupBy('status_pegawai')
            ->get()
            ->map(function ($item) {
                return [
                    'name' => $item->status_pegawai,
                    'value' => $item->total,
                    'color' => $item->status_pegawai === 'PEGAWAI TETAP' ? '#10B981' : '#F59E0B',
                ];
            });
    }

    /**
     * Get employees by gender
     */
    private function getEmployeesByGender()
    {
        // Handle both L/P and Laki-laki/Perempuan formats
        $maleCount = Employee::where(function ($query) {
            $query->where('jenis_kelamin', 'L')
                  ->orWhere('jenis_kelamin', 'Laki-laki');
        })->where('status', 'active')->count();
        
        $femaleCount = Employee::where(function ($query) {
            $query->where('jenis_kelamin', 'P')
                  ->orWhere('jenis_kelamin', 'Perempuan');
        })->where('status', 'active')->count();

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
    }

    /**
     * Get monthly hires for the last 12 months
     */
    private function getMonthlyHires()
    {
        $monthlyData = Employee::select(
                DB::raw('YEAR(COALESCE(tmt_mulai_kerja, created_at)) as year'),
                DB::raw('MONTH(COALESCE(tmt_mulai_kerja, created_at)) as month'),
                DB::raw('count(*) as total')
            )
            ->where(function ($query) {
                $query->where('tmt_mulai_kerja', '>=', now()->subMonths(12))
                      ->orWhere('created_at', '>=', now()->subMonths(12));
            })
            ->where('status', 'active')
            ->groupBy('year', 'month')
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
    }

    /**
     * Get age distribution
     */
    private function getAgeDistribution()
    {
        $employees = Employee::whereNotNull('tanggal_lahir')
                           ->where('status', 'active')
                           ->get();

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
            return 0;
        }
    }

    /**
     * Get default statistics (fallback)
     */
    private function getDefaultStatistics()
    {
        return [
            'total_employees' => 0,
            'active_employees' => 0,
            'inactive_employees' => 0,
            'pegawai_tetap' => 0,
            'tad' => 0,
            'male_employees' => 0,
            'female_employees' => 0,
            'total_organizations' => 0,
            'recent_hires' => 0,
            'upcoming_retirement' => 0,
            'growth_rate' => 0,
        ];
    }

    /**
     * Get default chart data (fallback)
     */
    private function getDefaultChartData()
    {
        return [
            'by_organization' => [],
            'by_status' => [],
            'by_gender' => [],
            'monthly_hires' => [],
            'age_distribution' => [],
        ];
    }

    /**
     * Export to CSV
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
            'exported_at' => now()->toISOString(),
        ]);
    }
}