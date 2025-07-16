<?php

namespace App\Http\Controllers\Common;

use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\Controller;
use App\Model\Common\ManagerSetting;
use App\User;
use Closure;
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

    public function updateManagerSettings(Request $request)
    {
        $this->validate($request, [
            'existingAccManager' => 'required_with:newAccManager|integer',
            'newAccManager' => 'required_with:existingAccManager|integer|different:existingAccManager',
            'existingSaleManager' => 'required_with:newSaleManager|integer',
            'newSaleManager' => 'required_with:existingSaleManager|integer|different:existingSaleManager',
            'autoAssignAccount' => 'required|boolean',
            'autoAssignSales' => 'required|boolean',
        ], [
            'existingAccManager.required_with' => __('message.existingAccManager_required'),
            'newAccManager.required_with' => __('message.newAccManager_required'),
            'newAccManager.different' => __('message.same_account_manager_error'),
            'existingSaleManager.required_with' => __('message.select_system_sales_manager'),
            'newSaleManager.required_with' => __('message.select_new_sales_manager'),
            'newSaleManager.different' => __('message.sales_manager_must_be_different'),
        ]);

        try {
            $mailer = new AuthController;

            $this->updateManager(
                'account_manager',
                'position',
                'account',
                $request->existingAccManager,
                $request->newAccManager,
                fn ($user) => $mailer->accountManagerMail($user)
            );

            $this->updateManager(
                'manager',
                'position',
                'sales',
                $request->existingSaleManager,
                $request->newSaleManager,
                fn ($user) => $mailer->salesManagerMail($user)
            );

            ManagerSetting::whereManagerRole('account')->update(['auto_assign' => $request->autoAssignAccount]);
            ManagerSetting::whereManagerRole('sales')->update(['auto_assign' => $request->autoAssignSales]);

            return successResponse(__('message.manager_settings_updated_successfully'));
        } catch (\Exception $e) {
            return errorResponse($e->getMessage());
        }
    }

    private function updateManager($managerColumn, $positionColumn, $role, $oldManagerId, $newManagerId, Closure $mailCallback)
    {
        if (! filled($oldManagerId) || ! filled($newManagerId)) {
            return;
        }

        $position = $role === 'account' ? 'account_manager' : 'manager';
        User::where('id', $newManagerId)->update([$positionColumn => $position]);

        User::where($managerColumn, $oldManagerId)->update([$managerColumn => $newManagerId]);

        if (emailSendingStatus()) {
            $users = User::where($managerColumn, $newManagerId)->get();
            foreach ($users as $user) {
                $mailCallback($user);
            }
        }
    }
}
