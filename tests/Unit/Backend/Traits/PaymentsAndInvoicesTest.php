<?php

declare(strict_types=1);

namespace Tests\Unit\Backend\Traits;

use App\Traits\PaymentsAndInvoices;
use Illuminate\Support\Collection;
use Tests\TestCase;

/**
 * Concrete class to use the trait under test.
 */
class ConcretePaymentsAndInvoices
{
    use PaymentsAndInvoices;
}

class PaymentsAndInvoicesTest extends TestCase
{
    private ConcretePaymentsAndInvoices $subject;

    protected function setUp(): void
    {
        parent::setUp();
        $this->subject = new ConcretePaymentsAndInvoices;
    }

    // =========================================================================
    // getQuantity() – when $qty is truthy, returns immediately (no DB)
    // =========================================================================

    public function test_get_quantity_returns_qty_or_one(): void
    {
        $this->assertSame(10, $this->subject->getQuantity(10));
        $this->assertSame(1, $this->subject->getQuantity(0));
        $this->assertSame(1, $this->subject->getQuantity(null));
    }

    // =========================================================================
    // getTotalInvoice() – pure logic, no DB
    // =========================================================================

    public function test_get_total_invoice_sums_grand_totals(): void
    {
        $invoices = new Collection([
            (object) ['grand_total' => 100],
            (object) ['grand_total' => 50.5],
            (object) ['grand_total' => 25],
        ]);

        $result = $this->subject->getTotalInvoice($invoices);

        $this->assertSame(175.5, $result);
    }

    public function test_get_total_invoice_returns_zero_for_empty_collection(): void
    {
        $result = $this->subject->getTotalInvoice(new Collection);
        $this->assertSame(0, $result);
    }

    // =========================================================================
    // getAmountPaid() – returns 0 for non-existent user (empty result set)
    // =========================================================================

    public function test_get_amount_paid_returns_zero_for_nonexistent_user(): void
    {
        $result = $this->subject->getAmountPaid(999999999);
        $this->assertSame(0.0, $result);
    }
}
