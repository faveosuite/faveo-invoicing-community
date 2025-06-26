<?php

namespace App\Http\Controllers\Front;

use App\Http\Controllers\Common\TemplateController;
use App\Model\Common\Setting;
use App\Model\Order\Invoice;
use App\Model\Payment\Currency;
use App\Model\Payment\Plan;
use App\Model\Payment\PlanPrice;
use App\Model\Payment\Tax;
use App\Model\Payment\TaxByState;
use App\Model\Payment\TaxOption;
use App\Model\Product\Product;
use App\User;
use Cart;
use Illuminate\Http\Request;
use Session;

class CartController extends BaseCartController
{
    public $templateController;

    public $product;

    public $currency;

    public $addons;

    public $addonRelation;

    public $licence;

    public $tax_option;

    public $tax_by_state;

    public $setting;

    public function __construct()
    {
        $templateController = new TemplateController();
        $this->templateController = $templateController;

        $product = new Product();
        $this->product = $product;

        $plan_price = new PlanPrice();
        $this->$plan_price = $plan_price;

        $currency = new Currency();
        $this->currency = $currency;

        $tax = new Tax();
        $this->tax = $tax;

        $setting = new Setting();
        $this->setting = $setting;

        $tax_option = new TaxOption();
        $this->tax_option = $tax_option;

        $tax_by_state = new TaxByState();
        $this->tax_by_state = new $tax_by_state();
    }

    /**
     * The first request to the cart Page comes here
     * Get Plan id and Product id as Request.
     *
     * @param  int  $plan  Planid;
     * @param  int  $id  Productid;
     */
    public function cart(Request $request)
    {
        \Session::forget('priceRemaining');
        \Session::forget('priceToBePaid');
        \Session::forget('discount');
        try {
            $plan = '';
            $domain = '';
            $subscription = '';
            if ($request->has('subscription')) {//put he Plan id sent into session variable
                $subscription = $request->get('subscription');
                Session::put('plan_id', $subscription);
            }
            $id = $request->input('id');
            if ($request->has('domain')) {
                $domain = $request->input('domain').'.'.cloudSubDomain();
            }
            if (! property_exists($subscription, Cart::getContent())) {
                $items = $this->addProduct($id, $domain);
                \Cart::add($items); //Add Items To the Cart Collection
            }

            return redirect('show/cart');
        } catch (\Exception $ex) {
            app('log')->error($ex->getMessage());

            return redirect()->back()->with('fails', $ex->getMessage());
        }
    }

    /**
     * Returns the Collection to be added to cart.
     *
     * @author Ashutosh Pathak <ashutosh.pathak@ladybirdweb.com>
     *
     * @date   2019-01-10T18:14:09+0530
     *
     * @param  int  $id  Product Id
     * @return array $items  Array of items and Tax conditions to the cart
     */
    public function addProduct(int $id, $domain = null)
    {
        try {
            $qty = 1;
            $agents = 0; //Unlmited Agents
            if (\Session::has('plan_id')) { //If a plan is selected from dropdown in pricing page, this is true
                $planid = \Session::get('plan_id');
            } else {
                $planid = Plan::where('product', $id)->pluck('id')->first();
            }
            $product = Product::find($id);
            $plan = $product->planRelation->find($planid);
            if ($plan) { //If Plan For a Product exists
                $quantity = $plan->planPrice->first()->product_quantity;
                //If Product quantity is null(when show agent in Product Seting Selected),then set quantity as 1;
                $qty = $quantity != null ? $quantity : 1;
                $currency = userCurrencyAndPrice('', $plan);
                // $this->checkProductsHaveSimilarCurrency($currency['currency']);
            } else {
                throw new \Exception(__('message.product_add_cart_no_plan'));
            }
            $actualPrice = $this->cost($product->id, $planid);
            if (\Session::has('plan') && $product->can_modify_agent) {
                $agtQty = $plan->where('id', $planid)->first()->planPrice->first()->no_of_agents;
            } else {
                $agtQty = $plan->planPrice->first()->no_of_agents;
            }
            $agents = $agtQty != null ? $agtQty : 0;
            $items = ['id' => $planid, 'name' => $product->name, 'price' => $actualPrice,
                'quantity' => $qty, 'attributes' => ['currency' => $currency['currency'], 'symbol' => $currency['symbol'], 'agents' => $agents, 'domain' => $domain], 'associatedModel' => $product];

            return $items;
        } catch (\Exception $e) {
            app('log')->error($e->getMessage());
            throw new \Exception($e->getMessage());
        }
    }

    /**
     * If multiple products are being added to cart, this method checks all the products have similar currency.
     *
     * @param  string  $currency  Currency of the product to be added to cart
     */
    private function checkProductsHaveSimilarCurrency($currency)
    {
        $carts = \Cart::getContent();
        foreach ($carts as $cart) {
            if ($cart->attributes['currency'] != $currency) {
                throw new \Exception(__('message.similar_currency_required'));
            }
        }
    }

    public function showCart()
    {
        try {
            $cartCollection = Cart::getContent();
            foreach ($cartCollection as $item) {
                $cart_currency = $item->attributes->currency;
                \Session::put('currency', $cart_currency);
                $unpaidInvoice = $this->checkUnpaidInvoices($item);

                if ($unpaidInvoice) {
                    Cart::clear($item->id);

                    return redirect('my-invoice/'.$unpaidInvoice->id.'#invoice-section')
                    ->with('warning', __('message.unpaid_invoice_warning'));
                }
            }

            $cartCollection = Cart::getContent()->sortByDesc(function ($item) {
                return (int) $item->id;
            });

            return view('themes.default1.front.cart', compact('cartCollection'));
        } catch (\Exception $ex) {
            app('log')->error($ex->getMessage());

            return redirect()->back()->with('fails', $ex->getMessage());
        }
    }

    /**
     * Checks the unpaid invoice of authorized user.
     *
     * @param  $item
     * @return null|array
     */
    private function checkUnpaidInvoices($item)
    {
        if (\Auth::check()) {
            $unpaidInvoice = Invoice::where('user_id', \Auth::user()->id)
                ->where('is_renewed', 0)
                ->where('status', 'pending')
                ->whereHas('invoiceItem', function ($query) use ($item) {
                    $query->where('product_name', $item->name)->where('quantity', $item->quantity);
                })
                ->first();

            if ($unpaidInvoice) {
                Cart::clear($item->id);

                return $unpaidInvoice;
            }
        }

        return null;
    }

    /**
     * This function removes cart content based on id.
     *
     * @param  Request  $request
     * @return string
     */
    public function cartRemove(Request $request)
    {
        $id = $request->input('id');
        Cart::remove($id);
        Cart::removeConditionsByType('tax');
        Cart::removeConditionsByType('coupon');
        Cart::clearItemConditions($id);

        return 'success';
    }

    /**
     * This function removes overall cart content.
     *
     * @param
     * @return \Illuminate\Http\RedirectResponse
     */
    public function clearCart()
    {
        foreach (Cart::getContent() as $item) {
            Cart::remove($item->id);
            Cart::clearItemConditions($item->id);
            if (\Session::has('domain'.$item->id)) {
                \Session::forget('domain'.$item->id);
            }
        }

        if (Session::has('plan')) {
            Session::forget('plan');
        }
        $renew_control = new \App\Http\Controllers\Order\RenewController();
        $renew_control->removeSession();
        Cart::clearCartConditions();
        Cart::clear();

        return redirect('show/cart');
    }

    /**
     * @param  int  $productid
     * @param  int  $userid
     * @param  int  $planid
     * @return int
     *
     * @throws \Exception
     */
    public function cost($productid, $planid = '', $userid = '', $admin = false)
    {
        try {
            $cost = $this->planCost($productid, $userid, $planid, $admin);

            return $cost;
        } catch (\Exception $ex) {
            app('log')->error($ex->getMessage());
            throw new \Exception($ex->getMessage());
        }
    }

    /**
     * Get Cost For a particular Plan.
     *
     * @param  int  $productid
     * @param  int  $userid
     * @param  int  $planid
     * @return int
     *
     * @throws Exception
     */
    public function planCost($productid, $userid = '', $planid = '', $admin = false)
    {
        try {
            $product = Product::find($productid);
            if ($product->status == 1) {
                $plans = Plan::where('product', $productid)->get();

                if (! \Auth::user()) {//When user is not logged in
                    $location = getLocation();
                    $country = findCountryByGeoip($location['iso_code']);
                    $countryids = \App\Model\Common\Country::where('country_code_char2', $country)->value('country_id');
                    $currencyAndSymbol = getCurrencyForClient($country);
                }
                if (\Auth::user()) {
                    if ($userid == '') {
                        $country = \Auth::user()->country;
                    } else {
                        $country = \DB::table('users')->where('id', $userid)->value('country');
                    }
                    $countryids = \App\Model\Common\Country::where('country_code_char2', $country)->value('country_id');

                    $currencyAndSymbol = getCurrencyForClient($country);
                }

                foreach ($plans as $plan) {
                    $currencyQuery = Plan::where('product', $productid);
                    if ($userid != '') {
                        $currency = userCurrencyAndPrice($userid, $plan);
                    } else {
                        $currency = userCurrencyAndPrice('', $plan);
                    }

                    if (! $admin) {
//                        $offerprice = PlanPrice::where('plan_id', $plan->id)->where('currency', $currency)->value('offer_price');
                        if (\Session::get('toggleState') == 'yearly' || \Session::get('toggleState') == null) {
                            $id = $currencyQuery->whereIn('days', [365, 366])->value('id');
                            $daysQuery = PlanPrice::where('plan_id', $id)->where('currency', $currency)->where('country_id', $countryids)->firstOr(function () use ($currency, $id) {
                                return PlanPrice::where('plan_id', $id)->where('currency', $currency)->where('country_id', 0)->first();
                            });

//                            $cost = $daysQuery->offer_price ? $daysQuery->add_price - (($daysQuery->offer_price / 100) * $daysQuery->add_price) : $daysQuery->add_price;
                            $cost = $daysQuery->add_price;

                        } elseif (\Session::get('toggleState') == 'monthly') {
                            $id = $currencyQuery->whereIn('days', [30, 31])->value('id');
                            $daysQuery = PlanPrice::where('plan_id', $id)->where('currency', $currency)->where('country_id', $countryids)->firstOr(function () use ($currency, $id) {
                                return PlanPrice::where('plan_id', $id)->where('currency', $currency)->where('country_id', 0)->first();
                            });
                            $cost = $daysQuery->offer_price ? $daysQuery->add_price - (($daysQuery->offer_price / 100) * $daysQuery->add_price) : $daysQuery->add_price;
                        }
                    } else {
                        $id = $planid;
                        $daysQuery = PlanPrice::where('plan_id', $id)->where('currency', $currency)->where('country_id', $countryids)->firstOr(function () use ($currency, $id) {
                            return PlanPrice::where('plan_id', $id)->where('currency', $currency)->where('country_id', 0)->first();
                        });
                        $cost = $daysQuery->offer_price ? $daysQuery->add_price - (($daysQuery->offer_price / 100) * $daysQuery->add_price) : $daysQuery->add_price;
                    }
                }

                Session::put('plan', $id);
                Session::put('planDays', Session::get('toggleState'));
                if (\Auth::check()) {
                    Session::forget('toggleState');
                }

                return $cost;
            } else {
                $cost = 0;
                $months = 0;
                if (! $planid) {//When Product Is Added from Cart
                    $planid = Plan::where('product', $productid)->pluck('id')->first();
                } elseif (checkPlanSession() && ! $planid) {
                    $planid = Session::get('plan_id');
                }
                $plan = Plan::where('id', $planid)->where('product', $productid)->first();
                if ($plan) { //Get the Total Plan Cost if the Plan Exists For a Product
                    $currency = userCurrencyAndPrice($userid, $plan);
                    $months = 1;
                    $product = Product::find($productid);
                    $days = $plan->periods->pluck('days')->first();
                    $price = $currency['plan']->add_price;
                    if ($days) { //If Period Is defined for a Particular Plan ie no. of Days Generated
                        $months = $days >= '365' ? $days / 30 / 12 : $days / 30;
                    }
                    $finalPrice = str_replace(',', '', $price);
                    $cost = round($months) * $finalPrice;
                    if ($currency['plan']['offer_price'] != '' && $currency['plan']['offer_price'] != null) {
                        $cost = $cost - ($cost * ($currency['plan']['offer_price'] / 100));
                    }

                    return $cost;
                } else {
                    throw new \Exception(__('message.product_add_cart_no_plan'));
                }

                return $cost;
            }
        } catch (\Exception $ex) {
            throw new \Exception($ex->getMessage());
        }
    }

    /**
     * This function is used to validate coupon and update it.
     *
     * @param  Request  $request
     * @return string
     */
    public function addCouponUpdate(Request $request)
    {
        try {
            $code = \Request::get('coupon');
            $promo_controller = new \App\Http\Controllers\Payment\PromotionController();
            $result = $promo_controller->checkCode($code);
            if ($result == 'success') {
                return redirect()->back()->with('success', \Lang::get('message.updated-successfully'));
            }

            return redirect()->back();
        } catch (\Exception $ex) {
            return redirect()->back()->with('fails', $ex->getMessage());
        }
    }

    /**
     * This function is used to remove coupon.
     *
     * @param  Request  $request
     * @return string
     */
    public function removeCoupon(Request $request)
    {
        try {
            $productid = '';
            $originalPrice = Session::get('oldPrice');
            foreach (\Cart::getContent() as $item) {
                $productid = $item->id;
            }
            if ($productid && $originalPrice) {
                Cart::update($productid, [
                    'price' => $originalPrice,
                ]);
                Session::forget('code');
                Session::forget('oldprice');
                Session::forget('usage');

                return redirect()->back()->with('success', \Lang::get('message.remove_coupon'));
            } else {
                return redirect()->back()->with('fails', \Lang::get('message.no_product'));
            }
        } catch (\Exception $ex) {
            return redirect()->back()->with('fails', \Lang::get('message.oops'));
        }
    }
}
