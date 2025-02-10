@extends('themes.default1.layouts.master')
@section('title')
Currency
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
    <div class="alert alert-success alert-dismissable" style="display: none;">
        <i class="fa  fa-check-circle"></i>
        <span class="success-msg"></span>
        <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>

    </div>
    <div id="response"></div>

<div class="card card-secondary card-outline">





    <div class="card-body table-responsive">
        <div class="row">

            <div class="col-md-12">
                <table id="currency-table" styleClass="borderless">
                    

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

    </div>

</div>




<link rel="stylesheet" type="text/css" href="//cdn.datatables.net/1.10.12/css/jquery.dataTables.min.css" />
<script src="//cdn.datatables.net/1.10.12/js/jquery.dataTables.min.js"></script>
<script type="text/javascript">
        $('#currency-table').DataTable({
            processing: true,
            serverSide: true,
            bDestroy: true,
            ajax: {
              "url":  '{!! route('get-currency.datatable') !!}',
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
        $('.toggle_event_editing').change(function(){
            var current_id = $(this).children('.module_id');
            var current_status = $(this).children('.modules_settings_value');

            $.ajax({
                type: 'POST',
                url: '{{route("change.currency.status")}}',
                data: {
                    "_token": "{{ csrf_token() }}",
                    current_id: current_id.val(),
                    current_status: current_status.val()
                }
            }).done(function(result) {
                current_status.val( current_status.val() == 1 ? 0 : 1);
                $(window).scrollTop(0);
                $('.success-msg').html(result);
                $('.alert-success').css('display', 'block');
                setInterval(function() {
                    $('.alert-success').slideUp(3000);
                }, 500);
                location.reload();
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