<?php

namespace App\Http\Controllers\Common;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Product\ProductController;
use App\Model\Common\Template;
use App\Model\Common\TemplateType;
use App\Model\Payment\Plan;
use App\Model\Payment\PlanPrice;
use App\Model\Product\Product;
use Illuminate\Http\Request;

class TemplateController extends Controller
{
    public $template;

    public $type;

    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('admin');

        $template = new Template();
        $this->template = $template;

        $type = new TemplateType();
        $this->type = $type;
    }

    public function index()
    {
        try {
            return view('themes.default1.common.template.inbox');
        } catch (\Exception $ex) {
            return redirect()->back()->with('fails', $ex->getMessage());
        }
    }

    public function getTemplates()
    {
        return \DataTables::of($this->template->select('id', 'name', 'type'))
                        ->orderColumn('name', '-id $1')
                        ->orderColumn('type', '-created_at $1')
                        ->addColumn('checkbox', function ($model) {
                            return "<input type='checkbox' class='template_checkbox' 
                            value=".$model->id.' name=select[] id=check>';
                        })

                         ->addColumn('name', function ($model) {
                             return $model->name;
                         })
                        ->addColumn('type', function ($model) {
                            return $this->type->where('id', $model->type)->value('name');
                        })
                        ->addColumn('action', function ($model) {
                            return '<a href='.url('template/'.$model->id.'/edit').
                            " class='btn btn-sm btn-secondary btn-xs'".tooltip('Edit')."<i class='fa fa-edit'
                                 style='color:white;'> </i></a>";
                        })
                         ->filterColumn('name', function ($query, $keyword) {
                             $sql = 'name like ?';
                             $query->whereRaw($sql, ["%{$keyword}%"]);
                         })
                         ->filterColumn('type', function ($query, $keyword) {
                             $sql = 'type like ?';
                             $query->whereRaw($sql, ["%{$keyword}%"]);
                         })
                        ->rawColumns(['checkbox', 'name', 'type', 'action'])
                        ->make(true);
    }

    public function create()
    {
        try {
            $controller = new ProductController();
            $url = $controller->GetMyUrl();
            $i = $this->template->orderBy('created_at', 'desc')->first()->id + 1;
            $cartUrl = $url.'/'.$i;
            $type = $this->type->pluck('name', 'id')->toArray();

            return view('themes.default1.common.template.create', compact('type', 'cartUrl'));
        } catch (\Exception $ex) {
            return redirect()->back()->with('fails', $ex->getMessage());
        }
    }

    public function store(Request $request)
    {
        $this->validate($request, [
            'name' => 'required',
            'data' => 'required',
            'type' => 'required',
        ]);

        try {
            $this->template->fill($request->input())->save();

            return redirect()->back()->with('success', \Lang::get('message.saved-successfully'));
        } catch (\Exception $ex) {
            return redirect()->back()->with('fails', $ex->getMessage());
        }
    }

    public function edit($id)
    {
        try {
            $controller = new ProductController();
            $url = $controller->GetMyUrl();

            $i = $this->template->orderBy('created_at', 'desc')->first()->id + 1;
            $cartUrl = $url.'/'.$i;
            $template = $this->template->where('id', $id)->first();
            $type = $this->type->pluck('name', 'id')->toArray();

            return view('themes.default1.common.template.edit', compact('type', 'template', 'cartUrl'));
        } catch (\Exception $ex) {
            return redirect()->back()->with('fails', $ex->getMessage());
        }
    }

    public function update($id, Request $request)
    {
        $this->validate($request, [
            'name' => 'required',
            'data' => 'required',
            'type' => 'required',
        ]);

        try {
            $template = $this->template->where('id', $id)->first();
            $template->fill($request->input())->save();

            return redirect()->back()->with('success', \Lang::get('message.updated-successfully'));
        } catch (\Exception $ex) {
            return redirect()->back()->with('fails', $ex->getMessage());
        }
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
            $ids = $request->input('select');
            if (! empty($ids)) {
                foreach ($ids as $id) {
                    $template = $this->template->where('id', $id)->first();
                    if ($template) {
                        $template->delete();
                    } else {
                        echo "<div class='alert alert-danger alert-dismissable'>
                    <i class='fa fa-ban'></i>
                    <b>"./* @scrutinizer ignore-type */\Lang::get('message.alert').'!</b> '.
                    /* @scrutinizer ignore-type */\Lang::get('message.failed').'
                    <button type=button class=close data-dismiss=alert aria-hidden=true>&times;</button>
                        './* @scrutinizer ignore-type */\Lang::get('message.no-record').'
                </div>';
                        //echo \Lang::get('message.no-record') . '  [id=>' . $id . ']';
                    }
                }
                echo "<div class='alert alert-success alert-dismissable'>
                    <i class='fa fa-ban'></i>
                    <b>"./* @scrutinizer ignore-type */\Lang::get('message.alert').
                    '!</b> './* @scrutinizer ignore-type */\Lang::get('message.success').'
                    <button type=button class=close data-dismiss=alert aria-hidden=true>&times;</button>
                        './* @scrutinizer ignore-type */\Lang::get('message.deleted-successfully').'
                </div>';
            } else {
                echo "<div class='alert alert-danger alert-dismissable'>
                    <i class='fa fa-ban'></i>
                    <b>"./* @scrutinizer ignore-type */\Lang::get('message.alert').'!</b> '.
                    /* @scrutinizer ignore-type */\Lang::get('message.failed').'
                    <button type=button class=close data-dismiss=alert aria-hidden=true>&times;</button>
                        './* @scrutinizer ignore-type */\Lang::get('message.select-a-row').'
                </div>';
            }
        } catch (\Exception $e) {
            echo "<div class='alert alert-danger alert-dismissable'>
                    <i class='fa fa-ban'></i>
                    <b>"./* @scrutinizer ignore-type */\Lang::get('message.alert').'!</b> '.
                    /* @scrutinizer ignore-type */\Lang::get('message.failed').'
                    <button type=button class=close data-dismiss=alert aria-hidden=true>&times;</button>
                        '.$e->getMessage().'
                </div>';
        }
    }

    public function plans($url, $id)
    {
        try {
            $plan = new Plan();
            $plan_form = 'Free'; //No Subscription
            $plans = $plan->where('product', '=', $id)->pluck('name', 'id')->toArray();
            $type = Product::find($id);
            $planid = Plan::where('product', $id)->value('id');
            $price = PlanPrice::where('plan_id', $planid)->value('renew_price');

            $plans = $this->prices($id);
            $status = Product::find($id);
            if ($plans == []) {
                return '';
            }
            if ($plans && $status->status != 1) {
                $plan_form = \Form::select('subscription', ['Plans' => $plans], null);
            } else {
                $plan_form = \Form::select('subscription', ['Plans' => $plans], null, ['class' => 'planhide']);
            }
            $form = \Form::open(['method' => 'get', 'url' => $url]).
            $plan_form.
            \Form::hidden('id', $id);

            return $form;
        } catch (\Exception $ex) {
            return redirect()->back()->with('fails', $ex->getMessage());
        }
    }

    /**
     * Gets the least amount to be displayed on pricing page on the top.
     *
     * @param  int  $id  Product id
     * @return string Product price with html
     */
    public function leastAmount($id)
    {
        $countryCheck = true;
        try {
            $cost = 'Free';
            $plans = Plan::where('product', $id)->get();
            if ($plans->count() == 0) {
                return 'Custom Pricing';
            }

            $prices = [];
            if ($plans->count() > 0) {
                foreach ($plans as $plan) {
                    $offerprice = PlanPrice::where('plan_id', $plan->id)->value('offer_price');
                    $planDetails = userCurrencyAndPrice('', $plan);
                    $prices[] = $planDetails['plan']->add_price;
                    $prices[] .= $planDetails['symbol'];
                    $prices[] .= $planDetails['currency'];
                }
                if (! empty($prices[3])) {
                    $format = ($prices[0] != '0') ? currencyFormat(min([$prices[0]]), $code = $prices[2]) : currencyFormat(min([$prices[3]]), $code = $prices[2]);
                } else {
                    $format = currencyFormat(min([$prices[0]]), $code = $prices[2]);
                }
                $finalPrice = str_replace($prices[1], '', $format);
                $cost = '<span class="price-unit">'.$prices[1].'</span>'.$finalPrice;
            }

            return $cost;
        } catch (\Exception $ex) {
            return redirect()->back()->with('fails', $ex->getMessage());
        }
    }

    public function getPrice($months, $price, $priceDescription, $value, $cost, $currency, $offer, $product)
    {
        if (isset($offer) && $offer !== '' && $offer !== null) {
            $cost = $cost - ($offer / 100 * $cost);
        }
        $price1 = currencyFormat($cost, $code = $currency);
        $price[$value->id] = $months.'  '.$price1.' '.$priceDescription;

        return $price;
    }

    public function prices($id)
    {
        try {
            $plans = Plan::where('product', $id)->orderBy('id', 'desc')->get();
            $price = [];
            foreach ($plans as $value) {
                $offer = PlanPrice::where('plan_id', $value->id)->value('offer_price');
                $product = Product::find($value->product);
                $currencyAndSymbol = userCurrencyAndPrice('', $value);
                $currency = $currencyAndSymbol['currency'];
                $symbol = $currencyAndSymbol['symbol'];
                $cost = $currencyAndSymbol['plan']->add_price;
                $priceDescription = $currencyAndSymbol['plan']->price_description;
                $cost = rounding($cost);
                $duration = $value->periods;
                $months = count($duration) > 0 ? $duration->first()->name : '';
                if ($product->type != '4') {
                    $price = $this->getPrice($months, $price, $priceDescription, $value, $cost, $currency, $offer, $product);
                } elseif ($cost != '0' && $product->type == '4') {
                    $price = $this->getPrice($months, $price, $priceDescription, $value, $cost, $currency, $offer, $product);
                }
                // $price = currencyFormat($cost, $code = $currency);
            }

            return $price;
        } catch (\Exception $ex) {
            app('log')->error($ex->getMessage());

            return redirect()->back()->with('fails', $ex->getMessage());
        }
    }

    public function toggle(Request $request)
    {
        $status = $request->toggleState;
        if ($status == 'selected') {
            \Session::forget('toggleState');
            \Session::put('toggleState', 'monthly');
        } elseif ($status == 'unselected') {
            \Session::forget('toggleState');
            \Session::put('toggleState', 'yearly');
        }
    }
}
