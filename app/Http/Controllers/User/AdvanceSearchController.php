<?php

namespace App\Http\Controllers\User;

use App\Services\Payment\CreditBalanceService;

class AdvanceSearchController extends AdminOrderInvoiceController
{
    /** Client's spendable credit balance. Pass no currency to sum across all of the user's currencies (display-only use). */
    public function getExtraAmt(mixed $userId, ?string $currency = null): float
    {
        return app(CreditBalanceService::class)->balance((int) $userId, $currency);
    }
}
