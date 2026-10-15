<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    public function run(): void
    {
        // The primary administrator. Google sign-in works for this email
        // once Google credentials are configured in .env.
        User::updateOrCreate(
            ['email' => 'unicropweb003@gmail.com'],
            [
                'name' => 'Administrator',
                'phone' => '9999999999',
                'password' => Hash::make('Admin@12345'),
                'is_admin' => true,
                'is_blocked' => false,
            ]
        );
    }
}
