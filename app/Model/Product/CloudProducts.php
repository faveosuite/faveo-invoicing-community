<?php

namespace App\Model\Product;

use App\BaseModel;
use App\Model\Payment\Plan;
use App\Traits\SystemActivityLogsTrait;

/**
 * @property int $id
 * @property int $cloud_product
 * @property int $cloud_free_plan
 * @property string $cloud_product_key
 * @property int $trial_status
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \Spatie\Activitylog\Models\Activity> $activitiesAsSubject
 * @property-read int|null $activities_as_subject_count
 * @property-read Plan $plan
 * @property-read \App\Model\Product\Product $product
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
        $product = Product::find($this->cloud_product)->name ?? 'Unknown Product';
        $plan = Plan::find($this->cloud_free_plan)->name ?? 'Unknown Plan';

        return [
            'cloud_product' => ['Product Name', fn ($value) => Product::find($value)?->name],
            'cloud_free_plan' => ['Free Plan', fn ($value) => Plan::find($value)?->name],
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
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo<\App\Model\Product\Product, $this>
     */
    public function product(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Product::class, 'cloud_product');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo<\App\Model\Payment\Plan, $this>
     */
    public function plan(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Plan::class, 'cloud_free_plan');
    }
}
