<?php

namespace App\Http\Controllers\Product;

use App\Http\Controllers\License\LicensePermissionsController;
use App\Http\Requests\PlanRequest;
use App\Model\Common\Country;
use App\Model\Common\Setting;
use App\Model\Payment\Currency;
use App\Model\Payment\Period;
use App\Model\Payment\Plan;
use App\Model\Payment\PlanPrice;
use App\Model\Product\Product;
use App\Model\Product\Subscription;
use Illuminate\Http\Request;
use Yajra\DataTables\DataTables;

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
     * Display a listing of the resource.
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        $countries = Country::get(['country_id', 'country_name'])->toArray();
        $currency = $this->currency->where('status', '1')->pluck('name', 'code')->toArray();
        $periods = $this->period->pluck('name', 'days')->toArray();
        $products = $this->product->pluck('name', 'id')->toArray();

        return view(
            'themes.default1.product.plan.index',
            compact('currency', 'periods', 'products', 'countries')
        );
    }

    /**
     * Get plans for chumper datatable.
     */
    public function getPlans()
    {
        $new_plan = Plan::leftJoin('products', 'products.id', '=', 'plans.product')
            ->leftJoin('plan_prices', 'plan_prices.plan_id', '=', 'plans.id')
            ->select('plans.id', 'plans.name', 'plans.days', 'products.name as product', 'plan_prices.add_price', 'plan_prices.currency');
        $defaultCurrency = Setting::where('id', 1)->value('default_currency');

        return DataTables::of($new_plan)
                        ->orderColumn('name', '-plans.id $1')
                        ->orderColumn('days', '-plans.id  $1')
                        ->orderColumn('product', '-plans.id  $1')
                        ->orderColumn('price', '-plans.id  $1')
                        ->orderColumn('currency', '-plans.id  $1')
                        ->addColumn('checkbox', function ($model) {
                            return "<input type='checkbox' class='plan_checkbox' 
                            value=".$model->id.' name=select[] id=check>';
                        })

                        ->addColumn('name', function ($model) {
                            return ucfirst($model->name);
                        })
                        ->addColumn('days', function ($model) {
                            if ($model->days != '') {
                                $months = $model->days / 30;

                                return round($months);
                            }

                            return 'Not Available';
                        })
                        ->addColumn('product', function ($model) {
                            $product = $model->product;

                            if ($product) {
                                $response = $model->product;
                            } else {
                                $response = '--';
                            }

                            return ucfirst($response);
                        })
                         ->addColumn('price', function ($model) {
                             $price = PlanPrice::where('plan_id', $model->id)->where('currency', $model->currency)
                            ->pluck('add_price')->first();
                             if ($price != null) {
                                 return $price;
                             } else {
                                 return 'Not Available';
                             }
                         })
                         ->addColumn('currency', function ($model) use ($defaultCurrency) {
                             if ($defaultCurrency && $model->currency != null) {
                                 return $model->currency;
                             } else {
                                 return 'Not Available';
                             }
                         })
                        ->addColumn('action', function ($model) {
                            return '<a href='.url('plans/'.$model->id.'/edit')." 
                            class='btn btn-sm btn-secondary btn-xs'".tooltip('Edit')."<i class='fa fa-edit' 
                            style='color:white;'> </i></a>";
                        })
                          ->filterColumn('name', function ($query, $keyword) {
                              $sql = 'plans.name like ?';
                              $query->whereRaw($sql, ["%{$keyword}%"]);
                          })
                          ->filterColumn('product', function ($model, $keyword) {
                              $sql = 'products.name like ?';
                              $model->whereRaw($sql, ["%{$keyword}%"]);
                          })
                            ->filterColumn('days', function ($query, $keyword) {
                                $sql = 'plans.days like ?';
                                $query->whereRaw($sql, ["%{$keyword}%"]);
                            })
                              ->filterColumn('price', function ($query, $keyword) {
                                  $sql = 'plan_prices.add_price like ?';
                                  $query->whereRaw($sql, ["%{$keyword}%"]);
                              })
                        ->rawColumns(['checkbox', 'name', 'days', 'product', 'price', 'currency', 'action'])
                        ->make(true);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function create()
    {
        $currency = $this->currency->where('status', 1)->pluck('name', 'code')->toArray();
        $periods = $this->period->pluck('name', 'days')->toArray();
        $products = $this->product->pluck('name', 'id')->toArray();

        return view('themes.default1.product.plan.create', compact('currency', 'periods', 'products'));
    }

    /**
     * Store the Plans Details While Plan Creation.
     *
     * @param  Request  $request  Plan Form Details
     * @return [type] Saves Plan
     *
     * @throws \Illuminate\Validation\ValidationException
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
                        'country_id' => $request->input('country_id')[$key],
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

            return redirect()->back()->with('success', \Lang::get('message.saved-successfully'));
        } catch (Exception $ex) {
            return redirect()->back()->withj('fails', $ex->getMessage());
        }
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  Plan  $plan
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function edit(Plan $plan)
    {
        $currency = $this->currency->where('status', '1')->pluck('name', 'code')->toArray();
        $countries = Country::get(['country_id', 'country_name'])->toArray();
        $planPrices = $plan->planPrice()->get()->toArray();
        $periods = $this->period->pluck('name', 'days')->toArray();
        $products = $this->product->pluck('name', 'id')->toArray();
        $priceDescription = $planPrices[0]['price_description'];
        $productQuantity = $planPrices[0]['product_quantity'];
        $agentQuantity = $planPrices[0]['no_of_agents'];
        foreach ($products as $key => $product) {
            $selectedProduct = $this->product->where('id', $plan->product)
          ->pluck('name', 'id', 'subscription')->toArray();
        }
        $selectedPeriods = $this->period->where('days', $plan->days)
       ->pluck('name', 'days')->toArray();

        return view(
            'themes.default1.product.plan.edit',
            compact(
                'plan',
                'currency',
                'periods',
                'products',
                'selectedPeriods',
                'selectedProduct',
                'priceDescription',
                'productQuantity',
                'agentQuantity',
                'countries',
                'planPrices'
            )
        );
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  Plan  $plan
     * @param  PlanRequest  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(Plan $plan, PlanRequest $request)
    {
        $add_prices = $request->add_price;
        $renew_prices = $request->renew_price;
        $offer_prices = $request->input('offer_price');

        $plan->fill($request->input())->save();

        if (count($add_prices) > 0) {
            $dataForCreating = [];
            $plan->planPrice()->delete();

            foreach ($add_prices as $key => $value) {
                $dataForCreating[] = [
                    'plan_id' => $plan->id,
                    'country_id' => $request->country_id[$key],
                    'currency' => $request->currency[$key],
                    'add_price' => $value,
                    'renew_price' => $renew_prices[$key],
                    'offer_price' => isset($request->offer_price[$key]) ? $request->offer_price[$key] : null,
                    'price_description' => $request->price_description,
                    'product_quantity' => $request->product_quantity,
                    'no_of_agents' => $request->no_of_agents,
                ];
            }

            $plan->planPrice()->insert($dataForCreating);
        }

        return redirect()->back()->with('success', trans('message.updated-successfully'));
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Response
     */
    public function destroy(Request $request)
    {
        $ids = $request->input('select');
        if (! empty($ids)) {
            foreach ($ids as $id) {
                $plan = $this->plan->where('id', $id)->first();
                if ($plan) {
                    $plan->delete();
                } else {
                    echo "<div class='alert alert-success alert-dismissable'>
                    <i class='fa fa-ban'></i>
                    <b>"./* @scrutinizer ignore-type */\Lang::get('message.alert').'!</b> '.
                    /* @scrutinizer ignore-type */\Lang::get('message.success').'
                    <button type=button class=close data-dismiss=alert aria-hidden=true>&times;</button>
                        './* @scrutinizer ignore-type */\Lang::get('message.no-record').'
                </div>';
                    //echo \Lang::get('message.no-record') . '  [id=>' . $id . ']';
                }
            }
            echo "<div class='alert alert-success alert-dismissable'>
                    <i class='fa fa-ban'></i>
                    <b>"./* @scrutinizer ignore-type */\Lang::get('message.alert').'!</b> '.
                    /* @scrutinizer ignore-type */\Lang::get('message.success').'
                    <button type=button class=close data-dismiss=alert aria-hidden=true>&times;</button>
                        './* @scrutinizer ignore-type */\Lang::get('message.deleted-successfully').'
                </div>';
        } else {
            echo "<div class='alert alert-success alert-dismissable'>
                    <i class='fa fa-ban'></i>
                    <b>"./* @scrutinizer ignore-type */ \Lang::get('message.alert').'!</b> '.
                    /* @scrutinizer ignore-type */ \Lang::get('message.success').'
                    <button type=button class=close data-dismiss=alert aria-hidden=true>&times;</button>
                        './* @scrutinizer ignore-type */\Lang::get('message.select-a-row').'
                </div>';
            //echo \Lang::get('message.select-a-row');
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
            $product_id = $request->input('product_id');
            $permissions = LicensePermissionsController::getPermissionsForProduct($product_id);
            $checkSubscription = $permissions['generateUpdatesxpiryDate'] != 0 || $permissions['generateLicenseExpiryDate'] != 0
           || $permissions['generateSupportExpiryDate'] != 0 ? 1 : 0;
            $product = Product::find($product_id);
            $checkIfAgentEnabled = ($product->show_agent == 1) ? 1 : 0;
            $result = ['subscription' => $checkSubscription, 'agentEnable' => $checkIfAgentEnabled];

            return response()->json($result);
        } catch (\Exception $ex) {
            $result = ['subscription' => $ex->getMessage()];

            return response()->json($result);
        }
    }
}
