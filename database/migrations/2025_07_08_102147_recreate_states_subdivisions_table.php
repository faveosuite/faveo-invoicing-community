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
        Schema::dropIfExists('states_subdivisions');

        Schema::create('states_subdivisions', function (Blueprint $table) {
            $table->mediumIncrements('state_subdivision_id');
            $table->string('state_subdivision_name', 255);
            $table->char('country_code', 2);
            $table->string('iso2', 255)->nullable();
            $table->string('primary_level_name', 191)->nullable();
            $table->decimal('latitude', 10, 8)->nullable();
            $table->decimal('longitude', 11, 8)->nullable();
            $table->unsignedMediumInteger('country_id');
            $table->foreign('country_id')->references('country_id')->on('countries')->onDelete('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('states');
    }
};
