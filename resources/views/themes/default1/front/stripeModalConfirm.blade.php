@extends('themes.default1.layouts.front.master')

@section('title', 'Stripe')

@section('page-header', 'Cart')

@section('page-heading', 'Stripe/Confirmation')

@section('breadcrumb')
@if(Auth::check())
    <li><a class="text-primary" href="{{url('my-invoices')}}">{{ __('message.home')}}</a></li>
@else
    <li><a class="text-primary" href="{{url('login')}}">{{ __('message.home')}}</a></li>
@endif
    <li class="active text-dark">{{ __('message.stripe')}}</li>
@stop

@section('main-class', 'main shop')

@section('content')  
@php
function getFlagIconByCardBrand($cardBrand) {
    switch ($cardBrand) {
        case 'visa':
            return 'https://static.vecteezy.com/system/resources/previews/020/975/567/non_2x/visa-logo-visa-icon-transparent-free-png.png';
        case 'mastercard':
            return 'https://pngimg.com/d/mastercard_PNG23.png';
        case 'amex':
            return 'https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcQ2lfp0fkZmeGd6aCOzuIBC1QDTvcyGcM6OGQ&usqp=CAU';
        default:
            return 'path/to/default-flag-icon.png'; // Default flag icon for unknown brands 
    }
}
@endphp
 <div class="container py-4">

            <div class="row justify-content-center">

                <div class="col-lg-8">
    <div class="card border-width-3 border-radius-0 border-color-hover-dark mb-4">

                        <div class="card-body">

                            <h4 class="font-weight-bold text-uppercase text-4 mb-3">{{ __('message.your_order_confirmation')}}</h4>

                            <table class="shop_table cart-totals mb-0">

                                <tbody>
                                   

                                <tr>
                                    <td colspan="2" class="border-top-0">
                                        <strong class="text-color-dark">{{ __('message.product')}}</strong>
                                    </td>
                                </tr>
                                 @foreach($invoice_item as $item)

                                <tr>
                                    <td>
                                        <strong class="d-block text-color-dark line-height-1 font-weight-semibold">{{$item->product_name}} <span class="product-qty">x {{$item->quantity}}</span></strong>
                                    </td>
                                    <td class="text-end align-top">
                                        <span class="amount font-weight-medium text-color-grey">{{currencyFormat($item->regular_price,$code = $invoice->currency)}}</span>
                                    </td>
                                </tr>

                               @endforeach
                               <tr class="cart-subtotal">
                                    <td class="border-top-0">
                                        <strong class="text-color-dark">{{ __('message.sub_total')}}</strong>
                                    </td>
                                    <td class="border-top-0 text-end">
                                        <strong><span class="amount font-weight-medium">{{currencyFormat($invoice->grand_total,$code = $invoice->currency)}}</span></strong>
                                    </td>
                                </tr>

                                <tr class="total">
                                    <td>
                                        <strong class="text-color-dark text-3-5">{{ __('message.total')}}</strong>
                                    </td>
                                    @php
                                    $cardBrandIcon = getFlagIconByCardBrand($details->brand);

                                    @endphp
                                    <td class="text-end">
                                        <strong class="text-color-dark"><span class="amount text-color-dark text-5">{{currencyFormat($invoice->grand_total,$code = $invoice->currency)}}</span></strong><br>
                                       <strong class="d-block text-color-dark line-height-1 font-weight-bold"><span class="amount text-color-dark text-3">{{ __('message.paid_with')}}
                                          @if($cardBrandIcon)
                                            <img class="img-responsive" src="{{ $cardBrandIcon }}" alt="{{ $details->brand }} Icon" width="25" height="25">
                                        @endif
                                        <span class="text-color-dark">**** **** **** {{ $details->last4 }}</span>
                                       </span>
                                       </strong>
                                    

                                    </td>
                                    
                                    
                                </tr>
                                </tbody>
                            </table>
                            <input type="hidden" id="invoiceData" value="{{$invoice->id}}">

                        </div>
                        
                    </div>
                    </div>
                    </div>
                    
                     <div class="row justify-content-end">
                        <div class="col-lg-8">
                            <!-- Use a form for submission -->
                            <form id="stripeConfirmForm" action="{{ url('final/stripe') }}" method="POST">
                                @csrf
                                <input type="hidden" name="payment_intent" value="{{ $status }}">
                                <input type="hidden" name="invoice" value="{{ $invoice->id }}">
                                <button type="submit" class="btn btn-dark btn-modern w-50 text-uppercase text-3 py-3">
                                    {{ __('message.finish')}} <i class="fas fa-arrow-right ms-2"></i>
                                </button>
                            </form>
                        </div>
                    </div>
                    </div>
@stop

@section('script')

@stop
