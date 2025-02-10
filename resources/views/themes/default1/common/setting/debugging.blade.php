@extends('themes.default1.layouts.master')
@section('title')
Debugging Settings
@stop
@section('content-header')
    <div class="col-sm-6">
        <h1>{{ __('message.debugging_settings') }}</h1>
    </div>
    <div class="col-sm-6">
        <ol class="breadcrumb float-sm-right">
            <li class="breadcrumb-item"><a href="{{url('/')}}"><i class="fa fa-dashboard"></i> {{ __('message.home') }}</a></li>
            <li class="breadcrumb-item"><a href="{{url('settings')}}"><i class="fa fa-dashboard"></i> {{ __('message.settings') }}</a></li>
            <li class="breadcrumb-item active">{{ __('message.debugging_settings') }}</li>
        </ol>
    </div><!-- /.col -->
@stop
@section('content')
    <div class="card card-secondary card-outline">

    <div class="card-header">

        <div id="response"></div>
        <h5>{{ __('message.set_debugg_option') }}
          </h5>
    </div>
    <?php
    $de = env('APP_DEBUG');
    ?>
        <div class="card-body">
        {!! Form::open(['url' => 'save/debugg', 'method' => 'POST']) !!}
        <div class="row">
            <div class="col-md-4">
                <div class="form-group">
                    {!! Form::label('debug',Lang::get('Debugging')) !!}
                    <div class="row">
                        <div class="col-sm-3">
                            <input type="radio" name="debug" value="true" @if($de == true) checked="true" @endif > {{Lang::get('Enable')}}
                        </div>
                        <div class="col-sm-3">
                            <input type="radio" name="debug" value="false" @if($de == false) checked="true" @endif> {{Lang::get('Disable')}}
                        </div>
                    </div>
                </div> 
            </div>            

    </div>
    <div>
    <button type="submit" class="btn btn-primary">{{ __('message.save') }}</button>
    </div>

</div>

</div>




@stop


