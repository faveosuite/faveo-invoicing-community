@extends('log-viewer::_template.master')
@section('title')
    {{ __('message.logs_viewer') }}
@stop
@section('content-header')
    <div class="col-sm-6">
        <h1>{{ __('message.logs_viewer') }}</h1>
    </div>
    <div class="col-sm-6">
        <ol class="breadcrumb float-sm-right">
            <li class="breadcrumb-item"><a href="{{url('/')}}"><i class="fa fa-dashboard"></i> {{ __('message.home') }}</a></li>
            <li class="breadcrumb-item"><a href="{{url('settings')}}"><i class="fa fa-dashboard"></i> {{ __('message.settings') }}</a></li>
            <li class="breadcrumb-item"><a href="{{url('log-viewer')}}"><i class="fa fa-dashboard"></i> {{ __('message.Log_viewer') }}</a></li>
            <li class="breadcrumb-item active">{{ __('message.logs') }}</li>
        </ol>
    </div><!-- /.col -->
@stop

@section('content')
    <div id="success-message" class="alert alert-success" style="display: none;"></div>
    <div id="fail-message" class="alert alert-fail" style="display: none;"></div>


    <div class="card card-primary card-outline">
        <div class="card-body">
            <h4 class="card-header">{{ __('message.logs') }}</h4>

            {!! $rows->render() !!}

            <div class="table-responsive">
                <table class="table table-condensed table-hover table-stats">
                    <thead>
                    <tr>
                        @foreach($headers as $key => $header)
                            <th class="{{ $key == 'date' ? 'text-left' : 'text-center' }}">
                                @if ($key == 'date')
                                    <span class="label label-info">{{ $header }}</span>
                                @else
                                    <span class="level level-{{ $key }}">
                                {!! log_styler()->icon($key) . ' ' . $header !!}
                            </span>
                                @endif
                            </th>
                        @endforeach
                        <th class="text-right">{{ __('message.actions') }}</th>
                    </tr>
                    </thead>
                    <tbody>
                    @if ($rows->count() > 0)
                        @foreach($rows as $date => $row)
                            <tr>
                                @foreach($row as $key => $value)
                                    <td class="{{ $key == 'date' ? 'text-left' : 'text-center' }}">
                                        @if ($key == 'date')
                                            <span class="label label-primary">{{ $value }}</span>
                                        @elseif ($value == 0)
                                            <span class="level level-empty">{{ $value }}</span>
                                        @else
                                            <a href="{{ route('log-viewer::logs.filter', [$date, $key]) }}">
                                                <span class="level level-{{ $key }}">{{ $value }}</span>
                                            </a>
                                        @endif
                                    </td>
                                @endforeach
                                <td class="text-right">
                                    <a href="{{ route('log-viewer::logs.show', [$date]) }}" class="btn btn-xs btn-info"
                                       data-toggle="tooltip" title="{{ __('message.view_logs') }}">
                                        <i class="fa fa-search"></i>
                                    </a>
                                    <a href="{{ route('log-viewer::logs.download', [$date]) }}"
                                       class="btn btn-xs btn-success" data-toggle="tooltip" title="{{ __('message.download_logs') }}">
                                        <i class="fa fa-download"></i>
                                    </a>
                                    <a href="#delete-log-modal" class="btn btn-xs btn-danger delete"
                                       data-log-date="{{ $date }}" data-toggle="tooltip" title="{{ __('message.delete_logs') }}">
                                        <i class="fa fa-trash"></i>
                                    </a>
                                </td>
                            </tr>
                        @endforeach
                    @else
                        <tr>
                            <td colspan="11" class="text-center">
                                <span class="label label-default">{{ trans('log-viewer::general.empty-logs') }}</span>
                            </td>
                        </tr>
                    @endif
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    {!! $rows->render() !!}
@endsection


{{-- DELETE MODAL --}}
<div id="delete-log-modal" class="modal fade">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">{{ __('message.confirm_delete') }}</h4>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <p>{{ __('message.delete_log_file') }}</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-sm btn-default pull-left" data-dismiss="modal">{{ __('message.cancel') }}</button>
                <button id="confirmDelete" class="btn btn-sm btn-danger">{{ __('message.delete') }}</button>
            </div>
        </div>
    </div>
</div>





<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js"></script>

<script>
    $(document).ready(function() {
        $('.delete').on('click', function(e) {
            var logDate = $(this).data('log-date');
            $('#delete-log-modal').data('log-date', logDate);
            $('#delete-log-form input[name="date"]').val(logDate);
            $('#delete-log-modal').modal('show');
        });

        $('#confirmDelete').on('click', function() {
            var logDate = $('#delete-log-modal').data('log-date');
            $.ajax({
                type: 'DELETE',
                url: '{{ route('log-viewer::logs.delete') }}',
                data: {
                    date: logDate
                },
                success: function(data) {
                    $('#success-message').text(@json(__('message.log_file_deleted_successfully'))).show();
                    $('#delete-log-modal').modal('hide');
                    setTimeout(function() {
                        location.reload();
                    }, 3000);
                },
                error: function(xhr, textStatus, errorThrown) {
                    $('#fail-message').text(@json(__('message.oops'))).show();
                    $('#delete-log-modal').modal('hide');
                    setTimeout(function() {
                        location.reload();
                    }, 3000);
                }
            });
        });
    });
    $(function () {
        $('[data-toggle="tooltip"]').tooltip()
    })
</script>

