@extends('themes.default1.layouts.master')
@section('title')
Activity Log
@stop
@section('content-header')
    <div class="col-sm-6">
        <h1>{{ __('message.all-users') }}</h1>
    </div>
    <div class="col-sm-6">
        <ol class="breadcrumb float-sm-right">
            <li class="breadcrumb-item"><a href="{{url('/')}}"><i class="fa fa-dashboard"></i> {{ __('message.home') }}</a></li>
            <li class="breadcrumb-item"><a href="{{url('settings')}}"><i class="fa fa-dashboard"></i> {{ __('message.settings') }}</a></li>
            <li class="breadcrumb-item active">{{ __('message.activity_log') }}</li>
        </ol>
    </div><!-- /.col -->
@stop

@section('content')

    <div class="row">
        <div class="col-12">
            <div class="card card-secondary card-outline collapsed-card">
                <div class="card-header">
                    <h3 class="card-title">{{Lang::get('message.filters')}}</h3>

                   <div class="card-tools">

            <button type="button" class="btn btn-tool" id="tip-search" title="Expand"> <i id="search-icon" class="fas fa-plus"></i>
                            </button>
            
        </div>
                </div>

    <!-- /.box-header -->
    <div class="card-body" table-responsive" id="advance-search" style="display:none;">
        {!! Form::open(['method'=>'get']) !!}

        <div class="row">

           
            <div class="col-md-3 form-group">
                <!-- first name -->
                {!! Form::label('from','View Logs From') !!}
                <div class="input-group date" id="log_from" data-target-input="nearest">
                    <input type="text" name="from" value="{{$from}}" id="from" class="form-control datetimepicker-input" autocomplete="off"  data-target="#log_from"/>

                    <div class="input-group-append" data-target="#log_from" data-toggle="datetimepicker">
                        <div class="input-group-text"><i class="fa fa-calendar"></i></div>
                    </div>

                </div>

            </div>
            <div class="col-md-3 form-group">
                <!-- first name -->
                {!! Form::label('till','View Logs Till') !!}

                <div class="input-group date" id="log_till" data-target-input="nearest">
                    <input type="text" name="till" value="{{$till}}" id="till" class="form-control datetimepicker-input" autocomplete="off"  data-target="#log_till"/>

                    <div class="input-group-append" data-target="#log_till" data-toggle="datetimepicker">
                        <div class="input-group-text"><i class="fa fa-calendar"></i></div>
                    </div>

                </div>


            </div>
            <div class="col-md-3 form-group">
                <!-- first name -->
                {!! Form::label('delFrom','Delete Logs From') !!}
                <div class="input-group date" id="del_from" data-target-input="nearest">
                    <input type="text" name="delFrom" id="delfrom" class="form-control datetimepicker-input" autocomplete="off"  data-target="#del_from"/>

                    <div class="input-group-append" data-target="#del_from" data-toggle="datetimepicker">
                        <div class="input-group-text"><i class="fa fa-calendar"></i></div>
                    </div>

                </div>




            </div>
              <div class="col-md-3 form-group">
                <!-- first name -->
                {!! Form::label('delTill','Delete Logs Till') !!}
                  <div class="input-group date" id="del_till" data-target-input="nearest">
                      <input type="text" name="delTill" id="deltill" class="form-control datetimepicker-input" autocomplete="off"  data-target="#del_till"/>

                      <div class="input-group-append" data-target="#del_till" data-toggle="datetimepicker">
                          <div class="input-group-text"><i class="fa fa-calendar"></i></div>
                      </div>

                  </div>


            </div>
           

          
                <div class="col-md-6">
                    <!-- {!! Form::submit('Search',['class'=>'btn btn-primary']) !!} -->
                      <button name="Search" type="submit"  class="btn btn-secondary" data-loading-text="<i class='fa fa-search fa-spin fa-1x fa-fw'>&nbsp;</i> updating..."><i class="fa fa-search">&nbsp;</i>{!!Lang::get('message.apply')!!}</button>
                    <!-- {!! Form::submit('Reset',['class'=>'btn btn-danger','id'=>'reset']) !!} -->
                    <button name="Reset" type="submit" id="reset" class="btn btn-secondary" data-loading-text="<i class='fa fa-refresh fa-spin fa-1x fa-fw'>&nbsp;</i> updating..."><i class="fa fa-sync-alt">&nbsp;</i>{!!Lang::get('message.reset')!!}</button>


                </div>
            

        </div>
<script type="text/javascript">
                    $(function () {
                    $('#reset').on('click', function () {
                      
                        $('#from').val('');
                        $('#till').val('');
                         $('#delfrom').val('');
                         $('#deltill').val('');
                       
                    });
                });
                </script>


        {!! Form::close() !!}
    </div>
</div>

        </div>
    </div>
    <div id="response"></div>
    <div class="card card-secondary card-outline">


<div class="card-body table-responsive">

  <div class="row">
          <div class="col-md-12">
            <table id="activity-table" class="table display" cellspacing="0"  styleClass="borderless">
                     <button  value="" class="btn btn-secondary btn-sm btn-alldell" id="bulk_delete"><i class="fa fa-trash">&nbsp;&nbsp;</i> {{ __('message.delmultiple') }}</button><br /><br />
                     
                    <thead><tr>
                        <th class="no-sort"><input type="checkbox" name="select_all" onchange="checking(this)"></th>
                            <th>{{ __('message.module') }}</th>
                            <th>{{ __('message.description_event') }}</th>
                             <th>{{ __('message.name_page') }}</th>
                              <th>{{ __('message.role') }}</th>
                            <!-- <th>Subject id</th> -->
                            <!-- <th>Subject type</th> -->
                                                                                                             
                             <th>{{ __('message.previous') }}</th>
                             <th>{{ __('message.updated') }}</th>
                              <th>{{ __('message.date') }}</th>
                        </tr></thead>

                   </table>
            
        

   
</div>
</div>
</div>
</div>
    <link rel="stylesheet" href="https://cdn.datatables.net/1.10.21/css/jquery.dataTables.min.css">
    <script src="https://cdn.datatables.net/1.10.21/js/jquery.dataTables.min.js"></script>

<script type="text/javascript">
     function readmore(){
                        var maxLength = 100;
                        $("#activity-table tbody tr td").each(function(){
                            var myStr = $(this).text();

                            if($.trim(myStr).length > maxLength){
                                var newStr = myStr.substring(0, maxLength);
                                 $(this).empty().html(newStr);
                                var removedStr = myStr.substring(maxLength, $.trim(myStr).length);
                                $(this).append('<span class="more-text">' + removedStr + '</span>');
                                $(this).append(' <a href="javascript:void(0);" class="read-more">{{ __('message.read_more') }}</a>');                            }
                          }); 
                         }
        $('#activity-table').DataTable({
            
            // "initComplete": function(settings, json) {
            //              readmore();
            // },
            processing: true,
            serverSide: true,
            stateSave: false,
            order: [[ 1, "asc" ]],
              ajax: {
            "url":  '{!! route('get-activity',"log_from=$from&log_till=$till&delFrom=$delFrom&delTill=$delTill") !!}',
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
                {data: 'description', name: 'description'},
                {data: 'username', name: 'username'},
                 {data: 'role', name: 'role'},
                // {data: 'subject_id', name: 'subject_id'},
                // {data: 'subject_type', name: 'subject_type'},
                
                // {data: 'causer_type', name: 'causer_type'},
                {data: 'old', name: 'old'},
                 {data: 'new', name: 'new'},
                {data: 'created_at', name: 'created_at'}
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
    $(document).on('click','#activity-table tbody tr td .read-more',function(){
        var text=$(this).siblings(".more-text").text().replace('read more...','');
        console.log(text)
        $(this).siblings(".more-text").html(text);
        $(this).siblings(".more-text").contents().unwrap();
        $(this).remove();
    });
    $(function () {
    $('[data-toggle="popover"]').popover()
    })
</script>
    <script>

       function checking(e){
              $('#activity-table').find("td input[type='checkbox']").prop('checked', $(e).prop('checked'));
         }
         

         $(document).on('click','#bulk_delete',function(){
          var id=[];
          if (confirm("Are you sure you want to delete this?"))
            {
                $('.activity:checked').each(function(){
                  id.push($(this).val())
                });
                if(id.length >0)
                { 
                   $.ajax({
                          url:"{!! route('activity-delete') !!}",
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
                    alert("{{ __('message.select_checkbox') }}");
                }
            }  

         });
   </script>
   @section('datepicker')
<script type="text/javascript">
    $('#log_from').datetimepicker({
        format: 'L'
    })
    $('#log_till').datetimepicker({
        format: 'L'
    })
    $('#del_from').datetimepicker({
        format: 'L'
    })
    $('#del_till').datetimepicker({
        format: 'L'
    })
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
  















