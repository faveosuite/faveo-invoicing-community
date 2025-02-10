@extends('themes.default1.layouts.master')
@section('title')
Orders
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
    0% { transform: rotate(0deg); }
    100% { transform: rotate(360deg); }
    }


        #order-table_wrapper {
            overflow-x: auto; 
        }

        #order-table .dataTables_scrollBody {
            overflow-y: hidden; 
        }

        #order-table .dataTables_scrollHeadInner,
        #order-table .dataTables_scrollFootInner {
            overflow: auto;
        }

        #order-table .dataTables_scrollHeadInner table,
        #order-table .dataTables_scrollFootInner table {
            width: auto; 
        }

        #order-table .dataTables_scrollHeadInner,
        #order-table .dataTables_scrollFootInner,
        #order-table .dataTables_scrollBody {
            margin-right: 0 !important;
        }
        #order-table th,
        #order-table td {
            word-break: initial;
        }
        
        .custom-dropdown {
            position: relative;
            z-index: 1050;
        }
        
        
        .custom-dropdown .form-check {
            padding-right: 60px; 
            position: relative;
            right: -15px; 
        }
        
        .dropdown-menu {
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            position: absolute;
            max-height: 150px; 
            overflow-y: auto; 
            overflow-x: hidden;
            width: max-content; 
        }
        
        #order_export-report-btn,
        .custom-dropdown {
            z-index: 1000;
        }
        
        #order_export-report-btn {
            position: absolute;
            right: 20px;
            top: 20px;
        }
        
        .card-body.table-responsive {
            position: relative;
            overflow: hidden;
        }
        
        .d-flex.justify-content-between {
            margin-bottom: 1rem;
        }
        
        #order-table_wrapper input[type="search"]  {
            position: relative;
            right: 130px;
        }
        
        .dataTables_filter {
            position: relative;
            z-index: 1;
            right: 50px; 
        }

</style>
    <div class="col-sm-6">
        <h1>{{ __('message.view_all_orders') }}</h1>
    </div>
    <div class="col-sm-6">
        <ol class="breadcrumb float-sm-right">
            <li class="breadcrumb-item"><a href="{{url('/')}}"><i class="fa fa-dashboard"></i> {{ __('message.home') }}</a></li>
            <li class="breadcrumb-item active">{{ __('message.all-orders') }}</li>
        </ol>
    </div><!-- /.col -->
@stop
@section('content')
<div id="export-message"></div>
<div class="row">
    <div class="col-12">
        <div class="card card-secondary card-outline collapsed-card">
    <div class="card-header">
        <h3 class="card-title">{{ __('message.advance_search') }}</h3>

        <div class="card-tools">

             <button type="button" class="btn btn-tool" id="tip-search" title="{{ __('message.expand') }}"> <i id="search-icon" class="fas fa-plus"></i>
                            </button>
            
        </div>
    </div>
    <!-- /.box-header -->
      <div class="card-body" id="advance-search" style="display:none;">
        {!! Form::open(['method'=>'get']) !!}

        <div class="row">

            <div class="col-md-3 form-group">
                <!-- first name -->
                {!! Form::label('order_no','Order No:') !!}
                {!! Form::text('order_no',$request->order_no,['class' => 'form-control','id'=>'order_no']) !!}

            </div>
            <div class="col-md-3 form-group">
                <!-- first name -->
                {!! Form::label('product_id','Product') !!} <br>
                {!! Form::select('product_id',[null => 'Choose']+ $paidUnpaidOptions + $products, $request->product_id, ['class' => 'form-control select2','style'=>'width:100%','id'=>'product_id','onChange'=>'getProductVersion(this.value)']) !!}
            </div>
            

            
            <div class="col-md-3 form-group">
                <!-- first name -->
                {!! Form::label('from','From') !!}
                <div class="input-group date" id="order_from" data-target-input="nearest">
                    <input type="text" name="from" class="form-control datetimepicker-input" autocomplete="off" value="{!! $request->from !!}" data-target="#order_from"/>


                    <div class="input-group-append" data-target="#order_from" data-toggle="datetimepicker">
                        <div class="input-group-text"><i class="fa fa-calendar"></i></div>
                    </div>

                </div>


            </div>
            <div class="col-md-3 form-group">
                <!-- first name -->
                {!! Form::label('till','To') !!}
                <div class="input-group date" id="order_till" data-target-input="nearest">
                    <input type="text" name="till" class="form-control datetimepicker-input" autocomplete="off" value="{!! $request->till !!}" data-target="#order_till"/>


                    <div class="input-group-append" data-target="#order_till" data-toggle="datetimepicker">
                        <div class="input-group-text"><i class="fa fa-calendar"></i></div>
                    </div>

                </div>


            </div>
            <div class="col-md-3 form-group">
                <!-- first name -->
                {!! Form::label('domain','Domain') !!}
                {!! Form::text('domain',$request->domain,['class' => 'form-control','id'=>'domain']) !!}

            </div>

            <div class="col-md-3 form-group">
                <!-- first name -->
                {!! Form::label('act_inst','Installations') !!}
                {!! Form::select('act_inst',[null => 'Choose']+ $insNotIns + $activeInstallationOptions + $inactiveInstallationOptions, $request->act_inst, ['class' => 'form-control','id'=>'act_inst']) !!}
            </div>

           

            <div class="col-md-3 form-group">
                <!-- first name -->
                {!! Form::label('renewal','Subscriptions') !!}
                {!! Form::select('renewal',[null => 'Choose']+ $renewal, $request->renewal, ['class' => 'form-control','id'=>'renewal']) !!}
            </div>



        {!! Form::hidden('select', $request->version, ['class' => 'form-control', 'id' => 'select']) !!}
        <div class="col-md-3 form-group {{ $errors->has('state') ? 'has-error' : '' }}">
            <!-- name -->
            {!! Form::label('version', 'Version') !!}
            <select name="version" id="version-list" class="form-control">
        
                    <option value="">{{ __('message.choose_product') }}</option>
            </select>
        </div>

                </div>

           <div class='row'>
                <div class="col-md-6">
                      <button name="Search" type="submit"  class="btn btn-secondary"><i class="fa fa-search"></i>&nbsp;{!!Lang::get('message.search')!!}</button>
                      &nbsp;
                    <!-- <a class="btn btn-secondary" href="{!! url('/orders') !!}"><i class="fas fa-sync-alt"></i>&nbsp;{!!Lang::get('message.reset')!!}</a> -->
                    {!! Form::submit('Reset',['class'=>'btn btn-secondary','id'=>'reset']) !!}
                </div>
        </div>
    </div>
</div>
    </div>
</div>
    <div class="card card-secondary card-outline">
    <div class="card-header">
        <h3 class="card-title">{{ __('message.orders') }}</h3>
        <div class="card-tools">
            <button type="button" id="order_export-report-btn" class="btn btn-sm pull-right" data-toggle="tooltip" title="{{ __('message.export') }}" style="position: relative;top: 0px;left: -1px;">
                <i class="fas fa-paper-plane"></i>
            </button>
            <a href="{{url('invoice/generate')}}" class="btn btn-sm pull-right" data-toggle="tooltip" title="{{ __('message.create-invoice') }}" style="position: relative; right: 0px;">
                <span class="fas fa-plus"></span>
            </a>
        </div>

    </div>
        <div id="response"></div>

    <div class="card-body table-responsive">

        
                <div class="d-flex justify-content-between mb-3">
                         <button value="" class="btn btn-secondary btn-sm btn-alldell" id="bulk_delete" style="padding-top: 10px;">
                        <i class="fa fa-trash"></i>&nbsp;&nbsp;{{ __('message.delmultiple') }}
                    </button>
                    <div class="custom-dropdown" id="columnUpdate">
                        <button class="btn btn-default" type="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false" style="position: relative;top: 53px;">
                            <span class="fa fa-columns"></span>&nbsp;&nbsp;{{ __('message.selected_columns') }}&nbsp;&nbsp;<span class="fas fa-caret-down"></span>
                        </button>
                        <div class="dropdown-menu" aria-labelledby="dropdownMenuButton">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" value="client" id="clientCheckbox">
                                <label class="form-check-label" for="clientCheckbox">{{ __('message.user') }}</label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" value="email" id="emailCheckbox">
                                <label class="form-check-label" for="emailCheckbox">{{ __('message.email') }}</label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" value="mobile" id="mobileCheckbox">
                                <label class="form-check-label" for="mobileCheckbox">{{ __('message.mobile') }}</label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" value="country" id="countryCheckbox">
                                <label class="form-check-label" for="countryCheckbox">{{ __('message.country') }}</label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" value="number" id="numberCheckbox">
                                <label class="form-check-label" for="numberCheckbox">{{ __('message.order_no') }}</label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" value="status" id="statusCheckbox">
                                <label class="form-check-label" for="statusCheckbox">{{ __('message.order-status') }}</label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" value="product_name" id="productCheckbox">
                                <label class="form-check-label" for="productCheckbox">{{ __('message.product') }}</label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" value="plan_name" id="planCheckbox">
                                <label class="form-check-label" for="planCheckbox">{{ __('message.plan') }}</label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" value="version" id="versionCheckbox">
                                <label class="form-check-label" for="versionCheckbox">{{ __('message.version') }}</label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" value="agents" id="agentsCheckbox">
                                <label class="form-check-label" for="agentsCheckbox">{{ __('message.agents') }}</label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" value="order_status" id="inorder_statusCheckbox">
                                <label class="form-check-label" for="inorder_statusCheckbox">{{ __('message.status') }}</label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" value="order_date" id="inorder_dateCheckbox">
                                <label class="form-check-label" for="inorder_dateCheckbox">{{ __('message.order_date') }}</label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" value="update_ends_at" id="inupdate_ends_atCheckbox">
                                <label class="form-check-label" for="inupdate_ends_atCheckbox">{{ __('message.expiry') }}</label>
                            </div>
                            <br>
                            <button type="button" class="btn btn-primary btn-sm" style="left: 10px; position: relative;" id="saveColumnsBtn">{{ __('message.apply') }}</button>
                        </div>
                    </div>
                </div>

                <div id="loading" style="display: none;">
                    <div class="spinner"></div>
                </div>
                
                <table id="order-table" class="table display" cellspacing="0" width="100%" styleClass="borderless">
              
                    <thead>
                        <tr>
                            <th class="no-sort"><input type="checkbox" name="select_all" onchange="checking(this)"></th>
                            <th>{{ __('message.user') }}</th>
                            <th>{{ __('message.email') }}</th>
                            <th>{{ __('message.mobile') }}</th>
                            <th>{{ __('message.country') }}</th>
                            <th>{{ __('message.order_no') }}</th>
                            <th>{{ __('message.order-status') }}</th>
                            <th>{{ __('message.product') }}</th>
                            <th>{{ __('message.plan') }}</th>
                            <th>{{ __('message.version') }}</th>
                            <th>{{ __('message.agents') }}</th>
                            <th>{{ __('message.status') }}</th>
                            <th>{{ __('message.order_date') }}</th>
                            <th>{{ __('message.expiry') }}</th>
                            <th>{{ __('message.action') }}</th>
                        </tr>
                    </thead>
                </table>
          
        </div>
</div>

   <script>
       $('ul.nav-sidebar a').filter(function() {
        return this.id == 'all_order';
    }).addClass('active');

    // for treeview
    $('ul.nav-treeview a').filter(function() {
        return this.id == 'all_order';
    }).parentsUntil(".nav-sidebar > .nav-treeview").addClass('menu-open').prev('a').addClass('active');
    </script>
<link rel="stylesheet" href="https://cdn.datatables.net/1.10.22/css/jquery.dataTables.min.css">

<script src="https://cdn.datatables.net/1.10.22/js/jquery.dataTables.min.js"></script>

<script type="text/javascript">
    $(document).ready(function() {
        val = $('#product_id').val();
         if(val == '') {
                $('#version-list').val('Choose a product');
            } else {
                getProductVersion(val);
            }
        

        $(function () {
            //Initialize Select2 Elements
            $('.select2').select2()
        });
    });

           $(document).ready(function() {
            var orderTable = $('#order-table').DataTable({
            processing: true,
            serverSide: true,
            stateSave: false, 
            order: [[1, "asc"]], 
            "scrollX": true,
            "scrollCollapse": true,


            ajax: {
              "url": '{!! route('get-orders', "order_no=$request->order_no&product_id=$request->product_id&expiry=$request->expiry&expiryTill=$request->expiryTill&from=$request->from&till=$request->till&sub_from=$request->sub_from&sub_till=$request->sub_till&ins_not_ins=$request->ins_not_ins&domain=$request->domain&p_un=$request->p_un&act_ins=$request->act_inst&renewal=$request->renewal&inact_ins=$request->inact_inst&version=$request->version") !!}',
              error: function(xhr) {
                if (xhr.status == 401) {
                  alert('{{ __('message.session_expired') }}')
                  window.location.href = '/login';
                }
              },
                dataFilter: function(data) {
                    var json = jQuery.parseJSON(data);
                    if (json.data.length === 0) {
                        $('#order_export-report-btn').hide(); // Hide export button
                    } else {
                        $('#order_export-report-btn').show(); // Show export button
                    }
                    return data;
                }
            },
            columnDefs: [
              {
                targets: 'no-sort',
                orderable: false,
                order: []
              }
            ],

            "oLanguage": {
              "sLengthMenu": "_MENU_ Records per page",
              "sSearch": "<span style='position: relative;right: 140px;'>{!! __('message.search') !!}:</span> ",
              "sProcessing": ' <div class="overlay"><i class="fas fa-3x fa-sync-alt fa-spin"></i><div class="text-bold pt-2">{!! __('message.loading') !!}</div></div>'
            },
            columns: [
                {data: 'checkbox', name: 'checkbox'},
                {data: 'client', name: 'client'},
                {data: 'email', name: 'email' },
                {data: 'mobile', name: 'mobile' },
                {data: 'country', name: 'country' },
                {data: 'number', name: 'number'},
                {data: 'status', name: 'status'},
                {data: 'product_name', name: 'product_name'},
                {data: 'plan_name', name: 'plan_name'},
                {data: 'version', name: 'version'},
                {data: 'agents', name: 'agents'},
                {data: 'order_status', name: 'order_status'},
                {data: 'order_date', name: 'order_date'},
                {data: 'update_ends_at', name: 'update_ends_at'},
                {data: 'action', name: 'action'}
            ],

    

            "fnDrawCallback": function(oSettings) {
              $('[data-toggle="tooltip"]').tooltip({
                container: 'body'
              });
              $('.loader').css('display', 'none');
              var urlParams = new URLSearchParams(window.location.search);
              var hasSearchParams = urlParams.has('order_no') || urlParams.has('product_id') || urlParams.has('expiry') || urlParams.has('expiryTill') || urlParams.has('from') || urlParams.has('till') || urlParams.has('sub_from') || urlParams.has('sub_till') || urlParams.has('ins_not_ins') || urlParams.has('domain') || urlParams.has('p_un') || urlParams.has('act_inst') || urlParams.has('renewal') || urlParams.has('inact_ins') || urlParams.has('version');
              if (hasSearchParams) {
                $("#advance-search").css('display','block');
                $('#tip-search').attr('title', 'Collapse');
                $('#search-icon').removeClass('fa-plus').addClass('fa-minus');
              }
            },
            "fnPreDrawCallback": function(oSettings, json) {
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
        alert('{{ __('message.select_one_column') }}');
        return;
        }

        $.ajax({
            url: '{{ route('save-columns') }}',
            method: 'POST',
            data: {
                selected_columns: selectedColumns,
                entity_type: 'orders',
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

        orderTable.columns().every(function() {
            var column = this;
            if (selectedColumns.includes(column.dataSrc())) {
                column.visible(true);
            } else {
                column.visible(false);
            }
        });
        orderTable.draw();
    });

   $(document).ready(function() {
        $.ajax({
            url: '{{ route('get-columns') }}',
            method: 'GET',
            data: {
                entity_type: 'orders'
            },
            success: function(response) {
                var selectedColumns = response.selected_columns;
                orderTable.columns().every(function() {
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
        

          $('#order_export-report-btn').click(function() {
            $(this).prop('disabled', true);

            var selectedColumns = [];
            $('input[type="checkbox"]:checked').each(function() {
                selectedColumns.push($(this).val());
            });

            var urlParams = new URLSearchParams(window.location.search);
            console.log(urlParams);
            var searchParams = {};
            for (const [key, value] of urlParams) {
                searchParams[key] = value;
            }
             var loadingElement = document.getElementById("loading");
            loadingElement.style.display = "flex";
            $.ajax({
            "url": '{{ url("export-orders") }}?' +
                   'order_no={{ $request->order_no }}' +
                   '&product_id={{ $request->product_id }}' +
                   '&expiry={{ $request->expiry }}' +
                   '&expiryTill={{ $request->expiryTill }}' +
                   '&from={{ $request->from }}' +
                   '&till={{ $request->till }}' +
                   '&sub_from={{ $request->sub_from }}' +
                   '&sub_till={{ $request->sub_till }}' +
                   '&ins_not_ins={{ $request->ins_not_ins }}' +
                   '&domain={{ $request->domain }}' +
                   '&p_un={{ $request->p_un }}' +
                   '&act_ins={{ $request->act_inst }}' +
                   '&renewal={{ $request->renewal }}' +
                   '&inact_ins={{ $request->inact_inst }}' +
                   '&version={{ $request->version }}',

                method: 'GET',
                data: {
                    selected_columns: selectedColumns,
                    search_params: searchParams
                },
                success: function(response, status, xhr) {
                    var result = '<div class="alert alert-success">' +
                        '<button type="button" class="close" data-dismiss="alert" aria-label="Close">' +
                        '<span aria-hidden="true">&times;</span></button>' +
                        '<strong><i class="far fa-thumbs-up"></i> {{ __('message.well_done') }}</strong>' +
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

        function getProductVersion(val) {
        getAllVersions(val);
      }

      function getAllVersions(val) {
        selectver = $('#select').val()
        $.ajax({
            type: "GET",
              url: "{{url('get-product-versions')}}/" + val,
            data: 'select_id=' + selectver,
            success: function (data) {
                $("#version-list").html(data);
            }
        });
      }
    </script>


@stop

@section('icheck')
<script>
    function checking(e){
          
          $('#order-table').find("td input[type='checkbox']").prop('checked', $(e).prop('checked'));
     }
     

     $(document).on('click','#bulk_delete',function(){
      var id=[];
      if (confirm("Are you sure you want to delete this?"))
        {
            $('.order_checkbox:checked').each(function(){
              id.push($(this).val())
            });
            if(id.length >0)
            {
               $.ajax({
                      url:"{!! route('orders-delete') !!}",
                      method:"delete",
                      data: $('#check:checked').serialize(),
                      beforeSend: function () {
                $('#gif').show();
                },
                success: function (data) {
                $('#gif').hide();
                $('#response').html(data);
                location.reload();
                }
               })
            }
            else
            {
                alert("{{ __('message.select_checkbox') }}");
                return false;
            }
        } 
                return false;


     });
    $('#update_expiry').datetimepicker({
        format: 'L'
    });
    $('#update_expiry_till').datetimepicker({
        format: 'L'
    });
    $('#subs_from').datetimepicker({
        format: 'L'
    });
    $('#subs_till').datetimepicker({
        format: 'L'
    });
    $('#order_from').datetimepicker({
        format: 'L'
    });
    $('#order_till').datetimepicker({
        format: 'L'
    });
</script>

<script>
$(document).ready(function() {
  const initialProductValue = $('#product_id').val();

  const initialFromDateValue = $('#order_from').val();
  const initialTillDateValue = $('#order_till').val();

  $('#reset').on('click', function() {
    // Reset the input fields
    $('#order_no').val('');
    $('#from').val('');
    $('#till').val('');
    $('#domain').val('');
    $('#act_inst').val('');
    $('#renewal').val('');

    $('#product_id').val('');

    $('#version-list').val('');

    $('#order_from').datetimepicker('clear');
    $('#order_till').datetimepicker('clear');
  });
});
</script>

@stop
