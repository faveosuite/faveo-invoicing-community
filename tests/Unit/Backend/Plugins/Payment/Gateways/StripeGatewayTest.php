<?php

declare(strict_types=1);

namespace Tests\Unit\Backend\Plugins\Payment\Gateways;

use App\Plugins\Payment\Dto\Customer;
use App\Plugins\Payment\Dto\PaymentRequest;
use App\Plugins\Payment\Gateways\StripeGateway;
use PHPUnit\Framework\TestCase;
use Stripe\ApiRequestor;
use Stripe\HttpClient\ClientInterface;
use Stripe\HttpClient\CurlClient;

class StripeGatewayTest extends TestCase
{
    private function gateway(): StripeGateway
    {
        return new StripeGateway('sk_test_fake123');
    }

    /**
     * @return array{interval: string, interval_count: int}
     */
    private function recurringInterval(int $intervalDays): array
    {
        $method = new \ReflectionMethod(StripeGateway::class, 'recurringInterval');

        return $method->invoke($this->gateway(), $intervalDays);
    }

    public function test_short_interval_uses_days_or_months_under_365(): void
    {
        $resultDays = $this->recurringInterval(14); // 2-week plan
        $this->assertSame('day', $resultDays['interval']);
        $this->assertSame(14, $resultDays['interval_count']);

        $resultMonthly = $this->recurringInterval(30); // 1-month plan
        $this->assertSame('month', $resultMonthly['interval']);
        $this->assertSame(1, $resultMonthly['interval_count']);

        $resultQuarterly = $this->recurringInterval(90); // 3-month plan
        $this->assertSame('month', $resultQuarterly['interval']);
        $this->assertSame(3, $resultQuarterly['interval_count']);

        $result365 = $this->recurringInterval(365);
        $this->assertSame('year', $result365['interval']);
        $this->assertSame(1, $result365['interval_count']);
    }

    public function test_leap_year_366_days_maps_to_one_year_preventing_stripe_exception(): void
    {
        $result = $this->recurringInterval(366);
        $this->assertSame('year', $result['interval']);
        $this->assertSame(1, $result['interval_count']);
    }

    public function test_multi_year_plans_map_to_year_interval(): void
    {
        $result = $this->recurringInterval(730); // 2 years
        $this->assertSame('year', $result['interval']);
        $this->assertSame(2, $result['interval_count']);

        $result3 = $this->recurringInterval(1095); // 3 years
        $this->assertSame('year', $result3['interval']);
        $this->assertSame(3, $result3['interval_count']);
    }

    /**
     * Fake Stripe that keeps its real idempotency rule: a key reused with the same params
     * replays the first object, with different params it is rejected.
     */
    private function fakeStripe(): ClientInterface
    {
        return new class implements ClientInterface
        {
            /** @var array<string, array{params: string, body: string}> */
            public array $seen = [];

            /** @var list<string> */
            public array $customers = [];

            private int $next = 0;

            public function request($method, $absUrl, $headers, $params, $hasFile, $apiMode = 'v1', $maxNetworkRetries = null)
            {
                $key = '';
                foreach ($headers as $header) {
                    if (str_starts_with(strtolower($header), 'idempotency-key:')) {
                        $key = trim(substr($header, 16));
                    }
                }
                $sent = json_encode($params);
                if ($key !== '' && isset($this->seen[$key])) {
                    if ($this->seen[$key]['params'] !== $sent) {
                        return [json_encode(['error' => ['type' => 'idempotency_error',
                            'message' => 'Keys for idempotent requests can only be used with the same parameters they were first used with.']]), 400, []];
                    }

                    return [$this->seen[$key]['body'], 200, []];
                }
                $this->next++;
                $body = str_contains($absUrl, '/customers')
                    ? json_encode(['id' => 'cus_'.$this->next, 'object' => 'customer'])
                    : json_encode(['id' => 'pi_'.$this->next, 'object' => 'payment_intent', 'client_secret' => 'secret_'.$this->next, 'status' => 'requires_payment_method', 'customer' => $params['customer'] ?? null]);
                if (str_contains($absUrl, '/customers')) {
                    $this->customers[] = json_decode($body, true)['id'];
                }
                if ($key !== '') {
                    $this->seen[$key] = ['params' => $sent, 'body' => $body];
                }

                return [$body, 200, []];
            }
        };
    }

    private function cardRequest(string $line1): PaymentRequest
    {
        return new PaymentRequest(
            amount: 46999.0,
            currency: 'EUR',
            reference: '35386220',
            customer: new Customer(name: 'Jane Doe', email: 'jane@example.com', line1: $line1, city: 'Chennai', postalCode: '600001', country: 'IN'),
            description: 'Payment for Invoice No - 35386220',
            metadata: ['invoice_id' => 1, 'user_id' => 2, 'credit_applied' => 0],
            saveForFutureUse: true,
        );
    }

    protected function tearDown(): void
    {
        ApiRequestor::setHttpClient(CurlClient::instance());
        parent::tearDown();
    }

    public function test_paying_again_for_the_same_invoice_reuses_the_intent_and_customer(): void
    {
        $stripe = $this->fakeStripe();
        ApiRequestor::setHttpClient($stripe);

        $first = $this->gateway()->createCardPayment($this->cardRequest('1 Main St'));
        $second = $this->gateway()->createCardPayment($this->cardRequest('1 Main St'));

        $this->assertSame($first->id, $second->id);
        $this->assertCount(1, $stripe->customers, 'a repeat Pay click must not create another Stripe customer');
    }

    public function test_changed_customer_details_get_a_new_intent_instead_of_an_idempotency_error(): void
    {
        ApiRequestor::setHttpClient($this->fakeStripe());

        $first = $this->gateway()->createCardPayment($this->cardRequest('1 Main St'));
        $afterProfileEdit = $this->gateway()->createCardPayment($this->cardRequest('2 New Road'));

        $this->assertNotSame($first->id, $afterProfileEdit->id);
    }
}
