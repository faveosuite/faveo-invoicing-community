<?php

declare(strict_types=1);

namespace App\Model\Front;

use App\BaseModel;
use App\Traits\SystemActivityLogsTrait;

/**
 * @property int $id
 * @property string $name
 * @property string $type
 * @property int $publish
 * @property string $content
 * @property int|null $allow_tweets
 * @property int|null $allow_mailchimp
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property int $allow_social_media
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \Spatie\Activitylog\Models\Activity> $activitiesAsSubject
 * @property-read int|null $activities_as_subject_count
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Widgets newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Widgets newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Widgets query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Widgets whereAllowMailchimp($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Widgets whereAllowSocialMedia($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Widgets whereAllowTweets($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Widgets whereContent($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Widgets whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Widgets whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Widgets whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Widgets wherePublish($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Widgets whereType($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Widgets whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class Widgets extends BaseModel
{
    use SystemActivityLogsTrait;

    protected $table = 'widgets';

    protected $fillable = ['name', 'type', 'publish', 'content', 'allow_tweets', 'allow_mailchimp', 'allow_social_media'];

    protected string $logName = 'widgets';

    protected string $logNameColumn = 'name';

    protected array $logAttributes = [
        'name', 'type', 'publish', 'allow_tweets', 'allow_mailchimp', 'allow_social_media',
    ];

    protected array $logUrl = [
        'segments' => ['widgets', ':id', 'edit'],
    ];

    protected function getMappings(): array
    {
        return [
            'name' => ['Name', fn ($value) => $value],
            'type' => ['Type', fn ($value) => $value],
            'publish' => ['Publish status', fn ($value): array|string|null => $value ? __('message.active') : __('message.inactive')],
            'allow_tweets' => ['Allow Tweets', fn ($value): array|string|null => $value ? __('message.active') : __('message.inactive')],
            'allow_mailchimp' => ['Allow Mailchimp', fn ($value): array|string|null => $value ? __('message.active') : __('message.inactive')],
            'allow_social_media' => ['Allow Social Media', fn ($value): array|string|null => $value ? __('message.active') : __('message.inactive')],
        ];
    }
}
