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
                        ->orderColumn('action', '-created_at $1')
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
                            " class='btn btn-sm btn-secondary btn-xs'".tooltip(__('message.edit'))."<i class='fa fa-edit'
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
                        ->rawColumns(['name', 'type', 'action'])
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
            'reply_email' => 'required',
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
            $shortcodes = config('transform');
            $tooltips = config('shortcodes');

            $i = $this->template->orderBy('created_at', 'desc')->first()->id + 1;
            $cartUrl = $url.'/'.$i;
            $template = $this->template->where('id', $id)->first();
            $type = $this->type->pluck('name', 'id')->toArray();
            $templateType = TemplateType::find($template->type);
            $shortcodeName = $templateType->name;
            $codes = null;
            if (array_key_exists($shortcodeName, $shortcodes)) {
                $codes = $shortcodes[$shortcodeName];
            }

            return view('themes.default1.common.template.edit', compact('type', 'template', 'cartUrl', 'codes', 'tooltips'));
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
            $product = Product::find($id);
            if (! $product || $product->add_to_contact == 1) {
                return '';
            }

            $plansData = $this->prices($id);
            if (empty($plansData)) {
                return '';
            }
            $priceList = $this->getPriceList($id);
            $planOptions = '';

            foreach ($priceList as $planId => $planPrice) {
                $description = $plansData[$planId]['description'] ?? '';
                $price = $plansData[$planId]['price'] ?? '';
                $planOptions .= sprintf(
                    '<option value="%s" data-price="%s" data-description="%s">%s</option>',
                    htmlspecialchars($planId),
                    htmlspecialchars($planPrice),
                    htmlspecialchars($description),
                    htmlspecialchars($price)
                );
            }

            $planClass = ($plansData && $product->status != 1) ? 'stylePlan' : 'planhide';
            $planForm = '<select name="subscription" class="'.$planClass.'">'.$planOptions.'</select>';

            $form = html()->form('GET', $url)->open()
                .$planForm
                .html()->input('hidden', 'id')->value($id);

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
        try {
            $cost = 'Free';
            $plans = Plan::where('product', $id)->whereIn('days', [30, 31])->get();
            $prices = [];
            $symbol = '';
            $currency = '';
            foreach ($plans as $plan) {
                $planDetails = userCurrencyAndPrice('', $plan);
                $add_price = $planDetails['plan']->add_price ?? 0;

                // Only consider non-zero prices
                if ($add_price > 0) {
                    $prices[] = $add_price;
                    $symbol = $planDetails['symbol'];
                    $currency = $planDetails['currency'];
                }
            }

            if (! empty($prices)) {
                $minPrice = min($prices);
                $formattedPrice = currencyFormat($minPrice, $currency);
                $amountOnly = trim(str_replace($symbol, '', $formattedPrice));
                $cost = '<span class="price-unit">'.$symbol.'</span>'.$amountOnly;
            }

            return $cost;
        } catch (\Exception $ex) {
            return redirect()->back()->with('fails', $ex->getMessage());
        }
    }

    public function getPrice($months, $price, $priceDescription, $value, $cost, $currency, $offer, $product)
    {
        if (isset($offer) && $offer !== '' && $offer !== null) {
            $cost = $cost - ($offer / 100) * $cost;
        }
        $price1 = currencyFormat($cost, $code = $currency);
        $months = $cost == 0 ? $priceDescription : $months;
        $priceDescription = $priceDescription == '' ? $months : $priceDescription;
        $price[$value->id]['price'] = $price1.' '.$priceDescription;
        $price[$value->id]['description'] = $priceDescription != '' ? $priceDescription : '';

        return $price;
    }

    public function prices($id)
    {
        try {
            $plans = Plan::where('product', $id)
                ->with(['planPrice', 'periods'])
                ->orderByDesc('id')
                ->get();

            $result = [];

            // Cache product outside loop to avoid repeated queries
            $product = Product::find($id);
            if (! $product) {
                return $result;
            }

            $cloudPopupProducts = cloudPopupProducts();

            foreach ($plans as $plan) {
                $currencyAndSymbol = userCurrencyAndPrice('', $plan);
                $currency = $currencyAndSymbol['currency'];
                $planData = $currencyAndSymbol['plan'];

                if (! $planData || ($planData->add_price ?? 0) <= 0) {
                    continue;
                }

                $offer = PlanPrice::where('plan_id', $plan->id)
                    ->where('currency', $currency)
                    ->value('offer_price');

                $cost = rounding($planData->add_price);
                $priceDescription = $planData->price_description;
                $months = $plan->period ? $plan->period->name : '';

                $includePrice =
                    (! in_array($product->id, $cloudPopupProducts)) ||
                    (in_array($product->id, $cloudPopupProducts) && $cost != 0);

                if ($includePrice) {
                    $result = $this->getPrice(
                        $months,
                        $result,
                        $priceDescription,
                        $plan,
                        $cost,
                        $currency,
                        $offer,
                        $product
                    );
                }
            }

            return $result;
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
            \Session::put('toggleState', 'yearly');
        } elseif ($status == 'unselected') {
            \Session::forget('toggleState');
            \Session::put('toggleState', 'monthly');
        }
    }

    public function getPriceList($id)
    {
        try {
            $plans = Plan::where('product', $id)->orderBy('id', 'desc')->get();
            $prices = [];

            foreach ($plans as $plan) {
                $planDetails = userCurrencyAndPrice('', $plan);
                if (! $planDetails || ($planDetails['plan']->add_price ?? 0) <= 0) {
                    continue;
                }
                $cost = rounding($planDetails['plan']->add_price); // Get price and round it
                $currencyCode = $planDetails['currency']; // Get currency code

                // Format the price similar to YearlyAmount but without symbol
                $formattedPrice = currencyFormat($cost, $code = $currencyCode);
                $finalPrice = str_replace($planDetails['symbol'], '', $formattedPrice); // Remove symbol

                // Store only the formatted price with plan ID as key
                $prices[$plan->id] = trim($finalPrice);
            }

            return $prices;
        } catch (\Exception $ex) {
            app('log')->error($ex->getMessage());

            return [];
        }
    }
}
