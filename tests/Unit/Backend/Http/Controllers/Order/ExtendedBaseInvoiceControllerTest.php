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
        $response->assertStatus(412);
        $response->assertJsonValidationErrors(['payment_date'], 'message');
    }

    public function test_post_new_multiple_payment_missing_payment_method_returns_422(): void
    {
        $this->getLoggedInUser('admin');
        $client = User::factory()->create(['role' => 'user']);
        $response = $this->postJson("/newMultiplePayment/receive/{$client->id}", [
            'payment_date' => '2025-01-15',
            'totalAmt' => 100,
        ]);
        $response->assertStatus(412);
        $response->assertJsonValidationErrors(['payment_method'], 'message');
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
        $response->assertStatus(412);
        $response->assertJsonValidationErrors(['totalAmt'], 'message');
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
        $response->assertStatus(412);
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
    // postEdit — POST /invoice/edit/{id} — route is not available (no POST route)
    // =========================================================================

    public function test_post_edit_invoice_returns_405_as_route_not_available(): void
    {
        $this->getLoggedInUser('admin');
        $response = $this->postJson('/invoice/edit/999999', ['grand_total' => 100.0, 'status' => 'success']);
        $response->assertStatus(405);
    }

    public function test_post_edit_invoice_updates_existing_returns_405(): void
    {
        $this->getLoggedInUser('admin');
        $client = \App\User::factory()->create(['email' => 'edit-inv-'.uniqid().'@test.local']);
        $invoice = \App\Model\Order\Invoice::factory()->create(['user_id' => $client->id, 'grand_total' => 100.0, 'status' => 'pending']);
        $response = $this->postJson('/invoice/edit/'.$invoice->id, ['grand_total' => 150.0, 'status' => 'success']);
        $response->assertStatus(405);
    }

    public function test_post_edit_invoice_with_all_required_fields_returns_405(): void
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

        $response->assertStatus(405);
    }

    public function test_post_edit_invoice_missing_date_returns_405(): void
    {
        $this->getLoggedInUser('admin');
        $client = \App\User::factory()->create(['email' => 'edit-nod-'.uniqid().'@test.local']);
        $invoice = \App\Model\Order\Invoice::factory()->create(['user_id' => $client->id]);

        $response = $this->postJson('/invoice/edit/'.$invoice->id, [
            'total' => 150.0,
            'status' => 'success',
        ]);

        $response->assertStatus(405);
    }

    public function test_post_edit_invoice_missing_total_returns_405(): void
    {
        $this->getLoggedInUser('admin');
        $client = \App\User::factory()->create(['email' => 'edit-nototal-'.uniqid().'@test.local']);
        $invoice = \App\Model\Order\Invoice::factory()->create(['user_id' => $client->id]);

        $response = $this->postJson('/invoice/edit/'.$invoice->id, [
            'date' => '2025-06-01',
            'status' => 'success',
        ]);

        $response->assertStatus(405);
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
        // One payment for the money received, one allocation for where it went.
        $this->assertSame(1, \App\Model\Order\Payment::where('user_id', $client->id)->count());
        $this->assertDatabaseHas('payment_invoice', ['invoice_id' => $invoice->id, 'amount' => 100]);
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

    public function test_post_new_multiple_payment_banks_leftover_as_unapplied_payment(): void
    {
        $this->getLoggedInUser('admin');
        $client = User::factory()->create(['role' => 'user', 'email' => 'credit-pay-'.uniqid().'@test.local']);

        $response = $this->postJson("/newMultiplePayment/receive/{$client->id}", [
            'payment_date' => '2025-06-01',
            'payment_method' => 'cash',
            'totalAmt' => 200,
            'invoiceChecked' => [],
            'invoiceAmount' => [],
            'currency' => 'USD',
        ]);

        $response->assertStatus(200)
            ->assertJson(['success' => true]);

        // Nothing was allocated, so the whole receipt stays an unapplied
        // PAYMENT — real money arrived, and recording it as credit would say
        // we granted the client something instead.
        $this->assertSame(200.0, (new \App\Services\Payment\UnappliedPaymentService)->balance($client->id, 'USD'));
        $this->assertSame(0.0, (new \App\Services\Payment\CreditBalanceService)->balance($client->id, 'USD'));
        $this->assertSame(0, \App\Model\Order\CreditTransaction::where('user_id', $client->id)->count());
        $this->assertDatabaseHas('payments', [
            'user_id' => $client->id,
            'amount' => 200,
            'payment_method' => 'cash',
            'currency' => 'USD',
        ]);
        // Nothing claimed it, so it has no allocations at all.
        $this->assertSame(0, \App\Model\Order\PaymentInvoice::whereIn(
            'payment_id', \App\Model\Order\Payment::where('user_id', $client->id)->pluck('id')
        )->count());
    }

    // =========================================================================
    // POST /payments/{id}/apply — applyPaymentToInvoices
    // =========================================================================

    public function test_apply_payment_allocates_that_payment_and_no_other(): void
    {
        $this->getLoggedInUser('admin');
        $client = User::factory()->create(['role' => 'user', 'email' => 'apply-one-'.uniqid().'@test.local']);
        $invoice = \App\Model\Order\Invoice::factory()->create([
            'user_id' => $client->id, 'grand_total' => 30.0, 'currency' => 'USD', 'status' => 'pending',
        ]);

        $unapplied = new \App\Services\Payment\UnappliedPaymentService;
        $target = $unapplied->record($client->id, 'USD', 30.0, 'check');
        $other = $unapplied->record($client->id, 'USD', 500.0, 'cash');

        $response = $this->postJson("/payments/{$target->id}/apply", [
            'payment_date' => '2025-06-01',
            'invoiceChecked' => [0 => $invoice->id],
            'invoiceAmount' => [0 => 30],
        ]);

        $response->assertStatus(200)->assertJson(['success' => true]);

        // The screen names one payment, so that is the money spent — the other
        // receipt is untouched even though it could have covered this too.
        $this->assertSame(0.0, $unapplied->unappliedOn($client->id, (int) $target->id));
        $this->assertSame(500.0, $unapplied->unappliedOn($client->id, (int) $other->id));
        $this->assertDatabaseHas('invoices', ['id' => $invoice->id, 'status' => 'success']);
    }

    public function test_apply_payment_splits_one_receipt_across_several_invoices(): void
    {
        $this->getLoggedInUser('admin');
        $client = User::factory()->create(['role' => 'user', 'email' => 'apply-split-'.uniqid().'@test.local']);
        $a = \App\Model\Order\Invoice::factory()->create(['user_id' => $client->id, 'grand_total' => 20.0, 'currency' => 'USD', 'status' => 'pending']);
        $b = \App\Model\Order\Invoice::factory()->create(['user_id' => $client->id, 'grand_total' => 50.0, 'currency' => 'USD', 'status' => 'pending']);

        $unapplied = new \App\Services\Payment\UnappliedPaymentService;
        $payment = $unapplied->record($client->id, 'USD', 30.0, 'check');

        $this->postJson("/payments/{$payment->id}/apply", [
            'payment_date' => '2025-06-01',
            'invoiceChecked' => [0 => $a->id, 1 => $b->id],
            'invoiceAmount' => [0 => 20, 1 => 10],
        ])->assertStatus(200);

        // Still ONE payment for one real receipt — the split lives in the
        // allocation table, not in cloned payment rows.
        $this->assertSame(1, \App\Model\Order\Payment::where('user_id', $client->id)->count());
        $this->assertSame(30.0, (float) $payment->fresh()->amount);

        $allocations = $payment->allocations()->orderBy('invoice_id')->get();
        $this->assertCount(2, $allocations);
        $this->assertSame(
            [(int) $a->id => 20.0, (int) $b->id => 10.0],
            $allocations->mapWithKeys(fn ($x): array => [(int) $x->invoice_id => (float) $x->amount])->all()
        );

        $this->assertSame(20.0, $a->fresh()->paidTotal());
        $this->assertSame(10.0, $b->fresh()->paidTotal());
        $this->assertSame('partially paid', strtolower($b->fresh()->status));
        $this->assertSame(0.0, $unapplied->unappliedOn($client->id, (int) $payment->id));
    }

    public function test_apply_payment_refuses_more_than_that_payment_holds(): void
    {
        $this->getLoggedInUser('admin');
        $client = User::factory()->create(['role' => 'user', 'email' => 'apply-over-'.uniqid().'@test.local']);
        $invoice = \App\Model\Order\Invoice::factory()->create([
            'user_id' => $client->id, 'grand_total' => 400.0, 'currency' => 'USD', 'status' => 'pending',
        ]);

        $unapplied = new \App\Services\Payment\UnappliedPaymentService;
        $target = $unapplied->record($client->id, 'USD', 30.0, 'check');
        $unapplied->record($client->id, 'USD', 500.0, 'cash');

        $response = $this->postJson("/payments/{$target->id}/apply", [
            'payment_date' => '2025-06-01',
            'invoiceChecked' => [0 => $invoice->id],
            'invoiceAmount' => [0 => 400],
        ]);

        $this->assertFalse($response->json('success'));
        $this->assertSame(30.0, $unapplied->unappliedOn($client->id, (int) $target->id));
        $this->assertDatabaseHas('invoices', ['id' => $invoice->id, 'status' => 'pending']);
    }

    public function test_apply_payment_refuses_an_invoice_in_another_currency(): void
    {
        $this->getLoggedInUser('admin');
        $client = User::factory()->create(['role' => 'user', 'email' => 'apply-cur-'.uniqid().'@test.local']);
        $invoice = \App\Model\Order\Invoice::factory()->create([
            'user_id' => $client->id, 'grand_total' => 20.0, 'currency' => 'INR', 'status' => 'pending',
        ]);

        $unapplied = new \App\Services\Payment\UnappliedPaymentService;
        $target = $unapplied->record($client->id, 'USD', 30.0, 'check');

        $response = $this->postJson("/payments/{$target->id}/apply", [
            'payment_date' => '2025-06-01',
            'invoiceChecked' => [0 => $invoice->id],
            'invoiceAmount' => [0 => 20],
        ]);

        $this->assertFalse($response->json('success'));
        $this->assertSame(30.0, $unapplied->unappliedOn($client->id, (int) $target->id));
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
        (new \App\Services\Payment\CreditBalanceService)->grant(
            $client->id, 'USD', 200.0, \App\Model\Order\CreditTransaction::TYPE_OVERPAYMENT
        );

        // invoiceChecked and invoiceAmount are parallel arrays: key = numeric index
        $response = $this->postJson("/newMultiplePayment/update/{$client->id}", [
            'payment_date' => '2025-06-01',
            'payment_method' => 'cash',
            'invoiceChecked' => [0 => $invoice->id],
            'invoiceAmount' => [0 => 100],
        ]);

        $response->assertStatus(200)
            ->assertJson(['success' => true]);

        // 200 credit - 100 applied = 100 left; invoice is now fully paid.
        $this->assertSame(100.0, (new \App\Services\Payment\CreditBalanceService)->balance($client->id, 'USD'));
        $this->assertDatabaseHas('credit_transactions', [
            'user_id' => $client->id,
            'currency' => 'USD',
            'amount' => '-100',
            'type' => \App\Model\Order\CreditTransaction::TYPE_APPLIED_TO_INVOICE,
            'invoice_id' => $invoice->id,
        ]);
        $this->assertDatabaseHas('invoices', ['id' => $invoice->id, 'status' => 'success']);
    }

    public function test_post_new_multiple_payment_rejects_amount_over_invoice_due(): void
    {
        $this->getLoggedInUser('admin');
        $client = User::factory()->create(['role' => 'user', 'email' => 'over-pay-'.uniqid().'@test.local']);
        $invoice = \App\Model\Order\Invoice::factory()->create([
            'user_id' => $client->id,
            'grand_total' => 100.0,
            'currency' => 'USD',
            'status' => 'pending',
        ]);

        $response = $this->postJson("/newMultiplePayment/receive/{$client->id}", [
            'payment_date' => '2025-06-01',
            'payment_method' => 'cash',
            'totalAmt' => 500,
            'invoiceChecked' => [0 => $invoice->id],
            'invoiceAmount' => [0 => 500],
            'currency' => 'USD',
        ]);

        $this->assertFalse($response->json('success'));
        // Rolled back whole: no payment row, invoice untouched, nothing banked.
        $this->assertDatabaseMissing('payment_invoice', ['invoice_id' => $invoice->id]);
        $this->assertDatabaseHas('invoices', ['id' => $invoice->id, 'status' => 'pending']);
        $this->assertSame(0.0, (new \App\Services\Payment\UnappliedPaymentService)->balance($client->id, 'USD'));
    }

    public function test_post_new_multiple_payment_rejects_another_clients_invoice(): void
    {
        $this->getLoggedInUser('admin');
        $client = User::factory()->create(['role' => 'user', 'email' => 'payer-'.uniqid().'@test.local']);
        $stranger = User::factory()->create(['role' => 'user', 'email' => 'stranger-'.uniqid().'@test.local']);
        $invoice = \App\Model\Order\Invoice::factory()->create([
            'user_id' => $stranger->id,
            'grand_total' => 100.0,
            'currency' => 'USD',
            'status' => 'pending',
        ]);

        $response = $this->postJson("/newMultiplePayment/receive/{$client->id}", [
            'payment_date' => '2025-06-01',
            'payment_method' => 'cash',
            'totalAmt' => 100,
            'invoiceChecked' => [0 => $invoice->id],
            'invoiceAmount' => [0 => 100],
            'currency' => 'USD',
        ]);

        $this->assertFalse($response->json('success'));
        $this->assertDatabaseMissing('payment_invoice', ['invoice_id' => $invoice->id]);
    }

    public function test_post_new_multiple_payment_with_credit_balance_method_draws_from_ledger(): void
    {
        $this->getLoggedInUser('admin');
        $client = User::factory()->create(['role' => 'user', 'email' => 'credit-method-'.uniqid().'@test.local']);
        $invoice = \App\Model\Order\Invoice::factory()->create([
            'user_id' => $client->id,
            'grand_total' => 100.0,
            'currency' => 'USD',
            'status' => 'pending',
        ]);

        (new \App\Services\Payment\CreditBalanceService)->grant(
            $client->id, 'USD', 100.0, \App\Model\Order\CreditTransaction::TYPE_OVERPAYMENT
        );

        $response = $this->postJson("/newMultiplePayment/receive/{$client->id}", [
            'payment_date' => '2025-06-01',
            'payment_method' => 'Credit Balance',
            'totalAmt' => 100,
            'invoiceChecked' => [0 => $invoice->id],
            'invoiceAmount' => [0 => 100],
            'currency' => 'USD',
        ]);

        $response->assertStatus(200)->assertJson(['success' => true]);

        // Paying by credit spends the balance rather than inventing money.
        $this->assertSame(0.0, (new \App\Services\Payment\CreditBalanceService)->balance($client->id, 'USD'));
        $this->assertDatabaseHas('invoices', ['id' => $invoice->id, 'status' => 'success']);
    }

    public function test_post_new_multiple_payment_with_credit_balance_method_fails_without_credit(): void
    {
        $this->getLoggedInUser('admin');
        $client = User::factory()->create(['role' => 'user', 'email' => 'nocredit-method-'.uniqid().'@test.local']);
        $invoice = \App\Model\Order\Invoice::factory()->create([
            'user_id' => $client->id,
            'grand_total' => 100.0,
            'currency' => 'USD',
            'status' => 'pending',
        ]);

        $response = $this->postJson("/newMultiplePayment/receive/{$client->id}", [
            'payment_date' => '2025-06-01',
            'payment_method' => 'Credit Balance',
            'totalAmt' => 100,
            'invoiceChecked' => [0 => $invoice->id],
            'invoiceAmount' => [0 => 100],
            'currency' => 'USD',
        ]);

        $this->assertFalse($response->json('success'));
        $this->assertDatabaseMissing('payment_invoice', ['invoice_id' => $invoice->id]);
        $this->assertDatabaseHas('invoices', ['id' => $invoice->id, 'status' => 'pending']);
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

        $response->assertStatus(412);
        $this->assertArrayHasKey('invoiceChecked', $response->json('message'));
    }
}
