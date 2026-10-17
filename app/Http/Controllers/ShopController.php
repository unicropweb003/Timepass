<?php

namespace App\Http\Controllers;

use App\Models\ContactMessage;
use App\Support\ShopData;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class ShopController extends Controller
{
    public function home()
    {
        // Current offer shown below the home banner. Edit these values to change it.
        $offer = [
            'badge' => 'Limited time offer',
            'title' => 'Festive Mega Sale',
            'discount' => '40%',
            'code' => 'TIMEPASS40',
            'ends_at' => '2026-10-31 23:59:59',
            'description' => 'Celebrate the season with flat 40% off on our entire collection. Pick your favourites, apply the code at checkout and save big on every order.',
            'benefits' => [
                'Flat 40% off on all products',
                'Free delivery on orders above ₹499',
                'Easy 7-day returns and exchanges',
                'Extra gift wrapping at no cost',
            ],
            'terms' => [
                'Valid till' => '31 Oct 2026',
                'Minimum order' => '₹999',
                'Max discount' => '₹2,000',
                'Usage' => 'Once per customer',
            ],
        ];

        // Static promo banners. Images (800x1100) live in public/images/promos/.
        // The text is drawn over the empty top part of each photo. 'accent' colours the small label,
        // 'tint' is the photo's backdrop colour (keeps the text readable), and 'category' (plus optional
        // 'type') are the slugs from config/shop.php the banner links to (null = all products).
        $promos = [
            [
                'image' => 'images/promos/skincare-kit.jpg',
                'alt' => 'Skin care kit: cleanser, white treatment bottle and rich moisturizer in front of a kraft box',
                'eyebrow' => 'Skin Care',
                'title' => '3-Step Skin Care Kit',
                'tagline' => 'Cleanser, custom treatment and rich moisturizer in one box.',
                'accent' => '#3730a3',
                'tint' => '#c0b7dc',
                'category' => 'skin-care',
            ],
            [
                'image' => 'images/promos/toothpaste.jpg',
                'alt' => 'Three purifying toothpaste tubes in orange, blue and green standing on a rock',
                'eyebrow' => 'Oral Care',
                'title' => 'Purifying Toothpaste',
                'tagline' => 'SuperMint with fluoride: Sensitive, Whitening and Classic.',
                'accent' => '#c2410c',
                'tint' => '#b79a82',
                'category' => 'hygiene-oral-care',
                'type' => 'toothpaste',
            ],
            [
                'image' => 'images/promos/electrolyte.jpg',
                'alt' => 'Two amber bottles of unflavoured electrolyte concentrate on white stone',
                'eyebrow' => 'Hydration',
                'title' => 'Electrolyte Concentrate',
                'tagline' => 'Happy hydration in every glass. Unflavoured, 250 ml.',
                'accent' => '#9a3412',
                'tint' => '#ddbc9d',
                'category' => null,
            ],
            [
                'image' => 'images/promos/keratin-hair-care.jpg',
                'alt' => 'Silk keratin hair treatment tube and shampoo bottle surrounded by foam',
                'eyebrow' => 'Hair Care',
                'title' => 'Silk Keratin Hair Care',
                'tagline' => 'Micro proteins and amino acids for smooth, soft hair.',
                'accent' => '#78350f',
                'tint' => '#f3e9d3',
                'category' => 'hair-care',
            ],
        ];

        // Home page carousels: products from the catalogue (config/products.php) by tag.
        $bestSellers = [
            'title' => 'Best Sellers',
            'link' => route('products'),
            'products' => $this->catalog()->filter(fn ($p) => in_array('best-seller', $p['tags'], true))->values()->all(),
        ];

        $recommended = [
            'title' => 'Recommended for You',
            'link' => route('products'),
            'products' => $this->catalog()->filter(fn ($p) => in_array('recommended', $p['tags'], true))->values()->all(),
        ];

        // Banners: photo on one side, text on a coloured panel that fades into it.
        // 'panel' is the panel colour, 'dark' = light text on a dark panel. Images in public/images/banners/.
        $wideBanners = [
            [
                'image' => 'images/banners/grooming-set.jpg',
                'alt' => 'Day cream, facial oil and hard wax on a glass shelf',
                'eyebrow' => 'Grooming essentials',
                'title' => 'Look sharp, feel fresh',
                'text' => 'Day cream, facial oil and styling wax for a simple daily routine.',
                'cta' => 'Shop skin care',
                'url' => route('products', ['category' => 'skin-care']),
                'panel' => '#4b3f42',
                'dark' => true,
            ],
            [
                'image' => 'images/banners/keratin-care.jpg',
                'alt' => 'Silk keratin hair treatment and shampoo with foam',
                'eyebrow' => 'Hair care',
                'title' => 'Silky smooth, every wash',
                'text' => 'Keratin shampoo and treatment for soft, tangle-free hair.',
                'cta' => 'Shop hair care',
                'url' => route('products', ['category' => 'hair-care']),
                'panel' => '#cdba91',
                'dark' => false,
            ],
        ];

        $dualBanners = [
            [
                'image' => 'images/banners/skincare-lineup.jpg',
                'alt' => 'Cleanser, sunscreen, blush, balm, toner and brow gel on white blocks',
                'eyebrow' => 'Everyday edit',
                'title' => 'Your daily skin care, sorted',
                'text' => 'Cleanser, sunscreen, toner and a touch of colour.',
                'cta' => 'Shop skin care',
                'url' => route('products', ['category' => 'skin-care']),
                'panel' => '#dde8f2',
                'dark' => false,
            ],
            [
                'image' => 'images/banners/balm-sunscreen.jpg',
                'alt' => 'Lip and skin balm next to a daily sunscreen and a cold drink',
                'eyebrow' => 'Summer ready',
                'title' => 'Sun care that feels like nothing',
                'text' => 'Daily SPF and a soothing balm for lips and skin.',
                'cta' => 'Shop sunscreens',
                'url' => route('products', ['category' => 'skin-care', 'type' => 'sunscreens']),
                'panel' => '#f4ece4',
                'dark' => false,
            ],
        ];

        // Customer reviews for the "What our customers say" section. These are now
        // managed from the admin panel (Reviews). The sample reviews below are only a
        // fallback shown when no reviews exist in the database yet.
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

        // Active reviews come from the cached storefront data (no query per page
        // view). If the table is empty or not migrated, the samples above stay.
        if ($dbReviews = ShopData::reviews()) {
            $reviews = $dbReviews;
        }

        return view('home', compact('offer', 'promos', 'bestSellers', 'recommended', 'reviews', 'wideBanners', 'dualBanners'));
    }

    public function products(Request $request)
    {
        $categories = config('shop.categories', []);
        $forms = config('shop.forms', []);

        // Filters: ?category=skin-care&type=serums&form=liquids. Unknown values are ignored,
        // so an outdated link just shows the wider list.
        $category = collect($categories)->firstWhere('slug', $request->query('category'));
        $typeSlug = $request->query('type');
        $type = $category && is_string($typeSlug) && isset($category['types'][$typeSlug]) ? $typeSlug : null;
        $form = collect($forms)->firstWhere('slug', $request->query('form'));

        // Price filter: ?min_price=300&max_price=600 (rupees, whole numbers; either end optional).
        $price = fn (string $key) => is_string($v = $request->query($key)) && ctype_digit($v) ? (int) $v : null;
        $minPrice = $price('min_price');
        $maxPrice = $price('max_price');
        if ($minPrice !== null && $maxPrice !== null && $minPrice > $maxPrice) {
            [$minPrice, $maxPrice] = [$maxPrice, $minPrice];
        }

        $catalog = $this->catalog();
        $inCategory = fn ($p) => (! $category || $p['category_slug'] === $category['slug']) && (! $type || $p['type_slug'] === $type);
        $inForm = fn ($p) => ! $form || $p['form_slug'] === $form['slug'];
        $inPrice = fn ($p) => ($minPrice === null || $p['price'] >= $minPrice) && ($maxPrice === null || $p['price'] <= $maxPrice);

        $products = $catalog->filter($inCategory)->filter($inForm)->filter($inPrice)->values();

        // Sidebar counts: each section counts products matching the *other* active filters.
        $forCategories = $catalog->filter($inForm)->filter($inPrice);
        $counts = [
            'all' => $forCategories->count(),
            'categories' => $forCategories->countBy('category_slug')->all(),
            'types' => $forCategories->countBy(fn ($p) => $p['category_slug'].'/'.$p['type_slug'])->all(),
            'forms' => $catalog->filter($inCategory)->filter($inPrice)->countBy('form_slug')->all(),
        ];

        // Quick price ranges in the sidebar: [label, min, max] (null = open-ended).
        $priceRanges = [
            ['Under ₹300', null, 299],
            ['₹300 - ₹600', 300, 600],
            ['₹600 - ₹1,000', 600, 1000],
            ['Over ₹1,000', 1000, null],
        ];
        $forPrices = $catalog->filter($inCategory)->filter($inForm);
        $priceRanges = array_map(fn ($r) => [...$r, $forPrices->filter(
            fn ($p) => ($r[1] === null || $p['price'] >= $r[1]) && ($r[2] === null || $p['price'] <= $r[2])
        )->count()], $priceRanges);

        return view('products', compact('categories', 'forms', 'category', 'type', 'form', 'products', 'counts', 'minPrice', 'maxPrice', 'priceRanges'));
    }

    /**
     * Catalogue products (config/products.php) with display names and links added,
     * in the shape the product card partial expects.
     */
    private function catalog()
    {
        $categories = collect(config('shop.categories', []))->keyBy('slug');

        return collect(config('products', []))
            // Show a product as long as its category exists. The type is optional:
            // products added without a (matching) type still appear on the site.
            ->filter(fn ($p) => isset($categories[$p['category'] ?? null]))
            ->map(function ($p) use ($categories) {
                $slug = $p['slug'] ?? Str::slug($p['name']);
                $categorySlug = $p['category'];
                $typeSlug = $p['type'] ?? null;
                $category = $categories[$categorySlug];
                $typeName = ($typeSlug !== null && isset($category['types'][$typeSlug]))
                    ? $category['types'][$typeSlug]
                    : null;

                return array_merge($p, [
                    'slug' => $slug,
                    'category_slug' => $categorySlug,
                    'type_slug' => $typeSlug,
                    'form_slug' => $p['form'] ?? null,
                    'url' => route('product.show', ['product' => $slug]),
                    'category' => $category['name'],
                    'type' => $typeName,
                ]);
            })
            ->values();
    }

    /**
     * Single product detail page (looked up by slug).
     */
    public function show(string $product)
    {
        $catalog = $this->catalog();
        $item = $catalog->firstWhere('slug', $product);
        abort_if($item === null, 404);

        $related = $catalog
            ->where('category_slug', $item['category_slug'])
            ->where('slug', '!=', $item['slug'])
            ->take(4)->values();

        return view('product', ['product' => $item, 'related' => $related]);
    }

    public function about()
    {
        return view('about');
    }

    public function contact()
    {
        return view('contact');
    }

    public function search(Request $request)
    {
        $q = trim((string) $request->query('q', ''));

        $pages = [
            ['title' => 'Home', 'route' => 'home', 'description' => 'Welcome to TimePass home page.'],
            ['title' => 'Products', 'route' => 'products', 'description' => 'Browse our products page.'],
            ['title' => 'About', 'route' => 'about', 'description' => 'Learn more about TimePass.'],
            ['title' => 'Contact', 'route' => 'contact', 'description' => 'Get in touch with us.'],
        ];

        $results = $q === '' ? [] : array_values(array_filter($pages, function ($p) use ($q) {
            return str_contains(strtolower($p['title']), strtolower($q))
                || str_contains(strtolower($p['description']), strtolower($q));
        }));

        return view('search', compact('results', 'q'));
    }

    public function contactSubmit(Request $request)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255'],
            'phone' => ['nullable', 'string', 'max:20', 'regex:/^\+?[0-9\s\-()]{7,20}$/'],
            'subject' => ['nullable', 'string', Rule::in(ContactMessage::SUBJECTS)],
            'message' => ['required', 'string', 'min:5', 'max:5000'],
        ], [
            'phone.regex' => 'Please enter a valid phone number.',
        ]);

        // Saved as a lead for the admin panel (Contact Leads).
        ContactMessage::create($data + [
            'user_id' => $request->user()?->id,
            'ip_address' => $request->ip(),
        ]);

        return redirect()->route('contact')->with('contact_sent', [
            'name' => $data['name'],
            'email' => $data['email'],
            'phone' => $data['phone'] ?? null,
        ]);
    }
}
