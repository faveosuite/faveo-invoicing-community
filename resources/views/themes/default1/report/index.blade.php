@extends('themes.default1.layouts.master')
@section('title')
    {{ __('message.reports') }}
@stop
@section('content-header')
    <div class="col-sm-6">
        <h1>{{ __('message.all_reports') }}</h1>
    </div>
    <div class="col-sm-6">
        <ol class="breadcrumb float-sm-right">
            <li class="breadcrumb-item"><a href="{{url('/')}}"><i class="fa fa-dashboard"></i> {{ __('message.home') }}</a></li>
            <li class="breadcrumb-item active">{{ __('message.all_reports') }}</li>
        </ol>
    </div><!-- /.col -->
@stop
@section('content')

    <div class="card card-secondary card-outline">
 <div class="card-header">
            <h3 class="card-title">{{ __('message.reports') }}</h3>
        </div>
 
        <div id="response"></div>
       <div class="card-body table-responsive">
             
             <div class="row">
            
            <div class="col-md-12">
               
                 <table id="reports-table" class="table display" cellspacing="0" width="100%" styleClass="borderless">
                     <button  value="" class="btn btn-secondary btn-sm btn-alldell" id="bulk_delete"><i class="fa fa-trash"></i>&nbsp;&nbsp;{{Lang::get('message.delmultiple')}}</button><br /><br />
                    <thead><tr>
                        <th class="no-sort" style="width:20px"><input type="checkbox" name="select_all" onchange="checking(this)"></th>
                            <th>{{ __('message.file_name') }}</th>
                            <th>{{ __('message.format') }}</th>
                            <th>{{ __('message.type') }}</th>
                            <th>{{ __('message.contact') }}</th>
                            <th>{{ __('message.created_at') }}</th>
                            <th>{{ __('message.action') }}</th>
                        </tr></thead>

                   </table>
            </div>
        </div>

    </div>

</div>

    <link rel="stylesheet" href="https://cdn.datatables.net/1.10.21/css/jquery.dataTables.min.css">

    <script src="https://cdn.datatables.net/1.10.21/js/jquery.dataTables.min.js"></script>
<script type="text/javascript">
        $('#reports-table').DataTable({
            processing: true,
            serverSide: true,
            stateSave: false,
            ordering: true,
            searching:true,
            select: true,
            order: [[ 5, "asc" ]],
               ajax: {
            "url":  '{!! url('get-reports') !!}',
               error: function(xhr) {
               if(xhr.status == 401) {
                alert('{{ __('message.session_expired') }}')
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
                search:         "{{ __('message.datatable_search') }} ",
                zeroRecords:    "{{ __('message.no_matching_records_found') }} ",
                infoEmpty:      "{{ __('message.info_empty') }}",
                infoFiltered:   "{{ __('message.info_filtered') }}",
                lengthMenu:     "{{ __('message.sLengthMenu') }}",
                loadingRecords: "{{ __('message.loading_records') }}",
            },

            columnDefs: [
             { targets: 'no-sort', 
                    orderable: false,
                    order: []
                }
          ],
            columns: [
                {data: 'checkbox', name: 'checkbox'},
                {data: 'file_name', name: 'file_name'},
                {data: 'format', name: 'format'},
                {data: 'type', name: 'type'},
                {data: 'contact', name: 'contact'},
                {data: 'created_at', name: 'created_at'},
                {data: 'action', name: 'action'}
            ],
            "fnDrawCallback": function( oSettings ) {
                // bindEditButton();
                $('.loader').css('display', 'none');
            },
            "fnPreDrawCallback": function(oSettings, json) {
                $('.loader').css('display', 'block');
            },
        });
    </script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <script>
    function checking(e){
      $('#reports-table').find("td input[type='checkbox']").prop('checked',$(e).prop('checked'));
    }


      $(document).on('click','#bulk_delete',function(){

      var id=[];
          $('.type_checkbox:checked').each(function(){
              id.push($(this).val())
          });
          if(id.length<=0){
              swal.fire({
                  title: "<h2 style='text-align: left; padding-left: 17px !important; margin-bottom:10px !important;'>{{Lang::get('message.Select')}}</h2>",
                  html: "<div  style='display: flex; flex-direction: column; align-items:stretch; width:100%; margin:0px !important'>" +
                      "<div style='border-top: 1px solid #ccc; border-bottom: 1px solid #ccc;padding-top: 13px;'>" +
                      "<p style='text-align: left; margin-left:17px'>{{Lang::get('message.sweet_report')}}</p>" + "</div>" +
                      "</div>",
                  position: 'top',
                  confirmButtonText: "OK",
                  showCloseButton: true,
                  confirmButtonColor: "#007bff",
                  width: "600px",
                  buttonsStyling: false,
                  customClass: {
                      confirmButton: 'btn btn-primary btn-sm custom-confirm',
                  }
              })
          }else {
              var swl = swal.fire({
                  title: "<h2 style='text-align: left; padding-left: 17px !important; margin-bottom:10px !important;'>{{Lang::get('message.Delete')}}</h2>",
                  html: "<div  style='display: flex; flex-direction: column; align-items:stretch; width:100%; margin:0px !important'>" +
                      "<div style='border-top: 1px solid #ccc; border-bottom: 1px solid #ccc;padding-top: 13px;'>" +
                      "<p style='text-align: left; margin-left:17px'>{{Lang::get('message.report_delete')}}</p>" + "</div>" +
                      "</div>",
                  showCancelButton: true,
                  showCloseButton: true,
                  position: "top",
                  width: "600px",

                  confirmButtonText: @json(trans('message.Delete')),
                  confirmButtonColor: "#007bff",
                  buttonsStyling: false,
                  reverseButtons: true,
                  customClass: {
                      actions: 'swal2-actions-custom-fix',
                      confirmButton: 'btn btn-primary btn-sm custom-confirm',
                      cancelButton: 'btn btn-secondary btn-sm custom-cancel'
                  }
              }).then((result) => {
                  if (result.isConfirmed) {
                      $('.type_checkbox:checked').each(function () {
                          id.push($(this).val())
                      });
                      if (id.length > 0) {
                          $.ajax({
                              url: "{!! url('report-delete') !!}",
                              method: "delete",
                              data: $('#check:checked').serialize(),
                              beforeSend: function () {
                                  $('#gif').show();
                              },
                              success: function (data) {
                                  $('#gif').hide();
                                  $('#response').html(data);
                                  setTimeout(function () {
                                      location.reload();
                                  }, 5000);
                              }
                          })
                      } else {
                          swal.fire({
                              title: "<h2 style='text-align: left; padding-left: 17px !important; margin-bottom:10px !important;'>{{Lang::get('message.Select')}}</h2>",
                              html: "<div  style='display: flex; flex-direction: column; align-items:stretch; width:100%; margin:0px !important'>" +
                                  "<div style='border-top: 1px solid #ccc; border-bottom: 1px solid #ccc;padding-top: 13px;'>" +
                                  "<p style='text-align: left; margin-left:17px'>{{Lang::get('message.sweet_report')}}</p>" + "</div>" +
                                  "</div>",
                              position: 'top',
                              confirmButtonText: "OK",
                              showCloseButton: true,
                              confirmButtonColor: "#007bff",
                              width: "600px",
                          })
                      }
                  } else if (result.dismiss === Swal.DismissReason.cancel) {
                      // Action if "No" is clicked
                      window.close();
                  }
              })
              return false;
          }
     });
 </script>
<script>
     $('ul.nav-sidebar a').filter(function() {
        return this.id == 'all_reports';
    }).addClass('active');

    // for treeview
    $('ul.nav-treeview a').filter(function() {
        return this.id == 'all_reports';
    }).parentsUntil(".nav-sidebar > .nav-treeview").addClass('menu-open').prev('a').addClass('active');

         $(document).ready(function(){
    $('[data-toggle="tooltip"]').tooltip();
});

</script>
@stop


