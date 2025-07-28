@extends('themes.default1.layouts.front.master')
@section('title')
500
@stop
@section('page-header')
500
@stop
@section('breadcrumb')
<li><a href="{{url('home')}}">{{ __('message.home')}}</a></li>
<li class="active">500</li>
@stop
@section('main-class') "main shop" @stop
@section('content')
<section class="page-not-found">
    <div class="row">
        <div style="text-align:center;">
            <div class="page-not-found-main">
                <h3>{{ __('message.sorry_something_wrong')}} <i class="fa fa-bug"></i></h3>
                <p>{{ __('message.error_fixed')}}</p>
                <p><a href="{{url('home')}}">{{ __('message.go_back')}}</a></p>
            </div>
        </div>

    </div>
</section>
@stop