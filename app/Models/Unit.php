<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Log;

class Unit extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'code', 
        'unit_organisasi',
        'description',
        'is_active'
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    /**
     * Unit Organisasi options - Sesuai struktur GAPURA ANGKASA
     */
    const UNIT_ORGANISASI_OPTIONS = [
        'EGM',
        'GM', 
        'Airside',
        'Landside',
        'Back Office',
        'SSQC',
        'Ancillary'
    ];

    /**
     * UPDATED: Unit display mapping dari kode unit ke nama panjang
     * Digunakan untuk dokumentasi/referensi, tapi dashboard menggunakan KODE SAJA
     * CRITICAL: Field name berisi kode unit untuk konsistensi dengan dashboard
     */
    const UNIT_DISPLAY_MAPPING = [
        'MO' => 'Movement Operations',
        'ME' => 'Maintenance Equipment',
        'MF' => 'Movement Flight',
        'MS' => 'Movement Service',
        'MK' => 'Management Keuangan',
        'MU' => 'Management Unit',
        'MQ' => 'Management Quality',
        'MB' => 'Management Business',
        'EGM' => 'EGM',
        'GM' => 'GM'
    ];

    /**
     * LEGACY: Unit code mapping untuk backward compatibility
     * Tetap dipertahankan untuk fallback jika diperlukan
     */
    const UNIT_CODE_MAPPING = [
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

    /**
     * NEW: Get unit display mapping untuk frontend
     */
    public static function getUnitDisplayMapping()
    {
        return self::UNIT_DISPLAY_MAPPING;
    }

    /**
     * CRITICAL FIX: Get unit code - prioritas field code, fallback ke name
     * Field name dan code sekarang sama-sama berisi kode unit untuk real-time consistency
     */
    public function getUnitCode()
    {
        // Priority: code field, fallback to name field (both contain unit codes)
        return $this->code ?? $this->name;
    }

    /**
     * NEW: Get unit long name untuk display (reference only)
     */
    public function getUnitLongName()
    {
        $mapping = self::UNIT_DISPLAY_MAPPING;
        $unitCode = $this->getUnitCode();
        
        return $mapping[$unitCode] ?? $unitCode;
    }

    /**
     * UPDATED: Format unit display dengan kode (XX) Nama Panjang - UNTUK UI FORM SAJA
     * Dashboard menggunakan KODE SAJA dari getUnitCodeForDashboard()
     */
    public function getFormattedDisplayNameAttribute()
    {
        $unitCode = $this->getUnitCode();
        $longName = $this->getUnitLongName();
        
        // Untuk EGM dan GM, tampilkan kode saja
        if (in_array($unitCode, ['EGM', 'GM'])) {
            return $unitCode;
        }
        
        // Format: (XX) Nama Panjang - HANYA UNTUK UI FORM
        return "({$unitCode}) {$longName}";
    }

    /**
     * CRITICAL NEW: Get unit code untuk dashboard - KODE SAJA format
     * CRITICAL: Method ini digunakan oleh DashboardController untuk konsistensi
     */
    public function getUnitCodeForDashboard()
    {
        // Return KODE SAJA untuk dashboard consistency
        return $this->getUnitCode();
    }

    /**
     * CRITICAL NEW: Format unit untuk dashboard dengan KODE SAJA
     * CRITICAL: Konsisten dengan DashboardController.formatUnitForChart()
     */
    public function getFormattedForDashboard()
    {
        // Return KODE SAJA untuk dashboard real-time
        return $this->getUnitCode();
    }

    // =====================================================
    // CRITICAL FIX: RELATIONSHIPS UNTUK REAL-TIME DASHBOARD
    // =====================================================

    /**
     * CRITICAL FIX: Get employees yang belongs to this unit - ENHANCED untuk real-time
     * CRITICAL: Relationship ini digunakan oleh DashboardController untuk real-time updates
     */
    public function employees()
    {
        return $this->hasMany(Employee::class, 'unit_id', 'id');
    }

    /**
     * Get sub units yang belongs to this unit (active only)
     */
    public function subUnits()
    {
        return $this->hasMany(SubUnit::class)->where('is_active', true);
    }

    /**
     * Get all sub units (including inactive)
     */
    public function allSubUnits()
    {
        return $this->hasMany(SubUnit::class);
    }

    // =====================================================
    // CRITICAL FIX: DASHBOARD CHART METHODS - REAL-TIME COMPATIBLE
    // =====================================================

    /**
     * COMPLETELY FIXED: Method untuk dashboard grafik - KODE SAJA format untuk real-time
     * CRITICAL: Method ini digunakan oleh DashboardController.getUnitChartData()
     */
    public static function getUnitDataForChart()
    {
        try {
            $units = self::active()->with('employees')->get();
            
            Log::info('UNIT MODEL: getUnitDataForChart called for real-time dashboard', [
                'units_found' => $units->count(),
                'method' => 'getUnitDataForChart',
                'format' => 'KODE_SAJA_for_dashboard_consistency'
            ]);
            
            return $units->map(function($unit) {
                $employeeCount = $unit->employees()->count();
                $unitCode = $unit->getUnitCodeForDashboard(); // KODE SAJA
                
                return [
                    'name' => $unitCode, // CRITICAL: KODE SAJA untuk dashboard consistency
                    'code' => $unitCode,
                    'unit_code' => $unitCode,
                    'long_name' => $unit->getUnitLongName(),
                    'unit_organisasi' => $unit->unit_organisasi,
                    'count' => $employeeCount,
                    'value' => $employeeCount, // Alias untuk grafik
                    'label' => $unitCode, // KODE SAJA
                    'formatted_for_dashboard' => $unitCode, // KODE SAJA
                    'unit_id' => $unit->id
                ];
            })->filter(function($unit) {
                return $unit['count'] > 0; // Hanya tampilkan unit yang ada karyawannya
            })->values();
            
        } catch (\Exception $e) {
            Log::error('UNIT MODEL: getUnitDataForChart error', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return collect();
        }
    }

    /**
     * CRITICAL FIX: Get unit organisasi dengan employee count untuk dashboard - KODE SAJA format
     * UPDATED: Menggunakan format KODE SAJA untuk konsistensi dashboard real-time
     */
    public static function getUnitOrganisasiWithEmployeeCountForChart()
    {
        try {
            $result = [];
            
            foreach (self::UNIT_ORGANISASI_OPTIONS as $unitOrganisasi) {
                $units = self::active()->byUnitOrganisasi($unitOrganisasi)->with('employees')->get();
                
                $unitData = [];
                $totalEmployees = 0;
                
                foreach ($units as $unit) {
                    $employeeCount = $unit->employees()->count();
                    $totalEmployees += $employeeCount;
                    
                    if ($employeeCount > 0) {
                        $unitCode = $unit->getUnitCodeForDashboard(); // KODE SAJA
                        
                        $unitData[] = [
                            'name' => $unitCode, // CRITICAL: KODE SAJA untuk dashboard
                            'code' => $unitCode,
                            'unit_code' => $unitCode,
                            'long_name' => $unit->getUnitLongName(),
                            'count' => $employeeCount,
                            'value' => $employeeCount,
                            'label' => $unitCode, // KODE SAJA
                            'formatted_for_dashboard' => $unitCode, // KODE SAJA
                            'unit_id' => $unit->id
                        ];
                    }
                }
                
                if ($totalEmployees > 0) {
                    $result[] = [
                        'unit_organisasi' => $unitOrganisasi,
                        'total_employees' => $totalEmployees,
                        'units' => $unitData,
                        'units_count' => count($unitData),
                    ];
                }
            }
            
            Log::info('UNIT MODEL: getUnitOrganisasiWithEmployeeCountForChart completed', [
                'unit_organisasi_count' => count($result),
                'format' => 'KODE_SAJA_for_dashboard_real_time'
            ]);
            
            return $result;
            
        } catch (\Exception $e) {
            Log::error('UNIT MODEL: getUnitOrganisasiWithEmployeeCountForChart error', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return [];
        }
    }

    // =====================================================
    // UI FORM METHODS - FORMATTED DISPLAY (UNTUK FORM SAJA, BUKAN DASHBOARD)
    // =====================================================

    /**
     * UPDATED: Static method untuk format unit dengan kode - UNTUK UI FORM
     * Dashboard menggunakan getUnitCodeForDashboard() yang return KODE SAJA
     */
    public static function formatUnitWithCode($unitCode, $unitOrganisasi = null)
    {
        $mapping = self::UNIT_DISPLAY_MAPPING;
        $longName = $mapping[$unitCode] ?? $unitCode;
        
        // Untuk EGM dan GM, tampilkan kode saja
        if (in_array($unitCode, ['EGM', 'GM'])) {
            return $unitCode;
        }
        
        // Format: (XX) Nama Panjang - UNTUK UI FORM SAJA
        return "({$unitCode}) {$longName}";
    }

    /**
     * UPDATED: Get semua unit dengan format kode untuk dropdown UI FORM
     * Dashboard menggunakan method lain yang return KODE SAJA
     */
    public static function getUnitsWithCodeFormat()
    {
        return self::active()
            ->get()
            ->map(function($unit) {
                $unitCode = $unit->getUnitCode();
                
                return [
                    'id' => $unit->id,
                    'name' => $unitCode, // Unit code untuk value
                    'code' => $unitCode,
                    'unit_organisasi' => $unit->unit_organisasi,
                    'formatted_name' => $unit->formatted_display_name, // Format untuk UI form
                    'dashboard_name' => $unitCode, // KODE SAJA untuk dashboard
                    'long_name' => $unit->getUnitLongName(),
                    'display_label' => $unit->formatted_display_name, // Format untuk dropdown
                ];
            });
    }

    /**
     * UPDATED: Get units untuk unit organisasi tertentu dengan format kode - UNTUK UI FORM
     */
    public static function getUnitsByOrganisasiWithCode($unitOrganisasi)
    {
        return self::active()
            ->byUnitOrganisasi($unitOrganisasi)
            ->get()
            ->map(function($unit) {
                $unitCode = $unit->getUnitCode();
                
                return [
                    'id' => $unit->id,
                    'name' => $unitCode, // Unit code untuk value
                    'code' => $unitCode,
                    'unit_organisasi' => $unit->unit_organisasi,
                    'formatted_name' => $unit->formatted_display_name, // Format untuk UI form
                    'dashboard_name' => $unitCode, // KODE SAJA untuk dashboard
                    'long_name' => $unit->getUnitLongName(),
                    'display_label' => $unit->formatted_display_name, // Format untuk dropdown
                ];
            });
    }

    // =====================================================
    // SCOPES & QUERIES
    // =====================================================

    /**
     * Scope untuk unit aktif
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope untuk filter berdasarkan unit organisasi
     */
    public function scopeByUnitOrganisasi($query, $unitOrganisasi)
    {
        return $query->where('unit_organisasi', $unitOrganisasi);
    }

    /**
     * Get units grouped by unit organisasi
     */
    public static function getGroupedByUnitOrganisasi()
    {
        return self::active()
            ->with('subUnits')
            ->get()
            ->groupBy('unit_organisasi');
    }

    /**
     * Get unit organisasi with counts for statistics
     */
    public static function getUnitOrganisasiWithCounts()
    {
        $result = [];
        
        foreach (self::UNIT_ORGANISASI_OPTIONS as $unitOrganisasi) {
            $unitCount = self::active()->byUnitOrganisasi($unitOrganisasi)->count();
            $subUnitCount = SubUnit::active()
                ->whereHas('unit', function($query) use ($unitOrganisasi) {
                    $query->where('unit_organisasi', $unitOrganisasi);
                })
                ->count();
            
            $employeeCount = Employee::whereHas('unit', function($query) use ($unitOrganisasi) {
                $query->where('unit_organisasi', $unitOrganisasi);
            })->count();
            
            $result[] = [
                'name' => $unitOrganisasi,
                'unit_count' => $unitCount,
                'sub_unit_count' => $subUnitCount,
                'employee_count' => $employeeCount
            ];
        }
        
        return $result;
    }

    /**
     * Check if unit organisasi has sub units
     */
    public function hasSubUnits()
    {
        return $this->subUnits()->count() > 0;
    }

    /**
     * Get active sub units count
     */
    public function getActiveSubUnitsCountAttribute()
    {
        return $this->subUnits()->count();
    }

    /**
     * CRITICAL FIX: Get total employees count in this unit - Enhanced untuk real-time
     */
    public function getEmployeesCountAttribute()
    {
        try {
            $count = $this->employees()->count();
            
            Log::debug('UNIT MODEL: getEmployeesCountAttribute called', [
                'unit_id' => $this->id,
                'unit_code' => $this->getUnitCode(),
                'employee_count' => $count,
                'real_time_tracking' => true
            ]);
            
            return $count;
        } catch (\Exception $e) {
            Log::error('UNIT MODEL: getEmployeesCountAttribute error', [
                'unit_id' => $this->id,
                'error' => $e->getMessage()
            ]);
            return 0;
        }
    }

    /**
     * UPDATED: Get full name dengan prefix unit organisasi - menggunakan kode unit
     */
    public function getFullNameAttribute()
    {
        return $this->unit_organisasi . ' - ' . $this->getUnitCode();
    }

    /**
     * UPDATED: Get display name for dropdown dengan format kode - UNTUK UI FORM
     */
    public function getDisplayNameAttribute()
    {
        $formattedName = $this->formatted_display_name;
        $subUnitsCount = $this->active_sub_units_count;
        
        if ($subUnitsCount > 0) {
            return $formattedName . " ({$subUnitsCount} sub units)";
        }
        return $formattedName;
    }

    /**
     * Check if this unit belongs to specific unit organisasi
     */
    public function belongsToUnitOrganisasi($unitOrganisasi)
    {
        return $this->unit_organisasi === $unitOrganisasi;
    }

    /**
     * Get all units by unit organisasi with employee counts
     */
    public static function getByUnitOrganisasiWithEmployeeCounts($unitOrganisasi)
    {
        return self::active()
            ->byUnitOrganisasi($unitOrganisasi)
            ->with(['subUnits', 'employees'])
            ->get()
            ->map(function ($unit) {
                $unit->employees_count = $unit->employees()->count();
                $unit->sub_units_count = $unit->subUnits()->count();
                return $unit;
            });
    }

    /**
     * Get unit organisasi summary for dashboard
     */
    public static function getUnitOrganisasiSummary()
    {
        $summary = [];
        
        foreach (self::UNIT_ORGANISASI_OPTIONS as $unitOrganisasi) {
            $units = self::active()->byUnitOrganisasi($unitOrganisasi)->get();
            $totalSubUnits = 0;
            $totalEmployees = 0;
            
            foreach ($units as $unit) {
                $totalSubUnits += $unit->subUnits()->count();
                $totalEmployees += $unit->employees()->count();
            }
            
            $summary[$unitOrganisasi] = [
                'units_count' => $units->count(),
                'sub_units_count' => $totalSubUnits,
                'employees_count' => $totalEmployees,
                'has_sub_units' => $totalSubUnits > 0
            ];
        }
        
        return $summary;
    }

    /**
     * Scope untuk unit yang memiliki sub units
     */
    public function scopeWithSubUnits($query)
    {
        return $query->has('subUnits');
    }

    /**
     * Scope untuk unit yang tidak memiliki sub units
     */
    public function scopeWithoutSubUnits($query)
    {
        return $query->doesntHave('subUnits');
    }

    // =====================================================
    // HELPER METHODS
    // =====================================================

    /**
     * NEW: Get unit code from unit name (untuk backward compatibility)
     */
    public static function getCodeFromName($unitName)
    {
        // Karena sekarang name berisi kode, langsung return name
        $unit = self::where('name', $unitName)->first();
        return $unit ? $unit->getUnitCode() : $unitName;
    }

    /**
     * NEW: Get unit name from unit code (untuk backward compatibility)  
     */
    public static function getNameFromCode($unitCode)
    {
        $mapping = self::UNIT_DISPLAY_MAPPING;
        return $mapping[$unitCode] ?? $unitCode;
    }

    /**
     * NEW: Check if unit requires sub units based on unit organisasi
     */
    public function requiresSubUnits()
    {
        $unitsWithoutSubUnits = ['EGM', 'GM'];
        return !in_array($this->unit_organisasi, $unitsWithoutSubUnits);
    }

    /**
     * CRITICAL FIX: Get unit by code - Enhanced untuk real-time compatibility
     */
    public static function findByCode($code)
    {
        return self::where('name', $code)->orWhere('code', $code)->first();
    }

    /**
     * NEW: Get formatted name untuk consistency dengan sistem lama - UNTUK UI FORM
     */
    public function getFormattedName()
    {
        return $this->formatted_display_name;
    }

    /**
     * CRITICAL NEW: Get formatted name untuk dashboard - KODE SAJA
     */
    public function getFormattedNameForDashboard()
    {
        return $this->getUnitCodeForDashboard();
    }

    // =====================================================
    // CRITICAL FIX: BOOT METHOD - ENHANCED LOGGING UNTUK REAL-TIME TRACKING
    // =====================================================

    /**
     * CRITICAL FIX: Boot method dengan auto-generate code dan enhanced logging
     * Field name dan code sekarang sama-sama berisi kode unit untuk real-time consistency
     */
    protected static function boot()
    {
        parent::boot();
        
        static::creating(function ($unit) {
            // Pastikan name dan code konsisten berisi kode unit
            if (empty($unit->code) && !empty($unit->name)) {
                $unit->code = $unit->name;
            }
            
            if (empty($unit->name) && !empty($unit->code)) {
                $unit->name = $unit->code;
            }
            
            // Jika keduanya kosong, auto-generate dari unit_organisasi
            if (empty($unit->name) && empty($unit->code)) {
                $generatedCode = strtoupper(substr($unit->unit_organisasi, 0, 2));
                $unit->name = $generatedCode;
                $unit->code = $generatedCode;
            }
            
            Log::info('UNIT MODEL: Creating new unit for real-time system', [
                'unit_name' => $unit->name,
                'unit_code' => $unit->code,
                'unit_organisasi' => $unit->unit_organisasi,
                'consistency_check' => $unit->name === $unit->code,
                'real_time_ready' => true
            ]);
        });
        
        // CRITICAL FIX: Enhanced created event untuk real-time tracking
        static::created(function ($unit) {
            try {
                Log::info('UNIT CREATED - REAL-TIME TRACKING: New unit added to system', [
                    'unit_id' => $unit->id,
                    'unit_name' => $unit->name,
                    'unit_code' => $unit->code,
                    'unit_organisasi' => $unit->unit_organisasi,
                    'dashboard_code' => $unit->getUnitCodeForDashboard(),
                    'real_time_tracking' => true,
                    'dashboard_impact' => 'Available for employee assignment and chart updates'
                ]);
            } catch (\Exception $e) {
                Log::warning('Unit creation logging failed: ' . $e->getMessage());
            }
        });
        
        static::updating(function ($unit) {
            // Track unit changes yang bisa mempengaruhi dashboard
            $nameChanged = $unit->isDirty('name');
            $codeChanged = $unit->isDirty('code');
            $orgChanged = $unit->isDirty('unit_organisasi');
            
            // Pastikan name dan code tetap sinkron
            if ($nameChanged) {
                $unit->code = $unit->name;
            }
            
            if ($codeChanged) {
                $unit->name = $unit->code;
            }
            
            if ($nameChanged || $codeChanged || $orgChanged) {
                Log::info('UNIT UPDATING - REAL-TIME TRACKING: Unit changes detected', [
                    'unit_id' => $unit->id,
                    'name_changed' => $nameChanged,
                    'code_changed' => $codeChanged,
                    'org_changed' => $orgChanged,
                    'old_name' => $nameChanged ? $unit->getOriginal('name') : null,
                    'new_name' => $unit->name,
                    'old_code' => $codeChanged ? $unit->getOriginal('code') : null,
                    'new_code' => $unit->code,
                    'dashboard_impact' => 'May affect employee unit display and chart data'
                ]);
            }
        });
        
        // CRITICAL FIX: Enhanced updated event untuk real-time tracking
        static::updated(function ($unit) {
            try {
                Log::info('UNIT UPDATED - REAL-TIME TRACKING: Unit data modified', [
                    'unit_id' => $unit->id,
                    'unit_name' => $unit->name,
                    'unit_code' => $unit->code,
                    'unit_organisasi' => $unit->unit_organisasi,
                    'dashboard_code' => $unit->getUnitCodeForDashboard(),
                    'employee_count' => $unit->employees()->count(),
                    'real_time_tracking' => true,
                    'updated_at' => $unit->updated_at
                ]);
            } catch (\Exception $e) {
                Log::warning('Unit update logging failed: ' . $e->getMessage());
            }
        });
    }
}