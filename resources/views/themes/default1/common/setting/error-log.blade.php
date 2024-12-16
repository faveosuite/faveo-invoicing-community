@extends('themes.default1.layouts.master')
@section('content')
<div class="row">

    <div class="col-md-12">
        <div class="box">
            <div class="box-header">
                @if (count($errors) > 0)
                <div class="alert alert-danger">
                    <strong>Whoops!</strong> There were some problems with your input.<br><br>
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
                {!! html()->modelForm($set, 'PATCH', url('settings/error'))->acceptsFiles()->open() !!}

                <table class="table table-condensed">
                    <tr>
                        <td><h3 class="box-title">{{ __('message.error-log') }}</h3></td>
                        <td>{!! html()->submit(__('message.update'))->class('btn btn-primary pull-right') !!}</td>
                    </tr>

                    <tr>
                        <td><b>{!! html()->label(__('message.error-log'))->for('error_log') !!}</b></td>
                        <td>
                            <div class="form-group {{ $errors->has('error_log') ? 'has-error' : '' }}">
                                {!! html()->radio('error_log', true, '1') !!} <span> {{ __('message.yes') }}</span>
                                {!! html()->radio('error_log', false, '0') !!} <span> {{ __('message.no') }}</span>
                                <p><i> {{ __('message.enable-error-logging') }}</i></p>
                            </div>
                        </td>
                    </tr>

                    <tr>
                        <td><b>{!! html()->label(__('message.error-email'))->for('error_email') !!}</b></td>
                        <td>
                            <div class="form-group {{ $errors->has('error_email') ? 'has-error' : '' }}">
                                {!! html()->text('error_email')->class('form-control') !!}
                                <p><i> {{ __('message.provide-error-reporting-email') }}</i></p>
                            </div>
                        </td>
                    </tr>
                </table>

                {!! html()->closeModelForm() !!}
            </div>
        </div>
    </div>
</div>
@stop