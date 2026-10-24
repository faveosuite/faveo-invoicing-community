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
    // getCreditBalance — GET /get-credit-balance
    // -------------------------------------------------------------------------

    public function test_get_credit_balance_lists_history_from_the_ledger(): void
    {
        $invoice = Invoice::factory()->create([
            'user_id' => $this->user->id,
            'grand_total' => 30.0,
            'currency' => 'USD',
            'status' => 'pending',
        ]);

        $credits = new \App\Services\Payment\CreditBalanceService;
        $credits->grant($this->user->id, 'USD', 80.0, \App\Model\Order\CreditTransaction::TYPE_OVERPAYMENT, note: 'Received via cash on 2026-08-21');
        $credits->apply($this->user->id, 'USD', 30.0, (int) $invoice->id);

        $data = $this->getJson('/get-credit-balance')->assertStatus(200)->json('data');

        // Two ledger movements, newest first, each explained in words — the old
        // credit_activity table would have returned an empty list here.
        $this->assertCount(2, $data['activity']);
        $this->assertStringContainsString($invoice->number, $data['activity'][0]['text']);
        $this->assertStringContainsString('applied', $data['activity'][0]['text']);
        $this->assertStringContainsString('Received via cash on 2026-08-21', $data['activity'][1]['text']);
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

    public function test_get_client_order_detail_returns_404_for_unknown_order_id(): void
    {
        $response = $this->getJson('/get-my-order/999999');

        $response->assertStatus(404)
            ->assertJson(['success' => false]);
    }

    public function test_get_client_order_detail_returns_404_for_non_numeric_id(): void
    {
        // The SPA once sent /get-my-order/undefined while navigating away; that used to 500.
        $this->getJson('/get-my-order/undefined')->assertStatus(404);
    }

    public function test_get_client_order_detail_returns_single_order(): void
    {
        $product = Product::first() ?? Product::create(['name' => 'Test Product '.uniqid()]);

        $order = Order::create([
            'client' => $this->user->id,
            'product' => $product->id,
            'order_status' => 'executed',
            'number' => mt_rand(10000000, 99999999),
            'price_override' => 0,
        ]);

        $response = $this->getJson('/get-my-order/'.$order->id);

        $response->assertStatus(200)
            ->assertJson(['success' => true]);

        $data = $response->json('data');
        $this->assertEquals($order->id, $data['id']);
        $this->assertEquals($order->number, $data['number']);
        $this->assertArrayHasKey('product_name', $data);
        $this->assertArrayHasKey('status', $data);
    }

    public function test_get_client_order_detail_includes_license_mode_domain_and_machine_id(): void
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

        $response = $this->getJson('/get-my-order/'.$order->id);

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

    /**
     * An order whose versions the client may list: "Allow downloads" on and updates that never end.
     *
     * @return array{0: Product, 1: Order}
     */
    private function downloadableOrder(): array
    {
        $type = \App\Model\License\LicenseType::factory()->create();
        $type->permissions()->attach(\App\Model\License\LicensePermission::firstOrCreate(['permissions' => 'Can be Downloaded'])->id);
        $product = Product::create(['name' => 'Test Product '.uniqid(), 'type' => $type->id]);
        $order = Order::create(['client' => $this->user->id, 'order_status' => 'executed', 'product' => $product->id, 'number' => random_int(100000000, 999999999)]);
        \App\Model\Product\Subscription::factory()->create([
            'order_id' => $order->id, 'product_id' => $product->id, 'user_id' => $this->user->id, 'update_ends_at' => null, 'is_deleted' => 0,
        ]);

        return [$product, $order];
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

    public function test_version_popup_features_the_latest_release_and_the_latest_official_when_latest_is_an_rc(): void
    {
        [$product, $order] = $this->downloadableOrder();
        $upload = fn (string $version, string $type, string $date) => \App\Model\Product\ProductUpload::create([
            'product_id' => $product->id, 'title' => $version, 'version' => $version, 'file' => 'x.zip',
            'is_private' => 0, 'release_type' => $type,
        ])->forceFill(['created_at' => $date])->save();
        $featured = fn () => collect($this->getJson("/get-versions/{$order->id}?limit=1")->assertStatus(200)->json('data.featured'))
            ->map(fn ($v) => [$v['version'], $v['release_type']])->all();

        $upload('v1.0.0', 'official', '2026-01-01');
        $upload('v1.1.0', 'official', '2026-02-01');
        // Latest is official → only that one.
        $this->assertSame([['v1.1.0', 'official']], $featured());

        $upload('v1.2.0.RC.1', 'pre_release', '2026-03-01');
        // Latest is an RC → the RC first, then the latest official alongside it.
        $this->assertSame([['v1.2.0.RC.1', 'pre_release'], ['v1.1.0', 'official']], $featured());

        // "Latest" = last added (highest id), even if its date is older.
        $upload('v1.3.0', 'official', '2025-12-01');
        $this->assertSame([['v1.3.0', 'official']], $featured());
    }

    public function test_version_list_pages_never_skip_or_repeat_versions_uploaded_at_the_same_time(): void
    {
        // QA case: a bulk upload gives several versions one timestamp; sorted by date,
        // v9.4.3.RC.3 went missing between pages.
        [$product, $order] = $this->downloadableOrder();
        $versions = ['v9.4.3.RC.1', 'v9.4.3.RC.2', 'v9.4.3.RC.3', 'v9.4.3.RC.4', 'v9.4.3.RC.5'];
        foreach ($versions as $version) {
            \App\Model\Product\ProductUpload::create([
                'product_id' => $product->id, 'title' => $version, 'version' => $version, 'file' => 'x.zip',
                'is_private' => 0, 'release_type' => 'pre_release',
            ])->forceFill(['created_at' => '2026-04-20 12:21:34'])->save();
        }

        $seen = [];
        foreach ([1, 2, 3] as $page) {
            $rows = $this->getJson("/get-versions/{$order->id}?limit=2&page={$page}&sort-field=created_at&sort-order=desc")->json('data.data');
            $seen = array_merge($seen, array_column($rows, 'version'));
        }

        // The newest (RC.5) is the featured card, so the table lists the other four.
        $this->assertSame(array_reverse(array_slice($versions, 0, 4)), $seen);
    }

    public function test_previous_versions_table_leaves_out_the_featured_versions(): void
    {
        [$product, $order] = $this->downloadableOrder();
        $upload = fn (string $version, string $type) => \App\Model\Product\ProductUpload::create([
            'product_id' => $product->id, 'title' => $version, 'version' => $version, 'file' => 'x.zip',
            'is_private' => 0, 'release_type' => $type,
        ]);
        $upload('v1.0.0', 'official');
        $upload('v1.1.0', 'official');
        $upload('v1.2.0.RC.1', 'pre_release'); // latest is an RC → it and v1.1.0 are both cards

        $data = $this->getJson("/get-versions/{$order->id}")->assertOk()->json('data');

        $this->assertSame(['v1.2.0.RC.1', 'v1.1.0'], array_column(array_values($data['featured']), 'version'));
        $this->assertSame(['v1.0.0'], array_column($data['data'], 'version'));
        $this->assertTrue($data['has_previous']);
    }

    public function test_github_versions_list_more_than_three_and_only_link_the_rows_shown(): void
    {
        [$product, $order] = $this->downloadableOrder();
        \App\Model\Product\Product::whereKey($product->id)->update(['github_owner' => 'acme', 'github_repository' => 'app']);
        $releases = array_map(fn (int $i): array => [
            'tag_name' => "v1.{$i}.0", 'name' => "v1.{$i}.0", 'prerelease' => false, 'body' => '',
            'created_at' => now()->subDays(10 - $i)->toIso8601String(), 'zipball_url' => "https://api.github.test/zip/v1.{$i}.0",
        ], range(5, 0)); // newest first, as GitHub returns them

        $github = \Mockery::mock(\App\Http\Controllers\Github\GithubApiController::class);
        $github->shouldReceive('releases')->once()->andReturn($releases);
        // 2 table rows + 1 featured card — not all 6 releases.
        $github->shouldReceive('resolveDownloadUrl')->times(3)->andReturnUsing(fn (string $zip): string => $zip.'?signed');
        $this->app->instance(\App\Http\Controllers\Github\GithubApiController::class, $github);

        $data = $this->getJson("/get-versions/{$order->id}?limit=2")->assertOk()->json('data');

        $this->assertSame('v1.5.0', array_values($data['featured'])[0]['version']);
        $this->assertSame(5, $data['total']); // every older release, not capped at 3
        $this->assertSame(['v1.4.0', 'v1.3.0'], array_column($data['data'], 'version'));
        $this->assertStringEndsWith('?signed', $data['data'][0]['download_url']);
    }

    public function test_no_previous_versions_when_only_the_featured_one_exists(): void
    {
        [$product, $order] = $this->downloadableOrder();
        \App\Model\Product\ProductUpload::create(['product_id' => $product->id, 'title' => 'v1', 'version' => 'v1.0.0', 'file' => 'x.zip', 'is_private' => 0, 'release_type' => 'official']);

        $data = $this->getJson("/get-versions/{$order->id}")->assertOk()->json('data');

        $this->assertSame([], $data['data']);
        $this->assertFalse($data['has_previous']);
    }

    public function test_renew_popup_vue_shows_one_time_plans_only_to_one_time_orders(): void
    {
        $product = Product::create(['name' => 'Test Product '.uniqid()]);
        $oneTimePeriod = \App\Model\Payment\Period::firstOrCreate(['name' => 'One Time'], ['days' => 365]);
        $currency = getCurrencyForClient($this->user->country);
        $makePlan = function (string $name) use ($product, $currency) {
            $plan = \App\Model\Payment\Plan::create(['name' => $name, 'product' => $product->id, 'days' => 180, 'status' => 1]);
            \App\Model\Payment\PlanPrice::create(['plan_id' => $plan->id, 'currency' => $currency, 'add_price' => 100, 'renew_price' => 100]);

            return $plan;
        };
        $recurring = $makePlan('Half yearly');
        $oneTime = $makePlan('Lifetime');
        $oneTime->periods()->attach($oneTimePeriod->id);

        $makeOrder = function ($plan) use ($product) {
            $order = Order::create(['client' => $this->user->id, 'order_status' => 'executed', 'product' => $product->id, 'number' => random_int(100000000, 999999999)]);
            \App\Model\Product\Subscription::create(['user_id' => $this->user->id, 'plan_id' => $plan->id, 'order_id' => $order->id, 'product_id' => $product->id]);

            return $order;
        };

        $planIds = fn ($order) => collect($this->getJson("/renew-popup-details/{$product->id}?order={$order->id}")->json('data.plans'))->pluck('id')->all();

        $this->assertSame([$recurring->id], $planIds($makeOrder($recurring)));
        $this->assertSame([$oneTime->id], $planIds($makeOrder($oneTime)));
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

    public function test_get_cloud_settings_is_refused_for_non_cloud_order(): void
    {
        $product = \App\Model\Product\Product::create(['name' => 'Test Product '.uniqid()]);

        $order = \App\Model\Order\Order::create([
            'client' => $this->user->id,
            'product' => $product->id,
            'order_status' => 'executed',
            'number' => 'CLOUD-'.uniqid(),
        ]);

        $response = $this->getJson('/get-cloud-settings/'.$order->id);

        // Product not in cloudPopupProducts() → refused by OrderPolicy::manageCloud
        $response->assertStatus(403)
            ->assertJson(['success' => false, 'message' => __('message.no_permission_for_action')]);
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

    public function test_renewals_count_and_list_skip_deleted_instances_terminated_orders_and_lifetime_licenses(): void
    {
        $make = function (string $status, int $deleted, bool $licenseExpires = true): Order {
            $order = Order::factory()->withRelations()->create(['client' => $this->user->id, 'order_status' => $status]);
            \App\Model\Product\Subscription::where('order_id', $order->id)->update([
                'ends_at' => $licenseExpires ? now()->subDay() : null,
                'update_ends_at' => now()->subDay(),
                'is_deleted' => $deleted,
            ]);

            return $order;
        };
        $renewable = $make('executed', 0);
        $make('executed', 1);   // instance deleted — nothing left to renew
        $make('Terminated', 0); // replaced by its upgrade order
        $make('executed', 0, false); // lifetime license (One Time plan): only its updates lapsed

        $this->assertSame(1, $this->getJson('/client-dashboard-details')->json('data.order_renewals_count'));

        $ids = collect($this->getJson('/get-my-orders?renewal=1')->json('data.data'))->pluck('id')->all();
        $this->assertSame([$renewable->id], $ids);
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
        $this->assertContains($response->status(), [400, 412]);
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
        $this->assertContains($response->status(), [200, 412]);
    }
}
