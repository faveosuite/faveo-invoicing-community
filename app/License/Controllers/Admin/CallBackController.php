<?php

namespace App\License\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\License\Helpers\LicenseHelper;
use App\License\Models\License;
use App\License\Models\LicenseCallback;
use App\License\Models\VersionCallback;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Lang;

class CallBackController extends Controller
{
    public function licneseCallbacks(Request $request)
    {
        $perPage = $request->input('perPage', 10);
        $page = $request->input('page', 1);
        $searchQuery = str_replace('-', '', $request->input('search_query'));
        $sortOrder = $request->input('sort_order', 'desc');
        $sortField = $request->input('sort_field', 'id');
        $allowedSortFields = ['id', 'product_id', 'user_id', 'license_code', 'callback_domain', 'callback_ip', 'callback_date_time', 'callback_status'];
        $sortField = in_array($sortField, $allowedSortFields, true) ? $sortField : 'id';
        $sortOrder = strtolower($sortOrder) === 'asc' ? 'asc' : 'desc';

        $paginatedCallbacks = LicenseCallback::query()
            ->with(['product:id,name', 'user:id,email'])
            ->when($searchQuery, function ($query) use ($searchQuery) {
                $query->where(function ($query) use ($searchQuery) {
                    $query->whereHas('product', function ($productQuery) use ($searchQuery) {
                        $productQuery->where('name', 'LIKE', '%'.$searchQuery.'%');
                    })->orWhereHas('user', function ($userQuery) use ($searchQuery) {
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

        $paginatedCallbacks->getCollection()->transform(function (LicenseCallback $callback) use ($licenseIdsByCode) {
            return [
                'id' => $callback->id,
                'product_id' => $callback->product_id,
                'user_id' => $callback->user_id,
                'license_code' => $callback->license_code,
                'callback_domain' => $callback->callback_domain,
                'callback_ip' => $callback->callback_ip,
                'callback_date_time' => $callback->callback_date_time,
                'callback_status' => $callback->callback_status,
                'product_title' => optional($callback->product)->name,
                'client_email' => optional($callback->user)->email,
                'license_id' => $licenseIdsByCode[$callback->license_code] ?? null,
            ];
        });

        return successResponse(Lang::get('lang.Callback_Show'), $paginatedCallbacks, 200);
    }

    public function updateCallbacks(Request $request)
    {
        $perPage = $request->input('perPage', 10); // Number of items per page
        $page = $request->input('page', 1); // Get the current page from the request
        $searchQuery = str_replace('-', '', $request->input('search_query'));
        $sortOrder = $request->input('sort_order', 'desc');
        $sortField = $request->input('sort_field', 'id');
        $allowedSortFields = ['id', 'version_id', 'callback_ip', 'callback_type', 'callback_date_time', 'callback_status'];
        $sortField = in_array($sortField, $allowedSortFields, true) ? $sortField : 'id';
        $sortOrder = strtolower($sortOrder) === 'asc' ? 'asc' : 'desc';

        $updateCallbacks = VersionCallback::query()
            ->with(['version:id,product_id,version', 'version.product:id,name'])
            ->when($searchQuery, function ($query) use ($searchQuery) {
                $query->where(function ($query) use ($searchQuery) {
                    $query->whereHas('version.product', function ($productQuery) use ($searchQuery) {
                        $productQuery->where('name', 'LIKE', '%'.$searchQuery.'%');
                    })->orWhereHas('version', function ($versionQuery) use ($searchQuery) {
                        $versionQuery->where('version', 'LIKE', '%'.$searchQuery.'%');
                    })->orWhere('callback_type', 'LIKE', '%'.$searchQuery.'%')
                        ->orWhere('callback_ip', 'LIKE', '%'.$searchQuery.'%')
                        ->orWhere('callback_status', 'LIKE', '%'.LicenseHelper::statusFormatter($searchQuery).'%')
                        ->orWhere('callback_date_time', 'LIKE', '%'.$searchQuery.'%');
                });
            })
            ->orderBy($sortField, $sortOrder)
            ->paginate($perPage, ['*'], 'page', $page);

        $updateCallbacks->getCollection()->transform(function (VersionCallback $callback) {
            $version = $callback->version;
            $product = optional($version)->product;

            return [
                'id' => $callback->id,
                'version_id' => $callback->version_id,
                'callback_ip' => $callback->callback_ip,
                'callback_types' => $callback->callback_type,
                'callback_date_time' => $callback->callback_date_time,
                'callback_status' => $callback->callback_status,
                'product_title' => optional($product)->name,
                'product_id' => optional($product)->id,
                'version_number' => optional($version)->version,
            ];
        });

        return successResponse('', $updateCallbacks);
    }

    //format and return callback type text
    private function returnFormattedCallbackTypeArray($callback_type)
    {
        $callback_type_formatted = '';

        if ($callback_type == 1) {
            $callback_type_formatted = 'Version Check';
        } elseif ($callback_type == 2) {
            $callback_type_formatted = 'Installation';
        } elseif ($callback_type == 3) {
            $callback_type_formatted = 'Upgrade';
        } else {
            $callback_type_formatted = 'Unknown';
        }

        return $callback_type_formatted;
    }

    public function callbacksDelete(Request $request)
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
        if ($action_success == 1) { //everything OK
            $page_message = "Deleted $removed_records callback(s).";
        } else { //display error message
            $page_message = "Callback could not be deleted because of this reason: $error_details";
        }

        LicenseHelper::logAdminReport(strip_tags($page_message), 1, 1, $action_success);

        return successResponse($page_message, $removed_records, 200);
    }

    //delete callback
    private function deleteCallback($callback_id, $isLicense)
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
