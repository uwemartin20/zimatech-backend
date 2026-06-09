<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Material extends Model
{
    protected $fillable = [
        'name',
        'quantity',
        'tablar',
        'threshold',
        'type',
    ];

    protected $casts = [
        'quantity' => 'integer',
        'threshold' => 'integer',
    ];

    /*
    |--------------------------------------------------------------------------
    | Accessors
    |--------------------------------------------------------------------------
    */

    public function getStatusAttribute()
    {
        if (is_null($this->threshold)) {
            return 'ok';
        }

        return $this->quantity <= $this->threshold ? 'low' : 'ok';
    }

    public function suppliers()
    {
        // If your table name is 'material_supplier', Laravel guesses it automatically.
        // If you used 'material_suppliers' (plural), pass it as the second argument.
        return $this->belongsToMany(Supplier::class, 'material_suppliers');
    }
}
