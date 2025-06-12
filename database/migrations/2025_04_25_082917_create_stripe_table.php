<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if(!Schema::hasTable('stripe')) {
            Schema::create('stripe', function ($table) {
                $table->increments('id');
                $table->string('image_url');
                $table->string('processing_fee');
                $table->string('base_currency');
                $table->string('currencies');
                $table->timestamps();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('stripe');
    }
};
