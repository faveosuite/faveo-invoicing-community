<?php

declare(strict_types=1);

namespace App\Events;

use App\Model\Order\Invoice;

class OrderPlacedEvent
{
    public function __construct(
        public readonly Invoice $invoice,
    ) {
    }
}
