@extends('themes.default1.layouts.master')

@section('title')
Edit User
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

         {!! Form::model($user,['url'=>'clients/'.$user->id,'method'=>'PATCH']) !!}

        <div class="row">

            <div class="col-md-12">



                <div class="row">

                    <div class="col-md-3 form-group {{ $errors->has('first_name') ? 'has-error' : '' }}">
                        <!-- first name -->
                        {!! Form::label('first_name',Lang::get('message.first_name'),['class'=>'required']) !!}
                        {!! Form::text('first_name',null,['class' => 'form-control']) !!}

                    </div>

                    <div class="col-md-3 form-group {{ $errors->has('last_name') ? 'has-error' : '' }}">
                        <!-- last name -->
                        {!! Form::label('last_name',Lang::get('message.last_name'),['class'=>'required']) !!}
                        {!! Form::text('last_name',null,['class' => 'form-control']) !!}

                    </div>


                    <div class="col-md-3 form-group {{ $errors->has('email') ? 'has-error' : '' }}">
                        <!-- email -->
                        {!! Form::label('email',Lang::get('message.email'),['class'=>'required']) !!}
                        {!! Form::text('email',null,['class' => 'form-control']) !!}

                    </div>
                    
                    <div class="col-md-3 form-group {{ $errors->has('user_name') ? 'has-error' : '' }}">
                        <!-- mobile -->
                        {!! Form::label('user_name',Lang::get('message.user_name')) !!}
                        {!! Form::text('user_name',null,['class' => 'form-control']) !!}

                    </div>

                    

                </div>

                <div class="row">

                    <div class="col-md-3 form-group {{ $errors->has('company') ? 'has-error' : '' }}">
                        <!-- company -->
                        {!! Form::label('company',Lang::get('message.company'),['class'=>'required']) !!}
                        {!! Form::text('company',null,['class' => 'form-control']) !!}

                    </div>
                    <div class="col-md-3 form-group {{ $errors->has('bussiness') ? 'has-error' : '' }}">
                        <!-- company -->
                        {!! Form::label('bussiness','Industry') !!}

                        <!--{!! Form::select('bussiness', [''=>'Choose','Industries'=>$bussinesses],null,['class' => 'form-control chosen-select select2','data-live-search'=>'true','data-live-search-placeholder' => 'Search','data-dropup-auto'=>'false']) !!}-->


                        <select name="bussiness"  class="form-control select2" data-live-search="true" data-live-search-placeholder="{{ __('message.search') }}" data-dropup-auto="false">
                            <option value="">{{ __('message.choose') }}</option>
                         @foreach($bussinesses as $key=>$bussiness)
                         
                        <option value="{{$key}}" <?php  if(in_array($bussiness, $selectedIndustry) ) 


                        { echo "selected";} ?>>{{$bussiness}}</option>
                            @endforeach
                         </select> 
                           
                    </div>


                    <div class="col-md-3 form-group {{ $errors->has('active') ? 'has-error' : '' }}">
                        <!-- mobile -->
                        {!! Form::label('active',Lang::get('message.email')) !!}
                        <p>{!! Form::radio('email_verified',1,true) !!}&nbsp;Active&nbsp;&nbsp;{!! Form::radio('email_verified',0) !!}&nbsp;{{ __('message.inactive') }}</p>

                    </div>
                    <div class="col-md-3 form-group {{ $errors->has('mobile_verified') ? 'has-error' : '' }}">
                        <!-- mobile -->
                        {!! Form::label('mobile_verified',Lang::get('message.mobile')) !!}
                        <p>{!! Form::radio('mobile_verified',1,true) !!}&nbsp;{{ __('message.active') }}&nbsp;&nbsp;{!! Form::radio('mobile_verified',0) !!}&nbsp;{{ __('message.inactive') }}</p>

                    </div>
                </div>
                <div class="row">
                    <div class="col-md-3 form-group {{ $errors->has('role') ? 'has-error' : '' }}">
                        <!-- email -->
                        {!! Form::label('role',Lang::get('message.role')) !!}
                        {!! Form::select('role',['user'=>'User','admin'=>'Admin'],null,['class' => 'form-control']) !!}

                    </div>
                    <div class="col-md-3 form-group {{ $errors->has('position') ? 'has-error' : '' }}">
                        <!-- email -->
                        {!! Form::label('position', __('message.position')) !!}
                        {!! Form::select('position',['Choose'=>'Choose','manager'=>'Sales Manager','account_manager'=>'Account Manager'],null,['class' => 'form-control']) !!}

                    </div>
                    <?php
                   $types = DB::table('company_types')->pluck('name','short')->toArray();
                    $sizes = DB::table('company_sizes')->pluck('name','short')->toArray();
                    ?>
                     <div class="col-md-3 form-group {{ $errors->has('company_type') ? 'has-error' : '' }}">
                        <!-- email -->
                        {!! Form::label('company_type','Company Type') !!}
                        
                         <!--{!! Form::select('company_type', [''=>'Choose','Company Type'=>$types],null,['class' => 'form-control chosen-select select2','data-live-search'=>'true','data-live-search-placeholder' => 'Search','data-dropup-auto'=>'false']) !!}-->


                           <select name="company_type"  class="form-control chosen-select select2" data-live-search="true" data-live-search-placeholder="Search" data-dropup-auto="false">
                            <option value="">{{ __('message.choose') }}</option>
                         @foreach($types as $key=>$type)
                                   <option value="{{$key}}" <?php  if(in_array($type, $selectedCompany) ) { echo "selected";} ?>>{{$type}}</option>
                           
                             @endforeach
                              </select> 

                    </div>
                     <div class="col-md-3 form-group {{ $errors->has('company_size') ? 'has-error' : '' }}">
                        <!-- email -->
                        {!! Form::label('company_size','Company Size') !!}

                        {!! Form::select('company_size', [''=>'Choose','Company Size'=>$sizes],null,['class' => 'form-control chosen-select select2','data-live-search'=>'true','data-live-search-placeholder' => 'Search','data-dropup-auto'=>'false']) !!}

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
                    {!! Form::label('address',Lang::get('message.address'),['class'=>'required']) !!}
                    {!! Form::textarea('address',null,['class' => 'form-control']) !!}

                </div>

                <div class="row">

                    <div class="col-md-3 form-group {{ $errors->has('town') ? 'has-error' : '' }}">
                        <!-- mobile -->
                        {!! Form::label('town',Lang::get('message.town')) !!}
                        {!! Form::text('town',null,['class' => 'form-control']) !!}

                    </div>

                    <div class="col-md-3 form-group {{ $errors->has('country') ? 'has-error' : '' }}">
                        <!-- name -->
                        {!! Form::label('country',Lang::get('message.country'),['class'=>'required']) !!}
                        <?php $countries = \App\Model\Common\Country::pluck('nicename', 'country_code_char2')->toArray(); ?>

                        {!! Form::select('country',[Lang::get('message.choose')=>$countries],null,['class' => 'form-control select2','id'=>'country','onChange'=>'getCountryAttr(this.value)','data-live-search'=>'true','required','data-live-search-placeholder' => __('message.search'),'data-dropup-auto'=>'false','data-size'=>'10']) !!}

                    </div>
                    <div class="col-md-3 form-group {{ $errors->has('state') ? 'has-error' : '' }}">
                        <!-- name -->
                        {!! Form::label('state',Lang::get('message.state')) !!}
                        <!--{!! Form::select('state',[],null,['class' => 'form-control','id'=>'state-list']) !!}-->



                        <select name="state" id="state-list" class="form-control">

                            @if(count($state)>0)
                            <option value="{{$state['id']}}">{{$state['name']}}</option>
                            @endif
                            <option value="">{{ __('message.select_state') }}</option>
                            @foreach($states as $key=>$value)
                            <option value="{{$key}}">{{$value}}</option>
                            @endforeach
                        </select>

                    </div>

                    <div class="col-md-3 form-group {{ $errors->has('zip') ? 'has-error' : '' }}">
                        <!-- mobile -->
                        {!! Form::label('zip',Lang::get('message.zip')) !!}
                        {!! Form::text('zip',null,['class' => 'form-control']) !!}

                    </div>
                    <div class="col-md-3 form-group {{ $errors->has('timezone_id') ? 'has-error' : '' }}">
                        <!-- mobile -->
                        {!! Form::label('timezone_id',Lang::get('message.timezone'),['class'=>'required']) !!}

                         {!! Form::select('timezone_id', ['Timezones'=>$timezones],null,['class' => 'form-control chosen-select select2','data-live-search'=>'true','required','data-live-search-placeholder' =>  __('message.search'),'data-dropup-auto'=>'false']) !!}

                    </div>
                    
                       <div class="col-md-3 form-group {{ $errors->has('mobile_code') ? 'has-error' : '' }}">
                  {!! Form::label('mobile',null,['class' => 'required'],Lang::get('message.mobile'),['class'=>'required']) !!}
                     {!! Form::hidden('mobile_code',null,['id'=>'mobile_code_hidden']) !!}
                           {!! Form::tel('mobile', $user->mobile, ['class' => 'form-control selected-dial-code', 'id' => 'mobile_code']) !!}
                           {!! Form::hidden('mobile_country_iso',null,['id' => 'mobile_country_iso']) !!}
                       <span id="valid-msg" class="hide"></span>
                       <span id="error-msg" class="hide"></span>
                </div>
                   
                  
                    <div class="col-md-3 form-group {{ $errors->has('skype') ? 'has-error' : '' }}">
                        <!-- mobile -->
                        {!! Form::label('skype', __('message.skype')) !!}
                        {!! Form::text('skype',null,['class' => 'form-control']) !!}

                    </div>
                    @if($user->role=='user')
                    <div class="col-md-3 form-group {{ $errors->has('manager') ? 'has-error' : '' }}">
                        <!-- mobile -->
                        {!! Form::label('manager', __('message.sales_manager')) !!}
                        {!! Form::select('manager',[''=>'Choose','Managers'=>$managers],null,['class' => 'form-control']) !!}

                    </div>

                     <div class="col-md-3 form-group {{ $errors->has('manager') ? 'has-error' : '' }}">
                        <!-- mobile -->
                         {!! Form::label('account_manager', __('message.account_manager')) !!}
                        {!! Form::select('account_manager',[''=>'Choose','Managers'=>$acc_managers],null,['class' => 'form-control']) !!}

                    </div>
                    @endif
                </div>
              
            </div>
        </div>
        <h4><button type="submit" class="btn btn-primary pull-right" id="submit"><i class="fas fa-sync-alt">&nbsp;</i>{!!Lang::get('message.update')!!}</button></h4>

        {!! Form::close() !!}
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
             errorMsg.innerHTML = "{{ __('message.enter_valid_number') }}";
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
             errorMsg.innerHTML = "{{ __('message.enter_valid_number') }}";
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