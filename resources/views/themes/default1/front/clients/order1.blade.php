@extends('themes.default1.layouts.front.master')
@section('title')
Orders
@stop
@section('page-header')
<br>
Cart
@stop
@section('nav-orders')
active
@stop
@section('page-heading')
 My Orders
@stop
@section('breadcrumb')
    @if(Auth::check())
        <li><a class="text-primary" href="{{url('my-invoices')}}">Home</a></li>
    @else
         <li><a class="text-primary" href="{{url('login')}}">Home</a></li>
    @endif
     <li class="active text-dark">My Orders</li>
@stop 

@section('content')
<style>
[type=search] {
        padding-right: 20px !important;
}
</style>

      <div class="row pt-2">

            @include('themes.default1.front.clients.navbar')


                <div class="col-lg-9">

                   <div class="tab-pane tab-pane-navigation active" id="invoices" role="tabpanel">

                        <div id="examples" class="container py-4">

                            <div class="row">

                                <div class="col">


                                                <table id="order-table"class="table table-striped table-bordered">
                                                 <thead><tr>
                                                <th>Product Name</th>
                                                <th>Purchase Date</th>
                                                <th>Order No</th>
                                                <th>Version</th>
                                                <th>Agents</th>
                                                <th>Expiry Date</th>
                                                <th>Action</th>
                                            </tr></thead>
                                            </table>
                                </div>
                            </div>
                        </div>
                    </div>

                </div>

            </div>
                    <script src="https://cdn.datatables.net/1.10.22/js/jquery.dataTables.min.js"></script>

     <link rel="stylesheet" type="text/css" href="//cdn.datatables.net/1.10.22/css/jquery.dataTables.min.css" />
    <script src="{{asset("common/js/jquery-2.1.4.js")}}" type="text/javascript"></script>
    <script src="{{asset("common/js/jquery2.1.1.min.js")}}" type="text/javascript"></script>
    <script src="//cdn.datatables.net/1.10.22/js/jquery.dataTables.min.js"></script>
    <script type="text/javascript">
        $('#order-table').DataTable({
            processing: true,
            serverSide: true,
            order: [[ 4, "asc" ]],
            ajax: {
            "url":  '{!! route('get-my-orders') !!}',
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
                "sProcessing": '<img id="blur-bg" class="backgroundfadein" style="top:40%;left:50%; width: 50px; height:50 px; display: block; position:    fixed;" src="{!! asset("lb-faveo/media/images/gifloader3.gif") !!}">'
            },
           
        
    
    
            columns: [
                 {data: 'product_name', name: 'products.name'},
                 {data: 'date', name: 'date'},
                 {data: 'number', name: 'orders.number'},
                 {data: 'version', name: 'version'},
                 {data: 'agents', name: 'agents'},
                 {data: 'expiry', name: 'expiry'},
              
                // {data: 'group', name: 'Group'},
                // {data: 'currency', name: 'Currency'},
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


@stop











