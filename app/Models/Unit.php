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
     * UPDATED: Unit code mapping untuk format display (XX) Nama Unit
     * Sesuai dengan requirement user untuk dropdown dan grafik
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
     * NEW: Get unit code mapping untuk frontend
     */
    public static function getUnitCodeMapping()
    {
        return self::UNIT_CODE_MAPPING;
    }

    /**
     * NEW: Get unit code berdasarkan nama unit dan unit organisasi
     */
    public function getUnitCode()
    {
        $mapping = self::UNIT_CODE_MAPPING;
        
        if (isset($mapping[$this->unit_organisasi][$this->name])) {
            return $mapping[$this->unit_organisasi][$this->name];
        }
        
        // Fallback untuk unit tanpa mapping (EGM, GM)
        return $this->code ?? strtoupper(substr($this->name, 0, 2));
    }

    /**
     * NEW: Format unit display dengan kode (XX) Nama Unit
     */
    public function getFormattedDisplayNameAttribute()
    {
        $mapping = self::UNIT_CODE_MAPPING;
        
        if (isset($mapping[$this->unit_organisasi][$this->name])) {
            $code = $mapping[$this->unit_organisasi][$this->name];
            return "({$code}) {$this->name}";
        }
        
        // Fallback untuk unit tanpa mapping (EGM, GM)
        return $this->name;
    }

    /**
     * NEW: Static method untuk format unit dengan kode
     */
    public static function formatUnitWithCode($unitName, $unitOrganisasi)
    {
        $mapping = self::UNIT_CODE_MAPPING;
        
        if (isset($mapping[$unitOrganisasi][$unitName])) {
            $code = $mapping[$unitOrganisasi][$unitName];
            return "({$code}) {$unitName}";
        }
        
        // Fallback untuk unit tanpa mapping (EGM, GM)
        return $unitName;
    }

    /**
     * NEW: Get semua unit dengan format kode untuk dropdown
     */
    public static function getUnitsWithCodeFormat()
    {
        return self::active()
            ->get()
            ->map(function($unit) {
                return [
                    'id' => $unit->id,
                    'name' => $unit->name,
                    'code' => $unit->getUnitCode(),
                    'unit_organisasi' => $unit->unit_organisasi,
                    'formatted_name' => $unit->formatted_display_name,
                    'original_name' => $unit->name,
                    'display_label' => $unit->formatted_display_name,
                ];
            });
    }

    /**
     * NEW: Get units untuk unit organisasi tertentu dengan format kode
     */
    public static function getUnitsByOrganisasiWithCode($unitOrganisasi)
    {
        return self::active()
            ->byUnitOrganisasi($unitOrganisasi)
            ->get()
            ->map(function($unit) {
                return [
                    'id' => $unit->id,
                    'name' => $unit->name,
                    'code' => $unit->getUnitCode(),
                    'unit_organisasi' => $unit->unit_organisasi,
                    'formatted_name' => $unit->formatted_display_name,
                    'original_name' => $unit->name,
                    'display_label' => $unit->formatted_display_name,
                ];
            });
    }

    /**
     * NEW: Method untuk dashboard grafik - get unit data dengan format kode
     */
    public static function getUnitDataForChart()
    {
        $units = self::active()->with('employees')->get();
        
        return $units->map(function($unit) {
            $employeeCount = $unit->employees()->count();
            
            return [
                'name' => $unit->formatted_display_name, // Format dengan kode untuk grafik
                'original_name' => $unit->name,
                'code' => $unit->getUnitCode(),
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
                        'original_name' => $unit->name,
                        'code' => $unit->getUnitCode(),
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
     * Get full name dengan prefix unit organisasi
     */
    public function getFullNameAttribute()
    {
        return $this->unit_organisasi . ' - ' . $this->name;
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
     * UPDATED: Boot method dengan auto-generate code yang lebih baik
     */
    protected static function boot()
    {
        parent::boot();
        
        static::creating(function ($unit) {
            if (empty($unit->code)) {
                // Coba dapatkan kode dari mapping terlebih dahulu
                $mapping = self::UNIT_CODE_MAPPING;
                if (isset($mapping[$unit->unit_organisasi][$unit->name])) {
                    $unit->code = $mapping[$unit->unit_organisasi][$unit->name];
                } else {
                    // Fallback ke auto-generate
                    $unit->code = strtoupper(str_replace(' ', '_', $unit->name));
                }
            }
        });
        
        static::updating(function ($unit) {
            // Update code jika nama unit berubah dan ada mapping
            $mapping = self::UNIT_CODE_MAPPING;
            if (isset($mapping[$unit->unit_organisasi][$unit->name])) {
                $unit->code = $mapping[$unit->unit_organisasi][$unit->name];
            }
        });
    }
}