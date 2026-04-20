@extends('themes.default1.layouts.master')
@section('title')
    {{ __('message.settings') }}
@stop
@section('content-header')
    <div class="col-sm-6">
        <h1>{{ __('message.application_settings') }}</h1>
    </div>
    <div class="col-sm-6">
        <ol class="breadcrumb float-sm-right">
            <li class="breadcrumb-item"><a href="{{url('/')}}"><i class="fa fa-dashboard"></i> {{ __('message.home') }}
                </a></li>
            <li class="breadcrumb-item active">{{ __('message.settings') }}</li>
        </ol>
    </div><!-- /.col -->
@stop
@section('content')
    <style scoped>

        .icons-color {
            color: #3c8dbc;
        }

        .settingiconblue {
            padding-top: 0.5rem;
            padding-bottom: 0.5rem;
        }

        .settingdivblue {
            display: flex;
            align-items: center;
            justify-content: center;
            height: 100%;
            width: 80px;
            margin: 0 auto;
            text-align: center;
            border: 5px solid #C4D8E4;
            border-radius: 100%;
            padding-top: 5px;
        }

        .settingdivblue span {
            text-align: center;
        }


        .fw_400 { font-weight: 400; }

        .settingiconblue p{
            text-align: center;
            font-size: 16px;
            word-wrap: break-word;
            font-variant: small-caps;
            font-weight: 500;
            line-height: 30px;
        }
    </style>
    <div class="card card-secondary card-outline">

        <!-- /.box-header -->
        <div class="card-header">
            <h3 class="card-title">{{ __('message.settings') }}</h3>
        </div>
        <div class="card-body">
            <div class="row">
                <!--/.col-md-2-->
                <div class="col-md-2 col-sm-6">
                    <div class="settingiconblue">
                        <div class="settingdivblue">
                            <a class="icons-color" href="{{ url('settings/system') }}">
                                <span class="fa-stack fa-2x">
                                    <i class="fa fa-laptop fa-stack-1x"></i>
                                </span>
                            </a>
                        </div>
                        <div class="text-center text-sm fw_400">{{ __('message.system-settings') }}</div>
                    </div>
                </div>


                <div class="col-md-2 col-sm-6">
                    <div class="settingiconblue">
                        <div class="settingdivblue">
                            <a class="icons-color" href="{{url('job-scheduler')}}">
                                <span class="fa-stack fa-2x">
                                    <i class="fas fa-tachometer-alt fa-stack-1x"></i>
                                </span>
                            </a>
                        </div>
                        <div class="text-center text-sm fw_400">{!! Lang::get('message.cron') !!}</div>
                    </div>
                </div>


                <div class="col-md-2 col-sm-6">
                    <div class="settingiconblue">
                        <div class="settingdivblue">
                            <a class="icons-color" href="{{url('license-type')}}">
                                <span class="fa-stack fa-2x">
                                    <i class="fa fa-file fa-stack-1x"></i>
                                </span>
                            </a>
                        </div>
                        <div class="text-center text-sm fw_400">{{ __('message.lic_type') }}</div>
                    </div>
                </div>

                <div class="col-md-2 col-sm-6">
                    <div class="settingiconblue">
                        <div class="settingdivblue">
                            <a class="icons-color" href="{{url('license-permissions')}}">
                                <span class="fa-stack fa-2x">
                                    <i class="fa fa-sitemap fa-stack-1x"></i>
                                </span>
                            </a>
                        </div>
                        <div class="text-center text-sm fw_400">{{ __('message.license_permission') }}</div>
                    </div>
                </div>

                <div class="col-md-2 col-sm-6">
                    <div class="settingiconblue">
                        <div class="settingdivblue">
                            <a class="icons-color" href="{{url('file-storage')}}">
                                <span class="fa-stack fa-2x">
                                    <i class="fas fa-file-archive fa-stack-1x"></i>
                                </span>
                            </a>
                        </div>
                        <div class="text-center text-sm fw_400">{{ __('message.file_storage') }}</div>
                    </div>
                </div>

                <div class="col-md-2 col-sm-6">
                    <div class="settingiconblue">
                        <div class="settingdivblue">
                            <a class="icons-color" href="{{ url('payment-gateway-integration') }}">
                                <span class="fa-stack fa-2x">
                                    <i class="fa fa-credit-card fa-stack-1x"></i>
                                </span>
                            </a>
                        </div>
                        <div class="text-center text-sm fw_400">{{ __('message.payment_gateway_integrations') }}</div>
                    </div>
                </div>

                <div class="col-md-2 col-sm-6">
                    <div class="settingiconblue">
                        <div class="settingdivblue">
                            <a class="icons-color" href="{{url('system-managers')}}">
                                <span class="fa-stack fa-2x">
                                    <i class="fa fa-users fa-stack-1x"></i>
                                </span>
                            </a>
                        </div>
                        <div class="text-center text-sm fw_400">{{ __('message.system_manager') }}</div>
                    </div>
                </div>

                <div class="col-md-2 col-sm-6">
                    <div class="settingiconblue">
                        <div class="settingdivblue">
                            <a class="icons-color" href="{{url('third-party-keys')}}">
                                <span class="fa-stack fa-2x">
                                    <i class="fas fa-file-signature fa-stack-1x"></i>
                                </span>
                            </a>
                        </div>
                        <div class="text-center text-sm fw_400">{{ __('message.third_party_apps') }}</div>
                    </div>
                </div>

                <div class="col-md-2 col-sm-6">
                    <div class="settingiconblue">
                        <div class="settingdivblue">
                            <a class="icons-color" href="{{url('view/tenant')}}">
                                <span class="fa-stack fa-2x">
                                    <i class="fa fa-cloud fa-stack-1x"></i>
                                </span>
                            </a>
                        </div>
                        <div class="text-center text-sm fw_400">{{ __('message.cloud_hub') }}</div>
                    </div>
                </div>

                <!--/.col-md-2-->
                <div class="col-md-2 col-sm-6">
                    <div class="settingiconblue">
                        <div class="settingdivblue">
                            <a class="icons-color" href="{{url('LocalizedLicense')}}">
                                <span class="fa-stack fa-2x">
                                    <i class="fa fa-file-word fa-stack-1x"></i>
                                </span>
                            </a>
                        </div>
                        <div class="text-center text-sm fw_400">{{ __('message.localized_license') }}</div>
                    </div>
                </div>


                <!--/.col-md-2-->
                <div class="col-md-2 col-sm-6">
                    <div class="settingiconblue">
                        <div class="settingdivblue">
                            <a class="icons-color" href="{{url('debugg')}}">
                                <span class="fa-stack fa-2x">
                                   <i class="fa fa-bug fa-stack-1x"></i>
                                </span>
                            </a>
                        </div>
                        <div class="text-center text-sm fw_400">{{ __('message.debug') }}</div>
                    </div>
                </div>
                @if(config('clockwork.enable'))
                    <div class="col-md-2 col-sm-6">
                        <div class="settingiconblue">
                            <div class="settingdivblue">
                                <a class="icons-color" href="javascript:;" onclick="checkMonitoring('clockwork')">
                                <span class="fa-stack fa-2x">
                                   <i class="fa fa-clock fa-stack-1x"></i>
                                </span>
                                </a>
                            </div>
                            <div class="text-center text-sm fw_400">{{ __('message.clockwork') }}</div>
                        </div>
                    </div>
                @endif
                @if(config('pulse.enabled'))
                    <div class="col-md-2 col-sm-6">
                        <div class="settingiconblue">
                            <div class="settingdivblue">
                                <a class="icons-color" href="javascript:;" onclick="checkMonitoring('pulse')">
                                <span class="fa-stack fa-2x">
                                    <i class="fas fa-heartbeat fa-stack-1x"></i>
                                </span>
                                </a>
                            </div>
                            <div class="text-center text-sm fw_400">{{ __('message.pulse') }}</div>
                        </div>
                    </div>
                @endif
                <!--/.col-md-2-->
                <div class="col-md-2 col-sm-6">
                    <div class="settingiconblue">
                        <div class="settingdivblue">
                            <a class="icons-color" href="{{url('social-logins')}}">
                            <span class="fa-stack fa-2x">
                                <i class="fas fa-globe fa-stack-1x"></i>
                            </span>
                            </a>
                        </div>
                        <div class="text-center text-sm fw_400">{{ __('message.social_logins') }}</div>
                    </div>
                </div>

                <div class="col-md-2 col-sm-6">
                    <div class="settingiconblue">
                        <div class="settingdivblue">
                            <a class="icons-color" href="{{url('languages')}}">
                            <span class="fa-stack fa-2x">
                                <i class="fas fa-language fa-stack-1x"></i>
                            </span>
                            </a>
                        </div>
                        <div class="text-center text-sm fw_400">{{ __('message.language') }}</div>
                    </div>
                </div>
                <div class="col-md-2 col-sm-6">
                    <div class="settingiconblue">
                        <div class="settingdivblue">
                            <a class="icons-color" href="{{url('whatsapp-users')}}">
                            <span class="fa-stack fa-2x">
                            <i class="fab fa-whatsapp fa-stack-1x" style="font-size: 1.4em;"></i>
                            </span>
                            </a>
                        </div>
                        <div class="text-center text-sm fw_400">Whatsapp-Users</div>
                    </div>
                </div>
                @if($mailSendingStatus==1)
                    <div class="col-md-2 col-sm-6">
                        <div class="settingiconblue">
                            <div class="settingdivblue">
                                <a class="icons-color" href="{{url('contact-option')}}">
                            <span class="fa-stack fa-2x">
                                <i class="fas fa-address-book fa-stack-1x"></i>
                            </span>
                                </a>
                            </div>
                            <div class="text-center text-sm fw_400">{{ __('message.contact_options') }}</div>
                        </div>
                    </div>
                @endif

            </div>

        </div>


        <!-- /.row -->

        <!-- ./box-body -->
    </div>
    <!-- /.box -->

    <div class="card card-secondary card-outline">
        <div class="card-header">
            <h3 class="card-title">{{Lang::get('message.logs')}}</h3>
        </div>
        <!-- /.box-header -->
        <div class="card-body">
            <div class="row">
                <!--/.col-md-2-->

                <div class="col-md-2 col-sm-6">
                    <div class="settingiconblue">
                        <div class="settingdivblue">
                            <a class="icons-color" href="{{ url('system-logs') }}">
                                <span class="fa-stack fa-2x">
                                    <i class="fa fa-history fa-stack-1x"></i>
                                </span>
                            </a>
                        </div>
                        <div class="text-center text-sm fw_400">{{ __('message.log_setting') }}</div>
                    </div>
                </div>

                <div class="col-md-2 col-sm-6">
                    <div class="settingiconblue">
                        <div class="settingdivblue">
                            <a class="icons-color" href="{{ url('settings/activitylog') }}">
                                <span class="fa-stack fa-2x">
                                    <i class="fas fa-clipboard-list"></i>
                                </span>
                            </a>
                        </div>
                        <div class="text-center text-sm fw_400">{{ __('message.activity_log') }}</div>
                    </div>
                </div>

                <div class="col-md-2 col-sm-6">
                    <div class="settingiconblue">
                        <div class="settingdivblue">
                            <a class="icons-color" href="{{ url('settings/paymentlog') }}">
                                <span class="fa-stack fa-2x">
                                    <i class="fa fa-money-check-alt fa-stack-1x"></i>
                                </span>
                            </a>
                        </div>
                        <div class="text-center text-sm fw_400">{{ __('message.payment_log') }}</div>
                    </div>
                </div>

                <?php
                $isMobileVerificationEnabled = \App\Model\Common\StatusSetting::first()->value('msg91_status');
                ?>
                @if($isMobileVerificationEnabled)
                    <div class="col-md-2 col-sm-6">
                        <div class="settingiconblue">
                            <div class="settingdivblue">
                                <a class="icons-color" href="{{ url('sms/reports') }}">
                                <span class="fa-stack fa-2x">
                                    <i class="fas fa-comments"></i>
                                </span>
                                </a>
                            </div>
                            <div class="text-center text-sm fw_400">{{ __('message.msg91_reports') }}</div>
                        </div>
                    </div>
                @endif
            </div>
            <!-- /.row -->
        </div>
        <!-- ./box-body -->
    </div>



    <div class="card card-secondary card-outline">
        <div class="card-header">
            <h3 class="card-title">{{ __('message.email') }}</h3>
        </div>
        <!-- /.box-header -->
        <div class="card-body">
            <div class="row">
                <!--col-md-2-->
                <div class="col-md-2 col-sm-6">
                    <div class="settingiconblue">
                        <div class="settingdivblue">
                            <a class="icons-color" href="{{ url('settings/email') }}">
                                <span class="fa-stack fa-2x">
                                    <i class="fa fa-envelope fa-stack-1x"></i>
                                </span>
                            </a>
                        </div>
                        <div class="text-center text-sm fw_400">{{ __('message.email_settings') }}</div>
                    </div>
                </div>
                <!--/.col-md-2-->
                <!--col-md-2-->
                <div class="col-md-2 col-sm-6">
                    <div class="settingiconblue">
                        <div class="settingdivblue">
                            <a class="icons-color" href="{{ url('settings/template') }}">
                                <span class="fa-stack fa-2x">
                                    <i class="fa fa-folder fa-stack-1x"></i>
                                </span>
                            </a>
                        </div>
                        <div class="text-center text-sm fw_400">{{ __('message.template_settings') }}</div>
                    </div>
                </div>
                <!--/.col-md-2-->
                <!--/.col-md-2-->
                <div class="col-md-2 col-sm-6">
                    <div class="settingiconblue">
                        <div class="settingdivblue">
                            <a class="icons-color" href="{{url('template')}}">
                                <span class="fa-stack fa-2x">
                                    <i class="fas fa-file-alt fa-stack-1x"></i>
                                </span>
                            </a>
                        </div>
                        <div class="text-center text-sm fw_400">{{ __('message.templates') }}</div>
                    </div>
                </div>
                <!--/.col-md-2-->


                @if($isRedisConfigured)
                    <div class="col-md-2 col-sm-6">
                        <div class="settingiconblue">
                            <div class="settingdivblue">
                                <a class="icons-color" href="javascript:;" onclick="checkMonitoring('horizon')">
                                <span class="fa-stack fa-2x">
                                    <i class="fa fa-desktop fa-stack-1x"></i>
                                </span>
                                </a>
                            </div>
                            <div class="text-center text-sm fw_400">{{ __('message.queue_monitoring') }}</div>
                        </div>
                    </div>
                @endif

            </div>
            <!-- /.row -->
        </div>
        <!-- ./box-body -->
    </div>

    <div class="card card-secondary card-outline">
        <div class="card-header">
            <h3 class="card-title">{{ __('message.api') }}</h3>
        </div>
        <!-- /.box-header -->
        <div class="card-body">
            <div class="row">
                <!--col-md-2-->
                {{--                <div class="col-md-2 col-sm-6">--}}
                {{--                    <div class="settingiconblue">--}}
                {{--                        <div class="settingdivblue">--}}
                {{--                            <a class="icons-color" href="{{ url('github') }}">--}}
                {{--                                <span class="fa-stack fa-2x">--}}
                {{--                                    <i class="fab fa-github-square fa-stack-1x"></i>--}}
                {{--                                </span>--}}
                {{--                            </a>--}}
                {{--                        </div>--}}
                {{--                        <div class="text-center text-sm fw_400">Github</div>--}}
                {{--                    </div>--}}
                {{--                </div>--}}
                <!--/.col-md-2-->
                <!--col-md-2-->
                <?php
                $mailchimpStatus = \App\Model\Common\StatusSetting::first()->value('mailchimp_status');
                $pipedriveStatus = \App\Model\Common\StatusSetting::first()->value('pipedrive_status');
                $groupId = \App\Model\Common\PipedriveGroups::where('group_name', 'Person')->value('id');
                $recaptchaStatus = \App\Model\Common\StatusSetting::first()->value('recaptcha_status');
                ?>
                @if($pipedriveStatus == 1)
                    <div class="col-md-2 col-sm-6">
                        <div class="settingiconblue">
                            <div class="settingdivblue">
                                <a class="icons-color" href="{{ url('pipedrive/mapping/'. $groupId) }}">
                                <span class="fa-stack fa-2x">
                                    {{--pipedrive svg--}}
                                   <svg width="48px" height="48px" viewBox="0 0 304 304"
                                        xmlns="http://www.w3.org/2000/svg"><g fill="none" fill-rule="evenodd"><g
                                                   transform="translate(67, 44)"><path fill="#3c8dbc"
                                                                                       d="M59.68,81.18c0,20.36,10.33,42.32,33.05,42.32c16.86,0,33.9-13.16,33.9-42.62c0-25.83-13.4-43.17-33.33-43.17c-16.25,0-33.6,11.41-33.6,43.17ZM101.3,0c40.75,0,68.15,32.27,68.15,80.31c0,47.29-28.87,80.31-69.13,80.31c-19.67,0-32.27-8.43-38.87-14.52c0.05,1.45,0.08,3.07,0.08,4.8v64.12H18.33V44.16c0-2.48-0.8-3.29-3.24-3.29H0.55V3.47h35.42c16.31,0,20.49,8.3,21.28,14.7C63.87,10.75,77.59,0,101.3,0Z"/></g></g></svg>
                                </span>
                                </a>
                            </div>
                            <div class="text-center text-sm fw_400">Pipedrive</div>
                        </div>
                    </div>
                @endif

            @if( $recaptchaStatus == 1 )
            <div class="col-md-2 col-sm-6">
                <div class="settingiconblue">
                    <div class="settingdivblue">
                        <a class="icons-color" href="{{ url('recaptcha') }}">
                <span class="fa-stack fa-2x">
                    {{--Shield icon for reCAPTCHA--}}
                   <i class="fas fa-shield-alt"></i>
                </span>
                        </a>
                    </div>
                    <div class="text-center text-sm fw_400">reCAPTCHA</div>
                </div>
            </div>
            @endif

            <!--/.col-md-2-->
                <div class="col-md-2 col-sm-6">
                    <div class="settingiconblue">
                        <div class="settingdivblue">
                            <a class="icons-color" href="{{ url('third-party-integration') }}">
                                <span class="fa-stack fa-2x">
                                    <i class="fa fa-cogs fa-stack-1x"></i>
                                </span>
                            </a>
                        </div>
                        <div class="text-center text-sm fw_400">{{ __('message.third_party_integrations') }}</div>
                    </div>
                </div>
            </div>
            <!-- /.row -->
        </div>
        <!-- ./box-body -->
    </div>

    <div class="card card-secondary card-outline">
        <div class="card-header">
            <h3 class="card-title">{{ __('message.common') }}</h3>
        </div>
        <!-- /.box-header -->
        <div class="card-body">
            <div class="row">
                <!--/.col-md-2-->
                <div class="col-md-2 col-sm-6">
                    <div class="settingiconblue">
                        <div class="settingdivblue">
                            <a class="icons-color" href="{{url('tax')}}">
                                <span class="fa-stack fa-2x">
                                    <i class="fas fa-money-check-alt fa-stack-1x"></i>
                                </span>
                            </a>
                        </div>
                        <div class="text-center text-sm fw_400">{{ __('message.tax') }}</div>
                    </div>
                </div>
                <!--/.col-md-2-->
                <!--/.col-md-2-->
                <div class="col-md-2 col-sm-6">
                    <div class="settingiconblue">
                        <div class="settingdivblue">
                            <a class="icons-color" href="{{url('currency')}}">
                                <span class="fa-stack fa-2x">
                                    <i class="fas fa-dollar-sign fa-stack-1x"></i>
                                </span>
                            </a>
                        </div>
                        <div class="text-center text-sm fw_400">{{ __('message.currency') }}</div>
                    </div>
                </div>
                <!--/.col-md-2-->
                <div class="col-md-2 col-sm-6">
                    <div class="settingiconblue">
                        <div class="settingdivblue">
                            <a class="icons-color" href="{{url('get-country')}}">
                                <span class="fa-stack fa-2x">
                                    <i class="fa fa-flag-checkered fa-stack-1x"></i>
                                </span>
                            </a>
                        </div>
                        <div class="text-center text-sm fw_400">{{ __('message.country_list') }}</div>
                    </div>
                </div>

            <div class="col-md-2 col-sm-6">
                <div class="settingiconblue">
                    <div class="settingdivblue">
                        <a class="icons-color" href="{{url('queue')}}">
                                <span class="fa-stack fa-2x">
                                    <i class="fa fa-upload fa-stack-1x"></i>
                                </span>
                        </a>
                    </div>
                    <div class="text-center text-sm fw_400">{{ __('message.queues') }}</div>
                </div>
            </div>
            </div>
            <!-- /.row -->
        </div>
        <!-- ./box-body -->
    </div>
    <div class="card card-secondary card-outline">
        <div class="card-header">
            <h3 class="card-title">{{ __('message.widgets') }}</h3>
        </div>
        <!-- /.box-header -->
        <div class="card-body">
            <div class="row">
                <!--/.col-md-2-->
                <div class="col-md-2 col-sm-6">
                    <div class="settingiconblue">
                        <div class="settingdivblue">
                            <a class="icons-color" href="{{ url('widgets') }}">
                                <span class="fa-stack fa-2x">
                                    <i class="fa fa-list-alt fa-stack-1x"></i>
                                </span>
                            </a>
                        </div>
                        <div class="text-center text-sm fw_400">{{ __('message.footer') }}</div>
                    </div>
                </div>
                <!--/.col-md-2-->
                <!--col-md-2-->
                <div class="col-md-2 col-sm-6">
                    <div class="settingiconblue">
                        <div class="settingdivblue">
                            <a class="icons-color" href="{{ url('social-media') }}">
                                <span class="fa-stack fa-2x">
                                    <i class="fa fa-cubes fa-stack-1x"></i>
                                </span>
                            </a>
                        </div>
                        <div class="text-center text-sm fw_400">{{ __('message.social-media') }}</div>
                    </div>
                </div>
                <!--/.col-md-2-->

                <div class="col-md-2 col-sm-6">
                    <div class="settingiconblue">
                        <div class="settingdivblue">
                            <a class="icons-color" href="{{ url('chat') }}">
                                <span class="fa-stack fa-2x">
                                    <i class="fas fa-code fa-stack-1x"></i>
                                </span>
                            </a>
                        </div>
                        <div class="text-center text-sm fw_400">{{ __('message.analytics_custom_code') }}</div>
                    </div>
                </div>
            </div>
            <!-- /.row -->
        </div>
        <!-- ./box-body -->
    </div>

    {{-- Monitoring Unavailable Modal --}}
    <div class="modal fade" id="monitoringUnavailableModal" tabindex="-1" role="dialog" aria-modal="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content border-0">
                <div class="modal-header">
                    <h5 class="modal-title">
                        <div class="d-inline-flex align-items-center justify-content-center rounded p-2 mr-2" style="background-color: #fff3cd; color: #856d00;" aria-hidden="true">
                            <i class="fas fa-exclamation-triangle"></i>
                        </div>
                        {{ __('message.monitoring_unavailable') }}
                    </h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="d-flex align-items-center justify-content-center p-0">
                        <div class="w-100">
                            <div class="d-flex align-items-start">
                                <div class="flex-grow-1">
                                    <h3 class="mb-3 font-weight-bold text-dark" id="monitoringModalTitle"></h3>

                                    <p class="mb-2 font-weight-semibold text-dark">
                                        {{ __('message.pulse_horizon_invalid_installation_path_detected') }}
                                    </p>

                                    <p class="text-muted mb-3">
                                        {{ __('message.pulse_horizon_folder_based_installations_are_not_supported') }}
                                    </p>

                                    <div class="mb-3">
                                        <div class="small font-weight-semibold text-muted mb-2">{{ __('message.pulse_horizon_example') }}</div>

                                        <div class="d-flex align-items-center mb-2" style="gap: 8px;">
                                            <i class="fas fa-times-circle text-danger fa-xs"></i>
                                            <div class="small font-weight-semibold mb-0">
                                                {{ __('message.pulse_horizon_not_supported') }} &middot;
                                                <span class="text-monospace text-muted small">{{ __('message.pulse_horizon_not_supported_url') }}</span>
                                            </div>
                                        </div>

                                        <div class="d-flex align-items-center" style="gap: 8px;">
                                            <i class="fas fa-check-circle text-success fa-xs"></i>
                                            <div class="small font-weight-semibold mb-0">
                                                {{ __('message.pulse_horizon_supported') }} &middot;
                                                <span class="text-monospace text-muted small">{{ __('message.pulse_horizon_supported_root_url') }}</span>
                                            </div>
                                        </div>
                                    </div>

                                    <p class="text-muted mb-3">
                                        {{ __('message.pulse_horizon_install_the_application_on_root_domain_or_subdomain') }}
                                    </p>

                                    <ul class="text-muted small mb-3 pl-3">
                                        <li>{{ __('message.pulse_horizon_next_step_move_application_to_web_root') }}</li>
                                        <li>{{ __('message.pulse_horizon_next_step_configure_subdomain') }}</li>
                                        <li>{{ __('message.pulse_horizon_next_step_clear_cache_and_try_again') }}</li>
                                    </ul>

                                    <p class="text-muted small mb-3" id="monitoringRedirectReason"></p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        $('ul.nav-sidebar a').filter(function () {
            return this.id == 'setting';
        }).addClass('active');

        // for treeview
        $('ul.nav-treeview a').filter(function () {
            return this.id == 'setting';
        }).parentsUntil(".nav-sidebar > .nav-treeview").addClass('menu-open').prev('a').addClass('active');

        function checkMonitoring(type) {
            var toolUrls = {
                pulse: '{{ url("pulse") }}',
                horizon: '{{ url("horizon") }}',
                clockwork: '{{ url("clockwork/app") }}'
            };
            var toolLabels = { pulse: 'Pulse', horizon: 'Horizon', clockwork: 'Clockwork' };
            var toolTitles = {
                pulse: '{!! __("message.pulse_could_not_load") !!}',
                horizon: '{!! __("message.horizon_could_not_load") !!}',
                clockwork: '{!! __("message.clockwork_could_not_load") !!}'
            };

            $.ajax({
                url: '{{ url("monitoring/check") }}',
                type: 'GET',
                data: { type: type },
                dataType: 'json',
                success: function(response) {
                    var data = response.data || response;
                    if (data.allowed) {
                        window.open(toolUrls[type], '_blank');
                    } else {
                        $('#monitoringModalTitle').text(toolTitles[type]);
                        $('#monitoringRedirectReason').text(
                            '{!! __("message.monitoring_redirect_reason", ["tool" => ":tool"]) !!}'.replace(':tool', toolLabels[type])
                        );
                        $('#monitoringUnavailableModal').modal('show');
                    }
                },
                error: function() {
                    window.open(toolUrls[type], '_blank');
                }
            });
        }
    </script>
@stop
