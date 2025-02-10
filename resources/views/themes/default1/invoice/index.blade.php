@extends('themes.default1.layouts.master')
@section('title')
Invoices
@stop
@section('content-header')
<style type="text/css">
#loader {
    display: flex;
    justify-content: center;
    align-items: center;
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(0, 0, 0, 0.3);
    z-index: 9999;
}

#loader i.fa-spinner {
    font-size: 48px;
}

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
    }
    
    #invoice_export-report-btn,
    .custom-dropdown {
        z-index: 1000;
    }
    
    #invoice_export-report-btn {
        position: absolute;
        right: 20px;
        top: 20px;
    }
    
    .card-body.table-responsive {
        position: relative;
        overflow: hidden;
        padding-top: 0px;
    }
    
    .d-flex.justify-content-between {
        margin-bottom: 1rem;
    }
    
    #invoice-table_wrapper input[type="search"] {
        position: relative;
        right: 180px; /* Adjust as needed */
    }
    
    .dataTables_filter {
        position: relative;
        z-index: 1;
    }


</style>
    <div class="col-sm-6">
        <h1>{{ __('message.all-invoices') }}</h1>
    </div>
    <div class="col-sm-6">
        <ol class="breadcrumb float-sm-right">
            <li class="breadcrumb-item"><a href="{{url('/')}}"><i class="fa fa-dashboard"></i> {{ __('message.home') }}</a></li>
            <li class="breadcrumb-item active">{{ __('message.all-invoices') }}</li>
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
                {!! Form::label('name','First Name') !!}
                {!! Form::text('name',$request->name,['class' => 'form-control','id'=>'name']) !!}

            </div>

            <div class="col-md-3 form-group">
                <!-- first name -->
                {!! Form::label('invoice_no','Invoice No') !!}
                {!! Form::text('invoice_no',$request->invoice_no,['class' => 'form-control','id'=>'invoice_no']) !!}

            </div>
       <div class="col-md-3 form-group">
    <!-- first name -->
    {!! Form::label('status', 'Status') !!}
    <select name="status" class="form-control" id="status">
        <option value="">{{ __('message.choose') }}</option>
        <option value="pending" @if($request->input('status') === 'pending') selected @endif>{{ __('message.choose') }}</option>
        <option value="Partially paid" @if($request->input('status') === 'Partially paid') selected @endif>{{ __('message.partially_paid') }}</option>
        <option value="success" @if($request->input('status') === 'success') selected @endif>{{ __('message.paid') }}</option>
    </select>
</div>


              <div class="col-md-3 form-group">
            {!! Form::label('currency', 'Currency') !!}
            <select name="currency_id" class="form-control" id="currency">
                <option value="">{{ __('message.choose') }}</option>
                @foreach($currencies as $currency)
                    @if($currency === $request->input('currency_id'))
                        <option value="{{ $currency }}" selected>{{ $currency }}</option>
                    @else
                        <option value="{{ $currency }}">{{ $currency }}</option>
                    @endif
                @endforeach
            </select>
        </div>

            
            <div class="col-md-3 form-group">
                <!-- first name -->
                {!! Form::label('from','Invoice From') !!}
                <div class="input-group date" id="invoice_from" data-target-input="nearest">
                    <input type="text" name="from" class="form-control datetimepicker-input" autocomplete="off" value="{{ $request->input('from') }}"  data-target="#invoice_from"/>

                    <div class="input-group-append" data-target="#invoice_from" data-toggle="datetimepicker">
                        <div class="input-group-text"><i class="fa fa-calendar"></i></div>
                    </div>

                </div>



            </div>
            <div class="col-md-3 form-group">
                <!-- first name -->
                {!! Form::label('till','Invoice Till') !!}
                <div class="input-group date" id="invoice_till" data-target-input="nearest">
                    <input type="text" name="till" class="form-control datetimepicker-input" autocomplete="off" value="{{ $request->input('till') }}"  data-target="#invoice_till"/>

                    <div class="input-group-append" data-target="#invoice_till" data-toggle="datetimepicker">
                        <div class="input-group-text"><i class="fa fa-calendar"></i></div>
                    </div>

                </div>

            </div>
        </div>
            

            <div class='row'>
                <div class="col-md-6">
                    <!-- {!! Form::submit('Search',['class'=>'btn btn-primary']) !!} -->
                    <button name="Search" type="submit"  class="btn btn-secondary"><i class="fa fa-search">&nbsp;</i>{!!Lang::get('Search')!!}</button>
                     &nbsp;&nbsp;
                    {!! Form::submit('Reset',['class'=>'btn btn-secondary','id'=>'reset']) !!}
                    </div>
            </div>


        </div>
        {!! Form::close() !!}
    </div>
        </div>
    </div>

<div class="card card-secondary card-outline">
    <div class="card-header">
      
        <h3 class="card-title">{{ Lang::get('message.invoices') }}</h3>
        <div class="card-tools">
        <button type="button" id="invoice_export-report-btn" class="btn btn-sm pull-right" data-toggle="tooltip" title="Export" style="position: absolute; left: 94%; top: 13px; z-index: 10;">
            <i class="fas fa-paper-plane"></i>
        </button>
        <a href="{{ url('invoice/generate') }}" class="btn btn-sm pull-right" data-toggle="tooltip" title="Create new invoice" style="position: absolute; left: 97%; top: 13px;right: 0px;">
            <i class="fas fa-plus"></i>
        </a>
    </div>

    </div>
      <div id="response"></div>

    <div class="card-body table-responsive"><br >

           <div class="d-flex justify-content-between mb-3">
                <button value="" class="btn btn-secondary btn-sm btn-alldell" id="bulk_delete">
                        <i class="fa fa-trash"></i>&nbsp;&nbsp;{{ __('message.delmultiple') }}
                    </button>
                <form id="columnForm">
                    <div class="custom-dropdown" id="columnUpdate">
                        <button class="btn btn-default pull-right" type="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false" style="position: relative;top: 52px;">
                            <span class="fa fa-columns"></span>&nbsp;&nbsp;{{ __('message.selected_columns') }}&nbsp;&nbsp;<span class="fas fa-caret-down"></span>
                        </button>
                        <div class="dropdown-menu" aria-labelledby="dropdownMenuButton">

                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" value="user_id" id="nameCheckbox">
                                <label class="form-check-label" for="nameCheckbox">{{ __('message.user') }}</label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" value="email" id="inemailCheckbox">
                                <label class="form-check-label" for="inemailCheckbox">{{ __('message.email') }}</label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" value="mobile" id="inmobileCheckbox">
                                <label class="form-check-label" for="inmobileCheckbox">{{ __('message.mobile') }}</label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" value="country" id="incountryCheckbox">
                                <label class="form-check-label" for="incountryCheckbox">{{ __('message.country') }}</label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" value="number" id="numberCheckbox">
                                <label class="form-check-label" for="numberCheckbox">{{ __('message.invoice_no') }}</label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" value="product" id="productCheckbox">
                                <label class="form-check-label" for="productCheckbox">{{ __('message.product') }}</label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" value="date" id="dateCheckbox">
                                <label class="form-check-label" for="dateCheckbox">{{ __('message.date') }}</label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" value="grand_total" id="totalCheckbox">
                                <label class="form-check-label" for="totalCheckbox">{{ __('message.total') }}</label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" value="status" id="instatusCheckbox">
                                <label class="form-check-label" for="instatusCheckbox">{{ __('message.status') }}</label>
                            </div>

                            <br>
                            <button type="button" class="btn btn-primary btn-sm" style="left: 10px; position: relative;" id="insaveColumnsBtn">{{ __('message.apply') }}</button>
                        </div>
                    </div>
                </form>
            </div>

        <div id="loading" style="display: none;">
            <div class="spinner"></div>
        </div>

        <div class="row">
            <div class="col-md-12">
                <div id="loader" style="display: none;">
                    <i class="fa fa-spinner fa-spin"></i>
                </div>
                <table id="invoice-table" class="table display" cellspacing="0" width="100%">
                
                    <thead>
                        <tr>
                            <th class="no-sort"><input type="checkbox" name="select_all" onchange="checking(this)"></th>
                            <th>{{ __('message.user') }}</th>
                            <th>{{ __('message.email') }}</th>
                            <th>{{ __('message.mobile') }}</th>
                            <th>{{ __('message.country') }}</th>
                            <th>{{ __('message.invoice_no') }}</th>
                            <th>{{ __('message.product') }}</th>
                            <th>{{ __('message.date') }}</th>
                            <th>{{ __('message.total') }}</th>
                            <th>{{ __('message.status') }}</th>
                            <th>{{ __('message.action') }}</th>
                        </tr>
                    </thead>
                </table>
            </div>
        </div>
    </div>
</div>

    <link rel="stylesheet" href="https://cdn.datatables.net/1.10.22/css/jquery.dataTables.min.css">
    <script>
     $('ul.nav-sidebar a').filter(function() {
        return this.id == 'all_invoice';
    }).addClass('active');

    // for treeview
    $('ul.nav-treeview a').filter(function() {
        return this.id == 'all_invoice';
    }).parentsUntil(".nav-sidebar > .nav-treeview").addClass('menu-open').prev('a').addClass('active');
</script>

    <script src="https://cdn.datatables.net/1.10.22/js/jquery.dataTables.min.js"></script>

<script type="text/javascript">
    $(document).ready(function() {
        var invoiceTable = $('#invoice-table').DataTable({
            processing: true,
            serverSide: true,
            stateSave: false,
            order: [[{!! $request->sort_field ?: 5 !!}, {!! "'".$request->sort_order."'" ?: "'asc'" !!}]],
            ajax: {
                "url": '{!! route('get-invoices', "name=$name&invoice_no=$invoice_no&status=$status&currency_id=$currency_id&from=$from&till=$till") !!}',
                error: function(xhr) {
                    if (xhr.status == 401) {
                        alert('{{ __('message.session_expired') }}')
                        window.location.href = '/login';
                    }
                },
                 dataFilter: function(data) {
                    var json = jQuery.parseJSON(data);
                    if (json.data.length === 0) {
                        $('#invoice_export-report-btn').hide(); // Hide export button
                    } else {
                        $('#invoice_export-report-btn').show(); // Show export button
                    }
                    return data;
                }
            },
            "oLanguage": {
                "sLengthMenu": "_MENU_ Records per page",
                "sSearch": "<span style='position: relative;right: 180px;'>{{ __('message.search') }}:</span> ",
                "sProcessing": ' <div class="overlay"><i class="fas fa-3x fa-sync-alt fa-spin"></i><div class="text-bold pt-2">{{ __('message.loading') }}</div></div>'
            },
            columnDefs: [
                {
                    targets: 'no-sort',
                    orderable: false,
                    order: []
                }
            ],
            columns: [
                { data: 'checkbox', name: 'checkbox' },
                { data: 'user_id', name: 'user_id' },
                { data: 'email', name: 'email' },
                { data: 'mobile', name: 'mobile' },
                { data: 'country', name: 'country' },
                { data: 'number', name: 'number' },
                { data: 'product', name: 'product' },
                { data: 'date', name: 'created_at' }, 
                { data: 'grand_total', name: 'grand_total' },
                { data: 'status', name: 'status' },
                { data: 'action', name: 'action' }
            ],
            "fnDrawCallback": function(oSettings) {
                $('[data-toggle="tooltip"]').tooltip({
                    container: 'body'
                });
                $('.loader').css('display', 'none');

                var urlParams = new URLSearchParams(window.location.search);
                var hasSearchParams = urlParams.has('name') || urlParams.has('invoice_no') || urlParams.has('status') || urlParams.has('currency_id') || urlParams.has('from') || urlParams.has('till');
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

      
    $('#insaveColumnsBtn').click(function() {
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
                entity_type: 'invoices',
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

        invoiceTable.columns().every(function() {
            var column = this;
            if (selectedColumns.includes(column.dataSrc())) {
                column.visible(true);
            } else {
                column.visible(false);
            }
        });
        invoiceTable.draw();
    });

      $(document).ready(function() {
        $.ajax({
            url: '{{ route('get-columns') }}',
            method: 'GET',
            data: {
                entity_type: 'invoices'
            },
            success: function(response) {
                var selectedColumns = response.selected_columns;
                invoiceTable.columns().every(function() {
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
        $('#invoice_export-report-btn').click(function() {
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
                url: '{{ url("export-invoices") }}',
                method: 'GET',
                data: {
                    selected_columns: selectedColumns,
                    search_params: searchParams
                },
                    success: function(response, status, xhr) {
                    var result = '<div class="alert alert-success">' +
                        '<button type="button" class="close" data-dismiss="alert" aria-label="Close">' +
                        '<span aria-hidden="true">&times;</span></button>' +
                        '<strong><i class="far fa-thumbs-up"></i> {{ __('message.well_done') }} </strong>' +
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
    function checking(e){
          
          $('#invoice-table').find("td input[type='checkbox']").prop('checked', $(e).prop('checked'));
     }
     

     $(document).on('click','#bulk_delete',function(){
      var id=[];
      if (confirm("Are you sure you want to delete this?"))
        {
            $('.invoice_checkbox:checked').each(function(){
              id.push($(this).val())
            });
            if(id.length >0)
            {
               $.ajax({
                      url:"{!! route('invoice-delete') !!}",
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
            }
        }  

     });
</script>
@stop
@section('datepicker')
<script type="text/javascript">
    $('#invoice_from').datetimepicker({
        format: 'L'
    });
    $('#invoice_till').datetimepicker({
        format: 'L'
    });
</script>

<script>
$(document).ready(function() {
  const initialProductValue = $('#product_id').val();

  const initialFromDateValue = $('#invoice_from').val();
  const initialTillDateValue = $('#invoice_till').val();

  $('#reset').on('click', function() {
    // Reset the input fields
    $('#name').val('');
    $('#invoice_no').val('');
    $('#status').val('');
    $('#currency').val('');

    $('#product_id').val(initialProductValue);

    $('#invoice_from').datetimepicker('clear');
    $('#invoice_till').datetimepicker('clear');
  });
});
</script>


@stop
















