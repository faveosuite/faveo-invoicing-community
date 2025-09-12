
<!-- Edit Mobile Modal -->
<div class="modal fade" id="editMobileModal" tabindex="-1" role="dialog" aria-labelledby="editMobileModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" style="max-width: 570px;">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">{{ __('Update Mobile Number') }}</h5>
                <button type="button" class="btn-close closeandrefresh" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div id="mobileAlertShow" class="alert alert-danger alert-dismissible" role="alert" style="display: none">
                    <span id="mobileAlertShowMsg"></span>
                    <button type="button" class="btn-close"
                            data-bs-dismiss="alert"
                            aria-label="Close">
                    </button>
                </div>
                <form id="editMobileForm">
                    @csrf
                    <div class="form-group mb-3">
                        <label for="newMobile">{{ __('message.mobileSettings_details.mobile') }}</label>
                        <input type="text" class="form-control" id="newMobile" name="mobile_to_verify">
                        <span id="editMobileError" class="text-danger"></span>
                    </div>
                    <button type="submit" id="editMobileFormBtn" class="btn btn-dark">{{ __('message.submit') }}</button>
                </form>
                <div id="editMobileSuccess" class="text-success mt-2" style="display:none;"></div>
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
                <div id="otpSuccess" class="alert alert-danger alert-dismissible " role="alert" style="display: none">
                    <span id="otpAlertShowMsg"></span>
                    <button type="button" class="btn-close"
                            data-bs-dismiss="alert"
                            aria-label="Close">
                    </button>
                </div>

                <form id="otpVerificationForm">
                    @csrf
                    <div class="form-group mb-3">
                        <label for="otpCode">{{ __('Enter OTP Code') }}</label>
                        <input type="text" class="form-control" id="otpCodeNew" name="otp_code">
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
                <h5 class="modal-title">{{ __('OTP Code Verification') }}</h5>
                <button type="button" class="btn-close closeandrefresh" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div id="otpAlertSuccess" class="alert alert-success"></div>

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
{{--                <div id="otpSuccess" class="text-success mt-2" style="display:none;"></div>--}}
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

</script>