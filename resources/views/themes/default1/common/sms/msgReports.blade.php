@extends('themes.default1.layouts.master')
@section('title')
    Users
@stop


@section('content-header')
    <div class="col-sm-6">
        <h1>MSG91 Reports</h1>
    </div>
    <div class="col-sm-6">
        <ol class="breadcrumb float-sm-right">
            <li class="breadcrumb-item"><a href="{{url('/')}}"><i class="fa fa-dashboard"></i> Home</a></li>
            <li class="breadcrumb-item"><a href="{{url('settings')}}"> Settings</a></li>
            <li class="breadcrumb-item active">MSG91 Reports</li>
        </ol>
    </div><!-- /.col -->
@stop

@section('content')
    <div class="card card-secondary card-outline">
        <div class="card-body table-responsive">
            <div id="loading" style="display: none;">
                <div class="spinner"></div>
            </div>

            <table id="reports-table" class="table display" cellspacing="0" width="100%">
                <thead>
                <tr>
{{--                    <th class="no-sort"><input type="checkbox" name="select_all" onchange="checking(this)"></th>--}}
                    <th>Request ID</th>
                    <th>User</th>
                    <th>Email</th>
                    <th>Mobile Number</th>
                    <th>Sender ID</th>
                    <th>Status</th>
                    <th>Failure Reason</th>
                    <th>Date</th>
                </tr>
                </thead>

            </table>
        </div>
    </div>
    <link rel="stylesheet" href="https://cdn.datatables.net/1.10.22/css/jquery.dataTables.min.css">
    <script src="https://cdn.datatables.net/buttons/1.0.3/js/dataTables.buttons.min.js"></script>
    <script src="https://cdn.datatables.net/1.10.22/js/jquery.dataTables.min.js"></script>
    <script>
        $(document).ready(function() {
            // Initialize DataTable with proper configuration
            var reportsTable = $('#reports-table').DataTable({
                processing: true,
                serverSide: true,
                stateSave: false,
                ajax: {
                    url: '{!! url('getMsgReports') !!}',
                    type: 'GET',
                    dataType: 'json',
                    error: function(xhr) {
                        if (xhr.status == 401) {
                            alert('Your session has expired. Please login again to continue.');
                            window.location.href = '/login';
                        }
                    },
                    dataFilter: function(data) {
                        var json = jQuery.parseJSON(data);
                        if (json.data.length === 0) {
                            $('#export-report-btn').hide(); // Hide export button
                        } else {
                            $('#export-report-btn').show(); // Show export button
                        }
                        return data;
                    }
                },
                language: {
                    lengthMenu: "_MENU_ Records per page",
                    search: "<span style='margin-right: 10px;'>Search:</span>",
                    processing: '<div class="overlay dataTables_processing"><i class="fas fa-3x fa-sync-alt fa-spin"></i><div class="text-bold pt-2">Loading...</div></div>'
                },
                columnDefs: [{
                    targets: 'no-sort',
                    orderable: false,
                    order: []
                }],
                columns: [
                    {data: 'request_id', name: 'request_id'},
                    {
                        data: 'user.full_name',
                        name: 'user.full_name',
                        render: function (data, type, row) {
                            const baseUrl = @json(url('clients'));
                            if (row.user?.id && data) {
                                return `<a href="${baseUrl}/${row.user.id}" class="text-primary">${data}</a>`;
                            }
                            return '---';
                        }
                    },
                    {
                      data: 'user.email',
                      name: 'user.email',
                    },
                    {data: 'mobile_number', name: 'mobile_number'},
                    {data: 'formatted_sender_id', name: 'sender_id'},
                    {
                        data: 'readable_status',
                        name: 'status',
                        render: function (data, type, row) {
                            const statusClasses = {
                                '0': 'badge badge-warning',
                                '1': 'badge badge-success',
                            };
                            const badgeClass = statusClasses[row.status] || 'badge badge-danger';
                            return `<span class="${badgeClass}">${data}</span>`;
                        }
                    },
                    {
                        data: 'failure_reason',
                        name: 'failure_reason',
                        render: (data, type, row) => {
                            if (data) {
                                return `<span>${data}</span>`;
                            }
                            return '---';
                        }
                    },
                    {data: 'date', name: 'date'}
                ],
                drawCallback: function(settings) {
                    // Initialize tooltips after table draw
                    $('[data-toggle="tooltip"]').tooltip({
                        container: 'body'
                    });
                    $('.loader').hide();
                },
                preDrawCallback: function(settings) {
                    // Check for URL parameters
                    var urlParams = new URLSearchParams(window.location.search);
                    var hasSearchParams = urlParams.has('request_id') ||
                        urlParams.has('mobile_number') ||
                        urlParams.has('sender_id') ||
                        urlParams.has('status') ||
                        urlParams.has('date') ||
                        urlParams.has('failure_reason');

                    if (hasSearchParams) {
                        $("#advance-search").show();
                        $('#tip-search').attr('title', 'Collapse');
                        $('#search-icon').removeClass('fa-plus').addClass('fa-minus');
                    } else {
                        $("#advance-search").collapse('hide');
                    }
                    $('.loader').show();
                }
            });

            // Add refresh button next to search bar
            $('.dataTables_filter').append(`
    <button id="refresh-table-btn" class="btn btn-link p-2" data-toggle="tooltip" title="Refresh Table" style="text-decoration: none;">
        <i class="fas fa-sync-alt text-secondary" id="refresh-icon" style="font-size: 1.2rem; transition: transform 0.5s ease;"></i>
    </button>
`);


            // Handle refresh button click
            $(document).on('click', '#refresh-table-btn', function() {
                reportsTable.ajax.reload(null, false);
            });

            // Handle select all checkbox
            $('input[name="select_all"]').on('change', function() {
                $('.row-checkbox').prop('checked', $(this).prop('checked'));
            });
        });
    </script>
@stop