<?php

namespace App\Http\Controllers\Common;

use App\ApiKey;
use App\CloudPopUp;
use App\Email_log;
use App\EmailValidationResults;
use App\Facades\Attach;
use App\Http\Controllers\BillingInstaller\InstallerController;
use App\Model\CloudDataCenters;
use App\Model\Common\CommonSettings;
use App\Model\Common\Country;
use App\Model\Common\EmailMobileValidationProviders;
use App\Model\Common\FaveoCloud;
use App\Model\Common\PipedriveGroups;
use App\Model\Common\Setting;
use App\Model\Common\State;
use App\Model\Common\StatusSetting;
use App\Model\Common\Template;
use App\Model\Common\TemplateType;
use App\Model\Common\Timezone;
use App\Model\Github\Github;
use App\Model\Mailjob\ActivityLogDay;
use App\Model\Mailjob\Condition;
use App\Model\Mailjob\ExpiryMailDay;
use App\Model\Mailjob\QueueService;
use App\Model\Payment\Currency;
use App\Model\Payment\Plan;
use App\Model\Plugin;
use App\Model\Product\Product;
use App\Payment_log;
use App\ThirdPartyApp;
use App\User;
use Cache;
use DB;
use Exception;
use Illuminate\Contracts\Database\Query\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\Http;
use Lang;
use Sentry\State\HubInterface;
use Spatie\Activitylog\Models\Activity;

class SettingsController extends BaseSettingsController
{
    /**
     * @var \App\ApiKey
     */
    public $apikey;

    /**
     * @var \App\Model\Common\StatusSetting
     */
    public $statusSetting;

    public function __construct()
    {
        $this->middleware('auth', ['except' => 'checkPaymentGateway']);
        $this->middleware('admin', ['except' => 'checkPaymentGateway']);

        $apikey = new ApiKey();
        $this->apikey = $apikey;

        $status = new StatusSetting();
        $this->statusSetting = $status;
    }

    public function mobileVerification(ApiKey $apikeys): \Illuminate\Http\JsonResponse
    {
        [$mobileauthkey,$msg91Sender,$msg91TemplateId,$msg91ThirdPartyId] = array_values($apikeys->select('msg91_auth_key', 'msg91_sender', 'msg91_template_id', 'msg91_third_party_id')->first()->toArray());

        $data = [
            'mobileauthkey' => $mobileauthkey,
            'msg91Sender' => $msg91Sender,
            'msg91TemplateId' => $msg91TemplateId,
            'selectedApp' => $msg91ThirdPartyId,
        ];

        return successResponse('', $data);
    }

    public function termsUrl(ApiKey $apikeys): \Illuminate\Http\JsonResponse
    {
        $termsUrl = $apikeys->value('terms_url');

        $data = [
            'termsUrl' => $termsUrl,
        ];

        return successResponse('', $data);
    }

    public function pipedrivekeys(ApiKey $apikeys): \Illuminate\Http\JsonResponse
    {
        $pipedriveKey = $apikeys->value('pipedrive_api_key');

        $data = [
            'pipedriveKey' => $pipedriveKey,

        ];

        return successResponse('', $data);
    }

    public function githubkeys(ApiKey $apikeys): \Illuminate\Http\JsonResponse
    {
        $model = new Github();
        try {
            $github = $model->firstOrFail();
            $githubStatus = StatusSetting::first()->github_status;
            $githubFileds = $github->select('client_id', 'client_secret', 'username', 'password')->first();
            $data = [
                'githubFileds' => $githubFileds,

            ];

            return successResponse('', $data);
        } catch (Exception) {
            $data = [
                'githubFileds' => '',

            ];

            return successResponse('', $data);
        }
    }

    public function postKeys(ApiKey $apikeys, Request $request): \Illuminate\Http\RedirectResponse
    {
        try {
            $keys = $apikeys->find(1);
            $keys->fill($request->input())->save();

            return back()->with('success', Lang::get('message.updated-successfully'));
        } catch (Exception $exception) {
            return back()->with('fails', $exception->getMessage());
        }
    }

    /**
     * PAyment Gateway that is shown on the basis of currency.
     *
     * @param  string  $currency  The currency of the Product Selected
     * @return string Name of the Payment Gateway
     */
    public static function checkPaymentGateway($currency)
    {
        try {
            $active_plugins = Plugin::where('status', 1)->get();
            $allAcivePluginName = [];
            if ($active_plugins) {
                foreach ($active_plugins as $plugin) {
                    if (isCurrencySupportedForPayments($currency, strtolower((string) $plugin->name))) {
                        $allAcivePluginName[] = $plugin->name;
                    }
                }
            }

            return $allAcivePluginName;
        } catch (Exception $exception) {
            return back()->with('fails', $exception->getMessage());
        }
    }

    public function getSettingsIndexData(): \Illuminate\Http\JsonResponse
    {
        $statusSetting = $this->statusSetting->first();

        return successResponse('', [
            'is_redis_configured' => (bool) QueueService::where('short_name', 'redis')->value('status'),
            'is_debug_mode' => (bool) config('app.debug'),
            'is_pulse_enabled' => (bool) commonSettings('debugging', 'pulse_enabled'),
            'is_clockwork_enabled' => (bool) commonSettings('debugging', 'clockwork_enable'),
            'is_mail_sending_enabled' => (int) Setting::value('sending_status') === 1,
            'is_msg91_enabled' => (bool) $statusSetting?->msg91_status,
            'is_pipedrive_enabled' => (int) $statusSetting?->pipedrive_status === 1,
            'is_recaptcha_enabled' => (int) $statusSetting?->recaptcha_status === 1,
            'is_email_validation_enabled' => (bool) $statusSetting?->email_validation_status,
        ]);
    }

    public function settingsTemplate(): \Illuminate\Http\JsonResponse
    {
        try {
            $types = TemplateType::all()->map(fn ($t): array => [
                'id' => $t->id,
                'name' => $t->name,
                'selected_template_id' => $t->selected_template_id,
            ])->values();

            $templates = Template::select('id', 'name')
                ->orderBy('name')
                ->get();

            return successResponse('', compact('types', 'templates'));
        } catch (Exception $exception) {
            return errorResponse($exception->getMessage());
        }
    }

    public function postSettingsTemplate(Request $request): \Illuminate\Http\JsonResponse
    {
        try {
            foreach ($request->input('mappings', []) as $typeId => $templateId) {
                TemplateType::where('id', (int) $typeId)
                    ->update(['selected_template_id' => $templateId ?: null]);
            }

            return successResponse(Lang::get('message.updated-successfully'));
        } catch (Exception $exception) {
            return errorResponse($exception->getMessage());
        }
    }

    public function getErrorSettings(Setting $settings): \Illuminate\Http\JsonResponse
    {
        try {
            $set = $settings->find(1);

            return successResponse('', [
                'error_log' => (bool) $set->error_log,
                'error_email' => $set->error_email ?? '',
            ]);
        } catch (Exception $exception) {
            return errorResponse($exception->getMessage());
        }
    }

    public function getSystemSettingsData(Setting $settings): \Illuminate\Http\JsonResponse
    {
        try {
            $set = $settings->with('timezone:id,name,location')->find(1) ?: $settings->create(['company' => '']);
            $languages = new InstallerController()->languageList()->getData()->data ?? [];

            return successResponse('', [
                'settings' => [
                    'company' => $set->company,
                    'company_email' => $set->company_email,
                    'title' => $set->title,
                    'favicon_title' => $set->favicon_title,
                    'favicon_title_client' => $set->favicon_title_client,
                    'website' => $set->website,
                    'phone' => $set->phone,
                    'phone_code' => $set->phone_code,
                    'phone_country_iso' => $set->phone_country_iso,
                    'address' => $set->address,
                    'city' => $set->city,
                    'state' => $set->state,
                    'country' => $set->country,
                    'zip' => $set->zip,
                    'cin_no' => $set->cin_no,
                    'gstin' => $set->gstin,
                    'default_currency' => $set->default_currency,
                    'language' => $set->content,
                    'knowledge_base_url' => $set->knowledge_base_url,
                    'autorenewal_status' => (bool) $set->autorenewal_status,
                    'logo' => $set->logo,
                    'admin_logo' => $set->admin_logo,
                    'fav_icon' => $set->fav_icon,
                    'timezone_id' => $set->timezone_id,
                    'timezone' => $set->timezone,
                    'date_format' => $set->date_format ?? 'd/m/Y',
                    'time_format' => $set->time_format ?? 'H:i',
                ],
                'countries' => Country::orderBy('country_name')->get(['country_code_char2', 'country_name']),
                'states' => State::where('country_code', $set->country)->orderBy('state_subdivision_name')->get(['iso2', 'state_subdivision_name']),
                'currencies' => Currency::orderBy('name')->get(['code', 'name', 'symbol']),
                'languages' => $languages,
                'timezones' => Timezone::orderBy('name')->get(['id', 'name', 'location']),
                'date_formats' => [
                    ['value' => 'd/m/Y', 'label' => 'DD/MM/YYYY'],
                    ['value' => 'm/d/Y', 'label' => 'MM/DD/YYYY'],
                    ['value' => 'Y/m/d', 'label' => 'YYYY/MM/DD'],
                    ['value' => 'Y-m-d', 'label' => 'YYYY-MM-DD'],
                    ['value' => 'd-m-Y', 'label' => 'DD-MM-YYYY'],
                    ['value' => 'm-d-Y', 'label' => 'MM-DD-YYYY'],
                    ['value' => 'M j, Y', 'label' => 'Mon DD, YYYY'],
                ],
                'time_formats' => [
                    ['value' => 'H:i',   'label' => '24 Hours'],
                    ['value' => 'h:i A', 'label' => '12 Hours'],
                ],
            ]);
        } catch (Exception $exception) {
            return errorResponse($exception->getMessage());
        }
    }

    public function updateSystemSettingsData(Setting $settings, Request $request): \Illuminate\Http\JsonResponse
    {
        $request->validate([
            'company' => ['required', 'max:50'],
            'company_email' => ['required', 'email'],
            'website' => ['required', 'url'],
            'phone' => ['required'],
            'address' => ['required'],
            'state' => ['required'],
            'country' => ['required'],
            'default_currency' => ['required'],
            'logo' => ['sometimes', 'file', 'mimes:jpeg,png,jpg', 'max:2048'],
            'admin-logo' => ['sometimes', 'file', 'mimes:jpeg,png,jpg', 'max:2048'],
            'fav-icon' => ['sometimes', 'file', 'mimes:jpeg,png,jpg', 'max:2048'],
        ]);

        try {
            $setting = $settings->find(1) ?: $settings->create(['company' => '']);
            $input = $request->only([
                'company', 'company_email', 'title', 'favicon_title', 'favicon_title_client',
                'website', 'phone', 'phone_code', 'phone_country_iso', 'address', 'city',
                'state', 'country', 'zip', 'cin_no', 'gstin', 'default_currency', 'knowledge_base_url',
            ]);
            $input['autorenewal_status'] = $request->boolean('autorenewal_status');

            if ($request->hasFile('logo')) {
                $path = Attach::put('images', $request->file('logo'), null, true);
                $setting->logo = basename((string) $path);
            }

            if ($request->hasFile('admin-logo')) {
                $path = Attach::put('admin/images', $request->file('admin-logo'), null, true);
                $setting->admin_logo = basename((string) $path);
            }

            if ($request->hasFile('fav-icon')) {
                $path = Attach::put('common/images', $request->file('fav-icon'), null, true);
                $setting->fav_icon = basename((string) $path);
            }

            $setting->default_symbol = Currency::where('code', $request->input('default_currency'))->value('symbol');
            $setting->content = $request->input('language');
            $setting->fill($input)->save();

            return successResponse(Lang::get('message.updated-successfully'));
        } catch (Exception $exception) {
            return errorResponse($exception->getMessage());
        }
    }

    public function updateDateTimeSettingsData(Setting $settings, Request $request): \Illuminate\Http\JsonResponse
    {
        $request->validate([
            'timezone_id' => ['required', 'integer', 'exists:timezone,id'],
            'date_format' => ['required', 'string', 'max:20'],
            'time_format' => ['required', 'string', 'max:20'],
        ]);

        try {
            $setting = $settings->find(1) ?: $settings->create(['company' => '']);
            $setting->fill($request->only(['timezone_id', 'date_format', 'time_format']))->save();

            Cache::forget('system_datetime_format');
            Cache::forget('system_timezone');

            return successResponse(Lang::get('message.updated-successfully'));
        } catch (Exception $exception) {
            return errorResponse($exception->getMessage());
        }
    }

    public function getModuleSettings(Request $request): \Illuminate\Http\JsonResponse
    {
        try {
            $status = StatusSetting::first();

            $all = [
                ['key' => 'gcaptchastatus',        'slug' => 'recaptcha',         'name' => Lang::get('message.recaptcha_heading'),               'description' => Lang::get('message.google_description'),               'enabled' => (bool) $status?->recaptcha_status,        'route' => '/settings/api/recaptcha'],
                ['key' => 'mstatus',               'slug' => 'msg91',             'name' => Lang::get('message.msg91_heading'),                   'description' => Lang::get('message.msg91_description'),               'enabled' => (bool) $status?->msg91_status,            'route' => '/settings/api/msg91'],
                ['key' => 'mailchimpstatus',        'slug' => 'mailchimp',         'name' => Lang::get('message.mailchimp'),                'description' => Lang::get('message.mailchimp_description'),           'enabled' => (bool) $status?->mailchimp_status,        'route' => '/settings/api/mailchimp'],
                ['key' => 'termsStatus',            'slug' => 'terms',             'name' => Lang::get('message.terms_heading'),                   'description' => Lang::get('message.terms_description'),               'enabled' => (bool) $status?->terms,                   'route' => '/settings/api/terms'],
                ['key' => 'pipedrivestatus',        'slug' => 'pipedrive',         'name' => Lang::get('message.pipedrive'),               'description' => Lang::get('message.pipedrive_description'),           'enabled' => (bool) $status?->pipedrive_status,        'route' => '/settings/api/pipedrive'],
                ['key' => 'githubstatus',           'slug' => 'github',            'name' => Lang::get('message.github_heading'),                  'description' => Lang::get('message.github_description'),              'enabled' => (bool) $status?->github_status,           'route' => '/settings/api/github'],
                ['key' => 'email_validation_status', 'slug' => 'email-validation',  'name' => Lang::get('message.email_provider'),                  'description' => Lang::get('message.email_validation_description'),    'enabled' => (bool) $status?->email_validation_status, 'route' => '/settings/api/email-validation'],
                ['key' => 'mobile_validation_status', 'slug' => 'mobile-validation', 'name' => Lang::get('message.mobile_provider'),                 'description' => Lang::get('message.mobile_validation_description'),   'enabled' => (bool) $status?->mobile_validation_status, 'route' => '/settings/api/mobile-validation'],
                ['key' => 'whatsapp_status',        'slug' => 'whatsapp',          'name' => Lang::get('message.whatsapp_config'),                  'description' => Lang::get('message.whatsapp_thirdParty_explanation'), 'enabled' => (bool) $status?->whatsapp_status,         'route' => '/settings/whatsapp-integration'],
                ['key' => 'zoho',                   'slug' => 'zoho',              'name' => Lang::get('message.zoho_integration'),                'description' => Lang::get('message.zoho_description'),                'enabled' => true,                                              'route' => '/settings/api/zoho', 'settings_only' => true],
            ];

            foreach ($all as $index => &$item) {
                $item['id'] = $index + 1;
            }

            unset($item);

            $search = trim((string) $request->input('search-query', ''));
            if ($search !== '') {
                $all = array_values(array_filter($all, fn (array $m): bool => stripos((string) $m['name'], $search) !== false ||
                    stripos((string) $m['description'], $search) !== false
                ));
            }

            $total = count($all);
            $perPage = max(1, (int) $request->input('limit', 10));
            $page = max(1, (int) $request->input('page', 1));
            $offset = ($page - 1) * $perPage;
            $items = array_slice($all, $offset, $perPage);
            $lastPage = max(1, (int) ceil($total / $perPage));
            $base = $request->url();

            return successResponse('', [
                'data' => $items,
                'total' => $total,
                'per_page' => $perPage,
                'current_page' => $page,
                'from' => $total > 0 ? $offset + 1 : null,
                'to' => $total > 0 ? min($offset + $perPage, $total) : null,
                'next_page_url' => $page < $lastPage ? $base.'?page='.($page + 1).'&limit='.$perPage : null,
                'prev_page_url' => $page > 1 ? $base.'?page='.($page - 1).'&limit='.$perPage : null,
            ]);
        } catch (Exception $exception) {
            return errorResponse($exception->getMessage());
        }
    }

    public function getPipedriveSettings(): \Illuminate\Http\JsonResponse
    {
        try {
            $groups = PipedriveGroups::whereIn('group_name', ['Person', 'Organization', 'Deal'])
                ->pluck('id', 'group_name');

            return successResponse('', [
                'status' => (bool) StatusSetting::value('pipedrive_status'),
                'pipedrive_key' => ApiKey::value('pipedrive_api_key'),
                'require_pipedrive_user_verification' => (bool) ApiKey::value('require_pipedrive_user_verification'),
                'groups' => [
                    'personId' => $groups['Person'] ?? null,
                    'organizationId' => $groups['Organization'] ?? null,
                    'dealId' => $groups['Deal'] ?? null,
                ],
            ]);
        } catch (Exception $exception) {
            return errorResponse($exception->getMessage());
        }
    }

    public function updatePipedriveSettings(Request $request): \Illuminate\Http\JsonResponse
    {
        try {
            StatusSetting::findOrFail(1)->update(['pipedrive_status' => $request->boolean('status')]);
            ApiKey::findOrFail(1)->update([
                'pipedrive_api_key' => $request->input('pipedrive_key'),
                'require_pipedrive_user_verification' => $request->boolean('require_pipedrive_user_verification'),
            ]);

            return successResponse(Lang::get('message.pipedrive_setting'));
        } catch (Exception $exception) {
            return errorResponse($exception->getMessage());
        }
    }

    public function getCronSettingsData(): \Illuminate\Http\JsonResponse
    {
        try {
            $status = StatusSetting::find(1);
            $days = ExpiryMailDay::first();
            $activityDay = ActivityLogDay::first();
            $conditions = Condition::pluck('value', 'job');

            return successResponse('', [
                'cron_path' => base_path('artisan'),
                'exec_enabled' => $this->execEnabled(),
                'php_paths' => $this->getPHPBinPath(),
                'statuses' => [
                    'expiry_cron' => (bool) $status?->expiry_mail,
                    'activity' => (bool) $status?->activity_log_delete,
                    'subs_expirymail' => (bool) $status?->subs_expirymail,
                    'postsubs_expirymail' => (bool) $status?->post_expirymail,
                    'cloud_cron' => (bool) $status?->cloud_mail_status,
                    'invoice_cron' => (bool) $status?->invoice_deletion_status,
                    'msg91_cron' => (bool) $status?->msg91_report_delete_status,
                    'reoon_cron' => (bool) $status?->reoon_deletion_status,
                    'systemlogs_cron' => (bool) $status?->system_log_status,
                    'installationlogs_cron' => (bool) $status?->installation_logs_status,
                    'licensereports_cron' => (bool) $status?->license_reports_cleanup_status,
                    'licensecallbacks_cron' => (bool) $status?->license_callbacks_cleanup_status,
                    'licensecrack_cron' => (bool) $status?->license_crack_reports_cleanup_status,
                    'licensesystem_cron' => (bool) $status?->license_system_reports_cleanup_status,
                    'licenseversions_cron' => (bool) $status?->license_versions_cleanup_status,
                ],
                'conditions' => $conditions,
                'days' => [
                    'expiryday' => json_decode((string) $days?->days ?: '[]', associative: true),
                    'subexpiryday' => json_decode((string) $days?->autorenewal_days ?: '[]', associative: true),
                    'postsubexpiry_days' => json_decode((string) $days?->postexpiry_days ?: '[]', associative: true),
                    'cloud_days' => $days?->cloud_days,
                    'invoice_days' => $days?->invoice_days,
                    'msg91_days' => $days?->msg91_days,
                    'reoon_days' => $days?->reoon_logs_days,
                    'system_logs_days' => $days?->system_logs_days,
                    'installation_logs_days' => $days?->installation_logs_expire_days,
                    'license_reports_days' => $days?->license_reports_cleanup_days,
                    'license_callbacks_days' => $days?->license_callbacks_cleanup_days,
                    'license_crack_days' => $days?->license_crack_reports_cleanup_days,
                    'license_system_days' => $days?->license_system_reports_cleanup_days,
                    'license_versions_days' => $days?->license_versions_cleanup_days,
                    'logdelday' => $activityDay?->days,
                ],
            ]);
        } catch (Exception $exception) {
            return errorResponse($exception->getMessage());
        }
    }

    public function updateCronSettingsData(Request $request): \Illuminate\Http\JsonResponse
    {
        try {
            $status = StatusSetting::findOrFail(1);
            $map = [
                'expiry_cron' => 'expiry_mail',
                'activity' => 'activity_log_delete',
                'subs_expirymail' => 'subs_expirymail',
                'postsubs_expirymail' => 'post_expirymail',
                'cloud_cron' => 'cloud_mail_status',
                'invoice_cron' => 'invoice_deletion_status',
                'msg91_cron' => 'msg91_report_delete_status',
                'reoon_cron' => 'reoon_deletion_status',
                'systemlogs_cron' => 'system_log_status',
                'installationlogs_cron' => 'installation_logs_status',
                'licensereports_cron' => 'license_reports_cleanup_status',
                'licensecallbacks_cron' => 'license_callbacks_cleanup_status',
                'licensecrack_cron' => 'license_crack_reports_cleanup_status',
                'licensesystem_cron' => 'license_system_reports_cleanup_status',
                'licenseversions_cron' => 'license_versions_cleanup_status',
            ];
            foreach ($map as $input => $column) {
                $status->{$column} = $request->boolean('statuses.'.$input);
            }

            $status->save();

            Condition::truncate();
            foreach ($request->input('conditions', []) as $job => $value) {
                Condition::create(['job' => $job, 'value' => $value]);
            }

            return successResponse(Lang::get('message.updated-successfully'));
        } catch (Exception $exception) {
            return errorResponse($exception->getMessage());
        }
    }

    public function updateCronDaysData(Request $request): \Illuminate\Http\JsonResponse
    {
        try {
            ExpiryMailDay::truncate();
            ExpiryMailDay::create([
                'days' => json_encode($request->input('expiryday', [])),
                'autorenewal_days' => json_encode($request->input('subexpiryday', [])),
                'postexpiry_days' => json_encode($request->input('postsubexpiry_days', [])),
            ]);

            DB::table('expiry_mail_days')->update([
                'cloud_days' => $request->input('cloud_days'),
                'invoice_days' => $request->input('invoice_days'),
                'msg91_days' => $request->input('msg91_days'),
                'reoon_logs_days' => $request->input('reoon_days'),
                'system_logs_days' => $request->input('system_logs_days'),
                'installation_logs_expire_days' => $request->input('installation_logs_days'),
                'license_reports_cleanup_days' => $request->input('license_reports_days'),
                'license_callbacks_cleanup_days' => $request->input('license_callbacks_days'),
                'license_crack_reports_cleanup_days' => $request->input('license_crack_days'),
                'license_system_reports_cleanup_days' => $request->input('license_system_days'),
                'license_versions_cleanup_days' => $request->input('license_versions_days'),
            ]);
            ActivityLogDay::updateOrCreate(['id' => 1], ['days' => $request->input('logdelday')]);

            return successResponse(Lang::get('message.updated-successfully'));
        } catch (Exception $exception) {
            return errorResponse($exception->getMessage());
        }
    }

    public function getCloudDetails(): \Illuminate\Http\JsonResponse
    {
        try {
            $cloud = FaveoCloud::find(1);
            $cloudPopUp = CloudPopUp::find(1);

            $products = Product::orderBy('name')->get(['id', 'name'])
                ->map(fn ($p): array => ['id' => $p->id, 'name' => $p->name]);

            $plans = Plan::orderBy('name')->get(['id', 'name'])
                ->map(fn ($p): array => ['id' => $p->id, 'name' => $p->name]);

            $countries = Country::where('country_name', '!=', '')
                ->orderBy('country_name')
                ->get(['country_code_char2', 'country_name'])
                ->map(fn ($c): array => ['code' => strtolower((string) $c->country_code_char2), 'name' => $c->country_name]);

            $regions = CloudDataCenters::all()
                ->map(fn ($r): array => [
                    'name' => implode(', ', array_filter([$r->cloud_city, $r->cloud_state, $r->cloud_countries])),
                    'latitude' => (float) $r->latitude,
                    'longitude' => (float) $r->longitude,
                ]);

            return successResponse('', [
                'cloud_central_domain' => $cloud?->cloud_central_domain,
                'cloud_cname' => $cloud?->cloud_cname,
                'cloud_button' => (bool) StatusSetting::value('cloud_button'),
                'cloud_top_message' => $cloudPopUp->cloud_top_message ?? '',
                'cloud_label_field' => $cloudPopUp->cloud_label_field ?? '',
                'cloud_label_radio' => $cloudPopUp->cloud_label_radio ?? '',
                'products' => $products,
                'plans' => $plans,
                'countries' => $countries,
                'regions' => $regions,
            ]);
        } catch (Exception $exception) {
            return errorResponse($exception->getMessage());
        }
    }

    public function getBody(int $id): \Illuminate\Http\JsonResponse
    {
        try {
            /** @phpstan-ignore class.notFound */
            $email = Email_log::findOrFail($id);

            return successResponse('', ['body' => $email->body, 'subject' => $email->subject]);
        } catch (Exception $exception) {
            return errorResponse($exception->getMessage());
        }
    }

    public function getActivityApi(Request $request): \Illuminate\Http\JsonResponse
    {
        try {
            $searchString = $request->input('search-query', '');
            $sortField = $request->input('sort-field', 'created_at');
            $sortOrder = $request->input('sort-order', 'desc');
            $limit = $request->input('limit', 10);

            $query = Activity::query()
                ->leftJoin('users', 'activity_log.causer_id', '=', 'users.id')
                ->select(
                    'activity_log.id',
                    'activity_log.log_name',
                    'activity_log.description',
                    'activity_log.event',
                    'activity_log.properties',
                    'activity_log.causer_id',
                    'activity_log.created_at',
                    'users.first_name',
                    'users.last_name',
                    'users.email',
                    'users.role as user_role'
                );

            $query = $this->filterQuery($query);

            if ($searchString) {
                $query->where(function ($q) use ($searchString): void {
                    $q->where('activity_log.log_name', 'like', sprintf('%%%s%%', $searchString))
                        ->orWhere('activity_log.event', 'like', sprintf('%%%s%%', $searchString))
                        ->orWhere('activity_log.description', 'like', sprintf('%%%s%%', $searchString))
                        ->orWhere('users.email', 'like', sprintf('%%%s%%', $searchString))
                        ->orWhereRaw("CONCAT(users.first_name, ' ', users.last_name) LIKE ?", [sprintf('%%%s%%', $searchString)]);
                });
            }

            $allowedActivitySorts = ['created_at', 'module', 'event', 'performed_by', 'role'];
            if (! in_array($sortField, $allowedActivitySorts, strict: true)) {
                $sortField = 'created_at';
            }

            $activitySortMap = [
                'performed_by' => 'users.first_name',
                'role' => 'users.role',
                'module' => 'activity_log.log_name',
            ];
            $activitySortColumn = $activitySortMap[$sortField] ?? 'activity_log.'.$sortField;
            $logs = $query->orderBy($activitySortColumn, $sortOrder)->simplePaginate($limit);

            $logs->getCollection()->transform(fn ($row): array => [
                'id' => $row->id,
                'module' => $row->log_name ?? '—',
                'event' => ucfirst($row->event ?? '—'),
                'description' => $row->description ?? '—',
                'detailed_properties' => $this->formatProperties($row->properties, $row->event),
                'performed_by' => $row->first_name ? trim($row->first_name.' '.$row->last_name) : __('message.system'),
                'performed_by_id' => $row->causer_id ?? null,
                'email' => $row->email ?? '',
                'role' => ucfirst($row->user_role ?? '—'),
                'created_at' => $row->created_at ? $row->created_at->format('Y-m-d H:i') : '—',
            ]);

            return successResponse('', $logs);
        } catch (Exception $exception) {
            return errorResponse($exception->getMessage());
        }
    }

    public function getActivityFilters(): \Illuminate\Http\JsonResponse
    {
        try {
            $modules = Activity::distinct()->pluck('log_name')->filter()->values();

            $userIds = Activity::distinct()->pluck('causer_id')->filter();
            $users = User::select('id', 'first_name', 'last_name', 'email')
                ->whereIn('id', $userIds)
                ->orderBy('first_name')
                ->get()
                ->map(fn ($u): array => [
                    'id' => $u->id,
                    'name' => trim($u->first_name.' '.$u->last_name).' <'.$u->email.'>',
                ]);

            return successResponse('', ['modules' => $modules, 'users' => $users]);
        } catch (Exception $exception) {
            return errorResponse($exception->getMessage());
        }
    }

    public function getPaymentLogApi(Request $request): \Illuminate\Http\JsonResponse
    {
        try {
            $searchString = $request->input('search-query', '');
            $sortField = $request->input('sort-field', 'date');
            $sortOrder = $request->input('sort-order', 'desc');
            $limit = $request->input('limit', 10);

            $query = Payment_log::query()
                ->leftJoin('users', 'payment_logs.from', '=', 'users.email')
                ->select(
                    'payment_logs.id',
                    'payment_logs.from',
                    'payment_logs.date',
                    'payment_logs.status',
                    'payment_logs.payment_method',
                    'payment_logs.order',
                    'payment_logs.exception',
                    'payment_logs.amount',
                    'payment_logs.payment_type',
                    'payment_logs.created_at',
                    'users.id as user_id',
                    DB::raw("CONCAT(users.first_name, ' ', users.last_name) as user_name")
                );

            if ($status = $request->input('status')) {
                $query->where('payment_logs.status', $status);
            }

            if ($dateFrom = $request->input('date_from')) {
                $query->where('payment_logs.date', '>=', Date::parse($dateFrom)->startOfDay());
            }

            if ($dateTill = $request->input('date_till')) {
                $query->where('payment_logs.date', '<=', Date::parse($dateTill)->endOfDay());
            }

            if ($searchString) {
                $query->where(function (Builder $q) use ($searchString): void {
                    $q->where('payment_logs.order', 'like', sprintf('%%%s%%', $searchString))
                        ->orWhere('payment_logs.status', 'like', sprintf('%%%s%%', $searchString))
                        ->orWhere('payment_logs.payment_method', 'like', sprintf('%%%s%%', $searchString))
                        ->orWhere('payment_logs.from', 'like', sprintf('%%%s%%', $searchString));
                });
            }

            $allowedPaymentSorts = ['date', 'amount', 'status', 'order', 'payment_method', 'payment_type', 'user'];
            if (! in_array($sortField, $allowedPaymentSorts, strict: true)) {
                $sortField = 'date';
            }

            $paymentSortMap = ['user' => 'users.first_name'];
            $paymentSortColumn = $paymentSortMap[$sortField] ?? 'payment_logs.'.$sortField;
            $logs = $query->orderBy($paymentSortColumn, $sortOrder)->simplePaginate($limit);

            $logs->getCollection()->transform(fn ($row): array => [
                'id' => $row->id,
                'date' => $row->date ? Date::parse($row->date)->format('Y-m-d H:i') : '—',
                'user' => $row->user_name ? trim((string) $row->user_name) : ($row->from ?? '—'),
                'user_id' => $row->user_id,
                'order' => $row->order ?? '—',
                'amount' => $row->amount ?? '—',
                'payment_method' => ucfirst($row->payment_method ?? '—'),
                'payment_type' => ucfirst($row->payment_type ?? '—'),
                'status' => ucfirst($row->status ?? '—'),
                'exception' => $row->status === 'failed' ? $row->exception : null,
            ]);

            return successResponse('', $logs);
        } catch (Exception $exception) {
            return errorResponse($exception->getMessage());
        }
    }

    public function mailSearch(string $from = '', string $till = ''): \Illuminate\Database\Eloquent\Builder
    {
        /** @phpstan-ignore class.notFound */
        $join = Email_log::select('id', 'from', 'to', 'date', 'subject', 'status');

        if ($from) {
            $from = $this->DateFormat($from);
            $tillDate = $this->DateFormat($till ?: $this->DateFormat()); // Use $till if provided, otherwise, use current date
            $join = $join->whereBetween('date', [$from, $tillDate]);
        }

        if ($till) {
            $till = $this->DateFormat($till);
            /** @phpstan-ignore class.notFound */
            $fromDate = Email_log::first()->date;
            $fromDate = $this->DateFormat($from ?: $fromDate); // Use $from if provided, otherwise, use the first email log date
            $join = $join->whereBetween('date', [$fromDate, $till]);
        }

        return $join;
    }

    public function postSettingsError(Setting $settings, Request $request): \Illuminate\Http\JsonResponse
    {
        try {
            $setting = $settings->find(1);
            $setting->fill($request->only(['error_log', 'error_email']))->save();

            return successResponse(Lang::get('message.updated-successfully'));
        } catch (Exception) {
            return errorResponse(Lang::get('message.err_msg'));
        }
    }

    public function debugSettings(): \Illuminate\Http\JsonResponse
    {
        return successResponse('', [
            'debug' => (bool) commonSettings('debugging', 'app_debug'),
            'pulse_enabled' => (bool) commonSettings('debugging', 'pulse_enabled'),
            'clockwork_enable' => (bool) commonSettings('debugging', 'clockwork_enable'),
            'sentry_reporting' => (bool) commonSettings('sentry', 'crash_reporting'),
            'sentry_performance' => (bool) commonSettings('sentry', 'performance_monitoring'),
        ]);
    }

    public function postdebugSettings(Request $request): \Illuminate\Http\JsonResponse
    {
        $bool = fn (string $key): string => $request->boolean($key) ? '1' : '0';

        CommonSettings::upsert([
            ['option_name' => 'debugging', 'optional_field' => 'app_debug',              'option_value' => $bool('debug')],
            ['option_name' => 'debugging', 'optional_field' => 'pulse_enabled',          'option_value' => $bool('pulse_enabled')],
            ['option_name' => 'debugging', 'optional_field' => 'clockwork_enable',       'option_value' => $bool('clockwork_enable')],
            ['option_name' => 'sentry',    'optional_field' => 'crash_reporting',        'option_value' => $bool('sentry_reporting')],
            ['option_name' => 'sentry',    'optional_field' => 'performance_monitoring', 'option_value' => $bool('sentry_performance')],
        ], ['option_name', 'optional_field'], ['option_value']);

        Cache::forget('debugging_settings');

        $tracesRate = $request->boolean('sentry_performance') ? 0.1 : 0;

        config([
            'app.debug' => $request->boolean('debug'),
            'pulse.enabled' => $request->boolean('pulse_enabled'),
            'clockwork.enable' => $request->boolean('clockwork_enable'),
            'app.sentry_reporting' => $request->boolean('sentry_reporting'),
            'sentry.traces_sample_rate' => $tracesRate,
        ]);

        if (app()->bound(HubInterface::class)) {
            resolve(HubInterface::class)
                ->getClient()
                ?->getOptions()
                ->setTracesSampleRate($tracesRate ?: null);
        }

        return successResponse(Lang::get('message.updated-successfully'));
    }

    public function paymentSearch(string $from = '', string $till = ''): \Illuminate\Database\Eloquent\Builder
    {
        $join = Payment_log::query()->leftJoin('users', 'payment_logs.from', '=', 'users.email')
            ->select('payment_logs.id', 'from', 'to', 'date', 'subject', 'status', 'payment_logs.created_at', 'payment_method', 'order', 'exception', 'email', DB::raw("CONCAT(first_name, ' ', last_name) as name"), 'users.id', 'payment_logs.id as count', 'amount', 'payment_type');

        if ($from || $till) {
            $fromDate = $from
                ? Date::parse($this->DateFormat($from))->startOfDay()
                : Date::parse(Payment_log::oldest('date')->value('date'))->startOfDay();

            $tillDate = $till
                ? Date::parse($this->DateFormat($till))->endOfDay()
                : Date::now()->endOfDay();

            $join->whereBetween('date', [$fromDate, $tillDate]);
        }

        return $join;
    }

    private function DateFormat(?string $date = null): string
    {
        if ($date === null) {
            return date('Y-m-d H:i:s');
        }

        return date('Y-m-d H:i:s', strtotime($date));
    }

    public function destroyPayment(Request $request): \Illuminate\Http\JsonResponse
    {
        try {
            $ids = $request->input('ids', []);

            if (empty($ids)) {
                return errorResponse(__('message.select-a-row'));
            }

            Payment_log::whereIn('id', $ids)->delete();

            return successResponse(__('message.deleted-successfully'));
        } catch (Exception $exception) {
            return errorResponse($exception->getMessage());
        }
    }

    public function contactOption(): \Illuminate\Http\JsonResponse
    {
        return successResponse('', [
            'sending_status' => Setting::value('sending_status'),
            'emailverification_status' => StatusSetting::value('emailverification_status'),
            'msg91_status' => StatusSetting::value('msg91_status'),
            'verification_preference' => ApiKey::value('verification_preference'),
        ]);
    }

    public function postContactOption(Request $request): \Illuminate\Http\JsonResponse
    {
        $data = $request->only(['email_enabled', 'mobile_enabled', 'preferred_verification']);

        StatusSetting::query()->first()?->update([
            'emailverification_status' => $data['email_enabled'],
            'msg91_status' => $data['mobile_enabled'],
        ]);
        ApiKey::query()->first()?->update([
            'verification_preference' => $data['preferred_verification'] ?? null,
        ]);

        return successResponse(__('message.contact_setting_update'));
    }

    public function emailCheckboxData(): \Illuminate\Http\JsonResponse
    {
        $current = EmailMobileValidationProviders::where('provider', 'reoon')->value('accepted_output') ?? 1;
        $statusOptions = $this->setStatus($current);

        $response = '<div class="form-group">
            <label for="allowed_statuses" class="required">'.__('message.allowed_estatus').'</label>'
            .$statusOptions.
            '</div>
            <span class="error invalid-feedback d-block" id="checkboxErrorMessage"></span>';

        return successResponse(trans('message.success'), $response);
    }

    public function listEmailValidationLogs(Request $request): \Illuminate\Http\JsonResponse
    {
        try {
            $query = EmailValidationResults::select(['id', 'email', 'method', 'status', 'registration', 'created_at']);

            if ($search = $request->input('search-query')) {
                $query->where(function ($q) use ($search): void {
                    $q->where('email', 'like', sprintf('%%%s%%', $search))
                      ->orWhere('method', 'like', sprintf('%%%s%%', $search))
                      ->orWhere('status', 'like', sprintf('%%%s%%', $search));
                });
            }

            $sortField = $request->input('sort-field', 'created_at');
            if (! in_array($sortField, ['email', 'method', 'status', 'registration', 'created_at'])) {
                $sortField = 'created_at';
            }

            $query->orderBy($sortField, $request->input('sort-order', 'desc') === 'asc' ? 'asc' : 'desc');

            return successResponse('', $query->paginate($request->input('limit', 10))
                ->through(fn ($r): array => array_merge($r->only(['id', 'email', 'registration', 'created_at']), [
                    'method' => ucfirst((string) $r->method),
                    'status' => ucfirst((string) $r->status),
                ])));
        } catch (Exception $exception) {
            return errorResponse($exception->getMessage());
        }
    }

    public function getEmailValidationResults(Request $request): \Illuminate\Http\JsonResponse
    {
        try {
            $id = $request->input('id');
            $result = EmailValidationResults::where('id', $id)->first();

            $cont1 = json_decode((string) $result->result, associative: true);
            $cont2 = ['name' => $result->first_name.' '.$result->last_name,
                'mobile Number' => '+'.$result->mobile_code.$result->mobile,
                'email' => $result->email,
                'company Name' => $result->company,
                'address' => $result->address,
                'country' => Country::where('country_code_char2', $result->country)->value('country_name'),
                'state' => State::where('state_subdivision_id', $result->state)->value('primary_level_name'),
                'city' => $result->town, ];
            $final = ($result->first_name && $result->last_name) ? array_merge($cont2, $cont1) : $cont1;

            return successResponse(trans('message.success'), $final);
        } catch (Exception $exception) {
            return errorResponse($exception->getMessage());
        }
    }

    public function getEmailValidationUserResults(Request $request): \Illuminate\Http\JsonResponse
    {
        try {
            $id = $request->input('id');
            $result = EmailValidationResults::where('id', $id)->first();
            $content = ['name' => $result->first_name.' '.$result->last_name,
                'mobile Number' => '+'.$result->mobile_code.$result->mobile,
                'email' => $result->email,
                'company Name' => $result->company,
                'address' => $result->address,
                'country' => Country::where('country_code_char2', $result->country)->value('nicename'),
                'state' => State::where('state_subdivision_code', $result->state)->value('state_subdivision_name'),
                'city' => $result->town, ];

            return successResponse(trans('message.success'), $content);
        } catch (Exception $exception) {
            dd($exception->getMessage());
        }
    }

    private function setStatus(int $current): string
    {
        $map = [
            'safe' => 1,
            'catch_all' => 2,
            'unknown' => 4,
            'invalid' => 8,
            'disabled' => 16,
            'disposable' => 32,
            'inbox_full' => 64,
            'role_account' => 128,
            'spamtrap' => 256,
        ];

        $statusOptions = '';
        foreach ($map as $status => $bit) {
            $checked = (($current & $bit) !== 0) ? 'checked' : '';
            $label = ucfirst(str_replace('_', ' ', $status));
            $statusOptions .= '<div class="form-check">
        <input class="form-check-input emailStatusCheckbox" type="checkbox" 
               name="allowed_statuses[]" value="'.$bit.'" id="status_'.$status.'" '.$checked.'>
        <label class="form-check-label" for="status_'.$status.'">'.$label.'</label>
    </div>';
        }

        return $statusOptions;
    }

    public function emailSettingsSave(Request $request): \Illuminate\Http\JsonResponse
    {
        $emailSave = new EmailMobileValidationProviders();

        $response = Http::get('https://emailverifier.reoon.com/api/v1/check-account-balance/', [
            'key' => $request->input('apikey'),
        ]);
        $content = $response->json();
        if ($content['status'] === 'error') {
            return errorResponse(trans('message.emailApikey_error'));
        }

        $emailSave->where('type', 'email')->update(['to_use' => 0]);
        $apikey = trim((string) $request->input('apikey'));
        try {
            $accepted_output = $request->input('mode') == 'quick' ? $emailSave->where('type', 'email')->value('accepted_output') : $request->input('accepted_output');
            $emailMobileProvider = EmailMobileValidationProviders::where('provider', $request->input('provider'))->firstOrFail();
            $emailMobileProvider->update(['api_key' => $apikey,
                'mode' => $request->input('mode'), 'accepted_output' => $accepted_output, 'to_use' => 1]);
            StatusSetting::where('id', 1)->update(['email_validation_status' => 1]);

            return successResponse(trans('message.email_validation_success'));
        } catch (Exception) {
            return errorResponse(Lang::get('message.invalid_key'));
        }
    }

    public function mobileSettingsSave(Request $request): ?\Illuminate\Http\JsonResponse
    {
        $emailSave = new EmailMobileValidationProviders();
        $provider = $request->input('provider');
        $apikey = trim((string) $request->input('apikey'));
        $apisecret = trim((string) $request->input('apisecret'));
        if ($provider == 'vonage') {
            $response = Http::get('https://rest.nexmo.com/account/get-balance/', [
                'api_key' => $apikey,
                'api_secret' => $apisecret,
            ]);
            if (! $response->successful() && ! $response->json('value')) {
                return errorResponse(trans('message.mobileApikey_error'));
            }

            $emailSave->where('type', 'mobile')->update(['to_use' => 0]);

            $emailSave->where('provider', $request->input('provider'))->update(['api_key' => $apikey,
                'mode' => $request->input('mode'), 'api_secret' => $apisecret, 'to_use' => 1]);
            StatusSetting::where('id', 1)->update(['mobile_validation_status' => 1]);

            return successResponse(Lang::get('message.mobile_validation_success'));
        }

        if ($provider == 'abstract') {
            $response = Http::get('https://phonevalidation.abstractapi.com/v1/', [
                'api_key' => $request->input('apikey'),
                'phone' => '+14155552671',
            ]);

            if (! $response->successful() && $response->json('error')) {
                return errorResponse(trans('message.mobileApikey_error'));
            }

            $emailSave->where('type', 'mobile')->update(['to_use' => 0]);

            $emailSave->where('provider', $request->input('provider'))->update(['api_key' => $request->input('apikey'), 'to_use' => 1]);
            StatusSetting::where('id', 1)->update(['mobile_validation_status' => 1]);

            return successResponse(Lang::get('message.mobile_validation_success_abstract'));
        }

        return null;
    }

    public function getMsg91Settings(): \Illuminate\Http\JsonResponse
    {
        try {
            $apiKey = ApiKey::first();
            $thirdPartyApps = ThirdPartyApp::orderBy('app_name')->get(['id', 'app_name', 'app_key', 'app_secret']);

            return successResponse('', [
                'msg91_auth_key' => $apiKey->msg91_auth_key ?? '',
                'msg91_sender' => $apiKey->msg91_sender ?? '',
                'msg91_template_id' => $apiKey->msg91_template_id ?? '',
                'third_party_id' => $apiKey->msg91_third_party_id ?? null,
                'third_party_apps' => $thirdPartyApps,
            ]);
        } catch (Exception $exception) {
            return errorResponse($exception->getMessage());
        }
    }

    public function getGithubSettings(): \Illuminate\Http\JsonResponse
    {
        try {
            $github = Github::first();

            return successResponse('', [
                'username' => $github->username ?? '',
                'password' => $github->password ?? '',
            ]);
        } catch (Exception $exception) {
            return errorResponse($exception->getMessage());
        }
    }

    public function getTermsSettings(): \Illuminate\Http\JsonResponse
    {
        try {
            return successResponse('', [
                'terms_url' => ApiKey::value('terms_url') ?? '',
            ]);
        } catch (Exception $exception) {
            return errorResponse($exception->getMessage());
        }
    }

    public function getEmailValidationSettings(): \Illuminate\Http\JsonResponse
    {
        try {
            $provider = EmailMobileValidationProviders::where('type', 'email')
                ->where('to_use', 1)
                ->first();

            if (! $provider) {
                $provider = EmailMobileValidationProviders::where('type', 'email')->first();
            }

            $statusBits = [1, 2, 4, 8, 16, 32, 64, 128, 256];
            $statusNames = ['safe', 'catch_all', 'unknown', 'invalid', 'disabled', 'disposable', 'inbox_full', 'role_account', 'spamtrap'];
            $current = (int) ($provider->accepted_output ?? 1);
            $selected = array_values(array_filter($statusBits, fn (int $b): bool => ($current & $b) === $b));

            return successResponse('', [
                'provider' => $provider->provider ?? 'reoon',
                'api_key' => $provider->api_key ?? '',
                'mode' => $provider->mode ?? 'quick',
                'accepted_output' => $current,
                'selected_bits' => $selected,
                'status_options' => array_map(fn ($b, $n): array => ['bit' => $b, 'name' => $n], $statusBits, $statusNames),
            ]);
        } catch (Exception $exception) {
            return errorResponse($exception->getMessage());
        }
    }

    public function getMobileValidationSettings(): \Illuminate\Http\JsonResponse
    {
        try {
            $provider = EmailMobileValidationProviders::where('type', 'mobile')
                ->where('to_use', 1)
                ->first();

            if (! $provider) {
                $provider = EmailMobileValidationProviders::where('type', 'mobile')->first();
            }

            return successResponse('', [
                'provider' => $provider->provider ?? 'vonage',
                'api_key' => $provider->api_key ?? '',
                'api_secret' => $provider->api_secret ?? '',
                'mode' => $provider->mode ?? 'basic',
            ]);
        } catch (Exception $exception) {
            return errorResponse($exception->getMessage());
        }
    }
}
