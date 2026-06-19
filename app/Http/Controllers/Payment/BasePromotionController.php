<?php

namespace App\Http\Controllers\Payment;

use App\Http\Controllers\Controller;
use App\Model\Payment\Plan;
use App\Model\Payment\Promotion;
use App\Model\Product\Product;
use Exception;
use Illuminate\Support\Str;
use Session;

class BasePromotionController extends Controller
{
    public function getCode(): \Illuminate\Http\JsonResponse
    {
        try {
            return successResponse('', strtoupper(Str::random(6)));
        } catch (Exception $exception) {
            return errorResponse($exception->getMessage());
        }
    }

    public function findCost(mixed $type, mixed $value, mixed $price, mixed $productid): float|int|null
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

    public function getPromotionDetails(mixed $code): mixed
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
            throw new Exception(__('message.no-product-related-to-this-code'));
        }

        //check the usess
        $cont = new PromotionController();
        $uses = $cont->checkNumberOfUses($code);

        if ($uses !== 'success') {
            throw new Exception(__('message.usage-of-code-completed'));
        }

        //check for the expiry date
        $expiry = $this->checkExpiry($code); // @phpstan-ignore method.notFound
        if ($expiry != 'success') {
            throw new Exception(__('message.usage-of-code-expired'));
        }

        return $promo;
    }

    public function findCostAfterDiscount(mixed $promoid, mixed $productid, mixed $userid): float|int|null
    {
        try {
            $planid = '';
            /** @var \App\Model\Payment\Promotion $promotion */
            $promotion = Promotion::findOrFail($promoid);
            if (checkPlanSession()) {
                $planid = Session::get('plan');
            }

            /** @var \App\Model\Product\Product $product */
            $product = Product::findOrFail($productid);
            $planId = $planid ?: Plan::where('product', $product->id)->where('status', 1)->value('id');
            $userPlan = userCurrencyAndPrice($userid, $product->planRelation()->findOrFail($planId));
            if (empty($userPlan['plan'])) {
                throw new Exception(__('message.no_available_plans_currency'));
            }

            $planPrice = $userPlan['plan'];
            $cost = (float) $planPrice->add_price;
            $offer = $planPrice->offer_price ?? 0;
            $price = $offer > 0 ? $cost * (1 - $offer / 100) : $cost;
            Session::put('oldPrice', $price);

            return $this->findCost($promotion->type, $promotion->value, $price, $productid);
        } catch (Exception $exception) {
            throw new Exception($exception->getMessage(), $exception->getCode(), $exception);
        }
    }
}
