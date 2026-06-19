<?php

namespace App\Http\Controllers\Front;

use App\Http\Controllers\Common\SettingsController;
use App\Http\Controllers\Github\GithubApiController;
use App\Http\Controllers\License\LicensePermissionsController;
use App\Http\Controllers\Order\RenewController;
use App\License\Models\Installation;
use App\Model\Common\Country;
use App\Model\Common\Setting;
use App\Model\Common\StatusSetting;
use App\Model\Github\Github;
use App\Model\Order\Invoice;
use App\Model\Order\InvoiceItem;
use App\Model\Order\InvoiceTaxLine;
use App\Model\Order\Order;
use App\Model\Order\Payment;
use App\Model\Payment\Plan;
use App\Model\Payment\PlanPrice;
use App\Model\Product\Product;
use App\Model\Product\ProductUpload;
use App\Model\Product\Subscription;
use App\Payment_log;
use App\Services\Payment\ProcessingFee;
use App\User;
use App\WhatsappIntegration;
use Auth;
use DB;
use Exception;
use Illuminate\Contracts\Database\Query\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Logger;
use Override;

class ClientController extends BaseClientController
{
    /**
     * @var \App\User
     */
    public $user;

    /**
     * @var \App\Model\Order\Invoice
     */
    public $invoice;

    /**
     * @var \App\Model\Order\Order
     */
    public $order;

    /**
     * @var \App\Model\Product\Subscription
     */
    public $subscription;

    /**
     * @var \App\Model\Order\Payment
     */
    public $payment;

    public function __construct()
    {
        $this->middleware('auth');
        $user = new User();
        $this->user = $user;

        $invoice = new Invoice();
        $this->invoice = $invoice;

        $order = new Order();
        $this->order = $order;

        $subscription = new Subscription();
        $this->subscription = $subscription;

        $payment = new Payment();
        $this->payment = $payment;

        $product_upload = new ProductUpload();
        $this->product_upload = $product_upload; // @phpstan-ignore property.notFound

        $product = new Product();
        $this->product = $product; // @phpstan-ignore property.notFound

        $github_controller = new GithubApiController();
        $this->github_api = $github_controller; // @phpstan-ignore property.notFound

        $model = new Github();
        $this->github = $model->firstOrFail(); // @phpstan-ignore property.notFound

        $this->client_id = $this->github->client_id; // @phpstan-ignore property.notFound
        $this->client_secret = $this->github->client_secret; // @phpstan-ignore property.notFound
    }

    /**
     *  Auto-renew by id and redirect to paynow page.
     */
    public function autoRenewbyid(): \Illuminate\Http\RedirectResponse
    {
        try {
            $id = request()->route('id');
            $order_id = DB::table('order_invoice_relations')->where('invoice_id', $id)->value('order_id');
            $sub = Subscription::where('order_id', $order_id)->first();
            if (!$sub instanceof Subscription) {
                throw new Exception('Subscription not found.');
            }
            $planid = $sub->plan_id;
            $plan = Plan::find($planid);
            $planDetails = userCurrencyAndPrice($sub->user_id, $plan);
            if (is_null($planDetails['plan'])) {
                throw new Exception(__('message.no_available_plans_currency'));
            }

            $cost = $planDetails['plan']->renew_price;
            $currency = $planDetails['currency'];
            $controller = new RenewController();
            $items = InvoiceItem::where('invoice_id', $id)->first();
            if (!$items instanceof InvoiceItem) {
                throw new Exception('Invoice item not found.');
            }
            $invoiceid = $items->invoice_id;
            // $this->setSession($id, $planid);

            return redirect('paynow/'.$id);
        } catch(Exception $exception) {
            return redirect('my-orders')->with('fails', $exception->getMessage());
        }
    }

    /**
     *  Get all the invoices in data table.
     *
     * @param  request  $request
     *
     * @throws Exception
     */
    public function getInvoices(Request $request): \Illuminate\Http\JsonResponse
    {
        $query = Invoice::with([
            'orders:id,number',
            'payment' => fn ($q) => $q->where('payment_status', 'success')->select('invoice_id', 'amount'),
        ])
            ->select('id', 'number', 'date', 'grand_total', 'billing_pay', 'status', 'currency', 'is_renewed')
            ->where('user_id', Auth::id());
        $search = trim((string) $request->input('search-query', ''));

        if ($search !== '' && $search !== '0') {
            $query->where(function (Builder $q) use ($search): void {
                $q->where('number', 'like', sprintf('%%%s%%', $search))
                  ->orWhere('status', 'like', sprintf('%%%s%%', $search));
            });
        }

        $allowed = ['number' => 'number', 'date' => 'date', 'grand_total' => 'grand_total'];
        $sortCol = $allowed[$request->input('sort-field', 'date')] ?? 'date';
        $sortDir = $request->input('sort-order', 'desc') === 'asc' ? 'asc' : 'desc';
        $query->orderBy($sortCol, $sortDir);

        $paginated = $query->paginate((int) $request->input('limit', 10));

        $paginated->getCollection()->transform(function ($invoice): array {
            $paymentTotal = $invoice->payment->sum('amount');
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
            ];
        });

        return successResponse('', $paginated);
    }

    public function getClientOrder(Request $request): \Illuminate\Http\JsonResponse
    {
        $query = $this->getClientPanelOrdersData();

        if ($id = $request->input('id')) {
            $order = (clone $query)->where('id', $id)->first();
            if (! $order) {
                return errorResponse(__('message.no_records_found'), 404);
            }

            $latestInvoice = $order->invoices->first();
            $user = Auth::user();
            if (!$user instanceof User) {
                return errorResponse('Unauthorized', 401);
            }

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
                'serial_key' => $order->serial_key,
                'invoice_id' => $latestInvoice?->id,
                'invoice_number' => $latestInvoice?->number,
                'sub_id' => $order->subscription?->id,
                'agents' => $order->invoiceItem?->agents,
                'current_plan' => $order->subscription?->plan?->name,
                'client_id' => $order->client,
                'is_cloud' => in_array($order->productRelation?->id, cloudPopupProducts()),
                'autorenew_status' => (bool) $order->subscription?->autoRenew_status,
                'is_subscribed' => (bool) $order->subscription?->is_subscribed,
                'autorenew_log' => Payment_log::where('order', $order->number)
                    ->where('payment_type', 'Payment method updated')
                    ->orderByDesc('id')
                    ->first(['payment_method', 'date']),
                'available_gateways' => $this->autoRenewalGateways($user->country),
                'autorenewal_enabled' => $this->autoRenewalGateways($user->country) !== [],
                'whatsapp_enabled' => (bool) $order->productRelation?->whatsapp_integration,
                'whatsapp_signup_enabled' => (bool) StatusSetting::value('whatsapp_status'),
                'whatsapp_app_id' => WhatsappIntegration::first()?->app_id,
                'whatsapp_config_id' => WhatsappIntegration::first()?->config_id,
                'user' => [
                    'name' => ucfirst($user->first_name ?? '').' '.ucfirst($user->last_name ?? ''),
                    'email' => $user->email,
                    'mobile' => ($user->mobile_code ? '(+'.$user->mobile_code.') ' : '').($user->mobile ?? ''),
                    'address' => $user->address ?? '',
                ],
            ]);
        }

        $search = trim((string) $request->input('search-query', ''));

        if ($search !== '' && $search !== '0') {
            $query->where(function (Builder $q) use ($search): void {
                $q->where('number', 'like', sprintf('%%%s%%', $search))
                  ->orWhereHas('productRelation', fn (Builder $pq) => $pq->where('name', 'like', sprintf('%%%s%%', $search)));
            });
        }

        $sortField = $request->input('sort-field', 'order_date');
        $sortDir = $request->input('sort-order', 'desc') === 'asc' ? 'asc' : 'desc';

        match ($sortField) {
            'number' => $query->orderBy('number', $sortDir),
            'update_ends_at' => $query->orderBy(
                Subscription::select('update_ends_at')->whereColumn('order_id', 'orders.id')->limit(1),
                $sortDir
            ),
            default => $query->orderBy('created_at', $sortDir),
        };

        $paginated = $query->paginate((int) $request->input('limit', 10));

        $productIds = $paginated->getCollection()->pluck('productRelation.id')->unique()->filter()->values()->toArray();
        $downloadPerms = [];
        foreach ($productIds as $pid) {
            $perms = LicensePermissionsController::getPermissionsForProduct((int) $pid);
            $downloadPerms[$pid] = $perms['downloadPermission'] == 1;
        }

        $paginated->getCollection()->transform(function ($order) use ($downloadPerms): array {
            $hasDownload = $downloadPerms[$order->productRelation?->id] ?? false;
            $latestInvoice = $order->invoices->first();

            return [
                'id' => $order->id,
                'number' => $order->number,
                'product_name' => $order->productRelation?->name,
                'version' => $order->subscription?->version,
                'status' => $order->order_status,
                'order_date' => $order->created_at,
                'update_ends_at' => $order->subscription?->update_ends_at,
                'agents' => $order->invoiceItem?->agents,
                'current_plan' => $order->subscription?->plan?->name,
                'product_id' => $order->productRelation?->id,
                'client_id' => $order->client,
                'invoice_number' => $latestInvoice?->number,
                'sub_id' => $order->subscription?->id,
                'show_download' => $hasDownload,
                'show_cloud_delete' => ! $hasDownload,
                'is_terminated' => $order->order_status === 'Terminated',
            ];
        });

        return successResponse('', $paginated);
    }

    /**
     * Cloud settings data for the client order view (Vue cloud-settings tab).
     * Returns the current domain, agent count, plan, expiry and the plan list
     * used by the change-domain / change-agents / upgrade-downgrade modals.
     *
     * @param  $orderId
     */
    public function getCloudSettings(int $orderId): JsonResponse
    {
        try {
            $user = Auth::user();
            if (!$user instanceof User) {
                return errorResponse('Unauthorized', 401);
            }
            $order = $this->getClientPanelOrdersData()->where('id', $orderId)->first();
            $product = $order?->productRelation;

            if (! $order || ! $product || ! in_array($product->id, cloudPopupProducts())) {
                return errorResponse(__('message.no_records_found'));
            }

            $currency = getCurrencyForClient($user->country);
            $subscription = $order->subscription;

            $installation_path = Installation::where('license_code', $order->serial_key)
                ->where('installation_path', '!=', cloudCentralDomain())
                ->latest('updated_at')->value('installation_path');

            $currentAgents = ltrim(substr($order->serial_key, 12), '0');

            $planIdOld = $subscription?->plan_id;
            $planName = Plan::where('id', $planIdOld)->value('name');
            $pricePerAgent = PlanPrice::where('plan_id', $planIdOld)
                ->where('currency', $currency)->latest()->value('add_price');

            // Plans available for upgrade/downgrade (other cloud products' plans, free excluded).
            $plans = $this->planPriceProductRelation($product);
            $planIds = array_keys($plans);
            $countryids = Country::where('country_code_char2', $user->country)->first();
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

    public function renewPopupVue(Request $request, int $productid): \Illuminate\Http\JsonResponse
    {
        try {
            $user = Auth::user();
            if (!$user instanceof User) {
                return errorResponse('Unauthorized', 401);
            }
            $currency = getCurrencyForClient($user->country);

            $plans = Plan::where('product', $productid)
                ->where('status', 1)
                ->where('days', '!=', 14)
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
            return errorResponse($exception->getMessage());
        }
    }

    #[Override]
    public function getInvoicesByOrderId(mixed $orderid, mixed $userid, mixed $admin = null): \Illuminate\Http\JsonResponse
    {
        try {
            if (! authorizeOwnership((int) $userid, allowAdmin: true)) {
                return errorResponse(__('message.unauthorized_action'), 403);
            }

            $order = Order::where('id', $orderid)->where('client', $userid)->firstOrFail();

            $invoiceIds = $order->invoices()->pluck('invoices.id');

            $paginated = Invoice::whereIn('id', $invoiceIds)
                ->select('id', 'number', 'date', 'grand_total', 'currency', 'status')
                ->orderBy('date', 'desc')
                ->paginate(10);

            $paginated->getCollection()->transform(fn ($model): array => [
                'id' => $model->id,
                'number' => $model->number,
                'date' => $model->date,
                'grand_total' => currencyFormat($model->grand_total, $model->currency),
                'status' => $model->status,
            ]);

            return successResponse('', $paginated);
        } catch (Exception $exception) {
            return errorResponse($exception->getMessage());
        }
    }

    /**
     * @return array<mixed>
     */
    public function prepareInvoiceData(\App\Model\Order\Invoice $invoice, ?\App\User $user = null): array
    {
        $payments = $invoice->payment;
        $user ??= Auth::user();
        $items = $invoice->invoiceItem()->get();

        $orderIDs = $invoice->orderRelation()->pluck('order_id')->toArray();

        $items->each(function ($item) use ($orderIDs): void {
            $order = Order::whereIn('id', $orderIDs)
                ->where('product', $item->product_id)
                ->first();

            $item->order = $order; // @phpstan-ignore assign.propertyReadOnly
        });
        $order = $this->order->getOrderLink($invoice->orderRelation()->value('order_id'), 'my-order');
        $set = Setting::find(1);
        $date = getDateHtml($invoice->date);
        $symbol = $invoice->currency;

        switch ($invoice->status) {
            case 'Success':
                $statusClass = 'text-success';
                $statusText = 'PAID';
                break;
            case 'partially paid':
                $statusClass = 'text-warning';
                $statusText = 'Partially paid';
                break;
            default:
                $statusClass = 'text-fail';
                $statusText = 'Unpaid';
        }

        // ==== CALCULATIONS ====

        $itemsSubtotal = 0;
        $taxAmt = 0;

        foreach ($items as $item) {
            $itemsSubtotal += floatval($item->subtotal);

            if ($item->tax_name != 'null') {
                $taxAmt += floatval($item->subtotal);
            }
        }

        // Tax breakdown from the persisted invoice_tax_lines, grouped per tax.
        $gstSplit = [];

        foreach (InvoiceTaxLine::where('invoice_id', $invoice->id)->get()->groupBy('label') as $label => $lines) {
            $amount = (float) $lines->sum('amount');
            $firstLine = $lines->first();
            $rate = $firstLine instanceof InvoiceTaxLine ? (float) $firstLine->rate : 0.0;
            $percentage = rtrim(rtrim(number_format($rate, 2, '.', ''), '0'), '.').'%';

            $gstSplit[] = [
                'name' => $label,
                'percentage' => $percentage,
                'labels' => [$label.'@'.$percentage],
                'values' => [currencyFormat($amount, $invoice->currency)],
            ];
        }

        // grand_total is stored fee-inclusive, so the fee is the part of it above
        // the pre-fee total — reverse it out (matches Order\InvoiceController).
        $processingFeeAmount = ProcessingFee::fromInclusive((float) $invoice->grand_total, $invoice->processing_fee);
        $base64 = '';
        if ($set && $set->logo) {
            $type = pathinfo((string) $set->logo, PATHINFO_EXTENSION);
            $logoData = file_get_contents($set->logo);
            if ($logoData !== false) {
                $base64 = 'data:image/'.$type.';base64,'.base64_encode($logoData);
            }
        }

        return compact(
            'payments',
            'user',
            'items',
            'order',
            'set',
            'date',
            'symbol',
            'statusClass',
            'statusText',
            'itemsSubtotal',
            'taxAmt',
            'gstSplit',
            'processingFeeAmount',
            'base64'
        );
    }

    /**
     * Get list of all the versions from Filesystem.
     */
    public function getVersionList(Request $request, int $orderid): \Illuminate\Http\JsonResponse
    {
        try {
            $order = Order::with([
                'productRelation:id,github_owner,github_repository',
                'subscription:id,order_id,product_id,update_ends_at',
                'invoices' => fn ($q) => $q->select('invoices.id', 'invoices.number')->latest('invoices.id'),
            ])->where('id', $orderid)->where('client', Auth::id())->first();

            if (! $order) {
                return errorResponse(__('message.unauthorized_action'), 403);
            }

            $product = $order->productRelation;
            if (!$product instanceof Product) {
                return errorResponse('Product relation not found.', 404);
            }
            $subscription = $order->subscription;

            if ($product->github_owner && $product->github_repository) {
                return $this->githubVersions($request, $product, $subscription);
            }

            return $this->uploadVersions($request, $order, $product, $subscription);
        } catch (Exception $exception) {
            return errorResponse($exception->getMessage(), 500);
        }
    }

    /**
     * @return array<mixed>
     */
    private function autoRenewalGateways(string $country): array
    {
        $status = StatusSetting::first(['stripe_auto_renewal', 'razorpay_auto_renewal']);
        $currency = getCurrencyForClient($country);
        $active = SettingsController::checkPaymentGateway($currency);
        if (!is_array($active)) {
            $active = [];
        }
        $active = array_map(strtolower(...), $active);

        $enabled = [];
        if ($status?->stripe_auto_renewal && in_array('stripe', $active)) {
            $enabled[] = 'Stripe';
        }

        if ($status?->razorpay_auto_renewal && in_array('razorpay', $active)) {
            $enabled[] = 'Razorpay';
        }

        return $enabled;
    }

    private function githubVersions(Request $request, \App\Model\Product\Product $product, ?\App\Model\Product\Subscription $subscription): \Illuminate\Http\JsonResponse
    {
        $allReleases = array_slice(
            $this->github_api->releases($product->github_owner, $product->github_repository), // @phpstan-ignore property.notFound
            0, 3, preserve_keys: true
        );

        $downloadPermission = LicensePermissionsController::getPermissionsForProduct((int) $product->id);
        $allowTillExpiry = $downloadPermission['allowDownloadTillExpiry'] == 1;
        $countVersions = count($allReleases);
        $countExpiry = 0;

        if ($subscription instanceof \App\Model\Product\Subscription) {
            foreach ($allReleases as $release) {
                if (strtotime((string) $release['created_at']) < strtotime((string) $subscription->update_ends_at)
                    || $subscription->update_ends_at == '0000-00-00 00:00:00') {
                    $countExpiry++;
                }
            }
        }

        $search = trim((string) $request->input('search-query', ''));
        $items = collect();

        foreach ($allReleases as $release) {
            if ($search && stripos($release['tag_name'].$release['name'], $search) === false) {
                continue;
            }

            $canDownload = false;
            $downloadUrl = null;

            if (!$subscription instanceof \App\Model\Product\Subscription) {
                $canDownload = true;
            } elseif ($allowTillExpiry) {
                $canDownload = strtotime((string) $release['created_at']) < strtotime((string) $subscription->update_ends_at)
                    || $subscription->update_ends_at == '0000-00-00 00:00:00';
            } else {
                $canDownload = $countExpiry === $countVersions;
            }

            if ($canDownload) {
                try {
                    $downloadUrl = $this->github_api->resolveDownloadUrl($release['zipball_url']); // @phpstan-ignore property.notFound
                } catch (Exception) {
                    $downloadUrl = null;
                }
            }

            $items->push([
                'version' => ucfirst((string) $release['tag_name']),
                'name' => ucfirst((string) $release['name']),
                'description' => $release['body'] ?? '',
                'created_at' => $release['created_at'],
                'can_download' => $canDownload,
                'download_url' => $downloadUrl,
            ]);
        }

        $page = (int) $request->input('page', 1);
        $perPage = (int) $request->input('limit', 10);
        $paginator = new LengthAwarePaginator(
            $items->forPage($page, $perPage)->values(),
            $items->count(),
            $perPage,
            $page,
            ['path' => $request->url(), 'query' => $request->query()]
        );

        return successResponse('', $paginator);
    }

    private function uploadVersions(Request $request, \App\Model\Order\Order $order, \App\Model\Product\Product $product, ?\App\Model\Product\Subscription $subscription): \Illuminate\Http\JsonResponse
    {
        $search = trim((string) $request->input('search-query', ''));
        $order->invoices->first()?->number;

        $base = ProductUpload::where('product_id', $product->id)
            ->where('is_private', 0)
            ->select('id', 'product_id', 'version', 'title', 'description', 'created_at', 'release_type');

        if ($search !== '' && $search !== '0') {
            $base->where(function ($q) use ($search): void {
                $q->where('version', 'LIKE', sprintf('%%%s%%', $search))
                  ->orWhere('title', 'LIKE', sprintf('%%%s%%', $search))
                  ->orWhere('description', 'LIKE', sprintf('%%%s%%', $search));
            });
        }

        $allowed = ['version' => 'version', 'name' => 'title', 'created_at' => 'created_at'];
        $sortCol = $allowed[$request->input('sort-field', 'created_at')] ?? 'created_at';
        $sortDir = $request->input('sort-order', 'desc') === 'asc' ? 'asc' : 'desc';
        $base->orderBy($sortCol, $sortDir);

        $downloadPermission = LicensePermissionsController::getPermissionsForProduct((int) $product->id);
        $allowTillExpiry = $downloadPermission['allowDownloadTillExpiry'] == 1;

        $countVersions = (clone $base)->count();
        $countExpiry = 0;

        if ($subscription && ! $allowTillExpiry) {
            $countExpiry = $subscription->update_ends_at == '0000-00-00 00:00:00'
                ? $countVersions
                : (clone $base)->where('created_at', '<', $subscription->update_ends_at)->count();
        }

        $paginator = $base->paginate((int) $request->input('limit', 10));

        $paginator->getCollection()->transform(function ($version) use ($allowTillExpiry, $countExpiry, $countVersions, $subscription, $order): array {
            $canDownload = false;

            if (!$subscription instanceof \App\Model\Product\Subscription) {
                $canDownload = true;
            } elseif ($allowTillExpiry) {
                $createdAt = $version->created_at;
                $canDownload = $createdAt
                    ? ($createdAt->toDateTimeString() < $subscription->update_ends_at || $subscription->update_ends_at == '0000-00-00 00:00:00')
                    : ($subscription->update_ends_at == '0000-00-00 00:00:00');
            } else {
                $canDownload = $countExpiry == $countVersions;
            }

            return [
                'version' => ucfirst((string) $version->version).' '.getPreReleaseStatusLabel($version->release_type),
                'name' => ucfirst((string) $version->title),
                'description' => ucfirst($version->description ?? ''),
                'created_at' => $version->created_at,
                'can_download' => $canDownload,
                'download_url' => $canDownload
                    ? url(sprintf('download/%s/%s', $order->id, $version->id))
                    : null,
            ];
        });

        return successResponse('', $paginator);
    }

    /**
     *  Gets all the order details for a particular user.
     * @return \Illuminate\Database\Eloquent\Builder<\App\Model\Order\Order>
     */
    public function getClientPanelOrdersData(): \Illuminate\Database\Eloquent\Builder
    {
        return Order::with([
            'productRelation:id,name,github_owner,github_repository,type,whatsapp_integration',
            'subscription:id,order_id,plan_id,version,update_ends_at,ends_at',
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
    public function profile(Request $request): \Illuminate\Http\JsonResponse
    {
        try {
            $user = $this->user->where('id', Auth::id())->first();

            return successResponse('', ['user' => $user]);
        } catch (Exception $exception) {
            return errorResponse($exception->getMessage());
        }
    }

    public function generateMerchantRandomString(mixed $length = 10): string
    {
        return substr(bin2hex(random_bytes($length)), 0, $length);
    }

    /**
     * Get plan name and id ,options for upgrading or downgrading the cloud plan.
     *
     * @param  $product
     * @return array<mixed>
     */
    private function planPriceProductRelation(\App\Model\Product\Product $product): array
    {
        return Plan::where('product', '!=', $product->id)
            ->whereHas('productRelation', function ($query): void {
                $query->where('type', 4)
                      ->where('can_modify_agent', 1);
            })
            ->whereHas('planPrice', function ($query): void {
                $query->where('renew_price', '!=', 0);
            })
            ->pluck('name', 'id')
            ->toArray();
    }

    /**
     * Get renewal price for related plans.
     *
     * @param  array<mixed>  $planIds
     * @param  string  $userCountry
     * @param  array<mixed>  $plans
     * @param  \App\Model\Product\Product  $product
     * @return array<mixed>
     */
    private function planDetails(array $planIds, string $userCountry, array $plans, \App\Model\Product\Product $product): array
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
     * @param  $orderid
     * @param  $userid
     *
     * @throws Exception
     */
    public function getPaymentByOrderIdClient(int $orderid, int $userid): \Illuminate\Http\JsonResponse
    {
        try {
            if (! authorizeOwnership($userid, allowAdmin: true)) {
                return errorResponse(__('message.unauthorized_action'), 403);
            }

            $order = $this->order->where('id', $orderid)->where('client', $userid)->firstOrFail();

            $invoiceIds = $order->invoices()->pluck('invoices.id')->toArray();

            $paginated = $this->payment::query()
                ->with(['invoice:id,number,currency'])
                ->whereIn('invoice_id', $invoiceIds)->latest()
                ->paginate(10);

            $paginated->getCollection()->transform(fn ($payment): array => [
                'id' => $payment->id,
                'invoice_number' => $payment->invoice->number ?? '—',
                'amount' => currencyFormat($payment->amount, $payment->invoice->currency ?? ''),
                'payment_method' => $payment->payment_method,
                'payment_status' => $payment->payment_status,
                'created_at' => $payment->created_at,
            ]);

            return successResponse('', $paginated);
        } catch (Exception $exception) {
            return errorResponse($exception->getMessage());
        }
    }

    public function getOrderInstallations(Request $request, int $orderid): \Illuminate\Http\JsonResponse
    {
        try {
            $order = Order::where('id', $orderid)->where('client', Auth::id())->firstOrFail();

            $query = Installation::where('license_code', $order->serial_key)
                ->where('product_id', $order->product);
            $search = trim((string) $request->input('search-query', ''));

            if ($search !== '' && $search !== '0') {
                $query->where(function ($q) use ($search): void {
                    $q->where('installation_domain', 'like', sprintf('%%%s%%', $search))
                      ->orWhere('installation_ip', 'like', sprintf('%%%s%%', $search));
                });
            }

            $allowed = ['installation_path' => 'installation_domain', 'installation_ip' => 'installation_ip', 'last_active' => 'installation_date'];
            $sortCol = $allowed[$request->input('sort-field', 'last_active')] ?? 'installation_date';
            $sortDir = $request->input('sort-order', 'desc') === 'asc' ? 'asc' : 'desc';
            $query->orderBy($sortCol, $sortDir);

            $paginated = $query->paginate((int) $request->input('limit', 10));

            $paginated->getCollection()->transform(fn ($inst): array => [
                'installation_path' => $inst->installation_domain,
                'installation_ip' => $inst->installation_ip,
                'version' => $inst->version,
                'last_active' => $inst->installation_date,
            ]);

            return successResponse('', $paginated);
        } catch (Exception $exception) {
            return errorResponse($exception->getMessage());
        }
    }

    public function clientDetails(): \Illuminate\Http\JsonResponse
    {
        $user = Auth::user();
        if (!$user instanceof User) {
            return errorResponse('Unauthenticated.', 401);
        }

        return successResponse('', [
            'pending_invoices_count' => $user->invoice()->where('status', 'pending')->count(),
            'total_orders_count' => $user->order()->count(),
            'order_renewals_count' => $user->order()
                ->whereHas('subscription', fn ($q) => $q->where('update_ends_at', '<', now()))
                ->count(),
        ]);
    }

    public function payNow(int $invoiceid): \Illuminate\Http\JsonResponse
    {
        try {
            $paid = 0;
            $invoice = Invoice::find($invoiceid);
            if (!$invoice instanceof Invoice) {
                return errorResponse('Invoice not found.', 404);
            }
            $user = Auth::user();
            if (!$user instanceof User || $invoice->user_id != $user->id) {
                return errorResponse(__('message.invalid_payment_modification'));
            }

            if (count($invoice->payment()->get()) > 0) {
                $paid = array_sum($invoice->payment()->pluck('amount')->toArray());
                $invoice->grand_total -= $paid; // @phpstan-ignore assignOp.invalid
            }

            $items = collect();
            $product = null;
            $items = $invoice->invoiceItem()->get();
            if (count($items) > 0) {
                $invoiceItem = InvoiceItem::where('invoice_id', $invoiceid)->first();
                if ($invoiceItem instanceof InvoiceItem) {
                    $product = Product::find($invoiceItem->product_id);
                }
            }

            return successResponse('', ['invoice' => $invoice, 'items' => $items, 'paid' => $paid, 'product' => $product]);
        } catch (Exception $exception) {
            Logger::exception($exception);

            return errorResponse($exception->getMessage());
        }
    }
}
