<?php

namespace App\Mcp\Tools\Contact;

use App\Mcp\Support\Present;
use App\Mcp\Support\SiteContent;
use App\Models\Setting;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\Server\Attributes\Description;
use Laravel\Mcp\Server\Attributes\Name;
use Laravel\Mcp\Server\Tool;

#[Name('update_contact_info')]
#[Description('Update the store contact details. Only the fields you pass change. "social" is merged: pass e.g. {"instagram":"https://instagram.com/mystore"}; set a network to "" to remove it.')]
class UpdateContactInfo extends Tool
{
    public function handle(Request $request): Response
    {
        $data = $request->validate([
            'address' => 'sometimes|nullable|string|max:500',
            'email' => 'sometimes|nullable|email|max:200',
            'phone' => 'sometimes|nullable|string|max:50',
            'hours' => 'sometimes|nullable|string|max:200',
            'map_link' => 'sometimes|nullable|url|max:2000',
            'whatsapp' => 'sometimes|nullable|string|max:50',
            'social' => 'sometimes|array',
            'social.*' => 'nullable|string|max:500',
        ], [
            'email.email' => 'email must be a valid email address, e.g. support@mystore.com.',
            'map_link.url' => 'map_link must be a full link, e.g. https://maps.google.com/?q=...',
        ]);

        $contact = (array) (Setting::get('contact', []) ?: []);
        $changed = [];
        foreach (SiteContent::CONTACT_FIELDS as $field) {
            if (array_key_exists($field, $data)) {
                $contact[$field] = $data[$field];
                $changed[] = $field;
            }
        }
        if ($changed) {
            Setting::put('contact', $contact);
        }

        if (array_key_exists('social', $data)) {
            $social = (array) (Setting::get('social', []) ?: []);
            foreach ($data['social'] as $network => $link) {
                $key = strtolower(trim((string) $network));
                // Store "" (not unset) so it overrides the config default; the site hides empty links.
                $social[$key] = (string) ($link ?? '');
                $changed[] = "social.{$key}";
            }
            Setting::put('social', $social);
        }

        if (! $changed) {
            return Response::error('Nothing to update. Pass at least one of: address, email, phone, hours, map_link, whatsapp, social.');
        }

        return Present::reply('Updated contact info: '.implode(', ', $changed).'.', SiteContent::contact());
    }

    public function schema(JsonSchema $schema): array
    {
        return [
            'address' => $schema->string()->description('Store address.'),
            'email' => $schema->string()->description('Support email.'),
            'phone' => $schema->string()->description('Phone number, e.g. "+91 90000 00000".'),
            'hours' => $schema->string()->description('Opening hours, e.g. "Mon - Sat, 10 AM - 7 PM".'),
            'map_link' => $schema->string()->description('Google Maps (or other) link to the store location.'),
            'whatsapp' => $schema->string()->description('WhatsApp number.'),
            'social' => $schema->object()->description('Social links to set, e.g. {"instagram":"https://...","facebook":"https://..."}. Use "" to remove one.'),
        ];
    }
}
