<?php

declare(strict_types=1);

namespace Tests\Unit\Backend\Services\Payment;

use App\Plugins\Payment\Contracts\PaymentGateway;
use App\Plugins\Payment\Dto\PaymentRequest;
use App\Plugins\Payment\Exceptions\PaymentException;
use App\Plugins\Payment\PaymentGatewayManager;
use App\Services\Payment\PaymentService;
use Mockery;
use Mockery\MockInterface;
use Tests\DBTestCase;

class PaymentServiceTest extends DBTestCase
{
    /** @var PaymentService&MockInterface */
    private PaymentService $service;

    private PaymentGatewayManager $manager;

    protected function setUp(): void
    {
        parent::setUp();

        // A controlled manager with a basic (non-card) gateway registered.
        /** @var PaymentGateway&MockInterface $basicGateway */
        $basicGateway = Mockery::mock(PaymentGateway::class);

        $this->manager = (new PaymentGatewayManager)
            ->register('BasicGateway', fn (): PaymentGateway => $basicGateway);

        // Partial mock so manager() is swappable without hitting ApiKey DB row.
        /** @var PaymentService&MockInterface $service */
        $service = Mockery::mock(PaymentService::class)->makePartial();
        $service->shouldReceive('manager')->andReturn($this->manager)->byDefault();
        $this->service = $service;
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    // --- manager() ---

    public function test_manager_returns_payment_gateway_manager_instance(): void
    {
        // Uses the real manager() which reads ApiKey::find(1) from the DB.
        $real = new PaymentService();
        $result = $real->manager();

        $this->assertInstanceOf(PaymentGatewayManager::class, $result);
    }

    public function test_manager_registers_stripe_and_razorpay(): void
    {
        $real = new PaymentService();
        $mgr = $real->manager();

        $this->assertTrue($mgr->has('stripe'));
        $this->assertTrue($mgr->has('razorpay'));
    }

    // --- publishableKey() ---

    public function test_publishable_key_returns_string(): void
    {
        $real = new PaymentService();
        $key = $real->publishableKey();

        $this->assertIsString($key);
    }

    // --- startCardPayment() ---

    public function test_start_card_payment_throws_when_gateway_does_not_support_card_ui(): void
    {
        // BasicGateway implements PaymentGateway but NOT CardPaymentGateway.
        $request = new PaymentRequest(amount: 100.0, currency: 'USD', reference: 'test-ref');

        $this->expectException(PaymentException::class);
        $this->expectExceptionMessageMatches('/does not support a custom card UI/');

        $this->service->startCardPayment('BasicGateway', $request);
    }

    // --- start() ---

    public function test_start_throws_for_unregistered_gateway(): void
    {
        $request = new PaymentRequest(amount: 100.0, currency: 'USD', reference: 'test-ref');

        $this->expectException(PaymentException::class);

        $this->service->start('UnknownGateway', $request);
    }

    // --- capture() ---

    public function test_capture_throws_for_unregistered_gateway(): void
    {
        $this->expectException(PaymentException::class);

        $this->service->capture('UnknownGateway', []);
    }

    // --- verifyWebhook() ---

    public function test_verify_webhook_throws_for_unregistered_gateway(): void
    {
        $this->expectException(PaymentException::class);

        $this->service->verifyWebhook('UnknownGateway', 'raw', 'sig');
    }

    public function test_verify_webhook_delegates_to_gateway_and_returns_bool(): void
    {
        /** @var PaymentGateway&MockInterface $gateway */
        $gateway = Mockery::mock(PaymentGateway::class);
        $gateway->shouldReceive('verifyWebhook')
            ->once()
            ->with('raw-body', 'signature')
            ->andReturn(true);

        $manager = (new PaymentGatewayManager)
            ->register('TestGW', fn (): PaymentGateway => $gateway);

        /** @var PaymentService&MockInterface $service */
        $service = Mockery::mock(PaymentService::class)->makePartial();
        $service->shouldReceive('manager')->andReturn($manager);

        $result = $service->verifyWebhook('TestGW', 'raw-body', 'signature');

        $this->assertTrue($result);
    }
}
