@extends('themes.default1.layouts.master')
@section('title')
Categories
@stop
@section('content-header')
    <div class="col-sm-6">
        <h1>{{ __('message.all_categories')}}</h1>
    </div>
    <div class="col-sm-6">
        <ol class="breadcrumb float-sm-right">
            <li class="breadcrumb-item"><a href="{{url('/')}}"><i class="fa fa-dashboard"></i> {{ __('message.home')}}</a></li>
            <li class="breadcrumb-item active">{{ __('message.all_categories')}}</li>
        </ol>
    </div><!-- /.col -->


@stop
@section('content')

    <div class="card card-secondary card-outline">
        <div class="card-header">
            <h3 class="card-title">{{Lang::get('message.category')}}</h3>
            <div id="response"></div>
            <div class="card-tools">
                <a href="#create-category" data-toggle="modal" data-target="#create-category" class="btn btn-default btn-sm pull-right"><span class="fa fa-plus"></span>&nbsp;&nbsp;{{Lang::get('message.create')}}</a></h4>

            </div>
        </div>

       @include('themes.default1.category.create-category')
        @include('themes.default1.category.edit-category')
       <div class="card-body table-responsive">
             
             <div class="row">
            
            <div class="col-md-12">
               
                 <table id="products-table" class="table display" cellspacing="0" width="100%" styleClass="borderless">
                     <button  value="" class="btn btn-secondary btn-sm btn-alldell" id="bulk_delete"><i class="fa fa-trash"></i>&nbsp;&nbsp;{{Lang::get('message.delmultiple')}}</button><br /><br />
                    <thead><tr>
                        <th class="no-sort" style="width:20px"><input type="checkbox" name="select_all" onchange="checking(this)"></th>
                            <th>{{ __('message.category_name')}}</th>
                            <th>{{ __('message.action')}}</th>
                        </tr></thead>

                   </table>
            </div>
        </div>

    </div>

</div>
<script>
     $('ul.nav-sidebar a').filter(function() {
        return this.id == 'category';
    }).addClass('active');

    // for treeview
    $('ul.nav-treeview a').filter(function() {
        return this.id == 'category';
    }).parentsUntil(".nav-sidebar > .nav-treeview").addClass('menu-open').prev('a').addClass('active');
</script>
<link rel="stylesheet" type="text/css" href="//cdn.datatables.net/1.10.21/css/jquery.dataTables.min.css" />
<script src="//cdn.datatables.net/1.10.21/js/jquery.dataTables.min.js"></script>
<script type="text/javascript">
        $('#products-table').DataTable({
            processing: true,
            serverSide: true,
             stateSave: false,
              order: [[ 0, "desc" ]],
            ajax: '{!! route('get-category') !!}',
            "oLanguage": {
                "sLengthMenu": "_MENU_ Records per page",
                "sSearch"    : "Search: ",
                "sProcessing": ' <div class="overlay"><i class="fas fa-3x fa-sync-alt fa-spin"></i><div class="text-bold pt-2">{{ __('message.loading')}}</div></div>'
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
                {data: 'category_name', name: 'category_name'},
                {data: 'action', name: 'action'}
            ],
            "fnDrawCallback": function( oSettings ) {
              '<label data-toggle="tooltip" style="font-weight:500;" data-placement="top" title="safdsf"></label>'
                bindEditButton();
                $('.loader').css('display', 'none');
            },
            "fnPreDrawCallback": function(oSettings, json) {
                $('.loader').css('display', 'block');
            },
        });
    </script>
 <script>
    function checking(e){
      $('#products-table').find("td input[type='checkbox']").prop('checked',$(e).prop('checked'));
    }


    function bindEditButton() {
        
        $('.editCat').click(function(){
           var catName = $(this).attr('data-name');
           var catId   = $(this).attr('data-id');
            $("#edit-category").modal('show');
            $('#cname').val(catName);
             var url = "{{url('category/')}}"+"/"+catId
        $("#category-edit-form").attr('action', url)
        })
    }

      $(document).on('click','#bulk_delete',function(){
      var id=[];
      if (confirm("Are you sure you want to delete this?"))
        {
            $('.category_checkbox:checked').each(function(){
              id.push($(this).val())
            });
            if(id.length >0)
            {
               $.ajax({
                      url:"{!! route('category-delete') !!}",
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
                alert("{{ __('message.select_checkbox')}}");
            }
        }  

     });
 </script>


@stop


