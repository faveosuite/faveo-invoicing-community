<?php

namespace Tests\Unit\Backend\Http\Controllers\Front\Cart;

use App\Model\Payment\Promotion;
use App\Model\Payment\PromotionType;
use App\Model\Product\Product;
use App\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use PHPUnit\Framework\Attributes\Group;
use Tests\DBTestCase;

class CouponTest extends DBTestCase
{
    use DatabaseTransactions;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutMiddleware();
    }

    #[Group('coupon')]
    public function test_check_code_when_expired_coupon_provided(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $promotionType = PromotionType::first() ?? PromotionType::create(['name' => 'Fixed Amount']);
        $product = Product::factory()->create();
        Promotion::create([
            'code' => 'EXPIREDCOUPON',
            'type' => $promotionType->id,
            'uses' => '100',
            'value' => '100',
            'start' => '2017-06-30 00:00:00',
            'expiry' => '2017-07-30 00:00:00',
        ]);

        $response = $this->postJson('cart/coupon', ['code' => 'EXPIREDCOUPON']);

        $response->assertStatus(422);
        $response->assertJson(['success' => false]);
    }

    #[Group('coupon')]
    public function test_check_code_when_invalid_coupon_provided(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $response = $this->postJson('cart/coupon', ['code' => 'NONEXISTENTCODE99999']);

        $response->assertStatus(422);
        $response->assertJson(['success' => false]);
    }
}
