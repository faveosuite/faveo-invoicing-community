@extends('themes.default1.layouts.front.master')
@section('title')
    Razorpay Authentication
@stop
@section('page-header')
    Razorpay Authentication
@stop
@section('page-heading')
    Razorpay Authentication
@stop
@section('content')
    <div class="container shop py-3">

        <div class="row">
            <div class="d-flex justify-content-center">
            <div class="pe-5 pe-sm-5 pb-3 pb-sm-0 mt-2">
                <span class="btn btn-dark" id="rzpbutton">Please click here to make the Payment</span>
            </div>
            </div>
        </div>

    </div>
@stop
<script src="https://checkout.razorpay.com/v1/checkout.js"></script>
<script>
    var options = <?php echo $json; ?>

    /**
     * The entire list of Checkout fields is available at
     * https://docs.razorpay.com/docs/checkout-form#checkout-fields
     */
        // options.handler = function (response){
        //     document.getElementById('razorpay_payment_id').value = response.razorpay_payment_id;
        //     document.getElementById('razorpay_signature').value = response.razorpay_signature;
        //
        //     document.razorpayform.submit();
        // };

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

    document.getElementById('rzpbutton').onclick = function(e){

        rzp.open();
        e.preventDefault();
    }
</script>