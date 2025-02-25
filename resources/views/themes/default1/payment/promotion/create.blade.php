@extends('themes.default1.layouts.master')
@section('title')
Create Coupon
@stop
@section('content-header')
    <div class="col-sm-6">
        <h1>Create New Coupon</h1>
    </div>
    <div class="col-sm-6">
        <ol class="breadcrumb float-sm-right">
            <li class="breadcrumb-item"><a href="{{url('/')}}"><i class="fa fa-dashboard"></i> Home</a></li>
            <li class="breadcrumb-item"><a href="{{url('promotions')}}"><i class="fa fa-dashboard"></i> All Coupons</a></li>
            <li class="breadcrumb-item active">Create New Coupon</li>
        </ol>
    </div><!-- /.col -->


@stop

@section('content')

<div class="row">

    <div class="col-md-12">
        <div class="card card-secondary card-outline">


            <div class="card-body">
                {!! Form::open(['url'=>'promotions','id'=>'myform']) !!}

                <table class="table table-condensed">

                    <tr>
                        <td><b>{!! Form::label('company',Lang::get('message.code'),['class'=>'required']) !!}</b></td>
                        <td>
                            <div class="form-group {{ $errors->has('code') ? 'has-error' : '' }}">
                                    <div class="d-flex">
                                    <div class="col-md-6">
                                        <!-- {!! Form::text('code',null,['class' => 'form-control','id'=>'code']) !!} -->
                                        {!! Form::text('code',null,['class' => 'form-control','id'=>'code','title'=>'Generate Coupon Code']) !!}
                                       <!--   <input id="code" name="code" type="text" class="form-control" title="Generate Coupon Code"/> -->
                                        @error('code')
                                        <span class="error-message"> {{$message}}</span>
                                        @enderror
                                        <div class="input-group-append">
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <a href="#" class="btn btn-primary" id="get-code"><i class="fa fa-refresh"></i>&nbsp;Generate Code</a>
                                    </div>
                                </div>

                            </div>
                        </td>

                    </tr>
                    <tr>

                        <td ><b>{!! Form::label('type',Lang::get('message.type'),['class'=>'required']) !!}</b></td>

                        <td>
                            <div class="form-group col-lg-6 {{ $errors->has('type') ? 'has-error' : '' }}">


                                {!! Form::select('type',[''=>'Select','Types'=>$type],null,['class' => 'form-control',  'title'=>"Type Of Coupon"]) !!}
                                @error('type')
                                <span class="error-message"> {{$message}}</span>
                                @enderror
                                <div class="input-group-append">
                                </div>
                            </div>
                        </td>

                    </tr>
                    <tr>

                        <td><b>{!! Form::label('value',Lang::get('message.value'),['class'=>'required']) !!} &nbsp;&nbsp;<i class="fas fa-question-circle" data-toggle="tooltip" data-placement="top" title="Enter the discount amount."></i></b></td>
                        <td>
                            <div class="form-group col-lg-6 {{ $errors->has('value') ? 'has-error' : '' }}">
                                {!! Form::number('value',null,['class' => 'form-control','title'=>'Value of the Coupon']) !!}
                                @error('value')
                                <span class="error-message"> {{$message}}</span>
                                @enderror
                                <div class="input-group-append">
                                </div>

                            </div>
                        </td>

                    </tr>
                    <tr>

                        <td><b>{!! Form::label('uses',Lang::get('message.uses'),['class'=>'required']) !!} &nbsp;&nbsp;<i class="fas fa-question-circle" data-toggle="tooltip" data-placement="top" title="Enter the maximum number of times the coupon can be used, Coupon usage limit."></i></b></td>
                        <td>
                            <div class="form-group col-lg-6 {{ $errors->has('uses') ? 'has-error' : '' }}">
                                {!! Form::number('uses',null,['class' => 'form-control','title'=>'No. Of times the coupon can be Used']) !!}
                                @error('uses')
                                <span class="error-message"> {{$message}}</span>
                                @enderror
                                <div class="input-group-append">
                                </div>

                            </div>
                        </td>

                    </tr>
                    <tr>

                        <td><b>{!! Form::label('applied',Lang::get('message.applied'),['class'=>'required']) !!}</b></td>
                        <td>

                            <div class="form-group col-lg-6{{ $errors->has('applied') ? 'has-error' : '' }}">
                                {!! Form::select('applied',[''=>'Choose','Products'=>$product],null,['class' => 'form-control','data-live-search'=>'true','data-live-search-placeholder' => 'Search','data-dropup-auto'=>'false','data-size'=>'10','title'=>'Products for which coupon is Applied']) !!}
                                @error('applied')
                                <span class="error-message"> {{$message}}</span>
                                @enderror

                                <div class="input-group-append">
                                </div>
                           </div>
                        </td>

                    </tr>
                    <tr>

                        <td><b>{!! Form::label('start',Lang::get('message.start'),['class'=>'required']) !!}</b></td>
                        <td>
                            <div class="form-group {{ $errors->has('start') ? 'has-error' : '' }}">
                                <div class="input-group date col-lg-6" id="startDate" data-target-input="nearest">

                                     {!! Form::text('start',null,['class' => 'form-control datetimepicker-input','title'=>'Date from which Coupon is Valid','data-target'=>'#startDate']) !!}
                                    <div class="input-group-append" data-target="#startDate" data-toggle="datetimepicker">
                                        <div class="input-group-text"><i class="fa fa-calendar"></i></div>
                                    </div>
                                    @error('start')
                                    <span class="error-message"> {{$message}}</span>
                                    @enderror
                                    <div class="input-group-append">
                                    </div>
                                </div>

                            </div>
                        </td>

                    </tr>


                    <tr>

                        <td><b>{!! Form::label('expiry',Lang::get('message.expiry'),['class'=>'required']) !!}</b></td>
                        <td>
                            <div class="form-group {{ $errors->has('expiry') ? 'has-error' : '' }}">

                                <div class="input-group date col-lg-6" id="endDate" data-target-input="nearest">

                                    {!! Form::text('expiry',null,['class' => 'form-control datetimepicker-input','title'=>'Date on which Coupon Expires','data-target'=>'#endDate']) !!}

                                   
                                    <div class="input-group-append" data-target="#endDate" data-toggle="datetimepicker">
                                        <div class="input-group-text"><i class="fa fa-calendar"></i></div>
                                    </div>
                                    @error('expiry')
                                    <span class="error-message"> {{$message}}</span>
                                    @enderror
                                    <div class="input-group-append">
                                    </div>
                                </div>
                            </div>
                        </td>

                    </tr>




                    {!! Form::close() !!}

                </table>
                <button type="submit" class="btn btn-primary pull-right" id="submit" data-loading-text="<i class='fa fa-circle-o-notch fa-spin'>&nbsp;</i> Saving..."><i class="fa fa-save">&nbsp;</i>{!!Lang::get('message.save')!!}</button>
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
        ['code','uses','applied','expiry','start','value','type'].forEach(id => {

            document.getElementById(id).addEventListener('input', function () {
                removeErrorMessage(this);

            });
        });
        function isValidDate(dateString) {
            const regex = /^(0[1-9]|1[0-2])\/(0[1-9]|[12][0-9]|3[01])\/\d{4}$/;

            return regex.test(dateString);
        }

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
                console.log(data);
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