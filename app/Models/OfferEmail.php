<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OfferEmail extends Model
{
    use HasFactory;

    protected $fillable = [
        'project_offer_id', 'sender', 'recipient', 'subject', 'body', 'direction',
    ];

    public function offer()
    {
        return $this->belongsTo(ProjectOffer::class);
    }

    public function files()
    {
        return $this->hasMany(OfferFile::class, 'offer_email_id');
    }
}
