<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (! Schema::hasColumn('cloud_order', 'cloud_deleted')) {
            Schema::table('settings', function (Blueprint $table): void {
                $table->string('cloud_order')->nullable();
                $table->string('cloud_deleted')->nullable();
                $table->string('from_name')->nullable();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('settings', function (Blueprint $table): void {
            $table->dropColumn('cloud_order');
            $table->dropColumn('cloud_deleted');
            $table->dropColumn('from_name');
        });
    }
};
