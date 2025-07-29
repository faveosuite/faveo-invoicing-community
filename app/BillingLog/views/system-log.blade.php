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
                        <div class="settingdivblue" onclick="selectLogType(this, 'cron')">
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
                        <div class="settingdivblue" onclick="selectLogType(this, 'exception')">
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
                        <div class="settingdivblue" onclick="selectLogType(this, 'mail')">
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
                                    <input type="date" id="cron-date" name="cron_date" class="form-control" value="{{ date('Y-m-01') }}">
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
                                    <input type="date" id="exception-date" name="exception_date" class="form-control" value="{{ date('Y-m-01') }}">
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
                                    <input type="date" id="mail-date" name="mail_date" class="form-control" value="{{ date('Y-m-01') }}">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card card-secondary card-outline mt-3">
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
                                <th>Category</th>
                                <th>Command</th>
                                <th>Status</th>
                                <th>Output</th>
                                <th>Runtime</th>
                                <th>Created at</th>
                            </tr>
                            </thead>
                        </table>

                        <!-- Exception Logs Table -->
                        <table id="exception-table" class="table table-hover w-100">
                            <thead>
                            <tr>
                                <th>Category</th>
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
                                <th>Category</th>
                                <th>To</th>
                                <th>Subject</th>
                                <th>Status</th>
                                <th>Error</th>
                                <th>Created at</th>
                            </tr>
                            </thead>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <link rel="stylesheet" href="https://cdn.datatables.net/1.10.22/css/jquery.dataTables.min.css">
    <script src="https://cdn.datatables.net/1.10.22/js/jquery.dataTables.min.js"></script>
    <script>
        let currentLogType = '';
        let dataTableInstances = {};
        let selectedCategory = '';

        function getCategoryBox(category, count, logType) {
            return `
                <div class="col-md-6 col-sm-12 mb-3">
                    <div class="info-box bg-gradient-light h-100 category-box" onclick="selectCategory('${category}', '${logType}', this)">
                        <div class="info-box-content">
                            <span class="info-box-text">${category}</span>
                            <span class="info-box-number">
                                <span class="text-blue">${count} logs</span>
                            </span>
                        </div>
                    </div>
                </div>
            `;
        }

        function selectLogType(element, logType) {
            // Remove selected class from all elements
            document.querySelectorAll('.settingdivblue').forEach(div => {
                div.classList.remove('selected_type');
            });

            // Add selected class to clicked element
            element.classList.add('selected_type');

            // Set current log type
            currentLogType = logType;

            // Hide all filter boxes
            document.querySelectorAll('.filter-box').forEach(box => {
                box.classList.remove('active');
            });

            // Show selected filter box
            const selectedFilter = document.getElementById(logType + '-filter');
            if (selectedFilter) {
                selectedFilter.classList.add('active');
            }

            // Hide all tables
            document.querySelectorAll('.table-container table').forEach(table => {
                table.classList.remove('active');
            });

            // Show selected table
            const selectedTable = document.getElementById(logType + '-table');
            if (selectedTable) {
                selectedTable.classList.add('active');
            }

            // Load categories for this log type
            loadCategoriesForLogType(logType);

            // Update current selection display
            updateCurrentSelection(logType, '');
        }

        function selectCategory(category, logType, element) {
            // Remove selected class from all category boxes in current container
            const container = document.getElementById(logType + '-category-container');
            container.querySelectorAll('.category-box').forEach(box => {
                box.classList.remove('selected');
            });

            // Add selected class to clicked element
            element.classList.add('selected');

            // Set selected category
            selectedCategory = category;

            console.log(logType, category);
            // Initialize or reload DataTable for selected category
            initializeDataTable(logType, category);

            // Update current selection display
            updateCurrentSelection(logType, category);
        }

        function updateCurrentSelection(logType, category) {
            const display = document.getElementById('current-selection');
            if (category) {
                display.textContent = `Showing: ${logType.charAt(0).toUpperCase() + logType.slice(1)} Logs - ${category}`;
            } else {
                display.textContent = `Selected: ${logType.charAt(0).toUpperCase() + logType.slice(1)} Logs`;
            }
        }

        function initializeDataTable(logType, category = '') {
            const tableId = logType + '-table';

            // Destroy existing DataTable if it exists
            if (dataTableInstances[tableId]) {
                dataTableInstances[tableId].destroy();
            }

            // Get date input value for current log type
            const dateInput = document.getElementById(logType + '-date');
            const selectedDate = dateInput ? dateInput.value : '';

            // Initialize new DataTable
            dataTableInstances[tableId] = $('#' + tableId).DataTable({
                processing: true,
                serverSide: true,
                ajax: {
                    url: getLogEndpoint(logType),
                    type: 'POST',
                    data: function(d) {
                        d._token = '{{ csrf_token() }}';
                        d.date = selectedDate;
                        d.category = category;
                        d.log_type = logType;
                        return d;
                    }
                },
                columns: getColumnsForLogType(logType),
                order: [[getColumnsForLogType(logType).length - 1, 'desc']], // Order by last column (created_at) descending
                pageLength: 25,
                responsive: true,
                language: {
                    processing: "Loading " + logType + " logs...",
                    emptyTable: "No " + logType + " logs found for " + category,
                    zeroRecords: "No matching " + logType + " logs found"
                }
            });
        }

        function getColumnsForLogType(logType) {
            switch(logType) {
                case 'cron':
                    return [
                        { data: 'category', name: 'category' },
                        { data: 'command', name: 'command' },
                        { data: 'status', name: 'status' },
                        { data: 'output', name: 'output', render: function(data, type, row) {
                                if (type === 'display' && data && data.length > 100) {
                                    return data.substr(0, 100) + '...';
                                }
                                return data || '';
                            }},
                        { data: 'runtime', name: 'runtime' },
                        { data: 'created_at', name: 'created_at', render: function(data, type, row) {
                                if (type === 'display') {
                                    return new Date(data).toLocaleString();
                                }
                                return data;
                            }}
                    ];
                case 'exception':
                    return [
                        { data: 'category', name: 'category.name' },
                        { data: 'file', name: 'file' },
                        { data: 'line', name: 'line' },
                        { data: 'message', name: 'message', render: function(data, type, row) {
                                if (type === 'display' && data.length > 100) {
                                    return data.substr(0, 100) + '...';
                                }
                                return data;
                            }},
                        { data: 'trace', name: 'trace', render: function(data, type, row) {
                                if (type === 'display' && data && data.length > 50) {
                                    return data.substr(0, 50) + '...';
                                }
                                return data || '';
                            }},
                        { data: 'created_at', name: 'created_at', render: function(data, type, row) {
                                if (type === 'display') {
                                    return new Date(data).toLocaleString();
                                }
                                return data;
                            }}
                    ];
                case 'mail':
                    return [
                        { data: 'category', name: 'category' },
                        { data: 'to', name: 'to' },
                        { data: 'subject', name: 'subject' },
                        { data: 'status', name: 'status' },
                        { data: 'error', name: 'error', render: function(data, type, row) {
                                if (type === 'display' && data && data.length > 100) {
                                    return data.substr(0, 100) + '...';
                                }
                                return data || '';
                            }},
                        { data: 'created_at', name: 'created_at', render: function(data, type, row) {
                                if (type === 'display') {
                                    return new Date(data).toLocaleString();
                                }
                                return data;
                            }}
                    ];
                default:
                    return [];
            }
        }

        function getLogEndpoint(logType) {
            switch(logType) {
                case 'cron':
                    return '{{ url("logs/cron") }}';
                case 'exception':
                    return '{{ url("logs/exception") }}';
                case 'mail':
                    return '{{ url("logs/mail") }}';
                default:
                    return '{{ url("logs/exception") }}';
            }
        }

        function loadCategoriesForLogType(logType) {
            const container = document.getElementById(logType + '-category-container');
            const dateInput = document.getElementById(logType + '-date');
            const selectedDate = dateInput ? dateInput.value : '';

            $.ajax({
                url: '{{ url("log-category-list") }}',
                method: 'GET',
                data: {
                    date: selectedDate
                },
                success: function(response) {
                    container.innerHTML = '';

                    if (response.data && response.data.length > 0) {
                        response.data.forEach(category => {
                            const boxHtml = getCategoryBox(category.name, category.exceptionCount, logType);
                            const tempDiv = document.createElement('div');
                            tempDiv.innerHTML = boxHtml.trim();
                            const boxElement = tempDiv.firstChild;
                            container.appendChild(boxElement);
                        });
                    } else {
                        container.innerHTML = '<div class="col-12"><p class="text-center">No categories found for this date.</p></div>';
                    }
                },
                error: function(xhr, status, error) {
                    console.error("Error loading categories:", error);
                    container.innerHTML = '<div class="col-12"><p class="text-center text-danger">Error loading categories.</p></div>';
                }
            });
        }

        document.addEventListener("DOMContentLoaded", function () {
            // Add event listeners for date changes for each log type
            ['cron', 'exception', 'mail'].forEach(logType => {
                const dateInput = document.getElementById(logType + '-date');
                if (dateInput) {
                    dateInput.addEventListener('change', function () {
                        // Reload categories when date changes
                        if (currentLogType === logType) {
                            loadCategoriesForLogType(logType);

                            // Clear table if category was selected
                            if (selectedCategory) {
                                selectedCategory = '';
                                updateCurrentSelection(logType, '');

                                // Hide table
                                const table = document.getElementById(logType + '-table');
                                if (table) {
                                    table.classList.remove('active');
                                }
                            }
                        }
                    });
                }
            });
        });
    </script>
@stop