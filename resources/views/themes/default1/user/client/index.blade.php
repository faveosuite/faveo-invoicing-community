@extends('themes.default1.layouts.master')
@section('title')
Users
@stop


@section('content-header')
    <div class="col-sm-6">
        <h1>All Users</h1>
    </div>
    <div class="col-sm-6">
        <ol class="breadcrumb float-sm-right">
            <li class="breadcrumb-item"><a href="{{url('/')}}"><i class="fa fa-dashboard"></i> Home</a></li>
            <li class="breadcrumb-item active">All Users</li>
        </ol>
    </div><!-- /.col -->
    <style type="text/css">

        .custom-dropdown .form-check {
            padding-right: 60px;
            position: relative;
            right: -15px;
        }
          .dropdown-menu {
            position: absolute;
            max-height: 210px;
            overflow-y: hidden;
            overflow-x: hidden;
        }

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
        0% { transform: rotate(0deg); }
        100% { transform: rotate(360deg); }
       }

        .table-responsive {
            position: relative; 
        }
        
        .custom-dropdown {
            z-index: 1050; 
        }
        
        .btn-alldell, #export-report-btn {
            z-index: 1000; 
        }
        
        .d-flex.justify-content-between {
            margin-bottom: 1rem;
        }
        
        .dropdown-menu {
            z-index: 1050; 
        }
        
        #user-table_wrapper input[type="search"] {
            position: relative;
            right: 180px;
        }
        
        .dataTables_filter {
            position: relative;
            z-index: 1;
        }

     </style>
@stop
    
@section('content')
<div id="export-message"></div>

    <div class="row">
        <div class="col-12">
            <!-- Default box -->
            <div class="card card-secondary card-outline collapsed-card">
                <div class="card-header">
                    <h3 class="card-title">Advance Search</h3>

                    <div class="card-tools">
                        <button type="button" class="btn btn-tool" id="tip-search" title="Expand"> <i id="search-icon" class="fas fa-plus"></i>
                            </button>
                       
                    </div>
                </div>
                <div class="card-body" id="advance-search" style="display:none;">
                    {!! html()->form('get')->open() !!}

                    <div class="row">

                        <div class="col-md-3 form-group">
                            <!-- first name -->
                            {!! html()->label('Company Name', 'company') !!}
                            {!! html()->text('company', $request->company)->class('form-control')->id('company') !!}

                        </div>
                        <?php
                        $countries=DB::table('countries')->pluck('nicename','country_code_char2')->toarray();
                        ?>
                        <div class="col-md-3 form-group">
                            <!-- first name -->
                            {!! html()->label('Country', 'country') !!}<br>
                            <select style="width:100%;" name="country" value= "Choose" id="country"  class="form-control select2" data-live-search="true" data-live-search-placeholder="Search" data-dropup-auto="false" data-size="10">
                                <option value="" style="">Choose</option>
                                @foreach($countries as $key=> $country)
                                    @if($key == $request->country)
                                        <option value={{$key}} selected>{{$country}}</option>
                                    @else
                                        <option value={{$key}}>{{$country}}</option>
                                    @endif
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-3 form-group">
                            <!-- first name -->
                            {!! html()->label('Industries', 'industry') !!}<br>

                        <?php $old = ['agriculture_forestry'=>'Agriculture Forestry','safety_security_legal'=>'Safety Security Legal','business_information'=>'Business Information','finance_insurance'=>'Finance Insurance','gaming'=>'Gaming','real_estate_housing'=>'Real Estate Housing','health_services'=>'Health Services','education'=>'Education','food_hospitality'=>'Food Hospitality','personal_services'=>'Personal Services','transportation'=>'Transportation','construction_utilities_contracting'=>'Construction Utilities Contracting','motor_vehicle'=>'Motor Vehicle','animals_pets'=>'Animals & Pets','art_design'=>'Art & Design','auto_transport'=>'Auto & Transport','food_beverage'=>'Food & Beverage','beauty_fashion'=>'Beauty & Fashion','education_childcare'=>'Education & Childcare','environment_green_tech'=>'Environment & Green Tech','events_weddings'=>'Events & Weddings','finance_legal_consulting'=>'Finance, Legal & Consulting','government_municipal'=>'Government & Municipal','home_garden'=>'Home & Garden','internet_technology'=>'Internet & Technology','local_service_providers'=>'Local Service Providers','manufacturing_wholesale'=>'Manufacturing & Wholesale','marketing_advertising'=>'Marketing & Advertising','media_communication'=>'Media & Communication','medical_dental'=>'Medical & Dental','music_bands'=>'Music & Bands','non_profit_charity'=>'Non-Profit & Charity','real_estate'=>'Real Estate','religion'=>'Religion','retail_e-Commerce'=>'Retail & E-Commerce','sports_recreation'=>'Sports & Recreation','travel_hospitality'=>'Travel & Hospitality','other'=>'Other',];
                        $bussinesses =DB::table('bussinesses')->pluck('name','short')->toarray();
                        $acctManagers = DB::table('users')->where('position', 'account_manager')
                            ->pluck('first_name', 'id')->toArray();

                        $salesManagers = DB::table('users')->where('position', 'manager')
                            ->pluck('first_name', 'id')->toArray();
                        ?>
                        <!-- {!! html()->select('industry', array_merge(['Choose'], ['' => DB::table('bussinesses')->pluck('name', 'short')->toArray()], ['old' => $old]))->class('form-control')->id('industry')->attribute('data-live-search', 'true')->attribute('data-live-search-placeholder', 'Search')->attribute('data-dropup-auto', 'false')->attribute('data-size', '10') !!} -->

                            <select name="industry"  style="width:100%;" class="form-control select2" data-live-search="true",data-live-search-placeholder="Search" data-dropup-auto="false"  data-size="10" id="industry">
                                <option value="">Choose</option>
                                @foreach($bussinesses as $key=>$bussines)
                                    @if($key == $request->industry)
                                        <option value={{$key}} selected>{{$bussines}}</option>
                                    @else
                                        <option value={{$key}}>{{$bussines}}</option>
                                    @endif
                                @endforeach
                            </select>

                        </div>

                        <div class="col-md-3 form-group">
                            <!-- first name -->
                            {!! html()->label('Role', 'role') !!}
                            {!! html()->select('role', ['' => 'Choose'] + ['admin' => 'Admin', 'user' => 'User'], $request->role)
                                ->class('form-control')
                                ->id('role') !!}
                        </div>

                        <div class="col-md-3 form-group">
                            <!-- first name -->
                            {!! html()->label('Position', 'position') !!}
                            {!! html()->select('position', ['' => 'Choose'] + ['manager' => 'Sales Manager', 'account_manager' => 'Account Manager'], $request->position)
                                ->class('form-control')
                                ->id('position') !!}
                        </div>

                        <div class="col-md-3 form-group">
                            <!-- first name -->
                            {!! html()->label('Registered From', 'reg_from') !!}
                            <div class="input-group date" id="reservationdate_from" data-target-input="nearest">
                              <input type="text" name="reg_from" class="form-control datetimepicker-input" autocomplete="off" value="{!! $request->reg_from !!}" data-target="#reservationdate_from"/>                            
                              <div class="input-group-append" data-target="#reservationdate_from" data-toggle="datetimepicker">
                                    <div class="input-group-text"><i class="fa fa-calendar"></i></div>
                            
                            </div>

                        </div>
                        </div>

                        <div class="col-md-3 form-group">
                            <!-- first name -->
                            {!! html()->label('Registered Till', 'from') !!}
                            <div class="input-group date" id="reservationdate" data-target-input="nearest">
                                <input type="text" name="reg_till" class="form-control datetimepicker-input" autocomplete="off" value="{!! $request->reg_till !!}" data-target="#reservationdate"/>

                                <div class="input-group-append" data-target="#reservationdate" data-toggle="datetimepicker">
                                    <div class="input-group-text"><i class="fa fa-calendar"></i></div>
                                </div>

                            </div>


                        </div>

                        <div class="col-md-3 form-group">
                            <!-- first name -->
                            {!! html()->label('Users for Account Manager', 'from') !!}
                            <select name="actmanager"  class="form-control selectpicker" data-live-search="true",data-live-search-placeholder="Search" data-dropup-auto="false"  data-size="10" id="actmanager" >
                                <option value="">Choose</option>
                                @foreach($acctManagers as $key=>$acct)
                                    @if($key == $request->actmanager)
                                        <option value={{$key}} selected>{{$acct}}</option>
                                    @else
                                        <option value={{$key}}>{{$acct}}</option>
                                    @endif
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-3 form-group">
                            <!-- first name -->
                            {!! html()->label('Users for Sales Manager', 'from') !!}
                            <select name="salesmanager"  class="form-control selectpicker" data-live-search="true",data-live-search-placeholder="Search" data-dropup-auto="false"  data-size="10" id="salesmanager" >
                                <option value="">Choose</option>
                                @foreach($salesManagers as $key=>$sales)
                                    @if($key == $request->salesmanager)
                                        <option value={{$key}} selected>{{$sales}}</option>
                                    @else
                                        <option value={{$key}}>{{$sales}}</option>
                                    @endif
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-3 form-group">
                            <!-- Mobile Status -->
                            {!! html()->label('Mobile Status', 'mobile_verified') !!}
                            {!! html()->select('mobile_verified', ['' => 'Choose'] + ['1' => 'Active', '0' => 'Inactive'], $request->mobile_verified)
                                ->class('form-control')
                                ->id('mobile_verified') !!}
                        </div>

                        <div class="col-md-3 form-group">
                            <!-- Email Status -->
                            {!! html()->label('Email Status', 'active') !!}
                            {!! html()->select('active', ['' => 'Choose'] + ['1' => 'Active', '0' => 'Inactive'], $request->active)
                                ->class('form-control')
                                ->id('active') !!}
                        </div>

                        <div class="col-md-3 form-group">
                            <!-- 2FA Status -->
                            {!! html()->label('2FA Status', 'is_2fa_enabled') !!}
                            {!! html()->select('is_2fa_enabled', ['' => 'Choose'] + ['1' => 'Enabled', '0' => 'Disabled'], $request->is_2fa_enabled)
                                ->class('form-control')
                                ->id('is_2fa_enabled') !!}
                        </div>
                    </div>
                <!-- /.card-body -->
                    <button name="Search" type="submit" id="search"  class="btn btn-secondary"><i class="fa fa-search"></i>&nbsp;{!!Lang::get('Search')!!}</button>
                    &nbsp;
                    <!-- <a href="{!! url('clients') !!}" id="reset" class="btn btn-secondary"><i class="fas fa-sync-alt"></i>&nbsp;{!!Lang::get('Reset')!!}</a> -->
                    {!! html()->submit('Reset')
    ->class('btn btn-secondary')
    ->id('reset') !!}

                </div>
            <!-- /.card -->
        </div>
    </div>
</div>

<div class="card card-secondary card-outline">
    <div class="card-header">
        <h3 class="card-title">Users</h3>
        <div class="card-tools">
            
        <button type="button" id="export-report-btn" class="btn btn-sm pull-right" data-toggle="tooltip" title="Export" style="position: absolute;left: 95.5%;top: 13px;">
            <i class="fas fa-paper-plane"></i>
        </button>
            <a href="{{url('clients/create')}}" class="btn btn-sm pull-right" data-toggle="tooltip" title="Create new user" style="position: absolute;left: 97.5%;">
                <span class="fas fa-plus"></span>
            </a>
        </div>
    </div>
    
    <div id="response"></div>

    <div class="card-body table-responsive">
        <div class="d-flex justify-content-between mb-3">
            <button value="" class="btn btn-secondary btn-sm btn-alldell" id="bulk_delete">
                <i class="fa fa-trash"></i>&nbsp;&nbsp;Suspend Selected Users
            </button>
            
            <form id="columnForm">
                <div class="custom-dropdown" id="columnUpdate">
                    <button class="btn btn-default pull-right" type="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false" style="position: relative;top: 52px;">
                        <span class="fa fa-columns"></span>&nbsp;&nbsp;Select Columns&nbsp;&nbsp;<span class="fas fa-caret-down"></span>
                    </button>
                    <div class="dropdown-menu" aria-labelledby="dropdownMenuButton">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" value="name" id="nameCheckbox">
                            <label class="form-check-label" for="nameCheckbox">Name</label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" value="email" id="emailCheckbox">
                            <label class="form-check-label" for="emailCheckbox">Email</label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" value="mobile" id="mobileCheckbox">
                            <label class="form-check-label" for="mobileCheckbox">Mobile</label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" value="country" id="countryCheckbox">
                            <label class="form-check-label" for="countryCheckbox">Country</label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" value="created_at" id="RegisteredCheckbox">
                            <label class="form-check-label" for="RegisteredCheckbox">Registered on</label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" value="active" id="statusCheckbox">
                            <label class="form-check-label" for="statusCheckbox">Status</label>
                        </div>
                        <br>
                        <button type="button" class="btn btn-primary btn-sm" style="left: 10px; position: relative;" id="saveColumnsBtn">Apply</button>
                    </div>
                </div>
            </form>
        </div>

        
        <div id="loading" style="display: none;">
            <div class="spinner"></div>
        </div>

        <table id="user-table" class="table display" cellspacing="0" width="100%">
            <thead>
                <tr>
                    <th class="no-sort"><input type="checkbox" name="select_all" onchange="checking(this)"></th>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Mobile</th>
                    <th>Country</th>
                    <th>Registered on</th>
                    <th>Status</th>
                    <th>Action</th>
                </tr>
            </thead>
          
        </table>
    </div>
</div>


<div id="gif"></div>

    <link rel="stylesheet" href="https://cdn.datatables.net/1.10.22/css/jquery.dataTables.min.css">
        <!-- <script src="https://code.jquery.com/jquery-3.5.1.js"></script> -->
                <link rel="stylesheet" href="https://cdn.datatables.net/buttons/1.0.3/css/buttons.dataTables.min.css">
<script src="https://cdn.datatables.net/buttons/1.0.3/js/dataTables.buttons.min.js"></script> 

    <script src="https://cdn.datatables.net/1.10.22/js/jquery.dataTables.min.js"></script>
 <script type="text/javascript">
$(document).ready(function() {
    // Initialize DataTable
    var userTable = $('#user-table').DataTable({
        processing: true,
        serverSide: true,
        stateSave: false,
        order: [[{!! $request->sort_field ?: 5 !!}, {!! "'".$request->sort_order."'" ?: "'asc'" !!}]],
        ajax: {
            "url": '{!! route('get-clients', "company=$request->company&country=$request->country&industry=$request->industry&role=$request->role&position=$request->position&reg_from=$request->reg_from&reg_till=$request->reg_till&actmanager=$request->actmanager&salesmanager=$request->salesmanager&active=$request->active&mobile_verified=$request->mobile_verified&is_2fa_enabled=$request->is_2fa_enabled") !!}',
            error: function(xhr) {
                if (xhr.status == 401) {
                    alert('Your session has expired. Please login again to continue.');
                    window.location.href = '/login';
                }
            },
              dataFilter: function(data) {
                    var json = jQuery.parseJSON(data);
                    if (json.data.length === 0) {
                        $('#export-report-btn').hide(); // Hide export button
                    } else {
                        $('#export-report-btn').show(); // Show export button
                    }
                    return data;
                }
        },
        "oLanguage": {
            "sLengthMenu": "_MENU_ Records per page",
             "sSearch": "<span style='position: relative;right: 180px;'>Search:</span> ",
            "sProcessing": ' <div class="overlay dataTables_processing"><i class="fas fa-3x fa-sync-alt fa-spin" style=" margin-top: -25px;"></i><div class="text-bold pt-2">Loading...</div></div>'
        },
        columnDefs: [
            {
                targets: 'no-sort',
                orderable: false,
                order: []
            }
        ],
        columns: [
            { data: 'checkbox', name: 'checkbox',visible: true },
            { data: 'name', name: 'name' },
            { data: 'email', name: 'email' },
            { data: 'mobile', name: 'mobile' },
            { data: 'country', name: 'country' },
            { data: 'created_at', name: 'created_at' },
            { data: 'active', name: 'active' },
            { data: 'action', name: 'action' }
        ],
        "fnDrawCallback": function(oSettings) {
            $(function() {
                $('[data-toggle="tooltip"]').tooltip({
                    container: 'body'
                });
            });
            $('.loader').css('display', 'none');
        },
        "fnPreDrawCallback": function(oSettings) {
            var urlParams = new URLSearchParams(window.location.search);
            var hasSearchParams = urlParams.has('company') || urlParams.has('country') || urlParams.has('industry') || urlParams.has('reg_from') || urlParams.has('reg_till');
            if (hasSearchParams) {
                $("#advance-search").css('display','block');
                $('#tip-search').attr('title', 'Collapse');
                $('#search-icon').removeClass('fa-plus').addClass('fa-minus');
            } else {
                $("#advance-search").collapse("hide");
            }

            $('.loader').css('display', 'block');
        }
    });

    $('#saveColumnsBtn').click(function() {
        // Get selected columns
        var selectedColumns = [];
        $('input[type="checkbox"]:checked').each(function() {
            selectedColumns.push($(this).val());
        });
         if (selectedColumns.length === 0) {
        alert('Please select at least one column.');
        return;
        }

        $.ajax({
            url: '{{ route('save-columns') }}',
            method: 'POST',
            data: {
                selected_columns: selectedColumns,
                entity_type: 'users',
                _token: '{{ csrf_token() }}'
            },
            success: function(response) {
                location.reload();
                console.log(response.message);
            },
            error: function(xhr) {
                console.log('Failed to save column preferences');
            }
        });

        userTable.columns().every(function() {
            var column = this;
            if (selectedColumns.includes(column.dataSrc())) {
                column.visible(true);
            } else {
                column.visible(false);
            }
        });
        userTable.draw();
    });

   $(document).ready(function() {
        $.ajax({
            url: '{{ route('get-columns') }}',
            method: 'GET',
            data: {
                entity_type: 'users'
            },
            success: function(response) {
                var selectedColumns = response.selected_columns;
                userTable.columns().every(function() {
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

    // Export button click event
    $('#export-report-btn').click(function() {
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
         var loadingElement = document.getElementById("loading");
            loadingElement.style.display = "flex";
        $.ajax({
            url: '{{ url("export-users") }}',
            method: 'GET',
            data: {
                selected_columns: selectedColumns,
                search_params: searchParams
            },
                success: function(response, status, xhr) {
                var result = '<div class="alert alert-success">' +
                    '<button type="button" class="close" data-dismiss="alert" aria-label="Close">' +
                    '<span aria-hidden="true">&times;</span></button>' +
                    '<strong><i class="far fa-thumbs-up"></i> Well Done! </strong>' +
                    response.message + '!</div>';
                
                $('#export-message').html(result).removeClass('text-danger').addClass('text-success');
                setTimeout(function() {
                    location.reload();
                }, 5000);
            },
            error: function(xhr, status, error) {
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
</script>




@stop

@section('icheck')
<script>
    $(document).ready(function() {
        $(function () {
            //Initialize Select2 Elements
            $('.select2').select2()
        });
    });
   function checking(e){

          $('#user-table').find("td input[type='checkbox']").prop('checked', $(e).prop('checked'));
     }


     $(document).on('click','#bulk_delete',function(){
      var id=[];
      if (confirm("Are you sure you want to suspend this user?"))
        {
            $('.user_checkbox:checked').each(function(){
              id.push($(this).val())
            });
            if(id.length >0)
            {
               $.ajax({
                      url:"{!! Url('clients-delete') !!}",
                      method:"delete",
                      data: $('#check:checked').serialize(),
                      beforeSend: function () {
                     '<div class="overlay"><i class="fas fa-3x fa-sync-alt fa-spin"></i><div class="text-bold pt-2">Loading...</div></div>'
                },
                success: function (data) {
                $('#gif').html('');
                $('#response').html(data);
                 setTimeout(function(){
                    window.location.reload();
                },5000);
                }
               })
            }
            else
            {
                alert("Please select at least one checkbox");
                return false;
            }
        }
        return false;

     });
 </script>
 @stop
 @section('datepicker')
 <script type="text/javascript">

   $('#reservationdate').datetimepicker({
       format: 'L'

   });
        $('#reservationdate_from').datetimepicker({
      format: 'L'

    });
</script>

<script type="text/javascript">
    $(document).ready(function () {
        $('#reset').on('click', function () {
            $('#country').val('');
            $('#company').val('');
             const initialFromDateValue = $('#reservationdate_from').val();
              const initialTillDateValue = $('#reservationdate').val();

            $('#industry').val('').trigger('change');
            $('#role').val('').trigger('change');
            $('#position').val('').trigger('change');
          
            $('#actmanager').val('').trigger('change');
            $('#salesmanager').val('').trigger('change');

            $('#mobile_verified').val('').trigger('change');
            $('#active').val('').trigger('change');
            $('#is_2fa_enabled').val('').trigger('change');

             $('#reservationdate_from').datetimepicker('clear');
             $('#reservationdate').datetimepicker('clear');
        });
    });
</script>






@stop