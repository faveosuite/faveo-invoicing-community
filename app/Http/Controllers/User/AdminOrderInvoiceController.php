<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\License\Models\Installation;
use App\Model\Order\Invoice;
use App\Model\Order\Order;
use App\Model\Product\Product;
use Illuminate\Http\Request;
use Yajra\DataTables\DataTables;

class AdminOrderInvoiceController extends Controller
{


    //Get Payment Details on Invoice Page

    public function creditActivityPopup($paymentId)
    {
        return view('themes.default1.front.clients.credit-activity-popup', compact('paymentId'));
    }
}
