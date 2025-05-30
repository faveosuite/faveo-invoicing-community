@extends('themes.default1.layouts.master')
@section('title')
    Mobile-Validation-Providers
@stop
@section('content-header')
    <style>
        .col-2, .col-lg-2, .col-lg-4, .col-md-2, .col-md-4,.col-sm-2 {
            width: 0px;
        }
        .switch {
            position: relative;
            display: inline-block;
            width: 60px;
            height: 34px;
        }

        .switch input {display:none;}

        .slider {
            position: absolute;
            cursor: pointer;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background-color: #ccc;
            -webkit-transition: .4s;
            transition: .4s;
        }

        .slider:before {
            position: absolute;
            content: "";
            height: 26px;
            width: 26px;
            left: 4px;
            bottom: 4px;
            background-color: white;
            -webkit-transition: .4s;
            transition: .4s;
        }

        input:checked + .slider {
            background-color: #2196F3;
        }

        input:focus + .slider {
            box-shadow: 0 0 1px #2196F3;
        }

        input:checked + .slider:before {
            -webkit-transform: translateX(26px);
            -ms-transform: translateX(26px);
            transform: translateX(26px);
        }

        /* Rounded sliders */
        .slider.round {
            border-radius: 34px;
        }

        .slider.round:before {
            border-radius: 50%;
        }

        .error-border {
            border-color: red;
        }

        /* Adjust alignment if needed */
        .switch.toggle_event_editing.emailValidationStatus {
            display: inline-flex;
            align-items: center;
            margin-top: 10px; /* or tweak this */
            margin-left:20px;
        }

    </style>
    <div class="col-sm-6 md-6">
        <h1>Email Verification Providers</h1>
    </div>
    <div class="col-sm-6 md-6">
        <ol class="breadcrumb float-sm-right">
            <li class="breadcrumb-item"><a href="{{url('/')}}"><i class="fa fa-dashboard"></i> Home</a></li>
            <li class="breadcrumb-item"><a href="{{url('settings')}}"> Settings</a></li>
            <li class="breadcrumb-item active">Mobile Validation Providers</li>
        </ol>
    </div><!-- /.col -->
@stop


@section('content')
    <div class="card card-secondary card-outline" >
        <div class="card-body table-responsive">
            <div class="col-md-12">
                <div id="alertMessage12"></div>
                <div id="error"></div>
                <div class="row">
                    <div class="col-md-4 form-group d-flex align-items-center">
                        <label class="ETitle mb-0 me-5" style="min-width: 150px;">
                            {{ __('message.email_validation_heading') }}
                        </label>
                        <div class="d-flex align-items-center">
                            {!! $statusDisplay !!}
                        </div>
                    </div>
                </div>
                <div class ="row">
                    <div class="col-md-4 form-group" id="emailToDisp" style="display:none">
                        {!! html()->label(Lang::get('message.validation-provider'), 'user')->class('required') !!}
                        <select name="manager" value= {{ $emailProvider }} id="provider" class="form-control {{$errors->has('manager') ? ' is-invalid' : ''}}">
                            <option value="">Choose</option>
                            <option value="reoon">{{Lang::get('message.reoon')}}</option>
                        </select>
                        <div class="input-group-append"></div>
                    </div>
                </div>

                <div class ="row">
                    <div class="col-md-4 form-group" id="emailToRender">
                    </div>
                </div>

            </div>
        </div>
    </div>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <link rel="stylesheet" href="https://cdn.datatables.net/1.10.22/css/jquery.dataTables.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/buttons/1.0.3/css/buttons.dataTables.min.css">
    <script src="https://cdn.datatables.net/buttons/1.0.3/js/dataTables.buttons.min.js"></script>
    <script src="https://cdn.datatables.net/1.10.22/js/jquery.dataTables.min.js"></script>
    <script>
        $(document).ready(function(){
            if ($('#email_validation_status').prop("checked")) {
                document.getElementById('emailToDisp').style.display='block';
            }
        });


        $('#provider').on('change',function(){
            var value=$(this).val();
            if(value !== '') {
                $.ajax({
                    url: "{{url('emailData')}}",
                    type: 'post',
                    data: {
                        'value': value,
                    },
                    success: function (response) {
                        $('#emailToRender').html(response['data']);
                    }
                })
            }else{
                $('#emailToRender').html('');
            }
        });

        $(document).on('change', '.emailValidationStatus input[type="checkbox"]', function() {
            if ($('#email_validation_status').prop("checked")) {
                var checkboxvalue = 1;
            }
            else{
                var checkboxvalue = 0;
            }
            $.ajax({
                url : '{{url("licenseStatus")}}',
                type : 'post',
                data: {
                    "email_validation_status": checkboxvalue,
                },
                success: function (response) {
                    setTimeout(function() {
                        location.reload();
                    }, 3000);
                    $('#alertMessage12').show();
                    var result =  '<div class="alert alert-success alert-dismissable"><button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button><strong><i class="fa fa-check"></i> Success! </strong>'+response.message+'.</div>';
                    $('#alertMessage12').html(result);
                    $("#submit").html("<i class='fa fa-save'>&nbsp;&nbsp;</i>Save");
                    setInterval(function(){
                        $('#alertMessage12').slideUp(3000);
                    }, 1000);
                },
            });

        });


        $(document).on('click', '#submitEmail', function (e) {
            const userRequiredFields = {
                manager:@json(trans('message.system_manager.account_manager')),
                replace_with:@json(trans('message.system_manager.replacement')),
            }

            const userFields = {
                manager: $('#emailApikey'),
                replace_with: $('#emailMode'),
            };

            // Clear previous errors
            Object.values(userFields).forEach(field => {
                field.removeClass('is-invalid');
                field.next().next('.error').remove();

            });

            let isValid = true;

            // Validate required fields
            Object.keys(userFields).forEach(field => {
                const el = userFields[field];
                const value = el.val();
                if (!value || (typeof value === 'object' && value.length === 0)) {
                    el.addClass('is-invalid');
                    el.after(`<span class='error invalid-feedback'>${userRequiredFields[field]}</span>`);
                    isValid = false;
                }
            });


            // If validation fails, prevent form submission
            if (!isValid) {
                e.preventDefault();
            }else{
                let apikey=$('#emailApikey');
                let mode=$('#emailMode');
                let provider=$('#provider');
                $.ajax({
                    url:'{{url('email-settings-save')}}',
                    type:'post',
                    data:{'apikey':apikey.val(),'mode':mode.val(),'provider':provider.val()},
                    success:function(response){
                        setTimeout(function() {
                            location.reload();
                        }, 3000);
                        $('#alertMessage12').show();
                        var result =  '<div class="alert alert-success alert-dismissable"><button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button><strong><i class="fa fa-check"></i> Success! </strong>'+response.message+'</div>';
                        $('#alertMessage12').html(result);
                        setInterval(function(){
                            $('#alertMessage12').slideUp(3000);
                        }, 1000);
                    },
                    error:function(response){
                        console.log(response)
                        $('#alertMessage12').show();
                        var result =  '<div class="alert alert-danger alert-dismissable"><button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button><strong><i class="fa fa-ban"></i> Error! </strong>'+response.responseJSON.message+'</div>';
                        $('#alertMessage12').html(result);
                        setInterval(function(){
                            $('#alertMessage12').slideUp(3000);
                        }, 5000);
                    },
                })
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

        document.addEventListener('input', function (e) {
            if (['emailApikey', 'emailMode'].includes(e.target.id)) {
                removeErrorMessage(e.target);
            }
        });
    </script>
@stop