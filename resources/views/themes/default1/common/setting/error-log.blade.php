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
                {!! Form::model($set,['url'=>'settings/error','method'=>'patch','files'=>true]) !!}

                <table class="table table-condensed">
                    <tr>
                        <td><h3 class="box-title">{{Lang::get('message.error-log')}}</h3></td>
                        <td>{!! Form::submit(Lang::get('message.update'),['class'=>'btn btn-primary pull-right'])!!}</td>
                    </tr>

                    <tr>

                        <td><b>{!! Form::label('error_log',Lang::get('message.error-log')) !!}</b></td>
                        <td>
                            <div class="form-group {{ $errors->has('error_log') ? 'has-error' : '' }}">


                                {!! Form::radio('error_log','1',true) !!}<span>   {{Lang::get('message.yes')}}</span>
                                {!! Form::radio('error_log','0') !!}<span>   {{Lang::get('message.no')}}</span>
                                <p><i> {{Lang::get('message.enable-error-logging')}}</i> </p>


                            </div>
                        </td>

                    </tr>

                    <tr>

                        <td><b>{!! Form::label('error_email',Lang::get('message.error-email')) !!}</b></td>
                        <td>
                            <div class="form-group {{ $errors->has('error_email') ? 'has-error' : '' }}">


                                {!! Form::text('error_email',null,['class' => 'form-control']) !!}
                                <p><i> {{Lang::get('message.provide-error-reporting-email')}}</i> </p>


                            </div>
                        </td>
                        {!! Form::close() !!}
                    </tr>
                </table>
            </div>
        </div>
    </div>
</div>
@stop