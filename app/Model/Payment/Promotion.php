<?php

namespace App\Model\Payment;

use App\BaseModel;
use App\Model\Product\Product;
use App\Traits\SystemActivityLogsTrait;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Override;

/**
 * @property int $id
 * @property string $code
 * @property int $type
 * @property int $uses
 * @property string $value
 * @property string|null $start
 * @property string|null $expiry
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \Spatie\Activitylog\Models\Activity> $activitiesAsSubject
 * @property-read int|null $activities_as_subject_count
 * @property-read Product|null $products
 * @property-read \App\Model\Payment\PromotionType $promotionType
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Model\Payment\PromoProductRelation> $relation
 * @property-read int|null $relation_count
 *
 * @method static \Database\Factories\Model\Payment\PromotionFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Promotion newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Promotion newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Promotion query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Promotion whereCode($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Promotion whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Promotion whereExpiry($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Promotion whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Promotion whereStart($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Promotion whereType($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Promotion whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Promotion whereUses($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Promotion whereValue($value)
 *
 * @mixin \Eloquent
 */
class Promotion extends BaseModel
{
    use HasFactory;
    use SystemActivityLogsTrait;

    protected $table = 'promotions';

    protected $fillable = ['code', 'type', 'uses', 'value', 'start', 'expiry'];

    protected string $logName = 'promotions';

    protected string $logNameColumn = 'code';

    protected array $logAttributes = [
        'code', 'type', 'uses', 'value', 'start', 'expiry',
    ];

    protected array $logUrl = [
        'segments' => ['promotions', ':id', 'edit'],
    ];

    protected function getMappings(): array
    {
        return [
            'code' => ['Promotion Code', fn ($value) => $value],
            'type' => ['Promotion Type', fn ($value): string => $value === 1 ? 'Percentage' : 'Fixed Amount'],
            'uses' => ['Number of Uses', fn ($value) => $value],
            'value' => ['Promotion Value', fn ($value) => $value],
            'start' => ['Start Date', fn ($value) => $value],
            'expiry' => ['Expiry Date', fn ($value) => $value],
        ];
    }

    public function relation(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(PromoProductRelation::class, 'promotion_id');
    }

    #[Override]
    public function delete()
    {
        $this->relation->each(function ($relation): void {
            $relation->delete();
        });

        return parent::delete();
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo<\App\Model\Payment\PromotionType, $this>
     */
    public function promotionType(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(PromotionType::class, 'type', 'id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasOneThrough<\App\Model\Product\Product, \App\Model\Payment\PromoProductRelation, $this>
     */
    public function products(): \Illuminate\Database\Eloquent\Relations\HasOneThrough
    {
        return $this->hasOneThrough(
            Product::class,
            PromoProductRelation::class,
            'promotion_id',
            'id',
            'id',
            'product_id'
        );
    }
}
