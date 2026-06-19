<?php

namespace App\Model\Product;

use App\BaseModel;
use App\Model\Payment\Plan;
use App\Traits\SystemActivityLogsTrait;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;
use Spatie\Activitylog\Models\Activity;

/**
 * @property int $id
 * @property int $cloud_product
 * @property int $cloud_free_plan
 * @property string $cloud_product_key
 * @property int $trial_status
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read Collection<int, Activity> $activitiesAsSubject
 * @property-read int|null $activities_as_subject_count
 * @property-read Plan $plan
 * @property-read Product $product
 *
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CloudProducts newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CloudProducts newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CloudProducts query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CloudProducts whereCloudFreePlan($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CloudProducts whereCloudProduct($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CloudProducts whereCloudProductKey($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CloudProducts whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CloudProducts whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CloudProducts whereTrialStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CloudProducts whereUpdatedAt($value)
 *
 * @mixin \Eloquent
 */
class CloudProducts extends BaseModel
{
    use SystemActivityLogsTrait;

    protected $table = 'cloud_products';

    protected $guarded = [];

    protected string $logName = 'cloud';

    protected string $logNameColumn = 'cloud_product';

    /**
     * @var array<mixed>
     */
    protected array $logAttributes = [
        'cloud_product', 'cloud_free_plan', 'cloud_product_key', 'trial_status',
    ];

    /**
     * @var array<mixed>
     */
    protected array $logUrl = [
        'segments' => ['view/tenant'],
    ];

    /**
     * @return array<mixed>
     */
    protected function getMappings(): array
    {
        $product = Product::where('id', $this->cloud_product)->value('name') ?? 'Unknown Product';
        $plan = Plan::where('id', $this->cloud_free_plan)->value('name') ?? 'Unknown Plan';

        return [
            'cloud_product' => ['Product Name', fn ($value) => Product::find($value)?->name], // @phpstan-ignore property.notFound
            'cloud_free_plan' => ['Free Plan', fn ($value) => Plan::find($value)?->name], // @phpstan-ignore property.notFound
            'cloud_product_key' => ['Product Key', fn ($value) => $value],
            'trial_status' => [
                sprintf('Trial Status for %s (Plan : %s)', $product, $plan),
                fn ($value): array|string => $value ? __('message.active') : __('message.inactive'),
            ],
        ];
    }

    public function getLogNameColumn(): mixed
    {
        return Product::find($this->cloud_product)->name
            ?? $this->cloud_product;
    }

    /**
     * @return BelongsTo<Product, $this>
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class, 'cloud_product');
    }

    /**
     * @return BelongsTo<Plan, $this>
     */
    public function plan(): BelongsTo
    {
        return $this->belongsTo(Plan::class, 'cloud_free_plan');
    }
}
