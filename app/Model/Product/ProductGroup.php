<?php

namespace App\Model\Product;

use App\BaseModel;
use App\Model\Common\PricingTemplate;
use App\Traits\SystemActivityLogsTrait;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;
use Override;
use Spatie\Activitylog\Models\Activity;

/**
 * @property int $id
 * @property string $name
 * @property string $headline
 * @property string $tagline
 * @property string $available_payment
 * @property int $hidden
 * @property string $cart_link
 * @property int|null $pricing_templates_id
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property string $status
 * @property string|null $meta_title
 * @property string|null $meta_description
 * @property string|null $og_title
 * @property string|null $og_description
 * @property string|null $og_image
 * @property bool $og_same_as_meta
 * @property-read Collection<int, Activity> $activitiesAsSubject
 * @property-read int|null $activities_as_subject_count
 * @property-read Collection<int, ConfigurableOption> $config
 * @property-read int|null $config_count
 * @property-read Collection<int, GroupFeatures> $features
 * @property-read int|null $features_count
 * @property-read PricingTemplate|null $pricingTemplate
 * @property-read Collection<int, Product> $product
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
     * @use HasFactory<Factory>
     */
    use HasFactory;

    use SystemActivityLogsTrait;

    protected $table = 'product_groups';

    protected $fillable = ['id', 'name', 'headline', 'tagline', 'available_payment', 'hidden', 'cart_link', 'pricing_templates_id', 'status', 'meta_title', 'meta_description', 'og_title', 'og_description', 'og_image', 'og_same_as_meta'];

    protected string $logName = 'group';

    protected string $logNameColumn = 'name';

    /**
     * @var array<mixed>
     */
    protected array $logAttributes = ['name', 'headline', 'tagline', 'available_payment', 'hidden', 'cart_link', 'pricing_templates_id', 'status', 'meta_title', 'meta_description', 'og_title', 'og_description', 'og_image', 'og_same_as_meta'];

    /**
     * @var array<mixed>
     */
    protected array $logUrl = [
        'segments' => ['groups', ':id', 'edit'],
    ];

    /**
     * @return HasMany<ConfigurableOption, $this>
     */
    public function config(): HasMany
    {
        return $this->hasMany(ConfigurableOption::class, 'group_id');
    }

    /**
     * @return HasMany<GroupFeatures, $this>
     */
    public function features(): HasMany
    {
        return $this->hasMany(GroupFeatures::class, 'group_id');
    }

    /**
     * @return HasMany<Product, $this>
     */
    public function product(): HasMany
    {
        return $this->hasMany(Product::class, 'group');
    }

    /**
     * @return BelongsTo<PricingTemplate, $this>
     */
    public function pricingTemplate(): BelongsTo
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
            'meta_title' => ['Meta Title', fn ($value) => $value],
            'meta_description' => ['Meta Description', fn ($value) => $value],
            'og_title' => ['Open Graph Title', fn ($value) => $value],
            'og_description' => ['Open Graph Description', fn ($value) => $value],
            'og_image' => ['Open Graph Image', fn ($value) => $value],
            'og_same_as_meta' => ['Open Graph Same As Meta', fn ($value): array|string => $value ? __('message.yes') : __('message.no')],
        ];
    }
}
