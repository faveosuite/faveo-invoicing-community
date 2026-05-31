<?php

namespace App\Http\Controllers\Front;

use App\ApiKey;
use App\Auto_renewal;
use App\Http\Controllers\Github\GithubApiController;
use App\Http\Controllers\License\LicensePermissionsController;
use App\Http\Controllers\Order\RenewController;
use App\Model\Common\CreditActivity;
use App\Model\Common\Setting;
use App\Model\Common\StatusSetting;
use App\Model\Github\Github;
use App\Model\Order\Invoice;
use App\Model\Order\InvoiceItem;
use App\Model\Order\Order;
use App\Model\Order\OrderInvoiceRelation;
use App\Model\Order\Payment;
use App\Model\Payment\Plan;
use App\Model\Plugin;
use App\Model\Product\Product;
use App\Model\Product\ProductUpload;
use App\Model\Product\Subscription;
use App\Payment_log;
use App\Plugins\Stripe\Controllers\SettingsController;
use App\User;
use App\WhatsappIntegration;
use Exception;
use GuzzleHttp\Client;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Razorpay\Api\Api;

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
     * Create new Auto renewal and update auto-renewal status.
     *
     * @param  Request  $request
     * @return array{type:string,message:string}|JsonResponse
     */
    public function enableAutorenewalStatus(Request $request)
    {
        try {
            $currency = getCurrencyForClient(\Auth::user()->country);
            $amount = getMinimumAmountForPayments($currency, 'stripe');
            $orderid = $request->get('order_id');

            $order = Order::findOrFail($orderid);

            if (! authorizeOwnership($order->client)) {
                return errorResponse(__('message.unauthorized_action'), 403);
            }

            $url = url('my-order/'.$orderid.'#auto-renew');
            $controller = new SettingsController();
            $confirm = $controller->handlePayment($request, $amount, $currency, $url);

            $paymentIntent = \Stripe\PaymentIntent::retrieve($confirm['id']);
            $subscription = Subscription::where('order_id', $orderid)->first();
            if ($confirm->status == 'requires_action') {
                $redirectUrl = $paymentIntent->next_action->redirect_to_url->url;

                return $redirectUrl;
            } elseif ($confirm->status === 'succeeded') {
                $refund = \Stripe\Refund::create([
                    'payment_intent' => $confirm['id'],
                    'amount' => $confirm['amount'],
                ]);
                $invoice_id = OrderInvoiceRelation::where('order_id', $orderid)->value('invoice_id');
                $number = Invoice::where($paymentIntent->customerid)->value('number');
                $customer_details = [
                    'user_id' => \Auth::user()->id,
                    'customer_id' => $paymentIntent->customer,
                    'payment_method' => 'stripe',
                    'order_id' => $orderid,
                    'payment_intent_id' => $paymentIntent->payment_method,
                ];
                Auto_renewal::create($customer_details);
                Subscription::where('order_id', $orderid)->update(['is_subscribed' => '1', 'autoRenew_status' => '1']);
                $mail = new \App\Http\Controllers\Common\PhpMailController();

                $mail->payment_log(\Auth::user()->email, 'stripe', 'success', Order::where('id', $orderid)->value('number'), null, $amount, 'Payment method updated');

                $response = ['type' => 'success', 'message' => __('message.card_details_updated_successfully')];

                return ['type' => 'success', 'message' => __('message.card_details_updated_successfully')];
            }
        } catch(\Exception $ex) {
            $result = $ex->getMessage();
            $mail = new \App\Http\Controllers\Common\PhpMailController();
            $mail->payment_log(\Auth::user()->email, 'stripe', 'failed', Order::where('id', $orderid)->value('number'), $result, $amount, 'Payment method updated');
            $errorMessage = __('message.something_different_payment');

            return response()->json(['error' => $errorMessage], 500);
        }
    }

    /**
     *  Delete Auto renewal and update auto-renewal status.
     *
     * @param  Request  $request
     * @return JsonResponse
     */
    public function disableAutorenewalStatus(Request $request)
    {
        try {
            $orderid = $request->get('order_id');
            $userid = Subscription::where('order_id', $orderid)->value('user_id');
            User::findOrfail($userid);

            if (! authorizeOwnership($userid)) {
                return errorResponse(__('message.unauthorized_action'), 403);
            }

            $subscription = Subscription::where('order_id', $orderid)->first();
            $this->autoRenewalSubOps($subscription, $orderid);
            $response = ['type' => 'success', 'message' => __('message.auto_subscription_disabled')];

            return response()->json($response);
        } catch(\Exception $ex) {
            $result = $ex->getMessage();

            return response()->json(compact('result'), 500);
        }
    }

    private function autoRenewalSubOps($subscription, $orderid)
    {
        if ($subscription->rzp_subscription && $subscription->is_subscribed && $subscription->subscribe_id) {
            app(\App\Services\Payment\SubscriptionService::class)->cancelSubscription('Razorpay', $subscription->subscribe_id);
            Subscription::where('order_id', $orderid)->update(['is_subscribed' => '0', 'rzp_subscription' => '0']);
        } elseif ($subscription->autoRenew_status && $subscription->is_subscribed && $subscription->subscribe_id) {
            app(\App\Services\Payment\SubscriptionService::class)->cancelSubscription('Stripe', $subscription->subscribe_id);
            Subscription::where('order_id', $orderid)->update(['is_subscribed' => '0', 'autoRenew_status' => '0']);
        } else {
            Subscription::where('order_id', $orderid)->update(['is_subscribed' => '0', 'autoRenew_status' => '0', 'rzp_subscription' => '0']);
        }
    }

    /**
     *  Setup razorpay , create auto renewal and update auto renewal status.
     *
     * @param  Request  $request
     * @return RedirectResponse
     */
    public function enableRzpStatus(Request $request)
    {
        try {
            $currency = getCurrencyForClient(\Auth::user()->country);
            $amount = currencyFormat('1', $currency);
            $orderid = $request->route('orderid');
            $subscription = Subscription::where('order_id', $orderid)->first();

            User::findOrfail($subscription->user_id);

            if (! authorizeOwnership($subscription->user_id)) {
                return redirect()->back()->with('fails', __('message.unauthorized_action'));
            }

            $input = $request->all();
            $error = 'Payment Failed';
            $rzp_key = ApiKey::where('id', 1)->value('rzp_key');
            $rzp_secret = ApiKey::where('id', 1)->value('rzp_secret');
            $api = new Api($rzp_key, $rzp_secret);

            $payment = $api->payment->fetch($input['razorpay_payment_id']);
            $response = $api->payment->fetch($input['razorpay_payment_id']);
            $capture = $api->payment->fetch($response->id)->capture(['amount' => $response->amount]);
            $refund = $api->payment->fetch($response->id)->refund(['amount' => $response->amount, 'speed' => 'normal']);

            $invoice_id = OrderInvoiceRelation::where('order_id', $orderid)->value('invoice_id');
            $number = Invoice::where('id', $invoice_id)->value('number');

            $customer_details = [
                'user_id' => \Auth::user()->id,
                'customer_id' => $response['id'],
                'payment_method' => 'razorpay',
                'order_id' => $orderid,
            ];
            Auto_renewal::create($customer_details);

            Subscription::where('order_id', $orderid)->update(['is_subscribed' => '1', 'rzp_subscription' => '1']);

            $mail = new \App\Http\Controllers\Common\PhpMailController();
            $mail->payment_log(\Auth::user()->email, 'Razorpay', 'success', Order::where('id', $orderid)->value('number'), null, $amount, 'Payment method updated');

            return redirect()->back()->with('success', __('message.card_updated_successfully'));
        } catch(\Exception $ex) {
            $result = $ex->getMessage();
            $mail = new \App\Http\Controllers\Common\PhpMailController();
            $mail->payment_log(\Auth::user()->email, 'stripe', 'failed', Order::where('id', $orderid)->value('number'), $result, $amount, 'Payment method updated');

            return redirect()->back()->with('fails', __('message.payment_declined', ['msg' => $ex->getMessage()]));
        }
    }

    /**
     *  Auto-renew by id and redirect to paynow page.
     *
     * @param
     * @return RedirectResponse
     */
    public function autoRenewbyid()
    {
        try {
            $id = request()->route('id');
            $order_id = \DB::table('order_invoice_relations')->where('invoice_id', $id)->value('order_id');
            $sub = Subscription::where('order_id', $order_id)->first();
            $planid = $sub->plan_id;
            $plan = Plan::find($planid);
            $planDetails = userCurrencyAndPrice($sub->user_id, $plan);
            if (is_null($planDetails['plan'])) {
                throw new \Exception(__('message.no_available_plans_currency'));
            }
            $cost = $planDetails['plan']->renew_price;
            $currency = $planDetails['currency'];
            $controller = new RenewController();
            $items = InvoiceItem::where('invoice_id', $id)->first();
            $invoiceid = $items->invoice_id;
            // $this->setSession($id, $planid);

            return redirect('paynow/'.$id);
        } catch(\Exception $ex) {
            return redirect('my-orders')->with('fails', $ex->getMessage());
        }
    }

    /**
     *  Show the invoice to the client.
     *
     * @param  request  $request
     * @return \Illuminate\Contracts\View\View|RedirectResponse
     */
    public function invoices(Request $request)
    {
        try {
            $amt = Payment::where('user_id', \Auth::user()->id)->where('payment_method', 'Credit Balance')->where('payment_status', 'success')->value('amt_to_credit');
            $formattedValue = currencyFormat($amt, getCurrencyForClient(\Auth::user()->country), true);
            $payment_id = Payment::where('user_id', \Auth::user()->id)->where('payment_method', 'Credit Balance')->where('payment_status', 'success')->value('id');
            $payment_activity = CreditActivity::where('payment_id', $payment_id)->where('role', 'user')->orderBy('created_at', 'desc')->get();

            return view('themes.default1.front.clients.invoice', compact('request', 'formattedValue', 'payment_activity'));
        } catch (Exception $ex) {
            return redirect()->back()->with('fails', $ex->getMessage());
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
            ->where('user_id', \Auth::id());

        if ($search = trim($request->input('search-query', ''))) {
            $query->where(function ($q) use ($search) {
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
            $user = \Auth::user();

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
                'user' => [
                    'name' => ucfirst($user->first_name ?? '').' '.ucfirst($user->last_name ?? ''),
                    'email' => $user->email,
                    'mobile' => ($user->mobile_code ? '(+'.$user->mobile_code.') ' : '').($user->mobile ?? ''),
                    'address' => $user->address ?? '',
                ],
            ]);
        }

        if ($search = trim($request->input('search-query', ''))) {
            $query->where(function ($q) use ($search) {
                $q->where('number', 'like', "%{$search}%")
                  ->orWhereHas('productRelation', fn ($pq) => $pq->where('name', 'like', "%{$search}%"));
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

    public function renewPopupVue(Request $request, int $productid)
    {
        try {
            $user = \Auth::user();
            $currency = getCurrencyForClient($user->country);
            $isCloud = in_array($productid, cloudPopupProducts());

            $plans = Plan::join('products', 'plans.product', '=', 'products.id')
                ->leftJoin('plan_prices', 'plans.id', '=', 'plan_prices.plan_id')
                ->where('plans.product', $productid)
                ->where('plans.status', 1)
                ->where('plan_prices.currency', $currency)
                ->where('plan_prices.renew_price', '!=', '0')
                ->where('plans.days', '!=', 14)
                ->select('plans.id', 'plans.name', 'plan_prices.renew_price')
                ->get();

            $planOptions = $plans->map(function ($plan) use ($isCloud, $currency) {
                $label = $plan->name;
                if ($isCloud) {
                    $label .= ' (Plan price-per agent: '.currencyFormat($plan->renew_price, $currency, true).')';
                } else {
                    $label .= ' (Renewal price: '.currencyFormat($plan->renew_price, $currency, true).')';
                }

                return ['id' => $plan->id, 'name' => $label];
            })->values();

            if ($isCloud) {
                $planOptions = $planOptions->filter(fn ($p) => stripos($p['name'], 'free') === false)->values();
            }

            return successResponse('', [
                'plans' => $planOptions,
                'user_id' => $user->id,
                'is_cloud' => $isCloud,
            ]);
        } catch (\Exception $e) {
            return errorResponse($e->getMessage());
        }
    }

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

            $paginated->getCollection()->transform(function ($model) {
                return [
                    'id' => $model->id,
                    'number' => $model->number,
                    'date' => $model->date,
                    'grand_total' => currencyFormat($model->grand_total, $model->currency),
                    'status' => $model->status,
                ];
            });

            return successResponse('', $paginated);
        } catch (\Exception $e) {
            return errorResponse($e->getMessage());
        }
    }

    /**
     *  Show the invoice to the client.
     *
     * @param  $id
     * @return \Illuminate\Contracts\View\View|RedirectResponse
     *
     * @throws \Exception
     */
    public function getInvoice($id)
    {
        try {
            $invoice = $this->invoice->find($id);
            if (! $invoice) {
                throw new \Exception(__('message.invoice_not_found'));
            }

            if ($invoice->user_id != \Auth::id()) {
                throw new \Exception(__('message.invalid_invoice_modification'));
            }

            $data = $this->prepareInvoiceData($invoice);

            return view('themes.default1.front.clients.show-invoice', array_merge(['invoice' => $invoice], $data));
        } catch (\Exception $ex) {
            return redirect()->route('my-invoices')->with('fails', $ex->getMessage());
        }
    }

    public function prepareInvoiceData($invoice, $user = null)
    {
        $payments = $invoice->payment;
        $user = $user ?? \Auth::user();
        $items = $invoice->invoiceItem()->get();

        $orderIDs = $invoice->orderRelation()->pluck('order_id')->toArray();

        $items->each(function ($item) use ($orderIDs) {
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
        $taxName = [];

        foreach ($items as $item) {
            $itemsSubtotal += floatval($item->subtotal);

            if ($item->tax_name != 'null') {
                $taxAmt += floatval($item->subtotal);
            }

            $taxName[] = $item->tax_name.'@'.$item->tax_percentage;
        }

        $taxName = array_unique($taxName);

        $gstSplit = [];

        foreach ($taxName as $tax) {
            [$name, $percentage] = explode('@', $tax);
            if ($name == 'null') {
                continue;
            }

            $split = bifurcateTax($name, $percentage, $invoice->currency, $user->state, $taxAmt);

            $gstSplit[] = [
                'name' => $name,
                'percentage' => $percentage,
                'labels' => explode('<br>', $split['html']),
                'values' => explode('<br>', $split['tax']),
            ];
        }

        $values = array_column($gstSplit, 'values');

        $taxDeducted = array_sum(
            array_map(
                fn ($v) => (float) preg_replace('/[^0-9.\-]/', '', $v),
                array_merge(...$values)
            )
        );

        $processingFeeAmount = 0;

        if ($invoice->processing_fee && $invoice->processing_fee != '0%') {
            $percent = floatval(filter_var(
                $invoice->processing_fee,
                FILTER_SANITIZE_NUMBER_FLOAT,
                FILTER_FLAG_ALLOW_FRACTION
            ));

            $processingFeeAmount = ($percent / 100) * ($itemsSubtotal + $taxDeducted);
        }
        $base64 = '';
        if ($set->logo) {
            $type = pathinfo($set->logo, PATHINFO_EXTENSION);
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
    public function getVersionList(Request $request, $productid, $clientid, $invoiceid)
    {
        try {
            if (! authorizeOwnership((int) $clientid)) {
                return errorResponse(__('message.unauthorized_action'), 403);
            }

            $searchValue = $request->input('search.value');
            $invoice_id = Invoice::where('number', $invoiceid)->where('user_id', $clientid)->pluck('id')->first();

            $orders = OrderInvoiceRelation::where('invoice_id', $invoice_id)->get([
                'order_id',
            ])->toArray();

            $order = Order::whereIn('id', $orders)->where('product', $productid)->where('client', $clientid)->firstOrFail();

            $order_id = $order->id;

            $versions = ProductUpload::where('product_id', $productid)->where('is_private', 0)
                ->select(
                    'id',
                    'product_id',
                    'version',
                    'title',
                    'description',
                    'file',
                    'created_at',
                    'release_type'
                )
                ->latest();
            if ($searchValue) {
                $versions->where(function ($query) use ($searchValue) {
                    $query->where('version', 'LIKE', '%'.$searchValue.'%')
                        ->orWhere('title', 'LIKE', '%'.$searchValue.'%')
                        ->orWhere('description', 'LIKE', '%'.$searchValue.'%');
                });
            }

            $updatesEndDate = Subscription::select('update_ends_at')
                ->where('product_id', $productid)
                ->where('order_id', $order_id)
                ->first();

            $downloadPermission = LicensePermissionsController::getPermissionsForProduct($productid);

            return \DataTables::of($versions)
                ->addColumn('id', function ($version) {
                    return ucfirst($version->id);
                })
                ->addColumn('version', function ($version) {
                    return ucfirst($version->version).' '.getPreReleaseStatusLabel($version->release_type);
                })
                ->addColumn('title', function ($version) {
                    return ucfirst($version->title);
                })
                ->addColumn('description', function ($version) {
                    return ucfirst($version->description);
                })
                ->addColumn('file', function ($version) use ($downloadPermission, $updatesEndDate, $productid, $clientid, $invoiceid) {
                    if ($updatesEndDate) {
                        if ($downloadPermission['allowDownloadTillExpiry'] == 1) {
                            return $this->whenDownloadTillExpiry($updatesEndDate, $productid, $version, $clientid, $invoiceid);
                        } elseif ($downloadPermission['allowDownloadTillExpiry'] == 0) {
                            return $this->whenDownloadExpiresAfterExpiry($updatesEndDate, $productid, $version, $clientid, $invoiceid);
                        }
                    }
                })
                ->rawColumns(['version', 'title', 'description', 'file'])
                ->make(true);
        } catch (Exception $ex) {
            return errorResponse($ex->getMessage());
        }
    }

    /**
     * Get list of all the versions from Github.
     *
     * @param  type  $productid
     * @param  type  $clientid
     * @param  type  $invoiceid
     */
    public function getGithubVersionList($productid, $clientid, $invoiceid)
    {
        try {
            if (! authorizeOwnership((int) $clientid)) {
                return errorResponse(__('message.unauthorized_action'), 403);
            }
            $products = $this->product::where('id', $productid)
            ->select('name', 'version', 'github_owner', 'github_repository')->get();
            $owner = '';
            $repo = '';
            foreach ($products as $product) {
                $owner = $product->github_owner;
                $repo = $product->github_repository;
            }
            $url = "https://api.github.com/repos/$owner/$repo/releases";
            $countExpiry = 0;
            $link = $this->github_api->getCurl1($url);
            $link = $link['body'];
            $countVersions = 3; //because we are taking only the first 10 versions
            $link = array_slice($link, 0, 3, true);
            $order = Order::whereIn('id', \App\Model\Order\OrderInvoiceRelation::where('invoice_id', $invoiceid)->pluck('order_id'))->first();
            $order_id = $order->id;
            $orderEndDate = Subscription::select('update_ends_at')
                        ->where('product_id', $productid)->where('order_id', $order_id)->first();
            if ($orderEndDate) {
                foreach ($link as $lin) {
                    if (strtotime($lin['created_at']) < strtotime($orderEndDate->update_ends_at) || $orderEndDate->update_ends_at == '0000-00-00 00:00:00') {
                        $countExpiry = $countExpiry + 1;
                    }
                }
            }

            return \DataTables::of($link)
                            ->addColumn('version', function ($link) {
                                return ucfirst($link['tag_name']);
                            })
                            ->addColumn('name', function ($link) {
                                return ucfirst($link['name']);
                            })
                            ->addColumn('description', function ($link) {
                                $markdown = Str::markdown(ucfirst($link['body']));

                                return '<div class="markdown-output">'.$markdown.'</div>';
                            })
                            ->addColumn('file', function ($link) use ($countExpiry, $countVersions, $invoiceid, $productid) {
                                $order = Order::whereIn('id', \App\Model\Order\OrderInvoiceRelation::where('invoice_id', $invoiceid)->pluck('order_id'))->first();
                                $order_id = $order->id;
                                $orderEndDate = Subscription::select('update_ends_at')
                                ->where('product_id', $productid)->where('order_id', $order_id)->first();
                                if ($orderEndDate) {
                                    $actionButton = $this->getActionButton($countExpiry, $countVersions, $link, $orderEndDate, $productid);

                                    return $actionButton;
                                } elseif (! $orderEndDate) {
                                    $link = $this->github_api->getCurl1($link['zipball_url']);

                                    return '<p><a href="'.$link['header']['Location'].'" class="btn btn-sm btn-primary">'
                                        .__('message.download').
                                        '</a>&nbsp;</p>';
                                }
                            })
                            ->rawColumns(['version', 'name', 'description', 'file'])
                            ->make(true);
        } catch (Exception $ex) {
            echo $ex->getMessage();
        }
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
            'productRelation:id,name,github_owner,github_repository,type',
            'subscription:id,order_id,version,update_ends_at,ends_at',
            'invoiceItem:id,agents',
            'invoices' => fn ($q) => $q->select('invoices.id', 'invoices.number')->latest('invoices.id'),
        ])
        ->where('client', \Auth::id());
    }

    /**
     *  Returns to client profile page with needed variables.
     *
     * @param
     * @return \Illuminate\Contracts\View\View|RedirectResponse
     *
     * @throws Exception
     */
    public function profile(Request $request)
    {
        try {
            $user = $this->user->where('id', \Auth::user()->id)->first();

            if ($request->expectsJson()) {
                return successResponse('', ['user' => $user]);
            }

            $is2faEnabled = $user->is_2fa_enabled;
            $dateSinceEnabled = $user->google2fa_activation_date;
            $timezonesList = \App\Model\Common\Timezone::get();
            foreach ($timezonesList as $timezone) {
                $location = $timezone->location;
                if ($location) {
                    $start = strpos($location, '(');
                    $end = strpos($location, ')', $start + 1);
                    $length = $end - $start;
                    $result = substr($location, $start + 1, $length - 1);
                    $display[] = ['id' => $timezone->id, 'name' => '('.$result.')'.' '.$timezone->name];
                }
            }
            //for display
            $timezones = array_column($display, 'name', 'id');
            $state = getStateByCode($user->country, $user->state);
            $states = findStateByRegionId($user->country);
            $bussinesses = \App\Model\Common\Bussiness::pluck('name', 'short')->toArray();
            $selectedIndustry = \App\Model\Common\Bussiness::where('name', $user->bussiness)
            ->pluck('name', 'short')->toArray();
            $selectedCompany = \DB::table('company_types')->where('name', $user->company_type)
            ->pluck('name', 'short')->toArray();
            $selectedCompanySize = \DB::table('company_sizes')->where('short', $user->company_size)
            ->pluck('name', 'short')->toArray();

            $selectedCountry = \DB::table('countries')->where('country_code_char2', $user->country)
            ->value('country_name');

            return view(
                'themes.default1.front.clients.profile',
                compact('user', 'timezones', 'state', 'states', 'bussinesses', 'is2faEnabled', 'dateSinceEnabled', 'selectedIndustry', 'selectedCompany', 'selectedCompanySize', 'selectedCountry')
            );
        } catch (Exception $ex) {
            if ($request->expectsJson()) {
                return errorResponse($ex->getMessage());
            }

            return redirect()->back()->with('fails', $ex->getMessage());
        }
    }

    public function generateMerchantRandomString($length = 10)
    {
        return substr(bin2hex(random_bytes($length)), 0, $length);
    }

    /**
     *  Returns to individual order page.
     *
     * @param  int  $id
     * @return \Illuminate\Contracts\View\View|RedirectResponse
     *
     * @throws Exception
     */
    public function getOrder($id)
    {
        try {
            $user = \Auth::user();
            $order = $this->order->findOrFail($id);
            if ($order->client != $user->id) {
                throw new \Exception(trans('message.order_error_modification'));
            }
            $invoice = $order->invoice()->first();
            $items = $order->invoice()->first()->invoiceItem()->get();
            $subscription = $order->subscription()->first();
            $date = '--';
            $licdate = '--';
            $versionLabel = '--';
            if ($subscription) {
                $date = strtotime($subscription->update_ends_at) > 1 ? getExpiryLabel($subscription->update_ends_at, 'badge') : '--';
                $licdate = strtotime($subscription->ends_at) > 1 ? getExpiryLabel($subscription->ends_at, 'badge') : '--';
            }
            $product = $order->product()->first();
            $price = $product->price()->first();

            $allowDomainStatus = StatusSetting::pluck('domain_check')->first();
            $installationDetails = [];

            $installationDetails = app(\App\License\Services\InstallationService::class)->getInstallationsByProduct($order->serial_key, $order->product);

            $statusAutorenewal = Subscription::where('order_id', $id)->value('is_subscribed');

            $status = Subscription::where('order_id', $id)->value('autoRenew_status');
            $currency = getCurrencyForClient(\Auth::user()->country);
            $amount = currencyFormat(1, $currency);
            $payment_log = Payment_log::where('order', $order->number)
            ->where('amount', $amount)
            ->where('payment_type', 'Payment method updated')
            ->orderBy('id', 'desc')
            ->first();

            $relation = $order->invoiceRelation()->pluck('invoice_id')->toArray();
            if (count($relation) > 0) {
                $invoices = $relation;
            } else {
                $invoices = $order->invoice()->pluck('id')->toArray();
            }

            $recentPayment = $this->payment->whereIn('invoice_id', $invoices)
                ->select('id', 'invoice_id', 'user_id', 'amount', 'payment_method', 'payment_status', 'created_at')
                ->orderByDesc('created_at')
                ->first();

            $merchant_orderid = $this->generateMerchantRandomString();

            [$rzp_key, $rzp_secret,$apilayer_key,$stripe_key] = array_values(ApiKey::select('rzp_key', 'rzp_secret', 'apilayer_key', 'stripe_key')->first()->toArray());
            $api = new Api($rzp_key, $rzp_secret);
            $userCountry = \Auth::user()->country;
            $displayCurrency = getCurrencyForClient($userCountry);

            if (
                Plugin::whereStatus(1)->where('name', 'razorpay')->exists()
                && ! isCurrencySupportedForPayments($displayCurrency, 'razorpay')
            ) {
                throw new \Exception(__('message.unsupported_country'));
            }

            $exchangeRate = '';
            $orderData = [
                'receipt' => '3456',
                'amount' => getMinimumAmountForPayments($currency, 'razorpay'),
                'currency' => $displayCurrency,
                'payment_capture' => 0, // auto capture
            ];

            $razorpayOrder = ($rzp_key && $rzp_secret) ? $api->order->create($orderData) : '';

            $razorpayOrderId = ($razorpayOrder != null) ? $razorpayOrder['id'] : '';
            \Session::put('razorpay_order_id', $razorpayOrderId);
            $displayAmount = $amount = $orderData['amount'];

            $json = $this->dataToOrder($user, $rzp_key, $invoice, $userCountry, $exchangeRate, $merchant_orderid, $razorpayOrderId, $displayCurrency);
            $currency = $user->currency;
            $gateways = \App\Http\Controllers\Common\SettingsController::checkPaymentGateway($displayCurrency);
            $planid = \App\Model\Payment\Plan::where('product', $product->id)->value('id');
            $price = $order->price_override;

            $installation_path = \App\License\Models\Installation::where('license_code', $order->serial_key)
                ->where('installation_path', '!=', cloudCentralDomain())->latest('updated_at')->value('installation_path');
            $latestAgents = ltrim(substr($order->serial_key, 12), '0');
            $terminatedOrderId = \DB::table('terminated_order_upgrade')->where('upgraded_order_id', $order->id)->value('terminated_order_id');
            $terminatedOrderNumber = \App\Model\Order\Order::where('id', $terminatedOrderId)->value('number');
            if ($statusAutorenewal == 1 && $payment_log == null && ! empty($terminatedOrderId)) {
                $payment_log = $this->paymentLogGet($terminatedOrderNumber);
            }

            $plans = $this->planPriceProductRelation($product);
            $planIds = array_keys($plans);
            $countryids = \App\Model\Common\Country::where('country_code_char2', $userCountry)->first();
            $plans = $this->planDetails($planIds, $countryids, $userCountry, $plans, $product);

            $planIdOld = \App\Model\Product\Subscription::where('order_id', $id)->value('plan_id');
            $planNameReal = \App\Model\Payment\Plan::where('id', $planIdOld)->value('name');
            $autorenewal_status = Setting::where('id', 1)->value('autorenewal_status');

            $whatsappStatus = $product->whatsapp_integration;
            [$app_id, $config_id] =
                array_values(WhatsappIntegration::first()?->only(['app_id', 'config_id']) ?? [null, null]);
            $actualWhatsappStatus = StatusSetting::pluck('whatsapp_status')->first();

            return view(
                'themes.default1.front.clients.show-order',
                compact('invoice', 'order', 'user', 'product', 'subscription', 'installationDetails', 'allowDomainStatus', 'date',
                    'licdate', 'versionLabel', 'installationDetails', 'id', 'statusAutorenewal', 'status', 'payment_log', 'recentPayment', 'stripe_key', 'json', 'gateways',
                    'price', 'installation_path', 'latestAgents', 'terminatedOrderId', 'terminatedOrderNumber', 'payment_log', 'plans', 'planNameReal', 'whatsappStatus', 'app_id', 'config_id', 'autorenewal_status', 'actualWhatsappStatus',
                )
            );
        } catch (Exception $ex) {
            return redirect()->back()->with('fails', $ex->getMessage());
        }
    }

    /**
     * Get payment log for the order terminated.
     *
     * @param  $terminatedOrderNumber
     * @return array
     */
    private function paymentLogGet($terminatedOrderNumber)
    {
        $payment_log = \App\Payment_log::where('order', $terminatedOrderNumber)
            ->where('payment_type', 'Payment method updated')
            ->orderBy('id', 'desc')
            ->first();
        if (! $payment_log) {
            $payment_log = \App\Payment_log::where('order', $terminatedOrderNumber)
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
            ->whereHas('product', function ($query) {
                $query->where('type', 4)
                      ->where('can_modify_agent', 1);
            })
            ->whereHas('planPrice', function ($query) {
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

        $renewalPrices = \App\Model\Payment\PlanPrice::whereIn('plan_id', $planIds)
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
            $plans = array_filter($plans, function ($value) {
                return stripos($value, 'free') === false;
            });
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
     *  Returns to admin individual orders with payment details as datatable.
     *
     * @param  $orderid
     * @param  $userid
     * @return \Yajra\DataTables\DataTableAbstract|RedirectResponse
     *
     * @throws Exception
     */
    public function getPaymentByOrderId($orderid, $userid)
    {
        try {
            if (! authorizeOwnership($userid, true)) {
                return redirect()->back()->with('fails', __('messages.unauthorized_action'));
            }

            $order = $this->order->where('id', $orderid)->where('client', $userid)->first();
            // dd($order);
            $relation = $order->invoiceRelation()->pluck('invoice_id')->toArray();
            if (count($relation) > 0) {
                $invoices = $relation;
            } else {
                $invoices = $order->invoice()->pluck('id')->toArray();
            }
            $payments = $this->payment->whereIn('invoice_id', $invoices)
                    ->select('id', 'invoice_id', 'user_id', 'amount', 'payment_method', 'payment_status', 'created_at');

            return \DataTables::of($payments)
                            ->addColumn('checkbox', function ($model) {
                                return "<input type='checkbox' class='payment_checkbox'
                                    value=".$model->id.' name=select[] id=check>';
                            })
                            ->addColumn('number', function ($model) {
                                return $model->invoice()->first()->number;
                            })
                            ->addColumn('amount', function ($model) {
                                $currency = $model->invoice()->first()->currency;
                                $total = currencyFormat($model->amount, $code = $currency);

                                return $total;
                            })
                            ->addColumn('payment_method', function ($model) {
                                return $model->payment_method;
                            })
                             ->addColumn('payment_status', function ($model) {
                                 return $model->payment_status;
                             })
                            ->addColumn('created_at', function ($model) {
                                return getDateHtml($model->created_at);
                            })
                            ->rawColumns(['checkbox', 'number', 'amount',
                                'payment_method', 'payment_status', 'created_at', ])
                            ->make(true);
        } catch (Exception $ex) {
            return redirect()->back()->with('fails', $ex->getMessage());
        }
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

            $paginated->getCollection()->transform(function ($payment) {
                return [
                    'id' => $payment->id,
                    'invoice_number' => $payment->invoice?->number ?? '—',
                    'amount' => currencyFormat($payment->amount, $payment->invoice?->currency ?? ''),
                    'payment_method' => $payment->payment_method,
                    'payment_status' => $payment->payment_status,
                    'created_at' => $payment->created_at,
                ];
            });

            return successResponse('', $paginated);
        } catch (Exception $ex) {
            return errorResponse($ex->getMessage());
        }
    }

    public function getOrderInstallations(Request $request, $orderid)
    {
        try {
            $order = Order::where('id', $orderid)->where('client', \Auth::id())->firstOrFail();

            $query = \App\License\Models\Installation::where('license_code', $order->serial_key)
                ->where('product_id', $order->product);

            if ($search = trim($request->input('search-query', ''))) {
                $query->where(function ($q) use ($search) {
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
        } catch (\Exception $e) {
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
     *  Returns to client dashboard.
     *
     * @param
     * @return \Illuminate\Contracts\View\View
     *
     * @throws
     */
    public function index()
    {
        $user = auth()->user();
        $pendingInvoicesCount = $user->invoice()->where('status', 'pending')->count();
        $ordersCount = $user->order()->count();
        $renewalCount = $user->order()
        ->whereHas('subscription', function ($query) {
            $query->where('update_ends_at', '<', now());
        })
        ->count();

        return view('themes.default1.front.clients.index', compact('pendingInvoicesCount', 'ordersCount', 'renewalCount'));
    }

    /**
     * Delete an invoice and its related records based on specific conditions.
     *
     * @param  int  $id  The ID of the invoice to be deleted.
     * @return \Illuminate\Http\JsonResponse
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
        \Session::forget('invoice');
    }

    public function stripeUpdatePayment(Request $request)
    {
        try {
            $currency = getCurrencyForClient(\Auth::user()->country);
            $amount = currencyFormat(1, $currency);
            $orderid = $request->input('orderId');
            $stripeSecretKey = ApiKey::pluck('stripe_secret')->first();
            $stripe = new \Stripe\StripeClient($stripeSecretKey);
            $paymentIntent = $stripe->paymentIntents->retrieve($request->input('payment_intent'));
            if ($paymentIntent->status === 'succeeded') {
                $response = $this->stripePaymentUpdateSub($stripe, $paymentIntent, $orderid);

                return response()->json($response);
            } else {
                $response = ['type' => 'fails', 'message' => __('message.something_wrong')];

                return response()->json(compact('response'), 500);
            }
        } catch(\Exception $ex) {
            $result = $ex->getMessage();
            $mail = new \App\Http\Controllers\Common\PhpMailController();
            $mail->payment_log(\Auth::user()->email, 'stripe', 'failed', Order::where('id', $orderid)->value('number'), $result, $amount, 'Payment method updated');
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
            'user_id' => \Auth::user()->id,
            'customer_id' => $paymentIntent->customer,
            'payment_method' => 'stripe',
            'order_id' => $orderid,
            'payment_intent_id' => $paymentIntent->payment_method,
        ];
        Auto_renewal::create($customer_details);
        Subscription::where('order_id', $orderid)->update(['is_subscribed' => '1', 'autoRenew_status' => '1']);
        $mail = new \App\Http\Controllers\Common\PhpMailController();
        $mail->payment_log(\Auth::user()->email, 'stripe', 'success', Order::where('id', $orderid)->value('number'), null, $amount, 'Payment method updated');

        return ['type' => 'success', 'message' => __('message.card_details_updated_successfully')];
    }
}
