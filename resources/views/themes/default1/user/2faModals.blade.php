<style>
    .hide {
        display: none;
    }
    .button-content {
        display: flex;
        align-items: center;
        justify-content: center;
    }

    [dir="rtl"] .button-content {
        flex-direction: row-reverse;
    }

    [dir="ltr"] .button-content {
        flex-direction: row;
    }
</style>

@php
    $user = DB::table('users')->where('id', Auth::id())->first();
@endphp

@if($user->password)
    <div class="modal fade" id="2fa-modal1" data-backdrop="static" data-keyboard="false">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title">{{ __('message.set_up_authenticator') }}</h4>
                    <button type="button" class="close closeandrefresh" data-bs-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="form-group {{ $errors->has('name') ? 'has-error' : '' }}">
                        {!! html()->label(trans('message.varify_password'))->class('required') !!}
                        <div class="input-group">
                            <input type="password" name="password" id="user_password" placeholder="{{ __('message.enter_password') }}" class="form-control" required="required">
                            <div class="input-group-append">
                            <span role="button" class="input-group-text" onclick="togglePasswordVisibility(this)">
                                <i class="fa fa-eye-slash"></i>
                            </span>
                            </div>
                        </div>
                        <input type="hidden" name="login_type" id="login_type" value="login">
                        <span id="passerror"></span>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" id="verify_password" class="btn btn-primary"><i class="fa fa-check">&nbsp;&nbsp;</i>{{ __('message.validate') }}</button>
                </div>
            </div>
        </div>
    </div>
@else
    <div class="modal fade" id="2fa-modal1" data-backdrop="static" data-keyboard="false">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title">{{ __('message.set_up_authenticator') }}</h4>
                    <button type="button" class="close closeandrefresh" data-bs-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <form>
                        <input type="hidden" name="user_password" id="user_password" value="">
                        <input type="hidden" name="login_type" id="login_type" value="social">
                        <h5>Hi {{$user->first_name}},</h5>
                        <p><b>{{ __('message.continue_verify') }}</b></p>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="submit" id="verify_password" class="btn btn-primary"><i class="fa fa-check">&nbsp;&nbsp;</i>{{ __('message.validate') }}</button>
                </div>
            </div>
        </div>
    </div>
@endif

<div class="modal fade" id="2fa-recover-code-modal" data-backdrop="static" data-keyboard="false">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content" style="width:700px;">
            <div class="modal-header">
                <h4 class="modal-title">{{ __('message.client_recovery_code') }}</h4>
                <button type="button" class="close closeandrefresh" data-bs-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div id="alertMessagecopied"></div>
                <p>{{ __('message.recovery_code_account') }}</p>
                <div class="row align-items-center">
                    <div><label class="col-form-label"><strong>{{ __('message.client_recovery_code') }} :</strong></label></div>
                    <div class="col">
                        <div class="input-group">
                            <input type="text" id="recoverycode" class="form-control" disabled>
                            @component('mini_views.copied_flash_text', [
                           'navigations' => [
                              ['btnName' => 'rec_code', 'slot' => 'recovery', 'style' => '<span class="input-group-text" id="copyBtn" data-bs-toggle="tooltip" title="'. __('message.copy_clipboard').'" onclick="copyRecoveryCode()" ><i id="copy_icon_recovery" class="fa fa-clipboard"></i></span>'],
                           ]
                        ])
                            @endcomponent
                        </div>
                    </div>
                </div>
                <br>
                <div>
                    <p>{{ __('message.treat_recovery_code') }} <a href="https://lastpass.com" target="_blank">{{ __('message.lastpass') }}</a>, <a href="https://1Password.com" target="_blank">{{ __('message.1_password') }}</a>, {{ __('message.or') }} <a href="https://keepersecurity.com.com" target="_blank">{{ __('message.keeper') }}</a>.</p>
                </div>
                <span id="passerror"></span>
            </div>
            <div class="modal-footer">
                <button type="button" id="next_rec_code" class="btn btn-primary">
                    @if(in_array(app()->getLocale(), ['ar', 'he']))
                        <i class="fa fa-arrow-right"></i>&nbsp;&nbsp;{{ __('message.next') }}
                    @else
                        {{ __('message.next') }}&nbsp;&nbsp;<i class="fa fa-arrow-right"></i>
                    @endif
                </button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="2fa-modal2" data-backdrop="static" data-keyboard="false">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content" style="width:700px;">
            <div class="modal-header">
                <h4 class="modal-title">{{ __('message.set_up_authenticator') }}</h4>
                <button type="button" class="close closeandrefresh" data-bs-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="text-center">
                <div class="modal-body bar-code">
                    <ul class="col-sm-offset-3 offset-sm-2 text-left">
                        <li>{{ __('message.get_authenticator') }}</li>
                        <li>{{ __('message.app_select') }} <b>{{ __('message.set_up_account') }}</b></li>
                        <li>{{ __('message.choose') }} <b>{{ __('message.scan_barcode') }}</b></li>
                    </ul>
                    <div id="barcode">
                        <!--<img id="image"/>-->
                        <div id="svgshow"></div>
                    </div>
                    <a href="javascript:;" id="cantscanit">{{ __('message.caps_can_not_scan') }}</a>
                </div>
                <div class="modal-body secret-key">
                    <div id="alertMessage2"></div>
                    <ul class="col-sm-offset-3 offset-sm-2 text-left">
                        <li>{{ __('message.tap') }} <b>{{ __('message.menu') }}</b>, {{ __('message.then') }} <b>{{ __('message.set_up_account') }}</b></li>
                        <li>{{ __('message.tap') }} <b>{{ __('message.enter_provided_key') }}</b></li>
                        <li>{{ __('message.enter_email_address') }}</li>
                        <br>
                        <div class="col-md-6">
                            <input type="text" id="secretkeyid" readonly="readonly" class="form-control" style="width: auto;">
                        </div>
                        <br><br>
                        <li>{{ __('message.make_sure') }} <b>{{ __('message.time_based') }}</b> {{ __('message.is_turned_on') }} <b>{{ __('message.add') }}</b> {{ __('message.to_finish') }}</li>
                    </ul>
                    <a href="javascript:;" id="scanbarcode">{{ __('message.caps_scan_barcode') }}</a>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" id="scan_complete" class="btn btn-primary">
                    @if(in_array(app()->getLocale(), ['ar', 'he']))
                        <i class="fa fa-arrow-right"></i>&nbsp;&nbsp;{{ __('message.next') }}
                    @else
                        {{ __('message.next') }}&nbsp;&nbsp;<i class="fa fa-arrow-right"></i>
                    @endif
                </button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="2fa-modal3" data-backdrop="static" data-keyboard="false">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content" style="width:700px;">
            <div class="modal-header">
                <h4 class="modal-title">{{ __('message.set_up_authenticator') }}</h4>
                <button type="button" class="close closeandrefresh" data-bs-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body modal-body-spacing">
                {!! html()->label(trans('message.enter_6_digit_code'))->class('required') !!}
                <div class="form-group form-field-template">
                    <input type="text" name="password" id="passcode" placeholder="{{ __('message.enter_passcode') }}" class="form-control" required="required">
                    <span id="passcodeerror"></span>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" id="prev_button" class="btn btn-default" dir="{{ in_array(app()->getLocale(), ['ar', 'he']) ? 'rtl' : 'ltr' }}">
                 <span class="button-content">
                    <i class="fa {{ in_array(app()->getLocale(), ['ar', 'he']) ? 'fa-arrow-left' : 'fa-arrow-left' }}" style="margin-right: 8px;"></i>{{ __('message.previous') }}
                 </span>
                </button>
                <button type="button" id="pass_btn" class="btn ml-auto btn-primary pull-right float-right">
                    <i class="fa fa-check"></i> {{ __('message.verify') }}
                </button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="2fa-modal4" data-backdrop="static" data-keyboard="false">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content" style="width:700px;">
            <div class="modal-header">
                <h4 class="modal-title">{{ __('message.set_up_authenticator') }}</h4>
                <button type="button" class="close closeandrefresh" data-bs-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="form-group {{ $errors->has('name') ? 'has-error' : '' }}">
                    {{ __('message.set_auth_billing') }}
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default pull-left closeandrefresh" data-dismiss="modal"><i class="fa fa-times">&nbsp;&nbsp;</i>{{ __('message.close') }}</button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="2fa-modal5" data-backdrop="static" data-keyboard="false">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content" style="width:700px;">
            <div class="modal-header">
                <h4 class="modal-title">{{ __('message.two_factor_authentication') }}</h4>
                <button type="button" class="close closeandrefresh" data-bs-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div id="alertMessage"></div>
                <div class="form-group {{ $errors->has('name') ? 'has-error' : '' }}">
                    {{ __('message.two_factor_verification') }}
                </div>
            </div>
            <div class="modal-footer">
                <button class="btn btn-danger pull-right float-right" id="turnoff2fa"><i class="fa fa-power-off"></i> {{ __('message.caps_turn_off') }}</button>
            </div>
        </div>
    </div>
</div>


<div class="modal fade" id="2fa-view-code-modal" data-backdrop="static" data-keyboard="false">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content" style="width:700px;">
            <div class="modal-header">
                <h4 class="modal-title">{{ __('message.client_recovery_code') }}</h4>
                <button type="button" class="close closeandrefresh" data-bs-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <p>{{ __('message.recovery_code_once') }}</p>
                <div class="row align-items-center">
                    <div>
                        <label for="newrecoverycode" class="col-form-label"><strong>{{ __('message.client_recovery_code') }} :</strong></label>
                    </div>
                    <div class="col">
                        <div class="input-group">
                            <input type="text" id="newrecoverycode" class="form-control" value="YourRecoveryCodeHere" disabled>
                            @component('mini_views.copied_flash_text', [
                           'navigations' => [
                              ['btnName' => 'rec_code', 'slot' => 'recovery', 'style' => '<span class="input-group-text" id="copyNewCodeBtn" data-bs-toggle="tooltip" title="" onclick="copyNewRecoveryCode()" ><i id="copy_icon" class="fa fa-clipboard"></i></span>'],
                           ]
                        ])
                            @endcomponent
                        </div>
                    </div>
                </div>
                <span id="passerror"></span>
            </div>
            <div class="modal-footer">
                <button class="btn btn-dark" id="generateNewCode">{{ __('message.generate_new') }}</button>
            </div>
        </div>
    </div>
</div>

<script>


    document.addEventListener('keydown', function(e) {
        if (e.key === 'Enter') {
            e.preventDefault();

            const buttonIds = [
                'verify_password',
                'copyNewCodeBtn',
                'pass_btn',
                'scan_complete',
                'next_rec_code'
            ];

            for (const id of buttonIds) {
                const btn = document.getElementById(id);

                // Check if button exists and is visible
                if (btn && btn.offsetParent !== null) {
                    btn.click();
                    break; // stop after the first visible one is clicked
                }
            }
        }
    });
</script>