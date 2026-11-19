<?php

declare(strict_types=1);

namespace App\Services;

use App\License\Models\License;
use App\Model\Order\Order;
use App\Model\Order\OrderEntitlement;
use App\Model\Payment\Plan;
use App\Model\Product\Entitlement;
use App\Model\Product\Product;
use App\Model\Product\Subscription;
use Illuminate\Database\Eloquent\Collection;

/**
 * Works out what a license lets the customer use: one value per feature code,
 * ['sla' => true, 'agents' => 25, 'data_region' => 'eu']. payload() turns that into the block
 * the installed product reads.
 *
 * The plan's features (its value, or the feature default) → bought extras (order_entitlements)
 * → admin override. Features the plan doesn't include aren't sent. The plan's values are copied onto the license at
 * purchase (licenses.entitlement_base), so editing a plan later doesn't change what existing
 * customers bought until an admin applies it to them. Overrides (licenses.entitlement_overrides)
 * are per license and can end on a date, after which the plan value applies again.
 *
 * `agents` is special: the plan's Pricing sets how many are included, the agent count lives
 * in the license code's last four digits (installed helpdesks read it there) and changes only
 * through the paid agent flow, so a license always reads it from the code. UNLIMITED (-1) means no limit.
 */
class EntitlementService
{
    public const string AGENTS = 'agents';

    /** Version of the payload() block. Bump only for a change old products can't read. */
    public const int PAYLOAD_VERSION = 1;

    /** Number value meaning "no limit" (agents: 0000 in the license code). */
    public const int UNLIMITED = -1;

    /**
     * What a plan gives: the active features it includes, at its value or the feature's default.
     * Per-agent products also get the plan's agents.
     *
     * @return array<string, bool|int|string|null>
     */
    public function forPlan(int $productId, ?int $planId): array
    {
        $plan = $planId ? Plan::find($planId) : null;
        if (! $plan) {
            return [];
        }

        $values = $plan->entitlements()->pluck('plan_entitlements.value', 'entitlements.id');
        $result = [];
        foreach (Entitlement::whereIn('id', $values->keys())->where('is_active', true)->orderBy('sort')->orderBy('id')->get() as $item) {
            if (! self::isAgents($item)) {
                $result[$item->code] = $item->cast($values[$item->id] ?? $item->default_value);
            }
        }
        if (Product::whereKey($productId)->value('show_agent')) {
            $result[self::AGENTS] = self::planAgents($plan);
        }

        return $result;
    }

    public static function isAgents(Entitlement $item): bool
    {
        return $item->code === self::AGENTS;
    }

    /**
     * The plan charges for every agent, none included. Marked by an agents row on the plan (value
     * 0); its agent count (plan_prices.no_of_agents) is then 1 so customers start at one agent —
     * a license code ending 0000 would mean unlimited.
     */
    public static function paysForEveryAgent(?Plan $plan): bool
    {
        return $plan !== null && $plan->entitlements()->where('code', self::AGENTS)->exists();
    }

    /** The agents a plan starts with (plan_prices.no_of_agents); 0 there means unlimited, sent as UNLIMITED. */
    public static function planAgents(Plan $plan): int
    {
        $agents = (int) $plan->planPrice()->value('no_of_agents');

        return $agents > 0 ? $agents : self::UNLIMITED;
    }

    /**
     * The built-in Agents feature. Its value comes from the plan's Pricing (No. of Agents) and
     * the license code; plans set its per-agent price. It can't be removed.
     */
    public static function agents(): Entitlement
    {
        return Entitlement::firstOrCreate(
            ['code' => self::AGENTS],
            ['label' => 'Agents', 'type' => 'number', 'default_value' => null, 'is_active' => true, 'sort' => 0],
        );
    }

    /** Copies the plan's values onto the license. Called when the license is created and when its plan changes. */
    public function grant(License $license, ?int $planId): void
    {
        $license->entitlement_base = $this->forPlan($license->product_id, $planId);
        $license->save();
    }

    /**
     * Every item that applies to the license, with where its value comes from.
     *
     * @return list<array{item: Entitlement, plan: bool|int|string|null, extra: int, override: string|null, until: string|null, value: bool|int|string|null, locked: bool}>
     */
    public function rows(License $license): array
    {
        $base = $license->entitlement_base ?? [];
        $overrides = $this->activeOverrides($license);
        $extras = OrderEntitlement::whereHas('order', fn ($q) => $q->where('number', $license->license_order_number))
            ->where('quantity', '>', 0)
            ->pluck('quantity', 'entitlement_id');
        $agents = $this->agentsFromCode($license->license_code);
        $codes = [...array_keys($base), ...array_keys($overrides), ...($agents !== null ? [self::AGENTS] : [])];
        $rows = [];

        foreach ($this->items($codes, $extras->keys()->all()) as $item) {
            $code = $item->code;
            $inBase = array_key_exists($code, $base);

            // Archived items stay only for licenses that already have them.
            if (! $item->is_active && ! $inBase && ! isset($overrides[$code])) {
                continue;
            }

            // Not in the plan: starts empty (off / 0), so only what was bought or overridden counts.
            $plan = $inBase ? $base[$code] : $item->cast(null);
            $locked = $code === self::AGENTS && $agents !== null;
            $override = $locked ? null : ($overrides[$code] ?? null);
            $extra = (int) ($extras[$item->id] ?? 0);

            // Bought units add to the plan's number; a bought on/off feature is on.
            $withExtra = match (true) {
                $extra === 0 => $plan,
                $item->type === 'number' => (int) $plan + $extra,
                $item->type === 'boolean' => true,
                default => $plan,
            };

            $rows[] = [
                'item' => $item,
                'plan' => $plan,
                'extra' => $extra,
                'override' => $override['value'] ?? null,
                'until' => $override['until'] ?? null,
                'value' => $locked ? $agents : ($override ? $item->cast($override['value']) : $withExtra),
                'locked' => $locked,
            ];
        }

        return $rows;
    }

    /**
     * The license's final value per feature code.
     *
     * @return array<string, bool|int|string|null>
     */
    public function resolve(License $license): array
    {
        $result = [];
        foreach ($this->rows($license) as $row) {
            $result[$row['item']->code] = $row['value'];
        }

        return $result;
    }

    /**
     * The block the installed product reads, in the license file and the license check:
     *  - features: on/off features that are on
     *  - limits:   numbers (null = unlimited)
     *  - values:   select / text choices
     *  - ends:     an on/off feature given only by an override until a date (Y-m-d); the product
     *              switches it off after that day without waiting for a new license
     *  - expires:  license / updates / support end dates (Y-m-d, null = never)
     * Anything not listed is off (deny by default).
     *
     * @return array{v: int, features: list<string>, limits: array<string, int|null>, values: array<string, string>, ends: array<string, string>, expires: array<string, string|null>, issued_at: string}
     */
    public function payload(License $license): array
    {
        $features = [];
        $limits = [];
        $values = [];
        $ends = [];

        foreach ($this->rows($license) as $row) {
            $code = $row['item']->code;
            $value = $row['value'];

            if ($row['item']->type === 'boolean') {
                if ($value === true) {
                    $features[] = $code;
                }
            } elseif ($row['item']->type === 'number') {
                // A number left empty on the plan means no limit, like agents' 0000.
                $limits[$code] = $value === null || $value === self::UNLIMITED ? null : (int) $value;
            } elseif ($value !== null) {
                $values[$code] = (string) $value;
            }

            // On only because of a dated override: tell the product when it ends.
            if ($value === true && $row['until'] !== null && $row['plan'] !== true && $row['extra'] === 0) {
                $ends[$code] = $row['until'];
            }
        }

        $day = fn (?string $date): ?string => $date && ! str_starts_with($date, '0000') ? substr($date, 0, 10) : null;

        return [
            'v' => self::PAYLOAD_VERSION,
            'features' => $features,
            'limits' => $limits,
            'values' => $values,
            'ends' => $ends,
            'expires' => [
                'license' => $day($license->license_expire_date),
                'updates' => $day($license->license_updates_date),
                'support' => $day($license->license_support_date),
            ],
            'issued_at' => now()->toAtomString(),
        ];
    }

    /**
     * The license's list with labels, for showing to the customer.
     *
     * @return list<array{label: string, type: string, value: bool|int|string|null}>
     */
    public function forDisplay(License $license): array
    {
        return array_map(fn (array $row): array => [
            'label' => $row['item']->label,
            'type' => $row['item']->type,
            'value' => $row['value'],
        ], $this->rows($license));
    }

    /** Sets one item to a value for this license only, optionally until a date (Y-m-d). */
    public function setOverride(License $license, Entitlement $item, string $value, ?string $until): void
    {
        $overrides = $license->entitlement_overrides ?? [];
        $overrides[$item->code] = ['value' => $value, 'until' => $until];
        $license->entitlement_overrides = $overrides;
        $license->save();
    }

    public function removeOverride(License $license, Entitlement $item): void
    {
        $overrides = $license->entitlement_overrides ?? [];
        unset($overrides[$item->code]);
        $license->entitlement_overrides = $overrides ?: null;
        $license->save();
    }

    /**
     * Moves an old license's live overrides to the license that replaces it (plan upgrade or
     * downgrade).
     */
    public function carryOverrides(License $from, License $to): void
    {
        $kept = $this->activeOverrides($from);

        if ($kept !== []) {
            $to->entitlement_overrides = [...($to->entitlement_overrides ?? []), ...$kept];
            $to->save();
        }
    }

    /**
     * Re-copies the plan's current values onto every live license on it. Returns how many
     * licenses were updated. Overrides are kept.
     */
    public function applyPlanToLicenses(Plan $plan): int
    {
        $values = $this->forPlan((int) $plan->product, $plan->id);
        $count = 0;

        // ponytail: one UPDATE per chunk of license ids would be faster; fine at current plan sizes.
        $this->liveLicenses($plan)
            ->each(function (License $license) use ($values, &$count): void {
                $license->entitlement_base = $values;
                $license->save();
                $count++;
            });

        return $count;
    }

    /**
     * Licenses of the plan's orders that aren't terminated.
     *
     * @return \Illuminate\Database\Eloquent\Builder<License>
     */
    public function liveLicenses(Plan $plan): \Illuminate\Database\Eloquent\Builder
    {
        $orderNumbers = Order::whereIn('id', Subscription::where('plan_id', $plan->id)->select('order_id'))
            ->where('order_status', '!=', 'Terminated')
            ->select('number');

        return License::whereIn('license_order_number', $orderNumbers);
    }

    /**
     * The feature that unlocks a plugin product, created on first use: an on/off feature coded
     * from the SKU (WHT-APP-PRO → wht_app_pro), labelled with the product name, under "Plugins".
     * The code is set once; editing the SKU later doesn't change it. An existing on/off feature
     * with that code is reused; a clash with anything else, or no SKU, appends the product id.
     */
    public static function pluginFeature(Product $plugin): Entitlement
    {
        if ($plugin->unlocks_entitlement_id !== null && ($item = Entitlement::find($plugin->unlocks_entitlement_id))) {
            return $item;
        }

        $code = substr(trim((string) preg_replace('/[^a-z0-9]+/', '_', strtolower((string) $plugin->product_sku)), '_'), 0, 40);
        $code = preg_match('/^[a-z]/', $code) === 1 ? $code : 'plugin_'.ltrim($code.'_'.$plugin->id, '_');
        $item = Entitlement::where('code', $code)->first();
        if ($item && ($item->type !== 'boolean' || Product::where('unlocks_entitlement_id', $item->id)->whereKeyNot($plugin->id)->exists())) {
            $code .= '_'.$plugin->id;
            $item = null;
        }
        $item ??= Entitlement::create([
            'code' => $code, 'category' => 'Plugins', 'label' => $plugin->name, 'type' => 'boolean',
            'default_value' => '0', 'is_active' => true, 'sort' => (int) Entitlement::max('sort') + 1,
        ]);

        Product::whereKey($plugin->id)->update(['unlocks_entitlement_id' => $item->id]);
        $plugin->unlocks_entitlement_id = $item->id;

        return $item;
    }

    /**
     * Plugin products this license may run and download: those whose feature it has (from its
     * plan, a bought add-on or an admin override).
     *
     * @return list<int>
     */
    public function pluginIds(License $license): array
    {
        $granted = array_keys(array_filter($this->resolve($license), fn ($value): bool => $value === true));

        return $granted === [] ? [] : array_values(Product::whereIn('unlocks_entitlement_id', Entitlement::whereIn('code', $granted)->select('id'))
            ->pluck('id')->map(fn ($id): int => (int) $id)->all());
    }

    /**
     * Plugin products a product's plans include or sell — what "bundled with" used to mean.
     *
     * @return list<int>
     */
    public static function productPluginIds(int $productId): array
    {
        $plans = Plan::where('product', $productId)->select('id');
        $features = Entitlement::where(fn ($q) => $q
            ->whereIn('id', \DB::table('plan_entitlements')->whereIn('plan_id', $plans)->select('entitlement_id'))
            ->orWhereIn('id', \DB::table('entitlement_prices')->whereIn('plan_id', $plans)->select('entitlement_id')))
            ->select('id');

        return array_values(Product::whereIn('unlocks_entitlement_id', $features)->pluck('id')->map(fn ($id): int => (int) $id)->all());
    }

    /**
     * Overrides whose end date hasn't passed.
     *
     * @return array<string, array{value: string, until: string|null}>
     */
    private function activeOverrides(License $license): array
    {
        $today = now()->toDateString();

        return array_filter(
            $license->entitlement_overrides ?? [],
            fn (array $o): bool => $o['until'] === null || $o['until'] >= $today,
        );
    }

    /** Agent count from the license code's last four digits (0000 = unlimited → UNLIMITED), or null for codes without them. */
    private function agentsFromCode(string $code): ?int
    {
        $digits = substr($code, -4);
        if (strlen($code) <= 4 || ! ctype_digit($digits)) {
            return null;
        }

        return (int) $digits > 0 ? (int) $digits : self::UNLIMITED;
    }

    /**
     * The features with these codes or ids, archived included, in display order.
     *
     * @param  list<string>  $codes
     * @param  array<int, mixed>  $ids
     * @return Collection<int, Entitlement>
     */
    private function items(array $codes, array $ids): Collection
    {
        if ($codes === [] && $ids === []) {
            return new Collection;
        }

        return Entitlement::query()
            ->where(fn ($q) => $q->whereIn('id', $ids)->orWhereIn('code', $codes))
            ->orderBy('sort')->orderBy('id')->get();
    }
}
