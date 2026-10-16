<?php

namespace App\Mcp\Support;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

/** Look up records by id, slug or exact name (case-insensitive). */
class Finder
{
    public static function product(string|int|null $ref): ?Product
    {
        return static::find(Product::class, $ref);
    }

    public static function category(string|int|null $ref): ?Category
    {
        return static::find(Category::class, $ref);
    }

    /** @template T of Model @param class-string<T> $class @return T|null */
    private static function find(string $class, string|int|null $ref): ?Model
    {
        if ($ref === null || $ref === '') {
            return null;
        }

        $ref = trim((string) $ref);

        if (ctype_digit($ref) && ($hit = $class::find((int) $ref))) {
            return $hit;
        }

        return $class::where('slug', $ref)->first()
            ?? $class::where('slug', Str::slug($ref))->first()
            ?? $class::whereRaw('LOWER(name) = ?', [mb_strtolower($ref)])->first();
    }

    /** A slug based on $text that is unique for $class (ignoring $ignoreId). */
    public static function uniqueSlug(string $class, string $text, ?int $ignoreId = null): string
    {
        $base = Str::slug($text) ?: 'item';
        $slug = $base;
        $i = 1;
        while ($class::where('slug', $slug)->when($ignoreId, fn ($q) => $q->where('id', '!=', $ignoreId))->exists()) {
            $slug = $base.'-'.(++$i);
        }

        return $slug;
    }
}
