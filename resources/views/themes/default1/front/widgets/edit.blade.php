@extends('themes.default1.layouts.master')
@section('title')
Edit Widget
@stop
@section('content-header')
    <div class="col-sm-6">
        <h1>Edit Widget</h1>
    </div>
    <div class="col-sm-6">
        <ol class="breadcrumb float-sm-right">
            <li class="breadcrumb-item"><a href="{{url('/')}}"><i class="fa fa-dashboard"></i> Home</a></li>
            <li class="breadcrumb-item"><a href="{{url('settings')}}"><i class="fa fa-dashboard"></i> Settings</a></li>
            <li class="breadcrumb-item"><a href="{{url('widgets')}}"><i class="fa fa-dashboard"></i> All Widgets</a></li>
            <li class="breadcrumb-item active">Edit Widget</li>
        </ol>
    </div><!-- /.col -->
@stop
@section('content')
<div class="card card-secondary card-outline">



        {!! Form::model($widget,['url'=>'widgets/'.$widget->id,'method'=>'patch','id'=>'widgetForm']) !!}


    <div class="card-body">

        <div class="row">

            <div class="col-md-12">



                <div class="row">

                    <div class="col-md-4 form-group {{ $errors->has('name') ? 'has-error' : '' }}">
                        <!-- first name -->
                        {!! Form::label('name',Lang::get('message.name'),['class'=>'required']) !!}
                        {!! Form::text('name',null,['class' => 'form-control'. ($errors->has('name') ? ' is-invalid' : ''),'id'=>'name']) !!}
                        <div class="input-group-append">
                        </div>
                    </div>

                    <div class="col-md-4 form-group {{ $errors->has('publish') ? 'has-error' : '' }}">
                        <!-- last name -->
                        {!! Form::label('publish',Lang::get('message.publish'),['class'=>'required']) !!}
                        {!! Form::select('publish',[1=>'Yes',0=>'No'],null,['class' => 'form-control'. ($errors->has('publish') ? ' is-invalid' : ''),'id'=>'publish']) !!}
                        <div class="input-group-append">
                        </div>
                    </div>
                   <?php 
                $mail = ['class' => 'form-control','disabled' => 'true' , 'title' => 'Cofigure your mailchimp in settings to access'];
                $twitter = ['class' => 'form-control','disabled' => 'true', 'title' => 'Configure your tweets in settings to access'];
                
                ?>
                   
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
                        {!! Form::select('type', [''=>'Choose','footer1'=>'Footer 1','footer2'=>'Footer 2','footer3'=>'Footer 3'],$widget->type,['class' => 'form-control'. ($errors->has('type') ? ' is-invalid' : ''),'id'=>'type']) !!}
                        <div class="input-group-append">
                        </div>
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
                        {!! Form::textarea('content',null,['class'=>'form-control'. ($errors->has('content') ? ' is-invalid' : ''),'id'=>'textarea']) !!}

                    </div>


                </div>

            </div>

        </div>
        <button type="submit" class="btn btn-primary pull-right" id="submit" data-loading-text="<i class='fa fa-circle-o-notch fa-spin'>&nbsp;</i> Saving..."><i class="fa fa-sync-alt">&nbsp;&nbsp;</i>{!!Lang::get('message.update')!!}</button>

    </div>

</div>


{!! Form::close() !!}

<script>

    $(document).ready(function() {
        const userRequiredFields = {
            name:@json(trans('message.widget_details.name')),
            publish:@json(trans('message.widget_details.publish')),
            type:@json(trans('message.widget_details.type')),

        };

        $('#widgetForm').on('submit', function (e) {
            const userFields = {
                name:$('#name'),
                publish:$('#publish'),
                type:$('#type'),

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
            data: {'url': this.value},
            url: "{{url('get-url')}}",
            success: function (data) {
                $("#url").val(data)
            }
        });
    });
    $(document).on('input', '#name', function () {

        $.ajax({
            type: "get",
            data: {'slug': this.value},
            url: "{{url('get-url')}}",
            success: function (data) {
                $("#slug").val(data)
            }
        });
    });
</script>
@stop

