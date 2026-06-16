<?php

namespace App\Http\Controllers\Front;

use Illuminate\Contracts\Database\Query\Builder;
use Auth;
use App\Http\Controllers\Controller;
use App\Model\CloudDataCenters;
use App\Model\Payment\Currency;
use App\Model\Product\Product;
use App\Model\Product\ProductGroup;

class StoreController extends Controller
{
    public function getGroups()
    {
        $groups = ProductGroup::where('hidden', '0')
            ->select('id', 'name', 'headline', 'tagline', 'status')
            ->orderBy('id')
            ->get()
            ->map(fn ($g) => array_merge(
                $g->only(['id', 'name', 'headline', 'tagline']),
                ['status' => (bool) $g->status]
            ));

        return successResponse('', $groups);
    }

    public function getProducts(int $groupId)
    {
        $group = ProductGroup::findOrFail($groupId);

        $currency = $this->resolveCurrency();
        $symbol = $this->getCurrencySymbol($currency);

        $products = Product::with([
            'planRelation' => fn ($q) => $q
                ->where('status', 1)
                ->with([
                    'planPrice' => fn ($pq) => $pq->where('currency', $currency),
                    'periods',
                ]),
        ])
            ->where('group', $groupId)
            ->where('hidden', '!=', 1)
            ->whereHas('planRelation', fn (Builder $q) => $q
                ->where('status', 1)
                ->whereHas('planPrice', fn (Builder $pq) => $pq->where('currency', $currency))
            )
            ->orderBy('id')
            ->get()
            ->sortBy(fn ($p) => $p->planRelation
                ->flatMap(fn ($pl) => $pl->planPrice)
                ->pluck('add_price')
                ->filter(fn ($v) => $v !== null)
                ->min() ?? PHP_INT_MAX
            )
            ->values();

        return successResponse('', [
            'group' => array_merge(
                $group->only(['id', 'name', 'headline', 'tagline']),
                ['status' => (bool) $group->status]
            ),
            'currency' => $currency,
            'currency_symbol' => $symbol,
            'cloud_subdomain' => cloudSubDomain() ?? '',
            'data_centers' => CloudDataCenters::select('id', 'cloud_countries', 'cloud_state')->get()
                ->map(fn ($dc) => [
                    'id' => $dc->id,
                    'name' => trim($dc->cloud_countries.($dc->cloud_state ? ', '.$dc->cloud_state : '')),
                ])->values(),
            'products' => $products->map(fn ($p) => $this->transformProduct($p, $currency))->values(),
        ]);
    }

    private function resolveCurrency(): string
    {
        if (Auth::check()) {
            return getCurrencyForClient(Auth::user()->country);
        }

        $ip = request()->ip();
        $iso = cache()->remember("user_location_{$ip}", 60, fn () => getLocation($ip)['iso_code'] ?? null);

        return getCurrencyForClient($iso ? findCountryByGeoip($iso) : null);
    }

    private function getCurrencySymbol(string $currency): string
    {
        return Currency::where('code', $currency)->value('symbol') ?? $currency;
    }

    private function transformProduct(Product $product, string $currency): array
    {
        $highlighted = (bool) $product->highlight;
        $btnClass = $highlighted ? 'btn-primary' : 'btn-dark';

        $plans = $this->getProductPlans($product, $currency);
        $default = collect($plans)->firstWhere('is_default', true);
        $isFree = ($default['price_raw'] ?? 0) == 0;

        return [
            'id' => $product->id,
            'name' => $product->name,
            'short_description' => $product->short_description,
            'description' => $product->description,
            'highlighted' => $highlighted,
            'is_cloud' => in_array($product->id, cloudPopupProducts(), true),
            'display_price' => $default
                ? ($isFree ? __('message.free') : currencyFormat($default['price_raw'], $currency))
                : __('message.free'),
            'display_label' => $default
                ? ($isFree ? strtoupper(__('message.free')) : strtoupper((string) ($default['description'] ?? '')))
                : strtoupper(__('message.free')),
            'plans' => $plans,
            'button' => $this->buildButton($product, $btnClass),
        ];
    }

    private function getProductPlans(Product $product, string $currency): array
    {
        $result = [];
        $minCost = PHP_INT_MAX;
        $defaultId = null;

        foreach ($product->planRelation->sortByDesc('id') as $plan) {
            $planPrice = $plan->planPrice->first();
            if (! $planPrice) {
                continue;
            }

            $rawCost = (float) ($planPrice->add_price ?? 0);
            $offerPct = (float) ($planPrice->offer_price ?? 0);
            $finalCost = $offerPct > 0 ? $rawCost * (1 - $offerPct / 100) : $rawCost;

            $period = $plan->periods->first();
            $periodName = $period?->name ?? '';
            $description = $planPrice->price_description ?? $periodName;

            $months = max(1, (int) round(($period?->days ?? 30) / 30));
            $perMonthCost = $finalCost / $months;

            $formattedFinal = $finalCost == 0 ? __('message.free') : currencyFormat($finalCost, $currency);
            $optionLabel = trim($formattedFinal.($description ? ' '.$description : ''));

            $result[] = [
                'id' => $plan->id,
                'option_label' => $optionLabel,
                'price_raw' => $finalCost,
                'price_display' => $finalCost == 0 ? null : currencyFormat($finalCost, $currency, false),
                'price_per_month' => $finalCost == 0 ? null : currencyFormat($perMonthCost, $currency, false),
                'original_price_raw' => $offerPct > 0 ? $rawCost : null,
                'original_display' => $offerPct > 0 ? currencyFormat($rawCost, $currency, false) : null,
                'original_price_per_month' => $offerPct > 0 ? currencyFormat($rawCost / $months, $currency, false) : null,
                'description' => $description,
                'period' => $periodName,
                'is_default' => false,
            ];

            if ($finalCost < $minCost) {
                $minCost = $finalCost;
                $defaultId = $plan->id;
            }
        }

        foreach ($result as &$p) {
            $p['is_default'] = ($p['id'] === $defaultId);
        }

        return $result;
    }

    private function buildButton(Product $product, string $btnClass): array
    {
        if ($product->add_to_contact == 1) {
            return [
                'label' => __('message.contact_sales'),
                'class' => $btnClass,
                'type' => 'contact',
                'product_id' => null,
                'url' => url('contact-us'),
            ];
        }

        return [
            'label' => __('message.order_now'),
            'class' => $btnClass,
            'type' => 'order',
            'product_id' => $product->id,
            'url' => null,
        ];
    }
}
