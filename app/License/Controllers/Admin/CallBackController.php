<?php

namespace App\License\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\License\Helpers\LicenseHelper;
use App\License\Models\License;
use App\License\Models\LicenseCallback;
use App\License\Models\VersionCallback;
use Illuminate\Contracts\Database\Query\Builder;
use Illuminate\Http\Request;

class CallBackController extends Controller
{
    public function licneseCallbacks(Request $request): \Illuminate\Http\JsonResponse
    {
        $perPage = $request->input('perPage', 10);
        $page = $request->input('page', 1);
        $searchQuery = str_replace('-', '', $request->input('search_query'));
        $sortOrder = $request->input('sort_order', 'desc');
        $sortField = $request->input('sort_field', 'id');
        $allowedSortFields = ['id', 'product_id', 'user_id', 'license_code', 'callback_domain', 'callback_ip', 'callback_date_time', 'callback_status'];
        $sortField = in_array($sortField, $allowedSortFields, strict: true) ? $sortField : 'id';
        $sortOrder = strtolower((string) $sortOrder) === 'asc' ? 'asc' : 'desc';

        $paginatedCallbacks = LicenseCallback::query()
            ->with(['product:id,name', 'user:id,email'])
            ->when($searchQuery, function ($query) use ($searchQuery): void {
                $query->where(function (Builder $query) use ($searchQuery): void {
                    $query->whereHas('product', function (Builder $productQuery) use ($searchQuery): void {
                        $productQuery->where('name', 'LIKE', '%'.$searchQuery.'%');
                    })->orWhereHas('user', function (Builder $userQuery) use ($searchQuery): void {
                        $userQuery->where('email', 'LIKE', '%'.$searchQuery.'%');
                    })->orWhere('license_code', 'LIKE', '%'.$searchQuery.'%')
                        ->orWhere('callback_ip', 'LIKE', '%'.$searchQuery.'%')
                        ->orWhere('callback_status', 'LIKE', '%'.LicenseHelper::statusFormatter($searchQuery).'%')
                        ->orWhere('callback_domain', 'LIKE', '%'.$searchQuery.'%');
                });
            })
            ->orderBy($sortField, $sortOrder)
            ->paginate($perPage, ['*'], 'page', $page);

        $licenseIdsByCode = License::whereIn('license_code', $paginatedCallbacks->pluck('license_code')->filter()->unique())
            ->pluck('id', 'license_code');

        $paginatedCallbacks->getCollection()->transform(fn (LicenseCallback $callback): array => [
            'id' => $callback->id,
            'product_id' => $callback->product_id,
            'user_id' => $callback->user_id,
            'license_code' => $callback->license_code,
            'callback_domain' => $callback->callback_domain,
            'callback_ip' => $callback->callback_ip,
            'callback_date_time' => $callback->callback_date_time,
            'callback_status' => $callback->callback_status,
            'product_title' => $callback->product?->name,
            'client_email' => $callback->user?->email,
            'license_id' => $licenseIdsByCode[$callback->license_code] ?? null,
        ]);

        return successResponse(__('lang.Callback_Show'), $paginatedCallbacks, 200);
    }

    public function updateCallbacks(Request $request): \Illuminate\Http\JsonResponse
    {
        $perPage = $request->input('perPage', 10); // Number of items per page
        $page = $request->input('page', 1); // Get the current page from the request
        $searchQuery = str_replace('-', '', $request->input('search_query'));
        $sortOrder = $request->input('sort_order', 'desc');
        $sortField = $request->input('sort_field', 'id');
        $allowedSortFields = ['id', 'version_id', 'callback_ip', 'callback_type', 'callback_date_time', 'callback_status'];
        $sortField = in_array($sortField, $allowedSortFields, strict: true) ? $sortField : 'id';
        $sortOrder = strtolower((string) $sortOrder) === 'asc' ? 'asc' : 'desc';

        $updateCallbacks = VersionCallback::query()
            ->with(['version:id,product_id,version', 'version.product:id,name'])
            ->when($searchQuery, function ($query) use ($searchQuery): void {
                $query->where(function (Builder $query) use ($searchQuery): void {
                    $query->whereHas('version.product', function (Builder $productQuery) use ($searchQuery): void {
                        $productQuery->where('name', 'LIKE', '%'.$searchQuery.'%');
                    })->orWhereHas('version', function (Builder $versionQuery) use ($searchQuery): void {
                        $versionQuery->where('version', 'LIKE', '%'.$searchQuery.'%');
                    })->orWhere('callback_type', 'LIKE', '%'.$searchQuery.'%')
                        ->orWhere('callback_ip', 'LIKE', '%'.$searchQuery.'%')
                        ->orWhere('callback_status', 'LIKE', '%'.LicenseHelper::statusFormatter($searchQuery).'%')
                        ->orWhere('callback_date_time', 'LIKE', '%'.$searchQuery.'%');
                });
            })
            ->orderBy($sortField, $sortOrder)
            ->paginate($perPage, ['*'], 'page', $page);

        $updateCallbacks->getCollection()->transform(function (VersionCallback $callback): array {
            $version = $callback->version;
            $product = $version?->product;

            return [
                'id' => $callback->id,
                'version_id' => $callback->version_id,
                'callback_ip' => $callback->callback_ip,
                'callback_types' => $callback->callback_type,
                'callback_date_time' => $callback->callback_date_time,
                'callback_status' => $callback->callback_status,
                'product_title' => $product?->name,
                'product_id' => $product?->id,
                'version_number' => $version?->version,
            ];
        });

        return successResponse('', $updateCallbacks);
    }

    public function callbacksDelete(Request $request): \Illuminate\Http\JsonResponse
    {
        $removed_records = 0;
        $error_details = '';
        $action_success = 0;
        $callback_ids_array = $request->call;
        $isLicense = $request->get('isLicense');
        if (! empty($callback_ids_array)) {
            foreach ($callback_ids_array as $callback_id) {
                $removed_records += $this->deleteCallback($callback_id, $isLicense);
            }

            if (! LicenseHelper::validateIntegerValue($removed_records)) {
                $error_details .= 'Invalid record or database error.';
            } else {
                $action_success = 1;
            }
        } else {
            $error_details .= 'No record selected.';
        }

        if ($action_success === 1) { //everything OK
            $page_message = sprintf('Deleted %s callback(s).', $removed_records);
        } else { //display error message
            $page_message = 'Callback could not be deleted because of this reason: '.$error_details;
        }

        LicenseHelper::logAdminReport(strip_tags($page_message), 1, 1, $action_success);

        return successResponse($page_message, $removed_records, 200);
    }

    //delete callback
    private function deleteCallback(mixed $callback_id, mixed $isLicense): int|float
    {
        $removed_records = 0;
        if ($isLicense) {
            if (LicenseHelper::validateIntegerValue($callback_id)) {
                $removed_records += LicenseCallback::where('license_callbacks.product_id', $callback_id)->delete(); //doMysqlQuery("DELETE FROM apl_callbacks WHERE license_callbacks.product_id=?", array($callback_id), array("i"));
            }

            return $removed_records;
        }

        if (LicenseHelper::validateIntegerValue($callback_id)) {
            $removed_records += VersionCallback::where('product_id', $callback_id)->delete();
        }

        return $removed_records;
    }
}
