@extends('themes.default1.layouts.master')
@section('title')
Settings
@stop
@section('content-header')
    <div class="col-sm-6">
        <h1>Application Settings</h1>
    </div>
    <div class="col-sm-6">
        <ol class="breadcrumb float-sm-right">
            <li class="breadcrumb-item"><a href="{{url('/')}}"><i class="fa fa-dashboard"></i> Home</a></li>
            <li class="breadcrumb-item active">Settings</li>
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
            <h3 class="card-title">Settings</h3>
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
                        <div class="text-center text-sm fw_400">System Settings</div>
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
                        <div class="text-center text-sm fw_400">License Type</div>
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
                        <div class="text-center text-sm fw_400">License Permissions</div>
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
                        <div class="text-center text-sm fw_400">File Storage</div>
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
                        <div class="text-center text-sm fw_400">Payment Gateways</div>
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
                        <div class="text-center text-sm fw_400">System Managers</div>
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
                        <div class="text-center text-sm fw_400">Third party apps</div>
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
                        <div class="text-center text-sm fw_400">Cloud Hub</div>
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
                        <div class="text-center text-sm fw_400">Localized License</div>
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
                        <div class="text-center text-sm fw_400">Debug</div>
                    </div>
                </div>
             @if(env('APP_DEBUG') == 'true')
                    <div class="col-md-2 col-sm-6">
                    <div class="settingiconblue">
                        <div class="settingdivblue">
                            <a class="icons-color" href="{{url('clockwork/app')}}">
                                <span class="fa-stack fa-2x">
                                   <i class="fa fa-clock fa-stack-1x"></i>
                                </span>
                            </a>
                        </div>
                        <div class="text-center text-sm fw_400">ClockWork</div>
                    </div>
                </div>
                <div class="col-md-2 col-sm-6">
                    <div class="settingiconblue">
                        <div class="settingdivblue">
                            <a class="icons-color" href="{{url('pulse')}}">
                                <span class="fa-stack fa-2x">
                                    <i class="fas fa-heartbeat fa-stack-1x"></i>
                                </span>
                            </a>
                        </div>
                        <div class="text-center text-sm fw_400">Pulse</div>
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
                    <div class="text-center text-sm fw_400">Social Logins</div>
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
                        <div class="text-center text-sm fw_400">Error Log</div>
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
                        <div class="text-center text-sm fw_400">Activity Log</div>
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
                        <div class="text-center text-sm fw_400">Email Log</div>
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
                        <div class="text-center text-sm fw_400">Payment Log</div>
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
        <h3 class="card-title">Email</h3>
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
                        <div class="text-center text-sm fw_400">Email Settings</div>
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
                        <div class="text-center text-sm fw_400">Template Settings</div>
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
                        <div class="text-center text-sm fw_400">Templates</div>
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
                        <div class="text-center text-sm fw_400">Queues</div>
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
                        <div class="text-center text-sm fw_400">Queue Monitoring</div>
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
        <h3 class="card-title">Api</h3>
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
                        <div class="text-center text-sm fw_400">Github</div>
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
                        <div class="text-center text-sm fw_400">Mail Chimp</div>
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
                        <div class="text-center text-sm fw_400">Api Keys</div>
                    </div>
                </div>
        </div>
        <!-- /.row -->
    </div>
    <!-- ./box-body -->
</div>

<div class="card card-secondary card-outline">
    <div class="card-header">
        <h3 class="card-title">Common</h3>
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
                        <div class="text-center text-sm fw_400">Tax</div>
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
                        <div class="text-center text-sm fw_400">Currency</div>
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
                        <div class="text-center text-sm fw_400">Country List</div>
                    </div>
                </div>


        </div>
        <!-- /.row -->
    </div>
    <!-- ./box-body -->
</div>
<div class="card card-secondary card-outline">
    <div class="card-header">
        <h3 class="card-title">Widgets</h3>
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
                        <div class="text-center text-sm fw_400">Footer</div>
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
                        <div class="text-center text-sm fw_400">Social Media</div>
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
                        <div class="text-center text-sm fw_400">Analytics/Custom Code</div>
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
