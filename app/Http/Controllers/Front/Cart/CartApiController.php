<?php

namespace App\Http\Controllers\Front\Cart;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Front\Cart\Resources\CartResource;
use App\Http\Requests\Cart\AddCartItemRequest;
use App\Http\Requests\Cart\UpdateCartItemRequest;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CartApiController extends Controller
{
    public function __construct(private readonly CartService $cartService)
    {
    }

    public function show(Request $request): JsonResponse
    {
        return $this->cartResponse($request);
    }

    public function addItem(AddCartItemRequest $request): JsonResponse
    {
        $this->cartService->addItem($request, $request->validated());

        return $this->cartResponse($request);
    }

    public function updateItem(UpdateCartItemRequest $request, int|string $item): JsonResponse
    {
        abort_unless($this->cartService->ownsItem($request, $item), 403, 'Forbidden');
        $this->cartService->updateItem($request, $item, $request->validated());

        return $this->cartResponse($request);
    }

    public function removeItem(Request $request, int|string $item): JsonResponse
    {
        abort_unless($this->cartService->ownsItem($request, $item), 403, 'Forbidden');
        $this->cartService->removeItem($request, $item);

        return $this->cartResponse($request);
    }

    public function clear(Request $request): JsonResponse
    {
        $this->cartService->clear($request);

        return $this->cartResponse($request);
    }

    public function applyCoupon(Request $request): JsonResponse
    {
        $request->validate(['code' => ['required', 'string']]);

        try {
            $this->cartService->applyCoupon($request, $request->input('code'));
        } catch (Exception $exception) {
            return errorResponse($exception->getMessage(), 422);
        }

        return $this->cartResponse($request);
    }

    public function removeCoupon(Request $request): JsonResponse
    {
        $this->cartService->removeCoupon($request);

        return $this->cartResponse($request);
    }

    /** Checkout summary: cart + taxes + gateways + grand total. */
    public function checkout(Request $request): JsonResponse
    {
        $cart = $this->cartService->resolveCart($request)->load('items.product');
        $data = array_merge(
            new CartResource($cart)->toArray($request),
            $this->cartService->checkoutExtras($cart, $request->user()),
        );

        return successResponse('', $data);
    }

    public function placeOrder(Request $request): JsonResponse
    {
        $request->validate(['gateway' => ['required', 'string']]);

        $user = $request->user();
        $cart = $this->cartService->resolveCart($request)->load('items.product');

        if ($cart->items->isEmpty()) {
            return errorResponse(__('message.cart_empty'), 422);
        }

        // Build (or reuse) the pending invoice for this cart. Payment is driven
        // entirely by this invoice id from here on (pay page → charge endpoint),
        // so no payment state is stashed in the session. The cart is left intact
        // and is only emptied once payment succeeds.
        $invoice = $this->cartService->placeOrder($cart, $user);
        $currency = $cart->currency ?? 'USD';

        return successResponse('', [
            'invoice_id' => $invoice->id,
            'gateway' => $request->input('gateway'),
            'grand_total' => (float) $invoice->grand_total,
            'currency' => $currency,
        ]);
    }

    private function cartResponse(Request $request): JsonResponse
    {
        $cart = $this->cartService->resolveCart($request);

        return successResponse('', new CartResource($cart)->resolve($request));
    }
}
