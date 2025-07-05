@extends('themes.default1.layouts.master')
@section('title')
    {{ __('message.renew') }}
@stop
@section('content-header')
    <div class="col-sm-6">
        <h1>{{ __('message.renew_order') }}</h1>
    </div>
    <div class="col-sm-6">
        <ol class="breadcrumb float-sm-right">
            <li class="breadcrumb-item"><a href="{{url('/')}}"><i class="fa fa-dashboard"></i> {{ __('message.home') }}</a></li>
            <li class="breadcrumb-item"><a href="{{url('orders')}}"><i class="fa fa-dashboard"></i> {{ __('message.all-orders') }}</a></li>
            <li class="breadcrumb-item active">{{ __('message.renew_order') }}</li>
        </ol>
    </div><!-- /.col -->
@stop

@section('content')
<div class="card card-secondary card-outline">

    {!! html()->form('POST', url('renew/' . $id))->id('renew-form')->open() !!}


    <div class="card-body">
                <?php
                $currency = getCurrencyForClient(App\User::find($userid)->country);
                $plans = \App\Model\Payment\Plan::where('product', $productid)
                    ->whereHas('planPrice', function ($query) use ($currency) {
                        $query->where('currency', $currency);
                    })
                    ->pluck('name', 'id')
                    ->toArray();
                ?>
        <div class="row">

            <div class="col-md-12">



                <div class="row">

                    <div class="col-md-4 form-group {{ $errors->has('plan') ? 'has-error' : '' }}">
                        <!-- first name -->
                        {!! html()->label( __('message.plans'), 'plan')->class('required') !!}
                        <select name="plan" id="plans" onchange="fetchPlanCost(this.value)" class="form-control">
                            @if(count($plans) > 0)
                                <option value="">{{ __('message.choose') }}</option>
                                @foreach($plans as $key => $plan)
                                    <option value="{{ $key }}">{{ $plan }}</option>
                                @endforeach
                            @else
                                <option value="" disabled selected>
                                    {{ __('message.no_available_plans_currency') }}
                                </option>
                            @endif
                        </select>
                        <div class="input-group-append"></div>
                        <!-- {!! html()->select('plan', ['' => 'Select'] + $plans)->class('form-control')->attribute('onchange', 'fetchPlanCost(this.value)') !!} -->
                        {!! html()->hidden('user', $userid) !!}
                    </div>



                    <div class="col-md-4 form-group {{ $errors->has('payment_method') ? 'has-error' : '' }}">
                        {!! html()->label(Lang::get('message.payment-method'))->class('required')->for('payment_method') !!}
                        {!! html()->select('payment_method', ['' => __('message.choose'), 'cash' => 'Cash', 'check' => 'Check', 'online payment' => 'Online Payment', 'razorpay' => 'Razorpay', 'stripe' => 'Stripe'])->class('form-control')->id('payment_method') !!}
                    </div>

                    <div class="col-md-4 form-group {{ $errors->has('cost') ? 'has-error' : '' }}">
                        {!! html()->label(Lang::get('message.price'))->class('required')->for('cost') !!}
                        {!! html()->text('cost')->class('form-control')->id('price') !!}
                    </div>
                </div>

                @if(in_array($productid,cloudPopupProducts()))
                <div class="row">
                    <div class="col-md-4 form-group">
                        {!! html()->label( __('message.agents'))->class('col-form-label required')->for('agents') !!}
                        {!! html()->number('agents', $agents)->class('form-control')->id('agents')->placeholder('')->required() !!}
                    </div>
                </div>
                @endif




            </div>

        </div>
                    <button type="submit" class="btn btn-primary pull-right" id="submit" data-loading-text="<i class='fa fa-circle-o-notch fa-spin'>&nbsp;</i> {{ __('message.saving') }}"><i class="fa fa-save">&nbsp;&nbsp;</i>{{ __('message.save') }}</button>

    </div>

</div>


{!! html()->form()->close() !!}
<script>
    $(document).ready(function () {
        // Setup validation
        $('#renew-form').validate({
            rules: {
                plan: {
                    required: true
                },
                payment_method: {
                    required: true
                },
                cost: {
                    required: true,
                    number: true,
                    min: 1
                },
                agents: {
                    required: function () {
                        return $('#agents').length > 0; // only if agents field exists
                    },
                    number: true,
                    min: 1
                }
            },
            messages: {
                plan: {
                    required: "{{ __('message.renew_plan') }}"
                },
                payment_method: {
                    required: "{{ __('message.renew_payment_method') }}"
                },
                cost: {
                    required: "{{ __('message.renew_price') }}",
                    number: "{{ __('message.enter_valid_number') }}",
                    min: "{{ __('message.enter_valid_number') }}"
                },
                agents: {
                    required: "Please enter number of agents",
                    number: "{{ __('message.enter_valid_number') }}",
                    min: "{{ __('message.enter_valid_number') }}"
                },
            },
            errorClass: "is-invalid",
            errorPlacement: function (error, element) {
                error.addClass("invalid-feedback");
                if (element.parent(".input-group").length) {
                    error.insertAfter(element.parent());
                } else {
                    error.insertAfter(element);
                }
            }
        });
    });
</script>


 <script>


     $('ul.nav-sidebar a').filter(function() {
        return this.id == 'all_order';
    }).addClass('active');

    // for treeview
    $('ul.nav-treeview a').filter(function() {
        return this.id == 'all_order';
    }).parentsUntil(".nav-sidebar > .nav-treeview").addClass('menu-open').prev('a').addClass('active');

     $('.closebutton').on('click', function () {
         location.reload();
     });
     var shouldFetchPlanCost = true; // Disable further calls until needed

     @if(in_array($productid,cloudPopupProducts()))
     function fetchPlanCost(planId) {
         if(!shouldFetchPlanCost){
             return
         }
         var user = document.getElementsByName('user')[0].value;
         shouldFetchPlanCost = false
         $.ajax({
             type: "get",
             url: "{{ url('get-renew-cost') }}",
             data: { 'user': user, 'plan': planId },


             success: function (data) {
                 var agents = parseInt($('#agents').val() || 0);
                 var totalPrice = agents * parseFloat(data);
                 $('#price').val(totalPrice.toFixed(2));
                 shouldFetchPlanCost = true;
             }
         });
     }
     @else
     function fetchPlanCost(planId) {

         if(!shouldFetchPlanCost){

             return

         }

         var user = document.getElementsByName('user')[0].value;

         shouldFetchPlanCost = false

         $.ajax({

             type: "get",

             url: "{{ url('get-renew-cost') }}",

             data: { 'user': user, 'plan': planId },

             success: function (data) {

                 $("#price").val(data[0]);
                 shouldFetchPlanCost = true;

             }

         });

     }
     @endif
     // Call the fetchPlanCost function when the plan dropdown selection changes
     $('#plans').on('change', function () {
         var selectedPlanId = $(this).val(); // Get the selected plan ID
         fetchPlanCost(selectedPlanId); // Call the function to fetch plan cost
     });

     // Call the fetchPlanCost function initially with the default selected plan
     $(document).ready(function () {
         var initialPlanId = $('#plans').val();
         fetchPlanCost(initialPlanId);
     });

     // Prevent form submission when Enter key is pressed
     $('form').on('keypress', function (e) {
         if (e.keyCode === 13) {
             e.preventDefault();
         }
     });
     $('#agents').on('input', function () {
         var selectedPlanId = document.getElementsByName('plan')[0].value;
         fetchPlanCost(selectedPlanId);
     });




 </script>
@stop