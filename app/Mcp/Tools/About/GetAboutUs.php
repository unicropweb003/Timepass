<?php

namespace App\Mcp\Tools\About;

use App\Mcp\Support\Present;
use App\Mcp\Support\SiteContent;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\Server\Attributes\Description;
use Laravel\Mcp\Server\Attributes\Name;
use Laravel\Mcp\Server\Tool;
use Laravel\Mcp\Server\Tools\Annotations\IsReadOnly;

#[Name('get_about_us')]
#[Description('Get the About Us page content (title, content, image) currently shown on the website.')]
#[IsReadOnly]
class GetAboutUs extends Tool
{
    public function handle(Request $request): Response
    {
        return Present::reply('Current About Us content.', SiteContent::presentAbout());
    }

    public function schema(JsonSchema $schema): array
    {
        return [];
    }
}
