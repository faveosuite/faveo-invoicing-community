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
       $isDeal = request()->is('pipedrive/mapping/'.$groups['dealId'])
    @endphp
    <style>
        .delete-row, .reset-row {
            margin-top: 32px;
        }
    </style>
    <div class="alert alert-info" role="alert">
        <i class="fas fa-info-circle mr-2"></i>
        {{ __('message.pipedrive_config_info') }}
        <a class="sync-button" role="button" data-toggle="tooltip" title="{{ __('message.sync_pipedrive_fields') }}">
            <strong>{{ __('message.click_pipedrive_sync') }}</strong> <i class="fas fa-sync ml-1" id="sync-icon"></i>
        </a>
    </div>
    <div id="alert-container-pipe"></div>

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
                <form id="pipedrive-form">
                    <div class="card-body">
                        <div class="card card-secondary card-outline">
                            <div class="card-header">
                                <h5>Mapping Fields
                                    @if($isDeal)
                                        <i class="fas fa-info-circle fa-sm" data-toggle="tooltip" data-placement="top" title="{{ __('message.deal_title_required') }}"></i>
                                    @endif
                                </h5>
                            </div>

                            <div class="card-body">
                                <form id="pipedrive-form">
                                    <input value="{{ $group_id }}" name="group_id" hidden>
                                    <div id="select-container"></div>
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
            let alertTimeout;
            let localOptions = [];

            const $selectContainer = $('#select-container');
            const form = $('#pipedrive-form');

            function showAlert(type, response) {
                const isSuccess = type === 'success';
                const iconClass = isSuccess ? 'fa-check-circle' : 'fa-ban';
                const alertClass = isSuccess ? 'alert-success' : 'alert-danger';
                const message = response.message || response || 'An error occurred.';

                const html = `<div class="alert ${alertClass} alert-dismissible">
            <i class="fa ${iconClass} mr-2"></i> ${message}
            <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
        </div>`;

                $('#alert-container-pipe').html(html).show();
                clearTimeout(alertTimeout);
                window.scrollTo(0, 0);
                alertTimeout = setTimeout(() => $('#alert-container-pipe').slideUp('slow'), 5000);
            }

            function returnRow(pipedriveHtml, localHtml, selected1 = '', selected2 = '', index = 0) {
                const select2 = localHtml.replace(`value="${selected2}"`, `value="${selected2}" selected`);
                const actionBtn = index === 0
                    ? ``
                    : `<button type="button" class="btn btn-default delete-row" data-toggle="tooltip" title="Delete Attribute"><i class="fas fa-trash"></i></button>`;

                return `
<div class="row mapping-row" data-index="${index}">
    <div class="col-5">
        <div class="form-group">
            <label>Pipedrive Fields</label>
            <select class="form-control pipedrive-select select_fields" name="select1[]" data-selected="${selected1}">
                <option value="">-- Select --</option>
            </select>
        </div>
    </div>
    <div class="col-5">
        <div class="form-group">
            <label>Faveo Invoicing Fields</label>
            <select class="form-control select_fields" name="select2[]">
                <option value="">-- Select --</option>
                ${select2}
            </select>
        </div>
    </div>
    <div class="col-2 delete-row-col">
        ${actionBtn}
    </div>
</div>`;
            }

            function updatePipedriveDropdowns() {
                $('[data-toggle="tooltip"]').tooltip();
                const selectedValues = [];

                $selectContainer.find('select[name="select1[]"]').each(function () {
                    const val = $(this).val();
                    if (val) selectedValues.push(val);
                });

                $selectContainer.find('select[name="select1[]"]').each(function () {
                    const $this = $(this);
                    const currentVal = $this.val();
                    const dataSelected = $this.data('selected');

                    $this.find('option:not(:first)').remove();

                    if (window.pipedriveFields) {
                        $(window.pipedriveFields).each(function () {
                            const optionValue = this.id;
                            const optionText = this.field_name;

                            if (optionValue == currentVal || !selectedValues.includes(optionValue.toString())) {
                                $this.append(`<option value="${optionValue}">${optionText}</option>`);
                            }
                        });
                    }

                    if (currentVal) {
                        $this.val(currentVal);
                    } else if (dataSelected) {
                        $this.val(dataSelected);
                        if ($this.val()) {
                            $this.data('selected', '');
                        }
                    }
                });

                // Attach real-time validation to all select fields
                $selectContainer.find('select[name="select1[]"], select[name="select2[]"]').off('change').on('change', function () {
                    validateField(this);
                });
            }

            function validateField(field) {
                const $field = $(field);
                const $formGroup = $field.closest('.form-group');
                const value = $field.val();
                let isValid = true;
                let errorMessage = '';

                if (!value || value === '' || value === '0') {
                    isValid = false;
                    errorMessage = $field.data('error-message') || 'Please select an option.';
                }

                if (isValid) {
                    $field.removeClass('is-invalid');
                    $formGroup.find('.invalid-feedback').remove();
                } else {
                    $field.removeClass('is-valid').addClass('is-invalid');
                    if (!$formGroup.find('.invalid-feedback').length) {
                        $formGroup.append('<div class="invalid-feedback">' + errorMessage + '</div>');
                    } else {
                        $formGroup.find('.invalid-feedback').text(errorMessage);
                    }
                }

                return isValid;
            }

            function validateForm() {
                let isValid = true;

                form.find('select[name="select1[]"], select[name="select2[]"]').each(function () {
                    if (!validateField(this)) {
                        isValid = false;
                    }
                });

                return isValid;
            }

            $('#add-new').on('click', function () {
                const index = $selectContainer.children('.mapping-row').length;
                $selectContainer.append(returnRow(null, localOptions, '', '', index));
                updatePipedriveDropdowns();
            });

            $selectContainer.on('click', '.delete-row', function () {
                $(this).tooltip('dispose');
                $(this).closest('.mapping-row').remove();
                updatePipedriveDropdowns();
            });

            $selectContainer.on('click', '.reset-row', function () {
                const row = $(this).closest('.mapping-row');
                row.find('select[name="select1[]"]').val('').data('selected', '');
                row.find('select[name="select2[]"]').val('');
                row.find('select').removeClass('is-invalid');
                row.find('.invalid-feedback').remove();
                updatePipedriveDropdowns();
            });

            $selectContainer.on('change', 'select[name="select1[]"]', function () {
                const selectedValue = $(this).val();
                $(this).data('selected', '');
                updatePipedriveDropdowns();
                $(this).val(selectedValue);
            });

            form.on('submit', function (event) {
                event.preventDefault();

                if (validateForm()) {
                    $.ajax({
                        url: @json(url('sync/pipedrive')),
                        type: 'POST',
                        data: form.serialize(),
                        headers: {
                            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                        },
                        success: function (response) {
                            showAlert('success', response.message);
                            form.find('.is-valid, .is-invalid').removeClass('is-valid is-invalid');
                            form.find('.invalid-feedback').remove();
                        },
                        error: function (data) {
                            const response = data.responseJSON ?? JSON.parse(data.responseText);
                            showAlert('error', response);
                        }
                    });
                } else {
                    console.log("Form has validation errors.");
                }
            });

            // Initial field loading
            $.ajax({
                url: @json(url("getPipedriveFields/" . $group_id)),
                type: 'GET',
                success: function (response) {
                    if (response.success && response.data?.local_fields) {
                        const pipedriveFields = response.data.pipedrive_fields || [];
                        const localFields = response.data.local_fields || [];

                        window.pipedriveFields = pipedriveFields;

                        localOptions = localFields.map(f => `<option value="${f.id}">${f.field_name}</option>`).join('');

                        const mappedFields = pipedriveFields.filter(f => f.local_field_id !== null);

                        if (mappedFields.length) {
                            mappedFields.forEach((field, index) => {
                                $selectContainer.append(returnRow(null, localOptions, field.id, field.local_field_id, index));
                            });
                        } else {
                            $('#add-new').trigger('click');
                        }

                        updatePipedriveDropdowns();
                    }
                }
            });
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
                    },
                    error: function (xhr) {
                        showAlert('error', xhr.responseJSON?.message || 'An error occurred.');
                    },
                    complete: function () {
                        icon.removeClass('fa-spin');
                    }
                });
            });
        });
    </script>
@stop