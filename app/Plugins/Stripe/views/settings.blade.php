@extends('themes.default1.layouts.master')

@section('title', 'Stripe')

@section('content-header')
    <div class="col-sm-6">

        <h1>Stripe</h1>
    </div>
    <div class="col-sm-6">
        <ol class="breadcrumb float-sm-right">
            <li class="breadcrumb-item"><a href="{{ url('/') }}"><i class="fa fa-dashboard"></i> {{ __('message.home') }}</a></li>
            <li class="breadcrumb-item"><a href="{{ url('settings') }}"><i class="fa fa-dashboard"></i> {{ __('message.settings') }}</a></li>
            <li class="breadcrumb-item"><a href="{{ url('payment-gateway-integration') }}"><i class="fa fa-dashboard"></i> {{ __('message.payment_gateway_integrations') }}</a></li>
            <li class="breadcrumb-item active">{{ __('message.stripe') }}</li>
        </ol>
    </div>
@stop

@section('content')
    <style>
        .custom-error{
            font-size: 100%;
            font-weight:normal !important;
            color:#dc3545;
        }
    </style>
    <div class="col-md-12">
        <div class="card card-secondary card-outline">
            <div class="card-header">
                <h3 class="card-title">{{ __('message.key') }}</h3>
            </div>
            <div class="card-body">
                <div id="alertMessage"></div>
                <form id="stripeForm" method="POST">
                    <div class="form-group col-lg-5">
                        <label for="stripe_key" class="required">{{ __('message.stripe_publishable_key') }}</label>
                        <input
                                type="text"
                                id="stripe_key"
                                name="stripe_key"
                                value="{{ $stripeKeys->stripe_key }}"
                                class="form-control {{$errors->has('stripe_key') ? ' is-invalid' : ''}}"
                                placeholder="{{ __('message.enter_stripe_key') }}">
                        @error('stripe_key')
                        <small class="text-danger">{{ $message }}</small>
                        @enderror
                        <small id="stripe_keycheck" class="text-danger"></small>
                    </div>
                    <div class="form-group col-lg-5">
                        <label for="stripe_secret" class="required">{{ __('message.stripe_secret_key') }}</label>
                        <div class="input-group">
                        <input
                                type="password"
                                id="stripe_secret"
                                name="stripe_secret"
                                value="{{ $stripeKeys->stripe_secret }}"
                                class="form-control {{$errors->has('stripe_secret') ? ' is-invalid' : ''}}"
                                placeholder="{{ __('message.enter_stripe_secret_key') }}">
                        <div class="input-group-append">
                                        <span class="input-group-text" role="button" onclick="togglePasswordVisibility(this)">
                                            <i class="fa fa-eye-slash"></i>
                                        </span>
                        </div>
                        </div>
                        @error('stripe_secret')
                        <small class="text-danger">{{ $message }}</small>
                        @enderror
                        <small id="stripe_secretcheck" class="text-danger"></small>
                    </div>
                    <button type="submit" class="btn btn-primary" id="key_update">
                        <i class="fa fa-sync-alt"></i> {{ __('message.update') }}
                    </button>
                </form>
            </div>
        </div>
    </div>

    <script>
        $(document).ready(function() {
            function placeErrorMessage(error, element, errorMapping) {
                var errorContainer = errorMapping[element.attr("name")];

                if (errorContainer) {
                    $(errorContainer).html(error);
                } else {
                    error.insertAfter(element);
                }
            }
            $("#stripeForm").validate({
                errorClass:"custom-error",
                rules: {
                    stripe_key: {
                        required: true,
                        maxlength: 200
                    },
                    stripe_secret: {
                        required: true,
                        maxlength: 200
                    }
                },
                messages: {
                    stripe_key: {
                        required: "{{ __('message.required_stripe_key') }}",
                        maxlength: "{{ __('message.max_stripe_key') }}"
                    },
                    stripe_secret: {
                        required: "{{ __('message.required_stripe_secret') }}",
                        maxlength: "{{ __('message.max_stripe_secret') }}"
                    }
                },
                errorPlacement: function (error, element) {
                    var errorMapping = {
                        "stripe_key": "#stripe_keycheck",
                        "stripe_secret": "#stripe_secretcheck"
                    };

                    element.addClass('is-invalid');

                    placeErrorMessage(error, element, errorMapping);
                },

                submitHandler: function(form, event) {
                    event.preventDefault();
                    $('#key_update').html("<i class='fas fa-circle-notch fa-spin'></i> {{ __('message.please_wait') }}");
                    $("#key_update").attr('disabled', true);

                    // AJAX Request if validation passes
                    $.ajax({
                        url: '{{ url("update-api-key/payment-gateway/stripe") }}',
                        type: 'POST',
                        data: {
                            "stripe_key": $('#stripe_key').val(),
                            "stripe_secret": $('#stripe_secret').val()
                        },
                        success: function(data) {
                            $('#alertMessage').html(`<div class="alert alert-success alert-dismissable">
                        <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
                        <strong><i class="fa fa-check"></i> {{ __('message.success') }}! </strong>${data.message.message}.
                    </div>`).slideDown();
                        $("#key_update").html("<i class='fa fa-sync-alt'></i> {{ __('message.update') }}").attr('disabled', false);
                        setTimeout(function () { $('#alertMessage').slideUp(); }, 3000);
                    },
                    error: function (data) {
                        $('#alertMessage').html(`<div class="alert alert-danger alert-dismissable">
                        <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
                        <strong><i class="fa fa-ban"></i> {{ __('message.failed') }}! </strong>${data.responseJSON.message}
                    </div>`).slideDown();
                            $("#key_update").html("<i class='fa fa-sync-alt'></i> {{ __('message.update') }}").attr('disabled', false);
                        }
                    });
                },
                onfocusout: function (element) {
                    $(element).removeClass('is-invalid');
                    var errorContainer = {
                        "stripe_key": "#stripe_keycheck",
                        "stripe_secret": "#stripe_secretcheck"
                    }[element.name];
                    if (errorContainer) {
                        $(errorContainer).html('');
                    }
                },
                onkeyup: function (element) {
                    $(element).removeClass('is-invalid');
                    var errorContainer = {
                        "stripe_key": "#stripe_keycheck",
                        "stripe_secret": "#stripe_secretcheck"
                    }[element.name];
                    if (errorContainer) {
                        $(errorContainer).html('');
                    }
                }
            });
        });
    </script>

@stop