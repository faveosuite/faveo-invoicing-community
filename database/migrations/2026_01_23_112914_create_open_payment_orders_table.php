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
        if (! Schema::hasTable('open_payment_orders')) {
            Schema::create('open_payment_orders', function (Blueprint $table): void {
                $table->id();
                $table->string('name', 100);
                $table->string('email');
                $table->string('mobile', 20);
                $table->text('address');
                $table->string('city');
                $table->string('state');
                $table->string('zip', 15);
                $table->string('country');
                $table->string('company');
                $table->decimal('amount', 10, 2);         // total charged to gateway (base + fee)
                $table->decimal('base_amount', 10, 2);    // user-entered amount
                $table->decimal('processing_fee', 10, 2)->default(0);
                $table->decimal('processing_fee_rate', 5, 2)->default(0);
                $table->string('currency', 3);
                $table->string('gateway');
                $table->text('description')->nullable();
                $table->string('transaction_id')->nullable()->unique();
                $table->string('gateway_transaction_id')->nullable()->index();
                $table->string('payment_status')->default('pending');
                $table->timestamp('paid_at')->nullable();
                $table->timestamps();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('open_payment_orders');
    }
};
