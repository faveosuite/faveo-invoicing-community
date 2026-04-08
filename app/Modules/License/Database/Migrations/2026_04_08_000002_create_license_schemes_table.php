<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * License schemes table (from afl_license_schemes).
     * SQL schemas for client-side license storage.
     */
    public function up(): void
    {
        Schema::create('license_schemes', function (Blueprint $table) {
            $table->id();
            $table->text('scheme_query');
            $table->string('scheme_status')->default('active');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('license_schemes');
    }
};
