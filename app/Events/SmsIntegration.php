<?php

declare(strict_types=1);

namespace App\Events;

use Illuminate\Queue\SerializesModels;

class SmsIntegration extends Event
{
    use SerializesModels;

    /**
     * Create a new event instance.
     */
    public function __construct(public mixed $para)
    {
    }

    /**
     * Get the channels the event should be broadcast on.
     * @return array<mixed>
     */
    public function broadcastOn(): array
    {
        return [];
    }
}
