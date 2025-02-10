@extends('themes.default1.layouts.master')

@section('title')
    {{ __('message.language') }}
@stop

@section('content-header')
    <div class="col-sm-6">
        <h1>{{ __('message.language') }}</h1>
    </div>
    <div class="col-sm-6">
        <ol class="breadcrumb float-sm-right">
            <li class="breadcrumb-item"><a href="{{url('/')}}"><i class="fa fa-dashboard"></i> {{ __('message.home') }}</a></li>
            <li class="breadcrumb-item"><a href="{{url('settings')}}"><i class="fa fa-dashboard"></i> {{ __('message.settings') }}</a></li>
            <li class="breadcrumb-item active">{{ __('message.language') }}</li>
        </ol>
    </div><!-- /.col -->
@stop

@section('content')
<style>
.btn {
    display: inline-block;
    font-weight: 300;
    color: #212529;
    text-align: center;
    vertical-align: middle;
    -webkit-user-select: none;
    -moz-user-select: none;
    -ms-user-select: none;
    user-select: none;
    background-color: transparent;
    border: 1px solid transparent;
    padding: 0.175rem 0.55rem;
    font-size: 1rem;
    line-height: 1.5;
    border-radius: 0.25rem;
    transition: color .15s ease-in-out,background-color .15s ease-in-out,border-color .15s ease-in-out,box-shadow .15s ease-in-out;
}
    .btn-secondary {
    color: #fff;
    background-color: #6c757d;
    border-color: #6c757d;
    box-shadow: none;
}
    .btn-secondary:hover {
    color: #fff;
    background-color: #5a6268;
    border-color: #545b62;
}

.language-btn{
    color: #000;
    background-color: #f8f9fa;
    border-color: #d3d4d5;
    font-weight: 600;
}

.switch {
    position: relative;
    display: inline-block;
    width: 40px;
    height: 20px;
}

.switch input {display:none;}

.slider {
    position: absolute;
    cursor: pointer;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background-color: #f39795;
    -webkit-transition: .4s;
    transition: .4s;
}

.slider:before {
    position: absolute;
    content: "";
    height: 12px;
    width: 12px;
    left: 4px;
    bottom: 4px;
    background-color: white;
    -webkit-transition: .4s;
    transition: .4s;
}

input:checked + .slider {
    background-color: #28a745;
}

input:focus + .slider {
    box-shadow: 0 0 1px #28a745;
}

input:checked + .slider:before {
    -webkit-transform: translateX(20px);
    -ms-transform: translateX(20px);
    transform: translateX(20px);
}

/* Rounded sliders */
.slider.round {
    border-radius: 34px;
}

.slider.round:before {
    border-radius: 50%;
}

.slider.disabled {
    background-color: grey !important;
    cursor: not-allowed;
}

</style>

<div id="response">
    <div class="card card-secondary card-outline">
        <div class="card-body table-responsive">
            <div class="row">
                <div class="col-md-12 col-lg-12">
                    <div id="Localized-license-table_wrapper" class="dataTables_wrapper no-footer">
                        <div id="Localized-license-table_processing" class="dataTables_processing" style="display: none;">
                            <div class="overlay">
                                <i class="fas fa-3x fa-sync-alt fa-spin"></i>
                                <div class="text-bold pt-2">{{ __('message.loading') }}</div>
                            </div>
                        </div>
                        <table id="language-table" class="table display dataTable no-footer" cellspacing="0" width="100%" role="grid" aria-describedby="language-app-table_info">
                            <thead>
                                <tr>
                                    <th>{{ __('message.language') }}</th>
                                    <th>{{ __('message.native_name') }}</th>
                                    <th>{{ __('message.iso_code') }}</th>
                                    <th>{{ __('message.system_default') }}</th>
                                    <th>{{ __('message.action') }} &nbsp;<i class="fas fa-question-circle" data-toggle="tooltip" data-placement="top" title="This toggle allows you to enable or disable the dropdowns for available languages." data-original-title=""></i></th>
                                </tr>
                            </thead>
                            <tbody>
                            @foreach($languages as $language)
                                <tr>
                                    @php
                                        $localeMap = [
                                            'ar' => 'ae',
                                            'bsn' => 'bs',
                                            'de' => 'de',
                                            'en' => 'us',
                                            'en-gb' => 'gb',
                                            'es' => 'es',
                                            'fr' => 'fr',
                                            'id' => 'id',
                                            'it' => 'it',
                                            'kr' => 'kr',
                                            'mt' => 'mt',
                                            'nl' => 'nl',
                                            'no' => 'no',
                                            'pt' => 'pt',
                                            'ru' => 'ru',
                                            'vi' => 'vn',
                                            'zh-hans' => 'cn',
                                            'zh-hant' => 'cn',
                                            'ja' => 'jp',
                                            'ta' => 'in',
                                            'hi' => 'in',
                                            'he' => 'il',
                                            'tr' => 'tr',
                                        ];

                                        $localeKey = $language->locale; // Fallback to 'en'
                                        $flagCountryCode = $localeMap[$localeKey]; // Default to 'us' if not found
                                        $flagClass = 'flag-icon flag-icon-' . $flagCountryCode;
                                    @endphp
                                    <td>
                                        <i class="{{ $flagClass }}" style="margin-right: 5px;"></i>
                                        {{ $language->name ?? '' }}
                                    </td>
                                    <td>{{ $language->translation ?? '' }}</td>
                                    <td>{{ $language->locale ?? '' }}</td>
                                    <td>
                                        @if($defaultLang === $language->locale)
                                            <div class="btn btn-default language-btn text-success">{{ __('message.yes') }}</div>
                                        @else
                                            <div class="btn btn-default language-btn text-danger">{{ __('message.no') }}</div>
                                        @endif
                                    </td>
                                    <td>
                                        <label class="switch toggle_event_editing pull-right">
                                            <input type="checkbox"
                                                   class="checkbox language-toggle"
                                                   data-locale="{{ $language->locale }}"
                                                   {{ $language->enable_disable == 1 ? 'checked' : '' }}
                                                   {{ $defaultLang === $language->locale ? 'disabled' : '' }}>
                                            <span class="slider round {{ $defaultLang === $language->locale ? 'disabled' : '' }}" data-placement="top"
                                                  data-toggle="tooltip" title="{{ $defaultLang === $language->locale ? 'Can\'t disable the default language' : '' }}"></span>
                                        </label>
                                    </td>
                                </tr>
                            @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/1.10.21/css/jquery.dataTables.min.css">
<script src="https://cdn.datatables.net/1.10.21/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.3/dist/umd/popper.min.js"></script>

<script type="text/javascript">

    $(document).ready(function() {

        $('#language-table').DataTable({
            "lengthMenu": [[10, 25, 50, -1], [10, 25, 50, "All"]]
        });
    });

    $('ul.nav-sidebar a').filter(function() {
        return this.id == 'setting';
    }).addClass('active');

    $('ul.nav-treeview a').filter(function() {
        return this.id == 'setting';
    }).parentsUntil(".nav-sidebar > .nav-treeview").addClass('menu-open').prev('a').addClass('active');


    $(document).ready(function () {
        $('[data-toggle="tooltip"]').tooltip();
    });

    document.querySelectorAll('.language-toggle').forEach(toggle => {
        toggle.addEventListener('change', function () {
            const locale = this.dataset.locale;
            const status = this.checked ? 1 : 0;
            const slider = this.nextElementSibling;

            fetch('{{ url("/language-toggle") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({ locale: locale, status: status })
            })
                .then(response =>{

                    console.log(response);
                    if(response.status === 200){
                        window.location.reload();
                    }
                    })
                .catch(error => {
                    // alert('Something went wrong.');
                    console.error(error);
                    this.checked = !this.checked; // revert the toggle
                });
        });
    });
</script>

@stop
