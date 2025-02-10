@extends('themes.default1.layouts.master')
@section('title')
Report settings
@stop
@section('content-header')
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" rel="stylesheet">

    <div class="col-sm-6">
        <h1>{{ __('message.report_settings') }}</h1>
    </div>
    <div class="col-sm-6">
        <ol class="breadcrumb float-sm-right">
            <li class="breadcrumb-item"><a href="{{url('/')}}"><i class="fa fa-dashboard"></i> {{ __('message.home') }}</a></li>
            <li class="breadcrumb-item active">{{ __('message.report_settings') }}</li>
        </ol>
    </div><!-- /.col -->
@stop
@section('content')
    <div class="card card-secondary card-outline">
        <div class="card-header">
            <h3 class="card-title">{{ __('message.settings') }}</h3>
        </div>
          {!! Form::open(['url' => 'add_records', 'method' => 'post']) !!}
        <div class="card-body table-responsive">

        <div class="row">
            <div class="col-md-6">
                <div class="form-group">
                    {!! Form::label('records', Lang::get('Records per export'), ['class' => 'required']) !!}
                    <i class="fas fa-question-circle" data-toggle="tooltip" title="{{ __('message.report_limit') }}"></i>
                    
                    {!! Form::select('records', [
                        200 => '200',
                        400 => '400',
                        600 => '600',
                        800 => '800',
                        1000 => '1000',
                        1200 => '1200',
                        1400 => '1400',
                        1600 => '1600',
                        1800 => '1800',
                        2000 => '2000',
                        2200 => '2200',
                        2400 => '2400',
                        2600 => '2600',
                        2800 => '2800',
                        3000 => '3000'
                    ], $settings->records ?? null, ['class' => 'form-control']) !!}
                </div>
            </div>
        </div>

            <div class="row">
                <div class="col-md-12">
                    <button type="submit" class="btn btn-primary">
                        <i class="fa fa-save"></i> {!! Lang::get('message.save') !!}
                    </button>
                </div>
            </div>
        </div>
          {!! Form::close() !!}
    </div>




<script>
      $(document).ready(function(){
    $('[data-toggle="tooltip"]').tooltip();
});


     $('ul.nav-sidebar a').filter(function() {
        return this.id == 'add_col';
    }).addClass('active');

    // for treeview
    $('ul.nav-treeview a').filter(function() {
        return this.id == 'add_col';
    }).parentsUntil(".nav-sidebar > .nav-treeview").addClass('menu-open').prev('a').addClass('active');
</script>
@stop


