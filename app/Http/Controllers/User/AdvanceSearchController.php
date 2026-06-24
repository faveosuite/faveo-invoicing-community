<?php

namespace App\Http\Controllers\User;

use App\Model\Order\Payment;
use Exception;
use Logger;

class AdvanceSearchController extends AdminOrderInvoiceController
{
    public function getExtraAmt(mixed $userId): mixed
    {
        try {
            $amounts = Payment::where('user_id', $userId)->where('invoice_id', 0)->select('amt_to_credit')->get();
            $balance = 0;
            foreach ($amounts as $amount) {
                $balance += $amount->amt_to_credit;
            }

            return $balance;
        } catch (Exception $exception) {
            Logger::exception($exception);

            return errorResponse($exception->getMessage());
        }
    }
}
