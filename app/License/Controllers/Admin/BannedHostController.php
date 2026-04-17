<?php

namespace App\License\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\BannedHostRequest;
use App\License\Helpers\LicenseHelper;
use App\License\Models\LicenseBannedHost;
use App\License\Models\LicenseWhitelistIp;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Lang;

/**
 * Consist of functionalities for the Banned Host page in Auto Faveo licenser
 * Class BannedHostController.
 */
class BannedHostController extends Controller
{
    public function __construct(Request $request)
    {
        $this->ip_address = request()->server('REMOTE_ADDR');
    }

    /**
     *To Add Banned hosts of License manager.
     *
     * @param  BannedHostRequest  $request
     * @param  $api_key_secret
     * @param  $banned_host_ip
     * @param  $comments
     * @return array of details of banned host if added successfully
     */
    public function bannedHostAdd(BannedHostRequest $request)
    {
        $api_key_secret = $request->input('api_key_secret');
        $banned_host_ip = $request->input('banned_host_ip');
        $comments = $request->input('comments', '');

        $api_key = new ApiKeysController();
        $api_action_success = $api_key->apiKeyCheck($api_key_secret, $this->ip_address);
        if (empty($banned_host_ip) || $api_action_success != 1) {
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
        $api_key_secret = $request->get('api_key_secret');
        $banned_host_ip = $request->get('banned_host_ip');
        $comments = $request->get('comments');

        if (empty($id) || ! LicenseHelper::validateIntegerValue($id) ||
        empty($rows_array = LicenseBannedHost::where('id', $id)->get()->toArray())) { //invalid record
            return errorResponse(Lang::get('lang.banned_host_not_found'), 404);
        }
        $api_key = new ApiKeysController();
        $api_action_success = $api_key->apiKeyCheck($api_key_secret, $this->ip_address);
        if (empty($banned_host_ip) || $api_action_success != 1) {
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
     * @return success response of how many records deleted if deleted successfully
     */
    public function deleteBannedHost(Request $request)
    {
        $api_key_secret = $request->get('api_key_secret');
        $removed_records = 0;
        $id = $request->get('id');
        $api_key = new ApiKeysController();
        $api_action_success = $api_key->apiKeyCheck($api_key_secret, $this->ip_address);
        if ($api_action_success != 1 || ! LicenseHelper::validateIntegerValue($id)) {
            return errorResponse(Lang::get('lang.banned_empty'), 400);
        }
        $banned_ip = DB::table('license_banned_hosts')
                      ->where('id', $id)
                      ->value('banned_host_ip');

        DB::table('license_failed_logins')
                        ->where('failed_login_ip', $banned_ip)
                        ->delete();
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

        $banned = LicenseBannedHost::where(function ($query) use ($searchQuery) {
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
