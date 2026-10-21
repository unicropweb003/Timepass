<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->string('payment_status', 30)->nullable()->after('payment_method');
            $table->string('gateway_order_id', 50)->nullable()->unique()->after('payment_status');
            $table->timestamp('paid_at')->nullable()->after('gateway_order_id');
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropUnique(['gateway_order_id']);
            $table->dropColumn(['payment_status', 'gateway_order_id', 'paid_at']);
        });
    }
};
