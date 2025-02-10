@extends('themes.default1.layouts.master')
@section('title')
{{ __('message.create_coupon') }}
@stop
@section('content-header')
    <div class="col-sm-6">
        <h1>{{ __('message.create_new_coupon') }}</h1>
    </div>
    <div class="col-sm-6">
        <ol class="breadcrumb float-sm-right">
            <li class="breadcrumb-item"><a href="{{url('/')}}"><i class="fa fa-dashboard"></i> {{ __('message.home') }}</a></li>
            <li class="breadcrumb-item"><a href="{{url('promotions')}}"><i class="fa fa-dashboard"></i> {{ __('message.all_coupons') }}</a></li>
            <li class="breadcrumb-item active">{{ __('message.create_new_coupon') }}</li>
        </ol>
    </div><!-- /.col -->


@stop

@section('content')

<div class="row">

    <div class="col-md-12">
        <div class="card card-secondary card-outline">


            <div class="card-body">
                {!! html()->form('POST', url('promotions'))->id('myform')->open() !!}

                <table class="table table-condensed">

                    <tr>
                        <td><b>{!! html()->label(Lang::get('message.code'), 'company')->class('required') !!}</b></td>
                        <td>
                            <div class="form-group {{ $errors->has('code') ? 'has-error' : '' }}">
                                    <div class="d-flex">
                                    <div class="col-md-6">
                                        <!-- {!! html()->text('code')->class('form-control')->id('code') !!} -->
                                        {!! html()->text('code')->class('form-control'.($errors->has('code') ? ' is-invalid' : ''))->id('code')->attribute('title', __('message.generation_coupon_code')) !!}
                                       <!--   <input id="code" name="code" type="text" class="form-control" title="Generate Coupon Code"/> -->
                                        @error('code')
                                        <span class="error-message"> {{$message}}</span>
                                        @enderror
                                        <div class="input-group-append">
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <a href="#" class="btn btn-primary" id="get-code"><i class="fa fa-refresh"></i>&nbsp;{{ __('message.generate_code') }}</a>
                                    </div>
                                </div>

                            </div>
                        </td>

                    </tr>
                    <tr>

                        <td><b>{!! html()->label(Lang::get('message.type'), 'type')->class('required') !!}</b></td>

                        <td>
                            <div class="form-group col-lg-6 {{ $errors->has('type') ? 'has-error' : '' }}">
                                {!! html()->select('type', ['' => __('message.select'), 'Types' => $type])->class('form-control'.($errors->has('type') ? ' is-invalid' : ''))->attribute('title', __('message.type_of_coupon')) !!}
                                @error('type')
                                <span class="error-message"> {{$message}}</span>
                                @enderror
                                <div class="input-group-append">
                                </div>
                            </div>
                        </td>

                    </tr>
                    <tr>
                        <td><b>{!! html()->label(Lang::get('message.value'), 'value')->class('required') !!} &nbsp;&nbsp;<i class="fas fa-question-circle" data-toggle="tooltip" data-placement="top" title="{{ __('message.enter_discount_amount') }}"></i></b></td>
                        <td>
                            <div class="form-group col-lg-6 {{ $errors->has('value') ? 'has-error' : '' }}">
                                {!! html()->text('value')->class('form-control'.($errors->has('value') ? ' is-invalid' : ''))->attribute('title', __('message.value_of_coupon')) !!}
                                @error('value')
                                <span class="error-message"> {{$message}}</span>
                                @enderror
                                <div class="input-group-append">
                                </div>

                            </div>
                        </td>

                    </tr>
                    <tr>
                        <td><b>{!! html()->label(Lang::get('message.uses'), 'uses')->class('required') !!} &nbsp;&nbsp;<i class="fas fa-question-circle" data-toggle="tooltip" data-placement="top" title="{!! __('message.enter_times_coupon_usage_limit') !!}"></i></b></td>
                        <td>
                            <div class="form-group col-lg-6 {{ $errors->has('uses') ? 'has-error' : '' }}">
                                {!! html()->text('uses')->class('form-control'.($errors->has('uses') ? ' is-invalid' : ''))->attribute('title', __('message.coupon_used')) !!}
                                @error('uses')
                                <span class="error-message"> {{$message}}</span>
                                @enderror
                                <div class="input-group-append">
                                </div>

                            </div>
                        </td>

                    </tr>
                    <tr>

                        <td><b>{!! html()->label(Lang::get('message.applied'), 'applied')->class('required') !!}</b></td>
                        <td>

                            <div class="form-group col-lg-6{{ $errors->has('applied') ? 'has-error' : '' }}">
                                {!! html()->select('applied', ['' => __('message.choose'), 'Products' => $product])
     ->class('form-control'.($errors->has('applied') ? ' is-invalid' : ''))
     ->attribute('data-live-search', 'true')
     ->attribute('data-live-search-placeholder', 'Search')
     ->attribute('data-dropup-auto', 'false')
     ->attribute('data-size', '10')
     ->attribute('title', __('message.coupon_applied')) !!}
                                @error('applied')
                                <span class="error-message"> {{$message}}</span>
                                @enderror

                                <div class="input-group-append">
                                </div>
                           </div>
                        </td>

                    </tr>
                    <tr>

                        <td><b>{!! html()->label(Lang::get('message.start'), 'start')->class('required') !!}</b></td>
                        <td>
                            <div class="form-group {{ $errors->has('start') ? 'has-error' : '' }}">
                                <div class="input-group date col-lg-6" id="startDate" data-target-input="nearest">
                                    {!! html()->text('start')->class('form-control datetimepicker-input'.($errors->has('start') ? ' is-invalid' : ''))->attribute('title', __('message.coupon_valid'))->attribute('data-target', '#startDate') !!}
                                    <div class="input-group-append" data-target="#startDate" data-toggle="datetimepicker">
                                        <div class="input-group-text"><i class="fa fa-calendar"></i></div>
                                    </div>
                                    <div class="input-group-append">
                                    </div>
                                </div>
                                @error('start')
                                <div class="input-group date col-lg-6">
                                    <span class="error-message"> {{$message}}</span>
                                </div>
                                @enderror
                            </div>
                        </td>

                    </tr>


                    <tr>

                        <td><b>{!! html()->label(Lang::get('message.expiry'), 'expiry')->class('required') !!}</b></td>
                        <td>
                            <div class="form-group {{ $errors->has('expiry') ? 'has-error' : '' }}">

                                <div class="input-group date col-lg-6" id="endDate" data-target-input="nearest">
                                    {!! html()->text('expiry')->class('form-control datetimepicker-input'.($errors->has('expiry') ? ' is-invalid' : ''))->attribute('title', __('message.coupon_expires'))->attribute('data-target', '#endDate') !!}

                                    <div class="input-group-append" data-target="#endDate" data-toggle="datetimepicker">
                                        <div class="input-group-text"><i class="fa fa-calendar"></i></div>
                                    </div>

                                    <div class="input-group-append">
                                    </div>
                                </div>
                                @error('expiry')
                                <div class="input-group date col-lg-6">
                                    <span class="error-message"> {{$message}}</span>
                                </div>
                                @enderror

                            </div>
                        </td>

                    </tr>




                    {!! html()->form()->close() !!}

                </table>
                <button type="submit" class="btn btn-primary pull-right" id="submit" data-loading-text="<i class='fa fa-circle-o-notch fa-spin'>&nbsp;</i> {{ __('message.saving') }}"><i class="fa fa-save">&nbsp;</i>{!!Lang::get('message.save')!!}</button>
            </div>

        </div>
        <!-- /.box -->
    </div>
</div>
<script>

    $(document).ready(function() {
        const userRequiredFields = {
            code:@json(trans('message.coupon_details.add_code')),
            type:@json(trans('message.coupon_details.add_type')),
            uses:@json(trans('message.coupon_details.add_uses')),
            applied:@json(trans('message.coupon_details.add_applied')),
            expiry:@json(trans('message.coupon_details.add_expiry')),
            start:@json(trans('message.coupon_details.add_start')),
            value:@json(trans('message.coupon_details.add_value')),

        };

        $('#myform').on('submit', function (e) {
            const userFields = {
                code:$('#code'),
                value:$('#value'),
                type:$('#type'),
                uses:$('#uses'),
                expiry:$('#expiry'),
                start:$('#start'),
                applied:$('#applied'),
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
            if(isValid && !isValidDate(userFields.start.val())){
                showError(userFields.start, @json(trans('message.invoice_details.add_valid_date')));
                isValid = false;
            }

            if(isValid && !isValidDate(userFields.expiry.val())){
                showError(userFields.expiry, @json(trans('message.invoice_details.add_valid_date')));
                isValid = false;
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
        ['code','uses','applied','value','type'].forEach(id => {

            document.getElementById(id).addEventListener('input', function () {
                removeErrorMessage(this);

            });
        });
        function isValidDate(dateString) {
            const regex = /^(0[1-9]|1[0-2])\/(0[1-9]|[12][0-9]|3[01])\/\d{4}$/;

            return regex.test(dateString);
        }


    });

    $(document).on('input change', '[name="start"]', function () {
        $(this).removeClass('is-invalid'); // Remove error class when input changes
    });


    $(document).on('input change', '[name="expiry"]', function () {
        $(this).removeClass('is-invalid'); // Remove error class when input changes
    });

     $('ul.nav-sidebar a').filter(function() {
        return this.id == 'coupon';
    }).addClass('active');

    // for treeview
    $('ul.nav-treeview a').filter(function() {
        return this.id == 'coupon';
    }).parentsUntil(".nav-sidebar > .nav-treeview").addClass('menu-open').prev('a').addClass('active');

   $(document).ready(function(){
            $(function () {
                //Initialize Select2 Elements
                $('.select2').select2()
            });
        })

        $('#get-code').on('click',function(){
            $.ajax({
            type: "GET",
            url: "{{url('get-promotion-code')}}",
            success: function (data) {
                $("#code").val(data)
            }
        });

        })
        

      $("#myform :input").tooltip({
 
      // place tooltip on the right edge
      position: "center right",
 
      // a little tweaking of the position
      offset: [-2, 10],
 
      // use the built-in fadeIn/fadeOut effect
      effect: "fade",
 
      // custom opacity setting
      opacity: 0.7
 
      });

  
</script>
@stop
@section('datepicker')
<script type="text/javascript">

    $('#startDate').datetimepicker({
        format: 'L'
    });
    $('#endDate').datetimepicker({
        format: 'L'
    });

    $(document).ready(function() {
    $('[data-toggle="tooltip"]').tooltip();
});
</script>


@stop