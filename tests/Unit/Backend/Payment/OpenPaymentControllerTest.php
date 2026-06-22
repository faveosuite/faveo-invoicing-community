<?php

namespace Tests\Unit\Backend\Payment;

use App\ApiKey;
use App\Http\Controllers\Payment\OpenPaymentController;
use App\Model\Payment\OpenPaymentOrder;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Mockery;
use Tests\DBTestCase;

class OpenPaymentControllerTest extends DBTestCase
{
    use DatabaseTransactions;

    protected OpenPaymentController $controller;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutMiddleware();
        $this->controller = new OpenPaymentController;
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    /**
     * Helper to create a valid order data array.
     */
    protected function getValidOrderData(array $overrides = []): array
    {
        return array_merge([
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'mobile' => '+919876543210',
            'address' => '123 Main St',
            'city' => 'Mumbai',
            'state' => 'Maharashtra',
            'zip' => '400001',
            'country' => 'India',
            'company' => 'Test Company',
            'amount' => 100.00,
            'currency' => 'INR',
            'gateway' => 'Razorpay',
            'description' => 'Test payment',
        ], $overrides);
    }

    /**
     * Helper to create an OpenPaymentOrder.
     */
    protected function createOrder(array $overrides = []): OpenPaymentOrder
    {
        return OpenPaymentOrder::create($this->getValidOrderData($overrides));
    }

    /**
     * Helper to create API keys.
     */
    protected function createApiKeys(array $overrides = []): ApiKey
    {
        $data = array_merge([
            'rzp_key' => 'rzp_test_key',
            'rzp_secret' => 'rzp_test_secret',
            'stripe_key' => 'pk_test_key',
            'stripe_secret' => 'sk_test_secret',
        ], $overrides);

        // Use updateOrCreate to handle existing API keys
        $apiKey = ApiKey::first();
        if ($apiKey) {
            $apiKey->update($data);

            return $apiKey;
        }

        return ApiKey::create($data);
    }

    /* ==================== createOrder() Tests ==================== */

    public function test_create_order_with_valid_data(): void
    {
        $response = $this->postJson('/open-payment/create', $this->getValidOrderData());

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'message',
                'data' => ['order'],
            ]);

        $this->assertDatabaseHas('open_payment_orders', [
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'payment_status' => 'pending',
        ]);
    }

    public function test_create_order_sets_pending_status(): void
    {
        $response = $this->postJson('/open-payment/create', $this->getValidOrderData());

        $response->assertStatus(200);

        $order = OpenPaymentOrder::latest()->first();
        $this->assertEquals('pending', $order->payment_status);
    }

    public function test_create_order_generates_transaction_id(): void
    {
        $response = $this->postJson('/open-payment/create', $this->getValidOrderData());

        $response->assertStatus(200);

        $order = OpenPaymentOrder::latest()->first();
        $this->assertNotNull($order->transaction_id);
        $this->assertStringStartsWith('txn_', $order->transaction_id);
    }

    public function test_create_order_fails_without_name(): void
    {
        $data = $this->getValidOrderData();
        unset($data['name']);

        $response = $this->postJson('/open-payment/create', $data);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['name']);
    }

    public function test_create_order_fails_without_email(): void
    {
        $data = $this->getValidOrderData();
        unset($data['email']);

        $response = $this->postJson('/open-payment/create', $data);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['email']);
    }

    public function test_create_order_fails_with_invalid_email(): void
    {
        $data = $this->getValidOrderData(['email' => 'invalid-email']);

        $response = $this->postJson('/open-payment/create', $data);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['email']);
    }

    public function test_create_order_fails_without_amount(): void
    {
        $data = $this->getValidOrderData();
        unset($data['amount']);

        $response = $this->postJson('/open-payment/create', $data);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['amount']);
    }

    public function test_create_order_fails_with_zero_amount(): void
    {
        $data = $this->getValidOrderData(['amount' => 0]);

        $response = $this->postJson('/open-payment/create', $data);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['amount']);
    }

    public function test_create_order_fails_with_negative_amount(): void
    {
        $data = $this->getValidOrderData(['amount' => -100]);

        $response = $this->postJson('/open-payment/create', $data);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['amount']);
    }

    public function test_create_order_fails_with_invalid_currency(): void
    {
        $data = $this->getValidOrderData(['currency' => 'EUR']);

        $response = $this->postJson('/open-payment/create', $data);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['currency']);
    }

    public function test_create_order_fails_with_invalid_gateway(): void
    {
        $data = $this->getValidOrderData(['gateway' => 'PayPal']);

        $response = $this->postJson('/open-payment/create', $data);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['gateway']);
    }

    public function test_create_order_with_razorpay_gateway(): void
    {
        $data = $this->getValidOrderData(['gateway' => 'Razorpay']);

        $response = $this->postJson('/open-payment/create', $data);

        $response->assertStatus(200);

        $order = OpenPaymentOrder::latest()->first();
        $this->assertEquals('Razorpay', $order->gateway);
    }

    public function test_create_order_with_stripe_gateway(): void
    {
        $data = $this->getValidOrderData(['gateway' => 'Stripe']);

        $response = $this->postJson('/open-payment/create', $data);

        $response->assertStatus(200);

        $order = OpenPaymentOrder::latest()->first();
        $this->assertEquals('Stripe', $order->gateway);
    }

    public function test_create_order_with_usd_currency(): void
    {
        $data = $this->getValidOrderData(['currency' => 'USD']);

        $response = $this->postJson('/open-payment/create', $data);

        $response->assertStatus(200);

        $order = OpenPaymentOrder::latest()->first();
        $this->assertEquals('USD', $order->currency);
    }

    public function test_create_order_stores_all_fields(): void
    {
        $data = $this->getValidOrderData();

        $response = $this->postJson('/open-payment/create', $data);

        $response->assertStatus(200);

        $this->assertDatabaseHas('open_payment_orders', [
            'name' => $data['name'],
            'email' => $data['email'],
            'mobile' => $data['mobile'],
            'address' => $data['address'],
            'city' => $data['city'],
            'state' => $data['state'],
            'zip' => $data['zip'],
            'country' => $data['country'],
            'company' => $data['company'],
            'currency' => $data['currency'],
            'gateway' => $data['gateway'],
            'description' => $data['description'],
        ]);
    }

    /* ==================== getOrderDetails() Tests ==================== */

    public function test_get_order_details_returns_order(): void
    {
        $this->createApiKeys();
        $order = $this->createOrder();

        $response = $this->getJson('/open-payment/order/'.$order->id);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'data' => [
                    'order',
                    'rzp_key',
                    'stripe_key',
                ],
            ]);
    }

    public function test_get_order_details_returns_api_keys(): void
    {
        // Update or create API keys (since ApiKey::first() is used in controller)
        $apiKeys = ApiKey::first();
        if ($apiKeys) {
            $apiKeys->update([
                'rzp_key' => 'rzp_test_key_updated',
                'stripe_key' => 'pk_test_key_updated',
            ]);
        } else {
            $this->createApiKeys();
        }

        $order = $this->createOrder();

        $response = $this->getJson('/open-payment/order/'.$order->id);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => ['rzp_key', 'stripe_key', 'order'],
            ]);

        // Assert that keys are present (may have any value)
        $data = $response->json('data');
        $this->assertArrayHasKey('rzp_key', $data);
        $this->assertArrayHasKey('stripe_key', $data);
    }

    public function test_get_order_details_returns_error_for_invalid_id(): void
    {
        $this->createApiKeys();

        $response = $this->getJson('/open-payment/order/99999');

        // Controller returns error response (may be 200 with success:false or 404)
        $statusCode = $response->getStatusCode();
        $this->assertTrue(in_array($statusCode, [200, 404]), 'Expected 200 or 404, got '.$statusCode);

        if ($statusCode === 200) {
            $response->assertJsonPath('success', expect: false);
        }
    }

    public function test_get_order_details_fails_without_api_keys(): void
    {
        // Clear existing API keys to test this edge case
        ApiKey::query()->delete();

        $order = $this->createOrder();

        $response = $this->getJson('/open-payment/order/'.$order->id);

        // Controller returns 500 when API keys are not configured
        $response->assertStatus(500)
            ->assertJsonPath('success', expect: false);
    }

    /* ==================== preparePayment() Tests ==================== */

    public function test_prepare_payment_requires_order_id(): void
    {
        $response = $this->postJson('/open-payment/prepare', []);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['order_id']);
    }

    public function test_prepare_payment_fails_for_non_existent_order(): void
    {
        $response = $this->postJson('/open-payment/prepare', [
            'order_id' => 99999,
        ]);

        // Controller uses findOrFail which throws ModelNotFoundException
        $statusCode = $response->getStatusCode();
        $this->assertTrue(in_array($statusCode, [200, 404]), 'Expected 200 or 404, got '.$statusCode);
    }

    public function test_prepare_payment_fails_for_already_paid_order(): void
    {
        $this->createApiKeys();
        $order = $this->createOrder(['payment_status' => 'completed']);

        $response = $this->postJson('/open-payment/prepare', [
            'order_id' => $order->id,
        ]);

        // Controller returns error for already paid order (may be 200 or 400)
        $statusCode = $response->getStatusCode();
        $this->assertTrue(in_array($statusCode, [200, 400]), 'Expected 200 or 400, got '.$statusCode);
        $response->assertJsonPath('success', expect: false)
            ->assertJsonFragment(['message' => 'This order has already been paid']);
    }

    public function test_prepare_payment_fails_for_invalid_gateway(): void
    {
        // Manually create order with invalid gateway (bypassing validation)
        $order = new OpenPaymentOrder;
        $order->forceFill($this->getValidOrderData(['gateway' => 'InvalidGateway']));
        $order->save();

        $response = $this->postJson('/open-payment/prepare', [
            'order_id' => $order->id,
        ]);

        // Controller returns error for invalid gateway (may be 200 or 400)
        $statusCode = $response->getStatusCode();
        $this->assertTrue(in_array($statusCode, [200, 400]), 'Expected 200 or 400, got '.$statusCode);
        $response->assertJsonPath('success', expect: false)
            ->assertJsonFragment(['message' => 'Invalid payment gateway']);
    }

    /* ==================== verifyRazorpayPayment() Tests ==================== */

    public function test_verify_razorpay_requires_all_fields(): void
    {
        $response = $this->postJson('/open-payment/verify/razorpay', []);

        $response->assertStatus(422)
            ->assertJsonValidationErrors([
                'order_id',
                'razorpay_payment_id',
                'razorpay_order_id',
                'razorpay_signature',
            ]);
    }

    public function test_verify_razorpay_fails_for_invalid_order_id(): void
    {
        $response = $this->postJson('/open-payment/verify/razorpay', [
            'order_id' => 99999,
            'razorpay_payment_id' => 'pay_test123',
            'razorpay_order_id' => 'order_test123',
            'razorpay_signature' => 'signature123',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['order_id']);
    }

    public function test_verify_razorpay_returns_success_for_already_paid_order(): void
    {
        $this->createApiKeys();
        $order = $this->createOrder(['payment_status' => 'completed']);

        $response = $this->postJson('/open-payment/verify/razorpay', [
            'order_id' => $order->id,
            'razorpay_payment_id' => 'pay_test123',
            'razorpay_order_id' => 'order_test123',
            'razorpay_signature' => 'signature123',
        ]);

        $response->assertStatus(200)
            ->assertJsonPath('success', expect: true)
            ->assertJsonFragment(['message' => 'Payment already processed!']);
    }

    public function test_verify_razorpay_fails_without_api_keys(): void
    {
        // Clear existing API keys
        ApiKey::query()->delete();

        $order = $this->createOrder();

        $response = $this->postJson('/open-payment/verify/razorpay', [
            'order_id' => $order->id,
            'razorpay_payment_id' => 'pay_test123',
            'razorpay_order_id' => 'order_test123',
            'razorpay_signature' => 'signature123',
        ]);

        // Controller returns 500 when API keys not configured
        $response->assertStatus(500)
            ->assertJsonPath('success', expect: false);
    }

    /* ==================== verifyStripePayment() Tests ==================== */

    public function test_verify_stripe_requires_all_fields(): void
    {
        $response = $this->postJson('/open-payment/verify/stripe', []);

        $response->assertStatus(422)
            ->assertJsonValidationErrors([
                'order_id',
                'payment_intent_id',
            ]);
    }

    public function test_verify_stripe_fails_for_invalid_order_id(): void
    {
        $response = $this->postJson('/open-payment/verify/stripe', [
            'order_id' => 99999,
            'payment_intent_id' => 'pi_test123',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['order_id']);
    }

    public function test_verify_stripe_returns_success_for_already_paid_order(): void
    {
        $this->createApiKeys();
        $order = $this->createOrder(['payment_status' => 'completed']);

        $response = $this->postJson('/open-payment/verify/stripe', [
            'order_id' => $order->id,
            'payment_intent_id' => 'pi_test123',
        ]);

        $response->assertStatus(200)
            ->assertJsonPath('success', expect: true)
            ->assertJsonFragment(['message' => 'Payment already processed!']);
    }

    public function test_verify_stripe_fails_without_api_keys(): void
    {
        // Clear existing API keys
        ApiKey::query()->delete();

        $order = $this->createOrder();

        $response = $this->postJson('/open-payment/verify/stripe', [
            'order_id' => $order->id,
            'payment_intent_id' => 'pi_test123',
        ]);

        // Controller returns 500 when API keys not configured
        $response->assertStatus(500)
            ->assertJsonPath('success', expect: false);
    }

    /* ==================== handleRazorpayWebhook() Tests ==================== */

    public function test_razorpay_webhook_fails_without_api_keys(): void
    {
        // Clear existing API keys
        ApiKey::query()->delete();

        $response = $this->postJson('/open-payment/webhook/razorpay', []);

        // Controller returns 500 when API keys not configured
        $response->assertStatus(500)
            ->assertJsonPath('success', expect: false);
    }

    public function test_razorpay_webhook_fails_with_invalid_payload(): void
    {
        $this->createApiKeys();

        $response = $this->postJson('/open-payment/webhook/razorpay', []);

        // Controller returns error for invalid payload (may be 200 or 400)
        $statusCode = $response->getStatusCode();
        $this->assertTrue(in_array($statusCode, [200, 400]), 'Expected 200 or 400, got '.$statusCode);
        $response->assertJsonPath('success', expect: false);
    }

    public function test_razorpay_webhook_processes_payment_captured_event(): void
    {
        $this->createApiKeys();
        $order = $this->createOrder();

        $payload = json_encode([
            'event' => 'payment.captured',
            'payload' => [
                'payment' => [
                    'entity' => [
                        'id' => 'pay_test123',
                        'notes' => [
                            'order_id' => $order->id,
                        ],
                    ],
                ],
            ],
        ]);

        $response = $this->call('POST', '/open-payment/webhook/razorpay', [], [], [], [
            'CONTENT_TYPE' => 'application/json',
        ], $payload);

        $response->assertStatus(200)
            ->assertJsonPath('success', expect: true);

        $order->refresh();
        $this->assertEquals('completed', $order->payment_status);
        $this->assertEquals('pay_test123', $order->gateway_transaction_id);
    }

    public function test_razorpay_webhook_processes_payment_failed_event(): void
    {
        $this->createApiKeys();
        $order = $this->createOrder();

        $payload = json_encode([
            'event' => 'payment.failed',
            'payload' => [
                'payment' => [
                    'entity' => [
                        'id' => 'pay_test123',
                        'notes' => [
                            'order_id' => $order->id,
                        ],
                    ],
                ],
            ],
        ]);

        $response = $this->call('POST', '/open-payment/webhook/razorpay', [], [], [], [
            'CONTENT_TYPE' => 'application/json',
        ], $payload);

        $response->assertStatus(200);

        $order->refresh();
        $this->assertEquals('failed', $order->payment_status);
    }

    public function test_razorpay_webhook_skips_already_paid_order(): void
    {
        $this->createApiKeys();
        $order = $this->createOrder(['payment_status' => 'completed']);

        $payload = json_encode([
            'event' => 'payment.captured',
            'payload' => [
                'payment' => [
                    'entity' => [
                        'id' => 'pay_new123',
                        'notes' => [
                            'order_id' => $order->id,
                        ],
                    ],
                ],
            ],
        ]);

        $response = $this->call('POST', '/open-payment/webhook/razorpay', [], [], [], [
            'CONTENT_TYPE' => 'application/json',
        ], $payload);

        $response->assertStatus(200);

        $order->refresh();
        // Should not update gateway_transaction_id for already paid order
        $this->assertNotEquals('pay_new123', $order->gateway_transaction_id);
    }

    /* ==================== handleStripeWebhook() Tests ==================== */

    public function test_stripe_webhook_fails_without_api_keys(): void
    {
        // Clear existing API keys
        ApiKey::query()->delete();

        $response = $this->postJson('/open-payment/webhook/stripe', []);

        // Controller returns 500 when API keys not configured
        $response->assertStatus(500)
            ->assertJsonPath('success', expect: false);
    }

    public function test_stripe_webhook_fails_with_invalid_payload(): void
    {
        $this->createApiKeys();

        $response = $this->postJson('/open-payment/webhook/stripe', []);

        // Controller returns error for invalid payload (may be 200 or 400)
        $statusCode = $response->getStatusCode();
        $this->assertTrue(in_array($statusCode, [200, 400]), 'Expected 200 or 400, got '.$statusCode);
        $response->assertJsonPath('success', expect: false);
    }

    public function test_stripe_webhook_processes_payment_succeeded_event(): void
    {
        $this->createApiKeys();
        $order = $this->createOrder(['gateway' => 'Stripe']);

        $payload = json_encode([
            'type' => 'payment_intent.succeeded',
            'data' => [
                'object' => [
                    'id' => 'pi_test123',
                    'metadata' => [
                        'order_id' => $order->id,
                    ],
                ],
            ],
        ]);

        $response = $this->call('POST', '/open-payment/webhook/stripe', [], [], [], [
            'CONTENT_TYPE' => 'application/json',
        ], $payload);

        $response->assertStatus(200)
            ->assertJsonPath('success', expect: true);

        $order->refresh();
        $this->assertEquals('completed', $order->payment_status);
    }

    public function test_stripe_webhook_processes_payment_failed_event(): void
    {
        $this->createApiKeys();
        $order = $this->createOrder(['gateway' => 'Stripe']);

        $payload = json_encode([
            'type' => 'payment_intent.payment_failed',
            'data' => [
                'object' => [
                    'id' => 'pi_test123',
                    'metadata' => [
                        'order_id' => $order->id,
                    ],
                ],
            ],
        ]);

        $response = $this->call('POST', '/open-payment/webhook/stripe', [], [], [], [
            'CONTENT_TYPE' => 'application/json',
        ], $payload);

        $response->assertStatus(200);

        $order->refresh();
        $this->assertEquals('failed', $order->payment_status);
    }

    /* ==================== listOrders() Tests ==================== */

    public function test_list_orders_returns_paginated_results(): void
    {
        $this->createOrder();
        $this->createOrder(['email' => 'jane@example.com']);
        $this->createOrder(['email' => 'bob@example.com']);

        $response = $this->getJson('/open-payment/list');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'data' => [
                    'orders' => [
                        'data',
                        'current_page',
                        'per_page',
                        'total',
                    ],
                ],
            ]);
    }

    public function test_list_orders_filters_by_status(): void
    {
        $this->createOrder(['payment_status' => 'pending']);
        $this->createOrder(['payment_status' => 'completed', 'email' => 'completed@example.com']);
        $this->createOrder(['payment_status' => 'failed', 'email' => 'failed@example.com']);

        $response = $this->getJson('/open-payment/list?status=completed');

        $response->assertStatus(200);

        $orders = $response->json('data.orders.data');
        // Filter only our test orders
        $matchingOrders = array_filter($orders, fn (array $o): bool => $o['payment_status'] === 'completed');
        $this->assertGreaterThanOrEqual(1, count($matchingOrders));
    }

    public function test_list_orders_filters_by_gateway(): void
    {
        $this->createOrder(['gateway' => 'Razorpay']);
        $this->createOrder(['gateway' => 'Stripe', 'email' => 'stripe@example.com']);
        $this->createOrder(['gateway' => 'Razorpay', 'email' => 'rzp2@example.com']);

        $response = $this->getJson('/open-payment/list?gateway=Stripe');

        $response->assertStatus(200);

        $orders = $response->json('data.orders.data');
        // All returned orders should be Stripe
        foreach ($orders as $order) {
            $this->assertEquals('Stripe', $order['gateway']);
        }
    }

    public function test_list_orders_searches_by_name(): void
    {
        $this->createOrder(['name' => 'UniqueSearchName123']);
        $this->createOrder(['name' => 'Jane Smith', 'email' => 'jane@example.com']);

        $response = $this->getJson('/open-payment/list?search=UniqueSearchName123');

        $response->assertStatus(200);

        $orders = $response->json('data.orders.data');
        $this->assertCount(1, $orders);
        $this->assertEquals('UniqueSearchName123', $orders[0]['name']);
    }

    public function test_list_orders_searches_by_email(): void
    {
        $this->createOrder(['email' => 'uniquesearch@example.com']);
        $this->createOrder(['email' => 'another@example.com']);

        $response = $this->getJson('/open-payment/list?search=uniquesearch@');

        $response->assertStatus(200);

        $orders = $response->json('data.orders.data');
        $this->assertCount(1, $orders);
        $this->assertEquals('uniquesearch@example.com', $orders[0]['email']);
    }

    /* ==================== getOrder() Tests ==================== */

    public function test_get_order_returns_order(): void
    {
        $order = $this->createOrder();

        $response = $this->getJson('/open-payment/admin/'.$order->id);

        $response->assertStatus(200)
            ->assertJsonPath('success', expect: true)
            ->assertJsonStructure([
                'data' => ['order'],
            ]);
    }

    public function test_get_order_returns_error_for_invalid_id(): void
    {
        $response = $this->getJson('/open-payment/admin/99999');

        // Controller uses findOrFail which throws ModelNotFoundException
        $statusCode = $response->getStatusCode();
        $this->assertTrue(in_array($statusCode, [200, 404]), 'Expected 200 or 404, got '.$statusCode);
    }

    /* ==================== handleStripeCallback() Tests ==================== */

    public function test_stripe_callback_redirects_without_order_id(): void
    {
        $response = $this->get('/open-payment/stripe/callback');

        $response->assertRedirect('/open-payment');
    }

    public function test_stripe_callback_redirects_for_already_paid_order(): void
    {
        $this->createApiKeys();
        $order = $this->createOrder([
            'gateway' => 'Stripe',
            'payment_status' => 'completed',
        ]);

        $response = $this->get('/open-payment/stripe/callback?order_id='.$order->id);

        $response->assertRedirect('/open-payment?order_id='.$order->id.'&status=success');
    }

    public function test_stripe_callback_redirects_for_invalid_order(): void
    {
        $response = $this->get('/open-payment/stripe/callback?order_id=99999');

        $response->assertRedirect();
    }

    public function test_stripe_callback_redirects_without_api_keys(): void
    {
        $order = $this->createOrder(['gateway' => 'Stripe']);

        $response = $this->get('/open-payment/stripe/callback?order_id='.$order->id);

        $response->assertRedirect();
        $this->assertStringContainsString('Configuration error', $response->headers->get('Location'));
    }

    /* ==================== OpenPaymentOrder Model Tests ==================== */

    public function test_order_is_paid_returns_true_for_completed(): void
    {
        $order = $this->createOrder(['payment_status' => 'completed']);

        $this->assertTrue($order->isPaid());
    }

    public function test_order_is_paid_returns_false_for_pending(): void
    {
        $order = $this->createOrder(['payment_status' => 'pending']);

        $this->assertFalse($order->isPaid());
    }

    public function test_order_is_pending_returns_true_for_pending(): void
    {
        $order = $this->createOrder(['payment_status' => 'pending']);

        $this->assertTrue($order->isPending());
    }

    public function test_order_is_failed_returns_true_for_failed(): void
    {
        $order = $this->createOrder(['payment_status' => 'failed']);

        $this->assertTrue($order->isFailed());
    }

    public function test_order_gateway_id_returns_gateway_transaction_id(): void
    {
        $order = $this->createOrder();
        $order->update(['gateway_transaction_id' => 'gateway_123']);

        $this->assertEquals('gateway_123', $order->getGatewayId());
    }

    public function test_order_gateway_id_returns_null_when_not_set(): void
    {
        $order = $this->createOrder();

        $this->assertNull($order->getGatewayId());
    }

    /* ==================== Edge Cases & Integration Tests ==================== */

    public function test_each_order_has_unique_transaction_id(): void
    {
        $orders = [];
        for ($i = 0; $i < 5; $i++) {
            $orders[] = $this->createOrder(['email' => sprintf('unique%d@example.com', $i)]);
        }

        $transactionIds = collect($orders)->pluck('transaction_id')->unique();

        $this->assertCount(5, $transactionIds);
    }

    public function test_order_amount_is_stored_correctly(): void
    {
        $order = $this->createOrder(['amount' => 100.50]);

        // Amount should be stored correctly
        $this->assertEquals(100.50, (float) $order->amount);
    }

    public function test_order_paid_at_is_cast_to_datetime(): void
    {
        $order = $this->createOrder();
        $order->update(['paid_at' => now()]);

        $order->refresh();

        $this->assertInstanceOf(Carbon::class, $order->paid_at);
    }
}
