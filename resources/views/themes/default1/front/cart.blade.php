@extends('themes.default1.layouts.front.master')
@section('title')
    Cart
@stop
@section('page-header')
    <br>
    Cart
@stop
@section('page-heading')
Cart
@stop
@section('breadcrumb')
@if(Auth::check())
        <li><a class="text-primary" href="{{url('my-invoices')}}">Home</a></li>
@else
     <li><a class="text-primary" href="{{url('login')}}">Home</a></li>
@endif
 <li class="active text-dark">Cart</li>
@stop
@section('main-class') "main shop" @stop




@section('content')




    <?php
    $cartTotal = 0;
    ?>

        <div role="main" class="main shop pb-4">
              @if(!Cart::isEmpty())

            <div class="container py-4">
                

                <div class="row pb-4 mb-5">


                    <div class="col-lg-8 mb-5 mb-lg-0">

                        <form method="post" action="">

                            <div class="table-responsive">

                                @php
                                $isAgentAllowed = false;
                                $isAllowedtoEdit = false;

                                foreach ($cartCollection as $item) {
                                    // Your existing logic to calculate $isAgentAllowed and $isAllowedtoEdit
                                    // This part should remain the same as in your original code
                                    $cont = new \App\Http\Controllers\Product\ProductController();
                                    $isAgentAllowed = $cont->allowQuantityOrAgent($item->id);
                                    $isAllowedtoEdit = $cont->isAllowedtoEdit($item->id);

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

                                                Product

                                            </th>

                                            <th class="product-price text-uppercase" width="">

                                                Price
                                            </th>
                                            @if(!$isAgentAllowed)

                                            <th class="product-quantity text-uppercase" width="">

                                                Quantity
                                            </th>
                                            @else
                                            <th class="product-agents text-uppercase" width="">

                                                Agents
                                            </th>
                                            @endif


                                            <th class="product-subtotal text-uppercase" width="">

                                                Subtotal
                                            </th>
                                        </tr>
                                    </thead>
                                    <?php
                                        $sortedCart = $cartCollection->sortBy(function($item) {
                                            return $item->id;
                                        });
                                        ?>
                                             @forelse($sortedCart as $item)
                                    @php
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

                                                  if ($item->associatedModel->require_domain) {
                                                      $domain[$key] = $item->associatedModel->id;
                                                      $productName = $item->associatedModel->name;

                                                  }
                                                  $cont = new \App\Http\Controllers\Product\ProductController();
                                                  $isAgentAllowed = $cont->allowQuantityOrAgent($item->id);
                                                  $isAllowedtoEdit = $cont->isAllowedtoEdit($item->id);
                                            @endphp

                                    <tbody>

                                        <tr class="cart_table_item">

                                            <td class="product-thumbnail">

                                                <div class="product-thumbnail-wrapper" style="width: 100px;">

                                                    <a onclick="removeItem('{{$item->id}}');"class="product-thumbnail-remove"  data-bs-toggle="tooltip" title="Remove Product" style="top: -15px;">

                                                        <i class="fas fa-times"></i>
                                                    </a>

                                                    <span class="product-thumbnail-image" >
                                                        <?php
                                                            $image = \App\Model\Product\Product::find($item->associatedModel->id)->image;
                                                            ?>

                                                        <img width="90" height="90" alt="" class="img-fluid" src="{{$image}}"  data-bs-toggle="tooltip" title="{{$item->name}}">
                                                    </span>
                                                </div>
                                            </td>

                                            <td class="product-name">

                                                <span class="font-weight-semi-bold text-color-dark">{{$item->name}}</span>
                                                <br>
                                                <i style="font-size: 12px;">{{$item->attributes->domain}}</i>
                                            </td>

                                            <td class="product-price">

                                                <span class="amount font-weight-medium text-color-grey">{{currencyFormat($item->price,$code = $item->attributes->currency)}}</span>
                                                <div id="response"></div>
                                            </td>

                                            @if(!$isAgentAllowed)
                                                    <td class="product-quantity">
                                                        @if($isAllowedtoEdit['quantity'])
                                                            <div class="quantity">
                                                                <input type="button" class="quantityminus minus" value="-">
                                                                <input type = "hidden" class="productid" value="{{$item->id}}">
                                                                <input type = "hidden" class="quatprice" id="quatprice" value=" {{$item->price}}">
                                                                <input type="text" class="input-text qty text" title="Qty" id="qty" value="{{$item->quantity}}" name="quantity" id="quantity" min="1" step="1" disabled>
                                                                <input type="button" class="quantityplus plus" value="+">
                                                            </div>


                                                        @else
                                                            {{$item->quantity}}
                                                        @endif
                                                    </td>
                                                @else
                                                    <td class="product-agents">
                                                        @if (!$item->attributes->agents)
                                                            {{'Unlimited Agents'}}
                                                        @else
                                                            @if($isAllowedtoEdit['agent'])
                                                                <div class="quantity">
                                                                    <input type="button" class="agentminus minus" value="-">
                                                                    <input type="hidden" id="initialagent" value="{{$item->attributes->initialagent}}">
                                                                    <input type = "hidden" class="currency" value="{{$item->attributes->currency}}">
                                                                    <input type = "hidden" class="symbol" value="{{$item->attributes->symbol}}">
                                                                    <input type = "hidden" class="productid" value="{{$item->id}}">
                                                                    <input type = "hidden" class="agentprice" id="agentprice" value=" {{$item->getPriceSum()}}">
                                                                    <input type="text" class="input-text qty text" id="agtqty" title="Qty" value="{{$item->attributes->agents}}" name="quantity" min="1" step="1" disabled>
                                                                    <input type="button" class="agentplus plus" value="+">
                                                                </div>

                                                            @else
                                                                {{$item->attributes->agents}}
                                                            @endif
                                                    </td>
                                                @endif
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

                                <h4 class="font-weight-bold text-uppercase text-4 mb-3">Cart Totals</h4>


                                <div class="table-responsive">

                                    <table class="shop_table cart-totals mb-4">

                                        <tbody>

                                            <tr class="total">

                                                <td>
                                                    <strong class="text-color-dark text-3-5" style="font-family: Arial;">Total</strong>
                                                </td>

                                                <td class="text-end">
                                                    <strong class="text-color-dark"><span class="amount text-color-dark text-5"> {{currencyFormat($cartTotal, $item->attributes->currency)}}</span></strong>
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>

                                <div class="row justify-content-between mx-0">

                                    <div class="col-md-auto px-0 mb-3 mb-md-0">

                                        <div class="d-flex align-items-center">
                                            <form action="{{url('cart/clear')}}" method="post">
                                            {{ csrf_field() }}

                                             <a href="{{url('cart/clear')}}"><button class="btn btn-light btn-modern text-2 text-uppercase" style="background: #F4F4F4;">Clear Cart</button></a>
                                            </form>
                                        </div>
                                    </div>

                                    <div class="col-md-auto px-0">
                                        @if(count($domain)>0)

                                       <a href="#domain" data-toggle="modal" data-target="#domain" class="btn btn-dark btn-modern text-2 text-uppercase checkout">Checkout <i class="fas fa-arrow-right ms-2"></i></a>
                                         @else
                                         <a href="{{url('checkout')}}" class="btn btn-dark btn-modern text-2 text-uppercase checkout">Checkout <i class="fas fa-arrow-right ms-2"></i></a>
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
                                        <p class="text-black">There are no items in this cart.</p>
                                        @if(Auth::check())

                                            @php
                                                $data = \App\Model\Product\ProductGroup::where('hidden','!=', 1)->first();
                                            @endphp
                                        
                                           @if(!is_null($data))
                                            <a href="{{url("group/$data->pricing_templates_id/$data->id")}}" class="btn border-0 px-4 py-2 line-height-9 btn-tertiary me-2">CONTINUE SHOPPING
                                                @endif

                                                @else
                                                    <a href="{{url('login')}}" class="btn border-0 px-4 py-2 line-height-9 btn-tertiary me-2">CONTINUE SHOPPING
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
        * Increase Agent Qty
        */
        $('.agentplus').on('click', function () {
            var $agtqty = $(this).parents('.quantity').find('.qty');
            var $productid = $(this).parents('.quantity').find('.productid');
            var $agentprice = $(this).parents('.quantity').find('.agentprice');
            var $currency = $(this).parents('.quantity').find('.currency');
            var $symbol  = $(this).parents('.quantity').find('.symbol');
            var currency = $currency.val();//Get the Currency for the Product
            var symbol = $symbol.val();//Get the Symbol for the Currency
            var productid = parseInt($productid.val()); //get Product Id
            var currentAgtQty = parseInt($agtqty.val()); //Get Current Quantity of Prduct
            var actualAgentPrice = parseInt($agentprice.val());//Get Initial Price of Prduct
            // console.log(productid,currentVal,actualprice);

            var finalAgtqty = $agtqty.val(currentAgtQty + 1).val();
            var finalAgtprice = $agentprice.val(actualAgentPrice * finalAgtqty).val();

            var $this = $(this);
            $this.prop('disabled', true); // disable only the clicked button

            $.ajax({
                type: "POST",
                data:{'productid':productid},
                beforeSend: function () {
                    $('#response').html( "<img id='blur-bg' class='backgroundfadein' style='width: 50px; height:50 px; display: block; position:    fixed;' src='{!! asset('lb-faveo/media/images/gifloader3.gif') !!}'>");

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
        $(document).ready(function () {
            var currentagtQty = $('.qty').val();
            if (currentagtQty > 1) {
                $('.agentminus').on('click', function () {
                    if ($(this).prop('disabled')) {
                        return;
                    }

            var $agtqty = $(this).parents('.quantity').find('.qty');
            var $productid = $(this).parents('.quantity').find('.productid');
            var $agentprice = $(this).parents('.quantity').find('.agentprice');
            var $currency = $(this).parents('.quantity').find('.currency');
            var $symbol = $(this).parents('.quantity').find('.symbol');
            var currency = $currency.val();
            var symbol = $symbol.val();
            var productid = parseInt($productid.val());
            var currentAgtQty = parseInt($agtqty.val());
            var actualAgentPrice = parseInt($agentprice.val());

                    if (!isNaN(currentAgtQty) && currentAgtQty > 1) {
                        var finalAgtqty = $agtqty.val(currentAgtQty - 1).val();
                        var finalAgtprice = $agentprice.val(actualAgentPrice / 2).val();
                    } else {
                        return;
                    }

                    var $this = $(this);
                    $this.prop('disabled', true);

                    $.ajax({
                        type: "POST",
                        data: { 'productid': productid },
                        beforeSend: function () {
                            $('#response').html("<img id='blur-bg' class='backgroundfadein' style='width: 50px; height:50px; display: block; position: fixed;' src='{!! asset('lb-faveo/media/images/gifloader3.gif') !!}'>");
                        },
                        url: "{{url('reduce-agent-qty')}}",
                        success: function () {
                            location.reload();
                        },
                        complete: function () {
                            $this.prop('disabled', false);
                        }
                    });
                });
            }
        });




        /*
        *Increse Product Quantity
         */
        $('.quantityplus').on('click', function () {
            var $productid = $(this).parents('.quantity').find('.productid');
            var productid = parseInt($productid.val());

            var $this = $(this);
            $this.prop('disabled', true);

            $.ajax({
                type: "POST",
                data: { 'productid': productid },
                beforeSend: function () {
                    $('#response').html("<img id='blur-bg' class='backgroundfadein' style='width: 50px; height:50px; display: block; position: fixed;' src='{!! asset('lb-faveo/media/images/gifloader3.gif') !!}'>");
                },
                url: "{{url('update-qty')}}",
                success: function () {
                    location.reload();
                }
            });
        });
        /*
         * Reduce Product Quantity
         */
        $('.quantityminus').on('click', function () {
            var $qty = $(this).parents('.quantity').find('.qty');
            var $productid = $(this).parents('.quantity').find('.productid');
            var $price = $(this).parents('.quantity').find('.quatprice');
            var productid = parseInt($productid.val());
            var currentQty = parseInt($qty.val());
            var incraesePrice = parseInt($price.val());

            if (!isNaN(currentQty)) {
                var finalqty = $qty.val(currentQty - 1).val();
                var finalprice = $price.val(incraesePrice).val();
            }

            var $this = $(this);
            $this.prop('disabled', true);

            $.ajax({
                type: "POST",
                data: {'productid':productid},
                beforeSend: function () {
                    $('#response').html( "<img id='blur-bg' class='backgroundfadein' style='width: 50px; height:50 px; display: block; position:    fixed;' src='{!! asset('lb-faveo/media/images/gifloader3.gif') !!}'>");
                },
                url: "{{url('reduce-product-qty')}}",
                success: function () {
                    location.reload();
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