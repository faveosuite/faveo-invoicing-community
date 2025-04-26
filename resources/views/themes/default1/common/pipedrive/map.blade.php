@extends('themes.default1.layouts.master')
@section('title')
    Pipedrive
@stop
@section('content-header')
    <div class="col-sm-6">
        <h1>{{ $title }}</h1>
    </div>
    <div class="col-sm-6">
        <ol class="breadcrumb float-sm-right">
            <li class="breadcrumb-item"><a href="{{url('/')}}"> Home</a></li>
            <li class="breadcrumb-item"><a href="{{url('settings')}}"> Settings</a></li>
            <li class="breadcrumb-item"><a href="{{url('pipedrive')}}">Pipedrive Setting</a></li>
            <li class="breadcrumb-item active">Pipedrive Mapping</li>
        </ol>
    </div>
@stop

@section('content')
    <div id="alert-container-pipe"></div>
    <div class="row">
        <div class="col-md-12">
            <div class="card card-secondary card-outline">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-link mr-2"></i>
                        {{ Lang::get('message.list-fields') }}
                    </h3>
                </div>
                <div class="card-body">
                    <form id="mailchimp-form" method="POST" class="needs-validation" novalidate>
                        @csrf
                        <input name="group_id" type="hidden" value="{{ $group_id }}">
                        <div class="table-responsive">
                            <table class="table table-bordered table-striped" id="faveo-attribute-list">
                                <thead class="thead-light">
                                <tr>
                                    <th>
                                        Agora Attributes
                                        <i class="fas fa-question-circle"
                                           data-toggle="tooltip"
                                           title="Attributes available in the system"></i>
                                    </th>
                                    <th>
                                        Pipedrive Attributes
                                        <i class="fas fa-question-circle"
                                           data-toggle="tooltip"
                                           title="Attributes of the pipedrive app"></i>
                                    </th>
                                </tr>
                                </thead>
                                <tbody>
                                <!-- Dynamic rows will be populated here -->
                                </tbody>
                            </table>
                        </div>
                        <div class="row">
                            <div class="col-12">
                                <button type="submit" class="btn btn-primary float-right">
                                    <i class="fa fa-sync-alt mr-2"></i>
                                    {!! Lang::get('message.update') !!}
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script>
        $.ajax({
            url: @json(url("getPipedriveFields/{$group_id}")),
            type: 'GET',
            success: function (response) {
                if (response.success && response.data && Array.isArray(response.data.local_fields)) {
                    // Append rows to the table
                    response.data.local_fields.forEach(function (value) {
                        let options = '';

                        response.data.pipedrive_fields.forEach(function (field) {
                            options += `<option value="${field.field_key}" ${field.local_field_id === value.id ? 'selected' : ''}>${field.field_name}</option>`;
                        });

                        let row = `
                <tr>
                    <td>${value.field_name}</td>
                    <td>
                        <select class="form-control pipedrive-field" name="${value.field_key}">
                            <option value="">Select an option</option>
                            ${options}
                        </select>
                    </td>
                </tr>
            `;

                        $('#faveo-attribute-list tbody').append(row);
                    });

                    $('[data-toggle="tooltip"]').tooltip();

                    // Function to refresh dropdowns (now allowing duplicates)
                    function updateDropdowns() {
                        $('.pipedrive-field').each(function () {
                            let $dropdown = $(this);
                            let currentValue = $dropdown.val();

                            let options = response.data.pipedrive_fields
                                .map(field => `<option value="${field.field_key}" ${field.field_key === currentValue ? 'selected' : ''}>${field.field_name}</option>`)
                                .join('');

                            $dropdown.html(`<option value="">Select an option</option>${options}`);
                        });
                    }

                    // Bind event
                    $('.pipedrive-field').on('change', function () {
                        updateDropdowns();
                    });

                    updateDropdowns(); // Initial update
                }
            },
            error: function (xhr, error) {
                console.log('Error:', xhr, error);
            }
        });
        $(document).ready(function () {

            let alertTimeout;

            function showAlert(type, messageOrResponse) {

                // Generate appropriate HTML
                var html = generateAlertHtml(type, messageOrResponse);

                // Clear any existing alerts and remove the timeout
                $('#alert-container-pipe').html(html);
                clearTimeout(alertTimeout); // Clear the previous timeout if it exists

                // Display alert
                window.scrollTo(0, 0);

                // Auto-dismiss after 5 seconds
                alertTimeout = setTimeout(function () {
                    $('#alert-container-pipe').fadeOut('slow');
                }, 5000);
            }


            function generateAlertHtml(type, response) {
                // Determine alert styling based on type
                const isSuccess = type === 'success';
                const iconClass = isSuccess ? 'fa-check-circle' : 'fa-ban';
                const alertClass = isSuccess ? 'alert-success' : 'alert-danger';

                // Extract message and errors
                const message = response.message || response || 'An error occurred. Please try again.';
                const errors = response.errors || null;

                // Build base HTML
                let html = `<div class="alert ${alertClass} alert-dismissible">` +
                    `<i class="fa ${iconClass}"></i> ` +
                    `${message}` +
                    '<button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>';

                html += '</div>';

                return html;
            }

            // AJAX Form Submission
            $('#mailchimp-form').on('submit', function (event) {
                event.preventDefault();

                let formData = $(this).serialize();

                $.ajax({
                    url: @json(url('sync/pipedrive')),
                    type: 'POST',
                    data: formData,
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    success: function (response) {
                        showAlert('success', response.message);
                    },
                    error: function (data) {
                        var response = data.responseJSON ? data.responseJSON : JSON.parse(data.responseText);

                        if (response.errors) {
                            $.each(response.errors, function (field, messages) {
                                var validator = $('#regiser-form').validate();

                                var fieldSelector = $(`[name="${field}"]`).attr('name');  // Get the name attribute of the selected field

                                validator.showErrors({
                                    [fieldSelector]: messages[0]
                                });
                            });
                        } else {
                            showAlert('error', response);
                        }
                    }
                });
            });
        });
    </script>
@stop
