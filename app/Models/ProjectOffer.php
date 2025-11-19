<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProjectOffer extends Model
{
    use HasFactory;

    protected $fillable = [
        'customer_name',
        'customer_email',
        'subject',
        'description',
        'assigned_user_id',
        'status',
    ];

    public function calculations()
    {
        return $this->hasMany(OfferCalculation::class);
    }

    public function files()
    {
        return $this->hasMany(OfferFile::class)->whereNull('offer_email_id');
    }

    public function emails()
    {
        return $this->hasMany(OfferEmail::class);
    }

    public function assignedUser()
    {
        return $this->belongsTo(User::class, 'assigned_user_id');
    }
}
