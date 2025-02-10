@extends('themes.default1.layouts.master')
@section('title')
Products
@stop
@section('content-header')
    <div class="col-sm-6">
        <h1>{{ __('message.all-products') }}</h1>
    </div>
    <div class="col-sm-6">
        <ol class="breadcrumb float-sm-right">
            <li class="breadcrumb-item"><a href="{{url('/')}}"><i class="fa fa-dashboard"></i> {{ __('message.home') }}</a></li>
            <li class="breadcrumb-item active">{{ __('message.all-products') }}</li>
        </ol>
    </div><!-- /.col -->
@stop
@section('content')

    <div class="card card-secondary card-outline">

    <div class="card-header">
        <h3 class="card-title">{{ __('message.products') }}</h3>
        <div class="card-tools">
            <a href="{{url('products/create')}}" class="btn btn-default btn-sm pull-right"><span class="fas fa-plus"></span>&nbsp;{{ __('message.add_product') }}</a>


        </div>


    </div>
        <div id="response"></div>
       <div class="card-body table-responsive">
             
             <div class="row">
            
            <div class="col-md-12">
               
                 <table id="products-table" class="table display" cellspacing="0" width="100%" styleClass="borderless">
                     <button  value="" class="btn btn-secondary btn-sm btn-alldell" id="bulk_delete"><i class="fa fa-trash"></i>&nbsp;&nbsp;{{Lang::get('message.delmultiple')}}</button><br /><br />
                    <thead><tr>
                        <th class="no-sort"><input type="checkbox" name="select_all" onchange="checking(this)"></th>
                            <th>{{ __('message.name_page') }}</th>
                            <th>{{ __('message.image') }}</th>
                            <th>{{ __('message.license-type') }}</th>
                            <th>{{ __('message.group') }}</th>
                            <th>{{ __('message.action') }}</th>
                        </tr></thead>

                   </table>
            </div>
        </div>

    </div>

</div>
<div id="gif"></div>

    <link rel="stylesheet" href="https://cdn.datatables.net/1.10.21/css/jquery.dataTables.min.css">
    <script src="https://cdn.datatables.net/1.10.21/js/jquery.dataTables.min.js"></script>
<script type="text/javascript">
        var data  = '';
        $('#products-table').DataTable({
            processing: true,
            serverSide: true,
              order: [[ 0, "desc" ]],
              ajax: {
             "url":  '{!! route('get-products',"value=$data") !!}',
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
                {data: 'image', name: 'image'},
                {data: 'type', name: 'type'},
                {data: 'group', name: 'group'},
                {data: 'Action', name: 'Action'},
              
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
        return this.id == 'all_product';
    }).addClass('active');

    // for treeview
    $('ul.nav-treeview a').filter(function() {
        return this.id == 'all_product';
    }).parentsUntil(".nav-sidebar > .nav-treeview").addClass('menu-open').prev('a').addClass('active');
</script>
@stop


@section('icheck')

<script>
     function checking(e){
          
          $('#products-table').find("td input[type='checkbox']").prop('checked', $(e).prop('checked'));
     }
     

     $(document).on('click','#bulk_delete',function(){
      var id=[];
      if (confirm("Are you sure you want to delete this?"))
        {
            $('.product_checkbox:checked').each(function(){
              id.push($(this).val())
            });
            if(id.length >0)
            {
               $.ajax({
                      url:"{!! route('products-delete') !!}",
                      method:"delete",
                      data: $('#check:checked').serialize(),
                      beforeSend: function () {
                  $('#gif').html( "<img id='blur-bg' class='backgroundfadein' style='top:40%;left:50%; width: 50px; height:50 px; display: block; position:    fixed;' src='{!! asset('lb-faveo/media/images/gifloader3.gif') !!}'>");
                },
                success: function (data) {
                $('#gif').html('');
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