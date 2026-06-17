<?php

namespace App\Http\Controllers\Front;

use App\ApiKey;
use App\Auto_renewal;
use App\Http\Controllers\Common\PhpMailController;
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
use App\Model\Order\OrderInvoiceRelation;
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
use Session;
use Stripe\StripeClient;

class ClientController extends BaseClientController
{
    public $user;

    public $invoice;

    public $order;

    public $subscription;

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
        $this->product_upload = $product_upload;

        $product = new Product();
        $this->product = $product;

        $github_controller = new GithubApiController();
        $this->github_api = $github_controller;

        $model = new Github();
        $this->github = $model->firstOrFail();

        $this->client_id = $this->github->client_id;
        $this->client_secret = $this->github->client_secret;
    }

    /**
     * /**
     *  Auto-renew by id and redirect to paynow page.
     *
     * @param
     * @return RedirectResponse
     */
    public function autoRenewbyid()
    {
        try {
            $id = request()->route('id');
            $order_id = DB::table('order_invoice_relations')->where('invoice_id', $id)->value('order_id');
            $sub = Subscription::where('order_id', $order_id)->first();
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
            $invoiceid = $items->invoice_id;
            // $this->setSession($id, $planid);

            return redirect('paynow/'.$id);
        } catch(Exception $ex) {
            return redirect('my-orders')->with('fails', $ex->getMessage());
        }
    }

    /**
     *  Get all the invoices in data table.
     *
     * @param  request  $request
     * @return \Yajra\DataTables\DataTableAbstract
     *
     * @throws Exception
     */
    public function getInvoices(Request $request)
    {
        $query = Invoice::with([
            'orders:id,number',
            'payment' => fn ($q) => $q->where('payment_status', 'success')->select('invoice_id', 'amount'),
        ])
            ->select('id', 'number', 'date', 'grand_total', 'billing_pay', 'status', 'currency', 'is_renewed')
            ->where('user_id', Auth::id());

        if ($search = trim((string) $request->input('search-query', ''))) {
            $query->where(function (Builder $q) use ($search): void {
                $q->where('number', 'like', "%{$search}%")
                  ->orWhere('status', 'like', "%{$search}%");
            });
        }

        $allowed = ['number' => 'number', 'date' => 'date', 'grand_total' => 'grand_total'];
        $sortCol = $allowed[$request->input('sort-field', 'date')] ?? 'date';
        $sortDir = $request->input('sort-order', 'desc') === 'asc' ? 'asc' : 'desc';
        $query->orderBy($sortCol, $sortDir);

        $paginated = $query->paginate((int) $request->input('limit', 10));

        $paginated->getCollection()->transform(function ($invoice) {
            $paymentTotal = $invoice->payment->sum('amount');
            $paid = floatval($invoice->billing_pay ?? 0) + floatval($paymentTotal);
            $balance = max(0, floatval($invoice->grand_total) - $paid);
            $isPaid = strtolower($invoice->status ?? '') === 'success';

            return [
                'id' => $invoice->id,
                'number' => $invoice->number,
                'date' => $invoice->date,
                'is_renewed' => (bool) $invoice->is_renewed,
                'orders' => $invoice->orders->map(fn ($o) => ['id' => $o->id, 'number' => $o->number])->values(),
                'grand_total' => currencyFormat($invoice->grand_total, $invoice->currency),
                'paid' => currencyFormat($paid, $invoice->currency),
                'balance' => currencyFormat($balance, $invoice->currency),
                'status' => $isPaid ? 'Paid' : 'Unpaid',
                'show_pay' => ! $isPaid && floatval($invoice->grand_total) > 0,
            ];
        });

        return successResponse('', $paginated);
    }

    public function getClientOrder(Request $request)
    {
        $query = $this->getClientPanelOrdersData();

        if ($id = $request->input('id')) {
            $order = (clone $query)->where('id', $id)->first();
            if (! $order) {
                return errorResponse(__('message.no_records_found'), 404);
            }

            $latestInvoice = $order->invoices->first();
            $user = Auth::user();

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
                'autorenewal_enabled' => count($this->autoRenewalGateways($user->country)) > 0,
                'whatsapp_enabled' => (bool) $order->productRelation?->whatsapp_integration,
                'whatsapp_signup_enabled' => (bool) StatusSetting::pluck('whatsapp_status')->first(),
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

        if ($search = trim((string) $request->input('search-query', ''))) {
            $query->where(function (Builder $q) use ($search): void {
                $q->where('number', 'like', "%{$search}%")
                  ->orWhereHas('productRelation', fn (Builder $pq) => $pq->where('name', 'like', "%{$search}%"));
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

        $paginated->getCollection()->transform(function ($order) use ($downloadPerms) {
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
     * @return JsonResponse
     */
    public function getCloudSettings($orderId)
    {
        try {
            $user = Auth::user();
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
            $plans = $this->planDetails($planIds, $countryids, $user->country, $plans, $product);
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
                'is_free_plan' => $planName ? stripos((string) $planName, 'free') !== false : false,
                'plan_expiry' => $subscription?->ends_at,
                'price_per_agent' => currencyFormat($pricePerAgent, $currency, true),
                'plans' => $planOptions,
            ]);
        } catch (Exception $e) {
            Logger::exception($e);

            return errorResponse(__('message.something_bad'));
        }
    }

    public function renewPopupVue(Request $request, int $productid)
    {
        try {
            $user = Auth::user();
            $currency = getCurrencyForClient($user->country);

            $plans = Plan::where('product', $productid)
                ->where('status', 1)
                ->where('days', '!=', 14)
                ->with([
                    'planPrice' => fn ($q) => $q->where('currency', $currency)
                        ->where('renew_price', '!=', '0')])
                ->get()
                ->filter(fn ($plan) => $plan->planPrice->isNotEmpty());

            $planOptions = $plans->map(function ($plan) use ($currency) {
                $renewPrice = $plan->planPrice->first()->renew_price;

                return [
                    'id' => $plan->id,
                    'name' => $plan->name.' (Renewal price: '.currencyFormat($renewPrice, $currency, true).')',
                ];
            })->values();

            return successResponse('', [
                'plans' => $planOptions,
                'user_id' => $user->id,
            ]);
        } catch (Exception $e) {
            return errorResponse($e->getMessage());
        }
    }

    #[Override]
    public function getInvoicesByOrderId($orderid, $userid, $admin = null)
    {
        try {
            if (! authorizeOwnership((int) $userid, true)) {
                return errorResponse(__('message.unauthorized_action'), 403);
            }

            $order = Order::where('id', $orderid)->where('client', $userid)->firstOrFail();

            $invoiceIds = $order->invoices()->pluck('invoices.id');

            $paginated = Invoice::whereIn('id', $invoiceIds)
                ->select('id', 'number', 'date', 'grand_total', 'currency', 'status')
                ->orderBy('date', 'desc')
                ->paginate(10);

            $paginated->getCollection()->transform(fn ($model) => [
                'id' => $model->id,
                'number' => $model->number,
                'date' => $model->date,
                'grand_total' => currencyFormat($model->grand_total, $model->currency),
                'status' => $model->status,
            ]);

            return successResponse('', $paginated);
        } catch (Exception $e) {
            return errorResponse($e->getMessage());
        }
    }

    public function prepareInvoiceData($invoice, $user = null)
    {
        $payments = $invoice->payment;
        $user ??= Auth::user();
        $items = $invoice->invoiceItem()->get();

        $orderIDs = $invoice->orderRelation()->pluck('order_id')->toArray();

        $items->each(function ($item) use ($orderIDs): void {
            $order = Order::whereIn('id', $orderIDs)
                ->where('product', $item->product_id)
                ->first();

            $item->order = $order;
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
            $percentage = rtrim(rtrim(number_format((float) $lines->first()->rate, 2, '.', ''), '0'), '.').'%';

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
        if ($set->logo) {
            $type = pathinfo((string) $set->logo, PATHINFO_EXTENSION);
            $data = file_get_contents($set->logo);
            $base64 = 'data:image/'.$type.';base64,'.base64_encode($data);
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
     *
     * @param  type  $productid
     * @param  type  $clientid
     * @param  type  $invoiceid
     *
     * Get list of all the versions from Filesystem.
     * @param  type  $productid
     * @param  type  $clientid
     * @param  type  $invoiceid
     * @return type
     */
    public function getVersionList(Request $request, $orderid)
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
            $subscription = $order->subscription;

            if ($product->github_owner && $product->github_repository) {
                return $this->githubVersions($request, $product, $subscription);
            }

            return $this->uploadVersions($request, $order, $product, $subscription);
        } catch (Exception $ex) {
            return errorResponse($ex->getMessage(), 500);
        }
    }

    private function autoRenewalGateways(string $country): array
    {
        $status = StatusSetting::first(['stripe_auto_renewal', 'razorpay_auto_renewal']);
        $currency = getCurrencyForClient($country);
        $active = SettingsController::checkPaymentGateway($currency);
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

    private function githubVersions(Request $request, $product, $subscription)
    {
        $allReleases = array_slice(
            $this->github_api->releases($product->github_owner, $product->github_repository),
            0, 3, true
        );

        $downloadPermission = LicensePermissionsController::getPermissionsForProduct((int) $product->id);
        $allowTillExpiry = $downloadPermission['allowDownloadTillExpiry'] == 1;
        $countVersions = count($allReleases);
        $countExpiry = 0;

        if ($subscription) {
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

            if (! $subscription) {
                $canDownload = true;
            } elseif ($allowTillExpiry) {
                $canDownload = strtotime((string) $release['created_at']) < strtotime((string) $subscription->update_ends_at)
                    || $subscription->update_ends_at == '0000-00-00 00:00:00';
            } else {
                $canDownload = $countExpiry == $countVersions;
            }

            if ($canDownload) {
                try {
                    $downloadUrl = $this->github_api->resolveDownloadUrl($release['zipball_url']);
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

    private function uploadVersions(Request $request, $order, $product, $subscription)
    {
        $search = trim((string) $request->input('search-query', ''));
        $invoiceNumber = $order->invoices->first()?->number;

        $base = ProductUpload::where('product_id', $product->id)
            ->where('is_private', 0)
            ->select('id', 'product_id', 'version', 'title', 'description', 'created_at', 'release_type');

        if ($search) {
            $base->where(function ($q) use ($search): void {
                $q->where('version', 'LIKE', "%{$search}%")
                  ->orWhere('title', 'LIKE', "%{$search}%")
                  ->orWhere('description', 'LIKE', "%{$search}%");
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

        $paginator->getCollection()->transform(function ($version) use ($allowTillExpiry, $countExpiry, $countVersions, $subscription, $order, $product, $invoiceNumber) {
            $canDownload = false;

            if (! $subscription) {
                $canDownload = true;
            } elseif ($allowTillExpiry) {
                $canDownload = $version->created_at->toDateTimeString() < $subscription->update_ends_at
                    || $subscription->update_ends_at == '0000-00-00 00:00:00';
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
                    ? url("download/{$order->id}/{$version->id}")
                    : null,
            ];
        });

        return successResponse('', $paginator);
    }

    /**
     *  Gets all the order details for a particular user.
     *
     * @param
     * @return \Illuminate\Database\Eloquent\Builder
     *
     * @throws
     */
    public function getClientPanelOrdersData()
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
     * @param
     *
     * @throws Exception
     */
    public function profile(Request $request)
    {
        try {
            $user = $this->user->where('id', Auth::user()->id)->first();

            return successResponse('', ['user' => $user]);
        } catch (Exception $ex) {
            return errorResponse($ex->getMessage());
        }
    }

    public function generateMerchantRandomString($length = 10)
    {
        return substr(bin2hex(random_bytes($length)), 0, $length);
    }

    /**
     * Get payment log for the order terminated.
     *
     * @param  $terminatedOrderNumber
     * @return array
     */
    private function paymentLogGet($terminatedOrderNumber)
    {
        $payment_log = Payment_log::where('order', $terminatedOrderNumber)
            ->where('payment_type', 'Payment method updated')
            ->orderBy('id', 'desc')
            ->first();
        if (! $payment_log) {
            $payment_log = Payment_log::where('order', $terminatedOrderNumber)
                ->orderBy('id', 'desc')
                ->first();
        }

        return $payment_log;
    }

    /**
     * Get plan name and id ,options for upgrading or downgrading the cloud plan.
     *
     * @param  $product
     * @return array
     */
    private function planPriceProductRelation($product)
    {
        $plans = Plan::where('product', '!=', $product->id)
            ->whereHas('productRelation', function ($query): void {
                $query->where('type', 4)
                      ->where('can_modify_agent', 1);
            })
            ->whereHas('planPrice', function ($query): void {
                $query->where('renew_price', '!=', 0);
            })
            ->pluck('name', 'id')
            ->toArray();

        return $plans;
    }

    /**
     * Get renewal price for related plans.
     *
     * @param  $product
     * @param  $planIds
     * @param  $countryids
     * @param  $userCountry
     * @param  $plans
     * @return array
     */
    private function planDetails($planIds, $countryids, $userCountry, $plans, $product)
    {
        $currency = getCurrencyForClient($userCountry);

        $renewalPrices = PlanPrice::whereIn('plan_id', $planIds)
            ->where('currency', $currency)
            ->latest()
            ->pluck('renew_price', 'plan_id')
            ->toArray();

        foreach ($plans as $planId => $planName) {
            if (isset($renewalPrices[$planId])) {
                if (in_array($product->id, cloudPopupProducts())) {
                    $plans[$planId] .= ' (Plan price-per agent: '.currencyFormat($renewalPrices[$planId], $currency, true).')';
                }
            }
        }

        // Add more cloud IDs until we have a generic way to differentiate
        if (in_array($product->id, cloudPopupProducts())) {
            $plans = array_filter($plans, fn ($value) => stripos((string) $value, 'free') === false);
        }

        return $plans;
    }

    /**
     * It returns the user details.
     *
     * @param  $user
     * @param  $rzp_key
     * @param  $invoice
     * @param  $userCountry
     * @param  $exchangeRate
     * @param  $merchant_orderid
     * @param  $razorpayOrderId
     * @param  $displayCurrency
     * @return string
     */
    private function dataToOrder($user, $rzp_key, $invoice, $userCountry, $exchangeRate, $merchant_orderid, $razorpayOrderId, $displayCurrency)
    {
        $data = [
            'key' => $rzp_key,
            'name' => 'Faveo Helpdesk',
            'currency' => 'INR',
            'prefill' => [
                'contact' => $user->mobile_code.$user->mobile,
                'email' => $user->email,
            ],
            'description' => 'Order for Invoice No'.-$invoice->number,
            'notes' => [
                'First Name' => $user->first_name,
                'Last Name' => $user->last_name,
                'Company Name' => $user->company,
                'Address' => $user->address,
                'Email' => $user->email,
                'Country' => $userCountry,
                'State' => $user->state,
                'City' => $user->town,
                'Zip' => $user->zip,
                'Currency' => $user->currency,
                'Amount Paid' => '1',
                'Exchange Rate' => $exchangeRate,
                'merchant_order_id' => $merchant_orderid,
            ],
            'theme' => [
                'color' => '#F37254',
            ],
            'order_id' => $razorpayOrderId,
        ];
        if ($displayCurrency !== 'INR') {
            $data['display_currency'] = 'USD';
            $data['display_amount'] = '1';
        }

        return json_encode($data);
    }

    /**
     *  Returns to client individual orders with payment details as datatable.
     *
     * @param  $orderid
     * @param  $userid
     * @return \Yajra\DataTables\DataTableAbstract|RedirectResponse
     *
     * @throws Exception
     */
    public function getPaymentByOrderIdClient($orderid, $userid)
    {
        try {
            if (! authorizeOwnership($userid, true)) {
                return errorResponse(__('message.unauthorized_action'), 403);
            }

            $order = $this->order->where('id', $orderid)->where('client', $userid)->firstOrFail();

            $invoiceIds = $order->invoices()->pluck('invoices.id')->toArray();

            $paginated = $this->payment::query()
                ->with(['invoice:id,number,currency'])
                ->whereIn('invoice_id', $invoiceIds)
                ->orderBy('created_at', 'desc')
                ->paginate(10);

            $paginated->getCollection()->transform(fn ($payment) => [
                'id' => $payment->id,
                'invoice_number' => $payment->invoice?->number ?? '—',
                'amount' => currencyFormat($payment->amount, $payment->invoice?->currency ?? ''),
                'payment_method' => $payment->payment_method,
                'payment_status' => $payment->payment_status,
                'created_at' => $payment->created_at,
            ]);

            return successResponse('', $paginated);
        } catch (Exception $ex) {
            return errorResponse($ex->getMessage());
        }
    }

    public function getOrderInstallations(Request $request, $orderid)
    {
        try {
            $order = Order::where('id', $orderid)->where('client', Auth::id())->firstOrFail();

            $query = Installation::where('license_code', $order->serial_key)
                ->where('product_id', $order->product);

            if ($search = trim((string) $request->input('search-query', ''))) {
                $query->where(function ($q) use ($search): void {
                    $q->where('installation_domain', 'like', "%{$search}%")
                      ->orWhere('installation_ip', 'like', "%{$search}%");
                });
            }

            $allowed = ['installation_path' => 'installation_domain', 'installation_ip' => 'installation_ip', 'last_active' => 'installation_date'];
            $sortCol = $allowed[$request->input('sort-field', 'last_active')] ?? 'installation_date';
            $sortDir = $request->input('sort-order', 'desc') === 'asc' ? 'asc' : 'desc';
            $query->orderBy($sortCol, $sortDir);

            $paginated = $query->paginate((int) $request->input('limit', 10));

            $paginated->getCollection()->transform(fn ($inst) => [
                'installation_path' => $inst->installation_domain,
                'installation_ip' => $inst->installation_ip,
                'version' => $inst->version,
                'last_active' => $inst->installation_date,
            ]);

            return successResponse('', $paginated);
        } catch (Exception $e) {
            return errorResponse($e->getMessage());
        }
    }

    public function clientDetails()
    {
        $user = auth()->user();

        return successResponse('', [
            'pending_invoices_count' => $user->invoice()->where('status', 'pending')->count(),
            'total_orders_count' => $user->order()->count(),
            'order_renewals_count' => $user->order()
                ->whereHas('subscription', fn ($q) => $q->where('update_ends_at', '<', now()))
                ->count(),
        ]);
    }

    /**
     * Delete an invoice and its related records based on specific conditions.
     *
     * @param  int  $id  The ID of the invoice to be deleted.
     * @return JsonResponse
     */
    public function invoiceDelete($id)
    {
        $invoice = Invoice::find($id);

        if (! $invoice) {
            return response()->json(['error' => 'Invoice not found'], 404);
        }

        if (! authorizeOwnership($invoice->user_id)) {
            return errorResponse(__('message.unauthorized_action'), 403);
        }

        if ($this->canDeleteInvoice($invoice)) {
            $this->deleteInvoice($invoice);

            return response()->json(['message' => __('message.invoice_deleted_successfully')]);
        }

        return response()->json(['error' => __('message.cannot_delete_invoice')], 400);
    }

    /**
     *  Checks if Invoice can be deleted or not.
     *
     * @param  $invoice
     * @return bool
     *
     * @throws
     */
    private function canDeleteInvoice($invoice)
    {
        return (
            $invoice->is_renewed == 0 &&
            ! $invoice->orderRelation()->exists() &&
            $invoice->invoiceItem()->exists()
        ) || (
            $invoice->is_renewed != 0 &&
            $invoice->orderRelation()->exists() &&
            $invoice->invoiceItem()->exists()
        );
    }

    /**
     *  Deletes the invoice.
     *
     * @param  $invoice
     * @return
     *
     * @throws
     */
    private function deleteInvoice($invoice)
    {
        $invoice->invoiceItem()->delete();

        if ($invoice->is_renewed != 0 && $invoice->orderRelation()->exists()) {
            $invoice->orderRelation()->delete();
        }

        $invoice->delete();
        Session::forget('invoice');
    }

    public function stripeUpdatePayment(Request $request)
    {
        try {
            $currency = getCurrencyForClient(Auth::user()->country);
            $amount = currencyFormat(1, $currency);
            $orderid = $request->input('orderId');
            $stripeSecretKey = ApiKey::pluck('stripe_secret')->first();
            $stripe = new StripeClient($stripeSecretKey);
            $paymentIntent = $stripe->paymentIntents->retrieve($request->input('payment_intent'));
            if ($paymentIntent->status === 'succeeded') {
                $response = $this->stripePaymentUpdateSub($stripe, $paymentIntent, $orderid);

                return response()->json($response);
            } else {
                $response = ['type' => 'fails', 'message' => __('message.something_wrong')];

                return response()->json(compact('response'), 500);
            }
        } catch(Exception $ex) {
            $result = $ex->getMessage();
            $mail = new PhpMailController();
            $mail->payment_log(Auth::user()->email, 'stripe', 'failed', Order::where('id', $orderid)->value('number'), $result, $amount, 'Payment method updated');
            $errorMessage = __('message.something_wrong');

            return response()->json(['error' => $errorMessage], 500);
        }
    }

    private function stripePaymentUpdateSub($stripe, $paymentIntent, $orderid)
    {
        $refund = $stripe->refunds->create([
            'payment_intent' => $paymentIntent->id,
            'amount' => $paymentIntent->amount,
        ]);
        $invoice_id = OrderInvoiceRelation::where('order_id', $orderid)->value('invoice_id');
        $number = Invoice::where('id', $invoice_id)->value('number');
        $customer_details = [
            'user_id' => Auth::user()->id,
            'customer_id' => $paymentIntent->customer,
            'payment_method' => 'stripe',
            'order_id' => $orderid,
            'payment_intent_id' => $paymentIntent->payment_method,
        ];
        Auto_renewal::create($customer_details);
        Subscription::where('order_id', $orderid)->update(['is_subscribed' => '1', 'autoRenew_status' => '1']);
        $mail = new PhpMailController();
        $mail->payment_log(Auth::user()->email, 'stripe', 'success', Order::where('id', $orderid)->value('number'), null, $amount, 'Payment method updated');

        return ['type' => 'success', 'message' => __('message.card_details_updated_successfully')];
    }
}
