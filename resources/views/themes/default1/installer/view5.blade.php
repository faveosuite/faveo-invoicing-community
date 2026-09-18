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
            <p class="text-center lead fw-bold">{{trans('installer_messages.getting_started')}}</p>

            <div class="card card-light">
                <div class="card-header">
                    <h3 class="card-title">{{trans('installer_messages.sign_up_as_admin')}}</h3>
                </div>
                <div class="card-body">
                    <div class="row mb-3">
                        <label class="col-sm-2 col-form-label">{{trans('installer_messages.first_name')}} <span style="color: red;">*</span></label>
                        <div class="col-sm-10">
                            <input type="text" id="admin_first_name" class="form-control" placeholder="{{trans('installer_messages.first_name')}}">
                        </div>
                    </div>

                    <div class="row mb-3">
                        <label class="col-sm-2 col-form-label">{{trans('installer_messages.last_name')}} <span style="color: red;">*</span></label>
                        <div class="col-sm-10">
                            <input type="text" id="admin_last_name" class="form-control" placeholder="{{trans('installer_messages.last_name')}}">
                        </div>
                    </div>

                    <div class="row mb-3">
                        <label class="col-sm-2 col-form-label">{{trans('installer_messages.username')}} <span style="color: red;">*</span>
                            <i class="fas fa-question-circle text-primary" data-bs-toggle="tooltip" data-bs-placement="top" title="{{trans('installer_messages.username_info')}}"></i>
                        </label>
                        <div class="col-sm-10">
                            <input type="text" id="admin_username" class="form-control" placeholder="{{trans('installer_messages.username')}}">
                        </div>
                    </div>

                    <div class="row mb-3">
                        <label class="col-sm-2 col-form-label">{{trans('installer_messages.email')}} <span style="color: red;">*</span></label>
                        <div class="col-sm-10">
                            <input type="email" id="email" class="form-control" placeholder="{{trans('installer_messages.email')}}">
                        </div>
                    </div>

                    <div class="row mb-3">
                        <label class="col-sm-2 col-form-label">{{trans('installer_messages.password')}} <span style="color: red;">*</span></label>
                        <div class="col-sm-10">
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

                    <div class="row mb-3">
                        <label class="col-sm-2 col-form-label">{{trans('installer_messages.confirm_password')}} <span style="color: red;">*</span></label>
                        <div class="col-sm-10">
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
                    <div class="row mb-3">
                        <label class="col-sm-2 col-form-label">
                            {{ trans('installer_messages.timezone') }}
                            <span class="text-danger">*</span>
                            &nbsp;<i class="fas fa-question-circle text-primary"
                                     data-bs-toggle="tooltip"
                                     data-bs-placement="top"
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
                    <div class="row mb-3">
                        <label class="col-sm-2 col-form-label">
                            {{ trans('installer_messages.language') }}
                            <span class="text-danger">*</span>
                            &nbsp;<i class="fas fa-question-circle text-primary"
                                     data-bs-toggle="tooltip"
                                     data-bs-placement="top"
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
                    <div class="row mb-3 align-items-center">
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
                    <div class="row mb-3 align-items-center">
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
                    <div class="row mb-3 align-items-center">
                        <label class="col-sm-2 col-form-label mb-0">{{trans('installer_messages.redis_host')}}<span style="color: red;">*</span></label>
                        <div class="col-sm-10">
                            <input type="text" class="form-control" id="redis_host" placeholder="{{trans('installer_messages.redis_host')}}">
                        </div>
                    </div>

                    <div class="row mb-3 align-items-center">
                        <label class="col-sm-2 col-form-label mb-0">{{trans('installer_messages.redis_port')}}<span style="color: red;">*</span></label>
                        <div class="col-sm-10">
                            <input type="text" class="form-control" id="redis_port" placeholder="{{trans('installer_messages.redis_port')}}">
                        </div>
                    </div>

                    <div class="row mb-3 align-items-center">
                        <label class="col-sm-2 col-form-label mb-0">{{trans('installer_messages.redis_password')}}</label>
                        <div class="col-sm-10">
                            <input type="password" class="form-control" id="redis_password" placeholder="{{trans('installer_messages.redis_password')}}">
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="card-footer">
            <button class="btn btn-primary float-end" onclick="submitForm()">{{trans('installer_messages.continue')}} &nbsp;
                <i class="fas {{ in_array(app()->getLocale(), ['ar', 'he']) ? 'fa-arrow-left' : 'fa-arrow-right' }}"></i>
            </button>
        </div>
    </div>
    <script>
        document.getElementById('admin_username').addEventListener('input', function () {
            this.value = this.value.toLowerCase();
        });
        document.getElementById('email').addEventListener('input', function () {
            this.value = this.value.toLowerCase();
        });
        document.addEventListener('DOMContentLoaded', function () {
            document.querySelectorAll('[data-bs-toggle="tooltip"]').forEach(el => new bootstrap.Tooltip(el));
        });
        (function () {
            const pswdInfo  = document.getElementById('pswd_info');
            const pwdInput  = document.getElementById('admin_password');
            const els = {
                length:  document.getElementById('length'),
                letter:  document.getElementById('letter'),
                capital: document.getElementById('capital'),
                number:  document.getElementById('number'),
                special: document.getElementById('space'),
            };

            function setClass(el, ok) {
                if (!el) return;
                el.classList.toggle('text-success', ok);
                el.classList.toggle('text-danger', !ok);
            }

            pwdInput.addEventListener('focus', () => pswdInfo.style.display = '');
            pwdInput.addEventListener('keyup', function () {
                const v = this.value;
                const checks = {
                    length:  v.length >= 8 && v.length <= 16,
                    letter:  /[a-z]/.test(v),
                    capital: /[A-Z]/.test(v),
                    number:  /\d/.test(v),
                    special: /[~*!@$#%_+.?:,{ }]/.test(v),
                };
                Object.keys(checks).forEach(k => setClass(els[k], checks[k]));
                pswdInfo.style.display = Object.values(checks).every(Boolean) ? 'none' : '';
            });
        })();

        function submitForm() {
            const get = id => document.getElementById(id);
            const val = id => get(id)?.value ?? '';

            // Clear previous errors
            document.querySelectorAll('input.is-invalid, select.is-invalid').forEach(el => el.classList.remove('is-invalid'));
            document.querySelectorAll('.error.invalid-feedback').forEach(el => el.remove());

            const showError = (el, message) => {
                el.classList.add('is-invalid');
                const span = document.createElement('span');
                span.className = 'error invalid-feedback';
                span.textContent = message;
                if (el.id === 'admin_password' || el.id === 'admin_confirm_password') {
                    el.nextElementSibling?.after(span);
                } else {
                    el.after(span);
                }
            };

            let isValid = true;
            const required = {
                admin_first_name:    '{{__("installer_messages.firstname")}}',
                admin_last_name:     '{{__("installer_messages.lastname")}}',
                admin_username:      '{{__("installer_messages.username")}}',
                email:               '{{__("installer_messages.email")}}',
                admin_password:      '{{__("installer_messages.password")}}',
                admin_confirm_password: '{{__("installer_messages.confirm_password")}}',
            };
            Object.entries(required).forEach(([id, label]) => {
                if (!val(id)) { showError(get(id), `${label} {{__('installer_messages.is_required')}}`); isValid = false; }
            });

            const username_regex = /^[a-zA-Z0-9 _\-@.]{3,20}$/;
            const email_regex    = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            const password_regex = /^(?=\S*[a-z])(?=\S*[A-Z])(?=\S*\d)(?=\S*[^\w\s])\S{8,}/;

            if (!username_regex.test(val('admin_username'))) { showError(get('admin_username'), '{{trans("installer_messages.user_name_regex")}}'); isValid = false; }
            if (val('email') && !email_regex.test(val('email'))) { showError(get('email'), '{{trans("installer_messages.invalid_email")}}'); isValid = false; }
            if (!password_regex.test(val('admin_password'))) { showError(get('admin_password'), '{{trans("installer_messages.your_password_invalid")}}'); isValid = false; }
            if (val('admin_password') !== val('admin_confirm_password')) { showError(get('admin_confirm_password'), '{{trans("installer_messages.password_not_match")}}'); isValid = false; }

            if (val('driver') === 'redis') {
                ['redis_host', 'redis_port'].forEach(id => {
                    if (!val(id)) { showError(get(id), `${id.replace('_', ' ')} {{__('installer_messages.is_required')}}`); isValid = false; }
                });
            }

            if (!isValid) return;

            const data = {
                _token:       '{{ csrf_token() }}',
                first_name:   val('admin_first_name'),
                last_name:    val('admin_last_name'),
                user_name:    val('admin_username'),
                email:        val('email'),
                password:     val('admin_password'),
                environment:  val('environment'),
                cache_driver: val('driver'),
                timezone:     val('timezone'),
                language:     val('language'),
            };
            if (val('driver') === 'redis') {
                data.redis_host     = val('redis_host');
                data.redis_port     = val('redis_port');
                data.redis_password = val('redis_password');
            }

            const fieldMap = {
                first_name: 'admin_first_name', last_name: 'admin_last_name',
                user_name: 'admin_username', email: 'email', password: 'admin_password',
                redis_host: 'redis_host', redis_port: 'redis_port', redis_password: 'redis_password',
                environment: 'environment', cache_driver: 'driver', language: 'language', timezone: 'timezone',
            };

            fetch('{{ route("accountcheck") }}', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: new URLSearchParams(data),
            })
            .then(r => r.ok ? r.json() : r.json().then(j => Promise.reject({ status: r.status, json: j })))
            .then(() => { window.location.href = '{{ url("/final") }}'; })
            .catch(err => {
                if (err?.json?.message) {
                    Object.entries(err.json.message).forEach(([field, msgs]) => {
                        const el = get(fieldMap[field]);
                        if (el) showError(el, Array.isArray(msgs) ? msgs.join(', ') : msgs);
                    });
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

        document.querySelector('.toggle-password').addEventListener('click', function () {
            const input = document.getElementById('admin_password');
            const icon = this.querySelector('i');
            const isPassword = input.type === 'password';
            input.type = isPassword ? 'text' : 'password';
            icon.className = isPassword ? 'fa fa-eye' : 'fa fa-eye-slash';
        });

        document.querySelector('.toggle-confirm-password').addEventListener('click', function () {
            const input = document.getElementById('admin_confirm_password');
            const icon = this.querySelector('i');
            const isPassword = input.type === 'password';
            input.type = isPassword ? 'text' : 'password';
            icon.className = isPassword ? 'fa fa-eye' : 'fa fa-eye-slash';
        });
    </script>


@stop
