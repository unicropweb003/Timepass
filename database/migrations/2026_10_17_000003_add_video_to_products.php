<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * A short product video (Cloudinary URL or a direct https link to an
     * MP4/WebM/MOV file), shown in "Watch & shop" on the home page.
     */
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->text('video')->nullable()->after('image');
        });
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn('video');
        });
    }
};
