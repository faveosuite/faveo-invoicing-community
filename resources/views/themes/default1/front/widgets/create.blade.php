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
            <li class="breadcrumb-item"><a href="{{url('/')}}"><i class="fa fa-dashboard"></i> Home</a></li>
            <li class="breadcrumb-item"><a href="{{url('settings')}}"><i class="fa fa-dashboard"></i> Settings</a></li>
            <li class="breadcrumb-item"><a href="{{url('widgets')}}"><i class="fa fa-dashboard"></i> All Widgets</a></li>
            <li class="breadcrumb-item active">Create Widget</li>
        </ol>
    </div><!-- /.col -->
@stop
@section('content')
<div class="card card-secondary card-outline">


    {!! html()->form('POST', 'widgets')->open() !!}



    <div class="card-body">

        <div class="row">

            <div class="col-md-12">

                

                <div class="row">

                    <div class="col-md-4 form-group {{ $errors->has('name') ? 'has-error' : '' }}">
                        {!! html()->label(Lang::get('message.name'))->class('required')->for('name') !!}
                        {!! html()->text('name')->class('form-control')->id('name') !!}
                    </div>

                    <div class="col-md-4 form-group {{ $errors->has('publish') ? 'has-error' : '' }}">
                        {!! html()->label(Lang::get('message.publish'))->class('required')->for('publish') !!}
                        {!! html()->select('publish', [1 => 'Yes', 0 => 'No'])->class('form-control') !!}
                    </div>


                    <?php
                $mail = ['class' => 'form-control','disabled' => 'true' , 'title' => 'Cofigure your mailchimp in settings to access'];
                $twitter = ['class' => 'form-control','disabled' => 'true', 'title' => 'Configure your tweets in settings to access'];
                
                ?>



                    <div class="col-md-4 form-group {{ $errors->has('allow_tweets') ? 'has-error' : '' }}">
                        {!! html()->label(Lang::get('message.allow_tweets'))->class('required')->for('allow_tweets') !!}
                        {!! html()->select('allow_tweets', [1 => 'Yes', 0 => 'No'])
                            ->class('form-control')
                            ->attributes(($twitterStatus) ? [] : $twitter)
                        !!}
                    </div>

                    <div class="col-md-4 form-group {{ $errors->has('allow_mailchimp') ? 'has-error' : '' }}">
                        {!! html()->label(Lang::get('message.allow_mailchimp'))->class('required')->for('allow_mailchimp') !!}
                        {!! html()->select('allow_mailchimp', [1 => 'Yes', 0 => 'No'])
                            ->class('form-control')
                            ->attributes(($mailchimpStatus) ? [] : $mail)
                        !!}
                    </div>



                    <div class="col-md-4 form-group {{ $errors->has('allow_social_media') ? 'has-error' : '' }}">
                        {!! html()->label('Allow social media icons')->class('required')->for('allow_social_media') !!}
                        {!! html()->select('allow_social_media', [1 => 'Yes', 0 => 'No'])->class('form-control') !!}
                    </div>

                    <div class="col-md-4 form-group {{ $errors->has('type') ? 'has-error' : '' }}">
                        {!! html()->label(Lang::get('message.type'))->class('required')->for('type') !!}
                        {!! html()->select('type', ['' => 'Choose', 'footer1' => 'Footer 1', 'footer2' => 'Footer 2', 'footer3' => 'Footer 3'])->class('form-control') !!}
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


                        {!! html()->label(Lang::get('message.content'))->for('content') !!}
                        {!! html()->textarea('content')->class('form-control')->id('textarea') !!}

                    </div>


                </div>

            </div>

        </div>
       <button type="submit" class="btn btn-primary pull-right" id="submit" data-loading-text="<i class='fa fa-circle-o-notch fa-spin'>&nbsp;</i> Saving..."><i class="fa fa-save">&nbsp;&nbsp;</i>{!!Lang::get('message.save')!!}</button>

    </div>

</div>


{!! html()->form()->close() !!}
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

