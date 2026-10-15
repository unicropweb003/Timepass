<?php

namespace Database\Seeders;

use App\Models\Setting;
use Illuminate\Database\Seeder;

class SettingsSeeder extends Seeder
{
    public function run(): void
    {
        $shop = require config_path('shop.php');

        if (! Setting::find('contact')) {
            Setting::put('contact', $shop['contact'] ?? []);
        }
        if (! Setting::find('social')) {
            Setting::put('social', $shop['social'] ?? []);
        }
    }
}
