@extends('themes.default1.layouts.front.master')
@section('title')
Search Result
@stop
@section('page-header')
Search Result
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