<?php

namespace App\License\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\License\Helpers\LicenseHelper;
use App\License\Models\LicenseBannedHost;
use App\License\Models\LicenseSecuritySetting;
use App\License\Requests\BannedHostRequest;
use App\License\Requests\SecuritySettingsRequest;
use Illuminate\Contracts\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Consist of functionalities for the Banned Host page in Auto Faveo licenser
 * Class BannedHostController.
 */
class BannedHostController extends Controller
{
    protected string $ip_address;

    public function __construct()
    {
        $addr = request()->server('REMOTE_ADDR');
        $this->ip_address = is_string($addr) ? $addr : '';
    }

    /**
     *To Add Banned hosts of License manager.
     *
     * @param  $api_key_secret
     * @param  $banned_host_ip
     * @param  $comments
     */
    public function bannedHostAdd(BannedHostRequest $request): JsonResponse
    {
        $banned_host_ip = $request->input('banned_host_ip');
        $comments = $request->input('comments', '');

        if (empty($banned_host_ip)) {
            return errorResponse(__('license::lang.banned_empty'), 400);
        }

        $banned = new LicenseBannedHost([
            'banned_host_ip' => $banned_host_ip,
            'comments' => $comments,
        ]);
        $banned->save();

        return successResponse(__('license::lang.banned_add'), $banned, 201);
    }

    /**
     *To Edit Banned hosts of License manager.
     *
     * @param  BannedHostRequest  $request
     * @param  $api_key_secret
     * @param  $banned_host_ip
     * @param  $comments
     */
    public function bannedHostUpdate(BannedHostRequest $request): JsonResponse
    {
        $id = $request->input('id');
        $banned_host_ip = $request->input('banned_host_ip');
        $comments = $request->input('comments');

        if (empty($id) || ! LicenseHelper::validateIntegerValue($id) ||
        empty(LicenseBannedHost::where('id', $id)->get()->toArray())) { // invalid record
            return errorResponse(__('license::lang.banned_host_not_found'), 404);
        }

        $banned = LicenseBannedHost::where('id', $id)->update([
            'banned_host_ip' => $banned_host_ip,
            'comments' => $comments,
        ]);

        return successResponse(__('license::lang.banned_edit'), $banned, 201);
    }

    /**
     *To Delete Banned hosts of License manager.
     *
     * @param  $id
     */
    public function deleteBannedHost(Request $request): JsonResponse
    {
        $id = $request->input('id');
        if (! LicenseHelper::validateIntegerValue($id)) {
            return errorResponse(__('license::lang.banned_empty'), 400);
        }

        LicenseBannedHost::where('id', $id)->delete();

        return successResponse(__('license::lang.delete'), statusCode: 201);
    }

    /**
     * Returns the list of all the banned host present for this application.
     */
    public function show(Request $request): JsonResponse
    {
        $perPage = $request->input('limit', $request->input('perPage', 10)); // Number of items per page
        $page = $request->input('page', 1); // Get the current page from the request
        $searchQuery = $request->input('search-query', $request->input('search-query', $request->input('search_query', '')));
        $sortOrder = $request->input('sort-order', $request->input('sort_order', 'desc'));
        $sortField = $request->input('sort-field', $request->input('sort_field', 'id'));

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

        return successResponse(__('license::lang.Banned_Show'), $banned, 200);
    }

    public function view(mixed $id): JsonResponse
    {
        $banned_host_data = LicenseBannedHost::where('id', $id)->firstOrFail();

        return successResponse('', ['banned_host_data' => $banned_host_data], 200);
    }

    /**
     * Get the auto-ban settings: whether it's on, and the failed-attempts threshold.
     */
    public function getSecuritySettings(): JsonResponse
    {
        $settings = LicenseSecuritySetting::find(1);

        return successResponse('', [
            'auto_ban_enabled' => (bool) $settings?->auto_ban_enabled,
            'failed_licensings_limit' => $settings->failed_licensings_limit ?? 0,
        ], 200);
    }

    /**
     * Update the auto-ban settings.
     */
    public function updateSecuritySettings(SecuritySettingsRequest $request): JsonResponse
    {
        $settings = LicenseSecuritySetting::findOrFail(1);
        $settings->update([
            'auto_ban_enabled' => $request->boolean('auto_ban_enabled'),
            'failed_licensings_limit' => (int) $request->input('failed_licensings_limit'),
        ]);

        return successResponse(__('license::lang.security_settings_updated'), [
            'auto_ban_enabled' => $settings->auto_ban_enabled,
            'failed_licensings_limit' => $settings->failed_licensings_limit,
        ], 200);
    }
}
