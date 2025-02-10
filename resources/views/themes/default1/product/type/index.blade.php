@extends('themes.default1.layouts.master')
@section('title')
Product Types
@stop
@section('content-header')
<h1>
Product Types
</h1>
  <ol class="breadcrumb">
        <li><a href="{{url('/')}}"><i class="fa fa-dashboard"></i> {{ __('message.home') }}</a></li>
        <li class="active">{{ __('message.all_types') }}</li>
      </ol>
@stop
@section('content')

    <div class="box box-primary">

    <div class="box-header">
        @if (count($errors) > 0)
        <div class="alert alert-danger alert-dismissable">
             <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
            <strong>{{ __('message.whoops') }}</strong> {{ __('message.input_problem') }}<br><br>
            <ul>
                @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
        @endif

        @if(Session::has('success'))
        <div class="alert alert-success alert-dismissable">
            <i class="fa fa-check"></i>
             <b>{{Lang::get('message.success')}}!</b>
            <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
            {{Session::get('success')}}
        </div>
        @endif
        <!-- fail message -->
        @if(Session::has('fails'))
        <div class="alert alert-danger alert-dismissable">
            <i class="fa fa-ban"></i>
            <b>{{Lang::get('message.alert')}}!</b> {{Lang::get('message.failed')}}.
            <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
            {{Session::get('fails')}}
        </div>
        @endif
        <div id="response"></div>
        <h4>{{Lang::get('message.types')}}
            <a href="#create-type" data-toggle="modal" data-target="#create-type" class="btn btn-primary btn-sm pull-right"><span class="glyphicon glyphicon-plus"></span>&nbsp;&nbsp;{{Lang::get('message.create')}}</a></h4>
    </div>
       @include('themes.default1.product.type.create-type')
        @include('themes.default1.product.type.edit-type')
       <div class="box-body">
             
             <div class="row">
            
            <div class="col-md-12">
               
                 <table id="products-table" class="table display" cellspacing="0" width="100%" styleClass="borderless">
                     <button  value="" class="btn btn-danger btn-sm btn-alldell" id="bulk_delete"><i class="fa fa-trash"></i>&nbsp;&nbsp;{{Lang::get('message.delmultiple')}}</button><br /><br />
                    <thead><tr>
                        <th class="no-sort" style="width:20px"><input type="checkbox" name="select_all" onchange="checking(this)"></th>
                            <th>{{ __('message.name_page') }}</th>
                            <th>{{ __('message.action') }}</th>
                        </tr></thead>

                   </table>
            </div>
        </div>

    </div>

</div>

<link rel="stylesheet" type="text/css" href="//cdn.datatables.net/1.10.12/css/jquery.dataTables.min.css" />
<script src="//cdn.datatables.net/1.10.12/js/jquery.dataTables.min.js"></script>
<script type="text/javascript">
        $('#products-table').DataTable({
            processing: true,
            serverSide: true,
             stateSave: false,
              order: [[ 0, "desc" ]],
            ajax: '{!! route('get-type') !!}',
            "oLanguage": {
                "sLengthMenu": "_MENU_ Records per page",
                "sSearch"    : "Search: ",
                "sProcessing": '<img id="blur-bg" class="backgroundfadein" style="top:40%;left:50%; width: 50px; height:50 px; display: block; position:    fixed;" src="{!! asset("lb-faveo/media/images/gifloader3.gif") !!}">'
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
                {data: 'type_name', name: 'type_name'},
                {data: 'action', name: 'action'}
            ],
            "fnDrawCallback": function( oSettings ) {
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
        $('.editType').click(function(){
           var typeName = $(this).attr('data-name');
           var typeId   = $(this).attr('data-id');
            $("#edit-type").modal('show');
            $('#tname').val(typeName);
             var url = "{{url('product-type/')}}"+"/"+typeId
        $("#type-edit-form").attr('action', url)
        })
    }

      $(document).on('click','#bulk_delete',function(){
      var id=[];
      if (confirm("Are you sure you want to delete this?"))
        {
            $('.type_checkbox:checked').each(function(){
              id.push($(this).val())
            });
            if(id.length >0)
            {
               $.ajax({
                      url:"{!! route('type-delete') !!}",
                      method:"get",
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


