@extends('themes.default1.layouts.master')
@section('title')
    {{ __('message.edit_product') }}
@stop
@section('content-header')
    <div class="col-sm-6">
        <h1>{{ __('message.edit_product') }}</h1>

    </div>
    <div class="col-sm-6">
        <ol class="breadcrumb float-sm-right">
            <li class="breadcrumb-item"><a href="{{url('/')}}"><i class="fa fa-dashboard"></i> {{ __('message.home') }}</a></li>
            <li class="breadcrumb-item"><a href="{{url('products')}}"><i class="fa fa-dashboard"></i> {{ __('message.products') }}</a></li>
            <li class="breadcrumb-item active">{{ __('message.edit_product') }}</li>
        </ol>
    </div><!-- /.col -->
@stop

@section('content')
    <style>
        .more-text{
            display:none;
        }

        .resumable-file-name,
        .resumable-file-progress {
            display: inline-block;
            width: 200px; /* Set desired fixed width */
            white-space: normal; /* Allow wrapping */
            word-wrap: break-word; /* Break long words if needed */
            vertical-align: top;
        }

    </style>

    <link rel="stylesheet" href="{{asset('admin/css/select2.min.css')}}">

    <script src="//ajax.googleapis.com/ajax/libs/jquery/1.9.1/jquery.min.js"></script>
    <script type="text/javascript" src="https://ajax.googleapis.com/ajax/libs/jquery/1.8.3/jquery.min.js"></script>

    <script type="text/javascript">
        $(function () {
            $("#chkYes").click(function () {
                if ($("#chkYes").is(":checked")) {
                    $("#git").show();
                    $("#uploads").hide();
                    $("#hide").hide();
                }
                else{
                    $("#git").hide();
                    $("#uploads").hide();
                    $("#hide").hide();
                }
            });
        });

        $(function () {
            $("#chkNo").click(function () {
                if ($("#chkNo").is(":checked")) {
                    $("#git").hide();
                    $("#uploads").show();
                    $("#hide").show();
                } else {
                    $("#git").hide();
                    $("#uploads").hide();
                    $("#uploads").hide();
                    $("#hide").hide();
                }
            });
        });

    </script>
    <style>
        .select2-container--default .select2-selection--multiple .select2-selection__choice {
            background-color: #1b1818 !important;}
    </style>
    <div class="card card-secondary card-tabs">

        {!! html()->modelForm($product, 'PATCH', url('products/'.$product->id))->acceptsFiles()->id('editproduct')->open() !!}
        <div class="card-header p-0 pt-1">
            <ul class="nav nav-tabs" id="custom-tabs-one-tab" role="tablist">
                <li class="nav-item">
                    <a class="nav-link active" id="custom-tabs-detail-tab" data-toggle="pill" href="#custom-tabs-detail" role="tab" aria-controls="custom-tabs-detail" aria-selected="true">{{ __('message.details') }}</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" id="custom-tabs-plan-tab" data-toggle="pill" href="#custom-tabs-plan" role="tab" aria-controls="custom-tabs-plan" aria-selected="false">{{ __('message.plans') }}</a>
                </li>
            </ul>
        </div>
        <div class="card-body table-responsive">

            <div class="tab-content" id="custom-tabs-one-tabContent">
                <div class="tab-pane fade show active" id="custom-tabs-detail" Role="tabpanel" aria-labelledby="custom-tabs-detail-tab">
                    <div class="row">
                        <div class="col-md-4 form-group {{ $errors->has('name') ? 'has-error' : '' }}">
                            <!-- first name -->
                            {!! html()->label(trans('message.name'), 'name')->class('required') !!}
                            {!! html()->text('name')->class( 'form-control'.($errors->has('name') ? ' is-invalid' : ''))->id('productname') !!}
                            <h6 id= "namecheck"></h6>
                            @error('name')
                            <span class="error-message"> {{$message}}</span>
                            @enderror
                        </div>

                        <div class="col-md-4 form-group {{ $errors->has('type') ? 'has-error' : '' }}">
                            <!-- last name -->
                            {!! html()->label(trans('message.lic_type'), 'type')->class('required') !!}
                            {!! html()->select('type', ['' => __('message.choose'), 'Types' => $type])->class('form-control'.($errors->has('type') ? ' is-invalid' : '')) !!}
                            <div class="input-group-append"></div>
                            @error('type')
                            <span class="error-message"> {{$message}}</span>
                            @enderror
                        </div>
                            <?php
                            $groups = DB::table('product_groups')->pluck('name', 'id')->toarray();
                            ?>
                        <div class="col-md-4 form-group {{ $errors->has('group') ? 'has-error' : '' }}">
                            <!-- last name -->
                            {!! html()->label(Lang::get('message.group'), 'group')->class('required') !!}
                            <select name="group"  class="form-control {{$errors->has('group') ? ' is-invalid' : ''}}" id="groups">
                                <option value="">{{ __('message.choose') }}</option>
                                @foreach($groups as $key=>$group)
                                    <option value="{{$key}}" <?php  if (in_array($group, $selectedGroup)) {
                                        echo "selected";
                                    } ?>>{{$group}}</option>

                                @endforeach
                            </select>
                            <div class="input-group-append"></div>
                            @error('group')
                            <span class="error-message"> {{$message}}</span>
                            @enderror
                        </div>



                    </div>

                    <div class="row">

                        <div class="col-md-6 form-group {{ $errors->has('description') ? 'has-error' : '' }}">

                            {!! html()->label(Lang::get('message.price_description'), 'description')->class('required') !!}
                            <!-- {!! html()->text('description')->class('form-control'.($errors->has('description') ? ' is-invalid' : ''))->id('textarea1') !!}-->
                            <textarea hidden class="form-control"  name="description" id='textarea1'>{!! $product->description !!}</textarea>

                            @error('description')
                            <span class="error-message"> {{$message}}</span>
                            @enderror
                            <h6 id= "descheck"></h6>

                            {!! html()->label(Lang::get('message.short_description'), 'short_description')->class('required') !!}
                            <!-- {!! html()->text('description')->class('form-control'.($errors->has('short_description') ? ' is-invalid' : ''))->id('textarea2') !!}-->
                            <textarea hidden class="form-control"  name="short_description" id='textarea2'>{!! $product->short_description !!}</textarea>

                            @error('short_description')
                            <span class="error-message"> {{$message}}</span>
                            @enderror
                            <h6 id= "descheck"></h6>

                        </div>


                        <div class="col-md-6">
                            <ul class="list-unstyled">
                                <li>
                                    <div class="form-group {{ $errors->has('parent') ? 'has-error' : '' }}">
                                        <!-- last name -->
                                        {!! html()->label(trans('message.sku'), 'sku')->class('required') !!}
                                        {!! html()->text('product_sku')->class('form-control'.($errors->has('product_sku') ? ' is-invalid' : ''))->id('product_sku') !!}
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
                                        {!! html()->select('parent[]', ['' => __('message.choose'), 'Products' => $products])->class('form-control'.($errors->has('parent[]') ? ' is-invalid' : '')) !!}

                                    </div>
                                </li>

                                <li>
                                    <div class="form-group {{ $errors->has('image') ? 'has-error' : '' }}">
                                        <!-- last name -->
                                        {!! html()->label(trans('message.image'), 'image') !!}
                                        {!! html()->file('image')->id('image') !!}
                                        <span class="system-error text-danger d-block mt-1" id="profilepic-err-Msg"></span>
                                        <br>
                                        @if($product->image)
                                            <img src="{{$product->image }}" width="100px" alt="slider Image">
                                        @endif

                                    </div>
                                </li>


                                <table class="table">
                                    <input type="hidden" value="{{$checkowner}}" id="checkowner">
                                    <span>{{ __('message.where_retrieve_files') }}</span>
                                    </br>
                                    <input type="hidden" value="{{$githubStatus}}" id="gitstatus">
                                    @if($githubStatus==1)
                                        <tr>

                                            <td>

                                                <label for="chkYes" style="">
                                                    <input type="radio" id="chkYes" name="chkTax" />
                                                    {{ __('message.github') }}
                                                </label>

                                                <div class="col-md-10 gitstatus" id="git" style="display:none">
                                                    <li>
                                                        <div class="form-group {{ $errors->has('github_owner') ? 'has-error' : '' }}">
                                                            <!-- first name -->
                                                            {!! html()->label(trans('message.github-owner'), 'github_owner') !!}
                                                            {!! html()->text('github_owner')->class('form-control'.($errors->has('github_owner') ? ' is-invalid' : ''))->id('owner') !!}
                                                            @error('github_owner')
                                                            <span class="error-message"> {{$message}}</span>
                                                            @enderror
                                                        </div>
                                                        <div class="form-group {{ $errors->has('github_repository') ? 'has-error' : '' }}">
                                                            <!-- last name -->
                                                            {!! html()->label(trans('message.github-repository-name'), 'github_repository') !!}
                                                            {!! html()->text('github_repository')->class('form-control'.($errors->has('github_repository') ? ' is-invalid' : ''))->id('repo') !!}
                                                            @error('github_repository')
                                                            <span class="error-message"> {{$message}}</span>
                                                            @enderror

                                                        </div>
                                                    </li>

                                                    <li>
                                                        <div class="form-group {{ $errors->has('version') ? 'has-error' : '' }}">
                                                            <!-- last name -->
                                                            {!! html()->label(trans('message.version'), 'version') !!}
                                                            {!! html()->text('version')->class('form-control'.($errors->has('version') ? ' is-invalid' : ''))->id('version') !!}
                                                            @error('version')
                                                            <span class="error-message"> {{$message}}</span>
                                                            @enderror

                                                        </div>
                                                    </li>
                                                </div>
                                            </td>
                                        </tr>
                                    @endif
                                    <tr>
                                        <td><label for="chkNo">
                                                <input type="radio" id="chkNo" name="chkTax" />
                                                {{ __('message.filesystem') }}
                                            </label>
                                        </td>
                                    </tr>
                                </table>

                                <li>
                                    <div class="form-group {{ $errors->has('require_domain') ? 'has-error' : '' }}">
                                        <!-- last name -->
                                        {!! html()->label(trans('message.require_domain'), 'require_domain') !!}
                                        {!! html()->hidden('require_domain', 0) !!}
                                        <p>{!! html()->checkbox('require_domain', null ,1) !!} {{Lang::get('message.tick-to-show-domain-registration-options')}}</p>

                                    </div>
                                </li>
                                <li>
                                    <div class="form-group {{ $errors->has('shoping_cart_link') ? 'has-error' : '' }}">
                                        <!-- last name -->
                                        {!! html()->label(trans('message.shoping-cart-link'), 'shoping_cart_link') !!}
                                        {!! html()->text('shoping_cart_link', null)->class('form-control') !!}

                                    </div>
                                </li>
                                <li>
                                    <div class="row">

                                        <div class="form-group {{ $errors->has('hidden') ? 'has-error' : '' }}">
                                            <!-- first name -->
                                            {!! html()->label(trans('message.hidden'), 'hidden') !!}
                                            {!! html()->hidden('hidden', 0) !!}
                                            {!! html()->hidden('invoice_hidden', 0) !!}
                                                <?php
                                                $value=  "";
                                                $value1="";
                                                if ($product->hidden==1) {
                                                    $value = 'true';
                                                }
                                                if($product->invoice_hidden==1){
                                                    $value1=1;
                                                }
                                                ?>
                                            <p>{!! html()->checkbox('hidden', $value, 1) !!}  {{Lang::get('message.tick-to-hide-from-order-form')}}</p>
                                            <p>{!! html()->checkbox('invoice_hidden', $value1 ,1) !!}  {{Lang::get('message.tick-to-hide-from-invoice')}}</p>
                                        </div>

                                    </div>
                                </li>
                                <li>
                                    <div class="row">

                                        <div class="form-group {{ $errors->has('highlight') ? 'has-error' : '' }}">
                                            <!-- first name -->
                                            {!! html()->label(trans('message.highlight'))->for('highlight') !!}
                                            {!! html()->hidden('highlight', 0) !!}
                                                <?php
                                                $value=  "";
                                                if ($product->highlight==1) {
                                                    $value = 'true';
                                                }
                                                ?>
                                            <p>{!! html()->checkbox('highlight', $value) !!}  {{Lang::get('message.tick-to-highlight-product')}}</p>

                                        </div>

                                    </div>
                                </li>

                                <li>
                                    <div class="row">

                                        <div class="form-group {{ $errors->has('add_to_contact') ? 'has-error' : '' }}">
                                            <!-- first name -->
                                            {!! html()->label(trans('message.contact_to_sales'))->for('add_to_contact') !!}
                                            {!! html()->hidden('add_to_contact', 0) !!}
                                                <?php
                                                $value=  "";
                                                if ($product->add_to_contact==1) {
                                                    $value = 'true';
                                                }
                                                ?>
                                            <p>{!! html()->checkbox('add_to_contact', $value, 1) !!} {{Lang::get('message.tick-to-add_to_contact-product')}}</p>

                                        </div>

                                    </div>
                                </li>
                            </ul>
                        </div>

                    </div>

                    <div class="row">

                        <div class="col-md-12 form-group {{ $errors->has('product_description') ? 'has-error' : '' }}">
                            {!! html()->label(Lang::get('message.product_description'))->class('required') !!}
                            {!! html()->textarea('product_description', $product->product_description)->class('form-control'.($errors->has('product_description') ? ' is-invalid' : ''))->id('product-description') !!}
                            <div class="input-group-append"></div>
                            @error('product_description')
                            <span class="error-message"> {{$message}}</span>
                            @enderror
                            <h6 id="descheck"></h6>
                        </div>
                    </div>


                </div>

                <!-- /.tab-pane -->
                <div class="tab-pane fade" id="custom-tabs-plan" role="tabpanel"  aria-labelledby="custom-tabs-plan-tab">
                    <table class="table">

                        <span class="required">{{ __('message.show_cart_page') }}</span>
                        <tr>
                            <div class="row">
                                <td>

                                    <div><label>
                                            {!! html()->radio('show_agent', true, 1)->id('agent') !!}
                                            <!-- <input type ="radio" id="agent" value="0" name="cartquantity" hidden>   -->
                                            {{ __('message.agents') }}
                                        </label></div>

                                    <br/>
                                    <div class="col-md-10" id="allowmulagent" style="display:none">
                                        <p>{!! html()->checkbox('can_modify_agent', $canModifyAgent)->id('agent_multiple_quantity') !!} {{Lang::get('message.allow_multiple_agents_quantity')}} </p>
                                    </div>

                                </td>
                            </div>
                        </tr>
                        <tr>
                            <td>
                                <div>
                                    <label>
                                        {!! html()->radio('show_agent', false, 0)->id('quantity') !!}
                                        {{ __('message.product_quantity') }}
                                    </label>
                                </div>
                                <br/>
                                <div class="col-md-10" id="allowmulproduct" style="display:none">
                                    <p>{!! html()->checkbox('can_modify_quantity', $canModifyQuantity)->id('product_multiple_quantity') !!}  {{Lang::get('message.allow_multiple_product_quantity')}} </p>
                                </div>

                            </td>
                        </tr>
                    </table>

                    <tr>
                        <td><b>{!! html()->label(trans('message.taxes'), 'tax') !!}</b></td>
                        <td>
                            <div class="form-group {{ $errors->has('taxes') ? 'has-error' : '' }}">
                                <div class="row">

                                    <div class="col-md-2">
                                        <select id="editTax" placeholder="{{ __('message.select_taxes') }}" name="tax[]" style="width:500px;" class="select2" multiple="true">

                                            @foreach($taxes as $value)
                                                <option value={{$value['id']}} <?php echo (in_array($value['id'], $savedTaxes)) ?  "selected" : "" ;  ?>>{{$value['name'].'('.$value['name'].')'}}</option>

                                            @endforeach
                                        </select>



                                    </div>


                                </div>

                            </div>
                        </td>
                    </tr>



                    <br>
                    <h3>  {{ __('message.plans') }} &nbsp;
                        <!-- <a href="#create-plan-option" data-toggle="modal" data-target="#create-plan-option" class="btn btn-default">Add new</a> -->
                    </h3>

                    @if($product->plan())
                        <table class="table">

                            <tr>
                                <th>{{ __('message.name_page') }}</th>
                                <th>{{ __('message.months') }}</th>
                                <th>{{ __('message.action') }}</th>
                            </tr>
                            @foreach($product->plan()->where('product',$product->id)->get() as $plan)
                                <tr>

                                    <td>{{$plan->name}}</td>
                                        <?php
                                        if ($plan->days != '') {
                                            $months = $plan->days / 30;
                                        } else {
                                            $months = 'No Period Selected';
                                        }

                                        ?>
                                    <td>{{round((int) $months)}}</td>
                                    <td><a href="{{url('plans/'.$plan->id.'/edit')}}" class="btn btn-secondary btn-xs"{!! tooltip('Edit') !!}<i class='fa fa-edit' style='color:white;'></i></a></td>
                                </tr>
                            @endforeach
                        </table>
                    @else
                        <td>{{ __('message.no_plans_created') }}</td>
                    @endif

                </div>


            </div>
            <button type="submit" class="btn btn-primary pull-right" id="submit" data-loading-text="<i class='fa fa-circle-o-notch fa-spin'>&nbsp;</i> {{ __('message.saving') }}"><i class="fas fa-sync-alt">&nbsp;</i>{!!Lang::get('message.update')!!}</button>

            {!! html()->form()->close() !!}

            <!-- nav-tabs-custom -->




        </div>





    </div>
    </div>

    <div class="row" id="hide" style="display:none">
        <div class="col-md-12">
            <div class="card card-secondary card-outline" id="uploads">
                <div class="card-header">
                    <h3 class="card-title">{{ __('message.upload_files') }}</h3>

                    <div class="card-tools">
                        <a href="#create-upload-option" id="create" class="btn btn-default  btn-sm pull-right" data-toggle="modal" data-target="#create-upload-option"><span class="fa fa-plus"></span>&nbsp;&nbsp;{{Lang::get('message.add-file')}}</a>
                        @include('themes.default1.product.product.create-upload-option')

                    </div>
                </div>

                <div id="response"></div>
                <div id="product-alert-container"></div>
                <div class="card-body table-responsive">
                    <div class="row" >
                        <div class="col-md-12" >
                            <table id="upload-table" class="table display" cellspacing="0" width="100%" styleClass="borderless">
                                <button  value="" class="btn btn-secondary btn-sm btn-alldell" id="bulk_delete"><i class="fa fa-trash"></i>&nbsp;&nbsp; {{Lang::get('message.delmultiple')}}</button><br /><br />
                                <thead><tr>
                                    <th class="no-sort"><input type="checkbox" name="select_all" onchange="checking(this)"></th>
                                    <th>{{ __('message.title') }}</th>
                                    <th style="width:210px;">{{ __('message.description') }}</th>
                                    <th>{{ __('message.version') }}</th>
                                    <th>{{ __('message.release_type') }}</th>
                                    <th>{{ __('message.file') }}</th>
                                    <th>{{ __('message.action') }}</th>
                                </tr></thead>
                            </table>

                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Delete Confirmation Modal -->
    <div class="modal fade" id="deleteModal" tabindex="-1" aria-labelledby="deleteModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered"> <!-- Added modal-dialog-centered -->
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="deleteModalLabel">{{ __('message.confirm_deletion') }}</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    {{ __('message.want_delete_selected_files') }}
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">{{ __('message.cancel') }}</button>
                    <button type="button" id="confirmDelete" class="btn btn-danger">{{ __('message.delete') }}</button>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <script>
        $('.closebutton').on('click', function () {
            location.reload();
        })
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

        $(document).on('click','#bulk_delete',function() {

            var id = [];

            $('.upload_checkbox:checked').each(function(){
                id.push($(this).val())
            });
            if(id.length<=0){
                swal.fire({
                    title: "<h2 class='swal2-title custom-title'>{{Lang::get('message.Select')}}</h2>",
                    html: "<div class='swal2-html-container custom-content'>" +
                        "<div class='section-sa'>" +
                        "<p>{{Lang::get('message.sweet_file')}}</p>" + "</div>" +
                        "</div>",
                    position: 'top',
                    confirmButtonText: "{{ __('message.ok') }}",
                    showCloseButton: true,
                    confirmButtonColor: "#007bff",
                    width: "600px",
                })
            }
            else {
                var swl = swal.fire({
                    title: "<h2 class='swal2-title custom-title'>{{Lang::get('message.Delete')}}</h2>",
                    html: "<div class='swal2-html-container custom-content'>" +
                        "<div class='section-sa'>" +
                        "<p>{{Lang::get('message.file_delete')}}</p>" + "</div>" +
                        "</div>",
                    showCancelButton: true,
                    showCloseButton: true,
                    position: "top",
                    width: "600px",

                    confirmButtonText: @json(trans('message.Delete')),
                    cancelButtonText: "{{ __('message.cancel') }}",
                    confirmButtonColor: "#007bff",

                }).then((result) => {
                    if (result.isConfirmed) {
                        $('.upload_checkbox:checked').each(function () {
                            id.push($(this).val())
                        });
                        if (id.length > 0) {
                            $.ajax({
                                url: "{!! Url('uploads-delete') !!}",
                                method: "delete",
                                data: $('#checks:checked').serialize(),
                                beforeSend: function () {
                                    $('#gif').show();
                                },
                                success: function (data) {
                                    $('#gif').hide();
                                    var result="";
                                    if(data.success==true){
                                        var result =  '<div class="alert alert-success alert-dismissable"><button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button><strong><i class="fa fa-check"></i> {{ __('message.success') }} </strong>'+data.message+'</div>';
                                    }else{
                                        var result =  '<div class="alert alert-danger alert-dismissable"><button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button><strong><i class="fa fa-ban"></i> {{ __('message.error') }} </strong>'+data.message+'</div>';

                                    }
                                    $('#response').html(result);
                                    setTimeout(function() {
                                        location.reload();
                                    }, 5000);
                                }
                            })
                        } else {
                            swal.fire({
                                title: "<h2 class='swal2-title custom-title'>{{Lang::get('message.Select')}}</h2>",
                                html: "<div class='swal2-html-container custom-content'>" +
                                    "<div class='section-sa'>" +
                                    "<p>{{Lang::get('message.sweet_file')}}</p>" + "</div>" +
                                    "</div>",
                                position: 'top',
                                confirmButtonText: "{{ __('message.ok') }}",
                                showCloseButton: true,
                                confirmButtonColor: "#007bff",
                                width: "600px",
                            })
                        }
                    } else if (result.dismiss === Swal.DismissReason.cancel) {
                        // Action if "No" is clicked
                        window.close();
                    }
                })
                return false;
            }
        })
        $('ul.nav-sidebar a').filter(function() {
            return this.id == 'all_product';
        }).addClass('active');

        // for treeview
        $('ul.nav-treeview a').filter(function() {
            return this.id == 'all_product';
        }).parentsUntil(".nav-sidebar > .nav-treeview").addClass('menu-open').prev('a').addClass('active');
    </script>
    <script>
        $(function () {
            $('[data-toggle="tooltip"]').tooltip({
                container : 'body'
            });
        });
    </script>
    <link rel="stylesheet" href="https://cdn.datatables.net/1.10.21/css/jquery.dataTables.min.css">

    {{--<!-- <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.10.2/jquery.min.js"></script> -->
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/1/jquery.min.js"></script>--}}
    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <script src="//cdn.datatables.net/1.10.21/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.tiny.cloud/1/e3lij95fzfkwg5d1rx1e67g8kuu6ugnh3e4vdkmnxr0t8a4j/tinymce/5/tinymce.min.js" referrerpolicy="origin"></script>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.3/js/select2.min.js"></script>
    <link href="//cdnjs.cloudflare.com/ajax/libs/select2/4.0.0/css/select2.min.css" rel="stylesheet" />
    <script src="//cdnjs.cloudflare.com/ajax/libs/select2/4.0.0/js/select2.min.js"></script>

    <script>
        $(function(){
            tinymce.init({
                selector: '#product-description',
                height: 500,
                //  theme: 'modern',
                relative_urls: true,
                remove_script_host: false,
                convert_urls: false,
                directionality: '{{ isRtlForLang() ? "rtl" : "ltr" }}',
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

                        // Example condition: Change border if content length > 10
                        if (editor.getContent({ format: 'text' }).length <1) {
                            editorContainer.style.border = "1px solid #dc3545";
                        } else {
                            editorContainer.style.border = '1px solid silver';
                        }
                    });
                }
            });
            tinymce.init({
                selector: '#textarea1',
                height: 770,
                //  theme: 'modern',
                relative_urls: true,
                remove_script_host: false,
                convert_urls: false,
                directionality: '{{ isRtlForLang() ? "rtl" : "ltr" }}',
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
            tinymce.init({
                selector: '#textarea2',
                height:300,
                //  theme: 'modern',
                relative_urls: true,
                remove_script_host: false,
                convert_urls: false,
                directionality: '{{ isRtlForLang() ? "rtl" : "ltr" }}',
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
            tinymce.init({
                selector: '#textarea3',
                height: 300,
                //  theme: 'modern',
                relative_urls: true,
                remove_script_host: false,
                convert_urls: false,
                directionality: '{{ isRtlForLang() ? "rtl" : "ltr" }}',
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

        function readmore(){
            var maxLength = 300;
            $("#upload-table tbody tr td").each(function(){
                var myStr = $(this).text();
                if($.trim(myStr).length > maxLength){
                    var newStr = myStr.substring(0, maxLength);
                    $(this).empty().html(newStr);
                    var removedStr = myStr.substring(maxLength, $.trim(myStr).length);
                    $(this).append('<span class="more-text">' + removedStr + '</span>');
                    $(this).append(' <a href="javascript:void(0);" class="read-more">{{ __('message.read_more') }}</a>');
                }
            });
        }
        $('#upload-table').DataTable({
            destroy: true,
            "initComplete": function(settings, json) {
                readmore();
            },
            processing: true,
            serverSide: true,
            order: [[ 0, "desc" ]],

            ajax: '{!! route('get-upload',$product->id) !!}',
            "oLanguage": {
                "sLengthMenu": "_MENU_ Records per page",
                "sSearch"    : "{{ __('message.table_search') }} ",
                "sProcessing": ' <div class="overlay"><i class="fas fa-3x fa-sync-alt fa-spin"></i><div class="text-bold pt-2">{{ __('message.loading') }}</div></div>'
            },
            language: {
                paginate: {
                    first:      "{{ __('message.paginate_first') }}",
                    last:       "{{ __('message.paginate_last') }}",
                    next:       "{{ __('message.paginate_next') }}",
                    previous:   "{{ __('message.paginate_previous') }}"
                },
                emptyTable:     "{{ __('message.empty_table') }}",
                info:           "{{ __('message.datatable_info') }}",
                zeroRecords:    "{{ __('message.no_matching_records_found') }} ",
                infoEmpty:      "{{ __('message.info_empty') }}",
                infoFiltered:   "{{ __('message.info_filtered') }}",
                lengthMenu:     "{{ __('message.sLengthMenu') }}",
                loadingRecords: "{{ __('message.loading_records') }}",
            },
            columnDefs: [
                {
                    targets: 'no-sort',
                    orderable: false,
                    order: []
                }
            ],
            columns: [
                {data: 'checkbox', name: 'checkbox'},
                {data: 'title', name: 'title'},
                {data: 'description', name: 'description'},
                {data: 'version', name: 'version'},
                {data: 'releasetype', name: 'releasetype'},
                {data: 'file', name: 'file'},
                {data: 'action', name: 'action'}
            ],
            "fnDrawCallback": function( oSettings ) {
                bindEditButton();
                $('.loader').css('display', 'none');
            },
            "fnPreDrawCallback": function(oSettings, json) {
                $('.loader').css('display', 'block');
            },
        });



        function bindEditButton() {
            $('[data-toggle="tooltip"]').tooltip({
                container : 'body'
            });
            $('.editUploadsOption').click(function(){
                var upload_id = $(this).attr('data-id');
                var title =  $(this).attr('data-title');
                var version=  $(this).attr('data-version');
                var description =  $(this).attr('data-description');
                tinymce.get('product-description').setContent(description);
                $('#edit-uplaod-option').modal('show');
                $("#uploadid").val(upload_id);
                $("#product-title").val(title);
                $("#product-version").val(version);
            })
        }

        $("#editProductUpload").on('click',function(){
            $("#editProductUpload").html("<i class='fa fa-circle-o-notch fa-spin fa-1x fa-fw'></i>{{ __('message.please_wait') }}");
            var upload_id = $('#uploadid').val();
            var productname = $('#editName').val();
            var producttitle = $('#product-title').val();
            var description = tinyMCE.get('product-description').getContent()
            var version = $('#product-version').val();
            $.ajax({
                type : "PATCH",
                url  :  "{{url('upload/')}}"+"/"+upload_id,
                data :  {'productname': productname , 'producttitle': producttitle,
                    'description': description,'version':version},
                success: function(response) {
                    $("#editProductUpload").html("<i class='fa fa-floppy-o'>&nbsp;&nbsp;</i>{{ __('message.save') }}");
                    $('#alertMessage2').show();
                    $('#error1').hide();
                    var result =  '<div class="alert alert-success alert-dismissable"><button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button><strong><i class="far fa-check"></i> {{ __('message.success') }}! </strong>'+response.message+'.</div>';
                    $('#alertMessage2').html(result+ ".");
                } ,
                error: function(ex) {
                    $("#editProductUpload").html("<i class='fa fa-floppy-o'>&nbsp;&nbsp;</i>Save");
                    var html = '<div class="alert alert-danger"><strong>{{ __('message.whoops') }} </strong>{{ __('message.something_wrong') }}<br><br><ul>';
                    for (key in ex.responseJSON.errors) {
                        html += '<li>'+ ex.responseJSON.errors[key][0] + '</li>'
                    }
                    html += '</ul></div>';
                    $('#error1').show();
                    document.getElementById('error1').innerHTML = html;
                }
            });

        })

    </script>
    <script>
        function checking(e){
            $('#upload-table').find("td input[type='checkbox']").prop('checked', $(e).prop('checked'));
        }
        $(document).ready(function () {
            var selectedIds = [];

            let alertTimeout;
            function showAlert(type, messageOrResponse) {
                // Generate appropriate HTML
                var html = generateAlertHtml(type, messageOrResponse);

                // Clear any existing alerts and remove the timeout
                $('#product-alert-container').html(html);
                clearTimeout(alertTimeout); // Clear the previous timeout if it exists

                // Display alert
                $('html, body').animate({
                    scrollTop: $('#product-alert-container').offset().top
                }, 500);

                // Auto-dismiss after 5 seconds, then reload the page
                alertTimeout = setTimeout(function() {
                    $('#product-alert-container .alert').fadeOut('slow', function() {
                        location.reload(); // Reload after alert hides
                    });
                }, 5000);
            }



            function generateAlertHtml(type, response) {
                // Determine alert styling based on type
                const isSuccess = type === 'success';
                const iconClass = isSuccess ? 'fa-check-circle' : 'fa-ban';
                const alertClass = isSuccess ? 'alert-success' : 'alert-danger';

                // Extract message and errors
                const message = response.message || response || 'An error occurred. Please try again.';
                const errors = response.errors || null;

                // Build base HTML
                let html = `<div class="alert ${alertClass} alert-dismissible">` +
                    `<i class="fa ${iconClass}"></i> ` +
                    `${message}` +
                    '<button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>';

                html += '</div>';

                return html;
            }

            // Open modal when delete button is clicked
            // $(document).on('click', '#bulk_delete', function () {
            //     selectedIds = [];
            //
            //     $('.upload_checkbox:checked').each(function () {
            //         selectedIds.push($(this).val());
            //     });
            //
            //     if (selectedIds.length > 0) {
            //         $('#deleteModal').modal('show'); // Show Bootstrap modal
            //     } else {
            //         alert("Please select at least one checkbox");
            //     }
            // });

            // Confirm delete inside modal
            $('#confirmDelete').click(function () {
                $.ajax({
                    url: "{!! url('uploads-delete') !!}",
                    method: "POST",
                    contentType: "application/json", // Send JSON format
                    dataType: "json",
                    data: JSON.stringify({
                        _method: "DELETE",
                        _token: document.querySelector('meta[name="csrf-token"]').content, // CSRF Token
                        ids: selectedIds // Send the selected IDs
                    }),
                    beforeSend: function () {
                        $('#gif').show();
                    },
                    success: function (response) {
                        showAlert('success', response.message);
                    },
                    error: function (xhr) {
                        showAlert('error', xhr.responseJSON.message);
                    },
                    complete: function (){
                        $('#deleteModal').modal('hide');
                    }
                });
            });
        });


    </script>


    <script>
        $(document).on('click','#upload-table tbody tr td .read-more',function(){
            var text=$(this).siblings(".more-text").text().replace('read more...','');
            $(this).siblings(".more-text").html(text);
            $(this).siblings(".more-text").contents().unwrap();
            $(this).remove();
        });
    </script>

    <script>

        $(document).ready(function() {
            tinymce.get('textarea1').on('change', function() {
                let content = tinymce.get('textarea1').getContent();
                if(content !==''){
                    let editorContainer = document.querySelector(".tox-tinymce");
                    editorContainer.style.border = "1px solid silver";
                    removeErrorMessage(document.getElementById('textarea1'));
                }
            });

            tinymce.get('product-description').on('change', function() {
                let content = tinymce.get('product-description').getContent();
                if(content !==''){
                    let editorContainer = document.querySelector(".tox-tinymce");
                    editorContainer.style.border = "1px solid silver";
                    removeErrorMessage(document.getElementById('product-description'));
                }
            });

            const userRequiredFields = {
                name:@json(trans('message.product_details.add_name')),
                type:@json(trans('message.product_details.add_license_type')),
                group:@json(trans('message.product_details.add_group')),
                product_sku:@json(trans('message.product_details.add_product_sku')),
                description:@json(trans('message.product_details.add_description')),
                short_description:@json(trans('message.add_short_description')),
                productdes:@json(trans('message.product_details.add_product_description')),

            };

            $('#editproduct').on('submit', function (e) {
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
                    productdes:$('#product-description'),
                    description:$('#textarea1'),
                    short_description:$('#textarea2'),

                };


                // Clear previous errors
                Object.values(userFields).forEach(field => {
                    field.removeClass('is-invalid');
                    field.next().next('.error').remove();
                });

                let isValid = true;

                const showError = (field, message) => {

                    console.log(field);
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

            //Add input event listeners for all fields
            ['productname','type','group','product_sku'].forEach(id => {

                document.getElementById(id).addEventListener('input', function () {
                    removeErrorMessage(this);

                });
            });
        });

    </script>


    <script>
        $(document).ready(function(){
            var githubstatus =  $('#gitstatus').val();

            if( $("input[type=radio][name='show_agent']:checked").val() == 1) {
                $('#agent').prop('checked',true);
                $('#allowmulagent').show();
                if($('#agent_multiple_qty').val() == 1) {
                    $('#agent_multiple_qty').prop('checked',true);
                }
            }
            if($("input[type=radio][name='show_agent']:checked").val() == 0) {
                $('#quantity').prop('checked',true);
                $('#allowmulproduct').show();
                if($('#product_multiple_qty').val() == 1) {
                    $('#product_multiple_qty').prop('checked',true);
                }
            }

            if($('#checkowner').val() != '') {
                $('#chkYes').prop('checked',true);
                $('#git').show();
                if(githubstatus == 0) {
                    $("#owner").attr('disabled',true);
                    $("#repo").attr('disabled',true);
                    $("#version").attr('disabled',true);
                } else {
                    $("#owner").attr('enabled',true);
                    $("#repo").attr('enabled',true);
                    $("#version").attr('enabled',true);
                }
            } else if($('#checkowner').val() == '') {
                $('#chkNo').prop('checked',true);
                document.getElementById("hide").style.display="block";
                $("#uploads").show();
            }

            $("#chkNo").click(function () {
                if ($(this).is(":checked")) {
                    // Clear GitHub credential fields
                    $("#owner, #repo, #version").val('');
                }
            });

            // })

        });


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


    <script>
        $(document).ready(function() {
            $("#editTax").select2({
                placeholder: '{{ __('message.select_taxes') }}',
                tags:true,
                language: {
                    inputTooShort: function () {
                        return '{{ __("message.select2_input_too_short") }}';
                    },
                    noResults: function () {
                        return '{{ __("message.select2_no_results") }}';
                    },
                    searching: function () {
                        return '{{ __("message.select2_searching") }}';
                    }
                },
            });
        });
    </script>
    <script>
        $(document).ready(function () {
            var $fileUpload = $('#file-upload');
            var $progress = $('#upload-progress');
            var $progressBar = $('#progress-bar');
            var $progressText = $('#progress-text');
            var $fileName = $('#file-name');
            var $fileSize = $('#file-size');
            var $fileInfo = $('#file-info');
            var $fileIds = $('#file_ids');
            var $removeFile = $('#remove-file');

            if ($fileUpload.length > 0) {
                var resumable = new Resumable({
                    target: $fileUpload.data('url'),
                    chunkSize: 5 * 1024 * 1024, // 5MB
                    simultaneousUploads: 3,
                    testChunks: false,
                    query: { _token: $('input[name=_token]').val() }
                });

                if (!resumable.support) {
                    console.error('Resumable.js not supported');
                } else {
                    resumable.assignBrowse($fileUpload[0]);

                    // Clear previous error
                    function clearError() {
                        $fileUpload.removeClass('is-invalid');
                        $fileUpload.next('.invalid-feedback').remove();
                    }

                    // Reset file UI
                    function resetFileUI() {
                        // Hide file info
                        $fileInfo.addClass('d-none').removeClass('d-flex');
                        $fileName.text('');
                        $fileSize.text('');
                        $fileIds.val('');
                        // Hide progress
                        $progress.addClass('d-none');
                        $progressBar.css('width', '0%').attr('aria-valuenow', 0);
                        $progressText.text('0%');
                        // Re-enable file input
                        $fileUpload.prop('disabled', false);
                        // Reset file label
                        $('#file-label').text('Choose file');
                    }

                    // Remove file button
                    $removeFile.on('click', function () {
                        resetFileUI();
                        resumable.cancel(); // Cancel any ongoing upload
                    });

                    // File added
                    resumable.on('fileAdded', function (file) {
                        // Show progress, hide file info
                        $progress.removeClass('d-none');
                        $fileInfo.addClass('d-none').removeClass('d-flex');

                        // Prepare file info (but don't show yet)
                        $fileName.text(file.fileName);
                        $fileSize.text((file.size / 1024 / 1024).toFixed(2) + ' MB');

                        clearError();
                        $progressBar.css('width', '0%').attr('aria-valuenow', 0);
                        $progressText.text('0%');

                        resumable.upload();
                    });

                    // File progress
                    resumable.on('fileProgress', function (file) {
                        var progressPercent = Math.floor(file.progress() * 100);
                        $progressBar.css('width', progressPercent + '%').attr('aria-valuenow', progressPercent);
                        $progressText.text(progressPercent + '%');
                        clearError();
                    });

                    // File success
                    resumable.on('fileSuccess', function (file, response) {
                        try {
                            var data = JSON.parse(response);

                            // Save file id
                            $fileIds.val(data.name);

                            // Hide progress bar
                            $progress.addClass('d-none');

                            // Show file info with d-flex for proper alignment
                            $fileInfo.removeClass('d-none').addClass('d-flex');
                            $fileName.text(file.fileName);
                            $fileSize.text((file.size / 1024 / 1024).toFixed(2) + ' MB');

                            // Disable file input (only one file allowed)
                            $fileUpload.prop('disabled', true);

                            clearError();
                        } catch (e) {
                            $progress.addClass('d-none');
                            $fileInfo.removeClass('d-none').addClass('d-flex');
                            $fileUpload.prop('disabled', true);
                        }
                    });

                    // File error
                    resumable.on('fileError', function (file, response) {
                        clearError();

                        // Show error below the file input
                        var errorMsg = $('<span class="invalid-feedback d-block"></span>')
                            .text(JSON.parse(response) || 'Upload failed');
                        $fileUpload.after(errorMsg);
                        $fileUpload.addClass('is-invalid');

                        // Reset UI
                        resetFileUI();
                    });
                }
            }
        });

        //------------------------------------------------------------------------------------------------------------//

        function privateRelease()
        {
            // val = $('#p_release').val();
            if ($('#p_release').attr('checked',true)) {
                $('#p_release').val('1');
            } else {
                $('#p_release').val('0');
            }
        }

        function resrictedRelease()
        {
            // val = $('#p_release').val();
            if ($('#r_release').attr('checked',true)) {
                $('#r_release').val('1');
            } else {
                $('#r_release').val('0');
            }
        }

        function preRelease()
        {
            if ($('#pre_release').attr('checked',true)) {
                $('#pre_release').val('1');
            } else {
                $('#pre_release').val('0');
            }
        }


    </script>

    <script>

        $(document).ready(function() {

            const userRequiredFields = {
                title:@json(trans('message.add_files.title')),
                dependencies:@json(trans('message.add_files.dependencies')),
                version:@json(trans('message.add_files.version')),
                files:@json(trans('message.file_upload_required')),
            };

            $("#uploadVersion").on('click',function(e){
                const userFields = {
                    title:$('#producttitle'),
                    dependencies:$('#dependencies'),
                    version:$('#productver'),
                    files: $('#file-upload-list')
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
                    if (field === 'files') {
                        userFields[field].removeClass('is-invalid');
                        userFields[field].next('.error').remove(); // prevent duplicate errors

                        if ($('#file-upload-list li').length === 0) {
                            userFields[field].addClass('is-invalid');
                            userFields[field].after(`<span class='error invalid-feedback errorRemove'>@json(trans('message.file_upload_required'))</span>`);
                            isValid = false;
                        }
                    }
                    else {
                        $('.errorRemove').val('');
                        if (!userFields[field].val()) {
                            showError(userFields[field], userRequiredFields[field]);
                            isValid = false;
                        }
                    }
                });


                // If validation fails, prevent form submission
                if (!isValid) {
                    e.preventDefault();
                }else{
                    $("#uploadVersion").html("<i class='fas fa-circle-notch fa-spin'></i>  {{ __('message.please_wait') }}");
                    var filename = $('#file_ids').val();
                    var productname = $('#productname').val();
                    var producttitle = $('#producttitle').val();
                    var description = tinyMCE.get('textarea3').getContent()
                    var version = $('#productver').val();
                    var dependencies = $('#dependencies').val();
                    var private = $('#p_release').val();
                    var restricted = $('#r_release').val();
                    var releaseType = $('#release_type').val();
                    $.ajax({
                        type : "POST",
                        url  :  "{!! route('upload/save') !!}",
                        data :  {'filename': filename , 'productname': productname , 'producttitle': producttitle,
                            'description': description,'dependencies':dependencies,'version':version,'is_private': private,'is_restricted': restricted,'release_type': releaseType,'_token': '{!! csrf_token() !!}'},
                        success: function(response) {
                            $("#uploadVersion").html("<i class='fa fa-save'>&nbsp;&nbsp;</i>{{ __('message.save') }}");
                            $('#alertMessage1').show();
                            $('#error').hide();
                            var result =  '<div class="alert alert-success alert-dismissable" id="productUpload"><button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button><strong><i class="far fa-check"></i> {{ __('message.success') }}! </strong>'+response.message+'.</div>';
                            $('#alertMessage1').html(result+ ".");
                            setTimeout(function() {
                                location.reload();
                            }, 5000);
                        } ,
                        error: function(ex) {
                            $("#uploadVersion").html("<i class='fa fa-save'>&nbsp;&nbsp;</i>{{ __('message.save') }}");
                            var html = '<div class="alert alert-danger" id="productUpload"><strong>{{ __('message.whoops') }} </strong>{{ __('message.something_wrong') }}<br><br><ul>';
                            for (key in ex.responseJSON.errors) {
                                html += '<li>'+ ex.responseJSON.errors[key][0] + '</li>'
                            }
                            html += '</ul></div>';
                            $('#error').show();
                            document.getElementById('error').innerHTML = html;
                        }
                    });
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
            ['title','version','dependencies'].forEach(id => {

                document.getElementById(id).addEventListener('input', function () {
                    removeErrorMessage(this);

                });
            });

        });
    </script>




@stop