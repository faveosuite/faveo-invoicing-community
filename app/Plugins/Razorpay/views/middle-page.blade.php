@extends('themes.default1.layouts.front.master')
@section('title')
    {{ __('message.razorpay') }}
@stop
@section('page-heading')
    {{ __('message.place_order') }}
@stop
@section('page-heading')
 {{ __('message.checkout') }}
@stop
@section('breadcrumb')
@if(Auth::check())
        <li><a class="text-primary" href="{{url('my-invoices')}}">{{ __('message.home') }}</a></li>
@else
     <li><a class="text-primary" href="{{url('login')}}">{{ __('message.home') }}</a></li>
@endif
 <li class="active text-dark">{{ __('message.razorpay') }}</li>
@stop
@section('main-class') "main shop" @stop
@section('content')
<style type="text/css">
     .border-top{
            border-bottom: 0.5px solid #000;
            border-color: lightgrey;

        }

   [dir="rtl"] #place_order_razor {
       margin-right : 104px !important;
     }

</style>
<?php
$taxAmt = 0;
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
$currency = $invoice->currency;
if (\App\User::where('id',\Auth::user()->id)->value('billing_pay_balance') && $regularPayment) {
    $amt_to_credit = \DB::table('payments')
    ->where('user_id', \Auth::user()->id)
    ->where('payment_method','Credit Balance')
    ->where('payment_status','success')
    ->where('amt_to_credit','!=',0)
    ->value('amt_to_credit');
    if ($invoice->grand_total <= $amt_to_credit) {
        $cartTotal = 0;
    } else {
        $cartTotal = $invoice->grand_total - $amt_to_credit;
    }
} else {
    $cartTotal = $invoice->grand_total;
}


if ($currency == 'INR'){
$orderData = [
'receipt'         => '3456',
'amount'          => round($cartTotal*100), // 2000 rupees in paise

'currency'        => 'INR',
'payment_capture' => 0 // auto capture
 
];


} else {

 $orderData = [
'receipt'         => '3456',
'amount'          =>  round($cartTotal*100), // 2000 rupees in paise

'currency'        => $currency,
'payment_capture' => 0 // auto capture
     
];
}
$razorpayOrder = $api->order->create($orderData);
$razorpayOrderId = $razorpayOrder['id'];




$data = [
    "key"               => $rzp_key,
    "name"              => 'Faveo Helpdesk',
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
    "Amount Paid"       => $cartTotal*100,



    "merchant_order_id" =>  $merchant_orderid,
    ],
    "theme"             => [
    "color"             => "#F37254"
    ],
    "order_id"          => $razorpayOrderId,
];


$json = json_encode($data);




?>
@if($regularPayment)
 <div role="main" class="main">

            <div class="container shop py-3">

                <div class="row">

                    <div class="col-lg-7 mb-4 mb-lg-0">

                        <form method="post" action="">

                            <div class="totals-cart">

                                <table class="shop_table">

                                    <thead>

                                    <tr class="text-color-dark">

                                        <th class="product-thumbnail" width="15%">
                                            &nbsp;
                                        </th>

                                        <th class="product-name text-uppercase" width="">

                                            {{ __('message.product') }}

                                        </th>
                                    

                                        <th class="product-quantity text-uppercase" width="">

                                            {{ __('message.quantity') }}
                                        </th>
                                         <th class="product-agent text-uppercase" width="">

                                             {{ __('message.agents') }}
                                        </th>

                                        <th class="product-subtotal text-uppercase" width="">

                                            {{ __('message.total') }}
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

                                            <div class="product-thumbnail-wrapper">

                                                <a  onclick="removeItem('{{$item->id}}');" class="product-thumbnail-remove"  data-bs-toggle="tooltip" title="{{ __('message.remove_product') }}" style="top: -15px;left: 70px;">

                                                    <i class="fas fa-times"></i>
                                                </a>

                                                <span class="product-thumbnail-image"  data-bs-toggle="tooltip" title="{{ __('message.faveo_enterprise_advance') }}">

                                                        <img width="90" height="90" alt="" class="img-fluid" src="{{$item->associatedModel->image}}">
                                                    </span>
                                            </div>
                                        </td>

                                        <td class="product-name">

                                            <span class="font-weight-semi-bold text-color-dark"> {{$item->name}}</span>
                                        </td>
                             

                                        <td class="product-quantity">

                                            <span class="amount font-weight-medium text-color-grey">{{$item->quantity}}</span>
                                        </td>
                                        <td class="product-agent">

                                            <span class="amount font-weight-medium text-color-grey">{{($item->attributes->agents)?$item->attributes->agents:'Unlimited'}}
                                            </span>
                                        </td>


                                        <td class="product-subtotal" style="font-family: Arial;">
                                            @if(\Session::has('togglePrice') && $item->id == \Session::get('productid'))

                                            <span class="amount text-color-dark font-weight-bold text-4">
                                                {{currencyFormat($item->quantity * \Session::get('togglePrice'),$code = $item->attributes->currency)}}
                                            </span>
                                            @else
                                            <span class="amount text-color-dark font-weight-bold text-4">
                                                {{currencyFormat($item->quantity * $item->price,$code = $item->attributes->currency)}}
                                            </span>
                                            @endif
                                        </td>
                                    </tr>
                                     @empty 
                                    <p>{{ __('message.cart_void') }}</p>


                                    @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </form>
                    </div>

                    <div class="col-lg-5 position-relative">

                        <div class="card border-width-3 border-radius-0 border-color-hover-dark" data-plugin-sticky data-plugin-options="{'minWidth': 991, 'containerSelector': '.row', 'padding': {'top': 85}}">

                            <div class="card-body">

                                <h4 class="font-weight-bold text-uppercase text-4 mb-3">{{ __('message.your_order') }}</h4>

                                <table class="shop_table cart-totals mb-3">

                                    <tbody>

                                    <tr class="border-top">
                                        <td class="border-top-0">
                                            <strong class="d-block text-color-dark line-height-1 font-weight-semibold">{{ __('message.cart_subtotal') }}</strong>
                                        </td>
                                        <td class="text-end align-top border-top-0">
                                            <span class="amount font-weight-medium text-color-grey">{{currencyFormat($cartSubtotalWithoutCondition,$code = $currency)}}</span>
                                        </td>
                                    </tr>
                                    @if(Session::has('code'))
                                       <tr>
                                        <td class="border-top-0">
                                            <strong class="d-block text-color-dark line-height-1 font-weight-semibold">{{ __('message.discount') }}</strong>
                                        </td>
                                        <td class="text-end align-top border-top-0">
                                            <span class="amount font-weight-medium text-color-grey">
                                                <?php
                                                if (strpos(\Session::get('codevalue'), '%') == true) {
                                                        $discountValue = \Session::get('codevalue');
                                                    } else {
                                                        $discountValue = currencyFormat(\Session::get('codevalue'),$code = $item->attributes->currency);
                                                    }
                                                ?>

                                                {{$discountValue}}
                                            </span>
                                        </td>
                                    </tr>
                                    @endif

                                                   @if(count(\Cart::getConditionsByType('tax')) == 1)
                                                    @foreach(\Cart::getConditionsByType('tax') as $tax)
                                                    @if($tax->getName()!= 'null')
                                                    <?php
                                                        $bifurcateTax = bifurcateTax($tax->getName(), $tax->getValue(), $item->attributes->currency, \Auth::user()->state, \Cart::getContent()->sum('price'));
                                                        $partsHtml = explode('<br>', $bifurcateTax['html']);
                                                        $taxParts = explode('<br>', $bifurcateTax['tax']);
                                                        ?>
                                                     @foreach($partsHtml as $index => $part)
                                                      @php
                                                        $parts = explode('@', $part);
                                                        $cgst = $parts[0];
                                                        $percentage = $parts[1]; 
                                                    @endphp
                                                    <tr class="Taxes border-top-0 border-bottom">
                                                        <th class=""><strong class="d-block text-color-grey font-weight-semibold">{{ $cgst }}
                                                             <label style="font-size: 12px;font-weight: normal;">({{$percentage}})</label>
                                                        </strong>
                                                            
                                                        </th>
                                                        <td data-title="CGST" class="text-end align-top border-top-0">
                                                            <span class="align-top border-top-0">
                                                                <span class="amount font-weight-medium text-color-grey"></span>{{ $taxParts[$index] }}
                                                            </span>
                                                        </td>
                                                    </tr>
                                                  @endforeach
                                                    @endif
                                                    @endforeach

                                                    @else
                                                    @foreach(Cart::getContent() as $tax)
                                                    @if($tax->conditions)
                                                    <?php
                                                        $bifurcateTax = bifurcateTax($tax->conditions->getName(),$tax->conditions->getValue(),$item->attributes->currency, \Auth::user()->state, $tax->price*$tax->quantity);

                                                        $partsHtml = explode('<br>', $bifurcateTax['html']);
                                                        $taxParts = explode('<br>', $bifurcateTax['tax']);
                                                        ?>
                                                        @if (strpos($bifurcateTax['html'], 'null') === false)
                                                    @foreach($partsHtml as $index => $part)
                                                      @php
                                                        $parts = explode('@', $part);
                                                        $cgst = $parts[0];
                                                        $percentage = $parts[1]; 
                                                    @endphp
                                                    <tr class="Taxes border-top-0 border-bottom">
                                                        <th class=""><strong class="d-block text-color-grey font-weight-semibold">{{ $cgst }}
                                                             <label style="font-size: 12px;font-weight: normal;">({{$percentage}})</label>
                                                        </strong>
                                                            
                                                        </th>
                                                        <td data-title="CGST" class="text-end align-top border-top-0">
                                                            <span class="align-top border-top-0">
                                                                <span class="amount font-weight-medium text-color-grey"></span>{{ $taxParts[$index] }}
                                                            </span>
                                                        </td>
                                                    </tr>
                                                  @endforeach
                                                  @endif
                                             
                                                    @endif
                                                    
                                                    @endforeach
                                                   @endif
                                                

                                                    @if(count(\Cart::getConditionsByType('fee')))
                                                     @foreach(\Cart::getConditionsByType('fee') as $fee)
                                                     <tr class="border-top">
                                                         <td class="border-top-0">
                                            <strong class="d-block text-color-grey font-weight-semibold">{!! $fee->getName() !!}
                                            </strong>
                                        </td>
                                                          <td class="text-end align-top border-top-0">
                                            <span class="amount font-weight-medium text-color-grey">
                                                        {!! str_replace('%', '', $fee->getValue()) !!}

                                                     </span>
                                                      </td>
                                                     </tr>
                                                     @endforeach
                                                    @endif
                                     @if(\App\User::where('id',\Auth::user()->id)->value('billing_pay_balance'))

                                <tr class="cart-subtotal" style="color: indianred">
                                        <?php
                                        $amt_to_credit = \DB::table('payments')
                                            ->where('user_id', \Auth::user()->id)
                                            ->where('payment_method','Credit Balance')
                                            ->where('payment_status','success')
                                            ->where('amt_to_credit','!=',0)
                                            ->value('amt_to_credit');
                                        if (\Cart::getTotal() <= $amt_to_credit) {
                                            $cartBalance = \Cart::getTotal();
                                        } else {
                                            $cartBalance = $amt_to_credit;
                                        }
                                        ?>

                                  <td class="border-top-0" style="color: indianred">
                                                        <strong class="d-block text-color-dark line-height-1 font-weight-semibold">{{ __('message.amount_pending') }}
                                                        </strong></td>
                                   <td class="text-end align-top border-top-0">
                                                        <span class="amount font-weight-medium text-color-grey">
                                        -{{$dd=currencyFormat($cartBalance,$code = $item->attributes->currency)}}
                                    </span>
                                    </td>
                                </tr>
                                @endif

      

                                    <tr class="total">

                                        <td>
                                            <strong class="text-color-dark text-3-5">{{ __('message.total') }}</strong>
                                        </td>

                                        <?php
                                        if(\App\User::where('id',\Auth::user()->id)->value('billing_pay_balance')) {
                                            if (\Cart::getTotal() <= $amt_to_credit) {
                                                $amount = 0;
                                            } else {
                                                $amount = \Cart::getTotal()-$amt_to_credit;
                                            }
                                        }
                                        ?>
                                        <td class="text-end">
                                            <strong class="text-color-dark"><span class="amount text-color-dark text-5">{{currencyFormat($amount,$code = $item->attributes->currency)}}</span></strong>
                                        </td>
                                    </tr>
                                    </tbody>
                                </table>


                                <input type="submit" name="submit" value="{{ __('message.place_your_order_pay') }}" id="rzp-button1"  class="btn btn-dark btn-modern w-100 text-uppercase text-3 py-3" data-loading-text="{{ __('message.loading') }}">

                            </div>
                        </div>
                    </div>

                </div>
            </div>
        </div>
              @else
         <div role="main" class="main">

            <div class="container shop py-3">

                <div class="row">

                    <div class="col-lg-7 mb-4 mb-lg-0">

                        <form method="post" action="">

                            <div class="totals-cart">

                                <table class="shop_table">

                                    <thead>

                                    <tr class="text-color-dark">

                                        <th class="product-thumbnail" width="15%">
                                            &nbsp;
                                        </th>

                                        <th class="product-name text-uppercase heading" width="">

                                            {{ __('message.product') }}

                                        </th>
                              

                                        <th class="product-quantity text-uppercase heading" width="">

                                            {{ __('message.quantity') }}
                                        </th>
                                         <th class="product-agent text-uppercase heading" width="">

                                            {{ __('message.agents') }}
                                        </th>

                                        <th class="product-subtotal text-uppercase heading" width="">

                                            {{ __('message.total') }}
                                        </th>
                                    </tr>
                                    </thead>

                                    <tbody>
                                     @forelse($items as $item)
                                    @php
                                    $taxName[] =  $item->tax_name.'@'.$item->tax_percentage;
                                    if ($item->tax_name != 'null') {
                                        $taxAmt +=  $item->subtotal;
                                     }
                                    @endphp

                                    <tr class="cart_table_item">

                                        <td class="product-thumbnail">

                                            <div class="product-thumbnail-wrapper">

                                                <span class="product-thumbnail-image"  data-bs-toggle="tooltip" title="{{$item->product_name}}">

                                                        <img width="90" height="90" alt="" class="img-fluid" src="{{$product->image}}">
                                                    </span>
                                            </div>
                                        </td>

                                        <td class="product-name">

                                            <span class="font-weight-semi-bold text-color-dark">{{$item->product_name}}</span>
                                        </td>
                           

                                        <td class="product-quantity">

                                            <span class="amount font-weight-medium text-color-grey">{{$item->quantity}}</span>
                                        </td>
                                        <td class="product-agent">

                                            <span class="amount font-weight-medium text-color-grey">{{($item->agents)?$item->agents:'Unlimited'}}
                                            </span>
                                        </td>


                                        <td class="product-subtotal">

                                            <span class="amount text-color-dark font-weight-bold text-3">
                                                {{currencyFormat($item->regular_price,$code = $currency)}}
                                            </span>

                                        </td>
                                    </tr>
                                     @empty 
                                    <p>{{ __('message.cart_void') }}</p>


                                    @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </form>
                    </div>

                <div class="col-lg-5 position-relative">

                        <div class="card border-width-3 border-radius-0 border-color-hover-dark" id="place_order_razor" data-plugin-sticky data-plugin-options="{'minWidth': 991, 'containerSelector': '.row', 'padding': {'top': 85}}">

                            <div class="card-body">

                                <h4 class="font-weight-bold text-uppercase text-4 mb-3">{{ __('message.your_order') }}</h4>

                                <table class="shop_table cart-totals mb-3">

                                    <tbody>

                                    <tr class="border-top">
                                        <td class="border-top-0">
                                            <strong class="d-block text-color-dark line-height-1 font-weight-semibold">{{ __('message.cart_subtotal') }}</strong>
                                        </td>
                                          <?php 
                                        $subtotals = App\Model\Order\InvoiceItem::where('invoice_id',$invoice->id)->pluck('regular_price')->toArray();
                                        $subtotal = array_sum($subtotals);
                                        ?>
                                        <td class="text-end align-top border-top-0">
                                            <span class="amount font-weight-medium text-color-grey">{{currencyFormat($subtotal,$code = $currency)}}</span>
                                        </td>
                                    </tr>
                                    @php
                                    $taxName = array_unique($taxName);
                                    @endphp

                                     @if(Session::has('code'))
                                      <tr class="cart-subtotal">

                                        <td class="border-top-0">
                                            <strong class="d-block text-color-dark line-height-1 font-weight-semibold">{{ __('message.discount') }}</strong>
                                        </td>
                                        <td class="text-end align-top border-top-0">
                                            <span class="amount font-weight-medium text-color-grey">
                                             {{currencyFormat(\Session::get('codevalue'),$code = $currency)}}</span>
                                        </td>
                                    </tr>
                                    @endif

                                    @foreach($taxName as $tax)
                                      @php
                                      $taxDetails = explode('@', $tax);
                                      @endphp
                                   
                                    @if ($taxDetails[0]!= 'null')
                                                                
                                                           
                                             <?php
                                            $bifurcateTax = bifurcateTax($taxDetails[0],$taxDetails[1],\Auth::user()->currency, \Auth::user()->state, $taxAmt);
                                           $partsHtml = explode('<br>', $bifurcateTax['html']);
                                            $taxParts = explode('<br>', $bifurcateTax['tax']);
                                                        ?>
                                                    
                                                
                                                   @foreach($partsHtml as $index => $part)
                                                    @php
                                                        $parts = explode('@', $part);
                                                        $cgst = $parts[0];
                                                        $percentage = $parts[1]; 
                                                    @endphp
                                                    <tr class="Taxes border-top-0 border-bottom">
                                                        <th><strong class="d-block text-color-grey line-height-1 font-weight-semibold">{{ $cgst }}
                                                             <label style="font-size: 12px;font-weight: normal;">({{$percentage}})</label></strong>
                                                        </th>
                                                        <td data-title="CGST" class="text-end align-top border-top-0">
                                                            <span class="align-top border-top-0">
                                                                <span class="amount font-weight-medium text-color-grey"></span>{{ $taxParts[$index] }}
                                                            </span>
                                                        </td>
                                                    </tr>
                                                  @endforeach
                                   
                                        @endif
                                        @endforeach

                                        @if($paid)

                                        <tr class="total">
                                         <td class="border-top-0">
                                            <strong class="d-block text-color-dark line-height-1 font-weight-semibold">{{ __('message.paid') }}</strong>
                                        </td>
                                           <td class="border-top-0 text-end">
                                            <span class="amount font-weight-medium">

                                            {{currencyFormat($paid,$code = $currency)}}
                                        </span>
                                        </td>
                                    </tr>

                                    <tr class="total">
                                        <td class="border-top-0">
                                            <strong class="d-block text-color-dark line-height-1 font-weight-semibold">{{ __('message.amount_pending') }}</strong>
                                        </td>
                                            <td class="border-top-0 text-end">
                                            <span class="amount font-weight-medium">

                                            {{currencyFormat($amount,$code = $currency)}}
                                        </span>
                                        </td>
                                    </tr>
                                    @endif

                                    @if(\App\User::where('id',\Auth::user()->id)->value('billing_pay_balance'))
                                        <tr class="cart-subtotal" style="color: indianred">
                                             <td class="border-top-0">
                                            <strong class="text-color-dark">{{ __('message.amount_pending') }}</strong>

                                            </td>
                                              <td class="border-top-0 text-end">
                                            <span class="amount font-weight-medium">
                                                -{{$dd=currencyFormat($creditBalance,$code = $currency)}}
                                            </span>
                                            </td>
                                        </tr>
                                    @endif

                                        @if(count(\Cart::getConditionsByType('fee')))
                                        @foreach(\Cart::getConditionsByType('fee') as $fee)
                                            <tr>
                                                 <td class="border-top-0">
                                            <strong class="text-color-grey font-weight-semibold">{!! $fee->getName() !!}</strong>
                                            <br/>

                                                </td>
                                                <td class="border-top-0 text-end">
                                            <span class="amount font-weight-medium">
                                                     {!! str_replace('%', '', $fee->getValue()) !!}
                                                </span>
                                                </td>
                                            </tr>
                                        @endforeach
                                    @endif


                                    <tr class="total">

                                        <td>
                                            <strong class="text-color-dark text-3-5">{{ __('message.total') }}</strong>
                                        </td>
                                        <td class="text-end">
                                            <strong class="text-color-dark"><span class="amount text-color-dark text-5">{{currencyFormat($amount,$code = $currency)}}</span></strong>
                                        </td>
                                    </tr>
                                    </tbody>
                                </table>

                                 <input type="submit" name="submit" value="{{ __('message.place_your_order_pay') }}" id="rzp-button1"  class="btn btn-dark btn-modern w-100 text-uppercase text-3 py-3" data-loading-text="{{ __('message.loading') }}">
                                                             </div>
                        </div>
                    </div>

                </div>
            </div>
        </div>
        @endif

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
    <style>
        strong{
            margin-left: 20px;
        }
    </style>


@endsection
