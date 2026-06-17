<?php

namespace App\License\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\License\Helpers\LicenseHelper;
use App\License\Models\LicenseBannedHost;
use App\License\Models\LicenseWhitelistIp;
use App\License\Requests\BannedHostRequest;
use Illuminate\Contracts\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Lang;

/**
 * Consist of functionalities for the Banned Host page in Auto Faveo licenser
 * Class BannedHostController.
 */
class BannedHostController extends Controller
{
    public function __construct()
    {
        $this->ip_address = request()->server('REMOTE_ADDR');
    }

    /**
     *To Add Banned hosts of License manager.
     *
     * @param  $api_key_secret
     * @param  $banned_host_ip
     * @param  $comments
     * @return array of details of banned host if added successfully
     */
    public function bannedHostAdd(BannedHostRequest $request)
    {
        $banned_host_ip = $request->input('banned_host_ip');
        $comments = $request->input('comments', '');

        if (empty($banned_host_ip)) {
            return errorResponse(Lang::get('lang.banned_empty'), 400);
        }

        $whitelistIpExists = LicenseWhitelistIp::where('whitelist_host_ip', $banned_host_ip)->exists();
        if ($whitelistIpExists) {
            return errorResponse(Lang::get('lang.banned_ip_in_whitelist'), 400);
        }

        $banned = new LicenseBannedHost([
            'banned_host_ip' => $banned_host_ip,
            'comments' => $comments,
        ]);
        $banned->save();

        return successResponse(Lang::get('lang.banned_add'), $banned, 201);
    }

    /**
     *To Edit Banned hosts of License manager.
     *
     * @param  BannedHostRequest  $request
     * @param  $api_key_secret
     * @param  $banned_host_ip
     * @param  $comments
     * @return array of details of edited banned host if Updated successfully
     */
    public function bannedHostUpdate(Request $request)
    {
        $id = $request->get('id');
        $banned_host_ip = $request->get('banned_host_ip');
        $comments = $request->get('comments');

        if (empty($id) || ! LicenseHelper::validateIntegerValue($id) ||
        empty($rows_array = LicenseBannedHost::where('id', $id)->get()->toArray())) { //invalid record
            return errorResponse(Lang::get('lang.banned_host_not_found'), 404);
        }

        if (empty($banned_host_ip)) {
            return errorResponse(Lang::get('lang.banned_empty'), 400);
        }

        $whitelistIpExists = LicenseWhitelistIp::where('whitelist_host_ip', $banned_host_ip)->exists();
        if ($whitelistIpExists) {
            return errorResponse(Lang::get('lang.banned_ip_in_whitelist'), 400);
        }

        $banned = LicenseBannedHost::where('id', $id)->update([
            'banned_host_ip' => $banned_host_ip,
            'comments' => $comments,
        ]);

        return successResponse(Lang::get('lang.banned_edit'), $banned, 201);
    }

    /**
     *To Delete Banned hosts of License manager.
     *
     * @param  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function deleteBannedHost(Request $request)
    {
        $removed_records = 0;
        $id = $request->get('id');
        if (! LicenseHelper::validateIntegerValue($id)) {
            return errorResponse(Lang::get('lang.banned_empty'), 400);
        }

        $removed_records += LicenseBannedHost::where('id', $id)->delete();

        return successResponse(Lang::get('lang.delete'), $removed_records, 201);
    }

    /**
     * Returns the list of all the banned host present for this application.
     */
    public function show(Request $request)
    {
        $perPage = $request->input('perPage', 10); // Number of items per page
        $page = $request->input('page', 1); // Get the current page from the request
        $searchQuery = $request->input('search_query');
        $sortOrder = $request->input('sort_order', 'desc');
        $sortField = $request->input('sort_field', 'id');

        $banned = LicenseBannedHost::where(function (Builder $query) use ($searchQuery): void {
            $query->where('banned_host_ip', 'LIKE', '%'.$searchQuery.'%')
                ->orWhere('comments', 'LIKE', '%'.$searchQuery.'%');
        })
        ->orderBy($sortField, $sortOrder)
        ->paginate($perPage, ['*'], 'page', $page);

        $banned->getCollection()->transform(function ($host) {
            $host->banned_host_date = $host->created_at ? $host->created_at->format('Y-m-d') : '';

            return $host;
        });

        return successResponse(Lang::get('lang.Banned_Show'), $banned, 200);
    }

    public function view($id)
    {
        $banned_host_data = LicenseBannedHost::where('id', $id)->firstOrFail();

        if (! empty($banned_host_data)) {
            return successResponse('', ['banned_host_data' => $banned_host_data], 200);
        }

        return errorResponse(Lang::get('lang.invalid'), 400);
    }
}
