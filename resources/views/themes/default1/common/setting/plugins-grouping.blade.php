@extends('themes.default1.layouts.master')
@section('title')
    Plugins Grouping
@stop
@section('content-header')
    <div class="col-sm-6 md-6">
        <h1>Plugins Grouping</h1>
    </div>
    <div class="col-sm-6 md-6">
        <ol class="breadcrumb float-sm-right">
            <li class="breadcrumb-item"><a href="{{url('/')}}"><i class="fa fa-dashboard"></i> {{ __('message.home') }}</a></li>
            <li class="breadcrumb-item"><a href="{{url('settings')}}"> {{ __('message.settings') }}</a></li>
            <li class="breadcrumb-item active">Plugins Grouping</li>
        </ol>
    </div>
@stop

@section('content')
    <div class="card card-secondary card-outline">
        <div class="card-header">
            <div id="alertMessage12"></div>
            <div id="error"></div>
            <h5>Create Options to the Product</h5>
        </div>
        <div class="card-body flex">
            <div class="row">
                    <div class="form-group col-sm-6">
                        <label>Product: </label>
                        <select class="form-control {{$errors->has('disk') ? ' is-invalid' : ''}}" name="product_id" id="product_id">
                            <option value="">{{__('message.choose')}}</option>
                            @foreach($product as $pro)
                                <option value="{{$pro->id}}">{{$pro->name}}</option>
                            @endforeach
                        </select>
                    </div>
                <div class="form-group col-sm-6">
                    <label>Description:</label>
                    <input type="text" name="option_description" id="option_description" class="form-control"/>
                </div>
            </div>
            <div class="row">
                <div class="form-group col-sm-6">
                    <label>Option:</label>
                    <input type="text" name="display_option" id="display_option" class="form-control"/>
                </div>
                <div class="form-group col-sm-6">
                    <label>Type:</label>
                    <select class="form-control {{$errors->has('disk') ? ' is-invalid' : ''}}" name="option_type" id="option_type">
                        <option value="">{{__('message.choose')}}</option>
                        <option value="number">Number</option>
                        <option value="text">Text</option>
                    </select>
                </div>
            </div>
            <div class="row">
                <div class="col-md-12">
                    <button type="button" class="btn btn-primary float-right" id="add_button">
                        Add
                    </button>
                </div>
            </div>

        </div>
    <div class="card-body">
        <div class="row">
            <div class="col-md-12">
                <div class="table-responsive">

                    <table id="custom-table" class="table table-striped table-bordered display">
                        <thead>
                        <tr>
                            <th>Option Name</th>
                            <th>Description</th>
                            <th>Type</th>
                            <th>Action</th>
                        </tr>
                        </thead>
                    </table>
                </div>
            </div>
        </div>
    </div>

    </div>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.datatables.net/1.10.22/js/jquery.dataTables.min.js"></script>
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.8/css/jquery.dataTables.min.css">
    <script src="https://cdn.datatables.net/1.13.8/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <script>
        $(document).on('click','#add_button',function(){
            var product_id=$('#product_id').val();
            var display_option=$('#display_option').val();
            var option_description=$('#option_description').val();
            var option_type=$('#option_type').val();

            $.ajax({

                url : '{{url("save-plugin-options")}}',
                type : 'post',
                data:{
                    'product_id':product_id,
                    'display_option':display_option,
                    'option_description':option_description,
                    'option_type':option_type,
                },
                success: function (response) {
                    setTimeout(function() {
                        location.reload();
                    }, 3000);
                    $('#alertMessage12').show();
                    var result =  '<div class="alert alert-success alert-dismissable"><button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button><strong><i class="fa fa-check"></i> {{ __('message.success') }}! </strong>'+response.message+'.</div>';
                    $('#alertMessage12').html(result);
                    $("#submit").html("<i class='fa fa-save'>&nbsp;&nbsp;</i>{{ __('message.save') }}");
                    setInterval(function(){
                        $('#alertMessage12').slideUp(3000);
                    }, 1000);

                },
            });
        })

        $('#product_id').on('change',function(){
            var id=$(this).val();
            changeDatatable(id);
        })

        function changeDatatable(id) {
            $('#custom-table').DataTable({
                processing: true,
                serverSide: true,
                stateSave: false,
                destroy: true,

                ajax: {
                    url : '{{url("get-option-info")}}',
                    type : 'get',
                    data:{
                        'product_id':id,
                    },
                },

                oLanguage: {
                    sLengthMenu: "_MENU_ Records per page",
                    sSearch: "<span style='right: 180px;'>Search:</span> ",
                    sProcessing: ' <div class="overlay dataTables_processing"><i class="fas fa-3x fa-sync-alt fa-spin" style=" margin-top: -25px;"></i><div class="text-bold pt-2">{{ __('message.loading') }}</div></div>'
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
                    lengthMenu:     "{{ __('message.length_menu') }}",
                    loadingRecords: "{{ __('message.loading_records') }}",
                    search:         "{{ __('message.table_search') }}",
                },

                // Apply 'no-sort' class only to specific targets (3rd and 4th columns)
                columnDefs: [
                    {
                        targets: [2, 3], // Status and Action columns
                        orderable: false
                    }
                ],

                columns: [
                    { data: 'display_option', name: 'Option Name', orderable: true, searchable: true },
                    { data: 'option_description', name: 'Description', orderable: true, searchable: true },
                    { data: 'option_type', name: 'Type', orderable: false, searchable: false },
                    { data: 'action', name: 'Action', orderable: false, searchable: false }
                ]
            });
        };


        function deleteOption(id) {

            var swl=swal.fire({
                title:"<h2 class='swal2-title custom-title'>{{Lang::get('message.Delete')}}",
                html: "<div class='swal2-html-container custom-content'>" +
                    "<div class='section-sa'>" +
                    "<p>Are you sure you want to delete this option?<span class='text-danger'></span></p></div>"+
                    "</div>",
                showCancelButton: true,
                cancelButtonText: "{{ __('message.cancel') }}",
                showCloseButton: true,
                position:"top",
                width:"600px",

                confirmButtonText: @json(trans('message.Delete')),
                confirmButtonColor: "#007bff",

            }).then((result)=> {
                if(id.length > 0){
                    if (result.isConfirmed) {

                        $.ajax({
                            url: "{!! url('option-delete') !!}",
                            method: "delete",
                            data: { 'id': id, },
                            success: function (data) {
                                if (data.success === true) {
                                    var result = '<div class="alert alert-success alert-dismissable"><button type="button" class="close" data-dismiss="alert" aria-label="{{ __('message.close') }}"><span aria-hidden="true">&times;</span></button><strong><i class="fa fa-check"></i>{{ __('message.success') }}! </strong>' + data.message + '!</div>';
                                    $('#alertMessage12').show();
                                    $('#error').hide();
                                    $('#alertMessage12').html(result);
                                    setInterval(function () {
                                        $('#alertMessage12').slideUp(5000);
                                        location.reload();
                                    }, 3000);
                                } else if (data.success === false) {
                                    $('#alertMessage12').hide();
                                    $('#error').show();
                                    var result = '<div class="alert alert-danger alert-dismissable"><button type="button" class="close" data-dismiss="alert" aria-label="{{ __('message.close') }}"><span aria-hidden="true">&times;</span></button><strong><i class="fa fa-ban"></i>{{ __('message.whoops') }} </strong> {{ __('message.something_wrong') }}<br>' + data.message + '!</div>';
                                    $('#error').html(result);
                                    setInterval(function () {
                                        $('#error').slideUp(5000);
                                        location.reload();
                                    }, 10000);
                                }
                            },
                            error: function (data) {
                                $('#successmsg').hide();
                                $('#error').show();
                                var result = '<div class="alert alert-danger alert-dismissable"><button type="button" class="close" data-dismiss="alert" aria-label="{{ __('message.close') }}"><span aria-hidden="true">&times;</span></button><strong><i class="fa fa-ban"></i>{{ __('message.whoops') }} </strong> {{ __('message.something_wrong') }}<br>' + data.responseJSON.message + '!</div>';
                                $('#error').html(result);
                                setInterval(function () {
                                    $('#error').slideUp(5000);
                                    location.reload();
                                }, 10000);
                            },

                        });
                    } else {
                        window.close();
                    }
                }else if (result.dismiss === Swal.DismissReason.cancel) {
                    window.close();             }
            })
            return false;
        }
    </script>

@stop