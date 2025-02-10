@extends('log-viewer::_template.master')
@section('title')
    Log-Viewer
@stop
@section('content-header')
    <h1>
        {{ __('message.logs_viewer') }}
    </h1>
    <ol class="breadcrumb">
        <li><a href="{{url('/')}}"><i class="fa fa-dashboard"></i> {{ __('message.home') }}</a></li>
        <li><a href="{{url('settings')}}">{{ __('message.settings') }}</a></li>
        <li class="active">{{ __('message.error_logs') }}</li>
    </ol>
@stop
@section('content')

    <div class="box box-secondary">
        <div class="box-body">
            <div class="row">
                <div class="col-md-3">
                    <canvas id="stats-doughnut-chart" height="300"></canvas>
                </div>
                <div class="col-md-9">
                    <section class="box-body">
                        <div class="row">
                            @foreach($percents as $level => $item)
                                <div class="col-md-4">
                                    <div class="info-box level level-{{ $level }} {{ $item['count'] === 0 ? 'level-empty' : '' }}">
                                <span class="info-box-icon">
                                    {!! log_styler()->icon($level) !!}
                                </span>

                                        <div class="info-box-content">
                                            <span class="info-box-text">{{ $item['name'] }}</span>
                                            <span class="info-box-number">
                                        {{ $item['count'] }} entries - {!! $item['percent'] !!} %
                                    </span>
                                            <div class="progress">
                                                <div class="progress-bar" style="width: {{ $item['percent'] }}%"></div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </section>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    <script>
        $(function() {
            new Chart($('canvas#stats-doughnut-chart'), {
                type: 'doughnut',
                data: {!! $chartData !!},
                options: {
                    legend: {
                        position: 'bottom'
                    }
                }
            });
        });
    </script>
@endsection