@extends('themes.default1.layouts.front.master')
@section('title')
{{ __('message.two_factory_recovery') }}
@stop
@section('page-heading')
    {{ __('message.two_factory_recovery') }}
@stop
@section('page-header')
{{ __('message.forgot-password') }}
@stop
@section('breadcrumb')
    @if(Auth::check())
        <li><a class="text-primary" href="{{url('my-invoices')}}">{{ __('message.home')}}</a></li>
    @else
         <li><a class="text-primary" href="{{url('login')}}">{{ __('message.home')}}</a></li>
    @endif
     <li class="active text-dark">{{ __('message.two_factory_recovery')}}</li>
@stop 
@section('main-class') 
main
@stop
@section('content')
        <div class="container py-4">

            <div class="row justify-content-center">

                <div class="col-md-6 col-lg-6 mb-5 mb-lg-0 pe-5">

                    {!! html()->form('POST', route('verify-recovery-code'))->id('recovery_form')->open() !!}


                    <div class="row">

                            <div class="form-group col">

                                <label class="form-label text-color-dark text-3">{{ __('message.enter_recovery_code')}} <span class="text-color-danger">*</span></label>

                                <input type="text" name="rec_code"  value="" class="form-control form-control-lg text-4">
                            </div>
                            <h6 id="codecheck"></h6>
                        </div>

                    {!! honeypotField('recovery_code') !!}

                    <?php
                    $status = \App\Model\Common\StatusSetting::first();
                    ?>
                    @if ($status->recaptcha_status === 1)
                        <div id="recovery_recaptcha"></div>
                        <div id="recovery-verification"></div><br>
                    @elseif($status->v3_recaptcha_status === 1)
                        <input type="hidden" class="g-recaptcha-token" name="g-recaptcha-response" data-recaptcha-action="recovery">
                    @endif

                        <p class="text-2">{{ __('message.recovery_code_used')}}</p>

                        <div class="row">

                            <div class="form-group">
                               

                                   <a href="{{'verify-2fa'}}" >{{ __('message.login_authenticator_passcode')}}</a>
                               
                            </div>

                        </div>

                        <div class="row">

                            <div class="form-group col">

                                <button type="submit" class="btn btn-dark btn-modern w-100 text-uppercase font-weight-bold text-3 py-3" data-loading-text="{{ __('message.loading') }}">{{ __('message.verify')}}</button>
                            </div>
                        </div>
                    {!! html()->form()->close() !!}
                </div>
            </div>

        </div>
{{--        @extends('mini_views.recaptcha')--}}
        <script>
            let recovery_recaptcha_id;
            @if($status->recaptcha_status === 1)
            recaptchaFunctionToExecute.push(() => {
                recovery_recaptcha_id = grecaptcha.render('recovery_recaptcha', {'sitekey': siteKey});
            });
            @endif
            $(document).ready(function() {
                function placeErrorMessage(error, element, errorMapping = null) {
                    if (errorMapping !== null && errorMapping[element.attr("name")]) {
                        $(errorMapping[element.attr("name")]).html(error);
                    } else {
                        error.insertAfter(element);
                    }
                }
                $('#recovery_form').validate({
                    ignore: ":hidden:not(.g-recaptcha-response)",
                    rules: {
                        rec_code: {
                            required: true
                        },
                        "g-recaptcha-response": {
                            recaptchaRequired: true
                        }
                    },
                    messages: {
                        rec_code: {
                            required: "{{ __('message.please_enter_recovery_code') }}",
                        },
                        "g-recaptcha-response": {
                            recaptchaRequired: "{{ __('message.recaptcha_required') }}"
                        }
                    },
                    unhighlight: function (element) {
                        $(element).removeClass("is-valid");
                    },
                    errorPlacement: function (error, element) {
                        placeErrorMessage(error, element);
                    },
                    submitHandler: function (form) {
                        form.submit();
                    }
                });
            });
        </script>
@stop 
