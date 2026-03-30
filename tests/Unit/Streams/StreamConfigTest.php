<?php

namespace Tests\Unit\Streams;

use App\Streams\StreamConfig;
use Tests\TestCase;

class StreamConfigTest extends TestCase
{
    private string $configPath;

    protected function setUp(): void
    {
        parent::setUp();
        $this->configPath = storage_path('app/stream_config.ini');

        // Backup existing config if present
        if (file_exists($this->configPath)) {
            copy($this->configPath, $this->configPath.'.bak');
        }
    }

    protected function tearDown(): void
    {
        // Restore backup
        if (file_exists($this->configPath.'.bak')) {
            rename($this->configPath.'.bak', $this->configPath);
        } elseif (file_exists($this->configPath)) {
            unlink($this->configPath);
        }

        parent::tearDown();
    }

    public function test_value_returns_config_value(): void
    {
        file_put_contents($this->configPath, "API_KEY=secret123\n");

        $result = StreamConfig::value('API_KEY');

        $this->assertEquals('secret123', $result);
    }

    public function test_value_returns_default_when_key_not_found(): void
    {
        file_put_contents($this->configPath, "OTHER_KEY=value\n");

        $result = StreamConfig::value('MISSING_KEY', 'fallback');

        $this->assertEquals('fallback', $result);
    }

    public function test_value_returns_default_when_key_is_empty_string(): void
    {
        file_put_contents($this->configPath, "EMPTY_KEY=\n");

        $result = StreamConfig::value('EMPTY_KEY', 'default_val');

        $this->assertEquals('default_val', $result);
    }

    public function test_value_returns_null_default_when_no_default_provided(): void
    {
        file_put_contents($this->configPath, '');

        $result = StreamConfig::value('NONEXISTENT');

        $this->assertNull($result);
    }

    public function test_all_returns_all_config_values(): void
    {
        file_put_contents($this->configPath, "KEY_ONE=value1\nKEY_TWO=value2\n");

        $result = StreamConfig::all();

        $this->assertEquals(['KEY_ONE' => 'value1', 'KEY_TWO' => 'value2'], $result);
    }

    public function test_all_returns_empty_array_when_config_is_empty(): void
    {
        file_put_contents($this->configPath, '');

        $result = StreamConfig::all();

        $this->assertEquals([], $result);
    }

    public function test_modify_saves_config_values(): void
    {
        StreamConfig::modify(['host' => 'localhost', 'port' => '6379']);

        $content = file_get_contents($this->configPath);

        $this->assertStringContainsString('HOST=localhost', $content);
        $this->assertStringContainsString('PORT=6379', $content);
    }

    public function test_modify_uppercases_keys(): void
    {
        StreamConfig::modify(['my_key' => 'my_value']);

        $content = file_get_contents($this->configPath);

        $this->assertStringContainsString('MY_KEY=my_value', $content);
        $this->assertStringNotContainsString('my_key=', $content);
    }

    public function test_modify_overwrites_existing_config(): void
    {
        file_put_contents($this->configPath, "OLD_KEY=old_value\n");

        StreamConfig::modify(['new_key' => 'new_value']);

        $content = file_get_contents($this->configPath);

        $this->assertStringNotContainsString('OLD_KEY', $content);
        $this->assertStringContainsString('NEW_KEY=new_value', $content);
    }

    public function test_value_creates_config_file_if_not_exists(): void
    {
        if (file_exists($this->configPath)) {
            unlink($this->configPath);
        }

        StreamConfig::value('ANY_KEY', 'default');

        $this->assertFileExists($this->configPath);
    }

    public function test_modify_then_value_roundtrip(): void
    {
        StreamConfig::modify(['redis_host' => '127.0.0.1', 'redis_port' => '6380']);

        $this->assertEquals('127.0.0.1', StreamConfig::value('REDIS_HOST'));
        $this->assertEquals('6380', StreamConfig::value('REDIS_PORT'));
    }
}
