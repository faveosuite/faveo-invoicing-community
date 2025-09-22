@extends('themes.default1.layouts.master')

@section('title')
{{ __('message.edit_user') }}
@stop


    @section('content-header')
        <div class="col-sm-6">
            <h1>{{ __('message.edit_user') }}</h1>
        </div>
        <div class="col-sm-6">
            <ol class="breadcrumb float-sm-right">
                <li class="breadcrumb-item"><a href="{{url('/')}}"><i class="fa fa-dashboard"></i> {{ __('message.home') }}</a></li>
                <li class="breadcrumb-item active">{{ __('message.edit_user') }}</li>
            </ol>
        </div><!-- /.col -->
    @stop
@section('content')
<div class="card card-secondary card-outline">
    <div class="card-body">

        {!! html()->modelForm($user, 'PATCH', url('clients/' . $user->id))->class('userUpdateForm')->open() !!}

        <div class="row">

            <div class="col-md-12">



                <div class="row">

                    <div class="col-md-3 form-group {{ $errors->has('first_name') ? 'has-error' : '' }}">
                        <!-- first name -->
                        {!! html()->label(trans('message.first_name'))->class('required')->for('first_name') !!}
                        {!! html()->text('first_name')->class( 'form-control'. ($errors->has('first_name') ? ' is-invalid' : '')) !!}
                        @error('first_name')
                        <span class="error-message"> {{$message}}</span>
                        @enderror
                        <div class="input-group-append">
                        </div>
                    </div>

                    <div class="col-md-3 form-group {{ $errors->has('last_name') ? 'has-error' : '' }}">
                        <!-- last name -->
                        {!! html()->label(trans('message.last_name'))->class('required')->for('last_name') !!}
                        {!! html()->text('last_name')->class('form-control'. ($errors->has('last_name') ? ' is-invalid' : '')) !!}
                        @error('last_name')
                        <span class="error-message"> {{$message}}</span>
                        @enderror
                        <div class="input-group-append">
                        </div>
                    </div>


                    <div class="col-md-3 form-group {{ $errors->has('email') ? 'has-error' : '' }}">
                        <!-- email -->
                        {!! html()->label(trans('message.email'))->class('required')->for('email') !!}
                        {!! html()->text('email')->class('form-control'. ($errors->has('email') ? ' is-invalid' : '')) !!}
                        @error('email')
                        <span class="error-message"> {{$message}}</span>
                        @enderror
                        <span id="email-error-msg" class="hide"></span>
                        <div class="input-group-append">
                        </div>
                    </div>
                    
                    <div class="col-md-3 form-group {{ $errors->has('user_name') ? 'has-error' : '' }}">
                        <!-- username -->
                        {!! html()->label(trans('message.user_name'))->for('user_name')->class('required') !!}
                        {!! html()->text('user_name')->class('form-control'. ($errors->has('user_name') ? ' is-invalid' : '')) !!}
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
                        {!! html()->label(trans('message.company'))->for('company')->class('required') !!}
                        {!! html()->text('company')->class('form-control'. ($errors->has('company') ? ' is-invalid' : '')) !!}
                        @error('company')
                        <span class="error-message"> {{$message}}</span>
                        @enderror
                        <div class="input-group-append">
                        </div>
                    </div>
                    <div class="col-md-3 form-group {{ $errors->has('bussiness') ? 'has-error' : '' }}">
                        <!-- industry -->
                        {!! html()->label( __('message.industry'))->for('bussiness') !!}
                        <select name="bussiness"  class="form-control select2" data-live-search="true" data-live-search-placeholder="Search" data-dropup-auto="false">
                            <option value="">{{ __('message.choose') }}</option>
                         @foreach($bussinesses as $key=>$bussiness)
                        <option value="{{$key}}" <?php  if(in_array($bussiness, $selectedIndustry) )
                        { echo "selected";} ?>>{{$bussiness}}</option>
                            @endforeach
                         </select>
                        @error('business')
                        <span class="error-message"> {{$message}}</span>
                        @enderror
                    </div>


                    <div class="col-md-3 form-group {{ $errors->has('active') ? 'has-error' : '' }}">
                        <!-- email active -->
                        {!! html()->label(Lang::get('message.email'))->for('active') !!}
                        <p>{!! html()->radio('email_verified', true, 1) !!}&nbsp;{{ __('message.active') }}&nbsp;&nbsp;{!! html()->radio('email_verified', false, 0) !!}&nbsp;{{ __('message.inactive') }}</p>

                        @error('email_verified')
                        <span class="error-message"> {{$message}}</span>
                        @enderror
                    </div>
                    <div class="col-md-3 form-group {{ $errors->has('mobile_verified') ? 'has-error' : '' }}">
                        <!-- mobile active -->
                        {!! html()->label(Lang::get('message.mobile'))->for('mobile_verified') !!}
                        <p>{!! html()->radio('mobile_verified', true, 1)->checked() !!}&nbsp;{{ __('message.active') }}&nbsp;&nbsp;{!! html()->radio('mobile_verified', false, 0) !!}{{ __('message.inactive') }}</p>
                        @error('mobile_verified')
                        <span class="error-message"> {{$message}}</span>
                        @enderror
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-3 form-group {{ $errors->has('role') ? 'has-error' : '' }}">
                        <!-- role -->
                        {!! html()->label(Lang::get('message.role'))->for('role') !!}
                        {!! html()->select('role')->options(['user' => 'User', 'admin' => 'Admin'])->class('form-control'. ($errors->has('role') ? ' is-invalid' : '')) !!}
                        @error('role')
                        <span class="error-message"> {{$message}}</span>
                        @enderror
                    </div>
                    <div class="col-md-3 form-group {{ $errors->has('position') ? 'has-error' : '' }}">
                        <!-- position -->
                        {!! html()->label( __('message.position'))->for('position') !!}
                        {!! html()->select('position')->options(['' => __('message.choose'), 'manager' => 'Sales Manager', 'account_manager' => 'Account Manager'])->class('form-control'. ($errors->has('position') ? ' is-invalid' : '')) !!}
                        @error('position')
                        <span class="error-message"> {{$message}}</span>
                        @enderror
                    </div>
                    <?php
                   $types = DB::table('company_types')->pluck('name','short')->toArray();
                    $sizes = DB::table('company_sizes')->pluck('name','short')->toArray();
                    ?>
                     <div class="col-md-3 form-group {{ $errors->has('company_type') ? 'has-error' : '' }}">
                        <!-- email -->
                         {!! html()->label( __('message.company_type'))->for('company_type') !!}
                           <select name="company_type"  class="form-control chosen-select select2" data-live-search="true" data-live-search-placeholder="Search" data-dropup-auto="false">
                            <option value="">{{ __('message.choose') }}</option>
                         @foreach($types as $key=>$type)
                                   <option value="{{$key}}" <?php  if(in_array($type, $selectedCompany) ) { echo "selected";} ?>>{{$type}}</option>
                           
                             @endforeach
                              </select>
                         @error('company-type')
                         <span class="error-message"> {{$message}}</span>
                         @enderror
                    </div>
                     <div class="col-md-3 form-group {{ $errors->has('company_size') ? 'has-error' : '' }}">
                        <!-- email -->
                         {!! html()->label( __('message.company_size'), 'company_size') !!}
                         {!! html()->select('company_size')->options(['' => __('message.choose')] + ['Company Size' => $sizes])->class('form-control chosen-select select2')->attribute('data-live-search', 'true')->attribute('data-live-search-placeholder', 'Search')->attribute('data-dropup-auto', 'false') !!}
                         @error('company_size')
                         <span class="error-message"> {{$message}}</span>
                         @enderror
                    </div>
                </div>
                <div class="form-group {{ $errors->has('address') ? 'has-error' : '' }}">
                    <!-- address -->
                    {!! html()->label(Lang::get('message.address'), 'address')->class('required') !!}
                    {!! html()->textarea('address')->class('form-control'. ($errors->has('address') ? ' is-invalid' : '')) !!}
                    @error('address')
                    <span class="error-message"> {{$message}}</span>
                    @enderror
                    <div class="input-group-append">
                    </div>
                </div>

                <div class="row">

                    <div class="col-md-3 form-group {{ $errors->has('town') ? 'has-error' : '' }}">
                        <!-- town -->
                        {!! html()->label(Lang::get('message.town'), 'town') !!}
                        {!! html()->text('town')->class('form-control'. ($errors->has('town') ? ' is-invalid' : '')) !!}
                        @error('town')
                        <span class="error-message"> {{$message}}</span>
                        @enderror
                    </div>

                    <div class="col-md-3 form-group {{ $errors->has('country') ? 'has-error' : '' }}">
                        <!-- country -->
                        {!! html()->label(Lang::get('message.country'), 'country')->class('required') !!}
                        <?php $countries = \App\Model\Common\Country::pluck('nicename', 'country_code_char2')->toArray(); ?>

                        {!! html()->select('country')->options([Lang::get('message.choose') => $countries])
    ->class('form-control select2'. ($errors->has('country') ? ' is-invalid' : ''))
    ->id('country')
    ->attribute('onChange', 'getCountryAttr(this.value)')
    ->attribute('data-live-search', 'true')
    ->attribute('required', true)
    ->attribute('data-live-search-placeholder', 'Search')
    ->attribute('data-dropup-auto', 'false')
    ->attribute('data-size', '10')
 !!}
                        @error('country')
                        <span class="error-message"> {{$message}}</span>
                        @enderror
                    </div>
                    <div class="col-md-3 form-group {{ $errors->has('state') ? 'has-error' : '' }}">
                        <!-- state -->
                        {!! html()->label(Lang::get('message.state'))->for('state') !!}
                        <select name="state" id="state-list" class="form-control {{$errors->has('') ? ' is-invalid' : ''}}">
                            @if(count($state)>0)
                            <option value="{{$state['id']}}">{{$state['name']}}</option>
                            @endif
                            <option value="">{{ __('message.select_state') }}</option>
                            @foreach($states as $key=>$value)
                            <option value="{{$key}}">{{$value}}</option>
                            @endforeach
                        </select>
                        @error('state')
                        <span class="error-message"> {{$message}}</span>
                        @enderror
                    </div>

                    <div class="col-md-3 form-group {{ $errors->has('zip') ? 'has-error' : '' }}">
                        <!-- postal -->
                        {!! html()->label(Lang::get('message.zip'))->for('zip') !!}
                        {!! html()->text('zip')->class('form-control'. ($errors->has('zip') ? ' is-invalid' : ''))->id('zip1') !!}
                        <span id="zip-error-msg"></span>
                        @error('zip')
                        <span class="error-message"> {{$message}}</span>
                        @enderror
                    </div>
                    <div class="col-md-3 form-group {{ $errors->has('timezone_id') ? 'has-error' : '' }}">
                        <!-- timezone -->
                        {!! html()->label(Lang::get('message.timezone'))->for('timezone_id')->class('required') !!}
                        {!! html()->select('timezone_id', ['Timezones' => $timezones])->class('form-control chosen-select select2'. ($errors->has('timezone_id') ? ' is-invalid' : ''))->attribute('data-live-search', 'true')->attribute('required', true)->attribute('data-live-search-placeholder', 'Search')->attribute('data-dropup-auto', 'false') !!}
                        @error('timezone_id')
                        <span class="error-message"> {{$message}}</span>
                        @enderror
                    </div>
                    

                    <div class="col-md-3 form-group {{ $errors->has('mobile_code') ? 'has-error' : '' }}">
                        {!! html()->label(Lang::get('message.mobile'))->for('mobile')->class('required') !!}
                        {!! html()->hidden('mobile_code')->id('mobile_code_hidden') !!}
                        {!! html()->input('tel', 'mobile', $user->mobile)->class('form-control selected-dial-code'. ($errors->has('mobile') ? ' is-invalid' : ''))->id('mobile_code') !!}
                        {!! html()->hidden('mobile_country_iso')->id('mobile_country_iso') !!}
                        @error('mobile')
                        <span class="error-message"> {{$message}}</span>
                        @enderror
                        <div class="input-group-append">
                        </div>
                        <span id="error-msg" class="hide"></span>
                        <span id="valid-msg" class="hide"></span>


                   </div>

                  
                    <div class="col-md-3 form-group {{ $errors->has('skype') ? 'has-error' : '' }}">
                        <!-- skype -->
                        {!! html()->label( __('message.skype'))->for('skype') !!}
                        {!! html()->text('skype')->class('form-control') !!}
                        @error('skype')
                        <span class="error-message"> {{$message}}</span>
                        @enderror
                    </div>
                    @if($user->role=='user')
                    <div class="col-md-3 form-group {{ $errors->has('manager') ? 'has-error' : '' }}">
                        <!-- manager -->
                        {!! html()->label( __('message.sales_manager'))->for('manager') !!}
                        {!! html()->select('manager', ['' => 'Choose', 'Managers' => $managers])->class('form-control') !!}
                        @error('manager')
                        <span class="error-message"> {{$message}}</span>
                        @enderror
                    </div>

                     <div class="col-md-3 form-group {{ $errors->has('manager') ? 'has-error' : '' }}">
                        <!-- account manager -->
                         {!! html()->label( __('message.account_manager'))->for('account_manager') !!}
                         {!! html()->select('account_manager', ['' => __('message.choose'), 'Managers' => $acc_managers])->class('form-control') !!}
                         @error('account_manager')
                         <span class="error-message"> {{$message}}</span>
                         @enderror
                    </div>
                    @endif
                </div>
              
            </div>
        </div>
        <h4><button type="submit" class="btn btn-primary pull-right" id="submit"><i class="fas fa-sync-alt">&nbsp;</i>{!!Lang::get('message.update')!!}</button></h4>

        {!! html()->form()->close() !!}
    </div>
     
</div>
<script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-select/1.12.4/js/bootstrap-select.min.js"></script>

<script>
    document.getElementById('user_name').addEventListener('keydown', function (e) {
        if (e.code === 'Space') {
            e.preventDefault(); // Prevent the spacebar from adding a space
        }
    });

    document.getElementById('user_name').addEventListener('input', function () {
        this.value = this.value.toLowerCase(); // Convert input to lowercase
    });

  $('ul.nav-sidebar a').filter(function() {
        return this.id == 'all_user';
    }).addClass('active');

    // for treeview
    $('ul.nav-treeview a').filter(function() {
        return this.id == 'all_user';
    }).parentsUntil(".nav-sidebar > .nav-treeview").addClass('menu-open').prev('a').addClass('active');


    $('.selectpicker').selectpicker({
  style: 'btn-default',
  color: 'white',
  size: 4
});
    $('#country').on('change',function(){
        document.getElementById('town').value='';
    });

    $(document).ready(function() {
        const userRequiredFields = {
            first_name:@json(trans('message.user_edit_details.add_first_name')),
            last_name:@json(trans('message.user_edit_details.add_last_name')),
            email:@json(trans('message.user_edit_details.add_email')),
            company:@json(trans('message.user_edit_details.add_company')),
            address:@json(trans('message.user_edit_details.add_address')),
            mobile:@json(trans('message.user_edit_details.add_mobile')),
            user_name:@json(trans('message.username_or_email')),

        };

        $('#submit').on('click',function(e) {
            const userFields = {
                first_name: $('#first_name'),
                last_name: $('#last_name'),
                email: $('#email'),
                company: $('#company'),
                address: $('#address'),
                // mobile: $('#mobile_code'),
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

            if (userFields.user_name.val() !== '') {
                if (!validateUserName(userFields.user_name.val())) {
                    showError(userFields.user_name, @json(trans('message.username_or_email')));
                    isValid = false;
                }
            }

            var zip=$('#zip1');
            ziperrorMsg = document.querySelector("#zip-error-msg");

            if(zip.val()!==''){
                if(!zipRegex(zip.val())){
                    e.preventDefault();
                    ziperrorMsg.innerHTML = @json(trans('message.valid_zip'));

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

        document.getElementById("zip1").addEventListener('input',function(){
            ziperrorMsg = document.querySelector("#zip-error-msg");
            $('#zip1').removeClass('is-invalid');
            $('#zip1').css("border-color", "silver");
            ziperrorMsg.innerHTML = '';
        })

        function zipRegex(val) {
            var re = /^[a-zA-Z0-9]+$/;
            return re.test(val);
        }

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

        function validateUserName(userName) {
            const userNamePattern = /^[a-zA-Z0-9_]+$/; // Allows only alphanumeric characters and underscores
            const emailPattern = /^(?!.*\.\.)[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.(com|org|net|edu|gov|mil|co|io|biz|info|dev|xyz|in)$/;

            return userNamePattern.test(userName) || emailPattern.test(userName);
        }

    });

    $(document).ready(function(){
         $(function () {
             //Initialize Select2 Elements
             $('.select2').select2()
         });
    var country = $('#country').val();
    getCode(country);

    //phone number validation
    var telInput = $('#mobile_code'),
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
                errorMsg.classList.remove("hide");
                errorMsg.innerHTML = @json(trans('message.user_edit_details.add_phone_number'));
                $('#mobile_code').addClass('is-invalid');
                $('#mobile_code').css("border-color", "#dc3545");
                $('#error-msg').css({"width": "100%", "margin-top": ".25rem", "font-size": "80%", "color": "#dc3545"});
                e.preventDefault();
            }
        });
    $('.intl-tel-input').css('width', '100%');
    telInput.on('input blur', function () {
      reset();
        if ($.trim(telInput.val()) && telInput.val().length>1) {
            if (validatePhoneNumber(telInput.get(0))) {
              $('#mobile_code').css("border-color","");
              validMsg.classList.remove("hide");
              $('#submit').attr('disabled',false);
            }
            else {
            errorMsg.classList.remove("hide");
             errorMsg.innerHTML = @json(trans('message.user_edit_details.add_valid_phone'));
             $('#mobile_code').css("border-color","#dc3545");
             $('#error-msg').css({"width":"100%","margin-top":".25rem","font-size":"80%","color":"#dc3545"});
             $('#submit').attr('disabled',true);
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
            }else if(telInput.val()==''){
                errorMsg.classList.remove("hide");
                errorMsg.innerHTML = @json(trans('message.user_edit_details.add_valid_phone'));
                $('#mobile_code').css("border-color","red");
                $('#error-msg').css({"width":"100%","margin-top":".25rem","font-size":"80%","color":"#dc3545"});
                $('#submit').attr('disabled',true);
            }

            else {
             errorMsg.classList.remove("hide");
             errorMsg.innerHTML = @json(trans('message.user_edit_details.add_valid_phone'));
             $('#mobile_code').css("border-color","red");
             $('#error-msg').css({"width":"100%","margin-top":".25rem","font-size":"80%","color":"#dc3545"});
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
        $('#mobile_code_hidden').val(telInput.attr('data-dial-code'));
        $('#mobile_country_iso').val(telInput.attr('data-country-iso').toUpperCase());
        telInput.val(telInput.val().replace(/\D/g, ''));
    });
});


    function getCountryAttr(val) {
        getState(val);
        getCode(val);
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