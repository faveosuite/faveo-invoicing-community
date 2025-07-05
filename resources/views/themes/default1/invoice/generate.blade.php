@extends('themes.default1.layouts.master')
@section('title')
    {{ __('message.create_invoice') }}
@stop
@section('content-header')
    <div class="col-sm-6 md-6">
        <h1>{{ __('message.generate_an_invoice') }}</h1>
    </div>
    <div class="col-sm-6 md-6">
        <ol class="breadcrumb float-sm-right">
            <li class="breadcrumb-item"><a href="{{url('/')}}"><i class="fa fa-dashboard"></i> {{ __('message.home') }}</a></li>
            <li class="breadcrumb-item"><a href="{{url('clients')}}"> {{ __('message.all-users') }}</a></li>
            <li class="breadcrumb-item"><a href="{{url('invoices')}}">{{ __('message.view_invoices') }}</a></li>
            <li class="breadcrumb-item active">{{ __('message.generate_invoice') }}</li>
        </ol>
    </div><!-- /.col -->

@stop
@section('content')
<div class="col-md-12">
    <div id="invoice-alert-container"></div>
    <div class="card card-secondary card-outline">

    <div class="card-header">
{{--         @if($user!='')--}}
{{--            {!! html()->form('POST', url('generate/invoice/' . $user->id))->id('formoid')->open() !!}--}}
{{--            <input type="hidden" name="user" value="{{$user->id}}">--}}
{{--            <h5>{{ucfirst($user->first_name)}} {{ucfirst($user->last_name)}}, ({{$user->email}}) </h5>--}}
{{--            @else--}}
            {!! html()->form('POST', url('generate/invoice'))->id('formoid')->open() !!}
{{--        @endif--}}
        <div id="error">
        </div>
        <div id="successs">
        </div>
        <div id="fails">
        </div>  
         </div>  
    <div class="card-body">

        <div class="row">
{{--                @if($user=='')--}}
                <?php
                $users = [];
                ?>
                    <link rel="stylesheet" href="{{asset('admin/css/select2.min.css')}}">
                    <script src="{{asset('admin/plugins/select2.full.min.js')}}"></script>
                     <style>
                        .select2-container--default .select2-selection--multiple .select2-selection__choice {
                            background-color: #1b1818 !important;}
                    </style>

                <div class="col-sm-4 form-group">
                    {!! html()->label(Lang::get('message.clients'))->class('required') !!}
{{--                    {!! html()->select('user', [Lang::get('message.user') => $users])->multiple()->class("form-control select2". ($errors->has('user') ? ' is-invalid' : ''))->id('users')->attribute('name', 'user') !!}--}}
                    <select name="user[]" id="users" class="form-control select2" multiple>
                        @if($user)
                            <option value="{{ $user->id }}" selected>
                                {{ $user->first_name . ' ' . $user->last_name }}
                            </option>
                        @endif
                    </select>


                    <span class="error-message" id="user-msg"></span>
                    <h6 id ="productusercheck"></h6>
                </div>
{{--                @endif--}}
                <div class="col-md-4 lg-4 form-group {{ $errors->has('invoice_status') ? 'has-error' : '' }}">
                            <!-- first name -->
                    {!! html()->label(Lang::get('message.date'))->class('required') !!}


                         <div class="input-group date" id="invoice_date" data-target-input="nearest" >
                             {!! html()->text('date')->class('form-control datetimepicker-input'. ($errors->has('date') ? ' is-invalid' : ''))->id('datepicker')->attribute('data-target', '#invoice_date') !!}

                            <div class="input-group-append" data-target="#invoice_date" data-toggle="datetimepicker">
                                    <div class="input-group-text"><i class="fa fa-calendar"></i></div>

                            </div>
                             <span class="error-message" id="invoice-msg"></span>
                             @error('date')
                             <span class="error-message"> {{$message}}</span>
                             @enderror
                         </div>
                    </div>

                <div class="col-md-4 lg-4 form-group">
                    {!! html()->label(Lang::get('message.product'))->for('product')->class('required') !!}
                     <select name="product" value= "Choose" id="product" class="form-control {{$errors->has('product') ? ' is-invalid' : ''}}">
                             <option value="">{{ __('message.choose') }}</option>
                           @foreach($products as $key=>$product)
                              <option value={{$key}}>{{$product}}</option>
                          @endforeach
                          </select>

                    <span class="error-message" id="product-msg"></span>

                    <span id="user-error-msg" class="hide"></span>
                    <h6 id ="productnamecheck"></h6>
                    @error('product')
                    <span class="error-message"> {{$message}}</span>
                    @enderror
                </div>
                <div id="fields1" class="col-md-4">
                </div>
                <div class="col-md-4 form-group">
                    {!! html()->label(Lang::get('message.price'))->for('price')->class('required') !!}
                    {!! html()->text('price')->class('form-control'. ($errors->has('price') ? ' is-invalid' : ''))->id('price') !!}
                    <span class="error-message" id="price-msg"></span>
                      <h6 id ="pricecheck"></h6>
                    @error('price')
                    <span class="error-message"> {{$message}}</span>
                    @enderror
                </div>
                <div class="col-md-4 form-group">
                    {!! html()->label(__('message.coupon-code'))->for('code') !!}
                    {!! html()->text('code')->class('form-control'. ($errors->has('code') ? ' is-invalid' : '')) !!}
                    <span class="error-message" id="code-msg"></span>
                </div>
                    <div id="agents" class="col-md-4">
                    </div>
                    <div id="fields" class="col-md-4">
                    </div>
                 <div id="qty" class="col-md-4">
                </div>

            </div>
            <br>
             <h4> <button name="generate" type="submit" id="generate" class="btn btn-primary pull-right" ><i class="fas fa-sync-alt">&nbsp;</i>{!!Lang::get('message.generate')!!}</button></h4>
             
            {!! html()->form()->close() !!}

        </div>
    </div>
</div>



<script>
     $('ul.nav-sidebar a').filter(function() {
        return this.id == 'add_invoice';
    }).addClass('active');

    // for treeview
    $('ul.nav-treeview a').filter(function() {
        return this.id == 'add_invoice';
    }).parentsUntil(".nav-sidebar > .nav-treeview").addClass('menu-open').prev('a').addClass('active');


         $(document).ready(function() {

        $('#users').on('change', function () {
            if ($(this).val() !== '') {
                document.querySelector('.select2-selection').style.cssText = `
                        border: 1px solid silver;
                        background-image:null;
                        background-repeat: no-repeat;
                        background-position: right 10px center;
                        background-size: 16px 16px;`;
                removeErrorMessage(this);
            }
        });


        const userRequiredFields = {
            user:@json(trans('message.invoice_details.add_user')),
            datepicker:@json(trans('message.invoice_details.add_date')),
            product:@json(trans('message.invoice_details.add_product')),
            price:@json(trans('message.invoice_details.add_price')),
            plan:@json(trans('message.invoice_details.add_price')),

        };


        $('#generate').on('click', function (e) {
            @if($user='')
           if($('#users').val()==''){
               document.querySelector('.select2-selection').style.cssText = `
                        border: 1px solid #dc3545;
                        background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' fill='none' stroke='%23dc3545' viewBox='0 0 12 12'%3e%3ccircle cx='6' cy='6' r='4.5'/%3e%3cpath stroke-linejoin='round' d='M5.8 3.6h.4L6 6.5z'/%3e%3ccircle cx='6' cy='8.2' r='.6' fill='%23dc3545' stroke='none'/%3e%3c/svg%3e");
                        background-repeat: no-repeat;
                        background-position: right 10px center;
                        background-size: 16px 16px;`;
               e.preventDefault();
           }else{
               document.querySelector('.select2-selection').style.border='1px solid silver';

           }
            @endif
            if($('#price').val()==''){
                e.preventDefault();
            }

            if($('#product').val()==''){
                e.preventDefault();
            }

            const userFields = {
                user:$('#users'),
                datepicker:$('#datepicker'),
                product:$('#product'),
                price:$('#price'),
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
                if (!userFields[field].val() || (typeof userFields[field].val() == 'object' && userFields[field].val().length === 0)) {
                    showError(userFields[field], userRequiredFields[field]);
                    isValid = false;
                }
            });


            if(isValid && !isValidDate(userFields.datepicker.val())){
                showError(userFields.datepicker, @json(trans('message.invoice_details.add_valid_date')));
                isValid = false;
            }

            if(!document.getElementsByName('plan')[0].value){
                plan=document.getElementsByName('plan');
                plan[0].classList.add('is-invalid');
                document.getElementById('subscription-msg').innerHTML =@json(trans('message.subscription-error-message'));
                isValid=false;
            }else{
                plan[0].classList.remove('is-invalid');

            }

            if(!document.getElementsByName('agents')[0].value){
                plan=document.getElementsByName('agents');
                plan[0].classList.add('is-invalid');
                document.getElementById('agents-msg').innerHTML =@json(trans('message.agents-error-message'));
                isValid=false;
            }else{
                plan[0].classList.remove('is-invalid');
                document.getElementById('agents-msg').innerHTML ='';
            }

            if(!document.getElementsByName('cloud_domain')[0].value){
                plan=document.getElementsByName('cloud_domain');
                plan[0].classList.add('is-invalid');
                document.getElementById('cloud-msg').innerHTML =@json(trans('message.cloud-error-message'));
                isValid=false;
            }else{
                plan[0].classList.remove('is-invalid');
                document.getElementById('cloud-msg').innerHTML ='';

            }

            if(!document.getElementsByName('quantity')[0].value){
                plan=document.getElementsByName('quantity');
                plan[0].classList.add('is-invalid');
                document.getElementById('quantity-msg').innerHTML =@json(trans('message.quantity-error-message'));
                isValid=false;
            }else{
                plan[0].classList.remove('is-invalid');
                document.getElementById('quantity-msg').innerHTML ='';

            }

            if(document.getElementsByName('price').value===''){
                isValid=false;
            }

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
        ['users','price','datepicker'].forEach(id => {
            document.getElementById(id).addEventListener('input', function () {
                removeErrorMessage(this);

            });
        });

        function isValidDate(dateString) {
            const regex = /^(0[1-9]|1[0-2])\/(0[1-9]|[12][0-9]|3[01])\/\d{4}$/;

            return regex.test(dateString);
        }

    });

    function getPrice(val) {
        var user = $('#users').val()?.[0]; // Get first selected user ID


        var plan = "";
        var product = "";
        if ($('#plan').length > 0) {
            var plan = document.getElementsByName('plan');
            plan[0].classList.remove('is-invalid');
            document.getElementById('subscription-msg').innerHTML ='';
            document.getElementById('price').classList.remove('is-invalid');
        }
        if ($('#product').length > 0) {
            var product = document.getElementsByName('product')[0].value;
        }


        $.ajax({
            type: "POST",
            url: "{{url('get-price')}}",
            data: {'product': product, 'user': user, 'plan': val},
            //data: 'product=' + val+'user='+user,
            success: function (data) {
                var price = data['price'];
                var field = data['field'];
                var qty = data['quantity'];
                var agents = data['agents'];
                $("#price").val(price);
                const elementFields = document.getElementById('fields');
                // Check if the 'fields' element is now empty
                if (elementFields) {
                    elementFields.innerHTML = field
                }
                const element1 = document.getElementById('qty')
                if (element1) {
                    element1.innerHTML = qty
                }

                const element2 = document.getElementById('agents')
                if (element2) {
                    element2.innerHTML = agents
                }

            }
        });
    }

     $('#product').on('change', function () {
         var userId = ($('#users').val() || [])[0] || '';
         const productId = $('#product').val();

         // Remove validation error style
         const productInput = document.getElementsByName('product')[0];
         if (productInput) {
             productInput.classList.remove('is-invalid');
         }

         // Exit early if no product is selected
         if (!productId) return;

         $.ajax({
             type: 'GET',
             url: `{{ url('get-subscription') }}/${productId}?user_id=${userId}`,
             success: function (data) {
                 $('#generate').attr('disabled',false)
                 var field = data.data;
                 document.getElementById('fields1').innerHTML = field;
             },
             error: function (data, status, error) {
                 var response = data.responseJSON ? data.responseJSON : JSON.parse(data.responseText);
                 helper.showAlert({
                     message: response.message,
                     type: 'danger',
                     autoDismiss: 5000,
                     containerSelector: '#invoice-alert-container',
                 });
                 $('#generate').attr('disabled',true)
             }
         });
     });

     /* attach a submit handler to the form */
    $("#formoid").submit(function (event) {
      //   }
         /* stop form from submitting normally */
        event.preventDefault();

        /* get the action attribute from the <form action=""> element */
        var $form = $(this),
        url = $form.attr('action');
        var user = $('#users').val()?.[0];
        var plan = "";
        var subscription = 'false';
        var description = "";
        var product=document.getElementById("product").value;  
        var currentDate = new Date();
        var currentDate = new Date();
        var isoTime = currentDate.toISOString().split('T')[1].substring(0, 8);
        var selectedDate = new Date($("#datepicker").val());
        var combinedDateTime = new Date(selectedDate.getFullYear(), selectedDate.getMonth(), selectedDate.getDate(), currentDate.getHours(), currentDate.getMinutes(), currentDate.getSeconds());
        var dateFormat=$("#datepicker").val();
        $("#datepicker").val(combinedDateTime.toISOString().split('T')[0] + 'T' + isoTime);

        if (product != '') {
            var plan = document.getElementsByName('plan')[0].value;
            subscription = 'true';
        }
        if ($('#description').length > 0) {
            var description = document.getElementsByName('description')[0].value;
        }
        if ($('#domain').length > 0) {
            var domain = document.getElementsByName('domain')[0].value;
            var data = $("#formoid").serialize() + '&domain=' + domain + '&user=' + user;
            if ($('#quantity').length > 0) {
                var quantity = document.getElementsByName('quantity')[0].value;
                var data = $("#formoid").serialize() + '&domain=' + domain + '&quantity=' + quantity + '&user=' + user;
            } else if ($('#agents').length > 0) {
                 var agents = document.getElementsByName('agents')[0].value;
                 var data = $("#formoid").serialize() + '&domain=' + domain + '&agents=' + agents + '&user=' + user;
            } else{
                var data = $("#formoid").serialize() + '&domain=' + domain + '&user=' + user;
            }
        } else {
            if ($('#quantity').length > 0) {
                var quantity = document.getElementsByName('quantity')[0].value;
                var data = $("#formoid").serialize() + '&quantity=' + quantity + '&user=' + user;
            }
            else if(document.getElementsByName('agents')[0]){
                var agents = document.getElementsByName('agents')[0].value;
                var cloud_domain = document.getElementsByName('cloud_domain')[0]?.value ?? '';
                if(cloud_domain !== ''){
                    var data = $("#formoid").serialize() + '&agents=' + agents + '&user=' + user + '&cloud_domain=' +cloud_domain;
                }
                else{
                    var data = $("#formoid").serialize() + '&agents=' + agents + '&user=' + user;
                }
            }
            else {
                var data = $("#formoid").serialize() + '&user=' + user;
            }
        }
        data = data + '&plan=' + plan + '&subscription=' + subscription+'&description='+description;
        $("#generate").html("<i class='fas fa-circle-notch fa-spin'></i>  {{ __('message.please_wait') }}");

        $.ajax({
            type: "POST",
            url: url,
            data: data,
            success: function (data) {

                $("#generate").html("<i class='fas fa-sync-alt'>&nbsp;&nbsp;</i>{{ __('message.generate') }}");
                // $('#formoid')[0].reset();             
                if(data.success == true) {
                    $('#fails').hide();
                        $('#error').hide();
                        $('#successs').show();
                    var result =  '<div class="alert alert-success alert-dismissable"><button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button><strong><i class="fa fa-check"></i>{{ __('message.success') }}! </strong>'+data.message.success+'!</div>';
                    $('#successs').html(result);
                    setTimeout(function(){
                        $("#successs").slideUp(1000);
                    },10000);
                     $('#formoid').trigger("reset");
                     $('select').prop('selectedIndex', 0);
                     $("#users").val("");
                     $("#users").trigger("change");
                }
            },
            error: function (response) {
                $('#datepicker').val('');
                $('#datepicker').val(dateFormat);

                $("#generate").html("<i class='fas fa-sync-alt'>&nbsp;&nbsp;</i>{{ __('message.generate') }}");
                if(response.responseJSON.success == false) {
                    var html = '<div class="alert alert-danger"><button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button><strong>{{ __('message.whoops') }}! </strong>{{ __('message.something_wrong') }}<ul>';
                    html += '<li>' + response.responseJSON.message[0] + '</li>'
                } else {
                    var html = '<div class="alert alert-danger"><button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button><strong>{{ __('message.whoops') }}! </strong>{{ __('message.something_wrong') }}<br><br><ul>';
                for (var key in response.responseJSON.errors)
                {
                    html += '<li>' + response.responseJSON.errors[key][0] + '</li>'
                }
                 
                }                
                 html += '</ul></div>';
                 $('#error').show();
                setTimeout(function(){
                    $("#error").slideUp(1000);
                    $("#success").slideUp(1000);
                },10000);

                  document.getElementById('error').innerHTML = html;
                  $('#plan').addClass('is-invalid');
                document.getElementById('subscription-msg').innerHTML = response.responseJSON.errors['plan'];
                document.getElementById('price-msg').innerHTML = response.responseJSON.errors['price'];
                document.getElementById('product-msg').innerHTML = response.responseJSON.errors['product'];
                document.getElementById('user-msg').innerHTML = response.responseJSON.errors['user'];
            }
        });

    });
</script>
@stop
@section('datepicker')
<script>
     $('#invoice_date').datetimepicker({
      format: 'L'
    })

        $('#users').select2({
        placeholder: "{{ __('message.search') }}",
        minimumInputLength: 1,
        maximumSelectionLength: 1,
        ajax: {
            url: '{{route("search-email")}}',
            dataType: 'json',
            beforeSend: function(){
                $('.loader').css('display', 'block');
            },
            complete: function() {
                $('.loader').css('display', 'none');
            },
            data: function (params) {
                return {
                    q: $.trim(params.term)
                };
            },
            processResults: function (data) {
                return {
                      results: $.map(data, function (value) {
                    return {
                        image:value.profile_pic,
                        text:value.first_name+" "+value.last_name,
                        id: value.id,
                        email:value.text
                    }
                 })
                  }
            },
            cache: true
        },
           templateResult: formatState,
    });
        function formatState (state) { 
       
       var $state = $( '<div><div style="width: 17%;display: inline-block;"><img src='+state.image+' width=35px" height="35px" style="vertical-align:inherit"></div><div style="width: 80%;display: inline-block;"><div>'+state.text+'</div><div>'+state.email+'</div></div></div>');
        return $state;
    }

</script>
@stop