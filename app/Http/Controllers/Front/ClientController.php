<?php

namespace App\Http\Controllers\Front;

use App\Enums\OrderAbility;
use App\Http\Controllers\Github\GithubApiController;
use App\Http\Controllers\User\AdvanceSearchController;
use App\License\Models\License;
use App\Model\Common\StatusSetting;
use App\Model\Order\CreditTransaction;
use App\Model\Order\Invoice;
use App\Model\Order\Order;
use App\Model\Order\Payment;
use App\Model\Payment\Plan;
use App\Model\Payment\PlanPrice;
use App\Model\Product\Product;
use App\Model\Product\ProductUpload;
use App\Model\Product\Subscription;
use App\Payment_log;
use App\Services\Payment\AutoRenewalActivationService;
use App\User;
use App\WhatsappIntegration;
use Auth;
use Exception;
use Illuminate\Contracts\Database\Query\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Gate;
use Logger;

class ClientController extends BaseClientController
{
    private const ONE_TIME = 'One Time';

    /**
     * @var User
     */
    public $user;

    /**
     * @var Invoice
     */
    public $invoice;

    /**
     * @var Order
     */
    public $order;

    /**
     * @var Subscription
     */
    public $subscription;

    /**
     * @var Payment
     */
    public $payment;

    public function __construct()
    {
        $this->middleware('auth');
        $user = new User;
        $this->user = $user;

        $invoice = new Invoice;
        $this->invoice = $invoice;

        $order = new Order;
        $this->order = $order;

        $subscription = new Subscription;
        $this->subscription = $subscription;

        $payment = new Payment;
        $this->payment = $payment;
    }

    /**
     *  Get all the invoices in data table.
     *
     *
     * @throws Exception
     */
    public function getInvoices(Request $request): JsonResponse
    {
        $query = Invoice::with([
            'orders:id,number',
            'allocations.payment:id,payment_status',
        ])
            ->select('id', 'number', 'date', 'grand_total', 'billing_pay', 'status', 'currency', 'is_renewed')
            ->where('user_id', Auth::id());

        // Dashboard "Pending invoices" tile links here with ?status=pending.
        $status = strtolower((string) $request->input('status', ''));
        if (in_array($status, ['pending', 'success', 'partially paid'], true)) {
            $query->where('status', $status);
        }

        $search = trim((string) $request->input('search-query', ''));

        if ($search !== '' && $search !== '0') {
            $query->where(function (Builder $q) use ($search): void {
                $q->where('number', 'like', likePattern($search))
                    ->orWhere('status', 'like', likePattern($search));
            });
        }

        $allowed = ['number' => 'number', 'date' => 'date', 'grand_total' => 'grand_total'];
        $sortCol = $allowed[$request->input('sort-field', 'date')] ?? 'date';
        $sortDir = $request->input('sort-order', 'desc') === 'asc' ? 'asc' : 'desc';
        $query->orderByField($sortCol, $sortDir);

        $paginated = $query->paginate((int) $request->input('limit', 10));

        $paginated->getCollection()->transform(function ($invoice): array {
            // The allocated amounts, not the payments' own totals — one payment
            // can be spread over several invoices.
            $paymentTotal = $invoice->allocations
                ->filter(fn ($a): bool => $a->payment?->payment_status === 'success')
                ->sum(fn ($a): float => (float) $a->amount);
            $paid = floatval($invoice->billing_pay ?? 0) + floatval($paymentTotal);
            $balance = max(0, floatval($invoice->grand_total) - $paid);
            $isPaid = strtolower($invoice->status ?? '') === 'success';

            return [
                'id' => $invoice->id,
                'number' => $invoice->number,
                'date' => $invoice->date,
                'is_renewed' => (bool) $invoice->is_renewed,
                'orders' => $invoice->orders->map(fn ($o): array => ['id' => $o->id, 'number' => $o->number])->values(),
                'grand_total' => currencyFormat($invoice->grand_total, $invoice->currency),
                'paid' => currencyFormat($paid, $invoice->currency),
                'balance' => currencyFormat($balance, $invoice->currency),
                'status' => $isPaid ? 'Paid' : 'Unpaid',
                'show_pay' => ! $isPaid && floatval($invoice->grand_total) > 0,
                'show_delete' => ! $isPaid && $invoice->orders->isEmpty(),
            ];
        });

        return successResponse('', $paginated);
    }

    /**
     * Client's credit balance, summed across all currencies, and the history
     * behind it read straight from the ledger. The old `credit_activity` table
     * is no longer consulted: it stopped gaining rows when credit moved to the
     * ledger, so it left the client staring at a balance with no explanation.
     */
    public function getCreditBalance(): JsonResponse
    {
        try {
            $user = Auth::user();
            if (! $user instanceof User) {
                return errorResponse(__('message.unauthorized_action'), 401);
            }

            $balance = new AdvanceSearchController()->getExtraAmt($user->id);

            $transactions = CreditTransaction::where('user_id', $user->id)
                ->orderByDesc('created_at')
                ->orderByDesc('id') // same-second rows would otherwise order arbitrarily
                ->limit(50)
                ->get();

            $invoiceNumbers = Invoice::whereIn('id', $transactions->pluck('invoice_id')->filter())
                ->pluck('number', 'id');

            return successResponse('', [
                'balance' => currencyFormat($balance, getCurrencyForClient($user->country), true),
                'activity' => $transactions->map(fn ($transaction): array => [
                    'text' => $this->creditActivityText($transaction, $invoiceNumbers[$transaction->invoice_id] ?? null),
                    'created_at' => $transaction->created_at,
                ])->values(),
            ]);
        } catch (Exception $exception) {
            Logger::exception($exception);

            return errorResponse(__('message.something_bad'));
        }
    }

    /**
     * One ledger row as a sentence for the client. Plain text on purpose — the
     * note half of it is written by an admin, and this is rendered in the
     * client's browser.
     */
    private function creditActivityText(CreditTransaction $transaction, ?string $invoiceNumber): string
    {
        $amount = currencyFormat(abs((float) $transaction->amount), $transaction->currency, true);

        $text = $transaction->type === CreditTransaction::TYPE_APPLIED_TO_INVOICE
            ? __('message.credit_history_applied', ['amount' => $amount, 'number' => $invoiceNumber ?? '—'])
            : __('message.credit_history_added', ['amount' => $amount]);

        return $transaction->note ? $text.' '.$transaction->note : $text;
    }

    /**
     * Single order's full detail (license, autorenew, WhatsApp, deploy info,
     * etc.) — split out from getClientOrder() below, which is list-only.
     */
    public function getClientOrderDetail(string $id): JsonResponse
    {
        $order = $this->getClientPanelOrdersData()->where('id', $id)->first();
        if (! $order) {
            return errorResponse(__('message.no_records_found'), 404);
        }

        $latestInvoice = $order->invoices->first();
        $user = Auth::user();
        if (! $user instanceof User) {
            return errorResponse(__('message.unauthorized_action'), 401);
        }

        $license = License::where('license_order_number', $order->number)->first(['license_domain', 'license_ip', 'license_machine_id']);

        // A plan/agent change terminates the old order and creates a new one
        // (see terminated_order_upgrade, written by CloudExtraActivities).
        // Surface that link both ways so the client isn't left looking at a
        // dead order with no explanation, or a new one they don't remember
        // creating themselves.
        $replacementOrderId = \DB::table('terminated_order_upgrade')->where('terminated_order_id', $order->id)->value('upgraded_order_id');
        $predecessorOrderId = \DB::table('terminated_order_upgrade')->where('upgraded_order_id', $order->id)->value('terminated_order_id');
        $replacementOrder = $replacementOrderId ? \App\Model\Order\Order::where('client', $order->client)->find((int) $replacementOrderId) : null;
        $predecessorOrder = $predecessorOrderId ? \App\Model\Order\Order::where('client', $order->client)->find((int) $predecessorOrderId) : null;

        return successResponse('', [
            'id' => $order->id,
            'number' => $order->number,
            'product_name' => $order->productRelation?->name,
            'product_id' => $order->productRelation?->id,
            'version' => $order->subscription?->version,
            'status' => $order->order_status,
            'order_date' => $order->created_at,
            'update_ends_at' => $order->subscription?->update_ends_at,
            'license_ends_at' => $order->subscription?->ends_at,
            'support_ends_at' => $order->subscription?->support_ends_at,
            'serial_key' => $order->serial_key,
            'license_mode' => $order->license_mode,
            'license_domain' => $license?->license_domain,
            'license_ip' => $license?->license_ip,
            'license_machine_id' => $license?->license_machine_id,
            'invoice_id' => $latestInvoice?->id,
            'invoice_number' => $latestInvoice?->number,
            'sub_id' => $order->subscription?->id,
            'agents' => agentCount($order->invoiceItem?->agents),
            'current_plan' => $order->subscription?->plan?->name,
            'client_id' => $order->client,
            'is_cloud' => in_array($order->productRelation?->id, cloudPopupProducts()),
            // Instance deleted from the order list — the order stays, its cloud settings don't apply.
            'is_cloud_deleted' => (bool) $order->subscription?->is_deleted,
            'autorenew_status' => (bool) $order->subscription?->autoRenew_status,
            'is_subscribed' => (bool) $order->subscription?->is_subscribed,
            'auto_renew_state' => $order->subscription?->autoRenewState() ?? 'inactive',
            'can_renew' => Gate::allows(OrderAbility::Renew, $order),
            'can_deploy' => Gate::allows(OrderAbility::Deploy, $order),
            'can_manage_cloud' => Gate::allows(OrderAbility::ManageCloud, $order),
            'can_reissue' => Gate::allows(OrderAbility::Reissue, $order),
            'can_use_license_file' => Gate::allows(OrderAbility::UseLicenseFile, $order),
            'autorenew_log' => Payment_log::where('order', $order->number)
                ->where('payment_type', 'Payment method updated')
                ->orderByDesc('id')
                ->first(['payment_method', 'date']),
            'available_gateways' => AutoRenewalActivationService::gatewaysFor((string) $user->country),
            'autorenewal_enabled' => AutoRenewalActivationService::gatewaysFor((string) $user->country) !== [],
            'whatsapp_enabled' => (bool) $order->productRelation?->whatsapp_integration,
            'whatsapp_signup_enabled' => (bool) StatusSetting::value('whatsapp_status'),
            'whatsapp_app_id' => WhatsappIntegration::first()?->app_id,
            'whatsapp_config_id' => WhatsappIntegration::first()?->config_id,
            'user' => [
                'name' => ucfirst($user->first_name ?? '').' '.ucfirst($user->last_name ?? ''),
                'email' => $user->email,
                'mobile' => ($user->mobile_code ? '(+'.$user->mobile_code.') ' : '').($user->mobile ?? ''),
                'address' => $user->address ?? '',
                'town' => $user->town ?? '',
                // Stored as codes, shown as names — same lookups the invoice uses.
                'state' => getStateByCode((string) $user->country, (string) $user->state)['name'] ?: (string) $user->state,
                'country' => getCountryByCode((string) $user->country) ?: (string) $user->country,
                'zip' => $user->zip ?? '',
            ],
            'has_deployable_uploads' => ProductUpload::where('product_id', $order->productRelation?->id)
                ->where('is_private', 0)
                ->whereNotNull('file')
                ->where('file', '!=', '')
                ->exists(),
            'manual_install_guide_url' => \App\Model\Common\Setting::where('id', 1)->value('help_docs_url'),
            'replacement_order' => $replacementOrder ? ['id' => $replacementOrder->id, 'number' => $replacementOrder->number] : null,
            'predecessor_order' => $predecessorOrder ? ['id' => $predecessorOrder->id, 'number' => $predecessorOrder->number] : null,
        ]);
    }

    /**
     * Paginated order list for the client's My Orders page.
     */
    public function getClientOrder(Request $request): JsonResponse
    {
        $query = $this->getClientPanelOrdersData();

        $search = trim((string) $request->input('search-query', ''));

        if ($search !== '' && $search !== '0') {
            $query->where(function (Builder $q) use ($search): void {
                $q->where('number', 'like', likePattern($search))
                    ->orWhereHas('productRelation', fn (Builder $pq) => $pq->where('name', 'like', likePattern($search)));
            });
        }

        // Dashboard's "Order Renewals" widget links here with this flag — same
        // criteria as its own count in clientDetails().
        if ($request->boolean('renewal')) {
            $query->needsRenewal();
        }

        $sortField = $request->input('sort-field', 'order_date');
        $sortDir = $request->input('sort-order', 'desc') === 'asc' ? 'asc' : 'desc';

        match ($sortField) {
            'number' => $query->orderBy('number', $sortDir),
            'product_name' => $query->orderBy(
                Product::select('name')->whereColumn('id', 'orders.product'),
                $sortDir
            ),
            'license_ends_at' => $query->orderBy(
                Subscription::select('ends_at')->whereColumn('order_id', 'orders.id')->limit(1),
                $sortDir
            ),
            default => $query->orderBy('created_at', $sortDir),
        };

        $paginated = $query->paginate((int) $request->input('limit', 10));

        $paginated->getCollection()->transform(function ($order): array {
            $latestInvoice = $order->invoices->first();
            $licenseEndsAt = (string) $order->subscription?->ends_at;

            return [
                'id' => $order->id,
                'number' => $order->number,
                'product_name' => $order->productRelation?->name,
                'version' => $order->subscription?->version,
                'status' => $order->order_status,
                'order_date' => $order->created_at,
                'license_ends_at' => $order->subscription?->ends_at,
                'agents' => agentCount($order->invoiceItem?->agents),
                'current_plan' => $order->subscription?->plan?->name,
                'product_id' => $order->productRelation?->id,
                'client_id' => $order->client,
                'invoice_number' => $latestInvoice?->number,
                'sub_id' => $order->subscription?->id,
                'is_cloud_deleted' => (bool) $order->subscription?->is_deleted,
                'is_terminated' => $order->order_status === 'Terminated',
                // Action buttons — decided by OrderPolicy, which the endpoints enforce too.
                'can_renew' => Gate::allows(OrderAbility::Renew, $order),
                'can_download' => Gate::allows(OrderAbility::Download, $order),
                'can_delete_cloud' => Gate::allows(OrderAbility::DeleteCloud, $order),
                // Same label the admin order page badges expiry dates with.
                'expiry_status' => strtotime($licenseEndsAt) > 1 ? getExpiryLabel($licenseEndsAt)['status'] : null,
            ];
        });

        return successResponse('', $paginated);
    }

    /**
     * Cloud settings data for the client order view (Vue cloud-settings tab).
     * Returns the current domain, agent count, plan, expiry and the plan list
     * used by the change-domain / change-agents / upgrade-downgrade modals.
     */
    public function getCloudSettings(int $orderId): JsonResponse
    {
        try {
            $user = Auth::user();
            if (! $user instanceof User) {
                return errorResponse(__('message.unauthorized_action'), 401);
            }
            $order = $this->getClientPanelOrdersData()->where('id', $orderId)->first();
            $product = $order?->productRelation;

            if (! $order || ! $product) {
                return errorResponse(__('message.no_records_found'));
            }

            $access = Gate::inspect(OrderAbility::ManageCloud, $order);
            if ($access->denied()) {
                return deniedResponse($access);
            }

            $currency = getCurrencyForClient($user->country);
            $subscription = $order->subscription;

            $installation_path = $order->installedDomains()->first();

            $currentAgents = ltrim(substr($order->serial_key, 12), '0');

            $planIdOld = $subscription?->plan_id;
            $planName = Plan::where('id', $planIdOld)->value('name');
            $pricePerAgent = PlanPrice::where('plan_id', $planIdOld)
                ->where('currency', $currency)->latest()->value('add_price');

            // Plans available for upgrade/downgrade (other cloud products' plans, free excluded).
            $plans = $this->planPriceProductRelation($product);
            $planIds = array_keys($plans);
            $plans = $this->planDetails($planIds, $user->country, $plans, $product);
            $planOptions = [];
            foreach ($plans as $pid => $pname) {
                $planOptions[] = ['id' => $pid, 'name' => $pname];
            }

            return successResponse('', [
                'order_id' => $order->id,
                'product_id' => $product->id,
                'sub_id' => $subscription?->id,
                'serial_key' => $order->serial_key,
                'installation_path' => $installation_path,
                'current_agents' => $currentAgents,
                'current_plan_id' => $planIdOld,
                'current_plan_name' => $planName,
                'is_free_plan' => $planName && stripos((string) $planName, 'free') !== false,
                'plan_expiry' => $subscription?->ends_at,
                'price_per_agent' => currencyFormat($pricePerAgent, $currency, includeSymbol: true),
                'plans' => $planOptions,
            ]);
        } catch (Exception $exception) {
            Logger::exception($exception);

            return errorResponse(__('message.something_bad'));
        }
    }

    public function renewPopupVue(Request $request, int $productid): JsonResponse
    {
        try {
            $user = Auth::user();
            if (! $user instanceof User) {
                return errorResponse(__('message.unauthorized_action'), 401);
            }
            $currency = getCurrencyForClient($user->country);

            // A recurring order can't renew onto a one-time (lifetime) plan; a one-time
            // order still needs its own plan here to renew updates/support.
            $order = Order::where('client', $user->id)->find($request->integer('order'));
            $isOneTime = (bool) $order?->subscription?->plan?->periods()->where('name', self::ONE_TIME)->exists();

            $plans = Plan::where('product', $productid)
                ->where('status', 1)
                ->where('days', '!=', 14)
                ->when(
                    $isOneTime,
                    fn ($q) => $q->whereHas('periods', fn ($p) => $p->where('name', self::ONE_TIME)),
                    fn ($q) => $q->whereDoesntHave('periods', fn ($p) => $p->where('name', self::ONE_TIME)),
                )
                ->with([
                    'planPrice' => fn ($q) => $q->where('currency', $currency)
                        ->where('renew_price', '!=', '0')])
                ->get()
                ->filter(fn ($plan) => $plan->planPrice->isNotEmpty());

            $planOptions = $plans->map(function ($plan) use ($currency): array {
                $planPrice = $plan->planPrice->first();
                $renewPrice = $planPrice instanceof PlanPrice ? $planPrice->renew_price : 0;

                return [
                    'id' => $plan->id,
                    'name' => $plan->name.' (Renewal price: '.currencyFormat($renewPrice, $currency, includeSymbol: true).')',
                ];
            })->values();

            return successResponse('', [
                'plans' => $planOptions,
                'user_id' => $user->id,
            ]);
        } catch (Exception $exception) {
            \Logger::exception($exception);

            return errorResponse(__('message.sorry_something_wrong'));
        }
    }

    public function getInvoicesByOrderId(Request $request, mixed $orderid, mixed $userid, mixed $admin = null): JsonResponse
    {
        try {
            if (! authorizeOwnership((int) $userid, allowAdmin: true)) {
                return errorResponse(__('message.unauthorized_action'), 403);
            }

            $order = Order::where('id', $orderid)->where('client', $userid)->firstOrFail();

            $invoiceIds = $order->invoices()->pluck('invoices.id');

            $allowed = ['number', 'date'];
            $sortCol = in_array($request->input('sort-field'), $allowed, true) ? $request->input('sort-field') : 'date';
            $sortDir = $request->input('sort-order', 'desc') === 'asc' ? 'asc' : 'desc';

            $paginated = Invoice::whereIn('id', $invoiceIds)
                ->select('id', 'number', 'date', 'grand_total', 'currency', 'status', 'is_renewed')
                ->orderBy($sortCol, $sortDir)
                ->paginate(10);

            $paginated->getCollection()->transform(fn ($model): array => [
                'id' => $model->id,
                'number' => $model->number,
                'date' => $model->date,
                'grand_total' => currencyFormat($model->grand_total, $model->currency),
                'status' => $model->status,
                'is_renewed' => (bool) $model->is_renewed,
            ]);

            return successResponse('', $paginated);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $exception) {
            return errorResponse(__('message.record_not_found'));
        } catch (Exception $exception) {
            \Logger::exception($exception);

            return errorResponse(__('message.sorry_something_wrong'));
        }
    }

    /**
     * Get list of all the versions from Filesystem.
     */
    public function getVersionList(Request $request, int $orderid): JsonResponse
    {
        try {
            $order = Order::with([
                'productRelation:id,github_owner,github_repository,type',
                'subscription:id,order_id,product_id,update_ends_at,is_deleted',
                'invoices' => fn ($q) => $q->select('invoices.id', 'invoices.number')->latest('invoices.id'),
            ])->where('id', $orderid)->where('client', Auth::id())->first();

            if (! $order) {
                return errorResponse(__('message.unauthorized_action'), 403);
            }

            $product = $order->productRelation;
            if (! $product instanceof Product) {
                return errorResponse(__('message.product_relation_not_found'), 404);
            }
            $access = Gate::inspect(OrderAbility::Download, $order);
            if ($access->denied()) {
                return deniedResponse($access);
            }

            if ($product->github_owner && $product->github_repository) {
                return $this->githubVersions($request, $order, $product);
            }

            return $this->uploadVersions($request, $order, $product);
        } catch (Exception $exception) {
            \Logger::exception($exception);

            return errorResponse(__('message.sorry_something_wrong'), 500);
        }
    }

    private function githubVersions(Request $request, Order $order, Product $product): JsonResponse
    {
        // Built here, not in the constructor: it loads the GitHub settings row and would
        // otherwise run (and could fail) on every client-panel request.
        $githubApi = resolve(GithubApiController::class);

        $items = collect($githubApi->releases($product->github_owner, $product->github_repository))
            ->map(fn (array $release): array => [
                'version' => (string) $release['tag_name'],
                'release_type' => empty($release['prerelease']) ? 'official' : 'pre_release',
                'name' => ucfirst((string) $release['name']),
                'description' => $release['body'] ?? '',
                'created_at' => $release['created_at'],
                'can_download' => Gate::allows(OrderAbility::DownloadVersion, [$order, $release['created_at'] ?? null]),
                'zipball_url' => $release['zipball_url'] ?? null,
            ]);

        // A download link costs one GitHub call each, so only rows actually shown get one:
        // this page of the table and the featured cards — never the whole release history.
        $withDownloadUrl = function (?array $item) use ($githubApi): ?array {
            if ($item === null) {
                return null;
            }

            $url = null;
            if ($item['can_download'] && $item['zipball_url']) {
                try {
                    $url = $githubApi->resolveDownloadUrl($item['zipball_url']);
                } catch (Exception) {
                    $url = null;
                }
            }

            unset($item['zipball_url']);

            return $item + ['download_url' => $url];
        };

        // The featured cards are picked from every release (ignoring the table's search), and
        // the "previous versions" table lists only the rest — never a card's version again.
        $newestFirst = $items->sortByDesc('created_at')->values();
        $latest = $newestFirst->first();
        $latestOfficial = ($latest['release_type'] ?? null) !== 'official' ? $newestFirst->firstWhere('release_type', 'official') : null;
        $featuredVersions = array_column(array_filter([$latest, $latestOfficial]), 'version');
        $items = $items->reject(fn (array $item): bool => in_array($item['version'], $featuredVersions, true));
        $hasPrevious = $items->isNotEmpty();

        $search = trim((string) $request->input('search-query', ''));
        if ($search !== '') {
            $items = $items->filter(fn (array $item): bool => stripos($item['version'].$item['name'], $search) !== false);
        }

        $allowed = ['version', 'name', 'created_at'];
        $sortField = $request->input('sort-field', 'created_at');
        $sortField = in_array($sortField, $allowed, true) ? $sortField : 'created_at';
        $sortDir = $request->input('sort-order', 'desc') === 'asc' ? 'asc' : 'desc';
        $items = $sortDir === 'asc' ? $items->sortBy($sortField) : $items->sortByDesc($sortField);

        $page = (int) $request->input('page', 1);
        $perPage = (int) $request->input('limit', 10);
        $paginator = new LengthAwarePaginator(
            $items->forPage($page, $perPage)->map($withDownloadUrl)->values(),
            $items->count(),
            $perPage,
            $page,
            ['path' => $request->url(), 'query' => $request->query()]
        );

        return successResponse('', $paginator->toArray() + $this->versionPopupMeta(
            $withDownloadUrl($latest),
            $withDownloadUrl($latestOfficial),
            $order->subscription,
            $hasPrevious,
        ));
    }

    /**
     * Extra fields for the version popup, beside the paginated table rows:
     *  - featured: the latest release; when that is an RC/Beta, the latest official one after it
     *    — so customers aren't left to guess among every past version.
     *  - has_previous: whether any version exists beyond the featured ones (the toggle hides otherwise).
     *  - updates_ended_on: drives the "updates ended" notice. Reaching this list after
     *    updates ended means old versions are kept (OrderPolicy::download), so no flag for that.
     *
     * @param  array<string, mixed>|null  $latest
     * @param  array<string, mixed>|null  $latestOfficial
     * @return array<string, mixed>
     */
    private function versionPopupMeta(?array $latest, ?array $latestOfficial, ?Subscription $subscription, bool $hasPrevious): array
    {
        $featured = [];

        if ($latest) {
            $featured = ($latest['release_type'] ?? null) !== 'official'
                ? array_filter([$latest, $latestOfficial])
                : [$latest];
        }

        $updatesEnd = $subscription?->update_ends_at;
        $ended = $updatesEnd && $updatesEnd !== '0000-00-00 00:00:00' && strtotime((string) $updatesEnd) < time();

        return [
            'featured' => $featured,
            'has_previous' => $hasPrevious,
            'updates_ended_on' => $ended ? $updatesEnd : null,
        ];
    }

    private function uploadVersions(Request $request, Order $order, Product $product): JsonResponse
    {
        $search = trim((string) $request->input('search-query', ''));
        $order->invoices->first()?->number;

        $all = ProductUpload::where('product_id', $product->id)
            ->where('is_private', 0)
            ->select('id', 'product_id', 'version', 'title', 'description', 'created_at', 'release_type');

        // Featured cards ignore the table's search — they're always the true latest releases —
        // and the "previous versions" table lists only the rest, never a card's version again.
        $latest = (clone $all)->latest('id')->first();
        $latestOfficial = $latest && $latest->release_type !== 'official'
            ? (clone $all)->where('release_type', 'official')->latest('id')->first()
            : null;
        $base = (clone $all)->whereKeyNot(array_filter([$latest?->id, $latestOfficial?->id]));
        $hasPrevious = (clone $base)->exists();

        if ($search !== '' && $search !== '0') {
            $base->where(function ($q) use ($search): void {
                $q->where('version', 'LIKE', likePattern($search))
                    ->orWhere('title', 'LIKE', likePattern($search))
                    ->orWhere('description', 'LIKE', likePattern($search));
            });
        }

        // Newest = last added (highest id). Sorting the version text would put v9 above v10,
        // so the Version column sorts by the same added order.
        $allowed = ['version' => 'id', 'name' => 'title', 'created_at' => 'created_at'];
        $sortCol = $allowed[$request->input('sort-field', '')] ?? 'id';
        $sortDir = $request->input('sort-order', 'desc') === 'asc' ? 'asc' : 'desc';
        // id breaks ties: bulk uploads share one timestamp, and without a fixed order a row
        // could slip between pages (missing from one, repeated on the next).
        $base->orderBy($sortCol, $sortDir)->orderBy('id', $sortDir);

        $paginator = $base->paginate((int) $request->input('limit', 10));

        $toRow = function ($version) use ($order): array {
            $canDownload = Gate::allows(OrderAbility::DownloadVersion, [$order, $version->created_at?->toDateTimeString()]);

            return [
                'version' => (string) $version->version,
                'release_type' => getPreReleaseStatusLabel($version->release_type),
                'name' => ucfirst((string) $version->title),
                'description' => ucfirst($version->description ?? ''),
                'created_at' => $version->created_at,
                'can_download' => $canDownload,
                'download_url' => $canDownload
                    ? url(sprintf('download/%s/%s', $order->id, $version->id))
                    : null,
            ];
        };
        $paginator->getCollection()->transform($toRow);

        return successResponse('', $paginator->toArray() + $this->versionPopupMeta(
            $latest ? $toRow($latest) : null,
            $latestOfficial ? $toRow($latestOfficial) : null,
            $order->subscription,
            $hasPrevious,
        ));
    }

    /**
     *  Gets all the order details for a particular user.
     *
     * @return \Illuminate\Database\Eloquent\Builder<Order>
     */
    public function getClientPanelOrdersData(): \Illuminate\Database\Eloquent\Builder
    {
        return Order::with([
            'productRelation:id,name,github_owner,github_repository,type,whatsapp_integration',
            'productRelation.licenseType.permissions', // OrderPolicy reads these per row
            'subscription:id,order_id,plan_id,version,update_ends_at,ends_at,support_ends_at,is_subscribed,is_deleted,autoRenew_status,rzp_subscription',
            'subscription.plan:id,name',
            'invoiceItem:id,agents',
            'invoices' => fn ($q) => $q->select('invoices.id', 'invoices.number')->latest('invoices.id'),
        ])
            ->where('client', Auth::id());
    }

    /**
     *  Returns to client profile page with needed variables.
     *
     * @throws Exception
     */
    public function profile(Request $request): JsonResponse
    {
        try {
            // With its timezone, or the profile's Timezone box always loads empty.
            $user = $this->user->with('timezone')->where('id', Auth::id())->first();

            return successResponse('', ['user' => $user]);
        } catch (Exception $exception) {
            \Logger::exception($exception);

            return errorResponse(__('message.sorry_something_wrong'));
        }
    }

    /**
     * Get plan name and id ,options for upgrading or downgrading the cloud plan.
     *
     * @return array<mixed>
     */
    private function planPriceProductRelation(Product $product): array
    {
        return Plan::query()->cloudPlanChangeOptions($product)->pluck('name', 'id')->toArray();
    }

    /**
     * Get renewal price for related plans.
     *
     * @param  array<mixed>  $planIds
     * @param  array<mixed>  $plans
     * @return array<mixed>
     */
    private function planDetails(array $planIds, string $userCountry, array $plans, Product $product): array
    {
        $currency = getCurrencyForClient($userCountry);

        $renewalPrices = PlanPrice::whereIn('plan_id', $planIds)
            ->where('currency', $currency)
            ->latest()
            ->pluck('renew_price', 'plan_id')
            ->toArray();

        foreach (array_keys($plans) as $planId) {
            if (! isset($renewalPrices[$planId])) {
                continue;
            }

            if (! in_array($product->id, cloudPopupProducts())) {
                continue;
            }

            $plans[$planId] .= ' (Plan price-per agent: '.currencyFormat($renewalPrices[$planId], $currency, includeSymbol: true).')';
        }

        // Add more cloud IDs until we have a generic way to differentiate
        if (in_array($product->id, cloudPopupProducts())) {
            return array_filter($plans, fn ($value): bool => stripos((string) $value, 'free') === false);
        }

        return $plans;
    }

    /**
     *  Returns to client individual orders with payment details as datatable.
     *
     *
     * @throws Exception
     */
    public function getPaymentByOrderIdClient(Request $request, int $orderid, int $userid): JsonResponse
    {
        try {
            if (! authorizeOwnership($userid, allowAdmin: true)) {
                return errorResponse(__('message.unauthorized_action'), 403);
            }

            $order = $this->order->where('id', $orderid)->where('client', $userid)->firstOrFail();

            $invoiceIds = $order->invoices()->pluck('invoices.id')->toArray();

            $allowed = ['payment_status', 'created_at'];
            $sortCol = in_array($request->input('sort-field'), $allowed, true) ? $request->input('sort-field') : 'created_at';
            $sortDir = $request->input('sort-order', 'desc') === 'asc' ? 'asc' : 'desc';

            $paginated = $this->payment::query()
                ->with(['invoices:id,number,currency'])
                ->whereHas('invoices', fn ($q) => $q->whereIn('invoices.id', $invoiceIds))
                ->orderBy($sortCol, $sortDir)
                ->paginate(10);

            $paginated->getCollection()->transform(function ($payment): array {
                $firstInvoice = $payment->invoices->first();
                $invoiceCurrency = $firstInvoice ? $firstInvoice->currency : '';

                return [
                    'id' => $payment->id,
                    'invoice_id' => $firstInvoice?->id,
                    'invoice_number' => $payment->invoices->pluck('number')->implode(', ') ?: '—',
                    'amount' => currencyFormat($payment->amount, $payment->currency ?: $invoiceCurrency),
                    'payment_method' => $payment->payment_method,
                    'payment_status' => $payment->payment_status,
                    'created_at' => $payment->created_at,
                ];
            });

            return successResponse('', $paginated);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $exception) {
            return errorResponse(__('message.record_not_found'));
        } catch (Exception $exception) {
            \Logger::exception($exception);

            return errorResponse(__('message.sorry_something_wrong'));
        }
    }

    public function getOrderInstallations(Request $request, int $orderid): JsonResponse
    {
        try {
            $order = Order::where('id', $orderid)->where('client', Auth::id())->firstOrFail();

            // Same source as the admin order page: the check-in log of every license this order has had.
            $query = $order->installationLogs();
            $search = trim((string) $request->input('search-query', ''));

            if ($search !== '' && $search !== '0') {
                $query->where(function ($q) use ($search): void {
                    $q->where('installation_logs.installation_domain', 'like', likePattern($search))
                        ->orWhere('installation_logs.installation_ip', 'like', likePattern($search));
                });
            }

            $allowed = ['installation_path' => 'installation_domain', 'installation_ip' => 'installation_ip', 'last_active' => 'installation_last_active_date'];
            $sortCol = $allowed[$request->input('sort-field', 'last_active')] ?? 'installation_last_active_date';
            $sortDir = $request->input('sort-order', 'desc') === 'asc' ? 'asc' : 'desc';
            $query->orderBy('installation_logs.'.$sortCol, $sortDir);

            $paginated = $query->paginate((int) $request->input('limit', 10));

            $paginated->getCollection()->transform(fn ($log): array => [
                'installation_path' => $log->installation_domain,
                'installation_ip' => $log->installation_ip,
                'version' => $log->version_number,
                'last_active' => $log->installation_last_active_date,
            ]);

            return successResponse('', $paginated);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $exception) {
            return errorResponse(__('message.record_not_found'));
        } catch (Exception $exception) {
            \Logger::exception($exception);

            return errorResponse(__('message.sorry_something_wrong'));
        }
    }

    public function deleteInvoice(int $id): JsonResponse
    {
        try {
            $invoice = Invoice::with('orders')->where('user_id', Auth::id())->findOrFail($id);

            // Any payment against it (full or partial) — deleting would orphan the payment records.
            if (strtolower($invoice->status) === 'success' || $invoice->allocations()->exists()) {
                return errorResponse(__('message.cannot_delete_paid_invoice'));
            }

            if ($invoice->orders->isNotEmpty()) {
                return errorResponse(__('message.cannot_delete_invoice_with_order'));
            }

            $invoice->delete();

            return successResponse(__('message.deleted-successfully'));
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $exception) {
            return errorResponse(__('message.record_not_found'));
        } catch (Exception $exception) {
            \Logger::exception($exception);

            return errorResponse(__('message.sorry_something_wrong'));
        }
    }

    public function clientDetails(): JsonResponse
    {
        $user = Auth::user();
        if (! $user instanceof User) {
            return errorResponse(__('message.unauthenticated'), 401);
        }

        return successResponse('', [
            'pending_invoices_count' => $user->invoice()->where('status', 'pending')->count(),
            'total_orders_count' => $user->order()->count(),
            'order_renewals_count' => $user->order()->needsRenewal()->count(),
        ]);
    }
}
