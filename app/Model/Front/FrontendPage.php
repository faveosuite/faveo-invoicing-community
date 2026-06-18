<?php

namespace App\Model\Front;

use App\BaseModel;
use App\Traits\SystemActivityLogsTrait;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Facades\Date;

/**
 * @property int $id
 * @property int $parent_page_id
 * @property string $slug
 * @property string $name
 * @property string $content
 * @property string $url
 * @property string $type
 * @property int $publish
 * @property int $hidden
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \Spatie\Activitylog\Models\Activity> $activitiesAsSubject
 * @property-read int|null $activities_as_subject_count
 * @property-read FrontendPage|null $parent
 * @method static \Database\Factories\Model\Front\FrontendPageFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|FrontendPage newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|FrontendPage newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|FrontendPage query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|FrontendPage whereContent($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|FrontendPage whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|FrontendPage whereHidden($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|FrontendPage whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|FrontendPage whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|FrontendPage whereParentPageId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|FrontendPage wherePublish($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|FrontendPage whereSlug($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|FrontendPage whereType($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|FrontendPage whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|FrontendPage whereUrl($value)
 * @mixin \Eloquent
 */
class FrontendPage extends BaseModel
{
    use HasFactory;

    use SystemActivityLogsTrait;

    protected $table = 'frontend_pages';

    protected $fillable = ['parent_page_id', 'slug', 'name', 'content', 'url', 'publish', 'type', 'created_at'];

    protected string $logName = 'pages';

    protected string $logNameColumn = 'name';

    protected array $logAttributes = [
        'parent_page_id', 'slug', 'name', 'content', 'url', 'publish', 'type', 'created_at',
    ];

    protected array $logUrl = [
        'segments' => ['pages', ':id', 'edit'],
    ];

    protected function getMappings(): array
    {
        return [
            'parent_page_id' => ['Parent Page', fn ($value) => $value],
            'slug' => ['Slug', fn ($value) => $value],
            'name' => ['Name', fn ($value) => $value],
            'content' => ['Content', fn ($value) => $value],
            'url' => ['URL', fn ($value) => $value],
            'publish' => ['Publish status', fn ($value): array|string => $value ? __('message.active') : __('message.inactive')],
            'type' => ['Type', fn ($value) => $value],
            'created_at' => [
                'Publishing Date',
                fn ($value) => Date::parse($value)->format('d M Y, h:i A'),
            ],
        ];
    }

    protected function slug(): \Illuminate\Database\Eloquent\Casts\Attribute
    {
        return \Illuminate\Database\Eloquent\Casts\Attribute::make(set: function ($value): array {
            return ['slug' => str_replace(' ', '', $value)];
        });
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo<\App\Model\Front\FrontendPage, $this>
     */
    public function parent(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(FrontendPage::class, 'parent_page_id');
    }
}
