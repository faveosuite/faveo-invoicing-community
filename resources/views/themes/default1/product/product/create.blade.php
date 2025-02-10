@extends('themes.default1.layouts.master')
@section('title')
Create Product
@stop
@section('content-header')
    <div class="col-sm-6">
        <h1>{{ __('message.create_new_product') }}</h1>
    </div>
    <div class="col-sm-6">
        <ol class="breadcrumb float-sm-right">
            <li class="breadcrumb-item"><a href="{{url('/')}}"><i class="fa fa-dashboard"></i> {{ __('message.home') }}</a></li>
            <li class="breadcrumb-item"><a href="{{url('products')}}"><i class="fa fa-dashboard"></i>{{ __('message.products') }}</a></li>
            <li class="breadcrumb-item active">{{ __('message.create_new_product') }}</li>
        </ol>
    </div><!-- /.col -->
@stop
@section('content')
<head>
    <link rel="stylesheet" href="{{asset('admin/css/select2.min.css')}}">

    <script>
        $(function() {
            $('#agent').click(function(){
                if($('#agent').is(":checked")) {
                    $("#allowmulagent").show();
                    $("#allowmulproduct").hide();
                }
            })

        })

        $(function() {
            $('#quantity').click(function(){
                if($('#quantity').is(":checked")) {
                    $("#allowmulagent").hide();
                    $("#allowmulproduct").show();
                }
            })

        })
 </script>
    <style>
        .select2-container--default .select2-selection--multiple .select2-selection__choice {
            background-color: #1b1818 !important;
    </style>

</head>
<div class="card card-secondary card-tabs">
    {!! Form::open(['url'=>'products','method'=>'post','files' => true,'id'=>'createproduct']) !!}

    <div class="card-header p-0 pt-1">
        <ul class="nav nav-tabs" id="custom-tabs-one-tab" role="tablist">
            <li class="nav-item">
                <a class="nav-link active" id="custom-tabs-detail-tab" data-toggle="pill" href="#custom-tabs-detail" role="tab" aria-controls="custom-tabs-detail" aria-selected="true">{{ __('message.details') }}</a>
            </li>
            <li class="nav-item">
                <a class="nav-link" id="custom-tabs-plan-tab" data-toggle="pill" href="#custom-tabs-plan" role="tab" aria-controls="custom-tabs-plan" aria-selected="false">{{ __('message.tax') }}</a>
            </li>
        </ul>

    </div>

    <div class="card-body table-responsive">





                    <div class="tab-content" id="custom-tabs-one-tabContent">
                        <div class="tab-pane fade show active" id="custom-tabs-detail" Role="tabpanel" aria-labelledby="custom-tabs-detail-tab">
                            <div class="row">

                                <div class="col-md-4 form-group {{ $errors->has('name') ? 'has-error' : '' }}">
                                    <!-- first name -->
                                    {!! Form::label('name',Lang::get('message.name'),['class'=>'required']) !!}
                                    {!! Form::text('name',null,['class' => 'form-control', 'id' =>'productname']) !!}
                                <h6 id = "namecheck"></h6>

                                </div>

                                 <div class="col-md-4 form-group {{ $errors->has('type') ? 'has-error' : '' }}">
                                    <!-- last name -->
                                    {!! Form::label('type',Lang::get('message.lic_type'),['class'=>'required']) !!}
                                    {!! Form::select('type',[''=>'Choose','Types'=>$type],null,['class' => 'form-control']) !!}

                                </div>


                                <div class="col-md-4 form-group {{ $errors->has('group') ? 'has-error' : '' }}">
                                    <!-- last name -->
                                    {!! Form::label('group',Lang::get('message.group'),['class'=>'required']) !!}
                          <select name="group" value= "Choose" class="form-control">
                             <option value="">{{ __('message.choose') }}</option>
                           @foreach($group as $key=>$value)
                               @if (Request::old('group') == $key)
                             <option value={{$key}} selected>{{$value}}</option>
                             @else
                             <option value={{$key}}>{{$value}}</option>
                             @endif
                          @endforeach
                          </select>

                                </div>


                            </div>

                            <div class="row">

                                <div class="col-md-6 form-group {{ $errors->has('price_description') ? 'has-error' : '' }}">
                                    <!-- last name -->
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
                                            {title: '{{ __('message.test_template_one') }}', content: 'Test 1'},
                                            {title: '{{ __('message.test_template_two') }}', content: 'Test 2'}
                                        ],
                                          content_css: [
                                              '//fast.fonts.net/cssapi/e6dc9b99-64fe-4292-ad98-6974f93cd2a2.css',
                                              '//www.tinymce.com/css/codepen.min.css'
                                          ]
                                    });
                                    </script>

                                    {!! Form::label('price_description', trans('message.price_description'), ['class' => 'required']) !!}
                                    {!! Form::textarea('description', null, ['class' => 'form-control', 'id' => 'textarea']) !!}
                                <h6 id= "descheck"></h6>
                                </div>
                                <div class="col-md-6">




                                    <ul class="list-unstyled">
                                          <li>
                                            <div class="form-group {{ $errors->has('parent') ? 'has-error' : '' }}">
                                                <!-- last name -->
                                                {!! Form::label('sku',Lang::get('message.sku'),['class'=>'required']) !!}
                                                {!! Form::text('product_sku',null,['class' => 'form-control']) !!}

                                            </div>
                                        </li>

                                        <li>
                                            <div class="form-group {{ $errors->has('parent') ? 'has-error' : '' }}">
                                                <!-- last name -->
                                                {!! Form::label('parent',Lang::get('message.parent')) !!}
                                                {!! Form::select('parent[]',[''=>'Choose','Products'=>$products],null,['class' => 'form-control']) !!}

                                            </div>
                                        </li>
                                        <li>
                                        <div class="form-group {{ $errors->has('image') ? 'has-error' : '' }}">
                                            <!-- last name -->
                                            {!! Form::label('image',Lang::get('message.image')) !!}
                                            {!! Form::file('image') !!}

                                        </div>
                                        </li>

                                        <li>
                                            <div class="form-group {{ $errors->has('require_domain') ? 'has-error' : '' }}">
                                                <!-- last name -->
                                                {!! Form::label('require_domain',Lang::get('message.require_domain')) !!}
                                                <p>{!! Form::checkbox('require_domain',1) !!} {{Lang::get('message.tick-to-show-domain-registration-options')}}</p>

                                            </div>
                                        </li>

                                        <li>
                                            <div class="form-group {{ $errors->has('shoping_cart_link') ? 'has-error' : '' }}">
                                                <!-- last name -->
                                                {!! Form::label('shoping_cart_link',Lang::get('message.shoping-cart-link')) !!}
                                                {!! Form::text('shoping_cart_link',$cartUrl,['class'=>'form-control']) !!}

                                            </div>
                                        </li>

                                         <li>
                                            <div class="form-group {{ $errors->has('hidden') ? 'has-error' : '' }}">
                                                <!-- first name -->
                                               <!--  <button type="button" class="" data-toggle="tooltip" data-placement="top" title="Tooltip on top"></button> -->
                                                <label data-toggle="tooltip" data-placement="top" title="">Hidden</label>

                                                <p>{!! Form::checkbox('hidden',1) !!}  {{Lang::get('message.tick-to-hide-from-order-form')}}</p>

                                            </div>
                                        </li>
                                         <li>
                                            <div class="form-group {{ $errors->has('highlight') ? 'has-error' : '' }}">
                                                <!-- first name -->
                                               <!--  <button type="button" class="" data-toggle="tooltip" data-placement="top" title="Tooltip on top"></button> -->
                                                <label data-toggle="tooltip" data-placement="top" title="">Highlight</label>

                                                <p>{!! Form::checkbox('highlight') !!}  {{Lang::get('message.tick-to-highlight-product')}}</p>

                                            </div>
                                        </li>
                                          <li>
                                            <div class="form-group {{ $errors->has('add_to_contact') ? 'has-error' : '' }}">
                                                <!-- first name -->
                                               <!--  <button type="button" class="" data-toggle="tooltip" data-placement="top" title="Tooltip on top"></button> -->
                                                <label data-toggle="tooltip" data-placement="top" title="">Contact to sales</label>

                                                <p>{!! Form::checkbox('add_to_contact') !!}  {{Lang::get('message.tick-to-add_to_contact-product')}}</p>

                                            </div>
                                        </li>
                                    </ul>

                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-12 form-group {{ $errors->has('product_description') ? 'has-error' : '' }}">
                                    <!-- last name -->
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


                                    {!! Form::label('product_description', trans('message.product_description'), ['class' => 'required']) !!}
                                    {!! Form::textarea('product_description', null, ['class' => 'form-control', 'id' => 'product-description']) !!}
                                    <h6 id= "descheck"></h6>
                                </div>
                            </div>


                        </div>
                        <!-- /.tab-pane -->
                        <div class="tab-pane fade" id="custom-tabs-plan" role="tabpanel"  aria-labelledby="custom-tabs-plan-tab">

                        <table class="table">
                                <span class='required'>{{ __('message.show_cart_page') }}</span>
                                 <tr>
                                      <div class="row">
                                    <td>
                                        <div>
                                        <label>
                                            {!! Form::radio('show_agent',1,false,['id'=>'agent']) !!}
                                           <!-- <input type ="radio" id="agent" value="1" name="cartquantity">   -->
                                           {!! Form::hidden('can_modify_agent',0) !!}
                                            <!-- <input type ="radio" id="agent" value="0" name="cartquantity" hidden>   -->
                                            Agents
                                        </label>
                                        </div>
                                    <br/>
                                    <div class="col-md-10" id="allowmulagent" style="display:none">
                                       <p>{!! Form::checkbox('can_modify_agent',1) !!}  {{Lang::get('message.allow_multiple_agents_quantity')}} </p>
                                    </div>
                                   </td>
                                 </div>
                                </tr>
                                <tr>
                                    <td><label>
                                         {!! Form::radio('show_agent',0,false,['id'=>'quantity']) !!}
                                        <!-- <input type="radio" id="quantity" value="0" name="cartquantity"> -->
                                        {!! Form::hidden('can_modify_quantity',0) !!}
                                            Product Quantity
                                         </label>
                                         <br/>
                                     <div class="col-md-10" id="allowmulproduct" style="display:none">
                                       <p>{!! Form::checkbox('can_modify_quantity',1) !!}  {{Lang::get('message.allow_multiple_product_quantity')}} </p>
                                    </div>

                                    </td>
                                </tr>
                              </table>

                                <tr>
                                    <td><b>{!! Form::label('tax',Lang::get('message.taxes')) !!}</b></td>
                                    <td>
                                        <div class="form-group {{ $errors->has('taxes') ? 'has-error' : '' }}">
                                            <div class="row">
                                                <div class="col-md-2" >
<<<<<<< HEAD

                                                    <select id="Tax" placeholder="Select Taxes" name="tax[]" style="width:500px;" class="select2" multiple="multiple">
=======
                                                     
                                                    <select id="Tax" placeholder="{{ __('message.select_taxes') }}" name="tax[]" style="width:500px;" class="select2" multiple="multiple">
>>>>>>> d1d255d79 (fixes #3191)
                                                       <option></option>
                                                       @foreach($taxes as $key => $value)
                                                        <option value={{$key}}>{{$value}}</option>
                                                        @endforeach
                                                    </select>

                                                </div>
                                            </div>

                                        </div>
                                    </td>


                                </tr>
                                <tr>

                                </tr>



        {!! Form::close() !!}





                                    </div>
                        <button type="submit" class="btn btn-primary pull-right" id="submit" data-loading-text="<i class='fa fa-circle-o-notch fa-spin'>&nbsp;</i> {{ __('message.saving') }}"><i class="fa fa-save">&nbsp;&nbsp;</i>{!!Lang::get('message.save')!!}</button>
                    </div>
    </div>

    <script>
        $(document).ready(function() {
            $("#Tax").select2({
                placeholder: 'Select Taxes',
                tags:true
            });
        });
    </script>
    <script>
     $('ul.nav-sidebar a').filter(function() {
        return this.id == 'add_product';
    }).addClass('active');

    // for treeview
    $('ul.nav-treeview a').filter(function() {
        return this.id == 'add_product';
    }).parentsUntil(".nav-sidebar > .nav-treeview").addClass('menu-open').prev('a').addClass('active');
</script>
@stop

