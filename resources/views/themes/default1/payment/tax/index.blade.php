@extends('themes.default1.layouts.master')
@section('title')
Tax
@stop
@section('content-header')
    <div class="col-sm-6">
        <h1>{{ __('message.create_tax_option_class') }}</h1>
    </div>
    <div class="col-sm-6">
        <ol class="breadcrumb float-sm-right">
            <li class="breadcrumb-item"><a href="{{url('/')}}"><i class="fa fa-dashboard"></i> {{ __('message.home') }}</a></li>
            <li class="breadcrumb-item"><a href="{{url('settings')}}"><i class="fa fa-dashboard"></i> {{ __('message.settings') }}</a></li>
            <li class="breadcrumb-item active">{{ __('message.tax') }}</li>
        </ol>
    </div><!-- /.col -->
@stop

@section('content')

    <style>


.btn-default.btn-on-1.active{background-color: #006FFC;color: white;}

.btn-default.btn-off-1.active{background-color: #DA4F49;color: white;}
.btn-default.btn-on-2.active{background-color: #006FFC;color: white;}

.btn-default.btn-off-2.active{background-color: #DA4F49;color: white;}
.btn-default.btn-on-3.active{background-color: #006FFC;color: white;}

.btn-default.btn-off-3.active{background-color: #DA4F49;color: white;}

/* Rounded sliders */

</style>
<head>

  </head>
  





            
             
                

            @include('themes.default1.payment.tax.create-tax-option')


      
       <div class="card card-secondary card-outline">
           <div class="card-header">
               <h3 class="card-title">{{Lang::get('message.options')}}</h3>
               {!! Form::model($options,['url'=>'taxes/option','method'=>'post']) !!}
           </div>
        <div class="card-body">
            <div class="row">

                <div class="col-md-12">


                    <table class="table">



                       
                            <td>
                                {!! Form::label('tax_enable',Lang::get('message.tax-enable')) !!}
                            </td>
                            <td>
                                <label class="switch">
                                     <!-- {!! Form::hidden('tax_enable',0) !!} -->
                                 <!-- {!! Form::checkbox('tax_enable',1) !!} -->
                                 <!-- <input id="toggle-event" type="checkbox" data-toggle="toggle" name="tax_enable"> -->
                                <div class="btn-group btn-group-toggle"  data-toggle="buttons" >
                                    <label class="btn btn-default btn-on-1 btn-sm ">
                                    <input type="radio" id="chkYes" value="1" name="tax_enable" >{{ __('message.caps_enabled') }}</label>
                                    <label class="btn btn-default btn-off-1 btn-sm  ">
                                    <input type="radio" id="chkNo" value="0" name="tax_enable" >{{ __('message.caps_disabled') }}</label>
                                    <span class="slider"></span>
                                  </div>
                                    
                              </label>
                               
                            </td>
                        

                        <tr>
                            <td>
                                {!! Form::label('inclusive',Lang::get('message.prices-entered-with-tax')) !!}
                            </td>
                            <td>
                           
                                      <div class="btn-group btn-group-toggle"  data-toggle="buttons">
                                    <label class="btn btn-default btn-on-2 btn-sm ">
                                    <input type="radio" id="chkYes" value="1" name="inclusive">{{ __('message.caps_inclusive') }}</label>
                                    <label class="btn btn-default btn-off-2 btn-sm">
                                    <input type="radio" id="chkNo" value="0" name="inclusive">{{ __('message.caps_exclusive') }}</label>
                                    <span class="slider"></span>
                                  </div>
                            </td>
                        </tr>
                       
                       
                        <tr>
                            <td>
                                {!! Form::label('rounding',Lang::get('message.rounding')) !!}
                            </td>
                            <td>
                                    <div class="btn-group btn-group-toggle"  data-toggle="buttons">
                                    <label class="btn btn-default btn-on-3 btn-sm ">
                                    <input type="radio" id="chkYes" value="1" name="rounding">{{ __('message.caps_enabled') }}</label>
                                    <label class="btn btn-default btn-off-3 btn-sm">
                                    <input type="radio" id="chkNo" value="0" name="rounding">{{ __('message.caps_disabled') }}</label>
                                    <span class="slider"></span>
                                  </div>                            </td>
                        </tr>
                        
                    </table>


                       <button type="submit" class="btn btn-default pull-right" id="submit" ><i class="fa fa-save"></i>&nbsp;{!!Lang::get('message.save')!!}</button>

                       {!! Form::close() !!}
              </div>
                </div>
              </div>
            </div>

             <div class="card card-secondary card-outline">

                  <div class="card-header">
                      <h3 class="card-title">{{Lang::get('Tax Classes')}}</h3>

                      <div class="card-tools">
                          <a href="#create-tax-option" class="btn btn-default pull-right btn-sm" data-toggle="modal" data-target="#create-tax-option"><span class="fa fa-plus"></span>&nbsp;&nbsp;{{Lang::get('message.create')}}</a>


                      </div>
                  </div>
                  <div id="response"></div>
                 <div class="card-body table-responsive">
                  <div class="row">
                <div class="col-md-12">
                    <table id="tax-table" class="table display" cellspacing="0" width="100%" styleClass="borderless">
                    <button  value="" class="btn btn-secondary btn-sm btn-alldell" id="bulk_delete"><i class="fa fa-trash">&nbsp;&nbsp;</i> {{ __('message.delmultiple') }}</button><br /><br />
                        <thead><tr>
                            <th class="no-sort"><input type="checkbox" name="select_all" onchange="checking(this)"></th>
                             <th>{{ __('message.tax-type') }}</th>
                              <th>{{ __('message.name_page') }}</th>
                               <th>{{ __('message.country') }}</th>
                              <th>{{ __('message.state') }}</th>
                               <th>{{ __('message.rate') }} (%)</th>
                               <th>{{ __('message.action') }}</th>
                            </tr></thead>
                         </table>
                  

                </div>
                  </div>
            </div>


    </div>

    <link rel="stylesheet" type="text/css" href="//cdn.datatables.net/1.10.21/css/jquery.dataTables.min.css" />
    <script src="//cdn.datatables.net/1.10.21/js/jquery.dataTables.min.js"></script>
    </script>


  
 
    <script type="text/javascript">

      $(document).ready(function(){
      var btn = {{($options->tax_enable)}};
     if(btn== '1'){
$('.btn-on-1').addClass('active');
$('.btn-on-1').css("background-color", "#006FFC","color", "white");
     }
     else{
$('.btn-off-1').addClass('active');
$('.btn-off-1').css("background-color", "#DA4F49","color", "white");
// $('.gstshow').removeAttribute("style");
     }
    var btn1 = {{($options->inclusive)}};
     if(btn1== '1'){
$('.btn-on-2').addClass('active');
$('.btn-on-2').css("background-color", "#006FFC","color", "white");
// $('.gstshow').removeClass("hide");

     }
     else{
$('.btn-off-2').addClass('active');
$('.btn-off-2').css("background-color", "#DA4F49","color", "white");
// $('.gstshow').addClass('hide');
// $('.gstshow').removeAttribute("style");
     }
       var btn2 = {{($options->rounding)}};
     if(btn2== '1'){
$('.btn-on-3').addClass('active');
$('.btn-on-3').css("background-color", "#006FFC","color", "white");
// $('.gstshow').removeClass("hide");

     }
     else{
$('.btn-off-3').addClass('active');
$('.btn-off-3').css("background-color", "#DA4F49","color", "white");
// $('.gstshow').addClass('hide');
// $('.gstshow').removeAttribute("style");
     }

    })



   
     // $('#chkYes').click


            $('#tax-table').DataTable({
                processing: true,
                serverSide: true,
                stateSave: false,
                ordering: true,
                searching:true,
                select: true,
                order: [[ 1, "desc" ]],
                 ajax: {
              "url":  '{!! route('get-tax') !!}',
                 error: function(xhr) {
                 if(xhr.status == 401) {
                  alert('{{ __('message.session_expired') }}')
                  window.location.href = '/login';
                 }
              }

              },
                "oLanguage": {
                    "sLengthMenu": "_MENU_ Records per page",
                    "sSearch"    : "Search: ",
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
                    {data: 'checkbox', name: 'checkbox'},
                    {data: 'tax_classes_id', name: 'tax_classes_id'},
                    {data: 'name', name: 'name'},
                    {data: 'country', name: 'country'},
                    {data: 'state', name: 'state'},
                    {data: 'rate', name: 'rate'},
                    {data: 'action', name: 'action'}
                ],
                "fnDrawCallback": function( oSettings ) {
                    $(function () {
                        $('[data-toggle="tooltip"]').tooltip({
                            container : 'body'
                        });
                    });
                    $('.loader').css('display', 'none');
                },
                "fnPreDrawCallback": function(oSettings, json) {
                    $('.loader').css('display', 'block');
                },
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

    @section('icheck')
    <script>

       function checking(e){
              $('#tax-table').find("td input[type='checkbox']").prop('checked', $(e).prop('checked'));
         }
         

         $(document).on('click','#bulk_delete',function(){
          var id=[];
          if (confirm("Are you sure you want to delete this?"))
            {
                $('.tax_checkbox:checked').each(function(){
                  id.push($(this).val())
                });
                if(id.length >0)
                {
                   $.ajax({
                          url:"{!! route('tax-delete') !!}",
                          method:"delete",
                          data: $('#check:checked').serialize(),
                          beforeSend: function () {
                    $('#gif').show();
                    },
                    success: function (data) {
                    $('#gif').show();
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
     @if(count($errors) > 0)
      <script type="text/javascript">
       $( document ).ready(function() {
             $('#create-tax-option').modal('show');
        });
      </script>
      @endif
    @stop
   