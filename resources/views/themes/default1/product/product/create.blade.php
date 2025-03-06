@extends('themes.default1.layouts.master')
@section('title')
    Create Product
@stop
@section('content-header')
    <div class="col-sm-6">
        <h1>Create New Product</h1>
    </div>
    <div class="col-sm-6">
        <ol class="breadcrumb float-sm-right">
            <li class="breadcrumb-item"><a href="{{url('/')}}"><i class="fa fa-dashboard"></i> Home</a></li>
            <li class="breadcrumb-item"><a href="{{url('products')}}"><i class="fa fa-dashboard"></i>Products</a></li>
            <li class="breadcrumb-item active">Create New Product</li>
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
                background-color: #1b1818 !important;}
        </style>

    </head>
    <div class="card card-secondary card-tabs">
        {!! html()->form('POST', url('products'))->acceptsFiles()->id('createproduct')->open() !!}

        <div class="card-header p-0 pt-1">
            <ul class="nav nav-tabs" id="custom-tabs-one-tab" role="tablist">
                <li class="nav-item">
                    <a class="nav-link active" id="custom-tabs-detail-tab" data-toggle="pill" href="#custom-tabs-detail" role="tab" aria-controls="custom-tabs-detail" aria-selected="true">Details</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" id="custom-tabs-plan-tab" data-toggle="pill" href="#custom-tabs-plan" role="tab" aria-controls="custom-tabs-plan" aria-selected="false">Tax</a>
                </li>
            </ul>

        </div>

        <div class="card-body table-responsive">





            <div class="tab-content" id="custom-tabs-one-tabContent">
                <div class="tab-pane fade show active" id="custom-tabs-detail" Role="tabpanel" aria-labelledby="custom-tabs-detail-tab">
                    <div class="row">

                        <div class="col-md-4 form-group {{ $errors->has('name') ? 'has-error' : '' }}">
                            <!-- first name -->
                            {!! html()->label(Lang::get('message.name'), 'name')->class('required') !!}
                            {!! html()->text('name')->class('form-control'.($errors->has('name') ? ' is-invalid' : ''))->id('productname') !!}
                            @error('name')
                            <span class="error-message"> {{$message}}</span>
                            @enderror
                            <h6 id = "namecheck"></h6>

                        </div>

                        <div class="col-md-4 form-group {{ $errors->has('type') ? 'has-error' : '' }}">
                            <!-- last name -->
                            {!! html()->label(Lang::get('message.lic_type'), 'type')->class('required') !!}
                            {!! html()->select('type', ['' => 'Choose', 'Types' => $type])->class('form-control'.($errors->has('type') ? ' is-invalid' : ''))->id('type') !!}
                            <div class="input-group-append"></div>
                            @error('type')
                            <span class="error-message"> {{$message}}</span>
                            @enderror
                        </div>


                        <div class="col-md-4 form-group {{ $errors->has('group') ? 'has-error' : '' }}">
                            <!-- last name -->
                            {!! html()->label(Lang::get('message.group'), 'group')->class('required') !!}
                            <select name="group" value= "Choose" class="form-control {{$errors->has('group') ? ' is-invalid' : ''}}" id="groups">
                                <option value="">Choose</option>
                                @foreach($group as $key=>$value)
                                    @if (Request::old('group') == $key)
                                        <option value={{$key}} selected>{{$value}}</option>
                                    @else
                                        <option value={{$key}}>{{$value}}</option>
                                    @endif
                                @endforeach
                            </select>
                            <div class="input-group-append"></div>
                            @error('group')
                            <span class="error-message"> {{$message}}</span>
                            @enderror
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
                                        {title: 'Test template 1', content: 'Test 1'},
                                        {title: 'Test template 2', content: 'Test 2'}
                                    ],
                                    content_css: [
                                        '//fast.fonts.net/cssapi/e6dc9b99-64fe-4292-ad98-6974f93cd2a2.css',
                                        '//www.tinymce.com/css/codepen.min.css'
                                    ]
                                });
                            </script>

                            {!! html()->label(trans('message.price_description'), 'price_description')->class('required') !!}
                            {!! html()->textarea('description')->class('form-control'.($errors->has('description') ? ' is-invalid' : ''))->id('textarea1') !!}
                            <h6 id= "descheck"></h6>
                            @error('description')
                            <span class="error-message"> {{$message}}</span>
                            @enderror
                        </div>
                        <div class="col-md-6">




                            <ul class="list-unstyled">
                                <li>
                                    <div class="form-group {{ $errors->has('parent') ? 'has-error' : '' }}">
                                        <!-- last name -->
                                        {!! html()->label(trans('message.sku'), 'sku')->class('required') !!}
                                        {!! html()->text('product_sku')->class('form-control editor_1'.($errors->has('product_sku') ? ' is-invalid' : ''))->id('product_sku') !!}
                                        <div class="input-group-append"></div>
                                        @error('product_sku')
                                        <span class="error-message"> {{$message}}</span>
                                        @enderror
                                    </div>
                                </li>

                                <li>
                                    <div class="form-group {{ $errors->has('parent') ? 'has-error' : '' }}">
                                        <!-- last name -->
                                        {!! html()->label(trans('message.parent'), 'parent') !!}
                                        {!! html()->select('parent[]', ['' => 'Choose', 'Products' => $products])->class('form-control'.($errors->has('parent[]') ? ' is-invalid' : '')) !!}

                                    </div>
                                </li>
                                <li>
                                    <div class="form-group {{ $errors->has('image') ? 'has-error' : '' }}">
                                        <!-- last name -->
                                        {!! html()->label(trans('message.image'), 'image') !!}
                                        <div>
                                            {!! html()->file('image')->id('image') !!}
                                            <span class="system-error text-danger d-block mt-1" id="profilepic-err-Msg"></span>
                                        @error('image')
                                        <span class="error-message"> {{$message}}</span>
                                        @enderror
                                        </div>
                                    </div>
                                </li>

                                <li>
                                    <div class="form-group {{ $errors->has('require_domain') ? 'has-error' : '' }}">
                                        <!-- last name -->
                                        {!! html()->label(trans('message.require_domain'), 'require_domain') !!}
                                        <p>{!! html()->checkbox('require_domain',false, 1) !!} {{Lang::get('message.tick-to-show-domain-registration-options')}}</p>

                                    </div>
                                </li>

                                <li>
                                    <div class="form-group {{ $errors->has('shoping_cart_link') ? 'has-error' : '' }}">
                                        <!-- last name -->
                                        {!! html()->label(trans('message.shoping-cart-link'), 'shoping_cart_link') !!}
                                        {!! html()->text('shoping_cart_link', $cartUrl)->class('form-control') !!}
                                        @error('shoping_cart_link')
                                        <span class="error-message"> {{$message}}</span>
                                        @enderror
                                    </div>
                                </li>

                                <li>
                                    <div class="form-group {{ $errors->has('hidden') ? 'has-error' : '' }}">
                                        <!-- first name -->
                                        <!--  <button type="button" class="" data-toggle="tooltip" data-placement="top" title="Tooltip on top"></button> -->
                                        <label data-toggle="tooltip" data-placement="top" title="">Hidden</label>

                                        <p>{!! html()->checkbox('hidden', false ,1) !!}  {{Lang::get('message.tick-to-hide-from-order-form')}}</p>

                                    </div>
                                </li>
                                <li>
                                    <div class="form-group {{ $errors->has('highlight') ? 'has-error' : '' }}">
                                        <!-- first name -->
                                        <!--  <button type="button" class="" data-toggle="tooltip" data-placement="top" title="Tooltip on top"></button> -->
                                        <label data-toggle="tooltip" data-placement="top" title="">Highlight</label>

                                        <p>{!! html()->checkbox('highlight', false) !!}  {{Lang::get('message.tick-to-highlight-product')}}</p>

                                    </div>
                                </li>
                                <li>
                                    <div class="form-group {{ $errors->has('add_to_contact') ? 'has-error' : '' }}">
                                        <!-- first name -->
                                        <!--  <button type="button" class="" data-toggle="tooltip" data-placement="top" title="Tooltip on top"></button> -->
                                        <label data-toggle="tooltip" data-placement="top" title="">Contact to sales</label>

                                        <p>{!! html()->checkbox('add_to_contact', false) !!}  {{Lang::get('message.tick-to-add_to_contact-product')}}</p>

                                    </div>
                                </li>
                            </ul>

                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-12 form-group {{ $errors->has('product_description') ? 'has-error' : '' }}">
                            <!-- last name -->
{{--                            <script src="https://cdn.tiny.cloud/1/oiio010oipuw2n6qyq3li1h993tyg25lu28kgt1trxnjczpn/tinymce/5/tinymce.min.js" referrerpolicy="origin"></script>--}}
                            <script src="https://cdn.tiny.cloud/1/vj36xscbbzlnzmp9xo0kjctxdwdwfdll1rna0h0131am535t/tinymce/7/tinymce.min.js" referrerpolicy="origin"></script>

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
                                        setup: function (editor) {
                                            $('#submit').on('click', function () {
                                                let editorContainer = editor.getContainer();
                                                if (editor.getContent({ format: 'text' }).length <1) {
                                                    editorContainer.style.border = "1px solid #dc3545";
                                                } else {
                                                    editorContainer.style.border = '1px solid silver';
                                                }
                                            });
                                        }
                                });
                            </script>


                            {!! html()->label(trans('message.product_description'), 'product-description')->class('required') !!}
                            {!! html()->textarea('product_description')->class('form-control'.($errors->has('product_description') ? ' is-invalid' : ''))->id('textarea2') !!}
                            <h6 id= "descheck"></h6>
                            @error('product_description')
                            <span class="error-message"> {{$message}}</span>
                            @enderror
                        </div>
                    </div>


                </div>
                <!-- /.tab-pane -->
                <div class="tab-pane fade" id="custom-tabs-plan" role="tabpanel"  aria-labelledby="custom-tabs-plan-tab">

                    <table class="table">
                        <span class='required'>Show on Cart Page</span>
                        <tr>
                            <div class="row">
                                <td>
                                    <div>
                                        <label>
                                            {!! html()->radio('show_agent',false, 1)->id('agent') !!}
                                            <!-- <input type ="radio" id="agent" value="1" name="cartquantity">   -->
                                            {!! html()->hidden('can_modify_agent', 0) !!}
                                            <!-- <input type ="radio" id="agent" value="0" name="cartquantity" hidden>   -->
                                            Agents
                                        </label>
                                    </div>
                                    <br/>
                                    <div class="col-md-10" id="allowmulagent" style="display:none">
                                        <p>
                                            {!! html()->checkbox('can_modify_agent', false, 1)->id('can_modify_agent') !!}
                                            {{ Lang::get('message.allow_multiple_agents_quantity') }}
                                        </p>
                                    </div>
                                </td>
                            </div>
                        </tr>
                        <tr>
                            <td><label>
                                    {!! html()->radio('show_agent', 0)->id('quantity') !!}
                                    <!-- <input type="radio" id="quantity" value="0" name="cartquantity"> -->
                                    {!! html()->hidden('can_modify_quantity', 0) !!}
                                    Product Quantity
                                </label>
                                <br/>
                                <div class="col-md-10" id="allowmulproduct" style="display:none">
                                    <p>{!! html()->checkbox('can_modify_quantity',false, 1) !!}  {{Lang::get('message.allow_multiple_product_quantity')}} </p>
                                </div>

                            </td>
                        </tr>

                    </table>
                    <span id="error-message"></span><br/><br/>

                    <tr>
                        <td><b>{!! html()->label(trans('message.taxes'), 'tax') !!}</b></td>
                        <td>
                            <div class="form-group {{ $errors->has('taxes') ? 'has-error' : '' }}">
                                <div class="row">
                                    <div class="col-md-2" >

                                        <select id="Tax" placeholder="Select Taxes" name="tax[]" style="width:500px;" class="select2 " multiple="multiple">
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

                    {!! html()->closeModelForm() !!}

                </div>
                <button type="submit" class="btn btn-primary pull-right" id="submit" data-loading-text="<i class='fa fa-circle-o-notch fa-spin'>&nbsp;</i> Saving..."><i class="fa fa-save">&nbsp;&nbsp;</i>{!!Lang::get('message.save')!!}</button>
            </div>
        </div>
    </div>
        <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
        <script>
            $(document).ready(function() {
                var fup = document.getElementById('image');
                var errMsg = document.getElementById('profilepic-err-Msg');
                $('#image').on('change', function (e) {
                    var fileName = fup.value;
                    var filesize = e.target.files[0];
                    var ext = fileName.substring(fileName.lastIndexOf('.') + 1);
                    const maxSize = 2 * 1024 * 1024;
                    if (filesize.size > maxSize) {
                        errMsg.innerText =@json(trans('message.image_invalid_size'));
                        document.getElementById('submit').disabled = true;
                        return false;
                    }
                    if (ext !== "jpeg" && ext !== "jpg" && ext !== 'png') {
                        errMsg.innerText =@json(trans('message.image_invalid_message'));
                        document.getElementById('submit').disabled = true;
                        return false;
                    } else if (filesize.size > maxSize) {
                        errMsg.innerText =@json(trans('message.image_invalid_size'));
                        document.getElementById('submit').disabled = true;
                        return false;
                    } else {
                        errMsg.innerText = '';
                        document.getElementById('submit').disabled = false;
                        return true;
                    }
                });
            });
            $(document).ready(function() {
                tinymce.get('textarea1').on('change', function() {
                    let content = tinymce.get('textarea1').getContent();
                    if(content !==''){
                        let editorContainer = document.querySelector(".tox-tinymce");
                        editorContainer.style.border = "1px solid silver";
                        removeErrorMessage(document.getElementById('textarea1'));
                    }
                });

                tinymce.get('textarea2').on('change', function() {
                    let content = tinymce.get('textarea2').getContent();
                    if(content !==''){
                        let editorContainer = document.querySelector(".tox-tinymce");
                        editorContainer.style.border = "1px solid silver";
                        removeErrorMessage(document.getElementById('textarea2'));
                    }
                });


                const userRequiredFields = {
                    name:@json(trans('message.product_details.add_name')),
                    type:@json(trans('message.product_details.add_license_type')),
                    group:@json(trans('message.product_details.add_group')),
                    product_sku:@json(trans('message.product_details.add_product_sku')),
                    description:@json(trans('message.product_details.add_description')),
                    agent:@json(trans('message.product_details.add_description')),
                    quantity:@json(trans('message.product_details.add_description')),
                    productdes:@json(trans('message.product_details.add_product_description')),

                };

                $('#createproduct').on('submit', function (e) {
                    if ($('#textarea1').val() === '') {
                        let editorContainer = document.querySelector(".tox-tinymce");
                        editorContainer.style.border = "1px solid #dc3545";
                    }
                    else if($('#textarea1').val() !== ''){
                        let editorContainer = document.querySelector(".tox-tinymce");
                        editorContainer.style.border = "1px solid silver";
                    }else{
                        let editorContainer = document.querySelector(".tox-tinymce");
                        editorContainer.style.border = "1px solid silver";
                    }

                    const userFields = {
                        name:$('#productname'),
                        type:$('#type'),
                        group:$('#groups'),
                        product_sku:$('#product_sku'),
                        description:$('#textarea2'),
                        productdes:$('#textarea1'),

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



                    if(isValid && !document.querySelector('input[name="show_agent"]:checked')){
                        Swal.fire({
                            title: 'Incomplete Tax Details',
                            text: 'Mandatory fields missing in Tax screen.',
                            icon: 'info',
                            confirmButtonColor: '#286090'
                        });
                        //alert('Please check the tax page as well.')

                        isValid=false;
                    }else{

                        document.getElementById("error-message").textContent = "";

                    }

                    if(!document.querySelector('input[name="show_agent"]:checked')){
                        $('#error-message').css({"color": "#dc3545", "margin-top": "5px", "font-size": "80%"});
                        document.getElementById("error-message").textContent = "Please enter type of cart page";
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
                ['productname','type','groups','product_sku','agent','quantity','textarea12'].forEach(id => {

                    document.getElementById(id).addEventListener('input', function () {
                        removeErrorMessage(this);
                        document.querySelector(".error-message").remove();
                    });
                });

            });

            $(document).ready(function() {
                $("#Tax").select2({
                    placeholder: 'Select Taxes',
                    tags:true
                });
            });

            $('ul.nav-sidebar a').filter(function() {
                return this.id == 'add_product';
            }).addClass('active');

            // for treeview
            $('ul.nav-treeview a').filter(function() {
                return this.id == 'add_product';
            }).parentsUntil(".nav-sidebar > .nav-treeview").addClass('menu-open').prev('a').addClass('active');
        </script>


@stop
