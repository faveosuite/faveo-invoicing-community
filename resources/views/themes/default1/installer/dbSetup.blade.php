@extends('themes.default1.installer.layout.installer')
@section('dbSetup')
    active
@stop

@section('content')
    <style>
        .form-control.is-invalid {
            background-image: none !important;
        }
        .tooltip-inner {
            text-align: left;
        }
    </style>

    <div class="card">
        <div class="card-body pb-0">
            <p class="text-center lead text-bold">{{ __('installer_messages.database_setup') }}</p>
            <form id="databaseform">
                @csrf
                <div id="db_fields">
                    {{-- Host --}}
                    <div class="form-group row">
                        <label for="host" class="col-sm-2 col-form-label">
                            {{ __('installer_messages.host') }} <span style="color: red;">*</span>
                            <i class="fas fa-question-circle text-primary" data-toggle="tooltip" data-placement="top" title="{{ __('installer_messages.host_tooltip') }}"></i>
                        </label>
                        <div class="col-sm-10">
                            <input type="text" class="form-control" id="host" placeholder="{{ __('installer_messages.host') }}" value="localhost">
                        </div>
                    </div>

                    {{-- MySQL Port --}}
                    <div class="form-group row">
                        <label for="mysql_port" class="col-sm-2 col-form-label">
                            {{ __('installer_messages.mysql_port_label') }}
                            <i class="fas fa-question-circle text-primary" data-toggle="tooltip" data-placement="top" title="{{ __('installer_messages.mysql_port_tooltip') }}"></i>
                        </label>
                        <div class="col-sm-10">
                            <input type="text" class="form-control" id="mysql_port" placeholder="{{ __('installer_messages.port_number') }}">
                        </div>
                    </div>

                    {{-- Database Name --}}
                    <div class="form-group row">
                        <label for="database_name" class="col-sm-2 col-form-label">
                            {{ __('installer_messages.database_name_label') }} <span style="color: red;">*</span>
                        </label>
                        <div class="col-sm-10">
                            <input type="text" class="form-control" id="database_name" placeholder="{{ __('installer_messages.database') }}">
                        </div>
                    </div>

                    {{-- Username --}}
                    <div class="form-group row">
                        <label for="username" class="col-sm-2 col-form-label">
                            {{ __('installer_messages.username') }} <span style="color: red;">*</span>
                        </label>
                        <div class="col-sm-10">
                            <input type="text" class="form-control" id="username" placeholder="{{ __('installer_messages.username') }}">
                        </div>
                    </div>

                    {{-- Password --}}
                    <div class="form-group row">
                        <label for="admin_password" class="col-sm-2 col-form-label">
                            {{ __('installer_messages.password') }}
                        </label>
                        <div class="col-sm-10">
                            <div class="input-group">
                                <input type="password" class="form-control" id="admin_password" placeholder="{{ __('installer_messages.password') }}">
                                <div class="input-group-append">
                                    <span class="input-group-text toggle-password cursor-pointer"><i class="fas fa-eye-slash"></i></span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </form>
        </div>

        <div class="card-footer">
            <a class="btn btn-primary" id="previous" href="{{ url('probe.php')  }}">
                <i class="fas {{ in_array(app()->getLocale(), ['ar', 'he']) ? 'fa-arrow-left' : 'fa-arrow-right' }} previous"></i>&nbsp;
                {{ __('installer_messages.previous') }}
            </a>

            <button class="btn btn-primary float-right" type="submit" id="validate">
                {{ __('installer_messages.continue') }} &nbsp;
                <i class="fas fa-arrow-right continue"></i>
            </button>
        </div>
    </div>

    <script src="{{ asset('admin/js/jquery.min.js') }}"></script>

    <script type="text/javascript">
        $(document).ready(function(){
            $('[data-toggle="tooltip"]').tooltip();
        });

        document.getElementById('validate').addEventListener('click', function(event) {
            event.preventDefault();
            dbFormSubmit();
        });

        function dbFormSubmit() {
            const fields = {
                host: document.getElementById('host'),
                port: document.getElementById('mysql_port'),
                databaseName: document.getElementById('database_name'),
                username: document.getElementById('username'),
                password: document.getElementById('admin_password')
            };

            Object.values(fields).forEach(field => {
                field.classList.remove('is-invalid');
                const errorMessage = field.nextElementSibling;
                if (errorMessage && errorMessage.classList.contains('error')) {
                    errorMessage.remove();
                }
            });

            const showError = (field, message) => {
                field.classList.add('is-invalid');
                const errorSpan = document.createElement('span');
                errorSpan.className = 'error invalid-feedback';
                errorSpan.innerText = message;
                field.after(errorSpan);
            };

            let isValid = true;
            const requiredFields = {
                host: '{{__('installer_messages.host')}}',
                databaseName: '{{__('installer_messages.database_name')}}',
                username: '{{__('installer_messages.username')}}',
            };

            Object.keys(requiredFields).forEach(field => {
                if (!fields[field].value.trim()) {
                    showError(fields[field], `${requiredFields[field]} {{__('installer_messages.is_required')}}`);
                    isValid = false;
                }
            });

            if (!isValid) return;

            const data = {
                host: fields.host.value,
                port: fields.port.value,
                databasename: fields.databaseName.value,
                username: fields.username.value,
                password: fields.password.value
            };

            const url = '{{ route("posting") }}';
            $.ajax({
                url: url,
                type: 'POST',
                data: data,
                success: function(response) {
                    console.log('Success:', response);
                    window.location.href = '{{ url("/post-check") }}';
                },
                error: function(error) {
                    console.error('Error:', error);
                    alert('An error occurred while submitting the form.');
                }
            });
        }

        $('.toggle-password').click(function() {
            const input = $('#admin_password');
            const icon = $(this).find('i');

            if (input.attr('type') === 'password') {
                input.attr('type', 'text');
                icon.removeClass('fa-eye-slash').addClass('fa-eye');
            } else {
                input.attr('type', 'password');
                icon.removeClass('fa-eye').addClass('fa-eye-slash');
            }
        });
    </script>
@stop