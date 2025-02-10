@extends('themes.default1.layouts.front.master')
@section('title')
Profile
@stop
@section('nav-profile')
active
@stop
@section('page-heading')
    {{ __('message.profile')}}
@stop
@section('breadcrumb')
@if(Auth::check())
        <li><a class="text-primary" href="{{url('my-invoices')}}">{{ __('message.home')}}</a></li>
    @else
         <li><a class="text-primary" href="{{url('login')}}">{{ __('message.home')}}</a></li>
    @endif
     <li class="active text-dark">{{ __('message.profile')}}</li>
@stop
@section('content')
<style>
.switch {
  position: relative;
  display: inline-block;
  width: 60px;
  height: 34px;
}

.switch input {display:none;}

.slider {
  position: absolute;
  cursor: pointer;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background-color: #ccc;
  -webkit-transition: .4s;
  transition: .4s;
}

.slider:before {
  position: absolute;
  content: "";
  height: 26px;
  width: 26px;
  left: 4px;
  bottom: 4px;
  background-color: white;
  -webkit-transition: .4s;
  transition: .4s;
}

input:checked + .slider {
  background-color: #2196F3;
}

input:focus + .slider {
  box-shadow: 0 0 1px #2196F3;
}

input:checked + .slider:before {
  -webkit-transform: translateX(26px);
  -ms-transform: translateX(26px);
  transform: translateX(26px);
}

/* Rounded sliders */
.slider.round {
  border-radius: 34px;
}

.slider.round:before {
  border-radius: 50%;
}
.scrollit {
    overflow:scroll;
    height:600px;
}
</style>
<style>

    .required:after{
        content:'*';
        color:red;
        padding:0px;
    }


        .bootstrap-select.btn-group .dropdown-menu li a {
    margin-left: -12px !important;
}
 .btn-group>.btn:first-child {
    margin-left: 0;
    background-color: white;

   }
   .open>.dropdown-menu {
  display: block;
}
.bootstrap-select.btn-group .dropdown-toggle .filter-option {
    color:#555;
}
</style>
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/js/select2.min.js"></script>


     <div id= "alertMessage"></div>
     <div id= "error"></div>
  @include('themes.default1.user.2faModals')


        <div class="container pt-3 pb-2">

            <div class="row pt-2">

                <div class="col-lg-3 mt-4 mt-lg-0">


                    <aside class="sidebar mt-2 mb-5">

                        <ul class="nav nav-list flex-column">

                            <li class="nav-item">

                                <a class="nav-link active" id="profile_detail" href="#profile" data-bs-toggle="tab" data-hash data-hash-offset="0" data-hash-offset-lg="500" data-hash-delay="500">{{ __('message.my_profile')}}</a>
                            </li>

                            <li class="nav-item">

                                <a class="nav-link" id="change_password" href="#password" data-bs-toggle="tab" data-hash data-hash-offset="0" data-hash-offset-lg="500" data-hash-delay="500">{{ __('message.change_password')}}</a>
                            </li>

                            <li class="nav-item">

                                <a class="nav-link" id="two_fa" href="#twofa" data-bs-toggle="tab" data-hash data-hash-offset="0" data-hash-offset-lg="500" data-hash-delay="500">{{ __('message.setup_2fa')}}</a>
                            </li>
                        </ul>
                    </aside>
                </div>

                <div class="col-lg-9">

                    <div class="tab-pane tab-pane-navigation active" id="profile" role="tabpanel">

                           <div class="row">
                             {!! Form::model($user,['method' => 'PATCH','files'=>true , 'id' => 'client_form']) !!}
                               <div class="d-flex justify-content-center mb-4" id="profile_img">
                                   <div class="profile-image-outer-container">
                                       <?php
                                       $user = \DB::table('users')->find(\Auth::user()->id);
                                       ?>
                                       <div class="profile-image-inner-container bg-color-primary">
                                           <img id="previewImage" src="{{ Auth::user()->profile_pic }}" alt="Profile Picture">
                                           <span class="profile-image-button bg-color-dark">
                <i class="fas fa-camera text-light"></i>
            </span>
                                       </div>
                                       <input type="file" name="profile_pic" id="profilePic" class="form-control profile-image-input" accept="image/*">
                                   </div>
                               </div>


                            <div class="col-lg-12 order-1 order-lg-2">

                                    <div class="form-group row {{ $errors->has('first_name') ? 'has-error' : '' }}">
                                        <label class="col-lg-3 col-form-label form-control-label line-height-9 pt-2 text-2 required">{{ __('message.first_name')}}</label>
                                        <div class="col-lg-9">
                                        {!! Form::text('first_name',null,['class' => 'form-control text-3 h-auto py-2 ','id'=>'firstName']) !!}
                                        <h6 id="firstNameCheck"></h6>
                                        </div>
                                    </div>
                                    <div class="form-group row {{ $errors->has('last_name') ? 'has-error' : '' }}">
                                        <label class="col-lg-3 col-form-label form-control-label line-height-9 pt-2 text-2 required">{{ __('message.last_name')}}</label>
                                        <div class="col-lg-9">
                                         {!! Form::text('last_name',null,['class' => 'form-control text-3 h-auto py-2','id'=>'lastName']) !!}
                                         <h6 id="lastNameCheck"></h6>
                                        </div>
                                    </div>
                                    <div class="form-group row {{ $errors->has('email') ? 'has-error' : '' }}">
                                        <label class="col-lg-3 col-form-label form-control-label line-height-9 pt-2 text-2 required">{{ __('message.email')}}</label>
                                        <div class="col-lg-9">
                                             {!! Form::text('email',null,['class' => 'form-control text-3 h-auto py-2','id'=>'Email']) !!}
                                             <h6 id="emailCheck"></h6>
                                        </div>
                                    </div>
                                    <div class="form-group row {{ $errors->has('mobile_code') ? 'has-error' : '' }}">
                                        <label class="col-lg-3 col-form-label form-control-label line-height-9 pt-2 text-2 required">{{ __('message.mobile')}}</label>
                                        <div class="col-lg-9">
                                              {!! Form::hidden('mobile_code',null,['id'=>'code_hidden']) !!}
                                               <!--<input class="form-control selected-dial-code"  id="mobile_code" value="{{$user->mobile}}" name="mobile" type="tel"> -->

<<<<<<< HEAD
                                            {!! Form::input('tel', 'mobile', $user->mobile, ['class' => 'form-control selected-dial-code', 'id' => 'incode' ]) !!}
=======
                                            {!! Form::input('tel', 'mobile', $user->mobile, ['class' => 'form-control selected-dial-code', 'dir'=> app()->getLocale() == 'ar' ? 'rtl' : 'ltr', 'id' => 'incode' , 'data-country-iso' => $user->mobile_country_iso]) !!}
>>>>>>> 74aaff522 (Fixes #3191)
                                            {!! Form::hidden('mobile_country_iso',null,['id' => 'mobile_country_iso']) !!}
                                            <span id="invalid-msg" class="hide"></span>
                                               <span id="inerror-msg"></span>
                                        </div>
                                    </div>
                                    <div class="form-group row {{ $errors->has('company') ? 'has-error' : '' }}">
                                        <label class="col-lg-3 col-form-label form-control-label line-height-9 pt-2 text-2 required">{{ __('message.front_company')}}</label>
                                        <div class="col-lg-9">
                                            {!! Form::text('company',null,['class' => 'form-control text-3 h-auto py-2','id'=>'Company']) !!}
                                             <h6 id="companyCheck"></h6>
                                        </div>
                                    </div>
                                    <div class="form-group row {{ $errors->has('address') ? 'has-error' : '' }}">
                                        <label class="col-lg-3 col-form-label form-control-label line-height-9 pt-2 text-2 required">{{ __('message.address')}}</label>
                                        <div class="col-lg-9">
                                        {!! Form::textarea('address',null,['class' => 'form-control text-3 h-auto py-2','id'=>'Address']) !!}
                                           <h6 id="addressCheck"></h6>

                                        </div>
                                    </div>
                                    <div class="form-group row {{ $errors->has('town') ? 'has-error' : '' }}">
                                        <label class="col-lg-3 col-form-label form-control-label line-height-9 pt-2 text-2"></label>
                                        <div class="col-lg-6">
                                            {!! Form::text('town', null, ['class' => 'form-control text-3 h-auto py-2', 'id' => 'Town', 'placeholder' => trans('message.enter_town')]) !!}
                                        </div>
                                        <div class="col-lg-3 {{ $errors->has('state') ? 'has-error' : '' }}">
                                           <select name="state" class="form-control text-3 h-auto py-2">

                                                @if(count($state)>0)

                                                    <option value="{{$state['id']}}">{{$state['name']}}</option>

                                                <option value="">{{ __('message.select_state')}}</option>
                                                @foreach($states as $key=>$value)

                                                    <option value="{{$key}}">{{$value}}</option>
                                                @endforeach
                                               @endif
                                            </select>
                                            </div>
                                    </div>
                                     <div class="form-group row {{ $errors->has('=country') ? 'has-error' : '' }}">
                                        <label class="col-lg-3 col-form-label form-control-label line-height-9 pt-2 text-2">{{ __('message.country')}}</label>
                                        <div class="col-lg-9">
                                            {!! Form::text('country', $selectedCountry, [ 'class' => 'form-control input-lg', 'onChange' => 'getCountryAttr(this.value);', 'readonly' => 'readonly', 'title' => trans('message.admin_update_country'), 'data-toggle' => 'tooltip', 'data-placement' => 'top' ]) !!}

                                            {!! Form::hidden('country',null,['class' => 'form-control input-lg', 'id'=>'country']) !!}
                                         <h6 id="countryCheck"></h6>

                                        </div>
                                    </div>
                                    <div class="form-group row {{ $errors->has('timezone_id') ? 'has-error' : '' }}">
                                        <label class="col-lg-3 col-form-label form-control-label line-height-9 pt-2 text-2">{{ __('message.time_zone')}}</label>
                                        <div class="col-lg-9">
                                            <div class="custom-select-1">
                                            {!! Form::select('timezone_id',[Lang::get('message.choose')=>$timezones],null,['class' => 'form-control input-lg','id'=>'timezone']) !!}

                                            </div>
                                        </div>
                                    </div>
                                    <div class="form-group row">
                                        <div class="form-group col-lg-9">

                                        </div>
                                        <div class="form-group col-lg-3">
                                            <button type="submit" id="submit" class="btn btn-dark font-weight-bold text-3 btn-modern float-end" data-original-text="{{__('message.update')}}" data-loading-text="{{ __('message.loading') }}">{{ __('message.update')}}</button>
                                        </div>
                                    </div>


                            </div>
                            {!! Form::close() !!}
                        </div>


                    </div>

                    <div class="tab-pane tab-pane-navigation" id="password" role="tabpanel">

                        <div class="row">

                            <div class="col-lg-12 order-1 order-lg-2">

                                {!! Form::model($user,['url'=>'my-password' , 'method' => 'PATCH' , 'id' => 'changePasswordForm']) !!}

                                    <div class="form-group row {{ $errors->has('old_password') ? 'has-error' : '' }}">
                                        <label class="col-lg-3 col-form-label form-control-label line-height-9 pt-2 text-2 required">{{ __('message.old_password')}}</label>
                                        <div class="col-lg-9">
                                            <div class="input-group">
                                            {!! Form::password('old_password',['class' => 'form-control text-3 h-auto py-2','id'=>'old_password']) !!}
                                                <div class="input-group-append">
                                        <span class="input-group-text" role="button" onclick="togglePasswordVisibility(this)">
                                            <i class="fa fa-eye-slash"></i>
                                        </span>
                                                </div>
                                            </div>
                                             <h6 id="oldpasswordcheck"></h6>
                                        </div>
                                    </div>

                                    <div class="form-group row {{ $errors->has('new_password') ? 'has-error' : '' }}">
                                        <label class="col-lg-3 col-form-label form-control-label line-height-9 pt-2 text-2 required">{{ __('message.new_password')}}</label>
                                        <div class="col-lg-9">
                                            <div class="input-group">
                                            {!! Form::password('new_password',['class' => 'form-control text-3 h-auto py-2','id'=>'new_password']) !!}
                                                <div class="input-group-append">
                                        <span class="input-group-text" role="button" onclick="togglePasswordVisibility(this)">
                                            <i class="fa fa-eye-slash"></i>
                                        </span>
                                                </div>
                                            </div>
                                            <h6 id="newpasswordcheck"></h6>
                                            <small class="text-sm text-muted" id="pswd_info" style="display: none;">
                                                <span class="font-weight-bold">{{ \Lang::get('message.password_requirements') }}</span>
                                                <ul class="pl-4">
                                                    @foreach (\Lang::get('message.password_requirements_list') as $requirement)
                                                        <li id="{{ $requirement['id'] }}" class="text-danger">{{ $requirement['text'] }}</li>
                                                    @endforeach
                                                </ul>
                                            </small>
                                        </div>
                                    </div>

                                    <div class="form-group row {{ $errors->has('confirm_password') ? 'has-error' : '' }}">
                                        <label class="col-lg-3 col-form-label form-control-label line-height-9 pt-2 text-2 required">{{ __('message.confirm_password')}}</label>
                                        <div class="col-lg-9">
                                            <div class="input-group">
                                            {!! Form::password('confirm_password',['class' => 'form-control text-3 h-auto py-2','id'=>'confirm_password']) !!}
                                                <div class="input-group-append">
                                        <span class="input-group-text" role="button" onclick="togglePasswordVisibility(this)">
                                            <i class="fa fa-eye-slash"></i>
                                        </span>
                                                </div>
                                            </div>
                                            <h6 id ="confirmpasswordcheck"></h6>
                                        </div>
                                    </div>
                                    <div class="form-group row">
                                        <div class="form-group col-lg-9">

                                        </div>
                                        <div class="form-group col-lg-3">
                                            <button type="submit" class="btn btn-dark font-weight-bold text-3 btn-modern float-end" data-loading-text="{{ __('message.loading')}}" id="password">{{ __('message.update')}}</button>
                                        </div>
                                    </div>
                               {!! Form::close() !!}
                            </div>
                        </div>
                    </div>

                    <div class="tab-pane tab-pane-navigation" id="twofa" role="tabpanel">

                        <div class="row pt-5">

                            <div class="d-flex">

                                <div class="pe-3 pe-sm-5 pb-3 pb-sm-0 mt-2">


                                    @if($is2faEnabled ==0)
                                    <img src="{{asset('common/images/authenticator.png')}}" alt="Authenticator" style="margin-top: -6px!important;height:26px;" class="img-responsive img-circle img-sm">&nbsp;{{ __('message.authenticator_app')}}
                                @else
                                    <img src="{{asset('common/images/authenticator.png')}}" alt="Authenticator" style="margin-top: -6px!important;height:26px;" class="img-responsive img-circle img-sm">&nbsp;{{ __('message.two_step_verfication')}} {{getTimeInLoggedInUserTimeZone($dateSinceEnabled)}}
                                    <br><br><br>
                                    <div class="row">
                                        <div class="col-md-4">
                                            <button class="btn btn-dark btn-modern w-100 text-uppercase font-weight-bold text-3 py-3" id="viewRecCode" style="width: 250px !important;">{{ __('message.recovery_code')}}</button>
                                        </div>
                                    </div>
                                @endif
                                </div>

                                <div class="form-check form-switch">


                      <input value="{{$is2faEnabled}}" id="2fa" name="modules_settings" class="form-check-input" style="padding-right: 2rem;padding-left: 2rem;padding-top: 1rem!important;padding-bottom: 1rem!important;" type="checkbox" role="switch" <?php if ($is2faEnabled == 1) { echo 'checked'; } ?>>

                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

        </div>


<script src="{{asset('common/js/2fa.js')}}"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-select/1.13.18/js/bootstrap-select.min.js"></script>

                    <script>
                        $(document).ready(function() {
                            // Cache the selectors for better performance
                            var $pswdInfo = $('#pswd_info');
                            var $newPassword = $('#new_password');
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
                            $newPassword.focus(function() {
                                $pswdInfo.show();
                            }).blur(function() {
                                $pswdInfo.hide();
                            });

                            // Perform real-time validation on keyup
                            $newPassword.on('keyup', function() {
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
<script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/js/select2.min.js"></script>
<script src="{{asset('common/js/intlTelInput.js')}}"></script>

<script type="text/javascript">

// get the country data from the plugin
     $(document).ready(function(){
           $(function () {
             //Initialize Select2 Elements
             $('.select2').select2()
         });

    var incountry = $('#country').val();

    getCode(incountry);


    $('input').on('focus', function () {
        $(this).parent().removeClass('has-error');
    });
});


   function getCountryAttr(val) {
        if(val == 'IN') {
            $('#gstin').show()
        } else {
            $('#gstin').hide()
        }
        getState(val);
        getCode(val);
//        getCurrency(val);

    }



       function getState(val) {


        $.ajax({
            type: "GET",
              url: "{{url('get-state')}}/" + val,
            data: 'country_id=' + val,
            success: function (data) {
                $("#state-list").html(data);
            }
        });
    }
    function getCode(val) {
        $.ajax({
            type: "GET",
            url: "{{url('get-code')}}",
            data: 'country_id=' + val,
            success: function (data) {
                $("#code_hidden").val(data);
            }
        });
    }


</script>


<script>
    $(document).ready(function() {
        var mobInput = document.querySelector("#incode");
        updateCountryCodeAndFlag(mobInput, "{{ $user->mobile_country_iso }}")
        let alertTimeout;
        function showAlert(type, messageOrResponse) {

            // Generate appropriate HTML
            var html = generateAlertHtml(type, messageOrResponse);

            // Clear any existing alerts and remove the timeout
            $('#alertMessage').html(html);
            clearTimeout(alertTimeout); // Clear the previous timeout if it exists

            window.scrollTo(0, 0);


            alertTimeout = setTimeout(() => {
                $('#alertMessage .alert').fadeOut('slow', function () {
                    $(this).remove();
                });
            }, 5000);
        }


        function generateAlertHtml(type, response) {
            // Determine alert styling based on type
            const isSuccess = type === 'success';
            const iconClass = isSuccess ? 'fa-check-circle' : 'fa-ban';
            const alertClass = isSuccess ? 'alert-success' : 'alert-danger';

            // Extract message and errors
            const message = response.message || response || '{{ __('message.error_occurred') }}';
            const errors = response.errors || null;

            // Build base HTML
            let html = `<div class="alert ${alertClass} alert-dismissible">` +
                `<i class="fa ${iconClass}"></i> ` +
                `${message}` +
                '<button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>';

            html += '</div>';

            return html;
        }
        $.validator.addMethod("validPhone", function(value, element) {
            return validatePhoneNumber(element);
        }, "Please enter a valid phone number.");
        function placeErrorMessage(error, element, errorMapping = null) {
            if (errorMapping !== null && errorMapping[element.attr("name")]) {
                $(errorMapping[element.attr("name")]).html(error);
            } else {
                error.insertAfter(element);
            }
        }
        $.validator.addMethod("regex", function(value, element, regexp) {
            var re = new RegExp(regexp);
            return this.optional(element) || re.test(value);
        }, "Invalid format.");
        document.getElementById('profilePic').addEventListener('change', function(event) {
            const file = event.target.files[0];

            if (!file) return;

            // Allowed image types
            const allowedTypes = ["image/png", "image/jpg", "image/jpeg"];
            if (!allowedTypes.includes(file.type)) {
                showAlert('error',"{{ __('message.image_allowed') }}");
                return;
            }

            // Check file size (2MB limit)
            if (file.size > 2097152) {
                showAlert('error',"{{ __('message.image_max') }}");
                return;
            }

            const reader = new FileReader();
            reader.onload = function(e) {
                document.getElementById('previewImage').src = e.target.result;
            };
            reader.readAsDataURL(file);
        });
        $("#client_form").validate({
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
                    regex: /^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/,
                },
                mobile: {
                    required: true,
                    validPhone: true
                },
                company: {
                    required: true
                },
                address: {
                    required: true
                },
                town: {
                    required: true
                },
                state: {
                    required: true
                },
                country: {
                    required: true
                },
                timezone_id: {
                    required: true
                }
            },
            messages: {
                first_name: {
                    required: "{{ __('message.contact_error_firstname') }}",
                    regex: "{{ __('message.enter_valid_firstname') }}"
                },
                last_name: {
                    required: "{{ __('message.contact_error_lastname') }}",
                    regex: "{{ __('message.enter_valid_lastname') }}"
                },
                email: {
                    required: "{{ __('message.enter_your_email') }}",
                    regex: "{{ __('message.contact_error_email') }}"
                },
                mobile: {
                    required: "{{ __('message.error_mobile') }}",
                    validPhone: "{{ __('message.error_valid_number') }}"
                },
                company: {
                    required: "{{ __('message.enter_your_company_name') }}"
                },
                address: {
                    required: "{{ __('message.enter_your_address') }}"
                },
                town: {
                    required: "{{ __('message.enter_your_town') }}"
                },
                state: {
                    required: "{{ __('message.enter_your_state') }}"
                },
                country: {
                    required: "{{ __('message.enter_your_country') }}"
                },
                timezone_id: {
                    required: "{{ __('message.enter_your_timezone') }}"
                }
            },
            unhighlight: function (element) {
                $(element).removeClass("is-valid");
            },
            errorPlacement: function (error, element) {
                var errorMapping = {
                    "mobile": "#inerror-msg",
                };

                placeErrorMessage(error, element, errorMapping);
            },
            submitHandler: function (form) {
                var intelInput = $('#incode');
                $('#code_hidden').val(mobInput.getAttribute('data-dial-code'));
                let submitButton = $('#submit');
                $('#mobile_country_iso').val(intelInput.attr('data-country-iso').toUpperCase());
                intelInput.val(intelInput.val().replace(/\D/g, ''));
                var formData = new FormData(form);
                $.ajax({
                    url: '{{url('my-profile')}}',
                    type: 'POST',
                    data: formData,
                    contentType: false,
                    processData: false,
                    success: function (response) {
                        showAlert('success', response);
                    },
                    beforeSend: function () {
                        submitButton.prop('disabled', true).html(submitButton.data('loading-text'));
                    },
                    error: function (data) {
                        var response = data.responseJSON ? data.responseJSON : JSON.parse(data.responseText);

                        if (response.errors) {
                            $.each(response.errors, function(field, messages) {
                                var validator = $('#client_form').validate();

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
            }
        });

        $('#changePasswordForm').validate({
            rules: {
                old_password: {
                    required: true
                },
                new_password: {
                    required: true,
                    regex: /^(?=.*[A-Z])(?=.*[a-z])(?=.*\d)(?=.*[~*!@$#%_+.?:,{ }])[A-Za-z\d~*!@$#%_+.?:,{ }]{8,16}$/
                },
                confirm_password: {
                    required: true,
                    equalTo: '#new_password'
                }
            },
            messages: {
                old_password: "{{ __('message.old_pass_required') }}",
                new_password: {
                    required: "{{ __('message.new_pass_required') }}",
                    regex: "{{ __('message.strong_password') }}"
                },
                confirm_password: {
                    required: "{{ __('message.confirm_pass_required') }}",
                    equalTo: "{{ __('message.password_mismatch') }}"
                }
            },
            unhighlight: function (element) {
                $(element).removeClass("is-valid");
            },
            errorPlacement: function (error, element) {
                var errorMapping = {
                    "old_password": "#oldpasswordcheck",
                    "new_password": "#newpasswordcheck",
                    "confirm_password": "#confirmpasswordcheck"
                };

                placeErrorMessage(error, element, errorMapping);
            },
            submitHandler: function (form) {
                var formData = $(form).serialize();
                $.ajax({
                    url: '{{url('my-password')}}',
                    type: 'PATCH',
                    data: formData,
                    success: function (response) {
                        form.reset();
                        showAlert('success', response);
                    },
                    error: function (data) {
                        var response = data.responseJSON ? data.responseJSON : JSON.parse(data.responseText);

                        if (response.errors) {
                            $.each(response.errors, function(field, messages) {
                                var validator = $('#changePasswordForm').validate();

                                var fieldSelector = $(`[name="${field}"]`).attr('name');  // Get the name attribute of the selected field

                                validator.showErrors({
                                    [fieldSelector]: messages[0]
                                });
                            });
                        } else {
                            showAlert('error', response);
                        }
                    }
                });
            }
        });
    });
</script>
<!-- <script src="{{asset('common/js/licCode.js')}}"></script> -->

@stop


