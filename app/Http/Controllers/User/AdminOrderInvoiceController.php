<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Model\Order\InstallationDetail;
use App\Model\Order\Invoice;
use App\Model\Order\Order;
use Illuminate\Http\Request;
use Yajra\DataTables\DataTables;

class AdminOrderInvoiceController extends Controller
{
    public function getClientInvoice($id)
    {
        $client = $this->user->where('id', $id)->first();
        $invoices = Invoice::leftJoin('order_invoice_relations', 'invoices.id', '=', 'order_invoice_relations.invoice_id')
        ->leftJoin('orders', 'order_invoice_relations.order_id', '=', 'orders.id')
        ->select('invoices.id', 'invoices.user_id', 'invoices.date', 'invoices.number', 'invoices.grand_total', 'order_invoice_relations.order_id', 'invoices.is_renewed', 'invoices.status', 'invoices.currency', 'orders.number as order')
        ->groupBy('invoices.number')
        ->where('invoices.user_id', '=', $id);

        return DataTables::of($invoices)
         ->orderColumn('date', '-invoices.date $1')
         ->orderColumn('invoice_no', '-invoices.date $1')
         ->orderColumn('order_no', '-invoices.date $1')
         ->orderColumn('total', '-invoices.date $1')
         ->orderColumn('paid', '-invoices.date $1')
         ->orderColumn('balance', '-invoices.date $1')
         ->orderColumn('status', '-invoices.date $1')

                        ->addColumn('checkbox', function ($model) {
                            return "<input type='checkbox' class='invoice_checkbox' 
                            value=".$model->id.' name=select[] id=check>';
                        })
                        ->addColumn('date', function ($model) {
                            return getDateHtml($model->date);
                        })
                        ->addColumn('invoice_no', function ($model) {
                            $label = '<a href='.url('invoices/show?invoiceid='.$model->id).'>'.$model->number.'</a>';
                            if ($model->is_renewed) {
                                return $label.'&nbsp;'.getStatusLabel('renewed');
                            }

                            return $label;
                        })
                         ->addColumn('order_no', function ($model) {
                             if ($model->is_renewed) {
                                 $order = Order::find($model->order_id);
                                 if ($order) {
                                     return $order->first()->getOrderLink($model->order_id, 'orders');
                                 } else {
                                     return '--';
                                 }
                             } else {
                                 $allOrders = $model->order()->select('id', 'number')->get();
                                 $orderLinks = []; // Using an array to store links

                                 foreach ($allOrders as $order) {
                                     $orderLinks[] = $order->getOrderLink($order->id, 'orders');
                                 }

                                 $orderArray = implode(', ', $orderLinks); // Joining the links into a single string

                                 return $orderArray;
                             }
                         })
                        ->addColumn('total', function ($model) {
                            return currencyFormat($model->grand_total, $code = $model->currency);
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
                              return getStatusLabel($model->status);
                          })
                        ->addColumn('action', function ($model) {
                            $action = '';
                            $cont = new \App\Http\Controllers\Order\TaxRatesAndCodeExpiryController();
                            $check = $cont->checkExecution($model->id);
                            if ($check == false) {
                                $action = '<p><form id="execute-form" method="post" action='.url('order/execute?invoiceid='.$model->id).'>'.'<input type="hidden" name="_token" value='.\Session::token().'>'.'
                                <button type="submit" style="margin-top:-10px;" class="btn btn-sm btn-secondary btn-xs"'.tooltip('Execute Order').'<i class="fa fa-tasks" style="color:white;"></i></button></form></p>';

                                $action .= '<script>
                                        $("#execute-form").submit(function(event) {
                                            $("#loader").show();
                                        });
                                      </script>';
                            }
                            $editAction = '<a href='.url('invoices/edit/'.$model->id)
                                ." class='btn btn-sm btn-secondary btn-xs'".tooltip('Edit')."
                                <i class='fa fa-edit' style='color:white;'>
                                 </i></a>";

                            return '<a href='.url('invoices/show?invoiceid='.$model->id)
                            ." class='btn btn-sm btn-secondary btn-xs' ".tooltip('View')."<i class='fa fa-eye' 
                            style='color:white;'></i></a>"
                                    ."   $editAction $action";
                        })
                        ->filterColumn('invoice_no', function ($model, $keyword) {
                            $model->whereRaw('invoices.number like ?', ["%$keyword%"]);
                        })
                        ->filterColumn('order_no', function ($model, $keyword) {
                            $model->whereRaw('orders.number like ?', ["%$keyword%"]);
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
                         ->rawColumns(['checkbox', 'date', 'invoice_no', 'order_no', 'total', 'paid', 'balance', 'status', 'action'])
                        ->make(true);
    }

    public function getOrderDetail(Request $request, $id)
    {
        $order = Order::leftJoin('subscriptions', 'orders.id', '=', 'subscriptions.order_id')
            ->leftJoin('users', 'orders.client', '=', 'users.id')
            ->leftJoin('products', 'orders.product', '=', 'products.id')
            ->where('orders.client', '=', $id)
            ->select(
                'orders.id', 'orders.created_at', 'price_override', 'order_status', 'product', 'number', 'serial_key',
                'subscriptions.update_ends_at as subscription_ends_at', 'subscriptions.id as subscription_id', 'subscriptions.version as product_version', 'subscriptions.updated_at as subscription_updated_at', 'subscriptions.created_at as subscription_created_at',
                'products.name as product_name', \DB::raw("concat(first_name, ' ', last_name) as client_name"), 'client as client_id',
            )->orderBy('orders.created_at', 'desc');

        return DataTables::of($order)
                        ->addColumn('checkbox', function ($model) {
                            return "<input type='checkbox' class='order_checkbox' 
                            value=".$model->id.' name=select[] id=checkorder>';
                        })
                        ->addColumn('date', function ($model) {
                            return getDateHtml($model->created_at);
                        })
                        ->addColumn('product', function ($model) {
                            return $model->product_name;
                        })
                        ->addColumn('number', function ($model) {
                            $orderLink = '<a href='.url('orders/'.$model->id).'>'.$model->number.'</a>';
                            $installedPath = InstallationDetail::where('order_id', $model->id)->pluck('installation_path')->toArray();
                            if ($model->subscription_updated_at) {//For few older clients subscription was not generated, so no updated_at column exists
                                $orderLink = '<a href='.url('orders/'.$model->id).'>'.$model->number.'</a>'.installationStatusLabel($installedPath);
                            }
                            if ($model->order_status == 'Terminated') {
                                $badge = 'badge';

                                $orderLink = '<a href='.url('my-order/'.$model->id).'>'.$model->number.'</a>'.'&nbsp;<span class="'.$badge.' '.$badge.'-danger"  <label data-toggle="tooltip" style="font-weight:500;" data-placement="top" title="Order has been Terminated">

                         </label>
            Terminated</span>';
                            }

                            return $orderLink;
                        })
                         ->addColumn('version', function ($model) {
                             return $model->product_version;
                             $installedVersions = InstallationDetail::where('order_id', $model->id)->pluck('version')->toArray();
                             if (count($installedVersions)) {
                                 $latest = max($installedVersions);

                                 return getVersionAndLabel($latest, $model->product);
                             } else {
                                 return '--';
                             }
                         })
                          ->addColumn('expiry', function ($model) {
                              $ends_at = strtotime($model->subscription_ends_at) > 1 ? $model->subscription_ends_at : '--';

                              return getExpiryLabel($ends_at);
                          })
                         ->addColumn('status', function ($model) {
                             $status = $model->order_status;

                             return $status;
                         })
                        ->addColumn('action', function ($model) {
                            return '<a href='.url('orders/'.$model->id)." 
                            class='btn btn-sm btn-secondary btn-xs'".tooltip('View')."<i class='fa fa-eye' 
                            style='color:white;'> </i></a>";
                        })
                        ->rawColumns(['checkbox', 'date', 'product', 'number', 'version', 'expiry', 'status', 'action'])

->filter(function ($query) use ($request) {
    if ($request->has('search') && ! empty($request->input('search')['value'])) {
        $searchValue = $request->input('search')['value'];
        $query->where(function ($subquery) use ($searchValue) {
            $subquery->where('orders.number', 'like', "%{$searchValue}%")
                     ->orWhere('products.name', 'like', "%{$searchValue}%")
                     ->orWhere('subscriptions.version', 'like', "%{$searchValue}%")
                     ->orWhere('orders.order_status', 'like', "%{$searchValue}%");
        });
    }
})
                        ->make(true);
    }

    //Get Payment Details on Invoice Page
    public function getPaymentDetail($id)
    {
        $client = $this->user->where('id', $id)->first();
        $payments = $client->payment()->orderBy('created_at', 'desc');
        $extraAmt = $this->getExtraAmtPaid($id);

        return DataTables::of($payments)
                        ->addColumn('checkbox', function ($model) {
                            return "<input type='checkbox' class='payment_checkbox' 
                            value=".$model->id.' name=select[] id=checkpayment>';
                        })
                        ->addColumn('invoice_no', function ($model) {
                            return $model->invoice()->count() ? '<a href='.url('invoices/show?invoiceid='.$model->invoice()->first()->id).'>'.$model->invoice()->first()->number.'</a><br>' : '--';
                        })
                        ->addColumn('date', function ($model) {
                            return getDateHtml($model->created_at);
                        })
                        ->addColumn('payment_method', function ($model) {
                            return $model->payment_method;
                        })

                         ->addColumn('total', function ($model) use ($client, $extraAmt) {
                             if ($model->invoice_id == 0) {
                                 $amount = currencyFormat($extraAmt, $code = getCurrencyForClient($client->country));
                             } else {
                                 $currency = Invoice::find($model->invoice_id)->currency;
                                 $amount = currencyFormat($model->amount, $code = $currency);
                             }

                             return $amount;
                         })
                         ->addColumn('status', function ($model) {
                             return ucfirst($model->payment_status);
                         })

                         ->addColumn('action', function ($model) {
                             if ($model->invoice_id == 0) {
                                 if ($model->payment_method == 'Credit Balance') {
                                     return '<a href='.url('payments/'.$model->id.'/edit/')." class='btn btn-sm btn-secondary btn-xs' ".tooltip('Edit')." <i class='fa fa-edit' style='color:white;'> </i></a>   ".$this->creditActivityPopup($model->id);
                                 }

                                 return '<a href='.url('payments/'.$model->id.'/edit/')." class='btn btn-sm btn-secondary btn-xs' ".tooltip('Edit')." <i class='fa fa-edit' style='color:white;'> </i></a>";
                             } else {
                                 return '--';
                             }
                         })

                        ->rawColumns(['checkbox', 'invoice_no', 'date', 'payment_method', 'total', 'status', 'action'])

                        ->make(true);
    }

    public function creditActivityPopup($paymentId)
    {
        return view('themes.default1.front.clients.credit-activity-popup', compact('paymentId'));
    }
}
