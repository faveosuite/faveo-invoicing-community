        <div class="container py-4">

            <div class="row justify-content-center">

                <div class="col-lg-8">

                    <div class="card border-width-3 border-radius-0 border-color-success">

                        <div class="card-body text-center">
                            @if($show)

                            <p class="text-color-dark font-weight-bold text-4-5 mb-0"><i class="fas fa-check text-color-success me-1"></i>Your Payment has been received. A confirmation Mail has been sent to you on <a>{{\Auth::user()->email}}</a></p>
                            @else
                            <p class="text-color-dark font-weight-bold text-4-5 mb-0"><i class="fas fa-check text-color-success me-1"></i>Your Payment has been received.</p>
                            @endif
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
                            <span>
                                Order Number <br>
                                <strong class="text-color-dark">{{$orderNumber}}</strong>
                            </span>
                        </div>
     
                        <div class="text-center mt-4 mt-md-0">
                            <span>
                                Email <br>
                                <strong class="text-color-dark">{{\Auth::user()->email}}</strong>
                            </span>
                        </div>
                        <div class="text-center mt-4 mt-md-0">
                            <span>
                                Date <br>
                                <strong class="text-color-dark">{!! $date !!}</strong>
                            </span>
                        </div>
                        <div class="text-center mt-4 mt-md-0">
                            <span>
                                Total <br>
                                <strong class="text-color-dark">{{currencyFormat($invoice->grand_total,$code = $invoice->currency)}}</strong>
                            </span>
                        </div>

                    </div>

                    <div class="card border-width-3 border-radius-0 border-color-hover-dark mb-4">

                        <div class="card-body">

                            <h4 class="font-weight-bold text-uppercase text-4 mb-3">Your Order</h4>

                            <table class="shop_table cart-totals mb-0">

                                <tbody>

                                <tr>
                                    <td colspan="2" class="border-top-0">
                                        <strong class="text-color-dark">Product</strong>
                                    </td>
                                </tr>

                                <tr>
                                    <td>
                                        <strong class="d-block text-color-dark line-height-1 font-weight-semibold"> {{$product->name}}<span class="product-qty">x{{$items[0]->quantity}}</span></strong>
                                    </td>
                                    <td class="text-end align-top">
                                        <span class="amount font-weight-medium text-color-grey">{{currencyFormat($invoice->grand_total,$code = $invoice->currency)}}</span>
                                    </td>
                                </tr>

                                <tr class="total">
                                    <td>
                                        <strong class="text-color-dark text-3-5">Total</strong>
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
         @if($show)
@if($downloadPermission['downloadPermission'] == 1 && $product->type != '4')

 <a style="position: relative;left: 200px;" href="{{ url("product/download/$order->product/$invoice->number") }}"  class="btn btn-dark btn-modern text-uppercase text-3 py-3"><i class="fa fa-download"> </i>  Download the Latest Version here</a>
@else

@endif
 @endif
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