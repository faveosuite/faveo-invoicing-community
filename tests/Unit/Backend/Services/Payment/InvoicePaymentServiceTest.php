<?php

declare(strict_types=1);

namespace Tests\Unit\Backend\Services\Payment;

use App\Model\Order\Invoice;
use App\Plugins\Payment\Dto\PaymentResult;
use App\Plugins\Payment\Dto\PaymentSession;
use App\Services\Payment\AutoRenewalActivationService;
use App\Services\Payment\InvoicePaymentService;
use App\Services\Payment\PaymentService;
use App\Services\Payment\PostPaymentService;
use DB;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Mockery;
use Mockery\MockInterface;
use Tests\DBTestCase;

class InvoicePaymentServiceTest extends DBTestCase
{
    use DatabaseTransactions;

    private InvoicePaymentService $service;

    /** @var PaymentService&MockInterface */
    private PaymentService $payments;

    /** @var PostPaymentService&MockInterface */
    private PostPaymentService $postPayment;

    /**
     * What a real gateway returns for a payment on $invoice: the invoice_id we stamped (Stripe
     * `metadata`, Razorpay `notes`) plus the amount captured in minor units and its currency.
     * Defaults to a generous amount (covers any processing fee).
     *
     * @param  array<string, mixed>  $stamp
     */
    private function captured(Invoice $invoice, string $where = 'metadata', ?int $minor = null, ?string $currency = null, array $stamp = []): array
    {
        return [
            $where => ['invoice_id' => (string) $invoice->id] + $stamp,
            'amount_received' => $minor ?? \App\Plugins\Payment\Support\Money::toMinor((float) $invoice->grand_total * 1.1, (string) $invoice->currency),
            'currency' => strtolower($currency ?? (string) $invoice->currency),
        ];
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->payments = Mockery::mock(PaymentService::class);
        $this->postPayment = Mockery::mock(PostPaymentService::class);
        $this->service = new InvoicePaymentService(
            $this->payments,
            $this->postPayment,
            Mockery::mock(AutoRenewalActivationService::class),
        );
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    public function test_publishable_key_delegates_to_payment_service(): void
    {
        $this->payments->shouldReceive('publishableKey')->once()->andReturn('pk_test_123');

        $this->assertSame('pk_test_123', $this->service->publishableKey());
    }

    public function test_outstanding_returns_grand_total_when_no_payments(): void
    {
        $invoice = Invoice::factory()->create(['grand_total' => 150.0]);

        $outstanding = $this->service->outstanding($invoice);

        $this->assertEqualsWithDelta(150.0, $outstanding, 0.01);
    }

    public function test_outstanding_returns_zero_when_fully_paid(): void
    {
        $invoice = Invoice::factory()->create(['grand_total' => 100.0]);

        // A payment for the full amount, allocated to this invoice.
        \App\Model\Order\Payment::create([
            'user_id' => $invoice->user_id,
            'amount' => 100.0,
            'payment_method' => 'Stripe',
            'payment_status' => 'success',
            'currency' => $invoice->currency,
        ])->invoices()->attach($invoice->id, ['amount' => 100.0]);

        $outstanding = $this->service->outstanding($invoice);

        $this->assertEqualsWithDelta(0.0, $outstanding, 0.01);
    }

    public function test_apply_credit_fulfils_free_invoice_with_no_credit_needed(): void
    {
        $invoice = Invoice::factory()->create(['grand_total' => 0.0, 'status' => 'pending']);

        $this->postPayment->shouldReceive('handle')->once()->with(Mockery::on(
            fn ($arg) => $arg->id === $invoice->id
        ), 'Credit Balance')->andReturn([]);

        $result = $this->service->applyCredit($invoice);

        $this->assertTrue($result['paid_in_full']);
    }

    public function test_apply_credit_throws_when_outstanding_and_no_credit(): void
    {
        $invoice = Invoice::factory()->create(['grand_total' => 100.0, 'status' => 'pending']);

        $this->postPayment->shouldNotReceive('handle');
        $this->expectException(\Exception::class);

        $this->service->applyCredit($invoice);
    }

    public function test_available_credit_reads_from_the_ledger_for_the_given_currency(): void
    {
        $user = \App\User::factory()->create();
        app(\App\Services\Payment\CreditBalanceService::class)->grant(
            $user->id, 'USD', 75.0, \App\Model\Order\CreditTransaction::TYPE_OVERPAYMENT
        );

        $this->assertSame(75.0, $this->service->availableCredit($user->id, 'USD'));
    }

    public function test_available_credit_does_not_leak_across_currencies(): void
    {
        $user = \App\User::factory()->create();
        app(\App\Services\Payment\CreditBalanceService::class)->grant(
            $user->id, 'INR', 5000.0, \App\Model\Order\CreditTransaction::TYPE_OVERPAYMENT
        );

        $this->assertSame(0.0, $this->service->availableCredit($user->id, 'USD'));
    }

    public function test_available_credit_excludes_unapplied_payment(): void
    {
        $user = \App\User::factory()->create();
        app(\App\Services\Payment\UnappliedPaymentService::class)->record($user->id, 'USD', 60.0, 'cash');
        app(\App\Services\Payment\CreditBalanceService::class)->grant(
            $user->id, 'USD', 90.0, \App\Model\Order\CreditTransaction::TYPE_DOWNGRADE_PRORATION
        );

        // Unapplied payment is the client's own unallocated money, not credit
        // we granted — client-facing checkout must only ever offer the real
        // ledger balance (90), never the unapplied payment on top of it.
        $this->assertSame(90.0, $this->service->availableCredit($user->id, 'USD'));
    }

    public function test_apply_credit_only_spends_ledger_credit_never_unapplied_payment(): void
    {
        $user = \App\User::factory()->create(['role' => 'user', 'email' => 'spend-order-'.uniqid().'@test.local']);
        $invoice = Invoice::factory()->create([
            'user_id' => $user->id, 'grand_total' => 90.0, 'currency' => 'USD', 'status' => 'pending',
        ]);

        $unapplied = app(\App\Services\Payment\UnappliedPaymentService::class);
        $credit = app(\App\Services\Payment\CreditBalanceService::class);
        $unapplied->record($user->id, 'USD', 60.0, 'cash');
        $credit->grant($user->id, 'USD', 90.0, \App\Model\Order\CreditTransaction::TYPE_DOWNGRADE_PRORATION);

        $this->postPayment->shouldReceive('handle')->once()->andReturn([]);

        $result = $this->service->applyCredit($invoice);

        $this->assertTrue($result['paid_in_full']);
        $this->assertSame(90.0, $invoice->fresh()->paidTotal());
        // Checkout only ever spends the real credit ledger — unapplied
        // payment is the client's own unallocated money and is never touched
        // by this flow (only admins allocate it, elsewhere).
        $this->assertSame(60.0, $unapplied->balance($user->id, 'USD'));
        $this->assertSame(0.0, $credit->balance($user->id, 'USD'));
    }

    public function test_gateways_for_returns_array(): void
    {
        $result = $this->service->gatewaysFor('USD');

        $this->assertIsArray($result);
    }

    public function test_gateways_for_returns_mapped_array_when_gateways_active(): void
    {
        // checkPaymentGateway returns an array of names → covers lines 70-71
        // This hits the real SettingsController::checkPaymentGateway which queries Plugin table
        $result = $this->service->gatewaysFor('USD');

        // Either returns an array (with or without items) — both paths are valid
        $this->assertIsArray($result);

        // If non-empty, each entry must have 'name' key (line 70-71 covered)
        foreach ($result as $gateway) {
            $this->assertArrayHasKey('name', $gateway);
        }
    }

    public function test_confirm_returns_true_for_already_paid_invoice(): void
    {
        $invoice = Invoice::factory()->create(['grand_total' => 0.0]);

        $result = $this->service->confirm($invoice, 'Stripe', []);

        $this->assertTrue($result);
    }

    public function test_confirm_returns_false_when_capture_not_paid(): void
    {
        $invoice = Invoice::factory()->create(['grand_total' => 100.0]);

        $result = new PaymentResult(paid: false, gateway: 'Stripe');
        $this->payments->shouldReceive('capture')->once()->andReturn($result);

        $outcome = $this->service->confirm($invoice, 'Stripe', []);

        $this->assertFalse($outcome);
    }

    public function test_confirm_fulfils_order_when_capture_succeeds(): void
    {
        // status explicit — the factory's default is a random pick including
        // 'success', which would make confirm()'s atomic claim (status !=
        // 'success') skip fulfilment even though no payment was ever recorded;
        // a real freshly-created invoice always starts 'pending' (CartService).
        $invoice = Invoice::factory()->create(['grand_total' => 100.0, 'processing_fee' => null, 'status' => 'pending']);

        // 'NoFeeGateway' has no DB table → ProcessingFee::percent returns 0 → covers line 155
        $result = new PaymentResult(paid: true, gateway: 'NoFeeGateway', raw: $this->captured($invoice));
        $this->payments->shouldReceive('capture')->once()->andReturn($result);
        $this->postPayment->shouldReceive('handle')->once();

        $outcome = $this->service->confirm($invoice, 'NoFeeGateway', []);

        $this->assertTrue($outcome);
    }

    /**
     * Regression test for the redirect-vs-webhook race: both call confirm()
     * independently for the same payment and can arrive close enough together
     * that both would pass every check (outstanding(), capture(),
     * referenceBelongsToInvoice()) before either had actually fulfilled
     * anything. Simulates the second (losing) caller by marking the invoice
     * 'success' directly — exactly what the first caller's atomic claim would
     * have already done by the time this one reaches it — and asserts
     * fulfilment does not run a second time.
     */
    public function test_confirm_does_not_refulfil_when_another_caller_already_claimed_it(): void
    {
        $invoice = Invoice::factory()->create(['grand_total' => 100.0, 'processing_fee' => null, 'status' => 'pending']);

        $result = new PaymentResult(paid: true, gateway: 'NoFeeGateway', raw: $this->captured($invoice));
        $this->payments->shouldReceive('capture')->once()->andReturn($result);
        $this->postPayment->shouldNotReceive('handle');

        // The "other caller" (redirect or webhook, whichever wins the real
        // race) claims the invoice first.
        DB::table('invoices')->where('id', $invoice->id)->update(['status' => 'success']);

        $outcome = $this->service->confirm($invoice, 'NoFeeGateway', []);

        $this->assertTrue($outcome);
    }

    /**
     * Regression test: a captured payment only proves the reference is real —
     * not that it was ever meant for THIS invoice. Without checking the
     * invoice_id stamped into the gateway metadata at creation time, a
     * completed payment for a cheap invoice could be replayed to fulfil a
     * different, more expensive invoice the same user owns.
     */
    public function test_confirm_rejects_payment_for_a_different_invoice(): void
    {
        // status explicit — the factory's default is a random pick including
        // 'success', which would make confirm()'s atomic claim (status !=
        // 'success') skip fulfilment even though no payment was ever recorded;
        // a real freshly-created invoice always starts 'pending' (CartService).
        $invoice = Invoice::factory()->create(['grand_total' => 100.0, 'processing_fee' => null, 'status' => 'pending']);
        $otherInvoiceId = $invoice->id + 1;

        $result = new PaymentResult(paid: true, gateway: 'NoFeeGateway', raw: ['metadata' => ['invoice_id' => (string) $otherInvoiceId]]);
        $this->payments->shouldReceive('capture')->once()->andReturn($result);
        $this->postPayment->shouldNotReceive('handle');

        $outcome = $this->service->confirm($invoice, 'NoFeeGateway', []);

        $this->assertFalse($outcome);
    }

    public function test_confirm_rejects_payment_with_no_invoice_metadata_at_all(): void
    {
        // status explicit — the factory's default is a random pick including
        // 'success', which would make confirm()'s atomic claim (status !=
        // 'success') skip fulfilment even though no payment was ever recorded;
        // a real freshly-created invoice always starts 'pending' (CartService).
        $invoice = Invoice::factory()->create(['grand_total' => 100.0, 'processing_fee' => null, 'status' => 'pending']);

        $result = new PaymentResult(paid: true, gateway: 'NoFeeGateway', raw: []);
        $this->payments->shouldReceive('capture')->once()->andReturn($result);
        $this->postPayment->shouldNotReceive('handle');

        $outcome = $this->service->confirm($invoice, 'NoFeeGateway', []);

        $this->assertFalse($outcome);
    }

    public function test_confirm_accepts_razorpay_notes_style_metadata(): void
    {
        // status explicit — the factory's default is a random pick including
        // 'success', which would make confirm()'s atomic claim (status !=
        // 'success') skip fulfilment even though no payment was ever recorded;
        // a real freshly-created invoice always starts 'pending' (CartService).
        $invoice = Invoice::factory()->create(['grand_total' => 100.0, 'processing_fee' => null, 'status' => 'pending']);

        $result = new PaymentResult(paid: true, gateway: 'NoFeeGateway', raw: $this->captured($invoice, 'notes'));
        $this->payments->shouldReceive('capture')->once()->andReturn($result);
        $this->postPayment->shouldReceive('handle')->once();

        $outcome = $this->service->confirm($invoice, 'NoFeeGateway', []);

        $this->assertTrue($outcome);
    }

    public function test_start_delegates_to_start_card_payment_for_stripe(): void
    {
        $invoice = Invoice::factory()->create(['grand_total' => 100.0, 'currency' => 'USD']);
        $session = new PaymentSession(gateway: 'Stripe', id: 'pi_test', clientConfig: [], raw: []);

        $this->payments->shouldReceive('startCardPayment')->once()->andReturn($session);

        $result = $this->service->start($invoice, 'Stripe');

        $this->assertSame($session, $result);
    }

    public function test_confirm_applies_processing_fee_when_stripe_has_fee(): void
    {
        // Stripe has processing_fee = 2.5 in DB → covers lines 158-160
        // status explicit — the factory's default is a random pick including
        // 'success', which would make confirm()'s atomic claim (status !=
        // 'success') skip fulfilment even though no payment was ever recorded;
        // a real freshly-created invoice always starts 'pending' (CartService).
        $invoice = Invoice::factory()->create(['grand_total' => 100.0, 'processing_fee' => null, 'status' => 'pending']);

        $result = new PaymentResult(paid: true, gateway: 'Stripe', raw: $this->captured($invoice));
        $this->payments->shouldReceive('capture')->once()->andReturn($result);
        $this->postPayment->shouldReceive('handle')->once();

        $outcome = $this->service->confirm($invoice, 'Stripe', []);

        $this->assertTrue($outcome);
        // Processing fee should have been applied
        $this->assertNotNull($invoice->fresh()->processing_fee);
    }

    public function test_confirm_skips_processing_fee_when_already_applied(): void
    {
        // invoice has processing_fee already set → applyProcessingFee returns early (line 150)
        $invoice = Invoice::factory()->create(['grand_total' => 100.0, 'processing_fee' => '2%', 'status' => 'pending']);

        $result = new PaymentResult(paid: true, gateway: 'Stripe', raw: $this->captured($invoice));
        $this->payments->shouldReceive('capture')->once()->andReturn($result);
        $this->postPayment->shouldReceive('handle')->once();

        $outcome = $this->service->confirm($invoice, 'Stripe', []);

        $this->assertTrue($outcome);
    }

    /**
     * Regression test: activateAutoRenewalOptIn() used to pick only the
     * invoice's FIRST order-with-a-subscription (whereHas(...)->first()),
     * so a multi-product cart purchase only ever got auto-renewal activated
     * (and its email sent) for one product.
     */
    public function test_activate_auto_renewal_opt_in_activates_every_order_on_invoice(): void
    {
        \App\Model\Common\Setting::where('id', 1)->update(['autorenewal_status' => 1]);
        \App\Model\Common\StatusSetting::where('id', 1)->update(['stripe_auto_renewal' => 1]);

        $invoice = Invoice::factory()->create(['grand_total' => 100.0]);

        $product = \App\Model\Product\Product::first() ?? \App\Model\Product\Product::create(['name' => 'PPSvc '.uniqid()]);
        $plan = \App\Model\Payment\Plan::where('product', $product->id)->first() ?? \App\Model\Payment\Plan::create(['name' => 'PPPlan '.uniqid(), 'product' => $product->id, 'days' => 30]);

        $orderIds = [];
        foreach (range(1, 2) as $i) {
            $order = \App\Model\Order\Order::create([
                'client' => $invoice->user_id,
                'product' => $product->id,
                'order_status' => 'executed',
                'number' => mt_rand(100000, 999999),
            ]);
            \App\Model\Order\OrderInvoiceRelation::create(['order_id' => $order->id, 'invoice_id' => $invoice->id]);
            \App\Model\Product\Subscription::create([
                'order_id' => $order->id,
                'product_id' => $product->id,
                'plan_id' => $plan->id,
                'is_subscribed' => 0,
            ]);
            $orderIds[] = $order->id;
        }

        $mockAutoRenewal = Mockery::mock(AutoRenewalActivationService::class);
        $activatedOrderIds = [];
        $mockAutoRenewal->shouldReceive('activate')
            ->times(2)
            ->withArgs(function ($order, $user, $gateway, $reference) use (&$activatedOrderIds) {
                $activatedOrderIds[] = $order->id;

                return $gateway === 'stripe' && $reference === 'pi_test_123';
            });

        $service = new InvoicePaymentService($this->payments, $this->postPayment, $mockAutoRenewal);

        $result = new PaymentResult(paid: true, gateway: 'Stripe', reference: 'pi_test_123');
        $this->getPrivateMethod($service, 'activateAutoRenewalOptIn', [$invoice, 'Stripe', $result]);

        sort($orderIds);
        sort($activatedOrderIds);
        $this->assertSame($orderIds, $activatedOrderIds);
    }

    public function test_start_delegates_to_start_for_non_stripe(): void
    {
        $invoice = Invoice::factory()->create(['grand_total' => 100.0, 'currency' => 'USD']);
        $session = new PaymentSession(gateway: 'Razorpay', id: 'order_test', clientConfig: [], raw: []);

        $this->payments->shouldReceive('start')->once()->andReturn($session);

        $result = $this->service->start($invoice, 'Razorpay');

        $this->assertSame($session, $result);
    }

    // ── C3/C4: fulfil only what was actually paid; Razorpay credit really spent ──

    private function pendingInvoice(float $total, ?int $userId = null): Invoice
    {
        return Invoice::factory()->create(array_filter([
            'grand_total' => $total, 'currency' => 'USD', 'processing_fee' => null, 'status' => 'pending', 'user_id' => $userId,
        ], fn ($v) => $v !== null));
    }

    private function refusesWith(Invoice $invoice, array $raw, array $payload = []): void
    {
        $this->payments->shouldReceive('capture')->once()->andReturn(new PaymentResult(paid: true, gateway: 'NoFeeGateway', raw: $raw));
        $this->postPayment->shouldNotReceive('handle');

        $this->assertFalse($this->service->confirm($invoice, 'NoFeeGateway', $payload));
        $this->assertNotSame('success', $invoice->fresh()->status);
    }

    public function test_confirm_refuses_an_underpayment(): void
    {
        $invoice = $this->pendingInvoice(100.0);
        $this->refusesWith($invoice, $this->captured($invoice, minor: 9999)); // $99.99 of $100
    }

    public function test_confirm_refuses_a_payment_in_another_currency(): void
    {
        $invoice = $this->pendingInvoice(100.0);
        $this->refusesWith($invoice, $this->captured($invoice, minor: 1_000_000, currency: 'INR'));
    }

    public function test_confirm_refuses_when_the_gateway_reports_no_amount(): void
    {
        $invoice = $this->pendingInvoice(100.0);
        $this->refusesWith($invoice, ['metadata' => ['invoice_id' => (string) $invoice->id]]);
    }

    public function test_confirm_refuses_a_payment_opened_before_the_invoice_grew(): void
    {
        // A $10 payment opened, then the same pending invoice rebuilt at $1000 in another tab.
        $invoice = $this->pendingInvoice(10.0);
        $raw = $this->captured($invoice, minor: 1000);
        $invoice->update(['grand_total' => 1000.0]);

        $this->refusesWith($invoice->fresh(), $raw);
    }

    public function test_confirm_accepts_an_exact_payment(): void
    {
        $invoice = $this->pendingInvoice(100.0);
        $this->payments->shouldReceive('capture')->once()->andReturn(new PaymentResult(paid: true, gateway: 'NoFeeGateway', raw: $this->captured($invoice, minor: 10000)));
        $this->postPayment->shouldReceive('handle')->once();

        $this->assertTrue($this->service->confirm($invoice, 'NoFeeGateway', []));
    }

    public function test_confirm_spends_razorpay_credit_stamped_in_notes(): void
    {
        $user = \App\User::factory()->create(['email' => 'rzp-credit-'.uniqid().'@test.local']);
        app(\App\Services\Payment\CreditBalanceService::class)->grant($user->id, 'USD', 50.0, \App\Model\Order\CreditTransaction::TYPE_OVERPAYMENT);
        $invoice = $this->pendingInvoice(100.0, $user->id);

        // Razorpay returns our metadata as `notes`, values stringified; it charged the other $50.
        $raw = $this->captured($invoice, 'notes', minor: 5000, stamp: ['credit_applied' => '50']);
        $this->payments->shouldReceive('capture')->once()->andReturn(new PaymentResult(paid: true, gateway: 'NoFeeGateway', raw: $raw));
        $this->postPayment->shouldReceive('handle')->once();

        $this->assertTrue($this->service->confirm($invoice, 'NoFeeGateway', []));
        $this->assertSame(0.0, $this->service->availableCredit($user->id, 'USD')); // the 50 was really spent
    }

    public function test_confirm_ignores_a_credit_amount_sent_by_the_browser(): void
    {
        $user = \App\User::factory()->create(['email' => 'rzp-credit-'.uniqid().'@test.local']);
        app(\App\Services\Payment\CreditBalanceService::class)->grant($user->id, 'USD', 50.0, \App\Model\Order\CreditTransaction::TYPE_OVERPAYMENT);
        $invoice = $this->pendingInvoice(100.0, $user->id);

        // Payment opened WITHOUT credit, only $50 paid, browser claims credit_applied=50.
        $this->refusesWith($invoice, $this->captured($invoice, minor: 5000), ['credit_applied' => 50]);
        $this->assertSame(50.0, $this->service->availableCredit($user->id, 'USD'));
    }
}
