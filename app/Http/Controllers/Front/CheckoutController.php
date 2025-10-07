<?php

namespace App\Http\Controllers\Front;

use App\Http\Controllers\Common\MailChimpController;
use App\Http\Controllers\Common\TemplateController;
use App\Http\Controllers\Order\ExtendedBaseInvoiceController;
use App\Http\Controllers\Tenancy\TenantController;
use App\Model\Common\CreditActivity;
use App\Model\Common\FaveoCloud;
use App\Model\Common\Setting;
use App\Model\Common\Template;
use App\Model\Order\Invoice;
use App\Model\Order\InvoiceItem;
use App\Model\Order\Order;
use App\Model\Order\OrderInvoiceRelation;
use App\Model\Order\Payment;
use App\Model\Payment\Plan;
use App\Model\Payment\Promotion;
use App\Model\Payment\PromotionType;
use App\Model\Product\Price;
use App\Model\Product\Product;
use App\Model\Product\Subscription;
use App\Traits\Payment\PostPaymentHandle;
use App\Traits\TaxCalculation;
use App\User;
use Carbon\Carbon;
use Cart;
use Darryldecode\Cart\CartCondition;
use GuzzleHttp\Client;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class CheckoutController extends InfoController
{
    use TaxCalculation;
    use PostPaymentHandle;

    public $subscription;

    public $plan;

    public $templateController;

    public $product;

    public $price;

    public $user;

    public $setting;

    public $template;

    public $order;

    public $addon;

    public $invoice;

    public $invoiceItem;

    public $mailchimp;

    public $cart;

    public function __construct()
    {
        $subscription = new Subscription();
        $this->subscription = $subscription;

        $plan = new Plan();
        $this->plan = $plan;

        $templateController = new TemplateController();
        $this->templateController = $templateController;

        $product = new Product();
        $this->product = $product;

        $price = new Price();
        $this->price = $price;

        $user = new User();
        $this->user = $user;

        $setting = new Setting();
        $this->setting = $setting;

        $template = new Template();
        $this->template = $template;

        $order = new Order();
        $this->order = $order;

        $invoice = new Invoice();
        $this->invoice = $invoice;

        $invoiceItem = new InvoiceItem();
        $this->invoiceItem = $invoiceItem;

        $this->cart = new \App\Facades\Cart();
        // $mailchimp = new MailChimpController();
        // $this->mailchimp = $mailchimp;
    }

    /**
     *  This function returns to the checkout page with necessary data.
     *
     * @param  Request  $request
     * @return \Illuminate\Contracts\View\View|RedirectResponse
     *
     * @throws
     */
    public function checkoutForm(Request $request)
    {
        if (! \Auth::user()) {//If User is not Logged in then send him to login Page
            $url = $request->segments(); //The requested url (chekout).Save it in Session
            \Session::put('session-url', $url[0]);
            $content = $this->cart->getContent();
            $domain = $request->input('domain');
            if ($domain) {
                foreach ($domain as $key => $value) {
                    \Session::put('domain'.$key, $value); //Store all the domains Entered in Cart Page in Session
                }
            }
            \Session::put('content', $content);

            return redirect('login')->with('fails', __('message.please_login'));
        }

        // if (\Cart::isEmpty()) {//During renewal when payment fails due to some reason
        //     $invoice = \Session::get('invoice');
        //     if ($invoice && \Session::has('fails')) {
        //         return redirect('paynow/'.$invoice->id)->with('fails', 'Payment cannot be processed. Please try the other gateway.');
        //     }
        // }

        $content = $this->cart->getContent();
        $taxConditions = $this->getAttributes($content);
        try {
            $domain = $request->input('domain');
            if ($domain) {//Store the Domain  in session when user Logged In
                foreach ($domain as $key => $value) {
                    \Session::put('domain'.$key, $value);
                }
            }
            $discountPrice = null;
            $price = [];
            $quantity = [];
            foreach ($this->cart->getContent() as $item) {
                $price = $item['price'];
                $quantity = $item['quantity'];
                $domain = $item['attributes']['domain'] ?? null;
                if (! empty(\Session::get('code'))) {
                    $price = \Session::get('oldPrice');
                    $value = Promotion::where('code', \Session::get('code'))->value('value');
                    $typeid = Promotion::where('code', \Session::get('code'))->value('type');
                    $type = PromotionType::find($typeid);
                    $discountPrice = $type->name == 'Percentage' ? $price * (intval($value) / 100) : $value;
                    \Session::put('discountPrice', $discountPrice);
                }
                \Session::put('cloud_domain', $domain);
            }
            if (\Session::has('priceRemaining')) {
                $total = \Session::get('priceRemaining') > $this->cart->getTotal() ? \Session::get('priceRemaining') - $this->cart->getTotal() : \Session::get('discount');
                \Session::forget('discount');
                \Session::put('discount', $total);
            }

            $amt_to_credit = Payment::where('user_id', \Auth::user()->id)
                ->where('payment_method', 'Credit Balance')
                ->where('payment_status', 'success')
                ->where('amt_to_credit', '!=', 0)
                ->value('amt_to_credit');

            $curr = '';
            if (empty($content)) {
                $curr = '';
            } else {
                foreach ($content as $item) {
                    $curr = $item['attributes']['currency'];
                }
            }

            User::where('id', \Auth::user()->id)->update(['billing_pay_balance' => 0]);
            $cart = $this->cart;

            return view('themes.default1.front.checkout', compact('content', 'taxConditions', 'discountPrice', 'domain', 'amt_to_credit', 'curr', 'cart'));
        } catch (\Exception $ex) {
            app('log')->error($ex->getMessage());

            return redirect()->back()->with('fails', $ex->getMessage());
        }
    }

    /**
     * Get all the Attributes Sent From the cart along with Tax Conditions.
     *
     * @param  array  $content  Collection of the Cart Values
     * @return array Items along with their details,Attributes(Currency,Agents) and Tax Conditions
     */
    public function getAttributes($content)
    {
        try {
            if (count($content) > 0) {//after ProductPurchase this is not true as cart is cleared
                foreach ($content as $item) {
                    $cart_currency = $item['attributes']['currency']; //Get the currency of Product in the cart
                    \Session::put('cart_currency', $cart_currency);
                    $currency = getCurrencyForClient(\Auth::user()->country) != $cart_currency ? getCurrencyForClient(\Auth::user()->country) : $cart_currency; //If User Currency and cart currency are different the currency es set to user currency.
                    if ($cart_currency != $currency) {
                        $id = $item->id;
                        Cart::remove($id);
                    }
                    $require_domain = $item['associatedModel']['require_domain'];
                    $require = [];
                    if ($require_domain) {
                        $require[$key] = $item['associatedModel']['id'];
                    }

                    $taxConditions = $this->calculateTax($item['associatedModel']['id'], \Auth::user()->state, \Auth::user()->country); //Calculate Tax Condition by passing ProductId
//                    Cart::condition($taxConditions);

                    $this->cart->remove($item['id']);
                    //Return array of Product Details,attributes and their conditions
          //          $items[] = ['id' => $item->id, 'name' => $item->name, 'price' => $item->price,
     //                   'quantity' => $item->quantity, 'attributes' => ['currency' => $cart_currency, 'symbol' => $item->attributes->symbol, 'agents' => $item->attributes->agents, 'domain' => optional($item->attributes)->domain, 'priceToBePaid' => $item->attributes->priceToBePaid, 'priceRemaining' => $item->attributes->priceRemaining], 'associatedModel' => Product::find($item->associatedModel->id), 'conditions' => $taxConditions, ];
                    $attribute = ['currency' => $cart_currency, 'symbol' => $item['attributes']['symbol'], 'agents' => $item['attributes']['agents'],
                        'domain' => optional($item['attributes']['domain']), 'priceToBePaid' => $item['attributes']['priceToBePaid'] ?? null,
                        'priceRemaining' => $item['attributes']['priceRemaining'] ?? null];
                    $this->cart->add($item['id'], $item['name'], $item['price'],
                        $item['quantity'], $attribute, $taxConditions, Product::find($item['associatedModel']['id']));
                }

//                Cart::add($items);

                return $taxConditions;
            }
        } catch (\Exception $ex) {
            app('log')->error($ex->getMessage());

            return redirect()->back()->with('fails', $ex->getMessage());
        }
    }

    /**
     *  This function returns paynow page(scenario:when order renewal).
     *
     * @param  $invoiceid
     * @return \Illuminate\Contracts\View\View|RedirectResponse
     *
     * @throws
     */
    public function payNow($invoiceid)
    {
        try {
            $paid = 0;
            $invoice = $this->invoice->find($invoiceid);
            if ($invoice->user_id != \Auth::user()->id) {
                throw new \Exception(__('message.invalid_payment_modification'));
            }
            if (count($invoice->payment()->get())) {//If partial payment is made
                $paid = array_sum($invoice->payment()->pluck('amount')->toArray());
                $invoice->grand_total = $invoice->grand_total - $paid;
            }
            $items = new \Illuminate\Support\Collection();
            if ($invoice) {
                $items = $invoice->invoiceItem()->get();
                if (count($items) > 0) {
                    $product = $this->product($invoiceid);
                }
            }

            return view('themes.default1.front.paynow', compact('invoice', 'items', 'product', 'paid'));
        } catch (\Exception $ex) {
            app('log')->error($ex->getMessage());

            return redirect()->back()->with('fails', $ex->getMessage());
        }
    }

    /**
     *  This function contains post checkout operations(adding credit balance,initiating payment, creating order,invoice generation,create invoice items,create subscription).
     *
     * @param  Request  $request
     * @return RedirectResponse
     *
     * @throws
     */
    public function postCheckout(Request $request)
    {
        $isTrue = 1;
        $cost = $request->input('cost');
        $discount = \Session::get('discount');

        if (\Session::has('nothingLeft')) {
            User::where('id', \Auth::user()->id)->update(['billing_pay_balance' => 1]);
            $isTrue = \Session::get('nothingLeft');
        }

        if ($isTrue != 0) {
            if (\Cart::getTotal() > 0) {
                if (Cart::getSubTotal() != 0 || $cost > 0) {
                    $this->validate($request, [
                        'payment_gateway' => 'required',
                    ], [
                        'payment_gateway.required' => __('message.please_select_payment'),
                    ]);
                }
            }
        }
        try {
            $invoice_controller = new \App\Http\Controllers\Order\InvoiceController();
            $info_cont = new \App\Http\Controllers\Front\InfoController();
            $payment_method = ($isTrue) ? $request->input('payment_gateway') : 'Credits';
            \Session::put('payment_method', $payment_method);
            $paynow = $this->checkregularPaymentOrRenewal($request->input('invoice_id'));

            $cost = $request->input('cost');
            $state = $this->getState();

            if ($paynow === false) {//When regular payment
                $invoice = $invoice_controller->generateInvoice();
                $amount = (\Session::has('nothingLeft')) ? \Session::get('nothingLeft') : intval($this->cart->getTotal());

                if ($amount) {//If payment is for paid product
                    \Event::dispatch(new \App\Events\PaymentGateway(['request' => $request, 'invoice' => $invoice]));
                } else {
                    $show = true;
                    $date = getDateHtml($invoice->date);
                    $product = $this->product($invoice->id);
                    $items = $invoice->invoiceItem()->get();
                    $url = '';

                    $this->checkoutAction($invoice); //For free product generate invoice without payment
                    $orderNumber = Order::where('invoice_id', $invoice->id)->value('number');

                    $orders = Order::where('invoice_id', $invoice->id)->get();

                    $url = view('themes.default1.front.postCheckoutTemplate', compact('invoice', 'date', 'product', 'items', 'orders', 'orderNumber', 'show'))->render();
                    // }
                    \Cart::clear();
                    if (\Session::has('nothingLeft')) {
                        $do = ! (\Session::get('priceToBePaid') < \Session::get('priceRemaining'));

                        $this->doTheDeed($invoice, $do);
                        \Session::forget('nothingLeft');
                    }
                    if (! empty($invoice->cloud_domain)) {
                        $orderNumber = Order::where('invoice_id', $invoice->id)->whereIn('product', cloudPopupProducts())->value('number');
                        (new TenantController(new Client, new FaveoCloud()))->createTenant(new Request(['orderNo' => $orderNumber, 'domain' => $invoice->cloud_domain]));
                    }
                    if ($discount != null) {
                        $this->updateCredit($discount);
                    }

                    $this->performCloudActions($invoice);

                    return redirect('checkout')->with('Success', $url);
                }
            } else {//When renewal, pending payments
                $invoiceid = $request->input('invoice_id');
                $invoice = $this->invoice->find($invoiceid);

                $amount = intval($invoice->grand_total);
                if (\Session::has('nothingLeft')) {
                    $check = \Session::get('nothingLeft');
                }
                if ($amount) {//If payment is for paid product
                    \Event::dispatch(new \App\Events\PaymentGateway(['request' => $request, 'invoice' => $invoice]));
                } else {
                    $show = false;
                    $true = false;
                    $control = new \App\Http\Controllers\Order\RenewController();
                    $payment = new \App\Http\Controllers\Order\InvoiceController();
                    if (! empty($invoice->billing_pay)) {
                        Invoice::where('id', $invoice->id)->update(['grand_total' => ($invoice->grand_total + $invoice->billing_pay)]);
                    }
                    $payment->postRazorpayPayment($invoice);
                    $date = getDateHtml($invoice->date);
                    $product = $this->product($invoice->id);
                    $items = $invoice->invoiceItem()->get();

                    $url = '';
                    if ($control->checkRenew($invoice->is_renewed) || \Session::has('AgentAlteration')) {
                        $true = true;
                    }
                    $this->checkoutAction($invoice, $true); //For free product generate invoice without payment
                    $order = OrderInvoiceRelation::where('invoice_id', $invoice->id)->value('order_id');
                    $orders = Order::where('id', $order)->get();
                    $orderNumber = Order::where('id', $order)->value('number');

                    $url = view('themes.default1.front.postCheckoutTemplate', compact('invoice', 'date', 'product', 'items', 'orders', 'orderNumber', 'show'))->render();
                    if (\Session::has('nothingLeft')) {
                        $this->doTheDeed($invoice);
                        \Session::forget('nothingLeft');
                    }
                    if (\Session::has('agentIncreaseDate') || $control->checkRenew($invoice->is_renewed)) {
                        $control = new \App\Http\Controllers\Order\RenewController();
                        $control->successRenew($invoice, true);
                    }
                    if (! empty($invoice->cloud_domain)) {
                        $orderNumber = Order::where('invoice_id', $invoice->id)->whereIn('product', cloudPopupProducts())->value('number');
                        (new TenantController(new Client, new FaveoCloud()))->createTenant(new Request(['orderNo' => $orderNumber, 'domain' => $invoice->cloud_domain]));
                    }
                    $this->performCloudActions($invoice);
                    \Cart::clear();

                    return redirect('checkout')->with('Success', $url);
                }
            }
        } catch (\Exception $ex) {
            return redirect()->back()->with('fails', $ex->getMessage());
        }
    }

    /**
     *  This function updates the credit balance.
     *
     * @param  $discount
     * @return
     *
     * @throws
     */
    private function updateCredit($discount)
    {
        $payUpdate = Payment::where('user_id', \Auth::user()->id)->where('payment_status', 'success')->where('payment_method', 'Credit Balance')->get();

        $pay = Payment::where('user_id', \Auth::user()->id)->where('payment_status', 'success')->where('payment_method', 'Credit Balance')->value('amt_to_credit');
        $formattedValue = currencyFormat(round($discount), getCurrencyForClient(\Auth::user()->country), true);
        $payment_id = Payment::where('user_id', \Auth::user()->id)->where('payment_status', 'success')->where('payment_method', 'Credit Balance')->value('id');
        $formattedPay = currencyFormat($pay, getCurrencyForClient(\Auth::user()->country), true);
        $orderId = \Session::get('creditOrderId');
        $orderNumber = Order::where('id', $orderId)->value('number');

        if (! $payUpdate->isEmpty()) {
            $pay = $pay + round($discount);
            Payment::where('user_id', \Auth::user()->id)->where('payment_status', 'success')->update(['amt_to_credit' => $pay]);

            $messageAdmin = 'An amount of '.$formattedValue.' has been added to the existing balance due to a product downgrade. You can view the details of the downgraded order here: '.
                '<a href="'.config('app.url').'/orders/'.$orderId.'">'.$orderNumber.'</a>.';

            $messageClient = 'An amount of '.$formattedValue.' has been added to your existing balance due to a product downgrade. You can view the details of the downgraded order here: '.
                '<a href="'.config('app.url').'/my-order/'.$orderId.'">'.$orderNumber.'</a>.';
            CreditActivity::insert(['payment_id' => $payment_id, 'text' => $messageAdmin, 'role' => 'admin', 'created_at' => \Carbon\Carbon::now(), 'updated_at' => \Carbon\Carbon::now()]);
            CreditActivity::insert(['payment_id' => $payment_id, 'text' => $messageClient, 'role' => 'user', 'created_at' => \Carbon\Carbon::now(), 'updated_at' => \Carbon\Carbon::now()]);
        } else {
            $price = 0;
            \Session::put('discount', round($discount));
            (new ExtendedBaseInvoiceController())->multiplePayment(\Auth::user()->id, [0 => 'Credit Balance'], 'Credit Balance', Carbon::now(), $price, null, round($discount), 'pending');
        }
    }

    private function getProcessingFee($paymentMethod, $currency)
    {
        try {
            if ($paymentMethod) {
                return $paymentMethod == 'razorpay' ? 0 : \DB::table(strtolower($paymentMethod))->where('currencies', $currency)->value('processing_fee');
            }
        } catch (\Exception $e) {
            throw new \Exception(__('message.invalid_modification'));
        }
    }

    public static function updateFinalPrice(Request $request)
    {
        $value = '0%';
        if ($request->input('processing_fee')) {
            $value = $request->input('processing_fee').'%';
        }

        $updateValue = new CartCondition([
            'name' => 'Processing fee',
            'type' => 'fee',
            'target' => 'total',
            'value' => $value,
        ]);
        \Cart::condition($updateValue);
    }

    /**
     *  This function checks if it is a regular payment or renewal.
     *
     * @param  $invoiceid
     * @return bool
     *
     * @throws
     */
    public function checkregularPaymentOrRenewal($invoiceid)
    {
        $paynow = false;

        if ($invoiceid) {
            if (Invoice::find($invoiceid)->user_id != \Auth::user()->id) {
                throw new \Exception(__('message.invalid_modification'));
            }
            $paynow = true;
        }

        return $paynow;
    }

    /**
     *  This function is called in postcheckout here execution of order and post razorpay operation are done here.
     *
     * @param  $invoice
     * @param  $agent
     * @return string|RedirectResponse
     *
     * @throws
     */
    public function checkoutAction($invoice, $agent = false)
    {
        try {
            //get elements from invoice
            $invoice_number = $invoice->number;
            $invoice_id = $invoice->id;
            if ($this->cart->getConditions('fee') != null) {
                foreach ($this->cart->getConditions('fee') as $value) {
                    $invoice->processing_fee = $value['value'];
                }
            }
            // $invoice->processing_fee =
            $invoice->status = 'success';
            $invoice->save();
            $user_id = \Auth::user()->id;

            $url = '';

            $url = url("download/$user_id/$invoice->number");

            //execute the order
            if (! $agent) {
                $payment = new \App\Http\Controllers\Order\InvoiceController();
                $payment->postRazorpayPayment($invoice);

                $order = new \App\Http\Controllers\Order\OrderController();
                $order->executeOrder($invoice->id, $order_status = 'executed');
            }

            return 'success';
        } catch (\Exception $ex) {
            dd($ex->getMessage());
            app('log')->error($ex->getMessage());

            return redirect()->back()->with('fails', $ex->getMessage());
        }
    }

    public function product($invoiceid)
    {
        try {
            $invoice = $this->invoiceItem->where('invoice_id', $invoiceid)->first();
            $name = $invoice->product_name;
            $product = $this->product->where('name', $name)->first();

            return $product;
        } catch (\Exception $ex) {
            app('log')->error($ex->getMessage());

            throw new \Exception($ex->getMessage());
        }
    }

    /**
     *  This function is called in postcheckout this function is executed when user paid using credit balance and while changing cloud plan
     *   if the payable amount is less than old plans remaining amount in this we update the amt_to_credit.
     *
     * @param  $invoice
     * @param  $do
     *
     * @throws
     */
    private function doTheDeed($invoice, $do = true)
    {
        Payment::where('user_id', \Auth::user()->id)->where('payment_method', 'Credit Balance')->latest()->update(['payment_status' => 'success']);
        $amt_to_credit = Payment::where('user_id', \Auth::user()->id)->where('payment_status', 'success')->where('payment_method', 'Credit Balance')->value('amt_to_credit');
        if ($amt_to_credit && $do) {
            $amt_to_credit = (int) $amt_to_credit - (int) $invoice->billing_pay;
            Payment::where('user_id', \Auth::user()->id)->where('payment_method', 'Credit Balance')->where('payment_status', 'success')->update(['amt_to_credit' => $amt_to_credit]);
            User::where('id', \Auth::user()->id)->update(['billing_pay_balance' => 0]);
            $payment_id = \DB::table('payments')->where('user_id', \Auth::user()->id)->where('payment_status', 'success')->where('payment_method', 'Credit Balance')->value('id');
            $formattedValue = currencyFormat($invoice->billing_pay, $invoice->currency, true);
            $messageAdmin = 'The payment balance of '.$formattedValue.' has been utilized or adjusted with this invoice.'.
                ' You can view the details of the invoice '.
                '<a href="'.config('app.url').'/invoices/show?invoiceid='.$invoice->id.'">'.$invoice->number.'</a>.';

            $messageClient = 'The payment balance of '.$formattedValue.' has been utilized or adjusted with this invoice.'.
                ' You can view the details of the invoice '.
                '<a href="'.config('app.url').'/my-invoice/'.$invoice->id.'">'.$invoice->number.'</a>.';

            \DB::table('credit_activity')->insert(['payment_id' => $payment_id, 'text' => $messageAdmin, 'role' => 'admin', 'created_at' => \Carbon\Carbon::now(), 'updated_at' => \Carbon\Carbon::now()]);
            \DB::table('credit_activity')->insert(['payment_id' => $payment_id, 'text' => $messageClient, 'role' => 'user', 'created_at' => \Carbon\Carbon::now(), 'updated_at' => \Carbon\Carbon::now()]);
        }
    }

    /**
     *  This function performs multiple cloud operations(UpgradeDowngradePlan,AgentAlteration,AgentAlterationForRenewal).
     *
     * @param  $invoice
     *
     * @throws
     */
    private function performCloudActions($invoice)
    {
        $cloud = new \App\Http\Controllers\Tenancy\CloudExtraActivities(new Client, new FaveoCloud());

        if ($cloud->checkUpgradeDowngrade()) {
            $this->cloudUpDownGradeOps($cloud, $invoice);
        } elseif ($cloud->checkAgentAlteration()) {
            $this->cloudAgentAlterationOps($cloud, $invoice);
        } elseif (\Session::has('AgentAlterationRenew')) { // Added missing parentheses
            $this->cloudAgentAlterationRenew($cloud, $invoice);
        }
    }

    /**
     *  This function performs upgrading and downgrading cloud plan.
     *
     * @param  $invoice
     * @param  $cloud
     *
     * @throws
     */
    private function cloudUpDownGradeOps($cloud, $invoice)
    {
        $oldLicense = \Session::get('upgradeOldLicense');
        $installationPath = \Session::get('upgradeInstallationPath');
        $productId = \Session::get('upgradeProductId');
        $licenseCode = \Session::get('upgradeSerialKey');
        $this->doTheDeed($invoice, false);
        $cloud->doTheProductUpgradeDowngrade($licenseCode, $installationPath, $productId, $oldLicense);
        $this->perfromUpdateSubscription($invoice);
    }

    /**
     *  This function performs agent alteration for cloud plan.
     *
     * @param  $invoice
     * @param  $cloud
     *
     * @throws
     */
    private function cloudAgentAlterationOps($cloud, $invoice)
    {
        $subId = \Session::get('AgentAlteration'); // use if needed in the future
        $newAgents = \Session::get('newAgents');
        $orderId = \Session::get('orderId');
        $installationPath = \Session::get('installation_path');
        $productId = \Session::get('product_id');
        $oldLicense = \Session::get('oldLicense');
        $cloud->doTheAgentAltering($newAgents, $oldLicense, $orderId, $installationPath, $productId);
        $this->perfromUpdateSubscription($invoice);
    }

    /**
     *  This function performs agent alteration for cloud renewal plan.
     *
     * @param  $invoice
     * @param  $cloud
     *
     * @throws
     */
    private function cloudAgentAlterationRenew($cloud, $invoice)
    {
        $newAgents = \Session::get('newAgentsRenew');
        $orderId = \Session::get('orderIdRenew');
        $installationPath = \Session::get('installation_pathRenew');
        $productId = \Session::get('product_idRenew');
        $oldLicense = \Session::get('oldLicenseRenew');
        $cloud->doTheAgentAltering($newAgents, $oldLicense, $orderId, $installationPath, $productId);
        $this->perfromUpdateSubscription($invoice);
    }

    //update the subscription if needed
    public function perfromUpdateSubscription($invoice)
    {
        $orderId = OrderInvoiceRelation::where('invoice_id', $invoice->id)->latest()->value('order_id');
        $term_order_id = \DB::table('terminated_order_upgrade')->where('upgraded_order_id', $orderId)->value('terminated_order_id');
        $terminatedOrder = Order::find($term_order_id);
        if ($terminatedOrder) {
            $oldSubscription = Subscription::where('order_id', $terminatedOrder->id)->first();
            if ($terminatedOrder->order_status == 'Terminated' && $oldSubscription->subscribe_id != '' && $oldSubscription->subscribe_id != null) {
                $newSub = Subscription::where('order_id', $orderId)->update(['subscribe_id' => $oldSubscription->subscribe_id, 'is_subscribed' => $oldSubscription->is_subscribed, 'autoRenew_status' => $oldSubscription->autoRenew_status,
                    'rzp_subscription' => $oldSubscription->rzp_subscription]);
                $this->updateSubscriptionPriceIfNeeded($orderId, $invoice); //Check and update the subscription price if necessary
            } elseif ($terminatedOrder->order_status == 'Terminated' && $oldSubscription->is_subscribed == '1') {
                $newSub = Subscription::where('order_id', $orderId)->update(['is_subscribed' => $oldSubscription->is_subscribed, 'autoRenew_status' => $oldSubscription->autoRenew_status,
                    'rzp_subscription' => $oldSubscription->rzp_subscription]);
            }
        } else {
            $this->updateSubscriptionPriceIfNeeded($orderId, $invoice);
        }
    }
}
