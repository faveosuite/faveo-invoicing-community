<?php

namespace App\Modules\License\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Modules\License\Models\LicenseCallback;
use App\Model\Product\Product;
use App\Modules\License\Models\VersionCallback;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Lang;

class CallBackController extends Controller
{
    public function licneseCallbacks(Request $request)
    {
        $perPage = $request->input('perPage',10);
        $page = $request->input('page', 1);
        $searchQuery = str_replace("-","",$request->input('search_query'));
        $sortOrder= $request->input('sort_order','desc');
        $sortField =$request->input('sort_field','id');
        $paginatedCallbacks = DB::table('license_callbacks')
            ->selectRaw('license_callbacks.id, license_callbacks.product_id, license_callbacks.user_id, license_callbacks.license_code, license_callbacks.callback_domain, license_callbacks.callback_ip, license_callbacks.callback_date_time, license_callbacks.callback_status, products.name as product_title, products.id as product_id, users.email as client_email, licenses.id as license_id')
            ->leftJoin('products', 'license_callbacks.product_id', '=', 'products.id')
            ->leftJoin('users', 'license_callbacks.user_id', '=', 'users.id')
            ->leftJoin('licenses', 'license_callbacks.license_code', '=', 'licenses.license_code')
            ->when($searchQuery,function ($query) use ($searchQuery) {
                $query->where(function ($query) use ($searchQuery) {
                    $query->where('products.name', 'LIKE', '%' . $searchQuery . '%')
                        ->orWhere('users.email', 'LIKE', '%' . $searchQuery . '%')
                        ->orWhere('license_callbacks.license_code', 'LIKE', '%' . $searchQuery . '%')
                        ->orWhere('license_callbacks.callback_ip', 'LIKE', '%' . $searchQuery . '%')
                        ->orWhere('license_callbacks.callback_status', 'LIKE', '%' . statusFormatter($searchQuery) . '%')
                        ->orWhere('license_callbacks.callback_domain', 'LIKE', '%' . $searchQuery . '%');
                });
            })
            ->orderBy($sortField, $sortOrder)
            ->paginate($perPage, ['*'], 'page', $page);

        return successResponse(Lang::get('lang.Callback_Show'),$paginatedCallbacks,200);
    }

    public function updateCallbacks(Request $request)
    {
        $perPage = $request->input('perPage',10); // Number of items per page
        $page = $request->input('page', 1); // Get the current page from the request
        $searchQuery = str_replace("-","",$request->input('search_query'));
        $sortOrder= $request->input('sort_order','desc');
        $sortField =$request->input('sort_field','id');
       $updateCallbacks = DB::table('version_callbacks')
           ->selectRaw('version_callbacks.id, version_callbacks.version_id, version_callbacks.callback_ip, version_callbacks.callback_type as callback_types, version_callbacks.callback_date_time, version_callbacks.callback_status, products.name as product_title, products.id as product_id, product_versions.version_number')
           ->leftJoin('product_versions', 'version_callbacks.version_id', '=', 'product_versions.version_id')
           ->leftJoin('products', 'product_versions.product_id', '=', 'products.id')
           ->when($searchQuery,function ($query) use ($searchQuery) {
               $query->where(function ($query) use ($searchQuery) {
                   $query->where('products.name', 'LIKE', '%' . $searchQuery . '%')
                       ->orWhere('product_versions.version_number', 'LIKE', '%' . $searchQuery . '%')
                       ->orWhere('version_callbacks.callback_type', 'LIKE', '%' . $searchQuery . '%')
                       ->orWhere('version_callbacks.callback_ip', 'LIKE', '%' . $searchQuery . '%')
                       ->orWhere('version_callbacks.callback_status', 'LIKE', '%' . statusFormatter($searchQuery) . '%')
                       ->orWhere('version_callbacks.callback_date_time', 'LIKE', '%' . $searchQuery . '%');
               });
           })
           ->orderBy($sortField, $sortOrder)
           ->paginate($perPage, ['*'], 'page', $page);
       return successResponse('',$updateCallbacks);
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
            if (! aflValidateIntegerValue($removed_records)) {
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

        createReport(strip_tags($page_message), 1, 1, $action_success);

        return successResponse($page_message, $removed_records, 200);
    }

    //delete callback
    private function deleteCallback($callback_id, $isLicense)
    {
        $removed_records = 0;
        if ($isLicense) {
            if (aflValidateIntegerValue($callback_id)) {
                $removed_records += LicenseCallback::where('license_callbacks.product_id', $callback_id)->delete(); //doMysqlQuery("DELETE FROM apl_callbacks WHERE license_callbacks.product_id=?", array($callback_id), array("i"));
            }

            return $removed_records;
        }
        if (aflValidateIntegerValue($callback_id)) {
            $removed_records += VersionCallback::where('license_callbacks.product_id', $callback_id)->delete();
        }

        return $removed_records;
    }
}
