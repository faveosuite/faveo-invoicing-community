<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Separate "money arrived" from "what it paid for".
 *
 * A payment is one real-world event — an amount left the client's bank on a
 * date by a method. Which invoices it settles is a different fact, and it is
 * many-to-many: one payment can cover several invoices, and one invoice can
 * be settled by several payments. `payments.invoice_id` could only express
 * the second half, so the first was faked by cloning the payment row per
 * invoice — three rows that all look like money for one real receipt.
 *
 * This is Invoice Ninja's `paymentables` in its simplest useful form: one
 * payments row per event, one payment_invoice row per allocation. An
 * invoice's paid total is the sum of its allocations; a payment's unapplied
 * remainder is its amount less the allocations made from it.
 *
 * `payments.invoice_id` and `payments.parent_id` are left in place holding
 * their old values, but nothing reads them any more — the pivot is the only
 * authority on what a payment paid for.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payment_invoice', function (Blueprint $table): void {
            $table->increments('id');
            $table->integer('payment_id');
            $table->integer('invoice_id');
            $table->decimal('amount', 20, 4)->default(0);
            $table->timestamps();

            $table->index('payment_id');
            $table->index('invoice_id');
        });

        // Every payment that already names an invoice becomes one allocation of
        // its full amount. Rows created by the interim parent/child scheme are
        // allocations of their PARENT's money, so they fold into the parent and
        // the duplicate row goes away.
        DB::statement('
            INSERT INTO payment_invoice (payment_id, invoice_id, amount, created_at, updated_at)
            SELECT id, invoice_id, CAST(COALESCE(amount, 0) AS DECIMAL(20,4)), created_at, updated_at
            FROM payments
            WHERE invoice_id > 0 AND COALESCE(parent_id, 0) = 0
        ');

        DB::statement('
            INSERT INTO payment_invoice (payment_id, invoice_id, amount, created_at, updated_at)
            SELECT parent_id, invoice_id, CAST(COALESCE(amount, 0) AS DECIMAL(20,4)), created_at, updated_at
            FROM payments
            WHERE invoice_id > 0 AND COALESCE(parent_id, 0) > 0
        ');

        DB::table('payments')->where('invoice_id', '>', 0)->where('parent_id', '>', 0)->delete();

        $this->reclassifyCreditAsUnappliedPayment();
    }

    /**
     * An overpayment was never credit — it is the client's own money waiting to
     * be allocated. It was booked into the credit ledger by a backfill that was
     * never released; this puts it back where it belongs, as payments with an
     * unapplied remainder. Genuine credit (a downgrade proration, a goodwill
     * adjustment) is left alone — only overpayment rows are moved.
     */
    private function reclassifyCreditAsUnappliedPayment(): void
    {
        if (! Schema::hasTable('user_credit_balances') || ! Schema::hasTable('credit_transactions')) {
            return;
        }

        // Anything other than an overpayment is real credit, and untangling it
        // from spending is guesswork. Leave the whole ledger alone in that case.
        if (DB::table('credit_transactions')->whereNotIn('type', ['overpayment', 'applied_to_invoice'])->exists()) {
            return;
        }

        foreach (DB::table('user_credit_balances')->orderBy('user_id')->get() as $row) {
            $balance = round((float) $row->balance, 2);

            // Pre-ledger deposits kept their value in amt_to_credit and left
            // amount blank, so they read as zero-value payments. Move it across.
            DB::table('payments')
                ->where('user_id', $row->user_id)
                ->where('currency', $row->currency)
                ->where('invoice_id', 0)
                ->whereRaw('CAST(COALESCE(amount, 0) AS DECIMAL(20,4)) = 0')
                ->whereRaw('CAST(COALESCE(amt_to_credit, 0) AS DECIMAL(20,4)) > 0')
                ->update(['amount' => DB::raw('amt_to_credit')]);

            $shortfall = round($balance - $this->unappliedPool((int) $row->user_id, (string) $row->currency), 2);

            if ($shortfall < 0) {
                // More unapplied than they can actually spend, because some of
                // the credit was spent through the ledger. Restate it as one
                // accurate payment rather than leave the pool overstated.
                DB::table('payments')
                    ->where('user_id', $row->user_id)->where('currency', $row->currency)
                    ->where('invoice_id', 0)->update(['amount' => 0]);
                $shortfall = $balance;
            }

            if ($shortfall > 0) {
                DB::table('payments')->insert([
                    'invoice_id' => 0, 'parent_id' => 0, 'user_id' => $row->user_id,
                    'amount' => $shortfall, 'amt_to_credit' => 0,
                    'payment_method' => 'Reclassified credit', 'payment_status' => 'success',
                    'currency' => $row->currency, 'created_at' => now(), 'updated_at' => now(),
                ]);
            }
        }

        DB::table('credit_transactions')->delete();
        DB::table('user_credit_balances')->delete();
        DB::table('migrations')->where('migration', '2026_08_20_000002_migrate_legacy_credit_payments_to_ledger')->delete();
    }

    /** What the client has paid that no invoice has claimed yet. */
    private function unappliedPool(int $userId, string $currency): float
    {
        $perPayment = DB::table('payments as p')
            ->leftJoin('payment_invoice as a', 'a.payment_id', '=', 'p.id')
            ->where('p.user_id', $userId)
            ->where('p.currency', $currency)
            ->where('p.payment_status', 'success')
            ->groupBy('p.id')
            ->select(DB::raw('CAST(COALESCE(p.amount,0) AS DECIMAL(20,4)) - COALESCE(SUM(a.amount), 0) AS unapplied'));

        return (float) DB::query()->fromSub($perPayment, 'payments')->sum('unapplied');
    }

    public function down(): void
    {
        Schema::dropIfExists('payment_invoice');
    }
};
