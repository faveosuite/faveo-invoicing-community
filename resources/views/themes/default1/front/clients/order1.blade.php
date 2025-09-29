@extends('themes.default1.layouts.front.master')
@section('title')
 {{ __('message.orders') }}
@stop
@section('page-header')
<br>
{{ __('message.cart') }}
@stop
@section('nav-orders')
active
@stop
@section('page-heading')
    {{ __('message.my_orders')}}
@stop
@section('breadcrumb')
    <style>
        option
        {
            font-size: 11px !important;
        }
    </style>
    @if(Auth::check())
        <li><a class="text-primary" href="{{url('my-invoices')}}">{{ __('message.home')}}</a></li>
    @else
         <li><a class="text-primary" href="{{url('login')}}">{{ __('message.home')}}</a></li>
    @endif
     <li class="active text-dark">{{ __('message.my_orders')}}</li>
@stop 

@section('content')
<style>
[type=search] {
        padding-right: 20px !important;
}

</style>

      <div class="row pt-2">

            @include('themes.default1.front.clients.navbar')


                <div class="col-lg-10">

                   <div class="tab-pane tab-pane-navigation active" id="invoices" role="tabpanel">

                        <div id="examples" class="container py-4">

                            <div class="row">

                                <div class="col">
                                    <div class="row">

                                    <div class="col pb-3">
                                    <div class="table-responsive">
                                    <table id="order-table" class="table table-striped table-bordered mw-auto">
                                                 <thead><tr>
                                                <th>{{ __('message.product_name')}}</th>
                                                <th>{{ __('message.purchase_date')}}</th>
                                                <th>{{ __('message.order_no')}}</th>
                                                <th>{{ __('message.agents')}}</th>
                                                <th>{{ __('message.expiry_date')}}</th>
                                                <th>{{ __('message.action')}}</th>
                                            </tr></thead>
                                            </table>
                                            </div>
                                            </div>
                                            </div>
                                </div>
                            </div>
                        </div>
                    </div>

                </div>

            </div>
<link rel="stylesheet" type="text/css" href="//cdn.datatables.net/1.10.22/css/jquery.dataTables.min.css" />
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.7.1/jquery.min.js" type="text/javascript"></script>
<script src="//cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>


    <script type="text/javascript">
        // Function to remove error when input'id' => 'changePasswordForm'ng data
        const removeErrorMessage = (field) => {
            field.classList.remove('is-invalid');
            const error = field.nextElementSibling;
            if (error && error.classList.contains('error')) {
                error.remove();
            }
        };

//This Function is changed to using this url you will get paginated data not the datatable.
        $('#order-table').DataTable({
            processing: true,
            serverSide: true,
            order: [[ 4, "asc" ]],
            ajax: {
            "url": '{!! route('get-my-orders', "updated_ends_at=$request->updated_ends_at") !!}',
               error: function(xhr) {
               if(xhr.status == 401) {
                   alert(@json(__('message.session_expired')));
                window.location.href = '/login';
               }
            }

            },
            "oLanguage": {
                "sLengthMenu": "_MENU_ Records per page",
                "sSearch"    : "Search: ",
                "sProcessing": '<img id="blur-bg" class="backgroundfadein" style="top:40%;left:50%; width: 50px; height:50 px; display: block; position:    fixed;" src="{!! asset("lb-faveo/media/images/gifloader3.gif") !!}">'
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
                loadingRecords: "{{ __('message.loading_records') }}",
                search:         "{{ __('message.table_search') }}",
            },
           
        
    
    
            columns: [
                 {data: 'product_name', name: 'products.name'},
                 {data: 'date', name: 'date'},
                 {data: 'number', name: 'orders.number'},
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











