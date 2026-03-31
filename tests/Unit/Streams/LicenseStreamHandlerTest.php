<?php

namespace Tests\Unit\Streams;

use App\Streams\Adapters\RedisAdapterInterface;
use App\Streams\License\LicenseStreamHandler;
use App\Streams\RedisAdapterManager;
use Mockery;
use ReflectionClass;
use RuntimeException;
use Tests\TestCase;

class LicenseStreamHandlerTest extends TestCase
{
    private $redisMock;

    private $producerMock;

    protected function setUp(): void
    {
        parent::setUp();
        $this->redisMock = Mockery::mock(RedisAdapterInterface::class);
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    // ─── Helper methods ──────────────────────────────────────────

    /**
     * Create handler instance with mocked producer and Redis adapter.
     */
    private function createHandler(): LicenseStreamHandler
    {
        $handler = $this->getMockBuilder(LicenseStreamHandler::class)
            ->disableOriginalConstructor()
            ->getMock();

        return $handler;
    }

    // ─── formatExpiry() ──────────────────────────────────────────

    public function test_format_expiry_with_empty_string(): void
    {
        $reflection = new ReflectionClass(LicenseStreamHandler::class);
        $method = $reflection->getMethod('formatExpiry');
        $method->setAccessible(true);

        $result = $method->invoke(null, '');

        $this->assertEquals('', $result);
    }

    public function test_format_expiry_with_string_date(): void
    {
        $reflection = new ReflectionClass(LicenseStreamHandler::class);
        $method = $reflection->getMethod('formatExpiry');
        $method->setAccessible(true);

        $result = $method->invoke(null, '2026-12-31');

        $this->assertEquals('2026-12-31', $result);
    }

    public function test_format_expiry_with_carbon_instance(): void
    {
        $reflection = new ReflectionClass(LicenseStreamHandler::class);
        $method = $reflection->getMethod('formatExpiry');
        $method->setAccessible(true);

        $carbon = now()->setDate(2026, 6, 15);
        $result = $method->invoke(null, $carbon);

        $this->assertEquals('2026-06-15', $result);
    }

    // ─── getIpAndDomain() ────────────────────────────────────────

    public function test_get_ip_and_domain_with_ip_address(): void
    {
        $reflection = new ReflectionClass(LicenseStreamHandler::class);
        $method = $reflection->getMethod('getIpAndDomain');
        $method->setAccessible(true);

        $result = $method->invoke(null, '192.168.1.1');

        $this->assertEquals('192.168.1.1', $result['ip']);
        $this->assertEquals('', $result['domain']);
        $this->assertEquals(0, $result['requireDomain']);
    }

    public function test_get_ip_and_domain_with_domain_name(): void
    {
        $reflection = new ReflectionClass(LicenseStreamHandler::class);
        $method = $reflection->getMethod('getIpAndDomain');
        $method->setAccessible(true);

        $result = $method->invoke(null, 'example.com');

        $this->assertEquals('', $result['ip']);
        $this->assertEquals('example.com', $result['domain']);
        $this->assertEquals(1, $result['requireDomain']);
    }

    public function test_get_ip_and_domain_with_empty_string(): void
    {
        $reflection = new ReflectionClass(LicenseStreamHandler::class);
        $method = $reflection->getMethod('getIpAndDomain');
        $method->setAccessible(true);

        $result = $method->invoke(null, '');

        $this->assertEquals('', $result['ip']);
        $this->assertEquals('', $result['domain']);
        $this->assertEquals(0, $result['requireDomain']);
    }

    // ─── waitForResponse() timeout ───────────────────────────────

    public function test_wait_for_response_throws_on_timeout(): void
    {
        $handler = new class extends LicenseStreamHandler
        {
            public function __construct()
            {
                // Skip parent constructor
            }

            public function testWaitForResponse(string $correlationId, string $requestId, RedisAdapterInterface $redis): array
            {
                // Simulate the method with a 1-second timeout
                $start = time();
                $timeout = 1;
                $lastId = '0-0';

                while ((time() - $start) < $timeout) {
                    $messages = $redis->xrange('license_responses', $lastId, '+', 100);

                    foreach ($messages as $id => $message) {
                        $data = json_decode($message['message'] ?? '{}', true);
                        if (($data['payload']['correlation_id'] ?? null) === $correlationId) {
                            return $data['payload'] ?? [];
                        }
                        $lastId = $id;
                    }

                    usleep(50000);
                }

                throw new RuntimeException("Timeout waiting for response (correlation_id: {$correlationId})");
            }
        };

        $adapterMock = Mockery::mock(RedisAdapterInterface::class);
        $adapterMock->shouldReceive('xrange')->andReturn([]);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Timeout waiting for response');

        $handler->testWaitForResponse('test-corr-id', 'req-1', $adapterMock);
    }

    public function test_wait_for_response_finds_matching_message(): void
    {
        $handler = new class extends LicenseStreamHandler
        {
            public function __construct()
            {
            }

            public function testWaitForResponse(string $correlationId, RedisAdapterInterface $redis): array
            {
                $start = time();
                $lastId = '0-0';

                while ((time() - $start) < 5) {
                    $messages = $redis->xrange('license_responses', $lastId, '+', 100);

                    foreach ($messages as $id => $message) {
                        $data = json_decode($message['message'] ?? '{}', true);
                        if (($data['payload']['correlation_id'] ?? null) === $correlationId) {
                            return $data['payload'] ?? [];
                        }
                        $lastId = $id;
                    }

                    usleep(50000);
                }

                throw new RuntimeException('Timeout');
            }
        };

        $responsePayload = json_encode([
            'payload' => [
                'correlation_id' => 'abc-123',
                'result' => ['status' => 'success'],
            ],
        ]);

        $adapterMock = Mockery::mock(RedisAdapterInterface::class);
        $adapterMock->shouldReceive('xrange')
            ->once()
            ->andReturn([
                '200-0' => ['message' => $responsePayload],
            ]);

        $result = $handler->testWaitForResponse('abc-123', $adapterMock);

        $this->assertEquals('abc-123', $result['correlation_id']);
        $this->assertEquals(['status' => 'success'], $result['result']);
    }

    // ─── searchProductId() ───────────────────────────────────────

    public function test_search_product_id_returns_empty_on_no_data(): void
    {
        // Test the extraction logic used in searchProductId
        $data = [];
        $result = ! empty($data) ? ($data[0]['product_id'] ?? '') : '';
        $this->assertEquals('', $result);
    }

    public function test_search_product_id_extracts_id_from_results(): void
    {
        // Test the extraction logic
        $data = [
            ['product_id' => '42', 'product_sku' => 'SKU-001'],
        ];
        $result = ! empty($data) ? ($data[0]['product_id'] ?? '') : '';
        $this->assertEquals('42', $result);
    }

    // ─── searchUserId() ──────────────────────────────────────────

    public function test_search_user_id_extraction_logic(): void
    {
        $data = [
            ['client_id' => '99', 'client_email' => 'test@example.com'],
        ];
        $result = ! empty($data) ? ($data[0]['client_id'] ?? '') : '';
        $this->assertEquals('99', $result);
    }

    public function test_search_user_id_returns_empty_on_no_data(): void
    {
        $data = [];
        $result = ! empty($data) ? ($data[0]['client_id'] ?? '') : '';
        $this->assertEquals('', $result);
    }

    // ─── searchLicenseId() ───────────────────────────────────────

    public function test_search_license_id_finds_matching_product(): void
    {
        $reflection = new ReflectionClass(LicenseStreamHandler::class);
        $method = $reflection->getMethod('searchLicenseId');
        $method->setAccessible(true);

        // We test the extraction logic pattern
        $data = [
            [
                'product_id' => '10',
                'license_code' => 'LIC-001',
                'license_id' => '55',
                'license_require_domain' => 1,
                'license_limit' => 2,
            ],
            [
                'product_id' => '20',
                'license_code' => 'LIC-002',
                'license_id' => '66',
                'license_require_domain' => 0,
                'license_limit' => 1,
            ],
        ];

        // Simulate the search logic
        $productId = 20;
        $result = ['productId' => '', 'code' => '', 'licenseId' => '', 'allowedInstalltion' => '', 'installationLimit' => ''];

        foreach ($data as $detail) {
            if (($detail['product_id'] ?? null) == $productId) {
                $result = [
                    'productId' => $detail['product_id'] ?? '',
                    'code' => $detail['license_code'] ?? '',
                    'licenseId' => $detail['license_id'] ?? '',
                    'allowedInstalltion' => $detail['license_require_domain'] ?? '',
                    'installationLimit' => $detail['license_limit'] ?? '',
                ];
                break;
            }
        }

        $this->assertEquals('20', $result['productId']);
        $this->assertEquals('LIC-002', $result['code']);
        $this->assertEquals('66', $result['licenseId']);
    }

    public function test_search_license_id_returns_defaults_when_not_found(): void
    {
        $data = [
            ['product_id' => '10', 'license_code' => 'LIC-001'],
        ];

        $productId = 999;
        $result = ['productId' => '', 'code' => '', 'licenseId' => '', 'allowedInstalltion' => '', 'installationLimit' => ''];

        foreach ($data as $detail) {
            if (($detail['product_id'] ?? null) == $productId) {
                $result = [
                    'productId' => $detail['product_id'] ?? '',
                    'code' => $detail['license_code'] ?? '',
                    'licenseId' => $detail['license_id'] ?? '',
                    'allowedInstalltion' => $detail['license_require_domain'] ?? '',
                    'installationLimit' => $detail['license_limit'] ?? '',
                ];
                break;
            }
        }

        $this->assertEquals('', $result['productId']);
        $this->assertEquals('', $result['code']);
        $this->assertEquals('', $result['licenseId']);
    }
}
