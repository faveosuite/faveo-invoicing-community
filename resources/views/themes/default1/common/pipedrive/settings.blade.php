@extends('themes.default1.layouts.master')
@section('title')
    Mailchimp
@stop
@section('content-header')
    <div class="col-sm-6">
        <h1>Pipedrive Settings</h1>
    </div>
    <div class="col-sm-6">
        <ol class="breadcrumb float-sm-right">
            <li class="breadcrumb-item"><a href="{{url('/')}}"><i class="fa fa-dashboard"></i> Home</a></li>
            <li class="breadcrumb-item"><a href="{{url('settings')}}"><i class="fa fa-dashboard"></i> Settings</a></li>
            <li class="breadcrumb-item active">Pipedrive Setting</li>
        </ol>
    </div><!-- /.col -->
@stop

@section('content')

    <div class="row">
        <div class="col-md-12">
            <div id="alert-container"></div>
            <div class="card card-secondary card-outline">
                <div class="card-body">
                    <form method="post" action="{{ url('pipedrive') }}" enctype="multipart/form-data">
                        @csrf
                        <table class="table table-condensed">
                            <tr>
                                <td><b><label for="api_key" class="required">{{ __('message.api_key') }}</label></b></td>
                                <td>
                                    <div class="form-group {{ $errors->has('api_key') ? 'has-error' : '' }}">
                                        <input type="text" name="api_key" id="api_key" class="form-control" value="{{ $apiKey }}" disabled>
                                        <p><i>{{ __('message.enter-the-mailchimp-api-key-setting') }}</i></p>
                                    </div>
                                </td>
                            </tr>
                            <tr>
                                <td><b><label for="mapping" class="required">{{ __('message.mapping') }}</label></b></td>
                                <td>
                                    <div class="form-group">
                                        <div class="col-md-6">
                                            <a href="javascript:void(0);" id="mappingBtn" class="btn btn-secondary btn-sm">
                                                <span id="btnText">{{ __('message.mapping') }}</span>
                                                <i id="btnLoader" class="fas fa-spinner fa-spin ml-2" style="display: none;"></i>
                                            </a>
                                            <p><i>{{ __('message.map-the-mailchimp-field-with-agora') }}</i></p>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                        </table>
{{--                        <button type="submit" class="btn btn-primary pull-right" id="submit" style="margin-top:-40px;">--}}
{{--                            <i class="fa fa-sync-alt">&nbsp;</i>{{ __('message.update') }}--}}
{{--                        </button>--}}
                    </form>
                </div>
            </div>
        </div>
    </div>
    <script>
        $(document).ready(function () {
            let alertTimeout;
            function showAlert(type, messageOrResponse) {

                // Generate appropriate HTML
                var html = generateAlertHtml(type, messageOrResponse);

                // Clear any existing alerts and remove the timeout
                $('#alert-container').html(html);
                clearTimeout(alertTimeout); // Clear the previous timeout if it exists

                // Display alert
                window.scrollTo(0, 0);

                // Auto-dismiss after 5 seconds
                alertTimeout = setTimeout(function() {
                    $('#alert-container .alert').fadeOut('slow');
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
            $('#mappingBtn').on('click', function () {
                $.ajax({
                    url: "{{ route('sync-pipedrive') }}",
                    type: "POST",
                    dataType: "json",
                    data: {
                        _token: "{{ csrf_token() }}"
                    },
                    beforeSend: function () {
                        $('#btnLoader').show(); // Show the spinner
                        $('#mappingBtn').addClass('disabled');
                    },
                    success: function (response) {
                        showAlert('success', response.message);
                    },
                    error: function (data) {
                        var response = data.responseJSON ? data.responseJSON : JSON.parse(data.responseText);
                        showAlert('error', response);
                    },
                    complete: function () {
                        $('#btnLoader').hide(); // Hide the spinner
                        $('#mappingBtn').removeClass('disabled');
                    }
                });
            });
        });
    </script>
@stop