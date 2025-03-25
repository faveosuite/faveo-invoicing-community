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
        Schema::create('pipedrive_local_fields', function (Blueprint $table) {
            $table->id();
            $table->string('field_name'); // Display name
            $table->string('field_key')->nullable(); // API field key
            $table->string('pipedrive_key')->nullable(); // Unique Pipedrive field ID
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pipedrive_local_fields');
    }
};
