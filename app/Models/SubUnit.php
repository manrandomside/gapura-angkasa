<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SubUnit extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'code',
        'unit_id', 
        'description',
        'is_active'
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    /**
     * Get unit yang owns this sub unit
     */
    public function unit()
    {
        return $this->belongsTo(Unit::class);
    }

    /**
     * Get employees yang belongs to this sub unit
     */
    public function employees()
    {
        return $this->hasMany(Employee::class);
    }

    /**
     * Scope untuk sub unit aktif
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope untuk filter berdasarkan unit
     */
    public function scopeByUnit($query, $unitId)
    {
        return $query->where('unit_id', $unitId);
    }

    /**
     * UPDATED: Get full name dengan unit organisasi
     * Menggunakan unit code (bukan nama panjang) untuk konsistensi
     */
    public function getFullNameAttribute()
    {
        if (!$this->unit) {
            return $this->name;
        }
        
        return $this->unit->unit_organisasi . ' - ' . $this->unit->name . ' - ' . $this->name;
    }

    /**
     * NEW: Get formatted full name dengan nama panjang unit
     * Format: Unit Organisasi - (XX) Nama Panjang Unit - Sub Unit
     */
    public function getFormattedFullNameAttribute()
    {
        if (!$this->unit) {
            return $this->name;
        }
        
        return $this->unit->unit_organisasi . ' - ' . $this->unit->formatted_display_name . ' - ' . $this->name;
    }

    /**
     * NEW: Get display name untuk dropdown/select
     */
    public function getDisplayNameAttribute()
    {
        return $this->name;
    }

    /**
     * NEW: Get full display name dengan context unit
     */
    public function getFullDisplayNameAttribute()
    {
        if (!$this->unit) {
            return $this->name;
        }
        
        // Format: Sub Unit (Unit Code)
        return $this->name . ' (' . $this->unit->name . ')';
    }

    /**
     * NEW: Get hierarchical display name
     */
    public function getHierarchicalNameAttribute()
    {
        if (!$this->unit) {
            return $this->name;
        }
        
        // Format: Unit Organisasi > Unit Code > Sub Unit
        return $this->unit->unit_organisasi . ' > ' . $this->unit->name . ' > ' . $this->name;
    }

    /**
     * NEW: Get sub unit dengan unit code context
     */
    public function getSubUnitWithUnitCodeAttribute()
    {
        if (!$this->unit) {
            return $this->name;
        }
        
        return $this->name . ' (' . $this->unit->name . ')';
    }

    /**
     * Get active sub units count untuk unit ini
     */
    public function getActiveEmployeesCountAttribute()
    {
        return $this->employees()->count();
    }

    /**
     * Check if sub unit has employees
     */
    public function hasEmployees()
    {
        return $this->employees()->count() > 0;
    }

    /**
     * Get sub unit by name and unit
     */
    public static function findByNameAndUnit($name, $unitId)
    {
        return self::where('name', $name)
                   ->where('unit_id', $unitId)
                   ->first();
    }

    /**
     * Get sub unit by code and unit
     */
    public static function findByCodeAndUnit($code, $unitId)
    {
        return self::where('code', $code)
                   ->where('unit_id', $unitId)
                   ->first();
    }

    /**
     * Scope untuk sub unit berdasarkan unit organisasi
     */
    public function scopeByUnitOrganisasi($query, $unitOrganisasi)
    {
        return $query->whereHas('unit', function($q) use ($unitOrganisasi) {
            $q->where('unit_organisasi', $unitOrganisasi);
        });
    }

    /**
     * Get all sub units with their unit information
     */
    public static function withUnitInfo()
    {
        return self::with(['unit' => function($query) {
            $query->select('id', 'name', 'code', 'unit_organisasi');
        }]);
    }

    /**
     * Get sub units grouped by unit organisasi
     */
    public static function groupedByUnitOrganisasi()
    {
        return self::active()
                   ->with('unit')
                   ->get()
                   ->groupBy(function($subUnit) {
                       return $subUnit->unit ? $subUnit->unit->unit_organisasi : 'Unknown';
                   });
    }

    /**
     * Get sub units untuk specific unit dengan employee count
     */
    public static function forUnitWithEmployeeCount($unitId)
    {
        return self::active()
                   ->byUnit($unitId)
                   ->withCount('employees')
                   ->get()
                   ->map(function($subUnit) {
                       $subUnit->has_employees = $subUnit->employees_count > 0;
                       return $subUnit;
                   });
    }

    /**
     * NEW: Get formatted name untuk consistency dengan Unit model
     */
    public function getFormattedName()
    {
        return $this->formatted_full_name;
    }

    /**
     * Boot method untuk auto-generate code
     */
    protected static function boot()
    {
        parent::boot();
        
        static::creating(function ($subUnit) {
            if (empty($subUnit->code) && !empty($subUnit->name)) {
                // Generate code dari nama sub unit
                $code = strtoupper(str_replace([' ', '/', '&', '-', '.'], ['_', '_', '_', '_', '_'], $subUnit->name));
                
                // Limit length untuk database compatibility
                if (strlen($code) > 50) {
                    $code = substr($code, 0, 50);
                }
                
                $subUnit->code = $code;
            }
        });
        
        static::updating(function ($subUnit) {
            // Update code jika nama berubah dan code kosong
            if ($subUnit->isDirty('name') && empty($subUnit->code)) {
                $code = strtoupper(str_replace([' ', '/', '&', '-', '.'], ['_', '_', '_', '_', '_'], $subUnit->name));
                
                if (strlen($code) > 50) {
                    $code = substr($code, 0, 50);
                }
                
                $subUnit->code = $code;
            }
        });
    }
}