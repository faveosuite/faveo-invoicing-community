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
                            <td><b>{!! html()->label(__('message.driver'))->class('required')->for('driver') !!}</b></td>
                            <td>



                                {!! html()->select('driver', ['' => 'Choose','smtp' => 'SMTP','mail' => 'Php mail','mailgun' => 'Mailgun','mandrill' => 'Mandrill','ses' => 'SES','sparkpost' => 'Sparkpost'], $set->driver)->class('form-control'. ($errors->has('driver') ? ' is-invalid' : ''))->id('driver') !!}
                                <i> {{Lang::get('message.select-email-driver')}}</i>
                            @error('driver')
                            <span class="error-message"> {{$message}}</span>
                            @enderror


                        </td>
                        </div>
                    </tr>
                    <tr>
                        <div class="form-group showWhenSmtpSelected">
                            <td><b>{!! html()->label(__('message.port'))->class('required')->for('port') !!}</b></td>
                            <td>



                                {!! html()->text('port', $set->port)->class('form-control'. ($errors->has('port') ? ' is-invalid' : ''))->id('port') !!}
                                <i> {{Lang::get('message.enter-email-port')}}</i>
                            @error('port')
                            <span class="error-message"> {{$message}}</span>
                            @enderror

                        </td>
                        </div>
                    </tr>
                    <tr>
                        <div class="form-group showWhenSmtpSelected">
                            <td><b>{!! html()->label(__('message.host'))->class('required')->for('host') !!}</b></td>
                            <td>



                                {!! html()->text('host', $set->host)->class('form-control'. ($errors->has('host') ? ' is-invalid' : ''))->id('host') !!}
                                <i> {{Lang::get('message.enter-email-host')}}</i>
                            @error('host')
                            <span class="error-message"> {{$message}}</span>
                            @enderror

                        </td>
                        </div>

                    </tr>
                    <tr>
                        <div class="form-group showWhenSmtpSelected" >
                            <td><b>{!! html()->label(__('message.encryption'))->class('required')->for('encryption') !!}</b></td>
                            <td>


                                {!! html()->select('encryption', ['' => 'Choose','ssl' => 'SSL','tls' => 'TLS','starttls' => 'STARTTLS'], $set->encryption)->class('form-control'. ($errors->has('encryption') ? ' is-invalid' : ''))->id('encryption') !!}
                                <i> {{Lang::get('message.select-email-encryption-method')}}</i>
                            @error('encryption')
                            <span class="error-message"> {{$message}}</span>
                            @enderror

                        </td>
                        </div>

                    </tr>



                      <tr>
                          <div class="form-group secret" >
                          <td><b>{!! html()->label('Secret')->class('required')->for('secret') !!}</b></td>
                          <td>
                              {!! html()->text('secret', $set->secret)->class('form-control'. ($errors->has('secret') ? ' is-invalid' : ''))->id('secret') !!}
                              @error('secret')
                              <span class="error-message"> {{$message}}</span>
                              @enderror
                              <div class="input-group-append">
                              </div>
                          </div>
                      </tr>


                    <tr>
                        <div class="form-group showWhenMailGunSelected">
                            <td><b>{!! html()->label('Domain')->class('required')->for('domain') !!}</b></td>
                        <td>
                            {!! html()->text('domain', $set->domain)->class('form-control'. ($errors->has('domain') ? ' is-invalid' : ''))->id('domain') !!}
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
                            <td><b>{!! html()->label('API Key')->class('required')->for('api_key') !!}</b></td>
                        <td>
                            {!! html()->text('key', $set->key)->class('form-control'. ($errors->has('key') ? ' is-invalid' : ''))->id('api_key') !!}
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
                            <td><b>{!! html()->label('Region')->class('required')->for('region') !!}</b></td>
                        <td>
                            {!! html()->text('region', $set->region)->class('form-control'. ($errors->has('region') ? ' is-invalid' : ''))->id('region') !!}
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
                            <td><b>{!! html()->label(__('message.email'))->class('required')->for('email') !!}</b></td>
                            <td>


                                {!! html()->text('email', $set->email)->class('form-control'. ($errors->has('email') ? ' is-invalid' : ''))->id('email') !!}
                                <i> {{Lang::get('message.enter-email')}} ({{Lang::get('message.enter-email-message')}})</i>
                            @error('email')
                            <span class="error-message"> {{$message}}</span>
                            @enderror


                        </td>
                        </div>
                    </tr>

                      <tr>
                        <div class="form-group {{ $errors->has('from_name') ? 'has-error' : '' }}">
                            <td><b>{!! html()->label(__('From Name'))->class('required')->for('from_name') !!}</b></td>
                            <td>


                                {!! html()->text('from_name', $set->from_name)->class('form-control'. ($errors->has('from_name') ? ' is-invalid' : ''))->id('from_name') !!}
                                <i> {{Lang::get('Enter From Name')}} </i>
                            @error('from_name')
                            <span class="error-message"> {{$message}}</span>
                            @enderror

                        </td>
                        </div>
                    </tr>

                    <tr>
                        <div class="form-group {{ $errors->has('password') ? 'has-error' : '' }} showWhenSmtpSelected">
                            <td><b>{!! html()->label(__('message.password'))->class('required')->for('password') !!}</b></td>
                            <td>


                                {!! html()->password('password')->class('form-control'. ($errors->has('password') ? ' is-invalid' : ''))->id('password') !!}
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
                        from_name: $('#from_name').val(),
                        email: $('#email').val(),
                        password: $('#password').val(),
                        driver: $('#driver').val(),
                        port: $('#port').val(),
                        encryption: $('#encryption').val(),
                        host: $('#host').val(),
                        key: $('#api_key').val(),
                        secret: $('#secret').val(),
                        region: $('#region').val(),
                        domain: $('#domain').val(),
                    },
                    success: function (response) {
                        $("#emailSetting").attr('disabled', false);
                        $("#emailSetting").html("<i class='fa fa-save'>&nbsp;&nbsp;</i>Save");
                        const result = `<div class="alert alert-success alert-dismissable">
                    <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
                    <strong><i class="fa fa-check"></i> Success! </strong> ${response.message}.
                </div>`;
                        $('#alertMessage').html(result).show();
                        setTimeout(function () {
                            $('#alertMessage').slideUp(3000);
                        }, 1000);
                    },
                    error: function (response) {
                        $("#emailSetting").attr('disabled', false);
                        $("#emailSetting").html("<i class='fa fa-save'>&nbsp;&nbsp;</i>Save");
                        let html = `<div class="alert alert-danger">
                    <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
                    <strong>Whoops! </strong>Something went wrong<br><br><ul>`;
                        if (response.status === 422) {
                            $.each(response.responseJSON.errors, function (key, errors) {
                                html += `<li>${errors[0]}</li>`;
                            });
                        } else {
                            html += `<li>${response.responseJSON.message}</li>`;
                        }
                        html += '</ul></div>';
                        $('#alertMessage').html(html).show();
                        setTimeout(function () {
                            $('#alertMessage').slideUp(1000);
                        }, 10000);
                    }
                });
            }
            const userRequiredFields = {
                driver: @json(trans('message.emailSettings_details.driver')),
                email: @json(trans('message.emailSettings_details.email')),
                port: @json(trans('message.emailSettings_details.port')),
                host: @json(trans('message.emailSettings_details.host')),
                encryption: @json(trans('message.emailSettings_details.encryption')),
                from_name: @json(trans('message.emailSettings_details.from_name')),
                password: @json(trans('message.emailSettings_details.password')),
                secret: @json(trans('message.emailSettings_details.secret')),
                domain: @json(trans('message.emailSettings_details.domain')),
                api_key: @json(trans('message.emailSettings_details.api_key')),
                region: @json(trans('message.emailSettings_details.region')),
            };

            // Map driver values to their required field IDs
            const driverRequiredFields = {
                '': ['email', 'driver', 'from_name'], // Default fields if no driver is selected
                'smtp': ['email', 'port', 'host', 'encryption', 'from_name', 'password'],
                'mail': ['email', 'from_name'],
                'mailgun': ['email', 'from_name', 'secret', 'domain'],
                'mandrill': ['email', 'from_name', 'secret'],
                'ses': ['email', 'from_name', 'region', 'api_key', 'secret'],
                'sparkpost': ['email', 'from_name', 'secret']
            };

            // Validate required fields based on an array of field IDs
            function validateFields(requiredFieldIds) {
                let isValid = true;
                requiredFieldIds.forEach(function (id) {
                    const field = $('#' + id);
                    // Clear any previous error messages
                    field.removeClass('is-invalid');
                    field.nextAll('.error').remove();
                    if (!field.val()) {
                        isValid = false;
                        field.addClass('is-invalid');
                        // Insert error message after the field's immediate next sibling
                        field.next().after(`<span class='error invalid-feedback'>${userRequiredFields[id]}</span>`);
                    }
                });
                return isValid;
            }

            // Add input event listeners to remove error messages on change
            ['email', 'port', 'host', 'encryption', 'from_name', 'password', 'secret', 'region', 'domain', 'api_key'].forEach(function (id) {
                $('#' + id).on('input', function () {
                    $(this).removeClass('is-invalid');
                    $(this).nextAll('.error').remove();
                });
            });

            // Clear error messages on driver change
            $('#driver').on('change', function () {
                ['driver', 'email', 'port', 'host', 'encryption', 'from_name', 'password', 'secret', 'domain', 'api_key', 'region'].forEach(function (id) {
                    $('#' + id).removeClass('is-invalid');
                    $('#' + id).nextAll('.error').remove();
                });
            });

            // Unified click handler for the "Save" button
            $('#emailSetting').on('click', function (e) {
                e.preventDefault();
                const currentDriver = $('#driver').val();
                const requiredFields = driverRequiredFields.hasOwnProperty(currentDriver)
                    ? driverRequiredFields[currentDriver]
                    : [];
                if (validateFields(requiredFields)) {
                    emailOperation();
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