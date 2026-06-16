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
        $this->items?->each(fn ($item) => $item->setRelation('cart', $this->resource));

        return [
            'id' => $this->id,
            'currency' => $this->currency,
            'currency_symbol' => Currency::where('code', $this->currency)->value('symbol'),
            'items' => CartItemResource::collection($this->whenLoaded('items')),
            'item_count' => $this->itemCount(),
            'subtotal' => $this->subtotal(),
            'coupon_code' => $this->coupon_code,
            'coupon_discount' => (float) $this->coupon_discount,
            'total' => $this->total(),
        ];
    }
}
