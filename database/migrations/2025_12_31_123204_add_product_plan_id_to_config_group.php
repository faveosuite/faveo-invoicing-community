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
        Schema::table('config_group', function (Blueprint $table) {
            $table->foreignId('plan_id')->nullable();
            $table->foreignId('product_id')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('config_group', function (Blueprint $table) {
            $table->DropColumn('plan_id');
            $table->DropColumn('product_id');
        });
    }
};
