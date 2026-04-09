<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Feedback extends Model
{
    protected $table = 'feedback';

    protected $fillable = [
        'type',
        'machine',
        'department',
        'error_code',
        'problem',
        'solution',
        'name',
        'attachment',
    ];
}
