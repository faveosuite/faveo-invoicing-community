<?php

declare(strict_types=1);

namespace App\Http\Controllers\Product;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Tenancy\CloudExtraActivities;
use App\License\Models\License;
use App\Model\Order\Order;
use App\Model\Payment\Plan;
use App\Model\Product\Entitlement;
use App\Model\Product\Grant;
use App\Model\Product\Product;
use App\Model\Product\Subscription;
use App\Services\AgentCountService;
use App\Services\DateRules;
use App\Services\EntitlementSalesService;
use App\Services\EntitlementService;
use App\Services\LegacyDates;
use App\Services\SubscriptionRenewalService;
use Closure;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Logger;

/**
 * Admin: the shared feature list, the features each plan includes with their extra prices, and
 * per-order overrides.
 */
class EntitlementController extends Controller
{
    public function __construct(private readonly EntitlementService $service)
    {
    }

    /** The feature list: one page of it for the admin table (with `limit`), or all of it for saving. */
    public function index(Request $request): JsonResponse
    {
        EntitlementService::agents();

        $row = fn (Entitlement $item): array => [
            ...$item->only(['id', 'code', 'category', 'label', 'type', 'options', 'default_value', 'is_active']),
            'in_use' => $this->inUse($item),
            'system' => EntitlementService::isAgents($item), // built in: agents, can't be removed
        ];

        if (! $request->has('limit')) {
            return successResponse('', ['entitlements' => Entitlement::orderBy('sort')->orderBy('id')->get()->map($row)]);
        }

        $search = trim((string) $request->input('search-query', ''));
        $sortField = $request->input('sort-field');
        $page = Entitlement::query()
            ->when($search !== '', fn ($q) => $q->where(fn ($q) => $q
                ->where('label', 'like', likePattern($search))
                ->orWhere('code', 'like', likePattern($search))
                ->orWhere('category', 'like', likePattern($search))))
            ->when(in_array($sortField, ['label', 'category', 'type'], true),
                fn ($q) => $q->orderBy($sortField, $request->input('sort-order') === 'asc' ? 'asc' : 'desc'),
                fn ($q) => $q->orderBy('sort'))
            ->orderBy('id')
            ->paginate(min(100, max(1, (int) $request->input('limit', 10))));
        $page->getCollection()->transform($row);

        return successResponse('', $page);
    }

    /**
     * Saves the full feature list. A feature left out is deleted, or archived when plans or
     * customers already use it. A used feature can't change its code or type (products check the code).
     */
    public function sync(Request $request): JsonResponse
    {
        $slug = ['required', 'string', 'max:50', 'regex:/^[a-z][a-z0-9_]*$/'];

        $rows = $request->validate([
            'entitlements' => ['present', 'array'],
            'entitlements.*.id' => ['nullable', 'integer'],
            'entitlements.*.code' => [...$slug, Rule::notIn(['license'])], // expires.license is the subscription end
            'entitlements.*.category' => ['nullable', 'string', 'max:50'],
            'entitlements.*.label' => ['required', 'string', 'max:100'],
            'entitlements.*.type' => ['required', Rule::in(Entitlement::TYPES)],
            'entitlements.*.options' => ['nullable', 'array', 'required_if:entitlements.*.type,select'],
            'entitlements.*.options.*' => ['string', 'max:100'],
            'entitlements.*.default_value' => ['nullable'],
            'entitlements.*.is_active' => ['boolean'],
        ])['entitlements'];

        $existing = Entitlement::all()->keyBy('id');
        $seen = [];

        DB::transaction(function () use ($rows, $existing, &$seen): void {
            foreach (array_values($rows) as $i => $row) {
                $path = $row['code'];
                if (isset($seen[$path])) {
                    throw ValidationException::withMessages(["entitlements.$i.code" => __('message.entitlement_duplicate', ['key' => $path])]);
                }

                $item = isset($row['id'])
                    ? $existing->get($row['id'])
                    : $existing->first(fn (Entitlement $e): bool => $e->code === $row['code']);
                if (isset($row['id']) && ! $item) {
                    throw ValidationException::withMessages(["entitlements.$i.id" => __('message.record_not_found')]);
                }

                if ($item && $this->inUse($item) && ($item->code !== $row['code'] || $item->type !== $row['type'])) {
                    throw ValidationException::withMessages(["entitlements.$i.type" => __('message.entitlement_locked', ['key' => $item->code])]);
                }

                if ($path === EntitlementService::AGENTS || ($item && EntitlementService::isAgents($item))) {
                    $row = [...$row, 'code' => EntitlementService::AGENTS, 'type' => 'number', 'is_active' => true]; // built in
                }

                $item ??= new Entitlement;
                $item->fill([
                    'code' => $row['code'],
                    'category' => filled($row['category'] ?? null) ? trim((string) $row['category']) : null,
                    'label' => $row['label'],
                    'type' => $row['type'],
                    'options' => $row['type'] === 'select' ? array_values(array_unique($row['options'] ?? [])) : null,
                    'is_active' => $row['is_active'] ?? true,
                    'sort' => $i,
                ]);

                $default = $item->normalize($row['default_value'] ?? null);
                if ($default === false) {
                    throw ValidationException::withMessages(["entitlements.$i.default_value" => __('message.entitlement_invalid_value', ['key' => $path])]);
                }
                if ($default === null && in_array($item->type, ['select', 'text'], strict: true)) { // a number left empty is unlimited
                    throw ValidationException::withMessages(["entitlements.$i.default_value" => __('message.entitlement_default_required')]);
                }
                $item->default_value = $default;
                $item->save();

                $seen[$item->code] = $item->id;
            }

            foreach ($existing->except(array_values($seen))->reject(fn (Entitlement $e): bool => EntitlementService::isAgents($e)) as $removed) {
                $this->inUse($removed) ? $removed->update(['is_active' => false]) : $removed->delete();
            }
        });

        return successResponse(__('message.updated-successfully'));
    }

    /** Deletes a feature, or archives it when plans or customers already use it. */
    public function destroy(int $entitlementId): JsonResponse
    {
        $item = Entitlement::findOrFail($entitlementId);

        if (EntitlementService::isAgents($item)) {
            return errorResponse(__('message.entitlement_agents_builtin'), 422);
        }

        if ($this->inUse($item)) {
            $item->update(['is_active' => false]);

            return successResponse(__('message.entitlement_archived_done'));
        }

        $item->delete();

        return successResponse(__('message.deleted-successfully'));
    }

    /**
     * Every active feature with how this plan offers it:
     *  - none:     not available on this plan
     *  - included: the plan price covers it (value = how much; a number left empty is unlimited);
     *              a price lets customers buy more
     *  - optional: not included, customers can buy it (price required)
     */
    public function planIndex(int $planId): JsonResponse
    {
        $plan = Plan::findOrFail($planId);
        $included = $plan->entitlements()->pluck('plan_entitlements.value', 'entitlements.id');
        EntitlementService::agents();

        $items = Entitlement::with(['prices' => fn ($q) => $q->where('plan_id', $plan->id)])
            ->where(fn ($q) => $q->where('is_active', true)->orWhereIn('id', $included->keys()))
            ->orderBy('sort')->orderBy('id')->get()
            ->map(function (Entitlement $item) use ($included): array {
                $mode = match (true) {
                    $included->has($item->id) => 'included',
                    $item->prices->isNotEmpty() => 'optional',
                    default => 'none',
                };

                return [
                    ...$item->only(['id', 'code', 'category', 'label', 'type', 'options', 'default_value', 'is_active']),
                    'mode' => $mode,
                    'value' => $item->type === 'date' ? DateRules::decode($included->get($item->id)) : $included->get($item->id), // a date: how the plan ends it
                    'sellable' => in_array($item->type, ['boolean', 'number'], strict: true),
                    'prices' => $item->prices->mapWithKeys(fn ($p): array => [$p->currency => [
                        'unit_price' => (float) $p->unit_price,
                        'renew_price' => $p->renew_price === null ? null : (float) $p->renew_price,
                        'offer_percent' => $p->offer_percent === null ? null : (float) $p->offer_percent,
                    ]]),
                ];
            })->values();

        return successResponse('', [
            'entitlements' => $items,
            'currencies' => $plan->planPrice()->pluck('currency')->unique()->values(),
            'period' => $plan->periods()->value('name') ?? __('message.entitlement_billing_period'),
            'live_licenses' => $this->service->liveLicenses($plan)->count(),
            // The product's other plans, to copy features from.
            'other_plans' => Plan::where('product', $plan->product)->whereKeyNot($plan->id)->orderBy('name')->get(['id', 'name']),
        ]);
    }

    /** Saves how the plan offers each feature (see planIndex), its value and its prices per currency. */
    public function planSave(Request $request, int $planId): JsonResponse
    {
        $plan = Plan::findOrFail($planId);
        $money = ['nullable', 'numeric', 'min:0', 'max:9999999999'];
        $rows = $request->validate([
            'entitlements' => ['present', 'array'],
            'entitlements.*.id' => ['required', 'integer', 'distinct', Rule::exists('entitlements', 'id')],
            'entitlements.*.mode' => ['required', Rule::in(['none', 'included', 'optional'])],
            'entitlements.*.value' => ['nullable'],
            'entitlements.*.prices' => ['sometimes', 'array'],
            'entitlements.*.prices.*.unit_price' => $money,
            'entitlements.*.prices.*.renew_price' => $money,
            'entitlements.*.prices.*.offer_percent' => ['nullable', 'numeric', 'min:0', 'max:100'],
        ])['entitlements'];

        $items = Entitlement::whereIn('id', array_column($rows, 'id'))->get()->keyBy('id');
        $currencies = $plan->planPrice()->pluck('currency')->unique()->all();

        DB::transaction(function () use ($rows, $items, $plan, $currencies): void {
            foreach ($rows as $i => $row) {
                $item = $items->get($row['id']);
                if (! $item) {
                    continue;
                }
                $key = $item->code;
                $fail = fn (string $message) => throw ValidationException::withMessages(["entitlements.$i.value" => $message]);
                $prices = $this->pricesFrom((array) ($row['prices'] ?? []));
                $sellable = in_array($item->type, ['boolean', 'number'], strict: true);
                $agents = EntitlementService::isAgents($item);
                // Agents can't be optional: a plan sold by agents needs a count on the license.
                $mode = $agents && $row['mode'] === 'optional' ? 'included' : $row['mode'];

                if ($mode === 'optional' && (! $sellable || $prices === [])) {
                    $fail(__('message.entitlement_optional_needs_price', ['key' => $item->label]));
                }

                $plan->entitlements()->detach($item->id);
                $value = null;
                if ($mode === 'included') {
                    $value = $item->normalize($row['value'] ?? null);
                    // The license code carries agents in four digits.
                    if ($value === false || ($agents && (int) $value > 9999)) {
                        $fail(__('message.entitlement_invalid_value', ['key' => $key]));
                    }
                    // 0 included: every agent is paid, so every currency needs an agent price (else 0000 = unlimited).
                    if ($agents && $value === '0' && array_diff($currencies, array_keys($prices)) !== []) {
                        $fail(__('message.entitlement_agents_price_required'));
                    }
                    $plan->entitlements()->attach($item->id, ['value' => $value]);
                }

                // Prices: none → not sold; included on/off or unlimited → nothing more to buy.
                $keep = $sellable && ($mode === 'optional' || ($mode === 'included' && $item->type === 'number' && $value !== null));
                $item->prices()->where('plan_id', $plan->id)->delete();
                foreach ($keep ? $prices : [] as $currency => $price) {
                    $item->prices()->create(['plan_id' => $plan->id, 'currency' => $currency, ...$price]);
                }
            }
        });

        return successResponse(__('message.updated-successfully'));
    }

    /**
     * The filled-in prices per currency, ready to store.
     *
     * @param  array<mixed>  $input
     * @return array<string, array<string, float|null>>
     */
    private function pricesFrom(array $input): array
    {
        $prices = [];
        foreach ($input as $currency => $price) {
            if (! is_array($price) || ! isset($price['unit_price']) || $price['unit_price'] === '') {
                continue;
            }
            $prices[(string) $currency] = [
                'unit_price' => (float) $price['unit_price'],
                'renew_price' => isset($price['renew_price']) && $price['renew_price'] !== '' ? (float) $price['renew_price'] : null,
                'offer_percent' => isset($price['offer_percent']) && $price['offer_percent'] !== '' ? (float) $price['offer_percent'] : null,
            ];
        }

        return $prices;
    }

    /** Pushes the plan's current values to every live license already on it. */
    public function planApply(int $planId): JsonResponse
    {
        $count = $this->service->applyPlanToLicenses(Plan::findOrFail($planId));

        return successResponse(__('message.entitlement_applied', ['count' => $count]));
    }

    /**
     * One order's rights, for its Features & dates section — with or without a license:
     *  - entitlements: what the order has (plan, bought extras, overrides), with where it comes from
     *  - available:    every other active feature, which an override can give
     *  - dates:        when the license ends (the subscription) and each date feature ends
     */
    public function orderIndex(int $orderId): JsonResponse
    {
        [$owner, $subscription] = $this->orderOwner($orderId);

        $items = array_map(fn (array $row): array => [
            ...$row['item']->only(['id', 'code', 'category', 'label', 'type', 'options']),
            'plan_value' => $row['plan'],
            'in_plan' => $row['in_plan'],
            'extra' => $row['extra'],
            'overridden' => $row['overridden'], // an override of unlimited has a null value
            'override' => $row['override'],
            'until' => $row['until'],
            'value' => $row['value'],
            'from_code' => $row['locked'],
        ], $this->service->rows($owner));

        $available = Entitlement::where('is_active', true)->whereNotIn('id', array_column($items, 'id'))
            ->where('type', '!=', 'date') // dates come from the plan
            ->orderBy('sort')->orderBy('id')->get(['id', 'code', 'category', 'label', 'type', 'options']);

        $current = $this->service->currentDates($owner);
        $licenseEnd = $subscription !== null ? $subscription->ends_at : ($owner instanceof License ? $owner->license_expire_date : null);
        $dates = [['code' => 'license', 'label' => __('message.order_subscription'), 'ends_at' => EntitlementService::day($licenseEnd)]];
        foreach (Entitlement::where('type', 'date')->where('is_active', true)->orderBy('sort')->orderBy('id')->get(['code', 'label']) as $item) {
            $column = LegacyDates::COLUMNS[$item->code][1] ?? null;
            if (array_key_exists($item->code, $current) || $column !== null) {
                $dates[] = ['code' => $item->code, 'label' => $item->label, 'ends_at' => array_key_exists($item->code, $current) ? $current[$item->code] : EntitlementService::day($subscription?->{$column})];
            }
        }

        return successResponse('', [
            'entitlements' => $items,
            'available' => $available,
            'dates' => $dates,
            'has_license' => $owner instanceof License,
        ]);
    }

    /** Sets when the license (code "license") or one date feature ends for this order; empty = never. */
    public function orderDate(Request $request, int $orderId, string $code): JsonResponse
    {
        [$owner, $subscription] = $this->orderOwner($orderId);
        $date = $request->validate(['date' => ['nullable', 'date_format:Y-m-d']])['date'] ?? null;

        if ($code !== 'license' && ! Entitlement::where('code', $code)->where('type', 'date')->exists()) {
            throw ValidationException::withMessages(['date' => __('message.entitlement_invalid_value', ['key' => $code])]);
        }

        // The license end and the old update/support dates live in columns too (old products,
        // reports): set through the renewal service, which copies them to the license's grants.
        $column = $code === 'license' ? 'ends_at' : (LegacyDates::COLUMNS[$code][1] ?? null);
        if ($column !== null && $subscription !== null) {
            resolve(SubscriptionRenewalService::class)->setDate($subscription, $column, $date);
        } elseif ($code === 'license' && $owner instanceof License) {
            $owner->update(['license_expire_date' => $date]); // an old license without a subscription
        }
        // The grant itself (unchanged when the license already copied it from its column).
        if ($code !== 'license') {
            $this->service->setDate($owner, $code, $date);
        }

        return successResponse(__('message.updated-successfully'));
    }

    /** Overrides one item for this order only, optionally until a date. A number can be set to unlimited (stored as null). */
    public function orderOverride(Request $request, int $orderId, int $entitlementId): JsonResponse
    {
        [$owner, , $order] = $this->orderOwner($orderId);
        $item = Entitlement::findOrFail($entitlementId);
        $data = $request->validate([
            'unlimited' => ['boolean'],
            'value' => [Rule::requiredIf(! $request->boolean('unlimited'))],
            'until' => ['nullable', 'date', 'after_or_equal:today'],
        ]);
        $unlimited = $request->boolean('unlimited');
        $invalid = fn (): ValidationException => ValidationException::withMessages(['value' => __('message.entitlement_invalid_value', ['key' => $item->code])]);
        if ($item->type === 'date' || ($unlimited && $item->type !== 'number')) {
            throw $invalid();
        }

        if (EntitlementService::isAgents($item)) {
            // The count goes into the license code's last four digits, which can't change back on a date.
            if (isset($data['until'])) {
                throw ValidationException::withMessages(['until' => __('message.entitlement_agents_no_until')]);
            }
            // 0 would read as 0000 (unlimited): unlimited is asked for on its own.
            $count = $unlimited ? null : filter_var($data['value'], FILTER_VALIDATE_INT, ['options' => ['min_range' => 1, 'max_range' => 9999]]);
            if ($count === false) {
                throw $invalid();
            }
            $value = $count === null ? null : (string) $count;

            return $this->applyAgentsOverride($order, $owner, fn () => $this->service->setOverride($owner, $item, $value, null), __('message.updated-successfully'));
        }

        $value = null; // unlimited
        if (! $unlimited) {
            $value = $item->normalize($data['value']);
            if (! is_string($value)) {
                throw $invalid();
            }
        }

        $until = isset($data['until']) ? date('Y-m-d', (int) strtotime((string) $data['until'])) : null;
        $this->service->setOverride($owner, $item, $value, $until);

        return successResponse(__('message.updated-successfully'));
    }

    /** Removes an order's override, so the plan value applies again. */
    public function orderReset(int $orderId, int $entitlementId): JsonResponse
    {
        [$owner, , $order] = $this->orderOwner($orderId);
        $item = Entitlement::findOrFail($entitlementId);
        if (EntitlementService::isAgents($item)) {
            return $this->applyAgentsOverride($order, $owner, fn () => $this->service->removeOverride($owner, $item), __('message.entitlement_reset_done'));
        }
        $this->service->removeOverride($owner, $item);

        return successResponse(__('message.entitlement_reset_done'));
    }

    /**
     * Applies an Agents override change, then writes the order's new count into its license code
     * (and cloud tenant). Undone when a cloud tenant has more agents in use than the new count, when
     * the count would be 0 on a plan with a count (0000 reads as unlimited), or when the write fails.
     */
    private function applyAgentsOverride(Order $order, License|Subscription $owner, Closure $change, string $done): JsonResponse
    {
        // Paying an open Agents purchase later would add agents the override hides.
        $pending = resolve(EntitlementSalesService::class)->openInvoiceNumber($order, EntitlementService::agents());
        if ($pending !== null) {
            return errorResponse(__('message.order_change_invoice_pending', ['number' => $pending]));
        }

        $agentCount = resolve(AgentCountService::class);
        $had = $agentCount->hasOverride($order);
        $before = $agentCount->override($order); // null with $had: unlimited
        $current = (int) substr((string) $order->serial_key, -4); // 0 = unlimited
        $change();
        $total = $agentCount->total($order);

        $failure = match (true) {
            $total === 0 && ! $agentCount->unlimited($order) && EntitlementService::includedAgents(Plan::find((int) Subscription::where('order_id', $order->id)->value('plan_id'))) !== null => __('message.entitlement_agents_none'),
            $total > 0 && ($current === 0 || $total < $current) => $this->reduceRefusal($order, $total),
            default => null,
        };
        if ($failure === null && $order->serial_key) { // no license code: nothing to write
            try {
                $agentCount->write($order, $total);
            } catch (Exception $exception) {
                Logger::exception($exception);
                $failure = __('message.change_agents_failed');
            }
        }

        if ($failure !== null) {
            $agents = EntitlementService::agents();
            $had ? $this->service->setOverride($owner, $agents, $before === null ? null : (string) $before, null) : $this->service->removeOverride($owner, $agents);

            return errorResponse($failure);
        }

        return successResponse($done);
    }

    /**
     * Why a cloud order can't go down to $agents: its tenant can't be found to check, or has more
     * agents in use (cutting below that would strand them). Null when it can, or isn't cloud.
     */
    private function reduceRefusal(Order $order, int $agents): ?string
    {
        if (! in_array((int) $order->product, array_map(intval(...), cloudPopupProducts()), strict: true)) {
            return null;
        }
        $installationPath = $order->installedDomains()->first();
        if (! $installationPath) {
            return (string) __('message.installation_path_not_found');
        }

        return resolve(CloudExtraActivities::class)->checktheAgent($agents, $installationPath) ? (string) __('message.agent_reduce') : null;
    }

    /**
     * Whose grants an order's page edits: its license, or its subscription when the product issues
     * none (old orders may have a license without a subscription). 404 when the order has neither.
     *
     * @return array{0: License|Subscription, 1: Subscription|null, 2: Order}
     */
    private function orderOwner(int $orderId): array
    {
        $order = Order::findOrFail($orderId);
        $subscription = Subscription::where('order_id', $order->id)->first();
        $owner = License::where('license_order_number', $order->number)->first() ?? $subscription;
        if ($owner === null) {
            abort(404);
        }

        return [$owner, $subscription, $order];
    }

    private function inUse(Entitlement $item): bool
    {
        return DB::table('plan_entitlements')->where('entitlement_id', $item->id)->exists()
            || DB::table('order_entitlements')->where('entitlement_id', $item->id)->exists()
            || Product::where('unlocks_entitlement_id', $item->id)->exists()
            || Grant::where('code', $item->code)->exists();
    }
}
