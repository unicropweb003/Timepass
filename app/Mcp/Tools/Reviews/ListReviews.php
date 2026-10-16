<?php

namespace App\Mcp\Tools\Reviews;

use App\Models\Review;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\Server\Attributes\Description;
use Laravel\Mcp\Server\Attributes\Name;
use Laravel\Mcp\Server\Tool;
use Laravel\Mcp\Server\Tools\Annotations\IsReadOnly;

#[Name('list_reviews')]
#[Description('List customer reviews/testimonials shown on the site, with their id, author name, rating, text and status.')]
#[IsReadOnly]
class ListReviews extends Tool
{
    public function handle(Request $request): Response
    {
        $reviews = Review::query()
            ->orderBy('sort_order')
            ->orderByDesc('id')
            ->get()
            ->map(fn (Review $r) => [
                'id' => $r->id,
                'name' => $r->name,
                'tagline' => $r->tagline,
                'rating' => (int) $r->rating,
                'text' => $r->text,
                'is_active' => (bool) $r->is_active,
                'sort_order' => (int) $r->sort_order,
            ])->all();

        return Response::json([
            'count' => count($reviews),
            'reviews' => $reviews,
        ]);
    }

    public function schema(JsonSchema $schema): array
    {
        return [];
    }
}
