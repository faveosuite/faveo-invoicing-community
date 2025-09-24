@extends('themes.default1.layouts.master')
@section('title')
    {{ __('message.system-settings') }}
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
    <style>
        .system-error{
            font-size:80%;
            color:#dc3545;
        }

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
                {!! html()->modelForm($set, 'PATCH' ,url('settings/system'))->attribute('enctype', 'multipart/form-data')->acceptsFiles()->id('companyDetailsForm')->open() !!}
                <div class="row">
                 <div class="col-md-6">
              

                  

                    <tr>

                        <td><b>{!! html()->label(Lang::get('message.company-name'), 'company')->class('required') !!}</b></td>
                        <td>
                            <div class="form-group {{ $errors->has('company') ? 'has-error' : '' }}">


                                {!! html()->text('company')->class('form-control'.($errors->has('company') ? ' is-invalid' : '')) !!}
                                @error('company')
                                <span class="error-message"> {{$message}}</span>
                                @enderror
                                <div class="input-group-append">
                                </div>


                            </div>
                        </td>

                    </tr>

                    <tr>

                        <td><b>{!! html()->label(Lang::get('message.company-email'), 'company_email')->class('required') !!}</b></td>

                        <td>
                            <div class="form-group {{ $errors->has('company_email') ? 'has-error' : '' }}">


                                {!! html()->email('company_email')->class('form-control'.($errors->has('company_email') ? ' is-invalid' : '')) !!}
                                @error('company_email')
                                <span class="error-message"> {{$message}}</span>
                                @enderror
                                <span id="email-error-msg" class="hide"></span>
                                <div class="input-group-append">
                                </div>


                            </div>
                        </td>

                    </tr>
                      <tr>

                          <td><b>{!! html()->label(Lang::get('message.app-title'), 'title') !!}</b></td>
                          <td>
                            <div class="form-group {{ $errors->has('company') ? 'has-error' : '' }}">


                                {!! html()->text('title')->class('form-control'.($errors->has('title') ? ' is-invalid' : '')) !!}
                                @error('title')
                                <span class="error-message"> {{$message}}</span>
                                @enderror

                            </div>
                        </td>

                    </tr>
                    <tr>

                        <td><b>{!! html()->label(Lang::get('message.website'), 'website')->class('required') !!}</b></td>
                        <td>
                            <div class="form-group {{ $errors->has('website') ? 'has-error' : '' }}">

                                {!! html()->text('website')->class('form-control'.($errors->has('website') ? ' is-invalid' : ''))->placeholder('https://example.com') !!}
                                @error('website')
                                <span class="error-message"> {{$message}}</span>
                                @enderror
                                <div class="input-group-append">
                                </div>

                            </div>
                        </td>

                    </tr>
                    <tr>

                        <td><b>{!! html()->label(Lang::get('message.phone'), 'phone')->class('required') !!}</b></td>
                        <td>
                            <div class="form-group {{ $errors->has('phone') ? 'has-error' : '' }}">



                                {!! html()->input('tel', 'phone')->class('form-control selected-dial-code'.($errors->has('phone') ? ' is-invalid' : ''))->id('phone') !!}

                                {!! html()->hidden('phone_code')->id('phone_code_hidden') !!}
                                {!! html()->hidden('phone_country_iso')->id('phone_country_iso') !!}
                                @error('phone')
                                <span class="error-message"> {{$message}}</span>
                                @enderror
                                <span id="valid-msg" class="hide"></span>
                                <span id="error-msg" class="hide"></span>
                                <div class="input-group-append">
                                </div>

                            </div>
                        </td>

                    </tr>
                    <tr>

                        <td><b>{!! html()->label(Lang::get('message.address'), 'address')->class('required') !!}</b></td>
                        <td>
                            <div class="form-group {{ $errors->has('address') ? 'has-error' : '' }}">

                                {!! html()->textarea('address')->class('form-control'.($errors->has('address') ? ' is-invalid' : ''))->id('address')->attribute('rows', 10)->attribute('cols', 128) !!}
                                @error('address')
                                <span class="error-message"> {{$message}}</span>
                                @enderror
                                <div class="input-group-append">
                                </div>
                            </div>
                        </td>

                    </tr>
                     <tr>

                         <td><b>{!! html()->label(Lang::get('message.city'), 'City') !!}</b></td>
                         <td>
                            <div class="form-group {{ $errors->has('city') ? 'has-error' : '' }}">


                                {!! html()->text('city')->class('form-control'.($errors->has('city') ? ' is-invalid' : '')) !!}
                                @error('city')
                                <span class="error-message"> {{$message}}</span>
                                @enderror

                            </div>
                        </td>

                    </tr>

                    <tr>

                        <td><b>{!! html()->label( __('message.system_zip'), 'zip') !!}</b></td>
                        <td>
                            <div class="form-group {{ $errors->has('zip') ? 'has-error' : '' }}">


                                {!! html()->text('zip')->class('form-control'.($errors->has('zip') ? ' is-invalid' : ''))->id('zip1') !!}
                                <span id="zip-error-msg"></span>
                                @error('zip')
                                <span class="error-message"> {{$message}}</span>
                                @enderror

                            </div>
                        </td>

                    </tr>

                    <tr>

                        <td><b>{!! html()->label( __('message.knowledge_base_url'), 'knowledge_base_url') !!}</b></td>
                        <i class="fas fa-question-circle" data-toggle="tooltip" data-placement="top" title="" data-original-title="{{Lang::get('message.url_tooltip')}}"></i>
                        <td>
                            <div class="form-group {{ $errors->has('knowledge_base_url') ? 'has-error' : '' }}">

                                {!! html()->text('knowledge_base_url')->class('form-control'.($errors->has('knowledge_base_url') ? ' is-invalid' : ''))->id('knowledge_base_url')->placeholder('https://example.com') !!}
                                @error('knowledge_base_url')
                                <span class="error-message"> {{$message}}</span>
                                @enderror
                                <span id="url-error-msg" class="hide"></span>



                            </div>
                        </td>

                    </tr>

                     <tr>
                         <td>
                             <b>{!! html()->label(__('message.default_language'))->for('language')->class('required') !!}</b>
                         </td>
                         <td>
                             <select name="language" class="form-control" id="default_lang" required>
                                 @if($defaultLang == '')
                                     <option value="">{{ __('message.select_default_language') }}</option>
                                 @endif
                                 @foreach($languages as $language)
                                     <option value="{{ $language->locale }}"
                                             {{ $defaultLang === $language->locale ? 'selected' : '' }}>
                                         {{ $language->name }} ({{ $language->translation }}) ({{ $language->locale }})
                                     </option>
                                 @endforeach
                             </select>
                         </td>
                     </tr>
                     </br>

            </div>
            <div class="col-md-6">
            
                    <tr>

                        <td><b>{!! html()->label(Lang::get('message.country'), 'country')->class('required') !!}</b></td>
                        <td>
                            <div class="form-group {{ $errors->has('city') ? 'has-error' : '' }}">


                                <!-- {!! html()->text('country')->class('form-control') !!} -->
                                <!-- <p><i> {{Lang::get('message.country')}}</i> </p> -->
                                  <?php $countries = \App\Model\Common\Country::pluck('nicename', 'country_code_char2')->toArray(); ?>

                      <select name="country" value= "Choose" id="country" onChange="getCountryAttr(this.value)" class="form-control selectpicker {{$errors->has('country') ? ' is-invalid' : ''}}" data-live-search="true" data-live-search-placeholder="Search" data-dropup-auto="false" data-size="10">
                             <option value="">{{ __('message.choose') }}</option>
                           @foreach($countries as $key=>$country)
                              <option value="{{$key}}" <?php  if(in_array($country, $selectedCountry) ) { echo "selected";} ?>>{{$country}}</option>
                          @endforeach
                          </select>
                                @error('country')
                                <span class="error-message"> {{$message}}</span>
                                @enderror
                                <div class="input-group-append">
                                </div>

                    </div>

                           
                        </td>

                    </tr>

                     <tr class="form-group ">
                              
                               

                             <div class="form-group cin">
                                  <td>
                                      {!! html()->label(Lang::get('message.cin'), 'CIN No.') !!}
                                  </td>

                                 <td>
                                     {!! html()->text('cin_no')->class('form-control'.($errors->has('cin_no') ? ' is-invalid' : ''))->id('cin') !!}
                                     @error('cin_no')
                                     <span class="error-message"> {{$message}}</span>
                                 @enderror
                            </div>
                                     
                                 </td>
                          
                        </tr>

                     <tr class="form-group ">
                              <div class="form-group gstin">
                                 <td>
                                     {!! html()->label(Lang::get('message.gstin'), 'GSTIN') !!}
                                 </td>

                                 <td>

                                     {!! html()->text('gstin')->class('form-control'.($errors->has('gstin') ? ' is-invalid' : ''))->id('gstin') !!}
                                     <span class="hide" id="gst-error-msg"></span>
                                     @error('gstin')
                                     <span class="error-message"> {{$message}}</span>
                                  @enderror
                                 </div>
                                 </td>
                          
                        </tr>

                        

                    <tr>

                        <td><b>{!! html()->label(Lang::get('message.state'), 'state')->class('required') !!}</b></td>
                        <td>
                        <select name="state" id="state-list" class="form-control {{$errors->has('state') ? ' is-invalid' : ''}}">
                                @if($set->state)
                             <option value="{{$state['id']}}">{{$state['name']}}</option>
                            @endif
                            <option value="">{{ __('message.choose') }}</option>
                            @foreach($states as $key=>$value)
                            <option value="{{$key}}">{{$value}}</option>
                            @endforeach

                        </select>
                            @error('state')
                            <span class="error-message"> {{$message}}</span>
                            @enderror
                        </td>
                    </tr>
                    <br>
                        <tr>

                            <td><b>{!! html()->label(Lang::get('message.default-currency'), 'default_currency')->class('required') !!}</b></td>
                            <td>
                             <?php $currencies = \App\Model\Payment\Currency::where('status',1)->pluck('name','code')->toArray(); 
                             ?>
                         <select name="default_currency" value= "Choose"  class="form-control selectpicker {{$errors->has('default_currency') ? ' is-invalid' : ''}}" data-live-search="true" data-live-search-placeholder="Search" data-dropup-auto="false" data-size="10">
                               <option value="">{{ __('message.choose') }}</option>
                           @foreach($currencies as $key=>$currency)
                              <option value="{{$key}}" <?php  if(in_array($currency, $selectedCurrency) ) { echo "selected";} ?>>{{$currency}}</option>
                          @endforeach

                        </select>
                            @error('default_currency')
                            <span class="error-message"> {{$message}}</span>
                            @enderror
                            <div class="input-group-append">
                            </div>
                        </td>
                    </tr>
                    <br>
                      <tr>

                          <td><b>{!! html()->label(Lang::get('message.admin-logo'), 'logo') !!}</b></td>
                          <td>
                            <div class="form-group {{ $errors->has('admin-logo') ? 'has-error' : '' }}">
                                   {{ __('message.upload_application_logo') }}

                                <div class="d-flex align-items-center mt-1">
                                    @if($set->admin_logo)
                                        <img src="{{ $set->admin_logo }}" class="img-thumbnail shadow-sm border" style="height: 50px; width: 100px;" alt="Application Logo" id="preview-admin-logo">
                                    @endif

                                    <div class="custom-file ml-3">
                                        {!! html()->file('admin-logo')->class('custom-file-input cursor-pointer'.($errors->has('admin-logo') ? ' is-invalid' : ''))->id('admin-logo')->attribute('role', 'button') !!}
                                        <label role="button" class="custom-file-label cursor-pointer" for="admin-logo">{{ __('message.choose_file') }}</label>
                                        @error('admin_logo')
                                        <span class="error-message"> {{$message}}</span>
                                        @enderror
                                    </div>
                                </div>
                                <span class="hide system-error" id="admin-err-Msg"></span>
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
                                    {{ __('message.upload_favicon_admin_client_panel') }}

                                <div class="d-flex align-items-center mt-1">
                                    @if($set->fav_icon)
                                        <img src="{{ $set->fav_icon }}" class="img-thumbnail shadow-sm border" style="height: 50px; width: 100px;" alt="Favicon" id="preview-fav-icon">
                                    @endif

                                    <div class="custom-file ml-3">
                                        {!! html()->file('fav-icon')->class('custom-file-input'.($errors->has('fav-icon') ? ' is-invalid' : ''))->id('fav-icon')->attribute('role', 'button') !!}
                                        <label role="button" class="custom-file-label" for="fav-icon">{{ __('message.choose_file') }}</label>
                                    </div>
                                </div>
                                <span class="hide system-error" id="favicon-err-Msg"></span>
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


                                {!! html()->text('favicon_title')->class('form-control'.($errors->has('favicon_title') ? ' is-invalid' : '')) !!}
                                @error('favicon_title')
                                <span class="error-message"> {{$message}}</span>
                                @enderror


                            </div>
                        </td>

                    </tr>

                     <tr>

                         <td><b>{!! html()->label(Lang::get('message.fav-title-client'), 'favicon_title_client') !!}</b></td>
                         <td>
                            <div class="form-group {{ $errors->has('favicon_title_client') ? 'has-error' : '' }}">


                                {!! html()->text('favicon_title_client')->class('form-control'.($errors->has('favicon_title_client') ? ' is-invalid' : '')) !!}


                                @error('favicon_title_client')
                                <span class="error-message"> {{$message}}</span>
                                @enderror

                            </div>
                        </td>

                    </tr>

                <tr>

                        <td><b>{!! html()->label(Lang::get('message.client-logo'), 'logo') !!}</b></td>
                        <td>
                            <div class="form-group {{ $errors->has('logo') ? 'has-error' : '' }}">
                                {{ __('message.upload_company_logo') }}

                                <div class="d-flex align-items-center mt-1">
                                    @if($set->logo)
                                        <img src="{{ $set->logo }}" class="img-thumbnail shadow-sm border"
                                             style="height: 50px; width: 100px;" alt="Company Logo" id="preview-logo">
                                    @endif

                                    <div class="custom-file ml-3">

                                        {!! html()->file('logo')->class('custom-file-input'.($errors->has('logo') ? ' is-invalid' : ''))->id('logo')->attribute('role', 'button')->attribute('onchange', 'previewImage("preview-logo", "logo")') !!}

                                        <label role="button" class="custom-file-label" for="logo">{{ __('message.choose_file') }}</label>
                                    </div>
                                </div>
                                <span class="hide system-error" id="logo-err-Msg"></span>

                                @if($errors->has('logo'))
                                    <small class="form-text text-danger mt-1">
                                        <i class="fas fa-exclamation-circle"></i> {{ $errors->first('logo') }}
                                    </small>
                                @endif
                        </div>
                    </td>

                </tr>

                <tr>
                    <td><b>{!! html()->label(Lang::get('message.auto_renewal'), 'company_email')!!}</b> <i class="fas fa-question-circle" data-toggle="tooltip" data-placement="top" title="" data-original-title="{{Lang::get('message.auto_renewal_tooltip')}}"></i>
                    </td>

                            <div class="form-group">
                                <label class="switch toggle_event_editing">
                                    <input type="checkbox" name="autorenewal_status" value="{{$set->autorenewal_status}}"
                                           class="checkbox1" id="updateRenewalStatus">
                                    <span class="slider round"></span>
                                </label>
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


    <script>
        $(document).ready(function () {
            var renewal_status=$(".checkbox1").val();
            if(renewal_status==1){
                $('#updateRenewalStatus').prop('checked',true);
            }else{
                $('#updateRenewalStatus').prop('checked',false);

            }


            var fup = document.getElementById('logo');
            var errMsg=document.getElementById('logo-err-Msg');
            $('#logo').on('change',function(e){
                const input = document.getElementById('logo');
                const file = input.files?.[0];
                var fileName = fup.value;
                var ext = fileName.substring(fileName.lastIndexOf('.') + 1);

                const maxSize = 2 * 1024 * 1024;
                if(ext !=="jpeg" && ext !=="jpg" && ext !=='png') {
                    fup.classList.add('is-invalid');
                    errMsg.innerText=@json(trans('message.image_invalid_message'));
                    document.getElementById('submit').disabled = true;
                    return false;
                }else if(file.size>maxSize){
                    fup.classList.add('is-invalid');
                    errMsg.innerText=@json(trans('message.image_invalid_size'));
                    document.getElementById('submit').disabled = true;
                    return false;
                }
                else {
                    errMsg.innerText='';
                    fup.classList.remove('is-invalid');
                    document.getElementById('submit').disabled = false;
                    return true;
                }
            });

            var fup1 = document.getElementById('admin-logo');
            var errMsg1=document.getElementById('admin-err-Msg');
            $('#admin-logo').on('change',function(e){

                const input = document.getElementById('admin-logo');
                const file = input.files?.[0];
                var fileName = fup1.value;
                var ext = fileName.substring(fileName.lastIndexOf('.') + 1);

                const maxSize = 2 * 1024 * 1024;


                if(ext !=="jpeg" && ext !=="jpg" && ext !=='png') {
                    fup1.classList.add('is-invalid');
                    errMsg1.innerText=@json(trans('message.image_invalid_message'));
                    document.getElementById('submit').disabled = true;
                    return false;
                }else if(file.size>maxSize){
                    fup1.classList.add('is-invalid');
                    errMsg1.innerText=@json(trans('message.image_invalid_size'));
                    document.getElementById('submit').disabled = true;
                    return false;
                }
                else {
                    errMsg1.innerText='';
                    fup1.classList.remove('is-invalid');
                    document.getElementById('submit').disabled = false;
                    return true;
                }});

            var fup2 = document.getElementById('fav-icon');
            var errMsg2=document.getElementById('favicon-err-Msg');
            $('#fav-icon').on('change',function(e){
                const input = document.getElementById('fav-icon');
                const file = input.files?.[0];
                var fileName = fup2.value;
                var ext = fileName.substring(fileName.lastIndexOf('.') + 1);

                const maxSize = 2 * 1024 * 1024;
                if(ext !=="jpeg" && ext !=="jpg" && ext !=='png') {
                    fup2.classList.add('is-invalid');
                    errMsg2.innerText=@json(trans('message.image_invalid_message'));
                    document.getElementById('submit').disabled = true;
                    return false;
                }else if(file.size>maxSize){
                    fup2.classList.add('is-invalid');
                    errMsg2.innerText=@json(trans('message.image_invalid_size'));
                    document.getElementById('submit').disabled = true;
                    return false;
                }
                else {
                    errMsg2.innerText='';
                    fup2.classList.remove('is-invalid');
                    document.getElementById('submit').disabled = false;
                    return true;
                }
                });


            $('.custom-file-input').on('change', function() {
                let fileName = $(this).val().split('\\').pop();
                $(this).next('.custom-file-label').addClass('selected').html(fileName);
            });
        });
        $(document).ready(function() {
            const userRequiredFields = {
                company:@json(trans('message.com_details.company_name')),
                company_email:@json(trans('message.com_details.company_email')),
                website:@json(trans('message.com_details.add_website')),
                phone_code:@json(trans('message.com_details.add_phone')),
                address:@json(trans('message.com_details.add_address')),
                country:@json(trans('message.com_details.add_country')),
                default_currency:@json(trans('message.com_details.default_currency')),
                state:@json(trans('message.com_details.add_state')),
                default_lang:@json(trans('message.please_enter_default_language')),

            };

            $('#submit').on('click', function (e) {

                const userFields = {
                    company:$('#company'),
                    company_email:$('#company_email'),
                    website:$('#website'),
                    address:$('#address'),
                    state:$('#state-list'),
                    country:$('#country'),
                    default_lang:$('#default_lang'),
                };


                // Clear previous errors
                Object.values(userFields).forEach(field => {
                    field.removeClass('is-invalid');
                    field.next('.error').remove();

                });

                let isValid = true;
                const showError = (field, message) => {
                    field.addClass('is-invalid');
                    field.after(`<span class='error invalid-feedback'>${message}</span>`);
                };

                // Validate required fields
                Object.keys(userFields).forEach(field => {
                    if (!userFields[field].val()) {
                        showError(userFields[field], userRequiredFields[field]);
                        isValid = false;
                    }
                });

                if(isValid  && !isValidURL(userFields.website.val())){
                    showError(userFields.website,@json(trans('message.page_details.valid_url')),);
                    isValid=false;
                }

                if(isValid && userFields.company.val().length>50){
                    showError(userFields.company,@json(trans('message.valid_name')),);
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
            ['company','company_email','website','phone','address','country','default_currency','state-list'].forEach(id => {

                document.getElementById(id).addEventListener('input', function () {
                    removeErrorMessage(this);

                });
            });

            function isValidURL(url) {
                const pattern = /^(https?:\/\/)?([\w-]+\.)+([a-z]{2,6})(\/[\w-]*)*(\?.*)?(#.*)?$/i;
                return pattern.test(url);
            }

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
     errorMsg = document.querySelector("#error-msg");
    validMsg = document.querySelector("#valid-msg");

    var url=$('#knowledge_base_url');
    urlerrorMsg = document.querySelector("#url-error-msg");
        var reset = function() {
      errorMsg.innerHTML = "";
      errorMsg.classList.add("hide");
      validMsg.classList.add("hide");
    };
     $('.intl-tel-input').css('width', '100%');

        function isValidURL(url) {
            const pattern = /^(https?:\/\/)?([\w-]+\.)+([a-z]{2,6})(\/[\w-]*)*(\?.*)?(#.*)?$/i;
            return pattern.test(url);
        }

        $('#submit').on('click',function(e) {
            if(telInput.val()===''){
                e.preventDefault();
                errorMsg.classList.remove("hide");
                errorMsg.innerHTML = @json(trans('message.user_edit_details.add_phone_number'));
                $('#phone').addClass('is-invalid');
                $('#phone').css("border-color", "#dc3545");
                $('#error-msg').css({"width": "100%", "margin-top": ".25rem", "font-size": "80%", "color": "#dc3545"});
            }

            if(url.val()!== '') {
                if (!isValidURL(url.val())) {
                    e.preventDefault();
                    urlerrorMsg.classList.remove("hide");
                    urlerrorMsg.innerHTML = @json(trans('message.page_details.valid_url'));
                    $('#knowledge_base_url').addClass('is-invalid');
                    $('#knowledge_base_url').css("border-color", "#dc3545");
                    $('#url-error-msg').css({
                        "width": "100%",
                        "margin-top": ".25rem",
                        "font-size": "80%",
                        "color": "#dc3545"
                    });

                }
            }
            gstinerrorMsg=document.querySelector("#gst-error-msg");
            var gstin=$('#gstin');

            if(gstin.val()!==''){
                if(gstin.val().length!=15 ){
                    e.preventDefault();
                    gstinerrorMsg.innerHTML = @json(trans('message.valid_gst_number'));
                    $('#gstin').addClass('is-invalid');
                    $('#gstin').css("border-color", "#dc3545");
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
        });
        function zipRegex(val) {
            var re = /^[a-zA-Z0-9]+$/;
            return re.test(val);
        }
    telInput.on('input blur submit', function () {
      reset();
        if ($.trim(telInput.val())) {
            if (validatePhoneNumber(telInput.get(0))) {
              $('#phone').css("border-color","");
              validMsg.classList.remove("hide");
              $('#submit').attr('disabled',false);
            } else {
             errorMsg.innerHTML = @json(trans('message.enter_valid_number'));
                errorMsg.classList.remove("hide");
                errorMsg.innerHTML = @json(trans('message.enter_valid_number'));
                $('#phone').css("border-color", "#dc3545");
                $('#error-msg').css({"color": "#dc3545", "margin-top": "5px", "font-size": "80%"});
            }
        }
    });



        function validateEmail(email) {
            const emailPattern = /^(?!.*\.\.)[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.(com|org|net|edu|gov|mil|co|io|biz|info|dev|xyz)$/;
            return emailPattern.test(email);

        }

        emailErrorMsg = document.querySelector("#email-error-msg");
        var emailReset = function() {
            emailErrorMsg.innerHTML = "";
            emailErrorMsg.classList.add("hide");
        };


        var email=$('#company_email');
        email.on('input blur', function () {
            emailReset();
            if ($.trim(email.val())) {
                if (validateEmail(email.val())) {
                    $('#company_email').css("border-color","");
                    $('#submit').attr('disabled',false);
                } else {
                    emailErrorMsg.classList.remove("hide");
                    emailErrorMsg.innerHTML = @json(trans('message.contact_error_email'));
                    $('#company_email').css("border-color","#dc3545");
                    $('#email-error-msg').css({"color":"#dc3545","margin-top":"5px","font-size":"80%"});
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
                errorMsg.innerHTML = @json(trans('message.enter_valid_number'));
             $('#phone').css("border-color","#dc3545");
             $('#error-msg').css({"color":"#dc3545","margin-top":"5px","font-size":"80%"});
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
  

     $('ul.nav-sidebar a').filter(function() {
        return this.id == 'setting';
    }).addClass('active');

    // for treeview
    $('ul.nav-treeview a').filter(function() {
        return this.id == 'setting';
    }).parentsUntil(".nav-sidebar > .nav-treeview").addClass('menu-open').prev('a').addClass('active');

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
        const originalSrc=preview.src;
        if (!file){
            return
        }


            const allowedTypes = ["image/png", "image/jpeg", "image/jpg"];

            if (allowedTypes.includes(file.type)) {
                const reader = new FileReader();

                reader.onload = (e) => {
                    preview.src = e.target.result;
                };

                reader.onerror = () => {
                    input.value = '';
                };

                reader.readAsDataURL(file);
            } else {
                input.value = '';
                preview.src = originalSrc;
            }

    }

        document.getElementById("zip1").addEventListener('input',function(){
            ziperrorMsg = document.querySelector("#zip-error-msg");
            $('#zip1').removeClass('is-invalid');
            $('#zip1').css("border-color", "silver");
            ziperrorMsg.innerHTML = '';
        });
</script>


        

@stop