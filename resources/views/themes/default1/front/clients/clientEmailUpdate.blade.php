
<!-- Edit Email Modal -->
<div class="modal fade" id="editEmailModal" tabindex="-1" role="dialog" aria-labelledby="editEmailModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" style="max-width: 570px;">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">{{ __('Update Email Address') }}</h5>
                <button type="button" class="btn-close closeandrefresh" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="editEmailForm">
                    @csrf
                    <div class="form-group mb-3">
                        <label for="newEmail">{{ __('message.emailSettings_details.email') }}</label>
                        <input type="text" class="form-control" id="newEmail" name="email_to_verify">
                        <span id="editEmailError" class="text-danger"></span>
                    </div>
                    <button type="submit" id="editEmailFormBtn" class="btn btn-dark">{{ __('message.submit') }}</button>
                </form>
                <div id="editEmailSuccess" class="text-success mt-2" style="display:none;"></div>
            </div>
        </div>
    </div>
</div>

<!-- OTP Verification Modal for New Email -->
<div class="modal fade" id="otpVerificationModal" tabindex="-1" role="dialog" aria-labelledby="otpVerificationModalLabel" aria-hidden="true">
    {{--    <div class="modal fade" id="editEmailModal" tabindex="-1" role="dialog" aria-labelledby="otpVerificationModalLabel" aria-hidden="true">--}}
    <div class="modal-dialog modal-dialog-centered" style="max-width: 570px;">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">{{ __('OTP Code Verification') }}</h5>
                <button type="button" class="btn-close closeandrefresh" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="otpVerificationForm">
                    @csrf
                    <div class="alert alert-danger" role="alert">
                        This is a danger alert—check it out!
                    </div>
                    <div class="form-group mb-3">
                        <label for="otpCode">{{ __('Enter OTP Code') }}</label>
                        <input type="text" class="form-control" id="otpCodeNew" name="otp_code" required>
                        <input type="hidden" id="otpNewEmail" name="email_to_verify">
                        <span id="otpError" class="text-danger"></span>
                    </div>
                    <div class="col-12 mt-4">
                        <div class="row">
                            <div class="col-6 px-0">
                                <button id="otpButtonn" type="button" onclick="resendOTP('email', null)"
                                        class="btn border-0 p-0 d-inline-flex align-items-center"
                                        style="width: 110px; white-space: nowrap;">
                                    <i class="fa fa-refresh mr-1"></i>{{ __('message.resend_email') }}
                                </button>
                                <div id="timerEmail" class="ml-2"></div>
                            </div>
                            <div class="col-6 px-0 text-end">
                                <button type="submit" id="verifyOtpBtn"  class="btn btn-primary btn-lg">
                                    <span id="emailVerifyBtnText">Verify</span>
                                </button>
                            </div>
                        </div>
                    </div>
                </form>
                <div id="otpSuccess" class="text-success mt-2" style="display:none;"></div>
            </div>
        </div>
    </div>
</div>


<!-- OTP Verification Modal for Old Email -->
<div class="modal fade" id="otpVerificationModalForOldEmail" tabindex="-1" role="dialog" aria-labelledby="otpVerificationModalForOldEmail" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" style="max-width: 570px;">
        <div id="otpAlertSuccess" class="alert alert-success d-none"></div>
        <div id="otpAlertError" class="alert alert-danger d-none"></div>
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">{{ __('OTP Code Verification') }}</h5>
                <button type="button" class="btn-close closeandrefresh" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="otpVerificationForm">
                    @csrf
                    <div class="form-group mb-3">
                        <label for="otpCode">{{ __('Enter OTP Code') }}</label>
                        <input type="text" class="form-control" id="otpCodeOld" name="otp_code" required>
                        <input type="hidden" id="otpOldEmail" name="email_to_verify">
                        <span id="otpError" class="text-danger"></span>
                    </div>
                    <div class="col-12 mt-4">
                        <div class="row">
                            <div class="col-6 px-0">
                                <button id="resendOtpBtn" type="button" onclick="resendOTP('email',null)" class="btn border-0 p-0 d-inline-flex align-items-center">
                                    <i class="fa fa-refresh mr-1"></i> Resend Email
                                </button>
                                <div id="timerEmail" class="ml-2" style="display: none;">00 seconds</div>
                            </div>
                            <div class="col-6 px-0 text-end">
                                <button type="submit" id="verifyOtpBtn"  class="btn btn-primary btn-lg">
                                    <span id="emailVerifyBtnText">Verify</span>
                                </button>
                            </div>
                        </div>
                    </div>
                </form>
                <div id="otpSuccess" class="text-success mt-2" style="display:none;"></div>
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
</style>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>


<script>
    $(document).ready(function() {

        // const otpButton = document.getElementById("otpButton");
        // const additionalButton = document.getElementById("additionalButton");
        const timerDisplay = document.getElementById("timer");
        const emailOtpButton = document.getElementById("otpButtonn");
        const emailTimerDisplay = document.getElementById("timerEmail");
        let timerInterval;
        let emailTimerInterval;

        const TIMER_DURATION = 120;
        let emailCountdown = TIMER_DURATION;

        // Email edit modal logic
        $('#editEmailBtn').on('click', function() {
            $('#editEmailModal').modal('show');
            $('#editEmailError').text('');
            $('#editEmailSuccess').hide();
            $('#newEmail').val('');
            $('#newEmail').removeClass('is-invalid'); // Remove error styling
        });

        // 🔹 Submit email update
        $('#editEmailForm').on('submit', function(e) {
            e.preventDefault();

            let emailField = $('#newEmail');
            let emailVal = emailField.val().trim();
            let errorBox = $('#editEmailError');
            let successBox = $('#editEmailSuccess');
            let emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;

            emailField.removeClass('is-invalid');
            errorBox.text('');
            successBox.hide();

            // Validation: Empty
            if (!emailVal) {
                emailField.addClass('is-invalid');
                errorBox.text("Email is required.");
                return;
            }

            // Validation: Regex
            if (!emailRegex.test(emailVal)) {
                emailField.addClass('is-invalid');
                errorBox.text("Invalid email format.");
                return;
            }

            let formData = {
                _token: $('input[name="_token"]').val(),
                email_to_verify: emailVal
            };

            $('#editEmailError').text('');
            $('#editEmailSuccess').hide();

            $.ajax({
                url: "{{ url('emailUpdateEditProfile') }}",
                type: "POST",
                data: formData,
                success: function (response) {

                    if (response.success) {
                        console.log("Inside success 1");
                        console.log(response);

                        $('#otpNewEmail').val($('#newEmail').val());

                        $('#editEmailSuccess').text(response.message).show();

                        $('#editEmailModal').modal('hide');

                        setTimeout(() => {
                            console.log("perumal");
                            $('#otpVerificationModal').modal('show');
                        }, 500);
                    }
                    else {
                        $('#editEmailError').text(response.message || "Something went wrong!");
                    }
                },
                error: function (xhr) {
                    if (xhr.responseJSON && xhr.responseJSON.errors) {
                        $('#editEmailError').text(xhr.responseJSON.errors.new_email[0]);
                    } else {
                        $('#editEmailError').text("{{ __('Server error, please try again!') }}");
                    }
                }
            });
        });

        //Submit OTP verification and verify new email
        $('#otpVerificationForm').on('submit', function(e) {
            e.preventDefault();

            let formData = {
                _token: $('input[name="_token"]').val(),
                otp: $('#otpCodeNew').val(),
                email_to_verify: $('#otpNewEmail').val()
            };

            $('#otpError').text('');
            $('#otpSuccess').hide();

            $.ajax({
                url: "{{ url('otpVerifyForNewEmail') }}",
                type: "POST",
                data: formData,
                success: function (response) {
                    console.log("OTP verification success", response);

                    if (response.success) {
                        $('#otpSuccess').text(response.message).show();
                        $('#otpVerificationModal').modal('hide');

                        // ✅ Directly call emailUpdateEditProfile for old email verification
                        $.ajax({
                            url: "{{ url('emailUpdateEditProfile') }}",
                            type: "POST",
                            data: {
                                _token: $('input[name="_token"]').val(),
                                email_to_verify: "{{ \Auth::user()->email }}" // old email
                            },
                            success: function (res) {
                                if (res.success) {
                                    $('#otpOldEmail').val("{{ \Auth::user()->email }}");
                                    $('#otpVerificationModalForOldEmail').modal('show');
                                    $('#otpAlertSuccess').removeClass('d-none').text(res.message);
                                } else {
                                    $('#otpAlertError').removeClass('d-none').text(res.message || "Failed to send OTP.");
                                }
                            },
                            error: function () {
                                $('#otpAlertError').removeClass('d-none').text("{{ __('Server error, please try again!') }}");
                            }
                        });
                    } else {
                        $('#otpError').text(response.message || "Invalid OTP, please try again.");
                    }
                },
                error: function (xhr) {
                    if (xhr.responseJSON && xhr.responseJSON.errors) {
                        if (xhr.responseJSON.errors.otp) {
                            $('#otpError').text(xhr.responseJSON.errors.otp[0]);
                        } else if (xhr.responseJSON.errors.new_email) {
                            $('#otpError').text(xhr.responseJSON.errors.new_email[0]);
                        }
                    } else {
                        $('#otpError').text("{{ __('Server error, please try again!') }}");
                    }
                }
            });
        });

        $('#confirmYesBtn').on('click', function () {
            let confirmedEmail = $('#confirmedEmail').val();

            $.ajax({
                url: "{{ url('emailUpdateEditProfile') }}",
                type: "POST",
                data: {
                    _token: $('input[name="_token"]').val(),
                    email_to_verify: confirmedEmail
                },
                success: function (response) {
                    if (response.success) {
                        $('#otpOldEmail').val(confirmedEmail); // pass email to hidden field
                        $('#confirmEmailModal').modal('hide');
                        $('#otpVerificationModalForOldEmail').modal('show');
                        $('#otpAlertSuccess').removeClass('d-none').text(response.message);
                    } else {
                        $('#otpAlertError').removeClass('d-none').text(response.message || "Failed to send OTP.");
                    }
                },
                error: function () {
                    $('#otpAlertError').removeClass('d-none').text("{{ __('Server error, please try again!') }}");
                }
            });
        });

        //Submit OTP verification and verify old email
        $('#otpVerificationModalForOldEmail').on('submit', function(e) {
            e.preventDefault();

            let formData = {
                _token: $('input[name="_token"]').val(),
                otp: $('#otpCodeOld').val(),
                email_to_verify: $('#otpOldEmail').val()
            };

            $('#otpError').text('');
            $('#otpSuccess').hide();

            $.ajax({
                url: "{{ url('otpVerifyForNewEmail') }}",
                type: "POST",
                data: formData,
                success: function (response) {
                    console.log("OTP verification success", response);

                    if (response.success) {
                        $('#otpSuccess').text(response.message).show();
                        changeEmailFinal();
                        $('#otpVerificationModalForOldEmail').modal('hide');


                    } else {
                        $('#otpError').text(response.message || "Invalid OTP, please try again.");
                    }
                },
                error: function (xhr) {
                    if (xhr.responseJSON && xhr.responseJSON.errors) {
                        if (xhr.responseJSON.errors.otp) {
                            $('#otpError').text(xhr.responseJSON.errors.otp[0]);
                        } else if (xhr.responseJSON.errors.new_email) {
                            $('#otpError').text(xhr.responseJSON.errors.new_email[0]);
                        }
                    } else {
                        $('#otpError').text("{{ __('Server error, please try again!') }}");
                    }
                }
            });
        });

        function changeEmailFinal() {
            $.ajax({
                url: "{{ url('user/change-email') }}",
                type: "POST",
                data: {
                    _token: $('input[name="_token"]').val(),
                    newEmail: $('#newEmail').val()
                },
                success: function (res) {
                    if (res.success) {
                        alert("Email changed successfully!");
                        location.reload();
                    } else {
                        alert(res.message || "Email update failed.");
                    }
                }
            });
        }


        function updateTimer(display, countdown) {
            display.textContent = countdown.toString().padStart(2, '0') + " seconds";
        }

        function startTimer(button, display, duration, type = 'mobile') {
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

        async function resendOTP(default_type, type) {
            let data = {
                _token: $('input[name="_token"]').val(),
                default_type: default_type   // ✅ fixed (backend expects this key)
            };

            $.ajax({
                url: '{{ url('resend-otp/profile-update') }}',
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

    });
</script>