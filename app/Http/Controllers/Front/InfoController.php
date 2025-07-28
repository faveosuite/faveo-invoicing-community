<?php

namespace App\Http\Controllers\Front;

use App\Http\Controllers\Controller;
use App\Model\Common\State;
use App\Model\Payment\TaxByState;

class InfoController extends Controller
{
    /**
     * Get User State.
     *
     * @return type
     */
    public function getState()
    {
        if (\Auth::user()->country != 'IN') {
            $states = State::where('state_subdivision_code', \Auth::user()->state)
            ->pluck('state_subdivision_name')->first();
        } else {
            $states = TaxByState::where('state_code', \Auth::user()->state)->pluck('state')->first();
        }

        return $states;
    }

    public function payment($payment_method, $status)
    {
        if (! $payment_method) {
            $payment_method = '';
            $status = 'success';
        }

        return ['payment' => $payment_method, 'status' => $status];
    }
}
