@extends('themes.default1.layouts.master')
@section('title')
User Profile
@stop

@section('content-header')
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

    <div class="col-sm-6">
        <h1>User Profile</h1>
    </div>
    <div class="col-sm-6">

        <ol class="breadcrumb float-sm-right">
            <li class="breadcrumb-item"><a href="{{url('/')}}"><i class="fa fa-dashboard"></i> Home</a></li>
            <li class="breadcrumb-item active">Edit Profile</li>
        </ol>
    </div><!-- /.col -->


<style>

        .bootstrap-select.btn-group .dropdown-menu li a {
    margin-left: -12px !important;
}
 .btn-group>.btn:first-child {
    margin-left: 0;
    background-color: white;

   }
.bootstrap-select.btn-group .dropdown-toggle .filter-option {
    color:#555;
}


</style>
@endsection
@section('content')
<div class="row">

    <div class="col-md-6">


        {!! html()->form('PATCH', 'profile')->files()->model($user) !!}


        <div class="card card-secondary card-outline">
            <div class="card-header">
                <h3 class="card-title">Edit Profile</h3>


            </div>
            <div class="card-body">

                <div class="form-group {{ $errors->has('first_name') ? 'has-error' : '' }}">
                    <!-- first name -->
                    {!! html()->label(Lang::get('message.first_name'))->class('required')->for('first_name') !!}
                    {!! html()->text('first_name')->class('form-control') !!}

                </div>

                <div class="form-group {{ $errors->has('last_name') ? 'has-error' : '' }}">
                    {!! html()->label(Lang::get('message.last_name'))->class('required')->for('last_name') !!}
                    {!! html()->text('last_name')->class('form-control') !!}
                </div>

                <div class="form-group {{ $errors->has('user_name') ? 'has-error' : '' }}">
                    {!! html()->label(Lang::get('message.user_name'))->class('required')->for('user_name') !!}
                    {!! html()->text('user_name')->class('form-control') !!}
                </div>

                <div class="form-group">
                    {!! html()->label(Lang::get('message.email'))->class('required')->for('email') !!}
                    {!! html()->text('email')->class('form-control') !!}
                </div>

                <div class="form-group {{ $errors->has('company') ? 'has-error' : '' }}">
                    {!! html()->label(Lang::get('message.company'))->class('required')->for('company') !!}
                    {!! html()->text('company')->class('form-control') !!}
                </div>


                <div class="form-group {{ $errors->has('mobile_code') ? 'has-error' : '' }}">
                    {!! html()->label(Lang::get('message.mobile'))->class('required')->for('mobile') !!}
                    {!! html()->hidden('mobile_code')->id('mobile_code_hidden') !!}
                    <!--  <input class="form-control selected-dial-code"  id="mobile_code" value="{{$user->mobile}}" name="mobile" type="tel"> -->

                    {!! html()->input('tel', 'mobile', $user->mobile)->class('form-control selected-dial-code')->id('mobile_code') !!}
                    {!! html()->hidden('mobile_country_iso')->id('mobile_country_iso') !!}
                    <span id="valid-msg" class="hide"></span>
                       <span id="error-msg" class="hide"></span>
                </div>


                <div class="form-group {{ $errors->has('address') ? 'has-error' : '' }}">
                    <!-- phone number -->
                    {!! html()->label(Lang::get('message.address'))->class('required')->for('address') !!}
                    {!! html()->textarea('address')->class('form-control') !!}

                </div>

                <div class="row">

                    <div class="col-md-6 form-group {{ $errors->has('town') ? 'has-error' : '' }}">
                        <!-- mobile -->
                        {!! html()->label(Lang::get('message.town'))->for('town') !!}
                        {!! html()->text('town')->class('form-control') !!}

                    </div>

                    <div class="col-md-6 form-group {{ $errors->has('timezone_id') ? 'has-error' : '' }}">
                        <!-- mobile -->
                        {!! html()->label(Lang::get('message.timezone'))->for('timezone_id')->class('required') !!}
                        <!-- {!! html()->select('timezone_id')->options(['' => 'Select'] + $timezones)->class('form-control') !!} -->
                        {!! html()->select('timezone_id')->options([Lang::get('message.choose') => $timezones])->class('form-control selectpicker')->attribute('data-live-search', 'true')->attribute('required', true)->attribute('data-live-search-placeholder', 'Search')->attribute('data-dropup-auto', 'false')->attribute('data-size', '10') !!}


                    </div>

                </div>

                <div class="row">
                    <?php $countries = \App\Model\Common\Country::pluck('nicename', 'country_code_char2')->toArray(); ?>
                    <div class="col-md-6 form-group {{ $errors->has('country') ? 'has-error' : '' }}">
                        {!! html()->label(Lang::get('message.country'), 'country')->class('required') !!}
                        {!! html()->select('country')->options([Lang::get('message.choose') => $countries])->class('form-control select2')->id('country')->attribute('onChange', 'getCountryAttr(this.value)')->attribute('data-live-search', 'true')->attribute('required', 'required')->attribute('data-live-search-placeholder', 'Search')->attribute('data-dropup-auto', 'false')->attribute('data-size', '10')->attribute('disabled', 'disabled') !!}
                        <!-- name -->




                    </div>
                    <div class="col-md-6 form-group {{ $errors->has('state') ? 'has-error' : '' }}">
                        <!-- name -->
                        {!! html()->label(Lang::get('message.state'))->class('required') !!}
                        <!--{!! html()->select('state', [])->class('form-control')->id('state-list') !!} -->
                        <select name="state" id="state-list" class="form-control">
                            @if(count($state)>0)
                            <option value="{{$state['id']}}">{{$state['name']}}</option>
                            @endif
                            <option value="">Select State</option>
                            @foreach($states as $key=>$value)
                            <option value="{{$key}}">{{$value}}</option>
                            @endforeach
                        </select>

                    </div>


                </div>
                 <div class="row">
                     <div class="col-md-6 form-group {{ $errors->has('zip') ? 'has-error' : '' }}">
                         {!! html()->label(Lang::get('message.zip'))->for('zip') !!}
                         {!! html()->text('zip')->class('form-control') !!}
                     </div>

                     <div class="col-md-6 form-group" id="gstin">
                         {!! html()->label('GSTIN')->for('gstin') !!}
                         {!! html()->text('gstin')->class('form-control') !!}
                     </div>
                 </div>

                <div class="form-group {{ $errors->has('profile_pic') ? 'has-error' : '' }}">
                    <!-- profile pic -->
                    {!! html()->label(Lang::get('message.profile-picture'))->for('profile_pic') !!}
                    {!! html()->file('profile_pic') !!}
                    <br>

                       <?php
                        $user = \DB::table('users')->find(\Auth::user()->id);
                        ?>
                        <img src="{{ Auth::user()->profile_pic }}" class="img-thumbnail" style="height: 50px;">

                </div>
                <button type="submit" class="btn btn-primary pull-right" id="submit" data-loading-text="<i class='fa fa-circle-o-notch fa-spin'>&nbsp;</i> Updating..."><i class="fas fa-sync">&nbsp;&nbsp;</i>{!!Lang::get('message.update')!!}</button></h4>

                {!! html()->token() !!}
                {!! html()->form()->close() !!}

            </div>
        </div>
    </div>
    <div class="col-md-6">
        {!! html()->model($user)->patch('password')->id('changePasswordForm') !!}

        <div class="card card-secondary card-outline">
            <div class="card-header">
                <h3 class="card-title">{{Lang::get('message.change-password')}}</h3>


            </div>




            <div class="card-body">
                @if(Session::has('success1'))
                <div class="alert alert-success alert-dismissable">
                    <i class="fa fa-ban"></i>
                    <b>{{Lang::get('message.alert')}}!</b> {{Lang::get('message.success')}}.
                    <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
                    {{Session::get('success1')}}
                </div>
                @endif
                <!-- fail message -->
                @if(Session::has('fails1'))
                <div class="alert alert-danger alert-dismissable">
                    <i class="fa fa-ban"></i>
                    <b>{{Lang::get('message.alert')}}!</b> {{Lang::get('message.failed')}}.
                    <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
                    {{Session::get('fails1')}}
                </div>
                @endif
                <!-- old password -->
                <div class="form-group has-feedback {{ $errors->has('old_password') ? 'has-error' : '' }}">
                    {!! html()->label(Lang::get('message.old_password'))->class('required')->for('old_password') !!}
                    <div class="input-group">
                        {!! html()->password('old_password')->placeholder('Password')->class('form-control') !!}
                        <div class="input-group-append">
                                        <span class="input-group-text" role="button" onclick="togglePasswordVisibility(this)">
                                            <i class="fa fa-eye-slash"></i>
                                        </span>
                        </div>
                    </div>
                    <span class="glyphicon glyphicon-lock form-control-feedback"></span>
                </div>
                <!-- new password -->
                <div class="form-group has-feedback {{ $errors->has('new_password') ? 'has-error' : '' }}">
                    {!! html()->label(Lang::get('message.new_password'))->class('required')->for('new_password') !!}
                    <div class="input-group has-validation">
                        {!! html()->password('new_password')->placeholder('New Password')->class('form-control') !!}
                    <div class="input-group-append">
                                        <span class="input-group-text" role="button" onclick="togglePasswordVisibility(this)">
                                            <i class="fa fa-eye-slash"></i>
                                        </span>
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
                    <span class="glyphicon glyphicon-lock form-control-feedback"></span>
                </div>
                <!-- cofirm password -->
                <div class="form-group has-feedback {{ $errors->has('confirm_password') ? 'has-error' : '' }}">
                    {!! html()->label(Lang::get('message.confirm_password'))->class('required')->for('confirm_password') !!}
                    <div class="input-group">
                        {!! html()->password('confirm_password')->placeholder('Confirm Password')->class('form-control') !!}
                    <div class="input-group-append">
                                        <span class="input-group-text" role="button" onclick="togglePasswordVisibility(this)">
                                            <i class="fa fa-eye-slash"></i>
                                        </span>
                    </div>
                </div>
                    <span class="glyphicon glyphicon-lock form-control-feedback"></span>
                </div>
                    <button type="submit" class="btn btn-primary pull-right" id="submit" data-loading-text="<i class='fa fa-circle-o-notch fa-spin'>&nbsp;</i> Updating..."><i class="fas fa-sync">&nbsp;&nbsp;</i>{!!Lang::get('message.update')!!}</button>
                    {!! html()->form()->close() !!}
            </div>
        </div>



   


 @include('themes.default1.user.2faModals')


        <div class="card card-secondary card-outline">
            <div class="card-header">
                <h3 class="card-title">{{Lang::get('message.setup_2fa')}}</h3>


            </div>

        <div class="card-body">
            <div class="row">
                <div class="col-md-10">
                <h5>
                    @if($is2faEnabled ==0)
                    
                    <img src="{{asset('common/images/authenticator.png')}}" alt="Authenticator" style="margin-top: -6px!important;" class="img-responsive img-circle img-sm">&nbsp;Authenticator App
                    @else
                    <img src="{{asset('common/images/authenticator.png')}}" alt="Authenticator" style="margin-top: -6px!important;" class="img-responsive img-circle img-sm">&nbsp;2-Step Verification is ON since {{getTimeInLoggedInUserTimeZone($dateSinceEnabled)}}
                    <br><br><br>
                    <div class="row">
                 <div class="col-md-6">
                     <button class="btn btn-primary" id="viewRecCode">View Recovery Code</button>
                 </div>
             </div>
                    @endif
                </h5>
                </div>
                <div class="col-md-2">
                  <label class="switch toggle_event_editing pull-right">

                         <input type="checkbox" value="{{$is2faEnabled}}"  name="modules_settings"
                          class="checkbox" id="2fa">
                          <span class="slider round"></span>
                    </label>
                 </div>

            </div>
        </div>
        </div>
    </div>
</div>


{!! html()->form()->close() !!}
<script src="{{asset('common/js/2fa.js')}}"></script>
<script>
    $(document).ready(function() {
        const requiredFields = {
            old_password: @json(trans('message.old_pass_required')),
            new_password: @json(trans('message.new_pass_required')),
            confirm_password: @json(trans('message.confirm_pass_required')),
        };

        const pattern = /^(?=.*[A-Z])(?=.*[a-z])(?=.*\d)(?=.*[~*!@$#%_+.?:,{ }])[A-Za-z\d~*!@$#%_+.?:,{ }]{8,16}$/;

        $('#changePasswordForm').on('submit', function(e) {
            const fields = {
                old_password: $('#old_password'),
                new_password: $('#new_password'),
                confirm_password: $('#confirm_password'),
            };

            // Clear previous errors
            Object.values(fields).forEach(field => {
                field.removeClass('is-invalid');
                field.next().next('.error').remove();
            });

            let isValid = true;

            const showError = (field, message) => {
                field.addClass('is-invalid');
                field.next().after(`<span class='error invalid-feedback'>${message}</span>`);
            };

            // Validate required fields
            Object.keys(fields).forEach(field => {
                if (!fields[field].val()) {
                    showError(fields[field], requiredFields[field]);
                    isValid = false;
                }
            });

            if (isValid && fields.old_password.val() === fields.new_password.val()) {
                showError(fields.new_password,  @json(trans('message.new_password_different')));
                isValid = false;
            }

            // Validate new password against the regex
            if (isValid && !pattern.test(fields.new_password.val())) {
                showError(fields.new_password, @json(trans('message.strong_password')));
                isValid = false;
            }

            // Check if new password and confirm password match
            if (isValid && fields.new_password.val() !== fields.confirm_password.val()) {
                showError(fields.confirm_password, @json(trans('message.password_mismatch')));
                isValid = false;
            }

            // If validation fails, prevent form submission
            if (!isValid) {
                e.preventDefault();
            }
        });

        // Function to remove error when inputting data
        const removeErrorMessage = (field) => {
            field.classList.remove('is-invalid');
            const error = field.nextElementSibling;
            if (error && error.classList.contains('error')) {
                error.remove();
            }
        };

        // Add input event listeners for all fields
        ['new_password', 'old_password', 'confirm_password'].forEach(id => {
            document.getElementById(id).addEventListener('input', function() {
                removeErrorMessage(this);
            });
        });
    });

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

    // get the country data from the plugin
     $(document).ready(function(){
         $(function () {
             //Initialize Select2 Elements
             $('.select2').select2()
         });
    var country = $('#country').val();
    if(country == 'IN') {
        $('#gstin').show()
    } else {
        $('#gstin').hide();
    }
    getCode(country);
    var telInput = $('#mobile_code');
    addressDropdown = $("#country");
     errorMsg = document.querySelector("#error-msg"),
    validMsg = document.querySelector("#valid-msg");
     var reset = function() {
      errorMsg.innerHTML = "";
      errorMsg.classList.add("hide");
      validMsg.classList.add("hide");
    };
     $('.intl-tel-input').css('width', '100%');
    telInput.on('input blur', function () {
      reset();
        if ($.trim(telInput.val())) {
            if (validatePhoneNumber(telInput.get(0))) {
              $('#mobile_code').css("border-color","");
              validMsg.classList.remove("hide");
              $('#submit').attr('disabled',false);
            } else {
            errorMsg.classList.remove("hide");
            errorMsg.innerHTML = "Please enter a valid number";
             $('#mobile_code').css("border-color","red");
             $('#error-msg').css({"color":"red","margin-top":"5px"});
             $('#submit').attr('disabled',true);
            }
        }
    });

     addressDropdown.change(function() {
     telInput.intlTelInput("setCountry", $(this).val());
             if ($.trim(telInput.val())) {
            if (validatePhoneNumber(telInput.get(0))) {
              $('#mobile_code').css("border-color","");
              errorMsg.classList.add("hide");
              $('#submit').attr('disabled',false);
            } else {
            errorMsg.classList.remove("hide");
             errorMsg.innerHTML = "Please enter a valid number";
             $('#mobile_code').css("border-color","red");
             $('#error-msg').css({"color":"red","margin-top":"5px"});
             $('#submit').attr('disabled',true);
            }
        }
    });
    $('input').on('focus', function () {
        $(this).parent().removeClass('has-error');
    });

    var mobInput = document.querySelector("#mobile_code");
    updateCountryCodeAndFlag(mobInput, "{{ $user->mobile_country_iso }}")
    $('form').on('submit', function (e) {
        $('#mobile_country_iso').val(telInput.attr('data-country-iso').toUpperCase());
        $('#mobile_code_hidden').val(telInput.attr('data-dial-code'));
        telInput.val(telInput.val().replace(/\D/g, ''));
    });


});
</script>
<script>



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
                // $("#mobile_code").val(data);
                $("#mobile_code_hidden").val(data);
            }
        });
    }
</script>
@stop