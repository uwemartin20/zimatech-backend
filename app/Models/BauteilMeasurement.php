<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BauteilMeasurement extends Model
{
    protected $fillable = [
        'bauteil_id', 'height', 'width', 'weight', 'depth',
        'thickness', 'radius', 'unit'
    ];

    public function bauteil()
    {
        return $this->belongsTo(Bauteil::class);
    }
}
