<?php

namespace App\Mcp\Servers;

use App\Mcp\Tools\About\GetAboutUs;
use App\Mcp\Tools\About\UpdateAboutUs;
use App\Mcp\Tools\Categories\CreateCategory;
use App\Mcp\Tools\Categories\DeleteCategory;
use App\Mcp\Tools\Categories\GetCategory;
use App\Mcp\Tools\Categories\ListCategories;
use App\Mcp\Tools\Categories\UpdateCategory;
use App\Mcp\Tools\Contact\DeleteContactMessage;
use App\Mcp\Tools\Contact\GetContactInfo;
use App\Mcp\Tools\Contact\GetContactMessage;
use App\Mcp\Tools\Contact\ListContactMessages;
use App\Mcp\Tools\Contact\UpdateContactInfo;
use App\Mcp\Tools\Products\CreateProduct;
use App\Mcp\Tools\Products\DeleteProduct;
use App\Mcp\Tools\Products\GetProduct;
use App\Mcp\Tools\Products\ListProducts;
use App\Mcp\Tools\Products\UpdateProduct;
use App\Mcp\Tools\Reviews\CreateReview;
use App\Mcp\Tools\Reviews\DeleteReview;
use App\Mcp\Tools\Reviews\ListReviews;
use App\Mcp\Tools\Reviews\UpdateReview;
use Laravel\Mcp\Server;
use Laravel\Mcp\Server\Attributes\Instructions;
use Laravel\Mcp\Server\Attributes\Name;
use Laravel\Mcp\Server\Attributes\Version;
use Laravel\Mcp\Server\Tool;

#[Name('TimePass Store')]
#[Version('2.0.0')]
#[Instructions(<<<'TXT'
Manage the TimePass online store: products, product categories, the About Us page, contact info (plus contact-form messages) and customer reviews.

- One request is enough: create_product takes a category_name and creates the category if needed, and generates + uploads an AI product photo automatically when no image is given.
- Images: pass image_url (public https link) to use a real photo, image_upload_id from POST /upload, or image_source "none" for no image. Never invent file paths.
- Identify records for get/update/delete by id, slug or exact name.
- delete_* tools need confirm=true — only pass it when the user clearly asked to delete.
- Prices are whole rupees (INR). "price" is the regular price, "sale_price" the discounted price.
- If a reply says an image was NOT saved, tell the user why and offer to retry.
TXT)]
class StoreServer extends Server
{
    /** Show every tool on the first page so clients that ignore cursors still see them all. */
    public int $defaultPaginationLength = 50;

    /**
     * @var array<int, class-string<Tool>>
     */
    protected array $tools = [
        // Products
        CreateProduct::class,
        UpdateProduct::class,
        DeleteProduct::class,
        GetProduct::class,
        ListProducts::class,
        // Categories
        CreateCategory::class,
        UpdateCategory::class,
        DeleteCategory::class,
        GetCategory::class,
        ListCategories::class,
        // About Us
        GetAboutUs::class,
        UpdateAboutUs::class,
        // Contact
        GetContactInfo::class,
        UpdateContactInfo::class,
        ListContactMessages::class,
        GetContactMessage::class,
        DeleteContactMessage::class,
        // Reviews
        ListReviews::class,
        CreateReview::class,
        UpdateReview::class,
        DeleteReview::class,
    ];
}
