@extends('themes.default1.layouts.master')
@section('title')
    Contact-Options
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
        .scrollit {
            overflow:scroll;
            height:600px;
        }
        .error-border {
            border-color: red;
        }

        .custom-tooltip {
            font-size: 1.2rem; /* Adjust size to match h3 */
            margin-top: 3px;  /* Align with heading */
        }
        .tooltip-inner {
            background-color: black !important; /* Dark background */
            color: white !important;         /* Yellow text */
            font-size: 14px !important;        /* Change font size */
            font-weight: bold !important;      /* Bold text */
            font-family: Arial, sans-serif !important; /* Custom font */
            text-align: center !important;     /* Center align */
            padding: 10px 15px !important;     /* Adjust padding */
            border-radius: 5px !important;     /* Rounded corners */
            max-width: 250px !important;       /* Set max width */
            white-space: normal !important;    /* Allow text wrapping */
        }
    </style>
    <div class="col-sm-6 md-6">
        <h1>Contact Options</h1>
    </div>
    <div class="col-sm-6 md-6">
        <ol class="breadcrumb float-sm-right">
            <li class="breadcrumb-item"><a href="{{url('/')}}"><i class="fa fa-dashboard"></i> Home</a></li>
            <li class="breadcrumb-item"><a href="{{url('settings')}}"> Settings</a></li>
            <li class="breadcrumb-item active">Contact Options</li>
        </ol>
    </div><!-- /.col -->
@stop


@section('content')


    <div class="card card-secondary card-outline" >

        <!-- /.box-header -->
        <div class="card-body">
            <div id="alertMessage"></div>

            <div class="d-flex align-items-center">
                <h4 class="mb-0 me-2">Allow User to login only if they have </h4>
                <i class="fas fa-question-circle  custom-tooltip" data-toggle="tooltip" data-placement="top" style="margin-left: 7px" title="{{Lang::get('message.verify_email_tooltip')}}"></i>
            </div>
            <div class="form-check mt-2">
                <input type="checkbox" class="form-check-input checkbox5" id="email" value="{{$emailStatus}}" name="email_settings">
                <label class="form-check-label" for="email_set">Verified Email</label>
            </div>
    </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <link rel="stylesheet" href="https://cdn.datatables.net/1.10.22/css/jquery.dataTables.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/buttons/1.0.3/css/buttons.dataTables.min.css">
    <script src="https://cdn.datatables.net/buttons/1.0.3/js/dataTables.buttons.min.js"></script>

    <script src="https://cdn.datatables.net/1.10.22/js/jquery.dataTables.min.js"></script>


    <script>
        $(document).ready(function () {
            $('[data-toggle="tooltip"]').tooltip();

            // Force tooltip to update styles after rendering
            $('[data-toggle="tooltip"]').on('shown.bs.tooltip', function () {
                $('.tooltip-inner').css({
                    'background-color': '#222',
                    'color': '#ffcc00',
                    'font-size': '14px',
                    'font-weight': 'bold',
                    'border-radius': '8px',
                    'padding': '10px 15px'
                });

                $('.tooltip-arrow').css({
                    'border-top-color': '#222',
                    'border-bottom-color': '#222',
                    'border-left-color': '#222',
                    'border-right-color': '#222'
                });
            });
        });

        $(document).ready(function(){
            var status = $('.checkbox5').val();
            if(status ==1) {
                $('#email').prop('checked', true);
            }
        });
        document.getElementById("email").addEventListener("change", function() {
            if (this.checked) {
                var emailstatus = 1;
            } else {
                var emailstatus = 0;
            }

            $.ajax ({
                url: '{{url("updateemailDetails")}}',
                type : 'post',
                data: {
                    "status": emailstatus,
                },
                success: function (data) {
                    setTimeout(function() {
                        location.reload();
                    }, 3000);
                    $('#alertMessage').show();
                    var result =  '<div class="alert alert-success alert-dismissable"><button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button><strong><i class="fa fa-check"></i> Success! </strong>'+data.update+'.</div>';
                    $('#alertMessage').html(result+ ".");
                    $("#submit4").html("<i class='fa fa-save'>&nbsp;&nbsp;</i>Save");
                    setInterval(function(){
                        $('#alertMessage').slideUp(3000);
                    }, 1000);
                },
            });
        });
        $("#submit4").on('click',function (){ //When Submit button is checked
            if ($('#email').prop('checked')) {//if button is on
                var emailstatus = 1;
            } else {
                var emailstatus = 0;
            }
            $("#submit4").html("<i class='fas fa-circle-notch fa-spin'></i>  Please Wait...");
            $.ajax ({
                url: '{{url("updateemailDetails")}}',
                type : 'post',
                data: {
                    "status": emailstatus,
                },
                success: function (data) {
                    setTimeout(function() {
                        location.reload();
                    }, 3000);
                    $('#alertMessage').show();
                    var result =  '<div class="alert alert-success alert-dismissable"><button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button><strong><i class="fa fa-check"></i> Success! </strong>'+data.update+'.</div>';
                    $('#alertMessage').html(result+ ".");
                    $("#submit4").html("<i class='fa fa-save'>&nbsp;&nbsp;</i>Save");
                    setInterval(function(){
                        $('#alertMessage').slideUp(3000);
                    }, 1000);
                },
            });
        });
    </script>
 @stop