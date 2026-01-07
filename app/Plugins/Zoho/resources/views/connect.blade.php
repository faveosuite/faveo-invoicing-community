@extends('themes.default1.layouts.master')

@section('content')
    <div class="row">

        <!-- Zoho Campaigns Card -->
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Zoho Campaigns</h3>
                </div>
                <div class="card-body text-center">
                    <button
                            class="btn btn-primary connect-zoho"
                            data-platform="campaigns">
                        Connect Zoho Campaigns
                    </button>
                </div>
            </div>
        </div>

        <!-- Zoho CRM Card -->
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Zoho CRM</h3>
                </div>
                <div class="card-body text-center">
                    <button
                            class="btn btn-success connect-zoho"
                            data-platform="crm">
                        Connect Zoho CRM
                    </button>
                </div>
            </div>
        </div>

    </div>

    <script>
        $(document).ready(function () {
            $('.connect-zoho').on('click', function () {

                const platform = $(this).data('platform');

                $.ajax({
                    url: '{{ url('/zoho/oauth/redirect') }}',
                    type: 'GET',
                    data: {
                        platform: platform
                    },
                    success: function (response) {
                        if (response?.data?.redirect_url) {
                            window.location.href = response.data.redirect_url;
                        }
                    },
                    error: function (xhr, status, error) {
                        console.error('Zoho connect error:', error);
                    }
                });
            });
        });
    </script>
@endsection
