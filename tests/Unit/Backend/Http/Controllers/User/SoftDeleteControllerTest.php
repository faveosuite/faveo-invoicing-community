<?php

declare(strict_types=1);

namespace Tests\Unit\Backend\Http\Controllers\User;

use Tests\DBTestCase;

class SoftDeleteControllerTest extends DBTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutMiddleware(\App\Http\Middleware\Install::class);
    }

    // --- GET /soft-delete ---

    public function test_admin_gets_200(): void
    {
        $this->getLoggedInUser('admin');
        $this->getJson('/soft-delete')->assertStatus(200);
    }

    public function test_client_is_redirected_302(): void
    {
        $this->getLoggedInUser('user');
        $this->getJson('/soft-delete')->assertStatus(302);
    }

    public function test_guest_gets_401(): void
    {
        $this->getJson('/soft-delete')->assertStatus(401);
    }

    public function test_list_has_success_flag_and_paginated_structure(): void
    {
        $this->getLoggedInUser('admin');
        $response = $this->getJson('/soft-delete');

        $response->assertStatus(200);
        $this->assertTrue($response->json('success'));
        $this->assertIsArray($response->json('data.data'));
    }

    // --- POST /user/restore/{id} ---

    public function test_restore_nonexistent_user_returns_404(): void
    {
        $this->getLoggedInUser('admin');
        $this->postJson('/user/restore/999999999')->assertStatus(404);
    }

    public function test_restore_unauthenticated_returns_401(): void
    {
        $this->postJson('/user/restore/1')->assertStatus(401);
    }

    // --- DELETE /permanent-delete-client ---

    public function test_permanent_delete_nonexistent_returns_400_with_error_shape(): void
    {
        $this->getLoggedInUser('admin');
        $response = $this->deleteJson('/permanent-delete-client', ['id' => 999999999]);

        $response->assertStatus(400);
        $this->assertFalse($response->json('success'));
        $this->assertNotEmpty($response->json('message'));
    }

    public function test_permanent_delete_unauthenticated_returns_401(): void
    {
        $this->deleteJson('/permanent-delete-client', ['id' => 1])->assertStatus(401);
    }

    public function test_permanent_delete_removes_the_users_money_and_license_records(): void
    {
        $this->getLoggedInUser('admin');
        $user = \App\User::factory()->create(['role' => 'user']);
        $order = \App\Model\Order\Order::factory()->create(['client' => $user->id]);
        \App\License\Models\License::create(['product_id' => $order->product, 'license_code' => 'P'.$order->number, 'license_order_number' => $order->number]);
        $invoice = \App\Model\Order\Invoice::factory()->create(['user_id' => $user->id]);
        \App\Model\Order\InvoiceTaxLine::create(['invoice_id' => $invoice->id, 'label' => 'GST', 'rate' => 18, 'compound' => 0, 'amount' => 1]);
        $payment = \App\Model\Order\Payment::create(['user_id' => $user->id, 'amount' => 10, 'payment_method' => 'Credit Balance', 'payment_status' => 'success']);
        \App\Model\Order\CreditTransaction::create(['user_id' => $user->id, 'currency' => 'USD', 'amount' => 5, 'type' => \App\Model\Order\CreditTransaction::TYPE_MANUAL_GRANT]);
        \App\Model\Order\UserCreditBalance::create(['user_id' => $user->id, 'currency' => 'USD', 'balance' => 5]);
        $user->delete();

        $this->deleteJson('/permanent-delete-client', ['user_ids' => [$user->id]])->assertOk();

        $this->assertDatabaseMissing('licenses', ['license_order_number' => $order->number]);
        $this->assertDatabaseMissing('invoice_tax_lines', ['invoice_id' => $invoice->id]);
        $this->assertDatabaseMissing('payments', ['id' => $payment->id]);
        $this->assertDatabaseMissing('credit_transactions', ['user_id' => $user->id]);
        $this->assertDatabaseMissing('user_credit_balances', ['user_id' => $user->id]);
    }
}
