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
            // The plan's own price — the cart's Price column; unit_price adds the extras.
            'plan_price' => currencyFormat($this->priceFor($currency), $currency, includeSymbol: false), // @phpstan-ignore method.notFound
            'line_total' => currencyFormat($this->lineTotal(), $currency, includeSymbol: false), // @phpstan-ignore method.notFound
            'currency' => $currency,
            'currency_symbol' => $currencySymbol,
            'quantity' => (int) $this->quantity, // @phpstan-ignore property.notFound
            'domain' => $this->domain, // @phpstan-ignore property.notFound
            'require_domain' => $this->needsDomain(), // @phpstan-ignore method.notFound
            'billing_cycle' => $this->billing_cycle, // @phpstan-ignore property.notFound
            // Product-level edit permissions (drive which steppers are editable).
            'can_modify_quantity' => (bool) $this->product?->can_modify_quantity, // @phpstan-ignore property.notFound
            // Extras the customer can add to this plan (signed-in carts only; guests pick them after logging in).
            // Each extra shows as its own line under the plan, so it carries its own subtotal.
            'extras' => $request->user() ? array_map(fn (array $e): array => [
                ...$e,
                'unit_price' => currencyFormat($e['unit_price'], $currency, includeSymbol: false),
                'line_total' => currencyFormat($e['unit_price'] * $e['quantity'] * (int) $this->quantity, $currency, includeSymbol: false), // @phpstan-ignore property.notFound
            ], $this->availableExtras($currency)) : [], // @phpstan-ignore method.notFound
            // How long the plan runs ("1 Year", "One Time"), shown next to the plan line.
            'period' => $this->plan?->periodLabel(), // @phpstan-ignore property.notFound
            // The plan line (plan price × quantity).
            'plan_total' => currencyFormat($this->priceFor($currency) * (int) $this->quantity, $currency, includeSymbol: false), // @phpstan-ignore method.notFound, property.notFound
            // The plan's own subtotal (line_total minus extras), for the plan row.
            // All chosen add-ons together (the cart shows them as one line that opens up).
            'extras_total' => currencyFormat($this->extrasTotal($currency), $currency, includeSymbol: false), // @phpstan-ignore method.notFound
            'plan_line_total' => currencyFormat($this->lineTotal() - $this->extrasTotal($currency), $currency, includeSymbol: false), // @phpstan-ignore method.notFound, method.notFound
        ];
    }
}
