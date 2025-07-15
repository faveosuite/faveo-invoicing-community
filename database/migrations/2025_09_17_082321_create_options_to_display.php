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
        Schema::create('options_to_display', function (Blueprint $table) {
            $table->id();
            $table->integer('product_id');
            $table->string('display_option');
            $table->string('option_description');
            $table->string('option_type');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('options_to_display');
    }
};
