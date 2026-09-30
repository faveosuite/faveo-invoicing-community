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
        if (! Schema::hasTable('countries')) {
            Schema::create('countries', function (Blueprint $table): void {
                $table->integer('country_id', autoIncrement: true);
                $table->char('country_code_char2', 2);
                $table->string('country_name', 80);
                $table->string('nicename', 80);
                $table->char('country_code_char3', 3)->nullable();
                $table->smallInteger('numcode')->nullable();
                $table->integer('phonecode');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::drop('countries');
    }
};
