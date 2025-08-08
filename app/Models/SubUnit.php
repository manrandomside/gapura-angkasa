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
     * Get full name dengan unit organisasi
     */
    public function getFullNameAttribute()
    {
        return $this->unit->unit_organisasi . ' - ' . $this->unit->name . ' - ' . $this->name;
    }
}