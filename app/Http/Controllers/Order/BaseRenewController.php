<?php

namespace App\Http\Controllers\Order;

use Auth;
use Illuminate\Contracts\Database\Query\Builder;
use Illuminate\Support\Facades\Date;
use App\Http\Controllers\Controller;
use App\License\Models\Installation;
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
                throw new Exception(__('message.user_removed_database'));
            }

            if (! $product) {
                throw new Exception(__('message.product_removed_database'));
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
            $planId = $request->input('plan');
            $orderId = $request->input('order');

            if (! $planId) {
                $currency = getCurrencyForClient(Auth::user()->country);

                return successResponse('', ['formatted_price' => currencyFormat(0, $currency)]);
            }

            $plan = Plan::find($planId);
            $planDetails = userCurrencyAndPrice(Auth::user()->id, $plan);
            $price = $planDetails['plan']->renew_price;
            $currency = $planDetails['currency'];

            $agents = InvoiceItem::whereHas('invoice', fn (Builder $q) => $q->whereHas('orders', fn (Builder $q) => $q->where('orders.id', $orderId)))
                ->where('plan_id', $planId)
                ->orderByDesc('id')
                ->value('agents');

            if ($agents > 0 && $planDetails['plan']->no_of_agents > 0) {
                $renewalPrice = ($price / $planDetails['plan']->no_of_agents) * (int) $agents;
            } else {
                $renewalPrice = $price;
            }

            $formattedCurrency = currencyFormat($renewalPrice, $currency, true);

            return successResponse('', [
                'formatted_price' => $formattedCurrency,
                'renewalPrice' => $renewalPrice,
            ]);
        } catch (Exception $ex) {
            return errorResponse($ex->getMessage());
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
            $controller = new InvoiceController();
            $tax = $this->calculateTax($product->id, $user->state, $user->country);
            $tax_name = $tax['name'];
            $tax_rate = $tax['value'];
            $cost = rounding($controller->calculateTotal($tax_rate, $cost));
            $number = random_int(11111111, 99999999);
            $date = Date::now();
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
                $installation_path = Installation::where('license_code', Order::find($orderid)->serial_key)
                    ->latest('updated_at')->value('installation_path');
                $invoice->update([
                    'metadata' => [
                        'renewal_agent' => [
                            'new_agents' => $agents,
                            'order_id' => $orderid,
                            'installation_path' => $installation_path,
                            'product_id' => $product->id,
                            'old_license' => $license_code,
                        ],
                    ],
                ]);
            }

            return $items;
        } catch (Exception $ex) {
            throw new Exception($ex->getMessage());
        }
    }
}
