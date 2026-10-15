<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->boolean('is_admin')->default(false)->after('password');
            $table->boolean('is_blocked')->default(false)->after('is_admin');
            $table->string('google_id')->nullable()->unique()->after('is_blocked');
            $table->string('avatar')->nullable()->after('google_id');
            // Google-only admins may not have a password; social customers may not have a phone.
            $table->string('password')->nullable()->change();
            $table->string('phone', 20)->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['is_admin', 'is_blocked', 'google_id', 'avatar']);
        });
    }
};
