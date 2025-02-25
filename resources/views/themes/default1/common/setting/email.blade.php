@extends('themes.default1.layouts.master')
@section('title')
Email
@stop
@section('content-header')
    <div class="col-sm-6">
        <h1>Configure Mail</h1>
    </div>
    <div class="col-sm-6">
        <ol class="breadcrumb float-sm-right">
            <li class="breadcrumb-item"><a href="{{url('/')}}"><i class="fa fa-dashboard"></i> Home</a></li>
            <li class="breadcrumb-item"><a href="{{url('settings')}}"><i class="fa fa-dashboard"></i> Settings</a></li>
            <li class="breadcrumb-item active">Email</li>
        </ol>
    </div><!-- /.col -->
@stop
@section('content')
 <div id="alertMessage"></div>


<div class="card card-secondary card-outline">

       
            <div class="card-body">
                  <div class="col-md-12">


                      <tr>
                        <div class="form-group {{ $errors->has('driver') ? 'has-error' : '' }}">
                        <td><b>{!! Form::label('driver',Lang::get('message.driver'),['class'=>'required']) !!}</b></td>
                        <td>



                                {!! Form::select('driver',[''=>'Choose','smtp'=>'SMTP','mail'=>'Php mail','mailgun'=>'Mailgun','mandrill'=>'Mandrill','ses'=>'SES','sparkpost'=>'Sparkpost'],$set->driver,['class' => 'form-control', 'id'=>'driver']) !!}
                                <i> {{Lang::get('message.select-email-driver')}}</i>
                            @error('driver')
                            <span class="error-message"> {{$message}}</span>
                            @enderror


                        </td>
                        </div>
                    </tr>
                    <tr>
                        <div class="form-group showWhenSmtpSelected">
                        <td><b>{!! Form::label('port',Lang::get('message.port'),['class'=>'required']) !!}</b></td>
                        <td>



                                {!! Form::text('port',$set->port,['class' => 'form-control','id'=>'port']) !!}
                                <i> {{Lang::get('message.enter-email-port')}}</i>
                            @error('port')
                            <span class="error-message"> {{$message}}</span>
                            @enderror

                        </td>
                        </div>
                    </tr>
                    <tr>
                        <div class="form-group showWhenSmtpSelected">
                        <td><b>{!! Form::label('host',Lang::get('message.host'),['class'=>'required']) !!}</b></td>
                        <td>



                                {!! Form::text('host',$set->host,['class' => 'form-control','id'=>'host']) !!}
                                <i> {{Lang::get('message.enter-email-host')}}</i>
                            @error('host')
                            <span class="error-message"> {{$message}}</span>
                            @enderror

                        </td>
                        </div>

                    </tr>
                    <tr>
                        <div class="form-group showWhenSmtpSelected" >
                        <td><b>{!! Form::label('encryption',Lang::get('message.encryption'),['class'=>'required']) !!}</b></td>
                        <td>


                                {!! Form::select('encryption',[''=>'Choose','ssl'=>'SSL','tls'=>'TLS','starttls'=>'STARTTLS'],$set->encryption,['class' => 'form-control','id'=>'encryption']) !!}
                                <i> {{Lang::get('message.select-email-encryption-method')}}</i>
                            @error('encryption')
                            <span class="error-message"> {{$message}}</span>
                            @enderror

                        </td>
                        </div>

                    </tr>



                    <tr>
                        <div class="form-group secret" >
                        <td><b>{!! Form::label('secret','Secret',['class'=>'required']) !!}</b></td>
                        <td>


                                {!! Form::text('secret',$set->secret,['class' => 'form-control','id'=>'secret']) !!}
                            @error('secret')
                            <span class="error-message"> {{$message}}</span>
                            @enderror
                            <div class="input-group-append">
                            </div>
                        </div>

                    </tr>


                    <tr>
                        <div class="form-group showWhenMailGunSelected">
                        <td><b>{!! Form::label('domain','Domain',['class'=>'required']) !!}</b></td>
                        <td>
                             {!! Form::text('domain',$set->domain,['class' => 'form-control','id'=>'domain']) !!}
                            @error('domain')
                            <span class="error-message"> {{$message}}</span>
                            @enderror
                            <div class="input-group-append">
                            </div>
                         </td>
                        </div>
                    </tr>



                    <tr>
                        <div class="form-group showWhenSesSelected">
                        <td><b>{!! Form::label('key','API Key',['class'=>'required']) !!}</b></td>
                        <td>
                             {!! Form::text('key',$set->key,['class' => 'form-control','id'=>'api_key']) !!}
                            @error('key')
                            <span class="error-message"> {{$message}}</span>
                            @enderror
                            <div class="input-group-append">
                            </div>
                         </td>
                        </div>
                    </tr>

                      <tr>
                        <div class="form-group showWhenSesSelected">
                        <td><b>{!! Form::label('region','Region',['class'=>'required']) !!}</b></td>
                        <td>
                             {!! Form::text('region',$set->region,['class' => 'form-control','id'=>'region']) !!}
                            @error('region')
                            <span class="error-message"> {{$message}}</span>
                            @enderror
                            <div class="input-group-append">
                            </div>
                         </td>
                        </div>
                    </tr>



                    


                    <tr>
                        <div class="form-group {{ $errors->has('email') ? 'has-error' : '' }}">
                        <td><b>{!! Form::label('email',Lang::get('message.email'),['class'=>'required']) !!}</b></td>
                        <td>


                                {!! Form::text('email',$set->email,['class' => 'form-control','id'=>'email']) !!}
                                <i> {{Lang::get('message.enter-email')}} ({{Lang::get('message.enter-email-message')}})</i>
                            @error('email')
                            <span class="error-message"> {{$message}}</span>
                            @enderror


                        </td>
                        </div>
                    </tr>
                    
                      <tr>
                        <div class="form-group {{ $errors->has('from_name') ? 'has-error' : '' }}">
                        <td><b>{!! Form::label('from_name',Lang::get('From Name'),['class'=>'required']) !!}</b></td>
                        <td>


                                {!! Form::text('from_name',$set->from_name,['class' => 'form-control','id'=>'from_name']) !!}
                                <i> {{Lang::get('Enter From Name')}} </i>
                            @error('from_name')
                            <span class="error-message"> {{$message}}</span>
                            @enderror

                        </td>
                        </div>
                    </tr>

                    <tr>
                        <div class="form-group {{ $errors->has('password') ? 'has-error' : '' }} showWhenSmtpSelected">
                            <td><b>{!! Form::label('password',Lang::get('message.password'),['class'=>'required']) !!}</b></td>
                        <td>


                                {!! Form::password('password',['class' => 'form-control', 'id'=>'password']) !!}
                                <i> {{Lang::get('message.enter-email-password')}}</i>
                            @error('password')
                            <span class="error-message"> {{$message}}</span>
                            @enderror
                            
                        </td>
                        </div>
                    </tr>
                    <br>
                     <button type="submit" class="form-group btn btn-primary pull-right"  id="emailSetting"><i class="fa fa-save">&nbsp;&nbsp;</i>{!!Lang::get('message.save')!!}</button>
                  </div>
                  </div>

 </div>

    <script>

        $(document).ready(function() {
            function emailOperation(){
                $("#emailSetting").html("<i class='fas fa-circle-notch fa-spin'></i>  Please Wait...");
                $("#emailSetting").attr('disabled', true);
                $.ajax({

                    url: '{{url("settings/email")}}',
                    type: 'patch',
                    data: {
                        "from_name": $('#from_name').val(),
                        "email": $('#email').val(),
                        "password": $('#password').val(),
                        "driver": $('#driver').val(),
                        "port": $('#port').val(),
                        "encryption": $('#encryption').val(),
                        "host": $('#host').val(),
                        "key": $('#api_key').val(),
                        "secret": $('#secret').val(),
                        "region": $('#region').val(),
                        "domain": $('#domain').val(),
                    },
                    success: function (response) {
                        $("#emailSetting").attr('disabled', false);
                        $("#emailSetting").html("<i class='fa fa-save'>&nbsp;&nbsp;</i>Save");
                        $('#alertMessage').show();
                        var result = '<div class="alert alert-success alert-dismissable"><button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button><strong><i class="fa fa-check"></i> Success! </strong>' + response.message + '.</div>';
                        $('#alertMessage').html(result + ".");
                        $("#submit").html("<i class='fa fa-floppy-o'>&nbsp;&nbsp;</i>Save");
                        setInterval(function () {
                            $('#alertMessage').slideUp(3000);
                        }, 1000);
                    }, error: function (response) {
                        $("#emailSetting").attr('disabled', false);
                        var html = '<div class="alert alert-danger"><button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button><strong>Whoops! </strong>Something went wrong<br><br><ul>';
                        $("#emailSetting").html("<i class='fa fa-save'>&nbsp;&nbsp;</i>Save");
                        if (response.status == 422) {
                            for (key in response.responseJSON.errors) {
                                html += '<li>' + response.responseJSON.errors[key][0] + '</li>'
                            }

                        } else {
                            html += '<li>' + response.responseJSON.message + '</li>'
                        }
                        html += '</ul></div>';
                        $('#alertMessage').show();

                        document.getElementById('alertMessage').innerHTML = html;
                        // location.reload();
                    }


                });
            }
            const userRequiredFields = {
                driver:@json(trans('message.emailSettings_details.driver')),
                email:@json(trans('message.emailSettings_details.email')),
                port:@json(trans('message.emailSettings_details.port')),
                host:@json(trans('message.emailSettings_details.host')),
                encryption:@json(trans('message.emailSettings_details.encryption')),
                from_name:@json(trans('message.emailSettings_details.from_name')),
                password:@json(trans('message.emailSettings_details.password')),
                secret:@json(trans('message.emailSettings_details.secret')),
                domain:@json(trans('message.emailSettings_details.domain')),
                api_key:@json(trans('message.emailSettings_details.api_key')),
                region:@json(trans('message.emailSettings_details.region')),

            };

            if ($('#driver').val() === '') {
                $('#emailSetting').on('click', function (e) {
                    const userFields = {
                        email: $('#email'),
                        driver:$('#driver'),
                        from_name:$('#from_name'),
                    };


                    // Clear previous errors
                    Object.values(userFields).forEach(field => {
                        field.removeClass('is-invalid');
                        field.next().next('.error').remove();

                    });

                    let isValid = true;

                    const showError = (field, message) => {
                        field.addClass('is-invalid');
                        field.next().after(`<span class='error invalid-feedback'>${message}</span>`);
                    };

                    // Validate required fields
                    Object.keys(userFields).forEach(field => {
                        if (!userFields[field].val()) {
                            showError(userFields[field], userRequiredFields[field]);
                            isValid = false;
                        }
                    });


                    // If validation fails, prevent form submission
                    if (!isValid) {
                        e.preventDefault();
                    }
                });
                // Function to remove error when input'id' => 'changePasswordForm'ng data
                const removeErrorMessage = (field) => {
                    field.classList.remove('is-invalid');
                    const error = field.nextElementSibling;
                    if (error && error.classList.contains('error')) {
                        error.remove();
                    }
                };

                // Add input event listeners for all fields
                ['email', 'port', 'host', 'encryption', 'from_name','password','secret','region','domain','api_key'].forEach(id => {

                    document.getElementById(id).addEventListener('input', function () {
                        removeErrorMessage(this);

                    });
                });
            }

            $('#driver').on('change', function () {


                const rmFields = {
                    driver:$('#driver'),
                    email: $('#email'),
                    port:$('#port'),
                    host:$('#host'),
                    encryption:$('#encryption'),
                    from_name:$('#from_name'),
                    password:$('#password'),
                    secret:$('#secret'),
                };

                Object.values(rmFields).forEach(field => {
                    field.removeClass('is-invalid');
                    field.next().next('.error').remove();
                });

                 if ($('#driver').val() == 'smtp') {
                     $('#emailSetting').on('click', function (e) {
                         const userFields = {
                             email: $('#email'),
                             port: $('#port'),
                             host: $('#host'),
                             encryption: $('#encryption'),
                             from_name: $('#from_name'),
                             password: $('#password'),
                         };


                         // Clear previous errors
                         Object.values(userFields).forEach(field => {
                             field.removeClass('is-invalid');
                             field.next().next('.error').remove();

                         });

                         let isValid = true;

                         const showError = (field, message) => {
                             field.addClass('is-invalid');
                             field.next().after(`<span class='error invalid-feedback'>${message}</span>`);
                         };

                         // Validate required fields
                         Object.keys(userFields).forEach(field => {
                             if (!userFields[field].val()) {
                                 showError(userFields[field], userRequiredFields[field]);
                                 isValid = false;
                             }
                         });


                         // If validation fails, prevent form submission
                         if (!isValid) {
                             e.preventDefault();
                         }else{
                             emailOperation();
                         }
                     });
                     // Function to remove error when input'id' => 'changePasswordForm'ng data
                     const removeErrorMessage = (field) => {
                         field.classList.remove('is-invalid');
                         const error = field.nextElementSibling;
                         if (error && error.classList.contains('error')) {
                             error.remove();
                         }
                     };

                     // Add input event listeners for all fields
                     ['email', 'port', 'host', 'encryption', 'from_name', 'password', 'secret', 'region', 'domain', 'api_key'].forEach(id => {

                         document.getElementById(id).addEventListener('input', function () {
                             removeErrorMessage(this);

                         });
                     });



                }else if($('#driver').val() == 'mail'){
                    $('#emailSetting').on('click', function (e) {
                        const userFields = {
                            email: $('#email'),
                            from_name:$('#from_name'),
                        };


                        // Clear previous errors
                        Object.values(userFields).forEach(field => {
                            field.removeClass('is-invalid');
                            field.next().next('.error').remove();

                        });

                        let isValid = true;

                        const showError = (field, message) => {
                            field.addClass('is-invalid');
                            field.next().after(`<span class='error invalid-feedback'>${message}</span>`);
                        };

                        // Validate required fields
                        Object.keys(userFields).forEach(field => {
                            if (!userFields[field].val()) {
                                showError(userFields[field], userRequiredFields[field]);
                                isValid = false;
                            }
                        });


                        // If validation fails, prevent form submission
                        if (!isValid) {
                            e.preventDefault();
                        }else{
                            emailOperation();
                        }
                    });
                    // Function to remove error when input'id' => 'changePasswordForm'ng data
                    const removeErrorMessage = (field) => {
                        field.classList.remove('is-invalid');
                        const error = field.nextElementSibling;
                        if (error && error.classList.contains('error')) {
                            error.remove();
                        }
                    };

                    // Add input event listeners for all fields
                    ['email', 'port', 'host', 'encryption', 'from_name','password','secret','region','domain','api_key'].forEach(id => {

                        document.getElementById(id).addEventListener('input', function () {
                            removeErrorMessage(this);

                        });
                    });
                }else if($('#driver').val() == 'mailgun'){
                    $('#emailSetting').on('click', function (e) {
                        const userFields = {
                            email: $('#email'),
                            from_name:$('#from_name'),
                            secret:$('#secret'),
                            domain:$('#domain'),
                        };


                        // Clear previous errors
                        Object.values(userFields).forEach(field => {
                            field.removeClass('is-invalid');
                            field.next().next('.error').remove();

                        });

                        let isValid = true;

                        const showError = (field, message) => {
                            field.addClass('is-invalid');
                            field.next().after(`<span class='error invalid-feedback'>${message}</span>`);
                        };

                        // Validate required fields
                        Object.keys(userFields).forEach(field => {
                            if (!userFields[field].val()) {
                                showError(userFields[field], userRequiredFields[field]);
                                isValid = false;
                            }
                        });


                        // If validation fails, prevent form submission
                        if (!isValid) {
                            e.preventDefault();
                        }else{
                            emailOperation();
                        }
                    });
                    // Function to remove error when input'id' => 'changePasswordForm'ng data
                    const removeErrorMessage = (field) => {
                        field.classList.remove('is-invalid');
                        const error = field.nextElementSibling;
                        if (error && error.classList.contains('error')) {
                            error.remove();
                        }
                    };

                    // Add input event listeners for all fields
                    ['email', 'port', 'host', 'encryption', 'from_name','password','secret','region','domain','api_key'].forEach(id => {

                        document.getElementById(id).addEventListener('input', function () {
                            removeErrorMessage(this);

                        });
                    });
                }else if($('#driver').val() == 'mandrill'){
                    $('#emailSetting').on('click', function (e) {
                        const userFields = {
                            email: $('#email'),
                            from_name:$('#from_name'),
                            secret:$('#secret'),
                        };


                        // Clear previous errors
                        Object.values(userFields).forEach(field => {
                            field.removeClass('is-invalid');
                            field.next().next('.error').remove();

                        });

                        let isValid = true;

                        const showError = (field, message) => {
                            field.addClass('is-invalid');
                            field.next().after(`<span class='error invalid-feedback'>${message}</span>`);
                        };

                        // Validate required fields
                        Object.keys(userFields).forEach(field => {
                            if (!userFields[field].val()) {
                                showError(userFields[field], userRequiredFields[field]);
                                isValid = false;
                            }
                        });


                        // If validation fails, prevent form submission
                        if (!isValid) {
                            e.preventDefault();
                        }else{
                            emailOperation();
                        }
                    });
                    // Function to remove error when input'id' => 'changePasswordForm'ng data
                    const removeErrorMessage = (field) => {
                        field.classList.remove('is-invalid');
                        const error = field.nextElementSibling;
                        if (error && error.classList.contains('error')) {
                            error.remove();
                        }
                    };

                    // Add input event listeners for all fields
                    ['email', 'port', 'host', 'encryption', 'from_name','password','secret','region','domain','api_key'].forEach(id => {

                        document.getElementById(id).addEventListener('input', function () {
                            removeErrorMessage(this);

                        });
                    });
                }else if($('#driver').val() == 'ses'){
                    $('#emailSetting').on('click', function (e) {
                        const userFields = {
                            email: $('#email'),
                            from_name:$('#from_name'),
                            region:$('#region'),
                            api_key:$('#api_key'),
                            secret:$('#secret'),
                        };


                        // Clear previous errors
                        Object.values(userFields).forEach(field => {
                            field.removeClass('is-invalid');
                            field.next().next('.error').remove();

                        });

                        let isValid = true;

                        const showError = (field, message) => {
                            field.addClass('is-invalid');
                            field.next().after(`<span class='error invalid-feedback'>${message}</span>`);
                        };

                        // Validate required fields
                        Object.keys(userFields).forEach(field => {
                            if (!userFields[field].val()) {
                                showError(userFields[field], userRequiredFields[field]);
                                isValid = false;
                            }
                        });


                        // If validation fails, prevent form submission
                        if (!isValid) {
                            e.preventDefault();
                        }else{
                            emailOperation();
                        }
                    });
                    // Function to remove error when input'id' => 'changePasswordForm'ng data
                    const removeErrorMessage = (field) => {
                        field.classList.remove('is-invalid');
                        const error = field.nextElementSibling;
                        if (error && error.classList.contains('error')) {
                            error.remove();
                        }
                    };

                    // Add input event listeners for all fields
                    ['email', 'port', 'host', 'encryption', 'from_name','password','secret','region','domain','api_key'].forEach(id => {

                        document.getElementById(id).addEventListener('input', function () {
                            removeErrorMessage(this);

                        });
                    });
                }else if($('#driver').val() == 'sparkpost'){
                    $('#emailSetting').on('click', function (e) {
                        const userFields = {
                            email: $('#email'),
                            from_name:$('#from_name'),
                            secret:$('#secret'),
                        };


                        // Clear previous errors
                        Object.values(userFields).forEach(field => {
                            field.removeClass('is-invalid');
                            field.next().next('.error').remove();

                        });

                        let isValid = true;

                        const showError = (field, message) => {
                            field.addClass('is-invalid');
                            field.next().after(`<span class='error invalid-feedback'>${message}</span>`);
                        };

                        // Validate required fields
                        Object.keys(userFields).forEach(field => {
                            if (!userFields[field].val()) {
                                showError(userFields[field], userRequiredFields[field]);
                                isValid = false;
                            }
                        });


                        // If validation fails, prevent form submission
                        if (!isValid) {
                            e.preventDefault();
                        }else{
                            emailOperation();
                        }
                    });
                    // Function to remove error when input'id' => 'changePasswordForm'ng data
                    const removeErrorMessage = (field) => {
                        field.classList.remove('is-invalid');
                        const error = field.nextElementSibling;
                        if (error && error.classList.contains('error')) {
                            error.remove();
                        }
                    };

                    // Add input event listeners for all fields
                    ['email', 'port', 'host', 'encryption', 'from_name','password','secret','region','domain','api_key'].forEach(id => {

                        document.getElementById(id).addEventListener('input', function () {
                            removeErrorMessage(this);

                        });
                    });
                }
            });
        });
    </script>
<script>
     $('ul.nav-sidebar a').filter(function() {
        return this.id == 'setting';
    }).addClass('active');

    // for treeview
    $('ul.nav-treeview a').filter(function() {
        return this.id == 'setting';
    }).parentsUntil(".nav-sidebar > .nav-treeview").addClass('menu-open').prev('a').addClass('active');
</script>
    <script>
        $(document).ready(function(){

            if($('#driver').val() == 'smtp') {

                $('.showWhenSmtpSelected').show();
                $('.secret').hide();
                $('.showWhenMailGunSelected').hide();
                $('.showWhenMandrillSelected').hide();
                $('.showWhenSesSelected').hide();
            } else if($('#driver').val() == 'mailgun'){
                $('.showWhenSmtpSelected').hide();
                $('.showWhenSesSelected').hide();
                $('.showWhenMandrillSelected').hide();
                $('.secret').show();
                $('.showWhenMailGunSelected').show();
            } else if(driver == 'mandrill') {
                $('.showWhenSmtpSelected').hide();
                $('.showWhenSesSelected').hide();
                $('.showWhenMailGunSelected').hide();
                $('.secret').show();
            } else if(driver == 'ses') {
                $('.showWhenSmtpSelected').hide();
                $('.showWhenMailGunSelected').hide();
                $('.showWhenSesSelected').show();
                $('.secret').show();
            } else if(driver == 'sparkpost') {
                 $('.showWhenSmtpSelected').hide();
                 $('.showWhenMailGunSelected').hide();
                 $('.showWhenSesSelected').hide();
                 $('.secret').show();
            } else {
                 $('.showWhenSmtpSelected').hide();
                 $('.showWhenMailGunSelected').hide();
                 $('.showWhenSesSelected').hide();
                 $('.secret').hide();
            }
        })

        $('#driver').on('change',function(){

            var driver = $('#driver').val();
            if(driver == 'smtp')
            {

                $('.showWhenSmtpSelected').show();
                $('.secret').hide();
                $('#secret').val('');
                $('.showWhenMailGunSelected').hide();
                $('#domain').val('');
                $('.showWhenMandrillSelected').hide();
                $('.showWhenSesSelected').hide();
                 $('#api_key').val('');
                $('#region').val('');


            } else if(driver == 'mailgun') {
                $('.showWhenSmtpSelected').hide();
                $('#host').val('');
                $('#port').val('');
                $('#encryption').val('');
                $('.secret').show();    
                $('.showWhenMandrillSelected').hide();
                $('.showWhenSesSelected').hide();
                 $('#api_key').val('');
                $('#region').val('');
                $('.showWhenMailGunSelected').show();
            } else if(driver == 'mandrill') {
                $('.showWhenSmtpSelected').hide();
                $('#host').val('');
                $('#port').val('');
                $('#encryption').val('');
                $('.showWhenMailGunSelected').hide();
                $('#domain').val('');
                $('.showWhenSesSelected').hide();
                 $('#api_key').val('');
                $('#region').val('');
                $('.secret').show();
            } else if(driver == 'ses') {
                $('.showWhenSmtpSelected').hide();
                $('#host').val('');
                $('#port').val('');
                $('#encryption').val('');
                $('.showWhenMailGunSelected').hide();
                $('#domain').val('');
                $('.showWhenSesSelected').show();
                $('.secret').show();

            } else if(driver == 'sparkpost') {
                $('.showWhenSmtpSelected').hide();
                $('#host').val('');
                $('#port').val('');
                $('#encryption').val('');
                $('.showWhenMailGunSelected').hide();
                $('#domain').val('');
                $('.showWhenSesSelected').hide();
                $('#api_key').val('');
                $('#region').val('');
                $('.secret').show();    
            } else {
                $('.showWhenSmtpSelected').hide();
                $('#host').val('');
                $('#port').val('');
                $('#encryption').val('');
                $('.showWhenMailGunSelected').hide();
                $('#domain').val('');
                $('.showWhenSesSelected').hide();
                $('#api_key').val('');
                $('#region').val('');
                $('.secret').hide(); 
                $('#secret').val('');
                $('#password').val('');

            }
        })

        $('#emailSettting').on('click',function(){




        })
    </script>
@stop