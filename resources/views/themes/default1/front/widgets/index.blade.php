@extends('themes.default1.layouts.master')
@section('title')
Widgets
@stop
@section('content-header')
    <div class="col-sm-6">
        <h1>All Widgets</h1>
    </div>
    <div class="col-sm-6">
        <ol class="breadcrumb float-sm-right">
            <li class="breadcrumb-item"><a href="{{url('/')}}"><i class="fa fa-dashboard"></i> Home</a></li>
             <li class="breadcrumb-item"><a href="{{url('settings')}}"><i class="fa fa-dashboard"></i> Settings</a></li>
            <li class="breadcrumb-item active">Widgets</li>
        </ol>
    </div><!-- /.col -->
@stop
@section('content')
<div class="card card-secondary card-outline">
    <div class="card-header">
        <h3 class="card-title">{{Lang::get('message.widgets')}}</h3>

        <div class="card-tools">
            <a href="{{url('widgets/create')}}" class="btn btn-default btn-sm pull-right"><span class="fa fa-plus"></span>&nbsp;&nbsp;{{Lang::get('message.create')}}</a>

        </div>
    </div>



    <div class="card-body table-responsive">
        <div class="row">

            <div class="col-md-12">
       
                   <table id="widgets-table" class="table display" cellspacing="0" width="100%" styleClass="borderless">
                    <button  value="" class="btn btn-secondary btn-sm btn-alldell" id="bulk_delete"><i class= "fa fa-trash"></i>&nbsp;&nbsp;Delete Selected</button><br /><br />
                    <thead><tr>
                        <th class="no-sort"><input type="checkbox" name="select_all" onchange="checking(this)"></th>
                         <th>Name</th>
                          <th>Type</th>
                          <th>Created At</th>
                           <th>Action</th>
                        </tr></thead>
                     </table>
                

            </div>
        </div>

    </div>

</div>

<link rel="stylesheet" type="text/css" href="//cdn.datatables.net/1.10.22/css/jquery.dataTables.min.css" />
<script src="//cdn.datatables.net/1.10.22/js/jquery.dataTables.min.js"></script>
<script type="text/javascript">
        $('#widgets-table').DataTable({
                serverSide: true,
                stateSave: false,
                ordering: true,
                searching:true,
                select: true,
                order: [[ 1, "desc" ]],
             ajax: {
            "url":  '{!! route('get-widgets') !!}',
               error: function(xhr) {
               if(xhr.status == 401) {
                alert('Your session has expired. Please login again to continue.')
                window.location.href = '/login';
               }
            }

            },
            "oLanguage": {
                "sLengthMenu": "_MENU_ Records per page",
                "sSearch"    : "Search: ",
                "sProcessing": ' <div class="overlay"><i class="fas fa-3x fa-sync-alt fa-spin"></i><div class="text-bold pt-2">Loading...</div></div>'
            },
                columnDefs: [
                { 
                    targets: 'no-sort', 
                    orderable: false,
                    order: []
                }
            ],
            columns: [
                {data: 'checkbox', name: 'checkbox'},
                {data: 'name', name: 'name'},
                {data: 'type', name: 'type'},
                {data: 'created_at', name: 'created_at'},
                {data: 'action', name: 'action'}
            ],
            "fnDrawCallback": function( oSettings ) {
                $(function () {
                    $('[data-toggle="tooltip"]').tooltip({
                        container : 'body'
                    });
                });
                $('.loader').css('display', 'none');
            },
            "fnPreDrawCallback": function(oSettings, json) {
                $('.loader').css('display', 'block');
            },
        });
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

@stop

@section('icheck')
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <script>
   function checking(e){
          
          $('#widgets-table').find("td input[type='checkbox']").prop('checked', $(e).prop('checked'));
     }
     

     $(document).on('click','#bulk_delete',function(){
      var id=[];
         var swl = swal.fire({
             title: "<h2 style='text-align: left; padding-left: 17px !important; margin-bottom:10px !important;'>{{Lang::get('message.Delete')}}</h2>",
             html: "<div  style='display: flex; flex-direction: column; align-items:stretch; width:100%; margin:0px !important'>" +
                 "<div style='border-top: 1px solid #ccc; border-bottom: 1px solid #ccc;padding-top: 13px;'>" +
                 "<p style='text-align: left; margin-left:17px'>{{Lang::get('message.widget_delete')}}</p>" + "</div>" +
                 "</div>",
             showCancelButton: true,
             showCloseButton: true,
             position: "top",
             width: "600px",

             confirmButtonText: @json(trans('message.Delete')),
             confirmButtonColor: "#007bff",

         }).then((result) => {
             if (result.isConfirmed) {
                 $('.widget_checkbox:checked').each(function(){
                     id.push($(this).val())
                 });
                 if(id.length >0)
                 {
                     $.ajax({
                         url:"{!! Url('widgets-delete') !!}",
                         method:"delete",
                         data: $('#check:checked').serialize(),
                         beforeSend: function () {
                             $('#gif').show();
                         },
                         success: function (data) {
                             $('#gif').hide();
                             $('#response').html(data);
                             location.reload();
                         }
                     })
                 }
                 else {
                     swal.fire({
                         title: "<h2 style='text-align: left; padding-left: 17px !important; margin-bottom:10px !important;'>{{Lang::get('message.Select')}}</h2>",
                         html: "<div  style='display: flex; flex-direction: column; align-items:stretch; width:100%; margin:0px !important'>" +
                             "<div style='border-top: 1px solid #ccc; border-bottom: 1px solid #ccc;padding-top: 13px;'>" +
                             "<p style='text-align: left; margin-left:17px'>{{Lang::get('message.sweet_widget')}}</p>" + "</div>" +
                             "</div>",
                         position: 'top',
                         confirmButtonText: "OK",
                         showCloseButton: true,
                         confirmButtonColor: "#007bff",
                         width: "600px",
                     })
                 }
             } else if (result.dismiss === Swal.DismissReason.cancel) {
                 window.close();
             }
             return false;
         });
     });
</script>
@stop