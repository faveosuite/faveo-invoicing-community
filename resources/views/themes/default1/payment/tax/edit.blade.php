@extends('themes.default1.layouts.master')
@section('title')
Edit Tax
@stop
@section('content-header')
    <div class="col-sm-6">
        <h1>Edit Tax Class</h1>
    </div>
    <div class="col-sm-6">
        <ol class="breadcrumb float-sm-right">
            <li class="breadcrumb-item"><a href="{{url('/')}}"><i class="fa fa-dashboard"></i> Home</a></li>
            <li class="breadcrumb-item"><a href="{{url('settings')}}"><i class="fa fa-dashboard"></i> Settings</a></li>
            <li class="breadcrumb-item"><a href="{{url('tax')}}"><i class="fa fa-dashboard"></i> Tax</a></li>
            <li class="breadcrumb-item active">Edit Tax</li>
        </ol>
    </div><!-- /.col -->
@stop

@section('content')
<div class="card card-secondary card-outline">



    {{ html()->modelForm($tax, 'PATCH', url('tax/' . $tax->id))->open() }}



    <div class="card-body table-responsive">

        <div class="row">

            <div class="col-md-12">

                
                

                <div class="row">

                    <div class="col-md-4 form-group {{ $errors->has('name') ? 'has-error' : '' }}">
                        <!-- name -->
                        {{ html()->label(Lang::get('message.name'), 'name')->class('required') }}
                        {{ html()->text('name')->class('form-control')->id('tax-name') }}

                    </div>
                   
                    <div class="col-md-4 form-group {{ $errors->has('tax_class') ? 'has-error' : '' }}">
                        <!-- name -->
                        {{ html()->label(Lang::get('Tax Type'), 'tax_class')->class('required') }}
                        <select name="tax_classes_id" id="editTax" class="form-control">
                      <option value="{{$txClass->name}}">{{$taxClassName}}</option>
                      <option value="Others">Others</option>
                       @if($options->tax_enable)
                      <option value="Intra State GST">Intra State GST (Same Indian State)</option>
                      <option value="Inter State GST">Inter State GST (Other Indian State)</option>
                      <option value="Union Territory GST">Union Territory GST (Indian Union Territory)</option>
                        @endif
                      </select>

                       

                    </div>
                   
                    <div class="col-md-4 form-group">
                        <!-- name -->
                        {{ html()->label(Lang::get('message.status'), 'status') }}
                        <div class="row">
                            <div class="col-md-6 form-group {{ $errors->has('active') ? 'has-error' : '' }}">
                                <!-- name -->
                                {{ html()->label(Lang::get('message.active'), 'active') }}
                                {{ html()->radio('active', 1) }}

                            </div>
                            <div class="col-md-6 form-group {{ $errors->has('active') ? 'has-error' : '' }}">
                                <!-- name -->
                                {{ html()->label(Lang::get('message.inactive'), 'inactive') }}
                                {{ html()->radio('active', 0) }}

                            </div>
                        </div>

                    </div>


                </div>

                <div class="row">
                      
                    <div class="col-md-4 form-group {{ $errors->has('country') ? 'has-error' : '' }}">
                        
                   <?php $countries = \App\Model\Common\Country::pluck('nicename', 'country_code_char2')->toArray(); ?>
                  
                        <!-- name -->
                        {{ html()->label(Lang::get('message.country'), 'country') }}
                        {{ html()->select('country', ['' => 'All Countries'] + $countries)
                            ->class('form-control country') }}
                    </div>

                    <div class="col-md-4 form-group changegststate">
                        {{ html()->label(Lang::get('message.state'), 'state') }}
                        {{ html()->select('state', ['' => 'All States'] + $states)
                            ->class('form-control')
                            ->id('state-list') }}
                    </div>

                    <div class="col-md-4 form-group changegstrate">
                        {{ html()->label(Lang::get('message.rate') . ' (%)', 'rate')->class('required') }}
                        {{ html()->number('rate')->class('form-control') }}
                    </div>



                </div>
                <button type="submit" id="submit" class="btn btn-primary pull-right" data-loading-text="<i class='fa fa-refresh fa-spin fa-1x fa-fw'>&nbsp;</i> updating..."><i class="fa fa-sync-alt">&nbsp;&nbsp;</i>{!!Lang::get('Update')!!}</button>


            </div>

        </div>

    </div>
   
     <div class="card-body changegst" >
             
             <div class="row">
            
            <div class="col-md-12">
               
                 <table id="tax-table" class="table display" cellspacing="0" width="100%" styleClass="borderless">
                    
                    <thead><tr>
                            <th>Id</th>
                            <th>State</th>
                            <th>CGST</th>
                            <th>SGST</th>
                            <th>IGST</th>
                            <th>UTGST</th>
                        </tr></thead>

                   </table>
            </div>
        </div>

    </div>
<input type="hidden" value="{{$tax->country}}" id="hiddenvalue">
</div>

<link rel="stylesheet" type="text/css" href="//cdn.datatables.net/1.10.21/css/jquery.dataTables.min.css" />
<script src="//cdn.datatables.net/1.10.21/js/jquery.dataTables.min.js"></script>
<script type="text/javascript">
        $('#tax-table').DataTable({
            processing: true,
            serverSide: true,
             ajax: {
              "url":  '{!! route('get-taxtable') !!}',
                 error: function(xhr) {
                 if(xhr.status == 401) {
                  alert('Your session has expired. Please login again to continue.')
                  window.location.href = '/login';
                 }
              }

              },
            "oLanguage": {
                "sLengthMenu": "_MENU_ Records per page",
                "sSearch"    : "Search: ",
                "sProcessing": ' <div class="overlay"><i class="fas fa-3x fa-sync-alt fa-spin"></i><div class="text-bold pt-2">Loading...</div></div>'
            },
            columnDefs: [
                { 
                    targets: 'no-sort', 
                    orderable: false,
                    order: []
                }
            ],
            columns: [
             {data: 'id', name: 'id'},
                {data: 'state', name: 'state'},
                 {data: 'c_gst', name: 'c_gst'},
                {data: 's_gst', name: 's_gst'},
                {data: 'i_gst', name: 'i_gst'},
                {data: 'ut_gst', name: 'ut_gst'}
            ],
            "fnDrawCallback": function( oSettings ) {
                $('.loader').css('display', 'none');
            },
            "fnPreDrawCallback": function(oSettings, json) {
                $('.loader').css('display', 'block');
            },
        });
    </script>
<script>
    $(document).on("change", "#country", function () {
                var val= $(this).val();
          
        $.ajax({
            type: "GET",
            url: "{{url('get-state')}}/" + val,
            success: function (data) {
               $("#state-list").html(data);
                
            }
        });
    }); 

     $(document).ready(function(){
         var initial=$('#hiddenvalue').val();
         var initialState = $('#state-list').val();
          var taxValue=$('#editTax').val();
         
         if (taxValue == 'Inter State GST' || taxValue == 'Intra State GST' || taxValue == 'Union Territory GST') {
             $('.country').attr('disabled', true)
            $('#tax-name').attr('readonly', true)
         } else {
            $('.country').attr('disabled', false)
            $('#tax-name').attr('readonly', false)
         }

        if (initial != 'IN' || taxValue == 0)
          {
             $(document).find('.changegst').hide();
            
          }


  

if (initial == 'IN' && initialState == ''){
$(document).find('.changegststate').hide();
        $(document).find('.changegstrate').hide();

}

  $('#editTax').on('change', function() {
    var val= $(this).val();
     if (val == 'Others')
      {
        $('#tax-name').val('')
         $('.country').attr('disabled', false)
         $('#tax-name').attr('readonly',false);
          
         $(document).find('.changegst').hide();
        $(document).find('.country').show();
        $(document).find('.changegststate').show();
        $(document).find('.changegstrate').show();
       
         
      } else { 
            $('.country').attr('disabled', true)
            $('.country').val('IN')
            $('#tax-name').attr('readonly',true);

            if(this.value == 'Intra State GST') {
              $('#tax-name').val('CGST+SGST')
            } else if(this.value == 'Inter State GST') {
              $('#tax-name').val('IGST')
            } else {
                $('#tax-name').val('CGST+UTGST')
            }

             $(document).find('.changegst').show();
             $(document).find('.changegststate').hide();
             $(document).find('.changegstrate').hide();
           }

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

{!! html()->form()->close() !!}
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


