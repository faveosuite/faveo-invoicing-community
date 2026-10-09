<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * License options table (from license_options).
     * Key-value options for licenses/products.
     */
    public function up(): void
    {
        Schema::create('license_options', function (Blueprint $table): void {
            $table->id();
            $table->string('option_key')->index();
            $table->text('option_value')->nullable();
            $table->string('option_group')->nullable();
            $table->timestamps();
            $table->unique(['option_key', 'option_group']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('license_options');
    }
};
