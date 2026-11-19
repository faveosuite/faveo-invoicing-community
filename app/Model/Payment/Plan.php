<?php

namespace App\Model\Payment;

use App\BaseModel;
use App\Model\Product\Entitlement;
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
 * @property-read Collection<int, Entitlement> $entitlements
 * @property-read int|null $entitlements_count
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
     * Plans a client may renew onto: the same product, active, not the 14-day trial, with a
     * renewal price in the currency, and the same kind (one-time stays one-time, recurring
     * stays recurring). The renew popup lists these and the renew endpoint accepts only these.
     *
     * @param  Builder<static>  $query
     */
    #[Scope]
    protected function renewableFor(Builder $query, int $productId, bool $oneTime, string $currency): void
    {
        $query->where('product', $productId)
            ->where('status', 1)
            ->where('days', '!=', 14)
            ->when(
                $oneTime,
                fn ($q) => $q->whereHas('periods', fn ($p) => $p->where('name', 'One Time')),
                fn ($q) => $q->whereDoesntHave('periods', fn ($p) => $p->where('name', 'One Time')),
            )
            ->whereHas('planPrice', fn ($q) => $q->where('currency', $currency)->where('renew_price', '!=', '0'))
            // A plan charging for every agent needs an agent price in the currency, or agents are free.
            ->where(fn ($q) => $q
                ->whereDoesntHave('entitlements', fn ($e) => $e->where('code', 'agents'))
                ->orWhereExists(fn ($e) => $e->from('entitlement_prices')
                    ->join('entitlements', 'entitlements.id', '=', 'entitlement_prices.entitlement_id')
                    ->where('entitlements.code', 'agents')
                    ->whereColumn('entitlement_prices.plan_id', 'plans.id')
                    ->where('entitlement_prices.currency', $currency)));
    }

    /**
     * Plans a cloud order on $product can switch to: another cloud product that allows agent
     * changes, priced to renew, and not a free plan. The client's plan list and the
     * plan-change endpoints both read this, so only listed plans can be bought.
     *
     * @param  Builder<static>  $query
     */
    #[Scope]
    protected function cloudPlanChangeOptions(Builder $query, Product $product): void
    {
        $query->where('product', '!=', $product->id)
            ->whereHas('productRelation', fn (\Illuminate\Contracts\Database\Query\Builder $q) => $q->where('type', 4)->where('can_modify_agent', 1))
            ->whereHas('planPrice', fn (\Illuminate\Contracts\Database\Query\Builder $q) => $q->where('renew_price', '!=', 0))
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

    /** The plan's billing period (periods.id); prices for extras are set per period. */
    public function periodId(): ?int
    {
        $id = $this->periods()->value('periods.id');

        return $id === null ? null : (int) $id;
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
     * The features this plan includes, with its value (pivot `value`, stored form; null = the
     * feature's default).
     *
     * @return BelongsToMany<Entitlement, $this, Pivot>
     */
    public function entitlements(): BelongsToMany
    {
        return $this->belongsToMany(Entitlement::class, 'plan_entitlements')->withPivot('value')->withTimestamps();
    }
}
