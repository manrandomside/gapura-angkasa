<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

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
     * Digunakan untuk format display UI (XX) Nama Panjang
     * CRITICAL: Sekarang field name berisi kode unit, bukan nama panjang
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
     * UPDATED: Get unit code - sekarang field name sudah berisi kode unit
     */
    public function getUnitCode()
    {
        // Field name sekarang berisi kode unit
        return $this->name ?? $this->code;
    }

    /**
     * NEW: Get unit long name untuk display
     */
    public function getUnitLongName()
    {
        $mapping = self::UNIT_DISPLAY_MAPPING;
        $unitCode = $this->getUnitCode();
        
        return $mapping[$unitCode] ?? $unitCode;
    }

    /**
     * UPDATED: Format unit display dengan kode (XX) Nama Panjang
     * Menggunakan mapping dari kode ke nama panjang
     */
    public function getFormattedDisplayNameAttribute()
    {
        $unitCode = $this->getUnitCode();
        $longName = $this->getUnitLongName();
        
        // Untuk EGM dan GM, tampilkan kode saja
        if (in_array($unitCode, ['EGM', 'GM'])) {
            return $unitCode;
        }
        
        // Format: (XX) Nama Panjang
        return "({$unitCode}) {$longName}";
    }

    /**
     * UPDATED: Static method untuk format unit dengan kode
     * Menggunakan unit code yang sudah ada di field name
     */
    public static function formatUnitWithCode($unitCode, $unitOrganisasi = null)
    {
        $mapping = self::UNIT_DISPLAY_MAPPING;
        $longName = $mapping[$unitCode] ?? $unitCode;
        
        // Untuk EGM dan GM, tampilkan kode saja
        if (in_array($unitCode, ['EGM', 'GM'])) {
            return $unitCode;
        }
        
        // Format: (XX) Nama Panjang
        return "({$unitCode}) {$longName}";
    }

    /**
     * UPDATED: Get semua unit dengan format kode untuk dropdown
     */
    public static function getUnitsWithCodeFormat()
    {
        return self::active()
            ->get()
            ->map(function($unit) {
                return [
                    'id' => $unit->id,
                    'name' => $unit->name, // Sekarang berisi kode unit
                    'code' => $unit->getUnitCode(),
                    'unit_organisasi' => $unit->unit_organisasi,
                    'formatted_name' => $unit->formatted_display_name,
                    'long_name' => $unit->getUnitLongName(),
                    'display_label' => $unit->formatted_display_name,
                ];
            });
    }

    /**
     * UPDATED: Get units untuk unit organisasi tertentu dengan format kode
     */
    public static function getUnitsByOrganisasiWithCode($unitOrganisasi)
    {
        return self::active()
            ->byUnitOrganisasi($unitOrganisasi)
            ->get()
            ->map(function($unit) {
                return [
                    'id' => $unit->id,
                    'name' => $unit->name, // Sekarang berisi kode unit
                    'code' => $unit->getUnitCode(),
                    'unit_organisasi' => $unit->unit_organisasi,
                    'formatted_name' => $unit->formatted_display_name,
                    'long_name' => $unit->getUnitLongName(),
                    'display_label' => $unit->formatted_display_name,
                ];
            });
    }

    /**
     * UPDATED: Method untuk dashboard grafik - get unit data dengan format kode
     */
    public static function getUnitDataForChart()
    {
        $units = self::active()->with('employees')->get();
        
        return $units->map(function($unit) {
            $employeeCount = $unit->employees()->count();
            
            return [
                'name' => $unit->formatted_display_name, // Format dengan kode untuk grafik
                'code' => $unit->getUnitCode(),
                'long_name' => $unit->getUnitLongName(),
                'unit_organisasi' => $unit->unit_organisasi,
                'count' => $employeeCount,
                'value' => $employeeCount, // Alias untuk grafik
                'label' => $unit->formatted_display_name,
            ];
        })->filter(function($unit) {
            return $unit['count'] > 0; // Hanya tampilkan unit yang ada karyawannya
        })->values();
    }

    /**
     * UPDATED: Get unit organisasi dengan employee count untuk dashboard
     * Menggunakan format kode unit
     */
    public static function getUnitOrganisasiWithEmployeeCountForChart()
    {
        $result = [];
        
        foreach (self::UNIT_ORGANISASI_OPTIONS as $unitOrganisasi) {
            $units = self::active()->byUnitOrganisasi($unitOrganisasi)->with('employees')->get();
            
            $unitData = [];
            $totalEmployees = 0;
            
            foreach ($units as $unit) {
                $employeeCount = $unit->employees()->count();
                $totalEmployees += $employeeCount;
                
                if ($employeeCount > 0) {
                    $unitData[] = [
                        'name' => $unit->formatted_display_name, // Format dengan kode
                        'code' => $unit->getUnitCode(),
                        'long_name' => $unit->getUnitLongName(),
                        'count' => $employeeCount,
                        'value' => $employeeCount,
                        'label' => $unit->formatted_display_name,
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
        
        return $result;
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

    /**
     * Get employees yang belongs to this unit
     */
    public function employees()
    {
        return $this->hasMany(Employee::class);
    }

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
     * Get total employees count in this unit
     */
    public function getEmployeesCountAttribute()
    {
        return $this->employees()->count();
    }

    /**
     * UPDATED: Get full name dengan prefix unit organisasi
     * Menggunakan kode unit bukan nama panjang
     */
    public function getFullNameAttribute()
    {
        return $this->unit_organisasi . ' - ' . $this->getUnitCode();
    }

    /**
     * UPDATED: Get display name for dropdown dengan format kode
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

    /**
     * UPDATED: Boot method dengan auto-generate code yang konsisten
     * Field name dan code sekarang sama-sama berisi kode unit
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
        });
        
        static::updating(function ($unit) {
            // Pastikan name dan code tetap sinkron
            if ($unit->isDirty('name')) {
                $unit->code = $unit->name;
            }
            
            if ($unit->isDirty('code')) {
                $unit->name = $unit->code;
            }
        });
    }

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
     * NEW: Get unit by code
     */
    public static function findByCode($code)
    {
        return self::where('name', $code)->orWhere('code', $code)->first();
    }

    /**
     * NEW: Get formatted name untuk consistency dengan sistem lama
     */
    public function getFormattedName()
    {
        return $this->formatted_display_name;
    }
}