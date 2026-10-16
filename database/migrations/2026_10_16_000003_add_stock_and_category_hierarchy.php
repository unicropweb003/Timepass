<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Fields needed by the MCP content tools:
     *  - products.stock        (units in stock; null = not tracked)
     *  - categories.parent_id  (optional parent category)
     *  - categories.image      (category picture, e.g. media/12)
     *  - media.upload_token / expires_at (short-lived ids from POST /upload)
     */
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->unsignedInteger('stock')->nullable()->after('mrp');
        });

        Schema::table('categories', function (Blueprint $table) {
            // No DB-level foreign key (keeps SQLite/Postgres migrations simple);
            // the MCP tools keep it consistent and null it when a parent is deleted.
            $table->unsignedBigInteger('parent_id')->nullable()->index()->after('id');
            $table->text('image')->nullable()->after('icon');
        });

        Schema::table('media', function (Blueprint $table) {
            $table->string('upload_token', 64)->nullable()->unique()->after('id');
            $table->timestamp('expires_at')->nullable()->after('data');
        });
    }

    public function down(): void
    {
        Schema::table('media', function (Blueprint $table) {
            $table->dropUnique(['upload_token']);
            $table->dropColumn(['upload_token', 'expires_at']);
        });

        Schema::table('categories', function (Blueprint $table) {
            $table->dropIndex(['parent_id']);
            $table->dropColumn(['parent_id', 'image']);
        });

        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn('stock');
        });
    }
};
