<div class="modal fade" id="twoFactorPopupModalUser" tabindex="-1" aria-labelledby="twoFactorLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" style="max-width: 570px;">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title" id="twoFactorLabel">{{ __('message.two_factor') }}</h4>
                <button type="button" class="btn-close closeandrefresh" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <div class="modal-body">

                <div class="mb-3">
                    <label for="google2fa_code" class="form-label"> <strong>{{ __('message.enter_auth_code') }}</strong></label>
                    <input type="text" id="google2fa_code" class="form-control" maxlength="6" placeholder="XXXXXX">
                    <div id="error-message" class="invalid-feedback"></div>
                    <span class=" font-italic">{{ __('message.2fapopup_content') }}</span>
                </div>

            </div>

            <div class="modal-footer justify-content-between">
                <button type="button" class="btn btn-default pull-left closebutton" data-bs-dismiss="modal" id="cancel2FAPopup">{{ __('message.cancel') }}</button>
                <button type="button" id="verify2FAButton" class="btn btn-primary">{{ __('message.verify') }}</button>
            </div>
        </div>
    </div>
</div>