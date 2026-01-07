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
        Schema::create('zoho_field_mappings', function (Blueprint $table) {
            $table->id();

            // Zoho field reference
            $table->foreignId('zoho_field_id')
                ->constrained('zoho_fields')
                ->cascadeOnDelete();

            // Local field reference
            $table->foreignId('faveo_local_field_id')
                ->nullable()
                ->constrained('faveo_local_fields')
                ->nullOnDelete();

            // Mapping control
            $table->boolean('is_active')->default(true);

            // Default handling
            $table->json('default_value')->nullable();
            $table->boolean('use_default_if_empty')->default(false);


            $table->json('option_mapping')->nullable();

            $table->json('selected_option')->nullable();

            $table->timestamps();

            // Prevent duplicate mappings
            $table->unique(['zoho_field_id'], 'zoho_field_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('zoho_field_mappings');
    }
};
