<?php

namespace App\Model\Product;

use App\BaseModel;
use App\Facades\Attach;
use App\Model\Configure\ConfigOption;
use App\Model\Configure\PluginCompatibleWithProducts;
use App\Model\Configure\ProductPluginGroup;
use App\Model\Order\InvoiceItem;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class Product extends BaseModel
{
    use HasFactory;
    use LogsActivity;

    protected $table = 'products';

    protected $fillable = ['id', 'name', 'description', 'type', 'group', 'file', 'image', 'require_domain', 'category',
        'can_modify_agent',  'can_modify_quantity', 'show_agent', 'tax_apply', 'show_product_quantity', 'hidden',  'auto_terminate',
        'setup_order_placed', 'setup_first_payment', 'setup_accept_manually',
        'no_auto_setup', 'shoping_cart_link', 'process_url', 'github_owner',
        'github_repository',
        'deny_after_subscription', 'version', 'parent', 'subscription', 'product_sku', 'perpetual_license', 'product_description', 'invoice_hidden', ];

    protected static $logName = 'Product';

    protected static $logAttributes = ['name', 'description', 'type', 'file', 'category',
        'github_owner', 'github_repository', 'version',  'subscription', 'hidden', 'product_sku', ];

    protected static $logOnlyDirty = true;

    public function getDescriptionForEvent(string $eventName): string
    {
        if ($eventName == 'created') {
            return 'Product '.$this->name.' was created';
        }

        if ($eventName == 'updated') {
            return 'Product  <strong> '.$this->name.'</strong> was updated';
        }

        if ($eventName == 'deleted') {
            return 'Product <strong> '.$this->name.' </strong> was deleted';
        }

        return '';
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

    public function invoice_item()
    {
        return $this->hasMany(InvoiceItem::class, 'product_id');
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

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults();
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
