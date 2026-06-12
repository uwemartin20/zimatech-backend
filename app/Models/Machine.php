<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Machine extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'description', 'active', 'company'];

    public function timeRecords()
    {
        return $this->hasMany(TimeRecord::class);
    }

    public function productionSchedules()
    {
        return $this->hasMany(ProductionSchedule::class);
    }
}
