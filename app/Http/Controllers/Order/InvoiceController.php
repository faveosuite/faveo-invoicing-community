<?php

namespace App\Http\Controllers\Order;

use App\Http\Controllers\Tenancy\CloudExtraActivities;
use App\Http\Requests\InvoiceRequest;
use App\Jobs\ReportExport;
use App\Model\Common\FaveoCloud;
use App\Model\Common\Setting;
use App\Model\Common\Template;
use App\Model\Mailjob\QueueService;
use App\Model\Order\Invoice;
use App\Model\Order\InvoiceItem;
use App\Model\Order\InvoiceTaxLine;
use App\Model\Order\Order;
use App\Model\Order\OrderInvoiceRelation;
use App\Model\Order\Payment;
use App\Model\Payment\Currency;
use App\Model\Payment\Plan;
use App\Model\Payment\Promotion;
use App\Model\Payment\Tax;
use App\Model\Payment\TaxByState;
use App\Model\Payment\TaxOption;
use App\Model\Product\Price;
use App\Model\Product\Product;
use App\Services\Payment\ProcessingFee;
use App\Traits\CoupCodeAndInvoiceSearch;
use App\Traits\PaymentsAndInvoices;
use App\Traits\TaxCalculation;
use App\User;
use Auth;
use Exception;
use GuzzleHttp\Client;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Date;
use Logger;
use Session;
use Spatie\LaravelPdf\Enums\Format;
use Spatie\LaravelPdf\Facades\Pdf;
use Str;

class InvoiceController extends TaxRatesAndCodeExpiryController
{
    use  CoupCodeAndInvoiceSearch;
    use  PaymentsAndInvoices;
    use TaxCalculation;

    /**
     * @var \App\Model\Order\Invoice
     */
    public $invoice;

    /**
     * @var \App\Model\Order\InvoiceItem
     */
    public $invoiceItem;

    /**
     * @var \App\User
     */
    public $user;

    /**
     * @var \App\Model\Common\Template
     */
    public $template;

    /**
     * @var \App\Model\Common\Setting
     */
    public $setting;

    /**
     * @var \App\Model\Order\Payment
     */
    public $payment;

    /**
     * @var \App\Model\Product\Product
     */
    public $product;

    /**
     * @var \App\Model\Product\Price
     */
    public $price;

    /**
     * @var \App\Model\Payment\Promotion
     */
    public $promotion;

    /**
     * @var \App\Model\Payment\Currency
     */
    public $currency;

    /**
     * @var \App\Model\Payment\Tax
     */
    public $tax;

    /**
     * @var \App\Model\Payment\TaxOption
     */
    public $tax_option;

    /**
     * @var \App\Model\Order\Order
     */
    public $order;

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
        $this->tax_by_state = new $tax_by_state(); // @phpstan-ignore property.notFound
    }

    public function getInvoices(Request $request): \Illuminate\Http\JsonResponse
    {
        try {
            $searchQuery = $request->input('search-query', '');
            $sortOrder = $request->input('sort-order', 'desc');
            $sortField = $request->input('sort-field', 'created_at');
            $limit = $request->input('limit', 10);

            $allowedSorts = ['created_at', 'number', 'grand_total', 'status'];
            if (! in_array($sortField, $allowedSorts, strict: true)) {
                $sortField = 'created_at';
            }

            $query = $this->advanceSearch($request);

            $invoice = $query->when($searchQuery, function ($query, $search): void {
                $statusMapping = [
                    'paid' => 'success',
                    'unpaid' => 'pending',
                    'partially paid' => 'partially paid',
                    'partially' => 'partially paid',
                ];

                $status = array_key_exists($search, $statusMapping) ? $statusMapping[$search] : $search;
                $query->where(function ($q) use ($search, $status): void {
                    $q->whereHas('user', function ($q2) use ($search): void {
                        $q2->whereRaw('CONCAT(first_name, " ", last_name) LIKE ?', [sprintf('%%%s%%', $search)]);
                    })
                        ->orWhere('number', 'like', sprintf('%%%s%%', $search))
                        ->orWhere('status', 'like', sprintf('%%%s%%', $status))
                        ->orWhere('currency', 'like', sprintf('%%%s%%', $search));
                });
            })->orderBy($sortField, $sortOrder)->simplePaginate($limit);

            $invoice->getCollection()->transform(function ($invoice): array { // @phpstan-ignore argument.unresolvableType
                $statusMapping = [
                    'success' => 'Paid',
                    'pending' => 'Unpaid',
                    'partially paid' => 'Partially Paid',
                ];
                $status = Str::lower($invoice->status);

                $products = $invoice->invoiceItem ? $invoice->invoiceItem->pluck('item_name')->toArray() : [];

                return [
                    'id' => $invoice->id,
                    'user' => $invoice->user,
                    'number' => $invoice->number,
                    'products' => $products,
                    'created_at' => $invoice->created_at,
                    'grand_total' => currencyFormat($invoice->grand_total, $invoice->currency),
                    'status' => $statusMapping[$status] ?? $invoice->status,
                ];
            });

            return successResponse('', $invoice);
        } catch (Exception $exception) {
            return errorResponse($exception->getMessage());
        }
    }

    /**
     * Generate invoice from admin panel.
     *
     * @throws Exception
     */
    public function invoiceGenerateByForm(InvoiceRequest $request, int|string $user_id = ''): \Illuminate\Http\JsonResponse
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

                if (! (bool) new CloudExtraActivities(new Client, new FaveoCloud())->checkDomain($cloud_domain)) { // @phpstan-ignore-line
                    return errorResponse([trans('message.domain_taken')]);
                }
            }

            $productid = $request->input('product');

            $plan = $request->input('plan');
            $agents = $this->getAgents($agents, $productid, $plan);
            $qty = $this->getQuantity($qty, $productid, $plan);

            $code = $request->input('code');
            $total = (float) str_replace(',', '', $request->input('price'));
            $description = $request->input('description');
            if ($request->has('domain')) {
                $domain = $request->input('domain');
                $this->setDomain($productid, $domain);
            }

            $planObj = Plan::where('id', $plan)->first();
            $userCurrency = userCurrencyAndPrice($user_id, $planObj);
            $currency = $userCurrency['currency'];
            $number = random_int(11111111, 99999999);
            $date = Date::parse($request->input('date'));
            $product = Product::find($productid);

            $baseCost = $userCurrency['plan']->add_price;
            $offer = $userCurrency['plan']['offer_price'] ?? 0;
            $cost = $offer > 0 ? $baseCost * (1 - $offer / 100) : $baseCost;
            Session::put('plan', $plan);
            $couponTotal = $this->getGrandTotal($code, $total, $cost, $productid, $currency, $user_id);
            $grandTotalAfterCoupon = $qty * $couponTotal['total'];
            if (! $grandTotalAfterCoupon) {
                $status = 'success';
            }

            $user = User::where('id', $user_id)->select('state', 'country')->first();
            $tax = $this->calculateTax($product->id, $user->state, $user->country, taxCaluculationFromAdminPanel: true);
            $grand_total = rounding($this->calculateTotal($tax['value'], $grandTotalAfterCoupon));
            $subtotal = $qty * $total;
            $coupon = $subtotal * (intval($couponTotal['value']) / 100);
            $invoice = Invoice::create(['user_id' => $user_id, 'number' => $number, 'date' => $date,
                'coupon_code' => $couponTotal['code'], 'discount' => $coupon, 'discount_mode' => $couponTotal['mode'], 'grand_total' => $grand_total,  'currency' => $currency, 'status' => $status, 'description' => $description, 'cloud_domain' => str_replace('.'.cloudSubDomain(), '', $cloud_domain)]);

            $items = $this->createInvoiceItemsByAdmin($invoice->id, $productid,
                $total, $currency, $qty, $agents, $plan, $user_id, $tax['name'], $tax['value'], $total); // @phpstan-ignore argument.type
            $result = $this->getMessage($items, $user_id);
            Session::forget('plan');

            return successResponse($result); // @phpstan-ignore argument.type
        } catch (Exception $exception) {
            Logger::exception($exception);

            return errorResponse([$exception->getMessage()]);
        }
    }

    public function createInvoiceItemsByAdmin(int $invoiceid, string $productid, mixed $price,
                                              string $currency, int $qty, mixed $agents, int $planid, int $userid, ?string $tax_name, float|int $tax_rate, mixed $grandTotalAfterCoupon): \App\Model\Order\InvoiceItem|\Illuminate\Http\RedirectResponse
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

            // Persist the tax breakdown so admin- and renewal-created invoices
            // expose tax via invoice_tax_lines like cart invoices do.
            $percent = $this->sumPercent($tax_rate); // @phpstan-ignore argument.type
            if ($tax_name && strtolower((string) $tax_name) !== 'null' && $percent > 0) {
                InvoiceTaxLine::create([
                    'invoice_id' => $invoiceid,
                    'invoice_item_id' => $items->id,
                    'tax_rate_id' => null,
                    'label' => $tax_name,
                    'rate' => $percent,
                    'compound' => 0,
                    'amount' => round((float) $items->subtotal * $percent / 100, 4),
                ]);
            }

            return $items;
        } catch (Exception $exception) {
            return back()->with('fails', $exception->getMessage());
        }
    }

    public function setDomain(string $productid, string $domain): void
    {
        try {
            if (Session::has('domain'.$productid)) {
                Session::forget('domain'.$productid);
            }

            Session::put('domain'.$productid, $domain);
        } catch (Exception $exception) {
            throw new Exception($exception->getMessage(), $exception->getCode(), $exception);
        }
    }

    public function sendMail(int $userid, int $invoiceid): mixed
    {
        try {
            $invoice = $this->invoice->findOrFail($invoiceid);
            $number = $invoice->number;
            $total = $invoice->grand_total;

            return $this->sendInvoiceMail($userid, $number, $total, $invoiceid); // @phpstan-ignore argument.type, method.void
        } catch (Exception $exception) {
            throw new Exception($exception->getMessage(), $exception->getCode(), $exception);
        }
    }

    public function pdf(Request $request): \Illuminate\Http\JsonResponse|\Spatie\LaravelPdf\PdfBuilder
    {
        try {
            $id = $request->input('invoiceid');
            if (! $id) {
                return errorResponse(__('message.no-invoice-id'));
            }

            $invoice = $this->invoice->find($id);

            if (! $invoice) {
                return errorResponse(__('message.invalid-invoice-id'));
            }

            $authUser = Auth::user();
            if ($invoice->user_id != $authUser->id && $authUser->role != 'admin') {
                return errorResponse(__('message.invalid_user'));
            }

            $totals = self::calculateInvoice($id, formatCurrency: true);

            $set = Setting::select(
                'id', 'company', 'address', 'state', 'zip', 'city', 'country',
                'phone_code', 'phone', 'logo', 'company_email', 'gstin', 'cin_no'
            )->first();

            $invoiceUser = $invoice->user;
            if ($invoiceUser) {
                $invoiceUser->state = array_key_exists('name', getStateByCode($invoiceUser->country, $invoiceUser->state))
                    ? getStateByCode($invoiceUser->country, $invoiceUser->state)['name']
                    : $invoiceUser->state;
            }

            return Pdf::view('themes.default1.invoice.newpdf', [
                'invoice' => $invoice,
                'invoiceItems' => $invoice->invoiceItem()->get(),
                'user' => $invoiceUser,
                'set' => $set,
                'order' => Order::getOrderLink(OrderInvoiceRelation::where('invoice_id', $id)->value('order_id'), 'my-order'),
                'date' => getDateHtml($invoice->date),
                'symbol' => $invoice->currency,
                'totals' => $totals,
            ])
                ->format(Format::A4)
                ->margins(10, 10, 10, 10)
                ->download($authUser->first_name.'-invoice.pdf');
        } catch (Exception $exception) {
            return errorResponse($exception->getMessage());
        }
    }

    public function exportInvoices(Request $request): \Illuminate\Http\JsonResponse
    {
        try {
            ini_set('memory_limit', '-1');
            $selectedColumns = $request->input('selected_columns', []);
            $searchParams = $request->input('search_params', []);
            $email = Auth::user()->email;
            $driver = QueueService::where('status', '1')->first();

            if ($driver->name == 'Sync') {
                return errorResponse(__('message.cannot_sync_queue_driver'));
            }

            resolve('queue')->setDefaultDriver($driver->short_name);
            dispatch(new ReportExport('invoices', $selectedColumns, $searchParams, $email))->onQueue('reports');

            return successResponse(__('message.report_generation_in_progress'));
        } catch (Exception $exception) {
            Logger::exception($exception);

            return errorResponse($exception->getMessage());
        }
    }

    public function getInvoice(int $id): \Illuminate\Http\JsonResponse
    {
        try {
            $query = Invoice::with([
                'user:id,first_name,last_name,email,company,address,town,state,country,zip,mobile_code,mobile,gstin',
                'invoiceItem.order:id,number,invoice_item_id',
                'payment',
            ])->findOrFail($id);

            if (! $query->user || User::onlyTrashed()->find($query->user->id)) {
                throw new Exception(__('message.user_suspended'));
            }

            // Company settings
            $setting = Setting::select(
                'id', 'company', 'address', 'state', 'zip', 'city', 'country',
                'phone_code', 'phone', 'logo', 'company_email'
            )->first();

            $setting->state = array_key_exists('name', getStateByCode($setting->country, $setting->state))
                ? getStateByCode($setting->country, $setting->state)['name']
                : $setting->state;

            $query->user->state = array_key_exists('name', getStateByCode($query->user->country, $query->user->state))
                ? getStateByCode($query->user->country, $query->user->state)['name']
                : $query->user->state;

            $result = static::calculateInvoice($id, formatCurrency: true);

            $invoice = [
                'invoice' => [
                    'id' => $query->id,
                    'number' => $query->number,
                    'date' => $query->date,
                    'status' => $query->status,
                    'grand_total' => $query->grand_total,
                    'currency' => $query->currency,
                    'coupon_code' => $query->coupon_code,
                    'processing_fee_label' => $query->processing_fee,
                ],
                'from' => $setting,
                'to' => $query->user,
                'items' => $query->invoiceItem,
                'totals' => $result,
                'payments' => $query->payment,
            ];

            return successResponse('', $invoice);
        } catch (Exception $exception) {
            return errorResponse($exception->getMessage());
        }
    }

    /**
     * Get dynamic invoice totals for a given invoice ID.
     *
     * @param  int  $invoiceId
     * @param  bool  $formatCurrency  - whether to format currency strings or return numeric
     */
    public static function calculateInvoice(int $invoiceId, bool $formatCurrency = false): array
    {
        $invoice = Invoice::with(['invoiceItem', 'user'])->findOrFail($invoiceId);

        $itemSubtotal = 0;
        foreach ($invoice->invoiceItem as $item) {
            $itemSubtotal += $item->subtotal;
        }

        // Tax breakdown from the persisted invoice_tax_lines, grouped per tax.
        $taxes = [];
        $taxTotal = 0.0;
        foreach (InvoiceTaxLine::where('invoice_id', $invoice->id)->get()->groupBy('label') as $name => $lines) {
            $value = (float) $lines->sum('amount');
            $taxTotal += $value;
            $taxes[$name] = $formatCurrency
                ? currencyFormat($value, $invoice->currency)
                : round($value, 2);
        }

        // Processing fee: grand_total is stored fee-inclusive, so the fee amount
        // is the part of grand_total above the pre-fee total (NOT the % itself).
        $feeAmount = ProcessingFee::fromInclusive((float) $invoice->grand_total, $invoice->processing_fee);
        $processingFee = $formatCurrency ? currencyFormat($feeAmount, $invoice->currency) : round($feeAmount, 2);

        // Subtotal shown ex-tax: for tax-inclusive pricing the item subtotal is
        // gross, so strip the tax out so subtotal + tax + fee reconciles to total.
        $pricesIncludeTax = (int) TaxOption::find(1)?->inclusive === 1;
        $netSubtotal = $pricesIncludeTax ? ($itemSubtotal - $taxTotal) : $itemSubtotal;
        $subtotal = $formatCurrency ? currencyFormat($netSubtotal, $invoice->currency) : round($netSubtotal, 2);

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
        $grandTotal = $formatCurrency ? currencyFormat($invoice->grand_total, $invoice->currency) : round((float) $invoice->grand_total, 2);

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
