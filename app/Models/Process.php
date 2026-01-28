<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Process extends Model
{
    protected $fillable = ['project_id', 'procedure_id', 'bauteil_id', 'machine_id', 'name', 'start_time', 'end_time', 'count', 'source_file', 'total_seconds'];

    public function project()
    {
        return $this->belongsTo(Project::class);
    }

    public function procedure()
    {
        return $this->belongsTo(Procedure::class);
    }

    public function bauteil()
    {
        return $this->belongsTo(Bauteil::class);
    }

    public function machine()
    {
        return $this->belongsTo(Machine::class);
    }

    public function getDurationSecondsAttribute()
    {
        if (!$this->start_time || !$this->end_time) {
            return null;
        }
        return strtotime($this->end_time) - strtotime($this->start_time);
    }

    public function pauses()
    {
        return $this->hasMany(ProcessPause::class);
    }
}
