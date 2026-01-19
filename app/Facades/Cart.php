<?php

namespace App\Facades;

use App\Traits\TaxCalculation;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Facade;

class Cart extends Facade
{
    public $session;

    public $sessionName;

    use TaxCalculation;

    public function __construct()
    {
        $this->session = \Session();
        $this->sessionName = 'cart';
    }

    protected static function getFacadeAccessor(): string
    {
        return 'user-cart';
    }

    public function add($id, $name = null, $price = null, $quantity = null, $attributes = [], $conditions = [], $associatedModel = null,$group=null,$groupedProductId=null)
    {
        $cart = $this->getContent();
        $groupedProductId=empty($groupedProductId)?rand():$groupedProductId;
        if (is_array($id)) {
            if ($this->isMultiArray($id)) {
                foreach ($id as $item) {
                    $this->add($item['id'], $item['name'], $item['price'] ?? null, $item['quantity'] ?? null, $item['attributes'] ?? null, $item['conditions'] ?? null, $item['associatedModel'] ?? null,$item['group']??null,$item['groupProductId']??null);
                }
            } else {
                $this->add($id['id'], $id['name'], $id['price'] ?? null, $id['quantity'] ?? null, $id['attributes'] ?? null, $id['conditions'] ?? null, $id['associatedModel'] ?? null,$item['group']??null,$item['groupProductId']??null);
            }

            return $this;
        }

        $data = [
            'id' => $id,
            'name' => $name,
            'price' => $price,
            'quantity' => $quantity,
            'attributes' => $attributes,
            'conditions' => $conditions,
            'associatedModel' => $associatedModel,
            'group'=>$group,
            'groupedProductId'=>$groupedProductId
        ];

        if ($cart->has($data['id'])) {
            $this->update($data['id'], $data, 1);
        } else {
            $this->addRow($data['id'], $data);
        }
    }

    public function remove($id)
    {
        $cart = $this->getContent();

        $cart->forget($id);
        $this->save($cart);
    }

    public function isMultiArray($array, $recursive = false)
    {
        if ($recursive) {
            return (count($array) == count($array, COUNT_RECURSIVE)) ? false : true;
        } else {
            foreach ($array as $k => $v) {
                if (is_array($v)) {
                    return true;
                } else {
                    return false;
                }
            }
        }
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
        $content = $cart ? $cart['conditions'] : null;

        return $content;
    }

    public function getConditionsByType($type, $id)
    {
        $cart = $this->get($id);

        return $cart['conditions']['type'] == $type;
    }

    public function removeCartCondition($condition)
    {
        $cart = $this->getContent();
        foreach ($cart as $items) {
            if ($items['conditions']['type'] == $condition) {
                $items['conditions'] = null;
                $this->update($items['id'], ['conditions' => null]);
            }
        }
    }

    public function condition($condition)
    {
        $cart = $this->getContent();
        foreach ($cart as $items) {
            $this->update($items['id'], ['conditions' => $condition]);
        }
    }
}
