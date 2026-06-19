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
    protected string $ip_address;

    public function __construct()
    {
        $addr = request()->server('REMOTE_ADDR'); $this->ip_address = is_string($addr) ? $addr : '';
    }

    /**
     *To Add Banned hosts of License manager.
     *
     * @param  $api_key_secret
     * @param  $banned_host_ip
     * @param  $comments
     */
    public function bannedHostAdd(BannedHostRequest $request): \Illuminate\Http\JsonResponse
    {
        $banned_host_ip = $request->input('banned_host_ip');
        $comments = $request->input('comments', '');

        if (empty($banned_host_ip)) {
            return errorResponse(__('lang.banned_empty'), 400);
        }

        $whitelistIpExists = LicenseWhitelistIp::where('whitelist_host_ip', $banned_host_ip)->exists();
        if ($whitelistIpExists) {
            return errorResponse(__('lang.banned_ip_in_whitelist'), 400);
        }

        $banned = new LicenseBannedHost([
            'banned_host_ip' => $banned_host_ip,
            'comments' => $comments,
        ]);
        $banned->save();

        return successResponse(__('lang.banned_add'), $banned, 201);
    }

    /**
     *To Edit Banned hosts of License manager.
     *
     * @param  BannedHostRequest  $request
     * @param  $api_key_secret
     * @param  $banned_host_ip
     * @param  $comments
     */
    public function bannedHostUpdate(Request $request): \Illuminate\Http\JsonResponse
    {
        $id = $request->get('id');
        $banned_host_ip = $request->get('banned_host_ip');
        $comments = $request->get('comments');

        if (empty($id) || ! LicenseHelper::validateIntegerValue($id) ||
        empty($rows_array = LicenseBannedHost::where('id', $id)->get()->toArray())) { //invalid record
            return errorResponse(__('lang.banned_host_not_found'), 404);
        }

        if (empty($banned_host_ip)) {
            return errorResponse(__('lang.banned_empty'), 400);
        }

        $whitelistIpExists = LicenseWhitelistIp::where('whitelist_host_ip', $banned_host_ip)->exists();
        if ($whitelistIpExists) {
            return errorResponse(__('lang.banned_ip_in_whitelist'), 400);
        }

        $banned = LicenseBannedHost::where('id', $id)->update([
            'banned_host_ip' => $banned_host_ip,
            'comments' => $comments,
        ]);

        return successResponse(__('lang.banned_edit'), $banned, 201);
    }

    /**
     *To Delete Banned hosts of License manager.
     *
     * @param  $id
     */
    public function deleteBannedHost(Request $request): \Illuminate\Http\JsonResponse
    {
        $removed_records = 0;
        $id = $request->get('id');
        if (! LicenseHelper::validateIntegerValue($id)) {
            return errorResponse(__('lang.banned_empty'), 400);
        }

        $removed_records += LicenseBannedHost::where('id', $id)->delete();

        return successResponse(__('lang.delete'), $removed_records, 201);
    }

    /**
     * Returns the list of all the banned host present for this application.
     */
    public function show(Request $request): \Illuminate\Http\JsonResponse
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
            $host->banned_host_date = $host->created_at ? $host->created_at->format('Y-m-d') : ''; // @phpstan-ignore property.notFound

            return $host;
        });

        return successResponse(__('lang.Banned_Show'), $banned, 200);
    }

    public function view(mixed $id): \Illuminate\Http\JsonResponse
    {
        $banned_host_data = LicenseBannedHost::where('id', $id)->firstOrFail();

        return successResponse('', ['banned_host_data' => $banned_host_data], 200);
    }
}
