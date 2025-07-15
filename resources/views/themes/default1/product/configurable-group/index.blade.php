@extends('themes.default1.layouts.master')
@section('title')
    Configurable Groups
@stop
@section('content-header')
    <div class="col-sm-6 md-6">
        <h1>Configurable Groups</h1>
    </div>
    <div class="col-sm-6 md-6">
        <ol class="breadcrumb float-sm-right">
            <li class="breadcrumb-item"><a href="{{url('/')}}"><i class="fa fa-dashboard"></i> {{ __('message.home') }}</a></li>
            <li class="breadcrumb-item active">Configurable Groups</li>
        </ol>
    </div>
@stop
@section('content')
    <div class="card card-secondary card-outline">
        <div class="card-header">
            <div id="alertMessage12"></div>
            <div id="error"></div>
            <div class="d-flex justify-content-between align-items-center mb-2">
                <h5 class="mb-0">Configurable Options Group</h5>
                <a href="{{ url('configurable-group-create') }}" class="btn btn-default btn-sm">
                    <span class="fas fa-plus"></span>&nbsp;Create Configurable Group
                </a>
            </div>

        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-12">
                    <div class="table-responsive">

                        <table id="custom-table" class="table table-striped table-bordered display">
                            <thead>
                            <tr>
                                <th>Group Name</th>
                                <th>Description</th>
                                <th>Products</th>
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
    <script>
        $(document).ready(function() {
            console.log('hii');
            changeDatatable();
        })
        function changeDatatable() {
            $('#custom-table').DataTable({
                processing: true,
                serverSide: true,
                stateSave: false,
                destroy: true,

                ajax: {
                    url : '{{url("configurable-groups-table")}}',
                    type : 'get',
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
                    { data: 'group_name', name: 'Group Name', orderable: true, searchable: true },
                    { data: 'group_description', name: 'Description', orderable: true, searchable: true },
                    { data: 'products', name: 'Products', orderable: false, searchable: false },
                    { data: 'action', name: 'Action', orderable: false, searchable: false }
                ]
            });
        };
    </script>
@stop


