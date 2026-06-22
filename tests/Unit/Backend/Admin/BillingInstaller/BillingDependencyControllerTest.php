<?php

namespace Tests\Unit\Backend\Admin\BillingInstaller;

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
}
