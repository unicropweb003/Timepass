<?php

namespace App\Support;

use Illuminate\Support\Collection;
use Illuminate\Support\Str;

class Seo
{
    private const CATEGORY_META = [
        'skin-care' => [
            'Shop Skin Care Products Online in India | TimePass',
            'Browse skin care at TimePass, including cleansers, serums, moisturizers and sunscreens. Compare product details and prices to find a routine that suits you.',
        ],
        'hair-care' => [
            'Shop Hair Care Products Online in India | TimePass',
            'Explore hair care at TimePass, from shampoos and conditioners to styling products. Compare ingredients, details and current prices before choosing your pick.',
        ],
        'makeup' => [
            'Shop Affordable Makeup Products Online in India | TimePass',
            'Explore makeup at TimePass, including lip, eye and face products. Browse the available selection, compare product details and check current prices in one place.',
        ],
        'nail-care' => [
            'Shop Nail Care Products Online in India | TimePass',
            'Browse nail care products at TimePass, from polish to nail essentials. Compare the available options, product details and current prices before you choose.',
        ],
        'hygiene-oral-care' => [
            'Shop Personal Hygiene & Oral Care Online | TimePass',
            'Explore personal hygiene and oral care at TimePass, including deodorants and toothpaste. Compare the available products, their details and prices online.',
        ],
        'fragrances' => [
            'Shop Fragrances & Body Sprays Online in India | TimePass',
            'Browse fragrances at TimePass, including perfumes and body sprays. Compare descriptions, sizes where listed and prices to find a scent that fits your day.',
        ],
        'body-care' => [
            'Shop Body Care Products Online in India | TimePass',
            'Explore body care at TimePass, including lotions and other everyday essentials. Compare the available products, descriptions and current prices before choosing.',
        ],
    ];

    public static function home(): array
    {
        $url = route('home');

        return [
            'title' => 'TimePass Beauty | Skin Care, Makeup & More Online India',
            'description' => 'Explore beauty and personal care at TimePass. Browse skin care, hair care, makeup, fragrances and more, with product details and current prices in one place.',
            'canonical' => $url,
            'schema' => [[
                '@type' => 'Organization',
                '@id' => $url.'#organization',
                'name' => 'TimePass',
                'url' => $url,
            ]],
        ];
    }

    public static function catalog(?array $category, ?string $type, ?array $form, ?int $minPrice, ?int $maxPrice, Collection $products): array
    {
        $categorySlug = $category['slug'] ?? null;
        $canonical = $categorySlug
            ? route('products', ['category' => $categorySlug])
            : route('products');
        [$title, $description] = self::CATEGORY_META[$categorySlug] ?? [
            'Shop Beauty & Personal Care Products Online | TimePass',
            'Browse beauty and personal care products at TimePass. Explore skin care, hair care, makeup, fragrances and more, then compare product details and prices.',
        ];
        if ($categorySlug && ! isset(self::CATEGORY_META[$categorySlug])) {
            $title = 'Shop '.Str::limit($category['name'], 29, '…').' Products Online | TimePass';
            $description = self::shorten('Browse '.$category['name'].' at TimePass. '.($category['description'] ?? '').' Compare available products, details and displayed prices before choosing your next item online in India.', 159);
        }

        // Type, form and price filters create many overlapping URL combinations.
        // Only the main catalog and populated category pages are search landing pages.
        $allowedQuery = $categorySlug ? ['category'] : [];
        $filtered = $type !== null || $form !== null || $minPrice !== null || $maxPrice !== null
            || array_diff(array_keys(request()->query()), $allowedQuery) !== [];
        $hasRealProducts = $products->contains(fn ($product) => isset($product['id']));
        $indexable = ! $filtered && ($categorySlug === null || $hasRealProducts);

        $schema = [[
            '@type' => 'BreadcrumbList',
            'itemListElement' => array_values(array_filter([
                ['@type' => 'ListItem', 'position' => 1, 'name' => 'Products', 'item' => route('products')],
                $categorySlug ? ['@type' => 'ListItem', 'position' => 2, 'name' => $category['name'], 'item' => $canonical] : null,
            ])),
        ]];

        return compact('title', 'description', 'canonical', 'schema') + [
            'robots' => $indexable ? 'index,follow' : 'noindex,follow',
        ];
    }

    public static function product(array $product): array
    {
        $url = route('product.show', ['product' => $product['slug']]);
        $name = trim((string) $product['name']);
        $title = self::productTitle($name, (string) $product['category']);
        $description = self::shorten('Explore '.$name.' at TimePass. '.($product['description'] ?? '').' See product details and the current displayed price, then ask us any questions before choosing.', 159);
        $realProduct = isset($product['id']);

        $schema = [];
        if ($realProduct) {
            $data = [
                '@type' => 'Product',
                'name' => $name,
                'url' => $url,
                'description' => trim((string) ($product['description'] ?? '')),
            ];
            if ($image = ImageUrl::resolve($product['image'] ?? null)) {
                $data['image'] = [$image];
            }
            if (isset($product['price']) && is_numeric($product['price'])) {
                $data['offers'] = [
                    '@type' => 'Offer',
                    'url' => $url,
                    'priceCurrency' => 'INR',
                    'price' => (float) $product['price'],
                ];
            }
            $schema[] = $data;
        }

        $schema[] = [
            '@type' => 'BreadcrumbList',
            'itemListElement' => [
                ['@type' => 'ListItem', 'position' => 1, 'name' => 'Home', 'item' => route('home')],
                ['@type' => 'ListItem', 'position' => 2, 'name' => $product['category'], 'item' => route('products', ['category' => $product['category_slug']])],
                ['@type' => 'ListItem', 'position' => 3, 'name' => $name, 'item' => $url],
            ],
        ];

        return compact('title', 'description', 'schema') + [
            'canonical' => $url,
            'robots' => $realProduct ? 'index,follow' : 'noindex,follow',
        ];
    }

    public static function about(): array
    {
        return [
            'title' => 'About TimePass Beauty | Our Online Beauty Store in India',
            'description' => 'Learn about TimePass and explore the beauty and personal care categories available in our store. Find skin care, hair care, makeup and fragrance collections.',
            'canonical' => route('about'),
        ];
    }

    public static function contact(): array
    {
        return [
            'title' => 'Contact TimePass | Beauty Product Help & Enquiries',
            'description' => 'Have a question about a TimePass product or an order? Use our contact form to send your enquiry. You can also browse the catalog for product details and prices.',
            'canonical' => route('contact'),
            'schema' => [[
                '@type' => 'FAQPage',
                'mainEntity' => [
                    [
                        '@type' => 'Question',
                        'name' => 'How can I ask about a product?',
                        'acceptedAnswer' => ['@type' => 'Answer', 'text' => 'Use the contact form and include the product name in your message.'],
                    ],
                    [
                        '@type' => 'Question',
                        'name' => 'Where can I find product prices?',
                        'acceptedAnswer' => ['@type' => 'Answer', 'text' => 'Open a product page from the catalog to see its current displayed price.'],
                    ],
                ],
            ]],
        ];
    }

    public static function privatePage(string $title): array
    {
        return [
            'title' => trim($title).' | TimePass',
            'robots' => 'noindex,follow',
        ];
    }

    private static function shorten(string $text, int $limit): string
    {
        return Str::limit(preg_replace('/\s+/u', ' ', trim($text)), $limit, '…');
    }

    private static function productTitle(string $name, string $category): string
    {
        $candidates = [
            $name.' | '.$category.' | TimePass',
            $name.' | '.$category.' Online | TimePass',
            $name.' | Shop '.$category.' Online | TimePass',
            $name.' | Shop '.$category.' Online in India | TimePass',
            $name.' | Personal Care Online | TimePass',
            $name.' | Beauty Products Online in India | TimePass',
            $name.' | Personal Care Online in India | TimePass',
        ];
        foreach ($candidates as $candidate) {
            if (mb_strlen($candidate) >= 50 && mb_strlen($candidate) <= 60) {
                return $candidate;
            }
        }

        return Str::limit($name, 42, '…').' | TimePass Beauty';
    }
}
