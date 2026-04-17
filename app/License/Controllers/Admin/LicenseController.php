<?php

namespace App\License\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\License\Models\Installation;
use App\License\Models\InstallationLog;
use App\License\Models\License;
use App\License\Models\LicenseCallback;
use App\License\Models\LicensePlugin;
use App\License\Models\ProductVersion;
use App\License\Requests\LicenseRequest;
use App\Model\Product\Product;
use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Lang;
use Illuminate\Support\Facades\Log;

/**
 * Consist of functionalities for the License page in Auto Faveo licenser
 * Class LicenseController.
 */
class LicenseController extends Controller
{
    public function __construct()
    {
        $this->ip_address = request()->server('REMOTE_ADDR');
    }

    /**
     * To Add license Details to the license manager via request or entering them, it can be added with client id or anonymously.
     *
     * @param  LicenseRequest  $request
     * @param  $api_key_secret
     * @param  $id
     * @param  $license_require_domain
     * @param  $license_status
     * @param  $client_id
     * @param  $license_code
     * @param  $license_order_number
     * @param  $license_ip
     * @param  $license_domain
     * @param  $license_limit
     * @param  $license_expire_date
     * @param  $license_updates_date
     * @param  $license_support_date
     * @param  $license_comments
     * @return the details that has been added with a response
     */
    public function licenseAdd(LicenseRequest $request)
    {
        $api_action_success = 0;
        $api_error_detected = 0;
        $added_records = 0;

        $api_key_secret = $request->get('api_key_secret');
        $id = $request->get('id');
        $license_code = $request->get('license_code');
        $license_require_domain = $request->get('license_require_domain');
        $license_status = $request->get('license_status');
        $client_id = $request->get('client_id');
        $license_order_number = $request->get('license_order_number');
        $license_ip = $request->get('license_ip');
        $license_domain = $request->get('license_domain');
        $license_limit = $request->get('license_limit');
        $license_expire_date = $request->input('license_expire_date');
        $license_updates_date = $request->get('license_updates_date');
        $license_support_date = $request->get('license_support_date');
        $license_comments = $request->get('license_comments');

        if (LicenseHelper::validateIntegerValue($id) && LicenseHelper::validateIntegerValue($license_require_domain, 0, 1) && LicenseHelper::validateIntegerValue($license_status, 0, 2)) {
            if (empty($client_id) || ! LicenseHelper::validateIntegerValue($client_id)) { //in case no client_id was submitted, its value must be stored as NULL in database
                $client_id = null;
            }

            if (empty($license_code)) { //in case no license_code was submitted, its value must be stored as NULL in database
                $license_code = null;
            }
            $licenseChecks = $this->licenseChecks($client_id, $license_code, $license_ip, $license_domain, $license_limit, $license_expire_date, $license_updates_date, $license_support_date);
            if (! empty($licenseChecks)) {
                return $licenseChecks->content();
            }
            if ($api_error_detected != 1) {
                $license_date = date('Y-m-d');
                if (empty($license_envato) || ! LicenseHelper::validateIntegerValue($license_envato)) {
                    $license_envato = 0;
                }

                if ($license_status == 1) {
                    $license_cancel_date = '0000-00-00';
                } else {
                    if (empty($license_cancel_date) || ! LicenseHelper::verifyDateTime($license_cancel_date, 'Y-m-d')) { //set cancel date to now only if license is inactive and no previous cancel date set
                        $license_cancel_date = date('Y-m-d');
                    }
                }
                $license_expire_email_date = $license_expire_date;
                $license_updates_email_date = $license_updates_date;
                $license_support_email_date = $license_support_date;
                try {
                    DB::table('afl_licenses')
                        ->insertOrIgnore([
                            'client_id' => $client_id,
                            'license_code' => $license_code,
                            'id' => $id,
                            'license_order_number' => $license_order_number,
                            'license_ip' => $license_ip,
                            'license_domain' => $license_domain,
                            'license_require_domain' => $license_require_domain,
                            'license_limit' => $license_limit,
                            'license_date' => $license_date,
                            'license_cancel_date' => $license_cancel_date,
                            'license_expire_date' => $license_expire_date,
                            'license_updates_date' => $license_updates_date,
                            'license_support_date' => $license_support_date,
                            'license_expire_email_date' => $license_expire_email_date,
                            'license_updates_email_date' => $license_updates_email_date,
                            'license_support_email_date' => $license_support_email_date,
                            'license_comments' => $license_comments,
                            'license_envato' => $license_envato,
                            'license_status' => $license_status,
                        ]);
                    $added_records += 1;
                    //doMysqlQuery("INSERT IGNORE INTO apl_licenses (client_id, license_code, id, license_order_number, license_ip, license_domain, license_require_domain, license_limit, license_date, license_cancel_date, license_expire_date, license_updates_date, license_support_date, license_comments, license_envato, license_status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)", array($client_id, $license_code, $id, $license_order_number, $license_ip, $license_domain, $license_require_domain, $license_limit, $license_date, $license_cancel_date, $license_expire_date, $license_updates_date, $license_support_date, $license_comments, $license_envato, $license_status), array("i", "s", "i", "s", "s", "s", "i", "i", "s", "s", "s", "s", "s", "s", "i", "i"));
                } catch (\Exception $e) {
                    $added_records += 0;
                }

                if (! LicenseHelper::validateIntegerValue($added_records)) {
                    $api_error_detected = 1;

                    return errorResponse(Lang::get('lang.invalid_record_data'), 400);
                } else {
                    $action_success = 1;
                    $license_id = DB::getpdo()->lastInsertId();
                    if (LicenseHelper::validateIntegerValue($license_id)) {
                        foreach ($rows_array = License::leftJoin('products', 'licenses.product_id', '=', 'products.id')
                            ->leftJoin('users', 'licenses.user_id', '=', 'users.id')
                            ->where('licenses.id', $license_id)
                            ->get(['licenses.*', 'products.name as product_title', 'users.email as client_email'])
                            ->toArray() as $row) {
                            //fetchRow("SELECT * FROM apl_licenses LEFT JOIN apl_products ON apl_licenses.id=apl_products.id LEFT JOIN apl_clients ON apl_licenses.client_id=apl_clients.client_id WHERE apl_licenses.license_id=?", array($license_id), array("i")) as $row) //fetch product and client details to use in reports
                            extract((array) $row);
                        }
                        $client_formatted = LicenseHelper::formatClient($license_code, $client_email);

                        $api_response_array = ['api_action_success' => $api_action_success, 'api_error_detected' => $api_error_detected, 'action_success' => 1, 'error_detected' => 0, 'page_message' => $client_formatted]; //make array with response data

                        return successResponse(Lang::get('lang.adddd'), $client_formatted, 201);
                    }
                }
            }
        } else {
            return errorResponse(Lang::get('lang.invalid'), 400);
        }
    }

    /**
     * To Update license Details to the license manager via request or entering them, it can be added with client id or anonymously.
     *
     * @param  LicenseRequest  $request
     * @param  $api_key_secret
     * @param  $id
     * @param  $license_id
     * @param  $license_require_domain
     * @param  $license_status
     * @param  $client_id
     * @param  $license_code
     * @param  $license_order_number
     * @param  $license_ip
     * @param  $license_domain
     * @param  $license_limit
     * @param  $license_expire_date
     * @param  $license_updates_date
     * @param  $license_support_date
     * @param  $license_comments
     * @return the number of records that has been Updated with a response
     */
    public function licenseUpdate(Request $request)
    {
        $updated_records = 0;

        $api_key_secret = $request->get('api_key_secret');
        $license_id = $request->get('id');
        $id = $request->get('id');
        $license_require_domain = $request->get('license_require_domain');
        $license_status = $request->get('license_status');
        $client_id = $request->get('client_id');
        $license_code = $request->get('license_code');
        $license_order_number = $request->get('license_order_number');
        $license_ip = $request->get('license_ip');
        $license_domain = $request->get('license_domain');
        $license_limit = $request->get('license_limit');
        $license_expire_date = $request->get('license_expire_date');
        $license_updates_date = $request->get('license_updates_date');
        $license_support_date = $request->get('license_support_date');
        $license_comments = $request->get('license_comments');
        if (empty($license_id) || ! LicenseHelper::validateIntegerValue($license_id) || empty($rows_array = License::where('id', $license_id)->get())) {//invalid record
            return errorResponse(Lang::get('lang.license_id'), 400);
        }
        $optional_api_parameters_array = ['license_order_number', 'license_ip', 'license_domain', 'license_limit', 'license_expire_date', 'license_updates_date', 'license_support_date', 'license_comments']; //optional API parameters for this page
        foreach ($optional_api_parameters_array as $optional_api_parameter) { //in case some required parameter was not submitted, set its value empty to prevent "undefined variable" errors
            if (! isset($$optional_api_parameter)) {
                $$optional_api_parameter = '';
            }
        }
        if (LicenseHelper::validateIntegerValue($id) && LicenseHelper::validateIntegerValue($license_require_domain, 0, 1) && LicenseHelper::validateIntegerValue($license_status, 0, 2)) {
            if (empty($client_id) || ! LicenseHelper::validateIntegerValue($client_id)) { //in case no client_id was submitted, its value must be stored as NULL in database
                $client_id = null;
            }
            if (empty($license_code)) { //in case no license_code was submitted, its value must be stored as NULL in database
                $license_code = null;
            }
            $licenseChecks = $this->licenseChecks($client_id, $license_code, $license_ip, $license_domain, $license_limit, $license_expire_date, $license_updates_date, $license_support_date);
            if (! empty($licenseChecks)) {
                return errorResponse($licenseChecks->getOriginalContent()['message'], 400);
            }
            if (! empty($license_expire_date) && LicenseHelper::verifyDateTime($license_expire_date, 'Y-m-d') && $license_expire_date != $rows_array[0]['license_expire_date']) { //license_expire_date changed, reset license_expire_email_date, so client can receive new notification
                $license_expire_email_date = '0000-00-00';
            } else {
                $license_expire_email_date = $rows_array[0]['license_expire_email_date']; //use old license_expire_email_date
            }

            if (! empty($license_updates_date) && LicenseHelper::verifyDateTime($license_updates_date, 'Y-m-d') && $license_updates_date != $rows_array[0]['license_updates_date']) { //license_updates_date changed, reset license_updates_email_date, so client can receive new notification
                $license_updates_email_date = '0000-00-00';
            } else {
                $license_updates_email_date = $rows_array[0]['license_updates_email_date']; //use old license_updates_email_date
            }

            if (! empty($license_support_date) && LicenseHelper::verifyDateTime($license_support_date, 'Y-m-d') && $license_support_date != $rows_array[0]['license_support_date']) { //license_support_date changed, reset license_support_email_date, so client can receive new notification
                $license_support_email_date = '0000-00-00';
            } else {
                $license_support_email_date = $rows_array[0]['license_support_email_date']; //use old license_support_email_date
            }

            if ($license_status == 1) {
                $license_cancel_date = '0000-00-00';
            } else {
                $license_cancel_date = $rows_array[0]['license_cancel_date']; //use old license_cancel_date if license was deactivated previously and its status wasn't changed now
                if (empty($license_cancel_date) || ! LicenseHelper::verifyDateTime($license_cancel_date, 'Y-m-d')) { //set cancel date to now only if no previous cancel date set
                    $license_cancel_date = date('Y-m-d');
                }
            }
            $updated_records += License::where('id', $license_id)
                ->update([
                    'license_order_number' => $license_order_number,
                    'license_ip' => $license_ip,
                    'license_domain' => $license_domain,
                    'license_require_domain' => $license_require_domain,
                    'license_limit' => $license_limit,
                    'license_cancel_date' => $license_cancel_date,
                    'license_expire_date' => $license_expire_date,
                    'license_expire_email_date' => $license_expire_date,
                    'license_updates_date' => $license_updates_date,
                    'license_updates_email_date' => $license_updates_email_date,
                    'license_support_date' => $license_support_date,
                    'license_support_email_date' => $license_support_email_date,
                    'license_comments' => $license_comments,
                    'license_status' => $license_status,
                ]);
            //doMysqlQuery("UPDATE apl_licenses SET license_order_number=?, license_ip=?, license_domain=?, license_require_domain=?, license_limit=?, license_cancel_date=?, license_expire_date=?, license_expire_email_date=?, license_updates_date=?, license_updates_email_date=?, license_support_date=?, license_support_email_date=?, license_comments=?, license_envato=?, license_status=? WHERE license_id=?", array($license_order_number, $license_ip, $license_domain, $license_require_domain, $license_limit, $license_cancel_date, $license_expire_date, $license_expire_email_date, $license_updates_date, $license_updates_email_date, $license_support_date, $license_support_email_date, $license_comments, $license_envato, $license_status, $license_id), array("s", "s", "s", "i", "i", "s", "s", "s", "s", "s", "s", "s", "s", "i", "i", "i"));

            if (! LicenseHelper::validateIntegerValue($updated_records)) {
                $api_error_detected = 1;

                return errorResponse(Lang::get('lang.invalid_record_data'), 400);
            } else {
                foreach ($rows_array = License::leftJoin('products', 'licenses.product_id', '=', 'products.id')
                    ->leftJoin('users', 'licenses.user_id', '=', 'users.id')
                    ->where('licenses.id', $license_id)
                    ->get(['licenses.*', 'products.name as product_title', 'users.email as client_email'])
                    ->toArray() as $row) { //fetch product and client details to use in reports
                    extract((array) $row);
                }

                $client_formatted = LicenseHelper::formatClient($license_code, $client_email);

                return successResponse(Lang::get('lang.license_Update'), $client_formatted, 200);
            }
        } else {
            return errorResponse(Lang::get('lang.invalid'), 400);
        }
    }

    /**
     * To delete the license stored in the license manager.
     *
     * @param  $license_id
     * @return the removed records with a success response
     */
    public function deleteLicense(Request $request)
    {
        $license_id = $request->get('id');

        if (! LicenseHelper::validateIntegerValue($license_id)) {
            return errorResponse(Lang::get('lang.invalid'), 400);
        }

        $license_code = License::where('id', $license_id)->value('license_code');

        if (! $license_code) {
            return successResponse(Lang::get('lang.delete'), 0, 200);
        }
        // Begin transaction
        DB::transaction(function () use ($license_code, $license_id) {
            LicenseCallback::where('license_code', $license_code)->delete();
            Installation::where('license_code', $license_code)->delete();
            InstallationLog::where('license_code', $license_code)->delete();
            License::where('id', $license_id)->delete();
        });

        return successResponse(Lang::get('lang.delete'), 1, 200);
    }

    public function show(Request $request)
    {
        $perPage = $request->input('perPage', 10);
        $page = $request->input('page', 1);
        $searchQuery = $request->input('search_query', '');
        $sortOrder = $request->input('sort_order', 'desc');
        $sortField = $request->input('sort_field', 'id');
        $searchable = [
            'license_code',
            'license_ip',
            'license_limit',
            'license_expire_date',
            'license_support_date',
            'license_order_number',
            'license_domain',
            'license_date',
            'license_updates_date',
            'license_status',
        ];
        $licenseQuery = DB::table('licenses')
            ->selectRaw('licenses.id, licenses.product_id, licenses.user_id as client_id, licenses.license_code, licenses.license_ip, licenses.license_limit, licenses.license_expire_date, licenses.license_support_date, licenses.license_order_number, licenses.license_domain, licenses.license_date, licenses.license_updates_date, licenses.license_status, products.name as product_title, products.id as product_id, users.email as client_email')
            ->leftJoin('products', 'licenses.product_id', '=', 'products.id')
            ->leftJoin('users', 'licenses.user_id', '=', 'users.id')
            ->when($searchQuery, function ($query) use ($searchQuery, $searchable) {
                $query->where('users.email', 'like', '%'.$searchQuery.'%')
                    ->orWhere('products.name', 'like', '%'.$searchQuery.'%');
                foreach ($searchable as $field) {
                    if ($field == 'license_status') {
                        $query->orWhere('licenses.'.$field, 'like', '%'.LicenseHelper::statusFormatter($searchQuery).'%');
                    } elseif ($field == 'license_code') {
                        $query->orWhere('licenses.'.$field, 'like', '%'.str_replace('-', '', $searchQuery).'%');
                    } else {
                        $query->orWhere('licenses.'.$field, 'like', '%'.$searchQuery.'%');
                    }
                }
            })
            ->orderBy('licenses.'.$sortField, $sortOrder)
            ->paginate($perPage, ['*'], 'page', $page);
        $licenseQuery->getCollection()->transform(function ($license) {
            $license->license_order_url = $license->license_order_number ?? '';
            $license->installation_counts = DB::table('installations')
                ->where('license_code', $license->license_code)
                ->count();
            $license->latest_call_backs = DB::table('license_callbacks')
                ->where('license_code', $license->license_code)
                ->orderByDesc('callback_date_time')
                ->value('callback_date_time');
            $license->call_backs_count = DB::table('license_callbacks')
                ->where('license_code', $license->license_code)
                ->count();

            return $license;
        });

        return successResponse(Lang::get('lang.License_show'), $licenseQuery, 200);
    }

    public function edit($license_id)
    {
        $license = License::where('id', $license_id)->firstOrFail();
        $product_name = DB::table('licenses')
            ->join('products', 'licenses.product_id', '=', 'products.id')
            ->where('licenses.id', $license_id)
            ->get(['products.name as name', 'licenses.id']);

        $client_name = User::select(DB::raw('CONCAT(first_name, " ", last_name,"<",email,">") AS full_name'), 'users.id')
            ->join('licenses', 'licenses.user_id', '=', 'users.id')->where('licenses.id', $license_id)
            ->get('full_name', 'users.id');

        if (! empty($license)) {
            return successResponse('', ['license' => $license, 'product_name' => $product_name, 'client_name' => $client_name], 200);
        }

        return errorResponse(Lang::get('lang.invalid'), 400);
    }

    /**
     * To Format the client.
     *
     * @param  $license_code
     * @param  $client_email
     *                       return a formatted array of license code and client email
     */
    public function formatClient($license_code, $client_email)
    {
        if (! empty($license_code)) {
            $client_formatted = $license_code;
        } else {
            if (filter_var($client_email, FILTER_VALIDATE_EMAIL)) {
                $client_formatted = $client_email;
            } else {
                $client_formatted = 'Unknown Client';
            }
        }

        return $client_formatted;
    }

    /***
     * Just performs some license checks while adding a license in faveo license manager.
     */
    protected function licenseChecks($client_id, $license_code, $license_ip, $license_domain, $license_limit, $license_expire_date, $license_updates_date, $license_support_date)
    {
        if (! LicenseHelper::validateIntegerValue($client_id) && empty($license_code)) {
            $api_error_detected = 1;

            return errorResponse(Lang::get('lang.error_client_or_license_code'), 400);
        }

        if (LicenseHelper::validateIntegerValue($client_id) && ! empty($license_code)) {
            $api_error_detected = 1;

            return errorResponse(Lang::get('lang.invalid_licnese'), 400);
        }

        if (! empty($license_ip)) {
            $license_ips_array = explode(',', $license_ip);
            foreach ($license_ips_array as $ip_to_validate) {
                if (! filter_var($ip_to_validate, FILTER_VALIDATE_IP)) {
                    $api_error_detected = 1;

                    return errorResponse(Lang::get('lang.invalid_license_ip'), 400);
                    break;
                }
            }
        }

        if (! empty($license_domain)) {
            $license_domain_array = explode(',', $license_domain);
            foreach ($license_domain_array as $license_domain_array_key => $license_domain_array_value) {
                if (! aflValidateRawDomain(aflGetRawDomain($license_domain_array_value)) || ! ctype_alnum(substr($license_domain_array_value, -1))) { //invalid TLD, scheme included, or last symbol is not alphanumeric (most likely ends with / or another non-alphanumeric character)
                    $api_error_detected = 1;

                    return errorResponse(Lang::get('lang.invalid_domain'), 400);
                }
            }
        }

        if (! empty($license_limit) && ! LicenseHelper::validateIntegerValue($license_limit)) {
            $api_error_detected = 1;

            return errorResponse(Lang::get('lang.invalid_license_limit'), 400);
        }

        if (! empty($license_expire_date) && ! LicenseHelper::verifyDateTime($license_expire_date, 'Y-m-d')) {
            $api_error_detected = 1;

            return errorResponse(Lang::get('lang.invalid_license_expiry'), 400);
        }

        if (! empty($license_updates_date) && ! LicenseHelper::verifyDateTime($license_updates_date, 'Y-m-d')) {
            $api_error_detected = 1;

            return errorResponse(Lang::get('lang.invalid_license_update_date'), 400);
        }

        if (! empty($license_support_date) && ! LicenseHelper::verifyDateTime($license_support_date, 'Y-m-d')) {
            $api_error_detected = 1;

            return errorResponse(Lang::get('lang.invalid_license_support_date'), 400);
        }
    }

    public function reissueLicenseCloud(Request $request)
    {
        Installation::where('license_code', $request->get('license_code'))->delete();
    }

    public function licenseDeactivate(Request $request)
    {
        License::where('license_code', $request->get('license_code'))->update(['license_status' => 0]);
    }

    public function updateTheLicenseCode(Request $request)
    {
        return License::where('license_code', $request->old_license_code)
            ->update(['license_code' => $request->license_code]);
    }

    public function syncTheCreationOfLicense(Request $request)
    {
        try {
            $license_code = $request->input('license_code');
            $license_id = License::where('license_code', $license_code)->value('id');
            $ids = explode(',', $request->input('ids'));
            $license = License::find($license_id);

            if (! $license) {
                return response()->json(['error' => 'License not found'], 404);
            }

            $input_options = json_decode($request->input('options', '[]'), true);

            // Insert into `license_plugins`
            collect($ids)->each(function ($id) use ($license) {
                // Insert into `license_plugins` table
                LicensePlugin::updateOrCreate(
                    ['id' => $license->license_id, 'id' => $id],
                    ['id' => $license->license_id, 'id' => $id]
                );
            });

            // Insert into `license_options`
            foreach ($input_options as $option) {
                DB::table('license_options')->updateOrCreate(
                    [
                        'id' => $license_id,
                        'id' => $option['id'],
                        'option_group' => $option['option_group'],
                        'option_name' => $option['option_name'],
                        'key' => $option['key'],
                    ],
                    [
                        'value' => $option['value'],
                    ]
                );
            }

            return response()->json(['message' => 'License synchronization and options insertion complete']);
        } catch(\Exception $e) {
            Log::error($e->getMessage());
        }
    }

    public function licenseInfo(Request $request)
    {
        // Retrieve license information or throw 404 error if not found
        $license = License::where('license_code', $request->input('license_code'))->firstOrFail();

        // Retrieve product information related to the license
        $product = Product::find($license->id);

        // Retrieve addon information related to the license
        $addons = $license->addonProducts()->with(['latestVersion'])->get()->map(function ($product) {
            return [
                'id' => $product->id,
                'product_name' => $product->name,
                'product_attributes' => $product->product_attributes,
                'product_attributes_license' => $product->pivot->product_attributes_license,
                'latest_version' => optional($product->latestVersion)->version_number,
                'latest_version_file' => optional($product->latestVersion)->version_upgrade_file,
            ];
        });

        // Return success response with formatted data
        return successResponse(
            Lang::get('lang.license_info'),
            [
                'license' => $license,
                'product' => $product,
                'addons' => $addons,
            ],
            200
        );
    }

    public function individualLicenseInfo(Request $request): \Illuminate\Http\JsonResponse
    {
        $licenseCode = $request->input('license_code');

        // Retrieve the license with the given code and load related license options
        $license = License::where('license_code', $licenseCode)
            ->with('licenseOptions') // Eager load license options
            ->first();

        // Check if license is found
        if (! $license) {
            return successResponse('', []);
        }

        // Format the license options data to include license_code
        $licenseOptions = $license->licenseOptions->map(function ($option) use ($license) {
            return [
                'license_code' => $license->license_code,
                'id' => $option->id,
                'option_group' => $option->option_group,
                'option_name' => $option->option_name,
                'key' => $option->key,
                'value' => $option->value,
            ];
        })->toArray();

        return successResponse('', $licenseOptions);
    }

    public function giveLicenseTakeOrder(Request $request)
    {
        return successResponse('', License::where('license_code', $request->input('license_code'))->value('license_order_number'));
    }

    public function getPluginInfo(Request $request)
    {
        $license_codes = collect(json_decode($request->input('license_code'), true));
        $licenses = License::whereIn('license_code', $license_codes)->where(function ($q) {
            $q->where('license_expire_date', '>', \Carbon\Carbon::now())->orWhere('license_expire_date', '0000:00:00');
        })->get()->keyBy('license_code');

        $result = $license_codes->map(function ($license_code) use ($licenses) {
            $license = $licenses->get($license_code);

            if (! $license) {
                return null; // Skip if the license is not found
            }

            $ids = LicensePlugin::where('id', $license->id)->pluck('id')->toArray();
            $ids = ! empty($ids) ? $ids : [$license->id];

            return collect($ids)->unique()->map(function ($id) use ($license_code) {
                return $this->generateLicenseData($id, $license_code);
            })->filter();
        })->filter()->values();

        return successResponse('', $result->toJson());
    }

    private function generateLicenseData($id, $license_code)
    {
        $product = Product::find($id);
        $cloud = Product::find($id);

        $version = ProductVersion::where('id', $id)
            ->orderBy('version_id', 'desc')
            ->first();

        $installed = Installation::where('id', $id)
            ->where('license_code', $license_code)
            ->exists();

        return (! $product || ! $cloud || ! $version || $installed) ? null :
        [
            'id' => $id,
            'product_name' => $product->name,
            'product_key' => $cloud->product_key,
            'product_description' => $product->product_description,
            'version' => $version->version_number,
            'license_code' => $license_code,
            'path' => $cloud->product_path,
        ];
    }
}
