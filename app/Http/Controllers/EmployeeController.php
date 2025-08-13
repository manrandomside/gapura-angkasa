<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use App\Models\Organization;
use App\Models\Unit;
use App\Models\SubUnit;
use App\Helpers\TimezoneHelper;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;

class EmployeeController extends Controller
{
    /**
     * STATUS PEGAWAI CONSTANTS dengan TAD Split - FITUR BARU
     */
    const STATUS_PEGAWAI_OPTIONS = [
        'PEGAWAI TETAP',
        'PKWT',
        'TAD PAKET SDM',
        'TAD PAKET PEKERJAAN'
    ];

    /**
     * KELOMPOK JABATAN CONSTANTS - FITUR BARU
     */
    const KELOMPOK_JABATAN_OPTIONS = [
        'SUPERVISOR',
        'STAFF', 
        'MANAGER',
        'EXECUTIVE GENERAL MANAGER',
        'ACCOUNT EXECUTIVE/AE'
    ];

    // =====================================================
    // UNIT/SUBUNIT API METHODS - FIXED & ENHANCED
    // =====================================================

    /**
     * Get units berdasarkan unit organisasi - ENHANCED dengan better error handling
     */
    public function getUnits(Request $request)
    {
        try {
            $unitOrganisasi = $request->get('unit_organisasi');
            
            Log::info('Get Units API called', [
                'unit_organisasi' => $unitOrganisasi,
                'request_method' => $request->method(),
                'all_params' => $request->all()
            ]);
            
            if (!$unitOrganisasi) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unit organisasi parameter required',
                    'data' => [],
                    'debug_info' => [
                        'available_unit_organisasi' => Unit::UNIT_ORGANISASI_OPTIONS ?? [],
                        'example_usage' => '/api/units/by-organisasi?unit_organisasi=EGM'
                    ]
                ], 400);
            }

            // Validate unit organisasi
            $validUnitOrganisasi = Unit::UNIT_ORGANISASI_OPTIONS ?? [
                'EGM', 'GM', 'Airside', 'Landside', 'Back Office', 'SSQC', 'Ancillary'
            ];
            
            if (!in_array($unitOrganisasi, $validUnitOrganisasi)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid unit organisasi',
                    'data' => [],
                    'debug_info' => [
                        'provided' => $unitOrganisasi,
                        'valid_options' => $validUnitOrganisasi
                    ]
                ], 400);
            }

            // Check total units in database
            $totalUnits = Unit::count();
            Log::info('Database check', [
                'total_units_in_db' => $totalUnits,
                'unit_organisasi_requested' => $unitOrganisasi
            ]);

            if ($totalUnits === 0) {
                return response()->json([
                    'success' => false,
                    'message' => 'No units found in database. Please run UnitSeeder first.',
                    'data' => [],
                    'debug_info' => [
                        'seeder_command' => 'php artisan db:seed --class=UnitSeeder',
                        'total_units_in_db' => 0
                    ]
                ], 404);
            }

            // Get units for the specified unit organisasi
            $units = Unit::where('unit_organisasi', $unitOrganisasi)
                ->where('is_active', true)
                ->select('id', 'name', 'code', 'description', 'unit_organisasi')
                ->orderBy('name')
                ->get();

            Log::info('Units query result', [
                'unit_organisasi' => $unitOrganisasi,
                'units_found' => $units->count(),
                'units' => $units->pluck('name')->toArray()
            ]);

            // Unit organisasi yang tidak memiliki sub unit
            $unitWithoutSubUnits = ['EGM', 'GM'];
            $requiresSubUnit = !in_array($unitOrganisasi, $unitWithoutSubUnits);

            if ($units->isEmpty()) {
                return response()->json([
                    'success' => false,
                    'message' => "No units found for unit organisasi: {$unitOrganisasi}",
                    'data' => [],
                    'debug_info' => [
                        'unit_organisasi' => $unitOrganisasi,
                        'total_units_in_db' => $totalUnits,
                        'available_unit_organisasi_in_db' => Unit::distinct('unit_organisasi')->pluck('unit_organisasi')->toArray(),
                        'suggestion' => 'Check if UnitSeeder was run correctly or if unit_organisasi name matches exactly'
                    ]
                ], 404);
            }

            return response()->json([
                'success' => true,
                'message' => 'Units retrieved successfully',
                'data' => $units,
                'meta' => [
                    'unit_organisasi' => $unitOrganisasi,
                    'total_count' => $units->count(),
                    'requires_sub_unit' => $requiresSubUnit,
                    'filters_applied' => [
                        'unit_organisasi' => $unitOrganisasi,
                        'is_active' => true
                    ],
                    'note' => $requiresSubUnit 
                        ? 'Unit organisasi ini memerlukan sub unit' 
                        : 'Unit organisasi ini tidak memerlukan sub unit'
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('Get Units API Error', [
                'unit_organisasi' => $request->get('unit_organisasi'),
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error retrieving units',
                'error' => app()->environment('local') ? $e->getMessage() : 'Internal server error',
                'data' => []
            ], 500);
        }
    }

    /**
     * Get sub units berdasarkan unit - ENHANCED untuk handle unit tanpa sub unit
     */
    public function getSubUnits(Request $request)
    {
        try {
            $unitId = $request->get('unit_id');
            
            Log::info('Get Sub Units API called', [
                'unit_id' => $unitId,
                'request_method' => $request->method(),
                'all_params' => $request->all()
            ]);
            
            if (!$unitId) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unit ID required',
                    'data' => [],
                    'debug_info' => [
                        'example_usage' => '/api/sub-units/by-unit?unit_id=1'
                    ]
                ], 400);
            }

            // Validate unit exists
            $unit = Unit::find($unitId);
            if (!$unit) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unit not found',
                    'data' => [],
                    'debug_info' => [
                        'unit_id_provided' => $unitId,
                        'available_units' => Unit::select('id', 'name', 'unit_organisasi')->get()
                    ]
                ], 404);
            }

            // Unit organisasi yang tidak memiliki sub unit
            $unitWithoutSubUnits = ['EGM', 'GM'];
            
            // Check if this unit organisasi should have sub units
            if (in_array($unit->unit_organisasi, $unitWithoutSubUnits)) {
                Log::info('Unit does not require sub units', [
                    'unit_id' => $unitId,
                    'unit_name' => $unit->name,
                    'unit_organisasi' => $unit->unit_organisasi
                ]);
                
                return response()->json([
                    'success' => true,
                    'message' => "Unit {$unit->name} ({$unit->unit_organisasi}) tidak memiliki sub unit",
                    'data' => [],
                    'meta' => [
                        'unit_info' => [
                            'id' => $unit->id,
                            'name' => $unit->name,
                            'unit_organisasi' => $unit->unit_organisasi,
                            'requires_sub_unit' => false
                        ],
                        'total_count' => 0,
                        'note' => 'Unit organisasi ini tidak memerlukan sub unit'
                    ]
                ]);
            }

            // Get sub units for units that should have them
            $subUnits = SubUnit::where('unit_id', $unitId)
                ->where('is_active', true)
                ->select('id', 'name', 'code', 'description', 'unit_id')
                ->orderBy('name')
                ->get();

            Log::info('Sub units retrieved', [
                'unit_id' => $unitId,
                'unit_name' => $unit->name,
                'unit_organisasi' => $unit->unit_organisasi,
                'sub_units_count' => $subUnits->count(),
                'sub_units' => $subUnits->pluck('name')->toArray()
            ]);

            // Check if unit should have sub units but none found
            if ($subUnits->isEmpty()) {
                Log::warning('No sub units found for unit that should have them', [
                    'unit_id' => $unitId,
                    'unit_name' => $unit->name,
                    'unit_organisasi' => $unit->unit_organisasi
                ]);
                
                return response()->json([
                    'success' => true,
                    'message' => "Tidak ada sub unit ditemukan untuk unit {$unit->name}",
                    'data' => [],
                    'meta' => [
                        'unit_info' => [
                            'id' => $unit->id,
                            'name' => $unit->name,
                            'unit_organisasi' => $unit->unit_organisasi,
                            'requires_sub_unit' => true
                        ],
                        'total_count' => 0,
                        'warning' => 'Unit ini seharusnya memiliki sub unit. Pastikan UnitSeeder sudah dijalankan dengan benar.'
                    ]
                ]);
            }

            return response()->json([
                'success' => true,
                'message' => 'Sub units retrieved successfully',
                'data' => $subUnits,
                'meta' => [
                    'unit_info' => [
                        'id' => $unit->id,
                        'name' => $unit->name,
                        'unit_organisasi' => $unit->unit_organisasi,
                        'requires_sub_unit' => true
                    ],
                    'total_count' => $subUnits->count(),
                    'filters_applied' => [
                        'unit_id' => $unitId,
                        'is_active' => true
                    ]
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('Get Sub Units API Error', [
                'unit_id' => $request->get('unit_id'),
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error retrieving sub units',
                'error' => app()->environment('local') ? $e->getMessage() : 'Internal server error',
                'data' => []
            ], 500);
        }
    }

    // =====================================================
    // MAIN CRUD METHODS - FIXED FOR AUTO-INCREMENT ID
    // =====================================================

    /**
     * Display a listing of employees with enhanced search, filter, and pagination capabilities
     * FIXED: Updated untuk auto-increment ID compatibility
     */
    public function index(Request $request)
    {
        try {
            // Build query with relationships
            $query = Employee::query();

            // Load relationships if they exist
            if (method_exists(Employee::class, 'organization')) {
                $query->with('organization');
            }
            if (method_exists(Employee::class, 'unit')) {
                $query->with('unit');
            }
            if (method_exists(Employee::class, 'subUnit')) {
                $query->with('subUnit');
            }

            // Apply active scope - default to active employees
            $query->where('status', 'active');

            // Build filter conditions
            $filterConditions = [];
            
            // Apply search filters
            if ($request->filled('search')) {
                $searchTerm = $request->search;
                $query->where(function($q) use ($searchTerm) {
                    $q->where('nik', 'like', "%{$searchTerm}%")
                      ->orWhere('nip', 'like', "%{$searchTerm}%")
                      ->orWhere('nama_lengkap', 'like', "%{$searchTerm}%")
                      ->orWhere('jabatan', 'like', "%{$searchTerm}%")
                      ->orWhere('nama_jabatan', 'like', "%{$searchTerm}%")
                      ->orWhere('unit_organisasi', 'like', "%{$searchTerm}%")
                      ->orWhere('kelompok_jabatan', 'like', "%{$searchTerm}%")
                      ->orWhere('jenis_sepatu', 'like', "%{$searchTerm}%")
                      ->orWhere('ukuran_sepatu', 'like', "%{$searchTerm}%")
                      ->orWhereHas('unit', function ($q) use ($searchTerm) {
                          $q->where('name', 'like', "%{$searchTerm}%");
                      })
                      ->orWhereHas('subUnit', function ($q) use ($searchTerm) {
                          $q->where('name', 'like', "%{$searchTerm}%");
                      });
                });
                $filterConditions['search'] = $searchTerm;
            }

            // Filter by status pegawai
            if ($request->filled('status_pegawai') && $request->status_pegawai !== 'all') {
                $query->where('status_pegawai', $request->status_pegawai);
                $filterConditions['status_pegawai'] = $request->status_pegawai;
            }

            // Filter by kelompok jabatan
            if ($request->filled('kelompok_jabatan') && $request->kelompok_jabatan !== 'all') {
                $query->where('kelompok_jabatan', $request->kelompok_jabatan);
                $filterConditions['kelompok_jabatan'] = $request->kelompok_jabatan;
            }

            // Filter by unit organisasi
            if ($request->filled('unit_organisasi') && $request->unit_organisasi !== 'all') {
                $query->where('unit_organisasi', $request->unit_organisasi);
                $filterConditions['unit_organisasi'] = $request->unit_organisasi;
            }

            // Filter by unit
            if ($request->filled('unit_id') && $request->unit_id !== 'all') {
                $query->where('unit_id', $request->unit_id);
                $filterConditions['unit_id'] = $request->unit_id;
            }

            // Filter by sub unit
            if ($request->filled('sub_unit_id') && $request->sub_unit_id !== 'all') {
                $query->where('sub_unit_id', $request->sub_unit_id);
                $filterConditions['sub_unit_id'] = $request->sub_unit_id;
            }

            // Filter by gender
            if ($request->filled('jenis_kelamin') && $request->jenis_kelamin !== 'all') {
                $query->where('jenis_kelamin', $request->jenis_kelamin);
                $filterConditions['jenis_kelamin'] = $request->jenis_kelamin;
            }

            // Filter by jenis sepatu
            if ($request->filled('jenis_sepatu') && $request->jenis_sepatu !== 'all') {
                $query->where('jenis_sepatu', $request->jenis_sepatu);
                $filterConditions['jenis_sepatu'] = $request->jenis_sepatu;
            }

            // Filter by ukuran sepatu
            if ($request->filled('ukuran_sepatu') && $request->ukuran_sepatu !== 'all') {
                $query->where('ukuran_sepatu', $request->ukuran_sepatu);
                $filterConditions['ukuran_sepatu'] = $request->ukuran_sepatu;
            }

            // Set per page count
            $perPage = $request->get('per_page', 20);
            
            // Validate per_page parameter
            if (!in_array($perPage, [10, 20, 50, 100])) {
                $perPage = 20;
            }

            // Order by created_at desc to show newest first
            $employees = $query->orderBy('created_at', 'desc')
                             ->orderBy('nama_lengkap', 'asc')
                             ->paginate($perPage)
                             ->withQueryString();

            // Calculate statistics
            $statistics = $this->getEnhancedStatistics($filterConditions);

            // Get organizations for filter dropdown
            $organizations = $this->getOrganizationsForFilter();

            // Get filter options for dropdowns
            $filterOptions = $this->getFilterOptions();

            // Get new employees data for notifications
            $newEmployeesToday = $this->getNewEmployeesToday();
            $newEmployeesYesterday = $this->getNewEmployeesYesterday();
            $newEmployeesThisWeek = $this->getNewEmployeesThisWeek();

            // Get notification data from session
            $notificationData = $this->getNotificationData();

            // Get current time information
            $timeInfo = $this->getTimeBasedGreeting();
            $businessHours = $this->getBusinessHoursStatus();

            return Inertia::render('Employees/Index', [
                'employees' => $employees,
                'organizations' => $organizations,
                'filterOptions' => $filterOptions,
                'statistics' => $statistics,
                'filters' => $request->only([
                    'search', 
                    'status_pegawai', 
                    'kelompok_jabatan',
                    'unit_organisasi', 
                    'unit_id',
                    'sub_unit_id',
                    'jenis_kelamin', 
                    'jenis_sepatu', 
                    'ukuran_sepatu'
                ]),
                'pagination' => [
                    'current_page' => $employees->currentPage(),
                    'last_page' => $employees->lastPage(),
                    'per_page' => $employees->perPage(),
                    'total' => $employees->total(),
                    'from' => $employees->firstItem(),
                    'to' => $employees->lastItem(),
                    'has_pages' => $employees->hasPages(),
                    'has_more_pages' => $employees->hasMorePages(),
                    'on_first_page' => $employees->onFirstPage(),
                    'on_last_page' => $employees->onLastPage(),
                    'next_page_url' => $employees->nextPageUrl(),
                    'prev_page_url' => $employees->previousPageUrl(),
                    'links' => $employees->links()->elements[0] ?? []
                ],
                
                // Comprehensive notification data
                'notifications' => [
                    'session' => $notificationData,
                    'newToday' => $newEmployeesToday,
                    'newYesterday' => $newEmployeesYesterday,
                    'newThisWeek' => $newEmployeesThisWeek,
                    'timeInfo' => $timeInfo,
                    'businessHours' => $businessHours,
                    'witaTime' => $this->formatIndonesian($this->getWitaDate()),
                ],

                // Legacy compatibility
                'newEmployee' => $notificationData['newEmployee'] ?? null,
                'success' => $notificationData['success'] ?? null,
                'error' => $notificationData['error'] ?? null,
                'message' => $notificationData['message'] ?? null,
                'notification' => $notificationData['notification'] ?? null,
                'alerts' => $notificationData['alerts'] ?? [],

                // Page metadata
                'title' => 'Management Karyawan',
                'subtitle' => 'Kelola data karyawan PT Gapura Angkasa - Bandar Udara Ngurah Rai',
                'breadcrumbs' => [
                    ['name' => 'Dashboard', 'route' => 'dashboard'],
                    ['name' => 'Management Karyawan', 'route' => 'employees.index'],
                ],
            ]);

        } catch (\Exception $e) {
            Log::error('Employee Index Error', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'request_data' => $request->all()
            ]);

            return Inertia::render('Employees/Index', [
                'employees' => ['data' => []],
                'organizations' => [],
                'filterOptions' => $this->getDefaultFilterOptions(),
                'statistics' => $this->getDefaultStatistics(),
                'filters' => $request->only([
                    'search', 
                    'status_pegawai', 
                    'kelompok_jabatan',
                    'unit_organisasi',
                    'unit_id',
                    'sub_unit_id',
                    'jenis_kelamin', 
                    'jenis_sepatu', 
                    'ukuran_sepatu'
                ]),
                'pagination' => $this->getDefaultPagination(),
                'notifications' => [
                    'session' => null, 
                    'newToday' => [], 
                    'newYesterday' => 0,
                    'newThisWeek' => [], 
                    'timeInfo' => $this->getTimeBasedGreeting(),
                    'witaTime' => $this->formatIndonesian($this->getWitaDate()),
                ],
                'newEmployee' => null,
                'error' => 'Terjadi kesalahan saat memuat data karyawan: ' . $e->getMessage(),
                'notification' => null,
                'alerts' => [],
                'title' => 'Management Karyawan',
                'subtitle' => 'Kelola data karyawan PT Gapura Angkasa - Bandar Udara Ngurah Rai',
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
            
            // Enhanced unit options with fallbacks
            $unitOptions = $this->getUnitOptions();
            
            // Enhanced jabatan options
            $jabatanOptions = $this->getJabatanOptions();
            
            // Unit Organisasi options dari model Unit
            $unitOrganisasiOptions = Unit::UNIT_ORGANISASI_OPTIONS ?? [
                'EGM', 'GM', 'Airside', 'Landside', 'Back Office', 'SSQC', 'Ancillary'
            ];
            
            return Inertia::render('Employees/Create', [
                'organizations' => $organizations,
                'unitOptions' => $unitOptions,
                'jabatanOptions' => $jabatanOptions,
                'unitOrganisasiOptions' => $unitOrganisasiOptions,
                'statusPegawaiOptions' => self::STATUS_PEGAWAI_OPTIONS,
                'kelompokJabatanOptions' => self::KELOMPOK_JABATAN_OPTIONS,
                'success' => session('success'),
                'error' => session('error'),
                'message' => session('message'),
            ]);
        } catch (\Exception $e) {
            Log::error('Employee Create Error', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return redirect()->route('employees.index')
                ->with('error', 'Error loading create form: ' . $e->getMessage());
        }
    }

    /**
     * Store a newly created employee with comprehensive validation
     * FIXED: Auto-increment ID compatibility dan improved error handling
     */
    public function store(Request $request)
    {
        try {
            // Log request data untuk debugging (tanpa data sensitif)
            Log::info('Employee Store Request Started', [
                'nik' => $request->nik,
                'nip' => $request->nip,
                'nama_lengkap' => $request->nama_lengkap,
                'unit_organisasi' => $request->unit_organisasi,
                'unit_id' => $request->unit_id,
                'sub_unit_id' => $request->sub_unit_id,
                'jenis_kelamin' => $request->jenis_kelamin,
                'kelompok_jabatan' => $request->kelompok_jabatan,
                'status_pegawai' => $request->status_pegawai,
                'user_agent' => $request->header('User-Agent'),
                'ip_address' => $request->ip()
            ]);

            // Get available unit organisasi options
            $unitOrganisasiOptions = Unit::UNIT_ORGANISASI_OPTIONS ?? [
                'EGM', 'GM', 'Airside', 'Landside', 'Back Office', 'SSQC', 'Ancillary'
            ];

            // Unit organisasi yang tidak memiliki sub unit
            $unitWithoutSubUnits = ['EGM', 'GM'];

            // Enhanced validation rules
            $validator = Validator::make($request->all(), [
                // Required identity fields
                'nik' => [
                    'required',
                    'string',
                    'size:16',
                    'regex:/^[0-9]+$/',
                    'unique:employees,nik'
                ],
                'nip' => [
                    'required',
                    'string',
                    'min:5',
                    'regex:/^[0-9]+$/',
                    'unique:employees,nip'
                ],
                'nama_lengkap' => 'required|string|min:2|max:200',
                'jenis_kelamin' => [
                    'required',
                    'string',
                    Rule::in(['Laki-laki', 'Perempuan'])
                ],
                
                // Organizational structure validation
                'unit_organisasi' => [
                    'required',
                    'string',
                    Rule::in($unitOrganisasiOptions)
                ],
                'unit_id' => [
                    'required',
                    'integer',
                    'exists:units,id'
                ],
                'sub_unit_id' => [
                    'nullable',
                    'integer',
                    function ($attribute, $value, $fail) use ($request, $unitWithoutSubUnits) {
                        // Jika unit organisasi adalah EGM atau GM, sub unit tidak wajib
                        if (in_array($request->unit_organisasi, $unitWithoutSubUnits)) {
                            return; // Skip validation untuk EGM dan GM
                        }
                        
                        // Untuk unit lainnya, sub unit wajib diisi
                        if (!$value) {
                            $fail('Sub unit wajib diisi untuk unit organisasi ' . $request->unit_organisasi . '.');
                        }
                        
                        // Validasi bahwa sub unit exists
                        if ($value && !SubUnit::where('id', $value)->exists()) {
                            $fail('Sub unit yang dipilih tidak valid.');
                        }
                        
                        // Validasi bahwa sub unit belongs to unit yang dipilih
                        if ($value && $request->unit_id) {
                            $subUnit = SubUnit::find($value);
                            if ($subUnit && $subUnit->unit_id != $request->unit_id) {
                                $fail('Sub unit tidak sesuai dengan unit yang dipilih.');
                            }
                        }
                    }
                ],
                
                // Required job fields
                'nama_jabatan' => 'required|string|min:2|max:100',
                'kelompok_jabatan' => [
                    'required',
                    'string',
                    Rule::in(['SUPERVISOR', 'STAFF', 'MANAGER', 'EXECUTIVE GENERAL MANAGER', 'ACCOUNT EXECUTIVE/AE'])
                ],
                'status_pegawai' => [
                    'required',
                    'string',
                    Rule::in(['PEGAWAI TETAP', 'PKWT', 'TAD PAKET SDM', 'TAD PAKET PEKERJAAN'])
                ],
                
                // Optional fields dengan validasi yang lebih longgar
                'tempat_lahir' => 'nullable|string|max:100',
                'tanggal_lahir' => 'nullable|date|before:today',
                'alamat' => 'nullable|string|max:500',
                'kota_domisili' => 'nullable|string|max:100',
                'handphone' => 'nullable|string|max:20',
                'email' => 'nullable|email|max:100|unique:employees,email',
                'no_bpjs_kesehatan' => 'nullable|string|max:50',
                'no_bpjs_ketenagakerjaan' => 'nullable|string|max:50',
                'tmt_mulai_jabatan' => 'nullable|date',
                'tmt_mulai_kerja' => 'nullable|date',
                'tmt_pensiun' => 'nullable|date',
                'pendidikan_terakhir' => 'nullable|string|max:50',
                'pendidikan' => 'nullable|string|max:50',
                'instansi_pendidikan' => 'nullable|string|max:200',
                'jurusan' => 'nullable|string|max:100',
                'tahun_lulus' => 'nullable|integer|min:1950|max:' . (date('Y') + 5),
                'jenis_sepatu' => 'nullable|string|max:50',
                'ukuran_sepatu' => 'nullable|string|max:10',
                'height' => 'nullable|integer|min:100|max:250',
                'weight' => 'nullable|integer|min:30|max:200',
                'seragam' => 'nullable|string|max:100',
            ], [
                // Custom error messages
                'nik.required' => 'NIK wajib diisi.',
                'nik.size' => 'NIK harus tepat 16 digit.',
                'nik.regex' => 'NIK hanya boleh berisi angka.',
                'nik.unique' => 'NIK sudah terdaftar di sistem.',
                'nip.required' => 'NIP wajib diisi.',
                'nip.min' => 'NIP minimal 5 digit.',
                'nip.regex' => 'NIP hanya boleh berisi angka.',
                'nip.unique' => 'NIP sudah terdaftar di sistem.',
                'nama_lengkap.required' => 'Nama lengkap wajib diisi.',
                'jenis_kelamin.required' => 'Jenis kelamin wajib dipilih.',
                'unit_organisasi.required' => 'Unit organisasi wajib dipilih.',
                'unit_id.required' => 'Unit wajib dipilih.',
                'nama_jabatan.required' => 'Nama jabatan wajib diisi.',
                'kelompok_jabatan.required' => 'Kelompok jabatan wajib dipilih.',
                'status_pegawai.required' => 'Status pegawai wajib dipilih.',
                'email.unique' => 'Email sudah terdaftar di sistem.'
            ]);

            // Return validation errors using Inertia redirect back
            if ($validator->fails()) {
                Log::warning('Employee Store Validation Failed', [
                    'errors' => $validator->errors()->toArray(),
                    'nik' => $request->nik,
                    'nip' => $request->nip
                ]);

                return redirect()->back()
                    ->withErrors($validator->errors())
                    ->withInput()
                    ->with('error', 'Data tidak valid. Silakan periksa kembali form.');
            }

            // Prepare data for employee creation
            $employeeData = $validator->validated();
            
            // Handle jenis_kelamin conversion (Frontend: Laki-laki/Perempuan -> Database: L/P)
            if (isset($employeeData['jenis_kelamin'])) {
                $employeeData['jenis_kelamin'] = $employeeData['jenis_kelamin'] === 'Laki-laki' ? 'L' : 'P';
            }
            
            // Handle sub_unit_id untuk EGM dan GM (set null jika kosong)
            if (in_array($request->unit_organisasi, $unitWithoutSubUnits) && empty($employeeData['sub_unit_id'])) {
                $employeeData['sub_unit_id'] = null;
            }

            // Set default values yang diperlukan
            $employeeData['status'] = 'active';
            $employeeData['organization_id'] = 1; // Default organization
            $employeeData['lokasi_kerja'] = 'Bandar Udara Ngurah Rai'; // Default location
            $employeeData['cabang'] = 'Denpasar'; // Default branch

            // Calculate TMT Pensiun (56 tahun dari tanggal lahir) - only if birth date provided
            if (isset($employeeData['tanggal_lahir']) && !empty($employeeData['tanggal_lahir'])) {
                $birthDate = Carbon::parse($employeeData['tanggal_lahir']);
                $pensionDate = clone $birthDate;
                $pensionDate->addYears(56);
                
                // Apply logic: if birth date < 10th, pension on 1st of same month, else 1st of next month
                if ($birthDate->day < 10) {
                    $pensionDate->day = 1;
                } else {
                    $pensionDate->day = 1;
                    $pensionDate->addMonth();
                }
                
                $employeeData['tmt_pensiun'] = $pensionDate;
            }

            // Start database transaction
            DB::beginTransaction();
            
            try {
                // Create employee - FIXED: menggunakan auto-increment ID
                $employee = Employee::create($employeeData);
                
                // Commit transaction
                DB::commit();

                // FIXED: Log menggunakan ID dan NIK
                Log::info('Employee Created Successfully', [
                    'employee_id' => $employee->id, // FIXED: Use auto-increment ID
                    'employee_nik' => $employee->nik,
                    'nik' => $employee->nik,
                    'nip' => $employee->nip,
                    'nama_lengkap' => $employee->nama_lengkap,
                    'unit_organisasi' => $employee->unit_organisasi,
                    'has_sub_unit' => !is_null($employee->sub_unit_id)
                ]);

                // Return success response
                return redirect()->route('employees.index')
                    ->with('success', 'Karyawan berhasil ditambahkan!')
                    ->with('notification', [
                        'type' => 'success',
                        'title' => 'Berhasil!',
                        'message' => "Karyawan {$employee->nama_lengkap} berhasil ditambahkan ke sistem.",
                        'newEmployee' => [
                            'id' => $employee->id, // FIXED: Use auto-increment ID
                            'nik' => $employee->nik,
                            'nip' => $employee->nip,
                            'nama_lengkap' => $employee->nama_lengkap,
                            'unit_organisasi' => $employee->unit_organisasi,
                            'created_at' => $employee->created_at->format('d/m/Y H:i:s')
                        ]
                    ]);

            } catch (\Exception $e) {
                // Rollback transaction
                DB::rollBack();
                
                Log::error('Employee Database Creation Failed', [
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString(),
                    'employee_data' => $employeeData
                ]);

                return redirect()->back()
                    ->withInput()
                    ->with('error', 'Terjadi kesalahan database saat menyimpan data. Error: ' . $e->getMessage())
                    ->with('notification', [
                        'type' => 'error',
                        'title' => 'Gagal Menyimpan',
                        'message' => 'Terjadi kesalahan database. Silakan coba lagi atau hubungi administrator.'
                    ]);
            }

        } catch (\Exception $e) {
            Log::error('Employee Store Failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'request_data' => $request->except(['password'])
            ]);

            return redirect()->back()
                ->withInput()
                ->with('error', 'Terjadi kesalahan saat menyimpan data. Silakan coba lagi.')
                ->with('notification', [
                    'type' => 'error',
                    'title' => 'Gagal Menyimpan',
                    'message' => 'Terjadi kesalahan sistem. Silakan coba lagi atau hubungi administrator. Error: ' . $e->getMessage()
                ]);
        }
    }

    /**
     * Display the specified employee
     * FIXED: Parameter sekarang menggunakan flexible identifier (ID atau NIK)
     */
    public function show(string $identifier)
    {
        try {
            // FIXED: Find by flexible identifier (ID atau NIK)
            $employee = Employee::findByIdentifier($identifier)->first();
            
            if (!$employee) {
                return redirect()->route('employees.index')
                    ->with('error', 'Employee tidak ditemukan.');
            }
            
            // Load relationships if they exist
            if (method_exists($employee, 'organization')) {
                $employee->load('organization');
            }
            if (method_exists($employee, 'unit')) {
                $employee->load('unit');
            }
            if (method_exists($employee, 'subUnit')) {
                $employee->load('subUnit');
            }
            
            return Inertia::render('Employees/Show', [
                'employee' => $employee,
            ]);
        } catch (\Exception $e) {
            Log::error('Employee Show Error', [
                'employee_identifier' => $identifier,
                'error' => $e->getMessage()
            ]);

            return redirect()->route('employees.index')
                ->with('error', 'Error loading employee details: ' . $e->getMessage());
        }
    }

    /**
     * Show the form for editing the specified employee
     * FIXED: Parameter menggunakan flexible identifier, Include kelompok jabatan dan status pegawai options
     */
    public function edit(string $identifier)
    {
        try {
            // FIXED: Find by flexible identifier (ID atau NIK)
            $employee = Employee::findByIdentifier($identifier)->first();
            
            if (!$employee) {
                return redirect()->route('employees.index')
                    ->with('error', 'Employee tidak ditemukan.');
            }
            
            // Load relationships
            if (method_exists($employee, 'unit')) {
                $employee->load('unit');
            }
            if (method_exists($employee, 'subUnit')) {
                $employee->load('subUnit');
            }

            // Prepare employee data dengan format yang konsisten
            $employeeData = $employee->toArray();
            
            // Convert gender dari database format (L/P) ke display format
            if (isset($employeeData['jenis_kelamin'])) {
                $employeeData['jenis_kelamin'] = $employeeData['jenis_kelamin'] === 'L' ? 'Laki-laki' : 'Perempuan';
            }
            
            // Format dates untuk input type="date"
            $dateFields = ['tanggal_lahir', 'tmt_mulai_jabatan', 'tmt_mulai_kerja', 'tmt_pensiun'];
            foreach ($dateFields as $field) {
                if (isset($employeeData[$field]) && $employeeData[$field]) {
                    $employeeData[$field] = Carbon::parse($employeeData[$field])->format('Y-m-d');
                }
            }
            
            // Get organizations untuk dropdown jika diperlukan
            $organizations = $this->getOrganizationsForFilter();
            
            // Get unit options
            $unitOptions = $this->getUnitOptions();
            
            // Get jabatan options
            $jabatanOptions = $this->getJabatanOptions();

            // Unit Organisasi options dari model Unit
            $unitOrganisasiOptions = Unit::UNIT_ORGANISASI_OPTIONS ?? [
                'EGM', 'GM', 'Airside', 'Landside', 'Back Office', 'SSQC', 'Ancillary'
            ];

            return Inertia::render('Employees/Edit', [
                'employee' => $employeeData,
                'organizations' => $organizations,
                'unitOptions' => $unitOptions,
                'jabatanOptions' => $jabatanOptions,
                'unitOrganisasiOptions' => $unitOrganisasiOptions,
                'statusPegawaiOptions' => self::STATUS_PEGAWAI_OPTIONS,
                'kelompokJabatanOptions' => self::KELOMPOK_JABATAN_OPTIONS,
                'success' => session('success'),
                'error' => session('error'),
            ]);
        } catch (\Exception $e) {
            Log::error('Employee Edit Error', [
                'employee_identifier' => $identifier,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return redirect()->route('employees.index')
                ->with('error', 'Error loading edit form: ' . $e->getMessage());
        }
    }

    /**
     * Update the specified employee in storage
     * FIXED: Parameter menggunakan flexible identifier
     */
    public function update(Request $request, string $identifier)
    {
        try {
            // FIXED: Find by flexible identifier
            $employee = Employee::findByIdentifier($identifier)->first();
            
            if (!$employee) {
                return redirect()->route('employees.index')
                    ->with('error', 'Employee tidak ditemukan.');
            }

            // Get available unit organisasi options
            $unitOrganisasiOptions = Unit::UNIT_ORGANISASI_OPTIONS ?? 
                ['EGM', 'GM', 'Airside', 'Landside', 'Back Office', 'SSQC', 'Ancillary'];

            // Unit yang tidak memerlukan sub unit
            $unitWithoutSubUnits = ['EGM', 'GM'];

            // Validation rules (similar to store but with ignore for unique fields)
            $validator = Validator::make($request->all(), [
                'nik' => [
                    'required',
                    'string',
                    'size:16',
                    'regex:/^[0-9]+$/',
                    Rule::unique('employees', 'nik')->ignore($employee->id, 'id') // FIXED: ignore based on ID
                ],
                'nip' => [
                    'required',
                    'string',
                    'min:5',
                    'regex:/^[0-9]+$/',
                    Rule::unique('employees', 'nip')->ignore($employee->id, 'id') // FIXED: ignore based on ID
                ],
                'nama_lengkap' => 'required|string|min:2|max:200',
                'jenis_kelamin' => [
                    'required',
                    'string',
                    Rule::in(['Laki-laki', 'Perempuan'])
                ],
                'unit_organisasi' => [
                    'required',
                    'string',
                    Rule::in($unitOrganisasiOptions)
                ],
                'unit_id' => [
                    'required',
                    'integer',
                    'exists:units,id'
                ],
                'sub_unit_id' => [
                    'nullable',
                    'integer',
                    function ($attribute, $value, $fail) use ($request, $unitWithoutSubUnits) {
                        if (in_array($request->unit_organisasi, $unitWithoutSubUnits)) {
                            return;
                        }
                        
                        if (!$value) {
                            $fail('Sub unit wajib diisi untuk unit organisasi ' . $request->unit_organisasi . '.');
                        }
                        
                        if ($value && !SubUnit::where('id', $value)->exists()) {
                            $fail('Sub unit yang dipilih tidak valid.');
                        }
                        
                        if ($value && $request->unit_id) {
                            $subUnit = SubUnit::find($value);
                            if ($subUnit && $subUnit->unit_id != $request->unit_id) {
                                $fail('Sub unit tidak sesuai dengan unit yang dipilih.');
                            }
                        }
                    }
                ],
                'nama_jabatan' => 'required|string|max:255',
                'kelompok_jabatan' => ['required', Rule::in(self::KELOMPOK_JABATAN_OPTIONS)],
                'status_pegawai' => ['required', Rule::in(self::STATUS_PEGAWAI_OPTIONS)],
                
                // Optional fields
                'tempat_lahir' => 'nullable|string|max:100',
                'tanggal_lahir' => 'nullable|date|before:today',
                'alamat' => 'nullable|string|max:500',
                'kota_domisili' => 'nullable|string|max:100',
                'jabatan' => 'nullable|string|max:255',
                'tmt_mulai_jabatan' => 'nullable|date',
                'tmt_mulai_kerja' => 'nullable|date',
                
                // Contact fields
                'handphone' => 'nullable|string|max:20|regex:/^[0-9+\-\s()]+$/',
                'email' => [
                    'nullable',
                    'email',
                    'max:255',
                    Rule::unique('employees', 'email')->ignore($employee->id, 'id') // FIXED: ignore based on ID
                ],
                
                // Education fields
                'pendidikan_terakhir' => 'nullable|string|max:50',
                'pendidikan' => 'nullable|string|max:50',
                'instansi_pendidikan' => 'nullable|string|max:255',
                'jurusan' => 'nullable|string|max:100',
                'tahun_lulus' => 'nullable|integer|min:1950|max:' . (date('Y') + 5),
                
                // Physical attributes
                'height' => 'nullable|integer|min:100|max:250',
                'weight' => 'nullable|integer|min:30|max:200',
                'jenis_sepatu' => 'nullable|string|max:50',
                'ukuran_sepatu' => 'nullable|string|max:10',
                'seragam' => 'nullable|string|max:100',
                
                // BPJS fields
                'no_bpjs_kesehatan' => 'nullable|string|max:50',
                'no_bpjs_ketenagakerjaan' => 'nullable|string|max:50',
                
                // TMT fields
                'tmt_pensiun' => 'nullable|date',
            ]);

            if ($validator->fails()) {
                return redirect()->back()
                    ->withErrors($validator->errors())
                    ->withInput()
                    ->with('error', 'Data tidak valid. Silakan periksa kembali form.');
            }

            // Prepare update data
            $updateData = $validator->validated();
            
            // Handle jenis_kelamin conversion
            if (isset($updateData['jenis_kelamin'])) {
                $updateData['jenis_kelamin'] = $updateData['jenis_kelamin'] === 'Laki-laki' ? 'L' : 'P';
            }
            
            // Handle sub_unit_id untuk EGM dan GM
            if (in_array($request->unit_organisasi, $unitWithoutSubUnits) && empty($updateData['sub_unit_id'])) {
                $updateData['sub_unit_id'] = null;
            }

            // Update employee
            DB::beginTransaction();
            
            try {
                $employee->update($updateData);
                DB::commit();

                Log::info('Employee Updated Successfully', [
                    'employee_id' => $employee->id, // FIXED: Use ID
                    'employee_nik' => $employee->nik,
                    'updated_fields' => array_keys($updateData)
                ]);

                return redirect()->route('employees.index')
                    ->with('success', 'Data karyawan berhasil diperbarui!')
                    ->with('notification', [
                        'type' => 'success',
                        'title' => 'Berhasil!',
                        'message' => "Data karyawan {$employee->nama_lengkap} berhasil diperbarui."
                    ]);

            } catch (\Exception $e) {
                DB::rollBack();
                
                Log::error('Employee Update Database Failed', [
                    'employee_id' => $employee->id,
                    'error' => $e->getMessage(),
                    'update_data' => $updateData
                ]);

                return redirect()->back()
                    ->withInput()
                    ->with('error', 'Terjadi kesalahan database saat memperbarui data. Error: ' . $e->getMessage());
            }

        } catch (\Exception $e) {
            Log::error('Employee Update Failed', [
                'employee_identifier' => $identifier,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return redirect()->back()
                ->withInput()
                ->with('error', 'Terjadi kesalahan saat memperbarui data. Silakan coba lagi.');
        }
    }

    /**
     * Remove the specified employee from storage
     * FIXED: Parameter menggunakan flexible identifier
     */
    public function destroy(string $identifier)
    {
        try {
            // FIXED: Find by flexible identifier
            $employee = Employee::findByIdentifier($identifier)->first();
            
            if (!$employee) {
                return redirect()->route('employees.index')
                    ->with('error', 'Employee tidak ditemukan.');
            }

            $employeeName = $employee->nama_lengkap;
            $employeeNik = $employee->nik;

            DB::beginTransaction();
            
            try {
                $employee->delete();
                DB::commit();

                Log::info('Employee Deleted Successfully', [
                    'employee_id' => $employee->id, // FIXED: Use ID
                    'employee_nik' => $employeeNik,
                    'employee_name' => $employeeName
                ]);

                return redirect()->route('employees.index')
                    ->with('success', "Karyawan {$employeeName} berhasil dihapus!")
                    ->with('notification', [
                        'type' => 'success',
                        'title' => 'Berhasil!',
                        'message' => "Data karyawan {$employeeName} berhasil dihapus dari sistem."
                    ]);

            } catch (\Exception $e) {
                DB::rollBack();
                
                Log::error('Employee Delete Database Failed', [
                    'employee_id' => $employee->id,
                    'error' => $e->getMessage()
                ]);

                return redirect()->back()
                    ->with('error', 'Terjadi kesalahan database saat menghapus data. Error: ' . $e->getMessage());
            }

        } catch (\Exception $e) {
            Log::error('Employee Delete Failed', [
                'employee_identifier' => $identifier,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return redirect()->back()
                ->with('error', 'Terjadi kesalahan saat menghapus data. Silakan coba lagi.');
        }
    }

    /**
     * Get employee statistics (API endpoint)
     */
    public function getStatistics()
    {
        try {
            $statistics = [
                'total_employees' => Employee::count(),
                'active_employees' => Employee::where('status', 'active')->count(),
                'inactive_employees' => Employee::where('status', 'inactive')->count(),
                'pegawai_tetap' => Employee::where('status_pegawai', 'PEGAWAI TETAP')->count(),
                'pkwt' => Employee::where('status_pegawai', 'PKWT')->count(),
                'tad_total' => Employee::whereIn('status_pegawai', ['TAD PAKET SDM', 'TAD PAKET PEKERJAAN'])->count(),
                'tad_paket_sdm' => Employee::where('status_pegawai', 'TAD PAKET SDM')->count(),
                'tad_paket_pekerjaan' => Employee::where('status_pegawai', 'TAD PAKET PEKERJAAN')->count(),
                'tad' => Employee::whereIn('status_pegawai', ['TAD', 'TAD PAKET SDM', 'TAD PAKET PEKERJAAN'])->count(),
                'male_employees' => Employee::where('jenis_kelamin', 'L')->count(),
                'female_employees' => Employee::where('jenis_kelamin', 'P')->count(),
                'kelompok_jabatan' => [
                    'supervisor' => Employee::where('kelompok_jabatan', 'SUPERVISOR')->count(),
                    'staff' => Employee::where('kelompok_jabatan', 'STAFF')->count(),
                    'manager' => Employee::where('kelompok_jabatan', 'MANAGER')->count(),
                    'executive_gm' => Employee::where('kelompok_jabatan', 'EXECUTIVE GENERAL MANAGER')->count(),
                    'account_executive' => Employee::where('kelompok_jabatan', 'ACCOUNT EXECUTIVE/AE')->count(),
                ],
                'total_organizations' => $this->getOrganizationCount(),
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

    // =====================================================
    // SAFE PRIVATE HELPER METHODS - FIXED NULL HANDLING
    // =====================================================

    /**
     * Get enhanced statistics with safe null handling
     */
    private function getEnhancedStatistics($filterConditions = [])
    {
        try {
            if (!is_array($filterConditions)) {
                $filterConditions = [];
            }

            // If no filters applied, get global statistics
            if (empty($filterConditions)) {
                $total = Employee::where('status', 'active')->count();
                $pegawaiTetap = Employee::where('status', 'active')->where('status_pegawai', 'PEGAWAI TETAP')->count();
                $pkwt = Employee::where('status', 'active')->where('status_pegawai', 'PKWT')->count();
                
                // TAD Statistics dengan split
                $tadPaketSDM = Employee::where('status', 'active')->where('status_pegawai', 'TAD PAKET SDM')->count();
                $tadPaketPekerjaan = Employee::where('status', 'active')->where('status_pegawai', 'TAD PAKET PEKERJAAN')->count();
                $tadTotal = $tadPaketSDM + $tadPaketPekerjaan;
                
                // Backward compatibility - include legacy TAD
                $tadLegacy = Employee::where('status', 'active')->where('status_pegawai', 'TAD')->count();
                if ($tadLegacy > 0) {
                    $tadTotal += $tadLegacy;
                }
                
                $uniqueUnits = Employee::where('status', 'active')->whereNotNull('unit_organisasi')->distinct()->count('unit_organisasi');
            } else {
                // Apply filters to calculate statistics
                $query = Employee::where('status', 'active');
                
                if (isset($filterConditions['search'])) {
                    $searchTerm = $filterConditions['search'];
                    $query->where(function($q) use ($searchTerm) {
                        $q->where('nik', 'like', "%{$searchTerm}%")
                          ->orWhere('nip', 'like', "%{$searchTerm}%")
                          ->orWhere('nama_lengkap', 'like', "%{$searchTerm}%")
                          ->orWhere('jabatan', 'like', "%{$searchTerm}%")
                          ->orWhere('nama_jabatan', 'like', "%{$searchTerm}%")
                          ->orWhere('unit_organisasi', 'like', "%{$searchTerm}%")
                          ->orWhere('kelompok_jabatan', 'like', "%{$searchTerm}%")
                          ->orWhere('jenis_sepatu', 'like', "%{$searchTerm}%")
                          ->orWhere('ukuran_sepatu', 'like', "%{$searchTerm}%")
                          ->orWhereHas('unit', function ($q) use ($searchTerm) {
                              $q->where('name', 'like', "%{$searchTerm}%");
                          })
                          ->orWhereHas('subUnit', function ($q) use ($searchTerm) {
                              $q->where('name', 'like', "%{$searchTerm}%");
                          });
                    });
                }

                if (isset($filterConditions['status_pegawai'])) {
                    $query->where('status_pegawai', $filterConditions['status_pegawai']);
                }

                if (isset($filterConditions['kelompok_jabatan'])) {
                    $query->where('kelompok_jabatan', $filterConditions['kelompok_jabatan']);
                }

                if (isset($filterConditions['unit_organisasi'])) {
                    $query->where('unit_organisasi', $filterConditions['unit_organisasi']);
                }

                if (isset($filterConditions['unit_id'])) {
                    $query->where('unit_id', $filterConditions['unit_id']);
                }

                if (isset($filterConditions['sub_unit_id'])) {
                    $query->where('sub_unit_id', $filterConditions['sub_unit_id']);
                }

                if (isset($filterConditions['jenis_kelamin'])) {
                    $query->where('jenis_kelamin', $filterConditions['jenis_kelamin']);
                }

                if (isset($filterConditions['jenis_sepatu'])) {
                    $query->where('jenis_sepatu', $filterConditions['jenis_sepatu']);
                }

                if (isset($filterConditions['ukuran_sepatu'])) {
                    $query->where('ukuran_sepatu', $filterConditions['ukuran_sepatu']);
                }

                $total = $query->count();
                $pegawaiTetap = (clone $query)->where('status_pegawai', 'PEGAWAI TETAP')->count();
                $pkwt = (clone $query)->where('status_pegawai', 'PKWT')->count();
                
                // TAD dengan split
                $tadPaketSDM = (clone $query)->where('status_pegawai', 'TAD PAKET SDM')->count();
                $tadPaketPekerjaan = (clone $query)->where('status_pegawai', 'TAD PAKET PEKERJAAN')->count();
                $tadLegacy = (clone $query)->where('status_pegawai', 'TAD')->count();
                $tadTotal = $tadPaketSDM + $tadPaketPekerjaan + $tadLegacy;
                
                $uniqueUnits = (clone $query)->whereNotNull('unit_organisasi')->distinct()->count('unit_organisasi');
            }
            
            // Get new employees count (global, not filtered)
            $newToday = $this->getNewEmployeesToday();

            return [
                'total' => $total,
                'pegawaiTetap' => $pegawaiTetap,
                'pkwt' => $pkwt,
                'tad_total' => $tadTotal,
                'tad_paket_sdm' => $tadPaketSDM,
                'tad_paket_pekerjaan' => $tadPaketPekerjaan,
                'tad' => $tadTotal, // Backward compatibility
                'uniqueUnits' => $uniqueUnits,
                'newToday' => $newToday,
                'activeFilters' => is_array($filterConditions) ? count(array_filter($filterConditions, function($value) {
                    return !is_null($value) && $value !== '' && $value !== 'all';
                })) : 0
            ];

        } catch (\Exception $e) {
            Log::error('Enhanced Statistics Error: ' . $e->getMessage());
            return $this->getDefaultStatistics();
        }
    }

    /**
     * Get filter options with safe null handling
     */
    private function getFilterOptions()
    {
        try {
            return [
                'units' => $this->getUnitOptions(),
                'positions' => $this->getJabatanOptions(),
                'shoe_types' => ['Pantofel', 'Safety Shoes'],
                'shoe_sizes' => $this->getShoeSizeOptions(),
                'status_pegawai' => self::STATUS_PEGAWAI_OPTIONS,
                'kelompok_jabatan' => self::KELOMPOK_JABATAN_OPTIONS,
                'genders' => ['L', 'P'],
            ];
        } catch (\Exception $e) {
            Log::error('Filter Options Error: ' . $e->getMessage());
            return $this->getDefaultFilterOptions();
        }
    }

    /**
     * Get organizations for filter dropdown - SAFE VERSION
     */
    private function getOrganizationsForFilter()
    {
        try {
            if (!class_exists(Organization::class)) {
                return [];
            }

            $organizations = Organization::where('status', 'active')
                ->orderBy('name')
                ->get(['id', 'name', 'code']);
                
            return $organizations ? $organizations->toArray() : [];
                
        } catch (\Exception $e) {
            Log::error('Organizations Filter Error: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Get unique unit options - SAFE VERSION
     */
    private function getUnitOptions()
    {
        try {
            // Try to get from Unit model first
            if (class_exists(Unit::class)) {
                $unitOrganisasiOptions = Unit::UNIT_ORGANISASI_OPTIONS ?? [];
                if (!empty($unitOrganisasiOptions)) {
                    return $unitOrganisasiOptions;
                }
            }

            // Fallback to Employee table
            $units = Employee::whereNotNull('unit_organisasi')
                ->where('unit_organisasi', '!=', '')
                ->distinct()
                ->orderBy('unit_organisasi')
                ->pluck('unit_organisasi');

            if (!$units || $units->isEmpty()) {
                return [
                    'EGM', 'GM', 'Airside', 'Landside', 'Back Office', 'SSQC', 'Ancillary'
                ];
            }

            return $units->toArray();
        } catch (\Exception $e) {
            Log::error('Unit Options Error: ' . $e->getMessage());
            return [
                'EGM', 'GM', 'Airside', 'Landside', 'Back Office', 'SSQC', 'Ancillary'
            ];
        }
    }

    /**
     * Get unique jabatan options - SAFE VERSION
     */
    private function getJabatanOptions()
    {
        try {
            $jabatan = Employee::whereNotNull('nama_jabatan')
                ->where('nama_jabatan', '!=', '')
                ->distinct()
                ->orderBy('nama_jabatan')
                ->pluck('nama_jabatan');

            return $jabatan ? $jabatan->toArray() : [];
        } catch (\Exception $e) {
            Log::error('Jabatan Options Error: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Get unique shoe size options - SAFE VERSION
     */
    private function getShoeSizeOptions()
    {
        try {
            $sizes = Employee::whereNotNull('ukuran_sepatu')
                ->where('ukuran_sepatu', '!=', '')
                ->distinct()
                ->orderBy('ukuran_sepatu')
                ->pluck('ukuran_sepatu');

            return $sizes ? $sizes->toArray() : [];
        } catch (\Exception $e) {
            Log::error('Shoe Size Options Error: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Get new employees today count - SAFE VERSION
     */
    private function getNewEmployeesToday()
    {
        try {
            $today = Carbon::now()->startOfDay();
            $endOfDay = Carbon::now()->endOfDay();

            return Employee::whereBetween('created_at', [$today, $endOfDay])
                          ->where('status', 'active')
                          ->count();
        } catch (\Exception $e) {
            Log::error('New Employees Today Error: ' . $e->getMessage());
            return 0;
        }
    }

    /**
     * Get new employees yesterday count - SAFE VERSION  
     */
    private function getNewEmployeesYesterday()
    {
        try {
            $yesterday = Carbon::now()->subDay()->startOfDay();
            $endOfYesterday = Carbon::now()->subDay()->endOfDay();

            return Employee::whereBetween('created_at', [$yesterday, $endOfYesterday])
                          ->where('status', 'active')
                          ->count();
        } catch (\Exception $e) {
            Log::error('New Employees Yesterday Error: ' . $e->getMessage());
            return 0;
        }
    }

    /**
     * Get new employees this week count - SAFE VERSION
     */
    private function getNewEmployeesThisWeek()
    {
        try {
            $startOfWeek = Carbon::now()->startOfWeek();
            $now = Carbon::now();

            return Employee::whereBetween('created_at', [$startOfWeek, $now])
                          ->where('status', 'active')
                          ->count();
        } catch (\Exception $e) {
            Log::error('New Employees This Week Error: ' . $e->getMessage());
            return 0;
        }
    }

    /**
     * Get notification data - SAFE VERSION
     */
    private function getNotificationData()
    {
        try {
            return [
                'newEmployee' => session('newEmployee'),
                'success' => session('success'),
                'error' => session('error'),
                'message' => session('message'),
                'notification' => session('notification'),
                'alerts' => session('alerts', []),
            ];
        } catch (\Exception $e) {
            Log::error('Notification Data Error: ' . $e->getMessage());
            return [
                'newEmployee' => null,
                'success' => null,
                'error' => null,
                'message' => null,
                'notification' => null,
                'alerts' => [],
            ];
        }
    }

    /**
     * Get organization count - SAFE VERSION
     */
    private function getOrganizationCount()
    {
        try {
            if (class_exists(Organization::class)) {
                return Organization::count();
            }
            
            // Fallback: count unique units from employees
            return Employee::distinct('unit_organisasi')
                ->whereNotNull('unit_organisasi')
                ->count();
        } catch (\Exception $e) {
            Log::error('Organization Count Error: ' . $e->getMessage());
            return 0;
        }
    }

    /**
     * Get time based greeting - SAFE VERSION
     */
    private function getTimeBasedGreeting()
    {
        try {
            if (class_exists(TimezoneHelper::class)) {
                return TimezoneHelper::getTimeBasedGreeting();
            }
            
            $hour = Carbon::now()->hour;
            if ($hour < 12) {
                return ['greeting' => 'Selamat Pagi', 'period' => 'morning'];
            } elseif ($hour < 15) {
                return ['greeting' => 'Selamat Siang', 'period' => 'afternoon'];
            } elseif ($hour < 18) {
                return ['greeting' => 'Selamat Sore', 'period' => 'evening'];
            } else {
                return ['greeting' => 'Selamat Malam', 'period' => 'night'];
            }
        } catch (\Exception $e) {
            Log::error('Time Based Greeting Error: ' . $e->getMessage());
            return ['greeting' => 'Selamat Datang', 'period' => 'day'];
        }
    }

    /**
     * Get business hours status - SAFE VERSION
     */
    private function getBusinessHoursStatus()
    {
        try {
            if (class_exists(TimezoneHelper::class)) {
                return TimezoneHelper::getBusinessHoursStatus();
            }
            
            $hour = Carbon::now()->hour;
            return [
                'isBusinessHours' => $hour >= 8 && $hour < 17,
                'status' => $hour >= 8 && $hour < 17 ? 'open' : 'closed'
            ];
        } catch (\Exception $e) {
            Log::error('Business Hours Status Error: ' . $e->getMessage());
            return ['isBusinessHours' => true, 'status' => 'open'];
        }
    }

    /**
     * Get WITA date - SAFE VERSION
     */
    private function getWitaDate()
    {
        try {
            if (class_exists(TimezoneHelper::class)) {
                return TimezoneHelper::getWitaDate();
            }
            
            return Carbon::now('Asia/Makassar');
        } catch (\Exception $e) {
            Log::error('Get WITA Date Error: ' . $e->getMessage());
            return Carbon::now();
        }
    }

    /**
     * Format Indonesian date - SAFE VERSION
     */
    private function formatIndonesian($date)
    {
        try {
            if (class_exists(TimezoneHelper::class)) {
                return TimezoneHelper::formatIndonesian($date);
            }
            
            return $date->format('d/m/Y H:i');
        } catch (\Exception $e) {
            Log::error('Format Indonesian Error: ' . $e->getMessage());
            return Carbon::now()->format('d/m/Y H:i');
        }
    }

    /**
     * Get default statistics for fallback
     */
    private function getDefaultStatistics()
    {
        return [
            'total' => 0,
            'pegawaiTetap' => 0,
            'pkwt' => 0,
            'tad_total' => 0,
            'tad_paket_sdm' => 0,
            'tad_paket_pekerjaan' => 0,
            'tad' => 0,
            'uniqueUnits' => 0,
            'newToday' => 0,
            'newYesterday' => 0,
            'newThisWeek' => 0,
            'newThisMonth' => 0,
            'activeFilters' => 0
        ];
    }

    /**
     * Get default filter options for fallback
     */
    private function getDefaultFilterOptions()
    {
        return [
            'units' => [],
            'positions' => [],
            'shoe_types' => ['Pantofel', 'Safety Shoes'],
            'shoe_sizes' => [],
            'status_pegawai' => self::STATUS_PEGAWAI_OPTIONS,
            'kelompok_jabatan' => self::KELOMPOK_JABATAN_OPTIONS,
            'genders' => ['L', 'P'],
        ];
    }

    /**
     * Get default pagination for fallback
     */
    private function getDefaultPagination()
    {
        return [
            'current_page' => 1,
            'last_page' => 1,
            'per_page' => 20,
            'total' => 0,
            'from' => null,
            'to' => null,
            'has_pages' => false,
            'has_more_pages' => false,
            'on_first_page' => true,
            'on_last_page' => true,
            'next_page_url' => null,
            'prev_page_url' => null,
            'links' => []
        ];
    }
}