<?php

namespace App\Console\Commands;

use App\Models\Category;
use App\Models\Media;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\Setting;
use App\Services\CloudinaryUploader;
use Illuminate\Console\Command;

/**
 * Moves images that still live in the media table (paths like "media/12") to
 * Cloudinary and points the records that use them at the new https URL.
 *
 * The media rows are kept, so old /media/{id} links keep working. Safe to run
 * again: records that already have an https URL are not touched. It only runs
 * when started by hand (never on deploy).
 */
class MoveMediaToCloudinary extends Command
{
    protected $signature = 'media:to-cloudinary
        {--dry-run : Only list what would be moved; upload and change nothing}
        {--limit=0 : Move at most this many images (0 = all)}';

    protected $description = 'Move images from the media table to Cloudinary and update the records that use them';

    /** Cloudinary folder (under CLOUDINARY_FOLDER) per kind of record. */
    private const FOLDERS = ['product' => 'products', 'category' => 'categories', 'about' => 'about', 'order item' => 'products'];

    public function handle(CloudinaryUploader $uploader): int
    {
        $dryRun = (bool) $this->option('dry-run');

        if (! $dryRun && ! $uploader->isConfigured()) {
            $this->error('Cloudinary is not configured: set CLOUDINARY_URL first.');

            return self::FAILURE;
        }

        $references = $this->references();
        if ($references === []) {
            $this->info('Nothing to move: no record uses a media/{id} image.');

            return self::SUCCESS;
        }

        ksort($references);
        $ids = array_keys($references);
        if (($limit = max(0, (int) $this->option('limit'))) > 0) {
            $ids = array_slice($ids, 0, $limit);
        }

        $rows = [];
        $moved = $failed = 0;

        foreach ($ids as $id) {
            $users = $references[$id];
            $label = implode(', ', array_column($users, 'label'));
            $media = Media::find($id);

            if (! $media) {
                $rows[] = [$id, $label, 'skipped: the media row no longer exists'];
                $failed++;

                continue;
            }

            if ($dryRun) {
                $rows[] = [$id, $label, 'would move ('.round($media->size / 1024).' KB)'];

                continue;
            }

            try {
                $url = $uploader->uploadBytes((string) base64_decode($media->data), $media->mime, self::FOLDERS[$users[0]['kind']]);
            } catch (\Throwable $e) {
                $rows[] = [$id, $label, 'FAILED: '.CloudinaryUploader::safeMessage($e)];
                $failed++;

                continue;
            }

            foreach ($users as $user) {
                ($user['update'])($url);
            }
            $rows[] = [$id, $label, 'moved'];
            $moved++;
        }

        $this->table(['media id', 'used by', 'result'], $rows);

        if ($dryRun) {
            $this->info(count($ids).' image(s) would be moved to Cloudinary. Nothing was changed (dry run).');
        } else {
            $this->info("Moved {$moved} image(s) to Cloudinary".($failed ? ", {$failed} not moved" : '').'.');
            $this->line('The media rows were kept, so old /media/{id} links still work.');
        }

        if (count($ids) < count($references)) {
            $this->line((count($references) - count($ids)).' more image(s) left; run the command again to continue.');
        }

        return $failed ? self::FAILURE : self::SUCCESS;
    }

    /**
     * Every record whose image is a media/{id} path, grouped by media id.
     *
     * @return array<int, list<array{kind: string, label: string, update: callable}>>
     */
    private function references(): array
    {
        $refs = [];
        $add = function (?string $path, string $kind, string $label, callable $update) use (&$refs) {
            if ($path !== null && preg_match('#^/?media/(\d+)$#', $path, $m)) {
                $refs[(int) $m[1]][] = ['kind' => $kind, 'label' => $label, 'update' => $update];
            }
        };

        $models = [
            'product' => Product::query(),
            'category' => Category::query(),
            'order item' => OrderItem::query(),
        ];
        foreach ($models as $kind => $query) {
            $records = $query->where(fn ($q) => $q->where('image', 'like', 'media/%')->orWhere('image', 'like', '/media/%'))->get();

            foreach ($records as $record) {
                $name = $record->name ?? $record->product_name ?? '';
                $add($record->image, $kind, trim("{$kind} #{$record->id} {$name}"), fn (string $url) => $record->update(['image' => $url]));
            }
        }

        $about = (array) Setting::get('about', []);
        $add($about['image'] ?? null, 'about', 'About Us page', function (string $url) {
            $about = (array) Setting::get('about', []);
            $about['image'] = $url;
            Setting::put('about', $about);
        });

        return $refs;
    }
}
