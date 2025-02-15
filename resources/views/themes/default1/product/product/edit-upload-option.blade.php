@extends('themes.default1.layouts.master')
@section('title')
Edit Product Uploads
@stop
@section('content-header')
    <div class="col-sm-6">
        <h1>Edit Product Upload</h1>
    </div>
    <div class="col-sm-6">
        <ol class="breadcrumb float-sm-right">
            <li class="breadcrumb-item"><a href="{{url('/')}}"><i class="fa fa-dashboard"></i> Home</a></li>
            <li class="breadcrumb-item"><a href="{{url('products')}}"><i class="fa fa-dashboard"></i> All Products</a></li>
            <li class="breadcrumb-item active">Edit Product Uplaod</li>
        </ol>
    </div><!-- /.col -->
@stop
@section('content')
<div class="card card-secondary card-outline">
    <div class="card-header">

        {!! html()->form('PATCH', 'upload/' . $model->id)->model($model) !!}
        <h4>Edit Product </h4>

    </div>

        <div class="card-body">

        <div class="row">

            <div class="col-md-12">

                {!! html()->hidden('title', $model->id)->class('form-control')->disabled() !!}

                <div class="row">
                    <div class="col-md-4 form-group {{ $errors->has('name') ? 'has-error' : '' }}">
                        <!-- first name -->
                        {!! html()->label(Lang::get('message.product'))->class('required')->for('product') !!}
                        {!! html()->text('product', $selectedProduct)->class('form-control')->disabled() !!}
                    </div>

                    <div class="col-md-4 form-group {{ $errors->has('title') ? 'has-error' : '' }}">
                        <!-- first name -->
                        {!! html()->label(Lang::get('message.title'))->class('required')->for('title') !!}
                        {!! html()->text('title')->class('form-control') !!}
                    </div>

                    <div class="col-md-4 form-group {{ $errors->has('version') ? 'has-error' : '' }}">
                        <!-- first name -->
                        {!! html()->label(Lang::get('message.version'))->class('required')->for('version') !!}
                        {!! html()->text('version')->class('form-control')->readonly() !!}
                    </div>

                    <div class="col-md-12 form-group {{ $errors->has('description') ? 'has-error' : '' }}">
                        {!! html()->label(Lang::get('message.description'))->class('required')->for('description') !!}
                        {!! html()->textarea('description')->class('form-control')->id('desc-textarea') !!}
                        <h6 id="descheck"></h6>
                    </div>

                    <div class="col-md-12 form-group {{ $errors->has('description') ? 'has-error' : '' }}">
                        <i class="fa fa-info-circle" style="cursor: help; font-size: small; color: rgb(60, 141, 188);">
                            <label data-toggle="tooltip" style="font-weight:500;" data-placement="top" title="Enter JSON format."></label>
                        </i>
                        {!! html()->label(Lang::get('message.dependencies'))->class('required')->for('dependencies') !!}
                        {!! html()->textarea('dependencies')->class('form-control')->rows(5) !!}
                        <h6 id="descheck"></h6>
                    </div>

                    <div class="col-md-4 form-group {{ $errors->has('is_private') ? 'has-error' : '' }}">
                        <i class="fa fa-info-circle" style="cursor: help; font-size: small; color: rgb(60, 141, 188);">
                            <label data-toggle="tooltip" style="font-weight:500;" data-placement="top" title="If the release is kept private, product users won't receive notification for this release."></label>
                        </i>
                        {!! html()->label('Private Release')->for('is_private') !!}
                        {!! html()->checkbox('is_private', 1) !!}
                    </div>

                    <div class="col-md-4 form-group {{ $errors->has('release_type') ? 'has-error' : '' }}">
                        <i class="fa fa-info-circle" style="cursor: help; font-size: small; color: rgb(60, 141, 188);">
                            <label data-toggle="tooltip" style="font-weight:500;" data-placement="top" title="If the release is kept as Pre release, product users won't receive notification for this release."></label>
                        </i>
                        {!! html()->label('Releases')->for('release_type') !!}
                        {!! html()->select('release_type', ['official' => 'Official', 'pre_release' => 'Pre Release', 'beta' => 'Beta'], $model->release_type) !!}
                    </div>

                    <div class="col-md-4 form-group {{ $errors->has('is_restricted') ? 'has-error' : '' }}">
                        <i class="fa fa-info-circle" style="cursor: help; font-size: small; color: rgb(60, 141, 188);">
                            <label data-toggle="tooltip" style="font-weight:500;" data-placement="top" title="If update is kept restricted for this release, product users need to update their versions upto this release first before updating to further releases."></label>
                        </i>
                        {!! html()->label('Restrict update')->for('is_restricted') !!}
                        {!! html()->checkbox('is_restricted', 1) !!}
                    </div>
                </div>

                <button type="submit" class="btn btn-primary pull-right"><i class="fa fa-sync-alt">&nbsp;</i>{!!Lang::get('message.update')!!}</button>



            </div>

        </div>

    </div>
  </div>
 <script src="https://cdn.tiny.cloud/1/oiio010oipuw2n6qyq3li1h993tyg25lu28kgt1trxnjczpn/tinymce/5/tinymce.min.js" referrerpolicy="origin"></script>
                                    
   <script>
    $(function () {
        $('[data-toggle="tooltip"]').tooltip({
            container : 'body'
        });
    });
</script>
<script>
  
    $(function(){


      tinymce.init({
     selector: '#desc-textarea',
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
});
</script>
 @stop




