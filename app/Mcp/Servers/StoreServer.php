<?php

namespace App\Mcp\Servers;

use App\Mcp\Tools\Categories\CreateCategory;
use App\Mcp\Tools\Categories\DeleteCategory;
use App\Mcp\Tools\Categories\ListCategories;
use App\Mcp\Tools\Categories\UpdateCategory;
use App\Mcp\Tools\Products\CreateProduct;
use App\Mcp\Tools\Products\DeleteProduct;
use App\Mcp\Tools\Products\GenerateProductImage;
use App\Mcp\Tools\Products\ListProducts;
use App\Mcp\Tools\Products\SetProductImage;
use App\Mcp\Tools\Products\UpdateProduct;
use App\Mcp\Tools\Reviews\CreateReview;
use App\Mcp\Tools\Reviews\DeleteReview;
use App\Mcp\Tools\Reviews\ListReviews;
use App\Mcp\Tools\Reviews\UpdateReview;
use App\Mcp\Tools\Settings\GetSettings;
use App\Mcp\Tools\Settings\UpdateSettings;
use Laravel\Mcp\Server;
use Laravel\Mcp\Server\Attributes\Instructions;
use Laravel\Mcp\Server\Attributes\Name;
use Laravel\Mcp\Server\Attributes\Version;
use Laravel\Mcp\Server\Tool;

#[Name('TimePass Store')]
#[Version('1.0.0')]
#[Instructions('Manage the TimePass storefront content: products, categories, customer reviews, and site settings (contact + social links). Always list records first to get their id/slug before updating or deleting. Prices are whole rupees (INR). Deletes are permanent.')]
class StoreServer extends Server
{
    /**
     * The tools registered with this MCP server.
     *
     * @var array<int, class-string<Tool>>
     */
    protected array $tools = [
        // Products
        ListProducts::class,
        CreateProduct::class,
        UpdateProduct::class,
        DeleteProduct::class,
        SetProductImage::class,
        GenerateProductImage::class,
        // Categories
        ListCategories::class,
        CreateCategory::class,
        UpdateCategory::class,
        DeleteCategory::class,
        // Reviews
        ListReviews::class,
        CreateReview::class,
        UpdateReview::class,
        DeleteReview::class,
        // Settings
        GetSettings::class,
        UpdateSettings::class,
    ];
}
