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

.system-error {
    display: block;
    margin-top: 5px; /* Adds spacing between file input and error message */
    color: #dc3545; /* Bootstrap's danger color for errors */
    font-size: 0.875em; /* Slightly smaller font size */
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


        {!! Form::model($user,['id' => 'userUpdateForm','url'=>'profile', 'method' => 'PATCH','files'=>true]) !!}


        <div class="card card-secondary card-outline">
            <div class="card-header">
                <h3 class="card-title">Edit Profile</h3>


            </div>
            <div class="card-body">

                <div class="form-group {{ $errors->has('first_name') ? 'has-error' : '' }}">
                    <!-- first name -->
                    {!! Form::label('first_name',null,['class' => 'required'],Lang::get('message.first_name')) !!}
                    {!! Form::text('first_name',null,['class' => 'form-control'. ($errors->has('first_name') ? ' is-invalid' : '')]) !!}
                    @error('first_name')
                    <span class="error-message"> {{$message}}</span>
                    @enderror
                    <div class="input-group-append">
                    </div>
                </div>

                <div class="form-group {{ $errors->has('last_name') ? 'has-error' : '' }}">
                    <!-- last name -->
                    {!! Form::label('last_name',null,['class' => 'required'],Lang::get('message.last_name')) !!}
                    {!! Form::text('last_name',null,['class' => 'form-control'. ($errors->has('last_name') ? ' is-invalid' : '')]) !!}
                    @error('last_name')
                    <span class="error-message"> {{$message}}</span>
                    @enderror
                    <div class="input-group-append">
                    </div>
                </div>
                <div class="form-group {{ $errors->has('user_name') ? 'has-error' : '' }}">
                    <!-- mobile -->
                    {!! Form::label('user_name',null,['class' => 'required'],Lang::get('message.user_name')) !!}
                    {!! Form::text('user_name',null,['class' => 'form-control'. ($errors->has('user_name') ? ' is-invalid' : '')]) !!}
                    @error('user_name')
                    <span class="error-message"> {{$message}}</span>
                    @enderror
                    <div class="input-group-append">
                    </div>
                </div>


                <div class="form-group">
                    <!-- email -->
                    {!! Form::label('email',null,['class' => 'required'],Lang::get('message.email')) !!}
                     {!! Form::text('email',null,['class' => 'form-control'. ($errors->has('email') ? ' is-invalid' : '')]) !!}
                    @error('email')
                    <span class="error-message"> {{$message}}</span>
                    @enderror
                    <div class="input-group-append">
                    </div>
                </div>

                <div class="form-group {{ $errors->has('company') ? 'has-error' : '' }}">
                    <!-- company -->
                    {!! Form::label('company',null,['class' => 'required'],Lang::get('message.company')) !!}
                    {!! Form::text('company',null,['class' => 'form-control'. ($errors->has('company') ? ' is-invalid' : '')]) !!}
                    @error('company')
                    <span class="error-message"> {{$message}}</span>
                    @enderror
                    <div class="input-group-append">
                    </div>
                </div>


                <div class="form-group {{ $errors->has('mobile_code') ? 'has-error' : '' }}">
                  {!! Form::label('mobile',null,['class' => 'required'],Lang::get('message.mobile')) !!}
                     {!! Form::hidden('mobile_code',null,['id'=>'mobile_code_hidden']) !!}

                    {!! Form::input('tel', 'mobile', $user->mobile, ['class' => 'form-control selected-dial-code'. ($errors->has('mobile') ? ' is-invalid' : ''), 'id' => 'mobile_code']) !!}

                    {!! Form::hidden('mobile_country_iso',null,['id' => 'mobile_country_iso']) !!}
                    @error('mobile')
                    <span class="error-message"> {{$message}}</span>
                    @enderror
                    <span id="error-msg" class="hide"></span>
                    <span id="valid-msg" class="hide"></span>
                    <div class="input-group-append">
                    </div>


                </div>


                <div class="form-group {{ $errors->has('address') ? 'has-error' : '' }}">
                    <!-- phone number -->
                    {!! Form::label('address',null,['class' => 'required'],Lang::get('message.address')) !!}
                    {!! Form::textarea('address',null,['class' => 'form-control'. ($errors->has('address') ? ' is-invalid' : '')]) !!}
                    @error('address')
                    <span class="error-message"> {{$message}}</span>
                    @enderror
                    <div class="input-group-append">
                    </div>
                </div>

                <div class="row">

                    <div class="col-md-6 form-group {{ $errors->has('town') ? 'has-error' : '' }}">
                        <!-- mobile -->
                        {!! Form::label('town',Lang::get('message.town')) !!}
                        {!! Form::text('town',null,['class' => 'form-control'. ($errors->has('town') ? ' is-invalid' : '')]) !!}

                    </div>

                    <div class="col-md-6 form-group {{ $errors->has('timezone_id') ? 'has-error' : '' }}">
                        <!-- mobile -->
                        {!! Form::label('timezone_id',Lang::get('message.timezone'),['class' => 'required']) !!}
                        <!-- {!! Form::select('timezone_id',[''=>'Select','Timezones'=>$timezones],null,['class' => 'form-control']) !!} -->
                        {!! Form::select('timezone_id', [Lang::get('message.choose')=>$timezones],null,['class' => 'form-control select2 selectpicker'. ($errors->has('timezone_id') ? ' is-invalid' : ''),'data-live-search'=>'true','required','data-live-search-placeholder' => 'Search','data-dropup-auto'=>'false','data-size'=>'10']) !!}
                        @error('timezone_id')
                        <span class="error-message"> {{$message}}</span>
                        @enderror

                    </div>

                </div>

                <div class="row">
                    <?php $countries = \App\Model\Common\Country::pluck('nicename', 'country_code_char2')->toArray(); ?>
                    <div class="col-md-6 form-group {{ $errors->has('country') ? 'has-error' : '' }}">
                         {!! Form::label('country',Lang::get('message.country'),['class' => 'required']) !!}



                        {!! Form::select('country',[Lang::get('message.choose')=>$countries],null,['class' => 'form-control select2'. ($errors->has('country') ? ' is-invalid' : ''),'id'=>'country','onChange'=>'getCountryAttr(this.value)','data-live-search'=>'true','required','data-live-search-placeholder' => 'Search','data-dropup-auto'=>'false','data-size'=>'10']) !!}
                        <!-- name -->
                        @error('country')
                        <span class="error-message"> {{$message}}</span>
                        @enderror
                    </div>
                    <div class="col-md-6 form-group {{ $errors->has('state') ? 'has-error' : '' }}">
                        <!-- name -->
                        {!! Form::label('state',Lang::get('message.state'),['class' => 'required']) !!}
                        <!--{!! Form::select('state',[],null,['class' => 'form-control'. ($errors->has('state') ? ' is-invalid' : ''),'id'=>'state-list']) !!}-->
                        <select name="state" id="state-list" class="form-control">
                            @if(count($state)>0)
                            <option value="{{$state['id']}}">{{$state['name']}}</option>
                            @endif
                            <option value="">Select State</option>
                            @foreach($states as $key=>$value)
                            <option value="{{$key}}">{{$value}}</option>
                            @endforeach
                        </select>
                        @error('state')
                        <span class="error-message"> {{$message}}</span>
                        @enderror
                    </div>


                </div>
                 <div class="row">
                <div class="col-md-6 form-group {{ $errors->has('zip') ? 'has-error' : '' }}">
                    <!-- mobile -->
                    {!! Form::label('zip',null,Lang::get('message.zip')) !!}
                    {!! Form::text('zip',null,['class' => 'form-control'. ($errors->has('zip') ? ' is-invalid' : ''),'id'=>'zip1']) !!}
                    <span id="zip-error-msg"></span>
                    @error('zip')
                    <span class="error-message"> {{$message}}</span>
                    @enderror
                </div>

                <div class="col-md-6 form-group" id= "gstin">
                    <!-- mobile -->
                    {!! Form::label('GSTIN',null,'GSTIN') !!}
                    {!! Form::number('gstin',null,['class' => 'form-control'. ($errors->has('gstin') ? ' is-invalid' : ''),'id'=>'gstin1']) !!}
                    <span id="gst-error-msg"></span>
                    @error('gstin')
                    <span class="error-message"> {{$message}}</span>
                    @enderror
                </div>
              </div>

                <div class="form-group {{ $errors->has('profile_pic') ? 'has-error' : '' }}">
                    <!-- profile pic -->
                    {!! Form::label('profile_pic',Lang::get('message.profile-picture')) !!}

                    <div class="input-group">
                    {!! Form::file('profile_pic',['id'=>'profile_pic']) !!}
                    </div>
                    <span class="system-error" id="profilepic-err-Msg"></span>

                    <br>

                       <?php
                        $user = \DB::table('users')->find(\Auth::user()->id);
                        ?>
                        <img src="{{ Auth::user()->profile_pic }}" class="img-thumbnail" style="height: 50px;">

                </div>

                <button type="submit" class="btn btn-primary pull-right" id="submit" data-loading-text="<i class='fa fa-circle-o-notch fa-spin'>&nbsp;</i> Updating..."><i class="fas fa-sync">&nbsp;&nbsp;</i>{!!Lang::get('message.update')!!}</button></h4>

                {!! Form::token() !!}
                {!! Form::close() !!}
            </div>
        </div>
    </div>
    <div class="col-md-6">
        {!! Form::model($user, ['url' => 'password', 'method' => 'PATCH', 'id' => 'changePasswordForm']) !!}

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
                    {!! Form::label('old_password',null,['class' => 'required'],Lang::get('message.old_password')) !!}
                    <div class="input-group">
                    {!! Form::password('old_password',['placeholder'=>'Password','class' => 'form-control'. ($errors->has('old_password') ? ' is-invalid' : '')]) !!}
                        <div class="input-group-append">
                                        <span class="input-group-text" role="button" onclick="togglePasswordVisibility(this)">
                                            <i class="fa fa-eye-slash"></i>
                                        </span>
                        </div>
                    </div>
                    @error('old_password')
                    <span class="error-message"> {{$message}}</span>
                    @enderror
                    <span class="glyphicon glyphicon-lock form-control-feedback"></span>
                </div>
                <!-- new password -->
                <div class="form-group has-feedback {{ $errors->has('new_password') ? 'has-error' : '' }}">
                    {!! Form::label('new_password',null,['class' => 'required'],Lang::get('message.new_password')) !!}
                    <div class="input-group has-validation">
                    {!! Form::password('new_password',['placeholder'=>'New Password','class' => 'form-control'. ($errors->has('new_password') ? ' is-invalid' : '')]) !!}
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
                    @error('new_password')
                    <span class="error-message"> {{$message}}</span>
                    @enderror
                    <span class="glyphicon glyphicon-lock form-control-feedback"></span>
                </div>
                <!-- confirm password -->
                <div class="form-group has-feedback {{ $errors->has('confirm_password') ? 'has-error' : '' }}">
                    {!! Form::label('confirm_password',null,['class' => 'required'],Lang::get('message.confirm_password')) !!}
                    <div class="input-group">
                    {!! Form::password('confirm_password',['placeholder'=>'Confirm Password','class' => 'form-control'. ($errors->has('confirm_password') ? ' is-invalid' : '')]) !!}
                    <div class="input-group-append">
                                        <span class="input-group-text" role="button" onclick="togglePasswordVisibility(this)">
                                            <i class="fa fa-eye-slash"></i>
                                        </span>
                    </div>

                </div>
                    <span class="glyphicon glyphicon-lock form-control-feedback"></span>
                    @error('confirm_password')
                    <span class="error-message"> {{$message}}</span>
                    @enderror
                </div>
                    <button type="submit" class="btn btn-primary pull-right" id="submit" data-loading-text="<i class='fa fa-circle-o-notch fa-spin'>&nbsp;</i> Updating..."><i class="fas fa-sync">&nbsp;&nbsp;</i>{!!Lang::get('message.update')!!}</button>
                {!! Form::close() !!}
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


{!! Form::close() !!}
<script src="{{asset('common/js/2fa.js')}}"></script>
<script>
    $('#submit').on('click',function(e) {
        var gstin = $('#gstin1');
        gstinerrorMsg = document.querySelector("#gst-error-msg");
        if (gstin.val() !== '') {
            if (gstin.val().length != 15) {
                e.preventDefault();
                gstinerrorMsg.innerHTML = @json(trans('message.valid_gst_number'));
                $('#gstin1').addClass('is-invalid');
                $('#gstin1').css("border-color", "#dc3545");
                $('#gst-error-msg').css({
                    "width": "100%",
                    "margin-top": ".25rem",
                    "font-size": "80%",
                    "color": "#dc3545"
                });
            }
        }

        var zip=$('#zip1');
        ziperrorMsg = document.querySelector("#zip-error-msg");

        if(zip.val()!=''){
            if(!zipRegex(zip.val())){
                ziperrorMsg.innerHTML = @json(trans('message.valid_zip'));
                e.preventDefault();
                $('#zip1').addClass('is-invalid');
                $('#zip1').css("border-color", "#dc3545");
                $('#zip-error-msg').css({
                    "width": "100%",
                    "margin-top": ".25rem",
                    "font-size": "80%",
                    "color": "#dc3545"
                });
            }
        }
    })

    document.getElementById("zip1").addEventListener('input',function(){
        ziperrorMsg = document.querySelector("#zip-error-msg");
        $('#zip1').removeClass('is-invalid');
        $('#zip1').css("border-color", "silver");
        ziperrorMsg.innerHTML = '';
    });

    function zipRegex(val) {
        var re = /^[a-zA-Z0-9]+$/;
        return re.test(val);
    }
    $(document).ready(function() {
        var fup = document.getElementById('profile_pic');
        var errMsg=document.getElementById('profilepic-err-Msg');
        $('#profile_pic').on('change',function(e){

            var fileName = fup.value;
            var filesize=e.target.files[0];
            var ext = fileName.substring(fileName.lastIndexOf('.') + 1);
            const maxSize = 2 * 1024 * 1024;
            if(filesize.size>maxSize){
                errMsg.innerText=@json(trans('message.image_invalid_size'));
                document.getElementById('submit').disabled = true;
                return false;
            }
            if(ext !=="jpeg" && ext!=="jpg" && ext!=='png') {
                errMsg.innerText=@json(trans('message.image_invalid_message'));
                document.getElementById('submit').disabled = true;
                return false;
            }else if(filesize.size>maxSize){
                errMsg.innerText=@json(trans('message.image_invalid_size'));
                document.getElementById('submit').disabled = true;
                return false;
            }else {
                errMsg.innerText='';
                document.getElementById('submit').disabled = false;
                return true;
            }
        });

        const userRequiredFields = {
            first_name:@json(trans('message.user_edit_details.add_first_name')),
            last_name:@json(trans('message.user_edit_details.add_last_name')),
            email:@json(trans('message.user_edit_details.add_email')),
            company:@json(trans('message.user_edit_details.add_company')),
            address:@json(trans('message.user_edit_details.add_address')),
            mobile_code:@json(trans('message.user_edit_details.add_mobile')),
            user_name:@json(trans('message.user_edit_details.add_user_name')),
        };

        $('#submit').on('click', function (e) {
            const userFields = {
                first_name: $('#first_name'),
                last_name: $('#last_name'),
                email: $('#email'),
                company: $('#company'),
                address: $('#address'),
                user_name: $('#user_name'),
            };


            // Clear previous errors
            Object.values(userFields).forEach(field => {
                field.removeClass('is-invalid');
                field.next().next('.error').remove();

            });

            let isValid = true;

            const showError = (field, message) => {
                field.addClass('is-invalid');
                field.next().after(`<span class='error invalid-feedback'>${message}</span>`);
            };

            // Validate required fields
            Object.keys(userFields).forEach(field => {
                if (!userFields[field].val()) {
                    showError(userFields[field], userRequiredFields[field]);
                    isValid = false;
                }
            });

            if (isValid && !validateEmail(userFields.email.val())) {
                showError(userFields.email, @json(trans('message.user_edit_details.add_valid_email')));
                isValid = false;
            }


            if (isValid && !validName(userFields.first_name.val())) {
                showError(userFields.first_name, @json(trans('message.user_edit_details.add_valid_name')));
                isValid = false;
            }

            if (isValid && !validName(userFields.last_name.val())) {
                showError(userFields.last_name, @json(trans('message.user_edit_details.add_valid_lastname')));

                isValid = false;
            }

            // If validation fails, prevent form submission
            if (!isValid) {
                e.preventDefault();
            }
        });
            // Function to remove error when input'id' => 'changePasswordForm'ng data
            const removeErrorMessage = (field) => {
                field.classList.remove('is-invalid');
                const error = field.nextElementSibling;
                if (error && error.classList.contains('error')) {
                    error.remove();
                }
            };

            // Add input event listeners for all fields
            ['first_name','last_name','email','company','user_name','address','mobile','zip1'].forEach(id => {
                document.getElementById(id).addEventListener('input', function () {
                    removeErrorMessage(this);

            });
        });




        function validName(string){
            const nameRegex=/^[A-Za-z][A-Za-z-\s]+$/;
            return nameRegex.test(string);
        }

        function validateEmail(email) {

            const emailPattern = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;

            return emailPattern.test(email);

        }

    });


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

        // Function to remove error when input'id' => 'changePasswordForm'ng data
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
    window.onload = function () {
             if (country === 'IN') {
                 $('#gstin').show();
             } else {
                 $('#gstin').hide();
             }
         };
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

         $('#submit').on('click',function(e) {
             if(telInput.val()===''){
                 e.preventDefault();
                 errorMsg.classList.remove("hide");
                 errorMsg.innerHTML = @json(trans('message.user_edit_details.add_phone_number'));
                 $('#mobile_code').addClass('is-invalid');
                 $('#mobile_code').css("border-color", "#dc3545");
                 $('#error-msg').css({"width": "100%", "margin-top": ".25rem", "font-size": "80%", "color": "#dc3545"});
             }
         });

     $('.intl-tel-input').css('width', '100%');
    telInput.on('input blur', function () {
      reset();
        if ($.trim(telInput.val())) {
            if (validatePhoneNumber(telInput.get(0))) {
              $('#mobile_code').css("border-color","");
              validMsg.classList.remove("hide");
            } else {
            errorMsg.classList.remove("hide");
            errorMsg.innerHTML = "Please enter a valid number";
             $('#mobile_code').css("border-color", "#dc3545");
             $('#error-msg').css({"width": "100%", "margin-top": ".25rem", "font-size": "80%", "color": "#dc3545"});
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