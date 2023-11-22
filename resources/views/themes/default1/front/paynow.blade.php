@extends('themes.default1.layouts.front.master')
@section('title')
Checkout
@stop
@section('page-heading')
Checkout
@stop
@section('breadcrumb')
 @if(Auth::check())
        <li><a class="text-primary" href="{{url('my-invoices')}}">Home</a></li>
@else
     <li><a class="text-primary" href="{{url('login')}}">Home</a></li>
@endif
 <li class="active text-dark">Checkout</li>
@stop
@section('main-class') "main shop" @stop
@section('content')
<?php
    $currency = $invoice->currency;
    $symbol = \App\Model\Payment\Currency::where('code',$invoice->currency)->pluck('symbol')->first();
    $taxAmt = 0;
    if(empty($invoice->billing_pay)){
        $alter = true;
    }
    else{
        $alter = false;
    }
 $amt_to_credit = \DB::table('payments')
    ->where('user_id', \Auth::user()->id)
    ->where('payment_method','Credit Balance')
    ->where('payment_status','success')
    ->where('amt_to_credit','!=',0)
    ->value('amt_to_credit');

\DB::table('users')->where('id', \Auth::user()->id)->update(['billing_pay_balance'=>0]);

?>
    <div class="container shop py-3">

            <div class="row">

                <div class="col-lg-7 mb-4 mb-lg-0">

                    <form method="post" action="">

                        <div class="table-responsive">

                            <table class="shop_table cart">

                                <thead>

                                <tr class="text-color-dark">

                                    <th class="product-thumbnail" >
                                        &nbsp;
                                    </th>

                                    <th class="product-name text-uppercase">

                                        Product

                                    </th>

                                    <th class="product-price text-uppercase" >

                                        Version
                                    </th>

                                    <th class="product-quantity text-uppercase" >

                                        Quantity
                                    </th>
                                     <th class="product-agents text-uppercase" >

                                        Agent
                                    </th>

                                    <th class="product-subtotal text-uppercase text-end">

                                        Total
                                    </th>
                                </tr>
                                </thead>
                                 @foreach($items as $item)
                                        @php
                                        Session::forget('code');
                                        $taxName[] =  $item->tax_name.'@'.$item->tax_percentage;
                                        if ($item->tax_name != 'null') {
                                            $taxAmt +=  $item->subtotal;
                                         }
                                         @endphp

                                <tbody>


                                <tr class="cart_table_item">

                                    <td class="product-thumbnail">

                                        <div class="product-thumbnail-wrapper">

                                            <span class="product-thumbnail-image" data-bs-toggle="tooltip" title="Faveo Enterprise Advance">

                                                    <img width="90" height="90" alt="" class="img-fluid" src="{{$product->image}}">
                                                </span>
                                        </div>
                                    </td>

                                    <td class="product-name">

                                        <span class="font-weight-semi-bold text-color-dark">{{$item->product_name}}</span>
                                    </td>

                                    <td class="product-price">

                                        <span class="amount font-weight-medium text-color-grey">   
                                            @if($product->version)
                                            {{$product->version}}
                                            @else 
                                            Not available
                                            @endif</span>
                                    </td>

                                    <td class="product-quantity">

                                        <span class="amount font-weight-medium text-color-grey">{{$item->quantity}}</span>
                                    </td>
                                    <td class="product-agents">

                                        <span class="amount font-weight-medium text-color-grey">{{($item->agents)?$item->agents:'Unlimited'}}</span>
                                    </td>

                                    <td class="product-subtotal text-end">

                                        <span class="amount text-color-dark font-weight-bold text-4">{{currencyFormat(($item->regular_price),$code = $currency)}}</span>
                                    </td>
                                </tr>
                                </tbody>
                                @endforeach
                            </table>
                        </div>
                    </form>
                </div>

                <div class="col-lg-5 position-relative">

                    <div class="card border-width-3 border-radius-0 border-color-hover-dark" data-plugin-sticky data-plugin-options="{'minWidth': 991, 'containerSelector': '.row', 'padding': {'top': 85}}">

                        <div class="card-body">

                            <h4 class="font-weight-bold text-uppercase text-4 mb-3">Your Order</h4>

                            <div class="table-responsive">

                                <table class="shop_table cart-totals mb-3">

                                    <tbody>
                                <?php 
                                $subtotals = App\Model\Order\InvoiceItem::where('invoice_id',$invoice->id)->pluck('regular_price')->toArray();
                                $subtotal = array_sum($subtotals);
                                ?>

                                    <tr>
                                        <td class="border-top-0">
                                            <strong class="d-block text-color-dark line-height-1 font-weight-semibold">Cart Subtotal</strong>
                                        </td>
                                        <td class="text-end align-top border-top-0">
                                            <span class="amount font-weight-medium text-color-grey">{{currencyFormat($subtotal,$code = $currency)}}</span>
                                        </td>
                                    </tr>


                                @php
                                $taxName = array_unique($taxName);
                                @endphp
                                  @foreach($taxName as $tax)
                                  @php
                                  $taxDetails = explode('@', $tax);
                                  @endphp
                                  @if ($taxDetails[0]!= 'null')
                                                            
                                                       
                                    <tr>
                                         <?php
                                        $bifurcateTax = bifurcateTax($taxDetails[0],$taxDetails[1],\Auth::user()->currency, \Auth::user()->state, $taxAmt);
                                        ?>
                                       <td class="border-top-0">
                                                            <strong class="d-block text-color-dark line-height-1 font-weight-semibold">
                                                                {!! $bifurcateTax['html'] !!}
                                                            </span>
                                                        </td>
                                        <td class="text-end align-top border-top-0"><span class="amount font-weight-medium text-color-grey">
                                           
                                            {!! $bifurcateTax['tax'] !!}
                                        </span>

                                        </td>
                                    </tr>
                             
                               
                                    @endif
                                    @endforeach


                                    @if($paid)

                                    <td class="border-top-0">
                                                            <strong class="d-block text-color-dark line-height-1 font-weight-semibold">
                                                                Paid
                                                            </strong>
                                                        </td>
                                    <td class="text-end align-top border-top-0"><span class="amount font-weight-medium text-color-grey">

                                        {{currencyFormat($paid,$code = $currency)}}</span>
                                    </td>
                                </tr>

                                <tr class="total">
                                    <th>
                                        <strong>Balance</strong>
                                    </th>
                                    <td class="text-end align-top border-top-0"><span class="amount font-weight-medium text-color-grey">
                                        {{currencyFormat($invoice->grand_total,$code = $currency)}}
                                    </span>
                                    </td>
                                </tr>
                                @endif
                                <tr id="balance-row" class="cart-subtotal" style="color: indianred; display: none;">
                                    <td class="border-top-0">
                                    <strong class="d-block text-color-dark line-height-1 font-weight-semibold">Balance</strong></td>
                                    <td class="text-end align-top border-top-0">
                                        <span class="amount font-weight-medium text-color-grey">
                                        <?php
                                        if(empty($invoice->billing_pay)) {
                                            if ($invoice->grand_total <= $amt_to_credit) {
                                                $cartTotal = $invoice->grand_total;
                                            } else {
                                                $cartTotal = $amt_to_credit;
                                            }
                                        }else{
                                            $cartTotal = $invoice->billing_pay;
                                        }
                                        ?>
                                        -{{$dd=currencyFormat($cartTotal, $currency)}}
                                    </span>
                                    </td>
                                </tr>
                                @if($invoice->billing_pay)
                                <tr id="balance-row" class="cart-subtotal" style="color: indianred;">
                                  <td class="border-top-0">
                                    <strong class="d-block text-color-dark line-height-1 font-weight-semibold">Balance</strong></td>
                                    <td class="text-end align-top border-top-0">
                                        <span class="amount font-weight-medium text-color-grey">
                                        -{{$dd=currencyFormat($invoice->billing_pay, $currency)}}
                                    </span>
                                    </td>
                                </tr>
                                @endif

                                       <tr id="balance-row" class="cart-subtotal" style="color: indianred; display: none;">
                                               <td class="border-top-0">
                                    <strong class="d-block text-color-dark line-height-1 font-weight-semibold">Balance</strong></td>
                                            <td class="text-end align-top border-top-0">
                                        <span class="amount font-weight-medium text-color-grey">
                                                <?php
                                                if(empty($invoice->billing_pay)) {
                                                    if ($invoice->grand_total <= $amt_to_credit) {
                                                        $cartTotal = $invoice->grand_total;
                                                    } else {
                                                        $cartTotal = $amt_to_credit;
                                                    }
                                                }else{
                                                    $cartTotal = $invoice->billing_pay;
                                                }
                                                ?>
                                                -{{$dd=currencyFormat($cartTotal, $currency)}}\</span>
                                            </td>
                                        </tr>


                                    <tr class="total">

                                        <td>
                                            <strong class="text-color-dark text-3-5">Total</strong>
                                        </td>
                                        <?php
                                         if (\App\User::where('id',\Auth::user()->id)->value('billing_pay_balance')) {
                                            if ($invoice->grand_total <= $amt_to_credit) {
                                                $cartTotal = 0;
                                            } else {
                                                $cartTotal = $invoice->grand_total - $amt_to_credit;
                                            }
                                        } else {
                                            $cartTotal = $invoice->grand_total;
                                        }
                                        ?>
                                            <td class="text-end">
                                            <strong><span class="amount text-color-grey text-5">{{ currencyFormat($cartTotal, $code = $currency) }}</span></strong>
                                        </td>
                                    </tr>
                                    {!! Form::open(['url'=>'checkout-and-pay','method'=>'post','id' => 'checkoutsubmitform']) !!}
                                    @if($invoice->grand_total > 0)
                                     <?php $gateways = \App\Http\Controllers\Common\SettingsController::checkPaymentGateway($invoice->currency);
                       ?>
                          @if(count($gateways))
                          @if(empty($invoice->billing_pay) && $amt_to_credit)


                                    <tr class="total">

                                        <td colspan="2">

                                            <div class="row">

                                                <div class="col-sm-8">

                                                    <strong class="text-color-dark text-3-5">YOUR AVAILABLE BALANCE</strong>
                                                </div>
                                            </div>

                                            <div class="row mt-2">

                                                <div class="form-group col mb-0">

                                                    <div class="form-check">
                                                         @if(\App\User::where('id',\Auth::user()->id)->value('billing_pay_balance'))


                                                        <input class="form-check-input mt-1" type="checkbox" id="billing-pay-balance" name="agree" id="tabContent9Checkbox" data-msg-required="You must agree before submiting." checked>
                                                        @else
                                                         <input class="form-check-input mt-1" type="checkbox" id="billing-pay-balance" name="agree" id="tabContent9Checkbox" data-msg-required="You must agree before submiting." checked>
                                                        @endif

                                                        <label class="form-check-label" for="tabContent9Checkbox">
                                                            Use your balance <strong class="text-3-5">{{currencyFormat($amt_to_credit,$code = $currency)}}</strong>
                                                        </label>
                                                    </div>
                                                </div>
                                            </div>
                                        </td>
                                    </tr>
                                      @endif

                                    <tr class="payment-methods">

                                        <td colspan="2">

                                            <strong class="d-block text-color-dark mb-2">Payment Methods</strong>

                                            <div class="d-flex flex-column">
                                                 @foreach($gateways as $gateway)
                                                   <?php
                                                      $processingFee = \DB::table(strtolower($gateway))->where('currencies',$invoice->currency)->value('processing_fee');
                                                    ?>

                                                <label class="d-flex align-items-center text-color-grey mb-0" for="payment_method1">

                                                 {!! Form::radio('payment_gateway',$gateway,false,['id'=>'allow_gateway','onchange' => 'getGateway(this)','processfee'=>$processingFee]) !!}


                                                    <img alt="{{$gateway}}" width="111" src="{{asset('storage/client/images/'.$gateway.'.png')}}">
                                                    <div id="fee" style="display:none;position: relative; bottom: -30px;right: 110px;">
                                                        <p class="text-color-dark text-3-5">An extra processing fee of <b>{{$processingFee}}%</b> will be charged on your Order Total during the time of payment</p></div><br>
                                                </label>
                                                  @endforeach

                   
                                            </div>
                                        </td>
                                    </tr>
                                      @endif
                                             {!! Form::hidden('invoice_id',$invoice->id) !!}
                                             {!! Form::hidden('cost',$invoice->grand_total) !!}
                                    </tbody>
                                </table>
                            </div>
                            <button type="submit" id="proceed" class="btn btn-dark btn-modern w-100 text-uppercase text-3 py-3">Proceed <i class="fas fa-arrow-right ms-2"></i></button>
                           @endif
                             {!! Form::close() !!}
                        </div>
                    </div>
                </div>
            </div>
        </div>

<script src="https://ajax.googleapis.com/ajax/libs/jquery/1.7.1/jquery.min.js" type="text/javascript"></script>
<script>
  $('#checkoutsubmitform').submit(function(){
     $("#proceed").html("<i class='fa fa-circle-o-notch fa-spin fa-1x fa-fw'></i>Please Wait...")
    $("#proceed").prop('disabled', true);

  });
    $(document).ready(function(){
    var $gateways = $('input:radio[name = payment_gateway]');
    if($gateways.is(':checked') === false) {
        $gateways.filter('[value=Razorpay]').attr('checked', true);
        $('#fee').hide();
    } else {
        $gateways.filter('[value=Stripe]').attr('checked', true);
        $('#fee').show();
    }
  });

  function getGateway($this)
  {
    var gateWayName = $this.value;
    var fee = $this.getAttribute("processfee");
    console.log(fee)
    if (fee == '0') {
        $('#fee').hide();
    } else {
        $('#fee').show();
    }
  }

  $(document).ready(function () {
      $('#billing-pay-balance').on('change', function () {
          var isChecked = $(this).prop('checked');

          $.ajax({
              type: "POST",
              url: "{{ route('update-session') }}",
              data: { isChecked: isChecked },
              headers: {
                  'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
              },
              success: function(response) {
              }
          });
      });
  });
  $(document).ready(function () {
      $('#billing-pay-balance').on('change', function () {
          var isChecked = $(this).prop('checked');
          $('#balance-row').toggle(isChecked);
      });
  });

  $(document).ready(function () {
      function updateContent() {
          var isChecked = $('#billing-pay-balance').prop('checked'); // Get the checkbox status
          var cartTotal = parseFloat('{{ $invoice->grand_total }}');
          var alter = '{{$alter}}'
          var invoiceId = '{{$invoice->id}}';
          var billing_pay = null;
          // Check if the PHP variable exists and has a value
          var amountToCredit = parseFloat('{{ $amt_to_credit }}');
          var currency = '{{$currency}}';
          var updatedValue = 0;
          var $gateways = $('input:radio[name = payment_gateway]');


          // Calculate the updated value based on the checkbox status and PHP values
          if(isChecked){
              if(cartTotal<=amountToCredit){
                  updatedValue = 0;
                  billing_pay = cartTotal;
                  $gateways.filter('[value=Razorpay]').attr('checked', false);
                  $gateways.filter('[value=Stripe]').attr('checked', false);
                  $gateways.filter('[value=Razorpay]').attr('disabled', true);
                  $gateways.filter('[value=Stripe]').attr('disabled', true);
              }
              else{
                  updatedValue = cartTotal - amountToCredit;
                  billing_pay = amountToCredit;
                  $gateways.filter('[value=Razorpay]').attr('checked', true);
                  $gateways.filter('[value=Stripe]').attr('checked', true);
                  $gateways.filter('[value=Razorpay]').attr('disabled', false);
                  $gateways.filter('[value=Stripe]').attr('disabled', false);

              }
          }
          else{
              updatedValue = cartTotal;
              billing_pay = null;
              $gateways.filter('[value=Razorpay]').attr('checked', true);
              $gateways.filter('[value=Stripe]').attr('checked', true);
              $gateways.filter('[value=Razorpay]').attr('disabled', false);
              $gateways.filter('[value=Stripe]').attr('disabled', false);
          }
          // Make an AJAX request to the API endpoint
          $.ajax({
              type: "GET",
              url: "{{ url('format-currency') }}",
              data: {
                  amount: updatedValue,
                  currency: currency,
                  invoiceId: invoiceId,
                  billing_pay: billing_pay,
                  alter: alter,
              },
              success: function (data) {
                  // Update the content in the HTML element with the formatted value
                  $('#balance-content').html(data.formatted_value);
              },
              error: function (xhr, status, error) {
                  console.log(error);
              }
          });
      }

      // Initial update on page load
      updateContent();

      // Update content when the checkbox is clicked
      $('#billing-pay-balance').on('change', function () {
          updateContent();
      });
  });
</script>
<style>
    .underline-label {
        display: inline-block;
        border-bottom: 0.5px solid gray;
        width: 100%;
        padding-bottom: 3px;
    }



</style>
@endsection
