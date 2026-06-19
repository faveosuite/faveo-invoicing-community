<?php

declare(strict_types=1);

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;

class AdminOrderInvoiceController extends Controller
{
    //Get Payment Details on Invoice Page

    public function creditActivityPopup(mixed $paymentId): \Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View
    {
        return view('themes.default1.front.clients.credit-activity-popup', compact('paymentId')); // @phpstan-ignore argument.type
    }
}
