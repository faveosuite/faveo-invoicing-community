        <div class="container py-4">

            <div class="row justify-content-center">

                <div class="col-lg-8">

                    <div class="card border-width-3 border-radius-0 border-color-success">

                        <div class="card-body text-center">

                            <p class="text-color-dark font-weight-bold text-4-5 mb-0"><i class="fas fa-check text-color-success me-1"></i>{{ __('message.thanks_order_received')}}</p>
                        </div>
                    </div>
                @foreach($orders as $order)
                <?php
                $product = \App\Model\Product\Product::where('id', $order->product)->select('id', 'name','type')->first();
                $cont = new \App\Http\Controllers\License\LicensePermissionsController();
                $downloadPermission = $cont->getPermissionsForProduct($order->product);
                ?>
                @endforeach

                    <div class="d-flex flex-column flex-md-row justify-content-between py-3 px-4 my-4">

                        <div class="text-center">
                            <span><strong class="text-color-dark">
                                {{ __('message.invoice_number')}} </strong><br>
                                {{$invoice->number}}
                            </span>
                        </div>
     
                        <div class="text-center mt-4 mt-md-0">
                            <span><strong class="text-color-dark">
                                {{ __('message.email')}}</strong> <br>
                                {{\Auth::user()->email}}
                            </span>
                        </div>
                        <div class="text-center mt-4 mt-md-0">
                            <span><strong class="text-color-dark">
                                {{ __('message.date')}}</strong> <br>
                                {!! $date !!}
                            </span>
                        </div>
                        <div class="text-center mt-4 mt-md-0">
                            <span><strong class="text-color-dark">
                                {{ __('message.total')}}</strong> <br>
                                {{currencyFormat($invoice->grand_total,$code = $invoice->currency)}}
                            </span>
                        </div>

                    </div>

                    <div class="card border-width-3 border-radius-0 border-color-hover-dark mb-4">

                        <div class="card-body">

                            <h4 class="font-weight-bold text-uppercase text-4 mb-3">{{ __('message.your_order')}}</h4>

                            <table class="shop_table cart-totals mb-0">

                                <tbody>

                                <tr>
                                    <td colspan="2" class="border-top-0">
                                        <strong class="text-color-dark">{{ __('message.product')}}</strong>
                                    </td>
                                </tr>

                                          @foreach($orders as $order)
                                <?php
                                 $product = \App\Model\Product\Product::where('id', $order->product)->select('id', 'name','type')->first();
                                ?>

                                <tr>
                                   <td>
                                    <strong class="d-block text-color-dark line-height-1 font-weight-semibold">{{$product->name}} <span class="product-qty">x {{$order->qty}}</span></strong>
                                    <ul class="wc-item-meta" style="list-style: none; padding: 0;">
                                        <li style="display: inline-block;"><strong class="wc-item-meta-label">{{ __('message.order_number')}}:</strong> <p style="display: inline;">{{$order->number}}</p></li>
                                    </ul> 
                                </td>

                                    <?php
                                    $orderTotal = $order->price_override;
                                    ?>
                                    <td class="text-end align-top">
                                        <span class="amount font-weight-medium text-color-grey">{{currencyFormat($orderTotal,$code = $invoice->currency)}}</span><br>
                                        @if($downloadPermission['downloadPermission'] == 1 && !in_array($product->id,cloudPopupProducts()))
                      
                                            <a href="{{ url("product/download/$order->product/$invoice->number") }}" class="btn btn-light-scale-2 btn-sm text-dark" data-toggle="tooltip" aria-label="{{ __('message.click_to_download')}}" data-bs-original-title="{{ __('message.click_to_download')}}"><i class="fa fa-download"> </i></a>
                                           
                                        @endif
                                    </td>
                                </tr>
                               
                                @endforeach

                                <tr class="total">
                                    <td>
                                        <strong class="text-color-dark text-3-5">{{ __('message.total')}}</strong>
                                    </td>
                                    <td class="text-end">
                                        <strong class="text-color-dark"><span class="amount text-color-dark text-5">{{currencyFormat($invoice->grand_total,$code = $invoice->currency)}}</span></strong>
                                    </td>
                                </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

        </div>

        <script>
function deploy(button) {
var orderNumber = button.value;
openModal(orderNumber);
}


function openModal(orderNumber) {
$('#tenant .modal-body').text('Order Number: ' + orderNumber);
$('#tenant').modal('show');
}
</script>
