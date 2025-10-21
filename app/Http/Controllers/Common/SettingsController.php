<?php

namespace App\Http\Controllers\Common;

use App\ApiKey;
use App\Email_log;
use App\EmailValidationResults;
use App\Facades\Attach;
use App\Http\Controllers\BillingInstaller\InstallerController;
use App\Http\Controllers\Order\OrderSearchController;
use App\Http\Requests\Common\SettingsRequest;
use App\Model\Common\Country;
use App\Model\Common\EmailMobileValidationProviders;
use App\Model\Common\Mailchimp\MailchimpSetting;
use App\Model\Common\Setting;
use App\Model\Common\State;
use App\Model\Common\StatusSetting;
use App\Model\Common\Template;
use App\Model\Github\Github;
use App\Model\Mailjob\QueueService;
use App\Model\Order\Order;
use App\Model\Payment\Currency;
use App\Model\Plugin;
use App\Payment_log;
use App\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Http;
use Spatie\Activitylog\Models\Activity;
use Yajra\DataTables\DataTables;

class SettingsController extends BaseSettingsController
{
    public $apikey;

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

    public function settings(Setting $settings)
    {
        if (!$settings->where('id', '1')->first()) {
            $settings->create(['company' => '']);
        }
        $isRedisConfigured = QueueService::where('short_name', 'redis')->value('status');
        $mailSendingStatus = Setting::value('sending_status');

        return view('themes.default1.common.admin-settings', compact('isRedisConfigured', 'mailSendingStatus'));
    }

    public function plugins()
    {
        try {
            $payment = new PaymentSettingsController();
            $pay = $payment->fetchConfig();

            $status = Plugin::all();

            $response = [
                'payment_config' => $pay,
                'plugins' => $status,
            ];

            return successResponse(__('message.data-retrieved-successfully'), $response);

        } catch (\Exception $e) {
            return errorResponse($e->getMessage());
        }
    }

    /**
     * Get the Status and Api Keys for Settings Module.
     *
     * @param ApiKey $apikeys
     */
    public function licensekeys(ApiKey $apikeys)
    {
        [$licenseSecret, $licenseUrl, $licenseClientId, $licenseClientSecret, $licenseGrantType] = array_values($apikeys->select('license_api_secret',
            'license_api_url', 'license_client_id', 'license_client_secret', 'license_grant_type')->first()->toArray());
        $data = [
            'licenseGrantType' => $licenseGrantType,
            'licenseSecret' => $licenseSecret,
            'licenseClientId' => $licenseClientId,
            'licenseClientSecret' => $licenseClientSecret,
            'licenseUrl' => $licenseUrl,
        ];

        return successResponse('', $data);
    }

    public function mobileVerification(ApiKey $apikeys)
    {
        [$mobileauthkey, $msg91Sender, $msg91TemplateId, $msg91ThirdPartyId] = array_values($apikeys->select('msg91_auth_key', 'msg91_sender', 'msg91_template_id', 'msg91_third_party_id')->first()->toArray());

        $data = [
            'mobileauthkey' => $mobileauthkey,
            'msg91Sender' => $msg91Sender,
            'msg91TemplateId' => $msg91TemplateId,
            'selectedApp' => $msg91ThirdPartyId,
        ];

        return successResponse('', $data);
    }

    public function mailchimpKeys(ApiKey $apikeys)
    {
        $mailchimpSetting = StatusSetting::pluck('mailchimp_status')->first();

        [$mailchimpKey, $subscribe_status] = array_values(MailchimpSetting::select('api_key', 'subscribe_status')->first()->toArray());

        $mailchimp_set = new MailchimpSetting();
        $set = $mailchimp_set->firstOrFail();
        $mail_api_key = $set->api_key;
        try {
            $mailchimp_set = new MailchimpSetting();
            $set = $mailchimp_set->firstOrFail();
            $mail_api_key = $set->api_key;
            $mailchimp = new \Mailchimp\Mailchimp($mail_api_key);
            $allists = $mailchimp->get('lists?count=20')['lists'];
            $selectedList[] = $set->list_id;
        } catch (\Exception $e) {
            \Logger::exception($e);

            // Return null when it fails
            $mailchimp = '';
            $allists = [];
            $selectedList = [];
        }

        $data = [
            'mailchimpSetting' => $mailchimpSetting,
            'mailchimpKey' => $mailchimpKey,
            'allLists' => $allists,
            'selectedList' => $selectedList,
            'subscribe_status' => $subscribe_status,
        ];

        return successResponse('', $data);
    }

    public function termsUrl(ApiKey $apikeys)
    {
        $termsUrl = $apikeys->value('terms_url');

        $data = [
            'termsUrl' => $termsUrl,
        ];

        return successResponse('', $data);
    }

    public function twitterkeys(ApiKey $apikeys)
    {
        $twitterKeys = $apikeys->select('twitter_consumer_key', 'twitter_consumer_secret',
            'twitter_access_token', 'access_tooken_secret')->first();

        $data = [
            'twitterkeys' => $twitterKeys,

        ];

        return successResponse('', $data);
    }

    public function zohokeys(ApiKey $apikeys)
    {
        $zohoKey = $apikeys->value('zoho_api_key');

        $data = [
            'zohoKey' => $zohoKey,

        ];

        return successResponse('', $data);
    }

    public function pipedrivekeys(ApiKey $apikeys)
    {
        $pipedriveKey = $apikeys->value('pipedrive_api_key');

        $data = [
            'pipedriveKey' => $pipedriveKey,

        ];

        return successResponse('', $data);
    }

    public function githubkeys(ApiKey $apikeys)
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
        } catch (\Exception $e) {
            $data = [
                'githubFileds' => '',

            ];

            return successResponse('', $data);
        }
    }

    public function getKeys(ApiKey $apikeys)
    {
        try {
            $licenseSecret = $apikeys->pluck('license_api_secret')->first();
            $licenseUrl = $apikeys->pluck('license_api_url')->first();
            $licenseClientId = $apikeys->pluck('license_client_id')->first();
            $licenseClientSecret = $apikeys->pluck('license_client_secret')->first();
            $licenseGrantType = $apikeys->pluck('license_grant_type')->first();
            $status = StatusSetting::pluck('license_status')->first();
            $updateStatus = StatusSetting::pluck('update_settings')->first();
            $mobileStatus = StatusSetting::pluck('msg91_status')->first();
            $siteKey = $apikeys->pluck('nocaptcha_sitekey')->first();
            $secretKey = $apikeys->pluck('captcha_secretCheck')->first();
            $updateSecret = $apikeys->pluck('update_api_secret')->first();
            $mobileauthkey = $apikeys->pluck('msg91_auth_key')->first();
            $msg91Sender = $apikeys->pluck('msg91_sender')->first();
            $msg91TemplateId = $apikeys->pluck('msg91_template_id')->first();
            $updateUrl = $apikeys->pluck('update_api_url')->first();
            $twitterKeys = $apikeys->select('twitter_consumer_key', 'twitter_consumer_secret',
                'twitter_access_token', 'access_tooken_secret')->first();
            $twitterStatus = $this->statusSetting->pluck('twitter_status')->first();
            $zohoStatus = $this->statusSetting->pluck('zoho_status')->first();
            $zohoKey = $apikeys->pluck('zoho_api_key')->first();
            $rzpStatus = $this->statusSetting->pluck('rzp_status')->first();
            $rzpKeys = $apikeys->select('rzp_key', 'rzp_secret', 'apilayer_key')->first();
            $mailchimpSetting = StatusSetting::pluck('mailchimp_status')->first();
            $mailchimpKey = MailchimpSetting::pluck('api_key')->first();

            $termsStatus = StatusSetting::pluck('terms')->first();
            $termsUrl = $apikeys->pluck('terms_url')->first();
            $pipedriveKey = $apikeys->pluck('pipedrive_api_key')->first();
            $pipedriveStatus = StatusSetting::pluck('pipedrive_status')->first();
            $domainCheckStatus = StatusSetting::pluck('domain_check')->first();
            $mailSendingStatus = Setting::value('sending_status');
            $emailStatus = StatusSetting::pluck('emailverification_status')->first();
            $model = $apikeys->find(1);
            $mailchimp_set = new MailchimpSetting();
            $set = $mailchimp_set->firstOrFail();
            $mail_api_key = $set->api_key;
            try {
                $mailchimp = new \Mailchimp\Mailchimp($mail_api_key);
                $allists = $mailchimp->get('lists?count=20')['lists'];
                $selectedList[] = $set->list_id;
            } catch (\Exception $e) {
                \Logger::exception($e);
                $allists = [];
                $selectedList = [];
            }
            $model = new Github();
            $github = $model->firstOrFail();
            $githubStatus = StatusSetting::first()->github_status;
            $msg91ThirdPartyId = $apikeys->pluck('msg91_third_party_id')->first();
            $isPipedriveVerificationEnabled = ApiKey::value('require_pipedrive_user_verification');
            $selectedProvider = EmailMobileValidationProviders::where('type', 'mobile')->where('to_use', 1)->value('provider');

            return view('themes.default1.common.apikey', compact('model', 'status', 'licenseSecret', 'licenseUrl', 'siteKey', 'secretKey', 'updateStatus', 'updateSecret', 'updateUrl', 'mobileStatus', 'mobileauthkey', 'msg91Sender', 'msg91TemplateId', 'emailStatus', 'twitterStatus', 'twitterKeys', 'zohoStatus', 'zohoKey', 'rzpStatus', 'rzpKeys', 'mailchimpSetting', 'mailchimpKey', 'termsStatus', 'termsUrl', 'pipedriveKey', 'pipedriveStatus', 'domainCheckStatus', 'mailSendingStatus',
                'licenseClientId', 'licenseClientSecret', 'licenseGrantType', 'allists', 'selectedList', 'set', 'githubStatus', 'msg91ThirdPartyId', 'isPipedriveVerificationEnabled', 'selectedProvider'));
        } catch (\Exception $ex) {
            return redirect('/')->with('fails', $ex->getMessage());
        }
    }

    public function getDataTableData(Request $request)
    {
        $status = $this->statusSetting->value('license_status');
        $mobileStatus = $this->statusSetting->value('msg91_status');
        $captchaStatus = $this->statusSetting->value('recaptcha_status');
        $twitterStatus = $this->statusSetting->value('twitter_status');
        $zohoStatus = $this->statusSetting->value('zoho_status');
        $pipedriveStatus = $this->statusSetting->value('pipedrive_status');
        $domainCheckStatus = $this->statusSetting->value('domain_check');
        $githubStatus = $this->statusSetting->first()->github_status;
        $mailchimpSetting = $this->statusSetting->value('mailchimp_status');
        $termsStatus = $this->statusSetting->value('terms');
        $checkboxValue = $captchaStatus ? '1' : '0';
        $checked = $captchaStatus ? 'checked' : '';
        $emailStatus = $this->statusSetting->value('email_validation_status');
        $mobileValStatus = $this->statusSetting->value('mobile_validation_status');
        $whatsappStatus = $this->statusSetting->value('whatsapp_status');
        $toggleSwitch = '
        <label class="switch toggle_event_editing gcaptcha">
            <input type="checkbox" value="' . $checkboxValue . '"  
                   name="modules_settings"
                   class="checkbox2" id="captcha" ' . $checked . '>
            <span class="slider round"></span>
        </label>
    ';
        $mobileAction = $mobileStatus ? '<button id="msg91-edit-button" class="btn btn-sm btn-secondary btn-xs"><span class="nav-icon fa fa-fw fa-edit"></span></button>' : '';
        $licenseAction = $status ? '<button id="license-edit-button" class="btn btn-sm btn-secondary btn-xs" ><span class="nav-icon fa fa-fw fa-edit"></span></button>' : '';
        $mailchimpAction = $mailchimpSetting ? '<button id="mailchimp-edit-button" class="btn btn-sm btn-secondary btn-xs"><span class="nav-icon fa fa-fw fa-edit"></span></button>' : '';
        $termsAction = $termsStatus ? '<button id="termsUrl-edit-button" class="btn btn-sm btn-secondary btn-xs"><span class="nav-icon fa fa-fw fa-edit"></span></button>' : '';
        $pipedriveAction = $pipedriveStatus ? '<button id="pipedrive-edit-button" class="btn btn-sm btn-secondary btn-xs"><span class="nav-icon fa fa-fw fa-edit"></span></button>' : '';
        $githubAction = $githubStatus ? '<button id="github-edit-button" class="btn btn-sm btn-secondary btn-xs"><span class="nav-icon fa fa-fw fa-edit"></span></button>' : '';
        $recaptchaAction = $captchaStatus ? '<button id="captcha-edit-button" class="btn btn-sm btn-secondary btn-xs" ><span class="nav-icon fa fa-fw fa-edit"></span></button>' : '';
        $emailValidationAction = $emailStatus ? '<button id="emailValidation-edit-button" class="btn btn-sm btn-secondary btn-xs" ><span class="nav-icon fa fa-fw fa-edit"></span></button>' : '';
        $mobileValidationAction = $mobileValStatus ? '<button id="mobileValidation-edit-button" class="btn btn-sm btn-secondary btn-xs" ><span class="nav-icon fa fa-fw fa-edit"></span></button>' : '';
        $whatsappAction = $whatsappStatus ? '<button id="whatsapp-edit-button" class="btn btn-sm btn-secondary btn-xs" ><span class="nav-icon fa fa-fw fa-edit"></span></button>' : '';

        if ($request->ajax()) {
            $dataTable = collect([
                ['options' => \Lang::get('message.license_heading'), 'description' => \Lang::get('message.license_description'), 'status' => '
        <label class="switch toggle_event_editing licenser">
            <input type="checkbox" value="' . ($status ? '1' : '0') . '"  
                   name="modules_settings"
                   class="checkbox" id="License" ' . ($status ? 'checked' : '') . '>
            <span class="slider round"></span>
        </label>
    ', 'action' => $licenseAction,
                ],
                ['options' => \Lang::get('message.recaptcha_heading'), 'description' => \Lang::get('message.google_description'), 'status' => $toggleSwitch, 'action' => $recaptchaAction,
                ],
                ['options' => \Lang::get('message.msg91_heading'), 'description' => \Lang::get('message.msg91_description'), 'status' => '<label class="switch toggle_event_editing mstatus">
                    <input type="checkbox" value="' . ($mobileStatus ? '1' : '0') . '"  name="mobile_settings"
                           class="checkbox4" id="mobile"' . ($mobileStatus ? 'checked' : '') . '>
                    <span class="slider round"></span>
                    </label>', 'action' => $mobileAction,
                ],
                ['options' => \Lang::get('message.mailchimp_heading'), 'description' => \Lang::get('message.mailchimp_description'), 'status' => '<label class="switch toggle_event_editing mailchimpstatus">
                        <input type="checkbox" value="' . ($mailchimpSetting ? '1' : '0') . '"  name="mobile_settings"
                               class="checkbox9" id="mailchimp"' . ($mailchimpSetting ? 'checked' : '') . '>
                        <span class="slider round"></span>
                    </label>', 'action' => $mailchimpAction,
                ],
                ['options' => \Lang::get('message.terms_heading'), 'description' => \Lang::get('message.terms_description'), 'status' => '<label class="switch toggle_event_editing termstatus1">

                        <input type="checkbox" value="' . ($termsStatus ? '1' : '0') . '"  name="terms_settings"
                               class="checkbox10" id="terms"' . ($termsStatus ? 'checked' : '') . '>
                        <span class="slider round"></span>
                    </label>', 'action' => $termsAction,
                ],
                ['options' => \Lang::get('message.pipedrive_heading'), 'description' => \Lang::get('message.pipedrive_description'), 'status' => '<label class="switch toggle_event_editing pipedrivestatus">
                        <input type="checkbox" value="' . ($pipedriveStatus ? '1' : '0') . '"  name="pipedrive_settings"
                           class="checkbox13" id="pipedrive"' . ($pipedriveStatus ? 'checked' : '') . '>
                        <span class="slider round"></span>
                    </label>', 'action' => $pipedriveAction,
                ],
                ['options' => \Lang::get('message.github_heading'), 'description' => \Lang::get('message.github_description'), 'status' => '<label class="switch toggle_event_editing githubstatus">
                            <input type="checkbox" value="' . ($githubStatus ? '1' : '0') . '" name="github_settings" class="checkbox" id="github"' . ($githubStatus ? 'checked' : '') . '>
                            <span class="slider round"></span>
                        </label>', 'action' => $githubAction,
                ],
                ['options' => \Lang::get('message.email_provider'), 'description' => \Lang::get('message.email_validation_description'), 'status' => '<label class="switch toggle_event_editing emailValidationStatus">
                        <input type="checkbox" value="' . ($emailStatus ? '1' : '0') . '"  name="EmailValidationStatus"
                               class="checkboxEmail" id="email_validation_status"' . ($emailStatus ? 'checked' : '') . '>
                        <span class="slider round"></span>
                    </label>', 'action' => $emailValidationAction,
                ],
                ['options' => \Lang::get('message.mobile_provider'), 'description' => \Lang::get('message.mobile_validation_description'), 'status' => '<label class="switch toggle_event_editing mobileValidationStatus">
                        <input type="checkbox" value="' . ($mobileValStatus ? '1' : '0') . '"  name="mobileValidationStatus"
                               class="checkbox9" id="mobile_validation_status"' . ($mobileValStatus ? 'checked' : '') . '>
                        <span class="slider round"></span>
                    </label>', 'action' => $mobileValidationAction,
                ],
                ['options' => \Lang::get('message.whatsapp_config'), 'description' => \Lang::get('message.whatsapp_thirdParty_explanation'), 'status' => '<label class="switch toggle_event_editing whatsapp_status">
                        <input type="checkbox" value="'.($whatsappStatus ? '1' : '0').'"  name="whatsapp_status"
                               class="checkbox11 whatsapp_status" id="whatsapp_status"'.($whatsappStatus ? 'checked' : '').'>
                        <span class="slider round"></span>
                    </label>', 'action' => $whatsappAction, ],
            ]);

            return DataTables::of($dataTable)
                ->rawColumns(['status', 'action'])
                ->make(true);
        }
    }

    private function getStatus($value)
    {
        if ($value == 1) {
            return 'Active';
        } else {
            return 'Inactive';
        }
    }

    private function getStatus2($value, $value2)
    {
        if (!$value && !$value2) {
            return 'Inactive';
        } else {
            return 'Active';
        }
    }

    public function postKeys(ApiKey $apikeys, Request $request)
    {
        try {
            $keys = $apikeys->find(1);
            $keys->fill($request->input())->save();

            return redirect()->back()->with('success', \Lang::get('message.updated-successfully'));
        } catch (\Exception $ex) {
            return redirect()->back()->with('fails', $ex->getMessage());
        }
    }

    /**
     * PAyment Gateway that is shown on the basis of currency.
     *
     * @param string $currency The currency of the Product Selected
     * @return string Name of the Payment Gateway
     */
    public static function checkPaymentGateway($currency)
    {
        try {
            $active_plugins = Plugin::where('status', 1)->get();
            $allAcivePluginName = [];
            if ($active_plugins) {
                foreach ($active_plugins as $plugin) {
                    if (isCurrencySupportedForPayments($currency, strtolower($plugin->name))) {
                        $allAcivePluginName[] = $plugin->name;
                    }
                }
            }

            return $allAcivePluginName;
        } catch (\Exception $ex) {
            return redirect()->back()->with('fails', $ex->getMessage());
        }
    }

    public function settingsSystem(Setting $settings)
    {
        try {
            $set = $settings->find(1);
            $state = getStateByCode($set->country, $set->state);
            $selectedCountry = \DB::table('countries')->where('country_code_char2', $set->country)
                ->pluck('country_name', 'country_code_char2')->toArray();
            $selectedCurrency = \DB::table('currencies')->where('code', $set->default_currency)
                ->pluck('name', 'symbol')->toArray();
            $states = findStateByRegionId($set->country);
            $response = (new InstallerController())->languageList();
            $languages = $response->getData()->data ?? [];
            $defaultLang = optional(Setting::first())->content;

            $settings = Setting::with([
                    'defaultCurrency:id,code,name',
                    'country:country_id,country_name,country_code_char2',
                    'state:state_subdivision_id,state_subdivision_name,state_subdivision_code',
                    'language:id,name,locale'
                ]
            )->findOrFail(1);
            return successResponse( __('message.system_setting_fetched'), $settings);
        } catch (\Exception $ex) {
            return redirect()->back()->with('fails', $ex->getMessage());
        }
    }

    public function postSettingsSystem(Setting $settings, SettingsRequest $request)
    {
        try {
            $setting = $settings->find(1);
            $input = $request->input();
            $input['autorenewal_status'] = isset($input['autorenewal_status']) ? 1 : 0;
            if ($request->hasFile('logo')) {
                $path = Attach::put('images', $request->file('logo'), null, true);
                $setting->logo = basename($path);
            }

            if ($request->hasFile('admin-logo')) {
                $path = Attach::put('admin/images', $request->file('admin-logo'), null, true);
                $setting->admin_logo = basename($path);
            }

            if ($request->hasFile('fav-icon')) {
                $path = Attach::put('common/images', $request->file('fav-icon'), null, true);
                $setting->fav_icon = basename($path);
            }

            $setting->default_symbol = Currency::where('code', $request->input('default_currency'))
                ->pluck('symbol')->first();
            $setting->content = $request->input('language');

            $setting->fill(Arr::except($input, ['password', 'logo', 'admin-logo', 'fav-icon']))->save();

            return successResponse(__('message.updated-successfully'));
        } catch (\Exception $ex) {
            return redirect()->back()->with('fails', $ex->getMessage());
        }
    }

    /**
     * Get the id and value of the column.
     *
     * Remove the logo from the DB and local storage.
     */
    public function delete(Request $request)
    {
        try {
            if (isset($request->id)) {
                $todo = Setting::findOrFail($request->id);
                if ($request->column == 'logo') {
                    $logoPath = $todo->logo;
                    Attach::delete('images/' . $logoPath);
                    $todo->logo = null;
                }
                if ($request->column == 'admin') {
                    $adminLogoPath = $todo->admin_logo;
                    Attach::delete('admin/images/' . $adminLogoPath);
                    $todo->admin_logo = null;
                }
                if ($request->column == 'fav') {
                    $favIconPath = $todo->fav_icon;
                    Attach::delete('common/images' . $favIconPath);
                    $todo->fav_icon = null;
                }
                $todo->save();
                $response = ['type' => 'success', 'message' => __('message.logo_deleted_successfully')];

                return response()->json($response);
            }
        } catch (\Exception $ex) {
            $result = [$ex->getMessage()];

            return response()->json(compact('result'), 500);
        }
    }

    public function settingsEmail(Setting $settings)
    {
        try {
            $set = $settings->find(1);

            return view('themes.default1.common.setting.email', compact('set'));
        } catch (\Exception $ex) {
            return redirect()->back()->with('fails', $ex->getMessage());
        }
    }

    public function settingsTemplate(Setting $settings)
    {
        try {
            $set = $settings->find(1);
            $template = new Template();

            //$templates = $template->lists('name', 'id')->toArray();
            return view('themes.default1.common.setting.template', compact('set', 'template'));
        } catch (\Exception $ex) {
            return redirect()->back()->with('fails', $ex->getMessage());
        }
    }

    public function postSettingsTemplate(Setting $settings, Request $request)
    {
        try {
            $setting = $settings->find(1);
            $setting->fill($request->input())->save();

            return redirect()->back()->with('success', \Lang::get('message.updated-successfully'));
        } catch (\Exception $ex) {
            return redirect()->back()->with('fails', $ex->getMessage());
        }
    }

    public function settingsError(Setting $settings)
    {
        try {
            $set = $settings->find(1);

            return view('themes.default1.common.setting.error-log', compact('set'));
        } catch (\Exception $ex) {
            return redirect()->back()->with('fails', $ex->getMessage());
        }
    }

    public function settingsActivity(Request $request, Activity $activities)
    {
        $validator = \Validator::make($request->all(), [
            'from' => 'nullable|date',
            'till' => 'nullable|date|after:from',
        ]);

        if ($validator->fails()) {
            return redirect('settings/activitylog')
                ->with('fails', __('message.start_date_before_end_date'));
        }

        try {
            $from = $request->input('log_from');
            $till = $request->input('log_till');

            // Get distinct module names from activity logs
            $modules = $activities->query()
                ->select('log_name')
                ->distinct()
                ->pluck('log_name')
                ->filter()
                ->values();

            // Get distinct events from activity logs
            $events = $activities->query()
                ->select('event')
                ->distinct()
                ->pluck('event')
                ->filter()
                ->values();

            // Get users who performed actions (join with users table)
            $users = User::select('id', 'first_name', 'last_name', 'email')
                ->whereIn('id', $activities->query()->distinct()->pluck('causer_id'))
                ->orderBy('first_name')
                ->get();

            return view('themes.default1.common.Activity-Log', compact('from', 'till', 'modules', 'events', 'users'));
        } catch (\Exception $ex) {
            return redirect()->back()->with('fails', $ex->getMessage());
        }
    }

    public function getBody($id)
    {
        try {
            $email = Email_log::findOrFail($id);

            return successResponse('', ['body' => $email->body, 'subject' => $email->subject]);
        } catch (\Exception $ex) {
            return errorResponse($ex->getMessage());
        }
    }

    public function getActivity(Request $request)
    {
        try {
            $baseQuery = Activity::query()
                ->leftJoin('users', 'activity_log.causer_id', '=', 'users.id')
                ->select(
                    'activity_log.id',
                    'activity_log.log_name',
                    'activity_log.description',
                    'activity_log.event',
                    'activity_log.causer_type',
                    'activity_log.causer_id',
                    'activity_log.created_at',
                    'activity_log.properties',
                    'users.first_name',
                    'users.last_name',
                    'users.email',
                    'users.role as user_role'
                )
                ->with(['causer:id,user_name,role,first_name,last_name,email']);

            $baseQuery = $this->filterQuery($baseQuery);

            if ($search = $request->input('search.value')) {
                $baseQuery->where(function ($query) use ($search) {
                    $query->where('activity_log.log_name', 'like', "%{$search}%")
                        ->orWhere('activity_log.event', 'like', "%{$search}%")
                        ->orWhere('activity_log.description', 'like', "%{$search}%")
                        ->orWhere('users.first_name', 'like', "%{$search}%")
                        ->orWhere('users.last_name', 'like', "%{$search}%")
                        ->orWhere('users.email', 'like', "%{$search}%")
                        ->orWhere('users.role', 'like', "%{$search}%")
                        ->orWhereRaw("CONCAT(users.first_name, ' ', users.last_name) LIKE ?", ["%{$search}%"]);
                });
            }

            return \DataTables::of($baseQuery)
                ->addColumn('module', fn ($row) => $row->log_name ?? '---')
                ->addColumn('event', fn ($row) => ucfirst($row->event ?? '---'))
                ->addColumn('role', fn ($row) => ucfirst($row->user_role ?? '---'))
                ->addColumn('detailed_properties', fn ($row) => $this->formatProperties($row->properties, $row->event))
                ->addColumn('performed_by', fn ($row) => $this->generateLinkForPerformedBy($row->causer) ?? __('message.system'))
                ->addColumn('created_at', fn ($row) => $row->created_at ? getDateHtml($row->created_at) : '---')
                ->addColumn('description', fn ($row) => $row->description ?? '---')

                ->orderColumn('module', 'activity_log.log_name $1')
                ->orderColumn('event', 'activity_log.event $1')
                ->orderColumn('role', 'users.role $1')
                ->orderColumn('description', 'activity_log.description $1')
                ->orderColumn('created_at', 'activity_log.created_at $1')

                ->rawColumns(['performed_by', 'created_at', 'description'])
                ->make(true);
        } catch (\Exception $e) {
            return errorResponse($e->getMessage());
        }
    }

    public function getMails(Request $request)
    {
        try {
            $from = $request->input('from');
            $till = $request->input('till');

            $email_log = $this->mailSearch($from, $till);

            return Datatables::of($email_log)
                ->orderColumn('date', '-date $1')
                ->orderColumn('from', '-date $1')
                ->orderColumn('to', '-date $1')
                ->orderColumn('subject', '-date $1')
                ->addColumn('checkbox', function ($model) {
                    return "<input type='checkbox' class='email' value=" . $model->id . ' name=select[] id=check>';
                })
                ->addColumn('date', function ($model) {
                    $date = $model->date;

                    return getDateHtml($date);
                })
                ->addColumn('from', function ($model) {
                    return $model->from;
                })
                ->addColumn('to', function ($model) {
                    $id = User::where('email', $model->to)->value('id');

                    return '<a href=' . url('clients/' . $id) . '>' . ucfirst($model->to) . '<a>';
                })
                ->addColumn('subject', function ($model) {
                    return '<a href="#" class="text-primary view-mail" data-id="'.$model->id.'">'.e(ucfirst($model->subject)).'</a>';
                })
                ->rawColumns(['checkbox', 'date', 'from', 'to',
                    'bcc', 'subject', 'status',])
                ->filterColumn('from', function ($query, $keyword) {
                    $sql = '`from` like ?';
                    $query->whereRaw($sql, ["%{$keyword}%"]);
                })
                ->filterColumn('to', function ($query, $keyword) {
                    $sql = '`to` like ?';
                    $query->whereRaw($sql, ["%{$keyword}%"]);
                })
                ->filterColumn('subject', function ($query, $keyword) {
                    $sql = '`subject` like ?';
                    $query->whereRaw($sql, ["%{$keyword}%"]);
                })
                ->filterColumn('status', function ($query, $keyword) {
                    $sql = '`status` like ?';
                    $query->whereRaw($sql, ["%{$keyword}%"]);
                })
                ->rawColumns(['checkbox', 'date', 'from', 'to',
                    'bcc', 'subject', 'status',])
                ->make(true);
        } catch (\Exception $e) {
            return redirect()->back()->with('fails', $e->getMessage());
        }
    }

    public function mailSearch($from = '', $till = '')
    {
        $join = Email_log::select('id', 'from', 'to', 'date', 'subject', 'status');

        if ($from) {
            $from = $this->DateFormat($from);
            $tillDate = $this->DateFormat($till ?: $this->DateFormat()); // Use $till if provided, otherwise, use current date
            $join = $join->whereBetween('date', [$from, $tillDate]);
        }

        if ($till) {
            $till = $this->DateFormat($till);
            $fromDate = Email_log::first()->date;
            $fromDate = $this->DateFormat($from ?: $fromDate); // Use $from if provided, otherwise, use the first email log date
            $join = $join->whereBetween('date', [$fromDate, $till]);
        }

        return $join;
    }

    public function destroy(Request $request)
    {
        try {
            $ids = $request->input('select');
            if (!empty($ids)) {
                foreach ($ids as $id) {
                    $activity = Activity::where('id', $id)->first();
                    if ($activity) {
                        $activity->delete();
                    } else {
                        echo "<div class='alert alert-danger alert-dismissable'>
                        <i class='fa fa-ban'></i>

                        <b>" . /* @scrutinizer ignore-type */ \Lang::get('message.alert') . '!</b> ' .
                            /* @scrutinizer ignore-type */ \Lang::get('message.failed') . '

                        <button type=button class=close data-dismiss=alert aria-hidden=true>&times;</button>
                            ' . /* @scrutinizer ignore-type */ \Lang::get('message.no-record') . '
                    </div>';
                        //echo \Lang::get('message.no-record') . '  [id=>' . $id . ']';
                    }
                }
                echo "<div class='alert alert-success alert-dismissable'>
                        <i class='fa fa-ban'></i>
                        <b>" . /* @scrutinizer ignore-type */ \Lang::get('message.alert') . '!</b> '
                    . /* @scrutinizer ignore-type */ \Lang::get('message.success') . '
                        <button type=button class=close data-dismiss=alert aria-hidden=true>&times;</button>
                            ' . /* @scrutinizer ignore-type */ \Lang::get('message.deleted-successfully') . '
                    </div>';
            } else {
                echo "<div class='alert alert-danger alert-dismissable'>
                        <i class='fa fa-ban'></i>
                        <b>" . /* @scrutinizer ignore-type */ \Lang::get('message.alert') .
                    '!</b> ' . /* @scrutinizer ignore-type */ \Lang::get('message.failed') . '
                        <button type=button class=close data-dismiss=alert aria-hidden=true>&times;</button>
                            ' . /* @scrutinizer ignore-type */ \Lang::get('message.select-a-row') . '
                    </div>';
                //echo \Lang::get('message.select-a-row');
            }
        } catch (\Exception $e) {
            echo "<div class='alert alert-danger alert-dismissable'>
                        <i class='fa fa-ban'></i>
                        <b>" . /* @scrutinizer ignore-type */ \Lang::get('message.alert') . '!</b> ' .
                /* @scrutinizer ignore-type */ \Lang::get('message.failed') . '
                        <button type=button class=close data-dismiss=alert aria-hidden=true>&times;</button>
                            ' . \Lang::get('message.err_msg.') . '
                    </div>';
        }
    }

    public function postSettingsError(Setting $settings, Request $request)
    {
        try {
            $setting = $settings->find(1);
            $setting->fill($request->input())->save();

            return redirect()->back()->with('success', \Lang::get('message.updated-successfully'));
        } catch (\Exception $ex) {
            return redirect()->back()->with('fails', \Lang::get('message.err_msg'));
        }
    }

    public function debugSettings()
    {
        return view('themes.default1.common.setting.debugging');
    }

    public function postdebugSettings(Request $request)
    {
        $enable = $request->get('debug') === 'true';
        setEnvValue([
            'APP_DEBUG' => $enable ? 'true' : 'false',
            'PULSE_ENABLED' => $enable ? 'true' : 'false',
            'CLOCKWORK_ENABLE' => $enable ? 'true' : 'false',
        ]);

        return successResponse(__('message.updated-successfully'));
    }

    public function settingsPayment(Setting $settings, Request $request)
    {
        try {
            $from = $request->input('from');
            $till = $request->input('till');

            return view('themes.default1.common.payment-log', compact('from', 'till'));
        } catch (\Exception $ex) {
            return redirect()->back()->with('fails', \Lang::get('message.err_msg'));
        }
    }

    public function getPaymentLog(Request $request)
    {
        try {
            $from = $request->input('from');
            $till = $request->input('till');
            $search = $request->input('search_query', '');
            $sortField = $request->input('sort_field', 'date');
            $sortOrder = $request->input('sort_order', 'desc');
            $limit = $request->input('limit', 10);

            // Base payment search logic
            $query = $this->paymentLogData($from, $till);

            // Search filter
            if (!empty($search)) {
                $query->where(function ($q) use ($search) {
                    $q->whereHas('orderDetails', function ($sub) use ($search) {
                        $sub->where('number', 'like', "%{$search}%");
                    })
                        ->orWhere('status', 'like', "%{$search}%")
                        ->orWhere('order', 'like', "%{$search}%")
                        ->orWhere('payment_type', 'like', "%{$search}%")
                        ->orWhere('payment_method', 'like', "%{$search}%")
                        ->orWhereHas('user', function ($sub) use ($search) {
                            $sub->where('email', 'like', "%{$search}%")
                                ->orWhere('user_name', 'like', "%{$search}%")
                                ->orWhereRaw("CONCAT(first_name, ' ', last_name) LIKE ?", ["%{$search}%"]);
                        });
                });
            }

            // Sorting
            $logs = $query->orderBy($sortField, $sortOrder)->simplePaginate($limit);
            $total = $query->count();

            // Transform
            $logs->getCollection()->transform(function ($log) {
                $userName = $log->user ? trim($log->user->first_name . ' ' . $log->user->last_name) : null;
                return [
                    'id' => $log->id,
                    'order_number' => $log->order,
                    'order_link' => $log->orderDetails ? $this->hyperLinkGenerator('orders/' . $log->orderDetails->id, $log->order): null,
                    'payment_email' => $log->from,
                    'user_name' => $userName,
                    'user_email' => $log->user ? $log->user->email : null,
                    'user_link' => $log->user ? $this->hyperLinkGenerator('clients/' . $log->user->id, $userName) : null,
                    'amount' => $log->amount,
                    'description' => ucfirst($log->payment_type),
                    'payment_method' => ucfirst($log->payment_method),
                    'status' => ucfirst($log->status),
                    'exception' => $log->exception,
                    'date' => $log->date,
                ];
            });

            return successResponse( __('message.payment_logs_retrieved'), [
                'logs' => $logs,
                'total' => $total,
            ]);

        } catch (\Exception $e) {
            return errorResponse( $e->getMessage());
        }
    }

    public function paymentLogData($from = '', $till = '')
    {
        $query = Payment_log::with([
            'user:id,first_name,last_name,email,user_name',
            'orderDetails'
        ])->select([
            'id',
            'from',
            'to',
            'date',
            'subject',
            'status',
            'created_at',
            'payment_method',
            'order',
            'exception',
            'amount',
            'payment_type'
        ]);

        // Apply date filter if any date is provided
        if ($from || $till) {

            // If only one date is provided, use it for both "from" and "till"
            $from = $from ?: $till;
            $till = $till ?: $from;

            // Convert dates to UTC format
            $fromUtc = toFormatDateAndTime($from);
            $tillUtc = toFormatDateAndTime($till);


            // If only date provided (no time), include the entire day
            $fromUtc = strlen($from) <= 10 ? $fromUtc->startOfDay() : $fromUtc;
            $tillUtc = strlen($till) <= 10 ? $tillUtc->endOfDay() : $tillUtc;

            $query->whereBetween('created_at', [$fromUtc, $tillUtc]);
        }

        return $query;
    }

    private function DateFormat($date = null)
    {
        if ($date === null) {
            return date('Y-m-d H:i:s');
        }

        return date('Y-m-d H:i:s', strtotime($date));
    }

    public function destroyPayment(Request $request)
    {
        try {
            $ids = $request->input('select');

            if (empty($ids) || !is_array($ids)) {
                return errorResponse( __('message.select-a-row'),400);
            }

            $deleted = Payment_log::whereIn('id', $ids)->delete();

            if ($deleted) {
                return successResponse( __('message.deleted-successfully'));

            } else {
                return errorResponse( __('message.no_record_found'),404);
            }

        } catch (\Exception $e) {
           return errorResponse( $e->getMessage() );
        }
    }


    public function contactOption()
    {
        try {
            $mailSendingStatus = Setting::value('sending_status');
            $emailStatus = StatusSetting::value('emailverification_status');
            $mobileStatus = StatusSetting::value('msg91_status');
            $preferred_verification = ApiKey::value('verification_preference');

            return successResponse(__('message.contact_options_retrieved'), [
                'mailSendingStatus' => $mailSendingStatus,
                'emailStatus' => $emailStatus,
                'mobileStatus' => $mobileStatus,
                'preferred_verification' => $preferred_verification,
            ]);

        } catch (\Exception $e) {
            return errorResponse($e->getMessage());
        }
    }

    public function postContactOption(Request $request)
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

    public function emailData(Request $request)
    {
        ['api_key' => $apikey, 'mode' => $mode, 'accepted_output' => $current] = EmailMobileValidationProviders::where('provider', $request->input('value'))
            ->select('api_key', 'mode', 'accepted_output')
            ->first()
            ->toArray();

        $label2 = html()->label(__('message.emailApikey'), 'emailApikey')->class('required')->toHtml();
        $input = html()->text('emailApikey', $apikey)->class('form-control emailapikey')->id('emailApikey')->toHtml();
        $label1 = html()->label(__('message.emailMode'), 'emailMode')->class('required')->toHtml();
        $input1 = html()->text('emailMode', $mode)->class('form-control emailMode')->id('emailMode')->toHtml();
        $input3 = '<select class="form-control emailMode" id="emailMode" name="emailMode">'
            . '<option value="quick"' . ($mode == 'quick' ? ' selected' : '') . '>Quick</option>'
            . '<option value="power"' . ($mode == 'power' ? ' selected' : '') . '>Power</option>'
            . '</select>';

        if ($request->input('value') === 'reoon') {
            $response = '<div>
        <div class="form-group">' . $label2 . $input . '</div>
        <div class="form-group">' . $label1 . $input3 . '</div>
         <div class="form-group" id="checkboxToRender">
                </div>
        
            </div>';
            if ($mode == 'power') {
                $statusOptions = $this->setStatus($current);
                $response = '<div>
        <div class="form-group">' . $label2 . $input . '</div>
        <div class="form-group">' . $label1 . $input3 . '</div>
         <div class="form-group" id="checkboxToRender">
         <div class="form-group">
            <label for="allowed_statuses" class="required">' . __('message.allowed_estatus') . '</label>'
                    . $statusOptions .
                    '</div>
                </div>
                <span class="error invalid-feedback d-block" id="checkboxErrorMessage"></span>
            </div>';
            }
        } else {
            $response = '';
        }

        return successResponse(trans('message.success'), $response);
    }

    public function emailCheckboxData()
    {
        $current = EmailMobileValidationProviders::where('provider', 'reoon')->value('accepted_output') ?? 1;
        $statusOptions = $this->setStatus($current);

        $response = '<div class="form-group">
            <label for="allowed_statuses" class="required">' . __('message.allowed_estatus') . '</label>'
            . $statusOptions .
            '</div>
            <span class="error invalid-feedback d-block" id="checkboxErrorMessage"></span>';

        return successResponse(trans('message.success'), $response);
    }

    public function getEmailValidationLogs()
    {
        try {
            $query = EmailValidationResults::query();

            return \DataTables::of($query)
                ->orderColumn('email', function ($query, $order) {
                    $query->orderBy('email', $order);
                })
                ->orderColumn('method', function ($query, $order) {
                    $query->orderBy('method', $order);
                })
                ->orderColumn('status', function ($query, $order) {
                    $query->orderBy('status', $order);
                })
                ->orderColumn('registration', function ($query, $order) {
                    $query->orderBy('registration', $order);
                })
                ->orderColumn('created_at', function ($query, $order) {
                    $query->orderBy('created_at', $order);
                })
                ->addColumn('email', function ($query) {
                    return $query->email;
                })
                ->addColumn('method', function ($query) {
                    return ucfirst($query->method);
                })
                ->addColumn('status', function ($query) {
                    return ucfirst($query->status);
                })
                ->addColumn('result', function ($query) {
                    return  '<button  class="btn btn-light-scale-2 btn-sm text-dark" id="show-results" data-id='.$query->id.' data-toggle="tooltip" data-placement="top" title="'.__('message.click_here_view').'"><i class="fa fa-eye"></i></button>';
                })
                ->addColumn('registration', function ($query) {
                    return $query->registration;
                })
                ->addColumn('created_at', function ($query) {
                    return getDateHtml($query->created_at);
                })

                ->filterColumn('email', function ($query, $keyword) {
                    $query->whereRaw('email like ?', ["%{$keyword}%"]);
                })

                ->filterColumn('method', function ($query, $keyword) {
                    $query->whereRaw('method like ?', ["%{$keyword}%"]);
                })
                ->filterColumn('status', function ($query, $keyword) {
                    $query->whereRaw('status like ?', ["%{$keyword}%"]);
                })
                ->rawColumns(['email', 'method', 'status', 'result', 'created_at'])
                ->make(true);
        } catch (\Exception $e) {
            return errorResponse($e->getMessage());
        }
    }

    public function getEmailValidationResults(Request $request)
    {
        try {
            $id = $request->input('id');
            $result = EmailValidationResults::where('id', $id)->first();

            $cont1 = json_decode($result->result, true);
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
        } catch (\Exception $e) {
            return errorResponse($e->getMessage());
        }
    }

    public function getEmailValidationUserResults(Request $request)
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
        } catch (\Exception $e) {
            dd($e->getMessage());
        }
    }

    private function setStatus($current)
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
            $checked = ($current & $bit) ? 'checked' : '';
            $label = ucfirst(str_replace('_', ' ', $status));
            $statusOptions .= '<div class="form-check">
        <input class="form-check-input emailStatusCheckbox" type="checkbox" 
               name="allowed_statuses[]" value="' . $bit . '" id="status_' . $status . '" ' . $checked . '>
        <label class="form-check-label" for="status_' . $status . '">' . $label . '</label>
    </div>';
        }

        return $statusOptions;
    }

    public function mobileData(Request $request)
    {
        $provider = $request->input('value');

        ['api_key' => $apikey, 'mode' => $mode, 'api_secret' => $apisecret] = EmailMobileValidationProviders::where('provider', $provider)
            ->select('api_key', 'mode', 'api_secret')
            ->first()
            ->toArray();
        $label2 = html()->label(__('message.mobileApikey'), 'emailApikey')->class('required')->toHtml();
        $input = html()->text('apikey', $apikey)->class('form-control emailapikey')->id('mobileApikey')->toHtml();
        $label1 = html()->label(__('message.mobileApisecret'), 'apisecret')->class('required')->toHtml();
        $input1 = html()->text('apisecret', $apisecret)->class('form-control emailMode')->id('mobileApisecret')->toHtml();
        $label3 = html()->label(__('message.mobileMode'), 'mobileMode')->class('required')->toHtml();
        $input3 = html()->text('mobileMode', $mode)->class('form-control mobileMode')->id('mobileMode')->toHtml();
        $input4 = '<select class="form-control emailMode" id="mobileMode" name="mobileMode">'
            . '<option value="basic"' . ($mode == 'basic' ? ' selected' : '') . '>Basic</option>'
            . '<option value="standard"' . ($mode == 'standard' ? ' selected' : '') . '>Standard</option>'
            . '<option value="advanced/async"' . ($mode == 'advanced/async' ? ' selected' : '') . '>Advanced</option>'
            . '</select>';
        if ($provider == 'vonage') {
            $response = '<div>
        <div class="form-group">' . $label2 . $input . '</div>
        <div class="form-group">' . $label1 . $input1 . '</div>
        <div class="form-group">' . $label3 . $input4 . '</div>
    </div>';
        } else {
            $response = '<div>
        <div class="form-group">' . $label2 . $input . '</div>
    </div>';
        }

        return successResponse(trans('message.success'), $response);
    }

    public function emailSettingsSave(Request $request)
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
        $apikey = trim($request->input('apikey'));
        try {
            $accepted_output = $request->input('mode') == 'quick' ? $emailSave->where('type', 'email')->value('accepted_output') : $request->input('accepted_output');
            $emailMobileProvider = EmailMobileValidationProviders::where('provider', $request->input('provider'))->firstOrFail();
            $emailMobileProvider->update(['api_key' => $apikey,
                'mode' => $request->input('mode'), 'accepted_output' => $accepted_output, 'to_use' => 1]);

            return successResponse(trans('message.email_validation_success'));
        } catch (\Exception $e) {
            return errorResponse(\Lang::get('message.invalid_key'));
        }
    }

    public function mobileSettingsSave(Request $request)
    {
        $emailSave = new EmailMobileValidationProviders();
        $provider = $request->input('provider');
        $apikey = trim($request->input('apikey'));
        $apisecret = trim($request->input('apisecret'));
        if ($provider == 'vonage') {
            $response = Http::get('https://rest.nexmo.com/account/get-balance/', [
                'api_key' => $apikey,
                'api_secret' => $apisecret,
            ]);
            if (!$response->successful() && !$response->json('value')) {
                return errorResponse(trans('message.mobileApikey_error'));
            }
            $emailSave->where('type', 'mobile')->update(['to_use' => 0]);

            $emailSave->where('provider', $request->input('provider'))->update(['api_key' => $apikey,
                'mode' => $request->input('mode'), 'api_secret' => $apisecret, 'to_use' => 1]);

            return successResponse(\Lang::get('message.mobile_validation_success'));
        }

        if ($provider == 'abstract') {
            $response = Http::get('https://phonevalidation.abstractapi.com/v1/', [
                'api_key' => $request->input('apikey'),
                'phone' => '+14155552671',
            ]);

            if (!$response->successful() && $response->json('error')) {
                return errorResponse(trans('message.mobileApikey_error'));
            }
            $emailSave->where('type', 'mobile')->update(['to_use' => 0]);

            $emailSave->where('provider', $request->input('provider'))->update(['api_key' => $request->input('apikey'), 'to_use' => 1]);

            return successResponse(\Lang::get('message.mobile_validation_success_abstract'));
        }
    }

//    public function getBaseQueryForSystemLogs($from = null, $till = null)
//    {
//        $query = Activity::with(['causer:id,user_name,first_name,last_name,email', 'causer.role'])
//            ->select('id', 'log_name', 'description', 'event', 'causer_id', 'properties', 'created_at');
//
//        if ($from || $till) {
//            $from = $from
//                ? Carbon::parse($from)->startOfDay()
//                : Carbon::parse(Activity::min('created_at'))->startOfDay();
//
//            $till = $till
//                ? Carbon::parse($till)->endOfDay()
//                : Carbon::now()->endOfDay();
//            \Log::info('Activity Log Filter:', [
//                'Applied From' => $from,
//                'Applied Till' => $till
//            ]);
//
//            // ✅ Remove extra UTC conversion
//            $query->whereBetween('created_at', [$from, $till]);
//            \Log::info('Generated SQL Query:', [$query->toSql(), $query->getBindings()]);
//
//        }
//
//        return $query;
//    }
//
//
//    private function searchQuery($query, $search)
//    {
//        if (!empty($search)) {
//            $query->where(function ($q) use ($search) {
//                $q->where('log_name', 'LIKE', "%$search%")
//                    ->orWhere('description', 'LIKE', "%$search%")
//                    ->orWhereHas('causer', function ($q) use ($search) {
//                        $q->where('first_name', 'LIKE', "%$search%")
//                            ->orWhere('last_name', 'LIKE', "%$search%")
//                            ->orWhere('user_name', 'LIKE', "%$search%")
//                            ->orWhereRaw("CONCAT(first_name,' ',last_name) LIKE ?", ["%$search%"]);
//                    });
//            });
//        }
//        return $query;
//    }
//
//    private function adSearch($from, $till, $query)
//    {
//        if ($from && $till) {
//            $query->whereBetween('created_at', [$from, $till]);
//        }
//        return $query;
//    }

    public function getActivity(Request $request)
    {
        try {
            $searchString = $request->input('search-query', '');
            $sortOrder = $request->input('sort-order', 'desc');
            $sortField = $request->input('sort-field', 'created_at');
            $limit = $request->input('limit', 10);
            $from = $request->input('log_from');
            $till = $request->input('log_till');

            //Load Base Query (already includes date filtering)
            $query = $this->getBaseQueryForSystemLogs($from, $till);

            //Search Filter
            $query = $this->searchQueryForActivityLogs($query, $searchString);

            $logs = $query->orderBy($sortField, $sortOrder)
                ->simplePaginate($limit);
            $total = $query->count();


            $logs->getCollection()->transform(function ($log) {
                return [
                    'id' => $log->id,
                    'name' => ucfirst($log->log_name),
                    'description' => ucfirst($log->description),
                    'username' => $log->causer_id ? User::where('id', $log->causer_id)->value('user_name') : null,
                    'role' => $log->causer_id ? User::where('id',$log->causer_id)->value('role') : null,
                    'new' => $this->getNewEntry($log->properties, $log),
                    'old' => $this->getOldEntry($log->properties, $log),
                    'created_at' => $log->created_at->format('Y-m-d H:i:s'),
                ];
            });

            return successResponse('Activity logs fetched successfully', [
                'logs' => $logs,
                'total' => $total
            ]);
        } catch (\Exception $e) {
            return errorResponse($e->getMessage());
        }
    }

    public function getBaseQueryForSystemLogs($from = null, $till = null)
    {
        $query = Activity::with(['causer:id,user_name,first_name,last_name,email', 'causer.role'])->select('id', 'log_name', 'description', 'event', 'causer_id', 'properties', 'created_at');

        try {
            if ($from || $till) {

                // If only one date is provided, use it for both "from" and "till"
                $from = $from ?: $till;
                $till = $till ?: $from;

                // Convert dates to UTC format
                $fromUtc = toFormatDateAndTime($from);
                $tillUtc = toFormatDateAndTime($till);


                // If only date provided (no time), include the entire day
                $fromUtc = strlen($from) <= 10 ? $fromUtc->startOfDay() : $fromUtc;
                $tillUtc = strlen($till) <= 10 ? $tillUtc->endOfDay() : $tillUtc;

            $query->whereBetween('created_at', [$fromUtc, $tillUtc]);
        }

            return $query;

    } catch (\Exception $e) {
                return errorResponse( $e->getMessage());
            }
    }

    private function searchQueryForActivityLogs($query, $search)
    {
        if (!empty($search)) {
            $query->where(function ($q) use ($search) {
                $q->where('log_name', 'LIKE', "%$search%")
                    ->orWhere('description', 'LIKE', "%$search%")
                    ->orWhereHas('causer', function ($q) use ($search) {
                        $q->where('first_name', 'LIKE', "%$search%")
                            ->orWhere('last_name', 'LIKE', "%$search%")
                            ->orWhere('user_name', 'LIKE', "%$search%")
                            ->orWhereRaw("CONCAT(first_name, ' ', last_name) LIKE ?", ["%$search%"]);
                    });
            });
        }
        return $query;
    }

    private function hyperLinkGenerator($href, $value): string
    {
        return "<a href='" . url($href) . "'>" . $value . "</a>";
    }


}
