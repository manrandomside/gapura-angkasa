<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use App\Models\Organization;
use App\Models\Unit; // TAMBAHAN BARU
use App\Models\SubUnit; // TAMBAHAN BARU
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

    /**
     * Get unit organisasi options
     */
    public function getUnitOrganisasiOptions()
    {
        try {
            $unitOrganisasi = Unit::UNIT_ORGANISASI_OPTIONS ?? [
                'EGM', 'GM', 'Airside', 'Landside', 'Back Office', 'SSQC', 'Ancillary'
            ];

            Log::info('Unit Organisasi Options retrieved', [
                'options_count' => count($unitOrganisasi),
                'options' => $unitOrganisasi
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Unit organisasi options retrieved successfully',
                'data' => $unitOrganisasi
            ]);

        } catch (\Exception $e) {
            Log::error('Get Unit Organisasi Options Error', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error retrieving unit organisasi options',
                'data' => ['EGM', 'GM', 'Airside', 'Landside', 'Back Office', 'SSQC', 'Ancillary']
            ], 500);
        }
    }

    /**
     * Get all unit data in hierarchical structure
     */
    public function getAllUnitsHierarchy()
    {
        try {
            $hierarchy = [];
            
            foreach (Unit::UNIT_ORGANISASI_OPTIONS as $unitOrganisasi) {
                $units = Unit::active()
                    ->byUnitOrganisasi($unitOrganisasi)
                    ->with(['subUnits' => function($query) {
                        $query->active()->orderBy('name');
                    }])
                    ->orderBy('name')
                    ->get();

                if ($units->count() > 0) {
                    $hierarchy[$unitOrganisasi] = $units;
                }
            }

            return response()->json([
                'success' => true,
                'message' => 'Unit hierarchy retrieved successfully',
                'data' => $hierarchy
            ]);

        } catch (\Exception $e) {
            Log::error('Get Units Hierarchy Error', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error retrieving unit hierarchy',
                'data' => []
            ], 500);
        }
    }

    /**
     * Get unit statistics for monitoring
     */
    public function getUnitStatistics()
    {
        try {
            $statistics = [
                'total_unit_organisasi' => count(Unit::UNIT_ORGANISASI_OPTIONS),
                'total_units' => Unit::active()->count(),
                'total_sub_units' => SubUnit::active()->count(),
                'employees_with_units' => Employee::whereNotNull('unit_id')->count(),
                'employees_with_sub_units' => Employee::whereNotNull('sub_unit_id')->count(),
                'breakdown_by_unit_organisasi' => []
            ];

            // Get breakdown per unit organisasi
            foreach (Unit::UNIT_ORGANISASI_OPTIONS as $unitOrganisasi) {
                $unitCount = Unit::active()->byUnitOrganisasi($unitOrganisasi)->count();
                $subUnitCount = SubUnit::active()
                    ->whereHas('unit', function($query) use ($unitOrganisasi) {
                        $query->where('unit_organisasi', $unitOrganisasi);
                    })
                    ->count();
                
                $employeeCount = Employee::whereHas('unit', function($query) use ($unitOrganisasi) {
                    $query->where('unit_organisasi', $unitOrganisasi);
                })->count();

                $statistics['breakdown_by_unit_organisasi'][$unitOrganisasi] = [
                    'units' => $unitCount,
                    'sub_units' => $subUnitCount,
                    'employees' => $employeeCount
                ];
            }

            return response()->json([
                'success' => true,
                'message' => 'Unit statistics retrieved successfully',
                'data' => $statistics
            ]);

        } catch (\Exception $e) {
            Log::error('Get Unit Statistics Error', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error retrieving unit statistics',
                'data' => []
            ], 500);
        }
    }

    // =====================================================
    // MAIN CRUD METHODS - UPDATED FOR NIK PRIMARY KEY
    // =====================================================

    /**
     * Display a listing of employees with enhanced search, filter, and pagination capabilities
     * UPDATED: Support TAD Split dan Kelompok Jabatan filters dengan Safe Error Handling + Unit/SubUnit filters
     */
    public function index(Request $request)
    {
        try {
            // Build query with relationships - UPDATED: Include unit dan subUnit
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
                    $q->where('nik', 'like', "%{$searchTerm}%") // UPDATED: Search NIK instead of id
                      ->orWhere('nip', 'like', "%{$searchTerm}%")
                      ->orWhere('nama_lengkap', 'like', "%{$searchTerm}%")
                      ->orWhere('jabatan', 'like', "%{$searchTerm}%")
                      ->orWhere('nama_jabatan', 'like', "%{$searchTerm}%")
                      ->orWhere('unit_organisasi', 'like', "%{$searchTerm}%")
                      ->orWhere('kelompok_jabatan', 'like', "%{$searchTerm}%") // BARU: Search kelompok jabatan
                      ->orWhere('jenis_sepatu', 'like', "%{$searchTerm}%")
                      ->orWhere('ukuran_sepatu', 'like', "%{$searchTerm}%")
                      // TAMBAHAN BARU: Search dalam unit dan sub unit
                      ->orWhereHas('unit', function ($q) use ($searchTerm) {
                          $q->where('name', 'like', "%{$searchTerm}%");
                      })
                      ->orWhereHas('subUnit', function ($q) use ($searchTerm) {
                          $q->where('name', 'like', "%{$searchTerm}%");
                      });
                });
                $filterConditions['search'] = $searchTerm;
            }

            // Filter by status pegawai - UPDATED: Support TAD Split
            if ($request->filled('status_pegawai') && $request->status_pegawai !== 'all') {
                $query->where('status_pegawai', $request->status_pegawai);
                $filterConditions['status_pegawai'] = $request->status_pegawai;
            }

            // Filter by kelompok jabatan - FITUR BARU
            if ($request->filled('kelompok_jabatan') && $request->kelompok_jabatan !== 'all') {
                $query->where('kelompok_jabatan', $request->kelompok_jabatan);
                $filterConditions['kelompok_jabatan'] = $request->kelompok_jabatan;
            }

            // Filter by unit organisasi
            if ($request->filled('unit_organisasi') && $request->unit_organisasi !== 'all') {
                $query->where('unit_organisasi', $request->unit_organisasi);
                $filterConditions['unit_organisasi'] = $request->unit_organisasi;
            }

            // Filter by unit - TAMBAHAN BARU
            if ($request->filled('unit_id') && $request->unit_id !== 'all') {
                $query->where('unit_id', $request->unit_id);
                $filterConditions['unit_id'] = $request->unit_id;
            }

            // Filter by sub unit - TAMBAHAN BARU
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

            // Clone query for statistics calculation before pagination
            $statisticsQuery = clone $query;

            // Set per page count
            $perPage = $request->get('per_page', 20);
            
            // Validate per_page parameter
            if (!in_array($perPage, [10, 20, 50, 100])) {
                $perPage = 20;
            }

            // ENHANCED: Order by created_at desc to show newest first (for real-time update)
            $employees = $query->orderBy('created_at', 'desc')
                             ->orderBy('nama_lengkap', 'asc')
                             ->paginate($perPage)
                             ->withQueryString(); // Preserve query parameters in pagination links

            // ENHANCED: Calculate statistics with WITA timezone support - UPDATED untuk TAD Split
            $statistics = $this->getEnhancedStatistics($filterConditions);

            // Get organizations for filter dropdown - SAFE VERSION
            $organizations = $this->getOrganizationsForFilter();

            // Get filter options for dropdowns - UPDATED: Include kelompok jabatan
            $filterOptions = $this->getFilterOptions();

            // ENHANCED: Get new employees data for notifications (WITA timezone) - SAFE VERSION
            $newEmployeesToday = $this->getNewEmployeesToday();
            $newEmployeesYesterday = $this->getNewEmployeesYesterday();
            $newEmployeesThisWeek = $this->getNewEmployeesThisWeek();

            // Get notification data from session (if exists) - SAFE VERSION
            $notificationData = $this->getNotificationData();

            // Get current WITA time information - SAFE VERSION
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
                    'kelompok_jabatan', // BARU: Filter kelompok jabatan
                    'unit_organisasi', 
                    'unit_id', // TAMBAHAN BARU
                    'sub_unit_id', // TAMBAHAN BARU
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
                
                // ENHANCED: Comprehensive notification data with WITA timezone
                'notifications' => [
                    'session' => $notificationData,
                    'newToday' => $newEmployeesToday,
                    'newYesterday' => $newEmployeesYesterday,
                    'newThisWeek' => $newEmployeesThisWeek,
                    'timeInfo' => $timeInfo,
                    'businessHours' => $businessHours,
                    'witaTime' => $this->formatIndonesian($this->getWitaDate()),
                ],

                // Legacy compatibility (keep existing)
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
                    'unit_id', // TAMBAHAN BARU
                    'sub_unit_id', // TAMBAHAN BARU
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
     * UPDATED: Include kelompok jabatan dan status pegawai options dengan Safe Handling + Unit Organisasi Options
     */
    public function create()
    {
        try {
            $organizations = $this->getOrganizationsForFilter();
            
            // Enhanced unit options with fallbacks
            $unitOptions = $this->getUnitOptions();
            
            // Enhanced jabatan options
            $jabatanOptions = $this->getJabatanOptions();
            
            // Unit Organisasi options dari model Unit - TAMBAHAN BARU
            $unitOrganisasiOptions = Unit::UNIT_ORGANISASI_OPTIONS ?? [
                'EGM', 'GM', 'Airside', 'Landside', 'Back Office', 'SSQC', 'Ancillary'
            ];
            
            return Inertia::render('Employees/Create', [
                'organizations' => $organizations,
                'unitOptions' => $unitOptions,
                'jabatanOptions' => $jabatanOptions,
                'unitOrganisasiOptions' => $unitOrganisasiOptions, // TAMBAHAN BARU
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
     * FIXED: CONDITIONAL SUB UNIT VALIDATION - Enhanced validation untuk struktur organisasi dan TMT Pensiun calculation
     * FIXED: Return Inertia compatible response instead of JSON
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

            // Enhanced validation rules with conditional sub_unit_id validation
            $validator = Validator::make($request->all(), [
                // Required basic fields
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
                    'min:6',
                    'regex:/^[0-9]+$/',
                    'unique:employees,nip'
                ],
                'nama_lengkap' => 'required|string|min:2|max:100',
                'jenis_kelamin' => 'required|in:Laki-laki,Perempuan',
                
                // Unit organisasi validation
                'unit_organisasi' => [
                    'required',
                    'string',
                    Rule::in($unitOrganisasiOptions)
                ],
                'unit_id' => [
                    'required',
                    'integer',
                    'exists:units,id',
                    function ($attribute, $value, $fail) use ($request) {
                        if ($value && $request->unit_organisasi) {
                            $unit = Unit::find($value);
                            if (!$unit || $unit->unit_organisasi !== $request->unit_organisasi) {
                                $fail('Unit tidak sesuai dengan unit organisasi yang dipilih.');
                            }
                        }
                    }
                ],
                
                // FIXED: Conditional sub_unit_id validation
                'sub_unit_id' => [
                    function ($attribute, $value, $fail) use ($request, $unitWithoutSubUnits) {
                        // Jika unit organisasi adalah EGM atau GM, sub unit tidak wajib
                        if (in_array($request->unit_organisasi, $unitWithoutSubUnits)) {
                            // Sub unit boleh kosong untuk EGM dan GM
                            if ($value && !is_numeric($value)) {
                                $fail('Sub unit harus berupa angka yang valid.');
                            }
                            if ($value && !SubUnit::where('id', $value)->exists()) {
                                $fail('Sub unit yang dipilih tidak valid.');
                            }
                        } else {
                            // Untuk unit organisasi lain, sub unit wajib diisi
                            if (!$value) {
                                $fail('Sub unit wajib dipilih untuk unit organisasi ini.');
                            }
                            if ($value && !is_numeric($value)) {
                                $fail('Sub unit harus berupa angka yang valid.');
                            }
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
                
                // Optional fields dengan validasi
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
                'tmt_pensiun' => 'nullable|date|after:today',
                'pendidikan_terakhir' => 'nullable|string|max:50',
                'instansi_pendidikan' => 'nullable|string|max:200',
                'jurusan' => 'nullable|string|max:100',
                'tahun_lulus' => 'nullable|integer|min:1950|max:' . (date('Y') + 5),
                'jenis_sepatu' => 'nullable|in:Pantofel,Safety Shoes',
                'ukuran_sepatu' => 'nullable|integer|min:30|max:50',
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
                'nip.min' => 'NIP minimal 6 digit.',
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

            // FIXED: Return validation errors using Inertia redirect back
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
            
            // Handle sub_unit_id untuk EGM dan GM (set null jika kosong)
            if (in_array($request->unit_organisasi, $unitWithoutSubUnits) && empty($employeeData['sub_unit_id'])) {
                $employeeData['sub_unit_id'] = null;
            }

            // Set default values
            $employeeData['status'] = 'active';
            $employeeData['organization_id'] = 1; // Default organization

            // Calculate TMT Pensiun (56 tahun dari tanggal lahir)
            if (isset($employeeData['tanggal_lahir'])) {
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

            // Create employee
            DB::beginTransaction();
            
            $employee = Employee::create($employeeData);
            
            DB::commit();

            Log::info('Employee Created Successfully', [
                'employee_id' => $employee->id,
                'nik' => $employee->nik,
                'nip' => $employee->nip,
                'nama_lengkap' => $employee->nama_lengkap,
                'unit_organisasi' => $employee->unit_organisasi,
                'has_sub_unit' => !is_null($employee->sub_unit_id)
            ]);

            // FIXED: Return Inertia redirect with success message
            return redirect()->route('employees.index')
                ->with('success', 'Karyawan berhasil ditambahkan!')
                ->with('notification', [
                    'type' => 'success',
                    'title' => 'Berhasil!',
                    'message' => "Karyawan {$employee->nama_lengkap} berhasil ditambahkan ke sistem.",
                    'newEmployee' => [
                        'id' => $employee->id,
                        'nik' => $employee->nik,
                        'nip' => $employee->nip,
                        'nama_lengkap' => $employee->nama_lengkap,
                        'unit_organisasi' => $employee->unit_organisasi,
                        'created_at' => $employee->created_at->format('d/m/Y H:i:s')
                    ]
                ]);

        } catch (\Exception $e) {
            DB::rollBack();
            
            Log::error('Employee Store Failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'request_data' => $request->except(['password'])
            ]);

            // FIXED: Return Inertia redirect with error message
            return redirect()->back()
                ->withInput()
                ->with('error', 'Terjadi kesalahan saat menyimpan data. Silakan coba lagi.')
                ->with('notification', [
                    'type' => 'error',
                    'title' => 'Gagal Menyimpan',
                    'message' => 'Terjadi kesalahan sistem. Silakan coba lagi atau hubungi administrator.'
                ]);
        }
    }

    /**
     * Display the specified employee
     * UPDATED: Parameter sekarang menggunakan NIK (string) bukan ID auto-increment
     */
    public function show(string $nik)
    {
        try {
            // UPDATED: Find by NIK instead of auto-increment ID
            $employee = Employee::where('nik', $nik)->firstOrFail();
            
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
                'employee_nik' => $nik,
                'error' => $e->getMessage()
            ]);

            return redirect()->route('employees.index')
                ->with('error', 'Error loading employee details: ' . $e->getMessage());
        }
    }

    /**
     * Show the form for editing the specified employee
     * UPDATED: Parameter menggunakan NIK, Include kelompok jabatan dan status pegawai options + Unit Organisasi Options
     */
    public function edit(string $nik)
    {
        try {
            // UPDATED: Find by NIK instead of auto-increment ID
            $employee = Employee::where('nik', $nik)->firstOrFail();
            
            // Load relationships - TAMBAHAN BARU
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

            // Unit Organisasi options dari model Unit - TAMBAHAN BARU
            $unitOrganisasiOptions = Unit::UNIT_ORGANISASI_OPTIONS ?? [
                'EGM', 'GM', 'Airside', 'Landside', 'Back Office', 'SSQC', 'Ancillary'
            ];
            
            return Inertia::render('Employees/Edit', [
                'employee' => $employeeData,
                'organizations' => $organizations,
                'unitOptions' => $unitOptions,
                'jabatanOptions' => $jabatanOptions,
                'unitOrganisasiOptions' => $unitOrganisasiOptions, // TAMBAHAN BARU
                'statusPegawaiOptions' => self::STATUS_PEGAWAI_OPTIONS,
                'kelompokJabatanOptions' => self::KELOMPOK_JABATAN_OPTIONS,
                'title' => 'Edit Karyawan',
                'subtitle' => "Edit data karyawan {$employee->nama_lengkap}",
                'success' => session('success'),
                'error' => session('error'),
                'message' => session('message'),
            ]);
            
        } catch (\Exception $e) {
            Log::error('Employee Edit Error', [
                'employee_nik' => $nik,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return redirect()->route('employees.index')
                ->with([
                    'error' => 'Gagal memuat form edit karyawan.',
                    'notification' => [
                        'type' => 'error',
                        'title' => 'Gagal Memuat Data',
                        'message' => 'Terjadi kesalahan saat memuat form edit karyawan.'
                    ]
                ]);
        }
    }

    /**
     * Update the specified employee
     * UPDATED: Parameter menggunakan NIK, NIK tidak bisa diubah, NIP bisa diubah + Unit/SubUnit handling
     * FIXED: CONDITIONAL SUB UNIT VALIDATION untuk konsistensi dengan store method
     */
    public function update(Request $request, string $nik)
    {
        try {
            // UPDATED: Find by NIK instead of auto-increment ID
            $employee = Employee::where('nik', $nik)->firstOrFail();
            $originalData = $employee->toArray();

            // Get available unit organisasi options - TAMBAHAN BARU
            $unitOrganisasiOptions = Unit::UNIT_ORGANISASI_OPTIONS ?? [
                'EGM', 'GM', 'Airside', 'Landside', 'Back Office', 'SSQC', 'Ancillary'
            ];

            // Unit organisasi yang tidak memiliki sub unit
            $unitWithoutSubUnits = ['EGM', 'GM'];

            // FIXED: CONDITIONAL SUB UNIT VALIDATION - Validation untuk update - NIK tidak bisa diubah, NIP bisa diubah
            $validator = Validator::make($request->all(), [
                // NIP bisa diubah tapi harus tetap unique (kecuali untuk employee ini)
                'nip' => [
                    'required',
                    'string',
                    'min:6',
                    'max:20',
                    'regex:/^[0-9]+$/',
                    Rule::unique('employees', 'nip')->ignore($employee->nik, 'nik') // UPDATED: ignore based on NIK
                ],
                
                // Required fields
                'nama_lengkap' => 'required|string|min:2|max:255',
                'jenis_kelamin' => 'required|in:Laki-laki,Perempuan,L,P',
                'unit_organisasi' => ['required', 'string', 'max:100', Rule::in($unitOrganisasiOptions)],
                'unit_id' => 'required|exists:units,id',
                
                // FIXED: Conditional sub unit validation
                'sub_unit_id' => [
                    function ($attribute, $value, $fail) use ($request, $unitWithoutSubUnits) {
                        // Jika unit organisasi adalah EGM atau GM, sub unit tidak wajib
                        if (in_array($request->unit_organisasi, $unitWithoutSubUnits)) {
                            // Sub unit boleh kosong untuk EGM dan GM
                            if ($value && !is_numeric($value)) {
                                $fail('Sub unit harus berupa angka yang valid.');
                            }
                            if ($value && !SubUnit::where('id', $value)->exists()) {
                                $fail('Sub unit yang dipilih tidak valid.');
                            }
                        } else {
                            // Untuk unit organisasi lain, sub unit wajib diisi
                            if (!$value) {
                                $fail('Sub unit wajib dipilih untuk unit organisasi ini.');
                            }
                            if ($value && !is_numeric($value)) {
                                $fail('Sub unit harus berupa angka yang valid.');
                            }
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
                    Rule::unique('employees', 'email')->ignore($employee->nik, 'nik') // UPDATED: ignore based on NIK
                ],
                
                // Education fields
                'pendidikan_terakhir' => 'nullable|string|max:50',
                'pendidikan' => 'nullable|string|max:50',
                'instansi_pendidikan' => 'nullable|string|max:255',
                'jurusan' => 'nullable|string|max:100',
                'tahun_lulus' => 'nullable|integer|min:1950|max:' . (date('Y') + 5),
                
                // Physical data
                'jenis_sepatu' => 'nullable|in:Pantofel,Safety Shoes',
                'ukuran_sepatu' => 'nullable|integer|min:30|max:50',
                'height' => 'nullable|integer|min:100|max:250',
                'weight' => 'nullable|integer|min:30|max:200',
                
                // BPJS
                'no_bpjs_kesehatan' => 'nullable|string|max:20',
                'no_bpjs_ketenagakerjaan' => 'nullable|string|max:20',
                
                // Additional
                'seragam' => 'nullable|string|max:10',
                'organization_id' => 'nullable|exists:organizations,id',
            ], [
                'nip.required' => 'NIP wajib diisi',
                'nip.min' => 'NIP minimal 6 digit',
                'nip.regex' => 'NIP hanya boleh berisi angka',
                'nip.unique' => 'NIP sudah terdaftar di sistem',
                'unit_organisasi.required' => 'Unit organisasi wajib dipilih',
                'unit_id.required' => 'Unit wajib dipilih',
                // ... other custom messages
            ]);

            if ($validator->fails()) {
                return redirect()->back()
                    ->withErrors($validator)
                    ->withInput()
                    ->with([
                        'error' => 'Data tidak valid. Mohon periksa kembali.',
                        'notification' => [
                            'type' => 'error',
                            'title' => 'Validasi Gagal',
                            'message' => 'Mohon periksa kembali data yang diisi'
                        ]
                    ]);
            }

            $data = $validator->validated();
            
            // IMPORTANT: NIK tidak boleh diubah (tidak ada dalam validated data)
            // NIP boleh diubah sesuai validasi di atas
            
            // Convert gender to database format (L/P)
            if (isset($data['jenis_kelamin'])) {
                if (in_array($data['jenis_kelamin'], ['L', 'Laki-laki'])) {
                    $data['jenis_kelamin'] = 'L';
                } else {
                    $data['jenis_kelamin'] = 'P';
                }
            }

            // Handle sub_unit_id untuk EGM dan GM
            if (in_array($request->unit_organisasi, $unitWithoutSubUnits) && empty($data['sub_unit_id'])) {
                $data['sub_unit_id'] = null;
            }

            // REVISI: Enhanced TMT Pensiun calculation dengan logika baru
            if (isset($data['tanggal_lahir']) && $data['tanggal_lahir'] !== $originalData['tanggal_lahir']) {
                $birthDate = Carbon::parse($data['tanggal_lahir']);
                $data['usia'] = $birthDate->age;
                
                // REVISI: Logika TMT Pensiun berdasarkan aturan baru
                $pensionYear = $birthDate->year + 56;
                
                if ($birthDate->day < 10) {
                    // Lahir dibawah tanggal 10: pensiun 1 pada bulan yang sama
                    $pensionDate = Carbon::createFromDate($pensionYear, $birthDate->month, 1);
                } else {
                    // Lahir diatas tanggal 10: pensiun 1 bulan berikutnya
                    $pensionDate = Carbon::createFromDate($pensionYear, $birthDate->month, 1);
                    $pensionDate->addMonth(); // Tambah 1 bulan
                }
                
                $data['tmt_pensiun'] = $pensionDate->format('Y-m-d');
            } elseif (isset($data['tanggal_lahir'])) {
                $data['usia'] = Carbon::parse($data['tanggal_lahir'])->age;
            }

            // Handle jabatan field consistency
            if (!isset($data['jabatan']) && isset($data['nama_jabatan'])) {
                $data['jabatan'] = $data['nama_jabatan'];
            }

            // Validasi unit consistency
            if (!empty($data['unit_id'])) {
                $unit = Unit::find($data['unit_id']);
                if (!$unit || $unit->unit_organisasi !== $data['unit_organisasi']) {
                    return redirect()->back()
                        ->withInput()
                        ->withErrors(['unit_id' => 'Unit tidak sesuai dengan unit organisasi yang dipilih'])
                        ->with([
                            'error' => 'Unit tidak sesuai dengan unit organisasi yang dipilih',
                            'notification' => [
                                'type' => 'error',
                                'title' => 'Validasi Gagal',
                                'message' => 'Unit tidak sesuai dengan unit organisasi yang dipilih'
                            ]
                        ]);
                }
            }

            // Validasi sub unit consistency (hanya jika required)
            if (!in_array($data['unit_organisasi'], $unitWithoutSubUnits) && !empty($data['sub_unit_id'])) {
                $subUnit = SubUnit::find($data['sub_unit_id']);
                if (!$subUnit || $subUnit->unit_id != $data['unit_id']) {
                    return redirect()->back()
                        ->withInput()
                        ->withErrors(['sub_unit_id' => 'Sub unit tidak sesuai dengan unit yang dipilih'])
                        ->with([
                            'error' => 'Sub unit tidak sesuai dengan unit yang dipilih',
                            'notification' => [
                                'type' => 'error',
                                'title' => 'Validasi Gagal',
                                'message' => 'Sub unit tidak sesuai dengan unit yang dipilih'
                            ]
                        ]);
                }
            }

            // Update employee data
            $employee->update($data);

            // Log the update
            Log::info('Employee Updated Successfully', [
                'employee_nik' => $employee->nik,
                'employee_nip' => $employee->nip,
                'nama_lengkap' => $employee->nama_lengkap,
                'updated_fields' => array_keys(array_diff_assoc($data, $originalData)),
                'unit_organisasi' => $employee->unit_organisasi,
                'unit_id' => $employee->unit_id,
                'sub_unit_id' => $employee->sub_unit_id,
                'kelompok_jabatan' => $employee->kelompok_jabatan,
                'status_pegawai' => $employee->status_pegawai,
                'tmt_pensiun_updated' => isset($data['tmt_pensiun']) ? 'Yes' : 'No',
            ]);

            return redirect()->route('employees.index')
                ->with([
                    'success' => 'Data karyawan berhasil diperbarui!',
                    'message' => "Data karyawan {$employee->nama_lengkap} berhasil diperbarui.",
                    'notification' => [
                        'type' => 'success',
                        'title' => 'Data Berhasil Diperbarui',
                        'message' => "Data karyawan {$employee->nama_lengkap} berhasil diperbarui."
                    ]
                ]);

        } catch (\Exception $e) {
            Log::error('Employee Update Error', [
                'employee_nik' => $nik,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return redirect()->back()
                ->withInput()
                ->with([
                    'error' => 'Terjadi kesalahan sistem. Silakan coba lagi.',
                    'notification' => [
                        'type' => 'error',
                        'title' => 'Kesalahan Sistem',
                        'message' => 'Terjadi kesalahan sistem. Silakan coba lagi.'
                    ]
                ]);
        }
    }

    /**
     * Remove the specified employee (soft delete)
     * UPDATED: Parameter menggunakan NIK
     */
    public function destroy(string $nik)
    {
        try {
            // UPDATED: Find by NIK instead of auto-increment ID
            $employee = Employee::where('nik', $nik)->firstOrFail();
            
            $employeeName = $employee->nama_lengkap;
            $employeeNip = $employee->nip;
            $employeeNik = $employee->nik;
            
            // Soft delete by setting status to inactive
            $employee->update(['status' => 'inactive']);

            Log::info('Employee Deleted Successfully', [
                'employee_nik' => $employeeNik, // UPDATED: Use NIK as primary identifier
                'employee_nip' => $employeeNip,
                'nama_lengkap' => $employeeName
            ]);

            return redirect()->route('employees.index')
                ->with([
                    'success' => "Karyawan {$employeeName} berhasil dihapus!",
                    'message' => "Karyawan {$employeeName} (NIK: {$employeeNik}) berhasil dihapus dari sistem.",
                    'notification' => [
                        'type' => 'warning',
                        'title' => 'Karyawan Dihapus',
                        'message' => "Karyawan {$employeeName} (NIK: {$employeeNik}) berhasil dihapus dari sistem."
                    ]
                ]);
        } catch (\Exception $e) {
            Log::error('Employee Delete Error', [
                'employee_nik' => $nik,
                'error' => $e->getMessage()
            ]);

            return redirect()->route('employees.index')
                ->with('error', 'Error deleting employee: ' . $e->getMessage());
        }
    }

    /**
     * Get employee statistics (API endpoint) - UPDATED: Include TAD Split dan Kelompok Jabatan
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
                // TAD Statistics dengan split - FITUR BARU
                'tad_total' => Employee::whereIn('status_pegawai', ['TAD PAKET SDM', 'TAD PAKET PEKERJAAN'])->count(),
                'tad_paket_sdm' => Employee::where('status_pegawai', 'TAD PAKET SDM')->count(),
                'tad_paket_pekerjaan' => Employee::where('status_pegawai', 'TAD PAKET PEKERJAAN')->count(),
                // Backward compatibility
                'tad' => Employee::whereIn('status_pegawai', ['TAD', 'TAD PAKET SDM', 'TAD PAKET PEKERJAAN'])->count(),
                'male_employees' => Employee::where('jenis_kelamin', 'L')->count(),
                'female_employees' => Employee::where('jenis_kelamin', 'P')->count(),
                // Kelompok Jabatan statistics - FITUR BARU
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
     * Get enhanced statistics with safe null handling - UPDATED untuk TAD Split + Unit/SubUnit filters
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
                
                // TAD Statistics dengan split - FITUR BARU
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
                        $q->where('nik', 'like', "%{$searchTerm}%") // UPDATED: Search NIK instead of id
                          ->orWhere('nip', 'like', "%{$searchTerm}%")
                          ->orWhere('nama_lengkap', 'like', "%{$searchTerm}%")
                          ->orWhere('jabatan', 'like', "%{$searchTerm}%")
                          ->orWhere('nama_jabatan', 'like', "%{$searchTerm}%")
                          ->orWhere('unit_organisasi', 'like', "%{$searchTerm}%")
                          ->orWhere('kelompok_jabatan', 'like', "%{$searchTerm}%")
                          ->orWhere('jenis_sepatu', 'like', "%{$searchTerm}%")
                          ->orWhere('ukuran_sepatu', 'like', "%{$searchTerm}%")
                          // TAMBAHAN BARU: Search dalam unit dan sub unit
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

                // TAMBAHAN BARU: Unit dan Sub Unit filters
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
     * Get filter options with safe null handling - UPDATED: Include kelompok jabatan
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
     * Get unique unit options - SAFE VERSION - UPDATED: Use Unit model if available
     */
    private function getUnitOptions()
    {
        try {
            // Try to get from Unit model first - TAMBAHAN BARU
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
     * Build success notification - UPDATED: Enhanced with TMT Pensiun explanation
     */
    private function buildSuccessNotification($employee)
    {
        try {
            // Generate TMT Pensiun explanation
            $pensionExplanation = '';
            if ($employee->tanggal_lahir && $employee->tmt_pensiun) {
                $birthDate = Carbon::parse($employee->tanggal_lahir);
                $pensionDate = Carbon::parse($employee->tmt_pensiun);
                
                if ($birthDate->day < 10) {
                    $pensionExplanation = " (lahir tanggal {$birthDate->day}, pensiun 1 {$pensionDate->format('F Y')})";
                } else {
                    $pensionExplanation = " (lahir tanggal {$birthDate->day}, pensiun 1 {$pensionDate->format('F Y')})";
                }
            }

            return [
                'success' => 'Karyawan berhasil ditambahkan!',
                'newEmployee' => $employee,
                'message' => "Karyawan {$employee->nama_lengkap} dengan NIK {$employee->nik} telah berhasil ditambahkan ke sistem. TMT Pensiun telah dihitung otomatis{$pensionExplanation}.",
                'notification' => [
                    'type' => 'success',
                    'title' => 'Karyawan Baru Ditambahkan!',
                    'message' => "Karyawan {$employee->nama_lengkap} berhasil ditambahkan dengan NIK {$employee->nik}. TMT Pensiun: " . ($employee->tmt_pensiun ? Carbon::parse($employee->tmt_pensiun)->format('d/m/Y') : 'N/A'),
                    'employee_nik' => $employee->nik,
                    'duration' => 5000,
                ],
                'alerts' => [
                    [
                        'type' => 'success',
                        'message' => 'Data karyawan telah tersimpan dan muncul dalam daftar dengan TMT Pensiun yang telah dihitung otomatis.',
                        'duration' => 4000
                    ]
                ]
            ];
        } catch (\Exception $e) {
            Log::error('Build Success Notification Error: ' . $e->getMessage());
            return [
                'success' => 'Karyawan berhasil ditambahkan!',
                'message' => 'Data karyawan telah berhasil disimpan.',
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