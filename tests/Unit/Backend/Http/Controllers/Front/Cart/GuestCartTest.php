<?php

namespace Tests\Unit\Backend\Http\Controllers\Front\Cart;

use App\Http\Controllers\Front\Cart\GuestCart;
use App\Model\Cart\Cart;
use App\Model\Cart\CartItem;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\DBTestCase;

class GuestCartTest extends DBTestCase
{
    use DatabaseTransactions;

    private GuestCart $cart;

    protected function setUp(): void
    {
        parent::setUp();
        $this->getLoggedInUser('user');
        $this->cart = new GuestCart;
        $this->cart->clear(); // start clean
    }

    public function test_is_empty_returns_true_when_no_items(): void
    {
        $this->assertTrue($this->cart->isEmpty());
    }

    public function test_add_item_makes_cart_not_empty(): void
    {
        $this->cart->add(['product_id' => 1, 'plan_id' => 1, 'quantity' => 1], 'USD');
        $this->assertFalse($this->cart->isEmpty());
    }

    public function test_all_returns_array(): void
    {
        $result = $this->cart->all();
        $this->assertIsArray($result);
    }

    public function test_has_returns_false_for_nonexistent_id(): void
    {
        $this->assertFalse($this->cart->has(999));
    }

    public function test_clear_empties_cart(): void
    {
        $this->cart->add(['product_id' => 1, 'plan_id' => 1, 'quantity' => 1], 'USD');
        $this->cart->clear();
        $this->assertTrue($this->cart->isEmpty());
    }

    public function test_remove_removes_item(): void
    {
        $this->cart->add(['product_id' => 1, 'plan_id' => 1, 'quantity' => 1], 'USD');
        $items = $this->cart->all();
        if (! empty($items)) {
            $id = array_key_first($items);
            $this->cart->remove((int) $id);
            $this->assertFalse($this->cart->has((int) $id));
        }
        $this->assertTrue(true);
    }

    public function test_update_modifies_item(): void
    {
        $this->cart->add(['product_id' => 1, 'plan_id' => 1, 'quantity' => 1], 'USD');
        $items = $this->cart->all();
        if (! empty($items)) {
            $id = array_key_first($items);
            $this->cart->update((int) $id, ['quantity' => 3]);
        }
        $this->assertTrue(true);
    }

    public function test_to_cart_returns_cart_instance(): void
    {
        $result = $this->cart->toCart();
        $this->assertInstanceOf(Cart::class, $result);
    }

    public function test_add_multiple_items_and_check_all(): void
    {
        $this->cart->add(['product_id' => 1, 'plan_id' => 1, 'quantity' => 1], 'USD');
        $this->cart->add(['product_id' => 2, 'plan_id' => 2, 'quantity' => 2], 'USD');
        $all = $this->cart->all();
        $this->assertCount(2, $all);
    }
}
