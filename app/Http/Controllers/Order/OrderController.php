<?php

namespace App\Http\Controllers\Order;

use App\Events\UserOrderDelete;
use App\Http\Requests\Order\OrderRequest;
use App\Jobs\ReportExport;
use App\License\Services\LicenseService;
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
use Auth;
use Exception;
use Illuminate\Http\Request;
use Lang;
use Logger;

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

    public function getOrders(Request $request)
    {
        try {
            $searchQuery = $request->input('search-query', '');
            $sortOrder = $request->input('sort-order', 'desc');
            $sortField = $request->input('sort-field', 'created_at');
            $limit = $request->input('limit', 10);

            $allowedSorts = ['created_at', 'number', 'order_status', 'update_ends_at'];
            if (! in_array($sortField, $allowedSorts, true)) {
                $sortField = 'created_at';
            }

            $orderSearch = new OrderSearchController();
            $query = $orderSearch->advanceOrderSearch($request);
            $query = $orderSearch->applyOrdersSearch($query, $searchQuery);

            $paginated = $query->orderBy($sortField, $sortOrder)
                ->simplePaginate($limit);

            $paginated->getCollection()->transform(function ($order) {
                $user = $order->user;
                if ($user && $user->country) {
                    $name = getCountryByCode($user->country) ?? $user->country;
                    $user->setRawAttributes(array_merge($user->getAttributes(), ['country' => $name]), true);
                }

                $installedVersions = $order->installationDetail ? $order->installationDetail->pluck('version')->toArray() : [];
                $latestVersion = count($installedVersions) ? max($installedVersions) : null;

                $licenseAgents = substr((string) $order->serial_key, 12, 16) === '0000'
                    ? 'Unlimited'
                    : intval(substr((string) $order->serial_key, 12, 16), 10);

                return [
                    'id' => $order->id,
                    'number' => $order->number,
                    'order_status' => ucfirst((string) $order->order_status),
                    'product_name' => $order->productRelation?->name,
                    'product_id' => $order->product,
                    'group' => $order->productRelation?->groupRelation?->name,
                    'group_id' => $order->productRelation?->group,
                    'plan' => $order->subscription->plan?->name,
                    'plan_id' => $order->subscription->plan?->id,
                    'version' => $latestVersion ? getVersionAndLabel($latestVersion, $order->product) : null,
                    'agents' => $licenseAgents,
                    'status' => ! empty($order->installationDetail) ? 'Active' : 'Inactive',
                    'order_date' => $order->created_at,
                    'update_ends_at' => strtotime((string) $order->subscription->ends_at) > 1 ? $order->subscription->ends_at : null,
                    'subscription_updated_at' => $order->subscription->updated_at,
                    'user' => $user,
                ];
            });

            return successResponse('', $paginated);
        } catch (Exception $e) {
            return errorResponse($e->getMessage());
        }
    }

    public function getOrder($id)
    {
        $order = $this->order
            ->with([
                'user:id,first_name,last_name,email,mobile,mobile_code,address,country',
                'user.countryRelation:country_code_char2,country_name',
                'subscription.plan:id,name',
                'productRelation:id,name',
            ])
            ->findOrFail($id);

        if (! $order->user || $order->user->trashed()) {
            return errorResponse(__('message.user_suspended_restore_to_view'), 403);
        }

        $subscription = $order->subscription;

        $expiryDates = [
            'subscription_end' => $subscription && strtotime((string) $subscription->ends_at) > 1 ? getExpiryLabel($subscription->ends_at) : null,
            'update_end' => $subscription && strtotime((string) $subscription->update_ends_at) > 1 ? getExpiryLabel($subscription->update_ends_at) : null,
            'support_end' => $subscription && strtotime((string) $subscription->support_ends_at) > 1 ? getExpiryLabel($subscription->support_ends_at) : null,
        ];

        $paymentLog = Payment_log::where('order', $order->number)
            ->where('payment_type', 'Payment method updated')
            ->orderBy('id', 'desc')
            ->first(['payment_method', 'date']);

        $license = resolve(LicenseService::class)->findByCode($order->serial_key);

        return successResponse('', [
            'order' => $order,
            'license_details' => [
                'licence_code'      => $order->serial_key,
                'expiry_dates'      => $expiryDates,
                'installation_limit' => $license?->license_limit,
            ],
            'autorenewal' => $order->subscription->autoRenew_status,
            'is_subscribed' => $order->subscription->is_subscribed,
            'payment_log' => $paymentLog,
        ]);
    }

    public function getInstallationDetails($orderId)
    {
        try {
            $rows = InstallationDetail::where('order_id', $orderId)->get();

            $installationDetails = $rows->map(fn ($row) => [
                'path' => $row->installation_path,
                'ip' => $row->installation_ip,
                'version' => $row->version ?? null,
                'status' => $row->installation_status,
                'last_active_date' => $row->last_active,
            ])->values()->all();

            return successResponse('', $installationDetails);
        } catch (Exception $ex) {
            return errorResponse($ex->getMessage());
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

            return back()->with('success', Lang::get('message.updated-successfully'));
        } catch (Exception $e) {
            return back()->with('fails', $e->getMessage());
        }
    }

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
        } catch (Exception $e) {
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
        } catch (Exception $ex) {
            throw new Exception($ex->getMessage());
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
        } catch (Exception $ex) {
            throw new Exception($ex->getMessage());
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

            $email = Auth::user()->email;

            $driver = QueueService::where('status', '1')->first();

            if ($driver->name === 'Sync') {
                return errorResponse(__('message.cannot_sync_queue_driver'));
            }

            resolve('queue')->setDefaultDriver($driver->short_name);

            dispatch(new ReportExport('orders', $selectedColumns, $searchParams, $email))
                ->onQueue('reports');

            return successResponse(__('message.system_generating_report'));
        } catch (Exception $e) {
            Logger::exception($e);

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

            $order = Order::with([
                'user:id,first_name,last_name,email',
                'invoices',
            ])->findOrFail($orderId);

            $invoiceIds = $order->invoices->pluck('id')->toArray();

            $payments = Payment::whereIn('invoice_id', $invoiceIds)
                ->select(['id', 'invoice_id', 'user_id', 'amount', 'payment_method', 'payment_status', 'created_at'])
                ->when($searchQuery, function ($query) use ($searchQuery): void {
                    $query->where(function ($q) use ($searchQuery): void {
                        $q->where('payment_method', 'like', "%{$searchQuery}%")
                            ->orWhere('payment_status', 'like', "%{$searchQuery}%")
                            ->orWhere('amount', 'like', "%{$searchQuery}%")
                            ->orWhereHas('invoice', function ($inv) use ($searchQuery): void {
                                $inv->where('number', 'like', "%{$searchQuery}%");
                            });
                    });
                })
                ->orderBy($sortField, $sortOrder)
                ->simplePaginate($limit);

            $payments->getCollection()->transform(fn ($payment) => [
                'id' => $payment->id,
                'invoice_number' => $payment->invoice->number,
                'user_id' => $payment->user_id,
                'amount' => currencyFormat($payment->amount, $payment->invoice->currency),
                'payment_method' => $payment->payment_method,
                'payment_status' => $payment->payment_status,
                'created_at' => $payment->created_at,
            ]);

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

        $order = Order::with('user:id,first_name,last_name,email')->findOrFail($orderId);

        $invoices = $order->invoices()
            ->with(['invoiceItem:id,invoice_id,product_name'])
            ->orderBy($sortField, $sortOrder)
            ->simplePaginate($limit);

        $invoices->getCollection()->transform(fn ($invoice) => [
            'id' => $invoice->id,
            'number' => $invoice->number,
            'amount' => currencyFormat($invoice->grand_total, $invoice->currency),
            'status' => $invoice->status,
            'date' => $invoice->date,
            'products' => $invoice->invoiceItem->pluck('product_name')->toArray(),
        ]);

        return successResponse('', $invoices);
    }
}
