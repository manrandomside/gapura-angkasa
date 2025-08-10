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
     * Get display name for dropdown
     */
    public function getDisplayNameAttribute()
    {
        $subUnitsCount = $this->active_sub_units_count;
        if ($subUnitsCount > 0) {
            return $this->name . " ({$subUnitsCount} sub units)";
        }
        return $this->name;
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
     * Boot method untuk auto-generate code jika tidak ada
     */
    protected static function boot()
    {
        parent::boot();
        
        static::creating(function ($unit) {
            if (empty($unit->code)) {
                $unit->code = strtoupper(str_replace(' ', '_', $unit->name));
            }
        });
    }
}