<?php

namespace App\Mcp\Tools\Settings;

use App\Models\Setting;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\Server\Attributes\Description;
use Laravel\Mcp\Server\Attributes\Name;
use Laravel\Mcp\Server\Tool;
use Laravel\Mcp\Server\Tools\Annotations\IsReadOnly;

#[Name('get_settings')]
#[Description('Get the storefront contact details and social links currently shown on the site.')]
#[IsReadOnly]
class GetSettings extends Tool
{
    public function handle(Request $request): Response
    {
        return Response::structured([
            'contact' => Setting::get('contact', []),
            'social' => Setting::get('social', []),
        ]);
    }

    public function schema(JsonSchema $schema): array
    {
        return [];
    }
}
