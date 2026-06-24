<?php

namespace Tests\Unit\Backend\Http\Controllers\Order;

use App\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\DBTestCase;

class ExtendedBaseInvoiceControllerTest extends DBTestCase
{
    use DatabaseTransactions;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutMiddleware(\App\Http\Middleware\Install::class);
    }

    // =========================================================================
    // POST /newMultiplePayment/receive/{clientid} — postNewMultiplePayment
    // =========================================================================

    public function test_post_new_multiple_payment_missing_payment_date_returns_422(): void
    {
        $this->getLoggedInUser('admin');
        $client = User::factory()->create(['role' => 'user']);
        $response = $this->postJson("/newMultiplePayment/receive/{$client->id}", [
            'payment_method' => 'cash',
            'totalAmt' => 100,
        ]);
        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['payment_date']);
    }

    public function test_post_new_multiple_payment_missing_payment_method_returns_422(): void
    {
        $this->getLoggedInUser('admin');
        $client = User::factory()->create(['role' => 'user']);
        $response = $this->postJson("/newMultiplePayment/receive/{$client->id}", [
            'payment_date' => '2025-01-15',
            'totalAmt' => 100,
        ]);
        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['payment_method']);
    }

    public function test_post_new_multiple_payment_zero_amount_returns_422(): void
    {
        $this->getLoggedInUser('admin');
        $client = User::factory()->create(['role' => 'user']);
        $response = $this->postJson("/newMultiplePayment/receive/{$client->id}", [
            'payment_date' => '2025-01-15',
            'payment_method' => 'cash',
            'totalAmt' => 0,
        ]);
        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['totalAmt']);
    }

    public function test_post_new_multiple_payment_with_no_invoices_returns_200(): void
    {
        $this->getLoggedInUser('admin');
        $client = User::factory()->create(['role' => 'user']);
        $response = $this->postJson("/newMultiplePayment/receive/{$client->id}", [
            'payment_date' => '2025-01-15',
            'payment_method' => 'cash',
            'totalAmt' => 100,
            'invoiceChecked' => [],
        ]);
        $response->assertStatus(200);
        $response->assertJson(['success' => true]);
    }

    // =========================================================================
    // POST /newMultiplePayment/update/{clientid} — updateNewMultiplePayment
    // =========================================================================

    public function test_update_new_multiple_payment_missing_fields_returns_422(): void
    {
        $this->getLoggedInUser('admin');
        $client = User::factory()->create(['role' => 'user']);
        $response = $this->postJson("/newMultiplePayment/update/{$client->id}", []);
        $response->assertStatus(422);
    }
}
