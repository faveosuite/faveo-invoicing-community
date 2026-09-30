<?php

declare(strict_types=1);

namespace App\Model\Common;

use App\BaseModel;
use App\Traits\SystemActivityLogsTrait;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Carbon;
use Spatie\Activitylog\Models\Activity;

/**
 * @property int $id
 * @property string $page_key
 * @property string|null $meta_title
 * @property string|null $meta_description
 * @property string|null $og_title
 * @property string|null $og_description
 * @property string|null $og_image
 * @property bool $og_same_as_meta
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read Collection<int, Activity> $activitiesAsSubject
 * @property-read int|null $activities_as_subject_count
 *
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SeoDefaultPage newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SeoDefaultPage newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SeoDefaultPage query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SeoDefaultPage whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SeoDefaultPage whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SeoDefaultPage whereMetaDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SeoDefaultPage whereMetaTitle($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SeoDefaultPage wherePageKey($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SeoDefaultPage whereUpdatedAt($value)
 *
 * @mixin \Eloquent
 */
class SeoDefaultPage extends BaseModel
{
    /**
     * @use HasFactory<Factory>
     */
    use HasFactory;

    use SystemActivityLogsTrait;

    protected $table = 'seo_default_pages';

    protected $fillable = ['page_key', 'meta_title', 'meta_description', 'og_title', 'og_description', 'og_image', 'og_same_as_meta'];

    protected string $logName = 'seo';

    protected string $logNameColumn = 'page_key';

    /**
     * @var array<mixed>
     */
    protected array $logAttributes = [
        'meta_title', 'meta_description', 'og_title', 'og_description', 'og_image', 'og_same_as_meta',
    ];

    /**
     * @var array<mixed>
     */
    protected array $logUrl = [
        'segments' => ['seo', 'default-pages', ':page_key', 'edit'],
    ];

    /**
     * @return array<mixed>
     */
    protected function getMappings(): array
    {
        return [
            'meta_title' => ['Meta Title', fn ($value) => $value],
            'meta_description' => ['Meta Description', fn ($value) => $value],
            'og_title' => ['Open Graph Title', fn ($value) => $value],
            'og_description' => ['Open Graph Description', fn ($value) => $value],
            'og_image' => ['Open Graph Image', fn ($value) => $value],
            'og_same_as_meta' => ['Open Graph Same As Meta', fn ($value): array|string => $value ? __('message.yes') : __('message.no')],
        ];
    }
}
