<?php

namespace App\Mcp\Tools\About;

use App\Mcp\Support\ImageInput;
use App\Mcp\Support\ImagePipeline;
use App\Mcp\Support\Present;
use App\Mcp\Support\SiteContent;
use App\Models\Setting;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\Server\Attributes\Description;
use Laravel\Mcp\Server\Attributes\Name;
use Laravel\Mcp\Server\Tool;

#[Name('update_about_us')]
#[Description('Update the About Us page: title, content and/or image. Only passed fields change. For the image pass image_source "ai" (generate), image_url, image_upload_id, or "none" to remove it; the old stored image is deleted.')]
class UpdateAboutUs extends Tool
{
    public function handle(Request $request): Response
    {
        $data = $request->validate([
            'title' => 'sometimes|string|max:200',
            'content' => 'sometimes|string|max:20000',
        ] + ImageInput::rules());

        $about = SiteContent::about();
        $notes = [];

        foreach (['title', 'content'] as $field) {
            if (array_key_exists($field, $data)) {
                $about[$field] = $data[$field];
            }
        }

        $image = ImagePipeline::resolve($data, 'keep', $about['title'].'. '.$about['content'], 'about');
        if (in_array($image['action'], ['set', 'remove'], true)) {
            ImagePipeline::deleteStored($about['image']);
            $about['image'] = $image['path'];
        }
        if ($image['note']) {
            $notes[] = $image['note'];
        }

        Setting::put('about', $about);

        return Present::reply('Updated the About Us page.'.($notes ? ' '.implode(' ', $notes) : ''), SiteContent::presentAbout());
    }

    public function schema(JsonSchema $schema): array
    {
        return [
            'title' => $schema->string()->description('Main heading of the About Us page.'),
            'content' => $schema->string()->description('Intro text / story shown under the heading.'),
        ] + ImageInput::schema($schema, false);
    }
}
