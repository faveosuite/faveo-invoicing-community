@extends('themes.default1.layouts.front.master')
@section('title')
{{ __('message.search_result') }}
@stop
@section('page-header')
    {{ __('message.search_result') }}
@stop
@section('breadcrumb')
<li><a href="{{url('home')}}">{{ __('message.home')}}</a></li>
<li class="active">{{ __('message.search_result')}}</li>
@stop
@section('main-class') 
main
@stop
@section('content')
@foreach($model as $result)
<div >
    <div>
        <a href="{{$result->url}}"><h3>{{$result->name}}</h3></a>
    </div>
    <div>
        {!! str_limit($result->content,100,'...') !!}
    </div>
</div>
@endforeach
<div class="pagination">
    <?php echo $model->render(); ?>
</div>
@stop