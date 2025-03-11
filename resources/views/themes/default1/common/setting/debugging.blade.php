@extends('themes.default1.layouts.master')
@section('title')
Debugging Settings
@stop
@section('content-header')
    <div class="col-sm-6">
        <h1>Debugging Settings</h1>
    </div>
    <div class="col-sm-6">
        <ol class="breadcrumb float-sm-right">
            <li class="breadcrumb-item"><a href="{{url('/')}}"><i class="fa fa-dashboard"></i> Home</a></li>
            <li class="breadcrumb-item"><a href="{{url('settings')}}"><i class="fa fa-dashboard"></i> Settings</a></li>
            <li class="breadcrumb-item active">Debugging Settings</li>
        </ol>
    </div><!-- /.col -->
@stop
@section('content')
    <div class="card card-secondary card-outline">

    <div class="card-header">

        <div id="response"></div>
        <h5>Set Debugg Option
          </h5>
    </div>
    <?php
    $de = env('APP_DEBUG');
    ?>
        <div class="card-body">
            {!! html()->form('POST', url('save/debugg'))->open() !!}
            <div class="row">
                <div class="col-md-4">
                    <div class="form-group">
                        {!! html()->label(Lang::get('Debugging'), 'debug')->class('form-label fw-bold') !!}
                        <div class="mt-2">
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="radio" name="debug" value="true" id="debug-enable"
                                       @if($de == true) checked @endif>
                                <label class="form-check-label" for="debug-enable">{{ Lang::get('Enable') }}</label>
                            </div>
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="radio" name="debug" value="false" id="debug-disable"
                                       @if($de == false) checked @endif>
                                <label class="form-check-label" for="debug-disable">{{ Lang::get('Disable') }}</label>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="text-end mt-3">
                <button type="submit" class="form-group btn btn-primary">
                    <i class="fas fa-save"></i> {{ Lang::get('Save') }}
                </button>
            </div>

            {!! html()->form()->close() !!}
        </div>




@stop


