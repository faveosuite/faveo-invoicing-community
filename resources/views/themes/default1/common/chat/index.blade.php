@extends('themes.default1.layouts.master')
@section('title')
    {{ __('message.create_script') }}
@stop
@section('content-header')
    <div class="col-sm-6">
        <h1>{{ __('message.google_analytics_javascript')}}</h1>
    </div>
    <div class="col-sm-6">
        <ol class="breadcrumb float-sm-right">
            <li class="breadcrumb-item"><a href="{{url('/')}}"><i class="fa fa-dashboard"></i> {{ __('message.home')}}</a></li>
            <li class="breadcrumb-item"><a href="{{url('settings')}}"><i class="fa fa-dashboard"></i> {{ __('message.settings')}}</a></li>
            <li class="breadcrumb-item active">{{ __('message.script')}}</li>
        </ol>
    </div><!-- /.col -->
@stop

@section('content')



<div class="card card-secondary card-outline">
    <div class="card-header">
        <h3 class="card-title">{{ __('message.chat')}}</h3>

        <div class="card-tools">
            <a href="{{url('chat/create')}}" class="btn btn-default btn-sm pull-right"><span class="fa fa-plus"></span>&nbsp;&nbsp;{{Lang::get('message.create')}}</a></h4>

        </div>
    </div>



    <div id="response"></div>
    <div class="card-body table-responsive">
        <div class="row">

            <div class="col-md-12">
                <table id="script-table" class="table display" cellspacing="0" width="100%" styleClass="borderless">
                 <button  value="" class="btn btn-secondary btn-sm btn-alldell" id="bulk_delete"><i class="fa fa-trash"></i>&nbsp;&nbsp;{{ __('message.delmultiple')}}</button><br /><br />
                    <thead><tr>
                        <th class="no-sort"><input type="checkbox" name="select_all" onchange="checking(this)"></th>
                         <th>{{ __('message.category_name')}}</th>
                          <th>{{ __('message.action')}}</th>
                        </tr></thead>
                     </table>
            
               
            </div>
        </div>

    </div>

</div>

<link rel="stylesheet" type="text/css" href="//cdn.datatables.net/1.10.21/css/jquery.dataTables.min.css" />
<script src="//cdn.datatables.net/1.10.21/js/jquery.dataTables.min.js"></script>
<script type="text/javascript">
        $('#script-table').DataTable({
                serverSide: true,
                stateSave: false,
                ordering: true,
                searching:true,
                select: true,
                order: [[ 1, "desc" ]],
             ajax: {
            "url":  '{!! route('get-script') !!}',
               error: function(xhr) {
               if(xhr.status == 401) {
                alert('{{ __('message.session_expired')}}')
                window.location.href = '/login';
               }
            }

            },
            "oLanguage": {
                "sLengthMenu": "_MENU_ Records per page",
                "sSearch"    : "Search: ",
                "sProcessing": ' <div class="overlay"><i class="fas fa-3x fa-sync-alt fa-spin"></i><div class="text-bold pt-2">{{ __('message.loading') }}</div></div>'
            },
            language: {
                paginate: {
                    first:      "{{ __('message.paginate_first') }}",
                    last:       "{{ __('message.paginate_last') }}",
                    next:       "{{ __('message.paginate_next') }}",
                    previous:   "{{ __('message.paginate_previous') }}"
                },
                emptyTable:     "{{ __('message.empty_table') }}",
                info:           "{{ __('message.datatable_info') }}",
                zeroRecords:    "{{ __('message.no_matching_records_found') }} ",
                infoEmpty:      "{{ __('message.info_empty') }}",
                infoFiltered:   "{{ __('message.info_filtered') }}",
                lengthMenu:     "{{ __('message.length_menu') }}",
                loadingRecords: "{{ __('message.loading_records') }}",
                search:         "{{ __('message.table_search') }}",

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
          
          $('#script-table').find("td input[type='checkbox']").prop('checked', $(e).prop('checked'));
     }
     

     $(document).on('click','#bulk_delete',function(){
      var id=[];
         $('.chat_checkbox:checked').each(function(){
             id.push($(this).val())
         });
         if(id.length <=0){
             swal.fire({
                 title: "<h2 class='swal2-title custom-title'>{{Lang::get('message.Select')}}</h2>",
                 html: "<div class='swal2-html-container custom-content'>" +
                     "<div class='section-sa'>" +
                     "<p>{{Lang::get('message.sweet_script')}}</p>" + "</div>" +
                     "</div>",
                 position: 'top',
                 confirmButtonText: "{{ __('message.ok') }}",
                 showCloseButton: true,
                 confirmButtonColor: "#007bff",
                 width: "600px",
             })
         }
         else {
             var swl = swal.fire({
                 title: "<h2 class='swal2-title custom-title'>{{Lang::get('message.Delete')}}</h2>",
                 html: "<div  class='swal2-html-container custom-content'>" +
                     "<div class='section-sa'>" +
                     "<p>{{Lang::get('message.script_delete')}}</p>" + "</div>" +
                     "</div>",
                 showCancelButton: true,
                 showCloseButton: true,
                 position: "top",
                 width: "600px",

                 cancelButtonText: "{{ __('message.cancel') }}",
                 confirmButtonText: @json(trans('message.Delete')),
                 confirmButtonColor: "#007bff",
             }).then((result) => {
                 if (result.isConfirmed) {
                     $('.chat_checkbox:checked').each(function () {
                         id.push($(this).val())
                     });
                     if (id.length > 0) {
                         $.ajax({
                             url: "{!! route('script-delete') !!}",
                             method: "delete",
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
                     } else {
                         swal.fire({
                             title: "<h2 class='swal2-title custom-title'>{{Lang::get('message.Select')}}</h2>",
                             html: "<div  class='swal2-html-container custom-content'>" +
                                 "<div class='section-sa'>" +
                                 "<p>{{Lang::get('message.sweet_script')}}</p>" + "</div>" +
                                 "</div>",
                             position: 'top',
                             confirmButtonText: "{{ __('message.ok') }}",
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
         }
     });
</script>
@stop