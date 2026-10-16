<?php

namespace App\Mcp\Tools\Contact;

use App\Models\ContactMessage;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\Server\Attributes\Description;
use Laravel\Mcp\Server\Attributes\Name;
use Laravel\Mcp\Server\Tool;
use Laravel\Mcp\Server\Tools\Annotations\IsDestructive;

#[Name('delete_contact_message')]
#[Description('Permanently delete a contact-form message by id. You MUST pass confirm=true.')]
#[IsDestructive]
class DeleteContactMessage extends Tool
{
    public function handle(Request $request): Response
    {
        $data = $request->validate([
            'id' => 'required|integer',
            'confirm' => 'nullable',
        ]);

        $message = ContactMessage::find($data['id']);
        if (! $message) {
            return Response::error("No contact message with id {$data['id']}.");
        }

        if (! filter_var($data['confirm'] ?? false, FILTER_VALIDATE_BOOLEAN)) {
            return Response::error("Not deleted. To delete message #{$message->id} from {$message->name}, call again with confirm=true.");
        }

        $message->delete();

        return Response::text("Deleted contact message #{$data['id']}.");
    }

    public function schema(JsonSchema $schema): array
    {
        return [
            'id' => $schema->integer()->description('Message id.')->required(),
            'confirm' => $schema->boolean()->description('Must be true to delete.')->required(),
        ];
    }
}
