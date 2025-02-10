@extends('themes.default1.layouts.master')
@section('title')
Edit Page
@stop
@section('content-header')
    <div class="col-sm-6">
        <h1>{{ __('message.edit_page')}}</h1>
    </div>
    <div class="col-sm-6">
        <ol class="breadcrumb float-sm-right">
            <li class="breadcrumb-item"><a href="{{url('/')}}"><i class="fa fa-dashboard"></i> {{ __('message.home')}}</a></li>
            <li class="breadcrumb-item"><a href="{{url('pages')}}"><i class="fa fa-dashboard"></i> {{ __('message.pages')}}</a></li>
            <li class="breadcrumb-item active">{{ __('message.edit_page')}}</li>
        </ol>
    </div><!-- /.col -->
@stop

@section('content')
<div class="card card-secondary card-outline">

      {!! Form::model($page,['url'=>'pages/'.$page->id,'method'=>'patch']) !!}


    <div class="card-body table-responsive">

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

                    <div class="col-md-4 form-group {{ $errors->has('slug') ? 'has-error' : '' }}">
                        <!-- first name -->
                        {!! Form::label('slug',Lang::get('message.slug'),['class'=>'required']) !!}
                        {!! Form::text('slug',null,['class' => 'form-control','id'=>'slug']) !!}

                    </div>


                </div>
                <div class="row">

                    <div class="col-md-4 form-group {{ $errors->has('url') ? 'has-error' : '' }}">
                        <!-- first name -->
                        {!! Form::label('url',Lang::get('message.url'),['class'=>'required']) !!}
                        {!! Form::text('url',null,['class' => 'form-control','id'=>'url']) !!}

                    </div>

                    <div class="col-md-4 form-group {{ $errors->has('parent_page_id') ? 'has-error' : '' }}">
                        <!-- last name -->
                        {!! Form::label('parent_page_id',Lang::get('message.parent-page')) !!}
                        <select name="parent_page_id"  class="form-control">
                            <option value="0">{{ __('message.choose')}}</option>
                            @foreach($parents as $key=>$parent)

                                   <option value="{{$key}}" <?php  if(in_array($parent, $parentName) ) { echo "selected";} ?>>{{$parent}}</option>
                           
                             @endforeach
                        </select>
                    </div>

                     <div class="col-md-4 form-group {{ $errors->has('parent_page_id') ? 'has-error' : '' }}">
                        <!-- last name -->
                        {!! Form::label('type',Lang::get('message.page_type')) !!}
                          {!! Form::select('type',['none'=>'None','contactus'=>'Contact Us'],null,['class' => 'form-control']) !!} 

                    </div>
                    <?php
                         $defaults = DB::table('frontend_pages')->pluck('name','id')->toArray();
                         ?>
                       <div class="col-md-6 form-group {{ $errors->has('parent_page_id') ? 'has-error' : '' }}">
                        <!-- last name -->
                        {!! Form::label('default_page_id',Lang::get('message.default-page'),['class'=>'required']) !!}

                                   <select name="default_page_id"  class="form-control">
                                     <option value="">{{ __('message.my_invoices')}}</option>
                         @foreach($defaults as $key=>$value)
                                   <option value="{{$key}}" <?php  if($key == $selectedDefault)  { echo "selected";} ?>>{{$value}}</option>
                           
                             @endforeach
                              </select>

                    </div>
                    <div class="col-md-6 form-group {{ $errors->has('parent_page_id') ? 'has-error' : '' }}">
                        <!-- last name -->
                        {!! Form::label('publish_date',Lang::get('message.publish-date'),['class'=>'required']) !!}

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


                        {!! Form::label('content',Lang::get('message.content'),['class'=>'required']) !!}
                        {!! Form::textarea('content',null,['class'=>'form-control','id'=>'textarea']) !!}

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

{!! Form::close() !!}

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

