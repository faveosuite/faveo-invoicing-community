@extends('themes.default1.layouts.master')
@section('title')
Demo Page Settings
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
        {!! Form::open(['url' => 'save/demo', 'method' => 'POST']) !!}
        <div class="row">
                       <div class="col-md-4">
                <div class="form-group">
                    {!! Form::label('Demopage',Lang::get('Enable/Disable')) . ' <span class="required"></span>' !!}
                    <div class="row">
                        <div class="col-sm-3">
                            <input type="radio" name="status" value="true"  @if($Demo_page->status == true) checked="true" @endif > {{Lang::get('Enable')}}
                        </div>
                        <div class="col-sm-3">
                            <input type="radio" name="status" value="false" @if($Demo_page->status == false) checked="true" @endif> {{Lang::get('Disable')}}
                        </div>
                    </div>
                </div> 
            </div>  
        </div>

        <div class="text-center">
            <button type="submit" class="btn btn-primary float-left">{{ __('message.save') }}</button>
        </div>
        {!! Form::close() !!}
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


