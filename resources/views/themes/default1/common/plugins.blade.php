@extends('themes.default1.layouts.master')
@section('title')
Plugins
@stop
@section('content-header')
    <div class="col-sm-6">
        <h1>{{ __('message.payment_gateway') }}</h1>
    </div>
    <div class="col-sm-6">
        <ol class="breadcrumb float-sm-right">
            <li class="breadcrumb-item"><a href="{{url('/')}}"><i class="fa fa-dashboard"></i> {{ __('message.home') }}</a></li>
            <li class="breadcrumb-item"><a href="{{url('settings')}}"><i class="fa fa-dashboard"></i> {{ __('message.settings') }}</a></li>
            <li class="breadcrumb-item active">{{ __('message.plugins') }}</li>
        </ol>
    </div><!-- /.col -->
@stop
@section('content')

<div class="row">

    <div class="col-md-12">
        <div class="card card-secondary card-outline">



    <div class="card-body table-responsive">

        


        <div class="row">
            <div class="col-md-12">
                 <table id="plugin" class="table display" cellspacing="0" width="100%" styleClass="borderless">

                    <thead><tr>
                         <th>{{ __('message.category_name') }}</th>
                          <th>{{ __('message.description') }}</th>
                          <th>{{ __('message.author') }}</th>
                          <th>{{ __('message.website') }}</th>
                          <th>{{ __('message.version') }}</th>
                          <th>{{ __('message.action') }}</th>
                        </tr></thead>
                       
                        @foreach($pay as $key => $item)
                       
                         
                       
                        <tbody>
                            <tr>
                                <td>{{$item['name']}}</td>
                                <td>{{$item['description']}}</td>
                                 <td>{{$item['author']}}</td>
                                 
                                 <td><a href={{$item['website']}}>{{$item['website']}}</a></td>
                                  <td>{{$item['version']}}</td>
                             
                            
                        
                              @foreach($status as $s)
                              @if($item['name'] == $s->name && !$s->status)
                               <td><form method="post" action="{{url('plugin/status/'.$item['name'])}}"><input type="hidden" name="_token" value='.\Session::token().'>
                                   @csrf
                                    <button type="submit" class="btn btn-secondary btn-sm btn-xs" title = "{{ __('message.activate') }}"><i class="fa fa-tasks" style="color:white;"></i></button></form></td>
                                    @elseif($item['name'] == $s->name && $s->status)
                                     <td>
                                       <a href= "{{url($item['settings'])}}" class="btn btn-secondary btn-sm btn-xs"><i class="nav-icon fa fa-fw fa-cogs" style="color:white;"></i></a> 
                                     
                                       <form method="post" action="{{ url('plugin/status/'.$item['name']) }}"><input type="hidden" name="_token" value="\Session::token()
                                       ">
                                       @csrf
                                    
                                       <button type="submit" class="btn btn-secondary btn-sm btn-xs" title="{{ __('message.deactivate') }}"><i class="fa fa-tasks" style="color:white;"></i></button></form>
                                    </td>
                                    
                              @endif
                              
                              @endforeach
                                 
                                     
                                

                                    
                           
                           
                              
                            </tr>
                        </tbody>
                        
                        @endforeach
                        
                      
                     </table>
              
            </div>
        </div>
    </div>
</div>

      
        <!-- /.box -->

    </div>


</div>
<link rel="stylesheet" href="https://cdn.datatables.net/1.10.21/css/jquery.dataTables.min.css">
<script src="https://cdn.datatables.net/1.10.21/js/jquery.dataTables.min.js"></script>



@stop