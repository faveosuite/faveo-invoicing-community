<?php

namespace App\Http\Controllers\Common;

use App\ApiKey;
use App\Http\Controllers\Common\PHPController as PaymentSettingsController;
use App\Http\Controllers\Order\OrderSearchController;
use App\Model\Common\StatusSetting;
use App\Model\Mailjob\ActivityLogDay;
use App\Model\Mailjob\ExpiryMailDay;
use App\Traits\ApiKeySettings;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Spatie\Activitylog\Models\Activity;

class BaseSettingsController extends PaymentSettingsController
{
    use ApiKeySettings;

    /**
     * Get the logged activity.
     */
    public function getNewEntry($properties, $model)
    {
        $properties = (array_key_exists('attributes', $properties->toArray()))
        ? ($model->properties['attributes']) : null;

        $display = [];
        if ($properties != null) {
            if (array_key_exists('parent', $properties)) {
                unset($properties['parent']);
            }
            foreach ($properties as $key => $value) {
                $display[] = '<strong>'.'ucfirst'($key).'</strong>'.' : '.$value.'<br/>';
            }
            $updated = (count($properties) > 0) ? implode('', $display) : '--';

            return $updated;
        } else {
            return '--';
        }
    }

    /**
     * Get the older Entries.
     */
    public function getOldEntry($data, $model)
    {
        $oldData = '';
        $oldData = (array_key_exists('old', $data->toArray())) ? ($model->properties['old']) : null;
        if ($oldData != null) {
            if (count($oldData) > 0) {
                foreach ($oldData as $key => $value) {
                    $display[] = '<strong>'.'ucfirst'($key).'</strong>'.' : '.$value.'<br/>';
                }
            }

            $old = (count($oldData) > 0) ? implode('', $display) : '--';

            return $old;
        } else {
            return '--';
        }
    }

    public function destroyEmail(Request $request)
    {
        try {
            $ids = $request->input('select');
            if (! empty($ids)) {
                foreach ($ids as $id) {
                    $email = \DB::table('email_log')->where('id', $id)->delete();
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

    public function advanceSearch($from = '', $till = '', $delFrom = '', $delTill = '')
    {
        $join = new Activity();
        if ($from) {
            $from = $this->getDateFormat($from);
            $tills = $this->getDateFormat();
            $tillDate = (new OrderSearchController())->getTillDate($from, $till, $tills);
            $join = $join->whereBetween('created_at', [$from, $tillDate]);
        }
        if ($till) {
            $till = $this->getDateFormat($till);
            $froms = Activity::first()->created_at;
            $fromDate = (new OrderSearchController())->getFromDate($from, $froms);
            $join = $join->whereBetween('created_at', [$fromDate, $till]);
        }
        $join = $join
        ->select(
            'id',
            'log_name',
            'description',
            'subject_id',
            'subject_type',
            'causer_id',
            'properties',
            'created_at'
        );

        return $join;
    }

    public function getScheduler(StatusSetting $status)
    {
        $cronPath    = base_path('artisan');
        $status      = $status->find(1);
        $execEnabled = $this->execEnabled();
        $paths       = $this->getPHPBinPath();
        $warn        = '';
        $condition   = new \App\Model\Mailjob\Condition();

        // ✅ All hardcoded lists grouped into one variable
        $configurations = [
            'commands' => [
                'everyMinute'       => 'Every Minute',
                'everyFiveMinutes'  => 'Every Five Minute',
                'everyTenMinutes'   => 'Every Ten Minute',
                'everyThirtyMinutes'=> 'Every Thirty Minute',
                'hourly'            => 'Every Hour',
                'daily'             => 'Every Day',
                'dailyAt'           => 'Daily at',
                'weekly'            => 'Every Week',
                'monthly'           => 'Monthly',
                'yearly'            => 'Yearly',
            ],

            'expiryDays' => [
                '30' => '30 days',
                '15' => '15 days',
                '7'  => '7 days',
                '1'  => '1 day',
            ],

            'subs_expiry' => [
                '30' => '30 days',
                '15' => '15 days',
                '7'  => '7 days',
                '1'  => '1 day',
            ],

            'post_expiry' => [
                '30' => '30 days',
                '15' => '15 days',
                '7'  => '7 days',
                '1'  => '1 day',
            ],

            'cloudDays' => [
                '30' => '30 days',
                '15' => '15 days',
                '7'  => '7 days',
                '1'  => '1 day',
            ],

            'invoiceDays' => [
                '7' => '7 days',
                '5' => '5 days',
                '2' => '2 days',
                '1' => '1 day',
            ],

            'msg91Days' => [
                '720' => '720 Days',
                '365' => '365 days',
                '180' => '180 Days',
                '150' => '150 Days',
                '60'  => '60 Days',
                '30'  => '30 Days',
                '15'  => '15 Days',
                '5'   => '5 Days',
                '2'   => '2 Days',
                '0'   => 'Delete All Reports',
            ],

            'delLogDays' => [
                '720' => '720 Days',
                '365' => '365 days',
                '180' => '180 Days',
                '150' => '150 Days',
                '60'  => '60 Days',
                '30'  => '30 Days',
                '15'  => '15 Days',
                '5'   => '5 Days',
                '2'   => '2 Days',
                '0'   => 'Delete All Logs',
            ],
        ];


        $expiryMail = ExpiryMailDay::first();
        $beforeLog  = ActivityLogDay::first();

        $daysData = [
            'beforeLogDay'       => $beforeLog?->days,
            'selectedDays'       => ExpiryMailDay::all()->map(fn($row) => json_decode($row->days, true))->toArray(),
            'auto_expiry_days'   => $expiryMail ? json_decode($expiryMail->autorenewal_days, true) : [],
            'post_expiry_days'   => $expiryMail ? json_decode($expiryMail->postexpiry_days, true) : [],
            'beforeCloudDay'     => $expiryMail?->cloud_days,
            'invoiceDeletionDay' => $expiryMail?->invoice_days,
            'msgDeletionDays'    => $expiryMail?->msg91_days,
        ];

        return successResponse('',[
            'cronPath'      => $cronPath,
            'warn'          => $warn,
            'condition'     => $condition,
            'status'        => $status,
            'execEnabled'   => $execEnabled,
            'paths'         => $paths,
            'configurations'=> $configurations,
            'daysData'      => $daysData,
        ]);
    }

    public function postSchedular(StatusSetting $status, Request $request)
    {
        $allStatus = $status->whereId('1')->first();
        if ($request->expiry_cron) {
            $allStatus->expiry_mail = $request->expiry_cron;
        } else {
            $allStatus->expiry_mail = 0;
        }
        if ($request->activity) {
            $allStatus->activity_log_delete = $request->activity;
        } else {
            $allStatus->activity_log_delete = 0;
        }
        if ($request->subs_expirymail) {
            $allStatus->subs_expirymail = $request->subs_expirymail;
        } else {
            $allStatus->subs_expirymail = 0;
        }
        if ($request->postsubs_expirymail) {
            $allStatus->post_expirymail = $request->postsubs_expirymail;
        } else {
            $allStatus->post_expirymail = 0;
        }
        $allStatus->cloud_mail_status = $request->cloud_cron ? $request->cloud_cron : 0;
        $allStatus->invoice_deletion_status = $request->invoice_cron ? $request->invoice_cron : 0;
        $allStatus->msg91_report_delete_status = $request->msg91_cron ? $request->msg91_cron : 0;
        $allStatus->save();
        $this->saveConditions();

        return successResponse( __('message.updated-successfully'));

    }

    //Save the Cron Days for expiry Mails and Activity Log
    public function saveCronDays(Request $request)
    {
        ExpiryMailDay::truncate();

        ExpiryMailDay::create([
            'days' => json_encode($request->input('expiryday')),
            'autorenewal_days' => json_encode($request->input('subexpiryday')),
            'postexpiry_days' => json_encode($request->input('postsubexpiry_days')),
        ]);

        // $cloudDays = is_array($request->input('cloud_days')) ? $request->input('cloud_days') : [$request->input('cloud_days')];

        \DB::table('expiry_mail_days')->update(['cloud_days' => $request->input('cloud_days'), 'invoice_days' => $request->input('invoice_days'), 'msg91_days' => $request->input('msg91_days')]);
        ActivityLogDay::findOrFail(1)->update(['days' => $request->logdelday]);

        return redirect()->back()->with('success', \Lang::get('message.updated-successfully'));
    }

    //Save Google recaptch site key and secret in Database
    public function captchaDetails(Request $request)
    {
        $validated = $request->validate([
            'status' => 'required|boolean',
            'recaptcha_type' => 'required|in:v2,v3',
            'nocaptcha_sitekey' => 'required|string',
            'nocaptcha_secret' => 'required|string',
            'g-recaptcha-response' => ['required'],
        ]);

        // Perform custom validation for reCAPTCHA
        $this->validateRecaptcha(
            $validated['g-recaptcha-response'],
            $validated['nocaptcha_secret'],
            $request->ip(),
            $request->getHost()
        );

        if ($validated['status']) {
            setEnvValue([
                'NOCAPTCHA_SITEKEY' => $validated['nocaptcha_sitekey'],
                'NOCAPTCHA_SECRET' => $validated['nocaptcha_secret'],
            ]);

            $isV2 = $validated['recaptcha_type'] === 'v2';
            $isV3 = $validated['recaptcha_type'] === 'v3';

            StatusSetting::where('id', 1)->update([
                'recaptcha_status' => $isV2,
                'v3_recaptcha_status' => $isV3,
                'v3_v2_recaptcha_status' => 1,
            ]);

            ApiKey::where('id', 1)->update([
                'nocaptcha_sitekey' => $validated['nocaptcha_sitekey'],
                'captcha_secretCheck' => $validated['nocaptcha_secret'],
            ]);
        }

        return successResponse(__('message.recaptcha_settings_updated'));
    }

    protected function validateRecaptcha(string $token, string $secret, string $ip, string $expectedHostname): void
    {
        $response = \Http::asForm()->post('https://www.google.com/recaptcha/api/siteverify', [
            'secret' => $secret,
            'response' => $token,
            'remoteip' => $ip,
        ]);

        $body = $response->json();

        if (! ($body['success'] ?? false)) {
            throw ValidationException::withMessages([
                'g-recaptcha-response' => [__('message.captcha_failed')],
            ]);
        }

        if (isset($body['score'])) {
            if (
                ($body['score'] < 0.5) ||
                (($body['action'] ?? null) !== 'captcha_settings') ||
                (($body['hostname'] ?? null) !== $expectedHostname)
            ) {
                throw ValidationException::withMessages([
                    'g-recaptcha-response' => [__('message.captcha_failed')],
                ]);
            }
        }
    }

    //Save Google recaptcha site key and secret in Database
    public function v3captchaDetails(Request $request)
    {
        $status = $request->input('status');
        if ($status) {
            $nocaptcha_sitekey = $request->input('captcha_sitekey');
            $captcha_secretCheck = $request->input('captcha_secret');
            $values = ['RECAPTCHA_SITE_KEY' => $nocaptcha_sitekey, 'RECAPTCHA_SECRET_KEY' => $captcha_secretCheck];

            $envFile = app()->environmentFilePath();
            $str = file_get_contents($envFile);

            if (count($values) > 0) {
                foreach ($values as $envKey => $envValue) {
                    $str .= "\n"; // In case the searched variable is in the last line without \n
                    $keyPosition = strpos($str, "{$envKey}=");
                    $endOfLinePosition = strpos($str, "\n", $keyPosition);
                    $oldLine = substr($str, $keyPosition, $endOfLinePosition - $keyPosition);

                    // If key does not exist, add it
                    if (! $keyPosition || ! $endOfLinePosition || ! $oldLine) {
                        $str .= "{$envKey}={$envValue}\n";
                    } else {
                        $str = str_replace($oldLine, "{$envKey}={$envValue}", $str);
                    }
                }
            }

            $str = substr($str, 0, -1);
            if (! file_put_contents($envFile, $str)) {
                return false;
            }
        } else {
            $nocaptcha_sitekey = '';
            $captcha_secretCheck = '';
            $path_to_file = base_path('.env');
            $file_contents = file_get_contents($path_to_file);
            $file_contents_secretchek = str_replace([env('RECAPTCHA_SITE_KEY'), env('RECAPTCHA_SITE_KEY')], [$captcha_secretCheck, $nocaptcha_sitekey], $file_contents);
            file_put_contents($path_to_file, $file_contents_secretchek);
        }

        StatusSetting::findOrFail(1)->update(['v3recaptcha_status' => $status]);
        ApiKey::findOrFail(1)->update([
            'v3captcha_sitekey' => $nocaptcha_sitekey,
            'v3captcha_secretCheck' => $captcha_secretCheck,
        ]);

        return ['message' => 'success', 'update' => __('message.recaptcha_settings_updated')];
    }
}
