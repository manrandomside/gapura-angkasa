<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Models\Employee;
use App\Models\Organization;

class DashboardController extends Controller
{
    public function index()
    {
        // Data untuk dashboard Super Admin
        $totalEmployees = Employee::count();
        $activeEmployees = Employee::where('status', 'active')->count();
        $organizationUnits = Organization::where('status', 'active')->count();
        
        // Get organizations with employee count
        $organizations = Organization::with('employees')
            ->where('status', 'active')
            ->get()
            ->map(function($org) {
                return [
                    'id' => $org->id,
                    'name' => $org->name,
                    'code' => $org->code,
                    'employee_count' => $org->employees->count(),
                    'active_employee_count' => $org->employees->where('status', 'active')->count()
                ];
            });

        // Recent activities (simulasi data)
        $recentActivities = [
            [
                'type' => 'employee_added',
                'description' => 'Karyawan baru Ahmad Rizki telah ditambahkan',
                'time' => '2 jam yang lalu',
                'icon' => 'user-plus',
                'color' => 'green'
            ],
            [
                'type' => 'report_generated',
                'description' => 'Laporan bulanan Januari 2025 telah di-generate',
                'time' => '5 jam yang lalu',
                'icon' => 'document',
                'color' => 'blue'
            ],
            [
                'type' => 'organization_updated',
                'description' => 'Unit organisasi Divisi IT telah diperbarui',
                'time' => '1 hari yang lalu',
                'icon' => 'building',
                'color' => 'purple'
            ],
            [
                'type' => 'data_imported',
                'description' => 'Import data karyawan dari Excel berhasil 25 records',
                'time' => '2 hari yang lalu',
                'icon' => 'upload',
                'color' => 'orange'
            ]
        ];

        $dashboardData = [
            'total_employees' => $totalEmployees,
            'active_employees' => $activeEmployees,
            'active_percentage' => $totalEmployees > 0 ? round(($activeEmployees / $totalEmployees) * 100, 1) : 0,
            'organization_units' => $organizationUnits,
            'organizations' => $organizations,
            'recent_activities' => $recentActivities,
            'current_role' => 'Super Admin',
            'user_profile' => [
                'name' => 'GusDek',
                'position' => 'Jabatan',
                'email' => 'admin@gapura.com'
            ],
            // Statistics for cards
            'statistics' => [
                'new_employees_this_month' => Employee::whereMonth('hire_date', now()->month)
                    ->whereYear('hire_date', now()->year)
                    ->count(),
                'departments_with_full_access' => $organizationUnits,
                'data_completeness' => 100, // Placeholder
                'system_uptime' => '99.9%' // Placeholder
            ]
        ];

        return Inertia::render('Dashboard/SuperAdmin', $dashboardData);
    }

    public function employees()
    {
        $employees = Employee::with('organization')
            ->latest()
            ->paginate(50);

        return Inertia::render('Dashboard/Employees', [
            'employees' => $employees
        ]);
    }

    public function organizations()
    {
        $organizations = Organization::with('employees')
            ->where('status', 'active')
            ->get();

        return Inertia::render('Dashboard/Organizations', [
            'organizations' => $organizations
        ]);
    }

    public function reports()
    {
        return Inertia::render('Dashboard/Reports');
    }

    public function settings()
    {
        return Inertia::render('Dashboard/Settings');
    }

    // API endpoints for AJAX requests
    public function getStats()
    {
        return response()->json([
            'total_employees' => Employee::count(),
            'active_employees' => Employee::where('status', 'active')->count(),
            'organization_units' => Organization::where('status', 'active')->count(),
            'recent_hires' => Employee::where('hire_date', '>=', now()->subDays(30))->count()
        ]);
    }

    public function getOrganizationStats()
    {
        $stats = Organization::with('employees')
            ->where('status', 'active')
            ->get()
            ->map(function($org) {
                return [
                    'name' => $org->name,
                    'total' => $org->employees->count(),
                    'active' => $org->employees->where('status', 'active')->count()
                ];
            });

        return response()->json($stats);
    }
}