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

<div class="card card-primary card-outline">

       
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
    });
</script>
@stop