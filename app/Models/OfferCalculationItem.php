<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OfferCalculationItem extends Model
{
    protected $fillable = [
        'offer_calculation_id',
        'project_service_id',
        'hours',
        'price_per_hour',
        'pieces',
        'price_per_unit',
        'cost_type',
        'comment',
        'total',
    ];

    public function calculation()
    {
        return $this->belongsTo(OfferCalculation::class, 'offer_calculation_id');
    }

    public function service()
    {
        return $this->belongsTo(ProjectService::class, 'project_service_id');
    }
}
