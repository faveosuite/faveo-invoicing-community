<?php

namespace App\Http\Controllers\Order;

use App\Http\Controllers\Controller;
use App\Model\Order\InstallationDetail;
use App\Model\Order\Invoice;
use App\Model\Order\InvoiceItem;
use App\Model\Order\Order;
use App\Model\Payment\Plan;
use App\Model\Product\Product;
use App\Model\Product\Subscription;
use App\Traits\TaxCalculation;
use Exception;
use Illuminate\Http\Request;

class BaseRenewController extends Controller
{
    use TaxCalculation;

    public function invoiceBySubscriptionId($id, $planid, $cost, $currency, $agents = null)
    {
        try {
            $sub = Subscription::find($id);
            $order_id = $sub->order_id;

            return $this->getInvoiceByOrderId($order_id, $planid, $cost, $currency, $agents);
        } catch (Exception $ex) {
            throw new Exception($ex->getMessage());
        }
    }

    /**
     * generate Invoice and Invoice Item after Increasing the subscription date from Admin Panel.
     *
     * @param  int  $orderid  The Order ID
     * @param  int  $planid  The Plan Id related t the Subscription
     * @param  int  $cost  The Renew cost for for the Paln
     * @param  string  $currency  Currency of ther plan
     */
    public function getInvoiceByOrderId(int $orderid, int $planid, $cost, $currency, $agents = null)
    {
        try {
            $order = Order::find($orderid);
            $invoice_item_id = $order->invoice_item_id;
            $invoice_id = $order->invoice_id;
            $invoice = Invoice::find($invoice_id);
            if ($invoice_item_id == 0) {
                $invoice_item_id = $invoice->invoiceItem()->first()->id;
            }
            $item = InvoiceItem::find($invoice_item_id);
            $product = $this->getProductByProductId($item->product_id, $order);
            $user = $this->getUserById($order->client);
            if (! $user) {
                throw new Exception('User has removed from database');
            }
            if (! $product) {
                throw new Exception('Product has removed from database');
            }

            if (is_null($agents)) {
                $agents = $item->agents;
            }

            return $this->generateInvoice($product, $user, $orderid, $planid, $cost, $code = '', $agents, $currency);
        } catch (Exception $ex) {
            throw new Exception($ex->getMessage());
        }
    }

    public function getProductByProductId($id, $order = '')
    {
        try {
            $product = Product::find($id);
            if ($product) {
                return $product;
            } else {
                $product = Product::where('id', $order->product)->first();

                return $product;
            }
        } catch (Exception $ex) {
            throw new Exception($ex->getMessage());
        }
    }

    public function getCost(Request $request)
    {
        try {
            $isAgents = false;
            $planid = $request->input('plan');
            if (! $planid || $planid == 'Choose') {
                return 0;
            }
            $userid = $request->input('user');
            $plan = Plan::find($planid);

            $planDetails = userCurrencyAndPrice($userid, $plan);
            $price = $planDetails['plan']->renew_price;
            if (Product::where('id', $plan->product)->value('can_modify_agent')) {
                if($planDetails['plan']->no_of_agents != 0) {
                    $price = $price / $planDetails['plan']->no_of_agents;
                }
                $isAgents = true;
            }

            return [$price, $isAgents];
        } catch (Exception $ex) {
            throw new \Exception($ex->getMessage());
        }
    }

    public function generateInvoice($product, $user, $orderid, $planid, $cost, $code, $agents, $currency)
    {
        try {
            $controller = new InvoiceController();
            if ($code != '') {
                $product_cost = $controller->checkCode($code, $product->id, $currency);
            }
//            if (!empty($agents) && in_array($product->id, cloudPopupProducts())) {
//                $license_code = Order::where('id', $orderid)->value('serial_key');
//                $cost = $cost * (int) substr($license_code, -4);
//            }
            $renewalPrice = $cost; //Get Renewal Price before calculating tax over it to save as regular price of product
            $controller = new \App\Http\Controllers\Order\InvoiceController();
            $tax = $this->calculateTax($product->id, $user->state, $user->country);
            $tax_name = $tax->getName();
            $tax_rate = $tax->getValue();
            $cost = rounding($controller->calculateTotal($tax_rate, $cost));
            $number = rand(11111111, 99999999);
            $date = \Carbon\Carbon::now();
            $invoice = Invoice::create([
                'user_id' => $user->id,
                'number' => $number,
                'date' => $date,
                'grand_total' => $cost,
                'currency' => $currency,
                'is_renewed' => 1,
                'status' => 'pending',
            ]);
            $renewController = new RenewController();
            $renewController->createOrderInvoiceRelation($orderid, $invoice->id);
            $items = $controller->createInvoiceItemsByAdmin($invoice->id, $product->id, $renewalPrice, $currency, $qty = 1, $agents, $planid, $user->id, $tax_name, $tax_rate, $renewalPrice);
            if (in_array($product->id, cloudPopupProducts())) {
                $license_code = Order::where('id', $orderid)->value('serial_key');
                $installation_path = InstallationDetail::where('order_id', $orderid)->latest()->value('installation_path');
                \Session::put('AgentAlterationRenew', $user->id);
                \Session::put('newAgentsRenew', $agents);
                \Session::put('orderIdRenew', $orderid);
                \Session::put('installation_pathRenew', $installation_path);
                \Session::put('product_idRenew', $product->id);
                \Session::put('oldLicenseRenew', $license_code);
            }

            return $items;
        } catch (Exception $ex) {
            throw new Exception($ex->getMessage());
        }
    }
}
