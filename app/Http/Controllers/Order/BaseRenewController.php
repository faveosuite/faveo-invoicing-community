<?php

namespace App\Http\Controllers\Order;

use App\Http\Controllers\Controller;
use App\Model\Order\Invoice;
use App\Model\Order\InvoiceItem;
use App\Model\Order\Order;
use App\Model\Payment\Plan;
use App\Model\Product\Product;
use App\Model\Product\Subscription;
use App\Traits\TaxCalculation;
use App\User;
use Auth;
use Exception;
use Illuminate\Contracts\Database\Query\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Date;

class BaseRenewController extends Controller
{
    use TaxCalculation;

    public function invoiceBySubscriptionId(int $id, int $planid, float|int $cost, string $currency, int|string|null $agents = null): InvoiceItem|JsonResponse|RedirectResponse
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
    public function getInvoiceByOrderId(int $orderid, int $planid, float|int $cost, string $currency, int|string|null $agents = null): InvoiceItem|JsonResponse|RedirectResponse
    {
        try {
            /** @var Order $order */
            $order = Order::find($orderid);
            $item = InvoiceItem::find($order->invoice_item_id);
            if (! $item) {
                // Older orders carry no invoice_item_id. orders.invoice_id was dropped, so take
                // the first item of the order's original (oldest linked) invoice instead.
                $item = $order->invoices()->oldest('invoices.id')->first()?->invoiceItem()->first();
            }
            if (! $item instanceof InvoiceItem) {
                throw new Exception(__('message.record_not_found'));
            }
            $product = $this->getProductByProductId($item->product_id, $order);
            $user = $this->getUserById($order->client); // @phpstan-ignore method.notFound
            if (! $user) {
                throw new Exception(__('message.user_removed_database'));
            }

            if (! $product instanceof Product) {
                throw new Exception(__('message.product_removed_database'));
            }

            if (is_null($agents)) {
                $agents = $item->agents;
            }

            return $this->generateInvoice($product, $user, $orderid, $planid, $cost, '', $agents, $currency);
        } catch (Exception $exception) {
            throw new Exception($exception->getMessage(), $exception->getCode(), $exception);
        }
    }

    public function getProductByProductId(?int $id, Order|string|null $order = ''): ?Product
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

    public function getCost(Request $request): JsonResponse
    {
        try {
            $planId = $request->input('plan');
            $orderId = $request->input('order');

            /** @var User $authUser */
            $authUser = Auth::user();

            // Price for the order's owner: on the admin renew page the logged-in user is the
            // admin, and an INR admin pricing a USD client's renewal saved the INR amount as USD.
            // A client may only price their own orders.
            $order = $orderId ? Order::find((int) $orderId) : null;
            if ($order && ! authorizeOwnership((int) $order->client, allowAdmin: true)) {
                return errorResponse(__('message.unauthorized_action'), 403);
            }
            $customer = $order ? (User::find($order->client) ?? $authUser) : $authUser;

            if (! $planId) {
                $currency = getCurrencyForClient($customer->country);

                return successResponse('', [
                    'formatted_price' => currencyFormat(0, $currency),
                    'renewalPrice' => currencyFormat(0, $currency, includeSymbol: false),
                ]);
            }

            $plan = Plan::find($planId);
            $planDetails = userCurrencyAndPrice($customer->id, $plan);
            $price = $planDetails['plan']->renew_price;
            $currency = $planDetails['currency'];

            $agents = $this->orderAgents((int) $orderId);

            if ($agents > 0 && $planDetails['plan']->no_of_agents > 0) {
                $renewalPrice = ($price / $planDetails['plan']->no_of_agents) * (int) $agents;
            } else {
                $renewalPrice = $price;
            }

            $formattedCurrency = currencyFormat($renewalPrice, $currency, includeSymbol: true);

            return successResponse('', [
                'formatted_price' => $formattedCurrency,
                'renewalPrice' => currencyFormat($renewalPrice, $currency, includeSymbol: false),
            ]);
        } catch (Exception $exception) {
            \Logger::exception($exception);

            return errorResponse(__('message.sorry_something_wrong'));
        }
    }

    public function generateInvoice(Product $product, User $user, int $orderid, int $planid, float|int $cost, string $code, int|string|null $agents, string $currency): InvoiceItem|JsonResponse|RedirectResponse
    {
        try {
            //            if (!empty($agents) && in_array($product->id, cloudPopupProducts())) {
            //                $license_code = Order::where('id', $orderid)->value('serial_key');
            //                $cost = $cost * (int) substr($license_code, -4);
            //            }
            $renewalPrice = $cost; // Get Renewal Price before calculating tax over it to save as regular price of product
            $controller = new InvoiceController;
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
            $renewController = new RenewController;
            $renewController->createOrderInvoiceRelation($orderid, $invoice->id);
            $items = $controller->createInvoiceItemsByAdmin($invoice->id, (string) $product->id, $renewalPrice, $currency, 1, $agents, $planid, $user->id, $tax_name, (float) $tax_rate, $renewalPrice);
            if (in_array($product->id, cloudPopupProducts())) {
                $license_code = Order::where('id', $orderid)->value('serial_key');
                $installation_path = Order::find($orderid)?->installedDomains()->first();
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

    /**
     * The order's current agent count — from its latest invoice line, whatever plan that was on.
     * Shared by the renew popup (getCost) and the renewal invoice (renewByClient): the popup used
     * to look only at lines on the plan just picked, so after a plan switch it showed the full
     * plan price while the invoice charged for the real agent count.
     */
    protected function orderAgents(int $orderId): mixed
    {
        return InvoiceItem::whereHas('invoice', fn (Builder $q) => $q->whereHas('orders', fn (Builder $q) => $q->where('orders.id', $orderId))) // @phpstan-ignore argument.templateType
            ->orderByDesc('id')
            ->value('agents');
    }
}
