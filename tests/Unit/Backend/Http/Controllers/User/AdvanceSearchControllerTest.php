<?php

declare(strict_types=1);

namespace Tests\Unit\Backend\Http\Controllers\User;

use App\Http\Controllers\User\AdvanceSearchController;
use App\Model\Order\CreditTransaction;
use App\Services\Payment\CreditBalanceService;
use App\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\DBTestCase;

class AdvanceSearchControllerTest extends DBTestCase
{
    use DatabaseTransactions;

    public function test_get_extra_amt_returns_zero_for_a_user_with_no_credit(): void
    {
        $user = User::factory()->create();

        $this->assertSame(0.0, new AdvanceSearchController()->getExtraAmt($user->id));
    }

    public function test_get_extra_amt_returns_balance_for_a_currency(): void
    {
        $user = User::factory()->create();
        app(CreditBalanceService::class)->grant($user->id, 'USD', 40.0, CreditTransaction::TYPE_OVERPAYMENT);

        $this->assertSame(40.0, new AdvanceSearchController()->getExtraAmt($user->id, 'USD'));
    }

    public function test_get_extra_amt_with_no_currency_sums_across_currencies(): void
    {
        $user = User::factory()->create();
        app(CreditBalanceService::class)->grant($user->id, 'USD', 40.0, CreditTransaction::TYPE_OVERPAYMENT);
        app(CreditBalanceService::class)->grant($user->id, 'INR', 1000.0, CreditTransaction::TYPE_OVERPAYMENT);

        $this->assertSame(1040.0, new AdvanceSearchController()->getExtraAmt($user->id));
    }
}
