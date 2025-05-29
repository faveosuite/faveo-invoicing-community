@extends('themes.default1.layouts.master')
@section('title')
 {{ __('message.payment_logs') }}
@stop
@section('content-header')
<style>
    .modal-dialog-scrollable {
        max-height: calc(100vh - 200px);
        margin-top: 100px;
    }

    .modal-body {
        overflow-y: auto;
    }

    @media (max-width: 767.98px) {
        .modal-dialog-scrollable {
            max-height: calc(100vh - 120px);
            margin-top: 60px;
        }
    }

       .modal-lg .modal-content {
        background-color: black;
        border: none; 
    }

    .modal-lg .modal-content,
    .modal-lg .modal-header,
    .modal-lg .modal-footer {
        color: white;
    }

    .modal-lg .modal-header {
        border-bottom-color: transparent; 
    }

    .modal-lg .modal-header .close {
        color: white; 
    }

    .modal-lg .modal-body {
        max-height: 400px; 
        overflow-y: auto;
    }
</style>
    <div class="col-sm-6">
        <h1>{{ __('message.payment_log') }}</h1>
    </div>
    <div class="col-sm-6">
        <ol class="breadcrumb float-sm-right">
            <li class="breadcrumb-item"><a href="{{url('/')}}"><i class="fa fa-dashboard"></i> {{ __('message.home') }}</a></li>
            <li class="breadcrumb-item"><a href="{{url('settings')}}"><i class="fa fa-dashboard"></i> {{ __('message.settings') }}</a></li>
            <li class="breadcrumb-item active">{{ __('message.payment_log') }}</li>
        </ol>
    </div><!-- /.col -->
@stop
@section('content')


<div class="card card-secondary card-outline">

    <div class="card-header">

        <div id="response"></div>
        <h5>{{ __('message.search_here') }}
          </h5>
    </div>


<div class="modal fade" id="exception-modal" tabindex="-1" role="dialog" aria-labelledby="exception-modal-label" aria-hidden="true" style="margin-top: 10%;">
    <div class="modal-dialog modal-dialog-scrollable modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title" id="exception-modal-label">{{ __('message.exception_message') }}</h4>
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
            </div>
            <div class="modal-body">
                <p class="exception-message"></p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">{{ __('message.close') }}</button>
            </div>
        </div>
    </div>
</div>

 
        <div class="card-body">


            {!! html()->form('GET')->open() !!}

            <div class="row">
                         <div class="col-md-3 form-group">
                            <!-- first name -->
                             {!! html()->label( __('message.from'), 'from') !!}
                             <div class="input-group date" id="paymentreservationdate_from" data-target-input="nearest">
                                <input type="text" name="from" class="form-control datetimepicker-input" autocomplete="off" value="" data-target="#paymentreservationdate_from"/>

                                <div class="input-group-append" data-target="#paymentreservationdate_from" data-toggle="datetimepicker">
                                    <div class="input-group-text"><i class="fa fa-calendar"></i></div>
                                </div>

                            </div>
                        </div>

                        <div class="col-md-3 form-group">
                            <!-- first name -->
                            {!! html()->label( __('message.till'), 'till') !!}
                            <div class="input-group date" id="paymentreservationdate" data-target-input="nearest">
                                <input type="text" name="till" class="form-control datetimepicker-input" autocomplete="off" value="" data-target="#paymentreservationdate"/>

                                <div class="input-group-append" data-target="#paymentreservationdate" data-toggle="datetimepicker">
                                    <div class="input-group-text"><i class="fa fa-calendar"></i></div>
                                </div>

                            </div>


                        </div>



                          </div>
                <!-- /.card-body -->
                    <button name="Search" type="submit"  class="btn btn-secondary"><i class="fa fa-search"></i>&nbsp;{!!Lang::get('message.search')!!}</button>
                    &nbsp;
                    <a href="{!! url('settings/paymentlog') !!}" id="reset" class="btn btn-secondary"><i class="fas fa-sync-alt"></i>&nbsp;{!!Lang::get('message.reset')!!}</a>
            


</div>

</div>


 



</style>
    <div class="card card-secondary card-outline">


<div class="card-body table-responsive">

  <div class="row">
          <div class="col-md-12">

    
         
                           
             <table id="payment-table" class="table display" cellspacing="0"  styleClass="borderless">
                     <button  value="" class="btn btn-secondary btn-sm btn-alldell" id="bulk_delete"><i class="fa fa-trash">&nbsp;&nbsp;</i> {{ __('message.delete_selected') }}</button><br /><br />
                     
                    <thead><tr>

                            <th class="no-sort"><input type="checkbox" name="select_all" onchange="checking(this)"></th>

                            <th>{{ __('message.date') }}</th>
                             <th>{{ __('message.user') }}</th>
                               <th>{{ __('message.order_no') }}</th>
                               <th>{{ __('message.amount') }}</th>
                               <th>{{ __('message.description') }}</th>
                               <th>{{ __('message.payment-method') }}</th>
                               <th>{{ __('message.status') }}</th>
                               </tr></thead>

                   </table>
            
        

   
</div>
</div>
</div>
</div>

    <link rel="stylesheet" type="text/css" href="//cdn.datatables.net/1.10.21/css/jquery.dataTables.min.css" />
<script src="//cdn.datatables.net/1.10.21/js/jquery.dataTables.min.js"></script>
<!--  <script src="https://cdn.jsdelivr.net/npm/marked/marked.min.js"></script> -->

<script>
    $(document).ready(function() {
        $(document).on('click', '.show-exception', function(event) {
            event.preventDefault();

            var exceptionMessage = $(this).data('message');

            $('.exception-message').text(exceptionMessage);

            $('#exception-modal').modal('show');
        });

        $('#payment-table').DataTable({
            processing: true,
            serverSide: true,
            order: [[{!! request()->sort_field ?: 1 !!}, 'asc']],

            ajax: {
                "url": '{!! route('get-paymentlog', "from=$from&till=$till") !!}',
                error: function(xhr) {
                    if (xhr.status == 401) {
                        alert('{{ __('message.session_expired') }}');
                        window.location.href = '/login';
                    }
                }
            },
            "oLanguage": {
                "sLengthMenu": "_MENU_ Records per page",
                "sSearch": "Search: ",
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
                { data: 'checkbox', name: 'checkbox' },
                { data: 'date', name: 'date' },
                { data: 'user', name: 'user' },
                { data: 'ordernumber', name: 'ordernumber' },
                { data: 'amount', name: 'amount' },
                { data: 'paymenttype', name: 'paymenttype' },
                { data: 'paymentmethod', name: 'paymentmethod' },
                {
                    data: 'status',
                    name: 'status',
                    render: function(data, type, row) {
                        // Handle the exception message for the 'Failed' status
                        if (row.status === 'failed') {
                            return '<a href="#" class="show-exception" data-message="' + row.exception_message + '">{{ __('message.failed') }}</a>';
                        }

                        return data;
                    }
                }
            ],
            "fnDrawCallback": function(oSettings) {
                $(function () {
                    $('[data-toggle="tooltip"]').tooltip({
                        container: 'body'
                    });
                });
                $('.loader').css('display', 'none');
            },
            "fnPreDrawCallback": function(oSettings, json) {
                $('.loader').css('display', 'block');
            },
        });
    });
</script>


<!-- <script>
    $(document).on('click','#payment-table tbody tr td .read-more',function(){
        var text=$(this).siblings(".more-text").text().replace('read more...','');
        console.log(text)
        $(this).siblings(".more-text").html(text);
        $(this).siblings(".more-text").contents().unwrap();
        $(this).remove();
    });
    $(function () {
    $('[data-toggle="popover"]').popover()
    })
</script> -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

 <script>

       function checking(e){
              $('#payment-table').find("td input[type='checkbox']").prop('checked', $(e).prop('checked'));
         }
         

         $(document).on('click','#bulk_delete',function(e){
          var id=[];
             e.preventDefault();
             $('.email:checked').each(function(){
                 id.push($(this).val())
             });
             if(id.length<=0){
                 swal.fire({
                     title: "<h2 class='swal2-title custom-title'>{{Lang::get('message.Select')}}</h2>",
                     html: "<div class='swal2-html-container custom-content'>" +
                         "<div class='section-sa'>" +
                         "<p>{{Lang::get('message.sweet_payment_log')}}</p>" + "</div>" +
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
                     html: "<div class='swal2-html-container custom-content'>" +
                         "<div class='section-sa'>" +
                         "<p>{{Lang::get('message.payment_log_delete')}}</p>" + "</div>" +
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
                         $('.email:checked').each(function () {
                             id.push($(this).val())
                         });
                         if (id.length > 0) {
                             $.ajax({
                                 url: "{!! route('paymentlog-delete') !!}",
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
                                     "<p>{{Lang::get('message.sweet_payment_log')}}</p>" + "</div>" +
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
@section('datepicker')


<script>
     $('ul.nav-sidebar a').filter(function() {
        return this.id == 'setting';
    }).addClass('active');

    // for treeview
    $('ul.nav-treeview a').filter(function() {
        return this.id == 'setting';
    }).parentsUntil(".nav-sidebar > .nav-treeview").addClass('menu-open').prev('a').addClass('active');


       $('#paymentreservationdate').datetimepicker({
       format: 'L'
   });
        $('#paymentreservationdate_from').datetimepicker({
        format: 'L'
    });
</script>





@stop














