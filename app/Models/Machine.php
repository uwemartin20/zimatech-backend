<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\TimeRecord;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Machine extends Model
{
    use HasFactory;
    protected $fillable = ['name', 'description', 'active', 'company'];
    public function timeRecords()
    {
        return $this->hasMany(TimeRecord::class);
    }
}
