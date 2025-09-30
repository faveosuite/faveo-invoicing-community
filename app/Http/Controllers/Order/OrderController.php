<?php

namespace App\Http\Controllers\Order;

use App\Events\UserOrderDelete;
use App\Http\Requests\Order\OrderRequest;
use App\Jobs\ReportExport;
use App\Model\Common\StatusSetting;
use App\Model\Mailjob\QueueService;
use App\Model\Order\InstallationDetail;
use App\Model\Order\Invoice;
use App\Model\Order\InvoiceItem;
use App\Model\Order\Order;
use App\Model\Order\Payment;
use App\Model\Payment\Plan;
use App\Model\Payment\Promotion;
use App\Model\Product\Price;
use App\Model\Product\Product;
use App\Model\Product\ProductUpload;
use App\Model\Product\Subscription;
use App\Payment_log;
use App\User;
use Bugsnag;
use Illuminate\Http\Request;

class OrderController extends BaseOrderController
{
    // NOTE FROM AVINASH: utha le re deva
    // NOTE: don't lose hope.
    public $order;

    public $user;

    public $promotion;

    public $product;

    public $subscription;

    public $invoice;

    public $invoice_items;

    public $price;

    public $plan;

    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('admin', ['except' => ['getInstallationDetails']]);

        $order = new Order();
        $this->order = $order;

        $user = new User();
        $this->user = $user;

        $promotion = new Promotion();
        $this->promotion = $promotion;

        $product = new Product();
        $this->product = $product;

        $subscription = new Subscription();
        $this->subscription = $subscription;

        $invoice = new Invoice();
        $this->invoice = $invoice;

        $invoice_items = new InvoiceItem();
        $this->invoice_items = $invoice_items;

        $plan = new Plan();
        $this->plan = $plan;

        $price = new Price();
        $this->price = $price;

        $product_upload = new ProductUpload();
        $this->product_upload = $product_upload;
    }

    /**
     * Display a listing of the resource.
     *
     * @param  Request  $request
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function index(Request $request)
    {
        $validator = \Validator::make($request->all(), [
            'from' => 'nullable',
            'till' => 'nullable|after:from',

        ]);
        if ($validator->fails()) {
            $request->from = '';
            $request->till = '';

            return redirect('orders')->with('fails', __('message.start_date_before_end_date'));
        }
        try {
            $products = $this->product->where('id', '!=', 1)->pluck('name', 'id')->toArray();

            $paidUnpaidOptions = ['paid' => 'Paid Products', 'unpaid' => 'Unpaid Products'];
            $insNotIns = ['installed' => 'Yes (Installed atleast once)', 'not_installed' => 'No (Not Installed)'];
            $activeInstallationOptions = ['paid_ins' => 'Active installation'];
            $inactiveInstallationOptions = ['paid_inactive_ins' => 'Inactive installation'];
            $renewal = ['expired_subscription' => 'Expired Subscriptions', 'active_subscription' => 'Active Subscriptions', 'expiring_subscription' => 'Expiring Subscriptions'];
            $selectedVersion = $request->version;
            $allVersions = Subscription::where('version', '!=', '')->whereNotNull('version')
                ->orderBy('version', 'desc')->groupBy('version')
                ->select('version')->get();

            return view('themes.default1.order.index',
                compact('request', 'products', 'allVersions', 'activeInstallationOptions', 'paidUnpaidOptions', 'inactiveInstallationOptions', 'renewal', 'insNotIns', 'selectedVersion'));
        } catch (\Exception $e) {
            return redirect('orders')->with('fails', $e->getMessage());
        }
    }

    public function getOrders(Request $request)
    {
        try {
            $searchQuery = $request->input('search-query', '');
            $sortOrder = $request->input('sort-order', 'asc');
            $sortField = $request->input('sort-field', 'created_at');
            $limit = $request->input('limit', 10);
            $page = $request->input('page', 1);

            $orderSearch = new OrderSearchController();
            $query = $orderSearch->advanceOrderSearch($request);

            $paginated = $query->orderBy($sortField, $sortOrder)
                ->paginate($limit, ['*'], 'page', $page);

            // Map items
            $paginated->getCollection()->transform(function ($order) {
                $user = $order->user;
                $plan = Plan::find($order->subscription->plan_id)->name ?? null;
                $installedVersions = $order->installationDetail ? $order->installationDetail->pluck('version')->toArray() : [];
                $latestVersion = count($installedVersions) ? max($installedVersions) : null;

                $licenseAgents = substr($order->serial_key, 12, 16) === '0000'
                    ? 'Unlimited'
                    : intval(substr($order->serial_key, 12, 16), 10);

                $invoiceStatus = $this->checkInvoiceStatusByOrderId($order->id);

                return [
                    'id' => $order->id,
                    'product_name' => $order->productRelation->name,
                    'plan' => $plan,
                    'version' => $latestVersion ? getVersionAndLabel($latestVersion, $order->product) : null,
                    'agents' => $licenseAgents,
                    'number' => $order->number,
                    'status' => ! empty($order->installationDetail) ? 'Active' : 'Inactive',
                    'order_status' => ucfirst($order->order_status),
                    'order_date' => $order->created_at,
                    'update_ends_at' => strtotime($order->subscription->ends_at) > 1 ? $order->subscription->ends_at : null,
                    'subscription_updated_at' => $order->subscription->updated_at,
                    'user' => $user,
                    'action' => $this->getOrderActions($order, $invoiceStatus, $order->subscription?->id, $licenseAgents),
                ];
            });

            return successResponse('', $paginated);
        } catch (\Exception $e) {
            return errorResponse($e->getMessage());
        }
    }

    public function getOrder($id)
    {
        $order = $this->order
            ->with([
                'user:id,first_name,last_name,email,mobile,mobile_code,address,country',
                'subscription',
            ])
            ->findOrFail($id);

        // Check if client is soft-deleted
        if ($order->user->trashed()) {
            return errorResponse(__('message.user_suspended_restore_to_view'), 403);
        }

        $subscription = $order->subscription;

        $expiryDates = [
            'subscription_end' => $subscription && strtotime($subscription->ends_at) > 1 ? getExpiryLabel($subscription->ends_at) : null,
            'update_end' => $subscription && strtotime($subscription->update_ends_at) > 1 ? getExpiryLabel($subscription->update_ends_at) : null,
            'support_end' => $subscription && strtotime($subscription->support_ends_at) > 1 ? getExpiryLabel($subscription->support_ends_at) : null,
        ];

        $settings = StatusSetting::first(['license_status']);
        $licenseStatus = $settings->license_status ?? 0;

        $licenseController = new \App\Http\Controllers\License\LicenseController();
        $noOfAllowedInstallation = $licenseStatus ? $licenseController->getNoOfAllowedInstallation($order->serial_key, $order->product) : null;

        return successResponse('', [
            'order' => $order,
            'license_details' => [
                'licence_code' => $order->serial_key,
                'no_of_allowed_installation' => $noOfAllowedInstallation,
                'expiry_dates' => $expiryDates,
            ],
            'autorenewal' => $order->subscription->autoRenew_status,
        ]);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Response
     */
    public function create()
    {
        try {
            $clients = $this->user->pluck('first_name', 'id')->toArray();
            $product = $this->product->pluck('name', 'id')->toArray();
            $subscription = $this->subscription->pluck('name', 'id')->toArray();
            $promotion = $this->promotion->pluck('code', 'id')->toArray();

            return view('themes.default1.order.create', compact('clients', 'product', 'subscription', 'promotion'));
        } catch (\Exception $e) {
            Bugsnag::notifyExeption($e);

            return redirect()->back()->with('fails', $e->getMessage());
        }
    }

    public function getInstallationDetails($orderId)
    {
        try {
            // Fetch the order
            $order = $this->order->findOrFail($orderId);

            $licenseController = new \App\Http\Controllers\License\LicenseController();

            $installationLogs = $licenseController->getInstallationLogsDetails($order->serial_key);

            $installationDetails = [];
//            $data=array_map(function($installationLogs) use ($orderId){
//                $installationPath = $installationLogs['installation_domain'] ?? null;
//                $installationIp = $installationLogs['installation_ip'] ?? null;
//                $lastActive = $installationLogs['installation_last_active_date'] ?? null;
//                $installationStatus = $installationLogs['installation_status'] ?? null;
//
//                // Sync with database
//                if ($installationPath || $installationIp) {
//                    InstallationDetail::updateOrCreate(
//                        [
//                            'installation_path' => $installationPath,
//                            'installation_ip' => $installationIp,
//                        ],
//                        [
//                            'last_active' => $lastActive,
//                            'order_id' => $orderId,
//                            'installation_status' => $installationStatus,
//                        ]
//                    );
//                }
//                return[
//                    'path' => $installationLogs['installation_domain'] ?? null,
//                    'ip' => $installationLogs['installation_ip'] ?? null,
//                    'version' => $installationLogs['version_number'] ?? null,
//                    'status' => $installationLogs['installation_status'] ?? null,
//                    'last_active_date'=>$installationLogs['installation_last_active_date'] ?? null,
//                ];
//            },$installationLogs);

            foreach ($installationLogs as $log) {
                $installationPath = $log['installation_domain'] ?? null;
                $installationIp = $log['installation_ip'] ?? null;
                $lastActive = $log['installation_last_active_date'] ?? null;
                $installationStatus = $log['installation_status'] ?? null;

                // Sync with database
                if ($installationPath || $installationIp) {
                    InstallationDetail::updateOrCreate(
                        [
                            'installation_path' => $installationPath,
                            'installation_ip' => $installationIp,
                        ],
                        [
                            'last_active' => $lastActive,
                            'order_id' => $orderId,
                            'installation_status' => $installationStatus,
                        ]
                    );
                }

                $installationDetails[] = [
                    'path' => $installationPath,
                    'ip' => $installationIp,
                    'version' => $log['version_number'] ?? null,
                    'status' => $installationStatus,
                    'last_active_date' => $lastActive,
                ];
            }

//          return successResponse('',$data);
            return successResponse('', $installationDetails);
        } catch (\Exception $ex) {
            return errorResponse($ex->getMessage());
        }
    }

    /**
     * Store a newly created resource in storage.
     *
     * @return \Response
     */
    public function show($id)
    {
        try {
            $order = $this->order->findOrFail($id);
            if (User::onlyTrashed()->find($order->client)) {//If User is soft deleted for this order
                throw new \Exception(__('message.user_suspended_restore_to_view'));
            }
            $subscription = $order->subscription()->first();

            $date = '--';
            $licdate = '--';
            $supdate = '--';
            $connectionLabel = '--';
            $lastActivity = '--';
            $versionLabel = '--';
            if ($subscription) {
                $date = strtotime($subscription->update_ends_at) > 1 ? getExpiryLabel($subscription->update_ends_at) : '--';
                $licdate = strtotime($subscription->ends_at) > 1 ? getExpiryLabel($subscription->ends_at) : '--';
                $supdate = strtotime($subscription->support_ends_at) > 1 ? getExpiryLabel($subscription->support_ends_at) : '--';
            }
            $invoice = $this->invoice->where('id', $order->invoice_id)->first();

            if (! $invoice) {
                return redirect()->back()->with('fails', __('message.no_orders'));
            }
            $user = $this->user->find($invoice->user_id);
            $licenseStatus = StatusSetting::pluck('license_status')->first();
            $installationDetails = [];
            $noOfAllowedInstallation = '';
            $getInstallPreference = '';
            if ($licenseStatus == 1) {
                $cont = new \App\Http\Controllers\License\LicenseController();
                $noOfAllowedInstallation = $cont->getNoOfAllowedInstallation($order->serial_key, $order->product);
            }

            $allowDomainStatus = StatusSetting::pluck('domain_check')->first();

            $licenseStatus = StatusSetting::pluck('license_status')->first();
            $installationDetails = [];

            $cont = new \App\Http\Controllers\License\LicenseController();
            $installationDetails = $cont->searchInstallationPath($order->serial_key, $order->product);
            $currency = getCurrencyForClient($user->country);
            $amount = currencyFormat(1, $currency);
            $payment_log = Payment_log::where('order', $order->number)
            ->where('amount', $amount)
            ->where('payment_type', 'Payment method updated')
            ->orderBy('id', 'desc')
            ->first();

            $statusAutorenewal = Subscription::where('order_id', $id)->value('is_subscribed');

            return view('themes.default1.order.show',
                compact('user', 'order', 'subscription', 'licenseStatus', 'installationDetails', 'allowDomainStatus', 'noOfAllowedInstallation', 'lastActivity', 'versionLabel', 'date', 'licdate', 'supdate', 'installationDetails', 'id', 'statusAutorenewal', 'payment_log'));
        } catch (\Exception $ex) {
            return redirect()->back()->with('fails', $ex->getMessage());
        }
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Response
     */
    public function edit($id)
    {
        try {
            $order = $this->order->where('id', $id)->first();
            $clients = $this->user->pluck('first_name', 'id')->toArray();
            $product = $this->product->pluck('name', 'id')->toArray();
            $subscription = $this->subscription->pluck('name', 'id')->toArray();
            $promotion = $this->promotion->pluck('code', 'id')->toArray();

            return view('themes.default1.order.edit',
                compact('clients', 'product', 'subscription', 'promotion', 'order'));
        } catch (\Exception $e) {
            Bugsnag::notifyExeption($e);

            return redirect()->back()->with('fails', $e->getMessage());
        }
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  int  $id
     * @return \Response
     */
    public function update($id, OrderRequest $request)
    {
        try {
            $order = $this->order->where('id', $id)->first();
            $order->fill($request->input())->save();

            return redirect()->back()->with('success', \Lang::get('message.updated-successfully'));
        } catch (\Exception $e) {
            Bugsnag::notifyExeption($e);

            return redirect()->back()->with('fails', $e->getMessage());
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Response
     */
    public function deleteBulkOrders(Request $request)
    {
        try {
            $ids = $request->input('order_ids', []);

            if (empty($ids)) {
                return errorResponse(__('message.select-a-row'));
            }

            $orderIds = $this->order->whereIn('id', $ids)->pluck('id');

            $installationDetails = InstallationDetail::whereIn('order_id', $orderIds)
                ->where('installation_path', '!=', cloudCentralDomain())
                ->pluck('installation_path');

            foreach ($installationDetails as $path) {
                event(new UserOrderDelete($path));
            }

            $this->order->whereIn('id', $orderIds)->delete();

            return successResponse(__('message.deleted-successfully'));
        } catch (\Exception $e) {
            return errorResponse($e->getMessage());
        }
    }

    public function plan($invoice_item_id)
    {
        try {
            $planid = 0;
            $item = $this->invoice_items->find($invoice_item_id);
            if ($item) {
                $planid = $item->plan_id;
            }

            return $planid;
        } catch (\Exception $ex) {
            throw new \Exception($ex->getMessage());
        }
    }

    public function checkInvoiceStatusByOrderId($orderid)
    {
        try {
            $status = 'pending';
            $order = $this->order->find($orderid);
            if ($order) {
                $invoiceid = $order->invoice_id;
                $invoice = $this->invoice->find($invoiceid);
                if ($invoice) {
                    if ($invoice->status == 'Success') {
                        $status = 'success';
                    }
                }
            }

            return $status;
        } catch (\Exception $ex) {
            throw new \Exception($ex->getMessage());
        }
    }

    public function product($itemid)
    {
        $invoice_items = new InvoiceItem();
        $invoice_item = $invoice_items->find($itemid);
        $product = $invoice_item->product_name;

        return $product;
    }

    public function subscription($orderid)
    {
        $sub = $this->subscription->where('order_id', $orderid)->first();

        return $sub;
    }

    public function expiry($orderid)
    {
        $sub = $this->subscription($orderid);
        if ($sub) {
            return $sub->update_ends_at;
        }

        return '';
    }

    public function renew($orderid)
    {
        //$sub = $this->subscription($orderid);
        return url('my-orders');
    }

    public function exportOrders(Request $request)
    {
        try {
            ini_set('memory_limit', '-1');

            $selectedColumns = $request->input('selected_columns', []);

            $searchParams = $request->only([
                'order_no', 'product_id', 'expiry', 'expiryTill', 'from', 'till',
                'sub_from', 'sub_till', 'ins_not_ins', 'domain', 'p_un', 'act_ins',
                'renewal', 'inact_ins', 'version',
            ]);

            $email = \Auth::user()->email;

            $driver = QueueService::where('status', '1')->first();

            if ($driver->name === 'Sync') {
                return errorResponse(__('message.cannot_sync_queue_driver'));
            }

            // Set the queue driver dynamically
            app('queue')->setDefaultDriver($driver->short_name);

            ReportExport::dispatch('orders', $selectedColumns, $searchParams, $email)
                ->onQueue('reports');

            return successResponse(__('message.system_generating_report'));
        } catch (\Exception $e) {
            \Log::error(__('message.export_failed').$e->getMessage());

            return errorResponse($e->getMessage());
        }
    }

    public function getPaymentByOrderId(Request $request, $orderId)
    {
        try {
            $searchQuery = $request->input('search-query', '');
            $sortOrder = $request->input('sort-order', 'asc');
            $sortField = $request->input('sort-field', 'created_at');
            $limit = $request->input('limit', 10);
            $page = $request->input('page', 1);

            $order = Order::with([
                'user:id,first_name,last_name,email',
                'invoiceRelation',
            ])->findOrFail($orderId);

            $invoiceIds = $order->invoiceRelation->pluck('invoice_id')->toArray();

            $payments = Payment::whereIn('invoice_id', $invoiceIds)
                ->select(['id', 'invoice_id', 'user_id', 'amount', 'payment_method', 'payment_status', 'created_at'])
                ->when($searchQuery, function ($query) use ($searchQuery) {
                    $query->where(function ($q) use ($searchQuery) {
                        $q->where('payment_method', 'like', "%{$searchQuery}%")
                            ->orWhere('payment_status', 'like', "%{$searchQuery}%")
                            ->orWhere('amount', 'like', "%{$searchQuery}%")
                            ->orWhereHas('invoice', function ($inv) use ($searchQuery) {
                                $inv->where('number', 'like', "%{$searchQuery}%");
                            });
                    });
                })
                ->orderBy($sortField, $sortOrder)
                ->paginate($limit, ['*'], 'page', $page);

            $payments->getCollection()->transform(function ($payment) {
                return [
                    'id' => $payment->id,
                    'invoice_number' => $payment->invoice->number,
                    'user_id' => $payment->user_id,
                    'amount' => currencyFormat($payment->amount, $payment->invoice->currency),
                    'payment_method' => $payment->payment_method,
                    'payment_status' => $payment->payment_status,
                    'created_at' => $payment->created_at,
                ];
            });

            return successResponse('', $payments);
        } catch (Exception $ex) {
            return errorResponse($ex->getMessage());
        }
    }

    public function getOrderInvoices(Request $request, $orderId)
    {
        $searchQuery = $request->input('search-query', '');
        $sortOrder = $request->input('sort-order', 'asc');
        $sortField = $request->input('sort-field', 'created_at');
        $limit = $request->input('limit', 10);
        $page = $request->input('page', 1);

        $order = Order::with('user:id,first_name,last_name,email')->findOrFail($orderId);

        $invoices = $order->invoices()
            ->with(['invoiceItem:id,invoice_id,product_name'])
            ->orderBy($sortField, $sortOrder)
            ->paginate($limit, ['*'], 'page', $page);

        $invoices->getCollection()->transform(function ($invoice) {
            return [
                'id' => $invoice->id,
                'number' => $invoice->number,
                'amount' => currencyFormat($invoice->grand_total, $invoice->currency),
                'status' => $invoice->status,
                'date' => $invoice->date,
                'products' => $invoice->invoiceItem->pluck('product_name')->toArray(),
            ];
        });

        return successResponse('', $invoices);
    }
}
