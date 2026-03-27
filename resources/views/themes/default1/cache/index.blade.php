@extends('themes.default1.layouts.master')
@section('title')
    {{ __('message.cache_drivers') }}
@stop
@section('content-header')
    <div class="col-sm-6">
        <h1>{{ __('message.cache_drivers') }}</h1>
    </div>
    <div class="col-sm-6">
        <ol class="breadcrumb float-sm-right">
            <li class="breadcrumb-item"><a href="{{url('/')}}"><i class="fa fa-dashboard"></i> {{ __('message.home') }}</a></li>
            <li class="breadcrumb-item"><a href="{{url('settings')}}"><i class="fa fa-dashboard"></i> {{ __('message.settings') }}</a></li>
            <li class="breadcrumb-item active">{{ __('message.cache_drivers') }}</li>
        </ol>
    </div><!-- /.col -->
@stop
@section('content')

    <div id="alertMessage"></div>

    <div class="card card-secondary card-outline">
      <div class="card-body table-responsive">

       <div id="response"></div>

             <div class="row">

            <div class="col-md-12">

                 <table id="cache-table" class="table display" cellspacing="0" width="100%" styleClass="borderless">

                    <thead><tr>
                            <th>{{ __('message.name_page') }}</th>
                            <th>{{ __('message.status') }}</th>
                            <th>{{ __('message.action') }}</th>
                        </tr></thead>

                   </table>
            </div>
        </div>

    </div>

</div>

    <link rel="stylesheet" href="https://cdn.datatables.net/1.10.21/css/jquery.dataTables.min.css">

    <script src="https://cdn.datatables.net/1.10.21/js/jquery.dataTables.min.js"></script>
    <script>
     $('ul.nav-sidebar a').filter(function() {
        return this.id == 'setting';
    }).addClass('active');

    // for treeview
    $('ul.nav-treeview a').filter(function() {
        return this.id == 'setting';
    }).parentsUntil(".nav-sidebar > .nav-treeview").addClass('menu-open').prev('a').addClass('active');
</script>
<script type="text/javascript">
        $('#cache-table').DataTable({
            processing: true,
            serverSide: true,
            stateSave: false,
            ordering: false,
            searching:true,
            select: true,
              order: [],
               ajax: {
            "url":  '{!! route('get-cache-drivers') !!}',
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
                zeroRecords:    "{{ __('message.no_matching_records_found') }} ",
                infoEmpty:      "{{ __('message.info_empty') }}",
                infoFiltered:   "{{ __('message.info_filtered') }}",
                lengthMenu:     "{{ __('message.sLengthMenu') }}",
                loadingRecords: "{{ __('message.loading_records') }}",
            },

            columnDefs: [
                {
                    targets: 'no-sort',
                    orderable: true,
                    order: []
                }
            ],
            columns: [
                {data: 'name', name: 'name'},
                {data: 'status', name: 'status'},
                {data: 'action', name: 'action'}
            ],
            "fnDrawCallback": function( oSettings ) {
                $('.loader').css('display', 'none');
                $('[data-toggle="tooltip"]').tooltip();
            },
            "fnPreDrawCallback": function(oSettings, json) {
                $('.loader').css('display', 'block');
            },
        });

    function showAlert(type, message) {
        var icon = type === 'success' ? 'fa-check-circle' : 'fa-ban';
        var alertClass = type === 'success' ? 'alert-success' : 'alert-danger';
        $('#alertMessage').html(
            '<div class="alert ' + alertClass + ' alert-dismissable">' +
            '<i class="fa ' + icon + '"></i> ' + message +
            '<button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>' +
            '</div>'
        );
    }

    function activateCacheDriver(driver)
    {
        var $btn = $(event.target).closest('button');
        var $icon = $btn.find('i');
        var originalText = $btn.text();

        $.ajax({
            method: 'POST',
            url: "{{ url('cache-drivers') }}/" + driver + "/activate",
            data: {
                "_token": "{{ csrf_token() }}"
            },
            beforeSend: function() {
                $('#alertMessage').html('');
                $btn.prop('disabled', true);
                $icon.removeClass('fa-check-circle').addClass('fa-spinner fa-spin');
            },
            success: function (response) {
                showAlert('success', response.message || '{{ __("message.activated_successfully", ["name" => "Driver"]) }}');
                $('#cache-table').DataTable().ajax.reload();
            },
            error: function (xhr) {
                var response = xhr.responseJSON || {};
                showAlert('error', response.message || '{{ __("message.something_went_wrong") }}');
                console.error('Activate error:', xhr);
            },
            complete: function () {
                $btn.prop('disabled', false);
                $icon.removeClass('fa-spinner fa-spin').addClass('fa-check-circle');
            }
        });
    }
    </script>

@stop
