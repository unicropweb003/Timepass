<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Store contact details and social links (shown in the footer)
    |--------------------------------------------------------------------------
    |
    | PLACEHOLDERS: replace with the real details before going live. Set a
    | social link to null to hide its icon.
    |
    */

    'contact' => [
        'email' => 'support@timepass.example',
        'phone' => '+91 00000 00000',
        'address' => 'Your store address, City, State - 000000',
        'hours' => 'Mon - Sat, 10:00 AM - 7:00 PM',
    ],

    'social' => [
        'instagram' => 'https://instagram.com/',
        'facebook' => 'https://facebook.com/',
        'youtube' => 'https://youtube.com/',
        'x' => 'https://x.com/',
    ],

    /*
    |--------------------------------------------------------------------------
    | Product categories
    |--------------------------------------------------------------------------
    |
    | Cosmetics categories shown in the navbar "Shop by Category" menu and on
    | the Products page. Each category has product types (subcategories).
    |
    | 'slug'  is used in URLs: /products?category=skin-care&type=serums
    | 'types' maps each type's URL slug to its display name. Keep slugs stable
    |         once products use them; change the names freely.
    | 'icon'  is an outline SVG path (24x24, heroicons style).
    |
    */

    'categories' => [
        [
            'name' => 'Skin Care',
            'slug' => 'skin-care',
            'description' => 'Cleansers, serums, moisturizers and sunscreens',
            'icon' => 'M9.813 15.904 9 18.75l-.813-2.846a4.5 4.5 0 0 0-3.09-3.09L2.25 12l2.846-.813a4.5 4.5 0 0 0 3.09-3.09L9 5.25l.813 2.846a4.5 4.5 0 0 0 3.09 3.09L15.75 12l-2.846.813a4.5 4.5 0 0 0-3.09 3.09ZM18.259 8.715 18 9.75l-.259-1.035a3.375 3.375 0 0 0-2.455-2.456L14.25 6l1.036-.259a3.375 3.375 0 0 0 2.455-2.456L18 2.25l.259 1.035a3.375 3.375 0 0 0 2.456 2.456L21.75 6l-1.035.259a3.375 3.375 0 0 0-2.456 2.456Z',
            'types' => [
                'cleansers' => 'Cleansers',
                'toners' => 'Toners',
                'moisturizers' => 'Moisturizers',
                'serums' => 'Serums',
                'face-masks' => 'Face Masks',
                'exfoliants' => 'Exfoliants',
                'sunscreens' => 'Sunscreens',
                'anti-aging' => 'Anti-aging',
            ],
        ],
        [
            'name' => 'Hair Care',
            'slug' => 'hair-care',
            'description' => 'Shampoos, conditioners, colour and styling',
            'icon' => 'm7.848 8.25 1.536.887M7.848 8.25a3 3 0 1 1-5.196-3 3 3 0 0 1 5.196 3Zm1.536.887a2.165 2.165 0 0 1 1.083 1.839c.005.351.054.695.14 1.024M9.384 9.137l2.077 1.199M7.848 15.75l1.536-.887m-1.536.887a3 3 0 1 1-5.196 3 3 3 0 0 1 5.196-3Zm1.536-.887a2.165 2.165 0 0 0 1.083-1.838c.005-.352.054-.695.14-1.025m-1.223 2.863 2.077-1.199m0-3.328a4.323 4.323 0 0 1 2.068-1.379l5.325-1.628a4.5 4.5 0 0 1 2.48-.044l.803.215-7.794 4.5m-2.882-1.664A4.33 4.33 0 0 0 10.607 12m3.736 0 7.794 4.5-.802.215a4.5 4.5 0 0 1-2.48-.043l-5.326-1.629a4.324 4.324 0 0 1-2.068-1.379M14.343 12l-2.882 1.664',
            'types' => [
                'shampoos' => 'Shampoos',
                'conditioners' => 'Conditioners',
                'hair-dyes' => 'Hair Dyes',
                'straighteners' => 'Straighteners',
                'relaxers' => 'Relaxers',
                'styling-gels' => 'Styling Gels',
                'mousses' => 'Mousses',
                'hair-sprays' => 'Hair Sprays',
            ],
        ],
        [
            'name' => 'Makeup',
            'slug' => 'makeup',
            'description' => 'Colour cosmetics for face, eyes and lips',
            'icon' => 'M9.53 16.122a3 3 0 0 0-5.78 1.128 2.25 2.25 0 0 1-2.4 2.245 4.5 4.5 0 0 0 8.4-2.245c0-.399-.078-.78-.22-1.128Zm0 0a15.998 15.998 0 0 0 3.388-1.62m-5.043-.025a15.994 15.994 0 0 1 1.622-3.395m3.42 3.42a15.995 15.995 0 0 0 4.764-4.648l3.876-5.814a1.151 1.151 0 0 0-1.597-1.597L14.146 6.32a15.996 15.996 0 0 0-4.649 4.763m3.42 3.42a6.776 6.776 0 0 0-3.42-3.42',
            'types' => [
                'foundation' => 'Foundation',
                'concealer' => 'Concealer',
                'face-powder' => 'Face Powder & Compacts',
                'blush' => 'Blush',
                'highlighter' => 'Highlighter',
                'bronzer' => 'Bronzer',
                'eyeshadow' => 'Eyeshadow',
                'eyeliner' => 'Eyeliner',
                'mascara' => 'Mascara',
                'lipsticks' => 'Lipsticks',
            ],
        ],
        [
            'name' => 'Nail Care',
            'slug' => 'nail-care',
            'description' => 'Nail polish, hardeners and removers',
            'icon' => 'M4.098 19.902a3.75 3.75 0 0 0 5.304 0l6.401-6.402M6.75 21A3.75 3.75 0 0 1 3 17.25V4.125C3 3.504 3.504 3 4.125 3h5.25c.621 0 1.125.504 1.125 1.125v4.072M6.75 21a3.75 3.75 0 0 0 3.75-3.75V8.197M6.75 21h13.125c.621 0 1.125-.504 1.125-1.125v-5.25c0-.621-.504-1.125-1.125-1.125h-4.072M10.5 8.197l2.88-2.88c.438-.439 1.15-.439 1.59 0l3.712 3.713c.44.44.44 1.152 0 1.59l-2.879 2.88M6.75 17.25h.008v.008H6.75v-.008Z',
            'types' => [
                'nail-polish' => 'Nail Polish',
                'nail-hardeners' => 'Nail Hardeners',
                'cuticle-removers' => 'Cuticle Removers',
                'polish-removers' => 'Nail Polish Removers',
            ],
        ],
        [
            'name' => 'Personal Hygiene & Oral Care',
            'slug' => 'hygiene-oral-care',
            'description' => 'Deodorants, oral care and shaving',
            'icon' => 'M15.182 15.182a4.5 4.5 0 0 1-6.364 0M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0ZM9.75 9.75c0 .414-.168.75-.375.75S9 10.164 9 9.75 9.168 9 9.375 9s.375.336.375.75Zm-.375 0h.008v.015h-.008V9.75Zm5.625 0c0 .414-.168.75-.375.75s-.375-.336-.375-.75.168-.75.375-.75.375.336.375.75Zm-.375 0h.008v.015h-.008V9.75Z',
            'types' => [
                'deodorants' => 'Deodorants',
                'antiperspirants' => 'Antiperspirants',
                'toothpaste' => 'Toothpaste',
                'mouthwash' => 'Mouthwash',
                'shaving-creams' => 'Shaving Creams',
                'after-shave' => 'After-shave Lotions',
            ],
        ],
        [
            'name' => 'Fragrances',
            'slug' => 'fragrances',
            'description' => 'Perfumes, colognes and body sprays',
            'icon' => 'M9.75 3.104v5.714a2.25 2.25 0 0 1-.659 1.591L5 14.5M9.75 3.104c-.251.023-.501.05-.75.082m.75-.082a24.301 24.301 0 0 1 4.5 0m0 0v5.714c0 .597.237 1.17.659 1.591L19.8 15.3M14.25 3.104c.251.023.501.05.75.082M19.8 15.3l-1.57.393A9.065 9.065 0 0 1 12 15a9.065 9.065 0 0 0-6.23-.693L5 14.5m14.8.8 1.402 1.402c1.232 1.232.65 3.318-1.067 3.611A48.309 48.309 0 0 1 12 21c-2.773 0-5.491-.235-8.135-.687-1.718-.293-2.3-2.379-1.067-3.61L5 14.5',
            'types' => [
                'perfumes' => 'Perfumes',
                'colognes' => 'Colognes',
                'body-sprays' => 'Body Sprays',
            ],
        ],
        [
            'name' => 'Body Care',
            'slug' => 'body-care',
            'description' => 'Body lotions, shower gels and hand care',
            'icon' => 'M21 8.25c0-2.485-2.099-4.5-4.688-4.5-1.935 0-3.597 1.126-4.312 2.733-.715-1.607-2.377-2.733-4.313-2.733C5.1 3.75 3 5.765 3 8.25c0 7.22 9 12 9 12s9-4.78 9-12Z',
            'types' => [
                'body-lotions' => 'Body Lotions',
                'shower-gels' => 'Shower Gels',
                'hand-creams' => 'Hand Creams & Jellies',
                'talcum-powder' => 'Talcum Powder',
            ],
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Product forms
    |--------------------------------------------------------------------------
    |
    | Physical form of a product, a second way to filter on the Products page
    | (/products?form=creams). Works on its own or together with a category.
    |
    */

    'forms' => [
        ['name' => 'Creams & Emulsions', 'slug' => 'creams', 'examples' => 'Cold cream, vanishing cream, lotions and ointments'],
        ['name' => 'Powders', 'slug' => 'powders', 'examples' => 'Face powder, talcum powder and pressed compacts'],
        ['name' => 'Sticks', 'slug' => 'sticks', 'examples' => 'Lipsticks, deodorant sticks and blush cakes'],
        ['name' => 'Liquids & Solutions', 'slug' => 'liquids', 'examples' => 'Shampoos, shower gels, toners and after-shave lotions'],
        ['name' => 'Gels & Jellies', 'slug' => 'gels', 'examples' => 'Cleansing gels, hair gels and hand jellies'],
        ['name' => 'Aerosols', 'slug' => 'aerosols', 'examples' => 'Hair sprays, mousse and deodorant sprays'],
        ['name' => 'Pastes', 'slug' => 'pastes', 'examples' => 'Toothpaste and deodorant paste'],
    ],

];
