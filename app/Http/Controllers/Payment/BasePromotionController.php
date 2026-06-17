<?php

namespace App\Http\Controllers\Payment;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Front\CartController;
use App\Model\Payment\Promotion;
use Exception;
use Illuminate\Support\Str;
use Lang;
use Session;

class BasePromotionController extends Controller
{
    public function getCode()
    {
        try {
            return successResponse('', strtoupper(Str::random(6)));
        } catch (Exception $exception) {
            return errorResponse($exception->getMessage());
        }
    }

    public function findCost($type, $value, $price, $productid): float|int|null
    {
        try {
            $price = intval($price);
            switch ($type) {
                case 1://Percentage
                    $percentage = $price * (intval($value) / 100);

                    return  $price - $percentage;
                case 2:
                    //Fixed amount
                    if ($value > $price) {
                        throw new Exception(__('message.invalid_coupon_code'));
                    }

                    return $price - $value;
            }
        } catch (Exception $exception) {
            throw new Exception($exception->getMessage(), $exception->getCode(), $exception);
        }

        return null;
    }

    public function getPromotionDetails($code)
    {
        if (empty($code)) {
            throw new Exception(__('message.no_coupon_code_applied'));
        }

        $promo = Promotion::where('code', $code)->first();
        //check promotion code is valid
        if (! $promo) {
            throw new Exception(__('message.invalid_coupon_code'));
        }

        $relation = $promo->relation()->get();
        //check the relation between code and product
        if (count($relation) === 0) {
            throw new Exception(Lang::get('message.no-product-related-to-this-code'));
        }

        //check the usess
        $cont = new PromotionController();
        $uses = $cont->checkNumberOfUses($code);

        if ($uses !== 'success') {
            throw new Exception(Lang::get('message.usage-of-code-completed'));
        }

        //check for the expiry date
        $expiry = $this->checkExpiry($code);
        if ($expiry != 'success') {
            throw new Exception(Lang::get('message.usage-of-code-expired'));
        }

        return $promo;
    }

    public function findCostAfterDiscount($promoid, $productid, $userid): float|int|null
    {
        try {
            $planid = '';
            $promotion = Promotion::findOrFail($promoid);
            $cart_control = new CartController();
            if (checkPlanSession()) {
                $planid = Session::get('plan');
            }

            $price = $cart_control->planCost($productid, $userid, $planid);
            Session::put('oldPrice', $price);

            return $this->findCost($promotion->type, $promotion->value, $price, $productid);
        } catch (Exception $exception) {
            throw new Exception($exception->getMessage(), $exception->getCode(), $exception);
        }
    }
}
