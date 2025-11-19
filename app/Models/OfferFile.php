<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OfferFile extends Model
{
    use HasFactory;

    protected $fillable = [
        'project_offer_id', 'offer_email_id', 'file_name', 'file_path', 'description', 'uploaded_by'
    ];

    public function offer()
    {
        return $this->belongsTo(ProjectOffer::class, 'project_offer_id');
    }

    public function email()
    {
        return $this->belongsTo(OfferEmail::class, 'offer_email_id');
    }

    public function uploader()
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }
}
