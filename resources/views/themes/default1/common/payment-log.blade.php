@extends('themes.default1.layouts.master')
@section('title')
Payment Logs
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
        <h1>Payment Log</h1>
    </div>
    <div class="col-sm-6">
        <ol class="breadcrumb float-sm-right">
            <li class="breadcrumb-item"><a href="{{url('/')}}"><i class="fa fa-dashboard"></i> Home</a></li>
            <li class="breadcrumb-item"><a href="{{url('settings')}}"><i class="fa fa-dashboard"></i> Settings</a></li>
            <li class="breadcrumb-item active">Payment Log</li>
        </ol>
    </div><!-- /.col -->
@stop
@section('content')


<div class="card card-secondary card-outline">

    <div class="card-header">

        <div id="response"></div>
        <h5>Search Here
          </h5>
    </div>


<div class="modal fade" id="exception-modal" tabindex="-1" role="dialog" aria-labelledby="exception-modal-label" aria-hidden="true" style="margin-top: 10%;">
    <div class="modal-dialog modal-dialog-scrollable modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title" id="exception-modal-label">Exception Message</h4>
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
            </div>
            <div class="modal-body">
                <p class="exception-message"></p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

 
        <div class="card-body">


            {!! html()->form('GET')->open() !!}

            <div class="row">
                         <div class="col-md-3 form-group">
                            <!-- first name -->
                             {!! html()->label('From', 'from') !!}
                             <div class="input-group date" id="paymentreservationdate_from" data-target-input="nearest">
                                <input type="text" name="from" class="form-control datetimepicker-input" autocomplete="off" value="" data-target="#paymentreservationdate_from"/>

                                <div class="input-group-append" data-target="#paymentreservationdate_from" data-toggle="datetimepicker">
                                    <div class="input-group-text"><i class="fa fa-calendar"></i></div>
                                </div>

                            </div>
                        </div>

                        <div class="col-md-3 form-group">
                            <!-- first name -->
                            {!! html()->label('Till', 'till') !!}
                            <div class="input-group date" id="paymentreservationdate" data-target-input="nearest">
                                <input type="text" name="till" class="form-control datetimepicker-input" autocomplete="off" value="" data-target="#paymentreservationdate"/>

                                <div class="input-group-append" data-target="#paymentreservationdate" data-toggle="datetimepicker">
                                    <div class="input-group-text"><i class="fa fa-calendar"></i></div>
                                </div>

                            </div>


                        </div>



                          </div>
                <!-- /.card-body -->
                    <button name="Search" type="submit"  class="btn btn-secondary"><i class="fa fa-search"></i>&nbsp;{!!Lang::get('Search')!!}</button>
                    &nbsp;
                    <a href="{!! url('settings/paymentlog') !!}" id="reset" class="btn btn-secondary"><i class="fas fa-sync-alt"></i>&nbsp;{!!Lang::get('Reset')!!}</a>
            


</div>

</div>


 



</style>
    <div class="card card-secondary card-outline">


<div class="card-body table-responsive">

  <div class="row">
          <div class="col-md-12">

    
         
                           
             <table id="payment-table" class="table display" cellspacing="0"  styleClass="borderless">
                     <button  value="" class="btn btn-secondary btn-sm btn-alldell" id="bulk_delete"><i class="fa fa-trash">&nbsp;&nbsp;</i> Delete Selected</button><br /><br />
                     
                    <thead><tr>

                            <th class="no-sort"><input type="checkbox" name="select_all" onchange="checking(this)"></th>

                            <th>Date</th>
                             <th>User</th>   
                               <th>Order No</th> 
                               <th>Amount</th>
                               <th>Description</th>
                               <th>Payment Method</th>   
                               <th>Status</th>
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
                        alert('Your session has expired. Please login again to continue.');
                        window.location.href = '/login';
                    }
                }
            },
            "oLanguage": {
                "sLengthMenu": "_MENU_ Records per page",
                "sSearch": "Search: ",
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
                            return '<a href="#" class="show-exception" data-message="' + row.exception_message + '">Failed</a>';
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

 <script>

       function checking(e){
              $('#payment-table').find("td input[type='checkbox']").prop('checked', $(e).prop('checked'));
         }
         

         $(document).on('click','#bulk_delete',function(){
          var id=[];
          if (confirm("Are you sure you want to delete this?"))
            {
                $('.email:checked').each(function(){
                  id.push($(this).val())
                });
                if(id.length >0)
                { 
                   $.ajax({
                          url:"{!! route('paymentlog-delete') !!}",
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
                else
                {
                    alert("Please select at least one checkbox");
                    return false;
                }
                
            }  
            return false;

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














