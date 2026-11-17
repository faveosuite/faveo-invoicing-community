<?php

declare(strict_types=1);

namespace App\Http\Controllers\Front;

use App\Http\Controllers\Controller;
use App\Model\Order\Order;
use App\Model\Product\Entitlement;
use App\Services\EntitlementSalesService;
use App\Services\Payment\PostPaymentService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * Client: buy extras for one of their orders, or change how many renew.
 */
class OrderExtrasController extends Controller
{
    public function __construct(private readonly EntitlementSalesService $sales)
    {
    }

    public function index(int $orderId): JsonResponse
    {
        return successResponse('', ['extras' => $this->sales->forOrder($this->ownOrder($orderId))]);
    }

    /** What buying would cost now, before tax. */
    public function quote(Request $request, int $orderId): JsonResponse
    {
        [$order, $item, $quantity] = $this->purchaseInput($request, $orderId);

        return successResponse('', $this->sales->quote($order, $item, $quantity));
    }

    /** Creates the invoice; the client then pays it on the checkout page. */
    public function buy(Request $request, int $orderId): JsonResponse
    {
        [$order, $item, $quantity] = $this->purchaseInput($request, $orderId);
        $invoice = $this->sales->createPurchaseInvoice($order, $item, $quantity);

        return successResponse('', ['invoice_id' => $invoice->id]);
    }

    /** Sets how many units renew next period (0 cancels). No refund; takes effect at renewal. */
    public function renewal(Request $request, int $orderId, int $entitlementId): JsonResponse
    {
        $order = $this->ownOrder($orderId);
        $quantity = (int) $request->validate(['quantity' => ['required', 'integer', 'min:0', 'max:9999']])['quantity'];
        $item = Entitlement::findOrFail($entitlementId);

        // A renewal invoice is priced from what renews when it's raised. Changing that while it's
        // unpaid would let a cheap invoice (extras switched off) renew them all once switched back.
        $unpaid = $order->latestUnpaidInvoice();
        if ($unpaid) {
            return errorResponse(__('message.extra_renewal_blocked_unpaid', ['number' => $unpaid->number]), 409);
        }

        $this->sales->setRenewQuantity($order, $item, $quantity);

        // An active auto-renewal charges a stored amount; bring it in line with the new total.
        $latest = $order->invoices()->latest('invoices.id')->first();
        if ($latest) {
            resolve(PostPaymentService::class)->updateSubscriptionPriceIfNeeded($order->id, $latest);
        }

        return successResponse(__('message.extra_renewal_updated'));
    }

    /**
     * @return array{0: Order, 1: Entitlement, 2: int}
     */
    private function purchaseInput(Request $request, int $orderId): array
    {
        $order = $this->ownOrder($orderId);
        $data = $request->validate([
            'entitlement_id' => ['required', 'integer'],
            'quantity' => ['required', 'integer', 'min:1', 'max:9999'],
        ]);

        return [$order, Entitlement::findOrFail((int) $data['entitlement_id']), (int) $data['quantity']];
    }

    private function ownOrder(int $orderId): Order
    {
        $order = Order::findOrFail($orderId);
        abort_unless((int) $order->client === (int) Auth::id(), 403);

        return $order;
    }
}
