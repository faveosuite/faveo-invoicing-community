@extends('themes.default1.layouts.master')

@section('title', __('message.activity_log'))

@section('content-header')
    <div class="col-sm-6">
        <h1>{{ __('message.activity_logs') }}</h1>
    </div>
    <div class="col-sm-6">
        <ol class="breadcrumb float-sm-right">
            <li class="breadcrumb-item">
                <a href="{{ url('/') }}">
                    <i class="fa fa-dashboard"></i> {{ __('message.home') }}
                </a>
            </li>
            <li class="breadcrumb-item">
                <a href="{{ url('settings') }}">
                    <i class="fa fa-dashboard"></i> {{ __('message.settings') }}
                </a>
            </li>
            <li class="breadcrumb-item active">{{ __('message.activity_log') }}</li>
        </ol>
    </div>
@stop

@section('content')
    <style>
        #modalDescription table td {
            font-size: 13px;
            line-height: 1.4;
            word-break: break-word;
            white-space: normal;
            padding: 6px 8px;
            vertical-align: top;
        }
    </style>

    <div class="row">
        <div class="col-12">
            <div class="card card-secondary card-outline collapsed-card">
                <div class="card-header">
                    <h3 class="card-title">{{ __('message.filters') }}</h3>
                    <div class="card-tools">
                        <button type="button" class="btn btn-tool" id="tip-search" title="{{ __('message.expand') }}">
                            <i id="search-icon" class="fas fa-plus"></i>
                        </button>
                    </div>
                </div>

                <div class="card-body" id="advance-search" style="display:none;">
                    {!! html()->form('GET')->open() !!}
                    <div class="row">
                        <!-- NEW: Module Multi-select -->
                        <div class="col-md-6 col-lg-3 form-group">
                            {!! html()->label(__('message.module'))->for('module') !!}
                            <select name="module[]" id="module" class="form-control select2" multiple="multiple" style="width: 100%;">
                                @foreach($modules as $module)
                                    <option value="{{ $module }}">{{ ucfirst($module) }}</option>
                                @endforeach
                            </select>
                        </div>

                        <!-- NEW: Event Multi-select -->
                        <div class="col-md-6 col-lg-3 form-group">
                            {!! html()->label(__('message.event'))->for('event') !!}
                            <select name="event[]" id="event" class="form-control select2" multiple="multiple" style="width: 100%;">
                                <option value="created">{{ __('message.created') }}</option>
                                <option value="updated">{{ __('message.updated') }}</option>
                                <option value="deleted">{{ __('message.deleted') }}</option>
                                <option value="login">{{ __('message.login') }}</option>
                            </select>
                        </div>

                        <!-- NEW: Performed By Multi-select -->
                        <div class="col-md-6 col-lg-3 form-group">
                            {!! html()->label(__('message.performed_by'))->for('performed_by') !!}
                            <select name="performed_by[]" id="performed_by" class="form-control select2" multiple="multiple" style="width: 100%;">
                                @foreach($users as $user)
                                    <option value="{{ $user->id }}">
                                        {{ $user->first_name }} {{ $user->last_name }} < {{ $user->email }} >
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <!-- From Date -->
                        <div class="col-md-6 col-lg-3 form-group">
                            {!! html()->label(__('message.view_logs_from'))->for('from') !!}
                            <div class="input-group date" id="log_from" data-target-input="nearest">
                                <input type="text" name="log_from" value="{{ $from }}" id="from"
                                       class="form-control datetimepicker-input"
                                       autocomplete="off" data-target="#log_from" />
                                <div class="input-group-append" data-target="#log_from" data-toggle="datetimepicker">
                                    <div class="input-group-text"><i class="fa fa-calendar"></i></div>
                                </div>
                            </div>
                        </div>

                        <!-- Till Date -->
                        <div class="col-md-6 col-lg-3 form-group">
                            {!! html()->label(__('message.view_logs_till'))->for('till') !!}
                            <div class="input-group date" id="log_till" data-target-input="nearest">
                                <input type="text" name="log_till" value="{{ $till }}" id="till"
                                       class="form-control datetimepicker-input"
                                       autocomplete="off" data-target="#log_till" />
                                <div class="input-group-append" data-target="#log_till" data-toggle="datetimepicker">
                                    <div class="input-group-text"><i class="fa fa-calendar"></i></div>
                                </div>
                            </div>
                        </div>

                        <!-- Buttons -->
                        <div class="col-md-12">
                            <button type="submit" class="btn btn-secondary">
                                <i class="fa fa-search"></i> {{ __('message.apply') }}
                            </button>
                            <button type="button" id="reset" class="btn btn-secondary">
                                <i class="fa fa-sync-alt"></i> {{ __('message.reset') }}
                            </button>
                        </div>
                    </div>
                    {!! html()->form()->close() !!}
                </div>
            </div>
        </div>
    </div>

    <div class="card card-secondary card-outline">
        <div class="card-body">
            <table id="activity-table" class="table table-hover w-100">
                <thead>
                <tr>
                    <th>{{ __('message.module') }}</th>
                    <th>{{ __('message.event') }}</th>
                    <th>{{ __('message.role') }}</th>
                    <th>{{ __('message.performed_by') }}</th>
                    <th>{{ __('message.description') }}</th>
                    <th>{{ __('message.created_at') }}</th>
                </tr>
                </thead>
            </table>
        </div>
    </div>


    <div class="modal fade" id="readMoreModal" tabindex="-1" role="dialog" aria-labelledby="readMoreModalLabel"
         aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="readMoreModalLabel">{{ __('message.detailed_log_info') }}</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div id="modalDescription" class="table-responsive">
                        <table class="table table-striped table-bordered mb-0">
                            <tbody></tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="deleteSystemLogModal" tabindex="-1" role="dialog" aria-labelledby="deleteSystemLogModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-md" role="document">
            <div class="modal-content">

                <div class="modal-header">
                    <h5 class="modal-title" id="deleteSystemLogModalLabel">{{ __('log::lang.delete_logs') }}</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>

                <div class="modal-body">
                    <div id="delete-system-alert"></div>

                    <!-- To Date -->
                    <div class="form-group">
                        <label for="deleteSystemToDate">{{ __('log::lang.delete_logs_entries') }}</label>
                        <input type="date" class="form-control" id="deleteSystemToDate" name="to_date" required />
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">
                        {{ __('log::lang.cancel') }}
                    </button>
                    <button type="submit" class="btn btn-primary" id="confirmSystemDeleteBtn">
                        {{ __('log::lang.delete_logs') }}
                    </button>
                </div>

            </div>
        </div>
    </div>


    <!-- DataTables -->
    <link rel="stylesheet" href="https://cdn.datatables.net/1.10.22/css/jquery.dataTables.min.css">
    <script src="https://cdn.datatables.net/1.10.22/js/jquery.dataTables.min.js"></script>

    <script type="text/javascript">
        $(document).ready(function () {

            $('.select2').select2({ placeholder: "{{ __('message.select_option') }}" });

            // Reset button
            $('#reset').click(function () {
                $('#from, #till').val('');
                $('#module, #event, #performed_by').val(null).trigger('change');
                $('#activity-table').DataTable().ajax.reload();
            });


            // Datatable setup
            const table = $('#activity-table').DataTable({
                serverSide: true,
                processing: true,
                scrollX: true,
                ajax: {
                    url: '{!! route('get-activity') !!}',
                    data: function (d) {
                        d.log_from = $('#from').val();
                        d.log_till = $('#till').val();
                        d.module = $('#module').val();
                        d.event = $('#event').val();
                        d.performed_by = $('#performed_by').val();
                    },
                    error: function (xhr) {
                        if (xhr.status === 401) {
                            alert("@lang('message.session_expired')");
                            window.location.href = '/login';
                        }
                    }
                },
                language: {
                    processing: '<div class="overlay dataTables_processing"><i class="fas fa-3x fa-sync-alt fa-spin"></i></div>',
                    paginate: {
                        first: "{{ __('message.paginate_first') }}",
                        last: "{{ __('message.paginate_last') }}",
                        next: "{{ __('message.paginate_next') }}",
                        previous: "{{ __('message.paginate_previous') }}"
                    },
                    emptyTable: "{{ __('message.empty_table') }}",
                    info: "{{ __('message.datatable_info') }}",
                    zeroRecords: "{{ __('message.no_matching_records_found') }}",
                    infoEmpty: "{{ __('message.info_empty') }}",
                    infoFiltered: "{{ __('message.info_filtered') }}",
                    lengthMenu: "{{ __('message.length_menu') }}",
                    loadingRecords: "{{ __('message.loading_records') }}",
                    search: "{{ __('message.table_search') }}"
                },
                order: [[ 5, "desc" ]],
                columns: [
                    { data: 'module', orderable: false },
                    { data: 'event'},
                    { data: 'role', orderable: false },
                    { data: 'performed_by'},
                    {
                        data: 'description',
                        orderable: false,
                        render: function (data, type, row) {
                            if (!data) return '';

                            const details = row.detailed_properties;
                            const hasContent =
                                details &&
                                (
                                    (Array.isArray(details) && details.length > 0) ||
                                    (typeof details === 'object' && Object.keys(details).length > 0)
                                );

                            const readMore = hasContent
                                ? `<a href="#" class="text-primary read-more-link" data-description='${JSON.stringify(details)}'>
                   {{ __('message.read_more_caps') }}
                                </a>`
                                : '';

                            return `<div>${data}${hasContent ? '<br>' + readMore : ''}</div>`;
                        }
                    },
                    { data: 'created_at'},
                ],
                drawCallback: function () {
                    $('[data-toggle="tooltip"]').tooltip({ container: 'body' });
                },
                preDrawCallback: function(settings) {
                    const urlParams = new URLSearchParams(window.location.search);

                    const hasSearchParams =
                        urlParams.has('module[]') ||
                        urlParams.has('module') ||
                        urlParams.has('event[]') ||
                        urlParams.has('event') ||
                        urlParams.has('performed_by[]') ||
                        urlParams.has('performed_by') ||
                        urlParams.has('log_from') ||
                        urlParams.has('log_till');

                    if (hasSearchParams) {
                        $('#advance-search').show();
                        $('#tip-search').attr('title', "{{ __('message.collapse') }}");
                        $('#search-icon').removeClass('fa-plus').addClass('fa-minus');
                    } else {
                        $('#advance-search').collapse('hide');
                    }
                },
            });

            $('form').on('submit', function(e) {
                e.preventDefault();
                table.ajax.reload();
            });


            // Date pickers
            $('#log_from, #log_till').datetimepicker({ format: 'L' });

            // Read More modal handler
            $(document).on('click', '.read-more-link', function (e) {
                e.preventDefault();

                let details = $(this).data('description');

                // Normalize data to an array
                try {
                    details = (typeof details === 'string') ? JSON.parse(details) : details;
                } catch {
                    details = details ? [details] : [];
                }

                // Build table rows efficiently using map + join
                const rows = details
                    .map((item, i) => `
            <tr>
                <td>${item}</td>
            </tr>
        `)
                    .join('');

                // Update modal body and show
                $('#modalDescription tbody').html(rows);
                $('#readMoreModal').modal('show');
            });

            $('.dataTables_filter').append(`
            <button type="button" class="btn btn-secondary" data-toggle="modal" data-target="#deleteSystemLogModal">
                <i class="fas fa-trash-alt"></i>
            </button>
     <button id="refresh-table-btn" class="btn btn-link p-2" data-toggle="tooltip" title="{{ __('message.refresh') }}" style="text-decoration: none;">
         <i class="fas fa-sync-alt text-secondary" id="refresh-icon" style="font-size: 1.2rem; transition: transform 0.5s ease;"></i>
     </button>
 `);
            $(document).on('click', '#refresh-table-btn', function() {
                table.ajax.reload();
            });

            document.getElementById('deleteSystemToDate').value = new Date().toISOString().split('T')[0];

            $('#confirmSystemDeleteBtn').on('click', function (e) {
                e.preventDefault();

                const deleteToDateEl = $('#deleteSystemToDate');
                let toDate = deleteToDateEl.val();

                let hasError = false;

                // Validation
                if (!toDate) {
                    deleteToDateEl.addClass('is-invalid');
                    hasError = true;
                }

                if (hasError) return;

                // Disable button while processing
                let $btn = $(this);

                $.ajax({
                    url: "{{ url('logs/delete') }}",
                    type: "DELETE",
                    data: {
                        _token: "{{ csrf_token() }}",
                        to_date: toDate,
                        log_types: ['systemLogs']
                    },
                    beforeSend: function () {
                        $btn.prop('disabled', true).text('{{ __("log::lang.deleting") }}...');
                    },
                    success: function (response) {
                        helper.showAlert({
                            message: response.message || '{{ __("log::lang.logs_deleted_successfully") }}',
                            type: 'success',
                            autoDismiss: 5000,
                            containerSelector: '#delete-system-alert',
                        });

                        setTimeout(function() {
                            window.location.reload();
                        }, 5000)
                    },
                    error: function (xhr) {
                        helper.showAlert({
                            message: xhr.responseJSON?.message || '{{ __("log::lang.error_deleting_logs") }}',
                            type: 'error',
                            autoDismiss: 5000,
                            containerSelector: '#delete-system-alert',
                        });
                    },
                    complete: function () {
                        $btn.prop('disabled', false).text('{{ __("log::lang.delete_logs") }}');
                    }
                });
            });

            // Sidebar active state
            $('ul.nav-sidebar a#setting').addClass('active')
                .parentsUntil(".nav-sidebar > .nav-treeview").addClass('menu-open')
                .prev('a').addClass('active');
        });
    </script>

@stop
