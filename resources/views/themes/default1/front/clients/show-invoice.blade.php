@extends('themes.default1.layouts.front.master')
@section('title')
Invoice
@stop
@section('page-heading')
 View Invoice
@stop
@section('breadcrumb')
@section('breadcrumb')
    @if(Auth::check())
        <li><a class="text-primary" href="{{url('my-invoices')}}">Home</a></li>
    @else
         <li><a class="text-primary" href="{{url('login')}}">Home</a></li>
    @endif
     <li class="active text-dark">View Invoice</li>
@stop 
<style type="text/css">
    .text-fail{
        color: red;
    }
    .text-warning{
        color: yellow;
    }
    .invoice-table{
        border: none;
    }
    .table th{
        border-top: unset !important;
    }
    .moveleft{
        position: relative;
        left: 35px;
    }
    .table tr{
        line-height: 25px;
    }
</style>
@section('nav-invoice')
active
@stop

@section('content')
   @php
            $set = App\Model\Common\Setting::where('id', '1')->first();
            $date = getDateHtml($invoice->date);
            $symbol = $invoice->currency;
            $itemsSubtotal = 0;
            $taxAmt = 0;
        @endphp

        <div id="examples" class="container py-4" style="max-width: 900px">

            <div class="row">

                <div class="col-lg-6 col-sm-12 order-1 order-lg-2">

                    <div class="overflow-hidden mb-1">

                         @if($set->logo)
                        <img alt="Logo" width="150" height="100" src="{{asset('storage/images/'.$set->logo)}}" style="margin-top: -2px">
                         @endif

                        <h2 class="font-weight-normal text-7 mb-0">Invoice &nbsp;<span class="text-0 text-color-grey">#{{$invoice->number}}</span></h2>
                    </div>
                </div>

                <div class="col-lg-6 col-sm-12 text-lg-end order-1 order-lg-2">

                    <div class="overflow-hidden mb-2 pb-1">

                        <h4 class="mb-0">Date: {!! $date !!}</h4>
                    </div>

                    @php
                $statusClass = '';
                $statusText = '';

                switch ($invoice->status) {
                    case 'Success':
                        $statusClass = 'text-success';
                        $statusText = 'PAID';
                        break;
                    case 'partially paid':
                        $statusClass = 'text-warning';
                        $statusText = 'Partially paid';
                        break;
                    default:
                        $statusClass = 'text-fail';
                        $statusText = 'Unpaid';
                        break;
                }
                @endphp

                    <div class="overflow-hidden mb-4">
                     <h2 class="font-weight-normal text-7 mb-0 {{ $statusClass }}">
                    <strong class="font-weight-extra-bold">{{ $statusText }}</strong>
                </h2>


                    </div>
                </div>
            </div>

            <div class="row pt-3">

                <div class="col-lg-6">

                    <h2 class="text-color-dark font-weight-bold text-4 mb-1">From</h2>

                    <ul class="list list-unstyled text-2 mb-0">

                        <li class="mb-0"><strong>{{$set->company}}</strong></li>

                        <li class="mb-0">{{$set->address}}</li>

                        <li class="mb-0">{{$set->city}}<br/>
                                @if(key_exists('name',getStateByCode($set->state)))
                                {{getStateByCode($set->state)['name']}}
                                @endif
                                {{$set->zip}}<br/>
                                Country: {{getCountryByCode($set->country)}}<br/>
                                Mobile: <b>+</b>{{$set->phone_code}} {{$set->phone}}<br/>
                                Email: {{$set->company_email}}</li>

                         @if($set->gstin)
                        <li class="mb-0 mt-2 text-4"><b class="text-dark">GSTIN:</b> #{{$set->gstin}}</li>
                        @endif
                        @if($set->cin_no)

                        <li class="mb-0 text-4"><b class="text-dark">CIN:</b> #{{$set->cin_no}}</li>
                        @endif
                    </ul>
                </div>
              
                <div class="col-lg-6 mb-4 mb-lg-0">

                    <h2 class="text-color-dark font-weight-bold text-4 mb-1">To</h2>

                    <ul class="list list-unstyled text-2 mb-0">

                        <li class="mb-0"><strong>{{$user->first_name}} {{$user->last_name}}</strong></li>

                        @if($user->address)
                        {{$user->address}}<br/>
                        @endif
                        {{$user->town}}<br/>
                        @if(key_exists('name',getStateByCode($user->state)))
                            {{getStateByCode($user->state)['name']}}
                        @endif
                        {{$user->zip}}<br/>
                        Country: {{getCountryByCode($user->country)}}<br/>
                        Mobile: @if($user->mobile_code)<b>+</b>{{$user->mobile_code}}@endif {{$user->mobile}}<br/>
                        Email: {{$user->email}}<br />
                        @if($user->gstin)
                            <b>GSTIN:</b>  &nbsp; #{{$user->gstin}}
                            <br>
                            @endif
        </ul>
                </div>
            </div>
             <div class="bill-info" id="invoice-section">
            <div class="card p-3 mt-3">

                <div class="table-responsive">
                    <table class="table table-striped">

                        <thead>
                        <tr>
                            <th>Order No</th>
                            <th>Product</th>
                            <th>Price</th>
                            <th>Agents</th>
                            <th>Quantity</th>
                            <th>Subtotal</th>
                        </tr>
                        </thead>

                               <tbody>
                    @foreach($items as $item)
           
                        <tr>
                            @php
                            $taxName[] =  $item->tax_name.'@'.$item->tax_percentage;
                            if ($item->tax_name != 'null') {
                                $taxAmt +=  $item->subtotal;
                             }
                            $orderForThisItem = $item->order()->first();
                            $itemsSubtotal += $item->subtotal;
                            @endphp
                            @if($orderForThisItem)

                            <td> {!! $orderForThisItem->getOrderLink($orderForThisItem->id,'my-order') !!}</td>
                           
                                @elseif($order != '--')
                                <td>{!! $order !!}
                                <span class='badge badge-warning'>Renewed</span></td>
                                @else
                                <td>--</td>
                            @endif
                            @php
                              $period_id =\DB::table('plans_periods_relation')->where('plan_id',$item->plan_id)->latest()->value('period_id');
                              $plan = \DB::table('periods')->where('id',$period_id)->latest()->value('name');

                            @endphp
                            <td>{{$item->product_name}} {{($plan)}}
                            </td>
                             <td>{{currencyFormat(intval($item->regular_price),$code = $symbol)}}</td>
                             <td>{{($item->agents)?$item->agents:'Unlimited'}}</td>
                            <td>{{$item->quantity}}</td>
                            <td>{{currencyFormat($item->subtotal,$code = $symbol)}}</td>
                        </tr>
                    @endforeach
                    </tbody>
                    </table>
                </div>
                <div class="row">

                    <div class="col-sm-12 col-lg-6"></div>

                    <div class="col-sm-12 col-lg-6 text-lg-end">

                        <div class="table-responsive">

                            <table class="table h6 text-dark">

                              
                               
                                @foreach($items as $item)
                               @if($item->regular_price > 0)
                                 

                                <tbody>
                                <tr>

                                    <th>Subtotal</th>

                                    <td class="moveleft"  style="border-top: unset !important;">{{currencyFormat($itemsSubtotal,$code=$symbol)}}</td>
                                </tr>
                                @if($invoice->credits)
                                        <tr>
                                            <th>Discount</th>
                                            <td>{{currencyFormat($invoice->credits,$code=$symbol)}} (Credits)</td>
                                        </tr>
                                    @endif
                                @if($invoice->coupon_code && $invoice->discount)
                                <tr>
                                    <th>Discount</th>
                                    <td class="moveleft">{{currencyFormat($invoice->discount,$code=$symbol)}} ({{$invoice->coupon_code}})</td>
                                </tr>
                                @endif

                                 <?php
                                    $order = \App\Model\Order\Order::where('invoice_item_id',$item->id)->first();
                                    if($order != null) {
                                        $productId = $order->product;
                                    } else {
                                        $productId =  App\Model\Product\Product::where('name',$item->product_name)->pluck('id')->first();
                                    }
                                    $taxName = array_unique($taxName);
                                    ?>
                                    @foreach($taxName as $tax)
                                    <?php
                                    $taxDetails = explode('@', $tax);
                                    ?>
                                    @if ($taxDetails[0]!= 'null')

                                       
                                   <tr>
                                    <?php
                                    $bifurcateTax = bifurcateTax($taxDetails[0], $taxDetails[1], \Auth::user()->currency, \Auth::user()->state, $taxAmt);
                                    ?>
                                    @foreach(explode('<br>', $bifurcateTax['html']) as $index => $part)
                                    <tr>
                                        <th>
                                            <strong>
                                                <?php
                                                $parts = explode('@', $part);
                                                $cgst = $parts[0];
                                                $percentage = $parts[1];
                                                ?>
                                                <span class="font-weight-bold text-color-grey">{{ $cgst }}</span>
                                                <span style="font-weight: normal;color: grey;">({{ $percentage }})</span><br>
                                            </strong>
                                        </th>
                                        <td class="text-color-grey moveleft">
                                            <?php
                                            $taxParts = explode('<br>', $bifurcateTax['tax']);
                                            echo $taxParts[$index]; // Output tax amount corresponding to current index
                                            ?>
                                        </td>
                                    </tr>
                                    @endforeach
                                </tr>



                                     
                                       
                                    @endif
                                    @endforeach
                                    <?php
                                    $feeAmount = intval(ceil($invoice->grand_total * 0.99 / 100));
                                    ?>


                                @if($invoice->processing_fee != null && $invoice->processing_fee != '0%')
                                <tr>
                                    <th class="font-weight-bold text-color-grey">Processing fee <label style="font-weight: normal;">({{$invoice->processing_fee}})</label></th>
                                    <td class="text-color-grey moveleft">{{currencyFormat($feeAmount,$code = $symbol)}}</td>
                                </tr>
                                @endif
                                <tr class="h6">

                                    <th class="border-0">Total</th>

                                    <td class="border-0 moveleft">{{currencyFormat($invoice->grand_total,$code = $symbol)}}</td>
                                </tr>
                                @endif
                                @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
  
        
          @if(!$payments->isEmpty())
            <div class="card p-3 mt-3">

                <div class="table-responsive">
                    <table class="table">
                        
                        @foreach($payments as $payment)
                        @php
                        $DateTime = getDateHtml($payment->created_at);
                        $orderid = \DB::table('orders')->where('invoice_id',$invoice->id)->value('id');

                        @endphp

                        <thead>
                        <tr>
                            <th>Transaction Date</th>
                            <th>Method</th>
                            <th>Total</th>
                            <th>Status</th>
                        </tr>
                        </thead>

                        <tbody>
                        <tr>
                            <td>{!! $DateTime !!}</td>
                            <td>{{$payment->payment_method}}</td>
                            <td>{{currencyFormat($payment->amount,$code = $symbol)}}</td>
                            @if($payment->payment_status == 'success')
                            <td><span class="badge badge-success badge-xs">{{$payment->payment_status}}</span></td>
                            @else
                            <td><span class="badge badge-danger badge-xs">{{$payment->payment_status}}</span></td>
                            @endif

                        </tr>

                        </tbody>
                        @endforeach
                       
                    </table>
                </div>
            </div>
             @endif

            <div class="mt-4">

                <a href="{{url('pdf?invoiceid='.$invoice->id)}}" onclick="refreshPage()"  class="btn btn-dark float-end ms-2"><i class="fa fa-download"></i> Generate PDF</a>

                 @if($invoice->status !='Success')
                    <a href="{{url('paynow/'.$invoice->id)}}" target="_blank" class="btn btn-dark float-end ms-2"><i class="fa fa-credit-card"></i> Pay Now</a>
                @endif

            </div>
        </div>
<script>
  function refreshPage() {
    // Wait for 3 seconds (3000 milliseconds) before refreshing the page
    setTimeout(function() {
      // Reload the current page
      location.reload();
    }, 4000); // 3000 milliseconds = 3 seconds
  }
</script>
@stop