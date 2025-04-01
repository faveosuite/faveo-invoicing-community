@extends('themes.default1.layouts.master')

@section('title')
Edit User
@stop


    @section('content-header')
        <div class="col-sm-6">
            <h1>Edit User</h1>
        </div>
        <div class="col-sm-6">
            <ol class="breadcrumb float-sm-right">
                <li class="breadcrumb-item"><a href="{{url('/')}}"><i class="fa fa-dashboard"></i> Home</a></li>
                <li class="breadcrumb-item active">Edit User</li>
            </ol>
        </div><!-- /.col -->
    @stop
@section('content')
<div class="card card-secondary card-outline">
    <div class="card-body">

         {!! Form::model($user,['url'=>'clients/'.$user->id,'method'=>'PATCH','class'=>'userUpdateForm']) !!}

        <div class="row">

            <div class="col-md-12">



                <div class="row">

                    <div class="col-md-3 form-group {{ $errors->has('first_name') ? 'has-error' : '' }}">
                        <!-- first name -->
                        {!! Form::label('first_name',Lang::get('message.first_name'),['class'=>'required']) !!}
                        {!! Form::text('first_name',null,['class' => 'form-control'. ($errors->has('first_name') ? ' is-invalid' : '')]) !!}
                        @error('first_name')
                        <span class="error-message"> {{$message}}</span>
                        @enderror
                        <div class="input-group-append">
                        </div>
                    </div>

                    <div class="col-md-3 form-group {{ $errors->has('last_name') ? 'has-error' : '' }}">
                        <!-- last name -->
                        {!! Form::label('last_name',Lang::get('message.last_name'),['class'=>'required']) !!}
                        {!! Form::text('last_name',null,['class' => 'form-control'. ($errors->has('last_name') ? ' is-invalid' : '')]) !!}
                        @error('last_name')
                        <span class="error-message"> {{$message}}</span>
                        @enderror
                        <div class="input-group-append">
                        </div>
                    </div>


                    <div class="col-md-3 form-group {{ $errors->has('email') ? 'has-error' : '' }}">
                        <!-- email -->
                        {!! Form::label('email',Lang::get('message.email'),['class'=>'required']) !!}
                        {!! Form::text('email',null,['class' => 'form-control'. ($errors->has('email') ? ' is-invalid' : '')]) !!}
                        @error('email')
                        <span class="error-message"> {{$message}}</span>
                        @enderror
                        <span id="email-error-msg" class="hide"></span>
                        <div class="input-group-append">
                        </div>
                    </div>
                    
                    <div class="col-md-3 form-group {{ $errors->has('user_name') ? 'has-error' : '' }}">
                        <!-- username -->
                        {!! Form::label('user_name',Lang::get('message.user_name'),['class'=>'required']) !!}
                        {!! Form::text('user_name',null,['class' => 'form-control'. ($errors->has('user_name') ? ' is-invalid' : '')]) !!}
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
                        {!! Form::text('company',null,['class' => 'form-control'. ($errors->has('company') ? ' is-invalid' : '')]) !!}
                        @error('company')
                        <span class="error-message"> {{$message}}</span>
                        @enderror
                        <div class="input-group-append">
                        </div>
                    </div>
                    <div class="col-md-3 form-group {{ $errors->has('bussiness') ? 'has-error' : '' }}">
                        <!-- industry -->
                        {!! Form::label('bussiness','Industry') !!}
                        <select name="bussiness"  class="form-control select2" data-live-search="true" data-live-search-placeholder="Search" data-dropup-auto="false">
                            <option value="">Choose</option>
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
                        {!! Form::label('active',Lang::get('message.email')) !!}
                        <p>{!! Form::radio('email_verified',1,true) !!}&nbsp;Active&nbsp;&nbsp;{!! Form::radio('email_verified',0) !!}&nbsp;Inactive</p>

                        @error('email_verified')
                        <span class="error-message"> {{$message}}</span>
                        @enderror
                    </div>
                    <div class="col-md-3 form-group {{ $errors->has('mobile_verified') ? 'has-error' : '' }}">
                        <!-- mobile active -->
                        {!! Form::label('mobile_verified',Lang::get('message.mobile')) !!}
                        <p>{!! Form::radio('mobile_verified',1,true) !!}&nbsp;Active&nbsp;&nbsp;{!! Form::radio('mobile_verified',0) !!}&nbsp;Inactive</p>
                        @error('mobile_verified')
                        <span class="error-message"> {{$message}}</span>
                        @enderror
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-3 form-group {{ $errors->has('role') ? 'has-error' : '' }}">
                        <!-- role -->
                        {!! Form::label('role',Lang::get('message.role')) !!}
                        {!! Form::select('role',['user'=>'User','admin'=>'Admin'],null,['class' => 'form-control'. ($errors->has('role') ? ' is-invalid' : '')]) !!}
                        @error('role')
                        <span class="error-message"> {{$message}}</span>
                        @enderror
                    </div>
                    <div class="col-md-3 form-group {{ $errors->has('position') ? 'has-error' : '' }}">
                        <!-- position -->
                        {!! Form::label('position','Position') !!}
                        {!! Form::select('position',['Choose'=>'Choose','manager'=>'Sales Manager','account_manager'=>'Account Manager'],null,
                        ['class' => 'form-control'. ($errors->has('position') ? ' is-invalid' : '')]) !!}
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
                        {!! Form::label('company_type','Company Type') !!}
                           <select name="company_type"  class="form-control chosen-select select2" data-live-search="true" data-live-search-placeholder="Search" data-dropup-auto="false">
                            <option value="">Choose</option>
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
                        {!! Form::label('company_size','Company Size') !!}

                        {!! Form::select('company_size', [''=>'Choose','Company Size'=>$sizes],null,['class' => 'form-control chosen-select select2','data-live-search'=>'true','data-live-search-placeholder' => 'Search','data-dropup-auto'=>'false']) !!}
                         @error('company_size')
                         <span class="error-message"> {{$message}}</span>
                         @enderror
                    </div>
                </div>
                <div class="form-group {{ $errors->has('address') ? 'has-error' : '' }}">
                    <!-- address -->
                    {!! Form::label('address',Lang::get('message.address'),['class'=>'required']) !!}
                    {!! Form::textarea('address',null,['class' => 'form-control'. ($errors->has('address') ? ' is-invalid' : '')]) !!}
                    @error('address')
                    <span class="error-message"> {{$message}}</span>
                    @enderror
                    <div class="input-group-append">
                    </div>
                </div>

                <div class="row">

                    <div class="col-md-3 form-group {{ $errors->has('town') ? 'has-error' : '' }}">
                        <!-- town -->
                        {!! Form::label('town',Lang::get('message.town')) !!}
                        {!! Form::text('town',null,['class' => 'form-control'. ($errors->has('town') ? ' is-invalid' : ''),'id'=>'town']) !!}
                        @error('town')
                        <span class="error-message"> {{$message}}</span>
                        @enderror
                    </div>

                    <div class="col-md-3 form-group {{ $errors->has('country') ? 'has-error' : '' }}">
                        <!-- country -->
                        {!! Form::label('country',Lang::get('message.country'),['class'=>'required']) !!}
                        <?php $countries = \App\Model\Common\Country::pluck('nicename', 'country_code_char2')->toArray(); ?>

                        {!! Form::select('country',[Lang::get('message.choose')=>$countries],null,['class' => 'form-control select2'. ($errors->has('country') ? ' is-invalid' : ''),'id'=>'country','onChange'=>'getCountryAttr(this.value)','data-live-search'=>'true','required','data-live-search-placeholder' => 'Search','data-dropup-auto'=>'false','data-size'=>'10']) !!}
                        @error('country')
                        <span class="error-message"> {{$message}}</span>
                        @enderror
                    </div>
                    <div class="col-md-3 form-group {{ $errors->has('state') ? 'has-error' : '' }}">
                        <!-- state -->
                        {!! Form::label('state',Lang::get('message.state')) !!}
                        <select name="state" id="state-list" class="form-control {{$errors->has('') ? ' is-invalid' : ''}}">
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

                    <div class="col-md-3 form-group {{ $errors->has('zip') ? 'has-error' : '' }}">
                        <!-- postal -->
                        {!! Form::label('zip',Lang::get('message.zip')) !!}
                        {!! Form::text('zip',null,['class' => 'form-control'. ($errors->has('zip') ? ' is-invalid' : ''),'id'=>'zip1']) !!}
                        <span id="zip-error-msg"></span>
                        @error('zip')
                        <span class="error-message"> {{$message}}</span>
                        @enderror
                    </div>
                    <div class="col-md-3 form-group {{ $errors->has('timezone_id') ? 'has-error' : '' }}">
                        <!-- timezone -->
                        {!! Form::label('timezone_id',Lang::get('message.timezone'),['class'=>'required']) !!}

                         {!! Form::select('timezone_id', ['Timezones'=>$timezones],null,['class' => 'form-control chosen-select select2'. ($errors->has('timezone_id') ? ' is-invalid' : ''),'data-live-search'=>'true','required','data-live-search-placeholder' => 'Search','data-dropup-auto'=>'false']) !!}
                        @error('timezone_id')
                        <span class="error-message"> {{$message}}</span>
                        @enderror
                    </div>
                    

                    <div class="col-md-3 form-group {{ $errors->has('mobile_code') ? 'has-error' : '' }}">
                        {!! Form::label('mobile',null,['class' => 'required'],Lang::get('message.mobile')) !!}
                        {!! Form::hidden('mobile_code',null,['id'=>'mobile_code_hidden']) !!}
                        {!! Form::tel('mobile', $user->mobile, ['class' => 'form-control selected-dial-code'. ($errors->has('mobile') ? ' is-invalid' : ''), 'id' => 'mobile_code']) !!}
                        {!! Form::hidden('mobile_country_iso',null,['id' => 'mobile_country_iso']) !!}
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
                        {!! Form::label('skype','Skype') !!}
                        {!! Form::text('skype',null,['class' => 'form-control']) !!}
                        @error('skype')
                        <span class="error-message"> {{$message}}</span>
                        @enderror
                    </div>
                    @if($user->role=='user')
                    <div class="col-md-3 form-group {{ $errors->has('manager') ? 'has-error' : '' }}">
                        <!-- manager -->
                        {!! Form::label('manager','Sales Manager') !!}
                        {!! Form::select('manager',[''=>'Choose','Managers'=>$managers],null,['class' => 'form-control']) !!}
                        @error('manager')
                        <span class="error-message"> {{$message}}</span>
                        @enderror
                    </div>

                     <div class="col-md-3 form-group {{ $errors->has('manager') ? 'has-error' : '' }}">
                        <!-- account manager -->
                        {!! Form::label('account_manager','Account Manager') !!}
                        {!! Form::select('account_manager',[''=>'Choose','Managers'=>$acc_managers],null,['class' => 'form-control']) !!}
                         @error('account_manager')
                         <span class="error-message"> {{$message}}</span>
                         @enderror
                    </div>
                    @endif
                </div>
              
            </div>
        </div>
        <h4><button type="submit" class="btn btn-primary pull-right" id="submit"><i class="fas fa-sync-alt">&nbsp;</i>{!!Lang::get('message.update')!!}</button></h4>

        {!! Form::close() !!}
    </div>
     
</div>
<script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-select/1.12.4/js/bootstrap-select.min.js"></script>

<script>
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
            user_name:@json(trans('message.user_edit_details.add_user_name')),

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