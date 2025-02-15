@extends('themes.default1.layouts.master')
@section('title')
    Templates
@stop
@section('content-header')
    <div class="col-sm-6">
        <h1>Template Settings</h1>
    </div>
    <div class="col-sm-6">
        <ol class="breadcrumb float-sm-right">
            <li class="breadcrumb-item"><a href="{{url('/')}}"><i class="fa fa-dashboard"></i> Home</a></li>
            <li class="breadcrumb-item"><a href="{{url('settings')}}"><i class="fa fa-dashboard"></i> Settings</a></li>
            <li class="breadcrumb-item active">Template</li>
        </ol>
    </div><!-- /.col -->
@stop
@section('content')
<div class="row">

    <div class="col-md-12">
        <div class="card card-secondary card-outline">


            <div class="card-body table-responsive">
                {!! html()->model($set)
     ->route('settings.template')
     ->method('patch')
     ->files() !!}


                <tr>
                        <h4 class="box-title">{{Lang::get('Template List')}}</h4>
                    </tr>

                    <tr>

                        <td><b>{!! html()->label(Lang::get('message.welcome-mail'), 'welcome_mail') !!}</b></td>
                        <td>
                            <div class="form-group {{ $errors->has('welcome_mail') ? 'has-error' : '' }}">


                                {!! html()->select('welcome_mail', ['Templates' => $template->where('type', 1)->pluck('name', 'id')->toArray()])
    ->class('form-control') !!}
                                <p><i> {{Lang::get('message.choose-welcome-mail-template')}}</i> </p>


                            </div>
                        </td>

                    </tr>

                    <tr>

                        <td><b>{!! html()->label(Lang::get('message.order-mail'), 'order_mail') !!}</b></td>
                        <td>
                            <div class="form-group {{ $errors->has('order_mail') ? 'has-error' : '' }}">


                                {!! html()->select('order_mail', ['Templates' => $template->where('type', 7)->pluck('name', 'id')->toArray()])->class('form-control') !!}
                                <p><i> {{Lang::get('message.choose-order-mail-template')}}</i> </p>


                            </div>
                        </td>

                    </tr>
                    <tr>

                        <td><b>{!! html()->label(Lang::get('message.forgot-password'), 'forgot_password') !!}</b></td>
                        <td>
                            <div class="form-group {{ $errors->has('forgot_password') ? 'has-error' : '' }}">


                                {!! html()->select('forgot_password', ['Templates' => $template->where('type', 2)->pluck('name', 'id')->toArray()])->class('form-control') !!}

                                <p><i> {{Lang::get('message.choose-forgot-password-mail-template')}}</i> </p>


                            </div>
                        </td>

                    </tr>
                    <tr>

                        <td><b>{!! html()->label(Lang::get('message.subscription-going-to-end'), 'subscription_going_to_end') !!}</b></td>
                        <td>
                            <div class="form-group {{ $errors->has('subscription_going_to_end') ? 'has-error' : '' }}">


                                {!! html()->select('subscription_going_to_end', ['Templates' => $template->where('type', 4)->pluck('name', 'id')->toArray()])->class('form-control') !!}
                                <p><i> {{Lang::get('message.choose-subscription-going-to-end-notification-email-template')}}</i> </p>


                            </div>
                        </td>

                    </tr>
                    <tr>

                        <td><b>{!! html()->label(Lang::get('message.subscription-over'), 'subscription_over') !!}</b></td>
                        <td>
                            <div class="form-group {{ $errors->has('subscription_over') ? 'has-error' : '' }}">


                                {!! html()->select('subscription_over', ['Templates' => $template->where('type', 5)->pluck('name', 'id')->toArray()])->class('form-control') !!}
                                <p><i> {{Lang::get('message.choose-mail-template-to-notify-subscription-has-over')}}</i> </p>


                            </div>
                        </td>

                    </tr>
            <!--         <tr>

                        <td><b>{!! html()->label('Download', 'download') !!}</b></td>
                                        <td>
                                            <div class="form-group {{ $errors->has('download') ? 'has-error' : '' }}">


                                {!! html()->select('download', ['Templates' => $template->where('type', 8)->pluck('name', 'id')->toArray()])->class('form-control') !!}


                </div>
                        </td>
                        
                    </tr> -->
                    <tr>

                        <td><b>{!! html()->label(Lang::get('message.invoice'), 'invoice') !!}</b></td>
                        <td>
                            <div class="form-group {{ $errors->has('invoice') ? 'has-error' : '' }}">


                                {!! html()->select('invoice', ['Templates' => $template->where('type', 6)->pluck('name', 'id')->toArray()])->class('form-control') !!}
                                <p><i> {{Lang::get('Choose invoice Mail Template')}}</i> </p>



                            </div>
                        </td>

                    </tr>
                     <tr>

                         <td><b>{!! html()->label(Lang::get('Purchase Confirmation'), 'invoice') !!}</b></td>
                         <td>
                            <div class="form-group {{ $errors->has('invoice') ? 'has-error' : '' }}">


                                {!! html()->select('invoice')
    ->options($template->where('type', 7)->pluck('name', 'id')->toArray())
    ->class('form-control') !!}
                                <p><i> {{Lang::get('Choose Purchase Confirmation Mail Template')}}</i> </p>
                                


                            </div>
                        </td>

                    </tr>
                     <tr>

                         {!! html()->label(Lang::get('New Sales Manager'), 'invoice')->class('required') !!}
                         <td>
                            <div class="form-group {{ $errors->has('invoice') ? 'has-error' : '' }}">


                                {!! html()->select('invoice', $template->where('type', 9)->pluck('name', 'id')->toArray())->class('form-control') !!}
                                <p><i> {{Lang::get('Choose New Sales Manager Mail Template')}}</i> </p>
                                


                            </div>
                        </td>

                    </tr>
                      <tr>

                          {!! html()->label(Lang::get('New Account Manager'))->for('invoice')->class('required') !!}
                          <td>
                            <div class="form-group {{ $errors->has('invoice') ? 'has-error' : '' }}">


                                {!! html()->select('invoice')
    ->options($template->where('type', 10)->pluck('name', 'id')->toArray())
    ->class('form-control') !!}
                                <p><i> {{Lang::get('Choose New Account Manager Mail Template')}}</i> </p>
                                


                            </div>
                        </td>

                    </tr>
                     <tr>

                         {!! html()->label(Lang::get('Auto Renewal Reminder'), 'invoice')->class('required') !!}
                         <td>
                            <div class="form-group {{ $errors->has('auto_subscription_going_to_end') ? 'has-error' : '' }}">


                                {!! html()->select('invoice')
     ->options($template->where('type', 12)->pluck('name', 'id')->toArray())
     ->class('form-control') !!}
                                <p><i> {{Lang::get('Choose Auto Renewal Reminder Mail Template')}}</i> </p>

                                


                            </div>
                        </td>

                    </tr>
                    <tr>

                        {!! html()->label(Lang::get('Auto Payment Successfull'), 'invoice')->class('required') !!}
                        <td>
                            <div class="form-group {{ $errors->has('payment_successfull') ? 'has-error' : '' }}">


                                {!! html()->select('invoice')
    ->options($template->where('type', 13)->pluck('name', 'id')->toArray())
    ->class('form-control') !!}
                                <p><i> {{Lang::get('Choose Auto Payment Successfull Mail Template')}}</i> </p>

                                


                            </div>
                        </td>

                    </tr>
                       <tr>

                           <td><b>{!! html()->label(Lang::get('Auto Payment Failed'), 'invoice')->class('required') !!}</b></td>
                           <td>
                            <div class="form-group {{ $errors->has('payment_failed') ? 'has-error' : '' }}">


                                {!! html()->select('invoice')
    ->options($template->where('type', 14)->pluck('name', 'id')->toArray())
    ->class('form-control') !!}
                                <p><i> {{Lang::get('Choose Auto Payment Failed Mail Template')}}</i> </p>

                                


                            </div>
                        </td>

                    </tr>
                        <tr>

                            {!! html()->label(Lang::get('URGENT: Order has been deleted'))->class('required') !!}
                            <td>
                            <div class="form-group {{ $errors->has('cloud_deleted') ? 'has-error' : '' }}">


                                {!! html()->select('invoice')
    ->options($template->where('type', 19)->pluck('name', 'id')->toArray())
    ->class('form-control') !!}
                                <p><i> {{Lang::get('Choose Cloud order delete Mail Template')}}</i> </p>
                                


                            </div>
                        </td>

                    </tr>
                        <tr>

                            {!! html()->label(Lang::get('New instance created'), 'invoice')->class('required') !!}
                            <td>
                            <div class="form-group {{ $errors->has('cloud_created') ? 'has-error' : '' }}">


                                {!! html()->select('invoice')
    ->options($template->where('type', 20)->pluck('name', 'id')->toArray())
    ->class('form-control') !!}
                                <p><i> {{Lang::get('Choose Cloud order create Mail Template')}}</i> </p>
                                


                            </div>
                        </td>

                    </tr>
                         <tr>

                             <td><b>{!! html()->label(Lang::get('Contact us'), 'contact_us') !!}</b></td>
                             <td>
                            <div class="form-group {{ $errors->has('cloud_created') ? 'has-error' : '' }}">


                                {!! html()->select('contact_us')
    ->options($template->where('type', 21)->pluck('name', 'id')->toArray())
    ->class('form-control') !!}
                                <p><i> {{Lang::get('Choose Contact us Mail Template')}}</i> </p>
                                


                            </div>
                        </td>

                    </tr>
                        <tr>

                            {!! html()->label(Lang::get('Request a demo'), 'demo_request') !!}
                            <td>
                            <div class="form-group {{ $errors->has('cloud_created') ? 'has-error' : '' }}">


                                {!! html()->select('demo_request')
    ->options($template->where('type', 22)->pluck('name', 'id')->toArray())
    ->class('form-control') !!}
                                <p><i> {{Lang::get('Choose Demo request Mail Template')}}</i> </p>
                                


                            </div>
                        </td>

                    </tr>
                <tr>

                    {!! html()->label(Lang::get('Register Mail'), 'register_mail')->class('required') !!}
                    <td>
                        <div class="form-group {{ $errors->has('cloud_created') ? 'has-error' : '' }}">

                            {!! html()->select('register_mail', $template->where('type', 24)->pluck('name', 'id')->toArray())
    ->class('form-control') !!}
                            <p><i> {{Lang::get('Choose Register Mail Template')}}</i> </p>

                        </div>
                    </td>

                </tr>
                <br>
                <button type="submit" class="btn btn-primary pull-right" id="submit" style="margin-top:-40px;"><i class="fa fa-sync-alt">&nbsp;&nbsp;</i>{!!Lang::get('message.update')!!}</button>
                {!! html()->form()->close() !!}

            </div>
        </div>
    </div>
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