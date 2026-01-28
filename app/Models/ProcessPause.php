<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProcessPause extends Model
{
    protected $fillable = [
        'process_id',
        'pause_start',
        'pause_end',
        'pause_type',
        'reason',
    ];

    protected $casts = [
        'pause_start' => 'datetime',
        'pause_end' => 'datetime',
    ];

    public function process()
    {
        return $this->belongsTo(Process::class);
    }
}
