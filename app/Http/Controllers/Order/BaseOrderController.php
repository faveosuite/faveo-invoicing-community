<?php

namespace App\Http\Controllers\Order;

use App\Http\Controllers\Common\PhpMailController;
use App\Http\Controllers\License\LicensePermissionsController;
use App\License\Services\LicenseService;
use App\Model\Common\Setting;
use App\Model\Common\Template;
use App\Model\Common\TemplateType;
use App\Model\Configure\ProductPluginGroup;
use App\Model\Order\Invoice;
use App\Model\Order\InvoiceItem;
use App\Model\Order\Order;
use App\Model\Order\OrderInvoiceRelation;
use App\Model\Payment\Plan;
use App\Model\Product\Price;
use App\Model\Product\Product;
use App\Model\Product\ProductUpload;
use App\Model\Product\Subscription;
use App\Plugins\Stripe\Controllers\SettingsController;
use App\Traits\Order\UpdateDates;
use App\User;
use Carbon\Carbon;
use Crypt;
use Exception;
use Illuminate\Contracts\Routing\UrlGenerator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Date;

class BaseOrderController extends ExtendedOrderController
{
    protected mixed $sendMail = null;

    use UpdateDates;

    /**
     * inserting the values to orders table.
     *
     *
     * @return Collection<int|string, mixed>
     *
     * @throws Exception
     */
    public function executeOrder(int $invoiceId): Collection
    {
        $userId = Invoice::findOrFail($invoiceId)->user_id;
        $items = InvoiceItem::where('invoice_id', $invoiceId)->get();

        return $items->map(fn (InvoiceItem $item): Order => $this->processInvoiceItem($item, $userId)); // @phpstan-ignore return.type
    }

    private function processInvoiceItem(InvoiceItem $item, int $userId): Order
    {
        $productModel = Product::findOrFail($item->product_id);
        $product = $productModel->id;
        $version = ProductUpload::where('product_id', $product)->value('version') ?? '';

        $serialKey = $this->generateSerialKey($product, $item->agents); // @phpstan-ignore argument.type

        $order = Order::create([
            'invoice_item_id' => $item->id,
            'client' => $userId,
            'order_status' => 'executed',
            'serial_key' => Crypt::encrypt($serialKey),
            'product' => $product,
            'price_override' => $item->subtotal,
            'qty' => $item->quantity,
            'domain' => $item->domain,
            'number' => $this->generateNumber(),
        ]);

        OrderInvoiceRelation::create([
            'order_id' => $order->id, 'invoice_id' => $item->invoice_id]
        );

        if ($item->plan_id) {
            $this->addSubscription($order->id, $item->plan_id, $version, $product, $serialKey, $item->invoice_id);
            /** @var Product $productForAddons */
            $productForAddons = Product::find($product);
            $addOnIds = $productForAddons->productPluginGroupsAsProduct->pluck('plugin_id')->toArray();
            $cfgOptions = $this->formatConfigurableOptions($product);
            $options = is_array($cfgOptions) ? $cfgOptions : $cfgOptions->toArray();
            resolve(LicenseService::class)->syncAddons($serialKey, $addOnIds, $options);
        }

        if (emailSendingStatus()) {
            $this->sendOrderMail($userId, $order->id, $item->id); // @phpstan-ignore argument.type
        }

        return $order;
    }

    /**
     * inserting the values to subscription table.
     *
     *
     * @throws Exception
     *
     * @author Ashutosh Pathak <ashutosh.pathak@ladybirdweb.com>
     */
    public function addSubscription(int $orderid, int $planid, string $version, int $product, string $serial_key, ?int $invoiceId = null): void
    {
        $permissions = LicensePermissionsController::getPermissionsForProduct($product);
        $version ??= ''; // @phpstan-ignore nullCoalesce.variable

        $plan = Plan::findOrFail($planid);
        $order = Order::findOrFail($orderid);

        $meta = $invoiceId ? (Invoice::find($invoiceId)->metadata ?? []) : [];

        if (isset($meta['increase-decrease-days'])) {
            $days = $meta['increase-decrease-days'];
            $licenseExpiry = $this->getLicenseExpiryDate($permissions['generateLicenseExpiryDate'], $days);
            $updatesExpiry = $this->getUpdatesExpiryDate($permissions['generateUpdatesxpiryDate'], $days);
            $supportExpiry = $this->getSupportExpiryDate($permissions['generateSupportExpiryDate'], $days);
        } elseif (isset($meta['increase-decrease-days-dont-cloud'])) {
            $sub = Subscription::where('order_id', $meta['increase-decrease-days-dont-cloud'])->first();
            $licenseExpiry = $sub?->ends_at;
            $updatesExpiry = $sub?->ends_at;
            $supportExpiry = $sub?->ends_at;
        } else {
            $isOneTime = $plan->periods()->where('name', 'One Time')->exists();
            $licenseExpiry = $isOneTime ? '' : $this->getLicenseExpiryDate($permissions['generateLicenseExpiryDate'], $plan->days); // @phpstan-ignore argument.type
            $updatesExpiry = $this->getUpdatesExpiryDate($permissions['generateUpdatesxpiryDate'], $plan->days); // @phpstan-ignore argument.type
            $supportExpiry = $this->getSupportExpiryDate($permissions['generateSupportExpiryDate'], $plan->days); // @phpstan-ignore argument.type
        }

        Subscription::create([
            'user_id' => $order->client,
            'plan_id' => $plan->id,
            'order_id' => $orderid,
            'update_ends_at' => $updatesExpiry,
            'ends_at' => $licenseExpiry,
            'support_ends_at' => $supportExpiry,
            'version' => $version,
            'product_id' => $product,
            'is_subscribed' => '0',
        ]);

        $ipAndDomain = LicenseService::parseIpAndDomain($order->domain ?? '');
        resolve(LicenseService::class)->create([
            'product_id' => $product,
            'user_id' => $order->client,
            'license_code' => $serial_key,
            'license_order_number' => $order->number,
            'license_domain' => $ipAndDomain['domain'],
            'license_ip' => $ipAndDomain['ip'],
            'license_require_domain' => $ipAndDomain['requireDomain'],
            'license_limit' => 1,
            'license_expire_date' => $licenseExpiry instanceof Carbon ? $licenseExpiry->toDateString() : null,
            'license_updates_date' => $updatesExpiry instanceof Carbon ? $updatesExpiry->toDateString() : null,
            'license_support_date' => $supportExpiry instanceof Carbon ? $supportExpiry->toDateString() : null,
            'license_status' => 1,
        ]);
    }

    /**
     *  Get the Expiry Date for License.
     *
     * @param  bool  $permissions  [Whether Permissons for generating License Expiry Date are there or not]
     * @param  int  $days  [No of days that would get addeed to the current date ]
     * @return string [The final License Expiry date that is generated]
     */
    protected function getLicenseExpiryDate(bool $permissions, int $days): Carbon|string
    {
        $ends_at = '';
        if ($days > 0 && $permissions == 1) {
            $dt = Date::now();
            $ends_at = $dt->addDays($days);
        }

        return $ends_at;
    }

    /**
     *  Get the Expiry Date for Updates.
     *
     * @param  bool  $permissions  [Whether Permissons for generating Updates Expiry Date are there or not]
     * @param  int  $days  [No of days that would get added to the current date ]
     * @return string [The final Updates Expiry date that is generated]
     */
    protected function getUpdatesExpiryDate(bool $permissions, int $days): Carbon|string
    {
        $update_ends_at = '';
        if ($days > 0 && $permissions == 1) {
            $dt = Date::now();
            $update_ends_at = $dt->addDays($days);
        }

        return $update_ends_at;
    }

    /**
     *  Get the Expiry Date for Support.
     *
     * @param  bool  $permissions  [Whether Permissons for generating Updates Expiry Date are there or not]
     * @param  int  $days  [No of days that would get added to the current date ]
     * @return string [The final Suport Expiry date that is generated]
     */
    protected function getSupportExpiryDate(bool $permissions, int $days): Carbon|string
    {
        $support_ends_at = '';
        if ($days > 0 && $permissions == 1) {
            $dt = Date::now();
            $support_ends_at = $dt->addDays($days);
        }

        return $support_ends_at;
    }

    public function sendOrderMail(int $userid, string $orderid, int $itemid): void
    {
        // order
        $order = Order::find($orderid);
        // product
        $product = $this->product($itemid); // @phpstan-ignore method.notFound
        // user
        $productId = Product::where('id', $product)->value('id');
        $users = new User;
        /** @var User $user */
        $user = $users->find($userid);
        // check in the settings
        $settings = new Setting;
        /** @var Setting $setting */
        $setting = $settings::find(1);
        $orders = new Order;
        /** @var Order $order */
        $order = $orders->where('id', $orderid)->first();
        $invoiceId = OrderInvoiceRelation::where('order_id', $orderid)->value('invoice_id');
        /** @var Invoice|null $invoice */
        $invoice = Invoice::find($invoiceId);
        $number = $invoice?->number;
        $downloadurl = '';
        if ($user && $order->order_status == 'Executed') { // @phpstan-ignore booleanAnd.leftAlwaysTrue
            $downloadurl = url('product/download/'.$productId.'/'.$number);
        }

        // $downloadurl = $this->downloadUrl($userid, $orderid,$productId);
        $myaccounturl = url('my-order/'.$orderid);
        $invoiceurl = (string) $this->invoiceUrl($orderid); // @phpstan-ignore argument.type, cast.string
        // template
        $this->getMail($setting, $user, $downloadurl, $invoiceurl, $order, $productId, $orderid, $myaccounturl, $order->serial_key);
    }

    public function getMail(Setting $setting, User $user, string $downloadurl, string $invoiceurl, Order $order, ?int $productId, string $orderid, string $myaccounturl, string $licenseCode): void
    {
        $contact = getContactData();
        $product = Product::where('id', $productId)->first();
        if (! $product) {
            return;
        }

        $value = $product->type;

        /** @var Template $template */
        $template = TemplateType::getSelectedTemplate('order_mail');

        $knowledgeBaseUrl = $setting->knowledge_base_url;

        $knowledgeBaseUrlFinal = $knowledgeBaseUrl == null
            ? '<p>'.__('message.knowledge_base_no_url').'</p>
       <p>'.__('message.control_panel_invoice').'</p>'
            : '<p><a class="moz-txt-link-abbreviated" href="'.$knowledgeBaseUrl.'">'.__('message.knowledge_base_with_url').'</a> '.__('message.knowledge_base_help').'</p>
       <p>'.__('message.control_panel_invoice').'</p>';

        $orderHeading = ($value != '4') ? 'Download' : 'Order';
        $orderUrl = ($value != '4') ? $downloadurl : url('my-order/'.$orderid);
        $end = resolve(OrderController::class)->expiry((int) $orderid);
        $end = $end ? Date::parse($end)->format('M d, Y') : '';

        $replace = [
            'orderHeading' => $orderHeading,
            'name' => $user->first_name.' '.$user->last_name,
            'serialkeyurl' => $myaccounturl,
            'downloadurl' => $orderUrl,
            'invoiceurl' => $invoiceurl,
            'product' => $product->name,
            'number' => $order->number,
            'expiry' => $end,
            'url' => resolve(OrderController::class)->renew((int) $orderid),
            'knowledge_base' => $knowledgeBaseUrlFinal,
            'contact' => $contact['contact'],
            'logo' => $contact['logo'],
            'reply_email' => $setting->company_email,
            'licenseCode' => $licenseCode,
        ];

        $type = $template->type()->value('name') ?? '';
        $mail = new PhpMailController;
        $mail->SendEmail($setting->email, $user->email, $template->data, $template->name, $template->type()->value('name'), $replace, $type);

        $invoiceId = OrderInvoiceRelation::where('order_id', $orderid)->value('invoice_id');
        /** @var Invoice|null $orderInvoice */
        $orderInvoice = $invoiceId ? Invoice::find($invoiceId) : null;
        if ($orderInvoice?->grand_total) {
            SettingsController::sendPaymentSuccessMailtoAdmin($orderInvoice, (float) $orderInvoice->grand_total, $user, $product->name);
        }
    }

    public function invoiceUrl(int $orderid): UrlGenerator|string
    {
        $invoiceid = OrderInvoiceRelation::where('order_id', $orderid)->value('invoice_id');

        return url('my-invoice/'.$invoiceid);
    }

    /**
     * get the price of a product by id.
     *
     *
     * @throws Exception
     */
    public function getPrice(int $product_id): ?Price
    {
        try {
            return Price::where('product_id', $product_id)->first();
        } catch (Exception $exception) {
            throw new Exception($exception->getMessage(), $exception->getCode(), $exception);
        }
    }

    public function downloadUrl(string $userid, int $orderid): UrlGenerator|string
    {
        $invoiceId = OrderInvoiceRelation::where('order_id', $orderid)->value('invoice_id');
        /** @var Invoice|null $invoice */
        $invoice = Invoice::find($invoiceId);
        $number = $invoice?->number;

        return url('download/'.$userid.'/'.$number);
    }

    /**
     * @return array<mixed>|Collection<int, array{product_id: int, option_group: string, option_name: string, key: mixed, value: mixed}>
     */
    public function formatConfigurableOptions(int $productId): Collection|array
    {
        // Retrieve the product ID and related plugin IDs in one query
        $productIds = ProductPluginGroup::where('product_id', $productId)
            ->pluck('plugin_id')
            ->prepend($productId)
            ->toArray();

        // Fetch all products with related configurations in one query
        $products = Product::with('configOptions.configOptionValues')
            ->whereIn('id', $productIds)
            ->get();

        // Check if any products were found
        if ($products->isEmpty()) {
            return [];
        }

        // Format the configuration options
        // @phpstan-ignore return.type
        return $products->flatMap(fn ($product) => $product->configOptions->flatMap(fn ($configOption) => $configOption->configOptionValues->map(fn ($configOptionValue): array => [
            'product_id' => $product->id,
            'option_group' => $configOption->configGroup->config_group_name,
            'option_name' => $configOption->config_option_name,
            'key' => $configOptionValue->key,
            'value' => $configOptionValue->value,
        ])));
    }
}
