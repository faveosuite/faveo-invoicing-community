@extends('themes.default1.layouts.master')
@section('title')
System Setting
@stop
@section('content-header')
    <div class="col-sm-6">
        <h1>Company Details</h1>
    </div>
    <div class="col-sm-6">
        <ol class="breadcrumb float-sm-right">
            <li class="breadcrumb-item"><a href="{{url('/')}}"><i class="fa fa-dashboard"></i> Home</a></li>
            <li class="breadcrumb-item"><a href="{{url('settings')}}"><i class="fa fa-dashboard"></i> Settings</a></li>
            <li class="breadcrumb-item active">System Settings</li>
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
                {!! html()->modelForm($set, url('settings/system'))->patch()->enctype('multipart/form-data')->acceptsFiles()->open() !!}
                <div class="row">
                 <div class="col-md-6">
              

                  

                    <tr>

                        <td><b>{!! html()->label(Lang::get('message.company-name'), 'company')->class('required') !!}</b></td>
                        <td>
                            <div class="form-group {{ $errors->has('company') ? 'has-error' : '' }}">


                                {!! html()->text('company')->class('form-control') !!}



                            </div>
                        </td>

                    </tr>

                    <tr>

                        <td><b>{!! html()->label(Lang::get('message.company-email'), 'company_email')->class('required') !!}</b></td>

                        <td>
                            <div class="form-group {{ $errors->has('company_email') ? 'has-error' : '' }}">


                                {!! html()->email('company_email')->class('form-control') !!}



                            </div>
                        </td>

                    </tr>
                      <tr>

                          <td><b>{!! html()->label(Lang::get('message.app-title'), 'title') !!}</b></td>
                          <td>
                            <div class="form-group {{ $errors->has('company') ? 'has-error' : '' }}">


                                {!! html()->text('title')->class('form-control') !!}



                            </div>
                        </td>

                    </tr>
                    <tr>

                        <td><b>{!! html()->label(Lang::get('message.website'), 'website')->class('required') !!}</b></td>
                        <td>
                            <div class="form-group {{ $errors->has('website') ? 'has-error' : '' }}">


                                {!! html()->text('website')->class('form-control') !!}


                            </div>
                        </td>

                    </tr>
                    <tr>

                        <td><b>{!! html()->label(Lang::get('message.phone'), 'phone')->class('required') !!}</b></td>
                        <td>
                            <div class="form-group {{ $errors->has('phone') ? 'has-error' : '' }}">


                                {!! html()->input('tel', 'phone')->class('form-control selected-dial-code')->id('phone') !!}

                                {!! html()->hidden('phone_code')->id('phone_code_hidden') !!}
                                {!! html()->hidden('phone_country_iso')->id('phone_country_iso') !!}
                                <span id="valid-msg" class="hide"></span>
                                <span id="error-msg" class="hide"></span>
                               

                            </div>
                        </td>

                    </tr>
                    <tr>

                        <td><b>{!! html()->label(Lang::get('message.address'), 'address')->class('required') !!}</b></td>
                        <td>
                            <div class="form-group {{ $errors->has('address') ? 'has-error' : '' }}">

                                {!! html()->textarea('address')->class('form-control')->id('address')->attribute('rows', 10)->attribute('cols', 128) !!}

                            </div>
                        </td>

                    </tr>
                     <tr>

                         <td><b>{!! html()->label(Lang::get('message.city'), 'City') !!}</b></td>
                         <td>
                            <div class="form-group {{ $errors->has('city') ? 'has-error' : '' }}">


                                {!! html()->text('city')->class('form-control') !!}


                            </div>
                        </td>

                    </tr>

                    <tr>

                        <td><b>{!! html()->label('Zip', 'zip') !!}</b></td>
                        <td>
                            <div class="form-group {{ $errors->has('zip') ? 'has-error' : '' }}">


                                {!! html()->text('zip')->class('form-control') !!}


                            </div>
                        </td>

                    </tr>

                    <tr>

                        <td><b>{!! html()->label('Knowledge Base URL', 'knowledge_base_url') !!}</b></td>
                        <i class="fas fa-question-circle" data-toggle="tooltip" data-placement="top" title="" data-original-title="The URL provides detailed assistance for installation"></i>
                        <td>
                            <div class="form-group {{ $errors->has('knowledge_base_url') ? 'has-error' : '' }}">


                                {!! html()->text('knowledge_base_url')->class('form-control') !!}


                            </div>
                        </td>

                    </tr>
             
            </div>
            <div class="col-md-6">
            
                    <tr>

                        <td><b>{!! html()->label(Lang::get('message.country'), 'country')->class('required') !!}</b></td>
                        <td>
                            <div class="form-group {{ $errors->has('city') ? 'has-error' : '' }}">


                                <!-- {!! html()->text('country')->class('form-control') !!} -->
                                <!-- <p><i> {{Lang::get('message.country')}}</i> </p> -->
                                  <?php $countries = \App\Model\Common\Country::pluck('nicename', 'country_code_char2')->toArray(); ?>

                     <select name="country" value= "Choose" id="country" onChange="getCountryAttr(this.value)" class="form-control selectpicker" data-live-search="true" data-live-search-placeholder="Search" data-dropup-auto="false" data-size="10">
                             <option value="">Choose</option>
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
                                      {!! html()->label(Lang::get('CIN'), 'CIN No.') !!}
                                  </td>

                                 <td>
                                 {!! html()->text('cin_no')->class('form-control')->id('cin') !!}

                             </div>
                                     
                                 </td>
                          
                        </tr>

                     <tr class="form-group ">
                              <div class="form-group gstin">
                                 <td>
                                     {!! html()->label(Lang::get('GSTIN'), 'GSTIN') !!}
                                 </td>

                                 <td>

                                  {!! html()->text('gstin')->class('form-control')->id('gstin') !!}
                              </div>
                                 </td>
                          
                        </tr>

                        

                    <tr>

                        <td><b>{!! html()->label(Lang::get('message.state'), 'state')->class('required') !!}</b></td>
                        <td>
                        <select name="state" id="state-list" class="form-control">
                                @if($set->state)
                             <option value="{{$state['id']}}">{{$state['name']}}</option>
                            @endif
                            <option value="">Choose</option>
                            @foreach($states as $key=>$value)
                            <option value="{{$key}}">{{$value}}</option>
                            @endforeach

                        </select>
                        </td>
                    </tr>
                    <br>
                        <tr>

                            <td><b>{!! html()->label(Lang::get('message.default-currency'), 'default_currency')->class('required') !!}</b></td>
                            <td>
                             <?php $currencies = \App\Model\Payment\Currency::where('status',1)->pluck('name','code')->toArray(); 
                             ?>
                         <select name="default_currency" value= "Choose"  class="form-control selectpicker" data-live-search="true" data-live-search-placeholder="Search" data-dropup-auto="false" data-size="10">
                               <option value="">Choose</option>
                           @foreach($currencies as $key=>$currency)
                              <option value="{{$key}}" <?php  if(in_array($currency, $selectedCurrency) ) { echo "selected";} ?>>{{$currency}}</option>
                          @endforeach

                        </select>
                        </td>
                    </tr>
                    <br>
                      <tr>

                          <td><b>{!! html()->label(Lang::get('message.admin-logo'), 'logo') !!}</b></td>
                          <td>
                            <div class="form-group {{ $errors->has('admin-logo') ? 'has-error' : '' }}">
                                   {{ __('Upload Application Logo') }}

                                <div class="d-flex align-items-center mt-1">
                                    @if($set->admin_logo)
                                        <img src="{{ $set->admin_logo }}" class="img-thumbnail shadow-sm border" style="height: 50px; width: 100px;" alt="Application Logo" id="preview-admin-logo">
                                    @endif

                                    <div class="custom-file ml-3">
                                        {!! html()->file('admin-logo')->class('custom-file-input cursor-pointer')->id('admin-logo')->attribute('role', 'button') !!}
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

                         <td><b>{!! html()->label(Lang::get('message.fav-icon'), 'icon') !!}</b></td

                         <td>
                            <div class="form-group {{ $errors->has('fav-icon') ? 'has-error' : '' }}">
                                    {{ __('Upload favicon for Admin and Client Panel') }}

                                <div class="d-flex align-items-center mt-1">
                                    @if($set->fav_icon)
                                        <img src="{{ $set->fav_icon }}" class="img-thumbnail shadow-sm border" style="height: 50px; width: 100px;" alt="Favicon" id="preview-fav-icon">
                                    @endif

                                    <div class="custom-file ml-3">
                                        {!! html()->file('fav-icon')->class('custom-file-input')->id('fav-icon')->attribute('role', 'button') !!}
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

                         <td><b>{!! html()->label(Lang::get('message.fav-title-admin'), 'favicon_title') !!}</b></td>
                         <td>
                            <div class="form-group {{ $errors->has('favicon_title') ? 'has-error' : '' }}">


                                {!! html()->text('favicon_title')->class('form-control') !!}



                            </div>
                        </td>

                    </tr>

                     <tr>

                         <td><b>{!! html()->label(Lang::get('message.fav-title-client'), 'favicon_title_client') !!}</b></td>
                         <td>
                            <div class="form-group {{ $errors->has('favicon_title_client') ? 'has-error' : '' }}">


                                {!! html()->text('favicon_title_client')->class('form-control') !!}



                            </div>
                        </td>

                    </tr>

                    <tr>

                        <td><b>{!! html()->label(Lang::get('message.client-logo'), 'logo') !!}</b></td>
                        <td>
                            <div class="form-group {{ $errors->has('logo') ? 'has-error' : '' }}">
                                   {{ __('Upload the company logo') }}

                                <div class="d-flex align-items-center mt-1">
                                    @if($set->logo)
                                        <img src="{{ $set->logo }}" class="img-thumbnail shadow-sm border"
                                             style="height: 50px; width: 100px;" alt="Company Logo" id="preview-logo">
                                    @endif

                                    <div class="custom-file ml-3">
                                        {!! html()->file('logo')->class('custom-file-input')->id('logo')->attribute('role', 'button')->attribute('onchange', 'previewImage("preview-logo", "logo")') !!}

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


                {!! html()->closeModelForm() !!}
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
                    var result =  '<div class="alert alert-success"> <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button><strong><i class="far fa-thumbs-up"></i>Well Done! </strong>'+response.message+'!</div>';
                    $('#alertMessage3').html(result+ ".");
                    setTimeout(function(){
                       window.location.reload(1);
                    }, 3000); 
                               
               },
               error: function (ex) {
        
                    var myJSON = JSON.parse(ex.responseText);
                    var html = '<div class="alert alert-danger"><button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button><strong>Oh Snap! </strong>Something went wrong<br><br><ul>';
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