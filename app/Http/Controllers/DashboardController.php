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
     * Display dashboard index
     */
    public function index()
    {
        $statistics = $this->getStatistics();
        $chartData = $this->getChartData();
        
        return Inertia::render('Dashboard/Index', [
            'statistics' => $statistics,
            'chartData' => $chartData,
        ]);
    }

    /**
     * Get dashboard statistics
     */
    public function getStatistics()
    {
        try {
            $stats = [
                'total_employees' => Employee::count(),
                'active_employees' => Employee::where('status', 'active')->count(),
                'pegawai_tetap' => Employee::where('status_pegawai', 'PEGAWAI TETAP')->count(),
                'tad' => Employee::where('status_pegawai', 'TAD')->count(),
                'male_employees' => Employee::where('jenis_kelamin', 'L')->count(),
                'female_employees' => Employee::where('jenis_kelamin', 'P')->count(),
                'total_organizations' => Organization::count(),
            ];

            // Recent hires based on tmt_mulai_kerja (not hire_date)
            $recentHires = Employee::where('tmt_mulai_kerja', '>=', Carbon::now()->subMonths(6))
                                  ->where('status', 'active')
                                  ->count();

            // Upcoming retirement
            $upcomingRetirement = Employee::whereNotNull('tmt_pensiun')
                                        ->whereBetween('tmt_pensiun', [Carbon::now(), Carbon::now()->addMonths(12)])
                                        ->where('status', 'active')
                                        ->count();

            $stats['recent_hires'] = $recentHires;
            $stats['upcoming_retirement'] = $upcomingRetirement;

            return $stats;
        } catch (\Exception $e) {
            // Return default stats if error
            return [
                'total_employees' => 0,
                'active_employees' => 0,
                'pegawai_tetap' => 0,
                'tad' => 0,
                'male_employees' => 0,
                'female_employees' => 0,
                'total_organizations' => 0,
                'recent_hires' => 0,
                'upcoming_retirement' => 0,
            ];
        }
    }

    /**
     * Get chart data for dashboard
     */
    public function getChartData()
    {
        try {
            // Employees by organization
            $byOrganization = Employee::select('unit_organisasi', DB::raw('count(*) as total'))
                                    ->whereNotNull('unit_organisasi')
                                    ->where('status', 'active')
                                    ->groupBy('unit_organisasi')
                                    ->orderBy('total', 'desc')
                                    ->get();

            // Employees by status
            $byStatus = Employee::select('status_pegawai', DB::raw('count(*) as total'))
                              ->whereNotNull('status_pegawai')
                              ->where('status', 'active')
                              ->groupBy('status_pegawai')
                              ->get();

            // Monthly hire trend (using tmt_mulai_kerja)
            $monthlyHires = Employee::select(
                                DB::raw('YEAR(tmt_mulai_kerja) as year'),
                                DB::raw('MONTH(tmt_mulai_kerja) as month'),
                                DB::raw('count(*) as total')
                            )
                            ->whereNotNull('tmt_mulai_kerja')
                            ->where('tmt_mulai_kerja', '>=', Carbon::now()->subMonths(12))
                            ->where('status', 'active')
                            ->groupBy('year', 'month')
                            ->orderBy('year', 'asc')
                            ->orderBy('month', 'asc')
                            ->get();

            return [
                'by_organization' => $byOrganization,
                'by_status' => $byStatus,
                'monthly_hires' => $monthlyHires,
            ];
        } catch (\Exception $e) {
            return [
                'by_organization' => [],
                'by_status' => [],
                'monthly_hires' => [],
            ];
        }
    }

    /**
     * Get organizations for dashboard
     */
    public function organizations()
    {
        $organizations = Organization::withCount('employees')
                                   ->where('status', 'active')
                                   ->orderBy('name')
                                   ->get();

        return Inertia::render('Organizations/Index', [
            'organizations' => $organizations,
        ]);
    }

    /**
     * Get reports data
     */
    public function reports()
    {
        $reportData = [
            'employees_summary' => [
                'total' => Employee::count(),
                'active' => Employee::where('status', 'active')->count(),
                'by_unit' => Employee::select('unit_organisasi', DB::raw('count(*) as total'))
                                   ->whereNotNull('unit_organisasi')
                                   ->groupBy('unit_organisasi')
                                   ->get(),
            ],
            'organizations_summary' => [
                'total' => Organization::count(),
                'active' => Organization::where('status', 'active')->count(),
            ]
        ];

        return Inertia::render('Reports/Index', [
            'reportData' => $reportData,
        ]);
    }

    /**
     * API endpoint for statistics (for AJAX calls)
     */
    public function apiStatistics()
    {
        return response()->json($this->getStatistics());
    }

    /**
     * API endpoint for chart data (for AJAX calls)
     */
    public function apiChartData()
    {
        return response()->json($this->getChartData());
    }

    /**
     * Get employee distribution by age groups
     */
    public function getAgeDistribution()
    {
        try {
            $ageGroups = [
                '20-30' => Employee::whereBetween('usia', [20, 30])->where('status', 'active')->count(),
                '31-40' => Employee::whereBetween('usia', [31, 40])->where('status', 'active')->count(),
                '41-50' => Employee::whereBetween('usia', [41, 50])->where('status', 'active')->count(),
                '51-60' => Employee::whereBetween('usia', [51, 60])->where('status', 'active')->count(),
                '60+' => Employee::where('usia', '>', 60)->where('status', 'active')->count(),
            ];

            return response()->json($ageGroups);
        } catch (\Exception $e) {
            return response()->json([
                '20-30' => 0,
                '31-40' => 0,
                '41-50' => 0,
                '51-60' => 0,
                '60+' => 0,
            ]);
        }
    }

    /**
     * Get employee distribution by education level
     */
    public function getEducationDistribution()
    {
        try {
            $education = Employee::select('pendidikan', DB::raw('count(*) as total'))
                               ->whereNotNull('pendidikan')
                               ->where('status', 'active')
                               ->groupBy('pendidikan')
                               ->orderBy('total', 'desc')
                               ->get();

            return response()->json($education);
        } catch (\Exception $e) {
            return response()->json([]);
        }
    }

    /**
     * Get recent activities (new hires, promotions, etc)
     */
    public function getRecentActivities()
    {
        try {
            $activities = [];

            // Recent hires (using tmt_mulai_kerja)
            $recentHires = Employee::where('tmt_mulai_kerja', '>=', Carbon::now()->subDays(30))
                                 ->where('status', 'active')
                                 ->orderBy('tmt_mulai_kerja', 'desc')
                                 ->limit(10)
                                 ->get(['nama_lengkap', 'unit_organisasi', 'nama_jabatan', 'tmt_mulai_kerja']);

            foreach ($recentHires as $hire) {
                $activities[] = [
                    'type' => 'new_hire',
                    'title' => 'Karyawan Baru',
                    'description' => $hire->nama_lengkap . ' bergabung sebagai ' . $hire->nama_jabatan,
                    'date' => $hire->tmt_mulai_kerja,
                    'unit' => $hire->unit_organisasi,
                ];
            }

            // Sort by date
            usort($activities, function($a, $b) {
                return strtotime($b['date']) - strtotime($a['date']);
            });

            return response()->json(array_slice($activities, 0, 10));
        } catch (\Exception $e) {
            return response()->json([]);
        }
    }
}