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
            return $field['local_field_id'] !== null;
        })->values()->all();

    @endphp

    <style>
        .delete-row, .reset-row {
            margin-top: 32px;
        }

        .invalid-feedback {
            display: block;
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
                               href="{{ url('pipedrive/mapping/'.$groups['personId']) }}">Person</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link {{ $isOrganization ? 'active' : '' }}"
                               href="{{ url('pipedrive/mapping/'.$groups['organizationId']) }}">Organization</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link {{ $isDeal ? 'active' : '' }}"
                               href="{{ url('pipedrive/mapping/'.$groups['dealId']) }}">Deal</a>
                        </li>
                    </ul>
                </div>

                <form id="pipedrive-form" method="POST" action="{{ url('sync/pipedrive') }}">
                    @csrf
                    <div class="card-body">
                        <div class="card card-secondary card-outline">
                            <div class="card-header">
                                <h5>Mapping Fields
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
                                                        <label>Pipedrive Fields</label>
                                                        <select class="form-control pipedrive-select select_fields"
                                                                name="select1[]">
                                                            <option value="">-- Select --</option>
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
                                                        <label>Faveo Invoicing Fields</label>
                                                        <select class="form-control select_fields" name="select2[]">
                                                            <option value="">-- Select --</option>
                                                            @foreach($localFields as $localField)
                                                                <option value="{{ $localField['id'] }}"
                                                                        {{ $localField['id'] == $field['local_field_id'] ? 'selected' : '' }}>
                                                                    {{ $localField['field_name'] }}
                                                                </option>
                                                            @endforeach
                                                        </select>
                                                    </div>
                                                </div>
                                                <div class="col-2 delete-row-col">
                                                    @if($index > 0)
                                                        <button type="button" class="btn btn-default delete-row"
                                                                data-toggle="tooltip" title="Delete Attribute">
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
                                                    <label>Pipedrive Fields</label>
                                                    <select class="form-control pipedrive-select select_fields"
                                                            name="select1[]">
                                                        <option value="">-- Select --</option>
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
                                                    <label>Faveo Invoicing Fields</label>
                                                    <select class="form-control select_fields" name="select2[]">
                                                        <option value="">-- Select --</option>
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
                                        <i class="fas fa-plus-circle"></i> Add New
                                    </a>
                                </div>
                            </div>
                            <div class="card-footer">
                                <button type="submit" class="btn btn-primary pull-right">
                                    <i class="fa fa-save">&nbsp;&nbsp;</i>Save
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
            // Initialize Select2
            $('.select_fields').select2();

            let alertTimeout;

            // Store template elements for adding new rows with ALL options
            const allPipedriveOptions = [];
            @foreach($pipedriveFields as $field)
            allPipedriveOptions.push({
                id: '{{ $field['id'] }}',
                text: '{{ $field['field_name'] }}'
            });
            @endforeach

            const localOptionsHtml = `
        <option value="">-- Select --</option>
        @foreach($localFields as $localField)
            <option value="{{ $localField['id'] }}">{{ $localField['field_name'] }}</option>
        @endforeach
            `;

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

            // Add new row
            $('#add-new').on('click', function () {
                const $selectContainer = $('#select-container');
                const index = $selectContainer.children('.mapping-row').length;

                // Create select options HTML for Pipedrive fields with available options only
                const availableOptions = getAvailablePipedriveOptions();
                let pipedriveOptionsHtml = '<option value="">-- Select --</option>';
                availableOptions.forEach(option => {
                    pipedriveOptionsHtml += `<option value="${option.id}">${option.text}</option>`;
                });

                const newRow = `
        <div class="row mapping-row" data-index="${index}">
            <div class="col-5">
                <div class="form-group">
                    <label>Pipedrive Fields</label>
                    <select class="form-control pipedrive-select select_fields" name="select1[]">
                        ${pipedriveOptionsHtml}
                    </select>
                </div>
            </div>
            <div class="col-5">
                <div class="form-group">
                    <label>Faveo Invoicing Fields</label>
                    <select class="form-control select_fields" name="select2[]">
                        ${localOptionsHtml}
                    </select>
                </div>
            </div>
            <div class="col-2 delete-row-col">
                <button type="button" class="btn btn-default delete-row" data-toggle="tooltip" title="Delete Attribute">
                    <i class="fas fa-trash"></i>
                </button>
            </div>
        </div>`;

                $selectContainer.append(newRow);
                $('.select_fields').select2();
                $('[data-toggle="tooltip"]').tooltip();
            });

            // Delete row
            $('#select-container').on('click', '.delete-row', function () {
                $(this).tooltip('dispose');
                $(this).closest('.mapping-row').remove();
                updateSelectOptions();
            });

            // Handle field change
            $('#select-container').on('change', 'select[name="select1[]"]', function () {
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


            function validateForm() {
                let isValid = true;

                $('#pipedrive-form').find('select[name="select1[]"], select[name="select2[]"]').each(function () {
                    const $field = $(this);
                    const $formGroup = $field.closest('.form-group');
                    const value = $field.val();
                    const errorMessage = $field.data('error-message') || 'Please select an option.';
                    const hasError = !value || value === '' || value === '0';

                    $formGroup.find('.invalid-feedback').remove();
                    $field.removeClass('is-valid is-invalid');

                    if (hasError) {
                        isValid = false;
                        $field.addClass('is-invalid');
                        $formGroup.append(`<div class="invalid-feedback">${errorMessage}</div>`);
                    }
                });

                return isValid;
            }


            // Form submission with AJAX
            $('#pipedrive-form').on('submit', function (event) {
                event.preventDefault();

                if(!validateForm()){
                    return false;
                }

                $.ajax({
                    url: $(this).attr('action'),
                    type: 'POST',
                    data: $(this).serialize(),
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    success: function (response) {
                        showAlert('success', response.message);
                    },
                    error: function (xhr) {
                        const response = xhr.responseJSON || {};
                        showAlert('error', response.message || 'An error occurred.');
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
                        showAlert('success', response.message);
                        // Reload page to reflect updated fields
                        setTimeout(() => location.reload(), 1500);
                    },
                    error: function (xhr) {
                        showAlert('error', xhr.responseJSON?.message || 'An error occurred.');
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