@extends('themes.default1.installer.layout.installer')
@section('dbSetup')
    done
@stop

@section('database')
    done
@stop

@section('get-start')
    active
@stop

@section('content')
    <style>
        .form-control.is-invalid{
            background-image: none !important;
        }
        .tooltip-inner{
            text-align: left;
        }
    </style>

    <div class="card">
        <div class="card-body">
            <p class="text-center lead text-bold">{{trans('installer_messages.getting_started')}}</p>

            <div class="card card-light">
                <div class="card-header">
                    <h3 class="card-title">{{trans('installer_messages.sign_up_as_admin')}}</h3>
                </div>
                <div class="card-body">
                    <div class="form-group row">
                        <label class="col-sm-2 col-form-label">{{trans('installer_messages.first_name')}} <span style="color: red;">*</span></label>
                        <div class="col-sm-10 mb-2">
                            <input type="text" id="admin_first_name" class="form-control" placeholder="{{trans('installer_messages.first_name')}}">
                        </div>
                    </div>

                    <div class="form-group row">
                        <label class="col-sm-2 col-form-label">{{trans('installer_messages.last_name')}} <span style="color: red;">*</span></label>
                        <div class="col-sm-10 mb-2">
                            <input type="text" id="admin_last_name" class="form-control" placeholder="{{trans('installer_messages.last_name')}}">
                        </div>
                    </div>

                    <div class="form-group row">
                        <label class="col-sm-2 col-form-label">{{trans('installer_messages.username')}} <span style="color: red;">*</span>
                            <i class="fas fa-question-circle text-primary" data-toggle="tooltip" data-placement="top" title="{{trans('installer_messages.username_info')}}"></i>
                        </label>
                        <div class="col-sm-10 mb-2">
                            <input type="text" id="admin_username" class="form-control" placeholder="{{trans('installer_messages.username')}}">
                        </div>
                    </div>

                    <div class="form-group row">
                        <label class="col-sm-2 col-form-label">{{trans('installer_messages.email')}} <span style="color: red;">*</span></label>
                        <div class="col-sm-10 mb-2">
                            <input type="email" id="email" class="form-control" placeholder="{{trans('installer_messages.email')}}">
                        </div>
                    </div>

                    <div class="form-group row">
                        <label class="col-sm-2 col-form-label">{{trans('installer_messages.password')}} <span style="color: red;">*</span></label>
                        <div class="col-sm-10 mb-2">
                        <div class="input-group">
                            <input type="password" id="admin_password" class="form-control" placeholder="{{trans('installer_messages.password')}}">
                            <span class="input-group-text toggle-password cursor-pointer"><i class="fa fa-eye-slash"></i></span>
                        </div>
                            <small class="form-text text-muted mt-2" id="pswd_info" style="display: none;">
                                <?php
                                echo '<ul>';
                                foreach (trans('installer_messages.password_requirements_list') as $value) {
                                    echo '<li id="' . $value['id'] . '" class="text-danger">' . $value['text'] . '</li>';
                                }
                                echo '</ul>';
                                ?>
                            </small>
                        </div>
                    </div>

                    <div class="form-group row">
                        <label class="col-sm-2 col-form-label">{{trans('installer_messages.confirm_password')}} <span style="color: red;">*</span></label>
                        <div class="col-sm-10 mb-2">
                            <div class="input-group">
                            <input type="password" id="admin_confirm_password" class="form-control" placeholder="{{trans('installer_messages.confirm_password')}}">
                            <span class="input-group-text toggle-confirm-password cursor-pointer"><i class="fa fa-eye-slash"></i></span>
                        </div>
                        </div>
                    </div>

                </div>
            </div>

            <div class="card card-light">
                <div class="card-header">
                    <h3 class="card-title">{{ trans('installer_messages.system_information') }}</h3>
                </div>

                <div class="card-body">
                    {{-- Timezone --}}
                    <div class="form-group row mb-2">
                        <label class="col-sm-2 col-form-label">
                            {{ trans('installer_messages.timezone') }}
                            <span class="text-danger">*</span>
                            &nbsp;<i class="fas fa-question-circle text-primary"
                                     data-toggle="tooltip"
                                     data-placement="top"
                                     title="{{ trans('installer_messages.tooltip_timezone') }}"
                                     data-content="@{{Hostcontent}}"></i>&nbsp;
                        </label>

                        <div class="col-sm-10 mb-2">
                            <?php
                            $timezonesList = \App\Model\Common\Timezone::orderBy('id', 'ASC')->get();
                            $display = [];

                            foreach ($timezonesList as $timezone) {
                                $location = $timezone->location;
                                $start = strpos($location, '(');
                                $end = strpos($location, ')', $start !== false ? $start + 1 : 0);

                                if ($start !== false && $end !== false && $end > $start) {
                                    $length = $end - $start;
                                    $result = substr($location, $start + 1, $length - 1);
                                    $display[] = ['id' => $timezone->name, 'name' => '(' . $result . ') ' . $timezone->name];
                                } else {
                                    $display[] = ['id' => $timezone->name, 'name' => $timezone->name];
                                }
                            }

                            $timezones = array_column($display, 'name', 'id');
                            $browserTimezone = \Cache::get('timezone');
                            ?>

                            <select id="timezone" name="timeZone" class="form-control select2">
                                @foreach($timezones as $key => $value)
                                    <option value="{!! $key !!}" @if($key == $browserTimezone) selected @endif>
                                        {!! $value !!}&nbsp;
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    {{-- Language --}}
                    <div class="form-group row mb-2">
                        <label class="col-sm-2 col-form-label">
                            {{ trans('installer_messages.language') }}
                            <span class="text-danger">*</span>
                            &nbsp;<i class="fas fa-question-circle text-primary"
                                     data-toggle="tooltip"
                                     data-placement="top"
                                     title="{{ trans('installer_messages.tooltip_language') }}"
                                     data-content="@{{Hostcontent}}"></i>&nbsp;
                        </label>

                        <div class="col-sm-10 mb-2">
                            <?php
                            $path = base_path('lang');
                            $values = array_slice(scandir($path), 2);
                            ?>

                            <select id="language" name="language" class="form-control select2" data-placeholder="{{ trans('installer.choose_timezone') }}">
                                @foreach($values as $value)
                                    <option value="{!! $value !!}" @if($value == "en") selected @endif>
                                        {!! Config::get('languages.' . $value)[0] !!}&nbsp;({!! Config::get('languages.' . $value)[1] !!})
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    {{-- Environment --}}
                    <div class="form-group row align-items-center mb-2">
                        <label class="col-sm-2 col-form-label mb-0">
                            {{ trans('installer_messages.environment') }}
                            <span class="text-danger">*</span>
                        </label>

                        <div class="col-sm-10 mb-2">
                            <select id="environment" name="environment" class="form-control select2">
                                <option value="production" selected>{{ trans('installer_messages.production') }}</option>
                                <option value="development">{{ trans('installer_messages.development') }}</option>
                            </select>
                        </div>
                    </div>

                    {{-- Cache Driver --}}
                    <div class="form-group row align-items-center mb-2">
                        <label class="col-sm-2 col-form-label mb-0">
                            {{ trans('installer_messages.cache_driver') }}
                            <span class="text-danger">*</span>
                        </label>

                        <div class="col-sm-10 mb-2">
                            <select id="driver" name="driver" class="form-control select2">
                                <option value="file" selected>{{ trans('installer_messages.file') }}</option>
                                <option value="redis">{{ trans('installer_messages.redis') }}</option>
                            </select>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card card-light" id="redis-setup" style="display: none;">
                <div class="card-header">
                    <h3 class="card-title">{{trans('installer_messages.redis_setup')}}</h3>
                </div>
                <div class="card-body">
                    <div class="form-group row align-items-center">
                        <label class="col-sm-2 col-form-label mb-0">{{trans('installer_messages.redis_host')}}<span style="color: red;">*</span></label>
                        <div class="col-sm-10">
                            <input type="text" class="form-control" id="redis_host" placeholder="{{trans('installer_messages.redis_host')}}">
                        </div>
                    </div>

                    <div class="form-group row align-items-center">
                        <label class="col-sm-2 col-form-label mb-0">{{trans('installer_messages.redis_port')}}<span style="color: red;">*</span></label>
                        <div class="col-sm-10">
                            <input type="text" class="form-control" id="redis_port" placeholder="{{trans('installer_messages.redis_port')}}">
                        </div>
                    </div>

                    <div class="form-group row align-items-center">
                        <label class="col-sm-2 col-form-label mb-0">{{trans('installer_messages.redis_password')}}</label>
                        <div class="col-sm-10">
                            <input type="password" class="form-control" id="redis_password" placeholder="{{trans('installer_messages.redis_password')}}">
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="card-footer">
            <button class="btn btn-primary float-right" onclick="submitForm()">{{trans('installer_messages.continue')}} &nbsp;
                <i class="fas {{ in_array(app()->getLocale(), ['ar', 'he']) ? 'fa-arrow-left' : 'fa-arrow-right' }}"></i>
            </button>
        </div>
    </div>
    <script src="{{ asset('admin/js/jquery.min.js') }}"></script>


    <script type="text/javascript">
        document.getElementById('admin_username').addEventListener('input',function (){
            this.value = this.value.toLowerCase();
        });
        document.getElementById('email').addEventListener('input',function (){
            this.value = this.value.toLowerCase();
        });
        $(document).ready(function(){
            $('[data-toggle="tooltip"]').tooltip();
        });
        $(document).ready(function() {
            // Cache the selectors for better performance
            var $pswdInfo = $('#pswd_info');
            var $newPassword = $('#admin_password');
            var $length = $('#length');
            var $letter = $('#letter');
            var $capital = $('#capital');
            var $number = $('#number');
            var $special = $('#space');

            // Function to update validation classes
            function updateClass(condition, $element) {
                $element.toggleClass('text-success', condition).toggleClass('text-danger', !condition);
            }

            // Initially hide the password requirements
            $pswdInfo.hide();

            // Show password requirements on focus
            $newPassword.focus(function() {
                $pswdInfo.show();
            });

            // Perform real-time validation on keyup
            $newPassword.on('keyup', function() {
                var pswd = $(this).val();

                // Validate the length (8 to 16 characters)
                var lengthValid = pswd.length >= 8 && pswd.length <= 16;
                updateClass(lengthValid, $length);

                // Validate lowercase letter
                var letterValid = /[a-z]/.test(pswd);
                updateClass(letterValid, $letter);

                // Validate uppercase letter
                var capitalValid = /[A-Z]/.test(pswd);
                updateClass(capitalValid, $capital);

                // Validate number
                var numberValid = /\d/.test(pswd);
                updateClass(numberValid, $number);

                // Validate special character
                var specialValid = /[~*!@$#%_+.?:,{ }]/.test(pswd);
                updateClass(specialValid, $special);

                // Show or hide password requirements based on validation
                if (lengthValid && letterValid && capitalValid && numberValid && specialValid) {
                    $pswdInfo.hide();
                } else {
                    $pswdInfo.show();
                }
            });
        });

        function submitForm() {
            // Cache input elements
            const fields = {
                firstName: $('#admin_first_name'),
                lastName: $('#admin_last_name'),
                username: $('#admin_username'),
                email: $('#email'),
                password: $('#admin_password'),
                confirmPassword: $('#admin_confirm_password'),
                environment: $('#environment'),
                driver: $('#driver'),
                redisHost: $('#redis_host'),
                redisPort: $('#redis_port'),
                redisPassword: $('#redis_password'),
                language: $('#language'),
                timezone: $('#timezone')
            };

            // Clear previous errors
            $('input').removeClass('is-invalid');
            $('.error').remove();

            // Helper function to add error messages
            const showError = (field, message) => {
                field.addClass('is-invalid');
                if (field[0].id === 'admin_password' || field[0].id === 'admin_confirm_password') {
                    field.next('span').after(`<span class="error invalid-feedback">${message}</span>`);
                } else {
                    field.after(`<span class="error invalid-feedback">${message}</span>`);
                }
            };

            // Validate fields
            let isValid = true;
            const requiredFields = {
                firstName: '{{__('installer_messages.firstname')}}',
                lastName: '{{__('installer_messages.lastname')}}',
                username: '{{__('installer_messages.username')}}',
                email: '{{__('installer_messages.email')}}',
                password: '{{__('installer_messages.password')}}',
                confirmPassword: '{{__('installer_messages.confirm_password')}}'
            };

            Object.keys(requiredFields).forEach(field => {
                if (!fields[field].val()) {
                    showError(fields[field], `${requiredFields[field]} {{__('installer_messages.is_required')}}`);
                    isValid = false;
                }
            });

            const username_regex = /^[a-zA-Z0-9 _\-@.]{3,20}$/;
            const email_regex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            const password_regex = /^(?=\S*[a-z])(?=\S*[A-Z])(?=\S*\d)(?=\S*[^\w\s])\S{8,}/;

            if (!username_regex.test(fields.username.val())) {
                showError(fields.username, '{{trans('installer_messages.user_name_regex')}}');
                isValid = false;
            }
            if (fields.email.val() && !email_regex.test(fields.email.val())) {
                showError(fields.email, '{{trans('installer_messages.invalid_email')}}');
                isValid = false;
            }
            if (!password_regex.test(fields.password.val())) {
                showError(fields.password, '{{trans('installer_messages.your_password_invalid')}}');
                isValid = false;
            }

            // Validate passwords match
            if (fields.password.val() !== fields.confirmPassword.val()) {
                showError(fields.confirmPassword, '{{trans('installer_messages.password_not_match')}}');
                isValid = false;
            }

            // Validate Redis fields if driver is set to 'redis'
            if (fields.driver.val() === 'redis') {
                const redisFields = {
                    redisHost: 'Redis Host',
                    redisPort: 'Redis Port',
                };

                Object.keys(redisFields).forEach(field => {
                    if (!fields[field].val()) {
                        showError(fields[field], `${redisFields[field]} {{__('installer_messages.is_required')}}`);
                        isValid = false;
                    }
                });
            }

            if (!isValid) return; // Stop if validation fails

            // Collect data
            const data = {
                first_name: fields.firstName.val(),
                last_name: fields.lastName.val(),
                user_name: fields.username.val(),
                email: fields.email.val(),
                password: fields.password.val(),
                environment: fields.environment.val(),
                cache_driver: fields.driver.val(),
                timezone: fields.timezone.val(),
                language: fields.language.val()
            };

            if (fields.driver.val() === 'redis') {
                data.redis_host = fields.redisHost.val();
                data.redis_port = fields.redisPort.val();
                data.redis_password = fields.redisPassword.val();
            }

            // Send AJAX request
            const url = '{{ route("accountcheck") }}';
            $.ajax({
                url: url,
                type: 'POST',
                data: data,
                success: function(response) {
                    console.log('Form submitted successfully');
                    window.location.href = '{{ url("/final") }}';
                },
                error: function(error) {
                    let errors = error.responseJSON.message;
                    const fieldMapping = {
                        first_name: '#admin_first_name',
                        last_name: '#admin_last_name',
                        user_name: '#admin_username',
                        email: '#email',
                        password: '#admin_password',
                        redis_host: '#redis_host',
                        redis_port: '#redis_port',
                        redis_password: '#redis_password',
                        environment: '#environment',
                        cache_driver: '#driver',
                        language: '#language',
                        timezone: '#timezone'
                    };

                    // Loop through the errors and display them on the corresponding fields
                    Object.keys(errors).forEach(field => {
                        const errorMessages = errors[field];
                        const fieldElement = $(fieldMapping[field]); // Use the mapped field ID
                        if (fieldElement.length) {
                            showError(fieldElement, errorMessages.join(', '));
                        }
                    });
                    if (error.status === 400) {
                        showAlert(error.responseJSON.message, 'danger', true, 5000);
                    }
                }
            });
        }
        document.addEventListener('DOMContentLoaded', function() {
            // Get the driver select element and Redis setup section
            const driverSelect = document.getElementById('driver');
            const redisSetup = document.getElementById('redis-setup');

            // Function to toggle Redis setup visibility
            function toggleRedisSetup() {
                if (driverSelect.value === 'redis') {
                    redisSetup.style.display = 'block';
                } else {
                    redisSetup.style.display = 'none';
                }
            }

            // Initial check when the page loads
            toggleRedisSetup();

            // Add event listener for driver select change
            driverSelect.addEventListener('change', toggleRedisSetup);
        });

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

        $('.toggle-confirm-password').click(function() {
            const input = $('#admin_confirm_password');
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
