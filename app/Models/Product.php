<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    protected $fillable = [
        'name', 'slug', 'category', 'type', 'form', 'price', 'mrp',
        'description', 'rating', 'reviews', 'image', 'tags',
        'is_active', 'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'tags' => 'array',
            'rating' => 'float',
            'is_active' => 'boolean',
        ];
    }

    /**
     * Shape this record the way config('products') entries look, so the
     * existing ShopController catalogue keeps working unchanged.
     */
    public function toConfigArray(): array
    {
        return [
            'name' => $this->name,
            'category' => $this->category,
            'type' => $this->type,
            'form' => $this->form,
            'price' => (int) $this->price,
            'mrp' => $this->mrp !== null ? (int) $this->mrp : null,
            'description' => $this->description,
            'rating' => (float) $this->rating,
            'reviews' => (int) $this->reviews,
            'image' => $this->image,
            'tags' => $this->tags ?: [],
        ];
    }
}
