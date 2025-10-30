@extends('themes.default1.layouts.master')
@section('title')
    Whatsapp-Users
@stop
@section('content-header')

    <div class="col-sm-6 md-6">
        <h1>WhatsApp Users</h1>
    </div>
    <div class="col-sm-6 md-6">
        <ol class="breadcrumb float-sm-right">
            <li class="breadcrumb-item"><a href="{{url('/')}}"><i class="fa fa-dashboard"></i> {{ __('message.home')}}</a></li>
            <li class="breadcrumb-item"><a href="{{url('settings')}}"> {{ __('message.settings')}}</a></li>
            <li class="breadcrumb-item active">Whatsapp-Users</li>
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
                            <th>UserName</th>
                            <th>PhoneNumber</th>
                            <th>WabaId</th>
                            <th>PhoneNumberId</th>
                            <th>BusinessId</th>
                            <th>CreatedAt</th>
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
                { data: 'WabaId', name: 'WabaId', orderable: false, searchable: false },
                { data: 'PhoneNumberId', name: 'PhoneNumberId', orderable: false, searchable: false },
                { data: 'BusinessId', name: 'BusinessId', orderable: false, searchable: false },
                { data: 'created_at', name: 'CreatedAt', orderable: false, searchable: false }

            ]
        });
    });
</script>
    @stop