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

                {!! html()->form('POST', 'queue/'.$queue->id)->id('form') !!}
                <div id="response">

                    </div>
                      <button type="submit" class="form-group btn btn-primary pull-right"  id="submitButton"><i class="fa fa-save">&nbsp;&nbsp;</i>{!!Lang::get('message.save')!!}</button>
                    <!-- {!! html()->submit('save')->class('btn btn-primary')->id('submitButton')->disabled() !!} -->
                    {!! html()->form()->close() !!}
             
                 
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