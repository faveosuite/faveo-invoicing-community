
<!-- Edit Email Modal -->
<div class="modal fade" id="editEmailModal" tabindex="-1" role="dialog" aria-labelledby="editEmailModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" style="max-width: 570px;">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">{{ __('message.update_email_address') }}</h5>
                <button type="button" class="btn-close closeandrefresh" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div id="emailAlertShow" class="alert alert-danger alert-dismissible " role="alert" style="display: none">
                    <span id="emailAlertShowMsg"></span>
                </div>
                <form id="editEmailForm">
                    @csrf
                    <div class="form-group mb-3">
                        <label for="newEmail">{{ __('message.emailSettings_details.email') }}</label>
                        <input type="text" class="form-control" id="newEmail" name="email_to_verify">
                        <span id="editEmailError" class="invalid-feedback"></span>
                    </div>
                <div id="editEmailSuccess" class="text-success mt-2" style="display:none;"></div>
            </div>

            <div class="modal-footer">
                <button type="button" id="editEmailFormBtnCancel" class="btn btn-default pull-left closebutton">{{ __('message.cancel') }}</button>

                <button type="submit" id="editEmailFormBtn" class="btn btn-dark">{{ __('message.submit') }}</button>

            </div>
            </form>

        </div>
    </div>
</div>

<!-- OTP Verification Modal for New Email -->
<div class="modal fade" id="otpVerificationModal" tabindex="-1" role="dialog" aria-labelledby="otpVerificationModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" style="max-width: 570px;">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">{{ __('message.otp_code_verification') }}</h5>
                <button type="button" class="btn-close closeandrefresh" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="alert alert-warning" id="otpInfo" role="alert">
                    <i class="fa fa-info-circle me-2"></i>
                    <span id="otp-message" data-msg="{{ __('message.otp_sent_new_email') }}">
                    </span>
                </div>
                <div id="otpSuccess" class="alert alert-danger alert-dismissible " role="alert" style="display:block; padding: 10px 12px; font-size: 14px;">
                    <span id="otpAlertShowMsg"></span>
                </div>

                <form id="otpVerificationForm">
                    @csrf
                    <div class="form-group mb-3">
                        <label for="otpCode">{{ __('message.enter_otp_code') }}</label>
                        <input type="text" class="form-control" id="otpCodeNew" name="otp_code" maxlength="6">
                        <input type="hidden" id="otpNewEmail" name="email_to_verify">
                        <span id="otpErrorNew" class="invalid-feedback mb-2"></span>
                        <span class="font-italic">{{ __('message.enter_otp_code_verify') }}</span>
                    </div>
                    <div class="col-12 mt-4">
                        <div class="row">
                            <div class="col-6 px-0 d-flex align-items-center fs-6">
                                <button id="otpButtonn" type="button"
                                        class="btn btn-link p-0"
                                        style="color: gray; pointer-events: none; text-decoration: none;">
                                    <i class="fa fa-refresh"></i>
                                    {{ __('message.get_new_otp_code') }}
                                </button>
                                <div id="timerEmail" class="ms-2"></div>
                            </div>
                            <div class="col-6 px-0 text-end">
                                <button type="submit" id="verifyOtpBtn"  class="btn btn-primary btn-lg">
                                    <span id="emailVerifyBtnText">{{ __('message.verify') }}</span>
                                </button>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>


<!-- OTP Verification Modal for Old Email -->
<div class="modal fade" id="otpVerificationModalForOldEmail" tabindex="-1" role="dialog" aria-labelledby="otpVerificationModalForOldEmail" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" style="max-width: 570px;">
        <div id="otpAlertError" class="alert alert-danger d-none"></div>
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">{{ __('message.otp_code_verification') }}</h5>
                <button type="button" class="btn-close closeandrefresh" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="alert alert-warning" id="otpInfo" role="alert">
                    <i class="fa fa-info-circle me-2"></i>
                    <span>{{ __('message.otp_sent_to_old_email', ['email' => $user->email]) }}</span>
                </div>
                <div id="otpSuccessOld" class="alert d-none " role="alert" style="display:block; padding: 10px 12px; font-size: 14px;">
                    <span id="otpAlertShowMsgOld"></span>
                </div>

                <form id="otpVerificationFormOld">
                    @csrf
                    <div class="form-group mb-3">
                        <label for="otpCode">{{ __('message.enter_otp_code') }}</label>
                        <input type="text" class="form-control" id="otpCodeOld" name="otp_code">
                        <input type="hidden" id="otpOldEmail" name="email_to_verify" value="{{ $user->email }}">
                        <span id="otpErrorOld" class="invalid-feedback mb-2"></span>
                        <span class="font-italic">{{ __('message.enter_otp_old_email') }}</span>
                    </div>
                    <div class="col-12 mt-4">
                        <div class="row">
                            <div class="col-6 px-0 d-flex align-items-center">
                                <button id="resendOtpBtn" type="button"
                                        class="btn btn-link p-0"
                                        style="color: gray; pointer-events: none; text-decoration: none;">
                                    <i class="fa fa-refresh"></i>
                                    {{ __('message.get_new_otp_code') }}
                                </button>
                                <div id="timerEmailOld" class="ms-2"></div>
                            </div>
                            <div class="col-6 px-0 text-end">
                                <button type="submit" id="verifyOtpBtnOld"  class="btn btn-primary btn-lg">
                                    <span id="emailVerifyBtnText">{{ __('message.verify') }}</span>
                                </button>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="emailSuccessModal" tabindex="-1" role="dialog" aria-labelledby="successModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="successModalLabel">
                    {{ __('message.email_changed_successfully') }}
                </h5>
                <button type="button" class="btn-close closeandrefresh white-close" data-bs-dismiss="modal" aria-label="Close"></button>

            </div>
            <div class="modal-body text-center">
                <div class="mb-4">
                    <i class="fas fa-check-circle text-success" style="font-size: 4rem;"></i>
                </div>
                <h5 class="text-success mb-3">{{ __('message.email_updated_successfully') }}</h5>
                <div class="alert alert-success alert-dismissible fade show"  id="emailUpdatedAlert" style="display: none">
                    <p class="mb-1 text-black">{{ __('message.your_email_changed_successfully') }}</p>
                    <strong id="finalNewEmailDisplay"></strong>
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
    .white-close {
        filter: invert(1) brightness(100%);
    }
    .modal-footer.center-footer {
        justify-content: center !important;
    }
</style>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>


<script>
    $(document).ready(function() {
        $(document).on("close.bs.alert", "#emailAlertShow", function (e) {
            e.preventDefault();
            $(this).hide();
        });

        $(document).on("close.bs.alert", "#otpSuccess", function (e) {
            e.preventDefault();
            $(this).hide();
        });

        $(document).on("close.bs.alert", "#otpSuccessOld", function (e) {
            e.preventDefault();
            $(this).hide();
        });

        $(document).on("close.bs.alert", "#emailUpdatedAlert", function (e) {
            e.preventDefault();
            $(this).hide();
        });

        const csrfToken = $('input[name="_token"]').val();

        // Email edit modal logic
        $('#editEmailBtn').on('click', function() {
            $('#editEmailModal').modal('show');
            $('#editEmailError').text('');
            $('#editEmailSuccess').hide();
            $('#newEmail').val('');
            $('#newEmail').removeClass('is-invalid'); // Remove error styling
        });

        //Submit email update
  $('#editEmailForm').on('submit', function (e) {
        e.preventDefault();
        let emailField = $('#newEmail');
        let emailVal = emailField.val().trim();
        let errorBox = $('#editEmailError');
        let successBox = $('#editEmailSuccess');

        emailField.removeClass('is-invalid');
        errorBox.text('');
        successBox.hide();

        if (!emailVal) {
            showValidationError(emailField, errorBox, "{{ __('message.login_validation.email_required') }}");
            return;
        }

        let emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        if (!emailRegex.test(emailVal)) {
            showValidationError(emailField, errorBox, "{{ __('message.login_validation.email_regex') }}");
            return;
        }

      $.ajax({
          url: "{{ url('check-email/exist') }}",
          type: "POST",
          data: { _token: csrfToken, email: emailVal },
          beforeSend: function () {
              // Disable button and show loading message
              $("#editEmailFormBtn").prop("disabled", true).text("{{ __('message.sending') }}");
          },
          success: function (res) {
              if (res.success) {
                  if (res.data.email_verification_required === false) {
                      changeEmailFinal();
                  } else {
                      sendOtpToNewEmail(emailVal, csrfToken, errorBox);
                  }

                  let template = document.getElementById('otp-message').dataset.msg;
                  document.getElementById('otp-message').innerText = template.replace(':email', emailVal);

              }
              else {
                  $("#editEmailFormBtn").prop("disabled", false).text("{{ __('message.submit') }}");
              }
          },
          error: function (xhr) {
              let errorRes = xhr.responseJSON || {};
              let message = errorRes.message || "{{ __('message.something_wrong') }}";

              let alertBox = $("#emailAlertShow");

              alertBox
                  .removeClass()
                  .addClass("alert alert-danger alert-dismissible fade show")
                  .css("display", "block");

              $("#emailAlertShowMsg").text(message);
          },
      });
    });

    function showValidationError(field, errorBox, message) {
        field.addClass('is-invalid');
        errorBox.text(message);
    }

    function showServerError(errorBox, message) {
        errorBox.text(message);
    }

    function sendOtpToNewEmail(email, csrfToken, errorBox) {
        $.ajax({
            url: "{{ url('emailUpdateEditProfile') }}",
            type: "POST",
            data: { _token: csrfToken, email_to_verify: email },
            success: function (response) {
                if (response.success) {
                    $("#editEmailFormBtn").prop("disabled", false).text("{{ __('message.submit') }}");
                    $('#otpNewEmail').val(email);

                    // close email modal
                    $('#editEmailModal').modal('hide');


                    let alertOtpBox = $("#otpSuccess");
                    $('#otpSuccess').hide();

                    setTimeout(() => {
                        $('#otpVerificationModal')  .modal('show');

                        const button = document.getElementById("otpButtonn");
                        const display = document.getElementById("timerEmail");
                        startTimer(button, display, RESEND_DURATION);

                    }, 400);
                } else {
                    errorBox.text(response.message || "{{ __('message.something_wrong') }}");
                }
            },
            error: function () {
                showServerError(errorBox, "{{ __('message.something_wrong') }}");
            }
        });
    }


        $('#otpCodeNew').on('input', function () {
            let value = $(this).val();

            // Remove non-numeric characters
            value = value.replace(/\D/g, '');

            // Limit to 6 digits
            if (value.length > 6) {
                value = value.substring(0, 6);
            }

            $(this).val(value);
        });

        //Submit OTP verification and verify new email
        $('#otpVerificationForm').on('submit', function(e) {
            e.preventDefault();
            let otpValueNew = $('#otpCodeNew');
            let otpCodeValue = otpValueNew.val().trim();
            let errorBox2 = $('#otpErrorNew');
            let successBox2 = $('#otpSuccessNew');

            // Reset UI states
            otpValueNew.removeClass('is-invalid');
            errorBox2.text('');
            successBox2.hide();

            if (!otpCodeValue) {
                showValidationError(otpValueNew, errorBox2, "{{ __('message.otp_code_required') }}");
                return;
            }

            // Validation: only numbers and 6 digits
            let otpRegex = /^\d{6}$/;
            if (!otpRegex.test(otpCodeValue)) {
                showValidationError(otpValueNew, errorBox2, "{{ __('message.otp_code_invalid') }}");
                return;
            }

            let formData = {
                _token: $('input[name="_token"]').val(),
                otp: $('#otpCodeNew').val(),
                email_to_verify: $('#otpNewEmail').val()
            };

            $('#otpSuccess').hide();

            $.ajax({
                url: "{{ url('otpVerifyForNewEmail') }}",
                type: "POST",
                data: formData,
                beforeSend: function () {
                    // Disable button and show loading message
                    $("#verifyOtpBtn").prop("disabled", true).text("{{ __('message.2fa_verifying') }}");
                },
                success: function (response) {
                    if(response.success){
                    let oldEmail = "{{ auth()->user()->email }}";

                    $("#otpAlertShowMsg").text(response.message);

                        sendOtpToOldEmail(oldEmail,csrfToken,errorBox2);
                    }
                    else {
                        showValidationError(otpValueNew, errorBox2, response.message || "{{ __('message.invalid_otp_try_again') }}");
                        $("#verifyOtpBtn").prop("disabled", false).text("{{ __('message.verify') }}");
                    }
                },
                error: function (xhr) {
                    let errorRes2 = xhr.responseJSON || {};
                    let message2 = errorRes2.message || "{{ __('message.something_wrong') }}";
                    showValidationError(otpValueNew, errorBox2, message2);
                    $("#verifyOtpBtn").prop("disabled", false).text("{{ __('message.verify') }}");

                },
            });
        });

        function  sendOtpToOldEmail(oldEmail,csrfToken,errorbox2) {
            $.ajax({
                url: "{{ url('emailUpdateEditProfile') }}",
                type: "POST",
                data: {
                    _token: $('input[name="_token"]').val(),
                    email_to_verify: $('#otpOldEmail').val(),
                },
                success: function (res) {
                    if (res.success) {
                        $('#otpOldEmail').val("{{ \Auth::user()->email }}");
                        $('#otpVerificationModal').modal('hide');
                        let alertOtpBox = $("#otpSuccessOld");


                        setTimeout(() => {
                            $('#otpVerificationModalForOldEmail').modal('show');
                            const button2 = document.getElementById("resendOtpBtn");
                            const display2 = document.getElementById("timerEmailOld");
                            startTimer(button2, display2, RESEND_DURATION);

                        }, 400);

                    } else {
                        $('#otpAlertError').removeClass('d-none').text(res.message || "{{ __('message.failed_sent_otp') }}");
                    }
                },
                error: function () {
                    $('#otpAlertError').removeClass('d-none').text("{{ __('message.something_wrong') }}");
                }
            });
        }


        $('#otpCodeOld').on('input', function () {
            let value = $(this).val();

            // Remove non-numeric characters
            value = value.replace(/\D/g, '');

            // Limit to 6 digits
            if (value.length > 6) {
                value = value.substring(0, 6);
            }

            $(this).val(value);
        });

        //Submit OTP verification and verify old email
        $('#otpVerificationFormOld').on('submit', function(e) {
            e.preventDefault();
            let otpValueOld = $('#otpCodeOld');
            let otpCodeValueOld = otpValueOld.val().trim();
            let errorBox3 = $('#otpErrorOld');
            let successBox3 = $('#otpSuccessOld');

            // Reset UI states
            otpValueOld.removeClass('is-invalid');
            errorBox3.text('');
            successBox3.hide();

            if (!otpCodeValueOld) {
                showValidationError(otpValueOld, errorBox3, "{{ __('message.otp_code_required') }}");
                return;
            }

            // Validation: only numbers and 6 digits
            let otpRegex = /^\d{6}$/;
            if (!otpRegex.test(otpCodeValueOld)) {
                showValidationError(otpValueOld, errorBox3, "{{ __('message.otp_code_invalid') }}");
                return;
            }
            let formData = {
                _token: $('input[name="_token"]').val(),
                otp: $('#otpCodeOld').val(),
                email_to_verify: $('#otpOldEmail').val()
            };

            $('#otpErrorOld').text('');
            $('#otpSuccessOld').hide();

            $.ajax({
                url: "{{ url('otpVerifyForNewEmail') }}",
                type: "POST",
                data: formData,
                success: function (response) {

                    if (response.success) {
                        changeEmailFinal();
                    } else {
                        $('#otpErrorOld').text(response.message || "{{ __('message.invalid_otp_try_again') }}");
                    }
                },
                error: function (xhr) {
                    let errorRes3 = xhr.responseJSON || {};
                    let message3 = errorRes3.message || "{{ __('message.something_wrong') }}";
                    showValidationError(otpValueOld, errorBox3, message3);
                }
            });
        });

        function changeEmailFinal() {
            let emailSuccessBox = $('#editEmailSuccess');
            $.ajax({
                url: "{{ url('user/change-email') }}",
                type: "POST",
                data: {
                    _token: $('input[name="_token"]').val(),
                    newEmail: $('#newEmail').val()
                },
                success: function (res) {
                    if (res.success) {
                        $("#editEmailFormBtn").prop("disabled", false).text("{{ __('message.submit') }}");
                        $('#otpVerificationModalForOldEmail').modal('hide');
                        $('#editEmailModal').modal('hide');

                        // ✅ Update new email from DB response
                        $("#finalNewEmailDisplay").text(res.data.email);

                        // ✅ Show the hidden alert box
                        $("#emailUpdatedAlert").show();

                        // ✅ Show success modal
                        $('#emailSuccessModal').modal('show');

                    }
                },
                error: function (xhr) {
                    let errorRes = xhr.responseJSON || {};
                    let message = errorRes.message || "{{ __('message.something_wrong') }}";
                    let alertBoxOld = $("#otpSuccessOld");

                    alertBoxOld
                        .removeClass()
                        .addClass("alert alert-danger alert-dismissible fade show")
                        .css("display", "block");


                    $("#otpAlertShowMsgOld").text(message);
                }
            });
        }

        $('#otpButtonn').on('click', function (e) {
            if (this.disabled) return;
            $('#otpSuccess').hide();
            resendOTP('email','otpNewEmail','otpSuccess','otpAlertShowMsg','otpButtonn','timerEmail','verifyOtpBtn');
        });

        $('#resendOtpBtn').on('click', function (e) {
            if (this.disabled) return;
            $('#otpSuccessOld').hide();
            resendOTP('email','otpOldEmail','otpSuccessOld','otpAlertShowMsgOld','resendOtpBtn','timerEmailOld','verifyOtpBtnOld');
        });

        function resendOTP(type, inputId, alertId,msgId,btnId,timerId,verifyBtnId) {
            let emailToVerify = $("#" + inputId).val();
            let alertOtpResent = $("#" + alertId);
            let verifyBtn = $("#" + verifyBtnId);
            let msgSpan = $("#" + msgId);
            $.ajax({
                url: "{{ url('resendOtp/email-mobile') }}",
                type: "POST",
                data: {
                    _token: csrfToken,
                    type: type,
                    email_to_verify:emailToVerify,
                },
                beforeSend: function () {
                    verifyBtn.prop("disabled", true).text("{{ __('message.2fa_verifying') }}");
                },
                success: function (response) {
                    if (type === "email") {

                        alertOtpResent
                            .removeClass()
                            .addClass("alert alert-success alert-dismissible fade show")
                            .css("display", "block");

                        msgSpan.text(response.message);
                        let button = document.getElementById(btnId);
                        let display = document.getElementById(timerId);
                        if (button && display) {
                            startTimer(button, display);
                        }
                    }
                },
                error: function (xhr) {
                    let res = xhr.responseJSON || {};
                    let resMsg= res.message || "{{ __('message.something_wrong') }}";
                    alertOtpResent
                        .removeClass()
                        .addClass("alert alert-success alert-dismissible fade show")
                        .css("display", "block");

                    msgSpan.text(resMsg);
                },
                complete: function () {
                    verifyBtn.prop("disabled", false).text("{{ __('message.verify') }}");
                },
            });
        }

        const RESEND_DURATION = 60;

        function updateTimer(display, countdown) {
            display.textContent = countdown.toString().padStart(2, '0') + " seconds";
        }

        function startTimer(button, display, duration = RESEND_DURATION) {
            let countdown = duration;
            button.style.color = "gray";
            button.style.pointerEvents = "none";
            updateTimer(display, countdown);

            let interval = setInterval(() => {
                countdown--;
                if (countdown <= 0) {
                    clearInterval(interval);
                    display.textContent = "";
                    button.style.color = "#099fdc";
                    button.style.pointerEvents = "auto";
                } else {
                    updateTimer(display, countdown);
                }
            }, 1000);
        }

    });

</script>
