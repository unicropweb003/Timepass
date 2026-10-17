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
            URL::forceRootUrl(config('app.url'));
        }
    }
}
