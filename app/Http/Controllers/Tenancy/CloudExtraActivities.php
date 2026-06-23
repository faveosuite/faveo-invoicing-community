<?php

namespace App\Http\Controllers\Tenancy;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Order\InvoiceController as InvoiceCtrl;
use App\Http\Controllers\Order\RenewController;
use App\License\Models\Installation;
use App\License\Services\LicenseService;
use App\Model\CloudDataCenters;
use App\Model\Common\Country;
use App\Model\Common\FaveoCloud;
use App\Model\Common\State;
use App\Model\Order\Invoice;
use App\Model\Order\InvoiceItem;
use App\Model\Order\Order;
use App\Model\Order\OrderInvoiceRelation;
use App\Model\Order\Payment;
use App\Model\Payment\Plan;
use App\Model\Payment\PlanPrice;
use App\Model\Product\CloudProducts;
use App\Model\Product\Product;
use App\Model\Product\Subscription;
use App\ThirdPartyApp;
use App\Traits\TaxCalculation;
use App\User;
use Crypt;
use DB;
use Exception;
use GuzzleHttp\Client;
use Illuminate\Contracts\Database\Query\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Str;
use Lang;
use Logger;

class CloudExtraActivities extends Controller
{
    use TaxCalculation;

    public mixed $cloud = null;

    public function __construct(Client $client, FaveoCloud $cloud) // @phpstan-ignore constructor.unusedParameter
    {
        $this->cloud = $cloud->first();
        $this->middleware('auth', ['except' => ['verifyThirdPartyToken']]);
    }

    /**
     * @param  array<mixed>  $data
     */
    private function cloudApiPost(string $endpoint, array $data): object
    {
        $keys = ThirdPartyApp::where('app_name', 'faveo_app_key')->select('app_key', 'app_secret')->first();
        if (! $keys) {
            throw new Exception('Missing faveo_app_key setting');
        }
        $data = array_merge($data, ['app_key' => $keys->app_key, 'token' => Str::random(32), 'timestamp' => time()]);
        $hashedSignature = hash_hmac('sha256', http_build_query($data), (string) $keys->app_secret);

        $response = new Client()->request('POST', $this->cloud->cloud_central_domain.$endpoint, [
            'form_params' => $data,
            'headers' => ['signature' => $hashedSignature],
        ]);

        return json_decode('{'.explode('{', (string) $response->getBody())[1]);
    }

    private function daysRemaining(string $ends_at): int
    {
        return $this->daysRemaining($ends_at);
    }

    private function isExpired(string $ends_at): bool
    {
        return Date::now() >= Date::parse($ends_at);
    }

    private function checktheAgent(mixed $numberOfAgents, string $domain): mixed
    {
        $client = new Client([]);
        $response = $client->request('POST', 'https://'.$domain.'/api/agent-check', [
            'form_params' => ['number_of_agents' => $numberOfAgents],
        ]);
        $response = explode('{', (string) $response->getBody());

        return json_decode((string) Arr::first($response));
    }

    public function domainCloudAutofill(): JsonResponse
    {
        $company = User::where('id', $this->authUser()->id)->value('company');
        $company = substr(strtolower(str_replace(' ', '', $company)), 0, 28);

        return response()->json(['data' => $company]);
    }

    /**
     * @return array<mixed>
     */
    public function getUpgradeCost(Request $request): array
    {
        try {
            $planId = $request->input('plan');
            $agents = $request->input('agents');
            $orderId = $request->input('orderId');
            $plan = Plan::find($planId);

            $planDetails = userCurrencyAndPrice($this->authUser()->id, $plan);
            $actualPrice = $planDetails['plan']->add_price * $agents;
            $oldLicense = Order::where('id', $orderId)->latest()->value('serial_key');

            return $this->getThePaymentCalculationUpgradeDowngradeDisplay(
                $agents, $oldLicense, $orderId, $planId, $planDetails['plan']->add_price
            );
        } catch (Exception $exception) {
            Logger::exception($exception);

            return ['price_to_be_paid' => 'NaN', 'discount' => 'NaN', 'currency' => 'NaN'];
        }
    }

    public function changeDomain(Request $request): JsonResponse
    {
        try {
            $this->validate($request, [
                'currentDomain' => 'required',
                'newDomain' => 'required',
            ], [
                'currentDomain.required' => __('validation.current_domain_required'),
                'newDomain.required' => __('validation.new_domain_required'),
            ]);

            $orderId = $request->input('order_id');
            $order = Order::where('id', $orderId)->first();
            if (! $order) {
                return errorResponse(trans('message.something_went_wrong'));
            }

            if ($order->client != $this->authUser()->id) {
                return errorResponse(trans('message.invalid_user'));
            }

            $newDomain = $request->get('newDomain');
            $currentDomain = $request->get('currentDomain');

            if (! filter_var($newDomain, FILTER_VALIDATE_DOMAIN, FILTER_FLAG_HOSTNAME)) {
                return errorResponse(trans('message.not_allowed_domain'));
            }

            if (str_contains((string) $newDomain, '.'.cloudSubDomain())) {
                return errorResponse(trans('message.cloud_not_allowed'));
            }

            if ($newDomain === $currentDomain) {
                return errorResponse(trans('message.nothing_changed'));
            }

            $dns_record = dns_get_record($newDomain, DNS_CNAME);

            if (! strpos((string) $newDomain, (string) cloudSubDomain()) && ($dns_record === [] || $dns_record === false || ! in_array(cloudSubDomain(), array_column($dns_record, 'target')))) {
                return errorResponse(trans('message.cname'));
            }

            $this->cloudApiPost('/changeDomain', [
                'currentDomain' => $currentDomain,
                'newDomain' => $newDomain,
                'lic_code' => $request->get('lic_code'),
                'product_id' => $request->product_id,
            ]);

            $this->jobsForCloudDomain($newDomain);

            return successResponse(trans('message.cloud_domain_change'));
        } catch (Exception $exception) {
            Logger::exception($exception);

            return errorResponse(trans('message.wrong_domain'));
        }
    }

    private function jobsForCloudDomain(string $newDomain): void
    {
        new Client([])->request('GET', config('custom.cloud_job_url'), [
            'auth' => [config('custom.cloud_user'), config('custom.cloud_auth')],
            'query' => ['token' => config('custom.cloud_oauth_token'), 'domain' => $newDomain],
        ]);
    }

    public function agentAlteration(Request $request): JsonResponse
    {
        try {
            $newAgents = $request->newAgents;

            if (empty($newAgents)) {
                return errorResponse(trans('message.agent_zero'));
            }

            $orderId = $request->input('order_id');
            $order = Order::where('id', $orderId)->first();
            if (! $order) {
                return errorResponse(trans('message.something_went_wrong'));
            }

            if ($order->client != $this->authUser()->id) {
                return errorResponse(trans('message.invalid_user'));
            }

            $oldAgents = ltrim(substr((string) $order->serial_key, 12), '0');

            if ($request->agentAction == 'decrease' && $oldAgents <= $newAgents) {
                return errorResponse(trans('message.agent_decrease_invalid'));
            }

            $totalAgents = $request->agentAction == 'increase'
                ? $oldAgents + $newAgents
                : $oldAgents - $newAgents;

            $installationPath = Installation::where('license_code', $order->serial_key)
                ->where('installation_path', '!=', cloudCentralDomain())
                ->latest('updated_at')
                ->value('installation_path');

            if (empty($installationPath)) {
                return errorResponse(trans('message.installation_path_not_found'));
            }

            if ($this->checktheAgent($totalAgents, $installationPath)) {
                return errorResponse(trans('message.agent_reduce'));
            }

            $oldLicense = $order->serial_key;
            $items = $this->getThePaymentCalculation($newAgents, $oldLicense, $orderId, agentAction: $request->agentAction);
            $invoice = new RenewController()->renewBySubId($request->subId, $items['planId'], '', $items['price'], '', isAgentIncrease: false, agents: $totalAgents);
            if (! $invoice instanceof InvoiceItem) {
                return errorResponse(trans('message.something_went_wrong'));
            }

            // Determine if subscription is expired — if so, renewal date extension is needed
            $sub = Subscription::where('order_id', $orderId)->first();
            $isExpired = $sub && Date::now() >= Date::parse($sub->ends_at);

            $dbInvoice = Invoice::find($invoice->invoice_id);
            if ($dbInvoice) {
                $dbInvoice->update([
                    'metadata' => [
                        'type' => 'agent_alteration',
                        'sub_id' => $request->subId,
                        'new_agents' => $totalAgents,
                        'order_id' => $orderId,
                        'installation_path' => $installationPath,
                        'product_id' => $request->product_id,
                        'old_license' => $oldLicense,
                        'agent_increase_date' => $isExpired,
                    ],
                ]);
            }

            return successResponse('success', ['invoice_id' => $invoice->invoice_id]);
        } catch (Exception $exception) {
            Logger::exception($exception);

            return errorResponse(trans('message.wrong_agents'));
        }
    }

    public function upgradeDowngradeCloud(Request $request): JsonResponse
    {
        try {
            $planId = $request->id;
            $agents = (int) $request->agents;
            $orderId = $request->orderId;

            $order = Order::find($orderId);
            if (! $order instanceof Order) {
                return errorResponse(trans('message.something_went_wrong'));
            }
            if ($order->client != $this->authUser()->id) {
                return errorResponse(trans('message.invalid_user'));
            }

            $oldLicense = $order->serial_key;
            $installationPath = Installation::where('license_code', $oldLicense)
                ->where('installation_path', '!=', cloudCentralDomain())
                ->latest('updated_at')
                ->value('installation_path');

            $calc = $this->getThePaymentCalculationUpgradeDowngrade($agents, $oldLicense, $orderId, $planId);
            $price = abs(round($calc['price']));
            $discount = $calc['discount'] ?? null;
            $productNew = $calc['product'];
            $currencyNew = $calc['currency'];

            $user = $this->authUser();
            $tax = $this->calculateTax($productNew->id, (string) $user->state, $user->country);
            $invoiceCtrl = new InvoiceCtrl;
            $finalCost = rounding($invoiceCtrl->calculateTotal($tax['value'], $price));

            $invoice = Invoice::create([
                'user_id' => $user->id,
                'number' => random_int(11111111, 99999999),
                'date' => Date::now(),
                'grand_total' => $finalCost,
                'currency' => $currencyNew,
                'status' => 'pending',
                'metadata' => [
                    'type' => 'upgrade_downgrade',
                    'old_order_id' => $orderId,
                    'old_license' => $oldLicense,
                    'installation_path' => $installationPath,
                    'discount' => $discount,
                ],
            ]);

            OrderInvoiceRelation::create(['order_id' => $orderId, 'invoice_id' => $invoice->id]);

            $invoiceCtrl->createInvoiceItemsByAdmin(
                $invoice->id, $productNew->id, $price, $currencyNew,
                1, $agents, $planId, $user->id,
                $tax['name'], $tax['value'], $price // @phpstan-ignore argument.type
            );

            return successResponse('success', ['invoice_id' => $invoice->id]);
        } catch (Exception $exception) {
            Logger::exception($exception);

            return errorResponse(trans('message.wrong_upgrade'));
        }
    }

    /**
     * @return array<mixed>
     */
    private function getThePaymentCalculation(int $newAgents, string $oldLicense, int $orderId, ?int $planId = null, ?string $agentAction = null): array
    {
        try {
            $sub = Subscription::where('order_id', $orderId)->first();
            if (! $sub) {
                return [];
            }
            $planId ??= $sub->plan_id;
            $plan = Plan::with('productRelation')->find($planId);
            if (! $plan) {
                return [];
            }

            $product = $plan->productRelation;
            if (! $product) {
                return [];
            }
            $planTarget = $product->planRelation->find($planId);
            $currency = userCurrencyAndPrice('', $planTarget);
            $ends_at = (string) $sub->ends_at;
            $base_price = $currency['plan']?->add_price;
            $oldAgents = (int) substr($oldLicense, 12, 16);
            $planDays = (int) $plan->days;

            $totalAgents = 0;
            $price = 0.0;
            switch ($agentAction) {
                case 'increase':
                    $totalAgents = $newAgents + $oldAgents;
                    $price = $this->newAgentgreaterthenOld($ends_at, $base_price, $totalAgents, $oldAgents, $planDays);
                    break;
                case 'decrease':
                    $totalAgents = $oldAgents - $newAgents;
                    $price = $this->newAgentlessthenOld($ends_at, $base_price, $totalAgents, $oldAgents, $planDays);
                    break;
            }

            return [
                'id' => $product->id,
                'name' => $product->name,
                'price' => round($price),
                'planId' => $planId,
                'quantity' => 1,
                'attributes' => ['currency' => $currency['currency'], 'symbol' => $currency['symbol'], 'agents' => $totalAgents],
                'associatedModel' => $product,
            ];
        } catch (Exception $exception) {
            Logger::exception($exception);

            return [];
        }
    }

    private function newAgentgreaterthenOld(string $ends_at, float|int $base_price, int $newAgents, int $oldAgents, int $planDays): float
    {
        if ($this->isExpired($ends_at)) {
            return (float) ($base_price * $newAgents);
        }

        $agentsAdded = $newAgents - $oldAgents;
        $pricePerDay = $base_price / $planDays;
        $daysRemain = $this->daysRemaining($ends_at);

        return (float) ($agentsAdded * $pricePerDay * $daysRemain);
    }

    private function newAgentlessthenOld(string $ends_at, float|int $base_price, int $newAgents, int $oldAgents, int $planDays): float
    {
        if ($this->isExpired($ends_at)) {
            return (float) ($base_price * $newAgents);
        }

        $daysRemain = $this->daysRemaining($ends_at);
        $pricePerDayNewAgents = ($base_price * $newAgents) / $planDays;
        $pricePerDayOldAgents = ($base_price * $oldAgents) / $planDays;
        $priceRemaining = $pricePerDayOldAgents * $daysRemain;
        $priceToBePaid = $pricePerDayNewAgents * $daysRemain;

        return $priceToBePaid > $priceRemaining ? $priceToBePaid - $priceRemaining : 0;
    }

    /**
     * @return array<mixed>
     */
    private function getThePaymentCalculationUpgradeDowngrade(int $newAgents, string $oldLicense, int $orderId, int $planIdNew): array
    {
        try {
            $sub = Subscription::where('order_id', $orderId)->first();
            if (! $sub) {
                return ['price' => 0, 'discount' => null, 'product' => null, 'currency' => ''];
            }
            $planIdOld = $sub->plan_id;
            $ends_at = (string) $sub->ends_at;
            $oldAgents = (int) substr($oldLicense, 12, 16);

            $planOld = Plan::with('productRelation')->find($planIdOld);
            if (! $planOld) {
                return ['price' => 0, 'discount' => null, 'product' => null, 'currency' => ''];
            }
            $productOld = $planOld->productRelation;
            if (! $productOld) {
                return ['price' => 0, 'discount' => null, 'product' => null, 'currency' => ''];
            }
            $planTargetOld = $productOld->planRelation->find($planIdOld);
            $currencyOld = userCurrencyAndPrice('', $planTargetOld);
            $base_priceOld = PlanPrice::where('plan_id', $planIdOld)->where('currency', $currencyOld['currency'])->value('add_price') * $oldAgents;
            $planDaysOld = (int) $planOld->days;

            $planNew = Plan::with('productRelation')->find($planIdNew);
            if (! $planNew) {
                return ['price' => 0, 'discount' => null, 'product' => null, 'currency' => ''];
            }
            $productNew = $planNew->productRelation;
            if (! $productNew) {
                return ['price' => 0, 'discount' => null, 'product' => null, 'currency' => ''];
            }
            $planTargetNew = $productNew->planRelation->find($planIdNew);
            $currencyNew = userCurrencyAndPrice('', $planTargetNew);
            $base_price_new = PlanPrice::where('plan_id', $planIdNew)->where('currency', $currencyNew['currency'])->value('add_price') * $newAgents;
            $planDaysNew = (int) $planNew->days;

            if ($base_price_new > $base_priceOld) {
                $result = $this->newPriceGreaterThanOld($ends_at, $base_price_new, $planDaysNew, $base_priceOld, $planDaysOld);
            } elseif ($base_price_new == $base_priceOld) {
                $result = $this->newPriceEqualToOld($ends_at, $base_price_new);
            } else {
                $result = $this->newPriceLessThanOld($ends_at, $base_price_new, $base_priceOld, $planDaysNew, $planDaysOld);
            }

            return [
                'price' => $result['price'],
                'discount' => $result['discount'] ?? null,
                'product' => $productNew,
                'currency' => $currencyNew['currency'],
            ];
        } catch (Exception $exception) {
            Logger::exception($exception);

            return ['price' => 0, 'discount' => null, 'product' => null, 'currency' => ''];
        }
    }

    /**
     * @return array<mixed>
     */
    private function newPriceLessThanOld(string $ends_at, int|float $base_price_new, int|float $base_priceOld, int $planDaysNew, int $planDaysOld): array
    {
        if ($this->isExpired($ends_at)) {
            return ['price' => $base_price_new, 'priceRemaining' => 0, 'priceToBePaid' => $base_price_new, 'discount' => null];
        }

        $daysRemain = $this->daysRemaining($ends_at);
        $pricePerDayNew = $base_price_new / $planDaysNew;
        $pricePerDayOld = $base_priceOld / $planDaysOld;

        if ($planDaysOld !== $planDaysNew) {
            return $this->lessPriceNewDaysNotEqualToOldDays($daysRemain, $planDaysNew, $planDaysOld, $pricePerDayNew, $pricePerDayOld);
        }

        return $this->lessPriceNewDaysEqualToOldDays($daysRemain, $pricePerDayNew, $pricePerDayOld);
    }

    /**
     * @return array<mixed>
     */
    private function lessPriceNewDaysEqualToOldDays(int $daysRemain, int|float $pricePerDayForNewPlan, int|float $pricePerDayForOldPlan): array
    {
        $priceToBePaid = $pricePerDayForNewPlan * $daysRemain;
        $priceRemaining = $pricePerDayForOldPlan * $daysRemain;
        $discount = null;

        if ($priceToBePaid > $priceRemaining) {
            $price = $priceToBePaid - $priceRemaining;
        } else {
            $discount = round($priceRemaining - $priceToBePaid);
            DB::table('users')->where('id', $this->authUser()->id)->update(['billing_pay_balance' => 1]);
            $price = $priceToBePaid;
        }

        return ['price' => $price, 'priceRemaining' => $priceRemaining, 'priceToBePaid' => $priceToBePaid, 'discount' => $discount];
    }

    /**
     * @return array<mixed>
     */
    private function lessPriceNewDaysNotEqualToOldDays(int $daysRemain, int $planDaysNew, int $planDaysOld, int|float $pricePerDayForNewPlan, int|float $pricePerDayForOldPlan): array
    {
        $discount = null;

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
            $discount = round($priceRemaining - $priceToBePaid);
            User::where('id', $this->authUser()->id)->update(['billing_pay_balance' => 1]);
            $price = $priceToBePaid;
        }

        return ['price' => $price, 'priceRemaining' => $priceRemaining, 'priceToBePaid' => $priceToBePaid, 'discount' => $discount];
    }

    /**
     * @return array<mixed>
     */
    private function newPriceGreaterThanOld(string $ends_at, int|float $base_price_new, int $planDaysNew, int|float $base_priceOld, int $planDaysOld): array
    {
        if ($this->isExpired($ends_at)) {
            return ['price' => $base_price_new, 'priceRemaining' => 0, 'priceToBePaid' => $base_price_new, 'discount' => null];
        }

        $pricePerDayNew = $base_price_new / $planDaysNew;
        $pricePerDayOld = $base_priceOld / $planDaysOld;
        $daysRemain = $this->daysRemaining($ends_at);

        if ($planDaysNew !== $planDaysOld) {
            return $this->newPlanDaysNotEqualToOld($planDaysNew, $planDaysOld, $daysRemain, $pricePerDayNew, $pricePerDayOld);
        }

        return $this->newPlanDaysEqualToOld($daysRemain, $pricePerDayNew, $pricePerDayOld);
    }

    /**
     * @return array<mixed>
     */
    private function newPlanDaysNotEqualToOld(int $planDaysNew, int $planDaysOld, int $daysRemain, int|float $pricePerDayNew, int|float $pricePerDayOld): array
    {
        $daysRemainNew = $planDaysOld - $daysRemain;
        $daysRemainNewFinal = $planDaysNew - $daysRemainNew;
        $priceToBePaid = $pricePerDayNew * $daysRemainNewFinal;
        $priceRemaining = $pricePerDayOld * $daysRemain;

        return ['price' => $priceToBePaid - $priceRemaining, 'priceRemaining' => $priceRemaining, 'priceToBePaid' => $priceToBePaid, 'discount' => null];
    }

    /**
     * @return array<mixed>
     */
    private function newPlanDaysEqualToOld(int $daysRemain, int|float $pricePerDayNew, int|float $pricePerDayOld): array
    {
        $priceToBePaid = $pricePerDayNew * $daysRemain;
        $priceRemaining = $pricePerDayOld * $daysRemain;

        return ['price' => $priceToBePaid - $priceRemaining, 'priceRemaining' => $priceRemaining, 'priceToBePaid' => $priceToBePaid, 'discount' => null];
    }

    /**
     * @return array<mixed>
     */
    private function newPriceEqualToOld(string $ends_at, int|float $base_price_new): array
    {
        if ($this->isExpired($ends_at)) {
            return ['price' => $base_price_new, 'priceRemaining' => 0, 'priceToBePaid' => $base_price_new, 'discount' => null];
        }

        return ['price' => 0, 'priceRemaining' => 0, 'priceToBePaid' => 0, 'discount' => null];
    }

    public function doTheAgentAltering(string $newAgents, string $oldLicense, int $orderId, string $installation_path, int $product_id): JsonResponse
    {
        try {
            $len = strlen($newAgents);
            $lastFour = match (true) {
                $len === 1 => '000'.$newAgents,
                $len === 2 => '00'.$newAgents,
                $len === 3 => '0'.$newAgents,
                $len === 4 => $newAgents,
                default => '0000',
            };

            $licenseCode = substr($oldLicense, 0, -4).$lastFour;
            resolve(LicenseService::class)->updateLicenseCode($oldLicense, $licenseCode);
            Order::where('id', $orderId)->update(['serial_key' => Crypt::encrypt(substr($licenseCode, 0, 12).$lastFour)]);

            $result = $this->cloudApiPost('/performAgentUpgradeOrDowngrade', [
                'licenseCode' => $licenseCode,
                'installation_path' => $installation_path,
                'product_id' => $product_id,
                'old_lic_code' => $oldLicense,
            ]);

            $resultArray = (array) $result;
            if (($resultArray['status'] ?? null) == 'fails') {
                return errorResponse(trans('message.change_agents_failed'));
            }

            return successResponse(trans('message.agent_updated'));
        } catch (Exception $exception) {
            Logger::exception($exception);

            return errorResponse(trans('message.wrong_agents'));
        }
    }

    public function doTheProductUpgradeDowngrade(
        string $licenseCode,
        string $installationPath,
        int $productID,
        string $oldLicenseCode,
        int $terminatedOrderId = 0,
        int $newActiveOrderId = 0,
        ?float $discount = null
    ): void {
        $this->doTheActivity($terminatedOrderId, $newActiveOrderId, $discount);

        $this->cloudApiPost('/performProductUpgradeOrDowngrade', [
            'licenseCode' => $licenseCode,
            'installation_path' => $installationPath,
            'product_id' => $productID,
            'old_lic_code' => $oldLicenseCode,
        ]);

        Order::where('id', $terminatedOrderId)->update(['order_status' => 'Terminated']);
        DB::table('terminated_order_upgrade')->insert([
            'terminated_order_id' => $terminatedOrderId,
            'upgraded_order_id' => $newActiveOrderId,
        ]);
    }

    public function doTheActivity(int $terminatedOrderId, int $newActiveOrderId, ?float $discount = null): void
    {
        if ($discount === null) {
            return;
        }

        Payment::where('user_id', $this->authUser()->id)
            ->where('payment_status', 'pending')
            ->where('amt_to_credit', $discount)
            ->where('payment_method', 'Credit Balance')
            ->latest()
            ->update(['payment_status' => 'success']);

        $payment_id = DB::table('payments')
            ->where('user_id', $this->authUser()->id)
            ->where('payment_status', 'success')
            ->where('payment_method', 'Credit Balance')
            ->value('id');

        $formattedValue = currencyFormat($discount, getCurrencyForClient($this->authUser()->country), includeSymbol: true);
        $oldOrderNumber = Order::where('id', $terminatedOrderId)->value('number');
        $newOrderNumber = Order::where('id', $newActiveOrderId)->value('number');

        $messageAdmin = 'A credit of '.$formattedValue.' has been added to the balance due to a plan downgrade. Details of the terminated order can be found here: '
            .'<a href="'.config('app.url').'/orders/'.$terminatedOrderId.'">'.$oldOrderNumber.'</a>.'
            .' You can also view details of the downgraded order here: '
            .'<a href="'.config('app.url').'/orders/'.$newActiveOrderId.'">'.$newOrderNumber.'</a>.';

        $messageClient = 'A credit of '.$formattedValue.' has been added to your balance due to a product downgrade. Details of the terminated order can be found here: '
            .'<a href="'.config('app.url').'/my-order/'.$terminatedOrderId.'">'.$oldOrderNumber.'</a>.'
            .' You can also view details of the downgraded order here: '
            .'<a href="'.config('app.url').'/my-order/'.$newActiveOrderId.'">'.$newOrderNumber.'</a>.';

        DB::table('credit_activity')->insert(['payment_id' => $payment_id, 'text' => $messageAdmin,  'role' => 'admin', 'created_at' => Date::now(), 'updated_at' => Date::now()]);
        DB::table('credit_activity')->insert(['payment_id' => $payment_id, 'text' => $messageClient, 'role' => 'user',  'created_at' => Date::now(), 'updated_at' => Date::now()]);
    }

    /**
     * @return array<mixed>
     */
    private function getThePaymentCalculationUpgradeDowngradeDisplay(int $newAgents, string $oldAgents, int $orderId, int $planIdNew, float|int $pricePerAgent): array
    {
        try {
            $discount = 0;
            $planIdOld = Subscription::where('order_id', $orderId)->value('plan_id');
            $ends_at = (string) Subscription::where('order_id', $orderId)->value('ends_at');
            $oldAgents = substr($oldAgents, 12, 16);

            $product_id_old = Plan::where('id', $planIdOld)->value('product');
            $planDaysOld = Plan::where('id', $planIdOld)->value('days');
            $productOld = Product::find($product_id_old);
            if (! $productOld instanceof Product) {
                throw new Exception('Product not found');
            }
            $planTargetOld = $productOld->planRelation->find($planIdOld);
            $currencyOld = userCurrencyAndPrice('', $planTargetOld);
            $base_priceOld = PlanPrice::where('plan_id', $planIdOld)->where('currency', $currencyOld['currency'])->value('add_price') * $oldAgents;

            $product_id_new = Plan::where('id', $planIdNew)->value('product');
            $planDaysNew = Plan::where('id', $planIdNew)->value('days');
            $productNew = Product::find($product_id_new);
            if (! $productNew instanceof Product) {
                throw new Exception('Product not found');
            }
            $planTargetNew = $productNew->planRelation->find($planIdNew);
            $currencyNew = userCurrencyAndPrice('', $planTargetNew);
            $base_price_new = PlanPrice::where('plan_id', $planIdNew)->where('currency', $currencyNew['currency'])->value('add_price') * $newAgents;

            if ($base_price_new > $base_priceOld) {
                $variables = $this->displayPriceNewGreaterThanOld($ends_at, $base_price_new, $base_priceOld, $planDaysNew, $planDaysOld);
            } elseif ($base_price_new == $base_priceOld) {
                if ($this->isExpired($ends_at)) {
                    $variables = ['price' => $base_price_new, 'priceRemaining' => 0, 'priceToBePaid' => $base_price_new];
                } else {
                    $variables = ['price' => 0, 'priceRemaining' => 0, 'priceToBePaid' => 0];
                }
            } else {
                $variables = $this->displayPriceNewLessThanOld($ends_at, $base_price_new, $base_priceOld, $planDaysNew, $planDaysOld);
                $discount = $variables['discount'] ?? 0;
            }

            return [
                'priceoldplan' => currencyFormat($variables['priceRemaining'], $currencyNew['currency'], includeSymbol: true),
                'pricenewplan' => currencyFormat($variables['priceToBePaid'], $currencyNew['currency'], includeSymbol: true),
                'price_to_be_paid' => currencyFormat(abs($variables['price']), $currencyNew['currency'], includeSymbol: true),
                'discount' => currencyFormat($discount, $currencyNew['currency'], includeSymbol: true),
                'priceperagent' => currencyFormat($pricePerAgent, $currencyNew['currency'], includeSymbol: true),
            ];
        } catch (Exception $exception) {
            Logger::exception($exception);

            return ['price_to_be_paid' => 'NaN', 'discount' => 'NaN', 'currency' => 'NaN'];
        }
    }

    /**
     * @return array<mixed>
     */
    private function displayPriceNewGreaterThanOld(string $ends_at, int|float $base_price_new, int|float $base_priceOld, int $planDaysNew, int $planDaysOld): array
    {
        if ($this->isExpired($ends_at)) {
            return ['price' => $base_price_new, 'priceRemaining' => 0, 'priceToBePaid' => $base_price_new];
        }

        $pricePerDayNew = $base_price_new / $planDaysNew;
        $pricePerDayOld = $base_priceOld / $planDaysOld;
        $daysRemain = $this->daysRemaining($ends_at);

        if ($planDaysNew !== $planDaysOld) {
            $daysRemainNewFinal = $planDaysNew - ($planDaysOld - $daysRemain);
            $priceToBePaid = $pricePerDayNew * $daysRemainNewFinal;
            $priceRemaining = $pricePerDayOld * $daysRemain;
        } else {
            $priceToBePaid = $pricePerDayNew * $daysRemain;
            $priceRemaining = $pricePerDayOld * $daysRemain;
        }

        return ['price' => $priceToBePaid - $priceRemaining, 'priceRemaining' => $priceRemaining, 'priceToBePaid' => $priceToBePaid];
    }

    /**
     * @return array<mixed>
     */
    private function displayPriceNewLessThanOld(string $ends_at, int|float $base_price_new, int|float $base_priceOld, int $planDaysNew, int $planDaysOld): array
    {
        $discount = 0;

        if ($this->isExpired($ends_at)) {
            return ['price' => $base_price_new, 'priceRemaining' => 0, 'priceToBePaid' => $base_price_new, 'discount' => 0];
        }

        $daysRemain = $this->daysRemaining($ends_at);
        $pricePerDayNew = $base_price_new / $planDaysNew;
        $pricePerDayOld = $base_priceOld / $planDaysOld;

        if ($planDaysOld !== $planDaysNew) {
            $variables = $this->displayNewPlanDaysNotEqualOld($daysRemain, $planDaysNew, $planDaysOld, $pricePerDayNew, $pricePerDayOld);
            $price = $variables['price'];
            $priceToBePaid = $variables['priceToBePaid'];
            $priceRemaining = $variables['priceRemaining'];
            $discount = $variables['discount'];
        } else {
            $priceToBePaid = $pricePerDayNew * $daysRemain;
            $priceRemaining = $pricePerDayOld * $daysRemain;
            if ($priceToBePaid > $priceRemaining) {
                $price = $priceToBePaid - $priceRemaining;
            } else {
                $discount = $priceRemaining - $priceToBePaid;
                $price = 0;
            }
        }

        return ['price' => $price, 'priceRemaining' => $priceRemaining, 'priceToBePaid' => $priceToBePaid, 'discount' => $discount];
    }

    /**
     * @return array<mixed>
     */
    private function displayNewPlanDaysNotEqualOld(int $daysRemain, int $planDaysNew, int $planDaysOld, float|int $pricePerDayForNewPlan, float|int $pricePerDayForOldPlan): array
    {
        $discount = 0;

        if ($daysRemain <= $planDaysNew && $planDaysOld > $planDaysNew) {
            $priceToBePaid = $pricePerDayForNewPlan * $daysRemain;
            $priceRemaining = $pricePerDayForOldPlan * $daysRemain;
        } else {
            $daysRemainNewFinal = $planDaysNew - ($planDaysOld - $daysRemain);
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

    /**
     * @return array<mixed>
     */
    public function getThePaymentCalculationDisplay(Request $request): array|JsonResponse
    {
        try {
            $newAgents = $request->get('number');
            $oldAgents = $request->get('oldAgents');

            if ($request->agentAction == 'decrease' && $oldAgents <= $newAgents) {
                return errorResponse(trans('message.agent_decrease_invalid'));
            }

            $orderId = $request->get('orderId');
            $planId = Subscription::where('order_id', $orderId)->value('plan_id');
            $product = Product::find(Plan::where('id', $planId)->value('product'));
            if (! $product instanceof Product) {
                throw new Exception('Product not found');
            }
            $plan = $product->planRelation->find($planId);
            $currency = userCurrencyAndPrice('', $plan);
            $ends_at = (string) Subscription::where('order_id', $orderId)->value('ends_at');
            $planDays = (int) Plan::where('id', $planId)->value('days');
            $base_price = PlanPrice::where('plan_id', $planId)->where('currency', $currency['currency'])->value('add_price');

            if (empty($newAgents)) {
                return ['pricePerAgent' => currencyFormat($base_price, $currency['currency'], includeSymbol: true), 'totalPrice' => 0, 'priceToPay' => 0];
            }

            $totalAgents = 0;
            $price = 0.0;
            switch ($request->agentAction) {
                case 'increase':
                    $totalAgents = $newAgents + $oldAgents;
                    $price = $this->newAgentgreaterthenOld($ends_at, $base_price, $totalAgents, $oldAgents, $planDays);
                    break;
                case 'decrease':
                    $totalAgents = $oldAgents - $newAgents;
                    $price = $this->newAgentlessthenOld($ends_at, $base_price, $totalAgents, $oldAgents, $planDays);
                    break;
            }

            return [
                'pricePerAgent' => currencyFormat($base_price, $currency['currency'], includeSymbol: true),
                'totalPrice' => currencyFormat($base_price * $totalAgents, $currency['currency'], includeSymbol: true),
                'priceToPay' => currencyFormat($price, $currency['currency'], includeSymbol: true),
            ];
        } catch (Exception $exception) {
            Logger::exception($exception);

            return errorResponse('', ['data' => ['pricePerAgent' => 'NaN', 'totalPrice' => 'NaN', 'priceToPay' => 'NaN']]); // @phpstan-ignore argument.type
        }
    }

    public function checkDomain(string $domain): object
    {
        $keys = ThirdPartyApp::where('app_name', 'faveo_app_key')->first(['app_key', 'app_secret']);

        if (! $keys || empty($keys->app_key)) {
            throw new Exception(__('message.something_bad'));
        }

        return $this->cloudApiPost('/checkDomain', ['domain' => $domain, 'key' => $keys->app_key]);
    }

    public function fetchData(Request $request): JsonResponse
    {
        try {
            $productPlanData = CloudProducts::with(['product', 'plan'])
                ->when($request->input('search-query'), fn ($q, string $s) => $q->whereHas('product', fn (Builder $q2) => $q2->where('name', 'like', sprintf('%%%s%%', $s))))
                ->orderBy($request->input('sort-field', 'updated_at'), $request->input('sort-order', 'desc'))
                ->paginate((int) $request->input('limit', 10));

            $productPlanData->getCollection()->transform(fn ($model): array => [
                'id' => $model->id,
                'cloud_product' => $model->product->name ?? null,
                'cloud_product_id' => $model->product->id ?? null,
                'cloud_product_key' => $model->cloud_product_key,
                'cloud_free_plan' => $model->plan->name ?? null,
                'cloud_free_plan_id' => $model->plan->id ?? null,
                'trial_status' => (bool) $model->trial_status,
            ]);

            return successResponse('', $productPlanData);
        } catch (Exception) {
            return errorResponse(__('message.something_went_wrong'));
        }
    }

    public function updateTrialStatus(Request $request): JsonResponse
    {
        try {
            $cloudProduct = CloudProducts::findOrFail($request->input('id'));
            if ($cloudProduct instanceof CloudProducts) {
                $cloudProduct->update(['trial_status' => $request->input('status')]);
            }

            $msg = Lang::get('message.trial_status_updated');

            return successResponse(is_array($msg) ? '' : $msg);
        } catch (Exception) {
            $msg = Lang::get('message.trial_status_error');

            return errorResponse(is_array($msg) ? '' : $msg);
        }
    }

    public function DeleteProductConfig(Request $request): JsonResponse
    {
        try {
            $cloudProduct = CloudProducts::findOrFail($request->input('id'));
            if ($cloudProduct instanceof CloudProducts) {
                $cloudProduct->delete();
            }

            return successResponse(trans('message.pop_delete'));
        } catch (Exception $exception) {
            return errorResponse($exception->getMessage());
        }
    }

    public function storeCloudDataCenter(Request $request): JsonResponse
    {
        $request->validate(['cloud_countries' => ['required'], 'cloud_state' => ['required']]);

        $countryName = Country::where('country_code_char2', strtoupper((string) $request->get('cloud_countries')))->value('country_name');
        $state = $request->get('cloud_state');
        $city = $request->get('cloud_city');
        $geo = empty($city)
            ? $this->getStateCoordinates(strtoupper((string) $request->get('cloud_countries')).'-'.$state)
            : $this->getStateCoordinates($city);
        $state = State::where('country_code', strtoupper((string) $request->get('cloud_countries')))->where('iso2', $state)->value('state_subdivision_name');

        if ($geo !== null && $geo !== []) {
            CloudDataCenters::create([
                'cloud_countries' => $countryName,
                'cloud_state' => $state,
                'cloud_city' => $city,
                'latitude' => $geo['latitude'],
                'longitude' => $geo['longitude'],
            ]);

            return successResponse(__('message.saved_data_center'));
        }

        return errorResponse(__('message.no_lat_or_long'));
    }

    /**
     * @return array<mixed>
     */
    private function getStateCoordinates(string $stateName): ?array
    {
        $stateName = str_replace(' ', '+', $stateName);
        $url = sprintf('https://nominatim.openstreetmap.org/search?q=%s&format=json&limit=1', $stateName);
        $response = new Client(['verify' => true])->get($url, ['headers' => ['Referer' => $url]]);
        $data = json_decode($response->getBody(), associative: true);

        if (empty($data)) {
            return null;
        }

        return ['latitude' => $data[0]['lat'], 'longitude' => $data[0]['lon']];
    }

    public function removeLocation(Request $request): JsonResponse
    {
        try {
            $location = Arr::first(explode(', ', $request->location_id));
            CloudDataCenters::where('cloud_state', $location)->orWhere('cloud_city', $location)->delete();

            return successResponse(trans('message.removed_datacenter'));
        } catch (Exception) {
            return errorResponse(trans('message.something_went_wrong'));
        }
    }

    private function authUser(): User
    {
        $user = Auth::user();
        if (! $user instanceof User) {
            throw new Exception('Unauthorized');
        }

        return $user;
    }
}
