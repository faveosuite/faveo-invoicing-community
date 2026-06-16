<?php

namespace App\Http\Controllers\Front;

use App\ApiKey;
use App\DefaultPage;
use App\Demo_page;
use App\Http\Controllers\Common\PhpMailController;
use App\Http\Controllers\Common\TemplateController;
use App\Http\Controllers\Controller;
use App\Http\Controllers\Product\ProductController;
use App\Http\Requests\Front\ContactRequest;
use App\Http\Requests\Front\PageRequest;
use App\Model\Common\Country;
use App\Model\Common\PricingTemplate;
use App\Model\Common\Setting;
use App\Model\Common\State;
use App\Model\Common\StatusSetting;
use App\Model\Common\TemplateType;
use App\Model\Front\FrontendPage;
use App\Model\Payment\Plan;
use App\Model\Payment\PlanPrice;
use App\Model\Product\Product;
use App\Model\Product\ProductGroup;
use Auth;
use Config;
use DateTime;
use DB;
use Exception;
use Form;
use Illuminate\Contracts\Database\Query\Builder;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Foundation\Application;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Str;
use Lang;
use Logger;
use Throwable;

class PageController extends Controller
{
    public $page;

    public function __construct()
    {
        $this->middleware(['auth', 'admin'], ['except' => ['pageTemplates', 'postDemoReq', 'postContactUs', 'publishedPages', 'pageBySlug', 'contactUsInfo']]);
        $this->middleware('recaptcha:contact')->only('postContactUs');
        $this->middleware('recaptcha:demo')->only('postDemoReq');
        $page = new FrontendPage();
        $this->page = $page;
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

                return back()->with('success', trans('message.saved-successfully'));
            } else {
                return back()->with('fails', trans('message.limit_exceed'));
            }
        } catch (Exception $ex) {
            Logger::exception($ex);

            return back()->with('fails', $ex->getMessage());
        }
    }

    public function update($id, PageRequest $request)
    {
        try {
            $page = $this->page->findOrFail($id);

            $page->fill($request->except('created_at'));

            if ($request->filled('created_at')) {
                $page->created_at = Date::createFromFormat(
                    'm/d/Y',
                    $request->input('created_at')
                );
            }

            $page->save();

            if ($request->filled('default_page_id')) {
                $defaultUrl = $this->page
                    ->where('id', $request->input('default_page_id'))
                    ->value('url');

                DefaultPage::findOrFail(1)->update([
                    'page_id' => $request->input('default_page_id'),
                    'page_url' => $defaultUrl,
                ]);
            } else {
                DefaultPage::findOrFail(1)->update([
                    'page_id' => 1,
                    'page_url' => url('my-invoices'),
                ]);
            }

            return back()->with('success', Lang::get('message.updated-successfully'));
        } catch (Exception $ex) {
            return back()->with('fails', $ex->getMessage());
        }
    }

    public function getPageUrl($slug)
    {
        $productController = new ProductController();
        //  $url = url('/');
        //  $segment = $this->addSegment(['public/pages']);
        $url = url('/');

        $slug = Str::slug($slug, '-');
        echo $url.'/pages'.'/'.$slug;
    }

    public function getSlug($slug)
    {
        $slug = Str::slug($slug, '-');
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
        if ($request->has('slug')) {
            $slug = $request->input('slug');

            return $this->getSlug($slug);
        }

        if ($request->has('url')) {
            $slug = $request->input('url');

            return $this->getPageUrl($slug);
        }
    }

    /**
     * Public: list published pages for the front-end navbar.
     * Returns the hierarchy fields so the SPA can build parent/child menus.
     */
    public function publishedPages()
    {
        try {
            $pages = FrontendPage::where('publish', 1)
                ->select('id', 'name', 'slug', 'url', 'type', 'parent_page_id')
                ->orderBy('created_at', 'asc')
                ->get();

            return successResponse('', $pages);
        } catch (Exception $ex) {
            return errorResponse($ex->getMessage());
        }
    }

    /**
     * Public: fetch a single published page by slug for the SPA page view.
     * Returns null data (200) when not found so the client can show a
     * "page not found" state instead of being redirected.
     */
    public function pageBySlug($slug)
    {
        try {
            $page = FrontendPage::where('slug', $slug)
                ->where('publish', 1)
                ->select('id', 'name', 'slug', 'content', 'type')
                ->first();

            return successResponse('', $page);
        } catch (Exception $ex) {
            return errorResponse($ex->getMessage());
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
                    <b>"./* @scrutinizer ignore-type */Lang::get('message.alert').'!</b> '.
                    /* @scrutinizer ignore-type */
                    Lang::get('message.failed').'
                    <button type=button class=close data-dismiss=alert aria-hidden=true>&times;</button>
                        './* @scrutinizer ignore-type */Lang::get('message.no-record').'
                </div>';
                            //echo \Lang::get('message.no-record') . '  [id=>' . $id . ']';
                        }

                        echo "<div class='alert alert-success alert-dismissable'>
                    <i class='fa fa-ban'></i>

                    <b>"./* @scrutinizer ignore-type */ Lang::get('message.alert').'!</b> '.
                    /* @scrutinizer ignore-type */
                    Lang::get('message.success').'

                    <button type=button class=close data-dismiss=alert aria-hidden=true>&times;</button>
                        './* @scrutinizer ignore-type */Lang::get('message.deleted-successfully').'
                </div>';
                    } else {
                        echo "<div class='alert alert-danger alert-dismissable'>
                    <i class='fa fa-ban'></i>
                    <b>"./* @scrutinizer ignore-type */Lang::get('message.alert').'!</b> '.
                    /* @scrutinizer ignore-type */Lang::get('message.failed').'
                    <button type=button class=close data-dismiss=alert aria-hidden=true>&times;</button>
                        './* @scrutinizer ignore-type */ Lang::get('message.can-not-delete-default-page').'
                </div>';
                    }
                }
            } else {
                echo "<div class='alert alert-danger alert-dismissable'>
                    <i class='fa fa-ban'></i>
                    <b>"./* @scrutinizer ignore-type */Lang::get('message.alert').'!</b> '.
                    /* @scrutinizer ignore-type */Lang::get('message.failed').'
                    <button type=button class=close data-dismiss=alert aria-hidden=true>&times;</button>
                        './* @scrutinizer ignore-type */Lang::get('message.select-a-row').'
                </div>';
                //echo \Lang::get('message.select-a-row');
            }
        } catch (Exception $e) {
            echo "<div class='alert alert-danger alert-dismissable'>
                    <i class='fa fa-ban'></i>
                    <b>"./* @scrutinizer ignore-type */Lang::get('message.alert').'!</b> '.
                    /* @scrutinizer ignore-type */Lang::get('message.failed').'
                    <button type=button class=close data-dismiss=alert aria-hidden=true>&times;</button>
                        '.$e->getMessage().'
                </div>';
        }
    }

    public function getstrikePriceYear($id)
    {
        $countryCheck = true;
        try {
            $cost[0] = 'Free';
            $plans = Plan::where('product', $id)->where('status', 1)->get();
            $product = Product::find($id);
            $prices = [];
            if ($plans->count() > 0) {
                foreach ($plans as $plan) {
                    if ($product->status) {
                        if ($plan->days == 365 || $plan->days == 366) {
                            $currency = userCurrencyAndPrice('', $plan);
                            $offerprice = PlanPrice::where('plan_id', $plan->id)->where('currency', $currency)->value('offer_price');
                            $planDetails = userCurrencyAndPrice('', $plan);

                            $prices[$plan->id][] = ($product->status) ? ($planDetails['plan']->add_price / 12) : $planDetails['plan']->add_price;
                            $prices[$plan->id][] = $planDetails['symbol'];
                            $prices[$plan->id][] = $planDetails['currency'];
                            $prices[$plan->id][] = $plan->id;
                        }
                    } else {
                        $currency = userCurrencyAndPrice('', $plan);
                        $offerprice = PlanPrice::where('plan_id', $plan->id)->where('currency', $currency)->value('offer_price');
                        $planDetails = userCurrencyAndPrice('', $plan);
                        $prices[$plan->id][] = $planDetails['plan']->add_price;
                        $prices[$plan->id][] = $planDetails['symbol'];
                        $prices[$plan->id][] = $planDetails['currency'];
                        $prices[$plan->id][] = $plan->id;
                    }

                    if (isset($prices[$plan->id]) && ! empty($prices[$plan->id])) {
                        if (isset($offerprice) && $offerprice != '' && $offerprice != null) {
                            $prices[$plan->id][0] -= ($offerprice / 100) * $prices[$plan->id][0];
                        }

                        $format = currencyFormat(min([$prices[$plan->id][0]]), $code = $prices[$plan->id][2]);
                        $finalPrice = str_replace($prices[$plan->id][1], '', $format);
                        $cost[$plan->id] = '<span class="price-unit striked hide_custom" id="'.$prices[$plan->id][3].'">'.$prices[$plan->id][1].$finalPrice.'</span>';
                    }
                }
            }

            if (count($cost) > 1) {
                unset($cost[0]);
            }

            return $cost;
        } catch (Exception $ex) {
            throw $ex;
        }
    }

    public function transformTemplate($type, $data, $trasform = [])
    {
        $config = Config::get("transform.$type");
        $result = '';

        // Iterate using the original transform array to preserve product IDs as keys
        foreach ($trasform as $productId => $trans) {
            $mappedArray = $this->checkConfigKey($config, $trans);
            $array1 = $this->keyArray($mappedArray);
            $array2 = $this->valueArray($mappedArray);

            // Use product ID directly instead of looking up by name
            $id = $productId;
            $product = Product::find($id);
            $data = $product->highlight ? PricingTemplate::findorFail(1)->data : PricingTemplate::findorFail(2)->data;
            $offerprice = $this->getOfferprice($id);
            $description = self::getPriceDescription($id);
            $month_offer_price = $offerprice['30_days'] ?? null;
            $year_offer_price = $offerprice['365_days'] ?? null;

            if ($product->add_to_contact == 1) {
                $data = str_replace('{{strike-price}}', '', $data);
                $data = str_replace('{{strike-priceyear}}', '', $data);
                $data = str_replace('{{price}}', 'Custom Pricing', $data);
                $data = str_replace('{{price-year}}', 'Custom Pricing', $data);
            }

            if ($month_offer_price === '' || $month_offer_price === null) {
                $data = str_replace('{{strike-price}}', '', $data);
            }

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
                $offerpriceyearKeys = array_keys($offerpriceYear);
                $strikePrice = $this->YearlyAmountForOffer($id);
                $strikePriceKeys = array_keys($strikePrice);
                $data = str_replace('{{price}}', $offerprice, $data);
                if ($month_offer_price !== '' && $month_offer_price !== null) {
                    $data = str_replace('{{strike-price}}', $array2[1] ?? '', $data);
                }

                if (count($offerpriceyearKeys) > 1) {
                    $data = str_replace('{{price-year}}', implode(' ', $offerpriceYear), $data);
                } else {
                    $data = str_replace('{{price-year}}', $offerpriceYear[$offerpriceyearKeys[0]], $data);
                }

                if ($year_offer_price !== '' && $year_offer_price !== null) {
                    if (count($strikePriceKeys) > 1) {
                        $data = str_replace('{{strike-priceyear}}', implode(' ', $strikePrice), $data);
                    } else {
                        $data = str_replace('{{strike-priceyear}}', $strikePrice[$strikePriceKeys[0]], $data);
                    }
                }
            }

            $result .= str_replace($array1, $array2, $data);
        }

        return $result;
    }

    public function transform($type, $data, $trasform = [])
    {
        $config = Config::get("transform.$type");
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
            $plans = Plan::where('product', $id)->where('status', 1)->get();

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
                            $price -= ($offerprice / 100) * $price;
                        }

                        $prices[] = $price;
                        $prices[] = $symbol;
                        $prices[] = $currency;
                    }
                }

                if (! empty($prices)) {
                    $format = currencyFormat(min([$prices[0]]), $code = $prices[2]);
                    $finalPrice = str_replace($prices[1], '', $format);
                    $cost = '<span class="price-unit">'.$prices[1].'</span>'.$finalPrice;
                }
            }

            return $cost;
        } catch (Exception $ex) {
            throw $ex;
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
    public function pageTemplates(?int $templateid = null, int $group = 0)
    {
        $group = ProductGroup::findOrFail($group);
        try {
            $headline = $group->headline;
            $tagline = $group->tagline;
            $currencyAndSymbol = '';
            if (! Auth::user()) {
                $location = getLocation();
                $country = findCountryByGeoip($location['iso_code']);
                $currencyAndSymbol = getCurrencyForClient($country);
            }

            if (Auth::user()) {
                $country = DB::table('users')->where('id', Auth::user()->id)->value('country');
                $currencyAndSymbol = getCurrencyForClient($country);
            }

            $productsRelatedToGroup = Product::with([
                'planRelation' => function ($query) use ($currencyAndSymbol): void {
                    $query->where('days', '!=', 14)
                    ->with(['planPrice' => function ($priceQuery) use ($currencyAndSymbol): void {
                        $priceQuery->where('currency', $currencyAndSymbol);
                    }]);
                },
            ])
                ->where('group', $group->id)
                ->where('hidden', '!=', 1)
                ->whereHas('planRelation', function (Builder $query) use ($currencyAndSymbol): void {
                    $query->where('days', '!=', 14)
                    ->whereHas('planPrice', function (Builder $priceQuery) use ($currencyAndSymbol): void {
                        $priceQuery->where('currency', $currencyAndSymbol);
                    });
                })
            ->where(function (Builder $query) use ($currencyAndSymbol): void {
                $query->where('status', '!=', 1)
                    ->orWhere(function (Builder $activeQuery) use ($currencyAndSymbol): void {
                        $activeQuery->where('status', 1)
                            ->whereHas('planRelation', function (Builder $q) use ($currencyAndSymbol): void {
                                $q->whereIn('days', [30, 31])
                                    ->whereHas('planPrice', fn (Builder $pq) => $pq->where('currency', $currencyAndSymbol));
                            })
                            ->whereHas('planRelation', function (Builder $q) use ($currencyAndSymbol): void {
                                $q->whereIn('days', [365, 366])
                                    ->whereHas('planPrice', fn (Builder $pq) => $pq->where('currency', $currencyAndSymbol));
                            });
                    });
            })
                ->orderBy('id')
                ->get();

            $productsRelatedToGroup = $productsRelatedToGroup->sortBy(fn ($product) => $product->planRelation
                ->flatMap(fn ($plan) => $plan->planPrice)
                ->pluck('add_price')
                ->filter(fn ($v) => $v !== null)
                ->min() ?? PHP_INT_MAX)->values();

            $trasform = [];
            $templates = $this->getTemplateOne($productsRelatedToGroup, $trasform);
            if (empty($templates)) {
                $templates = Lang::get('message.empty_group');
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

            return successResponse('', ['templates' => $templates, 'headline' => $headline, 'tagline' => $tagline, 'description' => $description, 'status' => $status]);
//            return view('themes.default1.common.template.shoppingcart', compact('templates', 'headline', 'tagline', 'description', 'status'));
        } catch (Exception $ex) {
            return errorResponse($ex->getMessage());
        }
    }

    /**
     * This function returns to the contact us page.
     *
     * @param
     * @param
     * @return Factory|View|Application|RedirectResponse
     *
     * @throws
     */
    public function contactUsInfo()
    {
        try {
            $set = Setting::findOrFail(1);
            $address = preg_replace("/^\R+|\R+\z/", '', (string) $set->address);
            $state = State::where('country_code', $set->country)->where('iso2', $set->state)->value('state_subdivision_name');
            $country = Country::where('country_code_char2', $set->country)->value('country_name');
            $apiKeys = ApiKey::select('nocaptcha_sitekey', 'captcha_secretCheck')->first();
            $status = StatusSetting::select('msg91_status')->first();

            return successResponse('', [
                'address' => $address,
                'city' => $set->city,
                'state' => $state,
                'country' => $country,
                'zip' => $set->zip,
                'phone_code' => $set->phone_code,
                'phone' => $set->phone,
                'company_email' => $set->company_email,
                'recaptcha_key' => $apiKeys->nocaptcha_sitekey ?? null,
                'msg91_status' => (bool) ($status->msg91_status ?? false),
            ]);
        } catch (Exception $ex) {
            return errorResponse($ex->getMessage());
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
            $highlightedProducts = Product::whereIn('id', $helpdesk_products->pluck('id'))
                ->pluck('highlight', 'id')
                ->toArray();
            foreach ($helpdesk_products as $product) {
                $productId = $product->id;
                $productName = $product->name;
                $highlight = $highlightedProducts[$productId] ?? false;
                $orderButton = $highlight ? 'btn-primary' : 'btn-dark';

                $trasform[$productId] = [
                    'id' => $productId,
                    'price' => $temp_controller->leastAmount($productId),
                    'price-year' => $this->YearlyAmount($productId),
                    'price-description' => $this->getPriceDescription($productId),
                    'pricemonth-description' => $this->getmonthPriceDescription($productId),
                    'strike-price' => $temp_controller->leastAmount($productId),
                    'strike-priceyear' => $this->YearlyAmount($productId),
                    'name' => $productName,
                    'feature' => $product->description,
                    'product_description' => $product->short_description,
                    'subscription' => $product->type == 4 ? '' : $temp_controller->plans($product->shoping_cart_link, $productId),
                    'url' => $this->generateProductUrl($product, $orderButton, $highlight),
                ];
            }

            $data = PricingTemplate::findOrFail(1)->data;

            return $trasform;
//            return $this->transformTemplate('cart', $data, $trasform);
        } catch (Exception $ex) {
            return back()->with('fails', $ex->getMessage());
        }
    }

    private function generateProductUrl($product, $orderButton, $highlight)
    {
        if ($product->add_to_contact != 1) {
            if (in_array($product->id, cloudPopupProducts())) {
//                return '<button class="btn '.$orderButton.' btn-modern buttonsale" data-toggle="modal" data-target="#tenancy" data-mydata="'.$product->id.'">
//                                <span style="white-space: nowrap;">'.__('message.order_now').'</span>
//                            </button>';
                //for vue
                return['class' => $orderButton, 'product_id' => $product->id, 'type' => 'cloud', 'button' => __('message.order_now')];
            } elseif ($product->status) {
//                return '
//    <button type="button"
//        class="btn '.$orderButton.' btn-modern buttonsale api-order-btn"
//        data-product="'.$product->id.'">
//        '.__('message.order_now').'
//    </button>
                //';
                //For vue when product status is one different process takes place in store
                return['class' => $orderButton, 'product_id' => $product->id, 'type' => 'cloud', 'button' => __('message.order_now')];
            } else {
                //for vue
                return ['class' => $orderButton, 'type' => 'multioption', 'button' => __('message.order_now')];
//                return '<input type="submit" value="Order Now" class="btn '.$orderButton.' btn-modern buttonsale"></form>';
            }
        } else {
            //for vue
            return ['url' => 'https://www.faveohelpdesk.com/contact-us/', 'button' => __('message.contact_sales'), 'class' => $orderButton, 'type' => 'normal'];
//            return '<a class="btn '.$orderButton.' btn-modern sales buttonsale" href="https://www.faveohelpdesk.com/contact-us/">'.__('message.contact_sales').'</a>';
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
            $planid = Plan::where('product', $id)->where('status', 1)->value('id');
            $price = PlanPrice::where('plan_id', $planid)->value('renew_price');

            $plans = $this->prices($id);
            if ($plans) {
                $plan_form = Form::select('subscription', ['Plans' => $plans], null);
            }

            $form = Form::open(['method' => 'get', 'url' => $url]).
            $plan_form.
            Form::hidden('id', $id);

            return $product['add_to_contact'] == 1 ? '' : $form;
        } catch (Exception $ex) {
            return back()->with('fails', $ex->getMessage());
        }
    }

    public function getPrice($months, $price, $priceDescription, $value, $cost, $currency, $offer, $product)
    {
        $cost *= 12;
        if (isset($offer) && $offer !== '' && $offer !== null) {
            $cost -= $offer / 100 * $cost;
        }

        $price1 = currencyFormat($cost, $code = $currency);
        $price[$value->id] = $months.'  '.$price1.' '.$priceDescription;

        return $price;
    }

    public function prices($id)
    {
        try {
            $plans = Plan::where('product', $id)->where('status', 1)->orderBy('id', 'desc')->get();
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
        } catch (Exception $ex) {
            Logger::exception($ex);

            return back()->with('fails', $ex->getMessage());
        }
    }

    public function getOfferprice(int $productid)
    {
        $plans = Plan::with(['planPrice'])->where('product', $productid)->get();

        $offerprices = [
            '30_days' => null,
            '365_days' => null,
        ];

        foreach ($plans as $plan) {
            $currency = userCurrencyAndPrice('', $plan);

            if (! $currency || ! $plan->planPrice) {
                continue;
            }

            // Get offer_price directly from relation for matching currency
            $offer_price = $plan->planPrice
                ->where('currency', $currency['currency'])
                ->pluck('offer_price')
                ->first();

            if (! $offer_price) {
                continue;
            }

            if (in_array((int) $plan->days, [30, 31])) {
                $offerprices['30_days'] = $offer_price;
            } elseif (in_array((int) $plan->days, [365, 366])) {
                $offerprices['365_days'] = $offer_price;
            }
        }

        return $offerprices;
    }

    public function YearlyAmount($id)
    {
        try {
            $product = Product::find($id);
            $plans = Plan::where('product', $id)->where('status', 1)->get();

            $cost = 'Free';
            $currency = '';

            $priceList = [];

            foreach ($plans as $plan) {
                $planDetails = userCurrencyAndPrice('', $plan);

                if (! $planDetails || ($planDetails['plan']->add_price ?? 0) <= 0) {
                    continue;
                }

                if (in_array($plan->days, [365, 366])) {
                    $price = ($product->status)
                        ? ($planDetails['plan']->add_price / 12)
                        : $planDetails['plan']->add_price;

                    $priceList[] = [
                        'price' => $price,
                        'plan_id' => $plan->id,
                        'currency' => $planDetails['currency'],
                    ];
                } elseif (! $product->status && ! in_array($product->id, cloudPopupProducts())) {
                    $priceList[] = [
                        'price' => $planDetails['plan']->add_price,
                        'plan_id' => $plan->id,
                        'currency' => $planDetails['currency'],
                    ];
                }
            }

            if (! empty($priceList)) {
                usort($priceList, fn ($a, $b) => $a['price'] <=> $b['price']);
                $min = $priceList[0];
                $cost = $this->currencyFormatWithSpan($min['price'], $min['currency'], $min['plan_id']);
            }

            return $cost;
        } catch (Exception $ex) {
            throw $ex;
        }
    }

    public function YearlyAmountForOffer($id)
    {
        $countryCheck = true;
        try {
            $cost[] = 'Free';
            $plans = Plan::where('product', $id)->get();
            $product = Product::find($id);
            $prices = [];

            foreach ($plans as $plan) {
                if ($plan->days == 365 || $plan->days == 366) {
                    $planDetails = userCurrencyAndPrice('', $plan);
                    $prices[$plan->id][] = ($product->status) ? ($planDetails['plan']->add_price / 12) : $planDetails['plan']->add_price;
                    $prices[$plan->id][] = $planDetails['symbol'];
                    $prices[$plan->id][] = $planDetails['currency'];
                } elseif (! $product->status && ! in_array($product->id, cloudPopupProducts())) {
                    $planDetails = userCurrencyAndPrice('', $plan);
                    $prices[$plan->id][] = $planDetails['plan']->add_price;
                    $prices[$plan->id][] = $planDetails['symbol'];
                    $prices[$plan->id][] = $planDetails['currency'];
                }

                if (isset($prices[$plan->id]) && ! empty($prices)) {
                    $format = currencyFormat(min([$prices[$plan->id][0]]), $code = $prices[$plan->id][2]);
                    $finalPrice = str_replace($prices[$plan->id][1], '', $format);
                    $cost[$plan->id] = '<span class="price-unit strike-amount hide_custom" id="'.$plan->id.'">'.$prices[$plan->id][1].$finalPrice.'</span>';
                }
            }

            if (count($cost) > 1) {
                unset($cost[0]);
            }

            return $cost;
        } catch (Exception $ex) {
            throw $ex;
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

            $plans = Plan::where('product', $productid)->where('status', 1)->get();

            if ($plans) {
                foreach ($plans as $plan) {
                    if ($plan->days == 30 || $plan->days == 31) {
                        $description = $plan->planPrice->first();

                        if (is_null($description->add_price) || $description->add_price === '' || $description->add_price == 0) {
                            $priceDescription = 'free';
                        } else {
                            $priceDescription = $description->no_of_agents ? 'per month for <strong>'.' '.$description->no_of_agents.' '.'agent</strong>' : 'per month';
                            //for vue
//                            $priceDescription = $description->no_of_agents?$description->no_of_agents:'per month';
                        }

                        break;
                    }
                }
            }

            return $priceDescription;
        } catch (Exception $ex) {
            Logger::exception($ex);

            return back()->with('fails', $ex->getMessage());
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
                        ->where('status', 1)
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
        } catch (Exception $ex) {
            Logger::exception($ex);

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
        foreach ($array as $value) {
            $result[] = $value;
        }

        return $result;
    }

    public function postContactUs(ContactRequest $request)
    {
        try {
            $contact = getContactData();

            $isSpam = $this->detectSpam($request->input('email'), $request->input('message'));

            if ($isSpam) {
                return response()->json(['error' => 'Spam detected.'], 403);
            }

            $set = new Setting();
            $set = $set->findOrFail(1);

            $template = TemplateType::getSelectedTemplate('contact_us');
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
            $type = $template?->type()->value('name') ?? '';

            if (emailSendingStatus()) {
                $mail = new PhpMailController();
                $mail->SendEmail($set->email, $set->company_email, $template->data, $template->name, $template->type()->value('name'), $replace, $type);
            }

            return response()->json(['message' => __('message.message_sent_successfully_400')], 200);
        } catch (Exception $ex) {
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
        return (bool) preg_match('/!{5,}/', (string) $text);
    }

    private function containsExcessiveCaps($text)
    {
        $uppercaseCount = preg_match_all('/[A-Z]/', (string) $text);
        $lowercaseCount = preg_match_all('/[a-z]/', (string) $text);
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

        return array_any($spamKeywords, fn ($keyword) => stripos((string) $text, (string) $keyword) !== false);
    }

    public function postDemoReq(ContactRequest $request)
    {
        try {
            $contact = getContactData();
            $isSpam = $this->detectSpam($request->input('demoemail'), $request->input('demomessage'));

            if ($isSpam) {
                return response()->json(['error' => 'Spam detected.'], 403);
            }

            $set = new Setting();
            $set = $set->findOrFail(1);

            $template = TemplateType::getSelectedTemplate('demo_request');
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
            $type = $template?->type()->value('name') ?? '';
            $product = $request->input('product') != 'online' ? $request->input('product') : 'our product ';
            $templatename = $template->name.' '.'for'.' '.$product;

            if (emailSendingStatus()) {
                $mail = new PhpMailController();
                $mail->SendEmail($set->email, $set->company_email, $template->data, $templatename, $template->type()->value('name'), $replace, $type);
            }

            return successResponse(__('message.message_sent_successfully_400'));
        } catch (Exception $ex) {
            return errorResponse($ex->getMessage());
        }
    }

    public function getDemoStatus()
    {
        $demo = Demo_page::first();

        return successResponse('', [
            'status' => $demo ? (bool) $demo->status : false,
        ]);
    }

    public function saveDemoPage(Request $request)
    {
        $request->validate([
            'status' => ['required', 'boolean'],
        ]);

        Demo_page::updateOrCreate([],
            ['status' => $request->boolean('status')]
        );

        return successResponse(__('message.data_updated_successfully'));
    }

    public function getAllPages(Request $request)
    {
        $searchQuery = $request->input('search-query', '');
        $sortOrder = $request->input('sort-order', 'asc');
        $sortField = $request->input('sort-field', 'created_at');
        $limit = $request->input('limit', 10);

        $pages = FrontendPage::select('id', 'name', 'url', 'created_at')
            ->when($searchQuery, function ($query) use ($searchQuery): void {
                $query->where(function ($q) use ($searchQuery): void {
                    $q->where('name', 'like', "%{$searchQuery}%")
                        ->orWhere('url', 'like', "%{$searchQuery}%");
                });
            })
            ->orderBy($sortField, $sortOrder)
            ->simplePaginate($limit);

        return successResponse('', $pages);
    }

    public function deleteBulkPages(Request $request)
    {
        $ids = $request->input('page_ids', []);

        $defaultPageId = DefaultPage::pluck('page_id')->first();

        if (empty($ids)) {
            return errorResponse(__('message.select-a-row'));
        }

        if (in_array($defaultPageId, $ids)) {
            return errorResponse(__('message.can-not-delete-default-page'));
        }

        FrontendPage::whereIn('id', $ids)->where('id', '!=', $defaultPageId)->delete();

        return successResponse(__('message.deleted-successfully'));
    }

    public function currencyFormatWithSpan($amount, $currency, $id = null)
    {
        // number only
        $formatted = currencyFormat($amount, $currency, false);

        // formatted with symbol (actual placement)
        $withSymbol = currencyFormat($amount, $currency);

        // extract symbol by removing number part
        $symbol = trim(str_replace($formatted, '', $withSymbol));

        // prepare span
        $span = '<span class="price-unit"'.($id ? ' id="'.$id.'"' : '').'>'.$symbol.'</span>';

        // rebuild keeping correct placement
        if (str_starts_with((string) $withSymbol, $symbol)) {
            // symbol is in front
            return $span.$formatted;
        }

        // symbol at the end
        return $formatted.$span;
    }

    public function createPage(Request $request)
    {
        try {
            $pagesCount = FrontendPage::count();
            if ($pagesCount >= 3) {
                return errorResponse(__('message.limit_exceed'));
            }

            $url = $request->input('url');
            if ($request->input('type') === 'contactus') {
                $url = url('/contact-us');
            }

            $page = FrontendPage::create([
                'name' => $request->input('name'),
                'publish' => $request->input('publish', 0),
                'slug' => $request->input('slug'),
                'url' => $url,
                'parent_page_id' => $request->input('parent_page_id') ?? 0,
                'type' => $request->input('type'),
                'content' => $request->input('content'),
            ]);

            return successResponse(__('message.saved-successfully'), $page);
        } catch (Exception $ex) {
            return errorResponse($ex->getMessage());
        }
    }

    public function getPage(Request $request, $pageId)
    {
        try {
            $page = FrontendPage::with('parent:id,name')->findOrFail($pageId);
            $defaultPageId = DefaultPage::value('page_id');
            $data = $page->toArray();
            $data['is_default'] = (int) $page->id === (int) $defaultPageId;

            return successResponse('', $data);
        } catch (Exception $ex) {
            return errorResponse($ex->getMessage());
        }
    }

    public function updatePage(Request $request, $pageId)
    {
        try {
            $page = FrontendPage::findOrFail($pageId);

            // Fill except created_at
            $page->fill($request->except('created_at'));

            // parent_page_id is NOT NULL in the schema; default to 0 (no parent)
            if ($page->parent_page_id === null) {
                $page->parent_page_id = 0;
            }

            // Handle created_at if provided and valid
            if ($request->filled('created_at')) {
                $date = DateTime::createFromFormat('m/d/Y', $request->input('created_at'));
                if ($date) {
                    $page->created_at = $date->format('Y-m-d H:i:s');
                }
            }

            $page->save();

            $defaultPageId = $request->input('default_page_id');
            $defaultUrl = $defaultPageId
                ? FrontendPage::where('id', $defaultPageId)->value('url')
                : url('my-invoices');

            DefaultPage::findOrFail(1)->update([
                'page_id' => $defaultPageId ?? 1,
                'page_url' => $defaultUrl,
            ]);

            return successResponse(__('message.updated-successfully'), $page);
        } catch (Throwable $ex) {
            return errorResponse($ex->getMessage());
        }
    }
}
