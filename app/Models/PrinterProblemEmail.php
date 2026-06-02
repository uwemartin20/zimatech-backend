<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PrinterProblemEmail extends Model
{
    protected $table = 'printer_problem_emails';

    protected $fillable = [
        'problem_id', 'email_type', 'subject', 'body', 'ai_generated', 'direction'
    ];

    public function problem()
    {
        return $this->belongsTo(PrinterProblem::class, 'problem_id');
    }
}
