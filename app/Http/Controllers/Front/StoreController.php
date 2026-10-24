<?php

namespace App\Http\Controllers\Front;

use App\Http\Controllers\Controller;
use App\Model\CloudDataCenters;
use App\Model\Payment\Currency;
use App\Model\Product\Product;
use App\Model\Product\ProductGroup;
use App\Services\Seo\SeoTemplateFormatter;
use App\User;
use Auth;
use Illuminate\Contracts\Database\Query\Builder;
use Illuminate\Http\JsonResponse;

class StoreController extends Controller
{
    public function getGroups(SeoTemplateFormatter $formatter): JsonResponse
    {
        $groups = ProductGroup::where('hidden', '0')
            ->select('id', 'name', 'headline', 'tagline', 'status', 'meta_title', 'meta_description')
            ->orderBy('id')
            ->get()
            ->map(fn ($g): array => array_merge(
                $g->only(['id', 'name', 'headline', 'tagline']),
                [
                    'status' => (bool) $g->status,
                    'meta_title' => $formatter->resolveShortcodes($g->meta_title, $g->name),
                    'meta_description' => $formatter->resolveShortcodes($g->meta_description, $g->name),
                ]
            ));

        return successResponse('', $groups);
    }

    public function getProducts(int $groupId, SeoTemplateFormatter $formatter): JsonResponse
    {
        $group = ProductGroup::findOrFail($groupId);

        $currency = $this->resolveCurrency();
        $symbol = $this->getCurrencySymbol($currency);

        $groupMetaTitle = $formatter->resolveShortcodes($group->meta_title, $group->name) ?: $formatter->title($group->name);
        $groupMetaDescription = $formatter->resolveShortcodes($group->meta_description, $group->name) ?: $formatter->description($group->name);

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
            // Cheapest PAID plan (after offer): cloud products all carry a free trial plan,
            // so sorting on the plain minimum tied them all at 0 and left them in id order.
            ->sortBy(fn ($p) => $p->planRelation
                ->flatMap(fn ($pl) => $pl->planPrice)
                ->map(fn ($pp): float => (float) $pp->add_price * (1 - (float) ($pp->offer_price ?? 0) / 100))
                ->filter(fn (float $v): bool => $v > 0)
                ->min() ?? 0
            )
            ->values();

        return successResponse('', [
            'group' => array_merge(
                $group->only(['id', 'name', 'headline', 'tagline']),
                [
                    // The page's <h1>. headline is the customer-facing title
                    // ("Helpdesk Pricing"); name is the internal one the Store
                    // dropdown links by ("Helpdesk Perpetual").
                    'heading' => $group->headline ?: $group->name,
                    'status' => (bool) $group->status,
                    'meta_title' => $groupMetaTitle,
                    'meta_description' => $groupMetaDescription,
                ]
            ),
            'currency' => $currency,
            'currency_symbol' => $symbol,
            'cloud_subdomain' => cloudSubDomain() ?? '',
            'data_centers' => CloudDataCenters::select('id', 'cloud_countries', 'cloud_state', 'cloud_city')->get()
                ->map(fn ($dc): array => [
                    'id' => $dc->id,
                    'name' => implode(', ', array_filter([$dc->cloud_city, $dc->cloud_state, $dc->cloud_countries])),
                ])->values(),
            'products' => $products->map(fn (Product $p): array => $this->transformProduct($p, $currency))->values(),
        ]);
    }

    private function resolveCurrency(): string
    {
        if (Auth::check()) {
            /** @var User $authUser */
            $authUser = Auth::user();

            return getCurrencyForClient($authUser->country);
        }

        $ip = request()->ip();
        $iso = cache()->remember('user_location_'.$ip, 60, fn (): mixed => getLocation($ip)['iso_code'] ?? null);

        return getCurrencyForClient($iso ? (string) findCountryByGeoip($iso) : '');
    }

    private function getCurrencySymbol(string $currency): string
    {
        return Currency::where('code', $currency)->value('symbol') ?? $currency;
    }

    /**
     * @return array<mixed>
     */
    private function transformProduct(Product $product, string $currency): array
    {
        $highlighted = (bool) $product->highlight;
        $btnClass = $highlighted ? 'btn-primary' : 'btn-dark';

        $plans = $this->getProductPlans($product, $currency);
        $default = collect($plans)->firstWhere('is_default', operator: true);
        $isFree = ($default['price_raw'] ?? 0) == 0;

        // No default plan counts as free too.
        $displayPrice = __('message.free');
        $displayLabel = strtoupper(__('message.free'));

        if ($default && ! $isFree) {
            $displayPrice = currencyFormat($default['price_raw'], $currency);
            $displayLabel = strtoupper((string) ($default['description'] ?? ''));
        }

        return [
            'id' => $product->id,
            'name' => $product->name,
            'short_description' => $product->short_description,
            'description' => $product->description,
            'highlighted' => $highlighted,
            'is_cloud' => in_array($product->id, cloudPopupProducts(), strict: true),
            'display_price' => $displayPrice,
            'display_label' => $displayLabel,
            'plans' => $plans,
            'button' => $this->buildButton($product, $btnClass),
        ];
    }

    /**
     * @return array<mixed>
     */
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

            $option = $this->planOption($plan, $planPrice, $currency);
            $result[] = $option;

            if ($option['price_raw'] < $minCost) {
                $minCost = $option['price_raw'];
                $defaultId = $plan->id;
            }
        }

        foreach ($result as &$p) {
            $p['is_default'] = ($p['id'] === $defaultId);
        }

        return $result;
    }

    /**
     * One plan as the store shows it: offer price, per-month price, original price.
     *
     * @return array<string, mixed>
     */
    private function planOption(mixed $plan, mixed $planPrice, string $currency): array
    {
        $rawCost = (float) ($planPrice->add_price ?? 0);
        $offerPct = (float) ($planPrice->offer_price ?? 0);
        $hasOffer = $offerPct > 0;
        $finalCost = $hasOffer ? $rawCost * (1 - $offerPct / 100) : $rawCost;
        $isFree = $finalCost == 0;

        $period = $plan->periods->first();
        $periodName = $period->name ?? '';
        $description = $planPrice->price_description ?? $periodName;

        $months = max(1, (int) round((int) ($period->days ?? 30) / 30));
        $amount = fn (float $cost): string => currencyFormat($cost, $currency, includeSymbol: false);

        $formattedFinal = $isFree ? __('message.free') : currencyFormat($finalCost, $currency);

        return [
            'id' => $plan->id,
            'option_label' => trim($formattedFinal.($description ? ' '.$description : '')),
            'price_raw' => $finalCost,
            'price_display' => $isFree ? null : $amount($finalCost),
            'price_per_month' => $isFree ? null : $amount($finalCost / $months),
            'original_price_raw' => $hasOffer ? $rawCost : null,
            'original_display' => $hasOffer ? $amount($rawCost) : null,
            'original_price_per_month' => $hasOffer ? $amount($rawCost / $months) : null,
            'description' => $description,
            'period' => $periodName,
            'is_default' => false,
        ];
    }

    /**
     * @return array<mixed>
     */
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
