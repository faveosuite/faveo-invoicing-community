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
            <p class="text-center lead fw-bold">{{ __('installer_messages.database_setup') }}</p>
            <form id="databaseform">
                @csrf
                <div id="db_fields">
                    {{-- Host --}}
                    <div class="row mb-3">
                        <label for="host" class="col-sm-2 col-form-label">
                            {{ __('installer_messages.host') }} <span style="color: red;">*</span>
                            <i class="fas fa-question-circle text-primary" data-bs-toggle="tooltip" data-bs-placement="top" title="{{ __('installer_messages.host_tooltip') }}"></i>
                        </label>
                        <div class="col-sm-10">
                            <input type="text" class="form-control" id="host" placeholder="{{ __('installer_messages.host') }}" value="localhost">
                        </div>
                    </div>

                    {{-- MySQL Port --}}
                    <div class="row mb-3">
                        <label for="mysql_port" class="col-sm-2 col-form-label">
                            {{ __('installer_messages.mysql_port_label') }}
                            <i class="fas fa-question-circle text-primary" data-bs-toggle="tooltip" data-bs-placement="top" title="{{ __('installer_messages.mysql_port_tooltip') }}"></i>
                        </label>
                        <div class="col-sm-10">
                            <input type="text" class="form-control" id="mysql_port" placeholder="{{ __('installer_messages.port_number') }}">
                        </div>
                    </div>

                    {{-- Database Name --}}
                    <div class="row mb-3">
                        <label for="database_name" class="col-sm-2 col-form-label">
                            {{ __('installer_messages.database_name_label') }} <span style="color: red;">*</span>
                        </label>
                        <div class="col-sm-10">
                            <input type="text" class="form-control" id="database_name" placeholder="{{ __('installer_messages.database') }}">
                        </div>
                    </div>

                    {{-- Username --}}
                    <div class="row mb-3">
                        <label for="username" class="col-sm-2 col-form-label">
                            {{ __('installer_messages.username') }} <span style="color: red;">*</span>
                        </label>
                        <div class="col-sm-10">
                            <input type="text" class="form-control" id="username" placeholder="{{ __('installer_messages.username') }}">
                        </div>
                    </div>

                    {{-- Password --}}
                    <div class="row mb-3">
                        <label for="admin_password" class="col-sm-2 col-form-label">
                            {{ __('installer_messages.password') }}
                        </label>
                        <div class="col-sm-10">
                            <div class="input-group">
                                <input type="password" class="form-control" id="admin_password" placeholder="{{ __('installer_messages.password') }}">
                                <span class="input-group-text toggle-password cursor-pointer"><i class="fas fa-eye-slash"></i></span>
                            </div>
                        </div>
                    </div>
                </div>
            </form>
        </div>

        <div class="card-footer">
            <a class="btn btn-primary" id="previous" href="{{ url('probe.php') }}">
                <i class="fas {{ in_array(app()->getLocale(), ['ar', 'he']) ? 'fa-arrow-left' : 'fa-arrow-right' }} previous"></i>&nbsp;
                {{ __('installer_messages.previous') }}
            </a>

            <button class="btn btn-primary float-end" type="submit" id="validate">
                {{ __('installer_messages.continue') }} &nbsp;
                <i class="fas fa-arrow-right continue"></i>
            </button>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            document.querySelectorAll('[data-bs-toggle="tooltip"]').forEach(el => new bootstrap.Tooltip(el));
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

            // Controller always redirects — submit the form directly
            const form = document.getElementById('databaseform');
            form.action = '{{ route("posting") }}';
            form.method = 'POST';
            // Inject collected data into the form
            Object.entries(data).forEach(([key, value]) => {
                let input = form.querySelector(`[name="${key}"]`);
                if (!input) {
                    input = document.createElement('input');
                    input.type = 'hidden';
                    input.name = key;
                    form.appendChild(input);
                }
                input.value = value;
            });
            form.submit();
        }

        document.querySelector('.toggle-password').addEventListener('click', function () {
            const input = document.getElementById('admin_password');
            const icon = this.querySelector('i');
            const isPassword = input.type === 'password';
            input.type = isPassword ? 'text' : 'password';
            icon.className = isPassword ? 'fas fa-eye' : 'fas fa-eye-slash';
        });
    </script>
@stop