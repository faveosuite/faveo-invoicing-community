<?php

declare(strict_types=1);

namespace Tests\Unit\Backend\Jobs;

use App\Jobs\DeleteExpiredCloudInstances;
use App\Services\Cloud\ExpiredCloudInstanceCleaner;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Mockery;
use Mockery\MockInterface;
use Tests\TestCase;

class DeleteExpiredCloudInstancesTest extends TestCase
{
    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    public function test_implements_should_queue(): void
    {
        $this->assertInstanceOf(ShouldQueue::class, new DeleteExpiredCloudInstances());
    }

    public function test_is_unique_so_runs_cannot_overlap(): void
    {
        // Overlapping runs would email the same customers twice.
        $job = new DeleteExpiredCloudInstances();

        $this->assertInstanceOf(ShouldBeUnique::class, $job);
        $this->assertSame(3600, $job->uniqueFor);
    }

    public function test_handle_runs_the_cleanup(): void
    {
        /** @var ExpiredCloudInstanceCleaner&MockInterface $cleaner */
        $cleaner = Mockery::mock(ExpiredCloudInstanceCleaner::class);
        $cleaner->shouldReceive('__invoke')->once();

        (new DeleteExpiredCloudInstances())->handle($cleaner);
        $this->addToAssertionCount(1);
    }

    public function test_handle_accepts_injected_cleaner(): void
    {
        /** @var ExpiredCloudInstanceCleaner&MockInterface $cleaner */
        $cleaner = Mockery::mock(ExpiredCloudInstanceCleaner::class);
        $cleaner->shouldReceive('__invoke')->once();

        // Should not throw — constructor-less job, dependency injected via handle()
        (new DeleteExpiredCloudInstances())->handle($cleaner);
        $this->addToAssertionCount(1);
    }
}
