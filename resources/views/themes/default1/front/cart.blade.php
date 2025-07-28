@extends('themes.default1.layouts.front.master')
@section('title')
    {{ __('message.cart') }}
@stop
@section('page-header')
    <br>
    {{ __('message.cart') }}
@stop
@section('page-heading')
{{ __('message.cart') }}
@stop
@section('breadcrumb')
@if(Auth::check())
        <li><a class="text-primary" href="{{url('my-invoices')}}">{{ __('message.home')}}</a></li>
@else
     <li><a class="text-primary" href="{{url('login')}}">{{ __('message.home')}}</a></li>
@endif
 <li class="active text-dark">{{ __('message.cart')}}</li>
@stop
@section('main-class') "main shop" @stop




@section('content')


    <style>
        #global-loader {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            z-index: 9999;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .cart-info {
            position: relative;
            display: inline-block;
        }

        .cart-qty {
            position: absolute;
            top: -8px;
            right: -10px;
            background-color: #ed5348;
            color: #fff;
            font-size: 10px;
            font-weight: bold;
            padding: 2px 6px;
            border-radius: 10px;
            min-width: 20px;
            text-align: center;
            z-index: 10;
            white-space: nowrap;
            line-height: 1;
        }

        td.product-name {
            max-width: 85px !important;
            white-space: nowrap !important;
            overflow: hidden !important;
            text-overflow: ellipsis !important;
        }
    </style>


    <?php
    $cartTotal = 0;
    ?>
    <div id="global-loader" style="display: none;">
        <div class="global-loader-overlay">
            <img src="{{ asset('lb-faveo/media/images/gifloader3.gif') }}" alt="Loading..." width="60" height="60">
        </div>
    </div>

    <div role="main" class="main shop pb-4">

            <div class="container py-4">
                @if(!Cart::isEmpty())


                <div class="row pb-4 mb-5">


                    <div class="col-lg-8 mb-5 mb-lg-0">

                        <form method="post" action="">

                            <div class="table-responsive">

                                @php
                                $isAgentAllowed = false;
                                $isAllowedtoEdit = false;
                                foreach ($cartCollection as $item) {
                                    $productdetails=$item->associatedModel->getAttributes();
                                    // Your existing logic to calculate $isAgentAllowed and $isAllowedtoEdit
                                    // This part should remain the same as in your original code
                                    $cont = new \App\Http\Controllers\Product\ProductController();
                                    $isAgentAllowed = $cont->allowQuantityOrAgent($productdetails['id']);
                                    $isAllowedtoEdit = $cont->isAllowedtoEdit($productdetails['id']);
                                    break;
                                }
                                @endphp


                                <table class="shop_table cart">


                                    <thead>

                                        <tr class="text-color-dark">

                                            <th class="product-thumbnail" width="">
                                                &nbsp;
                                            </th>

                                            <th class="product-name text-uppercase" width="" style="font-family: Arial;">

                                                {{ __('message.product')}}

                                            </th>

                                            <th class="product-price text-uppercase" width="">

                                                {{ __('message.price')}}
                                            </th>

                                            <th class="product-quantity text-uppercase" width="">

                                                {{ __('message.quantity')}}
                                            </th>
                                            <th class="product-agents text-uppercase" width="">

                                                {{ __('message.agents')}}
                                            </th>


                                            <th class="product-subtotal text-uppercase" width="">

                                                {{ __('message.sub_total')}}
                                            </th>
                                        </tr>
                                    </thead>
                                             @forelse($cartCollection as $item)
                                    @php
                                                $productdetails1=$item->associatedModel->getAttributes();
                                                   if(\Auth::check()) {
                                                   Cart::clearItemConditions($item->id);
                                                   if(\Session::has('code')) {
                                                   \Session::forget('code');
                                                   \Session::forget('usage');
                                                    $cartcont = new \App\Http\Controllers\Front\CartController();
                                                    \Cart::update($item->id, [
                                                     'price'      => $cartcont->planCost($item->id, \Auth::user()->id),
                                                   ]);
                                                 }

                                                 }

                                                    $cartTotal += $item->getPriceSum();;
                                                     $domain = [];
//                                                     if ($item->associatedModel->require_domain) {
                                                        if($productdetails1['require_domain']){
//                                                         $domain[$key] = $item->associatedModel->id;
                                                         $domain[$key] = $productdetails1['id'];
//                                                         $productName = $item->associatedModel->name;
                                                         $productName = $productdetails1['name'];

                                                     }
                                                     $cont = new \App\Http\Controllers\Product\ProductController();
                                                     $isAgentAllowed = $cont->allowQuantityOrAgent($productdetails1['id']);//changed
                                                     $isAllowedtoEdit = $cont->isAllowedtoEdit($productdetails1['id']);//changed
                                            @endphp

                                    <tbody>

                                        <tr class="cart_table_item text-center align-middle">

                                            <td class="product-thumbnail">

                                                <div class="product-thumbnail-wrapper" style="width: 100px;">

                                                    <a onclick="removeItem('{{$item->id}}');" class="product-thumbnail-remove" data-bs-toggle="tooltip" title="{{ __('message.remove_product')}}" style="top: -15px;">

                                                        <i class="fas fa-times"></i>
                                                    </a>

                                                    <span class="product-thumbnail-image" >

                                                        <img width="90" height="90" alt="" class="img-fluid" src="{{$item->associatedModel->image}}"  data-bs-toggle="tooltip" title="{{$item->name}}">
                                                    </span>
                                                </div>
                                            </td>
                                            <td class="product-name">

                                                <span class="font-weight-semi-bold text-color-dark">{{$item->name}}</span>
                                                <br>
                                                <i style="font-size: 12px;">{{$item->attributes->domain}}</i>
                                            </td>

                                            <td class="product-price">
                                                <?php
                                                    $productPrice = $item->price;
                                                    if($isAgentAllowed && $item->toArray()['attributes']['agents']!=0){
                                                        $productPrice = $item->price/$item->toArray()['attributes']['agents'];
                                                    }
                                                    ?>

                                                <span class="amount font-weight-medium text-color-grey">{{$productPrice}}</span>
                                            </td>


                                                <td class="product-quantity">

                                                    @if($isAllowedtoEdit['quantity'])
                                                        <div class="quantity">
                                                            <input type="button" class="quantityminus minus" value="-">

                                                            <input type = "hidden" class="productid" value="{{$productdetails1['id']}}">
                                                            <input type = "hidden" class="planid" value="{{$item->id}}">
                                                            <input type = "hidden" class="quatprice" value=" {{$item->price}}">
                                                            <input type="text" class="input-text qty text quantity-input" title="Qty" value="{{$item->quantity}}" name="quantity" min="1" step="1" disabled>
                                                            <input type="button" class="quantityplus plus" value="+" >
                                                        </div>


                                                        @else
                                                            {{$item->quantity}}

                                                        @endif
                                                    </td>
                                                    <td class="product-agents">

                                                    @if (!$item->attributes->agents)

                                                            {{ __('message.unlimited_agents') }}
                                                    @else
                                                        @if($isAllowedtoEdit['agent'])
                                                            <div class="quantity">
                                                                <input type="button" class="agentminus minus" value="-">
                                                                <input type="hidden" class="initialagent" value="{{$item->attributes->initialagent}}">
                                                                <input type = "hidden" class="currency" value="{{$item->attributes->currency}}">
                                                                <input type = "hidden" class="symbol" value="{{$item->attributes->symbol}}">
                                                                <input type = "hidden" class="productid" value="{{$productdetails1['id']}}">
                                                                <input type = "hidden" class="planid" value="{{$item->id}}">
                                                                <input type = "hidden" class="agentprice" value=" {{$item->getPriceSum()}}">
                                                                <input type="text" class="input-text qty text agent-input" title="Qty" value="{{$item->attributes->agents}}" name="quantity" min="1" step="1" disabled>
                                                                <input type="button" class="agentplus plus" value="+">
                                                            </div>

                                                        @else
                                                            {{$item->attributes->agents}}
                                                        @endif
                                                </td>
                                            @endif


                                            <td class="product-subtotal">

                                                <span class="amount text-color-dark font-weight-bold text-4" style="font-family: Arial;">                                                        {{currencyFormat($item->getPriceSum(),$item->attributes->currency)}}
                                               </span>
                                            </td>
                                        </tr>

                                    </tbody>
                                     @endforeach

                                </table>

                            </div>

                        </form>
                    </div>

                    <div class="col-lg-4 position-relative">

                        <div class="card border-width-3 border-radius-0 border-color-hover-dark" data-plugin-sticky data-plugin-options="{'minWidth': 991, 'containerSelector': '.row', 'padding': {'top': 85}}">

                            <div class="card-body">

                                <h4 class="font-weight-bold text-uppercase text-4 mb-3">{{ __('message.cart_totals')}}</h4>


                                <div class="table-responsive">

                                    <table class="shop_table cart-totals mb-4">

                                        <tbody>

                                            <tr class="total">

                                                <td>
                                                    <strong class="text-color-dark text-3-5" style="font-family: Arial;">{{ __('message.total')}}</strong>
                                                </td>

                                                <td class="text-end">
                                                    <strong class="text-color-dark"><span class="amount text-color-dark text-5"> {{currencyFormat($cartTotal, $item->attributes->currency)}}</span></strong>
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>

                                <div class="row justify-content-between mx-0 flex-wrap">

                                    <div class="col-md-auto px-0 mb-3 mb-md-0">

                                        <div class="d-flex align-items-center mb-2">
                                            <form action="{{url('cart/clear')}}" method="post">
                                            {{ csrf_field() }}

                                             <a href="{{url('cart/clear')}}"><button class="btn btn-light btn-modern text-2 text-uppercase" style="background: #F4F4F4;">{{ __('message.clear_cart')}}</button></a>
                                            </form>
                                        </div>
                                    </div>

                                    <div class="col-md-auto px-0">
                                        @if(count($domain)>0)

                                       <a href="#domain" data-toggle="modal" data-target="#domain" class="btn btn-dark btn-modern text-2 text-uppercase checkout">{{ __('message.checkout') }} <i class="fas {{ isRtlForLang() ? 'fa-arrow-left me-2' : 'fa-arrow-right ms-2' }}"></i></a>
                                         @else
                                         <a href="{{url('checkout')}}" class="btn btn-dark btn-modern text-2 text-uppercase checkout">{{ __('message.checkout') }} <i class="fas {{ isRtlForLang() ? 'fa-arrow-left me-2' : 'fa-arrow-right ms-2' }}"></i></a>
                                          @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

            @else
                   <div class="featured-boxes">
                    <div class="row">
                        <div class="col-md-12" style="text-align: center;">

                                    <div class="col-md-offset-5">
                                        <p class="text-black">{{ __('message.no_item_cart')}}</p>
                                        @if(Auth::check())

                                            @php
                                                $data = \App\Model\Product\ProductGroup::where('hidden','!=', 1)->first();
                                            @endphp
                                        
                                           @if(!is_null($data))
                                            <a href="{{url("group/$data->pricing_templates_id/$data->id")}}" class="btn border-0 px-4 py-2 line-height-9 btn-tertiary me-2">{{ __('message.continue_shopping')}}
                                                @endif

                                                @else
                                                    <a href="{{url('login')}}" class="btn border-0 px-4 py-2 line-height-9 btn-tertiary me-2">{{ __('message.continue_shopping')}}
                                                        @endif
                                                    </a>
                                </div>
                        </div>
                    </div>
                </div>
                @endif
            </div>
        </div>
    <script src="{{asset('client/js/jquery.min.js')}}"></script>
    <script>

        /*
         * Increase No. Of Agents
         */
        $(document).on('click', '.agentplus', function(){
            var $quantity = $(this).closest('.quantity');
            var $agtqty = $quantity.find('.agent-input');
            var $productid = $quantity.find('.productid');
            var $planid = $quantity.find('.planid');
            var $agentprice = $quantity.find('.agentprice');
            var $currency = $quantity.find('.currency');
            var $symbol = $quantity.find('.symbol');

            var currency = $currency.val();//Get the Currency for the Product
            var symbol = $symbol.val();//Get the Symbol for the Currency
            var productid = parseInt($productid.val()); //get Product Id
            var planid = parseInt($planid.val()); //get Product Id
            var currentAgtQty = parseInt($agtqty.val()); //Get Current Quantity of Prduct
            var actualAgentPrice = parseInt($agentprice.val());//Get Initial Price of Prduct

            var finalAgtqty = currentAgtQty + 1;
            var finalAgtprice = actualAgentPrice * finalAgtqty;

            $agtqty.val(finalAgtqty);
            $agentprice.val(finalAgtprice);

            $.ajax({
                type: "POST",
                data:{'productid':productid,'planid':planid},
                beforeSend: function () {
                    $quantity.find('.agentminus, .agentplus').prop('disabled', true);
                    $('#global-loader').show();
                },
                url: "{{url('update-agent-qty')}}",
                success: function () {
                    location.reload();
                }
            });
        });
        /*
        *Decrease No. of Agents
         */
        $(document).on('click', '.agentminus', function(){
            var $quantity = $(this).closest('.quantity');
            var $agtqty = $quantity.find('.agent-input');
            var $productid = $quantity.find('.productid');
            var $planid = $quantity.find('.planid');
            var $agentprice = $quantity.find('.agentprice');
            var $currency = $quantity.find('.currency');
            var $symbol = $quantity.find('.symbol');

            var currentAgtQty = parseInt($agtqty.val());

            // Don't allow going below 1
            if (currentAgtQty <= 1) {
                return;
            }

            var currency = $currency.val();
            var symbol = $symbol.val();
            var productid = parseInt($productid.val());
            var planid = parseInt($planid.val());
            var actualAgentPrice = parseInt($agentprice.val());

            var finalAgtqty = currentAgtQty - 1;
            var finalAgtprice = actualAgentPrice / 2; // You might want to recalculate this properly

            $agtqty.val(finalAgtqty);
            $agentprice.val(finalAgtprice);

            $.ajax({
                type: "POST",
                data: {'productid': productid,'planid':planid},
                beforeSend: function () {
                    $quantity.find('.agentminus, .agentplus').prop('disabled', true);
                    $('#global-loader').show();
                },
                url: "{{url('reduce-agent-qty')}}",
                success: function () {
                    location.reload();
                },
                error: function() {
                    $quantity.find('.agentminus, .agentplus').prop('disabled', false);
                    $('#global-loader').hide();
                }
            });
        });

        /*
        *Increase Product Quantity
         */
        $(document).on('click', '.quantityplus', function(){
            var $quantity = $(this).closest('.quantity');
            var $productid = $quantity.find('.productid');
            var $planid = $quantity.find('.planid');
            var $responseContainer = $(this).closest('tr').find('.response-container');

            var productid = parseInt($productid.val());
            var planid = parseInt($planid.val());

            $.ajax({
                type: "POST",
                data: {'productid':productid,'planid':planid},
                beforeSend: function () {
                    $quantity.find('.quantityminus, .quantityplus').prop('disabled', true);
                    $('#global-loader').show();
                },
                url: "{{url('update-qty')}}",
                success: function () {
                    location.reload();
                },
                error: function() {
                    $quantity.find('.quantityminus, .quantityplus').prop('disabled', false);
                    $('#global-loader').hide();
                }
            });
        });

        /*
         * Reduce Product Quantity
         */
        $(document).on('click', '.quantityminus', function(){
            var $quantity = $(this).closest('.quantity');
            var $qty = $quantity.find('.quantity-input');
            var $productid = $quantity.find('.productid');
            var $price = $quantity.find('.quatprice');
            var $planid = $quantity.find('.planid');
            var $responseContainer = $(this).closest('tr').find('.response-container');

            var currentQty = parseInt($qty.val());

            // Don't allow going below 1
            if (currentQty <= 1) {
                return;
            }

            var productid = parseInt($productid.val());
            var planid = parseInt($planid.val());
            var price = parseInt($price.val());

            var finalqty = currentQty - 1;
            $qty.val(finalqty);

            $.ajax({
                type: "POST",
                data: {'productid':productid,'planid':planid},
                beforeSend: function () {
                    $quantity.find('.quantityminus, .quantityplus').prop('disabled', true);
                    $('#global-loader').show();
                },
                url: "{{url('reduce-product-qty')}}",
                success: function () {
                    location.reload();
                },
                error: function() {
                    $quantity.find('.quantityminus, .quantityplus').prop('disabled', false);
                    $('#global-loader').hide();
                }
            });
        });

        function Addon(id){
            $.ajax({
                type: "GET",
                data:{"id": id, "category": "addon"},
                url: "{{url('cart')}}",
                success: function (data) {
                    location.reload();
                }
            });
        }

    </script>

@stop