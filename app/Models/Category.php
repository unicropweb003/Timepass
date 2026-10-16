<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Category extends Model
{
    protected $fillable = [
        'parent_id', 'name', 'slug', 'description', 'icon', 'image', 'types', 'sort_order', 'is_active',
    ];

    protected function casts(): array
    {
        return [
            'types' => 'array',
            'is_active' => 'boolean',
            'parent_id' => 'integer',
        ];
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(Category::class, 'parent_id');
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
