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
    
    <style>
        .mapping-slot {
            border: 1px solid #dee2e6;
            border-radius: 8px;
            transition: all 0.3s;
            cursor: pointer;
            background-color: #ffffff;
        }

        .mapping-slot:hover {
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
            background-color: #f8f9fa;
        }

        .mapping-container {
            width: 100%;
            margin: 0 auto;
        }

        .font-weight-medium {
            font-weight: 500;
            font-size: 0.95rem;
        }

        @media (max-width: 767.98px) {
            .mapping-container {
                padding: 0 10px;
            }
        }
    </style>
    <div class="row">
        <div class="col-md-12">
            <div id="alert-container"></div>
            <div class="card card-secondary card-outline">
                <div class="card-body">
                    <div class="tab-content" id="fieldMappingTabsContent">
                        <div class="tab-pane fade show active" id="field-mapping" role="tabpanel" aria-labelledby="field-mapping-tab">
                            <div class="d-flex flex-column align-items-center">
                                <div class="tab-content-header text-center mb-4">
                                    <h4 class="font-weight-bold">Field Mapping</h4>
                                    <p class="text-muted mt-2">Choose the fields you want to map to keep your contacts, organizations, and deals in sync between Biling and Pipedrive</p>
                                </div>

                                <div class="mapping-container mt-2" >
                                    <!-- Mapping Slot 1 -->
                                    <a href="{{ url('pipedrive/mapping/'. $groups['personId']) }}" class="text-decoration-none text-dark">
                                    <div class="mapping-slot p-3 mb-3">
                                        <div class="row align-items-center justify-content-center no-gutters">
                                            <div class="col-5 text-center">
                                                <div class="d-flex align-items-center justify-content-center">
                                                    <img src="{{ $settings->admin_logo }}" alt="Saleshandy logo" class="mr-2" width="24">
                                                    <span class="font-weight-medium">Agora Fields</span>
                                                </div>
                                            </div>
                                            <div class="col-2 text-center">
                                                <i class="fas fa-exchange-alt text-primary"></i>
                                            </div>
                                            <div class="col-5 text-center">
                                                <div class="d-flex align-items-center justify-content-center">
                                                    <svg xmlns="http://www.w3.org/2000/svg" version="1.0" alt="Pipedrive icon" class="mr-2" width="24" viewBox="0 0 512.000000 512.000000" preserveAspectRatio="xMidYMid meet">

                                                        <g transform="translate(0.000000,512.000000) scale(0.100000,-0.100000)" fill="#000000" stroke="none">
                                                            <path d="M0 2560 l0 -2560 2560 0 2560 0 0 2560 0 2560 -2560 0 -2560 0 0 -2560z m3123 1646 c295 -89 522 -301 643 -602 141 -349 136 -805 -11 -1148 -61 -141 -123 -234 -230 -342 -210 -212 -445 -303 -745 -291 -186 8 -338 59 -468 157 -29 22 -55 40 -57 40 -3 0 -6 -237 -7 -527 l-3 -528 -320 0 -320 0 -5 1275 -5 1275 -157 3 -158 3 0 320 0 320 358 -3 c352 -3 358 -3 410 -27 62 -28 118 -89 133 -144 l11 -40 46 45 c132 127 273 204 433 238 24 5 116 8 204 6 139 -3 172 -7 248 -30z"/>
                                                            <path d="M2690 3670 c-120 -19 -202 -62 -283 -149 -105 -114 -146 -233 -154 -447 -8 -207 21 -323 114 -465 47 -73 106 -126 180 -163 65 -33 123 -45 214 -46 224 0 409 142 486 374 25 75 27 94 27 251 1 187 -9 245 -64 359 -99 209 -301 320 -520 286z"/>
                                                        </g>
                                                    </svg>
                                                    <span class="font-weight-medium">Person Fields</span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    </a>

                                    <!-- Mapping Slot 2 -->
                                    <a href="{{ url('pipedrive/mapping/'. $groups['dealId']) }}" class="text-decoration-none text-dark">
                                    <div class="mapping-slot p-3 mb-3">
                                        <div class="row align-items-center justify-content-center no-gutters">
                                            <div class="col-5 text-center">
                                                <div class="d-flex align-items-center justify-content-center">
                                                    <img src="{{ $settings->admin_logo }}" alt="Saleshandy logo" class="mr-2" width="24">
                                                    <span class="font-weight-medium">Agora Fields</span>
                                                </div>
                                            </div>
                                            <div class="col-2 text-center">
                                                <i class="fas fa-exchange-alt text-primary"></i>
                                            </div>
                                            <div class="col-5 text-center">
                                                <div class="d-flex align-items-center justify-content-center">
                                                    <svg xmlns="http://www.w3.org/2000/svg" version="1.0" alt="Pipedrive icon" class="mr-2" width="24" viewBox="0 0 512.000000 512.000000" preserveAspectRatio="xMidYMid meet">

                                                        <g transform="translate(0.000000,512.000000) scale(0.100000,-0.100000)" fill="#000000" stroke="none">
                                                            <path d="M0 2560 l0 -2560 2560 0 2560 0 0 2560 0 2560 -2560 0 -2560 0 0 -2560z m3123 1646 c295 -89 522 -301 643 -602 141 -349 136 -805 -11 -1148 -61 -141 -123 -234 -230 -342 -210 -212 -445 -303 -745 -291 -186 8 -338 59 -468 157 -29 22 -55 40 -57 40 -3 0 -6 -237 -7 -527 l-3 -528 -320 0 -320 0 -5 1275 -5 1275 -157 3 -158 3 0 320 0 320 358 -3 c352 -3 358 -3 410 -27 62 -28 118 -89 133 -144 l11 -40 46 45 c132 127 273 204 433 238 24 5 116 8 204 6 139 -3 172 -7 248 -30z"/>
                                                            <path d="M2690 3670 c-120 -19 -202 -62 -283 -149 -105 -114 -146 -233 -154 -447 -8 -207 21 -323 114 -465 47 -73 106 -126 180 -163 65 -33 123 -45 214 -46 224 0 409 142 486 374 25 75 27 94 27 251 1 187 -9 245 -64 359 -99 209 -301 320 -520 286z"/>
                                                        </g>
                                                    </svg>
                                                    <span class="font-weight-medium">Deal Fields</span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    </a>

                                    <!-- Mapping Slot 3 -->
                                    <a href="{{ url('pipedrive/mapping/'. $groups['organizationId']) }}" class="text-decoration-none text-dark">
                                    <div class="mapping-slot p-3 mb-3">
                                        <div class="row align-items-center justify-content-center no-gutters">
                                            <div class="col-5 text-center">
                                                <div class="d-flex align-items-center justify-content-center">
                                                    <img src="{{ $settings->admin_logo }}" alt="Saleshandy logo" class="mr-2" width="24">
                                                    <span class="font-weight-medium">Agora Fields</span>
                                                </div>
                                            </div>
                                            <div class="col-2 text-center">
                                                <i class="fas fa-exchange-alt text-primary"></i>
                                            </div>
                                            <div class="col-5 text-center">
                                                <div class="d-flex align-items-center justify-content-center">
                                                    <svg xmlns="http://www.w3.org/2000/svg" version="1.0" alt="Pipedrive icon" class="mr-2" width="24" viewBox="0 0 512.000000 512.000000" preserveAspectRatio="xMidYMid meet">

                                                        <g transform="translate(0.000000,512.000000) scale(0.100000,-0.100000)" fill="#000000" stroke="none">
                                                            <path d="M0 2560 l0 -2560 2560 0 2560 0 0 2560 0 2560 -2560 0 -2560 0 0 -2560z m3123 1646 c295 -89 522 -301 643 -602 141 -349 136 -805 -11 -1148 -61 -141 -123 -234 -230 -342 -210 -212 -445 -303 -745 -291 -186 8 -338 59 -468 157 -29 22 -55 40 -57 40 -3 0 -6 -237 -7 -527 l-3 -528 -320 0 -320 0 -5 1275 -5 1275 -157 3 -158 3 0 320 0 320 358 -3 c352 -3 358 -3 410 -27 62 -28 118 -89 133 -144 l11 -40 46 45 c132 127 273 204 433 238 24 5 116 8 204 6 139 -3 172 -7 248 -30z"/>
                                                            <path d="M2690 3670 c-120 -19 -202 -62 -283 -149 -105 -114 -146 -233 -154 -447 -8 -207 21 -323 114 -465 47 -73 106 -126 180 -163 65 -33 123 -45 214 -46 224 0 409 142 486 374 25 75 27 94 27 251 1 187 -9 245 -64 359 -99 209 -301 320 -520 286z"/>
                                                        </g>
                                                    </svg>
                                                    <span class="font-weight-medium">Organisation Fields</span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    </a>
                                </div>
                            </div>
                        </div>
                        <div class="tab-pane fade" id="triggers" role="tabpanel" aria-labelledby="triggers-tab">
                            <!-- Content for Triggers & Activity tab -->
                            <div class="text-center p-5">
                                <h4>Triggers & Activity Content</h4>
                                <p class="text-muted">This section is empty in the original HTML.</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@stop