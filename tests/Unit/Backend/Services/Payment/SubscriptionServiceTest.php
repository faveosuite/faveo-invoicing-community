<?php

declare(strict_types=1);

namespace Tests\Unit\Backend\Services\Payment;

use App\Plugins\Payment\Contracts\PaymentGateway;
use App\Plugins\Payment\Contracts\SubscriptionGateway;
use App\Plugins\Payment\Dto\SubscriptionRequest;
use App\Plugins\Payment\Dto\SubscriptionResult;
use App\Plugins\Payment\Exceptions\PaymentException;
use App\Plugins\Payment\PaymentGatewayManager;
use App\Services\Payment\PaymentService;
use App\Services\Payment\SubscriptionService;
use Mockery;
use Mockery\MockInterface;
use Tests\TestCase;

class SubscriptionServiceTest extends TestCase
{
    /** @var PaymentService&MockInterface */
    private PaymentService $payments;

    private SubscriptionService $service;

    protected function setUp(): void
    {
        parent::setUp();

        /** @var PaymentService&MockInterface $payments */
        $payments = Mockery::mock(PaymentService::class);
        $this->payments = $payments;
        $this->service = new SubscriptionService($this->payments);
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    // Helpers

    /**
     * Create a mock implementing both PaymentGateway and SubscriptionGateway
     * (PaymentGatewayManager::gateway() return type is PaymentGateway, but
     * SubscriptionService::gateway() also checks instanceof SubscriptionGateway).
     *
     * @return PaymentGateway&SubscriptionGateway&MockInterface
     */
    private function makeSubGateway(): MockInterface
    {
        /** @var PaymentGateway&SubscriptionGateway&MockInterface $mock */
        $mock = Mockery::mock(
            PaymentGateway::class.','.SubscriptionGateway::class
        );

        return $mock;
    }

    private function makeRequest(): SubscriptionRequest
    {
        return new SubscriptionRequest(
            amountMinor: 1999,
            currency: 'USD',
            intervalDays: 30,
            planName: 'Test Plan',
        );
    }

    private function makeResult(string $status = 'active'): SubscriptionResult
    {
        return new SubscriptionResult(
            gateway: 'TestGW',
            id: 'sub_123',
            status: $status,
        );
    }

    // --- gateway() guards (tested indirectly) ---

    public function test_create_subscription_throws_when_gateway_not_registered(): void
    {
        $manager = new PaymentGatewayManager(); // empty

        $this->payments->shouldReceive('manager')->andReturn($manager);

        $this->expectException(PaymentException::class);

        $this->service->createSubscription('UnknownGateway', $this->makeRequest());
    }

    public function test_create_subscription_throws_when_gateway_does_not_support_subscriptions(): void
    {
        /** @var PaymentGateway&MockInterface $basicGateway */
        $basicGateway = Mockery::mock(PaymentGateway::class); // NOT SubscriptionGateway

        $manager = (new PaymentGatewayManager)
            ->register('BasicGW', fn (): PaymentGateway => $basicGateway);

        $this->payments->shouldReceive('manager')->andReturn($manager);

        $this->expectException(PaymentException::class);
        $this->expectExceptionMessageMatches('/does not support subscriptions/');

        $this->service->createSubscription('BasicGW', $this->makeRequest());
    }

    public function test_create_subscription_delegates_to_subscription_gateway(): void
    {
        $subGateway = $this->makeSubGateway();
        $expected = $this->makeResult('active');

        $subGateway->shouldReceive('createSubscription')
            ->once()
            ->with(Mockery::type(SubscriptionRequest::class))
            ->andReturn($expected);

        $manager = (new PaymentGatewayManager)
            ->register('SubGW', fn (): PaymentGateway => $subGateway);

        $this->payments->shouldReceive('manager')->andReturn($manager);

        $result = $this->service->createSubscription('SubGW', $this->makeRequest());

        $this->assertSame('sub_123', $result->id);
        $this->assertSame('active', $result->status);
    }

    // --- getStatus() ---

    public function test_get_status_throws_for_non_subscription_gateway(): void
    {
        /** @var PaymentGateway&MockInterface $basicGateway */
        $basicGateway = Mockery::mock(PaymentGateway::class);

        $manager = (new PaymentGatewayManager)
            ->register('BasicGW', fn (): PaymentGateway => $basicGateway);

        $this->payments->shouldReceive('manager')->andReturn($manager);

        $this->expectException(PaymentException::class);

        $this->service->getStatus('BasicGW', 'sub_123');
    }

    public function test_get_status_delegates_to_gateway(): void
    {
        $subGateway = $this->makeSubGateway();
        $subGateway->shouldReceive('getSubscriptionStatus')
            ->once()
            ->with('sub_123')
            ->andReturn('active');

        $manager = (new PaymentGatewayManager)
            ->register('SubGW', fn (): PaymentGateway => $subGateway);

        $this->payments->shouldReceive('manager')->andReturn($manager);

        $status = $this->service->getStatus('SubGW', 'sub_123');

        $this->assertSame('active', $status);
    }

    // --- cancelSubscription() ---

    public function test_cancel_subscription_throws_for_non_subscription_gateway(): void
    {
        /** @var PaymentGateway&MockInterface $basicGateway */
        $basicGateway = Mockery::mock(PaymentGateway::class);

        $manager = (new PaymentGatewayManager)
            ->register('BasicGW', fn (): PaymentGateway => $basicGateway);

        $this->payments->shouldReceive('manager')->andReturn($manager);

        $this->expectException(PaymentException::class);

        $this->service->cancelSubscription('BasicGW', 'sub_123');
    }

    public function test_cancel_subscription_delegates_to_gateway(): void
    {
        $subGateway = $this->makeSubGateway();
        $cancelled = $this->makeResult('cancelled');

        $subGateway->shouldReceive('cancelSubscription')
            ->once()
            ->with('sub_123')
            ->andReturn($cancelled);

        $manager = (new PaymentGatewayManager)
            ->register('SubGW', fn (): PaymentGateway => $subGateway);

        $this->payments->shouldReceive('manager')->andReturn($manager);

        $result = $this->service->cancelSubscription('SubGW', 'sub_123');

        $this->assertSame('cancelled', $result->status);
    }

    // --- updateSubscriptionPrice() ---

    public function test_update_subscription_price_throws_for_non_subscription_gateway(): void
    {
        /** @var PaymentGateway&MockInterface $basicGateway */
        $basicGateway = Mockery::mock(PaymentGateway::class);

        $manager = (new PaymentGatewayManager)
            ->register('BasicGW', fn (): PaymentGateway => $basicGateway);

        $this->payments->shouldReceive('manager')->andReturn($manager);

        $this->expectException(PaymentException::class);

        $this->service->updateSubscriptionPrice('BasicGW', 'sub_123', $this->makeRequest());
    }

    public function test_update_subscription_price_delegates_to_gateway(): void
    {
        $subGateway = $this->makeSubGateway();
        $updated = $this->makeResult('active');

        $subGateway->shouldReceive('updateSubscriptionPrice')
            ->once()
            ->andReturn($updated);

        $manager = (new PaymentGatewayManager)
            ->register('SubGW', fn (): PaymentGateway => $subGateway);

        $this->payments->shouldReceive('manager')->andReturn($manager);

        $result = $this->service->updateSubscriptionPrice('SubGW', 'sub_123', $this->makeRequest());

        $this->assertSame('active', $result->status);
    }
}
