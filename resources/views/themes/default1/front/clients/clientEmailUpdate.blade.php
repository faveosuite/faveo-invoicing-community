
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
                            <input type="email" class="form-control" id="newEmail" name="new_email" required>
                            <span id="editEmailError" class="text-danger"></span>
                        </div>
                        <button type="submit" id="editEmailFormBtn" class="btn btn-dark">{{ __('message.submit') }}</button>
                    </form>
                    <div id="editEmailSuccess" class="text-success mt-2" style="display:none;"></div>
                </div>
            </div>
        </div>
    </div>

    <!-- OTP Verification Modal -->
    <div class="modal fade" id="otpVerificationModal" tabindex="-1" role="dialog" aria-labelledby="otpVerificationModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" style="max-width: 570px;">
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
                            <input type="text" class="form-control" id="otpCode" name="otp_code" required>
                            <input type="hidden" id="otpNewEmail" name="new_email">
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

    <!-- Confirmation Email Modal -->
    <div class="modal fade" id="confirmEmailModal" tabindex="-1" role="dialog" aria-labelledby="confirmEmailModal" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" style="max-width: 570px;">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">{{ __('Update Email Address') }}</h5>
                    <button type="button" class="btn-close closeandrefresh" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="text-center">
                        <div class="mb-4">
                            <i class="fas fa-envelope fa-3x text-primary mb-3"></i>
                            <h5 class="mb-3">{{ __('Do you need to change email address?') }}</h5>
                            <p class="text-muted">{{ __('This will require email verification for security purposes.') }}</p>
                        </div>

                        <div class="d-flex justify-content-center gap-3">
                            <button type="button" id="confirmYesBtn" class="btn btn-dark px-4">
                                <input type="hidden" id="confirmedEmail" name="confirmed_email" value="{{ \Auth::user()->email }}">                                {{ __('Yes') }}
                            </button>
                            <button type="button" id="confirmNoBtn" class="btn btn-secondary px-4" data-bs-dismiss="modal">
                                {{ __('No') }}
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>


    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>


    <script>
        $(document).ready(function() {

            // 🔹 Open edit email modal
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

                let formData = {
                    _token: $('input[name="_token"]').val(),
                    new_email: $('#newEmail').val()
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

            // 🔹 Submit OTP verification and verify email
            $('#otpVerificationForm').on('submit', function(e) {
                e.preventDefault();

                let formData = {
                    _token: $('input[name="_token"]').val(),
                    otp: $('#otpCode').val(),
                    new_email: $('#newEmail').val()
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
                            setTimeout(() => {
                                $('#otpVerificationModal').modal('hide');
                                $('#confirmEmailModal').modal('show');
                            }, 1000);
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

        });


    </script>

{{--<!-- OTP Verification Modal -->--}}
{{--    <div class="modal fade" id="otpVerificationModal" tabindex="-1" role="dialog" aria-labelledby="otpVerificationModal" aria-hidden="true">--}}
{{--        <div class="modal-dialog modal-dialog-centered" style="max-width: 570px;">--}}
{{--            <div class="modal-content">--}}
{{--                <div class="modal-header">--}}
{{--                    <h5 class="modal-title" id="editOtpModalLabel">{{ __('OTP code Verification') }}</h5>--}}
{{--                    <button type="button" class="btn-close closeandrefresh" data-bs-dismiss="modal" aria-label="Close"></button>--}}
{{--                </div>--}}
{{--                <div class="modal-body">--}}
{{--                    <form id="editEmailForm">--}}
{{--                        <div class="form-group">--}}
{{--                            <label for="newEmail">{{ __('message.emailSettings_details.email') }}</label>--}}
{{--                            <input type="email" class="form-control" id="newEmail" name="new_email" required>--}}
{{--                            <span id="editEmailError" class="text-danger"></span>--}}
{{--                        </div>--}}
{{--                        <div class="col-12 mt-4">--}}
{{--                            <div class="row">--}}
{{--                                <div class="col-6 px-0">--}}
{{--                                    <div class="mt-3 d-flex align-items-center">--}}
{{--                                        <button id="otpButtonn" type="button" onclick="resendOTP('email',null)" class="btn border-0 p-0 d-inline-flex align-items-center" style="width: 110px; white-space: nowrap;">--}}
{{--                                            <i class="fa fa-refresh mr-1"></i>Resend Email--}}
{{--                                        </button>--}}
{{--                                        <div id="timerEmail" class="ml-2" style="display: none;">00 seconds</div>--}}
{{--                                    </div>--}}
{{--                                </div>--}}
{{--                                <div class="col-6 px-0">--}}
{{--                                    <button type="button" id="emailVerifyBtn" onclick="isEmailVerified()" class="btn btn-primary btn-flat float-right btn-lg">--}}
{{--                                        <span id="emailVerifyBtnText">Verify</span>--}}
{{--                                    </button>--}}
{{--                                </div>--}}
{{--                            </div>--}}
{{--                        </div>                    </form>--}}
{{--                    <div id="editEmailSuccess" class="text-success mt-2" style="display:none;"></div>--}}
{{--                </div>--}}
{{--            </div>--}}
{{--        </div>--}}
{{--    </div>--}}

{{--<script>--}}
{{--    $(document).ready(function() {--}}
{{--        let otpTimer;--}}
{{--        let timeLeft = 300; // 5 minutes in seconds--}}
{{--        let pendingEmail = '';--}}

{{--        // Email edit modal logic--}}
{{--        $('#editEmailBtn').on('click', function() {--}}
{{--            $('#editEmailModal').modal('show');--}}
{{--            $('#editEmailError').text('');--}}
{{--            $('#editEmailSuccess').hide();--}}
{{--            $('#newEmail').val('');--}}
{{--            $('#newEmail').removeClass('is-invalid email-valid'); // Remove error styling--}}
{{--        });--}}

{{--        // Real-time email validation on input--}}
{{--        $('#newEmail').on('input blur', function() {--}}
{{--            validateEmail($(this).val());--}}
{{--        });--}}

{{--        // Email validation function--}}
{{--        function validateEmail(email) {--}}
{{--            var errorSpan = $('#editEmailError');--}}
{{--            var emailInput = $('#newEmail');--}}

{{--            // Clear previous errors--}}
{{--            errorSpan.text('');--}}
{{--            emailInput.removeClass('is-invalid email-valid');--}}

{{--            if (!email) {--}}
{{--                return false; // Empty field, don't show error until form submission--}}
{{--            }--}}

{{--            // Check email format--}}
{{--            var emailRegex = /^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/;--}}

{{--            if (!emailRegex.test(email)) {--}}
{{--                errorSpan.text("{{ __('Please enter a valid email address') }}");--}}
{{--                emailInput.addClass('is-invalid');--}}
{{--                return false;--}}
{{--            }--}}

{{--            // Additional validations--}}
{{--            if (email.length > 254) {--}}
{{--                errorSpan.text("{{ __('Email address is too long') }}");--}}
{{--                emailInput.addClass('is-invalid');--}}
{{--                return false;--}}
{{--            }--}}

{{--            // Check for consecutive dots--}}
{{--            if (email.includes('..')) {--}}
{{--                errorSpan.text("{{ __('Email address cannot contain consecutive dots') }}");--}}
{{--                emailInput.addClass('is-invalid');--}}
{{--                return false;--}}
{{--            }--}}

{{--            // Check if email starts or ends with dot--}}
{{--            if (email.startsWith('.') || email.endsWith('.')) {--}}
{{--                errorSpan.text("{{ __('Email address cannot start or end with a dot') }}");--}}
{{--                emailInput.addClass('is-invalid');--}}
{{--                return false;--}}
{{--            }--}}

{{--            // If all validations pass - add sky blue border--}}
{{--            emailInput.addClass('email-valid');--}}
{{--            return true;--}}
{{--        }--}}

{{--        // Email form submission--}}
{{--        $('#editEmailForm').on('submit', function(e) {--}}
{{--            e.preventDefault();--}}
{{--            var newEmail = $('#newEmail').val().trim();--}}
{{--            var errorSpan = $('#editEmailError');--}}
{{--            var successDiv = $('#editEmailSuccess');--}}

{{--            errorSpan.text('');--}}
{{--            successDiv.hide();--}}

{{--            // Check if email is empty--}}
{{--            if (!newEmail) {--}}
{{--                errorSpan.text("{{ __('Email address is required') }}");--}}
{{--                $('#newEmail').addClass('is-invalid').focus();--}}
{{--                return;--}}
{{--            }--}}

{{--            // Validate email format--}}
{{--            if (!validateEmail(newEmail)) {--}}
{{--                $('#newEmail').focus();--}}
{{--                return; // Stop submission if email is invalid--}}
{{--            }--}}

{{--            // Check if the new email is same as current email (optional)--}}
{{--            var currentEmail = $('#Email').val(); // Assuming current email field exists--}}
{{--            if (currentEmail && newEmail.toLowerCase() === currentEmail.toLowerCase()) {--}}
{{--                errorSpan.text("{{ __('New email address must be different from current email') }}");--}}
{{--                $('#newEmail').addClass('is-invalid').focus();--}}
{{--                return;--}}
{{--            }--}}

{{--            // If validation passes, send OTP--}}
{{--            //sendOTP(newEmail, errorSpan, successDiv);--}}
{{--            $('#editEmailFormBtn').on('click', function() {--}}
{{--                $('#otpVerificationModal').modal('show');--}}
{{--            });--}}
{{--        });--}}

{{--        // Function to send OTP--}}
{{--        function sendOTP(newEmail, errorSpan, successDiv) {--}}
{{--            $.ajax({--}}
{{--                url: '{{ url("emailUpdateEditProfie") }}', // ✅ Correct route--}}
{{--                type: 'POST',--}}
{{--                data: {--}}
{{--                    new_email: newEmail,--}}
{{--                    _token: '{{ csrf_token() }}'--}}
{{--                },--}}
{{--                beforeSend: function() {--}}
{{--                    $('#editEmailForm button[type="submit"]').prop('disabled', true).text('{{ __("Sending...") }}');--}}
{{--                    $('#newEmail').prop('disabled', true);--}}
{{--                },--}}
{{--                success: function(response) {--}}
{{--                    pendingEmail = newEmail;--}}
{{--                    $('#verificationEmail').text(newEmail);--}}
{{--                    $('#editEmailModal').modal('hide');--}}
{{--                    $('#otpVerificationModal').modal('show');--}}
{{--                    startOtpTimer();--}}
{{--                    resetOtpForm();--}}
{{--                },--}}
{{--                error: function(xhr) {--}}
{{--                    var resp = xhr.responseJSON || {};--}}
{{--                    var errorMessage = resp.message || "{{ __('Error sending verification code') }}";--}}

{{--                    if (xhr.status === 422 && resp.errors && resp.errors.new_email) {--}}
{{--                        errorMessage = resp.errors.new_email[0];--}}
{{--                    }--}}

{{--                    errorSpan.text(errorMessage);--}}
{{--                    $('#newEmail').addClass('is-invalid').focus();--}}
{{--                },--}}
{{--                complete: function() {--}}
{{--                    $('#editEmailForm button[type="submit"]').prop('disabled', false).text('{{ __("message.submit") }}');--}}
{{--                    $('#newEmail').prop('disabled', false);--}}
{{--                }--}}
{{--            });--}}
{{--        }--}}

{{--        // OTP verification form submission--}}
{{--        $('#otpVerificationForm').on('submit', function(e) {--}}
{{--            e.preventDefault();--}}
{{--            var otpCode = $('#otpCode').val();--}}
{{--            var errorSpan = $('#otpError');--}}
{{--            var successDiv = $('#otpSuccess');--}}

{{--            errorSpan.text('');--}}
{{--            successDiv.hide();--}}

{{--            if (!otpCode || otpCode.length !== 6) {--}}
{{--                errorSpan.text("{{ __('Please enter a valid 6-digit code') }}");--}}
{{--                return;--}}
{{--            }--}}

{{--            // Verify OTP and update email--}}
{{--            $.ajax({--}}
{{--                url: '{{url('my-profile/verify-email-otp')}}', // New route for verifying OTP--}}
{{--                type: 'POST',--}}
{{--                data: {--}}
{{--                    new_email: pendingEmail,--}}
{{--                    otp_code: otpCode,--}}
{{--                    _token: '{{ csrf_token() }}'--}}
{{--                },--}}
{{--                beforeSend: function() {--}}
{{--                    $('#verifyOtpBtn').prop('disabled', true).text('{{ __("Verifying...") }}');--}}
{{--                },--}}
{{--                success: function(response) {--}}
{{--                    clearInterval(otpTimer);--}}
{{--                    successDiv.html('<i class="fas fa-check-circle"></i> ' + (response.message || "{{ __('Email updated successfully') }}")).show();--}}
{{--                    $('#Email').val(pendingEmail); // Update the main email field--}}

{{--                    setTimeout(function() {--}}
{{--                        $('#otpVerificationModal').modal('hide');--}}
{{--                        location.reload(); // Refresh page to reflect changes--}}
{{--                    }, 2000);--}}
{{--                },--}}
{{--                error: function(xhr) {--}}
{{--                    var resp = xhr.responseJSON || {};--}}
{{--                    errorSpan.text(resp.message || "{{ __('Invalid verification code') }}");--}}
{{--                    $('#verifyOtpBtn').prop('disabled', false).text('{{ __("Verify Email") }}');--}}
{{--                }--}}
{{--            });--}}
{{--        });--}}

{{--        // Resend OTP--}}
{{--        $('#resendOtpBtn').on('click', function() {--}}
{{--            $.ajax({--}}
{{--                url: '{{url('my-profile/send-email-otp')}}',--}}
{{--                type: 'POST',--}}
{{--                data: {--}}
{{--                    new_email: pendingEmail,--}}
{{--                    _token: '{{ csrf_token() }}'--}}
{{--                },--}}
{{--                beforeSend: function() {--}}
{{--                    $('#resendOtpBtn').prop('disabled', true).text('{{ __("Sending...") }}');--}}
{{--                },--}}
{{--                success: function(response) {--}}
{{--                    timeLeft = 300; // Reset timer--}}
{{--                    startOtpTimer();--}}
{{--                    $('#otpError').text('').removeClass('text-danger').addClass('text-success')--}}
{{--                        .text('{{ __("Verification code sent successfully") }}');--}}

{{--                    setTimeout(function() {--}}
{{--                        $('#otpError').text('').removeClass('text-success');--}}
{{--                    }, 3000);--}}
{{--                },--}}
{{--                error: function(xhr) {--}}
{{--                    var resp = xhr.responseJSON || {};--}}
{{--                    $('#otpError').text(resp.message || "{{ __('Error sending verification code') }}");--}}
{{--                    $('#resendOtpBtn').prop('disabled', false).text('{{ __("Resend Code") }}');--}}
{{--                }--}}
{{--            });--}}
{{--        });--}}

{{--        // Back to email button--}}
{{--        $('#backToEmailBtn').on('click', function() {--}}
{{--            clearInterval(otpTimer);--}}
{{--            $('#otpVerificationModal').modal('hide');--}}
{{--            $('#editEmailModal').modal('show');--}}
{{--        });--}}

{{--        // OTP input formatting--}}
{{--        $('#otpCode').on('input', function() {--}}
{{--            this.value = this.value.replace(/[^0-9]/g, '');--}}
{{--            $('#otpError').text('');--}}
{{--        });--}}

{{--        // Start OTP timer--}}
{{--        function startOtpTimer() {--}}
{{--            timeLeft = 300; // 5 minutes--}}
{{--            $('#resendOtpBtn').prop('disabled', true);--}}

{{--            otpTimer = setInterval(function() {--}}
{{--                timeLeft--;--}}
{{--                var minutes = Math.floor(timeLeft / 60);--}}
{{--                var seconds = timeLeft % 60;--}}

{{--                $('#otpTimer').text(--}}
{{--                    (minutes < 10 ? '0' : '') + minutes + ':' +--}}
{{--                    (seconds < 10 ? '0' : '') + seconds--}}
{{--                );--}}

{{--                if (timeLeft <= 0) {--}}
{{--                    clearInterval(otpTimer);--}}
{{--                    $('#otpTimer').text('00:00');--}}
{{--                    $('#resendOtpBtn').prop('disabled', false);--}}
{{--                }--}}
{{--            }, 1000);--}}
{{--        }--}}

{{--        // Reset OTP form--}}
{{--        function resetOtpForm() {--}}
{{--            $('#otpCode').val('');--}}
{{--            $('#otpError').text('');--}}
{{--            $('#otpSuccess').hide();--}}
{{--            $('#verifyOtpBtn').prop('disabled', false).text('{{ __("Verify Email") }}');--}}
{{--        }--}}

{{--        // Clear validation when modal is hidden--}}
{{--        $('#editEmailModal').on('hidden.bs.modal', function() {--}}
{{--            $('#editEmailError').text('');--}}
{{--            $('#editEmailSuccess').hide();--}}
{{--            $('#newEmail').val('').removeClass('is-invalid email-valid');--}}
{{--        });--}}

{{--        // Clean up timer when modals are closed--}}
{{--        $('#otpVerificationModal').on('hidden.bs.modal', function() {--}}
{{--            clearInterval(otpTimer);--}}
{{--            resetOtpForm();--}}
{{--        });--}}
{{--    });--}}
{{--</script>--}}

<!-- Email Change Confirmation Modal -->
<div class="modal fade" id="editEmailModal" tabindex="-1" role="dialog" aria-labelledby="editEmailModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" style="max-width: 570px;">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">{{ __('Update Email Address') }}</h5>
                <button type="button" class="btn-close closeandrefresh" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

        </div>
    </div>
</div>