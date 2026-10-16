<?php

namespace App\Mcp\Support;

use App\Models\Setting;

/** Single-record site content (About Us, contact info) kept in settings. */
class SiteContent
{
    public const ABOUT_DEFAULTS = [
        'title' => 'Beauty essentials for your everyday self-care',
        'content' => 'Your one-stop beauty store for skin care, hair care, makeup, fragrances and more — trusted products at honest prices, so self-care feels simple and joyful.',
        'image' => null,
    ];

    public const CONTACT_FIELDS = ['address', 'email', 'phone', 'hours', 'map_link', 'whatsapp'];

    public static function about(): array
    {
        $stored = Setting::get('about', []);

        return array_merge(self::ABOUT_DEFAULTS, is_array($stored) ? $stored : []);
    }

    public static function presentAbout(): array
    {
        $about = static::about();

        return [
            'title' => $about['title'],
            'content' => $about['content'],
            'image_url' => Present::imageUrl($about['image']),
            'page_url' => route('about'),
        ];
    }

    /** Contact info = config/shop.php defaults overlaid with saved settings. */
    public static function contact(): array
    {
        $file = require config_path('shop.php');
        $contact = array_merge($file['contact'] ?? [], (array) (Setting::get('contact', []) ?: []));
        $social = array_merge($file['social'] ?? [], (array) (Setting::get('social', []) ?: []));

        $info = [];
        foreach (self::CONTACT_FIELDS as $field) {
            $info[$field] = $contact[$field] ?? null;
        }
        $info['social'] = array_filter($social, fn ($link) => $link !== null && $link !== '');
        $info['page_url'] = route('contact');

        return $info;
    }
}
