<?php

namespace App\Http\Controllers\Front\Cart\Resources;

use Override;
use App\Model\Payment\Currency;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CartItemResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    #[Override]
    public function toArray(Request $request): array
    {
        $currency = $this->cart?->currency ?? 'USD';
        $currencySymbol = Currency::where('code', $currency)->value('symbol') ?? $currency;

        return [
            'id' => $this->id,
            'product_id' => $this->product_id,
            'plan_id' => $this->plan_id,
            'name' => $this->product?->name,
            'image' => $this->product?->image,
            'unit_price' => $this->priceFor($currency),
            'line_total' => $this->lineTotal(),
            'currency' => $currency,
            'currency_symbol' => $currencySymbol,
            'quantity' => (int) $this->quantity,
            'agents' => (int) $this->agents,
            'domain' => $this->domain,
            'billing_cycle' => $this->billing_cycle,
            // Product-level edit permissions (drive which steppers are editable).
            'can_modify_quantity' => (bool) $this->product?->can_modify_quantity,
            'can_modify_agent' => (bool) $this->product?->can_modify_agent,
            'show_agent' => (bool) $this->product?->show_agent,
        ];
    }
}
