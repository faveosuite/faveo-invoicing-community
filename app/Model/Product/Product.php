<?php

namespace App\Model\Product;

use App\BaseModel;
use App\Facades\Attach;
use App\Model\Configure\ConfigOption;
use App\Model\Configure\PluginCompatibleWithProducts;
use App\Model\Configure\ProductPluginGroup;
use App\Traits\SystemActivityLogsTrait;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Product extends BaseModel
{
    use HasFactory;
    use SystemActivityLogsTrait;

    protected $table = 'products';

    protected $fillable = ['id', 'name', 'description', 'type', 'group', 'file', 'image', 'require_domain', 'category',
        'can_modify_agent',  'can_modify_quantity', 'show_agent', 'tax_apply', 'show_product_quantity', 'hidden',  'auto_terminate',
        'setup_order_placed', 'setup_first_payment', 'setup_accept_manually',
        'no_auto_setup', 'shoping_cart_link', 'process_url', 'github_owner',
        'github_repository',
        'deny_after_subscription', 'version', 'parent', 'subscription', 'product_sku', 'perpetual_license', 'product_description', 'invoice_hidden'];

    protected $logName = 'product';

    protected $logNameColumn = 'Settings';

    protected $logAttributes = [
        'name', 'description', 'type', 'group', 'file', 'image', 'require_domain', 'category',
        'can_modify_agent',  'can_modify_quantity', 'show_agent', 'tax_apply', 'show_product_quantity', 'hidden',  'auto_terminate',
        'setup_order_placed', 'setup_first_payment', 'setup_accept_manually',
        'no_auto_setup', 'shoping_cart_link', 'process_url', 'github_owner',
        'github_repository',
        'deny_after_subscription', 'version', 'subscription', 'product_sku', 'perpetual_license', 'product_description', 'invoice_hidden',
    ];

    protected $logUrl = ['products', 'edit'];

    protected function getMappings(): array
    {
        return [
            'type' => ['License Type', fn ($value) => $value ? \App\Model\License\LicenseType::find($value)?->name : 'No Type'],
            'group' => ['Product Group', fn ($value) => $value ? \App\Model\Product\ProductGroup::find($value)?->name : 'No Group'],
            'file' => ['Product File', fn ($value) => $value],
            'image' => ['Product Image', fn ($value) => $value],
            'require_domain' => ['Require Domain', fn ($value) => $value === 1 ? __('message.yes') : __('message.no')],
            'category' => ['Category', fn ($value) => $value],
            'can_modify_agent' => ['Can Modify Agent', fn ($value) => $value === 1 ? __('message.yes') : __('message.no')],
            'can_modify_quantity' => ['Can Modify Quantity', fn ($value) => $value === 1 ? __('message.yes') : __('message.no')],
            'show_agent' => ['Show Agent', fn ($value) => $value === 1 ? __('message.yes') : __('message.no')],
            'tax_apply' => ['Tax Apply', fn ($value) => $value === 1 ? __('message.yes') : __('message.no')],
            'show_product_quantity' => ['Show Product Quantity', fn ($value) => $value === 1 ? __('message.yes') : __('message.no')],
            'hidden' => ['Hidden', fn ($value) => $value === 1 ? __('message.yes') : __('message.no')],
            'auto_terminate' => ['Auto Terminate', fn ($value) => $value === 1 ? __('message.yes') : __('message.no')],
            'setup_order_placed' => ['Setup on Order Placed', fn ($value) => $value === 1 ? __('message.yes') : __('message.no')],
            'setup_first_payment' => ['Setup on First Payment', fn ($value) => $value === 1 ? __('message.yes') : __('message.no')],
            'setup_accept_manually' => ['Setup Accept Manually', fn ($value) => $value === 1 ? __('message.yes') : __('message.no')],
            'no_auto_setup' => ['No Auto Setup', fn ($value) => $value === 1 ? __('message.yes') : __('message.no')],
            'shoping_cart_link' => ['Shopping Cart Link', fn ($value) => $value],
            'process_url' => ['Process URL', fn ($value) => $value],
            'github_owner' => ['GitHub Owner', fn ($value) => $value],
            'github_repository' => ['GitHub Repository', fn ($value) => $value],
            'deny_after_subscription' => ['Deny After Subscription', fn ($value) => $value === 1 ? __('message.yes') : __('message.no')],
            'version' => ['Version', fn ($value) => $value],
            'subscription' => ['Subscription', fn ($value) => $value],
            'product_sku' => ['Product SKU', fn ($value) => $value],
            'perpetual_license' => ['Perpetual License', fn ($value) => $value === 1 ? __('message.yes') : __('message.no')],
            'product_description' => ['Product Description', fn ($value) => $value],
            'invoice_hidden' => ['Hide on Invoice', fn ($value) => $value === 1 ? __('message.yes') : __('message.no')],
        ];
    }

    // protected static $recordEvents = ['deleted'];

    public function order()
    {
        return $this->hasMany(\App\Model\Order\Order::class, 'product');
    }

    public function subscription()
    {
        return $this->hasMany(\App\Model\Product\Subscription::class);
    }

    public function licenseType()
    {
        return $this->belongsTo(\App\Model\License\LicenseType::class, 'type');
    }

    public function price()
    {
        return $this->hasMany(\App\Model\Product\Price::class);
    }

    public function PromoRelation()
    {
        return $this->hasMany(\App\Model\Payment\PromoProductRelation::class, 'product_id');
    }

    public function tax()
    {
        return $this->hasMany(\App\Model\Payment\TaxProductRelation::class, 'product_id');
    }

    public function productUpload()
    {
        return $this->hasMany(\App\Model\Product\ProductUpload::class, 'product_id');
    }

    public function delete()
    {
        $this->tax()->delete();
        $this->price()->delete();
        $this->PromoRelation()->delete();

        return parent::delete();
    }

    public function getImageAttribute($value)
    {
        if (! $value) {
            $image = asset('storage/common/images/No-image-found.png');
        } else {
            $image = Attach::getUrlPath('common/images/'.$value);
        }

        return $image;
    }

    public function setParentAttribute($value)
    {
        $value = implode(',', $value);
        $this->attributes['parent'] = $value;
    }

    public function getParentAttribute($value)
    {
        $value = explode(',', $value);

        return $value;
    }

    public function planRelation()
    {
        $related = \App\Model\Payment\Plan::class;

        return $this->hasMany($related, 'product');
    }

    public function group()
    {
        return $this->belongsTo(\App\Model\Product\ProductGroup::class);
    }

    public function plan()
    {
        $plan = $this->planRelation()->first();

        return $plan;
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

    public function configOptions()
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
}
