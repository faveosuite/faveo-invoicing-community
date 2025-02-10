@extends('themes.default1.layouts.master')
@section('title')
Create Widget
@stop
@section('content-header')
    <div class="col-sm-6">
        <h1>Create New Widget</h1>
    </div>
    <div class="col-sm-6">
        <ol class="breadcrumb float-sm-right">
            <li class="breadcrumb-item"><a href="{{url('/')}}"><i class="fa fa-dashboard"></i> {{ __('message.home')}}</a></li>
            <li class="breadcrumb-item"><a href="{{url('settings')}}"><i class="fa fa-dashboard"></i> {{ __('message.settings')}}</a></li>
            <li class="breadcrumb-item"><a href="{{url('widgets')}}"><i class="fa fa-dashboard"></i> {{ __('message.all_widgets')}}</a></li>
            <li class="breadcrumb-item active">{{ __('message.create_widget')}}</li>
        </ol>
    </div><!-- /.col -->
@stop
@section('content')
<div class="card card-secondary card-outline">


        {!! Form::open(['url'=>'widgets','method'=>'post']) !!}



    <div class="card-body">

        <div class="row">

            <div class="col-md-12">

                

                <div class="row">

                    <div class="col-md-4 form-group {{ $errors->has('name') ? 'has-error' : '' }}">
                        <!-- first name -->
                        {!! Form::label('name',Lang::get('message.name'),['class'=>'required']) !!}
                        {!! Form::text('name',null,['class' => 'form-control','id'=>'name']) !!}

                    </div>

                    <div class="col-md-4 form-group {{ $errors->has('publish') ? 'has-error' : '' }}">
                        <!-- last name -->
                        {!! Form::label('publish',Lang::get('message.publish'),['class'=>'required']) !!}
                        {!! Form::select('publish',[1=>'Yes',0=>'No'],null,['class' => 'form-control']) !!}

                    </div>
                     

                <?php
                $mail = ['class' => 'form-control', 'disabled' => 'true', 'title' => Lang::get('message.configure_mailchimp')];
                $twitter = ['class' => 'form-control', 'disabled' => 'true', 'title' => Lang::get('message.configure_tweet')];
                
                ?>

                    
                  
                    <div class="col-md-4 form-group {{ $errors->has('allow_tweets') ? 'has-error' : '' }}">
                        <!-- last name -->
                        {!! Form::label('allow_tweets',Lang::get('message.allow_tweets'),['class'=>'required']) !!}
                        {!! Form::select('allow_tweets',[1=>'Yes',0=>'No'],null,($twitterStatus) ? ['class' => 'form-control'] : $twitter) !!}

                    </div>
                  
        

                   
                   
                   <div class="col-md-4 form-group {{ $errors->has('allow_mailchimp') ? 'has-error' : '' }}">
                        <!-- last name -->
                        {!! Form::label('allow_mailchimp',Lang::get('message.allow_mailchimp'),['class'=>'required']) !!}
                        {!! Form::select('allow_mailchimp',[1=>'Yes',0=>'No'],null,($mailchimpStatus) ? ['class' => 'form-control'] : $mail) !!}

                    </div>
                 
                   

                    <div class="col-md-4 form-group {{ $errors->has('allow_mailchimp') ? 'has-error' : '' }}">
                        <!-- last name -->
                        {!! Form::label('allow_social_media','Allow social media icons',['class'=>'required']) !!}
                        {!! Form::select('allow_social_media',[1=>'Yes',0=>'No'],null,['class' => 'form-control']) !!}

                    </div>

                    
                    <div class="col-md-4 form-group {{ $errors->has('type') ? 'has-error' : '' }}">
                        <!-- last name -->
                        {!! Form::label('type',Lang::get('message.type'),['class'=>'required']) !!}
                        {!! Form::select('type', [''=>'Choose','footer1'=>'Footer 1','footer2'=>'Footer 2','footer3'=>'Footer 3'],null,['class' => 'form-control']) !!}

                        


                    </div>

                   


                </div>
                
                <div class="row">
                    <div class="col-md-12 form-group">

                       <script src="https://cdn.tiny.cloud/1/oiio010oipuw2n6qyq3li1h993tyg25lu28kgt1trxnjczpn/tinymce/5/tinymce.min.js" referrerpolicy="origin"></script>
                        <script>
                          tinymce.init({
                                         selector: 'textarea',
                                         height: 500,
                                         theme: 'silver',
                                         relative_urls: true,
                                         remove_script_host: false,
                                         convert_urls: false,
                                         plugins: [
                                          'advlist autolink lists link image charmap print preview hr anchor pagebreak',
                                          'searchreplace wordcount visualblocks visualchars code fullscreen',
                                          'insertdatetime media nonbreaking save table contextmenu directionality',
                                          'emoticons template paste textcolor colorpicker textpattern imagetools'
                                          ],
                                         toolbar1: 'insertfile undo redo | styleselect | bold italic | alignleft aligncenter alignright alignjustify | bullist numlist outdent indent | link image',
                                          toolbar2: 'print preview media | forecolor backcolor emoticons',
                                          image_advtab: true,
                                          templates: [
                                              {title: 'Test template 1', content: 'Test 1'},
                                              {title: 'Test template 2', content: 'Test 2'}
                                          ],
                                          content_css: [
                                              '//fast.fonts.net/cssapi/e6dc9b99-64fe-4292-ad98-6974f93cd2a2.css',
                                              '//www.tinymce.com/css/codepen.min.css'
                                          ]
                                    });
</script>


                        {!! Form::label('content',Lang::get('message.content')) !!}
                        {!! Form::textarea('content',null,['class'=>'form-control','id'=>'textarea']) !!}

                    </div>


                </div>

            </div>

        </div>
       <button type="submit" class="btn btn-primary pull-right" id="submit" data-loading-text="<i class='fa fa-circle-o-notch fa-spin'>&nbsp;</i> Saving..."><i class="fa fa-save">&nbsp;&nbsp;</i>{!!Lang::get('message.save')!!}</button>

    </div>

</div>


{!! Form::close() !!}
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

    $(document).on('input', '#name', function () {
        
         $.ajax({
            type: "get",
            data:{'url':this.value},
            url: "{{url('get-url')}}",
            success: function (data) {
                $("#url").val(data)
            }
        });
    });
    $(document).on('input', '#name', function () {
        
         $.ajax({
            type: "get",
            data:{'slug':this.value},
            url: "{{url('get-url')}}",
            success: function (data) {
                $("#slug").val(data)
            }
        });
    });
</script>
@stop

