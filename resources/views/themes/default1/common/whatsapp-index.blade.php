@extends('themes.default1.layouts.master')
@section('title')
    {{ __('message.whatsapp_users') }}
@stop
@section('content-header')

    <div class="col-sm-6 md-6">
        <h1>{{ __('message.whatsapp_users') }}</h1>
    </div>
    <div class="col-sm-6 md-6">
        <ol class="breadcrumb float-sm-right">
            <li class="breadcrumb-item"><a href="{{url('/')}}"><i class="fa fa-dashboard"></i> {{ __('message.home')}}</a></li>
            <li class="breadcrumb-item"><a href="{{url('settings')}}"> {{ __('message.settings')}}</a></li>
            <li class="breadcrumb-item active">{{ __('message.whatsapp_users') }}</li>
        </ol>
    </div><!-- /.col -->
@stop
@section('content')
<?php
$products= App\Model\Product\Product::get();
?>

        <!-- /.box-header -->
        <div class="card-body">
            <div id="alertMessage12"></div>

            <div class="row" style="height:760px">
                <div class="col-md-12">
                    <table id="custom-table" class="table display" cellspacing="0" width="100%">
                        <thead>
                        <tr>
                            <th>{{__('message.user_name')}}</th>
                            <th>{{__('message.phone_number')}}</th>
                            <th>{{__('message.waba_id')}}</th>
                            <th>{{__('message.phone_number_id')}}</th>
                            <th>{{__('message.business_id')}}</th>
                            <th>{{__('message.create_at')}}</th>
                            <th>Action</th>

                        </tr>
                        </thead>
                    </table>
                </div>
            </div>
        </div>
    </div>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <link rel="stylesheet" href="https://cdn.datatables.net/1.10.21/css/jquery.dataTables.min.css">
    <script src="https://cdn.datatables.net/1.10.21/js/jquery.dataTables.min.js"></script>
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
    $(document).ready(function() {
        $('#products').select2({
            placeholder: "Select products",
            allowClear: true
        });
    });
</script>

<script>
    $(document).ready(function () {
        $('#custom-table').DataTable({
            processing: true,
            serverSide: true,
            stateSave: false,

            ajax: "{{ url('whatsapp-users-table') }}", // Calls the separate function

            oLanguage: {
                sLengthMenu: "_MENU_ Records per page",
                sSearch: "<span style='right: 180px;'>Search:</span> ",
                {{--sProcessing: ' <div class="overlay dataTables_processing"><i class="fas fa-3x fa-sync-alt fa-spin" style=" margin-top: -25px;"></i><div class="text-bold pt-2">{{ __('message.loading') }}</div></div>'--}}
                sProcessing: ' <div class="overlay dataTables_processing"><i class="fas fa-3x fa-sync-alt fa-spin" style=" margin-top: -25px;"></i><div class="text-bold pt-2">{!! __('message.loading') !!}</div></div>'

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
                { data: 'UserName', name: 'UserName', orderable: true, searchable: true },
                { data: 'PhoneNumber', name: 'PhoneNumber', orderable: true, searchable: true },
                { data: 'WabaId', name: 'WabaId', orderable: true, searchable: true },
                { data: 'PhoneNumberId', name: 'PhoneNumberId', orderable: true, searchable: true },
                { data: 'BusinessId', name: 'BusinessId', orderable: true, searchable: true },
                { data: 'created_at', name: 'created_at', orderable: true, searchable: true },
                { data: 'action', name: 'action', orderable: false, searchable: false }

            ]
        });
    });

    $(document).on('click', '.copy-btn', function() {
        const button = $(this);
        const token = button.data('token');
        const message = button.siblings('.copy-msg');

        navigator.clipboard.writeText(token).then(() => {
            message.fadeIn(200).delay(1000).fadeOut(400);
        });
    });

    function deleteWhatsappUser(id) {
        var id = id;
        var orderId = orderId;
        var swl=swal.fire({
            title:"<h2 class='swal2-title custom-title'>{{Lang::get('message.Delete')}}",
            html: "<div class='swal2-html-container custom-content'>" +
                "<div class='section-sa'>" +
                "<p>Are you sure you want to delete this number?" +"?</p></div>"+
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
                        url: "{!! url('whatsapp-deregister') !!}",
                        method: "delete",
                        data: { 'id': id},
                        success: function (data) {
                            if (data.success === true) {
                                var result = '<div class="alert alert-success alert-dismissable"><button type="button" class="close" data-dismiss="alert" aria-label="{{ __('message.close') }}"><span aria-hidden="true">&times;</span></button><strong><i class="fa fa-check"></i>{{ __('message.success') }}! </strong>' + data.message + '!</div>';
                                $('#successmsg').show();
                                $('#error').hide();
                                $('#successmsg').html(result);
                                setInterval(function () {
                                    $('#successmsg').slideUp(5000);
                                    location.reload();
                                }, 3000);
                            } else if (data.success === false) {
                                $('#successmsg').hide();
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
                // Action if "No" is clicked
                window.close();             }
        })
        return false;
    }


</script>
    @stop