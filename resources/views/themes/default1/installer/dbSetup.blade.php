@extends('themes.default1.installer.layout.installer')
@section('dbSetup')
    active
@stop

@section('content')
    <style>
        .form-control.is-invalid{
            background-image: none !important;
        }
    </style>
    <div class="card">
        <div class="card-body pb-0">

            <p class="text-center lead text-bold">{{ __('installer_messages.database_setup')}}</p>
            <form id="databaseform">
                @csrf
                <div id="db_fields">
                    <div class="form-group row">
                        <div class="col-sm-6">
                            <label for="host" class="col-form-label">
                                {{ __('installer_messages.host')}} <span style="color: red;">*</span>
                                <i class="fas fa-info-circle" data-toggle="tooltip" data-placement="top" title="{{ __('installer_messages.host_tooltip')}}"></i>
                            </label>
                            <input type="text" class="form-control" id="host" placeholder="{{ __('installer_messages.host')}}" value="localhost">
                        </div>
                        <div class="col-sm-6">
                            <label for="database_name" class="col-form-label">{{ __('installer_messages.database_name_label')}} <span style="color: red;">*</span></label>
                            <input type="text" class="form-control" id="database_name" placeholder="{{ __('installer_messages.database')}}">
                        </div>
                    </div>

                    <div class="form-group row">
                        <div class="col-sm-6">
                            <label for="mysql_port" class="col-form-label">
                                {{ __('installer_messages.mysql_port_label')}}
                                <i class="fas fa-info-circle" data-toggle="tooltip" data-placement="top" title="{{ __('installer_messages.mysql_port_tooltip')}}"></i>
                            </label>
                            <input type="text" class="form-control" id="mysql_port" placeholder="Port Number">
                        </div>
                        <div class="col-sm-6">
                            <label for="username" class="col-form-label">{{ __('installer_messages.username')}} <span style="color: red;">*</span></label>
                            <input type="text" class="form-control" id="username" placeholder="{{ __('installer_messages.username')}}">
                        </div>
                    </div>

                    <div class="form-group row">
                        <div class="col-sm-6">
                            <label for="password" class="col-form-label">{{ __('installer_messages.password')}}</label>
                            <div class="input-group">
                                <input type="password" class="form-control" id="admin_password" placeholder="{{ __('installer_messages.password')}}">
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

            <a class="btn btn-primary" id="previous" href="{{ getUrl() }}/probe.php">    <i class="fas {{ app()->getLocale() == 'ar' ? 'fa-arrow-left' : 'fa-arrow-right' }} previous"></i>&nbsp;
                &nbsp; {{ __('installer_messages.previous')}}</a>

            <button class="btn btn-primary float-right" type="submit" id="validate">{{ __('installer_messages.continue')}} &nbsp;
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
            // const previous = document.getElementById('previous');
            // const continueButton = document.getElementById('continue');
            // previous.disabled = true;
            // continueButton.disabled = true;
            // Collect data from form inputs
            const fields = {
                host: document.getElementById('host'),
                port:document.getElementById('mysql_port'),
                databaseName: document.getElementById('database_name'),
                username: document.getElementById('username'),
                password: document.getElementById('admin_password')
            };

            // Clear previous error messages
            Object.values(fields).forEach(field => {
                field.classList.remove('is-invalid');
                const errorMessage = field.nextElementSibling;
                if (errorMessage && errorMessage.classList.contains('error')) {
                    errorMessage.remove();
                }
            });

            // Helper function to add error messages
            const showError = (field, message) => {
                field.classList.add('is-invalid');
                const errorSpan = document.createElement('span');
                errorSpan.className = 'error invalid-feedback';
                errorSpan.innerText = message;
                field.after(errorSpan);
            };

            // Validate required fields
            let isValid = true;
            const requiredFields = {
                host: 'Host',
                databaseName: 'Database Name',
                username: 'Username',
            };

            Object.keys(requiredFields).forEach(field => {
                if (!fields[field].value.trim()) {
                    showError(fields[field], `${requiredFields[field]} is required`);
                    isValid = false;
                }
            });

            // Stop form submission if validation fails
            if (!isValid) return;

            // Collect data if validation is successful
            const data = {
                host: fields.host.value,
                port: fields.port.value,
                databasename: fields.databaseName.value,
                username: fields.username.value,
                password: fields.password.value
            };

            // Ajax request
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

