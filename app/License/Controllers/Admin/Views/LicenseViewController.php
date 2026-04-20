<?php

namespace App\License\Controllers\Admin\Views;

use App\Http\Controllers\Controller;
use App\License\Helpers\LicenseHelper;
use App\License\Models\Installation;
use App\License\Models\InstallationLog;
use App\License\Models\License;
use App\License\Models\LicenseCallback;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Lang;

class LicenseViewController extends Controller
{
    public function getLicenseDetails($license_id)
    {
        $license = License::with(['product:id,name', 'user:id,email'])
            ->withCount(['installations as installation_counts', 'callbacks as call_backs_count'])
            ->withMax('callbacks as latest_call_backs', 'callback_date_time')
            ->find($license_id);

        if (! $license) {
            return successResponse(Lang::get('lang.license_details'), null);
        }

        $formatted = (object) [
            'id' => $license->id,
            'product_id' => $license->product_id,
            'client_id' => $license->user_id,
            'license_ip' => $license->license_ip,
            'license_code' => $license->license_code,
            'license_limit' => $license->license_limit,
            'license_expire_date' => LicenseHelper::formatDate($license->license_expire_date),
            'license_support_date' => LicenseHelper::formatDate($license->license_support_date),
            'license_order_number' => $license->license_order_number,
            'license_domain' => $license->license_domain,
            'license_date' => LicenseHelper::formatDatetime($license->license_date),
            'license_updates_date' => LicenseHelper::formatDate($license->license_updates_date),
            'license_status' => $license->license_status,
            'product_title' => optional($license->product)->name,
            'client_email' => optional($license->user)->email,
            'license_order_url' => $license->license_order_number ?? '',
            'installation_counts' => $license->installation_counts,
            'latest_call_backs' => LicenseHelper::formatDatetime($license->latest_call_backs),
            'call_backs_count' => $license->call_backs_count,
        ];

        return successResponse(Lang::get('lang.license_details'), $formatted);
    }

    public function getLicenseInstallations(Request $request, $license_id)
    {
        $perPage = $request->input('perPage', 10);
        $page = $request->input('page', 1);
        $searchQuery = $request->input('search_query');
        $sortOrder = $request->input('sort_order', 'desc');
        $sortField = $request->input('sort_field', 'id');

        $license = License::query()->select('id', 'user_id as client_id', 'license_code')->find($license_id);
        if (! $license) {
            return successResponse(Lang::get('lang.license_installations'), collect([]));
        }

        $licenseInstallations = Installation::query()
            ->select('id', 'user_id as client_id', 'installation_domain', 'installation_ip', 'installation_date', 'installation_status')
            ->where('license_code', $license->license_code)
            ->when($license->client_id, fn ($query) => $query->where('user_id', $license->client_id))
            ->when($searchQuery, function ($query) use ($searchQuery) {
                $query->where(function ($q) use ($searchQuery) {
                    $q->where('installation_domain', 'LIKE', '%'.$searchQuery.'%')
                        ->orWhere('installation_status', 'LIKE', '%'.LicenseHelper::statusFormatter($searchQuery).'%')
                        ->orWhere('installation_date', 'LIKE', '%'.$searchQuery.'%');
                });
            })
            ->orderBy($sortField, $sortOrder)
            ->paginate($perPage, ['*'], 'page', $page);

        return successResponse(Lang::get('lang.license_installations'), $licenseInstallations);
    }

    public function getLicenseCallBacks(Request $request, $license_id)
    {
        $perPage = $request->input('perPage', 10);
        $page = $request->input('page', 1);
        $searchQuery = $request->input('search_query');
        $sortOrder = $request->input('sort_order', 'desc');
        $sortField = $request->input('sort_field', 'id');

        $license = License::query()->select('id', 'user_id as client_id', 'license_code')->find($license_id);
        if (! $license) {
            return successResponse(Lang::get('lang.license_callback'), collect([]));
        }

        $licenseCallBacks = LicenseCallback::where('user_id', $license->client_id)
            ->where('license_code', $license->license_code)
            ->when($searchQuery, function ($query) use ($searchQuery) {
                $query->where(function ($q) use ($searchQuery) {
                    $q->where('callback_domain', 'LIKE', '%'.$searchQuery.'%')
                        ->orWhere('callback_status', 'LIKE', '%'.LicenseHelper::successErrorFormatter($searchQuery).'%')
                        ->orWhere('callback_date_time', 'LIKE', '%'.$searchQuery.'%');
                });
            })
            ->orderBy($sortField, $sortOrder)
            ->paginate($perPage, ['*'], 'page', $page);

        $licenseCallBacks->getCollection()->transform(fn (LicenseCallback $cb) => [
            'id' => $cb->id,
            'callback_domain' => $cb->callback_domain,
            'callback_ip' => $cb->callback_ip,
            'callback_date_time' => LicenseHelper::formatDatetime($cb->callback_date_time),
            'callback_status' => $cb->callback_status,
        ]);

        return successResponse(Lang::get('lang.license_callback'), $licenseCallBacks);
    }

    public function getLicenseInstallationLogs(Request $request, $license_id)
    {
        $perPage = $request->input('perPage', 10);
        $page = $request->input('page', 1);
        $searchQuery = $request->input('search_query');
        $sortOrder = $request->input('sort_order', 'desc');
        $sortField = $request->input('sort_field', 'installation_last_active_date');

        $license = License::query()->select('id', 'license_code')->find($license_id);
        if (! $license) {
            return successResponse('', collect([]));
        }

        $installationLogs = InstallationLog::where('license_code', $license->license_code)
            ->when($searchQuery, function ($query) use ($searchQuery) {
                $query->where('installation_domain', 'LIKE', '%'.$searchQuery.'%')
                    ->orWhere('installation_ip', 'LIKE', '%'.$searchQuery.'%');
            })
            ->orderBy($sortField, $sortOrder)
            ->paginate($perPage, ['*'], 'page', $page);

        return successResponse('', $installationLogs);
    }
}
