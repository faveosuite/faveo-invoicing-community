@extends('themes.default1.layouts.front.master')
@section('title')
    Razorpay
@stop
@section('page-heading')
    Razorpay
@stop
@section('page-heading')
 Checkout
@stop
@section('breadcrumb')
 @if(Auth::check())
     <li><a href="{{url('my-invoices')}}">{{ __('message.home')}}</a></li>
 @else
     <li><a href="{{url('login')}}">{{ __('message.home')}}</a></li>
 @endif
 <li><a href="{{url('checkout')}}">{{ __('message.checkout')}}</a></li>
 <li class="active">{{ __('message.razorpay')}}</li>
@stop
@section('main-class') "main shop" @stop
@section('content')
<?php

$cartSubtotalWithoutCondition = 0;
 
use Razorpay\Api\Api;
 $merchant_orderid= generateMerchantRandomString();  

function generateMerchantRandomString($length = 10) {
$characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
$charactersLength = strlen($characters);
$randomString = '';
for ($i = 0; $i < $length; $i++) {
    $randomString .= $characters[rand(0, $charactersLength - 1)];
}
return $randomString;
}
 $api = new Api($rzp_key, $rzp_secret);
$displayCurrency = \Auth::user()->currency;
$symbol = \Auth::user()->currency;
if ($symbol == 'INR'){


$exchangeRate= '';


$orderData = [
'receipt'         => 3456,
'amount'          => round($invoice->grand_total*100), // 2000 rupees in paise

'currency'        => 'INR',
'payment_capture' => 0 // auto capture
 
];


} else {
 
 $url = "http://apilayer.net/api/live?access_key=$apilayer_key";
 $exchange = json_decode(file_get_contents($url));

 $exchangeRate = $exchange->quotes->USDINR;
 // dd($exchangeRate);
 $displayAmount =$exchangeRate * $invoice->grand_total ;


 $orderData = [
'receipt'         => 3456,
'amount'          =>  round($displayAmount)*100, // 2000 rupees in paise

'currency'        => 'INR',
'payment_capture' => 0 // auto capture
     
];
}
$razorpayOrder = $api->order->create($orderData);
$razorpayOrderId = $razorpayOrder['id'];
$_SESSION['razorpay_order_id'] = $razorpayOrderId;
$displayAmount = $amount = $orderData['amount'];



$data = [


    "key"               => $rzp_key,
    "name"              => 'Faveo Helpdesk',
    "currency"          => 'INR',
     "prefill"=> [
        "contact"=>    \Auth::user()->mobile_code .\Auth::user()->mobile,
        "email"=>      \Auth::user()->email,
    ],
    "description"       =>  'Order for Invoice No' .-$invoice->number,
    "notes"             => [
    "First Name"         => \Auth::user()->first_name,
    "Last Name"         =>  \Auth::user()->last_name,
    "Company Name"      => \Auth::user()->company,
    "Address"           =>  \Auth::user()->address,
    "Email"             =>  \Auth::user()->email,
    "Country"           =>  \Auth::user()->country,
    "State"             => \Auth::user()->state,
    "City"              => \Auth::user()->town,
    "Zip"               => \Auth::user()->zip,
    "Currency"          => \Auth::user()->currency,
    "Amount Paid"   => $invoice->grand_total,
    "Exchange Rate"   =>  $exchangeRate,



    "merchant_order_id" =>  $merchant_orderid,
    ],
    "theme"             => [
    "color"             => "#F37254"
    ],
    "order_id"          => $razorpayOrderId,
];

if ($displayCurrency !== 'INR')
{
    $data['display_currency']  = 'USD';
    $data['display_amount']    =$invoice->grand_total;
    
}
$json = json_encode($data);


 $currency = \Auth::user()->currency;




?>
<div class="row">

    <div class="col-md-8">


         <div class="card card-default" style="margin-bottom: 40px;">
            <div class="card-header">
                <h4 class="card-title m-0">
                    {{ __('message.payment')}}
                </h4>
            </div>


            <div class="panel-body">

    
       
        
                <div>
                    <table class="shop_table cart">
                        <thead>
                            <tr>
                                <th class="product-thumbnail">
                                    &nbsp;
                                </th>

                                <th class="product-name">
                                    {{ __('message.product')}}
                                </th>
                                <th class="product-invoice">
                                    {{ __('message.invoice_no')}}.
                                </th>
                                <th class="product-version">
                                    {{ __('message.version')}}
                                </th>

                                <th class="product-quantity">
                                    {{ __('message.quantity')}}
                                </th>
                                <th class="product-total">
                                    {{ __('message.total')}}
                                </th>
                            </tr>
                        </thead>
                        <tbody>
                             @forelse(Cart::getContent() as $item)
                               @php
                            $cartSubtotalWithoutCondition += $item->getPriceSum();
                            @endphp
                            <tr class="cart_table_item">

                                <td class="product-thumbnail">
                                    
                                    <img width="100" height="100" alt="" class="img-responsive" src="{{$item->associatedModel->image}}">

                                </td>

                                <td class="product-name">
                                    {{$item->name}}
                                </td>
                                <td class="product-invoice">
                                    <a href="{{url('my-invoice/'.$invoice->id)}}" target="_blank">{{$invoice->number}}</a>
                                </td>
                                <td class="product-version">
                                    @if($item->associatedModel->version)
                                    {{$item->associatedModel->version}}
                                    @else
                                        {{ __('message.not_available')}}
                                    @endif
                                </td>
                                 

                                <td class="product-quantity">
                                    {{$item->quantity}}
                                </td>
                                <td class="product-total">
                                    <span class="amount">{{currencyFormat($item->price,$code = $item->attributes->currency)}}</span>
                                </td>
                            </tr>
                            @empty 
                        <p>{{ __('message.cart_void')}}</p>
                        @endforelse
                        


                    </table>
                    
                    

                </div>
               </div>
        </div>
    </div>
    <div class="col-md-4">
         
        <h4 class="heading-primary">{{ __('message.cart_total')}}</h4>
        <table class="cart-totals">
            <tbody>
                <tr class="cart-subtotal">
                  
                    <th>
                        <strong>{{ __('message.cart_subtotal')}}</strong>
                    </th>
                    <td>
                        <span class="amount">{{currencyFormat($cartSubtotalWithoutCondition,$code = $currency)}}</span>
                    </td>
                </tr>
                 @if(Session::has('code'))
                  <tr class="cart-subtotal">

                    <th>
                        <strong>{{ __('message.discount')}}</strong>
                    </th>
                    <td>
                         {{currencyFormat(\Session::get('codevalue'),$code = $item->attributes->currency)}}
                    </td>
                </tr>
                @endif
               
                @if(count(\Cart::getConditionsByType('tax')) == 1)
                @foreach(\Cart::getConditionsByType('tax') as $tax)



                 @if($tax->getName()!= 'null')
                <tr class="Taxes">
                    <?php
                    $bifurcateTax = bifurcateTax($tax->getName(),$tax->getValue(),$item->attributes->currency, \Auth::user()->state, \Cart::getContent()->sum('price'));
                    ?>
                   <th>
                        
                        <strong>{!! $bifurcateTax['html'] !!}</strong><br/>

                    </th>
                    <td>
                     {!! $bifurcateTax['tax'] !!}
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
                        
                        <strong>{!! $bifurcateTax['html'] !!}</strong><br/>

                    </th>
                    <td>
                     {!! $bifurcateTax['tax'] !!}
                  </td>
                  
                   
                </tr>
                @endif
                
                @endforeach
               @endif
                     
                <tr class="total">
                    <th>
                        <strong>{{ __('message.order_total')}}</strong>
                    </th>
                    <td>
                    <strong><span class="amount">{{currencyFormat(\Cart::getTotal(),$code = $item->attributes->currency)}} </span></strong>


                    </td>
                </tr>

            </tbody>

        </table>
        <br />
        <div class="form-group">
                   <div class="col-md-12" id="not-razor">
        <input type="submit" name="submit" value="Place Your Order And Pay" id="rzp-button1" class="btn btn-primary " data-loading-text="Loading..." style="width:100%">
    </div>
                </div>
    </div>
</div>
 <script src="https://checkout.razorpay.com/v1/checkout.js"></script>
 <form name='razorpayform' action="{!!url('payment/'.$invoice->id)!!}" method="POST">
      {{ csrf_field() }}
 <!--<button id="rzp-button1" class="btn btn-primary pull-right mb-xl" data-loading-text="Loading...">Pay Now</button>-->
<!--<form name='razorpayform' action="verify.php" method="POST">                                -->
<input type="hidden" name="razorpay_payment_id" id="razorpay_payment_id">
<input type="hidden" name="razorpay_signature"  id="razorpay_signature" >


</form>

 <script>

    // Checkout details as a json
var options = <?php echo $json; ?>


/**
 * The entire list of Checkout fields is available at
 * https://docs.razorpay.com/docs/checkout-form#checkout-fields
 */
options.handler = function (response){
    document.getElementById('razorpay_payment_id').value = response.razorpay_payment_id;
    document.getElementById('razorpay_signature').value = response.razorpay_signature;
   
    document.razorpayform.submit();
};

// Boolean whether to show image inside a white frame. (default: true)
options.theme.image_padding = false;

options.modal = {
    ondismiss: function() {
    },
    // Boolean indicating whether pressing escape key 
    // should close the checkout form. (default: true)
    escape: true,
    // Boolean indicating whether clicking translucent blank
    // space outside checkout form should close the form. (default: false)
    backdropclose: false
};

var rzp = new Razorpay(options);

document.getElementById('rzp-button1').onclick = function(e){
    
    rzp.open();
    e.preventDefault();
}
</script>


@endsection
