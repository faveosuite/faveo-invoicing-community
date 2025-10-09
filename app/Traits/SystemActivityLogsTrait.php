<?php

namespace App\Traits;

use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Models\Activity;
use Spatie\Activitylog\Traits\LogsActivity;

trait SystemActivityLogsTrait
{
    use LogsActivity;

    /**
     * Define the attribute mappings for logging
     * Example:
     * [
     *   'first_name' => ['name', fn($val) => strtoupper($val)],
     *   'email' => ['email_address', fn($val) => strtolower($val)],
     * ].
     */
    abstract protected function getMappings(): array;

    /**
     * Configure activity log options.
     */
    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly($this->logAttributes ?? [])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs()
            ->useLogName(trans('message.'.$this->getLogName()));
    }

    /**
     * Tap into the activity before saving.
     */
    public function tapActivity(Activity $activity, string $eventName): void
    {
        $this->generateDescriptionForLogs($activity, $eventName);
        $this->tapActivityLogs($activity);
    }

    /**
     * Modify properties based on mappings.
     */
    protected function tapActivityLogs(Activity $activity): void
    {
        $properties = $activity->properties instanceof Collection
            ? $activity->properties
            : collect($activity->properties);

        foreach (['attributes', 'old'] as $key) {
            if ($properties->has($key)) {
                $data = $properties->get($key, []);
                $data = $this->formatLoggingAttributes($data, $this->getMappings());
                $properties->put($key, $data);
            }
        }

        $activity->properties = $properties;
    }

    /**
     * Format attributes using mappings.
     */
    private function formatLoggingAttributes(array $attributes, array $mappings): array
    {
        foreach ($mappings as $key => [$newKey, $transform]) {
            if (Arr::has($attributes, $key)) {
                $attributes[$newKey] = is_callable($transform)
                    ? $transform($attributes[$key])
                    : $attributes[$key];
                unset($attributes[$key]);
            }
        }

        return $attributes;
    }

    /**
     * Generate dynamic description for logs.
     */
    private function generateDescriptionForLogs(Activity $activity, string $eventName): void
    {
        $logName = $this->getLogName();
        $logColumn = $this->logNameColumn ?? 'id';
        $logUrl = $this->getLogUrl($activity->subject_id) ?? '#';
        $name = $activity->subject->{$logColumn} ?? $logColumn;

        if ($eventName === 'deleted') {
            $activity->description = trans('message.log_description', [
                'module' => trans('message.'.$logName),
                'name' => $name,
                'event' => $eventName,
            ]);
        } else {
            $displayName = $this->requireLogUrl ?? true
                ? "<a href='{$logUrl}'>{$name}</a>"
                : '';

            $activity->description = trans('message.'.$logName).' '.
                $displayName.' '.
                trans('message.has_been')." {$eventName}";
        }
    }

    /**
     * Get dynamic log name.
     */
    private function getLogName(): string
    {
        return $this->logName ?? $this->getTable();
    }

    /**
     * Get dynamic log URL for the model.
     *
     * If you need to include the ID at the end of the URL, set the logUrl property to an array with two elements:
     *
     * @param  mixed  $id
     * @return string|null
     */
    protected function getLogUrl($id = null): ?string
    {
        if (empty($this->logUrl)) {
            return null;
        }

        $segments = is_array($this->logUrl) ? $this->logUrl : [$this->logUrl];

        $segments = array_map(fn ($part) => trim($part, '/'), $segments);

        if (count($segments) > 1 && $id !== null) {
            $segments = array_merge(
                [$segments[0]], // first segment
                [$id],          // ID in between
                array_slice($segments, 1) // rest of segments
            );
        }

        $path = implode('/', $segments);

        return url($path);
    }
}
