<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Position extends Model
{
    protected $fillable = [
        'project_id',
        'name',
        'project_service_id',
    ];

    public function project()
    {
        return $this->belongsTo(Project::class);
    }

    public function projectService()
    {
        return $this->belongsTo(ProjectService::class);
    }
}
