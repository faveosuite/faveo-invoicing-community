<?php

namespace App\Http\Controllers\Order;

use App\Http\Controllers\Tenancy\CloudExtraActivities;
use App\Http\Controllers\Tenancy\TenantController;
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
use App\Model\Product\CloudProducts;
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
use Illuminate\Contracts\Database\Query\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Date;
use Logger;
use Session;
use Spatie\LaravelPdf\Enums\Format;
use Spatie\LaravelPdf\Facades\Pdf;
use Spatie\LaravelPdf\PdfBuilder;
use Str;

class InvoiceController extends TaxRatesAndCodeExpiryController
{
    use CoupCodeAndInvoiceSearch;
    use PaymentsAndInvoices;
    use TaxCalculation;

    /**
     * @var Invoice
     */
    public $invoice;

    /**
     * @var InvoiceItem
     */
    public $invoiceItem;

    /**
     * @var User
     */
    public $user;

    /**
     * @var Template
     */
    public $template;

    /**
     * @var Setting
     */
    public $setting;

    /**
     * @var Payment
     */
    public $payment;

    /**
     * @var Product
     */
    public $product;

    /**
     * @var Price
     */
    public $price;

    /**
     * @var Promotion
     */
    public $promotion;

    /**
     * @var Currency
     */
    public $currency;

    /**
     * @var Tax
     */
    public $tax;

    /**
     * @var TaxOption
     */
    public $tax_option;

    /**
     * @var Order
     */
    public $order;

    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('admin', ['except' => ['pdf']]);

        $invoice = new Invoice;
        $this->invoice = $invoice;

        $invoiceItem = new InvoiceItem;
        $this->invoiceItem = $invoiceItem;

        $user = new User;
        $this->user = $user;

        $template = new Template;
        $this->template = $template;

        $seting = new Setting;
        $this->setting = $seting;

        $payment = new Payment;
        $this->payment = $payment;

        $product = new Product;
        $this->product = $product;

        $price = new Price;
        $this->price = $price;

        $promotion = new Promotion;
        $this->promotion = $promotion;

        $currency = new Currency;
        $this->currency = $currency;

        $tax = new Tax;
        $this->tax = $tax;

        $tax_option = new TaxOption;
        $this->tax_option = $tax_option;

        $order = new Order;
        $this->order = $order;

        $tax_by_state = new TaxByState;
        $this->tax_by_state = new $tax_by_state; // @phpstan-ignore property.notFound
    }

    public function getInvoices(Request $request): JsonResponse
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

            $invoice = $query->withCount('orderRelation')->when($searchQuery, function ($query, $search): void {
                $statusMapping = [
                    'paid' => 'success',
                    'unpaid' => 'pending',
                    'partially paid' => 'partially paid',
                    'partially' => 'partially paid',
                ];

                $status = array_key_exists($search, $statusMapping) ? $statusMapping[$search] : $search;
                $query->where(function (Builder $q) use ($search, $status): void {
                    $q->whereHas('user', function (Builder $q2) use ($search): void {
                        $q2->whereRaw('CONCAT(first_name, " ", last_name) LIKE ?', [sprintf('%%%s%%', $search)]);
                    })
                        ->orWhere('number', 'like', sprintf('%%%s%%', $search))
                        ->orWhere('status', 'like', sprintf('%%%s%%', $status))
                        ->orWhere('currency', 'like', sprintf('%%%s%%', $search));
                });
            })->orderBy($sortField, $sortOrder)->simplePaginate($limit);

            $invoice->getCollection()->transform(function ($invoice): array { // @phpstan-ignore method.unresolvableReturnType, argument.unresolvableType
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
                    'is_executed' => $invoice->order_relation_count > 0,
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
    public function invoiceGenerateByForm(InvoiceRequest $request, int|string $user_id = ''): JsonResponse
    {
        try {
            $cloud_domain = '';
            $agents = $request->input('agents');
            $status = 'pending';
            $qty = $request->input('quantity');
            if ($user_id == '') {
                $user_id = $request->input('user');
            }

            if ($request->filled('cloud_domain')) {
                $cloud_domain = $request->input('cloud_domain');

                $cloud_domain = $cloud_domain.'.'.cloudSubDomain();

                if (! (bool) new CloudExtraActivities(new Client, new FaveoCloud)->checkDomain($cloud_domain)) { // @phpstan-ignore-line
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
            if ($request->filled('domain')) {
                $domain = $request->input('domain');
                $this->setDomain($productid, $domain);
            }

            $planObj = Plan::where('id', $plan)->first();
            $userCurrency = userCurrencyAndPrice($user_id, $planObj);
            $currency = $userCurrency['currency'];
            $number = random_int(11111111, 99999999);
            $date = Date::parse($request->input('date'));
            $product = Product::find($productid);
            if (! $product instanceof Product) {
                throw new Exception('Product not found.');
            }

            $baseCost = $userCurrency['plan']?->add_price;
            $offer = $userCurrency['plan'] ? ($userCurrency['plan']['offer_price'] ?? 0) : 0;
            $cost = $offer > 0 ? $baseCost * (1 - $offer / 100) : $baseCost;
            Session::put('plan', $plan);
            $couponTotal = $this->getGrandTotal($code, $total, $cost, $productid, $currency, $user_id);
            $grandTotalAfterCoupon = $qty * $couponTotal['total'];
            if (! $grandTotalAfterCoupon) {
                $status = 'success';
            }

            $user = User::where('id', $user_id)->select('state', 'country')->first();
            if (! $user instanceof User) {
                throw new Exception('User not found.');
            }
            $user_state = (string) ($user->state ?? '');
            $user_country = (string) ($user->country ?? '');
            $tax = $this->calculateTax($product->id, $user_state, $user_country, taxCaluculationFromAdminPanel: true);
            $grand_total = rounding($this->calculateTotal($tax['value'], $grandTotalAfterCoupon));
            $subtotal = $qty * $total;
            $coupon = $subtotal * (intval($couponTotal['value']) / 100);
            $invoice = Invoice::create(['user_id' => $user_id, 'number' => $number, 'date' => $date,
                'coupon_code' => $couponTotal['code'], 'discount' => $coupon, 'discount_mode' => $couponTotal['mode'], 'grand_total' => $grand_total,  'currency' => $currency, 'status' => $status, 'description' => $description, 'cloud_domain' => str_replace('.'.cloudSubDomain(), '', $cloud_domain)]);

            $items = $this->createInvoiceItemsByAdmin($invoice->id, $productid,
                $total, $currency, $qty, $agents, $plan, $user_id, $tax['name'], (float) $tax['value'], $total);
            Session::forget('plan');

            if (! $items instanceof InvoiceItem) {
                return errorResponse([__('message.can-not-generate-invoice')]);
            }

            return successResponse(__('message.invoice-generated-successfully'));
        } catch (Exception $exception) {
            Logger::exception($exception);

            return errorResponse([$exception->getMessage()]);
        }
    }

    public function executeInvoice(int $id): JsonResponse
    {
        try {
            $invoice = Invoice::findOrFail($id);
            // 'Paid' (checkout/cart flow) and 'Success' (manual payment recording,
            // client renewals) both mean "fully paid" — see ExtendedBaseInvoiceController
            // and RenewController::successRenew().
            if (! in_array($invoice->status, ['Paid', 'Success'], true)) {
                return errorResponse(__('message.invoice-not-paid'));
            }

            (new OrderController)->executeOrder($id);

            if (! empty($invoice->cloud_domain)) {
                $cloudProductIds = CloudProducts::pluck('cloud_product');
                $orderIds = OrderInvoiceRelation::where('invoice_id', $id)->pluck('order_id');
                $orderNumber = Order::whereIn('id', $orderIds)
                    ->whereIn('product', $cloudProductIds)
                    ->value('number');
                if ($orderNumber) {
                    new TenantController(new Client, new FaveoCloud)->createTenant(
                        new Request(['orderNo' => $orderNumber, 'domain' => $invoice->cloud_domain, 'userInfo' => $invoice->user_id])
                    );
                }
            }

            return successResponse(__('message.order-executed-successfully'));
        } catch (Exception $exception) {
            return errorResponse([$exception->getMessage()]);
        }
    }

    public function createInvoiceItemsByAdmin(int $invoiceid, string $productid, mixed $price,
        string $currency, int $qty, mixed $agents, ?int $planid, int $userid, ?string $tax_name, float|int $tax_rate, mixed $grandTotalAfterCoupon): InvoiceItem|JsonResponse|RedirectResponse
    {
        try {
            $product = $this->product->findOrFail($productid);
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
            if ($tax_name && strtolower($tax_name) !== 'null' && $percent > 0) {
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
            return errorResponse($exception->getMessage());
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

    public function sendMail(int $userid, int $invoiceid): void
    {
        try {
            $invoice = $this->invoice->findOrFail($invoiceid);
            $number = $invoice->number;
            $total = $invoice->grand_total;

            $this->sendInvoiceMail($userid, $number, $total, $invoiceid); // @phpstan-ignore argument.type
        } catch (Exception $exception) {
            throw new Exception($exception->getMessage(), $exception->getCode(), $exception);
        }
    }

    public function pdf(Request $request): JsonResponse|PdfBuilder
    {
        try {
            $id = $request->input('invoiceid');
            if (! $id) {
                return errorResponse(__('message.no-invoice-id'));
            }

            $invoice = $this->invoice->find($id);
            if (! $invoice instanceof Invoice) {
                return errorResponse(__('message.invalid-invoice-id'));
            }

            $authUser = Auth::user();
            if (! $authUser instanceof User) {
                return errorResponse('Unauthorized', 401);
            }
            if ($invoice->user_id != $authUser->id && $authUser->role != 'admin') {
                return errorResponse(__('message.invalid_user'));
            }

            $totals = self::calculateInvoice($id, formatCurrency: true);

            $set = Setting::select(
                'id', 'company', 'address', 'state', 'zip', 'city', 'country',
                'phone_code', 'phone', 'logo', 'company_email', 'gstin', 'cin_no'
            )->first();

            $invoiceUser = $invoice->user;
            if ($invoiceUser instanceof User) {
                $userCountry = (string) ($invoiceUser->country ?? '');
                $userState = (string) ($invoiceUser->state ?? '');
                $invoiceUser->state = array_key_exists('name', getStateByCode($userCountry, $userState))
                    ? getStateByCode($userCountry, $userState)['name']
                    : $invoiceUser->state;
            }

            $invoiceItems = $invoice->invoiceItem()->with('order:id,invoice_item_id')->get();

            return Pdf::view('themes.default1.invoice.newpdf', [
                'invoice' => $invoice,
                'invoiceItems' => $invoiceItems,
                'user' => $invoiceUser,
                'set' => $set,
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

    public function exportInvoices(Request $request): JsonResponse
    {
        try {
            ini_set('memory_limit', '-1');
            $selectedColumns = $request->input('selected_columns', []);
            $searchParams = $request->input('search_params', []);
            $authUser = Auth::user();
            if (! $authUser instanceof User) {
                return errorResponse('Unauthorized', 401);
            }
            $email = $authUser->email;
            $driver = QueueService::where('status', '1')->first();
            if (! $driver instanceof QueueService) {
                return errorResponse('Queue driver not configured.');
            }

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

    public function getInvoice(int $id): JsonResponse
    {
        try {
            $query = Invoice::with([
                'user:id,first_name,last_name,email,company,address,town,state,country,zip,mobile_code,mobile,gstin',
                'invoiceItem.order:id,number,invoice_item_id',
                'allocations.payment',
            ])->findOrFail($id);

            if (! $query->user || User::onlyTrashed()->find($query->user->id)) {
                throw new Exception(__('message.user_suspended'));
            }

            // Company settings
            $setting = Setting::select(
                'id', 'company', 'address', 'state', 'zip', 'city', 'country',
                'phone_code', 'phone', 'logo', 'company_email'
            )->first();
            if (! $setting instanceof Setting) {
                throw new Exception('Company settings not configured.');
            }

            $settingCountry = (string) ($setting->country ?? '');
            $settingState = (string) ($setting->state ?? '');
            $setting->state = array_key_exists('name', getStateByCode($settingCountry, $settingState))
                ? getStateByCode($settingCountry, $settingState)['name']
                : $setting->state;

            $userCountry = (string) ($query->user->country ?? '');
            $userState = (string) ($query->user->state ?? '');
            $query->user->state = array_key_exists('name', getStateByCode($userCountry, $userState))
                ? getStateByCode($userCountry, $userState)['name']
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
                // Each payment with the slice of itself that landed here — a
                // payment covering three invoices must not show its full amount
                // against this one.
                'payments' => $query->allocations->map(fn ($allocation): array => [
                    'id' => $allocation->payment_id,
                    'amount' => (float) $allocation->amount,
                    'payment_method' => $allocation->payment?->payment_method,
                    'payment_status' => $allocation->payment?->payment_status,
                    'created_at' => $allocation->payment?->created_at,
                ])->values(),
            ];

            return successResponse('', $invoice);
        } catch (Exception $exception) {
            return errorResponse($exception->getMessage());
        }
    }

    /**
     * Get dynamic invoice totals for a given invoice ID.
     *
     * @param  bool  $formatCurrency  - whether to format currency strings or return numeric
     * @return array<mixed>
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
            $credits = round((float) $credits, 2);
            $discount = round((float) $discount, 2);
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
