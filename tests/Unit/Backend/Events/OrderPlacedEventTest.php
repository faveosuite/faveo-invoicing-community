<?php

declare(strict_types=1);

namespace Tests\Unit\Backend\Events;

use App\Events\OrderPlacedEvent;
use App\Model\Order\Invoice;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class OrderPlacedEventTest extends TestCase
{
    use DatabaseTransactions;

    public function test_stores_invoice_reference(): void
    {
        /** @var Invoice $invoice */
        $invoice = Invoice::factory()->create(['grand_total' => 99.99]);

        $event = new OrderPlacedEvent($invoice);

        $this->assertSame($invoice->id, $event->invoice->id);
        $this->assertSame(99.99, (float) $event->invoice->grand_total);
    }

    public function test_invoice_is_readonly_property(): void
    {
        /** @var Invoice $invoice */
        $invoice = Invoice::factory()->create();

        $event = new OrderPlacedEvent($invoice);

        // Verify readonly — attempting to reassign should throw in PHP 8.1+
        try {
            // @phpstan-ignore-next-line
            $event->invoice = Invoice::factory()->create(); // @phpstan-ignore assign.propertyReadOnly
            $this->fail('Expected Error for readonly property reassignment');
        } catch (\Error) {
            $this->assertTrue(true); // readonly enforced
        }
    }
}
