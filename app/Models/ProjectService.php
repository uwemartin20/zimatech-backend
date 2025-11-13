<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProjectService extends Model
{
    protected $fillable = ['name', 'color'];

    public function suppliers()
    {
        return $this->belongsToMany(Supplier::class, 'supplier_services', 'service_id', 'supplier_id')->withTimestamps();
    }
}
