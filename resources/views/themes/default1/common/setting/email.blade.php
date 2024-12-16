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



                                {!! html()->select('driver', [
                                    '' => 'Choose',
                                    'smtp' => 'SMTP',
                                    'mail' => 'Php mail',
                                    'mailgun' => 'Mailgun',
                                    'mandrill' => 'Mandrill',
                                    'ses' => 'SES',
                                    'sparkpost' => 'Sparkpost'
                                ], $set->driver)
                                ->class('form-control')
                                ->id('driver') !!}

                                <p><i> {{Lang::get('message.select-email-driver')}}</i> </p>



                        </td>
                        </div>
                    </tr>
                    <tr>
                        <div class="form-group showWhenSmtpSelected">
                            <td><b>{!! html()->label(__('message.port'))->class('required')->for('port') !!}</b></td>
                            <td>



                                {!! html()->text('port', $set->port)
    ->class('form-control')
    ->id('port') !!}
                                <p><i> {{Lang::get('message.enter-email-port')}}</i> </p>


                        </td>
                        </div>
                    </tr>
                    <tr>
                        <div class="form-group showWhenSmtpSelected">
                            <td><b>{!! html()->label(__('message.host'))->class('required')->for('host') !!}</b></td>
                            <td>



                                {!! html()->text('host', $set->host)
    ->class('form-control')
    ->id('host') !!}
                                <p><i> {{Lang::get('message.enter-email-host')}}</i> </p>


                        </td>
                        </div>

                    </tr>
                    <tr>
                        <div class="form-group showWhenSmtpSelected" >
                            <td><b>{!! html()->label(__('message.encryption'))->class('required')->for('encryption') !!}</b></td>
                            <td>


                                {!! html()->select('encryption', [
    '' => 'Choose',
    'ssl' => 'SSL',
    'tls' => 'TLS',
    'starttls' => 'STARTTLS'
], $set->encryption)
->class('form-control')
->id('encryption') !!}

                                <p><i> {{Lang::get('message.select-email-encryption-method')}}</i> </p>


                        </td>
                        </div>

                    </tr>



                      <tr>
                          <td><b>{!! html()->label('Secret')->class('required')->for('secret') !!}</b></td>
                          <td>
                              <div class="form-group secret">
                                  {!! html()->text('secret', $set->secret)
                                      ->class('form-control')
                                      ->id('secret') !!}
                              </div>
                          </td>
                      </tr>


                      <tr class="showWhenMailGunSelected">
                          <td><b>{!! html()->label('Domain')->class('required')->for('domain') !!}</b></td>
                          <td>
                              {!! html()->text('domain', $set->domain)
                                  ->class('form-control')
                                  ->id('domain') !!}
                          </td>
                      </tr>



                      <tr>
                        <div class="form-group showWhenSesSelected">
                            <td><b>{!! html()->label('API Key')->class('required')->for('api_key') !!}</b></td>
                        <td>
                            {!! html()->text('key', $set->key)
           ->class('form-control')
           ->id('api_key') !!}
                         </td>
                        </div>
                    </tr>

                      <tr>
                        <div class="form-group showWhenSesSelected">
                            <td><b>{!! html()->label('Region')->class('required')->for('region') !!}</b></td>
                        <td>
                            {!! html()->text('region', $set->region)
           ->class('form-control')
           ->id('region') !!} !!}
                         </td>
                        </div>
                    </tr>



                    


                    <tr>
                        <div class="form-group {{ $errors->has('email') ? 'has-error' : '' }}">
                            <td><b>{!! html()->label(__('message.email'))->class('required')->for('email') !!}</b></td>
                            <td>


                                {!! html()->text('email', $set->email)
     ->class('form-control')
     ->id('email') !!}
                                <p><i> {{Lang::get('message.enter-email')}} ({{Lang::get('message.enter-email-message')}})</i> </p>


                        </td>
                        </div>
                    </tr>
                    
                      <tr>
                        <div class="form-group {{ $errors->has('from_name') ? 'has-error' : '' }}">
                            <td><b>{!! html()->label(__('From Name'))->class('required')->for('from_name') !!}</b></td>
                            <td>


                                {!! html()->text('from_name', $set->from_name)
    ->class('form-control')
    ->id('from_name') !!}
                                <p><i> {{Lang::get('Enter From Name')}} </i> </p>


                        </td>
                        </div>
                    </tr>

                    <tr>
                        <div class="form-group {{ $errors->has('password') ? 'has-error' : '' }} showWhenSmtpSelected">
                            <td><b>{!! html()->label(__('message.password'))->class('required')->for('password') !!}</b></td>
                            <td>


                                {!! html()->password('password')
    ->class('form-control')
    ->id('password') !!}
                                <p><i> {{Lang::get('message.enter-email-password')}}</i> </p>

                            
                        </td>
                        </div>
                    </tr>
                    <br>
                     <button type="submit" class="form-group btn btn-primary pull-right"  id="emailSetting"><i class="fa fa-save">&nbsp;&nbsp;</i>{!!Lang::get('message.save')!!}</button>
                     
                  </div>
</div>
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

        $('#emailSetting').on('click',function(){



            $("#emailSetting").html("<i class='fas fa-circle-notch fa-spin'></i>  Please Wait...");
            $("#emailSetting").attr('disabled',true);
            $.ajax({

                url : '{{url("settings/email")}}',
                type : 'patch',
                data: {
                    "from_name" : $('#from_name').val(),
                     "email" : $('#email').val(),
                     "password" : $('#password').val(),
                     "driver" : $('#driver').val(),
                     "port"  : $('#port').val(),
                     "encryption" : $('#encryption'). val(),
                     "host" : $('#host').val(),
                     "key" : $('#api_key').val(),
                     "secret": $('#secret').val(),
                     "region": $('#region').val(),
                     "domain": $('#domain').val(),
                },
                success: function (response) {
                    $("#emailSetting").attr('disabled',false);
                    $("#emailSetting").html("<i class='fa fa-save'>&nbsp;&nbsp;</i>Save");
                    $('#alertMessage').show();
                    var result =  '<div class="alert alert-success alert-dismissable"><button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button><strong><i class="fa fa-check"></i> Success! </strong>'+response.message+'.</div>';
                    $('#alertMessage').html(result+ ".");
                    $("#submit").html("<i class='fa fa-floppy-o'>&nbsp;&nbsp;</i>Save");
                    setInterval(function(){
                        $('#alertMessage').slideUp(3000);
                    }, 1000);
                },error: function(response) {
                    $("#emailSetting").attr('disabled',false);
                    var html = '<div class="alert alert-danger"><button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button><strong>Whoops! </strong>Something went wrong<br><br><ul>';
                    $("#emailSetting").html("<i class='fa fa-save'>&nbsp;&nbsp;</i>Save");
                    if(response.status == 422) {
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
        })
    </script>
@stop