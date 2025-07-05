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
        $user = \Auth::user();

        if ($user->country !== 'IN') {
            return State::where('country_code', $user->country)
                ->where('iso2', $user->state)
                ->value('state_subdivision_name');
        }

        return TaxByState::where('state_code', $user->state)
            ->value('state');
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
