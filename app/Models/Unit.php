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
     * Unit Organisasi options
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
     * Get sub units yang belongs to this unit
     */
    public function subUnits()
    {
        return $this->hasMany(SubUnit::class)->where('is_active', true);
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
}