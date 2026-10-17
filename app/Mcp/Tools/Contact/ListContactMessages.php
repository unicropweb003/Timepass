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
use Laravel\Mcp\Server\Tools\Annotations\IsReadOnly;

#[Name('list_contact_messages')]
#[Description('List messages visitors sent through the website contact form, newest first, with search, unread filter and pagination.')]
#[IsReadOnly]
class ListContactMessages extends Tool
{
    public function handle(Request $request): Response
    {
        $data = $request->validate([
            'search' => 'nullable|string|max:100',
            'unread_only' => 'nullable|boolean',
            'page' => 'nullable|integer|min:1',
            'per_page' => 'nullable|integer|min:1|max:100',
        ]);

        $page = ContactMessage::query()
            ->when($data['search'] ?? null, fn ($q, $s) => $q->where(fn ($w) => $w
                ->whereRaw('LOWER(name) LIKE ?', ['%'.mb_strtolower($s).'%'])
                ->orWhereRaw('LOWER(email) LIKE ?', ['%'.mb_strtolower($s).'%'])
                ->orWhere('phone', 'like', '%'.$s.'%')
                ->orWhereRaw('LOWER(message) LIKE ?', ['%'.mb_strtolower($s).'%'])))
            ->when(! empty($data['unread_only']), fn ($q) => $q->whereNull('read_at'))
            ->latest('id')
            ->paginate($data['per_page'] ?? 25, ['*'], 'page', $data['page'] ?? 1);

        $items = $page->getCollection()->map(fn (ContactMessage $m) => [
            'id' => $m->id,
            'name' => $m->name,
            'email' => $m->email,
            'phone' => $m->phone,
            'subject' => $m->subject,
            'status' => $m->status,
            'preview' => mb_strimwidth((string) $m->message, 0, 140, '…'),
            'read' => $m->read_at !== null,
            'received_at' => $m->created_at?->toIso8601String(),
        ])->all();

        return Present::reply(
            "Found {$page->total()} message(s); showing page {$page->currentPage()} of {$page->lastPage()}.",
            ['pagination' => Present::page($page), 'messages' => $items]
        );
    }

    public function schema(JsonSchema $schema): array
    {
        return [
            'search' => $schema->string()->description('Text to match in name, email, phone or message.'),
            'unread_only' => $schema->boolean()->description('Only messages not yet read.'),
            'page' => $schema->integer()->description('Page number (default 1).'),
            'per_page' => $schema->integer()->description('Items per page (default 25, max 100).'),
        ];
    }
}
