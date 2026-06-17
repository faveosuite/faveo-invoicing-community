<?php

namespace Tests\Unit\Agent\Order;

use App\Http\Controllers\License\LicenseController;
use App\Model\Common\StatusSetting;
use App\Model\Order\Order;
use App\Model\Order\Payment;
use Event;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Date;
use Mockery\MockInterface;
use Tests\DBTestCase;

class OrderControllerTest extends DBTestCase
{
    use DatabaseTransactions;

    protected function setUp(): void
    {
        parent::setUp();
        $this->getLoggedInUser('admin');
    }

    public function test_get_orders_basic_success(): void
    {
        $order = Order::factory()->withRelations()->create();

        $response = $this->getJson('/orders');

        $response->assertStatus(200)
            ->assertJsonFragment(['success' => true])
            ->assertJsonFragment([
                'id' => $order->id,
                'plan' => $order->plan->name,
            ])
            ->assertJsonFragment([
                'email' => $order->user->email,
            ]);
    }

    public function test_get_orders_search(): void
    {
        $order = Order::factory()->withRelations()->create();

        $searchValue = $order->user->first_name;

        $response = $this->getJson('/orders?search-query=' . $searchValue);

        $response->assertStatus(200)
            ->assertJsonFragment([
                'first_name' => $searchValue,
                'id' => $order->id,
            ]);

        $this->assertCount(1, $response->json('data.data'));
    }

    public function test_get_orders_sorting(): void
    {
        Order::factory()->withRelations([
            'created_at' => now()->subSeconds(10),
        ])->create();

        Order::factory()->withRelations([
            'created_at' => now(),
        ])->create();

        $response = $this->getJson('/orders?sort-field=created_at&sort-order=desc');

        $response->assertStatus(200);

        $data = $response->json('data.data');

        $first = Date::parse($data[0]['order_date']);
        $second = Date::parse($data[1]['order_date']);

        $this->assertTrue($first->greaterThanOrEqualTo($second));
    }

    public function test_get_orders_unlimited_agents(): void
    {
        Order::factory()->withRelations([
            'serial_key' => 'AAAABBBBCCCC0000', // 0000 => Unlimited
        ])->create();

        $response = $this->getJson('/orders');

        $response->assertStatus(200)
            ->assertJsonFragment(['agents' => 'Unlimited']);
    }

    public function test_get_single_order_success(): void
    {
        $order = Order::factory()->withRelations()->create();

        StatusSetting::factory()->create(['license_status' => 0]);

        $response = $this->getJson('/order/' . $order->id);

        $response->assertStatus(200)
            ->assertJsonStructure(['data' => ['order']]);
    }

    public function test_get_single_order_user_soft_deleted_blocked(): void
    {
        $order = Order::factory()->withRelations()->create();

        $order->user->delete();

        $response = $this->getJson('/order/' . $order->id);

        $response->assertStatus(403)
            ->assertJsonFragment(['message' => __('message.user_suspended_restore_to_view')]);
    }

    public function test_get_single_order_not_found(): void
    {
        $response = $this->getJson('/order/999999');

        $response->assertStatus(404);
    }

    public function test_get_installation_details_success(): void
    {
        $order = Order::factory()->withRelations()->create();

        $mockResponse = [
            [
                'installation_domain' => 'domain.com',
                'installation_ip' => '127.0.0.1',
                'installation_last_active_date' => '2023-01-01',
                'installation_status' => 'active',
                'version_number' => '1.0',
            ],
        ];

        $this->mock(LicenseController::class, function (MockInterface $mock) use ($order, $mockResponse): void {
            $mock->shouldReceive('getInstallationLogsDetails')
                ->once()
                ->with($order->serial_key)
                ->andReturn($mockResponse);
        });

        $response = $this->getJson('/get-installation-details/' . $order->id);

        $response->assertStatus(200)
            ->assertJson([
                'data' => [
                    [
                        'path' => 'domain.com',
                        'ip' => '127.0.0.1',
                        'version' => '1.0',
                        'status' => 'active',
                        'last_active_date' => '2023-01-01',
                    ],
                ],
            ]);
    }

    public function test_get_installation_details_order_not_found(): void
    {
        $response = $this->getJson('/get-installation-details/99999');

        $response->assertStatus(400)
            ->assertJsonFragment(['success' => false]);
    }

    public function test_delete_bulk_orders_success(): void
    {
        Event::fake();

        $orders = collect(range(1, 10))
            ->map(fn () => Order::factory()->withRelations()->create()->id);

        $response = $this->deleteJson('/orders', [
            'order_ids' => $orders->toArray(),
        ]);

        $response->assertStatus(200)
            ->assertJsonFragment(['message' => __('message.deleted-successfully')]);

        $orders->each(
            fn ($id) => $this->assertDatabaseMissing('orders', ['id' => $id])
        );
    }

    public function test_delete_bulk_orders_empty_input(): void
    {
        $response = $this->deleteJson('/orders', [
            'order_ids' => [],
        ]);

        $response->assertStatus(400)
            ->assertJsonFragment(['message' => __('message.select-a-row')]);
    }

    public function test_delete_bulk_orders_not_found(): void
    {
        $response = $this->deleteJson('/orders', [
            'order_ids' => [999999],
        ]);

        $response->assertStatus(200);
    }

    public function test_get_order_payments_success(): void
    {
        $order = Order::factory()->withRelations()->create();

        Payment::factory()->create([
            'invoice_id' => $order->invoice->id,
            'payment_method' => 'Stripe',
        ]);

        $response = $this->getJson('/getOrderPayments/' . $order->id);

        $response->assertStatus(200)
            ->assertJsonFragment([
                'payment_method' => 'Stripe',
                'invoice_number' => $order->invoice->number,
            ]);
    }

    public function test_get_order_invoices_success(): void
    {
        $order = Order::factory()->withRelations()->create();

        $response = $this->getJson('/getOrderInvoices/' . $order->id);

        $response->assertStatus(200)
            ->assertJsonFragment(['products' => [$order->productModel->name]]);
    }

    public function test_get_order_invoices_sorting(): void
    {
        $order = Order::factory()->withRelations()->create();

        $response = $this->getJson(sprintf('/getOrderInvoices/%s?sort-order=desc', $order->id));

        $response->assertStatus(200);

        $firstDate = $response->json('data.data.0.date');
        $secondDate = $response->json('data.data.1.date');

        $this->assertTrue($firstDate >= $secondDate);
    }
}
