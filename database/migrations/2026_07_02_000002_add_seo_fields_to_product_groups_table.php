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
        if (! Schema::hasColumn('product_groups', 'meta_title')) {
            Schema::table('product_groups', function (Blueprint $table): void {
                $table->text('meta_title')->nullable()->after('cart_link');
                $table->text('meta_description')->nullable()->after('meta_title');
                $table->text('og_title')->nullable()->after('meta_description');
                $table->text('og_description')->nullable()->after('og_title');
                $table->string('og_image')->nullable()->after('og_description');
                $table->boolean('og_same_as_meta')->default(false)->after('og_image');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('product_groups', function (Blueprint $table): void {
            $table->dropColumn(['meta_title', 'meta_description', 'og_title', 'og_description', 'og_image', 'og_same_as_meta']);
        });
    }
};
