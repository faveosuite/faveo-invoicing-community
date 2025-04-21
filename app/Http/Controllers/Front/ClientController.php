<?php

namespace App\Http\Controllers\Front;

use App\ApiKey;
use App\Auto_renewal;
use App\Http\Controllers\Github\GithubApiController;
use App\Http\Controllers\License\LicensePermissionsController;
use App\Http\Controllers\Order\RenewController;
use App\Model\Common\StatusSetting;
use App\Model\Github\Github;
use App\Model\Order\Invoice;
use App\Model\Order\InvoiceItem;
use App\Model\Order\Order;
use App\Model\Order\OrderInvoiceRelation;
use App\Model\Order\Payment;
use App\Model\Payment\Currency;
use App\Model\Payment\Plan;
use App\Model\Payment\PlanPrice;
use App\Model\Product\Product;
use App\Model\Product\ProductUpload;
use App\Model\Product\Subscription;
use App\Payment_log;
use App\Plugins\Stripe\Controllers\SettingsController;
use App\User;
use Exception;
use GrahamCampbell\Markdown\Facades\Markdown;
use Illuminate\Http\Request;
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

    public function enableAutorenewalStatus(Request $request)
    {
        try {
            $amount = 1;
            $currency = getCurrencyForClient(\Auth::user()->country);
            $orderid = $request->get('order_id');
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

                $response = ['type' => 'success', 'message' => 'Your Card details are updated successfully.'];

                return ['type' => 'success', 'message' => 'Your Card details are updated successfully.'];
            }
        } catch(\Exception $ex) {
            $result = $ex->getMessage();
            $mail = new \App\Http\Controllers\Common\PhpMailController();
            $mail->payment_log(\Auth::user()->email, 'stripe', 'failed', Order::where('id', $orderid)->value('number'), $result, $amount, 'Payment method updated');
            $errorMessage = 'Something went wrong. Try with a different payment method.';

            return response()->json(['error' => $errorMessage], 500);
        }
    }

    public function disableAutorenewalStatus(Request $request)
    {
        try {
            $orderid = $request->get('order_id');
            $userid = Subscription::where('order_id', $orderid)->value('user_id');
            $user = User::find($userid);
            $subscription = Subscription::where('order_id', $orderid)->first();
            if ($subscription->rzp_subscription && $subscription->is_subscribed && $subscription->subscribe_id) {
                $rzp_key = ApiKey::where('id', 1)->value('rzp_key');
                $rzp_secret = ApiKey::where('id', 1)->value('rzp_secret');
                $api = new Api($rzp_key, $rzp_secret);
                $pause = $api->subscription->fetch($subscription->subscribe_id)->cancel();
                Subscription::where('order_id', $orderid)->update(['is_subscribed' => '0', 'rzp_subscription' => '0']);
            } elseif ($subscription->autoRenew_status && $subscription->is_subscribed && $subscription->subscribe_id) {
                $stripeSecretKey = ApiKey::pluck('stripe_secret')->first();
                $stripe = new \Stripe\StripeClient($stripeSecretKey);
                \Stripe\Stripe::setApiKey($stripeSecretKey);
                $pause = $stripe->subscriptions->cancel($subscription->subscribe_id, []);
                Subscription::where('order_id', $orderid)->update(['is_subscribed' => '0', 'autoRenew_status' => '0']);
            } else {
                Subscription::where('order_id', $orderid)->update(['is_subscribed' => '0', 'autoRenew_status' => '0', 'rzp_subscription' => '0']);
            }

            $response = ['type' => 'success', 'message' => 'Auto subscription Disabled successfully'];

            return response()->json($response);
        } catch(\Exception $ex) {
            $result = $ex->getMessage();

            return response()->json(compact('result'), 500);
        }
    }

    public function enableRzpStatus(Request $request)
    {
        try {
            $currency = getCurrencyForClient(\Auth::user()->country);
            $amount = currencyFormat('1', $currency);
            $orderid = $request->route('orderid');
            $subscription = Subscription::where('order_id', $orderid)->first();
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

            return redirect()->back()->with('success', 'Your card details are updated successfully and the amount will be automatically reversed within a week');
        } catch(\Exception $ex) {
            $result = $ex->getMessage();
            $mail = new \App\Http\Controllers\Common\PhpMailController();
            $mail->payment_log(\Auth::user()->email, 'stripe', 'failed', Order::where('id', $orderid)->value('number'), $result, $amount, 'Payment method updated');

            return redirect()->back()->with('fails', 'Your Payment was declined. '.$ex->getMessage().'. Please try with another card or gateway');
        }
    }

    public function autoRenewbyid()
    {
        try {
            $id = request()->route('id');
            $order_id = \DB::table('order_invoice_relations')->where('invoice_id', $id)->value('order_id');
            $sub = Subscription::where('order_id', $order_id)->first();
            $planid = $sub->plan_id;
            $plan = Plan::find($planid);
            $planDetails = userCurrencyAndPrice($sub->user_id, $plan);
            $cost = $planDetails['plan']->renew_price;
            $currency = $planDetails['currency'];
            $controller = new RenewController();
            $items = InvoiceItem::where('invoice_id', $id)->first();
            $invoiceid = $items->invoice_id;
            // $this->setSession($id, $planid);

            return redirect('paynow/'.$id);
        } catch(\Exception $ex) {
            echo $ex->getMessage();
        }
    }

    public function invoices(Request $request)
    {
        try {
            return view('themes.default1.front.clients.invoice', compact('request'));
        } catch (Exception $ex) {
            return redirect()->back()->with('fails', $ex->getMessage());
        }
    }

    public function getInvoices(Request $request)
    {
        $status = $request->input('status');
        $invoices = Invoice::leftJoin('order_invoice_relations', 'invoices.id', '=', 'order_invoice_relations.invoice_id')
        ->leftJoin('orders', 'order_invoice_relations.order_id', '=', 'orders.id')
        ->select('orders.number')
        ->select('invoices.id', 'invoices.user_id', 'invoices.date', 'invoices.number', 'invoices.grand_total', 'order_invoice_relations.order_id as orderNo', 'invoices.is_renewed', 'invoices.status', 'invoices.currency')
        ->groupBy('invoices.number')
        ->where('invoices.user_id', '=', \Auth::user()->id);

        if ($status == 'pending') {
            $invoices->where('invoices.status', '=', 'pending');
        }

        return \DataTables::of($invoices)
                    ->orderColumn('number', '-invoices.date $1')
                    ->orderColumn('orderNo', '-invoices.date $1')
                    ->orderColumn('date', '-invoices.date $1')
                    ->orderColumn('total', '-invoices.date $1')
                    ->orderColumn('paid', '-invoices.date $1')
                    ->orderColumn('balance', '-invoices.date $1')
                    ->orderColumn('status', '-invoices.date $1')
                    ->orderColumn('date', '-invoices.date $1')

                    ->addColumn('number', function ($model) {
                        if ($model->is_renewed) {
                            return '<a href='.url('my-invoice/'.$model->id).'>'.$model->number.'</a>&nbsp;'.getStatusLabel('renewed', 'badge');
                        } else {
                            return '<a href='.url('my-invoice/'.$model->id).'>'.$model->number.'</a>';
                        }
                    })
                        ->addColumn('orderNo', function ($model) {
                            if ($model->is_renewed) {
                                $order = Order::find($model->order_id);
                                if ($order) {
                                    return $order->first()->getOrderLink($model->order_id, 'my-order');
                                } else {
                                    return '--';
                                }
                            } else {
                                $allOrders = $model->order()->select('id', 'number')->get();
                                $orderLinks = []; // Using an array to store links

                                foreach ($allOrders as $order) {
                                    $orderLinks[] = $order->getOrderLink($order->id, 'my-order');
                                }

                                $orderArray = implode(', ', $orderLinks); // Joining the links into a single string

                                return $orderArray;
                            }
                        })
                    ->addColumn('date', function ($model) {
                        return getDateHtml($model->date);
                    })
                    ->addColumn('total', function ($model) {
                        return  currencyFormat($model->grand_total, $code = $model->currency);
                    })
                    ->addColumn('paid', function ($model) {
                        $payment = \App\Model\Order\Payment::where('invoice_id', $model->id)->select('amount')->get();
                        $c = count($payment);
                        $sum = 0;

                        for ($i = 0; $i <= $c - 1; $i++) {
                            $sum = $sum + $payment[$i]->amount;
                        }

                        return currencyFormat($sum, $code = $model->currency);
                    })
                     ->addColumn('balance', function ($model) {
                         $payment = \App\Model\Order\Payment::where('invoice_id', $model->id)->select('amount')->get();
                         $c = count($payment);
                         $sum = 0;

                         for ($i = 0; $i <= $c - 1; $i++) {
                             $sum = $sum + $payment[$i]->amount;
                         }
                         $pendingAmount = $model->grand_total - $sum;

                         if ($pendingAmount < 0) {
                             $pendingAmount = 0;
                         }

                         return currencyFormat($pendingAmount, $code = $model->currency);
                     })
                     ->addColumn('status', function ($model) {
                         return  getStatusLabel($model->status, 'badge');
                     })
                    ->addColumn('Action', function ($model) {
                        $status = $model->status;
                        $deleteButton = '';
                        $payNowButton = '';
                        $payment = '';
                        $viewButton = '<a href="'.url('my-invoice/'.$model->id).'" class="btn btn-light-scale-2 btn-sm text-dark" id="iconStyle" data-toggle="tooltip" data-placement="top" title="Click here to view"><i class="fa fa-eye"></i></a>';

                        if ($status != 'Success' && $model->grand_total > 0) {
                            $payNowButton = '<a href="'.url('paynow/'.$model->id).'" class="btn btn-light-scale-2 btn-sm text-dark" id="iconStyle" data-toggle="tooltip" data-placement="top" title="Click here to pay"><i class="fa fa-credit-card"></i></a>';

                            if (! $model->orderRelation()->exists()) {
                                $deleteButton = '<a class="btn btn-light-scale-2 btn-sm text-dark delete-btn" id="iconStyle" data-id="'.$model->id.'" data-toggle="tooltip" data-placement="top" title="Click here to delete"><i class="fa fa-trash"></i></a>';
                            }

                            return $payNowButton.' '.$deleteButton.' '.$viewButton;
                        }

                        return $viewButton.$payment;
                    })

                     ->filterColumn('number', function ($query, $keyword) {
                         $sql = 'invoices.number like ?';
                         $query->whereRaw($sql, ["%{$keyword}%"]);
                     })
                    ->filterColumn('status', function ($query, $keyword) {
                        if ($keyword == 'Paid' || $keyword == 'paid') {
                            $sql = 'status like ?';
                            $sql2 = 'success';
                            $query->whereRaw($sql, ["%{$sql2}%"]);
                        } elseif ($keyword == 'Unpaid' || $keyword == 'unpaid') {
                            $sql = 'status like ?';
                            $sql2 = 'pending';
                            $query->whereRaw($sql, ["%{$sql2}%"]);
                        } elseif ($keyword == 'Partiallypaid' || $keyword == 'Partially' || $keyword == 'partially') {
                            $sql = 'status like ?';
                            $sql2 = 'partially paid';
                            $query->whereRaw($sql, ["%{$sql2}%"]);
                        }
                    })
                    ->filterColumn('orderNo', function ($query, $keyword) {
                        $sql = 'orders.number like ?';
                        $query->whereRaw($sql, ["%{$keyword}%"]);
                    })

                    ->rawColumns(['number', 'orderNo', 'date', 'total', 'status', 'Action'])
                    // ->orderColumns('number', 'created_at', 'total')
                    ->make(true);
    }

    public function getInvoice($id)
    {
        try {
            $invoice = $this->invoice->find($id);
            if (! $invoice) {
                throw new \Exception('Invoice not found.');
            }
            $payments = $invoice->payment;
            $user = \Auth::user();
            if ($invoice->user_id != $user->id) {
                throw new \Exception('Cannot view invoice. Invalid modification of data.');
            }
            $items = $invoice->invoiceItem()->get();
            $order = $this->order->getOrderLink($invoice->orderRelation()->value('order_id'), 'my-order');
            $currency = getCurrencyForClient($user->country);
            $symbol = Currency::where('code', $currency)->value('symbol');

            return view('themes.default1.front.clients.show-invoice', compact('invoice', 'items', 'user', 'currency', 'symbol', 'order', 'payments'));
        } catch (Exception $ex) {
            return redirect()->route('my-invoices')->with('fails', $ex->getMessage());
        }
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
            $searchValue = $request->input('search.value');
            $invoice_id = Invoice::where('number', $invoiceid)->pluck('id')->first();
            $order = Order::where('invoice_id', '=', $invoice_id)->first();
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
            echo $ex->getMessage();
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
            $countVersions = 10; //because we are taking only the first 10 versions
            $link = array_slice($link, 0, 10, true);
            $order = Order::where('invoice_id', '=', $invoiceid)->first();
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
//                                $markdown = Markdown::convertToHtml(ucfirst($link['body']));
                                $markdown = $link['body'];

                                return $markdown;
                            })
                            ->addColumn('file', function ($link) use ($countExpiry, $countVersions, $invoiceid, $productid) {
                                $order = Order::where('invoice_id', '=', $invoiceid)->first();
                                $order_id = $order->id;
                                $orderEndDate = Subscription::select('update_ends_at')
                                ->where('product_id', $productid)->where('order_id', $order_id)->first();
                                if ($orderEndDate) {
                                    $actionButton = $this->getActionButton($countExpiry, $countVersions, $link, $orderEndDate, $productid);

                                    return $actionButton;
                                } elseif (! $orderEndDate) {
                                    $link = $this->github_api->getCurl1($link['zipball_url']);

                                    return '<p><a href='.$link['header']['Location']
                                    ." class='btn btn-sm btn-primary'>Download  </a>"
                                            .'&nbsp;
                                   </p>';
                                }
                            })
                            ->rawColumns(['version', 'name', 'description', 'file'])
                            ->make(true);
        } catch (Exception $ex) {
            echo $ex->getMessage();
        }
    }

    /*
     * Show all the orders for User
     */

    public function getOrders(Request $request)
    {
        try {
            $updated_ends_at = $request->input('updated_ends_at');
            $orders = $this->getClientPanelOrdersData();
            if ($updated_ends_at == 'expired') {
                $orders = $this->getClientPanelOrdersData()->where('update_ends_at', '<', now());
            }

            return \DataTables::of($orders)
                        ->orderColumn('products.name', '-orders.id $1')
                        ->orderColumn('date', '-orders.id $1')
                        ->orderColumn('orders.number', '-orders.id $1')
                        ->orderColumn('agents', '-orders.id $1')
                        ->orderColumn('expiry', '-orders.id $1')

                            ->addColumn('id', function ($model) {
                                return $model->id;
                            })
                            ->addColumn('date', function ($model) {
                                return getDateHtml($model->date);
                            })
                            ->addColumn('product_name', function ($model) {
                                return $model->product_name;
                            })
                            ->addColumn('number', function ($model) {
                                if ($model->order_status != 'Terminated') {
                                    return '<a href='.url('my-order/'.$model->id).'>'.$model->number.'</a>';
                                } else {
                                    $badge = 'badge';

                                    return '<a href='.url('my-order/'.$model->id).'>'.$model->number.'</a>'.'&nbsp;<span class="'.$badge.' '.$badge.'-danger"  <label data-toggle="tooltip" style="font-weight:500;" data-placement="top" title="Order has been Terminated">

                         </label>
            Terminated</span>';
                                }
                            })
                            ->addColumn('agents', function ($model) {
                                $license = substr($model->serial_key, 12, 16);
                                if ($license == '0000') {
                                    return 'Unlimited';
                                }

                                return intval($license, 10);
                            })

                            ->addColumn('expiry', function ($model) {
                                return getExpiryLabel($model->update_ends_at, 'badge');
                            })

                            ->addColumn('Action', function ($model) {
                                if ($model->order_status == 'Terminated') {
                                    return '<a href='.url('my-order/'.$model->id)." 
                                class='btn btn-light-scale-2 btn-sm text-dark' style='margin-right:5px;'>
                                <i class='fa fa-eye' data-toggle='tooltip' data-placement='top' title='Click here to view'></i></a>";
                                }
                                $plan = Plan::where('product', $model->product_id)->value('id');
                                $whatIsSub = Subscription::where('order_id', $model->id)->value('plan_id');
                                $planName = Plan::where('id', $whatIsSub)->value('name');
                                $price = PlanPrice::where('plan_id', $plan)->where('currency', \Auth::user()->currency)->value('renew_price');
                                $order_cont = new \App\Http\Controllers\Order\OrderController();
                                $status = $order_cont->checkInvoiceStatusByOrderId($model->id);
                                $url = '';
                                $deleteCloud = '';
                                $listUrl = '';
                                if ($status == 'success' && $model->price != '0' && $model->type == '4') {
                                    $deleteCloud = $this->getCloudDeletePopup($model, $model->product_id);
                                    $listUrl = $this->getPopup($model, $model->product_id);
                                } elseif ($status == 'success' && $model->price == '0' && $model->type != '4') {
                                    $listUrl = $this->getPopup($model, $model->product_id);
                                }
                                if (! in_array($model->product_id, cloudPopupProducts())) {
                                    $listUrl = $this->getPopup($model, $model->product_id);
                                }
                                $deleteCloud = $this->getCloudDeletePopup($model, $model->product_id);

                                $agents = substr($model->serial_key, 12, 16);
                                if ($agents == '0000') {
                                    $agents = 'Unlimited';
                                } else {
                                    $agents = intval($agents, 10);
                                }

                                $url = $this->renewPopup($model->sub_id, $model->product_id, $agents, $planName);

                                $changeDomain = $this->changeDomain($model, $model->product_id); // Need to add this if the client requirement intensifies.

                                return '<a href='.url('my-order/'.$model->id)." 
                                class='btn btn-light-scale-2 btn-sm text-dark' style='margin-right:5px;'>
                                <i class='fa fa-eye' data-toggle='tooltip' data-placement='top' title='Click here to view'></i>&nbsp; $listUrl $url $deleteCloud</a>";
                            })
                            ->filterColumn('product_name', function ($query, $keyword) {
                                $sql = 'product.name like ?';
                                $query->whereRaw($sql, ["%{$keyword}%"]);
                            })
                             ->filterColumn('number', function ($query, $keyword) {
                                 $sql = 'orders.number like ?';
                                 $query->whereRaw($sql, ["%{$keyword}%"]);
                             })
                            ->rawColumns(['id', 'product_name', 'date', 'number', 'agents', 'expiry', 'Action'])
                            ->make(true);
        } catch (Exception $ex) {
            app('log')->error($ex->getMessage());
            echo $ex->getMessage();
        }
    }

    public function getClientPanelOrdersData()
    {
        return Order::leftJoin('products', 'products.id', '=', 'orders.product')
            ->leftJoin('subscriptions', 'orders.id', '=', 'subscriptions.order_id')
            ->leftJoin('invoices', 'orders.invoice_id', 'invoices.id')
            ->select('products.name as product_name', 'products.github_owner', 'products.github_repository', 'products.type', 'products.id as product_id', 'orders.id', 'orders.number', 'orders.client', 'subscriptions.id as sub_id', 'subscriptions.version', 'subscriptions.update_ends_at', 'products.name', 'orders.client', 'invoices.id as invoice_id', 'invoices.number as invoice_number', 'orders.created_at as date', 'orders.price_override as price', 'orders.serial_key', 'orders.order_status')
            ->where('orders.client', \Auth::user()->id);
    }

    public function profile()
    {
        try {
            $user = $this->user->where('id', \Auth::user()->id)->first();
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
            $state = getStateByCode($user->state);
            $states = findStateByRegionId($user->country);
            $bussinesses = \App\Model\Common\Bussiness::pluck('name', 'short')->toArray();
            $selectedIndustry = \App\Model\Common\Bussiness::where('name', $user->bussiness)
            ->pluck('name', 'short')->toArray();
            $selectedCompany = \DB::table('company_types')->where('name', $user->company_type)
            ->pluck('name', 'short')->toArray();
            $selectedCompanySize = \DB::table('company_sizes')->where('short', $user->company_size)
            ->pluck('name', 'short')->toArray();

            $selectedCountry = \DB::table('countries')->where('country_code_char2', $user->country)
            ->value('nicename');

            return view(
                'themes.default1.front.clients.profile',
                compact('user', 'timezones', 'state', 'states', 'bussinesses', 'is2faEnabled', 'dateSinceEnabled', 'selectedIndustry', 'selectedCompany', 'selectedCompanySize', 'selectedCountry')
            );
        } catch (Exception $ex) {
            return redirect()->back()->with('fails', $ex->getMessage());
        }
    }

    public function generateMerchantRandomString($length = 10)
    {
        $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $charactersLength = strlen($characters);
        $randomString = '';
        for ($i = 0; $i < $length; $i++) {
            $randomString .= $characters[rand(0, $charactersLength - 1)];
        }

        return $randomString;
    }

    public function getOrder($id)
    {
        try {
            $user = \Auth::user();
            $order = $this->order->findOrFail($id);
            if ($order->client != $user->id) {
                throw new \Exception('Cannot view order. Invalid modification of data.');
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
                // $versionLabel = getVersionAndLabel($subscription->version, $order->product, 'badge');
            }
            $installationDetails = [];
            $licenseStatus = StatusSetting::pluck('license_status')->first();
            if ($licenseStatus == 1) {
                // $cont = new \App\Http\Controllers\License\LicenseController();
                // $installationDetails = $cont->searchInstallationPath($order->serial_key, $order->product);
            }
            $product = $order->product()->first();
            $price = $product->price()->first();
            $licenseStatus = StatusSetting::pluck('license_status')->first();
            $allowDomainStatus = StatusSetting::pluck('domain_check')->first();

            $licenseStatus = StatusSetting::pluck('license_status')->first();
            $installationDetails = [];

            $cont = new \App\Http\Controllers\License\LicenseController();
            $installationDetails = $cont->searchInstallationPath($order->serial_key, $order->product);

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
            $rzp_key = ApiKey::where('id', 1)->value('rzp_key');
            $rzp_secret = ApiKey::where('id', 1)->value('rzp_secret');
            $apilayer_key = ApiKey::where('id', 1)->value('apilayer_key');
            $stripe_key = ApiKey::where('id', 1)->value('stripe_key');
            $api = new Api($rzp_key, $rzp_secret);
            $displayCurrency = getCurrencyForClient(\Auth::user()->country);
            $symbol = getCurrencyForClient(\Auth::user()->country);

            if ($symbol == 'INR') {
                $exchangeRate = '';
                $orderData = [
                    'receipt' => '3456',
                    'amount' => round(1.00 * 100), // 2000 rupees in paise

                    'currency' => getCurrencyForClient(\Auth::user()->country),
                    'payment_capture' => 0, // auto capture
                ];
            } else {
                $exchangeRate = '';
                $orderData = [
                    'receipt' => '3456',
                    'amount' => round(1.00 * 100), // 2000 rupees in paise

                    'currency' => getCurrencyForClient(\Auth::user()->country),
                    'payment_capture' => 0, // auto capture

                ];
            }
            $razorpayOrder = ($rzp_key && $rzp_secret) ? $api->order->create($orderData) : '';

            $razorpayOrderId = ($razorpayOrder != null) ? $razorpayOrder['id'] : '';

            $_SESSION['razorpay_order_id'] = $razorpayOrderId;
            $displayAmount = $amount = $orderData['amount'];

            $data = [
                'key' => $rzp_key,
                'name' => 'Faveo Helpdesk',
                'currency' => 'INR',
                'prefill' => [
                    'contact' => \Auth::user()->mobile_code.\Auth::user()->mobile,
                    'email' => \Auth::user()->email,
                ],
                'description' => 'Order for Invoice No'.-$invoice->number,
                'notes' => [
                    'First Name' => \Auth::user()->first_name,
                    'Last Name' => \Auth::user()->last_name,
                    'Company Name' => \Auth::user()->company,
                    'Address' => \Auth::user()->address,
                    'Email' => \Auth::user()->email,
                    'Country' => \Auth::user()->country,
                    'State' => \Auth::user()->state,
                    'City' => \Auth::user()->town,
                    'Zip' => \Auth::user()->zip,
                    'Currency' => \Auth::user()->currency,
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
            $json = json_encode($data);
            $currency = \Auth::user()->currency;
            $gateways = \App\Http\Controllers\Common\SettingsController::checkPaymentGateway(getCurrencyForClient(\Auth::user()->country));
            // $processingFee = \DB::table(strtolower($gateways))->where('currencies',\Auth::user()->currency)->value('processing_fee');
            $planid = \App\Model\Payment\Plan::where('product', $product->id)->value('id');
            $price = $order->price_override;

            $installation_path = \App\Model\Order\InstallationDetail::where('order_id', $id)
                ->where('installation_path', '!=', cloudCentralDomain())->latest()->value('installation_path');
            $latestAgents = ltrim(substr($order->serial_key, 12), '0');
            $terminatedOrderId = \DB::table('terminated_order_upgrade')->where('upgraded_order_id', $order->id)->value('terminated_order_id');
            $terminatedOrderNumber = \App\Model\Order\Order::where('id', $terminatedOrderId)->value('number');
            if ($statusAutorenewal == 1 && $payment_log == null && ! empty($terminatedOrderId)) {
                $payment_log = \App\Payment_log::where('order', $terminatedOrderNumber)
                    ->where('payment_type', 'Payment method updated')
                    ->orderBy('id', 'desc')
                    ->first();
                if (! $payment_log) {
                    $payment_log = \App\Payment_log::where('order', $terminatedOrderNumber)
                        ->orderBy('id', 'desc')
                        ->first();
                }
            }

            $plans = Plan::join('products', 'plans.product', '=', 'products.id')
                ->leftJoin('plan_prices', 'plans.id', '=', 'plan_prices.plan_id')
                ->where('plans.product', '!=', $product->id)
                ->where('products.type', 4)
                ->where('products.can_modify_agent', 1)
                ->where('plan_prices.renew_price', '!=', '0')
                ->pluck('plans.name', 'plans.id')
                ->toArray();
            $planIds = array_keys($plans);
            $countryids = \App\Model\Common\Country::where('country_code_char2', \Auth::user()->country)->first();

            $renewalPrices = \App\Model\Payment\PlanPrice::whereIn('plan_id', $planIds)
                ->where('country_id', $countryids->country_id)
                ->where('currency', getCurrencyForClient(\Auth::user()->country))
                ->latest()
                ->pluck('renew_price', 'plan_id')
                ->toArray();

            if (empty($renewalPrices)) {
                $renewalPrices = \App\Model\Payment\PlanPrice::whereIn('plan_id', $planIds)
                    ->where('country_id', 0)
                    ->where('currency', getCurrencyForClient(\Auth::user()->country))
                    ->latest()
                    ->pluck('renew_price', 'plan_id')
                    ->toArray();
            }

            foreach ($plans as $planId => $planName) {
                if (isset($renewalPrices[$planId])) {
                    if (in_array($product->id, cloudPopupProducts())) {
                        $plans[$planId] .= ' (Plan price-per agent: '.currencyFormat($renewalPrices[$planId], getCurrencyForClient(\Auth::user()->country), true).')';
                    }
                }
            }
            // Add more cloud IDs until we have a generic way to differentiate
            if (in_array($product->id, cloudPopupProducts())) {
                $plans = array_filter($plans, function ($value) {
                    return stripos($value, 'free') === false;
                });
            }

            $planIdOld = \App\Model\Product\Subscription::where('order_id', $id)->value('plan_id');
            $planNameReal = \App\Model\Payment\Plan::where('id', $planIdOld)->value('name');

            return view(
                'themes.default1.front.clients.show-order',
                compact('invoice', 'order', 'user', 'product', 'subscription', 'licenseStatus', 'installationDetails', 'allowDomainStatus', 'date',
                    'licdate', 'versionLabel', 'installationDetails', 'id', 'statusAutorenewal', 'status', 'payment_log', 'recentPayment', 'stripe_key', 'json', 'gateways',
                    'price', 'installation_path', 'latestAgents', 'terminatedOrderId', 'terminatedOrderNumber', 'payment_log', 'plans', 'planNameReal'
                )
            );
        } catch (Exception $ex) {
            return redirect()->back()->with('fails', $ex->getMessage());
        }
    }

    public function getPaymentByOrderId($orderid, $userid)
    {
        try {
            // dd($orderid);
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

    public function getPaymentByOrderIdClient($orderid, $userid)
    {
        try {
            $order = $this->order->where('id', $orderid)->where('client', $userid)->first();
            // dd($order);
            $relation = $order->invoiceRelation()->pluck('invoice_id')->toArray();
            if (count($relation) > 0) {
                $invoices = $relation;
            } else {
                $invoices = $order->invoice()->pluck('id')->toArray();
            }
            // $payments = Payment::leftJoin('invoices', 'payments.invoice_id', '=', 'invoices.id')
            // ->select('payments.id', 'payments.invoice_id', 'payments.user_id', 'payments.payment_method', 'payments.payment_status', 'payments.created_at', 'payments.amount', 'invoices.id as invoice_id', 'invoices.number as invoice_number')
            // ->where('invoices.id', $invoices)
            // ->get();
            // $payments = $this->payment->whereIn('invoice_id', $invoices)->with('invoice:id,number')
            //         ->select('id', 'invoice_id', 'user_id', 'amount', 'payment_method', 'payment_status', 'created_at');

            $payments = $this->payment::query()
                    ->with(['invoice' => function ($query) {
                        $query->select('id', 'number');
                    }])->whereIn('invoice_id', $invoices);

            return \DataTables::of($payments)
                        ->orderColumn('number', '-created_at $1')
                        ->orderColumn('total', '-created_at $1')
                        ->orderColumn('payment_method', '-created_at $1')
                        ->orderColumn('payment_status', '-created_at $1')
                        ->orderColumn('created_at', '-created_at $1')

                            ->addColumn('number', function ($payments) {
                                return '<a href='.url('my-invoice/'.$payments->invoice()->first()->id).'>'.$payments->invoice()->first()->number.'</a>';
                            })
                              ->addColumn('total', function ($payments) {
                                  // return $payments->amount;
                                  return currencyFormat($payments->amount, $code = $payments->currency);
                              })
                               ->addColumn('payment_method', function ($payments) {
                                   return $payments->payment_method;
                               })
                                ->addColumn('payment_status', function ($payments) {
                                    return $payments->payment_status;
                                })
                               ->addColumn('created_at', function ($payments) {
                                   return  getDateHtml($payments->created_at);
                               })

                            ->filterColumn('number', function ($query, $keyword) {
                                $sql = 'number like ?';
                                $query->whereRaw($sql, ["%{$keyword}%"]);
                            })

                            ->rawColumns(['number', 'total', 'payment_method', 'payment_status', 'created_at'])
                            ->make(true);
        } catch (Exception $ex) {
            return redirect()->back()->with('fails', $ex->getMessage());
        }
    }

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

        if ($this->canDeleteInvoice($invoice)) {
            $this->deleteInvoice($invoice);

            return response()->json(['message' => 'Invoice deleted successfully']);
        }

        return response()->json(['error' => 'Cannot delete invoice.'], 400);
    }

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
                $response = ['type' => 'success', 'message' => 'Your Card details are updated successfully.'];

                return response()->json($response);
            } else {
                $response = ['type' => 'fails', 'message' => 'Something went wrong.'];

                return response()->json(compact('response'), 500);
            }
        } catch(\Exception $ex) {
            $result = $ex->getMessage();
            $mail = new \App\Http\Controllers\Common\PhpMailController();
            $mail->payment_log(\Auth::user()->email, 'stripe', 'failed', Order::where('id', $orderid)->value('number'), $result, $amount, 'Payment method updated');
            $errorMessage = 'Something went wrong. Try with a different payment method.';

            return response()->json(['error' => $errorMessage], 500);
        }
    }
}
