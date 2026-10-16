<?php

namespace App\Models;

use App\Support\ShopData;
use Illuminate\Database\Eloquent\Model;

class Review extends Model
{
    protected $fillable = [
        'name', 'tagline', 'rating', 'text', 'is_active', 'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'rating' => 'integer',
            'is_active' => 'boolean',
        ];
    }

    /** Clear the cached storefront data whenever a review changes. */
    protected static function booted(): void
    {
        static::saved(fn () => ShopData::flush());
        static::deleted(fn () => ShopData::flush());
    }
}
