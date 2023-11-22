<?php

namespace App\Http\Controllers\Front;

use App\Http\Controllers\Common\MailChimpController;
use App\Http\Controllers\Common\TemplateController;
use App\Http\Controllers\Tenancy\TenantController;
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
use App\Traits\TaxCalculation;
use App\User;
use Cart;
use Darryldecode\Cart\CartCondition;
use GuzzleHttp\Client;
use Illuminate\Http\Request;

class CheckoutController extends InfoController
{
    use TaxCalculation;

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

        // $mailchimp = new MailChimpController();
        // $this->mailchimp = $mailchimp;
    }

    /*
      * When Proceed to chekout button clicked first request comes here
     */
    public function checkoutForm(Request $request)
    {
        if (! \Auth::user()) {//If User is not Logged in then send him to login Page
            $url = $request->segments(); //The requested url (chekout).Save it in Session
            \Session::put('session-url', $url[0]);
            $content = Cart::getContent();
            $domain = $request->input('domain');
            if ($domain) {
                foreach ($domain as $key => $value) {
                    \Session::put('domain'.$key, $value); //Store all the domains Entered in Cart Page in Session
                }
            }
            \Session::put('content', $content);

            return redirect('login')->with('fails', 'Please login');
        }

        // if (\Cart::isEmpty()) {//During renewal when payment fails due to some reason
        //     $invoice = \Session::get('invoice');
        //     if ($invoice && \Session::has('fails')) {
        //         return redirect('paynow/'.$invoice->id)->with('fails', 'Payment cannot be processed. Please try the other gateway.');
        //     }
        // }

        $content = Cart::getContent();
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
            foreach (\Cart::getContent() as $item) {
                $price = $item->price;
                $quantity = $item->quantity;
                $domain = $item->attributes->domain;
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

            return view('themes.default1.front.checkout', compact('content', 'taxConditions', 'discountPrice', 'domain'));
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
                    $cart_currency = $item->attributes->currency; //Get the currency of Product in the cart
                    \Session::put('cart_currency', $cart_currency);
                    $currency = getCurrencyForClient(\Auth::user()->country) != $cart_currency ? getCurrencyForClient(\Auth::user()->country) : $cart_currency; //If User Currency and cart currency are different the currency es set to user currency.
                    if ($cart_currency != $currency) {
                        $id = $item->id;
                        Cart::remove($id);
                    }
                    $require_domain = $item->associatedModel->require_domain;
                    $require = [];
                    if ($require_domain) {
                        $require[$key] = $item->id;
                    }
                    $taxConditions = $this->calculateTax($item->id, \Auth::user()->state, \Auth::user()->country); //Calculate Tax Condition by passing ProductId
                    Cart::condition($taxConditions);
                    Cart::remove($item->id);

                    //Return array of Product Details,attributes and their conditions
                    $items[] = ['id' => $item->id, 'name' => $item->name, 'price' => $item->price,
                        'quantity' => $item->quantity, 'attributes' => ['currency' => $cart_currency, 'symbol' => $item->attributes->symbol, 'agents' => $item->attributes->agents, 'domain' => optional($item->attributes)->domain], 'associatedModel' => Product::find($item->id), 'conditions' => $taxConditions, ];
                }
                Cart::add($items);

                return $taxConditions;
            }
        } catch (\Exception $ex) {
            app('log')->error($ex->getMessage());

            return redirect()->back()->with('fails', $ex->getMessage());
        }
    }

    public function payNow($invoiceid)
    {
        try {
            $paid = 0;
            $invoice = $this->invoice->find($invoiceid);
            if ($invoice->user_id != \Auth::user()->id) {
                throw new \Exception('Cannot initiate payment. Invalid modification of data');
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

    public function postCheckout(Request $request)
    {
        $isTrue = 1;
        $cost = $request->input('cost');

        if (\Session::has('nothingLeft')) {
            $isTrue = \Session::get('nothingLeft');
        }

        if ($isTrue != 0) {
            if (\Cart::getTotal() > 0) {
                if (Cart::getSubTotal() != 0 || $cost > 0) {
                    $this->validate($request, [
                        'payment_gateway' => 'required',
                    ], [
                        'payment_gateway.required' => 'Please Select a Payment Gateway',
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
                $amount = intval(Cart::getSubTotal());
                if (\Session::has('nothingLeft')) {
                    $amount = \Session::get('nothingLeft');
                }
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
                        $this->doTheDeed($invoice);
                        \Session::forget('nothingLeft');
                    }
                    if (! empty($invoice->cloud_domain)) {
                        $orderNumber = Order::where('invoice_id', $invoice->id)->whereIn('product', [117, 119])->value('number');
                        (new TenantController(new Client, new FaveoCloud()))->createTenant(new Request(['orderNo' => $orderNumber, 'domain' => $invoice->cloud_domain]));
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
                        Invoice::where('id', $invoice->id)->update(['grand_total'=> ($invoice->grand_total + $invoice->billing_pay)]);
                    }
                    $payment->postRazorpayPayment($invoice);
                    $date = getDateHtml($invoice->date);
                    $product = $this->product($invoice->id);
                    $items = $invoice->invoiceItem()->get();

                    $url = '';
                    if ($control->checkRenew() || \Session::has('AgentAlteration')) {
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
                    if (\Session::has('agentIncreaseDate') || $control->checkRenew()) {
                        $control = new \App\Http\Controllers\Order\RenewController();
                        $control->successRenew($invoice, true);
                    }
                    if (! empty($invoice->cloud_domain)) {
                        $orderNumber = Order::where('invoice_id', $invoice->id)->whereIn('product', [117, 119])->value('number');
                        (new TenantController(new Client, new FaveoCloud()))->createTenant(new Request(['orderNo' => $orderNumber, 'domain' => $invoice->cloud_domain]));
                    }
                    $this->performCloudActions($invoice);
                    \Cart::clear();

                    return redirect('checkout')->with('Success', $url);
                }
            }
        } catch (\Exception $ex) {
            dd($ex);
            return redirect()->back()->with('fails', $ex->getMessage());
        }
    }

    private function getProcessingFee($paymentMethod, $currency)
    {
        try {
            if ($paymentMethod) {
                return $paymentMethod == 'razorpay' ? 0 : \DB::table(strtolower($paymentMethod))->where('currencies', $currency)->value('processing_fee');
            }
        } catch (\Exception $e) {
            throw new \Exception('Invalid modification of data');
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

    public function checkregularPaymentOrRenewal($invoiceid)
    {
        $paynow = false;

        if ($invoiceid) {
            if (Invoice::find($invoiceid)->user_id != \Auth::user()->id) {
                throw new \Exception('Invalid modification of data');
            }
            $paynow = true;
        }
        return $paynow;
    }

    public function checkoutAction($invoice, $agent = false)
    {
        try {
            //get elements from invoice
            $invoice_number = $invoice->number;
            $invoice_id = $invoice->id;

            foreach (\Cart::getConditionsByType('fee') as $value) {
                $invoice->processing_fee = $value->getValue();
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

    private function doTheDeed($invoice, $do = true)
    {
        Payment::where('user_id', \Auth::user()->id)->where('payment_method', 'Credit Balance')->latest()->update(['payment_status' => 'success']);

        $amt_to_credit = Payment::where('user_id', \Auth::user()->id)->where('payment_status', 'success')->where('payment_method', 'Credit Balance')->value('amt_to_credit');
        if ($amt_to_credit && $do) {
            $amt_to_credit = (int) $amt_to_credit - (int) $invoice->billing_pay;
            Payment::where('user_id', \Auth::user()->id)->where('payment_method', 'Credit Balance')->where('payment_status', 'success')->update(['amt_to_credit'=>$amt_to_credit]);
            User::where('id', \Auth::user()->id)->update(['billing_pay_balance'=>0]);
            $payment_id = \DB::table('payments')->where('user_id', \Auth::user()->id)->where('payment_status', 'success')->where('payment_method', 'Credit Balance')->value('id');
            $formattedValue = currencyFormat($invoice->billing_pay, $invoice->currency, true);
            $messageAdmin = 'The payment balance of '.$formattedValue.' has been utilized or adjusted with this invoice.'.
                ' You can view the details of the invoice '.
                '<a href="'.config('app.url').'/invoices/show?invoiceid='.$invoice->id.'">'.$invoice->number.'</a>.';

            $messageClient = 'The payment balance of '.$formattedValue.' has been utilized or adjusted with this invoice.'.
                ' You can view the details of the invoice '.
                '<a href="'.config('app.url').'/my-invoice/'.$invoice->id.'">'.$invoice->number.'</a>.';

            \DB::table('credit_activity')->insert(['payment_id'=>$payment_id, 'text'=>$messageAdmin, 'role'=>'admin', 'created_at'=>\Carbon\Carbon::now(), 'updated_at' => \Carbon\Carbon::now()]);
            \DB::table('credit_activity')->insert(['payment_id'=>$payment_id, 'text'=>$messageClient, 'role'=>'user', 'created_at'=>\Carbon\Carbon::now(), 'updated_at' => \Carbon\Carbon::now()]);
        }
    }

    private function performCloudActions($invoice)
    {
        $cloud = new \App\Http\Controllers\Tenancy\CloudExtraActivities(new Client, new FaveoCloud());

        if ($cloud->checkUpgradeDowngrade()) {
            $oldLicense = \Session::get('upgradeOldLicense');
            $installationPath = \Session::get('upgradeInstallationPath');
            $productId = \Session::get('upgradeProductId');
            $licenseCode = \Session::get('upgradeSerialKey');
            $this->doTheDeed($invoice, false);
            $cloud->doTheProductUpgradeDowngrade($licenseCode, $installationPath, $productId, $oldLicense);
        } elseif ($cloud->checkAgentAlteration()) {
            $subId = \Session::get('AgentAlteration'); // use if needed in the future
            $newAgents = \Session::get('newAgents');
            $orderId = \Session::get('orderId');
            $installationPath = \Session::get('installation_path');
            $productId = \Session::get('product_id');
            $oldLicense = \Session::get('oldLicense');
            $cloud->doTheAgentAltering($newAgents, $oldLicense, $orderId, $installationPath, $productId);
        } elseif (\Session::has('AgentAlterationRenew')) { // Added missing parentheses
            $newAgents = \Session::get('newAgentsRenew');
            $orderId = \Session::get('orderIdRenew');
            $installationPath = \Session::get('installation_pathRenew');
            $productId = \Session::get('product_idRenew');
            $oldLicense = \Session::get('oldLicenseRenew');
            $cloud->doTheAgentAltering($newAgents, $oldLicense, $orderId, $installationPath, $productId);
        }
    }
}
