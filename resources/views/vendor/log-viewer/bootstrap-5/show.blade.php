@extends('log-viewer::_template.master')
@section('title')
    Log-Viewer
@stop
@section('content-header')
    <div class="col-sm-6">
        <h1 class="m-0 text-dark">Log [{{ $log->date }}]</h1>
    </div><!-- /.col -->
    <div class="col-sm-6">
        <ol class="breadcrumb float-sm-right">
            <li class="breadcrumb-item"><a href="{{url('/')}}"><i class="fa fa-dashboard"></i> Home</a></li>
            <li class="breadcrumb-item"><a href="{{url('settings')}}">Settings</a></li>
            <li class="breadcrumb-item"><a href="{{url('log-viewer')}}">Log Viewer</a></li>
            <li class="breadcrumb-item active">View Logs</li>
        </ol>
    </div><!-- /.col -->



@stop
@section('content')
    <div id="success-message" class="alert alert-success" style="display: none;"></div>
    <div id="fail-message" class="alert alert-fail" style="display: none;"></div>
    <div class="card card-primary card-outline">
        <div class="card-body table-responsive">

            <div class="row">
                <div class="col-md-2">
                    @include('log-viewer::_partials.menu')
                </div>
                <div class="col-md-10">
                    {{-- Log Details --}}
                    <div class="panel panel-default">
                        <div class="panel-heading">
                            Log info :

                            <div class="group-btns pull-right">
                                <a href="{{ route('log-viewer::logs.download', [$log->date]) }}" class="btn btn-xs btn-success">
                                    <i class="fa fa-download"></i> DOWNLOAD
                                </a>
                                <a id="delete" class="btn btn-xs btn-danger"  style="color: white;" data-toggle="modal" data-log-date="{{ $log->date }}">
                                    <i class="fa fa-trash-o"></i> DELETE
                                </a>
                            </div>
                        </div>
                        <div class="card-body table-responsive">
                            <table class="table table-condensed">
                                <thead>
                                <tr>
                                    <td>File path :</td>

                                    <td colspan="5">{{ $log->getPath() }}</td>
                                </tr>
                                </thead>
                                <tbody>
                                <tr>
                                    <td>Log entries : </td>
                                    <td>
                                        <span class="label label-primary">{{ $entries->total() }}</span>
                                    </td>
                                    <td>Size :</td>
                                    <td>
                                        <span class="label label-primary">{{ $log->size() }}</span>
                                    </td>
                                    <td>Created at :</td>
                                    <td>
                                        <span class="label label-primary">{{ $log->createdAt() }}</span>
                                    </td>
                                    <td>Updated at :</td>
                                    <td>
                                        <span class="label label-primary">{{ $log->updatedAt() }}</span>
                                    </td>
                                </tr>
                                </tbody>
                            </table>
                        </div>

                        <div class="panel-footer">
                            {{-- Search --}}
                            <form action="{{ route('log-viewer::logs.search', [$log->date, $level]) }}" method="GET">
                                <div class=form-group">
                                    <div class="input-group">
                                        <input id="query" name="query" class="form-control"  value="{!! request('query') !!}" placeholder="typing something to search">
                                        &nbsp;&nbsp;
                                        <span class="input-group-btn">
                                    @if (request()->has('query'))
                                                <a href="{{ route('log-viewer::logs.show', [$log->date]) }}" class="btn btn-default"><i class="fas fa-sync-alt"></i></a>
                                            @endif
                                    <button id="search-btn" class="btn btn-primary"><i class="fa fa-search"></i></button>
                                </span>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>

                    {{-- Log Entries --}}
                    <div class="panel panel-default">
                        @if ($entries->hasPages())
                            <div class="panel-heading">
                                {!! $entries->appends(compact('query'))->render() !!}

                                <span class="label label-info pull-right">
                            Page {!! $entries->currentPage() !!} of {!! $entries->lastPage() !!}
                        </span>
                            </div>
                        @endif

                        <div class="table-responsive">
                            <table id="entries" class="table table-condensed">
                                <thead>
                                <tr>
                                    <th>ENV</th>
                                    <th style="width: 120px;">Level</th>
                                    <th style="width: 65px;">Time</th>
                                    <th>Header</th>
                                    <th class="text-right">Actions</th>
                                </tr>
                                </thead>
                                <tbody>

                                @forelse($entries as $key => $entry)
                                    <tr>
                                        <td>
                                            <span class="label label-env">{{ $entry->env }}</span>
                                        </td>
                                        <td>
                                        <span class="level level-{{ $entry->level }}">
                                            {!! $entry->level() !!}
                                        </span>
                                        </td>
                                        <td>
                                       
                                        <span class="label label-default">
                                            {{ $entry->datetime->format('H:i:s') }}
                                        </span>
                                        </td>

                                        <td>
                                            <p>{{ $entry->header }}</p>
                                        </td>
                                        <td class="text-right">
                                            @if ($entry->hasStack())
                                                <a class="btn btn-xs btn-default" role="button" data-toggle="collapse" href="#log-stack-{{ $key }}" aria-expanded="false" aria-controls="log-stack-{{ $key }}">
                                                    <i class="fa fa-toggle-on"></i> Stack
                                                </a>
                                            @endif
                                        </td>
                                    </tr>

                                    @if ($entry->hasStack())
                                        <tr>
                                            <td colspan="5" class="stack">
                                                <div class="stack-content collapse" id="log-stack-{{ $key }}">
                                                    {!! $entry->stack() !!}
                                                </div>
                                            </td>
                                        </tr>
                                    @endif
                                @empty
                                    <tr>
                                        <td colspan="5" class="text-center">
                                            <span class="label label-default">{{ trans('log-viewer::general.empty-logs') }}</span>
                                        </td>
                                    </tr>
                                @endforelse
                                </tbody>
                            </table>
                        </div>

                        @if ($entries->hasPages())
                            <div class="panel-footer">
                                {!! $entries->appends(compact('query'))->render() !!}

                                <span class="label label-info pull-right">
                            Page {!! $entries->currentPage() !!} of {!! $entries->lastPage() !!}
                        </span>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>

@endsection


{{-- DELETE MODAL --}}

<div id="delete-log-modal" class="modal fade">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">Confirm Delete</h4>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <p>Are you sure you want to <span class="label label-danger">DELETE</span> this log file <span class="label label-primary">{{ $log->date }}</span> ?</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-sm btn-default pull-left" data-dismiss="modal">Cancel</button>
                <button id="confirmDelete" class="btn btn-sm btn-danger">Delete</button>
            </div>
        </div>
    </div>
</div>



<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js"></script>

<script>
    $(document).ready(function() {
        $('#delete').on('click', function(e) {
            var logDate = $(this).data('log-date');
            $('#delete-log-form input[name="date"]').val(logDate);
            $('#delete-log-modal').modal('show');
        });
    });
</script>
<script>
    $(document).ready(function() {
        $('#delete-log-modal').on('show.bs.modal', function(event) {
            var logDate = $('#delete').data('log-date');
            var modal = $(this);

            modal.find('#delete-log-form input[name="date"]').val(logDate);

            $('#confirmDelete').on('click', function() {
                $.ajax({
                    type: 'DELETE', // Use DELETE method
                    url: '{{ route('log-viewer::logs.delete') }}',
                    data: {
                        date: logDate // Pass the log date as data
                    },
                    success: function(data) {

                        $('#success-message').text('Log file deleted successfully.').show();
                        $('#delete-log-modal').modal('hide');
                        setTimeout(function() {
                            window.location.href = '{{ route('log-viewer::logs.list') }}';
                        }, 2000);

                    },
                    error: function(xhr, textStatus, errorThrown) {
                        $('#fail-message').text('Something went wrong.').show();
                        $('#delete-log-modal').modal('hide');
                        setTimeout(function() {
                            window.location.href = '{{ route('log-viewer::logs.list') }}';
                        }, 2000);
                    }
                });
            });
        });
    });


</script>