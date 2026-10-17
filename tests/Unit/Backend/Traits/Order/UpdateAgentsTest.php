<?php

namespace Tests\Unit\Backend\Traits\Order;

use App\Enums\FaveoStatusCode;
use App\License\Models\License;
use App\Model\Order\Invoice;
use App\Model\Order\InvoiceItem;
use App\Model\Order\Order;
use App\Model\Order\OrderInvoiceRelation;
use App\Model\Payment\Plan;
use App\Model\Product\CloudProducts;
use App\Model\Product\Product;
use App\Model\Product\Subscription;
use App\User;
use GuzzleHttp\Client;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use PHPUnit\Framework\Attributes\Group;
use Tests\DBTestCase;

/**
 * Admin-side agent count edit — the license's last four digits are the seat
 * count, so these assert the digits actually move and the invoice items that
 * price the next renewal move with them.
 */
#[Group('Admin Agent Change')]
class UpdateAgentsTest extends DBTestCase
{
    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutMiddleware();
        $this->getLoggedInUser('admin');
        $this->admin = $this->user;

        // An empty queue: any outbound HTTP call fails the test rather than
        // silently reaching a real host.
        $this->app->bind(Client::class, fn (): Client => new Client([
            'handler' => HandlerStack::create(new MockHandler([])),
        ]));
    }

    protected function tearDown(): void
    {
        restore_error_handler();
        restore_exception_handler();

        parent::tearDown();
    }

    /**
     * @return array{order: Order, item: InvoiceItem}
     */
    private function makeOrder(string $serialKey, bool $cloud = false): array
    {
        $product = Product::create(['name' => 'Helpdesk '.uniqid(), 'description' => 'p', 'type' => 1]);
        $plan = Plan::create(['name' => 'Plan '.uniqid(), 'product' => $product->id, 'days' => 365]);

        if ($cloud) {
            CloudProducts::create(['cloud_product' => $product->id, 'cloud_free_plan' => $plan->id, 'cloud_product_key' => 'k'.uniqid()]);
        }

        $invoice = Invoice::factory()->create(['user_id' => $this->admin->id]);
        $item = InvoiceItem::create([
            'invoice_id' => $invoice->id,
            'product_name' => $product->name,
            'product_id' => $product->id,
            'agents' => (int) substr($serialKey, 12, 16),
        ]);

        $order = Order::create([
            'client' => $this->admin->id,
            'order_status' => 'executed',
            'product' => $product->id,
            'number' => (string) mt_rand(100000, 999999),
            'invoice_item_id' => $item->id,
            'serial_key' => $serialKey,
        ]);

        OrderInvoiceRelation::create(['order_id' => $order->id, 'invoice_id' => $invoice->id]);
        Subscription::create([
            'plan_id' => $plan->id, 'order_id' => $order->id, 'product_id' => $product->id,
            'version' => 'v6.0.0', 'update_ends_at' => '', 'ends_at' => '',
        ]);
        License::create([
            'product_id' => $product->id,
            'user_id' => $this->admin->id,
            'license_code' => $serialKey,
            'license_order_number' => $order->number,
        ]);

        return ['order' => $order, 'item' => $item];
    }

    public function test_self_hosted_agent_increase_rewrites_license_and_invoice_items(): void
    {
        ['order' => $order, 'item' => $item] = $this->makeOrder('1234567890120003');

        $response = $this->postJson('update-license-details', ['orderid' => $order->id, 'agents' => 10]);

        $response->assertStatus(200);
        $this->assertSame('1234567890120010', $order->fresh()->serial_key);
        $this->assertSame('10', (string) $item->fresh()->agents);
        $this->assertSame(1, License::where('license_code', '1234567890120010')->count());
    }

    public function test_agents_zero_means_unlimited(): void
    {
        ['order' => $order] = $this->makeOrder('1234567890120007');

        $this->postJson('update-license-details', ['orderid' => $order->id, 'agents' => 0])->assertStatus(200);

        $this->assertSame('1234567890120000', $order->fresh()->serial_key);
    }

    public function test_unchanged_agent_count_leaves_the_license_alone(): void
    {
        ['order' => $order] = $this->makeOrder('1234567890120005');

        $this->postJson('update-license-details', ['orderid' => $order->id, 'agents' => 5])->assertStatus(200);

        $this->assertSame('1234567890120005', $order->fresh()->serial_key);
    }

    public function test_cloud_order_without_an_installation_is_refused(): void
    {
        ['order' => $order] = $this->makeOrder('1234567890120003', cloud: true);

        $response = $this->postJson('update-license-details', ['orderid' => $order->id, 'agents' => 9]);

        $response->assertStatus(400);
        $this->assertSame('1234567890120003', $order->fresh()->serial_key);
    }

    public function test_non_numeric_agents_is_rejected(): void
    {
        ['order' => $order] = $this->makeOrder('1234567890120003');

        $this->postJson('update-license-details', ['orderid' => $order->id, 'agents' => 'lots'])
            ->assertStatus(FaveoStatusCode::ValidationError->value);

        $this->assertSame('1234567890120003', $order->fresh()->serial_key);
    }
}
