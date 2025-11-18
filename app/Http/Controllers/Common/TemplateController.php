<?php

namespace App\Http\Controllers\Common;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Product\ProductController;
use App\Model\Common\Template;
use App\Model\Common\TemplateType;
use App\Model\Payment\Period;
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

    public function getTemplates(Request $request)
    {
        try {
            $searchString = $request->input('search-query', '');
            $sortField = $request->input('sort_field', 'id');
            $sortOrder = $request->input('sort_order', 'desc');
            $limit = $request->input('limit', 10);

            $templateData = $this->template
                ->select('id', 'name', 'type')
                ->when($searchString, function ($query) use ($searchString) {
                    $query->where(function ($q) use ($searchString) {
                        $q->where('name', 'like', "%{$searchString}%")
                            ->orWhere('type', 'like', "%{$searchString}%");
                    });
                })
                ->orderBy($sortField, $sortOrder)
                ->simplePaginate($limit);

            $templateData->getCollection()->transform(function ($template) {
                $typeName = $this->type->where('id', $template->type)->value('name') ?? '';

                return [
                    'id' => $template->id,
                    'name' => $template->name,
                    'type' => $typeName,
                    'edit_url' => hyperLinkGenerator("template/edit/{$template->id}", __('message.edit')),
                ];
            });

            return successResponse( __('message.templates_fetched_successfully'), $templateData);

        } catch (\Exception $ex) {
            return errorResponse( __('message.something_went_wrong_fetch_templates'));
        }
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

    public function showTemplate($id)
    {
        try {
            $shortcodes = config('transform');
            $tooltips = config('shortcodes');

            $template = $this->template->find($id);

            if (!$template) {
                return errorResponse( __('message.template_not_found'));
            }
            $type = $this->type->pluck('name', 'id')->toArray();
            $templateType = TemplateType::find($template->type);
            $shortcodeName = $templateType ? $templateType->name : null;
            $codes = null;
            if ($shortcodeName && array_key_exists($shortcodeName, $shortcodes)) {
                $codes = $shortcodes[$shortcodeName];
            }

            $templateIdData = [
                'type' => $type,
                'template' => $template,
                'codes' => $codes,
                'tooltips' => $tooltips,
            ];

            return successResponse( __('message.templates_fetched_successfully'), $templateIdData);

        } catch (\Exception $ex) {
            return errorResponse( __('message.something_went_wrong_fetch_particular_template'));
        }
    }

    public function updateTemplate($id, Request $request)
    {
        $request->validate([
                'name' => 'required',
                'data' => 'required',
                'type' => 'required',
            ], [
                'name.required' => __('validation.auth_controller.name_required'),
                'data.required' => __('message.content_required'),
                'type.required' => __('message.template_type_required'),
            ]);
        try {
            $template = $this->template->find($id);
            if (!$template) {
                return errorResponse(__('message.template_not_found'));
            }

            $template->fill($request->all())->save();

            return successResponse( __('message.template_update_success'), $template);

        } catch (\Exception $ex) {
            return errorResponse(__('message.template_update_error'));
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
            $product = Product::find($id);
            $type = Product::find($id);
            $planid = Plan::where('product', $id)->value('id');
            $price = PlanPrice::where('plan_id', $planid)->value('renew_price');

            $plans = $this->prices($id);
            $status = Product::find($id);
            if ($plans == []) {
                return '';
            }
            $priceList = $this->getPriceList($id);
            $plan_options = '';
            $plan_options1 = [];
            foreach ($priceList as $planId => $planPrice) {
                $plan_options .= '<option value="'.$planId.'" data-price="'.$planPrice.'" data-description="'.$plans[$planId]['description'].'">'.$plans[$planId]['price'].'</option>';
//                $plan_options1[$planId] =['planId'=>$planId,'price'=>$planPrice,'description'=>$plans[$planId]['description'],'processedPrice'=>$plans[$planId]['price']];
            }
            $plan_class = ($plans && $status->status != 1) ? 'stylePlan' : 'planhide';
            $plan_form = '<select name="subscription" class="'.$plan_class.'">'.$plan_options.'</select>';

            $form = html()->form('GET', $url)->open().
            $plan_form.
            html()->input('hidden', 'id')->value($id);

            return $product['add_to_contact'] == 1 ? '' : $form;
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
            $product = Product::find($id);

            $prices = [];
            foreach ($plans as $plan) {
                if ($plan->days == 30 || $plan->days == 31) {
                    $offerprice = PlanPrice::where('plan_id', $plan->id)->value('offer_price');
                    $planDetails = userCurrencyAndPrice('', $plan);
                    $prices[] = $planDetails['plan']->add_price;
                    $prices[] .= $planDetails['symbol'];
                    $prices[] .= $planDetails['currency'];
                }
                if (! empty($prices)) {
                    $format = ($prices[0] != '0') ? currencyFormat(min([$prices[0]]), $code = $prices[2]) : currencyFormat(min([$prices[3]]), $code = $prices[2]);
                    $finalPrice = str_replace($prices[1], '', $format);
                    $cost = '<span class="price-unit">'.$prices[1].'</span>'.$finalPrice;
                    //For vue
//                    $cost=['currency'=>$prices[1], 'price'=>$finalPrice];
                }
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
            $plans = Plan::where('product', $id)->orderBy('id', 'desc')->get();
            $price = [];
            foreach ($plans as $value) {
                $currency = userCurrencyAndPrice('', $value);
                $offer = PlanPrice::where('plan_id', $value->id)->where('currency', $currency)->value('offer_price');
                $product = Product::find($value->product);
                $currencyAndSymbol = userCurrencyAndPrice('', $value);
                $currency = $currencyAndSymbol['currency'];
                $symbol = $currencyAndSymbol['symbol'];
                $cost = $currencyAndSymbol['plan']->add_price;
                $priceDescription = $currencyAndSymbol['plan']->price_description;

                $cost = rounding($cost);
                // $duration = $value->periods;
                $duration = Period::where('days', $value->days)->first();
                $months = $duration ? $duration->name : '';
                if (! in_array($product->id, cloudPopupProducts())) {
                    $price = $this->getPrice($months, $price, $priceDescription, $value, $cost, $currency, $offer, $product);
                } elseif ($cost != '0' && in_array($product->id, cloudPopupProducts())) {
                    $price = $this->getPrice($months, $price, $priceDescription, $value, $cost, $currency, $offer, $product);
                }
                // $price = currencyFormat($cost, $code = $currency);
            }

            return $price;
        } catch (\Exception $ex) {
            \Logger::exception($ex);

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
            \Logger::exception($ex);

            return [];
        }
    }
}
