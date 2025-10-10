@extends('themes.default1.layouts.front.master')

@section('title')
    {{ __('message.two_factor') }}
@stop

@section('page-heading')
    {{ __('message.two_factor') }}
@stop

@section('page-header')
    {{ __('message.forgot-password') }}
@stop

@section('breadcrumb')
    @if(Auth::check())
        <li><a class="text-primary" href="{{url('my-invoices')}}">{{ __('message.home') }}</a></li>
    @else
        <li><a class="text-primary" href="{{url('login')}}">{{ __('message.home') }}</a></li>
    @endif
    <li class="active text-dark">{{ __('message.two_factor') }}</li>
@stop

@section('main-class')
    main
@stop

@section('content')
    <div id="2fa-alert-container" class="mb-3"></div>

    <div class="container py-4">
        <div class="row justify-content-center">
            <div class="col-md-6 col-lg-6">

                {!! html()->form()->id('2fa_form')->open() !!}

                {{-- Auth Code --}}
                <div class="mb-4">
                    <label for="2fa_code" class="form-label text-color-dark fw-bold">
                        {{ __('message.enter_auth_code') }} <span class="text-danger">*</span>
                    </label>
                    <input type="text" name="totp" maxlength="6" id="2fa_code"
                           class="form-control form-control-lg text-4"
                           placeholder="{{ __('message.otp_placeholder') }}">
                    <div id="codecheck" class="form-text text-danger"></div>
                </div>

                <p class="text-muted mb-4">{{ __('message.open_two_factor') }}</p>

                {{-- Recaptcha --}}
                <div class="mb-4" id="2fa_recaptcha"></div>

                {!! honeypotField('2fa_code') !!}

                {{-- Recovery Link --}}
                @if(!Session::has('reset_token'))
                    <div class="mb-4">
                        <div class="text-muted">
                            {{ __('message.having_problem') }}
                            <a href="{{ url('recovery-code') }}" class="text-decoration-underline">
                                {{ __('message.login_recovery_code') }}
                            </a>
                        </div>
                    </div>
                @endif

                {{-- Submit Button --}}
                <div class="d-grid mb-4">
                    <button type="submit"
                            id="2fa-submit-button"
                            class="btn btn-dark btn-lg fw-bold text-uppercase text-3 py-3"
                            data-loading-text="{{ __('message.loading') }}"
                            data-original-text="{{ __('message.verify') }}">
                        {{ __('message.verify') }}
                    </button>
                </div>

                {!! html()->form()->close() !!}

            </div>
        </div>
    </div>
        <script>
            let twoFactorRecaptcha;

            (async () => {
                const twoFactorRecaptchaContainer = document.getElementById('2fa_recaptcha');

                twoFactorRecaptcha = await RecaptchaManager.init(twoFactorRecaptchaContainer, {
                    action: 'login_2fa',
                });

                // Make them globally available
                window.twoFactorRecaptcha = twoFactorRecaptcha;

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
                $('#2fa-alert-container').html(html);
                clearTimeout(alertTimeout); // Clear the previous timeout if it exists

                // Display alert
                window.scrollTo(0, 0);

                // Auto-dismiss after 5 seconds
                alertTimeout = setTimeout(function() {
                    $('#2fa-alert-container .alert').fadeOut('slow');
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

            $.validator.addMethod("totp6digits", function(value, element) {
                return this.optional(element) || /^[0-9]{6}$/.test(value);
            }, "{{ __('message.enter_valid_6_digit_code') }}");

            $('#2fa_form').validate({
                rules: {
                    totp: {
                        required: true,
                        totp6digits: true
                    }
                },
                messages: {
                    totp: {
                        required: "{{ __('message.please_enter_auth_code') }}"
                    }
                },
                unhighlight: function (element) {
                    $(element).removeClass("is-valid");
                },
                errorPlacement: function (error, element) {
                    placeErrorMessage(error, element);
                }
            });

            $('#2fa_form').on('submit', async function (event) {
                event.preventDefault();

                const $form = $(this);
                const $submitButton = $("#2fa-submit-button");

                if (!$form.valid()) {
                    return;
                }

                try {
                    let recaptchaToken = await window.twoFactorRecaptcha.tokenValidation(twoFactorRecaptcha, "login");
                    if (!recaptchaToken) return;

                    // Collect form data
                    let formData = $form.serializeArray();
                    if (!window.twoFactorRecaptcha.isDisabled() && recaptchaToken) {
                        formData.push({ name: "g-recaptcha-response", value: recaptchaToken });
                        formData.push({ name: "page_id", value: window.pageId });
                    }

                    $.ajax({
                        url: "{{ route('2fa/loginValidate') }}",
                        method: "POST",
                        data: $.param(formData),
                        beforeSend: function () {
                            $submitButton.prop("disabled", true).html($submitButton.data("loading-text"));
                        },
                        success: function (response) {
                            if (response.data?.redirect) {
                                window.location.href = response.data?.redirect;
                            } else {
                                showAlert("success", response.message);
                            }
                        },
                        error: async function (xhr) {
                            let response = xhr.responseJSON || JSON.parse(xhr.responseText || "{}");

                            // Handle reCAPTCHA fallback
                            if (response.data?.show_v2_recaptcha) {
                                await window.twoFactorRecaptcha.useFallback(true);
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
                        complete: function () {
                            $submitButton.prop("disabled", false).html($submitButton.data("original-text"));
                            window.twoFactorRecaptcha.reset();
                        }
                    });
                }catch (e) {
                    console.error("Form submit error:", e);
                    showAlert("error", "Something went wrong. Please try again.");
                }

            });
        });
    </script>
        <script>
            (function() {
                const checkUrl = "{{ route('2fa.session.check') }}";
                const checkInterval = 30000;

                function checkSession() {
                    $.ajax({
                        url: checkUrl,
                        type: 'GET',
                        success: function(response, status, xhr) {
                        },
                        error: function() {
                            window.location.href = '{{ url('login') }}';
                        }
                    });
                }

                setInterval(checkSession, checkInterval);
            })();
        </script>
@stop 
