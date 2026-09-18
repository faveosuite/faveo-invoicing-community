<?php

declare(strict_types=1);

namespace App\Model\Payment;

use App\BaseModel;
use App\Traits\SystemActivityLogsTrait;
use Deprecated;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;
use Spatie\Activitylog\Models\Activity;

/**
 * @property int $id
 * @property string $name
 * @property string $slug
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read Collection<int, Activity> $activitiesAsSubject
 * @property-read int|null $activities_as_subject_count
 * @property-read Collection<int, TaxRate> $rates
 * @property-read int|null $rates_count
 * @property-read Collection<int, Tax> $tax
 * @property-read int|null $tax_count
 * @property-read Collection<int, TaxProductRelation> $tax_product_relation
 * @property-read int|null $tax_product_relation_count
 *
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TaxClass newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TaxClass newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TaxClass query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TaxClass whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TaxClass whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TaxClass whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TaxClass whereSlug($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TaxClass whereUpdatedAt($value)
 *
 * @mixin \Eloquent
 */
class TaxClass extends BaseModel
{
    use SystemActivityLogsTrait;

    protected $table = 'tax_classes';

    protected $fillable = ['name', 'slug'];

    protected string $logName = 'tax';

    protected string $logNameColumn = 'name';

    /**
     * @var array<mixed>
     */
    protected array $logAttributes = [
        'name', 'slug',
    ];

    protected bool $requireLogUrl = false;

    /**
     * @return array<mixed>
     */
    protected function getMappings(): array
    {
        return [
            'name' => ['Tax Class Name', fn ($value) => $value],
            'slug' => ['Slug', fn ($value) => $value ?: 'standard'],
        ];
    }

    /** Generic tax rates that belong to this class (joined on slug). */
    /**
     * @return HasMany<TaxRate, $this>
     */
    public function rates(): HasMany
    {
        return $this->hasMany(TaxRate::class, 'tax_class', 'slug');
    }

    /**
     * @return HasMany<Tax, $this>
     */
    #[Deprecated(message: 'legacy India-GST taxes table; kept for historical data.')]
    public function tax(): HasMany
    {
        return $this->hasMany(Tax::class, 'tax_classes_id');
    }

    /**
     * @return HasMany<TaxProductRelation, $this>
     */
    public function tax_product_relation(): HasMany
    {
        return $this->hasMany(TaxProductRelation::class, 'tax_class_id');
    }
}
