<?php

namespace Tests\Unit\Client\Cart;

use App\Model\Order\Invoice;
use App\Model\Payment\PromoProductRelation;
use App\Model\Payment\Promotion;
use App\Model\Payment\PromotionType;
use App\Model\Product\Product;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\DBTestCase;

class CouponTest extends DBTestCase
{
    use DatabaseTransactions;

    #[Group('coupon')]
    // public function test_addCouponUpdate_whenCouponProvided()
    // {
    //     $this->withoutMiddleware();
    //     $this->getLoggedInUser();
    //     $user = $this->user;
    //     $invoice = Invoice::factory()->create(['user_id'=>$user->id]);
    //     $product = Product::factory()->create();

    //     $promotionType = PromotionType::create(['name'=>'Fixed Amount']);
    //     $promotion = Promotion::create(['code'=> 'FAVEOCOUPON',
    //         'type'                            => $promotionType->id,
    //         'uses'                            => '1',
    //         'value'                           => '100',
    //         'start'                           => '2018-06-30 00:00:00',
    //         'expiry'                          => '2018-07-30 00:00:00',
    //     ]);
    //     \Cart::add([
    //         'id'         => $product->id,
    //         'name'       => $product->name,
    //         'price'      => $invoice->grand_total,
    //         'quantity'   => 1,
    //         'attributes' => [],
    //     ]);
    //     $response = $this->call('POST', 'pricing/update', [
    //     'coupon' => 'FAVEOCOUPON',
    //     ]);
    //     $response->assertStatus(302);
    // }

    #[Group('coupon')]
    // public function test_checkCode_whenValidCouponProvided()
    // {
    //     $this->withoutMiddleware();
    //     $this->getLoggedInUser();
    //     $user = $this->user;
    //     $currency = $user->currency;
    //     $invoice = Invoice::factory()->create(['user_id'=>$user->id]);
    //     $promotionTypeName = PromotionType::find(2);
    //     $promotionType = $promotionTypeName->name;
    //     $product = Product::factory()->create();
    //     $promotion = Promotion::create(['code'=> 'FAVEOCOUPON',
    //         'type'                            => $promotionTypeName->id,
    //         'uses'                            => '100',
    //         'value'                           => '100',
    //         'start'                           => '2018-06-30 00:00:00',
    //         'expiry'                          => '2019-07-30 00:00:00',
    //     ]);

    //     $promotion = PromoProductRelation::create(['promotion_id'=> $promotion->id,
    //         'product_id'                                         => $product->id,
    //     ]);
    //     \Cart::add([
    //         'id'         => $product->id,
    //         'name'       => $product->name,
    //         'price'      => $invoice->grand_total,
    //         'quantity'   => 1,
    //         'attributes' => [],
    //     ]);
    //     $controller = new \App\Http\Controllers\Payment\PromotionController();
    //     $response = $controller->checkCode('FAVEOCOUPON', $product->id);
    //     $this->assertEquals($response, 'success');
    // }

    #[Group('coupon')]
    public function test_checkCode_whenExpiredCouponProvided()
    {
        $this->expectException(\Exception::class);
        $this->withoutMiddleware();

        $this->getLoggedInUser();
        $user = $this->user;
        $currency = $user->currency;
        $invoice = Invoice::factory()->create(['user_id' => $user->id]);
        $promotionTypeName = PromotionType::find(2);
        $promotionType = $promotionTypeName->name;
        $product = Product::factory()->create();
        $promotion = Promotion::create(['code' => 'FAVEOCOUPON',
            'type' => $promotionTypeName->id,
            'uses' => '100',
            'value' => '100',
            'start' => '2017-06-30 00:00:00',
            'expiry' => '2017-07-30 00:00:00',

        ]);

        $promotion = PromoProductRelation::create(['promotion_id' => $promotion->id,
            'product_id' => $product->id,
        ]);

        \Cart::add([
            'id' => $product->id,
            'name' => $product->name,
            'price' => $invoice->grand_total,
            'quantity' => 1,
            'attributes' => [],
        ]);
        $controller = new \App\Http\Controllers\Payment\PromotionController();
        $response = $controller->checkCode('FAVEOCOUPON', $product->id);
    }

    #[Group('coupon')]
    public function test_checkCode_whenInvalidCouponProvided()
    {
        $this->expectException(\Exception::class);
        $this->withoutMiddleware();
        $this->getLoggedInUser();
        $user = $this->user;
        $currency = $user->currency;
        $invoice = Invoice::factory()->create(['user_id' => $user->id]);
        $promotionTypeName = PromotionType::find(2);
        $promotionType = $promotionTypeName->name;
        $product = Product::factory()->create();
        $promotion = Promotion::create(['code' => 'FAVEOCOUPON',
            'type' => $promotionTypeName->id,
            'uses' => '100',
            'value' => '100',
            'start' => '2018-06-30 00:00:00',
            'expiry' => '2018-07-30 00:00:00',
        ]);

        $promotion = PromoProductRelation::create(['promotion_id' => $promotion->id,
            'product_id' => $product->id,
        ]);
        \Cart::add([
            'id' => $product->id,
            'name' => $product->name,
            'price' => $invoice->grand_total,
            'quantity' => 1,
            'attributes' => [],
        ]);
        $controller = new \App\Http\Controllers\Payment\PromotionController();
        $response = $controller->checkCode('FAVEOCOUPON123', $product->id);
    }
}
