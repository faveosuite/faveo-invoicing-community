<?php

namespace App\Http\Controllers\Product;

use App\Http\Requests\PlanRequest;
use App\Model\Payment\Currency;
use App\Model\Payment\Period;
use App\Model\Payment\Plan;
use App\Model\Payment\PlanPrice;
use App\Model\Product\Product;
use App\Model\Product\Subscription;
use DB;
use Exception;
use Illuminate\Contracts\Database\Query\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Throwable;

class PlanController extends ExtendedPlanController
{
    protected Currency $currency;

    protected PlanPrice $price;

    protected Period $period;

    protected Product $product;

    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('admin');
        $plan = new Plan;
        $this->plan = $plan; // @phpstan-ignore property.notFound

        $subscription = new Subscription;
        $this->subscription = $subscription; // @phpstan-ignore property.notFound

        $currency = new Currency;
        $this->currency = $currency;

        $price = new PlanPrice;
        $this->price = $price;

        $period = new Period;
        $this->period = $period;

        $product = new Product;
        $this->product = $product;
    }

    public function getAllPlans(Request $request): JsonResponse
    {
        $searchQuery = $request->input('search-query', '');
        $sortOrder = $request->input('sort-order', 'asc');
        $sortField = $request->input('sort-field', 'created_at');
        $limit = $request->input('limit', 10);

        $plans = Plan::with([
            'planPrice:id,plan_id,currency',
            'productRelation:id,name',
        ])
            ->when($searchQuery, function ($query, $searchQuery): void {
                $query->where(function (Builder $q) use ($searchQuery): void {
                    $daysRange = $this->parsePeriodToDaysRange($searchQuery);

                    $q->where('name', 'like', sprintf('%%%s%%', $searchQuery))
                        ->when($daysRange, fn ($q2) => $q2->orWhereBetween('days', $daysRange)) // @phpstan-ignore argument.type
                        ->orWhereHas('productRelation', fn (Builder $q3) => $q3->where('name', 'like', sprintf('%%%s%%', $searchQuery))
                        )
                        ->orWhereHas('planPrice', fn (Builder $q4) => $q4->where('currency', 'like', sprintf('%%%s%%', $searchQuery))
                        );
                });
            })
            ->orderBy($sortField, $sortOrder)
            ->paginate($limit);

        $plans->getCollection()->transform(fn ($plan): array => [
            'id' => $plan->id,
            'name' => $plan->name,
            'product' => $plan->productRelation?->name,
            'product_id' => $plan->product,
            'period' => formatDays((int) $plan->days),
            'currencies' => $plan->planPrice->pluck('currency')->toArray(),
            'created_at' => $plan->created_at,
        ]);

        return successResponse('', $plans);
    }

    /**
     * Convert human-readable period into a days range.
     *
     * @return array<mixed>|null [minDays, maxDays] or null if invalid
     */
    protected function parsePeriodToDaysRange(string $period): ?array
    {
        $period = trim(strtolower($period));

        if (preg_match('/(\d+)\s*(day|days|month|months|year|years)/', $period, $matches)) {
            $value = (int) $matches[1];
            $unit = $matches[2];

            return match ($unit) {
                'day', 'days' => [$value, $value],            // exact day
                'month', 'months' => [$value * 30, $value * 30 + 29], // 1 Month = 30–59, 2 Months = 60–89, etc.
                default => [$value * 365, $value * 365 + 364], // year/years: 1 Year = 365–729, etc.
            };
        }

        return null;
    }

    public function planCreate(PlanRequest $request): JsonResponse
    {
        try {
            // prevent creating duplicate plans for certain products
            if (in_array($request->product, cloudPopupProducts()) &&
                Plan::whereProduct($request->product)->where('days', $request->days)->exists()
            ) {
                return errorResponse('Plan already exists');
            }

            // Create the plan
            $plan = Plan::create(
                $request->only((new Plan)->getFillable())
            );

            // Attach period if days is provided
            if ($request->filled('days') && $periodId = Period::where('days', $request->days)->value('id')) {
                $plan->periods()->attach($periodId);
            }

            // Insert pricing data
            if ($request->filled('add_price')) {
                $priceData = collect((array) $request->add_price)->map(fn ($addPrice, $key): array => [
                    'plan_id' => $plan->id,
                    'currency' => $request->currency[$key],
                    'add_price' => $addPrice,
                    'renew_price' => $request->renew_price[$key],
                    'offer_price' => $request->offer_price[$key] ?: null,
                    'price_description' => $request->price_description,
                    'product_quantity' => $request->product_quantity,
                    'no_of_agents' => $request->no_of_agents,
                ])->all();

                $plan->planPrice()->insert($priceData);
            }

            return successResponse(__('message.saved-successfully'));
        } catch (Exception $exception) {
            return errorResponse($exception->getMessage());
        }
    }

    public function getPlan(mixed $planId): JsonResponse
    {
        try {
            /** @var Plan $plan */
            $plan = Plan::with([
                'planPrice',
                'productRelation:id,name',
            ])->findOrFail($planId);

            $firstPrice = $plan->planPrice->first();
            $plan->no_of_agents = $firstPrice?->no_of_agents; // @phpstan-ignore property.notFound
            $plan->product_quantity = $firstPrice?->product_quantity; // @phpstan-ignore property.notFound

            // Remove these fields from each planPrice item
            foreach ($plan->planPrice as $price) {
                unset($price->no_of_agents, $price->product_quantity);
            }

            return successResponse('', $plan);
        } catch (Exception $exception) {
            return errorResponse($exception->getMessage());
        }
    }

    public function updatePlan(mixed $planID, PlanRequest $request): JsonResponse
    {
        try {
            /** @var Plan $plan */
            $plan = Plan::findOrFail($planID);

            $plan->fill($request->validated())->save();

            // Update period if days is provided
            if ($request->filled('days') && $periodId = Period::where('days', $request->days)->value('id')) {
                $plan->periods()->sync([$periodId]);
                // sync replaces existing periods
            }

            // Update plan prices
            if ($request->filled('add_price')) {
                $plan->planPrice()->delete();

                $priceData = collect((array) $request->add_price)->map(fn ($addPrice, $key): array => [
                    'plan_id' => $plan->id,
                    'currency' => $request->currency[$key],
                    'add_price' => $addPrice,
                    'renew_price' => $request->renew_price[$key],
                    'offer_price' => $request->offer_price[$key] ?? null,
                    'price_description' => $request->price_description,
                    'product_quantity' => $request->product_quantity,
                    'no_of_agents' => $request->no_of_agents,
                ])->all();

                $plan->planPrice()->insert($priceData);
            }

            return successResponse(__('message.saved-successfully'));
        } catch (Exception $exception) {
            return errorResponse($exception->getMessage());
        }
    }

    public function deleteBulkPlans(Request $request): JsonResponse
    {
        $ids = $request->input('select', []);

        if (empty($ids)) {
            return errorResponse(__('message.select-a-row'));
        }

        try {
            DB::transaction(function () use ($ids): void {
                $plans = Plan::whereIn('id', $ids)->get();

                foreach ($plans as $plan) {
                    $plan->delete();
                }
            });

            return successResponse(__('message.deleted-successfully'));
        } catch (Throwable $throwable) {
            return errorResponse($throwable->getMessage());
        }
    }
}
