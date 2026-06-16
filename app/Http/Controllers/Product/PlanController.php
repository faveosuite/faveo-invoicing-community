<?php

namespace App\Http\Controllers\Product;

use Illuminate\Contracts\Database\Query\Builder;
use App\Http\Controllers\License\LicensePermissionsController;
use App\Http\Requests\PlanRequest;
use App\Model\Payment\Currency;
use App\Model\Payment\Period;
use App\Model\Payment\Plan;
use App\Model\Payment\PlanPrice;
use App\Model\Product\Product;
use App\Model\Product\Subscription;
use DB;
use Exception;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Lang;
use Throwable;

class PlanController extends ExtendedPlanController
{
    protected $currency;

    protected $price;

    protected $period;

    protected $product;

    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('admin');
        $plan = new Plan();
        $this->plan = $plan;
        $subscription = new Subscription();
        $this->subscription = $subscription;
        $currency = new Currency();
        $this->currency = $currency;
        $price = new PlanPrice();
        $this->price = $price;
        $period = new Period();
        $this->period = $period;
        $product = new Product();
        $this->product = $product;
    }

    /**
     * Store the Plans Details While Plan Creation.
     *
     * @param  Request  $request  Plan Form Details
     * @return [type] Saves Plan
     *
     * @throws ValidationException
     *
     * @author Ashutosh Pathak <ashutosh.pathak@ladybirdweb.com>
     *
     * @date   2019-01-08T13:32:57+0530
     */
    public function store(PlanRequest $request)
    {
        try {
            $add_prices = $request->add_price;
            $renew_prices = $request->renew_price;
            $offer_prices = $request->offer_price;
            $this->plan->fill($request->input())->save();
            if ($request->input('days') != '') {
                $period = Period::where('days', $request->input('days'))->first()->id;
                $this->plan->periods()->attach($period);
            }

            if (count($add_prices) > 0) {
                $dataForCreating = [];
                foreach ($add_prices as $key => $value) {
                    $dataForCreating[] = [
                        'plan_id' => $this->plan->id,
                        'currency' => $request->input('currency')[$key],
                        'add_price' => $value,
                        'renew_price' => $renew_prices[$key],
                        'offer_price' => $offer_prices[$key] !== '' ? $offer_prices[$key] : null,
                        'price_description' => $request->input('price_description'),
                        'product_quantity' => $request->input('product_quantity'),
                        'no_of_agents' => $request->no_of_agents,
                    ];
                }

                $this->plan->planPrice()->insert($dataForCreating);
            }

            return back()->with('success', Lang::get('message.saved-successfully'));
        } catch (Exception $ex) {
            return back()->withj('fails', $ex->getMessage());
        }
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  Plan  $plan
     * @param  PlanRequest  $request
     * @return RedirectResponse
     */
    public function update(Plan $plan, PlanRequest $request)
    {
        $add_prices = $request->add_price;
        $renew_prices = $request->renew_price;
        $plan->fill($request->input())->save();
        //To change the plan days,whenever we update plan
        if ($request->input('days') != '') {
            $period = Period::where('days', $request->input('days'))->first()->id;
            $plan->periods()->sync($period);
        }

        if (count($add_prices) > 0) {
            $plan->planPrice->each->delete();

            foreach ($add_prices as $key => $value) {
                $plan->planPrice()->create([
                    'plan_id' => $plan->id,
                    'currency' => $request->currency[$key],
                    'add_price' => $value,
                    'renew_price' => $renew_prices[$key],
                    'offer_price' => $request->offer_price[$key] ?? null,
                    'price_description' => $request->price_description,
                    'product_quantity' => $request->product_quantity,
                    'no_of_agents' => $request->no_of_agents,
                ]);
            }
        }

        return back()->with('success', trans('message.updated-successfully'));
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Response
     */
    public function destroy(Request $request)
    {
        try {
            $ids = array_unique($request->input('select', []));
            if (! empty($ids)) {
                foreach ($ids as $id) {
                    $plan = $this->plan->where('id', $id)->first();
                    if ($plan) {
                        $plan->delete();
                    } else {
                        echo "<div class='alert alert-success alert-dismissable'>
                    <i class='fa fa-ban'></i>
                    <b>". /* @scrutinizer ignore-type */ Lang::get('message.alert').'!</b> '.
                            /* @scrutinizer ignore-type */ Lang::get('message.success').'
                    <button type=button class=close data-dismiss=alert aria-hidden=true>&times;</button>
                        '. /* @scrutinizer ignore-type */ Lang::get('message.no-record').'
                </div>';
                        //echo \Lang::get('message.no-record') . '  [id=>' . $id . ']';
                    }
                }

                echo "<div class='alert alert-success alert-dismissable'>
                    <i class='fa fa-ban'></i>
                    <b>". /* @scrutinizer ignore-type */ Lang::get('message.alert').'!</b> '.
                    /* @scrutinizer ignore-type */ Lang::get('message.success').'
                    <button type=button class=close data-dismiss=alert aria-hidden=true>&times;</button>
                        '. /* @scrutinizer ignore-type */ Lang::get('message.deleted-successfully').'
                </div>';
            } else {
                echo "<div class='alert alert-success alert-dismissable'>
                    <i class='fa fa-ban'></i>
                    <b>". /* @scrutinizer ignore-type */ Lang::get('message.alert').'!</b> '.
                    /* @scrutinizer ignore-type */ Lang::get('message.success').'
                    <button type=button class=close data-dismiss=alert aria-hidden=true>&times;</button>
                        '. /* @scrutinizer ignore-type */ Lang::get('message.select-a-row').'
                </div>';
                //echo \Lang::get('message.select-a-row');
            }
        } catch(Exception) {
            echo "<div class='alert alert-danger alert-dismissable'>
                    <i class='fa fa-ban'></i>
                    <b>". /* @scrutinizer ignore-type */ Lang::get('message.alert').'!</b> '.
                /* @scrutinizer ignore-type */ Lang::get('message.cloud_plan_error').'
                    <button type=button class=close data-dismiss=alert aria-hidden=true>&times;</button>
                  
                </div>';
        }
    }

    /**
     * Whether to show Periods when Product Selected
     * Whether to show Product Quantity or No of Agents when Product Is Selected.
     *
     * @author Ashutosh Pathak <ashutosh.pathak@ladybirdweb.com>
     *
     * @date   2019-01-08T12:30:09+0530
     *
     * @param  Request  $request  Receive Product Id as Paramater
     * @return json Returns Boolean value FOR Whether Periods/Agents Enabled for Product
     */
    public function checkSubscription(Request $request)
    {
        try {
            $product_id = $request->integer('product_id');
            $permissions = LicensePermissionsController::getPermissionsForProduct($product_id);
            $checkSubscription = $permissions['generateUpdatesxpiryDate'] != 0 || $permissions['generateLicenseExpiryDate'] != 0
           || $permissions['generateSupportExpiryDate'] != 0 ? 1 : 0;
            $product = Product::find($product_id);
            $checkIfAgentEnabled = ($product->show_agent == 1) ? 1 : 0;
            $result = ['subscription' => $checkSubscription, 'agentEnable' => $checkIfAgentEnabled];

            return response()->json($result);
        } catch (Exception $ex) {
            $result = ['subscription' => $ex->getMessage()];

            return response()->json($result);
        }
    }

    public function getAllPlans(Request $request)
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

                    $q->where('name', 'like', "%$searchQuery%")
                        ->when($daysRange, fn ($q2) => $q2->orWhereBetween('days', $daysRange))
                        ->orWhereHas('productRelation', fn (Builder $q3) => $q3->where('name', 'like', "%$searchQuery%")
                        )
                        ->orWhereHas('planPrice', fn (Builder $q4) => $q4->where('currency', 'like', "%$searchQuery%")
                        );
                });
            })
            ->orderBy($sortField, $sortOrder)
            ->simplePaginate($limit);

        $plans->getCollection()->transform(fn ($plan) => [
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
     * @param  string  $period
     * @return array|null [minDays, maxDays] or null if invalid
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
                'year', 'years' => [$value * 365, $value * 365 + 364], // 1 Year = 365–729, etc.
                default => null,
            };
        }

        return null;
    }

    public function planCreate(PlanRequest $request)
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
            if ($request->filled('days')) {
                if ($periodId = Period::where('days', $request->days)->value('id')) {
                    $plan->periods()->attach($periodId);
                }
            }

            // Insert pricing data
            if ($request->filled('add_price')) {
                $priceData = collect($request->add_price)->map(fn ($addPrice, $key) => [
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
        } catch (Exception $ex) {
            return errorResponse($ex->getMessage());
        }
    }

    public function getPlan($planId)
    {
        try {
            $plan = Plan::with([
                'planPrice',
                'productRelation:id,name',
            ])->findOrFail($planId);

            $firstPrice = $plan->planPrice->first();
            $plan->no_of_agents = $firstPrice?->no_of_agents;
            $plan->product_quantity = $firstPrice?->product_quantity;

            // Remove these fields from each planPrice item
            foreach ($plan->planPrice as $price) {
                unset($price->no_of_agents, $price->product_quantity);
            }

            return successResponse('', $plan);
        } catch (Exception $ex) {
            return errorResponse($ex->getMessage());
        }
    }

    public function updatePlan($planID, PlanRequest $request)
    {
        try {
            $plan = Plan::findOrFail($planID);

            $plan->fill($request->validated())->save();

            // Update period if days is provided
            if ($request->filled('days')) {
                if ($periodId = Period::where('days', $request->days)->value('id')) {
                    $plan->periods()->sync([$periodId]); // sync replaces existing periods
                }
            }

            // Update plan prices
            if ($request->filled('add_price')) {
                $plan->planPrice()->delete();

                $priceData = collect($request->add_price)->map(fn ($addPrice, $key) => [
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
        } catch (Exception $ex) {
            return errorResponse($ex->getMessage());
        }
    }

    public function deleteBulkPlans(Request $request)
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
        } catch (Throwable $ex) {
            return errorResponse($ex->getMessage());
        }
    }
}
