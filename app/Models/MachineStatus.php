<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MachineStatus extends Model
{
    protected $fillable = ['name', 'color', 'active'] ;

    public function logs() 
    { 
        return $this->hasMany(TimeLog::class); 
    }
}
