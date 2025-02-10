@extends('themes.default1.layouts.master')
@section('title')
Localized License
@stop
@section('content-header')
<div class="col-sm-6">
    <h1>{{ __('message.localized_lic') }}</h1>
</div>
<div class="col-sm-6">
    <ol class="breadcrumb float-sm-right">
        <li class="breadcrumb-item"><a href="{{url('/')}}"><i class="fa fa-dashboard"></i> {{ __('message.home') }}</a></li>
        <li class="breadcrumb-item"><a href="{{url('settings')}}">{{ __('message.settings') }}</a></li>
        <li class="breadcrumb-item active">{{ __('message.localized_lic') }}</li>
    </ol>
</div><!-- /.col -->
@stop

@section('content')
<div id="response"></div>
 <div class="card card-secondary card-outline">
     <div class="card-body table-responsive">
            <div class="row">
              <div class="col-md-12 col-lg-12">
                 <div id="Localized-license-table_wrapper" class="dataTables_wrapper no-footer">
                 	<div id="Localized-license-table_processing" class="dataTables_processing" style="display: none;"> 
                 		<div class="overlay">
                 			<i class="fas fa-3x fa-sync-alt fa-spin"></i>
                 			<div class="text-bold pt-2">{{ __('message.loading') }}</div>
                 		</div>
                 	</div>
                 	<table id="Localized-license-table" class="table display dataTable no-footer" cellspacing="0" width="100%" styleclass="borderless" role="grid" aria-describedby="third-party-app-table_info" style="width: 100%;"> 
                    <thead>
                    	<tr role="row">
                                <th class="sorting" tabindex="0" aria-controls="Localized-license-table" rowspan="1" colspan="1" aria-label="App name: activate to sort column ascending" style="width: 98px;">{{ __('message.license_file_name') }}</th>
                    			<th class="sorting" tabindex="0" aria-controls="Localized-license-table" rowspan="1" colspan="1" aria-label="Action: activate to sort column ascending" style="width: 46px;">{{ __('message.action') }}</th></tr>
                    </thead>
                   <tbody>

                   @foreach($files_array = Storage::disk('public')->files() as $files)
                   @if(\Illuminate\Support\Str::startsWith($files, 'faveo-license'))
                    <tr role="row" class="odd">
                   		<td>{{$files}}</td>
                   		<td>
                            <p>
                            <div class="row">
                            <div class="col-md-1">
                            <form action="{{url('LocalizedLicense/delete/'.$files)}}" method="GET" onsubmit="return ConfirmDelete()">
                             <button  id="tooltipex" data-toggle="tooltip" data-id="2" data-appname="faveo_app_key" data-appkey="sgBX3dmgjsiRPCsy4qtQcMy1F2r8xqfl" data-secret="FAVEOSECRET" class="btn btn-sm btn-secondary btn-xs editThirdPartyApp" label="" style="font-weight:500;" title="{{ __('message.delete_license_file') }}">
                            <i class="fa fa-trash" style="color:white;"></i></button>&nbsp;
                            </form>
                        </div>
                        <div class="col-md-11 container2">
                            <a href="{{url('LocalizedLicense/downloadLicense/'.$files)}}"><button class="btn btn-secondary btn-sm ml-3" data-toggle="tooltip" title="Download this license file." data-placement="top">{{ __('message.download_license_file') }}</button></a>
                            <a href="{{url('LocalizedLicense/downloadPrivateKey/'.$files)}}"><button class="btn btn-secondary btn-sm ml-3" data-placement="top"data-toggle="tooltip" title="{{ __('message.download_this_license_key') }}">{{ __('message.download_license_key') }} </button></a>
                       </div>
                       </div>
                          </p>  
                       </td>     
                    </tr>
                 @endif
                 @endforeach             
     </tbody>
 </table>
               </div>
           </div>
        </div>

    </div>
</div>


    <link rel="stylesheet" type="text/css" href="//cdn.datatables.net/1.10.21/css/jquery.dataTables.min.css" />
    <script src="//cdn.datatables.net/1.10.21/js/jquery.dataTables.min.js"></script>
    
    <script type="text/javascript">
    function ConfirmDelete() {
        return confirm("{{ __('message.sure_delete_license_file') }}");
     }
     
     </script>
<script>
     $('ul.nav-sidebar a').filter(function() {
        return this.id == 'setting';
    }).addClass('active');
    // for treeview
    $('ul.nav-treeview a').filter(function() {
        return this.id == 'setting';
    }).parentsUntil(".nav-sidebar > .nav-treeview").addClass('menu-open').prev('a').addClass('active');
</script>
<script>
   $(document).ready(function() {
    $('#Localized-license-table').DataTable( {
        "lengthMenu": [[10, 25, 50, -1], [10, 25, 50, "All"]]
    } );
} );
</script>
<script type="text/javascript">
"fnDrawCallback": function( oSettings ) {
                $(function () {
                    $('[data-toggle="tooltip"]').tooltip({
                        container : 'body'
                    });
                });
                $('.loader').css('display', 'none');
            }
            "fnPreDrawCallback": function(oSettings, json) {
                $('.loader').css('display', 'block');
            }
</script>
<script type="text/javascript">
    $(document).ready(function () {
  $('[data-toggle="tooltip"]').tooltip()
});
</script>
@stop




  
