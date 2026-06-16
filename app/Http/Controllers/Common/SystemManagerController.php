<?php

namespace App\Http\Controllers\Common;

use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
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
        try {
            $users = User::select('id', 'first_name', 'last_name', 'email', 'position')
                ->where('role', 'admin')
                ->whereIn('position', ['account_manager', 'manager'])
                ->get();

            $accountManagers = $users
                ->filter(fn ($user) => $user->position === 'account_manager')
                ->map(fn ($user) => [

                    'id' => $user->id,
                    'name' => $user->first_name.' '.$user->last_name,
                    'email' => $user->email,
                ])
                ->values();

            $salesManagers = $users
                ->filter(fn ($user) => $user->position === 'manager')
                ->map(fn ($user) => [
                    'id' => $user->id,
                    'name' => $user->first_name.' '.$user->last_name,
                    'email' => $user->email,
                ])
                ->values();

            $settings = ManagerSetting::whereIn('manager_role', ['account', 'sales'])
                ->pluck('auto_assign', 'manager_role');

            $accountManagersAutoAssign = $settings['account'] ?? false;
            $salesManagersAutoAssign = $settings['sales'] ?? false;

            $response = [
                'account_managers' => $accountManagers,
                'sales_managers' => $salesManagers,
                'account_managers_auto_assign' => (bool) $accountManagersAutoAssign,
                'sales_managers_auto_assign' => (bool) $salesManagersAutoAssign,
            ];

            return successResponse('', $response);
        } catch (Exception $e) {
            return errorResponse($e->getMessage());
        }
    }

    public function searchAdmin(Request $request)
    {
        try {
            $term = trim($request->input('search-query') ?? '');

            $users = User::where('role', 'admin')
                ->when($term, function ($query) use ($term): void {
                    $query->where(function ($q) use ($term): void {
                        $q->where('first_name', 'LIKE', "%{$term}%")
                            ->orWhere('last_name', 'LIKE', "%{$term}%")
                            ->orWhere('email', 'LIKE', "%{$term}%");
                    });
                })
                ->select('id', 'email', 'first_name', 'last_name')
                ->simplePaginate();

            $users->getCollection()->transform(fn($user) => [
                'id' => $user->id,
                'name' => $user->first_name.' '.$user->last_name,
                'email' => $user->email,
            ]);

            return successResponse('', $users);
        } catch (Exception $e) {
            return errorResponse($e->getMessage());
        }
    }

    /**
     * Updates manager settings for account and sales managers.
     *
     * Validates the request, updates manager assignments, auto-assign settings,
     * and sends notification emails if enabled.
     *
     * @param Request $request
     * @return JsonResponse|RedirectResponse
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

            $roles = [
                'account' => $request->autoAssignAccount,
                'sales' => $request->autoAssignSales,
            ];

            foreach ($roles as $role => $autoAssign) {
                if ($setting = ManagerSetting::whereManagerRole($role)->first()) {
                    $setting->update(['auto_assign' => $autoAssign]);
                }
            }

            return successResponse(__('message.manager_settings_updated_successfully'));
        } catch (Exception $e) {
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
     * @param Closure $mailCallback Callback to send notification email.
     * @return void
     */
    private function updateManager($managerColumn, $positionColumn, $role, $oldManagerId, $newManagerId, Closure $mailCallback)
    {
        if (blank($oldManagerId) || blank($newManagerId)) {
            return;
        }

        $position = $role === 'account' ? 'account_manager' : 'manager';
        User::where('id', $newManagerId)->update([$positionColumn => $position]);

        $affectedUserIds = User::where($managerColumn, $oldManagerId)->pluck('id');

        User::where($managerColumn, $oldManagerId)->update([$managerColumn => $newManagerId]);

        if (emailSendingStatus() && $affectedUserIds->isNotEmpty()) {
            User::whereIn('id', $affectedUserIds)
                ->cursor()
                ->each(function ($user) use ($mailCallback): void {
                    $mailCallback($user);
                });
        }
    }
}
