<?php

namespace App\Http\Controllers\User;

use App\Events\UserOrderDelete;
use App\Model\Product\Subscription;
use App\User;
use DB;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SoftDeleteController extends ClientController
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('admin');
    }

    public function softDeletedUsers(Request $request): JsonResponse
    {
        $searchQuery = $request->input('search-query', '');
        $sortOrder = $request->input('sort-order', 'asc');
        $sortField = $request->input('sort-field', 'created_at');
        $limit = $request->input('limit', 10);

        $users = User::select('id', 'first_name', 'last_name', 'email', 'mobile', 'mobile_code', 'country', 'created_at')
            ->where(function ($query) use ($searchQuery): void {
                $query->where('email', 'like', '%'.$searchQuery.'%')
                    ->orWhere(DB::raw('CONCAT(first_name, " ", last_name)'), 'like', '%'.$searchQuery.'%')
                    ->orWhere('mobile', 'like', '%'.$searchQuery.'%')
                    ->orWhere('country', 'like', '%'.$searchQuery.'%')
                    ->orWhere('created_at', 'like', '%'.$searchQuery.'%');
            })
            ->orderBy($sortField, $sortOrder)
            ->onlyTrashed()
            ->simplePaginate($limit);

        $users->getCollection()->transform(function ($user) {
            if ($user->country) {
                $name = getCountryByCode($user->country) ?? $user->country;
                $user->setRawAttributes(array_merge($user->getAttributes(), ['country' => $name]), true);
            }

            return $user;
        });

        return successResponse('', $users);
    }

    public function restoreUser(mixed $id): JsonResponse
    {
        /** @var User|null $user */
        $user = User::onlyTrashed()->find($id);

        if (! $user) {
            return errorResponse(__('message.user_not_found'), 404);
        }

        $user->restore();

        return successResponse(__('message.user_restored_successfully'));
    }

    public function permanentDeleteUser(Request $request): JsonResponse
    {
        $ids = $request->input('user_ids', []);

        if (empty($ids)) {
            return errorResponse(__('message.select-a-row'));
        }

        try {
            User::onlyTrashed()->whereIn('id', $ids)->get()->each(function ($user): void {
                $user->order()->pluck('id')->each(function ($tenant): void {
                    $installation_path = DB::table('installation_details')
                        ->where('order_id', $tenant)
                        ->where('installation_path', '!=', cloudCentralDomain())
                        ->value('installation_path');

                    $isCloudDeleted = Subscription::where('order_id', $tenant)
                        ->where('is_deleted', 1)
                        ->exists();

                    if ($installation_path && ! $isCloudDeleted) {
                        event(new UserOrderDelete($installation_path, $tenant));
                    }
                });

                $user->invoiceItem()->delete();
                $user->orderRelation()->delete();
                $user->invoice()->delete();
                $user->order()->delete();
                $user->subscription()->delete();
                $user->comments()->delete();
                $user->auto_renewal()->delete();
                $user->export_details()->delete();
                $user->userLinkReports()->delete();
                $user->whatsappUsers()->delete();

                $user->forceDelete();
            });

            return successResponse(__('message.deleted-successfully'));
        } catch (Exception $exception) {
            return errorResponse($exception->getMessage());
        }
    }
}
