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
        Schema::create('cloud_pop_up', function (Blueprint $table): void {
            $table->id();
            $table->binary('cloud_top_message');
            $table->binary('cloud_label_field');
            $table->binary('cloud_label_radio');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cloud_pop_up');
    }
};
