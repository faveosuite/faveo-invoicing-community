@extends('themes.default1.layouts.master')

@section('title')
    {{ __('message.pipedrive') }}
@stop

@section('content-header')
    <div class="col-sm-6">
        <h1>{{ __('message.pipedrive_settings') }}</h1>
    </div>
    <div class="col-sm-6">
        <ol class="breadcrumb float-sm-right">
            <li class="breadcrumb-item"><a href="{{ url('/') }}">{{ __('message.home') }}</a></li>
            <li class="breadcrumb-item"><a href="{{ url('settings') }}">{{ __('message.settings') }}</a></li>
            <li class="breadcrumb-item active">{{ __('message.pipedrive_settings') }}</li>
        </ol>
    </div>
@stop

@section('content')
    @php
        $isPerson = request()->is('pipedrive/mapping/'.$groups['personId']);
        $isOrganization = request()->is('pipedrive/mapping/'.$groups['organizationId']);
        $isDeal = request()->is('pipedrive/mapping/'.$groups['dealId']);
        $localFields = $pipedriveData['local_fields'] ?? [];
        $pipedriveFields = $pipedriveData['pipedrive_fields'] ?? [];
        $mappedFields = collect($pipedriveFields)->filter(function($field) {
            return !empty($field['selected_field']);
        })->values()->toArray();
        $addCount = count($pipedriveFields) - count($mappedFields);
        if(count($mappedFields) === 0){
            $addCount--;
        }

    @endphp

    <style>
        .delete-row, .reset-row {
            margin-top: 32px;
        }

        .invalid-feedback {
            display: block;
        }

        a.disabled {
            pointer-events: none;
            opacity: 0.5;
            cursor: not-allowed;
        }
    </style>

    <div class="alert alert-info" role="alert">
        <i class="fas fa-info-circle mr-2"></i>
        {{ __('message.pipedrive_config_info') }}
        <a class="sync-button" role="button" data-toggle="tooltip" title="{{ __('message.sync_pipedrive_fields') }}">
            <strong>{{ __('message.click_pipedrive_sync') }}</strong> <i class="fas fa-sync ml-1" id="sync-icon"></i>
        </a>
    </div>

    <div id="alert-container-pipe">
        @if (session('success'))
            <div class="alert alert-success alert-dismissible">
                <i class="fa fa-check-circle mr-2"></i> {{ session('success') }}
                <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
            </div>
        @endif
        @if (session('error'))
            <div class="alert alert-danger alert-dismissible">
                <i class="fa fa-ban mr-2"></i> {{ session('error') }}
                <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
            </div>
        @endif
    </div>

    <div class="row">
        <div class="col-12">
            <div class="card card-secondary card-outline card-tabs">
                <div class="card-header p-0 pt-1 border-bottom-0">
                    <ul class="nav nav-tabs" id="custom-tabs-three-tab" role="tablist">
                        <li class="nav-item">
                            <a class="nav-link {{ $isPerson ? 'active' : '' }}"
                               href="{{ url('pipedrive/mapping/'.$groups['personId']) }}">{{ __('message.pipe_person') }}</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link {{ $isOrganization ? 'active' : '' }}"
                               href="{{ url('pipedrive/mapping/'.$groups['organizationId']) }}">{{ __('message.organization') }}</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link {{ $isDeal ? 'active' : '' }}"
                               href="{{ url('pipedrive/mapping/'.$groups['dealId']) }}">{{ __('message.pipe_deal') }}</a>
                        </li>
                    </ul>
                </div>

                <form id="pipedrive-form" method="POST" action="{{ url('sync/pipedrive') }}">
                    @csrf
                    <div class="card-body">
                        <div class="card card-secondary card-outline">
                            <div class="card-header">
                                <h5>{{ __('message.mapping_fields') }}
                                    @if($isDeal)
                                        <i class="fas fa-info-circle fa-sm" data-toggle="tooltip" data-placement="top"
                                           title="{{ __('message.deal_title_required') }}"></i>
                                    @endif
                                </h5>
                            </div>

                            <div class="card-body">
                                <input value="{{ $group_id }}" name="group_id" hidden>
                                <div id="select-container">
                                    @if(count($mappedFields) > 0)
                                        @foreach($mappedFields as $index => $field)
                                            <div class="row mapping-row" data-index="{{ $index }}">
                                                <div class="col-5">
                                                    <div class="form-group">
                                                        <label>{{ __('message.pipedrive_fields') }}</label>
                                                        <select class="form-control pipedrive-select select_fields"
                                                                name="select1[]">
                                                            <option value="">-- {{ __('message.select') }} --</option>
                                                            @foreach($pipedriveFields as $pipedriveField)
                                                                <option value="{{ $pipedriveField['id'] }}"
                                                                        {{ $pipedriveField['id'] == $field['id'] ? 'selected' : '' }}>
                                                                    {{ $pipedriveField['field_name'] }}
                                                                </option>
                                                            @endforeach
                                                        </select>
                                                    </div>
                                                </div>
                                                <div class="col-5">
                                                    <div class="form-group">
                                                        <label>{{ __('message.faveo_invoicing_fields') }}</label>
                                                            <?php
                                                            $isLocalField = empty($field['pipedrive_options']);
                                                            ?>
                                                        <select class="form-control select_fields" name="select2[]" faveo-field="{{ !$isLocalField ? "false" : "true" }}">
                                                            <option value="">-- {{ __('message.select') }} --</option>
                                                            @if(!$isLocalField)
                                                                @foreach($field['pipedrive_options'] as $pipedrive)
                                                                    <option value="{{ $pipedrive['id'] }}"
                                                                            {{ $pipedrive['id'] == $field['selected_field']['id'] ? 'selected' : '' }}>
                                                                        {{ $pipedrive['value'] }}
                                                                    </option>
                                                                @endforeach
                                                            @else
                                                                @foreach($field['local_field_options'] as $pipedrive)
                                                                    <option value="{{ $pipedrive['id'] }}"
                                                                            {{ $pipedrive['id'] == ($field['selected_field']['id'] ?? null) ? 'selected' : '' }}>
                                                                        {{ $pipedrive['field_name'] }}
                                                                    </option>
                                                                @endforeach
                                                            @endif
                                                        </select>
                                                    </div>
                                                </div>
                                                <div class="col-2 delete-row-col">
                                                    @if($index > 0)
                                                        <button type="button" class="btn btn-default delete-row"
                                                                data-toggle="tooltip" title="{{ __('message.delete_attribute') }}">
                                                            <i class="fas fa-trash"></i>
                                                        </button>
                                                    @endif
                                                </div>
                                            </div>
                                        @endforeach
                                    @else
                                        <div class="row mapping-row" data-index="0">
                                            <div class="col-5">
                                                <div class="form-group">
                                                    <label>{{ __('message.pipedrive_fields') }}</label>
                                                    <select class="form-control pipedrive-select select_fields"
                                                            name="select1[]">
                                                        <option value="">-- {{ __('message.select') }} --</option>
                                                        @foreach($pipedriveFields as $pipedriveField)
                                                            <option value="{{ $pipedriveField['id'] }}">
                                                                {{ $pipedriveField['field_name'] }}
                                                            </option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                            </div>
                                            <div class="col-5">
                                                <div class="form-group">
                                                    <label>{{ __('message.faveo_invoicing_fields') }}</label>
                                                    <select class="form-control select_fields" name="select2[]" faveo-field="true">
                                                        <option value="">-- {{ __('message.select') }} --</option>
                                                        @foreach($localFields as $localField)
                                                            <option value="{{ $localField['id'] }}">
                                                                {{ $localField['field_name'] }}
                                                            </option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                            </div>
                                            <div class="col-2 delete-row-col">
                                                <!-- No delete button for first row -->
                                            </div>
                                        </div>
                                    @endif
                                </div>
                                <div>
                                    <a class="text-primary" role="button" id="add-new">
                                        <i class="fas fa-plus-circle"></i> {{ __('message.addnew') }}
                                    </a>
                                </div>
                            </div>
                            <div class="card-footer">
                                <button type="submit" class="btn btn-primary pull-right" id="saveBtn">
                                    <i class="fa fa-save btn-icon"></i>&nbsp;&nbsp;<span class="btn-text">{{ __('message.save') }}</span>
                                </button>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <script>
        $(function () {
            let addCount = {{ $addCount }};
            // Initialize Select2
            $('.select_fields').select2({
                allowClear: true,
                placeholder: "{{ __('message.pipe_select_option') }}"
            });

            // Re-initialize Select2 on window resize
            const formElement = document.getElementById('pipedrive-form');
            new ResizeObserver(() => {
                $('.select_fields').select2({
                    allowClear: true,
                    placeholder: "{{ __('message.pipe_select_option') }}"
                });
            }).observe(formElement);

            let alertTimeout;

            // Store template elements for adding new rows
            const allPipedriveOptions = [];
            @foreach($pipedriveFields as $field)
            allPipedriveOptions.push({
                id: '{{ $field['id'] }}',
                text: '{{ $field['field_name'] }}'
            });
            @endforeach

            // Helper function for showing alerts
            function showAlert(type, message) {
                const isSuccess = type === 'success';
                const iconClass = isSuccess ? 'fa-check-circle' : 'fa-ban';
                const alertClass = isSuccess ? 'alert-success' : 'alert-danger';

                const html = `<div class="alert ${alertClass} alert-dismissible">
            <i class="fa ${iconClass} mr-2"></i> ${message}
            <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
        </div>`;

                $('#alert-container-pipe').html(html).show();
                clearTimeout(alertTimeout);
                window.scrollTo(0, 0);
                alertTimeout = setTimeout(() => $('#alert-container-pipe').slideUp('slow'), 5000);
            }

            // Add form validation function
            function validateForm() {
                let isValid = true;

                $('#select-container').find('.mapping-row').each(function () {
                    const $row = $(this);
                    const $select1 = $row.find('select[name="select1[]"]');
                    const $select2 = $row.find('select[name="select2[]"]');

                    const val1 = $select1.val();
                    const val2 = $select2.val();

                    // Reset feedback
                    $row.find('.invalid-feedback').remove();

                    // Remove is-invalid from Select2 container
                    $select1.next('.select2').find('.select2-selection').removeClass('is-invalid');
                    $select2.next('.select2').find('.select2-selection').removeClass('is-invalid');

                    // Validation for select1
                    if (!val1) {
                        isValid = false;
                        $select1.addClass('is-invalid');
                        $select1.parent().append('<div class="invalid-feedback">{{ __('message.pipe_select_field') }}</div>');
                    }

                    // Validation for select2
                    if (!val2) {
                        isValid = false;
                        $select2.addClass('is-invalid');
                        $select2.parent().append('<div class="invalid-feedback">{{ __('message.pipe_select_field') }}</div>');
                    }
                });

                return isValid;
            }


            // Handle change of Pipedrive dropdown
            $('#select-container').on('change', 'select[name="select1[]"]', function () {
                const $select = $(this);
                $select.removeClass('is-invalid'); // Clear any validation errors
                $select.parent().find('.invalid-feedback').remove();

                const $row = $select.closest('.mapping-row');
                const $faveoSelect = $row.find('select[name="select2[]"]');
                const selectedPipedriveFieldId = $select.val();

                // Update options visibility for all dropdowns
                updateSelectOptions();

                // If no value is selected, just reset the second dropdown to default options
                if (!selectedPipedriveFieldId) {
                    resetFaveoDropdown($faveoSelect);
                    return;
                }

                // Make AJAX call to get corresponding fields
                $.ajax({
                    url: "{{ url('pipedrive/get-dropdown') }}",
                    type: 'POST',
                    data: {
                        pipedrive_field_id: selectedPipedriveFieldId,
                        _token: $('meta[name="csrf-token"]').attr('content')
                    },
                    success: function (response) {
                        // Clear existing options and add new ones
                        $faveoSelect.empty().append('<option value="">-- {{ __('message.select') }} --</option>');

                        if (response.data.options && response.data.options.length > 0) {
                            // Add new options from API response
                            response.data.options.forEach(field => {
                                $faveoSelect.append(`<option value="${field.id}">${field.value}</option>`);
                            });

                            // Set the faveo-field attribute based on response
                            const isFaveoField = response.data.is_faveo_options;
                            $faveoSelect.attr('faveo-field', isFaveoField.toString());
                        } else {
                            // If no matching fields, show default options
                            resetFaveoDropdown($faveoSelect);
                        }

                        // Refresh Select2
                        $faveoSelect.trigger('change.select2');
                    },
                    error: function(xhr) {
                        resetFaveoDropdown($faveoSelect);
                    }
                });
            });

            if(addCount <= 0 ){
                document.getElementById('add-new').classList.add('disabled');
            }

            // Handle faveo field change to clear validation errors
            $('#select-container').on('change', 'select[name="select2[]"]', function () {
                $(this).removeClass('is-invalid');
                $(this).parent().find('.invalid-feedback').remove();
            });

            // Function to reset Faveo dropdown to default options
            function resetFaveoDropdown($select) {
                $select.empty().append(`
            <option value="">-- {{ __('message.select') }} --</option>
            @foreach($localFields as $localField)
                <option value="{{ $localField['id'] }}">{{ $localField['field_name'] }}</option>
            @endforeach
                `);

                // Set as a faveo field by default
                $select.attr('faveo-field', 'true');

                // Refresh Select2
                $select.trigger('change.select2');
            }

            // Add new row
            $('#add-new').on('click', function () {
                addCount--;
                if(addCount <= 0 ){
                    document.getElementById('add-new').classList.add('disabled');
                }
                const $selectContainer = $('#select-container');
                const index = $selectContainer.children('.mapping-row').length;

                // Create select options HTML for Pipedrive fields with available options only
                const availableOptions = getAvailablePipedriveOptions();
                let pipedriveOptionsHtml = '<option value="">-- {{ __('message.select') }} --</option>';
                availableOptions.forEach(option => {
                    pipedriveOptionsHtml += `<option value="${option.id}">${option.text}</option>`;
                });

                const localOptionsHtml = `
            <option value="">-- {{ __('message.select') }} --</option>
            @foreach($localFields as $localField)
                <option value="{{ $localField['id'] }}">{{ $localField['field_name'] }}</option>
            @endforeach
                `;

                const newRow = `
            <div class="row mapping-row" data-index="${index}">
                <div class="col-5">
                    <div class="form-group">
                        <label>{{ __('message.pipedrive_fields') }}</label>
                        <select class="form-control pipedrive-select select_fields" name="select1[]">
                            ${pipedriveOptionsHtml}
                        </select>
                    </div>
                </div>
                <div class="col-5">
                    <div class="form-group">
                        <label>{{ __('message.faveo_invoicing_fields') }}</label>
                        <select class="form-control select_fields" name="select2[]" faveo-field="true">
                            ${localOptionsHtml}
                        </select>
                    </div>
                </div>
                <div class="col-2 delete-row-col">
                    <button type="button" class="btn btn-default delete-row" data-toggle="tooltip" title="{{ __('message.delete_attribute') }}">
                        <i class="fas fa-trash"></i>
                    </button>
                </div>
            </div>`;

                $selectContainer.append(newRow);
                $('.select_fields').select2({
                    allowClear: true,
                    placeholder: "{{ __('message.pipe_select_option') }}"
                });
                $('[data-toggle="tooltip"]').tooltip();
            });

            // Delete row
            $('#select-container').on('click', '.delete-row', function () {
                addCount++;
                if(addCount >= 1 ){
                    document.getElementById('add-new').classList.remove('disabled');
                }
                $(this).tooltip('dispose');
                $(this).closest('.mapping-row').remove();
                updateSelectOptions();
            });

            // Get selected Pipedrive values
            function getSelectedPipedriveValues() {
                const selectedValues = [];
                $('#select-container').find('select[name="select1[]"]').each(function () {
                    const val = $(this).val();
                    if (val) selectedValues.push(val);
                });
                return selectedValues;
            }

            // Get available Pipedrive options (not selected in other dropdowns)
            function getAvailablePipedriveOptions() {
                const selectedValues = getSelectedPipedriveValues();
                return allPipedriveOptions.filter(option => !selectedValues.includes(option.id));
            }

            // Update Pipedrive dropdown options to remove selected options from other dropdowns
            function updateSelectOptions() {
                const selects = $('#select-container').find('select[name="select1[]"]');

                // First collect all the current selections to preserve them
                const currentSelections = [];
                selects.each(function () {
                    currentSelections.push($(this).val());
                });

                // Process each select dropdown
                selects.each(function (index) {
                    const $select = $(this);
                    const currentValue = currentSelections[index];

                    // Clear all options except the first one
                    $select.find('option:not(:first)').remove();

                    // Add all Pipedrive options
                    allPipedriveOptions.forEach(option => {
                        // Add this option if it's the current selection or not selected elsewhere
                        if (option.id === currentValue || !currentSelections.includes(option.id)) {
                            $select.append(`<option value="${option.id}">${option.text}</option>`);
                        }
                    });

                    // Restore current selection
                    $select.val(currentValue);

                    // Refresh Select2
                    $select.trigger('change.select2');
                });
            }

            // Custom form submission with proper handling of faveo_field attribute
            $('#pipedrive-form').on('submit', function (event) {
                event.preventDefault();

                if (!validateForm()) {
                    return false;
                }

                // Create a data object to hold our form data
                const formData = {
                    group_id: $('input[name="group_id"]').val(),
                    select1: [],
                    select2: []
                };

                // Process each mapping row
                $('#select-container').find('.mapping-row').each(function(index) {
                    const $row = $(this);
                    const $select1 = $row.find('select[name="select1[]"]');
                    const $select2 = $row.find('select[name="select2[]"]');

                    const select1Value = $select1.val();
                    const select2Value = $select2.val();


                    // Skip empty rows
                    if (!select1Value || !select2Value) {
                        return;
                    }

                    // Add select1 with id attribute
                    formData.select1.push({
                        id: select1Value
                    });

                    // Add select2 with id and faveo_field attribute
                    formData.select2.push({
                        id: select2Value,
                        faveo_fields: $select2.attr('faveo-field') === 'true'
                    });
                });

                var $btn = $('#saveBtn');
                var $icon = $btn.find('.btn-icon');
                var $text = $btn.find('.btn-text');

                // Send the data via AJAX
                $.ajax({
                    url: $(this).attr('action'),
                    type: 'POST',
                    data: formData,
                    beforeSend: function () {
                        $btn.prop('disabled', true);                         // Disable the button
                        $icon.removeClass('fa-save').addClass('fa-spinner fa-spin');  // Show spinner icon
                        $text.text('{{ __('message.saving') }}');                             // Update text
                    },
                    success: function (response) {
                        showAlert('success', response.message || 'Mapping saved successfully.');
                    },
                    error: function (xhr) {
                        const response = xhr.responseJSON || {};
                        showAlert('error', response.message || 'An error occurred while saving the mapping.');
                        console.error('Form submission error:', xhr);
                    },
                    complete: function () {
                        $btn.prop('disabled', false);                        // Re-enable button
                        $icon.removeClass('fa-spinner fa-spin').addClass('fa-save'); // Restore save icon
                        $text.text('{{ __('message.save') }}');                                  // Restore text
                    }
                });
            });

            // Sync button functionality
            $('.sync-button').on('click', function () {
                const icon = $('#sync-icon');

                $.ajax({
                    type: "GET",
                    url: "{{ url('syncing/pipedriveFields') }}",
                    beforeSend: function () {
                        icon.addClass('fa-spin');
                    },
                    success: function (response) {
                        showAlert('success', response.message || 'Fields synchronized successfully.');
                        // Reload page to reflect updated fields
                        setTimeout(() => location.reload(), 1500);
                    },
                    error: function (xhr) {
                        const response = xhr.responseJSON || {};
                        showAlert('error', response.message || 'An error occurred during synchronization.');
                        console.error('Sync error:', xhr);
                    },
                    complete: function () {
                        icon.removeClass('fa-spin');
                    }
                });
            });

            // Initialize tooltips
            $('[data-toggle="tooltip"]').tooltip();

            // Initialize options visibility on page load
            updateSelectOptions();
        });
    </script>
@stop