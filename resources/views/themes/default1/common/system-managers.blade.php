@extends('themes.default1.layouts.master')
@section('title')
    {{ __('message.system_manager') }}
@stop
@section('content-header')
<style>
    .select2-container--default .select2-selection--multiple .select2-selection__choice {
        background-color: #1b1818 !important;
    }

</style>
    <div class="col-sm-6">
        <h1>{{ __('message.system_manager') }}</h1>
    </div>
    <div class="col-sm-6">
        <ol class="breadcrumb float-sm-right">
            <li class="breadcrumb-item"><a href="{{url('/')}}"><i class="fa fa-dashboard"></i> {{ __('message.home') }}</a></li>
            <li class="breadcrumb-item"><a href="{{url('settings')}}"><i class="fa fa-dashboard"></i> {{ __('message.settings') }}</a></li>
            <li class="breadcrumb-item active">{{ __('message.system_manager') }}</li>
        </ol>
    </div>

@stop
@section('content')
    <div class="container-fluid">
        <div id="system-manager-alert"></div>

        <form id="managerSettingsForm">
            <div class="card card-secondary card-outline elevation-2">
                <div class="card-header">
                    <h3 class="card-title">{{ __('message.system_manager_settings') }}</h3>
                </div>

                <div class="card-body">
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <div class="custom-control custom-switch">
                                <input type="checkbox" class="custom-control-input" id="autoAssignAccountSwitch" name="autoAssignAccount" {{ $accountManagersAutoAssign ? 'checked' : '' }}>
                                <label class="custom-control-label" for="autoAssignAccountSwitch">
                                    <strong>{{ __('message.enable_account_manager') }}</strong>
                                    <small class="text-muted d-block">{{ __('message.account_upon_creation') }}</small>
                                </label>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="custom-control custom-switch">
                                <input type="checkbox" class="custom-control-input" id="autoAssignSalesSwitch" name="autoAssignSales" {{ $salesManagerAutoAssign ? 'checked' : '' }}>
                                <label class="custom-control-label" for="autoAssignSalesSwitch">
                                    <strong>{{ __('message.enable_sales_manager') }}</strong>
                                    <small class="text-muted d-block">{{ __('message.sales_upon_creation') }}</small>
                                </label>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <!-- Account Manager Replacement -->
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="accountManagerCurrent">{{ __('message.current_account_manager') }}</label>
                                <select class="form-control select2-manager" name="accountManagerCurrent" id="accountManagerCurrent">
                                    <option value="">{{ __('message.select') }}</option>
                                    @foreach($accountManagers as $key => $manager)
                                        <option value="{{ $key }}">{{ $manager }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="accountManagerNew">{{ __('message.select_replacement_manager') }}</label>
                                <select class="form-control select2-new" name="accountManagerNew" id="accountManagerNew" multiple>
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <!-- Sales Manager Replacement -->
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="salesManagerCurrent">{{ __('message.current_sales_manger') }}</label>
                                <select class="form-control select2-manager" name="salesManagerCurrent" id="salesManagerCurrent">
                                    <option value="">{{ __('message.select') }}</option>
                                    @foreach($salesManager as $key => $manager)
                                        <option value="{{ $key }}">{{ $manager }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="salesManagerNew">{{ __('message.select_replacement_sales_manager') }}</label>
                                <select class="form-control select2-new" name="salesManagerNew" id="salesManagerNew" multiple>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card-footer">
                    <button class="btn btn-primary" type="submit">
                        <i class="fas fa-save"></i>&nbsp; {{ __('message.save') }}
                    </button>
                </div>
            </div>
        </form>
    </div>

    <script>
        $(document).ready(function () {
            function initSelect2Manager() {
                $('.select2-manager').select2({
                    allowClear: true,
                    placeholder: "{{ __('message.pipe_select_option') }}"
                });
            }

            // Function to initialize select2-new
            function initSelect2New() {
                $('.select2-new').select2({
                    placeholder: "{{ __('message.search') }}",
                    minimumInputLength: 1,
                    maximumSelectionLength: 1,
                    language: {
                        inputTooShort: function () {
                            return '{{ __("message.select2_input_too_short") }}';
                        },
                        noResults: function () {
                            return '{{ __("message.select2_no_results") }}';
                        },
                        searching: function () {
                            return '{{ __("message.select2_searching") }}';
                        }
                    },
                    ajax: {
                        url: '{{ route("search-admins") }}',
                        dataType: 'json',
                        beforeSend: function () {
                            $('.loader').show();
                        },
                        complete: function () {
                            $('.loader').hide();
                        },
                        data: function (params) {
                            return {
                                q: $.trim(params.term)
                            };
                        },
                        processResults: function (data) {
                            return {
                                results: $.map(data, function (value) {
                                    return {
                                        image: value.profile_pic,
                                        text: value.first_name + " " + value.last_name,
                                        id: value.id,
                                        email: value.text
                                    };
                                })
                            };
                        },
                        cache: true
                    },
                    templateResult: formatState,
                });
            }

            // Format dropdown item with image and email
            function formatState(state) {
                if (!state.id) return state.text;

                return $(`
            <div>
                <div style="width: 14%; display: inline-block;">
                    <img src="${state.image}" width="35px" height="35px" style="vertical-align:middle">
                </div>
                <div style="width: 80%; display: inline-block;">
                    <div>${state.text}</div>
                    <div>${state.email}</div>
                </div>
            </div>
        `);
            }

            const containers = document.querySelectorAll('.container-fluid');
            containers.forEach(container => {
                new ResizeObserver(() => {
                    initSelect2Manager();
                    initSelect2New();
                }).observe(container);
            });

            initSelect2Manager();
            initSelect2New();


            $.validator.addMethod("requiredIfPairedSelected", function (value, element, selector) {
                const valIsEmpty = !value || (Array.isArray(value) && value.length === 0);
                const otherVal = $(selector).val();
                const otherIsEmpty = !otherVal || (Array.isArray(otherVal) && otherVal.length === 0);

                // Pass if both are empty; fail if this is empty and the other is not
                return valIsEmpty && otherIsEmpty || !valIsEmpty;
            }, "{{ __('validation.required') }}");


            $('#managerSettingsForm').validate({
                rules: {
                    accountManagerCurrent: {
                        requiredIfPairedSelected: "#accountManagerNew"
                    },
                    accountManagerNew: {
                        requiredIfPairedSelected: "#accountManagerCurrent"
                    },
                    salesManagerCurrent: {
                        requiredIfPairedSelected: "#salesManagerNew"
                    },
                    salesManagerNew: {
                        requiredIfPairedSelected: "#salesManagerCurrent"
                    }
                },
                messages: {
                    accountManagerCurrent: "{{ __('message.existingAccManager_required') }}",
                    accountManagerNew: "{{ __('message.newAccManager_required') }}",
                    salesManagerCurrent: "{{ __('message.select_system_sales_manager') }}",
                    salesManagerNew: "{{ __('message.select_new_sales_manager') }}"
                },
                errorPlacement: function (error, element) {
                    error.addClass('invalid-feedback');
                    error.insertAfter(element);
                },
                highlight: function (element) {
                    $(element).addClass('is-invalid');
                },
                unhighlight: function (element) {
                    $(element).removeClass('is-invalid');
                },
                submitHandler: function (form) {
                    const actionUrl = "{{ url('updateSystemManager') }}";
                    const payload = {
                        existingAccManager: $('#accountManagerCurrent').val(),
                        newAccManager: Array.isArray($('#accountManagerNew').val())
                            ? $('#accountManagerNew').val()[0]
                            : $('#accountManagerNew').val(),
                        existingSaleManager: $('#salesManagerCurrent').val(),
                        newSaleManager: Array.isArray($('#salesManagerNew').val())
                            ? $('#salesManagerNew').val()[0]
                            : $('#salesManagerNew').val(),
                        autoAssignAccount: $('#autoAssignAccountSwitch').is(':checked') ? 1 : 0,
                        autoAssignSales: $('#autoAssignSalesSwitch').is(':checked') ? 1 : 0
                    };
                    $.ajax({
                        url: actionUrl,
                        method: 'POST',
                        data: payload,
                        beforeSend: function() {
                            helper.globalLoader.show();
                        },
                        success: function (response) {
                            helper.showAlert({
                                message: response.message,
                                type: 'success',
                                autoDismiss: 5000,
                                containerSelector: '#system-manager-alert',
                            });
                        },
                        error: function (xhr) {
                            helper.showAlert({
                                message: xhr.responseJSON.message,
                                type: 'error',
                                autoDismiss: 5000,
                                containerSelector: '#system-manager-alert',
                            });
                        },
                        complete: function () {
                            helper.globalLoader.hide();
                        }
                    });
                }
            });
        });

    </script>
  @stop