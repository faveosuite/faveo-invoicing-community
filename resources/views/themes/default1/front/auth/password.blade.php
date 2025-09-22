@extends('themes.default1.layouts.front.master')
@section('title')
    {{ __('message.forgot_password_faveo_helpdesk') }}
@stop
@section('page-heading')
  {{ __('message.forgot-password') }}
@stop
@section('page-header')
    {{ __('message.forgot-password') }}
@stop
@section('breadcrumb')
    @if(Auth::check())
        <li><a class="text-primary" href="{{url('my-invoices')}}">{{ __('message.home')}}</a></li>
    @else
         <li><a class="text-primary" href="{{url('login')}}">{{ __('message.home')}}</a></li>
    @endif
     <li class="active text-dark">{{ __('message.forgot_password')}}</li>
@stop
@section('main-class')
main
@stop
@section('content')
    <div class="container py-4">

        {{-- Alerts (inside container for alignment) --}}
        <div id="alert-container" class="mb-3"></div>

        <div class="row justify-content-center">
            <div class="col-md-6 col-lg-6 mb-5 mb-lg-0">

                <form id="resetPasswordForm">
                    <div class="row">
                        <div class="form-group col">
                            <label class="form-label text-color-dark text-3">
                                {{ __('message.email_address') }}
                                <span class="text-color-danger">*</span>
                            </label>
                            <input name="email" id="email" type="email" class="form-control form-control-lg text-4">
                            <h6 id="resetpasswordcheck" class="text-danger mt-1"></h6>
                        </div>
                    </div>

                    <div class="row justify-content-between">
                        <div class="form-group col-md-auto">
                            <a class="text-decoration-none text-color-primary font-weight-semibold text-2" href="{{url('login')}}">
                                {{ __('message.know_password') }}
                            </a>
                        </div>
                    </div>

                    <div class="row">
                        <div class="form-group col">
                            <div id="recaptchaEmail"></div>
                        </div>
                    </div>

                    {!! honeypotField('forgot') !!}

                    <div class="row">
                        <div class="form-group col">
                            <button type="submit"
                                    class="btn btn-dark btn-modern w-100 text-uppercase font-weight-bold text-3 py-3"
                                    data-loading-text="{{ __('message.sending')}}"
                                    data-original-text="{{ __('message.send_mail')}}"
                                    id="resetmail">
                                {{ __('message.send_mail') }}
                            </button>
                        </div>
                    </div>
                </form>

            </div>
        </div>
    </div>
@stop

@section('script')
{{--@extends('mini_views.recaptcha')--}}
<script>
    let forgotRecaptcha;

    (async () => {
        const forgotRecaptchaContainer = document.getElementById('recaptchaEmail');

        forgotRecaptcha = await RecaptchaManager.init(forgotRecaptchaContainer, {
            action: 'forgot',
        });

        // Make them globally available
        window.forgotRecaptcha = forgotRecaptcha;

    })();
</script>

<script>
    $(document).ready(function(){
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
            $('#alert-container').html(html);
            clearTimeout(alertTimeout); // Clear the previous timeout if it exists

            // Display alert
            window.scrollTo(0, 0);

            // Auto-dismiss after 5 seconds
            alertTimeout = setTimeout(function() {
                $('#alert-container .alert').slideUp(3000, function() {
                    // Then fade out after slideUp finishes
                    $(this).fadeOut('slow');
                });
            }, 5000);
        }


        function generateAlertHtml(type, response) {
            // Determine alert styling based on type
            const isSuccess = type === 'success';
            const iconClass = isSuccess ? 'fa-check-circle' : 'fa-ban';
            const alertClass = isSuccess ? 'alert-success' : 'alert-danger';

            // Extract message and errors
            const message = response.message || response || 'An error occurred. Please try again.';

            // Build base HTML
            let html = `<div class="alert ${alertClass} alert-dismissible">` +
                `<i class="fa ${iconClass}"></i> ` +
                `${message}` +
                '<button type="button" class="btn-close" data-dismiss="alert" aria-hidden="true"></button>';

            html += '</div>';

            return html;
        }
        $.validator.addMethod("regex", function(value, element, regexp) {
            var re = new RegExp(regexp);
            return this.optional(element) || re.test(value);
        }, "{{ __('message.invalid_format') }}");

        $('#resetPasswordForm').validate({
            rules: {
                email: {
                    required: true,
                    email: true,
                    regex: /^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/,
                },
            },
            messages: {
                email: {
                    required: "{{ __('message.error_email_address') }}",
                    email: "{{ __('message.contact_error_email') }}",
                    regex: "{{ __('message.contact_error_email') }}"
                }
            },
            unhighlight: function (element) {
                $(element).removeClass("is-valid");
            },
            errorPlacement: function (error, element) {
                var errorMapping = {
                    "email": "#resetpasswordcheck",
                };

                placeErrorMessage(error, element, errorMapping);
            }
        });

        $('#resetPasswordForm').on('submit', async function(event) {
            event.preventDefault();

            const $form = $(this);

            const $submitButton = $('#resetmail');

            if (!$form.valid()) {
                return;
            }

            try {
                // Validate reCAPTCHA
                let recaptchaToken = await window.forgotRecaptcha.tokenValidation(forgotRecaptcha, "demo");
                if (!recaptchaToken) return;

                // Collect form data
                let formData = $form.serializeArray();

                if (!window.demoRecaptcha.isDisabled() && recaptchaToken) {
                    formData.push({ name: "g-recaptcha-response", value: recaptchaToken });
                    formData.push({ name: "page_id", value: window.pageId });
                }

                // Submit form
                $.ajax({
                    url: "{{ url('password/email') }}",
                    method: "POST",
                    data: $.param(formData),
                    beforeSend: function () {
                        $submitButton.prop("disabled", true).html($submitButton.data("loading-text"));
                    },
                    success: function (response) {
                        $form[0].reset();
                        showAlert('success', response.message);
                        setTimeout(function() {
                            window.location.href = "{{ url('login') }}";
                        }, 6000);
                    },
                    error: async function (xhr) {
                        let response = xhr.responseJSON || JSON.parse(xhr.responseText || "{}");

                        // Handle reCAPTCHA fallback
                        if (response.data?.show_v2_recaptcha) {
                            await window.forgotRecaptcha.useFallback(true);
                            showAlert("error", response.message || "An unexpected error occurred.");
                            return;
                        }

                        if (response.errors) {
                            $.each(response.errors, function (field, messages) {
                                validator.showErrors({ [field]: messages[0] });
                            });
                        } else {
                            showAlert("error", response.message || "An unexpected error occurred.");
                        }
                    },
                    complete: function () {
                        $submitButton.prop("disabled", false).html($submitButton.data("original-text"));
                        window.forgotRecaptcha.reset();
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
