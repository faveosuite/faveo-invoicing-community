<?php

namespace App\Traits;

use Illuminate\Queue\SyncQueue;

class QueueTrait extends SyncQueue
{
    public function getPayloadData(mixed $job): mixed
    {
        return $this->createPayload($job, 'default');
    }
}
