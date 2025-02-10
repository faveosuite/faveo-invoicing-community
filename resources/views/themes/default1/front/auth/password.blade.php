@extends('themes.default1.layouts.front.master')
@section('title')
Forgot Password | Faveo Helpdesk
@stop
@section('page-heading')
 Forgot Password
@stop
@section('page-header')
Forgot Password
@stop
@section('breadcrumb')
@section('breadcrumb')
    @if(Auth::check())
        <li><a class="text-primary" href="{{url('my-invoices')}}">{{ __('message.home')}}</a></li>
    @else
         <li><a class="text-primary" href="{{url('login')}}">{{ __('message.home')}}</a></li>
    @endif
     <li class="active text-dark">{{ __('message.forgot_password')}}</li>
@stop  
@stop
@section('main-class') 
main
@stop
@section('content')
    <div id="alert-container"></div>
        <div class="container py-4">

            <div class="row justify-content-center">

                <div class="col-md-6 col-lg-6 mb-5 mb-lg-0 pe-5">

                    <form id="resetPasswordForm">
                        <div class="row">

                            <div class="form-group col">

                                <label class="form-label text-color-dark text-3">{{ __('message.email_address')}}<span class="text-color-danger">*</span></label>

                                <input name="email" value="" id="email" type="email" class="form-control form-control-lg text-4">
                                <h6 id="resetpasswordcheck"></h6>
                            </div>
                        </div>

                        <div class="row justify-content-between">

                            <div class="form-group col-md-auto">

                                <a class="text-decoration-none text-color-primary font-weight-semibold text-2" href="{{url('login')}}">{{ __('message.know_password')}}</a>
                            </div>
                        </div>
                           @if ($status->recaptcha_status == 1)
                                <div id="recaptchaEmail"></div>
                                <span id="passcaptchacheck"></span><br>
                            @elseif($status->v3_recaptcha_status === 1)
                                 <input type="hidden" id="g-recaptcha-email" class="g-recaptcha-token" name="g-recaptcha-response">
                             @endif

                        <div class="row">

                            <div class="form-group col">

                                <button type="submit" class="btn btn-dark btn-modern w-100 text-uppercase font-weight-bold text-3 py-3" data-loading-text="{{ __('message.sending')}}" data-original-text="{{ __('message.send_mail')}}" name="sendOtp" id="resetmail">{{ __('message.send_mail')}}</button>

                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
@stop 
@section('script')
@extends('mini_views.recaptcha')
<script>
    let email_recaptcha_id;
    let recaptcha;
    let recaptchaToken;

    @if($status->recaptcha_status === 1)
    recaptchaFunctionToExecute.push(() => {
        email_recaptcha_id = grecaptcha.render('recaptchaEmail', {'sitekey': siteKey});
    });
    @endif
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
            const message = response.message || response || '{{ __('message.error_occurred') }}';

            // Build base HTML
            let html = `<div class="alert ${alertClass} alert-dismissible">` +
                `<i class="fa ${iconClass}"></i> ` +
                `${message}` +
                '<button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>';

            html += '</div>';

            return html;
        }
        $.validator.addMethod("recaptchaRequired", function(value, element) {
            try {
                if(!recaptchaEnabled) {
                    return false;
                }
            }catch (ex){
                return false
            }
            return value.trim() !== "";
        }, "{{ __('message.recaptcha_required') }}");
        $.validator.addMethod("regex", function(value, element, regexp) {
            var re = new RegExp(regexp);
            return this.optional(element) || re.test(value);
        }, "{{ __('message.invalid_format') }}");

        $('#resetPasswordForm').validate({
            ignore: ":hidden:not(.g-recaptcha-response)",
            rules: {
                email: {
                    required: true,
                    regex: /^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/,
                },
                "g-recaptcha-response": {
                    recaptchaRequired: true
                }
            },
            messages: {
                email: {
                    required: "{{ __('message.error_email_address') }}",
                    regex: "{{ __('message.contact_error_email') }}"
                },
                "g-recaptcha-response": {
                    recaptchaRequired: "{{ __('message.recaptcha_required') }}"
                }
            },
            unhighlight: function (element) {
                $(element).removeClass("is-valid");
            },
            errorPlacement: function (error, element) {
                var errorMapping = {
                    "email": "#resetpasswordcheck",
                    "g-recaptcha-response": "#passcaptchacheck"
                };

                placeErrorMessage(error, element, errorMapping);
            },
            submitHandler: function (form) {
                var formData = $(form).serialize();
                var submitButton = $('#resetmail');
                    $.ajax({
                        url: '{{url('password/email')}}',
                        type: 'POST',
                        data: formData,
                        dataType: 'json', // Expect JSON response
                        beforeSend: function () {
                            submitButton.prop('disabled', true).html(submitButton.data('loading-text'));
                        },
                        success: function (response) {
                            form.reset();
                            showAlert('success', response.message);
                        },
                        error: function (data) {
                            var response = data.responseJSON ? data.responseJSON : JSON.parse(data.responseText);
                            showAlert('error', response);
                        },
                        complete: function () {
                            submitButton.prop('disabled', false).html(submitButton.data('original-text'));
                            setTimeout(function() {
                                window.location.href = "{{ url('login') }}";
                            }, 3500);
                        }
                    });
                }
        });
    });
</script>
@stop
                              