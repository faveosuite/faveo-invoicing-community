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
        if (! Schema::hasTable('seo_default_pages')) {
            Schema::create('seo_default_pages', function (Blueprint $table): void {
                $table->increments('id');
                $table->string('page_key', 32)->unique();
                $table->text('meta_title')->nullable();
                $table->text('meta_description')->nullable();
                $table->text('og_title')->nullable();
                $table->text('og_description')->nullable();
                $table->string('og_image')->nullable();
                $table->boolean('og_same_as_meta')->default(false);
                $table->timestamps();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('seo_default_pages');
    }
};
