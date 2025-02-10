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
        <h1>{{ __('message.edit_template') }}</h1>
    </div>
    <div class="col-sm-6">
        <ol class="breadcrumb float-sm-right">
            <li class="breadcrumb-item"><a href="{{url('/')}}"><i class="fa fa-dashboard"></i> {{ __('message.home') }}</a></li>
            <li class="breadcrumb-item"><a href="{{url('settings')}}"><i class="fa fa-dashboard"></i> {{ __('message.settings') }}</a></li>
            <li class="breadcrumb-item"><a href="{{url('template')}}"><i class="fa fa-dashboard"></i> {{ __('message.templates') }}</a></li>
            <li class="breadcrumb-item active">{{ __('message.edit_template') }}</li>
        </ol>
    </div><!-- /.col -->
@stop

@section('content')

<div class="card card-secondary card-outline">
    <!-- Card Header with explanatory text -->
    <div class="card-header">
        <h3 class="card-title">{{ __('message.shortcode_information') }}</h3>
        <div class="card-tools">
            <button type="button" class="btn btn-tool" data-card-widget="collapse" data-toggle="tooltip" title="Collapse">
                <i class="fas fa-minus"></i>
            </button>
        </div>
    </div>
    <!-- Card Body with shortcode section -->
    <div class="card-body">
        <p>{{ __('message.below_shortcodes_templates') }}</p>
        <div class="shortcode-box">
            <h4>{{ __('message.available_shortcodes') }}</h4>
            <div class="shortcode-container">
                @foreach ($codes as $code)
                    <span class="shortcode" data-toggle="tooltip" data-placement="top" title="{{ $tooltips[$code] }}">{{ $code }}</span>
                @endforeach
            </div>
        </div>
    </div>
</div>


<div class="card card-secondary card-outline">


        {!! Form::model($template,['url'=>'template/'.$template->id,'method'=>'patch']) !!}


    <div class="card-body">

        <div class="row">

            <div class="col-md-12">

                

                <div class="row">

                    <div class="col-md-6 form-group {{ $errors->has('name') ? 'has-error' : '' }}">
                        <!-- first name -->
                        {!! Form::label('name',Lang::get('Subject'),['class'=>'required']) !!}
                        {!! Form::text('name',null,['class' => 'form-control']) !!}

                    </div>

                    <div class="col-md-6 form-group {{ $errors->has('type') ? 'has-error' : '' }}">
                        <!-- last name -->
                        {!! Form::label('type',Lang::get('message.template-types'),['class'=>'required']) !!}
                        {!! Form::select('type',[''=>'Select','Type'=>$type],null,['class' => 'form-control']) !!}

                    </div>

                     <div class="col-md-6 form-group {{ $errors->has('reply_to') ? 'has-error' : '' }}">
                        <!-- first name -->
                         {!! Form::label('reply_to', Lang::get('message.reply_to')) !!}
                        {!! Form::text('reply_to',null,['class' => 'form-control']) !!}

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

                        {!! Form::label('data',Lang::get('message.content'),['class'=>'required']) !!}
                        {!! Form::textarea('data',null,['class'=>'form-control','id'=>'textarea']) !!}

                    </div>


                </div>

            </div>

        </div>
        <button type="submit" class="btn btn-primary pull-right" id="submit" data-loading-text="<i class='fa fa-circle-o-notch fa-spin'>&nbsp;</i> Saving..."><i class="fa fa-sync-alt">&nbsp;</i>{!!Lang::get('message.update')!!}</button>

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
    $(document).ready(function() {
        $('.shortcode').each(function() {
            var tooltipText = $(this).data('tooltip');
            $(this).attr('title', tooltipText);
        });
        $('[data-toggle="tooltip"]').tooltip();
    });
</script>
@stop