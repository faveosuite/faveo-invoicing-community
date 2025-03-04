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

        {!! html()->form('PATCH', 'clients/'.$user->id)->model($user)->open() !!}

        <div class="row">

            <div class="col-md-12">



                <div class="row">

                    <div class="col-md-3 form-group {{ $errors->has('first_name') ? 'has-error' : '' }}">
                        <!-- first name -->
                        {!! html()->label(trans('message.first_name'))->class('required')->for('first_name') !!}
                        {!! html()->text('first_name')->class('form-control') !!}

                    </div>

                    <div class="col-md-3 form-group {{ $errors->has('last_name') ? 'has-error' : '' }}">
                        <!-- last name -->
                        {!! html()->label(trans('message.last_name'))->class('required')->for('last_name') !!}
                        {!! html()->text('last_name')->class('form-control') !!}

                    </div>


                    <div class="col-md-3 form-group {{ $errors->has('email') ? 'has-error' : '' }}">
                        <!-- email -->
                        {!! html()->label(trans('message.email'))->class('required')->for('email') !!}
                        {!! html()->text('email')->class('form-control') !!}

                    </div>
                    
                    <div class="col-md-3 form-group {{ $errors->has('user_name') ? 'has-error' : '' }}">
                        <!-- mobile -->
                        {!! html()->label(trans('message.user_name'))->for('user_name') !!}
                        {!! html()->text('user_name')->class('form-control') !!}

                    </div>

                    

                </div>

                <div class="row">

                    <div class="col-md-3 form-group {{ $errors->has('company') ? 'has-error' : '' }}">
                        <!-- company -->
                        {!! html()->label(trans('message.company'))->for('company')->class('required') !!}
                        {!! html()->text('company')->class('form-control') !!}

                    </div>
                    <div class="col-md-3 form-group {{ $errors->has('bussiness') ? 'has-error' : '' }}">
                        <!-- company -->
                        {!! html()->label('Industry')->for('bussiness') !!}

                        <!--{!! html()->select('bussiness')->options(['' => 'Choose'] + ['Industries' => $bussinesses])->class('form-control chosen-select select2')->attribute('data-live-search', 'true')->attribute('data-live-search-placeholder', 'Search')->attribute('data-dropup-auto', 'false') !!}-->


                        <select name="bussiness"  class="form-control select2" data-live-search="true" data-live-search-placeholder="Search" data-dropup-auto="false">
                            <option value="">Choose</option>
                         @foreach($bussinesses as $key=>$bussiness)
                         
                        <option value="{{$key}}" <?php  if(in_array($bussiness, $selectedIndustry) ) 


                        { echo "selected";} ?>>{{$bussiness}}</option>
                            @endforeach
                         </select> 
                           
                    </div>


                    <div class="col-md-3 form-group {{ $errors->has('active') ? 'has-error' : '' }}">
                        <!-- mobile -->
                        {!! html()->label(Lang::get('message.email'))->for('active') !!}
                        <p>{!! html()->radio('email_verified', true, 1) !!}&nbsp;Active&nbsp;&nbsp;{!! html()->radio('email_verified', false, 0) !!}&nbsp;Inactive</p>

                    </div>
                    <div class="col-md-3 form-group {{ $errors->has('mobile_verified') ? 'has-error' : '' }}">
                        <!-- mobile -->
                        {!! html()->label(Lang::get('message.mobile'))->for('mobile_verified') !!}
                        <p>{!! html()->radio('mobile_verified', true, 1)->checked() !!}&nbsp;Active&nbsp;&nbsp;{!! html()->radio('mobile_verified', false, 0) !!}&nbsp;Inactive</p>

                    </div>
                </div>
                <div class="row">
                    <div class="col-md-3 form-group {{ $errors->has('role') ? 'has-error' : '' }}">
                        <!-- email -->
                        {!! html()->label(Lang::get('message.role'))->for('role') !!}
                        {!! html()->select('role')->options(['user' => 'User', 'admin' => 'Admin'])->class('form-control') !!}

                    </div>
                    <div class="col-md-3 form-group {{ $errors->has('position') ? 'has-error' : '' }}">
                        <!-- email -->
                        {!! html()->label('Position')->for('position') !!}
                        {!! html()->select('position')->options(['Choose' => 'Choose', 'manager' => 'Sales Manager', 'account_manager' => 'Account Manager'])->class('form-control') !!}

                    </div>
                    <?php
                   $types = DB::table('company_types')->pluck('name','short')->toArray();
                    $sizes = DB::table('company_sizes')->pluck('name','short')->toArray();
                    ?>
                     <div class="col-md-3 form-group {{ $errors->has('company_type') ? 'has-error' : '' }}">
                        <!-- email -->
                         {!! html()->label('Company Type')->for('company_type') !!}

                         <!--{!! html()->select('company_type')->options(['' => 'Choose'] + ['Company Type' => $types])->class('form-control chosen-select select2')->attribute('data-live-search', 'true')->attribute('data-live-search-placeholder', 'Search')->attribute('data-dropup-auto', 'false') !!} -->


                           <select name="company_type"  class="form-control chosen-select select2" data-live-search="true" data-live-search-placeholder="Search" data-dropup-auto="false">
                            <option value="">Choose</option>
                         @foreach($types as $key=>$type)
                                   <option value="{{$key}}" <?php  if(in_array($type, $selectedCompany) ) { echo "selected";} ?>>{{$type}}</option>
                           
                             @endforeach
                              </select> 

                    </div>
                     <div class="col-md-3 form-group {{ $errors->has('company_size') ? 'has-error' : '' }}">
                        <!-- email -->
                         {!! html()->label('Company Size', 'company_size') !!}

                         {!! html()->select('company_size')->options(['' => 'Choose'] + ['Company Size' => $sizes])->class('form-control chosen-select select2')->attribute('data-live-search', 'true')->attribute('data-live-search-placeholder', 'Search')->attribute('data-dropup-auto', 'false') !!}

                         <!--  <select name="company_size"  class="form-control">
                            <option value="">Choose</option>
                        @foreach($sizes as $key=>$size)
                        <option value="{{$key}}" <?php  if(in_array($size, $selectedCompanySize) ) { echo "selected";} ?>>{{$size}}</option>
                           
                             @endforeach
                              </select> -->

                    </div>
                </div>
                <div class="form-group {{ $errors->has('address') ? 'has-error' : '' }}">
                    <!-- phone number -->
                    {!! html()->label(Lang::get('message.address'), 'address')->class('required') !!}
                    {!! html()->textarea('address')->class('form-control') !!}

                </div>

                <div class="row">

                    <div class="col-md-3 form-group {{ $errors->has('town') ? 'has-error' : '' }}">
                        <!-- mobile -->
                        {!! html()->label(Lang::get('message.town'), 'town') !!}
                        {!! html()->text('town')->class('form-control') !!}

                    </div>

                    <div class="col-md-3 form-group {{ $errors->has('country') ? 'has-error' : '' }}">
                        <!-- name -->
                        {!! html()->label(Lang::get('message.country'), 'country')->class('required') !!}
                        <?php $countries = \App\Model\Common\Country::pluck('nicename', 'country_code_char2')->toArray(); ?>

                        {!! html()->select('country')->options([Lang::get('message.choose') => $countries])
    ->class('form-control select2')
    ->id('country')
    ->attribute('onChange', 'getCountryAttr(this.value)')
    ->attribute('data-live-search', 'true')
    ->attribute('required', true)
    ->attribute('data-live-search-placeholder', 'Search')
    ->attribute('data-dropup-auto', 'false')
    ->attribute('data-size', '10')
 !!}

                    </div>
                    <div class="col-md-3 form-group {{ $errors->has('state') ? 'has-error' : '' }}">
                        <!-- name -->
                        {!! html()->label(Lang::get('message.state'))->for('state') !!}
                        <!--{!! html()->select('state', [])->class('form-control')->id('state-list') !!}-->



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

                    <div class="col-md-3 form-group {{ $errors->has('zip') ? 'has-error' : '' }}">
                        <!-- mobile -->
                        {!! html()->label(Lang::get('message.zip'))->for('zip') !!}
                        {!! html()->text('zip')->class('form-control') !!}

                    </div>
                    <div class="col-md-3 form-group {{ $errors->has('timezone_id') ? 'has-error' : '' }}">
                        <!-- mobile -->
                        {!! html()->label(Lang::get('message.timezone'))->for('timezone_id')->class('required') !!}
                        {!! html()->select('timezone_id', ['Timezones' => $timezones])->class('form-control chosen-select select2')->attribute('data-live-search', 'true')->attribute('required', true)->attribute('data-live-search-placeholder', 'Search')->attribute('data-dropup-auto', 'false') !!}

                    </div>
                    
                       <div class="col-md-3 form-group {{ $errors->has('mobile_code') ? 'has-error' : '' }}">
                           {!! html()->label(Lang::get('message.mobile'))->for('mobile')->class('required') !!}
                           {!! html()->hidden('mobile_code')->id('mobile_code_hidden') !!}
                           {!! html()->tel('mobile', $user->mobile)->class('form-control selected-dial-code')->id('mobile_code') !!}
                           {!! html()->hidden('mobile_country_iso')->id('mobile_country_iso') !!}
                           <span id="valid-msg" class="hide"></span>
                       <span id="error-msg" class="hide"></span>
                </div>
                   
                  
                    <div class="col-md-3 form-group {{ $errors->has('skype') ? 'has-error' : '' }}">
                        <!-- mobile -->
                        {!! html()->label('Skype')->for('skype') !!}
                        {!! html()->text('skype')->class('form-control') !!}

                    </div>
                    @if($user->role=='user')
                    <div class="col-md-3 form-group {{ $errors->has('manager') ? 'has-error' : '' }}">
                        <!-- mobile -->
                        {!! html()->label('Sales Manager')->for('manager') !!}
                        {!! html()->select('manager', ['' => 'Choose', 'Managers' => $managers])->class('form-control') !!}

                    </div>

                     <div class="col-md-3 form-group {{ $errors->has('manager') ? 'has-error' : '' }}">
                        <!-- mobile -->
                         {!! html()->label('Account Manager')->for('account_manager') !!}
                         {!! html()->select('account_manager', ['' => 'Choose', 'Managers' => $acc_managers])->class('form-control') !!}

                     </div>
                    @endif
                </div>
              
            </div>
        </div>
        <h4><button type="submit" class="btn btn-primary pull-right" id="submit"><i class="fas fa-sync-alt">&nbsp;</i>{!!Lang::get('message.update')!!}</button></h4>

        {!! html()->form()->close() !!}
    </div>
     
</div>
<script>
  $('ul.nav-sidebar a').filter(function() {
        return this.id == 'all_user';
    }).addClass('active');

    // for treeview
    $('ul.nav-treeview a').filter(function() {
        return this.id == 'all_user';
    }).parentsUntil(".nav-sidebar > .nav-treeview").addClass('menu-open').prev('a').addClass('active');
</script>

<script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-select/1.12.4/js/bootstrap-select.min.js"></script>
<script type="text/javascript">
    $('.selectpicker').selectpicker({
  style: 'btn-default',
  color: 'white',
  size: 4
});

   
     $(document).ready(function(){
         $(function () {
             //Initialize Select2 Elements
             $('.select2').select2()
         });
    var country = $('#country').val();
    getCode(country);

    var telInput = $('#mobile_code'),
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

</script>

<script>

    function getCountryAttr(val) {
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