<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Organization extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'code',
        'description',
        'location',
        'status'
    ];

    public function employees()
    {
        return $this->hasMany(Employee::class);
    }
}