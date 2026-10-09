<?php

declare(strict_types=1);

namespace Tests\Unit\Backend\Http\Controllers;

use App\Http\Middleware\Install;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use Torann\GeoIP\Facades\GeoIP;
use Torann\GeoIP\Location;
use Tests\TestCase;

class HomeControllerPricingDataTest extends TestCase
{
    use DatabaseTransactions;

    private int $group;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutMiddleware(Install::class);

        // GeoIP would call an external service; the visitor is always in the US (currency USD).
        GeoIP::shouldReceive('getLocation')->andReturn(new Location(['ip' => '8.8.8.8', 'iso_code' => 'US', 'country' => 'United States']));

        $this->group = DB::table('product_groups')->insertGetId([
            'name' => 'g-'.uniqid(), 'hidden' => 0,
            'pricing_templates_id' => DB::table('pricing_templates')->insertGetId(['name' => 't-'.uniqid()]),
        ]);
    }

    private function product(string $name, int $status, int $hidden = 0): int
    {
        return DB::table('products')->insertGetId([
            'name' => $name, 'group' => $this->group, 'status' => $status, 'hidden' => $hidden, 'type' => 1, 'created_at' => now(),
        ]);
    }

    private function price(int $product, int $days, string $currency = 'USD', string $amount = '10'): void
    {
        $plan = DB::table('plans')->insertGetId(['name' => 'p'.uniqid(), 'product' => $product, 'days' => $days, 'status' => 1]);
        DB::table('plan_prices')->insert(['plan_id' => $plan, 'currency' => $currency, 'add_price' => $amount, 'renew_price' => $amount, 'price_description' => 'desc']);
    }

    private function pricing(array $query): \Illuminate\Testing\TestResponse
    {
        return $this->getJson('/pricing/data?'.http_build_query($query));
    }

    public function test_requires_group_and_ip(): void
    {
        $this->pricing([])->assertStatus(422)->assertJsonStructure(['error']);
        $this->pricing(['group' => 999999999, 'ipAddress' => '8.8.8.8'])->assertStatus(422);
        $this->pricing(['group' => $this->group, 'ipAddress' => 'not-an-ip'])->assertStatus(422);
    }

    public function test_returns_visible_products_in_visitor_currency(): void
    {
        $shown = $this->product('shown', 0);
        $this->price($shown, 365);
        $this->price($this->product('hidden', 0, hidden: 1), 365);
        $this->price($this->product('other-currency', 0), 365, 'INR');

        $response = $this->pricing(['group' => $this->group, 'ipAddress' => '8.8.8.8'])->assertOk();

        $response->assertJsonPath('currency', 'USD');
        $response->assertJsonStructure(['products', 'currency', 'currency_symbol']);
        $this->assertSame(['shown'], array_column($response->json('products'), 'name'));
        $this->assertSame('desc', $response->json('products.0.price_description'));
    }

    public function test_cloud_product_needs_monthly_and_yearly_price(): void
    {
        $yearlyOnly = $this->product('yearly-only', 1);
        $this->price($yearlyOnly, 365);

        $both = $this->product('both', 1);
        $this->price($both, 30, amount: '5');
        $this->price($both, 365, amount: '4');

        $response = $this->pricing(['group' => $this->group, 'ipAddress' => '8.8.8.8'])->assertOk();

        $rows = $response->json('products');
        $this->assertSame(['both', 'both'], array_column($rows, 'name'));
        $this->assertEqualsCanonicalizing([30, 365], array_map('intval', array_column($rows, 'days')));
        $this->assertSame('per month', $rows[0]['price_description']);
    }
}
