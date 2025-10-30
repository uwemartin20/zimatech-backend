<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Project extends Model
{
    protected $fillable = ['auftragsnummer', 'project_name'];

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
}
