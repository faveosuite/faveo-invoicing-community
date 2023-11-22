@extends('themes.default1.layouts.front.master')
@section('title')
Checkout
@stop
@section('page-header')
Checkout
@stop
@section('page-heading')
 Checkout
@stop
@section('breadcrumb')
 @if(Auth::check())
 <style>
    .remove-coupon-btn {
      background: none;
      border: none;
      cursor: pointer;
      padding: 0;
    }
    .amount-with-icon {
        position: relative;
    }
    .amount {
        display: inline-block;
    }
    .remove-icon {
        position: absolute;
        top: 0;
        left: 100%; 
        margin-left: 5px; 
        font-size: 17px;
        background: none;
       border: none;

        }
        .fa-1x {
            font-size: 15px;
            margin-right: 3.4px;
        }

 </style>
        <li><a class="text-primary" href="{{url('my-invoices')}}">Home</a></li>
@else
     <li><a class="text-primary" href="{{url('login')}}">Home</a></li>
@endif
 <li class="active text-dark">Checkout</li>
@stop
@section('main-class') "main shop" @stop
@section('content')
    <?php
      $amt_to_credit = 0;
      $curr ='';
      if(empty($content)){
          $curr = '';
      }
      else{
          foreach ($content as $item){
              $curr = $item->attributes->currency;
          }
      }
    ?>
@if (!\Cart::isEmpty())
<?php
$cartSubtotalWithoutCondition = 0;
\DB::table('users')->where('id', \Auth::user()->id)->update(['billing_pay_balance'=>0]);
?>
    <div class="container shop py-3">

            <div class="row">

                <div class="col-lg-7 mb-4 mb-lg-0">

                    <form method="post" action="">

                        <div class="table-responsive">

                            <table class="shop_table">

                                <thead>

                                <tr class="text-color-dark">

                                    <th class="product-thumbnail" width="">
                                        &nbsp;
                                    </th>

                                    <th class="product-name text-uppercase" width="">

                                        Product

                                    </th>

                                    <th class="product-price text-uppercase" width="">

                                        Version
                                    </th>

                                    <th class="product-quantity text-uppercase" width="">

                                        Quantity
                                    </th>

                                    <th class="product-subtotal text-uppercase " width="">

                                        Total
                                    </th>
                                </tr>
                                </thead>

                                <tbody>
                                     {{Cart::removeCartCondition('Processing fee')}}
                                        @forelse($content as $item)
                                       

                                        @php
                                        $cartSubtotalWithoutCondition += $item->getPriceSum();
                                        @endphp

                                <tr class="cart_table_item">

                                    <td class="product-thumbnail">

                                        <div class="product-thumbnail-wrapper">

                                            <a onclick="removeItem('{{$item->id}}');" class="product-thumbnail-remove" data-bs-toggle="tooltip" title="Remove Product">

                                                <i class="fas fa-times"></i>
                                            </a>

                                            <span class="product-thumbnail-image" data-bs-toggle="tooltip" title="{{$item->name}}">

                                                    <img width="90" height="90" alt="" class="img-fluid" src="{{$item->associatedModel->image}}">
                                                </span>
                                        </div>
                                    </td>

                                    <td class="product-name">

                                        <span class="font-weight-semi-bold text-color-dark">{{$item->name}}</span>
                                    </td>

                                    <td class="product-price">

                                        <span class="amount font-weight-medium text-color-grey">     
                                            @if($item->associatedModel->version)
                                            {{$item->associatedModel->version}}
                                            @else
                                            Not available
                                            @endif</span>
                                    </td>
                                    <?php
                                    $cont = new \App\Http\Controllers\Product\ProductController();
                                    $isAgentAllowed = $cont->allowQuantityOrAgent($item->id);
                                    ?>
                                    @if(!$isAgentAllowed)

                                    <td class="product-quantity">

                                        <span class="amount font-weight-medium text-color-grey">{$item->quantity}}</span>
                                    </td>
                                    @else
                                     <td class="product-agents">
                                    {{($item->attributes->agents)?$item->attributes->agents:'Unlimited'}}
                                </td>
                                @endif

                                    <td class="product-subtotal ">

                                           <?php
                                    $productId = \DB::table('products')->where('name', $item->name)->value('id');
                                    $planid = null;
                                    if(\Session::has('priceToBePaid')){
                                        $price=\Session::get('priceToBePaid');
                                    }
                                    else {
                                        if (\Session::has('toggleState') || \Session::get('toggleState') == null) {
                                            $toggleState = \Session::get('toggleState');
                                            $price = $item->price;
                                        } else {
                                            $planid = \DB::table('plans')->where('product', $item->id)->value('id');
                                            $countryids = \App\Model\Common\Country::where('country_code_char2', \Auth::user()->country)->value('country_id');

                                            $price = \DB::table('plan_prices')->where('plan_id', $planid)->where('currency', $item->attributes->currency)->where('country_id',$countryids)->value('add_price');
                                            if(!$price){
                                                $price = \DB::table('plan_prices')->where('plan_id', $planid)->where('currency', $item->attributes->currency)->where('country_id',0)->value('add_price');

                                            }
                                        }
                                    }

                                    ?>

                                    <span class="amount text-color-dark font-weight-bold text-4">

                                           @if ($item->conditions && $item->conditions->getType() === 'coupon')
                                           <?php
                                           \Session::put('togglePrice',$item->conditions->getName())
                                           ?>

                                                {{ $item->quantity * $item->conditions->getName() }}
                                           
                                           

                                            @else
                                                {{currencyFormat($item->quantity * $price,$code = $item->attributes->currency)}}
                                            @endif
                                    </td>
                                </tr>
                                  @empty
                                <p>Your Cart is empty</p>
                                @endforelse
                                </tbody>
                            </table>
                        </div>
                    </form>
                </div>

                <div class="col-lg-5 position-relative">

                    <div class="card border-width-3 border-radius-0 border-color-hover-dark" data-plugin-sticky data-plugin-options="{'minWidth': 991, 'containerSelector': '.row', 'padding': {'top': 85}}">

                        <div class="card-body">

                            <h4 class="font-weight-bold text-uppercase text-4 mb-3">Your Order</h4>

                            <div class="totals-cart">

                                <table class="shop_table cart-totals mb-3">

                                    <tbody>

                                    <tr>
                                        <td class="border-top-0">
                                            <strong class="d-block text-color-dark line-height-1 font-weight-semibold">Cart Subtotal</strong>
                                        </td>
                                        <td class=" align-top border-top-0">
                                            <span class="amount font-weight-medium text-color-grey">                                {{currencyFormat($cartSubtotalWithoutCondition,$code = $item->attributes->currency)}}
                                           </span>
                                        </td>
                                    </tr>
                                    @if(Session::has('code'))

                                     <tr>
                                        <td class="border-top-0">
                                            <strong class="d-block text-color-dark line-height-1 font-weight-semibold">Discount</strong>
                                        </td>
                                        <td class=" align-top border-top-0">
                                            <span class="amount font-weight-medium text-color-grey">
                                                 <?php
                                            if (strpos(\Session::get('codevalue'), '%') == true) {
                                                    $discountValue = currencyFormat($discountPrice,$code = $item->attributes->currency);
                                                    echo $discountValue . '(<strong title="Coupon code">' . (\Session::get('code')) . '</strong>)';
                                                } else {
                                                    $discountValue = currencyFormat(\Session::get('codevalue'),$code = $item->attributes->currency);
                                                    echo $discountValue . '(<strong title="Coupon code">' . (\Session::get('code')) . '</strong>)';
                                                }
                                            ?>
                                            </span>
                                                 <form action="{{ url('remove-coupon') }}" method="POST">
                                                @csrf
                                                <button type="submit" class="remove-icon" title="Click to remove">
                                                    <i class="fas fa-times-circle"></i>
                                                </button>
                                            </form>
                                        </td>
                                    </tr>
                                    @endif


                                           @if(count(\Cart::getConditionsByType('tax')) == 1)
                                                    @foreach(\Cart::getConditions() as $tax)

                                                     @if($tax->getName()!= 'null')
                                                    <tr class="Taxes">
                                                        <?php
                                                        $bifurcateTax = bifurcateTax($tax->getName(),$tax->getValue(),$item->attributes->currency, \Auth::user()->state, \Cart::getContent()->sum('price'));
                                                        ?>
                                                       <th>
                                                            
                                                            <strong class="d-block text-color-dark line-height-1 font-weight-semibold">{!! $bifurcateTax['html'] !!}</strong><br/>

                                                        </th>
                                                        <td class=" align-top border-top-0">
                                                            <span class="amount font-weight-medium text-color-grey">
                                                         {!! $bifurcateTax['tax'] !!}
                                                     </span>
                                                      </td>
                                                      
                                                       
                                                    </tr>
                                                    @endif
                                                    @endforeach

                                                    @else
                                                    @foreach(Cart::getContent() as $tax)
                                                    @if($tax->conditions->getName() != 'null')
                                                    <tr class="Taxes">
                                                        <?php
                                                        $bifurcateTax = bifurcateTax($tax->conditions->getName(),$tax->conditions->getValue(),$item->attributes->currency, \Auth::user()->state, $tax->price*$tax->quantity);
                                                        ?>
                                                       <th>
                                                            
                                                            <strong class="d-block text-color-dark line-height-1 font-weight-semibold">{!! $bifurcateTax['html'] !!}</strong><br/>

                                                        </th>
                                                        <td class=" align-top border-top-0">
                                                            <span class="amount font-weight-medium text-color-grey">
                                                         {!! $bifurcateTax['tax'] !!}
                                                     </span>
                                                      </td>
                                                      
                                                       
                                                    </tr>
                                                    @endif
                                                    
                                                    @endforeach
                                                   @endif

                                                 <tr id="balance-row" class="cart-subtotal" style="color: indianred; display: none;">
                                                    <th><strong class="d-block text-color-dark line-height-1 font-weight-semibold">Balance</strong></th>
                                                    <td class=" align-top border-top-0">
                                                    <span class="amount font-weight-medium text-color-grey">

                                                            <?php
                                                            if (\Cart::getTotal() <= $amt_to_credit) {
                                                                $cartTotal = \Cart::getTotal();
                                                            } else {
                                                                $cartTotal = $amt_to_credit;
                                                            }
                                                            ?>
                                                        -{{$dd=currencyFormat($cartTotal, $item->attributes->currency)}}
                                                    </span>
                                                    </td>
                                                </tr>


                                 @if(Cart::getTotal()>0)
                                    <tr>
                                        <td colspan="2" class="border-top-0">


                                                <div class="col-md-auto px-0 mb-3 mb-md-0">
                                                    {!! Form::open(['url'=>'pricing/update','method'=>'post']) !!}

                                                    <div class="d-flex align-items-center">

                                                        <input type="text" class="form-control h-auto line-height-1 py-3" name="coupon"  placeholder="Coupon Code" / style="width: 250px;">

                                                        <button type="submit" class="btn btn-light btn-modern text-color-dark bg-color-light-scale-2 text-color-hover-light bg-color-hover-primary text-uppercase text-3 font-weight-bold border-0 ws-nowrap btn-px-4 py-3 ms-2">Apply</button>
                                                    </div>
                                                    {!! Form::close() !!}
                                                </div>
                                        </td>
                                    </tr>
                                    @endif

                                    <tr class="total">

                                        <td>
                                            <strong class="text-color-dark text-3-5">Total</strong>
                                        </td>
                                         <?php
                                            if (\App\User::where('id',\Auth::user()->id)->value('billing_pay_balance')) {
                                                if (\Cart::getTotal() <= $amt_to_credit) {
                                                    $cartTotal = 0;
                                                } else {
                                                    $cartTotal = \Cart::getTotal() - $amt_to_credit;
                                                }
                                            } else {
                                                $cartTotal = \Cart::getTotal();
                                            }
                                            ?>
                                        <td class="">
                                            <strong><span class="amount text-color-grey text-5">
                                                {{ currencyFormat($cartTotal, $code = $item->attributes->currency) }}
                                            </span></strong>
                                        </td>
                                    </tr>
                                {!! Form::open(['url'=>'checkout-and-pay','method'=>'post','id' => 'checkoutsubmitform' ]) !!}

                                    @if(Cart::getTotal()>0) 
                                    <?php
                                    $gateways = \App\Http\Controllers\Common\SettingsController::checkPaymentGateway($item->attributes['currency']);

                                     ?>
                                     
                                     @if($gateways)

                                    <tr class="total">
                                         <?php $amt_to_credit = \DB::table('payments')
                                        ->where('user_id', \Auth::user()->id)
                                        ->where('payment_method','Credit Balance')
                                        ->where('payment_status','success')
                                        ->where('amt_to_credit','!=',0)
                                        ->value('amt_to_credit');
                                        ?>
                                        @if($amt_to_credit)

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

                                                        <input class="form-check-input mt-1" type="checkbox" value="" name="agree" id="billing-pay-balance" data-msg-required="You must agree before submiting." checked>
                                                        @else
                                                        <input class="form-check-input mt-1" type="checkbox" value="" name="agree" id="billing-pay-balance" data-msg-required="You must agree before submiting.">
                                                        @endif

                                                        <label class="form-check-label" for="tabContent9Checkbox">
                                                            Use your balance <strong class="text-3-5">{{currencyFormat($amt_to_credit,$code = $item->attributes->currency)}}</strong>
                                                        </label>
                                                    </div>
                                                </div>
                                            </div>
                                        </td>
                                        @endif
                                    </tr>
                             
                                             <tr id="balance-row" class="cart-subtotal" style="color: indianred; display: none;">
                                                <td>
                                                 <input type="checkbox" id="billing-temp-balance" class="checkbox" checked disabled>
                                                <label for="billing-pay-balance" class="checkbox-label" disabled><b>Total Credits remaining on your current plan: {{currencyFormat(\Session::get('priceRemaining'),$code = $item->attributes->currency)}}</b></label>
                                            </td>
                                            </tr>
                                          
                                                  
                                    <tr class="payment-methods">

                                        <td colspan="2">
                                        


                                            <strong class="d-block text-color-dark mb-2">Payment Methods</strong>

                                            <div class="d-flex flex-column">
                                                @foreach($gateways as $gateway)
                                                 <?php
                                                $processingFee = \DB::table(strtolower($gateway))->where('currencies',$item->attributes['currency'])->value('processing_fee');
                                                ?>

                                                <label class="align-items-center text-color-grey mb-0" for="payment_method1">

                                                    {!! Form::radio('payment_gateway',$gateway,false,['id'=>'allow_gateway','onchange' => 'getGateway(this)','processfee'=>$processingFee]) !!}

                                                    <img alt="{{$gateway}}" width="111" src="{{asset('storage/client/images/'.$gateway.'.png')}}">

                                                        <p class="text-color-dark" id="fee" style="display:none;">An extra processing fee of <b>{{$processingFee}}%</b> will be charged on your Order Total during the time of payment</p>

                                                       

                                                </label>
                                                @endforeach
                                            </div>
                                        </td>
                                    </tr>
                                     @endif
                                     @endif
                                    </tbody>
                                </table>
                            </div>
                            <button type="submit" id="proceed" class="btn btn-dark btn-modern w-100 text-uppercase text-3 py-3">Proceed <i class="fas fa-arrow-right ms-2"></i></button>
                     
                             {!! Form::close() !!}


                        </div>
                    </div>
                </div>
            </div>
        </div>
        @elseif (\Cart::isEmpty())
@endif
<script src="https://ajax.googleapis.com/ajax/libs/jquery/1.7.1/jquery.min.js" type="text/javascript"></script>
<script>
    $(document).ready(function() {
        $('.clear-icon').click(function() {
            $(this).closest('.input-group').find('input[name=coupon]').val('').focus();
        });
    });
</script>
<script>

  $('#checkoutsubmitform').submit(function(){
     $("#proceed").html("<i class='fa fa-circle-o-notch fa-spin fa-1x ''></i>Processing ...")
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
          var cartTotal = parseFloat('{{ \Cart::getTotal() }}');
          // Check if the PHP variable exists and has a value
          var amountToCredit = parseFloat('{{ $amt_to_credit }}');
          var currency = '{{$curr}}';
          var updatedValue = 0;
          var $gateways = $('input:radio[name = payment_gateway]');

          // Calculate the updated value based on the checkbox status and PHP values
          if(isChecked){
              if(cartTotal<=amountToCredit){
                  updatedValue = 0;
                  $gateways.filter('[value=Razorpay]').attr('checked', false);
                  $gateways.filter('[value=Stripe]').attr('checked', false);
                  $gateways.filter('[value=Razorpay]').attr('disabled', true);
                  $gateways.filter('[value=Stripe]').attr('disabled', true);
              }
              else{
                  updatedValue = cartTotal - amountToCredit
                  $gateways.filter('[value=Razorpay]').attr('checked', true);
                  $gateways.filter('[value=Stripe]').attr('checked', true);
                  $gateways.filter('[value=Razorpay]').attr('disabled', false);
                  $gateways.filter('[value=Stripe]').attr('disabled', false);

              }
          }
          else{
              updatedValue = cartTotal;
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
                  currency: currency
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

@endsection
