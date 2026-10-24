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
        if (! Schema::hasTable('mobile')) {
            Schema::create('mobile', function (Blueprint $table): void {
                $table->integer('id', autoIncrement: true);
                $table->char('iso', 2);
                $table->string('name', 80);
                $table->string('nicename', 80);
                $table->char('iso3', 3)->nullable();
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
        Schema::drop('mobile');
    }
};
