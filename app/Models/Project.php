<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Project extends Model
{
    use HasFactory;
    protected $fillable = ['auftragsnummer_zf', 'auftragsnummer_zt', 'project_name', 'from_machine_logs', 'project_status_id', 'start_time', 'end_time'];

    protected $casts = [
        'start_time' => 'datetime',
        'end_time'   => 'datetime',
    ];

    public function procedures(): HasMany
    {
        return $this->hasMany(Procedure::class);
    }

    public function status()
    {
        return $this->belongsTo(ProjectStatus::class, 'project_status_id');
    }

    public function bauteile()
    {
        return $this->hasMany(Bauteil::class);
    }

    public function processes()
    {
        return $this->hasMany(Process::class);
    }

    public function getBauteileCountAttribute()
    {
        return $this->bauteile()->count();
    }

    public function getGesamtzeitAttribute()
    {
        $time = 0;

        // Add process seconds
        $time += $this->processes()->sum('total_seconds');

        return $time;
    }

    public function positions()
    {
        return $this->hasMany(Position::class);
    }
}
