<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class UserOrderDelete
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $domain;
    public $order_id;
    /**
     * Create a new event instance.
     */
    public function __construct($domain,$order_id)
    {
        $this->domain = $domain;
        $this->order_id=$order_id;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return array<int, \Illuminate\Broadcasting\Channel>
     */
    public function broadcastOn(): array
    {
        return [
            //            new PrivateChannel('channel-name'),
        ];
    }
}
