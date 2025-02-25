@extends('themes.default1.layouts.master')
@section('title')
Configure Queue
@stop
@section('content-header')
    <div class="col-sm-6">
        <h1>{{$queue->name}}</h1>
    </div>
    <div class="col-sm-6">
        <ol class="breadcrumb float-sm-right">
            <li class="breadcrumb-item"><a href="{{url('/')}}"><i class="fa fa-dashboard"></i> Home</a></li>
            <li class="breadcrumb-item"><a href="{{url('settings')}}"><i class="fa fa-dashboard"></i> Settings</a></li>
            <li class="breadcrumb-item"><a href="{{url('queue')}}"><i class="fa fa-dashboard"></i> Queues</a></li>
            <li class="breadcrumb-item active">Configure Queue</li>
        </ol>
    </div><!-- /.col -->
@stop
@section('content')
 <div id="alertMessage"></div>

<div class="card card-secondary card-outline">

       
            <div class="card-body">

                 {!! Form::open(['url'=>'queue/'.$queue->id,'method'=>'post','id'=>'form']) !!}
                    <div id="response">

                    </div>
                      <button type="submit" class="form-group btn btn-primary pull-right"  id="submitButton"><i class="fa fa-save">&nbsp;&nbsp;</i>{!!Lang::get('message.save')!!}</button>
                    <!-- {!! Form::submit('save',['class'=>'btn btn-primary', 'id'=>'submitButton', 'disabled'=>true]) !!} -->
                    {!! Form::close() !!}
             
                 
</div>
    <script>

    $(document).ready(function () {
        var queueid = '{{$queue->id}}';
        $.ajax({
            url: "{{url('form/queue')}}",
            dataType: "html",
            data: {'queueid': queueid},
            beforeSend: function() {
                $('.loader').css('display','block');
            },
            success: function (response) {
                console.log(response);
                $('.loader').css('display','none');
                $("#response").html(response);
                $("#submitButton").attr('disabled', false);

            },
            error: function ($xhr) {
                $data = JSON.parse($xhr.responseText);
                $("#response").html($data.message);
                $("#submitButton").attr('disabled', true);
            }
        });


        var queueName='{{$queue->name}}';
        console.log(queueName);
        const userRequiredFields = {
            driver:@json(trans('message.coupon_details.add_code')),
            host:@json(trans('message.coupon_details.add_type')),
            queue:@json(trans('message.coupon_details.add_uses')),
            applied:@json(trans('message.coupon_details.add_applied')),
            expiry:@json(trans('message.coupon_details.add_expiry')),
            start:@json(trans('message.coupon_details.add_start')),
            value:@json(trans('message.coupon_details.add_value')),

        };
        if(queueName === 'Beanstalkd') {
            $('#form').on('submit', function (e) {
                const userFields = {
                    driver: $('#driver'),
                    host: $('#host'),
                    queue: $('#queue'),
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
        }else if(queueName ==='SQS'){$('#form').on('submit', function (e) {
            const userFields = {
                driver: $('#driver'),
                region: $('#region'),
                key: $('#key'),
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
            }
        });

        }else if(queueName==='Iron'){
            $('#form').on('submit', function (e) {
                const userFields = {
                    driver: $('#driver'),
                    host: $('#host'),
                    queue: $('#queue'),
                    token:$('#token'),
                    project:$('#project'),
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
        }else{
            $('#form').on('submit', function (e) {
                const userFields = {
                    driver: $('#driver'),
                    queue: $('#queue'),
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
        }
        // Function to remove error when input'id' => 'changePasswordForm'ng data
        const removeErrorMessage = (field) => {
            field.classList.remove('is-invalid');
            const error = field.nextElementSibling;
            if (error && error.classList.contains('error')) {
                error.remove();
            }
        };

        // Add input event listeners for all fields
        ['queue','host','driver','expiry','start','value','type'].forEach(id => {

            document.getElementById(id).addEventListener('input', function () {
                removeErrorMessage(this);

            });
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
@stop