<?php

namespace App\Http\Controllers\Common;

use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\Controller;
use App\Model\Common\ManagerSetting;
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
        $accountManagers = User::selectRaw("id, CONCAT(first_name, ' ', last_name, ' <', email, '>') AS display")
            ->where('role', 'admin')
            ->where('position', 'account_manager')
            ->pluck('display', 'id')
            ->toArray();

        $salesManager = User::selectRaw("id, CONCAT(first_name, ' ', last_name, ' <', email, '>') AS display")
            ->where('role', 'admin')
            ->where('position', 'manager')
            ->pluck('display', 'id')
            ->toArray();

        $settings = ManagerSetting::whereIn('manager_role', ['account', 'sales'])
            ->pluck('auto_assign', 'manager_role');

        $accountManagersAutoAssign = $settings['account'];
        $salesManagerAutoAssign = $settings['sales'];

        return view('themes.default1.common.system-managers', compact(
            'accountManagers',
            'salesManager',
            'accountManagersAutoAssign',
            'salesManagerAutoAssign'
        ));
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
     * @return \HTTP
     */
    public function replaceAccountManager(Request $request)
    {
        $this->validate($request, [
            'existingAccManager' => 'required',
            'newAccManager' => 'required',
        ], [
            'existingAccManager.required' => __('message.existingAccManager_required'),
            'newAccManager.required'     => __('message.newAccManager_required'),
        ]);

        try {
            $existingId = $request->input('existingAccManager');
            $newId = $request->input('newAccManager');

            if ($existingId == $newId) {
                return errorResponse(__('message.same_account_manager_error'));
            }

            // Promote new user to account manager
            User::where('id', $newId)->update(['position' => 'account_manager']);

            // Reassign users under old manager to new one
            User::where('account_manager', $existingId)
                ->update(['account_manager' => $newId]);

            if (emailSendingStatus()) {
                $usersToNotify = User::where('account_manager', $newId)->get();
                $mailer = new AuthController;
                foreach ($usersToNotify as $user) {
                    $mailer->accountManagerMail($user);
                }
            }

            return successResponse(__('message.account_man_replaced_success'));
        } catch (\Exception $e) {
            return errorResponse($e->getMessage());
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
     * @return \HTTP
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
            $existingId = $request->input('existingSaleManager');
            $newId      = $request->input('newSaleManager');

            if ($existingId == $newId) {
                return errorResponse(__('message.sales_manager_must_be_different'));
            }

            // Promote the new user as sales manager
            User::where('id', $newId)->update(['position' => 'manager']);

            // Reassign all users under the old manager to the new one
            User::where('manager', $existingId)->update(['manager' => $newId]);

            // Notify affected users if email sending is enabled
            if (emailSendingStatus()) {
                $usersToNotify = User::where('manager', $newId)->get();

                $mailer = new AuthController;
                foreach ($usersToNotify as $user) {
                    $mailer->salesManagerMail($user);
                }
            }

            return successResponse(__('message.sales_man_replaced_success'));
        } catch (\Exception $e) {
            return errorResponse($e->getMessage());
        }
    }

    public function setAutoAssignToManager(Request $request)
    {
        $this->validate($request, [
            'manager_role' => 'required|in:account,sales',
            'status' => 'required|boolean',
        ], [
            'manager_role.required' => __('manager_validation.manager_role.required'),
            'manager_role.in' => __('manager_validation.manager_role.in'),
            'status.required' => __('manager_validation.status.required'),
            'status.boolean' => __('manager_validation.status.boolean'),
        ]);

        $managerRole = $request->input('manager_role');
        $status = (bool) $request->input('status');

        ManagerSetting::whereManagerRole($managerRole)
            ->update(['auto_assign' => $status]);

        return successResponse(__('message.auto_assign_success'));
    }
}
