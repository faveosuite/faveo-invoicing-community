<?php

namespace Tests\Unit\Backend\Console\Commands;

use App\Console\Commands\Install;
use Tests\TestCase;

/**
 * Tests for the Install Artisan command's public utility methods.
 * The handleAndLog() method is not tested (requires interactive CLI + DB setup).
 */
class InstallCommandTest extends TestCase
{
    private Install $command;

    protected function setUp(): void
    {
        parent::setUp();
        $this->command = new Install;
    }

    // -------------------------------------------------------------------------
    // formatAppUrl — pure string manipulation
    // -------------------------------------------------------------------------

    public function test_format_app_url_trims_trailing_slash(): void
    {
        $result = $this->command->formatAppUrl('https://example.com/');
        $this->assertSame('https://example.com', $result);
    }

    public function test_format_app_url_preserves_url_without_trailing_slash(): void
    {
        $result = $this->command->formatAppUrl('https://example.com');
        $this->assertSame('https://example.com', $result);
    }

    public function test_format_app_url_trims_multiple_trailing_slashes(): void
    {
        $result = $this->command->formatAppUrl('https://example.com///');
        $this->assertSame('https://example.com', $result);
    }

    public function test_format_app_url_trims_trailing_space_and_slash(): void
    {
        $result = $this->command->formatAppUrl('https://example.com/ ');
        $this->assertSame('https://example.com', $result);
    }

    // -------------------------------------------------------------------------
    // formatAppUrl edge cases
    // -------------------------------------------------------------------------

    public function test_format_app_url_with_path_preserves_path(): void
    {
        $result = $this->command->formatAppUrl('https://example.com/billing');
        $this->assertSame('https://example.com/billing', $result);
    }

    public function test_format_app_url_with_path_and_trailing_slash(): void
    {
        $result = $this->command->formatAppUrl('https://example.com/billing/');
        $this->assertSame('https://example.com/billing', $result);
    }
}
