<?php

namespace App\Http\Controllers\Order;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Date;

class Orders extends Controller
{
    public function __construct(public $orderid)
    {
    }

    public function getOrder()
    {
        /** @scrutinizer ignore-call */
        $order = self::find($this->orderid);

        return $order;
    }

    public function getSubscription()
    {
        $order = $this->getOrder();
        if ($order) {
            return $order->subscription;
        }
    }

    public function getProduct()
    {
        $order = $this->getOrder();
        if ($order) {
            return $order->product;
        }
    }

    public function getPlan()
    {
        $subscription = $this->getSubscription();
        if ($subscription) {
            return $subscription->plan;
        }
    }

    public function subscriptionPeriod()
    {
        $days = '';
        $plan = $this->getPlan();
        if ($plan) {
            return $plan->days;
        }

        return $days;
    }

    public function version()
    {
        $subscription = $this->getSubscription();
        if ($subscription) {
            return $subscription->vesion;
        }

        return null;
    }

    public function isExpired()
    {
        $expired = false;
        $subscription = $this->getSubscription();
        if ($subscription) {
            $end = $subscription->ends_at;
            $today = Date::now();
            if ($today->gt($end)) {
                $expired = true;
            }
        }

        return $expired;
    }

    public function productName()
    {
        $name = '';
        $product = $this->getProduct();
        if ($product) {
            return $product->name;
        }

        return $name;
    }

    public function isDownloadable()
    {
        $check = false;
        $product = $this->getProduct();
        if ($product) {
            $type = $product->type;
            if ($type) {
                $type_name = $type->name;
                if ($type_name == 'download') {
                    $check = true;
                }
            }
        }

        return $check;
    }
}
