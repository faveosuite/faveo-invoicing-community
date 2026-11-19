<?php

declare(strict_types=1);

namespace App\Services;

use App\License\Models\License;
use App\Model\Order\Order;
use App\Model\Order\OrderEntitlement;
use App\Model\Payment\Plan;
use App\Model\Product\Entitlement;
use App\Model\Product\Grant;
use App\Model\Product\Product;
use App\Model\Product\Subscription;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

/**
 * Works out what a license lets the customer use: one value per feature code,
 * ['sla' => true, 'agents' => 25, 'data_region' => 'eu']. payload() turns that into the block
 * the installed product reads.
 *
 * Every right is a grant (see Grant): the plan's value (copied at purchase, so editing a plan
 * later doesn't change what existing customers bought until an admin applies it to them) →
 * bought extras → admin override, which can end on a date, after which the plan value applies
 * again. Features with no grant aren't sent. The license's dates are grants too (run, updates,
 * support), kept in step with its date columns. Each change bumps licenses.version.
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

    /** Copies the plan's values onto the license, and its order's extras. Called when the license is created and when its plan changes. */
    public function grant(License $license, ?int $planId): void
    {
        $changed = $this->writePlan($license, $this->forPlan($license->product_id, $planId));
        $this->bumpIf($license, $this->writeExtras($license) || $changed);
    }

    /** Re-reads the license's bought extras (order_entitlements) into its grants. */
    public function syncExtras(License $license): void
    {
        $this->bumpIf($license, $this->writeExtras($license));
    }

    /** Keeps the run / updates / support grants equal to the license's date columns. */
    public function syncDates(License $license): void
    {
        $rows = [];
        foreach (Grant::DATES as $code => $column) {
            $rows[$code] = ['value' => null, 'ends_at' => self::day($license->{$column})];
        }
        $this->bumpIf($license, $this->replace($license, Grant::LICENSE, $rows));
    }

    /**
     * When the license, its updates and its support end (Y-m-d, null = never). Read from the date
     * grants; a license without them yet falls back to its columns.
     *
     * @return array{license: string|null, updates: string|null, support: string|null}
     */
    public function dates(License $license): array
    {
        $grants = Grant::where('license_id', $license->id)->where('source', Grant::LICENSE)->get()->keyBy('code');
        $date = fn (string $code): ?string => ($grant = $grants->get($code)) !== null
            ? $grant->ends_at?->toDateString()
            : self::day($license->{Grant::DATES[$code]});

        return ['license' => $date('run'), 'updates' => $date('updates'), 'support' => $date('support')];
    }

    /**
     * Every item that applies to the license, with where its value comes from.
     *
     * @return list<array{item: Entitlement, plan: bool|int|string|null, extra: int, override: string|null, until: string|null, value: bool|int|string|null, locked: bool}>
     */
    public function rows(License $license): array
    {
        $grants = Grant::where('license_id', $license->id)->where('source', '!=', Grant::LICENSE)->active()->get()->groupBy('source');
        $base = ($grants[Grant::PLAN] ?? collect())->pluck('value', 'code')->all();
        $extras = ($grants[Grant::EXTRA] ?? collect())->pluck('value', 'code')->all();
        $overrides = ($grants[Grant::ADMIN] ?? collect())->mapWithKeys(fn (Grant $g): array => [
            $g->code => ['value' => (string) $g->value, 'until' => $g->ends_at?->toDateString()],
        ])->all();
        $agents = $this->agentsFromCode($license->license_code);
        $codes = [...array_keys($base), ...array_keys($extras), ...array_keys($overrides), ...($agents !== null ? [self::AGENTS] : [])];
        $rows = [];

        foreach ($this->items($codes) as $item) {
            $code = $item->code;
            $inBase = array_key_exists($code, $base);

            // Archived items stay only for licenses that already have them.
            if (! $item->is_active && ! $inBase && ! isset($overrides[$code])) {
                continue;
            }

            // Not in the plan: starts empty (off / 0), so only what was bought or overridden counts.
            $plan = $item->cast($inBase ? $base[$code] : null);
            $locked = $code === self::AGENTS && $agents !== null;
            $override = $locked ? null : ($overrides[$code] ?? null);
            $extra = (int) ($extras[$code] ?? 0);

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

        return [
            'v' => self::PAYLOAD_VERSION,
            'features' => $features,
            'limits' => $limits,
            'values' => $values,
            'ends' => $ends,
            'expires' => $this->dates($license),
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
        $rows = $this->current($license, Grant::ADMIN);
        $rows[$item->code] = ['value' => $value, 'ends_at' => $until];
        $this->bumpIf($license, $this->replace($license, Grant::ADMIN, $rows));
    }

    public function removeOverride(License $license, Entitlement $item): void
    {
        $rows = $this->current($license, Grant::ADMIN);
        unset($rows[$item->code]);
        $this->bumpIf($license, $this->replace($license, Grant::ADMIN, $rows));
    }

    /**
     * Moves an old license's live overrides to the license that replaces it (plan upgrade or
     * downgrade).
     */
    public function carryOverrides(License $from, License $to): void
    {
        $kept = Grant::where('license_id', $from->id)->where('source', Grant::ADMIN)->active()->get()
            ->mapWithKeys(fn (Grant $g): array => [$g->code => ['value' => $g->value, 'ends_at' => $g->ends_at?->toDateString()]])
            ->all();

        if ($kept !== []) {
            $this->bumpIf($to, $this->replace($to, Grant::ADMIN, [...$this->current($to, Grant::ADMIN), ...$kept]));
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

        // ponytail: one write per license; batch per chunk if plans grow to tens of thousands of licenses.
        $this->liveLicenses($plan)
            ->each(function (License $license) use ($values, &$count): void {
                $this->bumpIf($license, $this->writePlan($license, $values));
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
     * Replaces the license's plan grants with these values.
     *
     * @param  array<string, bool|int|string|null>  $values
     */
    private function writePlan(License $license, array $values): bool
    {
        return $this->replace($license, Grant::PLAN, array_map(fn ($value): array => [
            'value' => match (true) {
                $value === null => null,
                is_bool($value) => $value ? '1' : '0',
                default => (string) $value,
            },
            'ends_at' => null,
        ], $values));
    }

    private function writeExtras(License $license): bool
    {
        $rows = [];
        if ($license->license_order_number !== null) {
            $bought = OrderEntitlement::with('entitlement:id,code')
                ->whereHas('order', fn ($q) => $q->where('number', $license->license_order_number))
                ->where('quantity', '>', 0)->get();
            foreach ($bought as $extra) {
                $rows[$extra->entitlement->code] = ['value' => (string) $extra->quantity, 'ends_at' => null];
            }
        }

        return $this->replace($license, Grant::EXTRA, $rows);
    }

    /**
     * The license's grants from one source, as code => {value, ends_at}.
     *
     * @return array<string, array{value: string|null, ends_at: string|null}>
     */
    private function current(License $license, string $source): array
    {
        return Grant::where('license_id', $license->id)->where('source', $source)->get()
            ->mapWithKeys(fn (Grant $g): array => [$g->code => ['value' => $g->value, 'ends_at' => $g->ends_at?->toDateString()]])
            ->all();
    }

    /**
     * Makes the license's grants from one source exactly these rows. Returns whether anything changed.
     *
     * @param  array<string, array{value: string|null, ends_at: string|null}>  $rows
     */
    private function replace(License $license, string $source, array $rows): bool
    {
        $current = $this->current($license, $source);
        ksort($current);
        ksort($rows);
        if ($current === $rows) {
            return false;
        }

        DB::transaction(function () use ($license, $source, $rows): void {
            Grant::where('license_id', $license->id)->where('source', $source)->delete();
            $now = now();
            Grant::insert(array_map(fn (string $code, array $row): array => [
                'license_id' => $license->id, 'code' => $code, 'value' => $row['value'], 'source' => $source,
                'ends_at' => $row['ends_at'], 'created_at' => $now, 'updated_at' => $now,
            ], array_keys($rows), $rows));
        });

        return true;
    }

    /** A product compares versions to tell a newer record, so every grant change counts one up. */
    private function bumpIf(License $license, bool $changed): void
    {
        if ($changed) {
            DB::table('licenses')->where('id', $license->id)->increment('version');
        }
    }

    /** A stored date as Y-m-d; empty or 0000-00-00 means never (null). */
    public static function day(mixed $date): ?string
    {
        $date = (string) $date;

        return $date !== '' && ! str_starts_with($date, '0000') ? substr($date, 0, 10) : null;
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
     * The features with these codes, archived included, in display order.
     *
     * @param  list<string>  $codes
     * @return Collection<int, Entitlement>
     */
    private function items(array $codes): Collection
    {
        return $codes === [] ? new Collection
            : Entitlement::whereIn('code', array_unique($codes))->orderBy('sort')->orderBy('id')->get();
    }
}
