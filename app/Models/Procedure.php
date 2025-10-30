<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Procedure extends Model
{
    protected $fillable = ['project_id', 'bauteil_id', 'start_time', 'end_time', 'source_file'];

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function bauteil()
    {
        return $this->belongsTo(Bauteil::class);
    }

    public function processes(): HasMany
    {
        return $this->hasMany(Process::class);
    }
}
