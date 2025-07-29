<div>
    <table id="{{ $id ?? 'dynamic-table' }}" class="table table-hover w-100">
        <thead>
        <tr>
            @foreach ($columns as $column)
                <th>{{ $column['title'] ?? ucfirst($column['data']) }}</th>
            @endforeach
        </tr>
        </thead>
    </table>
</div>
<link rel="stylesheet" type="text/css" href="//cdn.datatables.net/1.10.21/css/jquery.dataTables.min.css" />
<script src="//cdn.datatables.net/1.10.21/js/jquery.dataTables.min.js"></script>
<script>
    $(document).ready(function () {
        $('#{{ $id ?? 'dynamic-table' }}').DataTable({
            processing: true,
            serverSide: true,
            responsive: true,
            scrollX: true,
            ajax: {
                url: '{{ $url }}',
                method: 'POST',
                error: function(xhr) {
                    if (xhr.status === 401) {
                        alert('{{ __("message.session_expired") }}');
                        window.location.href = '/login';
                    }
                },
            },
            columns: @json($columns),
            order: @json($order ?? [[0, 'asc']]),
            searching: {{ $searching ?? 'true' }},
            ordering: {{ $ordering ?? 'true' }},
            select: {{ $select ?? 'false' }},
            oLanguage: {
                sLengthMenu: "{{ __('message.length_menu') }}",
                sSearch: "{{ __('Search') }}",
                sProcessing: '<div class="overlay"><i class="fas fa-sync-alt fa-spin"></i><div class="text-bold pt-2">{{ __("message.loading") }}</div></div>'
            },
            language: {
                paginate: {
                    first: "{{ __('message.paginate_first') }}",
                    last: "{{ __('message.paginate_last') }}",
                    next: "{{ __('message.paginate_next') }}",
                    previous: "{{ __('message.paginate_previous') }}"
                },
                emptyTable: "{{ __('message.empty_table') }}",
                info: "{{ __('message.datatable_info') }}",
                zeroRecords: "{{ __('message.no_matching_records_found') }}",
                infoEmpty: "{{ __('message.info_empty') }}",
                infoFiltered: "{{ __('message.info_filtered') }}"
            },
            fnDrawCallback: function() {
                $('[data-toggle="tooltip"]').tooltip();
                $('.loader').hide();
            },
            fnPreDrawCallback: function() {
                $('.loader').show();
            }
        });
    });
</script>
