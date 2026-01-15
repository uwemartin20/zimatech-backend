<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProjectService extends Model
{
    protected $fillable = ['name', 'color', 'active', 'parent_id'];

    public function suppliers()
    {
        return $this->belongsToMany(Supplier::class, 'supplier_services', 'service_id', 'supplier_id')->withTimestamps();
    }

    public function children()
    {
        return $this->hasMany(ProjectService::class, 'parent_id');
    }

    public function parent()
    {
        return $this->belongsTo(ProjectService::class, 'parent_id');
    }

    public function getCascadingSelectOptions()
    {
        $tree = [];
        $current = $this;

        while ($current) {
            $siblings = self::where('parent_id', $current->parent_id)
                            ->orderBy('name')
                            ->get()
                            ->map(function ($sibling) use ($current) {
                                return [
                                    'id' => $sibling->id,
                                    'name' => $sibling->name,
                                    'selected' => $sibling->id === $current->id,
                                ];
                            })
                            ->toArray();

            // prepend to build tree from root to child
            array_unshift($tree, $siblings);

            $current = $current->parent;
        }

        return $tree;
    }
}
