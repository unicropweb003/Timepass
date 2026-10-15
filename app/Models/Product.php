<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    protected $fillable = [
        'name', 'slug', 'category', 'type', 'form', 'price', 'mrp',
        'description', 'details', 'rating', 'reviews', 'image', 'tags',
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
            'id' => $this->id,
            'name' => $this->name,
            'slug' => $this->slug,
            'category' => $this->category,
            'type' => $this->type,
            'form' => $this->form,
            'price' => (int) $this->price,
            'mrp' => $this->mrp !== null ? (int) $this->mrp : null,
            'description' => $this->description,
            'details' => $this->details,
            'rating' => (float) $this->rating,
            'reviews' => (int) $this->reviews,
            'image' => $this->image,
            'tags' => $this->tags ?: [],
        ];
    }
}
