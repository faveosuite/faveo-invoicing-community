<?php

namespace App\Http\Controllers\Front;

use App\ApiKey;
use App\DefaultPage;
use App\Demo_page;
use App\Http\Controllers\Common\TemplateController;
use App\Http\Controllers\Controller;
use App\Http\Requests\Front\ContactRequest;
use App\Http\Requests\Front\PageRequest;
use App\Model\Common\Country;
use App\Model\Common\PricingTemplate;
use App\Model\Common\Setting;
use App\Model\Common\StatesSubdivisions;
use App\Model\Common\StatusSetting;
use App\Model\Common\Template;
use App\Model\Common\TemplateType;
use App\Model\Front\FrontendPage;
use App\Model\Payment\Plan;
use App\Model\Payment\PlanPrice;
use App\Model\Product\Product;
use App\Model\Product\ProductGroup;
use Illuminate\Http\Request;

class PageController extends Controller
{
    public $page;

    public function __construct()
    {
        $this->middleware('auth', ['except' => ['pageTemplates', 'contactUs', 'postDemoReq', 'postContactUs']]);

        $page = new FrontendPage();
        $this->page = $page;
    }

    public function index()
    {
        try {
            $pages_count = count($this->page->all());

            return view('themes.default1.front.page.index', compact('pages_count'));
        } catch (\Exception $ex) {
            return redirect()->back()->with('fails', $ex->getMessage());
        }
    }

    public function getPages()
    {
        return \DataTables::of($this->page->select('id', 'name', 'url', 'created_at'))
                        ->orderColumn('name', '-id $1')
                        ->orderColumn('url', '-id $1')
                        ->orderColumn('created_at', '-id $1')
                        ->addColumn('checkbox', function ($model) {
                            return "<input type='checkbox' class='page_checkbox' 
                            value=".$model->id.' name=select[] id=check>';
                        })
                        ->addColumn('name', function ($model) {
                            return ucfirst($model->name);
                        })
                        ->addColumn('url', function ($model) {
                            return $model->url;
                        })
                        ->addColumn('created_at', function ($model) {
                            return getDateHtml($model->created_at);
                        })

                        ->addColumn('action', function ($model) {
                            return '<a href='.url('pages/'.$model->id.'/edit')
                            ." class='btn btn-sm btn-secondary btn-xs'".tooltip('Edit')."<i class='fa fa-edit'
                                 style='color:white;'> </i></a>";
                        })
                          ->filterColumn('name', function ($query, $keyword) {
                              $sql = 'name like ?';
                              $query->whereRaw($sql, ["%{$keyword}%"]);
                          })
                            ->filterColumn('url', function ($query, $keyword) {
                                $sql = 'url like ?';
                                $query->whereRaw($sql, ["%{$keyword}%"]);
                            })

                          ->rawColumns(['checkbox', 'name', 'url',  'created_at', 'action'])
                        ->make(true);
        // ->searchColumns('name', 'content')
        // ->orderColumns('name')
        // ->make();
    }

    public function create()
    {
        try {
            $parents = $this->page->pluck('name', 'id')->toArray();

            return view('themes.default1.front.page.create', compact('parents'));
        } catch (\Exception $ex) {
            app('log')->error($ex->getMessage());

            return redirect()->back()->with('fails', $ex->getMessage());
        }
    }

    public function edit($id)
    {
        try {
            $page = $this->page->where('id', $id)->first();
            $parents = $this->page->where('id', '!=', $id)->pluck('name', 'id')->toArray();
            $selectedDefault = DefaultPage::value('page_id');
            $date = $this->page->where('id', $id)->pluck('created_at')->first();
            $publishingDate = date('m/d/Y', strtotime($date));
            $selectedParent = $this->page->where('id', $id)->pluck('parent_page_id')->toArray();
            $parentName = $this->page->where('id', $selectedParent)->pluck('name', 'id')->toArray();

            return view('themes.default1.front.page.edit', compact('parents', 'page', 'selectedDefault', 'publishingDate', 'selectedParent',
                'parentName'));
        } catch (\Exception $ex) {
            return redirect()->back()->with('fails', $ex->getMessage());
        }
    }

    public function store(PageRequest $request)
    {
        try {
            $pages_count = count($this->page->all());
            $url = $request->input('url');
            if ($request->input('type') == 'contactus') {
                $url = url('/contact-us');
            }
            $this->page->name = $request->input('name');
            $this->page->publish = $request->input('publish');
            $this->page->slug = $request->input('slug');
            $this->page->url = $url;
            $this->page->parent_page_id = $request->input('parent_page_id');
            $this->page->type = $request->input('type');
            $this->page->content = $request->input('content');
            if ($pages_count <= 2) {
                $this->page->save();

                return redirect()->back()->with('success', trans('message.saved-successfully'));
            } else {
                return redirect()->back()->with('fails', trans('message.limit_exceed'));
            }
        } catch (\Exception $ex) {
            app('log')->error($ex->getMessage());

            return redirect()->back()->with('fails', $ex->getMessage());
        }
    }

    public function update($id, PageRequest $request)
    {
        try {
            if ($request->input('default_page_id') != '') {
                $page = $this->page->where('id', $id)->first();
                $page->fill($request->except('created_at'))->save();
                $date = \DateTime::createFromFormat('m/d/Y', $request->input('created_at'));
                $page->created_at = $date->format('Y-m-d H:i:s');
                $page->save();
                $defaultUrl = $this->page->where('id', $request->input('default_page_id'))->pluck('url')->first();
                DefaultPage::find(1)->update(['page_id' => $request->input('default_page_id'), 'page_url' => $defaultUrl]);
            } else {
                DefaultPage::find(1)->update(['page_id' => 1, 'page_url' => url('my-invoices')]);
            }

            return redirect()->back()->with('success', \Lang::get('message.updated-successfully'));
        } catch (\Exception $ex) {
            return redirect()->back()->with('fails', $ex->getMessage());
        }
    }

    public function getPageUrl($slug)
    {
        $productController = new \App\Http\Controllers\Product\ProductController();
        //  $url = url('/');
        //  $segment = $this->addSegment(['public/pages']);
        $url = url('/');

        $slug = str_slug($slug, '-');
        echo $url.'/pages'.'/'.$slug;
    }

    public function getSlug($slug)
    {
        $slug = str_slug($slug, '-');
        echo $slug;
    }

    public function addSegment($segments = [])
    {
        $segment = '';
        foreach ($segments as $seg) {
            $segment .= '/'.$seg;
        }

        return $segment;
    }

    public function generate(Request $request)
    {
        // dd($request->all());
        if ($request->has('slug')) {
            $slug = $request->input('slug');

            return $this->getSlug($slug);
        }
        if ($request->has('url')) {
            $slug = $request->input('url');

            return $this->getPageUrl($slug);
        }
    }

    public function show($slug)
    {
        try {
            $page = $this->page->where('slug', $slug)->where('publish', 1)->first();
            if ($page && $page->type == 'cart') {
                return $this->cart();
            }

            return view('themes.default1.front.page.show', compact('page'));
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
            $defaultPageId = DefaultPage::pluck('page_id')->first();
            if (! empty($ids)) {
                foreach ($ids as $id) {
                    if ($id != $defaultPageId) {
                        $page = $this->page->where('id', $id)->first();
                        if ($page) {
                            $page->delete();
                        } else {
                            echo "<div class='alert alert-danger alert-dismissable'>
                    <i class='fa fa-ban'></i>
                    <b>"./* @scrutinizer ignore-type */\Lang::get('message.alert').'!</b> '.
                    /* @scrutinizer ignore-type */
                    \Lang::get('message.failed').'
                    <button type=button class=close data-dismiss=alert aria-hidden=true>&times;</button>
                        './* @scrutinizer ignore-type */\Lang::get('message.no-record').'
                </div>';
                            //echo \Lang::get('message.no-record') . '  [id=>' . $id . ']';
                        }
                        echo "<div class='alert alert-success alert-dismissable'>
                    <i class='fa fa-ban'></i>

                    <b>"./* @scrutinizer ignore-type */ \Lang::get('message.alert').'!</b> '.
                    /* @scrutinizer ignore-type */
                    \Lang::get('message.success').'

                    <button type=button class=close data-dismiss=alert aria-hidden=true>&times;</button>
                        './* @scrutinizer ignore-type */\Lang::get('message.deleted-successfully').'
                </div>';
                    } else {
                        echo "<div class='alert alert-danger alert-dismissable'>
                    <i class='fa fa-ban'></i>
                    <b>"./* @scrutinizer ignore-type */\Lang::get('message.alert').'!</b> '.
                    /* @scrutinizer ignore-type */\Lang::get('message.failed').'
                    <button type=button class=close data-dismiss=alert aria-hidden=true>&times;</button>
                        './* @scrutinizer ignore-type */ \Lang::get('message.can-not-delete-default-page').'
                </div>';
                    }
                }
            } else {
                echo "<div class='alert alert-danger alert-dismissable'>
                    <i class='fa fa-ban'></i>
                    <b>"./* @scrutinizer ignore-type */\Lang::get('message.alert').'!</b> '.
                    /* @scrutinizer ignore-type */\Lang::get('message.failed').'
                    <button type=button class=close data-dismiss=alert aria-hidden=true>&times;</button>
                        './* @scrutinizer ignore-type */\Lang::get('message.select-a-row').'
                </div>';
                //echo \Lang::get('message.select-a-row');
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

    public function getstrikePriceYear($id)
    {
        $countryCheck = true;
        try {
            $cost = 'Free';
            $plans = Plan::where('product', $id)->get();
            $product = Product::find($id);
            $prices = [];
            if ($plans->count() > 0) {
                foreach ($plans as $plan) {
                    if ($product->status) {
                        if ($plan->days == 365 || $plan->days == 366) {
                            $currency = userCurrencyAndPrice('', $plan);
                            $offerprice = PlanPrice::where('plan_id', $plan->id)->where('currency', $currency)->value('offer_price');
                            $planDetails = userCurrencyAndPrice('', $plan);
                            $prices[] = ($product->status) ? round($planDetails['plan']->add_price / 12) : $planDetails['plan']->add_price;
                            $prices[] .= $planDetails['symbol'];
                            $prices[] .= $planDetails['currency'];
                        }
                    } else {
                        $currency = userCurrencyAndPrice('', $plan);
                        $offerprice = PlanPrice::where('plan_id', $plan->id)->where('currency', $currency)->value('offer_price');
                        $planDetails = userCurrencyAndPrice('', $plan);
                        $prices[] = $planDetails['plan']->add_price;
                        $prices[] .= $planDetails['symbol'];
                        $prices[] .= $planDetails['currency'];
                    }

                    if (! empty($prices)) {
                        if (isset($offerprice) && $offerprice != '' && $offerprice != null) {
                            $prices[0] = $prices[0] - (($offerprice / 100) * $prices[0]);
                        }
                        $format = currencyFormat(min([$prices[0]]), $code = $prices[2]);
                        $finalPrice = str_replace($prices[1], '', $format);
                        $cost = '<span class="price-unit">'.$prices[1].'</span>'.$finalPrice;
                    }
                }
            }

            return $cost;
        } catch (\Exception $ex) {
            return redirect()->back()->with('fails', $ex->getMessage());
        }
    }

    public function transformTemplate($type, $data, $trasform = [])
    {
        $config = \Config::get("transform.$type");
        $result = '';
        $array = [];
        foreach ($trasform as $trans) {
            $array[] = $this->checkConfigKey($config, $trans);
        }
        $c = count($array);
        for ($i = 0; $i < $c; $i++) {
            $array1 = $this->keyArray($array[$i]);
            $array2 = $this->valueArray($array[$i]);
            $id = Product::where('name', $array2[0])->value('id');
            $data = Product::where('name', $array2[0])->value('highlight') ? PricingTemplate::findorFail(3)->data : PricingTemplate::findorFail(4)->data;
            $offerprice = $this->getOfferprice($id);
            $description = self::getPriceDescription($id);
            $month_offer_price = $offerprice['30_days'] ?? null;
            $year_offer_price = $offerprice['365_days'] ?? null;

            if (Product::find($id)->add_to_contact == 1) {
                $data = str_replace('{{strike-price}}', '', $data);
                $data = str_replace('{{strike-priceyear}}', '', $data);
                $data = str_replace('{{price}}', 'Custom Pricing', $data);
                $data = str_replace('{{price-year}}', 'Custom Pricing', $data);
            }
            if ($month_offer_price === '' || $month_offer_price === null) {
                $data = str_replace('{{strike-price}}', '', $data);
            }
            $product = Product::find($id);

            if (! $product->status) {
                if (empty($month_offer_price) && empty($year_offer_price)) {
                    $data = str_replace('{{strike-priceyear}}', '', $data);
                }
            } elseif (empty($year_offer_price)) {
                $data = str_replace('{{strike-priceyear}}', '', $data);
            }
            if ($year_offer_price !== '' && $year_offer_price !== null) {
                $offerprice = $this->getPayingprice($id);
                $offerpriceYear = $this->getstrikePriceYear($id);
                $strikePrice = $this->YearlyAmount($id);
                $data = str_replace('{{price}}', $offerprice, $data);
                if ($month_offer_price !== '' && $month_offer_price !== null) {
                    $data = str_replace('{{strike-price}}', $array2[1], $data);
                }
                $data = str_replace('{{price-year}}', $offerpriceYear, $data);

                if ($year_offer_price !== '' && $year_offer_price !== null) {
                    $data = str_replace('{{strike-priceyear}}', $strikePrice, $data);
                }
            }

            $result .= str_replace($array1, $array2, $data);
        }

        return $result;
    }

    public function transform($type, $data, $trasform = [])
    {
        $config = \Config::get("transform.$type");
        $result = '';
        $array = [];
        foreach ($trasform as $trans) {
            $array[] = $this->checkConfigKey($config, $trans);
        }
        $c = count($array);
        for ($i = 0; $i < $c; $i++) {
            $array1 = $this->keyArray($array[$i]);
            $array2 = $this->valueArray($array[$i]);
            $result .= str_replace($array1, $array2, $data);
        }

        return $result;
    }

    public function getPayingprice($id)
    {
        $countryCheck = true;
        try {
            $cost = 'Free';
            $plans = Plan::where('product', $id)->get();

            $prices = [];
            if ($plans->count() > 0) {
                foreach ($plans as $plan) {
                    if ($plan->days == 30 || $plan->days == 31) {
                        $currency = userCurrencyAndPrice('', $plan);
                        $offerprice = PlanPrice::where('plan_id', $plan->id)->where('currency', $currency)->value('offer_price');
                        $planDetails = userCurrencyAndPrice('', $plan);
                        $price = $planDetails['plan']->add_price;
                        $symbol = $planDetails['symbol'];
                        $currency = $planDetails['currency'];
                        if (isset($offerprice) && $offerprice != '' && $offerprice != null) {
                            $price = $price - (($offerprice / 100) * $price);
                        }

                        $prices[] = $price;
                        $prices[] .= $symbol;
                        $prices[] .= $currency;
                    }
                }

                if (! empty($prices)) {
                    $format = currencyFormat(min([$prices[0]]), $code = $prices[2]);
                    $finalPrice = str_replace($prices[1], '', $format);
                    $cost = '<span class="price-unit">'.$prices[1].'</span>'.$finalPrice;
                }
            }

            return $cost;
        } catch (\Exception $ex) {
            return redirect()->back()->with('fails', $ex->getMessage());
        }
    }

    /**
     * Get Page Template when Group in Store Dropdown is
     * selected on the basis of Group id.
     *
     * @author Ashutosh Pathak <ashutosh.pathak@ladybirdweb.com>
     *
     * @date   2019-01-10T01:20:52+0530
     *
     * @param  int  $groupid  Group id
     * @param  int  $templateid  Id of the Template
     * @return
     */
    public function pageTemplates(?int $templateid = null, int $groupid)
    {
        try {
            $productsHightlight = Product::wherehighlight(1)->get();

            // $data = PricingTemplate::findorFail($templateid)->data;
            $headline = ProductGroup::findorFail($groupid)->headline;
            $tagline = ProductGroup::findorFail($groupid)->tagline;
            $currencyAndSymbol = '';
            if (! \Auth::user()) {
                $location = getLocation();
                $country = findCountryByGeoip($location['iso_code']);
                $countryids = \App\Model\Common\Country::where('country_code_char2', $country)->value('country_id');
                $currencyAndSymbol = getCurrencyForClient($country);
            }
            if (\Auth::user()) {
                $country = \DB::table('users')->where('id', \Auth::user()->id)->value('country');
                $countryids = \App\Model\Common\Country::where('country_code_char2', $country)->value('country_id');
                $currencyAndSymbol = getCurrencyForClient($country);
            }

            $defaultCurrency = Setting::first()->default_currency;
            $productsRelatedToGroup = \App\Model\Product\Product::where('products.group', $groupid)
                ->where('products.hidden', '!=', 1)
                ->join('plans', 'products.id', '=', 'plans.product')
                ->join('plan_prices', 'plans.id', '=', 'plan_prices.plan_id')
                ->select(
                    'products.*',
                    'plan_prices.add_price',
                    \DB::raw("
            CASE
                WHEN plan_prices.currency = '$currencyAndSymbol' THEN 1
                WHEN plan_prices.currency = '$defaultCurrency' THEN 2
                WHEN plan_prices.currency != '' THEN 3
                ELSE 4
            END as currency_priority
        ")
                )
                ->orderBy('products.id')
                ->orderBy('currency_priority')
                ->orderByRaw('CAST(plan_prices.add_price AS DECIMAL(10, 2)) ASC')
                ->orderBy('plans.created_at', 'ASC')
                ->get()
                ->unique('id');

            $trasform = [];
            $templates = $this->getTemplateOne($productsRelatedToGroup, $trasform);
            if (empty($templates)) {
                $templates = \Lang::get('message.empty_group');
            }
            $products = Product::all();
            $plan = '';
            $description = '';
            $status = null;
            foreach ($productsRelatedToGroup as $product) {
                $plan = Product::find($product->id)->plan();
                $description = self::getPriceDescription($product->id);
                $status = Product::find($product->id);
            }

            return view('themes.default1.common.template.shoppingcart', compact('templates', 'headline', 'tagline', 'description', 'status'));
        } catch (\Exception $ex) {
            app('log')->error($ex->getMessage());

            return redirect()->back()->with('fails', $ex->getMessage());
        }
    }

    /**
     * This function returns to the contact us page.
     *
     * @param
     * @param
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View|\Illuminate\Foundation\Application|\Illuminate\Http\RedirectResponse
     *
     * @throws
     */
    public function contactUs()
    {
        try {
            $status = StatusSetting::select('recaptcha_status', 'v3_recaptcha_status', 'msg91_status', 'emailverification_status', 'terms')->first();
            $apiKeys = ApiKey::select('nocaptcha_sitekey', 'captcha_secretCheck', 'msg91_auth_key', 'terms_url')->first();
            $set = new \App\Model\Common\Setting();
            $set = $set->findOrFail(1);
            $address = preg_replace("/^\R+|\R+\z/", '', $set->address);
            $state = StatesSubdivisions::where('state_subdivision_code', $set->state)->value('state_subdivision_name');
            $country = Country::where('country_code_char2', $set->country)->value('country_name');

            return view('themes.default1.front.contact', compact('status', 'apiKeys', 'set', 'state', 'country', 'address'));
        } catch (\Exception $ex) {
            return redirect()->back()->with('fails', $ex->getMessage());
        }
    }

    /**
     * Get  Template For Products.
     *
     * @param  $helpdesk_products
     * @param  $data
     * @param  $trasform
     * @return string
     */
    public function getTemplateOne($helpdesk_products, &$trasform)
    {
        try {
            if ($helpdesk_products->isEmpty()) {
                return '';
            }

            $temp_controller = new TemplateController();
            $highlightedProducts = Product::whereIn('name', $helpdesk_products->pluck('name'))
                ->pluck('highlight', 'name')
                ->toArray();
            foreach ($helpdesk_products as $product) {
                $productId = $product->id;
                $productName = $product->name;
                $highlight = $highlightedProducts[$productName] ?? false;
                $orderButton = $highlight ? 'btn-primary' : 'btn-dark';

                $trasform[$productId] = [
                    'price' => $temp_controller->leastAmount($productId),
                    'price-year' => $this->YearlyAmount($productId),
                    'price-description' => $this->getPriceDescription($productId),
                    'pricemonth-description' => $this->getmonthPriceDescription($productId),
                    'strike-price' => $temp_controller->leastAmount($productId),
                    'strike-priceyear' => $this->YearlyAmount($productId),
                    'name' => $productName,
                    'feature' => $product->description,
                    'subscription' => $product->type == 4 ? '' : $temp_controller->plans($product->shoping_cart_link, $productId),
                    'url' => $this->generateProductUrl($product, $orderButton, $highlight),
                ];
            }
            $data = PricingTemplate::findOrFail(1)->data;

            return $this->transformTemplate('cart', $data, $trasform);
        } catch (\Exception $ex) {
            return redirect()->back()->with('fails', $ex->getMessage());
        }
    }

    private function generateProductUrl($product, $orderButton, $highlight)
    {
        if ($product->add_to_contact != 1) {
            if (in_array($product->id, cloudPopupProducts())) {
                return '<button class="btn '.$orderButton.' btn-modern buttonsale" data-toggle="modal" data-target="#tenancy" data-mydata="'.$product->id.'">
                                <span style="white-space: nowrap;">Order Now</span>
                            </button>';
            } else {
                return '<input type="submit" value="Order Now" class="btn '.$orderButton.' btn-modern buttonsale"></form>';
            }
        } else {
            return '<a class="btn '.$orderButton.' btn-modern sales buttonsale" href="https://www.faveohelpdesk.com/contact-us/">Contact Sales</a>';
        }
    }

    public function plansYear($url, $id)
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
            if ($plans) {
                $plan_form = \Form::select('subscription', ['Plans' => $plans], null);
            }
            $form = \Form::open(['method' => 'get', 'url' => $url]).
            $plan_form.
            \Form::hidden('id', $id);

            return $product['add_to_contact'] == 1 ? '' : $form;
        } catch (\Exception $ex) {
            return redirect()->back()->with('fails', $ex->getMessage());
        }
    }

    public function getPrice($months, $price, $priceDescription, $value, $cost, $currency, $offer, $product)
    {
        $cost = $cost * 12;
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
                $priceDescription = 'Per Year';
                $cost = rounding($cost);
                $duration = $value->periods;
                $months = count($duration) > 0 ? $duration->first()->name : '';
                if (! in_array($product->id, cloudPopupProducts())) {
                    $price = $this->getPrice($months, $price, $priceDescription, $value, $cost, $currency, $offer, $product);
                } elseif ($cost != '0' && in_array($product->id, cloudPopupProducts())) {
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

    public function getOfferprice(int $productid)
    {
        $plans = Plan::where('product', $productid)->get();

        $offerprices = [
            '30_days' => null,
            '365_days' => null,
        ];

        foreach ($plans as $plan) {
            $currency = userCurrencyAndPrice('', $plan);
            $offer_price = PlanPrice::where('plan_id', $plan->id)->where('currency', $currency)->value('offer_price');

            if ($plan->days == '30' || $plan->days == '31') {
                $offerprices['30_days'] = $offer_price;
            } elseif ($plan->days == '365' || $plan->days == '366') {
                $offerprices['365_days'] = $offer_price;
            }
        }

        return $offerprices;
    }

    public function YearlyAmount($id)
    {
        $countryCheck = true;
        try {
            $cost = 'Free';
            $plans = Plan::where('product', $id)->get();
            $product = Product::find($id);
            $offer = $this->getOfferprice($id);

            $planId = Plan::where('product', $id)->pluck('id')->first();
            $prices = [];
            foreach ($plans as $plan) {
                if ($plan->days == 365 || $plan->days == 366) {
                    $planDetails = userCurrencyAndPrice('', $plan);
                    $prices[] = ($product->status) ? round($planDetails['plan']->add_price / 12) : $planDetails['plan']->add_price;
                    $prices[] .= $planDetails['symbol'];
                    $prices[] .= $planDetails['currency'];
                } elseif (! $product->status && ! in_array($product->id, cloudPopupProducts())) {
                    $planDetails = userCurrencyAndPrice('', $plan);
                    $prices[] = $planDetails['plan']->add_price;
                    $prices[] .= $planDetails['symbol'];
                    $prices[] .= $planDetails['currency'];
                }

                if (! empty($prices)) {
                    $format = currencyFormat(min([$prices[0]]), $code = $prices[2]);
                    $finalPrice = str_replace($prices[1], '', $format);
                    $cost = '<span class="price-unit" id="'.$planId.'">'.$prices[1].'</span>'.$finalPrice;
                }
            }

            return $cost;
        } catch (\Exception $ex) {
            return redirect()->back()->with('fails', $ex->getMessage());
        }
    }

    public function getmonthPriceDescription(int $productid)
    {
        try {
            $product = Product::find($productid);

            if ($product['add_to_contact'] == 1) {
                return '';
            }

            $priceDescription = '';

            $plans = Plan::where('product', $productid)->get();

            if ($plans) {
                foreach ($plans as $plan) {
                    if ($plan->days == 30 || $plan->days == 31) {
                        $description = $plan->planPrice->first();

                        if (is_null($description->add_price) || $description->add_price === '' || $description->add_price == 0) {
                            $priceDescription = 'free';
                        } else {
                            $priceDescription = $description->no_of_agents ? 'per month for <strong>'.' '.$description->no_of_agents.' '.'agent</strong>' : 'per month';
                        }

                        break;
                    }
                }
            }

            return $priceDescription;
        } catch (\Exception $ex) {
            app('log')->error($ex->getMessage());

            return redirect()->back()->with('fails', $ex->getMessage());
        }
    }

    /**
     * Get Price Description(eg: Per Year,Per Month ,One-Time) for a Product.
     *
     * @author Ashutosh Pathak <ashutosh.pathak@ladybirdweb.com>
     *
     * @date   2019-01-09T00:20:09+0530
     *
     * @param  int  $productid  Id of the Product
     * @return string $priceDescription        The Description of the Price
     */
    public function getPriceDescription(int $productId)
    {
        try {
            $product = Product::find($productId);

            if ($product->add_to_contact == 1) {
                return '';
            }

            $plans = Plan::where('product', $productId)
                        ->with('planPrice')
                        ->cursor();

            foreach ($plans as $plan) {
                if (in_array($plan->days, [365, 366])) {
                    $description = $plan->planPrice->first();
                    if ($description) {
                        if (is_null($description->add_price) || $description->add_price === '' || $description->add_price == 0) {
                            return 'free';
                        }

                        if ($product->status) {
                            return $description->no_of_agents
                                ? 'per month for <strong>'.$description->no_of_agents.' agent</strong>'
                                : 'per month';
                        }

                        return $description->price_description;
                    }
                }
            }

            if (! $product->status) {
                $plan = $plans->first();
                if ($plan && $plan->planPrice->isNotEmpty()) {
                    return $plan->planPrice->first()->price_description;
                }
            }

            return '';
        } catch (\Exception $ex) {
            app('log')->error($ex->getMessage());

            return '';
        }
    }

    public function checkConfigKey($config, $transform)
    {
        $result = [];
        if ($config) {
            foreach ($config as $key => $value) {
                if (array_key_exists($key, $transform)) {
                    $result[$value] = $transform[$key];
                }
            }
        }

        return $result;
    }

    public function keyArray($array)
    {
        $result = [];
        foreach ($array as $key => $value) {
            $result[] = $key;
        }

        return $result;
    }

    public function valueArray($array)
    {
        $result = [];
        foreach ($array as $key => $value) {
            $result[] = $value;
        }

        return $result;
    }

    public function postContactUs(ContactRequest $request)
    {
        try {
            $contact = getContactData();
            // Check if the honeypot field is filled
            if ($request->input('conatcthoneypot_field') !== '') {
                return response()->json(['error' => 'Spam detected.'], 403);
            }

            $isSpam = $this->detectSpam($request->input('email'), $request->input('message'));

            if ($isSpam) {
                return response()->json(['error' => 'Spam detected.'], 403);
            }
            $set = new \App\Model\Common\Setting();
            $set = $set->findOrFail(1);

            $template_type = TemplateType::where('name', 'contact_us')->value('id');
            $template = Template::where('type', $template_type)->first();
            $replace = [
                'name' => $request->input('conName'),
                'email' => $request->input('email'),
                'message' => $request->input('conmessage'),
                'mobile' => $request->input('country_code').' '.$request->input('Mobile'),
                'ip_address' => $request->ip(),
                'title' => $set->title,
                'request_url' => request()->fullUrl(),
                'contact' => $contact['contact'],
                'logo' => $contact['logo'],
                'reply_email' => $request->input('email'),

            ];
            $type = '';

            if ($template) {
                $type_id = $template->type;
                $temp_type = new \App\Model\Common\TemplateType();
                $type = $temp_type->where('id', $type_id)->first()->name;
            }

            if (emailSendingStatus()) {
                $mail = new \App\Http\Controllers\Common\PhpMailController();
                $mail->SendEmail($set->email, $set->company_email, $template->data, $template->name, $replace, $type);
            }

            return response()->json(['message' => 'Your message was sent successfully. Thanks.'], 200);
        } catch (\Exception $ex) {
            return response()->json(['error' => $ex->getMessage()], 500);
        }
    }

    private function detectSpam($email, $message)
    {
        if ($this->containsExcessivePunctuation($message) || $this->containsExcessiveCaps($message) || $this->containsSpamKeywords($message)) {
            return true;
        }

        return false;
    }

    private function containsExcessivePunctuation($text)
    {
        return (bool) preg_match('/!{5,}/', $text);
    }

    private function containsExcessiveCaps($text)
    {
        $uppercaseCount = preg_match_all('/[A-Z]/', $text);
        $lowercaseCount = preg_match_all('/[a-z]/', $text);
        $totalCharacters = $uppercaseCount + $lowercaseCount;
        if ($totalCharacters > 0) {
            $percentageCaps = ($uppercaseCount / $totalCharacters) * 100;
            if ($percentageCaps > 50) {
                return true;
            }
        }

        return false;
    }

    private function containsSpamKeywords($text)
    {
        $spamKeywords = ['viagra', 'casino', 'lottery', 'free money', 'enlargement', 'promotions'];
        foreach ($spamKeywords as $keyword) {
            if (stripos($text, $keyword) !== false) {
                return true;
            }
        }

        return false;
    }

    public function viewDemoReq()
    {
        try {
            $status = StatusSetting::select('recaptcha_status', 'msg91_status', 'emailverification_status', 'terms')->first();
            $apiKeys = ApiKey::select('nocaptcha_sitekey', 'captcha_secretCheck', 'msg91_auth_key', 'terms_url')->first();

            return view('themes.default1.front.demoForm', compact('status', 'apiKeys'));
        } catch (\Exception $ex) {
            return redirect()->back()->with('fails', $ex->getMessage());
        }
    }

    public function postDemoReq(ContactRequest $request)
    {
        try {
            // Check if the honeypot field is filled
            if ($request->input('honeypot_field') !== '') {
                return response()->json(['error' => 'Spam detected.'], 403);
            }
            $contact = getContactData();
            $isSpam = $this->detectSpam($request->input('demoemail'), $request->input('demomessage'));

            if ($isSpam) {
                return response()->json(['error' => 'Spam detected.'], 403);
            }

            $set = new \App\Model\Common\Setting();
            $set = $set->findOrFail(1);

            $template_type = TemplateType::where('name', 'demo_request')->value('id');
            $template = Template::where('type', $template_type)->first();
            $replace = [
                'name' => $request->input('demoname'),
                'email' => $request->input('demoemail'),
                'message' => $request->input('demomessage'),
                'mobile' => $request->input('country_code').' '.$request->input('Mobile'),
                'ip_address' => $request->ip(),
                'title' => $set->title,
                'request_url' => request()->fullUrl(),
                'contact' => $contact['contact'],
                'logo' => $contact['logo'],
                'reply_email' => $request->input('demoemail'),

            ];
            $type = '';

            if ($template) {
                $type_id = $template->type;
                $temp_type = new \App\Model\Common\TemplateType();
                $type = $temp_type->where('id', $type_id)->first()->name;
            }
            $product = $request->input('product') != 'online' ? $request->input('product') : 'our product ';
            $templatename = $template->name.' '.'for'.' '.$product;

            if (emailSendingStatus()) {
                $mail = new \App\Http\Controllers\Common\PhpMailController();
                $mail->SendEmail($set->email, $set->company_email, $template->data, $templatename, $replace, $type);
            }

            return response()->json(['message' => 'Your message was sent successfully. Thanks.'], 200);
        } catch (\Exception $ex) {
            return response()->json(['error' => $ex->getMessage()], 500);
        }
    }

    public function VewDemoPage()
    {
        try {
            $Demo_page = Demo_page::first();

            return view('themes.default1.common.setting.demo-page', compact('Demo_page'));
        } catch (\Exception $ex) {
            return redirect()->back()->with('fails', $ex->getMessage());
        }
    }

    public function saveDemoPage(Request $request)
    {
        $data = $request->validate([
            'status' => 'required',
        ]);
        $data = [
            'status' => $request->input('status') === 'true' ? 1 : 0,
        ];

        $existingData = Demo_page::first();
        $existingData ? $existingData->update($data) : Demo_page::create($data);

        $message = $existingData ? 'Data updated successfully.' : 'Data created successfully.';

        return redirect()->back()->with('success', $message);
    }
}
