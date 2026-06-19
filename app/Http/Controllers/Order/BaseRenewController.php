<?php

namespace App\Http\Controllers\Order;

use App\Http\Controllers\Controller;
use App\License\Models\Installation;
use App\Model\Order\Invoice;
use App\Model\Order\InvoiceItem;
use App\Model\Order\Order;
use App\Model\Payment\Plan;
use App\Model\Product\Product;
use App\Model\Product\Subscription;
use App\Traits\TaxCalculation;
use Auth;
use Exception;
use Illuminate\Contracts\Database\Query\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Date;

class BaseRenewController extends Controller
{
    use TaxCalculation;

    public function invoiceBySubscriptionId(int $id, int $planid, float|int $cost, string $currency, int|string|null $agents = null): \App\Model\Order\InvoiceItem|\Illuminate\Http\RedirectResponse
    {
        try {
            $sub = Subscription::find($id);
            if (! $sub) {
                throw new Exception(__('message.record_not_found'));
            }

            $order_id = $sub->order_id;

            return $this->getInvoiceByOrderId($order_id, $planid, $cost, $currency, $agents);
        } catch (Exception $exception) {
            throw new Exception($exception->getMessage(), $exception->getCode(), $exception);
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
    public function getInvoiceByOrderId(int $orderid, int $planid, float|int $cost, string $currency, int|string|null $agents = null): \App\Model\Order\InvoiceItem|\Illuminate\Http\RedirectResponse
    {
        try {
            /** @var \App\Model\Order\Order $order */
            $order = Order::find($orderid);
            $invoice_item_id = $order->invoice_item_id;
            $invoice_id = $order->invoice_id; // @phpstan-ignore property.notFound
            /** @var \App\Model\Order\Invoice $invoice */
            $invoice = Invoice::find($invoice_id);
            if ($invoice_item_id == 0) {
                /** @var \App\Model\Order\InvoiceItem $firstInvoiceItem */
                $firstInvoiceItem = $invoice->invoiceItem()->first();
                $invoice_item_id = $firstInvoiceItem->id;
            }

            /** @var \App\Model\Order\InvoiceItem $item */
            $item = InvoiceItem::find($invoice_item_id);
            $product = $this->getProductByProductId($item->product_id, $order);
            $user = $this->getUserById($order->client); // @phpstan-ignore method.notFound
            if (! $user) {
                throw new Exception(__('message.user_removed_database'));
            }

            if (!$product instanceof \App\Model\Product\Product) {
                throw new Exception(__('message.product_removed_database'));
            }

            if (is_null($agents)) {
                $agents = $item->agents;
            }

            return $this->generateInvoice($product, $user, $orderid, $planid, $cost, $code = '', $agents, $currency);
        } catch (Exception $exception) {
            throw new Exception($exception->getMessage(), $exception->getCode(), $exception);
        }
    }

    public function getProductByProductId(?int $id, Order|string|null $order = ''): ?\App\Model\Product\Product
    {
        try {
            $product = Product::find($id);
            if ($product) {
                return $product;
            }

            return $order instanceof Order ? Product::where('id', $order->product)->first() : null;
        } catch (Exception $exception) {
            throw new Exception($exception->getMessage(), $exception->getCode(), $exception);
        }
    }

    public function getCost(Request $request): \Illuminate\Http\JsonResponse
    {
        try {
            $planId = $request->input('plan');
            $orderId = $request->input('order');

            /** @var \App\User $authUser */
            $authUser = Auth::user();
            if (! $planId) {
                $currency = getCurrencyForClient($authUser->country);

                return successResponse('', ['formatted_price' => currencyFormat(0, $currency)]);
            }

            $plan = Plan::find($planId);
            $planDetails = userCurrencyAndPrice($authUser->id, $plan);
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

            $formattedCurrency = currencyFormat($renewalPrice, $currency, includeSymbol: true);

            return successResponse('', [
                'formatted_price' => $formattedCurrency,
                'renewalPrice' => $renewalPrice,
            ]);
        } catch (Exception $exception) {
            return errorResponse($exception->getMessage());
        }
    }

    public function generateInvoice(\App\Model\Product\Product $product, \App\User $user, int $orderid, int $planid, float|int $cost, string $code, int|string|null $agents, string $currency): \App\Model\Order\InvoiceItem|\Illuminate\Http\RedirectResponse
    {
        try {
            $controller = new InvoiceController();
            if ($code !== '') {
                $product_cost = $controller->checkCode($code, $product->id, $currency); // @phpstan-ignore method.notFound
            }

//            if (!empty($agents) && in_array($product->id, cloudPopupProducts())) {
//                $license_code = Order::where('id', $orderid)->value('serial_key');
//                $cost = $cost * (int) substr($license_code, -4);
//            }
            $renewalPrice = $cost; //Get Renewal Price before calculating tax over it to save as regular price of product
            $controller = new InvoiceController();
            $tax = $this->calculateTax($product->id, $user->state ?? '', $user->country ?? '');
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
            $items = $controller->createInvoiceItemsByAdmin($invoice->id, (string) $product->id, $renewalPrice, $currency, $qty = 1, $agents, $planid, $user->id, $tax_name, $tax_rate, $renewalPrice); // @phpstan-ignore argument.type
            if (in_array($product->id, cloudPopupProducts())) {
                $license_code = Order::where('id', $orderid)->value('serial_key');
                $installation_path = Installation::where('license_code', Order::find($orderid)?->serial_key)
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
        } catch (Exception $exception) {
            throw new Exception($exception->getMessage(), $exception->getCode(), $exception);
        }
    }
}
