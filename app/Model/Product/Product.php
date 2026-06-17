<?php

namespace App\Model\Product;

use App\BaseModel;
use App\Facades\Attach;
use App\License\Models\Installation;
use App\License\Models\License;
use App\License\Models\LicenseCallback;
use App\License\Models\LicenseReport;
use App\Model\Configure\ConfigOption;
use App\Model\Configure\PluginCompatibleWithProducts;
use App\Model\Configure\ProductPluginGroup;
use App\Model\License\LicenseType;
use App\Model\Order\Order;
use App\Model\Payment\Plan;
use App\Model\Payment\PromoProductRelation;
use App\Model\Payment\TaxClass;
use App\Model\Payment\TaxProductRelation;
use App\Traits\SystemActivityLogsTrait;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\Pivot;
use Override;

/**
 * @property int $id
 * @property string $name
 * @property string $description
 * @property string $short_description
 * @property string $category
 * @property array $parent
 * @property int $type
 * @property int $group
 * @property string $welcome_email
 * @property int $require_domain
 * @property int|null $can_modify_agent
 * @property int|null $can_modify_quantity
 * @property int|null $show_agent
 * @property int $tax_apply
 * @property int|null $show_product_quantity
 * @property int $deny_after_subscription
 * @property int $hidden
 * @property int $invoice_hidden
 * @property int $multiple_qty
 * @property string $auto_terminate
 * @property int $setup_order_placed
 * @property int $setup_first_payment
 * @property int $setup_accept_manually
 * @property int $no_auto_setup
 * @property string $shoping_cart_link
 * @property string $file
 * @property string $image
 * @property string $version
 * @property string $github_owner
 * @property string $github_repository
 * @property string $process_url
 * @property \Illuminate\Database\Eloquent\Collection<int, \App\Model\Product\Subscription> $subscription
 * @property string|null $product_sku
 * @property int|null $perpetual_license
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property string $highlight
 * @property string $status
 * @property string $add_to_contact
 * @property string|null $product_type
 * @property string $product_description
 * @property string|null $product_url_homepage
 * @property string|null $product_url_download
 * @property string|null $product_envato_id
 * @property string|null $product_key
 * @property int $product_max_active_versions
 * @property string $whatsapp_integration
 * @property-read \Illuminate\Database\Eloquent\Collection<int, PromoProductRelation> $PromoRelation
 * @property-read int|null $promo_relation_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \Spatie\Activitylog\Models\Activity> $activitiesAsSubject
 * @property-read int|null $activities_as_subject_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, Product> $bundledPlugins
 * @property-read int|null $bundled_plugins_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, Product> $compatiblePlugins
 * @property-read int|null $compatible_plugins_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, ConfigOption> $configOptions
 * @property-read int|null $config_options_count
 * @property-read \App\Model\Product\ProductGroup|null $groupRelation
 * @property-read \Illuminate\Database\Eloquent\Collection<int, Installation> $installations
 * @property-read int|null $installations_count
 * @property-read \App\Model\Product\ProductUpload|null $latestVersion
 * @property-read \Illuminate\Database\Eloquent\Collection<int, LicenseCallback> $licenseCallbacks
 * @property-read int|null $license_callbacks_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, LicenseReport> $licenseReports
 * @property-read int|null $license_reports_count
 * @property-read LicenseType|null $licenseType
 * @property-read \Illuminate\Database\Eloquent\Collection<int, License> $licenses
 * @property-read int|null $licenses_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, Order> $order
 * @property-read int|null $order_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, Plan> $planRelation
 * @property-read int|null $plan_relation_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, PluginCompatibleWithProducts> $pluginCompWith
 * @property-read int|null $plugin_comp_with_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Model\Product\Price> $price
 * @property-read int|null $price_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, PluginCompatibleWithProducts> $productCompWith
 * @property-read int|null $product_comp_with_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, ProductPluginGroup> $productPluginGroupsAsPlugin
 * @property-read int|null $product_plugin_groups_as_plugin_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, ProductPluginGroup> $productPluginGroupsAsProduct
 * @property-read int|null $product_plugin_groups_as_product_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Model\Product\ProductUpload> $productUpload
 * @property-read int|null $product_upload_count
 * @property-read int|null $subscription_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, TaxProductRelation> $tax
 * @property-read int|null $tax_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, TaxClass> $taxes
 * @property-read int|null $taxes_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Model\Product\ProductUpload> $versions
 * @property-read int|null $versions_count
 * @method static \Database\Factories\Model\Product\ProductFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Product newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Product newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Product query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Product whereAddToContact($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Product whereAutoTerminate($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Product whereCanModifyAgent($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Product whereCanModifyQuantity($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Product whereCategory($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Product whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Product whereDenyAfterSubscription($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Product whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Product whereFile($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Product whereGithubOwner($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Product whereGithubRepository($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Product whereGroup($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Product whereHidden($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Product whereHighlight($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Product whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Product whereImage($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Product whereInvoiceHidden($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Product whereMultipleQty($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Product whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Product whereNoAutoSetup($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Product whereParent($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Product wherePerpetualLicense($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Product whereProcessUrl($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Product whereProductDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Product whereProductEnvatoId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Product whereProductKey($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Product whereProductMaxActiveVersions($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Product whereProductSku($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Product whereProductType($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Product whereProductUrlDownload($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Product whereProductUrlHomepage($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Product whereRequireDomain($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Product whereSetupAcceptManually($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Product whereSetupFirstPayment($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Product whereSetupOrderPlaced($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Product whereShopingCartLink($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Product whereShortDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Product whereShowAgent($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Product whereShowProductQuantity($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Product whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Product whereSubscription($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Product whereTaxApply($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Product whereType($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Product whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Product whereVersion($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Product whereWelcomeEmail($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Product whereWhatsappIntegration($value)
 * @mixin \Eloquent
 */
class Product extends BaseModel
{
    use HasFactory;
    use SystemActivityLogsTrait;

    protected $table = 'products';

    /**
     * --------------------------------------------------------------------------
     * Model Attributes
     * --------------------------------------------------------------------------.
     *
     * @property int $id
     * @property string $name
     * @property string|null $description
     * @property int $status Product status:
     *                       1 = Active — monthly/yearly subscription toggle is shown in the store
     *                       0 = Inactive — product is visible in the store but monthly/yearly toggle is hidden
     *                       --------------------------------------------------------------------------
     */
    protected $fillable = ['id', 'name', 'description', 'short_description', 'type', 'group', 'file', 'image', 'require_domain', 'category',
        'can_modify_agent',  'can_modify_quantity', 'show_agent', 'tax_apply', 'show_product_quantity', 'hidden',  'auto_terminate',
        'setup_order_placed', 'setup_first_payment', 'setup_accept_manually',
        'no_auto_setup', 'shoping_cart_link', 'process_url', 'github_owner',
        'github_repository',
        'deny_after_subscription', 'version', 'parent', 'subscription', 'product_sku', 'perpetual_license', 'product_description', 'invoice_hidden',
        'status', 'whatsapp_integration',
    ];

    protected string $logName = 'product';

    protected string $logNameColumn = 'name';

    protected array $logAttributes = [
        'name', 'type', 'group', 'file', 'image', 'require_domain', 'category',
        'can_modify_agent',  'can_modify_quantity', 'show_agent', 'tax_apply', 'show_product_quantity', 'hidden',  'auto_terminate',
        'setup_order_placed', 'setup_first_payment', 'setup_accept_manually',
        'no_auto_setup', 'shoping_cart_link', 'process_url', 'github_owner',
        'github_repository',
        'deny_after_subscription', 'version', 'subscription', 'product_sku', 'perpetual_license', 'invoice_hidden',
    ];

    protected array $logUrl = [
        'segments' => ['products', ':id', 'edit'],
    ];

    protected function getMappings(): array
    {
        return [
            'type' => ['License Type', fn ($value) => $value ? LicenseType::find($value)?->name : 'No Type'],
            'group' => ['Product Group', fn ($value) => $value ? ProductGroup::find($value)?->name : 'No Group'],
            'file' => ['Product File', fn ($value) => $value],
            'image' => ['Product Image', fn ($value) => $value],
            'require_domain' => ['Require Domain', fn ($value): array|string|null => $value === 1 ? __('message.yes') : __('message.no')],
            'category' => ['Category', fn ($value) => $value],
            'can_modify_agent' => ['Can Modify Agent', fn ($value): array|string|null => $value === 1 ? __('message.yes') : __('message.no')],
            'can_modify_quantity' => ['Can Modify Quantity', fn ($value): array|string|null => $value === 1 ? __('message.yes') : __('message.no')],
            'show_agent' => ['Show Agent', fn ($value): array|string|null => $value === 1 ? __('message.yes') : __('message.no')],
            'tax_apply' => ['Tax Apply', fn ($value): array|string|null => $value === 1 ? __('message.yes') : __('message.no')],
            'show_product_quantity' => ['Show Product Quantity', fn ($value): array|string|null => $value === 1 ? __('message.yes') : __('message.no')],
            'hidden' => ['Hidden', fn ($value): array|string|null => $value === 1 ? __('message.yes') : __('message.no')],
            'auto_terminate' => ['Auto Terminate', fn ($value): array|string|null => $value === 1 ? __('message.yes') : __('message.no')],
            'setup_order_placed' => ['Setup on Order Placed', fn ($value): array|string|null => $value === 1 ? __('message.yes') : __('message.no')],
            'setup_first_payment' => ['Setup on First Payment', fn ($value): array|string|null => $value === 1 ? __('message.yes') : __('message.no')],
            'setup_accept_manually' => ['Setup Accept Manually', fn ($value): array|string|null => $value === 1 ? __('message.yes') : __('message.no')],
            'no_auto_setup' => ['No Auto Setup', fn ($value): array|string|null => $value === 1 ? __('message.yes') : __('message.no')],
            'shoping_cart_link' => ['Shopping Cart Link', fn ($value) => $value],
            'process_url' => ['Process URL', fn ($value) => $value],
            'github_owner' => ['GitHub Owner', fn ($value) => $value],
            'github_repository' => ['GitHub Repository', fn ($value) => $value],
            'deny_after_subscription' => ['Deny After Subscription', fn ($value): array|string|null => $value === 1 ? __('message.yes') : __('message.no')],
            'version' => ['Version', fn ($value) => $value],
            'subscription' => ['Subscription', fn ($value) => $value],
            'product_sku' => ['Product SKU', fn ($value) => $value],
            'perpetual_license' => ['Perpetual License', fn ($value): array|string|null => $value === 1 ? __('message.yes') : __('message.no')],
            'invoice_hidden' => ['Hide on Invoice', fn ($value): array|string|null => $value === 1 ? __('message.yes') : __('message.no')],
        ];
    }

    // protected static $recordEvents = ['deleted'];

    public function order()
    {
        return $this->hasMany(Order::class, 'product');
    }

    public function subscription()
    {
        return $this->hasMany(Subscription::class);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo<\App\Model\License\LicenseType, $this>
     */
    public function licenseType(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(LicenseType::class, 'type');
    }

    public function price()
    {
        return $this->hasMany(Price::class);
    }

    public function PromoRelation()
    {
        return $this->hasMany(PromoProductRelation::class, 'product_id');
    }

    public function tax()
    {
        return $this->hasMany(TaxProductRelation::class, 'product_id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany<\App\Model\Payment\TaxClass, $this, \Illuminate\Database\Eloquent\Relations\Pivot>
     */
    public function taxes(): \Illuminate\Database\Eloquent\Relations\BelongsToMany
    {
        return $this->belongsToMany(
            TaxClass::class, // Related model
            TaxProductRelation::class, // Pivot table
            'product_id', // FK on pivot table to Product
            'tax_class_id' // FK on pivot table to TaxClass
        );
    }

    public function productUpload()
    {
        return $this->hasMany(ProductUpload::class, 'product_id');
    }

    #[Override]
    public function delete()
    {
        $this->tax()->delete();
        $this->price()->delete();
        $this->PromoRelation()->delete();

        return parent::delete();
    }

    protected function image(): \Illuminate\Database\Eloquent\Casts\Attribute
    {
        return \Illuminate\Database\Eloquent\Casts\Attribute::make(get: function (?string $value) {
            if (! $value) {
                return asset('common/images/image.png');
            }

            return Attach::getUrlPath('common/images/'.$value);
        });
    }

    protected function parent(): \Illuminate\Database\Eloquent\Casts\Attribute
    {
        return \Illuminate\Database\Eloquent\Casts\Attribute::make(get: function ($value): array {
            return explode(',', (string) $value);
        }, set: function ($value): array {
            $value = implode(',', $value);

            return ['parent' => $value];
        });
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany<\App\Model\Payment\Plan, $this>
     */
    public function planRelation(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        $related = Plan::class;

        return $this->hasMany($related, 'product');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo<\App\Model\Product\ProductGroup, $this>
     */
    public function groupRelation(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(ProductGroup::class, 'group', 'id');
    }

    public function plan()
    {
        return $this->planRelation()->first();
    }

    // Define the relationship with ProductPluginGroup (as product)
    public function productPluginGroupsAsProduct()
    {
        return $this->hasMany(ProductPluginGroup::class, 'product_id');
    }

    // Define the relationship with ProductPluginGroup (as plugin)
    public function productPluginGroupsAsPlugin()
    {
        return $this->hasMany(ProductPluginGroup::class, 'plugin_id');
    }

    // Plugins bundled with this product (config options / order flow)
    /**
     * @return BelongsToMany<Product, $this, Pivot>
     */
    public function bundledPlugins(): BelongsToMany
    {
        return $this->belongsToMany(static::class, 'product_plugin_group', 'product_id', 'plugin_id');
    }

    // Plugins compatible with this product (store display / license lookup)
    /**
     * @return BelongsToMany<Product, $this, Pivot>
     */
    public function compatiblePlugins(): BelongsToMany
    {
        return $this->belongsToMany(static::class, 'plugin_compatible_with_products', 'product_id', 'plugin_id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany<\App\Model\Configure\ConfigOption, $this>
     */
    public function configOptions(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(ConfigOption::class, 'product_id');
    }

    public function productCompWith()
    {
        return $this->hasMany(PluginCompatibleWithProducts::class, 'product_id');
    }

    // Define the relationship with Product (as plugin)
    public function pluginCompWith()
    {
        return $this->hasMany(PluginCompatibleWithProducts::class, 'plugin_id');
    }

    public function versions()
    {
        return $this->hasMany(ProductUpload::class, 'product_id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasOne<\App\Model\Product\ProductUpload, $this>
     */
    public function latestVersion(): \Illuminate\Database\Eloquent\Relations\HasOne
    {
        return $this->hasOne(ProductUpload::class, 'product_id')->latest();
    }

    public function licenses()
    {
        return $this->hasMany(License::class, 'product_id');
    }

    public function installations()
    {
        return $this->hasMany(Installation::class, 'product_id');
    }

    public function licenseReports()
    {
        return $this->hasMany(LicenseReport::class, 'product_id');
    }

    public function licenseCallbacks()
    {
        return $this->hasMany(LicenseCallback::class, 'product_id');
    }
}
