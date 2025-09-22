@extends('themes.default1.layouts.front.master')

@section('title')
    {{ __('message.two_factory_recovery') }}
@stop

@section('page-heading')
    {{ __('message.two_factory_recovery') }}
@stop

@section('page-header')
    {{ __('message.forgot-password') }}
@stop

@section('breadcrumb')
    @if(Auth::check())
        <li><a class="text-primary" href="{{ url('my-invoices') }}">{{ __('message.home') }}</a></li>
    @else
        <li><a class="text-primary" href="{{ url('login') }}">{{ __('message.home') }}</a></li>
    @endif
    <li class="active text-dark">{{ __('message.two_factory_recovery') }}</li>
@stop

@section('main-class')
    main
@stop

@section('content')
    <div id="2fa-recovery-alert-container" class="mb-3"></div>

    <div class="container py-4">
        <div class="row justify-content-center">
            <div class="col-md-6 col-lg-6"> <!-- Match previous 2FA width -->

                {!! html()->form('POST', route('verify-recovery-code'))->id('recovery_form')->open() !!}

                {{-- Recovery Code Input --}}
                <div class="mb-4">
                    <label for="rec_code" class="form-label text-color-dark fw-bold text-3">
                        {{ __('message.enter_recovery_code') }} <span class="text-danger">*</span>
                    </label>
                    <input type="text" name="rec_code" id="rec_code" value=""
                           class="form-control form-control-lg text-4"
                           placeholder="{{ __('message.enter_code') }}">
                    <div id="codecheck" class="form-text text-danger"></div>
                </div>

                <p class="text-muted mb-4">{{ __('message.recovery_code_used') }}</p>

                {{-- Recaptcha --}}
                <div class="mb-4" id="2fa_recovery_recaptcha"></div>

                {!! honeypotField('recovery_code') !!}

                {{-- Link back to authenticator code --}}
                <div class="mb-4">
                    <a href="{{ url('verify-2fa') }}">{{ __('message.login_authenticator_passcode') }}</a>
                </div>

                {{-- Submit Button --}}
                <div class="d-grid mb-4">
                    <button type="submit"
                            id="recovery-submit-button"
                            class="btn btn-dark btn-lg fw-bold text-uppercase text-3 py-3"
                            data-loading-text="{{ __('message.loading') }}">
                        {{ __('message.verify') }}
                    </button>
                </div>

                {!! html()->form()->close() !!}

            </div>
        </div>
    </div>

    <script>
        let recoveryRecaptcha;

        (async () => {
            const recoveryRecaptchaContainer = document.getElementById('2fa_recovery_recaptcha');

            recoveryRecaptcha = await RecaptchaManager.init(recoveryRecaptchaContainer, {
                action: 'login_recovery',
            });

            // Make them globally available
            window.recoveryRecaptcha = recoveryRecaptcha;

        })();
    </script>

    <script>

        $(document).ready(function() {
            function placeErrorMessage(error, element, errorMapping = null) {
                if (errorMapping !== null && errorMapping[element.attr("name")]) {
                    $(errorMapping[element.attr("name")]).html(error);
                } else {
                    error.insertAfter(element);
                }
            }

            let alertTimeout;

            function showAlert(type, messageOrResponse) {

                // Generate appropriate HTML
                var html = generateAlertHtml(type, messageOrResponse);

                // Clear any existing alerts and remove the timeout
                $('#2fa-recovery-alert-container').html(html);
                clearTimeout(alertTimeout); // Clear the previous timeout if it exists

                // Display alert
                window.scrollTo(0, 0);

                // Auto-dismiss after 5 seconds
                alertTimeout = setTimeout(function() {
                    $('#2fa-recovery-alert-container .alert').fadeOut('slow');
                }, 5000);
            }


            function generateAlertHtml(type, response) {
                // Determine alert styling based on type
                const isSuccess = type === 'success';
                const iconClass = isSuccess ? 'fa-check-circle' : 'fa-ban';
                const alertClass = isSuccess ? 'alert-success' : 'alert-danger';

                // Extract message and errors
                const message = response.message || response || 'An error occurred. Please try again.';
                const errors = response.errors || null;

                let html = `<div class="alert ${alertClass} alert-dismissible">` +
                    `<i class="fa ${iconClass}"></i> ` +
                    `${message}` +
                    '<button type="button" class="btn-close" data-dismiss="alert" aria-hidden="true"></button>';

                html += '</div>';

                return html;
            }

            $('#recovery_form').validate({
                rules: {
                    rec_code: { required: true },
                },
                messages: {
                    rec_code: { required: "{{ __('message.please_enter_recovery_code') }}" },
                },
                unhighlight: function (element) { $(element).removeClass("is-valid"); },
                errorPlacement: function (error, element) { placeErrorMessage(error, element); },
            });

            $('#recovery_form').on('submit', async function(e) {
                e.preventDefault();

                const $form = $(this);
                const $submitButton = $("#recovery-submit-button");

                if (!$form.valid()) {
                    return;
                }

                try {
                    let recaptchaToken = await window.recoveryRecaptcha.tokenValidation(recoveryRecaptcha, "login");
                    if (!recaptchaToken) return;

                    // Collect form data
                    let formData = $form.serializeArray();
                    if (!window.recoveryRecaptcha.isDisabled() && recaptchaToken) {
                        formData.push({ name: "g-recaptcha-response", value: recaptchaToken });
                        formData.push({ name: "page_id", value: window.pageId });
                    }

                    $.ajax({
                        url: "{{ route('verify-recovery-code') }}",
                        type: 'POST',
                        data: $.param(formData),
                        success: function(response) {
                            if (response.data?.redirect) {
                                window.location.href = response.data?.redirect;
                            } else {
                                showAlert("success", response.message);
                            }
                        },
                        error: async function(xhr) {
                            let response = xhr.responseJSON || JSON.parse(xhr.responseText || "{}");

                            // Handle reCAPTCHA fallback
                            if (response.data?.show_v2_recaptcha) {
                                await window.recoveryRecaptcha.useFallback(true);
                                showAlert("error", response.message || "An unexpected error occurred.");
                                return;
                            }

                            // Handle validation errors
                            if (response.errors) {
                                let validator = $form.validate();
                                $.each(response.errors, function (field, messages) {
                                    validator.showErrors({ [field]: messages[0] });
                                });
                            } else {
                                showAlert("error", response.message || "An unexpected error occurred.");
                            }
                        },
                        complete: function() {
                            $submitButton.prop("disabled", false).html($submitButton.data("original-text"));
                            window.recoveryRecaptcha.reset();
                        }
                    });
                }catch(e) {
                    console.error("Form submit error:", e);
                    showAlert("error", "Something went wrong. Please try again.");
                }
            });
        });
    </script>

    {{-- Session Check Script --}}
    <script>
        (function() {
            const checkUrl = "{{ route('2fa.session.check') }}";
            const checkInterval = 30000;

            function checkSession() {
                $.ajax({
                    url: checkUrl,
                    type: 'GET',
                    success: function() {},
                    error: function() { window.location.href = '{{ url('login') }}'; }
                });
            }

            setInterval(checkSession, checkInterval);
        })();
    </script>
@stop
