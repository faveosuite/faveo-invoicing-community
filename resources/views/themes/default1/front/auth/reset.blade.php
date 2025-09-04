@extends('themes.default1.layouts.front.master')

@section('title')
    {{ __('message.reset_password_faveo_helpdesk') }}
@stop

@section('page-heading')
    {{ __('message.reset_your_password') }}
@stop

@section('page-header')
    {{ __('message.reset_password') }}
@stop

@section('breadcrumb')
    @if(Auth::check())
        <li><a class="text-primary" href="{{url('my-invoices')}}">{{ __('message.home') }}</a></li>
    @else
        <li><a class="text-primary" href="{{url('login')}}">{{ __('message.home') }}</a></li>
    @endif
    <li class="active text-dark">{{ __('message.reset_password') }}</li>
@stop

@section('main-class')
    main
@stop

@section('content')
    <div id="reset-error-container"></div>

    <div class="container py-4">
        <div class="row justify-content-center">
            <div class="col-md-6">

                {!! html()->form()->id('changePasswordForm')->open() !!}
                <input type="hidden" name="token" value="{{ $reset_token }}">
                <input type="hidden" name="email" value="{{ $email }}">

                {{-- Password --}}
                <div class="mb-3">
                    <label for="password" class="form-label fw-bold">
                        {{ __('message.new_password') }} <span class="text-danger">*</span>
                    </label>
                    <div class="input-group">
                        <input type="password" id="password" name="password"
                               class="form-control form-control-lg"
                               placeholder="{{ __('message.password') }}">
                        <span class="input-group-text" role="button" onclick="togglePasswordVisibility(this)">
                        <i class="fa fa-eye-slash"></i>
                    </span>
                    </div>
                    <div id="password_error" class="invalid-feedback d-none"></div>

                    <small class="text-muted mt-2 d-none" id="pswd_info">
                        <span class="fw-bold">{{ __('message.password_requirements') }}</span>
                        <ul class="ps-3 mb-0">
                            @foreach (__('message.password_requirements_list') as $requirement)
                                <li id="{{ $requirement['id'] }}" class="text-danger">{{ $requirement['text'] }}</li>
                            @endforeach
                        </ul>
                    </small>
                </div>

                {{-- Confirm Password --}}
                <div class="mb-3">
                    <label for="confirm_password" class="form-label fw-bold">
                        {{ __('message.confirm_password') }} <span class="text-danger">*</span>
                    </label>
                    <div class="input-group">
                        {!! html()->password('password_confirmation')
                            ->class('form-control form-control-lg')
                            ->attribute('placeholder', __('message.retype_password'))
                            ->id('confirm_password') !!}
                        <span class="input-group-text" role="button" onclick="togglePasswordVisibility(this)">
                        <i class="fa fa-eye-slash"></i>
                    </span>
                    </div>
                    <div id="confirm_password_error" class="invalid-feedback d-none"></div>
                </div>

                {!! honeypotField('reset') !!}

                {{-- Recaptcha --}}
                <div class="mb-3">
                    <div id="recaptchaReset"></div>
                </div>

                {{-- Submit --}}
                <div class="d-grid">
                    <button type="submit" class="btn btn-dark btn-modern text-uppercase fw-bold py-3"
                            id="reset-button"
                            data-original-text="{{ __('message.reset_password') }}"
                            data-loading-text="{{ __('message.loading') }}">
                        {{ __('message.reset_password') }}
                    </button>
                </div>

                {!! html()->form()->close() !!}
            </div>
        </div>
    </div>
<script>
        let resetRecaptcha;

        (async () => {
            const demoRecaptchaContainer = document.getElementById('recaptchaReset');

            resetRecaptcha = await RecaptchaManager.init(demoRecaptchaContainer, {
                action: 'reset',
            });

            // Make them globally available
            window.resetRecaptcha = resetRecaptcha;

        })();
    </script>
     <script>
         $(document).ready(function() {
             // Cache the selectors for better performance
             var $pswdInfo = $('#pswd_info');
             var $password = $('#password');
             var $length = $('#length');
             var $letter = $('#letter');
             var $capital = $('#capital');
             var $number = $('#number');
             var $special = $('#space');

             // Function to update validation classes
             function updateClass(condition, $element) {
                 $element.toggleClass('text-success', condition).toggleClass('text-danger', !condition);
             }

             // Initially hide the password requirements
             $pswdInfo.hide();

             // Show/hide password requirements on focus/blur
             $password.focus(function() {
                 $pswdInfo.removeClass('d-none').show();
             }).blur(function() {
                 $pswdInfo.addClass('d-none').hide();
             });

             // Perform real-time validation on keyup
             $password.on('keyup', function() {
                 var pswd = $(this).val();

                 // Validate the length (8 to 16 characters)
                 updateClass(pswd.length >= 8 && pswd.length <= 16, $length);

                 // Validate lowercase letter
                 updateClass(/[a-z]/.test(pswd), $letter);

                 // Validate uppercase letter
                 updateClass(/[A-Z]/.test(pswd), $capital);

                 // Validate number
                 updateClass(/\d/.test(pswd), $number);

                 // Validate special character
                 updateClass(/[~*!@$#%_+.?:,{ }]/.test(pswd), $special);
             });
         });

         $(document).ready(function() {
             function placeErrorMessage(error, element, errorMapping = null) {
                 const errorText = error.text().trim();

                 if (errorMapping !== null && errorMapping[element.attr("name")]) {
                     const target = $(errorMapping[element.attr("name")]);

                     if (errorText) {
                         target.removeClass("d-none").html(errorText).show();
                         element.addClass("is-invalid").removeClass("is-valid");
                     } else {
                         target.addClass("d-none").empty().hide(); // Added .hide() for complete hiding
                         element.removeClass("is-invalid is-valid");
                     }
                 } else {
                     // Fallback: place error right after the element
                     if (errorText) {
                         error.insertAfter(element).show();
                         element.addClass("is-invalid").removeClass("is-valid");
                     } else {
                         error.remove();
                         element.removeClass("is-invalid is-valid");
                     }
                 }
             }

             let alertTimeout;

             function showAlert(type, messageOrResponse) {
                 // Generate appropriate HTML
                 var html = generateAlertHtml(type, messageOrResponse);

                 // Clear any existing alerts and remove the timeout
                 $('#reset-error-container').html(html);
                 clearTimeout(alertTimeout);

                 // Display alert
                 window.scrollTo(0, 0);

                 // Auto-dismiss after 5 seconds
                 alertTimeout = setTimeout(function() {
                     $('#reset-error-container .alert').fadeOut('slow');
                 }, 5000);
             }

             function generateAlertHtml(type, response) {
                 const isSuccess = type === 'success';
                 const iconClass = isSuccess ? 'fa-check-circle' : 'fa-ban';
                 const alertClass = isSuccess ? 'alert-success' : 'alert-danger';
                 const message = response.message || response || 'An error occurred. Please try again.';

                 let html = `<div class="alert ${alertClass} alert-dismissible">` +
                     `<i class="fa ${iconClass}"></i> ` +
                     `${message}` +
                     '<button type="button" class="btn-close" data-dismiss="alert" aria-hidden="true"></button>' +
                     '</div>';

                 return html;
             }

             // Clear error messages function
             function clearErrorMessages() {
                 $('.invalid-feedback').addClass('d-none').empty().hide();
                 $('.form-control').removeClass('is-invalid is-valid');
             }

             $('#changePasswordForm').validate({
                 rules: {
                     password: {
                         required: true,
                         regex: /^(?=.*[A-Z])(?=.*[a-z])(?=.*\d)(?=.*[~*!@$#%_+.?:,{ }])[A-Za-z\d~*!@$#%_+.?:,{ }]{8,16}$/,
                     },
                     password_confirmation: {
                         required: true,
                         equalTo: "#password"
                     },
                 },
                 messages: {
                     password: {
                         required: "{{ __('message.login_validation.password_required') }}",
                         regex: "{{ __('message.strong_password') }}"
                     },
                     password_confirmation: {
                         required: "{{ __('message.login_validation.confirm_password_required') }}",
                         equalTo: "{{ __('message.login_validation.confirm_password_equalto') }}"
                     },
                 },
                 unhighlight: function(element) {
                     $(element).removeClass("is-invalid is-valid");
                     // Clear the corresponding error message
                     const errorMapping = {
                         "password": "#password_error",
                         "password_confirmation": "#confirm_password_error",
                     };
                     const errorContainer = $(errorMapping[element.name]);
                     if (errorContainer.length) {
                         errorContainer.addClass("d-none").empty().hide();
                     }
                 },
                 errorPlacement: function (error, element) {
                     var errorMapping = {
                         "password": "#password_error",
                         "password_confirmation": "#confirm_password_error",
                     };
                     placeErrorMessage(error, element, errorMapping);
                 },
                 // Add success callback to ensure proper cleanup
                 success: function(label, element) {
                     const errorMapping = {
                         "password": "#password_error",
                         "password_confirmation": "#confirm_password_error",
                     };
                     const errorContainer = $(errorMapping[element.name]);
                     if (errorContainer.length) {
                         errorContainer.addClass("d-none").empty().hide();
                     }
                     $(element).removeClass("is-invalid");
                 }
             });

             // Add input event listeners to clear errors in real-time
             $('#password, #confirm_password').on('input', function() {
                 const fieldName = this.name || this.id.replace('confirm_', '');
                 const errorMapping = {
                     "password": "#password_error",
                     "password_confirmation": "#confirm_password_error",
                 };

                 if ($(this).valid()) {
                     const errorContainer = $(errorMapping[fieldName] || errorMapping[this.name]);
                     if (errorContainer.length) {
                         errorContainer.addClass("d-none").empty().hide();
                     }
                     $(this).removeClass("is-invalid");
                 }
             });

             $('#changePasswordForm').on('submit', async function(event) {
                 event.preventDefault();

                 const $form = $(this);
                 const $submitButton = $("#reset-button");

                 if (!$form.valid()) {
                     return;
                 }

                 try {
                     // Validate reCAPTCHA
                     let recaptchaToken = await window.resetRecaptcha.tokenValidation(resetRecaptcha, "login");
                     if (!recaptchaToken) return;

                     // Collect form data
                     let formData = $form.serializeArray();
                     if (!window.resetRecaptcha.isDisabled() && recaptchaToken) {
                         formData.push({ name: "g-recaptcha-response", value: recaptchaToken });
                         formData.push({ name: "page_id", value: window.pageId });
                     }

                     // Submit form
                     $.ajax({
                         url: "{{ url('password/reset') }}",
                         method: "POST",
                         data: $.param(formData),
                         beforeSend: function () {
                             clearErrorMessages(); // Clear any existing errors before submit
                             $submitButton.prop("disabled", true).html($submitButton.data("loading-text"));
                         },
                         success: function (response) {
                             showAlert("success", response.message);

                             if (response.data?.redirect) {
                                 window.location.href = response.data?.redirect;
                             }
                         },
                         error: async function (xhr) {
                             let response = xhr.responseJSON || JSON.parse(xhr.responseText || "{}");

                             // Handle reCAPTCHA fallback
                             if (response.data?.show_v2_recaptcha) {
                                 await window.resetRecaptcha.useFallback(true);
                                 showAlert("error", response.message || "An unexpected error occurred.");
                                 return;
                             }

                             // Handle validation errors
                             if (response.errors) {
                                 let validator = $form.validate();
                                 clearErrorMessages();
                                 $.each(response.errors, function (field, messages) {
                                     validator.showErrors({ [field]: messages[0] });
                                 });
                             } else {
                                 showAlert("error", response.message || "An unexpected error occurred.");
                             }
                         },
                         complete: function () {
                             $submitButton.prop("disabled", false).html($submitButton.data("original-text"));
                             window.resetRecaptcha.reset();
                         }
                     });
                 } catch (err) {
                     console.error("Form submit error:", err);
                     showAlert("error", "Something went wrong. Please try again.");
                 }
             });
         });
    </script>
@stop