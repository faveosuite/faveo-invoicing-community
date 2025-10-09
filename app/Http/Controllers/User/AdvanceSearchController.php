<?php

namespace App\Http\Controllers\User;

use App\Model\Order\Payment;
use App\User;
use Illuminate\Http\Request;

class AdvanceSearchController extends AdminOrderInvoiceController
{
    /**
     * Serach for Registered From,tILL.
     */
    public function getregFromTill($join, $reg_from, $reg_till)
    {
        if ($reg_from) {
            $fromDateStart = date_create($reg_from)->format('Y-m-d').' 00:00:00';
            $join = $join->where('created_at', '>=', $fromDateStart);
        }

        if ($reg_till) {
            $tillDateEnd = date_create($reg_till)->format('Y-m-d').' 23:59:59';
            $join = $join->where('created_at', '<=', $tillDateEnd);
        }

        return $join;
    }

    public function search(Request $request)
    {
        try {
            $term = trim($request->q);
            if (empty($term)) {
                return \Response::json([]);
            }
            $users = User::where('email', 'LIKE', '%'.$term.'%')
             ->orWhere('first_name', 'LIKE', '%'.$term.'%')
             ->orWhere('last_name', 'LIKE', '%'.$term.'%')
             ->select('id', 'email', 'profile_pic', 'first_name', 'last_name')->get();
            $formatted_tags = [];

            foreach ($users as $user) {
                $formatted_users[] = ['id' => $user->id, 'text' => $user->email, 'profile_pic' => $user->profile_pic,
                    'first_name' => $user->first_name, 'last_name' => $user->last_name, ];
            }

            return \Response::json($formatted_users);
        } catch (\Exception $e) {
            // returns if try fails with exception meaagse
            return redirect()->back()->with('fails', $e->getMessage());
        }
    }

    public function getUsers(Request $request)
    {
        $options = $this->user
                ->select('email AS text', 'id AS value')
                ->get();

        return response()->json(compact('options'));
    }

    public function getClientDetail($id)
    {
        $client = $this->user->where('id', $id)->first();
        $currency = $client->currency;
        if (array_key_exists('name', getStateByCode($client->state))) {
            $client->state = getStateByCode($client->state)['name'];
        }
        $client->country = ucwords(strtolower(getCountryByCode($client->country)));

        $displayData = ['currency' => $currency, 'client' => $client];

        return $displayData;
    }

    public function getExtraAmt($userId)
    {
        try {
            $amounts = Payment::where('user_id', $userId)->where('invoice_id', 0)->select('amt_to_credit')->get();
            $balance = 0;
            foreach ($amounts as $amount) {
                if ($amount) {
                    $balance = $balance + $amount->amt_to_credit;
                }
            }

            return $balance;
        } catch (\Exception $ex) {
            \Logger::exception($ex);

            return redirect()->back()->with('fails', $ex->getMessage());
        }
    }
}
