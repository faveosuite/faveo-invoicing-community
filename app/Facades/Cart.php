<?php

namespace App\Facades;

use App\Traits\TaxCalculation;
use Illuminate\Support\Collection;

class Cart
{
    public $session;

    public $sessionName;

    use TaxCalculation;

    public function __construct()
    {
        $this->session = \Session();
        $this->sessionName = 'cart';
    }

    public function add($id, $name = null, $price = null, $quantity = null, $attributes = [], $conditions = [], $associatedModel = null)
    {
        $cart = $this->getContent();

        $data = [
            'id' => $id,
            'name' => $name,
            'price' => $price,
            'quantity' => $quantity,
            'attributes' => $attributes,
            'conditions' => $conditions,
            'associatedModel' => $associatedModel,
        ];

        if ($cart->has($id)) {
            $this->update($id, $data, 1);
        } else {
            $this->addRow($id, $data);
        }
    }

    public function remove($id)
    {
        $cart = $this->getContent();

        $cart->forget($id);
        $this->save($cart);
    }

    public function clear()
    {
        $this->session->forget($this->sessionName);
    }

    public function update($id, $data, $quantity = null)
    {
        $cart = $this->getContent();
        $item = $cart->pull($id);
        $quant = $item['quantity'];
        foreach ($data as $key => $value) {
            $item[$key] = $value;
        }
        if ($quantity != null) {
            $item['quantity'] = $quant + $quantity;
        }
        $cart->put($id, $item);
        $this->save($cart);
    }

    public function getContent()
    {
        return new Collection($this->session->get($this->sessionName));
    }

    public function getTotal()
    {
        $cart = $this->getContent();
        $actual_price = 0;
        $subTotal = 0;

        foreach ($cart as $key => $value) {
            $subTotal = $this->getSubTotal($key) * $value['quantity'];
            if ($value['conditions'] != null) {
                $actual_price += $this->calculateTotal($value['conditions']['value'], $subTotal);
            } else {
                $actual_price += $subTotal;
            }
        }

        return $actual_price;
    }

    public function getSubTotal($id)
    {
        $cart = $this->getContent();
        $allCart = $cart->pull($id);
        $subTotal = $allCart['price'];

        return $subTotal;
    }

    public function addRow($id, $data)
    {
        $cart = $this->getContent();
        $cart->put($id, $data);
        $this->save($cart);
    }

    public function save($cart)
    {
        $this->session->put($this->sessionName, $cart);
    }

    public function get($itemId)
    {
        return $this->getContent()->get($itemId);
    }

    public function getCartValues($productId, $canReduceAgent = false)
    {
        $cart = $this->get($productId);
        if ($cart) {
            $agtqty = $cart['attributes']['agents'];
            $price = $cart['price'];
            $currency = $cart['attributes']['currency'];
            $symbol = $cart['attributes']['currency'];
        } else {
            throw new \Exception(__('message.product_not_in_cart'));
        }

        if ($canReduceAgent) {
            $price = $cart['price'] / $agtqty;
            $agtqty = $agtqty - 1;
            $price = $cart['price'] - $price;
        } else {
            $price = $cart['price'] / $agtqty;

            $agtqty = $agtqty + 1;

            $price = $price * $agtqty;
        }

        return ['agtqty' => $agtqty, 'price' => $price, 'currency' => $currency, 'symbol' => $symbol, 'domain' => $cart['attributes']['domain']];
    }

    public function isEmpty()
    {
        return $this->getContent()->isEmpty();
    }

    public function getPriceSum($id)
    {
        $content = $this->get($id);

        return  (int) $content['price'] * $content['quantity'];
    }

    public function getTotalQuantity()
    {
        $cart = $this->getContent();
        $total = 0;
        foreach ($cart as $collection) {
            $total += $collection['quantity'];
        }

        return $total;
    }

    public function getConditions($id)
    {
        $cart = $this->get($id);
        $content = $cart ? $$cart['conditions'] : null;

        return $content;
    }

    public function getConditionsByType($type, $id)
    {
        $cart = $this->get($id);

        return $cart['conditions']['type'] == $type;
    }
}
