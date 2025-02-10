@extends('themes.default1.layouts.master')
@section('title')
    {{ __('message.edit-invoice') }}
@stop
@section('content-header')
    <div class="col-sm-6">
        <h1> {{ __('message.edit_invoice') }}</h1>
    </div>
    <div class="col-sm-6">
        <ol class="breadcrumb float-sm-right">
            <li class="breadcrumb-item"><a href="{{url('/')}}"><i class="fa fa-dashboard"></i> {{ __('message.home') }}</a></li>
            <li class="breadcrumb-item"><a href="{{url('clients')}}"> {{ __('message.all-users') }}</a></li>
            <li class="breadcrumb-item"><a href="{{url('clients/'.$invoice->user_id)}}">{{ __('message.view_user') }}</a></li>
            <li class="breadcrumb-item active">{{ __('message.edit_invoice') }}</li>
        </ol>
    </div><!-- /.col -->

@stop

@section('content')

<div class="card card-secondary card-outline">

    <div class="card-header">
        {!! html()->form('POST', url('invoice/edit/'.$invoiceid))->id('editInvoiceForm')->open() !!}

        <h5>{{ __('message.invoice_number') }}:#{{$invoice->number}}	</h5>

    </div>

    <div class="card-body">
        <div class="row">
            <div class="col-md-12">
                <div class="row">

                   <div class="col-md-6 form-group {{ $errors->has('date') ? 'has-error' : '' }}">
                        <!-- date -->
                       {!! html()->label(Lang::get('message.date'))->class('required') !!}
                                 <input type="text" id="payment_date" name="date" value="{{$date}}" class="form-control datetimepicker-input {{$errors->has('date') ? ' is-invalid' : ''}}" autocomplete="off"  data-target="#payment" />
                             @error('date')
                             <span class="error-message"> {{$message}}</span>
                             @enderror
                                <div class="input-group-append" data-target="#payment" data-toggle="datetimepicker">
                                    <div class="input-group-text"><i class="fa fa-calendar"></i></div>

                                </div>
                            </div>
                    </div>

                    <div class="col-md-6 form-group {{ $errors->has('total') ? 'has-error' : '' }}">
                        <!-- total -->
                        {!! html()->label(Lang::get('message.invoice-total'))->class('required') !!}
                        <input type="text" name="total" class="form-control {{$errors->has('total') ? ' is-invalid' : ''}}" value="{{$invoice->grand_total}}" id="total">
                        @error('total')
                        <span class="error-message"> {{$message}}</span>
                        @enderror
                        <div id="error">
                        </div>
                    </div>


                     <div class="col-md-6 form-group {{ $errors->has('amount') ? 'has-error' : '' }}">
                        <!-- status -->
                         {!! html()->label(Lang::get('message.status'))->for('status') !!}
                         <select name="status"  class="form-control {{$errors->has('status') ? ' is-invalid' : ''}}" id="status">
                            <option selected="selected">{{$invoice->status}}</option>
                             <option value="">{{ __('message.choose') }}</option>
                          <option value="success">{{ __('message.success') }}</option>
                        <option value="pending">{{ __('message.pending') }}</option>
                         </select>
                         @error('status')
                         <span class="error-message"> {{$message}}</span>
                         @enderror
                         <div id="error">
                         </div>
                    </div>

                </div>
                <button type="submit" class="form-group btn btn-primary pull-right" id="submit"><i class="fa fa-save">&nbsp;</i>{!!Lang::get('message.update')!!}</button>

            </div>

        </div>

    </div>

</div>


{!! html()->form()->close() !!}
<script>

    $(document).ready(function() {


        const userRequiredFields = {
            payment_date:@json(trans('message.invoice_details.payment_date')),
            total:@json(trans('message.invoice_details.total')),
            status:@json(trans('message.invoice_details.status')),

        };

        $('#editInvoiceForm').on('submit', function (e) {
            const userFields = {
                payment_date:$('#payment_date'),
                total:$('#total'),
                status:$('#status'),
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

            if(isValid && !isValidDate(userFields.payment_date.val())){
                    showError(userFields.payment_date, @json(trans('message.invoice_details.add_valid_date')));
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

        function isValidDate(dateString) {
            const regex = /^(0[1-9]|1[0-2])\/(0[1-9]|[12][0-9]|3[01])\/\d{4}$/;
            return regex.test(dateString);
        }

        // Add input event listeners for all fields
        ['user','product','price','date'].forEach(id => {

            document.getElementById(id).addEventListener('input', function () {
                removeErrorMessage(this);

            });
        });
    });

     $('ul.nav-sidebar a').filter(function() {
        return this.id == 'all_invoice';
    }).addClass('active');

    // for treeview
    $('ul.nav-treeview a').filter(function() {
        return this.id == 'all_invoice';
    }).parentsUntil(".nav-sidebar > .nav-treeview").addClass('menu-open').prev('a').addClass('active');
</script>
@stop
@section('datepicker')
<script>
        $(function () {
        $('#payment').datetimepicker({
            format: 'L'
        });
    });
</script>
@stop