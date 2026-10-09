<?php

namespace App\Traits;

use App\User;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Spatie\Activitylog\Models\Activity;
use Spatie\Activitylog\Models\Concerns\LogsActivity;
use Spatie\Activitylog\Support\LogOptions;

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
    protected ?string $causerID = null;

    abstract protected function getMappings(): array;

    /**
     * Whether the logged item's name links to its admin page. Override to opt out.
     */
    protected function requiresLogUrl(): bool
    {
        return true;
    }

    /**
     * Configure activity log options.
     */
    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly($this->logAttributes)
            ->logOnlyDirty()
            ->dontLogEmptyChanges()
            ->useLogName(__('message.'.$this->getLogName(), [], 'en'));
    }

    /**
     * Tap into the activity before saving.
     */
    public function beforeActivityLogged(Activity $activity, string $eventName): void
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
        // activitylog v5 records the old/new values in attribute_changes (v4 used properties).
        $changes = $activity->attribute_changes instanceof Collection
            ? $activity->attribute_changes
            : collect($activity->attribute_changes ?? []);

        foreach (['attributes', 'old'] as $key) {
            if ($changes->has($key)) {
                $data = $changes->get($key, []);
                $data = $this->formatLoggingAttributes($data, $this->getMappings());
                $changes->put($key, $data);
            }
        }

        $activity->attribute_changes = $changes;
    }

    protected function setCauser(Activity $activity): void
    {
        $userId = $activity->subject->{$this->causerID} ?? null;

        $user = User::find($userId);
        if ($user instanceof User) {
            $activity->causer()->associate($user);
        }
    }

    /**
     * Format attributes using mappings.
     *
     * @param  array<mixed>  $attributes
     * @param  array<mixed>  $mappings
     * @return array<mixed>
     */
    private function formatLoggingAttributes(array $attributes, array $mappings): array
    {
        foreach ($mappings as $key => [$newKey, $transform]) {
            if (Arr::has($attributes, $key)) {
                $value = $attributes[$key];

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
        $logColumn = $this->getLogNameColumn();
        $logUrl = $this->getLogUrl($activity->subject_id);
        $name = $activity->subject->{$logColumn} ?? $logColumn;

        $eventName = $this->resolveDeletedEventName($activity, $eventName);

        // A deleted/suspended record has no page left to open, so only link live ones.
        $linkable = ! in_array($eventName, ['deleted', 'suspended']) && $this->requiresLogUrl() && $logUrl !== null;

        $displayName = sprintf('<strong>%s</strong>', $name);
        if ($linkable) {
            $displayName = '<a href="'.e($logUrl).'">'.$displayName.'</a>';
        }

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
                $activity->subject &&
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
        return $this->logName ?? ''; // @phpstan-ignore nullCoalesce.property
    }

    private function getLogNameColumn(): string
    {
        return $this->logNameColumn; // @phpstan-ignore property.notFound
    }

    /**
     * Get dynamic log URL for the model.
     *
     * If you need to include the ID at the end of the URL, set the logUrl property to an array with two elements:
     *
     * @param  mixed  $id
     */
    protected function getLogUrl($id = null): ?string
    {
        if (empty($this->logUrl['segments'])) {
            return null;
        }

        // ':id' is the logged record's id; any other ':column' is read off the record itself.
        $resolve = function ($s) use ($id) {
            if (! is_string($s) || ! str_starts_with($s, ':')) {
                return $s;
            }

            if ($s === ':id') {
                return $id;
            }

            return $this->getAttribute(substr($s, 1));
        };

        $segments = array_map($resolve, (array) $this->logUrl['segments']);
        $params = array_map($resolve, $this->logUrl['params'] ?? []);

        // Drop only empty segments — a bare array_filter() would also drop a legitimate 0 / '0'.
        $url = url(implode('/', array_filter($segments, fn ($v): bool => $v !== null && $v !== '')));

        return $params === [] ? $url : $url.'?'.http_build_query($params);
    }
}
