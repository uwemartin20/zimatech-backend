<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Project extends Model
{
    protected $fillable = ['auftragsnummer', 'project_name', 'from_machine_logs'];

    public function procedures(): HasMany
    {
        return $this->hasMany(Procedure::class);
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

    // 🔹 Sum of all process durations
    public function getGesamtzeitAttribute()
    {
        $time = 0;

        // Add process seconds
        $time += $this->processes()->sum('total_seconds');

        return $time;
    }
}
