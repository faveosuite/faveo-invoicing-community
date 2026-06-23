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

    protected function filterQuery(mixed $baseQuery): mixed
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
     * @param  array<mixed>  $properties
     * @return non-falsy-string[]
     */
    protected function formatProperties(array $properties, mixed $event): array
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

}
