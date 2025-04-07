@extends('themes.default1.layouts.front.master')
<?php
$isMobileVerified = ($setting->msg91_status == 1 && $user->mobile_verified != 1) ? false : true;
$isEmailVerified = ($setting->emailverification_status == 1 && $user->email_verified != 1) ?false : true;
?>
@section('title')
    @if(!$isMobileVerified && !$isEmailVerified)
        Email/Mobile Verification | Faveo Helpdesk
    @elseif(!$isEmailVerified)
        Email Verification | Faveo Helpdesk
    @elseif(!$isMobileVerified)
        Mobile Verification | Faveo Helpdesk
    @endif
@stop
@section('page-heading')
    @if(!$isMobileVerified && !$isEmailVerified)
        Email/Mobile Verification
    @elseif(!$isEmailVerified)
        Email Verification
    @elseif(!$isMobileVerified)
        Mobile Verification
    @endif
@stop
@section('breadcrumb')
    @if(Auth::check())
        <li><a class="text-primary" href="{{url('my-invoices')}}">{{ __('message.home') }}</a></li>
    @else
        <li><a class="text-primary" href="{{url('login')}}">{{ __('message.home') }}</a></li>
    @endif
    <li class="active text-dark">{{ __('message.verify') }}</li>
@stop
@section('main-class')
    main
@stop
@section('content')
    <style>
        #msform {
            text-align: center;
            position: relative;
            margin-top: 20px;
        }

        #msform fieldset {
            background: white;
            border: 0 none;
            border-radius: 0.5rem;
            box-sizing: border-box;
            width: 100%;
            margin: 0;
            padding-bottom: 20px;
            position: relative;
        }

        .form-card {
            text-align: left;
        }

        /*Hide all except first fieldset*/
        #msform fieldset:not(:first-of-type) {
            display: none;
        }

        #msform input, #msform textarea {
            padding: 8px 15px 8px 15px;
            border: 1px solid #ccc;
            border-radius: 0px;
            margin-top: 2px;
            width: 100%;
            box-sizing: border-box;
            color: #2C3E50;
            font-size: 15px;
            letter-spacing: 1px;
        }

        #msform input:focus, #msform textarea:focus {
            -moz-box-shadow: none !important;
            -webkit-box-shadow: none !important;
            box-shadow: none !important;
            border: 1px solid #099fdc;
            outline-width: 0;
        }

        /*Next Buttons*/
        #msform .action-button {
            width: 100px;
            background: #099fdc;
            font-weight: 600;
            color: white;
            border: 0 none;
            border-radius: 0px;
            cursor: pointer;
            margin: 10px 0px 10px 5px;
            float: right;
            text-transform: uppercase;
            border-radius: 5px;
            font-size: 14px;
        }

        #msform .action-button:hover, #msform .action-button:focus {
            background-color: #000000;
            color: #ffffff;
        }

        .card {
            z-index: 0;
            border: none;
            position: relative;
        }

        .purple-text {
            color: #099fdc;
            font-weight: normal;
        }

        /* Centering the progress bar container */
        #progressbar {
            margin-bottom: 30px;
            overflow: hidden;
            color: lightgrey;
            display: flex; /* Use flexbox to center items */
            justify-content: center; /* Center the progress bar horizontally */
            padding: 0;
            margin-left: 0; /* Reset margin-left */
        }

        /* Styling the progress steps */
        #progressbar li {
            list-style-type: none;
            font-size: 15px;
            width: 33.33%; /* Distribute steps evenly */
            text-align: center; /* Center the text */
            font-weight: 400;
            position: relative;
        }

        /* Active step color */
        #progressbar .active {
            color: #099fdc;
        }

        /* Icons in the ProgressBar */
        #progressbar #otp_li:before {
            font-family: FontAwesome;
            content: "\f095";
        }

        #progressbar #email_li:before {
            font-family: FontAwesome;
            content: "\f003";
        }

        #progressbar #success_li:before {
            font-family: FontAwesome;
            content: "\f087";
        }

        /* Icon styling before any progress */
        #progressbar li:before {
            width: 50px;
            height: 50px;
            line-height: 45px;
            display: block;
            font-size: 20px;
            color: #ffffff;
            background: lightgray;
            border-radius: 50%;
            margin: 0 auto 10px;
            padding: 2px;
        }

        /* ProgressBar connectors */
        #progressbar li:after {
            content: '';
            width: 100%;
            height: 2px;
            background: lightgray;
            position: absolute;
            left: 0;
            top: 25px;
            z-index: -1;
        }

        /* Active step and its connector */
        #progressbar li.active:before,
        #progressbar li.active:after {
            background: #099fdc;
        }

        #otpButton {
            color: #099fdc;
            font-size: 14px;
        }

        #otpButton:disabled {
            color: rgb(106, 106, 106);
        }

        #timer {
            color: rgb(169, 169, 169);
            font-size: 14px;
            font-weight: 600;
        }

        #additionalButton {
            color: #099fdc;
            font-weight: 600;
        }

        #additionalButton:disabled {
            color: rgb(169, 169, 169);
        }

        #otpButtonn {
            color: #099fdc;
            font-size: 14px;
        }

        #otpButtonn:disabled {
            color: rgb(106, 106, 106);
        }

        .emailalert {
            border: 1px solid #262626;
            background: #81818124;
            border-radius: 5px;
            padding: 10px;
            color: #262626;
            font-size: 13px;
        }

    </style>
    <div class="container-fluid">
        <div class="row justify-content-center">
            <div class="col-12 col-sm-11 col-md-10 col-lg-9 col-xl-8 text-center p-0 mt-3 mb-2">
                <div class="card px-0 pt-4 pb-0 mt-3 mb-3">

                    <form id="msform">
                        <!-- progressbar -->
                        <ul id="progressbar">
                            @if(!$isMobileVerified)
                                <li class="active" id="otp_li"><strong>{{ __('message.verify_mobile') }}</strong></li>
                            @endif
                            @if(!$isEmailVerified)
                                <li id="email_li"><strong>{{ __('message.verify_email') }}</strong></li>
                            @endif
                            <li id="success_li"><strong>{{ __('message.all_set') }}</strong></li>
                        </ul>
                        <br>
                        <!-- fieldsets -->
                        <fieldset id="fieldset_otp">
                            <div class="form-card">

                                <div id="alert-container"></div>

                                <p class="text-left text-color-dark text-3">{{ __('message.enter_code') }} <span class="text-color-danger"> *</span></p>

                                <input class="form-control h-100" type="text" id="otp" name="otp" placeholder="{{ __('message.otp_placeholder') }}"/>
                                <p class="mt-3">{{ __('message.otp_description') }}</p>

                                @if ($setting->recaptcha_status === 1)
                                    <div id="recaptchaMobile"></div>
                                @elseif($setting->v3_recaptcha_status === 1)
                                    <input type="hidden" id="g-recaptcha-mobile" class="g-recaptcha-token" name="g-recaptcha-response">
                                @endif
                                <div class="col-12">
                                    <div class="row">
                                        <div class="col-6 px-0">
                                            <div class="mt-2 d-flex align-items-center">
                                                <button id="otpButton" type="button"
                                                        onclick="resendOTP('mobile','text')" class="btn border-0 p-0"
                                                        style="width: 110px;">
                                                    <i class="fa fa-refresh"></i> {{ __('message.resend_otp') }}
                                                </button>
                                                <div id="timer"></div>
                                            </div>
                                            <div class="mt-1">
                                                <button id="additionalButton" type="button"
                                                        onclick="resendOTP('mobile','voice')"
                                                        class="border-0 px-1 background-transparent"
                                                        disabled><i class="fa fa-phone"></i> {{ __('message.otp_call') }}
                                                </button>
                                            </div>
                                        </div>
                                        <div class="col-6 px-0">
                                            <input type="button" onclick="submitOtp()" name="next"
                                                   class="next action-button float-right" value="{{ __('message.verify') }}"/>

                                        </div>
                                    </div>
                                </div>
                            </div>
                        </fieldset>
                        <fieldset id="fieldset_email">
                            <div class="form-card">

                                <div id="alert-container-email"></div>

                                <p class="text-left text-color-dark text-3">{{ __('message.enter_code') }} <span class="text-color-danger"> *</span></p>

                                <input class="form-control h-100" type="text" id="email_otp" name="email_otp" placeholder="{{ __('message.otp_placeholder') }}"/>
                                <p class="mt-3">{{ __('message.email_otp_description') }}</p>
                                @if($setting->v3_v2_recaptcha_status)
                                @if ($setting->recaptcha_status === 1)
                                    <div id="recaptchaEmail"></div>
                                @elseif($setting->v3_recaptcha_status === 1)
                                    <input type="hidden" id="g-recaptcha-email" class="g-recaptcha-token" name="g-recaptcha-response">
                                @endif
                                @endif
                                <div class="col-12 mt-4">
                                    <div class="row">
                                        <div class="col-6 px-0">
                                            <div class="mt-3 d-flex align-items-center">
                                                <button id="otpButtonn" type="button" onclick="resendOTP('email',null)"
                                                        class="btn border-0 p-0 d-inline-flex align-items-center"
                                                        style="width: 110px; white-space: nowrap;">
                                                    <i class="fa fa-refresh mr-1"></i>{{ __('message.resend_email') }}
                                                </button>
                                                <div id="timerEmail" class="ml-2"></div>
                                            </div>
                                        </div>
                                        <div class="col-6 px-0">
                                            <input onclick="isEmailVerified()" type="button" name="next"
                                                   class="next action-button float-right" value="{{ __('message.verify') }}"/>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </fieldset>
                        <fieldset id="fieldset_success">
                            <div class="form-card">

                                <h2 class="purple-text text-center"><strong>{{ __('message.all_success') }}</strong></h2>
                            </div>
                        </fieldset>
                    </form>

                    <div class="mt-2 text-start text-2">
                        {{ __('message.trouble_logging_in') }} <a href="{{ url('/contact-us') }}" class="text-decoration-none" target="_blank">{{ __('message.click_here') }}</a>
                    </div>
                </div>
            </div>
        </div>
    </div>

@stop
@section('script')
    @extends('mini_views.recaptcha')
    <script>

        const otpButton = document.getElementById("otpButton");
        const additionalButton = document.getElementById("additionalButton");
        const timerDisplay = document.getElementById("timer");
        const emailOtpButton = document.getElementById("otpButtonn");
        const emailTimerDisplay = document.getElementById("timerEmail");
        let timerInterval;
        const eid = @json($eid);

        let countdown = 120;

        let mobile_recaptcha_id;
        let email_recaptcha_id;
        let recaptcha;
        let recaptchaToken;

        @if($setting->recaptcha_status === 1)
        recaptchaFunctionToExecute.push(() => {
            mobile_recaptcha_id = grecaptcha.render('recaptchaMobile', { 'sitekey': siteKey });
            email_recaptcha_id = grecaptcha.render('recaptchaEmail', { 'sitekey': siteKey });
        });
            @endif

        ['email_otp', 'otp'].forEach(id => {
            document.getElementById(id).addEventListener('input', function () {
                this.value = this.value.replace(/[^0-9]/g, '').slice(0, 6);
            });
        });

        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': csrfToken
            }
        });

        function updateTimer(display, countdown) {
            display.textContent = countdown.toString().padStart(2, '0') + " seconds";
        }

        function startTimer(button, display, duration) {
            clearInterval(timerInterval);
            var countdown = countdown;
            button.disabled = true;
            additionalButton.disabled = true;
            display.style.display = "block";
            countdown = duration;
            updateTimer(display, countdown);
            timerInterval = setInterval(() => {
                countdown--;
                if (countdown <= 0) {
                    clearInterval(timerInterval);
                    button.disabled = false;
                    additionalButton.disabled = false;
                    display.style.display = "none";
                }
                updateTimer(display, countdown);
            }, 1000);
        }

        function resendOTP(default_type, type) {
            const data = {eid, default_type, type};
            $.ajax({
                url: '{{ url('resend_otp') }}',
                type: 'POST',
                data: data,
                success: function (response) {
                    if (default_type === 'mobile') {
                        startTimer(otpButton, timerDisplay, countdown);
                        showAlert('success', response.message, '#alert-container');
                    } else if (default_type === 'email') {
                        startTimer(emailOtpButton, emailTimerDisplay, countdown);
                        showAlert('success', response.message, '#alert-container-email');
                    }
                },
                error: function (error) {
                    if (default_type === 'mobile') {
                        showAlert('danger', error.responseJSON.message, '#alert-container');
                    } else if (default_type === 'email') {
                        showAlert('danger', error.responseJSON.message, '#alert-container-email');
                    }
                }
            });
        }

        function sendOTP() {
            const data = {eid};
            $.ajax({
                url: '{{ url('otp/send') }}',
                type: 'POST',
                data: data,
                success: function (response) {
                    startTimer(otpButton, timerDisplay, countdown);
                    showAlert('success', response.message, '#alert-container');
                },
                error: function (error) {
                    showAlert('danger', error.responseJSON.message, '#alert-container');
                }
            });
        }

        function submitOtp() {
            const otpField = $('#otp');
            const otpValue = otpField.val();
            const otpRegex = /^[0-9]{6}$/;

            otpField.removeClass('is-invalid').css('border-color', '');
            $('.error').remove();

            if (!otpValue) {
                showError(otpField, "{{ __('message.otp_required') }}");
                return;
            }

            if (!otpRegex.test(otpValue)) {
                showError(otpField, "{{ __('message.otp_invalid_format') }}");
                return;
            }

            @if($setting->recaptcha_status === 1)
                recaptcha = $('#recaptchaMobile');
            recaptchaToken = getRecaptchaTokenFromId(mobile_recaptcha_id);
            if(getRecaptchaTokenFromId(mobile_recaptcha_id) === ''){
                showError(recaptcha, "{{ __('message.recaptcha_required') }}");
                return;
            }
            @elseif($setting->v3_recaptcha_status === 1)
            updateRecaptchaTokens();
            recaptchaToken = $('#g-recaptcha-mobile').val();
            @endif

            const data = {eid, otp: otpValue, 'g-recaptcha-response': recaptchaToken ?? ''};

            $.ajax({
                url: '{{ url('otp/verify') }}',
                type: 'POST',
                data: data,
                success: function (response) {
                    @if(!$isEmailVerified)
                    activateFieldset("fieldSetTwo");
                    startTimer(emailOtpButton, emailTimerDisplay, countdown);
                    @else
                        window.location.href = "{{ url('/login') }}";
                    @endif
                },
                error: function (error) {
                    showAlert('danger', error.responseJSON.message, '#alert-container');
                }
            });
        }

        function activateFieldset(fieldSet) {
            const fieldsets = {
                fieldSetOne: document.getElementById('fieldset_otp'),
                fieldSetTwo: document.getElementById('fieldset_email'),
                fieldSetThree: document.getElementById('fieldset_success')
            };

            const progressList = {
                fieldSetOne: document.getElementById('otp_li'),
                fieldSetTwo: document.getElementById('email_li'),
                fieldSetThree: document.getElementById('success_li')
            };

            Object.values(fieldsets).forEach(fieldset => fieldset.style.display = 'none');
            // Object.values(progressList).forEach(progress => progress.classList.remove('active'));

            if (fieldsets[fieldSet] && progressList[fieldSet]) {
                if(fieldSet === 'fieldSetOne'){
                    startTimer(otpButton, timerDisplay, countdown);
                    sendOTP();
                }
                else if(fieldSet === 'fieldSetTwo'){
                    startTimer(emailOtpButton, emailTimerDisplay, countdown);
                    sendEmail();
                }
                fieldsets[fieldSet].style.display = 'block';
                progressList[fieldSet].classList.add('active');
            }
        }

        function sendEmail() {
            const data = {eid: eid};
            $.ajax({
                url: '{{ url('/send-email') }}',
                type: 'POST',
                data: data,
                success: function (response) {
                    showAlert('success', response.message, '#alert-container-email');
                },
                error: function (error) {
                    showAlert('danger', error.responseJSON.message, '#alert-container-email');
                }
            });
        }

        function isEmailVerified() {
            const otpField = $('#email_otp');
            const otpValue = otpField.val();
            const otpRegex = /^[0-9]{6}$/;

            otpField.removeClass('is-invalid').css('border-color', '');
            $('.error').remove();

            if (!otpValue) {
                showError(otpField, "{{ __('message.otp_required') }}");
                return;
            }

            if (!otpRegex.test(otpValue)) {
                showError(otpField, "{{ __('message.otp_invalid_format') }}");
                return;
            }

            @if($setting->recaptcha_status === 1)
                recaptcha = $('#recaptchaEmail');
            recaptchaToken = getRecaptchaTokenFromId(email_recaptcha_id);
            if(getRecaptchaTokenFromId(email_recaptcha_id) === ''){
                showError(recaptcha, "{{ __('message.recaptcha_required') }}");
                return;
            }
            @elseif($setting->v3_recaptcha_status === 1)
            updateRecaptchaTokens();
            recaptchaToken = $('#g-recaptcha-email').val();
            @endif

            const data = {eid, otp: otpValue, 'g-recaptcha-response':recaptchaToken ?? ''};
            $.ajax({
                url: '{{ url('email/verify') }}',
                type: 'POST',
                data: data,
                success: function (response) {
                    window.location.href = "{{ url('/login') }}";
                },
                error: function (error) {
                    showAlert('danger', error.responseJSON.message, '#alert-container-email');
                }
            });
        }

        function showAlert(type, message, container) {
            console.log(type, message);
            const icon = type === 'success' ? 'fa-check-circle' : 'fa-ban';
            const alertType = type === 'success' ? 'alert-success' : 'alert-danger';

            $(container).html(`
            <div class="alert ${alertType} alert-dismissable">
                <i class="fa ${icon}"></i> ${message}
                <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
            </div>
        `);

            setTimeout(() => {
                $(container).find('.alert').fadeOut('slow', function () {
                    $(this).remove();
                });
            }, 5000);
        }

        function showError(field, message) {
            field.addClass('is-invalid').css('border-color', 'red');
            field.after(`<span class="error invalid-feedback">${message}</span>`);
        }
        @if(!$isMobileVerified)
        activateFieldset('fieldSetOne');
        @elseif(!$isEmailVerified)
        activateFieldset('fieldSetTwo');
        @else
        activateFieldset('fieldSetOne');
        @endif
    </script>
@stop