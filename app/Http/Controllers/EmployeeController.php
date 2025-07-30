<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use App\Models\Organization;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class EmployeeController extends Controller
{
    /**
     * Display a listing of employees with search and filter capabilities
     */
    public function index(Request $request)
    {
        try {
            // Build query with safe method calls
            $query = Employee::query();

            // Add relationship if method exists
            if (method_exists(Employee::class, 'organization')) {
                $query->with('organization');
            }

            // Apply active scope if method exists, otherwise filter manually
            if (method_exists(Employee::class, 'scopeActive')) {
                $query->active();
            } else {
                $query->where('status', 'active');
            }

            // Search functionality
            if ($request->filled('search')) {
                $searchTerm = $request->search;
                $query->where(function($q) use ($searchTerm) {
                    $q->where('nip', 'like', "%{$searchTerm}%")
                      ->orWhere('nama_lengkap', 'like', "%{$searchTerm}%")
                      ->orWhere('nama_jabatan', 'like', "%{$searchTerm}%")
                      ->orWhere('unit_organisasi', 'like', "%{$searchTerm}%");
                });
            }

            // Filter by status pegawai
            if ($request->filled('status_pegawai') && $request->status_pegawai !== 'all') {
                $query->where('status_pegawai', $request->status_pegawai);
            }

            // Filter by unit organisasi
            if ($request->filled('unit_organisasi') && $request->unit_organisasi !== 'all') {
                $query->where('unit_organisasi', $request->unit_organisasi);
            }

            // Get paginated results
            $employees = $query->orderBy('nama_lengkap')
                              ->paginate(20)
                              ->withQueryString();

            // Get organizations safely
            $organizations = [];
            try {
                if (method_exists(Organization::class, 'scopeActive')) {
                    $organizations = Organization::active()->orderBy('name')->get();
                } else {
                    $organizations = Organization::where('status', 'active')->orderBy('name')->get();
                }
            } catch (\Exception $e) {
                // Continue with empty organizations if there's an error
                $organizations = [];
            }

            return Inertia::render('Employees/Index', [
                'employees' => $employees->items(), // Get only items for Inertia
                'organizations' => $organizations,
                'filters' => $request->only(['search', 'status_pegawai', 'unit_organisasi']),
                'pagination' => [
                    'current_page' => $employees->currentPage(),
                    'last_page' => $employees->lastPage(),
                    'per_page' => $employees->perPage(),
                    'total' => $employees->total(),
                    'from' => $employees->firstItem(),
                    'to' => $employees->lastItem(),
                ],
            ]);

        } catch (\Exception $e) {
            // Fallback response if there are any errors
            return Inertia::render('Employees/Index', [
                'employees' => [],
                'organizations' => [],
                'filters' => $request->only(['search', 'status_pegawai', 'unit_organisasi']),
                'pagination' => [
                    'current_page' => 1,
                    'last_page' => 1,
                    'per_page' => 20,
                    'total' => 0,
                    'from' => 0,
                    'to' => 0,
                ],
                'error' => 'Error loading employees: ' . $e->getMessage(),
            ]);
        }
    }

    /**
     * Show the form for creating a new employee
     */
    public function create()
    {
        try {
            if (method_exists(Organization::class, 'scopeActive')) {
                $organizations = Organization::active()->orderBy('name')->get();
            } else {
                $organizations = Organization::where('status', 'active')->orderBy('name')->get();
            }
        } catch (\Exception $e) {
            $organizations = [];
        }
        
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
            $data['usia'] = Carbon::parse($request->tanggal_lahir)->age;
        }

        // Set default values
        $data['status_kerja'] = 'Aktif';
        $data['provider'] = 'PT Gapura Angkasa';
        $data['lokasi_kerja'] = 'Bandar Udara Ngurah Rai';
        $data['cabang'] = 'DPS';
        $data['status'] = 'active';

        Employee::create($data);

        return redirect()->route('employees.index')
                        ->with('success', 'Data karyawan berhasil ditambahkan.');
    }

    /**
     * Display the specified employee
     */
    public function show(Employee $employee)
    {
        try {
            if (method_exists($employee, 'load') && method_exists($employee, 'organization')) {
                $employee->load('organization');
            }
        } catch (\Exception $e) {
            // Continue without loading relationship if there's an error
        }
        
        return Inertia::render('Employees/Show', [
            'employee' => $employee,
        ]);
    }

    /**
     * Show the form for editing the specified employee
     */
    public function edit(Employee $employee)
    {
        try {
            if (method_exists(Organization::class, 'scopeActive')) {
                $organizations = Organization::active()->orderBy('name')->get();
            } else {
                $organizations = Organization::where('status', 'active')->orderBy('name')->get();
            }
        } catch (\Exception $e) {
            $organizations = [];
        }
        
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
            $data['usia'] = Carbon::parse($request->tanggal_lahir)->age;
        }

        $employee->update($data);

        return redirect()->route('employees.index')
                        ->with('success', 'Data karyawan berhasil diperbarui.');
    }

    /**
     * Remove the specified employee (soft delete)
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
        try {
            // Safe method calls with fallbacks
            $statistics = [];
            $byUnit = [];
            $upcomingRetirement = [];

            if (method_exists(Employee::class, 'getStatistics')) {
                $statistics = Employee::getStatistics();
            } else {
                $statistics = [
                    'total' => Employee::count(),
                    'active' => Employee::where('status', 'active')->count(),
                    'pegawai_tetap' => Employee::where('status_pegawai', 'PEGAWAI TETAP')->count(),
                    'tad' => Employee::where('status_pegawai', 'TAD')->count(),
                ];
            }

            if (method_exists(Employee::class, 'getByUnitOrganisasi')) {
                $byUnit = Employee::getByUnitOrganisasi();
            } else {
                $byUnit = Employee::select('unit_organisasi', DB::raw('count(*) as total'))
                                ->whereNotNull('unit_organisasi')
                                ->where('status', 'active')
                                ->groupBy('unit_organisasi')
                                ->orderBy('total', 'desc')
                                ->get();
            }

            if (method_exists(Employee::class, 'getUpcomingRetirement')) {
                $upcomingRetirement = Employee::getUpcomingRetirement(6);
            } else {
                $upcomingRetirement = Employee::whereNotNull('tmt_pensiun')
                                            ->whereBetween('tmt_pensiun', [Carbon::now(), Carbon::now()->addMonths(6)])
                                            ->where('status', 'active')
                                            ->orderBy('tmt_pensiun', 'asc')
                                            ->limit(10)
                                            ->get();
            }

            return response()->json([
                'statistics' => $statistics,
                'by_unit' => $byUnit,
                'upcoming_retirement' => $upcomingRetirement,
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'statistics' => ['total' => 0, 'active' => 0],
                'by_unit' => [],
                'upcoming_retirement' => [],
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Export employees data to CSV
     */
    public function export(Request $request)
    {
        try {
            $query = Employee::query();

            // Add relationship if method exists
            if (method_exists(Employee::class, 'organization')) {
                $query->with('organization');
            }

            // Apply active scope if method exists
            if (method_exists(Employee::class, 'scopeActive')) {
                $query->active();
            } else {
                $query->where('status', 'active');
            }

            // Apply same filters as index
            if ($request->filled('search')) {
                $searchTerm = $request->search;
                $query->where(function($q) use ($searchTerm) {
                    $q->where('nip', 'like', "%{$searchTerm}%")
                      ->orWhere('nama_lengkap', 'like', "%{$searchTerm}%")
                      ->orWhere('nama_jabatan', 'like', "%{$searchTerm}%")
                      ->orWhere('unit_organisasi', 'like', "%{$searchTerm}%");
                });
            }

            if ($request->filled('status_pegawai') && $request->status_pegawai !== 'all') {
                $query->where('status_pegawai', $request->status_pegawai);
            }

            if ($request->filled('unit_organisasi') && $request->unit_organisasi !== 'all') {
                $query->where('unit_organisasi', $request->unit_organisasi);
            }

            $employees = $query->orderBy('nama_lengkap')->get();

            // Return CSV response
            $filename = 'data_karyawan_gapura_angkasa_' . date('Y-m-d_H-i-s') . '.csv';
            
            $headers = [
                'Content-Type' => 'text/csv; charset=UTF-8',
                'Content-Disposition' => 'attachment; filename="' . $filename . '"',
                'Cache-Control' => 'no-cache, no-store, must-revalidate',
                'Pragma' => 'no-cache',
                'Expires' => '0',
            ];

            $callback = function() use ($employees) {
                $file = fopen('php://output', 'w');
                
                // Add BOM for proper UTF-8 encoding in Excel
                fprintf($file, chr(0xEF).chr(0xBB).chr(0xBF));
                
                // CSV Headers
                fputcsv($file, [
                    'NIP', 'Nama Lengkap', 'Status Pegawai', 'Unit Organisasi', 
                    'Nama Jabatan', 'TMT Mulai Jabatan', 'Jenis Kelamin', 
                    'Handphone', 'Email', 'Pendidikan', 'Jurusan', 'Alamat',
                    'Tempat Lahir', 'Tanggal Lahir', 'Usia', 'Status Kerja'
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
                        $employee->jenis_kelamin === 'L' ? 'Laki-laki' : 'Perempuan',
                        $employee->handphone,
                        $employee->email ?? '-',
                        $employee->pendidikan,
                        $employee->jurusan,
                        $employee->alamat,
                        $employee->tempat_lahir,
                        $employee->tanggal_lahir ? $employee->tanggal_lahir->format('d/m/Y') : '',
                        $employee->usia,
                        $employee->status_kerja,
                    ]);
                }

                fclose($file);
            };

            return response()->stream($callback, 200, $headers);

        } catch (\Exception $e) {
            return redirect()->route('employees.index')
                            ->with('error', 'Gagal export data: ' . $e->getMessage());
        }
    }

    /**
     * Import employees from CSV/Excel
     */
    public function import(Request $request)
    {
        $request->validate([
            'file' => 'required|file|mimes:csv,txt,xlsx|max:10240', // 10MB max
        ]);

        try {
            // Handle file upload and processing
            $file = $request->file('file');
            $path = $file->getRealPath();
            
            if ($file->getClientOriginalExtension() === 'csv') {
                $this->processCsvImport($path);
            } else {
                $this->processExcelImport($path);
            }

            return redirect()->route('employees.index')
                            ->with('success', 'Data karyawan berhasil diimpor.');
        } catch (\Exception $e) {
            return redirect()->route('employees.index')
                            ->with('error', 'Gagal mengimpor data: ' . $e->getMessage());
        }
    }

    /**
     * Process CSV import
     */
    private function processCsvImport($filePath)
    {
        $csvData = array_map('str_getcsv', file($filePath));
        $header = array_shift($csvData);
        
        DB::beginTransaction();
        
        try {
            foreach ($csvData as $row) {
                if (count($row) < count($header)) continue;
                
                $data = array_combine($header, $row);
                
                // Skip if NIP already exists
                if (Employee::where('nip', $data['NIP'])->exists()) {
                    continue;
                }
                
                // Process and create employee record
                $this->createEmployeeFromImport($data);
            }
            
            DB::commit();
        } catch (\Exception $e) {
            DB::rollback();
            throw $e;
        }
    }

    /**
     * Create employee from import data
     */
    private function createEmployeeFromImport($data)
    {
        $employeeData = [
            'nip' => $data['NIP'] ?? null,
            'nama_lengkap' => $data['NAMA LENGKAP'] ?? null,
            'status_pegawai' => $data['STATUS PEGAWAI'] ?? 'PEGAWAI TETAP',
            'unit_organisasi' => $data['UNIT ORGANISASI'] ?? null,
            'nama_jabatan' => $data['NAMA JABATAN'] ?? null,
            'jenis_kelamin' => $data['JENIS KELAMIN'] ?? 'L',
            'handphone' => $data['HANDPHONE'] ?? null,
            'tempat_lahir' => $data['TEMPAT LAHIR'] ?? null,
            'alamat' => $data['ALAMAT'] ?? null,
            'pendidikan' => $data['PENDIDIKAN'] ?? null,
            'jurusan' => $data['JURUSAN'] ?? null,
            'provider' => 'PT Gapura Angkasa',
            'lokasi_kerja' => 'Bandar Udara Ngurah Rai',
            'cabang' => 'DPS',
            'status_kerja' => 'Aktif',
            'status' => 'active',
        ];

        // Parse dates
        if (!empty($data['TMT MULAI JABATAN'])) {
            try {
                $employeeData['tmt_mulai_jabatan'] = Carbon::createFromFormat('d/m/Y', $data['TMT MULAI JABATAN']);
            } catch (\Exception $e) {
                $employeeData['tmt_mulai_jabatan'] = null;
            }
        }

        if (!empty($data['TANGGAL LAHIR'])) {
            try {
                $employeeData['tanggal_lahir'] = Carbon::createFromFormat('d/m/Y', $data['TANGGAL LAHIR']);
                $employeeData['usia'] = $employeeData['tanggal_lahir']->age;
            } catch (\Exception $e) {
                $employeeData['tanggal_lahir'] = null;
            }
        }

        // Find organization safely
        if (!empty($data['UNIT ORGANISASI'])) {
            try {
                $organization = Organization::where('name', 'like', '%' . $data['UNIT ORGANISASI'] . '%')->first();
                if ($organization) {
                    $employeeData['organization_id'] = $organization->id;
                }
            } catch (\Exception $e) {
                // Continue without organization_id if there's an error
            }
        }

        Employee::create($employeeData);
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

        try {
            $query = Employee::where(function($q) use ($term) {
                        $q->where('nip', 'like', "%{$term}%")
                          ->orWhere('nama_lengkap', 'like', "%{$term}%");
                    });

            if (method_exists(Employee::class, 'scopeActive')) {
                $query->active();
            } else {
                $query->where('status', 'active');
            }

            $employees = $query->limit(10)
                             ->get(['id', 'nip', 'nama_lengkap', 'unit_organisasi']);

            return response()->json($employees);
        } catch (\Exception $e) {
            return response()->json([]);
        }
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

    /**
     * Get statistics for dashboard
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
            ];

            $unitStats = Employee::select('unit_organisasi', DB::raw('count(*) as total'))
                                ->whereNotNull('unit_organisasi')
                                ->where('status', 'active')
                                ->groupBy('unit_organisasi')
                                ->orderBy('total', 'desc')
                                ->get();

            $recentHires = Employee::where('tmt_mulai_kerja', '>=', Carbon::now()->subMonths(6))
                                  ->where('status', 'active')
                                  ->orderBy('tmt_mulai_kerja', 'desc')
                                  ->limit(10)
                                  ->get();

            $upcomingRetirement = Employee::whereNotNull('tmt_pensiun')
                                        ->whereBetween('tmt_pensiun', [Carbon::now(), Carbon::now()->addMonths(12)])
                                        ->where('status', 'active')
                                        ->orderBy('tmt_pensiun', 'asc')
                                        ->limit(10)
                                        ->get();

            return response()->json([
                'statistics' => $stats,
                'unit_statistics' => $unitStats,
                'recent_hires' => $recentHires,
                'upcoming_retirement' => $upcomingRetirement,
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'statistics' => [
                    'total_employees' => 0,
                    'active_employees' => 0,
                    'pegawai_tetap' => 0,
                    'tad' => 0,
                    'male_employees' => 0,
                    'female_employees' => 0,
                ],
                'unit_statistics' => [],
                'recent_hires' => [],
                'upcoming_retirement' => [],
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Search employees with advanced filters
     */
    public function search(Request $request)
    {
        try {
            $query = Employee::query();

            if (method_exists(Employee::class, 'organization')) {
                $query->with('organization');
            }

            if (method_exists(Employee::class, 'scopeActive')) {
                $query->active();
            } else {
                $query->where('status', 'active');
            }

            // Basic search
            if ($request->filled('q')) {
                $searchTerm = $request->q;
                $query->where(function($q) use ($searchTerm) {
                    $q->where('nip', 'like', "%{$searchTerm}%")
                      ->orWhere('nama_lengkap', 'like', "%{$searchTerm}%")
                      ->orWhere('nama_jabatan', 'like', "%{$searchTerm}%")
                      ->orWhere('unit_organisasi', 'like', "%{$searchTerm}%");
                });
            }

            // Advanced filters
            if ($request->filled('status_pegawai')) {
                $query->where('status_pegawai', $request->status_pegawai);
            }

            if ($request->filled('unit_organisasi')) {
                $query->where('unit_organisasi', $request->unit_organisasi);
            }

            if ($request->filled('jenis_kelamin')) {
                $query->where('jenis_kelamin', $request->jenis_kelamin);
            }

            if ($request->filled('pendidikan')) {
                $query->where('pendidikan', $request->pendidikan);
            }

            if ($request->filled('age_range')) {
                $ageRange = explode('-', $request->age_range);
                if (count($ageRange) === 2) {
                    $query->whereBetween('usia', [$ageRange[0], $ageRange[1]]);
                }
            }

            // Date range filters
            if ($request->filled('hire_date_from')) {
                $query->where('tmt_mulai_kerja', '>=', $request->hire_date_from);
            }

            if ($request->filled('hire_date_to')) {
                $query->where('tmt_mulai_kerja', '<=', $request->hire_date_to);
            }

            $employees = $query->orderBy('nama_lengkap')
                              ->paginate(20)
                              ->withQueryString();

            return response()->json([
                'employees' => $employees->items(),
                'pagination' => [
                    'current_page' => $employees->currentPage(),
                    'last_page' => $employees->lastPage(),
                    'per_page' => $employees->perPage(),
                    'total' => $employees->total(),
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'employees' => [],
                'pagination' => [
                    'current_page' => 1,
                    'last_page' => 1,
                    'per_page' => 20,
                    'total' => 0,
                ],
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Get employee profile data
     */
    public function profile(Employee $employee)
    {
        try {
            if (method_exists($employee, 'load') && method_exists($employee, 'organization')) {
                $employee->load('organization');
            }
            
            // Calculate work duration
            $workDuration = null;
            if ($employee->tmt_mulai_kerja) {
                $workDuration = Carbon::parse($employee->tmt_mulai_kerja)->diffForHumans(null, true);
            }

            // Calculate years until retirement
            $yearsToRetirement = null;
            if ($employee->tmt_pensiun) {
                $yearsToRetirement = Carbon::now()->diffInYears(Carbon::parse($employee->tmt_pensiun));
            }

            return response()->json([
                'employee' => $employee,
                'work_duration' => $workDuration,
                'years_to_retirement' => $yearsToRetirement,
                'is_near_retirement' => $yearsToRetirement && $yearsToRetirement <= 2,
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'employee' => $employee,
                'work_duration' => null,
                'years_to_retirement' => null,
                'is_near_retirement' => false,
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Generate employee ID card data
     */
    public function generateIdCard(Employee $employee)
    {
        try {
            if (method_exists($employee, 'load') && method_exists($employee, 'organization')) {
                $employee->load('organization');
            }
            
            $idCardData = [
                'nip' => $employee->nip,
                'nama_lengkap' => $employee->nama_lengkap,
                'unit_organisasi' => $employee->unit_organisasi,
                'nama_jabatan' => $employee->nama_jabatan,
                'foto' => null, // Placeholder for photo
                'qr_code' => base64_encode($employee->nip), // Simple QR code data
                'issued_date' => now()->format('d/m/Y'),
                'valid_until' => now()->addYears(2)->format('d/m/Y'),
            ];

            return response()->json($idCardData);

        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Failed to generate ID card: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Generate reports
     */
    public function generateReport(Request $request)
    {
        $request->validate([
            'type' => 'required|in:summary,detailed,by_unit,retirement',
            'format' => 'required|in:pdf,excel,csv',
        ]);

        try {
            switch ($request->type) {
                case 'summary':
                    return $this->generateSummaryReport($request->format);
                case 'detailed':
                    return $this->generateDetailedReport($request->format);
                case 'by_unit':
                    return $this->generateByUnitReport($request->format);
                case 'retirement':
                    return $this->generateRetirementReport($request->format);
                default:
                    return response()->json(['error' => 'Invalid report type'], 400);
            }
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Failed to generate report: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Generate summary report
     */
    private function generateSummaryReport($format)
    {
        try {
            // Get statistics safely
            $stats = [
                'total' => Employee::count(),
                'active' => Employee::where('status', 'active')->count(),
                'pegawai_tetap' => Employee::where('status_pegawai', 'PEGAWAI TETAP')->count(),
                'tad' => Employee::where('status_pegawai', 'TAD')->count(),
                'laki_laki' => Employee::where('jenis_kelamin', 'L')->count(),
                'perempuan' => Employee::where('jenis_kelamin', 'P')->count(),
            ];

            $unitStats = Employee::select('unit_organisasi', DB::raw('count(*) as total'))
                               ->whereNotNull('unit_organisasi')
                               ->where('status', 'active')
                               ->groupBy('unit_organisasi')
                               ->orderBy('total', 'desc')
                               ->get();
            
            if ($format === 'csv') {
                $filename = 'laporan_ringkasan_karyawan_' . date('Y-m-d') . '.csv';
                
                $headers = [
                    'Content-Type' => 'text/csv; charset=UTF-8',
                    'Content-Disposition' => 'attachment; filename="' . $filename . '"',
                ];

                $callback = function() use ($stats, $unitStats) {
                    $file = fopen('php://output', 'w');
                    fprintf($file, chr(0xEF).chr(0xBB).chr(0xBF));
                    
                    fputcsv($file, ['LAPORAN RINGKASAN KARYAWAN PT GAPURA ANGKASA']);
                    fputcsv($file, ['Tanggal Laporan', date('d/m/Y H:i:s')]);
                    fputcsv($file, []);
                    
                    fputcsv($file, ['STATISTIK UMUM']);
                    fputcsv($file, ['Total Karyawan', $stats['total']]);
                    fputcsv($file, ['Karyawan Aktif', $stats['active']]);
                    fputcsv($file, ['Pegawai Tetap', $stats['pegawai_tetap']]);
                    fputcsv($file, ['TAD', $stats['tad']]);
                    fputcsv($file, ['Laki-laki', $stats['laki_laki']]);
                    fputcsv($file, ['Perempuan', $stats['perempuan']]);
                    
                    fputcsv($file, []);
                    fputcsv($file, ['DISTRIBUSI PER UNIT ORGANISASI']);
                    fputcsv($file, ['Unit Organisasi', 'Jumlah Karyawan']);
                    
                    foreach ($unitStats as $unit) {
                        fputcsv($file, [$unit->unit_organisasi, $unit->total]);
                    }
                    
                    fclose($file);
                };

                return response()->stream($callback, 200, $headers);
            }
            
            // Default JSON response for other formats
            return response()->json([
                'statistics' => $stats,
                'unit_statistics' => $unitStats,
                'generated_at' => now()->format('Y-m-d H:i:s'),
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Failed to generate summary report: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Generate detailed report
     */
    private function generateDetailedReport($format)
    {
        try {
            $employees = Employee::where('status', 'active')
                               ->orderBy('nama_lengkap')
                               ->get();

            if ($format === 'csv') {
                $filename = 'laporan_detail_karyawan_' . date('Y-m-d') . '.csv';
                
                $headers = [
                    'Content-Type' => 'text/csv; charset=UTF-8',
                    'Content-Disposition' => 'attachment; filename="' . $filename . '"',
                ];

                $callback = function() use ($employees) {
                    $file = fopen('php://output', 'w');
                    fprintf($file, chr(0xEF).chr(0xBB).chr(0xBF));
                    
                    // Headers
                    fputcsv($file, [
                        'NIP', 'Nama Lengkap', 'Status Pegawai', 'Unit Organisasi',
                        'Nama Jabatan', 'TMT Mulai Jabatan', 'Jenis Kelamin',
                        'Handphone', 'Email', 'Pendidikan', 'Jurusan'
                    ]);

                    // Data
                    foreach ($employees as $employee) {
                        fputcsv($file, [
                            $employee->nip,
                            $employee->nama_lengkap,
                            $employee->status_pegawai,
                            $employee->unit_organisasi,
                            $employee->nama_jabatan,
                            $employee->tmt_mulai_jabatan ? $employee->tmt_mulai_jabatan->format('d/m/Y') : '',
                            $employee->jenis_kelamin === 'L' ? 'Laki-laki' : 'Perempuan',
                            $employee->handphone,
                            $employee->email ?? '-',
                            $employee->pendidikan,
                            $employee->jurusan,
                        ]);
                    }
                    
                    fclose($file);
                };

                return response()->stream($callback, 200, $headers);
            }

            return response()->json([
                'employees' => $employees,
                'total' => $employees->count(),
                'generated_at' => now()->format('Y-m-d H:i:s'),
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Failed to generate detailed report: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Generate by unit report
     */
    private function generateByUnitReport($format)
    {
        try {
            $unitStats = Employee::select('unit_organisasi', DB::raw('count(*) as total'))
                               ->whereNotNull('unit_organisasi')
                               ->where('status', 'active')
                               ->groupBy('unit_organisasi')
                               ->orderBy('total', 'desc')
                               ->get();

            if ($format === 'csv') {
                $filename = 'laporan_per_unit_' . date('Y-m-d') . '.csv';
                
                $headers = [
                    'Content-Type' => 'text/csv; charset=UTF-8',
                    'Content-Disposition' => 'attachment; filename="' . $filename . '"',
                ];

                $callback = function() use ($unitStats) {
                    $file = fopen('php://output', 'w');
                    fprintf($file, chr(0xEF).chr(0xBB).chr(0xBF));
                    
                    fputcsv($file, ['LAPORAN KARYAWAN PER UNIT ORGANISASI']);
                    fputcsv($file, ['Tanggal Laporan', date('d/m/Y H:i:s')]);
                    fputcsv($file, []);
                    fputcsv($file, ['Unit Organisasi', 'Jumlah Karyawan']);
                    
                    foreach ($unitStats as $unit) {
                        fputcsv($file, [$unit->unit_organisasi, $unit->total]);
                    }
                    
                    fclose($file);
                };

                return response()->stream($callback, 200, $headers);
            }

            return response()->json([
                'unit_statistics' => $unitStats,
                'generated_at' => now()->format('Y-m-d H:i:s'),
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Failed to generate by unit report: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Generate retirement report
     */
    private function generateRetirementReport($format)
    {
        try {
            $upcomingRetirement = Employee::whereNotNull('tmt_pensiun')
                                        ->whereBetween('tmt_pensiun', [Carbon::now(), Carbon::now()->addMonths(12)])
                                        ->where('status', 'active')
                                        ->orderBy('tmt_pensiun', 'asc')
                                        ->get();

            if ($format === 'csv') {
                $filename = 'laporan_pensiun_' . date('Y-m-d') . '.csv';
                
                $headers = [
                    'Content-Type' => 'text/csv; charset=UTF-8',
                    'Content-Disposition' => 'attachment; filename="' . $filename . '"',
                ];

                $callback = function() use ($upcomingRetirement) {
                    $file = fopen('php://output', 'w');
                    fprintf($file, chr(0xEF).chr(0xBB).chr(0xBF));
                    
                    fputcsv($file, ['LAPORAN KARYAWAN YANG AKAN PENSIUN']);
                    fputcsv($file, ['Periode', '12 Bulan Ke Depan']);
                    fputcsv($file, ['Tanggal Laporan', date('d/m/Y H:i:s')]);
                    fputcsv($file, []);
                    fputcsv($file, ['NIP', 'Nama Lengkap', 'Unit Organisasi', 'Nama Jabatan', 'TMT Pensiun']);
                    
                    foreach ($upcomingRetirement as $employee) {
                        fputcsv($file, [
                            $employee->nip,
                            $employee->nama_lengkap,
                            $employee->unit_organisasi,
                            $employee->nama_jabatan,
                            $employee->tmt_pensiun ? $employee->tmt_pensiun->format('d/m/Y') : ''
                        ]);
                    }
                    
                    fclose($file);
                };

                return response()->stream($callback, 200, $headers);
            }

            return response()->json([
                'upcoming_retirement' => $upcomingRetirement,
                'total' => $upcomingRetirement->count(),
                'generated_at' => now()->format('Y-m-d H:i:s'),
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Failed to generate retirement report: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Validate employee data
     */
    public function validateData(Request $request)
    {
        try {
            $errors = [];
            
            // Check for duplicate NIPs
            $duplicateNips = Employee::select('nip', DB::raw('count(*) as count'))
                                   ->groupBy('nip')
                                   ->having('count', '>', 1)
                                   ->get();
            
            if ($duplicateNips->count() > 0) {
                $errors[] = [
                    'type' => 'duplicate_nip',
                    'message' => 'Ditemukan NIP duplikat',
                    'data' => $duplicateNips
                ];
            }
            
            // Check for missing required fields
            $missingData = Employee::whereNull('nama_lengkap')
                                 ->orWhereNull('unit_organisasi')
                                 ->orWhereNull('nama_jabatan')
                                 ->get();
            
            if ($missingData->count() > 0) {
                $errors[] = [
                    'type' => 'missing_required',
                    'message' => 'Ditemukan data wajib yang kosong',
                    'data' => $missingData
                ];
            }
            
            // Check for invalid phone numbers
            $invalidPhones = Employee::whereNotNull('handphone')
                                   ->where('handphone', 'not like', '+62%')
                                   ->where('handphone', '!=', '')
                                   ->get();
            
            if ($invalidPhones->count() > 0) {
                $errors[] = [
                    'type' => 'invalid_phone',
                    'message' => 'Ditemukan format nomor telepon yang tidak valid',
                    'data' => $invalidPhones
                ];
            }

            return response()->json([
                'is_valid' => empty($errors),
                'errors' => $errors,
                'total_errors' => count($errors),
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'is_valid' => false,
                'errors' => [
                    [
                        'type' => 'system_error',
                        'message' => 'Error validating data: ' . $e->getMessage(),
                        'data' => []
                    ]
                ],
                'total_errors' => 1,
            ]);
        }
    }
}