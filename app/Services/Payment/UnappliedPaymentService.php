<?php

declare(strict_types=1);

namespace App\Services\Payment;

use App\Model\Order\Payment;
use DB;
use Exception;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Date;

/**
 * Money the client has paid us that no invoice has claimed yet.
 *
 * This is NOT credit. Invoice Ninja draws the same line and it matters: an
 * overpayment is the client's money sitting with us awaiting allocation
 * (their `client.payment_balance`, literally `SUM(payments.amount -
 * payments.applied)`), whereas credit is something we deliberately grant —
 * a downgrade proration, a goodwill adjustment — and only credit services
 * ever touch `client.credit_balance` there. Booking a receipt as credit
 * loses the fact that real money changed hands, and with it the method, the
 * date, and any hope of reconciling the bank statement.
 *
 * There is no special "receipt" row: every payment is just a payment, and its
 * unapplied portion is its amount less whatever `payment_invoice` says it has
 * already settled. A payment with no allocations is wholly unapplied.
 */
class UnappliedPaymentService
{
    /** Bank money received against no invoice yet. */
    public function record(int $userId, string $currency, float $amount, string $method, ?Carbon $date = null): Payment
    {
        return Payment::create([
            'invoice_id' => 0,
            'parent_id' => 0,
            'user_id' => $userId,
            'amount' => $amount,
            'amt_to_credit' => 0,
            'payment_method' => $method,
            'payment_status' => 'success',
            'created_at' => $date ?? Date::now(),
            'currency' => $currency,
        ]);
    }

    /** Unapplied total. Pass no currency to sum across all of them (display-only use). */
    public function balance(int $userId, ?string $currency = null): float
    {
        return (float) DB::query()
            ->fromSub($this->unappliedQuery($userId, $currency), 'payments')
            ->sum('unapplied');
    }

    /**
     * Unapplied total per currency — the honest breakdown, since unapplied
     * money can only ever pay an invoice in its own currency.
     *
     * @return array<int, array{currency: string, balance: float}>
     */
    public function balances(int $userId): array
    {
        return DB::query()
            ->fromSub($this->unappliedQuery($userId, null), 'payments')
            ->groupBy('currency')
            ->orderBy('currency')
            ->select('currency', DB::raw('SUM(unapplied) as balance'))
            ->get()
            ->map(fn ($row): array => ['currency' => (string) $row->currency, 'balance' => (float) $row->balance])
            ->all();
    }

    /** What is still unspent on one specific payment. */
    public function unappliedOn(int $userId, int $paymentId): float
    {
        return (float) DB::query()
            ->fromSub($this->unappliedQuery($userId, null, $paymentId), 'payment')
            ->sum('unapplied');
    }

    /**
     * Put unapplied money against an invoice, oldest payment first.
     *
     * Pass $paymentId to draw on one specific payment instead of the whole
     * pool — that is what "apply this payment" on a single payment row means.
     *
     * Locks the candidate payments before reading what is left on them, so two
     * admins allocating the same money at once can't both spend it.
     *
     * @throws Exception if the available amount can't cover what was asked for.
     */
    public function apply(int $userId, string $currency, float $amount, int $invoiceId, string $method, ?Carbon $date = null, ?int $paymentId = null): void
    {
        if ($amount <= 0) {
            return;
        }

        DB::transaction(function () use ($userId, $currency, $amount, $invoiceId, $date, $paymentId): void {
            // Lock the payment rows themselves before reading what is left on
            // them. Any other allocator must take the same locks first, so the
            // two serialise instead of both spending the same money.
            DB::table('payments')
                ->where('user_id', $userId)
                ->where('currency', $currency)
                ->where('payment_status', 'success')
                ->when($paymentId !== null, fn ($q) => $q->where('id', $paymentId))
                ->lockForUpdate()
                ->pluck('id');

            $candidates = $this->unappliedQuery($userId, $currency, $paymentId)->orderBy('p.id')->get();

            if (round((float) $candidates->sum('unapplied'), 2) < round($amount, 2)) {
                throw new Exception(__('message.insufficient_unapplied_payment'));
            }

            $remaining = $amount;

            foreach ($candidates as $candidate) {
                if ($remaining <= 0) {
                    break;
                }

                $take = min($remaining, (float) $candidate->unapplied);

                DB::table('payment_invoice')->insert([
                    'payment_id' => $candidate->id,
                    'invoice_id' => $invoiceId,
                    'amount' => $take,
                    'created_at' => $date ?? Date::now(),
                    'updated_at' => Date::now(),
                ]);

                $remaining -= $take;
            }
        });
    }

    /**
     * Payments with money still on them. The subtraction happens in SQL so the
     * locked read and the "how much is left" answer are the same query —
     * computing it in PHP afterwards would reopen the race the lock closes.
     */
    private function unappliedQuery(int $userId, ?string $currency, ?int $paymentId = null): \Illuminate\Database\Query\Builder
    {
        $query = DB::table('payments as p')
            ->leftJoin('payment_invoice as a', 'a.payment_id', '=', 'p.id')
            ->where('p.user_id', $userId)
            ->where('p.payment_status', 'success')
            ->groupBy('p.id', 'p.currency')
            ->havingRaw('unapplied > 0')
            ->select('p.id', 'p.currency', DB::raw(
                'CAST(COALESCE(p.amount, 0) AS DECIMAL(20,4)) - COALESCE(SUM(a.amount), 0) AS unapplied'
            ));

        if ($currency !== null) {
            $query->where('p.currency', $currency);
        }

        if ($paymentId !== null) {
            $query->where('p.id', $paymentId);
        }

        return $query;
    }
}
