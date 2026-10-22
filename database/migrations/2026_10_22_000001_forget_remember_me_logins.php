<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Logins no longer use "remember me". Clearing the stored tokens makes the
     * remember cookies already in browsers stop working, so those visitors are
     * signed out by the idle timeout too. Active sessions are not affected.
     */
    public function up(): void
    {
        DB::table('users')->whereNotNull('remember_token')->update(['remember_token' => null]);
    }

    public function down(): void
    {
        // The cleared tokens cannot be restored.
    }
};
