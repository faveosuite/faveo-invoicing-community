@extends('themes.default1.installer.layout.installer')
@section('dbSetup')
    done
@stop

@section('database')
    done
@stop

@section('post-check')
    done
@stop

@section('get-start')
    done
@stop

@section('final')
    active
@stop

@section('content')


        <div class="card">

            <div class="card-body">

                <p class="text-center lead text-bold">{{trans('installer_messages.final_setup')}}!</p>

                <div class="row">

                    <div class="col-6">

                        <p class="lead">{{trans('installer_messages.learn_more')}}</p>

                        <p><i class="fas fa-newspaper"></i>&nbsp;&nbsp;<a href="https://github.com/ladybirdweb/agora-invoicing-community/wiki" target="_blank">{{trans('installer_messages.knowledge_base')}}</a></p>
                        <p><i class="fas fa-envelope"></i>&nbsp;&nbsp;<a href="mailto:support@ladybirdweb.com">{{trans('installer_messages.email_support')}}</a></p>
                    </div>

                    <div class="col-6">

                        <a href="{!! url('login') !!}">
                            <div class="btn btn-primary">{{trans('installer_messages.login_button')}}&nbsp;<i class="fas {{ app()->getLocale() === 'ar' ? 'fa-arrow-left' : 'fa-arrow-right' }}"></i></div>
                        </a>

                    </div>
                </div>
            </div>
        </div>


@stop

{{--<h1>Final Page</h1>--}}