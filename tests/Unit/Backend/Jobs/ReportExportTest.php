<?php

declare(strict_types=1);

namespace Tests\Unit\Backend\Jobs;

use App\Http\Controllers\Report\ConcreteExportHandleController;
use App\Jobs\ReportExport;
use Illuminate\Contracts\Queue\ShouldQueue;
use Mockery;
use Tests\TestCase;

class ReportExportTest extends TestCase
{
    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    private function makeJob(string $type): ReportExport
    {
        return new ReportExport($type, [], [], 'test@example.com');
    }

    private function injectMockController(ReportExport $job): ConcreteExportHandleController
    {
        $mock = Mockery::mock(ConcreteExportHandleController::class);
        $prop = (new \ReflectionClass($job))->getProperty('exportHandleController');
        $prop->setAccessible(true);
        $prop->setValue($job, $mock);

        return $mock;
    }

    public function test_implements_should_queue(): void
    {
        $this->assertInstanceOf(ShouldQueue::class, $this->makeJob('users'));
    }

    public function test_tries_is_five(): void
    {
        $this->assertSame(5, $this->makeJob('users')->tries);
    }

    public function test_timeout_is_300(): void
    {
        $this->assertSame(300, $this->makeJob('users')->timeout);
    }

    public function test_handle_dispatches_user_exports(): void
    {
        $job = $this->makeJob('users');
        $mock = $this->injectMockController($job);
        $called = false;
        $mock->shouldReceive('userExports')->once()->andReturnUsing(function () use (&$called) {
            $called = true;

            return response()->json([]);
        });

        $job->handle();

        $this->assertTrue($called, 'userExports was not called');
    }

    public function test_handle_dispatches_invoice_exports(): void
    {
        $job = $this->makeJob('invoices');
        $mock = $this->injectMockController($job);
        $called = false;
        $mock->shouldReceive('invoiceExports')->once()->andReturnUsing(function () use (&$called) {
            $called = true;

            return response()->json([]);
        });

        $job->handle();

        $this->assertTrue($called, 'invoiceExports was not called');
    }

    public function test_handle_dispatches_order_exports(): void
    {
        $job = $this->makeJob('orders');
        $mock = $this->injectMockController($job);
        $called = false;
        $mock->shouldReceive('orderExports')->once()->andReturnUsing(function () use (&$called) {
            $called = true;

            return response()->json([]);
        });

        $job->handle();

        $this->assertTrue($called, 'orderExports was not called');
    }

    public function test_handle_dispatches_tenant_exports(): void
    {
        $job = $this->makeJob('tenats');
        $mock = $this->injectMockController($job);
        $called = false;
        $mock->shouldReceive('tenantExports')->once()->andReturnUsing(function () use (&$called) {
            $called = true;
        });

        $job->handle();

        $this->assertTrue($called, 'tenantExports was not called');
    }

    public function test_handle_returns_early_for_unknown_report_type(): void
    {
        $job = $this->makeJob('unknown_type');
        $mock = $this->injectMockController($job);
        $mock->shouldNotReceive('userExports');
        $mock->shouldNotReceive('invoiceExports');
        $mock->shouldNotReceive('orderExports');
        $mock->shouldNotReceive('tenantExports');

        $job->handle();

        $this->assertTrue(true);
    }
}
