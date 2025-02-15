@extends('themes.default1.layouts.master')
@section('title')
Create Page
@stop
@section('content-header')
    <div class="col-sm-6">
        <h1>Create New Page</h1>
    </div>
    <div class="col-sm-6">
        <ol class="breadcrumb float-sm-right">
            <li class="breadcrumb-item"><a href="{{url('/')}}"><i class="fa fa-dashboard"></i> Home</a></li>
            <li class="breadcrumb-item"><a href="{{url('pages')}}"><i class="fa fa-dashboard"></i> All Pages</a></li>
            <li class="breadcrumb-item active">Create New Page</li>
        </ol>
    </div><!-- /.col -->
@stop

@section('content')
<div class="card card-secondary card-outline">



    {!! html()->form('post', 'pages') !!}




    <div class="card-body">

        <div class="row">

            <div class="col-md-12">

               

                <div class="row">

                    <div class="col-md-4 form-group {{ $errors->has('name') ? 'has-error' : '' }}">
                        <!-- first name -->
                        {!! html()->label(Lang::get('message.name'), 'name')->class('required') !!}
                        {!! html()->text('name')->class('form-control')->id('name') !!}

                    </div>

                    <div class="col-md-4 form-group {{ $errors->has('publish') ? 'has-error' : '' }}">
                        {!! html()->label(Lang::get('message.publish'), 'publish')->class('required') !!}
                        {!! html()->select('publish', [1 => 'Yes', 0 => 'No'])->class('form-control') !!}
                    </div>

                    <div class="col-md-4 form-group {{ $errors->has('slug') ? 'has-error' : '' }}">
                        {!! html()->label(Lang::get('message.slug'), 'slug')->class('required') !!}
                        {!! html()->text('slug')->class('form-control')->id('slug') !!}
                    </div>


                </div>
                <div class="row">

                    <div class="col-md-4 form-group {{ $errors->has('url') ? 'has-error' : '' }}">
                        {!! html()->label(Lang::get('message.url'), 'url')->class('required') !!}
                        {!! html()->text('url')->class('form-control')->id('url') !!}
                    </div>

                    <div class="col-md-4 form-group {{ $errors->has('parent_page_id') ? 'has-error' : '' }}">
                        {!! html()->label(Lang::get('message.parent-page'), 'parent_page_id') !!}
                        {!! html()->select('parent_page_id', ['0' => 'Choose', 'Parent Pages' => $parents])->class('form-control') !!}
                    </div>

                    <div class="col-md-4 form-group {{ $errors->has('parent_page_id') ? 'has-error' : '' }}">
                        {!! html()->label(Lang::get('message.page_type'), 'type') !!}
                        {!! html()->select('type', ['none' => 'None', 'contactus' => 'Contact Us'])->class('form-control') !!}
                    </div>

                </div>

                <div class="row">
                    <div class="col-md-12 form-group">

                        <script src="//cdn.tinymce.com/4/tinymce.min.js"></script>
                        <script>
             tinymce.init({
                          selector: 'textarea',
                          height: 500,
                          theme: 'modern',
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


                        {!! html()->label(Lang::get('message.content'), 'content')->class('required') !!}
                        {!! html()->textarea('content')->class('form-control')->id('textarea') !!}

                    </div>


                </div>

            </div>

        </div>
        <h4><button type="submit" class="btn btn-primary pull-right" id="submit"><i class="fa fa-save">&nbsp;</i>{!!Lang::get('message.save')!!}</button></h4>

    </div>

</div>
<script>
     $('ul.nav-sidebar a').filter(function() {
        return this.id == 'all_new_page';
    }).addClass('active');

    // for treeview
    $('ul.nav-treeview a').filter(function() {
        return this.id == 'all_new_page';
    }).parentsUntil(".nav-sidebar > .nav-treeview").addClass('menu-open').prev('a').addClass('active');
</script>

{!! html()->closeModelForm() !!}

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
               $("#slug").val(data);
            }
        });
    });
</script>
@stop

