<?php

namespace Tests\Unit\Backend\Services\Cloud;

use App\Model\Mailjob\Condition;
use App\Model\Product\Subscription;
use App\Services\Cloud\ExpiredCloudInstanceCleaner;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\DBTestCase;

class ExpiredCloudInstanceCleanerTest extends DBTestCase
{
    use DatabaseTransactions;

    public function test_does_nothing_when_the_cloud_task_is_off(): void
    {
        Condition::where('job', 'cloud')->update(['active' => false]);
        $notDeleted = Subscription::where('is_deleted', 0)->count();

        (new ExpiredCloudInstanceCleaner)();

        $this->assertSame($notDeleted, Subscription::where('is_deleted', 0)->count());
    }
}
