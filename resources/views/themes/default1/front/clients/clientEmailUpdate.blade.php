
<!-- Edit Email Modal -->
<div class="modal fade" id="editEmailModal" tabindex="-1" role="dialog" aria-labelledby="editEmailModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" style="max-width: 570px;">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">{{ __('message.update_email_address') }}</h4>
                <button type="button" class="btn-close closeandrefresh" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div id="emailAlertShow" class="alert alert-danger alert-dismissible " role="alert" style="display: none">
                    <span id="emailAlertShowMsg"></span>
                </div>
                <form id="editEmailForm">
                    @csrf
                    <div class="form-group mb-3">
                        <label for="newEmail">{{ __('message.enter_new_email') }}</label>
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
                <h4 class="modal-title">{{ __('message.otp_code_verification') }}</h4>
                <button type="button" class="btn-close closeandrefresh" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="alert alert-warning" id="otpInfo" role="alert">
                    <i class="fa fa-info-circle me-2"></i>
                    <span id="otp-message" data-msg="{{ __('message.otp_sent_new_email') }}">
                    </span>
                </div>
                <div id="otpSuccess" class="" role="alert" style="display:block; padding: 10px 12px; font-size: 14px;">
                    <span id="otpAlertShowMsg"></span>
                </div>

                <form id="otpVerificationForm">
                    @csrf
                    <div class="form-group mb-3">
                        <label for="otpCode">{{ __('message.enter_otp_code') }}</label>
                        <input type="text" class="form-control" id="otpCodeNew" name="otp_code" maxlength="6">
                        <input type="hidden" id="otpNewEmail" name="email_to_verify">
                        <span id="otpErrorNew" class="invalid-feedback mb-2"></span>
                    </div>
                    <div class="col-12 mt-4">
                        <div class="row">
                            <div class="col-6 px-0 d-flex align-items-center fs-6">
                                <button id="otpButtonn" type="button"
                                        class="btn btn-link p-0 fs-6"
                                        style="color: gray; pointer-events: none; text-decoration: none;">
                                    <i class="fa fa-refresh"></i>
                                    {{ __('message.resend_otp') }}
                                </button>
                                <div id="timerEmail" class="ms-1"></div>
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
                <h4 class="modal-title">{{ __('message.otp_code_verification') }}</h4>
                <button type="button" class="btn-close closeandrefresh" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="alert alert-warning" id="otpInfo" role="alert">
                    <i class="fa fa-info-circle me-2"></i>
                    <span>{!! __('message.otp_sent_to_old_email', ['email' => e($user->email)]) !!}</span>
                </div>
                <div id="otpSuccessOld" class="alert d-none " role="alert" style="display:block; padding: 10px 12px; font-size: 14px;">
                    <span id="otpAlertShowMsgOld"></span>
                </div>

                <form id="otpVerificationFormOld">
                    @csrf
                    <div class="footpVerificationFormOldrm-group mb-3">
                        <label for="otpCode">{{ __('message.enter_otp_code') }}</label>
                        <input type="text" class="form-control" id="otpCodeOld" name="otp_code">
                        <input type="hidden" id="otpOldEmail" name="email_to_verify" value="{{ $user->email }}">
                        <span id="otpErrorOld" class="invalid-feedback mb-2"></span>
                    </div>
                    <div class="col-12 mt-4">
                        <div class="row">
                            <div class="col-6 px-0 d-flex align-items-center">
                                <button id="resendOtpBtn" type="button"
                                        class="btn btn-link p-0 fs-6"
                                        style="color: gray; pointer-events: none; text-decoration: none;">
                                    <i class="fa fa-refresh"></i>
                                    {{ __('message.resend_otp') }}
                                </button>
                                <div id="timerEmailOld" class="ms-1"></div>
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
            <div class="modal-body text-center py-4">
                <div class="mb-3">
                    <i class="fas fa-check-circle text-success" style="font-size: 4rem;"></i>
                </div>
                <h5 class="text-success mb-3">{{ __('message.email_updated_successfully') }}</h5>
                <p class="mb-1 text-muted">{{ __('message.your_email_changed_successfully') }}</p>
                <strong id="finalNewEmailDisplay" class="d-block mt-2 text-dark"></strong>
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

        const csrfToken = $('input[name="_token"]').val();

        function handleTooManyAttempts(error) {
            if (error.status === 429) {
                setTimeout(function () {
                    location.reload();
                }, 5000);
            }
        }

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
          url: "{{ url('profile/email/send-otp') }}",
          type: "POST",
          data: { _token: csrfToken, email_to_verify: emailVal, new_email: emailVal },
          beforeSend: function () {
              // Disable button and show loading message
              $("#editEmailFormBtn").prop("disabled", true).text("{{ __('message.sending') }}");
          },
          success: function (res) {
              if (res.success) {
                  if (res.data && res.data.email_updated) {
                      // Email updated directly (no verification needed)
                      $("#editEmailFormBtn").prop("disabled", false).text("{{ __('message.submit') }}");
                      $('#editEmailModal').modal('hide');
                      $("#finalNewEmailDisplay").text(res.data.email);
                      $('#emailSuccessModal').modal('show');
                  } else {
                      // Verification required - OTP sent to old email
                      $('#otpNewEmail').val(emailVal);
                      $('#editEmailModal').modal('hide');
                      $("#editEmailFormBtn").prop("disabled", false).text("{{ __('message.submit') }}");
                      setTimeout(() => {
                          $('#otpVerificationModalForOldEmail').modal('show');
                          const button2 = document.getElementById("resendOtpBtn");
                          const display2 = document.getElementById("timerEmailOld");
                          startTimer(button2, display2, RESEND_DURATION);
                      }, 400);
                  }

                  let template = document.getElementById('otp-message').dataset.msg;
                  let safeEmail = $('<div>').text(emailVal).html();
                  let rendered = template.replace(':email', `<b>${safeEmail}</b>`);
                  $('#otp-message').html(rendered);
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
              autoHidePopup(alertBox, 5000);
              handleTooManyAttempts(xhr);

              $("#editEmailFormBtn").prop("disabled", false).text("{{ __('message.submit') }}");

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
            url: "{{ url('profile/email/send-otp') }}",
            type: "POST",
            data: { _token: csrfToken, email_to_verify: email },
            success: function (response, textStatus, jqXHR) {
                const statusCodeForEmail = jqXHR.status;

                $("#otpNewEmail").val(email);

                // Close old email modal
                $("#otpVerificationModalForOldEmail").modal("hide");

                setTimeout(() => {
                    $("#otpVerificationModal").modal("show");

                    // Show success message for status 208 (already sent within 10 minutes)
                    if (statusCodeForEmail === 208) {
                        $('#otpSuccess')
                            .removeClass()
                            .addClass('alert alert-success alert-dismissible')
                            .show();
                        $('#otpAlertShowMsg').text(response.message);
                        autoHidePopup('#otpSuccess', 5000);
                    }

                    // Start resend timer
                    const button = document.getElementById("otpButtonn");
                    const display = document.getElementById("timerEmail");
                    startTimer(button, display, RESEND_DURATION);

                }, 400);
            },
            error: function (xhr) {
                let errorRes = xhr.responseJSON || {};
                let message = errorRes.message || "{{ __('message.something_wrong') }}";

                let alertBox = $("#otpSuccessOld");
                alertBox
                    .removeClass()
                    .addClass("alert alert-danger alert-dismissible fade show")
                    .css("display", "block");
                $("#otpAlertShowMsgOld").text(message);
                autoHidePopup(alertBox, 5000);
                handleTooManyAttempts(xhr);

                $("#verifyOtpBtnOld").prop("disabled", false).text("{{ __('message.verify') }}");
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

            // Reset UI states
            otpValueNew.removeClass('is-invalid');
            errorBox2.text('');

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
                email_to_verify: $('#otpNewEmail').val(),
                verify_type: 'new_email',
            };

            $('#otpSuccess').hide();

            $.ajax({
                url: "{{ url('profile/email/verify-otp') }}",
                type: "POST",
                data: formData,
                beforeSend: function () {
                    // Disable button and show loading message
                    $("#verifyOtpBtn").prop("disabled", true).text("{{ __('message.2fa_verifying') }}");
                },
                success: function (response) {
                    if(response.success){
                        if (response.data && response.data.email_updated) {
                            $("#editEmailFormBtn").prop("disabled", false).text("{{ __('message.submit') }}");
                            $('#otpVerificationModal').modal('hide');
                            $('#editEmailModal').modal('hide');
                            $("#finalNewEmailDisplay").text(response.data.email);
                            $('#emailSuccessModal').modal('show');
                        }
                    }
                    else {
                        showValidationError(otpValueNew, errorBox2, response.message || "{{ __('message.invalid_otp_try_again') }}");
                    }
                },
                error: function (xhr) {
                    let statusCode = xhr.status;
                    let errorRes2 = xhr.responseJSON || {};
                    let message2 = errorRes2.message || "{{ __('message.something_wrong') }}";

                    if (statusCode === 429) {
                        $('#otpSuccess').removeClass().addClass('alert alert-danger alert-dismissible').show();
                        $('#otpAlertShowMsg').text(message2);
                       autoHidePopup('#otpSuccess', 5000);
                    } else {
                        showValidationError(otpValueNew, errorBox2, message2);
                    }
                    handleTooManyAttempts(xhr);
                },
                complete: function () {
                    $("#verifyOtpBtn").prop("disabled", false).text("{{ __('message.verify') }}");
                },
            });
        });


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
                email_to_verify: $('#otpOldEmail').val(),
                verify_type: 'old_email',
            };

            $('#otpErrorOld').text('');
            $('#otpSuccessOld').hide();

            $.ajax({
                url: "{{ url('profile/email/verify-otp') }}",
                type: "POST",
                data: formData,
                success: function (response) {
                    if (response.success) {
                        let newEmail = $('#otpNewEmail').val();
                        sendOtpToNewEmail(newEmail, csrfToken, errorBox3);
                    } else {
                        $('#otpErrorOld').text(response.message || "{{ __('message.invalid_otp_try_again') }}");
                    }
                },
                error: function (xhr) {
                    let statusCodeold = xhr.status;
                    let errorRes3 = xhr.responseJSON || {};
                    let message3 = errorRes3.message || "{{ __('message.something_wrong') }}";

                    if (statusCodeold === 429) {
                        $('#otpSuccessOld').removeClass().addClass('alert alert-danger alert-dismissible').show();
                        $('#otpAlertShowMsgOld').text(message3);
                        autoHidePopup('#otpSuccessOld', 5000);
                    } else {
                        showValidationError(otpValueOld, errorBox3, message3);
                    }
                    handleTooManyAttempts(xhr);
                }
            });
        });

        $('#otpButtonn').on('click', function () {
            if (this.disabled) return;
            $('#otpSuccess').hide();

            let btn = $(this);
            btn.data("original-html", btn.html()); // store HTML including icon
            btn.prop("disabled", true);

            resendOTP('email','otpNewEmail','otpSuccess','otpAlertShowMsg','otpButtonn','timerEmail','verifyOtpBtn');
        });

        $('#resendOtpBtn').on('click', function () {
            if (this.disabled) return;
            $('#otpSuccessOld').hide();

            let btn = $(this);
            btn.data("original-html", btn.html());
            btn.prop("disabled", true);

            resendOTP('email','otpOldEmail','otpSuccessOld','otpAlertShowMsgOld','resendOtpBtn','timerEmailOld','verifyOtpBtnOld');
        });

        function resendOTP(type, inputId, alertId,msgId,btnId,timerId,verifyBtnId) {
            let emailToVerify = $("#" + inputId).val();
            let alertOtpResent = $("#" + alertId);
            let verifyBtn = $("#" + verifyBtnId);
            let msgSpan = $("#" + msgId);
            $.ajax({
                url: "{{ url('profile/resend-otp') }}",
                type: "POST",
                data: {
                    _token: csrfToken,
                    type: type,
                    email_to_verify:emailToVerify,
                },
                beforeSend: function () {
                    verifyBtn.prop("disabled", true);
                    let resendBtn = $("#" + btnId);
                    resendBtn[0].dataset.originalHtml = resendBtn.html();
                    resendBtn.html('<i class="fa fa-refresh fa-spin"></i> {{ __("message.resending") }}...');
                    resendBtn.prop("disabled", true);
                },
                success: function (response) {
                    if (type === "email") {

                        alertOtpResent
                            .removeClass()
                            .addClass("alert alert-success alert-dismissible fade show")
                            .css("display", "block");

                        msgSpan.text(response.message);
                        autoHidePopup(alertOtpResent, 5000);

                        let resendBtn2 = $("#" + btnId);
                        let display = document.getElementById(timerId);

                        // Restore "Resend OTP" text (original)
                        resendBtn2.html(resendBtn2.data("original-html"));
                        resendBtn2.prop('disabled', true);

                        // Start timer (existing functionality)
                        if (resendBtn2.length && display) {
                            startTimer(resendBtn2[0], display);
                        }
                    }
                },
                error: function (xhr) {
                    let res = xhr.responseJSON || {};
                    let resMsg= res.message || "{{ __('message.something_wrong') }}";
                    alertOtpResent
                        .removeClass()
                        .addClass("alert alert-danger alert-dismissible fade show")
                        .css("display", "block");

                    msgSpan.text(resMsg);
                    autoHidePopup(alertOtpResent, 5000);
                    handleTooManyAttempts(xhr);

                    let resendBtn = $("#" + btnId);
                    resendBtn.html(resendBtn[0].dataset.originalHtml || resendBtn.data("original-html"));
                    resendBtn.prop("disabled", false);
                    resendBtn[0].style.color = "#099fdc";
                    resendBtn[0].style.pointerEvents = "auto";
                },
                complete: function () {
                    verifyBtn.prop("disabled", false).text("{{ __('message.verify') }}");
                },
            });
        }

        const RESEND_DURATION = 120;

        function updateTimer(display, countdown) {
            display.textContent = countdown.toString().padStart(2, '0') + " seconds";
        }

        function startTimer(button, display, duration = RESEND_DURATION) {
            let countdown = duration;

            if (button && button.jquery) button = button[0];
            if (display && display.jquery) display = display[0];

            // Disable button at start
            button.disabled = true;
            button.style.color = "gray";
            button.style.pointerEvents = "none";
            updateTimer(display, countdown);

            let interval = setInterval(() => {
                countdown--;
                if (countdown <= 0) {
                    clearInterval(interval);
                    display.textContent = "";

                    // Restore visual state
                    button.style.color = "#099fdc";
                    button.style.pointerEvents = "auto";

                    button.disabled = false;

                } else {
                    updateTimer(display, countdown);
                }
            }, 1000);
        }

        function autoHidePopup(selectorOrElement, duration = 5000) {
            const popup = typeof selectorOrElement === 'string'
                ? $(selectorOrElement)
                : selectorOrElement;

            if (!popup.length) return;

            setTimeout(() => {
                popup.fadeOut('slow', function () {
                    popup.find('span').text('');
                });
            }, duration);
        }

    });

</script>
