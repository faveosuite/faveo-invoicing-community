@extends('themes.default1.layouts.master')
@section('title')
Plugins
@stop
@section('content-header')
    <div class="col-sm-6">
        <h1>Payment Gateway</h1>
    </div>
    <div class="col-sm-6">
        <ol class="breadcrumb float-sm-right">
            <li class="breadcrumb-item"><a href="{{url('/')}}"><i class="fa fa-dashboard"></i> Home</a></li>
            <li class="breadcrumb-item"><a href="{{url('settings')}}"><i class="fa fa-dashboard"></i> Settings</a></li>
            <li class="breadcrumb-item active">Plugins</li>
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
                         <th>Name</th>
                          <th>Description</th>
                          <th>Author</th>
                          <th>Website</th>
                          <th>Version</th>
                          <th>Action</th>
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
                                        <td>
                                            <form method="post" action="{{ url('plugin/status/'.$item['name']) }}">
                                                <input type="hidden" name="_token" value="{{ \Session::token() }}">
                                                @csrf
                                                <button type="submit" class="btn btn-secondary btn-sm btn-xs" data-toggle="tooltip" title="Activate">
                                                    <i class="fa fa-tasks" style="color:white;"></i>
                                                </button>
                                            </form>
                                        </td>
                                    @elseif($item['name'] == $s->name && $s->status)
                                        <td>
                                            <a href="{{ url($item['settings']) }}" class="btn btn-secondary btn-sm btn-xs" data-toggle="tooltip" title="Settings">
                                                <i class="nav-icon fa fa-fw fa-cogs" style="color:white;"></i>
                                            </a>
                                            <form method="post" action="{{ url('plugin/status/'.$item['name']) }}">
                                                <input type="hidden" name="_token" value="{{ \Session::token() }}">
                                                @csrf
                                                <button type="submit" class="btn btn-secondary btn-sm btn-xs" data-toggle="tooltip" title="Deactivate">
                                                    <i class="fa fa-tasks" style="color:white;"></i>
                                                </button>
                                            </form>
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

<script>
    $(document).ready(function(){
        $('[data-toggle="tooltip"]').tooltip();
    });
</script>

@stop