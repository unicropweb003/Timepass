<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Images stored in the database (base64) so they survive redeploys on
     * hosts with an ephemeral filesystem (e.g. Render). Served at /media/{id}.
     */
    public function up(): void
    {
        Schema::create('media', function (Blueprint $table) {
            $table->id();
            $table->string('mime', 100);
            $table->unsignedInteger('size');
            $table->longText('data'); // base64-encoded file contents
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('media');
    }
};
