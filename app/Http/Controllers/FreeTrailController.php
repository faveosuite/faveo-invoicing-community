<?php

namespace App\Http\Controllers;

use App\Http\Controllers\License\LicenseController;
use App\Http\Controllers\Order\BaseOrderController;
use App\Http\Controllers\Tenancy\TenantController;
use App\Model\Common\FaveoCloud;
use App\Model\Common\StatusSetting;
use App\Model\Order\Invoice;
use App\Model\Order\InvoiceItem;
use App\Model\Order\Order;
use App\Model\Product\CloudProducts;
use App\Model\Product\Product;
use App\Model\Product\Subscription;
use Auth;
use Crypt;
use DB;
use GuzzleHttp\Client;
use Illuminate\Http\Request;

class FreeTrailController extends Controller
{
    public $orderNo = null;
    public $tenantController;
    public $subscription;
    public $invoice;
    public $invoiceItem;
    public $order;
    public $product;

    public function __construct(?TenantController $tenantController = null)
    {
        $this->middleware('auth');

        $this->invoice = new Invoice();
        $this->invoiceItem = new InvoiceItem();
        $this->order = new Order();
        $this->subscription = new Subscription();
        $this->product = new Product();

        $this->tenantController = $tenantController ?: new TenantController(new Client, new FaveoCloud);
    }

    /**
     * Handle free trial creation.
     */
    public function firstLoginAttempt(Request $request)
    {
        $request->validate([
            'domain' => ['required', 'regex:/^[a-zA-Z0-9]+$/u'],
        ], [
            'domain.regex' => __('validation.special_characters_not_allowed'),
        ]);

        try {
            if (! Auth::check()) {
                return redirect()->route('login')->with('fails', __('message.free-login'));
            }

            $user = Auth::user();
            if ($user->id != $request->id) {
                throw new \Exception(__('message.cannot_generate_freetrial_cloud_instance'));
            }

            // Fetch cloud product
            $cloudProduct = CloudProducts::where('cloud_product_key', $request->product)
                ->select('cloud_free_plan', 'cloud_product')
                ->firstOrFail();

            // Prevent multiple trials
            $alreadyUsed = DB::table('free_trial_allowed')
                ->where('user_id', $user->id)
                ->where('product_id', $cloudProduct->cloud_product)
                ->exists();

            if ($alreadyUsed) {
                return ['status' => 'false', 'message' => __('message.limit_is_up')];
            }

            DB::beginTransaction();

            try {
                // Load product
                $product = Product::findOrFail($cloudProduct->cloud_product);

                // Create invoice
                $invoice = $this->generateFreetrialInvoice();

                // Create invoice item
                $invoiceItem = $this->createFreetrialInvoiceItems($invoice, $product);

                // Create order + license
                $serialKey = $this->executeFreetrialOrder($invoice, $invoiceItem);

                // Create tenant
                $tenantResponse = $this->tenantController->createTenant(
                    new Request([
                        'orderNo' => $this->orderNo,
                        'domain' => $request->domain,
                    ])
                );

                if ($tenantResponse['status'] === 'false') {
                    (new LicenseController())->deActivateTheLicense($serialKey);
                    DB::rollBack();

                    return $tenantResponse;
                }

                // Store usage
                DB::table('free_trial_allowed')->insert([
                    'user_id' => $user->id,
                    'product_id' => $cloudProduct->cloud_product,
                    'domain' => $tenantResponse['Free_trial_domain'],
                ]);

                Session()->forget('planDays');
                DB::commit();

                return $tenantResponse;
            } catch (\Throwable $e) {
                DB::rollBack();
                \Logger::exception($e);
                throw new \Exception(__('message.cannot_generate_freetrial_cloud_instance'));
            }
        } catch (\Throwable $e) {
            \Logger::exception($e);
            throw new \Exception(__('message.cannot_generate_freetrial_cloud_instance'));
        }
    }

    /**
     * Create invoice for trial.
     */
    private function generateFreetrialInvoice(): Invoice
    {
        try {
            $user = Auth::user();

            return Invoice::create([
                'user_id' => $user->id,
                'number' => random_int(10000000, 99999999),
                'date' => now(),
                'grand_total' => 0,
                'status' => 'success',
                'currency' => getCurrencyForClient($user->country),
            ]);
        } catch (\Throwable $e) {
            \Logger::exception($e);
            throw new \Exception(__('message.cannot_generate_invoice'));
        }
    }

    /**
     * Create invoice item.
     */
    private function createFreetrialInvoiceItems(Invoice $invoice, Product $product): InvoiceItem
    {
        try {
            return InvoiceItem::create([
                'invoice_id' => $invoice->id,
                'product_name' => $product->name,
                'product_id' => $product->id,
                'regular_price' => 0,
                'quantity' => 1,
                'tax_name' => 'null',
                'tax_percentage' => '0%',
                'subtotal' => 0,
                'domain' => '',
                'plan_id' => 0,
                'agents' => 1,
            ]);
        } catch (\Throwable $e) {
            \Logger::exception($e);
            throw new \Exception(__('message.cannot_generate_invoice_items'));
        }
    }

    /**
     * Create order.
     */
    private function executeFreetrialOrder(Invoice $invoice, InvoiceItem $invoiceItem)
    {
        try {
            return $this->createFreetrialOrder($invoice, $invoiceItem);
        } catch (\Throwable $e) {
            \Logger::exception($e);
            throw new \Exception(__('message.cannot_generate_order'));
        }
    }

    /**
     * Core order logic.
     */
    private function createFreetrialOrder(Invoice $invoice, InvoiceItem $invoiceItem)
    {
        try {
            $serialKey = $this->generateFreetrialSerialKey($invoiceItem->agents);

            $order = Order::create([
                'invoice_id' => $invoice->id,
                'invoice_item_id' => $invoiceItem->id,
                'client' => $invoice->user_id,
                'order_status' => 'executed',
                'serial_key' => Crypt::encrypt($serialKey),
                'product' => $invoiceItem->product_id,
                'price_override' => $invoiceItem->subtotal,
                'qty' => $invoiceItem->quantity,
                'domain' => $invoiceItem->domain,
                'number' => random_int(10000000, 99999999),
            ]);

            $this->orderNo = $order->number;

            $baseOrder = new BaseOrderController();
            $baseOrder->addOrderInvoiceRelation($invoice->id, $order->id);

            Session()->put('planDays', 'freeTrial');

            // Add subscription
            $product = Product::findOrFail($invoiceItem->product_id);
            $baseOrder->addSubscription($order->id, $invoiceItem->plan_id, $product->version, $product->id, $serialKey);

            // Add-ons
            $addOnIds = implode(',', $product->productPluginGroupsAsProduct->pluck('plugin_id')->toArray());
            $options = $baseOrder->formatConfigurableOptions($product->id);

            (new LicenseController())->syncTheAddonForALicense($addOnIds, $serialKey, $options);

            // Mailchimp
            if (StatusSetting::value('mailchimp_status')) {
                $baseOrder->addtoMailchimp($product->id, $invoice->user_id, $invoiceItem);
            }

            Session()->forget('planDays');

            return $serialKey;
        } catch (\Throwable $e) {
            \Logger::exception($e);
            throw new \Exception(__('message.cannot_generate_free_trial_order'));
        }
    }

    /**
     * Serial key generator.
     */
    private function generateFreetrialSerialKey($agents)
    {
        try {
            $len = strlen($agents);
            switch ($len) {//Get Last Four digits based on No.Of Agents
                case '1':
                    $lastFour = '000'.$agents;
                    break;
                case '2':
                    $lastFour = '00'.$agents;
                    break;
                case '3':
                    $lastFour = '0'.$agents;
                    break;
                case '4':
                    $lastFour = $agents;
                    break;
                default:
                    $lastFour = '0000';
            }
            $str = strtoupper(str_random(12));
            $licCode = $str.$lastFour;

            return $licCode;
        } catch (\Exception $ex) {
            \Logger::exception($ex);
            throw new \Exception(__('message.cannot_generate_free_trial_serialkey'));
        }
    }
}
