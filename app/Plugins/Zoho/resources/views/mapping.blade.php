@extends('themes.default1.layouts.master')

@section('title', 'Zoho CRM Mapping')

@section('content')

    <div class="card">
        <div class="card-header">
            <h3>Zoho {{ $integration->platform }} – <span id="module-name"></span> Mapping</h3>
        </div>

        <div class="card-body">
            <form id="zoho-mapping-form">
                @csrf

                <div id="select-container"></div>

                <button type="button" id="add-row" class="btn btn-link">
                    <i class="fa fa-plus"></i> Add New
                </button>

                <hr>

                <button type="submit" class="btn btn-primary">
                    Save Mapping
                </button>
            </form>
        </div>
    </div>

    <script>
        $(function () {

            const baseUrl = "{{ url('') }}";
            const module  = "{{ $module }}";
            const platform = "{{ $integration->platform }}";

            let cachedZohoFields = [];

            loadMappingData();

            /* ---------------- LOAD INITIAL DATA ---------------- */

            function loadMappingData() {
                // Fetch fields and mappings in parallel
                // Note: Fields endpoint requires lowercase module name (e.g. contacts/fields)
                const fieldsReq = $.get(`${baseUrl}/zoho/${platform}/${module.toLowerCase()}/fields`);
                const mappingReq = $.get(`${baseUrl}/zoho/${platform}/${module}/mapping/data`);

                $.when(fieldsReq, mappingReq).done(function (fieldsRes, mappingRes) {

                    // API returns { success: true, data: [...] }
                    // $.when returns [data, status, xhr] for each request
                    cachedZohoFields = fieldsRes[0].data;
                    const mappings = mappingRes[0].data;

                    $('#module-name').text(module);

                    renderRows(cachedZohoFields, mappings);
                });
            }

            /* ---------------- RENDER ROWS ---------------- */

            function renderRows(zohoFields, mappings) {

                $('#select-container').empty();

                if (!mappings || !mappings.length) {
                    addRow(zohoFields);
                    return;
                }

                mappings.forEach(mapping => addRow(zohoFields, mapping));
            }

            /* ---------------- ADD SINGLE ROW ---------------- */

            function addRow(zohoFields, mapping = null) {

                let zohoOptions = `<option value="">-- Select --</option>`;

                zohoFields.forEach(z => {
                    // API returns 'field_name', not 'display_name'
                    zohoOptions += `
                <option value="${z.id}" ${mapping && mapping.zoho_field_id == z.id ? 'selected' : ''}>
                    ${z.field_name}
                </option>
            `;
                });

                $('#select-container').append(`
            <div class="row mapping-row mb-2">
                <div class="col-5">
                    <select class="form-control zoho-select">
                        ${zohoOptions}
                    </select>
                </div>

                <div class="col-5">
                    <select class="form-control target-select" data-type="">
                        <option value="">-- Select --</option>
                    </select>
                </div>

                <div class="col-2">
                    <button type="button" class="btn btn-danger delete-row">
                        <i class="fa fa-trash"></i>
                    </button>
                </div>
            </div>
        `);

                if (mapping) {
                    loadOptionsForExisting(mapping);
                }
            }

            /* ---------------- LOAD OPTIONS FOR EXISTING MAPPING ---------------- */

            function loadOptionsForExisting(mapping) {

                const row    = $('#select-container .mapping-row').last();
                const target = row.find('.target-select');

                $.get(`${baseUrl}/zoho/options/${mapping.zoho_field_id}`, function (options) {

                    target.empty().append('<option value="">-- Select --</option>');

                    options.forEach(o => {

                        const isSelected =
                            o.type === mapping.selected.type &&
                            String(o.value) === String(mapping.selected.value);

                        target.append(`
                    <option value="${o.value}" ${isSelected ? 'selected' : ''}>
                        ${o.label}
                    </option>
                `);

                        if (isSelected) {
                            target.data('type', o.type);
                        }
                    });
                });
            }

            /* ---------------- CHANGE ZOHO FIELD ---------------- */

            $('#select-container').on('change', '.zoho-select', function () {

                const zohoId = $(this).val();
                const target = $(this).closest('.mapping-row').find('.target-select');

                if (!zohoId) return;

                $.get(`${baseUrl}/zoho/options/${zohoId}`, function (options) {

                    target.empty().append('<option value="">-- Select --</option>');

                    options.forEach(o => {
                        target.append(`<option value="${o.value}">${o.label}</option>`);
                    });

                    // clear previous type
                    target.data('type', '');
                });
            });

            /* ---------------- CHANGE TARGET FIELD ---------------- */

            $('#select-container').on('change', '.target-select', function () {

                const selectedOption = $(this).find('option:selected');
                const row            = $(this).closest('.mapping-row');
                const zohoId         = row.find('.zoho-select').val();

                if (!zohoId) return;

                $.get(`${baseUrl}/zoho/options/${zohoId}`, options => {
                    const opt = options.find(o => String(o.value) === selectedOption.val());
                    if (opt) {
                        $(this).data('type', opt.type);
                    }
                });
            });

            /* ---------------- ADD / REMOVE ROW ---------------- */

            $('#add-row').click(() => {
                if (cachedZohoFields.length > 0) {
                    addRow(cachedZohoFields);
                }
            });

            $('#select-container').on('click', '.delete-row', function () {
                $(this).closest('.mapping-row').remove();
            });

            /* ---------------- SUBMIT ---------------- */

            $('#zoho-mapping-form').submit(function (e) {

                e.preventDefault();

                const mappings = [];

                $('.mapping-row').each(function () {

                    const zoho   = $(this).find('.zoho-select').val();
                    const target = $(this).find('.target-select').val();
                    const type   = $(this).find('.target-select').data('type');

                    if (!zoho || !target || !type) return;

                    mappings.push({
                        zoho_field_id: zoho,
                        selected: {
                            type: type,
                            value: target
                        }
                    });
                });

                // Correct route is /zoho/mapping/save (not /zoho/crm/mapping/save)
                $.post(`${baseUrl}/zoho/mapping/save`, {
                    _token: $('meta[name="csrf-token"]').attr('content'),
                    module: module,
                    integration_id: {{ $integration->id }},
                    mappings
                }, res => alert(res.message));
            });

        });
    </script>

@endsection
