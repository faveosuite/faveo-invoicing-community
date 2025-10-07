<?php

namespace App\Http\Controllers\Front;

use App\Facades\Cart;
use App\Http\Controllers\Controller;
//use Cart;
use App\Model\Product\Product;
use Illuminate\Http\Request;

class BaseCartController extends Controller
{
    public $cart;

    public function __construct()
    {
        $this->cart = new Cart();
    }

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

                $this->cart->update($planid, [
                    'price' => $cartValues['price'],
                    'attributes' => ['agents' => $cartValues['agtqty'], 'currency' => $cartValues['currency'], 'symbol' => $cartValues['symbol']],
                ]);
            }

            return successResponse(__('message.cart_updated_successfully'));
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

                $this->cart->update($planid, [
                    'price' => $cartValues['price'],
                    'attributes' => ['agents' => $cartValues['agtqty'], 'currency' => $cartValues['currency'], 'symbol' => $cartValues['symbol'], 'domain' => $cartValues['domain']],
                ]);
            }

            return successResponse(__('message.cart_updated_successfully'));
        } catch (\Exception $ex) {
            return errorResponse($ex->getMessage());
        }
    }

    private function getCartValues($productId, $canReduceAgent = false)
    {
        $cart = $this->cart->get($productId);
        if ($cart) {
            $agtqty = $cart['attributes']['agents'];
            $price = $cart['price'];
            $currency = $cart['attributes']['currency'];
            $symbol = $cart['attributes']['currency'];
        } else {
            throw new \Exception(__('message.product_not_in_cart'));
        }

        if ($canReduceAgent && $agtqty > 1) {
            $price = $cart['price'] / $agtqty;
            $agtqty = $agtqty - 1;
            $price = $cart['price'] - $price;
        } else {
            $price = $cart['price'] / $agtqty;

            $agtqty = $agtqty + 1;

            $price = $price * $agtqty;
        }

        return ['agtqty' => $agtqty, 'price' => $price, 'currency' => $currency, 'symbol' => $symbol, 'domain' => $cart['attributes']['domain'] ?? null];
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
                $cart = $this->cart->get($planid);
                $qty = $cart['quantity'] - 1;
                if ($qty >= 1) {
                    $price = $this->cost($id, $planid);
                    $this->cart->update($planid, [
                        'quantity' => $qty,
                        'price' => $price,
                    ]);
                }
            } else {
                throw new \Exception(__('message.cannot_modify_quantity'));
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
                $cart = $this->cart->get($planid);
                $qty = $cart['quantity'] + 1;
                $price = $this->cost($id, $planid);
                $this->cart->update($planid, [
                    'quantity' => $qty,
                    'price' => $price,
                ]);
            } else {
                throw new \Exception(__('message.cannot_modify_quantity'));
            }
        } catch (\Exception $ex) {
            throw new \Exception($ex->getMessage());
        }
    }
}
