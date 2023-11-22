        <div class="container py-4">

            <div class="row justify-content-center">

                <div class="col-lg-8">

                    <div class="card border-width-3 border-radius-0 border-color-success">

                        <div class="card-body text-center">
                                  <?php
                                $currency = $invoice->currency;
                                $cont = new \App\Http\Controllers\License\LicensePermissionsController();
                                $downloadPermission = $cont->getPermissionsForProduct($product->id);
                                $date = getDateHtml($date);
                                ?>

                            <p class="text-color-dark font-weight-bold text-4-5 mb-0"><i class="fas fa-check text-color-success me-1"></i> Thank You. Your Order has been received.</p>
                        </div>
                    </div>

                    <div class="d-flex flex-column flex-md-row justify-content-between py-3 px-4 my-4">

                        <div class="text-center">
                            <span>
                                Order Number <br>
                                <strong class="text-color-dark">{!! $order_number !!}</strong>
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
                                Email <br>
                                <strong class="text-color-dark">{{\Auth::user()->email}}</strong>
                            </span>
                        </div>
                        <div class="text-center mt-4 mt-md-0">
                            <span>
                                Total <br>
                                <strong class="text-color-dark">{{currencyFormat($invoiceItem->subtotal,$code = $currency)}}</strong>
                            </span>
                        </div>
                        <div class="text-center mt-4 mt-md-0">
                            <span>
                                Payment Method <br>
                                <strong class="text-color-dark">{{Session::get('payment_method')}}</strong>
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
                                        <strong class="d-block text-color-dark line-height-1 font-weight-semibold">{{$invoiceItem->product_name}} <span class="product-qty">x{{$invoiceItem->quantity}}</span></strong>
                                    </td>
                                    <td class="text-end align-top">
                                        <span class="amount font-weight-medium text-color-grey">$99</span>
                                    </td>
                                </tr>
  

                                <tr class="total">
                                    <td>
                                        <strong class="text-color-dark text-3-5">Total</strong>
                                    </td>
                                    <td class="text-end">
                                        <strong class="text-color-dark"><span class="amount text-color-dark text-5">{{currencyFormat($invoiceItem->subtotal,$code = $currency)}}</span></strong>
                                    </td>
                                </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

        </div>