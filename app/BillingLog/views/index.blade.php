@extends('themes.default1.layouts.master')
@section('title')
    System Logs
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
            cursor: pointer;
            transition: all 0.3s ease;
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

    </style>

    <div class="card card-secondary card-outline">
        <div class="card-header">
            <h3 class="card-title">System Logs</h3>
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
                        <div class="text-center text-sm fw_400">Cron Logs</div>
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
                        <div class="text-center text-sm fw_400">Exception Logs</div>
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
                        <div class="text-center text-sm fw_400">Mail Logs</div>
                    </div>
                </div>
            </div>

            <!-- Filter Section for each log type -->
            <div class="card card-secondary card-outline mt-3">
                <div class="card-header">
                    <h3 class="card-title">Filter Logs</h3>
                </div>
                <div class="card-body">
                    <!-- Cron Logs Filter -->
                    <div id="cron-filter" class="filter-box">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="row" id="cron-category-container">
                                    <!-- Cron categories will be loaded here -->
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="cron-date">Date</label>
                                    <input type="date" id="cron-date" name="cron_date" class="form-control" value="{{ date('Y-m-d') }}">
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Exception Logs Filter -->
                    <div id="exception-filter" class="filter-box">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="row" id="exception-category-container">
                                    <!-- Exception categories will be loaded here -->
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="exception-date">Date</label>
                                    <input type="date" id="exception-date" name="exception_date" class="form-control" value="{{ date('Y-m-d') }}">
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Mail Logs Filter -->
                    <div id="mail-filter" class="filter-box">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="row" id="mail-category-container">
                                    <!-- Mail categories will be loaded here -->
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="mail-date">Date</label>
                                    <input type="date" id="mail-date" name="mail_date" class="form-control" value="{{ date('Y-m-d') }}">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div id="log-table-alert"></div>

            <div id="logs-card" class="card card-secondary card-outline mt-3" style="display: none;">
                <div class="card-header">
                    <h3 class="card-title">Logs</h3>
                    <div id="current-selection" class="float-right text-muted"></div>
                </div>
                <div class="card-body">
                    <div class="table-container">
                        <!-- Cron Logs Table -->
                        <table id="cron-table" class="table table-hover w-100">
                            <thead>
                            <tr>
                                <th>Command</th>
                                <th>Description</th>
                                <th>Duration (in seconds)</th>
                                <th>Created at</th>
                                <th>Status</th>
                            </tr>
                            </thead>
                        </table>

                        <!-- Exception Logs Table -->
                        <table id="exception-table" class="table table-hover w-100">
                            <thead>
                            <tr>
                                <th>File</th>
                                <th>Line</th>
                                <th>Message</th>
                                <th>Trace</th>
                                <th>Created at</th>
                            </tr>
                            </thead>
                        </table>

                        <!-- Mail Logs Table -->
                        <table id="mail-table" class="table table-hover w-100">
                            <thead>
                            <tr>
                                <th>Sender Mail</th>
                                <th>Receiver Mail</th>
                                <th>CC</th>
                                <th>BCC</th>
                                <th>Subject</th>
                                <th>Created at</th>
                                <th>Updated at</th>
                                <th>Status</th>
                                <th>Action</th>
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
                        Log Details
                    </h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="code-container">
                        <button id="copy-btn" class="copy-btn btn btn-sm btn-outline-secondary mb-2">
                            <i class="fas fa-copy"></i> <span class="copy-text">Copy</span>
                        </button>
                        <pre class="code-block" id="codeContent"></pre>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                    <div class="ml-auto"></div>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="retryLogModal" tabindex="-1" role="dialog" aria-labelledby="retryLogModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document"> <!-- Centering class added -->
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="retryLogModalLabel">Retry Mail</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    Are you sure you want to retry this mail?
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-primary" id="confirmRetryBtn">Retry</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Email Body Modal -->
    <div class="modal fade" id="emailModal" tabindex="-1" role="dialog" aria-labelledby="emailModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="emailModalLabel">Email Subject</h5>
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
                        {{ __('message.cancel') }}
                    </button>
                </div>
            </div>
        </div>
    </div>

    @include('log::loader')


    <link rel="stylesheet" href="https://cdn.datatables.net/1.10.22/css/jquery.dataTables.min.css">
    <script src="https://cdn.datatables.net/1.10.22/js/jquery.dataTables.min.js"></script>
    <script>
        const SystemLogs = {
            currentCategory: { mail: '', cron: '', exception: '' },
            currentType: '',
            tables: { mail: null, cron: null, exception: null },
            isLoadingCategories: false, // Prevent double loading

            init() {
                this.bindEvents();
                this.showLogType('mail');
            },

            bindEvents() {
                // Fixed: Use proper event binding
                document.querySelectorAll('.settingdivblue').forEach(el => {
                    el.addEventListener('click', (e) => {
                        e.preventDefault();
                        const type = el.getAttribute('data-type');
                        console.log('Clicked on:', type); // Debug log
                        this.showLogType(type);
                    });
                });

                ['mail', 'cron', 'exception'].forEach(type => {
                    const input = document.getElementById(`${type}-date`);
                    if (input) {
                        input.addEventListener('change', () => {
                            console.log('Date changed for:', type); // Debug log
                            // Only reload categories if this type is currently active
                            if (this.currentType === type && !this.isLoadingCategories) {
                                this.currentCategory[type] = '';
                                this.loadCategories(type);
                                this.destroyTable(type);
                                this.hideLogsCard();
                            }
                        });
                    }
                });
            },

            showLogType(type) {
                console.log('Showing log type:', type); // Debug log
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
                    console.log('Already loading categories, skipping...'); // Debug log
                    return;
                }

                const dateInput = document.getElementById(`${type}-date`);
                const container = document.getElementById(`${type}-category-container`);

                if (!container || !dateInput) {
                    console.error('Missing elements:', { container: !!container, dateInput: !!dateInput });
                    return;
                }

                const date = dateInput.value;
                console.log('Loading categories for:', type, 'date:', date); // Debug log

                this.isLoadingCategories = true; // Set loading flag

                // Show loading state
                container.innerHTML = '<div class="col-12"><p class="text-center">Loading categories...</p></div>';

                $.ajax({
                    url: '{{ url("log-category-list") }}',
                    method: 'GET',
                    data: { date, log_type: type },
                    success: (response) => {
                        console.log('Categories response:', response); // Debug log
                        container.innerHTML = '';

                        if (response.data && response.data.length) {
                            response.data.forEach(c => {
                                const box = document.createElement('div');
                                box.className = 'col-md-6 col-sm-12 mb-3';
                                box.innerHTML = this.getFilterBox(type, c);

                                // Add click event to category box
                                const categoryBox = box.querySelector('.category-box');
                                categoryBox.addEventListener('click', (e) => {
                                    // Determine if user clicked on Completed/Failed
                                    const statusElement = e.target.closest('.log-status');
                                    const status = statusElement ? statusElement.getAttribute('data-status') : null;

                                    // Remove selected class from all categories
                                    container.querySelectorAll('.category-box').forEach(b => {
                                        b.classList.remove('selected');
                                    });

                                    // Add selected class to clicked category
                                    categoryBox.classList.add('selected');

                                    // Update current category and load table
                                    SystemLogs.currentCategory[type] = c.id;
                                    SystemLogs.showLogsCard(type);

                                    // Optional: store or use status as filter
                                    SystemLogs.selectedCronStatus = status || null;

                                    SystemLogs.loadTable(type);
                                });

                                container.appendChild(box);
                            });
                        } else {
                            container.innerHTML = '<div class="col-12"><p class="text-center">No categories found for this date.</p></div>';
                        }
                    },
                    error: (xhr, status, error) => {
                        console.error('AJAX Error:', { xhr, status, error });
                        container.innerHTML = '<div class="col-12"><p class="text-center text-danger">Error loading categories. Please try again.</p></div>';
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
                        console.log(`Destroying DataTable for type: ${type}`);
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
                console.log('Loading table for:', type); // Debug log
                this.destroyAllTables();

                const dateInput = document.getElementById(`${type}-date`);
                if (!dateInput) {
                    console.error('Date input not found for type:', type);
                    return;
                }

                const date = dateInput.value;

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
                            console.error('DataTable AJAX Error:', { xhr, error, code });
                        }
                    },
                    columns: this.getColumns(type),
                    language: {
                        processing: `Loading ${type} logs...`,
                        emptyTable: `No ${type} logs found for this category.`,
                        zeroRecords: `No matching ${type} logs found`
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
            <span class="text-blue me-2 log-status" data-status="completed">${data.completed || 0} Completed</span>
            <span class="text-red log-status" data-status="failed">${data.failed || 0} Failed</span>
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
                                             <span class="text-blue">${data.count} logs</span>
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
                    <span class="text-blue log-status info-box-number" data-status="queued">
                        ${data.queued || 0} Queued
                    </span>
                </div>
                <div class="info-box-number d-flex justify-content-between mt-1">
                    <span class="text-blue log-status" data-status="sent">${data.sent || 0} Send</span>
                    <span class="text-red log-status" data-status="failed">${data.failed || 0} Failed</span>
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
                                        return `${short} <span class="read-more-message text-primary" data-full="${encodeURIComponent(data)}" style="cursor:pointer;"><u>Read more</u></span>`;
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
                                        return `${short}... <span class="read-more text-primary" data-full="${encodeURIComponent(data)}" style="cursor:pointer;"><u>Read more</u></span>`;
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
                    title="RETRY LOG"
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
                $btn.find('.copy-text').text('Copied!');

                // Revert after 1.5s
                setTimeout(() => {
                    $btn.removeClass('copied');
                    $btn.find('i').removeClass('fa-check').addClass('fa-copy');
                    $btn.find('.copy-text').text('Copy');
                }, 1500);
            }).catch((err) => {
                console.error('Copy failed:', err);
                alert("Failed to copy code.");
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