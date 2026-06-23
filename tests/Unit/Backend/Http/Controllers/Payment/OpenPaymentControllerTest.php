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

        $response->assertStatus(422);
        $errors = $response->json('errors');

        // Verify specific messages per the OpenPaymentRequest::messages()
        $this->assertSame('Please enter your name.', $errors['name'][0]);
        $this->assertSame('Please enter your email address.', $errors['email'][0]);
        $this->assertSame('Please enter your mobile number.', $errors['mobile'][0]);
        $this->assertSame('Please enter your address.', $errors['address'][0]);
        $this->assertSame('Please enter the payment amount.', $errors['amount'][0]);
        $this->assertSame('Please select a currency.', $errors['currency'][0]);
        $this->assertSame('Please select a payment gateway.', $errors['gateway'][0]);
    }

    public function test_create_invalid_email_gives_specific_message(): void
    {
        $data = array_merge($this->validPayload(), ['email' => 'not-an-email']);
        $response = $this->postJson('/pay/create', $data);

        $response->assertStatus(422);
        $this->assertSame('Please enter a valid email address.', $response->json('errors.email.0'));
    }

    public function test_create_amount_zero_gives_amount_error(): void
    {
        $data = array_merge($this->validPayload(), ['amount' => 0]);
        $response = $this->postJson('/pay/create', $data);

        $response->assertStatus(422);
        $this->assertArrayHasKey('amount', $response->json('errors'));
    }

    public function test_create_negative_amount_gives_amount_error(): void
    {
        $data = array_merge($this->validPayload(), ['amount' => -50]);
        $response = $this->postJson('/pay/create', $data);

        $response->assertStatus(422);
        $this->assertArrayHasKey('amount', $response->json('errors'));
    }

    public function test_create_unsupported_currency_eur_gives_currency_error(): void
    {
        $data = array_merge($this->validPayload(), ['currency' => 'EUR']);
        $response = $this->postJson('/pay/create', $data);

        $response->assertStatus(422);
        $this->assertArrayHasKey('currency', $response->json('errors'));
    }

    public function test_create_unsupported_gateway_paypal_gives_gateway_error(): void
    {
        $data = array_merge($this->validPayload(), ['gateway' => 'PayPal']);
        $response = $this->postJson('/pay/create', $data);

        $response->assertStatus(422);
        $this->assertArrayHasKey('gateway', $response->json('errors'));
    }

    public function test_create_mobile_7_chars_below_minimum_gives_mobile_error(): void
    {
        $data = array_merge($this->validPayload(), ['mobile' => '1234567']); // 7 < min:8
        $response = $this->postJson('/pay/create', $data);

        $response->assertStatus(422);
        $this->assertSame('Mobile number must be at least 8 characters.', $response->json('errors.mobile.0'));
    }

    public function test_create_name_over_100_chars_gives_name_error(): void
    {
        $data = array_merge($this->validPayload(), ['name' => str_repeat('a', 101)]);
        $response = $this->postJson('/pay/create', $data);

        $response->assertStatus(422);
        $this->assertSame('Name cannot exceed 100 characters.', $response->json('errors.name.0'));
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
}
