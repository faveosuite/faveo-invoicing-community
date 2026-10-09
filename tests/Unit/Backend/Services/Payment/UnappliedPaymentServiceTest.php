<?php

declare(strict_types=1);

namespace Tests\Unit\Backend\Services\Payment;

use App\Model\Order\Invoice;
use App\Model\Order\Payment;
use App\Services\Payment\CreditBalanceService;
use App\Services\Payment\UnappliedPaymentService;
use App\User;
use Exception;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\DBTestCase;

class UnappliedPaymentServiceTest extends DBTestCase
{
    use DatabaseTransactions;

    private UnappliedPaymentService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new UnappliedPaymentService;
    }

    private function client(): User
    {
        return User::factory()->create(['role' => 'user', 'email' => 'unapplied-'.uniqid().'@test.local']);
    }

    private function invoice(User $client, float $total, string $currency = 'USD'): Invoice
    {
        return Invoice::factory()->create([
            'user_id' => $client->id,
            'grand_total' => $total,
            'currency' => $currency,
            'status' => 'pending',
        ]);
    }

    public function test_recording_a_receipt_makes_it_spendable(): void
    {
        $client = $this->client();
        $this->service->record($client->id, 'USD', 500.0, 'cash');

        $this->assertSame(500.0, $this->service->balance($client->id, 'USD'));
    }

    public function test_a_receipt_is_a_payment_not_a_credit(): void
    {
        $client = $this->client();
        $this->service->record($client->id, 'USD', 500.0, 'cash');

        // The whole point of the split: money the client paid us never shows up
        // as credit, which is something we grant them.
        $this->assertSame(0.0, (new CreditBalanceService)->balance($client->id, 'USD'));
        $this->assertDatabaseHas('payments', [
            'user_id' => $client->id,
            'payment_method' => 'cash',
        ]);
    }

    public function test_applying_draws_the_pool_down_and_pays_the_invoice(): void
    {
        $client = $this->client();
        $invoice = $this->invoice($client, 120.0);
        $this->service->record($client->id, 'USD', 500.0, 'cash');

        $this->service->apply($client->id, 'USD', 120.0, (int) $invoice->id, 'Unapplied Payment');

        $this->assertSame(380.0, $this->service->balance($client->id, 'USD'));
        $this->assertSame(120.0, $invoice->fresh()->paidTotal());
    }

    public function test_the_original_payment_is_never_rewritten(): void
    {
        $client = $this->client();
        $invoice = $this->invoice($client, 120.0);
        $payment = $this->service->record($client->id, 'USD', 500.0, 'cash');

        $this->service->apply($client->id, 'USD', 120.0, (int) $invoice->id, 'Unapplied Payment');

        // "We received 500 by cash on this date" stays true; the allocation is
        // a row of its own, so there is still exactly one payment.
        $this->assertSame(500.0, (float) $payment->fresh()->amount);
        $this->assertSame(1, Payment::where('user_id', $client->id)->count());
        $this->assertDatabaseHas('payment_invoice', [
            'payment_id' => $payment->id,
            'invoice_id' => $invoice->id,
            'amount' => 120,
        ]);
    }

    public function test_it_spends_the_oldest_payment_first(): void
    {
        $client = $this->client();
        $invoice = $this->invoice($client, 150.0);
        $first = $this->service->record($client->id, 'USD', 100.0, 'cash');
        $second = $this->service->record($client->id, 'USD', 100.0, 'cash');

        $this->service->apply($client->id, 'USD', 150.0, (int) $invoice->id, 'Unapplied Payment');

        // First payment fully consumed, second only half.
        $this->assertSame(100.0, (float) $first->allocations()->sum('amount'));
        $this->assertSame(50.0, (float) $second->allocations()->sum('amount'));
        $this->assertSame(50.0, $this->service->balance($client->id, 'USD'));
    }

    public function test_it_refuses_to_spend_more_than_the_pool_holds(): void
    {
        $client = $this->client();
        $invoice = $this->invoice($client, 900.0);
        $this->service->record($client->id, 'USD', 100.0, 'cash');

        $this->expectException(Exception::class);

        try {
            $this->service->apply($client->id, 'USD', 900.0, (int) $invoice->id, 'Unapplied Payment');
        } finally {
            $this->assertSame(100.0, $this->service->balance($client->id, 'USD'));
            $this->assertSame(0.0, $invoice->fresh()->paidTotal());
        }
    }

    public function test_the_pool_never_crosses_currencies(): void
    {
        $client = $this->client();
        $invoice = $this->invoice($client, 50.0, 'INR');
        $this->service->record($client->id, 'USD', 500.0, 'cash');

        $this->assertSame(0.0, $this->service->balance($client->id, 'INR'));

        $this->expectException(Exception::class);
        $this->service->apply($client->id, 'INR', 50.0, (int) $invoice->id, 'Unapplied Payment');
    }

    public function test_balances_reports_each_currency_separately(): void
    {
        $client = $this->client();
        $this->service->record($client->id, 'USD', 25.0, 'cash');
        $this->service->record($client->id, 'INR', 700.0, 'cash');

        $this->assertEquals(
            [['currency' => 'INR', 'balance' => 700.0], ['currency' => 'USD', 'balance' => 25.0]],
            $this->service->balances($client->id)
        );
    }

    public function test_a_fully_spent_payment_leaves_nothing_behind(): void
    {
        $client = $this->client();
        $invoice = $this->invoice($client, 100.0);
        $this->service->record($client->id, 'USD', 100.0, 'cash');

        $this->service->apply($client->id, 'USD', 100.0, (int) $invoice->id, 'Unapplied Payment');

        $this->assertSame(0.0, $this->service->balance($client->id, 'USD'));
        $this->assertSame([], $this->service->balances($client->id));
    }
}
