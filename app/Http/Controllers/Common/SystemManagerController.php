<?php

namespace App\Http\Controllers\Common;

use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\Controller;
use App\Http\Requests\Common\SystemManagerSettingsRequest;
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
        $users = User::select('id', 'first_name', 'last_name', 'email', 'position')
            ->where('role', 'admin')
            ->whereIn('position', ['account_manager', 'manager'])
            ->get();

        $accountManagers = $users->filter(fn ($user) => $user->position === 'account_manager')
            ->mapWithKeys(fn ($user) => [$user->id => $user->first_name.' '.$user->last_name.' <'.$user->email.'>'])
            ->toArray();

        $salesManager = $users->filter(fn ($user) => $user->position === 'manager')
            ->mapWithKeys(fn ($user) => [$user->id => $user->first_name.' '.$user->last_name.' <'.$user->email.'>'])
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
     * Updates manager settings for account and sales managers.
     *
     * Validates the request, updates manager assignments, auto-assign settings,
     * and sends notification emails if enabled.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse|\Illuminate\Http\RedirectResponse
     */
    public function updateManagerSettings(SystemManagerSettingsRequest $request)
    {
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

    /**
     * Updates manager assignment and notifies users.
     *
     * @param  string  $managerColumn  The column representing the manager relationship.
     * @param  string  $positionColumn  The column representing the user's position.
     * @param  string  $role  The manager role ('account' or 'sales').
     * @param  int  $oldManagerId  The ID of the old manager.
     * @param  int  $newManagerId  The ID of the new manager.
     * @param  \Closure  $mailCallback  Callback to send notification email.
     * @return void
     */
    private function updateManager($managerColumn, $positionColumn, $role, $oldManagerId, $newManagerId, Closure $mailCallback)
    {
        if (! filled($oldManagerId) || ! filled($newManagerId)) {
            return;
        }

        $position = $role === 'account' ? 'account_manager' : 'manager';
        User::where('id', $newManagerId)->update([$positionColumn => $position]);

        User::where($managerColumn, $oldManagerId)->update([$managerColumn => $newManagerId]);

        if (emailSendingStatus()) {
            User::where($managerColumn, $newManagerId)
                ->cursor()
                ->each(function ($user) use ($mailCallback) {
                    $mailCallback($user);
                });
        }
    }
}
