<?php

namespace App\Http\Controllers\Common;

use App\ApiKey;
use App\Email_log;
use App\Facades\Attach;
use App\Http\Requests\Common\SettingsRequest;
use App\Model\Common\Mailchimp\MailchimpSetting;
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

        return view('themes.default1.common.admin-settings', compact('isRedisConfigured'));
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
    public function getKeys(ApiKey $apikeys)
    {
        try {
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
            $emailStatus = StatusSetting::pluck('emailverification_status')->first();
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
            $model = $apikeys->find(1);
            // $v3captchaStatus = StatusSetting::pluck('v3recaptcha_status')->first();
            // $v3siteKey = $apikeys->pluck('v3captcha_sitekey')->first();
            // $v3secretKey = $apikeys->pluck('v3captcha_secretCheck')->first();

            return view('themes.default1.common.apikey', compact('model', 'status', 'licenseSecret', 'licenseUrl', 'siteKey', 'secretKey', 'captchaStatus', 'v3CaptchaStatus', 'updateStatus', 'updateSecret', 'updateUrl', 'mobileStatus', 'mobileauthkey', 'msg91Sender', 'msg91TemplateId', 'emailStatus', 'twitterStatus', 'twitterKeys', 'zohoStatus', 'zohoKey', 'rzpStatus', 'rzpKeys', 'mailchimpSetting', 'mailchimpKey', 'termsStatus', 'termsUrl', 'pipedriveKey', 'pipedriveStatus', 'domainCheckStatus', 'mailSendingStatus', 'licenseClientId', 'licenseClientSecret', 'licenseGrantType'));
        } catch (\Exception $ex) {
            return redirect('/')->with('fails', $ex->getMessage());
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
        $enable = $request->get('debug') === 'true';
        setEnvValue('APP_DEBUG', $enable ? 'true' : 'false');
        setEnvValue('PULSE_ENABLED', $enable ? 'true' : 'false');

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
}
