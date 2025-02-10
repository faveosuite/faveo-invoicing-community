@extends('themes.default1.layouts.front.master')
@section('title')
{{ucfirst($page->name)}}
@stop
@section('page-header')
{{ucfirst($page->name)}}
@stop
@section('breadcrumb')
<li><a href="{{url('home')}}">{{ __('message.home')}}</a></li>
<li class="active">{{ucfirst($page->name)}}</li>
@stop
@section('main-class') 
main
@stop
@section('content')
{!! $page->content !!}
@stop