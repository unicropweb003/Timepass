<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// Turns contact-form messages into leads the team can follow up: phone and
// subject from the form, a follow-up status, and who sent it (customer account
// when logged in, IP). Additive only, so existing messages are kept.
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('contact_messages', function (Blueprint $table) {
            $table->string('phone', 30)->nullable()->after('email');
            $table->string('subject', 100)->nullable()->after('phone');
            $table->string('status', 20)->default('new')->index()->after('message');
            $table->foreignId('user_id')->nullable()->after('status')->constrained()->nullOnDelete();
            $table->string('ip_address', 45)->nullable()->after('user_id');
        });
    }

    public function down(): void
    {
        Schema::table('contact_messages', function (Blueprint $table) {
            $table->dropConstrainedForeignId('user_id');
            $table->dropIndex(['status']);
            $table->dropColumn(['phone', 'subject', 'status', 'ip_address']);
        });
    }
};
