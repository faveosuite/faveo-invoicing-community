<?php

declare(strict_types=1);

namespace Database\Seeders\v4_0_2_8_RC_1;

use App\License\Models\License;
use App\Model\Order\Order;
use App\Model\Order\OrderEntitlement;
use App\Model\Payment\Plan;
use App\Model\Payment\PlanPrice;
use App\Model\Product\Entitlement;
use App\Model\Product\Grant;
use App\Model\Product\Product;
use App\Model\Product\Subscription;
use App\Services\AgentCountService;
use App\Services\EntitlementService;
use App\Services\LegacyDates;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Schema;

class DatabaseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->packageRemoval();
        $this->fillEmptyNumberLimits();
        $this->seedAgentsEntitlements();
        $this->seedPlanAgentRows();
        $this->seedOrderAgentExtras();
        $this->seedCartAgentExtras();
        $this->seedUnlimitedAgentsAsEmpty();
        $this->seedUniquePluginLabels();
        $this->seedUnlimitedLicenseOverrides();
        $this->dropAgentColumns();
        $this->seedFixedCouponCurrency();
        $this->stripExtrasFromInvoiceItemNames();
        $this->seedPluginFeatures();
        $this->seedDateFeatures();
        $this->seedLicenseDateGrants();
        $this->seedGrantOwners();
        $this->seedSubscriptionDateGrants();
        $this->moveLicensePermissionsToProducts();
        $this->dropLicenseTypes();
    }

    /**
     * Plugin features named alike (two products called "Adhoc Approval", free and Pro) look the same on
     * plans and to customers: every one after the first gets its product's SKU added.
     */
    public function seedUniquePluginLabels(): void
    {
        $plugins = Product::whereNotNull('unlocks_entitlement_id')->pluck('product_sku', 'unlocks_entitlement_id');
        foreach (Entitlement::where('category', 'Plugins')->orderBy('id')->get()->groupBy('label') as $label => $same) {
            foreach ($same->slice(1) as $item) {
                $sku = $plugins[$item->id] ?? null;
                if ($sku) {
                    $item->update(['label' => $label.' ('.$sku.')']);
                }
            }
        }
    }

    /**
     * A license that runs unlimited agents (code ending 0000) on a plan that now counts them, with no
     * admin override, is billed for the plan's count while the product gives unlimited (the plan's
     * Agents row changed after the sale). Record the unlimited the customer has as an override, so
     * billing matches the license and agents can't be sold to it. The license is never rewritten.
     */
    public function seedUnlimitedLicenseOverrides(): void
    {
        $agents = EntitlementService::agents();
        $service = resolve(EntitlementService::class);
        Order::where('order_status', '!=', 'Terminated')->whereNotNull('serial_key')->with('subscription')->chunkById(500, function ($orders) use ($agents, $service): void {
            foreach ($orders as $order) {
                if (substr((string) $order->serial_key, -4) !== '0000'
                    || EntitlementService::includedAgents(Plan::find((int) $order->subscription?->plan_id)) === null
                    || resolve(AgentCountService::class)->hasOverride($order)) {
                    continue;
                }
                $owner = License::where('license_order_number', $order->number)->first() ?? $order->subscription;
                if ($owner !== null) {
                    $service->setOverride($owner, $agents, null, null);
                    $this->command?->warn("Order {$order->id}: license runs unlimited agents on a counted plan; recorded as an unlimited override");
                }
            }
        });
    }

    /** Unlimited agents were stored as -1 (plan grants and admin overrides); now, like any number, as empty. */
    public function seedUnlimitedAgentsAsEmpty(): void
    {
        Grant::where('code', EntitlementService::AGENTS)->where('value', '-1')->update(['value' => null, 'updated_at' => now()]);
    }

    public function packageRemoval(): void
    {
        $packages = [
            'darryldecode/cart',         // no Laravel 13 support; cart lives in App\Model\Cart\*
            'driftingly/rector-laravel', // Rector dropped as a dev tool
            'rector/rector',             // pulled in by driftingly/rector-laravel
            'webmozart/assert',          // only Rector used it
        ];

        $configs = [
            'shopping_cart.php', // belonged to darryldecode/cart
        ];

        foreach ($packages as $package) {

            $packagePath = base_path('vendor/'.$package);

            if (! File::exists($packagePath)) {
                continue;
            }

            File::deleteDirectory($packagePath);

            $authorPath = dirname($packagePath);

            if (
                File::exists($authorPath)
                && File::isDirectory($authorPath)
                && count(File::files($authorPath)) === 0
                && count(File::directories($authorPath)) === 0
            ) {
                File::deleteDirectory($authorPath);
            }
        }

        foreach ($configs as $config) {

            $configPath = config_path($config);

            if (File::exists($configPath)) {
                File::delete($configPath);
            }
        }
    }

    /**
     * An empty number on a plan now means unlimited; before, it meant the feature's default. Plans
     * saved that way get the default written in, so they keep their limit. Agents are left alone
     * (empty was already unlimited), and so is a feature without a default. Safe to run again.
     */
    public function fillEmptyNumberLimits(): void
    {
        $agents = Entitlement::where('code', EntitlementService::AGENTS)->value('id');
        foreach (Entitlement::where('type', 'number')->where('is_active', true)->whereNotNull('default_value')->where('default_value', '!=', '')
            ->when($agents !== null, fn ($q) => $q->whereKeyNot($agents))->get(['id', 'default_value']) as $item) {
            DB::table('plan_entitlements')->where('entitlement_id', $item->id)
                ->where(fn ($q) => $q->whereNull('value')->orWhere('value', ''))
                ->update(['value' => $item->default_value, 'updated_at' => now()]);
        }
    }

    /**
     * Agents are a built-in feature. Each per-agent plan whose agent count customers could change
     * (the product's old "can modify agents" box, or a cloud product) gets its per-agent price in
     * every currency it's sold in: plan price ÷ its agents (same for the renewal price, same
     * offer %), so customers pay exactly what they did. A plan's agent price is now what lets
     * customers change the count, so plans that had it fixed get none. Prices an admin already
     * set are left alone.
     */
    public function seedAgentsEntitlements(): void
    {
        // Runs once, before the agent columns are dropped: afterwards admins own these prices.
        if (! Schema::hasColumn('products', 'show_agent') || ! Schema::hasColumn('plan_prices', 'no_of_agents')) {
            return;
        }
        $agents = EntitlementService::agents();

        $rows = DB::table('plan_prices')
            ->join('plans', 'plans.id', '=', 'plan_prices.plan_id')
            ->join('products', 'products.id', '=', 'plans.product')
            ->where('products.show_agent', 1)
            ->where('plan_prices.no_of_agents', '>', 0)
            ->where(fn ($q) => Schema::hasColumn('products', 'can_modify_agent')
                ? $q->where('products.can_modify_agent', 1)->orWhereIn('products.id', cloudPopupProducts())
                : $q->whereIn('products.id', cloudPopupProducts()))
            ->get(['plan_prices.plan_id', 'plan_prices.currency', 'plan_prices.add_price', 'plan_prices.renew_price', 'plan_prices.offer_price', 'plan_prices.no_of_agents']);

        foreach ($rows->unique(fn (object $r): string => $r->plan_id.'|'.$r->currency) as $r) {
            $agents->prices()->firstOrCreate(
                ['plan_id' => $r->plan_id, 'currency' => $r->currency],
                [
                    'unit_price' => round((float) $r->add_price / (int) $r->no_of_agents, 4),
                    'renew_price' => round((float) $r->renew_price / (int) $r->no_of_agents, 4),
                    'offer_percent' => (float) $r->offer_price > 0 ? (float) $r->offer_price : null,
                ],
            );
        }
    }

    /**
     * Plans of products sold by agents get the Agents row with the count their price includes
     * (plan_prices.no_of_agents; 0 or none = unlimited). Rows already there with no value held the
     * count in no_of_agents too, so they get it the same way; "every agent paid" rows ('0') stay.
     */
    public function seedPlanAgentRows(): void
    {
        if (! Schema::hasColumn('products', 'show_agent') || ! Schema::hasColumn('plan_prices', 'no_of_agents')) {
            return;
        }
        $agentsId = EntitlementService::agents()->id;

        $plans = DB::table('plans')->join('products', 'products.id', '=', 'plans.product')->where('products.show_agent', 1)->pluck('plans.id');
        foreach ($plans as $planId) {
            $row = DB::table('plan_entitlements')->where('plan_id', $planId)->where('entitlement_id', $agentsId)->first();
            if ($row !== null && $row->value !== null && $row->value !== '') {
                continue;
            }
            $count = (int) DB::table('plan_prices')->where('plan_id', $planId)->max(DB::raw('CAST(no_of_agents AS UNSIGNED)'));
            $value = $count > 0 ? (string) $count : null;
            if ($row === null) {
                DB::table('plan_entitlements')->insert(['plan_id' => $planId, 'entitlement_id' => $agentsId, 'value' => $value, 'created_at' => now(), 'updated_at' => now()]);
            } else {
                DB::table('plan_entitlements')->where('id', $row->id)->update(['value' => $value, 'updated_at' => now()]);
            }
        }
    }

    /**
     * Live orders on a plan with a set agent count keep the count their last invoice charged for:
     * agents above the plan become an Agents extra, fewer than the plan a negative one, priced at
     * the plan's agent renewal price (else its share of the plan price) so renewals cost the same.
     * A license with unlimited agents (0000) gets an Agents override of unlimited. License codes
     * are never changed; orders whose license disagrees with the invoice are listed. Runs once,
     * while plan_prices.no_of_agents is still there.
     */
    public function seedOrderAgentExtras(): void
    {
        if (! Schema::hasColumn('plan_prices', 'no_of_agents')) {
            return;
        }
        $agents = EntitlementService::agents()->load('prices');
        $service = resolve(EntitlementService::class);
        $included = DB::table('plan_entitlements')->where('entitlement_id', $agents->id)->whereNotNull('value')->pluck('value', 'plan_id');

        Order::join('subscriptions', 'subscriptions.order_id', '=', 'orders.id')
            ->whereIn('subscriptions.plan_id', $included->keys())
            ->where('orders.order_status', '!=', 'Terminated')
            ->select('orders.*', 'subscriptions.plan_id as agents_plan_id')
            ->chunkById(200, function ($orders) use ($agents, $service, $included): void {
                foreach ($orders as $order) {
                    $planId = (int) $order->getAttribute('agents_plan_id');
                    $plan = Plan::find($planId);
                    $planIncluded = (int) $included[$planId];
                    $total = DB::table('order_invoice_relations as r')
                        ->join('invoice_items as i', 'i.invoice_id', '=', 'r.invoice_id')
                        ->where('r.order_id', $order->id)->where('i.product_id', $order->product)
                        ->whereNotNull('i.agents')->where('i.agents', '!=', '')
                        ->orderByDesc('r.invoice_id')->orderByDesc('i.id')->value('i.agents');
                    if ($plan === null || $total === null) {
                        continue;
                    }
                    $total = (int) $total;
                    $digits = substr((string) $order->serial_key, -4);
                    if ($digits !== str_pad((string) $total, 4, '0', STR_PAD_LEFT)) {
                        $this->command?->warn("Order {$order->id}: invoice {$total} agents, license {$digits}");
                    }

                    if ($digits === '0000') {
                        $owner = License::where('license_order_number', $order->number)->first() ?? Subscription::where('order_id', $order->id)->first();
                        $hasOverride = $owner !== null && Grant::where('code', EntitlementService::AGENTS)->where('source', Grant::ADMIN)
                            ->where($owner instanceof License ? 'license_id' : 'subscription_id', $owner->id)->exists();
                        if ($owner !== null && ! $hasOverride) {
                            $service->setOverride($owner, $agents, null, null); // null: unlimited
                        }
                    }

                    $diff = $total - $planIncluded;
                    if ($diff === 0 || $total === 0 || OrderEntitlement::where('order_id', $order->id)->where('entitlement_id', $agents->id)->exists()) {
                        continue;
                    }
                    $currency = (string) userCurrencyAndPrice($order->client, $plan)['currency'];
                    $planPrice = PlanPrice::where('plan_id', $plan->id)->where('currency', $currency)->first();
                    $share = $planPrice !== null && $planIncluded > 0 ? round((float) $planPrice->renew_price / $planIncluded, 4) : null;
                    $unitPrice = $agents->priceIn($currency, $plan, 'renewal') ?? $share;
                    if ($diff < 0 && $unitPrice !== null && $share !== null) {
                        $unitPrice = min($unitPrice, $share); // fewer agents never took off more than their share of the plan
                    }
                    if ($unitPrice === null) {
                        $this->command?->warn("Order {$order->id}: no agent price in {$currency}, its {$total} agents were not moved");

                        continue;
                    }
                    OrderEntitlement::create([
                        'order_id' => $order->id, 'entitlement_id' => $agents->id, 'quantity' => $diff,
                        'unit_price' => $unitPrice, 'currency' => $currency, 'period_id' => $plan->periodId(),
                    ]);
                }
            }, 'orders.id', 'id');
    }

    /** Cart lines with more agents than their plan includes keep the rest as an Agents extra. */
    public function seedCartAgentExtras(): void
    {
        if (! Schema::hasColumn('cart_items', 'agents')) {
            return;
        }
        $agentsId = (string) EntitlementService::agents()->id;
        $plans = [];

        foreach (DB::table('cart_items')->where('agents', '>', 0)->get(['id', 'plan_id', 'agents', 'extras']) as $line) {
            $plans[$line->plan_id] ??= EntitlementService::includedAgents(Plan::find((int) $line->plan_id));
            $extras = json_decode((string) $line->extras, true) ?: [];
            $included = $plans[$line->plan_id];
            if ($included === null || $line->agents <= $included || isset($extras[$agentsId])) {
                continue;
            }
            $extras[$agentsId] = $line->agents - $included;
            DB::table('cart_items')->where('id', $line->id)->update(['extras' => json_encode($extras)]);
        }
    }

    /** The agent columns the Agents feature replaces; read by the steps above first. */
    public function dropAgentColumns(): void
    {
        foreach (['products' => ['show_agent', 'can_modify_agent'], 'plan_prices' => ['no_of_agents'], 'cart_items' => ['agents']] as $table => $columns) {
            foreach ($columns as $column) {
                if (Schema::hasColumn($table, $column)) {
                    Schema::table($table, fn ($t) => $t->dropColumn($column));
                }
            }
        }
    }

    /**
     * Fixed Amount coupons (type 2) now carry the currency their amount is in. Ones made before
     * get the default currency — the one their amount was most likely written in.
     */
    public function seedFixedCouponCurrency(): void
    {
        DB::table('promotions')->where('type', 2)->whereNull('currency')
            ->update(['currency' => (string) DB::table('settings')->value('default_currency')]);
    }

    /**
     * Invoice lines used to carry their add-ons in the name ("Plan (+ Storage × 4, …)").
     * The add-ons are listed from the line's extras now, so keep just the product name.
     */
    public function stripExtrasFromInvoiceItemNames(): void
    {
        DB::table('invoice_items')->whereNotNull('extras')->where('product_name', 'like', '% (+ %')
            ->update(['product_name' => DB::raw("SUBSTRING_INDEX(product_name, ' (+ ', 1)")]);
    }

    /**
     * Plugins become features, so one rule decides access. Each plugin product gets the feature
     * that unlocks it; a product's bundled plugins become features its plans include; every
     * license without plan grants gets its plan's, keeping only the plugins it was linked to;
     * and a plugin link no plan explains (added by hand, or a license with no order here) becomes
     * a permanent admin grant. Every customer keeps exactly the plugins they had. Safe to run
     * again. Verify with `php artisan license:check-grants`.
     */
    public function seedPluginFeatures(): void
    {
        $this->seedPluginFeatureItems();
        $features = DB::table('products')->whereNotNull('unlocks_entitlement_id')->pluck('unlocks_entitlement_id', 'id');
        $codes = Entitlement::whereIn('id', $features)->pluck('code', 'id');

        // Bundled plugins → features every plan of the product includes.
        foreach (DB::table('product_plugin_group')->get(['product_id', 'plugin_id']) as $bundle) {
            if (isset($features[$bundle->plugin_id])) {
                DB::table('plan_entitlements')->insertOrIgnore(DB::table('plans')->where('product', $bundle->product_id)->pluck('id')
                    ->map(fn ($planId): array => ['plan_id' => $planId, 'entitlement_id' => $features[$bundle->plugin_id], 'value' => '1', 'created_at' => now(), 'updated_at' => now()])
                    ->all());
            }
        }

        // Licenses without plan grants get their plan's (one lookup per plan), minus plugins the
        // license was never linked to: nobody gains a plugin from the move. "Apply to existing
        // licenses" on the plan, or a plan change, gives them later, like linking used to.
        $service = resolve(EntitlementService::class);
        $planValues = [];
        DB::table('licenses as l')
            ->join('orders as o', 'o.number', '=', 'l.license_order_number')
            ->join('subscriptions as s', 's.order_id', '=', 'o.id')
            ->whereNotExists(fn ($q) => $q->from('grants')->whereColumn('grants.license_id', 'l.id')->where('grants.source', Grant::PLAN))
            ->select('l.id', 'l.product_id', 's.plan_id')
            ->chunkById(500, function ($rows) use ($service, &$planValues, $codes): void {
                $linked = $this->linkedCodes($rows->pluck('id')->all());
                $grants = [];
                foreach ($rows as $row) {
                    $key = $row->product_id.'|'.$row->plan_id;
                    $planValues[$key] ??= $service->forPlan((int) $row->product_id, (int) $row->plan_id);
                    $notLinked = array_diff($codes->all(), $linked[$row->id] ?? []);
                    foreach (array_diff_key($planValues[$key], array_flip($notLinked)) as $code => $value) {
                        $grants[] = $this->grantRow((int) $row->id, (string) $code, Grant::PLAN, match (true) {
                            $value === null => null,
                            is_bool($value) => $value ? '1' : '0',
                            default => (string) $value,
                        });
                    }
                }
                foreach (array_chunk($grants, 1000) as $part) {
                    DB::table('grants')->insertOrIgnore($part);
                }
            }, 'l.id', 'id');

        // Plugin links the plan doesn't cover → permanent admin grant.
        DB::table('license_plugins')->select('license_id')->distinct()->orderBy('license_id')
            ->chunk(500, function ($rows): void {
                $ids = $rows->pluck('license_id')->all();
                $planOn = DB::table('grants')->whereIn('license_id', $ids)->where('source', Grant::PLAN)->where('value', '1')
                    ->get(['license_id', 'code'])->groupBy('license_id')->map(fn ($g) => $g->pluck('code')->all());
                // Already granted (by an earlier run, whatever its subscription): not added twice.
                $owned = DB::table('grants')->whereIn('license_id', $ids)->where('source', Grant::ADMIN)
                    ->get(['license_id', 'code'])->groupBy('license_id')->map(fn ($g) => $g->pluck('code')->all());
                $grants = [];
                foreach ($this->linkedCodes($ids) as $licenseId => $linkedCodes) {
                    foreach (array_diff($linkedCodes, $planOn[$licenseId] ?? [], $owned[$licenseId] ?? []) as $code) {
                        $grants[] = $this->grantRow((int) $licenseId, $code, Grant::ADMIN, '1');
                    }
                }
                foreach (array_chunk($grants, 1000) as $part) {
                    DB::table('grants')->insertOrIgnore($part);
                }
            });
    }

    /**
     * Updates and support become date features that every plan includes, with the rule that
     * gives today's dates: they used to run for the plan's days when the product's license type
     * had "Generate Updates/Support Expiry Date" on, and never end otherwise. Products limit
     * downloads by updates unless that setting was off. Runs once: an admin's later choices
     * aren't overwritten.
     */
    public function seedDateFeatures(): void
    {
        $permissionOf = ['updates' => 'generateUpdatesxpiryDate', 'support' => 'generateSupportExpiryDate'];
        $items = [];
        foreach (['updates' => 'Updates', 'support' => 'Support'] as $code => $label) {
            $items[$code] = Entitlement::firstOrCreate(['code' => $code], [
                'label' => $label, 'type' => 'date', 'category' => 'Dates', 'default_value' => null,
                'is_active' => true, 'sort' => (int) Entitlement::max('sort') + 1,
            ]);
        }
        if (! $items['updates']->wasRecentlyCreated) {
            return;
        }

        Product::orderBy('id')->each(function (Product $product) use ($items, $permissionOf): void {
            $permissions = $this->oldPermissions($product);
            DB::table('products')->where('id', $product->id)
                ->update(['downloads_limited_by' => $permissions['generateUpdatesxpiryDate'] ? 'updates' : null]);

            foreach (Plan::where('product', $product->id)->get() as $plan) {
                $days = (int) $plan->days;
                $followsTerm = $permissions['generateLicenseExpiryDate'] && $days > 0
                    && ! $plan->periods()->where('name', 'One Time')->exists();
                foreach ($items as $code => $item) {
                    $rule = match (true) {
                        ! $permissions[$permissionOf[$code]] || $days <= 0 => ['ends' => 'never'],
                        $followsTerm => ['ends' => 'subscription'],
                        default => ['ends' => 'custom', 'every' => $days, 'unit' => 'day'],
                    };
                    DB::table('plan_entitlements')->insertOrIgnore([
                        'plan_id' => $plan->id, 'entitlement_id' => $item->id, 'value' => json_encode($rule),
                        'created_at' => now(), 'updated_at' => now(),
                    ]);
                }
            }
        });
    }

    /**
     * Every grant belongs to its license's order subscription (licenses from the old license
     * server without an order here keep none). Safe to run again.
     */
    public function seedGrantOwners(): void
    {
        DB::statement('
            UPDATE grants g
            JOIN licenses l ON l.id = g.license_id
            JOIN orders o ON o.number = l.license_order_number
            JOIN subscriptions s ON s.order_id = o.id
            SET g.subscription_id = s.id
            WHERE g.subscription_id IS NULL');
    }

    /**
     * Orders without a license keep their update/support dates as grants of the subscription
     * (no license to deliver to). Safe to run again.
     */
    public function seedSubscriptionDateGrants(): void
    {
        foreach (LegacyDates::COLUMNS as $code => [, $column]) {
            DB::statement("
                INSERT INTO grants (subscription_id, license_id, code, value, source, ends_at, created_at, updated_at)
                SELECT s.id, NULL, ?, NULL, ?, CASE WHEN s.{$column} IS NULL OR CAST(s.{$column} AS CHAR) LIKE '0000%' THEN NULL ELSE DATE(s.{$column}) END, NOW(), NOW()
                FROM subscriptions s
                JOIN orders o ON o.id = s.order_id
                WHERE NOT EXISTS (SELECT 1 FROM licenses l WHERE l.license_order_number = o.number)
                  AND NOT EXISTS (SELECT 1 FROM grants g WHERE g.subscription_id = s.id AND g.license_id IS NULL AND g.code = ? AND g.source = ?)", [$code, Grant::DATE, $code, Grant::DATE]);
        }
    }

    /**
     * License-type permissions go: "Can be Downloaded" and "Allow Downloads Before Updates
     * Expire" become the product's downloadable / keep_old_downloads; the expiry permissions are
     * already plan date rules (seedDateFeatures), and the license now ends with its plan period.
     * Plans whose licenses never expired but have a recurring period are listed, since their
     * licenses will now end each period. Runs after seedDateFeatures, which reads the same tables.
     */
    public function moveLicensePermissionsToProducts(): void
    {
        if (! Schema::hasTable('license_license_permissions')) {
            return;
        }

        Product::orderBy('id')->each(function (Product $product): void {
            $permissions = $this->oldPermissions($product);
            DB::table('products')->where('id', $product->id)->update([
                'downloadable' => $permissions['downloadPermission'],
                'keep_old_downloads' => $permissions['allowDownloadTillExpiry'],
            ]);

            if ($permissions['generateLicenseExpiryDate']) {
                return;
            }
            foreach (Plan::where('product', $product->id)->get() as $plan) {
                if ((int) $plan->days > 0 && ! $plan->periods()->where('name', 'One Time')->exists()) {
                    $this->command?->warn("Plan {$plan->id} ({$plan->name}) of {$product->name}: its licenses never expired; they now end with each {$plan->days}-day period.");
                }
            }
        });

        Schema::dropIfExists('license_license_permissions');
        Schema::dropIfExists('license_permissions');
    }

    /**
     * License types go: what they decided now lives on the product (Downloadable, the
     * downloads date, product_type "addon" for plugins). A product typed "plugin" that isn't
     * an add-on is listed, since only add-ons are kept from deploying now. Runs after
     * moveLicensePermissionsToProducts, which reads products.type.
     */
    public function dropLicenseTypes(): void
    {
        if (! Schema::hasColumn('products', 'type')) {
            return;
        }

        if (Schema::hasTable('license_types')) {
            // "No License" products (services) stop issuing licenses: their rights stay on the subscription.
            DB::table('products')->whereIn('type', DB::table('license_types')->whereRaw("LOWER(TRIM(name)) = 'no license'")->select('id'))
                ->update(['issues_license' => false]);

            $plugins = DB::table('products as p')->join('license_types as t', 't.id', '=', 'p.type')
                ->whereRaw("LOWER(TRIM(t.name)) = 'plugin'")->where(fn ($q) => $q->whereNull('p.product_type')->orWhere('p.product_type', '!=', 'addon'))
                ->get(['p.id', 'p.name']);
            foreach ($plugins as $product) {
                $this->command?->warn("Product {$product->id} ({$product->name}) had license type plugin but isn't an add-on: set its product type to add-on if it must not be deployed.");
            }
        }

        Schema::table('products', fn ($table) => $table->dropColumn('type'));
        Schema::dropIfExists('license_types');
    }

    /**
     * The product's old license-type permissions, read straight from the tables
     * moveLicensePermissionsToProducts() drops; all off once they are gone.
     *
     * @return array<string, bool>
     */
    private function oldPermissions(Product $product): array
    {
        $map = [
            'Generate Updates Expiry Date' => 'generateUpdatesxpiryDate',
            'Generate License Expiry Date' => 'generateLicenseExpiryDate',
            'Generate Support Expiry Date' => 'generateSupportExpiryDate',
            'Can be Downloaded' => 'downloadPermission',
            'Allow Downloads Before Updates Expire' => 'allowDownloadTillExpiry',
        ];
        $result = array_fill_keys(array_values($map), false);
        if (! Schema::hasTable('license_license_permissions')) {
            return $result;
        }

        $labels = DB::table('license_license_permissions as lp')
            ->join('license_permissions as p', 'p.id', '=', 'lp.license_permission_id')
            ->where('lp.license_type_id', $product->getRawOriginal('type'))
            ->pluck('p.permissions');
        foreach ($labels as $label) {
            if (isset($map[$label])) {
                $result[$map[$label]] = true;
            }
        }

        return $result;
    }

    /**
     * Each license's update and support dates become date grants (ends_at = the date, null =
     * never). Afterwards the License model keeps them in step with the old columns. Safe to run again.
     */
    public function seedLicenseDateGrants(): void
    {
        foreach (LegacyDates::COLUMNS as $code => [$column]) {
            // The unique key includes subscription_id, which seedGrantOwners fills in later: once it
            // has, INSERT IGNORE alone no longer sees the old row, so a re-run checks explicitly.
            DB::statement("
                INSERT IGNORE INTO grants (license_id, code, value, source, ends_at, created_at, updated_at)
                SELECT l.id, ?, NULL, ?, CASE WHEN l.{$column} IS NULL OR CAST(l.{$column} AS CHAR) LIKE '0000%' THEN NULL ELSE DATE(l.{$column}) END, NOW(), NOW()
                FROM licenses l
                WHERE NOT EXISTS (SELECT 1 FROM grants g WHERE g.license_id = l.id AND g.code = ? AND g.source = ?)", [$code, Grant::DATE, $code, Grant::DATE]);
        }
    }

    /**
     * The plugin feature codes each license is linked to (license_plugins).
     *
     * @param  array<int, mixed>  $licenseIds
     * @return array<array-key, array<int, mixed>>
     */
    private function linkedCodes(array $licenseIds): array
    {
        return DB::table('license_plugins as lp')
            ->join('products as p', 'p.id', '=', 'lp.product_id')
            ->join('entitlements as e', 'e.id', '=', 'p.unlocks_entitlement_id')
            ->whereIn('lp.license_id', $licenseIds)
            ->get(['lp.license_id', 'e.code'])
            ->groupBy('license_id')
            ->map(fn ($links): array => $links->pluck('code')->unique()->values()->all())
            ->all();
    }

    /** @return array<string, mixed> */
    private function grantRow(int $licenseId, string $code, string $source, ?string $value): array
    {
        return ['license_id' => $licenseId, 'code' => $code, 'value' => $value, 'source' => $source, 'ends_at' => null, 'created_at' => now(), 'updated_at' => now()];
    }

    /** One on/off feature per plugin product (see EntitlementService::pluginFeature). */
    private function seedPluginFeatureItems(): void
    {
        Product::where(fn ($q) => $q->whereIn('id', DB::table('license_plugins')->select('product_id'))
            ->orWhereIn('id', DB::table('product_plugin_group')->select('plugin_id'))
            ->orWhere('product_type', 'addon'))
            ->orderBy('id')
            ->each(fn (Product $plugin) => EntitlementService::pluginFeature($plugin));
    }
}
