<?php

namespace App\Mcp\Tools\Reviews;

use App\Models\Review;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\Server\Attributes\Description;
use Laravel\Mcp\Server\Attributes\Name;
use Laravel\Mcp\Server\Tool;

#[Name('update_review')]
#[Description('Update an existing review. Identify it by id (use list_reviews first). Only the fields you provide are changed.')]
class UpdateReview extends Tool
{
    public function handle(Request $request): Response
    {
        $data = $request->validate([
            'id' => 'required|integer',
            'name' => 'sometimes|string|max:150',
            'rating' => 'sometimes|integer|min:1|max:5',
            'text' => 'sometimes|string',
            'tagline' => 'nullable|string|max:200',
            'is_active' => 'sometimes|boolean',
            'sort_order' => 'sometimes|integer|min:0',
        ]);

        $review = Review::find($data['id']);
        if (! $review) {
            return Response::error("No review found with id {$data['id']}.");
        }

        unset($data['id']);
        $review->fill($data)->save();

        return Response::text("Updated review #{$review->id} by \"{$review->name}\".");
    }

    public function schema(JsonSchema $schema): array
    {
        return [
            'id' => $schema->integer()->description('The review id to update (from list_reviews).')->required(),
            'name' => $schema->string()->description('Reviewer / customer name.'),
            'rating' => $schema->integer()->description('Star rating from 1 to 5.'),
            'text' => $schema->string()->description('The review text.'),
            'tagline' => $schema->string()->description('Short tagline or role.'),
            'is_active' => $schema->boolean()->description('Show or hide the review.'),
            'sort_order' => $schema->integer()->description('Lower numbers appear first.'),
        ];
    }
}
