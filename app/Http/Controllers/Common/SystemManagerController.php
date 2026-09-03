<?php

namespace App\Http\Controllers\Common;

use App\Http\Controllers\Controller;
use App\Http\Requests\Common\SystemManagerSettingsRequest;
use App\Jobs\NotifyManagerChange;
use App\Model\Common\ManagerSetting;
use App\User;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SystemManagerController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('admin');
    }

    public function getSystemManagers(): JsonResponse
    {
        try {
            $users = User::select('id', 'first_name', 'last_name', 'email', 'position')
                ->where('role', 'admin')
                ->whereIn('position', ['account_manager', 'manager'])
                ->get();

            $accountManagers = $users
                ->filter(fn ($user): bool => $user->position === 'account_manager')
                ->map(fn ($user): array => [

                    'id' => $user->id,
                    'name' => $user->first_name.' '.$user->last_name,
                    'email' => $user->email,
                ])
                ->values();

            $salesManagers = $users
                ->filter(fn ($user): bool => $user->position === 'manager')
                ->map(fn ($user): array => [
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
        } catch (Exception $exception) {
            return errorResponse($exception->getMessage());
        }
    }

    public function searchAdmin(Request $request): JsonResponse
    {
        try {
            $term = trim($request->input('search-query') ?? '');

            $users = User::where('role', 'admin')
                ->when($term, function ($query) use ($term): void {
                    $query->where(function ($q) use ($term): void {
                        $q->where('first_name', 'LIKE', sprintf('%%%s%%', $term))
                            ->orWhere('last_name', 'LIKE', sprintf('%%%s%%', $term))
                            ->orWhere('email', 'LIKE', sprintf('%%%s%%', $term));
                    });
                })
                ->select('id', 'email', 'first_name', 'last_name')
                ->paginate();

            $users->getCollection()->transform(fn ($user): array => [
                'id' => $user->id,
                'name' => $user->first_name.' '.$user->last_name,
                'email' => $user->email,
            ]);

            return successResponse('', $users);
        } catch (Exception $exception) {
            return errorResponse($exception->getMessage());
        }
    }

    /**
     * Updates manager settings for account and sales managers.
     *
     * Validates the request, updates manager assignments, auto-assign settings,
     * and sends notification emails if enabled.
     */
    public function updateManagerSettings(SystemManagerSettingsRequest $request): JsonResponse
    {
        try {
            $this->updateManager('account_manager', $request->existingAccManager, $request->newAccManager);
            $this->updateManager('manager', $request->existingSaleManager, $request->newSaleManager);

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
        } catch (Exception $exception) {
            return errorResponse($exception->getMessage());
        }
    }

    private function updateManager(string $managerColumn, ?int $oldManagerId, ?int $newManagerId): void
    {
        if (blank($oldManagerId) || blank($newManagerId)) {
            return;
        }

        User::where('id', $newManagerId)->update(['position' => $managerColumn]);

        $affectedUserIds = emailSendingStatus()
            ? User::where($managerColumn, $oldManagerId)->pluck('id')->all()
            : [];

        User::where($managerColumn, $oldManagerId)->update([$managerColumn => $newManagerId]);

        if ($affectedUserIds) {
            NotifyManagerChange::dispatch($affectedUserIds, $managerColumn, (int) $newManagerId);
        }
    }
}
