<?php

namespace Database\Seeders;

use App\Models\Review;
use Illuminate\Database\Seeder;

class ReviewSeeder extends Seeder
{
    public function run(): void
    {
        // Only seed an empty table: this runs on every deploy and must not
        // re-create reviews you deleted or re-show ones you hid.
        if (Review::query()->exists()) {
            return;
        }

        // Starter testimonials (previously hard-coded on the home page).
        // Replace with real customer reviews from the admin before going live.
        $reviews = [
            [
                'name' => 'Anjali Mehta',
                'tagline' => 'Skin care lover',
                'rating' => 5,
                'text' => 'The Vitamin C serum has made my skin look brighter in just a few weeks. It feels light, absorbs quickly and works well under makeup.',
            ],
            [
                'name' => 'Rohan Verma',
                'tagline' => 'Hair care fan',
                'rating' => 4,
                'text' => 'The keratin shampoo leaves my hair soft and much less frizzy. Delivery was quick and the products were packed nicely.',
            ],
        ];

        foreach ($reviews as $i => $r) {
            Review::create($r + ['is_active' => true, 'sort_order' => $i]);
        }
    }
}
