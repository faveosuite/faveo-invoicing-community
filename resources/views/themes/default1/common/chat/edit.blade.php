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


        {!! Form::model($chat,['url'=>'chat/'.$chat->id,'method'=>'patch','id'=>'scriptForm']) !!}



    <div class="card-body">

        <div class="row">

            <div class="col-md-12">

                

                <div class="row">

                    <div class="col-md-12 form-group {{ $errors->has('name') ? 'has-error' : '' }}">
                        <!-- first name -->
                        {!! Form::label('name',Lang::get('message.name'),['class'=>'required']) !!}
                        {!! Form::text('name',null,['class' => 'form-control','id'=>'name']) !!}
                        @error('name')
                        <span class="error-message"> {{$message}}</span>
                        @enderror
                        <div class="input-group-append">
                        </div>
                    </div>

                  
                  

                </div>


                 <div class="row">

                    <div class="col-md-6 form-group {{ $errors->has('name') ? 'has-error' : '' }}">
                        <!-- first name -->
                        {!! Form::label('script','Show script',['class'=>'required']) !!}
                        <br>
                        {!! Form::radio('on_registration',1,true) !!}
                        <label for="on_registration" style="font-weight: normal !important;">On registration</label>
                        {!! Form::radio('on_registration',0,false) !!}
                        <label for="on_every_page" style="font-weight: normal !important;">On every page</label>
                        @error('on_registration')
                        <span class="error-message"> {{$message}}</span>
                        @enderror
                    </div>

                     <div class="col-md-3 form-group">
                        <!-- first name -->
                        {!! Form::label('analytics','Google analytics') !!}
                        {{Form::hidden('google_analytics',0,['id'=>'hidden_analytic'])}}
                        {!! Form::checkbox('google_analytics',$chat->google_analytics,null, array('id'=>'analytics')) !!}
                    </div>
                        <br>
                    <div class="col-md-3 form-group analytics_tag" hidden>
                        {!! Form::label('tag','Google analytics tag',['class'=>'required']) !!}
                        {!! Form::text('google_analytics_tag',null,['class' => 'form-control']) !!}
                    </div>

                    </div>
                    <div class="col-md-12 form-group ">
    {!! Form::label('data', Lang::get('message.content'), ['class' => 'required']) !!}


    <span class="tooltip-icon" style="color: #007bff;" data-toggle="tooltip" data-placement="top" title="{{ trans('message.tooltip_js_code') }}">
  <i class="fas fa-question-circle"></i>
</span>

    {!! Form::textarea('script', null, ['class' => 'form-control', 'id' => 'textarea']) !!}
                        @error('script')
                        <span class="error-message"> {{$message}}</span>
                        @enderror
                        <div class="input-group-append">
                        </div>
</div>
        <button type="submit" class="btn btn-primary pull-right" id="submit" data-loading-text="<i class='fa fa-circle-o-notch fa-spin'>&nbsp;</i> Saving..."><i class="fa fa-sync-alt">&nbsp;&nbsp;</i>Update</button>

    </div>

</div>


{!! Form::close() !!}

        <script>

            $(document).ready(function() {
                const userRequiredFields = {
                    name:@json(trans('message.script_details.name')),
                    content:@json(trans('message.script_details.content')),

                };

                $('#scriptForm').on('submit', function (e) {
                    const userFields = {
                        name:$('#name'),
                        content:$('#textarea'),

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

                    if(isValid && userFields.name.val().length>50){
                        showError(userFields.name,@json(trans('message.valid_widget_name')));
                        isValid=false;
                    }

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
                ['name','publish','type'].forEach(id => {

                    document.getElementById(id).addEventListener('input', function () {
                        removeErrorMessage(this);

                    });
                });
            });

        </script>


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