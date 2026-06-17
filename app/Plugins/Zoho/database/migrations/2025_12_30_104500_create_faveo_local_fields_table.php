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
        Schema::create('faveo_local_fields', function (Blueprint $table): void {
            $table->id();

            // Stable field identifier (email, subject, custom_12)
            $table->string('field_key');

            // Display label
            $table->string('display_name');

            // Field behavior
            $table->string('field_type');

            $table->boolean('is_active')->default(value: true);

            $table->timestamps();

            $table->unique('field_key', 'faveo_local_field_key_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('faveo_local_fields');
    }
};
