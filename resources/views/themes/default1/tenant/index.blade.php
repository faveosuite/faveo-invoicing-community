@extends('themes.default1.layouts.master')
@section('title')
    Cloud Hub
@stop



@section('content-header')
    <style type="text/css">
   /* Loading spinner */
    #loading {
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background-color: rgba(0, 0, 0, 0.5);
        display: flex;
        align-items: center;
        justify-content: center;
        z-index: 9999;
    }

    .spinner {
        border: 4px solid #f3f3f3;
        border-top: 4px solid #3498db;
        border-radius: 50%;
        width: 30px;
        height: 30px;
        animation: spin 1s linear infinite;
    }

    @keyframes spin {
        0% {
            transform: rotate(0deg);
        }
        100% {
            transform: rotate(360deg);
        }
    }

       /* Loading spinner */
    #tenatloading {
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background-color: rgba(0, 0, 0, 0.5);
        display: flex;
        align-items: center;
        justify-content: center;
        z-index: 9999;
    }

      .tenatspinner {
        border: 4px solid #f3f3f3;
        border-top: 4px solid #3498db;
        border-radius: 50%;
        width: 30px;
        height: 30px;
        animation: spin 1s linear infinite;
        }

  #tenant-table_wrapper input[type="search"] {
    position: relative;
    right: 180px !important;
    top: -4px;
    padding: 4px;
    border-radius: 5px;
}

#pop-product-table_wrapper input[type="search"] {
    position: initial;
    right: initial;
    padding: initial;
    border-radius: initial;
}

.custom-dropdown {
    position: relative;
    z-index: 9999;
}

.custom-dropdown .form-check {
    padding-right: 60px;
    position: relative;
    right: -15px;
}

.dropdown-menu {
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
    position: absolute;
    max-height: 300px;
    overflow-y: auto;
    overflow-x: hidden;
    width: max-content;
}

#tenat_export-report-btn,
.custom-dropdown {
    z-index: 1000;
}

#tenat_export-report-btn {
    position: absolute;
    right: 20px;
    top: 20px;
}

.card-body.table-responsive {
    position: relative;
    overflow: visible;
}

.dataTables_filter {
    position: relative;
    z-index: 1;
}

.d-flex.justify-content-between {
    margin-bottom: 1rem;
    position: relative;
}

</style>

    <div class="col-sm-6">
        <h1>{{ __('message.cloud_details') }}</h1>
    </div>

    <div class="col-sm-6">
        <ol class="breadcrumb float-sm-right">
            <li class="breadcrumb-item"><a href="{{ url('/') }}"> {{ __('message.home') }}</a></li>
            <li class="breadcrumb-item"><a href="{{ url('settings') }}"> {{ __('message.settings') }}</a></li>
            <li class="breadcrumb-item active">{{ __('message.cloud_hub') }}</li>
        </ol>
    </div>
@stop


@section('content')

    <div class="card card-secondary card-outline">
        <div class="card-header">
            <h3 class="card-title">{{ __('message.cloud_server') }}</h3>
        </div>
        <div class="card-body table-responsive">
            {!! Form::model($cloud, ['route'=> 'cloud-details']) !!}
            <div class="row">
                <div class="col-md-6">
                    <div class="form-group">
                        {!! Form::label('cloud_central_domain', Lang::get('message.cloud_central_domain'), ['class' => 'required']) !!}
                        {!! Form::text('cloud_central_domain', null, ['class' => 'form-control']) !!}
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        {!! Form::label('cloud_cname', Lang::get('message.cloud_cname'),['class' => 'required']) !!}
                        {!! Form::text('cloud_cname', null, ['class' => 'form-control']) !!}
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-md-12">
                    <button type="submit" class="btn btn-primary">
                        <i class="fa fa-save"></i> {!! Lang::get('message.save') !!}
                    </button>
                </div>
            </div>
            {!! Form::close() !!}
        </div>
    </div>
    <div class="card card-secondary card-outline">
        <div class="card-header">
            <h3 class="card-title">{{ __('message.customise_cloud_popup') }}</h3>
        </div>

        <div class="card-body table-responsive">
            {!! Form::model($cloudPopUp, ['route'=> 'cloud-pop-up']) !!}
            <div class="row">
                <div class="col-md-6">
                    <div class="form-group">
                        {!! Form::label('cloud_top_message', Lang::get('message.cloud_top_message'), ['class' => 'required']) !!}
                        {!! Form::text('cloud_top_message', null, ['class' => 'form-control']) !!}
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        {!! Form::label('cloud_label_field', Lang::get('message.cloud_label_field'),['class' => 'required']) !!}
                        {!! Form::text('cloud_label_field', null, ['class' => 'form-control']) !!}
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        {!! Form::label('cloud_label_radio', Lang::get('message.cloud_label_radio'),['class' => 'required']) !!}
                        {!! Form::text('cloud_label_radio', null, ['class' => 'form-control']) !!}
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-md-12">
                    <button type="submit" class="btn btn-primary">
                        <i class="fa fa-save"></i> {!! Lang::get('message.save') !!}
                    </button>
                </div>
            </div>
            {!! Form::close() !!}
        </div>
    </div>
    <?php
        $products = \DB::table('products')->get()
    ?>
    <div class="card card-secondary card-outline">
        <div class="card-header">
            <h3 class="card-title">{{ __('message.cloud_product_configuration') }}</h3>
        </div>
        <div class="card-body">
            {!! Form::model('',['route' => 'cloud-product-store']) !!}
            <div class="row original-fields">
                <div class="col-md-4">
                    {!! Form::label('cloud_product', Lang::get('message.cloud_product'), ['class' => 'required']) !!}
                    <div class="form-group">
                        <!-- Select Field 1 -->
                        <select name="cloud_product" class="form-control">
                            @foreach($products as $product)
                            <option value="{!! $product->id !!}">{{$product->name}}</option>
                            @endforeach
                            <!-- Add more options as needed -->
                        </select>
                    </div>
                </div>
                <div class="col-md-4">
                    <?php
                      $plans = \DB::table('plans')->get();
                    ?>
                    {!! Form::label('cloud_free_plan', Lang::get('message.cloud_free_plan'), ['class' => 'required']) !!}
                    <div class="form-group">
                        <select name="cloud_free_plan" class="form-control">
                            @foreach($plans as $plan)
                            <option value="{!! $plan->id !!}">{!! $plan->name !!}</option>
                            @endforeach

                        </select>
                    </div>
                </div>
                <div class="col-md-4">
                    {!! Form::label('cloud_product_key', Lang::get('message.cloud_product_key'), ['class' => 'required']) !!}
                    <div class="form-group">
                        <input type="text" name="cloud_product_key" class="form-control">
                    </div>
                </div>

            </div>

            <div class="row">
                <div class="col-md-6">
                    <button type="submit" class="btn btn-primary">
                        <i class="fa fa-save"></i> {{ __('message.save') }}
                    </button>
                </div>
            </div>
            {!! Form::close() !!}
        </div>
            <div id="loading" style="display: none;">
                <div class="spinner"></div>
            </div>
            <div id="successmsgpop"></div>
            <div id="errorpop"></div>
        <div class="card-body table-responsive">
            <table id="pop-product-table" class="table display" cellspacing="0" width="100%" styleClass="borderless">
                <thead>
                <tr>
                    <th>{{ __('message.cloud_prod') }}</th>
                    <th>{{ __('message.free_plan_cloud') }}</th>
                    <th>{{ __('message.cloud_prod_key') }}</th>
                    <th>{{ __('message.action') }}</th>
                </tr>
                </thead>
            </table>
        </div>
    </div>


    <script>
        $(document).ready(function () {
            $('#addFields').on('click', function () {
                var originalFields = $('.original-fields').html();
                $('.duplicate-fields').append(originalFields);
            });
        });
    </script>






    <div class="card card-secondary card-outline">
            <div class="card-header">
                <h3 class="card-title">{{ __('message.cloud_data_centers') }}</h3>
            </div>

          <div class="card-body">
            {!! Form::model('',['route' => 'cloud-data-center-store']) !!}
                  <div class="row">
                      <div class="col-md-4">
                          <?php $countries = \App\Model\Common\Country::cursor(); ?>
                          {!! Form::label('cloud_country', Lang::get('message.cloud_country'), ['class' => 'required']) !!}
                          <div class="form-group">
                              <!-- Select Field 1 -->
                              <select id="cloud_countries" name="cloud_countries" class="form-control select2">
                                  <option value="">{{ __('message.choose') }}</option>
                                  @foreach($countries as $country)
                                      <option value="{!! strtolower($country->country_code_char2) !!}">{{$country->nicename}}</option>
                                  @endforeach
                                  <!-- Add more options as needed -->
                              </select>
                          </div>
                      </div>
                      <div class="col-md-4">
                          <?php
                          $states = \App\Model\Common\State::get();
                          ?>
                          {!! Form::label('cloud_state', Lang::get('message.cloud_state'), ['class' => 'required']) !!}
                          <div class="form-group">

                              <select id="cloud_state" name="cloud_state" class="form-control">
                              </select>
                          </div>
                      </div>

                      <div class="col-md-4">
                          {!! Form::label('cloud_city', Lang::get('message.cloud_city')) !!}
                          <div class="form-group">
                              <input type="text" name="cloud_city" class="form-control">
                          </div>

                      </div>

                  </div>
                       <div class="row">
                           <div class="col-md-4">
                               <div class="form-group">
                                   <button type="submit" class="btn btn-primary">
                                       <i class="fa fa-save"></i> {{ __('message.save') }}
                                   </button>
                               </div>

                           </div>
                       </div>
                  {!! Form::close() !!}


            <div id="map" style="height: 450px;"></div>
          </div>
        </div>
        <div class="card card-secondary card-outline">
            <div class="card-header">
                <div id="response"></div>
                <h5>{{ __('message.set_cloud_free_trial') }}</h5>
            </div>
            <div class="card-body">
                {!! Form::open(['url' => 'enable/cloud', 'method' => 'POST']) !!}
            <div class="row">
                <div class="col-md-4">
                    <div class="form-group">
                        {!! Form::label('debug', Lang::get('message.cloud_free_trial')) !!}
                        <div class="row">
                            <div class="col-sm-3">
                                <input type="radio" name="debug" value="true" @if($cloudButton == 1) checked="true" @endif > {{Lang::get('message.enable')}}
                            </div>
                            <div class="col-sm-3">
                                <input type="radio" name="debug" value="false" @if($cloudButton == 0) checked="true" @endif > {{Lang::get('message.disable')}}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div>
                <button type="submit" class="btn btn-primary">
                    <i class="fa fa-save"></i> {{ __('message.save') }}
                </button>
            </div>
        </div>
    </div>
    @if($cloud != null)
    <div id="export-message"></div>
        <div class="card card-secondary card-outline">
    <div class="card-header">
        <h3 class="card-title">{{ __('message.tenants') }}</h3>
    </div>
    <div id="tenatloading" style="display: none;">
        <div class="tenatspinner"></div>
    </div>
    <div id="successmsg"></div>
    <div id="error"></div>

        <button type="button" id="tenat_export-report-btn" class="btn btn-sm pull-right" data-toggle="tooltip" title="{{ __('message.export') }}" style="position: absolute;right: 10px;top: 10px;">
            <i class="fas fa-paper-plane"></i>
        </button>
        <br />
         <div id="response"></div>


      <div class="card-body table-responsive" style="padding-top: 0px;">
        <div class="d-flex justify-content-end" style="padding-top: 0px;">
            <div class="custom-dropdown" id="columnUpdate">
                <button class="btn btn-default float-right" type="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false" style="position: relative;top: 32px;">
                    <span class="fa fa-columns"></span>&nbsp;&nbsp;{{ __('message.selected_columns') }}&nbsp;&nbsp;<span class="fas fa-caret-down"></span>
                </button>
                <div class="dropdown-menu" aria-labelledby="dropdownMenuButton">
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" value="Order" id="OrderCheckbox">
                        <label class="form-check-label" for="Order">{{ __('message.order') }}</label>
                    </div>
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" value="name" id="nameCheckbox">
                        <label class="form-check-label" for="name">{{ __('message.name_page') }}</label>
                    </div>
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" value="email" id="emailCheckbox">
                        <label class="form-check-label" for="email">{{ __('message.email') }}</label>
                    </div>
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" value="mobile" id="mobileCheckbox">
                        <label class="form-check-label" for="mobile">{{ __('message.mobile') }}</label>
                    </div>
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" value="country" id="countryCheckbox">
                        <label class="form-check-label" for="country">{{ __('message.country') }}</label>
                    </div>
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" value="Expiry day" id="Expiry dayCheckbox">
                        <label class="form-check-label" for="Expiry day">{{ __('message.expiry_date') }}</label>
                    </div>
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" value="Deletion day" id="Deletion dayCheckbox">
                        <label class="form-check-label" for="Deletion day">{{ __('message.deletion_day') }}</label>
                    </div>
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" value="plan" id="planCheckbox">
                        <label class="form-check-label" for="plan">{{ __('message.plan_status') }}</label>
                    </div>
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" value="tenants" id="tenantsCheckbox">
                        <label class="form-check-label" for="tenants">{{ __('message.tenants') }}</label>
                    </div>
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" value="domain" id="domainCheckbox">
                        <label class="form-check-label" for="domain">{{ __('message.admin_domain') }}</label>
                    </div>
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" value="db_name" id="db_nameCheckbox">
                        <label class="form-check-label" for="db_name">{{ __('message.db_name') }}</label>
                    </div>
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" value="db_username" id="db_usernameCheckbox">
                        <label class="form-check-label" for="db_username">{{ __('message.db_username') }}</label>
                    </div>
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" value="action" id="actionCheckbox">
                        <label class="form-check-label" for="action">{{ __('message.action') }}</label>
                    </div>
                    <br>
                    <button type="button" class="btn btn-primary btn-sm" style="position: relative; left: 20px; padding: 4px;" id="saveColumnsBtn">{{ __('message.apply') }}</button>
                </div>
            </div>

        </div>

        <div style="position: relative;">
            <table id="tenant-table" class="table display" cellspacing="0" width="100%" styleClass="borderless">
                <thead>
                    <tr>
                        <th>{{ __('message.order') }}</th>
                        <th>{{ __('message.user') }}</th>
                        <th>{{ __('message.email') }}</th>
                        <th>{{ __('message.mobile') }}</th>
                        <th>{{ __('message.country') }}</th>
                        <th>{{ __('message.expiry_date') }}</th>
                        <th>{{ __('message.deletion_day') }}</th>
                        <th>{{ __('message.plan_status') }}</th>
                        <th>{{ __('message.tenant') }}</th>
                        <th>{{ __('message.admin_domain') }}</th>
                        <th>{{ __('message.db_name') }}</th>
                        <th>{{ __('message.db_username') }}</th>
                        <th>{{ __('message.action') }}</th>
                    </tr>
                </thead>
            </table>
        </div>
    </div>
</div>

    @endif

    <link rel="stylesheet" href="https://cdn.datatables.net/1.10.21/css/jquery.dataTables.min.css">
    <script src="https://cdn.datatables.net/1.10.21/js/jquery.dataTables.min.js"></script>
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.7.1/dist/leaflet.css" />
    <script src="https://unpkg.com/leaflet@1.7.1/dist/leaflet.js"></script>

          <script>
                 $(document).ready(function(){
    $('[data-toggle="tooltip"]').tooltip();
});


        $(document).ready(function () {
            // Initialize DataTable
            var tenatTable = $('#tenant-table').DataTable({
                processing: true,
                serverSide: true,
                stateSave: false,
                order: [[0, "desc"]],
                "scrollX": true,
               "scrollCollapse": true,
                ajax: {
                    "url": '{!! route('get-tenants') !!}',
                    error: function (xhr) {
                        if (xhr.status == 401) {
                            alert('{{ __('message.session_expired') }}');
                            window.location.href = '/login';
                        }
                    },
                     dataFilter: function(data) {
                    var json = jQuery.parseJSON(data);
                    if (json.data.length === 0) {
                        $('#tenat_export-report-btn').hide(); // Hide export button
                    } else {
                        $('#tenat_export-report-btn').show(); // Show export button
                    }
                    return data;
                }
                },
                "oLanguage": {
                    "sLengthMenu": "_MENU_ Records per page",
                   "sSearch": "<span style='position: relative;right: 175px;'>{{ __('message.search') }}:</span> ",

                    "sProcessing": ' <div class="overlay"><i class="fas fa-3x fa-sync-alt fa-spin"></i><div class="text-bold pt-2">{{ __('message.loading') }}</div></div>'
                },
                columnDefs: [
                    { orderable: false, targets: 4 }
                ],
                columns: [
                    { data: 'Order', name: 'Order' },
                    {data: 'name', name: 'name'},
                    { data: 'email', name: 'email' },
                    { data: 'mobile', name: 'mobile' },
                    { data: 'country', name: 'country' },
                    { data: 'Expiry day', name: 'Expiry day' },
                    { data: 'Deletion day', name: 'Deletion day' },
                    { data: 'plan', name: 'plan' },
                    { data: 'tenants', name: 'tenants' },
                    { data: 'domain', name: 'domain' },
                    { data: 'db_name', name: 'db_name' },
                    { data: 'db_username', name: 'db_username' },
                    { data: 'action', name: 'action' },
                ],
                "fnDrawCallback": function (oSettings) {
                    $('.loader').css('display', 'none');
                },
                "fnPreDrawCallback": function (oSettings, json) {
                    $('.loader').css('display', 'block');
                },
            });


    $('#saveColumnsBtn').click(function() {
        // Get selected columns
        var selectedColumns = [];
        $('input[type="checkbox"]:checked').each(function() {
            selectedColumns.push($(this).val());
        });
         if (selectedColumns.length === 0) {
        alert('{{ __('message.select_checkbox') }}');
        return;
        }

        $.ajax({
            url: '{{ route('save-columns') }}',
            method: 'POST',
            data: {
                selected_columns: selectedColumns,
                entity_type: 'tenats',
                _token: '{{ csrf_token() }}'
            },
            success: function(response) {
                console.log(response.message);
            },
            error: function(xhr) {
                console.log('Failed to save column preferences');
            }
        });

        tenatTable.columns().every(function() {
            var column = this;
            if (selectedColumns.includes(column.dataSrc())) {
                column.visible(true);
            } else {
                column.visible(false);
            }
        });
        // tenatTable.draw();
    });

   $(document).ready(function() {
        $.ajax({
            url: '{{ route('get-columns') }}',
            method: 'GET',
            data: {
                entity_type: 'tenats'
            },
            success: function(response) {
                var selectedColumns = response.selected_columns;
                tenatTable.columns().every(function() {
                    var column = this;
                    if (selectedColumns.includes(column.dataSrc())) {
                        column.visible(true);
                    } else {
                        column.visible(false);
                    }
                });

                $('input[type="checkbox"]').each(function() {
                    var checkboxValue = $(this).val();
                    if (selectedColumns.includes(checkboxValue)) {
                        $(this).prop('checked', true);
                    } else {
                        $(this).prop('checked', false);
                    }
                });
            },
            error: function(xhr) {
                console.error('Failed to load column preferences.');
            }
        });
    });

        $('#tenat_export-report-btn').click(function() {
            $(this).prop('disabled', true);

            var selectedColumns = [];
            $('input[type="checkbox"]:checked').each(function() {
                selectedColumns.push($(this).val());
            });

            var urlParams = new URLSearchParams(window.location.search);
            var searchParams = {};
            for (const [key, value] of urlParams) {
                searchParams[key] = value;
            }
             var loadingElement = document.getElementById("tenatloading");
            loadingElement.style.display = "flex";
            $.ajax({
                url: '{{ url("export-tenats") }}',
                method: 'GET',
                data: {
                    selected_columns: selectedColumns,
                    search_params: searchParams
                },
                    success: function(response, status, xhr) {
                        var result = '<div class="alert alert-success">' +
                            '<button type="button" class="close" data-dismiss="alert" aria-label="Close">' +
                            '<span aria-hidden="true">&times;</span></button>' +
                            '<strong><i class="far fa-thumbs-up"></i> ' + @json(__('message.well_done')) + ' </strong>' +
                            response.message + '!</div>';

                    $('#export-message').html(result).removeClass('text-danger').addClass('text-success');
                    setTimeout(function() {
                        location.reload();
                    }, 5000);
                },
                error: function(xhr, status, error) {
                    console.log(error);
                    var result = '<div class="alert alert-danger">' +
                        '<button type="button" class="close" data-dismiss="alert" aria-label="Close">' +
                        '<span aria-hidden="true">&times;</span></button>' +
                        '<strong><i class="far fa-thumbs-down"></i> Oops! </strong>' +
                        'Export failed: ' + xhr.responseJSON.message + '</div>';

                    $('#export-message').html(result).removeClass('text-success').addClass('text-danger');
                    setTimeout(function() {
                        location.reload();
                    }, 5000);
                },
                 complete: function () {
                        loadingElement.style.display = "none";
                    }

            });
        });


        });


        function deleteTenant(id, orderId = "") {
            var id = id;
            var orderId = orderId;
            if (confirm("Are you sure you want to destroy this tenant?")) {
                var loadingElement = document.getElementById("loading");
                loadingElement.style.display = "flex";
                $.ajax({
                    url: "{!! url('delete-tenant') !!}",
                    method: "delete",
                    data: { 'id': id, 'orderId': orderId },
                    success: function (data) {
                        if (data.success === true) {
                            console.log(data.message);
                            var result = '<div class="alert alert-success alert-dismissable"><button type="button" class="close" data-dismiss="alert" aria-label="{{ __('message.close') }}"><span aria-hidden="true">&times;</span></button><strong><i class="fa fa-check"></i>{{ __('message.success') }}! </strong>' + data.message + '!</div>';
                            $('#successmsg').show();
                            $('#error').hide();
                            $('#successmsg').html(result);
                            setInterval(function () {
                                $('#successmsg').slideUp(5000);
                                location.reload();
                            }, 3000);
                        } else if (data.success === false) {
                            $('#successmsg').hide();
                            $('#error').show();
                            var result = '<div class="alert alert-danger alert-dismissable"><button type="button" class="close" data-dismiss="alert" aria-label="{{ __('message.close') }}"><span aria-hidden="true">&times;</span></button><strong><i class="fa fa-ban"></i>{{ __('message.whoops') }} </strong> {{ __('message.something_wrong') }}<br>' + data.message + '!</div>';
                            $('#error').html(result);
                            setInterval(function () {
                                $('#error').slideUp(5000);
                                location.reload();
                            }, 10000);
                        }
                    },
                    error: function (data) {
                        $('#successmsg').hide();
                        $('#error').show();
                        var result = '<div class="alert alert-danger alert-dismissable"><button type="button" class="close" data-dismiss="alert" aria-label="{{ __('message.close') }}"><span aria-hidden="true">&times;</span></button><strong><i class="fa fa-ban"></i>{{ __('message.whoops') }} </strong> {{ __('message.something_wrong') }}<br>' + data.responseJSON.message + '!</div>';
                        $('#error').html(result);
                        setInterval(function () {
                            $('#error').slideUp(5000);
                            location.reload();
                        }, 10000);
                    },
                    complete: function () {
                        loadingElement.style.display = "none"; // Hide the loading indicator
                    }
                });
            }
        }
    </script>

    <script>
        $(document).ready(function () {
            var map = L.map('map', {
                minZoom: 2, // Set the minimum zoom level to 2
            }).setView([0, 0], 2); // Initialize the map with a center and zoom level.

            // Add an OpenStreetMap tile layer.
            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                maxZoom: 19,
                attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">{{ __('message.openstreetmap') }}</a> {{ __('message.contributors') }}'
            }).addTo(map);

            // Define an array of region coordinates
            var regions = @json($regions);

            regions.forEach(function (region) {
                var regionLatLng = [region.latitude, region.longitude];
                var marker = L.marker(regionLatLng).addTo(map).bindPopup(region.name);

                var clickCount = 0;

                marker.on('click', function () {
                    clickCount++;

                    if (clickCount === 2) {
                        clickCount = 0;

                        removeLocation(region.name);
                    }
                });

                marker.on('tooltipclose', function () {
                    clickCount = 0;
                });

            });
        });

        // Function to remove a location
        function removeLocation(locationId) {
            // Send AJAX request to remove location
            $.ajax({
                url: "{{ route('remove-location') }}",
                type: 'DELETE',
                data: { location_id: locationId },
                success: function (data) {
                    // Update the map to remove the pin
                    map.eachLayer(function (layer) {
                        if (layer.options && layer.options.locationId === locationId) {
                            map.removeLayer(layer);
                        }
                    });

                }
            });
        }


            $(document).ready(function () {
            $('#pop-product-table').DataTable({
                processing: true,
                serverSide: true,
                stateSave: false,
                order: [[0, "desc"]],
                ajax: {
                    url: '{{ route("fetch-data") }}', // Replace with your actual data source URL
                    type: 'GET', // Or 'POST' depending on your server-side implementation
                },
                language: {
                    sLengthMenu: "_MENU_ Records per page",
                    sSearch: "Search: ",
                    sProcessing: ' <div class="overlay"><i class="fas fa-3x fa-sync-alt fa-spin"></i><div class="text-bold pt-2">{{ __('message.loading') }}</div></div>'
                },
                columnDefs: [
                    { orderable: false, targets: 3 }
                ],
                columns: [
                    { data: 'Cloud Product', name: 'Cloud Product' },
                    { data: 'Cloud free plan', name: 'Cloud free plan' },
                    { data: 'Cloud product key', name: 'Cloud product key' },
                    {data: 'action', name: 'action'},
                ],
                fnDrawCallback: function (oSettings) {
                    $('.loader').css('display', 'none');
                },
                fnPreDrawCallback: function (oSettings, json) {
                    $('.loader').css('display', 'block');
                },
            });
        });

        function popProduct(id) {
            var id = id;
            if (confirm("Are you sure you want to destroy this product configuration?")) {
                var loadingElement = document.getElementById("loading");
                loadingElement.style.display = "flex";
                $.ajax({
                    url: "{!! url('delete-cloud-product') !!}",
                    method: "delete",
                    data: { 'id': id },
                    success: function (data) {
                        if (data.success === true) {
                            var result = '<div class="alert alert-success alert-dismissable"><button type="button" class="close" data-dismiss="alert" aria-label="{{ __('message.close') }}"><span aria-hidden="true">&times;</span></button><strong><i class="fa fa-check"></i>{{ __('message.success') }}! </strong>' + data.message + '!</div>';
                            $('#successmsgpop').show();
                            $('#errorpop').hide();
                            $('#successmsgpop').html(result);
                            setInterval(function () {
                                $('#successmsgpop').slideUp(5000);
                                location.reload();
                            }, 3000);
                        } else if (data.success === false) {
                            $('#successmsgpop').hide();
                            $('#errorpop').show();
                            var result = '<div class="alert alert-danger alert-dismissable"><button type="button" class="close" data-dismiss="alert" aria-label="{{ __('message.close') }}"><span aria-hidden="true">&times;</span></button><strong><i class="fa fa-ban"></i>{{ __('message.whoops') }} </strong> {{ __('message.something_wrong') }}<br>' + data.message + '!</div>';
                            $('#errorpop').html(result);
                            setInterval(function () {
                                $('#errorpop').slideUp(5000);
                                location.reload();
                            }, 10000);
                        }
                    },
                    error: function (data) {
                        $('#successmsgpop').hide();
                        $('#errorpop').show();
                        var result = '<div class="alert alert-danger alert-dismissable"><button type="button" class="close" data-dismiss="alert" aria-label="{{ __('message.close') }}"><span aria-hidden="true">&times;</span></button><strong><i class="fa fa-ban"></i>{{ __('message.whoops') }} </strong> {{ __('message.something_wrong') }}<br>' + data.responseJSON.message + '!</div>';
                        $('#errorpop').html(result);
                        setInterval(function () {
                            $('#errorpop').slideUp(5000);
                            location.reload();
                        }, 10000);
                    },
                    complete: function () {
                        loadingElement.style.display = "none"; // Hide the loading indicator
                    }
                });
            }
        }

        $(document).ready(function () {
            // Listen for changes in the country dropdown
            $('#cloud_countries').change(function () {
                var country = $(this).val();

                // Make an AJAX request to fetch states based on the selected country
                $.ajax({
                    url: "{{ url('get-state') }}/" + country + "?country_id=" + country,
                    type: 'GET',
                    success: function (data) {
                        console.log(data);
                        // Update the state dropdown with the retrieved states
                        $('#cloud_state').empty();
                            $('#cloud_state').append(data);
                    },
                    error: function (xhr, status, error) {
                        console.error(error);
                    }
                });
            });
        });

        $(document).ready(function () {
            // Add an id to the country dropdown
            $(function () {
                //Initialize Select2 Elements
                $('.select2').select2()
            });
        });


    </script>
    <script>
        $('ul.nav-sidebar a').filter(function() {
            return this.id == 'setting';
        }).addClass('active');

        // for treeview
        $('ul.nav-treeview a').filter(function() {
            return this.id == 'setting';
        }).parentsUntil(".nav-sidebar > .nav-treeview").addClass('menu-open').prev('a').addClass('active');
    </script>

@stop

