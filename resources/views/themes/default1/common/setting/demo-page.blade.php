@extends('themes.default1.layouts.master')
@section('title')
    {{ __('message.demo_page_settings') }}
@stop
@section('content-header')
    <div class="col-sm-6">
        <h1>{{ __('message.demo_page_settings') }}</h1>
    </div>
    <div class="col-sm-6">
        <ol class="breadcrumb float-sm-right">
            <li class="breadcrumb-item"><a href="{{url('/')}}"><i class="fa fa-dashboard"></i> {{ __('message.home') }}</a></li>
            <li class="breadcrumb-item"><a href="{{url('settings')}}"><i class="fa fa-dashboard"></i> {{ __('message.settings') }}</a></li>
            <li class="breadcrumb-item active">{{ __('message.demopage_settings') }}</li>
        </ol>
    </div><!-- /.col -->
@stop
@section('content')
<div class="card card-secondary card-outline">
    <div class="card-header">
        <div id="response"></div>
        <h5>{{ __('message.configuring_demo') }}</h5>
    </div>

    <div class="card-body">
        {!! html()->form('POST', url('save/demo'))->open() !!}
        <div class="row">
            <div class="col-12 col-sm-2 mb-3">
                <div class="form-check">
                    <input class="form-check-input" type="radio" name="status" value="true"
                           @if($Demo_page->status == true) checked @endif id="enableStatus">
                    <label class="form-check-label text-wrap" for="enableStatus">
                        {{ __('message.enable') }}
                    </label>
                </div>
            </div>
            <div class="col-12 col-sm-2 mb-3">
                <div class="form-check">
                    <input class="form-check-input" type="radio" name="status" value="false"
                           @if($Demo_page->status == false) checked @endif id="disableStatus">
                    <label class="form-check-label text-wrap" for="disableStatus">
                        {{ __('message.disable') }}
                    </label>
                </div>
            </div>
        </div>

        <div class="text-center">
            <button type="submit" class="btn btn-primary float-left">{{ __('message.save') }}</button>
        </div>
        {!! html()->form()->close() !!}
    </div>
</div>


<script>
     $('ul.nav-sidebar a').filter(function() {
        return this.id == 'demo_page';
    }).addClass('active');

    // for treeview
    $('ul.nav-treeview a').filter(function() {
        return this.id == 'demo_page';
    }).parentsUntil(".nav-sidebar > .nav-treeview").addClass('menu-open').prev('a').addClass('active');
</script>

@stop


