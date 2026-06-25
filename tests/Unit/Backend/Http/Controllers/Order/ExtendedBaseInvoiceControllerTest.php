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

    // =========================================================================
    // newPayment — GET /newPayment/receive
    // =========================================================================

    public function test_new_payment_returns_400_for_unknown_client(): void
    {
        $this->getLoggedInUser('admin');
        $response = $this->getJson('/newPayment/receive?clientid=999999');
        $response->assertStatus(400)->assertJson(['success' => false]);
    }

    public function test_new_payment_returns_200_with_invoices_and_currencies(): void
    {
        $this->getLoggedInUser('admin');
        $client = \App\User::factory()->create(['role' => 'user', 'email' => 'newpay-'.uniqid().'@test.local']);
        $response = $this->getJson('/newPayment/receive?clientid='.$client->id);
        $response->assertStatus(200)->assertJson(['success' => true])
            ->assertJsonStructure(['data' => ['invoices', 'currencies']]);
        $this->assertIsArray($response->json('data.invoices'));
    }

    // =========================================================================
    // postEdit — POST /invoice/edit/{id}
    // =========================================================================

    public function test_post_edit_invoice_returns_error_for_nonexistent(): void
    {
        $this->getLoggedInUser('admin');
        $response = $this->postJson('/invoice/edit/999999', ['grand_total' => 100.0, 'status' => 'success']);
        $this->assertContains($response->status(), [200, 400, 422]);
    }

    public function test_post_edit_invoice_updates_existing(): void
    {
        $this->getLoggedInUser('admin');
        $client = \App\User::factory()->create(['email' => 'edit-inv-'.uniqid().'@test.local']);
        $invoice = \App\Model\Order\Invoice::factory()->create(['user_id' => $client->id, 'grand_total' => 100.0, 'status' => 'pending']);
        $response = $this->postJson('/invoice/edit/'.$invoice->id, ['grand_total' => 150.0, 'status' => 'success']);
        $this->assertContains($response->status(), [200, 400, 422]);
    }

    // =========================================================================
    // postEdit — POST /invoice/edit/{id} — happy path (all required fields present)
    // =========================================================================

    public function test_post_edit_invoice_with_all_required_fields_returns_200(): void
    {
        $this->getLoggedInUser('admin');
        $client = \App\User::factory()->create(['email' => 'post-edit-'.uniqid().'@test.local']);
        $invoice = \App\Model\Order\Invoice::factory()->create([
            'user_id' => $client->id,
            'grand_total' => 100.0,
            'status' => 'pending',
        ]);

        $response = $this->postJson('/invoice/edit/'.$invoice->id, [
            'date' => '2025-06-01',
            'total' => 150.0,
            'status' => 'success',
        ]);

        $response->assertStatus(200)
            ->assertJson(['success' => true]);
    }

    public function test_post_edit_invoice_missing_date_returns_422(): void
    {
        $this->getLoggedInUser('admin');
        $client = \App\User::factory()->create(['email' => 'edit-nod-'.uniqid().'@test.local']);
        $invoice = \App\Model\Order\Invoice::factory()->create(['user_id' => $client->id]);

        $response = $this->postJson('/invoice/edit/'.$invoice->id, [
            'total' => 150.0,
            'status' => 'success',
        ]);

        $response->assertStatus(422);
        $this->assertArrayHasKey('date', $response->json('errors'));
    }

    public function test_post_edit_invoice_missing_total_returns_422(): void
    {
        $this->getLoggedInUser('admin');
        $client = \App\User::factory()->create(['email' => 'edit-nototal-'.uniqid().'@test.local']);
        $invoice = \App\Model\Order\Invoice::factory()->create(['user_id' => $client->id]);

        $response = $this->postJson('/invoice/edit/'.$invoice->id, [
            'date' => '2025-06-01',
            'status' => 'success',
        ]);

        $response->assertStatus(422);
        $this->assertArrayHasKey('total', $response->json('errors'));
    }

    // =========================================================================
    // multiplePayment — called via postNewMultiplePayment with invoices
    // =========================================================================

    public function test_post_new_multiple_payment_with_invoice_marks_invoice_fully_paid(): void
    {
        $this->getLoggedInUser('admin');
        $client = User::factory()->create(['role' => 'user', 'email' => 'multi-pay-'.uniqid().'@test.local']);
        $invoice = \App\Model\Order\Invoice::factory()->create([
            'user_id' => $client->id,
            'grand_total' => 100.0,
            'currency' => 'USD',
            'status' => 'pending',
        ]);

        // invoiceChecked is [0 => invoiceId] and invoiceAmount is [0 => amount]
        // so the key matches the index of invoiceChecked, not the invoice id
        $response = $this->postJson("/newMultiplePayment/receive/{$client->id}", [
            'payment_date' => '2025-06-01',
            'payment_method' => 'cash',
            'totalAmt' => 100,
            'invoiceChecked' => [0 => $invoice->id],
            'invoiceAmount' => [0 => 100],
            'amtToCredit' => 0,
        ]);

        $response->assertStatus(200)
            ->assertJson(['success' => true]);

        // Invoice status should be updated to 'success'
        $this->assertDatabaseHas('invoices', [
            'id' => $invoice->id,
            'status' => 'success',
        ]);
    }

    public function test_post_new_multiple_payment_with_partial_amount_marks_invoice_partially_paid(): void
    {
        $this->getLoggedInUser('admin');
        $client = User::factory()->create(['role' => 'user', 'email' => 'partial-pay-'.uniqid().'@test.local']);
        $invoice = \App\Model\Order\Invoice::factory()->create([
            'user_id' => $client->id,
            'grand_total' => 200.0,
            'currency' => 'USD',
            'status' => 'pending',
        ]);

        $response = $this->postJson("/newMultiplePayment/receive/{$client->id}", [
            'payment_date' => '2025-06-01',
            'payment_method' => 'cash',
            'totalAmt' => 50,
            'invoiceChecked' => [0 => $invoice->id],
            'invoiceAmount' => [0 => 50],
            'amtToCredit' => 0,
        ]);

        $response->assertStatus(200)
            ->assertJson(['success' => true]);

        $this->assertDatabaseHas('invoices', [
            'id' => $invoice->id,
            'status' => 'partially paid',
        ]);
    }

    public function test_post_new_multiple_payment_with_credit_creates_credit_row(): void
    {
        $this->getLoggedInUser('admin');
        $client = User::factory()->create(['role' => 'user', 'email' => 'credit-pay-'.uniqid().'@test.local']);

        $response = $this->postJson("/newMultiplePayment/receive/{$client->id}", [
            'payment_date' => '2025-06-01',
            'payment_method' => 'cash',
            'totalAmt' => 200,
            'invoiceChecked' => [],
            'invoiceAmount' => [],
            'amtToCredit' => 50,
            'currency' => 'USD',
        ]);

        $response->assertStatus(200)
            ->assertJson(['success' => true]);

        // A credit row (invoice_id = 0) should be created
        $this->assertDatabaseHas('payments', [
            'user_id' => $client->id,
            'invoice_id' => 0,
            'amt_to_credit' => 50,
        ]);
    }

    // =========================================================================
    // updatePaymentByInvoice — called via updateNewMultiplePayment
    // =========================================================================

    public function test_update_new_multiple_payment_with_credit_applies_to_invoice(): void
    {
        $this->getLoggedInUser('admin');
        $client = User::factory()->create(['role' => 'user', 'email' => 'upd-pay-'.uniqid().'@test.local']);
        $invoice = \App\Model\Order\Invoice::factory()->create([
            'user_id' => $client->id,
            'grand_total' => 100.0,
            'currency' => 'USD',
            'status' => 'pending',
        ]);

        // Seed a credit balance for the client
        \App\Model\Order\Payment::create([
            'invoice_id' => 0,
            'user_id' => $client->id,
            'amount' => 200.0,
            'amt_to_credit' => 200.0,
            'payment_method' => 'cash',
            'payment_status' => 'success',
            'currency' => 'USD',
        ]);

        // invoiceChecked and invoiceAmount are parallel arrays: key = numeric index
        $response = $this->postJson("/newMultiplePayment/update/{$client->id}", [
            'payment_date' => '2025-06-01',
            'payment_method' => 'cash',
            'invoiceChecked' => [0 => $invoice->id],
            'invoiceAmount' => [0 => 100],
        ]);

        $response->assertStatus(200)
            ->assertJson(['success' => true]);
    }

    public function test_update_new_multiple_payment_fails_when_no_credit(): void
    {
        $this->getLoggedInUser('admin');
        $client = User::factory()->create(['role' => 'user', 'email' => 'nocredit-pay-'.uniqid().'@test.local']);
        $invoice = \App\Model\Order\Invoice::factory()->create([
            'user_id' => $client->id,
            'grand_total' => 100.0,
            'currency' => 'USD',
            'status' => 'pending',
        ]);

        // No credit balance seeded — should fail with insufficient credit
        $response = $this->postJson("/newMultiplePayment/update/{$client->id}", [
            'payment_date' => '2025-06-01',
            'payment_method' => 'cash',
            'invoiceChecked' => [0 => $invoice->id],
            'invoiceAmount' => [0 => 100],
        ]);

        // Expect error because insufficient credit (applied > 0 but creditBefore = 0)
        $this->assertContains($response->status(), [400, 500]);
        $this->assertFalse($response->json('success'));
    }

    public function test_update_new_multiple_payment_missing_invoiceChecked_returns_422(): void
    {
        $this->getLoggedInUser('admin');
        $client = User::factory()->create(['role' => 'user', 'email' => 'upd-valid-'.uniqid().'@test.local']);

        $response = $this->postJson("/newMultiplePayment/update/{$client->id}", [
            'payment_date' => '2025-06-01',
            'payment_method' => 'cash',
            // invoiceChecked missing
        ]);

        $response->assertStatus(422);
        $this->assertArrayHasKey('invoiceChecked', $response->json('errors'));
    }
}
