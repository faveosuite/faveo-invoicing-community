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
        if (! Schema::hasTable('widgets')) {
            Schema::create('widgets', function (Blueprint $table): void {
                $table->increments('id');
                $table->string('name');
                $table->string('type');
                $table->integer('publish');
                $table->text('content');
                $table->timestamps();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::drop('widgets');
    }
};
