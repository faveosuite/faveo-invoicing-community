<?php

namespace App\Http\Controllers\Order;

use App\Http\Controllers\Controller;

class Orders extends Controller
{
    public $orderid;

    public function __construct($order_id)
    {
        $this->orderid = $order_id;
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
            $subscription = $order->subscription;

            return $subscription;
        }
    }

    public function getProduct()
    {
        $order = $this->getOrder();
        if ($order) {
            $product = $order->product;

            return $product;
        }
    }

    public function getPlan()
    {
        $subscription = $this->getSubscription();
        if ($subscription) {
            $plan = $subscription->plan;

            return $plan;
        }
    }

    public function subscriptionPeriod()
    {
        $days = '';
        $plan = $this->getPlan();
        if ($plan) {
            $days = $plan->days;
        }

        return $days;
    }

    public function version()
    {
        $subscription = $this->getSubscription();
        if ($subscription) {
            $version = $subscription->vesion;
        }

        return $version;
    }

    public function isExpired()
    {
        $expired = false;
        $subscription = $this->getSubscription();
        if ($subscription) {
            $end = $subscription->ends_at;
            $today = \Carbon\Carbon::now();
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
            $name = $product->name;
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
