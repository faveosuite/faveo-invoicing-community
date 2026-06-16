<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;

class AdminOrderInvoiceController extends Controller
{
    //Get Payment Details on Invoice Page

    public function creditActivityPopup($paymentId)
    {
        return view('themes.default1.front.clients.credit-activity-popup', compact('paymentId'));
    }
}
