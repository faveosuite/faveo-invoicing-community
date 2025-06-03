@extends('themes.default1.layouts.master')
@section('title')
    {{ __('message.plans') }}
@stop
@section('content-header')
    <div class="col-sm-6">
        <h1>{{ __('message.all_plans') }}</h1>
    </div>
    <div class="col-sm-6">
        <ol class="breadcrumb float-sm-right">
            <li class="breadcrumb-item"><a href="{{url('/')}}"><i class="fa fa-dashboard"></i> {{ __('message.home') }}</a></li>
            <li class="breadcrumb-item active">{{ __('message.all_plans') }}</li>
        </ol>
    </div><!-- /.col -->
@stop
@section('content')



<div class="card card-secondary card-outline">
    <div class="card-header">
        @include('themes.default1.product.plan.create')
        <h3 class="card-title">{{ __('message.plans') }}</h3>
        <div class="card-tools">
            <a href="#create-plan-option" data-toggle="modal" data-target="#create-plan-option" class="btn btn-default btn-sm pull-right"><span class="fa fa-plus"></span>&nbsp;{{Lang::get('message.create')}}</a></h4>


        </div>


    </div>


    <div id="response"></div>

    <div class="card-body table-responsive">
         @include('themes.default1.product.plan.popup.create-period') 
        <div class="row">
            <div class="col-md-12">
              
               </div><br><br>
            <div class="col-md-12">

                    <table id="plan-table" class="table display" cellspacing="0" width="100%" styleClass="borderless">
                    <button  value="" class="btn btn-secondary btn-sm btn-alldell" id="bulk_delete"><i class="fa fa-trash"></i>&nbsp;&nbsp;{{ __('message.delmultiple') }}</button><br /><br />
                    <thead><tr>
                             <th class="no-sort"><input type="checkbox" name="select_all" onchange="checking(this)"></th>
                            <th>{{ __('message.name_page') }}</th>
                            <th>{{ __('message.months') }}</th>
                            <th>{{ __('message.products') }}</th>
                            <th>{{ __('message.price') }}</th>
                            <th>{{ __('message.currency') }}</th>
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


        $('#plan-table').DataTable({
            processing: true,
            serverSide: true,
            order: [[ 1, "desc" ]],

             ajax: {
            "url":  '{!! route('get-plans') !!}',
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
                zeroRecords:    "{{ __('message.no_matching_records_found') }} ",
                search:         "{{ __('message.datatable_search') }} ",
                infoEmpty:      "{{ __('message.info_empty') }}",
                infoFiltered:   "{{ __('message.info_filtered') }}",
                lengthMenu:     "{{ __('message.sLengthMenu') }}",
                loadingRecords: "{{ __('message.loading_records') }}",
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
                {data: 'days', name: 'days'},
                {data: 'product', name: 'name'},
                {data: 'price', name: 'price'},
                {data: 'currency', name: 'currency'},
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
        return this.id == 'plan';
    }).addClass('active');

    // for treeview
    $('ul.nav-treeview a').filter(function() {
        return this.id == 'plan';
    }).parentsUntil(".nav-sidebar > .nav-treeview").addClass('menu-open').prev('a').addClass('active');
</script>

@stop

@section('icheck')
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <script>
     function checking(e){
          
          $('#plan-table').find("td input[type='checkbox']").prop('checked', $(e).prop('checked'));
     }
     

     $(document).on('click','#bulk_delete',function(){
      var id=[];
         $('.plan_checkbox:checked').each(function(){
             id.push($(this).val())
         });
         if(id.length<=0){
             swal.fire({
                 title: "<h2 class='swal2-title custom-title'>{{Lang::get('message.Select')}}</h2>",
                 html: "<div class='swal2-html-container custom-content'>" +
                     "<div class='section-sa'>" +
                     "<p>{{Lang::get('message.sweet_plan')}}</p>" + "</div>" +
                     "</div>",
                 position: 'top',
                 confirmButtonText: "{{ __('message.ok') }}",
                 showCloseButton: true,
                 confirmButtonColor: "#007bff",
                 width: "600px",
             })
         }else {
             var swl = swal.fire({
                 title: "<h2 class='swal2-title custom-title'>{{Lang::get('message.Delete')}}</h2>",
                 html: "<div class='swal2-html-container custom-content'>" +
                     "<div class='section-sa'>" +
                     "<p>{{Lang::get('message.plan_delete')}}</p>" + "</div>" +
                     "</div>",
                 showCancelButton: true,
                 showCloseButton: true,
                 position: "top",
                 width: "600px",
                 confirmButtonText: @json(trans('message.Delete')),
                 cancelButtonText: "{{ __('message.cancel') }}",
                 confirmButtonColor: "#007bff",
             }).then((result) => {
                 if (result.isConfirmed) {
                     $('.plan_checkbox:checked').each(function () {
                         id.push($(this).val())
                     });
                     if (id.length > 0) {
                         $.ajax({
                             url: "{!! route('plans-delete') !!}",
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
                             html: "<div class='swal2-html-container custom-content'>" +
                                 "<div class='section-sa'>" +
                                 "<p>{{Lang::get('message.sweet_plan')}}</p>" + "</div>" +
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
             })
         }
     });
   
</script>
@if (count($errors) > 0)
    <script type="text/javascript">
        $( document ).ready(function() {
             $('#create-plan-option').modal('show');
        });
    </script>
  @endif
@stop