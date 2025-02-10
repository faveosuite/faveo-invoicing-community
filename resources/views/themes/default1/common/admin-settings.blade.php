@extends('themes.default1.layouts.master')
@section('title')
Settings
@stop
@section('content-header')
    <div class="col-sm-6">
        <h1>{{ __('message.application_settings') }}</h1>
    </div>
    <div class="col-sm-6">
        <ol class="breadcrumb float-sm-right">
            <li class="breadcrumb-item"><a href="{{url('/')}}"><i class="fa fa-dashboard"></i> {{ __('message.home') }}</a></li>
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
                            <a class="icons-color" href="{{ url('plugin') }}">
                                <span class="fa-stack fa-2x">
                                    <i class="fa fa-credit-card fa-stack-1x"></i>
                                </span>
                            </a>
                        </div>
                        <div class="text-center text-sm fw_400">{{ __('message.payment_gateway') }}</div>
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
             @if(env('APP_DEBUG') == 'true')
                    <div class="col-md-2 col-sm-6">
                    <div class="settingiconblue">
                        <div class="settingdivblue">
                            <a class="icons-color" href="{{url('clockwork/app')}}">
                                <span class="fa-stack fa-2x">
                                   <i class="fa fa-clock fa-lg fa-stack-1x"></i>
                                </span>
                            </a>
                        </div>
                        <div class="text-center text-sm fw_400">{{ __('message.clockwork') }}</div>
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

            </div>
    </div>
        <!-- /.row -->

    <!-- ./box-body -->
</div>
<!-- /.box -->

<div class="card card-secondary card-outline">
    <div class="card-header">
        <h3 class="card-title">{{Lang::get('message.log_setting')}}</h3>
    </div>
    <!-- /.box-header -->
    <div class="card-body">
        <div class="row">
                <!--/.col-md-2-->



                 <div class="col-md-2 col-sm-6">
                    <div class="settingiconblue">
                        <div class="settingdivblue">
                            <a class="icons-color" href="{{ url('log-viewer') }}">
                                <span class="fa-stack fa-2x">
                                    <i class="fa fa-bug fa-stack-1x"></i>
                                </span>
                            </a>
                        </div>
                        <div class="text-center text-sm fw_400">{{ __('message.err_log') }}</div>
                    </div>
                </div>

                <div class="col-md-2 col-sm-6">
                    <div class="settingiconblue">
                        <div class="settingdivblue">
                            <a class="icons-color" href="{{ url('settings/activitylog') }}">
                                <span class="fa-stack fa-2x">
                                    <i class="fa fa-history fa-stack-1x"></i>
                                </span>
                            </a>
                        </div>
                        <div class="text-center text-sm fw_400">{{ __('message.activity_log') }}</div>
                    </div>
                </div>

                 <div class="col-md-2 col-sm-6">
                    <div class="settingiconblue">
                        <div class="settingdivblue">
                            <a class="icons-color" href="{{ url('settings/maillog') }}">
                                <span class="fa-stack fa-2x">
                                    <i class="fa fa-envelope-square fa-stack-1x"></i>
                                </span>
                            </a>
                        </div>
                        <div class="text-center text-sm fw_400">{{ __('message.email_log') }}</div>
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

          <!--        <div class="col-md-2 col-sm-6">
                    <div class="settingiconblue">
                        <div class="settingdivblue">
                            <a href="{{ url('settings/maillog') }}">
                                <span class="fa-stack fa-2x">
                                    <i class="fa fa-archive fa-stack-1x"></i>
                                </span>
                            </a>
                        </div>
                        <p class="box-title" >{{Lang::get('message.cleanup_log')}}</p>
                    </div>
                </div> -->




                 <!--/.col-md-2-->

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

                @if($isRedisConfigured)
                 <div class="col-md-2 col-sm-6">
                    <div class="settingiconblue">
                        <div class="settingdivblue">
                            <a class="icons-color" href="{{url('horizon')}}" target="_blank">
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
                <div class="col-md-2 col-sm-6">
                    <div class="settingiconblue">
                        <div class="settingdivblue">
                            <a class="icons-color" href="{{ url('github') }}">
                                <span class="fa-stack fa-2x">
                                    <i class="fab fa-github-square fa-stack-1x"></i>
                                </span>
                            </a>
                        </div>
                        <div class="text-center text-sm fw_400">{{ __('message.github') }}</div>
                    </div>
                </div>
                <!--/.col-md-2-->
                <!--col-md-2-->
                <?php
                $mailchimpStatus = \App\Model\Common\StatusSetting::first()->value('mailchimp_status');
                ?>
                @if($mailchimpStatus ==1)
                <div class="col-md-2 col-sm-6">
                    <div class="settingiconblue">
                        <div class="settingdivblue">
                            <a class="icons-color" href="{{ url('mailchimp') }}">
                                <span class="fa-stack fa-2x">
                                    <i class="fab fa-mailchimp fa-stack-1x"></i>
                                </span>
                            </a>
                        </div>
                        <div class="text-center text-sm fw_400">{{ __('message.mail_chimp') }}</div>
                    </div>
                </div>
                @endif
                <!--/.col-md-2-->
                <div class="col-md-2 col-sm-6">
                    <div class="settingiconblue">
                        <div class="settingdivblue">
                            <a class="icons-color" href="{{ url('apikeys') }}">
                                <span class="fa-stack fa-2x">
                                    <i class="fa fa-cogs fa-stack-1x"></i>
                                </span>
                            </a>
                        </div>
                        <div class="text-center text-sm fw_400">{{ __('message.key') }}</div>
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
