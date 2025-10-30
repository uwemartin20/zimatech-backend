<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Bauteil extends Model
{
    use HasFactory;

    protected $table = 'bauteile'; // table name

    protected $fillable = [
        'name',
        'project_id',
        'parent_id',
    ];

    /**
     * The project this Bauteil belongs to
     */
    public function project()
    {
        return $this->belongsTo(Project::class);
    }

    /**
     * Procedures associated with this Bauteil
     */
    public function procedures()
    {
        return $this->hasMany(Procedure::class);
    }

    /**
     * Parent Bauteil (if any)
     */
    public function parent()
    {
        return $this->belongsTo(Bauteil::class, 'parent_id');
    }

    /**
     * Child Bauteile
     */
    public function children()
    {
        return $this->hasMany(Bauteil::class, 'parent_id');
    }

    /**
     * Processes associated with this Bauteil
     */
    public function processes()
    {
        return $this->hasMany(Process::class, 'bauteil_id');
    }
}
