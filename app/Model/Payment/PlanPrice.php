<?php

namespace App\Model\Payment;

use App\Model\Common\Country;
use App\Traits\SystemActivityLogsTrait;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property int $plan_id
 * @property string $currency
 * @property string $add_price
 * @property string $renew_price
 * @property string|null $price_description
 * @property string|null $product_quantity
 * @property string|null $no_of_agents
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property string|null $offer_price
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \Spatie\Activitylog\Models\Activity> $activitiesAsSubject
 * @property-read int|null $activities_as_subject_count
 * @property-read \App\Model\Payment\Plan|null $plan
 * @method static \Database\Factories\Model\Payment\PlanPriceFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PlanPrice newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PlanPrice newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PlanPrice query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PlanPrice whereAddPrice($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PlanPrice whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PlanPrice whereCurrency($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PlanPrice whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PlanPrice whereNoOfAgents($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PlanPrice whereOfferPrice($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PlanPrice wherePlanId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PlanPrice wherePriceDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PlanPrice whereProductQuantity($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PlanPrice whereRenewPrice($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PlanPrice whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class PlanPrice extends Model
{
    /**
     * @use HasFactory<\Illuminate\Database\Eloquent\Factories\Factory>
     */
    use HasFactory;
    use SystemActivityLogsTrait;

    protected $table = 'plan_prices';

    protected $fillable = ['plan_id', 'currency', 'add_price', 'renew_price', 'price_description', 'product_quantity', 'no_of_agents', 'offer_price'];

    protected string $logName = 'plan';

    protected string $logNameColumn = 'price';

    /**
     * @var array<mixed>
     */
    protected array $logAttributes = [
        'plan_id', 'currency', 'add_price', 'renew_price', 'price_description', 'product_quantity', 'no_of_agents', 'country_id', 'offer_price',
    ];

    /**
     * @var array<mixed>
     */
    protected array $logUrl = [
        'segments' => ['plans'],
    ];

    /**
     * @return array<mixed>
     */
    protected function getMappings(): array
    {
        return [
            'plan_id' => ['Plan Name', fn ($value) => Plan::find($value)?->name], // @phpstan-ignore property.notFound
            'currency' => ['Currency', fn ($value) => $value],
            'add_price' => ['Add Price', fn ($value) => $value],
            'renew_price' => ['Renew Price', fn ($value) => $value],
            'price_description' => ['Price Description', fn ($value) => $value],
            'product_quantity' => ['Product Quantity', fn ($value) => $value],
            'no_of_agents' => ['Number of Agents', fn ($value) => $value],
            'country_id' => ['Country', fn ($value) => Country::find($value)?->name], // @phpstan-ignore property.notFound
            'offer_price' => ['Offer Price', fn ($value) => $value],
        ];
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo<Plan, $this>
     */
    public function plan(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Plan::class, 'plan_id', 'id');
    }
}
