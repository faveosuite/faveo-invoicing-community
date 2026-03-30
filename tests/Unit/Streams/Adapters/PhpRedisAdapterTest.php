<?php

namespace Tests\Unit\Streams\Adapters;

use App\Streams\Adapters\PhpRedisAdapter;
use Illuminate\Support\Facades\Redis;
use Mockery;
use Tests\TestCase;

class PhpRedisAdapterTest extends TestCase
{
    private PhpRedisAdapter $adapter;

    private $connectionMock;

    protected function setUp(): void
    {
        parent::setUp();
        $this->adapter = new PhpRedisAdapter;
        $this->connectionMock = Mockery::mock();

        Redis::shouldReceive('connection')
            ->with('streams')
            ->andReturn($this->connectionMock);
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    // ─── connection() ────────────────────────────────────────────

    public function test_connection_delegates_to_redis_facade(): void
    {
        $mock = Mockery::mock();
        Redis::shouldReceive('connection')->with('custom')->once()->andReturn($mock);

        $result = $this->adapter->connection('custom');

        $this->assertSame($mock, $result);
    }

    public function test_connection_with_null_uses_default(): void
    {
        $mock = Mockery::mock();
        Redis::shouldReceive('connection')->with(null)->once()->andReturn($mock);

        $result = $this->adapter->connection(null);

        $this->assertSame($mock, $result);
    }

    // ─── xadd() ──────────────────────────────────────────────────

    public function test_xadd_without_options(): void
    {
        $this->connectionMock
            ->shouldReceive('xAdd')
            ->once()
            ->with('my-stream', '*', ['key' => 'val'], 0, false)
            ->andReturn('1234-0');

        $result = $this->adapter->xadd('my-stream', '*', ['key' => 'val']);

        $this->assertEquals('1234-0', $result);
    }

    public function test_xadd_with_approximate_maxlen(): void
    {
        $this->connectionMock
            ->shouldReceive('xAdd')
            ->once()
            ->with('my-stream', '*', ['k' => 'v'], 1000, true)
            ->andReturn('1234-0');

        $result = $this->adapter->xadd('my-stream', '*', ['k' => 'v'], [
            'MAXLEN' => ['~', 1000],
        ]);

        $this->assertEquals('1234-0', $result);
    }

    public function test_xadd_with_exact_maxlen(): void
    {
        $this->connectionMock
            ->shouldReceive('xAdd')
            ->once()
            ->with('my-stream', '*', ['k' => 'v'], 500, false)
            ->andReturn('1234-0');

        $result = $this->adapter->xadd('my-stream', '*', ['k' => 'v'], [
            'MAXLEN' => ['', 500],
        ]);

        $this->assertEquals('1234-0', $result);
    }

    // ─── del() ───────────────────────────────────────────────────

    public function test_del_deletes_stream(): void
    {
        $this->connectionMock
            ->shouldReceive('del')
            ->once()
            ->with('my-stream')
            ->andReturn(1);

        $result = $this->adapter->del('my-stream');

        $this->assertEquals(1, $result);
    }

    // ─── xrange() ────────────────────────────────────────────────

    public function test_xrange_with_count(): void
    {
        $expected = ['100-0' => ['key' => 'val']];

        $this->connectionMock
            ->shouldReceive('xRange')
            ->once()
            ->with('my-stream', '-', '+', 50)
            ->andReturn($expected);

        $result = $this->adapter->xrange('my-stream', '-', '+', 50);

        $this->assertEquals($expected, $result);
    }

    public function test_xrange_without_count(): void
    {
        $expected = ['100-0' => ['key' => 'val']];

        $this->connectionMock
            ->shouldReceive('xRange')
            ->once()
            ->with('my-stream', '-', '+', -1)
            ->andReturn($expected);

        $result = $this->adapter->xrange('my-stream', '-', '+');

        $this->assertEquals($expected, $result);
    }

    public function test_xrange_returns_empty_array_on_false(): void
    {
        $this->connectionMock
            ->shouldReceive('xRange')
            ->once()
            ->andReturn(false);

        $result = $this->adapter->xrange('my-stream', '-', '+');

        $this->assertEquals([], $result);
    }

    // ─── xgroup() ────────────────────────────────────────────────

    public function test_xgroup_creates_group(): void
    {
        $this->connectionMock
            ->shouldReceive('xGroup')
            ->once()
            ->with('CREATE', 'my-stream', 'my-group', '0', true)
            ->andReturn(true);

        $result = $this->adapter->xgroup('CREATE', 'my-stream', 'my-group', '0', true);

        $this->assertTrue($result);
    }

    public function test_xgroup_without_mkstream(): void
    {
        $this->connectionMock
            ->shouldReceive('xGroup')
            ->once()
            ->with('CREATE', 'my-stream', 'my-group', '$', false)
            ->andReturn(true);

        $result = $this->adapter->xgroup('CREATE', 'my-stream', 'my-group', '$');

        $this->assertTrue($result);
    }

    // ─── xreadgroup() ────────────────────────────────────────────

    public function test_xreadgroup_without_block(): void
    {
        $expected = ['my-stream' => ['100-0' => ['key' => 'val']]];

        $this->connectionMock
            ->shouldReceive('xReadGroup')
            ->once()
            ->with('grp', 'consumer1', ['my-stream' => '>'], 10)
            ->andReturn($expected);

        $result = $this->adapter->xreadgroup('grp', 'consumer1', ['my-stream' => '>'], 10);

        $this->assertEquals($expected, $result);
    }

    public function test_xreadgroup_with_block(): void
    {
        $expected = ['my-stream' => ['100-0' => ['key' => 'val']]];

        $this->connectionMock
            ->shouldReceive('xReadGroup')
            ->once()
            ->with('grp', 'consumer1', ['my-stream' => '>'], 10, 5000)
            ->andReturn($expected);

        $result = $this->adapter->xreadgroup('grp', 'consumer1', ['my-stream' => '>'], 10, 5000);

        $this->assertEquals($expected, $result);
    }

    public function test_xreadgroup_returns_empty_on_false(): void
    {
        $this->connectionMock
            ->shouldReceive('xReadGroup')
            ->once()
            ->andReturn(false);

        $result = $this->adapter->xreadgroup('grp', 'c', ['s' => '>'], 10);

        $this->assertEquals([], $result);
    }

    // ─── xack() ──────────────────────────────────────────────────

    public function test_xack_acknowledges_messages(): void
    {
        $this->connectionMock
            ->shouldReceive('xAck')
            ->once()
            ->with('my-stream', 'my-group', ['100-0', '100-1'])
            ->andReturn(2);

        $result = $this->adapter->xack('my-stream', 'my-group', ['100-0', '100-1']);

        $this->assertEquals(2, $result);
    }

    // ─── xpending() ─────────────────────────────────────────────

    public function test_xpending_returns_pending_messages(): void
    {
        $expected = [['100-0', 'consumer1', 30000, 2]];

        $this->connectionMock
            ->shouldReceive('xPending')
            ->once()
            ->with('my-stream', 'grp', '-', '+', 10, null)
            ->andReturn($expected);

        $result = $this->adapter->xpending('my-stream', 'grp', '-', '+', 10);

        $this->assertEquals($expected, $result);
    }

    public function test_xpending_with_consumer_filter(): void
    {
        $expected = [['100-0', 'consumer1', 30000, 1]];

        $this->connectionMock
            ->shouldReceive('xPending')
            ->once()
            ->with('my-stream', 'grp', '-', '+', 5, 'consumer1')
            ->andReturn($expected);

        $result = $this->adapter->xpending('my-stream', 'grp', '-', '+', 5, 'consumer1');

        $this->assertEquals($expected, $result);
    }

    public function test_xpending_returns_empty_on_false(): void
    {
        $this->connectionMock
            ->shouldReceive('xPending')
            ->once()
            ->andReturn(false);

        $result = $this->adapter->xpending('s', 'g', '-', '+', 10);

        $this->assertEquals([], $result);
    }

    // ─── xclaim() ────────────────────────────────────────────────

    public function test_xclaim_claims_messages(): void
    {
        $expected = ['100-0' => ['key' => 'val']];

        $this->connectionMock
            ->shouldReceive('xClaim')
            ->once()
            ->with('my-stream', 'grp', 'consumer1', 60000, ['100-0'], [])
            ->andReturn($expected);

        $result = $this->adapter->xclaim('my-stream', 'grp', 'consumer1', 60000, ['100-0']);

        $this->assertEquals($expected, $result);
    }

    public function test_xclaim_returns_empty_on_false(): void
    {
        $this->connectionMock
            ->shouldReceive('xClaim')
            ->once()
            ->andReturn(false);

        $result = $this->adapter->xclaim('s', 'g', 'c', 1000, ['id']);

        $this->assertEquals([], $result);
    }

    // ─── xdel() ──────────────────────────────────────────────────

    public function test_xdel_deletes_messages(): void
    {
        $this->connectionMock
            ->shouldReceive('xDel')
            ->once()
            ->with('my-stream', ['100-0', '100-1'])
            ->andReturn(2);

        $result = $this->adapter->xdel('my-stream', ['100-0', '100-1']);

        $this->assertEquals(2, $result);
    }

    // ─── xtrim() ─────────────────────────────────────────────────

    public function test_xtrim_approximate(): void
    {
        $this->connectionMock
            ->shouldReceive('xTrim')
            ->once()
            ->with('my-stream', 1000, true)
            ->andReturn(50);

        $result = $this->adapter->xtrim('my-stream', '~', 1000);

        $this->assertEquals(50, $result);
    }

    public function test_xtrim_exact(): void
    {
        $this->connectionMock
            ->shouldReceive('xTrim')
            ->once()
            ->with('my-stream', 500, false)
            ->andReturn(25);

        $result = $this->adapter->xtrim('my-stream', '', 500);

        $this->assertEquals(25, $result);
    }

    // ─── xinfo() ─────────────────────────────────────────────────

    public function test_xinfo_returns_stream_info(): void
    {
        $expected = ['length' => 100, 'first-entry' => ['100-0']];

        $this->connectionMock
            ->shouldReceive('xInfo')
            ->once()
            ->with('STREAM', 'my-stream')
            ->andReturn($expected);

        $result = $this->adapter->xinfo('STREAM', 'my-stream');

        $this->assertEquals($expected, $result);
    }

    public function test_xinfo_returns_empty_on_false(): void
    {
        $this->connectionMock
            ->shouldReceive('xInfo')
            ->once()
            ->andReturn(false);

        $result = $this->adapter->xinfo('STREAM', 'my-stream');

        $this->assertEquals([], $result);
    }

    public function test_xinfo_passes_additional_args(): void
    {
        $this->connectionMock
            ->shouldReceive('xInfo')
            ->once()
            ->with('CONSUMERS', 'my-stream', 'my-group')
            ->andReturn([]);

        $result = $this->adapter->xinfo('CONSUMERS', 'my-stream', 'my-group');

        $this->assertEquals([], $result);
    }

    // ─── pipeline() ──────────────────────────────────────────────

    public function test_pipeline_returns_pipeline_instance(): void
    {
        $pipelineMock = Mockery::mock();

        $this->connectionMock
            ->shouldReceive('pipeline')
            ->once()
            ->andReturn($pipelineMock);

        $result = $this->adapter->pipeline();

        $this->assertSame($pipelineMock, $result);
    }
}
