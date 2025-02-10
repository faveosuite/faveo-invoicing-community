@extends('themes.default1.layouts.master')
@section('title')
Mailchimp
@stop
@section('content-header')
    <div class="col-sm-6">
        <h1>{{ __('message.templates') }}</h1>
    </div>
    <div class="col-sm-6">
        <ol class="breadcrumb float-sm-right">
            <li class="breadcrumb-item"><a href="{{url('/')}}"><i class="fa fa-dashboard"></i> {{ __('message.home') }}</a></li>
            <li class="breadcrumb-item"><a href="{{url('settings')}}"><i class="fa fa-dashboard"></i> {{ __('message.settings') }}</a></li>
            <li class="breadcrumb-item"><a href="{{url('mailchimp')}}"><i class="fa fa-dashboard"></i> {{ __('message.mailchimp_setting') }}</a></li>
            <li class="breadcrumb-item active">{{ __('message.mailchimp_mapping') }}</li>
        </ol>
    </div><!-- /.col -->
@stop

@section('content')
<style>
.switch {
  position: relative;
  display: inline-block;
  width: 60px;
  height: 34px;
}

.switch input {display:none;}

.slider {
  position: absolute;
  cursor: pointer;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background-color: #ccc;
  -webkit-transition: .4s;
  transition: .4s;
}

.slider:before {
  position: absolute;
  content: "";
  height: 26px;
  width: 26px;
  left: 4px;
  bottom: 4px;
  background-color: white;
  -webkit-transition: .4s;
  transition: .4s;
}

input:checked + .slider {
  background-color: #2196F3;
}

input:focus + .slider {
  box-shadow: 0 0 1px #2196F3;
}

input:checked + .slider:before {
  -webkit-transform: translateX(26px);
  -ms-transform: translateX(26px);
  transform: translateX(26px);
}

/* Rounded sliders */
.slider.round {
  border-radius: 34px;
}

.slider.round:before {
  border-radius: 50%;
}
.scrollit {
    overflow:scroll;
    height:600px;
}
</style>
<div class="row">

    <div class="col-md-12">
        <div class="card card-secondary card-outline">

            <div class="card-header">
                <h3 class="card-title">{{Lang::get('message.list-fields')}}</h3>
                {!! Form::model($model,['url'=>'mail-chimp/mapping','method'=>'patch','files'=>true]) !!}
               
                       
                    
              
               </div>   
            <div class="card-body">

                <table class="table table-hover">
                    <tr>
                        <th>{{Lang::get('message.agora-fields')}}</th>
                        <th>{{Lang::get('message.mailchimp-fields')}}</th>
                    </tr>
                    <tr>
                        <td>{{Lang::get('message.first_name')}}</td>
                        <td>{!! Form::select('first_name',[''=>'Select','Fields'=>$mailchimp_fields],null,['class'=>'form-control']) !!}</td>
                    </tr>
                    <tr>
                        <td>{{Lang::get('message.last_name')}}</td>
                        <td>{!! Form::select('last_name',[''=>'Select','Fields'=>$mailchimp_fields],null,['class'=>'form-control']) !!}</td>
                    </tr>
                    <tr>
                        <td>{{Lang::get('message.company')}}</td>
                        <td>{!! Form::select('company',[''=>'Select','Fields'=>$mailchimp_fields],null,['class'=>'form-control']) !!}</td>
                    </tr>
                    <tr>
                        <td>{{Lang::get('message.mobile')}}</td>
                        <td>{!! Form::select('mobile',[''=>'Select','Fields'=>$mailchimp_fields],null,['class'=>'form-control']) !!}</td>
                    </tr>
                    <tr>
                        <td>{{Lang::get('message.address')}}</td>
                        <td>{!! Form::select('address',[''=>'Select','Fields'=>$mailchimp_fields],null,['class'=>'form-control']) !!}</td>
                    </tr>
                     <tr>
                        <td>{{Lang::get('message.country')}}</td>
                        <td>{!! Form::select('country',[''=>'Select','Fields'=>$mailchimp_fields],null,['class'=>'form-control']) !!}</td>
                    </tr>
                    <tr>
                        <td>{{Lang::get('message.town')}}</td>
                        <td>{!! Form::select('town',[''=>'Select','Fields'=>$mailchimp_fields],null,['class'=>'form-control']) !!}</td>
                    </tr>
                    <tr>
                        <td>{{Lang::get('message.state')}}</td>
                        <td>{!! Form::select('state',[''=>'Select','Fields'=>$mailchimp_fields],null,['class'=>'form-control']) !!}</td>
                    </tr>
                    <tr>
                        <td>{{Lang::get('message.zip')}}</td>
                        <td>{!! Form::select('zip',[''=>'Select','Fields'=>$mailchimp_fields],null,['class'=>'form-control']) !!}</td>
                    </tr>
                    <tr>
                        <td>{{Lang::get('message.active')}}</td>
                        <td>{!! Form::select('active',[''=>'Select','Fields'=>$mailchimp_fields],null,['class'=>'form-control']) !!}</td>
                    </tr>
                    <tr>
                        <td>{{Lang::get('message.role')}}</td>
                        <td>{!! Form::select('role',[''=>'Select','Fields'=>$mailchimp_fields],null,['class'=>'form-control']) !!}</td>
                    </tr>
                    <tr>
                        <td>{{Lang::get('message.app-title')}}</td>
                        <td>{!! Form::select('source',[''=>'Select','Fields'=>$mailchimp_fields],null,['class'=>'form-control']) !!}</td>
                    </tr>


                </table>
                <button type="submit" class="btn btn-primary pull-right" id="submit" ><i class="fa fa-sync-alt">&nbsp;&nbsp;</i>{!!Lang::get('message.update')!!}</button>

                {!! Form::close() !!}

            </div>



            </div>



        </div>
        <!-- /.box -->
    <div class="col-md-12">
         <div class="card card-secondary card-outline">

         
                        
            <div class="card-body">


                   <div id="alertMessage"></div>
                <div class="card-header">
                    <h3 class="card-title" >{{Lang::get('message.group-fields')}}</h3><br>
{{--                    <h4>Map Products</h4>--}}
            <div class="form-group">
            <span style="color:red;">*&nbsp {{ __('message.map_mailchimp_products') }} </span>
        </div>  
               <label class="switch toggle_event_editing">
                          
                         <input type="checkbox" value="{{$status->mailchimp_product_status}}"  name="modules_settings" 
                          class="checkbox1" id="updateProductStatus">
                          <span class="slider round"></span>
                    </label>
               </div>   

            {!! Form::model($model2,['url'=>'mailchimp-group/mapping','id'=>'mapproducts','method'=>'patch','files'=>true]) !!}
               
              
                        <h5>{{ __('message.select_a_group') }}</h5>
                         <select name="group"  class="form-control col-md-2"  id="group" onChange="getGroup(this.value)">
                            <option value="">{{ __('message.choose') }}</option>
                @foreach ($display as $key=>$value)
               <option value="{{$value['id']}}">{{$value['title']}}</option>
                 
            
                @endforeach
            </select>
                <table class="table table-hover">
                    <tr>
                        <th>{{Lang::get('message.products')}}</th>
                        <th>{{Lang::get('Mapped With')}}</th>
                        <th>{{Lang::get('message.mailchimp-product')}}</th>
                    </tr>

                    <tr>
                        <td>
                              <select name="row[1][]" id="plan" class="form-control">
                                @if(count($productList)>0)
                                <option value="">{{$productList[0]}}</option>
                                @endif
                                  <option value="">{{ __('message.choose') }}</option>
                              @foreach($agoraProducts as $key=>$product)

                                   <option value="{{$key}}">{{$product}}</option>
                           
                             @endforeach
                                </select>
                        </td>
                          <td>
                            @if(count($categoryList)>0)
                             <input type="text" name="map[1][]" value="{{$categoryList[0]}}" readonly>
                             @else
                            <input type="text" name="map[1][]" value="" readonly>
                             @endif
                        </td>
                         <td>
                              <select name="row[1][]" id="fields" class="form-control field">
                              </select>
                        </td>
                   
                    </tr>

                    <tr>
                        <td>
                              <select name="row[2][]" id="plan" class="form-control">
                                @if(count($productList)>0)
                                <option value="">{{$productList[1]}}</option>
                                @endif
                                  <option value="">{{ __('message.choose') }}</option>
                              @foreach($agoraProducts as $key=>$product)
                             
                                   <option value="{{$key}}">{{$product}}</option>
                           
                             @endforeach
                                </select>
                        </td>
                         <td>
                             @if(count($categoryList)>0)
                             <input type="text" name="map[2][]" value="{{$categoryList[1]}}" readonly>
                             @else
                            <input type="text" name="map[2][]" value="" readonly>
                             @endif
                        </td>
                         <td>
                              <select name="row[2][]" id="fields" class="form-control field">
                             </select>
                        </td>
                   
                    </tr>



                     <tr>
                        <td>
                              <select name="row[3][]" id="plan" class="form-control">
                                @if(count($productList)>0)
                                <option value="">{{$productList[2]}}</option>
                                @endif
                                  <option value="">{{ __('message.choose') }}</option>
                              @foreach($agoraProducts as $key=>$product)
                             
                                   <option value="{{$key}}">{{$product}}</option>
                           
                             @endforeach
                                </select>
                        </td>
                         <td>
                             @if(count($categoryList)>0)
                             <input type="text" name="map[3][]" value="{{$categoryList[2]}}" readonly>
                             @else
                             <input type="text" name="map[3][]" value="" readonly>
                             @endif
                        </td>
                         <td>
                              <select name="row[3][]" id="fields" class="form-control field">
                                <option value=""></option>
                            </select>
                        </td>
                   
                    </tr>



                     <tr>
                        <td>
                              <select name="row[4][]" id="plan" class="form-control ">
                                @if(count($productList)>0)
                                <option value="">{{$productList[3]}}</option>
                                @endif
                                  <option value="">{{ __('message.choose') }}</option>
                              @foreach($agoraProducts as $key=>$product)
                             
                                   <option value="{{$key}}">{{$product}}</option>
                           
                             @endforeach
                                </select>
                        </td>
                         <td>
                             @if(count($categoryList)>0)
                             <input type="text" name="map[4][]" value="{{$categoryList[3]}}" readonly>
                             @else
                             <input type="text" name="map[4][]" value="" readonly>
                             @endif
                        </td>
                         <td>
                              <select name="row[4][]" id="fields" class="form-control field">
                            </select>
                        </td>
                   
                    </tr>

   
                    <tr>
                        <td>
                              <select name="row[5][]" id="plan" class="form-control">
                                @if(count($productList)>0)
                                <option value="">{{$productList[4]}}</option>
                                @endif
                                  <option value="">{{ __('message.choose') }}</option>
                              @foreach($agoraProducts as $key=>$product)
                             
                                   <option value="{{$key}}">{{$product}}</option>
                           
                             @endforeach
                                </select>
                        </td>
                        
                         <td>
                              @if(count($categoryList)>0)
                             <input type="text" name="map[5][]" value="{{$categoryList[4]}}" readonly>
                             @else
                             <input type="text" name="map[5][]" value="" readonly>
                             @endif
                           
                        </td>
                          <td>
                              <select name="row[5][]" id="fields" class="form-control field">
                            </select>
                        </td>
                   
                    </tr>


                      <tr>
                        <td>
                              <select name="row[6][]" id="plan" class="form-control">
                                @if(count($productList)>0)
                                <option value="">{{$productList[5]}}</option>
                                @endif
                                  <option value="">{{ __('message.choose') }}</option>
                              @foreach($agoraProducts as $key=>$product)
                             
                                   <option value="{{$key}}">{{$product}}</option>
                           
                             @endforeach
                                </select>
                        </td>
                         <td>
                            @if(count($categoryList)>0)
                             <input type="text" name="map[6][]" value="{{$categoryList[5]}}" readonly>
                             @else
                             <input type="text" name="map[6][]" value="" readonly>
                             @endif
                        </td>
                         <td>
                              <select name="row[6][]" id="fields" class="form-control field">
                            </select>
                        </td>
                   
                    </tr>


                     <tr>
                        <td>
                              <select name="row[7][]" id="plan" class="form-control">
                                @if(count($productList)>0)
                                <option value="">{{$productList[6]}}</option>
                                @endif
                                  <option value="">{{ __('message.choose') }}</option>
                              @foreach($agoraProducts as $key=>$product)
                                   <option value="{{$key}}">{{$product}}</option>
                           
                             @endforeach
                                </select>
                        </td>
                         <td>
                         @if(count($categoryList)>0)
                             <input type="text" name="map[7][]" value="{{$categoryList[6]}}" readonly>
                             @else
                             <input type="text" name="map[7][]" value="" readonly>
                             @endif
                         </td>
                         <td>
                              <select name="row[7][]" id="fields" class="form-control field">
                            </select>
                        </td>
                   
                    </tr>

                     <tr>
                        <td>
                              <select name="row[8][]" id="plan" class="form-control">
                                @if(count($productList)>0)
                                <option value="">{{$productList[7]}}</option>
                                @endif
                                  <option value="">{{ __('message.choose') }}</option>
                              @foreach($agoraProducts as $key=>$product)
                                   <option value="{{$key}}">{{$product}}</option>
                           
                             @endforeach
                                </select>
                        </td>
                        <td>
                         @if(count($categoryList)>0)
                             <input type="text" name="map[8][]" value="{{$categoryList[7]}}" readonly>
                             @else
                             <input type="text" name="map[8][]" value="" readonly>
                             @endif
                         </td>
                         <td>
                              <select name="row[8][]" id="fields" class="form-control field">
                            </select>
                        </td>
                   
                    </tr>

                      <tr>
                        <td>
                              <select name="row[9][]" id="plan" class="form-control">
                                @if(count($productList)>0)
                                <option value="">{{$productList[8]}}</option>
                                @endif
                                  <option value="">{{ __('message.choose') }}</option>
                              @foreach($agoraProducts as $key=>$product)
                                   <option value="{{$key}}">{{$product}}</option>
                           
                             @endforeach
                                </select>
                        </td>
                        <td>
                         @if(count($categoryList)>0)
                             <input type="text" name="map[9][]" value="{{$categoryList[8]}}" readonly>
                             @else
                             <input type="text" name="map[9][]" value="" readonly>
                             @endif
                         </td>
                         <td>
                              <select name="row[9][]" id="fields" class="form-control field">
                            </select>
                        </td>
                   
                    </tr>

                     <tr>
                        <td>
                              <select name="row[10][]" id="plan" class="form-control">
                                @if(count($productList)>0)
                                <option value="">{{$productList[9]}}</option>
                                @endif
                                  <option value="">{{ __('message.choose') }}</option>
                              @foreach($agoraProducts as $key=>$product)
                                   <option value="{{$key}}">{{$product}}</option>
                           
                             @endforeach
                                </select>
                        </td>
                        <td>
                         @if(count($categoryList)>0)
                             <input type="text" name="map[10][]" value="{{$categoryList[9]}}" readonly>
                             @else
                             <input type="text" name="map[10][]" value="" readonly>
                             @endif
                         </td>
                         <td>
                              <select name="row[10][]" id="fields" class="form-control field">
                            </select>
                        </td>
                   
                    </tr>

                 </table>
                <button type="submit" class="btn btn-primary pull-right" id="submit" ><i class="fa fa-sync-alt">&nbsp;</i>{!!Lang::get('message.update')!!}</button>

                {!! Form::close() !!}


            </div>

            

        </div>

    </div>
    <div class="col-md-12">
        <div class="card card-secondary card-outline">
           <div id="alertMessage1"></div>
             <div class="card-body">
                  <h3 class="card-title">{{ __('message.map_paid_group') }}</h3><br>
            <div class="card-header">
                <div class="form-group">
                    <span style="color:red;">*&nbsp;{{ __('message.mailchimp_dropdown_true_false') }}<br> {{ __('message.mailchimp_account_group') }}</span>
            </div>  
                <label class="switch">
                          
                         <input type="checkbox" value="{{$status->mailchimp_ispaid_status}}"  name="modules_settings" 
                          class="checkbox2" id="updateispaidStatus">
                          <span class="slider round"></span>
                    </label>
               
              
               </div>

               
                 
                 
                        
          
                
            {!! Form::model($model2,['url'=>'mailchimp-ispaid/mapping','method'=>'patch','id'=>'mapispaid','files'=>true]) !!}
               
                          <br>
                        <h5>{{ __('message.select_a_group') }}</h5>
                         <select name="group"  class="form-control col-md-2"  id="group" onChange="getGroup(this.value)">
                            <option value="">{{ __('message.choose') }}</option>
                @foreach ($display as $key=>$value)
               <option value="{{$value['id']}}"<?php  if(in_array($value['id'], $selectedIsPaid) ) 
                        { echo "selected";} ?>>{{$value['title']}}</option>
                 
            
                @endforeach
            </select>


                 <br>
                 <button type="submit" class="btn btn-primary pull-right" id="submit" data-loading-text="<i class='fa fa-circle-o-notch fa-spin'>&nbsp;</i> Saving..."><i class="fa fa-sync-alt">&nbsp;&nbsp;</i>{!!Lang::get('message.update')!!}</button>
                 {!! Form::close() !!}

            </div>



        </div>
    </div>

    </div>


</div>
<script>
     $('ul.nav-sidebar a').filter(function() {
        return this.id == 'setting';
    }).addClass('active');

    // for treeview
    $('ul.nav-treeview a').filter(function() {
        return this.id == 'setting';
    }).parentsUntil(".nav-sidebar > .nav-treeview").addClass('menu-open').prev('a').addClass('active');
</script>
<script> 

    $(document).ready(function () {
    var val = $("#group").val();
    getGroup(val);
    });
    function getGroup(val)
    { 
    if(val!=""){
     getInterestGroup(val)
    }
      else{
        $(".field").html('<option value=>{{ __('message.please_select_a_group') }}</option>').val('');
    }
   }

    function getInterestGroup(val)
    {
       $.ajax({
        type : "GET",
        url : "{{url('get-group-field')}}/" + val,
       
        success: function(data) {
             $(".field").html(data);

            }
        });
     }
    //Update Mailchimp product status
 $(document).ready(function (){
  var productstatus =  $('.checkbox1').val();
    if(productstatus ==1)
     {
       $('#updateProductStatus').prop('checked',true);
       $('#mapproducts').attr('hidden', false);
      } else if(productstatus ==0){
      $('#updateProductStatus').prop('checked',false);
        $('#mapproducts').attr('hidden', true);
     }
  });


     $('.checkbox1').on('click',function(){
      if ($('#updateProductStatus').val() == 1) {
       var status = 0;
       $('#mapproducts').attr('hidden', true);

    } else if ($('#updateProductStatus').val() == 0){  
        var status = 1;
        $('#mapproducts').attr('hidden', false);
    }
     $.ajax ({
      url: '{{url("mailchimp-prod-status")}}',
      type : 'post',
      data: {
       "status": status,
      },
       success: function (data) {
            $('#alertMessage').show();
            var result = '<div class="alert alert-success alert-dismissable"><button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button><strong><i class="fa fa-check"></i> ' + @json(__('message.success')) + '! </strong>' + data.update + '.</div>';
            $('#alertMessage').html(result+ ".");
              setInterval(function(){ 
                $('#alertMessage').slideUp(3000); 
            }, 1000);
               location.reload();
          },
    })

     })

    //Update Is Paid Status
 $(document).ready(function (){
  var ispaidstatus =  $('.checkbox2').val();
    if(ispaidstatus ==1)
     {
       $('#updateispaidStatus').prop('checked',true);
       $('#mapispaid').attr('hidden', false);
      } else if(ispaidstatus ==0){
      $('#updateispaidStatus').prop('checked',false);
        $('#mapispaid').attr('hidden', true);
     }
  });


     $('.checkbox2').on('click',function(){
      if ($('#updateispaidStatus').val() == 1) {
       var status = 0;
       $('#mapispaid').attr('hidden', true);

    } else if ($('#updateispaidStatus').val() == 0){  
        var status = 1;
        $('#mapispaid').attr('hidden', false);
    }
     $.ajax ({
      url: '{{url("mailchimp-paid-status")}}',
      type : 'post',
      data: {
       "status": status,
      },
       success: function (data) {
            $('#alertMessage1').show();
           var result = '<div class="alert alert-success alert-dismissable"><button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button><strong><i class="fa fa-check"></i> ' + '{{ __("message.success") }}' + '! </strong>' + data.update + '.</div>';
            $('#alertMessage1').html(result+ ".");
              setInterval(function(){ 
                $('#alertMessage1').slideUp(3000); 
            }, 1000);
               location.reload();
          },
    })

     })
</script>
@stop