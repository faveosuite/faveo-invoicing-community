<?php

namespace App\Http\Controllers\Front\Cart\Resources;

use App\Model\Payment\Currency;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Override;

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
        $currency = $this->cart->currency ?? 'USD';
        $currencySymbol = Currency::where('code', $currency)->value('symbol') ?? $currency;

        return [
            'id' => $this->id, // @phpstan-ignore property.notFound
            'product_id' => $this->product_id, // @phpstan-ignore property.notFound
            'plan_id' => $this->plan_id, // @phpstan-ignore property.notFound
            'name' => $this->product?->name, // @phpstan-ignore property.notFound
            'image' => $this->product?->image, // @phpstan-ignore property.notFound
            'unit_price' => currencyFormat($this->unitPrice($currency), $currency, includeSymbol: false), // @phpstan-ignore method.notFound
            // The plan's own price (for its included agents) — the cart's Price column, so it
            // doesn't jump to the line total when agents are added; unit_price is agent-scaled.
            'plan_price' => currencyFormat($this->priceFor($currency), $currency, includeSymbol: false), // @phpstan-ignore method.notFound
            'line_total' => currencyFormat($this->lineTotal(), $currency, includeSymbol: false), // @phpstan-ignore method.notFound
            'currency' => $currency,
            'currency_symbol' => $currencySymbol,
            'quantity' => (int) $this->quantity, // @phpstan-ignore property.notFound
            'agents' => (int) $this->agents, // @phpstan-ignore property.notFound
            'domain' => $this->domain, // @phpstan-ignore property.notFound
            'require_domain' => $this->needsDomain(), // @phpstan-ignore method.notFound
            'billing_cycle' => $this->billing_cycle, // @phpstan-ignore property.notFound
            // Product-level edit permissions (drive which steppers are editable).
            'can_modify_quantity' => (bool) $this->product?->can_modify_quantity, // @phpstan-ignore property.notFound
            'can_modify_agent' => (bool) $this->product?->can_modify_agent, // @phpstan-ignore property.notFound
            'show_agent' => (bool) $this->product?->show_agent, // @phpstan-ignore property.notFound
        ];
    }
}
