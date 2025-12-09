<?php

namespace App\Http\Controllers\Common;

use App\ApiKey;
use App\Http\Controllers\Common\PHPController as PaymentSettingsController;
use App\Model\Common\StatusSetting;
use App\Model\Mailjob\ActivityLogDay;
use App\Model\Mailjob\ExpiryMailDay;
use App\Traits\ApiKeySettings;
use Carbon\Carbon;
use Illuminate\Http\Request;
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

    protected function getBaseQueryForSystemLogs()
    {
        return Activity::with(['causer:id,user_name,role,first_name,last_name,email'])->select('log_name', 'description', 'event', 'causer_type', 'causer_id', 'created_at', 'properties', 'id');
    }

    protected function filterQueryForActivityLogs($baseQuery)
    {
        $from = request()->input('log_from');
        $till = request()->input('log_till');

        return $baseQuery
            ->when(request()->filled('module'), function ($query) {
                $modules = (array) request()->module;
                $query->whereIn('activity_log.log_name', $modules);
            })
            ->when(request()->filled('event'), function ($query) {
                $events = (array) request()->event;
                $query->whereIn('activity_log.event', $events);
            })
            ->when(request()->filled('performed_by'), function ($query) {
                $performedBy = (array) request()->performed_by;
                $query->whereIn('activity_log.causer_id', $performedBy);
            })
            ->when($from || $till, function ($query) use ($from, $till) {
                $from = $from
                    ? Carbon::parse($from)->startOfDay()
                    : Carbon::minValue();

                $till = $till
                    ? Carbon::parse($till)->endOfDay()
                    : Carbon::now();

                if ($from->lessThanOrEqualTo($till)) {
                    $query->whereBetween('activity_log.created_at', [$from, $till]);
                }
            });
    }

    /**
     * This function is used to create a detailed description for the logs.
     * In the properties column of the activity_log table, the data is stored in the below format
     * {"attributes":{"Status":"Active"},"old":{"Status":"Inactive"}}
     * where old represents the old data and attributes represents the new data.
     */
    protected function formatProperties($properties, $event)
    {
        $formatted = [];

        $old = $properties['old'] ?? [];
        $attributes = $properties['attributes'] ?? [];

        // Helper to clean and escape values
        $escape = function ($value) {
            if (is_array($value) || is_object($value)) {
                $value = json_encode($value); // handle JSON fields
            }

            return htmlspecialchars(strip_tags((string) $value), ENT_QUOTES, 'UTF-8');
        };

        if ($event === 'updated') {
            foreach ($old as $key => $value) {
                $from = empty($value) ? 'null' : $escape($value);
                $to = isset($attributes[$key]) ? $escape($attributes[$key]) : 'null';

                $formatted[] = trans('message.updated').' '.ucfirst($key).' '
                    .trans('message.from').' '.$from.' '
                    .trans('message.to').' '.$to;
            }
        }

        if ($event === 'created') {
            foreach ($attributes as $key => $value) {
                if (! empty($value) && $value !== '--') {
                    $formatted[] = trans('message.set').' '.ucfirst($key).' '
                        .trans('message.to').' '.$escape($value);
                }
            }
        }

        return $formatted;
    }

    /**
     * This function will create a hyper link for the agent/admin who is performing the action.
     */
    protected function generateLinkForPerformedBy($causer)
    {
        if (empty($causer) || empty($causer['id'])) {
            return null;
        }

        $name = trim(($causer['first_name'] ?? '').' '.($causer['last_name'] ?? ''));
        $url = url('clients/'.$causer['id']);

        return sprintf('<a href="%s">%s</a>', e($url), e($name ?: 'Unknown User'));
    }

    public function getScheduler(StatusSetting $status)
    {
        try {
            $cronPath = base_path('artisan');
            $status = $status->find(1);
            $execEnabled = $this->execEnabled();
            $paths = $this->getPHPBinPath();
            $warn = '';
            $condition = new \App\Model\Mailjob\Condition();

            $commands = [
                'everyMinute' => 'Every Minute',
                'everyFiveMinutes' => 'Every Five Minute',
                'everyTenMinutes' => 'Every Ten Minute',
                'everyThirtyMinutes' => 'Every Thirty Minute',
                'hourly' => 'Every Hour',
                'daily' => 'Every Day',
                'dailyAt' => 'Daily at',
                'weekly' => 'Every Week',
                'monthly' => 'Monthly',
                'yearly' => 'Yearly',
            ];

            $expiryDays = [
                '30' => '30 days', '15' => '15 days',
                '7' => '7 days',   '1' => '1 day',
            ];

            $Subs_expiry = $expiryDays;
            $post_expiry = $expiryDays;
            $cloudDays = $expiryDays;

            $invoiceDays = [
                '7' => '7 days', '5' => '5 days',
                '2' => '2 days', '1' => '1 day',
            ];

            $reoonDays = [
                '30' => '30 days', '15' => '15 days', '10' => '10 days',
                '5' => '5 days',   '1' => '1 day',
            ];

            $msg91Days = [
                '720' => '720 Days', '365' => '365 days', '180' => '180 Days',
                '150' => '150 Days', '60' => '60 Days',  '30' => '30 Days',
                '15' => '15 Days',  '5' => '5 Days',   '2' => '2 Days',
                '0' => 'Delete All Reports',
            ];

            $systemLogsDays = [
                '720' => '720 Days', '365' => '365 days', '180' => '180 Days',
                '150' => '150 Days', '60' => '60 Days',  '30' => '30 Days',
                '15' => '15 Days',  '5' => '5 Days',   '2' => '2 Days',
                '0' => 'Delete All Logs',
            ];

            $expiry = ExpiryMailDay::first();
            $activityLog = ActivityLogDay::first();

            $selectedDays = json_decode($expiry->days ?? '[]', true);
            $Auto_expiryday = json_decode($expiry->autorenewal_days ?? '[]', true);
            $post_expiryday = json_decode($expiry->postexpiry_days ?? '[]', true);
            $beforeCloudDay = [$expiry->cloud_days ?? null];
            $invoiceDeletionDay = [$expiry->invoice_days ?? null];
            $msgDeletionDays = [$expiry->msg91_days ?? null];
            $ReeonLogDeletionDays = [$expiry->reoon_logs_days ?? null];
            $systemLogsDeletionDays = [$expiry->system_logs_days ?? null];
            $beforeLogDay = [$activityLog->days ?? null];

            return successResponse(__('message.scheduler_fetched_successfully'), [
                'cronPath' => $cronPath,
                'warn' => $warn,
                'commands' => $commands,
                'condition' => $condition,
                'status' => $status,
                'expiryDays' => $expiryDays,
                'selectedDays' => $selectedDays,
                'delLogDays' => $systemLogsDays,
                'beforeLogDay' => $beforeLogDay,
                'execEnabled' => $execEnabled,
                'paths' => $paths,
                'Subs_expiry' => $Subs_expiry,
                'Auto_expiryday' => $Auto_expiryday,
                'post_expiry' => $post_expiry,
                'post_expiryday' => $post_expiryday,
                'cloudDays' => $cloudDays,
                'beforeCloudDay' => $beforeCloudDay,
                'invoiceDays' => $invoiceDays,
                'invoiceDeletionDay' => $invoiceDeletionDay,
                'msg91Days' => $msg91Days,
                'msgDeletionDays' => $msgDeletionDays,
                'ReeonLogDeletionDays' => $ReeonLogDeletionDays,
                'reoonDays' => $reoonDays,
                'systemLogsDays' => $systemLogsDays,
                'systemLogsDeletionDays' => $systemLogsDeletionDays,
            ]);
        } catch (\Throwable $e) {
            return errorResponse(__('message.something_went_wrong_try_again'));
        }
    }

    public function postSchedular(StatusSetting $status, Request $request)
    {
        try {
            $statusRecord = $status->findOrFail(1);

            $statusRecord->expiry_mail = $request->input('expiry_cron', 0);
            $statusRecord->activity_log_delete = $request->input('activity', 0);
            $statusRecord->subs_expirymail = $request->input('subs_expirymail', 0);
            $statusRecord->post_expirymail = $request->input('postsubs_expirymail', 0);
            $statusRecord->cloud_mail_status = $request->input('cloud_cron', 0);
            $statusRecord->invoice_deletion_status = $request->input('invoice_cron', 0);
            $statusRecord->msg91_report_delete_status = $request->input('msg91_cron', 0);
            $statusRecord->system_log_status = $request->input('systemlogs_cron', 0);
            $statusRecord->reoon_deletion_status = $request->input('reoon_cron', 0);

            $statusRecord->save();

            // Save all cron conditions
            $this->saveConditions($request);

            return successResponse(__('message.updated-successfully'));
        } catch (\Throwable $ex) {
            return errorResponse(__('message.something_went_wrong_try_again'));
        }
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

        \DB::table('expiry_mail_days')->update(['cloud_days' => $request->input('cloud_days'), 'invoice_days' => $request->input('invoice_days'),
            'msg91_days' => $request->input('msg91_days'), 'reoon_logs_days' => $request->input('reoon_days'), 'system_logs_days' => $request->input('system_logs_days')]);
        ActivityLogDay::findOrFail(1)->update(['days' => $request->logdelday]);

        return redirect()->back()->with('success', \Lang::get('message.updated-successfully'));
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

    protected function filterQueryForPaymentLog($query)
    {
        $from = request()->input('from');
        $till = request()->input('till');

        return $query
            // Filter by payment status
            ->when(request()->filled('status'), function ($q) {
                $statuses = (array) request()->status;
                $q->whereIn('status', $statuses);
            })

            // Filter by payment method (Stripe / Razorpay / PayPal)
            ->when(request()->filled('payment_method'), function ($q) {
                $methods = (array) request()->payment_method;
                $q->whereIn('payment_method', $methods);
            })

            // Filter by payment type (subscription / invoice / renewal)
            ->when(request()->filled('payment_type'), function ($q) {
                $types = (array) request()->payment_type;
                $q->whereIn('payment_type', $types);
            })

            // Date filter using the same logic of system logs
            ->when($from || $till, function ($q) use ($from, $till) {
                $from = $from
                    ? Carbon::parse($from)->startOfDay()
                    : Carbon::minValue();
                $till = $till
                    ? Carbon::parse($till)->endOfDay()
                    : Carbon::now();

                if ($from->lessThanOrEqualTo($till)) {
                    $q->whereBetween('created_at', [$from, $till]);
                }
            });
    }
}
