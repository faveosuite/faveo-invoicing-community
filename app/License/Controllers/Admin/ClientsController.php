<?php

namespace App\License\Controllers\Admin;

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Controller;
use App\Http\Requests\ClientRequest;
use App\License\Helpers\LicenseHelper;
use App\User;
use Doctrine\DBAL\Query;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Lang;
use Illuminate\Support\Str;

/**
 * Consist of functionalities for the client page in Auto Faveo licenser
 * Class ClientsController.
 */
class ClientsController extends Controller
{
    public function __construct(Request $request)
    {
        $this->ip_address = request()->server('REMOTE_ADDR');
    }

    /**
     * Stores newly added clients into the database.
     *
     * @param  ClientRequest  $request
     * @param  $api_key_secret
     * @param  $first_name
     * @param  $last_name
     * @param  $email
     * @param  $active
     * @return response that a new client is added with array of details
     */
    public function clientAdd(ClientRequest $request)
    {
        $added_records = 0;
        $api_key_secret = $request->get('api_key_secret');
        $first_name = $request->get('first_name');
        $last_name = $request->get('last_name');
        $email = $request->get('email');
        $client_username = $request->get('client_username');
        $active = $request->get('active');
        $role = ($request->get('role') == 0) ? 'admin' : 'client';
        $password = Str::random(8);
        $client_password = $role == 'admin' ? Hash::make($password) : null;

        $api_key = new ApiKeysController();
        $api_action_success = $api_key->apiKeyCheck($api_key_secret, $this->ip_address);
        $optional_api_parameters_array = ['client_username']; //optional API parameters for this page
        foreach ($optional_api_parameters_array as $optional_api_parameter) { //in case some required parameter was not submitted, set its value empty to prevent "undefined variable" errors
            if (! isset($$optional_api_parameter)) {
                $$optional_api_parameter = '';
            }
        }

        if (
            ! empty($first_name) && ! empty($last_name) && filter_var($email, FILTER_VALIDATE_EMAIL)
            && LicenseHelper::validateIntegerValue($active, 0, 2) && $api_action_success == 1
        ) {
            $created_at = date('Y-m-d');
            if ($active != 1) {
                $updated_at = '0000-00-00';
            } else {
                if (empty($updated_at) || ! LicenseHelper::verifyDateTime($updated_at, 'Y-m-d')) { //set cancel date to now only if client is inactive and no previous cancel date set
                    $updated_at = date('Y-m-d');
                }
            }
            try {
                $dataToInsert = [
                    'first_name' => $first_name,
                    'last_name' => $last_name,
                    'email' => $email,
                    'updated_at' => $updated_at,
                    'active' => $active,
                    'role' => $role,
                    'client_username' => $client_username,
                ];
                if ($active == '1') {
                    $dataToInsert['created_at'] = $created_at;
                }
                try {
                    if ($role == 'admin' && $active == '1') {
                        $dataToInsert['client_password'] = $client_password;
                        $add = User::insertOrIgnore($dataToInsert);
                        $added_records += 1;
                        $client_name = $first_name.' '.$last_name;
                        $data = [
                            'client_name' => $client_name,
                            'email' => $email,
                            'password' => $password,
                            'appUrl' => config('app.url'),
                        ];
                        $title = Lang::get('lang.login_credentials_agora');
                        $template = 'emails.welcomeEmail';

                        postEmailSendConfig($email, $title, $template, $data);
                    } else {
                        $added_records += 1;
                        $add = User::insertOrIgnore($dataToInsert);
                    }
                } catch (\Exception $e) {
                    return errorResponse(Lang::get('lang.Client_Add_Failed'), 500);
                }
            } catch (\Exception $e) {
                $added_records += 0;
            }

            if (! LicenseHelper::validateIntegerValue($added_records)) {
                $api_error_detected = 1;

                return errorResponse(Lang::get('lang.invalid'), 400);
            }

            return successResponse(Lang::get('lang.Client_Add'), $add, 201);
        }

        return errorResponse(Lang::get('lang.invalid'), 400);
    }

    /**
     * shows newly added clients from the database.
     *
     * @return response that a client is deleted
     */
    public function show(Request $request, $client_id)
    {
        // Set default pagination values
        $perPage = $request->input('perPage', 10); // Default per page is 10
        $page = $request->input('page', 1);
        $searchQuery = $request->input('search_query');
        $sortOrder = $request->input('sort_order', 'desc');
        $sortField = $request->input('sort_field', 'id');

        // Query to retrieve clients excluding the specified client ID
        $clients = User::where('id', '!=', $client_id)
            ->when($searchQuery, function ($query) use ($searchQuery) {
                return $query->where(function ($query) use ($searchQuery) {
                    $query->where(DB::raw('CONCAT(first_name, " ", last_name)'), 'LIKE', '%'.$searchQuery.'%')
                        ->orWhere('role', 'LIKE', '%'.$searchQuery.'%')
                        ->orWhere('active', 'LIKE', '%'.LicenseHelper::statusFormatter($searchQuery).'%')
                        ->orWhere('email', 'LIKE', '%'.$searchQuery.'%');
                });
            })
            ->select(DB::raw('CONCAT(first_name, " ", last_name) AS full_name'), 'id', 'email', 'role', 'active', 'updated_at', 'created_at', 'is_2fa_enabled')
            ->orderBy($sortField, $sortOrder)
            ->paginate($perPage, ['*'], 'page', $page);

        // Return success response with paginated client data
        return successResponse(Lang::get('lang.Client_Show'), $clients, 200);
    }

    /**
     * Deletes the clients from the database based on the id.
     *
     * @param  $client_id
     * @return response that a client is deleted and all the cascades
     */
    public function deleteClient(Request $request)
    {
        $client_id = $request->get('id');
        $removed_records = 0;
        $api_key_secret = $request->get('api_key_secret');

        $api_key = new ApiKeysController();
        $api_action_success = $api_key->apiKeyCheck($api_key_secret, $this->ip_address);

        if (! LicenseHelper::validateIntegerValue($client_id) && $api_action_success != 1) {
            return errorResponse(Lang::get('lang.Not_found_client'), 404);
        }

        DB::beginTransaction(); //mysqli_begin_transaction($GLOBALS["mysqli"]);
        $transaction_errors_array = [];
        try {
            DB::table('license_callbacks')->where('user_id', $client_id)->delete(); //deleting the callbacks for this client
            DB::table('installations')->where('user_id', $client_id)->delete(); //deleting installations of this client
            DB::table('licenses')->where('user_id', $client_id)->delete(); // deleting the licenses created by this client
            $removed_records += DB::table('users')->where('id', $client_id)->delete(); //deleting the client
            DB::commit();

            return successResponse(Lang::get('lang.Client_Destroy'), $removed_records, 200);
        } catch (Exception $e) {
            $transaction_errors_array[] = $e->getMessage();
            DB::rollBack();
            $removed_records = 0;

            return errorResponse(Lang::get('lang.invalid'), 400);
        }

        return $removed_records;
    }

    public function edit($client_id)
    {
        $client = User::where('id', $client_id)->firstOrFail();

        if (! empty($client)) {
            $client->role = ($client->role == 'admin') ? 0 : 1;

            return successResponse('', ['client' => $client], 200);
        }

        return errorResponse(Lang::get('lang.invalid'), 400);
    }

    /**
     * Updates the clients from the database based on the id.
     *
     * @param  Request  $request
     * @param  $client_id
     * @param  $api_key_secret
     * @param  $first_name
     * @param  $last_name
     * @param  $email
     * @param  $active
     * @return response that a client details is edited
     */
    public function clientUpdate(Request $request)
    {
        $updated_records = 0;
        $api_key_secret = $request->get('api_key_secret');
        $client_id = $request->get('id');
        $first_name = $request->get('first_name');
        $last_name = $request->get('last_name');
        $email = $request->get('email');
        $client_username = $request->get('client_username');
        $active = $request->get('active');
        $role = ($request->get('role') == 0) ? 'admin' : 'client';
        $password = Str::random(8);
        $client_password = $role == 'admin' ? Hash::make($password) : null;

        if (
            empty($client_id) || ! LicenseHelper::validateIntegerValue($client_id) ||
            empty($rows_array = User::where('id', $client_id)->get())
        ) { //invalid record
            return errorResponse(Lang::get('lang.not_found_client'), 404);
        }
        $api_key = new ApiKeysController();
        $api_action_success = $api_key->apiKeyCheck($api_key_secret, $this->ip_address);
        $optional_api_parameters_array = ['client_username']; //optional API parameters for this page
        foreach ($optional_api_parameters_array as $optional_api_parameter) { //in case some required parameter was not submitted, set its value empty to prevent "undefined variable" errors
            if (! isset($$optional_api_parameter)) {
                $$optional_api_parameter = '';
            }
        }
        if (! empty($first_name) && ! empty($last_name) && filter_var($email, FILTER_VALIDATE_EMAIL) && LicenseHelper::validateIntegerValue($active, 0, 2) && $api_action_success == 1) {
            if ($active == 1) {
                $updated_at = '0000-00-00';
            } else {
                $updated_at = $rows_array[0]['updated_at']; //use old updated_at if client was deactivated previously and its status wasn't changed now
                if (empty($updated_at) || ! LicenseHelper::verifyDateTime($updated_at, 'Y-m-d')) { //set cancel date to now only if no previous cancel date set
                    $updated_at = date('Y-m-d');
                }
            }
            $role = User::where('id', $client_id)->value('role');
            $status = User::where('id', $client_id)->value('active');
            $active_date = User::where('id', $client_id)->value('created_at');

            $dataToUpdate = [
                'first_name' => $first_name,
                'last_name' => $last_name,
                'email' => $email,
                'client_username' => $client_username,
                'updated_at' => $updated_at,
                'active' => $active,
                'role' => $role,
            ];
            if ($active == 1 && $active_date == '0000-00-00') {
                $dataToUpdate['created_at'] = date('Y-m-d');
            }
            // The  below code controls the flow of providing credentials to users based on the condition email is fired to the particular user if are creating user with eole client or inactive status then he should not be recieving any credentials  the below code also controls the logic to send email only first the admin gets activated .

            $changingroleCondition = ($role == 'admin' && $role == 'client' && $active == '1');
            $changingstatusCondition = ($role == 'admin' && $role == 'admin' && $status == '0' && $active == '1' && $active_date == '0000-00-00');

            if ($changingroleCondition || $changingstatusCondition) {
                try {
                    $dataToUpdate['client_password'] = $client_password;
                    $updated_records = User::where('id', $client_id)
                        ->update($dataToUpdate);
                    $client_name = $first_name.' '.$last_name;
                    $data = [
                        'client_name' => $client_name,
                        'email' => $email,
                        'password' => $password,
                        'appUrl' => config('app.url'),

                    ];
                    $title = Lang::get('lang.admin_privileges');
                    $template = 'emails.adminRoleMail';
                    postEmailSendConfig($email, $title, $template, $data);
                } catch (\Exception $e) {
                    return errorResponse(Lang::get('lang.Client_Add_Failed'), 500);
                }
            } else {
                $updated_records = User::where('id', $client_id)
                    ->update($dataToUpdate);
            }

            if ($role == 'client' || $active == 0) {
                (new AuthController())->logout(new Request, $client_id);
                $logout = DB::table('oauth_access_tokens')
                    ->where('user_id', $client_id)->delete();
            }

            if (! LicenseHelper::validateIntegerValue($updated_records)) {
                $error_detected = 1;

                return errorResponse(Lang::get('lang.nothing_updated'), 400);
            } else {
                return successResponse(Lang::get('lang.Client_Update'), $updated_records, 200);
            }
        }

        return errorResponse(Lang::get('lang.invalid_client'), 400);
    }
}
