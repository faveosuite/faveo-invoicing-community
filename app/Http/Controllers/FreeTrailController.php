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
use App\Model\Payment\Plan;
use App\Model\Payment\PlanPrice;
use App\Model\Payment\TaxOption;
use App\Model\Product\Product;
use App\Model\Product\Subscription;
use App\User;
use Auth;
use Crypt;
use DB;
use GuzzleHttp\Client;
use Illuminate\Http\Request;

class FreeTrailController extends Controller
{
    public $orderNo = null;

    public function __construct()
    {
        $this->middleware('auth');

        $this->invoice = new Invoice();

        $this->invoiceItem = new InvoiceItem();

        $this->order = new Order();

        $this->subscription = new Subscription();
    }

    /**
     * Get the auth user id
     * Check the first_time_login is not equal to zero in DB to correspaonding,if its not change into one.
     *
     * @return order
     */
    public function firstLoginAttempt(Request $request)
    {
        $this->validate($request, [
            'domain' => 'required|regex:/^[a-zA-Z0-9]+$/u',
        ], [
            'domain.regex' => 'Special characters are not allowed in domain name',
        ]);
        try {
            if (! Auth::check()) {
                return redirect('login')->back()->with('fails', \Lang::get('message.free-login'));
            }

            $userId = $request->get('id');
            if (Auth::user()->id == $userId) {
                $product_is = ($request->product == 'Helpdesk') ? 117 : 119;
                if (\DB::table('free_trial_allowed')->where('user_id', $userId)->where('product_id', $product_is)->count() >= 1) {
                    return ['status' => 'false', 'message' => trans('message.limit_is_up')];
                }

                DB::beginTransaction(); // Start a database transaction

                try {
                    $this->generateFreetrailInvoice();
                    $this->createFreetrailInvoiceItems($request->get('product'));
                    $serial_key = $this->executeFreetrailOrder();

                    $isSuccess = (new TenantController(new Client, new FaveoCloud()))->createTenant(new Request(['orderNo' => $this->orderNo, 'domain' => $request->domain]));

                    if ($isSuccess['status'] == 'false') {
                        (new LicenseController())->deActivateTheLicense($serial_key);

                        DB::rollback(); // Rollback the transaction

                        return $isSuccess;
                    }
                    \DB::table('free_trial_allowed')->insert([
                        'user_id' => $userId,
                        'product_id' => ($request->get('product') == 'Helpdesk' ? 117 : 119),
                        'domain' => $request->domain.'.faveocloud.com',
                    ]);
                    DB::commit(); // Commit the transaction

                    return $isSuccess;
                } catch (\Exception $ex) {
                    DB::rollback(); // Rollback the transaction
                    app('log')->error($ex->getMessage());
                    throw new \Exception('Can not Generate Free-trial Cloud instance');
                }
            }
        } catch (\Exception $ex) {
            app('log')->error($ex->getMessage());
            throw new \Exception('Can not Generate Free-trial Cloud instance');
        }
    }

    /**
     * Generate invoice from client panel for free trial.
     *
     * @throws \Exception
     */
    private function generateFreetrailInvoice()
    {
        try {
            $tax_rule = new TaxOption();
            $rule = $tax_rule->findOrFail(1);
            $rounding = $rule->rounding;
            $user_id = \Auth::user()->id;
            $grand_total = $rounding ? round(\Cart::getTotal()) : \Cart::getTotal();
            $number = rand(11111111, 99999999);
            $date = \Carbon\Carbon::now();
            $currency = \Session::has('cart_currency') ? \Session::get('cart_currency') : getCurrencyForClient(\Auth::user()->country);
            $invoice = $this->invoice->create(['user_id' => $user_id, 'number' => $number, 'date' => $date, 'grand_total' => $grand_total, 'status' => 'success',
                'currency' => $currency, ]);

            return $invoice;
        } catch (\Exception $ex) {
            app('log')->error($ex->getMessage());

            throw new \Exception('Can not Generate Invoice');
        }
    }

    /**
     * Generate invoice items for free trial.
     *
     * @throws \Exception
     */
    private function createFreetrailInvoiceItems($product_type)
    {
        try {
            if ($product_type == 'Helpdesk') {
                $product = Product::with(['planRelation' => function ($query) {
                    $query->where('name', 'LIKE', '%free%');
                }])->find(117);
            } else {
                $product = Product::with(['planRelation' => function ($query) {
                    $query->where('name', 'LIKE', '%free%');
                }])->find(119);
            }

            if ($product) {
                $plan_id = $product->planRelation()->pluck('id');
                $cart = \Cart::getContent();
                $userId = \Auth::user()->id;
                $invoice = $this->invoice->where('user_id', $userId)->latest()->first();
                $invoiceid = $invoice->id;
                $invoiceItem = $this->invoiceItem->create([
                    'invoice_id' => $invoiceid,
                    'product_name' => $product->name,
                    'regular_price' => planPrice::whereIn('plan_id', $plan_id)
                        ->where('currency', \Auth::user()->currency)->pluck('add_price'),
                    'quantity' => 1,
                    'tax_name' => 'null',
                    'tax_percentage' => $product->planRelation()->pluck('allow_tax'),
                    'subtotal' => 0,
                    'domain' => '',
                    'plan_id' => 0,
                    'agents' => planPrice::whereIn('plan_id', $plan_id)
                        ->where('currency', \Auth::user()->currency)->pluck('no_of_agents'),
                ]);

                return $invoiceItem;
            } else {
                throw new \Exception('Can not Find the Product');
            }
        } catch (\Exception $ex) {
            app('log')->error($ex->getMessage());

            throw new \Exception('Can not Generate Invoice items');
        }
    }

    /**
     * Generate Order from client panel for free trial.
     *
     * @throws \Exception
     */
    private function executeFreetrailOrder()
    {
        try {
            $order_status = 'executed';
            $userId = \Auth::user()->id;
            $invoice = $this->invoice->where('user_id', $userId)->latest()->first();
            $invoiceid = $invoice->id;
            $item = $this->invoiceItem->where('invoice_id', $invoiceid)->latest()->first();
            $user_id = $this->invoice->find($invoiceid)->user_id;
            $items = $this->getIfFreetrailItemPresent($item, $invoiceid, $user_id, $order_status);

            return $items;
        } catch (\Exception $ex) {
            app('log')->error($ex->getMessage());

            throw new \Exception('Can not Generate order');
        }
    }

    /**
     * Create order for free trial if the invoice items present in the DB.
     *
     * @throws \Exception
     */
    private function getIfFreetrailItemPresent($item, $invoiceid, $user_id, $order_status)
    {
        try {
            $product = Product::where('name', $item->product_name)->value('id');
            $version = Product::where('name', $item->product_name)->first()->version;
            $serial_key = $this->generateFreetrailSerialKey($product, 1); //Send Product Id and Agents to generate Serial Key
            $domain = $item->domain;
            //$plan_id = $this->plan($item->id);
            $plan_id = Plan::where('product', $product)->where('name', 'LIKE', '%free%')
                ->value('id');

            $order = $this->order->create([

                'invoice_id' => $invoiceid,
                'invoice_item_id' => $item->id,
                'client' => $user_id,
                'order_status' => $order_status,
                'serial_key' => Crypt::encrypt($serial_key),
                'product' => $product,
                'price_override' => $item->subtotal,
                'qty' => $item->quantity,
                'domain' => $domain,
                'number' => $this->generateFreetrailNumber(),
            ]);
            $this->orderNo = $order->number;
            $baseorder = new BaseOrderController();
            $baseorder->addOrderInvoiceRelation($invoiceid, $order->id);

            if ($plan_id) {
                $baseorder->addSubscription($order->id, $plan_id, $version, $product, $serial_key);
            }
            $mailchimpStatus = StatusSetting::pluck('mailchimp_status')->first();
            if ($mailchimpStatus) {
                $baseorder->addtoMailchimp($product, $user_id, $item);
            }

            return $serial_key;
        } catch (\Exception $ex) {
            app('log')->error($ex->getMessage());

            throw new \Exception('Can not Generate free trial order');
        }
    }

    /**
     * Generate serial key for free trial.
     *
     * @throws \Exception
     */
    private function generateFreetrailSerialKey(int $productid, $agents)
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
            app('log')->error($ex->getMessage());

            throw new \Exception('Can not Generate Free trail serialkey');
        }
    }

    private function generateFreetrailNumber()
    {
        return rand('10000000', '99999999');
    }
}
