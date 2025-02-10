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

        {!! Form::open(['url'=>'renew/'.$id,'method'=>'post']) !!}


    <div class="card-body">
                <?php 
                 $plans = App\Model\Payment\Plan::where('product',$productid)->pluck('name','id')->toArray();
                ?>
        <div class="row">

            <div class="col-md-12">



                <div class="row">

                    <div class="col-md-4 form-group {{ $errors->has('plan') ? 'has-error' : '' }}">
                        <!-- first name -->
                        {!! Form::label('plan','Plan',['class'=>'required']) !!}
                          <select name="plan" id="plans" onchange="fetchPlanCost(this.value)" class="form-control" >
                             <option value=''>{{ __('message.choose') }}</option>
                           @foreach($plans as $key=>$plan)
                              <option value={{$key}}>{{$plan}}</option>
                          @endforeach
                          </select>
                        <div class="input-group-append"></div>
                        <!-- {!! Form::select('plan',[''=>'Select','Plans'=>$plans],null,['class' => 'form-control','onchange'=>'fetchPlanCost(this.value)']) !!} -->
                        {!! Form::hidden('user',$userid) !!}
                    </div>

                   

                   <div class="col-md-4 form-group {{ $errors->has('payment_method') ? 'has-error' : '' }}">
                        <!-- last name -->
                        {!! Form::label('payment_method',Lang::get('message.payment-method'),['class'=>'required']) !!}
                        {!! Form::select('payment_method',[''=> __('message.choose'),'cash'=>'Cash','check'=>'Check','online payment'=>'Online Payment','razorpay'=>'Razorpay','stripe'=>'Stripe'],null,['class' => 'form-control','id'=>'payment_method']) !!}
                       <div class="input-group-append"></div>
                    </div>
                     <div class="col-md-4 form-group {{ $errors->has('cost') ? 'has-error' : '' }}">
                        <!-- last name -->
                        {!! Form::label('cost',Lang::get('message.price'),['class'=>'required']) !!}
                        {!! Form::text('cost',null,['class' => 'form-control','id'=>'price']) !!}
                         <div class="input-group-append"></div>

                    </div>
                </div>
                @if(in_array($productid,cloudPopupProducts()))
                <div class="row">
                    <div class="col-md-4 form-group">
                        {!! Form::label('agents', __('message.agents'), ['class' => 'col-form-label required']) !!}
                        {!! Form::number('agents', $agents, ['class' => 'form-control', 'id' => 'agents', 'min' => '1', 'placeholder' => '', 'required']) !!}
                    </div>
                </div>
                @endif




            </div>

        </div>
                    <button type="submit" class="btn btn-primary pull-right" id="submit" data-loading-text="<i class='fa fa-circle-o-notch fa-spin'>&nbsp;</i> {{ __('message.saving') }}"><i class="fa fa-save">&nbsp;&nbsp;</i>{{ __('message.save') }}</button>

    </div>

</div>


{!! Form::close() !!}
 <script>
     $(document).ready(function() {
         const userRequiredFields = {
             planname:@json(trans('message.renew_plan')),
             planproduct:@json(trans('message.renew_price')),
             regular_price:@json(trans('message.renew_payment_method')),
         };

         $('#submit').on('click', function (e) {
             const userFields = {
                 planname:$('#plans'),
                 planproduct:$('#price'),
                 regular_price:$('#payment_method'),
             };

             // Clear previous errors
             Object.values(userFields).forEach(field => {
                 field.removeClass('is-invalid');
                 field.next().next('.error').remove();

             });

             let isValid = true;

             const showError = (field, message) => {
                 field.addClass('is-invalid');
                 field.next().after(`<span class='error invalid-feedback'>${message}</span>`);
             };

             // Validate required fields
             Object.keys(userFields).forEach(field => {
                 if (!userFields[field].val()) {
                     showError(userFields[field], userRequiredFields[field]);
                     isValid = false;
                 }
             });

             // If validation fails, prevent form submission
             if (!isValid) {
                 e.preventDefault();
             }
         });
         // Function to remove error when input'id' => 'changePasswordForm'ng data
         const removeErrorMessage = (field) => {
             field.classList.remove('is-invalid');
             const error = field.nextElementSibling;
             if (error && error.classList.contains('error')) {
                 error.remove();
             }
         };

         // Add input event listeners for all fields
         ['plans','price','payment_method'].forEach(id => {

             document.getElementById(id).addEventListener('input', function () {
                 removeErrorMessage(this);

             });
         });
     });


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