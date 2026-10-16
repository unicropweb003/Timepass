<?php

namespace App\Mcp\Tools\Reviews;

use App\Models\Review;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\Server\Attributes\Description;
use Laravel\Mcp\Server\Attributes\Name;
use Laravel\Mcp\Server\Tool;

#[Name('create_review')]
#[Description('Create a customer review/testimonial for the site. Author name, rating (1-5) and text are required.')]
class CreateReview extends Tool
{
    public function handle(Request $request): Response
    {
        $data = $request->validate([
            'name' => 'required|string|max:150',
            'rating' => 'required|integer|min:1|max:5',
            'text' => 'required|string',
            'tagline' => 'nullable|string|max:200',
            'is_active' => 'nullable|boolean',
            'sort_order' => 'nullable|integer|min:0',
        ]);

        $review = Review::create([
            'name' => $data['name'],
            'tagline' => $data['tagline'] ?? null,
            'rating' => $data['rating'],
            'text' => $data['text'],
            'is_active' => $data['is_active'] ?? true,
            'sort_order' => $data['sort_order'] ?? 0,
        ]);

        return Response::text("Created review #{$review->id} by \"{$review->name}\" ({$review->rating}/5).");
    }

    public function schema(JsonSchema $schema): array
    {
        return [
            'name' => $schema->string()->description('Reviewer / customer name.')->required(),
            'rating' => $schema->integer()->description('Star rating from 1 to 5.')->required(),
            'text' => $schema->string()->description('The review text.')->required(),
            'tagline' => $schema->string()->description('Short tagline or role, e.g. "Verified buyer".'),
            'is_active' => $schema->boolean()->description('Whether the review is shown (default true).'),
            'sort_order' => $schema->integer()->description('Lower numbers appear first (default 0).'),
        ];
    }
}
