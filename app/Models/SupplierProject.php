<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SupplierProject extends Model
{
    protected $fillable = [
        'name', 'supplier_offer_id', 'project_status_id',
        'start_date', 'checkup_date', 'end_date',
        'extra_note', 'additional_expense'
    ];

    public function offer()
    {
        return $this->belongsTo(SupplierOffer::class, 'supplier_offer_id');
    }

    public function status()
    {
        return $this->belongsTo(ProjectStatus::class, 'project_status_id');
    }

    public function getGesamtpreisAttribute()
    {
        $offer = $this->offer;

        if (!$offer) {
            return 0;
        }

        $baseTotal = ($offer->price ?? 0) * ($offer->pieces_to_develop ?? 1);

        // Add extra expense if any
        $total = $baseTotal + ($this->additional_expense ?? 0);

        return $total;
    }
}
