<?php

namespace Tests\Unit;

use App\Http\Controllers\Front\PageController;
use App\Model\Front\FrontendPage;
use App\Model\Payment\Plan;
use App\Model\Product\Product;
use App\User;
use Tests\DBTestCase;

class PageControllerTest extends DBTestCase
{
    private $con;

    protected function setUp(): void
    {
        parent::setUp();
        $this->con = new PageController();
    }

    /**
     * A basic unit test example.
     *
     * @return void
     */
    public function test_validation_fails_if_required_field_empty()
    {
        $user = User::factory()->create(['role' => 'admin']);
        $this->actingAs($user);
        $response = $this->post('/pages', [
            'name' => 'demo',
            'slug' => 'demopass',
            'url' => 'http://demo.com',
            'content' => 'Here the new page created',
        ]);
        $errors = session('errors');
        $response->assertStatus(302);
    }

    public function test_updatepage_returnstatus200()
    {
        $user = User::factory()->create(['role' => 'admin']);
        $this->actingAs($user);
        $page = FrontendPage::create(['name' => 'demo',
            'slug' => 'demopass',
            'url' => 'http://demo.com',
            'publish' => 'yes',
            'content' => 'Here the new page created', ]);

        $response = $this->post('/pages', [
            'id' => $page->id,
            'name' => 'demo',
            'slug' => 'demopass',
            'url' => 'http://demo.com',
            'publish' => 'yes',
            'content' => 'Here the new page created',
        ]);
        $this->assertDatabaseHas('frontend_pages', ['name' => 'demo']);
    }

//    public function test_plansYear_returnstatus200()
//    {
//        // Create a sample product
//        $product = Product::factory()->create([
//            'id' => 1,
//            'name' => 'Example Product',
//        ]);
//
//        // Call the plansYear method with the sample URL and product ID
//        $page = new PageController();
//        $form = $page->plansYear('/example-url', $product->id);
//
//        // Assert that the form contains the expected action URL
//        $expectedAction = 'http://localhost/example-url';
//        $this->assertStringContainsString('action="'.$expectedAction.'"', $form);
//    }

    public function test_getPrice()
    {
        $product = Product::factory()->create([
            'id' => 40,
            'name' => 'demo Product',
        ]);
        $months = 12;
        $price = [];
        $priceDescription = 'per year';
        $value = Plan::factory()->create([
            'product' => $product->id,
            'days' => 30,
        ]);
        $value->id = 1;
        $cost = 100;
        $currency = 'USD';
        $offer = 10;

        $page = new PageController();
        $result = $page->getPrice($months, $price, $priceDescription, $value, $cost, $currency, $offer, $product);

        $expectedPrice = '12  $1,080.00 per year';
        $this->assertEquals($expectedPrice, $result[$value->id]);
    }

    public function testDetectSpamWithExcessivePunctuation()
    {
        $message = 'This is a spam message with too many exclamation marks!!!!!!!!!!';
        $response = $this->getPrivateMethod($this->con, 'containsExcessivePunctuation', [$message]);
        $this->assertTrue($response);
    }

    public function testDetectSpamWithExcessiveCaps()
    {
        $message = 'This is a SPAM message with TOO MANY CAPITAL LETTERS';
        $response = $this->getPrivateMethod($this->con, 'containsExcessiveCaps', [$message]);
        $this->assertTrue($response);
    }

    public function testDetectSpamWithSpamKeywords()
    {
        $message = 'This message contains the word Viagra';
        $response = $this->getPrivateMethod($this->con, 'containsSpamKeywords', [$message]);
        $this->assertTrue($response);
    }

    public function testDetectNonSpam()
    {
        $email = 'example@gmail.com';
        $message = 'Hi,Need a demo.';
        $response = $this->getPrivateMethod($this->con, 'detectSpam', [$email, $message]);
        $this->assertFalse($response);
    }

    public function testDetectSpam()
    {
        $email = 'example@gmail.com';
        $message = 'Promo codes and promotions are a great way to get additional benefits from using the online bookmaker 1xbet. Promo codes for one x bet One of these promo codes is 1xpromo. It makes it possible to receive an additional bonus to your account when registering on the bookmaker’s website. In order to take advantage of this offer, you need to enter a promo code during registration and make the first deposit of at least 1000 rubles. After this, a bonus in the amount of 100% of the deposit amount will be credited to your account. The received bonus can be used for betting on sporting events or online casinos. The conditions for receiving and using the bonus are described in detail on the official 1xbet website. The site also regularly holds promotions, within which you can receive additional promotional codes or bonuses. Follow the news and don’t miss the opportunity to get even more benefits from playing at 1xbet.';
        $response = $this->getPrivateMethod($this->con, 'detectSpam', [$email, $message]);
        $this->assertTrue($response);
    }
}
