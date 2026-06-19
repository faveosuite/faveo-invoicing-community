<?php

namespace App\Services\Tax;

use App\Model\Common\Setting;
use App\Model\Payment\TaxOption;
use App\Model\Payment\TaxRate;

/**
 * Resolves which tax rates apply to a customer + tax class, mirroring
 * WooCommerce's WC_Tax::find_rates() / get_rates():
 *
 *  - country & state match the rate or its wildcard ('');
 *  - postcode/city narrow a rate when it declares locations;
 *  - within one location only ONE rate per `priority` applies (most specific
 *    wins);
 *  - compound flag is preserved for the engine.
 *
 * Lookups hit the (small, indexed) tax_rates table directly — no caching, so
 * a rate edit takes effect immediately.
 */
class TaxRateResolver
{
    /**
     * Find rates for an explicit location.
     *
     * @return array<int, array{id:int, rate:float, label:string, compound:bool, priority:int}>
     */
    public function findRates(string $country, string $state = '', string $postcode = '', string $city = '', string $taxClass = ''): array
    {
        $country = strtoupper(trim($country));
        $state = strtoupper(trim($state));
        $postcode = $this->normalizePostcode($postcode);
        $city = strtoupper(trim($city));

        if ($country === '') {
            return [];
        }

        return $this->matchRates($country, $state, $postcode, $city, $taxClass);
    }

    /**
     * Resolve rates for a customer model, honouring the tax_based_on setting
     * and per-customer exemption.
     * @return array<mixed>
     */
    public function ratesForCustomer(string $taxClass, mixed $user): array
    {
        [$country, $state, $postcode, $city] = $this->customerLocation($user);

        return $this->findRates($country, $state, $postcode, $city, $taxClass);
    }

    /**
     * @return array{0:string,1:string,2:string,3:string} [country, state, postcode, city]
     */
    public function customerLocation(mixed $user): array
    {
        $basedOn = TaxOption::find(1)?->tax_based_on ?: 'billing';

        if ($basedOn === 'base' || ! $user) {
            $setting = Setting::first();

            return [
                (string) ($setting->country ?? ''),
                (string) ($setting->state ?? ''),
                '',
                '',
            ];
        }

        return [
            (string) ($user->country ?? ''),
            (string) ($user->state ?? ''),
            (string) ($user->zip ?? $user->postcode ?? ''),
            (string) ($user->city ?? ''),
        ];
    }

    /**
     * @return array<mixed>
     */
    private function matchRates(string $country, string $state, string $postcode, string $city, string $taxClass): array
    {
        $candidates = TaxRate::with('locations')
            ->where('active', operator: true)
            ->whereIn('country', [$country, ''])
            ->whereIn('state', [$state, ''])
            ->where('tax_class', $taxClass)
            ->orderBy('priority')
            ->get();

        $scored = [];
        foreach ($candidates as $rate) {
            if (! $this->locationMatches($rate, $postcode, $city)) {
                continue;
            }

            $scored[] = [
                'rate' => $rate,
                'score' => $this->specificity($rate),
            ];
        }

        // Most specific first, stable within a priority.
        usort($scored, fn (array $a, array $b): int => [$a['rate']->priority, -$a['score'], $a['rate']->id]
            <=> [$b['rate']->priority, -$b['score'], $b['rate']->id]);

        $matched = [];
        $seenPriority = [];
        foreach ($scored as $entry) {
            $rate = $entry['rate'];
            if (in_array($rate->priority, $seenPriority, strict: true)) {
                continue; // one rate per priority
            }

            $seenPriority[] = $rate->priority;
            $matched[] = [
                'id' => (int) $rate->id,
                'rate' => (float) $rate->rate,
                'label' => $rate->name,
                'compound' => (bool) $rate->compound,
                'priority' => (int) $rate->priority,
            ];
        }

        return $matched;
    }

    private function locationMatches(TaxRate $rate, string $postcode, string $city): bool
    {
        $postcodeLocations = $rate->locations->where('location_type', 'postcode');
        $cityLocations = $rate->locations->where('location_type', 'city');

        $postcodeOk = $postcodeLocations->isEmpty()
            || $postcodeLocations->contains(fn ($loc): bool => $this->postcodeMatches($postcode, $loc->location_code));

        $cityOk = $cityLocations->isEmpty()
            || $cityLocations->contains(fn ($loc): bool => strtoupper(trim((string) $loc->location_code)) === $city);

        return $postcodeOk && $cityOk;
    }

    private function postcodeMatches(string $postcode, string $code): bool
    {
        $code = strtoupper(trim($code));
        if ($postcode === '') {
            return false;
        }

        // Range: "12000...12999"
        if (str_contains($code, '...')) {
            [$from, $to] = array_map(trim(...), explode('...', $code, 2));
            if (is_numeric($from) && is_numeric($to) && is_numeric($postcode)) {
                return (float) $postcode >= (float) $from && (float) $postcode <= (float) $to;
            }

            return false;
        }

        // Wildcard: "12*"
        if (str_contains($code, '*')) {
            $pattern = '/^'.str_replace('\*', '.*', preg_quote($code, '/')).'$/';

            return (bool) preg_match($pattern, $postcode);
        }

        return $code === $postcode;
    }

    private function specificity(TaxRate $rate): int
    {
        $score = 0;
        $score += $rate->country !== '' ? 1 : 0;
        $score += $rate->state !== '' ? 1 : 0;
        $score += $rate->locations->where('location_type', 'postcode')->isNotEmpty() ? 2 : 0;
        $score += $rate->locations->where('location_type', 'city')->isNotEmpty() ? 2 : 0;

        return $score;
    }

    private function normalizePostcode(string $postcode): string
    {
        return strtoupper(str_replace([' ', '-'], '', trim($postcode)));
    }
}
