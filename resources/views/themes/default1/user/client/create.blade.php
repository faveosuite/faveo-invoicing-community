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
            {!! html()->form('POST', 'clients')->open() !!}



            <div class="row">

            <div class="col-md-12">



                <div class="row">

                    <div class="col-md-3 form-group {{ $errors->has('first_name') ? 'has-error' : '' }}">
                        <!-- first name -->
                        {!! html()->label(Lang::get('message.first_name'))->class('required') !!}
                        {!! html()->text('first_name')->class('form-control') !!}
                    </div>

                    <div class="col-md-3 form-group {{ $errors->has('last_name') ? 'has-error' : '' }}">
                        <!-- last name -->
                        {!! html()->label(Lang::get('message.last_name'))->class('required') !!}
                        {!! html()->text('last_name')->class('form-control') !!}
                    </div>

                    <div class="col-md-3 form-group {{ $errors->has('email') ? 'has-error' : '' }}">
                        <!-- email -->
                        {!! html()->label(Lang::get('message.email'))->class('required') !!}
                        {!! html()->text('email')->class('form-control') !!}
                    </div>

                    <div class="col-md-3 form-group {{ $errors->has('user_name') ? 'has-error' : '' }}">
                        <!-- mobile -->
                        {!! html()->label(Lang::get('message.user_name')) !!}
                        {!! html()->text('user_name')->class('form-control') !!}
                    </div>

                </div>

                <div class="row">

                    <div class="col-md-3 form-group {{ $errors->has('company') ? 'has-error' : '' }}">
                        <!-- company -->
                        {!! html()->label(Lang::get('message.company'))->class('required') !!}
                        {!! html()->text('company')->class('form-control') !!}

                    </div>
                    <div class="col-md-3 form-group {{ $errors->has('bussiness') ? 'has-error' : '' }}">
                        <!-- company -->
                        {!! html()->label('Industry')->for('bussiness') !!}
                        <!-- {!! html()->select('bussiness')->options(['Choose' => 'Choose', '' => $bussinesses])->class('form-control selectpicker')->attribute('data-live-search', 'true')->attribute('data-live-search-placeholder', 'Search')->attribute('data-dropup-auto', 'false')->attribute('data-size', '10') !!} -->
                       <select name="bussiness"  class="form-control select2">
                             <option value="">Choose</option>
                           @foreach($bussinesses as $key=>$bussines)
                           @if (Request::old('bussiness') == $key)
                             <option value={{$key}} selected>{{$bussines}}</option>
                             @else
                            <option value="{{ $key }}">{{ $bussines }}</option>
                          @endif

                          @endforeach
                          </select>
             
                       

                    </div>


                    <div class="col-md-3 form-group {{ $errors->has('active') ? 'has-error' : '' }}">
                        <!-- mobile -->
                        {!! html()->label(Lang::get('message.email'), 'active') !!}
                        <p>
                            {!! html()->radio('active', true, 1) !!}
                            &nbsp;Active&nbsp;&nbsp;
                            {!! html()->radio('active', false, 0) !!}
                            &nbsp;Inactive
                        </p>
                    </div>

                    <div class="col-md-3 form-group {{ $errors->has('mobile_verified') ? 'has-error' : '' }}">
                        <!-- mobile -->
                        {!! html()->label(Lang::get('message.mobile'), 'mobile_verified') !!}
                        <p>
                            {!! html()->radio('mobile_verified', true, 1) !!}
                            &nbsp;Active&nbsp;&nbsp;
                            {!! html()->radio('mobile_verified', false, 0) !!}
                            &nbsp;Inactive
                        </p>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-3 form-group {{ $errors->has('role') ? 'has-error' : '' }}">
                        <!-- email -->
                        {!! html()->label(Lang::get('message.role'), 'role') !!}
                        {!! html()->select('role', ['user' => 'User', 'admin' => 'Admin'])->class('form-control') !!}
                    </div>

                    <div class="col-md-3 form-group {{ $errors->has('position') ? 'has-error' : '' }}">
                        <!-- email -->
                        {!! html()->label('Position', 'position') !!}
                        {!! html()->select('position', ['' => 'Choose', 'manager' => 'Sales Manager', 'account_manager' => 'Account Manager'])->class('form-control') !!}
                    </div>
                    <?php
                    $type = DB::table('company_types')->pluck('name','short')->toarray();
                    $size = DB::table('company_sizes')->pluck('name','short')->toarray();
                    ?>
                     <div class="col-md-3 form-group {{ $errors->has('role') ? 'has-error' : '' }}">
                        <!-- email -->
                         {!! html()->label('Company Type', 'company_type') !!}
                         <!-- {!! html()->select('company_type')->options(['choose' => 'Choose', '' => $type])->class('form-control') !!} -->

                         <select name="company_type" value= "Choose" class="form-control">
                             <option value="">Choose</option>
                           @foreach($type as $key=>$types)
                              @if (Request::old('company_type') == $key)
                             <option value={{$key}} selected>{{$types}}</option>
                             @else
                             <option value={{$key}}>{{$types}}</option>
                               @endif
                          @endforeach
                          </select>

                    </div>
                     <div class="col-md-3 form-group {{ $errors->has('role') ? 'has-error' : '' }}">
                        <!-- email -->
                         {!! html()->label('Company Size', 'company_size') !!}
                         <!-- {!! html()->select('company_size', ['choose' => 'Choose', '' => $size])->class('form-control') !!} -->
                          <select name="company_size" value= "Choose" class="form-control">
                             <option value="">Choose</option>
                           @foreach($size as $key=>$sizes)
                              @if (Request::old('company_size') == $key)
                             <option value={{$key}} selected>{{$sizes}}</option>
                             @else
                             <option value={{$key}}>{{$sizes}}</option>
                             @endif
                          @endforeach
                          </select>

                    </div>
                </div>


                <div class="form-group {{ $errors->has('address') ? 'has-error' : '' }}">
                    <!-- phone number -->
                    {!! html()->label(Lang::get('message.address'))->class('required') !!}
                    {!! html()->textarea('address')->class('form-control') !!}

                </div>

                <div class="row">

                    <div class="col-md-3 form-group {{ $errors->has('town') ? 'has-error' : '' }}">
                        <!-- mobile -->
                        {!! html()->label(Lang::get('message.town')) !!}
                        {!! html()->text('town')->class('form-control') !!}

                    </div>

                    <?php $countries = \App\Model\Common\Country::pluck('nicename', 'country_code_char2')->toArray();
                     ?>
                    <div class="col-md-3 form-group{{ $errors->has('country') ? 'has-error' : '' }}">
                        <!-- name -->
                        {!! html()->label(Lang::get('message.country'))->class('required') !!}



                        <select name="country" value= "Choose" id="country" onChange="getCountryAttr(this.value)" class="form-control select2">
                             <option value="">Choose</option>
                           @foreach($countries as $key=>$country)
                            @if (Request::old('country') == strtolower($key) || Request::old('country') == $key)

                            <option value={{$key}} selected>{{$country}}</option>
                             @else
                              <option value={{$key}}>{{$country}}</option>
                               @endif
                          @endforeach
                          </select>

                    </div>
                    <?php
                     $selectedstate = \App\Model\Common\State::select('state_subdivision_code','state_subdivision_name')->get();
                    ?>
                    <div class="col-md-3 form-group {{ $errors->has('state') ? 'has-error' : '' }}">
                        <!-- name -->
                        {!! html()->label(Lang::get('message.state')) !!}
                        <!--{!! html()->select('state', [], null)->class('form-control')->id('state-list') !!}-->
                          <select name="state" id="state-list" class="form-control">
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

                    </div>
                    <div class="col-md-3 form-group {{ $errors->has('zip') ? 'has-error' : '' }}">
                        <!-- mobile -->
                        {!! html()->label(Lang::get('message.zip')) !!}
                        {!! html()->text('zip')->class('form-control') !!}

                    </div>
                    <div class="col-md-3 form-group {{ $errors->has('timezone_id') ? 'has-error' : '' }}">
                        <!-- mobile -->
                        {!! html()->label(Lang::get('message.timezone'))->class('required') !!}
                        {!! html()->select('timezone_id', ['' => 'Choose', 'Timezones' => $timezones])
                            ->class('form-control select2')
                            ->attribute('data-live-search', 'true')
                            ->attribute('data-live-search-placeholder', 'Search')
                            ->attribute('data-dropup-auto', 'false')
                            ->attribute('data-size', '10') !!}


                        <!--   <select name="timezone_id" value= "Choose" class="form-control selectpicker" data-live-search="true" data-live-search-placeholder="Search" data-dropup-auto="false" data-size="10"">
                             <option value="">Choose</option>
                           @foreach($timezones as $key=>$timezone)

                             <option value={{$key}}>{{$timezone}}</option>
                          @endforeach
                          </select> -->

                    </div>
                   
                   <!--  <div class="col-md-4 form-group {{ $errors->has('mobile_code') ? 'has-error' : '' }}">
                        <label class="required">Country code</label>
                        {!! html()->hidden('mobile_code')->id('mobile_code_hidden') !!}
                    {!! html()->text('mobil')->class('form-control')->disabled()->id('mobile_code') !!}


                    </div> -->
                   


                    <div class="col-md-3 form-group {{ $errors->has('mobile') ? 'has-error' : '' }}">
                        <!-- mobile -->

                        {!! html()->label(Lang::get('message.mobile'))->class('required') !!}
                        {!! html()->hidden('mobile_code')->id('mobile_code_hidden') !!}
                        <input class="form-control"  id="mobile_code" name="mobile" value="{{ old('mobile') }}" type="tel">
                        {!! html()->hidden('mobile_country_iso')->id('mobile_country_iso') !!}
                        <span id="valid-msg" class="hide"></span>
                          <span id="error-msg" class="hide"></span>
                    </div>


                    <div class="col-md-3 form-group {{ $errors->has('skype') ? 'has-error' : '' }}">
                        <!-- mobile -->
                        {!! html()->label('Skype', 'Skype') !!}
                        {!! html()->text('skype')->class('form-control') !!}

                    </div>
                    
                    <div class="col-md-3 form-group {{ $errors->has('manager') ? 'has-error' : '' }}">
                        <!-- mobile -->
                        {!! html()->label('manager', 'Sales Manager') !!}
                        <!-- {!! html()->select('manager', ['' => 'Select', 'Managers' => $managers])->class('form-control') !!} -->
                         <select name="manager" value= "Choose" class="form-control">
                             <option value="">Choose</option>
                           @foreach($managers as $key=>$manager)
                             <option value={{$key}}>{{$manager}}</option>
                          @endforeach
                          </select>
                    </div>

                      <div class="col-md-3 form-group {{ $errors->has('manager') ? 'has-error' : '' }}">
                        <!-- mobile -->
                          {!! html()->label('Account Manager', 'manager') !!}
                          <!-- {!! html()->select('manager')->options(['' => 'Select'] + $managers)->class('form-control') !!} -->
                         <select name="account_manager" value= "Choose" class="form-control">
                             <option value="">Choose</option>
                           @foreach($accountManager as $key=>$manager)
                             <option value={{$key}}>{{$manager}}</option>
                          @endforeach
                          </select>
                    </div>

                </div>

            </div>
        </div>
            <h4><button type="submit" class="btn btn-primary pull-right" id="submit"><i class="fas fa-save">&nbsp;</i>{!!Lang::get('message.save')!!}</button></h4>

            {!! html()->form()->close()!!}
    </div>
</div>





<script>
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

// set it's initial value
// var initialCountry = telInput.intlTelInput("getSelectedCountryData").iso2;
// addressDropdown.val(initialCountry);

// listen to the telephone input for changes
telInput.on("countrychange", function(e, countryData) {
  addressDropdown.val(countryData.iso2);
});
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
    updateCountryCodeAndFlag(telInput.get(0), addressDropdown.val());
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

            $('form').on('submit', function (e) {
                $('#mobile_country_iso').val(telInput.attr('data-country-iso').toUpperCase());
                $('input[name=mobile_code]').val(telInput.attr('data-dial-code'));
                telInput.val(telInput.val().replace(/\D/g, ''));
            });
});

    function getCountryAttr(val) {
        getState(val);
        // getCode(val);
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