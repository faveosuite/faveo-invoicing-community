<div class="modal fade" id="twoFactorPopupModalAdmin" tabindex="-1" role="dialog" aria-labelledby="twoFactorLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content" style="width:120%;">
            <div class="modal-header">
                <h5 class="modal-title">{{ __('message.two_factor') }}</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">×</span>
                </button>
            </div>
            <div class="modal-body">
                <p>{{ __('message.enter_auth_code') }}</p>
                <form id="twoFactorForm" novalidate>
                    <div class="form-group">
                        <input type="text" class="form-control" id="google2fa_code" name="google2fa_code"
                               placeholder="XXXXXX" maxlength="6" minlength="6" required pattern="^\d{6}$">
                        <div class="invalid-feedback">
                            {{ __('message.invalid_code_2fa') }}
                        </div>
                        <span class="text-secondary font-italic">{{ __('message.2fapopup_content') }}</span>
                    </div>
                </form>
            </div>
            <div class="modal-footer justify-content-between">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">{{ __('message.cancel') }}</button>
                <button type="button" class="btn btn-primary" id="verify2FAButton">{{ __('message.verify') }}</button>
            </div>
        </div>
    </div>
</div>
<script>
    $(document).ready(function () {
        const $modal = $('#twoFactorPopupModalAdmin');
        const $form = $('#twoFactorForm');
        const $input = $('#google2fa_code');

        // Reset modal state
        function resetModal() {
            $form.removeClass('was-validated');
            $input.val('');
            $input.removeClass('is-invalid');
        }

        $modal.on('show.bs.modal', resetModal);
        $modal.on('hidden.bs.modal', resetModal);

        $('#verify2FAButton').click(function () {
            const code = $input.val().trim();

            // Clear previous error
            $input.removeClass('is-invalid');

            // Required + pattern check (6-digit number)
            if (!code || !/^\d{6}$/.test(code)) {
                $input.addClass('is-invalid');
                $input.siblings('.invalid-feedback').text(
                    code ? '{{ __("message.invalid_code_2fa") }}' : '{{ __('message.auth_code_required') }}'
                );
                return;
            }

            // Proceed with AJAX
            $.ajax({
                url: "{{ route('verify.2fa.admin') }}",
                method: 'POST',
                data: {
                    totp: code,
                    _token: '{{ csrf_token() }}'
                },
                success: function(response) {
                    $('#error-message').hide();
                    $('#twoFactorPopupModalAdmin').modal('hide');

                    setTimeout(() => {
                        const form = document.getElementById('changePasswordForm');
                        HTMLFormElement.prototype.submit.call(form);
                    }, 300);
                },
                error: function (xhr) {
                    let msg = xhr.responseJSON?.message ?? '{{ __("message.invalid_code_2fa") }}';
                    $input.addClass('is-invalid');
                    $input.siblings('.invalid-feedback').text(msg);
                }
            });
        });
    });
</script>
<style>
</style>