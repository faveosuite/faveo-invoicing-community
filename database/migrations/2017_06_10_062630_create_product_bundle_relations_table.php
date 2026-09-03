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
        if (! Schema::hasTable('product_bundle_relations')) {
            Schema::create('product_bundle_relations', function (Blueprint $table): void {
                $table->increments('id');
                $table->integer('product_id')->unsigned()->index('product_bundle_relations_product_id_foreign');
                $table->integer('bundle_id')->unsigned()->index('product_bundle_relations_bundle_id_foreign');
                $table->timestamps();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::drop('product_bundle_relations');
    }
};
