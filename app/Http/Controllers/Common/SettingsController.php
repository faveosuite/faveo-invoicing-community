<?php

namespace App\Http\Controllers\Common;

use App\ApiKey;
use App\Email_log;
use App\Facades\Attach;
use App\Http\Requests\Common\SettingsRequest;
use App\Model\Common\Mailchimp\MailchimpSetting;
use App\Model\Github\Github;
use App\Model\Common\Setting;
use App\Model\Common\StatusSetting;
use App\Model\Common\Template;
use App\Model\Mailjob\QueueService;
use App\Model\Order\Order;
use App\Model\Payment\Currency;
use App\Model\Plugin;
use App\Payment_log;
use App\User;
use File;
use Illuminate\Http\Request;
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
        if (! $settings->where('id', '1')->first()) {
            $settings->create(['company' => '']);
        }
        $isRedisConfigured = QueueService::where('short_name', 'redis')->value('status');
        $mailSendingStatus = Setting::value('sending_status');

        return view('themes.default1.common.admin-settings', compact('isRedisConfigured', 'mailSendingStatus'));
        //return view('themes.default1.common.settings', compact('setting', 'template'));
    }

    public function plugins()
    {
        $a = [];
        $payment = new PaymentSettingsController();
        $pay = $payment->fetchConfig();

        $status = Plugin::all();

        // $demo = json_decode(json_encode($plug));
        // $status = collect($demo)->all();

        return view('themes.default1.common.plugins', compact('pay', 'status'));
    }

    /**
     * Get the Status and Api Keys for Settings Module.
     *
     * @param  ApiKey  $apikeys
     */
    public function licensekeys(ApiKey $apikeys)
    {
        $licenseSecret = $apikeys->pluck('license_api_secret')->first();
        $licenseUrl = $apikeys->pluck('license_api_url')->first();
        $licenseClientId = $apikeys->pluck('license_client_id')->first();
        $licenseClientSecret = $apikeys->pluck('license_client_secret')->first();
        $licenseGrantType = $apikeys->pluck('license_grant_type')->first();

        return response()->json([
            'licenseGrantType' => $licenseGrantType,
            'licenseSecret' => $licenseSecret,
            'licenseClientId' => $licenseClientId,
            'licenseClientSecret' => $licenseClientSecret,
            'licenseUrl' => $licenseUrl,
        ]);
    }

    public function googleCaptcha(ApiKey $apikeys)
    {
        $captchaStatus = StatusSetting::pluck('recaptcha_status')->first();
        $v3CaptchaStatus = StatusSetting::pluck('v3_recaptcha_status')->first();
        $siteKey = $apikeys->pluck('nocaptcha_sitekey')->first();
        $secretKey = $apikeys->pluck('captcha_secretCheck')->first();

        return response()->json([
            'captchaStatus' => $captchaStatus,
            'v3CaptchaStatus' => $v3CaptchaStatus,
            'siteKey' => $siteKey,
            'secretKey' => $secretKey,
        ]);
    }

    public function mobileVerification(ApiKey $apikeys)
    {


        $mobileauthkey = $apikeys->pluck('msg91_auth_key')->first();
        $msg91Sender = $apikeys->pluck('msg91_sender')->first();
        $msg91TemplateId = $apikeys->pluck('msg91_template_id')->first();

        return response()->json([
            'mobileauthkey' => $mobileauthkey,
            'msg91Sender' => $msg91Sender,
            'msg91TemplateId' => $msg91TemplateId,
        ]);
    }

    public function mailchimpKeys(ApiKey $apikeys)
    {
        $mailchimpSetting = StatusSetting::pluck('mailchimp_status')->first();
        $mailchimpKey = MailchimpSetting::pluck('api_key')->first();
        $subscribe_status = MailchimpSetting::pluck('subscribe_status')->first();
        $mailchimp_set = new MailchimpSetting();
        $set = $mailchimp_set->firstOrFail();
        $mail_api_key = $set->api_key;
        try {
            $mailchimp = new \Mailchimp\Mailchimp($mail_api_key);
            $allists = $mailchimp->get('lists?count=20')['lists'];
            $selectedList[] = $set->list_id;
        } catch (\Exception $e) {
            // Log the error if needed
            \Log::error('Mailchimp Initialization Failed: '.$e->getMessage());

            // Return null when it fails
            $mailchimp = '';
            $allists = [];
            $selectedList = [];
        }

        return response()->json([
            'mailchimpSetting' => $mailchimpSetting,
            'mailchimpKey' => $mailchimpKey,
            'allLists' => $allists,
            'selectedList' => $selectedList,
            'subscribe_status' => $subscribe_status,
        ]);
    }

    public function termsUrl(ApiKey $apikeys)
    {
        $termsUrl = $apikeys->pluck('terms_url')->first();

        return response()->json([
            'termsUrl' => $termsUrl,
        ]);
    }

    public function twitterkeys(ApiKey $apikeys)
    {
        $twitterKeys = $apikeys->select('twitter_consumer_key', 'twitter_consumer_secret',
            'twitter_access_token', 'access_tooken_secret')->first();

        return response()->json([
            'twitterkeys' => $twitterKeys,

        ]);
    }

    public function zohokeys(ApiKey $apikeys)
    {
        $zohoKey = $apikeys->pluck('zoho_api_key')->first();

        return response()->json([
            'zohoKey' => $zohoKey,

        ]);
    }

    public function pipedrivekeys(ApiKey $apikeys)
    {
        $pipedriveKey = $apikeys->pluck('pipedrive_api_key')->first();

        return response()->json([
            'pipedriveKey' => $pipedriveKey,

        ]);
    }

    public function githubkeys(ApiKey $apikeys)
    {
        $model = new Github();
        $github = $model->firstOrFail();
        $githubStatus = StatusSetting::first()->github_status;
        $githubFileds = $github->select('client_id', 'client_secret', 'username', 'password')->first();
        return response()->json([
            'githubFileds' => $githubFileds,

        ]);
    }

    public function getKeys(ApiKey $apikeys)
    {
        try {
            $mailchimpverifiedStatus = 0;
            $licenseClientId = ApiKey::pluck('license_client_id')->first();
            $licenseClientSecret = ApiKey::pluck('license_client_secret')->first();
            $licenseGrantType = ApiKey::pluck('license_grant_type')->first();
            $licenseSecret = $apikeys->pluck('license_api_secret')->first();
            $licenseUrl = $apikeys->pluck('license_api_url')->first();
            $licenseClientId = $apikeys->pluck('license_client_id')->first();
            $licenseClientSecret = $apikeys->pluck('license_client_secret')->first();
            $licenseGrantType = $apikeys->pluck('license_grant_type')->first();
            $status = StatusSetting::pluck('license_status')->first();
            $captchaStatus = StatusSetting::pluck('recaptcha_status')->first();
            $v3CaptchaStatus = StatusSetting::pluck('v3_recaptcha_status')->first();
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
            // $v3captchaStatus = StatusSetting::pluck('v3recaptcha_status')->first();
            // $v3siteKey = $apikeys->pluck('v3captcha_sitekey')->first();
            // $v3secretKey = $apikeys->pluck('v3captcha_secretCheck')->first();
            $mailchimp_set = new MailchimpSetting();

            $set = $mailchimp_set->firstOrFail();

            $mail_api_key = $set->api_key;
//            $mailchimp = ''; // Default to null in case of failure
//            $mailchimp = new \Mailchimp\Mailchimp($mail_api_key);
//            $allists = $mailchimp->get('lists?count=20')['lists'];
//            $selectedList[] = $set->list_id;
            try {
                $mailchimp = new \Mailchimp\Mailchimp($mail_api_key);
                $allists = $mailchimp->get('lists?count=20')['lists'];
                $selectedList[] = $set->list_id;
            } catch (\Exception $e) {
                \Log::error('Mailchimp Initialization Failed: '.$e->getMessage());
                $allists = [];
                $selectedList = [];
            }
            $model = new Github();
            $github = $model->firstOrFail();
            $githubStatus = StatusSetting::first()->github_status;
//            $githubFileds = $github->select('client_id', 'client_secret', 'username', 'password')->first();
$githubFileds=(object)[
    'client_id' => '1325',
    'client_secret'=>'23452',
    'username'=>'test',
    'password'=>'test',

];
            return view('themes.default1.common.apikey', compact('model', 'status', 'licenseSecret', 'licenseUrl', 'siteKey', 'secretKey', 'captchaStatus', 'v3CaptchaStatus', 'updateStatus', 'updateSecret', 'updateUrl', 'mobileStatus', 'mobileauthkey', 'msg91Sender', 'msg91TemplateId', 'emailStatus', 'twitterStatus', 'twitterKeys', 'zohoStatus', 'zohoKey', 'rzpStatus', 'rzpKeys', 'mailchimpSetting', 'mailchimpKey', 'termsStatus', 'termsUrl', 'pipedriveKey', 'pipedriveStatus', 'domainCheckStatus', 'mailSendingStatus',
                'licenseClientId', 'licenseClientSecret', 'licenseGrantType','allists', 'selectedList','set','githubStatus','githubFileds'));
        } catch (\Exception $ex) {
            return redirect('/')->with('fails', $ex->getMessage());
        }
    }


    public function getDataTableData(Request $request){
        $status = StatusSetting::pluck('license_status')->first();
        $mobileStatus = StatusSetting::pluck('msg91_status')->first();
        $captchaStatus = StatusSetting::pluck('recaptcha_status')->first();
        $v3CaptchaStatus = StatusSetting::pluck('v3_recaptcha_status')->first();
        $twitterStatus = $this->statusSetting->pluck('twitter_status')->first();
        $zohoStatus = $this->statusSetting->pluck('zoho_status')->first();
        $pipedriveStatus = StatusSetting::pluck('pipedrive_status')->first();
        $domainCheckStatus = StatusSetting::pluck('domain_check')->first();
        $githubStatus = StatusSetting::first()->github_status;
        $mailchimpSetting = StatusSetting::pluck('mailchimp_status')->first();
        $termsStatus = StatusSetting::pluck('terms')->first();
        $v3_v2_recaptcha_status = StatusSetting::pluck('v3_v2_recaptcha_status')->first();
        $checkboxValue = $v3_v2_recaptcha_status ? '1' : '0';
        $checked = $v3_v2_recaptcha_status ? 'checked' : '';

        $toggleSwitch = '
        <label class="switch toggle_event_editing gcaptcha">
            <input type="checkbox" value="'.$checkboxValue.'"  
                   name="modules_settings"
                   class="checkbox2" id="captcha" '.$checked.'>
            <span class="slider round"></span>
        </label>
    ';

        if ($request->ajax()) {
            $dataTable = collect([
                ['options' => 'Auto Faveo Licenser & Update Manager', 'description' => 'The Faveo License Manager Integration adds the ability to manage software licenses and updates within Faveo Invoicing, allowing seamless tracking, activation, and updating of licenses directly through the platform', 'status' => '
        <label class="switch toggle_event_editing licenser">
            <input type="checkbox" value="'.($status ? '1' : '0').'"  
                   name="modules_settings"
                   class="checkbox" id="License" '.($status ? 'checked' : '').'>
            <span class="slider round"></span>
        </label>
    ', 'action' => '<button id="license-edit-button" class="btn btn-sm btn-secondary btn-xs" ><span class="nav-icon fa fa-fw fa-edit"></span></button>',
                ],
                //            ['options' => "Don't Allow Domin/Ip based Restriction",'description'=>'Not Available', 'status' => $this->getStatus($domainCheckStatus), 'action' => 'NotAvailable'],
                ['options' => 'Google reCAPTCHA', 'description' => 'The Google reCAPTCHA integration adds a reCAPTCHA feature to all unauthenticated pages, helping to protect your forms from bots and automated submissions, ensuring that only genuine users can submit information', 'status' => $toggleSwitch, 'action' => '<button id="captcha-edit-button" class="btn btn-sm btn-secondary btn-xs" ><span class="nav-icon fa fa-fw fa-edit"></span></button>',
                ],
                ['options' => 'Msg 91(Mobile Verification)', 'description' => "The MSG91.com service is used to send OTPs (One-Time Passwords) to verify the contact's mobile number during registration, ensuring a secure and reliable method of contact verification.", 'status' => '<label class="switch toggle_event_editing mstatus">
                    <input type="checkbox" value="'.($mobileStatus ? '1' : '0').'"  name="mobile_settings"
                           class="checkbox4" id="mobile"'.($mobileStatus ? 'checked' : '').'>
                    <span class="slider round"></span>
                    </label>', 'action' => '<button id="msg91-edit-button" class="btn btn-sm btn-secondary btn-xs"><span class="nav-icon fa fa-fw fa-edit"></span></button>',
                ],
                ['options' => 'Mailchimp', 'description' => 'The Mailchimp plugin seamlessly pushes all contact data from Faveo Invoicing to Mailchimp during contact registration and profile edits. Additionally, purchase data can be mapped to Mailchimp. A dedicated mapping page allows users to customize which data should be synced between the two platforms', 'status' => '<label class="switch toggle_event_editing mailchimpstatus">
                        <input type="checkbox" value="'.($mailchimpSetting ? '1' : '0').'"  name="mobile_settings"
                               class="checkbox9" id="mailchimp"'.($mailchimpSetting ? 'checked' : '').'>
                        <span class="slider round"></span>
                    </label>', 'action' => '<button id="mailchimp-edit-button" class="btn btn-sm btn-secondary btn-xs"><span class="nav-icon fa fa-fw fa-edit"></span></button>',
                ],
                ['options' => 'Show Terms on Registration Page', 'description' => "When the 'Show Terms on Registration Page' option is enabled, a checkbox is displayed on the registration page, requiring users to agree to the Terms and Conditions before completing their registration.", 'status' => '<label class="switch toggle_event_editing termstatus1">

                        <input type="checkbox" value="'.($termsStatus ? '1' : '0').'"  name="terms_settings"
                               class="checkbox10" id="terms"'.($termsStatus ? 'checked' : '').'>
                        <span class="slider round"></span>
                    </label>', 'action' => '<button id="termsUrl-edit-button" class="btn btn-sm btn-secondary btn-xs"><span class="nav-icon fa fa-fw fa-edit"></span></button>',
                ],
//                ['options' => 'Twitter', 'description' => 'This plugin displays live tweets from a specified Twitter page directly in the footer of the portal, keeping users updated with the latest posts in real-time.', 'status' => '<label class="switch toggle_event_editing twitterstatus">
//                    <input type="checkbox" value="'.($twitterStatus ? '1' : '0').'"  name="twitter_settings"
//                           class="checkbox6" id="twitter"'.($twitterStatus ? 'checked' : '').'>
//                    <span class="slider round"></span>
//                    </label>', 'action' => '<button id="twitter-edit-button" class="btn btn-sm btn-secondary btn-xs"><span class="nav-icon fa fa-fw fa-edit"></span></button>',
//                ],
//                ['options' => 'Zoho CRM', 'description' => 'The Zoho CRM plugin seamlessly transfers all contact data from Faveo Invoicing to Zoho CRM upon contact registration and profile edits, ensuring your CRM is always up-to-date with the latest contact information.', 'status' => '                    <label class="switch toggle_event_editing zohostatus">
//                        <input type="checkbox" value="'.($zohoStatus ? '1' : '0').'"  name="zoho_settings"
//                           class="checkbox8" id="zoho"'.($zohoStatus ? 'checked' : '').'>
//                        <span class="slider round"></span>
//                    </label>', 'action' => '<button id="zoho-edit-button" class="btn btn-sm btn-secondary btn-xs"><span class="nav-icon fa fa-fw fa-edit"></span></button>',
//                ],
                ['options' => 'Pipedrive', 'description' => 'The Pipedrive CRM plugin automatically pushes all contact data from Faveo Invoicing to Pipedrive CRM during contact registration and profile edits. A dedicated mapping page allows users to customize which data fields should be synced between the two platforms.', 'status' => '                    <label class="switch toggle_event_editing pipedrivestatus">
                        <input type="checkbox" value="'.($pipedriveStatus ? '1' : '0').'"  name="pipedrive_settings"
                           class="checkbox13" id="pipedrive"'.($pipedriveStatus ? 'checked' : '').'>
                        <span class="slider round"></span>
                    </label>', 'action' => '<button id="pipedrive-edit-button" class="btn btn-sm btn-secondary btn-xs"><span class="nav-icon fa fa-fw fa-edit"></span></button>',
                ],
                ['options' => 'Github', 'description' => 'The GitHub integration adds the ability to retrieve and download products directly from a GitHub repository, streamlining the process of accessing and managing files from your GitHub projects within the platform', 'status' => '                        <label class="switch toggle_event_editing githubstatus">
                            <input type="checkbox" value="'.($githubStatus ? '1' : '0').'" name="github_settings" class="checkbox" id="github"'.($githubStatus ? 'checked' : '').'>
                            <span class="slider round"></span>
                        </label>', 'action' => '<button id="github-edit-button" class="btn btn-sm btn-secondary btn-xs"><span class="nav-icon fa fa-fw fa-edit"></span></button>',
                ],
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
        if (! $value && ! $value2) {
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
     * @param  string  $currency  The currency of the Product Selected
     * @return string Name of the Payment Gateway
     */
    public static function checkPaymentGateway($currency)
    {
        try {
            $plugins = new Plugin();
            $models = [];
            $gateways = '';
            $name = '';
            $allAcivePluginName = [];
            $active_plugins = $plugins->where('status', 1)->get(); //get the plugins that are active
            if ($active_plugins) {
                foreach ($active_plugins as $plugin) {
                    $models[] = \DB::table(strtolower($plugin->name))->first(); //get the table of the active plugin
                    $allCurrencies[] = \DB::table(strtolower($plugin->name))->pluck('currencies')->toArray(); //get the table of the active plugin
                    $pluginName[] = $plugin->name; //get the name of active plugin
                }
                if ($models) {//If more than 1 plugin is active it will check the currencies allowed for that plugin.If the currencies allowed matches the passed arguement(currency),that plugin name is returned
                    for ($i = 0; $i < count($pluginName); $i++) {
                        $curr = implode(',', $allCurrencies[$i]);
                        $currencies = explode(',', $curr);
                        if (in_array($currency, $currencies)) {
                            $name = $pluginName[$i];
                            $allAcivePluginName[] = $name;
                        }
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
            $state = getStateByCode($set->state);
            $selectedCountry = \DB::table('countries')->where('country_code_char2', $set->country)
                ->pluck('nicename', 'country_code_char2')->toArray();
            $selectedCurrency = \DB::table('currencies')->where('code', $set->default_currency)
                ->pluck('name', 'symbol')->toArray();
            $states = findStateByRegionId($set->country);

            return view(
                'themes.default1.common.setting.system',
                compact('set', 'selectedCountry', 'state', 'states', 'selectedCurrency')
            );
        } catch (\Exception $ex) {
            return redirect()->back()->with('fails', $ex->getMessage());
        }
    }

    public function postSettingsSystem(Setting $settings, SettingsRequest $request)
    {
        try {
            $setting = $settings->find(1);

            if ($request->hasFile('logo')) {
                $path = Attach::put('images', $request->file('logo'));
                $setting->logo = basename($path);
            }

            if ($request->hasFile('admin-logo')) {
                $path = Attach::put('admin/images', $request->file('admin-logo'));
                $setting->admin_logo = basename($path);
            }

            if ($request->hasFile('fav-icon')) {
                $path = Attach::put('common/images', $request->file('fav-icon'));
                $setting->fav_icon = basename($path);
            }

            $setting->default_symbol = Currency::where('code', $request->input('default_currency'))
                            ->pluck('symbol')->first();

            $setting->fill($request->except('password', 'logo', 'admin-logo', 'fav-icon'))->save();

            return redirect()->back()->with('success', \Lang::get('message.updated-successfully'));
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
                    Attach::delete('images/'.$logoPath);
                    $todo->logo = null;
                }
                if ($request->column == 'admin') {
                    $adminLogoPath = $todo->admin_logo;
                    Attach::delete('admin/images/'.$adminLogoPath);
                    $todo->admin_logo = null;
                }
                if ($request->column == 'fav') {
                    $favIconPath = $todo->fav_icon;
                    Attach::delete('common/images'.$favIconPath);
                    $todo->fav_icon = null;
                }
                $todo->save();
                $response = ['type' => 'success', 'message' => 'Logo Deleted successfully'];

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
            'from' => 'nullable',
            'till' => 'nullable|after:from',
            'delFrom' => 'nullable',
            'delTill' => 'nullable|after:delFrom',
        ]);
        if ($validator->fails()) {
            $request->from = '';
            $request->till = '';
            $request->delFrom = '';
            $request->delTill = '';

            return redirect('settings/activitylog')->with('fails', 'Start date should be before end date');
        }
        try {
            $activity = $activities->all();
            $from = $request->input('from');
            $till = $request->input('till');
            $delFrom = $request->input('delFrom');
            $delTill = $request->input('delTill');

            return view('themes.default1.common.Activity-Log', compact('activity', 'from', 'till', 'delFrom', 'delTill'));
        } catch (\Exception $ex) {
            return redirect()->back()->with('fails', $ex->getMessage());
        }
    }

    public function settingsMail(Request $request)
    {
        try {
            $from = $request->input('mailfrom');
            $till = $request->input('mailtill');

            return view('themes.default1.common.email-log', compact('from', 'till'));
        } catch (\Exception $ex) {
            return redirect()->back()->with('fails', $ex->getMessage());
        }
    }

    public function getActivity(Request $request)
    {
        try {
            $from = $request->input('log_from');
            $till = $request->input('log_till');
            $delFrom = $request->input('delFrom');
            $delTill = $request->input('delTill');
            $query = $this->advanceSearch($from, $till, $delFrom, $delTill);

            return \DataTables::of($query->take(50))
             ->setTotalRecords($query->count())
              ->orderColumn('name', '-created_at $1')
              ->orderColumn('description', '-created_at $1')
              ->orderColumn('role', '-created_at $1')
              ->orderColumn('new', '-created_at $1')
              ->orderColumn('old', '-created_at $1')
              ->orderColumn('created_at', '-created_at $1')

             ->addColumn('checkbox', function ($model) {
                 return "<input type='checkbox' class='activity' value=".$model->id.' name=select[] id=check>';
             })
                           ->addColumn('name', function ($model) {
                               return ucfirst($model->log_name);
                           })
                             ->addColumn('description', function ($model) {
                                 return ucfirst($model->description);
                             })
                          ->addColumn('username', function ($model) {
                              $causer_id = $model->causer_id;
                              $names = User::where('id', $causer_id)->pluck('last_name', 'first_name');
                              foreach ($names as $key => $value) {
                                  $fullName = $key.' '.$value;

                                  return $fullName;
                              }
                          })
                              ->addColumn('role', function ($model) {
                                  $causer_id = $model->causer_id;
                                  $role = User::where('id', $causer_id)->pluck('role');

                                  return json_decode($role);
                              })
                               ->addColumn('new', function ($model) {
                                   $properties = $model->properties;
                                   $newEntry = $this->getNewEntry($properties, $model);

                                   return $newEntry;
                               })
                                ->addColumn('old', function ($model) {
                                    $data = $model->properties;
                                    $oldEntry = $this->getOldEntry($data, $model);

                                    return $oldEntry;
                                })
                                ->addColumn('created_at', function ($model) {
                                    return getDateHtml($model->created_at);
                                })

                                    ->filterColumn('log_name', function ($query, $keyword) {
                                        $sql = 'log_name like ?';
                                        $query->whereRaw($sql, ["%{$keyword}%"]);
                                    })

                                ->filterColumn('description', function ($query, $keyword) {
                                    $sql = 'description like ?';
                                    $query->whereRaw($sql, ["%{$keyword}%"]);
                                })

                            ->filterColumn('causer_id', function ($query, $keyword) {
                                $sql = 'first_name like ?';
                                $query->whereRaw($sql, ["%{$keyword}%"]);
                            })

                            ->rawColumns(['checkbox', 'name', 'description',
                                'username', 'role', 'new', 'old', 'created_at', ])
                            ->make(true);
        } catch (\Exception $e) {
            return redirect()->back()->with('fails', $e->getMessage());
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
                    return "<input type='checkbox' class='email' value=".$model->id.' name=select[] id=check>';
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

                    return '<a href='.url('clients/'.$id).'>'.ucfirst($model->to).'<a>';
                })

                ->addColumn('subject', function ($model) {
                    return ucfirst($model->subject);
                })
                ->rawColumns(['checkbox', 'date', 'from', 'to',
                    'bcc', 'subject',  'status', ])
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
                    'bcc', 'subject',  'status', ])
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
            if (! empty($ids)) {
                foreach ($ids as $id) {
                    $activity = Activity::where('id', $id)->first();
                    if ($activity) {
                        $activity->delete();
                    } else {
                        echo "<div class='alert alert-danger alert-dismissable'>
                        <i class='fa fa-ban'></i>

                        <b>"./* @scrutinizer ignore-type */\Lang::get('message.alert').'!</b> '.
                            /* @scrutinizer ignore-type */     \Lang::get('message.failed').'

                        <button type=button class=close data-dismiss=alert aria-hidden=true>&times;</button>
                            './* @scrutinizer ignore-type */\Lang::get('message.no-record').'
                    </div>';
                        //echo \Lang::get('message.no-record') . '  [id=>' . $id . ']';
                    }
                }
                echo "<div class='alert alert-success alert-dismissable'>
                        <i class='fa fa-ban'></i>
                        <b>"./* @scrutinizer ignore-type */\Lang::get('message.alert').'!</b> '
                    ./* @scrutinizer ignore-type */\Lang::get('message.success').'
                        <button type=button class=close data-dismiss=alert aria-hidden=true>&times;</button>
                            './* @scrutinizer ignore-type */ \Lang::get('message.deleted-successfully').'
                    </div>';
            } else {
                echo "<div class='alert alert-danger alert-dismissable'>
                        <i class='fa fa-ban'></i>
                        <b>"./* @scrutinizer ignore-type */ \Lang::get('message.alert').
                    '!</b> './* @scrutinizer ignore-type */\Lang::get('message.failed').'
                        <button type=button class=close data-dismiss=alert aria-hidden=true>&times;</button>
                            './* @scrutinizer ignore-type */ \Lang::get('message.select-a-row').'
                    </div>';
                //echo \Lang::get('message.select-a-row');
            }
        } catch (\Exception $e) {
            echo "<div class='alert alert-danger alert-dismissable'>
                        <i class='fa fa-ban'></i>
                        <b>"./* @scrutinizer ignore-type */\Lang::get('message.alert').'!</b> '.
                /* @scrutinizer ignore-type */\Lang::get('message.failed').'
                        <button type=button class=close data-dismiss=alert aria-hidden=true>&times;</button>
                            '.\Lang::get('message.err_msg.').'
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
        $request = $request->get('debug');
        if ($request == 'true') {
            $debug_new = base_path().DIRECTORY_SEPARATOR.'.env';
            $datacontent = File::get($debug_new);
            $datacontent = str_replace('APP_DEBUG=false', 'APP_DEBUG=true', $datacontent);
            File::put($debug_new, $datacontent);
        } elseif ($request == 'false') {
            $debug_new = base_path().DIRECTORY_SEPARATOR.'.env';
            $datacontent = File::get($debug_new);
            $datacontent = str_replace('APP_DEBUG=true', 'APP_DEBUG=false', $datacontent);
            File::put($debug_new, $datacontent);
        }

        return redirect()->back()->with('success', \Lang::get('message.updated-successfully'));
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

    public function getPaymentlog(Request $request)
    {
        try {
            $from = $request->input('from');
            $till = $request->input('till');
            $query = $this->paymentSearch($from, $till);

            return Datatables::of($query)
            ->orderColumn('date', '-date $1')
            ->orderColumn('user', '-date $1')
            ->orderColumn('ordernumber', '-date $1')
            ->orderColumn('amount', '-date $1')
            ->orderColumn('paymenttype', '-date $1')
            ->orderColumn('paymentmethod', '-date $1')
            ->orderColumn('status', '-date $1')

                ->addColumn('checkbox', function ($model) {
                    return "<input type='checkbox' class='email' value=".$model->count.' name=select[] id=check>';
                })

                ->addColumn('date', function ($model) {
                    $date = $model->date;

                    return getDateHtml($date);
                })
                ->addColumn('user', function ($model) {
                    $user = User::where('email', $model->from)->select('first_name', 'last_name', 'id')->first();
                    if ($user) {
                        return '<a href='.url('clients/'.$model->id).'>'.ucfirst($model->name).'</a>';
                    }

                    return '';
                })

                ->addColumn('paymentmethod', function ($model) {
                    return ucfirst($model->payment_method);
                })
                ->addColumn('ordernumber', function ($model) {
                    $id = Order::where('number', $model->order)->select('id')->value('id');
                    $orderLink = '<a href='.url('orders/'.$id).'>'.$model->order.'</a>';

                    return $orderLink;
                })
                ->addColumn('amount', function ($model) {
                    return ucfirst($model->amount);
                })
                ->addColumn('paymenttype', function ($model) {
                    return ucfirst($model->payment_type);
                })
                ->addColumn('status', function ($model) {
                    if ($model->status === 'failed') {
                        $exceptionMessage = $model->exception;

                        return '<a href="#" class="show-exception" data-message="'.$exceptionMessage.'">Failed</a>';
                    }

                    return ucfirst($model->status);
                })
                ->rawColumns(['checkbox', 'date', 'user',
                    'bcc', 'status', 'paymentmethod', 'ordernumber', 'amount', 'paymenttype'])

                ->filterColumn('user', function ($model, $keyword) {
                    $model->whereRaw("CONCAT(first_name, ' ',last_name) like ?", ["%$keyword%"]);
                })

                ->filterColumn('status', function ($query, $keyword) {
                    $sql = '`status` like ?';
                    $query->whereRaw($sql, ["%{$keyword}%"]);
                })
                ->filterColumn('paymenttype', function ($query, $keyword) {
                    $sql = '`payment_type` like ?';
                    $query->whereRaw($sql, ["%{$keyword}%"]);
                })
                ->filterColumn('amount', function ($query, $keyword) {
                    $sql = '`amount` like ?';
                    $query->whereRaw($sql, ["%{$keyword}%"]);
                })
                 ->filterColumn('paymentmethod', function ($query, $keyword) {
                     $sql = '`payment_method` like ?';
                     $query->whereRaw($sql, ["%{$keyword}%"]);
                 })
                 ->filterColumn('ordernumber', function ($query, $keyword) {
                     $sql = '`order` like ?';
                     $query->whereRaw($sql, ["%{$keyword}%"]);
                 })

                ->make(true);
        } catch (\Exception $e) {
            return redirect()->back()->with('fails', $e->getMessage());
        }
    }

    public function paymentSearch($from = '', $till = '')
    {
        $join = Payment_log::query()->leftJoin('users', 'payment_logs.from', '=', 'users.email')
            ->select('payment_logs.id', 'from', 'to', 'date', 'subject', 'status', 'payment_logs.created_at', 'payment_method', 'order', 'exception', 'email', \DB::raw("CONCAT(first_name, ' ', last_name) as name"), 'users.id', 'payment_logs.id as count', 'amount', 'payment_type');

        if ($from) {
            $from = $this->DateFormat($from);
            $tillDate = $this->DateFormat($till ?: date('Y-m-d H:i:s'));
            $join->whereBetween('date', [$from, $tillDate]);
        }

        if ($till) {
            $till = $this->DateFormat($till);
            $fromDate = Payment_log::oldest('date')->value('date');
            $fromDate = $this->DateFormat($from ?: $fromDate);
            $join->whereBetween('date', [$fromDate, $till]);
        }

        return $join;
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
            if (! empty($ids)) {
                foreach ($ids as $id) {
                    $email = \DB::table('payment_logs')->where('id', $id)->delete();
                    if ($email) {
                        // $email->delete();
                    } else {
                        echo "<div class='alert alert-danger alert-dismissable'>
                        <i class='fa fa-ban'></i>

                        <b>"./* @scrutinizer ignore-type */\Lang::get('message.alert').'!</b> '.
                        /* @scrutinizer ignore-type */     \Lang::get('message.failed').'

                        <button type=button class=close data-dismiss=alert aria-hidden=true>&times;</button>
                            './* @scrutinizer ignore-type */\Lang::get('message.no-record').'
                    </div>';
                        //echo \Lang::get('message.no-record') . '  [id=>' . $id . ']';
                    }
                }
                echo "<div class='alert alert-success alert-dismissable'>
                        <i class='fa fa-ban'></i>
                        <b>"./* @scrutinizer ignore-type */\Lang::get('message.alert').'!</b> '
                        ./* @scrutinizer ignore-type */\Lang::get('message.success').'
                        <button type=button class=close data-dismiss=alert aria-hidden=true>&times;</button>
                            './* @scrutinizer ignore-type */ \Lang::get('message.deleted-successfully').'
                    </div>';
            } else {
                echo "<div class='alert alert-danger alert-dismissable'>
                        <i class='fa fa-ban'></i>
                        <b>"./* @scrutinizer ignore-type */ \Lang::get('message.alert').
                        '!</b> './* @scrutinizer ignore-type */\Lang::get('message.failed').'
                        <button type=button class=close data-dismiss=alert aria-hidden=true>&times;</button>
                            './* @scrutinizer ignore-type */ \Lang::get('message.select-a-row').'
                    </div>';
                //echo \Lang::get('message.select-a-row');
            }
        } catch (\Exception $e) {
            echo "<div class='alert alert-danger alert-dismissable'>
                        <i class='fa fa-ban'></i>
                        <b>"./* @scrutinizer ignore-type */\Lang::get('message.alert').'!</b> '.
                        /* @scrutinizer ignore-type */\Lang::get('message.failed').'
                        <button type=button class=close data-dismiss=alert aria-hidden=true>&times;</button>
                            '.$e->getMessage().'
                    </div>';
        }
    }

    public function contactOption()
    {
        $mailSendingStatus = Setting::value('sending_status');
        $emailStatus = StatusSetting::pluck('emailverification_status')->first();

        return view('themes.default1.common.setting.contact-options', compact('mailSendingStatus', 'emailStatus'));
    }
}
