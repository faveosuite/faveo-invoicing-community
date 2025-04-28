<?php

namespace App\Http\Controllers\Common;

use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\Controller;
use App\User;
use Illuminate\Http\Request;

class SystemManagerController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('admin');
    }

    public function getSystemManagers()
    {
        $accountManagers = User::where('role', 'admin')
            ->where('position', 'account_manager')
            ->pluck('first_name', 'id')->toArray();

        $salesManager = User::where('role', 'admin')
        ->where('position', 'manager')
        ->pluck('first_name', 'id')->toArray();

        return view('themes.default1.common.system-managers', compact('accountManagers', 'salesManager'));
    }

    public function searchAdmin(Request $request)
    {
        try {
            $term = trim($request->q);
            if (empty($term)) {
                return \Response::json([]);
            }
            $users = User::where('email', 'LIKE', '%'.$term.'%')
             ->orWhere('first_name', 'LIKE', '%'.$term.'%')
             ->orWhere('last_name', 'LIKE', '%'.$term.'%')
             ->select('id', 'email', 'profile_pic', 'first_name', 'last_name', 'role')->get();
            $formatted_tags = [];

            foreach ($users as $user) {
                if ($user->role == 'admin') {
                    $formatted_users[] = ['id' => $user->id, 'text' => $user->email, 'profile_pic' => $user->profile_pic,
                        'first_name' => $user->first_name, 'last_name' => $user->last_name, ];
                }
            }

            return \Response::json($formatted_users);
        } catch (\Exception $e) {
            // returns if try fails with exception meaagse
            return redirect()->back()->with('fails', $e->getMessage());
        }
    }

    /**
     * Replace old account manager with the newly selected account manager.
     *
     * @author Ashutosh Pathak <ashutosh.pathak@ladybirdweb.com>
     *
     * @date   2019-08-21T12:54:03+0530
     *
     * @param  Request  $request
     * @return array
     */
    public function replaceAccountManager(Request $request)
    {
        $this->validate($request, [
            'existingAccManager' => 'required',
            'newAccManager' => 'required',
        ], [
            'existingAccManager.required' => __('message.existingAccManager_required'),
            'newAccManager.required' => __('message.newAccManager_required'),
        ]);

        try {
            $existingAccManager = $request->input('existingAccManager');
            $newAccountManager = $request->input('newAccManager')[0];
            if ($existingAccManager == $newAccountManager) {
                return ['message' => 'fails', 'update' => __('message.same_account_manager_error')];
            }
            //First make the selected Admin as account Manager-
            User::where('id', $newAccountManager)->update(['position' => 'account_manager']);
            $accManagers = User::where('account_manager', $existingAccManager)->get();
            foreach ($accManagers as $accManager) {
                User::where('id', $accManager->id)->update(['account_manager' => $newAccountManager]);
            }
            $arrayOfBccEmails = User::where('account_manager', $newAccountManager)->get();
            if ($arrayOfBccEmails && emailSendingStatus()) {
                foreach ($arrayOfBccEmails as $user) {
                    $cont = new AuthController();
                    $sendMail = $cont->accountManagerMail($user);
                }
            }

            return ['message' => 'success', 'update' => \Lang::get('message.account_man_replaced_success')];
        } catch (\Exception $ex) {
            return ['message' => 'fails', 'update' => $ex->getMessage()];
        }
    }

    /**
     * Replace old sales manager with the newly selected sales manager.
     *
     * @author Ashutosh Pathak <ashutosh.pathak@ladybirdweb.com>
     *
     * @date   2019-08-21T12:54:03+0530
     *
     * @param  Request  $request
     * @return array
     */
    public function replaceSalesManager(Request $request)
    {
        $this->validate($request, [
            'existingSaleManager' => 'required',
            'newSaleManager' => 'required',
        ], [
            'existingSaleManager.required' => __('message.select_system_sales_manager'),
            'newSaleManager.required' => __('message.select_new_sales_manager'),
        ]);

        try {
            $existingSaleManager = $request->input('existingSaleManager');
            $newSalesManager = $request->input('newSaleManager')[0];
            if ($existingSaleManager == $newSalesManager) {
                return ['message' => 'fails', 'update' => __('message.sales_manager_must_be_different')];
            }
            //First make the selected Admin as sales Manager-
            User::where('id', $newSalesManager)->update(['position' => 'manager']);

            $saleManagers = User::where('manager', $existingSaleManager)->get();
            foreach ($saleManagers as $saleManager) {
                User::where('id', $saleManager->id)->update(['manager' => $newSalesManager]);
            }
            $arrayOfBccEmails = User::where('manager', $newSalesManager)->get();
            if ($arrayOfBccEmails && emailSendingStatus()) {
                foreach ($arrayOfBccEmails as $user) {
                    $cont = new AuthController();
                    $sendMail = $cont->salesManagerMail($user);
                }
            }

            return ['message' => 'success', 'update' => \Lang::get('message.sales_man_replaced_success')];
        } catch (\Exception $ex) {
            return ['message' => 'fails', 'update' => $ex->getMessage()];
        }
    }
}
