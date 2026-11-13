<?php

declare(strict_types=1);

namespace Tests\Unit\Backend\Http\Controllers;

use App\Http\Controllers\Front\StoreController;
use App\Http\Middleware\Install;
use App\Model\Common\Setting;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use Illuminate\Testing\TestResponse;
use Tests\TestCase;

class HomeControllerPricingDataTest extends TestCase
{
    use DatabaseTransactions;

    private int $group;

    // Prices go in the default currency: it is always in the feed, whatever the countries table holds.
    private string $currency;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutMiddleware(Install::class);

        $this->currency = (string) Setting::value('default_currency');
        $this->group = DB::table('product_groups')->insertGetId([
            'name' => 'g-test', 'headline' => 'Test Pricing', 'tagline' => 'tag', 'hidden' => 0, 'status' => 1,
            'pricing_templates_id' => DB::table('pricing_templates')->insertGetId(['name' => 't-'.uniqid()]),
        ]);
    }

    private function product(string $name, int $contactSales = 0): int
    {
        return DB::table('products')->insertGetId([
            'name' => $name, 'group' => $this->group, 'status' => 0, 'hidden' => 0, 'type' => 1,
            'add_to_contact' => $contactSales, 'created_at' => now(),
        ]);
    }

    private function price(int $product, string $amount, string $offer = '0'): void
    {
        $plan = DB::table('plans')->insertGetId(['name' => 'p'.uniqid(), 'product' => $product, 'days' => 365, 'status' => 1]);
        DB::table('plan_prices')->insert([
            'plan_id' => $plan, 'currency' => $this->currency, 'add_price' => $amount, 'renew_price' => $amount,
            'offer_price' => $offer, 'price_description' => 'per year',
        ]);
    }

    private function feed(): TestResponse
    {
        return $this->getJson('/pricing/data')->assertOk();
    }

    /** @return array<string, mixed>|null the test group as the feed lists it */
    private function feedGroup(TestResponse $response): ?array
    {
        return collect($response->json('groups'))->firstWhere('id', $this->group);
    }

    public function test_feed_lists_the_store_cards_for_each_group_and_currency(): void
    {
        $this->price($this->product('premium'), '300', offer: '10');
        $this->price($this->product('basic'), '100');
        $this->price($this->product('enterprise', contactSales: 1), '999');

        $group = $this->feedGroup($this->feed());

        // Product text once + the per-currency part gives back exactly what /store/{group} shows.
        $details = collect($group['products'])->keyBy('id');
        $cards = collect($group['prices'][$this->currency])->map(fn (array $card): array => $details[$card['id']] + $card);
        $store = resolve(StoreController::class)->storeProducts($this->group, $this->currency);
        $this->assertEquals($store->map(fn (array $p): array => collect($p)->sortKeys()->all())->all(), $cards->map(fn (array $p): array => collect($p)->sortKeys()->all())->all());

        $this->assertSame(['basic', 'premium', 'enterprise'], $cards->pluck('name')->all());
        $this->assertSame('contact', $cards->last()['button']['type']);
        $this->assertSame(['id', 'name', 'heading', 'tagline', 'switcher', 'products', 'prices'], array_keys($group));
        $this->assertSame('Test Pricing', $group['heading']);
        $this->assertTrue($group['switcher']);
    }

    public function test_feed_has_the_store_url_currencies_and_the_country_map_checkout_uses(): void
    {
        $response = $this->feed();

        $response->assertJsonStructure(['generated_at', 'store_url', 'labels' => ['custom_pricing', 'free', 'monthly', 'yearly', 'select_plan'], 'default_currency', 'currencies', 'countries', 'groups']);
        $response->assertJsonPath('labels.custom_pricing', __('message.custom_pricing'));
        $response->assertJsonPath('store_url', url('/'));
        $response->assertJsonPath('default_currency', $this->currency);
        $this->assertArrayHasKey($this->currency, $response->json('currencies'));

        // Each mapped country gets the same currency as checkout's getCurrencyForClient(), and that currency is priced.
        foreach ($response->json('countries') as $country => $currency) {
            $this->assertSame((string) getCurrencyForClient($country), $currency, "country $country");
            $this->assertNotSame($this->currency, $currency);
            $this->assertArrayHasKey($currency, $response->json('currencies'));
        }
    }

    public function test_groups_with_nothing_to_sell_are_left_out(): void
    {
        $this->product('no-price-yet');

        $this->assertNull($this->feedGroup($this->feed()));
    }
}
