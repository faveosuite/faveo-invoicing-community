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
        <li class="breadcrumb-item"><a href="{{url('/')}}"><i class="fa fa-dashboard"></i> Home</a></li>
        <li class="breadcrumb-item"><a href="{{url('settings')}}"> Settings</a></li>
        <li class="breadcrumb-item active">Api Key</li>
    </ol>
</div><!-- /.col -->
@stop
@section('content')


    <div class="card card-secondary card-outline" >

        <!-- /.box-header -->
        <div class="card-body">
            <div id="alertMessage"></div>
            <div class="scrollit" style="height:800px">
                <div class="row">
                    <div class="col-md-12">
                        <table id="custom-table" class="table table-striped">
                            <thead>
                            <tr>
                                <th>Options</th>
                                <th>Status</th>
                                <th>Action</th>
                            </tr>
                            </thead>
                        </table>
{{--                        <table class="table table-bordered ">--}}
{{--                            <thead>--}}
{{--                            <tr>--}}

{{--                                <th>Options</th>--}}
{{--                                <th>Status</th>--}}
{{--                                <th>Fields</th>--}}
{{--                                <th>Action</th>--}}
{{--                            </tr>--}}
{{--                            </thead>--}}
{{--                            <tbody>--}}
{{--                            <tr>--}}

{{--                                <td class="col-md-2">Auto Faveo Licenser & Update Manager</td>--}}
{{--                                <td class="col-md-2">--}}
{{--                                    <label class="switch toggle_event_editing">--}}

{{--                                        @if($status==1)--}}
{{--                                            <p>Active</p>--}}
{{--                                        @else--}}
{{--                                            <p>Inactive</p>--}}
{{--                                        @endif--}}
{{--                                        <input type="checkbox" value="{{$status}}"  name="modules_settings"--}}
{{--                                               class="checkbox" id="License">--}}
{{--                                        <span class="slider round"></span>--}}
{{--                                    </label>--}}

{{--                                </td>--}}

{{--                                <td class="col-md-4 licenseEmptyField">--}}
{{--                                    {!! Form::label('lic_api_secret',Lang::get('message.lic_api_secret')) !!}--}}
{{--                                    {!! Form::text('license_api',null,['class' => 'form-control secretHide','disabled'=>'disabled'--}}
{{--                                    ]) !!}--}}
{{--                                    <h6 id="license_apiCheck"></h6>--}}
{{--                                    <br/>--}}
{{--                                    <!-- last name -->--}}
{{--                                    {!! Form::label('lic_api_url',Lang::get('message.lic_api_url')) !!} :--}}
{{--                                    {!! Form::text('license_api',null,['class' => 'form-control urlHide','disabled'=>'disabled']) !!}--}}
{{--                                    <h6 id="license_urlCheck"></h6>--}}
{{--                                    <br/>--}}
{{--                                    {!! Form::label('lic_client_id',Lang::get('message.lic_client_id')) !!} :--}}
{{--                                    {!! Form::text('license_client_id',null,['class' => 'form-control urlHide','disabled'=>'disabled']) !!}--}}
{{--                                    <h6 id="license_clientIdCheck"></h6>--}}
{{--                                    <br/>--}}
{{--                                    {!! Form::label('lic_client_secret',Lang::get('message.lic_client_secret')) !!} :--}}
{{--                                    {!! Form::text('license_client_secret',null,['class' => 'form-control urlHide','disabled'=>'disabled']) !!}--}}
{{--                                    <h6 id="license_clientSecretCheck"></h6>--}}
{{--                                    <br/>--}}
{{--                                    {!! Form::label('lic_grant_type',Lang::get('message.lic_grant_type')) !!} :--}}
{{--                                    {!! Form::text('license_grant_type',null,['class' => 'form-control urlHide','disabled'=>'disabled']) !!}--}}
{{--                                    <h6 id="license_grantTypeCheck"></h6>--}}

{{--                                </td>--}}

{{--                                <td class="col-md-4 LicenseField hide">--}}


{{--                                    <!-- last name -->--}}
{{--                                    {!! Form::label('lic_api_secret',Lang::get('message.lic_api_secret')) !!}--}}
{{--                                    {!! Form::text('license_api_secret',$licenseSecret,['class' => 'form-control','id'=>'license_api_secret']) !!}--}}
{{--                                    <h6 id="license_apiCheck"></h6>--}}
{{--                                    <br/>--}}

{{--                                    <!-- last name -->--}}
{{--                                    {!! Form::label('lic_api_url',Lang::get('message.lic_api_url')) !!} :--}}
{{--                                    {!! Form::text('license_api_url',$licenseUrl,['class' => 'form-control','id'=>'license_api_url']) !!}--}}
{{--                                    <h6 id="license_urlCheck"></h6>--}}
{{--                                    <br/>--}}

{{--                                    {!! Form::label('lic_client_id',Lang::get('message.lic_client_id')) !!} :--}}
{{--                                    {!! Form::text('license_client_id',$licenseClientId,['class' => 'form-control','id'=>'license_client_id']) !!}--}}
{{--                                    <h6 id="license_clientIdCheck"></h6>--}}
{{--                                    <br/>--}}

{{--                                    {!! Form::label('lic_client_secret',Lang::get('message.lic_client_secret')) !!} :--}}
{{--                                    {!! Form::text('license_client_secret',$licenseClientSecret,['class' => 'form-control','id'=>'license_client_secret']) !!}--}}
{{--                                    <h6 id="license_clientSecretCheck"></h6>--}}
{{--                                    <br/>--}}


{{--                                    {!! Form::label('lic_grant_type',Lang::get('message.lic_grant_type')) !!} :--}}
{{--                                    {!! Form::text('license_grant_type',$licenseGrantType,['class' => 'form-control','id'=>'license_grant_type']) !!}--}}
{{--                                    <h6 id="license_grantTypeCheck"></h6>--}}


{{--                                </td>--}}
{{--                            --}}
{{--                                <td class="col-md-2"><button type="submit" class="form-group btn btn-primary"  onclick="licenseDetails()" id="submit"><i class="fa fa-save">&nbsp;</i>{!!Lang::get('message.save')!!}</button></td>--}}
{{--                                <td class="col-md-2"><a href="#create-third-party-app" data-toggle="modal" data-target="#create-third-party-app" class="btn btn-sm btn-secondary btn-xs editThirdPartyApp"><span class="fa fa-edit"></span></a></td>--}}

{{--                            </tr>--}}

{{--                            <tr>--}}

{{--                                <td class="col-md-2">Don't Allow Domin/Ip based Restriction</td>--}}
{{--                                <td class="col-md-2">--}}
{{--                                    <label class="switch toggle_event_editing">--}}

{{--                                        @if($domainCheckStatus==1)--}}
{{--                                            <p>Active</p>--}}
{{--                                        @else--}}
{{--                                            <p>Inactive</p>--}}
{{--                                        @endif--}}
{{--                                        <input type="checkbox" value="{{$domainCheckStatus}}"  name="domain_settings"--}}
{{--                                               class="checkbox15" id="domain">--}}
{{--                                        <span class="slider round"></span>--}}
{{--                                    </label>--}}

{{--                                </td>--}}
{{--                                <td class="col-md-4 domainverify">--}}

{{--                                    <b>Not Available</b>--}}


{{--                                </td>--}}
{{--                                <td class="col-md-2"><button type="submit" class="form-group btn btn-primary"  id="submit14"><i class="fa fa-save">&nbsp;&nbsp;</i>{!!Lang::get('message.save')!!}</button></td>--}}
{{--                                <td class="col-md-2"><b>Not Available</b></td>--}}
{{--                            </tr>--}}


{{--                            <!--<tr>--}}

{{--                  <td class="col-md-2">Auto Update</td>--}}
{{--                  <td class="col-md-2">--}}
{{--                    <label class="switch toggle_event_editing">--}}

{{--                         <input type="checkbox" value="{{$updateStatus}}"  name="modules_settings"--}}
{{--                          class="checkbox3" id="update">--}}
{{--                          <span class="slider round"></span>--}}
{{--                    </label>--}}

{{--                  </td>--}}

{{--                  <td class="col-md-4 updateEmptyField">--}}
{{--                  {!! Form::label('update_api_secret',Lang::get('message.lic_api_secret')) !!}--}}
{{--                            {!! Form::text('update_api',null,['class' => 'form-control updatesecretHide','disabled'=>'disabled']) !!}-->--}}



{{--                            <!-- last name -->--}}
{{--                            <!-- {!! Form::label('update_api_url',Lang::get('message.lic_api_url')) !!} :--}}
{{--                        {!! Form::text('update_api',null,['class' => 'form-control updateurlHide','disabled'=>'disabled']) !!}--}}

{{--                            </td>--}}
{{--                            <td class="col-md-4 updateField hide">-->--}}


{{--                            <!-- last name -->--}}
{{--                            <!--{!! Form::label('update_api_secret',Lang::get('message.lic_api_secret')) !!}--}}
{{--                            {!! Form::text('update_api_secret',$updateSecret,['class' => 'form-control','id'=>'update_api_secret']) !!}--}}
{{--                            <h6 id="update_apiCheck"></h6>--}}
{{--                            <br/>-->--}}

{{--                            <!-- last name -->--}}
{{--                            <!--{!! Form::label('update_api_url',Lang::get('message.lic_api_url')) !!} :--}}
{{--                        {!! Form::text('update_api_url',$updateUrl,['class' => 'form-control','id'=>'update_api_url']) !!}--}}
{{--                            <h6 id="update_urlCheck"></h6>--}}

{{--                   </td>--}}
{{--                      <td class="col-md-2" ><button type="submit" class="form-group btn btn-primary" onclick="updateDetails()" id="submitudpate"><i class="fa fa-save">&nbsp;&nbsp;</i>{!!Lang::get('message.save')!!}</button></td>--}}
{{--                </tr>-->--}}

{{--                --}}

{{--                            <tr>--}}
{{--                                <td class="col-md-2">Google reCAPTCHA</td>--}}
{{--                                <td class="col-md-2">--}}
{{--                                    <label class="switch toggle_event_editing">--}}
{{--                                            @if($captchaStatus || $v3CaptchaStatus)--}}
{{--                                                <p>Active</p>--}}
{{--                                        @else--}}
{{--                                            <p>Inactive</p>--}}
{{--                                        @endif--}}
{{--                                        <input type="checkbox" value="{{ $captchaStatus || $v3CaptchaStatus}}"  name="modules_settings"--}}
{{--                                               class="checkbox2" id="captcha">--}}
{{--                                        <span class="slider round"></span>--}}
{{--                                    </label>--}}

{{--                                </td>--}}

{{--                                <td class="col-md-4 captchaEmptyField">--}}
{{--                                    {!! Form::label('nocaptcha_secret',Lang::get('message.nocaptcha_secret')) !!}--}}
{{--                                    {!! Form::text('nocaptcha_secret1',null,['class' => 'form-control nocapsecretHide','disabled'=>'disabled']) !!}--}}
{{--                                    <h6 id=""></h6>--}}


{{--                                    <!-- last name -->--}}
{{--                                    {!! Form::label('nocaptcha_sitekey',Lang::get('message.nocaptcha_sitekey')) !!} :--}}
{{--                                    {!! Form::text('nocaptcha_sitekey1',null,['class' => 'form-control siteKeyHide','disabled'=>'disabled']) !!}--}}
{{--                                    <h6 id=""></h6>--}}
{{--                                </td>--}}
{{--                                <td class="col-md-4 captchaField hide">--}}
{{--                                    <div class="form-group m-1 d-flex">--}}
{{--                                        <div class="custom-control custom-radio m-2">--}}
{{--                                            <input class="custom-control-input" type="radio" id="captchaRadioV2" name="customRadio" {{ $captchaStatus === 1 ? 'checked' : '' }}>--}}
{{--                                            <label for="captchaRadioV2" class="custom-control-label">{{ __('message.recaptcha_v2') }}</label>--}}
{{--                                        </div>--}}
{{--                                        <div class="custom-control custom-radio m-2">--}}
{{--                                            <input class="custom-control-input" type="radio" id="captchaRadioV3" name="customRadio" {{ $v3CaptchaStatus === 1 ? 'checked' : '' }}>--}}
{{--                                            <label for="captchaRadioV3" class="custom-control-label">{{ __('message.recaptcha_v3') }}</label>--}}
{{--                                        </div>--}}
{{--                                    </div>--}}

{{--                                    <!-- last name -->--}}
{{--                                    {!! Form::label('nocaptcha_sitekey',Lang::get('message.nocaptcha_sitekey')) !!}--}}
{{--                                    {!! Form::text('nocaptcha_sitekey',$siteKey,['class' => 'form-control','id'=>'nocaptcha_sitekey']) !!}--}}
{{--                                    <h6 id="captcha_sitekeyCheck"></h6>--}}

{{--                                    <!-- last name -->--}}
{{--                                    {!! Form::label('nocaptcha_secret',Lang::get('message.nocaptcha_secret')) !!}--}}
{{--                                    {!! Form::text('nocaptcha_secret',$secretKey,['class' => 'form-control','id'=>'nocaptcha_secret']) !!}--}}
{{--                                    <h6 id="captcha_secretCheck"></h6>--}}
{{--                                    <br/>--}}

{{--                                </td>--}}
{{--                                <td class="col-md-2"><button type="submit" class="form-group btn btn-primary" onclick="captchaDetails()" id="submit2"><i class="fa fa-save">&nbsp;&nbsp;</i>{!!Lang::get('message.save')!!}</button></td>--}}
{{--                                <td class="col-md-2"><a href="#google-recaptcha" data-toggle="modal" data-target="#google-recaptcha" class="btn btn-sm btn-secondary btn-xs editThirdPartyApp"><span class="fa fa-edit"></span></a></td>--}}

{{--                            </tr>--}}
{{--                         <tr>--}}

{{--                                <td class="col-md-2">Msg 91(Mobile Verification)</td>--}}
{{--                                <td class="col-md-2">--}}
{{--                                    <label class="switch toggle_event_editing">--}}
{{--                                        @if($mobileStatus)--}}
{{--                                            <p>Active</p>--}}
{{--                                        @else--}}
{{--                                            <p>Inactive</p>--}}
{{--                                        @endif--}}

{{--                                    </label>--}}

{{--                                </td>--}}
{{--                                <td class="col-md-4 mobileverify">--}}

{{--                                    <input type ="hidden" id="hiddenMobValue" value="{{$mobileauthkey}}">--}}
{{--                                    <!-- last name -->--}}
{{--                                    {!! Form::label('mobile',Lang::get('message.msg91_key')) !!}--}}
{{--                                    {!! Form::text('msg91_auth_key',$mobileauthkey,['class' => 'form-control mobile_authkey','id'=>'mobile_authkey']) !!}--}}
{{--                                    <h6 id="mobile_check"></h6>--}}
{{--                                    <br/>--}}
{{--                                    <input type ="hidden" id="hiddenSender" value="{{$msg91Sender}}">--}}
{{--                                    {!! Form::label('mobile',Lang::get('message.msg91_sender')) !!}--}}
{{--                                    {!! Form::text('msg91_sender',$msg91Sender,['class' => 'form-control sender','id'=>'sender']) !!}--}}
{{--                                    <h6 id="sender_check"></h6>--}}

{{--                                    <input type ="hidden" id="hiddenTemplate" value="{{$msg91TemplateId}}">--}}
{{--                                    {!! Form::label('mobile',Lang::get('message.msg91_template_id')) !!}--}}
{{--                                    {!! Form::text('msg91_template_id',$msg91TemplateId,['class' => 'form-control template_id','id'=>'template_id']) !!}--}}
{{--                                    <h6 id="template_check"></h6>--}}
{{--                                </td>--}}
{{--                                <td class="col-md-2"><button type="submit" class="form-group btn btn-primary"  id="submit3"><i class="fa fa-save">&nbsp;&nbsp;</i>{!!Lang::get('message.save')!!}</button></td>--}}
{{--                             <td class="col-md-2"><a href="#msg-91" data-toggle="modal" data-target="#msg-91" class="btn btn-sm btn-secondary btn-xs editThirdPartyApp"><span class="fa fa-edit"></span></a></td>--}}

{{--                         </tr>--}}


{{--                            <tr>--}}

{{--                                <td class="col-md-2">Mailchimp</td>--}}
{{--                                <td class="col-md-2">--}}
{{--                                    <label class="switch toggle_event_editing">--}}

{{--                                @if($mailchimpSetting==1)--}}
{{--                                        <p>Active</p>--}}
{{--                                    @else--}}
{{--                                        <p>Inactive</p>--}}
{{--                                    @endif--}}
{{--                                    </label>--}}

{{--                                    --}}{{--                                    <label class="switch toggle_event_editing">--}}

{{--                                        <input type="checkbox" value="{{$mailchimpSetting}}"  name="mobile_settings"--}}
{{--                                               class="checkbox9" id="mailchimp">--}}
{{--                                        <span class="slider round"></span>--}}
{{--                                    </label>--}}

{{--                                </td>--}}
{{--                                <td class="col-md-4 mailchimpverify">--}}

{{--                                    <input type ="hidden" id="hiddenMailChimpValue" value="{{$mailchimpKey}}">--}}
{{--                                    <!-- last name -->--}}
{{--                                    {!! Form::label('mailchimp',Lang::get('message.mailchimp_key')) !!}--}}
{{--                                    {!! Form::text('mailchimp',$mailchimpKey,['class' => 'form-control mailchimp_authkey','id'=>'mailchimp_authkey']) !!}--}}
{{--                                    <h6 id="mailchimp_check"></h6>--}}
{{--                                    <br/>--}}


{{--                                </td>--}}
{{--                                <td class="col-md-2"><a href="#mailchimps" data-toggle="modal" data-target="#mailchimps" class="btn btn-sm btn-secondary btn-xs editThirdPartyApp"><span class="fa fa-edit"></span></a></td>--}}

{{--                            </tr>--}}

{{--                            <tr>--}}

{{--                                <td class="col-md-2">Show Terms on Registration Page</td>--}}
{{--                                <td class="col-md-2">--}}
{{--                                    <label class="switch toggle_event_editing">--}}
{{--                                        @if($termsStatus)--}}
{{--                                            <p>Active</p>--}}
{{--                                        @else--}}
{{--                                            <p>Inactive</p>--}}
{{--                                        @endif--}}
{{--                                    </label>--}}

{{--                                </td>--}}
{{--                                <td class="col-md-4 termsverify">--}}

{{--                                    <input type ="hidden" id="hiddenTermsValue" value="{{$termsUrl}}">--}}
{{--                                    <!-- last name -->--}}
{{--                                    {!! Form::label('terms',Lang::get('message.terms_url')) !!}--}}
{{--                                    {!! Form::text('terms',$termsUrl,['class' => 'form-control terms_url','id'=>'terms_url']) !!}--}}
{{--                                    <h6 id="terms_check"></h6>--}}
{{--                                    <br/>--}}


{{--                                </td>--}}
{{--                                <td class="col-md-2"><button type="submit" class="form-group btn btn-primary"  id="submit10"><i class="fa fa-save">&nbsp;</i>{!!Lang::get('message.save')!!}</button></td>--}}
{{--                                <td class="col-md-2"><a href="#showTerms" data-toggle="modal" data-target="#showTerms" class="btn btn-sm btn-secondary btn-xs editThirdPartyApp"><span class="fa fa-edit"></span></a></td>--}}

{{--                            </tr>--}}


{{--                            @if($mailSendingStatus)--}}

{{--                                <tr>--}}

{{--                                    <td class="col-md-2">Email Verification</td>--}}
{{--                                    <td class="col-md-2">--}}
{{--                                        <label class="switch toggle_event_editing">--}}

{{--                                            <input type="checkbox" value="{{$emailStatus}}"  name="email_settings"--}}
{{--                                                   class="checkbox5" id="email">--}}
{{--                                            <span class="slider round"></span>--}}
{{--                                        </label>--}}

{{--                                    </td>--}}
{{--                                    <td class="col-md-4 mobileverify">--}}

{{--                                        <b>Not Available</b>--}}


{{--                                    </td>--}}
{{--                                    <td class="col-md-2" ><button type="submit" class="form-group btn btn-primary"  id="submit4"><i class="fa fa-save">&nbsp;&nbsp;</i>{!!Lang::get('message.save')!!}</button></td>--}}
{{--                                </tr>--}}
{{--                            @endif--}}


{{--                            <tr>--}}

{{--                                <td class="col-md-2">Twitter</td>--}}
{{--                                <td class="col-md-2">--}}
{{--                                    <label class="switch toggle_event_editing">--}}
{{--                                        @if($twitterStatus==1)--}}
{{--                                            <p>Active</p>--}}
{{--                                        @else--}}
{{--                                            <p>Inactive</p>--}}
{{--                                            @endif--}}
{{--                                    </label>--}}
{{--                                </td>--}}

{{--                                <td class="col-md-2 twitterverify">--}}
{{--                                    <input type ="hidden" id="hidden_consumer_key" value="{{$twitterKeys->twitter_consumer_key}}">--}}
{{--                                    <input type ="hidden" id="hidden_consumer_secret" value="{{$twitterKeys->twitter_consumer_secret}}">--}}
{{--                                    <input type ="hidden" id="hidden_access_token" value="{{$twitterKeys->twitter_access_token}}">--}}
{{--                                    <input type ="hidden" id="hidden_token_secret" value="{{$twitterKeys->access_tooken_secret}}">--}}
{{--                                    {!! Form::label('consumer_key',Lang::get('message.consumer_key')) !!}--}}
{{--                                    {!! Form::text('consumer_key',$twitterKeys->twitter_consumer_key,['class' => 'form-control consumer_key','id'=>'consumer_key']) !!}--}}
{{--                                    <h6 id="consumer_keycheck"></h6>--}}


{{--                                    <!-- last name -->--}}
{{--                                    {!! Form::label('consumer_secret',Lang::get('message.consumer_secret')) !!}--}}
{{--                                    {!! Form::text('consumer_secret',$twitterKeys->twitter_consumer_secret,['class' => 'form-control consumer_secret','id'=>'consumer_secret']) !!}--}}
{{--                                    <h6 id="consumer_secretcheck"></h6>--}}



{{--                                    {!! Form::label('access_token',Lang::get('message.access_token')) !!}--}}
{{--                                    {!! Form::text('access_token',$twitterKeys->twitter_access_token,['class' => 'form-control access_token','id'=>'access_token']) !!}--}}
{{--                                    <h6 id="access_tokencheck"></h6>--}}



{{--                                    {!! Form::label('token_secret',Lang::get('message.token_secret')) !!}--}}
{{--                                    {!! Form::text('token_secret',$twitterKeys->access_tooken_secret,['class' => 'form-control token_secret','id'=>'token_secret']) !!}--}}
{{--                                    <h6 id="token_secretcheck"></h6>--}}


{{--                                </td>--}}

{{--                                <td class="col-md-2" ><button type="submit" class="form-group btn btn-primary"  id="submit5"><i class="fa fa-save">&nbsp;&nbsp;</i>{!!Lang::get('message.save')!!}</button></td>--}}
{{--                                <td class="col-md-2"><a href="#twitters" data-toggle="modal" data-target="#twitters" class="btn btn-sm btn-secondary btn-xs editThirdPartyApp"><span class="fa fa-edit"></span></a></td>--}}

{{--                            </tr>--}}

{{--                            <tr>--}}

{{--                                <td class="col-md-2">Zoho CRM</td>--}}
{{--                                <td class="col-md-2">--}}
{{--                                    <label class="switch toggle_event_editing">--}}
{{--                                        @if($zohoStatus)--}}
{{--                                            <p>Active</p>--}}
{{--                                        @else--}}
{{--                                            <p>Inactive</p>--}}
{{--                                        @endif--}}
{{--                                    </label>--}}

{{--                                </td>--}}
{{--                                <td class="col-md-4 zohoverify">--}}

{{--                                    <input type ="hidden" id="hidden_zoho_key" value="{{$zohoKey}}">--}}
{{--                                    <!-- last name -->--}}
{{--                                    {!! Form::label('zoho_key',Lang::get('message.zoho_crm')) !!}--}}
{{--                                    {!! Form::text('zoho_key',$zohoKey,['class' => 'form-control zoho_key','id'=>'zoho_key']) !!}--}}
{{--                                    <h6 id="zoho_keycheck"></h6>--}}
{{--                                    <br/>--}}


{{--                                </td>--}}
{{--                                <td class="col-md-2"><button type="submit" class="form-group btn btn-primary"  id="submit7"><i class="fa fa-save">&nbsp;&nbsp;</i>{!!Lang::get('message.save')!!}</button></td>--}}
{{--                                <td class="col-md-2"><a href="#zohoCrm" data-toggle="modal" data-target="#zohoCrm" class="btn btn-sm btn-secondary btn-xs editThirdPartyApp"><span class="fa fa-edit"></span></a></td>--}}

{{--                            </tr>--}}

{{--                            <tr>--}}

{{--                                <td class="col-md-2">Pipedrive</td>--}}
{{--                                <td class="col-md-2">--}}
{{--                                    <label class="switch toggle_event_editing">--}}
{{--                                        @if($pipedriveStatus)--}}
{{--                                            <p>Active</p>--}}
{{--                                        @else--}}
{{--                                            <p>Inactive</p>--}}
{{--                                        @endif--}}
{{--                                    </label>--}}

{{--                                </td>--}}
{{--                                <td class="col-md-4 pipedriveverify">--}}

{{--                                    <input type ="hidden" id="hidden_pipedrive_key" value="{{$pipedriveKey}}">--}}
{{--                                    <!-- last name -->--}}
{{--                                    {!! Form::label('pipedrive_key',Lang::get('message.pipedrive_key')) !!}--}}
{{--                                    {!! Form::text('pipedrive_key',$pipedriveKey,['class' => 'form-control pipedrive_key','id'=>'pipedrive_key']) !!}--}}
{{--                                    <h6 id="pipedrive_keycheck"></h6>--}}
{{--                                    <br/>--}}


{{--                                </td>--}}
{{--                                <td class="col-md-2"><button type="submit" class="form-group btn btn-primary"  id="submit13"><i class="fa fa-save">&nbsp;&nbsp;</i>{!!Lang::get('message.save')!!}</button></td>--}}
{{--                                <td class="col-md-2"><a href="#pipedrv" data-toggle="modal" data-target="#pipedrv" class="btn btn-sm btn-secondary btn-xs editThirdPartyApp"><span class="fa fa-edit"></span></a></td>--}}

{{--                            </tr>--}}

{{--                            <tr>--}}
{{--                                <td class="col-md-2">Github</td>--}}
{{--                                <td class="col-md-2">--}}
{{--                                    <label class="switch toggle_event_editing">--}}
{{--                                    @if($githubStatus==1)--}}
{{--                                        <p>Active</p>--}}
{{--                                    @else--}}
{{--                                        <p>Inactive</p>--}}
{{--                                    @endif--}}
{{--                                    </label>--}}
{{--                                </td>--}}
{{--                                <td class="col-md-2"><a href="#githubSet" data-toggle="modal" data-target="#githubSet" class="btn btn-sm btn-secondary btn-xs editThirdPartyApp"><span class="fa fa-edit"></span></a></td>--}}

{{--                            </tr>--}}
{{--                            </tbody>--}}


{{--                        </table>--}}
                    </div>
                </div>
            </div>
        </div>
        <!-- /.box-body -->
    </div>


    <div class="modal fade" id="githubSet" data-backdrop="static" data-keyboard="false">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title">Github Setings</h4>

                </div>
                <div class="modal-body">
                    <input type ="hidden" id="hidden_git_username" value="{{$githubFileds->username}}">
                    <input type ="hidden" id="hidden_git_password" value="{{$githubFileds->password}}">
                    <input type ="hidden" id="hidden_git_client" value="{{$githubFileds->client_id}}">
                    <input type ="hidden" id="hidden_client_secret" value="{{$githubFileds->client_secret}}">
                    <div class= "form-group {{ $errors->has('name') ? 'has-error' : '' }}">
                        {!! Form::label('username',Lang::get('message.username'),['class'=>'required']) !!}
                        {!! Form::text('username',$githubFileds->username,['class' => 'form-control git_username','id'=>'git_username']) !!}
                        <h6 id="user"></h6>
                    </div>

                    <div class= "form-group {{ $errors->has('name') ? 'has-error' : '' }}">
                        <!-- last name -->
                        {!! Form::label('password',Lang::get('message.password'),['class'=>'required']) !!}
                        <input type= "password" value="{{$githubFileds->password}}" name="password" id="git_password" class="form-control git_password">
                        <h6 id="pass"></h6>
                    </div>

                    <div class= "form-group {{ $errors->has('name') ? 'has-error' : '' }}">
                        {!! Form::label('client_id',Lang::get('message.client_id'),['class'=>'required']) !!}
                        {!! Form::text('client_id',$githubFileds->client_id,['class' => 'form-control git_client','id'=>'git_client']) !!}
                        <h6 id="c_id"></h6>
                    </div>

                    <div class= "form-group {{ $errors->has('name') ? 'has-error' : '' }}">
                        {!! Form::label('client_secret',Lang::get('message.client_secret'),['class'=>'required']) !!}
                        {!! Form::text('client_secret',$githubFileds->client_secret,['class' => 'form-control git_secret','id'=>'git_secret']) !!}
                        <h6 id="c_secret"></h6>
                    </div>

                    <div style="display: flex; align-items: center; gap: 10px;">
                        {!! Form::label('github_settings',Lang::get('message.github_settings'),['class'=>'required']) !!}

                        <label class="switch toggle_event_editing">
                            <input type="checkbox" value="{{$githubStatus}}" name="github_settings" class="checkbox" id="github">
                            <span class="slider round"></span>
                        </label>
                    </div>

                </div>

                <div class="modal-footer justify-content-between">
                    <button type="button" id="close" class="btn btn-default pull-left closebutton" data-dismiss="modal"><i class="fa fa-times"></i>&nbsp;Close</button>
                    <button type="submit" id="submit" class="btn btn-primary pull-right" id="submit" data-loading-text="<i class='fa fa-circle-o-notch fa-spin'>&nbsp;</i> Saving..."><i class="fa fa-sync-alt">&nbsp;</i>{!!Lang::get('message.update')!!}</button>

                </div>
            </div>
        </div>
    </div>


    <div class="modal fade" id="create-third-party-app" data-backdrop="static" data-keyboard="false">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title">Auto Faveo Licenser & Update Manager</h4>

                </div>
                <div class="modal-body">

                    <div class= "form-group {{ $errors->has('name') ? 'has-error' : '' }}">
                        {!! Form::label('lic_api_secret',Lang::get('message.lic_api_secret'),['class'=>'required']) !!}
                        {!! Form::text('license_api_secret',$licenseSecret,['class' => 'form-control','id'=>'license_api_secret']) !!}
                        <h6 id="license_apiCheck"></h6>
                    </div>

                    <div class= "form-group {{ $errors->has('name') ? 'has-error' : '' }}">
                        <!-- last name -->
                        {!! Form::label('lic_api_url',Lang::get('message.lic_api_url'),['class'=>'required']) !!}
                        {!! Form::text('license_api_url',$licenseUrl,['class' => 'form-control','id'=>'license_api_url']) !!}
                        <h6 id="license_urlCheck"></h6>
                    </div>

                    <div class= "form-group {{ $errors->has('name') ? 'has-error' : '' }}">
                        {!! Form::label('lic_client_id',Lang::get('message.lic_client_id'),['class'=>'required']) !!}
                        {!! Form::text('license_client_id',$licenseClientId,['class' => 'form-control','id'=>'license_client_id']) !!}
                        <h6 id="license_clientIdCheck"></h6>
                    </div>

                    <div class= "form-group {{ $errors->has('name') ? 'has-error' : '' }}">
                        {!! Form::label('lic_client_secret',Lang::get('message.lic_client_secret'),['class'=>'required']) !!}
                        {!! Form::text('license_client_secret',$licenseClientSecret,['class' => 'form-control','id'=>'license_client_secret']) !!}
                        <h6 id="license_clientSecretCheck"></h6>
                    </div>

                    <div class= "form-group {{ $errors->has('name') ? 'has-error' : '' }}">
                        {!! Form::label('lic_grant_type',Lang::get('message.lic_grant_type'),['class'=>'required']) !!}
                        {!! Form::text('license_grant_type',$licenseGrantType,['class' => 'form-control','id'=>'license_grant_type']) !!}
                        <h6 id="license_grantTypeCheck"></h6>
                    </div>

                    <div style="display: flex; align-items: center; gap: 10px;">
                        {!! Form::label('modules_settings',Lang::get('message.modules_settings'),['class'=>'required']) !!}

                        <label class="switch toggle_event_editing">
                        <input type="checkbox" value="{{$status}}"  name="modules_settings"
                               class="checkbox" id="License">
                        <span class="slider round"></span>
                    </label>
                    </div>
                </div>

                <div class="modal-footer justify-content-between">
                    <button type="button" id="close" class="btn btn-default pull-left closebutton" data-dismiss="modal"><i class="fa fa-times"></i>&nbsp;Close</button>
                    <button type="submit" class="form-group btn btn-primary"  onclick="licenseDetails()" id="submit"><i class="fa fa-save">&nbsp;</i>{!!Lang::get('message.save')!!}</button>

                </div>
            </div>
        </div>
    </div>



    <div class="modal fade" id="google-recaptcha" data-backdrop="static" data-keyboard="false">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title">Google reCAPTCHA</h4>

                </div>
                <div class="modal-body">
                    <div class="form-group m-1 d-flex">
                        <div class="custom-control custom-radio m-2">
                            <input class="custom-control-input " type="radio" id="captchaRadioV2" name="customRadio" {{ $captchaStatus === 1 ? 'checked' : '' }}>
                            <label for="captchaRadioV2" class="custom-control-label">{{ __('message.recaptcha_v2') }}</label>
                        </div>
                        <div class="custom-control custom-radio m-2">
                            <input class="custom-control-input" type="radio" id="captchaRadioV3" name="customRadio" {{ $v3CaptchaStatus === 1 ? 'checked' : '' }}>
                            <label for="captchaRadioV3" class="custom-control-label">{{ __('message.recaptcha_v3') }}</label>
                        </div>
                    </div>
                    <div class= "form-group {{ $errors->has('name') ? 'has-error' : '' }}">
                        {!! Form::label('nocaptcha_sitekey',Lang::get('message.nocaptcha_sitekey'),['class'=>'required']) !!}
                        {!! Form::text('nocaptcha_sitekey',$siteKey,['class' => 'form-control','id'=>'nocaptcha_sitekey']) !!}
                        <h6 id="captcha_sitekeyCheck"></h6>
                    </div>

                    <div class= "form-group {{ $errors->has('name') ? 'has-error' : '' }}">
                        <!-- last name -->
                        {!! Form::label('nocaptcha_secret',Lang::get('message.nocaptcha_secret'),['class'=>'required']) !!}
                        {!! Form::text('nocaptcha_secret',$secretKey,['class' => 'form-control','id'=>'nocaptcha_secret']) !!}
                        <h6 id="captcha_secretCheck"></h6>
                    </div>

                    <div style="display: flex; align-items: center; gap: 10px;">
                        {!! Form::label('captcha',Lang::get('message.captcha'),['class'=>'required']) !!}
                        <label class="switch toggle_event_editing">
                    <input type="checkbox" value="{{ $captchaStatus || $v3CaptchaStatus}}"  name="modules_settings"
                           class="checkbox2" id="captcha">
                    <span class="slider round"></span>
                    </label>
                    </div>
                </div>

                <div class="modal-footer justify-content-between">
                    <button type="button" id="close" class="btn btn-default pull-left closebutton" data-dismiss="modal"><i class="fa fa-times"></i>&nbsp;Close</button>
                    <button type="submit" class="form-group btn btn-primary" onclick="captchaDetails()" id="submit2"><i class="fa fa-save">&nbsp;&nbsp;</i>{!!Lang::get('message.save')!!}</button>

                </div>
            </div>
        </div>
    </div>


    <div class="modal fade" id="msg-91" data-backdrop="static" data-keyboard="false">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title">Msg 91(Mobile Verification)</h4>

                </div>
                <div class="modal-body">
                    <input type ="hidden" id="hiddenMobValue" value="{{$mobileauthkey}}">

                    <div class= "form-group {{ $errors->has('name') ? 'has-error' : '' }}">
                        {!! Form::label('mobile',Lang::get('message.msg91_key'),['class'=>'required']) !!}
                        {!! Form::text('msg91_auth_key',$mobileauthkey,['class' => 'form-control mobile_authkey','id'=>'mobile_authkey']) !!}
                        <h6 id="mobile_check"></h6>
                    </div>

                    <input type ="hidden" id="hiddenSender" value="{{$msg91Sender}}">

                    <div class= "form-group {{ $errors->has('name') ? 'has-error' : '' }}">
                        {!! Form::label('mobile',Lang::get('message.msg91_sender'),['class'=>'required']) !!}
                        {!! Form::text('msg91_sender',$msg91Sender,['class' => 'form-control sender','id'=>'sender']) !!}
                        <h6 id="sender_check"></h6>
                    </div>

                    <input type ="hidden" id="hiddenTemplate" value="{{$msg91TemplateId}}">

                    <div class= "form-group {{ $errors->has('name') ? 'has-error' : '' }}">
                        {!! Form::label('mobile',Lang::get('message.msg91_template_id'),['class'=>'required']) !!}
                        {!! Form::text('msg91_template_id',$msg91TemplateId,['class' => 'form-control template_id','id'=>'template_id']) !!}
                        <h6 id="template_check"></h6>
                    </div>

                    <div style="display: flex; align-items: center; gap: 10px;">
                        {!! Form::label('mobile_settings',Lang::get('message.mobile_settings'),['class'=>'required']) !!}
                        <label class="switch toggle_event_editing">
                    <input type="checkbox" value="{{$mobileStatus}}"  name="mobile_settings"
                           class="checkbox4" id="mobile">
                    <span class="slider round"></span>
                    </label>
                    </div>
                </div>

                <div class="modal-footer justify-content-between">
                    <button type="button" id="close" class="btn btn-default pull-left closebutton" data-dismiss="modal"><i class="fa fa-times"></i>&nbsp;Close</button>
{{--                    <button type="submit" class="form-group btn btn-primary" onclick="captchaDetails()" id="submit2"><i class="fa fa-save">&nbsp;&nbsp;</i>{!!Lang::get('message.save')!!}</button>--}}
                    <button type="submit" class="form-group btn btn-primary"  id="submit3"><i class="fa fa-save">&nbsp;&nbsp;</i>{!!Lang::get('message.save')!!}</button>

                </div>
            </div>
        </div>
    </div>


    <div class="modal fade" id="mailchimps" data-backdrop="static" data-keyboard="false">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title">Mailchimp</h4>
                </div>
                <div class="modal-body">
                    <input type ="hidden" id="hiddenMailChimpValue" value="{{$mailchimpKey}}">
                    <div class= "form-group {{ $errors->has('name') ? 'has-error' : '' }}">
                        {!! Form::label('mailchimp',Lang::get('message.mailchimp_key'),['class'=>'required']) !!}
                        {!! Form::text('mailchimp',$mailchimpKey,['class' => 'form-control mailchimp_authkey','id'=>'mailchimp_authkey']) !!}
                        <h6 id="mailchimp_check"></h6>
                    </div>
                    <div style="display: flex; align-items: center; gap: 10px;">
                        {!! Form::label('mailchimp',Lang::get('message.mailchimp_settings'),['class'=>'required']) !!}
                        <label class="switch toggle_event_editing">
                        <input type="checkbox" value="{{$mailchimpSetting}}"  name="mobile_settings"
                               class="checkbox9" id="mailchimp">
                        <span class="slider round"></span>
                    </label>
                    </div>
                </div>

                <div class="modal-footer justify-content-between">
                    <button type="button" id="close" class="btn btn-default pull-left closebutton" data-dismiss="modal"><i class="fa fa-times"></i>&nbsp;Close</button>
                   <button type="submit" class="form-group btn btn-primary"  id="submit9"><i class="fa fa-save">&nbsp;&nbsp;</i>{!!Lang::get('message.save')!!}</button>

                </div>

                <?php
                $mailchimpStatus = \App\Model\Common\StatusSetting::first()->value('mailchimp_status');
                ?>
                @if($mailchimpStatus ==1)
                <div class="card-body">
                    {!! Form::model($set,['url'=>'mailchimp','method'=>'patch','files'=>true]) !!}

                    <table class="table table-condensed">


                        <tr>

                            <td><b>{!! Form::label('api_key',Lang::get('message.api_key'),['class'=>'required']) !!}</b></td>
                            <td>
                                <div class="form-group {{ $errors->has('api_key') ? 'has-error' : '' }}">


                                    {!! Form::text('api_key', null, ['class' => 'form-control']) !!}
                                    <p><i> {{ Lang::get('message.enter-the-mailchimp-api-key-setting') }}</i></p>


                                </div>
                            </td>

                        </tr>
                        <tr>

                            <td><b>{!! Form::label('list_id',Lang::get('message.list_id'),['class'=>'required']) !!}</b></td>
                            <td>
                                <div class="row">
                                    <div class="col-md-6 form-group {{ $errors->has('list_id') ? 'has-error' : '' }}">
                                        <select name="list_id" class="form-control" </select>
                                        <option value="">Choose</option>
                                        @foreach($allists as $list)
                                            <option value="{{$list->id}}"<?php  if(in_array($list->id, $selectedList) )
                                            { echo "selected";} ?>>{{$list->name}}</option>

                                        @endforeach
                                        <p><i> {{Lang::get('message.enter-the-mailchimp-list-id')}}</i> </p>


                                    </div>
                                </div>
                            </td>

                        </tr>

                        <tr>

                            <td><b>{!! Form::label('subscribe_status',Lang::get('message.subscribe_status'),['class'=>'required']) !!}</b></td>
                            <td>
                                <div class="form-group {{ $errors->has('subscribe_status') ? 'has-error' : '' }}">


                                    {!! Form::select('subscribe_status',['subscribed'=>'Subscribed','unsubscribed'=>'Unsubscribed','cleaned'=>'Cleaned','pending'=>'Pending'],null,['class' => 'form-control']) !!}
                                    <p><i> {{Lang::get('message.enter-the-mailchimp-subscribe-status')}}</i> </p>


                                </div>
                            </td>

                        </tr>

                        @if($set->api_key&&$set->list_id)
                            <tr>

                                <td><b>{!! Form::label('mapping',Lang::get('message.mapping'),['class'=>'required']) !!}</b></td>
                                <td>
                                    <div class="form-group">


                                        <div class="col-md-6">
                                            <a href="{{url('mail-chimp/mapping')}}" class="btn btn-secondary btn-sm">{{Lang::get('message.mapping')}}</a>
                                            <p><i> {{Lang::get('message.map-the-mailchimp-field-with-agora')}}</i> </p>
                                        </div>



                                    </div>
                                </td>

                            </tr>

                        @endif
                        {!! Form::close() !!}

                    </table>


                    <button type="submit" class="btn btn-primary pull-right" id="submit" style="margin-top:-40px;"><i class="fa fa-sync-alt">&nbsp;</i>{!!Lang::get('message.update')!!}</button>

                </div>
                @endif

            </div>
        </div>
    </div>


    <div class="modal fade" id="showTerms" data-backdrop="static" data-keyboard="false">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title">Show Terms on Registration Page</h4>
                </div>
                <div class="modal-body">
                    <input type ="hidden" id="hiddenTermsValue" value="{{$termsUrl}}">
                    <div class= "form-group {{ $errors->has('name') ? 'has-error' : '' }}">
                        {!! Form::label('terms',Lang::get('message.terms_url'),['class'=>'required']) !!}
                        {!! Form::text('terms',$termsUrl,['class' => 'form-control terms_url','id'=>'terms_url']) !!}
                        <h6 id="terms_check"></h6>
                    </div>
                    <div style="display: flex; align-items: center; gap: 10px;">
                        {!! Form::label('terms_settings',Lang::get('message.terms_settings'),['class'=>'required']) !!}

                        <label class="switch toggle_event_editing">

                        <input type="checkbox" value="{{$termsStatus}}"  name="terms_settings"
                               class="checkbox10" id="terms">
                        <span class="slider round"></span>
                    </label>
                    </div>
                </div>

                <div class="modal-footer justify-content-between">
                    <button type="button" id="close" class="btn btn-default pull-left closebutton" data-dismiss="modal"><i class="fa fa-times"></i>&nbsp;Close</button>
{{--                    <button type="submit" class="form-group btn btn-primary"  id="submit9"><i class="fa fa-save">&nbsp;&nbsp;</i>{!!Lang::get('message.save')!!}</button>--}}
                    <button type="submit" class="form-group btn btn-primary"  id="submit10"><i class="fa fa-save">&nbsp;</i>{!!Lang::get('message.save')!!}</button>

                </div>
            </div>
        </div>
    </div>



    <div class="modal fade" id="twitters" data-backdrop="static" data-keyboard="false">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title">Twitter</h4>

                </div>
                <div class="modal-body">
                    <input type ="hidden" id="hidden_consumer_key" value="{{$twitterKeys->twitter_consumer_key}}">
                    <input type ="hidden" id="hidden_consumer_secret" value="{{$twitterKeys->twitter_consumer_secret}}">
                    <input type ="hidden" id="hidden_access_token" value="{{$twitterKeys->twitter_access_token}}">
                    <input type ="hidden" id="hidden_token_secret" value="{{$twitterKeys->access_tooken_secret}}">

                    <div class= "form-group {{ $errors->has('name') ? 'has-error' : '' }}">
                        {!! Form::label('consumer_key',Lang::get('message.consumer_key'),['class'=>'required']) !!}
                        {!! Form::text('consumer_key',$twitterKeys->twitter_consumer_key,['class' => 'form-control consumer_key','id'=>'consumer_key']) !!}
                        <h6 id="consumer_keycheck"></h6>
                    </div>

                    <div class= "form-group {{ $errors->has('name') ? 'has-error' : '' }}">
                        <!-- last name -->
                        {!! Form::label('consumer_secret',Lang::get('message.consumer_secret'),['class'=>'required']) !!}
                        {!! Form::text('consumer_secret',$twitterKeys->twitter_consumer_secret,['class' => 'form-control consumer_secret','id'=>'consumer_secret']) !!}
                        <h6 id="consumer_secretcheck"></h6>
                    </div>

                    <div class= "form-group {{ $errors->has('name') ? 'has-error' : '' }}">
                        {!! Form::label('access_token',Lang::get('message.access_token'),['class'=>'required']) !!}
                        {!! Form::text('access_token',$twitterKeys->twitter_access_token,['class' => 'form-control access_token','id'=>'access_token']) !!}
                        <h6 id="access_tokencheck"></h6>
                    </div>


                    <div class= "form-group {{ $errors->has('name') ? 'has-error' : '' }}">
                        {!! Form::label('token_secret',Lang::get('message.token_secret'),['class'=>'required']) !!}
                        {!! Form::text('token_secret',$twitterKeys->access_tooken_secret,['class' => 'form-control token_secret','id'=>'token_secret']) !!}
                        <h6 id="token_secretcheck"></h6>
                    </div>
                    <div style="display: flex; align-items: center; gap: 10px;">
                        {!! Form::label('twitter_settings',Lang::get('message.twitter_settings'),['class'=>'required']) !!}

                    <label class="switch toggle_event_editing">
                    <input type="checkbox" value="{{$twitterStatus}}"  name="twitter_settings"
                           class="checkbox6" id="twitter">
                    <span class="slider round"></span>
                    </label>
                    </div>
                </div>

                <div class="modal-footer justify-content-between">
                    <button type="button" id="close" class="btn btn-default pull-left closebutton" data-dismiss="modal"><i class="fa fa-times"></i>&nbsp;Close</button>
                    <button type="submit" class="form-group btn btn-primary"  id="submit5"><i class="fa fa-save">&nbsp;&nbsp;</i>{!!Lang::get('message.save')!!}</button>

                </div>
            </div>
        </div>
    </div>


    <div class="modal fade" id="zohoCrm" data-backdrop="static" data-keyboard="false">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title">Zoho CRM</h4>
                </div>
                <div class="modal-body">

                    <input type ="hidden" id="hidden_zoho_key" value="{{$zohoKey}}">

                    <div class= "form-group {{ $errors->has('name') ? 'has-error' : '' }}">
                        {!! Form::label('zoho_key',Lang::get('message.zoho_crm'),['class'=>'required']) !!}
                        {!! Form::text('zoho_key',$zohoKey,['class' => 'form-control zoho_key','id'=>'zoho_key']) !!}
                        <h6 id="zoho_keycheck"></h6>
                    </div>
                    <div style="display: flex; align-items: center; gap: 10px;">
                        {!! Form::label('zoho_settings',Lang::get('message.zoho_settings'),['class'=>'required']) !!}

                    <label class="switch toggle_event_editing">
                        <input type="checkbox" value="{{$zohoStatus}}"  name="zoho_settings"
                           class="checkbox8" id="zoho">
                        <span class="slider round"></span>
                    </label>
                    </div>
                </div>

                <div class="modal-footer justify-content-between">
                    <button type="button" id="close" class="btn btn-default pull-left closebutton" data-dismiss="modal"><i class="fa fa-times"></i>&nbsp;Close</button>
                    <button type="submit" class="form-group btn btn-primary"  id="submit7"><i class="fa fa-save">&nbsp;</i>{!!Lang::get('message.save')!!}</button>

                </div>
            </div>
        </div>
    </div>


    <div class="modal fade" id="pipedrv" data-backdrop="static" data-keyboard="false">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title">pipedrive</h4>
                </div>
                <div class="modal-body">
                    <input type ="hidden" id="hidden_pipedrive_key" value="{{$pipedriveKey}}">
                    <div class= "form-group {{ $errors->has('name') ? 'has-error' : '' }}">
                        {!! Form::label('pipedrive_key',Lang::get('message.pipedrive_key'),['class'=>'required']) !!}
                        {!! Form::text('pipedrive_key',$pipedriveKey,['class' => 'form-control pipedrive_key','id'=>'pipedrive_key']) !!}
                        <h6 id="pipedrive_keycheck"></h6>
                    </div>
                    <div style="display: flex; align-items: center; gap: 10px;">
                        {!! Form::label('pipedrive_settings',Lang::get('message.pipedrive_settings'),['class'=>'required']) !!}

                    <label class="switch toggle_event_editing">
                        <input type="checkbox" value="{{$pipedriveStatus}}"  name="pipedrive_settings"
                           class="checkbox13" id="pipedrive">
                        <span class="slider round"></span>
                    </label>
                    </div>
                </div>

                <div class="modal-footer justify-content-between">
                    <button type="button" id="close" class="btn btn-default pull-left closebutton" data-dismiss="modal"><i class="fa fa-times"></i>&nbsp;Close</button>
                    <button type="submit" class="form-group btn btn-primary"  id="submit13"><i class="fa fa-save">&nbsp;</i>{!!Lang::get('message.save')!!}</button>

                </div>
            </div>
        </div>
    </div>
    {!! Form::close() !!}

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.11.5/js/dataTables.bootstrap4.min.js"></script>

    <script>
        $(document).ready(function () {
            $('#custom-table').DataTable({
                processing: true,
                serverSide: true,
                ajax: "{{ route('datatable.data') }}", // Calls the separate function
                columns: [
                    { data: 'options', name: 'options' },
                    { data: 'status', name: 'status', orderable: false, searchable: false },
                    { data: 'action', name: 'action', orderable: false, searchable: false }
                ]
            });
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
        //License Manager
        $(document).ready(function(){
            var status = $('.checkbox').val();
            licensebox=document.getElementById('License').value;
            if(licensebox ==1) {
                $('#License').prop('checked', true);
                // $('.LicenseField').show();
                // $('.licenseEmptyField').hide();
                // $('#license_api_secret').attr('enabled', true);
                // $('#license_api_url').attr('enabled', true);
                // $('#license_client_secret').attr('enabled',true);
                // $('#license_client_id').attr('enabled',true);
                // $('#license_grant_type').attr('enabled',true);

            } else if(licensebox ==0) {
                // $('.LicenseField').hide();
                // $('.licenseEmptyField').show();
                // $('#license_api_secret').attr('disabled', true);
                // $('#license_api_url').attr('disabled', true);
                // $('#license_client_secret').attr('disabled',true);
                // $('#license_client_id').attr('disabled',true);
                // $('#license_grant_type').attr('disabled',true);

            }
        });
        $('#license_apiCheck').hide();
        $('#License').change(function () {

            if ($(this).prop("checked")) {

                // checked
                // $('#license_api_secret').val();
                // $('#license_api_url').val();
                // $('#license_client_id').val();
                // $('#license_client_secret').val();
                // $('.LicenseField').show();
                // $('.licenseEmptyField').hide();
                // $('#license_api_secret').attr('enabled', true);
                // $('#license_api_url').attr('enabled', true);
                // $('#license_client_secret').attr('enabled',true);
                // $('#license_client_id').attr('enabled',true);
                // $('#license_grant_type').attr('enabled',true)
            }
            else{
                // $('.LicenseField').hide();
                $('.nocapsecretHide').val('');
                $('.siteKeyHide').val('');
                // $('.licenseEmptyField').show();
               //
               // $('.licenseEmptyField').show();
               //  $('#license_api_secret').attr('disabled', true);
               //  $('#license_api_url').attr('disabled', true);
               //  $('#license_client_secret').attr('disabled',true);
               //  $('#license_client_id').attr('disabled',true);
               //  $('#license_grant_type').attr('disabled',true)
               
        }
    });

        function licenseDetails(e){

            if ($('#License').prop("checked")) {
                var checkboxvalue = 1;

                // if ($('#license_api_secret').val() =="" ) {
                //     $('#license_apiCheck').show();
                //     $('#license_apiCheck').html("Please Enter API Secret Key");
                //     $('#license_api_secret').css("border-color","red");
                //     $('#license_apiCheck').css({"color":"red","margin-top":"5px"});
                //     setTimeout(function(){
                //     $('#license_apiCheck').hide();
                //       $('#license_api_secret').css("border-color","");
                //         }, 1500);
                //     return false;
                // }
         
                // if ($('#license_api_url').val() =="" ) {
                //     $('#license_urlCheck').show();
                //     $('#license_urlCheck').html("Please Enter API URL");
                //     $('#license_api_url').css("border-color","red");
                //     $('#license_urlCheck').css({"color":"red","margin-top":"5px"});
                //     setTimeout(function(){
                //     $('#license_urlCheck').hide();
                //       $('#license_api_url').css("border-color","");
                //         }, 1500);
                //     return false;
                // }
                //
                // if ($('#license_client_id').val() =="" ) {
                //     $('#license_clientIdCheck').show();
                //     $('#license_clientIdCheck').html("Please Enter Client Id For License Manager");
                //     $('#license_client_id').css("border-color","red");
                //     $('#license_clientIdCheck').css({"color":"red","margin-top":"5px"});
                //     setTimeout(function(){
                //     $('#license_clientIdCheck').hide();
                //       $('#license_client_id').css("border-color","");
                //         }, 1500);
                //     return false;
                // }
                // if ($('#license_client_secret').val() =="" ) {
                //     $('#license_clientSecretCheck').show();
                //     $('#license_clientSecretCheck').html("Please Enter Your Client Secret For License Manager");
                //     $('#license_client_secret').css("border-color","red");
                //     $('#license_clientSecretCheck').css({"color":"red","margin-top":"5px"});
                //     setTimeout(function(){
                //     $('#license_clientSecretCheck').hide();
                //       $('#license_client_secret').css("border-color","");
                //         }, 1500);
                //     return false;
                // }
                // if ($('#license_grant_type').val() =="" ) {
                //     $('#license_grantTypeCheck').show();
                //     $('#license_grantTypeCheck').html("Please Enter Your Grant Type For License Manager");
                //     $('#license_grant_type').css("border-color","red");
                //     $('#license_grantTypeCheck').css({"color":"red","margin-top":"5px"});
                //     setTimeout(function(){
                //     $('#license_grantTypeCheck').hide();
                //       $('#license_grant_type').css("border-color","");
                //         }, 1500);
                //     return false;
                // }
            }
            else{
                var checkboxvalue = 0;
            }



            const userRequiredFields = {
                name:@json(trans('message.license_api_secret')),
                type:@json(trans('message.license_api_url')),
                group:@json(trans('message.license_client_id')),
                product_sku:@json(trans('message.license_client_secret')),
                description:@json(trans('message.license_grant_type')),
            };

                const userFields = {
                    name:$('#license_api_secret'),
                    type:$('#license_api_url'),
                    group:$('#license_client_id'),
                    product_sku:$('#license_client_secret'),
                    description:$('#license_grant_type'),
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

                // if (isValid && userRequiredFields.description.val()==null) {
                //     isValid = false;
                // }


                // If validation fails, prevent form submission
                if (!isValid) {
                    e.preventDefault();
                }





        $("#submit").html("<i class='fas fa-circle-notch fa-spin'></i>  Please Wait...");
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
                    location.reload();
                    $('#alertMessage').show();
                    var result =  '<div class="alert alert-success alert-dismissable"><button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button><strong><i class="fa fa-check"></i> Success! </strong>'+response.update+'.</div>';
                    $('#alertMessage').html(result+ ".");
                    $("#submit").html("<i class='fa fa-save'>&nbsp;&nbsp;</i>Save");
                    setInterval(function(){
                        $('#alertMessage').slideUp(3000);
                    }, 1000);

                },


            });
        };

        // Function to remove error when input'id' => 'changePasswordForm'ng data
        const removeErrorMessage = (field) => {
            field.classList.remove('is-invalid');
            const error = field.nextElementSibling;
            if (error && error.classList.contains('error')) {
                error.remove();
            }
        };

        ['license_api_secret','license_api_url','license_client_id',
            'license_client_secret','license_grant_type',
            'nocaptcha_secret','nocaptcha_sitekey','mobile_authkey'
            ,'sender','template_id','consumer_key','consumer_secret',
            'access_token','token_secret','git_username',
        'git_password','git_client','git_secret',
        'zoho_key','pipedrive_key','terms_url','mailchimp_authkey'].forEach(id => {

            document.getElementById(id).addEventListener('input', function () {
                removeErrorMessage(this);

            });
        });

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
                    $('#update_apiCheck').html("Please Enter API Secret Key");
                    $('#update_api_secret').css("border-color","red");
                    $('#update_apiCheck').css({"color":"red","margin-top":"5px"});
                    return false;
                }
                if ($('#update_api_url').val() == '' ) {
                    alert('df');
                    $('#update_urlCheck').show();
                    $('#update_urlCheck').html("Please Enter API URL");
                    $('#update_api_url').css("border-color","red");
                    $('#update_urlCheck').css({"color":"red","margin-top":"5px"});
                    return false;
                }

            }
            else{
                var checkboxvalue = 0;
            }
            $("#submitudpate").html("<i class='fas fa-circle-notch fa-spin'></i>  Please Wait...");
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
                    var result =  '<div class="alert alert-success alert-dismissable"><button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button><strong><i class="fa fa-check"></i> Success! </strong>'+response.update+'.</div>';
                    $('#alertMessage').html(result+ ".");
                    $("#submitudpate").html("<i class='fa fa-floppy-o'>&nbsp;&nbsp;</i>Save");
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
                // $('.captchaField').show();
                // $('.captchaEmptyField').hide();
            } else if(status ==0) {
                // $('.captchaField').hide();
                // $('.captchaEmptyField').show();

            }
        });
        // $('#captcha_secretCheck').hide();
        // $('#captcha').change(function () {
        //     if ($(this).prop("checked")) {
        //         // checked
        //         $('#nocaptcha_secret').val();
        //         $('#nocaptcha_sitekey').val();
        //         $('.captchaField').show();
        //         $('.captchaEmptyField').hide();
        //     }
        //     else{
        //         $('.captchaField').hide();
        //         $('.secretHide').val('');
        //         $('.urlHide').val('');
        //         $('.captchaEmptyField').show();
        //
        //
        //     }
        // });

        function captchaDetails(e){
      
            if ($('#captcha').prop("checked")) {
                var checkboxvalue = 1;
                // if ($('#nocaptcha_secret').val() =="" ) {
                //     $('#captcha_secretCheck').show();
                //     $('#captcha_secretCheck').html("Please Enter Secret Key");
                //     $('#captcha_secret').css("border-color","red");
                //     $('#captcha_secretCheck').css({"color":"red","margin-top":"5px"});
                //     return false;
                // }
                // if ($('#nocaptcha_sitekey').val() =="" ) {
                //     $('#captcha_sitekeyCheck').show();
                //     $('#captcha_sitekeyCheck').html("Please Enter Sitekey");
                //     $('#nocaptcha_sitekey').css("border-color","red");
                //     $('#captcha_sitekeyCheck').css({"color":"red","margin-top":"5px"});
                //     return false;
                // }

            }
            else{
                var checkboxvalue = 0;
            }

            const userRequiredFields = {
                name:@json(trans('message.site_key')),
                type:@json(trans('message.secret_key')),
            };

            const userFields = {
                name:$('#nocaptcha_secret'),
                type:$('#nocaptcha_sitekey'),
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

            // if (isValid && userRequiredFields.description.val()==null) {
            //     isValid = false;
            // }


            // If validation fails, prevent form submission
            if (!isValid) {
                e.preventDefault();
            }



            $("#submit2").html("<i class='fas fa-circle-notch fa-spin'></i>  Please Wait...");
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
                    var result =  '<div class="alert alert-success alert-dismissable"><button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button><strong><i class="fa fa-check"></i> Success! </strong>'+data.update+'.</div>';
                    $('#alertMessage').html(result+ ".");
                    $("#submit2").html("<i class='fa fa-save'>&nbsp;&nbsp;</i>Save");
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
                    $('#v3captcha_secretCheck').html("Please Enter Secret Key");
                    $('#captcha_secret').css("border-color","red");
                    $('#v3captcha_secretCheck').css({"color":"red","margin-top":"5px"});
                    return false;
                }
                if ($('#captcha_sitekey').val() =="" ) {
                    $('#captcha_sitekeyCheck').show();
                    $('#captcha_sitekeyCheck').html("Please Enter Sitekey");
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
                    var result =  '<div class="alert alert-success alert-dismissable"><button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button><strong><i class="fa fa-check"></i> Success! </strong>'+data.update+'.</div>';
                    $('#alertMessage').html(result+ ".");
                    $("#submit3").html("<i class='fa fa-save'>&nbsp;&nbsp;</i>Save");
                    setInterval(function(){
                        $('#alertMessage').slideUp(3000);
                    }, 1000);
                },

            });
        };

 <!--------------------------------------------------------------------------------------------->
        /*
       *MSG 91
        */
        $(document).ready(function (){
            var mobilestatus =  $('.checkbox4').val();
            if(mobilestatus ==1)
            {
                $('#mobile').prop('checked',true);
                // $('.mobile_authkey').attr('enabled', true);
                // $('.sender').attr('enabled', true);
                // $('.template_id').attr('enabled',true)
            } else if(mobilestatus ==0){
                $('#mobile').prop('checked',false);
                // $('.mobile_authkey').attr('disabled', true);
                // $('.sender').attr('disabled', true);
                // $('.template_id').attr('disabled', true)
            }
        });
        $("#mobile").on('change',function (){
            if($(this).prop('checked')) {
                var mobilekey =  $('#hiddenMobValue').val();
                var sender =  $('#hiddenSender').val();
                var template =  $('#hiddenTemplate').val();
                // $('.mobile_authkey').attr('disabled', false);
                // $('.sender').attr('disabled', false);
                // // $('.template_id').attr('disabled',false)
                // $('#mobile_authkey').val(mobilekey);
                // $('#sender').val(sender);
                // $('#template_id').val(template);


            } else {
                // $('.mobile_authkey').attr('disabled', true);
                // $('.sender').attr('disabled', true);
                // $('.template_id').attr('disabled',true)
                // $('.mobile_authkey').val('');
                // $('.sender').val('');
                // $('.template_id').val('');

            }
        });
        //Validate and pass value through ajax
        $("#submit3").on('click', function () {
            if ($('#mobile').prop('checked')) { // If checkbox is checked
                var mobilestatus = 1;

                // // Validate Auth Key
                // if ($('#mobile_authkey').val() === "") {
                //     $('#mobile_check').show().text("Please Enter Auth Key").css({ "color": "red", "margin-top": "5px" });
                //     $('#mobile_authkey').addClass('error-border');
                //     return false;
                // } else {
                //     $('#mobile_check').hide();
                //     $('#mobile_authkey').removeClass('error-border');
                // }

                // // Validate Sender
                // if ($('#sender').val() !== "") {
                //     const senderRegex = /^[a-zA-Z]{0,6}$/;
                //     if (senderRegex.test($('#sender').val())) {
                //         $('#sender_check').hide();
                //         $('#sender').removeClass('error-border');
                //     } else {
                //         $('#sender_check').show().text("Sender can only be alphabets and maximum 6 characters").css({ "color": "red", "margin-top": "5px" });
                //         $('#sender').addClass('error-border');
                //         return false;
                //     }
                // }

                // // Validate Template ID
                // if ($('#template_id').val() === "") {
                //     $('#template_id').addClass('error-border');
                //     $('#template_check').show().text("Please Enter Template ID").css({ "color": "red", "margin-top": "5px" });
                //     return false;
                // } else {
                //     $('#template_id').removeClass('error-border');
                //     $('#template_check').hide();
                // }
            } else {
                // // Reset fields when mobile is unchecked
                // $('#mobile_authkey, #sender, #template_id').removeClass('error-border');
                // $('#mobile_check, #sender_check, #template_check').hide().text("");
                mobilestatus = 0;
            }



            const userRequiredFields = {
                name:@json(trans('message.mobile_authkey')),
                type:@json(trans('message.sender')),
                template:@json(trans('message.templateId')),

            };

            const userFields = {
                name:$('#mobile_authkey'),
                type:$('#sender'),
                template:$('#template_id')
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

            // if (isValid && userRequiredFields.description.val()==null) {
            //     isValid = false;
            // }


            // If validation fails, prevent form submission
            if (!isValid) {
               e.preventDefault();
            }




            // Show loading state
            $("#submit3").html("<i class='fas fa-circle-notch fa-spin'></i> Please Wait...");

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
                    <strong><i class="fa fa-check"></i> Success! </strong>${data.update}.
                </div>`;
                    $('#alertMessage').show().html(result);
                    $("#submit3").html("<i class='fa fa-save'>&nbsp;&nbsp;</i>Save");


                    setInterval(function(){
                        $('#alertMessage').slideUp(3000);
                    }, 1000);
                },
                error: function () {
                    $('#alertMessage').html("<div class='alert alert-danger'>An error occurred. Please try again.</div>").show();
                    $("#submit3").html("<i class='fa fa-save'>&nbsp;&nbsp;</i>Save");
                }
            });
        });




        <!------------------------------------------------------------------------------------------------------------------->
        /*
         * Email Status Setting
         */
        // $(document).ready(function (){
        //     var emailstatus =  $('.checkbox5').val();
        //     if(emailstatus ==1)
        //     {
        //         $('#email').prop('checked',true);
        //     } else if(emailstatus ==0){
        //         $('#email').prop('checked',false);
        //     }
        // });
        //Validate and pass value through ajax
        $("#submit4").on('click',function (){ //When Submit button is checked
            if ($('#email').prop('checked')) {//if button is on
                var emailstatus = 1;
            } else {
                var emailstatus = 0;
            }
            $("#submit4").html("<i class='fas fa-circle-notch fa-spin'></i>  Please Wait...");
            $.ajax ({
                url: '{{url("updateemailDetails")}}',
                type : 'post',
                data: {
                    "status": emailstatus,
                },
                success: function (data) {
                    $('#alertMessage').show();
                    var result =  '<div class="alert alert-success alert-dismissable"><button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button><strong><i class="fa fa-check"></i> Success! </strong>'+data.update+'.</div>';
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
                // $('#consumer_key').attr('enabled', true);
                // $('#consumer_secret').attr('enabled', true);
                // $('#access_token').attr('enabled', true);
                // $('#token_secret').attr('enabled', true);

            } else if(twitterstatus ==0){
                $('#twitter').prop('checked',false);
                // $('.consumer_key').attr('disabled', true);
                // $('.consumer_secret').attr('disabled', true);
                // $('.access_token').attr('disabled', true);
                // $('.token_secret').attr('disabled', true);
            }
        });

        // $("#twitter").on('change',function (){
        //     if($(this).prop('checked')) {
        //         var consumer_key =  $('#hidden_consumer_key').val();
        //         var consumer_secret =  $('#hidden_consumer_secret').val();
        //         var access_token =  $('#hidden_access_token').val();
        //         var token_secret =  $('#hidden_token_secret').val();
        //         $('.consumer_key').attr('disabled', false);
        //         $('.consumer_secret').attr('disabled', false);
        //         $('.access_token').attr('disabled', false);
        //         $('.token_secret').attr('disabled', false);
        //         $('#consumer_key').val(consumer_key);
        //         $('#consumer_secret').val(consumer_secret);
        //         $('#access_token').val(access_token);
        //         $('#token_secret').val(token_secret);
        //
        //     } else {
        //         $('.consumer_key').attr('disabled', true);
        //         $('.consumer_secret').attr('disabled', true);
        //         $('.access_token').attr('disabled', true);
        //         $('.token_secret').attr('disabled', true);
        //         $('#consumer_key').val('');
        //         $('#consumer_secret').val('');
        //         $('#access_token').val('');
        //         $('#token_secret').val('');
        //
        //
        //     }
        // });

        //Validate and pass value through ajax
        $("#submit5").on('click',function (){ //When Submit button is clicked
            if ($('#twitter').prop('checked')) {//if button is on
                var twitterstatus = 1;
                // if ($('#consumer_key').val() == "") { //if value is not entered
                //     $('#consumer_keycheck').show();
                //     $('#consumer_keycheck').html("Please Enter Twitter Consumer Key");
                //     $('#consumer_key').css("border-color","red");
                //     $('#consumer_keycheck').css({"color":"red","margin-top":"5px"});
                //     return false;
                // } else if ($('#consumer_secret').val() == "") {
                //     $('#consumer_secretcheck').show();
                //     $('#consumer_secretcheck').html("Please Enter Twitter Consumer Secret");
                //     $('#consumer_secret').css("border-color","red");
                //     $('#consumer_secretcheck').css({"color":"red","margin-top":"5px"});
                //     return false;
                // } else if ($('#access_token').val() == "") {
                //     $('#access_tokencheck').show();
                //     $('#access_tokencheck').html("Please Enter Twitter Access Token");
                //     $('#access_token').css("border-color","red");
                //     $('#access_tokencheck').css({"color":"red","margin-top":"5px"});
                //     return false;
                // } else if ($('#token_secret').val() == "") {
                //     $('#token_secretcheck').show();
                //     $('#token_secretcheck').html("Please Enter Twitter Token Secret");
                //     $('#token_secret').css("border-color","red");
                //     $('#token_secretcheck').css({"color":"red","margin-top":"5px"});
                //     return false;
                // }
            } else {
                // $('#consumer_keycheck').html("");
                // $('#consumer_key').css("border-color","");
                // $('#consumer_secretcheck').html("");
                // $('#consumer_secret').css("border-color","");
                // $('#access_tokencheck').html("");
                // $('#access_token').css("border-color","");
                // $('#token_secretcheck').html("");
                // $('#token_secret').css("border-color","");
                var twitterstatus = 0;
            }




            const userRequiredFields = {
                name:@json(trans('message.consumer_key_s')),
                type:@json(trans('message.consumer_secret_s')),
                template:@json(trans('message.access_token_s')),
                token:@json(trans('message.token_secret_s')),
            };

            const userFields = {
                name:$('#consumer_key'),
                type:$('#consumer_secret'),
                template:$('#access_token'),
                token:$('#token_secret'),

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

            // if (isValid && userRequiredFields.description.val()==null) {
            //     isValid = false;
            // }


            // If validation fails, prevent form submission
            if (!isValid) {
                preventDefault();
            }




            $("#submit5").html("<i class='fas fa-circle-notch fa-spin'></i>  Please Wait...");
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
                    var result =  '<div class="alert alert-success alert-dismissable"><button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button><strong><i class="fa fa-check"></i> Success! </strong>'+data.update+'.</div>';
                    $('#alertMessage').html(result+ ".");
                    $("#submit5").html("<i class='fa fa-save'>&nbsp;</i>Save");
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
                // $('.zoho_key').attr('enabled', true);
            } else if(zohostatus ==0){
                $('#zoho').prop('checked',false);
                // $('.zoho_key').attr('disabled', true);
            }
        });
        // $("#zoho").on('change',function (){
        //     if($(this).prop('checked')) {
        //         var zohokey =  $('#hidden_zoho_key').val();
        //         $('.zoho_key').attr('disabled', false);
        //         $('#zoho_key').val(zohokey);
        //
        //     } else {
        //         $('.zoho_key').attr('disabled', true);
        //         $('.zoho_key').val('');
        //
        //
        //     }
        // });
        //Validate and pass value through ajax
        $("#submit7").on('click',function (){ //When Submit button is checked
            if ($('#zoho').prop('checked')) {//if button is on
                var zohostatus = 1;
                // if ($('#zoho_key').val() == "") { //if value is not entered
                //     $('#zoho_keycheck').show();
                //     $('#zoho_keycheck').html("Please Enter Zoho Key");
                //     $('#zoho_key').css("border-color","red");
                //     $('#zoho_keycheck').css({"color":"red","margin-top":"5px"});
                //     return false;
                // }
            } else {
                // $('#zoho_keycheck').html("");
                // $('#zoho_key').css("border-color","");
                var zohostatus = 0;

            }

            const userRequiredFields = {
                name:@json(trans('message.zoho_key_s')),

            };

            const userFields = {
                name:$('#zoho_key'),
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

            // if (isValid && userRequiredFields.description.val()==null) {
            //     isValid = false;
            // }


            // If validation fails, prevent form submission
            if (!isValid) {
                preventDefault();
            }




            $("#submit7").html("<i class='fas fa-circle-notch fa-spin'></i>  Please Wait...");
            $.ajax ({
                url: '{{url("updatezohoDetails")}}',
                type : 'post',
                data: {
                    "status": zohostatus,
                    "zoho_key": $('#zoho_key').val(),
                },
                success: function (data) {
                    $('#alertMessage').show();
                    var result =  '<div class="alert alert-success alert-dismissable"><button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button><strong><i class="fa fa-check"></i> Success! </strong>'+data.update+'.</div>';
                    $('#alertMessage').html(result+ ".");
                    $("#submit7").html("<i class='fa fa-save'>&nbsp;&nbsp;</i>Save");
                    setInterval(function(){
                        $('#alertMessage').slideUp(3000);
                    }, 1000);
                },
            })
        });
 <!--------------------------------------------------------------------------------------------->
        /*
       *Mailchimp
        */
        $(document).ready(function (){
            var mailchimpstatus =  $('.checkbox9').val();
            if(mailchimpstatus ==1)
            {
                $('#mailchimp').prop('checked',true);
                // $('.mailchimp_authkey').attr('enabled', true);
            } else if(mailchimpstatus ==0){
                $('#mailchimp').prop('checked',false);
                // $('.mailchimp_authkey').attr('disabled', true);
            }
        });
        // $("#mailchimp").on('change',function (){
        //     if($(this).prop('checked')) {
        //         var mailchimpkey =  $('#hiddenMailChimpValue').val();
        //         $('.mailchimp_authkey').attr('disabled', false);
        //         $('#mailchimp_authkey').val(mailchimpkey);
        //
        //     } else {
        //         // $('.mailchimp_authkey').attr('disabled', true);
        //         // $('.mailchimp_authkey').val('');
        //
        //
        //     }
        // });
        //Validate and pass value through ajax
        $("#submit9").on('click',function (){ //When Submit button is checked
            if ($('#mailchimp').prop('checked')) {//if button is on
                var chimpstatus = 1;
                // if ($('#mailchimp_authkey').val() == "") { //if value is not entered
                //     $('#mailchimp_check').show();
                //     $('#mailchimp_check').html("Please Enter Mailchimp Api Key");
                //     $('#mailchimp_authkey').css("border-color","red");
                //     $('#mailchimp_check').css({"color":"red","margin-top":"5px"});
                //     return false;
                // }
            } else {
            //     $('#mailchimp_check').html("");
            //     $('#mailchimp_authkey').css("border-color","");
                var chimpstatus = 0;

            }


            const userRequiredFields = {
                name:@json(trans('message.mailchimp_authkey_s')),
            };

            const userFields = {
                name:$('#mailchimp_authkey'),

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

            // if (isValid && userRequiredFields.description.val()==null) {
            //     isValid = false;
            // }


            // If validation fails, prevent form submission
            if (!isValid) {
                preventDefault();
            }



            $("#submit9").html("<i class='fas fa-circle-notch fa-spin'></i>  Please Wait...");
            $.ajax ({
                url: '{{url("updateMailchimpDetails")}}',
                type : 'post',
                data: {
                    "status": chimpstatus,
                    "mailchimp_auth_key": $('#mailchimp_authkey').val(),
                },
                success: function (data) {
                    console.log(data['message'])
                    if(data['message']==='success'){
                    $('#alertMessage').show();
                    var result =  '<div class="alert alert-success alert-dismissable"><button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button><strong><i class="fa fa-check"></i> Success! </strong>'+data.update+'.</div>';
                    $('#alertMessage').html(result+ ".");
                    $("#submit9").html("<i class='fa fa-save'>&nbsp;</i>Save");
                    setInterval(function(){
                        $('#alertMessage').slideUp(3000);
                    }, 1000);}else{
                        $('#alertMessage').show();
                        var result =  '<div class="alert alert-danger alert-dismissable"><button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button><strong><i class="fa fa-check"></i> error! </strong>'+data.update+'.</div>';
                        $('#alertMessage').html(result+ ".");
                        $("#submit9").html("<i class='fa fa-save'>&nbsp;</i>Save");
                        setInterval(function(){
                            $('#alertMessage').slideUp(3000);
                        }, 1000);
                    }
                },
            })
        });

   <!--------------------------------------------------------------------------------------------->
        /*
       *Terms
        */
        $(document).ready(function (){
            var termsstatus =  $('.checkbox10').val();
            if(termsstatus ==1)
            {
                $('#terms').prop('checked',true);
                // $('.terms_url').attr('enabled', true);
            } else if(termsstatus ==0){
                $('#terms').prop('checked',false);
                // $('.terms_url').attr('disabled', true);
            }
        });
        // $("#terms").on('change',function (){
        //     if($(this).prop('checked')) {
        //         var terms =  $('#hiddenTermsValue').val();
        //         // $('.terms_url').attr('disabled', false);
        //         $('#terms_url').val(terms);
        //
        //     } else {
        //         $('.terms_url').attr('disabled', true);
        //         $('.terms_url').val('');
        //
        //
        //     }
        // });
        //Validate and pass value through ajax
        $("#submit10").on('click',function (){ //When Submit button is checked
            if ($('#terms').prop('checked')) {//if button is on
                var termsstatus = 1;
                // if ($('#terms_url').val() == "") { //if value is not entered
                //     $('#terms_check').show();
                //     $('#terms_check').html("Please Enter Terms Url");
                //     $('#terms_url').css("border-color","red");
                //     $('#terms_check').css({"color":"red","margin-top":"5px"});
                //     return false;
                // }
            } else {
                // $('#terms_check').html("");
                // $('#terms_url').css("border-color","");
                var termsstatus = 0;

            }



            const userRequiredFields = {
                name:@json(trans('message.terms_url_s')),

            };

            const userFields = {
                name:$('#terms_url'),

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

            // if (isValid && userRequiredFields.description.val()==null) {
            //     isValid = false;
            // }


            // If validation fails, prevent form submission
            if (!isValid) {
                preventDefault();
            }




            $("#submit10").html("<i class='fas fa-circle-notch fa-spin'></i>  Please Wait...");
            $.ajax ({
                url: '{{url("updateTermsDetails")}}',
                type : 'post',
                data: {
                    "status": termsstatus,
                    "terms_url": $('#terms_url').val(),
                },
                success: function (data) {
                    $('#alertMessage').show();
                    var result =  '<div class="alert alert-success alert-dismissable"><button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button><strong><i class="fa fa-check"></i> Success! </strong>'+data.update+'.</div>';
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
                // $('.pipedrive_key').attr('enabled', true);
            } else if(pipedrivestatus ==0){
                $('#pipedrive').prop('checked',false);
                // $('.pipedrive_key').attr('disabled', true);
            }
        });
        // $("#pipedrive").on('change',function (){
        //     if($(this).prop('checked')) {
        //         var pipedrivekey =  $('#hidden_pipedrive_key').val();
        //         $('.pipedrive_key').attr('disabled', false);
        //         $('#pipedrive_key').val(pipedrivekey);
        //
        //     } else {
        //         $('.pipedrive_key').attr('disabled', true);
        //         $('.pipedrive_key').val('');
        //
        //
        //     }
        // });
        //Validate and pass value through ajax
        $("#submit13").on('click',function (){ //When Submit button is checked
            if ($('#pipedrive').prop('checked')) {//if button is on
                var pipedrivestatus = 1;
                // if ($('#pipedrive_key').val() == "") { //if value is not entered
                //     $('#pipedrive_keycheck').show();
                //     $('#pipedrive_keycheck').html("Please Enter Pipedrive API Key");
                //     $('#pipedrive_key').css("border-color","red");
                //     $('#pipedrive_keycheck').css({"color":"red","margin-top":"5px"});
                //     return false;
                // }
            } else {
                // $('#pipedrive_keycheck').html("");
                // $('#pipedrive_key').css("border-color","");
                var pipedrivestatus = 0;

            }


            const userRequiredFields = {
                name:@json(trans('message.pipedrive_key_s')),

            };

            const userFields = {
                name:$('#pipedrive_key'),
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

            // if (isValid && userRequiredFields.description.val()==null) {
            //     isValid = false;
            // }


            // If validation fails, prevent form submission
            if (!isValid) {
                preventDefault();
            }




            $("#submit13").html("<i class='fas fa-circle-notch fa-spin'></i>  Please Wait...");
            $.ajax ({
                url: '{{url("updatepipedriveDetails")}}',
                type : 'post',
                data: {
                    "status": pipedrivestatus,
                    "pipedrive_key": $('#pipedrive_key').val(),
                },
                success: function (data) {
                    $('#alertMessage').show();
                    var result =  '<div class="alert alert-success alert-dismissable"><button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button><strong><i class="fa fa-check"></i> Success! </strong>'+data.update+'.</div>';
                    $('#alertMessage').html(result+ ".");
                    $("#submit13").html("<i class='fa fa-save'>&nbsp;&nbsp;</i>Save");
                    setInterval(function(){
                        $('#alertMessage').slideUp(3000);
                    }, 1000);
                },
            })
        });
 <!--------------------------------------------------------------------------------------------->

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
            $("#submit14").html("<i class='fas fa-circle-notch fa-spin'></i>  Please Wait...");
            $.ajax ({
                url: '{{url("updatedomainCheckDetails")}}',
                type : 'post',
                data: {
                    "status": domainstatus,
                },
                success: function (data) {
                    $('#alertMessage').show();
                    var result =  '<div class="alert alert-success alert-dismissable"><button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button><strong><i class="fa fa-check"></i> Success! </strong>'+data.update+'.</div>';
                    $('#alertMessage').html(result+ ".");
                    $("#submit14").html("<i class='fa fa-save'>&nbsp;</i>Save");
                    setInterval(function(){
                        $('#alertMessage').slideUp(3000);
                    }, 1000);
                },
            });
        });

    </script>

    <script>
        $(document).ready(function (){
            var githubstatus =  $('.checkbox').val();
            if(githubstatus ==1)
            {
                $('#github').prop('checked',true);
                // $('#git_username').attr('enabled', true);
                // $('#git_password').attr('enabled', true);
                // $('#git_client').attr('enabled', true);
                // $('#git_secret').attr('enabled', true);

            } else if(githubstatus ==0){
                $('#github').prop('checked',false);
                // $('.git_username').attr('disabled', true);
                // $('.git_password').attr('disabled', true);
                // $('.git_client').attr('disabled', true);
                // $('.git_secret').attr('disabled', true);
            }
        });

        // $("#github").on('change',function (){
        //     if($(this).prop('checked')) {
        //         var username =  $('#hidden_git_username').val();
        //         var password =  $('#hidden_git_password').val();
        //         var client =  $('#hidden_git_client').val();
        //         var secret =  $('#hidden_git_secret').val();
        //         $('.git_username').attr('disabled', false);
        //         $('.git_password').attr('disabled', false);
        //         $('.git_client').attr('disabled', false);
        //         $('.git_secret').attr('disabled', false);
        //         $('#git_username').val(username);
        //         $('#git_password').val(password);
        //         $('#git_client').val(client);
        //         $('#git_secret').val(secret);
        //
        //     } else {
        //         $('.git_username').attr('disabled', true);
        //         $('.git_password').attr('disabled', true);
        //         $('.git_client').attr('disabled', true);
        //         $('.git_secret').attr('disabled', true);
        //         // $('#git_username').val('');
        //         // $('#git_password').val('');
        //         // $('#git_client').val('');
        //         // $('#git_secret').val('');
        //     }
        // });

        //Validate and pass value through ajax
        $("#submit").on('click',function (){ //When Submit button is clicked
            if ($('#github').prop('checked')) {//if button is on
                var githubstatus = 1;
                // if ($('#git_username').val() == "") { //if value is not entered
                //     $('#user').show();
                //     $('#user').html("Please Enter github Username");
                //     $('#git_username').css("border-color","red");
                //     $('#user').css({"color":"red","margin-top":"5px"});
                //     return false;
                // } else if ($('#git_password').val() == "") {
                //     $('#pass').show();
                //     $('#pass').html("Please Enter Github Password");
                //     $('#git_password').css("border-color","red");
                //     $('#pass').css({"color":"red","margin-top":"5px"});
                //     return false;
                // } else if ($('#git_client').val() == "") {
                //     $('#c_id').show();
                //     $('#c_id').html("Please Enter Client Id");
                //     $('#git_client').css("border-color","red");
                //     $('#c_id').css({"color":"red","margin-top":"5px"});
                //     return false;
                // } else if ($('#git_secret').val() == "") {
                //     $('#c_secret').show();
                //     $('#c_secret').html("Please Enter Client Secret Key");
                //     $('#git_secret').css("border-color","red");
                //     $('#c_secret').css({"color":"red","margin-top":"5px"});
                //     return false;
                // }
            } else {
                // $('#user').html("");
                // $('#git_username').css("border-color","");
                // $('#pass').html("");
                // $('#git_password').css("border-color","");
                // $('#c_id').html("");
                // $('#git_client').css("border-color","");
                // $('#c_secret').html("");
                // $('#git_secret').css("border-color","");
                var githubstatus = 0;
            }



            const userRequiredFields = {
                name:@json(trans('message.git_username_s')),
                type:@json(trans('message.git_password_s')),
                template:@json(trans('message.git_client_s')),
                token:@json(trans('message.git_secret_s')),
            };

            const userFields = {
                name:$('#git_username'),
                type:$('#git_password'),
                template:$('#git_client'),
                token:$('#git_secret'),

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

            // if (isValid && userRequiredFields.description.val()==null) {
            //     isValid = false;
            // }


            // If validation fails, prevent form submission
            if (!isValid) {
                preventDefault();
            }





            $("#submit").html("<i class='fas fa-circle-notch fa-spin'></i>  Please Wait...");
            $.ajax ({
                url: '{{url("github-setting")}}',
                type : 'post',
                data: {
                    "status": githubstatus,
                    "git_username": $('#git_username').val(),"git_password" : $('#git_password').val() ,
                    "git_client":$('#git_client').val() ,  "git_secret" : $('#git_secret').val()
                },
                success: function (data) {
                    $('#alertMessage').show();
                    var result =  '<div class="alert alert-success alert-dismissable"><button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button><strong><i class="fa fa-check"></i> Success! </strong>'+data.update+'.</div>';
                    $('#alertMessage').html(result+ ".");
                    $("#submit").html("<i class='fa fa-sync-alt'>&nbsp;</i>Update");
                    setInterval(function(){
                        $('#alertMessage').slideUp(3000);
                    }, 1000);
                },
            })
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
@stop