@extends('themes.default1.layouts.master')
@section('title')
  Edit Plan
@stop
@section('content-header')
  <div class="col-sm-6">
        <h1>{{ __('message.edit_plan') }}</h1>
    </div>
    <div class="col-sm-6">
        <ol class="breadcrumb float-sm-right">
            <li class="breadcrumb-item"><a href="{{url('/')}}"><i class="fa fa-dashboard"></i> {{ __('message.home') }}</a></li>
            <li class="breadcrumb-item"><a href="{{url('plans')}}"><i class="fa fa-dashboard"></i> {{ __('message.all_plans') }}</a></li>
            <li class="breadcrumb-item active">{{ __('message.edit_plan') }}</li>
        </ol>
    </div><!-- /.col -->
@stop
@section('content')
  <div class="card card-secondary card-outline">

  

    
    {!! Form::model($plan,['url'=>'plans/'.$plan->id,'method'=>'patch']) !!}
    <div class="card-body">


      <div class="row">

        <div class="col-md-12">

          <div class="row">
            <div class="col-md-4 form-group {{ $errors->has('name') ? 'has-error' : '' }}">
              <!-- first name -->
              {!! Form::label('name',Lang::get('message.name'),['class'=>'required']) !!}
              {!! Form::text('name',null,['class' => 'form-control']) !!}

            </div>
            <div class="col-md-4 form-group {{ $errors->has('product') ? 'has-error' : '' }}">
              <!-- first name -->
              {!! Form::label('product',Lang::get('message.product'),['class'=>'required']) !!}
              <select name="product" id="planproduct" class="form-control" onchange="myProduct()">
                <option value="">{{ __('message.choose') }}</option>

                @foreach($products as $key=>$product)
                  <option value="{{$key}}"  <?php  if(in_array($product, $selectedProduct) ) { echo "selected";} ?>>{{$product}}</option>

                @endforeach
              </select>



            </div>
            <div class="col-md-4 form-group plandays {{ $errors->has('days') ? 'has-error' : '' }}">
              <!-- last name -->
              {!! Form::label('days','Periods',['class'=>'required']) !!}
              <select name="days" id="plan" class="form-control">
                <option value="">{{ __('message.choose') }}</option>

                @foreach($periods as $key=>$period)
                  <option value="{{$key}}" <?php  if(in_array($period, $selectedPeriods) ) { echo "selected";} ?>>{{$period}}</option>

                @endforeach
              </select>

            </div>

            <div class="col-md-12">


                <table class="table table-responsive table-bordered table-hover" id="dynamic_table">
                  <thead>
                    <tr>
                      <th class="col-sm-6" style="width:10%">{{ Lang::get('message.country') }} <span class="text-red">*</span> </th>
                      <th class="col-sm-6" style="width:10%">{{ Lang::get('message.currency') }} <span class="text-red">*</span> </th>
                      <th class="col-sm-6" style="width:10%">{{ Lang::get('message.regular-price') }} <span class="text-red">*</span> </th>
                      <th class="col-sm-3" style="width:10%">
                        {{ Lang::get('Offer Price') }} <span class="text-bold">(%)</span>
                      </th>
                      <th class="col-sm-6" style="width:10%">
                        {{ Lang::get('message.renew-price') }} <span class="text-red">*</span>
                      </th>
                    </tr>
                  </thead>

                  <tbody>
                    @foreach($planPrices as $row)
                      <tr id="row{{$loop->iteration}}" class="form-group {{ $errors->has('add_price.'.$key) ? 'has-error' : '' }}">

                        <td>
                          <select name="country_id[{{ $row['id'] }}]" class="form-control" >
                            @if (0 === $row['country_id'])
                                <option value="0" selected>{{ __('message.default') }}</option>
                            @endif
                            @if (0 !== $row['country_id'])
                              @foreach ($countries as $country)
                                <option value="{{$country['country_id']}}"  @if ($country['country_id'] === $row['country_id'])
                                  {{ 'selected' }}
                                        @endif>
                                  {{ $country['country_name'] }}
                                </option>
                              @endforeach
                            @endif
                          </select>
                        </td>

                        <td>
                          <select name="currency[{{ $row['id'] }}]" class="form-control">
                            <option value="">
                              {{ __('message.choose') }}
                              </option>
                            @foreach ($currency as $code => $name)
                              <option value="{{ $code }}" @if ($code === $row['currency'])
                                  {{ 'selected' }}
                              @endif>
                                {{ $name }}
                              </option>
                            @endforeach
                          </select>
                        </td>

                        <td>
                          <input type="text" class="form-control" name="add_price[{{ $row['id'] }}]" value="{{ $row['add_price'] }}">

                          <td>
                          <input type="text" class="form-control" name="offer_price[{{ $row['id'] }}]" value="{{ $row['offer_price'] }}">
                        </td>
                        </td>

                        <td>
                          <div class="{{ ($row['country_id'] != 0) ? 'input-group' : '' }}">
                            <input type="text" class="form-control" name="renew_price[{{ $row['id'] }}]" value="{{ $row['renew_price'] }}"> &nbsp;&nbsp;
                            @if($row['country_id'] != 0)
                              <span class="input-group-text btn_remove" id="{{$loop->iteration}}"><i class="fa fa-minus"></i></span>
                            @endif
                          </div>
                        </td>

                      </tr>
                    @endforeach
                  </tbody>
                </table>
            </div>

            <div class="col-sm-12" style="margin-bottom: 10px;">
              <button class="btn btn-sm btn-default add-more"><i class="fa fa-plus"></i>&nbsp;{{ trans('message.add_price_for_country') }}</button>
            </div>


            <div class="col-md-4 form-group">
              <!-- last name -->
              {!! Form::label('description','Price Description') !!}
              {!! Form::text("price_description",$priceDescription,['class' => 'form-control' ,'placeholder'=> __('message.price_description')]) !!}
              <h6 id="dayscheck"></h6>

            </div>
            <div class="col-md-4 form-group">
              <!-- last name -->
              {!! Form::label('product_quantity','Product Quantity',['class'=>'required']) !!}
              {!! Form::number("product_quantity",$productQuantity,['class' => 'form-control','disabled'=>'disabled','id'=>'prodquant','placeholder'=> __('message.price_products')]) !!}

            </div>

            <div class="col-md-4 form-group">
              <!-- last name -->
                 <i class='fa fa-info-circle' style='cursor: help; font-size: small; color: rgb(60, 141, 188)'<label data-toggle="tooltip" style="font-weight:500;" data-placement="top" title="If '0' Agents Selected, Plan will be for Unlimited Agents.">
                        </label></i>
                {!! Form::label('agents','No. of Agents',['class'=>'required']) !!}
              {!! Form::number("no_of_agents",$agentQuantity,['class' => 'form-control' ,'disabled'=>'disabled','id'=>'agentquant','placeholder'=> __('message.price_agents')]) !!}

            </div>

          </div>

        </div>


      </div>
      <div class="box-footer">
      <button type="submit" class="btn btn-primary pull-left"><i class="fas fa-sync-alt">&nbsp;</i>{!!Lang::get('message.update')!!}</button>

    </div>

    </div>

    

  </div>



  {!! Form::close() !!}

  <script>
    $(function () {
        $('[data-toggle="tooltip"]').tooltip({
            container : 'body'
        });
    });

     $('ul.nav-sidebar a').filter(function() {
        return this.id == 'plan';
    }).addClass('active');

    // for treeview
    $('ul.nav-treeview a').filter(function() {
        return this.id == 'plan';
    }).parentsUntil(".nav-sidebar > .nav-treeview").addClass('menu-open').prev('a').addClass('active');


    $( document ).ready(function() {
      var product = document.getElementById('planproduct').value;
      $.ajax({
        type: 'get',
        url : "{{url('get-period')}}",
        data: {'product_id':product},
        success: function (data){
          if(data.subscription != 1 ){
            $('.plandays').hide();
          }
          else{
            $('.plandays').show();
          }
          if(data.agentEnable != 1) {//Check if Product quantity to be sh`own or No. of Agents
            document.getElementById("prodquant").disabled = false;
            document.getElementById("agentquant").disabled = true;

          } else if(data.agentEnable == 1){
            document.getElementById("agentquant").disabled = false;
            document.getElementById("prodquant").disabled = true;
          }
        }
      });

      var i = 1000;
      $(".add-more").click(function (e) {
        e.preventDefault();
        i++;
        $('#dynamic_table tr:last').after(`
        <tr id="row` + i + `">
          <td>
            <select name="country_id[]" class="form-control selectpicker" >
              <option value="" selected disabled>Choose Country</option>
              @foreach ($countries as $country)
                <option value="{{$country['country_id']}}">
                  {{ $country['country_name'] }}
                </option>
              @endforeach
            </select>
          </td>

          <td>
            <select name="currency[]" class="form-control">
            <option value="">
              Choose
            </option>
              @foreach ($currency as $code => $name)
                <option value="{{ $code }}">
                  {{ $name }}
                </option>
              @endforeach
            </select>
          </td>

          <td>
            <input type="text" class="form-control" name="add_price[]">
          </td>
          <td>
            <input type="text" class="form-control" name="offer_price[]">
          </td>

          <td>
            <div class="input-group">
              <input type="text" class="form-control" name="renew_price[]">&nbsp;&nbsp;
              <span id="` + i + `" class="input-group-text btn_remove"><i class="fa fa-minus"></i></span>
            </div>
          </td>

        </tr>`)
      });

      $(document).on('click', '.btn_remove', function () {
        var button_id = $(this).attr("id");
        $('#row' + button_id + '').remove();
      });

    });



    function myProduct(){
      var product = document.getElementById('planproduct').value;
      // console.log(product)
      $.ajax({
        type: 'get',
        url : "{{url('get-period')}}",
        data: {'product_id':product},
        success: function (data){
          console.log(data.subscription);

          if(data.subscription != 1 ){
            $('.plandays').hide();
          }
          else{
            $('.plandays').show();
          }
          if(data.agentEnable != 1) {//Check if Product quantity to be shown or No. of Agents
            document.getElementById("prodquant").disabled = false;
            document.getElementById("agentquant").disabled = true;

          } else if(data.agentEnable == 1){
            document.getElementById("agentquant").disabled = false;
            document.getElementById("prodquant").disabled = true;
          }

          var sub = data['subscription'];

        }
      });
    }
  </script>

@stop