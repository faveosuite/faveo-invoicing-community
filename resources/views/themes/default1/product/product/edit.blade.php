@extends('themes.default1.layouts.master')
@section('title')
Edit Product
@stop
@section('content-header')
    <div class="col-sm-6">
        <h1>Edit Product</h1>

    </div>
    <div class="col-sm-6">
        <ol class="breadcrumb float-sm-right">
            <li class="breadcrumb-item"><a href="{{url('/')}}"><i class="fa fa-dashboard"></i> Home</a></li>
            <li class="breadcrumb-item"><a href="{{url('products')}}"><i class="fa fa-dashboard"></i> Products</a></li>
            <li class="breadcrumb-item active">Edit Product</li>
        </ol>
    </div><!-- /.col -->
@stop

@section('content')
<style>
    .more-text{
     display:none;
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
    background-color: #1b1818 !important;
</style>
<div class="card card-secondary card-tabs">

    {!! html()->modelForm($product, 'PATCH', 'products/'.$product->id)->acceptsFiles()->id('editproduct')->open() !!}
    <div class="card-header p-0 pt-1">
        <ul class="nav nav-tabs" id="custom-tabs-one-tab" role="tablist">
            <li class="nav-item">
                <a class="nav-link active" id="custom-tabs-detail-tab" data-toggle="pill" href="#custom-tabs-detail" role="tab" aria-controls="custom-tabs-detail" aria-selected="true">Details</a>
            </li>
            <li class="nav-item">
                <a class="nav-link" id="custom-tabs-plan-tab" data-toggle="pill" href="#custom-tabs-plan" role="tab" aria-controls="custom-tabs-plan" aria-selected="false">Plans</a>
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
                                    {!! html()->text('name')->class('form-control')->id('productname') !!}
                                    <h6 id= "namecheck"></h6>

                                </div>

                                <div class="col-md-4 form-group {{ $errors->has('type') ? 'has-error' : '' }}">
                                    <!-- last name -->
                                    {!! html()->label(trans('message.lic_type'), 'type')->class('required') !!}
                                    {!! html()->select('type', ['' => 'Choose', 'Types' => $type])->class('form-control') !!}

                                </div>
                                <?php
                               $groups = DB::table('product_groups')->pluck('name', 'id')->toarray();
                                ?>
                                <div class="col-md-4 form-group {{ $errors->has('group') ? 'has-error' : '' }}">
                                    <!-- last name -->
                                    {!! html()->label(Lang::get('message.group'), 'group')->class('required') !!}
                                    <select name="group"  class="form-control">
                            <option >Choose</option>
                            @foreach($groups as $key=>$group)
                                   <option value="{{$key}}" <?php  if (in_array($group, $selectedGroup)) {
                                    echo "selected";
                                } ?>>{{$group}}</option>

                             @endforeach
                              </select>
                                </div>



                            </div>

                            <div class="row">

                                <div class="col-md-6 form-group {{ $errors->has('description') ? 'has-error' : '' }}">

                                    {!! html()->label(Lang::get('message.price_description'), 'description')->class('required') !!}
                                    <!--{!! html()->text('description')->class('form-control')->id('textarea1') !!}-->
                                    <textarea hidden class="form-control"  name="description" id='textarea1'>{!! $product->description !!}</textarea>


                                     <h6 id= "descheck"></h6>
                                     </div>


                                   <div class="col-md-6">
                                    <ul class="list-unstyled">
                                        <li>
                                            <div class="form-group {{ $errors->has('parent') ? 'has-error' : '' }}">
                                                <!-- last name -->
                                                {!! html()->label(trans('message.sku'), 'product_sku')->class('required') !!}
                                                {!! html()->text('product_sku')->class('form-control') !!}

                                            </div>
                                        </li>
                                        <li>
                                            <div class="form-group {{ $errors->has('parent') ? 'has-error' : '' }}">
                                                <!-- last name -->
                                                {!! html()->label(trans('message.parent'), 'parent') !!}
                                                {!! html()->select('parent[]', ['' => 'Choose', 'Products' => $products])->class('form-control') !!}

                                            </div>
                                        </li>

                                        <li>
                                        <div class="form-group {{ $errors->has('image') ? 'has-error' : '' }}">
                                                        <!-- last name -->
                                            {!! html()->label(trans('message.image'), 'image') !!}
                                            {!! html()->file('image') !!}
                                            <br>
                                                @if($product->image)
                                               <img src="{{$product->image }}" width="100px" alt="slider Image">
                                               @endif

                                        </div>
                                                </li>


                                            <table class="table">
                                                <input type="hidden" value="{{$checkowner}}" id="checkowner">
                                              <span>Where do you want to retrieve your files from?</span>
                                             </br>
                                             <input type="hidden" value="{{$githubStatus}}" id="gitstatus">
                                                <tr>

                                                    <td>
                                                        <label for="chkYes" style="">
                                                        <input type="radio" id="chkYes" name="chkTax" />
                                                        Github
                                                    </label>
                                                      <div class="col-md-10 gitstatus" id="git" style="display:none">
                                                <li>
                                                    <div class="form-group {{ $errors->has('github_owner') ? 'has-error' : '' }}">
                                                        <!-- first name -->
                                                        {!! html()->label(trans('message.github-owner'), 'github_owner') !!}
                                                        {!! html()->text('github_owner')->class('form-control')->id('owner') !!}

                                                    </div>
                                                    <div class="form-group {{ $errors->has('github_repository') ? 'has-error' : '' }}">
                                                        <!-- last name -->
                                                        {!! html()->label(trans('message.github-repository-name'), 'github_repository') !!}
                                                        {!! html()->text('github_repository')->class('form-control')->id('repo') !!}


                                                    </div>
                                                </li>

                                                <li>
                                                    <div class="form-group {{ $errors->has('version') ? 'has-error' : '' }}">
                                                        <!-- last name -->
                                                        {!! html()->label(trans('message.version'), 'version') !!}
                                                        {!! html()->text('version')->class('form-control')->id('version') !!}


                                                    </div>
                                                </li>
                                                      </div>
                                                   </td>
                                               </tr>
                                                <tr>
                                                    <td><label for="chkNo">
                                                        <input type="radio" id="chkNo" name="chkTax" />
                                                            Filesystem
                                                        </label>
                                                    </td>
                                                </tr>
                                             </table>

                                        <li>
                                            <div class="form-group {{ $errors->has('require_domain') ? 'has-error' : '' }}">
                                                <!-- last name -->
                                                {!! html()->label(trans('message.require_domain'), 'require_domain') !!}
                                                {!! html()->hidden('require_domain', 0) !!}
                                                <p>
                                                    {!! html()->checkbox('require_domain', 1) !!}
                                                    {{ trans('message.tick-to-show-domain-registration-options') }}
                                                </p>

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
                                                   <?php
                                                $value=  "";
                                                if ($product->hidden==1) {
                                                    $value = 'true';
                                                }
                                                ?>
                                                   <p>{!! html()->checkbox('hidden', $value, 1) !!} {{ trans('message.tick-to-hide-from-order-form') }}</p>

                                               </div>

                                            </div>
                                        </li>
                                          <li>
                                            <div class="row">

                                               <div class="form-group {{ $errors->has('highlight') ? 'has-error' : '' }}">
                                                <!-- first name -->
                                                   {!! html()->label(trans('message.highlight')) !!}
                                                   {!! html()->hidden('highlight', 0) !!}
                                                   <?php
                                                $value=  "";
                                                if ($product->highlight==1) {
                                                    $value = 'true';
                                                }
                                                ?>
                                                   {!! html()->checkbox('highlight', $value)->label(trans('message.tick-to-highlight-product')) !!}

                                               </div>

                                            </div>
                                        </li>

                                            <li>
                                            <div class="row">

                                               <div class="form-group {{ $errors->has('add_to_contact') ? 'has-error' : '' }}">
                                                <!-- first name -->
                                                   {!! html()->label(Lang::get('Contact to sales'))->for('add_to_contact') !!}
                                                   {!! html()->checkbox('add_to_contact', $value) !!}
                                                   <?php
                                                $value=  "";
                                                if ($product->add_to_contact==1) {
                                                    $value = 'true';
                                                }
                                                ?>
                                                <p>{!! html()->checkbox('add_to_contact', $value) !!}{{Lang::get('message.tick-to-add_to_contact-product')}}</p>

                                            </div>

                                            </div>
                                        </li>

                                </div>

                            </div>
                            <div class="row">

                            <div class="col-md-12 form-group {{ $errors->has('product_description') ? 'has-error' : '' }}">
                                {!! html()->label(Lang::get('message.product_description'))->class('required') !!}
                                {!! html()->textarea('product_description', $product->product_description)->class('form-control')->id('product-description') !!}
                                <h6 id="descheck"></h6>
                            </div>
                            </div>


                        </div>

                         <!-- /.tab-pane -->
                        <div class="tab-pane fade" id="custom-tabs-plan" role="tabpanel"  aria-labelledby="custom-tabs-plan-tab">
                            <table class="table">

                                <span class="required">Show on Cart Page</span>
                                 <tr>
                                    <div class="row">
                                    <td>

                                        <div><label>
                                                {!! html()->radio('show_agent', null, 1)->id('agent') !!}
                                                <!-- <input type ="radio" id="agent" value="0" name="cartquantity" hidden>   -->
                                            Agents
                                        </label></div>

                                    <br/>
                                    <div class="col-md-10" id="allowmulagent" style="display:none">
                                       <p>{!! html()->checkbox('can_modify_agent', 1, null)->id('agent_multiple_quantity') !!}{{Lang::get('message.allow_multiple_agents_quantity')}} </p>
                                    </div>

                                   </td>
                                  </div>
                                </tr>
                                <tr>
                                    <td>
                                        <div>
                                            <label>
                                                {!! html()->radio('show_agent', false, null)->id('quantity') !!}
                                                Product Quantity
                                         </label>
                                        </div>
                                         <br/>
                                     <div class="col-md-10" id="allowmulproduct" style="display:none">
                                       <p>{!! html()->checkbox('can_modify_quantity', 1, null)->id('product_multiple_quantity') !!}{{Lang::get('message.allow_multiple_product_quantity')}} </p>
                                    </div>

                                    </td>
                                </tr>
                        </table>

                                <tr>
                                    <td><b>{!! html()->label('tax', trans('message.taxes')) !!}</b></td>
                                    <td>
                                        <div class="form-group {{ $errors->has('taxes') ? 'has-error' : '' }}">
                                            <div class="row">

                                                <div class="col-md-2">
                                                   <select id="editTax" placeholder="Select Taxes" name="tax[]" style="width:500px;" class="select2" multiple="true">

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
                                            <h3>  Plans &nbsp;
                                                <!-- <a href="#create-plan-option" data-toggle="modal" data-target="#create-plan-option" class="btn btn-default">Add new</a> -->
                                            </h3>

                                            @if($product->plan())
                                                <table class="table">

                                                    <tr>
                                                        <th>Name</th>
                                                        <th>Months</th>
                                                        <th>Action</th>
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
                                                            <td><a href="{{url('plans/'.$plan->id.'/edit')}}" class="btn btn-secondary btn-xs".{!! tooltip('Edit') !!}<i class='fa fa-edit' style='color:white;'></i></a></td>
                                                        </tr>
                                                     @endforeach
                                                </table>
                                    @else
                                        <td>No Plans Created</td>
                            @endif

                                        </div>


       </div>
        <button type="submit" class="btn btn-primary pull-right" id="submit" data-loading-text="<i class='fa fa-circle-o-notch fa-spin'>&nbsp;</i> Saving..."><i class="fas fa-sync-alt">&nbsp;</i>{!!Lang::get('message.update')!!}</button>

           {!! html()->form()->close() !!}

           <!-- nav-tabs-custom -->




                              </div>





       </div>
                   </div>

        <div class="row" id="hide" style="display:none">
        <div class="col-md-12">
        <div class="card card-secondary card-outline" id="uploads">
            <div class="card-header">
                <h3 class="card-title">Upload Files</h3>

                <div class="card-tools">
                    <a href="#create-upload-option" id="create" class="btn btn-default  btn-sm pull-right" data-toggle="modal" data-target="#create-upload-option"><span class="fa fa-plus"></span>&nbsp;&nbsp;{{Lang::get('message.add-file')}}</a>
                    @include('themes.default1.product.product.create-upload-option')

                </div>
            </div>

            <!-- <div id="response"></div> -->
            <div id="product-alert-container"></div>
            <div class="card-body table-responsive">
                <div class="row" >
                    <div class="col-md-12" >
                         <table id="upload-table" class="table display" cellspacing="0" width="100%" styleClass="borderless">
                          <button  value="" class="btn btn-secondary btn-sm btn-alldell" id="bulk_delete"><i class="fa fa-trash"></i>&nbsp;&nbsp; {{Lang::get('message.delmultiple')}}</button><br /><br />
                    <thead><tr>
                         <th class="no-sort"><input type="checkbox" name="select_all" onchange="checking(this)"></th>
                        <th>Title</th>
                        <th style="width:210px;">Description</th>
                        <th>Version</th>
                        <th>ReleaseType</th>
                        <th>File</th>
                        <th>Action</th>
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
                <h5 class="modal-title" id="deleteModalLabel">Confirm Deletion</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                Are you sure you want to delete the selected files?
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                <button type="button" id="confirmDelete" class="btn btn-danger">Delete</button>
            </div>
        </div>
    </div>
</div>


<script>
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

<!-- <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.10.2/jquery.min.js"></script> -->
<script src="https://ajax.googleapis.com/ajax/libs/jquery/1/jquery.min.js"></script>
<script src="//cdn.datatables.net/1.10.21/js/jquery.dataTables.min.js"></script>
 <script src="https://cdn.tiny.cloud/1/oiio010oipuw2n6qyq3li1h993tyg25lu28kgt1trxnjczpn/tinymce/5/tinymce.min.js" referrerpolicy="origin"></script>

<script>
                                        $(function(){
                                          tinymce.init({
                                         selector: '#product-description',
                                         height: 500,
                                       //  theme: 'modern',
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
                                          tinymce.init({
                                         selector: '#textarea1',
                                         height: 770,
                                       //  theme: 'modern',
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
                                              tinymce.init({
                                         selector: '#textarea3',
                                         height: 300,
                                       //  theme: 'modern',
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
<script type="text/javascript">
        function readmore(){
                        var maxLength = 300;
                        $("#upload-table tbody tr td").each(function(){
                            var myStr = $(this).text();
                            if($.trim(myStr).length > maxLength){
                                var newStr = myStr.substring(0, maxLength);
                                 $(this).empty().html(newStr);
                                var removedStr = myStr.substring(maxLength, $.trim(myStr).length);
                                $(this).append('<span class="more-text">' + removedStr + '</span>');
                                $(this).append(' <a href="javascript:void(0);" class="read-more">read more...</a>');
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
                "sSearch"    : "Search: ",
                "sProcessing": ' <div class="overlay"><i class="fas fa-3x fa-sync-alt fa-spin"></i><div class="text-bold pt-2">Loading...</div></div>'
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
      $("#editProductUpload").html("<i class='fa fa-circle-o-notch fa-spin fa-1x fa-fw'></i>Please Wait...");
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
         $("#editProductUpload").html("<i class='fa fa-floppy-o'>&nbsp;&nbsp;</i>Save");
        $('#alertMessage2').show();
        $('#error1').hide();
        var result =  '<div class="alert alert-success alert-dismissable"><button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button><strong><i class="far fa-check"></i> Success! </strong>'+response.message+'.</div>';
        $('#alertMessage2').html(result+ ".");
       } ,
       error: function(ex) {
         $("#editProductUpload").html("<i class='fa fa-floppy-o'>&nbsp;&nbsp;</i>Save");
        var html = '<div class="alert alert-danger"><strong>Whoops! </strong>Something went wrong<br><br><ul>';
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

     $(document).on('click','#bulk_delete',function(){
      var id=[];
      if (confirm("Are you sure you want to delete this?"))
        {
            $('.upload_checkbox:checked').each(function(){
              id.push($(this).val())
            });
            if(id.length >0)
            {
               $.ajax({
                      url:"{!! Url('uploads-delete') !!}",
                      method:"delete",
                      data: $('#checks:checked').serialize(),
                      beforeSend: function () {
                $('#gif').show();
                },
                success: function (data) {

                $('#gif').hide();
                $('#response').html(data);
                location.reload();
                }
               })
            }
            else
            {
                alert("Please select at least one checkbox");
            }
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
            $(document).on('click', '#bulk_delete', function () {
                selectedIds = [];

                $('.upload_checkbox:checked').each(function () {
                    selectedIds.push($(this).val());
                });

                if (selectedIds.length > 0) {
                    $('#deleteModal').modal('show'); // Show Bootstrap modal
                } else {
                    alert("Please select at least one checkbox");
                }
            });

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








    <script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.3/js/select2.min.js"></script>
  <link href="//cdnjs.cloudflare.com/ajax/libs/select2/4.0.0/css/select2.min.css" rel="stylesheet" />
<script src="//cdnjs.cloudflare.com/ajax/libs/select2/4.0.0/js/select2.min.js"></script>
<script>
        $(document).on('click','#upload-table tbody tr td .read-more',function(){
        var text=$(this).siblings(".more-text").text().replace('read more...','');
        $(this).siblings(".more-text").html(text);
        $(this).siblings(".more-text").contents().unwrap();
        $(this).remove();
    });
</script>
<script>
       // Jquery validation for Product Creation
    $(document).ready(function(){
        $('#namecheck').hide();
        $('#descheck').hide();

        var nameErr= true;
        var desErr = true;

        $('#editproduct').submit(function(){
            function name_check(){
                var name = $('#productname').val();
                if (name.length == ''){
                   $('#namecheck').show();
                   $('#namecheck').html('This field is required');
                   $('#namecheck').focus();
                   $('#productname').css("border-color","red");
                   $('#namecheck').css({"color":"red","margin-top":"5px"});
                }
                 else{
                     $('#namecheck').hide();
                      $('#productname').css("border-color","");
                     return true;
                     }
            }

            function des_check(){
                var des = $('#textarea1').val();
                if (des.length == ''){
                    $('#descheck').show();
                    $('#descheck').html('This field is required');
                    $('#descheck').focus();
                    $('#textarea1').css("border-color","red");
                    $('#descheck').css({"color":"red","margin-top":"5px"});
                }
                else{
                     $('#descheck').hide();
                     $('#textarea1').css("border-color","");
                     return true;
                }
            }
            name_check();
            des_check();
             if(name_check() && des_check()){
                return true;
             }
            else{
            return false;
          }
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
        placeholder: 'Select Taxes',
        tags:true
    });
});
</script>
<script>
 $(document).ready(function(){

var $ = window.$; // use the global jQuery instance

var $fileUpload = $('#resumable-browse');
var $fileUploadDrop = $('#resumable-drop');
var $uploadList = $("#file-upload-list");

if ($fileUpload.length > 0 && $fileUploadDrop.length > 0) {
    var resumable = new Resumable({
        // Use chunk size that is smaller than your maximum limit due a resumable issue
        // https://github.com/23/resumable.js/issues/51
        chunkSize: 1 * 1024 * 1024, // 1MB
        simultaneousUploads: 3,
        testChunks: false,
        throttleProgressCallbacks: 1,
        // Get the url from data-url tag
        target: $fileUpload.data('url'),
        // Append token to the request - required for web routes
        query:{_token : $('input[name=_token]').val()}
    });

// Resumable.js isn't supported, fall back on a different method
    if (!resumable.support) {
        $('#resumable-error').show();
    } else {
        // Show a place for dropping/selecting files
        $fileUploadDrop.show();
        resumable.assignDrop($fileUpload[0]);
        resumable.assignBrowse($fileUploadDrop[0]);

        // Handle file add event
        resumable.on('fileAdded', function (file) {
            // Show progress pabr
            $uploadList.show();
            // Show pause, hide resume
            $('.resumable-progress .progress-resume-link').hide();
            $('.resumable-progress .progress-pause-link').show();
            // Add the file to the list
            $uploadList.append('<li class="resumable-file-' + file.uniqueIdentifier + '">Uploading <span class="resumable-file-name"></span> <span class="resumable-file-progress"></span>');
            $('.resumable-file-' + file.uniqueIdentifier + ' .resumable-file-name').html(file.fileName);
            // Actually start the upload
            resumable.upload();
        });
        resumable.on('fileSuccess', function (file, message) {
            // Reflect that the file upload has completed
            $('.resumable-file-' + file.uniqueIdentifier + ' .resumable-file-progress').html('(completed)');
             $("#file_ids").val(JSON.parse(message).name);
        });
        resumable.on('fileError', function (file, message) {
            // Reflect that the file upload has resulted in error
            $('.resumable-file-' + file.uniqueIdentifier + ' .resumable-file-progress').html('(file could not be uploaded: ' + message + ')');
        });
        resumable.on('fileProgress', function (file) {
            // Handle progress for both the file and the overall upload
            $('.resumable-file-' + file.uniqueIdentifier + ' .resumable-file-progress').html(Math.floor(file.progress() * 100) + '%');
            $('.progress-bar').css({width: Math.floor(resumable.progress() * 100) + '%'});
        });
    }

}
})

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


 $("#uploadVersion").on('click',function(){
      $("#uploadVersion").html("<i class='fas fa-circle-notch fa-spin'></i>  Please Wait...");
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
         $("#uploadVersion").html("<i class='fa fa-save'>&nbsp;&nbsp;</i>Save");
        $('#alertMessage1').show();
        $('#error').hide();
        var result =  '<div class="alert alert-success alert-dismissable"><button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button><strong><i class="far fa-check"></i> Success! </strong>'+response.message+'.</div>';
        $('#alertMessage1').html(result+ ".");
       } ,
       error: function(ex) {
         $("#uploadVersion").html("<i class='fa fa-save'>&nbsp;&nbsp;</i>Save");
        var html = '<div class="alert alert-danger"><strong>Whoops! </strong>Something went wrong<br><br><ul>';
        for (key in ex.responseJSON.errors) {
           html += '<li>'+ ex.responseJSON.errors[key][0] + '</li>'
        }
          html += '</ul></div>';
           $('#error').show();
           document.getElementById('error').innerHTML = html;
       }
    });

 })
</script>


@stop