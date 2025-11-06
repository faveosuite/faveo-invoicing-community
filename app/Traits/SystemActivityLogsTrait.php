<?php

namespace App\Traits;

use App\User;
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
            ->useLogName(__('message.'.$this->getLogName(), [], 'en'));
    }

    /**
     * Tap into the activity before saving.
     */
    public function tapActivity(Activity $activity, string $eventName): void
    {
        $this->generateDescriptionForLogs($activity, $eventName);
        $this->tapActivityLogs($activity);
        $this->setCauser($activity);
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

    protected function setCauser(Activity $activity)
    {
        $userId = $activity->subject->{$this->causerID} ?? null;

        if ($user = User::find($userId)) {
            $activity->causer()->associate($user);
        }
    }

    /**
     * Format attributes using mappings.
     */
    private function formatLoggingAttributes(array $attributes, array $mappings): array
    {
        foreach ($mappings as $key => [$newKey, $transform]) {
            if (Arr::has($attributes, $key)) {
                $value = $attributes[$key];

                if (is_string($value)) {
                    $value = htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
                }

                $attributes[$newKey] = is_callable($transform)
                    ? $transform($value)
                    : $value;

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

        $eventName = $this->resolveDeletedEventName($activity, $eventName);

        $displayName = in_array($eventName, ['deleted', 'suspended'])
            ? "<strong>{$name}</strong>"
            : ($this->requireLogUrl ?? true
                ? "<a href='{$logUrl}'><strong>{$name}</strong></a>"
                : "<strong>{$name}</strong>");

        $activity->description = __('message.log_description', [
            'module' => __('message.'.$logName, [], 'en'),
            'name' => $displayName,
            'event' => $eventName,
        ], 'en');
    }

    /**
     * ✅ Determine the delete event name for logging.
     * Distinguishes between:
     * - Soft delete → "suspended"
     * - Force delete → "deleted".
     */
    private function resolveDeletedEventName(Activity $activity, string $eventName): string
    {
        if ($eventName === 'deleted') {
            if (
                method_exists($activity->subject, 'isForceDeleting') &&
                ! $activity->subject->isForceDeleting()
            ) {
                return 'suspended';
            }

            return 'deleted';
        }

        return $eventName;
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
        if (empty($this->logUrl['segments'])) {
            return null;
        }

        $segments = array_map(
            fn ($s) => $s === ':id' && $id !== null ? $id : $s,
            (array) $this->logUrl['segments']
        );

        $params = array_map(
            fn ($v) => $v === ':id' && $id !== null ? $id : $v,
            $this->logUrl['params'] ?? []
        );

        $url = url(implode('/', array_filter($segments)));

        if ($params) {
            $url .= '?'.http_build_query($params);
        }

        return $url;
    }
}
