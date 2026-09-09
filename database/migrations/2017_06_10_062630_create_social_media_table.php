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
        if (! Schema::hasTable('social_media')) {
            Schema::create('social_media', function (Blueprint $table): void {
                $table->increments('id');
                $table->string('class');
                $table->string('fa_class', 225);
                $table->string('name');
                $table->string('link');
                $table->timestamps();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::drop('social_media');
    }
};
