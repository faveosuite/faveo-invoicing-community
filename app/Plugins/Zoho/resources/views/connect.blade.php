@extends('themes.default1.layouts.master')

@section('content')
    @if(request('zoho_status') === 'success')
        <div class="alert alert-success alert-dismissible fade show">
            {{ urldecode(request('message')) }}
            <button type="button" class="close" data-dismiss="alert">
                &times;
            </button>
        </div>
    @endif

    @if(request('zoho_status') === 'error')
        <div class="alert alert-danger alert-dismissible fade show">
            {{ urldecode(request('message')) }}
            <button type="button" class="close" data-dismiss="alert">
                &times;
            </button>
        </div>
    @endif


    <div class="row">

        @foreach($integrations as $integration)
            <div class="col-md-6">
                <div class="card">

                    <!-- HEADER -->
                    <div class="card-header d-flex align-items-center">

                        <!-- LEFT: TITLE + STATUS -->
                        <div class="d-flex align-items-center">
                            <h3 class="card-title mb-0 text-capitalize">
                                Zoho {{ $integration->platform }}
                            </h3>

                            <span
                                    class="badge ml-2 {{ $integration->is_active ? 'badge-success' : 'badge-secondary' }}">
            {{ $integration->is_active ? 'Connected' : 'Not Connected' }}
        </span>
                        </div>

                        <!-- PUSH RIGHT -->
                        <div class="ml-auto">
                            @if($integration->is_active)
                                <a
                                        href="{{ url('zoho/'.$integration->platform. '/contacts/mapping') }}"
                                        class="text-muted"
                                        title="Settings">
                                    <i class="fa fa-cog fa-lg"></i>
                                </a>
                            @endif
                        </div>

                    </div>


                    <!-- BODY -->
                    <div class="card-body d-flex flex-column">

                        <p class="text-muted mb-3">
                            {{ $integration->description }}
                        </p>

                        <!-- BUTTON ALIGN RIGHT -->
                        <div class="mt-auto text-right">
                            <button
                                    class="btn {{ $integration->is_active ? 'btn-outline-secondary' : 'btn-primary' }} connect-zoho"
                                    data-integration-id="{{ $integration->id }}"
                                    data-platform="{{ $integration->platform }}">
                                {{ $integration->is_active ? 'Reconnect' : 'Connect' }}
                            </button>
                        </div>

                    </div>

                </div>

            </div>
        @endforeach

    </div>

    <!-- ===================== ZOHO MODAL ===================== -->
    <div class="modal fade" id="zohoModal" tabindex="-1" role="dialog">
        <div class="modal-dialog" role="document">
            <div class="modal-content">

                <div class="modal-header">
                    <h5 class="modal-title">
                        Connect Zoho <span id="modal-platform-title"></span>
                    </h5>
                    <button type="button" class="close" data-dismiss="modal">
                        <span>&times;</span>
                    </button>
                </div>

                <div class="modal-body">
                    <input type="hidden" id="zoho-integration-id">

                    <div class="form-group">
                        <label>Client ID</label>
                        <input type="text" id="client-id" class="form-control">
                    </div>

                    <div class="form-group">
                        <label>Client Secret</label>
                        <input type="password" id="client-secret" class="form-control">
                    </div>

                    <div class="form-group">
                        <label>Redirect URI</label>
                        <input
                                type="text"
                                id="redirect-uri"
                                class="form-control"
                                placeholder="https://example.com/zoho/callback"
                        >
                    </div>

                    <div class="form-group">
                        <label>Region</label>
                        <select id="region" class="form-control">
                            <option value="in">India</option>
                            <option value="us">US</option>
                            <option value="eu">Europe</option>
                            <option value="au">Australia</option>
                        </select>
                    </div>

                    <div id="zoho-error" class="text-danger d-none">
                        Something went wrong. Please try again.
                    </div>
                </div>

                <div class="modal-footer">
                    <button id="save-zoho" class="btn btn-success">
                        Save & Continue
                    </button>
                    <button class="btn btn-secondary" data-dismiss="modal">
                        Cancel
                    </button>
                </div>

            </div>
        </div>
    </div>
    <!-- ===================== END MODAL ===================== -->

    <script>
        $(document).ready(function () {

            $('.connect-zoho').on('click', function () {

                const integrationId = $(this).data('integration-id');
                const platform = $(this).data('platform');

                // set values
                $('#zoho-integration-id').val(integrationId);
                $('#modal-platform-title').text(platform.toUpperCase());
                $('#zoho-error').addClass('d-none');

                // clear fields initially
                $('#client-id').val('');
                $('#client-secret').val('');
                $('#redirect-uri').val('');
                $('#region').val('in');

                // OPEN MODAL FIRST
                $('#zohoModal').modal('show');

                // FETCH EXISTING DATA FROM API
                $.get(
                    '{{ url('/zoho/getKeys') }}/' + integrationId,
                    function (response) {

                        if (!response.success) {
                            return;
                        }

                        $('#client-id').val(response.data.client_id);

                        $('#client-secret')
                            .val(response.data.client_secret)
                            .data('masked', true);

                        $('#redirect-uri').val(response.data.redirect_uri);

                        $('#region').val(response.data.region);
                    }
                );
            });

            // Save credentials
            $('#save-zoho').on('click', function () {

                $.ajax({
                    url: '{{ url('/zoho/saveKeys') }}',
                    method: 'POST',
                    data: {
                        integration_id: $('#zoho-integration-id').val(),
                        client_id: $('#client-id').val(),
                        client_secret: $('#client-secret').val(),
                        redirect_uri: $('#redirect-uri').val(),
                        region: $('#region').val(),
                        _token: '{{ csrf_token() }}'
                    },
                    success: function (response) {
                        if (response?.data?.redirect_url) {
                            window.location.href = response.data.redirect_url;
                        } else {
                            $('#zoho-error').removeClass('d-none');
                        }
                    },
                    error: function () {
                        $('#zoho-error').removeClass('d-none');
                    }
                });

            });

        });
    </script>

@endsection
