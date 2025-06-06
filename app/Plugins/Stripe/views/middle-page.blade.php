@extends('themes.default1.layouts.front.master')
@section('title')
    {{$gateway}}
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
 <li class="active text-dark">{{$gateway}}</li>
@stop
 <style>
        .horizontal-images {
            display: flex;
            justify-content: flex-end;
            align-items: center;
        }
        .horizontal-images img {
            height: auto;
            width: 12%;
            margin-right: 5px;
        }
        .custom-close {
        position: absolute;
        top: -20px;
        right: -20px;
        width: 30px;
        height: 30px;
        background-color: red;
        border-radius: 50%;
        border: none;
        cursor: pointer;
        display: flex;
        justify-content: center;
        align-items: center;
        color: white !important;
        font-size: 20px;
      }
      
    .totals-cart{
       margin-left: -90px !important;
   }
   .img-fluid{
       max-width: 300px !important;
   }
     .border-top{
            border-bottom: 0.5px solid #000;
            border-color: lightgrey;

        }

        #card-number, #card-expiry, #card-cvc {
            padding: 0.375rem 0.75rem;
            border: 1px solid #ced4da;
            border-color: rgba(0, 0, 0, 0.09);
            height: calc(1.5em + 0.75rem + 2px);
            min-height: calc(1.5em + 1rem + 2px);
            display: block;
            width: 100%;
            font-size: 1rem;
            font-weight: 400;
            line-height: 1.5;
            color: #212529;
            background-color: #fff;
            background-clip: padding-box;
            border-top-color: rgb(206, 212, 218);
            border-right-color: rgb(206, 212, 218);
            border-bottom-color: rgb(206, 212, 218);
            border-left-color: rgb(206, 212, 218);
            -webkit-appearance: none;
            -moz-appearance: none;
            appearance: none;
            border-radius: .375rem;
            transition: border-color .15s ease-in-out,box-shadow .15s ease-in-out;
            align-content: center;
        }
        .StripeElement--invalid {
            border: 1px solid #df1b41 !important;
        }
 </style>
<script src="https://js.stripe.com/v3/"></script>

@section('main-class') "main shop" @stop
@section('content')
<style>
 .fa-1x {
            font-size: 17px;
            margin-right: 5px;
            margin-top: 2px;
        }
    .heading {
        font-size: 14px;
    }

</style>

<?php
 $taxAmt = 0;
$cartSubtotalWithoutCondition = 0;
$currency = $invoice->currency;

$processingFee = \DB::table(strtolower('stripe'))->where('currencies',$currency)->value('processing_fee');
$processingFee = (float) $processingFee / 100;

$feeAmount = intval(ceil($invoice->grand_total*$processingFee));
?>
@if($regularPayment)
 <div role="main" class="main">

            <div class="container shop py-3">

                <div class="row">

                    <div class="col-lg-7 mb-4 mb-lg-0">

                        <form method="post" action="">

                            <div class="totals-cart">

                                <table class="shop_table cart">

                                    <thead>

                                    <tr class="text-color-dark">

                                        <th class="product-thumbnail">
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

                                        <th class="product-subtotal text-uppercase heading">

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

                                                <a  onclick="removeItem('{{$item->id}}');" class="product-thumbnail-remove"  data-bs-toggle="tooltip" title="{{ __('message.remove_product') }}" style="top: -15px;right: 15px;">

                                                    <i class="fas fa-times"></i>
                                                </a>

                                                <span class="product-thumbnail-image">

                                                        <img width="90" height="90" alt="" class="img-fluid" src="{{$item->associatedModel->image}}"   data-bs-toggle="tooltip" title="{{$item->name}}">
                                                    </span>
                                            </div>
                                        </td>

                                        <td class="product-name">

                                            <span class="font-weight-semi-bold text-color-dark" style="font-family: Arial;"> {{$item->name}}</span>
                                        </td>
                            
                                        <td class="product-quantity">

                                            <span class="amount font-weight-medium text-color-grey">{{$item->quantity}}</span>
                                        </td>
                                        <td class="product-agent">

                                            <span class="amount font-weight-medium text-color-grey">{{($item->attributes->agents)?$item->attributes->agents:'Unlimited'}}
                                            </span>
                                        </td>


                                        <td class="product-subtotal">
                                            @if(\Session::has('togglePrice') && $item->id == \Session::get('productid'))

                                            <span class="amount text-color-dark font-weight-bold text-4" style="font-family: Arial;">
                                                {{currencyFormat($item->quantity * \Session::get('togglePrice'),$code = $item->attributes->currency)}}
                                            </span>
                                            @else
                                            <span class="amount text-color-dark font-weight-bold text-4" style="font-family: Arial;">
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
                                                        <th class="d-block font-weight-medium text-color-grey ">{{ $cgst }}
                                                            <label style="font-size: 12px;font-weight: normal;">({{$percentage}})</label>
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
                                                        <th class="d-block font-weight-semibold text-color-grey ">{{ $cgst }}
                                                            <label style="font-size: 12px;font-weight: normal;">({{$percentage}})</label>
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
                                                     <tr>
                                                         <td class="border-top-0">
                                            <strong class="d-block text-color-grey  font-weight-semibold">{!! $fee->getName() !!}
                                                <label style="font-size: 12px;font-weight: normal;">({!! $fee->getValue() !!})</label>
                                            </strong>
                                        </td>
                                         @if($fee->getValue() === '0%')
                                                    <td class="text-end align-top border-top-0">
                                                        <span class="amount font-weight-medium text-color-grey">
                                                            0
                                                        </span>
                                                    </td>
                                                @else
                                                    <td class="text-end align-top border-top-0">
                                                        <span class="amount font-weight-medium text-color-grey">
                                                            {{ currencyFormat($feeAmount, $code = $item->attributes->currency) }}
                                                        </span>
                                                    </td>
                                                @endif
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

                              <input type="submit" name="submit" value="Place Your Order And Pay" id="{{ strtolower($gateway) === 'stripe' ? 'stripe-button1' : 'rzp-button1' }}" class="btn btn-dark btn-modern w-100 text-uppercase text-3 py-3" data-loading-text="{{ __('message.loading') }}">


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

                                <table class="shop_table cart">

                                    <thead>

                                    <tr class="text-color-dark">

                                        <th class="product-thumbnail" width="">
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

                        <div class="card border-width-3 border-radius-0 border-color-hover-dark" data-plugin-sticky data-plugin-options="{'minWidth': 991, 'containerSelector': '.row', 'padding': {'top': 85}}">

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
                                                        <th class="d-block font-weight-semibold text-color-grey ">{{ $cgst }}
                                                            <label style="font-size: 12px;font-weight: normal;">({{$percentage}})</label>
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
                                            <strong class="d-block text-color-dark line-height-1 font-weight-semibold">{{ __('message.amount_received') }}</strong>
                                        </td>
                                           <td class="border-top-0 text-end">
                                            <span class="amount font-weight-medium">

                                            {{currencyFormat($paid,$code = $currency)}}
                                        </span>
                                        </td>
                                    </tr>

                                    <tr class="total">
                                        <td class="border-top-0">
                                            <strong class="d-block text-color-dark line-height-1 font-weight-semibold">{{ __('message.balance') }}</strong>
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
                                             <label style="font-size: 12px;font-weight: normal;">({!! $fee->getValue() !!})</label>
                                                </td>
                                              @if($fee->getValue() === '0%')
                                            <td class="align-top border-top-0">
                                                <span class="amount font-weight-medium text-color-grey">
                                                    0
                                                </span>
                                            </td>
                                        @else
                                            <td class="text-end align-top border-top-0">
                                                <span class="amount font-weight-medium text-color-grey">
                                                    {{ currencyFormat($feeAmount, $code = $item->attributes->currency) }}
                                                </span>
                                            </td>
                                        @endif
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
                              <input type="submit" name="submit" value="Place Your Order And Pay" id="{{ strtolower($gateway) === 'stripe' ? 'stripe-button1' : 'rzp-button1' }}" class="btn btn-dark btn-modern w-100 text-uppercase text-3 py-3" data-loading-text="{{ __('message.loading') }}">

                            </div>
                        </div>
                    </div>

                </div>
            </div>
        </div>
        @endif


        <div class="modal fade" id="stripeModal" data-keyboard="false" data-backdrop="static">
    <div class="modal-dialog">
        <div class="modal-content" style="padding: 16px;">
             <div class="modal-header">
            <button style="position: absolute; top: -10px; right: -10px; width: 30px; height: 30px; border-radius: 50%; background-color: black;" type="button" class="close custom-close" aria-hidden="true">&times;</button>
                <h4 style="white-space: nowrap;" class="modal-title" id="defaultModalLabel">{{ __('message.enter_card_details') }}</h4>

         <div class="horizontal-images">
        <img class="img-responsive" src="https://static.vecteezy.com/system/resources/previews/020/975/567/non_2x/visa-logo-visa-icon-transparent-free-png.png">
        <img class="img-responsive" src="https://pngimg.com/d/mastercard_PNG23.png">
        <img class="img-responsive" src="https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcQ2lfp0fkZmeGd6aCOzuIBC1QDTvcyGcM6OGQ&usqp=CAU">
    </div>


               
            </div>
            <div class="col-md-12 ">
            <div class="modal-body">
                <div id="card-errors"></div>
                <form id="payment-form" class="mx-auto" style="max-width: 500px;">
                    <!-- Card Number Field (with built-in Stripe icon) -->
                    <div class="mb-3">
                        <label for="card-number" class="form-label">{{ __('message.card_number') }}</label>
                        <div id="card-number" class="StripeElement"></div>
                        <div id="card-number-errors" class="text-danger mt-1" role="alert"></div>
                    </div>

                    <!-- Row for Expiry Date and CVC -->
                    <div class="row mb-3">
                        <!-- Expiry Date Field -->
                        <div class="col-md-6 mb-3">
                            <label for="card-expiry" class="form-label">{{ __('message.expiry_date') }}</label>
                            <div id="card-expiry" class="StripeElement"></div>
                            <div id="card-expiry-errors" class="text-danger mt-1" role="alert"></div>
                        </div>

                        <!-- CVC Field -->
                        <div class="col-md-6 mb-3">
                            <label for="card-cvc" class="form-label">{{ __('message.card_cvc') }}</label>
                            <div id="card-cvc" class="StripeElement"></div>
                            <div id="card-cvc-errors" class="text-danger mt-1" role="alert"></div>
                        </div>
                    </div>

                    <!-- Total Summary -->
                    <div class="d-grid mb-4">
                        <div class="btn btn-lg btn-outline-dark disabled" style="pointer-events: none;">
                            <div class="d-flex justify-content-between w-100">
                                <span>{{ __('message.total') }}</span>
                                <span id="order-total">{{ currencyFormat($amount, $code=$currency) }}</span>
                            </div>
                        </div>
                    </div>
                    <div class="form-group row">
                        <div class="col-md-12">
                            <button type="submit" id="pay_now" class="btn btn-primary btn-block">
                                {{ __('message.caps_pay_now') }}
                            </button>
                        </div>
                    </div>
                </form>
                <form id="token-form" method="POST" action="{{ url('stripe') }}">
                    <input type="hidden" id="stripe-token" name="stripeToken">
                </form>
            </div>
        </div>
        </div>
    </div>
</div>

<script src="https://js.stripe.com/v3/"></script>
<script>
    // Initialize Stripe
    const stripe = Stripe("{{ $stripe_key }}",{
        locale: 'en' // Set locale if needed
    });

    // Define appearance options as per Stripe's Appearance API docs
    const appearance = {
        theme: 'stripe',
        variables: {
            fontFamily: 'Arial, sans-serif',
            fontSizeBase: '16px',
            colorPrimary: '#0570de',
            colorBackground: '#ffffff',
            colorText: '#30313d',
            colorDanger: '#df1b41',
            borderRadius: '4px'
        },
        rules: {
            '.Input': { padding: '10px' },
            '.StripeElement--invalid': {
                borderColor: '#df1b41',
                borderWidth: '1px',
                borderStyle: 'solid'
            }
        }
    };

    // Create an instance of Elements with the appearance configuration
    const elements = stripe.elements({ appearance });

    // Create card elements
    const cardNumber = elements.create('cardNumber', {
        showIcon: true,
        iconStyle: 'solid'
    });
    cardNumber.mount('#card-number');

    const cardExpiry = elements.create('cardExpiry');
    cardExpiry.mount('#card-expiry');

    const cardCvc = elements.create('cardCvc');
    cardCvc.mount('#card-cvc');

    // Helper function to handle error events for each element
    function setupErrorHandling(element, errorElementId, containerId) {
        element.addEventListener('change', (event) => {
            const errorDiv = document.getElementById(errorElementId);
            const container = document.getElementById(containerId);
            if (event.error) {
                errorDiv.textContent = event.error.message;
                container.classList.add('StripeElement--invalid');
            } else {
                errorDiv.textContent = '';
                container.classList.remove('StripeElement--invalid');
            }
        });
    }

    // Set up error handling for each field
    setupErrorHandling(cardNumber, 'card-number-errors', 'card-number');
    setupErrorHandling(cardExpiry, 'card-expiry-errors', 'card-expiry');
    setupErrorHandling(cardCvc, 'card-cvc-errors', 'card-cvc');

    // Handle form submission and generate Stripe token
    const form = document.getElementById('payment-form');

    let alertTimeout;

    function showAlert(type, messageOrResponse) {

        // Generate appropriate HTML
        var html = generateAlertHtml(type, messageOrResponse);

        // Clear any existing alerts and remove the timeout
        $('#card-errors').html(html);
        clearTimeout(alertTimeout); // Clear the previous timeout if it exists

        // Display alert
        window.scrollTo(0, 0);

        // Auto-dismiss after 5 seconds
        alertTimeout = setTimeout(function() {
            $('#card-errors .alert').fadeOut('slow');
        }, 5000);
    }


    function generateAlertHtml(type, response) {
        // Determine alert styling based on type
        const isSuccess = type === 'success';
        const iconClass = isSuccess ? 'fa-check-circle' : 'fa-ban';
        const alertClass = isSuccess ? 'alert-success' : 'alert-danger';

        // Extract message and errors
        const message = response.message || response || 'An error occurred. Please try again.';
        const errors = response.errors || null;

        // Build base HTML
        let html = `<div class="alert ${alertClass} alert-dismissible">` +
            `<i class="fa ${iconClass}"></i> ` +
            `${message}` +
            '<button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>';

        html += '</div>';

        return html;
    }
    form.addEventListener('submit', async (event) => {
        event.preventDefault();

        // Generate a token using the card number element
        const { token, error } = await stripe.createToken(cardNumber);

        if(token) {
            var $payButton = $("#pay_now");
            $payButton.prop("disabled", true);
            $payButton.html("<i class='fa fa-circle-o-notch fa-spin fa-1x'></i> " + @json(__('message.processing')));
            document.getElementById('stripe-token').value = token.id;
            document.getElementById('token-form').submit();
        }
        else if (error) {
            showAlert('error', error);
        }
    });
</script>
<script>
     $('.custom-close').click(function(){
               location.reload();
               });


</script>
<script>
    $('#stripe-button1').on('click',function(){
        $('#stripeModal').modal('show');
    })

$(function() {
    var $form         = $(".require-validation");
  $('form.require-validation').bind('submit', function(e) {
    var $form         = $(".require-validation"),
        inputSelector = ['input[type=email]', 'input[type=password]',
                         'input[type=text]', 'input[type=file]',
                         'textarea'].join(', '),
        $inputs       = $form.find('.required').find(inputSelector),
        $errorMessage = $form.find('div.error'),
        valid         = true;
        $errorMessage.addClass('hide');
 
        $('.has-error').removeClass('has-error');
    $inputs.each(function(i, el) {
      var $input = $(el);
      if ($input.val() === '') {
        $input.parent().addClass('has-error');
        $errorMessage.removeClass('hide');
        e.preventDefault();
      }
    });

  });
  
  function stripeResponseHandler(status, response) {
        if (response.error) {
            $('.error')
                .removeClass('hide')
                .find('.alert')
                .text(response.error.message);
        } else {
            // token contains id, last4, and card type
            var token = response['id'];
            // insert the token into the form so it gets submitted to the server
            $form.find('input[type=text]').empty();
            $form.append("<input type='hidden' name='stripeToken' value='" + token + "'/>");
            $form.get(0).submit();
        }
    }
  
});
</script>

 <script src="https://checkout.razorpay.com/v1/checkout.js"></script>
 <form name='razorpayform' action="{!!url('payment/'.$invoice->id)!!}" method="POST">
      {{ csrf_field() }}
 <!--<button id="rzp-button1" class="btn btn-primary pull-right mb-xl" data-loading-text="Loading...">Pay Now</button>-->
<!--<form name='razorpayform' action="verify.php" method="POST">                                -->
<input type="hidden" name="razorpay_payment_id" id="razorpay_payment_id">
<input type="hidden" name="razorpay_signature"  id="razorpay_signature" >


</form>
@if($gateway === 'Razorpay')
    <?php
        $json = $data['json'];
    ?>
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
@endif


@endsection
