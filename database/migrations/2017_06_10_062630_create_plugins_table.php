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
        if (! Schema::hasTable('plugins')) {
            Schema::create('plugins', function (Blueprint $table): void {
                $table->increments('id');
                $table->string('name');
                $table->string('path');
                $table->integer('status');
                $table->string('type');
                $table->timestamps();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::drop('plugins');
    }
};
