@extends('themes.default1.layouts.master')
@section('title')
    Payment Gateway Integrations
@stop
@section('content-header')
    <div class="col-sm-6">
        <h1>Payment Gateway Integrations</h1>
    </div>
    <div class="col-sm-6">
        <ol class="breadcrumb float-sm-right">
            <li class="breadcrumb-item"><a href="{{url('/')}}"><i class="fa fa-dashboard"></i> Home</a></li>
            <li class="breadcrumb-item"><a href="{{url('settings')}}"><i class="fa fa-dashboard"></i> Settings</a></li>
            <li class="breadcrumb-item active">Payment Gateway Integrations</li>
        </ol>
    </div><!-- /.col -->
@stop
@section('content')

<div class="row">

    <div class="col-md-12">
        <div class="card card-secondary card-outline">



    <div class="card-body table-responsive">
        <div id="alertMessage"></div>
        


        <div class="row">
            <div class="col-md-12">
                 <table id="plugin" class="table display" cellspacing="0" width="100%" styleClass="borderless">

                    <thead><tr>
                         <th>Name</th>
                          <th>Description</th>
                          <th>Author</th>
                          <th>Website</th>
                          <th>Version</th>
                        <th>Status</th>
                          <th>Action</th>
                        </tr></thead>
                       
                        @foreach($pay as $key => $item)
                        <tbody>
                                <td>{{$item['name']}}</td>
                                <td>{{$item['description']}}</td>
                                 <td>{{$item['author']}}</td>
                                 
                                 <td><a href={{$item['website']}}>{{$item['website']}}</a></td>
                                  <td>{{$item['version']}}</td>


                              @foreach($status as $s)
                              @if($item['name'] == $s->name)

                                       <td>
                                            <label class="switch toggle_event_editing">
                                                <input type="checkbox" value="{{$s->status}}" name="{{$item['name']}}"
                                                       class="{{$item['name']}}" id="{{$item['name']}}">
                                                <span class="slider round"></span>
                                            </label>
                                       </td>


                                    @if($item['name'] == $s->name && $s->status)
                                     <td>
                                       <a href= "{{url($item['settings'])}}" class="btn btn-secondary btn-sm btn-xs"><i class="nav-icon fa fa-fw fa-edit" style="color:white;"></i></a>
                                    </td>
                                     @endif

                                     @endif
                              @endforeach
                                 
                                     
                                

                                    
                           
                           
                              
                            </tr>
                        </tbody>
                        
                        @endforeach
                        
                      
                     </table>
              
            </div>
        </div>
    </div>
</div>

      
        <!-- /.box -->

    </div>


</div>

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


</style>
<link rel="stylesheet" href="https://cdn.datatables.net/1.10.21/css/jquery.dataTables.min.css">
<script src="https://cdn.datatables.net/1.10.21/js/jquery.dataTables.min.js"></script>

<script>

    $(document).ready(function() {

        var status1 = $('.Razorpay').val();
        if (status1 == 1) {
            $('#Razorpay').prop('checked', true);
        }

        var status2 = $('.Stripe').val();
        if (status2 == 1) {
            $('#Stripe').prop('checked', true);
        }
    });

    $('#Razorpay').on('change',function(){
        var checkstatus=0;
        if ($('#Razorpay').prop("checked")) {
            checkstatus=1;
        }else{
           checkstatus=0;
        }
        $.ajax({
            url : '{{url("updatePaymentStatus")}}',
            type : 'post',
            data: {
                "status": checkstatus,
                "name":"Razorpay",
            },
            success: function (data) {
                setTimeout(function() {
                    location.reload();
                }, 3000);
                $('#alertMessage').show();
                var result =  '<div class="alert alert-success alert-dismissable"><button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button><strong><i class="fa fa-check"></i> Success! </strong>'+data.message+'.</div>';
                $('#alertMessage').html(result);
                setInterval(function(){
                    $('#alertMessage').slideUp(3000);
                }, 1000);
            },

        });

    });


    $('#Stripe').on('change',function(){
        var checkstatus=0;
        if ($('#Stripe').prop("checked")) {
            checkstatus=1;
        }else{
            checkstatus=0;
        }
        $.ajax({
            url : '{{url("updatePaymentStatus")}}',
            type : 'post',
            data: {
                "status": checkstatus,
                "name":"Stripe",
            },
            success: function (data) {
                setTimeout(function() {
                    location.reload();
                }, 3000);
                $('#alertMessage').show();
                var result =  '<div class="alert alert-success alert-dismissable"><button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button><strong><i class="fa fa-check"></i> Success! </strong>'+data.message+'.</div>';
                $('#alertMessage').html(result);
                setInterval(function(){
                    $('#alertMessage').slideUp(3000);
                }, 1000);
            },

        });

    });

</script>
<script>
    $(document).ready(function(){
        $('[data-toggle="tooltip"]').tooltip();
    });
</script>

@stop