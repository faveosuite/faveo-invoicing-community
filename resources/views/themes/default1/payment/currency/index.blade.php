@extends('themes.default1.layouts.master')
@section('title')
    {{ __('message.currency') }}
@stop

@section('content-header')
<style>
.switch {
  position: relative;
  display: inline-block;
  width: 60px;
  height: 34px;
}

.switch input {display:none;}

.slider {
  position: absolute;
  cursor: pointer;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background-color: #ccc;
  -webkit-transition: .4s;
  transition: .4s;
}

.slider:before {
  position: absolute;
  content: "";
  height: 26px;
  width: 26px;
  left: 4px;
  bottom: 4px;
  background-color: white;
  -webkit-transition: .4s;
  transition: .4s;
}

input:checked + .slider {
  background-color: #2196F3;
}

input:focus + .slider {
  box-shadow: 0 0 1px #2196F3;
}

input:checked + .slider:before {
  -webkit-transform: translateX(26px);
  -ms-transform: translateX(26px);
  transform: translateX(26px);
}

/* Rounded sliders */
.slider.round {
  border-radius: 34px;
}

.slider.round:before {
  border-radius: 50%;
}

.text-center{
    margin-right: 100px;
}
.dashboard-center{
    margin-left: 20px;
}
</style>
<div class="col-sm-6">
    <h1>{{ __('message.all_currency') }}</h1>
</div>
<div class="col-sm-6">
    <ol class="breadcrumb float-sm-right">
        <li class="breadcrumb-item"><a href="{{url('/')}}"><i class="fa fa-dashboard"></i> {{ __('message.home') }}</a></li>
        <li class="breadcrumb-item"><a href="{{url('settings')}}"><i class="fa fa-dashboard"></i> {{ __('message.settings') }}</a></li>
        <li class="breadcrumb-item active">{{ __('message.currency') }}</li>
    </ol>
</div><!-- /.col -->
@stop
@section('content')
    <div id="response"></div>

<div class="card card-secondary card-outline">


    <div class="card-body" style="overflow-x: auto;">
        <table id="currency-table" class="table display dt-responsive nowrap" cellspacing="0" width="100%">
            <thead>
            <tr>
                <th>{{ __('message.currency_name') }}</th>
                <th>{{ __('message.currency_code') }}</th>
                <th>{{ __('message.currency_symbol') }}</th>
                <th>{{ __('message.dashboard_currency') }}</th>
                <th>{{ __('message.status') }}</th>
            </tr>
            </thead>
        </table>
    </div>

</div>




    <link rel="stylesheet" href="https://cdn.datatables.net/1.10.22/css/jquery.dataTables.min.css">
    <script src="//cdn.datatables.net/1.10.21/js/jquery.dataTables.min.js"></script>
<script type="text/javascript">
        $('#currency-table').DataTable({
            processing: true,
            serverSide: true,
            responsive: true,
            scrollX: true,
            ajax: {
              url:  '{!! route('get-currency.datatable') !!}',
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
                search:         "{{ __('message.datatable_search') }} ",
                infoEmpty:      "{{ __('message.info_empty') }}",
                infoFiltered:   "{{ __('message.info_filtered') }}",
                lengthMenu:     "{{ __('message.sLengthMenu') }}",
                loadingRecords: "{{ __('message.loading_records') }}",
            },
            order: [[4, 'desc']],
            columnDefs: [
                {
                    targets: [2, 3],
                    orderable: false,
                }
            ],

            
         
            columns: [
                {data: 'name', name: 'name'},
                {data: 'code', name: 'code'},
                {data: 'symbol', name: 'symbol'},
                {data: 'dashboard', name: 'dashboard'},
                {data: 'status', name: 'status'},
                
            ],
            "fnDrawCallback": function( oSettings ) {
                $('.loader').css('display', 'none');
                bindChangeStatusEvent();
            },
            "fnPreDrawCallback": function(oSettings, json) {
                $('.loader').css('display', 'block');
            },
        });

             function bindChangeStatusEvent() {
                 $('.toggle_event_editing').on('change', function () {
                     let $el = $(this);
                     let currentId = $el.find('.module_id').val();
                     let currentStatus = $el.find('.modules_settings_value').val();

                     $.ajax({
                         url: "{{ route('change.currency.status') }}",
                         type: 'POST',
                         data: {
                             _token: "{{ csrf_token() }}",
                             current_id: currentId,
                             current_status: currentStatus
                         },
                         beforeSend: function () {
                             $el.prop('disabled', true);
                         },
                         success: function (response) {
                             $el.find('.modules_settings_value').val(currentStatus == 1 ? 0 : 1);

                             helper.showAlert({
                                 message: response.message || response,
                                 type: 'success',
                                 autoDismiss: 5000,
                                 containerSelector: '#response',
                                 refresh: true
                             });
                         },
                         error: function (xhr) {
                             helper.showAlert({
                                 message: xhr.responseJSON?.message || 'Unknown error',
                                 type: 'error',
                                 autoDismiss: 5000,
                                 containerSelector: '#response',
                                 refresh: true
                             });
                         },
                         complete: function () {
                             $el.prop('disabled', false);
                         }
                     });
                 });
             }
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

@stop