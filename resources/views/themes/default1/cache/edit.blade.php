@extends('themes.default1.layouts.master')
@section('title')
    {{ __('message.configure_cache') }} - {{ $driverConfig['name'] }}
@stop
@section('content-header')
    <div class="col-sm-6">
        <h1>{{ $driverConfig['name'] }}</h1>
    </div>
    <div class="col-sm-6">
        <ol class="breadcrumb float-sm-right">
            <li class="breadcrumb-item"><a href="{{url('/')}}"><i class="fa fa-dashboard"></i> {{ __('message.home') }}</a></li>
            <li class="breadcrumb-item"><a href="{{url('settings')}}"><i class="fa fa-dashboard"></i> {{ __('message.settings') }}</a></li>
            <li class="breadcrumb-item"><a href="{{ route('cache.index') }}"><i class="fa fa-dashboard"></i> {{ __('message.cache_drivers') }}</a></li>
            <li class="breadcrumb-item active">{{ __('message.configure_cache') }}</li>
        </ol>
    </div>
@stop
@section('content')

 <div id="alertMessage"></div>

<div class="card card-secondary card-outline">
        <div class="card-body">

            @if($driver === 'redis')
                <div class="row">
                    <div class="col-md-6 form-group">
                        {!! html()->label(__('message.redis_connection'))->for('connection_redis') !!}
                        <span class="text-red"> *</span>
                        {!! html()->text('connection_redis', $currentValues['connection_redis'])
                            ->class('form-control')
                            ->placeholder('cache')
                            ->id('connection_redis') !!}
                    </div>
                </div>
            @endif

            <button type="button" class="form-group btn btn-primary pull-right" id="submitButton">
                <i class="fa fa-save" id="submitIcon">&nbsp;&nbsp;</i><span id="submitText">{{ __('message.save') }}</span>
            </button>
        </div>
    </div>

<script>
    $('ul.nav-sidebar a').filter(function() {
        return this.id == 'setting';
    }).addClass('active');

    $('ul.nav-treeview a').filter(function() {
        return this.id == 'setting';
    }).parentsUntil(".nav-sidebar > .nav-treeview").addClass('menu-open').prev('a').addClass('active');

    function showAlert(type, message) {
        var icon = type === 'success' ? 'fa-check-circle' : 'fa-ban';
        var alertClass = type === 'success' ? 'alert-success' : 'alert-danger';
        $('#alertMessage').html(
            '<div class="alert ' + alertClass + ' alert-dismissable">' +
            '<i class="fa ' + icon + '"></i> ' + message +
            '<button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>' +
            '</div>'
        );
    }

    $(document).ready(function () {

        $('#connection_redis').on('input', function () {
            $(this).removeClass('is-invalid');
            $(this).next('.error').remove();
        });

        $('#submitButton').on('click', function () {
            var connectionRedis = $('#connection_redis');
            var $btn = $(this);
            var $icon = $('#submitIcon');
            var $text = $('#submitText');

            // Clear previous errors
            connectionRedis.removeClass('is-invalid');
            connectionRedis.next('.error').remove();
            $('#alertMessage').html('');

            if (!connectionRedis.val()) {
                connectionRedis.addClass('is-invalid');
                connectionRedis.after('<span class="error invalid-feedback">{{ __("message.field_required") }}</span>');
                return;
            }

            $.ajax({
                method: 'POST',
                url: "{{ route('cache.update', $driver) }}",
                data: {
                    "_token": "{{ csrf_token() }}",
                    "connection_redis": connectionRedis.val()
                },
                beforeSend: function() {
                    $btn.prop('disabled', true);
                    $icon.removeClass('fa-save').addClass('fa-spinner fa-spin');
                    $text.text('{{ __("message.saving") }}');
                    $('#alertMessage').html('');
                },
                success: function (response) {
                    showAlert('success', response.message || '{{ __("message.cache_driver_updated") }}');
                    setTimeout(function () {
                        window.location.href = "{{ route('cache.index') }}";
                    }, 3000);
                },
                error: function (xhr) {
                    var response = xhr.responseJSON || {};
                    showAlert('error', response.message || '{{ __("message.something_went_wrong") }}');
                    console.error('Form submission error:', xhr);
                },
                complete: function () {
                    $btn.prop('disabled', false);
                    $icon.removeClass('fa-spinner fa-spin').addClass('fa-save');
                    $text.text('{{ __('message.save') }}');
                }
            });
        });
    });
</script>

@stop
