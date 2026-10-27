<?php

namespace App\Http\Controllers\Order;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Date;

class Orders extends Controller
{
    public function __construct(public mixed $orderid)
    {
    }

    public function getOrder(): mixed
    {
        /** @scrutinizer ignore-call */
        $order = self::find($this->orderid); // @phpstan-ignore staticMethod.notFound

        return $order;
    }

    public function getSubscription(): mixed
    {
        $order = $this->getOrder();
        if ($order) {
            return $order->subscription;
        }

        return null;
    }

    public function getProduct(): mixed
    {
        $order = $this->getOrder();
        if ($order) {
            return $order->product;
        }

        return null;
    }

    public function getPlan(): mixed
    {
        $subscription = $this->getSubscription();
        if ($subscription) {
            return $subscription->plan;
        }

        return null;
    }

    public function subscriptionPeriod(): mixed
    {
        $days = '';
        $plan = $this->getPlan();
        if ($plan) {
            return $plan->days;
        }

        return $days;
    }

    public function version(): mixed
    {
        $subscription = $this->getSubscription();
        if ($subscription) {
            return $subscription->vesion;
        }

        return null;
    }

    public function isExpired(): mixed
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

    public function productName(): mixed
    {
        $name = '';
        $product = $this->getProduct();
        if ($product) {
            return $product->name;
        }

        return $name;
    }

    public function isDownloadable(): mixed
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
