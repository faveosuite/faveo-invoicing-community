<?php

namespace App\Http\Controllers\Front;

use App\Http\Controllers\Controller;
use App\Model\Product\Product;
use Cart;
use Illuminate\Http\Request;

class BaseCartController extends Controller
{
    /**
     * Reduce No. of Agents When Minus button Is Clicked.
     *
     * @param  Request  $request  Get productid , Product quantity ,Price,Currency,Symbol as Request
     * @return success
     */
    public function reduceAgentQty(Request $request)
    {
        try {
            $id = $request->input('productid');
            $planid = $request->input('planid');
            $hasPermissionToModifyAgent = Product::find($id)->can_modify_agent;
            if ($hasPermissionToModifyAgent) {
                $cartValues = $this->getCartValues($planid, true);

                Cart::update($planid, [
                    'price' => $cartValues['price'],
                    'attributes' => ['agents' => $cartValues['agtqty'], 'currency' => $cartValues['currency'], 'symbol' => $cartValues['symbol']],
                ]);
            }

            return successResponse('Cart updated successfully');
        } catch (\Exception $ex) {
            return errorResponse($ex->getMessage());
        }
    }

    /**
     * Update The Quantity And Price in cart when No of Agents Increasd.
     *
     * @param  Request  $request  Get productid , Product quantity ,Price,Currency,Symbol as Request
     * @return success
     */
    public function updateAgentQty(Request $request)
    {
        try {
            $id = $request->input('productid');
            $planid = $request->input('planid');
            $hasPermissionToModifyAgent = Product::find($id)->can_modify_agent;
            if ($hasPermissionToModifyAgent) {
                $cartValues = $this->getCartValues($planid);
                Cart::update($planid, [
                    'price' => $cartValues['price'],
                    'attributes' => ['agents' => $cartValues['agtqty'], 'currency' => $cartValues['currency'], 'symbol' => $cartValues['symbol'], 'domain' => $cartValues['domain']],
                ]);
            }

            return successResponse('Cart updated successfully');
        } catch (\Exception $ex) {
            return errorResponse($ex->getMessage());
        }
    }

    private function getCartValues($productId, $canReduceAgent = false)
    {
        $cart = \Cart::get($productId);
        if ($cart) {
            $agtqty = $cart->attributes->agents;
            $price = $cart->price;

            $currency = $cart->attributes->currency;
            $symbol = $cart->attributes->currency;
        } else {
            throw new \Exception('Product not present in cart.');
        }

        if ($canReduceAgent) {
            $price = $cart->price / $agtqty;
            $agtqty = $agtqty - 1;
            $price = $cart->price - $price;
        } else {
            $price = $cart->price / $agtqty;

            $agtqty = $agtqty + 1;

            $price = $price * $agtqty;
        }

        return ['agtqty' => $agtqty, 'price' => $price, 'currency' => $currency, 'symbol' => $symbol, 'domain' => $cart->attributes->domain];
    }

    /**
     * Reduce The Quantity And Price in cart whenMinus Button is Clicked.
     *
     * @param  Request  $request  Get productid , Product quantity ,Price as Request
     * @return success
     */
    public function reduceProductQty(Request $request)
    {
        try {
            $id = $request->input('productid');
            $planid = $request->input('planid');
            $hasPermissionToModifyQuantity = Product::find($id)->can_modify_quantity;
            if ($hasPermissionToModifyQuantity) {
                $cart = \Cart::get($planid);
                $qty = $cart->quantity - 1;
                $price = $this->cost($id);
                Cart::update($planid, [
                    'quantity' => -1,
                    'price' => $price,
                ]);
            } else {
                throw new \Exception('Cannot Modify Quantity');
            }
        } catch (\Exception $ex) {
            throw new \Exception($ex->getMessage());
        }
    }

    /**
     * Update The Quantity And Price in cart when No of Products Increasd.
     *
     * @param  Request  $request  Get productid , Product quantity ,Price as Request
     * @return success
     */
    public function updateProductQty(Request $request)
    {
        try {
            $id = $request->input('productid');
            $planid = $request->input('planid');
            $hasPermissionToModifyQuantity = Product::find($id)->can_modify_quantity;
            if ($hasPermissionToModifyQuantity) {
                $cart = \Cart::get($planid);
                $qty = $cart->quantity + 1;
                $price = $this->cost($id);
                Cart::update($planid, [
                    'quantity' => [
                        'relative' => false,
                        'value' => $qty,
                    ],
                    'price' => $price,
                ]);
            } else {
                throw new \Exception('Cannot Modify Quantity');
            }
        } catch (\Exception $ex) {
            throw new \Exception($ex->getMessage());
        }
    }
}
