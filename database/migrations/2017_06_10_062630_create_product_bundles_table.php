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
        if (! Schema::hasTable('product_bundles')) {
            Schema::create('product_bundles', function (Blueprint $table): void {
                $table->increments('id');
                $table->string('name');
                $table->timestamp('valid_from');
                $table->dateTime('valid_till');
                $table->integer('uses');
                $table->integer('maximum_uses');
                $table->integer('allow-promotion');
                $table->integer('show');
                $table->timestamps();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::drop('product_bundles');
    }
};
