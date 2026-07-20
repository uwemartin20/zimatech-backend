<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class Material extends Model
{
    protected $fillable = [
        'name',
        'code',
        'description',
        'quantity',
        'on_hold_quantity',
        'order_quantity',
        'tablar',
        'threshold',
        'type',
        'unit',
        'image',
        'order_status',
        'lager_id',
        'is_werkzeug',
        'is_active',
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
        if (is_null($this->threshold) || (int) $this->threshold <= 0) {
            return 'ok';
        }

        return $this->quantity <= $this->threshold ? 'low' : 'ok';
    }

    public function suppliers()
    {
        // If your table name is 'material_supplier', Laravel guesses it automatically.
        // If you used 'material_suppliers' (plural), pass it as the second argument.
        return $this->belongsToMany(Supplier::class, 'material_suppliers')->withTimestamps();
    }

    public function lager()
    {
        return $this->belongsTo(Lager::class);
    }

    public function consumptionRecords()
    {
        return $this->hasMany(MaterialConsumption::class);
    }

    public function usedRecords()
    {
        return $this->consumptionRecords()->where('consumption_type', 'use');
    }

    public function returnedRecords()
    {
        return $this->consumptionRecords()->where('consumption_type', 'return');
    }

    public function werkzeug()
    {
        return $this->is_werkzeug;
    }

    public function active()
    {
        return $this->is_active;
    }

    public function orderStatus()
    {
        return $this->order_status;
    }

    /*
    |--------------------------------------------------------------------------
    | Scopes
    |--------------------------------------------------------------------------
    */

    public function scopeLowStock(Builder $q): Builder
    {
        return $q->whereNotNull('threshold')
            ->where('threshold', '>', 0)
            ->whereColumn('quantity', '<=', 'threshold');
    }

    public function scopeEmpty(Builder $q): Builder
    {
        return $q->where('quantity', 0);
    }

    public function scopeForStatus(Builder $q, string $status): Builder
    {
        return $q->where('order_status', $status);
    }

    /*
    |--------------------------------------------------------------------------
    | Helpers
    |--------------------------------------------------------------------------
    */

    public function mostRecentSupplier(): ?Supplier
    {
        return $this->suppliers()
            ->orderByDesc('material_suppliers.created_at')
            ->first();
    }

    public function getStatusLabelAttribute(): ?string
    {
        if ($this->order_status === null) {
            return null;
        }

        return match ($this->order_status) {
            'notified' => __('tablar.status.notified'),
            'ordered' => __('tablar.status.ordered'),
            'blocked' => __('tablar.status.blocked'),
            'delivered' => __('tablar.status.delivered'),
            default => ucfirst($this->order_status),
        };
    }
}
