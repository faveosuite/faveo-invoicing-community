<?php

namespace App\Model\Payment;

use App\BaseModel;
use App\Model\Configure\ConfigOption;
use App\Model\Product\Product;
use App\Traits\SystemActivityLogsTrait;
use DB;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\Pivot;
use Illuminate\Support\Carbon;
use Override;
use Spatie\Activitylog\Models\Activity;

/**
 * @property int $id
 * @property string $name
 * @property int $product
 * @property int $allow_tax
 * @property string|null $days
 * @property int $status
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read Collection<int, Activity> $activitiesAsSubject
 * @property-read int|null $activities_as_subject_count
 * @property-read Collection<int, ConfigOption> $configOptions
 * @property-read int|null $config_options_count
 * @property-read Collection<int, Period> $periods
 * @property-read int|null $periods_count
 * @property-read Collection<int, PlanPrice> $planPrice
 * @property-read int|null $plan_price_count
 * @property-read Product|null $productRelation
 *
 * @method static Builder<static>|Plan cloudPlanChangeOptions(Product $product)
 * @method static \Database\Factories\Model\Payment\PlanFactory factory($count = null, $state = [])
 * @method static Builder<static>|Plan newModelQuery()
 * @method static Builder<static>|Plan newQuery()
 * @method static Builder<static>|Plan query()
 * @method static Builder<static>|Plan whereAllowTax($value)
 * @method static Builder<static>|Plan whereCreatedAt($value)
 * @method static Builder<static>|Plan whereDays($value)
 * @method static Builder<static>|Plan whereId($value)
 * @method static Builder<static>|Plan whereName($value)
 * @method static Builder<static>|Plan whereProduct($value)
 * @method static Builder<static>|Plan whereStatus($value)
 * @method static Builder<static>|Plan whereUpdatedAt($value)
 *
 * @mixin \Eloquent
 */
class Plan extends BaseModel
{
    /**
     * @use HasFactory<Factory>
     */
    use HasFactory;

    use SystemActivityLogsTrait;

    protected $table = 'plans';

    protected $fillable = ['name', 'product', 'allow_tax', 'days', 'status'];

    protected string $logName = 'plan';

    protected string $logNameColumn = 'name';

    /**
     * @var array<mixed>
     */
    protected array $logAttributes = [
        'name', 'product', 'allow_tax', 'days', 'status',
    ];

    /**
     * @var array<mixed>
     */
    protected array $logUrl = [
        'segments' => ['admin', 'products', 'plans', ':id', 'edit'],
    ];

    /**
     * @return array<mixed>
     */
    protected function getMappings(): array
    {
        return [
            'name' => ['Plan Name', fn ($value) => $value],
            'product' => ['Product', fn ($value) => Product::find($value)?->name], // @phpstan-ignore property.notFound
            'allow_tax' => ['Allow Tax', fn ($value): array|string => $value === 1 ? __('message.yes') : __('message.no')],
            'days' => ['Plan Days', fn ($value) => $value],
            'status' => ['Status', fn ($value): array|string => $value === 1 ? __('message.active') : __('message.inactive')],
        ];
    }

    /**
     * @return HasMany<PlanPrice, $this>
     */
    public function planPrice(): HasMany
    {
        return $this->hasMany(PlanPrice::class);
    }

    /**
     * Plans a cloud order on $product can switch to: another cloud product that allows agent
     * changes, priced to renew, and not a free plan. The client's plan list and the
     * plan-change endpoints both read this, so only listed plans can be bought.
     *
     * @param  Builder<Plan>  $query
     */
    #[Scope]
    protected function cloudPlanChangeOptions(Builder $query, Product $product): void
    {
        $query->where('product', '!=', $product->id)
            ->whereHas('productRelation', fn ($q) => $q->where('type', 4)->where('can_modify_agent', 1))
            ->whereHas('planPrice', fn ($q) => $q->where('renew_price', '!=', 0))
            ->where('name', 'not like', '%free%');
    }

    /**
     * @return BelongsTo<Product, $this>
     */
    public function productRelation(): BelongsTo
    {
        return $this->belongsTo(Product::class, 'product', 'id');
    }

    /**
     * How long the plan runs, as the admin lists show it. The chosen period ("One Time") beats
     * the days, which on a one-time plan are only the updates window; else the days ("1 Month").
     * Load `periods` first when listing many plans.
     */
    public function periodLabel(): ?string
    {
        return $this->periods->first()->name ?? formatDays((int) $this->days);
    }

    /**
     * @return BelongsToMany<Model, Model, Pivot, string>
     */
    public function periods(): BelongsToMany
    {
        return $this->belongstoMany(Period::class, 'plans_periods_relation')->withTimestamps(); // @phpstan-ignore return.type
    }

    #[Override]
    public function delete()
    {
        return DB::transaction(function () {
            $this->planPrice()->delete();
            $this->periods()->detach();

            return parent::delete();
        });
    }

    /**
     * @return HasMany<ConfigOption, $this>
     */
    public function configOptions(): HasMany
    {
        return $this->hasMany(ConfigOption::class);
    }
}
