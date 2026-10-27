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
        if (! Schema::hasTable('taxes')) {
            Schema::create('taxes', function (Blueprint $table): void {
                $table->increments('id');
                $table->integer('tax_classes_id')->default(1);
                $table->integer('level');
                $table->integer('active');
                $table->string('name');
                $table->string('country');
                $table->string('state');
                $table->string('rate');
                $table->integer('compound');
                $table->timestamps();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::drop('taxes');
    }
};
