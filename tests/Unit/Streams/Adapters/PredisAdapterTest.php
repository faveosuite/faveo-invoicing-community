<?php

namespace Tests\Unit\Streams\Adapters;

use App\Streams\Adapters\PredisAdapter;
use Illuminate\Support\Facades\Redis;
use Mockery;
use ReflectionClass;
use Tests\TestCase;

class PredisAdapterTest extends TestCase
{
    private $adapter;

    private $connectionMock;

    /**
     * Captured args from the last executeRaw call.
     */
    private ?array $capturedArgs = null;

    protected function setUp(): void
    {
        parent::setUp();
        $this->connectionMock = Mockery::mock();

        Redis::shouldReceive('connection')
            ->with('streams')
            ->andReturn($this->connectionMock);

        // Use a partial mock so we can override executeRaw to avoid needing Predis library
        $this->adapter = Mockery::mock(PredisAdapter::class)->makePartial()->shouldAllowMockingProtectedMethods();
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    private function expectExecuteRaw(array $expectedArgs, $returnValue = null): void
    {
        $this->adapter
            ->shouldReceive('executeRaw')
            ->once()
            ->with($expectedArgs)
            ->andReturn($returnValue);
    }

    // ─── connection() ────────────────────────────────────────────

    public function test_connection_delegates_to_redis_facade(): void
    {
        $mock = Mockery::mock();
        Redis::shouldReceive('connection')->with('custom')->once()->andReturn($mock);

        $result = $this->adapter->connection('custom');

        $this->assertSame($mock, $result);
    }

    // ─── xadd() ──────────────────────────────────────────────────

    public function test_xadd_without_options(): void
    {
        $this->expectExecuteRaw(['XADD', 'my-stream', '*', 'key', 'val'], '1234-0');

        $result = $this->adapter->xadd('my-stream', '*', ['key' => 'val']);

        $this->assertEquals('1234-0', $result);
    }

    public function test_xadd_with_approximate_maxlen(): void
    {
        $this->expectExecuteRaw(
            ['XADD', 'my-stream', 'MAXLEN', '~', '1000', '*', 'k', 'v'],
            '1234-0'
        );

        $result = $this->adapter->xadd('my-stream', '*', ['k' => 'v'], [
            'MAXLEN' => ['~', 1000],
        ]);

        $this->assertEquals('1234-0', $result);
    }

    public function test_xadd_with_exact_maxlen(): void
    {
        $this->expectExecuteRaw(
            ['XADD', 'my-stream', 'MAXLEN', '500', '*', 'k', 'v'],
            '1234-0'
        );

        $result = $this->adapter->xadd('my-stream', '*', ['k' => 'v'], [
            'MAXLEN' => ['', 500],
        ]);

        $this->assertEquals('1234-0', $result);
    }

    // ─── del() ───────────────────────────────────────────────────

    public function test_del_deletes_stream(): void
    {
        $this->expectExecuteRaw(['DEL', 'my-stream'], 1);

        $result = $this->adapter->del('my-stream');

        $this->assertEquals(1, $result);
    }

    // ─── xrange() ────────────────────────────────────────────────

    public function test_xrange_without_count(): void
    {
        $raw = [
            ['100-0', ['field1', 'value1', 'field2', 'value2']],
        ];

        $this->expectExecuteRaw(['XRANGE', 'my-stream', '-', '+'], $raw);

        $result = $this->adapter->xrange('my-stream', '-', '+');

        $this->assertEquals(['100-0' => ['field1' => 'value1', 'field2' => 'value2']], $result);
    }

    public function test_xrange_with_count(): void
    {
        $raw = [
            ['100-0', ['k', 'v']],
        ];

        $this->expectExecuteRaw(['XRANGE', 'my-stream', '-', '+', 'COUNT', '50'], $raw);

        $result = $this->adapter->xrange('my-stream', '-', '+', 50);

        $this->assertEquals(['100-0' => ['k' => 'v']], $result);
    }

    public function test_xrange_returns_empty_on_null(): void
    {
        $this->expectExecuteRaw(['XRANGE', 'my-stream', '-', '+'], null);

        $result = $this->adapter->xrange('my-stream', '-', '+');

        $this->assertEquals([], $result);
    }

    // ─── xgroup() ────────────────────────────────────────────────

    public function test_xgroup_create_with_mkstream(): void
    {
        $this->expectExecuteRaw(
            ['XGROUP', 'CREATE', 'my-stream', 'grp', '0', 'MKSTREAM'],
            'OK'
        );

        $result = $this->adapter->xgroup('CREATE', 'my-stream', 'grp', '0', true);

        $this->assertEquals('OK', $result);
    }

    public function test_xgroup_create_without_mkstream(): void
    {
        $this->expectExecuteRaw(
            ['XGROUP', 'CREATE', 'my-stream', 'grp', '$'],
            'OK'
        );

        $result = $this->adapter->xgroup('CREATE', 'my-stream', 'grp', '$');

        $this->assertEquals('OK', $result);
    }

    // ─── xreadgroup() ────────────────────────────────────────────

    public function test_xreadgroup_without_block(): void
    {
        $raw = [
            ['my-stream', [['100-0', ['field', 'value']]]],
        ];

        $this->expectExecuteRaw(
            ['XREADGROUP', 'GROUP', 'grp', 'consumer1', 'COUNT', '10', 'STREAMS', 'my-stream', '>'],
            $raw
        );

        $result = $this->adapter->xreadgroup('grp', 'consumer1', ['my-stream' => '>'], 10);

        $this->assertEquals(['my-stream' => ['100-0' => ['field' => 'value']]], $result);
    }

    public function test_xreadgroup_with_block(): void
    {
        $raw = [
            ['my-stream', [['100-0', ['f', 'v']]]],
        ];

        $this->expectExecuteRaw(
            ['XREADGROUP', 'GROUP', 'grp', 'c1', 'BLOCK', '5000', 'COUNT', '10', 'STREAMS', 'my-stream', '>'],
            $raw
        );

        $result = $this->adapter->xreadgroup('grp', 'c1', ['my-stream' => '>'], 10, 5000);

        $this->assertEquals(['my-stream' => ['100-0' => ['f' => 'v']]], $result);
    }

    public function test_xreadgroup_returns_empty_on_null(): void
    {
        $this->expectExecuteRaw(
            ['XREADGROUP', 'GROUP', 'grp', 'c', 'COUNT', '10', 'STREAMS', 's', '>'],
            null
        );

        $result = $this->adapter->xreadgroup('grp', 'c', ['s' => '>'], 10);

        $this->assertEquals([], $result);
    }

    // ─── xack() ──────────────────────────────────────────────────

    public function test_xack_acknowledges_messages(): void
    {
        $this->expectExecuteRaw(['XACK', 'my-stream', 'grp', '100-0', '100-1'], 2);

        $result = $this->adapter->xack('my-stream', 'grp', ['100-0', '100-1']);

        $this->assertEquals(2, $result);
    }

    // ─── xpending() ─────────────────────────────────────────────

    public function test_xpending_without_consumer(): void
    {
        $expected = [['100-0', 'consumer1', 30000, 2]];

        $this->expectExecuteRaw(
            ['XPENDING', 'my-stream', 'grp', '-', '+', '10'],
            $expected
        );

        $result = $this->adapter->xpending('my-stream', 'grp', '-', '+', 10);

        $this->assertEquals($expected, $result);
    }

    public function test_xpending_with_consumer(): void
    {
        $expected = [['100-0', 'c1', 30000, 1]];

        $this->expectExecuteRaw(
            ['XPENDING', 'my-stream', 'grp', '-', '+', '5', 'c1'],
            $expected
        );

        $result = $this->adapter->xpending('my-stream', 'grp', '-', '+', 5, 'c1');

        $this->assertEquals($expected, $result);
    }

    public function test_xpending_returns_empty_on_false(): void
    {
        $this->expectExecuteRaw(
            ['XPENDING', 'my-stream', 'grp', '-', '+', '10'],
            false
        );

        $result = $this->adapter->xpending('my-stream', 'grp', '-', '+', 10);

        $this->assertEquals([], $result);
    }

    // ─── xclaim() ────────────────────────────────────────────────

    public function test_xclaim_claims_messages(): void
    {
        $raw = [
            ['100-0', ['key', 'val']],
        ];

        $this->expectExecuteRaw(
            ['XCLAIM', 'my-stream', 'grp', 'c1', '60000', '100-0'],
            $raw
        );

        $result = $this->adapter->xclaim('my-stream', 'grp', 'c1', 60000, ['100-0']);

        $this->assertEquals(['100-0' => ['key' => 'val']], $result);
    }

    public function test_xclaim_with_options(): void
    {
        $raw = [['100-0', ['k', 'v']]];

        $this->adapter
            ->shouldReceive('executeRaw')
            ->once()
            ->withArgs(function (array $args) {
                return in_array('IDLE', $args) && in_array('30000', $args);
            })
            ->andReturn($raw);

        $result = $this->adapter->xclaim('s', 'g', 'c', 1000, ['100-0'], ['IDLE' => 30000]);

        $this->assertEquals(['100-0' => ['k' => 'v']], $result);
    }

    // ─── xdel() ──────────────────────────────────────────────────

    public function test_xdel_deletes_messages(): void
    {
        $this->expectExecuteRaw(['XDEL', 'my-stream', '100-0', '100-1'], 2);

        $result = $this->adapter->xdel('my-stream', ['100-0', '100-1']);

        $this->assertEquals(2, $result);
    }

    // ─── xtrim() ─────────────────────────────────────────────────

    public function test_xtrim_approximate(): void
    {
        $this->expectExecuteRaw(['XTRIM', 'my-stream', 'MAXLEN', '~', '1000'], 50);

        $result = $this->adapter->xtrim('my-stream', '~', 1000);

        $this->assertEquals(50, $result);
    }

    public function test_xtrim_exact(): void
    {
        $this->expectExecuteRaw(['XTRIM', 'my-stream', 'MAXLEN', '500'], 25);

        $result = $this->adapter->xtrim('my-stream', '', 500);

        $this->assertEquals(25, $result);
    }

    // ─── xinfo() ─────────────────────────────────────────────────

    public function test_xinfo_returns_stream_info(): void
    {
        $expected = ['length', 100];

        $this->expectExecuteRaw(['XINFO', 'STREAM', 'my-stream'], $expected);

        $result = $this->adapter->xinfo('STREAM', 'my-stream');

        $this->assertEquals($expected, $result);
    }

    public function test_xinfo_with_extra_args(): void
    {
        $this->expectExecuteRaw(['XINFO', 'CONSUMERS', 'my-stream', 'grp'], []);

        $result = $this->adapter->xinfo('CONSUMERS', 'my-stream', 'grp');

        $this->assertEquals([], $result);
    }

    public function test_xinfo_returns_empty_on_false(): void
    {
        $this->expectExecuteRaw(['XINFO', 'STREAM', 'my-stream'], false);

        $result = $this->adapter->xinfo('STREAM', 'my-stream');

        $this->assertEquals([], $result);
    }

    // ─── pipeline() ──────────────────────────────────────────────

    public function test_pipeline_returns_pipeline(): void
    {
        $pipelineMock = Mockery::mock();

        $this->connectionMock
            ->shouldReceive('pipeline')
            ->once()
            ->andReturn($pipelineMock);

        $result = $this->adapter->pipeline();

        $this->assertSame($pipelineMock, $result);
    }

    // ─── parseStreamEntries() ────────────────────────────────────

    public function test_parse_stream_entries_converts_raw_to_associative(): void
    {
        $adapter = new PredisAdapter;
        $reflection = new ReflectionClass(PredisAdapter::class);
        $method = $reflection->getMethod('parseStreamEntries');
        $method->setAccessible(true);

        $raw = [
            ['id-1', ['field1', 'value1', 'field2', 'value2']],
            ['id-2', ['name', 'John', 'age', '30']],
        ];

        $result = $method->invoke($adapter, $raw);

        $this->assertEquals([
            'id-1' => ['field1' => 'value1', 'field2' => 'value2'],
            'id-2' => ['name' => 'John', 'age' => '30'],
        ], $result);
    }

    public function test_parse_stream_entries_returns_empty_for_null(): void
    {
        $adapter = new PredisAdapter;
        $reflection = new ReflectionClass(PredisAdapter::class);
        $method = $reflection->getMethod('parseStreamEntries');
        $method->setAccessible(true);

        $result = $method->invoke($adapter, null);

        $this->assertEquals([], $result);
    }

    public function test_parse_stream_entries_returns_empty_for_empty_array(): void
    {
        $adapter = new PredisAdapter;
        $reflection = new ReflectionClass(PredisAdapter::class);
        $method = $reflection->getMethod('parseStreamEntries');
        $method->setAccessible(true);

        $result = $method->invoke($adapter, []);

        $this->assertEquals([], $result);
    }
}
