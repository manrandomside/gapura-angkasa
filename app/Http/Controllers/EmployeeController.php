<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use App\Models\Organization;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class EmployeeController extends Controller
{
    /**
     * Display a listing of employees with enhanced search and filter capabilities
     */
    public function index(Request $request)
    {
        try {
            // Build query with relationships
            $query = Employee::query();

            // Load organization relationship if it exists
            if (method_exists(Employee::class, 'organization')) {
                $query->with('organization');
            }

            // Apply active scope - default to active employees
            $query->where('status', 'active');

            // Apply search filters
            if ($request->filled('search')) {
                $searchTerm = $request->search;
                $query->where(function($q) use ($searchTerm) {
                    $q->where('nip', 'like', "%{$searchTerm}%")
                      ->orWhere('nama_lengkap', 'like', "%{$searchTerm}%")
                      ->orWhere('jabatan', 'like', "%{$searchTerm}%")
                      ->orWhere('nama_jabatan', 'like', "%{$searchTerm}%")
                      ->orWhere('unit_organisasi', 'like', "%{$searchTerm}%")
                      ->orWhere('jenis_sepatu', 'like', "%{$searchTerm}%")
                      ->orWhere('ukuran_sepatu', 'like', "%{$searchTerm}%");
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

            // Filter by gender
            if ($request->filled('jenis_kelamin') && $request->jenis_kelamin !== 'all') {
                $query->where('jenis_kelamin', $request->jenis_kelamin);
            }

            // Filter by jenis sepatu
            if ($request->filled('jenis_sepatu') && $request->jenis_sepatu !== 'all') {
                $query->where('jenis_sepatu', $request->jenis_sepatu);
            }

            // Filter by ukuran sepatu
            if ($request->filled('ukuran_sepatu') && $request->ukuran_sepatu !== 'all') {
                $query->where('ukuran_sepatu', $request->ukuran_sepatu);
            }

            // Get results - convert pagination to collection for Inertia compatibility
            $employees = $query->orderBy('nama_lengkap', 'asc')->get();

            // Get organizations for filter dropdown
            $organizations = $this->getOrganizationsForFilter();

            return Inertia::render('Employees/Index', [
                'employees' => $employees,
                'organizations' => $organizations,
                'filters' => $request->only(['search', 'status_pegawai', 'unit_organisasi', 'jenis_kelamin', 'jenis_sepatu', 'ukuran_sepatu']),
                'success' => session('success'),
                'error' => session('error'),
                'info' => session('info'),
            ]);

        } catch (\Exception $e) {
            return Inertia::render('Employees/Index', [
                'employees' => [],
                'organizations' => [],
                'filters' => $request->only(['search', 'status_pegawai', 'unit_organisasi', 'jenis_kelamin', 'jenis_sepatu', 'ukuran_sepatu']),
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
            $organizations = $this->getOrganizationsForFilter();
            
            return Inertia::render('Employees/Create', [
                'organizations' => $organizations,
                'unitOptions' => $this->getUnitOptions(),
                'jabatanOptions' => $this->getJabatanOptions(),
            ]);
        } catch (\Exception $e) {
            return redirect()->route('employees.index')
                ->with('error', 'Error loading create form: ' . $e->getMessage());
        }
    }

    /**
     * Store a newly created employee with comprehensive validation
     */
    public function store(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'nip' => [
                    'required',
                    'string',
                    'max:20',
                    Rule::unique('employees', 'nip')
                ],
                'nama_lengkap' => 'required|string|max:255',
                'jenis_kelamin' => 'required|in:Laki-laki,Perempuan,L,P',
                'tempat_lahir' => 'nullable|string|max:100',
                'tanggal_lahir' => 'nullable|date|before:today',
                'alamat' => 'nullable|string|max:500',
                'no_telepon' => 'nullable|string|max:20',
                'handphone' => 'nullable|string|max:20',
                'email' => 'nullable|email|max:255|unique:employees,email',
                'unit_organisasi' => 'required|string|max:100',
                'jabatan' => 'nullable|string|max:255',
                'nama_jabatan' => 'required|string|max:255',
                'status_pegawai' => 'required|in:PEGAWAI TETAP,TAD',
                'tmt_mulai_jabatan' => 'nullable|date',
                'tmt_mulai_kerja' => 'nullable|date',
                'tmt_pensiun' => 'nullable|date|after:today',
                'pendidikan_terakhir' => 'nullable|string|max:50',
                'pendidikan' => 'nullable|string|max:50',
                'instansi_pendidikan' => 'nullable|string|max:255',
                'jurusan' => 'nullable|string|max:100',
                'tahun_lulus' => 'nullable|integer|min:1950|max:' . (date('Y') + 5),
                'jenis_sepatu' => 'nullable|in:Pantofel,Safety Shoes',
                'ukuran_sepatu' => 'nullable|string|max:10',
                'kota_domisili' => 'nullable|string|max:100',
                'organization_id' => 'nullable|exists:organizations,id',
                'no_bpjs_kesehatan' => 'nullable|string|max:50',
                'no_bpjs_ketenagakerjaan' => 'nullable|string|max:50',
                'height' => 'nullable|numeric|between:100,250',
                'weight' => 'nullable|numeric|between:30,200',
            ]);

            if ($validator->fails()) {
                return redirect()->back()
                    ->withErrors($validator)
                    ->withInput();
            }

            $data = $validator->validated();
            
            // Normalize gender field
            if (in_array($data['jenis_kelamin'], ['L', 'Laki-laki'])) {
                $data['jenis_kelamin'] = 'Laki-laki';
            } else {
                $data['jenis_kelamin'] = 'Perempuan';
            }

            // Calculate age if birth date is provided
            if (isset($data['tanggal_lahir'])) {
                $data['usia'] = Carbon::parse($data['tanggal_lahir'])->age;
            }

            // Set default values for GAPURA ANGKASA
            $data['status'] = 'active';
            $data['status_kerja'] = 'Aktif';
            $data['provider'] = 'PT Gapura Angkasa';
            $data['lokasi_kerja'] = 'Bandar Udara Ngurah Rai';
            $data['cabang'] = 'DPS';

            // Handle jabatan field consistency
            if (!isset($data['jabatan']) && isset($data['nama_jabatan'])) {
                $data['jabatan'] = $data['nama_jabatan'];
            }

            Employee::create($data);

            return redirect()->route('employees.index')
                ->with('success', 'Data karyawan berhasil ditambahkan.');

        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Error creating employee: ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Display the specified employee
     */
    public function show(Employee $employee)
    {
        try {
            // Load organization relationship if it exists
            if (method_exists($employee, 'organization')) {
                $employee->load('organization');
            }
            
            return Inertia::render('Employees/Show', [
                'employee' => $employee,
            ]);
        } catch (\Exception $e) {
            return redirect()->route('employees.index')
                ->with('error', 'Error loading employee details: ' . $e->getMessage());
        }
    }

    /**
     * Show the form for editing the specified employee
     */
    public function edit(Employee $employee)
    {
        try {
            $organizations = $this->getOrganizationsForFilter();
            
            return Inertia::render('Employees/Edit', [
                'employee' => $employee,
                'organizations' => $organizations,
                'unitOptions' => $this->getUnitOptions(),
                'jabatanOptions' => $this->getJabatanOptions(),
            ]);
        } catch (\Exception $e) {
            return redirect()->route('employees.index')
                ->with('error', 'Error loading edit form: ' . $e->getMessage());
        }
    }

    /**
     * Update the specified employee
     */
    public function update(Request $request, Employee $employee)
    {
        try {
            $validator = Validator::make($request->all(), [
                'nip' => [
                    'required',
                    'string',
                    'max:20',
                    Rule::unique('employees', 'nip')->ignore($employee->id)
                ],
                'nama_lengkap' => 'required|string|max:255',
                'jenis_kelamin' => 'required|in:Laki-laki,Perempuan,L,P',
                'tempat_lahir' => 'nullable|string|max:100',
                'tanggal_lahir' => 'nullable|date|before:today',
                'alamat' => 'nullable|string|max:500',
                'no_telepon' => 'nullable|string|max:20',
                'handphone' => 'nullable|string|max:20',
                'email' => 'nullable|email|max:255|unique:employees,email,' . $employee->id,
                'unit_organisasi' => 'required|string|max:100',
                'jabatan' => 'nullable|string|max:255',
                'nama_jabatan' => 'required|string|max:255',
                'status_pegawai' => 'required|in:PEGAWAI TETAP,TAD',
                'tmt_mulai_jabatan' => 'nullable|date',
                'tmt_mulai_kerja' => 'nullable|date',
                'tmt_pensiun' => 'nullable|date|after:today',
                'pendidikan_terakhir' => 'nullable|string|max:50',
                'pendidikan' => 'nullable|string|max:50',
                'instansi_pendidikan' => 'nullable|string|max:255',
                'jurusan' => 'nullable|string|max:100',
                'tahun_lulus' => 'nullable|integer|min:1950|max:' . (date('Y') + 5),
                'jenis_sepatu' => 'nullable|in:Pantofel,Safety Shoes',
                'ukuran_sepatu' => 'nullable|string|max:10',
                'kota_domisili' => 'nullable|string|max:100',
                'organization_id' => 'nullable|exists:organizations,id',
                'no_bpjs_kesehatan' => 'nullable|string|max:50',
                'no_bpjs_ketenagakerjaan' => 'nullable|string|max:50',
                'height' => 'nullable|numeric|between:100,250',
                'weight' => 'nullable|numeric|between:30,200',
            ]);

            if ($validator->fails()) {
                return redirect()->back()
                    ->withErrors($validator)
                    ->withInput();
            }

            $data = $validator->validated();
            
            // Normalize gender field
            if (in_array($data['jenis_kelamin'], ['L', 'Laki-laki'])) {
                $data['jenis_kelamin'] = 'Laki-laki';
            } else {
                $data['jenis_kelamin'] = 'Perempuan';
            }

            // Recalculate age if birth date is updated
            if (isset($data['tanggal_lahir'])) {
                $data['usia'] = Carbon::parse($data['tanggal_lahir'])->age;
            }

            // Handle jabatan field consistency
            if (!isset($data['jabatan']) && isset($data['nama_jabatan'])) {
                $data['jabatan'] = $data['nama_jabatan'];
            }

            $employee->update($data);

            return redirect()->route('employees.index')
                ->with('success', 'Data karyawan berhasil diperbarui.');

        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Error updating employee: ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Remove the specified employee (soft delete)
     */
    public function destroy(Employee $employee)
    {
        try {
            $employeeName = $employee->nama_lengkap;
            
            // Soft delete by setting status to inactive
            $employee->update(['status' => 'inactive']);

            return redirect()->route('employees.index')
                ->with('success', "Karyawan {$employeeName} berhasil dihapus.");
        } catch (\Exception $e) {
            return redirect()->route('employees.index')
                ->with('error', 'Error deleting employee: ' . $e->getMessage());
        }
    }

    /**
     * Search employees (API endpoint) - Enhanced with shoe data
     */
    public function search(Request $request)
    {
        try {
            $query = $request->get('q', '');
            $limit = $request->get('limit', 10);
            
            $employees = Employee::where('status', 'active')
                ->where(function($q) use ($query) {
                    $q->where('nama_lengkap', 'like', "%{$query}%")
                      ->orWhere('nip', 'like', "%{$query}%")
                      ->orWhere('jabatan', 'like', "%{$query}%")
                      ->orWhere('nama_jabatan', 'like', "%{$query}%")
                      ->orWhere('jenis_sepatu', 'like', "%{$query}%")
                      ->orWhere('ukuran_sepatu', 'like', "%{$query}%");
                })
                ->limit($limit)
                ->get(['id', 'nip', 'nama_lengkap', 'jabatan', 'nama_jabatan', 'unit_organisasi', 'jenis_sepatu', 'ukuran_sepatu']);

            return response()->json([
                'employees' => $employees,
                'total' => $employees->count(),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'employees' => [],
                'total' => 0,
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get employee statistics (API endpoint) - Enhanced with shoe statistics
     */
    public function getStatistics()
    {
        try {
            $statistics = [
                'total_employees' => Employee::count(),
                'active_employees' => Employee::where('status', 'active')->count(),
                'inactive_employees' => Employee::where('status', 'inactive')->count(),
                'pegawai_tetap' => Employee::where('status_pegawai', 'PEGAWAI TETAP')->count(),
                'tad' => Employee::where('status_pegawai', 'TAD')->count(),
                'male_employees' => Employee::whereIn('jenis_kelamin', ['L', 'Laki-laki'])->count(),
                'female_employees' => Employee::whereIn('jenis_kelamin', ['P', 'Perempuan'])->count(),
                'shoe_statistics' => [
                    'pantofel' => Employee::where('jenis_sepatu', 'Pantofel')->count(),
                    'safety_shoes' => Employee::where('jenis_sepatu', 'Safety Shoes')->count(),
                    'no_shoe_data' => Employee::whereNull('jenis_sepatu')->count(),
                    'size_distribution' => $this->getShoesSizeDistribution(),
                ],
                'by_organization' => $this->getEmployeesByOrganization(),
                'by_education' => $this->getEmployeesByEducation(),
                'recent_hires' => $this->getRecentHires(),
                'upcoming_retirement' => $this->getUpcomingRetirement(),
            ];

            return response()->json($statistics);
        } catch (\Exception $e) {
            return response()->json([
                'error' => $e->getMessage(),
                'total_employees' => 0,
                'active_employees' => 0,
            ], 500);
        }
    }

    /**
     * Export employees data to CSV - Enhanced with shoe data
     */
    public function export(Request $request)
    {
        try {
            $query = Employee::where('status', 'active');

            // Apply same filters as index
            if ($request->filled('search')) {
                $searchTerm = $request->search;
                $query->where(function($q) use ($searchTerm) {
                    $q->where('nip', 'like', "%{$searchTerm}%")
                      ->orWhere('nama_lengkap', 'like', "%{$searchTerm}%")
                      ->orWhere('jabatan', 'like', "%{$searchTerm}%")
                      ->orWhere('nama_jabatan', 'like', "%{$searchTerm}%")
                      ->orWhere('unit_organisasi', 'like', "%{$searchTerm}%")
                      ->orWhere('jenis_sepatu', 'like', "%{$searchTerm}%")
                      ->orWhere('ukuran_sepatu', 'like', "%{$searchTerm}%");
                });
            }

            if ($request->filled('status_pegawai') && $request->status_pegawai !== 'all') {
                $query->where('status_pegawai', $request->status_pegawai);
            }

            if ($request->filled('unit_organisasi') && $request->unit_organisasi !== 'all') {
                $query->where('unit_organisasi', $request->unit_organisasi);
            }

            if ($request->filled('jenis_sepatu') && $request->jenis_sepatu !== 'all') {
                $query->where('jenis_sepatu', $request->jenis_sepatu);
            }

            if ($request->filled('ukuran_sepatu') && $request->ukuran_sepatu !== 'all') {
                $query->where('ukuran_sepatu', $request->ukuran_sepatu);
            }

            $employees = $query->orderBy('nama_lengkap')->get();

            // Generate CSV filename
            $filename = 'data_karyawan_gapura_angkasa_' . date('Y-m-d_H-i-s') . '.csv';
            
            $headers = [
                'Content-Type' => 'text/csv; charset=UTF-8',
                'Content-Disposition' => "attachment; filename=\"$filename\"",
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
                    'NIP', 'Nama Lengkap', 'Jenis Kelamin', 'Tempat Lahir', 'Tanggal Lahir', 
                    'Alamat', 'No Telepon', 'Handphone', 'Email', 'Unit Organisasi', 
                    'Jabatan', 'Status Pegawai', 'TMT Mulai Jabatan', 'TMT Mulai Kerja',
                    'Pendidikan Terakhir', 'Instansi Pendidikan', 'Jurusan', 'Tahun Lulus',
                    'Jenis Sepatu', 'Ukuran Sepatu', 'Kota Domisili', 'Usia', 'Status'
                ]);

                // CSV Data
                foreach ($employees as $employee) {
                    fputcsv($file, [
                        $employee->nip,
                        $employee->nama_lengkap,
                        $employee->jenis_kelamin,
                        $employee->tempat_lahir,
                        $employee->tanggal_lahir ? Carbon::parse($employee->tanggal_lahir)->format('d/m/Y') : '',
                        $employee->alamat,
                        $employee->no_telepon,
                        $employee->handphone,
                        $employee->email,
                        $employee->unit_organisasi,
                        $employee->jabatan ?: $employee->nama_jabatan,
                        $employee->status_pegawai,
                        $employee->tmt_mulai_jabatan ? Carbon::parse($employee->tmt_mulai_jabatan)->format('d/m/Y') : '',
                        $employee->tmt_mulai_kerja ? Carbon::parse($employee->tmt_mulai_kerja)->format('d/m/Y') : '',
                        $employee->pendidikan_terakhir ?: $employee->pendidikan,
                        $employee->instansi_pendidikan,
                        $employee->jurusan,
                        $employee->tahun_lulus,
                        $employee->jenis_sepatu ?: '-',
                        $employee->ukuran_sepatu ?: '-',
                        $employee->kota_domisili,
                        $employee->usia,
                        $employee->status_kerja ?: 'Aktif',
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
        $validator = Validator::make($request->all(), [
            'file' => 'required|file|mimes:csv,txt,xlsx,xls|max:10240', // 10MB max
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->with('error', 'File tidak valid. Pastikan file berformat CSV atau Excel dan ukuran maksimal 10MB.');
        }

        try {
            $file = $request->file('file');
            $path = $file->getRealPath();
            
            if (in_array($file->getClientOriginalExtension(), ['csv', 'txt'])) {
                $imported = $this->processCsvImport($path);
            } else {
                $imported = $this->processExcelImport($path);
            }

            return redirect()->route('employees.index')
                ->with('success', "Berhasil mengimpor {$imported} data karyawan.");
        } catch (\Exception $e) {
            return redirect()->route('employees.index')
                ->with('error', 'Gagal mengimpor data: ' . $e->getMessage());
        }
    }

    /**
     * Get employee profile (API endpoint)
     */
    public function profile(Employee $employee)
    {
        try {
            // Load organization relationship if it exists
            if (method_exists($employee, 'organization')) {
                $employee->load('organization');
            }
            
            // Calculate additional profile data
            $workDuration = null;
            if ($employee->tmt_mulai_kerja) {
                $workDuration = Carbon::parse($employee->tmt_mulai_kerja)->diffForHumans(null, true);
            }

            $yearsToRetirement = null;
            if ($employee->tmt_pensiun) {
                $yearsToRetirement = Carbon::now()->diffInYears(Carbon::parse($employee->tmt_pensiun));
            }

            $profileCompletion = $this->calculateProfileCompletion($employee);

            return response()->json([
                'employee' => $employee,
                'work_duration' => $workDuration,
                'years_to_retirement' => $yearsToRetirement,
                'is_near_retirement' => $yearsToRetirement && $yearsToRetirement <= 2,
                'profile_completion' => $profileCompletion,
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Bulk actions on employees
     */
    public function bulkAction(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'action' => 'required|in:activate,deactivate,delete',
            'employee_ids' => 'required|array|min:1',
            'employee_ids.*' => 'exists:employees,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            $employees = Employee::whereIn('id', $request->employee_ids);
            $count = count($request->employee_ids);

            switch ($request->action) {
                case 'activate':
                    $employees->update(['status' => 'active']);
                    $message = "{$count} karyawan berhasil diaktifkan.";
                    break;
                case 'deactivate':
                    $employees->update(['status' => 'inactive']);
                    $message = "{$count} karyawan berhasil dinonaktifkan.";
                    break;
                case 'delete':
                    $employees->update(['status' => 'inactive']);
                    $message = "{$count} karyawan berhasil dihapus.";
                    break;
            }

            return response()->json([
                'success' => true,
                'message' => $message,
                'affected_count' => $count,
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Bulk action failed: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Validate employee data
     */
    public function validateData(Request $request)
    {
        try {
            $rules = [
                'nip' => 'required|string|max:20',
                'nama_lengkap' => 'required|string|max:255',
                'email' => 'nullable|email|max:255',
                'unit_organisasi' => 'required|string|max:100',
                'nama_jabatan' => 'required|string|max:255',
            ];

            $validator = Validator::make($request->all(), $rules);

            if ($validator->fails()) {
                return response()->json([
                    'valid' => false,
                    'errors' => $validator->errors(),
                ]);
            }

            // Check for duplicate NIP
            $nipQuery = Employee::where('nip', $request->nip);
            if ($request->has('employee_id')) {
                $nipQuery->where('id', '!=', $request->employee_id);
            }
            
            if ($nipQuery->exists()) {
                return response()->json([
                    'valid' => false,
                    'errors' => ['nip' => ['NIP sudah digunakan oleh karyawan lain']],
                ]);
            }

            return response()->json([
                'valid' => true,
                'message' => 'Data valid',
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'valid' => false,
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Generate various reports
     */
    public function generateReport(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'type' => 'required|in:summary,detailed,by_unit,retirement,shoes',
            'format' => 'required|in:csv,excel,json',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ], 422);
        }

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
                case 'shoes':
                    return $this->generateShoesReport($request->format);
                default:
                    return response()->json(['error' => 'Invalid report type'], 400);
            }
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Failed to generate report: ' . $e->getMessage()
            ], 500);
        }
    }

    // =====================================================
    // PRIVATE HELPER METHODS
    // =====================================================

    /**
     * Get organizations for filter dropdown
     */
    private function getOrganizationsForFilter()
    {
        try {
            return Organization::where('status', 'active')
                ->orderBy('name')
                ->get(['id', 'name', 'code']);
        } catch (\Exception $e) {
            return [];
        }
    }

    /**
     * Get unique unit options from employees
     */
    private function getUnitOptions()
    {
        try {
            return Employee::whereNotNull('unit_organisasi')
                ->distinct()
                ->orderBy('unit_organisasi')
                ->pluck('unit_organisasi')
                ->filter()
                ->values();
        } catch (\Exception $e) {
            return collect([]);
        }
    }

    /**
     * Get unique jabatan options from employees
     */
    private function getJabatanOptions()
    {
        try {
            $jabatan = Employee::whereNotNull('jabatan')
                ->distinct()
                ->pluck('jabatan')
                ->filter();
                
            $namaJabatan = Employee::whereNotNull('nama_jabatan')
                ->distinct()
                ->pluck('nama_jabatan')
                ->filter();

            return $jabatan->merge($namaJabatan)
                ->unique()
                ->sort()
                ->values();
        } catch (\Exception $e) {
            return collect([]);
        }
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
                    'count' => $item->total,
                ];
            });
    }

    /**
     * Get employees by education
     */
    private function getEmployeesByEducation()
    {
        return Employee::select(
                DB::raw('COALESCE(pendidikan_terakhir, pendidikan) as education'),
                DB::raw('count(*) as total')
            )
            ->whereNotNull(DB::raw('COALESCE(pendidikan_terakhir, pendidikan)'))
            ->where('status', 'active')
            ->groupBy(DB::raw('COALESCE(pendidikan_terakhir, pendidikan)'))
            ->orderBy('total', 'desc')
            ->get()
            ->map(function ($item) {
                return [
                    'name' => $item->education,
                    'count' => $item->total,
                ];
            });
    }

    /**
     * Get shoes size distribution
     */
    private function getShoesSizeDistribution()
    {
        return Employee::select('ukuran_sepatu', DB::raw('count(*) as total'))
            ->whereNotNull('ukuran_sepatu')
            ->where('status', 'active')
            ->groupBy('ukuran_sepatu')
            ->orderBy('ukuran_sepatu')
            ->get()
            ->map(function ($item) {
                return [
                    'size' => $item->ukuran_sepatu,
                    'count' => $item->total,
                ];
            });
    }

    /**
     * Get recent hires (last 6 months)
     */
    private function getRecentHires()
    {
        return Employee::where(function($query) {
                $query->where('tmt_mulai_kerja', '>=', Carbon::now()->subMonths(6))
                      ->orWhere('created_at', '>=', Carbon::now()->subMonths(6));
            })
            ->where('status', 'active')
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get(['id', 'nip', 'nama_lengkap', 'unit_organisasi', 'jabatan', 'nama_jabatan', 'tmt_mulai_kerja', 'created_at']);
    }

    /**
     * Get upcoming retirement (next 12 months)
     */
    private function getUpcomingRetirement()
    {
        return Employee::whereNotNull('tmt_pensiun')
            ->whereBetween('tmt_pensiun', [Carbon::now(), Carbon::now()->addMonths(12)])
            ->where('status', 'active')
            ->orderBy('tmt_pensiun', 'asc')
            ->limit(10)
            ->get(['id', 'nip', 'nama_lengkap', 'unit_organisasi', 'jabatan', 'nama_jabatan', 'tmt_pensiun']);
    }

    /**
     * Calculate profile completion percentage
     */
    private function calculateProfileCompletion(Employee $employee)
    {
        $fields = [
            'nip', 'nama_lengkap', 'jenis_kelamin', 'tempat_lahir', 
            'tanggal_lahir', 'alamat', 'no_telepon', 'email',
            'unit_organisasi', 'jabatan', 'status_pegawai',
            'tmt_mulai_jabatan', 'pendidikan_terakhir', 'jenis_sepatu', 'ukuran_sepatu'
        ];

        $completedFields = 0;
        foreach ($fields as $field) {
            if (!empty($employee->$field)) {
                $completedFields++;
            }
        }

        return round(($completedFields / count($fields)) * 100);
    }

    /**
     * Process CSV import
     */
    private function processCsvImport($filePath)
    {
        $csvData = array_map('str_getcsv', file($filePath));
        $header = array_shift($csvData);
        $imported = 0;
        
        DB::beginTransaction();
        
        try {
            foreach ($csvData as $row) {
                if (count($row) < count($header)) continue;
                
                $data = array_combine($header, $row);
                
                // Skip if NIP already exists
                if (Employee::where('nip', $data['NIP'] ?? '')->exists()) {
                    continue;
                }
                
                // Process and create employee record
                if ($this->createEmployeeFromImport($data)) {
                    $imported++;
                }
            }
            
            DB::commit();
            return $imported;
        } catch (\Exception $e) {
            DB::rollback();
            throw $e;
        }
    }

    /**
     * Process Excel import (placeholder)
     */
    private function processExcelImport($filePath)
    {
        // This would require a library like PhpSpreadsheet
        // For now, treat as CSV
        return $this->processCsvImport($filePath);
    }

    /**
     * Create employee from import data
     */
    private function createEmployeeFromImport($data)
    {
        try {
            $employeeData = [
                'nip' => $data['NIP'] ?? null,
                'nama_lengkap' => $data['NAMA LENGKAP'] ?? $data['Nama Lengkap'] ?? null,
                'status_pegawai' => $data['STATUS PEGAWAI'] ?? $data['Status Pegawai'] ?? 'PEGAWAI TETAP',
                'unit_organisasi' => $data['UNIT ORGANISASI'] ?? $data['Unit Organisasi'] ?? null,
                'jabatan' => $data['JABATAN'] ?? $data['Jabatan'] ?? null,
                'nama_jabatan' => $data['NAMA JABATAN'] ?? $data['Nama Jabatan'] ?? $data['JABATAN'] ?? $data['Jabatan'] ?? null,
                'jenis_kelamin' => $this->normalizeGender($data['JENIS KELAMIN'] ?? $data['Jenis Kelamin'] ?? 'L'),
                'handphone' => $data['HANDPHONE'] ?? $data['Handphone'] ?? $data['NO TELEPON'] ?? $data['No Telepon'] ?? null,
                'tempat_lahir' => $data['TEMPAT LAHIR'] ?? $data['Tempat Lahir'] ?? null,
                'alamat' => $data['ALAMAT'] ?? $data['Alamat'] ?? null,
                'pendidikan_terakhir' => $data['PENDIDIKAN'] ?? $data['Pendidikan'] ?? $data['PENDIDIKAN TERAKHIR'] ?? null,
                'jurusan' => $data['JURUSAN'] ?? $data['Jurusan'] ?? null,
                'email' => $data['EMAIL'] ?? $data['Email'] ?? null,
                'jenis_sepatu' => $data['JENIS SEPATU'] ?? $data['Jenis Sepatu'] ?? null,
                'ukuran_sepatu' => $data['UKURAN SEPATU'] ?? $data['Ukuran Sepatu'] ?? null,
                'provider' => 'PT Gapura Angkasa',
                'lokasi_kerja' => 'Bandar Udara Ngurah Rai',
                'cabang' => 'DPS',
                'status_kerja' => 'Aktif',
                'status' => 'active',
            ];

            // Parse dates
            if (!empty($data['TMT MULAI JABATAN']) || !empty($data['Tmt Mulai Jabatan'])) {
                try {
                    $dateStr = $data['TMT MULAI JABATAN'] ?? $data['Tmt Mulai Jabatan'];
                    $employeeData['tmt_mulai_jabatan'] = Carbon::createFromFormat('d/m/Y', $dateStr);
                } catch (\Exception $e) {
                    $employeeData['tmt_mulai_jabatan'] = null;
                }
            }

            if (!empty($data['TANGGAL LAHIR']) || !empty($data['Tanggal Lahir'])) {
                try {
                    $dateStr = $data['TANGGAL LAHIR'] ?? $data['Tanggal Lahir'];
                    $employeeData['tanggal_lahir'] = Carbon::createFromFormat('d/m/Y', $dateStr);
                    $employeeData['usia'] = $employeeData['tanggal_lahir']->age;
                } catch (\Exception $e) {
                    $employeeData['tanggal_lahir'] = null;
                }
            }

            // Find organization safely
            if (!empty($employeeData['unit_organisasi'])) {
                try {
                    $organization = Organization::where('name', 'like', '%' . $employeeData['unit_organisasi'] . '%')->first();
                    if ($organization) {
                        $employeeData['organization_id'] = $organization->id;
                    }
                } catch (\Exception $e) {
                    // Continue without organization_id
                }
            }

            Employee::create($employeeData);
            return true;
        } catch (\Exception $e) {
            // Log error but continue with next record
            return false;
        }
    }

    /**
     * Normalize gender field
     */
    private function normalizeGender($gender)
    {
        $gender = strtoupper(trim($gender));
        
        if (in_array($gender, ['L', 'LAKI-LAKI', 'LAKI', 'MALE', 'M'])) {
            return 'Laki-laki';
        } elseif (in_array($gender, ['P', 'PEREMPUAN', 'WANITA', 'FEMALE', 'F'])) {
            return 'Perempuan';
        }
        
        return 'Laki-laki'; // Default
    }

    /**
     * Generate summary report
     */
    private function generateSummaryReport($format)
    {
        try {
            $stats = [
                'total' => Employee::count(),
                'active' => Employee::where('status', 'active')->count(),
                'pegawai_tetap' => Employee::where('status_pegawai', 'PEGAWAI TETAP')->count(),
                'tad' => Employee::where('status_pegawai', 'TAD')->count(),
                'laki_laki' => Employee::whereIn('jenis_kelamin', ['L', 'Laki-laki'])->count(),
                'perempuan' => Employee::whereIn('jenis_kelamin', ['P', 'Perempuan'])->count(),
                'pantofel' => Employee::where('jenis_sepatu', 'Pantofel')->count(),
                'safety_shoes' => Employee::where('jenis_sepatu', 'Safety Shoes')->count(),
            ];

            $unitStats = Employee::select('unit_organisasi', DB::raw('count(*) as total'))
                ->whereNotNull('unit_organisasi')
                ->where('status', 'active')
                ->groupBy('unit_organisasi')
                ->orderBy('total', 'desc')
                ->get();
            
            if ($format === 'csv') {
                return $this->generateCsvReport('laporan_ringkasan_karyawan', $stats, $unitStats);
            }
            
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
                $filename = 'laporan_detail_karyawan_' . date('Y-m-d_H-i-s') . '.csv';
                
                $headers = [
                    'Content-Type' => 'text/csv; charset=UTF-8',
                    'Content-Disposition' => "attachment; filename=\"$filename\"",
                ];

                $callback = function() use ($employees) {
                    $file = fopen('php://output', 'w');
                    fprintf($file, chr(0xEF).chr(0xBB).chr(0xBF));
                    
                    // Headers
                    fputcsv($file, [
                        'NIP', 'Nama Lengkap', 'Status Pegawai', 'Unit Organisasi',
                        'Jabatan', 'TMT Mulai Jabatan', 'Jenis Kelamin',
                        'Handphone', 'Email', 'Pendidikan', 'Jurusan', 'Jenis Sepatu', 'Ukuran Sepatu'
                    ]);

                    // Data
                    foreach ($employees as $employee) {
                        fputcsv($file, [
                            $employee->nip,
                            $employee->nama_lengkap,
                            $employee->status_pegawai,
                            $employee->unit_organisasi,
                            $employee->jabatan ?: $employee->nama_jabatan,
                            $employee->tmt_mulai_jabatan ? Carbon::parse($employee->tmt_mulai_jabatan)->format('d/m/Y') : '',
                            $employee->jenis_kelamin,
                            $employee->handphone,
                            $employee->email ?: '-',
                            $employee->pendidikan_terakhir ?: $employee->pendidikan,
                            $employee->jurusan,
                            $employee->jenis_sepatu ?: '-',
                            $employee->ukuran_sepatu ?: '-',
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
                $filename = 'laporan_per_unit_' . date('Y-m-d_H-i-s') . '.csv';
                
                $headers = [
                    'Content-Type' => 'text/csv; charset=UTF-8',
                    'Content-Disposition' => "attachment; filename=\"$filename\"",
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
                $filename = 'laporan_pensiun_' . date('Y-m-d_H-i-s') . '.csv';
                
                $headers = [
                    'Content-Type' => 'text/csv; charset=UTF-8',
                    'Content-Disposition' => "attachment; filename=\"$filename\"",
                ];

                $callback = function() use ($upcomingRetirement) {
                    $file = fopen('php://output', 'w');
                    fprintf($file, chr(0xEF).chr(0xBB).chr(0xBF));
                    
                    fputcsv($file, ['LAPORAN KARYAWAN YANG AKAN PENSIUN']);
                    fputcsv($file, ['Periode', '12 Bulan Ke Depan']);
                    fputcsv($file, ['Tanggal Laporan', date('d/m/Y H:i:s')]);
                    fputcsv($file, []);
                    fputcsv($file, ['NIP', 'Nama Lengkap', 'Unit Organisasi', 'Jabatan', 'TMT Pensiun']);
                    
                    foreach ($upcomingRetirement as $employee) {
                        fputcsv($file, [
                            $employee->nip,
                            $employee->nama_lengkap,
                            $employee->unit_organisasi,
                            $employee->jabatan ?: $employee->nama_jabatan,
                            $employee->tmt_pensiun ? Carbon::parse($employee->tmt_pensiun)->format('d/m/Y') : ''
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
     * Generate shoes report
     */
    private function generateShoesReport($format)
    {
        try {
            $shoeStats = [
                'pantofel' => Employee::where('jenis_sepatu', 'Pantofel')->count(),
                'safety_shoes' => Employee::where('jenis_sepatu', 'Safety Shoes')->count(),
                'no_data' => Employee::whereNull('jenis_sepatu')->count(),
            ];

            $sizeDistribution = Employee::select('ukuran_sepatu', DB::raw('count(*) as total'))
                ->whereNotNull('ukuran_sepatu')
                ->where('status', 'active')
                ->groupBy('ukuran_sepatu')
                ->orderBy('ukuran_sepatu')
                ->get();

            $shoesByUnit = Employee::select('unit_organisasi', 'jenis_sepatu', DB::raw('count(*) as total'))
                ->whereNotNull('jenis_sepatu')
                ->where('status', 'active')
                ->groupBy('unit_organisasi', 'jenis_sepatu')
                ->orderBy('unit_organisasi')
                ->get();

            if ($format === 'csv') {
                $filename = 'laporan_distribusi_sepatu_' . date('Y-m-d_H-i-s') . '.csv';
                
                $headers = [
                    'Content-Type' => 'text/csv; charset=UTF-8',
                    'Content-Disposition' => "attachment; filename=\"$filename\"",
                ];

                $callback = function() use ($shoeStats, $sizeDistribution, $shoesByUnit) {
                    $file = fopen('php://output', 'w');
                    fprintf($file, chr(0xEF).chr(0xBB).chr(0xBF));
                    
                    fputcsv($file, ['LAPORAN DISTRIBUSI SEPATU KARYAWAN']);
                    fputcsv($file, ['PT GAPURA ANGKASA - BANDAR UDARA NGURAH RAI']);
                    fputcsv($file, ['Tanggal Laporan', date('d/m/Y H:i:s')]);
                    fputcsv($file, []);
                    
                    fputcsv($file, ['RINGKASAN JENIS SEPATU']);
                    fputcsv($file, ['Jenis Sepatu', 'Jumlah Karyawan']);
                    fputcsv($file, ['Pantofel', $shoeStats['pantofel']]);
                    fputcsv($file, ['Safety Shoes', $shoeStats['safety_shoes']]);
                    fputcsv($file, ['Belum ada data', $shoeStats['no_data']]);
                    
                    fputcsv($file, []);
                    fputcsv($file, ['DISTRIBUSI UKURAN SEPATU']);
                    fputcsv($file, ['Ukuran', 'Jumlah Karyawan']);
                    
                    foreach ($sizeDistribution as $size) {
                        fputcsv($file, [$size->ukuran_sepatu, $size->total]);
                    }
                    
                    fputcsv($file, []);
                    fputcsv($file, ['DISTRIBUSI PER UNIT ORGANISASI']);
                    fputcsv($file, ['Unit Organisasi', 'Jenis Sepatu', 'Jumlah']);
                    
                    foreach ($shoesByUnit as $item) {
                        fputcsv($file, [$item->unit_organisasi, $item->jenis_sepatu, $item->total]);
                    }
                    
                    fclose($file);
                };

                return response()->stream($callback, 200, $headers);
            }

            return response()->json([
                'shoe_statistics' => $shoeStats,
                'size_distribution' => $sizeDistribution,
                'shoes_by_unit' => $shoesByUnit,
                'generated_at' => now()->format('Y-m-d H:i:s'),
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Failed to generate shoes report: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Generate CSV report helper
     */
    private function generateCsvReport($reportName, $stats, $unitStats)
    {
        $filename = $reportName . '_' . date('Y-m-d_H-i-s') . '.csv';
        
        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"$filename\"",
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
            
            if (isset($stats['pantofel']) && isset($stats['safety_shoes'])) {
                fputcsv($file, []);
                fputcsv($file, ['DISTRIBUSI SEPATU']);
                fputcsv($file, ['Pantofel', $stats['pantofel']]);
                fputcsv($file, ['Safety Shoes', $stats['safety_shoes']]);
            }
            
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
}