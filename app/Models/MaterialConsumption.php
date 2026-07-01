<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MaterialConsumption extends Model
{
    protected $table = 'material_consumption';

    protected $fillable = [
        'material_id',
        'quantity',
        'consumption_type',
        'consumption_time',
    ];

    public function material()
    {
        return $this->belongsTo(Material::class);
    }

    public function scopeUsed($query)
    {
        return $query->where('consumption_type', 'use');
    }

    public function scopeReturned($query)
    {
        return $query->where('consumption_type', 'return');
    }

    public function scopeBetweenDates($query, $startDate, $endDate)
    {
        return $query->whereBetween('consumption_time', [$startDate, $endDate]);
    }
}
