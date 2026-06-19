<?php

namespace App\Model\Product;

use App\BaseModel;
use App\Model\Common\PricingTemplate;
use App\Traits\SystemActivityLogsTrait;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Override;

/**
 * @property int $id
 * @property string $name
 * @property string $headline
 * @property string $tagline
 * @property string $available_payment
 * @property int $hidden
 * @property string $cart_link
 * @property int|null $pricing_templates_id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property string $status
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \Spatie\Activitylog\Models\Activity> $activitiesAsSubject
 * @property-read int|null $activities_as_subject_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Model\Product\ConfigurableOption> $config
 * @property-read int|null $config_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Model\Product\GroupFeatures> $features
 * @property-read int|null $features_count
 * @property-read PricingTemplate|null $pricingTemplate
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Model\Product\Product> $product
 * @property-read int|null $product_count
 *
 * @method static \Database\Factories\Model\Product\ProductGroupFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductGroup newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductGroup newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductGroup query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductGroup whereAvailablePayment($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductGroup whereCartLink($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductGroup whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductGroup whereHeadline($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductGroup whereHidden($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductGroup whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductGroup whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductGroup wherePricingTemplatesId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductGroup whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductGroup whereTagline($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductGroup whereUpdatedAt($value)
 *
 * @mixin \Eloquent
 */
class ProductGroup extends BaseModel
{
    /**
     * @use HasFactory<\Illuminate\Database\Eloquent\Factories\Factory>
     */
    use HasFactory;
    use SystemActivityLogsTrait;

    protected $table = 'product_groups';

    protected $fillable = ['id', 'name', 'headline', 'tagline', 'available_payment', 'hidden', 'cart_link', 'pricing_templates_id', 'status'];

    protected string $logName = 'group';

    protected string $logNameColumn = 'name';

    /**
     * @var array<mixed>
     */
    protected array $logAttributes = ['name', 'headline', 'tagline', 'available_payment', 'hidden', 'cart_link', 'pricing_templates_id', 'status'];

    /**
     * @var array<mixed>
     */
    protected array $logUrl = [
        'segments' => ['groups', ':id', 'edit'],
    ];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany<ConfigurableOption, $this>
     */
    public function config(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(ConfigurableOption::class, 'group_id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany<GroupFeatures, $this>
     */
    public function features(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(GroupFeatures::class, 'group_id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany<\App\Model\Product\Product, $this>
     */
    public function product(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Product::class, 'group');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo<\App\Model\Common\PricingTemplate, $this>
     */
    public function pricingTemplate(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(PricingTemplate::class, 'pricing_templates_id', 'id');
    }

    #[Override]
    public function delete(): bool
    {
        $this->config()->delete();
        $this->features()->delete();

        return (bool) parent::delete();
    }

    /**
     * @return array<mixed>
     */
    protected function getMappings(): array
    {
        return [
            'name' => ['Name', fn ($value) => $value],
            'headline' => ['Headline', fn ($value) => $value],
            'tagline' => ['Tagline', fn ($value) => $value],
            'available_payment' => ['Available Payment', fn ($value) => $value],
            'hidden' => ['Hidden', fn ($value) => $value],
            'cart_link' => ['Cart Link', fn ($value) => $value],
            'pricing_templates_id' => ['Pricing Template', fn ($value) => $value],
            'status' => ['Status', fn ($value) => $value],
        ];
    }
}
