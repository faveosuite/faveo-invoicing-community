<?php

namespace App\Http\Controllers\Payment;

use App\Http\Controllers\Controller;
use App\Model\Payment\Promotion;

class BasePromotionController extends Controller
{
    public function getCode()
    {
        try {
            $code = str_random(6);
            echo strtoupper($code);
        } catch (\Exception $ex) {
            return redirect()->back()->with('fails', $ex->getMessage());
        }
    }

    public function findCost($type, $value, $price, $productid)
    {
        try {
            $price = intval($price);
            switch ($type) {
                case 1://Percentage
                    $percentage = $price * (intval($value) / 100);

                    return  $price - $percentage;
                case 2://Fixed amount
                    return $price - $value;
            }
        } catch (\Exception $ex) {
            throw new \Exception($ex->getMessage());
        }
    }

    public function getPromotionDetails($code)
    {
        if (empty($code)) {
            throw new \Exception(__('message.no_coupon_code_applied'));
        }
        $promo = Promotion::where('code', $code)->first();
        //check promotion code is valid
        if (! $promo) {
            throw new \Exception(__('message.invalid_coupon_code'));
        }
        $relation = $promo->relation()->get();
        //check the relation between code and product
        if (count($relation) == 0) {
            throw new \Exception(\Lang::get('message.no-product-related-to-this-code'));
        }
        //check the usess
        $cont = new \App\Http\Controllers\Payment\PromotionController();
        $uses = $cont->checkNumberOfUses($code);

        if ($uses != 'success') {
            throw new \Exception(\Lang::get('message.usage-of-code-completed'));
        }
        //check for the expiry date
        $expiry = $this->checkExpiry($code);
        if ($expiry != 'success') {
            throw new \Exception(\Lang::get('message.usage-of-code-expired'));
        }

        return $promo;
    }

    public function findCostAfterDiscount($promoid, $productid, $userid)
    {
        try {
            $promotion = Promotion::findOrFail($promoid);
            $cart_control = new \App\Http\Controllers\Front\CartController();
            if (checkPlanSession()) {
                $planid = \Session::get('plan');
            }
            $price = $cart_control->planCost($productid, $userid, $planid = '');
            \Session::put('oldPrice', $price);
            $updated_price = $this->findCost($promotion->type, $promotion->value, $price, $productid);

            return $updated_price;
        } catch (\Exception $ex) {
            dd($ex);
            throw new \Exception($ex->getMessage());
        }
    }
}
