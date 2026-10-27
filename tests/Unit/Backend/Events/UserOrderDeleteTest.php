<?php

namespace Tests\Unit\Backend\Events;

use App\Events\UserOrderDelete;
use Tests\TestCase;

class UserOrderDeleteTest extends TestCase
{
    public function test_event_can_be_instantiated_with_domain_and_order_id(): void
    {
        $event = new UserOrderDelete('test.example.com', 42);
        $this->assertInstanceOf(UserOrderDelete::class, $event);
        $this->assertEquals('test.example.com', $event->domain);
        $this->assertEquals(42, $event->order_id);
    }

    public function test_event_broadcast_on_returns_empty_array(): void
    {
        $event = new UserOrderDelete('domain.com', 1);
        $channels = $event->broadcastOn();
        $this->assertIsArray($channels);
        $this->assertEmpty($channels);
    }

    public function test_event_accepts_null_values(): void
    {
        $event = new UserOrderDelete(null, null);
        $this->assertNull($event->domain);
        $this->assertNull($event->order_id);
    }
}
