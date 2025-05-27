@extends('themes.default1.layouts.master')
@section('title')
    {{ __('message.contact-options') }}
@stop
@section('content-header')
    <div class="col-sm-6 md-6">
        <h1>{{ __('message.contact_options') }}</h1>
    </div>
    <div class="col-sm-6 md-6">
        <ol class="breadcrumb float-sm-right">
            <li class="breadcrumb-item"><a href="{{url('/')}}"><i class="fa fa-dashboard"></i> {{ __('message.home') }}</a></li>
            <li class="breadcrumb-item"><a href="{{url('settings')}}"> {{ __('message.settings') }}</a></li>
            <li class="breadcrumb-item active">{{ __('message.contact_options') }}</li>
        </ol>
    </div><!-- /.col -->
@stop


@section('content')
    <div id="alertContainer"></div>
    <div class="card card-secondary card-outline">
        <div class="card-header">
            <h3 class="card-title">Settings</h3>
        </div>
        <!-- /.card-header -->
        <!-- form start -->
        {!! html()->form()->id('verification-form')->open() !!}
        <div class="card-body">
                <!-- Email & Mobile Toggles on the Same Row -->
                <div class="form-group row">
                    <label class="col-sm-4 col-form-label">Enable Verifications</label>
                    <div class="col-sm-4 d-flex align-items-center">
                        <div class="custom-control custom-switch mr-3">
                            {!! html()->checkbox('email_enabled')->checked($emailStatus)->value(1)->id('email_enabled')->class('custom-control-input') !!}
                            <label class="custom-control-label" for="email_enabled">Email</label>
                        </div>
                    </div>
                    <div class="col-sm-4 d-flex align-items-center">
                        <div class="custom-control custom-switch">
                            {!! html()->checkbox('mobile_enabled')->checked($mobileStatus)->value(1)->id('mobile_enabled')->class('custom-control-input') !!}
                            <label class="custom-control-label" for="mobile_enabled">Mobile</label>
                        </div>
                    </div>
                </div>

                <!-- Preference Dropdown (Initially Visible or Hidden) -->
                <div class="form-group row" id="preference_group">
                    <label for="preferred_verification" class="col-sm-4 col-form-label">Preferred Verification</label>
                    <div class="col-sm-8">
                        {!! html()->select('preferred_verification')->options(['' => 'Select Preference','email' => 'Email First','mobile' => 'Mobile First',])->class('form-control')->id('preferred_verification')->value($preferred_verification) !!}
                    </div>
                </div>
            </div>

            <!-- /.card-body -->

            <div class="card-footer">
                <button type="submit" class="btn btn-primary pull-right" id="contact-form-option"
                        data-loading-text="<i class='fa fa-circle-o-notch fa-spin'>&nbsp;</i> Saving...">
                    <i class="fa fa-save">&nbsp;&nbsp;</i>{{ trans('message.save') }}
                </button>
            </div>
        {!! html()->form()->close() !!}
    </div>


    <script>
        $(document).ready(function () {
            $('[data-toggle="tooltip"]').tooltip();

            function updatePreferredDropdown() {
                let emailEnabled = $('#email_enabled').is(':checked');
                let mobileEnabled = $('#mobile_enabled').is(':checked');
                let $dropdown = $('select[name="preferred_verification"]');
                if (emailEnabled && mobileEnabled) {
                    $dropdown.prop('disabled', false);
                } else {
                    $dropdown.prop('disabled', true);

                    if (emailEnabled) {
                        $dropdown.val('email');
                    } else if (mobileEnabled) {
                        $dropdown.val('mobile');
                    } else {
                        $dropdown.val('');
                    }
                }
            }

            // Call once on load
            updatePreferredDropdown();

            // Bind change events
            $('#email_enabled, #mobile_enabled').change(updatePreferredDropdown);

            // Form submit handler
            $('#verification-form').submit(function (e) {
                e.preventDefault(); // Prevent default form submission
                const submitButton = $('#contact-form-option');

                $.ajax({
                    url: "{{ url('verificationSettings') }}",
                    type: 'POST',
                    data: {
                        email_enabled: $('#email_enabled').is(':checked') ? 1 : 0,
                        mobile_enabled: $('#mobile_enabled').is(':checked') ? 1 : 0,
                        preferred_verification: $('#preferred_verification').val(),
                    },
                    beforeSend: function () {
                        submitButton.prop('disabled', true).html(submitButton.data('loading-text'));
                    },
                    success: function (response) {
                        helper.showAlert({
                            id: 'my-alert',
                            message: response.message,
                            type: 'success',
                            autoDismiss: 5000,
                            containerSelector: '#alertContainer',
                        });
                    },
                    error: function (xhr) {
                        helper.showAlert({
                            id: 'my-alert',
                            message: 'Error Occurred: ' + xhr.responseJSON.message,
                            type: 'error',
                            autoDismiss: 5000,
                            containerSelector: '#alertContainer',
                        });
                    },
                    complete: function () {
                        submitButton.prop('disabled', false).html("<i class='fa fa-save'>&nbsp;&nbsp;</i>{{ trans('message.save') }}");
                    }
                });
            });
        });
    </script>
 @stop