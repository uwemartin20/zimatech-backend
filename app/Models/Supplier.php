<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Supplier extends Model
{
    protected $fillable = [
        'name', 'company', 'address', 'phone_number', 'email', 'website'
    ];

    public function services()
    {
        return $this->belongsToMany(ProjectService::class, 'supplier_services', 'supplier_id', 'service_id')->withTimestamps();
    }

    public function offers()
    {
        return $this->hasMany(SupplierOffer::class);
    }
}
