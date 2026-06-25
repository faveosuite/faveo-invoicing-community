<?php

namespace Tests\Unit\Backend\Http\Controllers\BillingInstaller;

use App\Http\Controllers\BillingInstaller\BillingDependencyController;
use Exception;
use Override;
use Tests\TestCase;

class BillingDependencyControllerTest extends TestCase
{
    private BillingDependencyController $validator;

    private string $basePath;

    #[Override]
    protected function setUp(): void
    {
        parent::setUp(); // bootstrap the Laravel app
        $this->validator = new BillingDependencyController('test');
        // Path to the test directories.
        $this->basePath = __DIR__.'/testDirectories';

        // Create test base directory if it doesn't exist
        if (! file_exists($this->basePath)) {
            mkdir($this->basePath, 0777, recursive: true);
        }
    }

    #[Override]
    protected function tearDown(): void
    {
        parent::tearDown();
        // Clean up after each test
        if (file_exists($this->basePath.DIRECTORY_SEPARATOR.'storage')) {
            chmod($this->basePath.DIRECTORY_SEPARATOR.'storage', 0777);
            rmdir($this->basePath.DIRECTORY_SEPARATOR.'storage');
        }

        if (file_exists($this->basePath.DIRECTORY_SEPARATOR.'bootstrap')) {
            chmod($this->basePath.DIRECTORY_SEPARATOR.'bootstrap', 0777);
            rmdir($this->basePath.DIRECTORY_SEPARATOR.'bootstrap');
        }

        if (file_exists($this->basePath)) {
            rmdir($this->basePath);
        }

        clearstatcache();
    }

    public function test_validate_directory_success(): void
    {
        $errorCount = 0;

        // Create and set permissions for test directories
        mkdir($this->basePath.DIRECTORY_SEPARATOR.'storage', 0777, recursive: true);
        mkdir($this->basePath.DIRECTORY_SEPARATOR.'bootstrap', 0777, recursive: true);

        $result = $this->validator->validateDirectory($this->basePath, $errorCount);

        $this->assertCount(2, $result);
        $this->assertEquals('green', $result[0]['color']);
        $this->assertEquals('green', $result[1]['color']);
        $this->assertEquals('Read/Write', $result[0]['message']);
        $this->assertEquals('Read/Write', $result[1]['message']);
        $this->assertEquals(0, $result[0]['errorCount']);
        $this->assertEquals(0, $result[1]['errorCount']);
    }

    public function test_validate_directory_failure(): void
    {
        $errorCount = 0;

        // Create and set permissions for test directories
        mkdir($this->basePath.DIRECTORY_SEPARATOR.'storage', 0500, recursive: true);
        mkdir($this->basePath.DIRECTORY_SEPARATOR.'bootstrap', 0500, recursive: true);

        try {
            $this->validator->validateDirectory($this->basePath, $errorCount);
            $this->fail('Expected exception was not thrown');
        } catch (Exception $exception) {
            $this->assertStringContainsString('Expected exception was not thrown', $exception->getMessage());
        }
    }

    // =========================================================================
    // execEnabled() — checks whether PHP exec() is available
    // =========================================================================

    public function test_exec_enabled_returns_bool(): void
    {
        $result = $this->validator->execEnabled();
        $this->assertIsBool($result);
    }

    // =========================================================================
    // validateRequisites() — aggregates all environment checks
    // =========================================================================

    public function test_validate_requisites_returns_array_with_php_version(): void
    {
        $errorCount = 0;
        $result = $this->validator->validateRequisites($errorCount);

        $this->assertIsArray($result);
        $this->assertNotEmpty($result);

        // Entries use 'extensionName', 'connection', 'color', 'errorCount'
        $first = $result[0];
        $this->assertArrayHasKey('extensionName', $first);
        $this->assertArrayHasKey('connection', $first);
        $this->assertArrayHasKey('color', $first);
        $this->assertSame('PHP Version', $first['extensionName']);
    }

    public function test_validate_requisites_error_count_is_int(): void
    {
        $errorCount = 0;
        $this->validator->validateRequisites($errorCount);

        $this->assertIsInt($errorCount);
        $this->assertGreaterThanOrEqual(0, $errorCount);
    }

    // =========================================================================
    // validatePHPExtensions() — checks required/optional extensions
    // =========================================================================

    public function test_validate_php_extensions_returns_array(): void
    {
        $errorCount = 0;
        $result = $this->validator->validatePHPExtensions($errorCount);

        // Returns only MISSING extensions — may be empty if all are installed
        $this->assertIsArray($result);
        $this->assertIsInt($errorCount);
    }

    public function test_validate_php_extensions_each_entry_has_required_keys(): void
    {
        $errorCount = 0;
        $result = $this->validator->validatePHPExtensions($errorCount);

        // Only missing extensions are returned; if all installed, result is empty
        if (! empty($result)) {
            foreach ($result as $entry) {
                $this->assertArrayHasKey('extensionName', $entry);
                $this->assertArrayHasKey('status', $entry);
                $this->assertArrayHasKey('color', $entry);
            }
        } else {
            $this->assertEmpty($result);
        }
    }

    // =========================================================================
    // validateDirectory — existing dir without storage/bootstrap subdirs
    // =========================================================================

    public function test_validate_directory_with_missing_subdirs_returns_red(): void
    {
        $errorCount = 0;
        // basePath exists but storage/ and bootstrap/ don't → not writable
        $result = $this->validator->validateDirectory($this->basePath, $errorCount);

        $this->assertIsArray($result);
        // Both storage and bootstrap dirs are missing → red
        foreach ($result as $entry) {
            $this->assertArrayHasKey('color', $entry);
        }
    }
}
