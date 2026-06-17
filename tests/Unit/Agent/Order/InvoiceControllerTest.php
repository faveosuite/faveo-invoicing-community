<?php

namespace Tests\Unit\Agent\Order;

use App\Model\Order\Invoice;
use App\Model\Order\InvoiceItem;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\DBTestCase;

class InvoiceControllerTest extends DBTestCase
{
    use DatabaseTransactions;

    protected function setUp(): void
    {
        parent::setUp();
        $this->getLoggedInUser('admin');
    }

    private function createInvoiceWithItems(array $invoiceData = [])
    {
        $invoice = Invoice::factory()->create($invoiceData);

        InvoiceItem::factory()->create([
            'invoice_id' => $invoice->id,
            'subtotal' => 500,
            'tax_name' => 'GST',
            'tax_percentage' => 18,
        ]);

        return $invoice;
    }

    public function test_get_invoices_success(): void
    {
        Invoice::factory()->count(4)->create();

        $response = $this->getJson('/invoices?limit=2');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    'data' => [
                        [
                            'id',
                            'user',
                            'number',
                            'grand_total',
                            'status',
                        ],
                    ],
                ],
            ]);
    }

    public function test_get_invoices_with_search_query(): void
    {
        Invoice::factory()->create([
            'status' => 'success',
            'number' => 'INV12345',
        ]);

        $response = $this->getJson('/invoices?search-query=paid');

        $response->assertStatus(200)
            ->assertJsonFragment(['number' => 'INV12345']);
    }

    public function test_get_invoices_empty_list(): void
    {
        $response = $this->getJson('/invoices');

        $response->assertStatus(200);

        $this->assertEquals(0, count($response->json('data.data')));
    }

    public function test_delete_bulk_invoices_success(): void
    {
        $invoices = Invoice::factory()->count(3)->create();
        $ids = $invoices->pluck('id')->toArray();

        $response = $this->deleteJson('/invoices', [
            'invoice_ids' => $ids,
        ]);

        $response->assertStatus(200)
            ->assertJsonFragment(['message' => __('message.deleted-successfully')]);

        foreach ($ids as $id) {
            $this->assertDatabaseMissing('invoices', ['id' => $id]);
        }
    }

    public function test_delete_bulk_invoices_empty_ids(): void
    {
        $response = $this->deleteJson('/invoices', [
            'invoice_ids' => [],
        ]);

        $response->assertStatus(400)
            ->assertJsonFragment(['message' => __('message.select-a-row')]);
    }

    public function test_get_invoice_success(): void
    {
        $invoice = $this->createInvoiceWithItems();

        $response = $this->getJson('/invoice/'.$invoice->id);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    'invoice' => ['number', 'date'],
                    'from' => ['company', 'country', 'state'],
                    'to' => ['first_name', 'email'],
                    'items',
                    'totals' => [
                        'subtotal',
                        'tax',
                        'processing_fee',
                        'credits',
                        'discount',
                        'total',
                    ],
                ],
            ]);
    }

    public function test_get_invoice_not_found(): void
    {
        $response = $this->getJson('/invoice/999999');

        $response->assertStatus(400);
    }

    public function test_get_invoice_user_suspended(): void
    {
        $invoice = $this->createInvoiceWithItems();
        $invoice->user->delete();

        $response = $this->getJson('/invoice/'.$invoice->id);

        $response->assertStatus(400);
        $response->assertJsonFragment([
            'message' => __('message.user_suspended'),
        ]);
    }
}
