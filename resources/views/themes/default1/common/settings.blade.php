@extends('themes.default1.layouts.master')
@section('content')

<div class="row">

    <div class="col-md-12">
        <div class="box">
            <div class="box-header">
                @if (count($errors) > 0)
                <div class="alert alert-danger">
                    <strong>{{ __('message.whoops') }}</strong> {{ __('message.input_problem') }}<br><br>
                    <ul>
                        @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
                @endif

                @if(Session::has('success'))
                <div class="alert alert-success alert-dismissable">
                    <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
                    {{Session::get('success')}}
                </div>
                @endif
                <!-- fail message -->
                @if(Session::has('fails'))
                <div class="alert alert-danger alert-dismissable">
                    <i class="fa fa-ban"></i>
                    <b>{{Lang::get('message.alert')}}!</b> {{Lang::get('message.failed')}}.
                    <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
                    {{Session::get('fails')}}
                </div>
                @endif

            </div>

            <div class="box-body">
                {!! html()->modelForm($setting, 'PATCH', url('settings'))->acceptsFiles()->open() !!}

                <table class="table table-condensed">

                    <tr>
                        <td><h3 class="box-title">{{Lang::get('message.company')}}</h3></td>
                        <td>{!! html()->submit(Lang::get('message.update'))->class('btn btn-primary pull-right') !!}</td>

                    </tr>

                    <tr>

                        <td><b>{!! html()->label(Lang::get('message.company'), 'company')->class('required') !!}</b></td>
                        <td>
                            <div class="form-group {{ $errors->has('company') ? 'has-error' : '' }}">


                                {!! html()->text('company')->class('form-control') !!}
                                <p><i> {{Lang::get('message.enter-the-company-name')}}</i> </p>


                            </div>
                        </td>

                    </tr>
                    <tr>

                        <td><b>{!! html()->label(Lang::get('message.website'), 'website') !!}</b></td>
                        <td>
                            <div class="form-group {{ $errors->has('website') ? 'has-error' : '' }}">


                                {!! html()->text('website')->class('form-control') !!}
                                <p><i> {{Lang::get('message.enter-the-company-website')}}</i> </p>

                            </div>
                        </td>

                    </tr>
                    <tr>

                        <td><b>{!! html()->label(Lang::get('message.phone'), 'phone') !!}</b></td>
                        <td>
                            <div class="form-group {{ $errors->has('phone') ? 'has-error' : '' }}">


                                {!! html()->text('phone')->class('form-control') !!}
                                <p><i> {{Lang::get('message.enter-the-company-phone-number')}}</i> </p>

                            </div>
                        </td>

                    </tr>
                    <tr>

                        <td><b>{!! html()->label(Lang::get('message.address'), 'address')->class('required') !!}</b></td>
                        <td>
                            <div class="form-group {{ $errors->has('address') ? 'has-error' : '' }}">

                                {!! html()->textarea('address')
    ->class('form-control')
    ->attribute('id', 'address')
    ->attribute('rows', 10)
    ->attribute('cols', 128) !!}
                                <p><i> {{Lang::get('message.enter-company-address')}}</i> </p>
                            </div>
                        </td>

                    </tr>
                    <tr>

                        <td><b>{!! html()->label(Lang::get('message.logo'), 'logo') !!}</b></td>
                        <td>
                            <div class="form-group {{ $errors->has('logo') ? 'has-error' : '' }}">

                                {!! html()->file('logo') !!}
                                <p><i> {{Lang::get('message.enter-the-company-logo')}}</i> </p>
                                @if($setting->logo)
                                <img src="{{asset('cart/img/logo/'.$setting->logo)}}" class="img-thumbnail" style="height: 100px;">
                                @endif
                            </div>
                        </td>

                    </tr>

                    <tr>
                        <td><h3 class="box-title">{{Lang::get('message.smtp')}}</h3></td>
                        <td></td>
                    </tr>
                    <tr>

                        <td><b>{!! html()->label(Lang::get('message.driver'), 'driver')->class('required') !!}</b></td>
                        <td>
                            <div class="form-group {{ $errors->has('driver') ? 'has-error' : '' }}">


                                {!! html()->select('driver', ['mail' => 'Mail', 'smtp' => 'SMTP'])->class('form-control') !!}
                                <p><i> {{Lang::get('message.select-email-driver')}}</i> </p>


                            </div>
                        </td>

                    </tr>
                    <tr>

                        <td><b>{!! html()->label(Lang::get('message.port'), 'port') !!}</b></td>
                        <td>
                            <div class="form-group {{ $errors->has('port') ? 'has-error' : '' }}">


                                {!! html()->text('port')->class('form-control') !!}
                                <p><i> {{Lang::get('message.enter-email-port')}}</i> </p>

                            </div>
                        </td>

                    </tr>
                    <tr>

                        <td>{!! html()->label(__('message.host'))->for('host')->class('font-weight-bold') !!}</td><td>
                            <div class="form-group {{ $errors->has('host') ? 'has-error' : '' }}">


                                {!! html()->text('host')->class('form-control')->id('host') !!}
                                <p><i> {{Lang::get('message.enter-email-host')}}</i> </p>

                            </div>
                        </td>

                    </tr>
                    <tr>

                        <td><b>{!! html()->label(Lang::get('message.encryption'), 'encryption') !!}</b></td>
                        <td>
                            <div class="form-group {{ $errors->has('encryption') ? 'has-error' : '' }}">

                                {!! html()->text('encryption')->class('form-control') !!}
                                <p><i> {{Lang::get('message.select-email-encryption-method')}}</i> </p>

                            </div>
                        </td>

                    </tr>
                    <tr>

                        <td><b>{!! html()->label(Lang::get('message.email'), 'email')->class('required') !!}</b></td>
                        <td>
                            <div class="form-group {{ $errors->has('email') ? 'has-error' : '' }}">

                                {!! html()->text('email')->class('form-control') !!}
                                <p><i> {{Lang::get('message.enter-email')}}</i> </p>

                            </div>
                        </td>

                    </tr>
                    <tr>

                        <td><b>{!! html()->label(Lang::get('message.password'), 'password')->class('required') !!}</b></td>
                        <td>
                            <div class="form-group {{ $errors->has('password') ? 'has-error' : '' }}">

                                {!! html()->password('password')->class('form-control') !!}
                                <p><i> {{Lang::get('message.enter-email-password')}}</i> </p>

                            </div>
                        </td>

                    </tr>

                    <tr>
                        <td><h3 class="box-title">{{Lang::get('message.error-log')}}</h3></td>
                        <td></td>
                    </tr>

                    <tr>

                        <td><b>{!! html()->label(Lang::get('message.error-log'), 'error_log') !!}</b></td>
                        <td>
                            <div class="form-group {{ $errors->has('error_log') ? 'has-error' : '' }}">


                                {!! html()->radio('error_log', true, '1')->label(Lang::get('message.yes')) !!}
                                {!! html()->radio('error_log', false, '0')->label(Lang::get('message.no')) !!}
                                <p><i> {{Lang::get('message.enable-error-logging')}}</i> </p>


                            </div>
                        </td>

                    </tr>

                    <tr>

                        <td><b>{!! html()->label(Lang::get('message.error-email'), 'error_email') !!}</b></td>
                        <td>
                            <div class="form-group {{ $errors->has('error_email') ? 'has-error' : '' }}">


                                {!! html()->text('error_email')->class('form-control') !!}
                                <p><i> {{Lang::get('message.provide-error-reporting-email')}}</i> </p>


                            </div>
                        </td>

                    </tr>

                    <tr>
                        <td><h3 class="box-title">{{Lang::get('message.templates')}}</h3></td>
                        <td></td>
                    </tr>

                    <tr>

                        <td><b>{!! html()->label(Lang::get('message.welcome-mail'), 'welcome_mail') !!}</b></td>
                        <td>
                            <div class="form-group {{ $errors->has('welcome_mail') ? 'has-error' : '' }}">


                                {!! html()->select('welcome_mail', ['Templates' => $template->where('type', 1)->pluck('name', 'id')->toArray()])->class('form-control') !!}
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

                        <td><b>{!! html()->label(Lang::get('message.forgot-password'))->for('forgot_password') !!}</b></td>
                        <td>
                            <div class="form-group {{ $errors->has('forgot_password') ? 'has-error' : '' }}">


                                {!! html()->select('forgot_password', ['Templates' => $template->where('type', 2)->pluck('name', 'id')->toArray()])->class('form-control') !!}
                                <p><i> {{Lang::get('message.choose-forgot-password-mail-template')}}</i> </p>


                            </div>
                        </td>

                    </tr>
                    <tr>

                        <td><b>{!! html()->label(Lang::get('message.subscription-going-to-end'))->for('subscription_going_to_end') !!}</b></td>
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
                    <tr>

                        <td><b>{!! html()->label(Lang::get('message.cart'))->for('cart') !!}</b></td>
                        <td>
                            <div class="form-group {{ $errors->has('cart') ? 'has-error' : '' }}">


                                {!! html()->select('cart', ['Templates' => $template->where('type', 3)->pluck('name', 'id')->toArray()])->class('form-control') !!}
                                <p><i> {{Lang::get('message.choose-shoping-cart-template')}}</i> </p>


                            </div>
                        </td>

                    </tr>

                    {!! html()->closeModelForm() !!}
                </table>



            </div>

        </div>
        <!-- /.box -->

    </div>


</div>

@stop