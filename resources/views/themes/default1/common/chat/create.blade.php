@extends('themes.default1.layouts.master')
@section('title')
 {{ __('message.script') }}
@stop
@section('content-header')
    <div class="col-sm-6">
        <h1>{{ __('message.create_script_code')}}</h1>
    </div>
    <div class="col-sm-6">
        <ol class="breadcrumb float-sm-right">
            <li class="breadcrumb-item"><a href="{{url('/')}}"><i class="fa fa-dashboard"></i> {{ __('message.home')}}</a></li>
            <li class="breadcrumb-item"><a href="{{url('settings')}}"><i class="fa fa-dashboard"></i> {{ __('message.settings')}}</a></li>
            <li class="breadcrumb-item"><a href="{{url('chat')}}"><i class="fa fa-dashboard"></i> {{ __('message.script')}}</a></li>
            <li class="breadcrumb-item active">{{ __('message.create_script')}}</li>
        </ol>
    </div><!-- /.col -->
@stop
@section('content')
<div class="card card-secondary card-outline">

    {!! html()->form('POST', url('chat'))->id('scriptForm')->open() !!}



    <div class="card-body">

        <div class="row">

            <div class="col-md-12">



                <div class="row">

                    <div class="col-md-12 form-group {{ $errors->has('name') ? 'has-error' : '' }}">
                        <!-- first name -->
                        {!! html()->label(Lang::get('message.name'), 'name')->class('required') !!}
                        {!! html()->text('name')->class('form-control'. ($errors->has('name') ? ' is-invalid' : ''))->id('name') !!}
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
                        {!! html()->label( __('message.show_script'), 'script')->class('required') !!}
                        <br>
                        {!! html()->radio('on_registration', 1)->checked() !!}
                        <label for="on_registration" style="font-weight: normal !important;">{{ __('message.on_registration') }}</label>
                        &nbsp;{!! html()->radio('on_registration', 0) !!}
                        <label for="on_every_page" style="font-weight: normal !important;">{{ __('message.on_every_page') }}</label>
                        @error('on_registration')
                        <span class="error-message"> {{$message}}</span>
                        @enderror
                    </div>

                     <div class="col-md-3 form-group">
                        <!-- first name -->
                         {!! html()->checkbox('google_analytics')->id('analytics') !!}
                         {!! html()->label( __('message.google_analytics'), 'analytics') !!}
                         {!! html()->hidden('google_analytics', 0)->id('hidden_analytic') !!}
                        <!-- <input type="checkbox" name="google_analytics" id="analytics"> -->
                    </div>
                        <br>
                    <div class="col-md-3 form-group analytics_tag" hidden>
                        {!! html()->label( __('message.chat_google_analytics_tag'), 'tag')->class('required') !!}
                        {!! html()->text('google_analytics_tag')->class('form-control') !!}
                        <span class="error-messag hide" id="google-error-msg"></span>
                    </div>

                   

                   


                </div>

                <div class="row">
                    <div class="col-md-12 form-group">


                        {!! html()->label(Lang::get('message.content'), 'data')->class('required') !!}
                        {!! html()->textarea('script')->class('form-control'. ($errors->has('script') ? ' is-invalid' : ''))->id('textarea') !!}
                        @error('script')
                        <span class="error-message"> {{$message}}</span>
                        @enderror
                        <div class="input-group-append">
                        </div>
                    </div>


                </div>

            </div>

        </div>
        <button type="submit" class="btn btn-primary pull-right" id="submit" data-loading-text="<i class='fa fa-circle-o-notch fa-spin'>&nbsp;</i> {{ __('message.saving') }}"><i class="fa fa-save">&nbsp;&nbsp;</i>{!!Lang::get('message.save')!!}</button>

    </div>

</div>


{!! html()->form()->close() !!}
<style>
    .error-messag{
        font-size:80%;
        color:#dc3545;
    }
</style>
<script>
   document.querySelector('#google_analytics_tag').addEventListener('input',function(){
       if($('#google_analytics_tag').val()!==''){
           $('#google_analytics_tag').removeClass('is-invalid');
           document.getElementById('google-error-msg').innerHTML ='';
       }

   });
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

            if(isValid && userFields.name.val().length>50){
                showError(userFields.name,@json(trans('message.valid_widget_name')));
                isValid=false;
            }

            // Validate required fields
            Object.keys(userFields).forEach(field => {
                if (!userFields[field].val()) {
                    showError(userFields[field], userRequiredFields[field]);
                    isValid = false;
                }
            });

            if(document.querySelector('input[name="google_analytics"]:checked')){
                if($('#google_analytics_tag').val()===''){
                    $('#google_analytics_tag').addClass('is-invalid');
                    document.getElementById('google-error-msg').innerHTML =@json(trans('message.google_analytics_tag'));
                    isValid=false;
                }
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
        ['name','textarea'].forEach(id => {

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


    $(document).ready(function(){
        $('#analytics').prop('checked',false)
        $('#hidden_analytic').val(0)
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