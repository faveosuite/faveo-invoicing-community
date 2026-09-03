<?php

namespace App\Model\Payment;

use App\BaseModel;
use App\Model\Product\Product;
use App\Traits\SystemActivityLogsTrait;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOneThrough;
use Illuminate\Support\Carbon;
use Override;
use Spatie\Activitylog\Models\Activity;

/**
 * @property int $id
 * @property string $code
 * @property int $type
 * @property int $uses
 * @property string $value
 * @property string|null $start
 * @property string|null $expiry
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read Collection<int, Activity> $activitiesAsSubject
 * @property-read int|null $activities_as_subject_count
 * @property-read Product|null $products
 * @property-read PromotionType $promotionType
 * @property-read Collection<int, PromoProductRelation> $relation
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
    /**
     * @use HasFactory<Factory>
     */
    use HasFactory;

    use SystemActivityLogsTrait;

    protected $table = 'promotions';

    protected $fillable = ['code', 'type', 'uses', 'value', 'start', 'expiry'];

    protected string $logName = 'promotions';

    protected string $logNameColumn = 'code';

    /**
     * @var array<mixed>
     */
    protected array $logAttributes = [
        'code', 'type', 'uses', 'value', 'start', 'expiry',
    ];

    /**
     * @var array<mixed>
     */
    protected array $logUrl = [
        'segments' => ['promotions', ':id', 'edit'],
    ];

    /**
     * @return array<mixed>
     */
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

    /**
     * @return HasMany<PromoProductRelation, $this>
     */
    public function relation(): HasMany
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
     * @return BelongsTo<PromotionType, $this>
     */
    public function promotionType(): BelongsTo
    {
        return $this->belongsTo(PromotionType::class, 'type', 'id');
    }

    /**
     * @return HasOneThrough<Product, PromoProductRelation, $this>
     */
    public function products(): HasOneThrough
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
