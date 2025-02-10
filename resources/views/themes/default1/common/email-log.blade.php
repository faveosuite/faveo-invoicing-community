@extends('themes.default1.layouts.master')
@section('title')
     {{ __('message.email_logs') }}
@stop
@section('content-header')
    <div class="col-sm-6">
        <h1>{{ __('message.email_log') }}</h1>
    </div>
    <div class="col-sm-6">
        <ol class="breadcrumb float-sm-right">
            <li class="breadcrumb-item"><a href="{{url('/')}}"><i class="fa fa-dashboard"></i> {{ __('message.home') }}</a></li>
            <li class="breadcrumb-item"><a href="{{url('settings')}}"><i class="fa fa-dashboard"></i> {{ __('message.settings') }}</a></li>
            <li class="breadcrumb-item active">{{ __('message.email_log') }}</li>
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
 
        <div class="card-body">


            {!! html()->form('GET')->open() !!}

            <div class="row">
                         <div class="col-md-3 form-group">
                            <!-- first name -->
                             {!! html()->label( __('message.from'), 'from') !!}
                             <div class="input-group date" id="maillogreservationdate_from" data-target-input="nearest">
                                <input type="text" name="mailfrom" class="form-control datetimepicker-input" autocomplete="off" value="" data-target="#maillogreservationdate_from"/>

                                <div class="input-group-append" data-target="#maillogreservationdate_from" data-toggle="datetimepicker">
                                    <div class="input-group-text"><i class="fa fa-calendar"></i></div>
                                </div>

                            </div>
                        </div>

                        <div class="col-md-3 form-group">
                            <!-- first name -->
                            {!! html()->label( __('message.till'), 'till') !!}
                            <div class="input-group date" id="mailligreservationdate" data-target-input="nearest">
                                <input type="text" name="mailtill" class="form-control datetimepicker-input" autocomplete="off" value="" data-target="#mailligreservationdate"/>

                                <div class="input-group-append" data-target="#mailligreservationdate" data-toggle="datetimepicker">
                                    <div class="input-group-text"><i class="fa fa-calendar"></i></div>
                                </div>

                            </div>


                        </div>



                          </div>
                <!-- /.card-body -->
                    <button name="Search" type="submit"  class="btn btn-secondary"><i class="fa fa-search"></i>&nbsp;{!!Lang::get('message.search')!!}</button>
                    &nbsp;
                    <a href="{!! url('settings/maillog') !!}" id="reset" class="btn btn-secondary"><i class="fas fa-sync-alt"></i>&nbsp;{!!Lang::get('message.reset')!!}</a>
            


</div>

</div>


</style>
    <div class="card card-secondary card-outline">


<div class="card-body table-responsive">

  <div class="row">
          <div class="col-md-12">

	
         
                           
             <table id="email-table" class="table display" cellspacing="0"  styleClass="borderless">
                     <button  value="" class="btn btn-secondary btn-sm btn-alldell" id="bulk_delete"><i class="fa fa-trash">&nbsp;&nbsp;</i> {{ __('message.delmultiple') }}</button><br /><br />
                     
                    <thead><tr>

                            <th class="no-sort"><input type="checkbox" name="select_all" onchange="checking(this)"></th>

                            <th>{{ __('message.date') }}</th>
                            <th>{{ __('message.from') }}</th>
                             <th>{{ __('message.to') }}</th>
                               <th>{{ __('message.sub') }}</th>
                           
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

<script type="text/javascript">
  
        $('#email-table').DataTable({


             processing: true,
             serverSide: true,
             ordering: true,
             searching:true,
             select: true,
            order: [[ 1, "asc" ]],
             ajax: {
            url: "{!! route('get-email', ['from' => $from, 'till' => $till]) !!}",

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
                infoEmpty:      "{{ __('message.info_empty') }}",
                infoFiltered:   "{{ __('message.info_filtered') }}",
                lengthMenu:     "{{ __('message.length_menu') }}",
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
                {data: 'date', name: 'date'},
                {data: 'from', name: 'from'},
                {data: 'to', name: 'to'},
                 {data: 'subject', name: 'subject'},
                
                 {data: 'status', name: 'status'},
                
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

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <script>

       function checking(e){
              $('#email-table').find("td input[type='checkbox']").prop('checked', $(e).prop('checked'));
         }
         

         $(document).on('click','#bulk_delete',function(e){
          var id=[];
          e.preventDefault();
             $('.email:checked').each(function(){
                 id.push($(this).val())
             });
             if(id.length<=0){
                 swal.fire({
                     title: "<h2 style='text-align: left; padding-left: 17px !important; margin-bottom:10px !important;'>{{Lang::get('message.Select')}}</h2>",
                     html: "<div  style='display: flex; flex-direction: column; align-items:stretch; width:100%; margin:0px !important'>" +
                         "<div style='border-top: 1px solid #ccc; border-bottom: 1px solid #ccc;padding-top: 13px;'>" +
                         "<p style='text-align: left; margin-left:17px'>{{Lang::get('message.sweet_email_log')}}</p>" + "</div>" +
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
             }
             else {
                 var swl = swal.fire({
                     title: "<h2 style='text-align: left; padding-left: 17px !important; margin-bottom:10px !important;'>{{Lang::get('message.Delete')}}</h2>",
                     html: "<div  style='display: flex; flex-direction: column; align-items:stretch; width:100%; margin:0px !important'>" +
                         "<div style='border-top: 1px solid #ccc; border-bottom: 1px solid #ccc;padding-top: 13px;'>" +
                         "<p style='text-align: left; margin-left:17px'>{{Lang::get('message.email_log_delete')}}</p>" + "</div>" +
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
                         $('.email:checked').each(function () {
                             id.push($(this).val())
                         });
                         if (id.length > 0) {
                             $.ajax({
                                 url: "{!! route('email-delete') !!}",
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
                                 title: "<h2 style='text-align: left; padding-left: 17px !important; margin-bottom:10px !important;'>{{Lang::get('message.Select')}}</h2>",
                                 html: "<div  style='display: flex; flex-direction: column; align-items:stretch; width:100%; margin:0px !important'>" +
                                     "<div style='border-top: 1px solid #ccc; border-bottom: 1px solid #ccc;padding-top: 13px;'>" +
                                     "<p style='text-align: left; margin-left:17px'>{{Lang::get('message.sweet_email_log')}}</p>" + "</div>" +
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


       $('#mailligreservationdate').datetimepicker({
       format: 'L'
   });
        $('#maillogreservationdate_from').datetimepicker({
        format: 'L'
    });
</script>


@stop














