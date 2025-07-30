<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use App\Models\Organization;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Validation\Rule;

class EmployeeController extends Controller
{
    /**
     * Display a listing of employees
     */
    public function index(Request $request)
    {
        $query = Employee::with('organization')
                         ->active();

        // Search functionality
        if ($request->filled('search')) {
            $query->search($request->search);
        }

        // Filter by status pegawai
        if ($request->filled('status_pegawai') && $request->status_pegawai !== 'all') {
            $query->byStatusPegawai($request->status_pegawai);
        }

        // Filter by unit organisasi
        if ($request->filled('unit_organisasi') && $request->unit_organisasi !== 'all') {
            $query->byUnitOrganisasi($request->unit_organisasi);
        }

        // Sorting
        $sortField = $request->get('sort', 'nama_lengkap');
        $sortDirection = $request->get('direction', 'asc');
        
        $validSortFields = ['nama_lengkap', 'nip', 'status_pegawai', 'tmt_mulai_jabatan', 'unit_organisasi'];
        if (in_array($sortField, $validSortFields)) {
            $query->orderBy($sortField, $sortDirection);
        }

        // Pagination
        $employees = $query->paginate(10)->withQueryString();

        // Get filter options
        $statusOptions = Employee::select('status_pegawai')
                                ->distinct()
                                ->whereNotNull('status_pegawai')
                                ->orderBy('status_pegawai')
                                ->pluck('status_pegawai');

        $unitOptions = Employee::select('unit_organisasi')
                              ->distinct()
                              ->whereNotNull('unit_organisasi')
                              ->orderBy('unit_organisasi')
                              ->pluck('unit_organisasi');

        return Inertia::render('Employees/Index', [
            'employees' => $employees,
            'filters' => $request->only(['search', 'status_pegawai', 'unit_organisasi']),
            'statusOptions' => $statusOptions,
            'unitOptions' => $unitOptions,
            'statistics' => Employee::getStatistics(),
        ]);
    }

    /**
     * Show the form for creating a new employee
     */
    public function create()
    {
        $organizations = Organization::active()->orderBy('name')->get();
        
        return Inertia::render('Employees/Create', [
            'organizations' => $organizations,
        ]);
    }

    /**
     * Store a newly created employee
     */
    public function store(Request $request)
    {
        $request->validate([
            'nip' => [
                'required',
                'string',
                'max:20',
                Rule::unique('employees', 'nip')
            ],
            'nama_lengkap' => 'required|string|max:255',
            'status_pegawai' => 'required|in:PEGAWAI TETAP,TAD',
            'unit_organisasi' => 'required|string|max:100',
            'nama_jabatan' => 'required|string|max:255',
            'jenis_kelamin' => 'required|in:L,P',
            'tmt_mulai_jabatan' => 'required|date',
            'handphone' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:255',
            'jenis_sepatu' => 'nullable|in:Pantofel,Safety Shoes',
            'ukuran_sepatu' => 'nullable|string|max:10',
            'tempat_lahir' => 'nullable|string|max:100',
            'tanggal_lahir' => 'nullable|date',
            'kota_domisili' => 'nullable|string|max:100',
            'alamat' => 'nullable|string',
            'pendidikan' => 'nullable|string|max:50',
            'instansi_pendidikan' => 'nullable|string|max:255',
            'jurusan' => 'nullable|string|max:100',
            'tahun_lulus' => 'nullable|integer|min:1950|max:' . (date('Y') + 5),
            'organization_id' => 'nullable|exists:organizations,id',
        ]);

        // Calculate age if birth date is provided
        $data = $request->all();
        if ($request->tanggal_lahir) {
            $data['usia'] = \Carbon\Carbon::parse($request->tanggal_lahir)->age;
        }

        Employee::create($data);

        return redirect()->route('employees.index')
                        ->with('success', 'Data karyawan berhasil ditambahkan.');
    }

    /**
     * Display the specified employee
     */
    public function show(Employee $employee)
    {
        $employee->load('organization');
        
        return Inertia::render('Employees/Show', [
            'employee' => $employee,
        ]);
    }

    /**
     * Show the form for editing the specified employee
     */
    public function edit(Employee $employee)
    {
        $organizations = Organization::active()->orderBy('name')->get();
        
        return Inertia::render('Employees/Edit', [
            'employee' => $employee,
            'organizations' => $organizations,
        ]);
    }

    /**
     * Update the specified employee
     */
    public function update(Request $request, Employee $employee)
    {
        $request->validate([
            'nip' => [
                'required',
                'string',
                'max:20',
                Rule::unique('employees', 'nip')->ignore($employee->id)
            ],
            'nama_lengkap' => 'required|string|max:255',
            'status_pegawai' => 'required|in:PEGAWAI TETAP,TAD',
            'unit_organisasi' => 'required|string|max:100',
            'nama_jabatan' => 'required|string|max:255',
            'jenis_kelamin' => 'required|in:L,P',
            'tmt_mulai_jabatan' => 'required|date',
            'handphone' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:255',
            'jenis_sepatu' => 'nullable|in:Pantofel,Safety Shoes',
            'ukuran_sepatu' => 'nullable|string|max:10',
            'tempat_lahir' => 'nullable|string|max:100',
            'tanggal_lahir' => 'nullable|date',
            'kota_domisili' => 'nullable|string|max:100',
            'alamat' => 'nullable|string',
            'pendidikan' => 'nullable|string|max:50',
            'instansi_pendidikan' => 'nullable|string|max:255',
            'jurusan' => 'nullable|string|max:100',
            'tahun_lulus' => 'nullable|integer|min:1950|max:' . (date('Y') + 5),
            'organization_id' => 'nullable|exists:organizations,id',
        ]);

        // Calculate age if birth date is provided
        $data = $request->all();
        if ($request->tanggal_lahir) {
            $data['usia'] = \Carbon\Carbon::parse($request->tanggal_lahir)->age;
        }

        $employee->update($data);

        return redirect()->route('employees.index')
                        ->with('success', 'Data karyawan berhasil diperbarui.');
    }

    /**
     * Remove the specified employee
     */
    public function destroy(Employee $employee)
    {
        // Soft delete by setting status to inactive
        $employee->update(['status' => 'inactive']);

        return redirect()->route('employees.index')
                        ->with('success', 'Data karyawan berhasil dihapus.');
    }

    /**
     * Get employees data for dashboard
     */
    public function getDashboardData()
    {
        $statistics = Employee::getStatistics();
        $byUnit = Employee::getByUnitOrganisasi();
        $upcomingRetirement = Employee::getUpcomingRetirement(6);

        return response()->json([
            'statistics' => $statistics,
            'by_unit' => $byUnit,
            'upcoming_retirement' => $upcomingRetirement,
        ]);
    }

    /**
     * Export employees data
     */
    public function export(Request $request)
    {
        $query = Employee::with('organization')->active();

        // Apply same filters as index
        if ($request->filled('search')) {
            $query->search($request->search);
        }

        if ($request->filled('status_pegawai') && $request->status_pegawai !== 'all') {
            $query->byStatusPegawai($request->status_pegawai);
        }

        if ($request->filled('unit_organisasi') && $request->unit_organisasi !== 'all') {
            $query->byUnitOrganisasi($request->unit_organisasi);
        }

        $employees = $query->orderBy('nama_lengkap')->get();

        // Return CSV response
        $filename = 'data_karyawan_' . date('Y-m-d_H-i-s') . '.csv';
        
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ];

        $callback = function() use ($employees) {
            $file = fopen('php://output', 'w');
            
            // CSV Headers
            fputcsv($file, [
                'NIP', 'Nama Lengkap', 'Status Pegawai', 'Unit Organisasi', 
                'Nama Jabatan', 'TMT Mulai Jabatan', 'Jenis Kelamin', 
                'Handphone', 'Email', 'Pendidikan', 'Jurusan'
            ]);

            // CSV Data
            foreach ($employees as $employee) {
                fputcsv($file, [
                    $employee->nip,
                    $employee->nama_lengkap,
                    $employee->status_pegawai,
                    $employee->unit_organisasi,
                    $employee->nama_jabatan,
                    $employee->tmt_mulai_jabatan ? $employee->tmt_mulai_jabatan->format('d/m/Y') : '',
                    $employee->jenis_kelamin_lengkap,
                    $employee->handphone,
                    $employee->email_default,
                    $employee->pendidikan,
                    $employee->jurusan,
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * Import employees from CSV/Excel
     */
    public function import(Request $request)
    {
        $request->validate([
            'file' => 'required|file|mimes:csv,txt,xlsx|max:10240', // 10MB max
        ]);

        // Handle file import logic here
        // This would involve parsing the uploaded file and creating Employee records

        return redirect()->route('employees.index')
                        ->with('success', 'Data karyawan berhasil diimpor.');
    }

    /**
     * Get employee suggestions for autocomplete
     */
    public function suggestions(Request $request)
    {
        $term = $request->get('term');
        
        if (strlen($term) < 2) {
            return response()->json([]);
        }

        $employees = Employee::search($term)
                            ->active()
                            ->limit(10)
                            ->get(['id', 'nip', 'nama_lengkap', 'unit_organisasi']);

        return response()->json($employees);
    }

    /**
     * Bulk actions for employees
     */
    public function bulkAction(Request $request)
    {
        $request->validate([
            'action' => 'required|in:activate,deactivate,delete',
            'employee_ids' => 'required|array',
            'employee_ids.*' => 'exists:employees,id',
        ]);

        $employees = Employee::whereIn('id', $request->employee_ids);

        switch ($request->action) {
            case 'activate':
                $employees->update(['status' => 'active']);
                $message = 'Karyawan berhasil diaktifkan.';
                break;
            case 'deactivate':
                $employees->update(['status' => 'inactive']);
                $message = 'Karyawan berhasil dinonaktifkan.';
                break;
            case 'delete':
                $employees->update(['status' => 'inactive']);
                $message = 'Karyawan berhasil dihapus.';
                break;
        }

        return redirect()->route('employees.index')
                        ->with('success', $message);
    }
}