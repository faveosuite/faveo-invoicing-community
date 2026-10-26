<?php

namespace App\Model\Mailjob;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property string|null $job
 * @property string|null $value
 * @property string|null $command
 * @property bool $active
 * @property bool $is_background
 * @property mixed $days
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 *
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Condition newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Condition newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Condition query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Condition whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Condition whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Condition whereJob($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Condition whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Condition whereValue($value)
 *
 * @mixin \Eloquent
 */
class Condition extends Model
{
    protected $table = 'conditions';

    protected $fillable = ['job', 'value', 'command', 'active', 'is_background', 'days'];

    protected $casts = [
        'active' => 'boolean',
        'is_background' => 'boolean',
        'days' => 'json',
    ];

    public static function isActive(string $job): bool
    {
        return (bool) static::where('job', $job)->value('active');
    }

    /**
     * A task's single "N days" setting (retention or grace period), or null when it has none.
     */
    public static function dayCount(string $job): ?int
    {
        $days = static::where('job', $job)->value('days');

        return is_numeric($days) ? (int) $days : null;
    }

    /**
     * A task's "N days before/after" reminder list, e.g. [30, 15, 7, 1]; empty when it has none.
     *
     * @return list<int>
     */
    public static function dayList(string $job): array
    {
        $days = static::where('job', $job)->value('days');

        return is_array($days) ? array_values(array_map(intval(...), $days)) : [];
    }

    /**
     * @return array{condition: string, at: string}
     */
    public function getConditionValue(mixed $job): array
    {
        return self::parseValue($this->where('job', $job)->value('value'));
    }

    /**
     * This task's schedule.
     *
     * @return array{condition: string, at: string}
     */
    public function frequency(): array
    {
        return self::parseValue($this->value);
    }

    /**
     * A stored value is "frequency" or "frequency,time" (e.g. "dailyAt,08:00").
     *
     * @return array{condition: string, at: string}
     */
    private static function parseValue(?string $value): array
    {
        $parts = explode(',', (string) $value);

        return ['condition' => $parts[0], 'at' => $parts[1] ?? ''];
    }

    public function checkArray(mixed $key, mixed $array): mixed
    {
        if (is_array($array) && array_key_exists($key, $array)) {
            return $array[$key];
        }

        return '';
    }
}
