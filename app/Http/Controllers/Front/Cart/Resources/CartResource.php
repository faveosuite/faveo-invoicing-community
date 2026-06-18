<?php

namespace App\Http\Controllers\Front\Cart\Resources;

use App\Model\Payment\Currency;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Override;

class CartResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    #[Override]
    public function toArray(Request $request): array
    {
        // Set cart relation on each item so CartItemResource can reach cart->currency
        $this->items?->each(fn ($item) => $item->setRelation('cart', $this->resource)); // @phpstan-ignore property.notFound

        return [
            'id' => $this->id, // @phpstan-ignore property.notFound
            'currency' => $this->currency, // @phpstan-ignore property.notFound
            'currency_symbol' => Currency::where('code', $this->currency)->value('symbol'), // @phpstan-ignore property.notFound
            'items' => CartItemResource::collection($this->whenLoaded('items')),
            'item_count' => $this->itemCount(), // @phpstan-ignore method.notFound
            'subtotal' => $this->subtotal(), // @phpstan-ignore method.notFound
            'coupon_code' => $this->coupon_code, // @phpstan-ignore property.notFound
            'coupon_discount' => (float) $this->coupon_discount, // @phpstan-ignore property.notFound
            'total' => $this->total(), // @phpstan-ignore method.notFound
        ];
    }
}
