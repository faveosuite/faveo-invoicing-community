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
        Schema::create('terminated_order_upgrade', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('terminated_order_id');
            $table->unsignedBigInteger('upgraded_order_id');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('terminated_order_upgrade');
    }
};
