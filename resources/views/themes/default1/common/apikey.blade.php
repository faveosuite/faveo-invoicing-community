@extends('themes.default1.layouts.master')
@section('title')
    Api Key
@stop
@section('content-header')
    <style>
        .col-2, .col-lg-2, .col-lg-4, .col-md-2, .col-md-4,.col-sm-2 {
            width: 0px;
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
        .error-border {
            border-color: red;
        }


</style>
<div class="col-sm-6 md-6">
    <h1>API Keys</h1>
</div>
<div class="col-sm-6 md-6">
    <ol class="breadcrumb float-sm-right">
        <li class="breadcrumb-item"><a href="{{url('/')}}"><i class="fa fa-dashboard"></i> {{ __('message.home') }}</a></li>
        <li class="breadcrumb-item"><a href="{{url('settings')}}"> {{ __('message.settings') }}</a></li>
        <li class="breadcrumb-item active">{{ __('message.api_key') }}</li>
    </ol>
</div><!-- /.col -->
@stop
@section('content')


    <div class="card card-secondary card-outline">

        <!-- /.box-header -->
        <div class="card-body">
            <div id="alertMessage"></div>
            <div class="scrollit">
                <div class="row">
                    <div class="col-md-12">

                        <table class="table table-bordered ">
                            <thead>
                            <tr>

                                <th>{{ __('message.options') }}</th>
                                <th>{{ __('message.status') }}</th>
                                <th>{{ __('message.fields') }}</th>
                                <th>{{ __('message.action') }}</th>
                            </tr>
                            </thead>
                            <tbody>
                            <tr>

                                <td class="col-md-2">{{ __('message.auto_faveo_licenser') }}</td>
                                <td class="col-md-2">
                                    <label class="switch toggle_event_editing">

                                        <input type="checkbox" value="{{$status}}"  name="modules_settings"
                                               class="checkbox" id="License">
                                        <span class="slider round"></span>
                                    </label>

                                </td>

                                <td class="col-md-4 licenseEmptyField">
                                    {!! Form::label('lic_api_secret',Lang::get('message.lic_api_secret')) !!}
                                    {!! Form::text('license_api',null,['class' => 'form-control secretHide','disabled'=>'disabled'
                                    ]) !!}

                                    <!-- last name -->
                                    {!! Form::label('lic_api_url',Lang::get('message.lic_api_url')) !!} :
                                    {!! Form::text('license_api',null,['class' => 'form-control urlHide','disabled'=>'disabled']) !!}

                                    {!! Form::label('lic_client_id',Lang::get('message.lic_client_id')) !!} :
                                    {!! Form::text('license_client_id',null,['class' => 'form-control urlHide','disabled'=>'disabled']) !!}

                                    {!! Form::label('lic_client_secret',Lang::get('message.lic_client_secret')) !!} :
                                    {!! Form::text('license_client_secret',null,['class' => 'form-control urlHide','disabled'=>'disabled']) !!}

                                    {!! Form::label('lic_grant_type',Lang::get('message.lic_grant_type')) !!} :
                                    {!! Form::text('license_grant_type',null,['class' => 'form-control urlHide','disabled'=>'disabled']) !!}

                                </td>
                                <td class="col-md-4 LicenseField hide">


                                    <!-- last name -->
                                    {!! Form::label('lic_api_secret',Lang::get('message.lic_api_secret')) !!}
                                    {!! Form::text('license_api_secret',$licenseSecret,['class' => 'form-control','id'=>'license_api_secret']) !!}
                                    <h6 id="license_apiCheck"></h6>
                                    <br/>

                                    <!-- last name -->
                                    {!! Form::label('lic_api_url',Lang::get('message.lic_api_url')) !!} :
                                    {!! Form::text('license_api_url',$licenseUrl,['class' => 'form-control','id'=>'license_api_url']) !!}
                                    <h6 id="license_urlCheck"></h6>
                                    <br/>

                                    {!! Form::label('lic_client_id',Lang::get('message.lic_client_id')) !!} :
                                    {!! Form::text('license_client_id',$licenseClientId,['class' => 'form-control','id'=>'license_client_id']) !!}
                                    <h6 id="license_clientIdCheck"></h6>
                                    <br/>

                                    {!! Form::label('lic_client_secret',Lang::get('message.lic_client_secret')) !!} :
                                    {!! Form::text('license_client_secret',$licenseClientSecret,['class' => 'form-control','id'=>'license_client_secret']) !!}
                                    <h6 id="license_clientSecretCheck"></h6>
                                    <br/>


                                    {!! Form::label('lic_grant_type',Lang::get('message.lic_grant_type')) !!} :
                                    {!! Form::text('license_grant_type',$licenseGrantType,['class' => 'form-control','id'=>'license_grant_type']) !!}
                                    <h6 id="license_grantTypeCheck"></h6>


                                </td>
                            
                                <td class="col-md-2"><button type="submit" class="form-group btn btn-primary"  onclick="licenseDetails()" id="submit"><i class="fa fa-save">&nbsp;</i>{!!Lang::get('message.save')!!}</button></td>

                            </tr>

                            <tr>

                                <td class="col-md-2">{{ __('message.do_not_allow_domain') }}</td>
                                <td class="col-md-2">
                                    <label class="switch toggle_event_editing">

                                        <input type="checkbox" value="{{$domainCheckStatus}}"  name="domain_settings"
                                               class="checkbox15" id="domain">
                                        <span class="slider round"></span>
                                    </label>

                                </td>
                                <td class="col-md-4 domainverify">

                                    <b>{{ __('message.not_available') }}</b>


                                </td>
                                <td class="col-md-2"><button type="submit" class="form-group btn btn-primary"  id="submit14"><i class="fa fa-save">&nbsp;&nbsp;</i>{!!Lang::get('message.save')!!}</button></td>
                            </tr>


                            <!--<tr>

                  <td class="col-md-2">Auto Update</td>
                  <td class="col-md-2">
                    <label class="switch toggle_event_editing">

                         <input type="checkbox" value="{{$updateStatus}}"  name="modules_settings"
                          class="checkbox3" id="update">
                          <span class="slider round"></span>
                    </label>

                  </td>

                  <td class="col-md-4 updateEmptyField">
                  {!! Form::label('update_api_secret',Lang::get('message.lic_api_secret')) !!}
                            {!! Form::text('update_api',null,['class' => 'form-control updatesecretHide','disabled'=>'disabled']) !!}-->



                            <!-- last name -->
                            <!-- {!! Form::label('update_api_url',Lang::get('message.lic_api_url')) !!} :
                        {!! Form::text('update_api',null,['class' => 'form-control updateurlHide','disabled'=>'disabled']) !!}

                            </td>
                            <td class="col-md-4 updateField hide">-->


                            <!-- last name -->
                            <!--{!! Form::label('update_api_secret',Lang::get('message.lic_api_secret')) !!}
                            {!! Form::text('update_api_secret',$updateSecret,['class' => 'form-control','id'=>'update_api_secret']) !!}
                            <h6 id="update_apiCheck"></h6>
                            <br/>-->

                            <!-- last name -->
                            <!--{!! Form::label('update_api_url',Lang::get('message.lic_api_url')) !!} :
                        {!! Form::text('update_api_url',$updateUrl,['class' => 'form-control','id'=>'update_api_url']) !!}
                            <h6 id="update_urlCheck"></h6>

                   </td>
                      <td class="col-md-2" ><button type="submit" class="form-group btn btn-primary" onclick="updateDetails()" id="submitudpate"><i class="fa fa-save">&nbsp;&nbsp;</i>{!!Lang::get('message.save')!!}</button></td>
                </tr>-->

                

                            <tr>
                                <td class="col-md-2">{{ __('message.google_recaptcha') }}</td>
                                <td class="col-md-2">
                                    <label class="switch toggle_event_editing">

                                        <input type="checkbox" value="{{ $captchaStatus || $v3CaptchaStatus}}"  name="modules_settings"
                                               class="checkbox2" id="captcha">
                                        <span class="slider round"></span>
                                    </label>

                                </td>

                                <td class="col-md-4 captchaEmptyField">
                                    {!! Form::label('nocaptcha_secret',Lang::get('message.nocaptcha_secret')) !!}
                                    {!! Form::text('nocaptcha_secret1',null,['class' => 'form-control nocapsecretHide','disabled'=>'disabled']) !!}
                                    <h6 id=""></h6>


                                    <!-- last name -->
                                    {!! Form::label('nocaptcha_sitekey',Lang::get('message.nocaptcha_sitekey')) !!} :
                                    {!! Form::text('nocaptcha_sitekey1',null,['class' => 'form-control siteKeyHide','disabled'=>'disabled']) !!}
                                    <h6 id=""></h6>
                                </td>
                                <td class="col-md-4 captchaField hide">
                                    <div class="form-group m-1 d-flex">
                                        <div class="custom-control custom-radio m-2">
                                            <input class="custom-control-input" type="radio" id="captchaRadioV2" name="customRadio" {{ $captchaStatus === 1 ? 'checked' : '' }}>
                                            <label for="captchaRadioV2" class="custom-control-label">{{ __('message.recaptcha_v2') }}</label>
                                        </div>
                                        <div class="custom-control custom-radio m-2">
                                            <input class="custom-control-input" type="radio" id="captchaRadioV3" name="customRadio" {{ $v3CaptchaStatus === 1 ? 'checked' : '' }}>
                                            <label for="captchaRadioV3" class="custom-control-label">{{ __('message.recaptcha_v3') }}</label>
                                        </div>
                                    </div>

                                    <!-- last name -->
                                    {!! Form::label('nocaptcha_sitekey',Lang::get('message.nocaptcha_sitekey')) !!}
                                    {!! Form::text('nocaptcha_sitekey',$siteKey,['class' => 'form-control','id'=>'nocaptcha_sitekey']) !!}
                                    <h6 id="captcha_sitekeyCheck"></h6>

                                    <!-- last name -->
                                    {!! Form::label('nocaptcha_secret',Lang::get('message.nocaptcha_secret')) !!}
                                    {!! Form::text('nocaptcha_secret',$secretKey,['class' => 'form-control','id'=>'nocaptcha_secret']) !!}
                                    <h6 id="captcha_secretCheck"></h6>
                                    <br/>

                                </td>
                                <td class="col-md-2"><button type="submit" class="form-group btn btn-primary" onclick="captchaDetails()" id="submit2"><i class="fa fa-save">&nbsp;&nbsp;</i>{!!Lang::get('message.save')!!}</button></td>
                            </tr>
                         <tr>

                                <td class="col-md-2">{{ __('message.msg_mobile_verification') }}</td>
                                <td class="col-md-2">
                                    <label class="switch toggle_event_editing">

                                        <input type="checkbox" value="{{$mobileStatus}}"  name="mobile_settings"
                                               class="checkbox4" id="mobile">
                                        <span class="slider round"></span>
                                    </label>

                                </td>
                                <td class="col-md-4 mobileverify">

                                    <input type ="hidden" id="hiddenMobValue" value="{{$mobileauthkey}}">
                                    <!-- last name -->
                                    {!! Form::label('mobile',Lang::get('message.msg91_key')) !!}
                                    {!! Form::text('msg91_auth_key',$mobileauthkey,['class' => 'form-control mobile_authkey','id'=>'mobile_authkey']) !!}
                                    <h6 id="mobile_check"></h6>
                                    <br/>
                                    <input type ="hidden" id="hiddenSender" value="{{$msg91Sender}}">
                                    {!! Form::label('mobile',Lang::get('message.msg91_sender')) !!}
                                    {!! Form::text('msg91_sender',$msg91Sender,['class' => 'form-control sender','id'=>'sender']) !!}
                                    <h6 id="sender_check"></h6>

                                    <input type ="hidden" id="hiddenTemplate" value="{{$msg91TemplateId}}">
                                    {!! Form::label('mobile',Lang::get('message.msg91_template_id')) !!}
                                    {!! Form::text('msg91_template_id',$msg91TemplateId,['class' => 'form-control template_id','id'=>'template_id']) !!}
                                    <h6 id="template_check"></h6>
                                </td>
                                <td class="col-md-2"><button type="submit" class="form-group btn btn-primary"  id="submit3"><i class="fa fa-save">&nbsp;&nbsp;</i>{!!Lang::get('message.save')!!}</button></td>
                            </tr>


                            <tr>

                                <td class="col-md-2">{{ __('message.mailchimp') }}</td>
                                <td class="col-md-2">
                                    <label class="switch toggle_event_editing">

                                        <input type="checkbox" value="{{$mailchimpSetting}}"  name="mobile_settings"
                                               class="checkbox9" id="mailchimp">
                                        <span class="slider round"></span>
                                    </label>

                                </td>
                                <td class="col-md-4 mailchimpverify">

                                    <input type ="hidden" id="hiddenMailChimpValue" value="{{$mailchimpKey}}">
                                    <!-- last name -->
                                    {!! Form::label('mailchimp',Lang::get('message.mailchimp_key')) !!}
                                    {!! Form::text('mailchimp',$mailchimpKey,['class' => 'form-control mailchimp_authkey','id'=>'mailchimp_authkey']) !!}
                                    <h6 id="mailchimp_check"></h6>
                                    <br/>


                                </td>
                                <td class="col-md-2"><button type="submit" class="form-group btn btn-primary"  id="submit9"><i class="fa fa-save">&nbsp;&nbsp;</i>{!!Lang::get('message.save')!!}</button></td>
                            </tr>

                            <tr>

                                <td class="col-md-2">{{ __('message.show_terms_registration') }}</td>
                                <td class="col-md-2">
                                    <label class="switch toggle_event_editing">

                                        <input type="checkbox" value="{{$termsStatus}}"  name="terms_settings"
                                               class="checkbox10" id="terms">
                                        <span class="slider round"></span>
                                    </label>

                                </td>
                                <td class="col-md-4 termsverify">

                                    <input type ="hidden" id="hiddenTermsValue" value="{{$termsUrl}}">
                                    <!-- last name -->
                                    {!! Form::label('terms',Lang::get('message.terms_url')) !!}
                                    {!! Form::text('terms',$termsUrl,['class' => 'form-control terms_url','id'=>'terms_url']) !!}
                                    <h6 id="terms_check"></h6>
                                    <br/>


                                </td>
                                <td class="col-md-2"><button type="submit" class="form-group btn btn-primary"  id="submit10"><i class="fa fa-save">&nbsp;</i>{!!Lang::get('message.save')!!}</button></td>
                            </tr>


                            @if($mailSendingStatus)

                                <tr>

                                    <td class="col-md-2">{{ __('message.email_verification_api') }}</td>
                                    <td class="col-md-2">
                                        <label class="switch toggle_event_editing">

                                            <input type="checkbox" value="{{$emailStatus}}"  name="email_settings"
                                                   class="checkbox5" id="email">
                                            <span class="slider round"></span>
                                        </label>

                                    </td>
                                    <td class="col-md-4 mobileverify">

                                        <b>{{ __('message.not_available') }}</b>


                                    </td>
                                    <td class="col-md-2" ><button type="submit" class="form-group btn btn-primary"  id="submit4"><i class="fa fa-save">&nbsp;&nbsp;</i>{!!Lang::get('message.save')!!}</button></td>
                                </tr>
                            @endif


                            <tr>

                                <td class="col-md-2">{{ __('message.twitter') }}</td>
                                <td class="col-md-2">
                                    <label class="switch toggle_event_editing">

                                        <input type="checkbox" value="{{$twitterStatus}}"  name="twitter_settings"
                                               class="checkbox6" id="twitter">
                                        <span class="slider round"></span>
                                    </label>

                                </td>

                                <td class="col-md-2 twitterverify">
                                    <input type ="hidden" id="hidden_consumer_key" value="{{$twitterKeys->twitter_consumer_key}}">
                                    <input type ="hidden" id="hidden_consumer_secret" value="{{$twitterKeys->twitter_consumer_secret}}">
                                    <input type ="hidden" id="hidden_access_token" value="{{$twitterKeys->twitter_access_token}}">
                                    <input type ="hidden" id="hidden_token_secret" value="{{$twitterKeys->access_tooken_secret}}">
                                    {!! Form::label('consumer_key',Lang::get('message.consumer_key')) !!}
                                    {!! Form::text('consumer_key',$twitterKeys->twitter_consumer_key,['class' => 'form-control consumer_key','id'=>'consumer_key']) !!}
                                    <h6 id="consumer_keycheck"></h6>


                                    <!-- last name -->
                                    {!! Form::label('consumer_secret',Lang::get('message.consumer_secret')) !!}
                                    {!! Form::text('consumer_secret',$twitterKeys->twitter_consumer_secret,['class' => 'form-control consumer_secret','id'=>'consumer_secret']) !!}
                                    <h6 id="consumer_secretcheck"></h6>



                                    {!! Form::label('access_token',Lang::get('message.access_token')) !!}
                                    {!! Form::text('access_token',$twitterKeys->twitter_access_token,['class' => 'form-control access_token','id'=>'access_token']) !!}
                                    <h6 id="access_tokencheck"></h6>



                                    {!! Form::label('token_secret',Lang::get('message.token_secret')) !!}
                                    {!! Form::text('token_secret',$twitterKeys->access_tooken_secret,['class' => 'form-control token_secret','id'=>'token_secret']) !!}
                                    <h6 id="token_secretcheck"></h6>


                                </td>

                                <td class="col-md-2" ><button type="submit" class="form-group btn btn-primary"  id="submit5"><i class="fa fa-save">&nbsp;&nbsp;</i>{!!Lang::get('message.save')!!}</button></td>
                            </tr>

                            <tr>

                                <td class="col-md-2">{{ __('message.zoho_crm') }}</td>
                                <td class="col-md-2">
                                    <label class="switch toggle_event_editing">

                                        <input type="checkbox" value="{{$zohoStatus}}"  name="zoho_settings"
                                               class="checkbox8" id="zoho">
                                        <span class="slider round"></span>
                                    </label>

                                </td>
                                <td class="col-md-4 zohoverify">

                                    <input type ="hidden" id="hidden_zoho_key" value="{{$zohoKey}}">
                                    <!-- last name -->
                                    {!! Form::label('zoho_key',Lang::get('message.zoho_crm')) !!}
                                    {!! Form::text('zoho_key',$zohoKey,['class' => 'form-control zoho_key','id'=>'zoho_key']) !!}
                                    <h6 id="zoho_keycheck"></h6>
                                    <br/>


                                </td>
                                <td class="col-md-2"><button type="submit" class="form-group btn btn-primary"  id="submit7"><i class="fa fa-save">&nbsp;&nbsp;</i>{!!Lang::get('message.save')!!}</button></td>
                            </tr>

                            <tr>

                                <td class="col-md-2">{{ __('message.pipedrive') }}</td>
                                <td class="col-md-2">
                                    <label class="switch toggle_event_editing">

                                        <input type="checkbox" value="{{$pipedriveStatus}}"  name="pipedrive_settings"
                                               class="checkbox13" id="pipedrive">
                                        <span class="slider round"></span>
                                    </label>

                                </td>
                                <td class="col-md-4 pipedriveverify">

                                    <input type ="hidden" id="hidden_pipedrive_key" value="{{$pipedriveKey}}">
                                    <!-- last name -->
                                    {!! Form::label('pipedrive_key',Lang::get('message.pipedrive_key')) !!}
                                    {!! Form::text('pipedrive_key',$pipedriveKey,['class' => 'form-control pipedrive_key','id'=>'pipedrive_key']) !!}
                                    <h6 id="pipedrive_keycheck"></h6>
                                    <br/>


                                </td>
                                <td class="col-md-2"><button type="submit" class="form-group btn btn-primary"  id="submit13"><i class="fa fa-save">&nbsp;&nbsp;</i>{!!Lang::get('message.save')!!}</button></td>
                            </tr>

                            </tbody>


                        </table>
                    </div>
                </div>
            </div>
        </div>
        <!-- /.box-body -->
    </div>


    {!! Form::close() !!}
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
        //License Manager
        $(document).ready(function(){
            var status = $('.checkbox').val();
            if(status ==1) {
                $('#License').prop('checked', true);
                $('.LicenseField').show();
                $('.licenseEmptyField').hide();
            } else if(status ==0) {
                $('.LicenseField').addClass("hide");
                $('.licenseEmptyField').show();

            }
        });
        $('#license_apiCheck').hide();
        $('#License').change(function () {
            if ($(this).prop("checked")) {
                // checked
                $('#license_api_secret').val();
                $('#license_api_url').val();
                $('#license_client_id').val();
                $('#license_client_secret').val();
                $('#license_grant_type').val();
                $('.LicenseField').show();
                $('.licenseEmptyField').hide();
            }
            else{
                $('.LicenseField').hide();
                $('.nocapsecretHide').val('');
                $('.siteKeyHide').val('');
                $('.licenseEmptyField').show();

               $('.licenseEmptyField').show();
               
               
        }
    });



        function licenseDetails(){

            if ($('#License').prop("checked")) {
                var checkboxvalue = 1;

                if ($('#license_api_secret').val() =="" ) {
                    $('#license_apiCheck').show();
                    $('#license_apiCheck').html("{{ __('message.enter_api_secret_key') }}");
                    $('#license_api_secret').css("border-color","red");
                    $('#license_apiCheck').css({"color":"red","margin-top":"5px"});
                    setTimeout(function(){
                    $('#license_apiCheck').hide();
                      $('#license_api_secret').css("border-color","");
                        }, 1500);
                    return false;
                }
         
                if ($('#license_api_url').val() =="" ) {
                    $('#license_urlCheck').show();
                    $('#license_urlCheck').html("{{ __('message.enter_api_url') }}");
                    $('#license_api_url').css("border-color","red");
                    $('#license_urlCheck').css({"color":"red","margin-top":"5px"});
                    setTimeout(function(){
                    $('#license_urlCheck').hide();
                      $('#license_api_url').css("border-color","");
                        }, 1500);
                    return false;
                }

                if ($('#license_client_id').val() =="" ) {
                    $('#license_clientIdCheck').show();
                    $('#license_clientIdCheck').html("{{ __('message.enter_client_id') }}");
                    $('#license_client_id').css("border-color","red");
                    $('#license_clientIdCheck').css({"color":"red","margin-top":"5px"});
                    setTimeout(function(){
                    $('#license_clientIdCheck').hide();
                      $('#license_client_id').css("border-color","");
                        }, 1500);
                    return false;
                }
                if ($('#license_client_secret').val() =="" ) {
                    $('#license_clientSecretCheck').show();
                    $('#license_clientSecretCheck').html("{{ __('message.enter_client_secret') }}");
                    $('#license_client_secret').css("border-color","red");
                    $('#license_clientSecretCheck').css({"color":"red","margin-top":"5px"});
                    setTimeout(function(){
                    $('#license_clientSecretCheck').hide();
                      $('#license_client_secret').css("border-color","");
                        }, 1500);
                    return false;
                }
                if ($('#license_grant_type').val() =="" ) {
                    $('#license_grantTypeCheck').show();
                    $('#license_grantTypeCheck').html("{{ __('message.enter_grant_type') }}");
                    $('#license_grant_type').css("border-color","red");
                    $('#license_grantTypeCheck').css({"color":"red","margin-top":"5px"});
                    setTimeout(function(){
                    $('#license_grantTypeCheck').hide();
                      $('#license_grant_type').css("border-color","");
                        }, 1500);
                    return false;
                }
            }
            else{
                var checkboxvalue = 0;
            }
            $("#submit").html("<i class='fas fa-circle-notch fa-spin'></i>  {{ __('message.please_wait') }}");
            $.ajax({

                url : '{{url("licenseDetails")}}',
                type : 'post',
                data: {
                    "status": checkboxvalue,
                    "license_api_secret": $('#license_api_secret').val(),
                    "license_api_url" :$('#license_api_url').val(),
                    "license_client_id": $('#license_client_id').val(),
                    "license_client_secret" :$('#license_client_secret').val(),
                    "license_grant_type": $('#license_grant_type').val(),

                },
                success: function (response) {
                    $('#alertMessage').show();
                    var result =  '<div class="alert alert-success alert-dismissable"><button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button><strong><i class="fa fa-check"></i> {{ __('message.success') }}! </strong>'+response.update+'.</div>';
                    $('#alertMessage').html(result+ ".");
                    $("#submit").html("<i class='fa fa-save'>&nbsp;&nbsp;</i>Save");
                    setInterval(function(){
                        $('#alertMessage').slideUp(3000);
                    }, 1000);
                },


            });
        };



        //Auto Update
        $(document).ready(function(){

            var status = $('.checkbox3').val();
            if(status ==1) {
                $('#update').prop('checked', true);
                $('.updateField').show();
                $('.updateEmptyField').hide();
            } else if(status ==0) {
                $('.updateField').hide();
                $('.updateEmptyField').show();

            }
        });
        $('#update_apiCheck').hide();
        $('#update').change(function () {
            if ($(this).prop("checked")) {
                // checked
                $('#update_api_secret').val();
                $('#update_api_url').val();
                $('.updateField').show();
                $('.updateEmptyField').hide();
            }
            else{
                $('.updateField').addClass("hide");
                $('.updatesecretHide').val('');
                $('.updateurlHide').val('');
                $('.updateEmptyField').removeClass("hide");


            }
        });

        function updateDetails(){
            if ($('#update').prop("checked")) {
                var checkboxvalue = 1;
                if ($('#update_api_secret').val() == '' ) {
                    $('#update_apiCheck').show();
                    $('#update_apiCheck').html("{{ __('message.enter_api_secret_key') }}");
                    $('#update_api_secret').css("border-color","red");
                    $('#update_apiCheck').css({"color":"red","margin-top":"5px"});
                    return false;
                }
                if ($('#update_api_url').val() == '' ) {
                    alert('df');
                    $('#update_urlCheck').show();
                    $('#update_urlCheck').html("{{ __('message.enter_api_url') }}");
                    $('#update_api_url').css("border-color","red");
                    $('#update_urlCheck').css({"color":"red","margin-top":"5px"});
                    return false;
                }

            }
            else{
                var checkboxvalue = 0;
            }
            $("#submitudpate").html("<i class='fas fa-circle-notch fa-spin'></i>  {{ __('message.please_wait') }}");
            $.ajax({

                url : '{{url("updateDetails")}}',
                type : 'post',
                data: {
                    "status": checkboxvalue,
                    "update_api_secret": $('#update_api_secret').val(),
                    "update_api_url" :$('#update_api_url').val(),
                },
                success: function (response) {
                    $('#alertMessage').show();
                    var result =  '<div class="alert alert-success alert-dismissable"><button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button><strong><i class="fa fa-check"></i> {{ __('message.success') }}! </strong>'+response.update+'.</div>';
                    $('#alertMessage').html(result+ ".");
                    $("#submitudpate").html("<i class='fa fa-floppy-o'>&nbsp;&nbsp;</i>{{ __('message.save') }}");
                    setInterval(function(){
                        $('#alertMessage').slideUp(3000);
                    }, 1000);
                },


            });
        };





        /**
         * Google ReCAPTCHA
         *
         */
        $(document).ready(function(){
         
            var status = $('.checkbox2').val();
            if(status ==1) {
                $('#captcha').prop('checked', true);
                $('.captchaField').show();
                $('.captchaEmptyField').hide();
            } else if(status ==0) {
                $('.captchaField').hide();
                $('.captchaEmptyField').show();

            }
        });
        $('#captcha_secretCheck').hide();
        $('#captcha').change(function () {
            if ($(this).prop("checked")) {
                // checked
                $('#nocaptcha_secret').val();
                $('#nocaptcha_sitekey').val();
                $('.captchaField').show();
                $('.captchaEmptyField').hide();
            }
            else{
                $('.captchaField').hide();
                $('.secretHide').val('');
                $('.urlHide').val('');
                $('.captchaEmptyField').show();


            }
        });

        function captchaDetails(){
      
            if ($('#captcha').prop("checked")) {
                var checkboxvalue = 1;
                if ($('#nocaptcha_secret').val() =="" ) {
                    $('#captcha_secretCheck').show();
                    $('#captcha_secretCheck').html("{{ __('message.enter_secret_key') }}");
                    $('#captcha_secret').css("border-color","red");
                    $('#captcha_secretCheck').css({"color":"red","margin-top":"5px"});
                    return false;
                }
                if ($('#nocaptcha_sitekey').val() =="" ) {
                    $('#captcha_sitekeyCheck').show();
                    $('#captcha_sitekeyCheck').html("{{ __('message.enter_site_key') }}");
                    $('#nocaptcha_sitekey').css("border-color","red");
                    $('#captcha_sitekeyCheck').css({"color":"red","margin-top":"5px"});
                    return false;
                }

            }
            else{
                var checkboxvalue = 0;
            }
            $("#submit2").html("<i class='fas fa-circle-notch fa-spin'></i>  {{ __('message.please_wait') }}");
            $.ajax({

                url : '{{url("captchaDetails")}}',
                type : 'post',
                data: {
                    "status": checkboxvalue,
                    "recaptcha_type": $('#captchaRadioV2').prop('checked') ? 'v2' : 'v3',
                    "nocaptcha_sitekey": $('#nocaptcha_sitekey').val(),
                    "nocaptcha_secret" :$('#nocaptcha_secret').val(),
                },
                success: function (data) {
                    $('#alertMessage').show();
                    var result =  '<div class="alert alert-success alert-dismissable"><button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button><strong><i class="fa fa-check"></i> {{ __('message.success') }}! </strong>'+data.update+'.</div>';
                    $('#alertMessage').html(result+ ".");
                    $("#submit2").html("<i class='fa fa-save'>&nbsp;&nbsp;</i>{{ __('message.save') }}");
                    setInterval(function(){
                        $('#alertMessage').slideUp(3000);
                    }, 1000);
                },

            });
        };






 $(document).ready(function(){
            var status = $('.checkbox3').val();
            if(status ==1) {
                $('#v3captcha').prop('checked', true);
                $('.v3captchaField').show();
                $('.v3captchaEmptyField').hide();
            } else if(status ==0) {
                $('.v3captchaField').hide();
                $('.v3captchaEmptyField').show();

            }
        });
        $('#v3captcha_secretCheck').hide();
        $('#v3captcha').change(function () {
            if ($(this).prop("checked")) {
                // checked
                $('#captcha_secret').val();
                $('#captcha_sitekey').val();
                $('.v3captchaField').show();
                $('.v3captchaEmptyField').hide();
            }
            else{
                $('.v3captchaField').hide();
                $('.v3nocapsecretHide').val('');
                $('.v3urlHide').val('');
                $('.v3captchaEmptyField').show();


            }
        });

        function v3captchaDetails(){
            if ($('#v3captcha').prop("checked")) {
                var checkboxvalue = 1;
                if ($('#captcha_secret').val() =="" ) {
                    $('#v3captcha_secretCheck').show();
                    $('#v3captcha_secretCheck').html("{{ __('message.enter_secret_key') }}");
                    $('#captcha_secret').css("border-color","red");
                    $('#v3captcha_secretCheck').css({"color":"red","margin-top":"5px"});
                    return false;
                }
                if ($('#captcha_sitekey').val() =="" ) {
                    $('#captcha_sitekeyCheck').show();
                    $('#captcha_sitekeyCheck').html("{{ __('message.enter_site_key') }}");
                    $('#captcha_sitekey').css("border-color","red");
                    $('#captcha_sitekeyCheck').css({"color":"red","margin-top":"5px"});
                    return false;
                }

            }
            else{
                var checkboxvalue = 0;
            }
            // $("#submitv3").html("<i class='fas fa-circle-notch fa-spin'></i>  Please Wait...");
            $.ajax({

                url : '{{url("v3captchaDetails")}}',
                type : 'post',
                data: {
                    "status": checkboxvalue,
                    "recaptcha_type": $('#captchaRadioV2').prop('checked') ? 'v2' : 'v3',
                    "captcha_sitekey": $('#captcha_sitekey').val(),
                    "captcha_secret" :$('#captcha_secret').val(),
                },
                success: function (data) {
                    $('#alertMessage').show();
                    var result =  '<div class="alert alert-success alert-dismissable"><button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button><strong><i class="fa fa-check"></i> {{ __('message.success') }}! </strong>'+data.update+'.</div>';
                    $('#alertMessage').html(result+ ".");
                    $("#submit3").html("<i class='fa fa-save'>&nbsp;&nbsp;</i>{{ __('message.save') }}");
                    setInterval(function(){
                        $('#alertMessage').slideUp(3000);
                    }, 1000);
                },

            });
        }

        /*
       *MSG 91
        */
        $(document).ready(function (){
            var mobilestatus =  $('.checkbox4').val();
            if(mobilestatus ==1)
            {
                $('#mobile').prop('checked',true);
                $('.mobile_authkey').attr('enabled', true);
                $('.sender').attr('enabled', true);
                $('.template_id').attr('enabled',true)
            } else if(mobilestatus ==0){
                $('#mobile').prop('checked',false);
                $('.mobile_authkey').attr('disabled', true);
                $('.sender').attr('disabled', true);
                $('.template_id').attr('disabled', true)
            }
        });
        $("#mobile").on('change',function (){
            if($(this).prop('checked')) {
                var mobilekey =  $('#hiddenMobValue').val();
                var sender =  $('#hiddenSender').val();
                var template =  $('#hiddenTemplate').val();
                $('.mobile_authkey').attr('disabled', false);
                $('.sender').attr('disabled', false);
                $('.template_id').attr('disabled',false)
                $('#mobile_authkey').val(mobilekey);
                $('#sender').val(sender);
                $('#template_id').val(template);


            } else {
                $('.mobile_authkey').attr('disabled', true);
                $('.sender').attr('disabled', true);
                $('.template_id').attr('disabled',true)
                $('.mobile_authkey').val('');
                $('.sender').val('');
                $('.template_id').val('');

            }
        });
        //Validate and pass value through ajax
        $("#submit3").on('click', function () {
            if ($('#mobile').prop('checked')) { // If checkbox is checked
                var mobilestatus = 1;

                // Validate Auth Key
                if ($('#mobile_authkey').val() === "") {
                    $('#mobile_check').show().text("{{ __('message.enter_auth_key') }}").css({ "color": "red", "margin-top": "5px" });
                    $('#mobile_authkey').addClass('error-border');
                    return false;
                } else {
                    $('#mobile_check').hide();
                    $('#mobile_authkey').removeClass('error-border');
                }

                // Validate Sender
                if ($('#sender').val() !== "") {
                    const senderRegex = /^[a-zA-Z]{0,6}$/;
                    if (senderRegex.test($('#sender').val())) {
                        $('#sender_check').hide();
                        $('#sender').removeClass('error-border');
                    } else {
                        $('#sender_check').show().text("{{ __('message.check_characters') }}").css({ "color": "red", "margin-top": "5px" });
                        $('#sender').addClass('error-border');
                        return false;
                    }
                }

                // Validate Template ID
                if ($('#template_id').val() === "") {
                    $('#template_id').addClass('error-border');
                    $('#template_check').show().text("{{ __('message.enter_template_id') }}").css({ "color": "red", "margin-top": "5px" });
                    return false;
                } else {
                    $('#template_id').removeClass('error-border');
                    $('#template_check').hide();
                }
            } else {
                // Reset fields when mobile is unchecked
                $('#mobile_authkey, #sender, #template_id').removeClass('error-border');
                $('#mobile_check, #sender_check, #template_check').hide().text("");
                mobilestatus = 0;
            }

            // Show loading state
            $("#submit3").html("<i class='fas fa-circle-notch fa-spin'></i> {{ __('message.please_wait') }}");

            // AJAX request
            $.ajax({
                url: '{{url("updatemobileDetails")}}',
                type: 'POST',
                data: {
                    "status": mobilestatus,
                    "msg91_auth_key": $('#mobile_authkey').val(),
                    "msg91_sender": $('#sender').val(),
                    "msg91_template_id": $('#template_id').val(),
                },
                success: function (data) {
                    const result = `
                <div class="alert alert-success alert-dismissable">
                    <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
                    <strong><i class="fa fa-check"></i> {{ __('message.success') }}! </strong>${data.update}.
                </div>`;
                    $('#alertMessage').show().html(result);
                    $("#submit3").html("<i class='fa fa-save'>&nbsp;&nbsp;</i>{{ __('message.save') }}");

                    console.log("in");

                    setInterval(function(){
                        $('#alertMessage').slideUp(3000);
                    }, 1000);
                },
                error: function () {
                    $('#alertMessage').html("<div class='alert alert-danger'>{{ __('message.error_occurred') }}</div>").show();
                    $("#submit3").html("<i class='fa fa-save'>&nbsp;&nbsp;</i>{{ __('message.save') }}");
                }
            });
        });




        <!------------------------------------------------------------------------------------------------------------------->
        /*
         * Email Status Setting
         */
        $(document).ready(function (){
            var emailstatus =  $('.checkbox5').val();
            if(emailstatus ==1)
            {
                $('#email').prop('checked',true);
            } else if(emailstatus ==0){
                $('#email').prop('checked',false);
            }
        });
        //Validate and pass value through ajax
        $("#submit4").on('click',function (){ //When Submit button is checked
            if ($('#email').prop('checked')) {//if button is on
                var emailstatus = 1;
            } else {
                var emailstatus = 0;
            }
            $("#submit4").html("<i class='fas fa-circle-notch fa-spin'></i>  {{ __('message.please_wait') }}");
            $.ajax ({
                url: '{{url("updateemailDetails")}}',
                type : 'post',
                data: {
                    "status": emailstatus,
                },
                success: function (data) {
                    $('#alertMessage').show();
                    var result =  '<div class="alert alert-success alert-dismissable"><button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button><strong><i class="fa fa-check"></i> {{ __('message.success') }}! </strong>'+data.update+'.</div>';
                    $('#alertMessage').html(result+ ".");
                    $("#submit4").html("<i class='fa fa-save'>&nbsp;&nbsp;</i>Save");
                    setInterval(function(){
                        $('#alertMessage').slideUp(3000);
                    }, 1000);
                },
            });
        });

        <!------------------------------------------------------------------------------------------------------------------->
        /*
         * Twitter Settings
         */
        $(document).ready(function (){
            var twitterstatus =  $('.checkbox6').val();
            if(twitterstatus ==1)
            {
                $('#twitter').prop('checked',true);
                $('#consumer_key').attr('enabled', true);
                $('#consumer_secret').attr('enabled', true);
                $('#access_token').attr('enabled', true);
                $('#token_secret').attr('enabled', true);

            } else if(twitterstatus ==0){
                $('#twitter').prop('checked',false);
                $('.consumer_key').attr('disabled', true);
                $('.consumer_secret').attr('disabled', true);
                $('.access_token').attr('disabled', true);
                $('.token_secret').attr('disabled', true);
            }
        });

        $("#twitter").on('change',function (){
            if($(this).prop('checked')) {
                var consumer_key =  $('#hidden_consumer_key').val();
                var consumer_secret =  $('#hidden_consumer_secret').val();
                var access_token =  $('#hidden_access_token').val();
                var token_secret =  $('#hidden_token_secret').val();
                $('.consumer_key').attr('disabled', false);
                $('.consumer_secret').attr('disabled', false);
                $('.access_token').attr('disabled', false);
                $('.token_secret').attr('disabled', false);
                $('#consumer_key').val(consumer_key);
                $('#consumer_secret').val(consumer_secret);
                $('#access_token').val(access_token);
                $('#token_secret').val(token_secret);

            } else {
                $('.consumer_key').attr('disabled', true);
                $('.consumer_secret').attr('disabled', true);
                $('.access_token').attr('disabled', true);
                $('.token_secret').attr('disabled', true);
                $('#consumer_key').val('');
                $('#consumer_secret').val('');
                $('#access_token').val('');
                $('#token_secret').val('');


            }
        });

        //Validate and pass value through ajax
        $("#submit5").on('click',function (){ //When Submit button is clicked
            if ($('#twitter').prop('checked')) {//if button is on
                var twitterstatus = 1;
                if ($('#consumer_key').val() == "") { //if value is not entered
                    $('#consumer_keycheck').show();
                    $('#consumer_keycheck').html("{{ __('message.enter_twitter_key') }}");
                    $('#consumer_key').css("border-color","red");
                    $('#consumer_keycheck').css({"color":"red","margin-top":"5px"});
                    return false;
                } else if ($('#consumer_secret').val() == "") {
                    $('#consumer_secretcheck').show();
                    $('#consumer_secretcheck').html("{{ __('message.enter_twitter_secret') }}");
                    $('#consumer_secret').css("border-color","red");
                    $('#consumer_secretcheck').css({"color":"red","margin-top":"5px"});
                    return false;
                } else if ($('#access_token').val() == "") {
                    $('#access_tokencheck').show();
                    $('#access_tokencheck').html("{{ __('message.enter_twitter_access_token') }}");
                    $('#access_token').css("border-color","red");
                    $('#access_tokencheck').css({"color":"red","margin-top":"5px"});
                    return false;
                } else if ($('#token_secret').val() == "") {
                    $('#token_secretcheck').show();
                    $('#token_secretcheck').html("{{ __('message.enter_twitter_token_secret') }}");
                    $('#token_secret').css("border-color","red");
                    $('#token_secretcheck').css({"color":"red","margin-top":"5px"});
                    return false;
                }
            } else {
                $('#consumer_keycheck').html("");
                $('#consumer_key').css("border-color","");
                $('#consumer_secretcheck').html("");
                $('#consumer_secret').css("border-color","");
                $('#access_tokencheck').html("");
                $('#access_token').css("border-color","");
                $('#token_secretcheck').html("");
                $('#token_secret').css("border-color","");
                var twitterstatus = 0;
            }
            $("#submit5").html("<i class='fas fa-circle-notch fa-spin'></i>  {{ __('message.please_wait') }}");
            $.ajax ({
                url: '{{url("updatetwitterDetails")}}',
                type : 'post',
                data: {
                    "status": twitterstatus,
                    "consumer_key": $('#consumer_key').val(),"consumer_secret" : $('#consumer_secret').val() ,
                    "access_token":$('#access_token').val() ,  "token_secret" : $('#token_secret').val()
                },
                success: function (data) {
                    $('#alertMessage').show();
                    var result =  '<div class="alert alert-success alert-dismissable"><button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button><strong><i class="fa fa-check"></i> {{ __('message.success') }}! </strong>'+data.update+'.</div>';
                    $('#alertMessage').html(result+ ".");
                    $("#submit5").html("<i class='fa fa-save'>&nbsp;</i>{{ __('message.save') }}");
                    setInterval(function(){
                        $('#alertMessage').slideUp(3000);
                    }, 1000);
                },
            })
        });




        <!---------------------------------------------------------------------------------------------------------------->
        /*
       *Zoho
        */
        $(document).ready(function (){
            var zohostatus =  $('.checkbox8').val();
            if(zohostatus ==1)
            {
                $('#zoho').prop('checked',true);
                $('.zoho_key').attr('enabled', true);
            } else if(zohostatus ==0){
                $('#zoho').prop('checked',false);
                $('.zoho_key').attr('disabled', true);
            }
        });
        $("#zoho").on('change',function (){
            if($(this).prop('checked')) {
                var zohokey =  $('#hidden_zoho_key').val();
                $('.zoho_key').attr('disabled', false);
                $('#zoho_key').val(zohokey);

            } else {
                $('.zoho_key').attr('disabled', true);
                $('.zoho_key').val('');


            }
        });
        //Validate and pass value through ajax
        $("#submit7").on('click',function (){ //When Submit button is checked
            if ($('#zoho').prop('checked')) {//if button is on
                var zohostatus = 1;
                if ($('#zoho_key').val() == "") { //if value is not entered
                    $('#zoho_keycheck').show();
                    $('#zoho_keycheck').html("{{ __('message.enter_zoho_key') }}");
                    $('#zoho_key').css("border-color","red");
                    $('#zoho_keycheck').css({"color":"red","margin-top":"5px"});
                    return false;
                }
            } else {
                $('#zoho_keycheck').html("");
                $('#zoho_key').css("border-color","");
                var zohostatus = 0;

            }
            $("#submit7").html("<i class='fas fa-circle-notch fa-spin'></i>  {{ __('message.please_wait') }}");
            $.ajax ({
                url: '{{url("updatezohoDetails")}}',
                type : 'post',
                data: {
                    "status": zohostatus,
                    "zoho_key": $('#zoho_key').val(),
                },
                success: function (data) {
                    $('#alertMessage').show();
                    var result =  '<div class="alert alert-success alert-dismissable"><button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button><strong><i class="fa fa-check"></i> {{ __('message.success') }}! </strong>'+data.update+'.</div>';
                    $('#alertMessage').html(result+ ".");
                    $("#submit7").html("<i class='fa fa-save'>&nbsp;&nbsp;</i>{{ __('message.save') }}");
                    setInterval(function(){
                        $('#alertMessage').slideUp(3000);
                    }, 1000);
                },
            })
        });

        /*
       *Mailchimp
        */
        $(document).ready(function (){
            var mailchimpstatus =  $('.checkbox9').val();
            if(mailchimpstatus ==1)
            {
                $('#mailchimp').prop('checked',true);
                $('.mailchimp_authkey').attr('enabled', true);
            } else if(mailchimpstatus ==0){
                $('#mailchimp').prop('checked',false);
                $('.mailchimp_authkey').attr('disabled', true);
            }
        });
        $("#mailchimp").on('change',function (){
            if($(this).prop('checked')) {
                var mailchimpkey =  $('#hiddenMailChimpValue').val();
                $('.mailchimp_authkey').attr('disabled', false);
                $('#mailchimp_authkey').val(mailchimpkey);

            } else {
                $('.mailchimp_authkey').attr('disabled', true);
                $('.mailchimp_authkey').val('');


            }
        });
        //Validate and pass value through ajax
        $("#submit9").on('click',function (){ //When Submit button is checked
            if ($('#mailchimp').prop('checked')) {//if button is on
                var chimpstatus = 1;
                if ($('#mailchimp_authkey').val() == "") { //if value is not entered
                    $('#mailchimp_check').show();
                    $('#mailchimp_check').html("{{ __('message.enter_mailchimp_key') }}");
                    $('#mailchimp_authkey').css("border-color","red");
                    $('#mailchimp_check').css({"color":"red","margin-top":"5px"});
                    return false;
                }
            } else {
                $('#mailchimp_check').html("");
                $('#mailchimp_authkey').css("border-color","");
                var chimpstatus = 0;

            }
            $("#submit9").html("<i class='fas fa-circle-notch fa-spin'></i>  {{ __('message.please_wait') }}");
            $.ajax ({
                url: '{{url("updateMailchimpDetails")}}',
                type : 'post',
                data: {
                    "status": chimpstatus,
                    "mailchimp_auth_key": $('#mailchimp_authkey').val(),
                },
                success: function (data) {
                    $('#alertMessage').show();
                    var result =  '<div class="alert alert-success alert-dismissable"><button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button><strong><i class="fa fa-check"></i> {{ __('message.success') }}! </strong>'+data.update+'.</div>';
                    $('#alertMessage').html(result+ ".");
                    $("#submit9").html("<i class='fa fa-save'>&nbsp;</i>{{ __('message.save') }}");
                    setInterval(function(){
                        $('#alertMessage').slideUp(3000);
                    }, 1000);
                },
            })
        });

        /*
       *Terms
        */
        $(document).ready(function (){
            var termsstatus =  $('.checkbox10').val();
            if(termsstatus ==1)
            {
                $('#terms').prop('checked',true);
                $('.terms_url').attr('enabled', true);
            } else if(termsstatus ==0){
                $('#terms').prop('checked',false);
                $('.terms_url').attr('disabled', true);
            }
        });
        $("#terms").on('change',function (){
            if($(this).prop('checked')) {
                var terms =  $('#hiddenTermsValue').val();
                $('.terms_url').attr('disabled', false);
                $('#terms_url').val(terms);

            } else {
                $('.terms_url').attr('disabled', true);
                $('.terms_url').val('');


            }
        });
        //Validate and pass value through ajax
        $("#submit10").on('click',function (){ //When Submit button is checked
            if ($('#terms').prop('checked')) {//if button is on
                var termsstatus = 1;
                if ($('#terms_url').val() == "") { //if value is not entered
                    $('#terms_check').show();
                    $('#terms_check').html("{{ __('message.enter_terms_url') }}");
                    $('#terms_url').css("border-color","red");
                    $('#terms_check').css({"color":"red","margin-top":"5px"});
                    return false;
                }
            } else {
                $('#terms_check').html("");
                $('#terms_url').css("border-color","");
                var termsstatus = 0;

            }
            $("#submit10").html("<i class='fas fa-circle-notch fa-spin'></i>  {{ __('message.please_wait') }}");
            $.ajax ({
                url: '{{url("updateTermsDetails")}}',
                type : 'post',
                data: {
                    "status": termsstatus,
                    "terms_url": $('#terms_url').val(),
                },
                success: function (data) {
                    $('#alertMessage').show();
                    var result =  '<div class="alert alert-success alert-dismissable"><button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button><strong><i class="fa fa-check"></i> {{ __('message.success') }}! </strong>'+data.update+'.</div>';
                    $('#alertMessage').html(result+ ".");
                    $("#submit10").html("<i class='fa fa-save'>&nbsp;&nbsp;</i>Save");
                    setInterval(function(){
                        $('#alertMessage').slideUp(3000);
                    }, 1000);
                },
            })
        });

        <!---------------------------------------------------------------------------------------------------------------->
        /*
       *Piprdrive
        */
        $(document).ready(function (){
            var pipedrivestatus =  $('.checkbox13').val();
            if(pipedrivestatus ==1)
            {
                $('#pipedrive').prop('checked',true);
                $('.pipedrive_key').attr('enabled', true);
            } else if(pipedrivestatus ==0){
                $('#pipedrive').prop('checked',false);
                $('.pipedrive_key').attr('disabled', true);
            }
        });
        $("#pipedrive").on('change',function (){
            if($(this).prop('checked')) {
                var pipedrivekey =  $('#hidden_pipedrive_key').val();
                $('.pipedrive_key').attr('disabled', false);
                $('#pipedrive_key').val(pipedrivekey);

            } else {
                $('.pipedrive_key').attr('disabled', true);
                $('.pipedrive_key').val('');


            }
        });
        //Validate and pass value through ajax
        $("#submit13").on('click',function (){ //When Submit button is checked
            if ($('#pipedrive').prop('checked')) {//if button is on
                var pipedrivestatus = 1;
                if ($('#pipedrive_key').val() == "") { //if value is not entered
                    $('#pipedrive_keycheck').show();
                    $('#pipedrive_keycheck').html("{{ __('message.enter_pipedrive_api') }}");
                    $('#pipedrive_key').css("border-color","red");
                    $('#pipedrive_keycheck').css({"color":"red","margin-top":"5px"});
                    return false;
                }
            } else {
                $('#pipedrive_keycheck').html("");
                $('#pipedrive_key').css("border-color","");
                var pipedrivestatus = 0;

            }
            $("#submit13").html("<i class='fas fa-circle-notch fa-spin'></i>  {{ __('message.please_wait') }}");
            $.ajax ({
                url: '{{url("updatepipedriveDetails")}}',
                type : 'post',
                data: {
                    "status": pipedrivestatus,
                    "pipedrive_key": $('#pipedrive_key').val(),
                },
                success: function (data) {
                    $('#alertMessage').show();
                    var result =  '<div class="alert alert-success alert-dismissable"><button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button><strong><i class="fa fa-check"></i> {{ __('message.success') }}! </strong>'+data.update+'.</div>';
                    $('#alertMessage').html(result+ ".");
                    $("#submit13").html("<i class='fa fa-save'>&nbsp;&nbsp;</i>{{ __('message.save') }}");
                    setInterval(function(){
                        $('#alertMessage').slideUp(3000);
                    }, 1000);
                },
            })
        });

        /*
        * Domain Check Setting
        */
        $(document).ready(function (){
            var domainstatus =  $('.checkbox15').val();
            if(domainstatus ==1)
            {
                $('#domain').prop('checked',true);
            } else if(domainstatus ==0){
                $('#domain').prop('checked',false);
            }
        });
        //Validate and pass value through ajax
        $("#submit14").on('click',function (){ //When Submit button is checked
            if ($('#domain').prop('checked')) {//if button is on
                var domainstatus = 1;
            } else {
                var domainstatus = 0;
            }
            $("#submit14").html("<i class='fas fa-circle-notch fa-spin'></i>  {{ __('message.please_wait') }}");
            $.ajax ({
                url: '{{url("updatedomainCheckDetails")}}',
                type : 'post',
                data: {
                    "status": domainstatus,
                },
                success: function (data) {
                    $('#alertMessage').show();
                    var result =  '<div class="alert alert-success alert-dismissable"><button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button><strong><i class="fa fa-check"></i> {{ __('message.success') }}! </strong>'+data.update+'.</div>';
                    $('#alertMessage').html(result+ ".");
                    $("#submit14").html("<i class='fa fa-save'>&nbsp;</i>{{ __('message.save') }}");
                    setInterval(function(){
                        $('#alertMessage').slideUp(3000);
                    }, 1000);
                },
            });
        });

    </script>
@stop