<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PrinterProblemAttachment extends Model
{
    protected $table = 'printer_problem_attachments';

    protected $fillable = [
        'problem_id', 'file_name', 'file_path', 'mime_type', 'file_size', 'type', 'uploaded_by',
    ];

    public function problem()
    {
        return $this->belongsTo(PrinterProblem::class, 'problem_id');
    }

    public function uploadedBy()
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }
}
