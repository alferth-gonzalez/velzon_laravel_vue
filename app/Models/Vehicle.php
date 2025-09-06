<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Vehicle extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = ['plate', 'description', 'driver_id', 'maintenance_at', 'notes'];

    protected $casts = [
        'maintenance_at' => 'date',
    ];

    public function driver()
    {
        return $this->belongsTo(User::class, 'driver_id');
    }
}
