<?php

declare(strict_types=1);

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
        // The spendable number, one row per user+currency. Locked (SELECT ... FOR
        // UPDATE) by CreditBalanceService on every grant/apply so concurrent
        // requests can't double-spend the same balance.
        Schema::create('user_credit_balances', function (Blueprint $table): void {
            $table->increments('id');
            $table->integer('user_id');
            $table->string('currency', 10);
            $table->string('balance')->default('0');
            $table->timestamps();

            $table->unique(['user_id', 'currency']);
        });

        // The append-only history behind that number — never edited or deleted.
        // amount is signed: positive for a deposit (overpayment, manual grant,
        // downgrade proration), negative for a spend (applied_to_invoice).
        Schema::create('credit_transactions', function (Blueprint $table): void {
            $table->increments('id');
            $table->integer('user_id');
            $table->string('currency', 10);
            $table->string('amount');
            $table->string('type', 40);
            $table->integer('payment_id')->nullable();
            $table->integer('invoice_id')->nullable();
            $table->string('note')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'currency']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('credit_transactions');
        Schema::dropIfExists('user_credit_balances');
    }
};
