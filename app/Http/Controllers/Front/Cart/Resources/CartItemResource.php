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
            // Extras the customer can add to this plan (signed-in carts only; guests pick them after logging in).
            // Each extra shows as its own line under the plan, so it carries its own subtotal.
            'extras' => $request->user() ? array_map(fn (array $e): array => [
                ...$e,
                'unit_price' => currencyFormat($e['unit_price'], $currency, includeSymbol: false),
                'line_total' => currencyFormat($e['unit_price'] * $e['quantity'] * (int) $this->quantity, $currency, includeSymbol: false), // @phpstan-ignore property.notFound
            ], $this->availableExtras($currency)) : [], // @phpstan-ignore method.notFound
            // One agent's price for this plan (shown next to the agents stepper).
            'agent_price' => $this->agentPrice($currency) === null ? null : currencyFormat((float) $this->agentPrice($currency), $currency, includeSymbol: false), // @phpstan-ignore method.notFound, method.notFound
            // How long the plan runs ("1 Year", "One Time"), shown next to the plan line.
            'period' => $this->plan?->periodLabel(), // @phpstan-ignore property.notFound
            // Agents the plan price covers (0 = every agent is paid, null = not priced per agent / unlimited),
            // and what the agents above or below that add to the line.
            'included_agents' => $this->includedAgents($currency), // @phpstan-ignore method.notFound
            // The plan line (plan price × quantity) and what the chosen agents add to it (negative = fewer than included; null = nothing).
            'plan_total' => currencyFormat($planTotal = $this->priceFor($currency) * (int) $this->quantity, $currency, includeSymbol: false), // @phpstan-ignore method.notFound, property.notFound
            'agents_total' => abs($agents = round($this->lineTotal() - $this->extrasTotal($currency) - $planTotal, 2)) < 0.005 ? null : currencyFormat(abs($agents), $currency, includeSymbol: false), // @phpstan-ignore method.notFound, method.notFound
            'agents_fewer' => $agents < 0,
            // The plan's own subtotal (line_total minus extras), for the plan row.
            // All chosen add-ons together (the cart shows them as one line that opens up).
            'extras_total' => currencyFormat($this->extrasTotal($currency), $currency, includeSymbol: false), // @phpstan-ignore method.notFound
            'plan_line_total' => currencyFormat($this->lineTotal() - $this->extrasTotal($currency), $currency, includeSymbol: false), // @phpstan-ignore method.notFound, method.notFound
        ];
    }
}
