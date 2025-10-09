<?php

namespace App\Model\Payment;

use App\Traits\SystemActivityLogsTrait;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PlanPrice extends Model
{
    use HasFactory,SystemActivityLogsTrait;

    protected $table = 'plan_prices';

    protected $fillable = ['plan_id', 'currency', 'add_price', 'renew_price', 'price_description', 'product_quantity', 'no_of_agents', 'country_id', 'offer_price'];

    protected $logName = 'plan';

    protected $logNameColumn = 'price';


    protected $logAttributes = [
        'plan_id', 'currency', 'add_price', 'renew_price', 'price_description', 'product_quantity', 'no_of_agents', 'country_id', 'offer_price'
    ];

    protected $logUrl = ['plans'];

    protected function getMappings(): array
    {
        return [
            'plan_id' => ['Plan Name', fn ($value) => \App\Model\Payment\Plan::find($value)?->name],
            'currency' => ['Currency', fn ($value) => $value],
            'add_price' => ['Add Price', fn ($value) => $value],
            'renew_price' => ['Renew Price', fn ($value) => $value],
            'price_description' => ['Price Description', fn ($value) => $value],
            'product_quantity' => ['Product Quantity', fn ($value) => $value],
            'no_of_agents' => ['Number of Agents', fn ($value) => $value],
            'country_id' => ['Country', fn ($value) => \App\Model\Common\Country::find($value)?->name],
            'offer_price' => ['Offer Price', fn ($value) => $value],
        ];
    }
}
