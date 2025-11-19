<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OfferCalculation extends Model
{
    use HasFactory;

    protected $fillable = [
        'project_offer_id',
        'designation',
        'hours',
        'cost',
        'material_cost',
        'external_cost',
        'extra_tax',
        'final_offer',
        'pieces',
        'total_cost',
        'offer_cost',
        'notes',
        'created_by',
    ];

    public function items()
    {
        return $this->hasMany(OfferCalculationItem::class);
    }
    
    public function offer()
    {
        return $this->belongsTo(ProjectOffer::class, 'project_offer_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    // 🔹 Computed attributes
    public function getGesamtKostenAttribute()
    {
        $pieces = $this->pieces ?: 1;
        $base = ($this->total_cost ?? 0);
        return $base * $pieces;
    }

    public function getGesamtAngebotAttribute()
    {
        $pieces = $this->pieces ?: 1;
        return ($this->offer_cost ?? 0) * $pieces;
    }
}
