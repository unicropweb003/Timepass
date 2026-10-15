<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    protected $fillable = [
        'name', 'slug', 'description', 'icon', 'types', 'sort_order', 'is_active',
    ];

    protected function casts(): array
    {
        return [
            'types' => 'array',
            'is_active' => 'boolean',
        ];
    }

    /**
     * Shape this record the way config('shop.categories') entries look,
     * so the existing front-end keeps working unchanged.
     */
    public function toConfigArray(): array
    {
        return [
            'name' => $this->name,
            'slug' => $this->slug,
            'description' => $this->description,
            'icon' => $this->icon,
            'types' => $this->types ?: [],
        ];
    }
}
