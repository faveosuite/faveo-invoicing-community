<?php

namespace App\Http\Controllers\Common;

use App\Http\Controllers\Common\PHPController as PaymentSettingsController;
use App\Model\Common\StatusSetting;
use App\Model\Mailjob\ActivityLogDay;
use App\Model\Mailjob\ExpiryMailDay;
use App\Traits\ApiKeySettings;
use DB;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Date;
use Lang;
use Spatie\Activitylog\Models\Activity;

class BaseSettingsController extends PaymentSettingsController
{
    use ApiKeySettings;

    protected function filterQuery($baseQuery)
    {
        $from = request()->input('log_from');
        $till = request()->input('log_till');

        return $baseQuery
            ->when(request()->filled('module'), function ($query): void {
                $modules = (array) request()->module;
                $query->whereIn('activity_log.log_name', $modules);
            })
            ->when(request()->filled('event'), function ($query): void {
                $events = (array) request()->event;
                $query->whereIn('activity_log.event', $events);
            })
            ->when(request()->filled('performed_by'), function ($query): void {
                $performedBy = (array) request()->performed_by;
                $query->whereIn('activity_log.causer_id', $performedBy);
            })
            ->when($from, function ($query) use ($from): void {
                $query->where('activity_log.created_at', '>=', Date::parse($from)->startOfDay());
            })
            ->when($till, function ($query) use ($till): void {
                $query->where('activity_log.created_at', '<=', Date::parse($till)->endOfDay());
            });
    }

    /**
     * This function is used to create a detailed description for the logs.
     * In the properties column of the activity_log table, the data is stored in the below format
     * {"attributes":{"Status":"Active"},"old":{"Status":"Inactive"}}
     * where old represents the old data and attributes represents the new data.
     *
     * @return non-falsy-string[]
     */
    protected function formatProperties(array $properties, $event): array
    {
        $formatted = [];

        $old = $properties['old'] ?? [];
        $attributes = $properties['attributes'] ?? [];

        // Helper to clean and escape values
        $escape = function ($value): string {
            if (is_array($value) || is_object($value)) {
                $value = json_encode($value); // handle JSON fields
            }

            return htmlspecialchars(strip_tags((string) $value), ENT_QUOTES, 'UTF-8');
        };

        if ($event === 'updated') {
            foreach ($old as $key => $value) {
                $from = empty($value) ? 'null' : $escape($value);
                $to = isset($attributes[$key]) ? $escape($attributes[$key]) : 'null';

                $formatted[] = trans('message.updated').' '.ucfirst((string) $key).' '
                    .trans('message.from').' '.$from.' '
                    .trans('message.to').' '.$to;
            }
        }

        if ($event === 'created') {
            foreach ($attributes as $key => $value) {
                if (! empty($value) && $value !== '--') {
                    $formatted[] = trans('message.set').' '.ucfirst((string) $key).' '
                        .trans('message.to').' '.$escape($value);
                }
            }
        }

        return $formatted;
    }

    public function postSchedular(StatusSetting $status, Request $request)
    {
        $allStatus = $status->whereId('1')->first();
        $allStatus->expiry_mail = $request->expiry_cron ? $request->expiry_cron : 0;

        $allStatus->activity_log_delete = $request->activity ? $request->activity : 0;

        $allStatus->subs_expirymail = $request->subs_expirymail ? $request->subs_expirymail : 0;

        $allStatus->post_expirymail = $request->postsubs_expirymail ? $request->postsubs_expirymail : 0;

        $allStatus->cloud_mail_status = $request->cloud_cron ?: 0;
        $allStatus->invoice_deletion_status = $request->invoice_cron ?: 0;
        $allStatus->msg91_report_delete_status = $request->msg91_cron ?: 0;
        $allStatus->reoon_deletion_status = $request->reoon_cron ?: 0;
        $allStatus->system_log_status = $request->systemlogs_cron ?: 0;
        $allStatus->installation_logs_status = $request->installationlogs_cron ?: 0;
        $allStatus->license_reports_cleanup_status = $request->licensereports_cron ?: 0;
        $allStatus->license_callbacks_cleanup_status = $request->licensecallbacks_cron ?: 0;
        $allStatus->license_crack_reports_cleanup_status = $request->licensecrack_cron ?: 0;
        $allStatus->license_system_reports_cleanup_status = $request->licensesystem_cron ?: 0;
        $allStatus->license_versions_cleanup_status = $request->licenseversions_cron ?: 0;
        $allStatus->save();
        $this->saveConditions(); // @phpstan-ignore arguments.count

        /* redirect to Index page with Success Message */
        return redirect('job-scheduler')->with('success', Lang::get('message.updated-successfully'));
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
        ActivityLogDay::findOrFail(1)->update(['days' => $request->logdelday]);

        return back()->with('success', Lang::get('message.updated-successfully'));
    }
}
