<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('common_settings', function (Blueprint $table): void {
            $table->increments('id');
            $table->string('option_name');
            $table->string('optional_field');
            $table->string('option_value');
            $table->string('status');
            $table->timestamps();
            $table->unique(['option_name', 'optional_field']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::drop('common_settings');
    }
};
