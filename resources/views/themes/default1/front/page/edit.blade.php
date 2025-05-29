@extends('themes.default1.layouts.master')
@section('title')
    {{ __('message.edit_page')}}
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

    {!! html()->modelForm($page, 'PATCH', url('pages/'.$page->id))->id('createPage')->open() !!}


    <div class="card-body table-responsive">

        <div class="row">

            <div class="col-md-12">

                <div class="row">

                    <div class="col-md-4 form-group {{ $errors->has('name') ? 'has-error' : '' }}">
                        <!-- name -->
                        {!! html()->label(Lang::get('message.name'), 'name')->class('required') !!}
                        {!! html()->text('name')->class('form-control'.($errors->has('name') ? ' is-invalid' : ''))->id('name') !!}
                        @error('name')
                        <span class="error-message"> {{$message}}</span>
                        @enderror
                        <div class="input-group-append">
                        </div>
                    </div>

                    <div class="col-md-4 form-group {{ $errors->has('publish') ? 'has-error' : '' }}">
                        <!-- publish -->
                        {!! html()->label(Lang::get('message.publish'), 'publish')->class('required') !!}
                        {!! html()->select('publish')->options([1 => 'Yes', 0 => 'No'])->class('form-control'.($errors->has('publish') ? ' is-invalid' : '')) !!}
                        @error('publish')
                        <span class="error-message"> {{$message}}</span>
                        @enderror
                        <div class="input-group-append">
                        </div>
                    </div>

                    <div class="col-md-4 form-group {{ $errors->has('slug') ? 'has-error' : '' }}">
                        <!-- slug -->
                        {!! html()->label(Lang::get('message.slug'), 'slug')->class('required') !!}
                        {!! html()->text('slug')->class('form-control'.($errors->has('slug') ? ' is-invalid' : ''))->id('slug') !!}
                        @error('slug')
                        <span class="error-message"> {{$message}}</span>
                        @enderror
                        <div class="input-group-append">
                        </div>
                    </div>


                </div>
                <div class="row">

                    <div class="col-md-4 form-group {{ $errors->has('url') ? 'has-error' : '' }}">
                        <!-- url -->
                        {!! html()->label(Lang::get('message.url'), 'url')->class('required') !!}
                        {!! html()->text('url')->class('form-control'.($errors->has('url') ? ' is-invalid' : ''))->id('url')->placeholder('https://example.com') !!}
                        @error('url')
                        <span class="error-message"> {{$message}}</span>
                        @enderror
                        <div class="input-group-append">
                        </div>
                    </div>

                    <div class="col-md-4 form-group {{ $errors->has('parent_page_id') ? 'has-error' : '' }}">
                        <!-- parent_page_id -->
                        {!! html()->label(Lang::get('message.parent-page'), 'parent_page_id') !!}
                        <select name="parent_page_id"  class="form-control {{$errors->has('') ? ' is-invalid' : ''}}">
                            <option value="0">{{ __('message.choose')}}</option>
                            @foreach($parents as $key=>$parent)

                                   <option value="{{$key}}" <?php  if(in_array($parent, $parentName) ) { echo "selected";} ?>>{{$parent}}</option>
                           
                             @endforeach
                        </select>
                        @error('parent_page_id')
                        <span class="error-message"> {{$message}}</span>
                        @enderror
                    </div>

                     <div class="col-md-4 form-group {{ $errors->has('parent_page_id') ? 'has-error' : '' }}">
                        <!-- type -->
                         {!! html()->label(Lang::get('message.page_type'), 'type') !!}
                         {!! html()->select('type', ['none' => 'None', 'contactus' => 'Contact Us'])->class('form-control'.($errors->has('type') ? ' is-invalid' : '')) !!}

                     </div>
                    <?php
                         $defaults = DB::table('frontend_pages')->pluck('name','id')->toArray();
                         ?>
                       <div class="col-md-6 form-group {{ $errors->has('parent_page_id') ? 'has-error' : '' }}">
                        <!-- default_page_id -->
                           {!! html()->label(Lang::get('message.default-page'), 'default_page_id')->class('required') !!}
                                   <select name="default_page_id"  class="form-control {{$errors->has('default_page_id') ? ' is-invalid' : ''}}" >
                                     <option value="">{{ __('message.my_invoices')}}</option>
                         @foreach($defaults as $key=>$value)
                                   <option value="{{$key}}" <?php  if($key == $selectedDefault)  { echo "selected";} ?>>{{$value}}</option>
                           
                             @endforeach
                              </select>
                           @error('default_page_id')
                           <span class="error-message"> {{$message}}</span>
                           @enderror
                    </div>
                    <div class="col-md-6 form-group {{ $errors->has('parent_page_id') ? 'has-error' : '' }}">
                        <!-- publish_date -->
                        {!! html()->label(Lang::get('message.publish-date'), 'publish_date')->class('required') !!}

                        <div class="input-group date" id="publishing_date" data-target-input="nearest">
                        <input type="text" name="created_at" value="{{$publishingDate}}" class="form-control datetimepicker-input {{$errors->has('created_at') ? ' is-invalid' : ''}}" autocomplete="off"  data-target="#publishing_date" id="created_at"/>

                        <div class="input-group-append" data-target="#publishing_date"  data-toggle="datetimepicker">
                            <div class="input-group-text"><i class="fa fa-calendar"></i></div>
                        </div>

                        </div>
                        @error('created_at')
                        <span class="error-message"> {{$message}}</span>
                        @enderror
                    </div>

                </div>

                <div class="row">
                    <div class="col-md-12 form-group">
                        @php
                            $locale = app()->getLocale();
                            $rtlLocales = ['ar', 'he'];
                            $isRtl = in_array($locale, $rtlLocales);
                        @endphp

                        <script src="//cdn.tinymce.com/4/tinymce.min.js"></script>
                        <script>
             tinymce.init({
                          selector: 'textarea',
                          height: 500,
                          theme: 'modern',
                          relative_urls: true,
                          remove_script_host: false,
                          convert_urls: false,
                          language: '{{ $locale }}',
                          @if($locale !== 'en')
                          language_url: 'https://cdn.tiny.cloud/1/no-api-key/tinymce/5/langs/{{$locale}}.js',
                          @endif
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
                        {!! html()->textarea('content')->class('form-control'.($errors->has('content') ? ' is-invalid' : ''))->id('textarea') !!}
                        @error('content')
                        <span class="error-message"> {{$message}}</span>
                        @enderror
                        <div class="input-group-append">
                        </div>
                    </div>


                </div>

            </div>

        </div>
        <button type="submit" class="btn btn-primary pull-right" id="submit"><i class="fa fa-sync-alt">&nbsp;</i>&nbsp;{!!Lang::get('message.update')!!}</button>
    </div>

</div>
{!! html()->closeModelForm() !!}

<script>
     $('ul.nav-sidebar a').filter(function() {
        return this.id == 'all_page';
    }).addClass('active');

    // for treeview
    $('ul.nav-treeview a').filter(function() {
        return this.id == 'all_page';
    }).parentsUntil(".nav-sidebar > .nav-treeview").addClass('menu-open').prev('a').addClass('active');


      $(document).ready(function() {
          const userRequiredFields = {
              name:@json(trans('message.page_details.add_name')),
              publish:@json(trans('message.page_details.add_publish')),
              slug:@json(trans('message.page_details.add_slug')),
              url:@json(trans('message.page_details.add_url')),
              content:@json(trans('message.page_details.add_content')),
              created_at:@json(trans('message.page_details.publish_date')),

          };

          $('#createPage').on('submit', function (e) {
              const userFields = {
                  name:$('#name'),
                  publish:$('#publish'),
                  slug:$('#slug'),
                  url:$('#url'),
                  content:$('#textarea'),
                  created_at:$('#created_at'),

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



              if(userFields.url.val()!=='') {
                  if (!isValidURL(userFields.url.val())) {
                      showError(userFields.url,@json(trans('message.page_details.valid_url')),);
                      isValid = false;
                  }
              }

              // If validation fails, prevent form submission
              if (!isValid) {
                  console.log(3);
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
          ['name','publish','url','slug','content'].forEach(id => {

              document.getElementById(id).addEventListener('input', function () {
                  removeErrorMessage(this);

              });
          });



          function isValidURL(string) {
              try {
                  new URL(string);
                  return true;
              } catch (err) {
                  return false;
              }
          }
      });



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

