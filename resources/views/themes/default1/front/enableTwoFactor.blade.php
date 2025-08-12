@extends('themes.default1.layouts.front.master')
@section('title')
{{ __('message.two_factor') }}
@stop
@section('page-heading')
    {{ __('message.two_factor') }}
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
     <li class="active text-dark">{{ __('message.two_factor')}}</li>
@stop 
@section('main-class') 
main
@stop
@section('content')
        <div class="container py-4">

            <div class="row justify-content-center">

                <div class="col-md-6 col-lg-6 mb-5 mb-lg-0 pe-5">

                    {!! html()->form('POST', route('2fa/loginValidate'))->id('2fa_form')->open() !!}


                    <div class="row">

                            <div class="form-group col">

                                <label class="form-label text-color-dark text-3">{{ __('message.enter_auth_code')}} <span class="text-color-danger">*</span></label>

                                <input type="text" name="totp"  id="2fa_code" value="" class="form-control form-control-lg text-4">
                            </div>
                            <h6 id="codecheck"></h6>
                        </div>

                    {!! honeypotField('2fa_code') !!}

                    <?php
                    $status = \App\Model\Common\StatusSetting::first();
                    ?>
                    @if ($status->recaptcha_status === 1)
                        <div id="2fa_recaptcha"></div>
                        <div id="2fa-verification"></div><br>
                    @elseif($status->v3_recaptcha_status === 1)
                        <input type="hidden" class="g-recaptcha-token" name="g-recaptcha-response" data-recaptcha-action="2fa">
                    @endif

                        <p class="text-2">{{ __('message.open_two_factor')}}</p>

                        <div class="row">

                            <div class="form-group">
                                @if(!Session::has('reset_token'))

                                <div class="custom-control custom-checkbox">

                                    <label style="position: absolute;left: 0px;">{{ __('message.having_problem')}} <a href="{{'recovery-code'}}" >{{ __('message.login_recovery_code')}}</a></label>
                                </div>
                                  @endif
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
        let two_factor_recaptcha_id;
        @if($status->recaptcha_status === 1)
        recaptchaFunctionToExecute.push(() => {
            two_factor_recaptcha_id = grecaptcha.render('2fa_recaptcha', {'sitekey': siteKey});
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
            $.validator.addMethod("recaptchaRequired", function(value, element) {
                try {
                    if(!recaptchaEnabled) {
                        return false;
                    }
                }catch (ex){
                    return false
                }
                return value.trim() !== "";
            }, "{{ __('message.recaptcha_required') }}");

            $('#2fa_form').validate({
                ignore: ":hidden:not(.g-recaptcha-response)",
                rules: {
                    totp: {
                        required: true
                    },
                    "g-recaptcha-response": {
                        recaptchaRequired: true
                    }
                },
                messages: {
                    totp: {
                        required: "{{ __('message.please_enter_auth_code') }}"
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
        <script>
            (function() {
                const checkUrl = "{{ route('2fa.session.check') }}";
                const checkInterval = 30000;

                function checkSession() {
                    $.ajax({
                        url: checkUrl,
                        type: 'GET',
                        success: function(response, status, xhr) {
                        },
                        error: function() {
                            window.location.href = '{{ url('login') }}';
                        }
                    });
                }

                setInterval(checkSession, checkInterval);
            })();
        </script>
@stop 
