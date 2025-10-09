<?php

namespace App\Model\Product;

use App\BaseModel;
use App\Model\Payment\Plan;
use App\Traits\SystemActivityLogsTrait;

class CloudProducts extends BaseModel
{
    use SystemActivityLogsTrait;

    protected $table = 'cloud_products';

    protected $guarded = [];

    protected $logName = 'cloud';

    protected $logNameColumn = 'Settings';


    protected $logAttributes = [
        'cloud_product'	,'cloud_free_plan','cloud_product_key', 'trial_status'
    ];

    protected $logUrl = ['view/tenant'];

    protected function getMappings(): array
    {
        $product = Product::find($this->cloud_product)?->name ?? 'Unknown Product';
        $plan = Plan::find($this->cloud_free_plan)?->name ?? 'Unknown Plan';

        return [
            'cloud_product' => ['Product Name', fn ($value) => \App\Model\Product\Product::find($value)?->name],
            'cloud_free_plan' => ['Free Plan', fn ($value) => \App\Model\Payment\Plan::find($value)?->name],
            'cloud_product_key' => ['Product Key', fn ($value) => $value],
            'trial_status' => [
                "Trial Status for {$product} (Plan : {$plan})",
                fn ($value) => $value ? __('message.active') : __('message.inactive')
            ],
        ];
    }

    public function product()
    {
        return $this->belongsTo(Product::class, 'cloud_product');
    }

    public function plan()
    {
        return $this->belongsTo(Plan::class, 'cloud_free_plan');
    }
}
