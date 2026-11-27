<?php

namespace App\Http\Controllers\Tenancy;

use App\Enums\OrderAbility;
use App\Http\Controllers\Controller;
use App\Http\Controllers\Order\InvoiceController as InvoiceCtrl;
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
use App\Model\Payment\Currency;
use App\Model\Payment\Plan;
use App\Model\Payment\PlanPrice;
use App\Model\Product\CloudProducts;
use App\Model\Product\Product;
use App\Model\Product\Subscription;
use App\Services\AgentCountService;
use App\Services\Cloud\CloudApi;
use App\Services\EntitlementSalesService;
use App\Services\EntitlementService;
use App\Services\Payment\CreditBalanceService;
use App\Traits\TaxCalculation;
use App\User;
use DB;
use Exception;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Contracts\Database\Query\Builder;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\Gate;
use Lang;
use Logger;
use RuntimeException;

class CloudExtraActivities extends Controller
{
    use TaxCalculation;

    public mixed $cloud = null;

    private Client $client;

    public function __construct(Client $client, FaveoCloud $cloud)
    {
        $this->client = $client;
        $this->cloud = $cloud->first();
    }

    private function cloudApi(): CloudApi
    {
        return new CloudApi($this->client, $this->cloud);
    }

    /**
     * How long the new plan runs when the plan length changes mid-term. Moving to a LONGER
     * plan: its length less the days already used on the old one (development's formula).
     * Moving to a SHORTER plan: the days left, at most one new period — the old formula,
     * new − (old − left), goes negative once more of the old plan was used than the new
     * plan lasts (yearly → monthly with 200 days left: 30 − 165 = −135, an expiry in the
     * past and 135 phantom days credited).
     */
    private function newTermDays(int $planDaysNew, int $planDaysOld, int $daysRemain): int
    {
        return $planDaysOld > $planDaysNew
            ? min($daysRemain, $planDaysNew)
            : $planDaysNew - ($planDaysOld - $daysRemain);
    }

    private function daysRemaining(string $ends_at): int
    {
        return (int) Date::now()->diffInDays(Date::parse($ends_at), absolute: false);
    }

    private function isExpired(string $ends_at): bool
    {
        return Date::now() >= Date::parse($ends_at);
    }

    public function checktheAgent(mixed $numberOfAgents, string $domain): mixed
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
    public function getUpgradeCost(Request $request): array|JsonResponse
    {
        try {
            $this->validate($request, [
                'plan' => 'required|integer', // NOSONAR - Laravel validation rule, repeated by design
                'orderId' => 'required|integer',
            ]);

            // agents is deliberately not read from the request: the client cannot be
            // trusted to report its own agent count (see calculatePlanChange()).
            $planId = (int) $request->input('plan');
            // Same rule as the purchase, so a price is never shown for a change that would be refused.
            $order = $this->cloudOrder((int) $request->input('orderId'), OrderAbility::ChangeCloudPlan, $planId);
            if ($order instanceof JsonResponse) {
                return $order;
            }

            $plan = Plan::find($planId);

            $planDetails = userCurrencyAndPrice($this->authUser()->id, $plan);
            $calc = $this->calculatePlanChange($order, $planId);

            return [
                'priceoldplan' => currencyFormat($calc['priceoldplan'], $calc['currency'], includeSymbol: false),
                'pricenewplan' => currencyFormat($calc['pricenewplan'], $calc['currency'], includeSymbol: false),
                'price_to_be_paid' => currencyFormat(abs($calc['price']), $calc['currency'], includeSymbol: false),
                'discount' => currencyFormat($calc['discount'] ?? 0, $calc['currency'], includeSymbol: false),
                // Old plan's remaining value actually spent on the new plan;
                // the rest (discount) is banked as credit.
                'credit_applied' => currencyFormat((float) $calc['priceoldplan'] - (float) ($calc['discount'] ?? 0), $calc['currency'], includeSymbol: false),
                'priceperagent' => currencyFormat($planDetails['plan']->add_price ?? 0, $calc['currency'], includeSymbol: false),
                'currency_symbol' => Currency::where('code', $calc['currency'])->value('symbol') ?? $calc['currency'],
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
                'newDomain' => 'required',
            ], [
                'newDomain.required' => __('validation.new_domain_required'),
            ]);

            $order = $this->cloudOrder((int) $request->input('order_id'), OrderAbility::ManageCloud);
            if ($order instanceof JsonResponse) {
                return $order;
            }

            // The instance being moved is this order's own — never a domain, license or
            // product sent by the browser, which could point at someone else's instance.
            $currentDomain = $order->installedDomains()->first();
            if (empty($currentDomain)) {
                return errorResponse(trans('message.installation_path_not_found'));
            }

            $newDomain = $request->input('newDomain');

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

            $result = $this->cloudApi()->send('POST', '/changeDomain', [
                'currentDomain' => $currentDomain,
                'newDomain' => $newDomain,
                'lic_code' => $order->serial_key,
                'product_id' => $order->getRawOriginal('product'),
            ]);
            if (($result['status'] ?? null) !== 'success') {
                throw new Exception('Cloud changeDomain failed: '.json_encode($result));
            }

            \Cache::forget(TenantController::TENANTS_CACHE_KEY);
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

    public function upgradeDowngradeCloud(Request $request): JsonResponse
    {
        try {
            $this->validate($request, [
                'id' => 'required|integer',
                'orderId' => 'required|integer',
            ]);

            $planId = (int) $request->input('id');
            // agents is deliberately not read from the request: the client cannot be
            // trusted to report its own agent count (see calculatePlanChange()).
            // One change at a time on a live cloud order, and only to an offered plan — see
            // OrderPolicy::changeCloudPlan. A second unpaid invoice would credit the same old
            // plan again (each downgrade banks its leftover value).
            $order = $this->cloudOrder((int) $request->input('orderId'), OrderAbility::ChangeCloudPlan, $planId);
            if ($order instanceof JsonResponse) {
                return $order;
            }

            $installationPath = $order->installedDomains()->first();

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
            $termDays = $calc['days'] !== null ? ['increase-decrease-days' => (int) $calc['days']] : [];

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
                ] + ($calc['keepOldExpiry']
                    // Same-length swap: the new subscription keeps the old order's expiry.
                    ? ['increase-decrease-days-dont-cloud' => $order->id]
                    // Otherwise the remaining/new-term days it was priced for — not a fresh full period.
                    : $termDays),
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

            // The new order's license digits: what the new plan includes + the Agents extra it
            // carries there (the same carry the price used). An admin's Agents override carries
            // over, so its count stays (0 = unlimited). Else 0 for a flat or unlimited plan; a
            // plan that includes none never gets 0000.
            $planNew = Plan::findOrFail($planId);
            $agentCount = resolve(AgentCountService::class);
            $included = EntitlementService::includedAgents($planNew);
            $carried = collect(resolve(EntitlementSalesService::class)->carriedNumbers($order, $planNew, $currencyNew))
                ->first(fn (array $row): bool => $row['sold'] && EntitlementService::isAgents($row['item']));
            $agentsNew = match (true) {
                $agentCount->hasOverride($order) => $agentCount->total($order),
                $included === null => 0,
                default => max($included === 0 ? 1 : 0, $included + ($carried['quantity'] ?? 0)),
            };
            $item = $invoiceCtrl->createInvoiceItemsByAdmin(
                $invoice->id, (string) $productNew->id, $grossPrice, $currencyNew,
                1, $agentsNew, $planId, $user->id,
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
     * The order a cloud action is for, or the refusal to send back. OrderPolicy decides
     * (owner, live cloud instance, nothing pending, an offered plan…); "doesn't exist"
     * and "not yours" get the same 403 so neither leaks which it was. Every endpoint that
     * prices or changes a cloud order resolves it here.
     */
    private function cloudOrder(int $orderId, OrderAbility $ability, mixed ...$arguments): Order|JsonResponse
    {
        $order = Order::find($orderId);
        if (! $order instanceof Order) {
            return errorResponse(__('message.unauthorized_action'), 403);
        }

        $access = Gate::inspect($ability, [$order, ...$arguments]);

        return $access->allowed() ? $order : deniedResponse($access);
    }

    /**
     * Proration for changing an existing cloud order to a different plan —
     * the one calculation both the pay page's preview ({@see getUpgradeCost})
     * and the actual charge ({@see upgradeDowngradeCloud}) use, so they can't
     * quietly disagree the way two hand-maintained copies of this formula
     * once did. Agents are priced as the order's Agents extra, never taken
     * from the request.
     *
     * @return array{price: float, discount: float|null, product: ?Product, currency: string, priceoldplan: float, pricenewplan: float, days: int|null, keepOldExpiry: bool}
     */
    private function calculatePlanChange(Order $order, int $planIdNew): array
    {
        $empty = ['price' => 0.0, 'discount' => null, 'product' => null, 'currency' => '', 'priceoldplan' => 0.0, 'pricenewplan' => 0.0, 'days' => null, 'keepOldExpiry' => false];

        try {
            $sub = Subscription::where('order_id', $order->id)->first();
            if (! $sub) {
                return $empty;
            }

            $ends_at = (string) $sub->ends_at;

            $planOld = Plan::with('productRelation')->find($sub->plan_id);
            $productOld = $planOld?->productRelation;
            if (! $planOld || ! $productOld) {
                return $empty;
            }

            $currencyOld = userCurrencyAndPrice('', $productOld->planRelation->find($sub->plan_id));
            $base_priceOld = $this->planValue($order, $planOld, $currencyOld['currency'], moving: false);
            $planDaysOld = (int) $planOld->days;

            $planNew = Plan::with('productRelation')->find($planIdNew);
            $productNew = $planNew?->productRelation;
            if (! $planNew || ! $productNew) {
                return $empty;
            }

            $currencyNew = userCurrencyAndPrice('', $productNew->planRelation->find($planIdNew));
            $base_price_new = $this->planValue($order, $planNew, $currencyNew['currency'], moving: true);
            $planDaysNew = (int) $planNew->days;

            if ($base_price_new > $base_priceOld) {
                $result = $this->newPriceGreaterThanOld($ends_at, $base_price_new, $planDaysNew, $base_priceOld, $planDaysOld);
            } elseif ($base_price_new == $base_priceOld) {
                $result = $this->newPriceEqualToOld($ends_at, $base_price_new, $planDaysNew, $planDaysOld);
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
                // How long the new subscription runs (development kept these in the session).
                'days' => $result['days'] ?? null,
                'keepOldExpiry' => $result['keepOldExpiry'] ?? false,
            ];
        } catch (Exception $exception) {
            Logger::exception($exception);

            return $empty;
        }
    }

    /**
     * One full period of $plan for this order, as the shop prices it: the plan price (offer
     * applied) plus the extras the order carries onto it. Comparing old and new on this footing
     * charges an upgrade for the extras' new term and credits a downgrade only what was actually
     * paid, not the list price. On the plan the order is $moving to, its extras are the ones it
     * will carry there ({@see EntitlementSalesService::carriedNumbers()}).
     */
    private function planValue(Order $order, Plan $plan, string $currency, bool $moving = true): float
    {
        $row = PlanPrice::where('plan_id', $plan->id)->where('currency', $currency)->first();
        if (! $row) {
            return 0.0;
        }
        $sales = resolve(EntitlementSalesService::class);

        return $sales->planPrice($row, 'purchase')
            + ($moving ? $sales->movedExtrasValue($order, $plan, $currency) : $sales->extrasValue($order, $currency, $plan));
    }

    /**
     * @return array<mixed>
     */
    private function newPriceLessThanOld(string $ends_at, int|float $base_price_new, int|float $base_priceOld, int $planDaysNew, int $planDaysOld): array
    {
        if ($this->isExpired($ends_at)) {
            return ['price' => $base_price_new, 'priceRemaining' => 0, 'priceToBePaid' => $base_price_new, 'discount' => null, 'days' => $planDaysNew];
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

        return ['price' => $price, 'priceRemaining' => $priceRemaining, 'priceToBePaid' => $priceToBePaid, 'discount' => $discount, 'keepOldExpiry' => true];
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
            $term = ['keepOldExpiry' => true];
        } else {
            $days = $this->newTermDays($planDaysNew, $planDaysOld, $daysRemain);
            $priceToBePaid = $pricePerDayForNewPlan * $days;
            $priceRemaining = $pricePerDayForOldPlan * $daysRemain;
            $term = ['days' => $days];
        }

        if ($priceToBePaid > $priceRemaining) {
            $price = $priceToBePaid - $priceRemaining;
        } else {
            // Same reasoning as lessPriceNewDaysEqualToOldDays above.
            $discount = $priceRemaining - $priceToBePaid;
            User::where('id', $this->authUser()->id)->update(['billing_pay_balance' => 1]);
            $price = 0;
        }

        return ['price' => $price, 'priceRemaining' => $priceRemaining, 'priceToBePaid' => $priceToBePaid, 'discount' => $discount] + $term;
    }

    /**
     * @return array<mixed>
     */
    private function newPriceGreaterThanOld(string $ends_at, int|float $base_price_new, int $planDaysNew, int|float $base_priceOld, int $planDaysOld): array
    {
        if ($this->isExpired($ends_at)) {
            return ['price' => $base_price_new, 'priceRemaining' => 0, 'priceToBePaid' => $base_price_new, 'discount' => null, 'days' => $planDaysNew];
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
        $days = $this->newTermDays($planDaysNew, $planDaysOld, $daysRemain);
        $priceToBePaid = $pricePerDayNew * $days;
        $priceRemaining = $pricePerDayOld * $daysRemain;

        return ['price' => $priceToBePaid - $priceRemaining, 'priceRemaining' => $priceRemaining, 'priceToBePaid' => $priceToBePaid, 'discount' => null, 'days' => $days];
    }

    /**
     * @return array<mixed>
     */
    private function newPlanDaysEqualToOld(int $daysRemain, int|float $pricePerDayNew, int|float $pricePerDayOld): array
    {
        $priceToBePaid = $pricePerDayNew * $daysRemain;
        $priceRemaining = $pricePerDayOld * $daysRemain;

        return ['price' => $priceToBePaid - $priceRemaining, 'priceRemaining' => $priceRemaining, 'priceToBePaid' => $priceToBePaid, 'discount' => null, 'keepOldExpiry' => true];
    }

    /**
     * @return array<mixed>
     */
    private function newPriceEqualToOld(string $ends_at, int|float $base_price_new, int $planDaysNew, int $planDaysOld): array
    {
        if ($this->isExpired($ends_at)) {
            return ['price' => $base_price_new, 'priceRemaining' => 0, 'priceToBePaid' => $base_price_new, 'discount' => null, 'days' => $planDaysNew];
        }

        // Free swap; only the term changes — development's currentDateLessThanEndDate().
        $daysRemain = $this->daysRemaining($ends_at);
        $term = match (true) {
            $planDaysNew === $planDaysOld => ['keepOldExpiry' => true],
            default => ['days' => $this->newTermDays($planDaysNew, $planDaysOld, $daysRemain)],
        };

        return ['price' => 0, 'priceRemaining' => 0, 'priceToBePaid' => 0, 'discount' => null] + $term;
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

        // The new order's license is created in the payment transaction, and the tenant
        // verifies it against billing on a separate connection — so it must be committed
        // first, or the tenant gets "license not found" (after already dropping its old one).
        DB::afterCommit(function () use ($licenseCode, $installationPath, $productID, $oldLicenseCode, $terminatedOrderId, $newActiveOrderId): void {
            try {
                $result = $this->cloudApi()->send('POST', '/performProductUpgradeOrDowngrade', [
                    'licenseCode' => $licenseCode,
                    'installation_path' => $installationPath,
                    'product_id' => $productID,
                    'old_lic_code' => $oldLicenseCode,
                ]);
            } catch (Exception $exception) {
                $result = ['error' => $exception->getMessage()];
            }

            // Paid for already, so billing still switches orders; the instance needs fixing by hand.
            if (($result['status'] ?? null) !== 'success') {
                Logger::exception(new Exception(sprintf('Cloud plan change failed for %s (order %d → %d), apply it manually: %s', $installationPath, $terminatedOrderId, $newActiveOrderId, json_encode($result))));

                return;
            }

            // The tenant runs on the new license now; the old one would otherwise still
            // install and verify anywhere until its own expiry. Kept active on failure so
            // the tenant isn't left unlicensed while the change is applied by hand.
            resolve(LicenseService::class)->deactivate($oldLicenseCode);
        });

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

        // The order's customer — never the logged-in user: this also runs when an admin records
        // the payment (the credit landed on the admin's account) and from webhooks (no user at all).
        $client = User::findOrFail((int) Order::whereKey($terminatedOrderId)->value('client'));

        resolve(CreditBalanceService::class)->grant(
            $client->id,
            $currency ?? getCurrencyForClient((string) $client->country),
            $discount,
            CreditTransaction::TYPE_DOWNGRADE_PRORATION,
            // Order numbers, not ids: this note is shown to the client in their credit history.
            note: sprintf(
                'Plan downgrade: terminated order #%s, new order #%s',
                Order::whereKey($terminatedOrderId)->value('number') ?? $terminatedOrderId,
                Order::whereKey($newActiveOrderId)->value('number') ?? $newActiveOrderId,
            ),
        );
    }

    /**
     * Whether a cloud domain is still free. The cloud answers "true"/"false"
     * ahead of a JSON body, so only that leading part is read.
     */
    public function checkDomain(string $domain): bool
    {
        $api = $this->cloudApi();
        $key = $api->keys()->app_key;

        try {
            $body = $api->raw('POST', '/checkDomain', ['form_params' => ['domain' => $domain, 'key' => $key]]);
        } catch (GuzzleException $guzzleException) {
            // Guzzle errors are RuntimeExceptions carrying the raw response HTML — never surface that.
            Logger::exception($guzzleException);

            throw new RuntimeException(__('message.cloud_server_unavailable'), $guzzleException->getCode(), $guzzleException);
        }

        $flag = strstr($body, '{', before_needle: true);

        // Same truthiness as development; a body starting with "{" gives an empty flag → null → taken.
        return (bool) json_decode($flag === false ? $body : $flag);
    }

    public function fetchData(Request $request): JsonResponse
    {
        try {
            $sortField = $request->input('sort-field', 'updated_at');
            $sortOrder = $request->input('sort-order', 'desc');

            $query = CloudProducts::with(['product', 'plan'])
                ->when($request->input('search-query'), fn ($q, string $s) => $q->whereHas('product', fn (Builder $q2) => $q2->where('name', 'like', likePattern($s))));

            // cloud_product/cloud_free_plan display the related name (see transform
            // below), not the raw FK — sort by that same name via a correlated
            // subquery instead of ordering by the meaningless FK id.
            match ($sortField) {
                'cloud_product' => $query->orderBy(Product::select('name')->whereColumn('id', 'cloud_products.cloud_product'), $sortOrder),
                'cloud_free_plan' => $query->orderBy(Plan::select('name')->whereColumn('id', 'cloud_products.cloud_free_plan'), $sortOrder),
                default => $query->orderBy($sortField, $sortOrder),
            };

            $productPlanData = $query->paginate((int) $request->input('limit', 10));

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
        } catch (ModelNotFoundException $exception) {
            return errorResponse(__('message.record_not_found'));
        } catch (Exception $exception) {
            Logger::exception($exception);

            return errorResponse(__('message.sorry_something_wrong'));
        }
    }

    public function storeCloudDataCenter(Request $request): JsonResponse
    {
        $request->validate(['cloud_countries' => ['required'], 'cloud_state' => ['required']]);

        $countryName = Country::where('country_code_char2', strtoupper((string) $request->input('cloud_countries')))->value('country_name');
        $stateCode = $request->input('cloud_state');
        $city = $request->input('cloud_city');
        $state = State::where('country_code', strtoupper((string) $request->input('cloud_countries')))->where('iso2', $stateCode)->value('state_subdivision_name');

        $exists = CloudDataCenters::where('cloud_countries', $countryName)
            ->where('cloud_state', $state)
            ->where(fn ($q) => filled($city) ? $q->where('cloud_city', $city) : $q->whereNull('cloud_city')->orWhere('cloud_city', ''))
            ->exists();
        if ($exists) {
            return errorResponse(__('message.data_center_exists'));
        }

        $geo = empty($city)
            ? $this->getStateCoordinates(strtoupper((string) $request->input('cloud_countries')).'-'.$stateCode)
            : $this->getStateCoordinates($city);

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
