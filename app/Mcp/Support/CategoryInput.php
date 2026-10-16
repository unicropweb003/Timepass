<?php

namespace App\Mcp\Support;

use App\Models\Category;

/** Resolve the category a product should belong to, auto-creating by name. */
class CategoryInput
{
    /**
     * Uses category_id, else category_name (id/slug/name accepted). When a
     * name is given that matches nothing, a new active category is created.
     *
     * @return array{0: ?Category, 1: bool, 2: ?string} [category, created, error]
     */
    public static function resolve(array $data, bool $required): array
    {
        if (! empty($data['category_id'])) {
            $cat = Category::find((int) $data['category_id']);

            return $cat ? [$cat, false, null] : [null, false, "No category with id {$data['category_id']}. Use list_categories, or pass category_name to create one."];
        }

        $name = trim((string) ($data['category_name'] ?? ''));
        if ($name === '') {
            return $required
                ? [null, false, 'Give category_name (e.g. "Men\'s Wear") or category_id. A new category is created if the name does not exist.']
                : [null, false, null];
        }

        if ($cat = Finder::category($name)) {
            return [$cat, false, null];
        }

        $cat = Category::create([
            'name' => $name,
            'slug' => Finder::uniqueSlug(Category::class, $name),
            'types' => [],
            'is_active' => true,
            'sort_order' => (int) Category::max('sort_order') + 1,
        ]);

        return [$cat, true, null];
    }
}
