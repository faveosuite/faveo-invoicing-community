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
            <li class="breadcrumb-item"><a href="{{ url('/') }}"><i class="fa fa-dashboard"></i> Home</a></li>
            <li class="breadcrumb-item"><a href="{{ url('settings') }}">Settings</a></li>
            <li class="breadcrumb-item active">MSG91 Reports</li>
        </ol>
    </div>
@stop

@section('content')
    <div class="row">
        <div class="col-12">
            <!-- Search -->
            <div class="card card-secondary card-outline {{ request()->all() ? '' : 'collapsed-card' }}">
                <div class="card-header">
                    <h3 class="card-title">Advance Search</h3>
                    <div class="card-tools">
                        <button type="button" class="btn btn-tool" id="tip-search" title="Expand">
                            <i id="search-icon" class="fas {{ request()->all() ? 'fa-minus' : 'fa-plus' }}"></i>
                        </button>
                    </div>
                </div>
                <div class="card-body" id="advance-search" style="{{ request()->all() ? '' : 'display: none;' }}">
                    <form method="POST" action="{{ url()->current() }}">
                        @csrf
                        <div class="row">
                            <div class="col-md-3 form-group">
                                <label for="request_id">Request ID</label>
                                <input type="text" name="request_id" class="form-control" value="{{ old('request_id', request('request_id')) }}">
                            </div>
                            <div class="col-md-3 form-group">
                                <label for="full_name">Full Name</label>
                                <input type="text" name="full_name" class="form-control" value="{{ old('full_name', request('full_name')) }}">
                            </div>
                            <div class="col-md-3 form-group">
                                <label for="email">Email</label>
                                <input type="email" name="email" class="form-control" value="{{ old('email', request('email')) }}">
                            </div>
                            <div class="col-md-3 form-group">
                                <label for="mobile_number">Mobile Number</label>
                                <input type="text" name="mobile_number" class="form-control" value="{{ old('mobile_number', request('mobile_number')) }}">
                            </div>
                            <div class="col-md-3 form-group">
                                <label for="sender_id">Sender ID</label>
                                <input type="text" name="sender_id" class="form-control" value="{{ old('sender_id', request('sender_id')) }}">
                            </div>
                            <div class="col-md-3 form-group">
                                <label for="status">Status</label>
                                <select name="status" class="form-control">
                                    <option value="">Select Status</option>
                                    <option value="0" {{ request('status') === '0' ? 'selected' : '' }}>Pending</option>
                                    <option value="1" {{ request('status') === '1' ? 'selected' : '' }}>Delivered</option>
                                    <option value="2" {{ request('status') === '2' ? 'selected' : '' }}>Failed</option>
                                    <option value="9" {{ request('status') === '9' ? 'selected' : '' }}>NDNC</option>
                                    <option value="rejected" {{ request('status') === 'rejected' ? 'selected' : '' }}>Rejected</option>
                                    <option value="17" {{ request('status') === '17' ? 'selected' : '' }}>Blocked number</option>
                                </select>
                            </div>
                            <div class="col-md-3 form-group">
                                <label for="country">Country</label>
                                <select name="country" class="form-control">
                                    <option value="">Select Country</option>
                                    @foreach(\App\Model\Common\Country::all() as $country)
                                        <option value="{{ $country->country_code_char2 }}" {{ request('country') === $country->country_code_char2 ? 'selected' : '' }}>
                                            {{ $country->nicename }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-3 form-group">
                                <label for="failure_reason">Failure Reason</label>
                                <input type="text" name="failure_reason" class="form-control" value="{{ old('failure_reason', request('failure_reason')) }}">
                            </div>
                            <div class="col-md-3 form-group">
                                <label for="date_from">Date From</label>
                                <div class="input-group date" id="date_from_picker" data-target-input="nearest">
                                    <input type="text" name="date_from" class="form-control datetimepicker-input" data-target="#date_from_picker" autocomplete="off" value="{{ old('date_from', request('date_from')) }}" />
                                    <div class="input-group-append" data-target="#date_from_picker" data-toggle="datetimepicker">
                                        <div class="input-group-text"><i class="fa fa-calendar"></i></div>
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-3 form-group">
                                <label for="date_to">Date To</label>
                                <div class="input-group date" id="date_to_picker" data-target-input="nearest">
                                    <input type="text" name="date_to" class="form-control datetimepicker-input" data-target="#date_to_picker" autocomplete="off" value="{{ old('date_to', request('date_to')) }}" />
                                    <div class="input-group-append" data-target="#date_to_picker" data-toggle="datetimepicker">
                                        <div class="input-group-text"><i class="fa fa-calendar"></i></div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <button type="submit" class="btn btn-secondary"><i class="fa fa-search"></i> Search</button>
                        <a href="{{ url()->current() }}" class="btn btn-secondary"><i class="fas fa-sync-alt"></i> Reset</a>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Table -->
    <div class="card card-secondary card-outline">
        <div class="card-body table-responsive">
            <table id="reports-table" class="table display" cellspacing="0" width="100%">
                <thead>
                <tr>
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

    <!-- Assets -->
    <link rel="stylesheet" href="https://cdn.datatables.net/1.10.22/css/jquery.dataTables.min.css">
    <script src="https://cdn.datatables.net/1.10.22/js/jquery.dataTables.min.js"></script>
    <script>
        $(document).ready(function() {
            const table = $('#reports-table').DataTable({
                processing: true,
                serverSide: true,
                ajax: {
                    url: '{!! url('getMsgReports') !!}',
                    type: 'GET',
                    data: function(d) {
                        d.request_id = $('input[name="request_id"]').val();
                        d.full_name = $('input[name="full_name"]').val();
                        d.email = $('input[name="email"]').val();
                        d.mobile_number = $('input[name="mobile_number"]').val();
                        d.sender_id = $('input[name="sender_id"]').val();
                        d.status = $('select[name="status"]').val();
                        d.country = $('select[name="country"]').val();
                        d.failure_reason = $('input[name="failure_reason"]').val();
                        d.date_from = $('input[name="date_from"]').val();
                        d.date_to = $('input[name="date_to"]').val();
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
                        render: function(data, type, row) {
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
                        render: function(data) {
                            return data ?? '---';
                        }
                    },
                    {
                        data: 'mobile_number',
                        name: 'mobile_number',
                        render: function (data, type, row) {
                            const countryIso = row.countries?.nicename || '';
                            if (data) {
                                return `<span data-toggle="tooltip" title="${countryIso}">${data}</span>`;
                            }
                            return '---';
                        }
                    },
                    {data: 'formatted_sender_id', name: 'sender_id'},
                    {
                        data: 'readable_status',
                        name: 'status',
                        render: function(data, type, row) {
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
                        render: function(data) {
                            return data ? `<span>${data}</span>` : '---';
                        }
                    },
                    {data: 'date', name: 'date'}
                ],
                drawCallback: function(settings) {
                    $('[data-toggle="tooltip"]').tooltip({
                        container: 'body'
                    });
                    $('.loader').hide();
                },
                preDrawCallback: function(settings) {
                    const urlParams = new URLSearchParams(window.location.search);
                    const hasSearchParams = urlParams.has('request_id') ||
                        urlParams.has('mobile_number') ||
                        urlParams.has('sender_id') ||
                        urlParams.has('status') ||
                        urlParams.has('date') ||
                        urlParams.has('failure_reason');

                    if (hasSearchParams) {
                        $('#advance-search').show();
                        $('#tip-search').attr('title', 'Collapse');
                        $('#search-icon').removeClass('fa-plus').addClass('fa-minus');
                    } else {
                        $('#advance-search').collapse('hide');
                    }

                    $('.loader').show();
                }
            });

            // Submit filter form
            $('form').on('submit', function(e) {
                e.preventDefault();
                table.ajax.reload();
            });

            // Reset filters
            $('#reset-filters').on('click', function () {
                $('#filter-form')[0].reset();
                table.ajax.reload();
            });
        });
    </script>

@stop

@section('datepicker')
    <script type="text/javascript">
        $('#date_from_picker').datetimepicker({
            format: 'L'

        });
        $('#date_to_picker').datetimepicker({
            format: 'L'

        });
    </script>
@stop