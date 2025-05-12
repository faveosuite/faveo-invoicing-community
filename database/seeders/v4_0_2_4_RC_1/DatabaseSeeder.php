<?php

namespace Database\Seeders\v4_0_2_4_RC_1;

use App\Model\Common\Msg91Status;
use App\Model\Common\PipedriveLocalFields;
use App\Model\Order\InvoiceItem;
use App\Model\Order\Order;
use App\Model\Product\Product;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->addMsgStatus();
        $this->invoiceItemProductIDChange();
    }

    public function addMsgStatus()
    {
        $statuses = [
            ['status_code' => 0, 'status_label' => 'Pending'],
            ['status_code' => 1, 'status_label' => 'Delivered'],
            ['status_code' => 2, 'status_label' => 'Failed'],
            ['status_code' => 9, 'status_label' => 'NDNC'],
            ['status_code' => 16, 'status_label' => 'Rejected'],
            ['status_code' => 25, 'status_label' => 'Rejected'],
            ['status_code' => 17, 'status_label' => 'Blocked number'],
        ];

        foreach ($statuses as $status) {
            Msg91Status::updateOrCreate(
                ['status_code' => $status['status_code']],
                ['status_label' => $status['status_label']]
            );
        }
    }

    public function invoiceItemProductIDChange()
    {
        $orders = Order::all();
        foreach ($orders as $order) {
            // Make sure invoice_item_id and product_id exist in the order
            if ($order->invoice_item_id && $order->product) {
                // Find the invoice item
                $invoiceItem = InvoiceItem::find($order->invoice_item_id);
                $product = Product::find($order->product);
                if ($invoiceItem && $product) {
                    $invoiceItem->product_id = $product->id;
                    $invoiceItem->save();
                }
            }
        }
    }
}