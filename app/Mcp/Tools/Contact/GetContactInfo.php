<?php

namespace App\Mcp\Tools\Contact;

use App\Mcp\Support\Present;
use App\Mcp\Support\SiteContent;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\Server\Attributes\Description;
use Laravel\Mcp\Server\Attributes\Name;
use Laravel\Mcp\Server\Tool;
use Laravel\Mcp\Server\Tools\Annotations\IsReadOnly;

#[Name('get_contact_info')]
#[Description('Get the store contact details shown on the website: address, email, phone, opening hours, map link, WhatsApp and social links.')]
#[IsReadOnly]
class GetContactInfo extends Tool
{
    public function handle(Request $request): Response
    {
        return Present::reply('Current contact information.', SiteContent::contact());
    }

    public function schema(JsonSchema $schema): array
    {
        return [];
    }
}
