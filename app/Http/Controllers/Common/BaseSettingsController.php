<?php

namespace App\Http\Controllers\Common;

use App\Http\Controllers\Common\PHPController as PaymentSettingsController;
use App\Traits\ApiKeySettings;
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
                $query->where('activity_log.created_at', '>=', userDayStart($from));
            })
            ->when($till, function ($query) use ($till): void {
                $query->where('activity_log.created_at', '<=', userDayEnd($till));
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
        $old = $properties['old'] ?? [];
        $attributes = $properties['attributes'] ?? [];

        return match ($event) {
            'updated' => $this->updatedLogLines($old, $attributes),
            'created' => $this->createdLogLines($attributes),
            default => [],
        };
    }

    /**
     * "Updated <Field> From <old> To <new>" for every changed field.
     *
     * @param  array<mixed>  $old
     * @param  array<mixed>  $attributes
     * @return non-falsy-string[]
     */
    private function updatedLogLines(array $old, array $attributes): array
    {
        $formatted = [];
        foreach ($old as $key => $value) {
            $from = empty($value) ? 'null' : $this->escapeLogValue($value);
            $to = isset($attributes[$key]) ? $this->escapeLogValue($attributes[$key]) : 'null';

            $formatted[] = trans('message.updated').' '.ucfirst((string) $key).' '
                .trans('message.from').' '.$from.' '
                .trans('message.to').' '.$to;
        }

        return $formatted;
    }

    /**
     * "Set <Field> To <value>" for every field given a real value.
     *
     * @param  array<mixed>  $attributes
     * @return non-falsy-string[]
     */
    private function createdLogLines(array $attributes): array
    {
        $formatted = [];
        foreach ($attributes as $key => $value) {
            if (! empty($value) && $value !== '--') {
                $formatted[] = trans('message.set').' '.ucfirst((string) $key).' '
                    .trans('message.to').' '.$this->escapeLogValue($value);
            }
        }

        return $formatted;
    }

    /** Log value as safe text: JSON for arrays/objects, tags stripped, HTML-escaped. */
    private function escapeLogValue(mixed $value): string
    {
        if (is_array($value) || is_object($value)) {
            $value = json_encode($value); // handle JSON fields
        }

        return htmlspecialchars(strip_tags((string) $value), ENT_QUOTES, 'UTF-8');
    }
}
