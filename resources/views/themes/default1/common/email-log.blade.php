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

<div class="modal fade" id="emailModal" tabindex="-1" role="dialog" aria-labelledby="emailModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="emailModalLabel">Email Subject</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>

            <div class="modal-body">
                <div class="d-flex justify-content-center">
                    <div  id="emailBody" >
                        <!-- Email content loads here -->
                    </div>
                </div>
            </div>

            <div class="modal-footer justify-content-between">
                <button type="button" class="btn btn-default" data-dismiss="modal">
                    {{ __('message.cancel')}}
                </button>
            </div>
        </div>
    </div>
</div>


    <link rel="stylesheet" type="text/css" href="//cdn.datatables.net/1.10.21/css/jquery.dataTables.min.css" />
<script src="//cdn.datatables.net/1.10.21/js/jquery.dataTables.min.js"></script>
<!--  <script src="https://cdn.jsdelivr.net/npm/marked/marked.min.js"></script> -->

<script type="text/javascript">
    $(document).on('click', '.view-mail', function (e) {
        e.preventDefault();
        const id = $(this).data('id');

        $.get("{{ url('/email-log/body') }}/" + id, function (res) {
            $('#emailModalLabel').text(res.data.subject);
            $('#emailBody').html(res.data.body);
            $('#emailModal').modal('show');
        });
    });

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
                     title: "<h2 class='swal2-title custom-title'>{{Lang::get('message.Select')}}</h2>",
                     html: "<div  class='swal2-html-container custom-content'>" +
                         "<div class='section-sa'>" +
                         "<p>{{Lang::get('message.sweet_email_log')}}</p>" + "</div>" +
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
                         "<p>{{Lang::get('message.email_log_delete')}}</p>" + "</div>" +
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
                                 title: "<h2 class='swal2-title custom-title'>{{Lang::get('message.Select')}}</h2>",
                                 html: "<div class='swal2-html-container custom-content'>" +
                                     "<div class='section-sa'>" +
                                     "<p>{{Lang::get('message.sweet_email_log')}}</p>" + "</div>" +
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


       $('#mailligreservationdate').datetimepicker({
       format: 'L'
   });
        $('#maillogreservationdate_from').datetimepicker({
        format: 'L'
    });
</script>


@stop














