@extends('themes.default1.layouts.master')
@section('title')
Edit Page
@stop
@section('content-header')
    <div class="col-sm-6">
        <h1>Edit Page</h1>
    </div>
    <div class="col-sm-6">
        <ol class="breadcrumb float-sm-right">
            <li class="breadcrumb-item"><a href="{{url('/')}}"><i class="fa fa-dashboard"></i> Home</a></li>
            <li class="breadcrumb-item"><a href="{{url('pages')}}"><i class="fa fa-dashboard"></i> Pages</a></li>
            <li class="breadcrumb-item active">Edit Page</li>
        </ol>
    </div><!-- /.col -->
@stop

@section('content')
<div class="card card-secondary card-outline">

    {!! html()->model($page)->method('patch')->route('pages.update', $page->id) !!}


    <div class="card-body table-responsive">

        <div class="row">

            <div class="col-md-12">



                <div class="row">

                    <div class="col-md-4 form-group {{ $errors->has('name') ? 'has-error' : '' }}">
                        {!! html()->label(Lang::get('message.name'), 'name')->class('required') !!}
                        {!! html()->text('name')->class('form-control')->id('name') !!}
                    </div>

                    <div class="col-md-4 form-group {{ $errors->has('publish') ? 'has-error' : '' }}">
                        {!! html()->label(Lang::get('message.publish'), 'publish')->class('required') !!}
                        {!! html()->select('publish')->options([1 => 'Yes', 0 => 'No'])->class('form-control') !!}
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
                        <!-- last name -->
                        {!! html()->label(Lang::get('message.parent-page'), 'parent_page_id') !!}
                        <select name="parent_page_id"  class="form-control">
                            <option value="0">Choose</option>
                            @foreach($parents as $key=>$parent)

                                   <option value="{{$key}}" <?php  if(in_array($parent, $parentName) ) { echo "selected";} ?>>{{$parent}}</option>
                           
                             @endforeach
                        </select>
                    </div>

                     <div class="col-md-4 form-group {{ $errors->has('parent_page_id') ? 'has-error' : '' }}">
                        <!-- last name -->
                         {!! html()->label(Lang::get('message.page_type'), 'type') !!}
                         {!! html()->select('type', ['none' => 'None', 'contactus' => 'Contact Us'])->class('form-control') !!}

                     </div>
                    <?php
                         $defaults = DB::table('frontend_pages')->pluck('name','id')->toArray();
                         ?>
                       <div class="col-md-6 form-group {{ $errors->has('parent_page_id') ? 'has-error' : '' }}">
                        <!-- last name -->
                           {!! html()->label(Lang::get('message.default-page'), 'default_page_id')->class('required') !!}

                           <select name="default_page_id"  class="form-control">
                                     <option value="">My Invoices</option>
                         @foreach($defaults as $key=>$value)
                                   <option value="{{$key}}" <?php  if($key == $selectedDefault)  { echo "selected";} ?>>{{$value}}</option>
                           
                             @endforeach
                              </select>

                    </div>
                    <div class="col-md-6 form-group {{ $errors->has('parent_page_id') ? 'has-error' : '' }}">
                        <!-- last name -->
                        {!! html()->label(Lang::get('message.publish-date'), 'publish_date')->class('required') !!}

                        <div class="input-group date" id="publishing_date" data-target-input="nearest">
                        <input type="text" name="created_at" value="{{$publishingDate}}" class="form-control datetimepicker-input" autocomplete="off"  data-target="#publishing_date"/>

                        <div class="input-group-append" data-target="#publishing_date"  data-toggle="datetimepicker">
                            <div class="input-group-text"><i class="fa fa-calendar"></i></div>
                        </div>

                        </div>

                         <!--  <div class="input-group">
                    <div class="input-group-prepend">
                      <span class="input-group-text"><i class="far fa-calendar-alt"></i></span>
                    </div>
                    <input type="text" class="form-control" data-inputmask-alias="datetime" data-inputmask-inputformat="dd/mm/yyyy" data-mask>
                  </div> -->
                       <!--  <div class="form-group">
                         <div class="input-group">
                             <div class="input-group-prepend">
                      <span class="input-group-text"><i class="far fa-calendar-alt"></i></span>
                         </div>
                     <input name="created_at" type="text" value="{{$publishingDate}}" class="form-control" data-inputmask-alias="datetime" data-inputmask-inputformat="dd/mm/yyyy" data-mask>
                        </div>
                      </div> -->

                          <!-- <div class="form-group">
                                    <div class='input-group date' id='datetimepicker1'>
                                        <input type='text' name="valid_from" id="valid_from" class="form-control" />
                                        <span class="input-group-addon">
                                            <span class="glyphicon glyphicon-calendar"></span>
                                        </span>
                                    </div>
                                </div> -->

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
        <button type="submit" class="btn btn-primary pull-right" id="submit"><i class="fa fa-sync-alt">&nbsp;</i>&nbsp;{!!Lang::get('message.update')!!}</button>
    </div>

</div>
<script>
     $('ul.nav-sidebar a').filter(function() {
        return this.id == 'all_page';
    }).addClass('active');

    // for treeview
    $('ul.nav-treeview a').filter(function() {
        return this.id == 'all_page';
    }).parentsUntil(".nav-sidebar > .nav-treeview").addClass('menu-open').prev('a').addClass('active');
</script>

{!! html()->closeModelForm() !!}

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
@section('icheck')
<script>
  $('#publishing_date').datetimepicker({
        format: 'L'
    });
  </script>
  @stop
@stop

