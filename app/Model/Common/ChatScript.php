<?php

declare(strict_types=1);

namespace App\Model\Common;

use App\Traits\SystemActivityLogsTrait;
use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property string|null $name
 * @property string|null $script
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property int $on_registration
 * @property int $on_every_page
 * @property int $google_analytics
 * @property string|null $google_analytics_tag
 * @property int $non_authenticated
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \Spatie\Activitylog\Models\Activity> $activitiesAsSubject
 * @property-read int|null $activities_as_subject_count
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ChatScript newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ChatScript newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ChatScript query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ChatScript whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ChatScript whereGoogleAnalytics($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ChatScript whereGoogleAnalyticsTag($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ChatScript whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ChatScript whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ChatScript whereNonAuthenticated($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ChatScript whereOnEveryPage($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ChatScript whereOnRegistration($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ChatScript whereScript($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ChatScript whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class ChatScript extends Model
{
    use SystemActivityLogsTrait;

    protected $table = 'chat_scripts';

    protected $fillable = ['name', 'script', 'on_registration', 'on_every_page', 'google_analytics', 'google_analytics_tag'];

    protected string $logName = 'chat-script';

    protected string $logNameColumn = 'name';

    protected array $logAttributes = [
        'name', 'script', 'on_registration', 'on_every_page', 'google_analytics', 'google_analytics_tag',
    ];

    protected array $logUrl = [
        'segments' => ['chat', ':id', 'edit'],
    ];

    protected function getMappings(): array
    {
        return [
            'name' => ['Name', fn ($value) => $value],
            'script' => ['Script', fn ($value) => $value],
            'on_registration' => ['On Registration', fn ($value): array|string|null => (int) $value === 1 ? __('message.active') : __('message.inactive')],
            'on_every_page' => ['On Every Page', fn ($value): array|string|null => (int) $value === 1 ? __('message.active') : __('message.inactive')],
            'google_analytics' => ['Google Analytics', fn ($value): array|string|null => (int) $value === 1 ? __('message.active') : __('message.inactive')],
            'google_analytics_tag' => ['Google Analytics Tag', fn ($value) => $value],
        ];
    }
}
