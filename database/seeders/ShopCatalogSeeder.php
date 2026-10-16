<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class ShopCatalogSeeder extends Seeder
{
    /**
     * Fills an EMPTY catalogue with the sample data. Runs on every deploy, so
     * it must never touch existing rows — otherwise edits made in the admin
     * panel or via MCP would be reverted (and deleted samples re-created).
     */
    public function run(): void
    {
        // Read the raw config files directly so we always get the original
        // sample data, even after the DB has started overriding config.
        $shop = require config_path('shop.php');
        $products = require config_path('products.php');

        if (! Category::query()->exists()) {
            foreach ($shop['categories'] ?? [] as $i => $cat) {
                Category::create([
                    'slug' => $cat['slug'],
                    'name' => $cat['name'],
                    'description' => $cat['description'] ?? null,
                    'icon' => $cat['icon'] ?? null,
                    'types' => $cat['types'] ?? [],
                    'sort_order' => $i,
                    'is_active' => true,
                ]);
            }
        }

        if (! Product::query()->exists()) {
            foreach ($products as $i => $p) {
                Product::create([
                    'slug' => Str::slug($p['name']),
                    'name' => $p['name'],
                    'category' => $p['category'],
                    'type' => $p['type'] ?? null,
                    'form' => $p['form'] ?? null,
                    'price' => $p['price'],
                    'mrp' => $p['mrp'] ?? null,
                    'description' => $p['description'] ?? null,
                    'rating' => $p['rating'] ?? 0,
                    'reviews' => $p['reviews'] ?? 0,
                    'image' => $p['image'] ?? null,
                    'tags' => $p['tags'] ?? [],
                    'is_active' => true,
                    'sort_order' => $i,
                ]);
            }
        }
    }
}
