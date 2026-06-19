<?php

declare(strict_types=1);

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;

class AdminOrderInvoiceController extends Controller
{
    // Get Payment Details on Invoice Page

    public function creditActivityPopup(mixed $paymentId): Factory|View
    {
        return view('themes.default1.front.clients.credit-activity-popup', compact('paymentId')); // @phpstan-ignore argument.type
    }
}
