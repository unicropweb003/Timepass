<?php

namespace App\Mcp\Tools\Reviews;

use App\Mcp\Support\Present;
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
        $data = $request->validate([
            'page' => 'nullable|integer|min:1',
            'per_page' => 'nullable|integer|min:1|max:50',
        ]);

        $page = Review::query()
            ->orderBy('sort_order')
            ->orderByDesc('id')
            ->paginate($data['per_page'] ?? 20, ['*'], 'page', $data['page'] ?? 1);

        $reviews = $page->getCollection()
            ->map(fn (Review $r) => [
                'id' => $r->id,
                'name' => $r->name,
                'tagline' => $r->tagline,
                'rating' => (int) $r->rating,
                'text' => $r->text,
                'is_active' => (bool) $r->is_active,
                'sort_order' => (int) $r->sort_order,
            ])->all();

        // Same keys as before; "pagination" is new (count = reviews on this page).
        return Response::json([
            'count' => count($reviews),
            'reviews' => $reviews,
            'pagination' => Present::page($page),
        ]);
    }

    public function schema(JsonSchema $schema): array
    {
        return [
            'page' => $schema->integer()->description('Page number (default 1).'),
            'per_page' => $schema->integer()->description('Items per page (default 20, max 50).'),
        ];
    }
}
