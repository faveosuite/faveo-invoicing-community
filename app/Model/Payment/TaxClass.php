<?php

declare(strict_types=1);

namespace App\Model\Payment;

use App\BaseModel;
use App\Traits\SystemActivityLogsTrait;
use Deprecated;

/**
 * @property int $id
 * @property string $name
 * @property string $slug
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \Spatie\Activitylog\Models\Activity> $activitiesAsSubject
 * @property-read int|null $activities_as_subject_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Model\Payment\TaxRate> $rates
 * @property-read int|null $rates_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Model\Payment\Tax> $tax
 * @property-read int|null $tax_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Model\Payment\TaxProductRelation> $tax_product_relation
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

    protected array $logAttributes = [
        'name', 'slug',
    ];

    protected bool $requireLogUrl = false;

    protected function getMappings(): array
    {
        return [
            'name' => ['Tax Class Name', fn ($value) => $value],
            'slug' => ['Slug', fn ($value) => $value ?: 'standard'],
        ];
    }

    /** Generic tax rates that belong to this class (joined on slug). */
    public function rates()
    {
        return $this->hasMany(TaxRate::class, 'tax_class', 'slug');
    }

    #[Deprecated(message: 'legacy India-GST taxes table; kept for historical data.')]
    public function tax()
    {
        return $this->hasMany(Tax::class, 'tax_classes_id');
    }

    public function tax_product_relation()
    {
        return $this->hasMany(TaxProductRelation::class, 'tax_class_id');
    }
}
