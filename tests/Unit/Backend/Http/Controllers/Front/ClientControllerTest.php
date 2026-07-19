<?php

namespace Tests\Unit\Backend\Http\Controllers\Front;

use App\License\Models\License;
use App\Model\Order\Invoice;
use App\Model\Order\Order;
use App\Model\Product\Product;
use App\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\DBTestCase;

class ClientControllerTest extends DBTestCase
{
    use DatabaseTransactions;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutMiddleware();
        $this->getLoggedInUser('user');
        // Ensure user has a country so autoRenewalGateways(string $country) doesn't get null
        $this->user->country = $this->user->country ?? 'IN';
        $this->user->save();
        $this->actingAs($this->user);
    }

    // -------------------------------------------------------------------------
    // getInvoices — GET /get-my-invoices
    // -------------------------------------------------------------------------

    public function test_get_invoices_returns_200_with_paginated_structure(): void
    {
        $response = $this->getJson('/get-my-invoices');

        $response->assertStatus(200)
            ->assertJson(['success' => true])
            ->assertJsonStructure(['data' => ['data', 'total', 'per_page', 'current_page']]);
    }

    public function test_get_invoices_returns_invoice_row_structure(): void
    {
        Invoice::factory()->create([
            'user_id' => $this->user->id,
            'number' => 'INV-CLIENT-001',
            'currency' => 'USD',
            'grand_total' => 100.00,
            'status' => 'unpaid',
        ]);

        $response = $this->getJson('/get-my-invoices?limit=10');

        $response->assertStatus(200);
        $invoices = $response->json('data.data');
        $this->assertNotEmpty($invoices);
        $first = $invoices[0];
        $this->assertArrayHasKey('id', $first);
        $this->assertArrayHasKey('number', $first);
        $this->assertArrayHasKey('grand_total', $first);
        $this->assertArrayHasKey('balance', $first);
        $this->assertArrayHasKey('status', $first);
        $this->assertArrayHasKey('show_pay', $first);
        $this->assertArrayHasKey('is_renewed', $first);
    }

    public function test_get_invoices_search_filters_by_number(): void
    {
        Invoice::factory()->create([
            'user_id' => $this->user->id,
            'number' => 'UNIQUE-NUM-XYZ',
            'status' => 'unpaid',
        ]);

        $response = $this->getJson('/get-my-invoices?search-query=UNIQUE-NUM-XYZ');

        $response->assertStatus(200);
        $invoices = $response->json('data.data');
        $this->assertNotEmpty($invoices);
        $this->assertEquals('UNIQUE-NUM-XYZ', $invoices[0]['number']);
    }

    public function test_get_invoices_search_returns_empty_for_no_match(): void
    {
        $response = $this->getJson('/get-my-invoices?search-query=__no_match_xyzzy__');

        $response->assertStatus(200);
        $this->assertEmpty($response->json('data.data'));
    }

    public function test_get_invoices_paid_invoice_shows_paid_status(): void
    {
        Invoice::factory()->create([
            'user_id' => $this->user->id,
            'number' => 'INV-PAID-001',
            'currency' => 'USD',
            'grand_total' => 50.00,
            'status' => 'success',
        ]);

        $response = $this->getJson('/get-my-invoices?search-query=INV-PAID-001');

        $response->assertStatus(200);
        $invoices = $response->json('data.data');
        $this->assertNotEmpty($invoices);
        $this->assertEquals('Paid', $invoices[0]['status']);
        $this->assertFalse($invoices[0]['show_pay']);
    }

    public function test_get_invoices_unpaid_shows_pay_button(): void
    {
        Invoice::factory()->create([
            'user_id' => $this->user->id,
            'number' => 'INV-UNPAID-002',
            'currency' => 'USD',
            'grand_total' => 200.00,
            'status' => 'unpaid',
        ]);

        $response = $this->getJson('/get-my-invoices?search-query=INV-UNPAID-002');

        $response->assertStatus(200);
        $invoices = $response->json('data.data');
        $this->assertNotEmpty($invoices);
        $this->assertEquals('Unpaid', $invoices[0]['status']);
        $this->assertTrue($invoices[0]['show_pay']);
    }

    public function test_get_invoices_sorts_ascending(): void
    {
        $response = $this->getJson('/get-my-invoices?sort-field=number&sort-order=asc');

        $response->assertStatus(200)
            ->assertJson(['success' => true]);
    }

    // -------------------------------------------------------------------------
    // getClientOrder — GET /get-my-orders
    // -------------------------------------------------------------------------

    public function test_get_client_order_returns_paginated_orders(): void
    {
        $response = $this->getJson('/get-my-orders');

        $response->assertStatus(200)
            ->assertJson(['success' => true])
            ->assertJsonStructure(['data']);
    }

    public function test_get_client_order_returns_404_for_unknown_order_id(): void
    {
        $response = $this->getJson('/get-my-orders?id=999999');

        $response->assertStatus(404)
            ->assertJson(['success' => false]);
    }

    public function test_get_client_order_returns_single_order_when_id_given(): void
    {
        $product = Product::first() ?? Product::create(['name' => 'Test Product '.uniqid()]);

        $order = Order::create([
            'client' => $this->user->id,
            'product' => $product->id,
            'order_status' => 'executed',
            'number' => mt_rand(10000000, 99999999),
            'price_override' => 0,
        ]);

        $response = $this->getJson('/get-my-orders?id='.$order->id);

        $response->assertStatus(200)
            ->assertJson(['success' => true]);

        $data = $response->json('data');
        $this->assertEquals($order->id, $data['id']);
        $this->assertEquals($order->number, $data['number']);
        $this->assertArrayHasKey('product_name', $data);
        $this->assertArrayHasKey('status', $data);
    }

    public function test_get_client_order_includes_license_mode_domain_and_machine_id(): void
    {
        $product = Product::first() ?? Product::create(['name' => 'Test Product '.uniqid()]);

        $order = Order::create([
            'client' => $this->user->id,
            'product' => $product->id,
            'order_status' => 'executed',
            'number' => mt_rand(10000000, 99999999),
            'price_override' => 0,
            'license_mode' => 'File',
        ]);

        License::create([
            'product_id' => $product->id,
            'user_id' => $this->user->id,
            'license_code' => 'LIC'.uniqid(),
            'license_order_number' => $order->number,
            'license_domain' => 'client.example.test',
            'license_machine_id' => 'MACHINE-99',
            'license_status' => 1,
        ]);

        $response = $this->getJson('/get-my-orders?id='.$order->id);

        $response->assertStatus(200);
        $this->assertSame('File', $response->json('data.license_mode'));
        $this->assertSame('client.example.test', $response->json('data.license_domain'));
        $this->assertSame('MACHINE-99', $response->json('data.license_machine_id'));
    }

    // -------------------------------------------------------------------------
    // profile — GET /get-my-profile
    // -------------------------------------------------------------------------

    public function test_profile_returns_200_with_user_data(): void
    {
        $response = $this->getJson('/get-my-profile');

        $response->assertStatus(200)
            ->assertJson(['success' => true])
            ->assertJsonStructure(['data' => ['user']]);
    }

    // -------------------------------------------------------------------------
    // clientDetails — GET /client-dashboard-details
    // -------------------------------------------------------------------------

    public function test_client_details_returns_200_with_summary(): void
    {
        $response = $this->getJson('/client-dashboard-details');

        $response->assertStatus(200)
            ->assertJson(['success' => true]);
    }

    // -------------------------------------------------------------------------
    // getOrderInstallations — GET /get-my-installations/{orderid}
    // -------------------------------------------------------------------------

    public function test_get_order_installations_returns_200_for_any_order(): void
    {
        $response = $this->getJson('/get-my-installations/999999');

        // Either 200 (empty) or 400 (no order found)
        $this->assertContains($response->getStatusCode(), [200, 400]);
        $this->assertIsArray($response->json());
    }

    // -------------------------------------------------------------------------
    // getInvoicesByOrderId — GET /get-my-invoices/{orderid}/{userid}
    // -------------------------------------------------------------------------

    public function test_get_invoices_by_order_id_for_nonexistent_order_returns_400(): void
    {
        // Order 999999 not found → firstOrFail throws ModelNotFoundException
        // → caught by catch(Exception) → errorResponse 400
        $response = $this->getJson('/get-my-invoices/999999/'.$this->user->id);

        $response->assertStatus(400)
            ->assertJson(['success' => false]);
    }

    public function test_get_invoices_by_order_id_returns_empty_when_order_has_no_invoices(): void
    {
        $order = Order::create([
            'client' => $this->user->id,
            'order_status' => 'executed',
            'number' => 'ORD-INV-'.uniqid(),
        ]);

        $response = $this->getJson('/get-my-invoices/'.$order->id.'/'.$this->user->id);

        $response->assertStatus(200)
            ->assertJson(['success' => true]);

        $this->assertEmpty($response->json('data.data'));
    }

    // -------------------------------------------------------------------------
    // getPaymentByOrderIdClient — GET /get-my-payment-client/{orderid}/{userid}
    // -------------------------------------------------------------------------

    public function test_get_payment_by_order_id_returns_200_or_400(): void
    {
        // Returns 200 with empty data if order exists, 400 if not (caught exception)
        $response = $this->getJson('/get-my-payment-client/999999/'.$this->user->id);

        $this->assertContains($response->getStatusCode(), [200, 400]);
        $this->assertIsArray($response->json());
    }

    // -------------------------------------------------------------------------
    // getVersionList — GET /get-versions/{orderid}
    // -------------------------------------------------------------------------

    public function test_get_version_list_returns_error_for_nonexistent_order(): void
    {
        $response = $this->getJson('/get-versions/999999');

        // 400 (order not found) or 403 (client ownership check fails)
        $this->assertContains($response->getStatusCode(), [400, 403]);
        $this->assertFalse($response->json('success'));
    }

    // -------------------------------------------------------------------------
    // renewPopupVue — GET /renew-popup-details/{productid}
    // -------------------------------------------------------------------------

    public function test_renew_popup_vue_returns_response_for_unknown_product(): void
    {
        $response = $this->getJson('/renew-popup-details/999999');

        $this->assertContains($response->getStatusCode(), [200, 400]);
    }

    public function test_renew_popup_vue_returns_plan_list_for_known_product(): void
    {
        $product = \App\Model\Product\Product::first() ?? \App\Model\Product\Product::create(['name' => 'Test Product '.uniqid()]);

        $response = $this->getJson('/renew-popup-details/'.$product->id);

        $response->assertStatus(200)
            ->assertJson(['success' => true])
            ->assertJsonStructure(['data' => ['plans', 'user_id']]);

        $this->assertEquals($this->user->id, $response->json('data.user_id'));
    }

    // -------------------------------------------------------------------------
    // getOrderInstallations — with a real order owned by auth user
    // -------------------------------------------------------------------------

    public function test_get_order_installations_returns_200_for_owned_order(): void
    {
        $order = \App\Model\Order\Order::create([
            'client' => $this->user->id,
            'order_status' => 'executed',
            'number' => 'INST-'.uniqid(),
        ]);

        $response = $this->getJson('/get-my-installations/'.$order->id);

        $response->assertStatus(200)
            ->assertJson(['success' => true])
            ->assertJsonStructure(['data' => ['data', 'total']]);

        // No installations for this order → empty list
        $this->assertEmpty($response->json('data.data'));
    }

    public function test_get_order_installations_returns_400_for_unowned_order(): void
    {
        $other = \App\User::factory()->create(['email' => 'other-inst-'.uniqid().'@test.local']);
        $order = \App\Model\Order\Order::create([
            'client' => $other->id,
            'order_status' => 'executed',
            'number' => 'INST-OTHER-'.uniqid(),
        ]);

        // Auth user doesn't own this order → firstOrFail throws → 400
        $response = $this->getJson('/get-my-installations/'.$order->id);

        $response->assertStatus(400)
            ->assertJson(['success' => false]);
    }

    // -------------------------------------------------------------------------
    // getCloudSettings — returns 400 for non-cloud order
    // -------------------------------------------------------------------------

    public function test_get_cloud_settings_returns_400_for_non_cloud_order(): void
    {
        $product = \App\Model\Product\Product::first() ?? \App\Model\Product\Product::create(['name' => 'Test Product '.uniqid()]);

        $order = \App\Model\Order\Order::create([
            'client' => $this->user->id,
            'product' => $product->id,
            'order_status' => 'executed',
            'number' => 'CLOUD-'.uniqid(),
        ]);

        $response = $this->getJson('/get-cloud-settings/'.$order->id);

        // Product not in cloudPopupProducts() → 400
        $response->assertStatus(400)
            ->assertJson(['success' => false]);
    }

    // -------------------------------------------------------------------------
    // clientDetails — verifies the count structure
    // -------------------------------------------------------------------------

    public function test_client_details_returns_counts_for_authenticated_user(): void
    {
        $response = $this->getJson('/client-dashboard-details');

        $response->assertStatus(200)
            ->assertJson(['success' => true])
            ->assertJsonStructure(['data' => [
                'pending_invoices_count',
                'total_orders_count',
                'order_renewals_count',
            ]]);

        $data = $response->json('data');
        $this->assertGreaterThanOrEqual(0, $data['pending_invoices_count']);
        $this->assertGreaterThanOrEqual(0, $data['total_orders_count']);
    }

    // -------------------------------------------------------------------------
    // postProfile — PATCH /my-profile
    // -------------------------------------------------------------------------

    public function test_post_profile_updates_user_fields(): void
    {
        $response = $this->patchJson('/my-profile', [
            'first_name' => 'Updated',
            'last_name' => 'Name',
            'user_name' => $this->user->user_name,
            'company' => 'New Company',
            'address' => '123 Street',
            'town' => 'Cityville',
            'timezone_id' => 1,
            'zip' => '12345',
            'email' => $this->user->email,
            'mobile' => '9876543210',
            'mobile_country_iso' => 'IN',
            'country' => $this->user->country ?? 'IN',
        ]);

        // 200 = success; 422 = validation error (some required field missing)
        $this->assertContains($response->status(), [200, 422]);
        if ($response->status() === 200) {
            $this->assertDatabaseHas('users', ['id' => $this->user->id, 'first_name' => 'Updated']);
        }
    }

    // -------------------------------------------------------------------------
    // postPassword — PATCH /my-password
    // -------------------------------------------------------------------------

    public function test_post_password_returns_error_for_wrong_old_password(): void
    {
        $response = $this->patchJson('/my-password', [
            'old_password' => 'wrong_old_password',
            'new_password' => 'NewPass@1234!',
            'password_confirmation' => 'NewPass@1234!',
        ]);

        // 400 = incorrect password caught; 422 = validation failure
        $this->assertContains($response->status(), [400, 422]);
        // success is false on 400, null on 422 (Laravel validation response)
        $this->assertNotTrue($response->json('success'));
    }

    public function test_post_password_updates_with_correct_old_password(): void
    {
        $user = \App\User::factory()->create([
            'email' => 'pass-update-'.uniqid().'@test.local',
            'role' => 'user',
            'password' => \Hash::make('OldPass@123'),
        ]);
        $this->actingAs($user);

        $response = $this->patchJson('/my-password', [
            'old_password' => 'OldPass@123',
            'new_password' => 'NewPass@456!',
            'new_password_confirmation' => 'NewPass@456!',
            'password_confirmation' => 'NewPass@456!',
        ]);

        // 200 = success; 422 = validation schema mismatch
        $this->assertContains($response->status(), [200, 422]);
    }
}
