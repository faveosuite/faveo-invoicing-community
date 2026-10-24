<?php

namespace App\Http\Controllers\Tenancy;

use App\CloudPopUp;
use App\Enums\OrderAbility;
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
use App\Model\Mailjob\Condition;
use App\Model\Mailjob\QueueService;
use App\Model\Order\Order;
use App\Model\Payment\PlanPrice;
use App\Model\Product\CloudProducts;
use App\Model\Product\Product;
use App\Model\Product\Subscription;
use App\Services\Cloud\CloudApi;
use App\Services\Payment\AutoRenewalActivationService;
use App\User;
use Auth;
use DB;
use Exception;
use GuzzleHttp\Client;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Lang;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Logger;
use Throwable;

class TenantController extends Controller
{
    private const AUTO_DELETION = 'Auto deletion';
    private const USERS_PAGE = 'admin/users/';
    private const REASON_SEPARATOR = '. Reason: ';
    public const TENANTS_CACHE_KEY = 'cloud_tenants';

    /** Tenants table column => where getTenants() keeps its value. */
    private const TENANT_SORT_KEYS = [
        'order' => 'order.order_number', // the column shows the number; ids would look unsorted
        'user' => 'user.name',
        'email' => 'user.email',
        'mobile' => 'user.mobile',
        'country' => 'user.country',
        'expiry' => 'dates.subscription_expiry',
        'deletion' => 'dates.deletion_date',
        'plan' => 'order.subscription',
        'tenant' => 'tenant_id',
        'domain' => 'domain',
        'db_name' => 'database.name',
        'db_username' => 'database.username',
    ];

    private mixed $cloud = null;

    public function __construct(Client $client, FaveoCloud $cloud)
    {
        $this->client = $client; // @phpstan-ignore property.notFound
        $this->cloud = $cloud->first();

        $this->middleware('auth', ['except' => ['verifyThirdPartyToken']]);
    }

    private function cloudApi(): CloudApi
    {
        return new CloudApi($this->client, $this->cloud); // @phpstan-ignore property.notFound
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
            $this->cloudApi()->keys();
        } catch (\RuntimeException $e) {
            return errorResponse($e->getMessage()); // "Invalid App key provided. Please contact admin."
        }

        try {
            $searchQuery = $request->input('search-query', '');
            $sortOrder = $request->input('sort-order', 'asc');
            $limit = (int) $request->input('limit', 10);
            $page = (int) $request->input('page', 1);

            // Short cache: paging/searching the table shouldn't re-hit the cloud each time.
            // Cleared whenever a tenant is created or deleted.
            $data = Cache::remember(self::TENANTS_CACHE_KEY, 30, fn (): array => $this->cloudApi()->get('/tenants'));

            $tenants = collect((array) ($data['message'] ?? []))->reject(fn ($t): bool => $t === null);

            // Look everything up in a handful of queries instead of several per tenant.
            $orderIds = Order::idsForDomains($tenants->pluck('domain')->filter()->values()->all());
            $orders = Order::with([
                'user:id,first_name,last_name,email,mobile,mobile_code,country',
                'subscription:id,order_id,plan_id,ends_at',
            ])->select('id', 'number', 'client')->findMany(array_values($orderIds))->keyBy('id');
            $countries = Country::whereIn('country_code_char2', $orders->pluck('user.country')->filter()->unique())
                ->pluck('country_name', 'country_code_char2');
            $paidPlanIds = PlanPrice::whereIn('plan_id', $orders->pluck('subscription.plan_id')->filter()->unique())
                ->latest()->get(['plan_id', 'add_price'])->unique('plan_id')
                ->filter(fn (PlanPrice $price): bool => (bool) $price->add_price)->pluck('plan_id')->all();
            $cloudDays = (int) Condition::dayCount('cloud');

            $tenantList = $tenants->map(function (array $model) use ($orderIds, $orders, $countries, $paidPlanIds, $cloudDays): array {
                $order_id = $orderIds[strtolower((string) ($model['domain'] ?? ''))] ?? null;
                $order = $order_id ? $orders->get($order_id) : null;
                $order_number = $order?->number;
                $userData = $this->getUserData($order?->user, $countries->all());
                $subData = $this->getSubscriptionDataForCloud($order?->subscription, $paidPlanIds, $cloudDays);

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
                            'order_id' => $order_id, // destroyTenant's orderId is the order's id, not its number
                            'delete_url' => url(sprintf('tenants/%s/delete', $model['id'])),
                        ],
                    ],
                ];
            });

            if ($searchQuery) {
                $needle = strtolower(trim((string) $searchQuery));
                $tenantList = $tenantList->filter(fn (array $item): bool => collect([
                    $item['domain'], $item['tenant_id'], $item['order']['order_number'],
                    $item['user']['name'] ?? null, $item['user']['email'] ?? null,
                    $item['database']['name'], $item['database']['username'],
                ])->contains(fn ($value): bool => str_contains(strtolower((string) $value), $needle)));
            }

            // The cloud sends no creation date, so the default "newest" = newest order.
            // Empty values always go last, whichever direction.
            $sortKey = self::TENANT_SORT_KEYS[$request->input('sort-field')] ?? 'order.order_id';
            $value = static function (array $row) use ($sortKey): mixed {
                $value = data_get($row, $sortKey);

                return is_string($value) ? strtolower($value) : $value;
            };
            $tenantList = $tenantList->sortBy([
                fn (array $a, array $b): int => ($value($a) === null) <=> ($value($b) === null),
                fn (array $a, array $b): int => $sortOrder === 'asc' ? $value($a) <=> $value($b) : $value($b) <=> $value($a),
            ])->values();

            $total = $tenantList->count();
            $offset = ($page - 1) * $limit;
            $items = $tenantList->slice($offset, $limit)->values();

            $paginator = new LengthAwarePaginator($items, $total, $limit, $page, [
                'path' => $request->url(),
                'query' => $request->query(),
            ]);

            return successResponse(__('message.tenants_fetched_successfully'), $paginator);
        } catch (Throwable $e) {
            Logger::exception($e);

            return errorResponse(__('message.something_went_wrong'), 500);
        }
    }

    /**
     * Logic for creating new tenant is handled here.
     */
    public function createTenant(Request $request): JsonResponse // NOSONAR - cloud instance handling, kept as is
    {
        Cache::forget(self::TENANTS_CACHE_KEY);

        $order = Order::where('number', $request->orderNo)->first();
        if (! $order) {
            return errorResponse(__('message.something_went_wrong'));
        }
        $product = CloudProducts::where('cloud_product', $order->productRelation()->value('id'))->value('cloud_product_key');

        $this->validate($request,
            [
                'orderNo' => 'required',
                // Matches the frontend's already-correct cloudTrialValidations.js rule
                // (letters/digits, hyphens allowed but not at the start/end) — this used
                // to be stricter and rejected valid hyphenated domains after the customer
                // had already been charged (see QA bug #41).
                'domain' => 'required|regex:/^[a-zA-Z0-9]([a-zA-Z0-9-]*[a-zA-Z0-9])?$/',
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
        $faveoCloud = '';

        try {
            $company = (string) $request->input('domain');

            // Convert spaces to underscores
            $company = str_replace(' ', '', $company);

            // Convert uppercase letters to lowercase
            $faveoCloud = strtolower($company).'.'.cloudSubDomain();

            $dns_record = dns_get_record($faveoCloud, DNS_CNAME);
            if (! strpos($faveoCloud, (string) cloudSubDomain()) && ($dns_record === [] || $dns_record === false || ! in_array(cloudSubDomain(), array_column($dns_record, 'target')))) {
                return errorResponse(trans('message.cname'));
            }

            $licCode = $order->serial_key;
            $api = $this->cloudApi();
            $api->keys(); // no app key → nothing to send; fail before storing a token

            // The cloud calls verifyThirdPartyToken back with this token.
            $token = Str::random(32);
            DB::table('third_party_tokens')->insert(['user_id' => $userId, 'token' => $token]);
            $result = $api->send('POST', '/tenants', ['domain' => $faveoCloud, 'token' => $token, 'lic_code' => $licCode, 'username' => $userEmail, 'userId' => $userId, 'product' => $product, 'product_id' => $order->product]);
            if (($result['status'] ?? null) == 'fails') { // nosemgrep: php.lang.security.md5-loose-equality.md5-loose-equality
                if (($result['message'] ?? null) == 'Domain already taken. Please select a different domain') { // nosemgrep: php.lang.security.md5-loose-equality.md5-loose-equality
                    $toDisplay = preg_replace('/\s+/', '', (string) $product);
                    $newRandomDomain = substr($toDisplay.str_shuffle('abcdefghijklmnopqrstuvwxyz0123456789'), 0, 28);

                    return $this->createTenantWithRandomDomain($newRandomDomain, $request);
                }

                $this->prepareMessages($faveoCloud, $userEmail);

                Logger::exception(new Exception(sprintf('Cloud tenant create failed for %s: %s', $faveoCloud, json_encode($result))));
                $this->googleChat($result['message'] ?? '');

                return errorResponse(trans('message.something_bad'));
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
                    sprintf("Cloud instance <b><a href='http://%s' target='_blank'>%s</a></b> created successfully for user <b><a href='", $faveoCloud, $faveoCloud).url(self::USERS_PAGE.$userId).sprintf("'><strong>%s %s</strong></a></b>", $userFirstName, $userLastName),
                    'created',
                    'Cloud'
                );

                $this->prepareMessages($faveoCloud, $userEmail, success: true);
                if ($template instanceof Template) {
                    $mail->SendEmail($settings->email, $userEmail, $template->data, $template->name, $template->type()->value('name'), $replace, $type);
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
            \Logger::exception($exception);

            return ['status' => 'fails', 'message' => __('message.sorry_something_wrong')];
        }
    }

    public function destroyTenant(Request $request): JsonResponse
    {
        Cache::forget(self::TENANTS_CACHE_KEY);

        try {
            $responseArray = $this->cloudApi()->send('DELETE', '/tenants', ['id' => $request->input('id'), 'deleteTenant' => true]);
            $user = Auth::user()->email ?? self::AUTO_DELETION; // also runs from the expiry cron, with no logged-in user

            if (($responseArray['status'] ?? null) == 'success') { // nosemgrep: php.lang.security.md5-loose-equality.md5-loose-equality
                $this->deleteCronForTenant($request->input('id'));
                DB::table('free_trial_allowed')->where('domain', $request->input('id'))->delete();
                if (! empty($request->orderId)) {
                    $this->statusChange($request->orderId);
                }

                if (! empty($request->orderId)) {
                    // The Order serial_key accessor already decrypts, so value() returns the plain key.
                    $serialKey = Order::where('id', $request->input('orderId'))->value('serial_key');
                    if ($serialKey) {
                        resolve(LicenseService::class)->reissueLicenseCloud($serialKey);
                    }
                }

                $loggingUser = Auth::check()
                    ? "<a href='".url(self::USERS_PAGE.Auth::id())."'>".$this->authUser()->first_name.' '.$this->authUser()->last_name.'</a>'
                    : self::AUTO_DELETION;

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

            $this->googleChat('Tenant deletion failed for '.$user.self::REASON_SEPARATOR.json_encode($responseArray));

            return errorResponse(__('message.cloud_deleted_failed'));
        } catch (Exception $exception) {
            Logger::exception($exception);
            $message = 'Tenant deletion error, Request '.json_encode($request->all()).self::REASON_SEPARATOR.$exception->getMessage();
            $this->googleChat($message);

            return errorResponse(__('message.sorry_something_wrong'));
        }
    }

    /**
     * Marks the order's cloud instance as deleted. Every instance deletion
     * (client, admin, bulk/user delete, post-expiry cron) ends here, so this is
     * also where auto-renewal stops: a deleted instance must never be renewed
     * or charged again. Same path as the order page's "disable auto renewal",
     * including cancelling the live Stripe/Razorpay subscription.
     */
    public function statusChange(int $order_id): void
    {
        $order = Order::where('id', $order_id)->first();
        if (! $order) {
            return;
        }

        $order->subscription()->update(['is_deleted' => 1]);

        foreach (Subscription::where('order_id', $order->id)->get() as $subscription) {
            try {
                resolve(AutoRenewalActivationService::class)->deactivate($subscription, cancelAtGateway: true);
            } catch (Throwable $throwable) {
                // The instance is already gone; don't fail the deletion over this — log it for follow-up.
                Logger::exception($throwable);
            }
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

    public function DeleteCloudInstanceForClient(string $orderNumber, bool $isDelete): RedirectResponse|JsonResponse|null // NOSONAR - cloud instance handling, kept as is
    {
        Cache::forget(self::TENANTS_CACHE_KEY);

        if ($isDelete) {
            $order = Order::where('number', $orderNumber)->where('client', $this->authUser()->id)->first();
            if (! $order instanceof Order) {
                return errorResponse(__('message.something_wrong_cloud_instance'));
            }

            // Only the owner's own, live cloud order — see OrderPolicy::deleteCloud.
            $access = Gate::inspect(OrderAbility::DeleteCloud, $order);
            if ($access->denied()) {
                return deniedResponse($access);
            }
            $order_id = $order->id;
            $installation_path = $order->installedDomains()->first();
            try {
                $api = $this->cloudApi();
                $tenant = collect((array) ($api->get('/tenants')['message'] ?? []))
                    ->first(fn ($t): bool => is_array($t) && ($t['domain'] ?? null) == $installation_path);
                if (! $tenant) {
                    return errorResponse(__('message.something_wrong_cloud_instance'));
                }
                $response = $api->send('DELETE', '/tenants', ['id' => $tenant['id'], 'deleteTenant' => true]);
            } catch (Exception $exception) {
                Logger::exception($exception);

                return errorResponse(__('message.cloud_deleted_failed'));
            }

            $user = $this->authUser()->email ?? self::AUTO_DELETION;
            if (($response['status'] ?? null) == 'success') { // nosemgrep: php.lang.security.md5-loose-equality.md5-loose-equality
                $this->deleteCronForTenant($tenant['id']);
                $this->reissueCloudLicense($order_id);
                // Only the instance goes; the order stays, marked the same way the admin delete does.
                $this->statusChange($order_id);
                DB::table('free_trial_allowed')->where('domain', $installation_path)->delete();

                $loggingUser = Auth::check()
                    ? "<a href='".url(self::USERS_PAGE.Auth::id())."'>".$this->authUser()->first_name.' '.$this->authUser()->last_name.'</a>'
                    : self::AUTO_DELETION;

                logActivity(
                    sprintf('Cloud instance <b>%s</b> deleted by <b>%s</b>', $installation_path, $loggingUser),
                    'deleted',
                    'Cloud'
                );

                $this->googleChat('Hello, it has come to my notice that '.$user.' has deleted this cloud instance '.$installation_path);

                return successResponse(__('message.cloud_deleted_successfully'));
            }

            Logger::exception(new Exception((string) json_encode($response)));
            $this->googleChat('Tenant deletion failed for '.$user.self::REASON_SEPARATOR.json_encode($response));

            return errorResponse(__('message.cloud_deleted_failed'));
        }

        return null;
    }

    /**
     * @return array<mixed>
     */
    protected function reissueCloudLicense(int $order_id): JsonResponse|array // NOSONAR - cloud instance handling, kept as is
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
        if (empty($url)) { // ponytail: webhook is optional; skip instead of letting Guzzle blow up and mask the real error
            return;
        }
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
                'cloud_product' => ['required', Rule::unique('cloud_products', 'cloud_product')],
                'cloud_free_plan' => [
                    'required',
                    Rule::exists('plans', 'id')->where('product', $request->input('cloud_product')),
                ],
                'cloud_product_key' => ['required'],
            ],
            [
                'cloud_product.required' => __('validation.cloud_tenant.cloud_product_required'),
                'cloud_product.unique' => __('validation.cloud_tenant.cloud_product_unique'),
                'cloud_free_plan.required' => __('validation.cloud_tenant.cloud_free_plan_required'),
                'cloud_free_plan.exists' => __('validation.cloud_tenant.cloud_free_plan_invalid'),
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
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $exception) {
            return errorResponse(__('message.record_not_found'));
        } catch (Exception $exception) {
            Logger::exception($exception);

            return errorResponse(__('message.sorry_something_wrong'));
        }
    }

    /**
     * @param  array<string, string>  $countries  country code => name
     * @return array<mixed>
     */
    private function getUserData(?User $user, array $countries): ?array
    {
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
            'country' => $countries[$user->country] ?? null,
            'profile' => url(self::USERS_PAGE.$user->id),
        ];
    }

    /**
     * @param  array<int, mixed>  $paidPlanIds
     * @return array<mixed>
     */
    private function getSubscriptionDataForCloud(?Subscription $subscription, array $paidPlanIds, int $cloudDays): ?array
    {
        if (! $subscription instanceof Subscription) {
            return null;
        }

        $expiry = Date::parse($subscription->ends_at);

        return [
            'subscription_expiry' => $expiry->toISOString(),
            'deletion_date' => $cloudDays !== 0 ? $expiry->copy()->addDays($cloudDays)->toISOString() : null,
            'plan' => in_array($subscription->plan_id, $paidPlanIds) ? 'Paid Subscription' : 'Free Trial',
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
