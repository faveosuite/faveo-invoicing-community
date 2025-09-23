
        <!-- Edit Mobile Modal -->
        <div class="modal fade" id="editMobileModal" tabindex="-1" aria-labelledby="editMobileModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered" style="max-width: 570px;">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="editMobileModalLabel">
                            {{ __('message.update_mobile_no') }}
                        </h5>
                        <button type="button" class="btn-close closeandrefresh" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>

                    <div class="modal-body">
                        <div id="mobileAlertShow" class="alert alert-danger alert-dismissible fade show d-none" role="alert" style="display:block; padding: 10px 12px; font-size: 14px;">
                            <span id="mobileAlertShowMsg"></span>
                        </div>

                        <div id="editMobileSuccess" class="alert alert-success d-none" role="alert"></div>

                        <form id="editMobileForm">
                            @csrf
                            <div class="mb-3">
                                <label for="newMobile" class="form-label">{{ __('message.enter_new_mobile_no') }}</label>
                                <input type="tel" class="form-control" id="newMobile" name="mobile_to_verify">
                                <span id="editMobileError" class="invalid-feedback"></span>
                            </div>
                        </form>
                    </div>

                    <div class="modal-footer">
                        <button type="button" class="btn btn-default pull-left closebutton" data-bs-dismiss="modal">
                            {{ __('message.cancel') }}
                        </button>
                        <button type="submit" form="editMobileForm" id="editMobileFormBtn" class="btn btn-dark px-4">
                            {{ __('message.submit') }}
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- OTP Verification Modal for Mobile -->
        <div class="modal fade" id="otpMobVerificationModalForNew" tabindex="-1" role="dialog" aria-labelledby="otpVerificationModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered" style="max-width: 570px;">
                <div class="modal-content">
                    <!-- Header -->
                    <div class="modal-header">
                        <h5 class="modal-title">{{ __('message.otp_code_verification') }}</h5>
                        <button type="button" class="btn-close closeandrefresh" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>

                    <div class="modal-body">
                        <div class="alert alert-warning" id="otpInfoMob" role="alert">
                            <i class="fa fa-info-circle me-2"></i>
                            <span id="otp-message-mobile" data-msg="{{ __('message.otp_sent_mobile_no') }}">
                            </span>
                        </div>
                        <div id="otpMobileAlert" class="alert d-none" role="alert" style="display:block; padding: 10px 12px; font-size: 14px;">
                            <span id="otpMobileAlertMsg"></span>
                        </div>

                        <form id="otpVerificationFormMobileNew">
                            @csrf
                            <div class="form-group mb-3">
                                <label for="otpCodeMobile">{{ __('message.enter_otp_code') }}</label>
                                <input type="text" class="form-control" id="otpCodeMobile" name="otp_code" maxlength="6">
                                <input type="hidden" id="existEmail" name="existEmail" value="{{$user->email}}">
                                <span id="otpErrorMobile" class="invalid-feedback"></span>
                                <span class="font-italic">
                                    {{ __('message.enter_otp_code_new_mobile_no') }}
                                </span>
                            </div>

                            <div class="col-12 mt-4">
                                <div class="row">
                                    <div class="col-6 px-0 d-flex align-items-center">
                                        <button id="otpMobileResendBtn" type="button"
                                                class="btn btn-link p-0"
                                                style="color: gray; pointer-events: none; text-decoration: none;">
                                            <i class="fa fa-refresh"></i>
                                            {{ __('message.get_new_otp_code') }}
                                        </button>
                                        <div id="timerMobile" class="ms-2"></div>
                                    </div>

                                    <div class="col-6 px-0 text-end">
                                        <button type="submit" id="verifyOtpMobileBtn" class="btn btn-primary btn-lg">
                                            <span id="mobileVerifyBtnText">{{ __('message.verify') }}</span>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <!--For Confirmation verification through existing mobile number -->
        <div class="modal fade" id="confirmationFromEmailModal" tabindex="-1" role="dialog" aria-labelledby="otpVerificationModalForNewMobile" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered" style="max-width: 570px;">
                <div id="otpAlertErrorMobile" class="alert alert-danger d-none"></div>
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">{{ __('message.otp_code_verification') }}</h5>
                        <button type="button" class="btn-close closeandrefresh" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="alert alert-warning" id="otpInfo" role="alert">
                            <i class="fa fa-info-circle me-2"></i>
                            <span>{{ __('message.otp_sent_to_email_for_mobile_change', ['email' => $user->email]) }}</span>
                        </div>
                        <div id="otpSuccessMobile" class="alert d-none" role="alert" style="display:block; padding: 10px 12px; font-size: 14px;">
                            <span id="otpAlertShowMsgMobile"></span>
                        </div>

                        <form id="verifyOtpWithExistEmail">
                            @csrf
                            <div class="form-group mb-3">
                                <label for="otpCodeEmail">{{ __('message.enter_otp_code') }}</label>
                                <input type="text" class="form-control" id="otpCodeEmail" name="otp_code" maxlength="6">

                                <input type="hidden" id="otpMobileNumber" name="mobile_to_verify">
                                <input type="hidden" id="otpDialCode" name="dial_code">
                                <input type="hidden" id="otpCountryIso" name="country_iso">

                                <span id="otpErrorEmail" class="invalid-feedback mb-2"></span>
                                <span class="font-italic">{{ __('message.enter_otp_old_email') }}</span>

                            </div>
                            <div class="col-12 mt-4">
                                <div class="row">
                                    <div class="col-6 px-0 d-flex align-items-center">
                                        <button id="resendOtpBtnMobile" type="button"
                                                class="btn btn-link p-0"
                                                style="color: gray; pointer-events: none; text-decoration: none;">
                                            <i class="fa fa-refresh"></i>
                                            {{ __('message.get_new_otp_code') }}
                                        </button>
                                        <div id="timerMobileEmail" class="ms-2"></div>
                                    </div>
                                    <div class="col-6 px-0 text-end">
                                        <button type="submit" id="verifyOtpMobileBtnEmail" class="btn btn-primary btn-lg">
                                            <span id="mobileVerifyBtnText">{{ __('message.verify') }}</span>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <!--Final Success Modal Updated Mobile Number -->
        <div class="modal fade" id="mobileSuccessModal" tabindex="-1" role="dialog" aria-labelledby="successModalLabelMobile" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="successModalLabelMobile">
                            {{ __('message.mobile_no_changed_successfully') }}
                        </h5>
                        <button type="button" class="btn-close closeandrefresh white-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body text-center">
                        <div class="mb-4">
                            <i class="fas fa-check-circle text-success" style="font-size: 4rem;"></i>
                        </div>
                        <h5 class="text-success mb-3">{{ __('message.mobile_no_updated_successfully') }}</h5>
                        <div class="alert alert-success" id="mobileUpdatedAlert" style="display: none">
                            <p class="mb-1 text-black">{{ __('message.your_mobile_no_changed_successfully') }}</p>
                            <strong id="finalNewMobileDisplay"></strong>
                        </div>
                    </div>
                    <div class="modal-footer center-footer">
                        <button type="button" class="btn btn-dark" data-bs-dismiss="modal" onclick="location.reload()">
                            {{ __('message.done') }}
                        </button>
                    </div>
                </div>
            </div>
        </div>



<style>
    .modal-footer.center-footer {
        justify-content: center !important;
    }
</style>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>


<script>
    $(document).ready(function () {
        var mobInput = document.querySelector("#newMobile");
        const csrfToken = $('input[name="_token"]').val();
        window.AppGlobals = {
            newMobileFull: null,
            dialCode: null,
            isoCode: null,
            cleanPhone: null, // will hold something like +919000990003
        };
        // Open the New Mobile Modal
        $('#editMobileBtn').on('click', function() {
            $('#editMobileModal').modal('show');
        });

        $(document).on("close.bs.alert", "#mobileAlertShow", function (e) {
            e.preventDefault();
            $(this).hide();
        });

        $(document).on("close.bs.alert", "#otpMobileAlert", function (e) {
            e.preventDefault();
            $(this).hide();
        });

        $(document).on("close.bs.alert", "#otpErrorMobile", function (e) {
            e.preventDefault();
            $(this).hide();
        });

        $(document).on("close.bs.alert", "#mobileUpdatedAlert", function (e) {
            e.preventDefault();
            $(this).hide();
        });

        function showValidationError(field, errorBox, message) {
            field.addClass('is-invalid');       // red border
            errorBox.text(message).show();      // show error message
        }

        function clearValidation(field, errorBox) {
            field.removeClass('is-invalid');
            errorBox.text('').hide();
        }

        $('#otpCodeMobile').on('input', function () {
            let value = $(this).val();

            // Remove non-numeric characters
            value = value.replace(/\D/g, '');

            // Limit to 6 digits
            if (value.length > 6) {
                value = value.substring(0, 6);
            }

            $(this).val(value);
        });

        $('#otpCodeEmail').on('input', function () {
            let value = $(this).val();

            // Remove non-numeric characters
            value = value.replace(/\D/g, '');

            // Limit to 6 digits
            if (value.length > 6) {
                value = value.substring(0, 6);
            }

            $(this).val(value);
        });

        // Submit new mobile number for verification
        $('#editMobileForm').on('submit', function (e) {
            e.preventDefault();
            let mobField = $('#newMobile');
            let mobileVal = mobField.val().trim();
            let errorBox = $('#editMobileError');

            // Clear previous validation
            clearValidation(mobField, errorBox);

            if (!mobileVal) {
                showValidationError(mobField, errorBox, "{{ __('message.login_validation.mobile_required') }}");
                return;
            }

            // Extract dial code & ISO
            let dialCode   = mobInput.getAttribute('data-dial-code') || "";
            let isoCode    = $('#newMobile').attr('data-country-iso')?.toUpperCase() || "";
            let cleanPhone = mobileVal.replace(/\D/g, ''); // remove non-digits

            // Save globally for verify step
            let fullMobile = dialCode + cleanPhone;
            let fullMobileInfo = `+${dialCode} ${cleanPhone}`;
            window.AppGlobals.dialCode = dialCode;
            window.AppGlobals.isoCode = isoCode;
            window.AppGlobals.cleanPhone = cleanPhone;
            window.AppGlobals.newMobileFull = fullMobile;

            // Disable button while processing
            $('#editMobileFormBtn').prop('disabled', true).text('{{ __('message.sending') }}');

            $.ajax({
                url: "{{ url('mobileNoexist') }}",
                type: "POST",
                data: {
                    _token: csrfToken,
                    mobile_to_verify: cleanPhone,
                    dial_code: dialCode,
                    country_iso: isoCode
                },
                success: function (response) {
                    if (response.data.mobile_verification_required === false) {
                        changeMobileFinal();
                    }
                    else{
                        sendOtpToNewMobile(cleanPhone, dialCode, isoCode);
                    }
                    let template = document.getElementById('otp-message-mobile').dataset.msg;
                    document.getElementById('otp-message-mobile').innerText = template.replace(':mobile', fullMobileInfo);
                },
                error: function (xhr) {
                    let mobileErr1 = xhr.responseJSON || {};
                    let mobMsg1 = mobileErr1.message || "{{ __('message.something_wrong') }}";
                    let alertBoxMob = $("#mobileAlertShow");

                    alertBoxMob
                        .removeClass() // clear old classes
                        .addClass("alert alert-danger alert-dismissible fade show")
                        .css("display", "block"); // force show again

                    $("#mobileAlertShowMsg").text(mobMsg1);

                    $('#editMobileFormBtn').prop('disabled', false).text("{{ __('message.submit') }}");
                }
            });
        });

        // Send OTP to new mobile number
        function sendOtpToNewMobile(mobile, dialCode, countryIso) {
            $.ajax({
                url: "{{ url('newMobileNoVerify') }}",
                type: "POST",
                data: {
                    _token: csrfToken,
                    mobile_to_verify: mobile,
                    dial_code: dialCode,
                    country_iso: countryIso
                },
                success: function (response) {
                    if (response.success) {
                        $("#editMobileModal").modal("hide");
                        $("#otpMobVerificationModalForNew").modal("show");
                        let alertMobOtpSuccess = $("#otpMobileAlert");


                        setTimeout(() => {
                            $('#otpMobVerificationModalForNew').modal('show');
                            startTimer(
                                document.getElementById("otpMobileResendBtn"),
                                document.getElementById("timerMobile"),
                            );
                        }, 400);

                    }
                },
                error: function (xhr) {
                    let mob2 = xhr.responseJSON || {};
                    let mobMsg2 = mob2.message || "{{ __('message.something_wrong') }}";
                    showValidationError(mobField2, errorBoxMob2,mobMsg2);
                },
            });
        }

        // Verify  OTP for new mobile number
        $('#otpVerificationFormMobileNew').on('submit', function (e) {
            e.preventDefault();
            let mobField2 = $('#otpCodeMobile');
            let mobileVal2 = mobField2.val().trim();
            let errorBoxMob2 = $('#otpErrorMobile');

            let otp = $('#otpCodeMobile').val().trim();
            $('#otpMobileAlert').hide();

            if (!mobileVal2) {
                showValidationError(mobField2, errorBoxMob2, "{{ __('message.otp_code_required') }}");
                return;
            }

            let otpRegex = /^\d{6}$/;
            if (!otpRegex.test(mobileVal2)) {
                showValidationError(mobField2, errorBoxMob2, "{{ __('message.otp_code_invalid') }}");
                return;
            }
            let fullMobile = window.AppGlobals.newMobileFull;

            $.ajax({
                url: "{{ url('verify/newMobileNoOtp') }}",
                type: "POST",
                data: {
                    _token: csrfToken,
                    mobile_to_verify: fullMobile,
                    otp: otp
                },
                beforeSend: function () {
                    // Disable button and show loading message
                    $("#verifyOtpMobileBtn").prop("disabled", true).text("{{ __('message.2fa_verifying') }}");
                },
                success: function (response) {
                    let existEmailVal = $('#existEmail').val();
                    if (response.success) {
                        sentOtpCodeToAuthEmail(existEmailVal);
                    }
                    else {
                        $("#verifyOtpMobileBtn").prop("disabled", false).text("{{ __('message.verify') }}");
                    }
                },
                error: function (xhr) {
                    $("#verifyOtpMobileBtn").prop("disabled", false).text("{{ __('message.verify') }}");
                    let mob2 = xhr.responseJSON || {};
                    let mobMsg2 = mob2.message || "{{ __('message.something_wrong') }}";
                    showValidationError(mobField2, errorBoxMob2,mobMsg2);
                },
            });
        });

        function sentOtpCodeToAuthEmail(existEmailVal) {
            let alertMobOtpSuccess2 = $("#otpSuccessMobile");
            $.ajax({
                url: "{{ url('emailUpdateEditProfile') }}",
                type: "POST",
                data: {
                    _token: csrfToken,
                    email_to_verify: existEmailVal,
                    is_mobile: 1
                },
                success: function (response) {
                    $("#otpMobVerificationModalForNew").modal("hide");

                    setTimeout(() => {
                        $("#confirmationFromEmailModal").modal("show");
                        startTimer(
                            document.getElementById("resendOtpBtnMobile"),
                            document.getElementById("timerMobileEmail"),
                        );
                    }, 200);
                    },
                error: function (xhr) {
                    let authVl = xhr.responseJSON || {};
                    let authMsg= authVl.message || "{{ __('message.something_wrong') }}";
                    alertMobOtpSuccess2
                        .removeClass()
                        .addClass("alert alert-danger alert-dismissible fade show")
                        .css("display", "block");

                    $("#otpAlertShowMsgMobile").text(authMsg);
                    },
            });

        }

        // Verify OTP sent to existing email for final confirmation
        $('#verifyOtpWithExistEmail').on('submit', function (e) {
            e.preventDefault();
            let mobField3 = $('#otpCodeEmail');
            let mobileVal3 = mobField3.val().trim();
            let errorBoxMob3 = $('#otpErrorEmail');

            $('#otpSuccessMobile').hide();


            if (!mobileVal3) {
                showValidationError(mobField3, errorBoxMob3, "{{ __('message.otp_code_required') }}");
                return;
            }

            let otpRegex = /^\d{6}$/;
            if (!otpRegex.test(mobileVal3)) {
                showValidationError(mobField3, errorBoxMob3, "{{ __('message.otp_code_invalid') }}");
                return;
            }
            let fullMobile = window.AppGlobals.newMobileFull;

            $.ajax({
                url: "{{ url('otpVerifyForNewEmail') }}",
                type: "POST",
                data: {
                    _token: csrfToken,
                    otp: mobileVal3,
                    email_to_verify: "{{ $user->email }}",
                },
                beforeSend: function () {
                    // Disable button and show loading message
                    $("#verifyOtpMobileBtnEmail").prop("disabled", true).text("{{ __('message.2fa_verifying') }}");
                },
                success: function (response) {
                    if (response.success) {
                        changeMobileFinal();
                    } else {
                        $("#verifyOtpMobileBtnEmail").prop("disabled", false).text("{{ __('message.verify') }}");
                    }
                },
                error: function (xhr) {
                    let mob3 = xhr.responseJSON || {};
                    let mobMsg3 = mob3.message || "{{ __('message.2fa_verifying') }}";
                    showValidationError(mobField3, errorBoxMob3, mobMsg3);
                    $("#verifyOtpMobileBtnEmail").prop("disabled", false).text("{{ __('message.verify') }}");

                },
            });
        });

        //Update the new mobile number
        function changeMobileFinal() {

            let alertMobOtpSuccess3 = $("#otpSuccessMobile");
            let alertMobSuccessBox = $("#mobileUpdatedAlert");
            $.ajax({
                url: "{{ url('user/change-mobile-no') }}",
                type: "POST",
                data: {
                    _token: csrfToken,
                    newMobile: window.AppGlobals.cleanPhone,
                    dial_code: window.AppGlobals.dialCode,
                    country_iso: window.AppGlobals.isoCode,
                },
                success: function (res) {
                    if (res.success) {
                        $('#confirmationFromEmailModal').modal('hide');
                        $('#editMobileModal').modal('hide');
                        $("#finalNewMobileDisplay").text("+" + res.data.mobile_code + " " + res.data.mobile);
                        alertMobSuccessBox
                            .removeClass()
                            .addClass("alert alert-success alert-dismissible fade show")
                            .css("display", "block");

                        $('#mobileSuccessModal').modal('show');
                    }
                },
                error: function (xhr) {
                    let mob4 = xhr.responseJSON || {};
                    let mobMsg4 = mob4.message || "{{ __('message.something_wrong') }}";
                    alertMobOtpSuccess3
                        .removeClass()
                        .addClass("alert alert-danger alert-dismissible fade show")
                        .css("display", "block");

                    $("#otpAlertShowMsgMobile").text(mobMsg4);
                    $("#mobileVerifyBtnText").prop("disabled", false).text("{{ __('message.verify') }}");

                },
            });
        }

        // Resend OTP for new mobile number
        function resentOtpForMobile(type) {
            let fullMobile = window.AppGlobals.newMobileFull;
            let dialCode = window.AppGlobals.dialCode;
            let isoCode = window.AppGlobals.isoCode;

            $.ajax({
                url: "{{ url('resendOtp/email-mobile') }}",
                type: "POST",
                data: {
                    _token: csrfToken,
                    mobile_to_verify: fullMobile,
                    dial_code: dialCode,
                    type: type,
                    country_iso: isoCode
                },
                success: function (response) {
                    if (response.success) {
                        let alertMobOtpSuccess = $("#otpMobileAlert");

                        setTimeout(() => {
                            $('#otpMobVerificationModalForNew').modal('show');
                            alertMobOtpSuccess
                                .removeClass()
                                .addClass("alert alert-success alert-dismissible fade show")
                                .css("display", "block");

                            $("#otpMobileAlertMsg").text(response.message);
                        }, 400);

                        // 🔹 Restart timer after resend
                        startTimer(
                            document.getElementById("otpMobileResendBtn"),
                            document.getElementById("timerMobile"),
                        );
                    }
                },
                error: function (xhr) {
                    let mob2 = xhr.responseJSON || {};
                    let mobMsg2 = mob2.message || "{{ __('message.something_wrong') }}";
                    let alertBoxMob = $("#otpMobileAlert");

                    alertBoxMob
                        .removeClass()
                        .addClass("alert alert-danger alert-dismissible fade show")
                        .css("display", "block");

                    $("#otpMobileAlertMsg").text(mobMsg2);
                },
            });
        }

        $('#otpMobileResendBtn').on('click', function (e) {
            // if button still disabled this won't fire, but just in case:
            if (this.disabled) return;
            $('#otpMobileAlert').hide();
            resentOtpForMobile('mobile');
        });

        $('#resendOtpBtnMobile').on('click', function (e) {
            // if button still disabled this won't fire, but just in case:
            if (this.disabled) return;
            $('#otpSuccessMobile').hide();
            resendOtpCodeToAuthEmail('email');
        });

        // Resend OTP for confirmationFromEmailModal
        function resendOtpCodeToAuthEmail(type) {
            let alertBox = $("#otpSuccessMobile");

            $.ajax({
                url: "{{ url('resendOtp/email-mobile') }}",
                type: "POST",
                data: {
                    _token: csrfToken,
                    type: type,
                    email_to_verify: "{{ $user->email }}",
                    is_mobile: 1
                },
                beforeSend: function () {
                    // Disable button and show loading message
                    $("#verifyOtpMobileBtnEmail").prop("disabled", true).text("{{ __('message.2fa_verifying') }}");
                },
                success: function (response) {
                    setTimeout(() => {
                        $("#confirmationFromEmailModal").modal("show");
                        alertBox
                            .removeClass()
                            .addClass("alert alert-success alert-dismissible fade show")
                            .css("display", "block");

                        $("#otpAlertShowMsgMobile").text(response.message);
                    }, 200);

                    //Restart timer after resend
                    startTimer(
                        document.getElementById("resendOtpBtnMobile"),
                        document.getElementById("timerMobileEmail"),
                    );
                },
                error: function (xhr) {
                    $("#verifyOtpMobileBtnEmail").prop("disabled", false).text("{{ __('message.verify') }}");
                    let err = xhr.responseJSON || {};
                    let msg = err.message || "{{ __('message.something_wrong') }}";
                    alertBox
                        .removeClass()
                        .addClass("alert alert-danger alert-dismissible fade show")
                        .css("display", "block");

                    $("#otpAlertShowMsgMobile").text(msg);
                },
                complete: function() {
                    $("#verifyOtpMobileBtnEmail").prop("disabled", false).text("{{ __('message.verify') }}");
                },
            });
        }


        const RESEND_DURATION = 60; // 1 min lock

        function updateTimer(display, countdown) {
            display.textContent = countdown.toString().padStart(2, '0') + " seconds";
        }

        function startTimer(button, display, duration = RESEND_DURATION) {
            let countdown = duration;
            button.style.color = "gray";             // grey text
            button.style.pointerEvents = "none";     // disable click
            updateTimer(display, countdown);

            let interval = setInterval(() => {
                countdown--;
                if (countdown <= 0) {
                    clearInterval(interval);
                    display.textContent = "";        // clear timer
                    button.style.color = "#099fdc";     // make clickable
                    button.style.pointerEvents = "auto";
                } else {
                    updateTimer(display, countdown);
                }
            }, 1000);
        }
    });

</script>