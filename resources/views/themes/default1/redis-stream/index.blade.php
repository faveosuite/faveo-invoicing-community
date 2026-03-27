@extends('themes.default1.layouts.master')
@section('title')
    {{ __('message.redis_streams') }}
@stop
@section('content-header')
    <div class="col-sm-6">
        <h1>{{ __('message.redis_streams') }}</h1>
    </div>
    <div class="col-sm-6">
        <ol class="breadcrumb float-sm-right">
            <li class="breadcrumb-item"><a href="{{url('/')}}"><i class="fa fa-dashboard"></i> {{ __('message.home') }}</a></li>
            <li class="breadcrumb-item"><a href="{{url('settings')}}"><i class="fa fa-dashboard"></i> {{ __('message.settings') }}</a></li>
            <li class="breadcrumb-item active">{{ __('message.redis_streams') }}</li>
        </ol>
    </div><!-- /.col -->
@stop
@section('content')

    <div id="alertMessage"></div>

    <div class="card card-secondary card-outline">
        <div class="card-body">
            <div class="row">
                <div class="col-md-6 form-group">
                    {!! html()->label(__('message.host'))->for('stream_redis_host') !!}
                    <span class="text-red"> *</span>
                    {!! html()->text('stream_redis_host', $currentValues['stream_redis_host'])
                        ->class('form-control')
                        ->placeholder('127.0.0.1')
                        ->id('stream_redis_host') !!}
                </div>
                <div class="col-md-6 form-group">
                    {!! html()->label(__('message.port'))->for('stream_redis_port') !!}
                    <span class="text-red"> *</span>
                    {!! html()->text('stream_redis_port', $currentValues['stream_redis_port'])
                        ->class('form-control')
                        ->placeholder('6379')
                        ->id('stream_redis_port') !!}
                </div>
                <div class="col-md-6 form-group">
                    {!! html()->label(__('message.username'))->for('stream_redis_username') !!}
                    {!! html()->text('stream_redis_username', $currentValues['stream_redis_username'])
                        ->class('form-control')
                        ->placeholder(__('message.username'))
                        ->id('stream_redis_username') !!}
                </div>
                <div class="col-md-6 form-group">
                    {!! html()->label(__('message.password'))->for('stream_redis_password') !!}
                    <div class="input-group">
                        {!! html()->input('password', 'stream_redis_password', $currentValues['stream_redis_password'])
                            ->class('form-control')
                            ->placeholder(__('message.password'))
                            ->id('stream_redis_password') !!}
                        <div class="input-group-append">
                            <span class="input-group-text" style="cursor:pointer;" onclick="togglePassword()">
                                <i class="fas fa-eye-slash" id="togglePasswordIcon"></i>
                            </span>
                        </div>
                    </div>
                </div>
                <div class="col-md-6 form-group">
                    {!! html()->label(__('message.database'))->for('stream_redis_database') !!}
                    <span class="text-red"> *</span>
                    {!! html()->text('stream_redis_database', $currentValues['stream_redis_database'])
                        ->class('form-control')
                        ->placeholder('2')
                        ->id('stream_redis_database') !!}
                </div>
            </div>
        </div>
        <div class="card-footer">
            <button type="button" class="btn btn-primary pull-right" id="submitButton">
                <i class="fa fa-save" id="submitIcon">&nbsp;&nbsp;</i><span id="submitText">{{ __('message.save') }}</span>
            </button>
            <button type="button" class="btn btn-secondary pull-right mr-3" id="testConnectionButton">
                <i class="fas fa-plug" id="testIcon"></i> &nbsp;<span id="testText">{{ __('message.faveo_licenser_ping') }}</span>
            </button>
        </div>
    </div>

<script>
    $('ul.nav-sidebar a').filter(function() {
        return this.id == 'setting';
    }).addClass('active');

    function togglePassword() {
        var field = $('#stream_redis_password');
        var icon = $('#togglePasswordIcon');
        if (field.attr('type') === 'password') {
            field.attr('type', 'text');
            icon.removeClass('fa-eye-slash').addClass('fa-eye');
        } else {
            field.attr('type', 'password');
            icon.removeClass('fa-eye').addClass('fa-eye-slash');
        }
    }

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

        $('input').on('input', function () {
            $(this).removeClass('is-invalid');
            $(this).next('.error').remove();
        });

        $('#submitButton').on('click', function () {
            var $btn = $(this);
            var $icon = $('#submitIcon');
            var $text = $('#submitText');
            var isValid = true;

            // Clear previous errors
            $('input').removeClass('is-invalid');
            $('.error').remove();
            $('#alertMessage').html('');

            // Validate required fields
            var requiredFields = ['stream_redis_host', 'stream_redis_port', 'stream_redis_database'];
            requiredFields.forEach(function (field) {
                var $field = $('#' + field);
                if (!$field.val()) {
                    $field.addClass('is-invalid');
                    $field.after('<span class="error invalid-feedback">{{ __("message.field_required") }}</span>');
                    isValid = false;
                }
            });

            if (!isValid) return;

            $.ajax({
                method: 'POST',
                url: "{{ route('redis-stream.update') }}",
                data: {
                    "_token": "{{ csrf_token() }}",
                    "stream_redis_host": $('#stream_redis_host').val(),
                    "stream_redis_port": $('#stream_redis_port').val(),
                    "stream_redis_username": $('#stream_redis_username').val(),
                    "stream_redis_password": $('#stream_redis_password').val(),
                    "stream_redis_database": $('#stream_redis_database').val()
                },
                beforeSend: function() {
                    $btn.prop('disabled', true);
                    $('#alertMessage').html('');
                },
                success: function (response) {
                    showAlert('success', response.message || '{{ __("message.redis_stream_config_updated") }}');
                },
                error: function (xhr) {
                    var response = xhr.responseJSON || {};
                    showAlert('error', response.message || '{{ __("message.something_went_wrong") }}');
                    console.error('Form submission error:', xhr);
                },
                complete: function () {
                    $btn.prop('disabled', false);
                    setTimeout(function () {
                        $('#alertMessage').html('');
                    }, 5000);
                }
            });
        });

        $('#testConnectionButton').on('click', function () {
            var $btn = $(this);
            var isValid = true;

            $('input').removeClass('is-invalid');
            $('.error').remove();
            $('#alertMessage').html('');

            var requiredFields = ['stream_redis_host', 'stream_redis_port', 'stream_redis_database'];
            requiredFields.forEach(function (field) {
                var $field = $('#' + field);
                if (!$field.val()) {
                    $field.addClass('is-invalid');
                    $field.after('<span class="error invalid-feedback">{{ __("message.field_required") }}</span>');
                    isValid = false;
                }
            });

            if (!isValid) return;

            $.ajax({
                method: 'POST',
                url: "{{ route('redis-stream.test') }}",
                data: {
                    "_token": "{{ csrf_token() }}",
                    "stream_redis_host": $('#stream_redis_host').val(),
                    "stream_redis_port": $('#stream_redis_port').val(),
                    "stream_redis_username": $('#stream_redis_username').val(),
                    "stream_redis_password": $('#stream_redis_password').val(),
                    "stream_redis_database": $('#stream_redis_database').val()
                },
                beforeSend: function() {
                    $btn.prop('disabled', true);
                    $('#testIcon').removeClass('fa-plug').addClass('fa-spinner fa-spin');
                    $('#testText').text('{{ __("message.pinging") }}');
                },
                success: function (response) {
                    showAlert('success', response.message || '{{ __("message.connection_successful") }}');
                },
                error: function (xhr) {
                    var response = xhr.responseJSON || {};
                    showAlert('error', response.message || '{{ __("message.something_went_wrong") }}');
                },
                complete: function () {
                    $btn.prop('disabled', false);
                    $('#testIcon').removeClass('fa-spinner fa-spin').addClass('fa-plug');
                    $('#testText').text('{{ __("message.faveo_licenser_ping") }}');
                    setTimeout(function () {
                        $('#alertMessage').html('');
                    }, 5000);
                }
            });
        });
    });
</script>

@stop
