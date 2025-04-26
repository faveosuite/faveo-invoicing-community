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
        Schema::create('pipedrive_fields', function (Blueprint $table) {
            $table->id();
            $table->string('field_name');
            $table->string('field_key')->nullable();
            $table->string('field_type')->nullable();
            $table->unsignedBigInteger('local_field_id')->nullable();
            $table->unsignedBigInteger('pipedrive_group_id')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pipedrive_fields');
    }
};
