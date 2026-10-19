<?php

namespace Tests\Feature;

use Tests\TestCase;

class SeoTest extends TestCase
{
    private function realProduct(): array
    {
        return [
            'id' => 42,
            'name' => 'Test Serum',
            'slug' => 'test-serum',
            'category' => 'skin-care',
            'type' => 'serums',
            'form' => 'liquids',
            'price' => 499,
            'mrp' => null,
            'description' => 'A sample description for a persisted product.',
            'rating' => 0,
            'reviews' => 0,
            'image' => null,
            'tags' => [],
        ];
    }

    public function test_sitemap_lists_only_public_pages_and_persisted_products(): void
    {
        config(['products' => [
            $this->realProduct(),
            ['name' => 'Fallback Only', 'slug' => 'fallback-only', 'category' => 'skin-care'],
        ]]);

        $response = $this->get('/sitemap.xml')->assertOk()
            ->assertHeader('Content-Type', 'application/xml; charset=UTF-8');

        $xml = simplexml_load_string($response->getContent());
        $this->assertNotFalse($xml);
        $this->assertCount(6, $xml->url);
        $this->assertStringContainsString('/product/test-serum', $response->getContent());
        $this->assertStringNotContainsString('fallback-only', $response->getContent());
        $this->assertStringNotContainsString('/search', $response->getContent());
        $this->assertStringNotContainsString('/account', $response->getContent());
    }

    public function test_product_metadata_uses_persisted_details_without_sample_reviews(): void
    {
        config(['products' => [$this->realProduct()]]);

        $response = $this->get('/product/test-serum')->assertOk();

        $response->assertSee('<link rel="canonical" href="'.route('product.show', 'test-serum').'">', false);
        $response->assertSee('"@type":"Product"', false);
        $response->assertSee('"priceCurrency":"INR"', false);
        $response->assertDontSee('aggregateRating');
        $response->assertDontSee('reviewCount');
    }

    public function test_filter_and_search_pages_are_not_indexed(): void
    {
        config(['products' => [$this->realProduct()]]);

        $this->get('/products?category=skin-care')->assertOk()
            ->assertSee('<meta name="robots" content="index,follow">', false);
        $this->get('/products?category=skin-care&type=serums')->assertOk()
            ->assertSee('<meta name="robots" content="noindex,follow">', false)
            ->assertSee('href="'.route('products', ['category' => 'skin-care']).'"', false);
        $this->get('/search?q=serum')->assertOk()
            ->assertSee('<meta name="robots" content="noindex,follow">', false);
    }

    public function test_contact_faq_schema_matches_visible_answers(): void
    {
        $this->get('/contact')->assertOk()
            ->assertSee('How can I ask about a product?')
            ->assertSee('Use the contact form and include the product name in your message.')
            ->assertSee('"@type":"FAQPage"', false);
    }

    public function test_public_json_ld_is_valid_json(): void
    {
        config(['products' => [$this->realProduct()]]);
        foreach (['/', '/products?category=skin-care', '/product/test-serum', '/contact'] as $path) {
            $html = $this->get($path)->assertOk()->getContent();
            preg_match_all('#<script type="application/ld\+json">(.*?)</script>#s', $html, $matches);
            $this->assertNotEmpty($matches[1], $path);
            foreach ($matches[1] as $json) {
                $data = json_decode($json, true);
                $this->assertSame(JSON_ERROR_NONE, json_last_error(), $path);
                $this->assertArrayHasKey('@context', $data, $path.' '.$json);
                $this->assertSame('https://schema.org', $data['@context']);
            }
        }
    }

    public function test_homepage_does_not_show_fallback_testimonials(): void
    {
        $this->get('/')->assertOk()
            ->assertDontSee('Anjali Mehta')
            ->assertDontSee('Rohan Verma')
            ->assertDontSee('What our customers say');
    }

    public function test_public_landing_page_copy_stays_within_requested_lengths(): void
    {
        config(['products' => [$this->realProduct()]]);
        $paths = ['/', '/products', '/about', '/contact'];
        foreach (config('shop.categories', []) as $category) {
            $paths[] = '/products?category='.$category['slug'];
        }

        $violations = [];
        foreach ($paths as $path) {
            $html = $this->get($path)->assertOk()->getContent();
            preg_match('/<title>(.*?)<\/title>/s', $html, $title);
            preg_match('/<meta name="description" content="(.*?)">/s', $html, $description);
            $titleLength = mb_strlen(html_entity_decode($title[1] ?? ''));
            $descriptionLength = mb_strlen(html_entity_decode($description[1] ?? ''));
            if ($titleLength < 50 || $titleLength > 60 || $descriptionLength < 150 || $descriptionLength > 160) {
                $violations[$path] = [$titleLength, $descriptionLength];
            }
        }

        $this->assertSame([], $violations);
    }

    public function test_sample_catalog_product_metadata_stays_in_requested_range(): void
    {
        $violations = [];
        $categories = collect(config('shop.categories'))->keyBy('slug');
        foreach (config('products', []) as $product) {
            $product['slug'] = \Illuminate\Support\Str::slug($product['name']);
            $product['category_slug'] = $product['category'];
            $product['category'] = $categories[$product['category']]['name'];
            $seo = \App\Support\Seo::product($product);
            $titleLength = mb_strlen($seo['title']);
            $descriptionLength = mb_strlen($seo['description']);
            if ($titleLength < 50 || $titleLength > 60 || $descriptionLength < 150 || $descriptionLength > 160) {
                $violations[$product['slug']] = [$titleLength, $descriptionLength];
            }
        }

        $this->assertSame([], $violations);
    }
}
