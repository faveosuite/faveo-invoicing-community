<?php

namespace App\Http\Controllers\Tenancy;

use App\CloudPopUp;
use App\Http\Controllers\Common\PhpMailController;
use App\Http\Controllers\Controller;
use App\Jobs\ReportExport;
use App\License\Services\InstallationService;
use App\License\Services\LicenseService;
use App\Model\Common\Country;
use App\Model\Common\FaveoCloud;
use App\Model\Common\Setting;
use App\Model\Common\StatusSetting;
use App\Model\Common\Template;
use App\Model\Common\TemplateType;
use App\Model\Mailjob\ExpiryMailDay;
use App\Model\Mailjob\QueueService;
use App\Model\Order\InstallationDetail;
use App\Model\Order\Order;
use App\Model\Payment\PlanPrice;
use App\Model\Product\CloudProducts;
use App\Model\Product\Product;
use App\Model\Product\Subscription;
use App\ThirdPartyApp;
use App\User;
use Auth;
use Crypt;
use DB;
use Exception;
use GuzzleHttp\Client;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\Lang;
use Illuminate\Support\Str;
use Logger;
use Throwable;

class TenantController extends Controller
{
    private mixed $cloud = null;

    public function __construct(Client $client, FaveoCloud $cloud)
    {
        $this->client = $client; // @phpstan-ignore property.notFound
        $this->cloud = $cloud->first();

        $this->middleware('auth', ['except' => ['verifyThirdPartyToken']]);
    }

    public function enableCloud(Request $request): JsonResponse
    {
        try {
            $statusSetting = StatusSetting::findOrFail(1);
            $statusSetting->update([
                'cloud_button' => $request->debug == 'true' ? '1' : '0',
            ]);

            return successResponse(__('message.updated-successfully'));
        } catch (Exception) {
            return errorResponse(__('message.something_went_wrong_try_again'));
        }
    }

    public function getTenants(Request $request): JsonResponse
    {
        try {
            $searchQuery = $request->input('search-query', '');
            $sortOrder = $request->input('sort-order', 'asc');
            $sortField = $request->input('sort-field', 'created_at');
            $limit = (int) $request->input('limit', 10);
            $page = (int) $request->input('page', 1);

            $keys = ThirdPartyApp::where('app_name', 'faveo_app_key')
                ->select('app_key', 'app_secret')
                ->first();

            if (! $keys || empty($keys->app_key)) {
                return errorResponse(__('message.cloud_invalid_message'));
            }

            $response = $this->client->request('GET', // @phpstan-ignore property.notFound
                $this->cloud->cloud_central_domain.'/tenants',
                ['query' => ['key' => $keys->app_key]]
            );

            $data = json_decode((string) $response->getBody(), associative: true);

            $tenants = collect((array) ($data['message'] ?? []))->reject(fn ($t): bool => $t === null);

            $tenantList = $tenants->map(function (array $model): array {
                $order_id = $this->getOrderId($model['domain']);
                $order_number = $order_id ? Order::select('id', 'number')->find($order_id)?->number : null;
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
                        'tenant_domain' => $model['domain'] ? 'http://'.$model['domain'] : null,
                    ],

                    'action' => [
                        'delete' => [
                            'tenant_id' => $model['id'],
                            'order_number' => $order_number,
                            'delete_url' => url(sprintf('tenants/%s/delete', $model['id'])),
                        ],
                    ],
                ];
            });

            if ($searchQuery) {
                $tenantList = $tenantList->filter(fn ($item): bool => isset($item['user']['name']) &&
                    str_contains(strtolower($item['user']['name']), strtolower((string) $searchQuery)));
            }

            $tenantList = $tenantList->sortBy($sortField, SORT_REGULAR, $sortOrder)->values();

            $total = $tenantList->count();
            $offset = ($page - 1) * $limit;
            $items = $tenantList->slice($offset, $limit)->values();

            $paginator = new LengthAwarePaginator($items, $total, $limit, $page, [
                'path' => $request->url(),
                'query' => $request->query(),
            ]);

            return successResponse(__('message.tenants_fetched_successfully'), $paginator);
        } catch (Throwable) {
            return errorResponse(__('message.something_went_wrong'), 500);
        }
    }

    /**
     * Logic for creating new tenant is handled here.
     */
    public function createTenant(Request $request): JsonResponse
    {
        $order = Order::where('number', $request->orderNo)->first();
        if (! $order) {
            return errorResponse(__('message.something_went_wrong'));
        }
        $product = CloudProducts::where('cloud_product', $order->productRelation()->value('id'))->value('cloud_product_key');

        $this->validate($request,
            [
                'orderNo' => 'required',
                'domain' => 'required||regex:/^[a-zA-Z0-9]+$/u',
            ],
            [
                'domain.regex' => 'Special characters are not allowed in domain name',
            ]);

        $settings = Setting::find(1);
        if (! $settings) {
            return errorResponse(trans('message.something_bad'));
        }
        $userInformation = $request->has('userInfo') ? User::find($request->input('userInfo')) : $this->authUser();
        if (! $userInformation instanceof User) {
            $userInformation = $this->authUser();
        }

        $userEmail = $userInformation->email;
        $userFirstName = $userInformation->first_name;
        $userLastName = $userInformation->last_name;
        $userId = $userInformation->id;

        $mail = new PhpMailController;

        try {
            $company = (string) $request->input('domain');

            // Convert spaces to underscores
            $company = str_replace(' ', '', $company);

            // Convert uppercase letters to lowercase
            $faveoCloud = strtolower($company).'.'.cloudSubDomain();

            $dns_record = dns_get_record($faveoCloud, DNS_CNAME);
            if (! strpos($faveoCloud, (string) cloudSubDomain()) && ($dns_record === [] || $dns_record === false || ! in_array(cloudSubDomain(), array_column($dns_record, 'target')))) {
                return errorResponse(trans('message.cname'));
                // return ['status' => 'false', 'message' => trans('message.cname')];
            }

            $licCode = $order->serial_key;
            $keys = ThirdPartyApp::where('app_name', 'faveo_app_key')->select('app_key', 'app_secret')->first();
            if (! $keys?->app_key) {// Validate if the app key to be sent is valid or not
                return errorResponse(trans('message.something_bad'));
                // return ['status' => 'false', 'message' => trans('message.something_bad')];
            }

            $token = Str::random(32);
            DB::table('third_party_tokens')->insert(['user_id' => $userId, 'token' => $token]);
            $data = ['domain' => $faveoCloud, 'app_key' => $keys->app_key, 'token' => $token, 'lic_code' => $licCode, 'username' => $userEmail, 'userId' => $userId, 'timestamp' => time(), 'product' => $product, 'product_id' => $order->product];
            $encodedData = http_build_query($data);
            $hashedSignature = hash_hmac('sha256', $encodedData, (string) $keys->app_secret);
            $response = $this->client->request( // @phpstan-ignore property.notFound
                'POST',
                $this->cloud->cloud_central_domain.'/tenants', ['form_params' => $data, 'headers' => ['signature' => $hashedSignature]]
            );
            $response = explode('{', (string) $response->getBody());

            $response = '{'.$response[1];

            $result = json_decode($response, true);
            if (! is_array($result)) {
                return errorResponse(trans('message.something_bad'));
            }
            if (($result['status'] ?? null) == 'fails') { // nosemgrep: php.lang.security.md5-loose-equality.md5-loose-equality
                if (($result['message'] ?? null) == 'Domain already taken. Please select a different domain') { // nosemgrep: php.lang.security.md5-loose-equality.md5-loose-equality
                    $toDisplay = preg_replace('/\s+/', '', (string) $product);
                    $newRandomDomain = substr($toDisplay.str_shuffle('abcdefghijklmnopqrstuvwxyz0123456789'), 0, 28);

                    return $this->createTenantWithRandomDomain($newRandomDomain, $request);
                }

                $this->prepareMessages($faveoCloud, $userEmail);

                $this->googleChat($result['message'] ?? '');

                return errorResponse(trans('message.something_bad'));
                // return ['status' => 'false', 'message' => trans('message.something_bad')];
            } elseif (($result['status'] ?? null) == 'validationFailure') { // nosemgrep: php.lang.security.md5-loose-equality.md5-loose-equality
                $this->prepareMessages($faveoCloud, $userEmail);

                $this->googleChat($result['message'] ?? '');

                return errorResponse($result['message'] ?? '');
            } else {
                $this->client->request('GET', config('custom.cloud_job_url_normal'), [ // @phpstan-ignore property.notFound
                    'auth' => [config('custom.cloud_user'), config('custom.cloud_auth')],
                    'query' => [
                        'token' => config('custom.cloud_oauth_token'),
                        'domain' => $faveoCloud,
                    ],
                ]);

                // template
                $template = TemplateType::getSelectedTemplate('cloud_created');
                $contact = getContactData();

                $productName = Product::find($order->product)->name ?? '';
                $type = $template?->type()->value('name') ?? '';
                $subject = 'Your '.$productName.' is now ready for use. Get started!';
                $resultMessage = $result['message'] ?? '';
                $resultInstallationUrl = $result['installationUrl'] ?? '';
                $resultReason = $result['reason'] ?? '';
                $message = ($resultReason != '') ? __('message.'.$resultMessage, ['installationUrl' => $resultInstallationUrl, 'reason' => $resultReason]) : // nosemgrep: php.lang.security.md5-loose-equality.md5-loose-equality
                                        __('message.'.$resultMessage, ['installationUrl' => $resultInstallationUrl]);

                $message = str_replace('website', strtolower((string) $product), (string) $message);
                $message = str_replace('. You will receive password on your registered email', '', $message);
                $userData = $message.'<br><br> Email:'.' '.$userEmail.'<br>'.'Password:'.' '.($result['password'] ?? '');

                $replace = [
                    'message' => $userData,
                    'product' => $productName,
                    'name' => $userFirstName.' '.$userLastName,
                    'contact' => $contact['contact'],
                    'logo' => $contact['logo'],
                    'title' => $settings->title,
                    'company_email' => $settings->company_email,
                    'reply_email' => $settings->company_email,

                ];

                logActivity(
                    sprintf("Cloud instance <b><a href='http://%s' target='_blank'>%s</a></b> created successfully for user <b><a href='", $faveoCloud, $faveoCloud).url('clients/'.$userId).sprintf("'><strong>%s %s</strong></a></b>", $userFirstName, $userLastName),
                    'created',
                    'Cloud'
                );

                $this->prepareMessages($faveoCloud, $userEmail, success: true);
                if ($template instanceof Template) {
                    $mail->SendEmail($settings->email, $userEmail, $template->data, $subject, $template->type()->value('name'), $replace, $type);
                }

                if ($resultReason != '') { // nosemgrep: php.lang.security.md5-loose-equality.md5-loose-equality
                    $data = ['status' => $result['status'] ?? '', 'message' => ($result['message'] ?? '').trans('message.cloud_created_successfully'), 'installationUrl' => $result['installationUrl'] ?? '', 'reason' => $result['reason'] ?? '', 'Free_trial_domain' => $faveoCloud];

                    return successResponse('', $data);
                }

                $data = ['status' => $result['status'] ?? '', 'message' => ($result['message'] ?? '').trans('message.cloud_created_successfully'), 'installationUrl' => $result['installationUrl'] ?? '', 'Free_trial_domain' => $faveoCloud];

                return successResponse('', $data);
            }
        } catch (Exception $exception) {
            Logger::exception($exception);
            $message = $exception->getMessage().' Domain: '.$faveoCloud.' Email: '.$userEmail;
            $this->googleChat($message);

            return errorResponse(trans('message.something_bad'));
        }
    }

    /**
     * @return array<mixed>
     */
    public function verifyThirdPartyToken(Request $request): array
    {
        try {
            $token = $request->input('token');
            $userId = $request->input('userId');
            $faveoToken = DB::table('third_party_tokens')->where('user_id', $userId)->value('token');
            if ($faveoToken && $token == $faveoToken) {
                DB::table('third_party_tokens')->where('user_id', $userId)->delete();

                // delete third party token here
                return ['status' => 'success', 'message' => 'Valid token'];
            }

            return ['status' => 'fails', 'message' => 'Invalid token'];
        } catch (Exception $exception) {
            return ['status' => 'fails', 'message' => $exception->getMessage()];
        }
    }

    public function destroyTenant(Request $request): JsonResponse
    {
        try {
            $keys = ThirdPartyApp::where('app_name', 'faveo_app_key')->select('app_key', 'app_secret')->first();
            if (! $keys) {
                return errorResponse(__('message.something_went_wrong_try_again'));
            }
            $token = Str::random(32);
            $data = ['id' => $request->input('id'), 'app_key' => $keys->app_key, 'deleteTenant' => true, 'token' => $token, 'timestamp' => time()];
            $encodedData = http_build_query($data);
            $hashedSignature = hash_hmac('sha256', $encodedData, (string) $keys->app_secret);
            $response = $this->client->request( // @phpstan-ignore property.notFound
                'DELETE',
                $this->cloud->cloud_central_domain.'/tenants', ['form_params' => $data, 'headers' => ['signature' => $hashedSignature]]
            );
            $responseBody = (string) $response->getBody();
            $responseArray = json_decode($responseBody, true);
            if (! is_array($responseArray)) {
                return errorResponse(__('message.cloud_deleted_failed'));
            }
            $user = $this->authUser()->email ?? 'Auto deletion';

            if (($responseArray['status'] ?? null) == 'success') { // nosemgrep: php.lang.security.md5-loose-equality.md5-loose-equality
                $this->deleteCronForTenant($request->input('id'));
                DB::table('free_trial_allowed')->where('domain', $request->input('id'))->delete();
                if (! empty($request->orderId)) {
                    $this->statusChange($request->orderId);
                }

                //                (empty($request->orderId)) ?: Order::where('number', $request->get('orderId'))->delete();
                if (! empty($request->orderId)) {
                    $encryptedKey = Order::where('number', $request->input('orderId'))->value('serial_key');
                    if ($encryptedKey) {
                        resolve(LicenseService::class)
                            ->reissueLicenseCloud(Crypt::decrypt($encryptedKey));
                    }
                }

                $loggingUser = Auth::check()
                    ? "<a href='".url('clients/'.Auth::id())."'>".$this->authUser()->first_name.' '.$this->authUser()->last_name.'</a>'
                    : 'Auto deletion';

                logActivity(
                    sprintf('Cloud instance <b>%s</b> deleted by <b>%s</b>', $request->input('id'), $loggingUser),
                    'deleted',
                    'Cloud'
                );

                $this->googleChat('Hello, it has come to my notice that '.$user.' has deleted this cloud instance '.$request->input('id'));

                return successResponse(__('message.cloud_deleted_successfully'));
            }

            if (($responseArray['message'] ?? null) == 'tenant_not_found' && ! empty($request->orderId)) { // nosemgrep: php.lang.security.md5-loose-equality.md5-loose-equality
                $this->statusChange($request->orderId);
            }

            $this->googleChat('Tenant deletion failed for '.$user.'. Reason: '.$responseBody);

            return errorResponse(__('message.cloud_deleted_failed'));
        } catch (Exception $exception) {
            Logger::exception($exception);
            $message = 'Tenant deletion error, Request '.json_encode($request->all()).'. Reason: '.$exception->getMessage();
            $this->googleChat($message);

            return errorResponse($exception->getMessage());
        }
    }

    public function statusChange(int $order_id): void
    {
        $order = Order::where('id', $order_id)->first();
        if ($order) {
            $order->subscription()->update(['is_deleted' => 1]);
        }
    }

    private function deleteCronForTenant(string $tenantId): void
    {
        if (strpos($tenantId, (string) cloudSubDomain())) {
            $this->client->request('GET', config('custom.cloud_delete_job_url_normal'), [ // @phpstan-ignore property.notFound
                'auth' => [config('custom.cloud_user'), config('custom.cloud_auth')],
                'query' => [
                    'token' => config('custom.cloud_oauth_token'),
                    'domain' => $tenantId,
                ],
            ]);
        } else {
            $this->client->request('GET', config('custom.cloud_delete_job_url_custom'), [ // @phpstan-ignore property.notFound
                'auth' => [config('custom.cloud_user'), config('custom.cloud_auth')],
                'query' => [
                    'token' => config('custom.cloud_oauth_token'),
                    'domain' => $tenantId,
                ],
            ]);
        }
    }

    public function saveCloudDetails(Request $request): JsonResponse
    {
        $this->validate($request, [
            'cloud_central_domain' => 'required',
            'cloud_cname' => 'required',
        ], [
            'cloud_central_domain.required' => __('validation.cloud_central_domain_required'),
            'cloud_cname.required' => __('validation.cloud_cname_required'),
        ]);

        try {
            $data = [
                'cloud_central_domain' => $request->input('cloud_central_domain'),
                'cloud_cname' => $request->input('cloud_cname'),
                'cloud_job_url' => $request->input('cloud_job_url'),
                'cloud_job_url_normal' => $request->input('cloud_job_url_normal'),
                'cloud_user' => $request->input('cloud_user'),
                'cloud_delete_job_url_normal' => $request->input('cloud_delete_job_url_normal'),
                'cloud_delete_job_url_custom' => $request->input('cloud_delete_job_url_custom'),
            ];

            if (filled($request->input('cloud_auth'))) {
                $data['cloud_auth'] = $request->input('cloud_auth');
            }

            if (filled($request->input('cloud_oauth_token'))) {
                $data['cloud_oauth_token'] = $request->input('cloud_oauth_token');
            }

            if (filled($request->input('google_chat_webhook'))) {
                $data['google_chat_webhook'] = $request->input('google_chat_webhook');
            }

            (new FaveoCloud)->updateOrCreate(['id' => 1], $data);

            return successResponse(__('message.updated-successfully'));
        } catch (Exception) {
            return errorResponse(__('message.something_went_wrong_try_again'));
        }
    }

    public function DeleteCloudInstanceForClient(string $orderNumber, bool $isDelete): RedirectResponse|JsonResponse|null
    {
        if ($isDelete) {
            $keys = ThirdPartyApp::where('app_name', 'faveo_app_key')->select('app_key', 'app_secret')->first();
            if (! $keys) {
                return errorResponse(__('message.something_went_wrong_try_again'));
            }
            $token = Str::random(32);
            $order_id = Order::where('number', $orderNumber)->where('client', $this->authUser()->id)->value('id');
            $installation_path = DB::table('installation_details')->where('order_id', $order_id)->where('installation_path', '!=', cloudCentralDomain())->value('installation_path');
            $response = $this->client->request( // @phpstan-ignore property.notFound
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
            $counter = count($domainArray);
            for ($i = 0; $i < $counter; $i++) {
                if (! is_null($domainArray[$i]) && $domainArray[$i]->domain == $installation_path) {
                    $data = ['id' => $domainArray[$i]->id, 'app_key' => $keys->app_key, 'deleteTenant' => true, 'token' => $token, 'timestamp' => time()];
                    $encodedData = http_build_query($data);
                    $hashedSignature = hash_hmac('sha256', $encodedData, (string) $keys->app_secret);
                    $response = $this->client->request( // @phpstan-ignore property.notFound
                        'DELETE',
                        $this->cloud->cloud_central_domain.'/tenants', ['form_params' => $data, 'headers' => ['signature' => $hashedSignature]]
                    );
                    $responseBody = (string) $response->getBody();
                    $response = json_decode($responseBody);
                    $user = $this->authUser()->email ?? 'Auto deletion';
                    if ($response->status == 'success') { // nosemgrep: php.lang.security.md5-loose-equality.md5-loose-equality
                        $this->deleteCronForTenant($domainArray[$i]->id);
                        $this->reissueCloudLicense($order_id);
                        Order::where('number', $orderNumber)->where('client', $this->authUser()->id)->delete();
                        DB::table('free_trial_allowed')->where('domain', $installation_path)->delete();

                        $loggingUser = Auth::check()
                            ? "<a href='".url('clients/'.Auth::id())."'>".$this->authUser()->first_name.' '.$this->authUser()->last_name.'</a>'
                            : 'Auto deletion';

                        logActivity(
                            sprintf('Cloud instance <b>%s</b> deleted by <b>%s</b>', $installation_path, $loggingUser),
                            'deleted',
                            'Cloud'
                        );

                        $this->googleChat('Hello, it has come to my notice that '.$user.' has deleted this cloud instance '.$installation_path);

                        return successResponse(__('message.cloud_deleted_successfully'));
                    }

                    Logger::exception(new Exception($response->message));
                    $this->googleChat('Tenant deletion failed for '.$user.'. Reason: '.$responseBody);

                    return errorResponse(__('message.cloud_deleted_failed   '));
                }
            }

            return errorResponse(__('message.something_wrong_cloud_instance'));
        }

        return null;
    }

    /**
     * @return array<mixed>
     */
    protected function reissueCloudLicense(int $order_id): JsonResponse|array
    {
        $order = Order::findorFail($order_id);
        if ($this->authUser()->role != 'admin' && $order->client != $this->authUser()->id) {
            return errorResponse(__('message.cannot_remove_license_installation'));
        }

        $order->domain = '';
        $licenseCode = $order->serial_key;
        $order->save();
        $licenseExpiry = $order->subscription ? $order->subscription->ends_at : null;
        $updatesExpiry = $order->subscription ? $order->subscription->update_ends_at : null;
        $supportExpiry = $order->subscription ? $order->subscription->support_ends_at : null;
        $ipAndDomain = LicenseService::parseIpAndDomain($order->domain);
        $l_expiry_time = strtotime((string) $licenseExpiry);
        $u_expiry_time = strtotime((string) $updatesExpiry);
        $s_expiry_time = strtotime((string) $supportExpiry);
        $l_expiry = ($l_expiry_time !== false && $l_expiry_time > 1) ? date('Y-m-d', $l_expiry_time) : '';
        $u_expiry = ($u_expiry_time !== false && $u_expiry_time > 1) ? date('Y-m-d', $u_expiry_time) : '';
        $s_expiry = ($s_expiry_time !== false && $s_expiry_time > 1) ? date('Y-m-d', $s_expiry_time) : '';
        $licenseService = resolve(LicenseService::class);
        $existingLicense = $licenseService->findByCode($licenseCode);
        if ($existingLicense) {
            $licenseService->update($existingLicense->id, [
                'license_order_number' => $order->number,
                'license_require_domain' => $ipAndDomain['requireDomain'],
                'license_expire_date' => $l_expiry ?: $existingLicense->license_expire_date,
                'license_updates_date' => $u_expiry ?: $existingLicense->license_updates_date,
                'license_support_date' => $s_expiry ?: $existingLicense->license_support_date,
                'license_domain' => $ipAndDomain['domain'],
                'license_ip' => $ipAndDomain['ip'],
            ]);
        }

        // Remove installations so the install slots are freed
        resolve(InstallationService::class)->deleteByLicenseCode($licenseCode);

        return ['message' => 'success', 'update' => __('message.license_installations_removed')];
    }

    private function prepareMessages(string $domain, string $user, bool $success = false): void
    {
        if ($success) {
            $this->googleChat('Hello, It has come to my notice that this domain has been created successfully Domain name:'.$domain.' and this is their email: '.$user."\u{2705}\u{2705}\u{2705}");
        } else {
            $this->googleChat('Hello, It has come to my notice that this domain has not been created successfully Domain name:'.$domain.' and this is their email: '.$user.'&#10060;'."\u{2716}\u{2716}\u{2716}");
        }
    }

    private function googleChat(string $text): void
    {
        $url = config('custom.google_chat');
        $message = [
            'text' => $text,
        ];
        $message_headers = [
            'Content-Type' => 'application/json; charset=UTF-8',
        ];
        $this->client->post($url, [ // @phpstan-ignore property.notFound
            'headers' => $message_headers,
            'body' => json_encode($message),
        ]);
    }

    private function createTenantWithRandomDomain(string $randomDomain, Request $request): JsonResponse
    {
        // Modify the request with the new random domain
        $request->merge(['domain' => $randomDomain]);

        // Call the createTenant function with the modified request
        return $this->createTenant($request);
    }

    public function cloudPopUp(Request $request): JsonResponse
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
        } catch (Exception) {
            return errorResponse(__('message.something_went_wrong_try_again'));
        }
    }

    public function cloudProductStore(Request $request): JsonResponse
    {
        $request->validate(
            [
                'cloud_product' => ['required'],
                'cloud_free_plan' => ['required'],
                'cloud_product_key' => ['required'],
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
        } catch (Exception) {
            return errorResponse(__('message.something_went_wrong_try_again'));
        }
    }

    public function exportTenats(Request $request): JsonResponse
    {
        try {
            ini_set('memory_limit', '-1');
            $selectedColumns = $request->input('selected_columns', []);
            $searchParams = $request->input('search_params', []);
            $email = $this->authUser()->email;

            /** @var QueueService $driver */
            $driver = QueueService::where('status', '1')->firstOrFail();

            if ($driver->name === 'Sync') {
                return errorResponse(__('message.cannot_sync_queue_driver'));
            }

            resolve('queue')->setDefaultDriver($driver->short_name);
            dispatch(new ReportExport('tenats', $selectedColumns, $searchParams, $email))->onQueue('reports');

            return successResponse(__('message.system_generating_report'));
        } catch (Exception $exception) {
            Logger::exception($exception);

            return errorResponse($exception->getMessage());
        }
    }

    private function getOrderId(string $domain): ?int
    {
        return InstallationDetail::where('installation_path', $domain)->latest()->value('order_id');
    }

    /**
     * @return array<mixed>
     */
    private function getUserData(?int $order_id): ?array
    {
        if (! $order_id) {
            return null;
        }

        $userId = Order::where('id', $order_id)->value('client');
        $user = User::select('id', 'first_name', 'last_name', 'email', 'mobile', 'mobile_code', 'country')->find($userId);

        if (! $user instanceof User) {
            return null;
        }

        return [
            'id' => $user->id,
            'name' => ucfirst((string) $user->first_name).' '.ucfirst((string) $user->last_name),
            'email' => $user->email,
            'mobile' => ($user->mobile_code && $user->mobile)
                ? '+'.$user->mobile_code.' '.$user->mobile
                : null,
            'country' => Country::where('country_code_char2', $user->country)->value('country_name'),
            'profile' => url('clients/'.$user->id),
        ];
    }

    /**
     * @return array<mixed>
     */
    private function getSubscriptionDataForCloud(?int $order_id): ?array
    {
        if (! $order_id) {
            return null;
        }

        $subscription = Subscription::select('id', 'order_id', 'plan_id', 'ends_at')->where('order_id', $order_id)->first();

        if (! $subscription) {
            return null;
        }

        $plan_id = $subscription->plan_id;
        $price = PlanPrice::where('plan_id', $plan_id)->latest()->value('add_price');
        $plan = $price ? 'Paid Subscription' : 'Free Trial';

        $expiry = Date::parse($subscription->ends_at)->format('d M Y');
        $cloud_days = (int) ExpiryMailDay::whereNotNull('cloud_days')->value('cloud_days');
        $deletion_date = $cloud_days !== 0 ? Date::parse($expiry)->addDays($cloud_days)->format('d M Y') : null;

        return [
            'subscription_expiry' => $expiry,
            'deletion_date' => $deletion_date,
            'plan' => $plan,
        ];
    }

    private function authUser(): User
    {
        $user = \Illuminate\Support\Facades\Auth::user();
        if (! $user instanceof User) {
            throw new Exception('Unauthorized');
        }

        return $user;
    }
}
