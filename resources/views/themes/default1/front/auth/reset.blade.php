@extends('themes.default1.layouts.front.master')
@section('title')
Reset Password | Faveo Helpdesk
@stop
@section('page-heading')
Reset Your Password
@stop
@section('page-header')
Reset Password
@stop
@section('breadcrumb')
    @if(Auth::check())
        <li><a class="text-primary" href="{{url('my-invoices')}}">Home</a></li>
    @else
         <li><a class="text-primary" href="{{url('login')}}">Home</a></li>
    @endif
     <li class="active text-dark">Reset Password</li>
@stop 
@section('main-class') 
main
@stop
@section('content')


     <div class="container py-4">

            <div class="row justify-content-center">

                <div class="col-md-6 col-lg-6 mb-5 mb-lg-0 pe-5">

                    {!!  Form::open(['url'=>'/password/reset', 'method'=>'post' , 'id' => 'changePasswordForm']) !!}
                            <input type="hidden" name="token" value="{{ $reset_token }}">
                            <input type="hidden" name="email" value="{{ $email }}">

                        <div class="row">

                            <div class="form-group col {{ $errors->has('password') ? 'has-error' : '' }}">

                                <label class="form-label text-color-dark text-3">New Password <span class="text-color-danger">*</span></label>
                                <div class="input-group">
                                <input type="password" id="password" value="" class="form-control form-control-lg text-4" placeholder="Password" name='password'<?php if( count($errors) > 0) {?> style="width: 98%;position: relative;left: 5px;"<?php } ?>>
                                    <div class="input-group-append">
                                        <span class="input-group-text" role="button" onclick="togglePasswordVisibility(this)">
                                            <i class="fa fa-eye-slash"></i>
                                        </span>
                                    </div>
                                </div>
                                <small class="text-sm text-muted" id="pswd_info" style="display: none;">
                                    <span class="font-weight-bold">{{ \Lang::get('message.password_requirements') }}</span>
                                    <ul class="pl-4">
                                        @foreach (\Lang::get('message.password_requirements_list') as $requirement)
                                            <li id="{{ $requirement['id'] }}" class="text-danger">{{ $requirement['text'] }}</li>
                                        @endforeach
                                    </ul>
                                </small>

                            </div>
                        </div>

                        <div class="row">

                            <div class="form-group col {{ $errors->has('password_confirmation') ? 'has-error' : '' }}">

                                <label class="form-label text-color-dark text-3">Confirm Password <span class="text-color-danger">*</span></label>
                                <div class="input-group">
                                {!! Form::password('password_confirmation',['placeholder'=>'Retype password','class' => 'form-control form-control-lg text-4' , 'id' => 'confirm_password']) !!}

                                <div class="input-group-append">
                                        <span class="input-group-text" role="button" onclick="togglePasswordVisibility(this)">
                                            <i class="fa fa-eye-slash"></i>
                                        </span>
                                </div>
                            </div>
                            </div>
                        </div>

                        <div class="row">

                            <div class="form-group col">

                                <button type="submit" class="btn btn-dark btn-modern w-100 text-uppercase font-weight-bold text-3 py-3" data-loading-text="Loading...">Reset Password</button>

                            </div>
                        </div>
                    {!! Form::close() !!}
                </div>
            </div>
        </div>

    <script>
        $(document).ready(function() {
            const requiredFields = {
                password: @json(trans('message.password_required')),
                confirm_password: @json(trans('message.confirm_pass_required')),
            };

            const pattern = /^(?=.*[A-Z])(?=.*[a-z])(?=.*\d)(?=.*[~*!@$#%_+.?:,{ }])[A-Za-z\d~*!@$#%_+.?:,{ }]{8,16}$/;

            $('#changePasswordForm').on('submit', function(e) {
                const fields = {
                    password: $('#password'),
                    confirm_password: $('#confirm_password'),
                };

                // Clear previous errors
                Object.values(fields).forEach(field => {
                    field.removeClass('is-invalid');
                    field.next().next('.error').remove();
                });

                let isValid = true;

                const showError = (field, message) => {
                    field.addClass('is-invalid');
                    field.next().after(`<span class='error invalid-feedback'>${message}</span>`);
                };

                // Validate required fields
                Object.keys(fields).forEach(field => {
                    if (!fields[field].val()) {
                        showError(fields[field], requiredFields[field]);
                        isValid = false;
                    }
                });

                // Validate new password against the regex
                if (isValid && !pattern.test(fields.password.val())) {
                    showError(fields.password, @json(trans('message.strong_password')));
                    isValid = false;
                }

                // Check if new password and confirm password match
                if (isValid && fields.password.val() !== fields.confirm_password.val()) {
                    showError(fields.confirm_password, @json(trans('message.password_mismatch')));
                    isValid = false;
                }

                // If validation fails, prevent form submission
                if (!isValid) {
                    e.preventDefault();
                }
            });

            // Function to remove error when inputting data
            const removeErrorMessage = (field) => {
                field.classList.remove('is-invalid');
                const error = field.nextElementSibling;
                if (error && error.classList.contains('error')) {
                    error.remove();
                }
            };

            // Add input event listeners for all fields
            ['password', 'confirm_password'].forEach(id => {
                document.getElementById(id).addEventListener('input', function() {
                    removeErrorMessage(this);
                });
            });
        });

        $(document).ready(function() {
            // Cache the selectors for better performance
            var $pswdInfo = $('#pswd_info');
            var $password = $('#password');
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

            // Show/hide password requirements on focus/blur
            $password.focus(function() {
                $pswdInfo.show();
            }).blur(function() {
                $pswdInfo.hide();
            });

            // Perform real-time validation on keyup
            $password.on('keyup', function() {
                var pswd = $(this).val();

                // Validate the length (8 to 16 characters)
                updateClass(pswd.length >= 8 && pswd.length <= 16, $length);

                // Validate lowercase letter
                updateClass(/[a-z]/.test(pswd), $letter);

                // Validate uppercase letter
                updateClass(/[A-Z]/.test(pswd), $capital);

                // Validate number
                updateClass(/\d/.test(pswd), $number);

                // Validate special character
                updateClass(/[~*!@$#%_+.?:,{ }]/.test(pswd), $special);
            });
        });
    </script>
@stop