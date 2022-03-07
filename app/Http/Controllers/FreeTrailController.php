<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Order\BaseOrderController;
use App\Model\Common\StatusSetting;
use App\Model\Order\Invoice;
use App\Model\Order\InvoiceItem;
use App\Model\Order\Order;
use App\Model\Payment\Plan;
use App\Model\Product\Product;
use App\Model\Product\Subscription;
use App\User;
use Auth;
use Crypt;
use DB;
use Illuminate\Http\Request;
use Lang;

class FreeTrailController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $invoice = new Invoice();
        $this->invoice = $invoice;

        $invoiceItem = new InvoiceItem();
        $this->invoiceItem = $invoiceItem;

        $order = new Order();
        $this->order = $order;

        $subscription = new Subscription();
        $this->subscription = $subscription;
    }

    public function firstloginatem(Request $request)
    {
        if (! Auth::check()) {
            return redirect('login')->back()->with('fails', \Lang::get('message.free-login'));
        }

        $id = $request->get('id');
        if (Auth::user()->id == $id) {
            $user_login = User::find($id);
            if ($user_login->first_time_login != 0) {
                return errorResponse(Lang::get('message.false'), 400);
            }
            $user = User::where('id', $id)->update(['first_time_login' => 1]);

            $invoice = $this->generateFreetrailInvoice();

            $invoice_items = $this->createFreetrailInvoiceItems();

            $order = $this->executeFreetrailOrder();

            return successResponse(Lang::get('message.succs_fre'), $order, 200);
        }
    }

    private function generateFreetrailInvoice()
    {
        $tax_rule = new \App\Model\Payment\TaxOption();
        $rule = $tax_rule->findOrFail(1);
        $rounding = $rule->rounding;
        $user_id = \Auth::user()->id;

        $grand_total = \Cart::getTotal();
        $number = rand(11111111, 99999999);
        $date = \Carbon\Carbon::now();
        if ($rounding) {
            $grand_total = round($grand_total);
        }
        $currency = \Session::has('cart_currency') ? \Session::get('cart_currency') : getCurrencyForClient(\Auth::user()->country);
        $invoice = $this->invoice->create(['user_id' => $user_id, 'number' => $number, 'date'=> $date, 'grand_total' => $grand_total, 'status' => 'pending',
            'currency' => $currency, ]);

        return $invoice;
    }

    private function createFreetrailInvoiceItems()
    {
        $cart = \Cart::getContent();
        $userId = \Auth::user()->id;
        $invoice = DB::table('invoices')->where('user_id', $userId)->first();
        $invoiceid = $invoice->id;
        $invoiceItem = $this->invoiceItem->create([
            'invoice_id'     => $invoiceid,
            'product_name'   => 'Faveo Cloud(Beta)',
            'regular_price'  => 0,
            'quantity'       => 1,
            'tax_name'       => 'null',
            'tax_percentage' => 0,
            'subtotal'       => 0,
            'domain'         => '',
            'plan_id'        => 0,
            'agents'         => 0,
        ]);

        return $invoiceItem;
    }

    private function executeFreetrailOrder()
    {
        $order_status = 'executed';
        $userId = \Auth::user()->id;
        $invoice = DB::table('invoices')->where('user_id', $userId)->first();
        $invoiceid = $invoice->id;

        $invoice_items = DB::table('invoice_items')->where('invoice_id', $invoiceid)->get();

        $user_id = $this->invoice->find($invoiceid)->user_id;

        if (count($invoice_items) > 0) {
            foreach ($invoice_items as $item) {
                if ($item) {
                    $items = $this->getIfFreetrailItemPresent($item, $invoiceid, $user_id, $order_status);
                }
            }
        }

        return 'success';
    }

    private function getIfFreetrailItemPresent($item, $invoiceid, $user_id, $order_status)
    {
        $product = Product::where('name', $item->product_name)->first()->id;
        $version = Product::where('name', $item->product_name)->first()->version;
        if ($version == null) {
            //Get Version from Product Upload Table
            $version = $this->product_upload->where('product_id', $product)->pluck('version')->first();
        }
        $serial_key = $this->generateFreetrailSerialKey($product, $item->agents); //Send Product Id and Agents to generate Serial Key
        $domain = $item->domain;
        //$plan_id = $this->plan($item->id);
        $plan_id = Plan::where('product', '=', $product)
                           ->value('id');

        $order = $this->order->create([

            'invoice_id'      => $invoiceid,
            'invoice_item_id' => $item->id,
            'client'          => $user_id,
            'order_status'    => $order_status,
            'serial_key'      => Crypt::encrypt($serial_key),
            'product'         => $product,
            'price_override'  => $item->subtotal,
            'qty'             => $item->quantity,
            'domain'          => $domain,
            'number'          => $this->generateFreetrailNumber(),
        ]);

        $baseorder = new BaseOrderController();
        $baseorder->addOrderInvoiceRelation($invoiceid, $order->id);

        if ($plan_id != 0) {
            $baseorder->addSubscription($order->id, $plan_id, $version, $product, $serial_key);
        }
        $mailchimpStatus = StatusSetting::pluck('mailchimp_status')->first();
        if ($mailchimpStatus) {
            $baseorder->addtoMailchimp($product, $user_id, $item);
        }
    }

    private function generateFreetrailSerialKey(int $productid, $agents)
    {
        $len = strlen($agents);
        switch ($len) {//Get Last Four digits based on No.Of Agents

                case '1':
                   $lastFour = '000'.$agents;
                    break;
                   case '2':

                    $lastFour = '00'.$agents;
                     break;
                      case '3':
                    $lastFour = '0'.$agents;
                     break;
                      case '4':
                    $lastFour = $agents;

                     break;
                default:
                    $lastFour = '0000';
                    break;
            }
        $str = strtoupper(str_random(12));
        $licCode = $str.$lastFour;

        return $licCode;
    }

    private function generateFreetrailNumber()
    {
        return rand('10000000', '99999999');
    }
}
