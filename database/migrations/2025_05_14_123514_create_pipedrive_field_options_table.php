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
        Schema::create('pipedrive_field_options', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('pipedrive_field_id');
            $table->string('key')->nullable();
            $table->string('value')->nullable();
            $table->boolean('status')->default(0);
            $table->timestamps();

            $table->foreign('pipedrive_field_id')
                ->references('id')->on('pipedrive_fields')
                ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pipedrive_field_options');
    }
};
