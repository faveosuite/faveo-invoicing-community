<?php

namespace App\Http\Controllers\User;

use App\Model\Order\Payment;
use App\User;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Date;
use Logger;
use Response;

class AdvanceSearchController extends AdminOrderInvoiceController
{
    /**
     * Serach for Registered From,tILL.
     */
    public function getregFromTill(mixed $join, mixed $reg_from, mixed $reg_till): mixed
    {
        if ($reg_from) {
            $fromDateStart = Date::parse($reg_from)->format('Y-m-d').' 00:00:00';
            $join = $join->where('users.created_at', '>=', $fromDateStart);
        }

        if ($reg_till) {
            $tillDateEnd = Date::parse($reg_till)->format('Y-m-d').' 23:59:59';
            $join = $join->where('users.created_at', '<=', $tillDateEnd);
        }

        return $join;
    }

    public function search(Request $request): mixed
    {
        try {
            $term = trim($request->q);
            if ($term === '' || $term === '0') {
                return Response::json([]);
            }

            $users = User::where('email', 'LIKE', '%'.$term.'%')
                ->orWhere('first_name', 'LIKE', '%'.$term.'%')
                ->orWhere('last_name', 'LIKE', '%'.$term.'%')
                ->select('id', 'email', 'profile_pic', 'first_name', 'last_name')->get();
            $formatted_tags = [];
            $formatted_users = [];

            foreach ($users as $user) {
                $formatted_users[] = ['id' => $user->id, 'text' => $user->email, 'profile_pic' => $user->profile_pic,
                    'first_name' => $user->first_name, 'last_name' => $user->last_name, ];
            }

            return Response::json($formatted_users);
        } catch (Exception $exception) {
            // returns if try fails with exception meaagse
            return errorResponse($exception->getMessage());
        }
    }

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
