<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// Indexes for the columns the app filters, sorts and joins on. Postgres does not
// index foreign keys by itself (MySQL does), so lookups such as "this customer's
// orders" or withCount('orders') scanned the whole table.
//
// Safe to re-run: an index is skipped when one with the same name or on exactly the
// same columns already exists. down() drops only the indexes this migration made.
return new class extends Migration
{
    /** table => list of column sets, one index each */
    private const INDEXES = [
        'orders' => [['user_id', 'created_at'], ['status'], ['created_at']],
        'order_items' => [['order_id']],
        'transactions' => [['user_id', 'created_at'], ['order_id'], ['status'], ['created_at']],
        'addresses' => [['user_id']],
        'wishlists' => [['product_id']],
        'contact_messages' => [['user_id'], ['read_at'], ['created_at']],
        'products' => [['category']],
        'users' => [['created_at']],
    ];

    public function up(): void
    {
        foreach (self::INDEXES as $table => $indexes) {
            if (! Schema::hasTable($table)) {
                continue;
            }

            foreach ($indexes as $columns) {
                $name = $this->indexName($table, $columns);
                if (Schema::hasIndex($table, $name) || Schema::hasIndex($table, $columns)) {
                    continue;
                }

                Schema::table($table, fn (Blueprint $t) => $t->index($columns, $name));
            }
        }
    }

    public function down(): void
    {
        foreach (self::INDEXES as $table => $indexes) {
            if (! Schema::hasTable($table)) {
                continue;
            }

            foreach ($indexes as $columns) {
                $name = $this->indexName($table, $columns);
                if (Schema::hasIndex($table, $name)) {
                    Schema::table($table, fn (Blueprint $t) => $t->dropIndex($name));
                }
            }
        }
    }

    private function indexName(string $table, array $columns): string
    {
        return $table.'_'.implode('_', $columns).'_perf_index';
    }
};
