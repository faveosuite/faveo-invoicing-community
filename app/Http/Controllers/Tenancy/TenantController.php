<?php

namespace App\Http\Controllers\Tenancy;

use App\CloudPopUp;
use App\Http\Controllers\Controller;
use App\Http\Controllers\License\LicenseController;
use App\Jobs\ReportExport;
use App\Model\CloudDataCenters;
use App\Model\Common\Country;
use App\Model\Common\FaveoCloud;
use App\Model\Common\Setting;
use App\Model\Common\StatusSetting;
use App\Model\Mailjob\ExpiryMailDay;
use App\Model\Mailjob\QueueService;
use App\Model\Order\InstallationDetail;
use App\Model\Order\Order;
use App\Model\Payment\PlanPrice;
use App\Model\Product\CloudProducts;
use App\Model\Product\Subscription;
use App\ThirdPartyApp;
use App\User;
use Carbon\Carbon;
use Exception;
use GuzzleHttp\Client;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Lang;

class TenantController extends Controller
{
    private $cloud;

    public function __construct(Client $client, FaveoCloud $cloud)
    {
        $this->client = $client;
        $this->cloud = $cloud->first();

        $this->middleware('auth', ['except' => ['verifyThirdPartyToken']]);
    }

    public function viewTenant()
    {
        try {
            if ($this->cloud && $this->cloud->cloud_central_domain) {
                $app_key = null;
                $cloud = $this->cloud;
                $cloudPopUp = CloudPopUp::find(1);
                $keys = ThirdPartyApp::where('app_name', 'faveo_app_key')->select('app_key', 'app_secret')->first();

                throw_if($keys && ! $keys->app_key, new Exception(Lang::get('message.cloud_invalid_message')));

                $app_key = optional($keys)->app_key;

                if ($response = $this->client->request(
                    'GET',
                    $this->cloud->cloud_central_domain.'/tenants',
                    [
                        'query' => [
                            'key' => $app_key,
                        ],
                    ]
                )) {
                    $responseBody = (string) $response->getBody();
                    $responseData = json_decode($responseBody, true);
                    $de = collect($responseData['message'])->paginate(5);
                }
            } else {
                $de = null;
                $cloudButton = null;
                $cloud = null;
                $cloudPopUp = null;
            }
            $cloudButton = StatusSetting::value('cloud_button');
            $cloudDataCenters = CloudDataCenters::all();

            // Format the results as per the specified format
            $regions = $cloudDataCenters->map(function ($center) {
                return [
                    'name' => ! empty($center->cloud_city) ? $center->cloud_city.', '.$center->cloud_countries : $center->cloud_state.', '.$center->cloud_countries,
                    'latitude' => $center->latitude,
                    'longitude' => $center->longitude,
                ];
            });

            return successResponse('', [
                'de' => $de,
                'cloudButton' => $cloudButton,
                'cloud' => $cloud,
                'regions' => $regions,
                'cloudPopUp' => $cloudPopUp,
            ]);
        } catch (\Exception $e) {
            \Logger::exception($e);

            return errorResponse(Lang::get('message.cloud_error_message'));
        }
    }

    public function enableCloud(Request $request)
    {
        try {
            $statusSetting = StatusSetting::findOrFail(1);
            $statusSetting->update([
                'cloud_button' => $request->debug == 'true' ? '1' : '0',
            ]);

            return successResponse(__('message.updated-successfully'));
        } catch (\Exception $ex) {
            return errorResponse(__('message.something_went_wrong_try_again'));
        }
    }

    public function getTenants(Request $request)
    {
        try {
            $searchQuery = $request->input('search-query', '');
            $sortOrder = $request->input('sort-order', 'asc');
            $sortField = $request->input('sort-field', 'created_at');
            $limit = (int) $request->input('limit', 10);

            $keys = ThirdPartyApp::where('app_name', 'faveo_app_key')
                    ->select('app_key', 'app_secret')
                    ->first();

            if (! $keys || empty($keys->app_key)) {
                return errorResponse(__('message.cloud_invalid_message'));
            }

            $response = $this->client->request('GET',
                $this->cloud->cloud_central_domain.'/tenants',
                ['query' => ['key' => $keys->app_key]]
            );

            $data = json_decode($response->getBody(), true);

            $tenants = collect($data['message'])->reject(fn ($t) => $t === null);

            $tenantList = $tenants->map(function ($model) {
                $order_id = $this->getOrderId($model['domain']);
                $order_number = $order_id ? Order::find($order_id)?->number : null;

                $userData = $this->getUserData($order_id);
                $subData = $this->getSubscriptionDataForCloud($order_id);

                return [
                    'tenant_id' => $model['id'] ?? null,
                    'domain' => $model['domain'] ?? null,

                    'database' => [
                        'name' => $model['database_name'] ?? null,
                        'username' => $model['database_user_name'] ?? null,
                    ],

                    'order' => [
                        'order_id' => $order_id,
                        'order_number' => $order_number,
                        'subscription' => $subData['plan'] ?? null,
                    ],

                    'user' => $userData,
                    'dates' => $subData,
                    'links' => [
                        'tenant_domain' => $model['domain'] ? "http://{$model['domain']}" : null,
                    ],

                    'action' => [
                        'delete' => [
                            'tenant_id' => $model['id'],
                            'order_number' => $order_number,
                            'delete_url' => url("tenants/{$model['id']}/delete"),
                        ],
                    ],
                ];
            });

            if ($searchQuery) {
                $tenantList = $tenantList->filter(function ($item) use ($searchQuery) {
                    return isset($item['user']['name']) &&
                        str_contains(strtolower($item['user']['name']), strtolower($searchQuery));
                });
            }

            $tenantList = $tenantList->sortBy($sortField, SORT_REGULAR, $sortOrder);

            $tenantList = $tenantList->values()->take($limit);

            return successResponse(__('message.tenants_fetched_successfully'), $tenantList);
        } catch (\Throwable $e) {
            return errorResponse(__('message.something_went_wrong'), 500);
        }
    }

    private function postCurl($post_url, $post_info)
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $post_url);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $post_info);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
        $result = curl_exec($ch);
        curl_close($ch);

        return $result;
    }

    /**
     * Logic for creating new tenant is handled here.
     */
    public function createTenant(Request $request)
    {
        $order = Order::wherenumber($request->orderNo)->get();
        $product = CloudProducts::where('cloud_product', $order[0]->product()->value('id'))->value('cloud_product_key');

        $this->validate($request,
            [
                'orderNo' => 'required',
                'domain' => 'required||regex:/^[a-zA-Z0-9]+$/u',
            ],
            [
                'domain.regex' => 'Special characters are not allowed in domain name',
            ]);

        $settings = Setting::find(1);
        $userInformation = $request->has('userInfo') ? User::find($request->input('userInfo')) : \Auth::user();

        $userEmail = $userInformation->email;
        $userFirstName = $userInformation->first_name;
        $userLastName = $userInformation->last_name;
        $userId = $userInformation->id;

        $mail = new \App\Http\Controllers\Common\PhpMailController();

        try {
            $company = (string) $request->input('domain');

            // Convert spaces to underscores
            $company = str_replace(' ', '', $company);

            // Convert uppercase letters to lowercase
            $faveoCloud = strtolower($company).'.'.cloudSubDomain();

            $dns_record = dns_get_record($faveoCloud, DNS_CNAME);
            if (! strpos($faveoCloud, cloudSubDomain())) {
                if (empty($dns_record) || ! in_array(cloudSubDomain(), array_column($dns_record, 'target'))) {
                    return ['status' => 'false', 'message' => trans('message.cname')];
                }
            }

            $licCode = Order::where('number', $request->input('orderNo'))->first()->serial_key;
            $keys = ThirdPartyApp::where('app_name', 'faveo_app_key')->select('app_key', 'app_secret')->first();
            if (! optional($keys)->app_key) {//Validate if the app key to be sent is valid or not
                return ['status' => 'false', 'message' => trans('message.something_bad')];
            }

            $token = str_random(32);
            \DB::table('third_party_tokens')->insert(['user_id' => $userId, 'token' => $token]);
            $client = new Client([]);
            $data = ['domain' => $faveoCloud, 'app_key' => $keys->app_key, 'token' => $token, 'lic_code' => $licCode, 'username' => $userEmail, 'userId' => $userId, 'timestamp' => time(), 'product' => $product, 'product_id' => $order[0]->product()->value('id')];
            $encodedData = http_build_query($data);
            $hashedSignature = hash_hmac('sha256', $encodedData, $keys->app_secret);
            $response = $client->request(
                'POST',
                $this->cloud->cloud_central_domain.'/tenants', ['form_params' => $data, 'headers' => ['signature' => $hashedSignature]]
            );
            $response = explode('{', (string) $response->getBody());

            $response = '{'.$response[1];

            $result = json_decode($response);
            if ($result->status == 'fails') {
                if ($result->message == 'Domain already taken. Please select a different domain') {
                    $toDisplay = preg_replace('/\s+/', '', $product);
                    $newRandomDomain = substr($toDisplay.str_shuffle('abcdefghijklmnopqrstuvwxyz0123456789'), 0, 28);

                    return $this->createTenantWithRandomDomain($newRandomDomain, $request);
                }

                $this->prepareMessages($faveoCloud, $userEmail);

                $this->googleChat($result->message);

                return ['status' => 'false', 'message' => trans('message.something_bad')];
            } elseif ($result->status == 'validationFailure') {
                $this->prepareMessages($faveoCloud, $userEmail);

                $this->googleChat($result->message);

                return ['status' => 'validationFailure', 'message' => $result->message];
            } else {
                $client->request('GET', env('CLOUD_JOB_URL_NORMAL'), [
                    'auth' => [env('CLOUD_USER'), env('CLOUD_AUTH')],
                    'query' => [
                        'token' => env('CLOUD_OAUTH_TOKEN'),
                        'domain' => $faveoCloud,
                    ],
                ]);

                //template
                $template = new \App\Model\Common\Template();
                $temp_type_id = \DB::table('template_types')->where('name', 'cloud_created')->value('id');
                $template = $template->where('type', $temp_type_id)->first();
                $contact = getContactData();

                $type = '';
                if ($template) {
                    $type_id = $template->type;
                    $temp_type = new \App\Model\Common\TemplateType();
                    $type = $temp_type->where('id', $type_id)->first()->name;
                }
                $subject = 'Your '.$order[0]->product()->value('name').' is now ready for use. Get started!';
                $message = (isset($result->reason) && $result->reason != '') ? __('message.'.$result->message, ['installationUrl' => $result->installationUrl, 'reason' => $result->reason]) :
                                        __('message.'.$result->message, ['installationUrl' => $result->installationUrl]);

                $message = str_replace('website', strtolower($product), $message);
                $message = str_replace('. You will receive password on your registered email', '', $message);
                $userData = $message.'<br><br> Email:'.' '.$userEmail.'<br>'.'Password:'.' '.$result->password;

                $replace = [
                    'message' => $userData,
                    'product' => $order[0]->product()->value('name'),
                    'name' => $userFirstName.' '.$userLastName,
                    'contact' => $contact['contact'],
                    'logo' => $contact['logo'],
                    'title' => $settings->title,
                    'company_email' => $settings->company_email,
                    'reply_email' => $settings->company_email,

                ];

                logActivity(
                    "Cloud instance <b>{$faveoCloud}</b> created successfully for user <b>{$userEmail}</b>",
                    'created',
                    'Cloud'
                );

                $this->prepareMessages($faveoCloud, $userEmail, true);
                $mail->SendEmail($settings->email, $userEmail, $template->data, $subject, $template->type()->value('name'), $replace, $type);
                if (isset($result->reason) && $result->reason != '') {
                    return ['status' => $result->status, 'message' => $result->message.trans('message.cloud_created_successfully'), 'installationUrl' => $result->installationUrl, 'reason' => $result->reason, 'Free_trial_domain' => $faveoCloud];
                }

                return ['status' => $result->status, 'message' => $result->message.trans('message.cloud_created_successfully'), 'installationUrl' => $result->installationUrl, 'Free_trial_domain' => $faveoCloud];
            }
        } catch (Exception $e) {
            \Logger::exception($e);
            $message = $e->getMessage().' Domain: '.$faveoCloud.' Email: '.$userEmail;
            $this->googleChat($message);

            return ['status' => 'false', 'message' => trans('message.something_bad')];
        }
    }

    public function verifyThirdPartyToken(Request $request)
    {
        try {
            $token = $request->input('token');
            $userId = $request->input('userId');
            $faveoToken = \DB::table('third_party_tokens')->where('user_id', $userId)->value('token');
            if ($faveoToken && $token == $faveoToken) {
                \DB::table('third_party_tokens')->where('user_id', $userId)->delete();
                //delete third party token here
                $response = ['status' => 'success', 'message' => 'Valid token'];
            } else {
                $response = ['status' => 'fails', 'message' => 'Invalid token'];
            }

            return $response;
        } catch (Exception $e) {
            $error = ['status' => 'fails', 'message' => $e->getMessage()];

            return $error;
        }
    }

    public function destroyTenant(Request $request)
    {
        try {
            $keys = ThirdPartyApp::where('app_name', 'faveo_app_key')->select('app_key', 'app_secret')->first();
            $token = str_random(32);
            $data = ['id' => $request->input('id'), 'app_key' => $keys->app_key, 'deleteTenant' => true, 'token' => $token, 'timestamp' => time()];
            $encodedData = http_build_query($data);
            $hashedSignature = hash_hmac('sha256', $encodedData, $keys->app_secret);
            $client = new Client([]);
            $response = $client->request(
                'DELETE',
                $this->cloud->cloud_central_domain.'/tenants', ['form_params' => $data, 'headers' => ['signature' => $hashedSignature]]
            );
            $responseBody = (string) $response->getBody();
            $response = json_decode($responseBody);
            $user = optional(\Auth::user())->email ?? 'Auto deletion';

            if ($response->status == 'success') {
                $this->deleteCronForTenant($request->input('id'));
                \DB::table('free_trial_allowed')->where('domain', $request->input('id'))->delete();
                if (! empty($request->orderId)) {
                    $order = Order::where('id', $request->get('orderId'))->first();
                    $sub = $order->subscription()->first();
                    $sub->is_deleted = 1;
                    $sub->save();
                    //  $order->delete();
                }
//                (empty($request->orderId)) ?: Order::where('number', $request->get('orderId'))->delete();
                (new LicenseController())->reissueDomain($request->input('id'));

                logActivity(
                    "Cloud instance <b>{$request->input('id')}</b> deleted by <b>{$user}</b>",
                    'deleted',
                    'Cloud'
                );

                $this->googleChat('Hello, it has come to my notice that '.$user.' has deleted this cloud instance '.$request->input('id'));

                return successResponse(__('message.cloud_deleted_successfully'));
            } else {
                $this->googleChat('Tenant deletion failed for '.$user.'. Reason: '.$responseBody);

                return errorResponse(__('message.cloud_deleted_failed'));
            }
        } catch (Exception $e) {
            \Logger::exception($e);
            $message = 'Tenant deletion error, Request '.json_encode($request->all()).'. Reason: '.$e->getMessage();
            $this->googleChat($message);

            return errorResponse($e->getMessage());
        }
    }

    private function deleteCronForTenant($tenantId)
    {
        $client = new Client();
        if (strpos($tenantId, cloudSubDomain())) {
            $client->request('GET', env('CLOUD__DELETE_JOB_URL_NORMAL'), [
                'auth' => [env('CLOUD_USER'), env('CLOUD_AUTH')],
                'query' => [
                    'token' => env('CLOUD_OAUTH_TOKEN'),
                    'domain' => $tenantId,
                ],
            ]);
        } else {
            $client->request('GET', env('CLOUD__DELETE_JOB_URL_CUSTOM'), [
                'auth' => [env('CLOUD_USER'), env('CLOUD_AUTH')],
                'query' => [
                    'token' => env('CLOUD_OAUTH_TOKEN'),
                    'domain' => $tenantId,
                ],
            ]);
        }
    }

    public function saveCloudDetails(Request $request)
    {
        $this->validate($request, [
            'cloud_central_domain' => 'required',
            'cloud_cname' => 'required',
        ], [
            'cloud_central_domain.required' => __('validation.cloud_central_domain_required'),
            'cloud_cname.required' => __('validation.cloud_cname_required'),
        ]);

        try {
            $cloud = new FaveoCloud;
            $cloud->updateOrCreate(['id' => 1], ['cloud_central_domain' => $request->input('cloud_central_domain'), 'cloud_cname' => $request->input('cloud_cname')]);

            return successResponse(__('message.updated-successfully'));
        } catch (Exception $e) {
            return errorResponse(__('message.something_went_wrong_try_again'));
        }
    }

    public function DeleteCloudInstanceForClient($orderNumber, $isDelete)
    {
        if ($isDelete) {
            $keys = ThirdPartyApp::where('app_name', 'faveo_app_key')->select('app_key', 'app_secret')->first();
            $token = str_random(32);
            $order_id = Order::where('number', $orderNumber)->where('client', \Auth::user()->id)->value('id');
            $installation_path = \DB::table('installation_details')->where('order_id', $order_id)->where('installation_path', '!=', cloudCentralDomain())->value('installation_path');
            $response = $this->client->request(
                'GET',
                $this->cloud->cloud_central_domain.'/tenants', [
                    'query' => [
                        'key' => $keys->app_key,
                    ],
                ]
            );
            $responseBody = (string) $response->getBody();
            $response = json_decode($responseBody);
            $domainArray = $response->message;
            for ($i = 0; $i < count($domainArray); $i++) {
                if (! is_null($domainArray[$i])) {
                    if ($domainArray[$i]->domain == $installation_path) {
                        $data = ['id' => $domainArray[$i]->id, 'app_key' => $keys->app_key, 'deleteTenant' => true, 'token' => $token, 'timestamp' => time()];
                        $encodedData = http_build_query($data);
                        $hashedSignature = hash_hmac('sha256', $encodedData, $keys->app_secret);
                        $client = new Client([]);
                        $response = $client->request(
                            'DELETE',
                            $this->cloud->cloud_central_domain.'/tenants', ['form_params' => $data, 'headers' => ['signature' => $hashedSignature]]
                        );
                        $responseBody = (string) $response->getBody();
                        $response = json_decode($responseBody);
                        $user = optional(\Auth::user())->email ?? 'Auto deletion';
                        if ($response->status == 'success') {
                            $this->deleteCronForTenant($domainArray[$i]->id);
                            $this->reissueCloudLicense($order_id);
                            Order::where('number', $orderNumber)->where('client', \Auth::user()->id)->delete();
                            \DB::table('free_trial_allowed')->where('domain', $installation_path)->delete();

                            logActivity(
                                "Cloud instance <b>{$installation_path}</b> deleted by </b>{$user}</b>",
                                'deleted',
                                'Cloud'
                            );

                            $this->googleChat('Hello, it has come to my notice that '.$user.' has deleted this cloud instance '.$installation_path);

                            return redirect()->back()->with('success', __('message.cloud_deleted_successfully'));
                        } else {
                            \Logger::exception(new Exception($response->message));

                            $this->googleChat('Tenant deletion failed for '.$user.'. Reason: '.$responseBody);

                            return redirect()->back()->with('fails', __('message.cloud_deleted_failed   '));
                        }
                    }
                }
            }

            return redirect()->back()->with('fails', __('message.something_wrong_cloud_instance'));
        }
    }

    protected function reissueCloudLicense($order_id)
    {
        $order = Order::findorFail($order_id);
        if (\Auth::user()->role != 'admin' && $order->client != \Auth::user()->id) {
            return errorResponse(__('message.cannot_remove_license_installation'));
        }
        $order->domain = '';
        $licenseCode = $order->serial_key;
        $order->save();
        $licenseStatus = \DB::table('status_settings')->pluck('license_status')->first();
        if ($licenseStatus == 1) {
            $licenseExpiry = $order->subscription->ends_at;
            $updatesExpiry = $order->subscription->update_ends_at;
            $supportExpiry = $order->subscription->support_ends_at;
            $cont = new \App\Http\Controllers\License\LicenseController();
            $updateLicensedDomain = $cont->updateLicensedDomain($licenseCode, $order->domain, $order->product, $licenseExpiry, $updatesExpiry, $supportExpiry, $order->number);
            //Now make Installation status as inactive
            $updateInstallStatus = $cont->updateInstalledDomain($licenseCode, $order->product);
        }

        return ['message' => 'success', 'update' => __('message.license_installations_removed')];
    }

    private function prepareMessages($domain, $user, $success = false)
    {
        if ($success) {
            $this->googleChat('Hello, It has come to my notice that this domain has been created successfully Domain name:'.$domain.' and this is their email: '.$user."\u{2705}\u{2705}\u{2705}");
        } else {
            $this->googleChat('Hello, It has come to my notice that this domain has not been created successfully Domain name:'.$domain.' and this is their email: '.$user.'&#10060;'."\u{2716}\u{2716}\u{2716}");
        }
    }

    private function googleChat($text)
    {
        $url = env('GOOGLE_CHAT');
        $message = [
            'text' => $text,
        ];
        $message_headers = [
            'Content-Type' => 'application/json; charset=UTF-8',
        ];
        $client = new Client();
        $client->post($url, [
            'headers' => $message_headers,
            'body' => json_encode($message),
        ]);
    }

    private function createTenantWithRandomDomain($randomDomain, Request $request)
    {
        // Modify the request with the new random domain
        $request->merge(['domain' => $randomDomain]);

        // Call the createTenant function with the modified request
        return $this->createTenant($request);
    }

    public function cloudPopUp(Request $request)
    {
        $this->validate($request, [
            'cloud_top_message' => 'required',
            'cloud_label_field' => 'required',
            'cloud_label_radio' => 'required',
        ],
            [
                'cloud_top_message.required' => __('validation.cloud_tenant.cloud_top_message_required'),
                'cloud_label_field.required' => __('validation.cloud_tenant.cloud_label_field_required'),
                'cloud_label_radio.required' => __('validation.cloud_tenant.cloud_label_radio_required'),
            ]);

        try {
            $cloud = new CloudPopUp;
            $cloud->updateOrCreate(['id' => 1], ['cloud_top_message' => $request->input('cloud_top_message'),
                'cloud_label_field' => $request->input('cloud_label_field'),
                'cloud_label_radio' => $request->input('cloud_label_radio')]);

            return successResponse(__('message.updated-successfully'));
        } catch (Exception $e) {
            return errorResponse(__('message.something_went_wrong_try_again'));
        }
    }

    public function cloudProductStore(Request $request)
    {
        $request->validate(
            [
                'cloud_product' => 'required',
                'cloud_free_plan' => 'required',
                'cloud_product_key' => 'required',
            ],
            [
                'cloud_product.required' => __('validation.cloud_tenant.cloud_product_required'),
                'cloud_free_plan.required' => __('validation.cloud_tenant.cloud_free_plan_required'),
                'cloud_product_key.required' => __('validation.cloud_tenant.cloud_product_key_required'),
            ]
        );
        try {
            CloudProducts::create($request->all());

            return successResponse(__('message.saved_products'));
        } catch(\Exception $e) {
            return errorResponse(__('message.something_went_wrong_try_again'));
        }
    }

    public function exportTenats(Request $request)
    {
        try {
            ini_set('memory_limit', '-1');
            $selectedColumns = $request->input('selected_columns', []);
            $searchParams = $request->input('search_params', []);
            $email = \Auth::user()->email;
            $driver = QueueService::where('status', '1')->first();

            if ($driver->name != 'Sync') {
                app('queue')->setDefaultDriver($driver->short_name);
                ReportExport::dispatch('tenats', $selectedColumns, $searchParams, $email)->onQueue('reports');

                return response()->json(['message' => __('message.report_generation_in_progress')], 200);
            } else {
                return response()->json(['message' => __('message.cannot_sync_queue_driver')], 400);
            }
        } catch (\Exception $e) {
            \Logger::exception($e);

            return response()->json(['message' => $e->getMessage()], 500);
        }
    }

    private function getOrderId($domain)
    {
        return InstallationDetail::where('installation_path', $domain)->latest()->value('order_id');
    }

    private function getUserData($order_id)
    {
        if (! $order_id) {
            return null;
        }

        $userId = Order::where('id', $order_id)->value('client');
        $user = User::find($userId);

        if (! $user) {
            return null;
        }

        return [
            'id' => $user->id,
            'name' => ucfirst($user->first_name).' '.ucfirst($user->last_name),
            'email' => $user->email,
            'mobile' => ($user->mobile_code && $user->mobile)
                ? '+'.$user->mobile_code.' '.$user->mobile
                : null,
            'country' => Country::where('country_code_char2', $user->country)->value('nicename'),
            'profile' => url("clients/{$user->id}"),
        ];
    }

    private function getSubscriptionDataForCloud($order_id)
    {
        if (! $order_id) {
            return null;
        }

        $subscription = Subscription::where('order_id', $order_id)->first();

        if (! $subscription) {
            return null;
        }

        $plan_id = $subscription->plan_id;
        $price = PlanPrice::where('plan_id', $plan_id)->latest()->value('add_price');
        $plan = $price ? 'Paid Subscription' : 'Free Trial';

        $expiry = Carbon::parse($subscription->ends_at)->format('d M Y');
        $cloud_days = ExpiryMailDay::whereNotNull('cloud_days')->value('cloud_days');
        $deletion_date = $expiry ? Carbon::parse($expiry)->addDays($cloud_days)->format('d M Y') : null;

        return [
            'subscription_expiry' => $expiry ?: null,
            'deletion_date' => $deletion_date,
            'plan' => $plan,
        ];
    }
}
