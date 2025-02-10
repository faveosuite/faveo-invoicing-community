@extends('themes.default1.layouts.master')
@section('title')
System Setting
@stop
@section('content-header')
    <div class="col-sm-6">
        <h1>{{ __('message.company_details') }}</h1>
    </div>
    <div class="col-sm-6">
        <ol class="breadcrumb float-sm-right">
            <li class="breadcrumb-item"><a href="{{url('/')}}"><i class="fa fa-dashboard"></i> {{ __('message.home') }}</a></li>
            <li class="breadcrumb-item"><a href="{{url('settings')}}"><i class="fa fa-dashboard"></i> {{ __('message.settings') }}</a></li>
            <li class="breadcrumb-item active">{{ __('message.system-settings') }}</li>
        </ol>
    </div><!-- /.col -->
@stop
@section('content')

    <div id="alertMessage3"></div>
    <div id="error2"></div>

<div class="row">

    <div class="col-md-12">
        <div class="card card-secondary card-outline">
            <div class="box-header">

            </div>

            <div class="card-body">
                {!! Form::model($set,['url'=>'settings/system','method'=>'patch','files'=>true, 'enctype' => 'multipart/form-data']) !!}
                <div class="row">
                 <div class="col-md-6">
              

                  

                    <tr>

                        <td><b>{!! Form::label('company',Lang::get('message.company-name'),['class'=>'required']) !!}</b></td>
                        <td>
                            <div class="form-group {{ $errors->has('company') ? 'has-error' : '' }}">


                                {!! Form::text('company',null,['class' => 'form-control']) !!}
                                


                            </div>
                        </td>

                    </tr>

                    <tr>

                        <td><b>{!! Form::label('company_email',Lang::get('message.company-email'),['class'=>'required']) !!}</b></td>
                       
                        <td>
                            <div class="form-group {{ $errors->has('company_email') ? 'has-error' : '' }}">


                                {!! Form::email('company_email', null, ['class' => 'form-control']) !!}
                                


                            </div>
                        </td>

                    </tr>
                      <tr>

                        <td><b>{!! Form::label('title',Lang::get('message.app-title')) !!}</b></td>
                        <td>
                            <div class="form-group {{ $errors->has('company') ? 'has-error' : '' }}">


                                {!! Form::text('title',null,['class' => 'form-control']) !!}
                                


                            </div>
                        </td>

                    </tr>
                    <tr>

                        <td><b>{!! Form::label('website',Lang::get('message.website'),['class'=>'required']) !!}</b></td>
                        <td>
                            <div class="form-group {{ $errors->has('website') ? 'has-error' : '' }}">


                                {!! Form::text('website',null,['class' => 'form-control']) !!}
                               

                            </div>
                        </td>

                    </tr>
                    <tr>

                        <td><b>{!! Form::label('phone',Lang::get('message.phone'),['class'=>'required']) !!}</b></td>
                        <td>
                            <div class="form-group {{ $errors->has('phone') ? 'has-error' : '' }}">


                                {!! Form::input('tel', 'phone', null, ['class' => 'form-control selected-dial-code', 'id' => 'phone']) !!}

                                {!! Form::hidden('phone_code',null,['id'=>'phone_code_hidden']) !!}
                                {!! Form::hidden('phone_country_iso',null,['id' => 'phone_country_iso']) !!}
                                <span id="valid-msg" class="hide"></span>
                                <span id="error-msg" class="hide"></span>
                               

                            </div>
                        </td>

                    </tr>
                    <tr>

                        <td><b>{!! Form::label('address',Lang::get('message.address'),['class'=>'required']) !!}</b></td>
                        <td>
                            <div class="form-group {{ $errors->has('address') ? 'has-error' : '' }}">

                                {!! Form::textarea('address',null,['class' => 'form-control','size' => '128x10','id'=>'address']) !!}
                               
                            </div>
                        </td>

                    </tr>
                     <tr>

                        <td><b>{!! Form::label('City',Lang::get('message.city')) !!}</b></td>
                        <td>
                            <div class="form-group {{ $errors->has('city') ? 'has-error' : '' }}">


                                {!! Form::text('city',null,['class' => 'form-control']) !!}
                                

                            </div>
                        </td>

                    </tr>

                    <tr>

                        <td><b>{!! Form::label('zip','Zip') !!}</b></td>
                        <td>
                            <div class="form-group {{ $errors->has('zip') ? 'has-error' : '' }}">


                                {!! Form::text('zip',null,['class' => 'form-control']) !!}
                                

                            </div>
                        </td>

                    </tr>

                    <tr>

                        <td><b>{!! Form::label('knowledge_base_url','Knowledge Base URL') !!}</b></td>
                        <i class="fas fa-question-circle" data-toggle="tooltip" data-placement="top" title="" data-original-title="{{ __('message.url_installation') }}"></i>
                        <td>
                            <div class="form-group {{ $errors->has('knowledge_base_url') ? 'has-error' : '' }}">


                                {!! Form::text('knowledge_base_url',null,['class' => 'form-control']) !!}
                                

                            </div>
                        </td>

                    </tr>
             
            </div>
            <div class="col-md-6">
            
                    <tr>

                        <td><b>{!! Form::label('country',Lang::get('message.country') ,['class'=>'required']) !!}</b></td>
                        <td>
                            <div class="form-group {{ $errors->has('city') ? 'has-error' : '' }}">


                                <!-- {!! Form::text('country',null,['class' => 'form-control']) !!} -->
                                <!-- <p><i> {{Lang::get('message.country')}}</i> </p> -->
                                  <?php $countries = \App\Model\Common\Country::pluck('nicename', 'country_code_char2')->toArray(); ?>

                     <select name="country" value= "Choose" id="country" onChange="getCountryAttr(this.value)" class="form-control selectpicker" data-live-search="true" data-live-search-placeholder="Search" data-dropup-auto="false" data-size="10">
                             <option value="">{{ __('message.choose') }}</option>
                           @foreach($countries as $key=>$country)
                              <option value="{{$key}}" <?php  if(in_array($country, $selectedCountry) ) { echo "selected";} ?>>{{$country}}</option>
                          @endforeach
                          </select>


                    </div>

                           
                        </td>

                    </tr>

                     <tr class="form-group ">
                              
                               

                             <div class="form-group cin">
                                  <td>
                                    {!! Form::label('CIN No.',Lang::get('CIN')) !!}
                                </td>

                                 <td>
                                {!! Form::text('cin_no',null,['class' => 'form-control','id'=>'cin']) !!}

                            </div>
                                     
                                 </td>
                          
                        </tr>

                     <tr class="form-group ">
                              <div class="form-group gstin">
                                 <td>
                                    {!! Form::label('GSTIN',Lang::get('GSTIN')) !!}
                                </td>

                                 <td>
                                     
                                    {!! Form::text('gstin',null,['class' => 'form-control','id'=>'gstin']) !!}
                                 </div>
                                 </td>
                          
                        </tr>

                        

                    <tr>

                        <td><b>{!! Form::label('state',Lang::get('message.state') ,['class'=>'required']) !!}</b></td>
                        <td>
                        <select name="state" id="state-list" class="form-control">
                                @if($set->state)
                             <option value="{{$state['id']}}">{{$state['name']}}</option>
                            @endif
                            <option value="">{{ __('message.choose') }}</option>
                            @foreach($states as $key=>$value)
                            <option value="{{$key}}">{{$value}}</option>
                            @endforeach

                        </select>
                        </td>
                    </tr>
                    <br>
                        <tr>

                        <td><b>{!! Form::label('default_currency',Lang::get('message.default-currency') ,['class'=>'required']) !!}</b></td>
                        <td>
                             <?php $currencies = \App\Model\Payment\Currency::where('status',1)->pluck('name','code')->toArray(); 
                             ?>
                         <select name="default_currency" value= "Choose"  class="form-control selectpicker" data-live-search="true" data-live-search-placeholder="Search" data-dropup-auto="false" data-size="10">
                               <option value="">{{ __('message.choose') }}</option>
                           @foreach($currencies as $key=>$currency)
                              <option value="{{$key}}" <?php  if(in_array($currency, $selectedCurrency) ) { echo "selected";} ?>>{{$currency}}</option>
                          @endforeach

                        </select>
                        </td>
                    </tr>
                    <br>
                      <tr>
                     
                        <td><b>{!! Form::label('logo',Lang::get('message.admin-logo')) !!}</b></td>
                        <td>
                            <div class="form-group {{ $errors->has('admin-logo') ? 'has-error' : '' }}">
                                   {{ __('Upload Application Logo') }}

                                <div class="d-flex align-items-center mt-1">
                                    @if($set->admin_logo)
                                        <img src="{{ $set->admin_logo }}" class="img-thumbnail shadow-sm border" style="height: 50px; width: 100px;" alt="Application Logo" id="preview-admin-logo">
                                    @endif

                                    <div class="custom-file ml-3">
                                        {!! Form::file('admin-logo', ['class' => 'custom-file-input cursor-pointer', 'id' => 'admin-logo' , 'role' => 'button']) !!}
                                        <label role="button" class="custom-file-label cursor-pointer" for="admin-logo">{{ __('Choose file') }}</label>
                                    </div>
                                </div>

                                @if($errors->has('admin-logo'))
                                    <small class="form-text text-danger mt-1">
                                        <i class="fas fa-exclamation-circle"></i> {{ $errors->first('admin-logo') }}
                                    </small>
                                @endif
                            </div>
                        </td>
                       
                    </tr>

                     <tr>

                        <td><b>{!! Form::label('icon',Lang::get('message.fav-icon')) !!}</b></td>

                        <td>
                            <div class="form-group {{ $errors->has('fav-icon') ? 'has-error' : '' }}">
                                    {{ __('Upload favicon for Admin and Client Panel') }}

                                <div class="d-flex align-items-center mt-1">
                                    @if($set->fav_icon)
                                        <img src="{{ $set->fav_icon }}" class="img-thumbnail shadow-sm border" style="height: 50px; width: 100px;" alt="Favicon" id="preview-fav-icon">
                                    @endif

                                    <div class="custom-file ml-3">
                                        {!! Form::file('fav-icon', ['class' => 'custom-file-input', 'id' => 'fav-icon' ,'role' => 'button']) !!}
                                        <label role="button" class="custom-file-label" for="fav-icon">{{ __('Choose file') }}</label>
                                    </div>
                                </div>

                                @if($errors->has('fav-icon'))
                                    <small class="form-text text-danger mt-1">
                                        <i class="fas fa-exclamation-circle"></i> {{ $errors->first('fav-icon') }}
                                    </small>
                                @endif
                            </div>
                        </td>
                       
                    </tr>

                     <tr>

                        <td><b>{!! Form::label('favicon_title',Lang::get('message.fav-title-admin')) !!}</b></td>
                        <td>
                            <div class="form-group {{ $errors->has('favicon_title') ? 'has-error' : '' }}">


                                {!! Form::text('favicon_title',null,['class' => 'form-control']) !!}
                                


                            </div>
                        </td>

                    </tr>

                     <tr>

                        <td><b>{!! Form::label('favicon_title_client',Lang::get('message.fav-title-client')) !!}</b></td>
                        <td>
                            <div class="form-group {{ $errors->has('favicon_title_client') ? 'has-error' : '' }}">


                                {!! Form::text('favicon_title_client',null,['class' => 'form-control']) !!}
                                


                            </div>
                        </td>

                    </tr>

                    <tr>

                        <td><b>{!! Form::label('logo',Lang::get('message.client-logo')) !!}</b></td>
                        <td>
                            <div class="form-group {{ $errors->has('logo') ? 'has-error' : '' }}">
                                {{Lang::get('Upload the company logo')}}
                                <div class="d-flex align-items-center mt-1">
                                    @if($set->logo)
                                        <img src="{{ $set->logo }}" class="img-thumbnail shadow-sm border"
                                             style="height: 50px; width: 100px;" alt="Company Logo" id="preview-logo">
                                    @endif

                                    <div class="custom-file ml-3">
                                        {!! Form::file('logo', ['class' => 'custom-file-input', 'id' => 'logo', 'role' => 'button', 'onchange' => 'previewImage("preview-logo", "logo")']) !!}
                                        <label role="button" class="custom-file-label" for="logo">{{ __('Choose file') }}</label>
                                    </div>
                                </div>

                                @if($errors->has('logo'))
                                    <small class="form-text text-danger mt-1">
                                        <i class="fas fa-exclamation-circle"></i> {{ $errors->first('logo') }}
                                    </small>
                                @endif
                            </div>
                        </td>

                    </tr>


            </div>

                </div>
                <button type="submit" class="btn btn-primary" id="submit" name="submit" value="save" ><i class="fa fa-sync-alt">&nbsp;</i>{!!Lang::get('message.update')!!}</button>
               

                {!! Form::close() !!}
        </div>
    </div>
</div>
<script src="https://cdnjs.cloudflare.com/ajax/libs/sweetalert/2.1.0/sweetalert.min.js"></script>
<script type="text/javascript">

    $(document).ready(function () {
        $('.custom-file-input').on('change', function() {
            let fileName = $(this).val().split('\\').pop();
            $(this).next('.custom-file-label').addClass('selected').html(fileName);
        });
    });

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
    var telInput = $('#phone');
    addressDropdown = $("#country");
     errorMsg = document.querySelector("#error-msg"),
    validMsg = document.querySelector("#valid-msg");
     var reset = function() {
      errorMsg.innerHTML = "";
      errorMsg.classList.add("hide");
      validMsg.classList.add("hide");
    };
     $('.intl-tel-input').css('width', '100%');
    telInput.on('input blur submit', function () {
      reset();
        if ($.trim(telInput.val())) {
            if (validatePhoneNumber(telInput.get(0))) {
              $('#phone').css("border-color","");
              validMsg.classList.remove("hide");
              $('#submit').attr('disabled',false);
            } else {
             errorMsg.innerHTML = "Please enter a valid number";
             $('#phone').css("border-color","red");
             $('#error-msg').css({"color":"red","margin-top":"5px"});
             errorMsg.classList.remove("hide");
             $('#submit').attr('disabled',true);
            }
        }
    });

     addressDropdown.change(function() {
         updateCountryCodeAndFlag(telInput.get(0), addressDropdown.val());
             if ($.trim(telInput.val())) {
            if (validatePhoneNumber(telInput.get(0))) {
              $('#phone').css("border-color","");
              errorMsg.classList.add("hide");
                errorMsg.innerHTML = "";
              $('#submit').attr('disabled',false);
            } else {
                errorMsg.innerHTML = "Please enter a valid number";
             $('#phone').css("border-color","red");
             $('#error-msg').css({"color":"red","margin-top":"5px"});
             errorMsg.classList.remove("hide");
             $('#submit').attr('disabled',true);
            }
        }
    });
    $('input').on('focus', function () {
        $(this).parent().removeClass('has-error');
    });

    var mobInput = document.querySelector("#phone");
    updateCountryCodeAndFlag(mobInput, "{{ $set->phone_country_iso }}")
    $('form').on('submit', function (e) {
        $('#phone_code_hidden').val(telInput.attr('data-dial-code'));
        $('#phone_country_iso').val(telInput.attr('data-country-iso').toUpperCase());
        telInput.val(telInput.val().replace(/\D/g, ''));
    });


});
 
 
     $('.show_confirm').click(function(event) {
          var id = $(this).attr('id');
          var column = $(this).attr('value');
        

        
            if (confirm("{{Lang::get('message.confirm') }}")) 
        {
                $.ajax({
               
                type: 'POST',
                url: "{{url('changeLogo')}}",
                dataType: 'json',
                headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
                data: {id:id,column:column,"_token": "{{ csrf_token() }}"},
               success: function (response) {
                    $('#alertMessage3').show();
                    var result =  '<div class="alert alert-success"> <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button><strong><i class="far fa-thumbs-up"></i>{{ __('message.well_done') }} </strong>'+response.message+'!</div>';
                    $('#alertMessage3').html(result+ ".");
                    setTimeout(function(){
                       window.location.reload(1);
                    }, 3000); 
                               
               },
               error: function (ex) {
        
                    var myJSON = JSON.parse(ex.responseText);
                    var html = '<div class="alert alert-danger"><button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button><strong>{{ __('message.oh_snap') }} </strong>{{ __('message.something_wrong') }}<br><br><ul>';
                    for (var key in myJSON)
                    {
                        html += '<li>' + myJSON[key][0] + '</li>'
                    }
                    html += '</ul></div>';

                    $('#error2').show();
                    document.getElementById('error2').innerHTML = html;
               }
              
            });
            }
            return false;
      });
  
</script>
<script>
     $('ul.nav-sidebar a').filter(function() {
        return this.id == 'setting';
    }).addClass('active');

    // for treeview
    $('ul.nav-treeview a').filter(function() {
        return this.id == 'setting';
    }).parentsUntil(".nav-sidebar > .nav-treeview").addClass('menu-open').prev('a').addClass('active');
</script>
<script>
    $(document).ready(function(){
        var country = $('#country').val();
        if (country == 'IN')
        {
            $('.cin').show();
            $('.gstin').show();
        } else {
            $('.cin').hide();
            $('.gstin').hide();
        }
    })

    $('#country').on('change', function (){
       if($(this).val() == 'IN'){
        $('.cin').show();
        $('.gstin').show();
       } else {
         $('.cin').hide();
        $('.gstin').hide();
        $('#cin').val('');
        $('#gstin').val('');
       }
    })


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
                // $("#mobile_code").val(data);
                $("#phone_code_hidden").val(data);
            }
        });
    }

      $(document).ready(function(){
    $('[data-toggle="tooltip"]').tooltip();
});


    ['logo', 'admin-logo', 'fav-icon'].forEach((id) => {
        const input = document.getElementById(id);
        const preview = document.getElementById(`preview-${id}`);

        if (input && preview) {
            input.addEventListener('change', () => {
                // Clear previous preview if file selection is canceled
                if (!input.files.length) {
                    preview.src = '';
                    return;
                }
                previewImage(input, preview);
            });
        }
    });

    function previewImage(input, preview) {
        const file = input.files?.[0];

        if (!file) return;

        const reader = new FileReader();

        reader.onload = (e) => {
            preview.src = e.target.result;
        };

        reader.onerror = () => {
            input.value = '';
        };

        reader.readAsDataURL(file);
    }
</script>


        

@stop