<?php

namespace App\Providers;

use App\Services\CloudinaryUploader;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // Built explicitly so the Cloudinary client is only created when an upload
        // or delete actually happens (and never fails just because a page needs it).
        $this->app->singleton(CloudinaryUploader::class, fn () => new CloudinaryUploader);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        if (config('app.env') === 'production') {
            URL::forceScheme('https');
        }

        if (config('app.force_url')) {
            // Host and scheme both come from APP_URL: forceRootUrl() alone keeps the
            // incoming request's scheme, so a request reaching an origin over plain
            // http (e.g. from the load balancer) would otherwise get http:// links.
            $root = rtrim((string) config('app.url'), '/');
            URL::forceRootUrl($root);
            if ($scheme = parse_url($root, PHP_URL_SCHEME)) {
                URL::forceScheme($scheme);
            }
        }
    }
}
