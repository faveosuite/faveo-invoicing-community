<?php

declare(strict_types=1);

namespace Tests\Unit\Backend\Listeners;

use App\Events\UserOrderDelete;
use App\Listeners\CloudDeletion;
use Illuminate\Contracts\Queue\ShouldQueue;
use Tests\DBTestCase;

class CloudDeletionTest extends DBTestCase
{
    public function test_implements_should_queue(): void
    {
        $this->assertInstanceOf(ShouldQueue::class, new CloudDeletion());
    }

    public function test_handle_asks_the_cloud_to_delete_and_logs_when_it_cannot(): void
    {
        config(['custom.google_chat' => null]); // no real webhook call
        \App\ThirdPartyApp::where('app_name', 'faveo_app_key')->delete(); // so no real cloud call either

        new CloudDeletion()->handle(new UserOrderDelete('test-domain', 'order-123'));

        // Without an app key the cloud can't be reached; that must be logged, not thrown.
        $this->assertDatabaseHas('exception_logs', ['message' => __('message.cloud_invalid_message')]);
    }
}
