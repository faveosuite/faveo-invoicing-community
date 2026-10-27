<?php

declare(strict_types=1);

namespace Tests\Unit\Backend\Services\Payment;

use App\Model\Order\CreditTransaction;
use App\Services\Payment\CreditBalanceService;
use App\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\DBTestCase;

class CreditBalanceServiceTest extends DBTestCase
{
    use DatabaseTransactions;

    private CreditBalanceService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new CreditBalanceService;
    }

    public function test_balance_is_zero_for_a_user_with_no_credit(): void
    {
        $user = User::factory()->create();

        $this->assertSame(0.0, $this->service->balance($user->id, 'USD'));
    }

    public function test_grant_increases_balance_and_records_a_ledger_row(): void
    {
        $user = User::factory()->create();

        $this->service->grant($user->id, 'USD', 50.0, CreditTransaction::TYPE_OVERPAYMENT);

        $this->assertSame(50.0, $this->service->balance($user->id, 'USD'));
        $this->assertDatabaseHas('credit_transactions', [
            'user_id' => $user->id,
            'currency' => 'USD',
            'amount' => '50',
            'type' => CreditTransaction::TYPE_OVERPAYMENT,
        ]);
    }

    public function test_grant_is_pooled_across_types_within_the_same_currency(): void
    {
        $user = User::factory()->create();

        $this->service->grant($user->id, 'USD', 20.0, CreditTransaction::TYPE_MANUAL_GRANT);
        $this->service->grant($user->id, 'USD', 30.0, CreditTransaction::TYPE_DOWNGRADE_PRORATION);

        $this->assertSame(50.0, $this->service->balance($user->id, 'USD'));
    }

    public function test_grant_keeps_different_currencies_separate(): void
    {
        $user = User::factory()->create();

        $this->service->grant($user->id, 'USD', 20.0, CreditTransaction::TYPE_OVERPAYMENT);
        $this->service->grant($user->id, 'INR', 500.0, CreditTransaction::TYPE_OVERPAYMENT);

        $this->assertSame(20.0, $this->service->balance($user->id, 'USD'));
        $this->assertSame(500.0, $this->service->balance($user->id, 'INR'));
    }

    public function test_balance_with_no_currency_sums_across_all_currencies(): void
    {
        $user = User::factory()->create();

        $this->service->grant($user->id, 'USD', 20.0, CreditTransaction::TYPE_OVERPAYMENT);
        $this->service->grant($user->id, 'INR', 500.0, CreditTransaction::TYPE_OVERPAYMENT);

        $this->assertSame(520.0, $this->service->balance($user->id));
    }

    public function test_apply_decreases_balance_and_records_a_negative_ledger_row(): void
    {
        $user = User::factory()->create();
        $this->service->grant($user->id, 'USD', 50.0, CreditTransaction::TYPE_OVERPAYMENT);

        $this->service->apply($user->id, 'USD', 30.0, invoiceId: 123);

        $this->assertSame(20.0, $this->service->balance($user->id, 'USD'));
        $this->assertDatabaseHas('credit_transactions', [
            'user_id' => $user->id,
            'currency' => 'USD',
            'amount' => '-30',
            'type' => CreditTransaction::TYPE_APPLIED_TO_INVOICE,
            'invoice_id' => 123,
        ]);
    }

    public function test_apply_throws_when_balance_is_insufficient(): void
    {
        $user = User::factory()->create();
        $this->service->grant($user->id, 'USD', 10.0, CreditTransaction::TYPE_OVERPAYMENT);

        $this->expectException(\Exception::class);

        $this->service->apply($user->id, 'USD', 20.0, invoiceId: 1);
    }

    public function test_apply_does_not_touch_a_different_currencys_balance(): void
    {
        $user = User::factory()->create();
        $this->service->grant($user->id, 'INR', 1000.0, CreditTransaction::TYPE_OVERPAYMENT);

        $this->expectException(\Exception::class);

        // No USD credit exists even though the user has plenty of INR credit.
        $this->service->apply($user->id, 'USD', 10.0, invoiceId: 1);
    }
}
