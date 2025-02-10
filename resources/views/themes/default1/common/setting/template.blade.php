@extends('themes.default1.layouts.master')
@section('title')
    Templates
@stop
@section('content-header')
    <div class="col-sm-6">
        <h1>{{ __('message.template_settings') }}</h1>
    </div>
    <div class="col-sm-6">
        <ol class="breadcrumb float-sm-right">
            <li class="breadcrumb-item"><a href="{{url('/')}}"><i class="fa fa-dashboard"></i> {{ __('message.home') }}</a></li>
            <li class="breadcrumb-item"><a href="{{url('settings')}}"><i class="fa fa-dashboard"></i> {{ __('message.settings') }}</a></li>
            <li class="breadcrumb-item active">{{ __('message.template') }}</li>
        </ol>
    </div><!-- /.col -->
@stop
@section('content')
<div class="row">

    <div class="col-md-12">
        <div class="card card-secondary card-outline">


            <div class="card-body table-responsive">
                {!! Form::model($set,['url'=>'settings/template','method'=>'patch','files'=>true]) !!}

                
                    <tr>
                        <h4 class="box-title">{{Lang::get('template_list')}}</h4>
                    </tr>

                    <tr>

                        <td><b>{!! Form::label('welcome_mail',Lang::get('message.welcome-mail')) !!}</b></td>
                        <td>
                            <div class="form-group {{ $errors->has('welcome_mail') ? 'has-error' : '' }}">


                                {!! Form::select('welcome_mail',['Templates'=>$template->where('type',1)->pluck('name','id')->toArray()],null,['class'=>'form-control']) !!}
                                <p><i> {{Lang::get('message.choose-welcome-mail-template')}}</i> </p>


                            </div>
                        </td>

                    </tr>

                    <tr>

                        <td><b>{!! Form::label('order_mail',Lang::get('message.order-mail')) !!}</b></td>
                        <td>
                            <div class="form-group {{ $errors->has('order_mail') ? 'has-error' : '' }}">


                                {!! Form::select('order_mail',['Templates'=>$template->where('type',7)->pluck('name','id')->toArray()],null,['class'=>'form-control']) !!}
                                <p><i> {{Lang::get('message.choose-order-mail-template')}}</i> </p>


                            </div>
                        </td>

                    </tr>
                    <tr>

                        <td><b>{!! Form::label('forgot_password',Lang::get('message.forgot-password')) !!}</b></td>
                        <td>
                            <div class="form-group {{ $errors->has('forgot_password') ? 'has-error' : '' }}">


                                {!! Form::select('forgot_password',['Templates'=>$template->where('type',2)->pluck('name','id')->toArray()],null,['class'=>'form-control']) !!}
                                <p><i> {{Lang::get('message.choose-forgot-password-mail-template')}}</i> </p>


                            </div>
                        </td>

                    </tr>
                    <tr>

                        <td><b>{!! Form::label('subscription_going_to_end',Lang::get('message.subscription-going-to-end')) !!}</b></td>
                        <td>
                            <div class="form-group {{ $errors->has('subscription_going_to_end') ? 'has-error' : '' }}">


                                {!! Form::select('subscription_going_to_end',['Templates'=>$template->where('type',4)->pluck('name','id')->toArray()],null,['class'=>'form-control']) !!}
                                <p><i> {{Lang::get('message.choose-subscription-going-to-end-notification-email-template')}}</i> </p>


                            </div>
                        </td>

                    </tr>
                    <tr>

                        <td><b>{!! Form::label('subscription_over',Lang::get('message.subscription-over')) !!}</b></td>
                        <td>
                            <div class="form-group {{ $errors->has('subscription_over') ? 'has-error' : '' }}">


                                {!! Form::select('subscription_over',['Templates'=>$template->where('type',5)->pluck('name','id')->toArray()],null,['class'=>'form-control']) !!}
                                <p><i> {{Lang::get('message.choose-mail-template-to-notify-subscription-has-over')}}</i> </p>


                            </div>
                        </td>

                    </tr>
            <!--         <tr>

                        <td><b>{!! Form::label('download','Download') !!}</b></td>
                        <td>
                            <div class="form-group {{ $errors->has('download') ? 'has-error' : '' }}">


                                {!! Form::select('download',['Templates'=>$template->where('type',8)->pluck('name','id')->toArray()],null,['class'=>'form-control']) !!}
                                


                            </div>
                        </td>
                        
                    </tr> -->
                    <tr>

                        <td><b>{!! Form::label('invoice',Lang::get('message.invoice')) !!}</b></td>
                        <td>
                            <div class="form-group {{ $errors->has('invoice') ? 'has-error' : '' }}">


                                {!! Form::select('invoice',['Templates'=>$template->where('type',6)->pluck('name','id')->toArray()],null,['class'=>'form-control']) !!}
                                <p><i> {{ __('message.invoice_mail_template') }}</i> </p>



                            </div>
                        </td>

                    </tr>
                     <tr>

                         <td><b>{!! Form::label('invoice', __('message.purchase_confirmation')) !!}</b></td>

                         <td>
                            <div class="form-group {{ $errors->has('invoice') ? 'has-error' : '' }}">


                                {!! Form::select('invoice',['Templates'=>$template->where('type',7)->pluck('name','id')->toArray()],null,['class'=>'form-control']) !!}
                                 <p><i> {{ __('message.confirmation_mail_template') }}</i> </p>
                                


                            </div>
                        </td>

                    </tr>
                     <tr>

                         <td><b>{!! Form::label('invoice', __('message.new_sales_manager')) !!}</b></td>
                        <td>
                            <div class="form-group {{ $errors->has('invoice') ? 'has-error' : '' }}">


                                {!! Form::select('invoice',['Templates'=>$template->where('type',9)->pluck('name','id')->toArray()],null,['class'=>'form-control']) !!}
                                <p><i> {{ __('message.choose_manager_template') }}</i> </p>
                                


                            </div>
                        </td>

                    </tr>
                      <tr>

                          <td><b>{!! Form::label('invoice', __('message.new_account_manager')) !!}</b></td>
                        <td>
                            <div class="form-group {{ $errors->has('invoice') ? 'has-error' : '' }}">


                                {!! Form::select('invoice',['Templates'=>$template->where('type',10)->pluck('name','id')->toArray()],null,['class'=>'form-control']) !!}
                                <p><i> {{ __('message.choose_new_account_manager') }}</i> </p>
                                


                            </div>
                        </td>

                    </tr>
                     <tr>

                         <td><b>{!! Form::label('invoice', __('message.auto_renewal_reminder')) !!}</b></td>
                        <td>
                            <div class="form-group {{ $errors->has('auto_subscription_going_to_end') ? 'has-error' : '' }}">


                                {!! Form::select('invoice',['Templates'=>$template->where('type',12)->pluck('name','id')->toArray()],null,['class'=>'form-control']) !!}
                                <p><i> {{ __('message.choose_auto_renewal_reminder') }}</i> </p>

                                


                            </div>
                        </td>

                    </tr>
                    <tr>

                        <td><b>{!! Form::label('invoice', __('message.auto_payment_success')) !!}</b></td>

                        <td>
                            <div class="form-group {{ $errors->has('payment_successfull') ? 'has-error' : '' }}">


                                {!! Form::select('invoice',['Templates'=>$template->where('type',13)->pluck('name','id')->toArray()],null,['class'=>'form-control']) !!}
                                <p><i> {{ __('message.choose_auto_payment_success') }}</i> </p>

                                


                            </div>
                        </td>

                    </tr>
                       <tr>

                           <td><b>{!! Form::label('invoice', __('message.auto_payment_failed')) !!}</b></td>
                        <td>
                            <div class="form-group {{ $errors->has('payment_failed') ? 'has-error' : '' }}">


                                {!! Form::select('invoice',['Templates'=>$template->where('type',14)->pluck('name','id')->toArray()],null,['class'=>'form-control']) !!}
                                <p><i> {{ __('message.choose_auto_payment_failed') }}</i> </p>

                                


                            </div>
                        </td>

                    </tr>
                        <tr>

                            <td><b>{!! Form::label('invoice', __('message.urgent_order_deleted')) !!}</b></td>
                        <td>
                            <div class="form-group {{ $errors->has('cloud_deleted') ? 'has-error' : '' }}">


                                {!! Form::select('invoice',['Templates'=>$template->where('type',19)->pluck('name','id')->toArray()],null,['class'=>'form-control']) !!}
                                <p><i> {{ __('message.choose_cloud_order') }}</i> </p>
                                


                            </div>
                        </td>

                    </tr>
                        <tr>

                            <td><b>{!! Form::label('invoice', __('message.new_instance_created')) !!}</b></td>
                        <td>
                            <div class="form-group {{ $errors->has('cloud_created') ? 'has-error' : '' }}">


                                {!! Form::select('invoice',['Templates'=>$template->where('type',20)->pluck('name','id')->toArray()],null,['class'=>'form-control']) !!}
                                <p><i> {{ __('message.choose_cloud_order_create') }}</i> </p>
                                


                            </div>
                        </td>

                    </tr>
                         <tr>

                             <td><b>{!! Form::label('contact_us', __('message.contact_us')) !!}</b></td>
                        <td>
                            <div class="form-group {{ $errors->has('cloud_created') ? 'has-error' : '' }}">


                                {!! Form::select('contact_us',['Templates'=>$template->where('type',21)->pluck('name','id')->toArray()],null,['class'=>'form-control']) !!}
                                <p><i> {{ __('message.choose_contact_mail_template') }}</i> </p>
                                


                            </div>
                        </td>

                    </tr>
                        <tr>

                            <td><b>{!! Form::label('demo_request', __('message.request_demo')) !!}</b></td>
                        <td>
                            <div class="form-group {{ $errors->has('cloud_created') ? 'has-error' : '' }}">


                                {!! Form::select('demo_request',['Templates'=>$template->where('type',22)->pluck('name','id')->toArray()],null,['class'=>'form-control']) !!}
                                <p><i> {{ __('message.choose_request_mail_template') }}</i> </p>
                                


                            </div>
                        </td>

                    </tr>
                <tr>

                    <td><b>{!! Form::label('demo_request', __('message.request_demo')) !!}</b></td>
                    <td>
                        <div class="form-group {{ $errors->has('cloud_created') ? 'has-error' : '' }}">

                            {!! Form::select('register_mail',['Templates'=>$template->where('type',24)->pluck('name','id')->toArray()],null,['class'=>'form-control']) !!}
                            <p><i> {{ __('message.choose_register_mail_template') }}</i> </p>

                        </div>
                    </td>

                </tr>
                <br>
                <button type="submit" class="btn btn-primary pull-right" id="submit" style="margin-top:-40px;"><i class="fa fa-sync-alt">&nbsp;&nbsp;</i>{!!Lang::get('message.update')!!}</button>
                {!! Form::close() !!}

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