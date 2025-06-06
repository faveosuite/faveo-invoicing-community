@extends('themes.default1.layouts.front.master')
@section('title')
    @if(!$isMobileVerified && !$isEmailVerified)
        {{ __('message.email_mobile_faveo') }}
    @elseif(!$isEmailVerified)
        {{ __('message.email_mobile_faveo') }}
    @elseif(!$isMobileVerified)
        {{ __('message.mobile_faveo') }}
    @endif
@stop
@section('page-heading')
    @if(!$isMobileVerified && !$isEmailVerified)
        {{ __('message.email_mobile') }}
    @elseif(!$isEmailVerified)
        {{ __('message.email_verification_api') }}
    @elseif(!$isMobileVerified)
        {{ __('message.mobile_verification') }}
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
            display: flex;
            justify-content: center;
            padding: 0;
            margin-left: 0;
        }

        /* Styling the progress steps */
        #progressbar li {
            list-style-type: none;
            font-size: 15px;
            width: 33.33%;
            text-align: center;
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

       [dir="rtl"] #otpButton{
           width: 125px !important;
       }
         [dir="rtl"] #otpButtonn{
              width: 135px !important;
         }
       #otpAlertMsg{
           text-align:left;
       }
         [dir="rtl"] #otpAlertMsg{
              text-align:right;
         }

        .hidden {
            display: none !important;
        }
    </style>
    <div class="container-fluid">
        <div class="row justify-content-center">
            <div class="col-12 col-sm-11 col-md-10 col-lg-9 col-xl-8 text-center p-0 mt-3 mb-2">
                <div class="card px-0 pt-4 pb-0 mt-3 mb-3">

                    <form id="msform">
                        <!-- progressbar -->
                        <ul id="progressbar">
                            @if(!$isMobileVerified && !$isEmailVerified)
                                @if($verification_preference === 'email')
                                    <li class="active" id="email_li"><strong>{{ __('message.verify_email') }}</strong></li>
                                    <li id="otp_li"><strong>{{ __('message.verify_mobile') }}</strong></li>
                                @else
                                    <li class="active" id="otp_li"><strong>{{ __('message.verify_mobile') }}</strong></li>
                                    <li id="email_li"><strong>{{ __('message.verify_email') }}</strong></li>
                                @endif
                            @elseif(!$isMobileVerified && $isEmailVerified)
                                <li class="active" id="otp_li"><strong>{{ __('message.verify_mobile') }}</strong></li>
                            @elseif(!$isEmailVerified && $isMobileVerified)
                                <li class="active" id="email_li"><strong>{{ __('message.verify_email') }}</strong></li>
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
                                    <input type="hidden" id="g-recaptcha-mobile" class="g-recaptcha-token" name="g-recaptcha-response"  data-recaptcha-action="verifyMobileOtp">
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
                                            <div class="mt-1 float-left">
                                                <button id="additionalButton" type="button"
                                                        onclick="resendOTP('mobile','voice')"
                                                        class="border-0 px-1 background-transparent"
                                                        disabled><i class="fa fa-phone"></i> {{ __('message.otp_call') }}
                                                </button>
                                            </div>
                                        </div>
                                        <div class="col-6 px-0">
                                            <button type="button" id="mobileVerifyBtn" onclick="submitOtp()" class="btn btn-primary btn-flat float-right btn-lg">
                                                <span id="mobileVerifyBtnText">{{ __('message.verify') }}</span>
                                            </button>
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
                                        <input type="hidden" id="g-recaptcha-email" class="g-recaptcha-token" name="g-recaptcha-response"  data-recaptcha-action="verifyEmailOtp">
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
                                            <button type="button" id="emailVerifyBtn" onclick="isEmailVerified()" class="btn btn-primary btn-flat float-right btn-lg">
                                                <span id="emailVerifyBtnText">{{ __('message.verify') }}</span>
                                            </button>
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
        let emailTimerInterval;
        const eid = @json($eid);

        const TIMER_DURATION = 120;
        let mobileCountdown = TIMER_DURATION;
        let emailCountdown = TIMER_DURATION;

        let mobile_recaptcha_id;
        let email_recaptcha_id;
        let recaptcha;
        let recaptchaToken;

        // Verification state tracking
        let verificationState = {
            isMobileVerified: @json($isMobileVerified),
            isEmailVerified: @json($isEmailVerified),
            verificationPreference: @json($verification_preference ?? 'mobile')
        };

        @if($setting->recaptcha_status === 1)
        recaptchaFunctionToExecute.push(() => {
            mobile_recaptcha_id = grecaptcha.render('recaptchaMobile', { 'sitekey': siteKey });
            email_recaptcha_id = grecaptcha.render('recaptchaEmail', { 'sitekey': siteKey });
        });
        @endif

        // Restrict input to 6 digits only
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

        function startTimer(button, display, duration, type = 'mobile') {
            if (type === 'mobile') {
                clearInterval(timerInterval);
                mobileCountdown = duration;
                button.disabled = true;
                additionalButton.disabled = true;
                display.style.display = "block";
                updateTimer(display, mobileCountdown);

                timerInterval = setInterval(() => {
                    mobileCountdown--;
                    if (mobileCountdown <= 0) {
                        clearInterval(timerInterval);
                        button.disabled = false;
                        additionalButton.disabled = false;
                        display.style.display = "none";
                    }
                    updateTimer(display, mobileCountdown);
                }, 1000);
            } else if (type === 'email') {
                clearInterval(emailTimerInterval);
                emailCountdown = duration;
                button.disabled = true;
                display.style.display = "block";
                updateTimer(display, emailCountdown);

                emailTimerInterval = setInterval(() => {
                    emailCountdown--;
                    if (emailCountdown <= 0) {
                        clearInterval(emailTimerInterval);
                        button.disabled = false;
                        display.style.display = "none";
                    }
                    updateTimer(display, emailCountdown);
                }, 1000);
            }
        }
        async function generateV3Token(data, action = 'default') {
            @if($setting->v3_recaptcha_status === 1)
                try {
                const token = await generateRecaptchaToken(action);
                data['g-recaptcha-response'] = token;
            } catch (error) {
                // handle token error silently
            }
            @endif
                return data;
        }

        async function resendOTP(default_type, type) {
            const data = await generateV3Token({eid, default_type, type}, 'resendOtp');
            $.ajax({
                url: '{{ url('resend_otp') }}',
                type: 'POST',
                data: data,
                success: function (response) {
                    if (default_type === 'mobile') {
                        startTimer(otpButton, timerDisplay, TIMER_DURATION, 'mobile');
                        showAlert('success', response.message, '#alert-container');
                    } else if (default_type === 'email') {
                        startTimer(emailOtpButton, emailTimerDisplay, TIMER_DURATION, 'email');
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

        async function sendOTP() {
            startTimer(otpButton, timerDisplay, TIMER_DURATION, 'mobile');
            const data = await generateV3Token({eid : eid}, 'sendOtp');
            $.ajax({
                url: '{{ url('otp/send') }}',
                type: 'POST',
                data: data,
                success: function (response) {
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
                beforeSend: function () {
                    toggleButtonState('mobileVerifyBtn', 'mobileVerifyBtnText', true); // Disable and show "Verifying..."
                },
                success: function (response) {
                    verificationState.isMobileVerified = true;
                    updateProgressBar('mobile', 'completed');

                    if (!verificationState.isEmailVerified) {
                        showNextVerificationStep();
                    } else {
                        showSuccessAndRedirect();
                    }
                },
                error: function (error) {
                    showAlert('danger', error.responseJSON.message, '#alert-container');

                },
                complete: function () {
                    toggleButtonState('mobileVerifyBtn', 'mobileVerifyBtnText', false); // Re-enable and reset text to "Verify"
                }
            });
        }


        function autoFocus() {
            // Focus on OTP if it's active
            if (document.querySelector('#otp_li')?.classList.contains('active')) {
                setTimeout(() => {
                    const otpInput = document.querySelector('#otp');
                    if (otpInput) {
                        otpInput.focus();
                        otpInput.addEventListener('keyup', function (event) {
                            if (event.key === 'Enter') {
                                submitOtp();
                            }
                        });
                    }
                }, 300);
            }

            // Focus on Email OTP if it's active
            if (document.querySelector('#email_li')?.classList.contains('active')) {
                setTimeout(() => {
                    const emailOtpInput = document.querySelector('#email_otp');
                    if (emailOtpInput) {
                        emailOtpInput.focus();
                        emailOtpInput.addEventListener('keyup', function (event) {
                            if (event.key === 'Enter') {
                                isEmailVerified();
                            }
                        });
                    }
                }, 300);
            }
        }

        async function sendEmail() {
            const data = await generateV3Token({eid: eid}, 'sendEmail');
            $.ajax({
                url: '{{ url('/send-email') }}',
                type: 'POST',
                data: data,
                success: function (response) {
                    startTimer(emailOtpButton, emailTimerDisplay, TIMER_DURATION, 'email');
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
                beforeSend: function () {
                    toggleButtonState('emailVerifyBtn', 'emailVerifyBtnText', true); // Disable and show "Verifying..."
                },
                success: function (response) {
                    verificationState.isEmailVerified = true;
                    updateProgressBar('email', 'completed');

                    if (!verificationState.isMobileVerified) {
                        showNextVerificationStep();
                    } else {
                        showSuccessAndRedirect();
                    }
                },
                error: function (error) {
                    showAlert('danger', error.responseJSON.message, '#alert-container-email');
                },
                complete: function () {
                    toggleButtonState('emailVerifyBtn', 'emailVerifyBtnText', false); // Re-enable and reset text to "Verify"
                }
            });
        }

        function toggleButtonState(buttonId, textSpanId, isLoading = true) {
            const button = document.getElementById(buttonId);
            const textSpan = document.getElementById(textSpanId);

            if (!button || !textSpan) return;

            if (isLoading) {
                button.disabled = true;
                textSpan.textContent = 'Verifying...';
            } else {
                button.disabled = false;
                textSpan.textContent = 'Verify';
            }
        }

        function updateProgressBar(type, status) {
            const element = document.getElementById(type === 'mobile' ? 'otp_li' : 'email_li');
            if (element) {
                element.classList.add(status);
            }
        }

        function showNextVerificationStep() {
            if (!verificationState.isMobileVerified && verificationState.isEmailVerified) {
                // Show mobile verification next
                activateFieldset('fieldSetOne');
            } else if (!verificationState.isEmailVerified && verificationState.isMobileVerified) {
                // Show email verification next
                activateFieldset('fieldSetTwo');
            }
        }

        function showSuccessAndRedirect() {
            window.location.href = "{{ url('/login') }}";
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

            // Hide all fieldsets
            Object.values(fieldsets).forEach(fieldset => {
                if (fieldset) fieldset.classList.add('hidden');
            });

            // Show the target fieldset and update progress
            if (fieldsets[fieldSet]) {
                fieldsets[fieldSet].classList.remove('hidden');

                if (fieldSet === 'fieldSetOne' && !verificationState.isMobileVerified) {
                    if (progressList[fieldSet]) {
                        progressList[fieldSet].classList.add('active');
                    }
                    autoFocus();
                    setTimeout(() => {
                        sendOTP();
                    }, 500);
                } else if (fieldSet === 'fieldSetTwo' && !verificationState.isEmailVerified) {
                    if (progressList[fieldSet]) {
                        progressList[fieldSet].classList.add('active');
                    }
                    autoFocus();
                    setTimeout(() => {
                        sendEmail();
                    }, 500);
                } else if (fieldSet === 'fieldSetThree') {
                    if (progressList[fieldSet]) {
                        progressList[fieldSet].classList.add('active');
                    }
                }
            }
        }

        function showAlert(type, message, container) {
            const icon = type === 'success' ? 'fa-check-circle' : 'fa-ban';
            const alertType = type === 'success' ? 'alert-success' : 'alert-danger';


            const html = `<div class="alert ${alertType} alert-dismissible" id="otpAlertMsg">
                    <i class="fa ${icon}"></i> ${message}
                    <button type="button" class="btn-close" data-dismiss="alert" aria-hidden="true"></button>
                  </div>`;

            $(container).html(html);

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

        // Initialize the appropriate verification step on page load
        $(document).ready(function() {
            if (verificationState.isMobileVerified && verificationState.isEmailVerified) {
                // Both verified - should not reach here, but redirect just in case
                window.location.href = "{{ url('/login') }}";
            } else if (!verificationState.isMobileVerified && !verificationState.isEmailVerified) {
                // Neither verified - show based on preference
                if (verificationState.verificationPreference === 'email') {
                    activateFieldset('fieldSetTwo');
                } else {
                    activateFieldset('fieldSetOne');
                }
            } else if (!verificationState.isMobileVerified && verificationState.isEmailVerified) {
                // Only mobile needs verification
                activateFieldset('fieldSetOne');
            } else if (!verificationState.isEmailVerified && verificationState.isMobileVerified) {
                // Only email needs verification
                activateFieldset('fieldSetTwo');
            }
        });
    </script>
@stop