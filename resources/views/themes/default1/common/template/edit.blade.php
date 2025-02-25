@extends('themes.default1.layouts.master')
@section('title')
Edit Templates
@stop
@section('content-header')
 <style>
        .shortcode-box {
            border: 1px solid #ccc;
            padding: 20px;
            margin-bottom: 20px;
        }

        .shortcode-container {
            margin-top: 10px;
        }

        .shortcode {
            margin-right: 20px;
        }

        .form-box {
            border: 1px solid #ccc;
            padding: 20px;
            margin: 20px 0;
        }
    </style>
    <div class="col-sm-6">
        <h1>Edit Template</h1>
    </div>
    <div class="col-sm-6">
        <ol class="breadcrumb float-sm-right">
            <li class="breadcrumb-item"><a href="{{url('/')}}"><i class="fa fa-dashboard"></i> Home</a></li>
            <li class="breadcrumb-item"><a href="{{url('settings')}}"><i class="fa fa-dashboard"></i> Settings</a></li>
            <li class="breadcrumb-item"><a href="{{url('template')}}"><i class="fa fa-dashboard"></i> Templates</a></li>
            <li class="breadcrumb-item active">Edit Template</li>
        </ol>
    </div><!-- /.col -->
@stop

@section('content')

<div class="card card-secondary card-outline">
    <!-- Card Header with explanatory text -->
    <div class="card-header">
        <h3 class="card-title">Shortcode Information</h3>
        <div class="card-tools">
            <button type="button" class="btn btn-tool" data-card-widget="collapse" data-toggle="tooltip" title="Collapse">
                <i class="fas fa-minus"></i>
            </button>
        </div>
    </div>
    <!-- Card Body with shortcode section -->
    <div class="card-body">
        <p>Below are the available shortcodes that you can use in your template:</p>
        <div class="shortcode-box">
            <h4>Available Shortcodes:</h4>
            <div class="shortcode-container">
                @foreach ($codes as $code)
                    <span class="shortcode" data-toggle="tooltip" data-placement="top" title="{{ $tooltips[$code] }}">{{ $code }}</span>
                @endforeach
            </div>
        </div>
    </div>
</div>


<div class="card card-secondary card-outline">


        {!! Form::model($template,['url'=>'template/'.$template->id,'method'=>'patch','id'=>'templateEditForm']) !!}


    <div class="card-body">

        <div class="row">

            <div class="col-md-12">

                

                <div class="row">

                    <div class="col-md-6 form-group {{ $errors->has('name') ? 'has-error' : '' }}">
                        <!-- first name -->
                        {!! Form::label('name',Lang::get('Subject'),['class'=>'required']) !!}
                        {!! Form::text('name',null,['class' => 'form-control']) !!}
                        @error('name')
                        <span class="error-message"> {{$message}}</span>
                        @enderror
                        <div class="input-group-append">
                        </div>
                    </div>

                    <div class="col-md-6 form-group {{ $errors->has('type') ? 'has-error' : '' }}">
                        <!-- last name -->
                        {!! Form::label('type',Lang::get('message.template-types'),['class'=>'required']) !!}
                        {!! Form::select('type',[''=>'Select','Type'=>$type],null,['class' => 'form-control']) !!}
                        @error('type')
                        <span class="error-message"> {{$message}}</span>
                        @enderror
                        <div class="input-group-append">
                        </div>
                    </div>

                     <div class="col-md-6 form-group {{ $errors->has('reply_to') ? 'has-error' : '' }}">
                        <!-- first name -->
                        {!! Form::label('reply_to',Lang::get('Reply to')) !!}
                        {!! Form::text('reply_to',null,['class' => 'form-control']) !!}
                         @error('reply_to')
                         <span class="error-message"> {{$message}}</span>
                         @enderror
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
                          ],
                          setup: function(editor) {
                              editor.on('init', function () {
                                  document.querySelector(".tox-tinymce").style.border = "1px solid silver"; // Change 'green' to any color
                              });
                          },
                      });
                        </script>

                        {!! Form::label('data',Lang::get('message.content'),['class'=>'required']) !!}
                        {!! Form::textarea('data',null,['class'=>'form-control','id'=>'textarea']) !!}
                        @error('data')
                        <span class="error-message"> {{$message}}</span>
                        @enderror
                    </div>


                </div>

            </div>

        </div>
        <button type="submit" class="btn btn-primary pull-right" id="submit" data-loading-text="<i class='fa fa-circle-o-notch fa-spin'>&nbsp;</i> Saving..."><i class="fa fa-sync-alt">&nbsp;</i>{!!Lang::get('message.update')!!}</button>

    </div>

</div>


{!! Form::close() !!}

<script>

    $(document).ready(function() {

        tinymce.get('textarea').on('change', function() {
            let content = tinymce.get('textarea').getContent();
            if(content !==''){
                let editorContainer = document.querySelector(".tox-tinymce");
                editorContainer.style.border = "1px solid silver";
                removeErrorMessage(document.getElementById('textarea'));
            }
        });

        const userRequiredFields = {
            name:@json(trans('message.templateEdit_details.subject')),
            type:@json(trans('message.templateEdit_details.template_type')),
            textarea:@json(trans('message.templateEdit_details.content')),
        };

        $('#templateEditForm').on('submit', function (e) {

            if ($('#textarea').val() === '') {
                let editorContainer = document.querySelector(".tox-tinymce");
                editorContainer.style.border = "1px solid #dc3545";
            }
            else if($('#textarea').val() !== ''){
                let editorContainer = document.querySelector(".tox-tinymce");
                editorContainer.style.border = "1px solid silver";
            }else{
                let editorContainer = document.querySelector(".tox-tinymce");
                editorContainer.style.border = "1px solid silver";
            }

            const userFields = {
                name:$('#name'),
                type:$('#type'),
                textarea:$('#textarea'),
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
        ['name','type','textarea'].forEach(id => {

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
    $(document).ready(function() {
        $('.shortcode').each(function() {
            var tooltipText = $(this).data('tooltip');
            $(this).attr('title', tooltipText);
        });
        $('[data-toggle="tooltip"]').tooltip();
    });
</script>
@stop