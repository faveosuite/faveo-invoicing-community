@extends('themes.default1.layouts.master')
@section('title')
Edit
@stop
@section('content-header')
    <div class="col-sm-6">
        <h1>Edit Script Code</h1>
    </div>
    <div class="col-sm-6">
        <ol class="breadcrumb float-sm-right">
            <li class="breadcrumb-item"><a href="{{url('/')}}"><i class="fa fa-dashboard"></i> Home</a></li>
            <li class="breadcrumb-item"><a href="{{url('settings')}}"><i class="fa fa-dashboard"></i> Settings</a></li>
            <li class="breadcrumb-item"><a href="{{url('chat')}}"><i class="fa fa-dashboard"></i> Script</a></li>
            <li class="breadcrumb-item active">Edit Script</li>
        </ol>
    </div><!-- /.col -->
@stop

@section('content')
<div class="card card-secondary card-outline">


    {!! html()->modelForm($chat, 'PATCH', url('chat/' . $chat->id))->open() !!}



    <div class="card-body">

        <div class="row">

            <div class="col-md-12">

                

                <div class="row">

                    <div class="col-md-12 form-group {{ $errors->has('name') ? 'has-error' : '' }}">
                        <!-- first name -->
                        {!! html()->label(Lang::get('message.name'), 'name')->class('required') !!}
                        {!! html()->text('name')->class('form-control') !!}

                    </div>

                  
                  

                </div>


                 <div class="row">

                    <div class="col-md-6 form-group {{ $errors->has('name') ? 'has-error' : '' }}">
                        <!-- first name -->
                        {!! html()->label('Show script', 'script')->class('required') !!}
                        <br>
                        {!! html()->label('On registration', 'on_registration') !!}
                        {!! html()->radio('on_registration', 1)->checked() !!}
                        &nbsp; &nbsp; &nbsp;
                        {!! html()->label('On every page', 'on_every_page') !!}
                        {!! html()->radio('on_registration', 0) !!}

                    </div>

                     <div class="col-md-3 form-group">
                         <!-- first name -->
                         {!! html()->label('Google analytics', 'analytics') !!}
                         {!! html()->hidden('google_analytics', 0)->id('hidden_analytic') !!}
                         {!! html()->checkbox('google_analytics', $chat->google_analytics)->id('analytics') !!}
                         <!-- <input type="checkbox" name="google_analytics" id="analytics"> -->
                     </div>
                     <br>
                     <div class="col-md-3 form-group analytics_tag" hidden>
                         {!! html()->label('Google analytics tag', 'tag')->class('required') !!}
                         {!! html()->text('google_analytics_tag')->class('form-control') !!}
                     </div>

                    </div>
                    <div class="col-md-12 form-group ">
                        {!! html()->label(Lang::get('message.content'), 'data')->class('required') !!}


    <span class="tooltip-icon" style="color: #007bff;" data-toggle="tooltip" data-placement="top" title="{{ trans('message.tooltip_js_code') }}">
  <i class="fas fa-question-circle"></i>
</span>

                        {!! html()->textarea('script')->class('form-control')->id('textarea') !!}
                    </div>
        <button type="submit" class="btn btn-primary pull-right" id="submit" data-loading-text="<i class='fa fa-circle-o-notch fa-spin'>&nbsp;</i> Saving..."><i class="fa fa-sync-alt">&nbsp;&nbsp;</i>Update</button>

    </div>

</div>


        {!! html()->form()->close() !!}

<script>
    /*when the admin lte and bootstrap gets upgrade the below code for tooltip-inner code need to be removed*/
    $(function () {
        $('[data-toggle="tooltip"]').tooltip();
    });
</script>
    <style>
        .tooltip-inner {
            background-color: black;
            color: #fff; /* Change this to the desired text color */
        }
    </style>

<script>
     $('ul.nav-sidebar a').filter(function() {
        return this.id == 'setting';
    }).addClass('active');

    // for treeview
    $('ul.nav-treeview a').filter(function() {
        return this.id == 'setting';
    }).parentsUntil(".nav-sidebar > .nav-treeview").addClass('menu-open').prev('a').addClass('active');

    $(document).ready(function(){
        var analytics = $('#analytics').val();
        console.log(analytics)
        if(analytics == 1) {
            $('#analytics').prop('checked',true)
            $('.analytics_tag').attr('hidden',false)
        } else {
           $('#analytics').prop('checked',false)
            $('.analytics_tag').attr('hidden',true) 
        }


        $('#analytics:checkbox').on('change',function(){
        if($(this).is(':checked')){
           $('#analytics').val(1);
           $('.analytics_tag').attr('hidden',false)
        } else {
             $('#analytics').val(0);
           $('.analytics_tag').attr('hidden',true)
        }
    })
    })
</script>
@stop