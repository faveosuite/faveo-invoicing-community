<?php

namespace App\Http\Controllers\User;

use App\Events\UserOrderDelete;
use App\User;
use Illuminate\Http\Request;

class SoftDeleteController extends ClientController
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('admin');
    }

    public function index(Request $request)
    {
        return view('themes.default1.user.client.softDelete', compact('request'));
    }

    public function softDeletedUsers(Request $request)
    {
        $searchQuery = $request->input('search-query', '');
        $sortOrder = $request->input('sort-order', 'asc');
        $sortField = $request->input('sort-field', 'created_at');
        $limit = $request->input('limit', 10);
        $page = $request->input('page', 1);

        $users = User::select('id', 'first_name', 'last_name', 'email', 'mobile', 'country', 'created_at')
            ->where(function($query) use ($searchQuery) {
                $query->where('email', 'like', '%'.$searchQuery.'%')
                    ->orWhere(\DB::raw('CONCAT(first_name, " ", last_name)'), 'like', '%'.$searchQuery.'%')
                    ->orWhere('mobile', 'like', '%'.$searchQuery.'%')
                    ->orWhere('country', 'like', '%'.$searchQuery.'%')
                    ->orWhere('created_at', 'like', '%'.$searchQuery.'%');
            })
            ->orderBy($sortField, $sortOrder)
            ->onlyTrashed()
            ->paginate($limit, ['*'], 'page', $page);

        return successResponse('', $users);
    }

    public function restoreUser($id)
    {
        $user = User::onlyTrashed()->find($id);

        if (! $user) {
            return errorResponse(__('message.user_not_found'), 404);
        }

        $user->restore();

        return successResponse(__('message.user_restored_successfully'));
    }

    public function permanentDeleteUser(Request $request)
    {
        $ids = $request->input('user_ids', []);

        if( empty($ids) ){
            return errorResponse(__('message.select-a-row'));
        }

        try {

            User::onlyTrashed()->whereIn('id', $ids)->get()->each(function($user) {
                $user->order()->pluck('id')->each(function($tenant) {
                    $installation_path = \DB::table('installation_details')
                        ->where('order_id', $tenant)
                        ->where('installation_path', '!=', cloudCentralDomain())
                        ->value('installation_path');

                    if ($installation_path) {
                        event(new UserOrderDelete($installation_path));
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

                $user->forceDelete();
            });

            return successResponse(__('message.deleted-successfully'));
        } catch (\Exception $e) {
            return errorResponse($e->getMessage());
        }
    }
}
