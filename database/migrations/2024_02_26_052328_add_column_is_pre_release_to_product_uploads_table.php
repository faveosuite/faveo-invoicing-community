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
        if (! Schema::hasColumn('product_uploads', 'is_pre_release')) {
            Schema::table('product_uploads', function (Blueprint $table) {
                $table->tinyInteger('is_pre_release')->default(0);
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('product_uploads', function (Blueprint $table) {
            $table->dropColumn('is_pre_release');
        });
    }
};
