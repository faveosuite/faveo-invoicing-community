@extends('themes.default1.layouts.master')
@section('title')
    {{ __('message.system_manager') }}
@stop
@section('content-header')
<style>
    .replace-row {
        margin-top: 32px;
    }
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
        <div class="card card-secondary card-outline elevation-2">
            <div class="card-header">
                <h3 class="card-title">
                    {{ __('message.system_manager_settings') }}
                </h3>
            </div>

            <div class="card-body">
                <!-- Account Manager Section -->
                <div class="card card-outline card-secondary">
                    <div class="card-header">
                        <h3 class="card-title">
                            <i class="fas fa-user-tie mr-2"></i>
                            {{ __('message.account_manager') }}
                        </h3>
                    </div>
                    <div class="card-body">
                        <!-- Auto Assignment Switch for Account Manager -->
                        <div class="row mb-3">
                            <div class="col-md-12">
                                <div class="custom-control custom-switch">
                                    <input type="checkbox" class="custom-control-input" id="autoAssignAccountSwitch" {{ $accountManagersAutoAssign ? 'checked' : '' }}>
                                    <label class="custom-control-label" for="autoAssignAccountSwitch">
                                        <strong>{{ __('message.enable_account_manager') }}</strong>
                                        <small class="text-muted d-block">{{ __('message.account_upon_creation') }}</small>
                                    </label>
                                </div>
                            </div>
                        </div>

                        <hr>
                        <form id="accountManagerForm">
                        <div class="row">
                            <div class="col-md-5">
                                <div class="form-group">
                                    <label for="accountManagerCurrent">
                                        <i class="fas fa-user mr-1"></i>
                                        {{ __('message.current_account_manager') }}
                                    </label>
                                    <select class="form-control select2-manager" name="accountManagerCurrent" id="accountManagerCurrent">
                                        <option value=""> {{ __('message.select') }} </option>
                                        @foreach($accountManagers as $key=>$manager)
                                            <option value={{$key}}>{{$manager}}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-5">
                                <div class="form-group">
                                    <label for="accountManagerNew">
                                        <i class="fas fa-user-plus mr-1"></i>
                                        {{ __('message.select_replacement_manager') }}
                                    </label>
                                    <select class="form-control select2-new" multiple name="accountManagerNew"  id="accountManagerNew">
                                    </select>
                                </div>
                            </div>
                            <div class="col-2">
                                <button type="submit" class="btn btn-primary replace-row">
                                    <i class="fas fa-exchange-alt mr-1"></i>
                                    {{ __('message.replace') }}
                                </button>
                            </div>
                        </div>
                        </form>
                    </div>
                </div>

                <!-- Sales Manager Section -->
                <div class="card card-outline card-secondary">
                    <div class="card-header">
                        <h3 class="card-title">
                            <i class="fas fa-chart-line mr-2"></i>
                            {{ __('message.sales_manager') }}
                        </h3>
                    </div>
                    <div class="card-body">
                        <!-- Auto Assignment Switch for Sales Manager -->
                        <div class="row mb-3">
                            <div class="col-md-12">
                                <div class="custom-control custom-switch">
                                    <input type="checkbox" class="custom-control-input" id="autoAssignSalesSwitch" {{ $salesManagerAutoAssign ? 'checked' : '' }}>
                                    <label class="custom-control-label" for="autoAssignSalesSwitch">
                                        <strong>{{ __('message.enable_sales_manager') }}</strong>
                                        <small class="text-muted d-block">{{ __('message.sales_upon_creation') }}</small>
                                    </label>
                                </div>
                            </div>
                        </div>

                        <hr>

                        <form id="salesManagerForm">
                        <div class="row">
                            <div class="col-md-5">
                                <div class="form-group">
                                    <label for="salesManagerCurrent">
                                        <i class="fas fa-user mr-1"></i>
                                        {{ __('message.current_sales_manger') }}
                                    </label>
                                    <select class="form-control select2-manager" name="salesManagerCurrent"  id="salesManagerCurrent">
                                        <option value=""> {{ __('message.select') }} </option>
                                        @foreach($salesManager as $key=>$manager)
                                            <option value={{$key}}>{{$manager}}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-5">
                                <div class="form-group">
                                    <label for="salesManagerNew">
                                        <i class="fas fa-user-plus mr-1"></i>
                                       {{ __('message.select_replacement_sales_manager') }}
                                    </label>
                                    <select class="form-control select2-new" multiple name="salesManagerNew" id="salesManagerNew">
                                    </select>
                                </div>
                            </div>
                            <div class="col-2">
                                <button type="submit" class="btn btn-primary replace-row">
        <span class="btn-text">
            <i class="fas fa-exchange-alt mr-1"></i>
            {{ __('message.replace') }}
        </span>
                                    <span class="btn-loader" style="display: none;">
            <i class="fas fa-spinner fa-spin mr-1"></i>
            Loading...
        </span>
                                </button>
                            </div>
                        </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
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

            function sendManagerAction(url, payload) {
                $.ajax({
                    url: url,
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

            function handleAutoAssignChange(selector, type) {
                const actionUrl = "{{ url('managerAutoAssign') }}";
                $(selector).on('change', function () {
                    const status = $(this).is(':checked') ? 1 : 0;
                    sendManagerAction(actionUrl, {
                        manager_role: type,
                        status: status
                    });
                });
            }

            handleAutoAssignChange('#autoAssignAccountSwitch', 'account');
            handleAutoAssignChange('#autoAssignSalesSwitch', 'sales');

            $('#accountManagerForm').validate({
                rules: {
                    accountManagerCurrent: { required: true },
                    accountManagerNew: { required: true }
                },
                messages: {
                    accountManagerCurrent: "{{ __('message.existingAccManager_required') }}",
                    accountManagerNew: "{{ __('message.newAccManager_required') }}"
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
                    const actionUrl = "{{ url('replace-acc-manager') }}";
                    const payload = {
                        existingAccManager: $('#accountManagerCurrent').val(),
                        newAccManager: Array.isArray($('#accountManagerNew').val())
                            ? $('#accountManagerNew').val()[0]
                            : $('#accountManagerNew').val()

                    };
                    sendManagerAction(actionUrl, payload);
                }
            });

            $('#salesManagerForm').validate({
                rules: {
                    salesManagerCurrent: { required: true },
                    salesManagerNew: { required: true }
                },
                messages: {
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
                    const actionUrl = "{{ url('replace-sales-manager') }}";
                    const payload = {
                        existingSaleManager: $('#salesManagerCurrent').val(),
                        newSaleManager: Array.isArray($('#salesManagerNew').val())
                            ? $('#salesManagerNew').val()[0]
                            : $('#salesManagerNew').val()
                    };
                    sendManagerAction(actionUrl, payload);
                }
            });
        });

    </script>
  @stop