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
use App\Model\Order\CreditTransaction;
use App\Model\Order\Invoice;
use App\Model\Order\InvoiceItem;
use App\Model\Order\InvoiceTaxLine;
use App\Model\Order\Order;
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

    private Client $client;

    public function __construct(Client $client, FaveoCloud $cloud)
    {
        $this->client = $client;
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

        $response = $this->client->request('POST', $this->cloud->cloud_central_domain.$endpoint, [
            'form_params' => $data,
            'headers' => ['signature' => $hashedSignature],
        ]);

        return json_decode('{'.explode('{', (string) $response->getBody())[1]);
    }

    private function daysRemaining(string $ends_at): int
    {
        return (int) Date::now()->diffInDays(Date::parse($ends_at), absolute: false);
    }

    private function isExpired(string $ends_at): bool
    {
        return Date::now() >= Date::parse($ends_at);
    }

    private function checktheAgent(mixed $numberOfAgents, string $domain): mixed
    {
        $response = $this->client->request('POST', 'https://'.$domain.'/api/agent-check', [
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
            $this->validate($request, [
                'plan' => 'required|integer',
                'orderId' => 'required|integer',
            ]);

            // agents is deliberately not read from the request — see
            // calculatePlanChange()/licenseAgents(): the client cannot be
            // trusted to report its own agent count.
            $planId = (int) $request->input('plan');
            $order = $this->authorizedOrder((int) $request->input('orderId'));
            $plan = Plan::find($planId);

            $planDetails = userCurrencyAndPrice($this->authUser()->id, $plan);
            $calc = $this->calculatePlanChange($order, $planId);

            return [
                'priceoldplan' => currencyFormat($calc['priceoldplan'], $calc['currency'], includeSymbol: false),
                'pricenewplan' => currencyFormat($calc['pricenewplan'], $calc['currency'], includeSymbol: false),
                'price_to_be_paid' => currencyFormat(abs($calc['price']), $calc['currency'], includeSymbol: false),
                'discount' => currencyFormat($calc['discount'] ?? 0, $calc['currency'], includeSymbol: false),
                'priceperagent' => currencyFormat($planDetails['plan']->add_price ?? 0, $calc['currency'], includeSymbol: false),
                'currency_symbol' => \App\Model\Payment\Currency::where('code', $calc['currency'])->value('symbol') ?? $calc['currency'],
            ];
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

            $newDomain = $request->input('newDomain');
            $currentDomain = $request->input('currentDomain');

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
                'lic_code' => $request->input('lic_code'),
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
        $this->client->request('GET', config('custom.cloud_job_url'), [
            'auth' => [config('custom.cloud_user'), config('custom.cloud_auth')],
            'query' => ['token' => config('custom.cloud_oauth_token'), 'domain' => $newDomain],
        ]);
    }

    /**
     * Change an order's agent count to a target total. The UI asks for the
     * desired total (not an increase/decrease amount) — simpler for the
     * client, and it also means direction and delta are always derived from
     * the order's own license here, never trusted from the request.
     */
    public function agentAlteration(Request $request): JsonResponse
    {
        try {
            $this->validate($request, [
                'orderId' => 'required|integer',
                'desiredAgents' => 'required|integer|min:1',
            ]);

            $order = $this->authorizedOrder((int) $request->input('orderId'));
            $oldAgents = $this->licenseAgents($order->serial_key);
            $desiredAgents = (int) $request->input('desiredAgents');

            if ($desiredAgents === $oldAgents) {
                return errorResponse(trans('message.nothing_changed'));
            }

            $installationPath = Installation::where('license_code', $order->serial_key)
                ->where('installation_path', '!=', cloudCentralDomain())
                ->latest('updated_at')
                ->value('installation_path');

            if (empty($installationPath)) {
                return errorResponse(trans('message.installation_path_not_found'));
            }

            if ($this->checktheAgent($desiredAgents, $installationPath)) {
                return errorResponse(trans('message.agent_reduce'));
            }

            $agentAction = $desiredAgents > $oldAgents ? 'increase' : 'decrease';
            $delta = abs($desiredAgents - $oldAgents);

            $items = $this->getThePaymentCalculation($delta, $order->serial_key, $order->id, agentAction: $agentAction);
            $invoice = new RenewController()->renewBySubId($request->subId, $items['planId'], '', $items['price'], '', isAgentIncrease: false, agents: $desiredAgents);
            if (! $invoice instanceof InvoiceItem) {
                return errorResponse(trans('message.something_went_wrong'));
            }

            // Determine if subscription is expired — if so, renewal date extension is needed
            $sub = Subscription::where('order_id', $order->id)->first();
            $isExpired = $sub && Date::now() >= Date::parse($sub->ends_at);

            $dbInvoice = Invoice::find($invoice->invoice_id);
            if ($dbInvoice) {
                $dbInvoice->update([
                    'metadata' => [
                        'type' => 'agent_alteration',
                        'sub_id' => $request->subId,
                        'new_agents' => $desiredAgents,
                        'order_id' => $order->id,
                        'installation_path' => $installationPath,
                        'product_id' => $order->product,
                        'old_license' => $order->serial_key,
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
            $this->validate($request, [
                'id' => 'required|integer',
                'orderId' => 'required|integer',
            ]);

            $planId = (int) $request->input('id');
            // agents is deliberately not read from the request — see
            // calculatePlanChange()/licenseAgents(): the client cannot be
            // trusted to report its own agent count when it's the very thing
            // the price is multiplied by.
            $order = $this->authorizedOrder((int) $request->input('orderId'));

            $installationPath = Installation::where('license_code', $order->serial_key)
                ->where('installation_path', '!=', cloudCentralDomain())
                ->latest('updated_at')
                ->value('installation_path');

            $calc = $this->calculatePlanChange($order, $planId);
            $price = abs(round($calc['price']));
            // The new plan's actual prorated cost before old-plan credit is
            // applied — kept so the invoice can show a real product price
            // and a real "credit applied" line instead of just a bare 0
            // whenever the old plan's credit fully covers it.
            $grossPrice = round($calc['pricenewplan']);
            // Rounded here, at the point it's actually banked as credit —
            // the preview (getUpgradeCost) shows the unrounded value.
            $discount = $calc['discount'] !== null ? round($calc['discount']) : null;
            $productNew = $calc['product'];
            $currencyNew = $calc['currency'];

            if (! $productNew instanceof Product) {
                return errorResponse(trans('message.something_went_wrong'));
            }

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
                // What's discounted off THIS invoice (old-plan credit covering
                // some/all of the new plan's cost) — read by the checkout
                // page's generic discount row. Distinct from metadata.discount
                // below, which is the *leftover* credit banked for future use
                // (see doTheActivity) — that's not a discount on this invoice.
                'discount' => max(0, $grossPrice - $price),
                'metadata' => [
                    'type' => 'upgrade_downgrade',
                    'old_order_id' => $order->id,
                    'old_license' => $order->serial_key,
                    'installation_path' => $installationPath,
                    'discount' => $discount,
                ],
            ]);

            // No OrderInvoiceRelation to the OLD order here on purpose: this
            // invoice needs to create a genuinely NEW order for the new plan
            // once paid (via executeOrders() in PostPaymentService), and that
            // relies on Order::whereIn(OrderInvoiceRelation...) being empty to
            // know a new order hasn't been made yet. A relation created here
            // made that check see the old order and think fulfilment was
            // already done, so the new order/subscription was never created
            // and the old order got wrongly marked Terminated in its place.
            // old_order_id in metadata above already carries this linkage.

            $item = $invoiceCtrl->createInvoiceItemsByAdmin(
                $invoice->id, (string) $productNew->id, $grossPrice, $currencyNew,
                1, $this->licenseAgents($order->serial_key), $planId, $user->id,
                $tax['name'], (float) $tax['value'], $grossPrice
            );

            // createInvoiceItemsByAdmin computes its tax line off the item's
            // (gross) subtotal — correct it to tax-on-what's-actually-charged
            // so it doesn't show tax on an amount the client never pays.
            if ($item instanceof InvoiceItem) {
                InvoiceTaxLine::where('invoice_item_id', $item->id)
                    ->update(['amount' => round($price * (float) $tax['value'] / 100, 4)]);
            }

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
                    break;
                case 'decrease':
                    $totalAgents = $oldAgents - $newAgents;
                    break;
            }
            $price = $this->agentProration($ends_at, $base_price, $totalAgents, $oldAgents, $planDays)['price'];

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

    /**
     * Proration for changing an order's agent count, same formula regardless
     * of direction: what the CURRENT agent count is worth for the rest of
     * this billing cycle (priceRemaining), what the DESIRED agent count
     * would cost for that same remaining period (priceToBePaid), and the
     * difference (price) — never negative, since a decrease isn't refunded
     * mid-cycle, only not-charged. Exposing the two halves (not just the net
     * price) lets the UI show the client exactly what they're paying for
     * instead of a single unexplained number.
     *
     * @return array{price: float, priceRemaining: float, priceToBePaid: float}
     */
    private function agentProration(string $ends_at, float|int $base_price, int $newAgents, int $oldAgents, int $planDays): array
    {
        if ($this->isExpired($ends_at)) {
            $full = (float) ($base_price * $newAgents);

            return ['price' => $full, 'priceRemaining' => 0.0, 'priceToBePaid' => $full];
        }

        $daysRemain = $this->daysRemaining($ends_at);
        $pricePerDay = $base_price / $planDays;
        $priceRemaining = $pricePerDay * $oldAgents * $daysRemain;
        $priceToBePaid = $pricePerDay * $newAgents * $daysRemain;

        return [
            'price' => max(0.0, $priceToBePaid - $priceRemaining),
            'priceRemaining' => $priceRemaining,
            'priceToBePaid' => $priceToBePaid,
        ];
    }

    /** Agent count encoded in a cloud license's serial key — the source of truth for pricing; a client's own claim about its agent count is never trusted. */
    private function licenseAgents(?string $serialKey): int
    {
        return (int) substr((string) $serialKey, 12, 16);
    }

    /**
     * Load a cloud order and enforce it belongs to the signed-in user. Same
     * failure for "doesn't exist" and "not yours" so neither leaks which one
     * it was. Every endpoint that prices or mutates a specific order must
     * resolve it through here, never read $request->orderId's Order directly.
     */
    private function authorizedOrder(int $orderId): Order
    {
        $order = Order::find($orderId);
        if (! $order instanceof Order || $order->client != $this->authUser()->id) {
            throw new Exception('Unauthorized');
        }

        return $order;
    }

    /**
     * Proration for changing an existing cloud order to a different plan —
     * the one calculation both the pay page's preview ({@see getUpgradeCost})
     * and the actual charge ({@see upgradeDowngradeCloud}) use, so they can't
     * quietly disagree the way two hand-maintained copies of this formula
     * once did. Agent count is always read off the order's own license (a
     * plan swap doesn't change agents — that's the separate agentAlteration
     * flow), never taken from the request.
     *
     * @return array{price: float, discount: float|null, product: ?Product, currency: string, priceoldplan: float, pricenewplan: float}
     */
    private function calculatePlanChange(Order $order, int $planIdNew): array
    {
        $empty = ['price' => 0.0, 'discount' => null, 'product' => null, 'currency' => '', 'priceoldplan' => 0.0, 'pricenewplan' => 0.0];

        try {
            $sub = Subscription::where('order_id', $order->id)->first();
            if (! $sub) {
                return $empty;
            }

            $agents = $this->licenseAgents($order->serial_key);
            $ends_at = (string) $sub->ends_at;

            $planOld = Plan::with('productRelation')->find($sub->plan_id);
            $productOld = $planOld?->productRelation;
            if (! $planOld || ! $productOld) {
                return $empty;
            }
            $currencyOld = userCurrencyAndPrice('', $productOld->planRelation->find($sub->plan_id));
            $base_priceOld = PlanPrice::where('plan_id', $sub->plan_id)->where('currency', $currencyOld['currency'])->value('add_price') * $agents;
            $planDaysOld = (int) $planOld->days;

            $planNew = Plan::with('productRelation')->find($planIdNew);
            $productNew = $planNew?->productRelation;
            if (! $planNew || ! $productNew) {
                return $empty;
            }
            $currencyNew = userCurrencyAndPrice('', $productNew->planRelation->find($planIdNew));
            $base_price_new = PlanPrice::where('plan_id', $planIdNew)->where('currency', $currencyNew['currency'])->value('add_price') * $agents;
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
                'priceoldplan' => $result['priceRemaining'],
                'pricenewplan' => $result['priceToBePaid'],
            ];
        } catch (Exception $exception) {
            Logger::exception($exception);

            return $empty;
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
            // Old plan's remaining value covers the new plan's prorated cost —
            // nothing is due now; the excess is banked as credit afterward
            // (see doTheActivity), not charged.
            $discount = $priceRemaining - $priceToBePaid;
            User::where('id', $this->authUser()->id)->update(['billing_pay_balance' => 1]);
            $price = 0;
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
            // Same reasoning as lessPriceNewDaysEqualToOldDays above.
            $discount = $priceRemaining - $priceToBePaid;
            User::where('id', $this->authUser()->id)->update(['billing_pay_balance' => 1]);
            $price = 0;
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
        ?float $discount = null,
        ?string $currency = null
    ): void {
        $this->doTheActivity($terminatedOrderId, $newActiveOrderId, $discount, $currency);

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

    /**
     * Bank the unused portion of the old plan as spendable credit, in the
     * downgrade invoice's own currency (falls back to the client's country
     * currency when none is given, e.g. a caller outside the invoice flow).
     */
    public function doTheActivity(int $terminatedOrderId, int $newActiveOrderId, ?float $discount = null, ?string $currency = null): void
    {
        if ($discount === null || $discount <= 0) {
            return;
        }

        app(\App\Services\Payment\CreditBalanceService::class)->grant(
            $this->authUser()->id,
            $currency ?? getCurrencyForClient($this->authUser()->country),
            $discount,
            CreditTransaction::TYPE_DOWNGRADE_PRORATION,
            note: sprintf('Plan downgrade: terminated order #%d, new order #%d', $terminatedOrderId, $newActiveOrderId),
        );
    }

    /**
     * @return array<mixed>
     */
    public function getThePaymentCalculationDisplay(Request $request): array|JsonResponse
    {
        try {
            $this->validate($request, [
                'orderId' => 'required|integer',
                'desiredAgents' => 'required|integer|min:1',
            ]);

            $order = $this->authorizedOrder((int) $request->input('orderId'));
            $oldAgents = $this->licenseAgents($order->serial_key);
            $desiredAgents = (int) $request->input('desiredAgents');

            $planId = Subscription::where('order_id', $order->id)->value('plan_id');
            $product = Product::find(Plan::where('id', $planId)->value('product'));
            if (! $product instanceof Product) {
                throw new Exception('Product not found');
            }
            $plan = $product->planRelation->find($planId);
            $currency = userCurrencyAndPrice('', $plan);
            $ends_at = (string) Subscription::where('order_id', $order->id)->value('ends_at');
            $planDays = (int) Plan::where('id', $planId)->value('days');
            $base_price = PlanPrice::where('plan_id', $planId)->where('currency', $currency['currency'])->value('add_price');

            // A single net "price to pay" wasn't enough for the client to see
            // what they're actually paying for — so this also exposes the
            // two halves that net figure comes from: what the CURRENT agent
            // count is worth for the rest of this cycle (currentAgentsCost),
            // and what the DESIRED agent count would cost for that same
            // remaining period (newAgentsCost). priceToPay is just their
            // difference (never negative — see agentProration).
            $calc = $this->agentProration($ends_at, $base_price, $desiredAgents, $oldAgents, $planDays);

            return [
                'pricePerAgent' => currencyFormat($base_price, $currency['currency'], includeSymbol: false),
                'totalPrice' => currencyFormat($base_price * $desiredAgents, $currency['currency'], includeSymbol: false),
                'currentAgentsCost' => currencyFormat($calc['priceRemaining'], $currency['currency'], includeSymbol: false),
                'newAgentsCost' => currencyFormat($calc['priceToBePaid'], $currency['currency'], includeSymbol: false),
                'priceToPay' => currencyFormat($calc['price'], $currency['currency'], includeSymbol: false),
                'currency_symbol' => \App\Model\Payment\Currency::where('code', $currency['currency'])->value('symbol') ?? $currency['currency'],
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

        $countryName = Country::where('country_code_char2', strtoupper((string) $request->input('cloud_countries')))->value('country_name');
        $state = $request->input('cloud_state');
        $city = $request->input('cloud_city');
        $geo = empty($city)
            ? $this->getStateCoordinates(strtoupper((string) $request->input('cloud_countries')).'-'.$state)
            : $this->getStateCoordinates($city);
        $state = State::where('country_code', strtoupper((string) $request->input('cloud_countries')))->where('iso2', $state)->value('state_subdivision_name');

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
