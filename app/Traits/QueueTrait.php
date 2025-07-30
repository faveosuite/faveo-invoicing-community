<?php

namespace App\Traits;

use Illuminate\Queue\SyncQueue;

class QueueTrait extends SyncQueue
{
    public function getPayloadData($job)
    {
        return $this->createPayload($job, 'default');
    }
}
