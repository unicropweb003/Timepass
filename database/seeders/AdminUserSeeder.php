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
        //
        // This runs on every deploy: it creates the account on a fresh
        // database, and afterwards only keeps it an unblocked admin. It never
        // resets the password or profile, so a changed password sticks.
        $user = User::firstOrNew(['email' => 'unicropweb003@gmail.com']);

        if (! $user->exists) {
            $user->name = 'Administrator';
            $user->password = Hash::make('Admin@12345');
            if (! User::where('phone', '9999999999')->exists()) {
                $user->phone = '9999999999';
            }
        }

        $user->is_admin = true;
        $user->is_blocked = false;
        $user->save();
    }
}
