<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SupplierOffer extends Model
{
    protected $fillable = [
        'date', 'price', 'parent_offer_id', 'supplier_id', 'project_service_id',
        'bauteil_id', 'offer_number', 'description', 'duration', 'pieces_to_develop'
    ];

    public function supplier()
    {
        return $this->belongsTo(Supplier::class);
    }

    public function service()
    {
        return $this->belongsTo(ProjectService::class, 'project_service_id');
    }

    public function parentOffer()
    {
        return $this->belongsTo(SupplierOffer::class, 'parent_offer_id');
    }

    public function bauteil()
    {
        return $this->belongsTo(Bauteil::class);
    }

    public function project()
    {
        return $this->hasOne(SupplierProject::class);
    }

    public function children()
    {
        return $this->hasMany(self::class, 'parent_offer_id');
    }
}
