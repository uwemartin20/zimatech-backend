<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TimeRecord extends Model
{
    protected $fillable = ['user_id', 'project_id', 'position_id', 'machine_id', 'start_time', 'end_time'];
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function project()
    {
        return $this->belongsTo(Project::class);
    }

    public function position()
    {
        return $this->belongsTo(Position::class);
    }

    public function machine()
    {
        return $this->belongsTo(Machine::class);
    }

    public function logs() 
    { 
        return $this->hasMany(TimeLog::class); 
    }

    public function getTotalDurationAttribute()
    {
        if (!$this->end_time) {
            return 0;
        }

        return $this->end_time->diffInMinutes($this->start_time);
    }

}
