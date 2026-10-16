<?php

namespace App\Mcp\Tools\Reviews;

use App\Models\Review;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\Server\Attributes\Description;
use Laravel\Mcp\Server\Attributes\Name;
use Laravel\Mcp\Server\Tool;
use Laravel\Mcp\Server\Tools\Annotations\IsDestructive;

#[Name('delete_review')]
#[Description('Permanently delete a review by its id. This cannot be undone. Use list_reviews first to confirm the correct id.')]
#[IsDestructive]
class DeleteReview extends Tool
{
    public function handle(Request $request): Response
    {
        $data = $request->validate([
            'id' => 'required|integer',
        ]);

        $review = Review::find($data['id']);
        if (! $review) {
            return Response::error("No review found with id {$data['id']}.");
        }

        $name = $review->name;
        $review->delete();

        return Response::text("Deleted review #{$data['id']} by \"{$name}\".");
    }

    public function schema(JsonSchema $schema): array
    {
        return [
            'id' => $schema->integer()->description('The id of the review to delete (from list_reviews).')->required(),
        ];
    }
}
