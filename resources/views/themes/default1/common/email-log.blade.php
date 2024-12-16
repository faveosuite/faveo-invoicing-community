@extends('themes.default1.layouts.master')
@section('title')
Email Logs
@stop
@section('content-header')
    <div class="col-sm-6">
        <h1>Email Log</h1>
    </div>
    <div class="col-sm-6">
        <ol class="breadcrumb float-sm-right">
            <li class="breadcrumb-item"><a href="{{url('/')}}"><i class="fa fa-dashboard"></i> Home</a></li>
            <li class="breadcrumb-item"><a href="{{url('settings')}}"><i class="fa fa-dashboard"></i> Settings</a></li>
            <li class="breadcrumb-item active">Email Log</li>
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
 
        <div class="card-body">


            {!! html()->form('GET')->open() !!}

            <div class="row">
                         <div class="col-md-3 form-group">
                            <!-- first name -->
                             {!! html()->label('From', 'from') !!}
                             <div class="input-group date" id="maillogreservationdate_from" data-target-input="nearest">
                                <input type="text" name="mailfrom" class="form-control datetimepicker-input" autocomplete="off" value="" data-target="#maillogreservationdate_from"/>

                                <div class="input-group-append" data-target="#maillogreservationdate_from" data-toggle="datetimepicker">
                                    <div class="input-group-text"><i class="fa fa-calendar"></i></div>
                                </div>

                            </div>
                        </div>

                        <div class="col-md-3 form-group">
                            <!-- first name -->
                            {!! html()->label('Till', 'till') !!}
                            <div class="input-group date" id="mailligreservationdate" data-target-input="nearest">
                                <input type="text" name="mailtill" class="form-control datetimepicker-input" autocomplete="off" value="" data-target="#mailligreservationdate"/>

                                <div class="input-group-append" data-target="#mailligreservationdate" data-toggle="datetimepicker">
                                    <div class="input-group-text"><i class="fa fa-calendar"></i></div>
                                </div>

                            </div>


                        </div>



                          </div>
                <!-- /.card-body -->
                    <button name="Search" type="submit"  class="btn btn-secondary"><i class="fa fa-search"></i>&nbsp;{!!Lang::get('Search')!!}</button>
                    &nbsp;
                    <a href="{!! url('settings/maillog') !!}" id="reset" class="btn btn-secondary"><i class="fas fa-sync-alt"></i>&nbsp;{!!Lang::get('Reset')!!}</a>
            


</div>

</div>


</style>
    <div class="card card-secondary card-outline">


<div class="card-body table-responsive">

  <div class="row">
          <div class="col-md-12">

	
         
                           
             <table id="email-table" class="table display" cellspacing="0"  styleClass="borderless">
                     <button  value="" class="btn btn-secondary btn-sm btn-alldell" id="bulk_delete"><i class="fa fa-trash">&nbsp;&nbsp;</i> Delete Selected</button><br /><br />
                     
                    <thead><tr>

                            <th class="no-sort"><input type="checkbox" name="select_all" onchange="checking(this)"></th>

                            <th>Date</th>
                            <th>From</th>
                             <th>To</th>   
                               <th>Subject</th>      
                           
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
<!-- <script>
    $(document).on('click','#email-table tbody tr td .read-more',function(){
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
              $('#email-table').find("td input[type='checkbox']").prop('checked', $(e).prop('checked'));
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
                          url:"{!! route('email-delete') !!}",
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
                }
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














