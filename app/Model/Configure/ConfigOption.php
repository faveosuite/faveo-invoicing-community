<?php

declare(strict_types=1);

namespace App\Model\Configure;

use App\Model\Payment\Plan;
use App\Model\Product\Product;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property int $group_id
 * @property string $config_option_name
 * @property string|null $config_option_description
 * @property int $plan_id
 * @property int|null $product_id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Model\Configure\ConfigGroup $configGroup
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Model\Configure\ConfigOptionValue> $configOptionValues
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
     * @use HasFactory<\Illuminate\Database\Eloquent\Factories\Factory>
     */
    use HasFactory;

    protected $table = 'config_option';

    protected $guarded = [];

    // Define the relationship with ConfigGroup
    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo<ConfigGroup, $this>
     */
    public function configGroup(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(ConfigGroup::class, 'group_id');
    }

    // Define the relationship with Plan
    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo<Plan, $this>
     */
    public function plan(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Plan::class);
    }

    // Define the relationship with ConfigOptionValue
    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany<\App\Model\Configure\ConfigOptionValue, $this>
     */
    public function configOptionValues(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(ConfigOptionValue::class, 'option_id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo<Product, $this>
     */
    public function product(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
}
