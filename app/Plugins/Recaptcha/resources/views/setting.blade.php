@extends('themes.default1.layouts.master')

@section('title')
    {{ __('message.captcha_settings') }}
@stop

@section('content-header')
    <div class="col-sm-6">
        <h1>{{ __('message.captcha_settings') }}</h1>
    </div>
    <div class="col-sm-6">
        <ol class="breadcrumb float-sm-right">
            <li class="breadcrumb-item"><a href="{{ url('/') }}"><i class="fa fa-dashboard"></i> {{ __('message.home') }}</a></li>
            <li class="breadcrumb-item"><a href="{{ url('settings') }}">{{ __('message.settings') }}</a></li>
            <li class="breadcrumb-item active">{{ __('message.captcha_settings') }}</li>
        </ol>
    </div>
@stop

@section('content')
    <div id="recaptcha-setting-alert" role="alert" aria-live="polite"></div>
    <div id="loading-overlay" class="d-none position-fixed w-100 h-100 overlay-bg">
        <div class="d-flex justify-content-center align-items-center h-100">
            <div class="spinner-border text-primary" role="status">
                <span class="sr-only">{{ __('message.loading') }}</span>
            </div>
        </div>
    </div>
    <div class="card card-secondary card-outline">
        <div class="card-header">
            <h3 class="card-title">{{ __('message.captcha_configuration') }}</h3>
        </div>
        {!! html()->form()->id('captcha-settings-form')->attributes(['novalidate' => true])->open() !!}
        <div class="card-body">
            {{-- General Configuration --}}
            <div class="form-group row">
                <label for="captcha_version" class="col-sm-4 col-form-label">{{ __('message.captcha_version') }}</label>
                <div class="col-sm-8">
                    {!! html()->select('captcha_version', [
                            'v3_invisible' => __('message.recaptcha_v3'),
                            'v2_invisible' => __('message.recaptcha_v2_invisible'),
                            'v2_checkbox' => __('message.recaptcha_v2_checkbox')
                        ])
                        ->class('form-control')
                        ->id('captcha_version')
                    !!}
                    <small class="form-text text-muted">{{ __('message.select_captcha_type') }}</small>
                </div>
            </div>

            <div class="form-group row" id="group_failover_action">
                <label for="failover_action" class="col-sm-4 col-form-label">{{ __('message.failover_action') }}</label>
                <div class="col-sm-8">
                    {!! html()->select('failover_action', [
                            'none' => __('message.none'),
                            'v2_checkbox' => __('message.fallback_v2_checkbox')
                        ])
                        ->class('form-control')
                        ->id('failover_action')
                    !!}
                    <small class="form-text text-muted">{{ __('message.action_if_captcha_fails') }}</small>
                </div>
            </div>

            <hr>

            {{-- reCAPTCHA v3 Settings --}}
            <div class="v3-settings-block">
                <h5 class="text-muted mb-3">{{ __('message.recaptcha_v3_settings') }}</h5>

                <div class="form-group row">
                    <label for="v3_site_key" class="col-sm-4 col-form-label">{{ __('message.v3_site_key') }}</label>
                    <div class="col-sm-8">
                        {!! html()->text('v3_site_key')->class('form-control')->id('v3_site_key')->placeholder(__('message.enter_v3_site_key')) !!}
                    </div>
                </div>

                <div class="form-group row">
                    <label for="v3_secret_key" class="col-sm-4 col-form-label">{{ __('message.v3_secret_key') }}</label>
                    <div class="col-sm-8">
                        {!! html()->password('v3_secret_key')->class('form-control')->placeholder(__('message.enter_v3_secret_key'))->value(old('v3_secret_key', $settings->v3_secret_key ?? '')) !!}
                    </div>
                </div>

                <div class="form-group row conditional-setting" id="group_score_threshold">
                    <label for="score_threshold" class="col-sm-4 col-form-label">{{ __('message.v3_score_threshold') }}</label>
                    <div class="col-sm-8">
                        {!! html()->input('number', 'score_threshold')
                            ->class('form-control')
                            ->attributes(['min' => 0, 'max' => 1, 'step' => 0.1])
                        !!}
                        <small class="form-text text-muted">{{ __('message.v3_score_hint') }}</small>
                    </div>
                </div>

                <div class="form-group row">
                    <label class="col-sm-4 col-form-label">{{ __('message.v3_preview') }}</label>
                    <div class="col-sm-8">
                        <div id="v3-preview-container" class="border p-3 bg-light min-height-100">
                            <div id="v3_response"></div>
                            <div id="v3_error" class="text-danger mt-1"></div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- reCAPTCHA v2 Settings --}}
            <div class="v2-settings-block">
                <h5 class="text-muted mb-3 mt-4">{{ __('message.recaptcha_v2_settings') }}</h5>

                <div class="form-group row">
                    <label for="v2_site_key" class="col-sm-4 col-form-label">{{ __('message.v2_site_key') }}</label>
                    <div class="col-sm-8">
                        {!! html()->text('v2_site_key')->class('form-control')->id('v2_site_key')->placeholder(__('message.enter_v2_site_key')) !!}
                    </div>
                </div>

                <div class="form-group row">
                    <label for="v2_secret_key" class="col-sm-4 col-form-label">{{ __('message.v2_secret_key') }}</label>
                    <div class="col-sm-8">
                        {!! html()->password('v2_secret_key')->class('form-control')->placeholder(__('message.enter_v2_secret_key')) !!}
                    </div>
                </div>

                <div class="form-group row">
                    <label class="col-sm-4 col-form-label">{{ __('message.v2_preview') }}</label>
                    <div class="col-sm-8">
                        <div id="v2-preview-container" class="border p-3 bg-light min-height-100">
                            <div id="v2_response"></div>
                            <div id="v2_error" class="text-danger mt-1"></div>
                        </div>
                    </div>
                </div>
            </div>

            <hr>

            {{-- Appearance & Messages --}}
            <h5 class="text-muted mb-3 mt-4">{{ __('message.appearance_messages') }}</h5>

            <div class="form-group row">
                <label for="error_message" class="col-sm-4 col-form-label">{{ __('message.error_message') }}</label>
                <div class="col-sm-8">
                    {!! html()->text('error_message')->class('form-control') !!}
                </div>
            </div>

            {{-- Conditional Appearance Settings --}}
            <div class="form-group row conditional-setting" id="group_theme">
                <label class="col-sm-4 col-form-label">{{ __('message.theme') }}</label>
                <div class="col-sm-8 d-flex align-items-center">
                    <div class="form-check form-check-inline">
                        {!! html()->radio('theme', true, 'light')->id('theme_light')->class('form-check-input') !!}
                        <label class="form-check-label" for="theme_light">{{ __('message.theme_light') }}</label>
                    </div>
                    <div class="form-check form-check-inline">
                        {!! html()->radio('theme', false, 'dark')->id('theme_dark')->class('form-check-input') !!}
                        <label class="form-check-label" for="theme_dark">{{ __('message.theme_dark') }}</label>
                    </div>
                </div>
            </div>

            <div class="form-group row conditional-setting" id="group_size">
                <label class="col-sm-4 col-form-label">{{ __('message.size') }}</label>
                <div class="col-sm-8 d-flex align-items-center">
                    <div class="form-check form-check-inline">
                        {!! html()->radio('size', true, 'normal')->id('size_normal')->class('form-check-input') !!}
                        <label class="form-check-label" for="size_normal">{{ __('message.size_normal') }}</label>
                    </div>
                    <div class="form-check form-check-inline">
                        {!! html()->radio('size', false, 'compact')->id('size_compact')->class('form-check-input') !!}
                        <label class="form-check-label" for="size_compact">{{ __('message.size_compact') }}</label>
                    </div>
                </div>
            </div>

            <div class="form-group row conditional-setting" id="group_badge_position">
                <label for="badge_position" class="col-sm-4 col-form-label">{{ __('message.badge_position') }}</label>
                <div class="col-sm-8">
                    {!! html()->select('badge_position', [
                            'bottomright' => __('message.badge_bottomright'),
                            'bottomleft'  => __('message.badge_bottomleft'),
                            'inline'      => __('message.badge_inline')
                        ])
                        ->class('form-control')
                        ->id('badge_position')
                    !!}
                </div>
            </div>

        </div>
        <div class="card-footer">
            <button type="submit" class="btn btn-primary pull-right">
                <i class="fa fa-save">&nbsp;&nbsp;</i>{{ __('message.save') }}
            </button>
        </div>
        {!! html()->form()->close() !!}
    </div>
    <script>
        {!! file_get_contents(app_path('Plugins/Recaptcha/resources/assets/js/recaptcha.js')) !!}
    </script>
    <script>
        class RecaptchaSettingsUI {
            constructor() {
                this.state = { settingsLoaded: false, previousVersion: null, isDirty: false };
                this.instances = { v3: null, v2: null, v2Invisible: null };
                this.elements = this.cacheElements();
                this.init();
            }
            cacheElements() {
                return {
                    form: document.getElementById('captcha-settings-form'),
                    captchaVersion: document.getElementById('captcha_version'),
                    failoverAction: document.getElementById('failover_action'),
                    v3SiteKey: document.getElementById('v3_site_key'),
                    v2SiteKey: document.getElementById('v2_site_key'),
                    v3SecretKey: document.getElementById('v3_secret_key'),
                    v2SecretKey: document.getElementById('v2_secret_key'),
                    scoreThreshold: document.getElementById('score_threshold'),
                    alert: document.getElementById('recaptcha-setting-alert'),
                    v3Response: document.getElementById('v3_response'),
                    v2Response: document.getElementById('v2_response'),
                    v3Error: document.getElementById('v3_error'),
                    v2Error: document.getElementById('v2_error'),
                    v3SettingsBlock: document.querySelector('.v3-settings-block'),
                    v2SettingsBlock: document.querySelector('.v2-settings-block'),
                    scoreGroup: document.getElementById('group_score_threshold'),
                    themeGroup: document.getElementById('group_theme'),
                    sizeGroup: document.getElementById('group_size'),
                    badgeGroup: document.getElementById('group_badge_position'),
                    loadingOverlay: document.getElementById('loading-overlay'),
                };
            }
            async init() {
                this.showLoading(true);
                this.setupEvents();
                await this.fetchSettings();
                this.showLoading(false);
            }
            setupEvents() {
                this.elements.captchaVersion.addEventListener('change', () => this.toggleCaptchaSettings());
                this.elements.failoverAction.addEventListener('change', () => this.handleFailoverChange());
                this.elements.v3SiteKey.addEventListener('input', () => { if (this.elements.v3SiteKey.value.trim() === '' && this.state.settingsLoaded) this.elements.v3SecretKey.value = ''; this.renderPreviews(); });
                this.elements.v2SiteKey.addEventListener('input', () => { if (this.elements.v2SiteKey.value.trim() === '' && this.state.settingsLoaded) this.elements.v2SecretKey.value = ''; this.renderPreviews(); });
                document.querySelectorAll('input[name="theme"], input[name="size"], input[name="badge_position"]').forEach(input => input.addEventListener('change', () => this.renderPreviews()));
                this.elements.form.addEventListener('submit', e => this.handleSubmit(e));
                window.addEventListener('beforeunload', () => this.clearAllPreviews());
            }
            async fetchSettings() {
                this.showLoading(true);
                $.ajax({
                    url: "{{ url('recaptcha-settings') }}",
                    method: 'GET',
                    dataType: 'json',
                    success: (response) => {
                        const data = response.data;
                        this.populateFields(data);
                        this.state.settingsLoaded = true;
                        this.state.previousVersion = data.captcha_version || 'v3_invisible';
                        this.toggleCaptchaSettings();
                        this.showLoading(false);
                    },
                    error: () => {
                        this.showAlert('Failed to load settings. Please refresh the page.', 'error');
                        this.showLoading(false);
                    }
                });
            }
            populateFields(data) {
                this.elements.captchaVersion.value = data.captcha_version || 'v3_invisible';
                this.elements.failoverAction.value = data.failover_action || 'none';
                this.elements.v3SiteKey.value = data.v3_site_key || '';
                this.elements.v3SecretKey.value = data.v3_secret_key || '';
                this.elements.scoreThreshold.value = data.score_threshold || 0.5;
                this.elements.v2SiteKey.value = data.v2_site_key || '';
                this.elements.v2SecretKey.value = data.v2_secret_key || '';
                document.querySelector(`input[name="theme"][value="${data.theme || 'light'}"]`).checked = true;
                document.querySelector(`input[name="size"][value="${data.size || 'normal'}"]`).checked = true;
                document.getElementById('badge_position').value = data.badge_position || 'bottomright';
                document.getElementById('error_message').value = data.error_message || 'Failed to verify you are human.';
            }
            toggleCaptchaSettings() {
                const selectedVersion = this.elements.captchaVersion.value;
                const selectedFailover = this.elements.failoverAction.value;
                // Show fallback only for v3
                document.getElementById('group_failover_action').style.display = selectedVersion === 'v3_invisible' ? 'flex' : 'none';
                this.elements.scoreGroup.style.display = selectedVersion === 'v3_invisible' ? 'flex' : 'none';
                this.elements.themeGroup.style.display = (selectedVersion === 'v2_checkbox' || selectedFailover === 'v2_checkbox') ? 'flex' : 'none';
                this.elements.sizeGroup.style.display = (selectedVersion === 'v2_checkbox' || selectedFailover === 'v2_checkbox') ? 'flex' : 'none';
                this.elements.badgeGroup.style.display = (selectedVersion === 'v3_invisible' || selectedVersion === 'v2_invisible') ? 'flex' : 'none';
                this.elements.v3SettingsBlock.style.display = selectedVersion === 'v3_invisible' ? 'block' : 'none';
                this.elements.v2SettingsBlock.style.display = (selectedVersion !== 'v3_invisible' || selectedFailover === 'v2_checkbox') ? 'block' : 'none';
                this.clearAllPreviews();
                setTimeout(() => this.renderPreviews(), 100);
            }
            handleFailoverChange() {
                this.toggleCaptchaSettings();
            }
            async renderPreviews() {
                this.clearAllPreviews();
                const selectedVersion = this.elements.captchaVersion.value;
                const selectedFailover = this.elements.failoverAction.value;
                const v3Key = this.elements.v3SiteKey.value.trim();
                const v2Key = this.elements.v2SiteKey.value.trim();
                const theme = document.querySelector('input[name="theme"]:checked').value;
                const size = document.querySelector('input[name="size"]:checked').value;
                if (selectedVersion === 'v3_invisible' && this.elements.v3SettingsBlock.style.display !== 'none' && v3Key.length > 10) {
                    await this.renderV3Preview(v3Key);
                }
                if ((selectedVersion === 'v2_checkbox' || selectedFailover === 'v2_checkbox') && this.elements.v2SettingsBlock.style.display !== 'none' && v2Key.length > 10) {
                    await this.renderV2Preview(v2Key, theme, size);
                }
                if (selectedVersion === 'v2_invisible' && this.elements.v2SettingsBlock.style.display !== 'none' && v2Key.length > 10) {
                    await this.renderV2Preview(v2Key);
                }
            }
            async renderV3Preview(siteKey) {
                this.clearV3Preview();
                try {
                    this.instances.v3 = await RecaptchaManager.init(this.elements.v3Response, { mode: 'v3', v3SiteKey: siteKey, action: 'settings_save', forceReload: true, badge: 'inline' });
                    const token = await this.instances.v3.getToken();
                    if (!token) throw new Error('No token received');
                } catch (error) {
                    this.clearV3Preview();
                    this.elements.v3Response.innerHTML = '<span class="text-danger">Failed to load reCAPTCHA v3. Check your site key.</span>';
                }
            }
            async renderV2Preview(siteKey, theme, size) {
                this.clearV2Preview();
                try {
                    this.instances.v2 = await RecaptchaManager.init(this.elements.v2Response, { mode: 'v2', v2SiteKey: siteKey, theme, size, debug: true });
                } catch (error) {
                    this.clearV2Preview();
                    this.elements.v2Response.innerHTML = '<span class="text-danger">Failed to load reCAPTCHA v2. Check your site key.</span>';
                }
            }
            async renderV2InvisiblePreview(siteKey) {
                this.clearV2Preview();
                try {
                    this.instances.v2Invisible = await RecaptchaManager.init(this.elements.v2Response, { mode: 'v2-invisible', v2SiteKey: siteKey, debug: true, badge: 'inline' });
                } catch (error) {
                    this.clearV2Preview();
                    this.elements.v2Response.innerHTML = '<span class="text-danger">Failed to load reCAPTCHA v2 Invisible. Check your site key.</span>';
                }
            }
            clearAllPreviews() {
                this.clearV3Preview();
                this.clearV2Preview();
                document.querySelectorAll('.grecaptcha-badge').forEach(el => el.remove());
            }
            clearV3Preview() {
                if (this.instances.v3) { try { this.instances.v3.destroy(); } catch(e){} this.instances.v3 = null; }
                this.elements.v3Response.innerHTML = '';
            }
            clearV2Preview() {
                if (this.instances.v2) { try { this.instances.v2.destroy(); } catch(e){} this.instances.v2 = null; }
                if (this.instances.v2Invisible) { try { this.instances.v2Invisible.destroy(); } catch(e){} this.instances.v2Invisible = null; }
                this.elements.v2Response.innerHTML = '';
            }
            showLoading(show) {
                if (show) {
                    helper.globalLoader.show();
                } else {
                    helper.globalLoader.hide();
                }
            }
            showAlert(message, type = 'info') {
                if (!message) return;
                helper.showAlert({
                    message,
                    type,
                    containerSelector: '#recaptcha-setting-alert',
                    autoDismiss: 5000,
                    dismissible: true,
                    id: 'recaptcha-setting-alert-box',
                });
            }
            async handleSubmit(e) {
                e.preventDefault();
                if (!$(this.elements.form).valid()) { this.shakeForm(); return false; }
                const submitButton = this.elements.form.querySelector('button[type="submit"]');
                submitButton.disabled = true;
                submitButton.innerHTML = '<i class="fa fa-spinner fa-spin"></i>&nbsp;&nbsp;Saving...';
                this.elements.v2Error.textContent = '';
                this.elements.v3Error.textContent = '';
                let v2ResponseToken = null, v3ResponseToken = null, hasError = false;
                if (this.instances.v3) {
                    try { v3ResponseToken = await this.instances.v3.getToken('settings_save'); if (!v3ResponseToken) { this.elements.v3Error.textContent = 'Please complete the reCAPTCHA v3.'; hasError = true; } } catch (e) { this.elements.v3Error.textContent = 'Failed to generate reCAPTCHA v3 token.'; hasError = true; }
                }
                if (this.instances.v2) {
                    try { v2ResponseToken = await this.instances.v2.getToken(); if (!v2ResponseToken) { this.elements.v2Error.textContent = 'Please complete the reCAPTCHA v2.'; hasError = true; } } catch (e) { this.elements.v2Error.textContent = 'Failed to generate reCAPTCHA v2 token.'; hasError = true; }
                }
                if (hasError) { submitButton.disabled = false; submitButton.innerHTML = '<i class="fa fa-save"></i>Save'; return; }
                const payload = {
                    captcha_version: this.elements.captchaVersion.value,
                    failover_action: this.elements.failoverAction.value,
                    v3_site_key: this.elements.v3SiteKey.value,
                    v3_secret_key: this.elements.v3SecretKey.value,
                    score_threshold: this.elements.scoreThreshold.value,
                    v2_site_key: this.elements.v2SiteKey.value,
                    v2_secret_key: this.elements.v2SecretKey.value,
                    theme: document.querySelector('input[name="theme"]:checked').value,
                    size: document.querySelector('input[name="size"]:checked').value,
                    badge_position: this.elements.badgeGroup.querySelector('select').value,
                    error_message: document.getElementById('error_message').value,
                    v3_g_recaptcha_response: v3ResponseToken,
                    v2_g_recaptcha_response: v2ResponseToken
                };
                $.ajax({
                    url: "{{ url('recaptcha-settings') }}",
                    method: 'POST',
                    contentType: 'application/json',
                    data: JSON.stringify(payload),
                    dataType: 'json',
                    success: (result) => {
                        if (result && result.message) {
                            this.showAlert(result.message, 'success');
                        } else {
                            this.showAlert('Settings saved.', 'success');
                        }
                    },
                    error: (xhr) => {
                        let msg = 'Failed to save settings. Please try again.';
                        if (xhr.responseJSON && xhr.responseJSON.message) msg = xhr.responseJSON.message;
                        this.showAlert(msg, 'error');
                        this.renderPreviews();
                    },
                    complete: () => {
                        submitButton.disabled = false;
                        submitButton.innerHTML = '<i class="fa fa-save"></i>Save';
                    }
                });
            }
            shakeForm() {
                this.elements.form.classList.add('shake');
                setTimeout(() => this.elements.form.classList.remove('shake'), 500);
            }
        }
        document.addEventListener('DOMContentLoaded', () => { window.recaptchaSettingsUI = new RecaptchaSettingsUI(); });
    </script>
@stop
