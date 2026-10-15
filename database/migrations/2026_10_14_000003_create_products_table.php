<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('category');                 // category slug
            $table->string('type')->nullable();         // type slug within category
            $table->string('form')->nullable();         // form slug (config/shop.php)
            $table->unsignedInteger('price');           // rupees
            $table->unsignedInteger('mrp')->nullable(); // original price (struck through)
            $table->text('description')->nullable();
            $table->decimal('rating', 2, 1)->default(0);
            $table->unsignedInteger('reviews')->default(0);
            $table->string('image')->nullable();        // path under public/
            $table->json('tags')->nullable();           // ["best-seller","recommended"]
            $table->boolean('is_active')->default(true);
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
