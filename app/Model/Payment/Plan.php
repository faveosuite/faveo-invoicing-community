<?php

namespace App\Model\Payment;

use App\BaseModel;
use App\Model\Configure\ConfigOption;
use App\Model\Product\Product;
use App\Traits\SystemActivityLogsTrait;
use DB;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Override;

/**
 * @property int $id
 * @property string $name
 * @property int $product
 * @property int $allow_tax
 * @property string|null $days
 * @property int $status
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \Spatie\Activitylog\Models\Activity> $activitiesAsSubject
 * @property-read int|null $activities_as_subject_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, ConfigOption> $configOptions
 * @property-read int|null $config_options_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Model\Payment\Period> $periods
 * @property-read int|null $periods_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Model\Payment\PlanPrice> $planPrice
 * @property-read int|null $plan_price_count
 * @property-read Product|null $productRelation
 * @method static \Database\Factories\Model\Payment\PlanFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Plan newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Plan newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Plan query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Plan whereAllowTax($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Plan whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Plan whereDays($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Plan whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Plan whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Plan whereProduct($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Plan whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Plan whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class Plan extends BaseModel
{
    use HasFactory;
    use SystemActivityLogsTrait;

    protected $table = 'plans';

    protected $fillable = ['name', 'product', 'allow_tax', 'days', 'status'];

    protected string $logName = 'plan';

    protected string $logNameColumn = 'name';

    protected array $logAttributes = [
        'name', 'product', 'allow_tax', 'days', 'status',
    ];

    protected array $logUrl = [
        'segments' => ['plans', ':id', 'edit'],
    ];

    protected function getMappings(): array
    {
        return [
            'name' => ['Plan Name', fn ($value) => $value],
            'product' => ['Product', fn ($value) => Product::find($value)?->name],
            'allow_tax' => ['Allow Tax', fn ($value): array|string => $value === 1 ? __('message.yes') : __('message.no')],
            'days' => ['Plan Days', fn ($value) => $value],
            'status' => ['Status', fn ($value): array|string => $value === 1 ? __('message.active') : __('message.inactive')],
        ];
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany<\App\Model\Payment\PlanPrice, $this>
     */
    public function planPrice(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(PlanPrice::class);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo<\App\Model\Product\Product, $this>
     */
    public function productRelation(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Product::class, 'product', 'id');
    }

    public function periods(): \Illuminate\Database\Eloquent\Relations\BelongsToMany
    {
        return $this->belongstoMany(Period::class, 'plans_periods_relation')->withTimestamps();
    }

    #[Override]
    public function delete()
    {
        return DB::transaction(function () {
            $this->planPrice()->delete();

            return parent::delete();
        });
    }

    public function configOptions()
    {
        return $this->hasMany(ConfigOption::class);
    }
}
