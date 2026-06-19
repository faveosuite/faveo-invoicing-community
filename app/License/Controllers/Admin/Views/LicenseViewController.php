<?php

namespace App\License\Controllers\Admin\Views;

use App\Http\Controllers\Controller;
use App\License\Helpers\LicenseHelper;
use App\License\Models\Installation;
use App\License\Models\InstallationLog;
use App\License\Models\License;
use App\License\Models\LicenseCallback;
use Illuminate\Contracts\Database\Query\Builder;
use Illuminate\Http\Request;

class LicenseViewController extends Controller
{
    public function getLicenseDetails(mixed $license_id): \Illuminate\Http\JsonResponse
    {
        $license = License::with(['product:id,name', 'user:id,email'])
            ->withCount(['installations as installation_counts', 'callbacks as call_backs_count'])
            ->withMax('callbacks as latest_call_backs', 'callback_date_time')
            ->find($license_id);

        if (! $license) {
            return successResponse(__('lang.license_details'), data: null);
        }

        $formatted = (object) [
            'id' => $license->id,
            'product_id' => $license->product_id,
            'client_id' => $license->user_id,
            'license_ip' => $license->license_ip,
            'license_code' => $license->license_code,
            'license_limit' => $license->license_limit,
            'license_expire_date' => $license->license_expire_date,
            'license_support_date' => $license->license_support_date,
            'license_order_number' => $license->license_order_number,
            'license_domain' => $license->license_domain,
            'license_date' => $license->license_date,
            'license_updates_date' => $license->license_updates_date,
            'license_status' => $license->license_status,
            'product_title' => $license->product->name,
            'client_email' => $license->user?->email,
            'license_order_url' => $license->license_order_number ?? '',
            'installation_counts' => $license->installation_counts, // @phpstan-ignore property.notFound
            'latest_call_backs' => $license->latest_call_backs, // @phpstan-ignore property.notFound
            'call_backs_count' => $license->call_backs_count,
        ];

        return successResponse(__('lang.license_details'), (array) $formatted);
    }

    public function getLicenseInstallations(Request $request, mixed $license_id): \Illuminate\Http\JsonResponse
    {
        $perPage = $request->input('perPage', 10);
        $page = $request->input('page', 1);
        $searchQuery = $request->input('search_query');
        $sortOrder = $request->input('sort_order', 'desc');
        $sortField = $request->input('sort_field', 'id');

        $license = License::query()->select('id', 'user_id as client_id', 'license_code')->find($license_id);
        if (! $license) {
            return successResponse(__('lang.license_installations'), collect([]));
        }

        $licenseInstallations = Installation::query()
            ->select('id', 'user_id as client_id', 'installation_domain', 'installation_ip', 'installation_date', 'installation_status')
            ->where('license_code', $license->license_code)
            ->when($license->client_id, fn ($query) => $query->where('user_id', $license->client_id)) // @phpstan-ignore property.notFound, property.notFound
            ->when($searchQuery, function ($query) use ($searchQuery): void {
                $query->where(function (Builder $q) use ($searchQuery): void {
                    $q->where('installation_domain', 'LIKE', '%'.$searchQuery.'%')
                        ->orWhere('installation_status', 'LIKE', '%'.LicenseHelper::statusFormatter($searchQuery).'%')
                        ->orWhere('installation_date', 'LIKE', '%'.$searchQuery.'%');
                });
            })
            ->orderBy($sortField, $sortOrder)
            ->paginate($perPage, ['*'], 'page', $page);

        return successResponse(__('lang.license_installations'), $licenseInstallations);
    }

    public function getLicenseCallBacks(Request $request, mixed $license_id): \Illuminate\Http\JsonResponse
    {
        $perPage = $request->input('perPage', 10);
        $page = $request->input('page', 1);
        $searchQuery = $request->input('search_query');
        $sortOrder = $request->input('sort_order', 'desc');
        $sortField = $request->input('sort_field', 'id');

        $license = License::query()->select('id', 'user_id as client_id', 'license_code')->find($license_id);
        if (! $license) {
            return successResponse(__('lang.license_callback'), collect([]));
        }

        $licenseCallBacks = LicenseCallback::where('user_id', $license->client_id) // @phpstan-ignore property.notFound
            ->where('license_code', $license->license_code)
            ->when($searchQuery, function ($query) use ($searchQuery): void {
                $query->where(function ($q) use ($searchQuery): void {
                    $q->where('callback_domain', 'LIKE', '%'.$searchQuery.'%')
                        ->orWhere('callback_status', 'LIKE', '%'.LicenseHelper::successErrorFormatter($searchQuery).'%')
                        ->orWhere('callback_date_time', 'LIKE', '%'.$searchQuery.'%');
                });
            })
            ->orderBy($sortField, $sortOrder)
            ->paginate($perPage, ['*'], 'page', $page);

        $licenseCallBacks->getCollection()->transform(fn (LicenseCallback $cb): array => [
            'id' => $cb->id,
            'callback_domain' => $cb->callback_domain,
            'callback_ip' => $cb->callback_ip,
            'callback_date_time' => $cb->callback_date_time,
            'callback_status' => $cb->callback_status,
        ]);

        return successResponse(__('lang.license_callback'), $licenseCallBacks);
    }

    public function getLicenseInstallationLogs(Request $request, mixed $license_id): \Illuminate\Http\JsonResponse
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
            ->when($searchQuery, function ($query) use ($searchQuery): void {
                $query->where('installation_domain', 'LIKE', '%'.$searchQuery.'%')
                    ->orWhere('installation_ip', 'LIKE', '%'.$searchQuery.'%');
            })
            ->orderBy($sortField, $sortOrder)
            ->paginate($perPage, ['*'], 'page', $page);

        return successResponse('', $installationLogs);
    }
}
