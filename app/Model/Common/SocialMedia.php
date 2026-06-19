<?php

declare(strict_types=1);

namespace App\Model\Common;

use App\BaseModel;
use App\Traits\SystemActivityLogsTrait;
use Illuminate\Database\Eloquent\Factories\HasFactory;

/**
 * @property int $id
 * @property string $class
 * @property string $fa_class
 * @property string $name
 * @property string $link
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \Spatie\Activitylog\Models\Activity> $activitiesAsSubject
 * @property-read int|null $activities_as_subject_count
 *
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SocialMedia newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SocialMedia newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SocialMedia query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SocialMedia whereClass($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SocialMedia whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SocialMedia whereFaClass($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SocialMedia whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SocialMedia whereLink($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SocialMedia whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SocialMedia whereUpdatedAt($value)
 *
 * @mixin \Eloquent
 */
class SocialMedia extends BaseModel
{
    /**
     * @use HasFactory<\Illuminate\Database\Eloquent\Factories\Factory>
     */
    use HasFactory;
    use SystemActivityLogsTrait;

    protected $table = 'social_media';

    protected $fillable = ['class', 'fa_class', 'name', 'link'];

    protected string $logName = 'social_media';

    protected string $logNameColumn = 'name';

    /**
     * @var array<mixed>
     */
    protected array $logAttributes = [
        'name', 'link',
    ];

    /**
     * @var array<mixed>
     */
    protected array $logUrl = [
        'segments' => ['social-media', ':id', 'edit'],
    ];

    /**
     * @return array<mixed>
     */
    protected function getMappings(): array
    {
        return [
            'name' => ['Social media name', fn ($value) => $value],
            'link' => ['Link', fn ($value) => $value],
        ];
    }
}
