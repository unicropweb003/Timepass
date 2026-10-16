<?php

namespace App\Mcp\Tools\Settings;

use App\Models\Setting;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\Server\Attributes\Description;
use Laravel\Mcp\Server\Attributes\Name;
use Laravel\Mcp\Server\Tool;

#[Name('update_settings')]
#[Description('Update the storefront contact details ("contact") or social links ("social"). Pass the fields you want to change in "values"; existing fields you omit are kept (merged, not replaced). Call get_settings first to see current values and field names.')]
class UpdateSettings extends Tool
{
    public function handle(Request $request): Response
    {
        $data = $request->validate([
            'group' => 'required|in:contact,social',
            'values' => 'required|array',
        ], [
            'group.in' => 'group must be either "contact" or "social".',
            'values.required' => 'Provide a "values" object with the fields to update, e.g. {"phone":"...","email":"..."}.',
        ]);

        $existing = Setting::get($data['group'], []);
        if (! is_array($existing)) {
            $existing = [];
        }

        $merged = array_merge($existing, $data['values']);
        Setting::put($data['group'], $merged);

        return Response::text("Updated \"{$data['group']}\" settings. Current fields: ".implode(', ', array_keys($merged)).'.');
    }

    public function schema(JsonSchema $schema): array
    {
        return [
            'group' => $schema->string()->enum(['contact', 'social'])->description('Which settings group to update: "contact" or "social".')->required(),
            'values' => $schema->object()->description('Object of fields to set, e.g. {"phone":"+91...","email":"hi@site.com"} for contact, or {"facebook":"https://...","instagram":"https://..."} for social. Omitted fields are kept.')->required(),
        ];
    }
}
