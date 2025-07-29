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
        $organizationUnits = Organization::count();
        $organizations = Organization::with('employees')->get()->map(function($org) {
            return [
                'id' => $org->id,
                'name' => $org->name,
                'employee_count' => $org->employees->count()
            ];
        });

        $dashboardData = [
            'total_employees' => $totalEmployees,
            'active_employees' => $activeEmployees,
            'active_percentage' => $totalEmployees > 0 ? round(($activeEmployees / $totalEmployees) * 100, 1) : 0,
            'organization_units' => $organizationUnits,
            'organizations' => $organizations,
            'current_role' => 'Super Admin',
            'user_profile' => [
                'name' => 'GusDek',
                'position' => 'Jabatan',
                'email' => 'admin@gapura.com'
            ]
        ];

        return Inertia::render('Dashboard/SuperAdmin', $dashboardData);
    }

    public function employees()
    {
        return Inertia::render('Dashboard/Employees');
    }

    public function organizations()
    {
        return Inertia::render('Dashboard/Organizations');
    }

    public function reports()
    {
        return Inertia::render('Dashboard/Reports');
    }

    public function settings()
    {
        return Inertia::render('Dashboard/Settings');
    }
}