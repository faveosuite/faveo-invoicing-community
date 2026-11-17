<?php

declare(strict_types=1);

namespace App\Http\Controllers\Product;

use App\Http\Controllers\Controller;
use App\License\Models\License;
use App\Model\Order\Order;
use App\Model\Payment\Plan;
use App\Model\Product\Entitlement;
use App\Model\Product\Product;
use App\Services\EntitlementService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

/**
 * Admin: the shared feature list, the features each plan includes with their extra prices, and
 * per-order overrides.
 */
class EntitlementController extends Controller
{
    public function __construct(private readonly EntitlementService $service) {}

    public function index(): JsonResponse
    {
        EntitlementService::agents();

        $items = Entitlement::orderBy('sort')->orderBy('id')->get()
            ->map(fn (Entitlement $item): array => [
                ...$item->only(['id', 'feature', 'key', 'label', 'type', 'options', 'default_value', 'is_active']),
                'in_use' => $this->inUse($item),
                'system' => EntitlementService::isAgents($item), // built in: agents, can't be removed
            ]);

        return successResponse('', ['entitlements' => $items]);
    }

    /**
     * Saves the full feature list. A feature left out is deleted, or archived when plans or
     * customers already use it. A used feature can't change feature, key or type.
     */
    public function sync(Request $request): JsonResponse
    {
        $slug = ['required', 'string', 'max:50', 'regex:/^[a-z][a-z0-9_]*$/'];

        $rows = $request->validate([
            'entitlements' => ['present', 'array'],
            'entitlements.*.id' => ['nullable', 'integer'],
            'entitlements.*.feature' => $slug,
            'entitlements.*.key' => $slug,
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
                $path = $row['feature'].'.'.$row['key'];
                if (isset($seen[$path])) {
                    throw ValidationException::withMessages(["entitlements.$i.key" => __('message.entitlement_duplicate', ['key' => $path])]);
                }

                $item = isset($row['id'])
                    ? $existing->get($row['id'])
                    : $existing->first(fn (Entitlement $e): bool => $e->feature === $row['feature'] && $e->key === $row['key']);
                if (isset($row['id']) && ! $item) {
                    throw ValidationException::withMessages(["entitlements.$i.id" => __('message.record_not_found')]);
                }

                if ($item && $this->inUse($item) && ($item->feature !== $row['feature'] || $item->key !== $row['key'] || $item->type !== $row['type'])) {
                    throw ValidationException::withMessages(["entitlements.$i.type" => __('message.entitlement_locked', ['key' => $item->feature.'.'.$item->key])]);
                }

                if ($path === EntitlementService::AGENTS || ($item && EntitlementService::isAgents($item))) {
                    $row = [...$row, 'feature' => 'agents', 'key' => 'max', 'type' => 'number', 'is_active' => true]; // built in
                }

                $item ??= new Entitlement;
                $item->fill([
                    'feature' => $row['feature'],
                    'key' => $row['key'],
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
                $item->default_value = $default;
                $item->save();

                $seen[$item->feature.'.'.$item->key] = $item->id;
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
     *  - included: the plan price covers it (value = how much); a price lets customers buy more
     *  - optional: not included, customers can buy it (price required)
     * Per-agent products also list Agents: included count (or unlimited) and the per-agent price.
     */
    public function planIndex(int $planId): JsonResponse
    {
        $plan = Plan::findOrFail($planId);
        $included = $plan->entitlements()->pluck('plan_entitlements.value', 'entitlements.id');
        $showAgents = (bool) Product::whereKey($plan->product)->value('show_agent');
        if ($showAgents) {
            EntitlementService::agents();
        }

        $items = Entitlement::with(['prices' => fn ($q) => $q->where('plan_id', $plan->id)])
            ->where(fn ($q) => $q->where('is_active', true)->orWhereIn('id', $included->keys()))
            ->orderBy('sort')->orderBy('id')->get()
            ->filter(fn (Entitlement $item): bool => $showAgents || ! EntitlementService::isAgents($item))
            ->map(function (Entitlement $item) use ($plan, $included): array {
                $agents = EntitlementService::isAgents($item);
                $mode = match (true) {
                    $agents, $included->has($item->id) => 'included',
                    $item->prices->isNotEmpty() => 'optional',
                    default => 'none',
                };

                return [
                    ...$item->only(['id', 'feature', 'key', 'label', 'type', 'options', 'default_value', 'is_active']),
                    'agents' => $agents,
                    'mode' => $mode,
                    'value' => $agents ? $this->includedAgents($plan) : ($included->get($item->id)),
                    'unlimited' => $agents && EntitlementService::planAgents($plan) === EntitlementService::UNLIMITED,
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
            'entitlements.*.unlimited' => ['boolean'],
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
                $key = $item->feature.'.'.$item->key;
                $fail = fn (string $message) => throw ValidationException::withMessages(["entitlements.$i.value" => $message]);
                $prices = $this->pricesFrom((array) ($row['prices'] ?? []));
                $sellable = in_array($item->type, ['boolean', 'number'], strict: true);
                $mode = EntitlementService::isAgents($item) ? 'included' : $row['mode'];

                if ($mode === 'optional' && (! $sellable || $prices === [])) {
                    $fail(__('message.entitlement_optional_needs_price', ['key' => $item->label]));
                }

                $plan->entitlements()->detach($item->id);
                if (EntitlementService::isAgents($item)) {
                    $this->saveAgents($plan, $row, $prices, $currencies, $fail);
                } elseif ($mode === 'included') {
                    $value = $item->normalize($row['value'] ?? null);
                    if ($value === false) {
                        $fail(__('message.entitlement_invalid_value', ['key' => $key]));
                    }
                    $plan->entitlements()->attach($item->id, ['value' => $value]);
                }

                // Prices: none → not sold; included on/off → nothing more to buy.
                $keep = $sellable && ($mode === 'optional' || ($mode === 'included' && $item->type === 'number'));
                $item->prices()->where('plan_id', $plan->id)->delete();
                foreach ($keep ? $prices : [] as $currency => $price) {
                    $item->prices()->create(['plan_id' => $plan->id, 'currency' => $currency, ...$price]);
                }
            }
        });

        return successResponse(__('message.updated-successfully'));
    }

    /**
     * Agents: unlimited, N included (customers start at N), or 0 included — every agent paid,
     * customers start at one agent, so every currency needs an agent price.
     *
     * @param  array<string, mixed>  $row
     * @param  array<string, array<string, float|null>>  $prices
     * @param  array<int, mixed>  $currencies
     */
    private function saveAgents(Plan $plan, array $row, array $prices, array $currencies, callable $fail): void
    {
        if ($row['unlimited'] ?? false) {
            $plan->planPrice()->update(['no_of_agents' => 0]);

            return;
        }

        $count = filter_var($row['value'] ?? null, FILTER_VALIDATE_INT, ['options' => ['min_range' => 0, 'max_range' => 9999]]);
        if ($count === false) {
            $fail(__('message.entitlement_agents_count_required'));
        }

        if ($count === 0) {
            if (array_diff($currencies, array_keys($prices)) !== []) {
                $fail(__('message.entitlement_agents_price_required'));
            }
            $plan->entitlements()->attach(EntitlementService::agents()->id, ['value' => '0']);
        }
        $plan->planPrice()->update(['no_of_agents' => max(1, (int) $count)]);
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

    /** One order's entitlements: plan value, override (if any) and the final value per item. */
    public function orderIndex(int $orderId): JsonResponse
    {
        $license = $this->orderLicense($orderId);

        $items = array_map(fn (array $row): array => [
            ...$row['item']->only(['id', 'feature', 'key', 'label', 'type', 'options']),
            'plan_value' => $row['plan'],
            'extra' => $row['extra'],
            'override' => $row['override'],
            'until' => $row['until'],
            'value' => $row['value'],
            'from_code' => $row['locked'],
            'locked' => $row['locked'] || $row['item']->feature.'.'.$row['item']->key === EntitlementService::AGENTS,
        ], $this->service->rows($license));

        // Features the plan doesn't include can be given by an override too.
        $shown = array_column($items, 'id');
        foreach (Entitlement::where('is_active', true)->whereNotIn('id', $shown)->orderBy('sort')->orderBy('id')->get() as $item) {
            if (EntitlementService::isAgents($item)) {
                continue;
            }
            $items[] = [
                ...$item->only(['id', 'feature', 'key', 'label', 'type', 'options']),
                'plan_value' => null, 'extra' => 0, 'override' => null, 'until' => null, 'value' => null,
                'from_code' => false, 'locked' => false,
            ];
        }

        return successResponse('', ['entitlements' => $items]);
    }

    /** Overrides one item for this order only, optionally until a date. */
    public function orderOverride(Request $request, int $orderId, int $entitlementId): JsonResponse
    {
        $license = $this->orderLicense($orderId);
        $item = Entitlement::findOrFail($entitlementId);
        $data = $request->validate([
            'value' => ['required'],
            'until' => ['nullable', 'date', 'after_or_equal:today'],
        ]);

        if ($item->feature.'.'.$item->key === EntitlementService::AGENTS) {
            throw ValidationException::withMessages(['value' => __('message.entitlement_agents_locked')]);
        }

        $value = $item->normalize($data['value']);
        if (! is_string($value)) {
            throw ValidationException::withMessages(['value' => __('message.entitlement_invalid_value', ['key' => $item->feature.'.'.$item->key])]);
        }

        $until = isset($data['until']) ? date('Y-m-d', (int) strtotime((string) $data['until'])) : null;
        $this->service->setOverride($license, $item, $value, $until);

        return successResponse(__('message.updated-successfully'));
    }

    /** Removes an order's override, so the plan value applies again. */
    public function orderReset(int $orderId, int $entitlementId): JsonResponse
    {
        $license = $this->orderLicense($orderId);
        $this->service->removeOverride($license, Entitlement::findOrFail($entitlementId));

        return successResponse(__('message.entitlement_reset_done'));
    }

    private function orderLicense(int $orderId): License
    {
        return License::where('license_order_number', Order::findOrFail($orderId)->number)->firstOrFail();
    }

    /** Agents the plan price includes, as the plan page shows it: '' = unlimited, '0' = every agent paid. */
    private function includedAgents(Plan $plan): string
    {
        if (EntitlementService::paysForEveryAgent($plan)) {
            return '0';
        }
        $agents = EntitlementService::planAgents($plan);

        return $agents === EntitlementService::UNLIMITED ? '' : (string) $agents;
    }

    private function inUse(Entitlement $item): bool
    {
        return DB::table('plan_entitlements')->where('entitlement_id', $item->id)->exists()
            || DB::table('order_entitlements')->where('entitlement_id', $item->id)->exists()
            || License::whereNotNull("entitlement_base->{$item->feature}->{$item->key}")->exists();
    }
}
