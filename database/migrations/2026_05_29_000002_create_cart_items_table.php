<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cart_items', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('cart_id');
            // products.id / plans.id are INT UNSIGNED (increments).
            $table->unsignedInteger('product_id');
            $table->unsignedInteger('plan_id')->nullable();
            $table->unsignedInteger('quantity')->default(1);
            $table->unsignedInteger('agents')->default(1);
            $table->string('domain')->nullable();
            $table->unsignedBigInteger('data_center_id')->nullable();
            $table->string('billing_cycle')->default('monthly'); // monthly|yearly|onetime
            $table->timestamps();

            $table->foreign('cart_id')->references('id')->on('carts')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cart_items');
    }
};
