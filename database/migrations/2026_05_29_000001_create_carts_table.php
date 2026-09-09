<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('carts', function (Blueprint $table): void {
            $table->id();
            // users.id is INT UNSIGNED (increments), so match with unsignedInteger.
            $table->unsignedInteger('user_id');
            $table->string('coupon_code')->nullable();
            $table->decimal('coupon_discount', 10, 2)->default(0);
            $table->string('currency', 10)->default('USD');
            // Links the cart to the pending invoice generated at place-order time
            // so a re-checkout reuses that invoice instead of spawning duplicates.
            // invoices.id is INT UNSIGNED (increments) — match the type.
            $table->unsignedInteger('invoice_id')->nullable();
            $table->timestamps();

            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('invoice_id')->references('id')->on('invoices')->nullOnDelete();
            $table->unique('user_id'); // one active cart per user
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('carts');
    }
};
