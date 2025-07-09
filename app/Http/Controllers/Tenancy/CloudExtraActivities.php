<?php

namespace App\Http\Controllers\Tenancy;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Front\CartController;
use App\Http\Controllers\License\LicenseController;
use App\Http\Controllers\Order\RenewController;
use App\Model\CloudDataCenters;
use App\Model\Common\Country;
use App\Model\Common\FaveoCloud;
use App\Model\Common\State;
use App\Model\Order\InstallationDetail;
use App\Model\Order\Invoice;
use App\Model\Order\Order;
use App\Model\Order\Payment;
use App\Model\Payment\Plan;
use App\Model\Payment\PlanPrice;
use App\Model\Product\CloudProducts;
use App\Model\Product\Product;
use App\Model\Product\Subscription;
use App\ThirdPartyApp;
use App\User;
use Carbon\Carbon;
use GuzzleHttp\Client;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CloudExtraActivities extends Controller
{
    public function __construct(Client $client, FaveoCloud $cloud)
    {
        $this->client = $client;
        $this->cloud = $cloud->first();

        $this->middleware('auth', ['except' => ['verifyThirdPartyToken', 'storeTenantTillPurchase']]);
    }

    /**
     *  This function returns if there are any active agents before we change the number of agents.
     *
     * @param  $numberOfAgents
     * @param  $domain
     * @return JsonResponse
     *
     * @throws
     */
    private function checktheAgent($numberOfAgents, $domain)
    {
        $client = new Client([]);
        $data = ['number_of_agents' => $numberOfAgents];
        $response = $client->request(
            'POST',
            'https://'.$domain.'/api/agent-check', ['form_params' => $data]
        );
        $response = explode('{', (string) $response->getBody());

        $response = array_first($response);

        return json_decode($response);
    }

    /**
     *  This function is used to autofill a field(company) and to change the format.
     *
     * @param
     * @return JsonResponse
     *
     * @throws
     */
    public function domainCloudAutofill()
    {
        // Fetch the company value from the database
        $company = User::where('id', \Auth::user()->id)->value('company');

        // Convert spaces to underscores
        $company = str_replace(' ', '', $company);

        // Convert uppercase letters to lowercase
        $company = substr(strtolower($company), 0, 28);

        // Output the modified company value
        return response()->json(['data' => $company]);
    }

    /**
     *  This function checks if the installation path is present or not, and returns installation the path if present.
     *
     * @param  Request  $request
     * @return JsonResponse
     *
     * @throws
     */
    public function orderDomainCloudAutofill(Request $request)
    {
        // Output the modified domain value
        $installtion_path = InstallationDetail::where('order_id', $request->orderId)->where('installation_path', '!=', cloudCentralDomain())->latest()->value('installation_path');
        if (! empty($installtion_path)) {
            return response()->json(['data' => $installtion_path]);
        }

        return response()->json(['data' => '']);
    }

    /**
     *  This function provides upgraded cost when we change the plan .
     *
     * @param  Request  $request
     * @return array
     *
     * @throws
     */
    public function getUpgradeCost(Request $request)
    {
        try {
            $planId = $request->input('plan');
            $agents = $request->input('agents');

            $orderId = $request->input('orderId');

            $plan = Plan::find($planId);
            $planDetails = userCurrencyAndPrice(\Auth::user()->id, $plan);

            $actualPrice = $planDetails['plan']->add_price * $agents;

            $oldLicense = Order::where('id', $orderId)->latest()->value('serial_key');

            return $this->getThePaymentCalculationUpgradeDowngradeDisplay($agents, $oldLicense, $orderId, $planId, $actualPrice, $planDetails['plan']->add_price);
        } catch (\Exception $ex) {
            app('log')->error($ex->getMessage());

            return ['price_to_be_paid' => 'NaN', 'discount' => 'NaN', 'currency' => 'NaN'];
        }
    }

    /**
     *  This function is used to change the domain.
     *
     * @param  Request  $request
     * @return
     *
     * @throws
     */
    public function changeDomain(Request $request)
    {
        try {
            $this->validate($request, [
                'currentDomain' => 'required',
                'newDomain' => 'required',
            ],
                [
                    'currentDomain.required' => __('validation.current_domain_required'),
                    'newDomain.required' => __('validation.new_domain_required'),
                ]);
            $keys = ThirdPartyApp::where('app_name', 'faveo_app_key')->select('app_key', 'app_secret')->first();
            $token = str_random(32);
            $newDomain = $request->get('newDomain');
            $currentDomain = $request->get('currentDomain');
            if (! filter_var($newDomain, FILTER_VALIDATE_DOMAIN, FILTER_FLAG_HOSTNAME)) {
                return errorResponse(trans('message.not_allowed_domain'));
            }
            if (strpos($newDomain, '.'.cloudSubDomain()) !== false) {
                return errorResponse(trans('message.cloud_not_allowed'));
            }
            if ($newDomain === $currentDomain) {
                return errorResponse(trans('message.nothing_changed'));
            }
            $data = ['currentDomain' => $currentDomain, 'newDomain' => $newDomain, 'lic_code' => $request->get('lic_code'), 'product_id' => $request->product_id, 'app_key' => $keys->app_key, 'token' => $token, 'timestamp' => time()];
            $dns_record = dns_get_record($newDomain, DNS_CNAME);
            if (! strpos($newDomain, cloudSubDomain())) {
                if (empty($dns_record) || ! in_array(cloudSubDomain(), array_column($dns_record, 'target'))) {
                    return errorResponse(trans('message.cname'));
                }
            }
            $encodedData = http_build_query($data);
            $hashedSignature = hash_hmac('sha256', $encodedData, $keys->app_secret);
            $client = new Client([]);
            $response = $client->request(
                'POST',
                $this->cloud->cloud_central_domain.'/changeDomain', ['form_params' => $data, 'headers' => ['signature' => $hashedSignature]]
            );
            $response = explode('{', (string) $response->getBody());

            $response = '{'.$response[1];

            $result = json_decode($response);

            $this->jobsForCloudDomain($newDomain, $currentDomain);

            return successResponse(trans('message.cloud_domain_change'));
        } catch(\Exception $e) {
            app('log')->error($e->getMessage());

            return errorResponse(trans('message.wrong_domain'));
        }
    }

    //No need to worry about performance because of if else,
    // these are just triggers that wait for no response
    private function jobsForCloudDomain($newDomain, $currentDomain)
    {
        $client = new Client([]);

        $client->request('GET', env('CLOUD_JOB_URL'), [
            'auth' => [env('CLOUD_USER'), env('CLOUD_AUTH')],
            'query' => [
                'token' => env('CLOUD_OAUTH_TOKEN'),
                'domain' => $newDomain,
            ],
        ]);
    }

    /**
     *  This function is used to change number of agents of cloud product.
     *
     * @param  Request  $request
     * @return string
     *
     * @throws
     */
    public function agentAlteration(Request $request)
    {
        try {
            $newAgents = $request->newAgents;
            if (empty($newAgents)) {
                return errorResponse(trans('message.agent_zero'));
            }
            $orderId = $request->input('orderId');
            $installation_path = InstallationDetail::where('order_id', $orderId)->where('installation_path', '!=', cloudCentralDomain())->latest()->value('installation_path');
            if (empty($installation_path)) {
                return errorResponse(trans('message.installation_path_not_found'));
            }
            $product_id = $request->product_id;

            if ($this->checktheAgent($newAgents, $installation_path)) {
                return errorResponse(trans('message.agent_reduce'));
            }

            $oldLicense = Order::where('id', $orderId)->latest()->value('serial_key');

            $items = $this->getThePaymentCalculation($newAgents, $oldLicense, $orderId);
            $invoice = (new RenewController())->renewBySubId($request->subId, $items['planId'], '', $items['price'], '', false, $newAgents);

            if ($invoice) {
                \Session::put('AgentAlteration', $request->subId);
                \Session::put('newAgents', $newAgents);
                \Session::put('orderId', $orderId);
                \Session::put('installation_path', $installation_path);
                \Session::put('product_id', $product_id);
                \Session::put('oldLicense', $oldLicense);

                return url('paynow/'.$invoice->invoice_id);
            }
        } catch(\Exception $e) {
            app('log')->error($e->getMessage());

            return errorResponse(trans('message.wrong_agents'));
        }
    }

    /**
     *  This function is used to get upgrade and downgrade plans value.
     *
     * @param  Request  $request
     * @return JsonResponse|string
     *
     * @throws
     */
    public function upgradeDowngradeCloud(Request $request)
    {
        try {
            $planId = $request->id;
            $agents = $request->agents;
            $orderId = $request->orderId;
            \Session::put('creditOrderId', $orderId);
            $oldLicense = Order::where('id', $orderId)->latest()->value('serial_key');
            $installation_path = InstallationDetail::where('order_id', $orderId)->where('installation_path', '!=', cloudCentralDomain())->latest()->value('installation_path');
//            if (empty($installation_path)) {
//                return errorResponse(trans('message.installation_path_not_found'));
//            }
            \Session::put('upgradeInstallationPath', $installation_path);

            $items = $this->getThePaymentCalculationUpgradeDowngrade($agents, $oldLicense, $orderId, $planId);

            \Cart::add($items); //Add Items To the Cart Collection
            \Session::put('upgradeDowngradeProduct', \Auth::user()->id);
            \Session::put('upgradeOldLicense', $oldLicense);
            \Session::put('upgradeorderId', $orderId);

            return response()->json(['redirectTo' => url('/checkout')]);
        } catch(\Exception $e) {
            app('log')->error($e->getMessage());

            return errorResponse(trans('message.wrong_upgrade'));
        }
    }

    /**
     *  This function is used for the calculation when we do agent alteration.
     *
     * @param  $newAgents
     * @param  $oldAgents
     * @param  $orderId
     * @param  $planId
     * @return array|\Illuminate\Http\Response
     *
     * @throws
     */
    private function getThePaymentCalculation($newAgents, $oldAgents, $orderId, $planId = null)
    {
        try {
            \Session::forget('upgradeDowngradeProduct');
            \Session::forget('upgradeOldLicense');
            \Session::forget('upgradeInstallationPath');
            \Session::forget('upgradeorderId');
            \Session::forget('upgradeProductId');
            \Session::forget('upgradeNewActiveOrder');
            \Session::forget('increase-decrease-days-dont-cloud');
            \Session::forget('increase-decrease-days');
            \Session::forget('priceRemaining');

            if (is_null($planId)) {
                $planId = Subscription::where('order_id', $orderId)->value('plan_id');
            }
            $product_id = Plan::where('id', $planId)->pluck('product')->first();
            $planDays = Plan::where('id', $planId)->pluck('days')->first();
            $product = Product::find($product_id);
            $plan = $product->planRelation->find($planId);
            $currency = userCurrencyAndPrice('', $plan);
            $ends_at = Subscription::where('order_id', $orderId)->value('ends_at');
            $countryid = \App\Model\Common\Country::where('country_code_char2', \Auth::user()->country)->value('country_id');
            $base_price = PlanPrice::where('plan_id', $planId)->where('currency', $currency['currency'])->where('country_id', $countryid)->value('add_price');
            if (! $base_price) {
                $base_price = PlanPrice::where('plan_id', $planId)->where('currency', $currency['currency'])->where('country_id', 0)->value('add_price');
            }
            $oldAgents = substr($oldAgents, 12, 16);
            if ($newAgents > $oldAgents) {
                $price = $this->newAgentgreaterthenOld($ends_at, $base_price, $newAgents, $oldAgents, $planDays);
            } else {
                $price = $this->newAgentlessthenOld($ends_at, $base_price, $newAgents, $oldAgents, $planDays);
            }
            $items = ['id' => $product_id, 'name' => $product->name, 'price' => round($price), 'planId' => $planId,
                'quantity' => 1, 'attributes' => ['currency' => $currency['currency'], 'symbol' => $currency['symbol'], 'agents' => $newAgents], 'associatedModel' => $product];

            return $items;
        } catch(\Exception $e) {
            app('log')->error($e->getMessage());

            return response(['status' => false, 'message' => trans('message.wrong_agents')]);
        }
    }

    /**
     *  This function is used for the calculation when new agents are greater than older agents.
     *
     * @param  $newAgents
     * @param  $oldAgents
     * @param  $planDays
     * @param  $base_price
     * @param  $ends_at
     * @return int
     *
     * @throws
     */
    private function newAgentgreaterthenOld($ends_at, $base_price, $newAgents, $oldAgents, $planDays)
    {
        if (Carbon::now() >= $ends_at) {
            $price = $base_price * $newAgents;
            \Session::put('agentIncreaseDate', 'do-it');
        } else {
            $agentsAdded = $newAgents - $oldAgents;
            $pricePerDay = $base_price / $planDays;
            $futureDateTime = Carbon::createFromFormat('Y-m-d H:i:s', $ends_at);
            $currentDateTime = Carbon::now();
            $daysRemain = $futureDateTime->diffInDays($currentDateTime);
            $pricePerThatAgent = $pricePerDay * $daysRemain;
            $price = $agentsAdded * $pricePerThatAgent;
        }

        return $price;
    }

    /**
     *  This function is used for the calculation when new agents are less than older agents.
     *
     * @param  $newAgents
     * @param  $oldAgents
     * @param  $planDays
     * @param  $base_price
     * @param  $ends_at
     * @return int
     *
     * @throws
     */
    private function newAgentlessthenOld($ends_at, $base_price, $newAgents, $oldAgents, $planDays)
    {
        if (Carbon::now() >= $ends_at) {
            $price = $base_price * $newAgents;
            \Session::put('agentIncreaseDate', 'do-it');
        } else {
            $futureDateTime = Carbon::createFromFormat('Y-m-d H:i:s', $ends_at);
            $currentDateTime = Carbon::now();
            $daysRemain = $futureDateTime->diffInDays($currentDateTime);
            $priceForNewAgents = $base_price * $newAgents;
            $priceForOldAgents = $base_price * $oldAgents;
            $pricePerDayForNewAgents = $priceForNewAgents / $planDays;
            $pricePerDayForOldAgents = $priceForOldAgents / $planDays;
            $priceRemaining = $pricePerDayForOldAgents * $daysRemain;
            $priceToBePaid = $pricePerDayForNewAgents * $daysRemain;
            $discount = $priceRemaining - $priceToBePaid;
            if ($priceToBePaid > $priceRemaining) {
                $price = $priceToBePaid - $priceRemaining;
            } else {
                $price = 0;
            }
        }

        return $price;
    }

    /**
     *  This function is used for the calculation when change the plan.
     *
     * @param  $newAgents
     * @param  $oldAgents
     * @param  $orderId
     * @param  $planIdNew
     * @return array|\Illuminate\Http\Response
     *
     * @throws
     */
    private function getThePaymentCalculationUpgradeDowngrade($newAgents, $oldAgents, $orderId, $planIdNew)
    {
        try {
            \Session::forget('AgentAlteration');
            \Session::forget('newAgents');
            \Session::forget('orderId');
            \Session::forget('installation_path');
            \Session::forget('product_id');
            \Session::forget('oldLicense');
            \Session::forget('increase-decrease-days');
            \Session::forget('increase-decrease-days-dont-cloud');
            \Session::forget('discount');
            \Session::forget('nothingLeft');
            $planIdOld = Subscription::where('order_id', $orderId)->value('plan_id');

            $ends_at = Subscription::where('order_id', $orderId)->value('ends_at');
            $oldAgents = substr($oldAgents, 12, 16);

            $product_id_old = Plan::where('id', $planIdOld)->pluck('product')->first();
            $planDaysOld = Plan::where('id', $planIdOld)->pluck('days')->first();
            $productOld = Product::find($product_id_old);
            $planOld = $productOld->planRelation->find($planIdOld);
            $currencyOld = userCurrencyAndPrice('', $planOld);
            $countryid = \App\Model\Common\Country::where('country_code_char2', \Auth::user()->country)->value('country_id');
            $base_priceOld = PlanPrice::where('plan_id', $planIdOld)->where('currency', $currencyOld['currency'])->where('country_id', $countryid)->value('add_price');
            if (! $base_priceOld) {
                $base_priceOld = PlanPrice::where('plan_id', $planIdOld)->where('currency', $currencyOld['currency'])->where('country_id', 0)->value('add_price') * $oldAgents;
            } else {
                $base_priceOld = $base_priceOld * $oldAgents;
            }

            $product_id_new = Plan::where('id', $planIdNew)->pluck('product')->first();
            $planDaysNew = Plan::where('id', $planIdNew)->pluck('days')->first();
            $productNew = Product::find($product_id_new);
            $planNew = $productNew->planRelation->find($planIdNew);
            $currencyNew = userCurrencyAndPrice('', $planNew);
            $base_price_new = PlanPrice::where('plan_id', $planIdNew)->where('currency', $currencyNew['currency'])->where('country_id', $countryid)->value('add_price');
            if (! $base_price_new) {
                $base_price_new = PlanPrice::where('plan_id', $planIdNew)->where('currency', $currencyNew['currency'])->where('country_id', 0)->value('add_price') * $newAgents;
            } else {
                $base_price_new = $base_price_new * $newAgents;
            }

            \Session::put('upgradeProductId', $product_id_new);
            \Session::put('plan', $planIdNew);

            if ($base_price_new > $base_priceOld) {
                $variables = $this->newPriceGreaterThanOld($ends_at, $base_price_new, $planDaysNew, $base_priceOld, $planDaysOld, $orderId);
                $price = $variables['price'];
                $priceRemaining = $variables['priceRemaining'];
                $priceToBePaid = $variables['priceToBePaid'];
            } elseif ($base_price_new == $base_priceOld) {
                $variables = $this->newPriceEqualToOld($ends_at, $base_price_new, $planDaysNew, $planDaysOld, $orderId);
                $price = $variables['price'];
                $priceRemaining = $variables['priceRemaining'];
                $priceToBePaid = $variables['priceToBePaid'];
            } else {
                $variables = $this->newPriceLessThanOld($ends_at, $base_price_new, $base_priceOld, $planDaysNew, $planDaysOld, $orderId);
                $price = $variables['price'];
                $priceRemaining = $variables['priceRemaining'];
                $priceToBePaid = $variables['priceToBePaid'];
            }

            \Session::put('priceRemaining', round($priceRemaining));
            \Session::put('priceToBePaid', round($priceToBePaid));
            $items = ['id' => $product_id_new, 'name' => $productNew->name, 'price' => round(abs($price)), 'planId' => $planIdNew,
                'quantity' => 1, 'attributes' => ['currency' => $currencyNew['currency'], 'symbol' => $currencyNew['symbol'], 'agents' => $newAgents,
                    'priceRemaining' => $priceRemaining, 'priceToBePaid' => $priceToBePaid], 'associatedModel' => $productNew];

            return $items;
        } catch(\Exception $e) {
            app('log')->error($e->getMessage());

            return response(['status' => false, 'message' => trans('message.wrong_upgrade')]);
        }
    }

    /**
     *  This function is used for the calculation when change the plan(when new price is less than old price).
     *
     * @param  $ends_at
     * @param  $base_price_new
     * @param  $base_priceOld
     * @param  $planDaysNew
     * @param  $planDaysOld
     * @param  $orderId
     * @return array
     *
     * @throws
     */
    private function newPriceLessThanOld($ends_at, $base_price_new, $base_priceOld, $planDaysNew, $planDaysOld, $orderId)
    {
        if (Carbon::now() >= $ends_at) {
            $price = $base_price_new;
            $priceRemaining = 0;
            $priceToBePaid = $price;
            \Session::put('increase-decrease-days', $planDaysNew);
        } else {
            $futureDateTime = Carbon::createFromFormat('Y-m-d H:i:s', $ends_at);
            $currentDateTime = Carbon::now();
            $daysRemain = $futureDateTime->diffInDays($currentDateTime);

            $pricePerDayForNewPlan = $base_price_new / $planDaysNew;

            $pricePerDayForOldPlan = $base_priceOld / $planDaysOld;

            if ($planDaysOld !== $planDaysNew) {
                $variables = $this->lessPriceNewDaysNotEqualToOldDays($daysRemain, $planDaysNew, $planDaysOld, $pricePerDayForNewPlan, $pricePerDayForOldPlan, $orderId);
                $price = $variables['price'];
                $priceRemaining = $variables['priceRemaining'];
                $priceToBePaid = $variables['priceToBePaid'];
            } else {
                $variables = $this->lessPriceNewDaysEqualToOldDays($daysRemain, $pricePerDayForNewPlan, $pricePerDayForOldPlan, $orderId);
                $price = $variables['price'];
                $priceRemaining = $variables['priceRemaining'];
                $priceToBePaid = $variables['priceToBePaid'];
            }
        }

        return ['price' => $price, 'priceRemaining' => $priceRemaining, 'priceToBePaid' => $priceToBePaid];
    }

    /**
     *  This function is used for the calculation when the price is less and the new plan days is equal to old plan days.
     *
     * @param  $daysRemain
     * @param  $pricePerDayForNewPlan
     * @param  $pricePerDayForOldPlan
     * @param  $orderId
     * @return array
     *
     * @throws
     */
    private function lessPriceNewDaysEqualToOldDays($daysRemain, $pricePerDayForNewPlan, $pricePerDayForOldPlan, $orderId)
    {
        $priceToBePaid = $pricePerDayForNewPlan * $daysRemain;
        $priceRemaining = $pricePerDayForOldPlan * $daysRemain;
        \Session::put('increase-decrease-days-dont-cloud', $orderId);

        if ($priceToBePaid > $priceRemaining) {
            $price = $priceToBePaid - $priceRemaining;
        } else {
            $discount = $priceRemaining - $priceToBePaid;
            \Session::put('nothingLeft', '0');
            \DB::table('users')->where('id', \Auth::user()->id)->update(['billing_pay_balance' => 1]);
            \Session::put('discount', round($discount));
            $price = $priceToBePaid;
        }

        return ['price' => $price, 'priceRemaining' => $priceRemaining, 'priceToBePaid' => $priceToBePaid];
    }

    /**
     *  This function is used for the calculation when the price is less and the new plan days is not equal to old plan days.
     *
     * @param  $daysRemain
     * @param  $pricePerDayForNewPlan
     * @param  $pricePerDayForOldPlan
     * @param  $orderId
     * @return array
     *
     * @throws
     */
    private function lessPriceNewDaysNotEqualToOldDays($daysRemain, $planDaysNew, $planDaysOld, $pricePerDayForNewPlan, $pricePerDayForOldPlan, $orderId)
    {
        if ($daysRemain <= $planDaysNew && $planDaysOld > $planDaysNew) {
            $priceToBePaid = $pricePerDayForNewPlan * $daysRemain;
            $priceRemaining = $pricePerDayForOldPlan * $daysRemain;
            \Session::put('increase-decrease-days-dont-cloud', $orderId);
        } else {
            $daysRemainNew = $planDaysOld - $daysRemain;
            $daysRemainNewFinal = $planDaysNew - $daysRemainNew;
            $priceToBePaid = $pricePerDayForNewPlan * $daysRemainNewFinal;
            $priceRemaining = $pricePerDayForOldPlan * $daysRemain;
            \Session::put('increase-decrease-days', $daysRemainNewFinal);
        }
        if ($priceToBePaid > $priceRemaining) {
            $price = $priceToBePaid - $priceRemaining;
        } else {
            $discount = $priceRemaining - $priceToBePaid;
            \Session::put('nothingLeft', '0');
            User::where('id', \Auth::user()->id)->update(['billing_pay_balance' => 1]);
            \Session::put('discount', round($discount));
            $price = $priceToBePaid;
        }

        return ['price' => $price, 'priceRemaining' => $priceRemaining, 'priceToBePaid' => $priceToBePaid];
    }

    /**
     *  This function is used for the calculation when the old price is greater than the new plan price.
     *
     * @param  $ends_at
     * @param  $base_price_new
     * @param  $planDaysNew
     * @param  $base_priceOld
     * @param  $planDaysOld
     * @param  $orderId
     * @return array
     *
     * @throws
     */
    private function newPriceGreaterThanOld($ends_at, $base_price_new, $planDaysNew, $base_priceOld, $planDaysOld, $orderId)
    {
        if (Carbon::now() >= $ends_at) {
            $price = $base_price_new;
            $priceRemaining = 0;
            $priceToBePaid = $price;
            \Session::put('increase-decrease-days', $planDaysNew);
        } else {
            $pricePerDayNew = $base_price_new / $planDaysNew; //800
            $pricePerDayOld = $base_priceOld / $planDaysOld; //1600
            $futureDateTime = Carbon::createFromFormat('Y-m-d H:i:s', $ends_at);
            $currentDateTime = Carbon::now();
            $daysRemain = $futureDateTime->diffInDays($currentDateTime);

            if ($planDaysNew !== $planDaysOld) {
                $variables = $this->newPlanDaysNotEqualToOld($planDaysNew, $planDaysOld, $daysRemain, $pricePerDayNew, $pricePerDayOld);
                $price = $variables['price'];
                $priceRemaining = $variables['priceRemaining'];
                $priceToBePaid = $variables['priceToBePaid'];
            } else {
                $variables = $this->newPlanDaysEqualToOld($daysRemain, $pricePerDayNew, $pricePerDayOld, $orderId);
                $price = $variables['price'];
                $priceRemaining = $variables['priceRemaining'];
                $priceToBePaid = $variables['priceToBePaid'];
            }
        }

        return ['price' => $price, 'priceRemaining' => $priceRemaining, 'priceToBePaid' => $priceToBePaid];
    }

    /**
     *  This function is used for the calculation when the new plan days is not equal to old plan days.
     *
     * @param  $planDaysNew
     * @param  $planDaysOld
     * @param  $daysRemain
     * @param  $pricePerDayNew
     * @param  $pricePerDayOld
     * @return array
     *
     * @throws
     */
    private function newPlanDaysNotEqualToOld($planDaysNew, $planDaysOld, $daysRemain, $pricePerDayNew, $pricePerDayOld)
    {
        $daysRemainNew = $planDaysOld - $daysRemain;
        $daysRemainNewFinal = $planDaysNew - $daysRemainNew;
        $pricePerThatAgentNew = $pricePerDayNew * $daysRemainNewFinal;
        $pricePerThatAgentOld = $pricePerDayOld * $daysRemain;
        $price = $pricePerThatAgentNew - $pricePerThatAgentOld;
        $priceRemaining = $pricePerThatAgentOld;
        $priceToBePaid = $pricePerThatAgentNew;
        \Session::put('increase-decrease-days', $daysRemainNewFinal);

        return ['price' => $price, 'priceRemaining' => $priceRemaining, 'priceToBePaid' => $priceToBePaid];
    }

    /**
     *  This function is used for the calculation when the new plan days is equal to old plan days.
     *
     * @param  $daysRemain
     * @param  $pricePerDayNew
     * @param  $pricePerDayOld
     * @param  $orderId
     * @return array
     *
     * @throws
     */
    private function newPlanDaysEqualToOld($daysRemain, $pricePerDayNew, $pricePerDayOld, $orderId)
    {
        $pricePerThatAgentNew = $pricePerDayNew * $daysRemain;
        $pricePerThatAgentOld = $pricePerDayOld * $daysRemain;
        $price = $pricePerThatAgentNew - $pricePerThatAgentOld;
        $priceRemaining = $pricePerThatAgentOld;
        $priceToBePaid = $pricePerThatAgentNew;
        \Session::put('increase-decrease-days-dont-cloud', $orderId);

        return ['price' => $price, 'priceRemaining' => $priceRemaining, 'priceToBePaid' => $priceToBePaid];
    }

    /**
     *  This function is used for the calculation when the old price is greater than the new plan price.
     *
     * @param  $ends_at
     * @param  $base_price_new
     * @param  $planDaysNew
     * @param  $planDaysOld
     * @param  $orderId
     * @return array
     *
     * @throws
     */
    private function newPriceEqualToOld($ends_at, $base_price_new, $planDaysNew, $planDaysOld, $orderId)
    {
        if (Carbon::now() >= $ends_at) {
            $price = $base_price_new;
            $priceRemaining = 0;
            $priceToBePaid = $price;
            \Session::put('increase-decrease-days', $planDaysNew);
        } else {
            $futureDateTime = Carbon::createFromFormat('Y-m-d H:i:s', $ends_at);
            $currentDateTime = Carbon::now();
            $daysRemain = $futureDateTime->diffInDays($currentDateTime);
            $variables = $this->currentDateLessThanEndDate($planDaysNew, $planDaysOld, $daysRemain, $orderId);
            $price = $variables['price'];
            $priceRemaining = $variables['priceRemaining'];
            $priceToBePaid = $variables['priceToBePaid'];
        }

        return ['price' => $price, 'priceRemaining' => $priceRemaining, 'priceToBePaid' => $priceToBePaid];
    }

    /**
     *  This function is used for the calculation when the current date is less than the subscription end date.
     *
     * @param  $daysRemain
     * @param  $planDaysNew
     * @param  $planDaysOld
     * @param  $orderId
     * @return array
     *
     * @throws
     */
    private function currentDateLessThanEndDate($planDaysNew, $planDaysOld, $daysRemain, $orderId)
    {
        if ($planDaysNew !== $planDaysOld) {
            if ($planDaysOld < $planDaysNew) {
                $daysRemainNew = $planDaysOld - $daysRemain;
                $daysRemainNewFinal = $planDaysNew - $daysRemainNew;
                \Session::put('increase-decrease-days', $daysRemainNewFinal);
            }
            if ($planDaysOld > $planDaysNew) {
                if ($daysRemain <= $planDaysNew) {
                    \Session::put('increase-decrease-days', $daysRemain);
                } else {
                    $daysRemainNew = $planDaysOld - $daysRemain;
                    $daysRemainNewFinal = $planDaysNew - $daysRemainNew;
                    \Session::put('increase-decrease-days', $daysRemainNewFinal);
                }
            }
            $priceRemaining = 0;
            $priceToBePaid = 0;
            $price = 0;
        } else {
            $priceRemaining = 0;
            $priceToBePaid = 0;
            $price = 0;
            \Session::put('increase-decrease-days-dont-cloud', $orderId);
        }

        return ['price' => $price, 'priceRemaining' => $priceRemaining, 'priceToBePaid' => $priceToBePaid];
    }

    public function checkAgentAlteration()
    {
        $cloud = false;
        if (\Session::has('AgentAlteration')) {
            return true;
        }

        return $cloud;
    }

    /**
     *  This function is used to do agent altering in cloud level.
     *
     * @param  $newAgents
     * @param  $oldLicense
     * @param  $orderId
     * @param  $installation_path
     * @param  $product_id
     * @return
     *
     * @throws
     */
    public function doTheAgentAltering($newAgents, $oldLicense, $orderId, $installation_path, $product_id)
    {
        try {
            $len = strlen($newAgents);
            switch ($len) {//Get Last Four digits based on No.Of Agents
                case '1':
                    $lastFour = '000'.$newAgents;
                    break;
                case '2':
                    $lastFour = '00'.$newAgents;
                    break;
                case '3':
                    $lastFour = '0'.$newAgents;
                    break;
                case '4':
                    $lastFour = $newAgents;
                    break;
                default:
                    $lastFour = '0000';
            }

            $license_code = substr($oldLicense, 0, -4).$lastFour;
            (new LicenseController())->updateLicense($license_code, $oldLicense);
            Order::where('id', $orderId)->update(['serial_key' => \Crypt::encrypt(substr($license_code, 0, 12).$lastFour)]);
            $keys = ThirdPartyApp::where('app_name', 'faveo_app_key')->select('app_key', 'app_secret')->first();
            $token = str_random(32);
            $data = ['licenseCode' => $license_code, 'installation_path' => $installation_path, 'product_id' => $product_id, 'old_lic_code' => $oldLicense, 'app_key' => $keys->app_key, 'token' => $token, 'timestamp' => time()];
            $encodedData = http_build_query($data);
            $hashedSignature = hash_hmac('sha256', $encodedData, $keys->app_secret);
            $client = new Client([]);
            $response = $client->request(
                'POST',
                $this->cloud->cloud_central_domain.'/performAgentUpgradeOrDowngrade', ['form_params' => $data, 'headers' => ['signature' => $hashedSignature]]
            );

            $response = explode('{', (string) $response->getBody());

            $response = '{'.$response[1];

            $result = json_decode($response);

            if ($result->status == 'fails') {
                return errorResponse(trans('message.change_agents_failed'));
            }
            \Session::forget('AgentAlteration');
            \Session::forget('newAgents');
            \Session::forget('orderId');
            \Session::forget('installation_path');
            \Session::forget('product_id');
            \Session::forget('oldLicense');

            return successResponse(trans('message.agent_updated'));
        } catch(\Exception $e) {
            app('log')->error($e->getMessage());

            return errorResponse(trans('message.wrong_agents'));
        }
    }

    /**
     *  This function is used to do agent altering in cloud level.
     *
     * @param  $licenseCode
     * @param  $installationPath
     * @param  $productID
     * @param  $oldLicenseCode
     * @return
     *
     * @throws
     */
    public function doTheProductUpgradeDowngrade($licenseCode, $installationPath, $productID, $oldLicenseCode)
    {
        \Session::forget('priceRemaining');

        $this->doTheActivity();

        $keys = ThirdPartyApp::where('app_name', 'faveo_app_key')->select('app_key', 'app_secret')->first();
        $token = str_random(32);
        $data = ['licenseCode' => $licenseCode, 'installation_path' => $installationPath, 'product_id' => $productID, 'old_lic_code' => $oldLicenseCode, 'app_key' => $keys->app_key, 'token' => $token, 'timestamp' => time()];
        $encodedData = http_build_query($data);
        $client = new Client();
        $hashedSignature = hash_hmac('sha256', $encodedData, $keys->app_secret);
        \Log::debug('sas', [$data, $hashedSignature]);
        $response = $client->request(
            'POST',
            $this->cloud->cloud_central_domain.'/performProductUpgradeOrDowngrade', ['form_params' => $data, 'headers' => ['signature' => $hashedSignature]]
        );

        $response = explode('{', (string) $response->getBody());

        $response = '{'.$response[1];

        json_decode($response);

        $orderId = \Session::get('upgradeorderId');

        Order::where('id', $orderId)->update(['order_status' => 'Terminated']);

        \DB::table('terminated_order_upgrade')->insert(['terminated_order_id' => $orderId, 'upgraded_order_id' => \Session::get('upgradeNewActiveOrder')]);

        \Session::forget('upgradeDowngradeProduct');
        \Session::forget('upgradeOldLicense');
        \Session::forget('upgradeInstallationPath');
        \Session::forget('upgradeorderId');
        \Session::forget('upgradeProductId');
        \Session::forget('upgradeNewActiveOrder');
        \Session::forget('plan');

        \Cart::clear();
    }

    public function checkUpgradeDowngrade()
    {
        $cloud = false;
        if (\Session::has('upgradeDowngradeProduct')) {
            return true;
        }

        return $cloud;
    }

    public function updateSession(Request $request)
    {
        if ($request->has('isChecked')) {
            ($request->input('isChecked') == 'true') ?
                \DB::table('users')->where('id', \Auth::user()->id)->update(['billing_pay_balance' => 1]) :
                \DB::table('users')->where('id', \Auth::user()->id)->update(['billing_pay_balance' => 0]);
        }

        return response()->json(['message' => __('message.developer_why_checking')]);
    }

    /**
     *  This function is used to when we select to pay from balance.
     *
     * @param  Request  $request
     * @return JsonResponse
     *
     * @throws
     */
    public function formatCurrency(Request $request)
    {
        $amount = $request->input('amount');
        $currency = $request->input('currency');
        if (! $amount && User::where('id', \Auth::user()->id)->value('billing_pay_balance')) {
            \Session::forget('nothingLeft');
            \Session::put('nothingLeft', $amount);
        }
        if ($request->has('invoiceId') && $request->has('alter')) {
            if ($request->get('alter')) {
                Invoice::where('id', $request->input('invoiceId'))->update(['billing_pay' => $request->get('billing_pay')]);
                Invoice::where('id', $request->input('invoiceId'))->update(['billing_pay' => $request->get('billing_pay')]);
                Invoice::where('id', $request->input('invoiceId'))->update(['grand_total' => $amount]);
            }
        }
        // Call the currencyFormat function or perform necessary formatting
        $formattedValue = currencyFormat($amount, $currency, true);

        return response()->json(['formatted_value' => $formattedValue]);
    }

    /**
     *  This function is used when we downgrade a plan, it will add the messages how much amount has been added to the credit balance.
     *
     * @param
     * @return
     *
     * @throws
     */
    private function doTheActivity()
    {
        if (\Session::has('discount')) {
            $discount = \Session::get('discount');
            if ($discount) {
                Payment::where('user_id', \Auth::user()->id)
                    ->where('payment_status', 'pending')->where('amt_to_credit', $discount)
                    ->where('payment_method', 'Credit Balance')
                    ->latest()->update(['payment_status' => 'success']);

                $payment_id = \DB::table('payments')->where('user_id', \Auth::user()->id)->where('payment_status', 'success')->where('payment_method', 'Credit Balance')->value('id');
                $formattedValue = currencyFormat($discount, getCurrencyForClient(\Auth::user()->country), true);
                $oldOrderId = \Session::get('upgradeorderId');
                $oldOrderNumber = Order::where('id', $oldOrderId)->value('number');
                $newOrderId = \Session::get('upgradeNewActiveOrder');
                $newOrderNumber = Order::where('id', $newOrderId)->value('number');

                $messageAdmin = 'A credit of '.$formattedValue.' has been added to the balance due to a plan downgrade. Details of the terminated order can be found here: '.
                    '<a href="'.config('app.url').'/orders/'.$oldOrderId.'">'.$oldOrderNumber.'</a>.'.' You can also view details of the downgraded order here: '.
                    '<a href="'.config('app.url').'/orders/'.$newOrderId.'">'.$newOrderNumber.'</a>.';

                $messageClient = 'A credit of '.$formattedValue.' has been added to your balance due to a product downgrade. Details of the terminated order can be found here: '.
                    '<a href="'.config('app.url').'/my-order/'.$oldOrderId.'">'.$oldOrderNumber.'</a>.'.' You can also view details of the downgraded order here: '.
                    '<a href="'.config('app.url').'/my-order/'.$newOrderId.'">'.$newOrderNumber.'</a>.';

                \DB::table('credit_activity')->insert(['payment_id' => $payment_id, 'text' => $messageAdmin, 'role' => 'admin', 'created_at' => \Carbon\Carbon::now(), 'updated_at' => \Carbon\Carbon::now()]);
                \DB::table('credit_activity')->insert(['payment_id' => $payment_id, 'text' => $messageClient, 'role' => 'user', 'created_at' => \Carbon\Carbon::now(), 'updated_at' => \Carbon\Carbon::now()]);

                \Session::forget('discount');
            }
        }
    }

    /**
     *  This function is used to provide the actual cost before upgrading and downgrading a plan, it will be displayed.
     *
     * @param  $newAgents
     * @param  $oldAgents
     * @param  $orderId
     * @param  $planIdNew
     * @param  $actualPrice
     * @param  $pricePerAgent
     * @return array
     *
     * @throws
     */
    private function getThePaymentCalculationUpgradeDowngradeDisplay($newAgents, $oldAgents, $orderId, $planIdNew, $actualPrice, $pricePerAgent)
    {
        try {
            $discount = 0;

            $planIdOld = Subscription::where('order_id', $orderId)->value('plan_id');

            $ends_at = Subscription::where('order_id', $orderId)->value('ends_at');
            $oldAgents = substr($oldAgents, 12, 16);

            $product_id_old = Plan::where('id', $planIdOld)->pluck('product')->first();
            $planDaysOld = Plan::where('id', $planIdOld)->pluck('days')->first();
            $productOld = Product::find($product_id_old);
            $planOld = $productOld->planRelation->find($planIdOld);
            $currencyOld = userCurrencyAndPrice('', $planOld);
            $countryid = \App\Model\Common\Country::where('country_code_char2', \Auth::user()->country)->value('country_id');

            $base_priceOld = PlanPrice::where('plan_id', $planIdOld)->where('currency', $currencyOld['currency'])->where('country_id', $countryid)->value('add_price');
            if (! $base_priceOld) {
                $base_priceOld = PlanPrice::where('plan_id', $planIdOld)->where('currency', $currencyOld['currency'])->where('country_id', 0)->value('add_price') * $oldAgents;
            } else {
                $base_priceOld = $base_priceOld * $oldAgents;
            }

            $product_id_new = Plan::where('id', $planIdNew)->pluck('product')->first();
            $planDaysNew = Plan::where('id', $planIdNew)->pluck('days')->first();
            $productNew = Product::find($product_id_new);
            $planNew = $productNew->planRelation->find($planIdNew);
            $currencyNew = userCurrencyAndPrice('', $planNew);
            $base_price_new = PlanPrice::where('plan_id', $planIdNew)->where('currency', $currencyNew['currency'])->where('country_id', $countryid)->value('add_price');
            if (! $base_price_new) {
                $base_price_new = PlanPrice::where('plan_id', $planIdNew)->where('currency', $currencyNew['currency'])->where('country_id', 0)->value('add_price') * $newAgents;
            } else {
                $base_price_new = $base_price_new * $newAgents;
            }

            if ($base_price_new > $base_priceOld) {
                $variables = $this->displayPriceNewGreaterThanOld($ends_at, $base_price_new, $base_priceOld, $planDaysNew, $planDaysOld);
                $price = $variables['price'];
                $priceToBePaid = $variables['priceToBePaid'];
                $priceRemaining = $variables['priceRemaining'];
            } elseif ($base_price_new == $base_priceOld) {
                if (Carbon::now() >= $ends_at) {
                    $price = $base_price_new;
                    $priceRemaining = 0;
                    $priceToBePaid = $price;
                } else {
                    $priceRemaining = 0;
                    $priceToBePaid = 0;
                    $price = 0;
                }
            } else {
                $variables = $this->displayPriceNewLessThanOld($ends_at, $base_price_new, $base_priceOld, $planDaysNew, $planDaysOld);
                $price = $variables['price'];
                $priceToBePaid = $variables['priceToBePaid'];
                $priceRemaining = $variables['priceRemaining'];
                $discount = $variables['discount'];
            }
            $items = ['priceoldplan' => currencyFormat($priceRemaining, $currencyNew['currency'], true), 'pricenewplan' => currencyFormat($priceToBePaid, $currencyNew['currency'], true), 'price_to_be_paid' => currencyFormat(abs($price), $currencyNew['currency'], true), 'discount' => currencyFormat($discount, $currencyNew['currency'], true), 'priceperagent' => currencyFormat($pricePerAgent, $currencyNew['currency'], true)];

            return $items;
        } catch(\Exception $e) {
            app('log')->error($e->getMessage());

            return ['price_to_be_paid' => 'NaN', 'discount' => 'NaN', 'currency' => 'NaN'];
        }
    }

    private function displayPriceNewGreaterThanOld($ends_at, $base_price_new, $base_priceOld, $planDaysNew, $planDaysOld)
    {
        if (Carbon::now() >= $ends_at) {
            $price = $base_price_new;
            $priceRemaining = 0;
            $priceToBePaid = $price;
        } else {
            $pricePerDayNew = $base_price_new / $planDaysNew; //800
            $pricePerDayOld = $base_priceOld / $planDaysOld; //1600
            $futureDateTime = Carbon::createFromFormat('Y-m-d H:i:s', $ends_at);
            $currentDateTime = Carbon::now();
            $daysRemain = $futureDateTime->diffInDays($currentDateTime);

            if ($planDaysNew !== $planDaysOld) {
                $daysRemainNew = $planDaysOld - $daysRemain;
                $daysRemainNewFinal = $planDaysNew - $daysRemainNew;
                $pricePerThatAgentNew = $pricePerDayNew * $daysRemainNewFinal;
                $pricePerThatAgentOld = $pricePerDayOld * $daysRemain;
                $price = $pricePerThatAgentNew - $pricePerThatAgentOld;
                $priceRemaining = $pricePerThatAgentOld;
                $priceToBePaid = $pricePerThatAgentNew;
            } else {
                $pricePerThatAgentNew = $pricePerDayNew * $daysRemain;
                $pricePerThatAgentOld = $pricePerDayOld * $daysRemain;
                $price = $pricePerThatAgentNew - $pricePerThatAgentOld;
                $priceRemaining = $pricePerThatAgentOld;
                $priceToBePaid = $pricePerThatAgentNew;
            }
        }

        return ['price' => $price, 'priceRemaining' => $priceRemaining, 'priceToBePaid' => $priceToBePaid];
    }

    private function displayPriceNewLessThanOld($ends_at, $base_price_new, $base_priceOld, $planDaysNew, $planDaysOld)
    {
        $discount = 0;
        if (Carbon::now() >= $ends_at) {
            $price = $base_price_new;
            $priceRemaining = 0;
            $priceToBePaid = $price;
        } else {
            $futureDateTime = Carbon::createFromFormat('Y-m-d H:i:s', $ends_at);
            $currentDateTime = Carbon::now();
            $daysRemain = $futureDateTime->diffInDays($currentDateTime);
            $pricePerDayForNewPlan = $base_price_new / $planDaysNew;
            $pricePerDayForOldPlan = $base_priceOld / $planDaysOld;

            if ($planDaysOld !== $planDaysNew) {
                $variables = $this->displayNewPlanDaysNotEqualOld($daysRemain, $planDaysNew, $planDaysOld, $pricePerDayForNewPlan, $pricePerDayForOldPlan);
                $price = $variables['price'];
                $priceToBePaid = $variables['priceToBePaid'];
                $priceRemaining = $variables['priceRemaining'];
                $discount = $variables['discount'];
            } else {
                $priceToBePaid = $pricePerDayForNewPlan * $daysRemain;
                $priceRemaining = $pricePerDayForOldPlan * $daysRemain;
                if ($priceToBePaid > $priceRemaining) {
                    $price = $priceToBePaid - $priceRemaining;
                } else {
                    $discount = $priceRemaining - $priceToBePaid;
                    $price = 0;
                }
            }
        }

        return ['price' => $price, 'priceRemaining' => $priceRemaining, 'priceToBePaid' => $priceToBePaid, 'discount' => $discount];
    }

    private function displayNewPlanDaysNotEqualOld($daysRemain, $planDaysNew, $planDaysOld, $pricePerDayForNewPlan, $pricePerDayForOldPlan)
    {
        $discount = 0;
        if ($daysRemain <= $planDaysNew && $planDaysOld > $planDaysNew) {
            $priceToBePaid = $pricePerDayForNewPlan * $daysRemain;
            $priceRemaining = $pricePerDayForOldPlan * $daysRemain;
        } else {
            $daysRemainNew = $planDaysOld - $daysRemain;
            $daysRemainNewFinal = $planDaysNew - $daysRemainNew;
            $priceToBePaid = $pricePerDayForNewPlan * $daysRemainNewFinal;
            $priceRemaining = $pricePerDayForOldPlan * $daysRemain;
        }
        if ($priceToBePaid > $priceRemaining) {
            $price = $priceToBePaid - $priceRemaining;
        } else {
            $discount = $priceRemaining - $priceToBePaid;
            $price = 0;
        }

        return ['price' => $price, 'priceRemaining' => $priceRemaining, 'priceToBePaid' => $priceToBePaid, 'discount' => $discount];
    }

    public function processFormat(Request $request)
    {
        return currencyFormat($request->get('totalPrice'), getCurrencyForClient(\Auth::user()->country), true);
    }

    /**
     *  This function is used to provide the actual cost before changing number of agents, it will be displayed.
     *
     * @param  request  $request
     * @return array
     *
     * @throws
     */
    public function getThePaymentCalculationDisplay(Request $request)
    {
        try {
            $newAgents = $request->get('number');

            $oldAgents = $request->get('oldAgents');
            $orderId = $request->get('orderId');
            $planId = Subscription::where('order_id', $orderId)->value('plan_id');

            $product_id = Plan::where('id', $planId)->pluck('product')->first();

            $planDays = Plan::where('id', $planId)->pluck('days')->first();
            $product = Product::find($product_id);
            $plan = $product->planRelation->find($planId);
            $currency = userCurrencyAndPrice('', $plan);
            $ends_at = Subscription::where('order_id', $orderId)->value('ends_at');
            $countryid = \App\Model\Common\Country::where('country_code_char2', \Auth::user()->country)->value('country_id');

            $base_price = PlanPrice::where('plan_id', $planId)->where('currency', $currency['currency'])->where('country_id', $countryid)->value('add_price');

            if (! $base_price) {
                $base_price = PlanPrice::where('plan_id', $planId)->where('currency', $currency['currency'])->where('country_id', 0)->value('add_price');
            }
            if (empty($newAgents)) {
                return ['pricePerAgent' => currencyFormat($base_price, $currency['currency'], true), 'totalPrice' => 0, 'priceToPay' => 0];
            }
            if ($newAgents > $oldAgents) {
                $price = $this->newAgentgreaterthenOld($ends_at, $base_price, $newAgents, $oldAgents, $planDays);
            } else {
                $price = $this->newAgentlessthenOld($ends_at, $base_price, $newAgents, $oldAgents, $planDays);
            }

            return ['pricePerAgent' => currencyFormat($base_price, $currency['currency'], true), 'totalPrice' => currencyFormat($base_price * $newAgents, $currency['currency'], true), 'priceToPay' => currencyFormat($price, $currency['currency'], true)];
        } catch(\Exception $e) {
            app('log')->error($e->getMessage());

            return ['pricePerAgent' => 'NaN', 'totalPrice' => 'NaN', 'priceToPay' => 'NaN'];
        }
    }

    public function storeTenantTillPurchase(Request $request)
    {
        if (! $this->checkDomain($request->input('domain'))) {
            return response(['status' => false, 'message' => trans('message.domain_taken')]);
        }
        \Session::forget('plan_id');
        (new CartController())->cart($request);

        return response()->json(['redirectTo' => env('APP_URL').'/show/cart']);
    }

    public function checkDomain($domain)
    {
        $client = new Client([]);
        $keys = ThirdPartyApp::where('app_name', 'faveo_app_key')->select('app_key', 'app_secret')->first();

        $data = ['domain' => $domain, 'key' => $keys->app_key];
        $response = $client->request(
            'POST',
            $this->cloud->cloud_central_domain.'/checkDomain', ['form_params' => $data]
        );
        $response = explode('{', (string) $response->getBody());

        $response = array_first($response);

        return json_decode($response);
    }

    public function fetchData()
    {
        $collection = collect(CloudProducts::cursor());

        return \DataTables::collection($collection)
            ->addColumn('Cloud Product', function ($model) {
                return "<p><a href='".url('/products/'.$model->product->id.'/edit')."'>".$model->product->name.'</a></p>';
            })
            ->addColumn('Cloud free plan', function ($model) {
                return "<p><a href='".url('/plans/'.$model->product->id.'/edit')."'>".$model->plan->name.'</a></p>';
            })
            ->addColumn('Cloud product key', function ($model) {
                return $model->cloud_product_key;
            })
            ->addColumn('action', function ($model) {
                return "<p><button data-toggle='modal'
                data-id='".$model->id."' data-name='' onclick=\"popProduct('".$model->id."')\" id='delpop".$model->id."'
                class='btn btn-sm btn-dark btn-xs delTenant' ".tooltip(__('message.delete'))."<i class='fa fa-trash'
                style='color:white;'> </i></button>&nbsp;</p>";
            })

            ->addColumn('status', function ($model) {
                $checked = $model->trial_status ? 'checked' : '';

                return '<label class="swich toggle_event_editing trialStatus">
                <input type="checkbox" class="checkbox9" name="trialStatus"
                       value="1" data-status="'.$model->trial_status.'" 
                       id="'.$model->id.'" '.$checked.'>
                <span class="slidr rund"></span>
            </label>';
            })
            ->rawColumns(['Cloud Product', 'Cloud free plan', 'Cloud product key', 'action', 'status'])
            ->make(true);
    }

    public function updateTrialStatus(Request $request)
    {
        try {
            $id = $request->input('id');
            $status = $request->input('status');
            CloudProducts::where('id', $id)->update(['trial_status' => $status]);

            return successResponse(\Lang::get('message.trial_status_updated'));
        } catch (\Exception $e) {
            return errorResponse(\Lang::get('message.trial_status_error'));
        }
    }

    public function trialCloudProducts()
    {
        $cloud = CloudProducts::where('trial_status', '1')->with('product')->get();
        $product = $cloud->pluck('product.name', 'cloud_product_key')->filter()->all();

        return successResponse('Products', $product);
    }

    public function DeleteProductConfig(Request $request)
    {
        try {
            CloudProducts::whereid($request->get('id'))->delete();

            return successResponse(trans('message.pop_delete'));
        } catch(\Exception $e) {
            return errorResponse($e->getMessage());
        }
    }

    public function storeCloudDataCenter(Request $request)
    {
        $request->validate(['cloud_countries' => 'required', 'cloud_state' => 'required']);
        $countryName = Country::where('country_code_char2', strtoupper($request->get('cloud_countries')))->value('nicename');
        $state = $request->get('cloud_state');
        $city = $request->get('cloud_city');
        $geo = (empty($city)) ? $this->getStateCoordinates($state) : $this->getStateCoordinates($city);
        $state = State::where('state_subdivision_code', $state)->value('state_subdivision_name');
        if (! empty($geo)) {
            CloudDataCenters::create([
                'cloud_countries' => $countryName,
                'cloud_state' => $state,
                'cloud_city' => $city,
                'latitude' => $geo['latitude'],
                'longitude' => $geo['longitude'],
            ]);

            return redirect()->back()->with('success', trans('message.saved_data_center'));
        } else {
            return redirect()->back()->with('fails', trans('message.no_lat_or_long'));
        }
    }

    private function getStateCoordinates($stateName)
    {
        $stateName = str_replace(' ', '+', $stateName);
        $url = "https://nominatim.openstreetmap.org/search?q={$stateName}&format=json&limit=1";
        $client = new Client([
            'verify' => true,
        ]);
        $response = $client->get($url, [
            'headers' => [
                'Referer' => $url,
            ],
        ]);

        $data = json_decode($response->getBody(), true);
        if (empty($data)) {
            return null;
        }
        $latitude = $data[0]['lat'];
        $longitude = $data[0]['lon'];

        return ['latitude' => $latitude, 'longitude' => $longitude];
    }

    public function removeLocation(Request $request)
    {
        try {
            $location = array_first(explode(', ', $request->location_id));
            CloudDataCenters::where('cloud_state', $location)->orWhere('cloud_city', $location)->delete();

            return redirect()->back()->with('success', trans('message.removed_datacenter'));
        } catch(\Exception $e) {
            return redirect()->back()->with('fails', trans('message.something_went_wrong'));
        }
    }
}
