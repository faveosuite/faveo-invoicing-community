@extends('themes.default1.layouts.master')
@section('title')
    {{ __('message.third_party_integrations') }}
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
/*.scrollit {*/
/*    overflow:scroll;*/
/*    height:600px;*/
/*}*/
        .error-border {
            border-color: red;
        }


</style>
<div class="col-sm-6 md-6">
    <h1>{{ __('message.third_party_integrations') }}</h1>
</div>
<div class="col-sm-6 md-6">
    <ol class="breadcrumb float-sm-right">
        <li class="breadcrumb-item"><a href="{{url('/')}}"><i class="fa fa-dashboard"></i> {{ __('message.home')}}</a></li>
        <li class="breadcrumb-item"><a href="{{url('settings')}}"> {{ __('message.settings')}}</a></li>
        <li class="breadcrumb-item active">{{ __('message.third_party_integrations') }}</li>
    </ol>
</div><!-- /.col -->
@stop
@section('content')


    <div class="card card-secondary card-outline" >

        <!-- /.box-header -->
        <div class="card-body">
            <div id="alertMessage12"></div>
{{--            <div class="scrollit" style="height:1000px">--}}
                <div class="row" style="height:960px">
                    <div class="col-md-12">
                        <table id="custom-table" class="table display" cellspacing="0" width="100%">
                            <thead>
                            <tr>
                                <th>{{ __('message.name_page') }}</th>
                                <th>{{ __('message.description') }}</th>
                                <th>{{ __('message.status') }}</th>
                                <th>{{ __('message.action') }}</th>
                            </tr>
                            </thead>
                        </table>
                 </div>
                </div>
            </div>
        </div>
        <!-- /.box-body -->
{{--    </div>--}}
    <div class="modal fade" id="msg-91" data-backdrop="static" data-keyboard="false">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title">{{ __('message.msg91_heading') }}</h4>

                </div>
                <div class="modal-body">
                    <div id="alertMessage3"></div>
                    <input type ="hidden" id="hiddenMobValue" value="{{$mobileauthkey}}">

                    <div class= "form-group {{ $errors->has('name') ? 'has-error' : '' }}">
                        {!! html()->label(__('message.msg91_key'), 'msg91_auth_key')->class('required') !!}
                        {!! html()->text('msg91_auth_key', $mobileauthkey)->class('form-control mobile_authkey')->id('mobile_authkey') !!}

                        <h6 id="mobile_check"></h6>
                    </div>

                    <input type ="hidden" id="hiddenSender" value="{{$msg91Sender}}">

                    <div class= "form-group {{ $errors->has('name') ? 'has-error' : '' }}">
                        {!! html()->label(__('message.msg91_sender'), 'msg91_sender')->class('required') !!}
                        {!! html()->text('msg91_sender', $msg91Sender)->class('form-control sender')->id('sender') !!}
                        <h6 id="sender_check"></h6>
                    </div>

                    <input type ="hidden" id="hiddenTemplate" value="{{$msg91TemplateId}}">

                    <div class= "form-group {{ $errors->has('name') ? 'has-error' : '' }}">
                        {!! html()->label(__('message.msg91_template_id'), 'msg91_template_id')->class('required') !!}
                        {!! html()->text('msg91_template_id', $msg91TemplateId)->class('form-control template_id')->id('template_id') !!}
                        <h6 id="template_check"></h6>
                    </div>

                    @php
                        $thirdPartyKeys = \App\ThirdPartyApp::all()->pluck('app_name', 'id');
                        $selectedApp = \App\ThirdPartyApp::find($msg91ThirdPartyId);
                        $appKey = $selectedApp?->app_key ?? '';
                        $appSecret = $selectedApp?->app_secret ?? '';
                    @endphp

                    {{-- Third Party App Selector --}}
                    {{ html()->label( __('message.msg91_third_party_app_key'), 'third_party_key')->class('required') }}
                    {{ html()->select('third_party_key',
                                         ['' => __('message.select_third_party_app')] + $thirdPartyKeys->toArray(),
                                         $msg91ThirdPartyId)
                                         ->class('form-control')
                                         ->id('third_party_key') }}
                    <h6 id="third_party_check"></h6>
                    <br/>

                    {{-- Webhook Field (Initially Hidden) --}}
                    <div id="webhook_section" style="display: none;">
                        {{ html()->label( __('message.webhook_url'), 'webhook_url') }}
                        <div class="input-group">
                            {{ html()->text('webhook_url')
                                ->attribute('readonly')
                                ->class('form-control')
                                ->id('webhook_url') }}
                            <div class="input-group-append" data-toggle="tooltip" data-placement="top" title="{{ __('message.copy_to_clipboard') }}" id="copy_tooltip_div">
                                <button type="button" class="btn btn-secondary" id="copy_button">
                                    <i class="fas fa-copy"></i>
                                </button>
                            </div>
                        </div>
                    </div>

                    </div>

                <div class="modal-footer justify-content-between">
                    <button type="button" id="close" class="btn btn-default pull-left closebutton" data-dismiss="modal"><i class="fa fa-times"></i>&nbsp;{{ __('message.close') }}</button>
                    <button type="submit" class="form-group btn btn-primary"  id="submit3"><i class="fa fa-save">&nbsp;&nbsp;</i>{!!Lang::get('message.save')!!}</button>

                </div>
            </div>
        </div>
    </div>


    <div class="modal fade" id="githubSet" data-backdrop="static" data-keyboard="false">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title">{{ __('message.github_settings') }}</h4>

                </div>
                <div class="modal-body">
                    <div id="alertMessage1"></div>
                    <div class= "form-group {{ $errors->has('name') ? 'has-error' : '' }}">
                        {!! html()->label(Lang::get('message.username'))->class('required')->for('git_username') !!}
                        {!! html()->text('username')->class('form-control git_username'. ($errors->has('username') ? ' is-invalid' : ''))->id('git_username') !!}
                        <h6 id="user"></h6>
                    </div>

                    <div class= "form-group {{ $errors->has('name') ? 'has-error' : '' }}">
                        <!-- last name -->
                        {!! html()->label(Lang::get('message.pat'))->class('required')->for('password') !!}

                        <div class="input-group">
                            <input type= "password" name="password" id="git_password" class="form-control git_password">

                            <div class="input-group-append">
                            <span role="button" class="input-group-text" onclick="togglePasswordVisibility(this)">
                                <i class="fa fa-eye-slash"></i>
                            </span>
                            </div>
                        </div>
                        <h6 id="pass"></h6>

                    </div>

                    <div class= "form-group {{ $errors->has('name') ? 'has-error' : '' }}" style="display:none">
                        {!! html()->label(Lang::get('message.client_id'))->class('required')->for('client_id') !!}
                        {!! html()->text('client_id')->class('form-control git_client'. ($errors->has('client_id') ? ' is-invalid' : ''))->id('git_client') !!}
                        <h6 id="c_id"></h6>
                    </div>

                    <div class= "form-group {{ $errors->has('name') ? 'has-error' : '' }}" style="display:none" >

                        {!! html()->label(Lang::get('message.client_secret'))->class('required')->for('client_secret') !!}
                        <div class="input-group">
                            <input type= "password" name="client_secret" id="git_secret" class="form-control git_secret">

                            <div class="input-group-append">
                            <span role="button" class="input-group-text" onclick="togglePasswordVisibility(this)">
                                <i class="fa fa-eye-slash"></i>
                            </span>
                        </div>
                        </div>
                        <h6 id="c_secret"></h6>
                    </div>

                </div>

                <div class="modal-footer justify-content-between">
                    <button type="button" id="close" class="btn btn-default pull-left closebutton" data-dismiss="modal"><i class="fa fa-times"></i>&nbsp;{{ __('message.close') }}</button>
                    <button type="submit" id="submit" class="btn btn-primary pull-right" id="submit" data-loading-text="<i class='fa fa-save'>&nbsp;</i> {{ __('message.saving') }}"><i class="fa fa-save">&nbsp;</i>{!!Lang::get('message.save')!!}</button>

                </div>
            </div>
        </div>
    </div>


    <div class="modal fade" id="create-third-party-app" data-backdrop="static" data-keyboard="false">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title">{{ __('message.license_heading') }}</h4>

                </div>
                <div class="modal-body">
                    <div id="alertMessage"></div>

                    <div class= "form-group {{ $errors->has('name') ? 'has-error' : '' }}">
                        {!! html()->label(Lang::get('message.lic_api_secret'))->class('required') !!}
                        <div class="input-group">
                            {!! html()->password('license_api_secret', $licenseSecret)->class('form-control')->id('license_api_secret') !!}

                            <div class="input-group-append">
                            <span role="button" class="input-group-text" onclick="togglePasswordVisibility(this)">
                                <i class="fa fa-eye-slash"></i>
                            </span>
                            </div>
                        </div>
                        <h6 id="license_apiCheck"></h6>
                    </div>

                    <div class= "form-group {{ $errors->has('name') ? 'has-error' : '' }}">
                        <!-- last name -->
                        {!! html()->label(Lang::get('message.lic_api_url'))->class('required') !!}
                        {!! html()->text('license_api_url', $licenseUrl)->class('form-control')->id('license_api_url') !!}
                        <h6 id="license_urlCheck"></h6>
                    </div>

                    <div class= "form-group {{ $errors->has('name') ? 'has-error' : '' }}">
                        {!! html()->label(Lang::get('message.lic_client_id'))->class('required') !!}
                        {!! html()->text('license_client_id', $licenseClientId)->class('form-control')->id('license_client_id') !!}
                        <h6 id="license_clientIdCheck"></h6>
                    </div>

                    <div class= "form-group {{ $errors->has('name') ? 'has-error' : '' }}">
                        {!! html()->label(Lang::get('message.lic_client_secret'))->class('required') !!}
                        <div class="input-group">
                            {!! html()->password('license_client_secret', $licenseClientSecret)->class('form-control')->id('license_client_secret') !!}

                            <div class="input-group-append">
                            <span role="button" class="input-group-text" onclick="togglePasswordVisibility(this)">
                                <i class="fa fa-eye-slash"></i>
                            </span>
                            </div>
                        </div>
                        <h6 id="license_clientSecretCheck"></h6>
                    </div>

                    <div class= "form-group {{ $errors->has('name') ? 'has-error' : '' }}">
                        {!! html()->label(Lang::get('message.lic_grant_type'))->class('required') !!}
                        {!! html()->select('license_grant_type',['' => __('message.Select'), 'client_credentials' => 'Client_credentials'])
                                    ->class('form-control')->id('license_grant_type') !!}

                        <h6 id="license_grantTypeCheck"></h6>
                    </div>


                </div>

                <div class="modal-footer justify-content-between">
                    <button type="button" id="close" class="btn btn-default pull-left closebutton" data-dismiss="modal"><i class="fa fa-times"></i>&nbsp;{{ __('message.close') }}</button>
                    <button type="submit" class="form-group btn btn-primary"  onclick="licenseDetails()" id="submit"><i class="fa fa-save">&nbsp;</i>{!!Lang::get('message.save')!!}</button>
                </div>
            </div>
        </div>
    </div>



    <div class="modal fade" id="google-recaptcha" data-backdrop="static" data-keyboard="false">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title">{{ __('message.google_recaptcha') }}</h4>

                </div>
                <div class="modal-body">
                    <div id="alertMessage2"></div>

                    <div class="form-group m-1">
                        <div class="d-flex align-items-center">
                            <div class="custom-control custom-radio m-2">
                                <input class="custom-control-input" type="radio" id="captchaRadioV2" name="customRadio" {{ $captchaStatus === 1 ? 'checked' : '' }}>
                                <label for="captchaRadioV2" class="custom-control-label">{{ __('message.recaptcha_v2') }}</label>
                                <i class="fas fa-question-circle  custom-tooltip" data-toggle="tooltip" data-placement="top" style="margin-left: 2px" title="{{Lang::get('message.v2_tooltip')}}"></i>
                            </div>
                            <div class="custom-control custom-radio m-2">
                                <input class="custom-control-input" type="radio" id="captchaRadioV3" name="customRadio" {{ $v3CaptchaStatus === 1 ? 'checked' : '' }}>
                                <label for="captchaRadioV3" class="custom-control-label">{{ __('message.recaptcha_v3') }}</label>
                                <i class="fas fa-question-circle  custom-tooltip" data-toggle="tooltip" data-placement="top" style="margin-left: 2px" title="{{Lang::get('message.v3_tooltip')}}"></i>
                            </div>
                        </div>
                        <span class="system-error text-danger ml-3" id="checkboxerror"></span>
                    </div>
                    <div class= "form-group {{ $errors->has('name') ? 'has-error' : '' }}">
                        {!! html()->label(__('message.nocaptcha_sitekey'), 'nocaptcha_sitekey')->class('required') !!}
                        {!! html()->text('nocaptcha_sitekey', $siteKey)->class('form-control')->id('nocaptcha_sitekey') !!}

                        <span class="system-error" id="status"></span>

                        <h6 id="captcha_sitekeyCheck"></h6>
                    </div>

                    <div class= "form-group {{ $errors->has('name') ? 'has-error' : '' }}">
                        <!-- last name -->
                        {!! html()->label(__('message.nocaptcha_secret'), 'nocaptcha_secret')->class('required') !!}
                        <div class="input-group">
                            <input type= "password" value="{{$secretKey}}" name='nocaptcha_secret' id='nocaptcha_secret' class="form-control">

                            <div class="input-group-append">
                            <span role="button" class="input-group-text" onclick="togglePasswordVisibility(this)">
                                <i class="fa fa-eye-slash"></i>
                            </span>
                            </div>
                        </div>
                        <h6 id="captcha_secretCheck"></h6>
                    </div>
                    <div id="recaptcha-wrapper">
                        <div id="recaptcha-container"></div>
                    </div>
                </div>

                <div class="modal-footer justify-content-between">
                    <button type="button" id="close" class="btn btn-default pull-left closebutton" data-dismiss="modal"><i class="fa fa-times"></i>&nbsp;{{ __('message.close') }}</button>
                    <button type="submit" class="form-group btn btn-primary" onclick="captchaDetails()" id="submit2"><i class="fa fa-save">&nbsp;&nbsp;</i>{!!Lang::get('message.save')!!}</button>

                </div>
            </div>
        </div>
    </div>




    <div class="modal fade" id="mailchimps" data-backdrop="static" data-keyboard="false">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title">{{ __('message.mailchimp') }}</h4>

                </div>

                <div class="modal-body">
                    <div id="alertMessage4"></div>

                        <div class= "form-group {{ $errors->has('name') ? 'has-error' : '' }}">
                            <input type ="hidden" id="hiddenMailChimpValue" value="{{$mailchimpKey}}">
                            {!! html()->label(__('message.mailchimp_key'), 'mailchimp')->class('required me-2') !!}
                            {!! html()->text('mailchimp', $mailchimpKey)->class('form-control mailchimp_authkey')->id('mailchimp_authkey') !!}

                            <h6 id="mailchimp_check" style="margin: 0;"></h6>
                        </div>

                        <div id="extraInput" style="display: none;">


                        <div class= "form-group {{ $errors->has('name') ? 'has-error' : '' }}">

                            {!! html()->label(Lang::get('message.list_id'), 'list_id')->class('required') !!}
                            <select name="list_id" class="form-control" id="list_id" style="width:100%">
                            <option value="">{{ __('message.choose') }}</option>
                            @foreach($allists as $list)
                                <option value="{{$list->id}}"<?php  if(in_array($list->id, $selectedList) )
                                { echo "selected";} ?>>{{$list->name}}</option>

                            @endforeach
                            </select>
                            <span><i> {{Lang::get('message.enter-the-mailchimp-list-id')}}</i> </span>

                        </div>

                        <div class= "form-group {{ $errors->has('name') ? 'has-error' : '' }}">
                            {!! html()->label(Lang::get('message.subscribe_status'), 'subscribe_status')->class('required') !!}
                            {!! html()->select('subscribe_status', [
                                'subscribed' => 'Subscribed',
                                'unsubscribed' => 'Unsubscribed',
                                'cleaned' => 'Cleaned',
                                'pending' => 'Pending'
                            ])->class('form-control') !!}
                            <span><i> {{Lang::get('message.enter-the-mailchimp-subscribe-status')}}</i> </span>
                        </div>

                            <div id="extraInput9" style="display: none;">

                            <div class= "form-group {{ $errors->has('name') ? 'has-error' : '' }}">
                                {!! html()->label(Lang::get('message.mapping'), 'mapping')->class('required') !!}
                                <a href="{{url('mail-chimp/mapping')}}" class="btn btn-secondary btn-sm">{{Lang::get('message.mapping')}}</a>
                                <p><i> {{Lang::get('message.map-the-mailchimp-field-with-agora')}}</i> </p>
                            </div>
                            </div>
                        </div>
                    </div>
                <div id="extraInput1" style="display: none;">
                <div class="modal-footer justify-content-between">
                    <button type="button" id="close1" class="btn btn-default pull-left closebutton" data-dismiss="modal"><i class="fa fa-times"></i>&nbsp;{{ __('message.close') }}</button>
                        <button type="submit" class="btn btn-primary pull-right" id="submit-chimp" ><i class="fa fa-save">&nbsp;</i>{!!Lang::get('message.save')!!}</button>
                    </div>

                </div>
{{--                {!! Form::close() !!}--}}

                <div id="extraInput5" style="display: block;">
                <div class="modal-footer justify-content-between">
                    <button type="button" id="close1" class="btn btn-default pull-left closebutton" data-dismiss="modal"><i class="fa fa-times"></i>&nbsp;{{ __('message.close') }}</button>

                    <button type="submit" class="btn btn-primary" id="submit9">
                        <i class="fa fa-save"></i>&nbsp;&nbsp;{!! Lang::get('message.save') !!}
                    </button>
                    </div>
                </div>

            </div>

        </div>
    </div>


    <div class="modal fade" id="showTerms" data-backdrop="static" data-keyboard="false">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title">{{ __('message.terms_heading') }}</h4>
                </div>
                <div class="modal-body">
                    <div id="alertMessage5"></div>
                    <input type ="hidden" id="hiddenTermsValue" value="{{$termsUrl}}">
                    <div class= "form-group {{ $errors->has('name') ? 'has-error' : '' }}">
                        {!! html()->label(__('message.terms_url'), 'terms')->class('required') !!}
                        {!! html()->text('terms', $termsUrl)->class('form-control terms_url')->id('terms_url') !!}
                        <h6 id="terms_check"></h6>
                 </div>
                </div>
                <div class="modal-footer justify-content-between">
                    <button type="button" id="close" class="btn btn-default pull-left closebutton" data-dismiss="modal"><i class="fa fa-times"></i>&nbsp;{{ __('message.close') }}</button>
                    <button type="submit" class="form-group btn btn-primary"  id="submit10"><i class="fa fa-save">&nbsp;</i>{!!Lang::get('message.save')!!}</button>

                </div>
        </div>
    </div>
    </div>



    <div class="modal fade" id="twitters" data-backdrop="static" data-keyboard="false">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title">{{ __('message.twitter') }}</h4>

                </div>
                <div class="modal-body">
                    <div id="alertMessage6"></div>
                    <input type ="hidden" id="hidden_consumer_key" value="{{$twitterKeys->twitter_consumer_key}}">
                    <input type ="hidden" id="hidden_consumer_secret" value="{{$twitterKeys->twitter_consumer_secret}}">
                    <input type ="hidden" id="hidden_access_token" value="{{$twitterKeys->twitter_access_token}}">
                    <input type ="hidden" id="hidden_token_secret" value="{{$twitterKeys->access_tooken_secret}}">

                    <div class= "form-group {{ $errors->has('name') ? 'has-error' : '' }}">
                        {!! html()->label(__('message.consumer_key'), 'consumer_key') !!}
                        {!! html()->text('consumer_key', $twitterKeys->twitter_consumer_key)->class('form-control consumer_key')->id('consumer_key') !!}
                        <h6 id="consumer_keycheck"></h6>
                    </div>

                    <div class= "form-group {{ $errors->has('name') ? 'has-error' : '' }}">
                        <!-- last name -->
                        {!! html()->label(__('message.consumer_secret'), 'consumer_secret') !!}
                        <div class="input-group">
                            <input type= "password" value="{{$twitterKeys->twitter_consumer_secret}}" name='consumer_secret' id='consumer_secret' class="form-control consumer_secret">

                            <div class="input-group-append">
                            <span role="button" class="input-group-text" onclick="togglePasswordVisibility(this)">
                                <i class="fa fa-eye-slash"></i>
                            </span>
                            </div>
                        </div>
                        <h6 id="consumer_secretcheck"></h6>
                    </div>

                    <div class= "form-group {{ $errors->has('name') ? 'has-error' : '' }}">

                        {!! html()->label(__('message.access_token'), 'access_token') !!}
                        {!! html()->text('access_token', $twitterKeys->twitter_access_token)->class('form-control access_token')->id('access_token') !!}
                        <h6 id="access_tokencheck"></h6>
                    </div>


                    <div class= "form-group {{ $errors->has('name') ? 'has-error' : '' }}">
                        {!! html()->label(__('message.token_secret'), 'token_secret') !!}
                        <div class="input-group">
                            <input type= "password" value="{{$twitterKeys->access_tooken_secret}}" name='token_secret' id='token_secret' class="form-control token_secret">

                            <div class="input-group-append">
                            <span role="button" class="input-group-text" onclick="togglePasswordVisibility(this)">
                                <i class="fa fa-eye-slash"></i>
                            </span>
                            </div>
                        </div>
                        <h6 id="token_secretcheck"></h6>
                    </div>

                </div>

                <div class="modal-footer justify-content-between">
                    <button type="button" id="close" class="btn btn-default pull-left closebutton" data-dismiss="modal"><i class="fa fa-times"></i>&nbsp;{{ __('message.close') }}</button>
                    <button type="submit" class="form-group btn btn-primary"  id="submit5"><i class="fa fa-save">&nbsp;&nbsp;</i>{!!Lang::get('message.save')!!}</button>

                </div>
            </div>
        </div>
    </div>


    <div class="modal fade" id="zohoCrm" data-backdrop="static" data-keyboard="false">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title">{{ __('message.zoho_crm') }}</h4>
                </div>
                <div class="modal-body">
                    <div id="alertMessage7"></div>

                    <input type ="hidden" id="hidden_zoho_key" value="{{$zohoKey}}">

                    <div class= "form-group {{ $errors->has('name') ? 'has-error' : '' }}">
                        {!! html()->label(__('message.zoho_crm'), 'zoho_key') !!}
                        {!! html()->text('zoho_key', $zohoKey)->class('form-control zoho_key')->id('zoho_key') !!}
                        <h6 id="zoho_keycheck"></h6>
                    </div>

                </div>

                <div class="modal-footer justify-content-between">
                    <button type="button" id="close" class="btn btn-default pull-left closebutton" data-dismiss="modal"><i class="fa fa-times"></i>&nbsp;{{ __('message.close') }}</button>
                    <button type="submit" class="form-group btn btn-primary"  id="submit7"><i class="fa fa-save">&nbsp;</i>{!!Lang::get('message.save')!!}</button>

                </div>
            </div>
        </div>
    </div>


    <div class="modal fade" id="pipedrv" data-backdrop="static" data-keyboard="false">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title">{{ __('message.pipedrive') }}</h4>
                </div>
                <div class="modal-body">
                    <div id="alertMessage8"></div>

                    <input type ="hidden" id="hidden_pipedrive_key" value="{{$pipedriveKey}}">
                    <div class= "form-group {{ $errors->has('name') ? 'has-error' : '' }}">
                        {!! html()->label(__('message.pipedrive_key'), 'pipedrive_key')->class('required') !!}
                        {!! html()->text('pipedrive_key', $pipedriveKey)->class('form-control pipedrive_key')->id('pipedrive_key') !!}
                         <h6 id="pipedrive_keycheck"></h6>
                    </div>
                    <div class="form-group">
                        <label>{{ __('message.user_verification') }}</label>&nbsp;&nbsp;<i class="fas fa-info-circle" data-toggle="tooltip" data-placement="top" title="{{ __('message.pipedrive_user_verification_tooltip') }}"></i>
                        <select class="form-control mt-2" id="pipedrive_key_status" name="pipedrive_key_status">
                            <option value="1" {{ $isPipedriveVerificationEnabled == 1 ? 'selected' : '' }}>Yes</option>
                            <option value="0" {{ $isPipedriveVerificationEnabled == 0 ? 'selected' : '' }}>No</option>
                        </select>
                    </div>

                </div>

                <div class="modal-footer justify-content-between">
                    <button type="button" id="close" class="btn btn-default pull-left closebutton" data-dismiss="modal"><i class="fa fa-times"></i>&nbsp;{{ __('message.close') }}</button>
                    <button type="submit" class="form-group btn btn-primary"  id="submit13"><i class="fa fa-save">&nbsp;</i>{!!Lang::get('message.save')!!}</button>

                </div>
            </div>
        </div>
    </div>
{{--    {!! Form::close() !!}--}}

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <link rel="stylesheet" href="https://cdn.datatables.net/1.10.22/css/jquery.dataTables.min.css">
    <!-- <script src="https://code.jquery.com/jquery-3.5.1.js"></script> -->
    <link rel="stylesheet" href="https://cdn.datatables.net/buttons/1.0.3/css/buttons.dataTables.min.css">
    <script src="https://cdn.datatables.net/buttons/1.0.3/js/dataTables.buttons.min.js"></script>

    <script src="https://cdn.datatables.net/1.10.22/js/jquery.dataTables.min.js"></script>
    <script src="https://www.google.com/recaptcha/api.js?onload=onloadCallback&render=explicit" async defer></script>

    <script>
        $('#close1').on('click',function(){
            location.reload();
        });

        let currentSiteKey = '';
        let scriptLoaded = false;

        function isLikelyV3Key(sitekey) {

            return sitekey.length > 35 && !sitekey.includes('localhost');

        }

        function loadRecaptchaScript(sitekey, callback) {
            // Remove old script if present
            const existingScript = document.querySelector('script[src*="google.com/recaptcha/api.js"]');
            if (existingScript) {
                existingScript.remove();
                scriptLoaded = false;
                delete window.grecaptcha;
            }

            const script = document.createElement('script');
            script.src = `https://www.google.com/recaptcha/api.js?render=${sitekey}`;
            script.async = true;
            script.defer = true;

            script.onload = () => {
                scriptLoaded = true;
                callback();
            };

            script.onerror = () => {
                document.getElementById("nocaptcha_sitekey").classList.add('is-invalid');
                document.getElementById("status").textContent = @json(trans('message.invalid_recaptcha_key'));
                document.getElementById("submit2").disabled=true;

            };

            document.head.appendChild(script);
        }

        function verifyWithRecaptchaV3(sitekey) {
            if (!window.grecaptcha) {
                document.getElementById("status").textContent = @json(trans('message.invalid_recaptcha_key'));
                document.getElementById("submit2").disabled=true;
                return;
            }

            try {
                grecaptcha.ready(() => {
                    try {
                        grecaptcha.execute(sitekey, {action: 'submit'})
                            .then(token => {
                                // Optional: Show token in console for debugging

                                if (!token || token.length < 100) {
                                    document.getElementById("submit2").disabled=true;
                                    throw new Error(@json(trans('message.invalid_recaptcha_key')));
                                }

                                document.getElementById("nocaptcha_sitekey").classList.remove('is-invalid');
                                document.getElementById("status").textContent = "";
                                document.getElementById("submit2").disabled=false;

                            })
                            .catch(err => {
                                document.getElementById("status").textContent = @json(trans('message.invalid_recaptcha_key'));
                                document.getElementById("nocaptcha_sitekey").classList.add('is-invalid');
                                document.getElementById("submit2").disabled=true;
                            });
                    }catch(err){
                        document.getElementById("status").textContent = @json(trans('message.invalid_recaptcha_key'));
                        document.getElementById("nocaptcha_sitekey").classList.add('is-invalid');
                        document.getElementById("submit2").disabled=true;

                    }
                });
            } catch (e) {
                document.getElementById("status").textContent = @json(trans('message.invalid_recaptcha_key'));
                document.getElementById("nocaptcha_sitekey").classList.add('is-invalid');
                document.getElementById("submit2").disabled=true;

            }
        }

        document.addEventListener('DOMContentLoaded', function () {
            document.getElementById('nocaptcha_sitekey').addEventListener('input', function () {
                const initial_id = document.querySelector('input[name="customRadio"]:checked')?.id;
                const newSiteKey = this.value.trim();
                resetRecaptchaContainer();

                document.getElementById("status").textContent = '';
                var captchaError=document.getElementById('captchaError');
                if(captchaError !=null) {
                    document.getElementById('captchaError').textContent = '';
                }
                if (initial_id === 'captchaRadioV3') {
                    if (!newSiteKey){
                        document.getElementById("submit2").disabled=true;
                        return;
                    }
                    if (!isLikelyV3Key(newSiteKey)) {
                        document.getElementById("status").textContent = @json(trans('message.invalid_recaptcha_key'));
                        document.getElementById("nocaptcha_sitekey").classList.add('is-invalid');
                        document.getElementById("submit2").disabled=true;
                        return;
                    }

                    if (newSiteKey && newSiteKey !== currentSiteKey) {
                        currentSiteKey = newSiteKey;
                        loadRecaptchaScript(newSiteKey, () => {
                            verifyWithRecaptchaV3(newSiteKey);
                        });
                    }
                }
            });
        });
    </script>


    <script>
        document.getElementById('nocaptcha_sitekey').addEventListener('input', function () {
            resetRecaptchaContainer();
            var captchaError=document.getElementById('cterms_headingaptchaError');
            if(captchaError !=null) {
                document.getElementById('captchaError').textContent = '';
            }
            var initial_id=$('input[name="customRadio"]:checked').attr('id');

            if(initial_id==='captchaRadioV2') {
                const sitekey = this.value.trim();
                if (!sitekey){
                    document.getElementById("submit2").disabled=true;
                    return;
                }
                validateRecaptchaV2Key(sitekey);

            }
            });
        let recaptchaWidgetId = null;

        function validateRecaptchaV2Key(sitekey) {
            const containerId = 'recaptcha-container';

            const oldScript = document.querySelector('script[src*="recaptcha/api.js"]');
            if (oldScript) {
                oldScript.remove();
            }
            document.getElementById("status").textContent = '';
            document.getElementById("nocaptcha_sitekey").classList.remove('is-invalid');
            // Clear any existing reCAPTCHA widget
            document.getElementById(containerId).innerHTML = '';
            recaptchaWidgetId = null;
            delete window.grecaptcha;

            // Load reCAPTCHA API script
            const script = document.createElement('script');
            script.src = 'https://www.google.com/recaptcha/api.js?onload=onV2Load&render=explicit';
            script.async = true;
            script.defer = true;

            window.onV2Load = function () {
                try {
                    // Render reCAPTCHA inside the container
                    recaptchaWidgetId = grecaptcha.render(containerId, {
                        sitekey: sitekey,
                        callback: function (token) {
                            document.getElementById("status").textContent = '';
                            document.getElementById("nocaptcha_sitekey").classList.remove('is-invalid');
                            document.getElementById("submit2").disabled=false;

                        },
                        'error-callback': function() {
                            // Handle error callback when reCAPTCHA fails
                            showError();
                        }
                    });
                    clearError();
                } catch (e) {
                    showError();
                }
            };

            script.onerror = function () {
                showError();
            };

            try {
                document.head.appendChild(script);
                clearError();
            } catch (err) {
                showError(); // Show error if there is an issue with appending the script tag
            }
        }



        // Function to display error messages
        function showError() {
            document.getElementById("status").textContent = @json(trans('message.invalid_recaptcha_key'));
            document.getElementById("nocaptcha_sitekey").classList.add('is-invalid');
            document.getElementById("submit2").disabled=true;

        }

        // Function to clear error messages
        function clearError() {
            document.getElementById("status").textContent = '';
            document.getElementById("nocaptcha_sitekey").classList.remove('is-invalid');
            document.getElementById("submit2").disabled=false;

        }

        function resetRecaptchaContainer() {
                // Remove old script
                const oldScript = document.querySelector('script[src*="recaptcha/api.js"]');
                if (oldScript) oldScript.remove();

                // Delete global grecaptcha object
                delete window.grecaptcha;

                // Remove container
                document.getElementById('recaptcha-container')?.remove();
                const newContainer = document.createElement('div');
                newContainer.id = 'recaptcha-container';
                document.getElementById('recaptcha-wrapper').appendChild(newContainer);

                recaptchaWidgetId = null;
            }


        $('input[name="customRadio"]').change(function () {
            var initial_key=$('#nocaptcha_sitekey').val();
            var initial_secret=$('#nocaptcha_secret').val();
            var initial_id=$('input[name="customRadio"]:checked').attr('id');
            document.getElementById("submit2").disabled=true;
            document.getElementById('checkboxerror').textContent='';
            document.getElementById("status").textContent = '';
            document.getElementById("nocaptcha_sitekey").classList.remove('is-invalid');
            const newSiteKey = initial_key;
            resetRecaptchaContainer();

            var captchaError=document.getElementById('captchaError');
            if(captchaError !=null) {
                document.getElementById('captchaError').textContent = '';
            }
            if (initial_id === 'captchaRadioV3') {
                if (!newSiteKey){
                    document.getElementById("submit2").disabled=true;
                    return;
                }
                if (!isLikelyV3Key(newSiteKey)) {
                    document.getElementById("status").textContent = @json(trans('message.invalid_recaptcha_key'));
                    document.getElementById("nocaptcha_sitekey").classList.add('is-invalid');
                    document.getElementById("submit2").disabled=true;
                    return;
                }

                if (newSiteKey) {
                    currentSiteKey = newSiteKey;
                    loadRecaptchaScript(newSiteKey, () => {
                        verifyWithRecaptchaV3(newSiteKey);
                    });
                }
            }
            if(initial_id==='captchaRadioV2') {
                const sitekey = initial_key;
                if (!sitekey){

                    document.getElementById("submit2").disabled=true;
                    return;}
                validateRecaptchaV2Key(sitekey);

            }
            var selectedId = $(this).attr('id'); // Get the ID of selected radio button

            let inputGroup1 =document.getElementById('nocaptcha_secret');
            let passwordInput1 = inputGroup1.type;
            let icon1 = inputGroup1.closest('.input-group').querySelector('i');
            if(passwordInput1==='text'){
                inputGroup1.type='password';
                icon1.classList.remove('fa-eye');
                icon1.classList.add('fa-eye-slash');
            }
        });

    </script>

    <script>
        $(document).on('click', '#license-edit-button', function() {
            $.ajax({

                url : '{{url("licensekeys")}}',
                type : 'post',
                success: function (response) {

                    $('#license_api_secret').val(response['data']['licenseSecret']);
                    let inputGroup =document.getElementById('license_api_secret');
                    let passwordInput = inputGroup.type;
                    let icon = inputGroup.closest('.input-group').querySelector('i');
                    if(passwordInput==='text'){
                        inputGroup.type='password';
                        icon.classList.remove('fa-eye');
                        icon.classList.add('fa-eye-slash');
                    }
                    $('#license_api_url').val(response['data']['licenseUrl']);
                    $('#license_client_id').val(response['data']['licenseClientId']);
                    $('#license_client_secret').val(response['data']['licenseClientSecret']);
                    let inputGroup1 =document.getElementById('license_client_secret');
                    let passwordInput1 = inputGroup1.type;
                    let icon1 = inputGroup1.closest('.input-group').querySelector('i');
                    if(passwordInput1==='text'){
                        inputGroup1.type='password';
                        icon1.classList.remove('fa-eye');
                        icon1.classList.add('fa-eye-slash');
                    }
                    $('#license_grant_type').val(response['data']['licenseGrantType']);
                    $('#create-third-party-app').modal('show');
                },
            });
        });

        $(document).on('click', '#captcha-edit-button', function() {
            $.ajax({

                url : '{{url("googleCaptcha")}}',
                type : 'post',
                success: function (response) {
                    if(response['data']['captchaStatus']){
                        selectCaptcha('captchaRadioV2');
                    }
                    if(response['data']['v3_recaptcha_status']){
                        selectCaptcha('captchaRadioV3');
                    }
                    document.getElementById("nocaptcha_sitekey").classList.remove('is-invalid');
                    document.getElementById("status").innerHTML = "";
                    $('#nocaptcha_sitekey').val(response['data']['siteKey']);
                    $('#nocaptcha_secret').val(response['data']['secretKey']);
                    let inputGroup1 =document.getElementById('nocaptcha_secret');
                    let passwordInput1 = inputGroup1.type;
                    let icon1 = inputGroup1.closest('.input-group').querySelector('i');
                    if(passwordInput1==='text'){
                        inputGroup1.type='password';
                        icon1.classList.remove('fa-eye');
                        icon1.classList.add('fa-eye-slash');
                    }
                    $('#google-recaptcha').modal('show');
                },
            });
        });
        function selectCaptcha(version) {
            let radios = document.getElementsByName('customRadio'); // Get all radio buttons by name
            radios.forEach(radio => {
                if (radio.id === version) {
                    radio.checked = true; // Select the correct radio
                } else {
                    radio.checked = false;
                }
            });
        }

        // Example Usage: Change selection dynamically
        $(document).on('click', '#msg91-edit-button', function() {
            $.ajax({

                url : '{{url("mobileVerification")}}',
                type : 'post',
                success: function (response) {
                    $('#mobile_authkey').val(response['data']['mobileauthkey']);
                    $('#sender').val(response['data']['msg91Sender']);
                    $('#template_id').val(response['data']['msg91TemplateId']);
                    $('#third_party_key').val(response['data']['selectedApp']);
                    $('#msg-91').modal('show');
                },
            });
        });

        $(document).on('click', '#termsUrl-edit-button', function() {
            $.ajax({

                url : '{{url("termsUrl")}}',
                type : 'post',
                success: function (response) {
                    $('#terms_url').val(response['data']['termsUrl']);

                    $('#showTerms').modal('show');
                },
            });
        });

        $(document).on('click', '#zoho-edit-button', function() {
            $.ajax({

                url : '{{url("zohokeys")}}',
                type : 'post',
                success: function (response) {
                    $('#zoho_key').val(response['data']['zohoKey']);

                    $('#zohoCrm').modal('show');
                },
            });
        });


        $(document).on('click', '#pipedrive-edit-button', function() {
            $.ajax({

                url : '{{url("pipedrivekeys")}}',
                type : 'post',
                success: function (response) {
                    $('#pipedrive_key').val(response['data']['pipedriveKey']);

                    $('#pipedrv').modal('show');
                },
            });
        });


        $(document).on('click', '#twitter-edit-button', function() {
            $.ajax({

                url : '{{url("twitterkeys")}}',
                type : 'post',
                success: function (response) {
                    $('#consumer_key').val(response['data']['twitterkeys']['twitter_consumer_key']);
                    $('#consumer_secret').val(response['data']['twitterkeys']['twitter_consumer_secret']);
                    $('#access_token').val(response['data']['twitterkeys']['twitter_access_token']);
                    $('#token_secret').val(response['data']['twitterkeys']['access_tooken_secret']);

                    $('#twitters').modal('show');
                },
            });
        });


        $(document).on('click', '#github-edit-button', function() {
            $.ajax({

                url : '{{url("githubkeys")}}',
                type : 'post',
                success: function (response) {
                    $('#git_username').val(response['data']['githubFileds']['username']);
                    $('#git_password').val(response['data']['githubFileds']['password']);
                    let inputGroup1 =document.getElementById('git_password');
                    let passwordInput1 = inputGroup1.type;
                    let icon1 = inputGroup1.closest('.input-group').querySelector('i');
                    if(passwordInput1==='text'){
                        inputGroup1.type='password';
                        icon1.classList.remove('fa-eye');
                        icon1.classList.add('fa-eye-slash');
                    }

                    $("#githubSet").modal('show');
                },
            });
        });


        $(document).on('click', '#mailchimp-edit-button', function() {
            $.ajax({

                url : '{{url("mailchimpkeys")}}',
                type : 'post',
                success: function (response) {
                    $('#mailchimp_authkey').val(response['data']['mailchimpKey']);
                    var array=response['allLists'];
                    if(array && array.length) {
                        var value1 = response['data']['allLists'][0]['id'];
                        var name1 = response['data']['allLists'][0]['name'];
                        var value2 = response['data']['subscribe_status'];
                        const select = document.getElementById('list_id');
                        select.value = value1;
                        select.text = name1;
                        const select1 = document.getElementById('subscribe_status');
                        select1.value = value2;
                    }

                    $("#mailchimps").modal('show');
                },
            });
        });
        $(document).on('change', '.licenser input[type="checkbox"]', function() {
            if ($('#License').prop("checked")) {
            var checkboxvalue = 1;
        }
        else{
            var checkboxvalue = 0;
        }
            $.ajax({
                url : '{{url("licenseStatus")}}',
                type : 'post',
                data: {
                    "status": checkboxvalue,
                },
                success: function (response) {
                    setTimeout(function() {
                        location.reload();
                    }, 3000);
                    $('#alertMessage12').show();
                    var result =  '<div class="alert alert-success alert-dismissable"><button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button><strong><i class="fa fa-check"></i> {{ __('message.success') }}! </strong>'+response.message+'.</div>';
                    $('#alertMessage12').html(result);
                    $("#submit").html("<i class='fa fa-save'>&nbsp;&nbsp;</i>{{ __('message.save') }}");
                    setInterval(function(){
                        $('#alertMessage12').slideUp(3000);
                    }, 1000);
                },
            });

        });

        $(document).on('change', '.mstatus input[type="checkbox"]', function() {
            if ($('#mobile').prop("checked")) {
                var checkboxvalue = 1;
            }
            else{
                var checkboxvalue = 0;
            }

            $.ajax({

                url : '{{url("licenseStatus")}}',
                type : 'post',
                data: {
                    "mstatus": checkboxvalue,
                },
                success: function (response) {
                    setTimeout(function() {
                        location.reload();
                    }, 3000);
                    $('#alertMessage12').show();
                    var result =  '<div class="alert alert-success alert-dismissable"><button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button><strong><i class="fa fa-check"></i> {{ __('message.success') }}! </strong>'+response.message+'.</div>';
                    $('#alertMessage12').html(result);
                    $("#submit").html("<i class='fa fa-save'>&nbsp;&nbsp;</i>{{ __('message.save') }}");
                    setInterval(function(){
                        $('#alertMessage12').slideUp(3000);
                    }, 1000);

                },
            });

        });

        $(document).on('change', '.mailchimpstatus input[type="checkbox"]', function() {
            if ($('#mailchimp').prop("checked")) {
                var checkboxvalue = 1;
            }
            else{
                var checkboxvalue = 0;
            }

            $.ajax({

                url : '{{url("licenseStatus")}}',
                type : 'post',
                data: {
                    "mailchimpstatus": checkboxvalue,
                },
                success: function (response) {
                    setTimeout(function() {
                        location.reload();
                    }, 3000);
                    $('#alertMessage12').show();
                    var result =  '<div class="alert alert-success alert-dismissable"><button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button><strong><i class="fa fa-check"></i> {{ __('message.success') }}! </strong>'+response.message+'.</div>';
                    $('#alertMessage12').html(result);
                    $("#submit").html("<i class='fa fa-save'>&nbsp;&nbsp;</i>{{ __('message.save') }}");
                    setInterval(function(){
                        $('#alertMessage12').slideUp(3000);
                    }, 1000);

                },
            });

        });


        $(document).on('change', '.termstatus1 input[type="checkbox"]', function() {
            if ($('#terms').prop("checked")) {
                var checkboxvalue = 1;
            }
            else{
                var checkboxvalue = 0;
            }

            $.ajax({

                url : '{{url("licenseStatus")}}',
                type : 'post',
                data: {
                    "termsStatus": checkboxvalue,
                },
                success: function (response) {
                    setTimeout(function() {
                        location.reload();
                    }, 3000);
                    $('#alertMessage12').show();
                    var result =  '<div class="alert alert-success alert-dismissable"><button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button><strong><i class="fa fa-check"></i> {{ __('message.success') }}! </strong>'+response.message+'.</div>';
                    $('#alertMessage12').html(result);
                    $("#submit").html("<i class='fa fa-save'>&nbsp;&nbsp;</i>{{ __('message.save') }}");
                    setInterval(function(){
                        $('#alertMessage12').slideUp(3000);
                    }, 1000);

                },
            });

        });

        $(document).on('change', '.twitterstatus input[type="checkbox"]', function() {
            if ($('#twitter').prop("checked")) {
                var checkboxvalue = 1;
            }
            else{
                var checkboxvalue = 0;
            }

            $.ajax({

                url : '{{url("licenseStatus")}}',
                type : 'post',
                data: {
                    "twitterstatus": checkboxvalue,
                },
                success: function (response) {
                    setTimeout(function() {
                        location.reload();
                    }, 3000);
                    $('#alertMessage12').show();
                    var result =  '<div class="alert alert-success alert-dismissable"><button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button><strong><i class="fa fa-check"></i> {{ __('message.success') }}! </strong>'+response.message+'.</div>';
                    $('#alertMessage12').html(result);
                    $("#submit").html("<i class='fa fa-save'>&nbsp;&nbsp;</i>{{ __('message.save') }}");
                    setInterval(function(){
                        $('#alertMessage12').slideUp(3000);
                    }, 1000);

                },
            });

        });

        $(document).on('change', '.gcaptcha input[type="checkbox"]', function() {
            if ($('#captcha').prop("checked")) {
                var checkboxvalue = 1;
            }
            else{
                var checkboxvalue = 0;
            }
            $.ajax({

                url : '{{url("licenseStatus")}}',
                type : 'post',
                data: {
                    "gcaptchastatus": checkboxvalue,
                },
                success: function (response) {
                    setTimeout(function() {
                        location.reload();
                    }, 3000);
                    $('#alertMessage12').show();
                    var result =  '<div class="alert alert-success alert-dismissable"><button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button><strong><i class="fa fa-check"></i> {{ __('message.success') }}! </strong>'+response.message+'.</div>';
                    $('#alertMessage12').html(result);
                    $("#submit").html("<i class='fa fa-save'>&nbsp;&nbsp;</i>{{ __('message.save') }}");
                    setInterval(function(){
                        $('#alertMessage12').slideUp(3000);
                    }, 1000);

                },
            });

        });


        $(document).on('change', '.zohostatus input[type="checkbox"]', function() {
            if ($('#zoho').prop("checked")) {
                var checkboxvalue = 1;
            }
            else{
                var checkboxvalue = 0;
            }

            $.ajax({

                url : '{{url("licenseStatus")}}',
                type : 'post',
                data: {
                    "zohostatus": checkboxvalue,
                },
                success: function (response) {
                    setTimeout(function() {
                        location.reload();
                    }, 3000);
                    $('#alertMessage12').show();
                    var result =  '<div class="alert alert-success alert-dismissable"><button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button><strong><i class="fa fa-check"></i> {{ __('message.success') }}! </strong>'+response.message+'.</div>';
                    $('#alertMessage12').html(result);
                    $("#submit").html("<i class='fa fa-save'>&nbsp;&nbsp;</i>{{ __('message.save') }}");
                    setInterval(function(){
                        $('#alertMessage12').slideUp(3000);
                    }, 1000);

                },
            });

        });

        $(document).on('change', '.pipedrivestatus input[type="checkbox"]', function() {
            if ($('#pipedrive').prop("checked")) {
                var checkboxvalue = 1;
            }
            else{
                var checkboxvalue = 0;
            }

            $.ajax({

                url : '{{url("licenseStatus")}}',
                type : 'post',
                data: {
                    "pipedrivestatus": checkboxvalue,
                },
                success: function (response) {
                    setTimeout(function() {
                        location.reload();
                    }, 3000);
                    $('#alertMessage12').show();
                    var result =  '<div class="alert alert-success alert-dismissable"><button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button><strong><i class="fa fa-check"></i> {{ __('message.success') }}! </strong>'+response.message+'.</div>';
                    $('#alertMessage12').html(result);
                    $("#submit").html("<i class='fa fa-save'>&nbsp;&nbsp;</i>{{ __('message.save') }}");
                    setInterval(function(){
                        $('#alertMessage12').slideUp(3000);
                    }, 1000);

                },
            });

        });

        $(document).on('change', '.githubstatus input[type="checkbox"]', function() {
            if ($('#github').prop("checked")) {
                var checkboxvalue = 1;
            }
            else{
                var checkboxvalue = 0;
            }

            $.ajax({

                url : '{{url("licenseStatus")}}',
                type : 'post',
                data: {
                    "githubstatus": checkboxvalue,
                },
                success: function (response) {
                    setTimeout(function() {
                        location.reload();
                    }, 3000);
                    $('#alertMessage12').show();
                    var result =  '<div class="alert alert-success alert-dismissable"><button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button><strong><i class="fa fa-check"></i> {{ __('message.success') }}! </strong>'+response.message+'.</div>';
                    $('#alertMessage12').html(result);
                    $("#submit").html("<i class='fa fa-save'>&nbsp;&nbsp;</i>{{ __('message.save') }}");
                    setInterval(function(){
                        $('#alertMessage12').slideUp(3000);
                    }, 1000);

                },
            });

        });


        $(document).ready(function () {
            $('#custom-table').DataTable({
                processing: true,
                serverSide: true,
                stateSave: false,

                ajax: "{{ route('datatable.data') }}", // Calls the separate function

                oLanguage: {
                    sLengthMenu: "_MENU_ Records per page",
                    sSearch: "<span style='right: 180px;'>Search:</span> ",
                    sProcessing: ' <div class="overlay dataTables_processing"><i class="fas fa-3x fa-sync-alt fa-spin" style=" margin-top: -25px;"></i><div class="text-bold pt-2">{{ __('message.loading') }}</div></div>'
                },
                language: {
                    paginate: {
                        first:      "{{ __('message.paginate_first') }}",
                        last:       "{{ __('message.paginate_last') }}",
                        next:       "{{ __('message.paginate_next') }}",
                        previous:   "{{ __('message.paginate_previous') }}"
                    },
                    emptyTable:     "{{ __('message.empty_table') }}",
                    info:           "{{ __('message.datatable_info') }}",
                    zeroRecords:    "{{ __('message.no_matching_records_found') }} ",
                    infoEmpty:      "{{ __('message.info_empty') }}",
                    infoFiltered:   "{{ __('message.info_filtered') }}",
                    lengthMenu:     "{{ __('message.length_menu') }}",
                    loadingRecords: "{{ __('message.loading_records') }}",
                    search:         "{{ __('message.table_search') }}",
                },

                // Apply 'no-sort' class only to specific targets (3rd and 4th columns)
                columnDefs: [
                    {
                        targets: [2, 3], // Status and Action columns
                        orderable: false
                    }
                ],

                columns: [
                    { data: 'options', name: 'options', orderable: true, searchable: true },
                    { data: 'description', name: 'description', orderable: true, searchable: true },
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
        $(document).ready(function(){
            $('[data-toggle="tooltip"]').tooltip();
        });
        function updateWebhookField(selectedId) {
            if (!selectedId) {
                $('#webhook_section').hide();
                $('#webhook_url').val('');
                return;
            }

            $.ajax({
                url: "{{ url('msgThirdPartyUpdate') }}/" + selectedId,
                type: 'GET',
                success: function (data) {
                    const key = data?.data?.app_key ?? '';
                    const secret = data?.data?.app_secret ?? '';

                    if (key && secret) {
                        const fullUrl = `{{ url('api/msg91/reports') }}?app_key=${key}&app_secret=${secret}`;
                        $('#webhook_url').val(fullUrl);
                        $('#webhook_section').show();
                    } else {
                        $('#webhook_section').hide();
                        $('#webhook_url').val('');
                    }
                },
                error: function () {
                    $('#webhook_section').hide();
                    $('#webhook_url').val('');
                }
            });
        }

        function copyToClipboard(inputSelector, buttonElement) {
            const input = $(inputSelector);
            const value = input.val().trim();

            if (!value) return;

            input[0].select();
            input[0].setSelectionRange(0, 99999);

            // Try to execute the copy command
            var successful = document.execCommand('copy');

            // Prepare the success or failure message for the tooltip
            var msg = successful ? 'Copied!' : 'Whoops, not copied!';

            const $tooltipDiv = $('#copy_tooltip_div');
            $tooltipDiv.attr('data-original-title', msg).tooltip('show');

            // Change the button icon to a check mark
            const icon = $(buttonElement).find('i');
            icon.removeClass('fa-copy').addClass('fa-check');

            // Restore the original icon after 3 seconds
            setTimeout(() => {
                icon.removeClass('fa-check').addClass('fa-copy');
            }, 3000);

            // Restore the original tooltip text after 3 seconds
            setTimeout(() => {
                $tooltipDiv.attr('data-original-title', @json(trans('message.copy_to_clipboard')));
            }, 3000);
        }

        var initialVal = $('#third_party_key').val();
        if (initialVal) {
            updateWebhookField(initialVal);
        }

        // On select change
        $('#third_party_key').on('change', function () {
            updateWebhookField($(this).val());
        });

        // Copy button click
        $('#copy_button').on('click', function () {
            copyToClipboard('#webhook_url', this);
        });

        //License Manager
        $(document).ready(function(){
            var status = $('.checkbox').val();
            licensebox=document.getElementById('License').value;
            if(licensebox ==1) {
                $('#License').prop('checked', true);


            } else if(licensebox ==0) {

            }
        });
        $('#license_apiCheck').hide();
        $('#License').on('change',function () {
            if ($(this).prop("checked")) {


            }
            else{
                $('.nocapsecretHide').val('');
                $('.siteKeyHide').val('');


        }
    });

        function licenseDetails(e){

            if ($('#License').prop("checked")) {
                var checkboxvalue = 1;
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

            if ($('#license_api_url').val() != '') {
                if (isValid && !isValidURL(userFields.type.val())) {
                    showError(userFields.type, @json(__('message.cloud_hub_valid_url')));
                    isValid = false;
                }
            }
                // If validation fails, prevent form submission
                if (!isValid) {
                    e.preventDefault();
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
                        setTimeout(function () {
                            location.reload();
                        }, 3000);
                        $('#alertMessage').show();
                        var result = '<div class="alert alert-success alert-dismissable"><button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button><strong><i class="fa fa-check"></i> {{ __('message.success') }}! </strong>' + response.message + '.</div>';
                        $('#alertMessage').html(result);
                        $("#submit").html("<i class='fa fa-save'>&nbsp;&nbsp;</i>{{ __('message.save') }}");
                        setInterval(function () {
                            $('#alertMessage').slideUp(3000);
                        }, 1000);
                },
                error:function(response){
                    $('#alertMessage').show();
                    var result = '<div class="alert alert-danger alert-dismissable"><button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button><strong><i class="fa fa-ban"></i> {{ __('message.error') }} </strong>' + response.responseJSON.message + '</div>';
                    $('#alertMessage').html(result);
                    $("#submit").html("<i class='fa fa-save'>&nbsp;&nbsp;</i>Save");
                    setInterval(function () {
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
        'zoho_key','pipedrive_key','terms_url','mailchimp_authkey','list_id','subscribe_status'].forEach(id => {

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
                    $('#update_apiCheck').html(@json(__('message.enter_api_secret_key')));
                    $('#update_api_secret').css("border-color","red");
                    $('#update_apiCheck').css({"color":"red","margin-top":"5px"});
                    return false;
                }
                if ($('#update_api_url').val() == '' ) {
                    alert('df');
                    $('#update_urlCheck').show();
                    $('#update_urlCheck').html(@json(__('message.enter_api_url')));
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
                    setTimeout(function() {
                        location.reload();
                    }, 3000);
                    $('#alertMessage').show();
                    var result =  '<div class="alert alert-success alert-dismissable"><button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button><strong><i class="fa fa-check"></i> {{ __('message.success') }}! </strong>'+response.update+'.</div>';
                    $('#alertMessage').html(result);
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
            } else if(status ==0) {
            }
        });

        function captchaDetails(e){
      
            if ($('#captcha').prop("checked")) {
                var checkboxvalue = 1;

            }
            else{
                var checkboxvalue = 0;
            }

            const userRequiredFields = {
                name:@json(trans('message.secret_key')),
                type:@json(trans('message.site_key')),
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
                field.next().after(`<span class='error invalid-feedback' id='captchaError'>${message}</span>`);
            };

            // Validate required fields
            Object.keys(userFields).forEach(field => {
                if (!userFields[field].val()) {
                    showError(userFields[field], userRequiredFields[field]);
                    isValid = false;
                }
            });


            var recaptchaType='';
            if($('#captchaRadioV2').prop('checked')){
                recaptchaType='v2';
            }
            if($('#captchaRadioV3').prop('checked')){
                recaptchaType='v3';
            }
            if(recaptchaType!=='v2' && recaptchaType!=='v3'){
                document.getElementById('checkboxerror').textContent=@json(trans('message.recaptcha_type_error'));
                isValid = false;
            }

            // If validation fails, prevent form submission
            if (!isValid) {
                document.getElementById("status").textContent='';
                e.preventDefault();
            }

            $("#submit2").html("<i class='fas fa-circle-notch fa-spin'></i>  {{ __('message.please_wait') }}");
            $.ajax({

                url : '{{url("captchaDetails")}}',
                type : 'post',
                data: {
                    "status": checkboxvalue,
                    "recaptcha_type": recaptchaType,
                    "nocaptcha_sitekey": $('#nocaptcha_sitekey').val(),
                    "nocaptcha_secret" :$('#nocaptcha_secret').val(),
                },
                success: function (data) {
                    setTimeout(function() {
                        location.reload();
                    }, 3000);
                    $('#alertMessage2').show();
                    var result =  '<div class="alert alert-success alert-dismissable"><button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button><strong><i class="fa fa-check"></i> {{ __('message.success') }}! </strong>'+data.message+'.</div>';
                    $('#alertMessage2').html(result);
                    $("#submit2").html("<i class='fa fa-save'>&nbsp;&nbsp;</i>{{ __('message.save') }}");
                    setInterval(function(){
                        $('#alertMessage2').slideUp(3000);
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
                    $('#v3captcha_secretCheck').html(@json(__('message.enter_secret_key')));
                    $('#captcha_secret').css("border-color","red");
                    $('#v3captcha_secretCheck').css({"color":"red","margin-top":"5px"});
                    return false;
                }
                if ($('#captcha_sitekey').val() =="" ) {
                    $('#captcha_sitekeyCheck').show();
                    $('#captcha_sitekeyCheck').html(@json(__('message.enter_secret_key')));
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
                    $('#alertMessage').html(result);
                    $("#submit3").html("<i class='fa fa-save'>&nbsp;&nbsp;</i>{{ __('message.save') }}");
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

            } else if(mobilestatus ==0){
                $('#mobile').prop('checked',false);

            }
        });
        $("#mobile").on('change',function (){
            if($(this).prop('checked')) {
                var mobilekey =  $('#hiddenMobValue').val();
                var sender =  $('#hiddenSender').val();
                var template =  $('#hiddenTemplate').val();
            }
        });
        //Validate and pass value through ajax
        $("#submit3").on('click', function () {
            if ($('#mobile').prop('checked')) { // If checkbox is checked
                var mobilestatus = 1;


            } else {

                mobilestatus = 0;
            }



            const userRequiredFields = {
                name:@json(trans('message.mobile_authkey')),
                type:@json(trans('message.sender')),
                template:@json(trans('message.templateId')),
                template1:@json(trans('message.third_party_key_error')),

            };

            const userFields = {
                name:$('#mobile_authkey'),
                type:$('#sender'),
                template:$('#template_id'),
                template1:$('#third_party_key'),

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




            // If validation fails, prevent form submission
            if (!isValid) {
               e.preventDefault();
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
                    'thirdPartyId':$('#third_party_key').val(),
                },
                success: function (data) {
                    setTimeout(function() {
                        location.reload();
                    }, 3000);
                    const result = `
                <div class="alert alert-success alert-dismissable">
                    <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
                    <strong><i class="fa fa-check"></i> {{ __('message.success') }}! </strong>${data.message}.
                </div>`;
                    $('#alertMessage3').show().html(result);
                    $("#submit3").html("<i class='fa fa-save'>&nbsp;&nbsp;</i>{{ __('message.save') }}");


                    setInterval(function(){
                        $('#alertMessage3').slideUp(3000);
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
                    setTimeout(function() {
                        location.reload();
                    }, 3000);
                    $('#alertMessage').show();
                    var result =  '<div class="alert alert-success alert-dismissable"><button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button><strong><i class="fa fa-check"></i> {{ __('message.success') }}! </strong>'+data.update+'.</div>';
                    $('#alertMessage').html(result);
                    $("#submit4").html("<i class='fa fa-save'>&nbsp;&nbsp;</i>{{ __('message.save') }}");
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


            } else if(twitterstatus ==0){
                $('#twitter').prop('checked',false);

            }
        });



        //Validate and pass value through ajax
        $("#submit5").on('click',function (){ //When Submit button is clicked
            if ($('#twitter').prop('checked')) {//if button is on
                var twitterstatus = 1;

            } else {

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




            // If validation fails, prevent form submission
            if (!isValid) {
                preventDefault();
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
                    setTimeout(function() {
                        location.reload();
                    }, 3000);
                    $('#alertMessage6').show();
                    var result =  '<div class="alert alert-success alert-dismissable"><button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button><strong><i class="fa fa-check"></i> {{ __('message.success') }}! </strong>'+data.update+'.</div>';
                    $('#alertMessage6').html(result);
                    $("#submit5").html("<i class='fa fa-save'>&nbsp;</i>{{ __('message.save') }}");
                    setInterval(function(){
                        $('#alertMessage6').slideUp(3000);
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

        //Validate and pass value through ajax
        $("#submit7").on('click',function (){ //When Submit button is checked
            if ($('#zoho').prop('checked')) {//if button is on
                var zohostatus = 1;

            } else {

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


            // If validation fails, prevent form submission
            if (!isValid) {
                preventDefault();
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
                    setTimeout(function() {
                        location.reload();
                    }, 3000);
                    $('#alertMessage7').show();
                    var result =  '<div class="alert alert-success alert-dismissable"><button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button><strong><i class="fa fa-check"></i> {{ __('message.success') }}! </strong>'+data.update+'.</div>';
                    $('#alertMessage7').html(result);
                    $("#submit7").html("<i class='fa fa-save'>&nbsp;&nbsp;</i>{{ __('message.save') }}");
                    setInterval(function(){
                        $('#alertMessage7').slideUp(3000);
                    }, 1000);
                },
            })
        });
 <!--------------------------------------------------------------------------------------------->
        /*
       *Mailchimp
        */
        //Validate and pass value through ajax
        $("#submit9").on('click',function (){ //When Submit button is checked
            if ($('#mailchimp').prop('checked')) {//if button is on
                var chimpstatus = 1;

            } else {

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

            // If validation fails, prevent form submission
            if (!isValid) {
                preventDefault();
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
                    document.getElementById('mailchimp_authkey').disabled=true;
                    var mailchimpstatus=data['data']['mailchimpverifiedStatus'];
                    var status=data['data']['status'];
                    if(mailchimpstatus===1){
                        let extraInput = document.getElementById('extraInput');
                        extraInput.style.display ='block';
                        let extraInput5 = document.getElementById('extraInput5');
                        extraInput5.style.display ='none';
                        let extraInput1 = document.getElementById('extraInput1');
                        extraInput1.style.display ='block';
                    }else{
                       let extraInput = document.getElementById('extraInput');
                       extraInput.style.display ='none';
                        let extraInput1 = document.getElementById('extraInput1');
                        extraInput1.style.display ='none';
                        let extraInput5 = document.getElementById('extraInput5');
                        extraInput5.style.display ='block';
                   }
                        var value1=data['data']['allLists'][0]['id'];
                        var name1=data['data']['allLists'][0]['name'];
                        var value2=data['data']['subscribe_status'];

                        if($('#list_id').val()!==value1) {

                            const options = [
                                {value: value1, text: name1},
                            ];

                            const select = document.getElementById('list_id');

                            options.forEach(optionData => {
                                const option = document.createElement('option');
                                option.value = optionData.value;
                                option.text = optionData.text;
                                select.appendChild(option);
                            });
                        }else{
                            let extraInput5 = document.getElementById('extraInput9');
                            extraInput5.style.display ='block';
                        }
                        const select1 = document.getElementById('subscribe_status');
                        select1.value=value2;


                    $('#alertMessage4').show();
                    var result =  '<div class="alert alert-success alert-dismissable"><button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button><strong><i class="fa fa-check"></i> {{ __('message.success') }}! </strong>'+data.message+'.</div>';
                    $('#alertMessage4').html(result);
                    $("#submit9").html("<i class='fa fa-save'>&nbsp;</i>{{ __('message.save') }}");
                    setInterval(function(){
                        $('#alertMessage4').slideUp(3000);
                    }, 1000);
                },
                error: function(data){
                    $('#alertMessage4').show();
                    var result =  '<div class="alert alert-danger alert-dismissable"><button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button><strong><i class="fa fa-ban"></i> {{ __('message.error') }} </strong>'+data.responseJSON.message+'.</div>';
                    $('#alertMessage4').html(result);
                    $("#submit9").html("<i class='fa fa-save'>&nbsp;</i>{{ __('message.save') }}");
                    setInterval(function(){
                        $('#alertMessage4').slideUp(3000);
                    }, 1000);
                },
            })
        });

        $("#submit-chimp").on('click',function () { //When Submit button is checked



            const userRequiredFields = {
                name:@json(trans('message.list_id_error')),
                type:@json(trans('message.subscribe_status_error')),

            };

            const userFields = {
                name:$('#list_id'),
                type:$('#subscribe_status'),

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

            // If validation fails, prevent form submission
            if (!isValid) {
                preventDefault();
            }


            var list_id=$('#list_id').val();
            var subscribe_status=$('#subscribe_status').val();
            $("#submit-chimp").html("<i class='fas fa-circle-notch fa-spin'></i>  {{ __('message.please_wait') }}");
            $.ajax ({
                url: '{{url("mailchimp")}}',
                type : 'patch',
                data: {
                    'list_id':list_id,
                    'subscribe_status':subscribe_status,
                },
                success: function (data) {
                    var list_id=data['data']['list_id'];
                    if(list_id===1){
                        let extraInput = document.getElementById('extraInput9');
                        extraInput.style.display ='block';
                    }else{
                        let extraInput1 = document.getElementById('extraInput9');
                        extraInput1.style.display ='none';
                    }
                    if(data['success']===true){

                        $('#alertMessage4').show();
                        var result =  '<div class="alert alert-success alert-dismissable"><button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button><strong><i class="fa fa-check"></i> {{ __('message.success') }}! </strong>'+data.message+'.</div>';
                        $('#alertMessage4').html(result);
                        $("#submit-chimp").html("<i class='fa fa-save'>&nbsp;</i>{{ __('message.save') }}");
                        setInterval(function(){
                            $('#alertMessage4').slideUp(3000);
                        }, 1000);}
                        else{
                        $('#alertMessage4').show();
                        var result =  '<div class="alert alert-danger alert-dismissable"><button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button><strong><i class="fa fa-ban"></i> {{ __('message.error') }} </strong>'+data.message+'.</div>';
                        $('#alertMessage4').html(result);
                        $("#submit-chimp").html("<i class='fa fa-save'>&nbsp;</i>{{ __('message.save') }}");
                        setInterval(function(){
                            $('#alertMessage4').slideUp(3000);
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

        //Validate and pass value through ajax
        $("#submit10").on('click',function (){ //When Submit button is checked
            if ($('#terms').prop('checked')) {//if button is on
                var termsstatus = 1;

            } else {

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

            if($('#term_url').val()!=''){
            if (isValid && !isValidURL(userFields.name.val())) {
                showError(userFields.name, '{{ __("message.cloud_hub_valid_url") }}');
                isValid = false;
            }
            }


            // If validation fails, prevent form submission
            if (!isValid) {
                preventDefault();
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
                        setTimeout(function () {
                            location.reload();
                        }, 3000);
                        $('#alertMessage5').show();
                        var result = '<div class="alert alert-success alert-dismissable"><button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button><strong><i class="fa fa-check"></i> {{ __('message.success') }}! </strong>' + data.message + '.</div>';
                        $('#alertMessage5').html(result);
                        $("#submit10").html("<i class='fa fa-save'>&nbsp;&nbsp;</i>{{ __('message.save') }}");
                        setInterval(function () {
                            $('#alertMessage5').slideUp(3000);
                        }, 1000);

                },
                error: function(data){
                    $('#alertMessage5').show();
                    var result = '<div class="alert alert-danger alert-dismissable"><button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button><strong><i class="fa fa-ban"></i> {{ __('message.error') }} </strong>' + data.responseJSON.message + '</div>';
                    $('#alertMessage5').html(result);
                    $("#submit10").html("<i class='fa fa-save'>&nbsp;&nbsp;</i>{{ __('message.save') }}");
                    setInterval(function () {
                        $('#alertMessage5').slideUp(3000);
                    }, 1000);
                },
            })
        });
        function isValidURL(url) {
            try {
                new URL(url);
                return true;
            } catch (e) {
                return false;
            }
        }
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

        //Validate and pass value through ajax
        $("#submit13").on('click',function (){ //When Submit button is checked
            if ($('#pipedrive').prop('checked')) {//if button is on
                var pipedrivestatus = 1;

            } else {

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



            // If validation fails, prevent form submission
            if (!isValid) {
                preventDefault();
            }




            $("#submit13").html("<i class='fas fa-circle-notch fa-spin'></i>  {{ __('message.please_wait') }}");
            $.ajax ({
                url: '{{url("updatepipedriveDetails")}}',
                type : 'post',
                data: {
                    "status": pipedrivestatus,
                    "pipedrive_key": $('#pipedrive_key').val(),
                    "require_pipedrive_user_verification": $('#pipedrive_key_status').val(),
                },
                success: function (data) {
                        setTimeout(function () {
                            location.reload();
                        }, 3000);
                        $('#alertMessage8').show();
                        var result = '<div class="alert alert-success alert-dismissable"><button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button><strong><i class="fa fa-check"></i> {{ __('message.success') }}! </strong>' + data.message + '.</div>';
                        $('#alertMessage8').html(result);
                        $("#submit13").html("<i class='fa fa-save'>&nbsp;&nbsp;</i>{{ __('message.save') }}");
                        setInterval(function () {
                            $('#alertMessage8').slideUp(3000);
                        }, 1000);
                },
                error: function(data){
                    $('#alertMessage8').show();
                    var result = '<div class="alert alert-danger alert-dismissable"><button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button><strong><i class="fa fa-ban"></i> {{ __('message.error') }} </strong>' + data.responseJSON.message + '.</div>';
                    $('#alertMessage8').html(result);
                    $("#submit13").html("<i class='fa fa-save'>&nbsp;&nbsp;</i>{{ __('message.save') }}");
                    setInterval(function () {
                        $('#alertMessage8').slideUp(3000);
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
                    $('#alertMessage').html(result);
                    $("#submit14").html("<i class='fa fa-save'>&nbsp;</i>{{ __('message.save') }}");
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


            } else if(githubstatus ==0){
                $('#github').prop('checked',false);

            }
        });



        //Validate and pass value through ajax
        $("#submit").on('click',function (){ //When Submit button is clicked
            if ($('#github').prop('checked')) {//if button is on
                var githubstatus = 1;

            } else {

                var githubstatus = 0;
            }



            const userRequiredFields = {
                name:@json(trans('message.git_username_s')),
                type:@json(trans('message.git_password_s')),

            };

            const userFields = {
                name:$('#git_username'),
                type:$('#git_password'),


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

            // If validation fails, prevent form submission
            if (!isValid) {
                preventDefault();
            }

            $("#submit").html("<i class='fas fa-circle-notch fa-spin'></i>  {{ __('message.please_wait') }}");
            $.ajax ({
                url: '{{url("github-setting")}}',
                type : 'post',
                data: {
                    "status": githubstatus,
                    "git_username": $('#git_username').val(),"git_password" : $('#git_password').val() ,

                },
                success: function (data) {
                        setTimeout(function () {
                            location.reload();
                        }, 3000);
                        $('#alertMessage1').show();
                        var result = '<div class="alert alert-success alert-dismissable"><button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button><strong><i class="fa fa-check"></i> {{ __('message.success') }}! </strong>' + data.message + '</div>';
                        $('#alertMessage1').html(result);
                        $("#submit").html("<i class='fa fa-save'>&nbsp;</i>{{ __('message.save') }}");
                        setInterval(function () {
                            $('#alertMessage1').slideUp(3000);
                        }, 1000);
                },
                error:function(data){
                    $('#alertMessage1').show();
                    var result = '<div class="alert alert-danger alert-dismissable"><button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button><strong><i class="fa fa-ban"></i> {{ __('message.error') }} </strong>' + data.responseJSON.message + '</div>';
                    $('#alertMessage1').html(result);
                    $("#submit").html("<i class='fa fa-save'>&nbsp;</i>{{ __('message.save') }}");
                    setInterval(function () {
                        $('#alertMessage1').slideUp(2000);
                    }, 6000);
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