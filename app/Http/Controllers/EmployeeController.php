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
     * KELOMPOK JABATAN CONSTANTS - UPDATED WITH GENERAL MANAGER AND NON
     */
    const KELOMPOK_JABATAN_OPTIONS = [
        'ACCOUNT EXECUTIVE/AE',
        'EXECUTIVE GENERAL MANAGER',
        'GENERAL MANAGER',
        'MANAGER',
        'STAFF',
        'SUPERVISOR',
        'NON'
    ];

    /**
     * PROVIDER OPTIONS CONSTANTS - NEW
     */
    const PROVIDER_OPTIONS = [
        'PT Gapura Angkasa',
        'PT Air Box Personalia',
        'PT Finfleet Teknologi Indonesia',
        'PT Mitra Angkasa Perdana',
        'PT Safari Dharma Sakti',
        'PT Grha Humanindo Management',
        'PT Duta Griya Sarana',
        'PT Aerotrans Wisata',
        'PT Mandala Garda Nusantara',
        'PT Kidora Mandiri Investama'
    ];

    /**
     * STATUS KERJA OPTIONS CONSTANTS - NEW
     */
    const STATUS_KERJA_OPTIONS = [
        'Aktif',
        'Non-Aktif',
        'Pensiun',
        'Mutasi'
    ];

    /**
     * FIXED: Unit display mapping untuk format KODE SAJA - konsisten dengan dashboard grafik
     * CRITICAL CHANGE: Hanya return kode unit, bukan nama panjang
     */
    private function getUnitDisplayMapping()
    {
        return [
            'EGM' => 'EGM',
            'GM' => 'GM',
            'MO' => 'MO',
            'ME' => 'ME',
            'MF' => 'MF',
            'MS' => 'MS',
            'MU' => 'MU',
            'MK' => 'MK',
            'MQ' => 'MQ',
            'MB' => 'MB',
        ];
    }

    /**
     * FIXED: Mapping kode unit ke nama organisasi berdasarkan data seeder sebenarnya
     * Sesuai dengan field kode_organisasi dan nama_organisasi di seeder
     */
    private function getUnitCodeToNameMapping()
    {
        return [
            'MO' => 'OPERATION SERVICES',
            'ME' => 'MAINTENANCE SERVICES', 
            'MF' => 'FLIGHT SERVICES',
            'MS' => 'MOVEMENT SERVICES',
            'MU' => 'MANAGEMENT UNIT',
            'MK' => 'FINANCE',
            'MQ' => 'QUALITY SERVICES',
            'MB' => 'BUSINESS SERVICES',
            'EGM' => 'EGM',
            'GM' => 'GM'
        ];
    }

    /**
     * UPDATED: Unit code mapping untuk format display - SIMPLIFIED ke kode saja
     * CRITICAL CHANGE: Mapping langsung ke kode unit, bukan nama panjang
     */
    private function getUnitCodeMapping()
    {
        return [
            'Airside' => [
                'Movement Operations' => 'MO',
                'Maintenance Equipment' => 'ME',
            ],
            'Landside' => [
                'Movement Flight' => 'MF',
                'Movement Service' => 'MS',
            ],
            'Back Office' => [
                'Management Keuangan' => 'MK',
                'Management Unit' => 'MU',
            ],
            'SSQC' => [
                'Management Quality' => 'MQ',
            ],
            'Ancillary' => [
                'Management Business' => 'MB',
            ],
        ];
    }

    /**
     * FIXED: Format employee unit display dengan KODE SAJA untuk konsistensi dengan dashboard
     * CRITICAL CHANGE: Hanya return kode unit, bukan format "(XX) Nama Unit"
     */
    private function formatEmployeeUnitDisplay($employee)
    {
        try {
            // Priority 1: gunakan kode_organisasi jika tersedia (data terbaru)
            if (!empty($employee->kode_organisasi)) {
                $unitCode = $employee->kode_organisasi;
                $mapping = $this->getUnitDisplayMapping();
                return $mapping[$unitCode] ?? $unitCode; // Return kode saja
            }
            
            // Priority 2: gunakan unit relationship dengan fallback ke kode
            if (!empty($employee->unit_organisasi) && method_exists($employee, 'unit') && $employee->unit) {
                // Coba ambil kode dari unit relationship
                if (!empty($employee->unit->code)) {
                    return $employee->unit->code;
                }
            }
            
            // Priority 3: gunakan mapping dari unit_organisasi ke kode default
            if (!empty($employee->unit_organisasi)) {
                return $this->getUnitCodeFromUnitOrganisasi($employee->unit_organisasi);
            }
            
            return 'Unit tidak tersedia';
            
        } catch (\Exception $e) {
            Log::warning('Format employee unit display error: ' . $e->getMessage());
            return $employee->unit_organisasi ?? 'Unit tidak tersedia';
        }
    }

    /**
     * FIXED: Helper method untuk format unit display dengan KODE SAJA
     * CRITICAL CHANGE: Hanya return kode unit, bukan format "(XX) Nama Unit"
     */
    private function formatUnitWithCode($unitName, $unitOrganisasi)
    {
        $mapping = $this->getUnitCodeMapping();
        
        if (isset($mapping[$unitOrganisasi][$unitName])) {
            return $mapping[$unitOrganisasi][$unitName]; // Return kode saja
        }
        
        // Fallback untuk unit tanpa mapping (EGM, GM) - return as is
        if (in_array($unitName, ['EGM', 'GM'])) {
            return $unitName;
        }
        
        // Last fallback
        return $unitName;
    }

    /**
     * FIXED: Helper method untuk format unit berdasarkan kode_organisasi - KODE SAJA
     * CRITICAL CHANGE: Hanya return kode unit
     */
    private function formatUnitForDisplay($unitCode)
    {
        $mapping = $this->getUnitDisplayMapping();
        return $mapping[$unitCode] ?? $unitCode; // Return kode saja
    }

    /**
     * NEW: Helper untuk mapping unit_organisasi ke kode unit default
     */
    private function getUnitCodeFromUnitOrganisasi($unitOrganisasi)
    {
        $mapping = [
            'EGM' => 'EGM',
            'GM' => 'GM',
            'Airside' => 'MO', // Default Airside ke MO
            'Landside' => 'MF', // Default Landside ke MF
            'Back Office' => 'MU', // Default Back Office ke MU
            'SSQC' => 'MQ',
            'Ancillary' => 'MB'
        ];
        
        return $mapping[$unitOrganisasi] ?? $unitOrganisasi;
    }

    /**
     * HELPER: Calculate masa kerja between two dates - PROPERLY FIXED VERSION
     * CRITICAL FIX: Use DateTime diff() instead of Carbon diffInYears/diffInMonths
     */
    private function calculateMasaKerja($tmtMulaiKerja, $tmtBerakhirKerja = null)
    {
        if (!$tmtMulaiKerja || empty($tmtMulaiKerja)) {
            Log::info('Calculate Masa Kerja: Empty tmt_mulai_kerja', [
                'tmt_mulai_kerja' => $tmtMulaiKerja,
                'tmt_berakhir_kerja' => $tmtBerakhirKerja
            ]);
            return "-";
        }

        try {
            $startDate = Carbon::parse($tmtMulaiKerja);
            $endDate = $tmtBerakhirKerja ? Carbon::parse($tmtBerakhirKerja) : Carbon::now('Asia/Makassar');

            // Validate dates
            if ($endDate < $startDate) {
                Log::warning('Calculate Masa Kerja: End date before start date', [
                    'tmt_mulai_kerja' => $tmtMulaiKerja,
                    'tmt_berakhir_kerja' => $tmtBerakhirKerja
                ]);
                return "Tanggal berakhir sebelum tanggal mulai";
            }

            // FIXED: Use DateTime diff() method to get proper DateInterval
            $interval = $startDate->diff($endDate);
            
            $years = $interval->y;
            $months = $interval->m;
            $days = $interval->d;

            // Format output
            if ($years > 0 && $months > 0) {
                $result = "{$years} tahun {$months} bulan";
            } else if ($years > 0) {
                $result = "{$years} tahun";
            } else if ($months > 0) {
                $result = "{$months} bulan";
            } else {
                // Check if it's at least a few days
                if ($days > 0) {
                    $result = "Kurang dari 1 bulan";
                } else {
                    $result = "Belum ada masa kerja";
                }
            }

            Log::info('Calculate Masa Kerja: Success', [
                'tmt_mulai_kerja' => $tmtMulaiKerja,
                'tmt_berakhir_kerja' => $tmtBerakhirKerja,
                'calculated_masa_kerja' => $result,
                'years' => $years,
                'months' => $months,
                'days' => $days
            ]);

            return $result;

        } catch (\Exception $e) {
            Log::error('Calculate Masa Kerja Error', [
                'tmt_mulai_kerja' => $tmtMulaiKerja,
                'tmt_berakhir_kerja' => $tmtBerakhirKerja,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return "Error dalam perhitungan";
        }
    }

    // =====================================================
    // UNIT/SUBUNIT API METHODS - UPDATED WITH UNIT CODE FORMAT ONLY
    // =====================================================

    /**
     * FIXED: Get units berdasarkan unit organisasi untuk cascading dropdown
     * CRITICAL CHANGE: Response hanya berisi kode unit, bukan nama panjang
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
                // FIXED: Fallback ke static data dengan KODE UNIT SAJA
                $staticStructure = [
                    'EGM' => ['EGM'],
                    'GM' => ['GM'],
                    'Airside' => ['MO', 'ME'], // Return kode saja
                    'Landside' => ['MF', 'MS'], // Return kode saja
                    'Back Office' => ['MU', 'MK'], // Return kode saja
                    'SSQC' => ['MQ'], // Return kode saja
                    'Ancillary' => ['MB'], // Return kode saja
                ];
                
                $units = $staticStructure[$unitOrganisasi] ?? [];
                
                if (empty($units)) {
                    return response()->json([
                        'success' => false,
                        'data' => [],
                        'message' => 'Tidak ada unit untuk unit organisasi ini'
                    ]);
                }
                
                // FIXED: Format unit dengan KODE SAJA
                $response = array_map(function($unitCode) use ($unitOrganisasi) {
                    return [
                        'value' => $unitCode, // Kode saja
                        'label' => $unitCode, // Kode saja
                        'id' => $unitCode,
                        'code' => $unitCode,
                        'name' => $unitCode, // Konsistensi: name juga kode
                        'original_name' => $unitCode
                    ];
                }, $units);
                
                return response()->json([
                    'success' => true,
                    'data' => $response,
                    'message' => 'Units loaded from static data with code format only'
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
                // FIXED: Fallback ke static data dengan KODE SAJA
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
                    $response = array_map(function($unitCode) use ($unitOrganisasi) {
                        return [
                            'value' => $unitCode, // Kode saja
                            'label' => $unitCode, // Kode saja
                            'id' => $unitCode,
                            'code' => $unitCode,
                            'name' => $unitCode, // Konsistensi: name juga kode
                            'original_name' => $unitCode
                        ];
                    }, $staticUnits);
                    
                    return response()->json([
                        'success' => true,
                        'data' => $response,
                        'message' => 'Units loaded from static fallback with code format only'
                    ]);
                }
                
                return response()->json([
                    'success' => false,
                    'data' => [],
                    'message' => 'Tidak ada unit tersedia untuk unit organisasi ini'
                ]);
            }

            // FIXED: Format units dari database dengan KODE SAJA
            // Berdasarkan perubahan UnitSeeder, unit.name sekarang sudah berisi kode
            $response = $units->map(function($unit) {
                return [
                    'value' => $unit->name, // Unit.name sekarang sudah kode
                    'label' => $unit->name, // Unit.name sekarang sudah kode
                    'id' => $unit->id,
                    'code' => $unit->code,
                    'name' => $unit->name, // Unit.name sekarang sudah kode
                    'original_name' => $unit->name
                ];
            });

            return response()->json([
                'success' => true,
                'data' => $response->toArray(),
                'message' => 'Units loaded successfully with code format only'
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
     * FIXED: Helper method untuk mendapatkan kode unit - SIMPLIFIED
     */
    private function getUnitCode($unitName, $unitOrganisasi)
    {
        $mapping = $this->getUnitCodeMapping();
        
        if (isset($mapping[$unitOrganisasi][$unitName])) {
            return $mapping[$unitOrganisasi][$unitName];
        }
        
        // Fallback untuk unit tanpa mapping
        return $unitName;
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
                // Fallback ke static data dengan mapping kode unit
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
                // FIXED: Cari berdasarkan name yang sekarang sudah kode atau code field
                $unitRecord = Unit::where('name', $unitName)->orWhere('code', $unitName)->first();
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
                // Fallback ke static data dengan mapping unit code
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
                
                $staticSubUnits = $staticStructure[$unitRecord->name] ?? $staticStructure[$unitRecord->code] ?? [];
                
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
                        // FIXED: Gunakan kode saja, bukan format "(XX) Nama Unit"
                        $unitCode = $unit->name; // Unit.name sekarang sudah kode
                        $hierarchy[$unitOrg]['units'][] = [
                            'id' => $unit->id,
                            'name' => $unit->name, // Sudah kode
                            'formatted_name' => $unitCode, // Kode saja
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
                'message' => 'Complete hierarchy loaded with unit code format only'
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
     * CRITICAL FIX: Enhanced Index method dengan masa_kerja calculation untuk setiap employee
     */
    public function index(Request $request)
    {
        try {
            // Log request untuk debugging
            Log::info('Employee Index Request', [
                'filters' => $request->only([
                    'search', 'status_pegawai', 'kelompok_jabatan', 'unit_organisasi', 
                    'unit_id', 'sub_unit_id', 'jenis_kelamin', 'jenis_sepatu', 'ukuran_sepatu',
                    'provider', 'status_kerja', 'grade' // NEW FILTERS
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

            // ENHANCED: Global search dengan field yang lebih lengkap + NEW FIELDS
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
                          ->orWhere('kelompok_jabatan', 'like', "%{$searchTerm}%")
                          // NEW: Search in new fields
                          ->orWhere('provider', 'like', "%{$searchTerm}%")
                          ->orWhere('unit_kerja_kontrak', 'like', "%{$searchTerm}%")
                          ->orWhere('grade', 'like', "%{$searchTerm}%")
                          ->orWhere('status_kerja', 'like', "%{$searchTerm}%")
                          ->orWhere('lokasi_kerja', 'like', "%{$searchTerm}%")
                          ->orWhere('cabang', 'like', "%{$searchTerm}%");
                          
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

            // EXISTING FILTERS + NEW FILTERS
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

            // NEW: Provider filter
            if ($request->filled('provider') && $request->provider !== 'all') {
                $query->where('provider', $request->provider);
                $filterConditions['provider'] = $request->provider;
            }

            // NEW: Status kerja filter
            if ($request->filled('status_kerja') && $request->status_kerja !== 'all') {
                $query->where('status_kerja', $request->status_kerja);
                $filterConditions['status_kerja'] = $request->status_kerja;
            }

            // NEW: Grade filter
            if ($request->filled('grade') && $request->grade !== 'all') {
                $query->where('grade', $request->grade);
                $filterConditions['grade'] = $request->grade;
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

            // CRITICAL FIX: Process setiap employee dengan format unit display yang benar
            $employees->getCollection()->transform(function ($employee) {
                // Convert to array untuk manipulasi
                $employeeData = $employee->toArray();
                
                // CRITICAL: Always recalculate masa_kerja untuk setiap employee
                if (isset($employeeData['tmt_mulai_kerja']) && !empty($employeeData['tmt_mulai_kerja'])) {
                    $calculatedMasaKerja = $this->calculateMasaKerja(
                        $employeeData['tmt_mulai_kerja'], 
                        $employeeData['tmt_berakhir_kerja'] ?? null
                    );
                    
                    // Set the calculated value
                    $employeeData['masa_kerja'] = $calculatedMasaKerja;
                } else {
                    $employeeData['masa_kerja'] = "-";
                }

                // FIXED: Format unit display dengan KODE SAJA untuk konsistensi
                $employeeData['unit_display_formatted'] = $this->formatEmployeeUnitDisplay($employee);
                
                // Convert gender dari database format (L/P) ke display format untuk consistency
                if (isset($employeeData['jenis_kelamin'])) {
                    $employeeData['jenis_kelamin'] = $employeeData['jenis_kelamin'] === 'L' ? 'Laki-laki' : 'Perempuan';
                }
                
                // Return employee sebagai object dengan data yang sudah diupdate
                return (object) $employeeData;
            });

            // Calculate statistics with applied filters
            $statistics = $this->getEnhancedStatistics($filterConditions);

            // Get organizations for filter dropdown
            $organizations = $this->getOrganizationsForFilter();

            // UPDATED: Get filter options dari database yang sebenarnya + NEW FIELDS
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

            // ENHANCED DEBUG LOG: Log final results
            Log::info('Employee Index - Final Results', [
                'total_found' => $employees->total(),
                'current_page' => $employees->currentPage(),
                'active_filters' => count(array_filter($filterConditions)),
                'employees_with_masa_kerja' => $employees->getCollection()->filter(function($emp) {
                    return isset($emp->masa_kerja) && $emp->masa_kerja !== '-' && $emp->masa_kerja !== null;
                })->count(),
                'employees_with_formatted_unit' => $employees->getCollection()->filter(function($emp) {
                    return isset($emp->unit_display_formatted);
                })->count(),
                'sample_employee_data' => $employees->getCollection()->first() ? [
                    'nik' => $employees->getCollection()->first()->nik ?? 'unknown',
                    'masa_kerja' => $employees->getCollection()->first()->masa_kerja ?? 'not_set',
                    'unit_display_formatted' => $employees->getCollection()->first()->unit_display_formatted ?? 'not_set'
                ] : 'no_employees'
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
                    'ukuran_sepatu',
                    // NEW FILTERS
                    'provider',
                    'status_kerja',
                    'grade'
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
     * UPDATED: Show the form for creating a new employee with new field options
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
                // NEW: Provider options
                'providerOptions' => self::PROVIDER_OPTIONS,
                'statusKerjaOptions' => self::STATUS_KERJA_OPTIONS,
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
     * UPDATED: Store a newly created employee with comprehensive validation and NEW FIELDS
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
                // NEW FIELDS
                'provider' => $request->provider,
                'unit_kerja_kontrak' => $request->unit_kerja_kontrak,
                'grade' => $request->grade,
                'tmt_mulai_kerja' => $request->tmt_mulai_kerja,
                'tmt_berakhir_kerja' => $request->tmt_berakhir_kerja,
                'tmt_mulai_jabatan' => $request->tmt_mulai_jabatan,
                'tmt_akhir_jabatan' => $request->tmt_akhir_jabatan,
                'user_agent' => $request->header('User-Agent'),
                'ip_address' => $request->ip()
            ]);

            // Get available unit organisasi options
            $unitOrganisasiOptions = Unit::UNIT_ORGANISASI_OPTIONS ?? [
                'EGM', 'GM', 'Airside', 'Landside', 'Back Office', 'SSQC', 'Ancillary'
            ];

            // Unit organisasi yang tidak memiliki sub unit
            $unitWithoutSubUnits = ['EGM', 'GM'];

            // UPDATED: Enhanced validation rules with NEW FIELDS
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
                    Rule::in(['Laki-laki', 'Perempuan', 'L', 'P'])
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
                    Rule::in(self::KELOMPOK_JABATAN_OPTIONS)
                ],
                'status_pegawai' => [
                    'required',
                    'string',
                    Rule::in(['PEGAWAI TETAP', 'PKWT', 'TAD PAKET SDM', 'TAD PAKET PEKERJAAN'])
                ],
                
                // NEW FIELD VALIDATIONS
                'provider' => [
                    'nullable',
                    'string',
                    Rule::in(self::PROVIDER_OPTIONS)
                ],
                'unit_kerja_kontrak' => 'nullable|string|max:255',
                'grade' => 'nullable|string|max:50',
                
                // NEW: Date validations with custom rules
                'tmt_mulai_kerja' => 'nullable|date',
                'tmt_berakhir_kerja' => [
                    'nullable',
                    'date',
                    function ($attribute, $value, $fail) use ($request) {
                        if ($value) {
                            // TMT berakhir kerja harus diset setelah TMT mulai kerja diisi
                            if (!$request->tmt_mulai_kerja) {
                                $fail('TMT Mulai Kerja harus diisi terlebih dahulu sebelum mengatur TMT Berakhir Kerja.');
                            } else {
                                $mulaiKerja = \Carbon\Carbon::parse($request->tmt_mulai_kerja);
                                $berakhirKerja = \Carbon\Carbon::parse($value);
                                
                                if ($berakhirKerja->lte($mulaiKerja)) {
                                    $fail('TMT Berakhir Kerja harus diatas tanggal TMT Mulai Kerja.');
                                }
                            }
                        }
                    }
                ],
                'tmt_mulai_jabatan' => 'nullable|date',
                'tmt_akhir_jabatan' => [
                    'nullable',
                    'date',
                    function ($attribute, $value, $fail) use ($request) {
                        if ($value) {
                            // TMT akhir jabatan harus diset setelah TMT mulai jabatan diisi
                            if (!$request->tmt_mulai_jabatan) {
                                $fail('TMT Mulai Jabatan harus diisi terlebih dahulu sebelum mengatur TMT Akhir Jabatan.');
                            } else {
                                $mulaiJabatan = \Carbon\Carbon::parse($request->tmt_mulai_jabatan);
                                $akhirJabatan = \Carbon\Carbon::parse($value);
                                
                                if ($akhirJabatan->lte($mulaiJabatan)) {
                                    $fail('TMT Akhir Jabatan harus diatas tanggal TMT Mulai Jabatan.');
                                }
                            }
                        }
                    }
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
                // UPDATED: Custom error messages including NEW FIELDS
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
                'email.unique' => 'Email sudah terdaftar di sistem.',
                // NEW FIELD MESSAGES
                'provider.in' => 'Provider yang dipilih tidak valid.',
                'unit_kerja_kontrak.max' => 'Unit kerja kontrak maksimal 255 karakter.',
                'grade.max' => 'Grade maksimal 50 karakter.',
                'tmt_berakhir_kerja.date' => 'TMT Berakhir Kerja harus berupa tanggal yang valid.',
                'tmt_akhir_jabatan.date' => 'TMT Akhir Jabatan harus berupa tanggal yang valid.',
                'alamat.max' => 'Alamat lengkap maksimal 500 karakter.',
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
            
            // Handle jenis_kelamin conversion (Support both formats)
            if (isset($employeeData['jenis_kelamin'])) {
                // Convert dari format apapun ke database format (L/P)
                if ($employeeData['jenis_kelamin'] === 'Laki-laki') {
                    $employeeData['jenis_kelamin'] = 'L';
                } elseif ($employeeData['jenis_kelamin'] === 'Perempuan') {
                    $employeeData['jenis_kelamin'] = 'P';
                }
                // Jika sudah L atau P, biarkan seperti itu
            }
            
            // Handle sub_unit_id untuk EGM dan GM (set null jika kosong)
            if (in_array($request->unit_organisasi, $unitWithoutSubUnits) && empty($employeeData['sub_unit_id'])) {
                $employeeData['sub_unit_id'] = null;
            }

            // NEW: Set default values untuk field baru yang tidak bisa diubah
            $employeeData['status'] = 'active';
            $employeeData['organization_id'] = 1; // Default organization
            $employeeData['lokasi_kerja'] = 'Bandar Udara Ngurah Rai'; // Fixed location
            $employeeData['cabang'] = 'DPS'; // Fixed branch

            // NEW: Auto-set status kerja based on tmt_berakhir_kerja logic
            if (isset($employeeData['tmt_berakhir_kerja']) && !empty($employeeData['tmt_berakhir_kerja'])) {
                // Status kerja otomatis menjadi aktif saat tmt berakhir kerja diisi
                $today = Carbon::now('Asia/Makassar');
                $endDate = Carbon::parse($employeeData['tmt_berakhir_kerja']);
                
                // Auto-set berdasarkan apakah sudah melewati tanggal berakhir atau belum
                $employeeData['status_kerja'] = $today->lte($endDate) ? 'Aktif' : 'Non-Aktif';
            } else {
                // Jika tidak ada tmt_berakhir_kerja, status kerja default Non-Aktif
                $employeeData['status_kerja'] = 'Non-Aktif';
            }

            // FIXED: Calculate masa kerja using helper function
            if (isset($employeeData['tmt_mulai_kerja']) && !empty($employeeData['tmt_mulai_kerja'])) {
                $employeeData['masa_kerja'] = $this->calculateMasaKerja(
                    $employeeData['tmt_mulai_kerja'], 
                    $employeeData['tmt_berakhir_kerja'] ?? null
                );
            }

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
                    'provider' => $employee->provider,
                    'grade' => $employee->grade,
                    'status_kerja' => $employee->status_kerja,
                    'lokasi_kerja' => $employee->lokasi_kerja,
                    'cabang' => $employee->cabang,
                    'masa_kerja' => $employee->masa_kerja ?? 'Belum dihitung',
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
                            'provider' => $employee->provider,
                            'grade' => $employee->grade,
                            'status_kerja' => $employee->status_kerja,
                            'lokasi_kerja' => $employee->lokasi_kerja,
                            'cabang' => $employee->cabang,
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
     * COMPLETELY FIXED: Display the specified employee with enhanced unit display format - KODE SAJA
     * Parameter sekarang menggunakan flexible identifier (ID atau NIK)
     * FIXED: Format unit display dengan KODE SAJA untuk konsistensi dengan dashboard
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

            // CRITICAL FIX: Prepare employee data and ALWAYS calculate masa kerja
            $employeeData = $employee->toArray();
            
            // ENHANCED: Always recalculate masa kerja to ensure it's current and accurate
            $masaKerjaCalculated = null;
            if (isset($employeeData['tmt_mulai_kerja']) && !empty($employeeData['tmt_mulai_kerja'])) {
                $masaKerjaCalculated = $this->calculateMasaKerja(
                    $employeeData['tmt_mulai_kerja'], 
                    $employeeData['tmt_berakhir_kerja'] ?? null
                );
                
                // CRITICAL: Set the calculated value
                $employeeData['masa_kerja'] = $masaKerjaCalculated;
            } else {
                // If no TMT Mulai Kerja, set fallback
                $employeeData['masa_kerja'] = "-";
            }

            // FIXED: Format unit display dengan KODE SAJA untuk konsistensi dengan dashboard
            $employeeData['unit_display_formatted'] = $this->formatEmployeeUnitDisplay($employee);
            
            // FIXED: Individual unit components dengan KODE SAJA
            if (!empty($employee->kode_organisasi)) {
                $employeeData['unit_organisasi_formatted'] = $this->formatUnitForDisplay($employee->kode_organisasi);
            } else {
                $employeeData['unit_organisasi_formatted'] = $this->getUnitCodeFromUnitOrganisasi($employee->unit_organisasi);
            }

            // Convert gender dari database format (L/P) ke display format jika perlu
            if (isset($employeeData['jenis_kelamin'])) {
                $employeeData['jenis_kelamin'] = $employeeData['jenis_kelamin'] === 'L' ? 'Laki-laki' : 'Perempuan';
            }

            // ENHANCED DEBUG LOGGING
            Log::info('Employee Show Data - Comprehensive Debug dengan Unit Format KODE SAJA', [
                'employee_id' => $employee->id,
                'employee_nik' => $employee->nik,
                'employee_name' => $employee->nama_lengkap,
                'kode_organisasi' => $employee->kode_organisasi,
                'unit_organisasi' => $employee->unit_organisasi,
                'nama_organisasi' => $employee->nama_organisasi,
                'unit_display_formatted' => $employeeData['unit_display_formatted'],
                'unit_organisasi_formatted' => $employeeData['unit_organisasi_formatted'],
                'tmt_mulai_kerja_raw' => $employee->tmt_mulai_kerja,
                'tmt_mulai_kerja_processed' => $employeeData['tmt_mulai_kerja'] ?? 'null',
                'tmt_berakhir_kerja_raw' => $employee->tmt_berakhir_kerja,
                'tmt_berakhir_kerja_processed' => $employeeData['tmt_berakhir_kerja'] ?? 'null',
                'masa_kerja_from_db' => $employee->masa_kerja,
                'masa_kerja_calculated' => $masaKerjaCalculated,
                'masa_kerja_final' => $employeeData['masa_kerja'],
                'has_tmt_mulai_kerja' => !empty($employee->tmt_mulai_kerja),
                'has_tmt_berakhir_kerja' => !empty($employee->tmt_berakhir_kerja),
            ]);
            
            return Inertia::render('Employees/Show', [
                'employee' => $employeeData, // Send processed data dengan unit format KODE SAJA dan guaranteed masa_kerja
            ]);
        } catch (\Exception $e) {
            Log::error('Employee Show Error', [
                'employee_identifier' => $identifier,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return redirect()->route('employees.index')
                ->with('error', 'Error loading employee details: ' . $e->getMessage());
        }
    }

    /**
     * UPDATED: Show the form for editing the specified employee dengan semua options untuk Edit.jsx
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
            
            // FIXED: Recalculate masa kerja to ensure it's current
            if (isset($employeeData['tmt_mulai_kerja']) && !empty($employeeData['tmt_mulai_kerja'])) {
                $employeeData['masa_kerja'] = $this->calculateMasaKerja(
                    $employeeData['tmt_mulai_kerja'], 
                    $employeeData['tmt_berakhir_kerja'] ?? null
                );
            }

            // FIXED: Format unit display dengan KODE SAJA untuk edit form
            $employeeData['unit_display_formatted'] = $this->formatEmployeeUnitDisplay($employee);
            
            // Format dates untuk input type="date" including ALL NEW FIELDS
            $dateFields = ['tanggal_lahir', 'tmt_mulai_jabatan', 'tmt_mulai_kerja', 'tmt_pensiun', 'tmt_akhir_jabatan', 'tmt_berakhir_kerja'];
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
                // UPDATED: Ensure ALL new field options are passed to Edit.jsx
                'providerOptions' => self::PROVIDER_OPTIONS,
                'statusKerjaOptions' => self::STATUS_KERJA_OPTIONS,
                'success' => session('success'),
                'error' => session('error'),
                'message' => session('message'),
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
     * UPDATED: Update the specified employee in storage with COMPREHENSIVE NEW FIELD validation
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

            // UPDATED: Comprehensive validation rules with ALL NEW FIELDS for Edit mode
            $validator = Validator::make($request->all(), [
                // Identity fields with ignore current record for unique validation
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
                    Rule::in(['Laki-laki', 'Perempuan', 'L', 'P'])
                ],
                
                // Organizational structure validation - SAME AS CREATE
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
                
                // Job fields - REQUIRED
                'nama_jabatan' => 'required|string|max:255',
                'kelompok_jabatan' => ['required', Rule::in(self::KELOMPOK_JABATAN_OPTIONS)],
                'status_pegawai' => ['required', Rule::in(self::STATUS_PEGAWAI_OPTIONS)],
                
                // NEW FIELD VALIDATIONS FOR EDIT MODE - COMPREHENSIVE
                'provider' => [
                    'nullable',
                    'string',
                    Rule::in(self::PROVIDER_OPTIONS)
                ],
                'unit_kerja_kontrak' => 'nullable|string|max:255',
                'grade' => 'nullable|string|max:50',
                'status_kerja' => [
                    'required', // REQUIRED in edit mode - user can change manually
                    'string',
                    Rule::in(self::STATUS_KERJA_OPTIONS)
                ],
                
                // NEW: Enhanced date validations for EDIT mode
                'tmt_mulai_kerja' => 'nullable|date',
                'tmt_berakhir_kerja' => [
                    'nullable',
                    'date',
                    function ($attribute, $value, $fail) use ($request) {
                        if ($value && $request->tmt_mulai_kerja) {
                            $mulaiKerja = \Carbon\Carbon::parse($request->tmt_mulai_kerja);
                            $berakhirKerja = \Carbon\Carbon::parse($value);
                            
                            if ($berakhirKerja->lte($mulaiKerja)) {
                                $fail('TMT Berakhir Kerja harus diatas tanggal TMT Mulai Kerja.');
                            }
                        }
                    }
                ],
                'tmt_mulai_jabatan' => 'nullable|date',
                'tmt_akhir_jabatan' => [
                    'nullable',
                    'date',
                    function ($attribute, $value, $fail) use ($request) {
                        if ($value && $request->tmt_mulai_jabatan) {
                            $mulaiJabatan = \Carbon\Carbon::parse($request->tmt_mulai_jabatan);
                            $akhirJabatan = \Carbon\Carbon::parse($value);
                            
                            if ($akhirJabatan->lte($mulaiJabatan)) {
                                $fail('TMT Akhir Jabatan harus diatas tanggal TMT Mulai Jabatan.');
                            }
                        }
                    }
                ],
                
                // Optional personal fields
                'tempat_lahir' => 'nullable|string|max:100',
                'tanggal_lahir' => 'nullable|date|before:today',
                'alamat' => 'nullable|string|max:500',
                'kota_domisili' => 'nullable|string|max:100',
                'jabatan' => 'nullable|string|max:255',
                
                // Contact fields with ignore current record
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
                
                // Physical attributes and equipment
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
                
                // FIXED FIELDS - These should be passed but not changed
                'lokasi_kerja' => 'string|in:Bandar Udara Ngurah Rai',
                'cabang' => 'string|in:DPS',
                'masa_kerja' => 'nullable|string', // This is calculated automatically
            ], [
                // UPDATED: Comprehensive error messages including ALL NEW FIELDS
                'nik.required' => 'NIK wajib diisi.',
                'nik.size' => 'NIK harus tepat 16 digit.',
                'nik.regex' => 'NIK hanya boleh berisi angka.',
                'nik.unique' => 'NIK sudah digunakan oleh karyawan lain.',
                'nip.required' => 'NIP wajib diisi.',
                'nip.min' => 'NIP minimal 5 digit.',
                'nip.regex' => 'NIP hanya boleh berisi angka.',
                'nip.unique' => 'NIP sudah digunakan oleh karyawan lain.',
                'nama_lengkap.required' => 'Nama lengkap wajib diisi.',
                'jenis_kelamin.required' => 'Jenis kelamin wajib dipilih.',
                'unit_organisasi.required' => 'Unit organisasi wajib dipilih.',
                'unit_id.required' => 'Unit wajib dipilih.',
                'nama_jabatan.required' => 'Nama jabatan wajib diisi.',
                'kelompok_jabatan.required' => 'Kelompok jabatan wajib dipilih.',
                'status_pegawai.required' => 'Status pegawai wajib dipilih.',
                'email.unique' => 'Email sudah digunakan oleh karyawan lain.',
                // NEW FIELD MESSAGES
                'provider.in' => 'Provider yang dipilih tidak valid.',
                'unit_kerja_kontrak.max' => 'Unit kerja kontrak maksimal 255 karakter.',
                'grade.max' => 'Grade maksimal 50 karakter.',
                'status_kerja.required' => 'Status kerja wajib dipilih.',
                'status_kerja.in' => 'Status kerja yang dipilih tidak valid.',
                'tmt_berakhir_kerja.date' => 'TMT Berakhir Kerja harus berupa tanggal yang valid.',
                'tmt_akhir_jabatan.date' => 'TMT Akhir Jabatan harus berupa tanggal yang valid.',
                'lokasi_kerja.in' => 'Lokasi kerja harus "Bandar Udara Ngurah Rai".',
                'cabang.in' => 'Cabang harus "DPS".',
                'alamat.max' => 'Alamat lengkap maksimal 500 karakter.',
            ]);

            if ($validator->fails()) {
                Log::warning('Employee Update Validation Failed', [
                    'employee_id' => $employee->id,
                    'errors' => $validator->errors()->toArray(),
                    'nik' => $request->nik,
                    'nip' => $request->nip
                ]);

                return redirect()->back()
                    ->withErrors($validator->errors())
                    ->withInput()
                    ->with('error', 'Data tidak valid. Silakan periksa kembali form.');
            }

            // Prepare update data
            $updateData = $validator->validated();
            
            // Handle jenis_kelamin conversion (Support both formats)
            if (isset($updateData['jenis_kelamin'])) {
                // Convert dari format apapun ke database format (L/P)
                if ($updateData['jenis_kelamin'] === 'Laki-laki') {
                    $updateData['jenis_kelamin'] = 'L';
                } elseif ($updateData['jenis_kelamin'] === 'Perempuan') {
                    $updateData['jenis_kelamin'] = 'P';
                }
                // Jika sudah L atau P, biarkan seperti itu
            }
            
            // Handle sub_unit_id untuk EGM dan GM
            if (in_array($request->unit_organisasi, $unitWithoutSubUnits) && empty($updateData['sub_unit_id'])) {
                $updateData['sub_unit_id'] = null;
            }

            // UPDATED: Ensure fixed fields remain unchanged even in edit mode
            $updateData['lokasi_kerja'] = 'Bandar Udara Ngurah Rai';
            $updateData['cabang'] = 'DPS';

            // FIXED: Recalculate masa kerja when dates change
            if (isset($updateData['tmt_mulai_kerja'])) {
                $updateData['masa_kerja'] = $this->calculateMasaKerja(
                    $updateData['tmt_mulai_kerja'], 
                    $updateData['tmt_berakhir_kerja'] ?? null
                );
            }

            // NEW: In edit mode, status_kerja is editable and NOT auto-calculated
            // This allows manual changes to Pensiun, Mutasi, etc.
            // The user can manually set these values via dropdown in Edit.jsx

            // Update employee
            DB::beginTransaction();
            
            try {
                $employee->update($updateData);
                DB::commit();

                Log::info('Employee Updated Successfully', [
                    'employee_id' => $employee->id,
                    'employee_nik' => $employee->nik,
                    'updated_fields' => array_keys($updateData),
                    'new_values' => [
                        'status_kerja' => $updateData['status_kerja'] ?? 'unchanged',
                        'provider' => $updateData['provider'] ?? 'null',
                        'grade' => $updateData['grade'] ?? 'null',
                        'unit_kerja_kontrak' => $updateData['unit_kerja_kontrak'] ?? 'null',
                        'masa_kerja' => $updateData['masa_kerja'] ?? 'unchanged',
                    ]
                ]);

                return redirect()->route('employees.index')
                    ->with('success', 'Data karyawan berhasil diperbarui!')
                    ->with('notification', [
                        'type' => 'success',
                        'title' => 'Berhasil!',
                        'message' => "Data karyawan {$employee->nama_lengkap} berhasil diperbarui dengan semua field baru."
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
     * FIXED: Remove the specified employee from storage with improved error handling and statistics refresh
     * Parameter menggunakan flexible identifier
     */
    public function destroy(string $identifier)
    {
        try {
            Log::info('Employee Delete Request Started', [
                'identifier' => $identifier,
                'request_ip' => request()->ip(),
                'user_agent' => request()->header('User-Agent')
            ]);

            // FIXED: Find by flexible identifier with comprehensive search
            $employee = null;
            
            // Try to find by ID first (if numeric)
            if (is_numeric($identifier)) {
                $employee = Employee::find($identifier);
            }
            
            // If not found by ID, try by NIK
            if (!$employee && strlen($identifier) == 16 && is_numeric($identifier)) {
                $employee = Employee::where('nik', $identifier)->first();
            }
            
            // If not found by NIK, try by NIP
            if (!$employee && is_numeric($identifier) && strlen($identifier) >= 5) {
                $employee = Employee::where('nip', $identifier)->first();
            }
            
            if (!$employee) {
                Log::warning('Employee Delete Failed: Employee Not Found', [
                    'identifier' => $identifier,
                    'search_attempts' => ['id', 'nik', 'nip']
                ]);

                return redirect()->route('employees.index')
                    ->with('error', 'Karyawan tidak ditemukan. Pastikan data yang akan dihapus masih ada di sistem.')
                    ->with('notification', [
                        'type' => 'error',
                        'title' => 'Gagal Menghapus',
                        'message' => 'Data karyawan tidak ditemukan di sistem.'
                    ]);
            }

            // Store employee info for logging and response
            $employeeName = $employee->nama_lengkap;
            $employeeNik = $employee->nik;
            $employeeNip = $employee->nip;
            $employeeId = $employee->id;

            Log::info('Employee Found for Deletion', [
                'employee_id' => $employeeId,
                'employee_nik' => $employeeNik,
                'employee_nip' => $employeeNip,
                'employee_name' => $employeeName,
                'unit_organisasi' => $employee->unit_organisasi,
                'status_pegawai' => $employee->status_pegawai
            ]);

            // Start database transaction for safe deletion
            DB::beginTransaction();
            
            try {
                // CRITICAL: Delete the employee record
                $deleteResult = $employee->delete();
                
                if (!$deleteResult) {
                    throw new \Exception('Failed to delete employee record from database');
                }
                
                // Commit the transaction
                DB::commit();

                Log::info('Employee Deleted Successfully', [
                    'employee_id' => $employeeId,
                    'employee_nik' => $employeeNik,
                    'employee_nip' => $employeeNip,
                    'employee_name' => $employeeName,
                    'deleted_at' => now()->format('Y-m-d H:i:s'),
                    'delete_result' => $deleteResult
                ]);

                // FIXED: Return with success message and force statistics refresh
                return redirect()->route('employees.index')
                    ->with('success', "Karyawan {$employeeName} (NIK: {$employeeNik}) berhasil dihapus dari sistem!")
                    ->with('notification', [
                        'type' => 'success',
                        'title' => 'Berhasil Menghapus!',
                        'message' => "Data karyawan {$employeeName} telah dihapus permanen dari sistem. Statistik telah diperbarui.",
                        'deleted_employee' => [
                            'id' => $employeeId,
                            'nik' => $employeeNik,
                            'nip' => $employeeNip,
                            'nama_lengkap' => $employeeName,
                            'deleted_at' => now()->format('d/m/Y H:i:s')
                        ]
                    ])
                    ->with('force_refresh', true); // Force page refresh untuk update statistics

            } catch (\Exception $e) {
                // Rollback transaction pada error
                DB::rollBack();
                
                Log::error('Employee Delete Database Transaction Failed', [
                    'employee_id' => $employeeId,
                    'employee_nik' => $employeeNik,
                    'employee_name' => $employeeName,
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString()
                ]);

                return redirect()->back()
                    ->with('error', 'Terjadi kesalahan database saat menghapus karyawan. Silakan coba lagi.')
                    ->with('notification', [
                        'type' => 'error',
                        'title' => 'Gagal Menghapus',
                        'message' => 'Terjadi kesalahan database. Data tidak dihapus. Error: ' . $e->getMessage()
                    ]);
            }

        } catch (\Exception $e) {
            Log::error('Employee Delete System Error', [
                'identifier' => $identifier,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'file' => $e->getFile(),
                'line' => $e->getLine()
            ]);

            return redirect()->back()
                ->with('error', 'Terjadi kesalahan sistem saat menghapus karyawan. Silakan hubungi administrator.')
                ->with('notification', [
                    'type' => 'error',
                    'title' => 'Kesalahan Sistem',
                    'message' => 'Terjadi kesalahan sistem. Silakan coba lagi atau hubungi administrator jika masalah berlanjut.'
                ]);
        }
    }

    /**
     * FIXED: Enhanced filter options dengan data dari database + ALL NEW FIELDS
     * CRITICAL: Units sekarang menggunakan KODE SAJA
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

                // FIXED: Unit dan sub unit options dari database dengan KODE SAJA
                'units' => $this->getUnitsForFilter(),
                'sub_units' => $this->getSubUnitsForFilter(),

                // UPDATED: Enhanced filter options for ALL NEW FIELDS
                'providers' => Employee::select('provider')
                    ->whereNotNull('provider')
                    ->distinct()
                    ->orderBy('provider')
                    ->pluck('provider')
                    ->toArray(),

                'status_kerja' => Employee::select('status_kerja')
                    ->whereNotNull('status_kerja')
                    ->distinct()
                    ->orderBy('status_kerja')
                    ->pluck('status_kerja')
                    ->toArray(),

                'grades' => Employee::select('grade')
                    ->whereNotNull('grade')
                    ->distinct()
                    ->orderBy('grade')
                    ->pluck('grade')
                    ->toArray(),

                // NEW: Additional filter options for comprehensive search
                'unit_kerja_kontrak' => Employee::select('unit_kerja_kontrak')
                    ->whereNotNull('unit_kerja_kontrak')
                    ->distinct()
                    ->orderBy('unit_kerja_kontrak')
                    ->pluck('unit_kerja_kontrak')
                    ->toArray(),
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
                'providers' => self::PROVIDER_OPTIONS,
                'status_kerja' => self::STATUS_KERJA_OPTIONS,
                'grades' => [],
                'unit_kerja_kontrak' => [],
            ];
        }
    }

    /**
     * FIXED: Get units dari database untuk filter dengan KODE SAJA
     * CRITICAL CHANGE: Mengembalikan kode unit saja, bukan format "(XX) Nama Unit"
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
                          // FIXED: Unit.name sekarang sudah kode, gunakan langsung
                          return [
                              'id' => $unit->id,
                              'name' => $unit->name, // Sudah kode
                              'formatted_name' => $unit->name, // Kode saja
                              'code' => $unit->code,
                              'unit_organisasi' => $unit->unit_organisasi,
                              'label' => $unit->name, // Kode saja
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
                                  'unit_name' => $subUnit->unit ? $subUnit->unit->name : '', // Unit.name sudah kode
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
     * UPDATED: Get employee statistics (API endpoint) with NEW FIELDS
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
                    'general_manager' => Employee::where('kelompok_jabatan', 'GENERAL MANAGER')->count(),
                    'executive_gm' => Employee::where('kelompok_jabatan', 'EXECUTIVE GENERAL MANAGER')->count(),
                    'account_executive' => Employee::where('kelompok_jabatan', 'ACCOUNT EXECUTIVE/AE')->count(),
                    'non' => Employee::where('kelompok_jabatan', 'NON')->count(),
                ],
                // UPDATED: Enhanced status kerja statistics
                'status_kerja' => [
                    'aktif' => Employee::where('status_kerja', 'Aktif')->count(),
                    'non_aktif' => Employee::where('status_kerja', 'Non-Aktif')->count(),
                    'pensiun' => Employee::where('status_kerja', 'Pensiun')->count(),
                    'mutasi' => Employee::where('status_kerja', 'Mutasi')->count(),
                ],
                // UPDATED: Enhanced provider statistics
                'by_provider' => Employee::select('provider', DB::raw('count(*) as total'))
                    ->whereNotNull('provider')
                    ->groupBy('provider')
                    ->orderBy('total', 'desc')
                    ->get()
                    ->map(function ($item) {
                        return [
                            'name' => $item->provider,
                            'count' => $item->total,
                        ];
                    })
                    ->toArray(),
                // NEW: Grade statistics
                'by_grade' => Employee::select('grade', DB::raw('count(*) as total'))
                    ->whereNotNull('grade')
                    ->groupBy('grade')
                    ->orderBy('grade')
                    ->get()
                    ->map(function ($item) {
                        return [
                            'name' => $item->grade,
                            'count' => $item->total,
                        ];
                    })
                    ->toArray(),
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
     * UPDATED: Get enhanced statistics with safe null handling and comprehensive filter support + ALL NEW FIELDS
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
                
                // NEW: Status kerja statistics
                $statusKerjaAktif = (clone $baseQuery)->where('status_kerja', 'Aktif')->count();
                $statusKerjaNonAktif = (clone $baseQuery)->where('status_kerja', 'Non-Aktif')->count();
                
                // NEW: Provider count
                $providerCount = (clone $baseQuery)->whereNotNull('provider')->distinct()->count('provider');
                
            } else {
                // Apply filters to calculate statistics including NEW FIELDS
                $query = Employee::query();
                if (Schema::hasColumn('employees', 'status')) {
                    $query->where('status', 'active');
                }
                
                // ENHANCED: Apply filters menggunakan enhanced search logic including NEW FIELDS
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
                          ->orWhere('handphone', 'like', "%{$searchTerm}%")
                          // NEW FIELD SEARCHES
                          ->orWhere('provider', 'like', "%{$searchTerm}%")
                          ->orWhere('unit_kerja_kontrak', 'like', "%{$searchTerm}%")
                          ->orWhere('grade', 'like', "%{$searchTerm}%")
                          ->orWhere('status_kerja', 'like', "%{$searchTerm}%");
                          
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

                // Apply other filters including NEW FIELDS
                foreach (['status_pegawai', 'kelompok_jabatan', 'unit_organisasi', 'unit_id', 'sub_unit_id', 'jenis_kelamin', 'jenis_sepatu', 'ukuran_sepatu', 'provider', 'status_kerja', 'grade'] as $filterKey) {
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
                
                // NEW: Status kerja statistics with filters
                $statusKerjaAktif = (clone $query)->where('status_kerja', 'Aktif')->count();
                $statusKerjaNonAktif = (clone $query)->where('status_kerja', 'Non-Aktif')->count();
                
                // NEW: Provider count with filters
                $providerCount = (clone $query)->whereNotNull('provider')->distinct()->count('provider');
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
                // NEW: Status kerja statistics
                'statusKerjaAktif' => $statusKerjaAktif ?? 0,
                'statusKerjaNonAktif' => $statusKerjaNonAktif ?? 0,
                // NEW: Provider statistics
                'providerCount' => $providerCount ?? 0,
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
            $today = Carbon::now('Asia/Makassar')->startOfDay();
            $endOfDay = Carbon::now('Asia/Makassar')->endOfDay();

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
            $yesterday = Carbon::now('Asia/Makassar')->subDay()->startOfDay();
            $endOfYesterday = Carbon::now('Asia/Makassar')->subDay()->endOfDay();

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
            $startOfWeek = Carbon::now('Asia/Makassar')->startOfWeek();
            $now = Carbon::now('Asia/Makassar');

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
            
            $hour = Carbon::now('Asia/Makassar')->hour;
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
            
            $hour = Carbon::now('Asia/Makassar')->hour;
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
     * UPDATED: Get default statistics for fallback with NEW FIELDS
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
            'activeFilters' => 0,
            // NEW: Default values for new field statistics
            'statusKerjaAktif' => 0,
            'statusKerjaNonAktif' => 0,
            'providerCount' => 0,
        ];
    }
}