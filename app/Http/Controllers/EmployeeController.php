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
use Illuminate\Support\Facades\Schema;

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
     * FIXED: Get units berdasarkan unit organisasi untuk cascading dropdown
     * Format response: {success: true/false, data: [], message: ''}
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
                    'data' => [],
                    'message' => 'Parameter unit_organisasi tidak ditemukan'
                ]);
            }

            // Validate unit organisasi
            $validUnitOrganisasi = [
                'EGM', 'GM', 'Airside', 'Landside', 'Back Office', 'SSQC', 'Ancillary'
            ];
            
            if (!in_array($unitOrganisasi, $validUnitOrganisasi)) {
                return response()->json([
                    'success' => false,
                    'data' => [],
                    'message' => 'Unit organisasi tidak valid'
                ]);
            }

            // Check if Unit model exists
            if (!class_exists('App\Models\Unit')) {
                // Fallback ke static data jika model tidak ada
                $staticStructure = [
                    'EGM' => ['EGM'],
                    'GM' => ['GM'],
                    'Airside' => ['MO', 'ME'],
                    'Landside' => ['MF', 'MS'],
                    'Back Office' => ['MU', 'MK'],
                    'SSQC' => ['MQ'],
                    'Ancillary' => ['MB'],
                ];
                
                $units = $staticStructure[$unitOrganisasi] ?? [];
                
                if (empty($units)) {
                    return response()->json([
                        'success' => false,
                        'data' => [],
                        'message' => 'Tidak ada unit untuk unit organisasi ini'
                    ]);
                }
                
                $response = array_map(function($unit) {
                    return [
                        'value' => $unit,
                        'label' => $unit,
                        'id' => $unit,
                        'code' => $unit,
                        'name' => $unit
                    ];
                }, $units);
                
                return response()->json([
                    'success' => true,
                    'data' => $response,
                    'message' => 'Units loaded from static data'
                ]);
            }

            // Get units from database
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

            if ($units->isEmpty()) {
                // Fallback ke static data jika database kosong
                $staticStructure = [
                    'EGM' => ['EGM'],
                    'GM' => ['GM'],
                    'Airside' => ['MO', 'ME'],
                    'Landside' => ['MF', 'MS'],
                    'Back Office' => ['MU', 'MK'],
                    'SSQC' => ['MQ'],
                    'Ancillary' => ['MB'],
                ];
                
                $staticUnits = $staticStructure[$unitOrganisasi] ?? [];
                
                if (!empty($staticUnits)) {
                    $response = array_map(function($unit) {
                        return [
                            'value' => $unit,
                            'label' => $unit,
                            'id' => $unit,
                            'code' => $unit,
                            'name' => $unit
                        ];
                    }, $staticUnits);
                    
                    return response()->json([
                        'success' => true,
                        'data' => $response,
                        'message' => 'Units loaded from static fallback'
                    ]);
                }
                
                return response()->json([
                    'success' => false,
                    'data' => [],
                    'message' => 'Tidak ada unit tersedia untuk unit organisasi ini'
                ]);
            }

            $response = $units->map(function($unit) {
                return [
                    'value' => $unit->name,
                    'label' => $unit->name,
                    'id' => $unit->id,
                    'code' => $unit->code,
                    'name' => $unit->name
                ];
            });

            return response()->json([
                'success' => true,
                'data' => $response->toArray(),
                'message' => 'Units loaded successfully'
            ]);

        } catch (\Exception $e) {
            Log::error('Get Units API Error', [
                'unit_organisasi' => $request->get('unit_organisasi'),
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'data' => [],
                'message' => 'Terjadi kesalahan saat mengambil data unit: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * FIXED: Get sub units berdasarkan unit untuk cascading dropdown
     * Format response: {success: true/false, data: [], message: ''}
     */
    public function getSubUnits(Request $request)
    {
        try {
            $unitId = $request->get('unit_id');
            $unitName = $request->get('unit'); // Support both unit_id and unit name
            
            Log::info('Get Sub Units API called', [
                'unit_id' => $unitId,
                'unit_name' => $unitName,
                'request_method' => $request->method(),
                'all_params' => $request->all()
            ]);
            
            if (!$unitId && !$unitName) {
                return response()->json([
                    'success' => false,
                    'data' => [],
                    'message' => 'Parameter unit_id atau unit tidak ditemukan'
                ]);
            }

            // Unit organisasi yang tidak memiliki sub unit
            $unitWithoutSubUnits = ['EGM', 'GM'];
            
            // Check if Unit model exists
            if (!class_exists('App\Models\Unit') || !class_exists('App\Models\SubUnit')) {
                // Fallback ke static data
                $staticStructure = [
                    'MO' => ['Flops', 'Depco', 'Ramp', 'Load Control', 'Load Master', 'ULD Control', 'Cargo Import', 'Cargo Export'],
                    'ME' => ['GSE Operator P/B', 'GSE Operator A/C', 'GSE Maintenance', 'BTT Operator', 'Line Maintenance'],
                    'MF' => ['KLM', 'Qatar', 'Korean Air', 'Vietjet Air', 'Scoot', 'Thai Airways', 'China Airlines', 'China Southern', 'Indigo', 'Xiamen Air', 'Aero Dili', 'Jeju Air', 'Hongkong Airlines', 'Air Busan', 'Vietnam Airlines', 'Sichuan Airlines', 'Aeroflot', 'Charter Flight'],
                    'MS' => ['MPGA', 'QG', 'IP'],
                    'MU' => ['Human Resources & General Affair', 'Fasilitas & Sarana'],
                    'MK' => ['Accounting', 'Budgeting', 'Treassury', 'Tax'],
                    'MQ' => ['Avsec', 'Safety Quality Control'],
                    'MB' => ['GPL', 'GLC', 'Joumpa'],
                ];
                
                $unit = $unitName ?: $unitId;
                $subUnits = $staticStructure[$unit] ?? [];
                
                if (empty($subUnits)) {
                    return response()->json([
                        'success' => true,
                        'data' => [],
                        'message' => 'Unit ini tidak memiliki sub unit'
                    ]);
                }
                
                $response = array_map(function($subUnit) {
                    return [
                        'value' => $subUnit,
                        'label' => $subUnit,
                        'id' => $subUnit,
                        'code' => $subUnit,
                        'name' => $subUnit
                    ];
                }, $subUnits);
                
                return response()->json([
                    'success' => true,
                    'data' => $response,
                    'message' => 'Sub units loaded from static data'
                ]);
            }

            // Cari unit berdasarkan ID atau nama
            $unitRecord = null;
            if ($unitId) {
                $unitRecord = Unit::find($unitId);
            } elseif ($unitName) {
                $unitRecord = Unit::where('name', $unitName)->first();
            }
            
            if (!$unitRecord) {
                return response()->json([
                    'success' => false,
                    'data' => [],
                    'message' => 'Unit tidak ditemukan'
                ]);
            }

            // Check if this unit organisasi should have sub units
            if (in_array($unitRecord->unit_organisasi, $unitWithoutSubUnits)) {
                return response()->json([
                    'success' => true,
                    'data' => [],
                    'message' => 'Unit organisasi ini tidak memiliki sub unit'
                ]);
            }

            // Get sub units from database
            $subUnits = SubUnit::where('unit_id', $unitRecord->id)
                ->where('is_active', true)
                ->select('id', 'name', 'code', 'description', 'unit_id')
                ->orderBy('name')
                ->get();

            Log::info('Sub units retrieved', [
                'unit' => $unitRecord->name,
                'unit_id' => $unitRecord->id,
                'sub_units_count' => $subUnits->count(),
                'sub_units' => $subUnits->pluck('name')->toArray()
            ]);

            if ($subUnits->isEmpty()) {
                // Fallback ke static data
                $staticStructure = [
                    'MO' => ['Flops', 'Depco', 'Ramp', 'Load Control', 'Load Master', 'ULD Control', 'Cargo Import', 'Cargo Export'],
                    'ME' => ['GSE Operator P/B', 'GSE Operator A/C', 'GSE Maintenance', 'BTT Operator', 'Line Maintenance'],
                    'MF' => ['KLM', 'Qatar', 'Korean Air', 'Vietjet Air', 'Scoot', 'Thai Airways', 'China Airlines', 'China Southern', 'Indigo', 'Xiamen Air', 'Aero Dili', 'Jeju Air', 'Hongkong Airlines', 'Air Busan', 'Vietnam Airlines', 'Sichuan Airlines', 'Aeroflot', 'Charter Flight'],
                    'MS' => ['MPGA', 'QG', 'IP'],
                    'MU' => ['Human Resources & General Affair', 'Fasilitas & Sarana'],
                    'MK' => ['Accounting', 'Budgeting', 'Treassury', 'Tax'],
                    'MQ' => ['Avsec', 'Safety Quality Control'],
                    'MB' => ['GPL', 'GLC', 'Joumpa'],
                ];
                
                $staticSubUnits = $staticStructure[$unitRecord->name] ?? [];
                
                if (!empty($staticSubUnits)) {
                    $response = array_map(function($subUnit) {
                        return [
                            'value' => $subUnit,
                            'label' => $subUnit,
                            'id' => $subUnit,
                            'code' => $subUnit,
                            'name' => $subUnit
                        ];
                    }, $staticSubUnits);
                    
                    return response()->json([
                        'success' => true,
                        'data' => $response,
                        'message' => 'Sub units loaded from static fallback'
                    ]);
                }
                
                return response()->json([
                    'success' => true,
                    'data' => [],
                    'message' => 'Unit ini tidak memiliki sub unit'
                ]);
            }

            $response = $subUnits->map(function($subUnit) {
                return [
                    'value' => $subUnit->name,
                    'label' => $subUnit->name,
                    'id' => $subUnit->id,
                    'code' => $subUnit->code,
                    'name' => $subUnit->name
                ];
            });

            return response()->json([
                'success' => true,
                'data' => $response->toArray(),
                'message' => 'Sub units loaded successfully'
            ]);

        } catch (\Exception $e) {
            Log::error('Get Sub Units API Error', [
                'unit_id' => $request->get('unit_id'),
                'unit_name' => $request->get('unit'),
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'data' => [],
                'message' => 'Terjadi kesalahan saat mengambil data sub unit: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * FIXED: Get unit organisasi options untuk dropdown
     */
    public function getUnitOrganisasiOptions()
    {
        try {
            $options = [
                'EGM', 'GM', 'Airside', 'Landside', 'Back Office', 'SSQC', 'Ancillary'
            ];
            
            $response = array_map(function($option) {
                return [
                    'value' => $option,
                    'label' => $option,
                ];
            }, $options);
            
            return response()->json([
                'success' => true,
                'data' => $response,
                'message' => 'Unit organisasi options loaded successfully'
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'data' => [],
                'message' => 'Terjadi kesalahan saat mengambil unit organisasi options: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * DEBUGGING: Get complete units hierarchy untuk debugging
     */
    public function getAllUnitsHierarchy()
    {
        try {
            $hierarchy = [];
            
            $unitOrganisasiOptions = [
                'EGM', 'GM', 'Airside', 'Landside', 'Back Office', 'SSQC', 'Ancillary'
            ];
            
            foreach ($unitOrganisasiOptions as $unitOrg) {
                $hierarchy[$unitOrg] = [
                    'units' => [],
                    'sub_units' => []
                ];
                
                // Get units
                if (class_exists('App\Models\Unit')) {
                    $units = Unit::where('unit_organisasi', $unitOrg)
                        ->where('is_active', true)
                        ->get();
                    
                    foreach ($units as $unit) {
                        $hierarchy[$unitOrg]['units'][] = [
                            'id' => $unit->id,
                            'name' => $unit->name,
                            'code' => $unit->code
                        ];
                        
                        // Get sub units for this unit
                        if (class_exists('App\Models\SubUnit')) {
                            $subUnits = SubUnit::where('unit_id', $unit->id)
                                ->where('is_active', true)
                                ->get();
                            
                            $hierarchy[$unitOrg]['sub_units'][$unit->name] = $subUnits->map(function($subUnit) {
                                return [
                                    'id' => $subUnit->id,
                                    'name' => $subUnit->name,
                                    'code' => $subUnit->code
                                ];
                            })->toArray();
                        }
                    }
                }
            }
            
            return response()->json([
                'success' => true,
                'data' => $hierarchy,
                'message' => 'Complete hierarchy loaded'
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'data' => [],
                'message' => 'Error loading hierarchy: ' . $e->getMessage()
            ]);
        }
    }

    // =====================================================
    // MAIN CRUD METHODS - ENHANCED FOR PERFECT SEARCH/FILTER
    // =====================================================

    /**
     * ENHANCED: Index method dengan search/filter functionality yang sempurna
     */
    public function index(Request $request)
    {
        try {
            // Log request untuk debugging
            Log::info('Employee Index Request', [
                'filters' => $request->only([
                    'search', 'status_pegawai', 'kelompok_jabatan', 'unit_organisasi', 
                    'unit_id', 'sub_unit_id', 'jenis_kelamin', 'jenis_sepatu', 'ukuran_sepatu'
                ]),
                'page' => $request->get('page', 1),
                'per_page' => $request->get('per_page', 20)
            ]);

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

            // Filter untuk status aktif (jika field 'status' ada)
            if (Schema::hasColumn('employees', 'status')) {
                $query->where('status', 'active');
            }

            // Initialize filter conditions untuk statistics
            $filterConditions = [];

            // ENHANCED: Global search dengan field yang lebih lengkap
            if ($request->filled('search')) {
                $searchTerm = trim($request->search);
                
                if (!empty($searchTerm)) {
                    $query->where(function($q) use ($searchTerm) {
                        $q->where('nama_lengkap', 'like', "%{$searchTerm}%")
                          ->orWhere('nip', 'like', "%{$searchTerm}%")
                          ->orWhere('nik', 'like', "%{$searchTerm}%")
                          ->orWhere('nama_jabatan', 'like', "%{$searchTerm}%")
                          ->orWhere('unit_organisasi', 'like', "%{$searchTerm}%")
                          ->orWhere('jenis_sepatu', 'like', "%{$searchTerm}%")
                          ->orWhere('ukuran_sepatu', 'like', "%{$searchTerm}%")
                          ->orWhere('tempat_lahir', 'like', "%{$searchTerm}%")
                          ->orWhere('alamat', 'like', "%{$searchTerm}%")
                          ->orWhere('handphone', 'like', "%{$searchTerm}%")
                          ->orWhere('kelompok_jabatan', 'like', "%{$searchTerm}%");
                          
                        // Search pada organization name jika relationship ada
                        if (method_exists(Employee::class, 'organization')) {
                            $q->orWhereHas('organization', function($orgQuery) use ($searchTerm) {
                                $orgQuery->where('name', 'like', "%{$searchTerm}%");
                            });
                        }
                        
                        // Search pada unit name jika relationship ada
                        if (method_exists(Employee::class, 'unit')) {
                            $q->orWhereHas('unit', function($unitQuery) use ($searchTerm) {
                                $unitQuery->where('name', 'like', "%{$searchTerm}%");
                            });
                        }
                        
                        // Search pada sub unit name jika relationship ada
                        if (method_exists(Employee::class, 'subUnit')) {
                            $q->orWhereHas('subUnit', function($subUnitQuery) use ($searchTerm) {
                                $subUnitQuery->where('name', 'like', "%{$searchTerm}%");
                            });
                        }
                    });
                    
                    $filterConditions['search'] = $searchTerm;
                }
            }

            // FIXED: Individual filters dengan validation yang lebih baik
            if ($request->filled('status_pegawai') && $request->status_pegawai !== 'all') {
                $query->where('status_pegawai', $request->status_pegawai);
                $filterConditions['status_pegawai'] = $request->status_pegawai;
            }

            if ($request->filled('kelompok_jabatan') && $request->kelompok_jabatan !== 'all') {
                $query->where('kelompok_jabatan', $request->kelompok_jabatan);
                $filterConditions['kelompok_jabatan'] = $request->kelompok_jabatan;
            }

            if ($request->filled('unit_organisasi') && $request->unit_organisasi !== 'all') {
                $query->where('unit_organisasi', $request->unit_organisasi);
                $filterConditions['unit_organisasi'] = $request->unit_organisasi;
            }

            // ENHANCED: Unit filter berdasarkan unit name atau ID dengan error handling
            if ($request->filled('unit_id') && $request->unit_id !== 'all') {
                if (is_numeric($request->unit_id)) {
                    $query->where('unit_id', $request->unit_id);
                } else {
                    // Jika bukan numeric, cari berdasarkan nama unit dalam relationship
                    try {
                        if (method_exists(Employee::class, 'unit')) {
                            $query->whereHas('unit', function ($unitQuery) use ($request) {
                                $unitQuery->where('name', $request->unit_id)
                                         ->orWhere('code', $request->unit_id);
                            });
                        } else {
                            // Fallback: cari berdasarkan kolom terkait jika ada
                            if (Schema::hasColumn('employees', 'unit_name')) {
                                $query->where('unit_name', $request->unit_id);
                            }
                        }
                    } catch (\Exception $e) {
                        Log::warning('Unit filter skipped: ' . $e->getMessage());
                    }
                }
                $filterConditions['unit_id'] = $request->unit_id;
            }

            // ENHANCED: Sub unit filter berdasarkan sub unit name atau ID dengan error handling
            if ($request->filled('sub_unit_id') && $request->sub_unit_id !== 'all') {
                if (is_numeric($request->sub_unit_id)) {
                    $query->where('sub_unit_id', $request->sub_unit_id);
                } else {
                    // Jika bukan numeric, cari berdasarkan nama sub unit dalam relationship
                    try {
                        if (method_exists(Employee::class, 'subUnit')) {
                            $query->whereHas('subUnit', function ($subUnitQuery) use ($request) {
                                $subUnitQuery->where('name', $request->sub_unit_id)
                                            ->orWhere('code', $request->sub_unit_id);
                            });
                        } else {
                            // Fallback: cari berdasarkan kolom terkait jika ada
                            if (Schema::hasColumn('employees', 'sub_unit_name')) {
                                $query->where('sub_unit_name', $request->sub_unit_id);
                            }
                        }
                    } catch (\Exception $e) {
                        Log::warning('SubUnit filter skipped: ' . $e->getMessage());
                    }
                }
                $filterConditions['sub_unit_id'] = $request->sub_unit_id;
            }

            // FIXED: Filter lainnya dengan validasi
            if ($request->filled('jenis_kelamin') && $request->jenis_kelamin !== 'all') {
                $query->where('jenis_kelamin', $request->jenis_kelamin);
                $filterConditions['jenis_kelamin'] = $request->jenis_kelamin;
            }

            if ($request->filled('jenis_sepatu') && $request->jenis_sepatu !== 'all') {
                $query->where('jenis_sepatu', $request->jenis_sepatu);
                $filterConditions['jenis_sepatu'] = $request->jenis_sepatu;
            }

            if ($request->filled('ukuran_sepatu') && $request->ukuran_sepatu !== 'all') {
                $query->where('ukuran_sepatu', $request->ukuran_sepatu);
                $filterConditions['ukuran_sepatu'] = $request->ukuran_sepatu;
            }

            // Pagination dengan validation
            $perPage = min(max((int) $request->get('per_page', 20), 5), 100);
            
            // Apply ordering untuk hasil yang konsisten
            $employees = $query->orderBy('created_at', 'desc')
                             ->orderBy('nama_lengkap', 'asc')
                             ->paginate($perPage)
                             ->withQueryString();

            // Calculate statistics with applied filters
            $statistics = $this->getEnhancedStatistics($filterConditions);

            // Get organizations for filter dropdown
            $organizations = $this->getOrganizationsForFilter();

            // FIXED: Get filter options dari database yang sebenarnya
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

            // Log results untuk debugging
            Log::info('Employee Index Results', [
                'total_found' => $employees->total(),
                'current_page' => $employees->currentPage(),
                'active_filters' => count(array_filter($filterConditions))
            ]);

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
            Log::error('Employee Index Error: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
                'request' => $request->all()
            ]);

            return redirect()->back()
                ->with('error', 'Terjadi kesalahan saat memuat data karyawan. Silakan coba lagi.');
        }
    }

    /**
     * ENHANCED: Method untuk API search (jika diperlukan untuk AJAX)
     */
    public function search(Request $request)
    {
        try {
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

            // Filter untuk status aktif
            if (Schema::hasColumn('employees', 'status')) {
                $query->where('status', 'active');
            }

            // Apply all the same filters as index method
            $filterConditions = [];

            // Global search
            if ($request->filled('search')) {
                $searchTerm = trim($request->search);
                
                if (!empty($searchTerm)) {
                    $query->where(function($q) use ($searchTerm) {
                        $q->where('nama_lengkap', 'like', "%{$searchTerm}%")
                          ->orWhere('nip', 'like', "%{$searchTerm}%")
                          ->orWhere('nik', 'like', "%{$searchTerm}%")
                          ->orWhere('nama_jabatan', 'like', "%{$searchTerm}%")
                          ->orWhere('unit_organisasi', 'like', "%{$searchTerm}%");
                    });
                    $filterConditions['search'] = $searchTerm;
                }
            }

            // Apply the same individual filters
            foreach (['status_pegawai', 'kelompok_jabatan', 'unit_organisasi', 'unit_id', 'sub_unit_id', 'jenis_kelamin', 'jenis_sepatu', 'ukuran_sepatu'] as $filterKey) {
                if ($request->filled($filterKey) && $request->$filterKey !== 'all') {
                    $query->where($filterKey, $request->$filterKey);
                    $filterConditions[$filterKey] = $request->$filterKey;
                }
            }

            $perPage = min(max((int) $request->get('per_page', 20), 5), 100);
            
            $employees = $query->orderBy('created_at', 'desc')
                             ->orderBy('nama_lengkap', 'asc')
                             ->paginate($perPage)
                             ->withQueryString();

            // Return JSON response untuk AJAX calls
            if ($request->expectsJson()) {
                return response()->json([
                    'employees' => $employees,
                    'statistics' => $this->getEnhancedStatistics($filterConditions),
                    'success' => true
                ]);
            }

            // Redirect ke index untuk non-JSON requests
            return redirect()->route('employees.index', $request->all());

        } catch (\Exception $e) {
            Log::error('Employee Search Error: ' . $e->getMessage());
            
            if ($request->expectsJson()) {
                return response()->json([
                    'error' => 'Terjadi kesalahan saat melakukan pencarian',
                    'success' => false
                ], 500);
            }
            
            return redirect()->back()->with('error', 'Terjadi kesalahan saat melakukan pencarian');
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
                // Create employee
                $employee = Employee::create($employeeData);
                
                // Commit transaction
                DB::commit();

                Log::info('Employee Created Successfully', [
                    'employee_id' => $employee->id,
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
                            'id' => $employee->id,
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
                    Rule::unique('employees', 'nik')->ignore($employee->id, 'id')
                ],
                'nip' => [
                    'required',
                    'string',
                    'min:5',
                    'regex:/^[0-9]+$/',
                    Rule::unique('employees', 'nip')->ignore($employee->id, 'id')
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
                    Rule::unique('employees', 'email')->ignore($employee->id, 'id')
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
                    'employee_id' => $employee->id,
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
                    'employee_id' => $employee->id,
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
     * FIXED: Enhanced filter options dengan data dari database
     */
    public function getFilterOptions()
    {
        try {
            return [
                'status_pegawai' => Employee::select('status_pegawai')
                    ->whereNotNull('status_pegawai')
                    ->distinct()
                    ->orderBy('status_pegawai')
                    ->pluck('status_pegawai')
                    ->toArray(),

                'unit_organisasi' => Employee::select('unit_organisasi')
                    ->whereNotNull('unit_organisasi')
                    ->distinct()
                    ->orderBy('unit_organisasi')
                    ->pluck('unit_organisasi')
                    ->toArray(),

                'kelompok_jabatan' => Employee::select('kelompok_jabatan')
                    ->whereNotNull('kelompok_jabatan')
                    ->distinct()
                    ->orderBy('kelompok_jabatan')
                    ->pluck('kelompok_jabatan')
                    ->toArray(),

                'jenis_kelamin' => ['L', 'P'],
                'jenis_sepatu' => ['Pantofel', 'Safety Shoes'],
                'ukuran_sepatu' => ['36', '37', '38', '39', '40', '41', '42', '43', '44'],

                // FIXED: Unit dan sub unit options dari database
                'units' => $this->getUnitsForFilter(),
                'sub_units' => $this->getSubUnitsForFilter(),
            ];
        } catch (\Exception $e) {
            return [
                'status_pegawai' => [],
                'unit_organisasi' => ['EGM', 'GM', 'Airside', 'Landside', 'Back Office', 'SSQC', 'Ancillary'],
                'kelompok_jabatan' => [],
                'jenis_kelamin' => ['L', 'P'],
                'jenis_sepatu' => ['Pantofel', 'Safety Shoes'],
                'ukuran_sepatu' => ['36', '37', '38', '39', '40', '41', '42', '43', '44'],
                'units' => [],
                'sub_units' => [],
            ];
        }
    }

    /**
     * FIXED: Get units dari database untuk filter
     */
    public function getUnitsForFilter()
    {
        try {
            if (!class_exists('App\Models\Unit')) {
                return [];
            }

            return Unit::select('id', 'name', 'code', 'unit_organisasi')
                      ->where('is_active', true)
                      ->orderBy('unit_organisasi')
                      ->orderBy('name')
                      ->get()
                      ->map(function($unit) {
                          return [
                              'id' => $unit->id,
                              'name' => $unit->name,
                              'code' => $unit->code,
                              'unit_organisasi' => $unit->unit_organisasi,
                              'label' => $unit->name,
                          ];
                      })
                      ->toArray();
        } catch (\Exception $e) {
            return [];
        }
    }

    /**
     * FIXED: Get sub units dari database untuk filter
     */
    public function getSubUnitsForFilter()
    {
        try {
            if (!class_exists('App\Models\SubUnit')) {
                return [];
            }

            return SubUnit::with('unit')
                          ->where('is_active', true)
                          ->orderBy('name')
                          ->get()
                          ->map(function($subUnit) {
                              return [
                                  'id' => $subUnit->id,
                                  'name' => $subUnit->name,
                                  'code' => $subUnit->code,
                                  'unit_id' => $subUnit->unit_id,
                                  'unit_name' => $subUnit->unit ? $subUnit->unit->name : '',
                                  'unit_organisasi' => $subUnit->unit ? $subUnit->unit->unit_organisasi : '',
                                  'label' => $subUnit->name,
                              ];
                          })
                          ->toArray();
        } catch (\Exception $e) {
            return [];
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
    // SAFE PRIVATE HELPER METHODS - ENHANCED NULL HANDLING
    // =====================================================

    /**
     * ENHANCED: Get enhanced statistics with safe null handling and comprehensive filter support
     */
    private function getEnhancedStatistics($filterConditions = [])
    {
        try {
            if (!is_array($filterConditions)) {
                $filterConditions = [];
            }

            // If no filters applied, get global statistics
            if (empty($filterConditions)) {
                $baseQuery = Employee::query();
                if (Schema::hasColumn('employees', 'status')) {
                    $baseQuery->where('status', 'active');
                }
                
                $total = $baseQuery->count();
                $pegawaiTetap = (clone $baseQuery)->where('status_pegawai', 'PEGAWAI TETAP')->count();
                $pkwt = (clone $baseQuery)->where('status_pegawai', 'PKWT')->count();
                
                // TAD Statistics dengan split
                $tadPaketSDM = (clone $baseQuery)->where('status_pegawai', 'TAD PAKET SDM')->count();
                $tadPaketPekerjaan = (clone $baseQuery)->where('status_pegawai', 'TAD PAKET PEKERJAAN')->count();
                $tadTotal = $tadPaketSDM + $tadPaketPekerjaan;
                
                // Backward compatibility - include legacy TAD
                $tadLegacy = (clone $baseQuery)->where('status_pegawai', 'TAD')->count();
                if ($tadLegacy > 0) {
                    $tadTotal += $tadLegacy;
                }
                
                $uniqueUnits = (clone $baseQuery)->whereNotNull('unit_organisasi')->distinct()->count('unit_organisasi');
            } else {
                // Apply filters to calculate statistics
                $query = Employee::query();
                if (Schema::hasColumn('employees', 'status')) {
                    $query->where('status', 'active');
                }
                
                // ENHANCED: Apply filters menggunakan enhanced search logic
                if (isset($filterConditions['search'])) {
                    $searchTerm = $filterConditions['search'];
                    $query->where(function($q) use ($searchTerm) {
                        $q->where('nama_lengkap', 'like', "%{$searchTerm}%")
                          ->orWhere('nip', 'like', "%{$searchTerm}%")
                          ->orWhere('nik', 'like', "%{$searchTerm}%")
                          ->orWhere('nama_jabatan', 'like', "%{$searchTerm}%")
                          ->orWhere('unit_organisasi', 'like', "%{$searchTerm}%")
                          ->orWhere('kelompok_jabatan', 'like', "%{$searchTerm}%")
                          ->orWhere('jenis_sepatu', 'like', "%{$searchTerm}%")
                          ->orWhere('ukuran_sepatu', 'like', "%{$searchTerm}%")
                          ->orWhere('tempat_lahir', 'like', "%{$searchTerm}%")
                          ->orWhere('alamat', 'like', "%{$searchTerm}%")
                          ->orWhere('handphone', 'like', "%{$searchTerm}%");
                          
                        // Try relationship search with error handling
                        try {
                            if (method_exists(Employee::class, 'unit')) {
                                $q->orWhereHas('unit', function ($unitQuery) use ($searchTerm) {
                                    $unitQuery->where('name', 'like', "%{$searchTerm}%")
                                             ->orWhere('code', 'like', "%{$searchTerm}%");
                                });
                            }
                            
                            if (method_exists(Employee::class, 'subUnit')) {
                                $q->orWhereHas('subUnit', function ($subUnitQuery) use ($searchTerm) {
                                    $subUnitQuery->where('name', 'like', "%{$searchTerm}%")
                                                ->orWhere('code', 'like', "%{$searchTerm}%");
                                });
                            }
                        } catch (\Exception $e) {
                            // Skip relationship search if models don't exist
                            Log::warning('Statistics relationship search skipped: ' . $e->getMessage());
                        }
                    });
                }

                // Apply other filters
                foreach (['status_pegawai', 'kelompok_jabatan', 'unit_organisasi', 'unit_id', 'sub_unit_id', 'jenis_kelamin', 'jenis_sepatu', 'ukuran_sepatu'] as $filterKey) {
                    if (isset($filterConditions[$filterKey])) {
                        $query->where($filterKey, $filterConditions[$filterKey]);
                    }
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
     * Get new employees today count - SAFE VERSION
     */
    private function getNewEmployeesToday()
    {
        try {
            $today = Carbon::now()->startOfDay();
            $endOfDay = Carbon::now()->endOfDay();

            $query = Employee::whereBetween('created_at', [$today, $endOfDay]);
            if (Schema::hasColumn('employees', 'status')) {
                $query->where('status', 'active');
            }

            return $query->count();
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

            $query = Employee::whereBetween('created_at', [$yesterday, $endOfYesterday]);
            if (Schema::hasColumn('employees', 'status')) {
                $query->where('status', 'active');
            }

            return $query->count();
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

            $query = Employee::whereBetween('created_at', [$startOfWeek, $now]);
            if (Schema::hasColumn('employees', 'status')) {
                $query->where('status', 'active');
            }

            return $query->count();
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