@extends('themes.default1.layouts.master')
@section('title')
Create User
@stop
@section('content-header')
    <div class="col-sm-6">
        <h1>Create New User</h1>
    </div>
    <div class="col-sm-6">
        <ol class="breadcrumb float-sm-right">
            <li class="breadcrumb-item"><a href="{{url('/')}}"><i class="fa fa-dashboard"></i> Home</a></li>
             <li class="breadcrumb-item"><a href="{{url('clients')}}"><i class="fa fa-dashboard"></i> Users</a></li>
            <li class="breadcrumb-item active">Create User</li>
        </ol>
    </div><!-- /.col -->


@stop

@section('content')



    <div class="card card-secondary card-outline">

        <div class="card-body">
            {!! Form::open(['url'=>'clients','method'=>'post','id'=>'userUpdateForm']) !!}

            <div class="row">

            <div class="col-md-12">



                <div class="row">

                    <div class="col-md-3 form-group {{ $errors->has('first_name') ? 'has-error' : '' }}">
                        <!-- first name -->
                        {!! Form::label('first_name',Lang::get('message.first_name'),['class'=>'required']) !!}
                        {!! Form::text('first_name', null, [
                            'class' => 'form-control' . ($errors->has('first_name') ? ' is-invalid' : '')
                        ]) !!}
                        @error('first_name')
                        <span class="error-message"> {{$message}}</span>
                            @enderror

                        <div class="input-group-append">

                        </div>
                    </div>

                    <div class="col-md-3 form-group {{ $errors->has('last_name') ? 'has-error' : '' }}">
                        <!-- last name -->
                        {!! Form::label('last_name',Lang::get('message.last_name'),['class'=>'required']) !!}
                        {!! Form::text('last_name',null,['class' => 'form-control'.($errors->has('last_name') ? ' is-invalid' : '')]) !!}
                        @error('last_name')
                        <span class="error-message"> {{$message}}</span>
                        @enderror
                        <div class="input-group-append">
                        </div>
                    </div>


                    <div class="col-md-3 form-group {{ $errors->has('email') ? 'has-error' : '' }}">
                        <!-- email -->
                        {!! Form::label('email',Lang::get('message.email'),['class'=>'required']) !!}
                        {!! Form::text('email',null,['class' => 'form-control'.($errors->has('email') ? ' is-invalid' : '')]) !!}
                        @error('email')
                        <span class="error-message"> {{$message}}</span>
                        @enderror
                        <span id="email-error-msg" class="hide"></span>
                        <div class="input-group-append">
                        </div>
                    </div>
                    <div class="col-md-3 form-group {{ $errors->has('user_name') ? 'has-error' : '' }}">
                        <!-- mobile -->
                        {!! Form::label('user_name',Lang::get('message.user_name'),['class'=>'required']) !!}
                        {!! Form::text('user_name',null,['class' => 'form-control'.($errors->has('user_name') ? ' is-invalid' : '')]) !!}
                        @error('user_name')
                        <span class="error-message"> {{$message}}</span>
                        @enderror
                        <div class="input-group-append">
                        </div>
                    </div>


                </div>

                <div class="row">

                    <div class="col-md-3 form-group {{ $errors->has('company') ? 'has-error' : '' }}">
                        <!-- company -->
                        {!! Form::label('company',Lang::get('message.company'),['class'=>'required']) !!}
                        {!! Form::text('company',null,['class' => 'form-control'.($errors->has('company') ? ' is-invalid' : '')]) !!}
                        @error('company')
                        <span class="error-message error invalid-feedback"> {{$message}}</span>
                        @enderror
                        <div class="input-group-append">
                        </div>
                    </div>
                    <div class="col-md-3 form-group {{ $errors->has('bussiness') ? 'has-error' : '' }}">
                        <!-- company -->
                        {!! Form::label('bussiness','Industry') !!}
                         <!-- {!! Form::select('bussiness',['Choose'=>'Choose',''=>$bussinesses],null,['class' => 'form-control selectpicker','data-live-search'=>'true', 'data-live-search-placeholder'=>'Search' ,'data-dropup-auto'=>'false', 'data-size'=>'10']) !!} -->
                       <select name="bussiness"  class="form-control select2 {{$errors->has('bussiness') ? ' is-invalid' : ''}}">
                             <option value="">Choose</option>
                           @foreach($bussinesses as $key=>$bussines)
                           @if (Request::old('bussiness') == $key)
                             <option value={{$key}} selected>{{$bussines}}</option>
                             @else
                            <option value="{{ $key }}">{{ $bussines }}</option>
                          @endif

                          @endforeach
                          </select>
                        @error('bussiness')
                        <span class="error-message"> {{$message}}</span>
                        @enderror
                       

                    </div>


                    <div class="col-md-3 form-group {{ $errors->has('active') ? 'has-error' : '' }}">
                        <!-- mobile -->
                        {!! Form::label('active',Lang::get('message.email')) !!}
                        <p>{!! Form::radio('active',1,true) !!}&nbsp;Active&nbsp;&nbsp;{!! Form::radio('active',0) !!}&nbsp;Inactive</p>

                    </div>

                      <div class="col-md-3 form-group {{ $errors->has('mobile_verified') ? 'has-error' : '' }}">
                        <!-- mobile -->
                        {!! Form::label('mobile_verified',Lang::get('message.mobile')) !!}
                        <p>{!! Form::radio('mobile_verified',1,true) !!}&nbsp;Active&nbsp;&nbsp;{!! Form::radio('mobile_verified',0) !!}&nbsp;Inactive</p>

                    </div>
                </div>
                <div class="row">
                    <div class="col-md-3 form-group {{ $errors->has('role') ? 'has-error' : '' }}">
                        <!-- email -->
                        {!! Form::label('role',Lang::get('message.role')) !!}
                        {!! Form::select('role',['user'=>'User','admin'=>'Admin'],null,['class' => 'form-control'.($errors->has('role') ? ' is-invalid' : '')]) !!}
                        @error('role')
                        <span class="error-message"> {{$message}}</span>
                        @enderror
                    </div>
                    <div class="col-md-3 form-group {{ $errors->has('position') ? 'has-error' : '' }}">
                        <!-- email -->
                        {!! Form::label('position','Position') !!}
                        {!! Form::select('position',[''=>'Choose','manager'=>'Sales Manager','account_manager'=>'Account Manager'],null,['class' => 'form-control'.($errors->has('position') ? ' is-invalid' : '')]) !!}
                        @error('position')
                        <span class="error-message"> {{$message}}</span>
                        @enderror
                    </div>
                    <?php
                    $type = DB::table('company_types')->pluck('name','short')->toarray();
                    $size = DB::table('company_sizes')->pluck('name','short')->toarray();
                    ?>
                     <div class="col-md-3 form-group {{ $errors->has('role') ? 'has-error' : '' }}">
                        <!-- email -->
                        {!! Form::label('company_type','Company Type') !!}
                        <!-- {!! Form::select('company_type',['choose'=>'Choose',''=>$type],null,['class' => 'form-control']) !!} -->

                         <select name="company_type" value= "Choose" class="form-control ($errors->has('company_type') ? ' is-invalid' : '')">
                             <option value="">Choose</option>
                           @foreach($type as $key=>$types)
                              @if (Request::old('company_type') == $key)
                             <option value={{$key}} selected>{{$types}}</option>
                             @else
                             <option value={{$key}}>{{$types}}</option>
                               @endif
                          @endforeach
                          </select>
                         @error('company_type')
                         <span class="error-message"> {{$message}}</span>
                         @enderror
                    </div>
                     <div class="col-md-3 form-group {{ $errors->has('role') ? 'has-error' : '' }}">
                        <!-- email -->
                        {!! Form::label('company_size','Company Size') !!}
                          <select name="company_size" value= "Choose" class="form-control ($errors->has('email') ? ' is-invalid' : '')">
                             <option value="">Choose</option>
                           @foreach($size as $key=>$sizes)
                              @if (Request::old('company_size') == $key)
                             <option value={{$key}} selected>{{$sizes}}</option>
                             @else
                             <option value={{$key}}>{{$sizes}}</option>
                             @endif
                          @endforeach
                          </select>
                         @error('company_size')
                         <span class="error-message"> {{$message}}</span>
                         @enderror
                    </div>
                </div>


                <div class="form-group {{ $errors->has('address') ? 'has-error' : '' }}">
                    <!-- phone number -->
                    {!! Form::label('address',Lang::get('message.address'),['class'=>'required']) !!}
                    {!! Form::textarea('address',null,['class' => 'form-control'.($errors->has('address') ? ' is-invalid' : '')]) !!}
                    @error('address')
                    <span class="error-message"> {{$message}}</span>
                    @enderror
                    <div class="input-group-append">
                    </div>
                </div>

                <div class="row">

                    <div class="col-md-3 form-group {{ $errors->has('town') ? 'has-error' : '' }}">
                        <!-- mobile -->
                        {!! Form::label('town',Lang::get('message.town')) !!}
                        {!! Form::text('town',null,['class' => 'form-control'.($errors->has('town') ? ' is-invalid' : ''),'id'=>'town']) !!}

                        @error('town')
                        <span class="error-message"> {{$message}}</span>
                        @enderror
                    </div>

                    <?php $countries = \App\Model\Common\Country::pluck('nicename', 'country_code_char2')->toArray();
                     ?>
                    <div class="col-md-3 form-group{{ $errors->has('country') ? 'has-error' : '' }}">
                        <!-- name -->
                        {!! Form::label('country',Lang::get('message.country'),['class'=>'required']) !!}



                          <select name="country" value= "Choose" id="country" onChange="getCountryAttr(this.value)" class="form-control select2 {{$errors->has('country') ? ' is-invalid' : ''}}">
                             <option value="">Choose</option>
                           @foreach($countries as $key=>$country)
                            @if (Request::old('country') == strtolower($key) || Request::old('country') == $key)

                            <option value={{$key}} selected>{{$country}}</option>
                             @else
                              <option value={{$key}}>{{$country}}</option>
                               @endif
                          @endforeach
                          </select>
                        @error('country')
                        <span class="error-message"> {{$message}}</span>
                        @enderror
                        <div class="input-group-append">
                        </div>
                    </div>
                    <?php
                     $selectedstate = \App\Model\Common\State::select('state_subdivision_code','state_subdivision_name')->get();
                    ?>
                    <div class="col-md-3 form-group {{ $errors->has('state') ? 'has-error' : '' }}">
                        <!-- name -->
                        {!! Form::label('state',Lang::get('message.state')) !!}
                        <!--{!! Form::select('state',[],null,['class' => 'form-control','id'=>'state-list']) !!}-->
                          <select name="state" id="state-list" class="form-control ($errors->has('state') ? ' is-invalid' : '')">
                        @if(old('state') != null)
                             @foreach($selectedstate as $key=>$state)
                             @if (Request::old('state') == $state->state_subdivision_code)
                             <option value="{{old('state')}}" selected>{{$state->state_subdivision_name}}</option>
                             @endif
                             @endforeach
                             @else
                      
                            <option value="">Choose A Country</option>
                            @endif

                        </select>
                        @error('state')
                        <span class="error-message"> {{$message}}</span>
                        @enderror
                    </div>
                    <div class="col-md-3 form-group {{ $errors->has('zip') ? 'has-error' : '' }}">
                        <!-- mobile -->
                        {!! Form::label('zip',Lang::get('message.zip')) !!}
                        {!! Form::text('zip',null,['class' => 'form-control'.($errors->has('zip') ? ' is-invalid' : '')]) !!}

                    </div>
                    <div class="col-md-3 form-group {{ $errors->has('timezone_id') ? 'has-error' : '' }}">
                        <!-- mobile -->
                        {!! Form::label('timezone_id',Lang::get('message.timezone'),['class'=>'required']) !!}
                         {!! Form::select('timezone_id', [''=>'Choose','Timezones'=>$timezones],null,['class' => 'form-control select2'.($errors->has('timezone_id') ? ' is-invalid' : ''),'data-live-search'=>'true','data-live-search-placeholder' => 'Search','data-dropup-auto'=>'false','data-size'=>'10']) !!}
                        @error('timezone_id')
                        <span class="error-message"> {{$message}}</span>
                        @enderror
                        <div class="input-group-append">
                        </div>
                    </div>

                    <div class="col-md-3 form-group {{ $errors->has('mobile') ? 'has-error' : '' }}">
                        <!-- mobile -->

                        {!! Form::label('mobile',Lang::get('message.mobile'),['class'=>'required']) !!}
                        {!! Form::hidden('mobile_code',null,['id'=>'mobile_code_hidden']) !!}
                         <input type="tel" class="form-control {{$errors->has('mobile') ? ' is-invalid' : ''}}"  id="mobile_code" name="mobile" value="{{ old('mobile') }}" >
                        <div class="input-group-append">
                        </div>
                        {!! Form::hidden('mobile_country_iso',null,['id' => 'mobile_country_iso']) !!}
                        @error('mobile')
                        <span class="error-message"> {{$message}}</span>
                        @enderror
                        <span id="valid-msg" class="hide"></span>
                          <span id="error-msg" class="hide"></span>
                    </div>


                    <div class="col-md-3 form-group {{ $errors->has('skype') ? 'has-error' : '' }}">
                        <!-- mobile -->
                        {!! Form::label('skype','Skype') !!}
                        {!! Form::text('skype',null,['class' => 'form-control']) !!}
                        @error('skype')
                        <span class="error-message"> {{$message}}</span>
                        @enderror
                    </div>
                    
                    <div class="col-md-3 form-group {{ $errors->has('manager') ? 'has-error' : '' }}">
                        <!-- mobile -->
                        {!! Form::label('manager','Sales Manager') !!}
                         <select name="manager" value= "Choose" class="form-control">
                             <option value="">Choose</option>
                           @foreach($managers as $key=>$manager)
                             <option value={{$key}}>{{$manager}}</option>
                          @endforeach
                          </select>
                        @error('manager')
                        <span class="error-message"> {{$message}}</span>
                        @enderror
                    </div>

                      <div class="col-md-3 form-group {{ $errors->has('manager') ? 'has-error' : '' }}">
                        <!-- mobile -->
                        {!! Form::label('manager','Account Manager') !!}
                         <select name="account_manager" value= "Choose" class="form-control">
                             <option value="">Choose</option>
                           @foreach($accountManager as $key=>$manager)
                             <option value={{$key}}>{{$manager}}</option>
                          @endforeach
                          </select>
                    </div>
                    @error('account_manager')
                    <span class="error-message"> {{$message}}</span>
                    @enderror
                </div>

            </div>
        </div>
            <h4><button type="submit" class="btn btn-primary pull-right" id="submit"><i class="fas fa-save">&nbsp;</i>{!!Lang::get('message.save')!!}</button></h4>

            {!! Form::close() !!}
    </div>
</div>


<script>

    $(document).ready(function() {
        $('#country').on('change',function(){
            document.getElementById('town').value='';
        });

        $('#timezone_id').on('change', function () {
            const removeErrorMessage = (field) => {
                field.classList.remove('is-invalid');
                const error = field.nextElementSibling;
                if (error && error.classList.contains('error')) {
                    error.remove();
                }
            };

            if ($(this).val() !== '') {
                $(this).next('.select2-container').find('.select2-selection').css('border', '1px solid silver');
                removeErrorMessage(this);
            }
        });

        $('#country').on('change', function () {
            const removeErrorMessage = (field) => {
                field.classList.remove('is-invalid');
                const error = field.nextElementSibling;
                if (error && error.classList.contains('error')) {
                    error.remove();
                }
            };

            if ($(this).val() !== '') {
                $(this).next('.select2-container').find('.select2-selection').css('border', '1px solid silver');
                removeErrorMessage(this);
            }
        });

        const userRequiredFields = {
            first_name:@json(trans('message.user_edit_details.add_first_name')),
            last_name:@json(trans('message.user_edit_details.add_last_name')),
            email:@json(trans('message.user_edit_details.add_email')),
            company:@json(trans('message.user_edit_details.add_company')),
            address:@json(trans('message.user_edit_details.add_address')),
            mobile:@json(trans('message.user_edit_details.add_mobile')),
            user_name:@json(trans('message.user_edit_details.add_user_name')),
            country:@json(trans('message.user_edit_details.add_country')),
            timezone:@json(trans('message.user_edit_details.add_timezone')),
        };

        $('#submit').on('click', function (e) {
            const userFields = {
                first_name: $('#first_name'),
                last_name: $('#last_name'),
                email: $('#email'),
                company: $('#company'),
                address: $('#address'),
                user_name: $('#user_name'),
                country:$('#country'),
                timezone:$('#timezone_id'),
            };

            if($('#country').val()===''){
                $('#country').next('.select2-container').find('.select2-selection').css('border', '1px solid #dc3545');

            }else{
                $('#country').next('.select2-container').find('.select2-selection').css('border', '1px solid silver');
            }

            if($('#timezone_id').val()===''){
                $('#timezone_id').next('.select2-container').find('.select2-selection').css('border', '1px solid #dc3545');

            }else{
                $('#timezone_id').next('.select2-container').find('.select2-selection').css('border', '1px solid silver');
            }


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

            if(userFields.first_name.val()!=='') {
                if (!validName(userFields.first_name.val())) {
                    showError(userFields.first_name, @json(trans('message.user_edit_details.add_valid_name')));
                    isValid = false;
                }
            }

            if(userFields.last_name.val()!=='') {
                if (!validLastName(userFields.last_name.val())) {
                    showError(userFields.last_name, @json(trans('message.user_edit_details.add_valid_lastname')));
                    isValid = false;
                }
            }

            if(userFields.company.val()!=='') {
                if (!validName(userFields.company.val())) {
                    showError(userFields.company,@json(trans('message.user_edit_details.add_valid_company')));
                    isValid = false;
                }
            }

            if(userFields.email.val()!=='') {
                if (!validateEmail(userFields.email.val())) {
                    showError(userFields.email, @json(trans('message.user_edit_details.add_valid_email')));
                    isValid = false;
                }
            }

            if(telInput.val()===''){
                errorMsg.classList.remove("hide");
                errorMsg.innerHTML = @json(trans('message.user_edit_details.add_phone_number'));
                $('#mobile_code').addClass('is-invalid');
                $('#mobile_code').css("border-color", "#dc3545");
                $('#error-msg').css({"width": "100%", "margin-top": ".25rem", "font-size": "80%", "color": "#dc3545"});
                isValid=false;
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
        ['first_name','last_name','email','company','user_name','address','mobile_code','country','timezone_id'].forEach(id => {

            document.getElementById(id).addEventListener('input', function () {
                removeErrorMessage(this);
            });
        });


    function validName(string){
        nameRegex=/^[A-Za-z][A-Za-z-\s]+$/;
        return nameRegex.test(string);
    }

        function validLastName(string){
            nameRegex=/^[A-Za-z]+(?:\s[A-Za-z]+)*$/;
            return nameRegex.test(string);
        }

        function validateEmail(email) {
            const emailPattern = /^(?!.*\.\.)[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.(com|org|net|edu|gov|mil|co|io|biz|info|dev|xyz|in)$/;
            return emailPattern.test(email);

        }

    });




    $('ul.nav-sidebar a').filter(function() {
      console.log('id-=== ', this.id)
        return this.id == 'add_new_user';
    }).addClass('active');

    // for treeview
    $('ul.nav-treeview a').filter(function() {
        return this.id == 'add_new_user';
    }).parentsUntil(".nav-sidebar > .nav-treeview").addClass('menu-open').prev('a').addClass('active');

        $(document).ready(function(){
            $(function () {
                //Initialize Select2 Elements
                $('.select2').select2()
            });
            val = $('#country').val();
            state = $('#state-list').val();
            if(state == '') {
                getState(val);
            } else {
                $('#state-list').val(state)
            }


  telInput = $("#mobile_code"),
   errorMsg = document.querySelector("#error-msg"),
    validMsg = document.querySelector("#valid-msg"),
  addressDropdown = $("#country");

  var reset = function() {
  errorMsg.innerHTML = "";
  errorMsg.classList.add("hide");
  validMsg.classList.add("hide");
};


// listen to the telephone input for changes


    telInput.on("countrychange", function (e, countryData) {
        addressDropdown.val(countryData.iso2);
    });
    telInput.on('input blur', function () {
        reset();
        if ($.trim(telInput.val())) {
            if (validatePhoneNumber(telInput.get(0))) {
                $('#mobile_code').css("border-color", "");
                validMsg.classList.remove("hide");
                $('#submit').attr('disabled', false);
            } else {
                errorMsg.classList.remove("hide");
                errorMsg.innerHTML = @json(trans('message.user_edit_details.add_valid_phone'));
                $('#mobile_code').css("border-color", "#dc3545");
                $('#error-msg').css({"color": "#dc3545", "margin-top": "5px", "font-size": "80%"});
            }
        }
    });


addressDropdown.change(function() {
    updateCountryCodeAndFlag(telInput.get(0), addressDropdown.val());
       if ($.trim(telInput.val())) {
            if (validatePhoneNumber(telInput.get(0))) {
              $('#mobile_code').css("border-color","");
              errorMsg.classList.add("hide");
              $('#submit').attr('disabled',false);
            } else {
              errorMsg.classList.remove("hide");
              errorMsg.innerHTML = @json(trans('message.user_edit_details.add_valid_phone'));
             $('#mobile_code').css("border-color","red");
             $('#error-msg').css({"color":"red","margin-top":"5px"});
            }
        }
});

            $('form').on('submit', function (e) {
                $('#mobile_country_iso').val(telInput.attr('data-country-iso').toUpperCase());
                $('input[name=mobile_code]').val(telInput.attr('data-dial-code'));
                telInput.val(telInput.val().replace(/\D/g, ''));
            });
});

    function getCountryAttr(val) {
        getState(val);

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
                $("#mobile_code").val(data);
                $("#mobile_code_hidden").val(data);
            }
        });
    }
    function getCurrency(val) {
        $.ajax({
            type: "GET",
            url: "{{url('get-currency')}}",
            data: 'country_id=' + val,
            success: function (data) {
                $("#currency").val(data);
            }
        });
    }
</script>
@stop