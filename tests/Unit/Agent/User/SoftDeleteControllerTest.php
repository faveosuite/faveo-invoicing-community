<?php

namespace Tests\Unit\Agent\User;

use App\Events\UserOrderDelete;
use App\Model\Order\Invoice;
use App\Model\Order\InvoiceItem;
use App\Model\Order\Order;
use App\Model\Product\Product;
use App\Model\Product\Subscription;
use App\User;
use DB;
use Event;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use PHPUnit\Framework\Attributes\Group;
use Tests\DBTestCase;

class SoftDeleteControllerTest extends DBTestCase
{
    use DatabaseTransactions;

    protected function setUp(): void
    {
        parent::setUp();
        $this->getLoggedInUser('admin');
    }

    #[Group('User')]
    public function test_can_get_only_soft_deleted_users(): void
    {
        User::factory()->count(3)->create(['deleted_at' => now()]);
        User::factory()->count(2)->create(); // NOT deleted

        $response = $this->getJson('/soft-delete');

        $response->assertStatus(200)
            ->assertJsonCount(3, 'data.data');
    }

    #[Group('User')]
    public function test_soft_deleted_users_search_works(): void
    {
        User::factory()->create([
            'deleted_at' => now(),
            'email' => 'search_user@test.com',
        ]);

        $response = $this->getJson('/soft-delete?search-query=search_user');

        $response->assertStatus(200)
            ->assertJsonFragment(['email' => 'search_user@test.com']);
    }

    #[Group('User')]
    public function test_soft_deleted_sorting_works(): void
    {
        User::factory()->create(['deleted_at' => now(), 'first_name' => 'AAA']);
        User::factory()->create(['deleted_at' => now(), 'first_name' => 'ZZZ']);

        $response = $this->getJson('/soft-delete?sort-field=first_name&sort-order=asc');

        $this->assertEquals('AAA', $response->json('data.data.0.first_name'));
    }

    #[Group('User')]
    public function test_can_restore_soft_deleted_user(): void
    {
        $user = User::factory()->create(['deleted_at' => now()]);

        $response = $this->getJson('/user/restore/' . $user->id);

        $response->assertStatus(200);
        $this->assertNull($user->fresh()->deleted_at);
    }

    #[Group('User')]
    public function test_restore_user_returns_404_if_not_found(): void
    {
        $response = $this->getJson('/user/restore/999999');

        $response->assertStatus(404)
            ->assertJsonFragment(['message' => __('message.user_not_found')]);
    }

    #[Group('User')]
    public function test_permanent_delete_requires_user_ids(): void
    {
        $response = $this->deleteJson('/permanent-delete-client', []);

        $response->assertStatus(400)
            ->assertJsonFragment(['message' => __('message.select-a-row')]);
    }

    #[Group('User')]
    public function test_can_permanently_delete_a_soft_deleted_user(): void
    {
        $user = User::factory()->create(['deleted_at' => now()]);

        $response = $this->deleteJson('/permanent-delete-client', [
            'user_ids' => [$user->id],
        ]);

        $response->assertStatus(200);
        $this->assertDatabaseMissing('users', ['id' => $user->id]);
    }

    #[Group('User')]
    public function test_permanent_delete_ignores_live_users(): void
    {
        $activeUser = User::factory()->create();

        $response = $this->deleteJson('/permanent-delete-client', [
            'user_ids' => [$activeUser->id],
        ]);

        $response->assertStatus(200);
        $this->assertDatabaseHas('users', ['id' => $activeUser->id]);
    }

    #[Group('User')]
    public function test_permanent_delete_skips_non_existent_ids(): void
    {
        $user = User::factory()->create(['deleted_at' => now()]);

        $response = $this->deleteJson('/permanent-delete-client', [
            'user_ids' => [$user->id, 999999],
        ]);

        $response->assertStatus(200);
        $this->assertDatabaseMissing('users', ['id' => $user->id]);
    }

    #[Group('User')]
    public function test_permanent_delete_removes_related_records(): void
    {
        $user = User::factory()->create(['deleted_at' => now()]);

        $product = Product::factory()->create();
        $order = Order::factory()->create(['client' => $user->id]);
        $invoice = Invoice::factory()->create(['user_id' => $user->id]);
        InvoiceItem::factory()->create([
            'invoice_id' => $invoice->id,
            'product_id' => $product->id,
        ]);
        Subscription::factory()->create([
            'user_id' => $user->id,
            'product_id' => $product->id,
        ]);

        $response = $this->deleteJson('/permanent-delete-client', [
            'user_ids' => [$user->id],
        ]);

        $response->assertStatus(200);

        $this->assertDatabaseMissing('orders', ['id' => $order->id]);
        $this->assertDatabaseMissing('invoices', ['id' => $invoice->id]);
        $this->assertDatabaseMissing('invoice_items', ['invoice_id' => $invoice->id]);
        $this->assertDatabaseMissing('users', ['id' => $user->id]);
    }

    #[Group('User')]
    public function test_event_fired_when_installation_path_exists(): void
    {
        Event::fake();

        $user = User::factory()->create(['deleted_at' => now()]);
        $order = Order::factory()->create(['client' => $user->id]);

        DB::table('installation_details')->insert([
            'order_id' => $order->id,
            'installation_path' => 'https://tenant1.example.com',
        ]);

        $this->deleteJson('/permanent-delete-client', [
            'user_ids' => [$user->id],
        ]);

        Event::assertDispatched(UserOrderDelete::class);
    }

    #[Group('User')]
    public function test_event_not_fired_for_cloud_central_domain(): void
    {
        Event::fake();

        $user = User::factory()->create(['deleted_at' => now()]);
        $order = Order::factory()->create(['client' => $user->id]);

        DB::table('installation_details')->insert([
            'order_id' => $order->id,
            'installation_path' => cloudCentralDomain(),
        ]);

        $this->deleteJson('/permanent-delete-client', [
            'user_ids' => [$user->id],
        ]);

        Event::assertNotDispatched(UserOrderDelete::class);
    }
}
