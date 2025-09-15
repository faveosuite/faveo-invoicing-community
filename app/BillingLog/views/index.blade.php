@extends('themes.default1.layouts.master')
@section('title')
    {{ __('log::lang.system_logs') }}
@stop
@section('content-header')
@stop

@section('content')
    <style scoped>
        .icons-color {
            color: #3c8dbc;
        }

        .settingiconblue {
            padding-top: 0.5rem;
            padding-bottom: 0.5rem;
        }

        .settingdivblue {
            display: flex;
            align-items: center;
            justify-content: center;
            height: 100%;
            width: 80px;
            margin: 0 auto;
            text-align: center;
            border: 5px solid #C4D8E4;
            border-radius: 100%;
            padding-top: 5px;
            cursor: pointer;
        }

        .settingdivblue span {
            text-align: center;
        }

        .selected_type {
            border-color: #3c8dbc !important;
        }

        .fw_400 { font-weight: 400; }

        .settingiconblue p{
            text-align: center;
            font-size: 16px;
            word-wrap: break-word;
            font-variant: small-caps;
            font-weight: 500;
            line-height: 30px;
        }

        .filter-box {
            display: none;
        }

        .filter-box.active {
            display: block;
        }

        .table-container table {
            display: none;
        }

        .table-container table.active {
            display: table;
        }

        .category-box {
            transition: all 0.3s ease;
        }

        .selector {
            cursor: pointer;
        }

        .category-box:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
        }

        .category-box.selected {
            border: 2px solid #3c8dbc;
            background-color: #f8f9fa;
        }
        .code-container {
            position: relative;
            background-color: #000;
            border-radius: 8px;
            margin: 15px 0;
        }

        .code-block {
            background-color: #000 !important;
            color: white;
            font-family: 'Courier New', monospace;
            font-size: 18px;
            padding: 20px;
            padding-top: 50px;
            border-radius: 8px;
            overflow: auto;
            max-height: 600px;
            white-space: pre;
            border: none;
        }

        .copy-btn {
            position: absolute;
            top: 10px;
            right: 10px;
            background: #333;
            border: 1px solid #555;
            color: #fff;
            padding: 5px 10px;
            border-radius: 4px;
            cursor: pointer;
            font-size: 12px;
            z-index: 10;
            transition: all 0.3s ease;
        }

        .copy-btn:hover {
            background: #555;
            border-color: #777;
        }

        .copy-btn.copied {
            background: #28a745;
            border-color: #28a745;
        }

        .copy-btn i {
            margin-right: 5px;
        }

        /* Custom scrollbar for webkit browsers */
        .code-block::-webkit-scrollbar {
            width: 8px;
            height: 8px;
        }

        .code-block::-webkit-scrollbar-track {
            background: #222;
            border-radius: 4px;
        }

        .code-block::-webkit-scrollbar-thumb {
            background: #888;
            border-radius: 4px;
        }

        .code-block::-webkit-scrollbar-thumb:hover {
            background: #aaa;
        }

        .scrollable-email-body {
            max-height: 60vh;
            overflow-y: auto;
        }

        #emailBody img {
            max-width: 20%;
        }

        /* Modern Bootstrap DateTimePicker Styles */
        .bootstrap-datetimepicker-widget {
            border: none !important;
            border-radius: 15px !important;
            box-shadow: none !important;
            background: transparent !important;
        }

        .bootstrap-datetimepicker-widget table {
            border: none !important;
            background: transparent !important;
        }

        .bootstrap-datetimepicker-widget table td,
        .bootstrap-datetimepicker-widget table th {
            border: none !important;
            padding: 8px !important;
            text-align: center;
            vertical-align: middle;
            border-radius: 8px !important;
            transition: all 0.2s ease;
        }

        .bootstrap-datetimepicker-widget table th {
            color: #6c757d !important;
            font-weight: 600;
            font-size: 0.85rem;
        }

        .bootstrap-datetimepicker-widget table td.day {
            color: #495057 !important;
            cursor: pointer;
        }

        .bootstrap-datetimepicker-widget table td.active,
        .bootstrap-datetimepicker-widget table td.active:hover {
            color: white !important;
            transform: scale(1.1);
        }

        .bootstrap-datetimepicker-widget table td.old,
        .bootstrap-datetimepicker-widget table td.new {
            color: #adb5bd !important;
        }

        .bootstrap-datetimepicker-widget .datepicker-days .table-condensed {
            border-spacing: 2px;
        }

        .date-box {
            box-shadow: 0 0 1px rgba(0,0,0,.125), 0 1px 3px rgba(0,0,0,.2);
            border-radius: .25rem;
            background-color: #fff;

            display: flex;
            justify-content: center;  /* center calendar */
            align-items: center;

            margin-bottom: 1rem;
            padding: .5rem;

            width: 320px;   /* fixed width for all cards */
            height: 320px;  /* fixed height for all cards */

            position: relative;
            flex-shrink: 0;
            box-sizing: border-box;
            overflow: hidden;
        }


    </style>

    <div class="card card-secondary card-outline">
        <div class="card-header">
            <h3 class="card-title">{{ __('log::lang.system_logs') }}</h3>
            <button type="button" class="btn btn-secondary float-right" data-toggle="modal" data-target="#deleteLogModal">
                <i class="fas fa-trash-alt"></i>
            </button>
        </div>
        <div class="card-body">
            <div class="row">
                <!-- Cron Logs -->
                <div class="col-md-2 col-sm-6">
                    <div class="settingiconblue">
                        <div class="settingdivblue" data-type="cron">
                            <a class="icons-color">
                                <span class="fa-stack fa-2x">
                                    <i class="fas fa-clock fa-stack-1x"></i>
                                </span>
                            </a>
                        </div>
                        <div class="text-center text-sm fw_400">{{ __('log::lang.cron_logs') }}</div>
                    </div>
                </div>

                <!-- Exception Logs -->
                <div class="col-md-2 col-sm-6">
                    <div class="settingiconblue">
                        <div class="settingdivblue" data-type="exception">
                            <a class="icons-color">
                                <span class="fa-stack fa-2x">
                                    <i class="fas fa-bug fa-stack-1x"></i>
                                </span>
                            </a>
                        </div>
                        <div class="text-center text-sm fw_400">{{ __('log::lang.exception_logs') }}</div>
                    </div>
                </div>

                <!-- Mail Logs -->
                <div class="col-md-2 col-sm-6">
                    <div class="settingiconblue">
                        <div class="settingdivblue" data-type="mail">
                            <a class="icons-color">
                                <span class="fa-stack fa-2x">
                                    <i class="fas fa-envelope fa-stack-1x"></i>
                                </span>
                            </a>
                        </div>
                        <div class="text-center text-sm fw_400">{{ __('log::lang.mail_logs') }}</div>
                    </div>
                </div>
            </div>

            <!-- Filter Section for each log type -->
            <div class="card card-secondary card-outline mt-3">
                <div class="card-header">
                    <h3 class="card-title">{{ __('log::lang.filter_logs') }}</h3>
                </div>
                <div class="card-body">
                    <!-- Cron Logs Filter -->
                    <div id="cron-filter" class="filter-box">
                        <div class="row">
                            <div class="col-md-9">
                                <div class="row" id="cron-category-container">
                                    <!-- Cron categories will be loaded here -->
                                </div>
                            </div>
                            <div class="col-md-3 d-flex justify-content-center">
                                <div class="date-card date-box">
                                    <div id="cron-date"></div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Exception Logs Filter -->
                    <div id="exception-filter" class="filter-box">
                        <div class="row">
                            <div class="col-md-9">
                                <div class="row" id="exception-category-container">
                                    <!-- Exception categories will be loaded here -->
                                </div>
                            </div>
                            <div class="col-md-3 d-flex justify-content-center">
                                <div class="date-card date-box">
                                    <div id="exception-date"></div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Mail Logs Filter -->
                    <div id="mail-filter" class="filter-box">
                        <div class="row">
                            <div class="col-md-9">
                                <div class="row" id="mail-category-container">
                                    <!-- Mail categories will be loaded here -->
                                </div>
                            </div>
                            <div class="col-md-3 d-flex justify-content-center">
                                <div class="date-card date-box">
                                    <div id="mail-date"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div id="log-table-alert"></div>

            <div id="logs-card" class="card card-secondary card-outline mt-3" style="display: none;">
                <div class="card-header">
                    <h3 class="card-title">{{ __('log::lang.logs') }}</h3>
                    <div id="current-selection" class="float-right text-muted"></div>
                </div>
                <div class="card-body">
                    <div class="table-container">
                        <!-- Cron Logs Table -->
                        <table id="cron-table" class="table table-hover w-100">
                            <thead>
                            <tr>
                                <th>{{ __('log::lang.command') }}</th>
                                <th>{{ __('log::lang.description') }}</th>
                                <th>{{ __('log::lang.duration') }}</th>
                                <th>{{ __('log::lang.created_at') }}</th>
                                <th>{{ __('log::lang.status') }}</th>
                            </tr>
                            </thead>
                        </table>

                        <!-- Exception Logs Table -->
                        <table id="exception-table" class="table table-hover w-100">
                            <thead>
                            <tr>
                                <th>{{ __('log::lang.file') }}</th>
                                <th>{{ __('log::lang.line') }}</th>
                                <th>{{ __('log::lang.message') }}</th>
                                <th>{{ __('log::lang.trace') }}</th>
                                <th>{{ __('log::lang.created_at') }}</th>
                            </tr>
                            </thead>
                        </table>

                        <!-- Mail Logs Table -->
                        <table id="mail-table" class="table table-hover w-100">
                            <thead>
                            <tr>
                                <th>{{ __('log::lang.sender_mail') }}</th>
                                <th>{{ __('log::lang.receiver_mail') }}</th>
                                <th>{{ __('log::lang.cc') }}</th>
                                <th>{{ __('log::lang.bcc') }}</th>
                                <th>{{ __('log::lang.subject') }}</th>
                                <th>{{ __('log::lang.created_at') }}</th>
                                <th>{{ __('log::lang.updated_at') }}</th>
                                <th>{{ __('log::lang.status') }}</th>
                                <th>{{ __('log::lang.action') }}</th>
                            </tr>
                            </thead>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal for full log content -->
    <div class="modal fade" id="codeModal" tabindex="-1" role="dialog" aria-labelledby="codeModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-xl modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="codeModalLabel">
                        {{ __('log::lang.log_details') }}
                    </h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="code-container">
                        <button id="copy-btn" class="copy-btn btn btn-sm btn-outline-secondary mb-2">
                            <i class="fas fa-copy"></i> <span class="copy-text">{{ __('log::lang.copy') }}</span>
                        </button>
                        <pre class="code-block" id="codeContent"></pre>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default" data-dismiss="modal">{{ __('log::lang.close') }}</button>
                    <div class="ml-auto"></div>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="retryLogModal" tabindex="-1" role="dialog" aria-labelledby="retryLogModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document"> <!-- Centering class added -->
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="retryLogModalLabel">{{ __('log::lang.retry_mail') }}</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    {{ __('log::lang.are_you_sure_you_want_to_retry_this_mail') }}
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">{{ __('log::lang.cancel') }}</button>
                    <button type="button" class="btn btn-primary" id="confirmRetryBtn">{{ __('log::lang.retry') }}</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Email Body Modal -->
    <div class="modal fade" id="emailModal" tabindex="-1" role="dialog" aria-labelledby="emailModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="emailModalLabel">{{ __('log::lang.email_subject') }}</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>

                <div class="modal-body scrollable-email-body">
                    <div id="emailBody">
                        <!-- Email content will load here -->
                    </div>
                </div>

                <div class="modal-footer justify-content-between">
                    <button type="button" class="btn btn-default" data-dismiss="modal">
                        {{ __('log::lang.cancel') }}
                    </button>
                </div>
            </div>
        </div>
    </div>
    <div class="modal fade" id="deleteLogModal" tabindex="-1" role="dialog" aria-labelledby="deleteLogModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-md" role="document">
            <div class="modal-content">

                <div class="modal-header">
                    <h5 class="modal-title" id="deleteLogModalLabel">{{ __('log::lang.delete_logs') }}</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>

                <div class="modal-body">
                    <div id="delete-alert"></div>
                    <!-- From Date -->
                    <div class="form-group">
                        <label for="deleteFromDate">{{ __('log::lang.from_date') }}</label>
                        <input type="date" class="form-control" id="deleteFromDate" name="from_date"/>
                    </div>

                    <!-- To Date -->
                    <div class="form-group">
                        <label for="deleteToDate">{{ __('log::lang.to_date') }}</label>
                        <input type="date" class="form-control" id="deleteToDate" name="to_date"/>
                    </div>

                    <!-- Inline Checkboxes -->
                    <div class="form-group">
                        <label>{{ __('log::lang.log_types') }}</label><br>
                        <div class="custom-control custom-checkbox d-inline mr-3">
                            <input type="checkbox" class="custom-control-input" id="deleteMailLogs" name="log_types[]" value="mail">
                            <label class="custom-control-label" for="deleteMailLogs">{{ __('log::lang.mail_logs') }}</label>
                        </div>
                        <div class="custom-control custom-checkbox d-inline mr-3">
                            <input type="checkbox" class="custom-control-input" id="deleteCronLogs" name="log_types[]" value="cron">
                            <label class="custom-control-label" for="deleteCronLogs">{{ __('log::lang.cron_logs') }}</label>
                        </div>
                        <div class="custom-control custom-checkbox d-inline">
                            <input type="checkbox" class="custom-control-input" id="deleteExceptionLogs" name="log_types[]" value="exception">
                            <label class="custom-control-label" for="deleteExceptionLogs">{{ __('log::lang.exception_logs') }}</label>
                        </div>
                    </div>

                    <div id="logTypesError" class="text-danger small"></div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">{{ __('log::lang.cancel') }}</button>
                    <button type="submit" class="btn btn-primary" id="confirmDeleteBtn">{{ __('log::lang.delete_logs') }}</button>
                </div>
            </div>
        </div>
    </div>

    @include('log::loader')


    <link rel="stylesheet" href="https://cdn.datatables.net/1.10.22/css/jquery.dataTables.min.css">
    <script src="https://cdn.datatables.net/1.10.22/js/jquery.dataTables.min.js"></script>
    <script>
        const SystemLogs = {
             selectedDates : {
                mail: new Date().toISOString().split('T')[0],
                cron: new Date().toISOString().split('T')[0],
                exception: new Date().toISOString().split('T')[0],
            },
            currentCategory: { mail: '', cron: '', exception: '' },
            currentType: '',
            tables: { mail: null, cron: null, exception: null },
            isLoadingCategories: false, // Prevent double loading

            init() {
                this.bindEvents();
                this.showLogType('cron');
                // Set default dates for delete modal
                const today = new Date().toISOString().split('T')[0];
                document.getElementById('deleteFromDate').value = today;
                document.getElementById('deleteToDate').value = today;
            },

            bindEvents() {
                // Fixed: Use proper event binding
                document.querySelectorAll('.settingdivblue').forEach(el => {
                    el.addEventListener('click', (e) => {
                        e.preventDefault();
                        const type = el.getAttribute('data-type');
                        this.showLogType(type);
                    });
                });

                ['mail', 'cron', 'exception'].forEach(type => {
                    const dateInput = document.getElementById(`${type}-date`);
                    if (!dateInput) return;

                    $(dateInput).datetimepicker({
                        inline: true,
                        format: 'YYYY-MM-DD',
                        viewMode: 'days',
                        date: this.selectedDates[type]
                    });

                    $(dateInput).on('change.datetimepicker', (e) => {
                        if (e.date) {
                            this.selectedDates[type] = e.date.format('YYYY-MM-DD');
                            if (this.currentType === type && !this.isLoadingCategories) {
                                this.currentCategory[type] = '';
                                this.loadCategories(type);
                                this.hideLogsCard();
                            }
                        }
                    });
                });
                
                // Delete logs functionality
                document.getElementById('confirmDeleteBtn').addEventListener('click', () => {
                    this.deleteLogs();
                });
            },

            showLogType(type) {
                this.currentType = type;

                // Fixed: Update selected UI properly
                document.querySelectorAll('.settingdivblue').forEach(el => {
                    el.classList.remove('selected_type');
                });

                // Add selected class to clicked element
                const selectedElement = document.querySelector(`.settingdivblue[data-type="${type}"]`);
                if (selectedElement) {
                    selectedElement.classList.add('selected_type');
                }

                // Toggle filter boxes
                document.querySelectorAll('.filter-box').forEach(box => {
                    box.classList.remove('active');
                });
                const filterBox = document.getElementById(`${type}-filter`);
                if (filterBox) {
                    filterBox.classList.add('active');
                }

                // Hide logs card initially
                this.hideLogsCard();

                // Load categories only once when switching log type
                this.loadCategories(type);
            },

            hideLogsCard() {
                const logsCard = document.getElementById('logs-card');
                if (logsCard) {
                    logsCard.style.display = 'none';
                }
                // Also hide individual tables
                document.querySelectorAll('.table-container table').forEach(table => {
                    table.classList.remove('active');
                });
            },

            showLogsCard(type) {
                const logsCard = document.getElementById('logs-card');
                if (logsCard) {
                    logsCard.style.display = 'block';
                }
                // Hide all tables first
                document.querySelectorAll('.table-container table').forEach(table => {
                    table.classList.remove('active');
                });
                // Show the specific table
                const table = document.getElementById(`${type}-table`);
                if (table) {
                    table.classList.add('active');
                }
            },

            loadCategories(type) {
                // Prevent double loading
                if (this.isLoadingCategories) {
                    return;
                }

                const container = document.getElementById(`${type}-category-container`);

                if (!container) {
                    return;
                }

                const date = this.selectedDates[type];

                this.isLoadingCategories = true; // Set loading flag

                helper.globalLoader.show();

                $.ajax({
                    url: '{{ url("log-category-list") }}',
                    method: 'GET',
                    data: { date, log_type: type },
                    success: (response) => {
                        helper.globalLoader.hide();
                        container.innerHTML = '';

                        if (response.data && response.data.length) {
                            response.data.forEach(c => {
                                const box = document.createElement('div');
                                box.className = 'col-md-4 col-sm-8 mb-3';
                                box.innerHTML = this.getFilterBox(type, c);

                                // Add click event to category box
                                const categoryBoxes = box.querySelectorAll('.selector');

                                categoryBoxes.forEach(el => {
                                    el.addEventListener('click', (e) => {
                                        // Determine if user clicked on Completed/Failed
                                        const statusElement = e.target.closest('.log-status');
                                        const status = statusElement ? statusElement.getAttribute('data-status') : null;

                                        // Remove selected class from all categories
                                        container.querySelectorAll('.category-box').forEach(b => {
                                            b.classList.remove('selected');
                                        });

                                        // Add selected class to clicked category
                                        el.classList.add('selected');

                                        // Update current category and load table
                                        SystemLogs.currentCategory[type] = type === 'cron' ? c.command : c.id;
                                        SystemLogs.showLogsCard(type);

                                        // Optional: store or use status as filter
                                        SystemLogs.selectedCronStatus = status || null;

                                        SystemLogs.loadTable(type);
                                    });
                                });

                                container.appendChild(box);
                            });
                        } else {
                            container.innerHTML = '<div class="col-12"><p class="text-center">{{ __("log::lang.no_categories_found") }}</p></div>';
                        }
                    },
                    error: (xhr, status, error) => {
                        container.innerHTML = '<div class="col-12"><p class="text-center text-danger">{{ __("log::lang.error_loading_categories") }}</p></div>';
                    },
                    complete: () => {
                        this.isLoadingCategories = false; // Reset loading flag
                    }
                });
            },

            destroyAllTables() {
                const types = ['mail', 'cron', 'exception'];

                types.forEach(type => {
                    const tableId = `#${type}-table`;

                    if ($.fn.DataTable.isDataTable(tableId)) {
                        $(tableId).DataTable().clear().destroy();
                    }

                    // Clear table body to remove residual data
                    $(`${tableId} tbody`).empty();

                    // Remove the DataTables wrapper if it exists
                    const wrapper = $(tableId).closest('.dataTables_wrapper');
                    if (wrapper.length) {
                        wrapper.remove();
                        // Re-add original table element to its parent
                        const cleanTable = `<table id="${type}-table" class="table table-hover w-100">
                <thead>
                    <tr>
                        <th>...</th> <!-- Fill in your headers here -->
                    </tr>
                </thead>
                <tbody></tbody>
            </table>`;
                        $(`#${type}-table-wrapper`).html(cleanTable); // Assume each table is wrapped like <div id="mail-table-wrapper"></div>
                    }

                    // Optionally reset tracking object
                    if (this.tables) {
                        this.tables[type] = null;
                    }
                });
            },

            loadTable(type) {
                this.destroyAllTables();

                const dateInput = document.getElementById(`${type}-date`);
                if (!dateInput) {
                    return;
                }

                const date = this.selectedDates[type];

                this.tables[type] = $(`#${type}-table`).DataTable({
                    serverSide: true,
                    processing: true,
                    scrollX: true,
                    responsive: false,
                    ajax: {
                        url: this.getEndpoint(type),
                        type: 'POST',
                        data: (d) => {
                            d._token = '{{ csrf_token() }}';
                            d.date = date;
                            d.category = this.currentCategory[type];
                            d.log_type = type;

                            if ((type === 'cron' || type === 'mail') && this.selectedCronStatus) {
                                d.status = this.selectedCronStatus;
                            }
                        },
                        error: function(xhr, error, code) {
                        }
                    },
                    columns: this.getColumns(type),
                    language: {
                        processing: '<div class="overlay dataTables_processing"><i class="fas fa-3x fa-sync-alt fa-spin"></i></div>',
                        emptyTable: `{{ __("log::lang.no_logs_found_for_category") }}`,
                        zeroRecords: `{{ __("log::lang.no_matching_logs_found") }}`
                    },
                    fnDrawCallback: function (oSettings) {
                        $('[data-toggle="tooltip"]').tooltip({
                            container: 'body',
                        });
                    }
                });
            },

            getEndpoint(type) {
                const endpoints = {
                    cron: '{{ url("logs/cron") }}',
                    exception: '{{ url("logs/exception") }}',
                    mail: '{{ url("logs/mail") }}'
                };
                return endpoints[type] || '';
            },

            getFilterBox(type, data){
                switch(type) {
                    case 'cron':
                        return `
      <div class="info-box bg-gradient-light h-100 category-box" data-category-id="${data.command}">
        <div class="info-box-content">
          <span class="info-box-text">${data.name}</span>
          <span class="info-box-number d-flex justify-content-between">
            <span class="text-blue me-2 log-status selector" data-status="completed">${data.completed || 0} {{ __("log::lang.completed") }}</span>
            <span class="text-red log-status selector" data-status="failed">${data.failed || 0} {{ __("log::lang.failed") }}</span>
          </span>
        </div>
      </div>
    `;
                    case 'exception':
                        return `
                         <div class="info-box bg-gradient-light h-100 category-box" data-category-id="${data.id}">
                              <div class="info-box-content">
                                   <span class="info-box-text">${data.name}</span>
                                        <span class="info-box-number">
                                             <span class="text-blue selector">${data.count} {{ __("log::lang.logs") }}</span>
                                        </span>
                                   </div>
                              </div>
                         `
                    case 'mail':
                        return `
        <div class="info-box bg-gradient-light h-100 category-box" data-category-id="${data.id}">
            <div class="info-box-content">
                <div class="d-flex justify-content-between align-items-center">
                    <span class="info-box-text">${data.name}</span>
                    <span class="text-blue log-status info-box-number selector" data-status="queued">
                        ${data.queued || 0} {{ __("log::lang.queued") }}
                    </span>
                </div>
                <div class="info-box-number d-flex justify-content-between mt-1">
                    <span class="text-blue log-status selector" data-status="sent">${data.sent || 0} {{ __("log::lang.send") }}</span>
                    <span class="text-red log-status selector" data-status="failed">${data.failed || 0} {{ __("log::lang.failed") }}</span>
                </div>
            </div>
        </div>
    `;
                }
                },

                        getColumns(type) {
                switch(type) {
                    case 'cron':
                        return [
                            { data: 'command', defaultContent: '' },
                            { data: 'description', defaultContent: '' },
                            { data: 'duration', defaultContent: '' },
                            {
                                data: 'created_at',
                                defaultContent: ''
                            },
                            { data: 'status', defaultContent: '' },
                        ];
                    case 'exception':
                        return [
                            {
                                data: 'file',
                                defaultContent: '',
                                className: 'text-start log-file',
                                width: '200px'
                            },
                            {
                                data: 'line',
                                defaultContent: '',
                                className: 'text-center log-line',
                                width: '70px'
                            },
                            {
                                data: 'message',
                                render: function(data) {
                                    if (!data) return '';
                                    if (data.length > 100) {
                                        const short = data.substr(0, 100);
                                        return `${short} <span class="read-more-message text-primary" data-full="${encodeURIComponent(data)}" style="cursor:pointer;"><u>{{ __("log::lang.read_more") }}</u></span>`;
                                    }
                                    return data;
                                },
                                defaultContent: '',
                                className: 'text-start log-message',
                                width: '300px'
                            },
                            {
                                data: 'trace',
                                render: function(data) {
                                    if (!data) return '';
                                    if (data.length > 50) {
                                        const short = data.substr(0, 50);
                                        return `${short}... <span class="read-more text-primary" data-full="${encodeURIComponent(data)}" style="cursor:pointer;"><u>{{ __("log::lang.read_more") }}</u></span>`;
                                    }
                                    return data;
                                },
                                defaultContent: '',
                                className: 'text-start log-trace',
                                width: '300px'
                            },
                            {
                                data: 'created_at',
                                defaultContent: '',
                                className: 'text-center log-created',
                                width: '180px'
                            }
                        ];
                    case 'mail':
                        return [
                            { data: 'sender_mail', defaultContent: '---' },
                            { data: 'receiver_mail', defaultContent: '---' },
                            { data: 'carbon_copy', defaultContent: '---' },
                            { data: 'blind_carbon_copy', defaultContent: '---' },
                            {
                                data: 'subject',
                                defaultContent: '',
                                render: function (data, type, row) {
                                    return `
            <a href="#" class="view-body" data-body="${encodeURIComponent(row.body)}">
                ${data}
            </a>
        `;
                                }
                            },
                            {
                                data: 'created_at',
                                render: function(data) {
                                    return data;
                                },
                                defaultContent: ''
                            },
                            {
                                data: 'updated_at',
                                render: function(data) {
                                    return data;
                                },
                                defaultContent: ''
                            },
                            {
                                data: 'status',
                                defaultContent: ''
                            },
                            {
                                data: 'is_retry',
                                orderable: false,
                                searchable: false,
                                render: function(status, type, row) {
                                    return `
            <button class="btn btn-light btn-sm retry-log-btn"
                    data-id="${row.id}"
                    ${status ? '' : 'disabled'}
                    title="{{ __("log::lang.retry_log") }}"
                    type="button">
                <i class="fas fa-redo"></i>
            </button>
        `;
                                }
                            }
                        ];
                    default:
                        return [];
                }
            },

            deleteLogs() {
                const fromDate = document.getElementById('deleteFromDate').value;
                const toDate = document.getElementById('deleteToDate').value;
                const deleteFromDateEl = $('#deleteFromDate');
                const deleteToDateEl = $('#deleteToDate');
                const logTypesErrorEl = $('#logTypesError');

                // Clear previous errors
                deleteFromDateEl.removeClass('is-invalid');
                deleteToDateEl.removeClass('is-invalid');
                logTypesErrorEl.text('');

                // Get selected log types
                const selectedTypes = [];
                if (document.getElementById('deleteMailLogs').checked) selectedTypes.push('mail');
                if (document.getElementById('deleteCronLogs').checked) selectedTypes.push('cron');
                if (document.getElementById('deleteExceptionLogs').checked) selectedTypes.push('exception');

                let hasError = false;

                // Validation
                if (!fromDate) {
                    deleteFromDateEl.addClass('is-invalid');
                    hasError = true;
                }
                if (!toDate) {
                    deleteToDateEl.addClass('is-invalid');
                    hasError = true;
                }
                if (selectedTypes.length === 0) {
                    logTypesErrorEl.text('{{ __("log::lang.please_select_at_least_one_log_type") }}');
                    hasError = true;
                }

                if (hasError) return;

                // Perform deletion via AJAX
                $.ajax({
                    url: '{{ url("logs/delete") }}',
                    method: 'delete',
                    data: {
                        _token: '{{ csrf_token() }}',
                        from_date: fromDate,
                        to_date: toDate,
                        log_types: selectedTypes
                    },
                    beforeSend: function() {
                        $('#confirmDeleteBtn').prop('disabled', true).text('{{ __("log::lang.deleting") }}');
                    },
                    success: function(response) {
                        helper.showAlert({
                            message: response.message || '{{ __("log::lang.logs_deleted_successfully") }}',
                            type: 'success',
                            autoDismiss: 5000,
                            containerSelector: '#delete-alert',
                        });

                        setTimeout(function() {
                            window.location.reload();
                        }, 5000)
                    },
                    error: function(xhr) {
                        helper.showAlert({
                            message: xhr.responseJSON?.message || '{{ __("log::lang.error_deleting_logs") }}',
                            type: 'error',
                            autoDismiss: 5000,
                            containerSelector: '#delete-alert',
                        });
                    },
                    complete: function () {
                        $('#confirmDeleteBtn').prop('disabled', false).text('{{ __("log::lang.delete_logs") }}');
                    }
                });
            }
        };

        // Ensure DOM is fully loaded before initializing
        document.addEventListener('DOMContentLoaded', function() {
            SystemLogs.init();
        });

        // Fallback for older browsers
        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', function() {
                SystemLogs.init();
            });
        } else {
            SystemLogs.init();
        }

        $(document).on('click', '.read-more, .read-more-message', function() {
            const fullText = decodeURIComponent($(this).data('full'));
            const isMessage = $(this).hasClass('read-more-message');

            const $codeContent = $('#codeContent').text(fullText);
            if (isMessage) {
                $codeContent.css({
                    'white-space': 'pre-wrap',
                    'word-wrap': 'break-word'
                });
            }else {
                $codeContent.css({
                    'white-space': '',
                    'word-wrap': ''
                });
            }
            const modal = new bootstrap.Modal(document.getElementById('codeModal'));
            modal.show();
        });

        $(document).on('click', '#copy-btn', function () {
            const code = $('#codeContent').text().trim();

            // Copy to clipboard
            navigator.clipboard.writeText(code).then(() => {
                // Change button appearance
                const $btn = $('#copy-btn');
                $btn.addClass('copied');
                $btn.find('i').removeClass('fa-copy').addClass('fa-check');
                $btn.find('.copy-text').text('{{ __("log::lang.copied") }}');

                // Revert after 1.5s
                setTimeout(() => {
                    $btn.removeClass('copied');
                    $btn.find('i').removeClass('fa-check').addClass('fa-copy');
                    $btn.find('.copy-text').text('{{ __("log::lang.copy") }}');
                }, 1500);
            }).catch((err) => {
            });
        });

        $(document).on('click', '.retry-log-btn:not([disabled])', function () {
            selectedLogId = $(this).data('id');
            $('#retryLogModal').modal('show');
        });

        let selectedLogId = null;

        $(document).on('click', '.retry-log-btn:not([disabled])', function () {
            selectedLogId = $(this).data('id');
            $('#retryLogModal').modal('show');
        });


        $('#confirmRetryBtn').on('click', function () {
            const baseRetryUrl = "{{ url('retry/mail-log') }}";
            if (!selectedLogId) return;
            const modalBody = document.getElementById('retryLogModal');

            $.ajax({
                url: `${baseRetryUrl}/${selectedLogId}`,
                method: 'GET',
                beforeSend: function () {
                    globalLoader.show(modalBody);
                },
                success: function (response) {
                    helper.showAlert({
                        id: 'my-alert',
                        message: response.message,
                        type: 'success',
                        autoDismiss: 5000,
                        containerSelector: '#log-table-alert',
                    });
                },
                error: function (xhr) {
                    helper.showAlert({
                        id: 'my-alert',
                        message: xhr.responseJSON.message,
                        type: 'error',
                        autoDismiss: 5000,
                        containerSelector: '#log-table-alert',
                    });
                },
                complete: function () {
                    globalLoader.hide(modalBody);
                    $('#retryLogModal').modal('hide');
                }
            });
        });

        function decodeHtmlEntities(html) {
            const txt = document.createElement("textarea");
            txt.innerHTML = html;
            return txt.value;
        }

        $(document).on('click', '.view-body', function (e) {
            e.preventDefault();

            const encodedBody = $(this).data('body');
            const decodedBody = decodeHtmlEntities(decodeURIComponent(encodedBody));

            $('#emailBody').html(decodedBody);
            $('#emailModal').modal('show');
        });

    </script>
@stop