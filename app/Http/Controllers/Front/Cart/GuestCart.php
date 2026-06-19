<?php

namespace App\Http\Controllers\Front\Cart;

use App\Model\Cart\Cart;
use App\Model\Cart\CartItem;
use App\Model\Payment\Plan;
use App\Model\Product\Product;
use Illuminate\Support\Facades\Session;

/**
 * A shopping cart for unauthenticated visitors, stored entirely in the session.
 *
 * Guests can only build a cart — coupons and checkout require logging in — so
 * this only manages line items. Nothing is written to the database, so there
 * are no orphaned rows to garbage-collect; an expired session takes the cart
 * with it. On login the items are folded into the user's DB cart
 * (see CartService::mergeGuestCart).
 *
 * Reads return a transient (unsaved) Cart with its items + products loaded, so
 * the same CartResource/Cart methods serve guests and authenticated users.
 */
class GuestCart
{
    private const string KEY = 'guest_cart';

    /**
     * @param array<mixed> $data
     */
    public function add(array $data, string $currency): void
    {
        $cart = $this->read();

        if (empty($cart['currency'])) {
            $cart['currency'] = $currency;
        }

        // Same product+plan+cycle already present → bump quantity (mirrors the DB path).
        foreach ($cart['items'] as $id => $item) {
            if ((int) $item['product_id'] === (int) $data['product_id']
                && ($item['plan_id'] ?? null) == ($data['plan_id'] ?? null)
                && ($item['billing_cycle'] ?? 'monthly') === ($data['billing_cycle'] ?? 'monthly')) {
                $cart['items'][$id]['quantity'] += (int) ($data['quantity'] ?? 1);
                $this->write($cart);

                return;
            }
        }

        $id = $cart['next_id']++;
        $cart['items'][$id] = [
            'id' => $id,
            'product_id' => (int) $data['product_id'],
            'plan_id' => $data['plan_id'] ?? null,
            'quantity' => (int) ($data['quantity'] ?? 1),
            'agents' => (int) ($data['agents'] ?? 1),
            'domain' => $data['domain'] ?? null,
            'billing_cycle' => $data['billing_cycle'] ?? 'monthly',
        ];

        $this->write($cart);
    }

    /**
     * @param array<mixed> $data
     */
    public function update(int $id, array $data): void
    {
        $cart = $this->read();

        if (! isset($cart['items'][$id])) {
            return;
        }

        if (isset($data['quantity'])) {
            $cart['items'][$id]['quantity'] = (int) $data['quantity'];
        }

        if (isset($data['agents'])) {
            $cart['items'][$id]['agents'] = (int) $data['agents'];
        }

        if (array_key_exists('domain', $data)) {
            $cart['items'][$id]['domain'] = $data['domain'];
        }

        $this->write($cart);
    }

    public function remove(int $id): void
    {
        $cart = $this->read();
        unset($cart['items'][$id]);
        $this->write($cart);
    }

    public function clear(): void
    {
        Session::forget(self::KEY);
    }

    /** @return array<int, array<string, mixed>> raw item rows (for merge-on-login) */
    public function all(): array
    {
        return array_values($this->read()['items']);
    }

    public function has(int $id): bool
    {
        return isset($this->read()['items'][$id]);
    }

    public function isEmpty(): bool
    {
        return empty($this->read()['items']);
    }

    /**
     * Build a transient (unsaved) Cart with hydrated items + products + plans so the
     * resource/serialization layer is identical to the authenticated path.
     */
    public function toCart(): Cart
    {
        $data = $this->read();
        $currency = $data['currency'] ?? 'USD';
        $productIds = array_column($data['items'], 'product_id');
        $planIds = array_filter(array_column($data['items'], 'plan_id'));

        $products = $productIds !== [] ? Product::whereIn('id', $productIds)->get()->keyBy('id') : collect();
        $plans = $planIds !== [] ? Plan::with('planPrice')->whereIn('id', $planIds)->get()->keyBy('id') : collect();

        $cart = new Cart(['coupon_discount' => 0, 'currency' => $currency]);

        $items = collect((array) $data['items'])->map(function (array $row) use ($products, $plans, $cart): \App\Model\Cart\CartItem {
            $item = new CartItem($row);
            $item->id = $row['id'];
            $item->setRelation('product', $products->get($row['product_id']));
            $item->setRelation('plan', $plans->get($row['plan_id']));
            $item->setRelation('cart', $cart);

            return $item;
        })->values();

        $cart->setRelation('items', $items);

        return $cart;
    }

    /** @return array{next_id: int, currency: string|null, items: array} */
    /**
     * @return array<mixed>
     */
    private function read(): array
    {
        return Session::get(self::KEY, ['next_id' => 1, 'currency' => null, 'items' => []]);
    }

    /**
     * @param array<mixed> $cart
     */
    private function write(array $cart): void
    {
        Session::put(self::KEY, $cart);
    }
}
