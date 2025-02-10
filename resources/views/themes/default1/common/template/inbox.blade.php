@extends('themes.default1.layouts.master')
@section('title')
Templates
@stop
@section('content-header')
    <div class="col-sm-6">
        <h1>{{ __('message.templates') }}</h1>
    </div>
    <div class="col-sm-6">
        <ol class="breadcrumb float-sm-right">
            <li class="breadcrumb-item"><a href="{{url('/')}}"><i class="fa fa-dashboard"></i> {{ __('message.home') }}</a></li>
            <li class="breadcrumb-item"><a href="{{url('settings')}}"><i class="fa fa-dashboard"></i> {{ __('message.settings') }}</a></li>
            <li class="breadcrumb-item active">{{ __('message.template') }}</li>
        </ol>
    </div><!-- /.col -->
@stop
@section('content')



<div class="card card-secondary card-outline">





    <div class="card-body table-responsive">
        <div class="row">

            <div class="col-md-12">
                <table id="templates-table" class="table display" cellspacing="0" width="100%" styleClass="borderless">
                    <thead><tr>
                         <th>{{ __('message.sub') }}</th>
                          <th>{{ __('message.type') }}</th>
                           <th>{{ __('message.action') }}</th>
                        </tr></thead>
                     </table>
            
                <script>
                    $('#delete').click(function () {
                        $.ajax({
                            url: "addons-delete",
                            type: "GET",
                            data: $('#check:checked').serialize(),
                            beforeSend: function () {
                                $('#gif').show();
                            },
                            success: function (data) {
                                $('#gif').hide();
                                $('#response').html(data);
                                location.reload();
                            }
                        });
                    });
                </script>
            </div>
        </div>

    </div>

</div>

<link rel="stylesheet" type="text/css" href="//cdn.datatables.net/1.10.21/css/jquery.dataTables.min.css" />
<script src="//cdn.datatables.net/1.10.21/js/jquery.dataTables.min.js"></script>
<script type="text/javascript">
        $('#templates-table').DataTable({
            processing: true,
            serverSide: true,
            stateSave: false,
            ordering: true,
            searching:true,
            select: true,
            order: [[ 0, "desc" ]],
            ajax: {
            "url":  '{!! route('get-templates') !!}',
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
                {data: 'name', name: 'name'},
                {data: 'type', name: 'Type'},
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
<script>
     function checking(e){
          
          $('#templates-table').find("td input[type='checkbox']").prop('checked', $(e).prop('checked'));
     }
     

     $(document).on('click','#bulk_delete',function(){
      var id=[];
      if (confirm("Are you sure you want to delete this?"))
        {
            $('.template_checkbox:checked').each(function(){
              id.push($(this).val())
            });
            if(id.length >0)
            {
               $.ajax({
                      url:"{!! route('templates-delete') !!}",
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
@stop