@extends('themes.default1.layouts.master')
@section('title')
Create Product
@stop
@section('content-header')
<h1>
Create New Product
</h1>
  <ol class="breadcrumb">
        <li><a href="{{url('/')}}"><i class="fa fa-dashboard"></i> Home</a></li>
        <li><a href="{{url('products')}}">All Products</a></li>
        <li class="active">Create Product</li>
      </ol>
@stop
@section('content')
<head>
    <link src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.3/js/select2.min.js">
</head> 
<div class="box box-primary">

    <div class="box-header">
        @if (count($errors) > 0)
        
        <div class="alert alert-danger alert-dismissable">
            <strong>Whoops!</strong> There were some problems with your input.<br><br>
            <ul>
                @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
        @endif

        @if(Session::has('success'))
        <div class="alert alert-success alert-dismissable">
            <i class="fa fa-check"></i>
            <b>{{Lang::get('message.success')}}!</b> 
            <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
            {{Session::get('success')}}
        </div>
        @endif
        <!-- fail message -->
        @if(Session::has('fails'))
        <div class="alert alert-danger alert-dismissable">
            <i class="fa fa-ban"></i>
            <b>{{Lang::get('message.alert')}}!</b> {{Lang::get('message.failed')}}.
            <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
            {{Session::get('fails')}}
        </div>
        @endif
        {!! Form::open(['url'=>'products','method'=>'post','files' => true,'id'=>'createproduct']) !!}
        <h4>{{Lang::get('message.product')}}	<button type="submit" class="btn btn-primary pull-right" id="submit" data-loading-text="<i class='fa fa-circle-o-notch fa-spin'>&nbsp;</i> Saving..."><i class="fa fa-floppy-o">&nbsp;&nbsp;</i>{!!Lang::get('message.save')!!}</button></h4>

    </div>

    <div class="box-body">

        <div class="row">

            <div class="col-md-12">



                <div class="nav-tabs-custom">
                    <ul class="nav nav-tabs">
                        <li class="active"><a href="#tab_1" data-toggle="tab">{{Lang::get('message.details')}}</a></li>
                        <li><a href="#tab_2" data-toggle="tab">{{Lang::get('message.price')}}</a></li>
                        <!-- <li><a href="#tab_3" data-toggle="tab">Plans</a></li> -->
                    </ul>
                    <div class="tab-content">
                        <div class="tab-pane active" id="tab_1">
                            <div class="row">

                                <div class="col-md-3 form-group {{ $errors->has('name') ? 'has-error' : '' }}">
                                    <!-- first name -->
                                    {!! Form::label('name',Lang::get('message.name'),['class'=>'required']) !!}
                                    {!! Form::text('name',null,['class' => 'form-control', 'id' =>'productname']) !!}
                                <h6 id = "namecheck"></h6>

                                </div>

                                 <div class="col-md-3 form-group {{ $errors->has('type') ? 'has-error' : '' }}">
                                    <!-- last name -->
                                    {!! Form::label('type',Lang::get('message.type'),['class'=>'required']) !!}
                                    {!! Form::select('type',['Types'=>$type],null,['class' => 'form-control']) !!}

                                </div>


                                <div class="col-md-3 form-group {{ $errors->has('group') ? 'has-error' : '' }}">
                                    <!-- last name -->
                                    {!! Form::label('group',Lang::get('message.group')) !!}
                          <select name="group" value= "Choose" class="form-control">
                             <option value="">Choose</option>
                           @foreach($group as $key=>$value)

                             <option value={{$key}}>{{$value}}</option>
                          @endforeach
                          </select>
  
                                </div>
                                <div class="col-md-3 form-group {{ $errors->has('category') ? 'has-error' : '' }}">
                                     <?php
                                        $type = DB::table('product_categories')->pluck('category_name')->toarray();
                                       
                                        ?>
                                    <!-- last name -->
                                    {!! Form::label('category',Lang::get('message.category'),['class'=>'required']) !!}
                                   <!--  {!! Form::select('category',['helpdesk'=>'Helpdesk','servicedesk'=>'ServiceDesk','service'=>'Service','satellite helpdesk'=>'Satellite Helpdesk','plugin'=>'Plugins','helpdeskvps'=>'HelpDesk VPS','servicedesk vps'=>'ServiceDesk VPS'],null,['class' => 'form-control']) !!} -->

                                     <select name="category" value= "Choose" class="form-control">
                             <option value="">Choose</option>
                           @foreach($type as $key=>$types)

                             <option value={{$types}}>{{$types}}</option>
                          @endforeach
                          </select>

                                </div>


                            </div>

                            <div class="row">

                                <div class="col-md-6 form-group {{ $errors->has('description') ? 'has-error' : '' }}">
                                    <!-- last name -->
                                    <script src="//cdn.tinymce.com/4/tinymce.min.js"></script>
                                    <script>
                                    tinymce.init({
                                        selector: 'textarea',
                                        plugins: "code",
                                        toolbar: "code",
                                        menubar: "tools"
                                    });
                                    </script>


                                    {!! Form::label('description',Lang::get('message.description')) !!}
                                    {!! Form::textarea('description',null,['class' => 'form-control','id'=>'textarea']) !!}
                                <h6 id= "descheck"></h6>
                                </div>
                                 <div class="form-group {{ $errors->has('image') ? 'has-error' : '' }}">
                                                        <!-- last name -->
                                                {!! Form::label('image',Lang::get('message.image')) !!}
                                                {!! Form::file('image') !!}

                                        </div>
                                <div class="col-md-6">
                                    <ul class="list-unstyled">
                                          <li>
                                            <div class="form-group {{ $errors->has('parent') ? 'has-error' : '' }}">
                                                <!-- last name -->
                                                {!! Form::label('sku',Lang::get('message.sku'),['class'=>'required']) !!}
                                                {!! Form::text('product_sku',null,['class' => 'form-control']) !!}

                                            </div>
                                        </li>

                                        <li>
                                            <div class="form-group {{ $errors->has('parent') ? 'has-error' : '' }}">
                                                <!-- last name -->
                                                {!! Form::label('parent',Lang::get('message.parent')) !!}
                                                {!! Form::select('parent[]',['Products'=>$products],null,['class' => 'form-control']) !!}

                                            </div>
                                        </li>

                                       
                           
                                        <li>
                                            <div class="form-group {{ $errors->has('require_domain') ? 'has-error' : '' }}">
                                                <!-- last name -->
                                                {!! Form::label('require_domain',Lang::get('message.require_domain')) !!}
                                                <p>{!! Form::checkbox('require_domain',1) !!} {{Lang::get('message.tick-to-show-domain-registration-options')}}</p>

                                            </div>
                                        </li>

                                        <li>
                                            <div class="form-group {{ $errors->has('shoping_cart_link') ? 'has-error' : '' }}">
                                                <!-- last name -->
                                                {!! Form::label('shoping_cart_link',Lang::get('message.shoping-cart-link')) !!}
                                                {!! Form::text('shoping_cart_link',$cartUrl,['class'=>'form-control']) !!}

                                            </div>
                                        </li>

                                         <li>
                                            <div class="form-group {{ $errors->has('hidden') ? 'has-error' : '' }}">
                                                <!-- first name -->
                                               <!--  <button type="button" class="" data-toggle="tooltip" data-placement="top" title="Tooltip on top"></button> -->
                                                <label data-toggle="tooltip" data-placement="top" title="">Hidden</label>
                                               
                                                <p>{!! Form::checkbox('hidden',1) !!}  {{Lang::get('message.tick-to-hide-from-order-form')}}</p>

                                            </div>
                                        </li>
                                    </ul>
                                </div>

                            </div>

                           
                        </div>
                        <!-- /.tab-pane -->
                        <div class="tab-pane" id="tab_2">
                            <table class="table table-responsive">

                                <tr>
                                    <td><b>{!! Form::label('subscription',Lang::get('message.subscription')) !!}</b></td>
                                    <td>
                                        <div class="form-group {{ $errors->has('subscription') ? 'has-error' : '' }}">
                                            <div class="row">
                                                <div class="col-md-4">
                                                    {!! Form::hidden('subscription',0) !!}
                                                    {!! Form::checkbox('subscription',1,true) !!}
                                                     <label data-toggle="tooltip" data-placement="top" title="If checked the product if purchased will have an expiry date, else, unlimited subscription">
                                                    {!! Form::label('subscription',Lang::get('message.limited-subscription')) !!}</label>
                                                </div>
                                            


                                         



                                                <div class="col-md-4">
                                                     {!! Form::radio('deny_after_subscription',1) !!}
                                                      <label name="subscription" data-toggle="tooltip" data-placement="top" title="{!!Lang::get('message.perpetual-description') !!}">
                                                    {!! Form::label('deny_after_subscription',Lang::get('message.perpetual-download')) !!}
                                                </div>

                                                <div class="col-md-4">
                                                    {!! Form::radio('deny_after_subscription',0) !!}
                                                      <label name="subscription" data-toggle="tooltip" data-placement="top" title="{!!Lang::get('message.retired-description') !!}">
                                                    {!! Form::label('deny_after_subscription',Lang::get('message.retired-download')) !!}
                                                </div>
                                                <div class="col-md-4">
                                                     {!! Form::hidden('perpetual_license',0) !!}
                                                    {!! Form::checkbox('perpetual_license',1,true) !!}
                                                      <label name="subscription" data-toggle="tooltip" data-placement="top" title="{!!Lang::get('message.perpetual_license-detail') !!}">
                                                    {!! Form::label('perpetual_license',Lang::get('message.perpetual_license')) !!}
                                                </div>
                                               

                                            </div>
                                        </div>
                                    </td>
                                </tr>

                               

                                <tr>
                                    <td><b>{!! Form::label('multiple_qty',Lang::get('message.allow-multiple-quantities')) !!}</b></td>
                                    <td>
                                        <div class="form-group {{ $errors->has('multiple_qty') ? 'has-error' : '' }}">

                                            <p>{!! Form::checkbox('multiple_qty',1) !!}  {{Lang::get('message.tick-this-box-to-allow-customers-to-specify-if-they-want-more-than-1-of-this-item-when-ordering')}} </p>

                                        </div>
                                    </td>
                                </tr>

                               

                                <tr>
                                    <td><b>{!! Form::label('tax',Lang::get('message.taxes')) !!}</b></td>
                                    <td>
                                        <div class="form-group {{ $errors->has('taxes') ? 'has-error' : '' }}">
                                            <div class="row">
                                                <div class="col-md-2" >
                                                     
                                                    <select id="Tax" placeholder="Select Taxes" name="tax[]" style="width:500px;" class="select2" multiple="true">
                                                       <option></option>
                                                       @foreach($taxes as $key => $value)
                                                        <option value={{$key}}>{{$value}}</option> 
                                                        @endforeach
                                                    </select>
                                                
                                                </div>
                                            </div>

                                        </div>
                                    </td>
                                   
                                    
                                </tr>
                                <tr>
                               
                                </tr>

                            </table>

                       
        {!! Form::close() !!}

 
                                        
                              <h3>  Plans &nbsp;<a href="#create-plan-option" data-toggle="modal" data-target="#create-plan-option" class="btn btn-default btn-sm"><span class="glyphicon glyphicon-plus"></span>&nbsp;&nbsp; Add new</a> </h3>
                           
                                      @include('themes.default1.product.plan.create') 
                                  
                                    </div>



@stop
 @section('icheck')
  <script src="//cdnjs.cloudflare.com/ajax/libs/jquery/2.0.3/js/jquery.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.3/js/select2.min.js"></script>
    <!-- <script type="text/javascript">
        $("#editTax").select2();
    </script> -->
<link href="//cdnjs.cloudflare.com/ajax/libs/select2/4.0.0/css/select2.min.css" rel="stylesheet" />
<script src="//cdnjs.cloudflare.com/ajax/libs/select2/4.0.0/js/select2.min.js"></script>


<!-- <script>
=======
<script>
>>>>>>> parent of 58a3ffa4... update
    // Jquery validation for Product Creation
    $(document).ready(function(){
        $('#namecheck').hide();
        $('#descheck').hide();

        var nameErr= true;
        var desErr = true;

        $('#createproduct').submit(function(){
            function name_check(){
                var name = $('#productname').val();
                if (name.length == ''){
                   $('#namecheck').show(); 
                   $('#namecheck').html('This field is required'); 
                   $('#namecheck').focus();
                   $('#productname').css("border-color","red");
                   $('#namecheck').css({"color":"red","margin-top":"5px"});
                }
                 else{
                     $('#namecheck').hide();
                      $('#productname').css("border-color","");
                     return true;
                     }
            }

            function des_check(){
                var des = $('#textarea').val();
                if (des.length == ''){
                    $('#descheck').show();
                    $('#descheck').html('This field is required');
                    $('#descheck').focus();
                    $('#textarea').css("border-color","red");
                    $('#descheck').css({"color":"red","margin-top":"5px"});
                }
                else{
                     $('#descheck').hide();
                     $('#textarea').css("border-color","");
                     return true;
                }
            }
            name_check();
            des_check();
             if(name_check() && des_check()){
                return true;
             }
            else{
            return false;
          }
        });
    });
<<<<<<< HEAD
</script> -->


</script>

<script>
    $(document).ready(function() {
    $("#Tax").select2({
        placeholder: 'Select Taxes',
        tags:true
    });
});
</script>
@stop