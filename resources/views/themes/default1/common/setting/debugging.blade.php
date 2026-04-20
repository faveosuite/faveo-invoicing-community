@extends('themes.default1.layouts.master')
@section('title')
    {{ __('message.debugging_settings') }}
@stop
@section('content-header')
    <div class="col-sm-6">
        <h1>{{ __('message.debugging_settings') }}</h1>
    </div>
    <div class="col-sm-6">
        <ol class="breadcrumb float-sm-right">
            <li class="breadcrumb-item"><a href="{{ url('/') }}"><i class="fa fa-dashboard"></i> {{ __('message.home') }}</a></li>
            <li class="breadcrumb-item"><a href="{{ url('settings') }}"><i class="fa fa-dashboard"></i> {{ __('message.settings') }}</a></li>
            <li class="breadcrumb-item active">{{ __('message.debugging_settings') }}</li>
        </ol>
    </div>
@stop
@section('content')

<style>
    .switch {
        position: relative;
        display: inline-block;
        width: 40px;
        height: 20px;
    }

    .switch input { display: none; }

    .slider {
        position: absolute;
        cursor: pointer;
        top: 0; left: 0; right: 0; bottom: 0;
        background-color: #f39795;
        -webkit-transition: .4s;
        transition: .4s;
    }

    .slider:before {
        position: absolute;
        content: "";
        height: 12px;
        width: 12px;
        left: 4px;
        bottom: 4px;
        background-color: white;
        -webkit-transition: .4s;
        transition: .4s;
    }

    input:checked + .slider { background-color: #28a745; }
    input:focus  + .slider  { box-shadow: 0 0 1px #28a745; }

    input:checked + .slider:before {
        -webkit-transform: translateX(20px);
        -ms-transform: translateX(20px);
        transform: translateX(20px);
    }

    .slider.round        { border-radius: 34px; }
    .slider.round:before { border-radius: 50%; }

    .toggle-row {
        display: flex;
        align-items: center;
        justify-content: space-between;
        border: 1px solid #dee2e6;
        border-radius: .375rem;
        padding: .85rem 1rem;
        margin-bottom: .6rem;
    }

    .toggle-row:last-child { margin-bottom: 0; }

    .section-label {
        font-size: .7rem;
        font-weight: 700;
        letter-spacing: .08em;
        color: #6c757d;
        text-transform: uppercase;
        margin-bottom: .75rem;
    }
</style>

<?php
    $de = config('app.debug');
    $pe = config('pulse.enabled');
    $ce = config('clockwork.enable');
    $sr = config('app.sentry_reporting', true);
    $sp = config('sentry.traces_sample_rate') > 0;
?>

<div class="card card-secondary card-outline">
    <div class="card-header">
        <h5 class="mb-0">{{ __('message.set_debugg_option') }}</h5>
    </div>

    <div class="card-body">
        {!! html()->form('POST', url('save/debugg'))->open() !!}

        <div class="row">

            {{-- Application Debugging --}}
            <div class="col-md-6 mb-3">
                <div class="card border h-100">
                    <div class="card-header d-flex align-items-center bg-light">
                        <i class="fas fa-bug fa-2x text-primary mr-3"></i>
                        <div>
                            <h6 class="mb-0 font-weight-bold">{{ __('message.application_debugging') }}</h6>
                            <small class="text-muted">{{ __('message.application_debugging_desc') }}</small>
                        </div>
                    </div>
                    <div class="card-body">
                        <p class="section-label">{{ __('message.debugging_options') }}</p>

                        <div class="toggle-row">
                            <div class="pr-3">
                                <strong>{{ __('message.debugging_mode') }}</strong>
                                <p class="text-muted small mb-0 mt-1">{{ __('message.debugging_mode_desc') }}</p>
                            </div>
                            <label class="switch mb-0">
                                <input type="hidden" name="debug" value="false">
                                <input type="checkbox" name="debug" value="true" {{ $de ? 'checked' : '' }}>
                                <span class="slider round"></span>
                            </label>
                        </div>

                        <div class="toggle-row">
                            <div class="pr-3">
                                <strong>{{ __('message.pulse_monitoring') }}</strong>
                                <p class="text-muted small mb-0 mt-1">{{ __('message.pulse_monitoring_desc') }}</p>
                            </div>
                            <label class="switch mb-0">
                                <input type="hidden" name="pulse_enabled" value="false">
                                <input type="checkbox" name="pulse_enabled" value="true" {{ $pe ? 'checked' : '' }}>
                                <span class="slider round"></span>
                            </label>
                        </div>

                        <div class="toggle-row">
                            <div class="pr-3">
                                <strong>{{ __('message.clockwork_debugging') }}</strong>
                                <p class="text-muted small mb-0 mt-1">{{ __('message.clockwork_debugging_desc') }}</p>
                            </div>
                            <label class="switch mb-0">
                                <input type="hidden" name="clockwork_enable" value="false">
                                <input type="checkbox" name="clockwork_enable" value="true" {{ $ce ? 'checked' : '' }}>
                                <span class="slider round"></span>
                            </label>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Application Monitoring --}}
            <div class="col-md-6 mb-3">
                <div class="card border h-100">
                    <div class="card-header d-flex align-items-center bg-light">
                        <i class="fas fa-satellite-dish fa-2x text-primary mr-3"></i>
                        <div>
                            <h6 class="mb-0 font-weight-bold">{{ __('message.application_monitoring') }}</h6>
                            <small class="text-muted">{{ __('message.application_monitoring_desc') }}</small>
                        </div>
                    </div>
                    <div class="card-body">
                        <p class="section-label">{{ __('message.monitoring_options') }}</p>

                        <div class="toggle-row">
                            <div class="pr-3">
                                <strong>{{ __('message.sentry_crash_reporting') }}</strong>
                                <i class="fas fa-question-circle"
                                   data-toggle="tooltip"
                                   title="{{ __('message.sentry_crash_reporting_tooltip') }}"></i>
                                <p class="text-muted small mb-0 mt-1">{{ __('message.sentry_crash_reporting_desc') }}</p>
                            </div>
                            <label class="switch mb-0">
                                <input type="hidden" name="sentry_reporting" value="false">
                                <input type="checkbox" name="sentry_reporting" value="true" {{ $sr ? 'checked' : '' }}>
                                <span class="slider round"></span>
                            </label>
                        </div>

                        <div class="toggle-row">
                            <div class="pr-3">
                                <strong>{{ __('message.sentry_performance') }}</strong>
                                <i class="fas fa-question-circle"
                                   data-toggle="tooltip"
                                   title="{{ __('message.sentry_performance_tooltip') }}"></i>
                                <p class="text-muted small mb-0 mt-1">{{ __('message.sentry_performance_desc') }}</p>
                            </div>
                            <label class="switch mb-0">
                                <input type="hidden" name="sentry_performance" value="false">
                                <input type="checkbox" name="sentry_performance" value="true" {{ $sp ? 'checked' : '' }}>
                                <span class="slider round"></span>
                            </label>
                        </div>
                    </div>
                </div>
            </div>

        </div>

        <div class="mt-2">
            <button type="submit" class="btn btn-primary">
                <i class="fas fa-save"></i> {{ __('message.save') }}
            </button>
        </div>

        {!! html()->form()->close() !!}
    </div>
</div>

<script>
    $(function () { $('[data-toggle="tooltip"]').tooltip(); });
</script>

@stop