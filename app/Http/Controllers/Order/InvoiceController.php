<?php

namespace App\Http\Controllers\Order;

use App\Http\Controllers\Front\CartController;
use App\Http\Controllers\Tenancy\CloudExtraActivities;
use App\Http\Requests\InvoiceRequest;
use App\Jobs\ReportExport;
use App\Model\Common\FaveoCloud;
use App\Model\Common\Setting;
use App\Model\Common\Template;
use App\Model\Mailjob\QueueService;
use App\Model\Order\Invoice;
use App\Model\Order\InvoiceItem;
use App\Model\Order\Order;
use App\Model\Order\Payment;
use App\Model\Payment\Currency;
use App\Model\Payment\Plan;
use App\Model\Payment\Promotion;
use App\Model\Payment\Tax;
use App\Model\Payment\TaxByState;
use App\Model\Payment\TaxOption;
use App\Model\Product\Price;
use App\Model\Product\Product;
use App\Traits\CoupCodeAndInvoiceSearch;
use App\Traits\PaymentsAndInvoices;
use App\Traits\TaxCalculation;
use App\User;
use GuzzleHttp\Client;
use Illuminate\Http\Request;

class InvoiceController extends TaxRatesAndCodeExpiryController
{
    use  CoupCodeAndInvoiceSearch;
    use  PaymentsAndInvoices;
    use TaxCalculation;

    public $invoice;

    public $invoiceItem;

    public $user;

    public $template;

    public $setting;

    public $payment;

    public $product;

    public $price;

    public $promotion;

    public $currency;

    public $tax;

    public $tax_option;

    public $order;

    public $cartController;

    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('admin', ['except' => ['pdf']]);

        $invoice = new Invoice();
        $this->invoice = $invoice;

        $invoiceItem = new InvoiceItem();
        $this->invoiceItem = $invoiceItem;

        $user = new User();
        $this->user = $user;

        $template = new Template();
        $this->template = $template;

        $seting = new Setting();
        $this->setting = $seting;

        $payment = new Payment();
        $this->payment = $payment;

        $product = new Product();
        $this->product = $product;

        $price = new Price();
        $this->price = $price;

        $promotion = new Promotion();
        $this->promotion = $promotion;

        $currency = new Currency();
        $this->currency = $currency;

        $tax = new Tax();
        $this->tax = $tax;

        $tax_option = new TaxOption();
        $this->tax_option = $tax_option;

        $order = new Order();
        $this->order = $order;

        $tax_by_state = new TaxByState();
        $this->tax_by_state = new $tax_by_state();

        $cartController = new CartController();
        $this->cartController = $cartController;
    }

    public function index(Request $request)
    {
        $validator = \Validator::make($request->all(), [
            'from' => 'nullable',
            'till' => 'nullable|after:from',

        ]);
        if ($validator->fails()) {
            $request->from = '';
            $request->till = '';

            return redirect('invoices')->with('fails', __('message.start_date_before_end_date'));
        }
        try {
            $currencies = Currency::where('status', 1)->pluck('code')->toArray();
            $name = $request->input('name');
            $invoice_no = $request->input('invoice_no');
            $status = $request->input('status');

            $currency_id = $request->input('currency_id');
            $from = $request->input('from');
            $till = $request->input('till');

            return view('themes.default1.invoice.index', compact('request', 'name', 'invoice_no', 'status', 'currencies', 'currency_id', 'from',

                'till'));
        } catch (\Exception $ex) {
            return redirect()->back()->with('fails', $ex->getMessage());
        }
    }

    public function getInvoices(Request $request)
    {
        try {
            $searchQuery = $request->input('search-query', '');
            $sortOrder = $request->input('sort-order', 'asc');
            $sortField = $request->input('sort-field', 'created_at');
            $limit = $request->input('limit', 10);
            $page = $request->input('page', 1);

            $query = $this->advanceSearch($request);

            $invoice = $query->when($searchQuery, function ($query, $search) {
                $statusMapping = [
                    'paid' => 'success',
                    'unpaid' => 'pending',
                    'partially paid' => 'partially paid',
                    'partially' => 'partially paid',
                ];

                $status = array_key_exists($search, $statusMapping) ? $statusMapping[$search] : $search;
                $query->where(function ($q) use ($search, $status) {
                    $q->whereHas('user', function ($q2) use ($search) {
                        $q2->whereRaw('CONCAT(first_name, " ", last_name) LIKE ?', ["%{$search}%"]);
                    })
                        ->orWhere('number', 'like', "%{$search}%")
                        ->orWhere('status', 'like', "%{$status}%")
                        ->orWhere('currency', 'like', "%{$search}%");
                });
            })->orderBy($sortField, $sortOrder)->paginate($limit, ['*'], 'page', $page);

            $invoice->getCollection()->transform(function ($invoice) {
                $statusMapping = [
                    'success' => 'Paid',
                    'pending' => 'Unpaid',
                    'partially paid' => 'Partially Paid',
                ];
                $status = \Str::lower($invoice->status);

                return [
                    'id' => $invoice->id,
                    'user' => $invoice->user,
                    'number' => $invoice->number,
                    'grand_total' => currencyFormat($invoice->grand_total, $invoice->currency),
                    'status' => $statusMapping[$status],
                ];
            });

            return successResponse('', $invoice);
        } catch (\Exception $ex) {
            return errorResponse($ex->getMessage());
        }
    }

    /**
     * Shoe Invoice when view Invoice is selected from dropdown in Admin Panel.
     *
     * @param  Request  $request  Get InvoiceId as Request
     */
    public function show(Request $request)
    {
        try {
            $invoice = Invoice::leftJoin('order_invoice_relations', 'invoices.id', '=', 'order_invoice_relations.invoice_id')

            ->select('invoices.id', 'invoices.user_id', 'invoices.created_at', 'invoices.date', 'invoices.currency', 'invoices.number', 'invoices.discount', 'invoices.grand_total', 'invoices.processing_fee', 'order_invoice_relations.order_id')
            ->where('invoices.id', '=', $request->input('invoiceid'))
            ->first();
            if (User::onlyTrashed()->find($invoice->user_id)) {
                throw new \Exception(__('message.user_suspended'));
            }
            $invoiceItems = $invoice->invoiceItem()->get();
            $user = $this->user->find($invoice->user_id);
            $order = Order::getOrderLink($invoice->order_id, 'orders');

            return view('themes.default1.invoice.show', compact('invoiceItems', 'invoice', 'user', 'order'));
        } catch (\Exception $ex) {
            app('log')->warning($ex->getMessage());

            return redirect()->back()->with('fails', $ex->getMessage());
        }
    }

    /**
     * not in use case.
     *
     * @param  Request  $request
     * @return type
     */
    public function generateById(Request $request)
    {
        try {
            $clientid = $request->input('clientid');
            $user = new User();
            if ($clientid) {
                $user = $user->where('id', $clientid)->first();
                if (! $user) {
                    return redirect()->back()->with('fails', __('message.invalid_user'));
                }
            } else {
                $user = '';
            }
            $products = $this->product->where('invoice_hidden', 0)->pluck('name', 'id')->toArray();
            $currency = $this->currency->pluck('name', 'code')->toArray();

            return view('themes.default1.invoice.generate', compact('user', 'products', 'currency'));
        } catch (\Exception $ex) {
            app('log')->info($ex->getMessage());

            return redirect()->back()->with('fails', $ex->getMessage());
        }
    }

    /**
     * Generate invoice from client panel.
     *
     * @throws \Exception
     */
    public function generateInvoice()
    {
        try {
            $amt_to_credit = null;
            $tax_rule = new \App\Model\Payment\TaxOption();
            $rule = $tax_rule->findOrFail(1);
            $rounding = $rule->rounding;
            $user_id = \Auth::user()->id;
            $grand_total = \Cart::getTotal();
            $number = rand(11111111, 99999999);
            $date = \Carbon\Carbon::now();
            if ($rounding) {
                $grand_total = round($grand_total);
            }
            if (User::where('id', $user_id)->value('billing_pay_balance')) {
                $amt_to_credit = \DB::table('payments')
                    ->where('user_id', \Auth::user()->id)
                    ->where('payment_method', 'Credit Balance')
                    ->where('payment_status', 'success')
                    ->where('amt_to_credit', '!=', 0)
                    ->value('amt_to_credit');

                if ($grand_total <= (int) $amt_to_credit) {
                    $amt_to_credit = $grand_total;
                }
            }

            $currency = \Session::has('cart_currency') ? \Session::get('cart_currency') : getCurrencyForClient(\Auth::user()->country);
            $cloud_domain = \Session::has('cloud_domain') ? \Session::get('cloud_domain') : '';
            $cont = new \App\Http\Controllers\Payment\PromotionController();
            $invoice = $this->invoice->create(['user_id' => $user_id, 'number' => $number, 'date' => $date, 'grand_total' => $grand_total, 'status' => 'pending',
                'currency' => $currency, 'coupon_code' => \Session::get('code'), 'discount' => \Session::get('discountPrice'), 'discount_mode' => 'coupon', 'billing_pay' => $amt_to_credit, 'cloud_domain' => str_replace('.'.cloudSubDomain(), '', $cloud_domain), 'credits' => \Session::get('priceRemaining')]);

            foreach (\Cart::getContent() as $cart) {
                $this->createInvoiceItems($invoice->id, $cart, $amt_to_credit);
            }

            if (emailSendingStatus()) {
                $this->sendMail($user_id, $invoice->id);
            }

            return $invoice;
        } catch (\Exception $ex) {
            app('log')->error($ex->getMessage());

            return redirect()->back()->with('fails', $ex->getMessage());
        }
    }

    public function createInvoiceItems($invoiceid, $cart, $amt_credit = null)
    {
        try {
            $planid = 0;
            $product_name = $cart->name;
            $product_id = Product::where('name', $product_name)->value('id');
            $regular_price = (\Session::has('priceToBePaid')) ? \Session::get('priceToBePaid') : $cart->price;
            $quantity = $cart->quantity;
            $agents = $cart->attributes->agents;
            $domain = $this->domain($cart->id);
            if (checkPlanSession()) {
                $planid = \Session::get('plan');
            }
            if ($planid == 0) {
                //When Product is added from Faveo Website
                $planid = Plan::where('id', $cart->id)->pluck('id')->first();
            }
            $subtotal = $cart->getPriceSum();
            $tax_name = $cart->conditions->getName();
            $tax_percentage = $cart->conditions->getValue();
            $invoiceItem = $this->invoiceItem->create([
                'invoice_id' => $invoiceid,
                'product_name' => $product_name,
                'product_id' => $product_id,
                'regular_price' => $regular_price,
                'quantity' => $quantity,
                'tax_name' => $tax_name,
                'tax_percentage' => $tax_percentage,
                'subtotal' => $subtotal,
                'domain' => $domain,
                'plan_id' => $planid,
                'agents' => $agents,
                'billing_pay' => $amt_credit,
            ]);

            return $invoiceItem;
        } catch (\Exception $ex) {
            throw new \Exception(__('message.cannot_create_invoice_item'));
        }
    }

    /**
     * Generate invoice from admin panel.
     *
     * @throws \Exception
     */
    public function invoiceGenerateByForm(InvoiceRequest $request, $user_id = '')
    {
        try {
            $cloud_domain = '';
            $agents = $request->input('agents');
            $status = 'pending';
            $qty = $request->input('quantity');
            if ($user_id == '') {
                $user_id = $request->input('user');
            }
            if ($request->has('cloud_domain')) {
                $cloud_domain = $request->input('cloud_domain');

                if (empty($cloud_domain)) {
                    return errorResponse([trans('message.cloud_domain_empty')]);
                }

                $cloud_domain = $cloud_domain.'.'.cloudSubDomain();

                if (! (new CloudExtraActivities(new Client, new FaveoCloud()))->checkDomain($cloud_domain)) {
                    return errorResponse([trans('message.domain_taken')]);
                }
            }

            $productid = $request->input('product');

            $plan = $request->input('plan');
            $agents = $this->getAgents($agents, $productid, $plan);
            $qty = $this->getQuantity($qty, $productid, $plan);

            $code = $request->input('code');
            $total = str_replace(',', '', $request->input('price'));
            $description = $request->input('description');
            if ($request->has('domain')) {
                $domain = $request->input('domain');
                $this->setDomain($productid, $domain);
            }
            $planObj = Plan::where('id', $plan)->first();
            $userCurrency = userCurrencyAndPrice($user_id, $planObj);
            $currency = $userCurrency['currency'];
            $number = rand(11111111, 99999999);
            $date = \Carbon\Carbon::parse($request->input('date'));
            $product = Product::find($productid);

            $cost = $this->cartController->cost($productid, $plan, $user_id);

            $couponTotal = $this->getGrandTotal($code, $total, $cost, $productid, $currency, $user_id);
            $grandTotalAfterCoupon = $qty * $couponTotal['total'];
            if (! $grandTotalAfterCoupon) {
                $status = 'success';
            }
            $user = User::where('id', $user_id)->select('state', 'country')->first();
            $tax = $this->calculateTax($product->id, $user->state, $user->country, true);
            $grand_total = rounding($this->calculateTotal($tax['value'], $grandTotalAfterCoupon));
            $coupon = rounding($grand_total * (intval($couponTotal['value']) / 100));
            $invoice = Invoice::create(['user_id' => $user_id, 'number' => $number, 'date' => $date,
                'coupon_code' => $couponTotal['code'], 'discount' => $coupon, 'discount_mode' => $couponTotal['mode'], 'grand_total' => $grand_total,  'currency' => $currency, 'status' => $status, 'description' => $description, 'cloud_domain' => str_replace('.'.cloudSubDomain(), '', $cloud_domain)]);

            $items = $this->createInvoiceItemsByAdmin($invoice->id, $productid,
                $total, $currency, $qty, $agents, $plan, $user_id, $tax['name'], $tax['value'], $grandTotalAfterCoupon);
            $result = $this->getMessage($items, $user_id);

            return successResponse($result);
        } catch (\Exception $ex) {
            app('log')->info($ex->getMessage());

            return errorResponse([$ex->getMessage()]);
        }
    }

    public function createInvoiceItemsByAdmin($invoiceid, $productid, $price,
        $currency, $qty, $agents, $planid, $userid, $tax_name, $tax_rate, $grandTotalAfterCoupon)
    {
        try {
            $product = $this->product->findOrFail($productid);
            $plan = Plan::where('product', $productid)->first();
            $subtotal = $qty * intval($grandTotalAfterCoupon);

            $domain = $this->domain($productid);
            $items = $this->invoiceItem->create([
                'invoice_id' => $invoiceid,
                'product_name' => $product->name,
                'product_id' => $productid,
                'regular_price' => $price,
                'quantity' => $qty,
                'subtotal' => rounding($subtotal),
                'tax_name' => $tax_name,
                'tax_percentage' => $tax_rate,
                'domain' => $domain,
                'plan_id' => $planid,
                'agents' => $agents,
            ]);

            return $items;
        } catch (\Exception $ex) {
            return redirect()->back()->with('fails', $ex->getMessage());
        }
    }

    public function setDomain($productid, $domain)
    {
        try {
            if (\Session::has('domain'.$productid)) {
                \Session::forget('domain'.$productid);
            }
            \Session::put('domain'.$productid, $domain);
        } catch (\Exception $ex) {
            throw new \Exception($ex->getMessage());
        }
    }

    public function sendMail($userid, $invoiceid)
    {
        try {
            $invoice = $this->invoice->find($invoiceid);
            $number = $invoice->number;
            $total = $invoice->grand_total;

            return $this->sendInvoiceMail($userid, $number, $total, $invoiceid);
        } catch (\Exception $ex) {
            throw new \Exception($ex->getMessage());
        }
    }

    public function pdf(Request $request)
    {
        try {
            $id = $request->input('invoiceid');
            if (! $id) {
                return redirect()->back()->with('fails', \Lang::get('message.no-invoice-id'));
            }
            $invoice = $this->invoice->where('id', $id)->first();

            $user = $this->user->find($invoice->user_id);

            if (! $user || ($user->id != \Auth::user()->id && \Auth::user()->role != 'admin')) {
                return redirect()->back()->with('fails', __('message.invalid_user'));
            }

            if (! $invoice) {
                return redirect()->back()->with('fails', \Lang::get('message.invalid-invoice-id'));
            }
            $invoiceItems = $this->invoiceItem->where('invoice_id', $id)->get();
            if ($invoiceItems->count() == 0) {
                return redirect()->back()->with('fails', \Lang::get('message.invalid-invoice-id'));
            }

            $order = $this->order->getOrderLink($invoice->orderRelation()->value('order_id'), 'my-order');
            // $order = Order::getOrderLink($invoice->order_id);
            $currency = $invoice->currency;
            $gst = TaxOption::select('tax_enable', 'Gst_No')->first();
            $symbol = $invoice->currency;
            // ini_set('max_execution_time', '0');
            $pdf = \PDF::loadView('themes.default1.invoice.newpdf', compact('invoiceItems', 'invoice', 'user', 'currency', 'symbol', 'gst', 'order'));

            return $pdf->download($user->first_name.'-invoice.pdf');
        } catch (\Exception $ex) {
            return redirect()->back()->with('fails', $ex->getMessage());
        }
    }

    public function exportInvoices(Request $request)
    {
        try {
            ini_set('memory_limit', '-1');
            $selectedColumns = $request->input('selected_columns', []);
            $searchParams = $request->input('search_params', []);
            $email = \Auth::user()->email;
            $driver = QueueService::where('status', '1')->first();
            if ($driver->name != 'Sync') {
                app('queue')->setDefaultDriver($driver->short_name);
                ReportExport::dispatch('invoices', $selectedColumns, $searchParams, $email)->onQueue('reports');

                return response()->json(['message' => __('message.report_generation_in_progress')], 200);
            } else {
                return response()->json(['message' => __('message.cannot_sync_queue_driver')], 400);
            }
        } catch (\Exception $e) {
            \Log::error('Export failed: '.$e->getMessage());

            return response()->json(['message' => $e->getMessage()], 500);
        }
    }

    public function getInvoice($id)
    {
        try {
            $query = Invoice::with([
                'user:id,first_name,last_name,email,company,address,town,state,country,zip,mobile_code,mobile',
                'invoiceItem.order:id,number,invoice_item_id',
            ])->findOrFail($id);

            if (User::onlyTrashed()->find($query->user->id)) {
                throw new \Exception(__('message.user_suspended'));
            }

            // Company settings
            $setting = Setting::select(
                'id', 'company', 'address', 'state', 'zip', 'city', 'country',
                'phone_code', 'phone', 'logo', 'company_email'
            )->first();

            $setting->state = key_exists('name', getStateByCode($setting->state))
                ? getStateByCode($setting->state)['name']
                : $setting->state;

            $query->user->state = key_exists('name', getStateByCode($query->user->state))
                ? getStateByCode($query->user->state)['name']
                : $query->user->state;

            $result = $this->calculateInvoice($id, true);

            $invoice = [
                'invoice' => [
                    'number' => $query->number,
                    'date' => $query->date,
                ],
                'from' => $setting,
                'to' => $query->user,
                'items' => $query->invoiceItem,
                'totals' => $result,
            ];

            return successResponse('', $invoice);
        } catch (\Exception $ex) {
            return errorResponse($ex->getMessage());
        }
    }

    /**
     * Get dynamic invoice totals for a given invoice ID.
     *
     * @param  int  $invoiceId
     * @param  bool  $formatCurrency  - whether to format currency strings or return numeric
     * @return array
     */
    public static function calculateInvoice($invoiceId, $formatCurrency = false)
    {
        $invoice = Invoice::with(['invoiceItem', 'user'])->findOrFail($invoiceId);

        $taxState = $invoice->user->state;
        $itemSubtotal = 0;
        $taxComponents = [];

        // Process each item
        foreach ($invoice->invoiceItem as $item) {
            $itemSubtotal += $item->subtotal;

            if ($item->tax_name) {
                $itemTaxes = bifurcate(
                    $item->tax_name,
                    $item->tax_percentage,
                    $invoice->currency,
                    $taxState,
                    $item->subtotal
                );

                foreach ($itemTaxes as $component) {
                    $taxComponents[$component['name']] = ($taxComponents[$component['name']] ?? 0) + $component['value'];
                }
            }
        }

        // Format taxes if required
        $taxes = [];
        foreach ($taxComponents as $name => $value) {
            $taxes[$name] = $formatCurrency
                ? currencyFormat($value, $invoice->currency)
                : round($value, 2);
        }

        // Processing fee
        $processingFee = $invoice->processing_fee ? floatval(filter_var($invoice->processing_fee, FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION)) : 0;
        if ($formatCurrency) {
            $processingFee = currencyFormat($processingFee, $invoice->currency);
        }

        // Subtotal
        $subtotal = $formatCurrency ? currencyFormat($itemSubtotal, $invoice->currency) : round($itemSubtotal, 2);

        // Credits and discounts
        $credits = $invoice->credits ?? 0;
        $discount = $invoice->discount ?? 0;
        if ($formatCurrency) {
            $credits = $credits ? currencyFormat($credits, $invoice->currency) : null;
            $discount = $discount ? currencyFormat($discount, $invoice->currency) : null;
        } else {
            $credits = round($credits, 2);
            $discount = round($discount, 2);
        }

        // Grand total (numeric)
        $grandTotal = $formatCurrency ? currencyFormat($invoice->grand_total, $invoice->currency) : round($invoice->grand_total, 2);

        return [
            'subtotal' => $subtotal,
            'tax' => $taxes,
            'processing_fee' => $processingFee,
            'credits' => $credits,
            'discount' => $discount,
            'total' => $grandTotal,
        ];
    }
}
