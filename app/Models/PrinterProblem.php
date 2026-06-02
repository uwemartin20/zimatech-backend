<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PrinterProblem extends Model
{
    protected $table = 'printer_problems';

    protected $fillable = [
        'problem_uid', 'order_number', 'designation', 'version_number', 'design_nozzle_diameter', 'tool_nozzle_diameter', 'material', 
        'print_temperature', 'bed_temperature', 'nozzle_height', 'offset_x', 'offset_y', 'offset_z', 'maintenance_completed', 'machine_error_id',
        'short_description', 'operator_explanation', 'issue_type', 'ai_troubleshooting', 'ai_next_steps', 'status', 'created_by'
    ];

    public function emails()
    {
        return $this->hasMany(PrinterProblemEmail::class, 'problem_id');
    }

    public function attachments()
    {
        return $this->hasMany(PrinterProblemAttachment::class, 'problem_id');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

}
