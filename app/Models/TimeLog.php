<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TimeLog extends Model
{
    protected $fillable = ['time_record_id', 'machine_status_id', 'start_time', 'end_time'];
    
    public function record() 
    { 
        return $this->belongsTo(TimeRecord::class, 'time_record_id'); 
    }
    public function status()
    {
        return $this->belongsTo(MachineStatus::class, 'machine_status_id');
    }
}
