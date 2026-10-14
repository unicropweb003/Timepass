<?php

/*
|--------------------------------------------------------------------------
| Product catalogue (SAMPLE DATA)
|--------------------------------------------------------------------------
|
| Used by the Products page (filtered by category / type / form) and the home
| page carousels ('tags': best-seller, recommended). 'category', 'type' and
| 'form' are slugs from config/shop.php. 'price' and optional 'mrp' (original price,
| shown struck through) are in rupees. 'image' is a path in public/ or null
| for the "No image" placeholder. Ratings and review counts are made up:
| replace them with real data before going live.
|
*/

return [
    ['name' => 'Vitamin C Brightening Serum', 'category' => 'skin-care', 'type' => 'serums', 'form' => 'liquids', 'price' => 899, 'mrp' => 1199, 'description' => 'Lightweight serum with vitamin C for a brighter, more even-looking skin tone.', 'rating' => 4.8, 'reviews' => 312, 'image' => null, 'tags' => ['best-seller']],
    ['name' => 'Hydrating Gel Moisturizer', 'category' => 'skin-care', 'type' => 'moisturizers', 'form' => 'gels', 'price' => 649, 'mrp' => 799, 'description' => 'Oil-free gel moisturizer with hyaluronic acid for all-day hydration.', 'rating' => 4.7, 'reviews' => 268, 'image' => null, 'tags' => ['best-seller']],
    ['name' => 'SPF 50 Sunscreen Lotion', 'category' => 'skin-care', 'type' => 'sunscreens', 'form' => 'creams', 'price' => 499, 'mrp' => 599, 'description' => 'Broad-spectrum SPF 50 protection with a light, non-greasy feel.', 'rating' => 4.7, 'reviews' => 176, 'image' => null, 'tags' => ['best-seller']],
    ['name' => 'Gentle Foaming Cleanser', 'category' => 'skin-care', 'type' => 'cleansers', 'form' => 'gels', 'price' => 349, 'mrp' => null, 'description' => 'Soap-free foaming cleanser that removes dirt and makeup without drying.', 'rating' => 4.6, 'reviews' => 143, 'image' => null, 'tags' => ['recommended']],
    ['name' => 'Rose Water Toner', 'category' => 'skin-care', 'type' => 'toners', 'form' => 'liquids', 'price' => 299, 'mrp' => 349, 'description' => 'Alcohol-free toner with rose water to refresh and balance skin.', 'rating' => 4.4, 'reviews' => 92, 'image' => null, 'tags' => []],
    ['name' => 'Keratin Smooth Shampoo', 'category' => 'hair-care', 'type' => 'shampoos', 'form' => 'liquids', 'price' => 549, 'mrp' => 699, 'description' => 'Sulphate-free shampoo with keratin for smooth, frizz-free hair.', 'rating' => 4.6, 'reviews' => 198, 'image' => null, 'tags' => ['best-seller']],
    ['name' => 'Argan Oil Conditioner', 'category' => 'hair-care', 'type' => 'conditioners', 'form' => 'creams', 'price' => 599, 'mrp' => null, 'description' => 'Nourishing conditioner with argan oil for soft, manageable hair.', 'rating' => 4.5, 'reviews' => 121, 'image' => null, 'tags' => ['recommended']],
    ['name' => 'Strong Hold Hair Gel', 'category' => 'hair-care', 'type' => 'styling-gels', 'form' => 'gels', 'price' => 199, 'mrp' => 249, 'description' => 'Non-sticky styling gel for all-day hold with a natural finish.', 'rating' => 4.3, 'reviews' => 77, 'image' => null, 'tags' => []],
    ['name' => 'Matte Liquid Lipstick', 'category' => 'makeup', 'type' => 'lipsticks', 'form' => 'liquids', 'price' => 399, 'mrp' => 499, 'description' => 'Long-wear matte lipstick with a smooth, non-drying finish.', 'rating' => 4.6, 'reviews' => 241, 'image' => null, 'tags' => ['best-seller']],
    ['name' => 'Creamy Bullet Lipstick', 'category' => 'makeup', 'type' => 'lipsticks', 'form' => 'sticks', 'price' => 349, 'mrp' => null, 'description' => 'Rich, creamy colour in one swipe with a comfortable satin finish.', 'rating' => 4.5, 'reviews' => 134, 'image' => null, 'tags' => []],
    ['name' => 'Volumizing Mascara', 'category' => 'makeup', 'type' => 'mascara', 'form' => 'liquids', 'price' => 449, 'mrp' => 549, 'description' => 'Buildable mascara for fuller, longer-looking lashes.', 'rating' => 4.4, 'reviews' => 109, 'image' => null, 'tags' => ['recommended']],
    ['name' => 'Pressed Compact Powder', 'category' => 'makeup', 'type' => 'face-powder', 'form' => 'powders', 'price' => 299, 'mrp' => null, 'description' => 'Lightweight pressed powder that controls shine and sets makeup.', 'rating' => 4.3, 'reviews' => 88, 'image' => null, 'tags' => []],
    ['name' => 'Quick-Dry Nail Polish', 'category' => 'nail-care', 'type' => 'nail-polish', 'form' => 'liquids', 'price' => 149, 'mrp' => 199, 'description' => 'Glossy, chip-resistant colour that dries in about a minute.', 'rating' => 4.3, 'reviews' => 87, 'image' => null, 'tags' => ['recommended']],
    ['name' => 'Fresh Mint Toothpaste', 'category' => 'hygiene-oral-care', 'type' => 'toothpaste', 'form' => 'pastes', 'price' => 99, 'mrp' => null, 'description' => 'Fluoride toothpaste with a cool mint taste for fresh breath.', 'rating' => 4.5, 'reviews' => 98, 'image' => null, 'tags' => ['recommended']],
    ['name' => '48h Deodorant Stick', 'category' => 'hygiene-oral-care', 'type' => 'deodorants', 'form' => 'sticks', 'price' => 249, 'mrp' => 299, 'description' => 'Alcohol-free deodorant stick for long-lasting freshness.', 'rating' => 4.2, 'reviews' => 64, 'image' => null, 'tags' => []],
    ['name' => 'Floral Eau de Parfum', 'category' => 'fragrances', 'type' => 'perfumes', 'form' => 'liquids', 'price' => 1499, 'mrp' => 1999, 'description' => 'Long-lasting floral fragrance with notes of rose and jasmine.', 'rating' => 4.5, 'reviews' => 154, 'image' => null, 'tags' => ['best-seller']],
    ['name' => 'Citrus Body Spray', 'category' => 'fragrances', 'type' => 'body-sprays', 'form' => 'aerosols', 'price' => 299, 'mrp' => null, 'description' => 'Light, zesty body spray for an all-day fresh feel.', 'rating' => 4.2, 'reviews' => 71, 'image' => null, 'tags' => []],
    ['name' => 'Shea Butter Body Lotion', 'category' => 'body-care', 'type' => 'body-lotions', 'form' => 'creams', 'price' => 399, 'mrp' => 449, 'description' => 'Rich body lotion with shea butter for soft, smooth skin.', 'rating' => 4.6, 'reviews' => 132, 'image' => null, 'tags' => ['recommended']],
];
