<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TimeChangeRequest extends Model
{
    protected $fillable = ['time_record_id', 'requested_by', 'reason', 'payload', 'status', 'approved_by', 'approved_at', 'record_start_time', 'record_end_time'];

    /**
     * Relationship: The time record this change request belongs to
     */
    public function timeRecord()
    {
        return $this->belongsTo(TimeRecord::class);
    }

    /**
     * Relationship: The user who submitted the change request
     */
    public function requestedBy()
    {
        return $this->belongsTo(User::class, 'requested_by');
    }

    public function approvedBy()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }
}
