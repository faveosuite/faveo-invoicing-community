@extends('themes.default1.layouts.master')
@section('title')
  Edit Plan
@stop
@section('content-header')
  <div class="col-sm-6">
        <h1>Edit Plan</h1>
    </div>
    <div class="col-sm-6">
        <ol class="breadcrumb float-sm-right">
            <li class="breadcrumb-item"><a href="{{url('/')}}"><i class="fa fa-dashboard"></i> Home</a></li>
            <li class="breadcrumb-item"><a href="{{url('plans')}}"><i class="fa fa-dashboard"></i> All Plans</a></li>
            <li class="breadcrumb-item active">Edit Plan</li>
        </ol>
    </div><!-- /.col -->
@stop
@section('content')
  <div class="card card-secondary card-outline">

    {!! Form::model($plan,['url'=>'plans/'.$plan->id,'method'=>'patch','id'=>'editPlan']) !!}
    <div class="card-body">

      <div class="row">

        <div class="col-md-12">

          <div class="row">
            <div class="col-md-4 form-group {{ $errors->has('name') ? 'has-error' : '' }}">
              <!-- name -->
              {!! Form::label('name',Lang::get('message.name'),['class'=>'required']) !!}
              {!! Form::text('name',null,['class' => 'form-control','id'=>'planname']) !!}
              @error('name')
              <span class="error-message"> {{$message}}</span>
              @enderror
              <div class="input-group-append">
              </div>
            </div>
            <div class="col-md-4 form-group {{ $errors->has('product') ? 'has-error' : '' }}">
              <!-- product -->
              {!! Form::label('product',Lang::get('message.product'),['class'=>'required']) !!}
              <select name="product" id="planproduct" class="form-control" onchange="myProduct()">
                <option value="">Choose</option>

                @foreach($products as $key=>$product)
                  <option value="{{$key}}"  <?php  if(in_array($product, $selectedProduct) ) { echo "selected";} ?>>{{$product}}</option>
                @endforeach
              </select>
              @error('product')
              <span class="error-message"> {{$message}}</span>
              @enderror
              <div class="input-group-append">
              </div>

            </div>
            <div class="col-md-4 form-group plandays {{ $errors->has('days') ? 'has-error' : '' }}">
              <!-- days-->
              {!! Form::label('days','Periods',['class'=>'required']) !!}
              <select name="days" id="plandays" class="form-control">
                <option value="">Choose</option>

                @foreach($periods as $key=>$period)
                  <option value="{{$key}}" <?php  if(in_array($period, $selectedPeriods) ) { echo "selected";} ?>>{{$period}}</option>

                @endforeach
              </select>
              @error('days')
              <span class="error-message"> {{$message}}</span>
              @enderror
              <div class="input-group-append">
              </div>
            </div>

            <div class="col-md-12">


                <table class="table table-responsive table-bordered table-hover" id="dynamic_table">
                  <thead>
                    <tr>
                      <th class="col-sm-6" style="width:10%">{{ Lang::get('message.country') }} <span class="text-red">*</span> </th>
                      <th class="col-sm-6" style="width:10%">{{ Lang::get('message.currency') }} <span class="text-red">*</span> </th>
                      <th class="col-sm-6" style="width:10%">{{ Lang::get('message.price') }} <span class="text-red">*</span> </th>
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
                          <select name="country_id[{{ $row['id'] }}]" class="form-control" id="country">
                            @if (0 === $row['country_id'])
                                <option value="0" selected>Default</option>
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
                          @error('country_id')
                          <span class="error-message"> {{$message}}</span>
                          @enderror

                          <div class="input-group-append">
                          </div>
                        </td>

                        <td>
                          <select name="currency[{{ $row['id'] }}]" class="form-control" id="currency">
                            <option value="">
                                Choose
                              </option>
                            @foreach ($currency as $code => $name)
                              <option value="{{ $code }}" @if ($code === $row['currency'])
                                  {{ 'selected' }}
                              @endif>
                                {{ $name }}
                              </option>
                            @endforeach
                          </select>
                          @error('currency')
                          <span class="error-message"> {{$message}}</span>
                          @enderror
                          <div class="input-group-append">
                          </div>
                        </td>

                        <td>
                          <input type="number" class="form-control" name="add_price[{{ $row['id'] }}]" value="{{ $row['add_price'] }}" id="regular_price">
                          @error('add_price')
                          <span class="error-message"> {{$message}}</span>
                          @enderror
                          <div class="input-group-append">
                          </div>
                          <td>
                          <input type="number" class="form-control" name="offer_price[{{ $row['id'] }}]" value="{{ $row['offer_price'] }}">
                          @error('offer_price')
                          <span class="error-message"> {{$message}}</span>
                          @enderror
                          <div class="input-group-append">
                          </div>
                        </td>
                        </td>

                        <td>
                          <div class="{{ ($row['country_id'] != 0) ? 'input-group' : '' }}">
                            <input type="number" class="form-control" name="renew_price[{{ $row['id'] }}]" value="{{ $row['renew_price'] }}" id="renew_price"> &nbsp;&nbsp;
                            @error('renew_price')
                            <span class="error-message"> {{$message}}</span>
                            @enderror
                            <div class="input-group-append">
                            </div>
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
              {!! Form::text("price_description",$priceDescription,['class' => 'form-control' ,'placeholder'=>'Enter Price Description to be Shown on Pricing Page. eg: Yearly,Monthly,One-Time']) !!}
              @error('description')
              <span class="error-message"> {{$message}}</span>
              @enderror
              <h6 id="dayscheck"></h6>

            </div>
            <div class="col-md-4 form-group">
              <!-- last name -->
              {!! Form::label('product_quantity','Product Quantity',['class'=>'required']) !!}
              {!! Form::number("product_quantity",$productQuantity,['class' => 'form-control','disabled'=>'disabled','id'=>'prodquant','placeholder'=>'Pricing for No. of Products']) !!}
              @error('product_quantity')
              <span class="error-message"> {{$message}}</span>
              @enderror
              <div class="input-group-append">
              </div>
            </div>

            <div class="col-md-4 form-group">
              <!-- last name -->
                 <i class='fa fa-info-circle' style='cursor: help; font-size: small; color: rgb(60, 141, 188)'<label data-toggle="tooltip" style="font-weight:500;" data-placement="top" title="If '0' Agents Selected, Plan will be for Unlimited Agents.">
                        </label></i>
                {!! Form::label('agents','No. of Agents',['class'=>'required']) !!}
              {!! Form::number("no_of_agents",$agentQuantity,['class' => 'form-control' ,'disabled'=>'disabled','id'=>'agentquant','placeholder'=>'Pricing for No. of Agents']) !!}
              @error('no_of_agents')
              <span class="error-message"> {{$message}}</span>
              @enderror
              <div class="input-group-append">
              </div>
            </div>

          </div>

        </div>
      </div>
      <div class="box-footer">
      <button type="submit" class="btn btn-primary pull-left" id="planButtons"><i class="fas fa-sync-alt">&nbsp;</i>{!!Lang::get('message.update')!!}</button>

    </div>

    </div>

  </div>



  {!! Form::close() !!}

  <script>

    $(document).ready(function() {
      const userRequiredFields = {
        planname:@json(trans('message.plan_details.planname')),
        planproduct:@json(trans('message.plan_details.planproduct')),
        agentquant:@json(trans('message.plan_details.agentquant')),
        regular_price:@json(trans('message.plan_details.regular_price')),
        renew_price:@json(trans('message.plan_details.renewal_price')),
        currency:@json(trans('message.plan_details.currency')),
        country:@json(trans('message.plan_details.country')),

      };

      $('#editPlan').on('submit', function (e) {
        const userFields = {
          planname:$('#planname'),
          planproduct:$('#planproduct'),
          regular_price:$('#regular_price'),
          renew_price:$('#renew_price'),
          currency:$('#currency'),
          country:$('#country'),
        };


        // Clear previous errors
        Object.values(userFields).forEach(field => {
          field.removeClass('is-invalid');
          field.next().next('.error').remove();

        });

        let isValid = true;

        const showError = (field, message) => {
          field.addClass('is-invalid');
          field.next().after(`<span class='error invalid-feedback'>${message}</span>`);
        };

        // Validate required fields
        Object.keys(userFields).forEach(field => {
          if (!userFields[field].val()) {
            showError(userFields[field], userRequiredFields[field]);
            isValid = false;
          }
        });

        // If validation fails, prevent form submission
        if (!isValid) {
          e.preventDefault();
        }
      });
      // Function to remove error when input'id' => 'changePasswordForm'ng data
      const removeErrorMessage = (field) => {
        field.classList.remove('is-invalid');
        const error = field.nextElementSibling;
        if (error && error.classList.contains('error')) {
          error.remove();
        }
      };

      // Add input event listeners for all fields
      ['planname','planproduct','country','currency','agentquant','renew_price','regular_price'].forEach(id => {

        document.getElementById(id).addEventListener('input', function () {
          removeErrorMessage(this);

        });
      });
    });

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