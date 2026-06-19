<?php

declare(strict_types=1);

namespace App\Model\Configure;

use App\Model\Payment\Plan;
use App\Model\Product\Product;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int $group_id
 * @property string $config_option_name
 * @property string|null $config_option_description
 * @property int $plan_id
 * @property int|null $product_id
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read ConfigGroup $configGroup
 * @property-read Collection<int, ConfigOptionValue> $configOptionValues
 * @property-read int|null $config_option_values_count
 * @property-read Plan $plan
 * @property-read Product|null $product
 *
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ConfigOption newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ConfigOption newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ConfigOption query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ConfigOption whereConfigOptionDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ConfigOption whereConfigOptionName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ConfigOption whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ConfigOption whereGroupId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ConfigOption whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ConfigOption wherePlanId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ConfigOption whereProductId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ConfigOption whereUpdatedAt($value)
 *
 * @mixin \Eloquent
 */
class ConfigOption extends Model
{
    /**
     * @use HasFactory<Factory>
     */
    use HasFactory;

    protected $table = 'config_option';

    protected $guarded = [];

    // Define the relationship with ConfigGroup
    /**
     * @return BelongsTo<ConfigGroup, $this>
     */
    public function configGroup(): BelongsTo
    {
        return $this->belongsTo(ConfigGroup::class, 'group_id');
    }

    // Define the relationship with Plan
    /**
     * @return BelongsTo<Plan, $this>
     */
    public function plan(): BelongsTo
    {
        return $this->belongsTo(Plan::class);
    }

    // Define the relationship with ConfigOptionValue
    /**
     * @return HasMany<ConfigOptionValue, $this>
     */
    public function configOptionValues(): HasMany
    {
        return $this->hasMany(ConfigOptionValue::class, 'option_id');
    }

    /**
     * @return BelongsTo<Product, $this>
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
}
