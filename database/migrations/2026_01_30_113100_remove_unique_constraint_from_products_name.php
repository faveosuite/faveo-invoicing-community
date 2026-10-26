<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Remove the unique constraint on product name to allow duplicate names
     * across different product groups. Optionally adds a composite unique
     * index on (name, group) to enforce uniqueness within the same group.
     */
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table): void {
            $table->dropUnique('name');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('products', function (Blueprint $table): void {
            $table->unique('name', 'name');
        });
    }
};
