<?php

namespace App\Mcp\Tools\Contact;

use App\Mcp\Support\Present;
use App\Models\ContactMessage;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\Server\Attributes\Description;
use Laravel\Mcp\Server\Attributes\Name;
use Laravel\Mcp\Server\Tool;

#[Name('get_contact_message')]
#[Description('Read one contact-form message in full by id. Marks it as read unless mark_read is false.')]
class GetContactMessage extends Tool
{
    public function handle(Request $request): Response
    {
        $data = $request->validate([
            'id' => 'required|integer',
            'mark_read' => 'nullable|boolean',
        ]);

        $message = ContactMessage::find($data['id']);
        if (! $message) {
            return Response::error("No contact message with id {$data['id']}. Use list_contact_messages.");
        }

        if (($data['mark_read'] ?? true) && $message->read_at === null) {
            $message->update(['read_at' => now()]);
        }

        return Present::reply("Message #{$message->id} from {$message->name}.", [
            'id' => $message->id,
            'name' => $message->name,
            'email' => $message->email,
            'phone' => $message->phone,
            'subject' => $message->subject,
            'message' => $message->message,
            'status' => $message->status,
            'customer_id' => $message->user_id,
            'read_at' => $message->read_at?->toIso8601String(),
            'received_at' => $message->created_at?->toIso8601String(),
        ]);
    }

    public function schema(JsonSchema $schema): array
    {
        return [
            'id' => $schema->integer()->description('Message id (from list_contact_messages).')->required(),
            'mark_read' => $schema->boolean()->description('Default true.'),
        ];
    }
}
