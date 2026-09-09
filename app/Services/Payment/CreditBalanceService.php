<?php

declare(strict_types=1);

namespace App\Services\Payment;

use App\Model\Order\CreditTransaction;
use App\Model\Order\UserCreditBalance;
use DB;
use Exception;
use Illuminate\Database\QueryException;

/**
 * The client's credit balance — one pooled, spendable number per user per
 * currency (see {@see UserCreditBalance}), backed by an append-only history
 * of every deposit/spend (see {@see CreditTransaction}).
 *
 * Credit is fully fungible within a currency: it doesn't matter whether it
 * came from an overpayment, a manual grant, or a downgrade proration — once
 * granted, it's just spendable balance. `type`/`payment_id`/`invoice_id` on a
 * ledger row are context for *why*, never a restriction on redemption.
 *
 * Every grant/apply runs inside one DB transaction that locks the balance
 * row first (SELECT ... FOR UPDATE), so two requests spending the same
 * client's credit at once can't double-spend it — the second waits for the
 * first to commit, then sees the real, current balance.
 */
class CreditBalanceService
{
    /** Spendable balance. Pass no currency to sum across all of the user's currencies (display-only use). */
    public function balance(int $userId, ?string $currency = null): float
    {
        $query = UserCreditBalance::where('user_id', $userId);

        if ($currency !== null) {
            return (float) ($query->where('currency', $currency)->value('balance') ?? 0);
        }

        return (float) $query->sum('balance');
    }

    /** Deposit credit for a user. A no-op for a zero/negative amount. */
    public function grant(int $userId, string $currency, float $amount, string $type, ?int $paymentId = null, ?int $invoiceId = null, ?string $note = null): void
    {
        if ($amount <= 0) {
            return;
        }

        DB::transaction(function () use ($userId, $currency, $amount, $type, $paymentId, $invoiceId, $note): void {
            $row = $this->lockedBalanceRow($userId, $currency);
            $row->balance = (string) ((float) $row->balance + $amount);
            $row->save();

            CreditTransaction::create([
                'user_id' => $userId,
                'currency' => $currency,
                'amount' => (string) $amount,
                'type' => $type,
                'payment_id' => $paymentId,
                'invoice_id' => $invoiceId,
                'note' => $note,
            ]);
        });
    }

    /**
     * Spend credit against an invoice. A no-op for a zero/negative amount.
     *
     * @throws Exception if the currency's balance can't cover the amount.
     */
    public function apply(int $userId, string $currency, float $amount, ?int $invoiceId = null, ?string $note = null): void
    {
        if ($amount <= 0) {
            return;
        }

        DB::transaction(function () use ($userId, $currency, $amount, $invoiceId, $note): void {
            $row = $this->lockedBalanceRow($userId, $currency);

            if ((float) $row->balance < $amount) {
                throw new Exception(__('message.insufficient_credit_balance'));
            }

            $row->balance = (string) ((float) $row->balance - $amount);
            $row->save();

            CreditTransaction::create([
                'user_id' => $userId,
                'currency' => $currency,
                'amount' => (string) (-$amount),
                'type' => CreditTransaction::TYPE_APPLIED_TO_INVOICE,
                'invoice_id' => $invoiceId,
                'note' => $note,
            ]);
        });
    }

    /**
     * The user's balance row for this currency, locked for the rest of the
     * current transaction. Created with a zero balance first if this is the
     * user's first credit event in this currency; the create is raced safely
     * against a concurrent first-timer via the table's unique constraint.
     */
    private function lockedBalanceRow(int $userId, string $currency): UserCreditBalance
    {
        $row = UserCreditBalance::where('user_id', $userId)->where('currency', $currency)->lockForUpdate()->first();
        if ($row) {
            return $row;
        }

        try {
            return UserCreditBalance::create(['user_id' => $userId, 'currency' => $currency, 'balance' => '0']);
        } catch (QueryException) {
            // Lost the race to create it — another transaction just committed
            // the same row; lock the one that now exists.
            return UserCreditBalance::where('user_id', $userId)->where('currency', $currency)->lockForUpdate()->firstOrFail();
        }
    }
}
