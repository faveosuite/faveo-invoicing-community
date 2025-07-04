@extends('themes.default1.layouts.front.master')
@section('title')
    {{ __('message.login_register') }}
@stop
@section('page-header')
    {{ __('message.login_register') }}
@stop
@section('page-heading')
    {{ __('message.sign_lr') }}
@stop
@section('breadcrumb')
    @if(Auth::check())
        <li><a class="text-primary" href="{{url('my-invoices')}}">{{ __('message.home') }}</a></li>
    @else
         <li><a class="text-primary" href="{{url('login')}}">{{ __('message.home') }}</a></li>
    @endif
     <li class="active text-dark">{{ __('message.sign-in') }} &nbsp;&nbsp;{{ __('message.or') }}&nbsp;&nbsp; {{ __('message.register') }}</li>
@stop 
@section('main-class')
    main
@stop
@section('content')
    <?php
    use App\Http\Controllers\Front\CartController;
    $country = findCountryByGeoip($location['iso_code']);
    $states = findStateByRegionId($location['iso_code']);
    $states = \App\Model\Common\State::pluck('state_subdivision_name', 'state_subdivision_code')->toArray();
    $state_code = $location['iso_code'] . "-" . $location['state'];
    $state = getStateByCode($state_code);


    ?>
    <style>
<?php 
$setting = \App\Model\Common\Setting::where('id', 1)->first();
$everyPageScripts = ''; 
$scripts = \App\Model\Common\ChatScript::where('on_registration', 1)->get();
foreach($scripts as $script) {
    if (strpos($script->script, '<script>') === false && strpos($script->script, '</script>') === false) {
        $everyPageScripts .= "<script>{$script->script}</script>";
    } else {
        $everyPageScripts .= $script->script;
    }
}
?>
  .bootstrap-select.btn-group .btn .filter-option 
  {
      font-weight: normal;
  }
        .required:after{
            content:'*';
            color:red;
            padding-left:0px;
        }


        

        .wizard-inner
        {
            display:none;
        }

        

        .nav-tabs{
            border-bottom: none;
            margin: -5px;
        }
        .tab-content {
            border-radius: 0px;
            box-shadow: inherit;

            border: none ;
            border-top: 0;
            /*padding: 15px;*/
        }

        .open>.dropdown-menu {
            display: block;
            color:black;
        }
        .inner>.dropdown-menu{
            margin-top: 0px;
        }
        .bootstrap-select .dropdown-toggle .caret {
           display: none;
        }

        .form-control:not(.form-control-sm):not(.form-control-lg) {
        /*font-size: 13.6px;
        font-size: 0.85rem;*/
        line-height: normal;
    }
        [dir="rtl"] #forgot_password_rtl{
            text-align:left !important;
            display:block !important;
        }



    </style>
    
    <link rel="stylesheet" href="{{asset('client/css/selectpicker.css')}}" />
    <div id="alert-container"></div>
    <div class="row">
        <div class="col-md-12">

            <section>
                <div class="col-md-12 tab-pane active">
                            <div class="featured-boxes">
                                <div id="error">
                                </div>
                                <div id="success">
                                </div>
                                <div id="fails">
                                </div>
                                <div id="alertMessage1"></div>
                                <div id="alertMessage2"></div>
                                <!-- <div id="error2">
                                </div>
                                <div id="alertMessage2" class="-text" ></div> -->
              
                                <div class="row justify-content-center">
                                          <div class="col-md-6 col-lg-6 mb-5 mb-lg-0 pe-5">

                    <h2 class="font-weight-bold text-5 mb-0">{{ __('message.login') }}</h2>

                    {!! html()->form('POST', url('login'))->id('formoid')->open() !!}

                        <div class="row">

                            <div class="form-group col {{ $errors->has('email1') ? 'has-error' : '' }}">

                                <label class="form-label text-color-dark text-3">{{ __('message.username_register') }}<span class="text-color-danger">*</span></label>

                                {!! html()->email('email_username')
   ->class('form-control form-control-lg text-4')
   ->id('username')
   ->attribute('autocomplete', 'off')
   ->style('height: calc(1.5em + 0.75rem + 2px);') !!}
                                <div id="error-login-email"></div>
                            </div>
                        </div>

                        <div class="row">

                            <div class="form-group col {{ $errors->has('password1') ? 'has-error' : '' }}">

                                <label class="form-label text-color-dark text-3">{{ __('message.password') }} <span class="text-color-danger">*</span></label>

                                <div class="input-group">
                                    {!! html()->password('password1')
    ->class('form-control form-control-lg text-4')
    ->id('pass')
    ->style('height: calc(1.5em + 0.75rem + 2px);') !!}

                                        <span class="input-group-text" role="button" onclick="togglePasswordVisibility(this)">
                                            <i class="fa fa-eye-slash"></i>
                                        </span>
                                </div>
                                <div id="error-login-password"></div>
                            </div>
                        </div>
                                              {!! honeypotField('login') !!}

                                              <div class="row mb-3 align-items-center">
                                                  <div class="col-6">
                                                      <div class="custom-control custom-checkbox">
                                                          {!! html()->checkbox('remember', false)->class('custom-control-input')->id('rememberme') !!}
                                                          <label class="custom-control-label text-2 cur-pointer" for="rememberme">
                                                              {{ __('message.remember-me') }}
                                                          </label>
                                                      </div>
                                                  </div>
                                                  <div class="col-6">
                                                      <div class="{{ $errors->has('password1') ? 'has-error' : '' }}" id="forgot_password_rtl" style="text-align: right; display: block;">
                                                          <a class="text-decoration-none text-color-primary font-weight-semibold text-2 text-break"
                                                             href="{{ url('password/reset') }}">
                                                              {{ __('message.forgot-my-password') }}
                                                          </a>
                                                      </div>
                                                  </div>
                                              </div>
                         <?php
                         use App\Model\Common\StatusSetting;
                         $gcaptcha = StatusSetting::pluck('v3_v2_recaptcha_status')->first();
                         ?>

                                              @if($gcaptcha===1)
                        @if ($status->recaptcha_status === 1)
                              <div id="login_recaptcha"></div>
                              <div id="loginrobot-verification"></div><br>
                        @elseif($status->v3_recaptcha_status === 1)

                              <input type="hidden" class="g-recaptcha-token" name="g-recaptcha-response" data-recaptcha-action="login">
                        @endif
                        @endif
                        <div class="row">

                            <div class="form-group col">

                                <button type="submit" id="login-btn" class="btn btn-dark btn-modern w-100 text-uppercase font-weight-bold text-3 py-3" data-loading-text="{{ __('message.loading') }}">{{ __('message.login')}}</button>
                                @if($google_status == 1 || $twitter_status == 1 || $github_status == 1 ||$linkedin_status == 1)

                                <div class="divider">

                                    <span class="bg-light px-4 position-absolute left-50pct top-50pct transform3dxy-n50">{{ __('message.or')}}</span>
                                </div>
                                @endif
                                @if($google_status == 1)

                                <a href="{{ url('/auth/redirect/google') }}" class="btn btn-primary-scale-2 btn-modern w-100 text-transform-none font-weight-bold align-items-center d-inline-flex justify-content-center text-3 py-3" data-loading-text="{{ __('message.loading') }}">

                                    <i class="fab fa-google text-5 me-2"></i> {{ __('message.login_google')}}
                                </a><br><br>
                                @endif
                                @if($twitter_status == 1)

                                <a href="{{ url('/auth/redirect/twitter') }}" class="btn btn-primary-scale-2 btn-modern w-100 text-transform-none font-weight-bold align-items-center d-inline-flex justify-content-center text-3 py-3" data-loading-text="{{ __('message.loading') }}">

                                    <i class="fab fa-twitter text-5 me-2"></i> {{ __('message.login_twitter')}}
                                </a><br><br>
                                @endif
                                @if($github_status == 1 )

                                <a href="{{ url('/auth/redirect/github') }}"  class="btn btn-primary-scale-2 btn-modern w-100 text-transform-none font-weight-bold align-items-center d-inline-flex justify-content-center text-3 py-3" data-loading-text="{{ __('message.loading') }}">

                                    <i class="fab fa-github text-5 me-2"></i> {{ __('message.login_github')}}
                                </a><br><br>
                                @endif
                                 @if($linkedin_status == 1 )

                                <a href="{{ url('/auth/redirect/linkedin') }}"  class="btn btn-primary-scale-2 btn-modern w-100 text-transform-none font-weight-bold align-items-center d-inline-flex justify-content-center text-3 py-3" data-loading-text="{{ __('message.loading') }}">

                                    <i class="fab fa-linkedin-in text-5 me-2"></i> {{ __('message.login_linkedin')}}
                                </a><br><br>
                                @endif
                            </div>
                        </div>
                        {!! html()->closeModelForm() !!}
                                          </div>

                <div class="col-md-6 col-lg-6 ps-5">


                    <h2 class="font-weight-bold text-5 mb-0">{{ __('message.register')}}</h2>

                    <form name="registerForm" id="regiser-form">

                        <div class="row">

                            <div class="form-group col {{ $errors->has('first_name') ? 'has-error' : '' }}">

                                <label class="form-label text-color-dark text-3">{{ __('message.first_name')}} <span class="text-color-danger">*</span></label>

                                {!! html()->text('first_name',e(old('first_name')))
   ->class('form-control form-control-lg text-4')
   ->id('first_name') !!}
                                <span id="first_namecheck"></span>
                            </div>

                            <div class="form-group col {{ $errors->has('last_name') ? 'has-error' : '' }}">

                                <label class="form-label text-color-dark text-3">{{ __('message.last_name')}} <span class="text-color-danger">*</span></label>

                                {!! html()->text('last_name')
    ->class('form-control form-control-lg text-4')
    ->id('last_name') !!}
                                <span id="last_namecheck"></span>
                            </div>
                        </div>

                        <div class="row">

                            <div class="form-group col {{ $errors->has('email') ? 'has-error' : '' }}">

                                <label class="form-label text-color-dark text-3">{{ __('message.email_address')}} <span class="text-color-danger">*</span></label>

                                {!! html()->email('email',e(old('email')))
    ->class('form-control form-control-lg text-4')
    ->id('email') !!}
                                <span id="emailcheck"></span>
                            </div>
                        </div>

                        <div class="row">

                            <div class="form-group col {{ $errors->has('company') ? 'has-error' : '' }}">

                                <label class="form-label text-color-dark text-3">{{ __('message.company_name')}} <span class="text-color-danger">*</span></label>

                                {!! html()->text('company')
    ->class('form-control form-control-lg text-4')
    ->id('company') !!}
                                <span id="companycheck"></span>
                            </div>
                        </div>

                        <div class="row">

                            <div class="form-group col {{ $errors->has('address') ? 'has-error' : '' }}">

                                <label class="form-label text-color-dark text-3">{{ __('message.address')}} <span class="text-color-danger">*</span></label>

                                {!! html()->textarea('address',e(old('address')))
    ->class('form-control form-control-lg text-4')
    ->rows(5)
    ->id('address') !!}
                                <span id="addresscheck"></span>
                            </div>
                        </div>

                        <div class="row">

                            <div class="form-group col {{ $errors->has('country') ? 'has-error' : '' }}">

                                <label class="form-label text-color-dark text-3">{{ __('message.country')}} <span class="text-color-danger">*</span></label>

                                <?php $countries = \App\Model\Common\Country::pluck('nicename', 'country_code_char2')->toArray(); ?>
                                {!! html()->select('country', ['' => ''] + $countries, $country)
    ->class('form-select form-control h-auto py-2 selectpicker con')
    ->attribute('data-live-search-style', 'startsWith')
    ->attribute('data-live-search', 'true')
    ->attribute('data-live-search-placeholder', 'Search')
    ->attribute('data-dropup-auto', 'false')
    ->attribute('data-size', '10')
    ->attribute('onchange', 'updateToMobile(this.value)')
    ->id('country') !!}
                                <span id="countrycheck"></span>
                            </div>
                        </div>

                        <div class="row">

                            <div class="form-group col {{ $errors->has('mobile_code') ? 'has-error' : '' }}">

                                <label class="form-label text-color-dark text-3">{{ __('message.mobile')}} <span class="text-color-danger">*</span></label>

{{--                                {!! html()->hidden('mobile')->id('mobile_code_hidden') !!}--}}
                                <input class="form-control form-control-lg rounded" id="mobilenum" name="mobile" type="tel">
                                {!! html()->hidden('mobile_code')
    ->class('form-control form-control-lg text-4')
    ->id('mobile_code') !!}

                                {!! html()->hidden('mobile_country_iso')
                                    ->id('mobile_country_iso') !!}
                                <span id="valid-msg" class="hide"></span>
                                <span id="error-msg" class="hide"></span>
                                <span id="mobile_codecheck"></span>
                                </div>
                        </div>

                      <div class="form-row hidden">
                        <div class="form-group col{{ $errors->has('state') ? 'has-error' : '' }}">
                            {!! html()->label(__('message.state'), 'state') !!}
                            <?php
                            $value = "";
                            if (count($state) > 0) {
                                $value = $state;
                            }
                            if (old('state')) {
                                $value = old('state');
                            }
                            ?>

                            {!! html()->select('state', $states, $value)
    ->class('form-control input-lg')
    ->id('state-list') !!}

                            <span id="statecheck"></span>
                        </div>

                    </div>

                        <div class="row">

                            <div class="form-group col {{ $errors->has('password') ? 'has-error' : '' }}">

                                <label class="form-label text-color-dark text-3">{{ __('message.password')}} <span class="text-color-danger">*</span></label>
                                <div class="input-group">
                                    {!! html()->password('password')
        ->class('form-control form-control-lg text-4')
        ->id('password') !!}
                                        <span class="input-group-text pointer" role="button" onclick="togglePasswordVisibility(this)">
                                            <i class="fa fa-eye-slash"></i>
                                        </span>
                                </div>
                                <span id="password1check"></span>
                            </div>

                            <div class="form-group col {{ $errors->has('password_confirmation') ? 'has-error' : '' }}">

                                <label class="form-label text-color-dark text-3">{{ __('message.re_enter_password')}} <span class="text-color-danger">*</span></label>
                                <div class="input-group">
                                    {!! html()->password('password_confirmation')
         ->class('form-control form-control-lg text-4')
         ->id('confirm_pass') !!}
                                        <span class="input-group-text pointer" role="button" onclick="togglePasswordVisibility(this)">
                                            <i class="fa fa-eye-slash"></i>
                                        </span>
                                </div>
                                <span id="conpasscheck"></span>
                            </div>
                        </div>

                        <small class="text-sm text-muted" id="pswd_info" style="display: none;">
                            <span class="font-weight-bold">{{ \Lang::get('message.password_requirements') }}</span>
                            <ul class="pl-4">
                                @foreach (\Lang::get('message.password_requirements_list') as $requirement)
                                    <li id="{{ $requirement['id'] }}" class="text-danger">{{ $requirement['text'] }}</li>
                                @endforeach
                            </ul>
                        </small>

                        <div class="form-row">
                        <div class="form-group col-lg-6">
                            @if ($status->recaptcha_status === 1)
                               <div id="register_recaptcha"></div>
                                <span id="captchacheck"></span>
                            @elseif($status->v3_recaptcha_status === 1)
                                <input type="hidden" id="g-recaptcha-register" class="g-recaptcha-token" name="g-recaptcha-response" data-recaptcha-action="register">
                            @endif
                        </div>
                    </div>

                        {!! honeypotField('registerForm') !!}

                        <div class="row">
                                @if($status->terms == 1)
                                <div class="form-group col-md-auto">
                                    <div class="custom-control custom-checkbox" style="padding-right: {{ isRtlForLang() ? '0px' : '100px' }};">
                                        <input type="checkbox" value="false" name="terms" id="term" class="custom-control-input">
                                        <label class="custom-control-label text-2 cur-pointer" for="term">
                                            <a href="{{$apiKeys->terms_url}}" target="_blank" class="text-decoration-none">{{ __('message.agree_term')}}</a>
                                        </label>
                                        <br><span id="termscheck"></span>
                                    </div>
                                </div>
                            @endif
                        </div>

                        <div class="row">

                            <div class="form-group col">
                                <button type="submit" name="register" id="register" class="btn btn-dark btn-modern w-100 text-uppercase font-weight-bold text-3 py-3" data-loading-text="{{ __('message.loading')}}" data-original-text="{{ __('message.register')}}">{{ __('message.register')}}</button>

                            </div>
                        </div>
                    </form>
                </div>
                                </div>
                            </div>
                        </div>
            </section>
        </div>
    </div>
@stop
@section('script')
    <!--<script async src="https://www.googletagmanager.com/gtag/js?id=<?php echo $analyticsTag; ?>"></script>-->
@extends('mini_views.recaptcha')
    <!--Start of Tawk.to Script-->
    {!! $everyPageScripts !!}
    <!--End of Tawk.to Script-->

    <script type="text/javascript">

        @php
            $success = session('success') ?? '';
        @endphp

        @if ($success === __('message.registration_complete'))
        window.addEventListener('DOMContentLoaded', function () {
            const input = document.getElementById('username');
            input?.focus();
        });
        @endif


        var input = document.querySelector("#mobilenum");

        function updateToMobile(value) {
            updateCountryCodeAndFlag(input, value)
        }

        // Recaptcha v2
        let login_recaptcha_id;
        let register_recaptcha_id;
        @if($status->recaptcha_status === 1)
        recaptchaFunctionToExecute.push(() => {
            login_recaptcha_id = grecaptcha.render('login_recaptcha', {'sitekey': siteKey});
            register_recaptcha_id = grecaptcha.render('register_recaptcha', {'sitekey': siteKey});
        });
        @endif


        $(document).ready(function () {
            // Cache the selectors for better performance
            var $pswdInfo = $('#pswd_info');
            var $newPassword = $('#password');
            var $length = $('#length');
            var $letter = $('#letter');
            var $capital = $('#capital');
            var $number = $('#number');
            var $special = $('#space');

            // Function to update validation classes
            function updateClass(condition, $element) {
                $element.toggleClass('text-success', condition).toggleClass('text-danger', !condition);
            }

            // Initially hide the password requirements
            $pswdInfo.hide();

            // Show/hide password requirements on focus/blur
            $newPassword.focus(function () {
                $pswdInfo.show();
            }).blur(function () {
                $pswdInfo.hide();
            });

            // Perform real-time validation on keyup
            $newPassword.on('keyup', function () {
                var pswd = $(this).val();

                // Validate the length (8 to 16 characters)
                updateClass(pswd.length >= 8 && pswd.length <= 16, $length);

                // Validate lowercase letter
                updateClass(/[a-z]/.test(pswd), $letter);

                // Validate uppercase letter
                updateClass(/[A-Z]/.test(pswd), $capital);

                // Validate number
                updateClass(/\d/.test(pswd), $number);

                // Validate special character
                updateClass(/[~*!@$#%_+.?:,{ }]/.test(pswd), $special);
            });
        });

    </script>



    {{--     Login validation--}}
    <script>
        $(document).ready(function () {
            function gtag_report_conversion(tag) {
                window.dataLayer = window.dataLayer || [];

                function gtag() {
                    dataLayer.push(arguments);
                }

                gtag('js', new Date());

                gtag('config', tag);
            }
            function placeErrorMessage(error, element, errorMapping = null) {
                if (errorMapping !== null && errorMapping[element.attr("name")]) {
                    $(errorMapping[element.attr("name")]).html(error);
                } else {
                    error.insertAfter(element);
                }
            }

            let alertTimeout;

            function showAlert(type, messageOrResponse) {

                // Generate appropriate HTML
                var html = generateAlertHtml(type, messageOrResponse);

                // Clear any existing alerts and remove the timeout
                $('#alert-container').html(html);
                clearTimeout(alertTimeout); // Clear the previous timeout if it exists

                // Display alert
                window.scrollTo(0, 0);

                // Auto-dismiss after 5 seconds
                alertTimeout = setTimeout(function() {
                    $('#alert-container .alert').fadeOut('slow');
                }, 5000);
            }


            function generateAlertHtml(type, response) {
                // Determine alert styling based on type
                const isSuccess = type === 'success';
                const iconClass = isSuccess ? 'fa-check-circle' : 'fa-ban';
                const alertClass = isSuccess ? 'alert-success' : 'alert-danger';

                // Extract message and errors
                const message = response.message || response || 'An error occurred. Please try again.';
                const errors = response.errors || null;

                let html = `<div class="alert ${alertClass} alert-dismissible">` +
                    `<i class="fa ${iconClass}"></i> ` +
                    `${message}` +
                    '<button type="button" class="btn-close" data-dismiss="alert" aria-hidden="true"></button>';

                html += '</div>';

                return html;
            }

            $.validator.addMethod("email_or_username", function(value, element) {
                // Email regex (improved version)
                var emailRegex = /^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/;
                var isEmail = emailRegex.test(value);

                return this.optional(element) || isEmail;
            }, "{{ __('message.emailSettings_details.email') }}");

            $.validator.addMethod("regex", function(value, element, regexp) {
                var re = new RegExp(regexp);
                return this.optional(element) || re.test(value);
            }, "{{ __('message.invalid_format') }}");

            $.validator.addMethod("validPhone", function(value, element) {
                return validatePhoneNumber(element);
            }, "{{ __('message.error_valid_number') }}");

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

            $(document).on('change', '#term', function () {
                $(this).val($(this).val() === "false" ? "true" : "false");
            });


            $('#formoid').validate({
                ignore: ":hidden:not(.g-recaptcha-response)",
                rules: {
                    email_username: {
                        required: true,
                        email:false,
                        email_or_username: true
                    },
                    password1: {
                        required: true,
                    },
                    "g-recaptcha-response": {
                        recaptchaRequired: true
                    }
                },
                messages: {
                    email_username: {
                        required: "{{ __('message.username_or_email') }}",
                        email_or_username: "{{ __('message.emailSettings_details.email') }}"
                    },
                    password1: {
                        required: "{{ __('message.received_password_enter') }}",
                    },
                    "g-recaptcha-response": {
                        recaptchaRequired: "{{ __('message.recaptcha_required') }}"
                    }
                },
                unhighlight: function(element) {
                    $(element).removeClass("is-valid");
                },
                errorPlacement: function (error, element) {
                    var errorMapping = {
                        "email_username": "#error-login-email",
                        "password1": "#error-login-password",
                        "g-recaptcha-response": "#loginrobot-verification"
                    };

                    placeErrorMessage(error, element, errorMapping);
                },
                submitHandler: function (form) {
                    form.submit();
                }
            });

            $("#regiser-form").validate({
                ignore: ":hidden:not(.g-recaptcha-response):not([name^='register'])",
                rules: {
                    first_name: {
                        required: true,
                        regex: /^[a-zA-Z][a-zA-Z' -]{0,98}$/
                    },
                    last_name: {
                        required: true,
                        regex: /^[a-zA-Z][a-zA-Z' -]{0,98}$/
                    },
                    email: {
                        required: true,
                        email: true,
                        regex: /^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/,
                    },
                    company: {
                        required: true
                    },
                    address: {
                        required: true,
                    },
                    country: {
                        required: true
                    },
                    mobile: {
                        required: true,
                        validPhone: true
                    },
                    password: {
                        required: true,
                        regex: /^(?=.*[A-Z])(?=.*[a-z])(?=.*\d)(?=.*[~*!@$#%_+.?:,{ }])[A-Za-z\d~*!@$#%_+.?:,{ }]{8,16}$/,
                    },
                    password_confirmation: {
                        required: true,
                        equalTo: "#password"
                    },
                    terms: {
                        required: function() {
                            return {{ $status->terms == 1 ? 'true' : 'false' }};
                        }
                    },
                    "g-recaptcha-response": {
                        recaptchaRequired: true
                    }
                },
                messages: {
                    first_name: {
                        required: "{{ __('message.login_validation.firstname_required') }}",
                        regex: "{{ __('message.login_validation.firstname_regex') }}"
                    },
                    last_name: {
                        required: "{{ __('message.login_validation.lastname_required') }}",
                        regex: "{{ __('message.login_validation.lastname_regex') }}"
                    },
                    email: {
                        required: "{{ __('message.login_validation.email_required') }}",
                        email: "{{ __('message.contact_error_email') }}",
                        regex: "{{ __('message.login_validation.email_regex') }}"
                    },
                    company: {
                        required: "{{ __('message.login_validation.company_required') }}"
                    },
                    address: {
                        required: "{{ __('message.login_validation.address_required') }}",
                    },
                    country: {
                        required: "{{ __('message.login_validation.country_required') }}"
                    },
                    mobile: {
                        required: "{{ __('message.login_validation.mobile_required') }}",
                    },
                    password: {
                        required: "{{ __('message.login_validation.password_required') }}",
                        regex: "{{ __('message.strong_password') }}"
                    },
                    password_confirmation: {
                        required: "{{ __('message.login_validation.confirm_password_required') }}",
                        equalTo: "{{ __('message.login_validation.confirm_password_equalto') }}"
                    },
                    terms: {
                        required: "{{ __('message.login_validation.terms_conditions_required') }}"
                    },
                    "g-recaptcha-response": {
                        recaptchaRequired: "{{ __('message.recaptcha_required') }}"
                    }

                },
                unhighlight: function(element) {
                    $(element).removeClass("is-valid"); // Remove "is-invalid" but don't add "is-valid"
                },
                errorPlacement: function (error, element) {
                    var errorMapping = {
                        "first_name": "#first_namecheck",
                        "last_name": "#last_namecheck",
                        "email": "#emailcheck",
                        "company": "#companycheck",
                        "address": "#addresscheck",
                        "country": "#countrycheck",
                        "mobile": "#mobile_codecheck",
                        "password": "#password1check",
                        "password_confirmation": "#conpasscheck",
                        "terms": "#termscheck",
                        "g-recaptcha-response": "#captchacheck"
                    };

                    placeErrorMessage(error, element, errorMapping);
                },
                submitHandler: function (form) {
                    $('#mobile_code').val(input.getAttribute('data-dial-code'));
                    $('#mobile_country_iso').val(input.getAttribute('data-country-iso').toUpperCase());
                    input.value = input.value.replace(/\D/g, '');
                    var formData = $(form).serialize();
                    let submitButton = $('#register');
                    var tag = "<?php echo $analyticsTag; ?>";
                    if (tag !== "") {
                        gtag_report_conversion(tag);
                    }
                    $.ajax({
                        url: '{{url("auth/register")}}',
                        method: 'POST',
                        data: formData,
                        beforeSend: function () {
                            submitButton.prop('disabled', true).html(submitButton.data('loading-text'));
                        },
                        success: function(response) {
                            @if($status->terms)
                            document.getElementById('term').value = false;
                            @endif
                            form.reset();
                            if (response.data.need_verify === 1) {
                                window.location.href = "{{ url('/verify') }}";
                            }else {
                                showAlert('success', response.message);
                            }
                        },
                        error: function(data, status, error) {
                            var response = data.responseJSON ? data.responseJSON : JSON.parse(data.responseText);

                            if (response.errors) {
                                $.each(response.errors, function(field, messages) {
                                    if (field === 'register' || field === 'g-recaptcha-response') {
                                        showAlert('error', messages[0]);
                                        return;
                                    }
                                    var validator = $('#regiser-form').validate();

                                    var fieldSelector = $(`[name="${field}"]`).attr('name');  // Get the name attribute of the selected field

                                    validator.showErrors({
                                        [fieldSelector]: messages[0]
                                    });
                                });
                            } else {
                                showAlert('error', response);
                            }
                        },
                        complete: function () {
                            submitButton.prop('disabled', false).html(submitButton.data('original-text'));
                        }
                    });

                    return false;
                }
            });
        });
    </script>

    <script>

        var data = '{{json_encode($value)}}';
        var state = JSON.parse(data.replace(/&quot;/g, '"'));
        // console.log(state)
        $(document).ready(function () {
            var val = $("#country").val();
            getCountryAttr(val);
        });

        function getCountryAttr(val) {
            if (val != "") {
                getState(val);
                getCode(val);
            } else {
                $("#state-list").html('<option value="">{{ __('message.error_select_country')}}</option>').val('');
            }

//        getCurrency(val);

        }

        function getState(val) {
            $.ajax({
                type: "GET",
                url: "{{url('get-loginstate')}}/" + val,
                data: {'country_id': val, '_token': "{{csrf_token()}}"},//'country_id=' + val,
                success: function (data) {

                    $("#state-list").html('<option value="">{{ __('message.error_select_country')}}</option>').val('');


                    $("#state-list").html(data).val(state.id);
                }
            });
        }


        function getCode(val) {
            $.ajax({
                type: "GET",
                url: "{{url('get-code')}}",
                data: {'country_id': val, '_token': "{{csrf_token()}}"},//'country_id=' + val,
                success: function (data) {
                    $("#mobile_code").val(data);
                    $("#mobile_code_hidden").val(data);
                }
            });
        }

        function getCurrency(val) {
            $.ajax({
                type: "GET",
                url: "{{url('get-currency')}}",
                data: {'country_id': val, '_token': "{{csrf_token()}}"},//'country_id=' + val,
                success: function (data) {
                    $("#currency").val(data);
                }
            });
        }
    </script>
    <!-- Google Code for Help Desk Pro | Campaign 001 Conversion Page
    In your html page, add the snippet and call
    goog_report_conversion when someone clicks on the
    chosen link or button. -->
    <script type="text/javascript">
        //<![CDATA[
        goog_snippet_vars = function () {
            var w = window;
            w.google_conversion_id = 1027628032;
            w.google_conversion_label = "uBhoCLT3i3AQgLiB6gM";
            w.google_remarketing_only = false;
        }
        // DO NOT CHANGE THE CODE BELOW.
        goog_report_conversion = function (url) {
            goog_snippet_vars();
            window.google_conversion_format = "3";
            var opt = new Object();
            opt.onload_callback = function () {
                if (typeof (url) != 'undefined') {
                    window.location = url;
                }
            }
            var conv_handler = window['google_trackConversion'];
            if (typeof (conv_handler) == 'function') {
                conv_handler(opt);
            }
            fbq('track', 'CompleteRegistration');
        }
        //]]>
    </script>
    <!-- Google Code for Help Desk Pro | Campaign 001 Conversion Page
    In your html page, add the snippet and call
    goog_report_conversion when someone clicks on the
    chosen link or button. -->
    <script type="text/javascript">
        //<![CDATA[
        goog_snippet_vars = function () {
            var w = window;
            w.google_conversion_id = 1027628032;
            w.google_conversion_label = "uBhoCLT3i3AQgLiB6gM";
            w.google_remarketing_only = false;
        }
        // DO NOT CHANGE THE CODE BELOW.
        goog_report_conversion = function (url) {
            goog_snippet_vars();
            window.google_conversion_format = "3";
            var opt = new Object();
            opt.onload_callback = function () {
                if (typeof (url) != 'undefined') {
                    window.location = url;
                }
            }
            var conv_handler = window['google_trackConversion'];
            if (typeof (conv_handler) == 'function') {
                conv_handler(opt);
            }
            fbq('track', 'CompleteRegistration');
        }
        //]]>
    </script>
    <script type="text/javascript"
            src="//www.googleadservices.com/pagead/conversion_async.js">
    </script>
    <!-- Facebook Pixel Code -->
    <!-- <script>
    !function(f,b,e,v,n,t,s)
    {if(f.fbq)return;n=f.fbq=function(){n.callMethod?
    n.callMethod.apply(n,arguments):n.queue.push(arguments)};
    if(!f._fbq)f._fbq=n;n.push=n;n.loaded=!0;n.version='2.0';
    n.queue=[];t=b.createElement(e);t.async=!0;
    t.src=v;s=b.getElementsByTagName(e)[0];
    s.parentNode.insertBefore(t,s)}(window,document,'script',
    'https://connect.facebook.net/en_US/fbevents.js');
     fbq('init', '308328899511239');
    fbq('track', 'PageView');

    </script> -->

    <script type="text/javascript"
            src="//www.googleadservices.com/pagead/conversion_async.js">
    </script>
    <script>
        $(document).ready(function () {

            $.ajaxSetup({headers: {'csrftoken': '{{ csrf_token() }}'}});

            //Initialize tooltips
            $('.nav-tabs > li a[title]').tooltip();
            $('.nav-tabs .active a[href="#step1"]').click(function () {
                $('.wizard-inner').css('display', 'none');
            })
            //Wizard
            if (!$('.nav-tabs .active a[href="#step1"]')) {
                $('.wizard-inner').css('display', 'block');
            }
            $('a[data-toggle="tab"]').on('show.bs.tab', function (e) {

                var $target = $(e.target);

                if ($target.parent().hasClass('disabled')) {
                    return false;
                }
            });

            /*$(".next-step").click(function (e) {
                $('.wizard-inner').show();
                var $active = $('.wizard .nav-tabs li.active');
                $active.next().removeClass('disabled');
                nextTab($active);
                window.scrollTo(0, 10);

            });*/

            $(".prev").click(function (e) {

                var $active = $('.wizard .nav-tabs li.active');
                $active.next().removeClass('disabled');
                prevTab($active);
                $('.wizard-inner').css('display', 'block');
            });
        });

        function nextTab(elem) {

            $(elem).next().find('a[data-toggle="tab"]').click();
        }

        function prevTab(elem) {
            $(elem).prev().find('a[data-toggle="tab"]').click();
        }
    </script>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/js/select2.min.js"></script>
    <noscript>
        <img height="1" width="1"
             src="https://www.facebook.com/tr?id=308328899511239&ev=PageView
&noscript=1"/>
    </noscript>
    <!-- End Facebook Pixel Code -->
@stop