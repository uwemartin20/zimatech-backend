<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\TimeRecord;

class Machine extends Model
{
    protected $fillable = ['name', 'description'];
    public function timeRecords()
    {
        return $this->hasMany(TimeRecord::class);
    }
}
