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
        if (! Schema::hasTable('configurable_options')) {
            Schema::create('configurable_options', function (Blueprint $table): void {
                $table->increments('id');
                $table->integer('group_id')->unsigned()->index('configurable_options_group_id_foreign');
                $table->integer('type');
                $table->string('title');
                $table->string('options');
                $table->integer('price');
                $table->timestamps();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::drop('configurable_options');
    }
};
