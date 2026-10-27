<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        foreach (['ends_at', 'update_ends_at', 'support_ends_at'] as $column) {
            DB::table('subscriptions')
                ->where($column, '0000-00-00 00:00:00')
                ->update([$column => null]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Zero-date values are bad data; nothing to restore.
    }
};
