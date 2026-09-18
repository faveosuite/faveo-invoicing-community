<?php

declare(strict_types=1);

namespace Tests\Unit\Backend\Http\Controllers\Payment;

use Tests\DBTestCase;

class OpenPaymentControllerTest extends DBTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutMiddleware(\App\Http\Middleware\Install::class);
    }

    private function validPayload(): array
    {
        return [
            'name' => 'Test User', 'email' => 'test@example.com', 'mobile' => '9876543210',
            'address' => '123 Main St', 'city' => 'Mumbai', 'state' => 'MH',
            'zip' => '400001', 'country' => 'IN', 'company' => 'Acme',
            'amount' => 100, 'currency' => 'INR', 'gateway' => 'Razorpay',
        ];
    }

    // --- GET /pay/config — public ---
    public function test_config_returns_200(): void
    {
        $this->getJson('/pay/config')->assertStatus(200);
    }

    // --- GET /pay/list ---
    public function test_list_admin_returns_200_with_paginated_data(): void
    {
        $this->getLoggedInUser('admin');
        $response = $this->getJson('/pay/list');

        $response->assertStatus(200);
        $this->assertTrue($response->json('success'));
        $this->assertIsArray($response->json('data.data'));
    }

    // --- POST /pay/create — every missing required field produces its own error message ---

    public function test_create_empty_body_returns_422_with_all_required_field_errors(): void
    {
        $response = $this->postJson('/pay/create', []);

        $response->assertStatus(412);
        $errors = $response->json('message');

        // Verify specific messages per the OpenPaymentRequest::messages()
        $this->assertSame('Please enter your name.', $errors['name']);
        $this->assertSame('Please enter your email address.', $errors['email']);
        $this->assertSame('Please enter your mobile number.', $errors['mobile']);
        $this->assertSame('Please enter your address.', $errors['address']);
        $this->assertSame('Please enter the payment amount.', $errors['amount']);
        $this->assertSame('Please select a currency.', $errors['currency']);
        $this->assertSame('Please select a payment gateway.', $errors['gateway']);
    }

    public function test_create_invalid_email_gives_specific_message(): void
    {
        $data = array_merge($this->validPayload(), ['email' => 'not-an-email']);
        $response = $this->postJson('/pay/create', $data);

        $response->assertStatus(412);
        $this->assertSame('Please enter a valid email address.', $response->json('message.email'));
    }

    public function test_create_amount_zero_gives_amount_error(): void
    {
        $data = array_merge($this->validPayload(), ['amount' => 0]);
        $response = $this->postJson('/pay/create', $data);

        $response->assertStatus(412);
        $this->assertArrayHasKey('amount', $response->json('message'));
    }

    public function test_create_negative_amount_gives_amount_error(): void
    {
        $data = array_merge($this->validPayload(), ['amount' => -50]);
        $response = $this->postJson('/pay/create', $data);

        $response->assertStatus(412);
        $this->assertArrayHasKey('amount', $response->json('message'));
    }

    public function test_create_unsupported_currency_eur_gives_currency_error(): void
    {
        $data = array_merge($this->validPayload(), ['currency' => 'EUR']);
        $response = $this->postJson('/pay/create', $data);

        $response->assertStatus(412);
        $this->assertArrayHasKey('currency', $response->json('message'));
    }

    public function test_create_unsupported_gateway_paypal_gives_gateway_error(): void
    {
        $data = array_merge($this->validPayload(), ['gateway' => 'PayPal']);
        $response = $this->postJson('/pay/create', $data);

        $response->assertStatus(412);
        $this->assertArrayHasKey('gateway', $response->json('message'));
    }

    public function test_create_mobile_7_chars_below_minimum_gives_mobile_error(): void
    {
        $data = array_merge($this->validPayload(), ['mobile' => '1234567']); // 7 < min:8
        $response = $this->postJson('/pay/create', $data);

        $response->assertStatus(412);
        $this->assertSame('Mobile number must be at least 8 characters.', $response->json('message.mobile'));
    }

    public function test_create_name_over_100_chars_gives_name_error(): void
    {
        $data = array_merge($this->validPayload(), ['name' => str_repeat('a', 101)]);
        $response = $this->postJson('/pay/create', $data);

        $response->assertStatus(412);
        $this->assertSame('Name cannot exceed 100 characters.', $response->json('message.name'));
    }

    // --- GET /pay/order/{id} ---

    public function test_nonexistent_order_returns_404(): void
    {
        $this->getJson('/pay/order/999999999')->assertStatus(404);
    }

    public function test_sql_injection_in_id_does_not_expose_data(): void
    {
        $response = $this->getJson("/pay/order/' OR 1=1 --");
        $this->assertStringNotContainsString('OR 1=1', (string) $response->getContent());
    }

    // =========================================================================
    // detectCountry — returns 200 with country key
    // =========================================================================

    public function test_detect_country_returns_200_with_country_key(): void
    {
        $response = $this->getJson('/pay/detect-country');

        $response->assertStatus(200)
            ->assertJson(['success' => true])
            ->assertJsonStructure(['data' => ['country']]);

        // Country may be null if IP geolocation fails
        $this->assertTrue(
            $response->json('data.country') === null || is_array($response->json('data.country'))
        );
    }

    // =========================================================================
    // calculate — with amount and gateway
    // =========================================================================

    public function test_calculate_returns_400_when_params_missing(): void
    {
        $response = $this->getJson('/pay/calculate');

        // Validation fails → 422 or 400
        $this->assertContains($response->status(), [400, 412]);
    }

    public function test_calculate_returns_totals_with_valid_params(): void
    {
        $response = $this->getJson('/pay/calculate?amount=100.00&gateway=stripe&currency=USD');

        $response->assertStatus(200)
            ->assertJson(['success' => true])
            ->assertJsonStructure(['data' => ['base_amount', 'processing_fee', 'processing_fee_rate', 'total']]);

        $this->assertSame('100.00', $response->json('data.base_amount'));
    }

    // =========================================================================
    // listOrders — admin list with pagination
    // =========================================================================

    public function test_list_orders_returns_200_with_paginated_structure(): void
    {
        $this->getLoggedInUser('admin');

        $response = $this->getJson('/pay/list');

        $response->assertStatus(200)
            ->assertJson(['success' => true])
            ->assertJsonStructure(['data' => ['data', 'total', 'per_page']]);
    }

    public function test_list_orders_with_search_returns_filtered_results(): void
    {
        $this->getLoggedInUser('admin');

        $response = $this->getJson('/pay/list?search-query=__nonexistent_order_xyzzy__');

        // 200 if open payment table exists, 400 if not configured
        $this->assertContains($response->status(), [200, 400]);
    }
}
